<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use App\Models\Slipcasting;

class SlipcastingController extends Controller
{
    public function getcsv(Request $request)
    {
        try
        {
            $response = new Slipcasting($request -> input('slipcastID'));
            return response() -> json ($response -> tolueneData, 200);
        } catch (\Exception $e)
        {
            return response() -> json (['error' => "could not find toluene data"], 404);
        }

    }
}
