<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;
use DNS2D;

class Steel extends Model
{
    //
	function getSteelList($params)
	{
	
		$query = DB::table('manu_inventory')
				->select(['manu_inventory_id','heat_id']);
				
				
				if(isset($params['like']))
				{
		
				$query->orWhere('manu_inventory_id','Like',$params['like'].'%');
				$query->orWhere('heat_id','Like','%'.$params['like'].'%');
			
				}
				
				if(isset($params['campaign_id']))
				{
				
					$query->Where('campaign_id','=',$params['campaign_id']);
			
				}
				
				$query = $query
				->take(10)
				->orderBy('manu_inventory_id','desc')
				->get();
			
		$temp = array();
		foreach($query as $obj)
		{
		$temp[] = array('id' => $obj->manu_inventory_id, 'text'=>'QMSI-'.$obj->manu_inventory_id.', '.$obj->heat_id);
		
		}
		
		return $temp;
	
	}
}
