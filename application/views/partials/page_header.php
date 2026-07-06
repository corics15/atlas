<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">

                <h1 class="m-0"><?= $pageTitle; ?></h1>

            </div>

            <div class="col-sm-6 text-right">

                <?php if (!empty($pageButton)) : ?>

                    <button
                        id="<?= $pageButton['id']; ?>"
                        class="btn btn-primary">

                        <i class="<?= $pageButton['icon']; ?>"></i>

                        <?= $pageButton['text']; ?>

                    </button>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>