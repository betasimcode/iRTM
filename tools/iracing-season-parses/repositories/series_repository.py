from models.series import Series
from repositories.irtm_client import IRTMClient


class SeriesRepository:
    """
    Responsable de sincronizar una serie con iRTM.
    """

    def __init__(self):

        self.client = IRTMClient()

    def save(self, series: Series) -> int:

        result = self.client.sync_series(series)

        print(
            f"✓ {series.name} → Series ID {result['series_id']}"
        )

        return result["series_id"]