<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Furnace extends Model
{
    //
	function getFurnaceList ($params)
	{
	
		$query = DB::table('manu_furnace')
		->select(['furnace_id as id', 'furnace_name as text']); // set up intial table

		if(isset($params['like']))
		{
		
			$query->where('furnace_name','Like','%'.$params['like'].'%');
		}
		
			
	
		$result = $query
		->take(20)
		->orderBy('furnace_id','desc')
		->get();
		
		return $result;
		
	}
}

