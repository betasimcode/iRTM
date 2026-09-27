import re


class TrackExtractor:
    """
    Extrae el alias del circuito exactamente como aparece
    en el PDF.

    Reconstruye automáticamente los nombres partidos
    entre dos líneas.
    """

    WEATHER_PATTERN = re.compile(
        r"\d+°F/\d+°C"
    )

    HEADER_PATTERN = re.compile(
        r"^Week\s+\d+\s+\(\d{4}-\d{2}-\d{2}\)\s+"
    )

    def extract(self, lines: list[str]) -> str:

        if not lines:
            return ""

        line = self.HEADER_PATTERN.sub("", lines[0])

        weather = self.WEATHER_PATTERN.search(line)

        if weather:
            line = line[:weather.start()]

        alias = line.strip()

        #
        # El PDF puede partir el nombre del circuito:
        #
        # Autodromo Internazionale Enzo e Dino Ferrari -
        # Grand Prix
        #
        if alias.endswith("-") and len(lines) > 1:

            second = lines[1]

            # Todo lo que venga después del tipo de salida
            # pertenece a la siguiente columna del PDF.
            second = re.split(
                r"\b(Standing start|Rolling start)\b",
                second,
                maxsplit=1
            )[0].strip()

            alias = f"{alias} {second}"


        return alias.strip()