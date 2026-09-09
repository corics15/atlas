const btnNewCustomerPayment = document.getElementById('btnNewCustomerPayment');
const btnEditCustomerPayment = document.getElementById('btnEditCustomerPayment');
const btnPostCustomerPayment = document.getElementById('btnPostCustomerPayment');
const btnCancelCustomerPayment = document.getElementById('btnCancelCustomerPayment');
const btnRefreshCustomerPayment = document.getElementById('btnRefreshCustomerPayment');
const btnSaveCustomerPayment = document.getElementById('btnSaveCustomerPayment');

const tblOutstandingInvoices = document.getElementById('tblOutstandingInvoices');
const tblAvailableCredits = document.getElementById('tblAvailableCredits');
const tblCustomerCreditRefunds = document.getElementById('tblCustomerCreditRefunds');

const hidCustomerPaymentId = document.getElementById('hidCustomerPaymentId');

const selCustomer = document.getElementById('selCustomer');
const selBranch = document.getElementById('selBranch');
const selPaymentMethod = document.getElementById('selPaymentMethod');
const selCollectedBy = document.getElementById('selCollectedBy');

const txtReferenceNo = document.getElementById('txtReferenceNo');
const txtAmountReceived = document.getElementById('txtAmountReceived');
const txtCustomerPaymentRemarks = document.getElementById('txtCustomerPaymentRemarks');
const dtPaymentDate = document.getElementById('dtPaymentDate');

