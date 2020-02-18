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
        $data = $request->all();
        $field['department'] = $data['department'] ? substr($data['department'], strpos($data['department'], '/') + 1) : null;
        $field['user']  = $data['user'] ? substr($data['user'], strpos($data['user'], '/') + 1) : null ;
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;
        $field['check_begin'] = $data['check_begin'] ?: null;
        $field['check_end'] = $data['check_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            $result['total'] = 0;
            $result['rows'] = null;
            return $result;
        }

        //查询到账利润数据
        $accounts = $this->oracle->table('BC_FREIGHT as BCFR')
                        ->leftJoin('BC_PUBLIC_ORDER as BCOR', 'BCFR.BC_PUBLIC_ORDER_ID', '=', 'BCOR.BC_PUBLIC_ORDER_ID')
                        ->selectRaw('BCOR.PUBLIC_CANVASSION_DEPARTMENT AS department,
                                    BCOR.PUBLIC_SALES_NAME AS sales,
                                    BCOR.PUBLIC_CONSIGNOR_NAME AS consignor,
                                    ROUND(SUM(CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END),2)  AS receive,
                                    ROUND(SUM((CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END) - (CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AP\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END)),2) AS profit')
                        ->whereRaw("NVL(BCFR.OP_TYPE,'INSERT') <> ? ", ['DELETE'])
                        ->where('BCOR.IS_VIRTUAL_ORDER', 'N')
                        ->where('BCOR.IS_DELETED', 'N')
                        ->where('BCOR.PUBLIC_SETTLE_OFFICE', 'WYCJ_ZJG')
                        ->where('BCOR.PUBLIC_IS_SHUT_OFF_LOAD', '<>', 'Y')
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
                            return $query->whereRaw("NVL(BCFR.WRITEOFF_STATUS,'S') <> ? ", ['W'] );
                        })
                        ->when($field['check_end'], function ($query) use ($field) {
                            return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) <= ?', [$field['check_end']]);
                        })
                        ->groupBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT', 'BCOR.PUBLIC_SALES_NAME', 'BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->orderBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT')
                        ->orderBy('BCOR.PUBLIC_SALES_NAME')
                        ->orderBy('BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->paginate($data['limit']);

        //$accounts = $accounts->appends($field);
        $result['total'] = $accounts->total() ?? 0;
        $result['rows'] = $accounts->items() ?? null;
        return $result;
        //$headers = ['揽货部门', '揽货人', '客户名称', '本位币不含税应收', '本位币含税利润'];
        //return view('dingtalk.accounts.index',['accounts' => $accounts,'headers' => $headers]);

    }

    public function export(Request $request)
    {
        $data = $request->all();
        $field['department'] = $data['department'] ? substr($data['department'], strpos($data['department'], '/') + 1) : null;
        $field['user']  = $data['user'] ? substr($data['user'], strpos($data['user'], '/') + 1) : null ;
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;
        $field['check_begin'] = $data['check_begin'] ?: null;
        $field['check_end'] = $data['check_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            return redirect()->route('accounts.index')->with('danger','查询条件不能为空！');
        }

        //查询到账利润数据
        $accounts = $this->oracle->table('BC_FREIGHT as BCFR')
            ->leftJoin('BC_PUBLIC_ORDER as BCOR', 'BCFR.BC_PUBLIC_ORDER_ID', '=', 'BCOR.BC_PUBLIC_ORDER_ID')
            ->selectRaw('BCOR.PUBLIC_CANVASSION_DEPARTMENT AS department,
                                    BCOR.PUBLIC_SALES_NAME AS sales,
                                    BCOR.PUBLIC_CONSIGNOR_NAME AS consignor,
                                    ROUND(SUM(CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END),2)  AS receive,
                                    ROUND(SUM((CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AR\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END) - (CASE WHEN BCFR.LEDGER_TYPE_CODE = \'AP\' THEN
                                          NVL(BCFR.PRIME_ESTIMATED_SETTLE_AMOUNT * BCFR.BUSINESS_EXCHANGE_RATE, 0) ELSE 0
                                          END)),2) AS profit')
                        ->whereRaw("NVL(BCFR.OP_TYPE,'INSERT') <> ? ", ['DELETE'])
                        ->where('BCOR.IS_VIRTUAL_ORDER', 'N')
                        ->where('BCOR.IS_DELETED', 'N')
                        ->where('BCOR.PUBLIC_SETTLE_OFFICE', 'WYCJ_ZJG')
                        ->where('BCOR.PUBLIC_IS_SHUT_OFF_LOAD', '<>', 'Y')
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
                            return $query->whereRaw("NVL(BCFR.WRITEOFF_STATUS,'S') <> ? ", ['W'] );
                        })
                        ->when($field['check_end'], function ($query) use ($field) {
                            return $query->whereRaw('SUBSTR(BCFR.CHECK_DATE,1,8) <= ?', [$field['check_end']]);
                        })
                        ->groupBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT', 'BCOR.PUBLIC_SALES_NAME', 'BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->orderBy('BCOR.PUBLIC_CANVASSION_DEPARTMENT')
                        ->orderBy('BCOR.PUBLIC_SALES_NAME')
                        ->orderBy('BCOR.PUBLIC_CONSIGNOR_NAME')
                        ->get();
        return Excel::download(new AccountExport($accounts),'到账利润.xlsx');
    }

    function emptyArray($array) {
        $is_empty = true;
        foreach($array as $a){
            if(!empty($a)){
                $is_empty = false;
                break;
            }
        }
        return $is_empty;
    }

}
