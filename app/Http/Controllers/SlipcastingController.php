<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use App\Models\Slipcasting;

class SlipcastingController extends Controller
{
    public function getcsv(Request $request)
    {
        $response = new Slipcasting(['slipcastID' => $request -> input('slipcastID')]);

        return response() -> json (["csv data:" => $response], 200);
    }
}
