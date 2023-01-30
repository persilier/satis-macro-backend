<?php

namespace Satis2020\ServicePackage\Exports\Demo;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;

class ClientExportDemo implements FromCollection, WithHeadings, ShouldQueue
{
    protected $data;

    /**
     * Write code on Method
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Write code on Method
     *
     * @return Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'institution',
            'category_client' ,
            'account_type',
            'account_number' ,
            'firstname' ,
            'lastname',
            'sexe' ,
            'telephone' ,
            'email',
            'ville',
        ];
    }
}