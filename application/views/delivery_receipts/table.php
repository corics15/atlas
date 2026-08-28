<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center" data-exclude="true">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllDeliveryReceipt"/>
        <label class="custom-control-label" for="chkSelectAllDeliveryReceipt"></label>
      </div>
    </th>
    <th class="text-center">Delivery Date</th>
    <th class="text-center">DR No.</th>
    <th class="text-center">SO No.</th>
    <th>Customer</th>
    <th>Remarks</th>
    <th class="text-center">Item Count</th>
    <th class="text-right">Total Amt</th>
    <th class="text-center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($deliveryReceipts)): ?>

  <tr>
    <td colspan="9" class="text-center text-muted py-3">
      No Delivery Receipts found.
    </td>
  </tr>

  <?php else: ?>
    <?php foreach ($deliveryReceipts as $row): ?>
      <?php
        switch ($row->status) {
          case 'OPEN':
            $status = '<span class="badge badge-secondary">OPEN</span>';
          break;
          case 'POSTED':
            $status = '<span class="badge badge-success">POSTED</span>';
          break;
          default:
            $status = '<span class="badge badge-danger">CANCELLED</span>';
          break;
        }
      ?>

      <tr data-id="<?= $row->id; ?>" data-status="<?= $row->status; ?>">
        <td class="text-center" data-exclude="true">
          <div class="custom-control custom-checkbox ml-2 mt-1">
            <input type="checkbox" class="custom-control-input chkDeliveryReceipt" id="chkDeliveryReceipt-<?= $row->id; ?>" value="<?= $row->id; ?>"/>
            <label class="custom-control-label" for="chkDeliveryReceipt-<?= $row->id; ?>"></label>
          </div>
        </td>

        <td class="text-center">
          <?= date('m/d/Y', strtotime($row->delivery_date)); ?>
        </td>

        <td class="text-center">
          <a href="<?= $row->url ?>" class="text-olive">
            <?= htmlspecialchars($row->dr_no); ?>
          </a>
        </td>

        <td class="text-center">
          <a href="<?= $row->so_url ?>" class="text-olive" target="_blank">
            <i class="fa-external-link-alt fas fa-xs mr-1"></i><?= htmlspecialchars($row->so_no); ?>
          </a>
        </td>

        <td data-excel-value="<?= htmlspecialchars($row->customer_name) ?>">
          <?php
            $customerName = htmlspecialchars($row->customer_name);
            echo (mb_strlen($customerName) > 30)
              ? mb_strimwidth($customerName, 0, 30, '...')
              : $customerName;
          ?>
        </td>
        <td data-excel-value="<?= htmlspecialchars($row->remarks) ?>">
          <?php
            $remarks = htmlspecialchars($row->remarks);
            echo (mb_strlen($remarks) > 30)
              ? mb_strimwidth($remarks, 0, 30, '...')
              : $remarks;
          ?>
        </td>
        <td class="text-center" data-t="n" data-num-fmt="#,##0"><?= number_format($row->item_count, 0) ?></td>
        <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($row->total_amount, 2) ?></td>
        <td class="text-center"><?= $status; ?></td>
      </tr>

    <?php endforeach; ?>
  <?php endif; ?>
</tbody>