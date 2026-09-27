import re

from models.parsed_round import ParsedRound


class ParsedRoundBuilder:
    """
    Extrae las weeks contenidas en una ParsedSeries.
    """

    WEEK_PATTERN = re.compile(
        r"^Week\s+(\d+)\s+\((\d{4}-\d{2}-\d{2})\)"
    )

    def build(self, parsed_series):

        rounds = []

        current_lines = []

        current_week = None
        current_start = None

        for line in parsed_series.lines:

            match = self.WEEK_PATTERN.match(line)

            if match:

                if current_week is not None:

                    rounds.append(

                        ParsedRound(

                            iracing_series_id=parsed_series.iracing_series_id,

                            week=current_week,

                            week_start=current_start,

                            lines=current_lines.copy()

                        )

                    )

                current_week = int(match.group(1))
                current_start = match.group(2)

                current_lines = [line]

            elif current_week is not None:

                current_lines.append(line)

        if current_week is not None:

            rounds.append(

                ParsedRound(

                    iracing_series_id=parsed_series.iracing_series_id,

                    week=current_week,

                    week_start=current_start,

                    lines=current_lines.copy()

                )

            )

        return rounds