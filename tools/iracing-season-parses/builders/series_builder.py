from models.parsed_series import ParsedSeries
from models.season import Season
from models.series import Series


class SeriesBuilder:
    """
    Construye el registro de la tabla 'series'
    a partir de una ParsedSeries.
    """

    def build(
        self,
        parsed: ParsedSeries,
        season: Season
    ) -> Series:

        return Series(

            name=parsed.header,

            season_year=season.year,

            season_number=season.number,

            status="draft",

            iracing_series_id=parsed.iracing_series_id

        )