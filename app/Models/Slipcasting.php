<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Requests;

class Slipcasting extends Model
{

    function __construct(array $attributes)
    {   $this -> slipcastID = $attributes['slipcastID'];
        $this -> getcsvData();

        return $this;

    }


    function getcsvData()
    {
        //-- Load csv data from file & explode into array of lines --
                                    // C:\inetpub\wwwroot\quantiam_api\..\..
        $csvData = file_get_contents(__DIR__.'/../../storage/slipcasting/toluenedata/'.$this -> slipcastID.'.csv');
        $lines = explode(PHP_EOL, $csvData);
        $arrays = array();

        foreach ($lines as $line)
        {
            $arrays[] = str_getcsv($line);
        }
        //-- Create object to store response on.
        $response = app() -> make('stdClass');
        $response -> title = $this -> slipcastID;
        $response -> dataset = array();

        $columnCnt = count($arrays[14]);
        $seriesCnt = $columnCnt - 3;
        $seriesObjects = array();

        for ($i = 0; $i < $seriesCnt; $i++)
        {
            $tempObject = app()->make('stdClass');
            $tempObject -> title = $arrays[14][$i+3];
            $tempObject -> x_label = 'Datetime';
            $tempObject -> y_label = 'ppm Toluene';
            $tempObject -> data = array();
            $response -> dataset[] = $tempObject;


        }

        for ($i = 15; $i < count($arrays); $i++)
        {
            if (count($arrays[$i]) > 3) {
                for ($k = 0; $k < $seriesCnt; $k++) {
                    $tempObj = app()->make('stdClass');

                    $tempObj->x = $arrays[$i][1] . " " . $arrays[$i][2];
                    $tempObj->y = $arrays[$i][3 + $k];

                    $response->dataset[$k]->data[] = $tempObj;
                }
            }

        }
        $this -> csvData = $response;
    }
}
