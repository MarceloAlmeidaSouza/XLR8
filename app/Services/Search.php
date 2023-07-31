<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class Search
{

    public static function getNearbyHotels(float $latitude, float $longitude, array $others)
    {

        $bounding_distance = 1;

        $result = DB::select("
            SELECT *, count(*) OVER() AS total FROM `hotels`
            WHERE
            (
                `latitude` BETWEEN ({$latitude} - {$bounding_distance}) AND ({$latitude} + {$bounding_distance})
                AND `longitude` BETWEEN ({$longitude} - {$bounding_distance}) AND ({$longitude} + {$bounding_distance})
            )
            LIMIT {$others['length']} OFFSET {$others['start']};
        ");

        $col = collect((array)$result)
        ->map(function($tag)use($latitude, $longitude, $bounding_distance){
            $tag->distance = (
                (
                    ACOS(
                        SIN($latitude * PI() / 180)
                        *
                        SIN($tag->latitude * PI() / 180)
                        +
                        COS($latitude * PI() / 180)
                        *
                        COS($tag->latitude * PI() / 180)
                        *
                        COS(($longitude - $tag->longitude) * PI() / 180)
                    )
                    *
                    180
                    /
                    PI()
                )
                *
                60
                *
                1.1515
            );
            return $tag;
        })
        ->sortBy($others['order_by'],1, $others['dir']=="desc");

        return ['total'=>$col->first()->total ?? 0, 'data'=> array_values($col->toArray())];
    }
}
