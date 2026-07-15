document.addEventListener('DOMContentLoaded', () => {

  const btnNewCustomer = document.getElementById('btnNewCustomer');
  const btnEditCustomer = document.getElementById('btnEditCustomer');
  const btnActivateCustomer = document.getElementById('btnActivateCustomer');
  const btnDeactivateCustomer = document.getElementById('btnDeactivateCustomer');
  const btnRefreshCustomer = document.getElementById('btnRefreshCustomer');

  const frmCustomer = document.getElementById('frmCustomer');

  const txtBarcode = document.getElementById('txtBarcode');
  const txtDescription = document.getElementById('txtDescription');
  const selSupplier = document.getElementById('selSupplier');
  const selUom = document.getElementById('selUom');
  const txtCost = document.getElementById('txtCost');
  const txtSRP = document.getElementById('txtSRP');

  const hidCustomerId = document.getElementById('hidCustomerId');
  const chkSelectAllCustomer = document.getElementById('chkSelectAllCustomer');

  Atlas.select.init('#selSupplier', '#mdlCustomer');
  Atlas.select.init('#selSalesman', '#mdlCustomer');
  Atlas.select.init('#selTerms', '#mdlCustomer');

  Atlas.table.init({
    checkbox: '.chkCustomer',
    selectAll: '#chkSelectAllCustomer',
    onChange: updateToolbarState
  });

  updateToolbarState();

  btnNewCustomer.addEventListener('click', () => {
    frmCustomer.reset();
    hidCustomerId.value = '';

    $('#selTerms').val('').trigger('change');

    Atlas.validation.clear();
    Atlas.modal.open({
      id: 'mdlCustomer',
      title: 'New Customer'
    });
  });

  frmCustomer.addEventListener('submit', async (e) => {
    e.preventDefault();

    await Atlas.form.submit({
      form: frmCustomer,
      url: 'customers/save',
      onSuccess: (result) => {
        frmCustomer.reset();
        hidCustomerId.value = '';
        Atlas.validation.clear();

        Atlas.modal.close('mdlCustomer');
        Atlas.toast.success(result.message);
        setTimeout(() => {
          Atlas.page.refresh();
        }, 1500);
      },
      onError: (result) => {
        console.log(result);
      }
    });
  });

  btnEditCustomer.addEventListener('click', async () => {
    const id = getSelectedCustomerId();

    if (!id) {
      return;
    }

    const result = await Atlas.ajax.get(
      'customers/get/' + id
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    frmCustomer.reset();

    hidCustomerId.value = result.data.id;

    txtCustomerName.value = result.data.customer_name;
    txtAddress.value = result.data.address;
    txtContactPerson.value = result.data.contact_person;
    txtMobileNo.value = result.data.mobile_no;
    txtTelephoneNo.value = result.data.telephone_no;
    txtEmailAddress.value = result.data.email_address;
    txtCreditLimit.value = result.data.credit_limit;

    $('#selSalesman').val(result.data.salesman_id).trigger('change');
    $('#selTerms').val(result.data.terms_id).trigger('change');

    Atlas.validation.clear();

    Atlas.modal.open({
      id: 'mdlCustomer',
      title: 'Edit Customer'
    });
  });

  btnActivateCustomer.addEventListener('click', async () => {
    const id = getSelectedCustomerId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Activate Customer',
      'Are you sure you want to activate the selected customer?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'customers/activate/' + id
    );

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => {
        Atlas.page.refresh();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  btnDeactivateCustomer.addEventListener('click', async () => {
    const id = getSelectedCustomerId();

    if (!id) {
      return;
    }

    const confirmed = await Atlas.dialog.confirm(
      'Deactivate Customer',
      'Are you sure you want to deactivate the selected customer?'
    );

    if (!confirmed) {
      return;
    }

    const result = await Atlas.ajax.post(
      'customers/deactivate/' + id
    );

    if (result.success) {
      Atlas.toast.success(result.message);
      setTimeout(() => {
        Atlas.page.refresh();
      }, 500);
    } else {
      Atlas.toast.error(result.message);
    }
  });

  btnRefreshCustomer.addEventListener('click', () => {
    Atlas.page.refresh();
  });
});

const getSelectedCustomerId = () => {
  const checked = Atlas.table.selected();

  if (checked.length === 0) {
    Atlas.toast.warning('Please select a customer.');
    return null;
  }

  if (checked.length > 1) {
    Atlas.toast.warning('Please select only one customer.');
    return null;
  }

  return checked[0].value;
}

const updateToolbarState = (selected = Atlas.table.selected()) => {
  const checked = selected.length;

  btnEditCustomer.disabled = (checked !== 1);
  btnActivateCustomer.disabled = (checked !== 1);
  btnDeactivateCustomer.disabled = (checked !== 1);
}