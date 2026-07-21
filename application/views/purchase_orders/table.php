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
    <th class="text-center">PO No.</th>
    <th class="text-center">Date</th>
    <th>Supplier</th>
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
            id="chkPurchaseOrder-<?= $po->id ?>"
            value="<?= $po->id ?>"
            data-status="<?= htmlspecialchars($po->status); ?>">
          <label
            class="custom-control-label"
            for="chkPurchaseOrder-<?= $po->id ?>">
          </label>
        </div>
      </td>
      <td class="text-center">
        <a href="<?= base_url('purchase_orders?id='.$po->id) ?>" class="text-olive text-wrap">
          <?= htmlspecialchars($po->po_no) ?>
        </a>
      </td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($po->po_date))) ?></td>
      <td><?= htmlspecialchars($po->supplier_name) ?></td>
      <td class="text-right">
        <?= number_format($po->total,2) ?>
      </td>
      <td class="text-center">
        <?php
          switch (htmlspecialchars($po->status)) {
            case 'OPEN':
              $status = '<span class="badge badge-success">OPEN</span>';
              break;

            case 'PARTIAL':
              $status = '<span class="badge badge-warning">PARTIAL</span>';
              break;

            case 'COMPLETED':
              $status = '<span class="badge badge-primary">COMPLETED</span>';
              break;

            case 'CLOSED':
              $status = '<span class="badge badge-secondary">CLOSED</span>';
              break;

            case 'CANCELLED':
              $status = '<span class="badge badge-danger">CANCELLED</span>';
              break;

            default:
              $status = '<span class="badge badge-light">UNKNOWN</span>';
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