<?php $this->load->view('partials/reports/header'); ?>

<div class="report">

  <table class="report-borderless" style="line-height:8px;table-layout:auto">
    <tr>
      <td width="12%"><strong>Supplier</strong></td>
      <td><?= htmlspecialchars($supplierName); ?></td>
    </tr>
    <tr>
      <td><strong>Date From</strong></td>
      <td><?= date('m/d/Y', strtotime($filters['date_from'])); ?></td>
    </tr>
    <tr>
      <td><strong>Date To</strong></td>
      <td><?= date('m/d/Y', strtotime($filters['date_to'])); ?></td>
    </tr>
  </table>

  <br>

  <table class="report-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Product</th>
        <th>UOM</th>
        <th class="text-right">Invoices</th>
        <th class="text-right">Qty Sold</th>
        <th class="text-right">Gross Sales</th>
        <th class="text-right">Discount</th>
        <th class="text-right">Net Sales</th>
      </tr>
    </thead>

    <tbody>
      <?php
        $grossTotal = 0;
        $discountTotal = 0;
        $netTotal = 0;
      ?>

      <?php foreach ($salesPerSupplierProducts as $index => $row): ?>

        <?php
          $grossSales = (float)$row->gross_sales;
          $discountAmount = (float)$row->discount_amount;
          $netSales = (float)$row->net_sales;
          $grossTotal += $grossSales;
          $discountTotal += $discountAmount;
          $netTotal += $netSales;
        ?>

        <tr>
          <td class="text-center"><?= $index + 1 ?>.</td>
          <td><?= htmlspecialchars($row->product_description); ?></td>
          <td class="text-center"><?= htmlspecialchars($row->uom); ?></td>
          <td class="text-right"><?= number_format((int)$row->invoice_count); ?></td>
          <td class="text-right"><?= number_format((float)$row->qty_sold, 2); ?></td>
          <td class="text-right"><?= number_format($grossSales, 2); ?></td>
          <td class="text-right"><?= number_format($discountAmount, 2); ?></td>
          <td class="text-right"><?= number_format($netSales, 2); ?></td>
        </tr>

      <?php endforeach; ?>

    </tbody>

    <tfoot>
      <tr>
        <th colspan="5" class="text-right">Grand Total</th>
        <th class="text-right"><?= number_format($grossTotal, 2); ?></th>
        <th class="text-right"><?= number_format($discountTotal, 2); ?></th>
        <th class="text-right"><?= number_format($netTotal, 2); ?></th>
      </tr>
    </tfoot>
  </table>

</div>

<div style="text-align:right;font-size:10px;margin-top:20px;">
  Printed By: <strong><?= strtoupper(htmlspecialchars($this->session->userdata('username'))); ?></strong>
  <?= date('m/d/Y h:i A'); ?>
</div>

<?php $this->load->view('partials/reports/scripts'); ?>