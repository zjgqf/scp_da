<?php

namespace App\Http\Controllers;

use App\Exports\SingleExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SinglesController extends Controller
{
    public function index()
    {
        return view('dingtalk.singles.index');
    }

    public function show(Request $request)
    {
        $data = $request->all();
        $field['user']  = $data['user'] ? substr($data['user'], strpos($data['user'], '/') + 1) : null ;
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            $result['total'] = 0;
            $result['rows'] = null;
            return $result;
        }

        //查询单票费用
        $accounts = $this->oracle->table('VW_BC_PUBLIC_ORDER_NO_DEL')
        ->selectRaw('PUBLIC_SALES_NAME AS sales,
                                 PUBLIC_SUB_BUSINESS_TYPE_NAME AS business,
                                 SUM(PUBLIC_CTN_TEU) AS teu,
                                 COUNT(BC_PUBLIC_ORDER_ID) AS total,
                                 SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SRS\')) AS business_srs,
                                 SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SPS\')) AS business_sps,
                                 SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SRS\')) -  SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SPS\')) AS profit')
        ->where('IS_VIRTUAL_ORDER', 'N')
        ->where('IS_DELETED', 'N')
        ->where('PUBLIC_SETTLE_OFFICE', 'WYCJ_ZJG')
        ->where('PUBLIC_IS_SHUT_OFF_LOAD', '<>', 'Y')
        ->when($field['user'], function ($query) use ($field) {
            return $query->where('PUBLIC_SALES_CODE', '=', $field['user']);
        })
        ->when($field['complete_begin'], function ($query) use ($field) {
            return $query->where('PUBLIC_COMPLETION_DATE', '>=', $field['complete_begin']);
        })
        ->when($field['complete_end'], function ($query) use ($field) {
            return $query->where('PUBLIC_COMPLETION_DATE', '<=', $field['complete_end']);
        })
        ->groupBy('PUBLIC_SALES_NAME', 'PUBLIC_SUB_BUSINESS_TYPE_NAME')
        ->orderBy('PUBLIC_SALES_NAME')
        ->orderBy('PUBLIC_SUB_BUSINESS_TYPE_NAME')
        ->paginate($data['limit']);

        //$accounts = $accounts->appends($field);
        $result['total'] = $accounts->total() ?? 0;
        $result['rows'] = $accounts->items() ?? null;
        return $result;

    }

    public function export(Request $request)
    {
        $data = $request->all();
        $field['user']  = $data['user'] ? substr($data['user'], strpos($data['user'], '/') + 1) : null ;
        $field['complete_begin'] = $data['complete_begin'] ?: null;
        $field['complete_end'] = $data['complete_end'] ?: null;

        $check = array_values($field);
        if($this->emptyArray($check)){
            return redirect()->route('singles.index')->with('danger','查询条件不能为空！');
        }

        //查询单票费用
        $accounts = $this->oracle->table('VW_BC_PUBLIC_ORDER_NO_DEL')
                    ->selectRaw('PUBLIC_SALES_NAME AS sales,
                                         PUBLIC_SUB_BUSINESS_TYPE_NAME AS business,
                                         SUM(PUBLIC_CTN_TEU) AS teu,
                                         COUNT(BC_PUBLIC_ORDER_ID) AS total,
                                    
                            
                                         SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SRS\')) AS business_srs,
                                         SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SPS\')) AS business_sps,
                                         SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SRS\')) -  SUM(FNC_BC_PUBLIC_ORDER_SINGLE(BC_PUBLIC_ORDER_ID, \'BASE_BUSINESS_SPS\')) AS profit')
                    ->where('IS_VIRTUAL_ORDER', 'N')
                    ->where('IS_DELETED', 'N')
                    ->where('PUBLIC_SETTLE_OFFICE', 'WYCJ_ZJG')
                    ->where('PUBLIC_IS_SHUT_OFF_LOAD', '<>', 'Y')
                    ->when($field['user'], function ($query) use ($field) {
                        return $query->where('PUBLIC_SALES_CODE', '=', $field['user']);
                    })
                    ->when($field['complete_begin'], function ($query) use ($field) {
                        return $query->where('PUBLIC_COMPLETION_DATE', '>=', $field['complete_begin']);
                    })
                    ->when($field['complete_end'], function ($query) use ($field) {
                        return $query->where('PUBLIC_COMPLETION_DATE', '<=', $field['complete_end']);
                    })
                    ->groupBy('PUBLIC_SALES_NAME', 'PUBLIC_SUB_BUSINESS_TYPE_NAME')
                    ->orderBy('PUBLIC_SALES_NAME')
                    ->orderBy('PUBLIC_SUB_BUSINESS_TYPE_NAME')
                    ->get();

        return Excel::download(new SingleExport($accounts),'单票利润.xlsx');
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
