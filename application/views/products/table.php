<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center" data-exclude="true">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllProduct">
        <label
          class="custom-control-label"
          for="chkSelectAllProduct">
        </label>
      </div>
    </th>
    <th>Supplier</th>
    <th class="text-center">Case Barcode</th>
    <th class="text-center">Barcode</th>
    <th>Description</th>
    <th>Packing</th>
    <th class="text-right">Qty on Hand</th>
    <th class="text-center">UOM</th>
    <th class="text-right">Cost</th>
    <th class="text-right">SRP</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($products)) : ?>
  <?php foreach ($products as $product) : ?>
  <tr>
    <?php /*** checkbox */ ?>
    <td class="text-center" data-exclude="true">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkProduct"
          id="chkProduct<?= $product->id; ?>"
          value="<?= $product->id; ?>">
        <label
          class="custom-control-label"
          for="chkProduct<?= $product->id; ?>">
        </label>
      </div>
    </td>
    <?php /*** suppliler */ ?>
    <td data-excel-value="<?= htmlspecialchars($product->supplier_name) ?>">
      <?php
        $supplierName = htmlspecialchars($product->supplier_name);
        echo (mb_strlen($supplierName) > 30)
          ? mb_strimwidth($supplierName, 0, 30, '...')
          : $supplierName;
      ?>
    </td>
    <?php /*** case barcode */ ?>
    <td class="text-center">
      <?= htmlspecialchars($product->case_barcode); ?>
    </td>
    <?php /*** barcode */ ?>
    <td class="text-center">
      <?= htmlspecialchars($product->barcode); ?>
    </td>
    <?php /*** description */ ?>
    <td data-excel-value="<?= htmlspecialchars($product->description) ?>">
      <?php
        $description = htmlspecialchars($product->description);
        echo (mb_strlen($description) > 30)
          ? mb_strimwidth($description, 0, 30, '...')
          : $description;
      ?>
    </td>
    <?php /*** packing */ ?>
    <td><?= htmlspecialchars($product->pkg) ?></td>
    <?php /*** qty */ ?>
    <td class="text-right" data-t="n" data-num-fmt="#,##0"><?= number_format($product->qty_on_hand) ?></td>
    <?php /*** uom */ ?>
    <td class="text-center"><?= $product->uom ?></td>
    <?php /*** cost */ ?>
    <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= $product->cost ?></td>
    <?php /*** srp */ ?>
    <td class="text-right"><?= $product->srp ?></td>
    <?php /*** active ? */ ?>
    <td class="text-center" data-excel-value="<?= $product->is_active == 't' ? 'Y' : '' ?>">
      <?= $product->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="11" class="text-center py-3">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>