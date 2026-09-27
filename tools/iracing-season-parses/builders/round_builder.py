from datetime import datetime, timedelta

from extractors.race_info_extractor import RaceInfoExtractor
from extractors.track_extractor import TrackExtractor
from models.series_round import SeriesRound
from resolvers.track_resolver import TrackResolver


class RoundBuilder:
    """
    Construye un SeriesRound a partir de un ParsedRound.
    """

    def __init__(self):

        self.track_extractor = TrackExtractor()

        self.track_resolver = TrackResolver()

        self.race_info_extractor = RaceInfoExtractor()

    def build(self, parsed_round):

        alias = self.track_extractor.extract(
            parsed_round.lines
        )

        track_id = self.track_resolver.resolve(
            alias
        )

        race_type, race_length = (
            self.race_info_extractor.extract(
                parsed_round.lines
            )
        )

        week_start = datetime.strptime(
            parsed_round.week_start,
            "%Y-%m-%d"
        ).date()

        week_end = week_start + timedelta(days=7)

        return SeriesRound(

            iracing_series_id=parsed_round.iracing_series_id,

            week=parsed_round.week,

            week_start=week_start,

            week_end=week_end,

            circuit_id=track_id,

            race_type=race_type,

            race_length=race_length,

            track_alias=alias

        )
