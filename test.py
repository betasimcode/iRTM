import time
import irsdk

ir = irsdk.IRSDK()

def dump_all(ir):
    print("\n================ FULL IRSDK DUMP ================\n")
    for key in ir.keys():
        try:
            print(f"{key}: {ir[key]}")
        except Exception as e:
            print(f"{key}: ❌ ERROR ({e})")
    print("\n================================================\n")


def dump_fuel(ir):
    print("\n🔍 FUEL VARIABLES\n")

    for key in ir.var_headers_names:
        if "Fuel" in key or "fuel" in key:
            try:
                print(f"{key}: {ir[key]}")
            except Exception as e:
                print(f"{key}: ❌ ERROR ({e})")


def dump_car(ir):
    print("\n🚗 CAR VARIABLES\n")

    for key in ir.var_headers_names:
        if "Car" in key:
            try:
                print(f"{key}: {ir[key]}")
            except:
                pass

def dump_all(ir):
    print("\n================ FULL IRSDK DUMP ================\n")

    for key in ir.var_headers_names:
        try:
            print(f"{key}: {ir[key]}")
        except Exception as e:
            print(f"{key}: ❌ ERROR ({e})")

    print("\n================================================\n")


def dump_driver_info(ir):
    print("\n👤 DRIVER INFO\n")
    try:
        d_info = ir['DriverInfo']
        if not d_info:
            print("No DriverInfo available")
            return

        print("DriverCarIdx:", d_info.get('DriverCarIdx'))

        for d in d_info.get('Drivers', []):
            if d['CarIdx'] == d_info.get('DriverCarIdx'):
                print("\n--- MY DRIVER ---")
                for k, v in d.items():
                    print(f"{k}: {v}")
    except Exception as e:
        print("❌ DriverInfo error:", e)


def main():
    print("⏳ Waiting for iRacing connection...")

    while not ir.startup():
        time.sleep(1)

    print("✅ Connected to iRacing")

    # Esperar unos segundos para que cargue todo
    time.sleep(2)

    dump_driver_info(ir)
    dump_fuel(ir)
    dump_car(ir)
    dump_all(ir)

    # 👇 descomenta si quieres TODO (mucho output)
    # dump_all(ir)


if __name__ == "__main__":
    main()