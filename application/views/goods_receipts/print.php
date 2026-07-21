<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Goods Receipt Note</title>
    <link rel="stylesheet" href="<?= atlas_asset('assets/css/print.css'); ?>">
    <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">

  </head>
  <body>

    <?php /*** header */ ?>
    <?php
      $company = atlas_company();

      $contactNo = null;
      $mobile    = trim($company->mobile_no ?? '');
      $telephone = trim($company->telephone_no ?? '');

      if ($mobile !== '' && $telephone !== '') {
          $contactNo = htmlspecialchars($mobile . ' / ' . $telephone);
      } elseif ($mobile !== '') {
          $contactNo = htmlspecialchars($mobile);
      } elseif ($telephone !== '') {
          $contactNo = htmlspecialchars($telephone);
      }
    ?>

    <?php foreach ($documents as $index => $document): ?>

      <?php
        $goodsReceipt = $document->header;
        $details      = $document->details;
      ?>

      <h2 class="text-center" style="margin-bottom:1px">
        <?= htmlspecialchars($company->company_name) ?>
      </h2>

      <div class="text-center" style="font-size:12px">
        <?= htmlspecialchars($company->address) ?><br>
        <?= $contactNo ?><br>
        <?= htmlspecialchars($company->tin_no) ?>
      </div>

      <h3 class="text-center">
        GOODS RECEIPT NOTE
      </h3>
      <table style="margin-bottom:15px;border:none;">
        <tr>
          <td style="border:none;width:70%;">
            <strong>Supplier :</strong>
            <?= htmlspecialchars($goodsReceipt->supplier_name) ?>
          </td>
          <td style="border:none;">
            <strong>GRN No :</strong>
            <?= htmlspecialchars($goodsReceipt->grn_no) ?>
          </td>
        </tr>
        <tr>
          <td style="border:none;">
            <strong>Purchase Order :</strong>
            <?= htmlspecialchars($goodsReceipt->po_no) ?>
          </td>
          <td style="border:none;">
            <strong>Date :</strong>
            <?= date('m/d/Y', strtotime($goodsReceipt->grn_date)) ?>
          </td>
        </tr>
      </table>

      <?php /*** remarks */ ?>
      <br>
      <strong>Remarks</strong>
      <div style="border:0.8px solid #000;padding:8px;min-height:60px;">
        <?= nl2br(htmlspecialchars($goodsReceipt->remarks)) ?>
      </div>
      <br>

      <?php /*** details table */ ?>
      <h3>
        Items Received
      </h3>
      <table>
        <thead>
          <tr>
            <th width="50">#</th>
            <th width="150" class="text-center">Barcode</th>
            <th width="100%">Description</th>
            <th width="80" class="text-center">UOM</th>
            <th width="110" class="text-right">Ordered</th>
            <th width="110" class="text-right">Received</th>
            <th width="120" class="text-right">Unit Cost</th>
            <th width="130" class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php $grandTotal = 0; ?>
          <?php if (empty($details)): ?>
          <tr>
            <td colspan="8" class="text-center">
              No items found.
            </td>
          </tr>

          <?php else: ?>

            <?php foreach ($details as $index => $item): ?>
              <?php
                $amount = $item->qty_received * $item->unit_cost;
                $grandTotal += $amount;
              ?>
              <tr>
                <td class="text-center">
                  <?= $index + 1 ?>
                </td>
                <td class="text-center">
                  <?= htmlspecialchars($item->barcode) ?>
                </td>
                <td>
                  <?= htmlspecialchars($item->description) ?>
                </td>
                <td class="text-center">
                  <?= htmlspecialchars($item->uom) ?>
                </td>
                <td class="text-right">
                  <?= number_format($item->qty_ordered) ?>
                </td>
                <td class="text-right">
                  <?= number_format($item->qty_received) ?>
                </td>
                <td class="text-right">
                  <?= number_format($item->unit_cost, 2) ?>
                </td>
                <td class="text-right">
                  <?= number_format($amount, 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php /*** grand total */ ?>
            <tr>
              <td colspan="7" class="text-right">
                <strong>Grand Total</strong>
              </td>
              <td class="text-right">
                <strong><?= number_format($grandTotal, 2) ?></strong>
              </td>
            </tr>

          <?php endif;?>
        </tbody>
      </table>

      <?php /*** signatories */ ?>
      <br><br>
      <table style="border:none;">
        <tr>
          <td style="border:none;text-align:center;width:33%;height:70px;vertical-align:bottom;">
            _________________________<br>
            Prepared By
          </td>
          <td style="border:none;text-align:center;width:33%;vertical-align:bottom;">
            _________________________<br>
            Checked By
          </td>
          <td style="border:none;text-align:center;width:34%;vertical-align:bottom;">
            _________________________<br>
            Received By
          </td>
        </tr>
      </table>

      <div style="text-align:right;font-size:10px;margin-top:20px;">
        Printed By:
        <?= htmlspecialchars($this->session->userdata('username')) ?>
        <?= date('m/d/Y h:i A') ?>
      </div>

      <?php if ($index < count($documents) - 1): ?>
        <div class="page-break"></div>
      <?php endif; ?>

    <?php endforeach; ?>

    <script>
      window.print();
    </script>

  </body>
</html>