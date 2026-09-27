from commands.import_series import ImportSeriesCommand
from commands.import_rounds import ImportRoundsCommand
from commands.build_tracks_config import BuildTracksConfigCommand

def main():

    BuildTracksConfigCommand().run(2026, 3)

    
if __name__ == "__main__":
    main()