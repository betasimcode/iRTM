import json
from pathlib import Path


class ConfigRepository:
    """
    Repositorio para cargar archivos de configuración JSON.
    """

    @staticmethod
    def load(file_path: Path) -> dict:

        with open(file_path, "r", encoding="utf-8") as file:
            return json.load(file)