# ============================================
# IRACING MANAGER - CLEAN CORE VERSION
# ============================================

import irsdk
import time
import requests
from datetime import datetime, UTC

import logging
from logging.handlers import RotatingFileHandler

# ============================================
# CONFIG
# ============================================

API_TOKEN = "4|u4zmUulGNwgI1q6Lxp3d0AU8YCa4rtX0sfs8gwe93a0a7423"
API_URL = "http://localhost:8000/api/telemetry/stint-batch"

HEADERS = {
    "X-API-TOKEN": API_TOKEN,
    "Content-Type": "application/json"
}

MIN_VALID_LAPS = 2
last_subsession_id = None

# ============================================
# LOGGER CONFIG (solo archivo)
# ============================================

logger = logging.getLogger("iRacingLogger")
logger.setLevel(logging.INFO)

handler = RotatingFileHandler(
    "iracing_logger.log",
    maxBytes=5_000_000,   # 5 MB por archivo
    backupCount=3        # guarda 3 archivos antiguos
)

formatter = logging.Formatter(
    "%(asctime)s | %(levelname)s | %(message)s"
)

handler.setFormatter(formatter)
logger.addHandler(handler)

logger.propagate = False  # evita duplicados

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
        driver_info = ir['DriverInfo']
        idx = driver_info['DriverCarIdx']
        driver = driver_info['Drivers'][idx]

        return {
            "iracing_user_id": driver.get("UserID"),
            "name": driver.get("UserName")
        }
    except:
        return None

def current_track(ir):
    try:
        display = ir['WeekendInfo']['TrackDisplayName']
        config = ir['WeekendInfo']['TrackConfigName']
        return f"{display} - {config}"
    except:
        return "Unknown"

def current_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        return d['Drivers'][idx]['CarScreenName']
    except:
        return "Unknown"

def current_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"

def restart():
    import sys
    import os
    print("🔄 Reiniciando script por cambio de evento...\n")
    os.execv(sys.executable, [sys.executable] + sys.argv)

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


def send_stint(ir, laps):

    print("\n========== ENVIANDO STINT ==========")

    def current_driver_data(ir):
        try:
            driver_info = ir['DriverInfo']
            idx = driver_info['DriverCarIdx']
            driver = driver_info['Drivers'][idx]

            return {
                "iracing_user_id": driver.get("UserID"),
                "name": driver.get("UserName")
            }
        except Exception as e:
            print("Driver detection error:", e)
            return None


    payload = {
        "driver": current_driver_data(ir),
        "car": current_car(ir),
        "track": stint_track,
        "session_type": stint_session_type,
        "track_length": get_track_length_km(ir),
        "laps": laps
    }

    print("Driver payload:", payload["driver"])

    try:
        r = requests.post(API_URL, json=payload, headers=HEADERS, timeout=15)
        print("Status:", r.status_code)
        print("Body:", r.text)
        if r.status_code == 200:
            print("STINT OK\n")
        else:
            print("STINT ERROR\n")
    except Exception as e:
        print("ERROR:", str(e))

    logger.info(
        f"Stint enviado | Vueltas: {len(laps_buffer)} | "
        f"Circuito: {stint_track} | "
        f"Sesion: {stint_session_type} | "
        f"Status: {r.status_code}"
    )

# ============================================
# INIT IRACING
# ============================================

ir = irsdk.IRSDK()

print("Esperando iRacing...")
while not ir.startup():
    time.sleep(1)

track_name = current_track(ir)
track_length = get_track_length_km(ir)

print(f"Conectado a iRacing - {track_name} - {track_length} Km")
print(current_driver_data(ir))

# ============================================
# VARIABLES GLOBALES
# ============================================

stint_active = False
laps_buffer = []
lap_start_fuel = 0
last_lap_completed = None
last_recorded_lap_time = None
stint_track = None
stint_session_type = None
last_track_id = None
lap_was_in_pit = False

# ============================================
# RESTART REAL DEL SCRIPT
# ============================================

