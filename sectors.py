import os
import yaml

TELEMETRY_FOLDER = r"C:\Users\Monte\Documents\iRacing\telemetry"

circuits = {}
sectors = {}


def extract_block(text, start_key, end_key):

    start = text.find(start_key)

    if start == -1:
        return None

    end = text.find(end_key, start)

    if end == -1:
        end = start + 5000

    return text[start:end]


def process_ibt(file_path):

    print("Reading:", file_path)

    with open(file_path, "rb") as f:
        raw = f.read()

    text = raw.decode("utf-8", errors="ignore")

    weekend_block = extract_block(text, "WeekendInfo:", "SessionInfo:")
    split_block = extract_block(text, "SplitTimeInfo:", "CarSetup:")

    if not weekend_block:
        return

    try:
        weekend = yaml.safe_load(weekend_block)
    except:
        return

    weekend = weekend.get("WeekendInfo", {})

    track_id = weekend.get("TrackID")

    if not track_id:
        return

    if track_id not in circuits:

        circuits[track_id] = {
            "name": weekend.get("TrackDisplayName"),
            "config": weekend.get("TrackConfigName"),
            "city": weekend.get("TrackCity"),
            "country": weekend.get("TrackCountry"),
            "length": weekend.get("TrackLength")
        }

    if not split_block:
        return

    try:
        split = yaml.safe_load(split_block)
    except:
        return

    split = split.get("SplitTimeInfo", {})

    sectors_list = split.get("Sectors", [])

    splits = []

    for s in sectors_list:

        pct = s.get("SectorStartPct")

        if pct and pct > 0:
            splits.append(round(float(pct), 6))

    if splits:

        sectors[track_id] = splits


def scan_folder():

    for file in os.listdir(TELEMETRY_FOLDER):

        if file.endswith(".ibt"):

            try:
                process_ibt(os.path.join(TELEMETRY_FOLDER, file))
            except Exception as e:
                print("Error:", e)


def generate_circuits_sql():

    with open("circuits.sql", "w", encoding="utf-8") as f:

        for track_id,data in circuits.items():

            length = data["length"]

            if length and "km" in length:
                length = float(length.replace("km","").strip())
            else:
                length = "NULL"

            sql = (
                "INSERT INTO circuits "
                "(iracing_track_id,name,variant,city,country,length_km) VALUES "
                f"({track_id},"
                f"'{data['name']}',"
                f"'{data['config']}',"
                f"'{data['city']}',"
                f"'{data['country']}',"
                f"{length});\n"
            )

            f.write(sql)

    print("Generated circuits.sql")


def generate_sectors_sql():

    with open("track_sectors.sql", "w") as f:

        for track_id,splits in sectors.items():

            for i,split in enumerate(splits, start=1):

                sql = (
                    "INSERT INTO track_maps "
                    "(track_id,sector_number,start_pct) VALUES "
                    f"({track_id},{i},{split});\n"
                )

                f.write(sql)

    print("Generated track_sectors.sql")


def main():

    scan_folder()

    print("\nCircuits found:", len(circuits))
    print("Tracks with sectors:", len(sectors))

    generate_circuits_sql()
    generate_sectors_sql()


if __name__ == "__main__":
    main()
