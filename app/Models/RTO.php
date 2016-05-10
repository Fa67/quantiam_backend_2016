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



    public function requestTime($info)
    {
       try
       {
            $id = DB::table ('timesheet_rtotime') -> insertGetID ($info);
            $response = $this -> getSpecificTable('timesheet_rtotime', 'rtotimeID', $id);
            $response = $response[0];

       } catch (\Exception $e)
       {
            $response['error'] = $e;
       }
       return $response;
        
    }



    public function editRTOtime($info)
    {
        try
        {   
            DB::table('timesheet_rtotime')  ->where('rtotimeID', $info['rtotimeID'])
                                            ->update($info);
            $response = $this -> getSpecificTable('timesheet_rtotime', null, $info['rtotimeID']);
            $response = $response[0];
        }
        catch (\Exception $e)
        {
            $response['error'] = $e;
        }
        return $response;   
    }



    public function postApproval($approval)
    {
    
        $id = DB::table('timesheet_rtoapprovals') -> insertGetID($approval);
        $response = $this -> getSpecificTable('timesheet_rtoapprovals', 'approvalID', $id);
        $response['approvalID'] = $id;
        return $response[0];
    }



    public function editApproval($approvalChange)
    {
        DB::table('timesheet_rtoapprovals') ->where('approvalID', $approvalChange['approvalID'])
                                            ->update($approvalChange);
            $response = $this -> getSpecificTable('timesheet_rtoapprovals', 'approvalID', $approvalChange['approvalID']);
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
        }
        catch (\Exception $e)
        {
            return (['error' => $e]);
        }
    }



    private function getRTOdata($requestID)
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

    public function getSubRTO($employeeID)
    { 
        $results = array();
        $requestIDs = DB::table('timesheet_rto') -> select ('requestID')
                                       -> where ('employeeID', '=', $employeeID)
                                       -> get();

        dd($requestIDs);
        foreach ($requestIDs as $obj)
        {
           $results[] = $this -> getRTOdata($obj -> requestID);
        }
        return $results;
    }
}
