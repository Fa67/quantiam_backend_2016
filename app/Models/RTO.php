<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


    private function editRTO($approval)
    {
        DB::table('timesheet_rto')  -> where    ('requestID', $approval -> requestID)
                                    -> update   (['status' => $approval -> approval, 'reason' => $approval -> reason]);
    
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
        dd($response);
   
        return $response;   
    }



    public function deleteRTOTime($rtotimeID)
    {
            DB::table('timesheet_rtotime')  ->where('rtotimeID', '=', $rtotimeID)
                                            ->delete();

            return ("rto_time ".$rtotimeID." deleted");

    }


    public function postApproval($user, $requestID)
    {
        $id = DB::table('timesheet_rtoapprovals') -> insertGetID(['approval' => $user -> approval, 'employeeID' => $user -> employeeID, 'requestID' => $requestID]);
/*        $response = $this -> getSpecificTable('timesheet_rtoapprovals', 'approvalID', $id);
        $response['approvalID'] = $id;*/
        return $id;
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

        if($params -> firstDate == null)
        {
           $firstDate = Carbon::now() -> addYear(-1) -> toDateString();
        }
        if ($params -> lastDate == null)
        {
            $lastDate = Carbon::now() -> toDateString();
        }
        if($params -> perPage == null)
        {
            $perPage = 15;
        }
        
        $requestIDarray = DB::table('timesheet_rto')
                                ->whereIn('employeeID', $idstofetch)
                                ->pluck('requestID'); // Requests column of requestID || for scalability, consider using chunk();
        

        $tableData = DB::table('timesheet_rto') ->join('employees', 'timesheet_rto.employeeID', '=', 'employees.employeeID')
                                                ->select('timesheet_rto.*', 'employees.firstname', 'employees.lastname')
                                                ->orderBy('created')
                                                ->whereIn('timesheet_rto.requestID', $requestIDarray)
                                                ->where('timesheet_rto.status', 'like', '%'.$status.'%')
                                                ->where('timesheet_rto.employeeID', 'like', '%'.$employeeID.'%')
                                                ->whereBetween('created', [$firstDate, $lastDate])
                                                ->paginate($perPage);                

        return $tableData;
    }
}
