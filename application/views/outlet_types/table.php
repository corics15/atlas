<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllOutletType">
        <label
          class="custom-control-label"
          for="chkSelectAllOutletType">
        </label>
      </div>
    </th>
    <th>Description</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($outlet_types)) : ?>
  <?php foreach ($outlet_types as $outlet_type) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkOutletType"
          id="chkOutletType<?= $outlet_type->id; ?>"
          value="<?= $outlet_type->id; ?>">
        <label
          class="custom-control-label"
          for="chkOutletType<?= $outlet_type->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($outlet_type->outlet_type_name); ?></td>
    <td class="text-center">
      <?= $outlet_type->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
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