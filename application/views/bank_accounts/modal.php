<div class="modal fade" id="mdlBankAccount" tabindex="-1" aria-labelledby="mdlBankAccountLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <?php /*** Header */ ?>
      <div class="modal-header">
        <h5 class="modal-title" id="mdlBankAccountLabel">Bank Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <?php /*** Form */ ?>
      <form id="frmBankAccount">
        <input type="hidden" id="hidBankAccountId" name="id">

        <?php /*** Body */ ?>
        <div class="modal-body">

          <?php /*** Bank + Account Name */ ?>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtBankName">Bank Name</label>
              <input type="text" id="txtBankName" name="bank_name" class="form-control form-control-sm text-uppercase" maxlength="150" placeholder="e.g. BDO">
              <small id="errBankName" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtAccountName">Account Name</label>
              <input type="text" id="txtAccountName" name="account_name" class="form-control form-control-sm text-uppercase" maxlength="150">
              <small id="errAccountName" class="text-danger"></small>
            </div>
          </div>

          <?php /*** Account No + Branch */ ?>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtAccountNo">Account No.</label>
              <input type="text" id="txtAccountNo" name="account_no" class="form-control form-control-sm text-uppercase" maxlength="100">
              <small id="errAccountNo" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtBankBranch">Bank Branch</label>
              <input type="text" id="txtBankBranch" name="bank_branch" class="form-control form-control-sm text-uppercase" maxlength="150" placeholder="Optional">
            </div>
          </div>

          <?php /*** Branch + COA Account */ ?>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="selBankBranchId">Branch</label>
              <select id="selBankBranchId" name="branch_id" class="form-control form-control-sm">
                <option value="">Shared / All Branches</option>
                <?php foreach ($branches as $branch): ?>
                  <option value="<?= (int) $branch->id ?>">
                    <?= htmlspecialchars($branch->branch_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="selCoaAccountId">COA Account</label>
              <select id="selCoaAccountId" name="coa_account_id" class="form-control form-control-sm">
                <option value="">Select Account</option>
                <?php foreach ($coa_accounts as $coa): ?>
                  <option value="<?= (int) $coa->id ?>">
                    <?= htmlspecialchars($coa->account_code . ' - ' . $coa->account_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="errCoaAccountId" class="text-danger"></small>
            </div>
          </div>

          <?php /*** Check Enabled */ ?>
          <div class="form-row">
            <div class="form-group col-md-6 mb-0">
              <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" class="custom-control-input" id="chkCheckEnabled" name="is_check_enabled" value="1" checked>
                <label class="custom-control-label" for="chkCheckEnabled">Check Enabled</label>
              </div>
            </div>
          </div>

        </div>

        <?php /*** Footer */ ?>
        <div class="modal-footer">
          <button type="submit" id="btnSaveBankAccount" class="btn btn-sm btn-default">Save Bank Account</button>
        </div>

      </form>
    </div>
  </div>
</div>
