import irsdk
import requests
import time
import threading
import sys
import os
import subprocess
from datetime import datetime
from pystray import Icon, Menu, MenuItem
from PIL import Image, ImageDraw

# --- CONFIGURACIÓN ---
BASE_URL = "http://localhost:8000/api"
STINT_URL = "http://localhost:8000/api/v1/stints"

# Ruta absoluta al ejecutable
PATH_CRONOMETRO_CS = r"C:\Users\Monte\source\repos\iRTeam_manager_logger\iRTeam_manager_logger\bin\x64\Debug\net8.0\iRTeam_manager_logger.exe"

current_user = "No driver"

ir = irsdk.IRSDK()

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

def current_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"


import os
import time


# ===============================
# IBT - DETECTAR ARCHIVO NUEVO
# ===============================
def wait_for_new_ibt(path, timeout=10):

    print("⏳ Esperando IBT...")

    start_time = time.time()
    initial_files = set(os.listdir(path))

    while time.time() - start_time < timeout:

        current_files = set(os.listdir(path))
        new_files = current_files - initial_files

        ibts = [f for f in new_files if f.endswith('.ibt')]

        if ibts:
            latest = max(
                [os.path.join(path, f) for f in ibts],
                key=os.path.getmtime
            )

            print("📂 IBT nuevo detectado:", latest)
            return latest

        time.sleep(0.5)

    print("❌ Timeout esperando IBT")
    return None

def get_latest_ibt(path):

    files = [
        os.path.join(path, f)
        for f in os.listdir(path)
        if f.endswith('.ibt')
    ]

    if not files:
        return None

    latest = max(files, key=os.path.getmtime)

    print("📂 IBT encontrado:", latest)

    return latest

# ===============================
# IBT - EXTRAER TEXTO
# ===============================
def extract_carsetup_from_ibt(file_path):

    try:
        with open(file_path, 'rb') as f:
            content = f.read()

        text = content.decode('utf-8', errors='ignore')

        start = text.find("CarSetup:")

        if start == -1:
            print("❌ No CarSetup encontrado")
            return None

        # 🔥 buscar siguiente bloque raíz (sin indentación)
        lines = text[start:].splitlines()

        setup_lines = []
        first = True

        for line in lines:

            # parar cuando aparece otra sección sin indentación
            if not first and not line.startswith(" ") and ":" in line:
                break

            setup_lines.append(line)
            first = False

        setup_text = "\n".join(setup_lines)

        # 🔥 seguridad: límite tamaño
        setup_text = setup_text[:10000]

        print("📏 Setup chars:", len(setup_text))

        return setup_text

    except Exception as e:
        print(f"❌ Error leyendo IBT: {e}")
        return None


# ===============================
# PARSEAR TEXTO → DICT
# ===============================
def parse_setup_text(setup_text):

    data = {}
    stack = []

    for line in setup_text.splitlines():

        if not line.strip():
            continue

        indent = len(line) - len(line.lstrip())
        key_value = line.strip().split(":", 1)

        key = key_value[0].strip()
        value = key_value[1].strip() if len(key_value) > 1 else None

        while stack and stack[-1][0] >= indent:
            stack.pop()

        if stack:
            parent = stack[-1][1]
        else:
            parent = data

        if value:
            parent[key] = value
        else:
            parent[key] = {}
            stack.append((indent, parent[key]))

    return data

def restart_app(reason="Unknown"):
    print(f"\n🔄 REINICIANDO LOGGER: {reason}\n")
    try:
        ir.shutdown()
    except:
        pass
    python = sys.executable
    os.execv(python, [python] + sys.argv)

