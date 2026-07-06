<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row mb-3">

          <div class="col-md-4">
            <form>
              <div class="row mb-3">
                <div class="col-auto">
                  <input
                    type="text"
                    name="keyword"
                    value="<?= htmlspecialchars($keyword ?? ''); ?>"
                    class="form-control"
                    placeholder="Search...">
                </div>
                <div class="col-auto">
                  <button
                    type="submit"
                    class="btn btn-secondary btn-block">
                  Search
                  </button>
                </div>
              </div>
            </form>
          </div>

        </div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover">
            <thead>
              <tr>
                <th width="80">ID</th>
                <th>Username</th>
                <th>Name</th>
                <th width="120">Status</th>
                <th width="120">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)) : ?>
              <?php foreach ($users as $user) : ?>
              <tr>
                <td><?= $user->id; ?></td>
                <td><?= htmlspecialchars($user->username); ?></td>
                <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name); ?></td>
                <td>
                  <?= $user->is_active ? 'Active' : 'Inactive'; ?>
                </td>
                <td>
                  <button
                    class="btn btn-sm btn-primary">
                  Edit
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php else : ?>
              <tr>
                <td colspan="5" class="text-center">
                  No records found.
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $this->load->view('users/modal'); ?>