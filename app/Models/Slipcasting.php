<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Requests;

use DB;

class Slipcasting extends Model
{

    function __construct($slipcastID = null)
    {


        if($slipcastID) {
            $this->slipcastID = $slipcastID;


            $slipdata = $this->getSlipcast($slipcastID);

            foreach($slipdata as $key => $value)
            {
                $this -> $key = $value;


            }


            $this->tolueneData = $this->getcsvData($slipcastID);
        }
        return $this;

    }


    function getcsvData($slipcastID)
    {
        //-- Load csv data from file & explode into array of lines --
        $csvData = file_get_contents(__DIR__.'/../../storage/slipcasting/toluenedata/QMSC-'.$slipcastID.'.csv');
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
        return $response;
    }

    function getSlipcast($slipcastID)
    {   // 'manu_slip_id',
        $manu_slipcasting = DB::table('manu_slipcasting')   -> select ('manu_slipcasting_profile_id',  'datetime', 'room_temp_at_cast AS room_temp', 'slip_temp_at_cast AS slip_temp') -> where('manu_slipcasting_id', '=', $slipcastID) -> first();
        $manu_operators = DB::table('manu_slipcasting_operators') -> join('employees', 'employees.employeeid', '=', 'manu_slipcasting_operators.operator_id')
                            -> select('employees.firstname', 'employees.lastname', 'manu_slipcasting_operators.operator_id AS employeeID') -> where('manu_slipcasting_operators.manu_slipcasting_id', '=', $slipcastID) -> get();
        ($manu_slipcasting -> operators = $manu_operators);

        return $manu_slipcasting;
    }


    function getHumidityData($slipcastID)
    {
        $txt_file = file_get_contents(__DIR__ . "/../../storage/humidity/QMSC-".$slipcastID.".txt");

        $rows = explode("\r\n", $txt_file);
        $response = app() -> make('stdClass');
        // Grab units
   // dd($rows);
        $response -> title = $slipcastID;

        $labels = [3 => 'Temp', 4 => 'Humidity', 5 => 'Dew Point'];
        $units = [3 => 'C', 4 => '%RH', 5 => 'C'];


        $response -> dataset = array();

        for ($k = 3; $k <= 5; $k++) {
            $temp = app()->make('stdClass');
            $temp->title = $labels[$k];
            $temp->x_label = 'datetime';
            $temp->y_label = $units[$k];
            $temp->data = array();
            $response->dataset[] = $temp;

        }

        for($i = 6; $i < count($rows) - 1; $i++)
            {
                for ($k = 0; $k < 3; $k++)
                {
                    $tempRow = preg_split('/[\s]+/', $rows[$i]);
                    $tempObj = app() -> make('stdClass');

                    $tempObj -> x = $tempRow[0].' '.$tempRow[1]. ' '.$tempRow[2];
                    $tempObj -> y = $tempRow[$k+3];

                    $response  -> dataset[$k] -> data[] = $tempObj;

                }


            }





        return $response;

    }

}
