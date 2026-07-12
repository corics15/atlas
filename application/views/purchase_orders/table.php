<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllPurchaseOrder">
        <label
          class="custom-control-label"
          for="chkSelectAllPurchaseOrder">
        </label>
      </div>
    </th>
    <th>PO No.</th>
    <th class="text-center">Date</th>
    <th>Customer</th>
    <th>Salesman</th>
    <th class="text-right">
      Total
    </th>
    <th class="text-center">
      Status
    </th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($purchaseOrders)): ?>

    <?php foreach ($purchaseOrders as $po): ?>
    <tr>
      <td class="text-center">
        <div class="custom-checkbox custom-control ml-2 mt-1">
          <input
            type="checkbox"
            class="custom-control-input chkPurchaseOrder"
            id="chkPurchaseOrder<?= $po->id ?>"
            value="<?= $po->id ?>">
          <label
            class="custom-control-label"
            for="chkPurchaseOrder<?= $po->id ?>">
          </label>
        </div>
      </td>
      <td><?= htmlspecialchars($po->po_no) ?></td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($po->po_date))) ?></td>
      <td><?= htmlspecialchars($po->customer_name) ?></td>
      <td><?= htmlspecialchars($po->salesman) ?></td>
      <td class="text-right">
        <?= number_format($po->total,2) ?>
      </td>
      <td class="text-center">
        <?php
          switch (htmlspecialchars($po->status)) {
            case 'CLOSED':
              $status = '<span class="badge badge-success">CLOSED</span>';
              break;
            case 'CANCELLED':
              $status = '<span class="badge badge-danger">CANCELLED</span>';
              break;
            default:
              $status = '<span class="badge badge-warning">OPEN</span>';
              break;
          }
          echo $status;
        ?>
      </td>
    </tr>
    <?php endforeach; ?>

  <?php else: ?>
  <tr>
    <td colspan="7" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>