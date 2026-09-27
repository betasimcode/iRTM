<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Car;

class StrategyService
{
    public static function calculate($car, $track, $minutes, $extra_laps = 1, $traffic_loss = 0)
    {
        // ===== STINTS =====
        $stints = DB::table('stints')
            ->whereRaw('TRIM(LOWER(car)) = TRIM(LOWER(?))', [$car])
            ->whereRaw('TRIM(LOWER(track)) = TRIM(LOWER(?))', [$track])
            ->where('laps','>',0)
            ->where('duration_seconds','>',0)
            ->get();

        if ($stints->count() == 0) {
            return ['error'=>'No hay datos suficientes'];
        }

        // ===== RITMO REPRESENTATIVO =====
        $valid = $stints->sortBy('avg_lap')->values();
        $take = max(1, floor($valid->count()*0.4));
        $representative = $valid->take($take);

        $fuel_per_lap = $representative->avg('avg_fuel');
        $avgLapTime = $representative->avg('avg_lap');

        // ===== VUELTAS =====
        $estimated_laps = floor(($minutes*60)/$avgLapTime);
        $total_laps = $estimated_laps + $extra_laps;
        $fuel = round($total_laps * $fuel_per_lap,1);

        // ===== TANQUE =====
        $tank = Car::whereRaw('LOWER(name) LIKE LOWER(?)',['%'.$car.'%'])
            ->value('tank_capacity');

        $strategy=null;
        $stint_plan=[];
        $pit_windows=[];

        if($tank){

            // repostajes
            if($fuel <= $tank){
                $strategy=[
                    'stops'=>0,
                    'start_fuel'=>round($fuel,1),
                    'refuels'=>[]
                ];
            }else{
                $remaining = $fuel - $tank;
                $refuels=[];
                while($remaining > 0){
                    $add=min($tank,$remaining);
                    $refuels[]=round($add,1);
                    $remaining-=$add;
                }
                $strategy=[
                    'stops'=>count($refuels),
                    'start_fuel'=>$tank,
                    'refuels'=>$refuels
                ];
            }

            // plan stints
            $max_laps_per_stint=floor($tank/$fuel_per_lap);
            $remaining_laps=$total_laps;

            while($remaining_laps>0){
                $run=min($max_laps_per_stint,$remaining_laps);
                $stint_plan[]=$run;
                $remaining_laps-=$run;
            }

            // ventanas
            $safety_margin_laps=1;
            $pit_earliest=$max_laps_per_stint-$safety_margin_laps;
            $pit_latest=$max_laps_per_stint;

            $total=0;
            for($i=0;$i<count($stint_plan)-1;$i++){
                $pit_windows[]=[
                    'from'=>$total+$pit_earliest,
                    'to'=>$total+$pit_latest
                ];
                $total+=$stint_plan[$i];
            }
        }

        // ===== UNDERCUT =====
        $undercut_per_lap=1.2;
        $undercut_laps=3;
        $undercut_gain=($undercut_per_lap*$undercut_laps)+$traffic_loss;

        if($undercut_gain>5)      $call="UNDERCUT claro";
        elseif($undercut_gain>2)  $call="UNDERCUT posible";
        elseif($undercut_gain>-2) $call="VENTANA NEUTRA";
        else                      $call="OVERCUT preferible";

        if(!is_numeric($fuel_per_lap) || $fuel_per_lap <= 0){
            return ['error'=>'Datos inválidos de consumo'];
        }

        if(!is_numeric($avgLapTime) || $avgLapTime <= 0){
            return ['error'=>'Datos inválidos de tiempos'];
        }

        return [
            'fuel'=>$fuel,
            'fuel_per_lap'=>round($fuel_per_lap,2),
            'estimated_laps'=>$estimated_laps,
            'total_laps'=>$total_laps,
            'strategy'=>$strategy,
            'stint_plan'=>$stint_plan,
            'pit_windows'=>$pit_windows,
            'strategy_call'=>$call,
            'undercut_gain'=>round($undercut_gain,2)
        ];
    }
}
