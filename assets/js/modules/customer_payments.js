const btnNewCustomerPayment = document.getElementById('btnNewCustomerPayment');
const btnEditCustomerPayment = document.getElementById('btnEditCustomerPayment');
const btnPostCustomerPayment = document.getElementById('btnPostCustomerPayment');
const btnCancelCustomerPayment = document.getElementById('btnCancelCustomerPayment');
const btnRefreshCustomerPayment = document.getElementById('btnRefreshCustomerPayment');
const selCustomer = document.getElementById('selCustomer');
const tblOutstandingInvoices = document.getElementById('tblOutstandingInvoices');
const txtAmountReceived = document.getElementById('txtAmountReceived');
const btnSaveCustomerPayment = document.getElementById('btnSaveCustomerPayment');
const hidCustomerPaymentId = document.getElementById('hidCustomerPaymentId');
const selBranch = document.getElementById('selBranch');
const selPaymentMethod = document.getElementById('selPaymentMethod');
const selCollectedBy = document.getElementById('selCollectedBy');
const txtReferenceNo = document.getElementById('txtReferenceNo');
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

  /*** default to main branch */
  $('#selBranch').val(1000).trigger('change');
  /*** dirty tracking */
  Atlas.select.onChange('#selBranch', () => markDirty());
  Atlas.select.onChange('#selPaymentMethod', () => markDirty());
  Atlas.select.onChange('#selCollectedBy', () => markDirty());
  document.addEventListener('input', (e) => {
    if (e.target.id === 'txtAmountReceived') {
      calculatePaymentAllocationTotals();
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

  /*** customer */
  Atlas.select.onChange('#selCustomer', async () => {
    await loadOutstandingInvoices();
    markDirty();
  });

  /*** refresh */
  btnRefreshCustomerPayment?.addEventListener(
    'click',
    () => Atlas.page.redirect('customer-payments')
  );

  /*** load existing payment allocations */
  if (
    window.customerPaymentId > 0 && selCustomer?.value
  ) {
    await loadOutstandingInvoices();
  }

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
        <td colspan="6" class="text-center text-muted py-3">
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
        <td colspan="6" class="text-center text-muted py-3">
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

    return `
    <tr
      data-sales-invoice-id="${invoice.id}"
      data-balance="${invoice.balance}">
      <td class="text-center">${escapeHtml(invoice.si_no)}</td>
      <td class="text-center">${Atlas.format.formatDate(escapeHtml(invoice.invoice_date))}</td>
      <td class="text-right">${Atlas.format.amount(invoice.total_amount)}</td>
      <td class="text-right">${Atlas.format.amount(invoice.amount_paid)}</td>
      <td class="text-right">${Atlas.format.amount(invoice.balance)}</td>
      <td>
        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right txtApplyAmount" ${amountValue} placeholder="0.00">
      </td>
    </tr>
  `;

  }).join('');

  calculatePaymentAllocationTotals();
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

  if (!dtPaymentDate?.value) {
    Atlas.toast.warning('Payment Date is required.');
    dtPaymentDate?.focus();
    return false;
  }

  if (!customerId) {
    Atlas.toast.warning('Please select a Customer.');
    return false;
  }

  if (!branchId) {
    Atlas.toast.warning('Please select a Branch.');
    return false;
  }

  if (amountReceived <= 0) {
    Atlas.toast.warning('Amount Received must be greater than zero.');

    txtAmountReceived?.focus();
    return false;
  }

  if (!paymentMethod) {
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