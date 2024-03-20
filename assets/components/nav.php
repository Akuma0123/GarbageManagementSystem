<header class="header">
    <div class="header-content">
      <div class="header-logo">
        <h1 class="logo">Garbage Management</h1>
      </div>
      <nav class="header-navigation">
        <a href="/index.php" class="<?= $page == 'home'? 'active-nav': '' ?>">Home</a>
        <a href="/aboutUs.php" class="<?= $page == 'aboutUs'? 'active-nav': '' ?>">About Us</a>
        <?php if(! isset($_SESSION['user']) ) { ?>
        <a href="/login.php">Login</a>
        <a href="/signup.php">Register</a>
        <?php }else { ?>
          <i class="fa-solid fa-bell"></i>
        <a href="requestForm.php" class="<?= $page == 'request'? 'active-nav': '' ?>">Request</a>
          <div class="user">
            <p class="username"><?= $_SESSION['user']['username']; ?> <i class="fa-solid fa-caret-down"></i></p>

            <div id="user-menus">
            <a href="/logout.php">Logout</a>
            </div>
          </div>
          <?php } ?>
          
      </nav>
    </div>
  </header>