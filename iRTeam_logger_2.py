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
        self.session_registered = False
        self.current_stint_id = None
        self.last_track_id = None
        self.current_track = None
        self.current_track_id = None
        self.last_sub_id = None
        self.stint_attempted = False
        self.current_sectors = "0.33,0.66,0.99"
        self.in_stint = False
        self.stop_event = threading.Event()
        self.icon = None
        self.csharp_process = None

    def get_headers(self):
        return {
            "X-API-TOKEN": self.token,
            "Accept": "application/json"
        }

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
            subsession_id = w.get('SubSessionID') if w else None

            car_path = None
            if d and 'Drivers' in d:
                idx = d.get('DriverCarIdx')
                if idx is not None and idx < len(d['Drivers']):
                    car_path = d['Drivers'][idx].get('CarPath')

            return track_id, subsession_id, car_path

        except:
            return None, None, None

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

            s_type = "Offline/Test"
            try:
                s_info = ir['SessionInfo']
                s_num = ir.get('SessionNum', 0)
                if s_info and 'Sessions' in s_info and len(s_info['Sessions']) > s_num:
                    s_type = s_info['Sessions'][s_num].get('SessionType', "Unknown")
            except: pass

            sof_val = 0
            try:
                raw_sof = ir['SessionResultsSOF']
                sof_val = int(raw_sof) if raw_sof is not None else 0
            except: sof_val = 0

            payload = {
                "session": {
                    "iracing_subsession_id": final_sub_id,
                    "session_type": str(s_type),
                    "sof": sof_val,
                    "session_at": datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                    "is_official": is_official
                },
                "track": { "iracing_track_id": self.current_track_id }
            }

            headers = self.get_headers()

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
                "laps": []  # 👈 importante luego
            }

            headers = self.get_headers()
            r = requests.post(STINT_URL, json=payload, headers=headers)

            if r.status_code in [200, 201]:
                self.current_stint_id = r.json().get('stint_id')
                print(f"⛽ Stint iniciado! ID:{self.current_stint_id}")
                self.launch_csharp_timer()
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

            track_id, subsession_id, car_path = self.detect_session_change()
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
                self.last_car_path = car_path
                self.session_registered = False
                print("🟢 Estado inicial capturado")

            # 🔥 CAMBIO DE SESIÓN
            elif subsession_id and self.last_sub_id and subsession_id != self.last_sub_id:
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

            if on_track and not self.in_stint and speed > 5 and lap_pct > 0.01:
                print("🏁 Pista detectada + RPM OK. Iniciando...")
                self.in_stint = True
                self.stint_attempted = True
                self.start_stint()

            elif not on_track and self.in_stint:
                print("🔴 Fin de stint detectado (Fuera de pista)")
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
