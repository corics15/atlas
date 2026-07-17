<?php
  defined('BASEPATH') OR exit('No direct script access allowed');

  $CI =& get_instance();
  $menu = $CI->config->item('atlas_menu');
  $app = $CI->config->item('atlas');

  $currentController = $CI->router->fetch_class();
  $currentMethod = $CI->router->fetch_method();

  $currentRoute = $currentController;

  if ($currentMethod !== 'index') {
    $currentRoute .= '/' . $currentMethod;
  }
?>

<aside class="main-sidebar sidebar-dark-olive elevation-4">

  <?php /*** logo */ ?>
  <div class="d-flex justify-content-center mt-2">
    <i class="fas fa-cubes fa-2x text-olive"> </i>
  </div>

  <?php /*** brand */ ?>
  <a
    href="<?= atlas_url('dashboard'); ?>"
    class="brand-link text-center">

    <span class="brand-text font-weight-light">
      <strong>ATLAS</strong>
      <small class="d-block text-muted">
        ERP Suite v<?= $app['app_version']; ?>
      </small>
    </span>

  </a>

  <div class="sidebar">

    <?php /*** user */ ?>
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <i class="fas fa-user-circle fa-2x text-light"></i>
      </div>

      <div class="info">
        <span class="d-block text-white">
          <?= htmlspecialchars(
            $this->session->userdata('fullname')
          ); ?>
        </span>

        <small class="text-muted">
          Administrator
        </small>
      </div>
    </div>

    <nav class="mt-2">
      <ul
        class="nav nav-pills nav-sidebar flex-column"
        data-widget="treeview"
        role="menu"
        data-accordion="false">
        <?php foreach ($menu as $item): ?>

          <?php /*** section header */ ?>
          <?php if (!empty($item['header'])): ?>
            <li class="nav-header">
              <?= $item['header']; ?>
            </li>
            <?php continue; ?>
          <?php endif; ?>

          <?php /*** normal menu */ ?>
          <?php if (empty($item['children'])): ?>
            <li class="nav-item">
              <a
                href="<?= atlas_url($item['url']); ?>"
                class="nav-link <?= ($currentRoute == $item['url']) ? 'active' : ''; ?>">
                <i class="nav-icon <?= $item['icon']; ?>"></i>
                <p class="<?= ($currentRoute == $item['url']) ? 'font-weight-bold' : ''; ?>">
                  <?= $item['title']; ?>
                </p>
              </a>
            </li>

            <?php else: ?>

            <?php
              $open = false;
              foreach ($item['children'] as $child) {
                if ($currentRoute == $child['url']) {
                  $open = true;
                  break;
                }
              }
              ?>
            <li class="nav-item <?= $open ? 'menu-open' : ''; ?>">
              <a
                href="#"
                class="nav-link <?= $open ? 'active' : ''; ?>">
                <i class="nav-icon <?= $item['icon']; ?>"></i>
                <p>
                  <?= $item['title']; ?>
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>

              <ul class="nav nav-treeview">
                <?php foreach ($item['children'] as $child): ?>
                  <li class="nav-item">
                    <a
                      href="<?= atlas_url($child['url']); ?>"
                      class="nav-link <?= ($currentRoute == $child['url']) ? 'active' : ''; ?>">
                      <i class="nav-icon <?= $child['icon']; ?>"></i>
                      <p class="<?= ($currentRoute == $child['url']) ? 'font-weight-normal' : ''; ?>">
                        <?= $child['title']; ?>
                      </p>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>

            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</aside>