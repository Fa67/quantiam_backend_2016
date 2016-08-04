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

		if($request->user->checkGroupMembership(4))
		{

		 $ids = DB::table("employees") -> whereNotNull ('employeeid', null) -> pluck('employeeid');
			$results = $this -> rto -> getSubRTO($ids, $params);
			return $results;

		}

		foreach($request->user->subordinates as $obj)
		{
			$idstofetch[] = $obj -> employeeid;
		}

		try
		{
			$results = $this -> rto -> getSubRTO($idstofetch, $params);	
			return response() -> json ($results, 200);

		}
		catch (\Exception $e)
		{
			return response() -> json(['error' => $e], 400);
		}


	}

	public function rtoDataList(Request $request)
    {
        // Initialize array containing employeeID and subordinates.
        $idstofetch = array($request->user->employeeid);

        if($request->user->checkGroupMembership(4))
        {

            $idstofetch = DB::table("employees") -> whereNotNull ('employeeid', null) -> pluck('employeeid');

        }
        else {

            foreach($request->user->subordinates as $obj)
            {
                $idstofetch[] = $obj -> employeeid;
            }
        }


        //Adapted from Tyson Boyce: slipDataList @ SlipcastingController
        $input = $request->all();

        $returnObj = array();

        if(!isset($input['draw']))
        {
            $input = array(
                'draw' => null,
                'start' => 0,
                'length' => 10,
                'search' => null,

            );
        }


        //$input['campaign_id'] = 7;
        $returnObj['draw'] = intval($input['draw']);

        $queryCount = DB::table('timesheet_rto')
            ->select(['timesheet_rto.*', 'employees.firstname', 'employees.lastname'])
            ->Leftjoin('employees', 'timesheet_rto.employeeID', '=', 'employees.employeeid');



        // What can we search or filter by?
        $SearchableConditionals = array('requestID', 'created','employeeID','employees.firstname', 'employees.lastname');
        $FilterableConditionals = array(
            'created' => 'timesheet_rto.created',
            'status' => 'timesheet_rto.status',
            'firstname' => 'employees.firstname',
            'lastname' => 'employees.lastname',
            'employeeID' => 'timesheet_rto.employeeID',

        );


        $query  = DB::table('timesheet_rto')
            ->select(['timesheet_rto.*', 'employees.firstname', 'employees.lastname'])
            ->whereIn('timesheet_rto.employeeID', $idstofetch)
            ->Leftjoin('employees', 'timesheet_rto.employeeID', '=', 'employees.employeeid')
            ->skip($input['start'])
            ->take($input['length']*2)
            ->orderBy('created','desc');


        //Search value functionality
        if($input['search']['value'])
        {
            foreach($SearchableConditionals as $key)
            {


                $query->orWhere($key,'Like','%'.$input['search']['value'].'%');
                $queryCount->orWhere($key,'Like','%'.$input['search']['value'].'%');

            }


        }

        //custom field functionality
        foreach($FilterableConditionals as $key =>$value)
        {
            if(isset($input[$key]) && strlen($input[$key]) > 0)
            {

                $query->Where($FilterableConditionals[$key],'=',''.$input[$key].'');
                $queryCount->Where($FilterableConditionals[$key],'=',''.$input[$key].'');

            }

        }

        //	$query->orWhere('characterName','Like','%Troyd%');
        $queryCount = $queryCount->count();
        $query = $query ->get();
        $resultCnt = count($query);


        $returnObj['recordsTotal'] = $queryCount;
        $returnObj['recordsFiltered'] = $queryCount;


        $omitt = array('updated');
        $last_request_id = 0;
        try{
            foreach($query as $rto)
            {

                if($rto->requestID != $last_request_id)
                {
                    $tempArray = array();


                    foreach($rto as $key => $value)
                    {

                        if(!in_array($key, $omitt))
                        {
                            $tempArray[$key] = $value;
                        }

                    }

                }
                else
                {
                    array_pop($tempObj);
                }

                $tempObj[] = $tempArray;

                $last_request_id = $rto->requestID;
            }


            $returnObj['aoData'] = $tempObj;
        }
        catch(\Exception  $e){}

        return response()->json($returnObj, 200);
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

		$this -> rto -> notifyApprovers($request, $request_id);

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
		$userInput = $request -> all();
		$userInput['requestID'] = $request_id;

		$permission = $this -> rto -> checkRtoPermission($request, $request_id, false);
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
		$permission = $this -> rto -> checkRtoPermission($request, $userInput ['rtotimeID']);
		if ($permission)
		{
			try
			{
				$response = $this -> rto -> editRTOtime($userInput);
				return response() -> json($response, 200);

			}catch (\Exception $e)
			{
				return response() -> json(['error' => $e], 400);
			}
		}
		else 
		{
			return response() -> json (['error' => "Cannot edit after an approval has been posted"], 403);
		}
	}



	public function deleteRTOTime(Request $request, $rtotime_id)
	{

		$permission = $this -> rto -> checkRtoPermission($request, $rtotime_id);
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

			if (isset($response -> error))
			{
				return response() -> json(['Error' => 'Approval already posted'], 400);
			}

			if ($response -> emailSupervisor == true)
			{
				$rto_url = getenv('RTO_URL');
				$message = "<p>".$nextSupervisorObj -> name.",<br><br><a href=".$rto_url.$requestID.">To see their request and enter your approval, click here.</p></a><p>This is an automated message.</p>";

				app('App\Http\Controllers\MailController')->send($request, $nextSupervisorObj -> employeeID, "RTO Approval for ".$rtoEmployee -> name, $message);
			}


			// Email employee upon approval/denial
			if ($response -> check == "approved")
			{
				$rto_url = getenv('RTO_URL');
				$message = "<p>".$rtoEmployee -> name.",<br><br><a href=".$rto_url.$requestID.">Your request for time off has been <b>".$response -> check."</b>.</p></a><p>This is an automated message.</p>";

				app('App\Http\Controllers\MailController')->send($request, $rtoEmployee -> employeeID, "Time Off Request ".$response -> check, $message);
			}

			else if ($response -> check == "denied")
			{
				$rto_url = getenv('RTO_URL');
				$message = "<p>".$rtoEmployee -> name.",<br><br><a href=".$rto_url.$requestID.">Sorry, but your request for time off has been denied by a supervisor.  Click here to see information about your request.</b>.</p></a><p>This is an automated message.</p>";

				app('App\Http\Controllers\MailController')->send($request, $rtoEmployee -> employeeID, "Time Off Request ".$response -> check, $message);

			}

			if ($response -> check == "approved")
			{
				$response -> logged = $this -> rto -> storeRtotimeData($requestID, $employeeID);
			}
			


			return response() -> json($response, 200);
		} else
		{
			return response() -> json(['error' => 'Unauthorized to approve this request'], 400);
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
			return response() -> json(['error' => 'Unauthorized access.  This Approval belongs to another employee.'], 400);
		}

	}

	public function deleteApproval(Request $request, $approvalID)
	{	
		$approvalData = DB::table('timesheet_rtoapprovals') -> where ('approvalID', $approvalID) -> first();

		$rtoData = DB::table('timesheet_rto') -> where ('requestID', $approvalData -> requestID) -> first();
		$status = $rtoData -> status;

		if ($status != 'pending')
		{
			$approvals = DB::table('timesheet_rtoapprovals') -> select('employeeID') -> where ('requestID', '=', $approvalData -> requestID) -> get();
			if (isset($approvals[1]))
			{
				$mgmtEmployeeID = $approvals[1] -> employeeID;
			}
			else
			{
				$mgmtEmployeeID = $approvals[0] -> employeeID;
			}

			if ($mgmtEmployeeID != $request -> user -> employeeID)
			{
				return array("error" => "cannot delete approval after second approval is posted.");
			}
			else
			{
				$rtoEmployeeID = $rtoData -> employeeID;
				$this -> rto -> unstoreRtotimeData($approvalData -> requestID, $rtoEmployeeID);
			}
		}

			$approvalEmployeeID = $approvalData -> employeeID;

			if ($request -> user -> employeeID != $approvalEmployeeID)
			{
				return response() -> json(["error" => "Unauthorized"], 400);
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