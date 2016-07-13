<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Requests;


Use App\Models\SlipcastingProfile;

use DB;
use DNS2D;

class Slipcasting extends Model
{

    function __construct($slipcastID = null)
    {


        if($slipcastID) {
            $this->buildSlipcastObj($slipcastID,null);
        }
        return $this;

    }
	
	function buildSlipcastObj($slipcastID,$graphs = null)
	{
	
		$this->identifier =  "QMSC-".$slipcastID;
		$temp = $this->getSlipcast($slipcastID);
		
		foreach($temp as $key=>$value)
		{
			$this->$key = $value;
		
		}
		$this->steel = $this->getSteel($slipcastID);
		$this->operators = $this->getOperators($slipcastID);
		
		if($this->manu_slipcasting_profile_id){
		$this->profile = new SlipcastingProfile($this->manu_slipcasting_profile_id);
		}
		$this->datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMSC-".$slipcastID, "DATAMATRIX",8,8);
		
		
		
		
		if($graphs)
		{
			$this->tolueneData = $this->getcsvData($slipcastID);
		
		}
	
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
        $response -> title = 'QMSC-'.$slipcastID;
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

        for ($i = 15; $i < count($arrays); $i += 10)
        {
            if (count($arrays[$i]) > 3) {
                for ($k = 0; $k < $seriesCnt; $k++) {
                    $tempObj = array();

                    $tempObj[0] = strtotime($arrays[$i][1] . " " . $arrays[$i][2])*1000;
                    $tempObj[1] = (float) $arrays[$i][3 + $k];

                    $response->dataset[$k]->data[] = $tempObj;
                }
            }

        }
        return $response;
    }

    function getSlipcast($slipcastID)
    {   // 'manu_slip_id',
        $manu_slipcasting = DB::table('manu_slipcasting')   ->
		select ('*') -> where('manu_slipcasting_id', '=', $slipcastID) -> first();
        
	

        return $manu_slipcasting;
    }


    function getHumidityData($slipcastID)
    {
        $txt_file = file_get_contents(__DIR__ . "/../../storage/slipcasting/humiditydata/QMSC-".$slipcastID.".txt");

        $rows = explode("\r\n", $txt_file);
        $response = app() -> make('stdClass');
        // Grab units
        $response -> title = substr($rows[1], 5);

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

        for($i = 6; $i < count($rows) - 1; $i += 10)
            {
                for ($k = 0; $k < 3; $k++)
                {
                    $tempRow = preg_split('/[\s]+/', $rows[$i]);
                    $tempObj = array();

                    $tempObj[0] = strtotime($tempRow[0].' '.$tempRow[1]. ' '.$tempRow[2])*1000;
                    $tempObj[1] = (float) $tempRow[$k+3];

                    $response  -> dataset[$k] -> data[] = $tempObj;

                }


            }





        return $response;

    }

   
	function getSteel ($slipcastID,$inventory_id = null)
	{
		$query = DB::table('manu_slipcasting_steel')
		->select('*')
		->join('manu_inventory','manu_slipcasting_steel.inventory_id','=','manu_inventory.manu_inventory_id')
		->where('manu_slipcasting_id','=',$slipcastID);
		
		if($inventory_id)
		{
		
		$query -> where('inventory_id','=',$inventory_id);
		}
		
		
		$query = $query->get();
		
		
		foreach($query as $obj)
		{
			$obj->datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMIS-".$obj->inventory_id, "DATAMATRIX",8,8);
			$obj->identifier =  "QMIS-".$obj->inventory_id;
			$obj->container_weights = $this->getSteelContainerWeight($obj->inventory_id);
		}

		return $query;

	}

	
	
	
   function addSteel($slipcast_id, $inventory_id)
    {
		
		
        $id = DB::table('manu_slipcasting_steel')->insert(['inventory_id' => $inventory_id, 'manu_slipcasting_id' => $slipcast_id]);

		$steel = $this->getSteel($slipcast_id, $inventory_id);
		
		
		
        return ($steel[0]);
    }



