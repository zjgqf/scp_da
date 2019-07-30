<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpressExport implements FromCollection,WithHeadings
{
    //需要导出的数据
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            '业务日期',
            '发货人账号',
            '运单号',
            '件数',
            '计费重量',
            '货物种类',
            '收件国家',
            '揽货人',
            '付款方式',
            '结算对象',
        ];
    }
}
