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
		if (null !== $request -> pendingStatus){
		$pendingStatus = $request -> pendingStatus;
		}
		else 
		{
			$pendingStatus = 'true';
		}

		foreach($request->user->subordinates as $obj)
		{
			$idstofetch[] = $obj -> employeeid;
		}

		try
		{
			$results = $this -> rto -> getSubRTO($idstofetch, $pendingStatus);	
			return response() -> json ($results, 200);

		}catch (\Exception $e)
		{
			return response() -> json(['error' => $e], 400);
		}


	}

	public function specRTO($requestID)
	{
		try {
			$response = $this -> rto -> getRTOdata($requestID);
			return response() -> json ([$response], 200);
			
		} catch (\Exception $e)
		{
			return response() -> json (['error' => $e], 400);
		}
	}




	public function requestSpecific($requestID)
	{
		$test = $this->rto->getRTOdata($requestID);
		return response() -> json($test , 200);
		
	}


	public function createRTO(Request $request)
	{	// Returns rto object containing table info.
		
		try 
		{
			$response = $this->rto->createRTO($request->user);
			return response() -> json($response , 200);
			
		} catch (\Exception $e) {
			return response() -> json(['error' => $e]);
		}
	}

	public function deleteRTO(Request $request, $request_id)
	{
		try
		{
			$response = $this -> rto -> deleteRTO($request_id);
			return response() -> json(['success' => $response], 200);
		}
		catch (\Exception $e)
		{
			return response() -> json (['error' => $e]);
		}
	}

	public function requestTime(Request $request, $request_id)
	{
		$userInput = json_decode(($request -> input), true);
		$userInput['requestID'] = $request_id;
		
		try
		{
			$response = $this -> rto -> requestTime($userInput);
			return response() -> json($response, 200);
		}catch(\Exception $e){
			return response() -> json(['error' => $e]);
		}

	}

	public function editRTOtime(Request $request)
	{	
		$userInput = json_decode(($request -> input), true);

		try
		{
			$reponse = $this -> rto -> editRTOtime($userInput);
			dd($response);
		}catch (\Exception $e)
		{
			return response() -> json(['error' => $e]);
		}
	}

	public function deleteRTOTime(Request $request, $rtotime_id)
	{
		try
		{
			$response = $this -> rto -> deleteRTOTime($rtotime_id);
			return response() -> json (['success' => $response], 200);
		}
		catch (\Exception $e)
		{
			return response() -> json(['error' => $e]);
		}
	}

	public function postApproval(Request $request, $requestID)
	{	
		$request -> user -> approval = 'approved';

		$employeeID = $this -> rto -> getRTOdata($requestID) -> employeeID;
		$employeeDepth = (new User($employeeID)) -> depth;

		if ($employeeDepth > $request -> user -> depth)
		{
			$id = $this -> rto -> postApproval($request -> user, $requestID);
			dd($id);
			return response() -> json(['approval' => 'Aproval ID: '.$id], 200);
		} else 
		{
			return response() -> json(['error' => 'Unauthorized to approve this request'], 401);
		}
	}

	public function editApproval(Request $request, $approvalID)
	{	
		$request -> user -> approvalChange = 'denied';

		$approvalEmployeeID = DB::table('timesheet_rtoapprovals')->where('approvalID', '=', $approvalID)->value('employeeID');

		if ($request -> user -> employeeID == $approvalEmployeeID)
		{
			$response = $this -> rto -> editApproval($request -> user, $approvalID);
			dd($response);
		}
		else 
		{
			return response() -> json(['error' => 'Unauthorized access.  This Approval belongs to another employee.'], 401);
		}

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