let isDirty = false;
let isLoading = true;

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.table.init({
    checkbox: '.chkCustomerPayment',
    selectAll: '#chkSelectAllCustomerPayments',
  });

  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selBranch');
  Atlas.select.init('#selPaymentMethod');
  Atlas.select.init('#selCollectedBy');

  /*** default to user branch */
  if (window.branchId)
    $('#selBranch').val(branchId).trigger('change');

  /*** dirty tracking, clear validation */
  dtPaymentDate?.addEventListener('change', () => clearInvalid(dtPaymentDate));
  txtAmountReceived?.addEventListener('input', () => clearInvalid(txtAmountReceived));

  Atlas.select.onChange('#selCustomer', () => {
    clearInvalid(selCustomer);
    markDirty();
  });
  Atlas.select.onChange('#selBranch', () => {
    clearInvalid(selBranch);
    markDirty();
  });
  Atlas.select.onChange('#selPaymentMethod', () => {
    clearInvalid(selPaymentMethod);
    markDirty();
  });

  document.addEventListener('input', (e) => {
    if (e.target.id === 'txtAmountReceived') {
      calculatePaymentAllocationTotals();
      clearInvalid(txtAmountReceived);
      markDirty();
    }

    if (e.target.id === 'txtReferenceNo') {
      markDirty();
    }

    if (e.target.classList.contains('txtApplyAmount')) {
      validateApplyAmount(e.target);
      calculatePaymentAllocationTotals();
      markDirty();
    }
  });
  /*** end dirty tracking */

  document.addEventListener('blur', (e) => {
    if (e.target.classList.contains('txtApplyAmount')) {
      const amount = Atlas.format.parseNumber(e.target.value || 0);

      e.target.value = amount.toFixed(2);
    }
  }, true);

  /*** new */
  btnNewCustomerPayment?.addEventListener('click', () => {
    Atlas.page.redirect('customer-payments/create');
  });

  /*** edit */
  btnEditCustomerPayment?.addEventListener('click', () => {
    const id = getSelectedCustomerPaymentId();

    if (!id) {
      return;
    }

    Atlas.page.redirect(`customer-payments/edit/${Atlas.id.encode(id)}`);
  });

  /*** save */
  btnSaveCustomerPayment?.addEventListener('click', async () => {

    if (!validateCustomerPayment()) {
      return;
    }

    const customerPayment = {
      id: hidCustomerPaymentId?.value ?? '',
      payment_date: dtPaymentDate.value,
      customer_id: Atlas.format.parseNumber(selCustomer.value),
      branch_id: Atlas.format.parseNumber(selBranch.value),
      amount_received: Atlas.format.parseNumber(txtAmountReceived.value),
      payment_method: selPaymentMethod.value,
      reference_no: txtReferenceNo.value,
      collected_by_salesman_id: Atlas.format.parseNumber(selCollectedBy.value || 0),
      remarks: txtCustomerPaymentRemarks.value,
      allocations: []
    };

    document.querySelectorAll('#tblOutstandingInvoices tbody tr[data-sales-invoice-id]').forEach(row => {
      const input = row.querySelector('.txtApplyAmount');

      if (!input) {
        return;
      }

      const amountApplied = Atlas.format.parseNumber(input.value || 0);
      if (amountApplied <= 0) {
        return;
      }

      customerPayment.allocations.push({
        sales_invoice_id: Atlas.format.parseNumber(row.dataset.salesInvoiceId),
        amount_applied: amountApplied
      });
    });

    btnSaveCustomerPayment.disabled = true;

    try {

      const result = await Atlas.ajax.post(
        'customer-payments/save',
        customerPayment
      );

      if (!result.success) {
        Atlas.toast.error(result.message);
        return;
      }

      Atlas.toast.success(result.message);
      isDirty = false;
      setTimeout(() => Atlas.page.redirect(`customer-payments/edit/${Atlas.id.encode(result.data.customer_payment_id)}`), 1500);

    } finally {
      btnSaveCustomerPayment.disabled = false;
    }

  });

  /*** post */
  btnPostCustomerPayment?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.customerPaymentId === 0) {
        Atlas.toast.warning('New Customer Payment, not saved yet.');
        return;
      } else if (window.customerPaymentId) {
        ids = [window.customerPaymentId];
      } else {
        Atlas.toast.warning('Please select at least one Customer Payment.');
        return;
      }
    }

    const confirmed = await Atlas.dialog.confirm(
      'Confirm Action',
      'Post Customer Payment?'
    );

    if (!confirmed) {
      return;
    }

    btnPostCustomerPayment.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'customer-payments/post',
        {
          ids: ids
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);
      isDirty = false;

      setTimeout(() => Atlas.page.refresh(), 1500);
    } finally {
      btnPostCustomerPayment.disabled = false;
    }
  });

  /*** cancel */
  btnCancelCustomerPayment?.addEventListener('click', async () => {
    let ids = Atlas.table.selectedIds();

    if (!ids || ids.length === 0) {
      if (window.customerPaymentId === 0) {
        Atlas.toast.warning('New Customer Payment, not saved yet.');
        return;
      } else if (window.customerPaymentId) {
        ids = [window.customerPaymentId];
      } else {
        Atlas.toast.warning('Please select at least one Customer Payment.');
        return;
      }
    }

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: `Cancel ${ids.length} Customer Payment(s)?`,
      text: 'Please provide the reason for cancellation.',
      inputPlaceholder: 'Enter cancellation reason...',
      required: false,
      confirmText: 'Confirm Cancellation'
    });

    if (reason === null) {
      return;
    }

    btnCancelCustomerPayment.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'customer-payments/cancel',
        {
          ids: ids,
          cancel_reason: reason
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);
      isDirty = false;

      setTimeout(() => Atlas.page.refresh(), 1200);

    } finally {
      btnCancelCustomerPayment.disabled = false;
    }
  });

  /*** customer change event */
  Atlas.select.onChange('#selCustomer', async () => {
    window.customerPaymentAllocations = [];

    await loadOutstandingInvoices();
    await loadAvailableCredits();
    await loadPaymentCreditRefunds();

    markDirty();
  });

  /*** refresh */
  btnRefreshCustomerPayment?.addEventListener(
    'click',
    () => Atlas.page.redirect('customer-payments')
  );

  /*** load existing payment allocations */
  if (window.customerPaymentId > 0 && selCustomer?.value) {
    await loadOutstandingInvoices();
    await loadAvailableCredits();
    await loadPaymentCreditRefunds();
  }

  /*** apply customer credit */
  tblAvailableCredits?.addEventListener('click', async e => {
    const button = e.target.closest('.btnApplyCustomerCredit');
    if (!button) return;

    const row = button.closest('tr[data-credit-type]');
    if (!row) return;

    const type = row.dataset.creditType;
    const creditId = parseInt(row.dataset.creditId, 10);
    const creditBalance = Atlas.format.parseNumber(row.dataset.creditBalance || 0);
    const select = row.querySelector('.selCreditTargetInvoice');
    const input = row.querySelector('.txtCreditApplyAmount');
    const salesInvoiceId = parseInt(select?.value || 0, 10);
    const amount = Atlas.format.parseNumber(input?.value || 0);

    if (!salesInvoiceId) {
      Atlas.toast.warning('Please select the Sales Invoice to apply the credit to.');
      select?.focus();
      return;
    }

    const selectedOption = select.options[select.selectedIndex];
    const invoiceBalance = Atlas.format.parseNumber(selectedOption?.dataset.balance || 0);

    if (amount <= 0) {
      Atlas.toast.warning('Credit amount must be greater than zero.');
      input?.focus();
      return;
    }

    if (amount > creditBalance) {
      Atlas.toast.warning('Amount cannot exceed the available customer credit.');
      input?.focus();
      return;
    }

    if (amount > invoiceBalance) {
      Atlas.toast.warning('Amount cannot exceed the Sales Invoice balance.');
      input?.focus();
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Confirm Credit Application',
      `Apply <span class="text-orange">${Atlas.format.amount(amount)}</span> from <span class="text-info">${escapeHtml(row.children[0].textContent.trim())}</span> to <span class="text-teal">${selectedOption.textContent.split(' - ')[0]}</span>?`
    );

    if (!confirmed) return;

    button.disabled = true;

    try {
      const endpoint = type === 'CP'
        ? 'customer-payments/apply-payment-credit'
        : 'customer-payments/apply-credit';

      const payload = {
        sales_invoice_id: salesInvoiceId,
        amount: amount,
        remarks: null
      };

      if (type === 'CP') {
        payload.customer_payment_id = creditId;
      } else {
        payload.credit_memo_id = creditId;
      }

      const response = await Atlas.ajax.post(endpoint, payload);

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);

      await loadOutstandingInvoices();
      await loadAvailableCredits();

    } finally {
      button.disabled = false;
    }
  });

  /*** btnRefundCustomerCredit: refund customer payment credit */
  tblAvailableCredits?.addEventListener('click', async e => {
    const button = e.target.closest('.btnRefundCustomerCredit');
    if (!button) return;

    const row = button.closest('tr[data-credit-type="CP"]');
    if (!row) return;

    const customerPaymentId = parseInt(row.dataset.creditId, 10);
    const creditBalance = Atlas.format.parseNumber(row.dataset.creditBalance || 0);
    const paymentNo = row.children[0]?.textContent.trim() || '';

    const amount = await Atlas.dialog.number({
      icon: 'warning',
      title: 'Refund Customer Credit',
      html: `
        <div class="text-center">
          <div>${escapeHtml(paymentNo)}</div>
          <div class="text-brown mt-1">
            Available Credit:
            <span class="font-weight-500 text-info">${Atlas.format.amount(creditBalance)}</span>
          </div>
        </div>
      `,
      inputPlaceholder: 'Enter refund amount...',
      min: 0.01,
      step: '0.01',
      confirmText: 'Continue'
    });

    if (amount === null) return;

    const refundAmount = amount;

    if (refundAmount <= 0) {
      Atlas.toast.warning('Refund amount must be greater than zero.');
      return;
    }

    if (refundAmount > creditBalance) {
      Atlas.toast.warning('Refund amount cannot exceed the available customer credit.');
      return;
    }

    const details = await Atlas.dialog.details({
      title: 'Refund Details',
      firstLabel: 'Reference No.',
      firstPlaceholder: 'Optional reference no.',
      secondLabel: 'Remarks',
      secondPlaceholder: 'Optional remarks...',
      confirmText: 'Continue'
    });

    if (!details) return;

    const confirmed = await Atlas.dialog.confirm(
      'Confirm Customer Credit Refund',
      `Refund <span class="text-info">${Atlas.format.amount(refundAmount)}</span> from <span class="text-brown">${escapeHtml(paymentNo)}</span>?`
    );

    if (!confirmed) return;

    button.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'customer-payments/refund-payment-credit',
        {
          customer_payment_id: customerPaymentId,
          refund_date: new Date().toLocaleDateString('en-CA'),
          amount: refundAmount,
          reference_no: details.first,
          remarks: details.second,
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);

      await loadAvailableCredits();

    } finally {
      button.disabled = false;
    }
  });

  /*** btnCancelCreditRefund: cancel customer credit refund */
  tblCustomerCreditRefunds?.addEventListener('click', async e => {
    const button = e.target.closest('.btnCancelCreditRefund');
    if (!button) return;

    const row = button.closest('tr[data-refund-id]');
    if (!row) return;

    const refundId = parseInt(row.dataset.refundId, 10);
    const sourceNo = row.children[1]?.textContent.trim() || '';
    const amount = Atlas.format.parseNumber(row.children[3]?.textContent || 0);

    const reason = await Atlas.dialog.textarea({
      icon: 'warning',
      title: 'Cancel Customer Credit Refund?',
      html: `<div class="text-center">
                <div class="text-teal">${escapeHtml(sourceNo)}</div>
                <div class="text-brown mt-1">
                  Refund Amount:
                  <span class="font-weight-500 text-info">${Atlas.format.amount(amount)}</span>
                </div>
              </div>
            `,
      inputPlaceholder: 'Enter cancellation reason, for audit purposes, this field is required....',
      required: true,
      confirmText: 'Cancel Refund'
    });

    if (reason === null) return;

    button.disabled = true;

    try {
      const response = await Atlas.ajax.post(
        'customer-payments/cancel-payment-credit-refund',
        {
          refund_id: refundId,
          cancel_reason: reason
        }
      );

      if (!response.success) {
        Atlas.toast.error(response.message);
        return;
      }

      Atlas.toast.success(response.message);

      await loadAvailableCredits();
      await loadPaymentCreditRefunds();
      await loadOutstandingInvoices();

    } finally {
      button.disabled = false;
    }
  });

  isDirty = false;
  isLoading = false;

});

