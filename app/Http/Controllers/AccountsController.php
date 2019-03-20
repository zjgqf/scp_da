<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountExport;

class AccountsController extends Controller
{

    public function index()
    {
        return view('dingtalk.accounts.index');
    }

    public function show(Request $request)
    {
        $field = $request->all();
        $field['department'] = substr($field['department'], strpos($field['department'], '/') + 1) ;
        $field['user']  = substr($field['user'], strpos($field['user'], '/') + 1) ;
        $field['complete_begin'] = str_replace('-','',$field['complete_begin']);
        $field['complete_end'] = str_replace('-','',$field['complete_end']);
        $field['check_begin'] = str_replace('-','',$field['check_begin']);
        $field['check_end'] = str_replace('-','',$field['check_end']);

        if(empty($field['check_begin']) || empty($field['check_end'])) {
            $result['total'] = '0';
            $result['rows'] = null;
            return $result;
        }

        //查询到账利润数据
        $accounts = $this->oracle->table('V_FREIGHT_ZJG as BCFR')
                        ->leftJoin('V_PUBLIC_ORDER_ZJG as BCOR', 'BCFR.BC_PUBLIC_ORDER_ID', '=', 'BCOR.BC_PUBLIC_ORDER_ID')
                        ->selectRaw('BCOR.PUBLIC_CANVASSION_DEPARTMENT AS department,
                                    BCOR.PUBLIC_SALES_NAME AS sales,
                                    BCOR.PUBLIC_CONSIGNOR_NAME AS consignor,
                                    SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END) AS receive,
                                    (SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END) - SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AP\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END)) AS profit')
                        ->when($field['department'], function ($query) use ($field) {
                            if($field['department'] !== 'WYCJ_ZJG') {
                                return $query->where('BCOR.PUBLIC_SALES_DEPARTMENT_CODE', '=', $field['department']);
                            }
                        })
                        ->when($field['user'], function ($query) use ($field) {
                            return $query->where('BCOR.PUBLIC_SALES_CODE', '=', $field['user']);
                        })
                        ->when($field['complete_begin'], function ($query) use ($field) {
                            return $query->where('BCOR.PUBLIC_COMPLETION_DATE', '>=', $field['complete_begin']);
                        })
                        ->when($field['complete_end'], function ($query) use ($field) {
                            return $query->where('BCOR.PUBLIC_COMPLETION_DATE', '<=', $field['complete_end']);
                        })
                        ->when($field['check_begin'], function ($query) use ($field) {
                            return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) >= ?', [$field['check_begin']]);
                        }, function ($query) {
                            return $query->where('1', '=', '2');
                        })
                        ->when($field['check_end'], function ($query) use ($field) {
                            return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) <= ?', [$field['check_end']]);
                        },function ($query) {
                            return $query->where('1', '=', '2');
                        })
                        ->groupBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT', 'BCOR.PUBLIC_SALES_NAME', 'BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->orderBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT')
                        ->orderBy('BCOR.PUBLIC_SALES_NAME')
                        ->orderBy('BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->paginate($field['limit']);
        //$accounts = $accounts->appends($field);
        $result['total'] = $accounts->total();
        $result['rows'] = $accounts->items();
        return $result;
        //$headers = ['揽货部门', '揽货人', '客户名称', '本位币不含税应收', '本位币含税利润'];
        //return view('dingtalk.accounts.index',['accounts' => $accounts,'headers' => $headers]);

    }

    public function export(Request $request)
    {
        $field = $request->all();
        $field['department'] = substr($field['department'], strpos($field['department'], '/') + 1) ;
        $field['user']  = substr($field['user'], strpos($field['user'], '/') + 1) ;
        $field['complete_begin'] = str_replace('-','',$field['complete_begin']);
        $field['complete_end'] = str_replace('-','',$field['complete_end']);
        $field['check_begin'] = str_replace('-','',$field['check_begin']);
        $field['check_end'] = str_replace('-','',$field['check_end']);


        if(empty($field['check_begin']) || empty($field['check_end'])) {
            return redirect()->route('accounts.search');
        }

        $accounts = $this->oracle->table('V_FREIGHT_ZJG as BCFR')
            ->leftJoin('V_PUBLIC_ORDER_ZJG as BCOR', 'BCFR.BC_PUBLIC_ORDER_ID', '=', 'BCOR.BC_PUBLIC_ORDER_ID')
            ->selectRaw('BCOR.PUBLIC_CANVASSION_DEPARTMENT AS department,
                                    BCOR.PUBLIC_SALES_NAME AS sales,
                                    BCOR.PUBLIC_CONSIGNOR_NAME AS consignor,
                                    SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END) AS receive,
                                    (SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END) - SUM(CASE
                                                WHEN BCFR.LEDGER_TYPE_CODE = \'AP\' THEN NVL(BCFR.ESTIMATED_AMOUNT, 0)
                                                ELSE 0
                                            END)) AS profit')
            ->when($field['department'], function ($query) use ($field) {
                if($field['department'] !== 'WYCJ_ZJG') {
                    return $query->where('BCOR.PUBLIC_SALES_DEPARTMENT_CODE', '=', $field['department']);
                }
            })
            ->when($field['user'], function ($query) use ($field) {
                return $query->where('BCOR.PUBLIC_SALES_CODE', '=', $field['user']);
            })
            ->when($field['complete_begin'], function ($query) use ($field) {
                return $query->where('BCOR.PUBLIC_COMPLETION_DATE', '>=', $field['complete_begin']);
            })
            ->when($field['complete_end'], function ($query) use ($field) {
                return $query->where('BCOR.PUBLIC_COMPLETION_DATE', '<=', $field['complete_end']);
            })
            ->when($field['check_begin'], function ($query) use ($field) {
                return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) >= ?', [$field['check_begin']]);
            }, function ($query) {
                return $query->where('1', '=', '2');
            })
            ->when($field['check_end'], function ($query) use ($field) {
                return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) <= ?', [$field['check_end']]);
            },function ($query) {
                return $query->where('1', '=', '2');
            })
            ->groupBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT', 'BCOR.PUBLIC_SALES_NAME', 'BCOR.PUBLIC_CONSIGNOR_NAME')
            ->orderBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT')
            ->orderBy('BCOR.PUBLIC_SALES_NAME')
            ->orderBy('BCOR.PUBLIC_CONSIGNOR_NAME')
            ->get();

        return Excel::download(new AccountExport($accounts),'到账利润.xlsx');
    }



}
