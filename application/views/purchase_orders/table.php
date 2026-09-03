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
    <th class="text-center">Date</th>
    <th class="text-center">PO No.</th>
    <th>Supplier</th>
    <th class="text-center">Item Count</th>
    <th class="text-right">Total</th>
    <th>Remarks</th>
    <th class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($purchaseOrders)): ?>

    <?php foreach ($purchaseOrders as $po): ?>
    <tr>
      <td class="text-center">
        <div class="custom-checkbox custom-control ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkPurchaseOrder" id="chkPurchaseOrder-<?= $po->id ?>" value="<?= $po->id ?>"
            data-status="<?= htmlspecialchars($po->status); ?>">
          <label class="custom-control-label" for="chkPurchaseOrder-<?= $po->id ?>"></label>
        </div>
      </td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($po->po_date))) ?></td>
      <td class="text-center">
        <a href="<?= $po->url ?>" class="font-weight-500 text-olive">
          <?= htmlspecialchars($po->po_no) ?>
        </a>
      </td>
      <td <?= mb_strlen($po->supplier_name) > 30 ? 'data-toggle="tooltip" title="'.htmlspecialchars($po->supplier_name).'"' : '' ?>>
        <?php
          $supplierName = htmlspecialchars($po->supplier_name);
          echo (mb_strlen($supplierName) > 30)
            ? mb_strimwidth($supplierName, 0, 30, '...')
            : $supplierName;
        ?>
      </td>
      <td class="text-center">
        <?= number_format($po->item_count) ?>
      </td>
      <td class="text-right">
        <?= number_format($po->total, 2) ?>
      </td>
      <td>
        <?php
          $remarks = htmlspecialchars($po->remarks);
          echo (mb_strlen($remarks) > 30)
            ? mb_strimwidth($remarks, 0, 30, '...')
            : $remarks;
        ?>
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
    <td colspan="8" class="text-center py-3">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>