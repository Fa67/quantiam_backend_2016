<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class rtoapprovalPostRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;  //  Not authorization needed; only posts a 'pending' table.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
			'requestID'			=> 'required|integer',
			'employeeID'		=> 'required|integer',
			'supervisorID'		=> 'required|integer',
            'supervisorlevel'	=> 'required|alpha',
			'reason'			=> 'alpha',
			// 'approval' is initiated as 'pending' & ammended in route::put(rto/requests/.../ @updateApproval)
			
        ];
    }
}
