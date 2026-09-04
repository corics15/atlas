<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-8 col-sm-6">
        <h1 class="m-0 atlas-page-title"><?= $pageTitle; ?></h1>
      </div>
      <div class="col-4 col-sm-6 text-right">
        <?php if (!empty($pageButton)) : ?>
          <button id="<?= $pageButton['id']; ?>" class="btn btn-sm btn-link text-nowrap">
            <i class="<?= $pageButton['icon']; ?> mr-1 mr-sm-2"></i>
            <span class="d-none d-sm-inline"><?= $pageButton['text']; ?></span>
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>