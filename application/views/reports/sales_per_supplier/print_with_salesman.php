<?php  $this->load->view('partials/reports/header'); ?>

<div class="report-info">
  <table class="report-borderless">
    <tr>
      <td width="12%"><strong>Date From</strong></td>
      <td><?= date('m/d/Y', strtotime(htmlspecialchars($filters['date_from']))) ?></td>
    </tr>
    <tr>
      <td><strong>Date To</strong></td>
      <td><?= date('m/d/Y', strtotime(htmlspecialchars($filters['date_to']))) ?></td>
    </tr>
  </table>
</div>

<?php
  $grossTotal = 0;
  $discountTotal = 0;
  $netTotal = 0;
?>

<table class="report-table">
  <thead>
    <tr>
      <th>Supplier</th>
      <th>Salesman</th>
      <th class="text-right">Invoices</th>
      <th class="text-right">Gross Sales</th>
      <th class="text-right">Discount</th>
      <th class="text-right">Net Sales</th>
    </tr>
  </thead>
  <tbody>

    <?php foreach ($salesPerSupplierSalesman as $row): ?>
      <?php
        $grossSales = (float)$row->gross_sales;
        $discountAmount = (float)$row->discount_amount;
        $netSales = (float)$row->net_sales;
        $grossTotal += $grossSales;
        $discountTotal += $discountAmount;
        $netTotal += $netSales;
      ?>
      <tr>
        <td><?= htmlspecialchars($row->supplier_name) ?></td>
        <td><?= htmlspecialchars($row->salesman_name) ?></td>
        <td class="text-right"><?= number_format((int)$row->invoice_count) ?></td>
        <td class="text-right"><?= number_format($grossSales, 2) ?></td>
        <td class="text-right"><?= number_format($discountAmount, 2) ?></td>
        <td class="text-right"><?= number_format($netSales, 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>

  <tfoot>
    <tr>
      <th colspan="2" class="text-right">        Grand Total      </th>
      <th class="text-right">-</th>
      <th class="text-right"><?= number_format($grossTotal, 2) ?></th>
      <th class="text-right"><?= number_format($discountTotal, 2) ?></th>
      <th class="text-right"><?= number_format($netTotal, 2) ?></th>
    </tr>
  </tfoot>
</table>

<div style="text-align:right;font-size:10px;margin-top:20px;">
  Printed By: <strong><?= strtoupper(htmlspecialchars($this->session->userdata('username'))); ?></strong>
  <?= date('m/d/Y h:i A'); ?>
</div>

<?php $this->load->view('partials/reports/scripts'); ?>