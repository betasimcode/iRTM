# ============================================
# IRACING TEAM MANAGER - DEBUG VERSION
# ============================================

import irsdk
import time
import requests
from datetime import datetime, UTC
import logging

BASE_URL = "http://localhost:8000/api"
API_URL = f"{BASE_URL}/telemetry/stint-batch"

MIN_VALID_LAPS = 2

# ============================================
# LOGGING
# ============================================

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(message)s"
)

# ============================================
# HELPERS
# ============================================

def clean_number(value):
    try:
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


def capture_final_tyre_wear(ir, timeout=8):

    print("Esperando estabilización de desgaste...")

    start_time = time.time()
    last_values = None
    stable_counter = 0

    while time.time() - start_time < timeout:

        ir.freeze_var_buffer_latest()

        current = {
            "fl": clean_number(ir['LFwearM']) if 'LFwearM' in ir.var_headers_names else None,
            "fr": clean_number(ir['RFwearM']) if 'RFwearM' in ir.var_headers_names else None,
            "rl": clean_number(ir['LRwearM']) if 'LRwearM' in ir.var_headers_names else None,
            "rr": clean_number(ir['RRwearM']) if 'RRwearM' in ir.var_headers_names else None,
        }

        print("Wear actual:", current)

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
            print("Wear estabilizado ✔")
            return current

        time.sleep(0.5)

    print("Timeout desgaste")
    return last_values


def fetch_api_token(iracing_user_id):
    r = requests.post(
        f"{BASE_URL}/logger/token",
        json={"iracing_user_id": iracing_user_id},
        timeout=5
    )
    print("TOKEN STATUS:", r.status_code)
    print("TOKEN BODY:", r.text)
    return r.json().get("api_token")


def build_headers(token):
    return {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }

# ============================================
# MAIN
# ============================================

ir = irsdk.IRSDK()

print("Esperando iRacing...")
while not ir.startup():
    time.sleep(1)

print("Conectado a iRacing")

driver_data = current_driver_data(ir)
print("Driver:", driver_data)

api_token = fetch_api_token(driver_data["iracing_user_id"])
headers = build_headers(api_token)

stint_active = False
laps_buffer = []
tyre_snapshots = []
was_in_pit = False

while ir.is_connected:

    try:
        ir.freeze_var_buffer_latest()

        is_on_track = ir['IsOnTrack']
        speed = ir['Speed'] * 3.6
        lap_completed = ir['LapCompleted']
        is_in_pit = ir['OnPitRoad']
        print("OnPitRoad:", is_in_pit, "Speed:", speed)
        # Detectar entrada pit box
        if stint_active and is_in_pit and not was_in_pit and speed < 3:
            print(">>> DETECTADO PIT BOX <<<")

            tyre_data = capture_final_tyre_wear(ir)

            snapshot = {
                "lap_number": lap_completed,
                "wear_fl": tyre_data["fl"],
                "wear_fr": tyre_data["fr"],
                "wear_rl": tyre_data["rl"],
                "wear_rr": tyre_data["rr"],
            }

            tyre_snapshots.append(snapshot)
            print("SNAPSHOT AÑADIDO:", snapshot)

        was_in_pit = is_in_pit

        # Inicio stint
        if not stint_active and is_on_track and speed > 10:
            print("INICIO STINT")
            stint_active = True
            laps_buffer = []

        # Detectar nueva vuelta
        if stint_active and lap_completed and len(laps_buffer) < lap_completed:
            lap_time = clean_number(ir['LapLastLapTime'])
            if lap_time:
                laps_buffer.append({
                    "lap": len(laps_buffer)+1,
                    "lap_time": lap_time,
                    "fuel": 0,
                    "fuel_used": 0,
                    "is_pit_lap": False,
                    "track_temp": 0,
                    "air_temp": 0,
                    "timestamp": datetime.now(UTC).isoformat()
                })
                print("Lap añadida:", lap_time)

        # Fin stint
        if stint_active and not is_on_track:
            print("FIN STINT")
            print("Laps:", len(laps_buffer))
            print("Snapshots:", tyre_snapshots)

            payload = {
                "driver": driver_data,
                "car": "TEST",
                "track": "TEST",
                "session_type": "TEST",
                "track_length": 0,
                "tyre_compound": None,
                "tyre_snapshots": tyre_snapshots,
                "app_version": "debug",
                "laps": laps_buffer
            }

            print("PAYLOAD:", payload)

            r = requests.post(API_URL, json=payload, headers=headers)
            print("POST STATUS:", r.status_code)
            print("POST BODY:", r.text)

            break

        time.sleep(0.1)

    except Exception as e:
        print("ERROR:", e)
        time.sleep(1)
