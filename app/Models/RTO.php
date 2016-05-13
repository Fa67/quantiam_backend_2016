<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;

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



    private function editRTO($approval)
    {
        DB::table('timesheet_rto')  -> where    ('requestID', $approval -> requestID)
                                    -> update   (['status' => $approval -> approval, 'reason' => $approval -> reason]);
    
    }



    public function requestTime($user)
    {
       try
       {
            $id = DB::table ('timesheet_rtotime') -> insertGetID ($user -> requestInfo);
            $response = $this -> getSpecificTable('timesheet_rtotime', 'rtotimeID', $id);
            $response = $response[0];

       } catch (\Exception $e)
       {
            $response['error'] = $e;
       }
       return $response;
        
    }



    public function editRTOtime($requestInfo)
    {   dd($requestInfo);
        try
        {   
            DB::table('timesheet_rtotime')  ->where('rtotimeID', $requestInfo['rtotimeID'])
                                            ->update($requestInfo);
            $response = $this -> getSpecificTable('timesheet_rtotime', 'rtotimeID', $requestInfo -> rtotimeID);
            $response = $response[0];
        }
        catch (\Exception $e)
        {
            $response['error'] = $e;
        }
        return $response;   
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
                                                -> get();

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

    public function getSubRTO($idstofetch, $pendingStatus = true)
    { 
        if ($pendingStatus == 'true')
        {
            $status = '=';
        }
        else {
            $status = '!=';
        }

        $return_object = array();
        $requestIDarray = DB::table('timesheet_rto')
                                ->whereIn('timesheet_rto.employeeID', $idstofetch)
                                ->pluck('requestID'); // Requests column of requestID || for scalability, consider using chunk();
        

        $tableData = DB::table('timesheet_rto') ->join('employees', 'timesheet_rto.employeeID', '=', 'employees.employeeID')
                                                ->select('timesheet_rto.*', 'employees.firstname', 'employees.lastname')
                                                ->orderBy('updated')
                                                ->whereIn('timesheet_rto.requestID', $requestIDarray)
                                                ->where('status', $status, 'pending')
                                                ->take(100)
                                                ->get();                 
        
        return $tableData;
    }
}
