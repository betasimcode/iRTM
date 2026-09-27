from builders.series_builder import SeriesBuilder
from models.season import Season
from providers.pdf_provider import PdfSeasonProvider
from repositories.series_repository import SeriesRepository


class ImportSeriesCommand:
    """
    Constructor de la tabla 'series'.
    """

    def run(self, year: int, number: int):

        season = Season(
            year=year,
            number=number
        )

        print("=" * 60)
        print("IMPORT SERIES")
        print("=" * 60)
        print()

        provider = PdfSeasonProvider(season)

        parsed_series = provider.get_series()

        print(f"Temporada : {season.year} S{season.number}")
        print(f"Series encontradas : {len(parsed_series)}")
        print()

        builder = SeriesBuilder()
        repository = SeriesRepository()

        for parsed in parsed_series:

            series = builder.build(parsed, season)

            repository.save(series)

        print("Proceso finalizado.")