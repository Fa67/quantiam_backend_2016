<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
Route::post('/auth', 'RTOController@createUserToken'); // Create a JWT for a specific user.

Route::group(['middleware' => 'authuser'], function() 
{
	Route::get('/authuser', 'RTOController@getAuthenticatedUser');

	Route::post('/rto', 'RTOController@loadRTO');	// Load all self- and subordinate-RTOs
	Route::get('/rto/{request_id}', 'RTOController@specRTO');	// Load specific rto
	Route::post('/rto/new', 'RTOController@createRTO');  // Post a new row in the timesheet_rto table.
	Route::put('/rto', 'RTOController@updateRTO');  // Edit existing RTO status.
	Route::delete('/rto/{request_id}', 'RTOController@deleteRTO'); // Delete and RTO and all its associated approvals/times.

	Route::post('/rto/{request_id}/requestTime', 'RTOController@requestTime');
	Route::put('/rto/requestTime', 'RTOController@editRTOtime');
	Route::delete('/rto/time/{rtotime_id}', 'RTOController@deleteRTOTime');

	Route::put('/approval/{approval_id}', 'RTOController@editApproval');
	Route::post('/approval/{request_id}', 'RTOController@postApproval');
	Route::delete('/approval/{approval_id}', 'RTOController@deleteApproval');

	Route::post('/mail/send', 'MailController@send');

	Route::get('/user/', 'userController@identifyUser');
	Route::get('/user/{user_id}', 'userController@specificUser');
	
	
	//Timesheet Controller
	Route::get('/u/rtobank/', 'TimesheetController@rto_allotment');
	Route::post('/rto/existingabsences/', 'TimesheetController@rto_existing_absences');
	Route::post('/timesheet/absencehours', 'TimesheetController@addAbsenceRequest');
	


Route::post('/user/new', 'userController@newUser');
Route::post('/user/move', 'userController@moveUser');
Route::post('/user/tree', 'userController@viewTree');
// Load/search all users
Route::get('/users', 'userController@getUsers');

//comment routes

Route::get('/comment/', 'CommentController@getComments');
Route::post('/comment/', 'CommentController@createComment');
Route::delete('/comment/{commentID}', 'CommentController@deleteComment');

	

});
// Request existing RTOs
// Post new approval.
Route::post('/rto/request/{request_id}/approval', 'RTOController@postApproval');  //  Post a new row in the timesheet_rtoapprovals table relevant to an existing RTO id.
// Edit existing approval
Route::put('/rto/request/{request_id}/approval/{approval_id}', 'RTOController@updateApproval');  //  Submitted from Supervisor to approve/deny (?modify) an RTO.
// Create a Json Web Token
// Post a new RTO date/time
Route::get('/user/{user_id}/getSubordinates', 'RTOController@getSubordinates');
Route::get('/user/{user_id}/getSupervisors', 'RTOController@getSupervisors');

Route::put('/user/{employee_id}', 'userController@editUser');



