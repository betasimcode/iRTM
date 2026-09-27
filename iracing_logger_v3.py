# ============================================
# IRACING TEAM MANAGER - STABLE TRAY VERSION
# ============================================

import irsdk
import time
import requests
from datetime import datetime, UTC
import os
import sys
import logging
from logging.handlers import RotatingFileHandler
import threading
import pystray
from PIL import Image
from plyer import notification
# ============================================
# APP INFO
# ============================================

APP_NAME = "iRacing Team Manager"
APP_VERSION = "0.1.0"

# ============================================
# GLOBAL STATE
# ============================================

status_text = "Iniciando..."
stints_sent = 0
tray_icon = None
running = True

current_car_name = "—"
current_track_name = "—"
current_tank_capacity = None

update_available = False
update_download_url = None
latest_version_available = None

BASE_URL = "http://localhost:8000/api"
API_URL = f"{BASE_URL}/telemetry/stint-batch"
API_TOKEN = None
HEADERS = None

MIN_VALID_LAPS = 2

# ============================================
# LOGGER CONFIG
# ============================================

logger = logging.getLogger("iRacingLogger")
logger.setLevel(logging.INFO)

handler = RotatingFileHandler(
    "iracing_logger.log",
    maxBytes=5_000_000,
    backupCount=3
)

formatter = logging.Formatter(
    "%(asctime)s | %(levelname)s | %(message)s"
)

handler.setFormatter(formatter)
logger.addHandler(handler)
logger.propagate = False

#============================================
# UPDATER CHEK
#============================================
def check_for_updates():
    global update_available, update_download_url, latest_version_available

    try:
        response = requests.get(f"{BASE_URL}/logger/version", timeout=5)

        if response.status_code != 200:
            return

        data = response.json()
        latest_version = data.get("version")
        download_url = data.get("download_url")

        if not latest_version or not download_url:
            return

        if latest_version != APP_VERSION:
            update_available = True
            update_download_url = download_url
            latest_version_available = latest_version

            logger.info(f"Nueva versión disponible: {latest_version}")

            notification.notify(
                title=APP_NAME,
                message=f"Nueva versión disponible: v{latest_version}",
                timeout=5
            )

            update_tray_tooltip()

    except Exception:
        logger.exception("Error comprobando actualizaciones")

def update_now(icon, item):
    global update_download_url

    if not update_download_url:
        return

    download_and_replace(update_download_url)

#============================================
# UPDATER DOWNLOAD
#============================================

def download_and_replace(url):
    try:
        exe_path = sys.executable
        temp_path = exe_path + ".new"

        logger.info("Descargando nueva versión...")

        r = requests.get(url, stream=True, timeout=30)

        with open(temp_path, "wb") as f:
            for chunk in r.iter_content(chunk_size=8192):
                f.write(chunk)

        logger.info("Reemplazando ejecutable...")

        # Crear script temporal para reemplazo
        updater_script = exe_path + "_updater.bat"

        with open(updater_script, "w") as f:
            f.write(f"""
@echo off
timeout /t 2 /nobreak >nul
del "{exe_path}"
rename "{temp_path}" "{os.path.basename(exe_path)}"
start "" "{exe_path}"
del "%~f0"
""")

        os.startfile(updater_script)
        os._exit(0)

    except Exception:
        logger.exception("Error durante actualización")

# ============================================
# TRAY TOOLTIP UPDATE
# ============================================

def update_tray_tooltip():
    global tray_icon
    global current_car_name
    global current_track_name
    global current_tank_capacity

    if tray_icon:

        update_line = ""
        if update_available:
            update_line = f"\nNueva versión: v{latest_version_available}"

        tank_text = (
            f"{current_tank_capacity} L"
            if current_tank_capacity
            else "Detectando..."
        )

        tray_icon.title = (
            f"{APP_NAME} v{APP_VERSION}\n"
            f"Estado: {status_text}\n"
            f"Stints: {stints_sent}\n"
            f"{current_car_name} @ {current_track_name}\n"
            f"Tank: {tank_text}"
            f"{update_line}"
        )

