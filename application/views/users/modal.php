<div class="modal fade" id="mdlUser" tabindex="-1">
  <div class="modal-dialog modal-lg">

    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          New User
        </h5>
        <button type="button"
          class="close"
          data-dismiss="modal">
        <span>&times;</span>
        </button>
      </div>

      <form id="frmUser">
        <div class="modal-body">
          <div class="form-group">
            <label>Username</label>
            <input type="text"
              id="txtUsername" name="username"
              class="form-control">
          </div>
          <div class="form-group">
            <label>First Name</label>
            <input type="text"
              id="txtFirstName" name="first_name"
              class="form-control">
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text"
              id="txtLastName" name="last_name"
              class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="submit"
            id="btnSave"
            class="btn btn-primary">
          Save
          </button>
        </div>
      </form>

    </div>

  </div>
</div>