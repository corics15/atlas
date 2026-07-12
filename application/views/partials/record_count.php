<div class="font-smr text-success mb-2">
  <?php
    $count = (int)($recordCount ?? 0);
    echo number_format($count) . ' ';
    echo ($count === 1)
        ? 'record found.'
        : 'records found.';
  ?>
</div>