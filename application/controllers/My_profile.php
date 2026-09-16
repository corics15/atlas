<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_profile extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('User_model');
  }

  public function index()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      show_404();
    }

    $this->data['profile'] = $this->User_model->getProfile($userId);

    if (!$this->data['profile']) {
      show_404();
    }

    $this->data['avatars'] = [];

    for ($i = 1; $i <= 20; $i++) {
      $this->data['avatars'][] = 'assets/images/avatars/atlas_avatar_' . $i . '.png';
    }

    $this->setPage('My Profile');
    $this->pageScript = 'my_profile';

    $this->render('my_profile/index');
  }

  public function changePassword()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      return $this->jsonResponse(
        false,
        'Your session has expired.'
      );
    }

    $request = $this->getJsonRequest();

    $currentPassword = trim($request['current_password'] ?? '');
    $newPassword = $request['new_password'] ?? '';
    $confirmPassword = $request['confirm_password'] ?? '';

    if (empty($currentPassword)) {
      return $this->jsonResponse(
        false,
        'Current password is required.'
      );
    }

    if (empty($newPassword)) {
      return $this->jsonResponse(
        false,
        'New password is required.'
      );
    }

    if ($newPassword !== $confirmPassword) {
      return $this->jsonResponse(
        false,
        'New password and confirmation do not match.'
      );
    }

    if (strlen($newPassword) < 8) {
      return $this->jsonResponse(
        false,
        'New password must be at least 8 characters long.'
      );
    }

    $user = $this->User_model->get($userId);

    if (!$user) {
      return $this->jsonResponse(
        false,
        'User account not found.'
      );
    }

    if (!password_verify($currentPassword, $user->password)) {
      return $this->jsonResponse(
        false,
        'Current password is incorrect.'
      );
    }

    if (password_verify($newPassword, $user->password)) {
      return $this->jsonResponse(
        false,
        'New password must be different from your current password.'
      );
    }

    $success = $this->User_model->updatePassword(
      $userId,
      $newPassword
    );

    if (!$success) {
      return $this->jsonResponse(
        false,
        'Unable to update your password.'
      );
    }

    return $this->jsonResponse(
      true,
      'Password updated successfully.'
    );
  }

  public function selectAvatar()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      return $this->jsonResponse(
        false,
        'Your session has expired.'
      );
    }

    /*** grab old avatar */
    $currentUser = $this->User_model->get($userId);
    if (!$currentUser) {
      return $this->jsonResponse(
        false,
        'User account not found.'
      );
    }
    $oldAvatar = $currentUser->avatar;
    /*** end grab */

    $request = $this->getJsonRequest();
    $avatar = $request['avatar'] ?? '';
    $allowedAvatars = [];

    for ($i = 1; $i <= 20; $i++) {
      $allowedAvatars[] =
        'assets/images/avatars/atlas_avatar_' . $i . '.png';
    }

    if (!in_array($avatar, $allowedAvatars, TRUE)) {
      return $this->jsonResponse(
        false,
        'Invalid avatar selection.'
      );
    }

    $updated = $this->User_model->updateAvatar(
      $userId,
      $avatar
    );

    if (!$updated) {
      return $this->jsonResponse(
        false,
        'Unable to update your avatar.'
      );
    }

    /*** remove old avatar after successful upload */
    if ($oldAvatar && strpos($oldAvatar, 'uploads/avatars/') === 0) {
      $oldAvatarFile = FCPATH . $oldAvatar;

      if (is_file($oldAvatarFile)) {
        @unlink($oldAvatarFile);
      }
    }
    /*** end remove */

    return $this->jsonResponse(
      true,
      'Avatar updated successfully.',
      [
        'avatar' => base_url($avatar)
      ]
    );
  }

  public function uploadAvatar()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      return $this->jsonResponse(
        false,
        'Your session has expired.'
      );
    }

    if (empty($_FILES['avatar']['name'])) {
      return $this->jsonResponse(
        false,
        'Please select an image.'
      );
    }

    $uploadPath = FCPATH . 'uploads/avatars/';

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, TRUE);
    }

    $config = [
      'upload_path'   => $uploadPath,
      'allowed_types' => 'jpg|jpeg|png|webp',
      'max_size'      => 2048,
      'encrypt_name'  => TRUE,
      'remove_spaces' => TRUE,
    ];

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('avatar')) {
      return $this->jsonResponse(
        false,
        strip_tags(
          $this->upload->display_errors('', '')
        )
      );
    }

    $upload = $this->upload->data();
    $avatarPath = 'uploads/avatars/' . $upload['file_name'];

    /*** grab old custom avatar */
    $currentUser = $this->User_model->get($userId);
    if (!$currentUser) {
      @unlink($upload['full_path']);

      return $this->jsonResponse(
        false,
        'User account not found.'
      );
    }
    $oldAvatar = $currentUser->avatar;
    /*** end grab */

    $updated = $this->User_model->updateAvatar(
      $userId,
      $avatarPath
    );

    if (!$updated) {
      @unlink($upload['full_path']);

      return $this->jsonResponse(
        false,
        'Unable to update your avatar.'
      );
    }

    /*** remove old custom avatar */
    if ($oldAvatar && strpos($oldAvatar, 'uploads/avatars/') === 0) {
      $oldAvatarFile = FCPATH . $oldAvatar;

      if (is_file($oldAvatarFile)) {
        @unlink($oldAvatarFile);
      }
    }
    /*** end remove */

    return $this->jsonResponse(
      true,
      'Avatar uploaded successfully.',
      [
        'avatar' => base_url($avatarPath)
      ]
    );
  }

  public function uploadSignature()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      return $this->jsonResponse(
        false,
        'Your session has expired.'
      );
    }

    if (empty($_FILES['signature']['name'])) {
      return $this->jsonResponse(
        false,
        'Please select a signature image.'
      );
    }

    $uploadPath = FCPATH . 'uploads/signatures/';

    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, TRUE);
    }

    $config = [
      'upload_path'   => $uploadPath,
      'allowed_types' => 'jpg|jpeg|png',
      'max_size'      => 2048,
      'encrypt_name'  => TRUE,
      'remove_spaces' => TRUE,
    ];

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('signature')) {
      return $this->jsonResponse(
        false,
        strip_tags(
          $this->upload->display_errors('', '')
        )
      );
    }

    $upload = $this->upload->data();
    $signaturePath = 'uploads/signatures/' . $upload['file_name'];

    /*** grab current user and old signature */
    $currentUser = $this->User_model->get($userId);

    if (!$currentUser) {
      @unlink($upload['full_path']);

      return $this->jsonResponse(
        false,
        'User account not found.'
      );
    }

    $oldSignature = $currentUser->signature;

    $updated = $this->User_model->updateSignature(
      $userId,
      $signaturePath
    );

    if (!$updated) {
      @unlink($upload['full_path']);

      return $this->jsonResponse(
        false,
        'Unable to update your signature.'
      );
    }

    /*** remove old uploaded signature */
    if ($oldSignature && strpos($oldSignature, 'uploads/signatures/') === 0) {
      $oldSignatureFile = FCPATH . $oldSignature;

      if (is_file($oldSignatureFile)) {
        @unlink($oldSignatureFile);
      }
    }

    return $this->jsonResponse(
      true,
      'Signature uploaded successfully.',
      [
        'signature' => base_url($signaturePath)
      ]
    );
  }

  public function removeSignature()
  {
    $userId = $this->session->userdata('user_id');

    if (!$userId) {
      return $this->jsonResponse(
        false,
        'Your session has expired.'
      );
    }

    $currentUser = $this->User_model->get($userId);

    if (!$currentUser) {
      return $this->jsonResponse(
        false,
        'User account not found.'
      );
    }

    if (empty($currentUser->signature)) {
      return $this->jsonResponse(
        false,
        'You do not have a signature to remove.'
      );
    }

    $oldSignature = $currentUser->signature;

    $updated = $this->User_model->updateSignature(
      $userId,
      NULL
    );

    if (!$updated) {
      return $this->jsonResponse(
        false,
        'Unable to remove your signature.'
      );
    }

    /*** remove uploaded signature file */
    if (strpos($oldSignature, 'uploads/signatures/') === 0) {
      $signatureFile = FCPATH . $oldSignature;

      if (is_file($signatureFile)) {
        @unlink($signatureFile);
      }
    }

    return $this->jsonResponse(
      true,
      'Signature removed successfully.'
    );
  }

}