# ============================================
# TOKEN MANAGEMENT
# ============================================

def fetch_api_token(iracing_user_id):
    try:
        response = requests.post(
            f"{BASE_URL}/logger/token",
            json={"iracing_user_id": iracing_user_id},
            timeout=5
        )

        if response.status_code == 200:
            logger.info("API token obtenido correctamente.")
            return response.json().get("api_token")

        logger.error(f"Error obteniendo token: {response.text}")
        return None

    except Exception:
        logger.exception("Excepción llamando a API de token:")
        return None


def build_headers(token):
    return {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }


def send_telemetry(payload, iracing_user_id):
    global API_TOKEN, HEADERS, stints_sent

    try:
        response = requests.post(API_URL, json=payload, headers=HEADERS, timeout=15)

        if response.status_code == 401:
            logger.warning("Token inválido. Renovando...")
            API_TOKEN = fetch_api_token(iracing_user_id)
            if not API_TOKEN:
                logger.critical("No se pudo renovar token.")
                return False

            HEADERS = build_headers(API_TOKEN)
            response = requests.post(API_URL, json=payload, headers=HEADERS, timeout=15)

        if response.status_code == 200:
            stints_sent += 1
            update_tray_tooltip()
            logger.info("Stint enviado correctamente.")
            return True

        logger.error(f"Error enviando stint: {response.text}")
        return False

    except Exception:
        logger.exception("Error enviando telemetría:")
        return False

# ============================================
# HELPERS
# ============================================

def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")):
            return None
        return v
    except:
        return None


def clean_temp(value):
    try:
        if isinstance(value, str):
            return float(value.replace(" C","").strip())
        return float(value)
    except:
        return None


