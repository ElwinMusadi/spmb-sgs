<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>{{ $invoiceNumber }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #1f2937;
      font-size: 12px;
      line-height: 1.5;
    }

    .wrapper {
      padding: 40px;
    }

    .header {
      width: 100%;
      border-bottom: 2px solid #19376d;
      padding-bottom: 20px;
      margin-bottom: 24px;
    }

    .header td {
      vertical-align: top;
    }

    .brand {
      font-size: 20px;
      font-weight: bold;
      color: #19376d;
    }

    .brand-logo {
      width: 46px;
      height: 46px;
      vertical-align: middle;
    }

    .brand-sub {
      font-size: 11px;
      color: #6b7280;
      margin-top: 2px;
    }

    .invoice-title {
      text-align: right;
      font-size: 26px;
      font-weight: bold;
      color: #19376d;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .meta {
      width: 100%;
      margin-bottom: 28px;
    }

    .meta td {
      vertical-align: top;
      width: 50%;
    }

    .meta-label {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #6b7280;
      margin-bottom: 4px;
    }

    .meta-value {
      font-size: 13px;
      font-weight: bold;
      color: #111827;
    }

    .meta-line {
      font-size: 11px;
      color: #374151;
      margin-top: 2px;
    }

    .text-right {
      text-align: right;
    }

    .status {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-paid {
      background: #dcfce7;
      color: #166534;
    }

    .status-awaiting {
      background: #fee2e2;
      color: #991b1b;
    }

    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }

    table.items thead th {
      background: #19376d;
      color: #ffffff;
      text-align: left;
      padding: 10px 12px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    table.items thead th.amount {
      text-align: right;
    }

    table.items tbody td {
      padding: 12px;
      border-bottom: 1px solid #e5e7eb;
    }

    .item-title {
      font-weight: bold;
      color: #111827;
    }

    .item-detail {
      font-size: 10px;
      color: #6b7280;
      margin-top: 2px;
    }

    .totals {
      width: 45%;
      margin-left: auto;
      border-collapse: collapse;
    }

    .totals td {
      padding: 6px 12px;
    }

    .totals td.label {
      color: #6b7280;
      text-align: right;
    }

    .totals td.value {
      text-align: right;
      font-weight: bold;
      color: #111827;
    }

    .totals tr.grand td {
      border-top: 2px solid #19376d;
      padding-top: 10px;
      font-size: 15px;
      color: #19376d;
    }

    .footer {
      margin-top: 48px;
      padding-top: 16px;
      border-top: 1px solid #e5e7eb;
      font-size: 10px;
      color: #9ca3af;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <table class="header">
      <tr>
        <td>
          <table>
            <tr>
              @if ($logoData)
                <td style="padding-right: 12px;">
                  <img src="{{ $logoData }}" alt="Logo Stella Gracia School" class="brand-logo">
                </td>
              @endif
              <td>
                <div class="brand">Stella Gracia School</div>
                <div class="brand-sub">Stella Gracia School Kupang</div>
                <div class="brand-sub">Sistem Penerimaan Murid Baru</div>
              </td>
            </tr>
          </table>
        </td>
        <td>
          <div class="invoice-title">Invoice</div>
        </td>
      </tr>
    </table>

    <table class="meta">
      <tr>
        <td>
          <div class="meta-label">Billed To</div>
          <div class="meta-value">{{ $application->student_full_name ?? $application->user->name }}</div>
          @if ($application->grade_level)
            <div class="meta-line">{{ $application->grade_level }}
              @if ($application->schoolLevel)
                &bull; {{ $application->schoolLevel->name }}
              @endif
            </div>
          @endif
          @if ($application->academicYear)
            <div class="meta-line">Academic Year {{ $application->academicYear->name }}</div>
          @endif
        </td>
        <td class="text-right">
          <div class="meta-label">Invoice No.</div>
          <div class="meta-value">{{ $invoiceNumber }}</div>
          <div class="meta-line">Issued: {{ $issuedAt->format('M d, Y') }}</div>
          <div class="meta-line">Due: {{ $dueAt->format('M d, Y') }}</div>
          <div style="margin-top: 8px;">
            @if ($application->payment_status === 'paid')
              <span class="status status-paid">Lunas / Paid</span>
            @else
              <span class="status status-awaiting">Awaiting Payment</span>
            @endif
          </div>
        </td>
      </tr>
    </table>

    <table class="items">
      <thead>
        <tr>
          <th>Description</th>
          <th class="amount">Amount (IDR)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lineItems as $item)
          <tr>
            <td>
              <div class="item-title">{{ $item['description'] }}</div>
              <div class="item-detail">{{ $item['detail'] }}</div>
            </td>
            <td class="text-right">{{ number_format($item['amount'], 0, ',', ',') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table class="totals">
      <tr>
        <td class="label">Subtotal</td>
        <td class="value">{{ number_format($subtotal, 0, ',', ',') }}</td>
      </tr>
      <tr>
        <td class="label">Admin Fee (Virtual Account)</td>
        <td class="value">{{ number_format($adminFee, 0, ',', ',') }}</td>
      </tr>
      <tr class="grand">
        <td class="label">Total Due</td>
        <td class="value">{{ number_format($total, 0, ',', ',') }}</td>
      </tr>
    </table>

    <div class="footer">
      This is a computer-generated invoice and does not require a signature.<br>
      Please retain your payment receipt until the status updates to "Lunas".
    </div>
  </div>
</body>

</html>
