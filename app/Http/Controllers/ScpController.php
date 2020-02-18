<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScpController extends Controller
{
    public function userList(Request $request)
    {
        $user = '%'. $request->user. '%';

        $users = $this->oracle->table('SYS_USER')
                    ->join('SYS_OFFICE', 'SYS_USER.SYS_OFFICE_ID', '=', 'SYS_OFFICE.SYS_OFFICE_ID')
                    ->select('sys_user.user_name', 'sys_user.user_name_cn')
                    ->where(function ($query) use ($user) {
                        $query->where('sys_user.user_name_cn','like', $user)
                            ->orWhere('sys_user.user_name','like', $user);
                    })
                    ->where('sys_office.settle_office_code', '=','WYCJ_ZJG')
                    ->get();
        return $users;
    }

    public function departmentList(Request $request)
    {
        $department = '%'. $request->department. '%';

        $departments = $this->oracle->table('SYS_OFFICE')
                        ->select('office_name','office_code')
                        ->where(function ($query) use($department){
                            $query->where('office_name', 'like', $department)
                                ->orWhere('office_code', 'like', $department);
                        })
                        ->where('settle_office_code', '=', 'WYCJ_ZJG')
                        ->get();

        return $departments;
    }

}
