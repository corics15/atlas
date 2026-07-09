<thead class="thead-cyan">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllSalesmen">
        <label
          class="custom-control-label"
          for="chkSelectAllSalesmen">
        </label>
      </div>
    </th>
    <th>Code</th>
    <th>Salesman</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($salesmen)) : ?>
  <?php foreach ($salesmen as $salesman) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkSalesman"
          id="chkSalesman<?= $salesman->id; ?>"
          value="<?= $salesman->id; ?>">
        <label
          class="custom-control-label"
          for="chkSalesman<?= $salesman->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($salesman->code); ?></td>
    <td>
      <?= htmlspecialchars($salesman->first_name . ' ' . $salesman->last_name); ?>
    </td>
    <td class="text-center">
      <?= $salesman->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="4" class="text-center">
      No records found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>