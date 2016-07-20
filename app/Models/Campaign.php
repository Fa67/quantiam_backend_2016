<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Campaign extends Model
{
    //
	
	function getCampaignList ($params)
	{
	
		$query = DB::table('manu_campaign')
		->select(['campaign_id as id', 'campaign_name as text']); // set up intial table
		
		
		
		if(isset($params['active']) && $params['active'] == true)
		{
			$query->where('campaign_active','=',1); //conditional based on variable presence.
		}
		
		if(isset($params['like']))
		{
		
			$query->where('campaign_name','Like','%'.$params['like'].'%');
		}
		
			
	
		$result = $query
		->take(10)
		->orderBy('campaign_id','desc')
		->get();
		
		return $result;
		
	}
}
