from config import TRACKS_FILE
from repositories.config_repository import ConfigRepository


class TrackResolver:
    """
    Resuelve un alias del PDF al track_id de iRTM.
    """

    def __init__(self):

        self.tracks = ConfigRepository().load(TRACKS_FILE)

        print(type(self.tracks))
        print(self.tracks)

    def resolve(self, alias: str):

        for track in self.tracks:

            if alias in track["aliases"]:

                return track["track_id"]

                

        return None