<div class="btn-group">
  <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
  Actions
  </button>

  <div class="dropdown-menu dropdown-menu-right">

    <?php /*** print item */  if (!empty($toolbar['print'])) : ?>
    <button
      id="<?= $toolbar['print']['id']; ?>"
      class="dropdown-item">
        <i class="<?= $toolbar['print']['icon']; ?>"></i>
      <?= $toolbar['print']['text']; ?>
    </button>
    <?php endif ?>

    <?php /*** excel download */ if (!empty($toolbar['excel'])) : ?>
    <button
      id="<?= $toolbar['excel']['id']; ?>"
      class="dropdown-item">
      <i class="<?= $toolbar['excel']['icon']; ?>"></i>
      <?= $toolbar['excel']['text']; ?>
    </button>
    <?php endif ?>

  </div>
</div>