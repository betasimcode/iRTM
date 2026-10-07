#!/usr/bin/env python3
"""
iRTM - IBT Probe
----------------
Prueba RAW de vueltas + reconstrucción de sectores desde un IBT.

La lógica de sectores reproduce el principio usado actualmente por el
cronómetro C#:

    LapDistPct + SessionTime
        -> interpolación exacta en cada SectorStartPct
        -> tiempo de cada sector

El IBT es la única fuente de datos de esta prueba.

NO:
- modifica el IBT
- accede a Laravel
- aplica filtros de iRTM
- utiliza el cronómetro C#

Sí:
- obtiene las fronteras de sector desde SplitTimeInfo.Sectors
- obtiene los tiempos desde SessionTime
- detecta el cruce de meta mediante LapDistPct
- calcula los sectores mediante interpolación lineal

Formato:
    MM:SS.mmm
"""

from __future__ import annotations

import argparse
import csv
import mmap
import struct
from pathlib import Path
from typing import Any

import yaml


HEADER_FORMAT = "<11i"
HEADER_SIZE = 112
VAR_HEADER_SIZE = 144
DISK_SUBHEADER_OFFSET = 112

TYPE_FORMAT = {
    0: "b",
    1: "?",
    2: "i",
    3: "I",
    4: "f",
    5: "d",
}

TYPE_NAME = {
    0: "char",
    1: "bool",
    2: "int",
    3: "bitfield",
    4: "float",
    5: "double",
}


def c_string(raw: bytes) -> str:
    return raw.split(b"\0", 1)[0].decode(
        "latin-1",
        errors="replace",
    )


def format_laptime(seconds: float | None) -> str:
    if seconds is None:
        return "--:--.---"

    seconds = float(seconds)

    if seconds < 0:
        return "--:--.---"

    total_ms = int(round(seconds * 1000))
    minutes = total_ms // 60000
    remaining_ms = total_ms % 60000
    secs = remaining_ms // 1000
    millis = remaining_ms % 1000

    return f"{minutes:02d}:{secs:02d}.{millis:03d}"


def read_header(mm: mmap.mmap) -> dict[str, Any]:
    values = struct.unpack_from(
        HEADER_FORMAT,
        mm,
        0,
    )

    (
        version,
        status,
        tick_rate,
        session_info_update,
        session_info_len,
        session_info_offset,
        num_vars,
        var_header_offset,
        num_buf,
        buf_len,
        cur_buf_tick_count,
    ) = values

    return {
        "version": version,
        "status": status,
        "tick_rate": tick_rate,
        "session_info_update": session_info_update,
        "session_info_len": session_info_len,
        "session_info_offset": session_info_offset,
        "num_vars": num_vars,
        "var_header_offset": var_header_offset,
        "num_buf": num_buf,
        "buf_len": buf_len,
        "cur_buf_tick_count": cur_buf_tick_count,
    }


def read_disk_header(mm: mmap.mmap) -> dict[str, Any]:
    (
        start_date,
        start_time,
        end_time,
        lap_count,
        record_count,
    ) = struct.unpack_from(
        "<Qddii",
        mm,
        DISK_SUBHEADER_OFFSET,
    )

    return {
        "session_start_date": start_date,
        "session_start_time": start_time,
        "session_end_time": end_time,
        "session_lap_count": lap_count,
        "session_record_count": record_count,
    }


def read_variables(
    mm: mmap.mmap,
    header: dict[str, Any],
) -> list[dict[str, Any]]:
    variables = []

    for index in range(header["num_vars"]):
        offset = (
            header["var_header_offset"]
            + index * VAR_HEADER_SIZE
        )

        var_type, var_offset, count, count_as_time = struct.unpack_from(
            "<iii?",
            mm,
            offset,
        )

        variables.append(
            {
                "index": index,
                "name": c_string(
                    mm[offset + 16:offset + 48]
                ),
                "type": var_type,
                "type_name": TYPE_NAME.get(
                    var_type,
                    f"unknown({var_type})",
                ),
                "offset": var_offset,
                "count": count,
                "count_as_time": bool(count_as_time),
                "description": c_string(
                    mm[offset + 48:offset + 112]
                ),
                "unit": c_string(
                    mm[offset + 112:offset + 144]
                ),
            }
        )

    return variables


def read_scalar(
    mm: mmap.mmap,
    data_offset: int,
    variable: dict[str, Any],
) -> Any:
    fmt = TYPE_FORMAT.get(variable["type"])

    if fmt is None or variable["count"] != 1:
        return None

    return struct.unpack_from(
        f"<{fmt}",
        mm,
        data_offset + variable["offset"],
    )[0]


