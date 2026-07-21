<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllGoodsReceipt">
        <label class="custom-control-label" for="chkSelectAllGoodsReceipt"></label>
      </div>
    </th>
    <th width="140">GRN No.</th>
    <th width="120" class="text-center">Date</th>
    <th width="140">PO No.</th>
    <th>Supplier</th>
    <th>Remarks</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($goodsReceipts)): ?>

    <tr>
      <td colspan="6" class="text-center text-muted py-3">
        No goods receipts found.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($goodsReceipts as $row): ?>
    <tr data-id="<?= $row->id; ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkGoodsReceipt" id="chkGoodsReceipt-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkGoodsReceipt-<?= $row->id ?>"></label>
        </div>
      </td>
      <td>
        <a href="<?= base_url('goods_receipts/view/'.$row->id) ?>" class="text-olive text-wrap">
          <?= htmlspecialchars($row->grn_no); ?>
        </a>
      </td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($row->grn_date))); ?></td>
      <td><?= htmlspecialchars($row->po_no); ?></td>
      <td><?= htmlspecialchars($row->supplier_name); ?></td>
      <td><?= htmlspecialchars($row->remarks); ?></td>
    </tr>
    <?php endforeach; ?>

  <?php endif; ?>
</tbody>