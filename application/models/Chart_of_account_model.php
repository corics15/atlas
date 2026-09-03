<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chart_of_account_model extends CI_Model
{

  private $table = 'm_chart_of_accounts';

  public function getAll($keyword = '')
  {
    $this->db
        ->select([
            'coa.id',
            'coa.account_code',
            'coa.account_name',
            'coa.parent_id',
            'coa.account_type',
            'coa.normal_balance',
            'coa.account_group_id',
            'coa.is_posting',
            'coa.is_active',
            'coa.remarks',
            'parent.account_code AS parent_code',
            'parent.account_name AS parent_name',
            'ag.group_code',
            'ag.group_name'
        ])
        ->from($this->table . ' coa')
        ->join($this->table . ' parent', 'parent.id = coa.parent_id', 'left')
        ->join('m_account_groups ag', 'ag.id = coa.account_group_id', 'left');

    if ($keyword !== '') {
        $this->db
            ->group_start()
                ->like('coa.account_code', $keyword)
                ->or_like('coa.account_name', $keyword)
                ->or_like('ag.group_name', $keyword)
            ->group_end();
    }

    return $this->db
        ->order_by('coa.account_code', 'ASC')
        ->get()
        ->result();
  }

  public function getParentAccounts()
  {
    return $this->db
        ->select('id, account_code, account_name, account_type, normal_balance, account_group_id')
        ->from($this->table)
        ->where('is_posting', FALSE)
        ->where('is_active', TRUE)
        ->order_by('account_code', 'ASC')
        ->get()
        ->result();
  }

  public function getAccountGroups()
  {
    return $this->db
        ->select('id, group_code, group_name, account_type')
        ->from('m_account_groups')
        ->where('is_active', TRUE)
        ->order_by('display_order', 'ASC')
        ->order_by('group_name', 'ASC')
        ->get()
        ->result();
  }

  public function getById($id)
  {
    return $this->db
        ->where('id', (int) $id)
        ->get($this->table)
        ->row();
  }

  public function save(array $data)
  {
    $this->db->trans_begin();

    try {

      $id = !empty($data['id']) ? (int) $data['id'] : 0;
      $isEdit = $id > 0;

      /*** existing */
      $existing = NULL;
      if ($isEdit) {
        $existing = $this->getById($id);

        if (!$existing) {
          throw new Exception('Chart of Account not found.');
        }
      }

      $accountCode = trim($data['account_code'] ?? '');
      $accountName = strtoupper(trim($data['account_name'] ?? ''));
      $parentId = !empty($data['parent_id']) ? (int) $data['parent_id'] : NULL;
      $groupId = !empty($data['account_group_id']) ? (int) $data['account_group_id'] : NULL;
      $accountType = strtoupper(trim($data['account_type'] ?? ''));
      $normalBalance = strtoupper(trim($data['normal_balance'] ?? ''));

      $isPosting = !empty($data['is_posting']);
      $isActive = !empty($data['is_active']);

      if ($accountCode === '') {
        throw new Exception('Account Code is required.');
      }

      if ($accountName === '') {
        throw new Exception('Account Name is required.');
      }

      $validTypes = [
        'ASSET',
        'LIABILITY',
        'EQUITY',
        'REVENUE',
        'EXPENSE'
      ];

      if (!in_array($accountType, $validTypes, TRUE)) {
        throw new Exception('Invalid Account Type.');
      }

      if (!in_array($normalBalance, ['DEBIT', 'CREDIT'], TRUE)) {
        throw new Exception('Invalid Normal Balance.');
      }

      /*** check for duplicate account codes */
      $this->db
          ->from($this->table)
          ->where('account_code', $accountCode);

      if ($isEdit) {
        $this->db->where('id <>', $id);
      }

      if ($this->db->count_all_results() > 0) {
        throw new Exception('Account Code already exists.');
      }

      /*** check parent account */
      if ($parentId !== NULL) {

        if ($isEdit && $this->wouldCreateParentCycle($id, $parentId)) {
          throw new Exception(
            'Invalid Parent Account. This would create a circular account hierarchy.'
          );
        }

        $parent = $this->getById($parentId);
        if (!$parent) {
          throw new Exception('Invalid Parent Account.');
        }

        $parentIsActive = (
            $parent->is_active === TRUE ||
            $parent->is_active === 't' ||
            $parent->is_active === '1' ||
            $parent->is_active === 1
        );
        if (!$parentIsActive) {
          throw new Exception(
            'Parent Account must be active.'
          );
        }

        $parentIsPosting = (
          $parent->is_posting === TRUE ||
          $parent->is_posting === 't' ||
          $parent->is_posting === '1' ||
          $parent->is_posting === 1
        );

        if ($parentIsPosting) {
          throw new Exception(
            'Parent Account must be a GROUP account.'
          );
        }

        if ($parent->account_type !== $accountType) {
          throw new Exception(
            'Account Type must match the Parent Account.'
          );
        }
      }

      /*** check report group */
      if ($groupId !== NULL) {
        $group = $this->db
            ->where('id', $groupId)
            ->where('is_active', TRUE)
            ->get('m_account_groups')
            ->row();

        if (!$group) {
          throw new Exception('Invalid Report Group.');
        }

        if ($group->account_type !== $accountType) {
          throw new Exception(
            'Report Group must match the Account Type.'
          );
        }
      }

      if ($isEdit && $isPosting) {
        $childCount = $this->db
            ->where('parent_id', $id)
            ->count_all_results($this->table);

        if ($childCount > 0) {
          throw new Exception(
            'This account has child accounts and must remain a GROUP account.'
          );
        }
      }

      $saveData = [
        'account_code'     => $accountCode,
        'account_name'     => $accountName,
        'parent_id'        => $parentId,
        'account_type'     => $accountType,
        'normal_balance'   => $normalBalance,
        'account_group_id' => $groupId,
        'is_posting'       => $isPosting,
        'is_active'        => $isActive,
        'remarks'          => trim($data['remarks'] ?? '') !== '' ? strtoupper(trim($data['remarks'])) : NULL
      ];

      if ($isEdit) {
        $saveData['updated_by'] = (int) $data['user_id'];
        $saveData['updated_on'] = date('Y-m-d H:i:s');
        $this->db
          ->where('id', $id)
          ->update($this->table, $saveData);

        $accountId = $id;
        $message = 'Chart of Account updated successfully.';

      } else {

        $saveData['entered_by'] = (int) $data['user_id'];
        $this->db->insert(
          $this->table,
          $saveData
        );

        $accountId = (int) $this->db->insert_id();
        $message = 'Chart of Account saved successfully.';
      }

      if ($this->db->trans_status() === FALSE || !$accountId) {
        throw new Exception(
          'Unable to save Chart of Account.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => $message,
        'data' => [
          'account_id' => $accountId
        ]
      ];

    } catch (Exception $e) {

        $this->db->trans_rollback();

        return [
          'success' => FALSE,
          'message' => $e->getMessage(),
          'data' => []
        ];
    }
  }

  private function wouldCreateParentCycle($accountId, $parentId)
  {
      $accountId = (int) $accountId;
      $parentId = (int) $parentId;

      while ($parentId > 0) {

          if ($parentId === $accountId) {
              return TRUE;
          }

          $parent = $this->getById($parentId);

          if (!$parent || empty($parent->parent_id)) {
              break;
          }

          $parentId = (int) $parent->parent_id;
      }

      return FALSE;
  }

  public function searchPostingAccounts($keyword, $limit = 10)
  {
    $keyword = trim($keyword);

    if (strlen($keyword) < 2) {
      return [];
    }

    $limit = (int) $limit;

    if ($limit <= 0 || $limit > 20) {
      $limit = 10;
    }

    $escaped = $this->db->escape_like_str($keyword);
    return $this->db
        ->select([
          'coa.id',
          'coa.account_code',
          'coa.account_name',
          'coa.account_type',
          'coa.normal_balance'
        ])
        ->from($this->table . ' coa')
        ->where('coa.is_active', TRUE)
        ->where('coa.is_posting', TRUE)
        ->group_start()
            ->where(
              "coa.account_code ILIKE '%{$escaped}%'"
            )
            ->or_where(
              "coa.account_name ILIKE '%{$escaped}%'"
            )
        ->group_end()
        ->order_by('coa.account_code', 'ASC')
        ->limit($limit)
        ->get()
        ->result();
  }

}