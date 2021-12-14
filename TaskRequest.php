<?php

namespace App\Request;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
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
            "name"=>"required",
            "client_id"=>"required",
            "status"=>"required"
        ];
    }

    public function messages()
    {
        return [
            "name.required"=>"name is required",
            "client_id.required"=>"client is required",
        ];
    }
}