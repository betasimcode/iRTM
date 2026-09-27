def process_ibt(data):

    ibt_laps = data["laps"]

    stint = find_stint(data)

    raw_laps = get_raw_laps(stint.id)

    merged = merge_laps(raw_laps, ibt_laps)

    save_merged_laps(stint.id, merged)

    return {"status": "ok"}
