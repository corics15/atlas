<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
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
    <th class="text-center">Barcode</th>
    <th>Description</th>
    <th class="text-center">UOM</th>
    <th class="text-right">Qty on Hand</th>
    <th class="text-right">Cost</th>
    <th class="text-right">SRP</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($products)) : ?>
  <?php foreach ($products as $product) : ?>
  <tr>
    <td class="text-center">
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
    <td><?= htmlspecialchars($product->supplier_name); ?></td>
    <td class="text-center">
      <?= htmlspecialchars($product->barcode); ?>
    </td>
    <td>
      <?= htmlspecialchars($product->description); ?>
    </td>
    <td class="text-center"><?= $product->uom ?></td>
    <td class="text-right"><?= number_format($product->qty_on_hand) ?></td>
    <td class="text-right"><?= $product->cost ?></td>
    <td class="text-right"><?= $product->srp ?></td>
    <td class="text-center">
      <?= $product->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="9" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>