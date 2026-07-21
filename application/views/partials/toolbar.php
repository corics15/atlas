<div class="d-flex justify-content-end mb-3">

  <div class="btn-group">
    <button
      class="btn btn-sm btn-secondary dropdown-toggle"
      data-toggle="dropdown">
    Actions
    </button>

    <div class="dropdown-menu dropdown-menu-right">

      <?php /*** new item */ if (!empty($toolbar['new'])) : ?>
      <button
        id="<?= $toolbar['new']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['new']['icon']; ?>"></i>
        <?= $toolbar['new']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** edit item */ if (!empty($toolbar['edit'])) : ?>
      <button
        id="<?= $toolbar['edit']['id']; ?>"
        class="dropdown-item">
      <i class="<?= $toolbar['edit']['icon']; ?>"></i>
      <?= $toolbar['edit']['text']; ?>
      </button>
      <?php endif ?>

      <?php  /*** receive goods */ if (!empty($toolbar['receive'])) : ?>
      <button
        id="<?= $toolbar['receive']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['receive']['icon']; ?>"></i>
        <?= $toolbar['receive']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** post inventory adjustment */  if (!empty($toolbar['post'])) : ?>
      <button
        id="<?= $toolbar['post']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['post']['icon']; ?>"></i>
        <?= $toolbar['post']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** reset password */  if (!empty($toolbar['resetPassword'])) : ?>
      <button
        id="<?= $toolbar['resetPassword']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['resetPassword']['icon']; ?>"></i>
        <?= $toolbar['resetPassword']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** activate item */  if (!empty($toolbar['activate'])) : ?>
      <button
        id="<?= $toolbar['activate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['activate']['icon']; ?>"></i>
        <?= $toolbar['activate']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** deactivate item */  if (!empty($toolbar['deactivate'])) : ?>
      <button
        id="<?= $toolbar['deactivate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['deactivate']['icon']; ?>"></i>
      <?= $toolbar['deactivate']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** inventory inquiry */  if (!empty($toolbar['inventoryInquiry'])) : ?>
      <button
        id="<?= $toolbar['inventoryInquiry']['id']; ?>"
        class="dropdown-item">
        <i class="<?= $toolbar['inventoryInquiry']['icon']; ?>"></i>
        <?= $toolbar['inventoryInquiry']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** print item */  if (!empty($toolbar['print'])) : ?>
      <button
        id="<?= $toolbar['print']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['print']['icon']; ?>"></i>
        <?= $toolbar['print']['text']; ?>
      </button>
      <?php endif ?>

      <?php /*** cancel item */  if (!empty($toolbar['cancel'])) : ?>
      <button
        id="<?= $toolbar['cancel']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['cancel']['icon']; ?>"></i>
      <?= $toolbar['cancel']['text']; ?>
      </button>
      <?php endif ?>

      <div class="dropdown-divider"></div>

      <?php  /*** refresh page */ ?>
      <button
        id="<?= $toolbar['refresh']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['refresh']['icon']; ?>"></i>
      <?= $toolbar['refresh']['text']; ?>
      </button>
    </div>
  </div>

</div>