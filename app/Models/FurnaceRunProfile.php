<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FurnaceRunProfile extends Model
{
      //
	function getFurnaceProfileList ($params)
	{
	
		$query = DB::table('manu_furnace_runs_profile')
		->select(['profile_id as id', 'profile_name as text']); // set up intial table

		/// option to return by type
		if(isset($params['furnace_run_type_id']) && $params['furnace_run_type_id'] > 0)
		{
		
			$query->where('furnace_run_type_id','=',$params['furnace_run_type_id']);
		}
		
		if(isset($params['like']))
		{
		
			$query->where('profile_name','Like','%'.$params['like'].'%');
		}
		
			
	
		$result = $query
		->take(20)
		->orderBy('profile_id','desc')
		->get();
		
		return $result;
		
	}
}
