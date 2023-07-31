<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Search;

class HotelsController extends Controller
{
    public function getNearbyHotels(Request $request){
        $data = $request->all();

        return response()->json(Search::getNearbyHotels($data['latitude'], $data['longitude'], $data));
    }
}