    function deleteSteel($slipcastID, $inventory_id)
    {
        DB::table('manu_slipcasting_steel')->where('manu_slipcasting_id', '=', $slipcastID)->where('inventory_id', '=', $inventory_id)->delete();

        return ('Tube '.$inventory_id.' deleted.');
    }



    function editSteel($params, $slipcast_id, $inventory_id)
    {
       $response =  DB::table('manu_slipcasting_steel')->where('manu_slipcasting_id', '=', $slipcast_id)->where('inventory_id', '=', $inventory_id)->update($params);
        return $response;
    }



    function getOperators($slipcastID)
    {

		$manu_operators = DB::table('manu_slipcasting_operators') -> join('employees', 'employees.employeeid', '=', 'manu_slipcasting_operators.operator_id')
                            -> select('employees.firstname', 'employees.lastname', 'manu_slipcasting_operators.operator_id AS employeeID') -> where('manu_slipcasting_operators.manu_slipcasting_id', '=', $slipcastID) -> get();
        return $manu_operators;
	
    }


	  function addOperator($slipcastID, $operatorID)
    {
		$params = array('manu_slipcasting_id' => $slipcastID, 'operator_id' => $operatorID);
		
		$id = DB::table('manu_slipcasting_operators')
		->insertGetID($params);
		
		$manu_operators = DB::table('manu_slipcasting_operators') -> join('employees', 'employees.employeeid', '=', 'manu_slipcasting_operators.operator_id')
		-> select('employees.firstname', 'employees.lastname', 'manu_slipcasting_operators.operator_id AS employeeID')
		-> where('manu_slipcasting_operators.manu_slipcasting_id', '=', $slipcastID)
		-> where('manu_slipcasting_operators.operator_id', '=', $operatorID)
		-> first();
        
		return $manu_operators;
	
		
    }
	

    function removeOperator($slipcastID, $operatorID)
    {
		$query = DB::table('manu_slipcasting_operators')
		->where('manu_slipcasting_id', '=',$slipcastID)
		->where('operator_id', '=',$operatorID)
		->delete();
		
		return true;
    }



    function editSlipcast($params, $slipcast_id)
    {
        DB::table('manu_slipcasting')->where('manu_slipcasting_id', '=', $slipcast_id)->update($params);

        return 'Successfully edited.';
    }


    function createSlipcast()
    {
	
		$params = array();
        $id = DB::table('manu_slipcasting')->insertGetID($params);

        $response = App() -> make('stdClass');
        $response -> id = $id;

        return $response;
    }
	
	function deleteSlipcast($slipcastID){
	
	
	$query =  DB::table('manu_slipcasting')
	->where('manu_slipcasting_id','=', $slipcastID)
	->delete();
	
	
	return;
	
	
	}
	
	function getSteelContainerWeight($inventoryID)
	{
	
	
		$query = DB::table('manu_slipcasting_steel_container_weights')
			->where('inventory_id', '=', $inventoryID)
			->get();
	
			return $query;
	}
	
	
	function editSteelContainerWeight($slipcast_id, $inventoryID, $containerID, $input)
	{
	
			$query = DB::table('manu_slipcasting_steel_container_weights')
			->where('container_id', '=', $containerID)
			->where('inventory_id', '=', $inventoryID)
			->get();
			
			if(count($query) > 0)
			{
			// update
			
			$query = DB::table('manu_slipcasting_steel_container_weights')
			->where('container_id', '=', $containerID)
			->where('inventory_id', '=', $inventoryID)
			->update($input);
			
			}
			else
			{
			$input['inventory_id'] = $inventoryID;
			$input['container_id'] = $containerID;
			
			$query = DB::table('manu_slipcasting_steel_container_weights')
			->insertGetID($input);
			}
			
		$query = DB::table('manu_slipcasting_steel_container_weights')
			->where('container_id', '=', $containerID)
			->where('inventory_id', '=', $inventoryID)
			->first();
			
			
	
		return $query;
	}

}
