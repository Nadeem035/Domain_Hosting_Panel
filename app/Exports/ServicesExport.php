<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServicesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return ['Service', 'Client', 'Type', 'Panel', 'Plan', 'Created', 'Expiry', 'Provider cost', 'Client price', 'Profit', 'Currency', 'Status'];
    }

    public function map($service): array
    {
        return [
            $service->domain_name ?: $service->hostingPlan?->name ?: 'Service #'.$service->id,
            $service->client?->name,
            $service->type->label(),
            $service->panel?->name,
            $service->hostingPlan?->name,
            $service->created_date?->toDateString(),
            $service->expiry_date?->toDateString(),
            (float) $service->company_price,
            (float) $service->client_price,
            $service->profit(),
            $service->currency,
            $service->status->label(),
        ];
    }
}