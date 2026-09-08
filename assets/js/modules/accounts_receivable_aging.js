const selCustomer = document.getElementById('selCustomer');

document.addEventListener('DOMContentLoaded', async () => {
  Atlas.select.init('#selCustomer');

  document.querySelectorAll('.js-aging-details').forEach(link => {
    link.addEventListener('click', async e => {
      e.preventDefault();

      const asOfDate = document.getElementById('dtAsOfDate')?.value;
      const result = await Atlas.ajax.get(
        'accounts-receivable-aging/details',
        {
          customer_id: link.dataset.customerId,
          as_of_date: document.getElementById('asOfDate')?.value
        }
      );

      if (!result.success) {
        console.error(result.message);
        return;
      }

      const body = document.getElementById('agingDetailsBody');
      const total = result.data.reduce((sum, row) => sum + Number(row.balance || 0), 0);

      document.getElementById('agingCustomerName').textContent = link.dataset.customerName;
      document.getElementById('agingAsOfDate').textContent = `As of ${Atlas.format.formatDate(asOfDate)}`;

      body.innerHTML = result.data.map(row => `
          <tr>
            <td class="text-center">${row.si_no}</td>
            <td class="text-center">${Atlas.format.formatDate(row.invoice_date)}</td>
            <td class="text-right">${Atlas.format.amount(row.total_amount)}</td>
            <td class="text-right">${Atlas.format.amount(row.amount_paid)}</td>
            <td class="text-right">${Atlas.format.amount(row.credit_memo)}</td>
            <td class="text-right font-weight-500">${Atlas.format.amount(row.balance)}</td>
          </tr>
        `).join('');

      document.getElementById('agingDetailsTotal').textContent = Atlas.format.amount(total);

      $('#agingDetailsModal').modal('show');
    });
  });
});