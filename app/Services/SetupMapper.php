<?php

namespace App\Services;

use App\Models\SetupItemDefinition;

class SetupMapper
{
    private static function normalizeKey($key)
    {
        $parts = explode('.', $key);

        // detectar esquina (fl, fr, rl, rr)
        $corner = null;

        foreach ($parts as $part) {
            if (in_array($part, ['fl','fr','rl','rr'])) {
                $corner = $part;
            }
        }

        $last = end($parts);

        return $corner ? "{$last}_{$corner}" : $last;
    }

    public static function map($values)
    {
        $zones = [
            'front_aero' => [],
            'rear_aero' => [],
            'front_dampers' => [],
            'fl_dampers' => [],
            'fr_dampers' => [],
            'front_chassis' => [],
            'rear_chassis' => [],
            'rear_dampers' => [],
            'rl_dampers' => [],
            'rr_dampers' => [],
            'main_chassis' => [],
            'drive_train'=>[],
            'diff'=>[],
            'data'=>[],
            'fl_tyre' => [],
            'fr_tyre' => [],
            'rl_tyre' => [],
            'rr_tyre' => [],
            'fl_susp' => [],
            'fr_susp' => [],
            'rl_susp' => [],
            'rr_susp' => [],
            'telemetry_FL' => [],
            'telemetry_FR' => [],
            'telemetry_RL' => [],
            'telemetry_RR' => [],
            'telemetry_tyre_FL' => [],
            'telemetry_tyre_FR' => [],
            'telemetry_tyre_RL' => [],
            'telemetry_tyre_RR' => [],
            'config' => []
        ];
        
        // 🔥 ESTO FALTABA
        $definitions = SetupItemDefinition::all()->keyBy('raw_key');

        foreach ($values as $item) {

            // 1️⃣ obtener key
            $key = $item->key ?? $item['key'] ?? null;
        
            if (!$key) {
                dump('ITEM SIN KEY', $item);
                continue;
            }
        
            // 2️⃣ obtener definición PRIMERO
            $def = $definitions[$key] ?? null;
        
            if (!$def || !$def->zone) {
                continue;
            }
        
            // 3️⃣ asignar position desde DB
            $position = strtolower($def->position ?? '');
        
            $item->position = $position;
        
            // 4️⃣ normalizar base key (SIN posición)
            $baseKey = self::normalizeKey($key);
        
            // 5️⃣ construir normalized_key
            $normalizedKey = $position
                ? "{$baseKey}_{$position}"
                : $baseKey;
        
            $item->normalized_key = strtolower($normalizedKey);
        
            // 6️⃣ mapping UI
            $item->mapped_label = $def->label ?? $key;
            $item->mapped_zone  = $def->zone;
        
            $zones[$def->zone][] = $item;
        }

        foreach ($zones as $zone => $items) {

            $unique = [];
        
            foreach ($items as $item) {
        
                $label = $item->mapped_label ?? $item->key;
        
                // 🔥 SOLO 1 POR LABEL EN ESTA ZONA
                if (!isset($unique[$label])) {
                    $unique[$label] = $item;
                }
        
                // opcional: si quieres priorizar uno sobre otro, aquí es donde se haría
            }
        
            $zones[$zone] = array_values($unique);
        }

        foreach ($zones as $zone => $items) {

            $zones[$zone] = collect($items)
                ->sortBy(function ($item) {
        
                    $label = $item->mapped_label ?? $item->label ?? '';
        
                    // 🔢 si hay número → ordenar por número
                    if (preg_match('/\d+/', $label, $matches)) {
                        return (int) $matches[0];
                    }
        
                    // 🔤 fallback → alfabético
                    return $label;
        
                })
                ->values(); // reindexa
        }

        // 🔥 detectar zonas con contenido
        $activeZones = collect($zones)
        ->filter(fn($items) => count($items) > 0)
        ->keys()
        ->values()
        ->toArray();

        $flags = [
            'has_front_dampers' => !empty($zones['front_dampers']),
            'has_rear_dampers'  => !empty($zones['rear_dampers']),
        ];
        
        $normalized = [];

        foreach ($values as $item) {

            $key = $item->normalized_key ?? null;

            if (!$key) continue;

            $normalized[$key] = $item->value;
        }
        return [
            'zones' => $zones,
            'active_zones' => $activeZones,
            'flags' => $flags,
            'values' => $normalized,
        ];
    }






}