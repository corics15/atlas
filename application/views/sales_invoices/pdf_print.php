<?php foreach ($documents as $documentIndex => $document): ?>

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

    /*** report header */
    $this->load->view(
      'partials/reports/pdf_header',
      [
        'title' => 'Sales Invoice'
      ]
    );
  ?>

  <?php /*** document header */ ?>
  <table style="line-height:12px">
    <tr>
      <td width="12%"><strong>SI No.</strong></td>
      <td width="48%"><strong><?= htmlspecialchars($header->si_no) ?></strong></td>
      <td width="15%"><strong>Invoice Date</strong></td>
      <td width="25%"><?= date('m/d/Y', strtotime($header->invoice_date)) ?></td>
    </tr>
    <tr>
      <td><strong>SO No.</strong></td>
      <td><?= htmlspecialchars($header->so_no) ?></td>
      <td><strong>Status</strong></td>
      <td><?= htmlspecialchars($header->status) ?></td>
    </tr>
    <tr>
      <td><strong>Customer</strong></td>
      <td><strong><?= htmlspecialchars($header->customer_name) ?></strong></td>
      <td><strong>Terms</strong></td>
      <td><?= htmlspecialchars($header->terms_name ?? '') ?></td>
    </tr>
    <tr>
      <td><strong>Salesman</strong></td>
      <td colspan="3"><?= htmlspecialchars($header->salesman_name) ?></td>
    </tr>
    <tr>
      <td><strong>Remarks</strong></td>
      <td colspan="3"><?= nl2br(htmlspecialchars($header->remarks ?? '')) ?></td>
    </tr>
  </table>

  <br><br>

  <?php /*** details */ ?>
  <table style="line-height:15px;font-size:8px;">
    <thead style="border:1px solid #000">
      <tr>
        <th width="4%" class="text-center border-left-end">#</th>
        <th width="13%" class="text-center border-top-bottom">Barcode</th>
        <th width="26%" class="border-top-bottom">Description</th>
        <th width="5%" class="border-top-bottom">Pkg</th>
        <th width="7%" class="text-center border-top-bottom">Qty</th>
        <th width="8%" class="text-center border-top-bottom">UOM</th>
        <th width="10%" class="text-right border-top-bottom">Unit Price</th>
        <th width="7%" class="text-center border-top-bottom">Disc.</th>
        <th width="10%" class="text-right border-top-bottom">Disc. Amt</th>
        <th width="10%" class="text-right border-right-end">Net Amt</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($details as $index => $detail): ?>
        <?php
          $rowGross = (float)$detail->qty * (float)$detail->unit_price;
          $rowDiscount = (float)$detail->discount_amount;
          $rowNet = $rowGross - $rowDiscount;
          $discountType = strtoupper(
            trim($detail->discount_type ?? '')
          );
        ?>
        <tr>
          <td width="4%" class="text-center border-left-bottom-right"><?= $index + 1 ?>.</td>
          <td width="13%" class="text-center border-bottom-right"><?= htmlspecialchars($detail->barcode) ?></td>
          <td width="26%" class="border-bottom-right"><?= htmlspecialchars($detail->description) ?></td>
          <td width="5%" class="border-bottom-right"><?= htmlspecialchars($detail->pkg) ?></td>
          <td width="7%" class="text-center border-bottom-right"><?= number_format((float)$detail->qty, 0) ?></td>
          <td width="8%" class="text-center border-bottom-right"><?= htmlspecialchars($detail->uom) ?></td>
          <td width="10%" class="text-right border-bottom-right"><?= number_format((float)$detail->unit_price, 2) ?></td>
          <td width="7%" class="text-center border-bottom-right">
            <?php if ($discountType === 'PERCENT'): ?>
              <?= number_format((float)$detail->discount_percent, 2) ?>%
            <?php elseif ($discountType === 'AMOUNT'): ?>
              Amount
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td width="10%" class="text-right border-bottom-right"><?= number_format($rowDiscount, 2) ?></td>
          <td width="10%" class="text-right border-bottom-right"><?= number_format($rowNet, 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br><br>

  <?php /*** totals */ ?>
  <table style="line-height:12px;font-size:8px;">
    <tr>
      <td width="80%" class="text-right">Gross Amount</td>
      <td width="20%" class="text-right"><?= number_format($grossAmount, 2) ?></td>
    </tr>
    <tr>
      <td class="text-right">Less Discount</td>
      <td class="text-right"><?= number_format($discountAmount, 2) ?></td>
    </tr>
    <tr>
      <td class="text-right">Subtotal</td>
      <td class="text-right"><?= number_format((float)$header->subtotal, 2) ?></td>
    </tr>
    <tr>
      <td class="text-right">VAT <?= number_format((float)$header->vat_rate, 2) ?>%</td>
      <td class="text-right"><?= number_format((float)$header->vat_amount, 2) ?></td>
    </tr>
    <tr>
      <td class="text-right"><strong>TOTAL</strong></td>
      <td class="text-right"><strong><?= number_format((float)$header->total_amount, 2) ?></strong></td>
    </tr>
  </table>

  <br><br>

  <?php /*** signatories */ ?>
  <table>
    <tr>
      <td class="font-7 text-center border-bottom">
        <?php $signatureFile = !empty($preparedBy->signature) ? FCPATH . $preparedBy->signature : ''; ?>

        <?php if ($signatureFile && is_file($signatureFile)): ?>
          <img src="<?= $signatureFile ?>" style="height:28px;max-width:120px;object-fit:contain;"><br>
        <?php endif; ?>

        <strong>
          <?= htmlspecialchars($preparedBy->first_name . ' ' . $preparedBy->last_name) ?>
        </strong>
      </td>
      <td class=""></td>
      <td class=""></td>
      <td class="border-bottom"></td>
    </tr>
    <tr>
      <td class="font-7 text-center">Prepared By</td>
      <td class="font-7 text-center"></td>
      <td class="font-7 text-center"></td>
      <td class="font-7 text-center">Approved By</td>
    </tr>
  </table>

  <?php /*** printed by */ ?>
  <table cellspacing="5">
    <tr>
      <td class="font-7 text-right" style="font-style:italic">
        Printed By:
        <strong><?= htmlspecialchars(strtoupper($this->session->userdata('username'))) ?></strong>
        <?= date('m/d/Y h:i A') ?>
      </td>
    </tr>
  </table>

  <?php if ($documentIndex < count($documents) - 1): ?>
    <br pagebreak="true" />
  <?php endif; ?>

<?php endforeach; ?>