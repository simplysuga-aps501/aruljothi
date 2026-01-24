<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    /**
     * Use a query instead of fetching everything
     */
    public function query()
    {
        return Lead::with(['location'])->withTrashed()->orderBy('id', 'desc');
    }

    /**
     * Define Excel headers
     */
    public function headings(): array
    {
        return [
            'ID',
            'Platform',
            'Lead Date',
            'Buyer Name',
            'Buyer Location',
            'Buyer Contact',
            'Platform Keyword',
            'Product Detail',
            'Delivery Location',
            'Expected Delivery Date',
            'Remarks',
            'Follow Up Date',
            'Status',
            'Assigned To',
            'User Log',
            'Modified By',
            'Deleted At',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Map each lead to row
     */
    public function map($lead): array
    {
        $location = $lead->location;

        $deliveryLocation = $location
            ? "{$location->place}, {$location->district}, {$location->state} - {$location->pincode}"
            : 'N/A';

        return [
            $lead->id,
            $lead->platform,
            $lead->lead_date,
            $lead->buyer_name,
            $lead->buyer_location,
            $lead->buyer_contact,
            $lead->platform_keyword,
            $lead->product_detail,
            $deliveryLocation,
            $lead->expected_delivery_date,
            $lead->remarks,
            $lead->follow_up_date,
            $lead->status,
            $lead->assigned_to,
            $lead->user_log,
            $lead->modified_by,
            $lead->deleted_at,
            $lead->created_at,
            $lead->updated_at,
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 20,
            'C' => 20,
            'D' => 25,
            'E' => 25,
            'F' => 15,
            'G' => 25,
            'H' => 40,
            'I' => 40,
            'J' => 20,
            'K' => 40,
            'L' => 20,
            'M' => 15,
            'N' => 20,
            'O' => 30,
            'P' => 20,
            'Q' => 20,
            'R' => 20,
            'S' => 20,
        ];
    }

    /**
     * Styles
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        $sheet->getStyle('A1:S1')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getDefaultRowDimension()->setRowHeight(18);
        return [];
    }

    /**
     * Chunk size for query
     */
    public function chunkSize(): int
    {
        return 1000; // fetch 1000 rows at a time
    }
}
