import irsdk
import time


def extract_samples_from_ibt(path):

    ir = irsdk.IRSDK()

    print("📂 Cargando IBT...")

    if not ir.startup(test_file=path):
        print("❌ No se pudo abrir IBT")
        return []

    samples = []

    last_time = -1
    freeze_count = 0

    print("📊 Leyendo samples...")

    while True:

        ir.freeze_var_buffer_latest()

        try:
            t = ir["SessionTime"]
            d = ir["LapDistPct"]
            lap = ir["Lap"]
        except:
            break

        # detectar fin de stream
        if t == last_time:
            freeze_count += 1
        else:
            freeze_count = 0

        last_time = t

        if (
            isinstance(t, (int, float)) and
            isinstance(d, (int, float)) and
            0 <= d <= 1.01 and
            t > 0
        ):
            samples.append({
                "session_time": t,
                "lap_dist": d,
                "lap": lap
            })

        if freeze_count > 100:
            break

        time.sleep(0.001)

    print(f"✅ Samples: {len(samples)}")

    return samples