window.addEventListener('beforeunload', e => {
  if (!isDirty) {
    return;
  }

  e.preventDefault();
});

const getSelectedCustomerPaymentId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning('Please select a Customer Payment.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one Customer Payment.');
    return null;
  }

  return checked[0].value;
};

const loadOutstandingInvoices = async () => {
  if (!tblOutstandingInvoices) {
    return;
  }

  const tbody = tblOutstandingInvoices.querySelector('tbody');
  const customerId = Atlas.format.parseNumber(selCustomer?.value || 0);

  if (!customerId) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">
          Select a customer to view outstanding invoices.
        </td>
      </tr>
    `;

    return;
  }

  const response = await Atlas.ajax.post(
    'customer-payments/outstanding-invoices',
    {
      customer_id: customerId
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  const invoices = response.data?.invoices || [];

  if (invoices.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">
          No outstanding invoices found.
        </td>
      </tr>
    `;

    return;
  }

  const savedAllocations = window.customerPaymentAllocations || [];
  tbody.innerHTML = invoices.map(invoice => {

    const savedAllocation = savedAllocations.find(
      allocation =>
        Number(allocation.sales_invoice_id) ===
        Number(invoice.id)
    );

    const amountApplied = savedAllocation ? Number(savedAllocation.amount_applied) : 0;
    const amountValue = amountApplied === 0 ? '' : `value="${amountApplied.toFixed(2)}"`;
    const creditAmount = Atlas.format.parseNumber(invoice.amount_credited || 0) + Atlas.format.parseNumber(invoice.credit_applied || 0);

    return `
    <tr
      data-sales-invoice-id="${invoice.id}"
      data-balance="${invoice.balance}">
      <td class="text-center">${escapeHtml(invoice.si_no)}</td>
      <td class="text-center">${Atlas.format.formatDate(escapeHtml(invoice.invoice_date))}</td>
      <td class="text-right">${Atlas.format.amount(invoice.total_amount)}</td>
      <td class="text-right">${Atlas.format.amount(invoice.amount_paid)}</td>
      <td class="text-right">${Atlas.format.amount(creditAmount)}</td>
      <td class="text-right">${Atlas.format.amount(invoice.balance)}</td>
      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-right txtApplyAmount" ${amountValue} placeholder="0.00"></td>
    </tr>
  `;

  }).join('');

  calculatePaymentAllocationTotals();
};

