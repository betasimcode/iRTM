<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Car;
use App\Models\Circuit;
use App\Services\StrategyService;

class FuelController extends Controller
{

    /* =========================
        API: circuitos por coche
       ========================= */
    public function tracks($carName)
    {
        $tracks = DB::table('stints')
            ->whereRaw('TRIM(LOWER(car)) = TRIM(LOWER(?))', [$carName])
            ->select(DB::raw('TRIM(track) as track'))
            ->groupBy('track')
            ->orderBy('track')
            ->pluck('track')
            ->values();

        return response()->json($tracks);
    }

    /* =========================
        Página calculadora
       ========================= */
    public function index(Request $request)
    {
        $cars = Car::orderBy('name')->pluck('name');
        $tracks = Circuit::orderBy('name')->pluck('name');

        // sin parámetros → solo formulario
        if (!$request->has('minutes')) {
        return view('fuel.index', [
                'cars' => $cars,
                'tracks' => $tracks,
                'calculated' => false
            ]);
        }

        // -------- Datos usuario --------
        $car = trim($request->car);
        $track = trim($request->track);
        $minutes = (float)$request->minutes;
        $extra_laps = (int) $request->extra_laps;
        $traffic_loss = (float)$request->traffic_loss;

        if (!$car || !$track || $minutes <= 0) {
            return view('fuel.index', [
                'cars' => $cars,
                'tracks' => $tracks,
                'calculated' => true,
                'error' => 'Selecciona coche, circuito y duración'
            ]);
        }

        // // -------- Obtener stints válidos --------
        // $stints = DB::table('stints')
        //     ->whereRaw('TRIM(LOWER(car)) = TRIM(LOWER(?))', [$car])
        //     ->whereRaw('TRIM(LOWER(track)) = TRIM(LOWER(?))', [$track])
        //     ->where('laps','>',0)
        //     ->where('duration_seconds','>',0)
        //     ->get();

        // if ($stints->count() == 0) {
        //     return view('fuel.index', [
        //         'cars' => $cars,
        //         'tracks' => $tracks,
        //         'selectedCar' => $car,
        //         'selectedTrack' => $track,
        //         'minutes' => $minutes,
        //         'calculated' => true,
        //         'error' => 'No hay datos suficientes para este combo'
        //     ]);
        // }

        //  // -------- Consumo y tiempos --------
        // $fuel_per_lap = $stints->avg('avg_fuel');
        // $avgLapTime = $stints->avg(fn($s)=>$s->duration_seconds/$s->laps);

        // $estimated_laps = floor(($minutes*60)/$avgLapTime);

        // // añadir contingencia elegida por el piloto
        // $total_laps = $estimated_laps + $extra_laps;

        // $fuel = round($total_laps * $fuel_per_lap,1);



        // // -------- Capacidad del tanque --------
        // $tank = Car::whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$car.'%'])
        //     ->value('tank_capacity');

        // $strategy=null;
        // $pit_laps=[];
        // $stint_plan=[];

        // if($tank){

        //     /* ===== Estrategia repostajes ===== */

        //     if($fuel <= $tank){

        //         $strategy=[
        //             'stops'=>0,
        //             'start_fuel'=>round($fuel,1),
        //             'refuels'=>[]
        //         ];

        //     }else{

        //         $remaining = $fuel - $tank;
        //         $refuels=[];

        //         while($remaining > 0){
        //             $add = min($tank,$remaining);
        //             $refuels[] = round($add,1);
        //             $remaining -= $add;
        //         }

        //         $strategy=[
        //             'stops'=>count($refuels),
        //             'start_fuel'=>$tank,
        //             'refuels'=>$refuels
        //         ];
        //     }

        //     /* ===== Ventanas de parada ===== */

        //     $max_laps_per_stint = floor($tank / $fuel_per_lap);

        //     $safety_margin_laps = 1;
        //     $pit_earliest = $max_laps_per_stint - $safety_margin_laps;
        //     $pit_latest   = $max_laps_per_stint;

        //     $remaining_laps = $total_laps;

        //     while($remaining_laps > 0){
        //         $run = min($max_laps_per_stint, $remaining_laps);
        //         $stint_plan[] = $run;
        //         $remaining_laps -= $run;
        //     }
        //     $safety_margin_laps = 1;

        //     $pit_earliest = $max_laps_per_stint - $safety_margin_laps;
        //     $pit_latest   = $max_laps_per_stint;

        //     $pit_windows = [];
        //     $total = 0;

        //     for($i=0; $i<count($stint_plan)-1; $i++){

        //         $start = $total + $pit_earliest;
        //         $end   = $total + $pit_latest;

        //         $pit_windows[] = [
        //             'from'=>$start,
        //             'to'=>$end
        //         ];

        //         $total += $stint_plan[$i];
        //     }

        //     $total=0;
        //     for($i=0;$i<count($stint_plan)-1;$i++){
        //         $total += $stint_plan[$i];
        //         $pit_laps[]=$total;
        //     }
        // }

        // /* =====================================
        //     UNDERCUT vs OVERCUT REALISTA
        //     ===================================== */

        //     // ganancia media por vuelta al parar antes
        //     $undercut_per_lap = 1.2;   // GT4 típico

        //     // vueltas donde existe ventaja
        //     $undercut_laps = 3;

        //     // tiempo ganado al rival
        //     $undercut_gain = ($undercut_per_lap * $undercut_laps) + $traffic_loss;

        //     // decisión
        //     if($undercut_gain > 5)
        //         $strategy_call = "UNDERCUT claro";
        //     elseif($undercut_gain > 2)
        //         $strategy_call = "UNDERCUT posible";
        //     elseif($undercut_gain > -2)
        //         $strategy_call = "VENTANA NEUTRA";
        //     else
        //         $strategy_call = "OVERCUT preferible";

        $result = StrategyService::calculate(
            $car,
            $track,
            $minutes,
            $extra_laps,
            $traffic_loss
        );

        if(isset($result['error'])){
            return view('fuel.index', [
                'cars'=>$cars,
                'tracks'=>$tracks,
                'selectedCar'=>$car,
                'selectedTrack'=>$track,
                'minutes'=>$minutes,
                'calculated'=>true,
                'error'=>$result['error']
            ]);
        }
        /// ENVIO DE DATOS
        return view('fuel.index', array_merge([

            'cars'=>$cars,
            'tracks'=>$tracks,
            'selectedCar'=>$car,
            'selectedTrack'=>$track,
            'minutes'=>$minutes,
            'calculated'=>true,
            'extra_laps'=>$extra_laps,
            'traffic_loss'=>$traffic_loss,

            // 'fuel'=>$fuel,
            // 'fuel_per_lap'=>round($fuel_per_lap,2),
            // 'estimated_laps'=>$estimated_laps,   // mostrar dato real
            // 'total_laps'=>$total_laps,           // estrategia
            // 'pit_laps'=> $pit_laps,
            // 'stint_plan'=>$stint_plan,
            // 'pit_windows'=>$pit_windows,
            // 'strategy_call'=>$strategy_call,
            // 'undercut_gain'=>round($undercut_gain,2),
            // 'strategy' => $strategy

        ], $result));
    }


}
