<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\User;
use App\Models\Nest;

use Baum\Node;
use Baum\Extensions\Query\Builder; 

class userController extends Controller
{

	public function userInfo($employee_id)
	{
		$employeeID = $employee_id;

		$response = new User($employeeID, true);
		dd($response);
	}


    public function newUser(Request $request)
	{
		dd($request -> all());
	}


	public function editUser(Request $request, $employee_id)
	{
		dd(Nest::where('employeeID', '=', $employee_id)->first());

	}


	public function moveUser(Request $request)
	{
		dd('shmeerp');
	}
}