<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class rtoapprovalUpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {	   //(*temp*)
        return true;	// Must employ a modified version of the hierarchy table to determine whether an employee has approval to approve RTO.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
