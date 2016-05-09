<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

use Baum\Node;

class User extends Model
{
    function __construct($input, $hierarchy = false)
    {
    	$this -> employeeid	= $input;
    	$this -> getUserData();
       
        if($hierarchy)
        {
        $this -> getSupervisors();
        $this -> getSubordinates();
        }

    	return $this;

    }

    public function getSubordinates()
    {

       
        $subordinates = Nest::where('employeeID', '=', $this -> employeeid) -> first() -> getDescendants();

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
        $ancestors = Nest::where('employeeID', '=', $this -> employeeid) -> first() -> getAncestors();

        $response = array();
        foreach($ancestors as $obj)
        {
            $user = new User($obj -> employeeID);
            $user -> depth = $obj -> depth;
            $response[] = $user;

        }

        $this -> supervisors = $response;

        return;
    }


    private function getUserData()
    {
    	$id = $this -> employeeid;

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

    return;
    }

    function updateUser($key, $value)
    {
    	try 
    	{
    		DB::table('employees')
    			->where('employeeid', '=', $this->employeeid)
    			->update([$key => $value]);

    		$this -> $key = $value;
    		
    	} catch (\Exception $e)
    	{
    		dd($e);
    		return response() -> json(['error' => $e], 400);
    	}
    }
}
