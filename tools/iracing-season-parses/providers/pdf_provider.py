import pdfplumber

from config import SAMPLES_DIR, SERIES_FILE
from models.parsed_series import ParsedSeries
from models.season import Season
from providers.season_provider import SeasonProvider
from repositories.config_repository import ConfigRepository


class PdfSeasonProvider(SeasonProvider):

    def __init__(self, season: Season):

        self.season = season

        self.pdf_path = (
            SAMPLES_DIR /
            f"{season.year}_{season.number}.pdf"
        )

        self.config = ConfigRepository().load(SERIES_FILE)

    def get_series(self) -> list[ParsedSeries]:

        parsed = []

        with pdfplumber.open(self.pdf_path) as pdf:

            for series in self.config["series"]:

                lines = []

                start_page = series["page"]
                end_page = series["end"]

                found_header = False

                for page_number in range(start_page, end_page + 1):

                    page = pdf.pages[page_number - 1]

                    text = page.extract_text()

                    if not text:
                        continue

                    for line in text.splitlines():

                        if not found_header:

                            if series["header"] in line:

                                found_header = True

                                lines.append(line)

                            continue

                        lines.append(line)

                if found_header:

                    parsed.append(

                        ParsedSeries(

                            iracing_series_id=series["iracing_series_id"],

                            header=series["header"],

                            page=start_page,

                            lines=lines

                        )

                    )

        return parsed