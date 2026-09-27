import time
import irsdk

ir = irsdk.IRSDK()
ir.startup()

print("🔍 Buscando iRacing...")

last_session_num = None
last_subsession = None

while True:
    # =========================
    # ESPERAR CONEXIÓN
    # =========================
    if not ir.is_connected:
        print("⏳ Esperando conexión con iRacing...")
        time.sleep(1)
        continue

    # 🔥 importante: refrescar buffer
    ir.freeze_var_buffer_latest()

    try:
        # =========================
        # LEER DATOS
        # =========================
        weekend = ir['WeekendInfo']
        session_info = ir['SessionInfo']

        if not weekend or not session_info:
            time.sleep(0.5)
            continue

        subsession_id = weekend.get('SubSessionID')
        session_num = ir['SessionNum']

        sessions = session_info.get('Sessions', [])

        if session_num >= len(sessions):
            time.sleep(0.5)
            continue

        session_type = sessions[session_num].get('SessionType')

        # =========================
        # DETECTAR CAMBIOS
        # =========================
        if subsession_id != last_subsession or session_num != last_session_num:

            print("\n==============================")
            print(f"📡 Subsession ID : {subsession_id}")
            print(f"🎯 Session Num   : {session_num}")
            print(f"🏁 Session Type  : {session_type}")
            print("==============================")

            last_subsession = subsession_id
            last_session_num = session_num

    except Exception as e:
        print(f"❌ Error leyendo datos: {e}")

    time.sleep(1)
