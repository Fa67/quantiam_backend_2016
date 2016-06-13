<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

use Baum\Node;

class User extends Model
{
    function __construct($input, $hierarchy = false)
    {
    	$this -> employeeID	= $input;
    	$this -> getUserData();

        if($hierarchy)
        {
        $this -> getSupervisors();
        $this -> getSubordinates();
		$this -> getGroups();
        }

    	return $this;

    }

    public function getSubordinates()
    {
        $subordinates = Nest::where('employeeID', '=', $this -> employeeID) -> first() -> getDescendants();

        $response = array();
        foreach($subordinates as $obj)
        {
            $user = new User($obj -> employeeID);
            $user -> depth = $obj -> depth;
            $response[] = $user;

        }
  
        $this -> subordinates = $response;
        

        return;
    }


    public function getSupervisors()
    {
       $ancestors = Nest::where('employeeID', '=', $this -> employeeID) -> first() -> getAncestors();

        $response = array();
        foreach($ancestors as $obj)
        {
            $user = new User($obj -> employeeID);
            $response[] = $user;

        }

        $this -> supervisors = $response;

        return;
    }
	
	private function getGroups()
	{
	
		$query = DB::table('group_members')
		->join('group', 'group_members.group_id', '=', 'group.group_id')
		->where('employeeid', '=', $this-> employeeID)
		->get();
		
		$this->groups = $query;
		return;
	
	
	}


    private function getUserData()
    {
    	$id = $this -> employeeID;
       	$temparray = array();
    	$employeeData = DB::table('employees')->select('*')
					    					  ->where('employeeid', '=', $id)
					    					  ->get();

    	foreach($employeeData as $obj)
    	{ 
    		foreach($obj as $key => $value)
    		{
    			$this -> $key  = $value;
    		}
    	}
    	$this -> name = $this->firstname.' '.$this->lastname;
        $this -> depth = Nest::where('employeeID', '=', $this -> employeeID) -> value('depth');

    return;
    }

    function updateUser($key, $value)
    {
    	try 
    	{
    		DB::table('employees')
    			->where('employeeid', '=', $this->employeeID)
    			->update([$key => $value]);

    		$this -> $key = $value;
    		
    	} catch (\Exception $e)
    	{
    		dd($e);
    		return response() -> json(['error' => $e], 400);
    	}
    }

    public function depth()
    {
        $depth = $this -> depth;
        return $depth;
    }
}
