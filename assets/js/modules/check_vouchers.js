const txtCheckVoucherNo = document.getElementById('cvNo');
const dtVoucherDate = document.getElementById('voucherDate');
const selBranch = document.getElementById('branchId');

const selPayeeType = document.getElementById('payeeType');
const selSupplier = document.getElementById('supplierId');
const txtPayeeName = document.getElementById('payeeName');

const selPaymentMethod = document.getElementById('paymentMethod');
const selBankAccount = document.getElementById('bankAccountId');
const txtCheckNo = document.getElementById('checkNo');
const dtCheckDate = document.getElementById('checkDate');
const txtReferenceNo = document.getElementById('referenceNo');
const txtParticulars = document.getElementById('particulars');

const divSupplierField = document.getElementById('supplierField');
const divOtherPayeeField = document.getElementById('otherPayeeField');
const divBankAccountField = document.getElementById('bankAccountField');
const divCheckFields = document.getElementById('checkFields');

const tblCheckVoucherDetails = document.getElementById('accountingDetailsBody');

const btnAddAccount = document.getElementById('btnAddAccount');
const btnNewCheckVoucher = document.getElementById('btnNewCheckVoucher');
const btnSaveCheckVoucher = document.getElementById('btnSaveCheckVoucher');
const btnPostCheckVoucher = document.getElementById('btnPostCheckVoucher');
const btnCancelCheckVoucher = document.getElementById('btnCancelCheckVoucher');
const btnBackToCheckVouchers = document.getElementById('btnBackToCheckVouchers');

const lblTotalDebit = document.getElementById('totalDebit');
const lblTotalCredit = document.getElementById('totalCredit');
const lblBalanceDifference = document.getElementById('balanceDifference');

let accountSearchTimer = null;
let accountSearchSequence = 0;


