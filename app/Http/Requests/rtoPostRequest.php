<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class rtoPostRequest extends Request
{	
	// Tool for ensuring form data is entered correctly.
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
	// Can be edited to ensure proper validation in the future.
    public function authorize() 
    {
        return true;
    } 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
		//inputs for timesheet_rto
		'requestID'		=> 'required|integer',
		'employeeID'	=> 'required|integer',
		'status'		=> 'required|string',
		'reason'		=> 'string',
		
        ];
    }
}
