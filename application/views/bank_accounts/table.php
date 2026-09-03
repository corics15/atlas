<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllBankAccount">
        <label class="custom-control-label" for="chkSelectAllBankAccount"></label>
      </div>
    </th>
    <th>Bank</th>
    <th>Account Name</th>
    <th>Account No.</th>
    <th>Bank Branch</th>
    <th>ATLAS Branch</th>
    <th>COA Account</th>
    <th class="text-center">Check</th>
    <th class="text-center">Active</th>
  </tr>
</thead>

<tbody>
  <?php if (!empty($bank_accounts)): ?>
    <?php foreach ($bank_accounts as $bank): ?>
      <tr>
        <td class="text-center">
          <div class="custom-checkbox custom-control ml-2 mt-1">
            <input type="checkbox" class="custom-control-input chkBankAccount" id="chkBankAccount<?= (int) $bank->id ?>" value="<?= (int) $bank->id ?>">
            <label class="custom-control-label" for="chkBankAccount<?= (int) $bank->id ?>"></label>
          </div>
        </td>
        <td><?= htmlspecialchars($bank->bank_name) ?></td>
        <td><?= htmlspecialchars($bank->account_name) ?></td>
        <td><?= htmlspecialchars($bank->account_no) ?></td>
        <td><?= htmlspecialchars($bank->bank_branch ?? '') ?></td>
        <td><?= !empty($bank->branch_name) ? htmlspecialchars($bank->branch_name) : '<span class="text-muted">SHARED</span>' ?></td>
        <td><?= htmlspecialchars($bank->account_code . ' - ' . $bank->coa_account_name) ?></td>
        <td class="text-center"><?= $bank->is_check_enabled == 't' ? '<i class="fas fa-check text-success"></i>' : '' ?></td>
        <td class="text-center"><?= $bank->is_active == 't' ? '<i class="fas fa-check text-success"></i>' : '' ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="9" class="text-center py-3">No records found.</td>
    </tr>
  <?php endif; ?>
</tbody>
