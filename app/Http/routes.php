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

	Route::put('/rto/approval/{approval_id}', 'RTOController@editApproval');
	Route::post('/rto/{request_id}/approval', 'RTOController@postApproval');

	Route::post('/mail/send', 'MailController@send');

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

Route::get('/user/{employee_id}', 'userController@userInfo');
Route::post('/user', 'userController@newUser');
Route::put('/user/{employee_id}', 'userController@editUser');
Route::put('/hierarchy/{employee_id}', 'userController@moveUser');
