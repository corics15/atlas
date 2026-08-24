const btnPrintSOA = document.getElementById('btnPrintSOA');

document.addEventListener('DOMContentLoaded', () => {
  Atlas.select.init('#selCustomer');

  btnPrintSOA?.addEventListener('click', () => { printSOA() });
});

const printSOA = () => {
  const customerId = document.querySelector('[name="customer_id"]')?.value || '';
  const dateFrom = document.querySelector('[name="date_from"]')?.value || '';
  const dateTo = document.querySelector('[name="date_to"]')?.value || '';

  if (!customerId) {
    Atlas.toast.warning('Please select a customer.');
    return;
  }

  if (!dateFrom || !dateTo) {
    Atlas.toast.warning('Statement period is required.');
    return;
  }

  if (dateFrom > dateTo) {
    Atlas.toast.warning('Date From cannot be later than Date To.');
    return;
  }

  Atlas.print.post(
    'statement-of-account/print',
    {
      customer_id: customerId,
      date_from: dateFrom,
      date_to: dateTo
    }
  );
};