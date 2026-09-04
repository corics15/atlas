<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllGoodsReceipt">
        <label class="custom-control-label" for="chkSelectAllGoodsReceipt"></label>
      </div>
    </th>
    <th width="120" class="text-center">Date</th>
    <th width="140" class="text-center">GRN No.</th>
    <th width="160" class="text-center">PO No.</th>
    <th>Supplier</th>
    <th class="text-center">Item Count</th>
    <th class="text-right">Total Amount</th>
    <th class="text-center">Status</th>
    <th>Remarks</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($goodsReceipts)): ?>

    <tr>
      <td colspan="9" class="text-center text-muted py-3">
        No goods receipts found.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($goodsReceipts as $row): ?>
    <tr data-id="<?= $row->id; ?>" data-status="<?= $row->status ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkGoodsReceipt" id="chkGoodsReceipt-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkGoodsReceipt-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($row->grn_date))); ?></td>
      <td class="text-center">
        <a href="<?= $row->url ?>" class="font-weight-500 text-olive">
          <?= htmlspecialchars($row->grn_no); ?>
        </a>
      </td>
      <td class="text-center">
        <a href="<?= $row->po_url  ?>" class="font-weight-500 text-olive" target="_blank"><i class="fa-external-link-alt fas fa-xs mr-1"></i><?= htmlspecialchars($row->po_no) ?></a>
      </td>
      <td <?= mb_strlen($row->supplier_name) > 30 ? 'data-toggle="tooltip" title="'.htmlspecialchars($row->supplier_name).'"' : '' ?>>
        <?php
          $supplierName = htmlspecialchars($row->supplier_name);
          echo (mb_strlen($supplierName) > 30)
            ? mb_strimwidth($supplierName, 0, 30, '...')
            : $supplierName;
        ?>
      </td>
      <td class="text-center"><?= number_format($row->item_count) ?></td>
      <td class="text-right"><?= number_format($row->total_amount, 2) ?></td>
      <td class="text-center">
        <?php
          switch (htmlspecialchars($row->status)) {
            case 'DRAFT':
              $status = '<span class="badge badge-secondary">DRAFT</span>';
              break;
            case 'POSTED':
              $status = '<span class="badge badge-success">POSTED</span>';
              break;
            default:
              $status = '<span class="badge badge-danger">CANCELLED</span>';
              break;
          }
          echo $status;
        ?>
      </td>
      <td>
        <?php
          $remarks = htmlspecialchars($row->remarks);
          echo (mb_strlen($remarks) > 30)
            ? mb_strimwidth($remarks, 0, 30, '...')
            : $remarks;
        ?>
      </td>
    </tr>
    <?php endforeach; ?>

  <?php endif; ?>
</tbody>