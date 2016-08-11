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

    Route::post('/rto/data/list', 'RTOController@rtoDataList');


	
	
	// Campaign
	
	Route::get('campaign/list','CampaignController@getCampaignList');
	
	
	//Timesheet Controller
	Route::get('/u/rtobank/', 'TimesheetController@rto_allotment');
	Route::post('/rto/existingabsences/', 'TimesheetController@rto_existing_absences');
	Route::post('/timesheet/absencehours', 'TimesheetController@addAbsenceRequest');
	Route::get('/timesheet/holidaylist', 'TimesheetController@getHolidayList');
	Route::post('/timesheet/holiday', 'TimesheetController@addHoliday');
	Route::delete('/timesheet/holiday/{id}', 'TimesheetController@removeHoliday');
	
	//User

	
	Route::get('/user/', 'userController@identifyUser');
	Route::post('/user/new', 'userController@newUser');
	Route::post('/user/move', 'userController@moveUser');
	Route::post('/user/tree', 'userController@viewTree');
	Route::put('/user/edit', 'userController@editUser');
	Route::get('/user/list', 'userController@getUserList');
	Route::get('/user/{user_id}', 'userController@specificUser');

	
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

	
	//Furnace Routes
	
	Route::get('furnace/list', 'FurnaceController@getFurnaceList');
	
	//Furnace Run Routes
	
	Route::post('furnacerun/list/datatable', 'FurnaceController@furnaceRunDatatables');
	Route::get('furnacerun/type/list', 'FurnaceController@getFurnaceRunTypeList');
	Route::post('furnacerun/', 'FurnaceController@createFurnaceRun');
	Route::put('furnacerun/{id}', 'FurnaceController@editFurnaceRun');
	Route::post('furnacerun/{furnacerunID}/steel/{inventoryID}', 'FurnaceController@addFurnaceRunSteel');
	Route::delete('furnacerun/{furnacerunID}/steel/{inventoryID}', 'FurnaceController@deleteFurnaceRunSteel');
	Route::put('furnacerun/{furnacerunID}/steel/{inventoryID}', 'FurnaceController@editFurnaceRunSteel');
	Route::post('furnacerun/{furnacerunID}/operator/{employeeID}', 'FurnaceController@addFurnaceRunOperator');
	Route::delete('furnacerun/{furnacerunID}/operator/{employeeID}', 'FurnaceController@deleteFurnaceRunOperator');


	
	// Furnace Run Profile Routes
	
	Route::get('furnacerun/profile/list', 'FurnaceController@getFurnaceProfileList');
	
	
	//Slip
	Route::get('slip/list','SlipController@getSlipList');
	Route::get('slip/{id}','SlipController@getSlip');
	Route::put('slip/{id}','SlipController@updateSlip');
	

	//Slip Recipe 
	Route::get('slip/recipe/list','SlipController@getSlipRecipeList');
	Route::get('slip/recipe/{id}','SlipController@getSlipRecipe');
	
	// Slip Viscosity
	
	Route::get('slip/{id}/viscosity', 'SlipViscosityController@getSlipViscosity');
	Route::put('slip/{id}/viscosity', 'SlipViscosityController@editSlipViscosity');
	Route::post('slip/{id}/viscosity', 'SlipViscosityController@createSlipViscosity');
	

	//Slipcasting
	Route::post('slipcast', 'SlipcastingController@createSlipcast');
	Route::get('slipcast/{slipcast_id}', 'SlipcastingController@getSlipcast'); //works 7/4/2016
	Route::put('slipcast/{slipcast_id}', 'SlipcastingController@editSlipcast');
	Route::delete('slipcast/{slipcast_id}', 'SlipcastingController@deleteSlipcast');
		//Task Completion
		Route::post('slipcast/{slipcast_id}/task/{task_id}', 'SlipcastingController@postTaskCompletion');
		Route::delete('slipcast/{slipcast_id}/task/{task_id}', 'SlipcastingController@deleteTaskCompletion');

	//Slipcasting Steel
	Route::post('slipcast/{slipcast_id}/steel/{inventory_id}', 'SlipcastingController@addSteel');
	Route::put('slipcast/{slipcast_id}/steel/{steel_id}', 'SlipcastingController@editSteel');
	Route::delete('slipcast/{slipcast_id}/steel/{steel_id}', 'SlipcastingController@deleteSteel');
	
	//Slipcasting Steel Container Weight
	Route::put('slipcast/{slipcast_id}/steel/{steel_id}/container/{container_id}', 'SlipcastingController@editSlipcastSteelContainerWeight');

	// Slipcasting Operator
	Route::post('slipcast/{slipcast_id}/operator/{operator_id}', 'SlipcastingController@addOperator');
	Route::delete('slipcast/{slipcast_id}/operator/{operator_id}', 'SlipcastingController@removeOperator');

	//Slipcasting Profile
	Route::get('slipcast/profile/list', 'SlipcastingController@getSlipCastProfileList'); //works 2016-7-5
	Route::get('slipcast/profile/{id}', 'SlipcastingController@getSlipCastProfile');
        //Profile editing
        Route::put('slipcast/profile/{profile_id}/{key}/{value}', 'SlipcastingController@editSlipcastProfile');
        Route::post('slipcast/profile/{profile_id}/newstep/{index}', 'SlipcastingController@addSlipcastProfileStep');
        Route::put('slipcast/profile/{profile_id}/steps/{step}/{value}', 'SlipcastingController@editSlipcastProfileSteps');
        Route::delete('slipcast/profile/{profile_id}/steps/{step}/delete', 'SlipcastingController@deleteSlipcastProfileSteps');
        Route::post('slipcast/profile/{profile_id}/steps', 'SlipcastingController@editSlipcastProfileStepsOrder');
	
	//Slipcasting Table
	Route::get('slipcast/table/list','SlipcastingController@getSlipcastTableList');
	
	//Slipcasting Analytics
	Route::get('slipcast/controlcharts/slipweight/campaign/{campaign_id}','SlipcastAnalyticController@getSlipcastSlipUsedData');
	Route::get('slipcast/controlcharts/viscositycast/campaign/{campaign_id}','SlipcastAnalyticController@getSlipcastCastedViscosityData');
	Route::get('slipcast/controlcharts/percent-solvent/campaign/{campaign_id}','SlipcastAnalyticController@getSlipcastPercentSolventData');
	
	Route::get('slipcast/scatterplot/viscosity-vs-solventpercent/campaign/{campaign_id}','SlipcastAnalyticController@getSlipcastScatterSolventPercentViscosity');
	
	
	//Ramp Profiles
	
	Route::get('ramp/profile/list/{type}/{active}', 'RampProfileController@getRampProfileList');
	
	//Steel Routes
	Route::get('steel/list','SteelController@getSteelList');
	Route::post('steel/list/datatable','SteelController@getSteelDatatables');
	Route::get('steel/{id}','SteelController@getSteel');
	
	
	//Dropzone Routes
	
	Route::post('dropzone','DropzoneController@dropzoneUpload');
	Route::get('dropzone/{hash}','DropzoneController@getImages');
	Route::delete('dropzone/{hash}/{filename}','DropzoneController@deleteImage');
	
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

