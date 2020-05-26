<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InterfacesController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->name;
        $customs =$this->oracle->table('V_CS_CUST')
                    ->selectRaw('DISTINCT CUST_NAME_CN AS NAME')
                    ->where('CUST_NAME_CN', 'like', '%'.$name .'%')
                    ->orWhere('CUST_NAME_EN', 'like', '%'.$name .'%')
                    ->orWhere('MEMONIC_NO', 'like', '%'.$name .'%')
                    ->limit(10)
                    ->get();

        return $customs;
    }
}
