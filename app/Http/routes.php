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
	Route::get('/timesheet/holidaylist', 'TimesheetController@getHolidayList');
	Route::post('/timesheet/holiday', 'TimesheetController@addHoliday');
	Route::delete('/timesheet/holiday/{id}', 'TimesheetController@removeHoliday');
	
	//User

	Route::post('/user/new', 'userController@newUser');
	Route::post('/user/move', 'userController@moveUser');
	Route::post('/user/tree', 'userController@viewTree');
	Route::put('/user/edit', 'userController@editUser');
	// Load/search all users
	Route::get('/users', 'userController@getUsers');
	Route::get('/userlistactive', 'userController@getActiveUserList');
	Route::get('/supervisors', 'userController@getSupervisors');

	//Comment

	Route::get('/comment/', 'CommentController@getComments');
	Route::post('/comment/', 'CommentController@createComment');
	Route::delete('/comment/{commentID}', 'CommentController@deleteComment');

	//Group


	Route::get('grouplist', 'GroupController@GroupList');
	//Route::get('group/{groupID}', GroupController@GroupInfo);
	//Route::post('group/', GroupController@GroupCreation);
	//Route::put('group/{groupID}', GroupController@GroupEdit);
	//Route::delete('group/{groupID}',GroupController@GroupDelete);


	//Group Member routes

	Route::delete('group/{groupID}/user/{userID}', 'GroupController@removeUserFromGroup');
	Route::post('group/{groupID}/user/{userID}', 'GroupController@addUserToGroup');

	//Slip
	Route::get('slip/{id}','SlipmakingController@getSlip');

	//Slip Recipe 
	Route::get('slip/recipe/{id}','SlipmakingController@getSlipRecipe');

	//Slipcasting
	Route::post('slipcast', 'SlipcastingController@createSlipcast');
	Route::get('slipcast/{slipcast_id}', 'SlipcastingController@getSlipcast'); //works 7/4/2016
	Route::put('slipcast/{slipcast_id}', 'SlipcastingController@editSlipcast');
	Route::delete('slipcast/{slipcast_id}', 'SlipcastingController@deleteSlipcast');

	//Slipcasting Steel
	Route::post('slipcast/{slipcast_id}/steel/{inventory_id}', 'SlipcastingController@addSteel');
	Route::put('slipcast/{slipcast_id}/steel/{steel_id}', 'SlipcastingController@editSteel');
	Route::delete('slipcast/{slipcast_id}/steel/{steel_id}', 'SlipcastingController@deleteSteel');

	// Slipcasting Operator
	Route::post('slipcast/{slipcast_id}/operator/{operator_id}', 'SlipcastingController@addOperator');
	Route::delete('slipcast/{slipcast_id}/operator/{operator_id}', 'SlipcastingController@removeOperator');

	//Slipcasting Profile
	Route::get('slipcast/profile/list', 'SlipcastingController@getSlipCastProfileList'); //works 2016-7-5
	
	//Slipcasting Table
	Route::get('slipcast/table/list','SlipcastingController@getSlipcastTableList');
	
	//Ramp Profiles
	
	Route::get('ramp/profile/list/{type}/{active}', 'RampProfileController@getRampProfileList');

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

Route::get('/csv', 'SlipcastingController@tolueneData');
Route::get('/slip', 'SlipcastingController@slipData');
Route::get('/humidity', 'SlipcastingController@humidityData');

Route::post('/slip/steel', 'SlipcastingController@slipData');




Route::get('slipcasting/list','SlipcastingController@slipDataList');

Route::get('furnace','FurnaceController@FurnaceRun');

Route::get('/furnacerun/{furnacerunid}/steel','FurnaceController@furnacesteelrun');

Route::get('/furnacerun/{furnacerunid}/operator','FurnaceController@furnaceoperatorrun');

Route::get('/furnacerun/{furnacerunid}/properties','FurnaceController@furnacepropertiesrun');

Route::get('/furnacerun/{furnacerunid}','FurnaceController@buildFurnaceRun');
