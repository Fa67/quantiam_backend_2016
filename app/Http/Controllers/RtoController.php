<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\User;
use App\Models\RTO;
use App\Models\Nest;
use App\Models\JWT;

use DB;

use Baum\Node;
use Baum\Extensions\Query\Builder;  // modified to continue using Lcobucci builder...

class RTOController extends Controller
{

	function __construct(Request $request){

		$this -> rto = new RTO();
		return;
	}

	public function loadRTO(Request $request)
	{	// Initialize array containing employeeID and subordinates.
		$idstofetch = array($request->user->employeeid);
		foreach($request->user->subordinates as $obj)
		{
			$idstofetch[] = $obj -> employeeid;
		}

	}


	// Return a Json array of Subordinate User Objects

	
	public function getSubordinates(Request $request, $user_id)
	{	dd();
		$subordinates = (new User($user_id)) -> getSubordinates();
		return response() -> json(['subs' => $subordinates -> subordinates]);
	}




	// Return a Json array of Supervisor + Sibling User Objects
	public function getSupervisors($user_id)
	{	dd($user_id);
		$supervisors = (new User($user_id));
		return response() -> json(['sups' => $supervisors -> supervisors]);

	}





	public function requestSpecific($requestID)
	{
		$test = $this->rto->getRTOdata($requestID);
		return response() -> json($test , 200);
		
	}


	public function createRTO(Request $request)
	{	// Returns rto object containing table info.
		$response = $this->rto->createRTO($request->user);
		return response() -> json($response , 200);
	}

	public function requestTime(Request $request)
	{	// Temporary, unsure how form data will be sent.
		$info = ['requestID' => $request -> requestID, 'date' => $request -> date, 'hours' => $request -> hours, 'type' => $request -> type];
		$response = $this -> rto -> requestTime($info);
		return response() -> json($response , 200);
	}

	public function editRTOtime(Request $request)
	{	// Accepts array of data called 'timeRequested'
		// rtotimeID, requestID
		// ex {"date":"2053-11-13","hours":"428","type":" triple time","rtotimeID":"45"}
		$info = json_decode($request->input('timeRequested'), true);
		//dd($info);
		$response = $this -> rto -> editRTOtime($info);
		dd($response);
		return response() -> json($response, 200);

	}

	public function postApproval(Request $request)
	{	// example input {"approval":"approved","employeeID":78,"supervisorlevel":"test","reason":null,"requestID":12}
		$approval = json_decode($request -> input('userinput'), true);
		$approval['supervisorID'] = $this -> rto -> findSupervisor($approval['employeeID']);
		$response = $this -> rto -> postApproval($approval);
		return response() -> json($response, 200);
	}

	public function editApproval(Request $request)
	{	//example input {"approval":"denied","approvalID":23,"reason":"angry"}
		$approvalChange = json_decode($request -> input ('approvalChange'), true);
		$response = $this -> rto -> editApproval($approvalChange);
		return response() -> json($response, 200);

	}


/*	public function postApproval(Requests\rtoapprovalPostRequest $request){
		


	}*/
	public function updateApproval(Requests\rtoapprovalUpdateRequest $request){
		dd('whatsup');
		
	}

	public function createUserToken(Request $request)
	{
		$payload = new JWT($request);

		if ($payload -> payload == 'error')
		{
			return response() -> json(['error' => 'LDAP Authorization Failed or LDAP Server is Unavailable'], 401);
		}


		else {
			return response() -> json(['token' => $payload -> payload], 200);
		}
	}

	public function getAuthenticatedUser(Request $request) 
	{
		dd($request -> user);
	}
}