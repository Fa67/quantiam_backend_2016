<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\User;
use App\Models\Nest;

use DB;

use Baum\Node;
use Baum\Extensions\Query\Builder; 

use Lcobucci\JWT\Parser;

class userController extends Controller
{


	public function searchUsers(Request $request, $isActive = true)
	{	
		// employeeID | email | ldap | firstname | lastname | title | compensation 
		if (null != ($request -> input('isActive')) &&( ($request -> input('isActive')) == 'true' || ($request -> input('isActive')) == 'false') )
		{	
			$isActive = filter_var(($request -> input('isActive')), FILTER_VALIDATE_BOOLEAN);
		}
		else if (null != ($request -> input('isActive')) &&( ($request -> input('isActive')) != 'true' || ($request -> input('isActive')) != 'false') )
		{
			return response () -> json(['error' => 'Improper input of employee active status'], 406);
		}

		// setting search terms
		$search = $request -> input('search');
			$name = explode(' ', $search);
			$firstname = $name[0];
				$lastname = $name[0];
					if (isset($name[1]))
					{
						$lastname = $name[1];
					}
		try
		{
			$response = DB::table('employees')	->where('employeeID', 'like', $search.'%')
												->orWhere('email', 'like', $search.'%')
												->orWhere('ldap_username', 'like', '%'.$search.'%')
												->orWhere('firstname', 'like', '%'.$firstname.'%')
												->orWhere('lastname', 'like', '%'.$firstname.'%')
												->orWhere('lastname', 'like', '%'.$lastname.'%')
												->orWhere('compensation', 'like', $search.'%')
												->orderBy('employeeid')
												->get();

			if (isset($response[0]))
			{
				return response() -> json(['results' => $response], 200);
			}
			else {
				return response() -> json(['results' => "Your search for -- ".$search." -- returned no results."], 200);
			}
		}
		catch (\Exception $e)
		{
			return response() -> json(['error' => $e]);
		}
	}


	public function userInfo($employee_id, $truth = true)
	{
		$employeeID = $employee_id;

		$response = new User($employeeID, $truth);
		return $response;
	}

	public function getUsers(Request $request)
	{
		$response = DB::table('employees')->leftjoin('hierarchy', 'hierarchy.employeeID', '=', 'employees.employeeID')->get();
		$response =  (json_decode(json_encode(($response)), true));
		
		return ($response);
	}


    public function newUser(Request $request)
	{
		if ($request -> root == false)
		{
			$newUser = Nest::create(['employeeID' => $request -> employeeID]);
			$parent = Nest::where('employeeID', '=', $request -> supervisorID) -> first();

			$newUser -> makeChildOf($parent);

			dd($newUser . " created with supervisor " . $parent);
		}
		else 
		{
			$newRoot = Nest::create(['employeeID' => $request -> employeeID]);
			dd("New root created with employeeID = ".$request -> employeeID);
		}


	}


	public function editUser(Request $request)
	{
		$params = $request -> all();
		$key = ($params['key']);
		$value = $params['value'];

		if ($key == 'ldap_username' || $key == 'email')
		{
			return response() -> json(['error' => 'Cannot edit ' . $key], 403);
		}
		else if ($key == 'compensation')
		{
			if ($value != 'Temporary' || $value != 'Hourly' || $value != 'Salary')
			{
				return response() -> json(['error' => "Improper input for compensation: '".$value."'"], 400);
			}
		}

		DB::table('employees')->where('email', $params['email'])
					->update([$key => $value]);

		return response() -> json (['success' => 'Changed '.$params['key'].' to '.$params['value']], 200);

	}


	public function moveUser(Request $request)
	{
		$nodeToMove = Nest::where('employeeID', '=', $request -> input('employeeID'))->first();
		$nodeParent = Nest::where('employeeID', '=', $request -> input('newSupervisorID')) -> first();
		$nodeToMove -> makeChildOf ($nodeParent);
		dd($nodeToMove.' moved under '.$nodeParent);
	}


	public function viewTree(Request $request)
	{
		if ($request -> idvalue)
		{
			$tree = Nest::where($request->input('idtag'), $request -> input('idvalue')) -> first() -> getDescendantsAndSelf() -> toHierarchy();
		}
		else 
		{
			$tree = Nest::where('parent_id', '=', null)->first()->getDescendantsAndSelf();
		}

		return $tree;
	}

	public function identifyUser(Request $request)
	{
       		$token = $request->header('authorization');
        		$token = str_replace('Bearer ', '', $token); //  Removes "Bearer " from token
        		$token = (new Parser())->parse((string) $token); // Parses from a string

        		$employeeID = $token -> getClaim('employeeID');
		
		$response = $this -> userInfo($employeeID, true);

		return response() -> json($response, 200);
	}

	public function specificUser($employeeID)
	{
		$response = $this -> userInfo($employeeID, true);

		return response() -> json($response, 200);
	}


	
	
	

	
	
	
}