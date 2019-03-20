<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AccountExport implements FromCollection,WithHeadings
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
            '揽货部门',
            '揽货人',
            '客户名称',
            '本位币不含税收入',
            '本位币不含税利润',
        ];
    }
}
