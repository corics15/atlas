<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body box-profile text-center">

            <div class="mb-3">

            <?php if (false) : ?>
              <?php if (!empty($profile->avatar)): ?>
                <img src="<?= base_url($profile->avatar); ?>" alt="User Avatar" class="profile-user-img img-fluid img-circle elevation-2" style="width:110px;height:110px;object-fit:cover;">
              <?php else: ?>
                <i class="fas fa-user-circle text-purple" style="font-size:110px;"></i>
              <?php endif; ?>
            <?php endif; ?>

            <div id="profileAvatarContainer" class="d-flex justify-content-center align-items-center" style="width:110px;height:110px;margin:0 auto;">
              <?php if (!empty($profile->avatar)): ?>

                <img id="imgProfileAvatar" src="<?= base_url($profile->avatar); ?>" alt="User Avatar" class="img-fluid img-circle elevation-2"
                  style="
                    width:110px;
                    height:110px;
                    object-fit:contain;
                  ">

              <?php else: ?>

                <i id="imgProfileAvatar" class="fas fa-user-circle text-purple" style="font-size:110px;"></i>

              <?php endif; ?>
            </div>

            </div>

            <h3 class="profile-username">
              <?= htmlspecialchars(
                $profile->first_name . ' ' . $profile->last_name
              ); ?>
            </h3>

            <p class="text-muted mb-1">
              <?= htmlspecialchars($profile->username); ?>
            </p>

            <span class="badge badge-olive">
              <?= htmlspecialchars(
                config_item('atlas')['access_levels'][$profile->access_level]
                  ?? $profile->access_level
              ); ?>
            </span>

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

      <div class="col-md-8">
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
    </div>

  </div>
</section>

<?php /*** Avatar Picker Modal */ ?>
<div class="modal fade" id="mdlAvatar" tabindex="-1" role="dialog" aria-labelledby="mdlAvatarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mdlAvatarLabel">
          <i class="fas fa-user-circle mr-2"></i>
          Choose Your Avatar
        </h5>

        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <h6>Select an avatar:</h6>
        <div id="avatarGrid" class="row justify-content-center">

          <?php foreach ($avatars as $index => $avatar): ?>
            <div class="col-4 col-sm-3 col-md-2 mb-3 text-center">
              <button type="button" class="btn p-1 avatar-option" data-avatar="<?= htmlspecialchars($avatar); ?>" title="Avatar <?= $index + 1; ?>">
                <img src="<?= base_url($avatar); ?>" alt="Avatar <?= $index + 1; ?>" class="img-fluid rounded-circle"
                  style="
                    width: 82px;
                    height: 82px;
                    object-fit: contain;
                  ">
              </button>
            </div>
          <?php endforeach; ?>

        </div>

        <?php /*** custom avatar preview */ ?>
        <div id="customAvatarPreview" class="text-center mt-4 d-none">
          <hr>
          <h6 class="mb-3">
            <i class="fas fa-camera mr-1"></i>
            Custom Avatar
          </h6>
          <img id="imgCustomAvatarPreview" src="" alt="Avatar Preview" class="img-fluid rounded-circle elevation-2"
            style="
              width: 120px;
              height: 120px;
              object-fit: contain;
            ">

          <div id="txtCustomAvatarName" class="text-muted small mt-2"></div>
          <div class="mt-3">
            <button type="button" id="btnCancelCustomAvatar" class="btn btn-default btn-sm">
              Cancel
            </button>

            <button type="button" id="btnUploadCustomAvatar" class="btn btn-olive btn-sm">
              <i class="fas fa-upload mr-1"></i>
              Upload Picture
            </button>
          </div>
        </div>
        <?php /*** end custom */ ?>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
          Cancel
        </button>

        <button type="button" id="btnUseAvatar" class="btn btn-olive btn-sm" disabled>
          <i class="fas fa-check mr-1"></i>
          Use Selected Avatar
        </button>

        <div class="text-muted my-2">
          — or —
        </div>

        <button type="button" id="btnUploadAvatar" class="btn btn-outline-warning btn-sm">
          <i class="fas fa-camera mr-1"></i>
          Upload My Own Picture
        </button>

        <input type="file" id="fileAvatar" accept="image/jpeg,image/png,image/webp" class="d-none">
      </div>

    </div>
  </div>
</div>