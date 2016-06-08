<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

use DB;

use Carbon\Carbon;

class RTO extends Model
{

     function createRTO($userObj)
    {
        try{
            $id = DB::table('timesheet_rto') -> insertGetID(['employeeID' =>$userObj -> employeeid]);
            $this -> requestID = $id;
            $response = $this->getRTOdata($id);
            return $response;

        }
        catch (\Exception $e)
        {
            return response(['error' => $e]);
        }
    }


    public function deleteRTO($requestID)
    {
        DB::table('timesheet_rto')  -> where ('requestID', '=', $requestID)
                                    -> delete();

        DB::table('timesheet_rtotime')  -> where ('requestID', '=', $requestID) -> delete();
        DB::table('timesheet_rtoapprovals') -> where ('requestID', '=', $requestID) -> delete();

        $response = "RTO with ID: ".$requestID." successfully deleted.";
        return $response;
    }


    private function editRTO($params)
    {
        DB::table('timesheet_rto')  -> where('requestID', $params['requestID'])
                                    -> update($params);

        $response = $params['status'];
        return $response;
    
    }



    public function requestTime($userInput)
    {   
       try
       {
            $id = DB::table ('timesheet_rtotime') -> insertGetID ($userInput);
            $response = $this -> getSpecificTable('timesheet_rtotime', 'rtotimeID', $id);

       } catch (\Exception $e)
       {
            $response['error'] = $e;
       }
       return $response;
        
    }



    public function editRTOtime($requestInfo)
    {   
      
        DB::table('timesheet_rtotime')  ->where('rtotimeID', $requestInfo['rtotimeID'])
                                        ->update($requestInfo);
        $response = $this -> getSpecificTable('timesheet_rtotime', 'rtotimeID', $requestInfo['rtotimeID']);
   
        return $response;   
    }



    public function deleteRTOTime($rtotimeID)
    {
            DB::table('timesheet_rtotime')  ->where('rtotimeID', '=', $rtotimeID)
                                            ->delete();

            return ("rto_time ".$rtotimeID." deleted");

    }


    public function checkRtoPermission($request, $requestID, $rtotime = true)
    {
        $approvalEmployee = new User($request -> user -> employeeID);

        if ($rtotime)
        {
            $requestID = DB::table('timesheet_rtotime') -> where ('rtotimeID', $requestID) -> value('requestID');
        }

        $requestingEmployee = new User(DB::table('timesheet_rto')->where('requestID', $requestID)->value('employeeID'));
        $approvals = DB::table('timesheet_rtoapprovals')  ->select('*')->where('requestID', '=', $requestID) ->get();
		

            if ($approvals == null)
            {
                return true;
            }
	else if (isset($approvals[0]) && !isset($approvals[1]) &&  $approvalEmployee -> depth < $requestingEmployee -> depth)
	{
		return true;
	}
	else 
	{
		return false;
	}
    }




    public function postApproval($params, $depth)
    {
        $id = DB::table('timesheet_rtoapprovals') -> insertGetID(['approval' => $params['approval'], 'employeeID' => $params['employeeID'], 'requestID' => $params['requestID']]);
              $response = $this -> getSpecificTable('timesheet_rtoapprovals', 'approvalID', $id);
        
        $temp  = $this -> checkApprovals($params['requestID'], $depth);
        $response ->check = $temp['status'];
        $response ->emailSupervisor = $temp['emailSupervisor'];
        return $response;
    }



    public function editApproval($user, $approvalID)
    {
        DB::table('timesheet_rtoapprovals') ->where('approvalID', $approvalID)
                                            ->update(['approval' => $user -> approvalChange]);
            $response = $this -> getSpecificTable('timesheet_rtoapprovals', 'approvalID', $approvalID);
            $response = $response[0];

            $this -> editRTO($response);

            return $response; 
    }

    public function deleteApproval($approvalID, $depth)
    {
        $tableRow = DB::table('timesheet_rtoapprovals') -> where ('approvalID', '=', $approvalID) ;
       $requestID = $tableRow -> value('requestID');
                           $tableRow -> delete();

        $response = ($this -> checkApprovals($requestID, $depth))['status'];
        return $response;
    }

    private function checkApprovals($requestID, $depth)
    {
        $approvals = DB::table('timesheet_rtoapprovals')->select('approval')->where('requestID', '=', $requestID)->get();
        $status = null;
        $emailSupervisor = false;

        if (!isset($approvals[0]))
        {
            $status = 'new';
        }
        else if (isset ($approvals[0]) && !isset($approvals[1]))
        {
                 if ($approvals[0] -> approval == 'denied')
                {
                    $status = 'denied';
                }

                else if ($approvals[0] -> approval == 'approved' && $depth > 0)
                {
                    $status = 'pending';
                    $emailSupervisor = true;
                }
                else if ($approvals[0] -> approval == 'approved' && $depth == 0)
                {
                    $status = 'approved';
                    $emailSupervisor = false;
                }
                else 
                {
                    return "improper approval format";
                }
        }
       else
        {
              if ($approvals[0] -> approval == 'denied' || $approvals[1] -> approval == 'denied')
              {
                     $status = 'denied';
              }
              else if ($approvals[1] -> approval == 'approved' && $approvals[1] -> approval == 'approved')
              {
                    $status = 'approved';
              }
        }

        if ($status == null)
        {
            $status = 'pending';
        }

        $params = array (
                        "requestID" => $requestID,
                        "status" => $status
                        );

        $response['status'] = $this -> editRTO($params);

        $response['emailSupervisor'] = $emailSupervisor;


        return $response;

    }


