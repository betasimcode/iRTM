from builders.parsed_round_builder import ParsedRoundBuilder
from builders.round_builder import RoundBuilder
from models.season import Season
from providers.pdf_provider import PdfSeasonProvider
from repositories.rounds_repository import RoundsRepository
from writers.missing_tracks_writer import MissingTracksWriter


class ImportRoundsCommand:
    """
    Construye y sincroniza el calendario de las series.
    """

    def run(self, year: int, number: int):

        season = Season(
            year=year,
            number=number
        )

        print("=" * 60)
        print("IMPORT ROUNDS")
        print("=" * 60)
        print()

        provider = PdfSeasonProvider(season)

        parsed_series = provider.get_series()

        parsed_round_builder = ParsedRoundBuilder()

        round_builder = RoundBuilder()

        repository = RoundsRepository()

        missing_writer = MissingTracksWriter()

        total_series = 0
        total_rounds = 0
        total_missing = 0

        for series in parsed_series:

            parsed_rounds = parsed_round_builder.build(series)

            series_rounds = []

            missing_tracks = 0

            for parsed_round in parsed_rounds:

                series_round = round_builder.build(parsed_round)

                if series_round.circuit_id is None:

                    print(
                        f"   ⚠ Week {series_round.week} omitida "
                        f"({series_round.track_alias})"
                    )

                    missing_writer.add(
                        series_round.track_alias
                    )

                    missing_tracks += 1

                    total_missing += 1

                    continue

                series_rounds.append(
                    series_round
                )

            if missing_tracks:

                print(
                    f"   ⚠ {missing_tracks} circuitos pendientes de resolver"
                )

            if series_rounds:

                repository.save(
                    series_rounds,
                    year,
                    number
                )

                print(
                    f"✓ {series.header} ({len(series_rounds)} rounds)"
                )

                total_series += 1

                total_rounds += len(series_rounds)

            else:

                print(
                    f"✗ {series.header} (ningún round válido)"
                )

        missing_count = missing_writer.save()

        print()
        print("=" * 60)
        print("IMPORT SUMMARY")
        print("=" * 60)

        print(
            f"Series sincronizadas : {total_series}"
        )

        print(
            f"Rounds sincronizadas : {total_rounds}"
        )

        print(
            f"Rounds omitidas      : {total_missing}"
        )

        print(
            f"Circuitos pendientes : {missing_count}"
        )

        if missing_count:

            print()

            print(
                "Archivo generado:"
            )

            print(
                "logs/missing_tracks.json"
            )

        print()
        print("Proceso finalizado.")
