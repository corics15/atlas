<form data-atlas-filter>

  <div class="row align-items-end">
    <div class="col-md-3">
      <div class="form-group mb-md-0">

        <label for="dtAsOfDate">As of Date</label>
        <input type="date" id="dtAsOfDate" name="as_of_date" class="form-control form-control-sm" value="<?= $as_of_date ?>">

      </div>
    </div>


    <div class="col-md-5">
      <div class="form-group mb-md-0">

        <label for="selCustomer">Customer</label>
        <select id="selCustomer" name="customer_id" class="form-control form-control-sm">
          <option value="0">All Customers</option>
            <?php foreach ($customers as $customer): ?>
              <option value="<?= (int)$customer->id ?>" <?= (int)$customer_id === (int)$customer->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($customer->customer_name) ?>
              </option>
            <?php endforeach; ?>
        </select>
      </div>
    </div>


    <div class="col-md-4">
      <button type="submit" class="btn btn-sm btn-default">
        <i class="fas fa-search mr-1"></i>
        Search
      </button>
    </div>
  </div>
</form>