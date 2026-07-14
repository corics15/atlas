<!DOCTYPE html>
<html>
  <head>
    <title>Purchase Order</title>
    <link rel="stylesheet" href="<?= atlas_asset('assets/css/print.css'); ?>">
    <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">
  </head>
  <body>

    <?php /*** header */ ?>
    <?php foreach ($documents as $index => $document): ?>

      <?php $header = $document->header; ?>
      <h2 class="text-center" style="margin-bottom:1px">
        <?= htmlspecialchars($app['company_name']) ?>
      </h2>
      <div class="text-center" style="font-size:12px !important">
        <?= htmlspecialchars($app['company_address']) ?><br>
        <?= htmlspecialchars($app['company_contact']) ?>
      </div>

      <h3 class="text-center">
        PURCHASE ORDER
      </h3>

      <table style="margin-bottom:15px;border:none;">
        <tr>
          <td style="border:none;width:70%;">
            <strong>Supplier :</strong>
            <?= htmlspecialchars($header->supplier_name) ?>
          </td>
          <td style="border:none;">
            <strong>PO No :</strong>
            <?= htmlspecialchars($header->po_no) ?>
          </td>
        </tr>
        <tr>
          <td style="border:none;">
            <strong>Address :</strong>
            <?= htmlspecialchars($header->address) ?>
          </td>
          <td style="border:none;">
            <strong>Date :</strong>
            <?= date('m/d/Y', strtotime($header->po_date)) ?>
          </td>
        </tr>
        <tr>
          <td style="border:none;">
            <strong>Contact :</strong>
            <?= htmlspecialchars($header->contact_person) ?>
          </td>
          <td style="border:none;">
            <strong>Terms :</strong>
            <?= htmlspecialchars($header->terms_name) ?>
          </td>
        </tr>
      </table>

      <?php /*** details table */ ?>
      <table>
        <thead>
          <tr>
            <th>Description</th>
            <th width="8%">UOM</th>
            <th width="8%" class="text-right">Qty</th>
            <th width="12%" class="text-right">Price</th>
            <th width="12%" class="text-right">Discount</th>
            <th width="14%" class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $total = 0;
            foreach($document->details as $detail):
              $amount = ($detail->qty * $detail->price) - $detail->discount;
              $total += $amount;
            ?>
          <tr>
            <td><?= htmlspecialchars($detail->description) ?></td>
            <td class="text-center">
              <?= htmlspecialchars($detail->uom) ?>
            </td>
            <td class="text-right">
              <?= number_format($detail->qty,2) ?>
            </td>
            <td class="text-right">
              <?= number_format($detail->price,2) ?>
            </td>
            <td class="text-right">
              <?= number_format($detail->discount,2) ?>
            </td>
            <td class="text-right">
              <?= number_format($amount,2) ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="5" class="text-right">
              <strong>Grand Total</strong>
            </td>
            <td class="text-right">
              <strong>
              <?= number_format($total,2) ?>
              </strong>
            </td>
          </tr>
        </tbody>
      </table>

      <?php /*** remarks */ ?>
      <br>
      <strong>Remarks</strong>
      <div style="border:1px solid #000;padding:8px;min-height:60px;">
      <?= nl2br(htmlspecialchars($header->remarks)) ?>
      </div>

      <?php /*** signatories */ ?>
      <br><br>
      <table style="border:none;">
        <tr>
          <td style="border:none;text-align:center;width:25%;height:70px;vertical-align:bottom;">
            _________________________<br>
            Prepared By
          </td>
          <td style="border:none;text-align:center;width:25%;vertical-align:bottom;">
            _________________________<br>
            Checked By
          </td>
          <td style="border:none;text-align:center;width:25%;vertical-align:bottom;">
            _________________________<br>
            Approved By
          </td>
          <td style="border:none;text-align:center;width:25%;vertical-align:bottom;">
            _________________________<br>
            Received By
          </td>
        </tr>
      </table>

      <div style="text-align:right;font-size:10px;margin-top:20px;">
        Printed By:
        <?= htmlspecialchars($this->session->userdata('username')).' '.date('m/d/Y h:i A'); ?>
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