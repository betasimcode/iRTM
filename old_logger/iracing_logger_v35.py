# ============================================
# IRACING LOGGER - STABLE RAW LAPTIMER VERSION
# ============================================

import irsdk
import time
import os
import sys
import requests
from ibt_processor import process_ibt_file
from datetime import datetime, UTC

BASE_URL = "http://localhost:8000/api"
API_URL = f"{BASE_URL}/telemetry/stint-batch"   # RAW
IBT_URL = f"{BASE_URL}/telemetry/ibt"          # IBT

MIN_VALID_LAPS = 2

IBT_FOLDER = os.path.expanduser("~/Documents/iRacing/telemetry")

# =========================
# IBT
# =========================

def get_latest_ibt():
    now = time.time()

    try:
        files = [
            os.path.join(IBT_FOLDER, f)
            for f in os.listdir(IBT_FOLDER)
            if f.endswith(".ibt") and now - os.path.getmtime(os.path.join(IBT_FOLDER, f)) < 120
        ]
    except:
        return None

    if not files:
        return None

    return max(files, key=os.path.getmtime)


def process_and_send_ibt(headers, track_id):

    ibt_file = get_latest_ibt()

    if not ibt_file:
        print("❌ No IBT encontrado")
        return

    print(f"📂 Procesando IBT: {ibt_file}")

    result = process_ibt_file(ibt_file)

    if not result:
        print("❌ IBT vacío")
        return

    payload = {
        "source": "ibt",
        "track_id": result["track_id"],
        "track_name": result["track_name"],
        "splits": result["splits"],
        "setup": result["setup"]
    }

    try:
        requests.post(
            IBT_URL,
            json=payload,
            headers=headers,
            timeout=30
        )

        print("📤 IBT enviado correctamente")

    except Exception as e:
        print("❌ Error enviando IBT:", e)


# =========================
# HELPERS
# =========================

def ir_safe(ir, key):
    try:
        return ir[key]
    except:
        return None


def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")):
            return None
        return v
    except:
        return None


# =========================
# CONTEXT
# =========================

def current_driver_data(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        drivers = d['Drivers']

        if idx is None or not drivers or idx >= len(drivers):
            return None

        driver = drivers[idx]

        return {
            "iracing_user_id": driver.get("UserID"),
            "name": driver.get("UserName")
        }
    except:
        return None


def current_track(ir):
    try:
        return ir['WeekendInfo'].get('TrackDisplayName')
    except:
        return "Unknown"


def current_track_id(ir):
    try:
        return ir['WeekendInfo'].get("TrackID")
    except:
        return None


def current_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        driver = d['Drivers'][idx]

        return {
            "car_name": driver.get("CarScreenName"),
            "car_path": driver.get("CarPath")
        }
    except:
        return None


def get_weather(ir):
    return {
        "track_temp": clean_number(ir_safe(ir, 'TrackTemp')),
        "air_temp": clean_number(ir_safe(ir, 'AirTemp')),
        "humidity": clean_number(ir_safe(ir, 'RelativeHumidity'))
    }


# =========================
# API
# =========================

def fetch_api_token(iracing_user_id):
    try:
        response = requests.post(
            f"{BASE_URL}/logger/token",
            json={"iracing_user_id": iracing_user_id},
            timeout=5
        )

        if response.status_code == 200:
            return response.json().get("api_token")
    except:
        pass

    return None


def send_telemetry(payload, headers):
    try:
        requests.post(API_URL, json=payload, headers=headers, timeout=15)
    except:
        pass


# =========================
# MAIN
# =========================

def run_logger():

    ir = irsdk.IRSDK()

    while not ir.startup():
        time.sleep(1)

    driver_data = None

    while not driver_data:
        ir.freeze_var_buffer_latest()
        driver_data = current_driver_data(ir)
        time.sleep(1)

    token = fetch_api_token(driver_data["iracing_user_id"])

    headers = {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }

    stint_active = False
    ibt_processed = False

    laps_buffer = []
    previous_fuel = None
    last_lap_time = None

    while True:

        try:
            ir.freeze_var_buffer_latest()

            speed = ir_safe(ir, 'Speed')
            is_on_track = ir_safe(ir, 'IsOnTrack')
            lap_time = clean_number(ir_safe(ir, 'LapLastLapTime'))
            lap_completed = ir_safe(ir, 'LapCompleted')
            fuel = clean_number(ir_safe(ir, 'FuelLevel'))

            if not isinstance(speed, (int, float)):
                time.sleep(0.05)
                continue

            speed *= 3.6

            # =========================
            # INICIO STINT
            # =========================
            if not stint_active and is_on_track and speed > 10:

                print("🟢 Inicio de stint")

                stint_active = True
                ibt_processed = False

                laps_buffer = []
                previous_fuel = fuel
                last_lap_time = None

            # =========================
            # LAPS
            # =========================
            if stint_active and is_on_track and lap_time:

                if last_lap_time is None:
                    last_lap_time = lap_time

                elif lap_time != last_lap_time and lap_time > 30:

                    weather = get_weather(ir)

                    fuel_used = None
                    if previous_fuel is not None and fuel is not None:
                        fuel_used = round(previous_fuel - fuel, 3)

                    laps_buffer.append({
                        "lap": lap_completed,
                        "lap_time": lap_time,
                        "fuel": fuel,
                        "fuel_used": fuel_used,
                        "weather": weather,
                        "timestamp": datetime.now(UTC).isoformat()
                    })

                    previous_fuel = fuel
                    last_lap_time = lap_time

            # =========================
            # FIN STINT
            # =========================
            if stint_active and not is_on_track and not ibt_processed:

                print("🔴 Fin de stint")

                if len(laps_buffer) >= MIN_VALID_LAPS:

                    payload = {
                        "driver": driver_data,
                        "car": current_car(ir),
                        "track": current_track(ir),
                        "track_id": current_track_id(ir),
                        "laps": laps_buffer
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                ibt_processed = True

                # 🔥 IBT separado
                time.sleep(5)
                process_and_send_ibt(headers, current_track_id(ir))

            time.sleep(0.05)

        except Exception as e:
            print("💥 ERROR:", e)


if __name__ == "__main__":
    run_logger()