const loadAvailableCredits = async () => {
  if (!tblAvailableCredits) return;

  const tbody = tblAvailableCredits.querySelector('tbody');
  const customerId = Atlas.format.parseNumber(selCustomer?.value || 0);

  if (!customerId) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">
          Select a customer to view available credit.
        </td>
      </tr>
    `;
    return;
  }

  const response = await Atlas.ajax.post(
    'customer-payments/available-credits',
    {
      customer_id: customerId
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  const creditMemos = (response.data?.credit_memos || []).map(credit => ({
    type: 'CM',
    id: credit.id,
    no: credit.cm_no,
    date: credit.credit_memo_date,
    source: credit.si_no,
    sourceId: credit.sales_invoice_id,
    balance: credit.balance
  }));

  const paymentCredits = (response.data?.payment_credits || []).map(credit => ({
    type: 'CP',
    id: credit.id,
    no: credit.payment_no,
    date: credit.payment_date,
    source: 'Unapplied Payment',
    balance: credit.balance
  }));

  const credits = [...creditMemos, ...paymentCredits];

  if (credits.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">
          No available customer credit found.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = credits.map(credit => `
    <tr
      data-credit-type="${credit.type}"
      data-credit-id="${credit.id}"
      data-credit-balance="${credit.balance}">
      <td class="text-center">
          ${credit.type === 'CM'
      ? `<a href="${Atlas.config.baseUrl}credit-memos/view/${Atlas.id.encode(credit.id)}"
                class="font-weight-500 text-olive"
                target="_blank">
                <i class="fas fa-external-link-alt fa-xs mr-1"></i>${escapeHtml(credit.no)}
              </a>`
      : escapeHtml(credit.no)
    }
      </td>
      <td class="text-center">${Atlas.format.formatDate(escapeHtml(credit.date))}</td>
      <td class="text-center">
        ${credit.type === 'CM'
      ? `<a href="${Atlas.config.baseUrl}sales-invoices/edit/${Atlas.id.encode(credit.sourceId)}"
                class="font-weight-500 text-olive"
                target="_blank">
              <i class="fas fa-external-link-alt fa-xs mr-1"></i>${escapeHtml(credit.source)}
            </a>`
      : escapeHtml(credit.source)
    }
      </td>
      <td class="text-right">${Atlas.format.amount(credit.balance)}</td>
      <td>
        <select class="form-control form-control-sm custom-select selCreditTargetInvoice">
          <option value="">Select SI</option>
        </select>
      </td>
      <td>
        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right txtCreditApplyAmount" placeholder="0.00">
      </td>
      <td class="text-center text-nowrap">
        <button type="button" class="btn btn-sm btn-link btnApplyCustomerCredit font-sm" data-toggle="tooltip" title="Apply Credit to SI">
          <i class="fas fa-check-circle mr-1"></i>Apply
        </button>
        ${credit.type === 'CP' ? `
          <button type="button" class="btn btn-sm btn-link text-danger btnRefundCustomerCredit font-sm" data-toggle="tooltip" title="Refund available credit back to Customer">
            <i class="fas fa-undo-alt mr-1"></i>Refund
          </button>
        ` : ''}
      </td>
    </tr>
  `).join('');

  /*** populate select on Available Customer Credit */
  const invoiceRows = document.querySelectorAll('#tblOutstandingInvoices tbody tr[data-sales-invoice-id]');
  tbody.querySelectorAll('.selCreditTargetInvoice').forEach(select => {
    invoiceRows.forEach(row => {
      const invoiceId = row.dataset.salesInvoiceId;
      const invoiceNo = row.children[0]?.textContent.trim() || '';
      const balance = Atlas.format.parseNumber(row.dataset.balance || 0);

      if (!invoiceId || balance <= 0) return;

      const option = document.createElement('option');
      option.value = invoiceId;
      option.textContent = `${invoiceNo} - ${Atlas.format.amount(balance)}`;
      option.dataset.balance = balance;
      select.appendChild(option);
    });
  });

  /*** re-initialize tooltips */
  Atlas.ui.init();
};

const loadPaymentCreditRefunds = async () => {
  if (!tblCustomerCreditRefunds) return;

  const tbody = tblCustomerCreditRefunds.querySelector('tbody');
  const customerId = Atlas.format.parseNumber(selCustomer?.value || 0);

  if (!customerId) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">Select a customer to view credit refunds.</td>
      </tr>
    `;
    return;
  }

  const response = await Atlas.ajax.post(
    'customer-payments/payment-credit-refunds',
    {
      customer_id: customerId
    }
  );

  if (!response.success) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-danger py-3">${escapeHtml(response.message)}</td>
      </tr>
    `;
    return;
  }

  const refunds = response.data || [];

  if (!refunds.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted py-3">No customer credit refunds found.</td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = refunds.map(refund => {
    const cancelled = refund.status === 'CANCELLED';

    return `
      <tr data-refund-id="${refund.id}">
        <td class="text-center">${escapeHtml(Atlas.format.formatDate(refund.refund_date) || '')}</td>
        <td class="text-center">${escapeHtml(refund.source_no || '')}</td>
        <td class="text-center">${escapeHtml(refund.reference_no || '')}</td>
        <td class="text-right">${Atlas.format.amount(refund.amount)}</td>
        <td>${escapeHtml(refund.remarks || '')}</td>
        <td class="text-center">
          <span class="badge ${refund.status === 'POSTED' ? 'badge-success' : 'badge-danger'}">
            ${escapeHtml(refund.status || '')}
          </span>
        </td>
        <td class="text-center">
          ${cancelled ? '' : `
            <button type="button" class="btn btn-sm btn-link font-sm btnCancelCreditRefund" title="Cancel refund amount" data-toggle="tooltip">
              <i class="fas fa-ban mr-1"></i>Cancel
            </button>
          `}
        </td>
      </tr>
    `;
  }).join('');

  /*** re-initialize tooltips */
  Atlas.ui.init();
};

const validateApplyAmount = input => {
  const row = input.closest('tr');
  if (!row) {
    return;
  }

  const balance = Math.round(Atlas.format.parseNumber(row.dataset.balance || 0) * 100) / 100;
  let amount = Math.round(Atlas.format.parseNumber(input.value || 0) * 100) / 100;
  if (amount < 0) {
    amount = 0;
    input.value = '0.00';
    return;
  }

  if (amount > balance) {
    input.value = balance.toFixed(2);
    Atlas.toast.warning('Applied amount cannot exceed the invoice balance.');
  }
};

const calculatePaymentAllocationTotals = () => {
  const amountReceived = Atlas.format.parseNumber(txtAmountReceived?.value || 0);
  let amountApplied = 0;

  document.querySelectorAll('.txtApplyAmount').forEach(input => {
    amountApplied += Atlas.format.parseNumber(input.value || 0);
  });

  const unapplied = amountReceived - amountApplied;

  document.getElementById('cpAmountReceived').textContent = Atlas.format.amount(amountReceived);
  document.getElementById('cpAmountApplied').textContent = Atlas.format.amount(amountApplied);
  document.getElementById('cpAmountUnapplied').textContent = Atlas.format.amount(unapplied);
};

const validateCustomerPayment = () => {
  const customerId = Atlas.format.parseNumber(selCustomer?.value || 0);
  const branchId = Atlas.format.parseNumber(selBranch?.value || 0);
  const amountReceived = Atlas.format.parseNumber(txtAmountReceived?.value || 0);
  const paymentMethod = selPaymentMethod?.value || '';

  clearInvalid(dtPaymentDate);
  clearInvalid(selCustomer);
  clearInvalid(selBranch);
  clearInvalid(txtAmountReceived);
  clearInvalid(selPaymentMethod);

  if (!dtPaymentDate?.value) {
    setInvalid(dtPaymentDate);
    Atlas.toast.warning('Payment Date is required.');
    dtPaymentDate?.focus();
    return false;
  }

  if (!customerId) {
    setInvalid(selCustomer);
    Atlas.toast.warning('Please select a Customer.');
    return false;
  }

  if (!branchId) {
    setInvalid(selBranch);
    Atlas.toast.warning('Please select a Branch.');
    return false;
  }

  if (amountReceived <= 0) {
    setInvalid(txtAmountReceived);
    Atlas.toast.warning('Amount Received must be greater than zero.');
    txtAmountReceived?.focus();
    return false;
  }

  if (!paymentMethod) {
    setInvalid(selPaymentMethod);
    Atlas.toast.warning('Please select a Payment Method.');
    return false;
  }

  let totalApplied = 0;
  let valid = true;

  document.querySelectorAll('#tblOutstandingInvoices tbody tr[data-sales-invoice-id]').forEach(row => {
    if (!valid) {
      return;
    }

    const input = row.querySelector('.txtApplyAmount');
    if (!input) {
      return;
    }

    const amountApplied = Atlas.format.parseNumber(input.value || 0);
    const balance = Atlas.format.parseNumber(row.dataset.balance || 0);

    if (amountApplied < 0) {
      Atlas.toast.warning('Applied amount cannot be negative.');
      input.focus();
      valid = false;
      return;
    }

    if (amountApplied > balance) {
      Atlas.toast.warning('Applied amount cannot exceed the invoice balance.');
      input.focus();
      valid = false;
      return;
    }

    totalApplied += amountApplied;
  });

  if (!valid) {
    return false;
  }

  if (totalApplied > amountReceived) {
    Atlas.toast.warning('Total applied amount cannot exceed the amount received.');
    return false;
  }

  return true;
};

const setInvalid = control => {
  if (!control) return;

  control.classList.add('is-invalid');

  if (control.tagName === 'SELECT') {
    const select2 = $(control).data('select2');

    if (select2?.$selection) {
      select2.$selection.css('border-color', '#dc3545');
    }
  }
};

const clearInvalid = control => {
  if (!control) return;

  control.classList.remove('is-invalid');

  if (control.tagName === 'SELECT') {
    const select2 = $(control).data('select2');

    if (select2?.$selection) {
      select2.$selection.css('border-color', '');
    }
  }
};

const escapeHtml = value => {
  const div = document.createElement('div');
  div.textContent = value ?? '';

  return div.innerHTML;
};

const markDirty = () => {
  if (isLoading) {
    return;
  }

  isDirty = true;
};