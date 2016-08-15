<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

use Baum\Node;

class User extends Model
{
    function __construct($input = null, $hierarchy = false)
    {
	
	
		if($input)
			{
			$this -> employeeID	= $input;
			$this -> getUserData();
			$this -> getGroups();
			$this -> permissions = $this -> getPermissions();

			if($hierarchy)
			{
			$this -> getSupervisors();
			$this -> getSubordinates();
			
			}
		}
    	return $this;

    }
	


    public function  checkGroupMembership($groupID)
    {


        foreach($this->groups as $group)
        {

            if($group->group_id == $groupID)
            {

                return true;
            }

        }

        return false; 

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
	
	public function getGroups()
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

        $temp = Nest::where('employeeID', '=', $this -> employeeID) -> first();

            $this->depth = $temp->depth;
            $this->id = $temp->id;
            $this->tag = $temp->tag;
			
			
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
    		return response() -> json(['error' => $e], 400);
    	}
    }

    public function depth()
    {
        $depth = $this -> depth;
        return $depth;
    }
	
	
	function getPermissions()
	{
		$returnObj = array();
	
		
		//get user permissions
	
	
		$query = DB::table('permissions_employees')
			->select(['permissions.permission_id','permissions.permission_name','permissions.permission_description'])
			->leftJoin('permissions', 'permissions.permission_id','=','permissions_employees.permission_id')
			->where('employee_id',$this -> employeeID)
			->get();
		
		foreach($query as $obj)
		{

				$obj->derived_from_group = 0;
				$returnObj[] = $obj;
				$permObj[] = $obj->permission_id;
			
		}
		
		
			//get permissions from groups
		foreach($this->groups as $groupObj)
		{
		  $groupIDs [] = $groupObj->group_id;
		}
		
		$query = DB::table('permissions_groups')
		->select(['permissions.permission_id','permissions.permission_name','permissions.permission_description','group.group_name'])
		->leftJoin('permissions', 'permissions.permission_id','=','permissions_groups.permission_id')
		->leftJoin('group', 'group.group_id','=','permissions_groups.group_id')
		->whereIn('group.group_id',$groupIDs)
		->get();
		
		foreach($query as $obj)
		{
		
			if(!in_array($obj->permission_id, $permObj))
			{
				$obj->derived_from_group = 1;
				$returnObj[] = $obj;
			}
		}
	
		
		return $returnObj;
	}
	
	
	function getUserList($params)
	{
	
		$query = DB::table('employees')
				->select(['employeeid','firstname','lastname']);
				
				
				if(!empty($params['like']))
				{
					$query->where('firstname','Like',$params['like'].'%');
                    $query->orWhere('lastname', 'Like', $params['like'].'%');
			
				}
				
				if(!empty($params['active']))
				{
					$query->whereNull('leavedate');
			
				}
				
				$query = $query
				->take(30)
				->orderBy('employeeid','desc')
				->get();
				
	
				
				
			
		$temp = array();
		foreach($query as $obj)
		{
		$temp[] = array('id' => $obj->employeeid, 'text'=>$obj->employeeid.' - '.$obj->firstname.' '.$obj->lastname);
		
		}
		
		return $temp;
	
	}
	
	
	function checkPermissions($permissionIDArray) //accepts arrays or IDs
	{
		
		
        foreach($this->permissions as $obj)
        {
			
			if(is_array($permissionIDArray))
			{
				// array of permissions
				if(in_array($obj->permission_id,$permissionIDArray))
				{
					$confirmedArray[] = $obj->permission_id;
				}
			
			
			}
			else
			{
				/// single permission
				if($obj->permission_id == $permissionIDArray)
				{
					return true;
				}
			}

        }
		
		
		if(is_array($permissionIDArray))
		{
			sort($permissionIDArray);
			sort($confirmedArray);
			if($permissionIDArray == $confirmedArray) return true;
		
		}
		
        return false; 
		
	
		
	}
	
}
