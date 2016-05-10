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

Route::group(['middleware' => 'auth'], function () {

Route::get('/rto/list/', 'RtoController@test'); // get all RTO requests applicable to this individual
Route::get('/hr/{employee_id}/hiearchy', 'RtoController@hrHiearchyBuild'); // get all RTO requests applicable to this individual
	
	
});

Route::group(['middleware' => 'authuser'], function() 
{
Route::get('/authuser', 'RTOController@getAuthenticatedUser');

Route::get('/rto', 'RTOController@loadRTO');	// Load all self- and subordinate-RTOs
Route::get('/rto/{request_id}', 'RTOController@specRTO');	// Load specific rto
Route::post('/rto', 'RTOController@createRTO');  // Post a new row in the timesheet_rto table.
Route::put('/rto', 'RTOController@updateRTO');  // Edit existing RTO status.
Route::get('/rto/request/callRTO', 'RTOController@callRTO');
Route::get('/rto/request/{requestID}', 'RTOController@requestSpecific');

Route::post('/rto/requestTime', 'RTOController@requestTime');
Route::put('/rto/requestTime', 'RTOController@editRTOtime');

Route::post('/rto/approval', 'RTOController@postApproval');
Route::put('/rto/approval', 'RTOController@editApproval');

Route::post('/mail/send', 'MailController@send');



});
// Request existing RTOs

// Post new approval.
Route::post('/rto/request/{request_id}/approval', 'RTOController@postApproval');  //  Post a new row in the timesheet_rtoapprovals table relevant to an existing RTO id.
// Edit existing approval
Route::put('/rto/request/{request_id}/approval/{approval_id}', 'RTOController@updateApproval');  //  Submitted from Supervisor to approve/deny (?modify) an RTO.
// Create a Json Web Token
Route::post('/createJWT', 'RTOController@createUserToken'); // Create a JWT for a specific user.
// Post a new RTO date/time
Route::get('/user/{user_id}/getSubordinates', 'RTOController@getSubordinates');
Route::get('/user/{user_id}/getSupervisors', 'RTOController@getSupervisors');