def get_first_buffer_offset(mm: mmap.mmap) -> int:
    # Offset del primer buffer de datos en el header IBT.
    return struct.unpack_from(
        "<i",
        mm,
        52,
    )[0]


def read_session_info(
    mm: mmap.mmap,
    header: dict[str, Any],
) -> dict[str, Any]:
    raw = mm[
        header["session_info_offset"]:
        header["session_info_offset"]
        + header["session_info_len"]
    ]

    text = raw.decode(
        "utf-8",
        errors="replace",
    )

    data = yaml.safe_load(text)

    if not isinstance(data, dict):
        raise RuntimeError(
            "SessionInfo del IBT no contiene YAML válido."
        )

    return data


def get_sector_boundaries(
    session_info: dict[str, Any],
) -> list[float]:
    """
    Lee:

        SplitTimeInfo:
            Sectors:
                SectorNum
                SectorStartPct

    Devuelve únicamente las fronteras de inicio de sector y añade
    1.0 como frontera de meta.
    """
    split_info = session_info.get(
        "SplitTimeInfo",
        {},
    )

    if not isinstance(split_info, dict):
        raise RuntimeError(
            "El IBT no contiene SplitTimeInfo."
        )

    sectors = split_info.get(
        "Sectors",
        [],
    )

    if not sectors:
        raise RuntimeError(
            "El IBT no contiene SplitTimeInfo.Sectors."
        )

    starts = sorted(
        set(
            float(sector["SectorStartPct"])
            for sector in sectors
            if sector.get("SectorStartPct") is not None
        )
    )

    if not starts:
        raise RuntimeError(
            "SplitTimeInfo.Sectors no contiene SectorStartPct."
        )

    if starts[0] > 0.000001:
        starts.insert(0, 0.0)

    if starts[-1] >= 1.0:
        starts = [
            pct
            for pct in starts
            if pct < 1.0
        ]

    return starts + [1.0]


def interpolate_time(
    target_pct: float,
    previous_pct: float,
    current_pct: float,
    previous_time: float,
    current_time: float,
) -> float:
    """
    Misma interpolación utilizada por el cronómetro C#:

        pTime + ((target - pPct) / (cPct - pPct))
                 * (cTime - pTime)
    """
    if abs(current_pct - previous_pct) < 0.000001:
        return current_time

    return (
        previous_time
        + (
            (target_pct - previous_pct)
            / (current_pct - previous_pct)
        )
        * (current_time - previous_time)
    )


def interpolate_sector(
    target_pct: float,
    samples: list[tuple[float, float]],
) -> float | None:
    """
    Busca el mismo cruce que haría el C#:

        currentPct >= target
        lastPct < target

    y aplica la misma interpolación con SessionTime.
    """
    for index in range(1, len(samples)):
        previous_pct, previous_time = samples[
            index - 1
        ]
        current_pct, current_time = samples[
            index
        ]

        if (
            current_pct >= target_pct
            and previous_pct < target_pct
        ):
            return interpolate_time(
                target_pct,
                previous_pct,
                current_pct,
                previous_time,
                current_time,
            )

    return None


def calculate_meta_crossing(
    previous_pct: float,
    current_pct: float,
    previous_time: float,
    current_time: float,
) -> float:
    """
    Reproduce el cálculo del cronómetro C# al pasar de ~100 % a ~0 %:

        target = 1.0
        currentPct = currentPct + 1.0
    """
    return interpolate_time(
        1.0,
        previous_pct,
        current_pct + 1.0,
        previous_time,
        current_time,
    )


def reconstruct_sectors(
    samples: list[tuple[float, float]],
    sector_starts: list[float],
    lap_start_time: float,
    lap_end_time: float,
) -> dict[str, Any]:
    """
    Reconstruye los timestamps de cada frontera y obtiene la duración
    de cada sector exactamente como el cronómetro actual.

    sector_starts contiene:
        [0.0, S2, S3, ..., 1.0]

    El 0.0 se representa mediante lap_start_time.
    El 1.0 se representa mediante lap_end_time.
    """
    timestamps = [lap_start_time]

    for target in sector_starts[1:-1]:
        exact_time = interpolate_sector(
            target,
            samples,
        )

        timestamps.append(
            exact_time
            if exact_time is not None
            else None
        )

    timestamps.append(lap_end_time)

    sector_times = []

    for index in range(1, len(timestamps)):
        start = timestamps[index - 1]
        end = timestamps[index]

        if start is None or end is None:
            sector_times.append(None)
        else:
            sector_times.append(
                end - start
            )

    reconstructed_lap = None

    if (
        timestamps[0] is not None
        and timestamps[-1] is not None
    ):
        reconstructed_lap = (
            timestamps[-1]
            - timestamps[0]
        )

    return {
        "boundary_timestamps": timestamps,
        "sector_times": sector_times,
        "reconstructed_lap_time": reconstructed_lap,
    }


