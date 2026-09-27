from repositories.irtm_client import IRTMClient


class RoundsRepository:
    """
    Sincroniza el calendario completo de una serie.
    """

    def __init__(self):

        self.client = IRTMClient()

    def save(self, rounds, year, number):

        if not rounds:
            return

        result = self.client.sync_rounds(
            rounds,
            year,
            number
        )

        if result.get("success"):

            print(
                f"   ✓ API OK ({len(rounds)} rounds)"
            )

        else:

            print(
                "   ✗ Error sincronizando rounds"
            )
