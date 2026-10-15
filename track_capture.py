#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
iRTM Track Boundary Capture
Captura una vuelta completa de un borde del circuito usando el SDK de iRacing.

Uso:
    py track_boundary_capture.py

Dependencia:
    py -m pip install irsdk

El script conserva las muestras GPS originales y sus metadatos. No suaviza ni
desplaza las coordenadas: el procesamiento geométrico se hace después.
"""

from __future__ import annotations

import csv
import json
import math
import sys
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

try:
    import irsdk
except ImportError:
    print("ERROR: falta el paquete 'irsdk'. Instálalo con: py -m pip install irsdk")
    raise SystemExit(1)


OUTPUT_ROOT = Path(__file__).resolve().parent / "captures"
POLL_INTERVAL_SECONDS = 1.0 / 60.0
WRAP_PREVIOUS_PCT = 0.85
WRAP_CURRENT_PCT = 0.15
MIN_SAMPLES = 100


def safe_float(value: Any) -> float | None:
    try:
        result = float(value)
        return result if math.isfinite(result) else None
    except (TypeError, ValueError):
        return None


def safe_int(value: Any) -> int | None:
    try:
        if value is None:
            return None
        return int(value)
    except (TypeError, ValueError):
        return None


def ir_value(ir: Any, key: str) -> Any:
    try:
        return ir[key]
    except Exception:
        return None


def read_weekend_info(ir: Any) -> dict[str, Any]:
    info = ir_value(ir, "WeekendInfo")
    if not isinstance(info, dict):
        return {}
    wanted = (
        "TrackID",
        "TrackName",
        "TrackConfigName",
        "TrackLength",
        "TrackDisplayName",
        "TrackDisplayShortName",
        "WeekendOptions",
    )
    result: dict[str, Any] = {}
    for key in wanted:
        value = info.get(key)
        if isinstance(value, (str, int, float, bool)) or value is None:
            result[key] = value
    return result


def choose_edge() -> str:
    while True:
        print("\n¿Qué borde vas a capturar?")
        print("  1. Derecho")
        print("  2. Izquierdo")
        answer = input("Selecciona 1 o 2: ").strip()
        if answer == "1":
            return "right"
        if answer == "2":
            return "left"
        print("Opción no válida.")


def wait_for_iracing(ir: Any) -> None:
    print("Esperando a que iRacing esté abierto y conectado...")
    while True:
        try:
            started = ir.startup()
            if started and ir.is_connected:
                return
        except KeyboardInterrupt:
            raise
        except Exception:
            pass
        time.sleep(2)


def read_sample(ir: Any, previous_session_time: float | None) -> dict[str, Any] | None:
    lat = safe_float(ir_value(ir, "Lat"))
    lon = safe_float(ir_value(ir, "Lon"))
    pct = safe_float(ir_value(ir, "LapDistPct"))
    session_time = safe_float(ir_value(ir, "SessionTime"))
    lap = safe_int(ir_value(ir, "Lap"))
    speed = safe_float(ir_value(ir, "Speed"))
    is_on_track = ir_value(ir, "IsOnTrack")

    # Lat/Lon son obligatorios. No se inventan coordenadas si la variable no existe.
    if lat is None or lon is None or pct is None:
        return None
    if not (-90 <= lat <= 90 and -180 <= lon <= 180):
        return None
    if not (0.0 <= pct <= 1.0):
        return None

    # SessionTime ayuda a evitar guardar repetidamente el mismo frame del SDK.
    if session_time is not None and previous_session_time is not None:
        if session_time <= previous_session_time:
            return None

    return {
        "timestamp_utc": datetime.now(timezone.utc).isoformat(timespec="milliseconds"),
        "session_time": session_time,
        "lap": lap,
        "lap_dist_pct": pct,
        "lat": lat,
        "lon": lon,
        "speed_m_s": speed,
        "is_on_track": bool(is_on_track) if is_on_track is not None else None,
    }


def detect_start_finish(previous_pct: float | None, current_pct: float) -> bool:
    return (
        previous_pct is not None
        and previous_pct >= WRAP_PREVIOUS_PCT
        and current_pct <= WRAP_CURRENT_PCT
    )


def unique_capture_dir(track_info: dict[str, Any], edge: str) -> Path:
    track_id = track_info.get("TrackID")
    track_name = str(track_info.get("TrackName") or "unknown_track")
    safe_name = "".join(c if c.isalnum() or c in "-_" else "_" for c in track_name).strip("_")
    safe_name = safe_name[:60] or "unknown_track"
    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    folder = OUTPUT_ROOT / f"{track_id or 'no_track_id'}_{safe_name}" / f"{edge}_{stamp}"
    folder.mkdir(parents=True, exist_ok=False)
    return folder


def main() -> int:
    print("=" * 62)
    print(" iRTM — Capturador independiente de límites GPS de circuito")
    print("=" * 62)
    print("Registra una vuelta completa. Mantén el centro del coche sobre")
    print("la línea de referencia del borde y conduce de forma constante.")
    print("Se guardarán las coordenadas originales sin suavizado.")
    edge = choose_edge()

    ir = irsdk.IRSDK()
    try:
        wait_for_iracing(ir)
        track_info = read_weekend_info(ir)

        print("\nConectado a iRacing.")
        print(f"Circuito: {track_info.get('TrackName') or 'desconocido'}")
        print(f"TrackID: {track_info.get('TrackID') or 'desconocido'}")
        print(f"Configuración: {track_info.get('TrackConfigName') or 'no indicada'}")

        missing = [name for name in ("Lat", "Lon", "LapDistPct") if ir_value(ir, name) is None]
        if missing:
            print("\nERROR: iRacing no está proporcionando estas variables: " + ", ".join(missing))
            print("Comprueba que estás en pista y que la sesión está inicializada.")
            return 2

        print("\nPREPARACIÓN")
        print("- Sitúa el coche cerca del punto de salida/meta.")
        print("- Espera a poder hacer una vuelta completa por el borde elegido.")
        input("- Cuando estés listo, pulsa ENTER para armar la captura...")

        print("\nCAPTURA ARMADA.")
        print("El script esperará a detectar el cruce de salida/meta para empezar.")
        print("Después registrará una vuelta completa y terminará automáticamente.")
        print("Pulsa Ctrl+C para cancelar sin completar la captura.\n")

        previous_pct: float | None = safe_float(ir_value(ir, "LapDistPct"))
        previous_session_time: float | None = None
        started_capture = False
        rows: list[dict[str, Any]] = []
        start_lap: int | None = None
        last_status = 0.0

        while True:
            if not ir.is_connected:
                print("\nSe perdió la conexión con iRacing. Captura cancelada.")
                return 3

            pct = safe_float(ir_value(ir, "LapDistPct"))
            if pct is None:
                time.sleep(POLL_INTERVAL_SECONDS)
                continue

            crossing = detect_start_finish(previous_pct, pct)
            sample = read_sample(ir, previous_session_time)

            if crossing and not started_capture:
                started_capture = True
                start_lap = safe_int(ir_value(ir, "Lap"))
                rows = []
                print("Cruce detectado: inicio de la vuelta capturada.")

            elif crossing and started_capture:
                # Guardar la muestra de cruce final para cerrar el 100% de la vuelta.
                if sample is not None:
                    rows.append(sample)
                print("Cruce detectado: vuelta completada.")
                break

            if started_capture and sample is not None:
                on_track = sample.get("is_on_track")
                # No descartamos automáticamente por IsOnTrack=False: algunas
                # transiciones del SDK pueden ser tardías; lo registramos para revisión.
                rows.append(sample)
                if sample.get("session_time") is not None:
                    previous_session_time = sample["session_time"]

                now = time.monotonic()
                if now - last_status >= 1.0:
                    pct_done = sample["lap_dist_pct"] * 100
                    print(
                        f"\rProgreso aproximado: {pct_done:6.2f}% | "
                        f"Muestras: {len(rows):5d} | "
                        f"Velocidad: {sample.get('speed_m_s') if sample.get('speed_m_s') is not None else 'N/D'} m/s",
                        end="",
                        flush=True,
                    )
                    last_status = now

            previous_pct = pct
            time.sleep(POLL_INTERVAL_SECONDS)

        print()
        if len(rows) < MIN_SAMPLES:
            print(f"ERROR: solo se capturaron {len(rows)} muestras; mínimo recomendado: {MIN_SAMPLES}.")
            print("No se guardará como captura válida.")
            return 4

        # Detectar metadatos útiles de la sesión sin hacerlos obligatorios.
        session_info = ir_value(ir, "SessionInfo")
        session_num = safe_int(ir_value(ir, "SessionNum"))
        capture_dir = unique_capture_dir(track_info, edge)
        csv_path = capture_dir / "boundary_samples.csv"
        json_path = capture_dir / "metadata.json"

        fieldnames = [
            "sample_index",
            "timestamp_utc",
            "session_time",
            "lap",
            "lap_dist_pct",
            "lat",
            "lon",
            "speed_m_s",
            "is_on_track",
        ]
        with csv_path.open("w", newline="", encoding="utf-8-sig") as handle:
            writer = csv.DictWriter(handle, fieldnames=fieldnames)
            writer.writeheader()
            for index, row in enumerate(rows):
                writer.writerow({"sample_index": index, **row})

        metadata = {
            "tool": "iRTM Track Boundary Capture",
            "schema_version": 1,
            "captured_at_utc": datetime.now(timezone.utc).isoformat(timespec="seconds"),
            "edge": edge,
            "start_lap": start_lap,
            "sample_count": len(rows),
            "capture_method": "live_irsdk",
            "coordinates": "WGS84 latitude/longitude as provided by iRacing",
            "processing": "raw; no smoothing, interpolation, or offset applied",
            "poll_interval_seconds": POLL_INTERVAL_SECONDS,
            "track": track_info,
            "session_num": session_num,
            "session_info_available": isinstance(session_info, dict),
            "csv_file": csv_path.name,
        }
        with json_path.open("w", encoding="utf-8") as handle:
            json.dump(metadata, handle, ensure_ascii=False, indent=2, default=str)

        print("\nCAPTURA GUARDADA")
        print(f"CSV:      {csv_path}")
        print(f"Metadatos:{json_path}")
        print(f"Muestras: {len(rows)}")
        print("\nEsta captura es la geometría GPS recorrida por el coche, no el borde")
        print("físico exacto. Mantén el centro del coche sobre la referencia elegida.")
        print("Repite el script para el otro borde, usando la misma configuración.")
        return 0

    except KeyboardInterrupt:
        print("\nCaptura cancelada por el usuario. No se guardó como captura completa.")
        return 130
    finally:
        try:
            ir.shutdown()
        except Exception:
            pass


if __name__ == "__main__":
    raise SystemExit(main())