def extract_laps(
    path: Path,
) -> dict[str, Any]:
    with path.open("rb") as f:
        with mmap.mmap(
            f.fileno(),
            0,
            access=mmap.ACCESS_READ,
        ) as mm:

            header = read_header(mm)
            disk = read_disk_header(mm)

            variables = read_variables(
                mm,
                header,
            )

            session_info = read_session_info(
                mm,
                header,
            )

            sector_boundaries = get_sector_boundaries(
                session_info,
            )

            variables_by_name = {
                variable["name"]: variable
                for variable in variables
            }

            required = [
                "Lap",
                "LapCompleted",
                "LapDistPct",
                "LapLastLapTime",
                "LapBestLapTime",
                "SessionTime",
            ]

            missing = [
                name
                for name in required
                if name not in variables_by_name
            ]

            if missing:
                raise RuntimeError(
                    "Faltan variables requeridas en el IBT: "
                    + ", ".join(missing)
                )

            first_buffer_offset = (
                get_first_buffer_offset(mm)
            )

            record_count = (
                disk["session_record_count"]
            )

            record_size = header["buf_len"]

            v_lap = variables_by_name["Lap"]
            v_completed = variables_by_name[
                "LapCompleted"
            ]
            v_pct = variables_by_name[
                "LapDistPct"
            ]
            v_last = variables_by_name[
                "LapLastLapTime"
            ]
            v_best = variables_by_name[
                "LapBestLapTime"
            ]
            v_session = variables_by_name[
                "SessionTime"
            ]

            laps = []

            current_lap_number = None
            current = None

            previous_pct = None
            previous_session_time = None
            previous_last_lap_time = None
            previous_best_lap_time = None

            for record_index in range(
                record_count
            ):
                data_offset = (
                    first_buffer_offset
                    + record_index * record_size
                )

                lap = read_scalar(
                    mm,
                    data_offset,
                    v_lap,
                )

                completed = read_scalar(
                    mm,
                    data_offset,
                    v_completed,
                )

                pct = float(
                    read_scalar(
                        mm,
                        data_offset,
                        v_pct,
                    )
                )

                session_time = float(
                    read_scalar(
                        mm,
                        data_offset,
                        v_session,
                    )
                )

                last_lap_time = read_scalar(
                    mm,
                    data_offset,
                    v_last,
                )

                best_lap_time = read_scalar(
                    mm,
                    data_offset,
                    v_best,
                )

                # Primer registro del IBT.
                if current_lap_number is None:
                    current_lap_number = lap

                    current = {
                        "lap": lap,
                        "completed_start": completed,
                        "start_record": record_index,
                        "end_record": record_index,
                        "first_pct": pct,
                        "last_pct": pct,
                        "samples": 1,
                        "lap_time": None,
                        "best_lap_time": best_lap_time,
                        "lap_start_time": None,
                        "lap_end_time": None,
                        "sector_samples": [],
                    }

                    previous_pct = pct
                    previous_session_time = (
                        session_time
                    )
                    previous_last_lap_time = (
                        last_lap_time
                    )
                    previous_best_lap_time = (
                        best_lap_time
                    )
                    continue

                # ------------------------------------------------------
                # CAMBIO DE LAP
                # ------------------------------------------------------
                if lap != current_lap_number:
                    assert current is not None
                    assert previous_pct is not None
                    assert previous_session_time is not None

                    # Este cruce 1 -> 0 es simultáneamente:
                    #   - final de la vuelta anterior
                    #   - inicio de la vuelta actual
                    meta_time = calculate_meta_crossing(
                        previous_pct,
                        pct,
                        previous_session_time,
                        session_time,
                    )

                    current["end_record"] = (
                        record_index - 1
                    )

                    current["last_pct"] = previous_pct

                    current["samples"] = (
                        current["end_record"]
                        - current["start_record"]
                        + 1
                    )

                    # El tiempo nativo que iRacing publica como
                    # LapLastLapTime aparece en el nuevo Lap.
                    if (
                        last_lap_time is not None
                        and last_lap_time > 0
                    ):
                        current["lap_time"] = float(
                            last_lap_time
                        )

                    current["best_lap_time"] = (
                        previous_best_lap_time
                        if previous_best_lap_time is not None
                        else current["best_lap_time"]
                    )

                    current["lap_end_time"] = (
                        meta_time
                    )

                    # La vuelta 0 / instalación inicial no tiene
                    # un inicio de meta válido.
                    if (
                        current["lap_start_time"]
                        is not None
                    ):
                        current["complete"] = (
                            current["first_pct"] < 0.01
                            and current["last_pct"] > 0.99
                            and current["lap_end_time"]
                            is not None
                        )

                        if current["complete"]:
                            current["lap_time_session"] = (
                                current["lap_end_time"]
                                - current["lap_start_time"]
                            )

                            current["sectors"] = (
                                reconstruct_sectors(
                                    current[
                                        "sector_samples"
                                    ],
                                    sector_boundaries,
                                    current[
                                        "lap_start_time"
                                    ],
                                    current[
                                        "lap_end_time"
                                    ],
                                )
                            )
                        else:
                            current["sectors"] = None
                    else:
                        current["complete"] = False
                        current["sectors"] = None

                    laps.append(current)

                    # --------------------------------------------------
                    # NUEVA VUELTA
                    # --------------------------------------------------
                    current_lap_number = lap

                    current = {
                        "lap": lap,
                        "completed_start": completed,
                        "start_record": record_index,
                        "end_record": record_index,
                        "first_pct": pct,
                        "last_pct": pct,
                        "samples": 1,
                        "lap_time": None,
                        "best_lap_time": best_lap_time,
                        "lap_start_time": meta_time,
                        "lap_end_time": None,
                        "sector_samples": [],
                    }

                    # No incluimos el primer punto del nuevo lap
                    # como cruce de sector porque ya estamos justo
                    # después de meta.

                else:
                    assert current is not None

                    current["end_record"] = record_index
                    current["last_pct"] = pct
                    current["samples"] += 1
                    current["best_lap_time"] = (
                        best_lap_time
                    )

                    if current["lap_start_time"] is not None:
                        current[
                            "sector_samples"
                        ].append(
                            (
                                pct,
                                session_time,
                            )
                        )

                previous_pct = pct
                previous_session_time = (
                    session_time
                )
                previous_last_lap_time = (
                    last_lap_time
                )
                previous_best_lap_time = (
                    best_lap_time
                )

            # ----------------------------------------------------------
            # ÚLTIMA VUELTA INCOMPLETA
            # ----------------------------------------------------------
            if current is not None:
                current["end_record"] = (
                    record_count - 1
                )

                current["last_pct"] = (
                    previous_pct
                    if previous_pct is not None
                    else current["last_pct"]
                )

                current["samples"] = (
                    current["end_record"]
                    - current["start_record"]
                    + 1
                )

                current["complete"] = False
                current["sectors"] = None

                laps.append(current)

            return {
                "header": header,
                "disk": disk,
                "first_buffer_offset": (
                    first_buffer_offset
                ),
                "sector_boundaries": (
                    sector_boundaries
                ),
                "laps": laps,
            }


