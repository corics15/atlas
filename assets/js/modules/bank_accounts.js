const btnNewBankAccount =
  document.getElementById(
    'btnNewBankAccount'
  );

const btnEditBankAccount =
  document.getElementById(
    'btnEditBankAccount'
  );

const btnActivateBankAccount =
  document.getElementById(
    'btnActivateBankAccount'
  );


document.addEventListener(
  'DOMContentLoaded',
  () => {


    const btnDeactivateBankAccount =
      document.getElementById(
        'btnDeactivateBankAccount'
      );

    const btnRefreshBankAccount =
      document.getElementById(
        'btnRefreshBankAccount'
      );


    const frmBankAccount =
      document.getElementById(
        'frmBankAccount'
      );

    const hidBankAccountId =
      document.getElementById(
        'hidBankAccountId'
      );

    const txtBankName =
      document.getElementById(
        'txtBankName'
      );

    const txtAccountName =
      document.getElementById(
        'txtAccountName'
      );

    const txtAccountNo =
      document.getElementById(
        'txtAccountNo'
      );

    const txtBankBranch =
      document.getElementById(
        'txtBankBranch'
      );

    const selBankBranchId =
      document.getElementById(
        'selBankBranchId'
      );

    const selCoaAccountId =
      document.getElementById(
        'selCoaAccountId'
      );

    const chkCheckEnabled =
      document.getElementById(
        'chkCheckEnabled'
      );


    Atlas.table.init({
      checkbox: '.chkBankAccount',
      selectAll:
        '#chkSelectAllBankAccount',
      onChange:
        updateToolbarState
    });

    updateToolbarState();


    /*** new */

    btnNewBankAccount
      .addEventListener(
        'click',
        () => {

          frmBankAccount.reset();

          hidBankAccountId.value =
            '';

          chkCheckEnabled.checked =
            true;

          Atlas.validation.clear();

          Atlas.modal.open({
            id: 'mdlBankAccount',
            title:
              'New Bank Account'
          });
        }
      );


    /*** save */

    frmBankAccount
      .addEventListener(
        'submit',
        async (e) => {

          e.preventDefault();

          await Atlas.form.submit({
            form:
              frmBankAccount,

            url:
              'bank_accounts/save',

            onSuccess:
              (result) => {

                frmBankAccount
                  .reset();

                hidBankAccountId
                  .value = '';

                Atlas.validation
                  .clear();

                Atlas.modal.close(
                  'mdlBankAccount'
                );

                Atlas.toast.success(
                  result.message
                );

                setTimeout(
                  () =>
                    Atlas.page
                      .refresh(),
                  1000
                );
              },

            onError:
              (result) => {

                if (result.message) {
                  Atlas.toast.error(
                    result.message
                  );
                }
              }
          });
        }
      );


    /*** edit */

    btnEditBankAccount
      .addEventListener(
        'click',
        async () => {

          const id =
            getSelectedBankAccountId();

          if (!id) {
            return;
          }

          const result =
            await Atlas.ajax.get(
              `bank_accounts/get/${id}`
            );

          if (!result.success) {

            Atlas.toast.error(
              result.message
            );

            return;
          }

          frmBankAccount.reset();

          hidBankAccountId.value =
            result.data.id;

          txtBankName.value =
            result.data.bank_name || '';

          txtAccountName.value =
            result.data.account_name || '';

          txtAccountNo.value =
            result.data.account_no || '';

          txtBankBranch.value =
            result.data.bank_branch || '';

          selBankBranchId.value =
            result.data.branch_id || '';

          selCoaAccountId.value =
            result.data.coa_account_id || '';

          chkCheckEnabled.checked =
            isTrue(
              result.data
                .is_check_enabled
            );

          Atlas.validation.clear();

          Atlas.modal.open({
            id: 'mdlBankAccount',
            title:
              'Edit Bank Account'
          });
        }
      );


    /*** activate */

    btnActivateBankAccount
      .addEventListener(
        'click',
        async () => {

          const id =
            getSelectedBankAccountId();

          if (!id) {
            return;
          }

          const confirmed =
            await Atlas.dialog.confirm(
              'Activate Bank Account',
              'Are you sure you want to activate the selected bank account?'
            );

          if (!confirmed) {
            return;
          }

          const result =
            await Atlas.ajax.post(
              `bank_accounts/activate/${id}`
            );

          if (result.success) {

            Atlas.toast.success(
              result.message
            );

            setTimeout(
              () =>
                Atlas.page.refresh(),
              500
            );

          } else {

            Atlas.toast.error(
              result.message
            );
          }
        }
      );


    /*** deactivate */

    btnDeactivateBankAccount
      .addEventListener(
        'click',
        async () => {

          const id =
            getSelectedBankAccountId();

          if (!id) {
            return;
          }

          const confirmed =
            await Atlas.dialog.confirm(
              'Deactivate Bank Account',
              'Are you sure you want to deactivate the selected bank account?'
            );

          if (!confirmed) {
            return;
          }

          const result =
            await Atlas.ajax.post(
              `bank_accounts/deactivate/${id}`
            );

          if (result.success) {

            Atlas.toast.success(
              result.message
            );

            setTimeout(
              () =>
                Atlas.page.refresh(),
              500
            );

          } else {

            Atlas.toast.error(
              result.message
            );
          }
        }
      );


    /*** refresh */

    btnRefreshBankAccount
      .addEventListener(
        'click',
        () =>
          Atlas.page.redirect(
            'bank_accounts'
          )
      );

  }
);


const getSelectedBankAccountId =
  () => {

    const checked =
      Atlas.table.selected();

    if (checked.length === 0) {

      Atlas.toast.warning(
        'Please select an item from the list.'
      );

      return null;
    }

    if (checked.length > 1) {

      Atlas.toast.warning(
        'Please select only one item from the list.'
      );

      return null;
    }

    return checked[0].value;
  };


const updateToolbarState =
  (
    selected =
      Atlas.table.selected()
  ) => {

    const checked =
      selected.length;

    btnEditBankAccount.disabled =
      checked !== 1;

    btnActivateBankAccount.disabled =
      checked !== 1;

    btnDeactivateBankAccount.disabled =
      checked !== 1;
  };


const isTrue = value =>
  [
    true,
    1,
    '1',
    't',
    'true'
  ].includes(value);