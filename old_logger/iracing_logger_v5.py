# ============================================
# IRACING LOGGER - BACKEND READY VERSION
# ============================================

import irsdk
import time
import os
import sys
import requests
from ibt_processor import process_ibt_file
from datetime import datetime, UTC

BASE_URL = "http://localhost:8000/api"
API_URL = f"{BASE_URL}/telemetry/stint-batch"

MIN_VALID_LAPS = 2


# =========================
# HELPERS
# =========================
def process_and_send_ibt(headers, track_id):

    ibt_file = get_latest_ibt()

    if not ibt_file:
        print("❌ No IBT encontrado")
        return

    splits = fetch_sector_splits(track_id, headers)

    if not splits:
        print("❌ No splits disponibles")
        return

    print(f"📂 Procesando IBT: {ibt_file}")

    result = process_ibt_file(ibt_file, splits)

    if not result:
        print("❌ IBT vacío")
        return

    payload = {
        "source": "ibt",
        "laps": result["laps"],
        "setup": result["setup"]
    }

    try:
        requests.post(
            f"{BASE_URL}/telemetry/ibt",
            json=payload,
            headers=headers,
            timeout=30
        )

        print("📤 IBT enviado correctamente")

    except Exception as e:
        print("❌ Error enviando IBT:", e)

def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")):
            return None
        return v
    except:
        return None


def ir_safe(ir, key):
    try:
        return ir[key]
    except:
        return None


def clean_temp(value):
    try:
        if isinstance(value, str):
            return float(value.replace(" C", "").strip())
        return float(value)
    except:
        return None


def restart_app(reason="Unknown"):
    print(f"\n🔄 Reiniciando logger → {reason}\n")
    python = sys.executable
    os.execv(python, [python] + sys.argv)


# =========================
# IR CONTEXT HELPERS
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


def current_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        drivers = d['Drivers']
        if idx is None or not drivers or idx >= len(drivers):
            return None

        driver = drivers[idx]

        return {
            "car_name": driver.get("CarScreenName"),
            "car_path": driver.get("CarPath"),
            "car_class_id": driver.get("CarClassID"),
            "car_class_short_name": driver.get("CarClassShortName")
        }
    except:
        return None


def current_track(ir):
    try:
        w = ir['WeekendInfo']
        name = w.get('TrackDisplayName')
        config = w.get('TrackConfigName')
        if config:
            return f"{name} - {config}"
        return name
    except:
        return "Unknown"


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
            return float(raw.replace("km", "").strip())

        if "mi" in raw:
            return float(raw.replace("mi", "").strip()) * 1.60934

    except:
        return None

    return None


def get_tyre_compound(ir):
    try:
        d = ir['DriverInfo']
        tires = d.get('DriverTires')
        if tires and isinstance(tires, list) and len(tires) > 0:
            return tires[0].get('TireCompoundType')
        return None
    except:
        return None


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
            print("✅ API token obtenido")
            return response.json().get("api_token")

        print("❌ Error token:", response.text)
        return None

    except Exception as e:
        print("❌ Excepción token:", e)
        return None


def send_telemetry(payload, headers):
    try:
        response = requests.post(API_URL, json=payload, headers=headers, timeout=15)

        if response.status_code == 200:
            print("📤 Stint enviado correctamente")
            return True

        print(f"❌ HTTP {response.status_code}")
        return False

    except Exception as e:
        print("❌ Excepción enviando:", e)
        return False


# =========================
# MAIN LOGGER
# =========================

