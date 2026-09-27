from dataclasses import dataclass


@dataclass
class ParsedSeries:
    """
    Bloque de una serie localizado dentro del PDF.
    """

    iracing_series_id: int

    header: str

    page: int

    lines: list[str]