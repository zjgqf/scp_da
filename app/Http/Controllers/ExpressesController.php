<?php

namespace App\Http\Controllers;

use App\Exports\ExpressExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExpressesController extends Controller
{
    public function index()
    {
        return view('dingtalk.expresses.index');
    }

    public function show(Request $request)
    {
        $data = $request->all();
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            $result['total'] = 0;
            $result['rows'] = null;
            return $result;
        }

        //查询单票费用
        $accounts = $this->oracle->table('EX_ORDER')
                    ->selectRaw('
                                BUSINESS_DATE, 
                                SHIPPER_ACCOUNT_NO, 
                                MBL_NO, 
                                NO_OF_PACKAGE, 
                                TO_CHAR(CHARGING_WEIGHT,\'fm9999999990.00\') as charging_weight, 
                                CARGO_TYPE, 
                                CONSIGNEE_COUNTRY_NAME, 
                                SALES_NAME, 
                                PAYMENT_MODE, 
                                SETTLE_CUST_NAME')
                    ->where('SETTLE_OFFICE', 'WYCJ_ZJG')
                    ->where('IS_SHUT_OFF_LOAD', '<>', 'Y')
                    ->where('PAYMENT_MODE', 'P')
                    ->where('CARGO_TYPE', 'S')
                    ->where('COMPANY_CODE', 'TNTWX')
                    ->where('CHARGING_WEIGHT', '>=', 0.5)
                    ->where('CHARGING_WEIGHT', '<=', 3)
                    ->when($field['complete_begin'], function ($query) use ($field) {
                        return $query->where('BUSINESS_DATE', '>=', $field['complete_begin']);
                    })
                    ->when($field['complete_end'], function ($query) use ($field) {
                        return $query->where('BUSINESS_DATE', '<=', $field['complete_end']);
                    })
                    ->paginate($data['limit']);

        //$accounts = $accounts->appends($field);
        $result['total'] = $accounts->total() ?? 0;
        $result['rows'] = $accounts->items() ?? null;
        return $result;

    }

    public function export(Request $request)
    {
        $data = $request->all();
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            return redirect()->route('singles.index')->with('danger','查询条件不能为空！');
        }

        //查询单票费用
        $accounts = $this->oracle->table('EX_ORDER')
            ->selectRaw('
                        BUSINESS_DATE, 
                        SHIPPER_ACCOUNT_NO, 
                        MBL_NO, 
                        NO_OF_PACKAGE, 
                        TO_CHAR(CHARGING_WEIGHT,\'fm9999999990.00\') as charging_weight, 
                        CARGO_TYPE, 
                        CONSIGNEE_COUNTRY_NAME, 
                        SALES_NAME, 
                        PAYMENT_MODE, 
                        SETTLE_CUST_NAME')
            ->where('SETTLE_OFFICE', 'WYCJ_ZJG')
            ->where('IS_SHUT_OFF_LOAD', '<>', 'Y')
            ->where('PAYMENT_MODE', 'P')
            ->where('CARGO_TYPE', 'S')
            ->where('COMPANY_CODE', 'TNTWX')
            ->where('CHARGING_WEIGHT', '>=', 0.5)
            ->where('CHARGING_WEIGHT', '<=', 3)
            ->when($field['complete_begin'], function ($query) use ($field) {
                return $query->where('BUSINESS_DATE', '>=', $field['complete_begin']);
            })
            ->when($field['complete_end'], function ($query) use ($field) {
                return $query->where('BUSINESS_DATE', '<=', $field['complete_end']);
            })
            ->get();

        return Excel::download(new ExpressExport($accounts),'快件.xlsx');
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