document.addEventListener('DOMContentLoaded', () => {

  Atlas.select.init('#branchId');
  Atlas.select.init('#payeeType');
  Atlas.select.init('#supplierId');
  Atlas.select.init('#paymentMethod');
  Atlas.select.init('#bankAccountId');

  btnNewCheckVoucher?.addEventListener('click', () => Atlas.page.redirect('check-vouchers/create'));

  /*** back */
  btnBackToCheckVouchers?.addEventListener('click', () => Atlas.page.redirect(`check-vouchers`));

  if (!document.getElementById('checkVoucherForm')) {
    return;
  }

  /*** payee type */
  Atlas.select.onChange('#payeeType', updatePayeeFields);

  /*** payment method */
  Atlas.select.onChange('#paymentMethod', updatePaymentFields);

  /*** add account */
  btnAddAccount?.addEventListener('click', () => addDetailRow());

  /*** save */
  btnSaveCheckVoucher?.addEventListener('click', async () => {

    if (!validateCheckVoucher()) {
      return;
    }

    const checkVoucher = buildCheckVoucherPayload();
    btnSaveCheckVoucher.disabled = true;

    try {

      const result = await Atlas.ajax.post(
        'check-vouchers/save',
        checkVoucher
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      setTimeout(() => Atlas.page.redirect(`check-vouchers/edit/${Atlas.id.encode(result.data.check_voucher_id)}`), 1500);

    } finally {

      btnSaveCheckVoucher.disabled = false;
    }
  }
  );

  /*** post */
  btnPostCheckVoucher?.addEventListener('click', async () => {
    const form = document.getElementById('checkVoucherForm');
    const id = form?.dataset.id;

    if (!id) {
      Atlas.toast.warning('Please save the Check Voucher first.');
      return;
    }

    const totalDebit = Atlas.format.parseNumber(lblTotalDebit?.textContent || 0);
    const totalCredit = Atlas.format.parseNumber(lblTotalCredit?.textContent || 0);

    if (totalDebit <= 0 || totalCredit <= 0) {
      Atlas.toast.warning('Check Voucher total must be greater than zero.');
      return;
    }

    if (Math.abs(totalDebit - totalCredit) > 0.005) {
      Atlas.toast.warning('Debit and Credit totals must be equal before posting.');
      return;
    }

    const response = await Atlas.dialog.confirm(
      'Confirm Action',
      `<div class="text-brown text-center">
        <p>Once posted, it can no longer be edited.</p>
        <p class="font-weight-500 text-danger">Post this Check Voucher?</p>
      </div>`
    );

    if (!response) {
      return;
    }

    btnPostCheckVoucher.disabled = true;

    try {
      const result = await Atlas.ajax.post(`check-vouchers/post/${id}`, {});

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(`${result.data.cv_no} posted. Journal: ${result.data.journal_no}`);
      setTimeout(() => window.location.reload(), 1500);
    } finally {
      btnPostCheckVoucher.disabled = false;
    }
  });

  /*** cancel */
  btnCancelCheckVoucher?.addEventListener('click', async () => {
    const form = document.getElementById('checkVoucherForm');
    const id = form?.dataset.id;

    if (!id) {
      Atlas.toast.warning('Invalid Check Voucher.');
      return;
    }

    const reason = await Atlas.dialog.textarea({
      title: 'Cancel Check Voucher',
      html: `<div class="text-center text-danger">
              Cancelling this Check Voucher cannot be undone.
            </div>`,
      inputLabel: 'Cancellation Reason',
      inputPlaceholder: 'Enter cancellation reason...',
      required: true,
      requiredMessage: 'Cancellation reason is required.',
      maxlength: 500,
      confirmText: 'Cancel Check Voucher',
      cancelText: 'Back'
    });

    if (reason === null) {
      return;
    }

    btnCancelCheckVoucher.disabled = true;

    try {
      const result = await Atlas.ajax.post(
        `check-vouchers/cancel/${id}`,
        { reason }
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      if (result.data.reversal_journal_no) {
        Atlas.toast.success(
          `${result.data.cv_no} cancelled. Reversal Journal: ${result.data.reversal_journal_no}`
        );
      } else {
        Atlas.toast.success(`${result.data.cv_no} cancelled.`);
      }

      setTimeout(() => window.location.reload(), 1500);
    } finally {
      btnCancelCheckVoucher.disabled = false;
    }
  });

  /*** account search */
  document.addEventListener('input', e => {
    if (!e.target.classList.contains('cv-account-code') && !e.target.classList.contains('cv-account-name')) {
      return;
    }

    const row = e.target.closest('tr');

    /*** typed text no longer represents selected account */
    row.dataset.accountId = '';
    searchAccounts(row, e.target);
  });

  /*** debit / credit */
  document.addEventListener('input', e => {
    if (!e.target.classList.contains('cv-debit') && !e.target.classList.contains('cv-credit')) {
      return;
    }

    const row = e.target.closest('tr');
    const debit = row.querySelector('.cv-debit');
    const credit = row.querySelector('.cv-credit');

    if (e.target.classList.contains('cv-debit') && Atlas.format.parseNumber(debit.value) > 0) {
      credit.value = '';
    }

    if (e.target.classList.contains('cv-credit') && Atlas.format.parseNumber(credit.value) > 0) {
      debit.value = '';
    }

    calculateCheckVoucherTotals();
  });

  /*** select account */
  document.addEventListener('click', e => {
    const item = e.target.closest('.cv-account-suggestion');

    if (!item) {
      return;
    }

    const row = item.closest('tr');
    populateAccountRow(
      row,
      {
        id: item.dataset.id,
        account_code: item.dataset.accountCode,
        account_name: item.dataset.accountName
      }
    );

    hideAccountSuggestions();
  });

  /*** delete row */
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-delete-cv-row');

    if (!btn) {
      return;
    }

    removeDetailRow(btn.closest('tr'));
  });

  /*** close autocomplete when clicking elsewhere */
  document.addEventListener('click', e => {
    if (!e.target.closest('.cv-account-search')) {
      hideAccountSuggestions();
    }
  });

  updatePayeeFields();
  updatePaymentFields();
  initializeDetails();
  calculateCheckVoucherTotals();

});

