<div class="d-flex justify-content-end mb-3">

  <div class="btn-group">
    <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">Actions</button>

    <div class="dropdown-menu dropdown-menu-right">

      <?php /*** new item */ if (!empty($toolbar['new'])) : ?>
      <button
        id="<?= $toolbar['new']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['new']['icon']; ?> mr-2"></i>
        <?= $toolbar['new']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** edit item */ if (!empty($toolbar['edit'])) : ?>
      <button
        id="<?= $toolbar['edit']['id']; ?>"
        class="dropdown-item">
      <i class="<?= $toolbar['edit']['icon']; ?> mr-2"></i>
      <?= $toolbar['edit']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** post item*/  if (!empty($toolbar['post'])) : ?>
      <button
        id="<?= $toolbar['post']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['post']['icon']; ?> mr-2"></i>
        <?= $toolbar['post']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** reset password */  if (!empty($toolbar['resetPassword'])) : ?>
      <button
        id="<?= $toolbar['resetPassword']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['resetPassword']['icon']; ?> mr-2"></i>
        <?= $toolbar['resetPassword']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** activate item */  if (!empty($toolbar['activate'])) : ?>
      <button
        id="<?= $toolbar['activate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['activate']['icon']; ?> mr-2"></i>
        <?= $toolbar['activate']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** deactivate item */  if (!empty($toolbar['deactivate'])) : ?>
      <button
        id="<?= $toolbar['deactivate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['deactivate']['icon']; ?> mr-2"></i>
        <?= $toolbar['deactivate']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** inventory inquiry */  if (!empty($toolbar['inventoryInquiry'])) : ?>
      <button
        id="<?= $toolbar['inventoryInquiry']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['inventoryInquiry']['icon']; ?> mr-2"></i>
        <?= $toolbar['inventoryInquiry']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** stock ledger */  if (!empty($toolbar['stockLedger'])) : ?>
      <button
        id="<?= $toolbar['stockLedger']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['stockLedger']['icon']; ?> mr-2"></i>
        <?= $toolbar['stockLedger']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** print item */  if (!empty($toolbar['print'])) : ?>
      <button
        id="<?= $toolbar['print']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['print']['icon']; ?> mr-2"></i>
        <?= $toolbar['print']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** cancel item */  if (!empty($toolbar['cancel'])) : ?>
      <button
        id="<?= $toolbar['cancel']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['cancel']['icon']; ?> mr-2"></i>
      <?= $toolbar['cancel']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** excel download */ if (!empty($toolbar['excel'])) : ?>
      <button
        id="<?= $toolbar['excel']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['excel']['icon']; ?> mr-2"></i>
        <?= $toolbar['excel']['text']; ?>
      </button>
      <?php endif ?>      

      <?php  /*** receive items */ if (!empty($toolbar['receive'])) : ?>
      <button
        id="<?= $toolbar['receive']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['receive']['icon']; ?> mr-2"></i>
        <?= $toolbar['receive']['text']; ?>
      </button>
      <?php endif ?>      

      <?php /*** create DR item*/  if (!empty($toolbar['create-dr'])) : ?>
      <button
        id="<?= $toolbar['create-dr']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['create-dr']['icon']; ?> mr-2"></i>
        <?= $toolbar['create-dr']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** create item*/  if (!empty($toolbar['create'])) : ?>
      <button
        id="<?= $toolbar['create']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['create']['icon']; ?> mr-2"></i>
        <?= $toolbar['create']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** transaction details set on check-vouchers*/  if (!empty($toolbar['transactions'])) : ?>
      <a
        href="<?= base_url($toolbar['transactions']['url']) ?>"
        id="<?= $toolbar['transactions']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['transactions']['icon']; ?> mr-2"></i>
        <?= $toolbar['transactions']['text']; ?>
      </a>
      <?php endif ?>

      <div class="dropdown-divider"></div>

      <?php  /*** refresh page */ ?>
      <a
        href="<?= base_url($toolbar['refresh']['url']) ?>"
        id="<?= $toolbar['refresh']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['refresh']['icon']; ?> mr-2"></i>
      <?= $toolbar['refresh']['text']; ?>
      </a>
    </div>
  </div>

</div>