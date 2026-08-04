<!-- <div class="card">
  <div class="card-header">
    <h3 class="card-title">Select Sales Order</h3>
  </div>

  <div class="card-body p-0"> -->
    <table class="table table-hover table-bordered mb-0">
      <thead>
        <tr>
          <th width="140">SO No.</th>
          <th width="120">Order Date</th>
          <th>Customer</th>
          <th width="120" class="text-center">Remaining Items</th>
          <th width="120"></th>
        </tr>
      </thead>

      <tbody>
        <?php if(empty($salesOrders)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted py-4">No Sales Orders available for delivery.</td>
          </tr>

          <?php else: ?>

            <?php foreach($salesOrders as $row): ?>

              <tr>
                <td><?= html_escape($row->so_no); ?></td>

                <td><?= date('m/d/Y', strtotime($row->order_date)); ?></td>

                <td><?= html_escape($row->customer_name); ?></td>

                <td class="text-center"><?= $row->remaining_items; ?></td>

                <td class="text-center">
                  <a href="<?= site_url('delivery-receipts/create/'.$row->id); ?>" class="btn btn-sm btn-default">
                    <i class="fas fa-check"></i>
                    Select
                  </a>
                </td>
              </tr>

            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <!-- </div>
</div> -->