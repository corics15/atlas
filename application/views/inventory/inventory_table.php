<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllInventory">
        <label class="custom-control-label" for="chkSelectAllInventory"></label>
      </div>
    </th>
    <th class="text-center">Case Barcode</th>
    <th class="text-center">Barcode</th>
    <th>Description</th>
    <th class="text-center">PKG</th>
    <th>Supplier</th>
    <th class="text-center">UOM</th>
    <th class="text-right">Qty</th>
    <th class="text-right">Cost</th>
    <th class="text-right">SP</th>
    <th class="text-right">Amount</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($inventoryInquiry)): ?>

    <tr>
      <td colspan="10" class="text-center text-muted py-3">
        No inventories found.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($inventoryInquiry as $row): ?>
    <tr data-id="<?= $row->product_id; ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkInventoryInquiry" id="chkInventoryInquiry-<?= $row->product_id ?>" value="<?= $row->product_id ?>">
          <label class="custom-control-label" for="chkInventoryInquiry-<?= $row->product_id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <a href="<?= base_url('inventory/ledger/'.$row->product_id) ?>" class="text-wrap text-olive"><?= htmlspecialchars($row->case_barcode); ?></a>
      </td>
      <td class="text-center">
        <a href="<?= base_url('inventory/ledger/'.$row->product_id) ?>" class="text-wrap text-olive"><?= htmlspecialchars($row->barcode); ?></a>
      </td>
      <td><?= htmlspecialchars($row->description); ?></td>
      <td class="text-center"><?= htmlspecialchars($row->pkg); ?></td>
      <td><?= htmlspecialchars($row->supplier_name); ?></td>
      <td class="text-center"><?= htmlspecialchars($row->uom); ?></td>
      <td class="text-right"><?= number_format($row->qty_on_hand); ?></td>
      <td class="text-right"><?= number_format($row->cost, 2); ?></td>
      <td class="text-right"><?= number_format($row->selling_price, 2); ?></td>
      <td class="text-right"><?= number_format($row->inventory_value, 2); ?></td>
    </tr>
    <?php endforeach; ?>

  <?php endif; ?>
</tbody>