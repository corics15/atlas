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