def write_csv(
    csv_path: Path,
    laps: list[dict[str, Any]],
) -> None:
    max_sectors = max(
        (
            len(
                lap["sectors"]["sector_times"]
            )
            for lap in laps
            if lap.get("sectors")
        ),
        default=0,
    )

    fields = [
        "Lap",
        "LapTimeNative",
        "LapTimeNativeSeconds",
        "LapTimeSession",
        "LapTimeSessionSeconds",
        "BestLapTime",
        "Samples",
        "StartRecord",
        "EndRecord",
        "Complete",
    ]

    for sector_number in range(
        1,
        max_sectors + 1,
    ):
        fields.append(
            f"S{sector_number}"
        )

    with csv_path.open(
        "w",
        newline="",
        encoding="utf-8-sig",
    ) as f:
        writer = csv.DictWriter(
            f,
            fieldnames=fields,
        )

        writer.writeheader()

        for lap in laps:
            row = {
                "Lap": lap["lap"],
                "LapTimeNative": format_laptime(
                    lap["lap_time"]
                ),
                "LapTimeNativeSeconds": (
                    f"{lap['lap_time']:.6f}"
                    if lap["lap_time"] is not None
                    else ""
                ),
                "LapTimeSession": format_laptime(
                    lap.get(
                        "lap_time_session"
                    )
                ),
                "LapTimeSessionSeconds": (
                    f"{lap['lap_time_session']:.6f}"
                    if lap.get(
                        "lap_time_session"
                    ) is not None
                    else ""
                ),
                "BestLapTime": format_laptime(
                    lap["best_lap_time"]
                ),
                "Samples": lap["samples"],
                "StartRecord": lap[
                    "start_record"
                ],
                "EndRecord": lap[
                    "end_record"
                ],
                "Complete": (
                    "YES"
                    if lap["complete"]
                    else "NO"
                ),
            }

            sectors = lap.get(
                "sectors"
            )

            if sectors:
                for index, sector_time in enumerate(
                    sectors["sector_times"],
                    start=1,
                ):
                    row[
                        f"S{index}"
                    ] = (
                        f"{sector_time:.6f}"
                        if sector_time is not None
                        else ""
                    )

            writer.writerow(row)


