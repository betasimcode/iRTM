import json
from pathlib import Path


class MissingTracksWriter:
    """
    Genera logs/missing_tracks.json con los
    circuitos pendientes de resolver.
    """

    def __init__(self):

        self.aliases = set()

    def add(self, alias: str | None):

        if alias:
            self.aliases.add(alias)

    def save(self) -> int:

        output = []

        for alias in sorted(self.aliases):

            output.append({

                "track_id": None,

                "aliases": [
                    alias
                ]

            })

        path = Path("logs/missing_tracks.json")

        path.parent.mkdir(
            parents=True,
            exist_ok=True
        )

        with open(
            path,
            "w",
            encoding="utf-8"
        ) as file:

            json.dump(
                output,
                file,
                indent=4,
                ensure_ascii=False
            )

        return len(output)