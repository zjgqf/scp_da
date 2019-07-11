<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SingleExport implements FromCollection,WithHeadings
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
            '揽货人',
            '业务子类',
            'TEU',
            '票数',
            '业务日期汇率不含税收入',
            '业务日期汇率不含税成本',
            '业务日期汇率毛利',
        ];
    }
}
