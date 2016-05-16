<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Baum\Node;
use Baum\Extensions\Query\Builder; 

class userController extends Controller
{

	public function userInfo(Request $request)
	{
		dd('slerp');
	}


    public function newUser(Request $request)
	{
		dd('merp');
	}


	public function editUser(Request $request)
	{
		dd('klorp');
	}


	public function moveUser(Request $request)
	{
		dd('shmeerp');
	}
}