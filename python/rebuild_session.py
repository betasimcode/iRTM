import sys
import json


# =========================================================
# SESSION RESULTS PARSER
# =========================================================

def extract_session_results_from_ibt(file_path):

    try:

        with open(file_path, 'rb') as f:

            content = f.read()

        text = content.decode(
            'utf-8',
            errors='ignore'
        )

        lines = text.splitlines()

        results = []

        # =====================================================
        # DRIVERS MAP
        # =====================================================

        drivers_map = {}

        in_drivers = False

        current_driver = None

        for line in lines:

            stripped = line.strip()

            # -------------------------------------------------
            # ENTER DRIVERS BLOCK
            # -------------------------------------------------

            if stripped.startswith('Drivers:'):

                in_drivers = True

                continue

            if not in_drivers:
                continue

            # -------------------------------------------------
            # NEW DRIVER
            # -------------------------------------------------

            if stripped.startswith('- CarIdx:'):

                try:

                    current_driver = {

                        'car_idx': int(
                            stripped.split(':', 1)[1].strip()
                        )
                    }

                except:

                    current_driver = None

                continue

            # -------------------------------------------------
            # USER NAME
            # -------------------------------------------------

            if current_driver and stripped.startswith('UserName:'):

                current_driver['user_name'] = (

                    stripped.split(':', 1)[1].strip()
                )

            # -------------------------------------------------
            # CAR NAME
            # -------------------------------------------------

            elif current_driver and stripped.startswith('CarScreenNameShort:'):

                current_driver['car_name'] = (

                    stripped.split(':', 1)[1].strip()
                )

            # -------------------------------------------------
            # IRATING
            # -------------------------------------------------

            elif current_driver and stripped.startswith('IRating:'):

                try:

                    current_driver['irating'] = int(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['irating'] = None

            # -------------------------------------------------
            # USER ID
            # -------------------------------------------------

            elif current_driver and stripped.startswith('UserID:'):

                try:

                    current_driver['iracing_user_id'] = int(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['iracing_user_id'] = None

            # -------------------------------------------------
            # CAR NUMBER
            # -------------------------------------------------

            elif current_driver and stripped.startswith('CarNumber:'):

                current_driver['car_number'] = (

                    stripped.split(':', 1)[1]
                    .strip()
                    .replace('"', '')
                )

            # -------------------------------------------------
            # LICENSE
            # -------------------------------------------------

            elif current_driver and stripped.startswith('LicString:'):

                current_driver['license_class'] = (

                    stripped.split(':', 1)[1].strip()
                )

            # -------------------------------------------------
            # COUNTRY
            # -------------------------------------------------

            elif current_driver and stripped.startswith('FlairName:'):

                current_driver['country'] = (

                    stripped.split(':', 1)[1].strip()
                )

            # -------------------------------------------------
            # COUNTRY ID
            # -------------------------------------------------

            elif current_driver and stripped.startswith('FlairID:'):

                try:

                    current_driver['country_id'] = int(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['country_id'] = None

            # -------------------------------------------------
            # DIVISION NAME
            # -------------------------------------------------

            elif current_driver and stripped.startswith('DivisionName:'):

                current_driver['division_name'] = (

                    stripped.split(':', 1)[1].strip()
                )

            # -------------------------------------------------
            # DIVISION ID
            # -------------------------------------------------

            elif current_driver and stripped.startswith('DivisionID:'):

                try:

                    current_driver['division_id'] = int(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['division_id'] = None

            # -------------------------------------------------
            # END DRIVER BLOCK
            # -------------------------------------------------

            elif current_driver and stripped.startswith('TeamIncidentCount:'):

                car_idx = current_driver.get('car_idx')

                if car_idx is not None:

                    drivers_map[car_idx] = current_driver

                current_driver = None

        # =====================================================
        # RESULTS POSITIONS
        # =====================================================

        in_results = False

        current_result = {}

        for line in lines:

            stripped = line.strip()

            if stripped.startswith('ResultsPositions:'):

                in_results = True

                continue

            if not in_results:
                continue

            # -------------------------------------------------
            # NEW POSITION
            # -------------------------------------------------

            if stripped.startswith('- Position:'):

                if current_result:

                    results.append(
                        current_result.copy()
                    )

                try:

                    position = int(

                        stripped.split(':', 1)[1].strip()
                    )

                    current_result = {

                        'position': position
                    }

                except:

                    current_result = {}

            # -------------------------------------------------
            # CAR IDX
            # -------------------------------------------------

            elif stripped.startswith('CarIdx:'):

                try:

                    car_idx = int(

                        stripped.split(':', 1)[1].strip()
                    )

                    # ignore invalid

                    if car_idx < 0:

                        current_result = {}

                        continue

                    current_result['car_idx'] = car_idx

                    driver = drivers_map.get(
                        car_idx,
                        {}
                    )

                    current_result['user_name'] = (
                        driver.get('user_name')
                    )

                    current_result['car_name'] = (
                        driver.get('car_name')
                    )

                    current_result['irating'] = (
                        driver.get('irating')
                    )

                    current_result['car_number'] = (
                        driver.get('car_number')
                    )

                    current_result['iracing_user_id'] = (
                        driver.get('iracing_user_id')
                    )

                    current_result['license_class'] = (
                        driver.get('license_class')
                    )

                    current_result['country'] = (
                        driver.get('country')
                    )

                    current_result['country_id'] = (
                        driver.get('country_id')
                    )

                    current_result['division_name'] = (
                        driver.get('division_name')
                    )

                    current_result['division_id'] = (
                        driver.get('division_id')
                    )

                except:

                    pass

            # -------------------------------------------------
            # CLASS POSITION
            # -------------------------------------------------

            elif stripped.startswith('ClassPosition:'):

                try:

                    current_result['class_position'] = int(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_result['class_position'] = None

            # -------------------------------------------------
            # FASTEST TIME
            # -------------------------------------------------

            elif 'FastestTime:' in stripped:

                try:

                    current_result['fastest_time'] = float(

                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_result['fastest_time'] = None

        # =====================================================
        # LAST RESULT
        # =====================================================

        if current_result:

            results.append(
                current_result.copy()
            )

        # =====================================================
        # REMOVE DUPLICATES
        # =====================================================

        unique = {}

        for r in results:

            pos = r.get('position')

            if pos is not None:

                unique[pos] = r

        clean_results = list(
            unique.values()
        )

        return {

            'success': True,

            'results': clean_results
        }

    except Exception as e:

        return {

            'success': False,

            'error': str(e)
        }


# =========================================================
# ENTRYPOINT
# =========================================================

if __name__ == "__main__":

    if len(sys.argv) < 2:

        print(json.dumps({

            'success': False,

            'error': 'Missing IBT path'
        }))

        sys.exit(1)

    ibt_path = sys.argv[1]

    result = extract_session_results_from_ibt(
        ibt_path
    )

    print(
        json.dumps(result)
    )