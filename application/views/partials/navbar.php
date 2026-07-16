<nav class="main-header navbar navbar-expand navbar-cyan navbar-light">
  <?php /*** left */ ?>
  <ul class="navbar-nav">
    <li class="nav-item">
      <a
        class="nav-link"
        data-widget="pushmenu"
        href="#"
        role="button">
      <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <?php /*** right */ ?>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a
        class="nav-link"
        data-toggle="dropdown"
        href="#">
        <i class="fas fa-user-circle mr-1"></i>
        <?= strtoupper(htmlspecialchars($this->session->userdata('username'))); ?>
      </a>

      <div class="dropdown-menu dropdown-menu-right">
        <span class="dropdown-item-text">
          Signed in
        </span>
        <div class="dropdown-divider"></div>
        <a
          href="<?= atlas_url('auth/logout'); ?>"
          class="dropdown-item"
          id="btnLogout">
          <i class="fas fa-sign-out-alt mr-2"></i>
        Logout
        </a>
      </div>

    </li>
  </ul>
</nav>