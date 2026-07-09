document.addEventListener('DOMContentLoaded', () => {

  const selCustomer = document.getElementById('selCustomer');
  const txtTerms = document.getElementById('txtTerms');
  const txtCreditLimit = document.getElementById('txtCreditLimit');

  Atlas.select.init('#selCustomer');
  Atlas.select.init('#selSalesman');

  Atlas.select.onChange('#selCustomer', (option) => {
    const salesmanId = option.dataset.salesmanId;

    $('#selSalesman').val(salesmanId).trigger('change');

    txtTerms.value = option.dataset.terms ?? '';
    txtCreditLimit.value = option.dataset.creditLimit;
  });
});