const updatePayeeFields = () => {

  if (!selPayeeType) {
    return;
  }

  const isSupplier = selPayeeType.value === 'SUPPLIER';
  divSupplierField?.classList.toggle('d-none', !isSupplier);
  divOtherPayeeField?.classList.toggle('d-none', isSupplier
  );
};

const updatePaymentFields = () => {

  if (!selPaymentMethod) {
    return;
  }

  const paymentMethod = selPaymentMethod.value;

  const usesBank = paymentMethod === 'CHECK' || paymentMethod === 'BANK_TRANSFER';
  divBankAccountField?.classList.toggle('d-none', !usesBank);
  divCheckFields?.classList.toggle('d-none', paymentMethod !== 'CHECK');
};

const initializeDetails = () => {
  if (!tblCheckVoucherDetails) {
    return;
  }

  /*** read-only voucher */
  if (!btnAddAccount) {
    return;
  }

  let details = [];

  const source = document.getElementById('existingCheckVoucherDetails');

  if (source) {
    try {
      details = JSON.parse(source.textContent);
    } catch (e) {
      Atlas.toast.error('Unable to load Check Voucher details.');
    }
  }

  tblCheckVoucherDetails.innerHTML = '';

  if (details.length > 0) {

    details.forEach(detail => {
      addDetailRow(detail);
    });

  } else {

    addDetailRow();
  }
};

const addDetailRow = (detail = {}) => {
  if (!tblCheckVoucherDetails) {
    return;
  }

  tblCheckVoucherDetails.insertAdjacentHTML('beforeend', createDetailRow(detail));
};

const createDetailRow = (detail = {}) => {
  const accountId = detail.account_id || '';
  const accountCode = escapeHtml(detail.account_code || '');
  const accountName = escapeHtml(detail.account_name || '');
  const debit = detail.debit || 0;
  const credit = detail.credit || 0;
  const remarks = escapeHtml(detail.remarks || '');

  return `
    <tr data-account-id="${accountId}">
      <td>
        <div class="position-relative cv-account-search">
          <input type="text" class="form-control form-control-sm cv-account-code" value="${accountCode}" autocomplete="off" placeholder="Account Code">
          <div class="cv-account-suggestions list-group position-absolute w-100 d-none" style="z-index: 1050;"></div>
        </div>
      </td>
      <td>
        <div class="position-relative cv-account-search">
          <input type="text" class="form-control form-control-sm cv-account-name" value="${accountName}" autocomplete="off" placeholder="Account Description">
          <div class="cv-account-suggestions list-group position-absolute w-100 d-none" style="z-index: 1050;"></div>
        </div>
      </td>
      <td>
        <input type="number" class="form-control form-control-sm text-right cv-debit" step="0.01" min="0" value="${debit > 0 ? debit.toFixed(2) : ''}">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm text-right cv-credit" step="0.01" min="0" value="${credit > 0 ? credit.toFixed(2) : ''}">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm text-uppercase cv-detail-remarks" value="${remarks}">
      </td>
      <td class="text-center">
        <i class="fas fa-trash text-danger pointer btn-delete-cv-row" data-toggle="tooltip" title="Remove"></i>
      </td>
    </tr>
  `;
};

const searchAccounts = (row, input) => {
  clearTimeout(accountSearchTimer);
  const keyword = input.value.trim();

  hideAccountSuggestions();

  if (keyword.length < 2) {
    return;
  }

  accountSearchTimer = setTimeout(async () => {
    const sequence = ++accountSearchSequence;
    const result = await Atlas.ajax.get(
      'check_vouchers/search_accounts',
      {
        q: keyword
      }
    );

    /*** ignore an older response */
    if (sequence !== accountSearchSequence) {
      return;
    }

    if (!result.success) {
      return;
    }

    showAccountSuggestions(
      row,
      input,
      result.data || []
    );

  },
    300
  );
};

