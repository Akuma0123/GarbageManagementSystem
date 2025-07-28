<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php $page = $page ?? ''; // prevent undefined variable warning ?>
<header class="header">
  <div class="header-content">
    <div class="header-logo">
      <h1 class="logo">Garbage Management</h1>
    </div>
    <nav class="header-navigation">
      <a href="/index.php" class="nav-link <?= $page == 'home' ? 'active-nav' : '' ?>">Home</a>
      <a href="/aboutUs.php" class="nav-link <?= $page == 'aboutUs' ? 'active-nav' : '' ?>">About Us</a>
      <?php if (isset($_SESSION['user_id'])) { ?>
        <a href="notification.php" class="nav-link <?= $page == 'notification' ? 'active-nav' : '' ?>"><i class="fa-solid fa-bell"></i></a>
        <a href="myRequest.php" class="nav-link <?= $page == 'myRequest' ? 'active-nav' : '' ?>"><i class="fa-solid fa-user"></i></a>
        <a href="requestForm.php" class="nav-link <?= $page == 'request' ? 'active-nav' : '' ?>">Request</a>
        <div class="user">
          <p class="username nav-link"><?= htmlspecialchars($_SESSION['username']); ?> <i class="fa-solid fa-caret-down"></i></p>
          <div id="user-menus">
            <a href="/logout.php">Logout</a>
          </div>
        </div>
      <?php } elseif (isset($_SESSION['driver_id'])) { ?>
        <a href="driver_dashboard.php" class="nav-link <?= $page == 'driver_dashboard' ? 'active-nav' : '' ?>"><i class="fa-solid fa-truck"></i> Dashboard</a>
        <div class="user">
          <p class="username nav-link"><?= htmlspecialchars($_SESSION['driver_name']); ?> <i class="fa-solid fa-caret-down"></i></p>
          <div id="user-menus">
            <a href="/logout.php">Logout</a>
          </div>
        </div>
      <?php } else { ?>
        <div class="dropdown" id="loginDropdown">
          <a href="#" class="dropbtn nav-link" id="loginDropdownBtn">Login <i class="fa-solid fa-caret-down"></i></a>
          <div class="dropdown-content">
            <a href="/login.php">User Login</a>
            <a href="/driver_login.php">Driver Login</a>
          </div>
        </div>
        <div class="dropdown" id="registerDropdown">
          <a href="#" class="dropbtn nav-link" id="registerDropdownBtn">Register <i class="fa-solid fa-caret-down"></i></a>
          <div class="dropdown-content">
            <a href="/signup.php">User Register</a>
            <a href="/driver_signup.php">Driver Register</a>
          </div>
        </div>
      <?php } ?>
    </nav>
  </div>

  <!-- Style and Script are fine as-is -->
  <style>
    .header-navigation {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .nav-link {
      margin: 0 8px;
      font-weight: 600;
      color: #222;
      text-decoration: none;
      transition: color 0.2s;
    }
    .nav-link:hover, .active-nav {
      background: #222;
      color: #fff !important;
      border-radius: 12px;
      padding: 6px 18px;
    }
    .dropdown {
      position: relative;
      display: inline-block;
      z-index: 10;
    }
    .dropbtn {
      cursor: pointer;
      display: flex;
      align-items: center;
      z-index: 11;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      top: 100%;
      background-color: #fff;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
      z-index: 100;
      border-radius: 8px;
      margin-top: 4px;
      padding: 8px 0;
    }
    .dropdown-content a {
      color: #333;
      padding: 12px 20px;
      display: block;
      font-weight: 700;
      transition: background 0.2s;
    }
    .dropdown-content a:hover {
      background-color: #f0f0f0;
    }
    .dropdown.open .dropdown-content {
      display: block;
    }
    .dropdown .fa-caret-down {
      margin-left: 4px;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function setupDropdown(btnId, dropdownId, otherDropdownId) {
        const btn = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);
        const otherDropdown = otherDropdownId ? document.getElementById(otherDropdownId) : null;
        if (!btn || !dropdown) return;

        btn.addEventListener('click', function (e) {
          e.preventDefault();
          if (otherDropdown) otherDropdown.classList.remove('open');
          dropdown.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
          if (!dropdown.contains(e.target) && e.target !== btn) {
            dropdown.classList.remove('open');
          }
        });
      }

      setupDropdown('loginDropdownBtn', 'loginDropdown', 'registerDropdown');
      setupDropdown('registerDropdownBtn', 'registerDropdown', 'loginDropdown');
    });
  </script>
</header>
