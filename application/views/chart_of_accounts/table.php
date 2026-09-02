<thead class="thead-orange">
  <tr>
    <th class="text-center" data-a-h="center">Account Code</th>
    <th>Account Name</th>
    <th class="text-center" data-a-h="center">Type</th>
    <th class="text-center" data-a-h="center">Normal Balance</th>
    <th>Report Group</th>
    <th class="text-center" data-a-h="center">Account Class</th>
    <th class="text-center" data-a-h="center">Active</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($accounts)): ?>

    <tr>
      <td colspan="7" class="text-center text-muted py-3">
        No accounts found.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($accounts as $row): ?>
      <tr data-id="<?= $row->id; ?>">

        <td class="text-center" data-a-h="center" data-t="n" data-num-fmt="####">
          <a href="<?= $row->url; ?>" class="text-olive">
            <?= htmlspecialchars($row->account_code); ?>
          </a>
        </td>

        <td class="<?= $row->is_posting == 't' ? 'pl-4' : 'font-weight-500 letter-spacing-2 text-orange' ?>" <?= $row->is_posting == 'f' ? 'data-f-bold="true" data-fill-color="66bb6a"' : '' ?>><?= htmlspecialchars($row->account_name); ?></td>
        <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->account_type); ?></td>
        <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->normal_balance); ?></td>
        <td><?= htmlspecialchars($row->group_name ?? ''); ?></td>

        <td class="text-center" data-a-h="center">
          <?php if ($row->is_posting == 't'): ?>
            <span class="badge badge-success">POSTING</span>
          <?php else: ?>
            <span class="badge badge-primary">GROUP</span>
          <?php endif; ?>
        </td>

        <td class="text-center" data-a-h="center" data-excel-value="<?= $row->is_active == 't' ? 'Y' : '' ?>">
          <?php if ($row->is_active == 't'): ?>
            <i class="fas fa-check text-success"></i>
          <?php endif; ?>
        </td>

      </tr>
    <?php endforeach; ?>

  <?php endif; ?>
</tbody>