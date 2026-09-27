import json
from pathlib import Path

from builders.parsed_round_builder import ParsedRoundBuilder
from extractors.track_extractor import TrackExtractor
from models.season import Season
from providers.pdf_provider import PdfSeasonProvider


class BuildTracksConfigCommand:
    """
    Genera tracks.generated.json a partir de los circuitos
    encontrados en el PDF de la temporada.

    Los track_id se resuelven contra el catálogo maestro
    config/tracks.json.
    """

    OUTPUT = Path("config/tracks.generated.json")
    MASTER = Path("config/tracks.json")

    def run(self, year: int, number: int):

        season = Season(
            year=year,
            number=number
        )

        provider = PdfSeasonProvider(season)

        parsed_series = provider.get_series()

        builder = ParsedRoundBuilder()

        extractor = TrackExtractor()

        # --------------------------------------------------
        # Cargar catálogo maestro
        # --------------------------------------------------

        with self.MASTER.open(
            "r",
            encoding="utf-8"
        ) as file:

            master_tracks = json.load(file)

        # --------------------------------------------------
        # Crear índice alias -> track_id
        # --------------------------------------------------

        track_index = {}

        for track in master_tracks:

            track_id = track.get("track_id")

            for alias in track.get("aliases", []):

                track_index[alias] = track_id

        # --------------------------------------------------
        # Extraer aliases utilizados en la temporada
        # --------------------------------------------------

        aliases = set()

        for series in parsed_series:

            rounds = builder.build(series)

            for round_item in rounds:

                alias = extractor.extract(
                    round_item.lines
                )

                if alias:

                    aliases.add(alias)

        # --------------------------------------------------
        # Construir configuración de temporada
        # --------------------------------------------------

        data = []

        resolved = 0
        unresolved = 0

        for alias in sorted(aliases):

            track_id = track_index.get(alias)

            if track_id is not None:

                resolved += 1

            else:

                unresolved += 1

            data.append({

                "track_id": track_id,

                "aliases": [
                    alias
                ]

            })

        # --------------------------------------------------
        # Guardar resultado
        # --------------------------------------------------

        self.OUTPUT.parent.mkdir(
            parents=True,
            exist_ok=True
        )

        self.OUTPUT.write_text(

            json.dumps(
                data,
                indent=4,
                ensure_ascii=False
            ),

            encoding="utf-8"

        )

        print()
        print("=" * 60)
        print("TRACK CONFIG GENERATED")
        print("=" * 60)
        print()
        print(
            f"Aliases encontrados : {len(data)}"
        )
        print(
            f"Tracks resueltos    : {resolved}"
        )
        print(
            f"Tracks pendientes   : {unresolved}"
        )
        print(
            f"Archivo             : {self.OUTPUT}"
        )
        print()