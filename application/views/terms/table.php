<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="chkSelectAllTerm">
        <label
          class="custom-control-label"
          for="chkSelectAllTerm">
        </label>
      </div>
    </th>
    <th>Terms</th>
    <th class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($terms)) : ?>
  <?php foreach ($terms as $term) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkTerm"
          id="chkTerm<?= $term->id; ?>"
          value="<?= $term->id; ?>">
        <label
          class="custom-control-label"
          for="chkTerm<?= $term->id; ?>">
        </label>
      </div>
    </td>
    <td><?= htmlspecialchars($term->terms_name); ?></td>
    <td class="text-center">
      <?= $term->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
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