<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bank_accounts extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model(['Bank_account_model', 'Branch_model']);
    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage('Bank Accounts', [
        'id'   => 'btnNewBankAccount',
        'icon' => 'fas fa-plus',
        'text' => 'New Bank Account'
    ]);

    $this->pageScript = 'bank_accounts';

    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword']       = $keyword;
    $this->data['bank_accounts'] = $this->Bank_account_model->getAll($keyword);
    $this->data['recordCount']   = count($this->data['bank_accounts']);
    $this->data['branches']      = $this->Branch_model->getDropdown();
    $this->data['coa_accounts']  = $this->Bank_account_model->getCoaDropdown();
    $this->data['searchPlaceHolder'] = 'Search accounts...';

    $this->data['tableContent'] = $this->load->view(
        'bank_accounts/table',
        $this->data,
        true
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditBankAccount',
        'text' => 'Edit',
        'icon' => 'fas fa-edit'
      ],
      'activate' => [
        'id'   => 'btnActivateBankAccount',
        'text' => 'Activate',
        'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
        'id'   => 'btnDeactivateBankAccount',
        'text' => 'Deactivate',
        'icon' => 'fas fa-ban'
      ],
      'refresh' => [
        'id'   => 'btnRefreshBankAccount',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'bank-accounts',
      ]
    ];

    $this->render('bank_accounts/index');
  }

  public function get($id)
  {
    $bankAccount = $this->Bank_account_model->get((int) $id);

    if (!$bankAccount) {
      return $this->jsonResponse(false, 'Bank Account not found.');
    }

    return $this->jsonResponse(true, '', $bankAccount);
  }

  public function save()
  {
    $postData = $this->input->post();
    $id       = (int) ($postData['id'] ?? 0);

    $this->form_validation->set_rules('bank_name', 'Bank Name', 'required|trim');
    $this->form_validation->set_rules('account_name', 'Account Name', 'required|trim');
    $this->form_validation->set_rules('account_no', 'Account No.', 'required|trim');
    $this->form_validation->set_rules('coa_account_id', 'COA Account', 'required|integer');

    if (!$this->form_validation->run()) {
        return $this->validationResponse();
    }

    /*** validate branch */
    $branchId = null;
    if (!empty($postData['branch_id'])) {
        $branch = $this->db->select('id')
            ->where('id', (int) $postData['branch_id'])
            ->where('is_active', true)
            ->get('m_branches')
            ->row();

        if (!$branch) {
            return $this->jsonResponse(false, 'Invalid or inactive Branch.');
        }
        $branchId = (int) $branch->id;
    }

    /*** validate COA */
    $coa = $this->db->select('id, account_type')
        ->where('id', (int) $postData['coa_account_id'])
        ->where('account_type', 'ASSET')
        ->where('is_posting', true)
        ->where('is_active', true)
        ->get('m_chart_of_accounts')
        ->row();

    if (!$coa) {
      return $this->jsonResponse(false, 'Please select a valid posting ASSET account.');
    }

    $data = [
      'branch_id'       => $branchId,
      'bank_name'       => strtoupper(trim($postData['bank_name'])),
      'account_name'    => strtoupper(trim($postData['account_name'])),
      'account_no'      => strtoupper(trim($postData['account_no'])),
      'bank_branch'     => !empty(trim($postData['bank_branch'] ?? '')) ? strtoupper(trim($postData['bank_branch'])) : null,
      'coa_account_id'  => (int) $postData['coa_account_id'],
      'is_check_enabled'=> !empty($postData['is_check_enabled'])
    ];

    if (empty($id)) {
      $data['is_active']   = true;
      $data['entered_by']  = $this->session->userdata('user_id');
      $data['entered_on']  = date('Y-m-d H:i:s');
    } else {
      if (!$this->Bank_account_model->get($id)) {
        return $this->jsonResponse(false, 'Bank Account not found.');
      }
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    $success = $this->Bank_account_model->save($data, $id);

    if (!$success) {
      return $this->jsonResponse(false, 'Unable to save Bank Account.');
    }

    return $this->jsonResponse(
        true,
        empty($id)
            ? 'Bank Account saved successfully.'
            : 'Bank Account updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Bank_account_model->get((int) $id)) {
      return $this->jsonResponse(false, 'Bank Account not found.');
    }

    $this->Bank_account_model->activate((int) $id);
    return $this->jsonResponse(true, 'Bank Account activated successfully.');
  }

  public function deactivate($id)
  {
    if (!$this->Bank_account_model->get((int) $id)) {
      return $this->jsonResponse(false, 'Bank Account not found.');
    }

    $this->Bank_account_model->deactivate((int) $id);
    return $this->jsonResponse(true, 'Bank Account deactivated successfully.');
  }
}
