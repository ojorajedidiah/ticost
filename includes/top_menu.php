<nav class="main-header navbar navbar-expand-md navbar-light navbar-white" style="font-weight: bold;background-color: azure;"><!--- #AAFCB8 -->
  <div class="container">
    <a href="home.php" class="navbar-brand">
      <img src="assets/img/logo.png" alt="TICOST Logo" class="mr-2" style="width: 85px; height: 40px;">
      <!-- <span class="brand-text font-weight-light" style="font-family: 'Lucy Said Ok', Courier, monospace; font-size:large;"><b>TICOST</b></span> -->
    </a>

    <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
          aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
    <div class="collapse navbar-collapse order-3" id="navbarCollapse">
      <ul class="navbar-nav">
        <!-- <li class="nav-item">
          <a href="" class="nav-link">Deals</a>
        </li>
        <li class="nav-item">
          <a href="" class="nav-link">Clients</a>
        </li> -->
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Fashion</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="home.php?p=deals" class="dropdown-item">Deals</a></li>
            <li><a href="home.php?p=clients" class="dropdown-item">Clients</a></li>
            <li><a href="" class="dropdown-item">Status Sheet</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="home.php?p=sendNote" class="nav-link">Send Email</a>
        </li>
        <li class="nav-item">
          <a href="home.php?p=users" class="nav-link">Users</a>
        </li>
        
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Dikins</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="home.php?p=dikins_list" class="dropdown-item">Product Lising</a></li>
            <li><a href="home.php?p=dikins_list" class="dropdown-item">Incoming Products</a></li>
            <li><a href="home.php?p=dikins_list" class="dropdown-item">Outgoing Products</a></li>
            <li><a href="home.php?p=dikins_list" class="dropdown-item">Status Sheet</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Reports</a>
          <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="" class="dropdown-item">Deals (WIPs)</a></li>
            <li><a href="" class="dropdown-item">Fulfilled Deals</a></li>
            <!-- <li><a href="" class="dropdown-item">Deals Reports</a></li> -->
            <li><a href="" class="dropdown-item">Clients Listings</a></li>
          </ul>
        </li>
      </ul>
    </div>
    
    <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Logout</a>
      </li>
    </ul>
  </div>
</nav>

