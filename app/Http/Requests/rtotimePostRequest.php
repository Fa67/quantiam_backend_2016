<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class rtotimePostRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     * Returns true for now.
     * @return bool
     */
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
            'requestID'	=> 'required|Integer',
			'type'		=> 'required|Alpha',
			'hours'		=> 'required|Numeric|min:0.5',
			'date'		=> 'required|date',
			
        ];
    }
}