    // Used to pull a specified table with a given id name and number.
    private function getSpecificTable($tablename, $idname, $idnumber)
    {
        try
        {
            $response = DB::table ($tablename)  -> select('*')
                                                -> where ($idname, '=', $idnumber)
                                                -> first();

            return $response;
        } 
        catch (\Exception $e)
        {
            return (['error' => $e]);
        }
    }



    // Used to pull a related requestID from a specified table id name and number.
    private function getRequestID($tablename, $idname, $idnumber)
    {
        try
        {
            $response = DB::table ($tablename)  -> select ('requestID')
                                                -> where ($idname, '=', $idnumber)
                                                -> get();
            return $response;
        }
        catch (\Exception $e)
        {
            return (['error' => $e]);
        }
    }



    public function getRTOdata($requestID)
    { 

        $return_object = array();
        
        $tableData = DB::table('timesheet_rto')
                        ->select('*')
                        ->where('timesheet_rto.requestID', '=', $requestID)
                        ->get();
                                            
        $rtoTime = DB::table('timesheet_rtotime')
                        ->select('*')
                        ->where('timesheet_rtotime.requestID', '=', $requestID)
                        ->get();    
                        
        $approvals = DB::table('timesheet_rtoapprovals')
                        ->select('*')
                        ->where('timesheet_rtoapprovals.requestID', '=', $requestID)
                        ->get();
        

        // Attach supervisor name to approvals object.
        foreach($approvals as $obj)
        {
            $name = DB::table('employees')->select('firstname', 'lastname')->where('employeeID', '=', $obj->employeeID)->first();
            $obj -> name = $name -> firstname." ".$name->lastname;
        }
                        //Sorts through table and creates an $obj of each row
                            foreach($tableData as $obj)
                        {
                            $tempObject = array(); //create temporary object/array
                            
                            //populate object with timesheet_rto data
                            //because there are 2 => objects, $key (1st) holds the column 'key,' and $value holds the 'value' of the column + row
                            foreach($obj as $key => $value)
                            {
                                //$tempObject[$key] = $value;
                                $this -> $key = $value;
                            }
                        }
                            $this -> requested_time = $rtoTime;
                                
                            $this -> approvals = $approvals; 

        return $this;
    }

    public function getSubRTO($idstofetch, $params)
    { 
        $status = $params -> status;
        $employeeID = $params -> employeeID;
        $firstDate = $params -> firstDate;
        $lastDate = $params -> lastDate;
        $perPage = $params -> perPage;
        $page = $params -> page;

        if ($page == null)
        {
            $page = 1;
        }
        if($firstDate == null)
        {
           $firstDate = Carbon::now() -> addYear(-1) -> toDateString();
        }
        if ($lastDate == null)
        {
            $lastDate = Carbon::tomorrow() -> toDateString();
        }
        if($perPage == null)
        {
            $perPage = 15;
        }
        
        $result['draw'] = $page;
        
        $requestIDarray = DB::table('timesheet_rto')
                                ->whereIn('employeeID', $idstofetch)
                                ->pluck('requestID'); // Requests column of requestID || for scalability, consider using chunk();

        $tableData = DB::table('timesheet_rto') ->join('employees', 'timesheet_rto.employeeID', '=', 'employees.employeeID')
                                                ->select('timesheet_rto.*', 'employees.firstname', 'employees.lastname')
                                                ->orderBy('created');

        $result['recordsTotal'] = $tableData -> count();

                                     $tableData ->whereIn('timesheet_rto.requestID', $requestIDarray)
                                                ->where('timesheet_rto.status', 'like', '%'.$status.'%')
                                                ->where('timesheet_rto.employeeID', 'like', '%'.$employeeID.'%')
                                                ->whereBetween('created', [$firstDate, $lastDate]);
        $result['recordsFiltered'] = $tableData -> count();
        $dataArray = $tableData ->simplePaginate($perPage)->toJson();
        $dataObj = json_decode($dataArray);
        $result['data'] = $dataObj -> data;

        return $result;
    }

    public function notifyApprovers($request, $requestID)
    {
        $approvalEmployeeIDs = DB::table('timesheet_rtoapprovals')->where('requestID', $requestID)->pluck('employeeID');
        $deletedRequests = DB::table('timesheet_rto')->join('timesheet_rtotime', 'timesheet_rto.requestID', '=', 'timesheet_rtotime.requestID')->select('timesheet_rtotime.rtotimeID', 'timesheet_rtotime.date', 'timesheet_rtotime.hours', 'timesheet_rtotime.type')->where('timesheet_rto.requestID', '=', $requestID)->get();

        $message = $request -> user -> name." has deleted their request for time off bearing your approval.";


        foreach ($approvalEmployeeIDs as $recipientID)
        {
                $rto_url = getenv('RTO_URL');
                $message = "</p>".$message."</p><p>This is an automated message.</p>";
                app('App\Http\Controllers\MailController')->send($request, $recipientID, "Time Off Request Deleted ", $message);
        }

    }
}
