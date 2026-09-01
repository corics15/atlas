<?php
  $account = $account ?? null;
  $isEdit  = !empty($account);

  $isPosting = $isEdit && (
      $account->is_posting === TRUE ||
      $account->is_posting === 't' ||
      $account->is_posting === '1' ||
      $account->is_posting === 1
  );

  $isActive = $isEdit && (
      $account->is_active === TRUE ||
      $account->is_active === 't' ||
      $account->is_active === '1' ||
      $account->is_active === 1
  );
?>

<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Account Information
          </h3>

          <div class="ml-auto">
            <a href="<?= base_url('chart-of-accounts') ?>" class="btn btn-sm btn-link">
              <i class="fa fa-arrow-alt-circle-left mr-2"></i>
              Back To List
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">

        <div class="form-row">

          <div class="form-group col-md-3">
            <label for="txtAccountCode">Account Code</label>
            <input type="text" id="txtAccountCode" class="form-control form-control-sm" maxlength="50" autocomplete="off" placeholder="Enter Account Code..."
              value="<?= $isEdit ? htmlspecialchars($account->account_code) : '' ?>">
          </div>

          <div class="form-group col-md-9">
            <label for="txtAccountName">Account Name</label>
            <input type="text" id="txtAccountName" class="form-control form-control-sm text-uppercase" maxlength="200" autocomplete="off" placeholder="Enter Account Name..."
              value="<?= $isEdit ? htmlspecialchars($account->account_name) : '' ?>">
          </div>

        </div>

        <div class="form-row">

          <div class="form-group col-md-6">
            <label for="selParentAccount">Parent Account</label>
            <select id="selParentAccount" class="custom-select form-control form-control-sm">
              <option value="">-- None --</option>
              <?php foreach ($parentAccounts as $parent): ?>
                <option
                  value="<?= (int) $parent->id ?>"
                  data-account-type="<?= htmlspecialchars($parent->account_type) ?>"
                  data-normal-balance="<?= htmlspecialchars($parent->normal_balance) ?>"
                  data-account-group-id="<?= (int) $parent->account_group_id ?>"
                  <?= $isEdit && (int) $account->parent_id === (int) $parent->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($parent->account_code . ' - ' . $parent->account_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-md-6">
            <label for="selAccountGroup">Report Group</label>
            <select id="selAccountGroup" class="custom-select form-control form-control-sm">
              <option value="">-- None --</option>
              <?php foreach ($accountGroups as $group): ?>
                <option
                  value="<?= (int) $group->id ?>"
                  <?= $isEdit &&
                      (int) $account->account_group_id === (int) $group->id
                      ? 'selected'
                      : '' ?>>
                  <?= htmlspecialchars($group->group_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <div class="form-row">

          <div class="form-group col-md-3">
            <label for="selAccountType">Account Type</label>
            <select id="selAccountType" class="custom-select form-control form-control-sm">
              <option value="">-- Select Account Type --</option>
              <option value="ASSET" <?= $isEdit && $account->account_type === 'ASSET' ? 'selected' : '' ?>>ASSET</option>
              <option value="LIABILITY" <?= $isEdit && $account->account_type === 'LIABILITY' ? 'selected' : '' ?>>LIABILITY</option>
              <option value="EQUITY" <?= $isEdit && $account->account_type === 'EQUITY' ? 'selected' : '' ?>>EQUITY</option>
              <option value="REVENUE" <?= $isEdit && $account->account_type === 'REVENUE' ? 'selected' : '' ?>>REVENUE</option>
              <option value="EXPENSE" <?= $isEdit && $account->account_type === 'EXPENSE' ? 'selected' : '' ?>>EXPENSE</option>
            </select>
          </div>

          <div class="form-group col-md-3">
            <label for="selNormalBalance">Normal Balance</label>
            <select id="selNormalBalance" class="custom-select form-control form-control-sm">
              <option value="">-- Select Normal Balance --</option>
              <option value="DEBIT" <?= $isEdit && $account->normal_balance  === 'DEBIT' ? 'selected' : '' ?>>DEBIT</option>
              <option value="CREDIT" <?= $isEdit && $account->normal_balance  === 'CREDIT' ? 'selected' : '' ?>>CREDIT</option>
            </select>
          </div>

          <div class="form-group col-md-3">
            <label for="selAccountClass">Account Class</label>
            <select id="selAccountClass" class="custom-select form-control form-control-sm">
              <option value="POSTING" <?= (!$isEdit || $isPosting) ? 'selected' : '' ?>>POSTING</option>
              <option value="GROUP" <?= ($isEdit && !$isPosting) ? 'selected' : '' ?>>GROUP</option>
            </select>
          </div>

          <div class="form-group col-md-3">
            <label for="selAccountStatus">Status</label>
            <select id="selAccountStatus" class="custom-select form-control form-control-sm">
              <option value="ACTIVE" <?= (!$isEdit || $isActive) ? 'selected' : '' ?>>ACTIVE</option>
              <option value="INACTIVE" <?= ($isEdit && !$isActive) ? 'selected' : '' ?>>INACTIVE</option>
            </select>
          </div>

        </div>

        <div class="form-group mb-0">
          <label for="txtRemarks">Remarks</label>
          <textarea id="txtRemarks" class="form-control form-control-sm text-uppercase" rows="2"
            placeholder="Enter Remarks..."><?= $isEdit ? htmlspecialchars($account->remarks) : '' ?></textarea>
        </div>

      </div>
    </div>

    <div class="card mt-3 mb-3">
      <div class="card-body">
        <div class="form-row">
          <div class="col-md-8">
            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Chart of Accounts Guide
              </div>

              <div>
                <span class="font-weight-500">GROUP</span>
                accounts organize the Chart of Accounts and cannot receive journal postings.
              </div>

              <div>
                <span class="font-weight-500">POSTING</span>
                accounts can be selected when recording accounting transactions.
              </div>
            </div>
          </div>

          <div class="col-md-4 d-flex align-items-end">
            <button type="button" id="btnSaveChartOfAccount" class="btn btn-default btn-sm btn-block">
              Save Account
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<input type="hidden" id="hidChartOfAccountId" value="<?= $isEdit ? (int) $account->id : '' ?>">