const showAccountSuggestions = (row, input, accounts) => {
  const container = input.closest('.cv-account-search').querySelector('.cv-account-suggestions');
  container.innerHTML = '';

  if (!accounts.length) {
    container.classList.add('d-none');
    return;
  }

  accounts.forEach(account => {
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action py-1 px-2 cv-account-suggestion';
    item.dataset.id = account.id;
    item.dataset.accountCode = account.account_code;
    item.dataset.accountName = account.account_name;
    item.innerHTML = `<span class="font-weight-500">
                          ${escapeHtml(account.account_code)}
                        </span>
                        <span class="ml-2">
                          ${escapeHtml(account.account_name)}
                        </span>
                      `;

    container.appendChild(item);
  });

  container.classList.remove('d-none');
};

const populateAccountRow = (row, account) => {
  row.dataset.accountId = account.id;
  row.querySelector('.cv-account-code').value = account.account_code;
  row.querySelector('.cv-account-name').value = account.account_name;
};

const hideAccountSuggestions = () => {
  document.querySelectorAll('.cv-account-suggestions').forEach(container => {
    container.classList.add('d-none');
    container.innerHTML = '';
  });
};

const removeDetailRow = row => {
  if (!tblCheckVoucherDetails) {
    return;
  }

  /*** always keep one entry row */
  if (tblCheckVoucherDetails.children.length === 1) {
    row.dataset.accountId = '';
    row.querySelector('.cv-account-code').value = '';
    row.querySelector('.cv-account-name').value = '';
    row.querySelector('.cv-debit').value = '';
    row.querySelector('.cv-credit').value = '';
    row.querySelector('.cv-detail-remarks').value = '';

  } else {
    row.remove();
  }

  calculateCheckVoucherTotals();

  setTimeout(() => tblCheckVoucherDetails.querySelector('.cv-account-code')?.focus(), 100);
};

const calculateCheckVoucherTotals = () => {
  if (!tblCheckVoucherDetails) {
    return;
  }

  let totalDebit = 0;
  let totalCredit = 0;

  tblCheckVoucherDetails.querySelectorAll('tr').forEach(row => {
    const debit = row.querySelector('.cv-debit');
    const credit = row.querySelector('.cv-credit');

    /*** read-only row */
    if (!debit || !credit) {
      const cells = row.querySelectorAll('td');

      if (cells.length >= 4) {
        totalDebit += Atlas.format.parseNumber(cells[2].textContent);
        totalCredit += Atlas.format.parseNumber(cells[3].textContent);
      }
      return;
    }

    totalDebit += Atlas.format.parseNumber(debit.value);
    totalCredit += Atlas.format.parseNumber(credit.value);
  });

  if (lblTotalDebit) {
    lblTotalDebit.textContent = Atlas.format.amount(totalDebit);
  }

  if (lblTotalCredit) {
    lblTotalCredit.textContent = Atlas.format.amount(totalCredit);
  }

  if (lblBalanceDifference) {
    lblBalanceDifference.textContent = Atlas.format.amount(Math.abs(totalDebit - totalCredit));
  }
};

const escapeHtml = value => {
  const element = document.createElement('div');
  element.textContent = String(value ?? '');
  return element.innerHTML;
};

