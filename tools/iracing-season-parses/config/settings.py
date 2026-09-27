from pathlib import Path


# Directorio raíz del proyecto
BASE_DIR = Path(__file__).resolve().parent.parent

# Directorio de configuración
CONFIG_DIR = BASE_DIR / "config"

# Directorio de muestras
SAMPLES_DIR = BASE_DIR / "samples"

# PDF de la temporada
SEASON_PDF = SAMPLES_DIR / "2026_4.pdf"

# Catálogo de series
SERIES_FILE = CONFIG_DIR / "series.json"

# Catálogo de circuitos
TRACKS_FILE = CONFIG_DIR / "tracks.json"