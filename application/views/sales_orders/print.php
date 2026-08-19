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
    <table class="table report-borderless table-sm mb-3">

      <tr>
        <td width="160"><strong>SO No.</strong></td>
        <td><?= htmlspecialchars($header->so_no) ?></td>
        <td width="140"><strong>Order Date</strong></td>
        <td width="180"><?= date('m/d/Y', strtotime($header->order_date)) ?></td>
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
    <table class="table table-bordered table-sm" style="width: 100%; table-layout: auto;">
      <thead>
        <tr>
          <th class="text-center" style="white-space: nowrap;">#</th>
          <th class="text-center" style="white-space: nowrap;">Barcode</th>
          <th style="white-space: nowrap;">Description</th>
          <th class="text-right" style="white-space: nowrap;">Qty</th>
          <th class="text-center" style="white-space: nowrap;">UOM</th>
          <th class="text-right" style="white-space: nowrap;">Unit Price</th>
          <th class="text-center" style="white-space: nowrap;">Discount</th>
          <th class="text-right" style="white-space: nowrap;">Disc. Amt</th>
          <th class="text-right" style="white-space: nowrap;">Net Amt</th>
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
            <td class="text-center" style="border:none"><?= $index + 1 ?>.</td>
            <td class="text-center" style="border:none"><?= htmlspecialchars($detail->barcode) ?></td>
            <td style="border:none"><?= htmlspecialchars($detail->description) ?></td>
            <td class="text-right" style="border:none"><?= number_format((float)$detail->qty, 2) ?></td>
            <td class="text-center" style="border:none"><?= htmlspecialchars($detail->uom) ?></td>
            <td class="text-right" style="border:none"><?= number_format((float)$detail->unit_price, 2) ?></td>
            <td class="text-center" style="border:none">
              <?php if ($discountType === 'PERCENT'): ?>
                <?= number_format((float)$detail->discount_percent, 2) ?>%
              <?php elseif ($discountType === 'AMOUNT'): ?>
              Amount
              <?php else: ?>
              -
              <?php endif; ?>
            </td>
            <td class="text-right" style="border:none"><?= number_format($rowDiscount, 2) ?></td>
            <td class="text-right" style="border:none"><?= number_format($rowNet, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php /*** end details */ ?>

    <?php /*** totals */ ?>
    <br>
    <hr>
    <table class="table report-borderless table-sm" style="width: 100%;">
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

    <br><br>

    <?php /*** signatures */ ?>
    <table class="table report-borderless">
      <tr>
        <td width="33%" class="text-center">

          _______________________________
          <br>
          Prepared By
        </td>

        <td width="34%"></td>
        <td width="33%" class="text-center">

          _______________________________
          <br>

          Approved By
        </td>
      </tr>
    </table>
    <?php /*** end signatures */ ?>

  </div>

  <?php if ($i < count($documents) - 1): ?>
    <div class="page-break"></div>
  <?php endif; ?>

<?php endforeach; ?>

<?php $this->load->view('partials/reports/scripts'); ?>