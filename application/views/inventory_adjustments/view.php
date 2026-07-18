<div class="card">
  <div class="card-body">

    <h4>
      <?= isset($inventoryAdjustment)
          ? htmlspecialchars($inventoryAdjustment->adjustment_no)
          : 'New Inventory Adjustment'; ?>
    </h4>

  </div>
</div>