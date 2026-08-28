<style>
  @media print {
    @page {
      size: A4 portrait;
      margin: 5mm;
    }
  }

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    margin: 0;
    padding: 0;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  thead {
    display: table-header-group;
  }

  tfoot {
    display: table-footer-group;
  }

  th {
    border: 0.5px solid #000;
    background: #efefef;
    padding: 6px;
  }

  tr {
    page-break-inside: avoid;
  }

  td {
    border: 0.5px solid #000;
    padding: 5px;
  }

  hr {
    border: none;
    border-top: 1px solid #999;
    margin: 10px 0 15px;
  }

  .w-100 {
    width: 100% !important;
  }

  .font-weight-bold {
    font-weight: bold !important;
  }

  .report-table tbody tr td {
    border: none;
  }

  .report-table tfoot tr th {
    border: none;
    border-top: 0.5px solid #000;
  }

  .text-center {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }

  .text-left {
    text-align: left;
  }

  .report-title {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    margin-top: 8px;
  }

  .company-name {
    font-size: 18px;
    font-weight: bold;
  }

  .signature-table {
    width: 100%;
    margin-top: 35px;
    page-break-inside: avoid;
  }

  .signature-table td {
    text-align: center;
    padding-top: 25px;
  }

  .footer-table td {
    border: none;
  }

  .signature {
    margin-top: 60px;
    text-align: center;
  }

  .signature-line {
    border-top: 1px solid #000;
    width: 220px;
    margin: auto;
    padding-top: 4px;
  }

  .report-subtitle {
    text-align: center;
    margin-bottom: 10px;
  }

  .report-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
  }

  .report-table thead {
    display: table-header-group;
  }

  .report-table tr {
    page-break-inside:avoid;
  }

  .report-table tbody tr:nth-child(even) {
    background: #fafafa;
  }

  .report-table th {
    border: 1px solid #000;
    padding: 6px;
    background: #efefef;
    font-size: 10px;
  }

  .report-table td {
    border:1px solid #000;
    padding:5px;
  }

  .report-table tfoot {
    display: table-footer-group;
    font-weight: bold;
  }

  .report-bordered th,
  .report-bordered td {
    border:0.5px solid #bdbdbd;
  }

  .report-borderless th,
  .report-borderless td {
    border:none;
  }

  .report-striped tbody tr:nth-child(even) {
    background:#fafafa;
  }

  .report-no-stripes tbody tr{
    background:transparent !important;
  }

  .report-compact th,
  .report-compact td {
    padding:3px;
  }

  .report-spacious th,
  .report-spacious td {
    padding:8px;
  }

  .no-border {
    border: none !important;
  }

  .page-break {
    display: block;
    page-break-after: always;
    break-after: page;
  }

  .summary-table td {
    padding: 0 4px;
    line-height: 1.2;
  }

  /*** SOA page layout */
  .soa-report {
    min-height: 245mm;
    display: flex;
    flex-direction: column;
  }

  .soa-content {
    flex: 1;
  }

  .soa-footer {
    margin-top: auto !important;
  }

  .soa-bill-to {
    width: 50%;
    display: inline-block;
    background-color: #bdbdbd !important;
    padding: 8px 0 8px 5px;

    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /*** transaction header */
  .soa-account-summary,
  .soa-footer,
  .soa-details thead th {
    background-color: #bdbdbd !important;

    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /*** alternating transaction rows */
  .soa-details tbody .soa-detail-row:nth-of-type(even) td {
    background-color: #f2f2f2 !important;

    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /*** current balance */
  .soa-current-balance {
    border-top: 1px solid #333;
    padding-top: 8px;
  }

  .soa-details tbody tr {
    height: 24px;
  }

  .soa-details tbody .soa-detail-row:nth-of-type(even) td {
    background-color: #f2f2f2 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  @media print {
    .soa-report {
      min-height: 245mm;
    }
  }
</style>