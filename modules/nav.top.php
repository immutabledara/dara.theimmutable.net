<header id="header" class="header fixed-top d-flex align-items-center">
	<div class="d-flex align-items-center justify-content-between">
		<div><i class="bi bi-three-dots-vertical text-gray toggle-sidebar-btn"></i></div>
		<?PHP if (stripos("".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."", "encyclopedia")){
			#echo '<a href="https://encyclopedia.dara.global/" class="logo d-flex align-items-center toggle-sidebar">';
			echo '<a href="#" class="logo d-flex align-items-center toggle-sidebar">';
			echo '<img src="/assets/images/logo.png" width=26 height=26 alt=""><img src="/assets/images/daraencyclopedia.png" style="padding-left: 2px;" height=18 alt=""></a>';
		}elseif (stripos("".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."", "deathrow")){
			#echo '<a href="https://deathrow.dara.global/" class="logo d-flex align-items-center toggle-sidebar">';
			echo '<a href="#" class="logo d-flex align-items-center toggle-sidebar">';
			echo '<img src="/assets/images/logo.png" width=26 height=26 alt=""><img src="/assets/images/daradeathrow.png" style="padding-left: 2px;" height=18 alt=""></a>';
		}else{
			echo '<a href="#" class="logo d-flex align-items-center toggle-sidebar">';
			echo '<img src="/assets/images/logo.png" width=26 height=26 alt=""><img src="/assets/images/projectdara.png" style="padding-left: 2px;" height=18 alt=""></a>';
		}

		?>
	</div>
	<?PHP
		if (stripos("".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."", "deathrow")===false){
			echo '<div class="search-bar">';
			echo '<div class="search-form d-flex align-items-center">';
			if (stripos("".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."", "encyclopedia")!==false){
				echo "<input type='text' name='encyclopediaSearch' class='form-control input-sm' autofocus='autofocus' id='encyclopediaSearch' placeholder='Search Encyclopedia Archives'/>";
				echo "<button type='submit' title='Search' id='btn-encyclopediaSearch'><i class='bi bi-search'></i></button>";
			}elseif (stripos("".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."", "deathrow")!==false){
				echo "<input type='text' name='deathrowSearch' class='form-control input-sm' autofocus='autofocus' id='deathrowSearch' placeholder='Search Wikipedia Deathrow'/>";
				echo "<button type='submit' title='Search' id='btn-deathrowSearch'><i class='bi bi-search'></i></button>";
			}else{
				echo "<input type='text' name='publicDARASearch' class='form-control input-sm' autofocus='autofocus' id='publicDARASearch' placeholder='Search Public Archives'/>";
				echo "<button type='submit' title='Search' id='btn-publicDARASearch' onClick='searchPublicRecords();'><i class='bi bi-search'></i></button>";
			}
			echo '</div>';
			echo '</div>';
		}
		?>
	<nav class="header-nav ms-auto">
		<ul class="d-flex align-items-center">
			<li class="nav-item d-block d-lg-none">
				<a class="nav-link nav-icon search-bar-toggle " href="#">
					<i class="bi bi-search"></i>
				</a>
			</li>
			<?PHP
			if (!in_array(basename($_SERVER['PHP_SELF']), array("wiki.php", "encyclopedia.php"))){
				if ((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == TRUE)){ ?>
					<li class="nav-item dropdown pe-4">
						<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
							<div>
							<i class="rounded-circle" id="walleticon"></i>
							</div>
							<span class="d-md-block dropdown-toggle ps-2 nav-loggedinaddress">Logged in as <?=$_SESSION['walletAddress_s']?></span>
						</a>
						<ul class="dropdown-menu dropdown-menu-end profile pt-0">
							<li class="dropdown-header loggedinaddress"></i><span>Logged in as <?=$_SESSION['walletAddress_s']?></span></li>
							<li class="d-none"><p class="text-muted align-middle text-center p-0 m-0" style="font-size: 1rem;"><?=$_SESSION['walletAddress']?></p></li>
							<li><a class="dropdown-item d-flex align-items-center" href="/index.php"><i class="bi bi-card-list"></i><span>Public Archives</span></a></li>
							<?PHP if ($whereAmI == "dashboard"){ ?>
								<li><a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#DARACredits"><i class="bi bi-bank2"></i><span>Manage Credits</span></a></li>
								<li><a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#DARAProfile"><i class="bi bi-person"></i><span>Your Profile</span></a></li>
								<li><hr class="dropdown-divider"></li>
							<?PHP } ?>
							<li><a class="dropdown-item d-flex align-items-center" href="/login.php"><i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>
					  </ul>
					</li>
				<?PHP }else{ ?>
					<a class="nav-link nav-profile d-flex align-items-center pe-4" href="#" onclick="userLoginOut()" data-bs-toggle="modal" data-bs-target="#loginmodal">Sign in</a>
				<?PHP }	
			}?>
		</ul>
	</nav>
</header>