const buildCheckVoucherPayload = () => {
  const form = document.getElementById('checkVoucherForm');

  const checkVoucher = {
    id: form.dataset.id || null,
    voucher_date: dtVoucherDate.value,
    branch_id: selBranch.value,
    payee_type: selPayeeType.value,
    supplier_id: selPayeeType.value === 'SUPPLIER' ? selSupplier.value : null,
    payee_name: selPayeeType.value === 'OTHER' ? txtPayeeName.value.trim() : '',
    payment_method: selPaymentMethod.value,
    bank_account_id: (selPaymentMethod.value === 'CHECK' || selPaymentMethod.value === 'BANK_TRANSFER') ? selBankAccount.value : null,
    check_no: selPaymentMethod.value === 'CHECK' ? txtCheckNo.value.trim() : '',
    check_date: selPaymentMethod.value === 'CHECK' ? dtCheckDate.value : null,
    reference_no: txtReferenceNo.value.trim(),
    particulars: txtParticulars.value.trim(),
    details: []
  };

  tblCheckVoucherDetails.querySelectorAll('tr').forEach(row => {
    if (!row.dataset.accountId) {
      return;
    }

    checkVoucher.details.push({
      account_id: Atlas.format.integer(row.dataset.accountId),
      debit: Atlas.format.parseNumber(row.querySelector('.cv-debit').value || 0),
      credit: Atlas.format.parseNumber(row.querySelector('.cv-credit').value || 0),
      remarks: row.querySelector('.cv-detail-remarks').value.trim()
    });
  });

  return checkVoucher;
};

const validateCheckVoucher = () => {
  if (!dtVoucherDate.value) {
    Atlas.toast.warning('Please enter the Voucher Date.');
    dtVoucherDate.focus();
    return false;
  }

  if (!selBranch.value) {
    Atlas.toast.warning('Please select a Branch.');
    selBranch.focus();
    return false;
  }

  if (!selPayeeType.value) {
    Atlas.toast.warning('Please select a Payee Type.');
    selPayeeType.focus();
    return false;
  }

  if (selPayeeType.value === 'SUPPLIER' && !selSupplier.value) {
    Atlas.toast.warning('Please select a Supplier.');
    selSupplier.focus();
    return false;
  }

  if (selPayeeType.value === 'OTHER' && !txtPayeeName.value.trim()) {
    Atlas.toast.warning('Please enter the Payee Name.');
    txtPayeeName.focus();
    return false;
  }

  if (!selPaymentMethod.value) {
    Atlas.toast.warning('Please select a Payment Method.');
    selPaymentMethod.focus();
    return false;
  }

  if ((selPaymentMethod.value === 'CHECK' || selPaymentMethod.value === 'BANK_TRANSFER') && !selBankAccount.value) {
    Atlas.toast.warning('Please select a Bank Account.');
    selBankAccount.focus();
    return false;
  }

  let hasEntry = false;

  const rows = tblCheckVoucherDetails.querySelectorAll('tr');

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const code = row.querySelector('.cv-account-code');
    const name = row.querySelector('.cv-account-name');
    const debitInput = row.querySelector('.cv-debit');
    const creditInput = row.querySelector('.cv-credit');
    const hasTypedAccount = code.value.trim() !== '' || name.value.trim() !== '';
    const debit = Atlas.format.parseNumber(debitInput.value || 0);
    const credit = Atlas.format.parseNumber(creditInput.value || 0);
    const hasAmount = debit > 0 || credit > 0;

    /*** completely blank row */
    if (!hasTypedAccount && !hasAmount) {
      continue;
    }

    if (!row.dataset.accountId) {
      Atlas.toast.warning(`Please select a valid Account on row ${i + 1}.`);
      code.focus();
      return false;
    }

    if (!((debit > 0 && credit === 0) || (credit > 0 && debit === 0))) {
      Atlas.toast.warning(`Please enter either a Debit or Credit amount on row ${i + 1}.`);
      debitInput.focus();
      return false;
    }

    hasEntry = true;
  }

  if (!hasEntry) {
    Atlas.toast.warning('Please add at least one accounting entry.');
    tblCheckVoucherDetails.querySelector('.cv-account-code')?.focus();
    return false;
  }

  return true;
};