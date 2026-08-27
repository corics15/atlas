<!DOCTYPE html>
<html>
  <head>
    <title>Purchase Order</title>
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

      <?php $header = $document->header; ?>

      <?php
        /*** report header */
        $this->load->view(
          'partials/reports/header',
          [
            'title'  => 'Purchase Order',
            'period' => null
          ]
        );
      ?>

      <table class="report-borderless">
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

      <?php /*** remarks */ ?>
      <br>
      <strong>Remarks</strong>
      <div style="padding:8px;min-height:30px;margin-bottom:5px">
      <?= nl2br(htmlspecialchars($header->remarks)) ?>
      </div>

      <?php /*** details table */ ?>
      <table class="report-table">
        <thead>
          <tr>
            <th class="text-center" width="5%">#</th>
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
            $index = 1;
            foreach($document->details as $detail):
              $amount = ($detail->qty * $detail->price) - $detail->discount;
              $total += $amount;
            ?>
          <tr>
            <td class="text-center"><?= $index ?>.</td>
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
          <?php $index++; endforeach; ?>
          <tr>
            <td colspan="6" class="text-right">
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

      <?php /*** signatories */ ?>
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