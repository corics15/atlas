<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($inventoryAdjustment->adjustment_no) ?></title>
    <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">
    <style>
      body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      margin: 30px;
      color: #000;
      }
      h2 {
      margin: 0 0 15px;
      text-align: center;
      }
      table {
      width: 100%;
      border-collapse: collapse;
      }
      td {
      padding: 4px;
      vertical-align: top;
      }
    </style>
  </head>
  <body>

    <?php /*** HEADER */ ?>

    <?php $company = atlas_company(); ?>
    <table style="margin-bottom:20px;">
      <tr>
        <td style="width:90px;">
          <?php /*** company logo goes here later */ ?>
        </td>
        <td style="text-align:center;">
          <div style="font-size:18px;font-weight:bold;">
            <?= htmlspecialchars($company->company_name) ?>
          </div>
          <div>
            <h4>INVENTORY ADJUSTMENT</h4>
          </div>
        </td>
        <td style="width:220px;">
          <table>
            <tr>
              <td><strong>Doc. No.</strong></td>
              <td><?= htmlspecialchars($inventoryAdjustment->adjustment_no) ?></td>
            </tr>
            <tr>
              <td><strong>Date</strong></td>
              <td><?= date('F d, Y', strtotime(htmlspecialchars($inventoryAdjustment->adjustment_date))) ?></td>
            </tr>
            <tr>
              <td><strong>Status</strong></td>
              <td><?= htmlspecialchars($inventoryAdjustment->status) ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <?php /*** REMARKS */ ?>
    <table>
        <tr>
          <td><strong>Remarks:</strong></td>
        </tr>
        <tr>
          <td><?= nl2br(htmlspecialchars($inventoryAdjustment->remarks)) ?></td>
        </tr>
    </table>

    <?php /*** DETAILS */ ?>
    <hr>
    <h4>Items</h4>
    <table border="1" cellspacing="0" cellpadding="5" width="100%">
      <thead>
        <tr>
          <th width="5%">#</th>
          <th width="15%">Barcode</th>
          <th>Description</th>
          <th width="10%">UOM</th>
          <th width="12%">On Hand</th>
          <th width="12%">Adjustment</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($details)): ?>
        <tr>
          <td colspan="7" style="text-align:center;">
            No items found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($details as $index => $item): ?>
        <tr>
          <td style="text-align:center;">
            <?= $index + 1 ?>
          </td>
          <td>
            <?= htmlspecialchars($item->barcode) ?>
          </td>
          <td>
            <?= htmlspecialchars($item->description) ?>
          </td>
          <td style="text-align:center;">
            <?= htmlspecialchars($item->uom) ?>
          </td>
          <td style="text-align:right;">
            <?= number_format($item->on_hand) ?>
          </td>
          <td style="text-align:right;">
            <?= number_format($item->adjustment_qty) ?>
          </td>
          <td>
            <?= htmlspecialchars($item->remarks) ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php /*** SIGNATURES */ ?>
    <br><br>
    <table width="100%">
      <tr>

        <td width="33%" style="text-align:center;">
          <strong>Prepared By</strong>
          <br><br><br>
          __________________________
          <br>
          <?= '<em>'.strtoupper(htmlspecialchars($inventoryAdjustment->entered_by_name)).'</em>' ?>
          <br>
          <small>
          <?= date('M d, Y h:i A', strtotime($inventoryAdjustment->entered_on)) ?>
          </small>
        </td>
        <td width="33%" style="text-align:center;">
          <?php if (!empty($inventoryAdjustment->updated_by_name)): ?>
          <strong>Last Updated By</strong>
          <br><br><br>
          __________________________
          <br>
          <?= '<em>'.strtoupper(htmlspecialchars($inventoryAdjustment->updated_by_name)).'</em>' ?>
          <br>
          <small>
          <?= date('M d, Y h:i A', strtotime($inventoryAdjustment->updated_on)) ?>
          </small>
          <?php endif; ?>
        </td>

        <td width="33%" style="text-align:center;">
          <?php if ($inventoryAdjustment->status === 'POSTED'): ?>
          <strong>Posted By</strong>
          <br><br><br>
          __________________________
          <br>
          <?= '<em>'.strtoupper(htmlspecialchars($inventoryAdjustment->posted_by_name)).'</em>' ?>
          <br>
          <small>
          <?= date('M d, Y h:i A', strtotime($inventoryAdjustment->posted_on)) ?>
          </small>
          <?php elseif ($inventoryAdjustment->status === 'CANCELLED'): ?>
          <strong>Cancelled By</strong>
          <br><br><br>
          __________________________
          <br>
          <?= '<em>'.strtoupper(htmlspecialchars($inventoryAdjustment->cancelled_by_name)).'</em>' ?>
          <br>
          <small>
          <?= date('M d, Y h:i A', strtotime($inventoryAdjustment->cancelled_on)) ?>
          </small>
          <?php if (!empty($inventoryAdjustment->cancel_reason)): ?>
          <br><br>
          <strong>Reason:</strong>
          <br>
          <?= nl2br(htmlspecialchars($inventoryAdjustment->cancel_reason)) ?>
          <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>
    </table>

  </body>
</html>

<script>
  window.onload = function () {
    window.print();
  };
</script>