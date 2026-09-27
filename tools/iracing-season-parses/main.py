import argparse

from commands.import_series import ImportSeriesCommand
from commands.import_rounds import ImportRoundsCommand
from commands.build_tracks_config import BuildTracksConfigCommand


def main():

    parser = argparse.ArgumentParser(
        description="iRacing Season Parser"
    )

    parser.add_argument(
        "--year",
        type=int,
        required=True,
        help="Año de la temporada"
    )

    parser.add_argument(
        "--season",
        type=int,
        required=True,
        help="Número de temporada"
    )

    args = parser.parse_args()

    year = args.year
    season = args.season

    print("=" * 60)
    print("iRACING SEASON PARSER")
    print("=" * 60)
    print()
    print(f"Temporada solicitada : {year} S{season}")
    print()

    ImportSeriesCommand().run(
        year,
        season
    )

    ImportRoundsCommand().run(
        year,
        season
    )


if __name__ == "__main__":
    main()