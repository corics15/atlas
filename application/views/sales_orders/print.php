<?php foreach ($documents as $i => $document): ?>
  <?php $this->load->view('partials/reports/header'); ?>
  <?php
    $header  = $document->header;
    $details = $document->details;

    $grossAmount = 0;
    $discountAmount = 0;

    foreach ($details as $detail) {
      $rowGross = (float)$detail->qty * (float)$detail->unit_price;
      $grossAmount += $rowGross;
      $discountAmount += (float)$detail->discount_amount;
    }
  ?>

  <div class="report">

    <?php /*** document header */ ?>
    <table class="report-borderless" style="table-layout:auto;line-height:8px">
      <tr>
        <td><strong>SO No.</strong></td>
        <td><?= htmlspecialchars($header->so_no) ?></td>
        <td><strong>Order Date</strong></td>
        <td><?= date('m/d/Y', strtotime($header->order_date)) ?></td>
      </tr>
      <tr>
        <td><strong>Customer</strong></td>
        <td><?= htmlspecialchars($header->customer_name) ?></td>
        <td><strong>Status</strong></td>
        <td><?= htmlspecialchars($header->status) ?></td>
      </tr>
      <tr>
        <td><strong>Salesman</strong></td>
        <td><?= htmlspecialchars($header->salesman_name) ?></td>
        <td><strong>Terms</strong></td>
        <td><?= htmlspecialchars($header->terms_name ?? '') ?></td>
      </tr>
      <tr>
        <td><strong>Remarks</strong></td>
        <td colspan="3">
          <?= nl2br(htmlspecialchars($header->remarks ?? '')) ?>
        </td>
      </tr>
    </table>
    <?php /*** end document header */ ?>

    <?php /*** details */ ?>
    <br>
    <table class="report-table">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Barcode</th>
          <th>Description</th>
          <th class="text-right">Qty</th>
          <th class="text-center">UOM</th>
          <th class="text-right">Unit Price</th>
          <th class="text-center">Discount</th>
          <th class="text-right">Disc. Amt</th>
          <th class="text-right">Net Amt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($details as $index => $detail): ?>
          <?php
            $rowGross = (float)$detail->qty * (float)$detail->unit_price;
            $rowDiscount = (float)$detail->discount_amount;
            $rowNet = $rowGross - $rowDiscount;
            $discountType = strtoupper(trim($detail->discount_type ?? ''));
          ?>
          <tr>
            <td class="text-center"><?= $index + 1 ?>.</td>
            <td class="text-center"><?= htmlspecialchars($detail->barcode) ?></td>
            <td><?= htmlspecialchars($detail->description) ?></td>
            <td class="text-right"><?= number_format((float)$detail->qty, 2) ?></td>
            <td class="text-center"><?= htmlspecialchars($detail->uom) ?></td>
            <td class="text-right"><?= number_format((float)$detail->unit_price, 2) ?></td>
            <td class="text-center">
              <?php if ($discountType === 'PERCENT'): ?>
                <?= number_format((float)$detail->discount_percent, 2) ?>%
              <?php elseif ($discountType === 'AMOUNT'): ?>
              Amount
              <?php else: ?>
              -
              <?php endif; ?>
            </td>
            <td class="text-right"><?= number_format($rowDiscount, 2) ?></td>
            <td class="text-right"><?= number_format($rowNet, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php /*** end details */ ?>

    <?php /*** totals */ ?>
    <br>
    <table class="report-borderless" style="line-height:8px">
      <tr>
        <td width="80%" class="text-right">Gross Amount</td>
        <td class="text-right" width="20%">
          <?= number_format($grossAmount, 2) ?>
        </td>
      </tr>
      <tr>
        <td class="text-right">Less Discount</td>
        <td class="text-right">
          <?= number_format($discountAmount, 2) ?>
        </td>
      </tr>
      <tr>
        <td class="text-right">Subtotal</td>
        <td class="text-right">
          <?= number_format((float)$header->subtotal, 2) ?>
        </td>
      </tr>
      <tr>
        <td class="text-right">VAT <?= number_format((float)$header->vat_rate, 2) ?>%</td>
        <td class="text-right">
          <?= number_format((float)$header->vat_amount, 2) ?>
        </td>
      </tr>
      <tr>
        <td class="text-right"><strong>TOTAL</strong></td>
        <td class="text-right">
          <strong><?= number_format((float)$header->total_amount, 2) ?></strong>
        </td>
      </tr>
    </table>
    <?php /*** end totals */ ?>

    <br><br><br>

    <?php /*** signatures */ ?>
    <table class="report-borderless" style="line-height:5px">
      <tr>
        <td class="text-center font-weight-bold"><?= $this->session->userdata('first_name').' '.$this->session->userdata('last_name') ?></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>_______________________________</td>
        <td>_______________________________</td>
        <td>_______________________________</td>
        <td>_______________________________</td>
      </tr>
      <tr>
        <td class="text-center">Prepared By</td>
        <td class="text-center">Approved By</td>
        <td class="text-center">Checked By</td>
        <td class="text-center">Received By</td>
      </tr>
    </table>
    <?php /*** end signatures */ ?>

  </div>

  <div style="text-align:right;font-size:10px;margin-top:20px;">
    Printed By: <strong><?= strtoupper(htmlspecialchars($this->session->userdata('username'))); ?></strong>
    <?= date('m/d/Y h:i A'); ?>
  </div>

  <?php if ($i < count($documents) - 1): ?>
    <div class="page-break"></div>
  <?php endif; ?>

<?php endforeach; ?>

<?php $this->load->view('partials/reports/scripts'); ?>