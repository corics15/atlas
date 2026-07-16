<div class="d-flex justify-content-end mb-3">

  <div class="btn-group">
    <button
      class="btn btn-sm btn-secondary dropdown-toggle"
      data-toggle="dropdown">
    Actions
    </button>

    <div class="dropdown-menu dropdown-menu-right">
      <button
        id="<?= $toolbar['edit']['id']; ?>"
        class="dropdown-item">
      <i class="<?= $toolbar['edit']['icon']; ?>"></i>
      <?= $toolbar['edit']['text']; ?>
      </button>

      <?php if (!empty($toolbar['receive'])) : ?>
      <button
        id="<?= $toolbar['receive']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['receive']['icon']; ?>"></i>
        <?= $toolbar['receive']['text']; ?>
      </button>
      <?php endif ?>

      <?php if (!empty($toolbar['resetPassword'])) : ?>
      <button
        id="<?= $toolbar['resetPassword']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['resetPassword']['icon']; ?>"></i>
        <?= $toolbar['resetPassword']['text']; ?>
      </button>
      <?php endif ?>

      <?php if (!empty($toolbar['activate'])) : ?>
      <button
        id="<?= $toolbar['activate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['activate']['icon']; ?>"></i>
        <?= $toolbar['activate']['text']; ?>
      </button>
      <?php endif ?>

      <?php if (!empty($toolbar['deactivate'])) : ?>
      <button
        id="<?= $toolbar['deactivate']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['deactivate']['icon']; ?>"></i>
      <?= $toolbar['deactivate']['text']; ?>
      </button>
      <?php endif ?>

      <?php if (!empty($toolbar['print'])) : ?>
      <button
        id="<?= $toolbar['print']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['print']['icon']; ?>"></i>
        <?= $toolbar['print']['text']; ?>
      </button>
      <?php endif ?>

      <?php if (!empty($toolbar['cancel'])) : ?>
      <button
        id="<?= $toolbar['cancel']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['cancel']['icon']; ?>"></i>
      <?= $toolbar['cancel']['text']; ?>
      </button>
      <?php endif ?>

      <div class="dropdown-divider"></div>

      <button
        id="<?= $toolbar['refresh']['id']; ?>"
        class="dropdown-item">
          <i class="<?= $toolbar['refresh']['icon']; ?>"></i>
      <?= $toolbar['refresh']['text']; ?>
      </button>
    </div>
  </div>

</div>