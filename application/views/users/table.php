<thead class="thead-gold">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllUsers">
        <label class="custom-control-label" for="chkSelectAllUsers"></label>
      </div>
    </th>
    <th>Username</th>
    <th>Name</th>
    <th width="120" class="text-center">Active</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($users)) : ?>
  <?php foreach ($users as $user) : ?>
  <tr>
    <td class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input type="checkbox" class="custom-control-input chkUser" id="chkUser<?= $user->id; ?>" value="<?= $user->id; ?>">
        <label class="custom-control-label" for="chkUser<?= $user->id; ?>"></label>
      </div>
    </td>
    <td><?= htmlspecialchars($user->username); ?></td>
    <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></td>
    <td class="text-center">
      <?= $user->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : ''; ?>
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