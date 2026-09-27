import requests

from config.irtm import API_URL, API_TOKEN, TIMEOUT
from models.series import Series


class IRTMClient:
    """
    Cliente HTTP para comunicarse con la API de iRTM.
    """

    def __init__(self):

        self.base_url = API_URL

        self.timeout = TIMEOUT

        self.headers = {
            "Accept": "application/json",
            "Authorization": f"Bearer {API_TOKEN}"
        }

    def get(self, endpoint: str):

        return requests.get(
            f"{self.base_url}/{endpoint}",
            headers=self.headers,
            timeout=self.timeout
        )

    def post(self, endpoint: str, payload: dict):

        return requests.post(
            f"{self.base_url}/{endpoint}",
            json=payload,
            headers=self.headers,
            timeout=self.timeout
        )

    def sync_series(self, series: Series) -> dict:

        payload = {

            "season_year": series.season_year,

            "season_number": series.season_number,

            "status": series.status,

            "iracing_series_id": series.iracing_series_id

        }

        response = self.post(
            "sync/series",
            payload
        )

        if response.ok:
            return response.json()

        self._print_error(response)

        return {}

    def sync_rounds(
        self,
        rounds: list,
        year: int,
        number: int
    ) -> dict:

        if not rounds:
            return {}

        payload = {

            "iracing_series_id": rounds[0].iracing_series_id,

            "season_year": year,

            "season_number": number,

            "rounds": [

                {

                    "week": round_item.week,

                    "week_start": round_item.week_start.isoformat(),

                    "week_end": round_item.week_end.isoformat(),

                    "circuit_id": round_item.circuit_id,

                    "race_type": round_item.race_type,

                    "race_length": round_item.race_length

                }

                for round_item in rounds

            ]

        }

        response = self.post(
            "sync/rounds",
            payload
        )

        if response.ok:
            return response.json()

        self._print_error(response)

        return {}

    def _print_error(self, response):

        print()
        print("=" * 60)
        print("ERROR API")
        print("=" * 60)
        print(f"HTTP {response.status_code}")
        print("Respuesta:")
        print(response.text)
        print("=" * 60)
        print()
