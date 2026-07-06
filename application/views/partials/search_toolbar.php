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
      <i class="fas fa-search"></i>
      Search
      </button>
    </div>
  </div>
</form>