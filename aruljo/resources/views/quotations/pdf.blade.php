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
        table { width: 100%; border-collapse: collapse; margin-top: 6px; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 4px; vertical-align: top; font-size: 11px; }
        th { background: #e3f3e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        td { word-wrap: break-word; }
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
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h2>ARULJOTHI PIPE WORKS</h2>
        <p style="margin: 0; font-size: 12px;">
            Mobile: 7373738363 &nbsp; | &nbsp;
            Email: aruljothi.pipeworks@gmail.com &nbsp; | &nbsp;
            GSTN: 33AAWFA5558P1ZL
        </p>
    </div>

    {{-- Quotation & Customer Info --}}
    <table class="no-border">
        <tr>
           <td style="width:60%;">
               <strong>To:</strong><br>
               {{ $version->customer_name ?? $quotation->lead->buyer_name }}<br>

               {{-- Address lines --}}
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
                   PIN: {{ $version->customer_pincode }}<br>
               @endif

               {{-- GST --}}
               @if(!empty($version->customer_gst_number))
                   GST: {{ $version->customer_gst_number }}<br>
               @endif

               {{-- Contact --}}
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

    {{-- Subject & Greeting --}}
    <div class="section">
        <p><strong>Subject:</strong> {{ $version->pdf_subject ?? 'Quotation for supply of RCC Products' }}</p>
        <p>
            Dear {{ $version->customer->name ?? $quotation->lead->buyer_name ?? 'Customer' }},
        </p>
        <p>
            In reference to your enquiry dated: {{ optional($quotation->lead->created_at)->format('d/m/Y') }},
            thank you for showing interest in our products. Please find below our quotation for your requirement.
        </p>
    </div>

    {{-- Quotation Summary Table --}}
    @php
        $grandSubtotal = 0;
        $gstRate = $version->gst_rate ?? 18;
        $cgstRate = $gstRate / 2;
        $sgstRate = $gstRate / 2;
    @endphp
    <div class="section">
        <h4 class="subheading">Quotation Summary</h4>
        <table>
            <thead>
                <tr>
                    <th style="width:5%">S.No</th>
                    <th style="width:45%">Product Description</th>
                    <th style="width:10%">Unit</th>
                    <th style="width:8%">Qty</th>
                    <th style="width:12%"> Total/Unit (₹) </th>
                    <th style="width:10%"> Amount (₹) </th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; $subtotal = 0; @endphp
                @foreach($version->priceDetails as $pd)
                    @php
                        $product = $pd->product;
                        $unit = $product->unit->name ?? 'Nos';
                        $unitPrice = ($pd->unit_price ?? 0) + ($pd->transport_unit ?? 0);
                        $totalUnitPrice = $pd->total_unit_price;
                        $amount = ($pd->total_qty ?? 1) * $unitPrice;
                        $subtotal += $amount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td style="text-align:left;">{{ strtoupper($product->name) }}</td>
                        <td class="text-center">{{ strtoupper($unit) }}</td>
                        <td class="text-center">{{ $pd->total_qty }}</td>
                        <td class="text-right">{{ number_format($totalUnitPrice, 2) }}</td>
                        <td class="text-right">{{ number_format($amount, 2) }}</td>
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
                    <th>₹{{ number_format($subtotal, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="5" class="text-right">
                        GST (CGST {{ number_format($cgstRate, 0) }}% + SGST {{ number_format($sgstRate, 0) }}%):
                    </th>
                    <th>₹{{ number_format($cgst + $sgst, 2) }}</th>
                </tr>
                <tr style="background:#d8f3dc;">
                    <th colspan="5" class="text-right">Total:</th>
                    <th><strong>₹{{ number_format($netTotal, 2) }}</strong></th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Price Breakdown Table --}}
    <div class="section">
        <h4 class="subheading">Price Breakdown (per unit)</h4>
        <table>
            <thead>
                <tr>
                    <th style="width:5%">S.No</th>
                    <th style="width:45%">Product Description</th>
                    <th style="width:15%">Price / Unit (₹)</th>
                    <th style="width:15%">GST / Unit (₹)</th>
                    <th style="width:15%">Total / Unit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach($version->priceDetails as $pd)
                    @php
                        $product = $pd->product;
                        $unitPrice = ($pd->unit_price ?? 0) + ($pd->transport_unit ?? 0);
                        $gstAmountPerUnit = $gstRate ? $unitPrice * $gstRate / 100 : 0;
                        $totalUnitPrice = $unitPrice + $gstAmountPerUnit;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td style="text-align:left;">{{ strtoupper($product->name) }}</td>
                        <td class="text-right">{{ number_format($unitPrice, 2) }}</td>
                        <td class="text-right">{{ number_format($gstAmountPerUnit, 2) }}</td>
                        <td class="text-right">{{ number_format($totalUnitPrice, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Delivery & Terms --}}
    <div class="section">
        <h4 class="subheading">Delivery Instructions:</h4>
        <p style="margin:2px 0; font-size:11px;">
            {{ $version->pdf_delivery ?? 'Materials are readily available. We can supply your requirement within 2 days as per your delivery schedule after placing your order.' }}
        </p>

        <h4 class="subheading">Terms & Conditions:</h4>
        <p style="margin:2px 0; font-size:11px;">
            {{ $version->pdf_terms ?? 'The above price includes loading and transportation. Unloading is under client scope.' }}
        </p>
    </div>

    {{-- Bank + Delivery Location (Side by Side) --}}
    <div class="section two-col">
        <div>
            <h4 class="subheading">Account Details:</h4>
            <table class="no-border bank-table">
                <tr><td>Name:</td><td>ARULJOTHI PIPE WORKS</td></tr>
                <tr><td>Bank:</td><td>HDFC</td></tr>
                <tr><td>Branch:</td><td>KARUR</td></tr>
                <tr><td>Account No:</td><td>99997373738363</td></tr>
                <tr><td>IFSC:</td><td>HDFC0006914</td></tr>
            </table>
        </div>
        <div>
            <h4 class="subheading">Delivery Location:</h4>
            <p style="margin-top:2px;">
                @php $location = $quotation->lead->location; @endphp
                @if($location)
                    {{ $location->full_location ?? '' }} - {{ $location->pincode ?? '' }}
                @elseif($version->delivery_location)
                    {{ $version->delivery_location->name ?? '' }}
                @else
                    N/A
                @endif
            </p>
        </div>
    </div>

    <div class="footer">
        3/106, Velampondi Village, Nenjikalipalayam, Tiruppur, Tamil Nadu - 639201
    </div>
</body>
</html>
