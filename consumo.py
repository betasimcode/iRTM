import irsdk
import time
import gspread
from oauth2client.service_account import ServiceAccountCredentials
from datetime import datetime
import requests

# =========================================================
# CONFIG
# =========================================================

BATCH_SIZE = 5
SLEEP_TIME = 0.15
API_TOKEN = "PEGA_AQUI_TU_TOKEN"
API_URL = "http://127.0.0.1:8000/api/telemetry/stint"


def send_to_laravel(data):
    headers = {
        "Authorization": f"Bearer {API_TOKEN}"
    }

    try:
        requests.post(API_URL, json=data, headers=headers, timeout=0.2)
    except Exception as e:
        pass

# =========================================================
# GOOGLE SHEETS
# =========================================================

SCOPE = [
    "https://spreadsheets.google.com/feeds",
    "https://www.googleapis.com/auth/drive"
]

creds = ServiceAccountCredentials.from_json_keyfile_name("credentials.json", SCOPE)
client = gspread.authorize(creds)

spreadsheet = client.open("CALENDARIO")

fuel_sheet = spreadsheet.worksheet("FUELDATA")

FUEL_HEADERS = [
    "Fecha",
    "Serie",
    "Vehiculo",
    "VehicleID",
    "Circuito",
    "Variante",
    "VariantID",
    "Lap",
    "Liters/Lap",
    "Avg L/Lap"
]

if not fuel_sheet.get_all_values():
    fuel_sheet.update([FUEL_HEADERS], "A1:J1")

# =========================================================
# IRACING INIT
# =========================================================

ir = irsdk.IRSDK()

if not ir.startup():
    print("❌ iRacing no activo")
    exit()

print("✅ Conectado a iRacing")

# =========================================================
# VARIABLES GLOBALES
# =========================================================

last_lap = None
lap_start_fuel = None
session_active = False

prev_dist_pct = None
lap_triggered = False

total_fuel_used = 0.0
lap_count = 0

track_name = "UNKNOWN"
variant_name = "UNKNOWN"
variant_id = "UNKNOWN"
serie_name = "UNKNOWN"

car_name = "UNKNOWN"
car_id = "UNKNOWN"

buffer_rows = []

# =========================================================
# LOOP PRINCIPAL
# =========================================================

while True:

    ir.freeze_var_buffer_latest()

    lap = ir['Lap'] or 0
    fuel = ir['FuelLevel'] or 0.0
    session_state = ir['SessionState'] or 0
    on_pit = ir['OnPitRoad'] or 0

    # =====================================================
    # TRACK + SERIES INFO
    # =====================================================

    if ir['WeekendInfo'] and track_name == "UNKNOWN":

        info = ir['WeekendInfo']

        track_name = info.get('TrackName', "UNKNOWN")
        variant_name = info.get('TrackDisplayName', "UNKNOWN")
        variant_id = info.get('TrackID', "UNKNOWN")
        serie_name = info.get('SeriesName', "UNKNOWN")

        print(f"🏁 {variant_name} | Serie: {serie_name}")

    # =====================================================
    # CAR INFO (DriverInfo correcto)
    # =====================================================

    if car_id == "UNKNOWN":

        try:
            driver_info = ir['DriverInfo']
            driver_idx = driver_info.get('DriverCarIdx')

            if driver_idx is not None:
                drivers = driver_info.get('Drivers', [])

                for d in drivers:
                    if d.get('CarIdx') == driver_idx:
                        car_name = d.get('CarScreenName', "UNKNOWN")
                        car_id = d.get('CarID', "UNKNOWN")
                        print(f"🚗 {car_name} | ID {car_id}")
                        break
        except:
            pass

    # =====================================================
    # SESSION START
    # =====================================================

    if not session_active and session_state > 0:

        session_active = True

        last_lap = lap
        lap_start_fuel = fuel
        total_fuel_used = 0.0
        lap_count = 0
        buffer_rows.clear()

        print("▶ Sesión activa")

    # =====================================================
    # SESSION ACTIVE
    # =====================================================

if session_active:

    dist_pct = ir['LapDistPct']

    if prev_dist_pct is not None:

        # cruzamos meta
        if prev_dist_pct > 0.90 and dist_pct < 0.10 and not lap_triggered:

            lap_triggered = True

            fuel_used = lap_start_fuel - fuel

            if not on_pit and 0 < fuel_used < 15:

                total_fuel_used += fuel_used
                lap_count += 1
                avg = total_fuel_used / lap_count

                print(f"⛽ Lap {lap_count} | {fuel_used:.3f} L | Avg {avg:.3f}")

                payload = {
                    "lap": lap_count,
                    "lap_time": ir['LapLastLapTime'],
                    "fuel": fuel,
                    "track_temp": ir['WeekendInfo']['TrackSurfaceTemp'],
                    "air_temp": ir['WeekendInfo']['TrackAirTemp'],
                    "timestamp": datetime.utcnow().isoformat()
                }

                send_to_laravel(payload)

            lap_start_fuel = fuel

        # reset del trigger
        if dist_pct > 0.20:
            lap_triggered = False

    prev_dist_pct = dist_pct

    # =====================================================
    # SESSION END
    # =====================================================

    if session_active and session_state == 0:

        if buffer_rows:
            fuel_sheet.append_rows(buffer_rows, value_input_option="USER_ENTERED")
            buffer_rows.clear()

        session_active = False
        track_name = "UNKNOWN"
        car_id = "UNKNOWN"
        car_name = "UNKNOWN"

        print("⏹ Sesión finalizada")

    time.sleep(SLEEP_TIME)