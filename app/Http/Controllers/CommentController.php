<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class CommentController extends Controller
{
   
	function editComment (Request $request, $commentID) {
	
	
	
	}
	
	
	function createComment (Request $request){
	
	
	//dd($request);
	$input = $request->all();
	
	$validate = array('comment_path', 'comment_text'); //expected fields. 
	
	if($input){
		foreach($input as $key => $value)
		{
		
		
			if(!in_array($key, $validate))
			{
				return response() -> json(['error' => $key.' was not sent as a parameter'], 400);
			}		
		
		}
	}
	else
	{
		return response() -> json(['error' => 'Please include the expected body arguments comment_path and comment_text for this request.'], 400);
		
	}
	
	
	$input['comment_employee_id'] = $request->user->employeeID;
			
	$query = DB::table('comments')->insertGetId($input);
	
	return response() -> json(['success' => 'Comment '.$query.' was successfully created', 'commentID' => $query], 200);
	
	
	}
	
	function deleteComment(Request $request, $commentID){
	
	
	$query = DB::table('comments')
			->where('comment_entry_id', '=', $commentID)
			->delete();
	
	return response() -> json(['success' => 'Comment '.$commentID.' was successfully deleted.'], 200);
	
	
	}
	
	function getComments (Request $request)
	{

			
			$input = $request->all();
			
			$query = DB::table('comments')
			->select('*')
			->where('comment_path', '=', $input['path'])
			->orderBy('comment_datetime', 'desc')
			->get();

			return response() -> json($query, 200);
			

	
	}
	
	
	
}