class MRTLogger:
    def __init__(self):
        self.token = None
        self.was_connected = False
        self.db_user_id = None
        self.last_sub_id = None
        self.db_session_id = None
        self.last_session_type = None
        self.session_registered = False
        self.current_stint_id = None
        self.last_track_id = None
        self.current_track = None
        self.current_track_id = None
        self.stint_attempted = False
        self.current_sectors = "0.33,0.66,0.99"
        self.in_stint = False
        self.tank_capacity = None
        self.last_tank_log_time = 0
        self.sync_attempts = 0
        self.car_synced = False

        self.last_lap_completed = None

        self.was_on_pitroad = False
        self.pit_in_this_lap = False
        self.pit_out_this_lap = False

        self.tyres_start_sent = False

        self.last_fuel = None

        self.stop_event = threading.Event()
        self.icon = None
        self.csharp_process = None

    def get_headers(self):
        return {
            "X-API-TOKEN": self.token,
            "Accept": "application/json"
        }

    def calculate_tank_capacity(self):
        try:
            fuel = float(ir['FuelLevel'] or 0.0)
            pct  = float(ir['FuelLevelPct'] or 0.0)

            if pct > 0:
                capacity = round(fuel / pct, 2)

                if capacity > 5:
                    return capacity

            return None

        except Exception as e:
            print(f"⚠️ Tank calc error: {e}")
            return None


    def sync_car_once(self, car_id, car_name):

        if self.car_synced:
            return

        try:
            r = requests.post(
                f"{BASE_URL}/logger/car",
                json={
                    "iracing_car_id": int(car_id),
                    "name": car_name,
                    "tank_capacity": self.calculate_tank_capacity()
                },
                headers=self.get_headers(),
                timeout=3
            )

            if r.status_code in [200, 201]:

                data = r.json()

                # 🔥 CASO 1 → ya existe en DB
                if data.get('status') == 'exists':
                    self.car_synced = True
                    self.tank_capacity = data.get('tank_capacity')
                    print(f"✅ Car ya existe en DB → Tank:{self.tank_capacity}")
                    return

                # 🔥 CASO 2 → recién creado
                if data.get('tank_capacity'):
                    self.car_synced = True
                    self.tank_capacity = data.get('tank_capacity')
                    print(f"✅ Car synced → Tank:{self.tank_capacity}")
                    return

            # 🔥 CONTROL DE LOG (solo si no hay respuesta válida)
            now = time.time()
            if now - self.last_tank_log_time > 5:
                print("⏳ Esperando tank_capacity válido...")
                self.last_tank_log_time = now

        except Exception as e:
            print(f"⚠️ Error sync car: {e}")

    def send_with_retry(self, payload):

        for i in range(5):

            try:
                r = requests.post(
                    f"{BASE_URL}/logger/setup-snapshot",
                    json=payload,
                    headers=self.get_headers(),
                    timeout=10
                )

                if r.status_code == 200:
                    print("✅ Setup guardado")
                    return r  # 👈 ESTO ES CLAVE

                print(f"⚠️ intento {i+1} fallo:", r.text)

            except Exception as e:
                print("❌ error:", e)

            time.sleep(1)

        print("💀 No se pudo guardar setup")
        return None

    def handle_stint_end(self):

        print("📦 Capturando setup desde IBT...")

        ibt_path = os.path.expanduser("~/Documents/iRacing/telemetry")

        time.sleep(2)

        ibt_file = get_latest_ibt(ibt_path)

        if not ibt_file:
            print("❌ No se encontró IBT")
            return

        text = extract_carsetup_from_ibt(ibt_file)

        if not text:
            print("❌ No se pudo extraer CarSetup")
            return

        data = parse_setup_text(text)

        if not data:
            print("⛔ Setup vacío")
            return

        payload = {
            "stint_id": self.current_stint_id,
            "values": data
        }

        print("📦 SETUP SNAPSHOT OK")
        print("SIZE:", len(str(data)))
        print("STINT ID (logger):", self.current_stint_id)

        try:
            r = self.send_with_retry(payload)  # 👈 CLAVE

            if r:
                print("STATUS:", r.status_code)
                print("RESPONSE:", r.text)
            else:
                print("❌ No se recibió respuesta del servidor")

        except Exception as e:
            print("❌ Error enviando setup:", e)




    def ir_safe(self, key):
        try:
            val = self.ir[key]

            # 🔥 si es lista/tuple → coger primer valor
            if isinstance(val, (list, tuple)):
                return val[0] if len(val) > 0 else None

            return val

        except Exception as e:
            print(f"⚠️ ir_safe fallo en {key}: {e}")
            return None

    def clean_number(self, v):
        try:
            if v is None:
                return 0
            return float(v)
        except:
            return 0

   # --- FUNCIÓN CONDICIONES DE PISTA ---
    def send_env_data(self):
        try:
            payload = {
                "user_id": self.db_user_id,
                "track_temp": float(ir['TrackTemp'] or 0),
                "air_temp": float(ir['AirTemp'] or 0),
                "wind_speed": float(ir['WindVel'] or 0),
                "wind_dir": float(ir['WindDir'] or 0),
                "humidity": float(ir['RelativeHumidity'] or 0),
                "sky": int(ir['Skies'] or 0),
                "track_state": int(ir['TrackWetness'] or 0)
            }

            requests.post(
                f"{BASE_URL}/logger/env",
                json=payload,
                headers=self.get_headers(),
                timeout=2
            )

        except Exception as e:
            print(f"⚠️ Error enviando ENV: {e}")

    def get_tyre_wear(self):
        try:
            wear = ir['CarIdxTireWear']
            idx = ir['DriverInfo']['DriverCarIdx']

            if wear and idx is not None and idx < len(wear):
                return wear[idx]  # [LF, RF, LR, RR]

        except:
            pass

        return None


    def send_tyre_snapshot(self, snapshot_type, lap):
        try:
            global ir

            payload = {

                "stint_id": self.current_stint_id,
                "lap_number": lap,
                "snapshot_type": snapshot_type,

                # ===== TEMPS BASE (centro) =====
                "temp_fl": float(ir['LFtempCM']),
                "temp_fr": float(ir['RFtempCM']),
                "temp_rl": float(ir['LRtempCM']),
                "temp_rr": float(ir['RRtempCM']),

                # ===== FRONT LEFT =====
                "temp_fl_o": float(ir['LFtempCL']),
                "temp_fl_m": float(ir['LFtempCM']),
                "temp_fl_i": float(ir['LFtempCR']),

                # ===== FRONT RIGHT (invertido) =====
                "temp_fr_o": float(ir['RFtempCR']),
                "temp_fr_m": float(ir['RFtempCM']),
                "temp_fr_i": float(ir['RFtempCL']),

                # ===== REAR LEFT =====
                "temp_rl_o": float(ir['LRtempCL']),
                "temp_rl_m": float(ir['LRtempCM']),
                "temp_rl_i": float(ir['LRtempCR']),

                # ===== REAR RIGHT (invertido) =====
                "temp_rr_o": float(ir['RRtempCR']),
                "temp_rr_m": float(ir['RRtempCM']),
                "temp_rr_i": float(ir['RRtempCL']),

                # ===== WEAR (MIDDLE estable) =====
                "wear_fl": float(ir['LFwearM']),
                "wear_fr": float(ir['RFwearM']),
                "wear_rl": float(ir['LRwearM']),
                "wear_rr": float(ir['RRwearM']),
            }

            # print("📦 PAYLOAD TYRES:", payload)  # 👈 DEBUG CLAVE

            r = requests.post(
                f"{BASE_URL}/stint-tyres",
                json=payload,
                headers=self.get_headers(),
                timeout=2
            )

            print("STATUS:", r.status_code)
            print("RESPONSE:", r.text)

        except Exception as e:
            print(f"❌ Error enviando tyres: {e}")


    # --- NUEVA FUNCIÓN PARA CARGAR SECTORES ---
    def load_track_maps(self, track_id):
        """Busca los sectores en la API/DB. Si falla, devuelve genéricos."""
        try:
            headers = self.get_headers()
            r = requests.get(f"{BASE_URL}/tracks/{track_id}/sectors", headers=headers, timeout=5)
            if r.status_code == 200:
                data = r.json()

            if isinstance(data, list) and len(data) > 0:
                pts = [str(item['pct']) for item in data]
                return ",".join(pts)
                if pts: return str(pts)
                print(f"🌐 GET {BASE_URL}/tracks/{track_id}/sectors")
                print("STATUS:", r.status_code)
                print("RESPONSE:", r.text)
        except Exception as e:
            print(f"⚠️ No se pudo conectar para sectores: {e}")

        print("ℹ️ Usando sectores genéricos (0.33, 0.66, 0.99)")
        return "0.33,0.66,0.99"


    # --- FUNCIÓN DE LANZAMIENTO UNIFICADA ---
    def launch_csharp_timer(self):
        if not os.path.exists(PATH_CRONOMETRO_CS):
            print(f"❌ ERROR: No existe el EXE en {PATH_CRONOMETRO_CS}")
            return

        working_dir = os.path.dirname(PATH_CRONOMETRO_CS)
        stint_id = str(self.current_stint_id)
        token = str(self.token)
        pts = self.current_sectors if self.current_sectors else "0.33,0.66,0.99"
        args_list = [PATH_CRONOMETRO_CS, stint_id, token, pts]

        print("--- 🔍 LANZANDO CRONÓMETRO C# ---")
        print(f"🚀 Stint ID: {stint_id} | 📍 Sectores: {pts}")

        try:
            self.csharp_process = subprocess.Popen(
                args_list,
                cwd=working_dir
            )
            print("✅ Proceso C# vinculado y ejecutándose.")
        except Exception as e:
            print(f"❌ Error crítico al lanzar C#: {e}")


    def stop_csharp_timer(self):
        if self.csharp_process:
            try:
                print("🛑 Deteniendo Cronómetro C#...")
                self.csharp_process.terminate()
                self.csharp_process.wait(timeout=2)
            except Exception:
                try: self.csharp_process.kill()
                except: pass
            finally:
                self.csharp_process = None

    def fetch_token(self):
        # `print("URL:", STINT_URL)`
        print("🔍 Buscando iRacing...")
        while not self.stop_event.is_set():
            if ir.startup() and ir.is_connected:
                d_info = ir['DriverInfo']
                u_id = d_info.get('DriverUserID')

                if u_id:
                    try:
                        r = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": u_id}, timeout=5)
                        if r.status_code == 200:
                            data = r.json()
                            self.token = data.get("api_token")
                            self.db_user_id = data.get("id")
                            self.iracing_user_id = u_id
                            print(f"✅ Token validated - User {u_id} (ID DB: {self.db_user_id}).")
                            return True
                    except Exception as e:
                        print(f"❌ Error API Token: {e}")
            time.sleep(3)
        return False

    def detect_session_change(self):
        try:
            w = ir['WeekendInfo']
            d = ir['DriverInfo']

            track_id = w.get('TrackID') if w else None
            subsession_id = str(w.get('SubSessionID')) if w and w.get('SubSessionID') else None

            car_path = None
            if d and 'Drivers' in d:
                idx = d.get('DriverCarIdx')
                if idx is not None and idx < len(d['Drivers']):
                    car_path = d['Drivers'][idx].get('CarPath')

            return track_id, subsession_id, car_path

        except:
            return None, None, None

    def get_session_type(self):
        try:
            s_info = ir['SessionInfo']
            s_num = ir.get('SessionNum', 0)

            if s_info and 'Sessions' in s_info and len(s_info['Sessions']) > s_num:
                return s_info['Sessions'][s_num].get('SessionType', "Unknown")

        except:
            pass

        return "Unknown"

    def register_session_db(self):
        try:
            if not ir.is_connected: return False
            w_info = ir['WeekendInfo']
            if not w_info: return False

            self.current_track = (w_info.get('TrackDisplayName'))
            self.current_track_id = int(w_info.get('TrackID', 0))
            self.current_sectors = self.load_track_maps(self.current_track_id)
            print(f"📍 Sectores cargados para {self.current_track}")
            print(f" {self.current_sectors}")

            sub_id = str(w_info.get('SubSessionID'))

            is_official = sub_id is not None and int(sub_id) > 0
            final_sub_id = str(sub_id) if is_official else f"TEST-{datetime.now().strftime('%Y%m%d%H%M%S')}"

            session_type = ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
            sof_val = 0
            try:
                raw_sof = ir['SessionResultsSOF']
                sof_val = int(raw_sof) if raw_sof is not None else 0
            except: sof_val = 0

            payload = {
                "session": {
                    "iracing_subsession_id": final_sub_id,
                    "session_type": session_type,
                    "sof": sof_val,
                    "session_at": datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                    "is_official": is_official
                },
                "track": { "iracing_track_id": self.current_track_id }
            }

            headers = self.get_headers()
            print(session_type)
            print(f"☁️ Enviando POST para Track {self.current_track_id}...")
            r = requests.post(f"{BASE_URL}/init-session", json=payload, headers=headers, timeout=8)

            if r.status_code in [200, 201]:
                response_data = r.json()
                self.db_session_id = response_data.get('db_session_id')
                self.last_sub_id = sub_id
                print(f"✅ [DB] Sesión registrada (ID: {self.db_session_id})")
                return True
            else:
                print(f"⚠️ Error API ({r.status_code}): {r.text}")
                return False

        except Exception as e:
            print(f"❌ Error crítico register_session_db: {e}")
            return False

    def start_stint(self):
        try:
            d_info = ir['DriverInfo']
            if not d_info: return

            my_idx = d_info.get('DriverCarIdx')
            my_driver_data = next((d for d in d_info.get('Drivers', []) if d['CarIdx'] == my_idx), None)

            car_name = my_driver_data.get('CarScreenName', 'Unknown') if my_driver_data else "Unknown"
            car_id = my_driver_data.get('CarID', 0) if my_driver_data else 0
            fuel_initial = float(ir['FuelLevel'] or 0.0)
            session_time_start = float(ir['SessionTime'] or 0.0)
            self.last_fuel = fuel_initial  # 👈 CLAVE
            # cuando detectas inicio de stint

            # print("DEBUG SessionTimeOfDay:", ir['SessionTimeOfDay'])
            payload = {
                "driver": {
                    "iracing_user_id": self.iracing_user_id
                },
                "db_session_id": self.db_session_id,
                "iracing_subsession_id": str(self.last_sub_id),
                "track_id": self.current_track_id,
                "car_name": car_name,
                "car_id": int(car_id),
                "fuel_setup": fuel_initial,
                "sim_time_day": float(ir['SessionTimeOfDay'] or 0),
                "track_temp": float(ir['TrackTemp'] or 0),
                "air_temp": float(ir['AirTemp'] or 0),
                "fuel_per_lap": 0,
                "fuel_consumed": 0,
                "session_time_start": session_time_start,
                "laps": []  # 👈 importante luego
            }
            # 👇 AÑADE ESTO ANTES del POST del stint

            try:
                requests.post(
                    f"{BASE_URL}/logger/car",
                    json={
                        "iracing_car_id": int(car_id),
                        "name": car_name
                    },
                    headers=self.get_headers(),
                    timeout=3
                )
            except Exception as e:
                print(f"⚠️ Error sync car: {e}")
            headers = self.get_headers()
            r = requests.post(STINT_URL, json=payload, headers=headers)

            if r.status_code in [200, 201]:
                # 👇 NUEVO



                self.current_stint_id = r.json().get('stint_id')
                self.send_tyre_snapshot("start", -1)
                self.tyres_start_sent = True
                print(f"{BASE_URL}/logger/car")
                print(f"⛽ Stint iniciado! ID:{self.current_stint_id}")
                print("🟢 Tyres START capturados al iniciar stint")
                self.launch_csharp_timer()

                self.last_lap_completed = None
                self.pit_in_this_lap = False
                self.pit_out_this_lap = False
                # print("DEBUG PAYLOAD:", payload)
            else:
                print(f"❌ Error al crear Stint ({r.status_code}): {r.text}")
                # NO resetees, evita loop infinito
                print("⚠️ Stint no creado, pero seguimos en pista")
        except Exception as e:
            print(f"❌ Error start_stint: {e}")
            self.in_stint = False

    def action_quit(self, icon, item):
        self.stop_csharp_timer()
        icon.stop()
        os._exit(0)

    def action_restart(self, icon, item):
        restart_app("Reinicio manual")

    def main_loop(self):

        if not self.fetch_token(): return

        while not self.stop_event.is_set():
            if not ir.is_connected:
                if self.in_stint:
                    self.stop_csharp_timer()
                    self.in_stint = False

                print("🔌 iRacing desconectado")
                restart_app("sim exit")
                # 🔥 RESET TOTAL (CLAVE)
                self.last_track_id = None
                self.last_sub_id = None
                self.last_car_path = None
                self.session_registered = False

                ir.startup()
                time.sleep(2)
                continue
            self.send_env_data()

            # 1. Comprobar cambios de sesión/pista
            ir.freeze_var_buffer_latest()

             # =========================
            # 🧠 TELEMETRÍA POR VUELTA
            # =========================
            if self.in_stint and self.current_stint_id:
                try:
                    current_lap = ir['LapCompleted'] or 0
                    is_on_pitroad = ir['OnPitRoad']

                    # Inicializar estado pit
                    if self.last_lap_completed is None:
                        self.last_lap_completed = current_lap
                        self.was_on_pitroad = is_on_pitroad

                    # Detectar PIT IN
                    if not self.was_on_pitroad and is_on_pitroad and (ir['Speed'] or 0) > 7:
                        self.pit_in_this_lap = True
                        print("🟥 PIT IN detectado (Python)")

                    # Detectar PIT OUT
                    if self.was_on_pitroad and not is_on_pitroad:
                        self.pit_out_this_lap = True
                        print("🟩 PIT OUT detectado (Python)")

                    self.was_on_pitroad = is_on_pitroad

                    # Detectar nueva vuelta
                    if current_lap != self.last_lap_completed:

                        # Reset flags
                        self.pit_in_this_lap = False
                        self.pit_out_this_lap = False

                        self.last_lap_completed = current_lap

                    # print(f"DEBUG TELEMETRIA → stint:{self.in_stint} id:{self.current_stint_id}")
                except Exception as e:
                    print(f"⚠️ Error telemetría: {e}")


            track_id, subsession_id, car_path = self.detect_session_change()

            # =========================
            # 🔥 DETECTAR CAMBIO DE SESSION TYPE
            # =========================

            current_session_type = self.get_session_type()

            if self.last_session_type is None:
                self.last_session_type = current_session_type

            elif current_session_type != self.last_session_type:
                print(f"\n🔁 CAMBIO DE SESSION TYPE: {self.last_session_type} → {current_session_type}")
                restart_app("Cambio de session type")
                return

            # 🚨 VALIDACIÓN FUERTE (clave)
            if not track_id:
                print(f"DEBUG → Track: {track_id} | Sub: {subsession_id}")
                print("⏳ Esperando track válido...")
                time.sleep(1)
                continue
            # 🧠 evitar lecturas inestables (cambio de server)
            if not hasattr(self, "stable_counter"):
                self.stable_counter = 0
                self.last_seen_track = None
                self.last_seen_sub = None

            if track_id == self.last_seen_track and subsession_id == self.last_seen_sub:
                self.stable_counter += 1
            else:
                self.stable_counter = 0

            self.last_seen_track = track_id
            self.last_seen_sub = subsession_id

            # esperar 2 ciclos estables
            if self.stable_counter < 1:
                print("⏳ ...")
                time.sleep(1)
                continue
            # 🔥 PRIMERA VEZ
            if self.last_track_id is None:
                self.last_track_id = track_id
                self.last_sub_id = subsession_id
                self.last_session_type = current_session_type
                self.last_car_path = car_path
                self.session_registered = False
                print("🟢 Estado inicial capturado")

            # 🔥 CAMBIO DE SESIÓN
            elif (
                    subsession_id
                    and self.last_sub_id
                    and subsession_id != self.last_sub_id
                    and int(subsession_id) > 0
                    and int(self.last_sub_id) > 0
                ):
                print(f"\n🔁 NUEVA SESIÓN: {self.last_sub_id} → {subsession_id}")
                restart_app("Cambio de sesión")
                return

            # 🔥 CAMBIO DE CIRCUITO
            elif track_id and track_id != self.last_track_id:
                print(f"\n🏁 CAMBIO DE CIRCUITO: {self.last_track_id} → {track_id}")
                restart_app("Cambio de circuito")
                return

            # 🔥 CAMBIO DE COCHE
            elif car_path and car_path != self.last_car_path:
                print(f"\n🚗 CAMBIO DE COCHE")
                restart_app("Cambio de coche")
                return

            # 🔄 ACTUALIZAR ESTADO
            self.last_track_id = track_id
            if subsession_id:
                self.last_sub_id = subsession_id
            self.last_car_path = car_path

            # 🔥 REGISTRAR SOLO UNA VEZ
            if not self.session_registered:
                if self.register_session_db():
                    self.session_registered = True

            # 2. Lógica de Stint
            on_track = ir['IsOnTrack']
            speed = ir['Speed'] or 0
            lap_pct = ir['LapDistPct'] or 0

            # 🚗 SYNC CAR (solo una vez y con datos válidos)
            try:
                d_info = ir['DriverInfo']
                my_idx = d_info.get('DriverCarIdx')
                my_driver_data = next((d for d in d_info.get('Drivers', []) if d['CarIdx'] == my_idx), None)

                if my_driver_data:
                    car_name = my_driver_data.get('CarScreenName', 'Unknown')
                    car_id = my_driver_data.get('CarID', 0)

                    self.sync_car_once(car_id, car_name)

            except Exception as e:
                print(f"⚠️ Error preparando sync car: {e}")

            if (
                on_track 
                and not self.in_stint 
                and speed > 5 
                and lap_pct > 0.01
                and self.car_synced  # 🔥 CLAVE
            ):
                print("🏁 Pista detectada + RPM OK. Iniciando...")
                self.in_stint = True
                self.stint_attempted = True
                self.start_stint()

                # self.send_setup_snapshot()
            elif not on_track and self.in_stint:
                print("🔴 Fin de stint detectado (Fuera de pista)")

                session_time_end = float(ir['SessionTime'] or 0.0)
                if ir['OnPitRoad'] and (ir['Speed'] or 0) < 1:
                    self.send_tyre_snapshot("end", ir['LapCompleted'] or 0)
                    print("🔴 Tyres END capturados en PIT")

                try:
                    # cerrar stint
                    requests.post(
                        f"{BASE_URL}/v1/stints/{self.current_stint_id}/end",
                        json={"session_time_end": session_time_end},
                        headers=self.get_headers(),
                        timeout=2
                    )

                    print("⏱️ Stint cerrado correctamente")

                    # 🔥 añadir esto
                    time.sleep(1)

                    # ahora sí
                    self.handle_stint_end()

                except Exception as e:
                    print(f"❌ Error cerrando stint: {e}")

                self.in_stint = False
                self.stint_attempted = False
                self.stop_csharp_timer()

            time.sleep(1)

    def create_tray_icon(self):
        image = Image.new('RGB', (64, 64), (30, 30, 30))
        draw = ImageDraw.Draw(image)
        draw.ellipse([10, 10, 54, 54], fill=(200, 0, 0))
        menu = Menu(
            MenuItem('MRT Logger Activo', lambda: None, enabled=False),
            MenuItem('Reiniciar Todo', self.action_restart),
            Menu.SEPARATOR,
            MenuItem('Cerrar Logger', self.action_quit)
        )
        self.icon = Icon("MRT_Logger", image, "iRacing MRT Logger", menu)
        threading.Thread(target=self.main_loop, daemon=True).start()
        self.icon.run()

if __name__ == "__main__":
    logger = MRTLogger()
    logger.create_tray_icon()
