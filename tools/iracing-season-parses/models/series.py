from dataclasses import dataclass


@dataclass
class Series:
    """
    Modelo listo para registrar en la tabla 'series' de iRTM.
    """

    name: str

    season_year: int

    season_number: int

    status: str

    iracing_series_id: int