def restart(reason="reinicio"):
    import sys
    import os

    print(f"\n🔄 Reiniciando script ({reason})...\n")
    sys.stdout.flush()

    os.execv(sys.executable, [sys.executable] + sys.argv)


# ============================================
# LOOP PRINCIPAL DEFINITIVO
# ============================================

while True:

    while not ir.startup():
        print("Esperando iRacing...")
        time.sleep(1)

    print(f"Conectado a iRacing - {current_track(ir)} - {get_track_length_km(ir)} Km")

    # Reset limpio de variables
    stint_active = False
    laps_buffer = []
    last_lap_completed = None
    last_recorded_lap_time = None

    while ir.is_connected:

        try:

            ir.freeze_var_buffer_latest()

            # ----------------------------------
            # BASE DATA
            # ----------------------------------
            is_in_pit_lane = ir['OnPitRoad']

            if stint_active and is_in_pit_lane:
                lap_was_in_pit = True
            speed = ir['Speed'] * 3.6
            fuel = ir['FuelLevel']
            is_on_track = ir['IsOnTrack']
            on_pit = ir['OnPitRoad']
            lap_completed = ir['LapCompleted']

            # ----------------------------------
            # START STINT
            # ----------------------------------

            if not stint_active and is_on_track and speed > 10:

                logger.info("Inicio de stint")

                stint_active = True
                laps_buffer = []
                lap_start_fuel = fuel
                last_lap_completed = lap_completed
                last_recorded_lap_time = None

                # Capturar contexto REAL aquí
                stint_track = current_track(ir)
                stint_session_type = current_session(ir)

            # ----------------------------------
            # DETECTOR DE VUELTA ESTABLE
            # ----------------------------------

            if (
                stint_active
                and is_on_track
                and lap_completed is not None
                and last_lap_completed is not None
                and lap_completed > last_lap_completed
            ):

                new_lap_time = None

                for _ in range(50):
                    ir.freeze_var_buffer_latest()
                    candidate = clean_number(ir['LapLastLapTime'])


                    if (
                        candidate
                        and candidate > 0
                        and candidate != last_recorded_lap_time
                    ):
                        new_lap_time = candidate
                        break

                    time.sleep(0.05)

                if not new_lap_time or new_lap_time > 300:
                    last_lap_completed = lap_completed
                    continue

                fuel_used = lap_start_fuel - fuel

                # Repostaje → solo resincronizar
                if fuel > lap_start_fuel:
                    lap_start_fuel = fuel
                    fuel_used = 0

                if 0 <= fuel_used < 15:

                    laps_buffer.append({
                        "lap": len(laps_buffer) + 1,
                        "lap_time": new_lap_time,
                        "fuel": clean_number(fuel),
                        "fuel_used": clean_number(fuel_used),
                        "is_pit_lap": lap_was_in_pit,
                        "track_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackSurfaceTemp'])),
                        "air_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackAirTemp'])),
                        "timestamp": datetime.now(UTC).isoformat()
                    })
                    lap_was_in_pit = False

                    print("lap", len(laps_buffer), "|", new_lap_time)
                    print("PIT LANE:", is_in_pit_lane)
                    lap_start_fuel = fuel
                    last_recorded_lap_time = new_lap_time

                last_lap_completed = lap_completed

            # ----------------------------------
            # FIN STINT (ESC / GARAGE)
            # ----------------------------------

            if stint_active and not is_on_track:

                logger.info("Salida de pista detectada")

                if len(laps_buffer) >= MIN_VALID_LAPS:
                    send_stint(ir, laps_buffer)
                else:
                    print("stint ignorado (<2 vueltas)")

                stint_active = False
                laps_buffer = []
                last_lap_completed = None
                last_recorded_lap_time = None

            time.sleep(0.05)

        except Exception as e:
            logger.error(f"ERROR LOOP: {e}")
            time.sleep(1)

    print("🔌 iRacing desconectado")

    # Si había stint activo, cerrarlo
    if stint_active and len(laps_buffer) >= MIN_VALID_LAPS:
        print("⏹ cerrando stint por desconexión")
        send_stint(ir, laps_buffer)

    restart("desconexion")