def current_driver_data(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        driver = d['Drivers'][idx]
        return {
            "iracing_user_id": driver.get("UserID"),
            "name": driver.get("UserName")
        }
    except:
        return None


def current_track(ir):
    try:
        return f"{ir['WeekendInfo']['TrackDisplayName']} - {ir['WeekendInfo']['TrackConfigName']}"
    except:
        return "Unknown"


def current_car(ir):
    try:
        car_idx = ir['DriverCarIdx']

        if car_idx is None:
            return None

        drivers = ir['DriverInfo']['Drivers']

        if not drivers or car_idx >= len(drivers):
            return None

        driver = drivers[car_idx]

        return {
            "car_id": ir['CarIdxCarID'][car_idx],
            "car_path": driver.get('CarPath'),
            "car_name": driver.get('CarScreenName'),
            "car_class_id": driver.get('CarClassID'),
            "car_class_short_name": driver.get('CarClassShortName')
        }

    except Exception as e:
        logger.error(f"Error obteniendo coche actual: {e}")
        return None

def get_tyre_compound(ir):
    try:
        driver_info = ir['DriverInfo']
        idx = driver_info['DriverCarIdx']
        compounds = ir['CarIdxTireCompound']
        return compounds[idx] if compounds else None
    except:
        return None

def capture_final_tyre_wear(ir, timeout=8):

    start_time = time.time()
    last_values = None
    stable_counter = 0

    while time.time() - start_time < timeout:

        ir.freeze_var_buffer_latest()
        logger.info(f"Connected: {ir.is_connected}")
        logger.info(f"WeekendInfo: {ir['WeekendInfo']}")
        logger.info(f"DriverCarIdx: {ir['DriverCarIdx']}")

        current = {
            "fl": clean_number(ir['LFwearM']) if 'LFwearM' in ir.var_headers_names else None,
            "fr": clean_number(ir['RFwearM']) if 'RFwearM' in ir.var_headers_names else None,
            "rl": clean_number(ir['LRwearM']) if 'LRwearM' in ir.var_headers_names else None,
            "rr": clean_number(ir['RRwearM']) if 'RRwearM' in ir.var_headers_names else None,
        }

        if last_values is None:
            last_values = current
            time.sleep(0.5)
            continue

        if current == last_values:
            stable_counter += 1
        else:
            stable_counter = 0
            last_values = current

        if stable_counter >= 3:
            return current

        time.sleep(0.5)

    return last_values

def current_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"


def get_track_length_km(ir):
    try:
        raw = ir['WeekendInfo']['TrackLength']
        if not raw:
            return None
        raw = raw.strip().lower()
        if "km" in raw:
            return float(raw.replace("km","").strip())
        if "mi" in raw:
            return float(raw.replace("mi","").strip()) * 1.60934
    except:
        return None
    return None

# ============================================
# MAIN LOGGER LOOP
# ============================================

def run_logger():
    global status_text, API_TOKEN, HEADERS, running
    global current_car_name, current_track_name, current_tank_capacity

    check_for_updates()
    ir = irsdk.IRSDK()

    while running:

        status_text = "Esperando iRacing"
        update_tray_tooltip()

        # ===== ESPERAR A QUE EL SDK ARRANQUE =====
        while not ir.startup() and running:
            time.sleep(1)

        if not running:
            break

        # ===== ESPERAR DRIVER DATA =====
        driver_data = None
        while not driver_data and running:
            driver_data = current_driver_data(ir)
            if not driver_data:
                time.sleep(1)

        if not running:
            break

        iracing_user_id = driver_data["iracing_user_id"]
        API_TOKEN = fetch_api_token(iracing_user_id)
        HEADERS = build_headers(API_TOKEN)

        logger.info("API token obtenido correctamente.")

        status_text = "Conectado"
        update_tray_tooltip()

        # ===== RESET ESTADO =====
        stint_active = False
        laps_buffer = []
        tyre_snapshots = []
        lap_start_fuel = 0
        last_lap_completed = None
        max_fuel_seen = 0

        last_subsession_id = None
        last_car_id = None

        # ===== LOOP PRINCIPAL =====
        while running:

            if not ir.is_connected:
                time.sleep(1)
                continue

            try:
                ir.freeze_var_buffer_latest()

                car_idx = ir['DriverCarIdx']
                drivers = ir['DriverInfo']['Drivers']

                if car_idx is None or not drivers or car_idx >= len(drivers):
                    time.sleep(0.1)
                    continue

                current_car_id = ir['CarIdxCarID'][car_idx]
                current_subsession = ir['WeekendInfo']['SubSessionID']

                # ===== PRIMER TICK DE SESIÓN =====
                if last_subsession_id is None:
                    last_subsession_id = current_subsession
                    last_car_id = current_car_id

                    car_info = current_car(ir)
                    if car_info:
                        current_car_name = car_info.get("car_name", "—")

                    current_track_name = current_track(ir)
                    current_tank_capacity = None
                    update_tray_tooltip()

                # ===== CAMBIO DE SESIÓN O COCHE =====
                elif (
                    current_subsession != last_subsession_id
                    or current_car_id != last_car_id
                ):

                    logger.info("Cambio de sesión o coche detectado.")

                    # Cerrar stint activo si procede
                    if stint_active and len(laps_buffer) >= MIN_VALID_LAPS:

                        payload = {
                            "driver": driver_data,
                            "car": current_car(ir),
                            "track": stint_track,
                            "session_type": stint_session_type,
                            "track_length": get_track_length_km(ir),
                            "tyre_snapshots": tyre_snapshots,
                            "tank_capacity_detected": round(max_fuel_seen, 2),
                            "app_version": APP_VERSION,
                            "laps": laps_buffer
                        }

                        send_telemetry(payload, iracing_user_id)

                    # Reset
                    stint_active = False
                    laps_buffer = []
                    tyre_snapshots = []
                    max_fuel_seen = 0

                    last_subsession_id = current_subsession
                    last_car_id = current_car_id

                    car_info = current_car(ir)
                    if car_info:
                        current_car_name = car_info.get("car_name", "—")

                    current_track_name = current_track(ir)
                    current_tank_capacity = None
                    update_tray_tooltip()

                # ===== VARIABLES LIVE =====
                is_on_track = ir['IsOnTrack']
                speed = ir['Speed'] * 3.6
                fuel = ir['FuelLevel']
                lap_completed = ir['LapCompleted']
                is_in_pit_lane = ir['OnPitRoad']

                max_fuel_seen = max(max_fuel_seen, fuel)

                if max_fuel_seen > 0:
                    current_tank_capacity = round(max_fuel_seen, 1)

                # ===== INICIO STINT =====
                if not stint_active and is_on_track and speed > 10:
                    logger.info("Inicio de stint detectado")

                    stint_active = True
                    laps_buffer = []
                    tyre_snapshots = []
                    lap_start_fuel = fuel
                    last_lap_completed = lap_completed
                    stint_track = current_track(ir)
                    stint_session_type = current_session(ir)
                    max_fuel_seen = fuel

                # ===== NUEVA VUELTA =====
                if (
                    stint_active
                    and is_on_track
                    and lap_completed
                    and last_lap_completed
                    and lap_completed > last_lap_completed
                ):

                    new_lap_time = clean_number(ir['LapLastLapTime'])

                    if not new_lap_time or new_lap_time > 300:
                        last_lap_completed = lap_completed
                        continue

                    fuel_used = lap_start_fuel - fuel
                    if fuel > lap_start_fuel:
                        lap_start_fuel = fuel
                        fuel_used = 0

                    laps_buffer.append({
                        "lap": len(laps_buffer) + 1,
                        "lap_time": new_lap_time,
                        "fuel": clean_number(fuel),
                        "fuel_used": clean_number(fuel_used),
                        "is_pit_lap": is_in_pit_lane,
                        "track_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackSurfaceTemp'])),
                        "air_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackAirTemp'])),
                        "timestamp": datetime.now(UTC).isoformat()
                    })

                    lap_start_fuel = fuel
                    last_lap_completed = lap_completed

                # ===== FIN STINT =====
                if stint_active and not is_on_track:
                    logger.info("Fin de stint detectado")

                    if len(laps_buffer) >= MIN_VALID_LAPS:

                        payload = {
                            "driver": driver_data,
                            "car": current_car(ir),
                            "track": stint_track,
                            "session_type": stint_session_type,
                            "track_length": get_track_length_km(ir),
                            "tyre_snapshots": tyre_snapshots,
                            "tank_capacity_detected": round(max_fuel_seen, 2),
                            "app_version": APP_VERSION,
                            "laps": laps_buffer
                        }

                        send_telemetry(payload, iracing_user_id)

                    stint_active = False
                    laps_buffer = []
                    tyre_snapshots = []
                    max_fuel_seen = 0

                time.sleep(0.05)

            except Exception as e:
                logger.error(f"ERROR LOOP: {e}")
                time.sleep(1)

# ============================================
# SYSTEM TRAY
# ============================================

def create_image():
    base_path = (
        sys._MEIPASS
        if getattr(sys, 'frozen', False)
        else os.path.dirname(os.path.abspath(__file__))
    )

    icon_path = os.path.join(base_path, "icon.ico")

    if os.path.exists(icon_path):
        return Image.open(icon_path)

    return Image.new('RGB', (64, 64), color=(255, 0, 0))


def on_exit(icon, item):
    global running
    running = False
    icon.stop()
    os._exit(0)


def on_restart(icon, item):
    icon.stop()
    os.execv(sys.executable, [sys.executable] + sys.argv)


def open_logs(icon, item):
    os.startfile(os.path.abspath("iracing_logger.log"))


# ============================================
# START
# ============================================

if __name__ == "__main__":

    image = create_image()

    menu = pystray.Menu(
    pystray.MenuItem(
        "Actualizar ahora",
        update_now,
        visible=lambda item: update_available
    ),
    pystray.MenuItem("Reiniciar", on_restart),
    pystray.MenuItem("Abrir logs", open_logs),
    pystray.Menu.SEPARATOR,
    pystray.MenuItem("Salir", on_exit)
)

    tray_icon = pystray.Icon(
        "iracing_team_manager",
        image,
        f"{APP_NAME} v{APP_VERSION}",
        menu
    )

    logger_thread = threading.Thread(target=run_logger, daemon=True)
    logger_thread.start()

    tray_icon.run()
