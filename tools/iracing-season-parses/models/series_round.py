from dataclasses import dataclass
from datetime import date


@dataclass
class SeriesRound:
    """
    Modelo listo para sincronizar con iRTM.
    """

    iracing_series_id: int

    week: int

    week_start: date

    week_end: date

    circuit_id: int | None

    race_type: str

    race_length: int

    # Solo para depuración y generación de missing_tracks.json
    # No se envía a Laravel.
    track_alias: str | None = None

    def __str__(self):

        return (
            f"SeriesRound("
            f"iracing_series_id={self.iracing_series_id}, "
            f"week={self.week}, "
            f"track_id={self.circuit_id}, "
            f"type='{self.race_type}', "
            f"length={self.race_length})"
        )