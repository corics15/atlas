<?php
  $cv = $checkVoucher ?? null;

  $id             = $cv->id ?? '';
  $cvNo           = $cv->cv_no ?? 'AUTO-GENERATED';
  $voucherDate    = $cv->voucher_date ?? date('Y-m-d');
  $branchId       = $cv->branch_id ?? $this->session->userdata('branch_id');
  $payeeType      = $cv->payee_type ?? 'SUPPLIER';
  $supplierId     = $cv->supplier_id ?? '';
  $payeeName      = $cv->payee_name ?? '';
  $paymentMethod  = $cv->payment_method ?? 'CHECK';
  $bankAccountId  = $cv->bank_account_id ?? '';
  $checkNo        = $cv->check_no ?? '';
  $checkDate      = $cv->check_date ?? '';
  $referenceNo    = $cv->reference_no ?? '';
  $particulars    = $cv->particulars ?? '';
  $status         = $cv->status ?? 'DRAFT';
  $disabled       = $isEditable ? '' : 'disabled';
?>

<div id="checkVoucherForm" data-id="<?= htmlspecialchars($id) ?>" data-editable="<?= $isEditable ? '1' : '0' ?>">

  <div class="card mb-3">
    <div class="card-header">
      <h3 class="card-title">Check Voucher Information</h3>
    </div>
    <div class="card-body">

      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="cvNo">CV No.</label>
            <input type="text" id="cvNo" class="form-control form-control-sm font-weight-500 text-brown" value="<?= htmlspecialchars($cvNo) ?>" readonly>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="voucherDate">Voucher Date</label>
            <input type="date" id="voucherDate" class="form-control form-control-sm" value="<?= htmlspecialchars($voucherDate) ?>" <?= $disabled ?>>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="branchId">Branch</label>
            <select id="branchId" class="form-control" <?= $disabled ?>>
              <option value="">Select Branch</option>
              <?php foreach ($branches as $branch): ?>
                <option value="<?= (int) $branch->id ?>" <?= (int) $branchId === (int) $branch->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($branch->branch_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Status</label>
            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($status) ?>" readonly>
          </div>
        </div>
      </div>

      <hr>

      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="payeeType">Payee Type</label>
            <select id="payeeType" class="form-control" <?= $disabled ?>>
              <option value="SUPPLIER" <?= $payeeType === 'SUPPLIER' ? 'selected' : '' ?>>Supplier</option>
              <option value="OTHER" <?= $payeeType === 'OTHER' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="col-md-5" id="supplierField">
          <div class="form-group">
            <label for="supplierId">Supplier</label>
            <select id="supplierId" class="form-control" <?= $disabled ?>>
              <option value="">Select Supplier</option>
              <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= (int) $supplier->id ?>" <?= (int) $supplierId === (int) $supplier->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($supplier->supplier_name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-5" id="otherPayeeField">
          <div class="form-group">
            <label for="payeeName">Payee Name</label>
            <input type="text" id="payeeName" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($payeeName) ?>" maxlength="200" <?= $disabled ?>>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="paymentMethod">Payment Method</label>
            <select id="paymentMethod" class="form-control" <?= $disabled ?>>
              <?php
                $paymentMethods = [
                  'CHECK' => 'Check',
                  'BANK_TRANSFER' => 'Bank Transfer',
                  'CASH' => 'Cash',
                  'OTHER' => 'Other'
                ];
              foreach ($paymentMethods as $value => $label): ?>
                <option value="<?= $value ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>>
                  <?= $label ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-5" id="bankAccountField">
          <div class="form-group">
            <label for="bankAccountId">Bank Account</label>
            <select id="bankAccountId" class="form-control" <?= $disabled ?>>
              <option value="">Select Bank Account</option>
              <?php foreach ($bankAccounts as $bank): ?>
                <option value="<?= (int) $bank->id ?>"
                        data-branch-id="<?= htmlspecialchars($bank->branch_id ?? '') ?>"
                        data-coa-account-id="<?= (int) $bank->coa_account_id ?>"
                        data-check-enabled="<?= $bank->is_check_enabled ? '1' : '0' ?>"
                        <?= (int) $bankAccountId === (int) $bank->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($bank->bank_name . ' - ' . $bank->account_name . ' (' . $bank->account_no . ')') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row" id="checkFields">
        <div class="col-md-3">
          <div class="form-group">
            <label for="checkNo">Check No.</label>
            <input type="text" id="checkNo" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($checkNo) ?>" maxlength="100" <?= $disabled ?>>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="checkDate">Check Date</label>
            <input type="date" id="checkDate" class="form-control form-control-sm" value="<?= htmlspecialchars($checkDate) ?>" <?= $disabled ?>>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="referenceNo">Reference No.</label>
            <input type="text" id="referenceNo" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($referenceNo) ?>" maxlength="100" <?= $disabled ?>>
          </div>
        </div>
      </div>

      <div class="form-group mb-0">
        <label for="particulars">Particulars / Explanation</label>
        <textarea id="particulars" class="form-control text-uppercase" rows="2" <?= $disabled ?>><?= htmlspecialchars($particulars) ?></textarea>
      </div>

    </div>
  </div>
</div>

<?php if (false) : ?>
<div id="checkVoucherForm" data-id="<?= htmlspecialchars($id) ?>" data-editable="<?= $isEditable ? '1' : '0' ?>">

  <?php /*** voucher Info */ ?>
  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title mb-0">Voucher</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="cvNo">CV No.</label>
          <input type="text" id="cvNo" class="form-control form-control-sm" value="<?= htmlspecialchars($cvNo) ?>" readonly>
        </div>
        <div class="form-group col-md-3">
          <label for="voucherDate">Voucher Date</label>
          <input type="date" id="voucherDate" class="form-control form-control-sm" value="<?= htmlspecialchars($voucherDate) ?>" <?= $disabled ?>>
        </div>
        <div class="form-group col-md-3">
          <label for="branchId">Branch</label>
          <select id="branchId" class="form-control form-control-sm" <?= $disabled ?>>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $branch): ?>
              <option value="<?= (int) $branch->id ?>" <?= (int) $branchId === (int) $branch->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($branch->branch_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Status</label>
          <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($status) ?>" readonly>
        </div>
      </div>
    </div>
  </div>

  <?php /*** payee Info */ ?>
  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title mb-0">Payee</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="payeeType">Payee Type</label>
          <select id="payeeType" class="form-control form-control-sm" <?= $disabled ?>>
            <option value="SUPPLIER" <?= $payeeType === 'SUPPLIER' ? 'selected' : '' ?>>Supplier</option>
            <option value="OTHER" <?= $payeeType === 'OTHER' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="form-group col-md-5" id="supplierField">
          <label for="supplierId">Supplier</label>
          <select id="supplierId" class="form-control form-control-sm" <?= $disabled ?>>
            <option value="">Select Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
              <option value="<?= (int) $supplier->id ?>" <?= (int) $supplierId === (int) $supplier->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($supplier->supplier_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4" id="otherPayeeField">
          <label for="payeeName">Payee Name</label>
          <input type="text" id="payeeName" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($payeeName) ?>" maxlength="200" <?= $disabled ?>>
        </div>
      </div>
    </div>
  </div>

  <?php /*** payment Info */ ?>
  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title mb-0">Payment</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="paymentMethod">Payment Method</label>
          <select id="paymentMethod" class="form-control form-control-sm" <?= $disabled ?>>
            <?php foreach (['CHECK'=>'Check','BANK_TRANSFER'=>'Bank Transfer','CASH'=>'Cash','OTHER'=>'Other'] as $value=>$label): ?>
              <option value="<?= $value ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-5" id="bankAccountField">
          <label for="bankAccountId">Bank Account</label>
          <select id="bankAccountId" class="form-control form-control-sm" <?= $disabled ?>>
            <option value="">Select Bank Account</option>
            <?php foreach ($bankAccounts as $bank): ?>
              <option value="<?= (int) $bank->id ?>" <?= (int) $bankAccountId === (int) $bank->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($bank->bank_name . ' - ' . $bank->account_name . ' (' . $bank->account_no . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-2" id="checkFields">
          <label for="checkNo">Check No.</label>
          <input type="text" id="checkNo" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($checkNo) ?>" maxlength="100" <?= $disabled ?>>
        </div>
        <div class="form-group col-md-2">
          <label for="checkDate">Check Date</label>
          <input type="date" id="checkDate" class="form-control form-control-sm" value="<?= htmlspecialchars($checkDate) ?>" <?= $disabled ?>>
        </div>
      </div>
    </div>
  </div>

  <?php /*** references */ ?>
  <div class="card mb-3">
    <div class="card-header"><h3 class="card-title mb-0">References</h3></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="referenceNo">Reference No.</label>
          <input type="text" id="referenceNo" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($referenceNo) ?>" maxlength="100" <?= $disabled ?>>
        </div>
        <div class="form-group col-md-6">
          <label for="particulars">Particulars / Explanation</label>
          <textarea id="particulars" class="form-control form-control-sm text-uppercase" rows="1" <?= $disabled ?>><?= htmlspecialchars($particulars) ?></textarea>
        </div>
      </div>
    </div>
  </div>

</div>
<?php endif; ?>