document.addEventListener('DOMContentLoaded', () => {

  const parentAccount = document.getElementById('selParentAccount');
  const accountType = document.getElementById('selAccountType');
  const normalBalance = document.getElementById('selNormalBalance');
  const accountGroup = document.getElementById('selAccountGroup');
  const saveButton = document.getElementById('btnSaveChartOfAccount');
  const newButton = document.getElementById('btnNewAccount');

  /*** new */
  newButton?.addEventListener('click', () => Atlas.page.redirect(`chart-of-accounts/create`));

  /*** event trigger */
  parentAccount?.addEventListener('change', () => {
    const option = parentAccount.options[parentAccount.selectedIndex];

    if (!option || !option.value) {
      return;
    }

    const parentType = option.dataset.accountType || '';
    const parentNormalBalance = option.dataset.normalBalance || '';
    const parentGroupId = option.dataset.accountGroupId || '';

    if (accountType && parentType) {
      accountType.value = parentType;
    }
    if (normalBalance && parentNormalBalance) {
      normalBalance.value = parentNormalBalance;
    }
    if (accountGroup && parentGroupId) {
      accountGroup.value = parentGroupId;
    }
  });

  /*** save */
  saveButton?.addEventListener('click', async () => {
    clearFieldErrors();

    const accountCode = document.getElementById('txtAccountCode');
    const accountName = document.getElementById('txtAccountName');
    const accountClass = document.getElementById('selAccountClass');
    const accountStatus = document.getElementById('selAccountStatus');
    const remarks = document.getElementById('txtRemarks');
    const accountId = document.getElementById('hidChartOfAccountId').value;

    if (!accountCode.value.trim()) {
      showFieldError(
        accountCode,
        'Account Code is required.'
      );
      return;
    }

    if (!accountName.value.trim()) {
      showFieldError(
        accountName,
        'Account Name is required.'
      );
      return;
    }

    if (!accountType.value) {
      showFieldError(
        accountType,
        'Account Type is required.'
      );
      return;
    }

    if (!normalBalance.value) {
      showFieldError(
        normalBalance,
        'Normal Balance is required.'
      );
      return;
    }

    const payload = {
      id: accountId || null,
      account_code: accountCode.value.trim(),
      account_name: accountName.value.trim(),
      parent_id: parentAccount.value || null,
      account_group_id: accountGroup.value || null,
      account_type: accountType.value,
      normal_balance: normalBalance.value,
      is_posting: accountClass.value === 'POSTING',
      is_active: accountStatus.value === 'ACTIVE',
      remarks: remarks.value.trim()
    };

    const result = await Atlas.ajax.post(
      'chart-of-accounts/save',
      payload
    );

    if (!result.success) {
      Atlas.toast.error(result.message);
      return;
    }

    Atlas.toast.success(result.message);
    setTimeout(() => Atlas.page.refresh(), 1200);
  });

  /*** error markers */
  const clearFieldErrors = () => document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  const showFieldError = (element, message) => {
    if (element) {
      element.classList.add('is-invalid');
      element.focus();

      if (element.scrollIntoView) {
        element.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }
    }

    Atlas.toast.error(message);
  };

  /*** clear errors */
  document.addEventListener('input', e => {
    if (e.target.classList.contains('is-invalid')) {
      e.target.classList.remove('is-invalid');
    }
  });
  document.addEventListener('change', e => {
    if (e.target.classList.contains('is-invalid')) {
      e.target.classList.remove('is-invalid');
    }
  });

});