def run_logger():

    ir = irsdk.IRSDK()
    last_subsession_id = None

    print("⏳ Esperando iRacing...")

    while not ir.startup():
        time.sleep(1)

    print("✅ SDK iniciado")

    # Esperar sesión válida
    while True:
        ir.freeze_var_buffer_latest()

        if not ir.is_connected:
            restart_app("SDK desconectado durante espera inicial")

        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data:
                break

        time.sleep(1)

    print("👤 Driver:", driver_data["name"])

    token = fetch_api_token(driver_data["iracing_user_id"])
    if not token:
        return

    headers = {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }

    stint_active = False
    laps_buffer = []
    tyre_snapshots = []
    previous_fuel = None
    last_lap_completed = None
    lap_was_in_pit = False

    print("🚀 Logger activo\n")

    while True:

        try:
            ir.freeze_var_buffer_latest()

            if not ir.is_connected:
                restart_app("SDK desconectado en loop principal")

            weekend = ir_safe(ir, 'WeekendInfo')
            if not weekend:
                time.sleep(0.2)
                continue

            current_subsession = weekend.get('SubSessionID')
            if last_subsession_id is None:
                last_subsession_id = current_subsession
            elif current_subsession and current_subsession != last_subsession_id:
                restart_app("Cambio de SubSessionID")

            speed_raw = ir_safe(ir, 'Speed')
            is_on_track = ir_safe(ir, 'IsOnTrack')
            fuel = clean_number(ir_safe(ir, 'FuelLevel'))
            lap_completed = ir_safe(ir, 'LapCompleted')
            lap_time = clean_number(ir_safe(ir, 'LapLastLapTime'))
            on_pit_road = ir_safe(ir, 'OnPitRoad')

            if not isinstance(speed_raw, (int, float)):
                time.sleep(0.1)
                continue

            speed = speed_raw * 3.6

            if on_pit_road:
                lap_was_in_pit = True

            # =====================
            # INICIO STINT
            # =====================
            if not stint_active and is_on_track and speed > 10:
                print("🟢 Inicio de stint")
                stint_active = True
                laps_buffer = []
                tyre_snapshots = []
                previous_fuel = fuel
                lap_was_in_pit = False
                last_lap_time_value = None
                last_lap_number_seen = None

            # =====================
            # NUEVA VUELTA ESTABLE
            # =====================

            if stint_active and is_on_track:

                if lap_time and lap_time > 0:

                    # Primera lectura del stint
                    if last_lap_time_value is None:
                        last_lap_time_value = lap_time
                        last_lap_number_seen = lap_completed

                    # Cambio real de vuelta
                    elif lap_time != last_lap_time_value:

                        completed_lap_number = lap_completed

                        fuel_used = None
                        if previous_fuel is not None and fuel is not None:
                            fuel_used = round(previous_fuel - fuel, 3)
                            if fuel_used < 0:
                                fuel_used = 0

                        print(f"⏱ V{completed_lap_number} | {lap_time:.3f}s | FuelUsed: {fuel_used} | Pit: {lap_was_in_pit}")

                        laps_buffer.append({
                            "lap": completed_lap_number,
                            "track_temp": clean_number(clean_temp(weekend.get('TrackSurfaceTemp'))),
                            "air_temp": clean_number(clean_temp(weekend.get('TrackAirTemp'))),
                            "lap_time": lap_time,
                            "fuel": fuel,
                            "fuel_used": fuel_used,
                            "is_pit_lap": lap_was_in_pit,
                            "timestamp": datetime.now(UTC).isoformat()
                        })

                        tyre_snapshots.append({
                            "lap_number": completed_lap_number,
                            "tyre_compound": get_tyre_compound(ir),
                            "wear_fl": clean_number(ir_safe(ir, 'LFwearM')),
                            "wear_fr": clean_number(ir_safe(ir, 'RFwearM')),
                            "wear_rl": clean_number(ir_safe(ir, 'LRwearM')),
                            "wear_rr": clean_number(ir_safe(ir, 'RRwearM'))
                        })

                        previous_fuel = fuel
                        lap_was_in_pit = False

                        last_lap_time_value = lap_time
                        last_lap_number_seen = lap_completed

            # =====================
            # FIN STINT
            # =====================
            if stint_active and not is_on_track:
                print("🔴 Fin de stint")

                if len(laps_buffer) >= MIN_VALID_LAPS:

                    payload = {
                        "driver": driver_data,
                        "car": current_car(ir),
                        "track": current_track(ir),
                        "session_type": current_session(ir),
                        "track_length": get_track_length_km(ir),
                        "tank_capacity": ir['DriverInfo'].get('DriverCarFuelMaxLtr'),
                        "laps": laps_buffer,
                        "tyre_snapshots": tyre_snapshots
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                laps_buffer = []
                tyre_snapshots = []
                previous_fuel = None
                last_lap_completed = None
                lap_was_in_pit = False

            time.sleep(0.1)

        except Exception as e:
            print("💥 ERROR:", e)
            time.sleep(1)


if __name__ == "__main__":
    run_logger()
