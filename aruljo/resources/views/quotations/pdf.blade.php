<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->quote_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; line-height: 1.3; margin: 20px; }
        h2, h3, h4, h5 { margin: 2px 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .section { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: auto; }
        th, td { border: 1px solid #333; padding: 4px; vertical-align: top; font-size: 11px; word-wrap: break-word; }
        th { background: #e3f3e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        ul { margin: 2px 0 2px 12px; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #2e7d32; padding-bottom: 3px; margin-bottom: 8px; line-height:1.4; }
        .header h2 { color: #2e7d32; font-size: 16px; margin-bottom: 1px; }
        .subheading { color: #2e7d32; font-weight: bold; font-size: 12px; margin-bottom: 2px; margin-top: 8px; }
        .no-border td { border: none !important; padding: 2px 0; }
        .footer { text-align: center; margin-top: 12px; border-top: 1px solid #999; padding-top: 2px; font-size: 10px; line-height:1.2; }
        .bank-table td { padding: 2px 4px; }
        .two-col { display: table; width: 100%; margin-top: 10px; }
        .two-col > div { display: table-cell; vertical-align: top; width: 50%; }
        .bank-table td:first-child { width: 90px; font-weight: bold; }
        .additional-info-section { margin-top: 12px; }
        .additional-info-header {font-size: 12px; font-weight: bold;  color: #2e7d32; margin-bottom: 4px;}
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>

{{-- ======================== HEADER ======================== --}}
<div class="header">
    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <td style="width:15%; text-align:center; border:none; padding:0;">
                <img src="{{ public_path('icons/logo_small.png') }}"
                     alt="Logo"
                     style="width:90px; height:90px; display:block;">
            </td>
            <td style="width:85%; text-align:center; border:none; padding:0;">
                <h2 style="margin:0; color:#2e7d32;">ARULJOTHI PIPE WORKS</h2>
                <p style="margin:0; font-size:12px;">
                    Mobile: 7373738363 &nbsp; | &nbsp;
                    Email: aruljothi.pipeworks@gmail.com &nbsp; | &nbsp;
                    GSTN: 33AAWFA5558P1ZL
                </p>
            </td>
        </tr>
    </table>
</div>

{{-- ================= CUSTOMER INFO ================= --}}
<table class="no-border">
    <tr>
        <td style="width:60%;">
            <strong>To:</strong><br>
            {{ $version->customer_name ?? $quotation->lead->buyer_name }}<br>

            @if(!empty($version->customer_address_line1))
                {{ $version->customer_address_line1 }}<br>
            @endif
            @if(!empty($version->customer_address_line2))
                {{ $version->customer_address_line2 }}<br>
            @endif
            @if(!empty($version->customer_district) || !empty($version->customer_state))
                {{ $version->customer_district ?? '' }}
                @if(!empty($version->customer_district) && !empty($version->customer_state)), @endif
                {{ $version->customer_state ?? '' }}<br>
            @endif
            @if(!empty($version->customer_pincode))
                Pincode: {{ $version->customer_pincode }}<br>
            @endif

            @if(!empty($version->customer_gst_number))
                GST: {{ $version->customer_gst_number }}<br>
            @endif

            @if(!empty($version->customer_contact) || !empty($quotation->lead->buyer_contact))
                Contact: {{ $version->customer_contact ?? $quotation->lead->buyer_contact }}
            @endif
        </td>
        <td style="width:40%;">
            <strong>Quotation No:</strong> {{ $quotation->quote_number }}<br>
            <strong>Date:</strong> {{ $version->pdf_date ?? now()->format('d/m/Y') }}
        </td>
    </tr>
</table>

{{-- ================= SUBJECT ================= --}}
<div class="section">
    <p><strong>Subject:</strong> {{ $version->pdf_subject ?? 'Quotation for supply of RCC Products' }}</p>
    <p>Dear {{ $version->customer->name ?? $quotation->lead->buyer_name ?? 'Customer' }},</p>
    <p>In reference to your enquiry dated: {{ optional($quotation->lead->created_at)->format('d/m/Y') }},
       thank you for showing interest in our products. Please find below our quotation for your requirement.
    </p>
</div>

{{-- ================= QUOTATION SUMMARY ================= --}}
@php
    $grandSubtotal = 0;
    $gstRate = $version->gst_rate ?? 18;
    $cgstRate = $gstRate / 2;
    $sgstRate = $gstRate / 2;

    function formatINR($amount) {
        $amount = round($amount, 2);
        $sign = $amount < 0 ? '-' : '';
        $amount = abs($amount);

        // Split amount into whole and decimal parts
        $amount_parts = explode('.', number_format($amount, 2, '.', ''));
        $integer = $amount_parts[0];
        $decimal = isset($amount_parts[1]) ? '.' . $amount_parts[1] : '';

        // Handle Indian number grouping (3,2,2,...)
        $len = strlen($integer);
        if ($len > 3) {
            $last3 = substr($integer, -3);
            $rest = substr($integer, 0, -3);
            $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
            $formatted = $rest . ',' . $last3;
        } else {
            $formatted = $integer;
        }

        return $sign . '₹' . $formatted . $decimal;
    }

@endphp

<div class="section">
    <h4 class="subheading">Quotation Summary</h4>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Product Description</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total/Unit (₹)</th>
                <th class="text-right">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; $subtotal = 0; @endphp
            @foreach($version->priceDetails as $pd)
                @php
                    $product = $pd->product;
                    $unit = $product->unit->name ?? 'Nos';
                    $unitPrice = ($pd->unit_price ?? 0) + ($pd->transport_unit ?? 0);
                    $totalUnitPrice = $pd->total_unit_price ?? $unitPrice;
                    $amount = ($pd->total_qty ?? 1) * $unitPrice;
                    $subtotal += $amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ strtoupper($product->name) }}</td>
                    <td class="text-center nowrap">{{ strtoupper($unit) }}</td>
                    <td class="text-center nowrap">{{ number_format($pd->total_qty ?? 0, 0, '.', ',') }}</td>
                    <td class="text-right nowrap">{{ formatINR($totalUnitPrice) }}</td>
                    <td class="text-right nowrap">{{ formatINR($amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        @php
            $cgst = $subtotal * $cgstRate / 100;
            $sgst = $subtotal * $sgstRate / 100;
            $netTotal = $subtotal + $cgst + $sgst;
        @endphp
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Subtotal:</th>
                <th>{{ formatINR($subtotal) }}</th>
            </tr>
            <tr>
                <th colspan="5" class="text-right">GST (CGST {{ $cgstRate }}% + SGST {{ $sgstRate }}%):</th>
                <th>{{ formatINR($cgst + $sgst) }}</th>
            </tr>
            <tr style="background:#d8f3dc;">
                <th colspan="5" class="text-right">Total:</th>
                <th><strong>{{ formatINR($netTotal) }}</strong></th>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ================= PRICE BREAKDOWN ================= --}}
<div class="section">
    <h4 class="subheading">Price Breakdown (per unit)</h4>
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Product Description</th>
                <th class="text-right">Price / Unit (₹)</th>
                <th class="text-right">GST / Unit (₹)</th>
                <th class="text-right">Total / Unit (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($version->priceDetails as $pd)
                @php
                    $product = $pd->product;
                    $unitPrice = ($pd->unit_price ?? 0) + ($pd->transport_unit ?? 0);
                    $gstAmountPerUnit = $unitPrice * $gstRate / 100;
                    $totalUnitPrice = $unitPrice + $gstAmountPerUnit;
                @endphp
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ strtoupper($product->name) }}</td>
                    <td class="text-right nowrap">{{ formatINR($unitPrice) }}</td>
                    <td class="text-right nowrap">{{ formatINR($gstAmountPerUnit) }}</td>
                    <td class="text-right nowrap">{{ formatINR($totalUnitPrice) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ================= Additional Information ================= --}}
@if($version->additionalFields->count())
    <div class="section  additional-info-section">
        @php
            $fields = $version->additionalFields->chunk(2);
        @endphp
        @foreach($fields as $chunk)
            <div class="two-col">
                @foreach($chunk as $field)
                    <div>
                        <div class="subheading additional-info-header">{{ $field->heading }}</div>
                        <p>{!! nl2br(e($field->content)) !!}</p>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

{{-- ================= DELIVERY + TERMS ================= --}}
<div class="section">
    <h4 class="subheading">Terms and Conditions</h4>
    <p>{!! nl2br(e($version->pdf_terms ?? 'The above price includes loading and transportation. Unloading is under client scope.')) !!}</p>
    <h4 class="subheading">Delivery Terms</h4>
    <p>{!! nl2br(e($version->pdf_delivery ?? 'Delivery will be made to the address mentioned above within the agreed timeline.')) !!}</p>
</div>

{{-- ================= BANK + QR SECTION ================= --}}
<div class="section two-col">
    <div style="width:60%;">
        <h4 class="subheading">Account Details:</h4>
        <table class="no-border bank-table">
            <tr><td>Name:</td><td>ARULJOTHI PIPE WORKS</td></tr>
            <tr><td>Bank:</td><td>HDFC</td></tr>
            <tr><td>Branch:</td><td>KARUR</td></tr>
            <tr><td>Account No:</td><td>99997373738363</td></tr>
            <tr><td>IFSC:</td><td>HDFC0006914</td></tr>
        </table>
    </div>

    <div style="width:40%; margin:0 auto; text-align:center;">
        <h4 class="subheading">Scan to Pay</h4>
        <img src="{{ public_path('payment_qr/Aruljothi_qr_small.png') }}"
             alt="Payment QR"
             style="width:150px; height:120px;">
        <p style="margin-top:6px; font-size:11px;">
            <strong>UPI ID:</strong> aruljothipipeworks@sbi
        </p>
    </div>

</div>

{{-- ================= FOOTER ================= --}}
<div class="footer">
    3/106, Velampondi Village, Nenjikalipalayam, Tiruppur, Tamil Nadu - 639201
</div>

</body>
</html>
