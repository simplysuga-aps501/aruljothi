<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * Fetch all leads (including soft-deleted)
     */
    public function collection()
    {
        return Lead::with(['location'])->withTrashed()->orderBy('id', 'desc')->get();
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
     * Map each lead record to export row
     */
    public function map($lead): array
    {
        $location = $lead->location;

        // Combine location details (as shown in UI)
        $deliveryLocation = 'N/A';
        if ($location) {
            $deliveryLocation = "{$location->place}, {$location->district}, {$location->state} - {$location->pincode}";
        }



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
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // ID
            'B' => 20,  // Platform
            'C' => 20,  // Lead Date
            'D' => 25,  // Buyer Name
            'E' => 25,  // Buyer Location
            'F' => 15,  // Buyer Contact
            'G' => 25,  // Platform Keyword
            'H' => 40,  // Product Detail
            'I' => 40,  // Delivery Location
            'J' => 20,  // Expected Delivery Date
            'K' => 40,  // Remarks
            'L' => 20,  // Follow Up Date
            'M' => 15,  // Status
            'N' => 20,  // Assigned To
            'O' => 30,  // User Log
            'P' => 20,  // Modified By
            'Q' => 20,  // Deleted At
            'R' => 20,  // Created At
            'S' => 20,  // Updated At
        ];
    }

    /**
     * Add styles (bold header + borders)
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        $sheet->getStyle('A1:S1')->getBorders()->getBottom()->setBorderStyle('thin');
        $sheet->getDefaultRowDimension()->setRowHeight(18);
        return [];
    }
}
