<thead>
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllUom">
        <label
          class="custom-control-label"
          for="chkSelectAllUom">
        </label>
      </div>
    </th>
    <th>UOM</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($uoms)) : ?>
  <?php foreach ($uoms as $uom) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkUom"
          id="chkUom<?= $uom->id; ?>"
          value="<?= $uom->id; ?>">
        <label
          class="custom-control-label"
          for="chkUom<?= $uom->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($uom->uom); ?></td>
    <td class="text-center">
      <?= $uom->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="3" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>