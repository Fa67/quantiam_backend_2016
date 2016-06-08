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
use Baum\Extensions\Query\Builder; 

class RTOController extends Controller
{

	function __construct(Request $request){

		$this -> rto = new RTO();
		return;
	}

	public function loadRTO(Request $request)
	{	// Initialize array containing employeeID and subordinates.
		$idstofetch = array($request->user->employeeid);
		$params = ($request -> all());
		$params = json_decode((json_encode($params)));


		foreach($request->user->subordinates as $obj)
		{
			$idstofetch[] = $obj -> employeeid;
		}

		try
		{
			$results = $this -> rto -> getSubRTO($idstofetch, $params);	
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
			return response() -> json ($response, 200);
			
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
		$permission = $this -> checkRtoPermission ($request_id, false);
		if ($permission)
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
		else 
		{
			return response() -> json(["error" => "cannot delete rto after posted approval"], 403);
		}
	}

	public function requestTime(Request $request, $request_id)
	{
		$userInput = $request -> all();
		$userInput['requestID'] = $request_id;

		$permission = $this -> checkRtoPermission($request_id, false);
		if ($permission)
		{
			try
			{
				$response = $this -> rto -> requestTime($userInput);
				return response() -> json($response, 200);
			}catch(\Exception $e){
				return response() -> json(['error' => $e]);
			}
		}
		else {
			return response() -> json(['error' => "cannot post time request after approval has been posted"], 403);
		}
	}

	public function editRTOtime(Request $request)
	{	
		$userInput = $request -> all();
		$permission = $this -> checkRtoPermission($userInput ['rtotimeID']);
		if ($permission)
		{
			try
			{
				$response = $this -> rto -> editRTOtime($userInput);
				return response() -> json($response, 200);

			}catch (\Exception $e)
			{
				return response() -> json(['error' => $e], 401);
			}
		}
		else 
		{
			return response() -> json (['error' => "Cannot edit after an approval has been posted"], 403);
		}
	}



    public function checkRtoPermission(Request $request, $requestID, $rtotime = true)
    {
        if ($rtotime)
        {
            $requestID = DB::table('timesheet_rtotime') -> where ('rtotimeID', $requestID) -> value('requestID');
        }

        $approvals = DB::table('timesheet_rtoapprovals') ->where('requestID', $requestID) ->value('approval');

                if ($approvals == null)
                {
                    return true;
                }
                else if ($approvals != null){

                    if(!$approvals[1] && $approvals[0] -> employeeID == $request -> user -> employeeID) {
                      
                             return true;

                       	}

                       	else if ($approvals[1] && $approvals[1] -> employeeID == $request -> user -> employeeID)

                       	{

                        	return true;

                       	}

          		else

              	{
              		return false;
                       }
                }
    }



	public function deleteRTOTime(Request $request, $rtotime_id)
	{

		$permission = $this -> rto -> checkRtoPermission($rtotime_id);
		if ($permission)
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
		else 
		{
			return response() -> json(['error' => "Cannot delete after an approval has been posted"], 418);
		}
	}

	public function postApproval(Request $request, $requestID)
	{	
		$approval = $request -> input("approval");
		$supervisorObj = $request -> user;
		if ($request -> user -> depth  > 0)
		{
			$nextSupervisorObj = $supervisorObj -> supervisors[count( $request -> user -> supervisors) -1];
		}
		else
		{
			$nextSupervisorObj = $supervisorObj;
		}
	

		$params = array(
			"approval" => $approval,
			"employeeID" => $supervisorObj -> employeeID,
			"requestID" => $requestID);

		$employeeID = $this -> rto -> getRTOdata($requestID) -> employeeID;
		$rtoEmployee = (new User($employeeID));

		if($rtoEmployee -> depth > $supervisorObj -> depth || $supervisorObj -> depth == 0)
		{
			$response = $this -> rto -> postApproval($params, $supervisorObj -> depth);
			$response -> name = $request -> user -> name;

			if ($response -> emailSupervisor == true)
			{
				$rto_url = getenv('RTO_URL');
				$message = "<p>".$nextSupervisorObj -> name.",<br><br><a href=".$rto_url.$requestID.">To see their request and enter your approval, click here.</p></a><p>This is an automated message.</p>";

				app('App\Http\Controllers\MailController')->send($request, $nextSupervisorObj -> employeeID, "RTO Approval for ".$rtoEmployee -> name, $message);
			}
			else {

			}


			return response() -> json($response, 200);
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

	public function deleteApproval(Request $request, $approvalID)
	{	
		$approvalEmployeeID = DB::table('timesheet_rtoapprovals')->where('approvalID', '=', $approvalID)->value('employeeID');

		if ($request -> user -> employeeID != $approvalEmployeeID)
		{
			return response() -> json(["error" => "Unauthorized"], 401);
		}

		else
		{
			$response = $this -> rto -> deleteApproval($approvalID, $request -> user -> depth);
		}

		return response() -> json($response, 200);
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