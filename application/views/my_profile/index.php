<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="row">

      <?php /*** profile */ ?>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body box-profile text-center">

            <div class="mb-3">

            <div id="profileAvatarContainer" class="d-flex justify-content-center align-items-center" style="width:110px;height:110px;margin:0 auto;">
              <?php if (!empty($profile->avatar)): ?>

                <img id="imgProfileAvatar" src="<?= base_url($profile->avatar); ?>" alt="User Avatar" class="img-fluid img-circle elevation-2" style="width:110px; height:110px; object-fit:contain;">

              <?php else: ?>

                <i id="imgProfileAvatar" class="fas fa-user-circle text-purple" style="font-size:110px;"></i>

              <?php endif; ?>
            </div>

            </div>

            <h3 class="profile-username"><?= htmlspecialchars($profile->first_name . ' ' . $profile->last_name); ?></h3>
            <p class="text-muted mb-1"><?= htmlspecialchars($profile->username); ?></p>
            <span class="badge badge-olive"><?= htmlspecialchars(config_item('atlas')['access_levels'][$profile->access_level] ?? $profile->access_level); ?></span>

            <hr>

            <dl class="row text-left mb-0">
              <dt class="col-sm-5 font-weight-500">Branch</dt>
              <dd class="col-sm-7">
                <?= htmlspecialchars($profile->branch_name ?? '—'); ?>
              </dd>

              <dt class="col-sm-5 font-weight-500">Email</dt>
              <dd class="col-sm-7">
                <?= htmlspecialchars($profile->email ?? '—'); ?>
              </dd>
            </dl>

            <button type="button" id="btnChangeAvatar" class="btn btn-default btn-sm mt-3">
              <i class="fas fa-camera mr-1"></i>
              Change Avatar
            </button>

            <input type="file" id="fileAvatar" accept="image/*" class="d-none">

          </div>
        </div>
      </div>

      <?php /*** change password */ ?>
      <div class="col-md-4">
        <div class="card">

          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-key mr-2"></i>
              Change Password
            </h3>
          </div>

          <form id="frmChangePassword">
            <div class="card-body">
              <div class="form-group">
                <label for="txtCurrentPassword">Current Password</label>

                <div class="input-group input-group-sm">
                  <input type="password" id="txtCurrentPassword" name="current_password" class="form-control" autocomplete="current-password">

                  <div class="input-group-append">
                    <button type="button" class="btn btn-default btn-toggle-password" data-target="txtCurrentPassword" tabindex="-1">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>

                <small id="errCurrentPassword" class="text-danger"></small>
              </div>

              <div class="form-group">
                <label for="txtNewPassword">New Password</label>

                <div class="input-group input-group-sm">
                  <input type="password" id="txtNewPassword" name="new_password" class="form-control" autocomplete="new-password">

                  <div class="input-group-append">
                    <button type="button" class="btn btn-default btn-toggle-password" data-target="txtNewPassword" tabindex="-1">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>

                <small id="errNewPassword" class="text-danger"></small>
              </div>

              <div class="form-group">
                <label for="txtConfirmPassword">Confirm New Password</label>

                <div class="input-group input-group-sm">
                  <input type="password" id="txtConfirmPassword" name="confirm_password" class="form-control" autocomplete="new-password">

                  <div class="input-group-append">
                    <button type="button" class="btn btn-default btn-toggle-password" data-target="txtConfirmPassword" tabindex="-1">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>

                <small id="errConfirmPassword" class="text-danger"></small>
              </div>

            </div>

            <div class="card-footer text-right">
              <button type="submit" id="btnChangePassword" class="btn btn-default btn-sm">
                <i class="fas fa-save mr-1"></i>
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>

      <?php /*** signature */ ?>
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-signature mr-2"></i>
              My Report Signature
            </h3>
          </div>

          <div class="card-body">
            <p class="text-muted small">
              Your signature will be used when you appear as Prepared By on reports. This signature is only visible to you.
            </p>
            <div id="signaturePreview" class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:350px;height:280px;max-width:100%;border:2px dotted #6c757d;">
              <?php if (!empty($profile->signature)): ?>
                <img id="imgSignature" src="<?= base_url($profile->signature); ?>" alt="My Signature" class="img-fluid" style="max-width:250px;height:80px;object-fit:contain;">
              <?php else: ?>
                <div id="noSignature" class="align-items-center d-flex flex-column py-3 text-muted">
                  <i class="fas fa-signature fa-2x mb-2"></i>
                  <div class="font-sm">No signature uploaded.</div>
                </div>
              <?php endif; ?>
            </div>

            <div class="text-center">
              <button type="button" id="btnSelectSignature" class="btn btn-default btn-sm">
                <i class="fas fa-upload mr-1"></i>
                <?= !empty($profile->signature)
                  ? 'Replace Signature'
                  : 'Upload Signature'; ?>
              </button>

              <button type="button" id="btnRemoveSignature" class="btn btn-link btn-sm ml-1 <?= empty($profile->signature) ? 'd-none' : ''; ?>">
                <i class="fas fa-trash-alt mr-1"></i>
                Remove Signature
              </button>

              <input type="file" id="fileSignature" accept="image/jpeg,image/png" class="d-none">
            </div>

            <small class="form-text text-muted text-center mt-2">
              JPG or PNG, maximum 2 MB.<br>
              Recommended maximum: <span class="font-weight-500 text-maroon">350x280 pixels</span>.
              Larger images may cause report content to move to the next page.
            </small>

          </div>
        </div>
      </div>

    </div>

  </div>
</section>

<?php /*** Avatar Picker Modal */ ?>
<?php $this->load->view('my_profile/modal'); ?>