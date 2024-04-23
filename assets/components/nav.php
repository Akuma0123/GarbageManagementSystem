<header class="header">
    <div class="header-content">
      <div class="header-logo">
        <h1 class="logo">Garbage Management</h1>
      </div>
      <nav class="header-navigation">
        
        <a href="/index.php" class="<?= $page == 'home'? 'active-nav': '' ?>">Home</a>
        <a href="/aboutUs.php" class="<?= $page == 'aboutUs'? 'active-nav': '' ?>">About Us</a>
        <?php if(! isset($_SESSION['user_id']) ) { ?>
          <a href="/login.php">Login</a>
          <a href="/signup.php">Register</a>
          <?php }else { ?>
            <a href="notification.php" class="<?= $page == 'notification'? 'active-nav': '' ?>"> <i class="fa-solid fa-bell"></i></a>

            <a href="myRequest.php" class="<?= $page == 'myRequest'? 'active-nav': '' ?>"><i class="fa-solid fa-user"></i></a>
        <a href="requestForm.php" class="<?= $page == 'request'? 'active-nav': '' ?>">Request</a>
          <div class="user">
          <p class="username"><?= $_SESSION['username']; ?> <i class="fa-solid fa-caret-down"></i></p>

         


            <div id="user-menus"> 
            <a href="/logout.php">Logout</a>
            </div>
          </div> 
          <?php } ?>
          
      </nav>
    </div>
  </header>