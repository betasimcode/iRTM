import re


class RaceInfoExtractor:
    """
    Extrae el tipo y la duración de la carrera
    desde la primera línea del bloque Week.
    """

    PATTERN = re.compile(
        r"(\d+)\s+(mins|laps)\s*$",
        re.IGNORECASE
    )

    def extract(self, lines: list[str]) -> tuple[str, int]:

        if not lines:
            return "", 0

        line = lines[0].strip()

        match = self.PATTERN.search(line)

        if not match:
            return "", 0

        value = int(match.group(1))

        unit = match.group(2).lower()

        if unit == "mins":
            return "time", value

        return "laps", value