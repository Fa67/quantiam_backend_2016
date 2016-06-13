<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;

class GroupController extends Controller
{
    //
		
	public function removeUserFromGroup(Request $request, $groupID, $userID)
	{
		$input = $request->all();
		//dd($input);
		
		
		if(isset($groupID) && isset($userID))
		{	
	
		$query = DB::table('group_members')
		->where('employeeid', '=', $userID)
		->where('group_id', '=', $groupID)
		->delete();
	
	
			return response() -> json(['success' => 'User '.$userID.' was removed from group '.$groupID.''], 200);
		}
		else
		{
			return response() -> json(['error' => 'You did not provide the correct parameters'], 400);
		}
		
	
	}
	
	
	
	public function addUserToGroup(Request $request, $groupID, $userID)
		{
			$input = $request->all();
			//dd($input);
			
			
			if(isset($groupID) && isset($userID))
			{	
		
			$query = DB::table('group_members')
			->insert(['employeeid' => $userID, 'group_id' => $groupID]);
		
		
				return response() -> json(['success' => 'User '.$userID.' was added to group '.$groupID.''], 200);
			}
			else
			{
				return response() -> json(['error' => 'You did not provide the correct parameters'], 400);
			}
			
		
		}

	
}
