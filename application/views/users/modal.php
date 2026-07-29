<div class="modal fade" id="mdlUser" tabindex="-1" aria-labelledby="mdlUserLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlUserLabel">User Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmUser">
        <input type="hidden" id="hidUserId" name="id">

        <div class="modal-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtUsername">Username</label>
              <input type="text" id="txtUsername" name="username"
                     class="form-control form-control-sm" placeholder="Enter username">
              <small id="errUsername" class="text-danger"></small>
            </div>
            <div class="form-group col-md-6">
              <label for="txtFirstName">First Name</label>
              <input type="text" id="txtFirstName" name="first_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter first name">
              <small id="errFirstName" class="text-danger"></small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="txtLastName">Last Name</label>
              <input type="text" id="txtLastName" name="last_name"
                     class="form-control form-control-sm text-uppercase" placeholder="Enter last name">
              <small id="errLastName" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <label for="selBranch">Branch</label>
              <select id="selBranch" name="branch_id" class="form-control form-control-sm">
                <option value="">Select Branch</option>
                <?php foreach ($branches as $branch): ?>
                  <option value="<?= $branch->id; ?>">
                    <?= htmlspecialchars($branch->branch_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="errBranch" class="text-danger"></small>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" id="btnSaveUser" class="btn btn-sm btn-default">Save User</button>
        </div>
      </form>

    </div>
  </div>
</div>
