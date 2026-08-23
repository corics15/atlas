const selCustomer = document.getElementById('selCustomer');
const tblCustomerLedger = document.getElementById('tblCustomerLedger');
const clOpeningBalance = document.getElementById('clOpeningBalance');
const clTotalInvoiced = document.getElementById('clTotalInvoiced');
const clTotalPaid = document.getElementById('clTotalPaid');
const clCurrentBalance = document.getElementById('clCurrentBalance');
const dtDateFrom = document.getElementById('dtDateFrom');
const dtDateTo = document.getElementById('dtDateTo');

document.addEventListener('DOMContentLoaded', async () => {

  Atlas.select.init('#selCustomer');

  Atlas.select.onChange('#selCustomer', async () => {
    await loadCustomerLedger();
  });

  dtDateFrom?.addEventListener('change', async () => {
    await loadCustomerLedger();
  });

  dtDateTo?.addEventListener('change', async () => {
    await loadCustomerLedger();
  });

});

const loadCustomerLedger = async () => {
  if (!tblCustomerLedger) {
    return;
  }

  const tbody = tblCustomerLedger.querySelector('tbody');
  const customerId = Atlas.format.parseNumber(selCustomer?.value || 0);

  if (!customerId) {
    clOpeningBalance.textContent = '0.00';
    clTotalInvoiced.textContent = '0.00';
    clTotalPaid.textContent = '0.00';
    clCurrentBalance.textContent = '0.00';

    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center text-muted py-3">
          Select a customer to view ledger transactions.
        </td>
      </tr>
    `;

    return;
  }

  /*** check for date range */
  if (dtDateFrom?.value && dtDateTo?.value && dtDateFrom.value > dtDateTo.value) {
    Atlas.toast.warning('Date From cannot be later than Date To.');
    return;
  }

  const response = await Atlas.ajax.post(
    'customer-ledger/ledger',
    {
      customer_id: customerId,
      date_from: dtDateFrom?.value || '',
      date_to: dtDateTo?.value || ''
    }
  );

  if (!response.success) {
    Atlas.toast.error(response.message);
    return;
  }

  const openingBalance = Atlas.format.parseNumber(response.data?.opening_balance || 0);
  const ledger = Array.isArray(response.data?.ledger) ? response.data.ledger : [];

  let totalInvoiced = 0;
  let totalPaid = 0;

  ledger.forEach(row => {
    totalInvoiced += Atlas.format.parseNumber(row.debit || 0);
    totalPaid += Atlas.format.parseNumber(row.credit || 0);
  });

  const currentBalance = openingBalance + totalInvoiced - totalPaid;
  /*** update summary */
  clOpeningBalance.textContent = Atlas.format.amount(openingBalance);
  clTotalInvoiced.textContent = Atlas.format.amount(totalInvoiced);
  clTotalPaid.textContent = Atlas.format.amount(totalPaid);
  clCurrentBalance.textContent = Atlas.format.amount(currentBalance);

  /*** no transactions in selected period */
  if (ledger.length === 0) {
    tbody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center text-muted py-3">
        No ledger transactions found.
      </td>
    </tr>
  `;

    return;
  }

  tbody.innerHTML = ledger.map(row => `
    <tr>
      <td class="text-center">${Atlas.format.formatDate(row.transaction_date)}</td>
      <td class="text-center">
        ${getTransactionUrl(row)
      ? `
              <a
                href="${Atlas.config.baseUrl}${getTransactionUrl(row)}"
                class="text-olive" target="_blank">
                <i class="fa-external-link-alt fas fa-xs mr-1"></i>
                ${escapeHtml(row.reference_no)}
              </a>
            `
      : escapeHtml(row.reference_no)
    }
      </td>
      <td class="text-center">${escapeHtml(row.transaction_type)}</td>
      <td class="text-right">${Atlas.format.parseNumber(row.debit) > 0 ? Atlas.format.amount(row.debit) : ``}</td>
      <td class="text-right">${Atlas.format.parseNumber(row.credit) > 0 ? Atlas.format.amount(row.credit) : ``}</td>
      <td class="text-right font-weight-500">${Atlas.format.amount(row.balance)}</td>
    </tr>
  `).join('');
};

const getTransactionUrl = row => {
  const id = Atlas.id.encode(row.transaction_id);

  switch (row.transaction_type) {
    case 'SALES INVOICE':
      return `sales-invoices/edit/${id}`;

    case 'CUSTOMER PAYMENT':
      return `customer-payments/edit/${id}`;

    default:
      return null;
  }
};

const escapeHtml = value => {
  const div = document.createElement('div');
  div.textContent = value ?? '';

  return div.innerHTML;
};