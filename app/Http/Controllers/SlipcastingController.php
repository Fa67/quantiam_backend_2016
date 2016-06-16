<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use App\Models\Slipcasting;

class SlipcastingController extends Controller
{
    public function __construct(Request $request)
    {
        $this -> slipcasting_id = substr($request -> input("slipcastID"), 5);

        $this -> slipcast = new Slipcasting();
    }

    public function tolueneData(Request $request)
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

    public function slipData()
    {
        $response = $this -> slipcast -> getSlipcast($this -> slipcasting_id);
        dd($response);
    }
}
