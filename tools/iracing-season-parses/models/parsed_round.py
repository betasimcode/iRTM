from dataclasses import dataclass


@dataclass
class ParsedRound:
    """
    Week obtenida del PDF.

    Representa el bloque completo de una week antes
    de resolver el circuito y construir SeriesRound.
    """

    iracing_series_id: int

    week: int

    week_start: str

    lines: list[str]