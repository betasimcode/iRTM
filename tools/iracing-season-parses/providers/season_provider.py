from abc import ABC, abstractmethod

from models.parsed_series import ParsedSeries


class SeasonProvider(ABC):
    """
    Contrato para cualquier proveedor de temporadas.
    """

    @abstractmethod
    def get_series(self) -> list[ParsedSeries]:
        """
        Devuelve las series encontradas en la fuente de datos.
        """
        raise NotImplementedError