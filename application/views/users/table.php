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