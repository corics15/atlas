<?php
  defined('BASEPATH') OR exit('No direct script access allowed');

  $CI =& get_instance();
  $menu = $CI->config->item('atlas_menu');
  $app = $CI->config->item('atlas');

  $userAccessLevel = $CI->session->userdata('access_level');

  /*** avatar */
  $sidebarUserId = $CI->session->userdata('user_id');
  $sidebarUser = $CI->User_model->get($sidebarUserId);
  $accessLevelLabels = [
    'ADMIN'   => 'Administrator',
    'MANAGER' => 'Manager',
    'STAFF'   => 'Staff',
    'VIEWER'  => 'Viewer',
  ];
  $sidebarAccessLabel =  $accessLevelLabels[$userAccessLevel] ?? $userAccessLevel;
  /*** end avatar */

  $hasAccess = function ($item) use ($userAccessLevel) {

    if (empty($item['access'])) {
      return TRUE;
    }

    return in_array(
      $userAccessLevel,
      $item['access'],
      TRUE
    );
  };

  $currentController = $CI->router->fetch_class();
  $currentMethod = $CI->router->fetch_method();

  $currentRoute = str_replace('_', '-', $currentController);

  if ($currentMethod !== 'index') {
    $currentRoute .= '/' . str_replace('_', '-', $currentMethod);
  }

  $isActiveRoute = function ($url) use ($currentRoute) {
    $route = trim($currentRoute, '/');
    $url = trim($url, '/');

    $routeModule = explode('/', $route)[0];
    $urlModule = explode('/', $url)[0];

    return $routeModule === $urlModule;
  };
?>

<aside class="main-sidebar sidebar-dark-olive elevation-4">

  <?php /*** logo */ ?>
  <div class="d-flex justify-content-center mt-2">
    <img src="<?= base_url('assets/images/atlas-sidebar-logo-sm.png'); ?>" alt="ATLAS"
      class="brand-image" style="width: 100%; max-width: 210px; height: 42px; object-fit: contain; opacity: 1;">
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
    <div class="d-flex mb-3 mt-1 pb-1 user-panel">
      <div class="image">

        <?php if (!empty($sidebarUser->avatar)): ?>
          <img  id="sidebarUserAvatar" src="<?= base_url($sidebarUser->avatar); ?>" alt="User Avatar" class="img-circle elevation-2" style="width: 34px; height: 34px; object-fit: contain;">
        <?php else: ?>
          <i class="fas fa-user-circle fa-2x text-purple"></i>
        <?php endif; ?>
      </div>

      <div class="info">
        <span class="d-block text-white">
          <?= htmlspecialchars(
            $this->session->userdata('fullname')
          ); ?>
        </span>

        <div class="text-orange">
          <?= htmlspecialchars($sidebarAccessLabel); ?>
        </div>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
        <?php $sectionHeader = NULL; ?>
        <?php foreach ($menu as $item): ?>

          <?php /*** section header */ ?>
          <?php if (!empty($item['header'])): ?>
            <?php
              $sectionHeader = $item['header'];
              continue;
            ?>
          <?php endif; ?>

          <?php /*** normal menu */ ?>
          <?php if (empty($item['children'])): ?>
            <?php if ($sectionHeader !== NULL): ?>
              <li class="nav-header">
                <?= $sectionHeader; ?>
              </li>
              <?php $sectionHeader = NULL; ?>
            <?php endif; ?>
            <li class="nav-item">
              <a href="<?= atlas_url($item['url']); ?>" class="nav-link <?= $isActiveRoute($item['url']) ? 'active' : ''; ?>">
                <i class="nav-icon <?= $item['icon']; ?>"></i>
                <p><?= $item['title']; ?></p>
              </a>
            </li>

          <?php else: ?>

            <?php
              $accessibleChildren = [];

              foreach ($item['children'] as $child) {
                if ($hasAccess($child)) {
                  $accessibleChildren[] = $child;
                }
              }

              if (empty($accessibleChildren)) {
                continue;
              }

              /*** render section header */
              if ($sectionHeader !== NULL) {
                ?>
                <li class="nav-header">
                  <?= $sectionHeader; ?>
                </li>
                <?php
                $sectionHeader = NULL;
              }

              $open = false;

              foreach ($accessibleChildren as $child) {
                if ($isActiveRoute($child['url'])) {
                  $open = TRUE;
                  break;
                }
              }
            ?>

            <li class="nav-item <?= $open ? 'menu-open' : ''; ?>">
              <a href="#" class="nav-link <?= $open ? 'active' : ''; ?>">
                <i class="nav-icon <?= $item['icon']; ?>"></i>
                <p>
                  <?= $item['title']; ?>
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>

              <ul class="nav nav-treeview">
                <?php foreach ($accessibleChildren as $child): ?>
                  <li class="nav-item">
                    <a href="<?= atlas_url($child['url']); ?>" class="nav-link <?= $isActiveRoute($child['url']) ? 'active' : ''; ?>">
                      <i class="nav-icon <?= $child['icon']; ?>"></i>
                      <p class="<?= $isActiveRoute($child['url']) ? 'font-weight-normal' : ''; ?>">
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