Route::get('/slipcast/{id}/toluene', 'SlipcastingController@tolueneData');
Route::get('/slip', 'SlipcastingController@slipData');
Route::get('/slipcast/{id}/humidity', 'SlipcastingController@humidityData');

Route::post('/slip/steel', 'SlipcastingController@slipData');




Route::post('slipcasting/list','SlipcastingController@slipDataList');


//Routes for FurnaceRun Model

Route::get('furnace','FurnaceController@FurnaceRun');

Route::get('/furnacerun/{furnacerunid}/steel','FurnaceController@furnacesteelrun');

Route::get('/furnacerun/{furnacerunid}/operator','FurnaceController@furnaceoperatorrun');

Route::get('/furnacerun/{furnacerunid}/properties','FurnaceController@furnacepropertiesrun');

Route::get('/furnacerun/{furnacerunid}/profile','FurnaceController@furnaceprofilerun');

Route::get('/furnacerun/{furnacerunid}','FurnaceController@buildFurnaceRun');


//Routes for Ramp Model

Route::get('/ramp/{rampprofileid}','RampProfileController@buidRampProfile');

//Routes for SearchPath Model

Route::get('/path/{furnacename}/{furnacerunname}','RampProfileController@setPath');

//Route for Image Processing

Route::get('/imageprocess/{experimenttype}/{coupontype}/{pressure}/{gritsize}/{loading}','ImageProcessingController@imageProcessing');