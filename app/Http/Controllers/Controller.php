<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    public function validate(Request $request, array $rules, ...$params)
    {
        $validator = Validator::make($request->all(), $rules, ...$params);
    
        if ($validator->fails()) {
            abort(422, json_encode(['errors' => $validator->errors()]));
        }
    
        return $validator->validated();
    }
    
}