def print_report(
    path: Path,
    result: dict[str, Any],
    csv_path: Path,
) -> None:
    header = result["header"]
    disk = result["disk"]
    laps = result["laps"]
    boundaries = result[
        "sector_boundaries"
    ]

    print("=" * 112)
    print(
        " iRTM - IBT Probe | LAP + SECTOR EXTRACTION"
    )
    print("=" * 112)

    print(
        f"IBT          : {path.name}"
    )
    print(
        f"Records      : {disk['session_record_count']:,}"
    )
    print(
        f"Tick rate    : {header['tick_rate']} Hz"
    )
    print(
        f"Record size  : {header['buf_len']} bytes"
    )
    print()

    print("SectorStartPct RAW:")
    print(
        "  "
        + " | ".join(
            f"S{i + 1}={pct:.6f}"
            for i, pct in enumerate(
                boundaries[:-1]
            )
        )
        + f" | FINISH={boundaries[-1]:.6f}"
    )

    print()
    print(
        "Sectores: interpolación sobre SessionTime "
        "siguiendo la lógica del C#."
    )
    print()

    complete = [
        lap
        for lap in laps
        if lap["complete"]
    ]

    for lap in complete:
        print(
            f"Lap {lap['lap']:>2}"
            f" | Native: "
            f"{format_laptime(lap['lap_time'])}"
            f" | Session: "
            f"{format_laptime(lap['lap_time_session'])}"
        )

        sectors = lap["sectors"]

        for index, sector_time in enumerate(
            sectors["sector_times"],
            start=1,
        ):
            print(
                f"    S{index}: "
                f"{format_laptime(sector_time)}"
            )

        sector_sum = sum(
            sector
            for sector in sectors["sector_times"]
            if sector is not None
        )

        session_lap = lap[
            "lap_time_session"
        ]

        delta = (
            sector_sum - session_lap
            if session_lap is not None
            else None
        )

        print(
            f"    SUM: "
            f"{format_laptime(sector_sum)}"
            f" | Δ sectores: "
            f"{delta * 1000:+.3f} ms"
        )

        if lap["lap_time"] is not None:
            native_delta = (
                session_lap
                - lap["lap_time"]
            )

            print(
                f"    Δ Session vs Native: "
                f"{native_delta * 1000:+.3f} ms"
            )

        print()

    print("-" * 112)
    print(
        f"Vueltas completas detectadas: "
        f"{len(complete)}"
    )
    print(
        f"CSV generado: {csv_path}"
    )
    print()
    print(
        "NOTA: los tiempos de sector no son una variable "
        "nativa independiente."
    )
    print(
        "Se reconstruyen desde las fronteras de SplitTimeInfo "
        "y SessionTime del propio IBT."
    )
    print(
        "No se aplican filtros de iRTM."
    )


def main() -> int:
    parser = argparse.ArgumentParser(
        description=(
            "Extrae vueltas y reconstruye sectores "
            "desde un archivo IBT."
        )
    )

    parser.add_argument(
        "ibt",
        type=Path,
        help="Ruta al archivo .ibt",
    )

    args = parser.parse_args()

    if not args.ibt.exists():
        raise SystemExit(
            f"IBT no encontrado: {args.ibt}"
        )

    result = extract_laps(
        args.ibt
    )

    csv_path = args.ibt.with_suffix(
        ".laps-sectors.csv"
    )

    write_csv(
        csv_path,
        result["laps"],
    )

    print_report(
        args.ibt,
        result,
        csv_path,
    )

    return 0


if __name__ == "__main__":
    raise SystemExit(
        main()
    )
