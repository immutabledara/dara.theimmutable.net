<aside id="sidebar" class="sidebar">
	<ul class="sidebar-nav" id="sidebar-nav">
	<?PHP if ((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == TRUE)){ ?>
				<li class="nav-item"><a class="nav-link <?PHP if ($whereAmI == "dashboard"){ echo ""; } else { echo "collapsed"; }?>" href="/dashboard.php"><i class="bi bi-command"></i><span>Dashboard</span></a></li>
				<li class="nav-item"><a class="nav-link <?PHP if ($whereAmI == "index"){ echo ""; } else { echo "collapsed"; }?>" href="/index.php"><i class="bi bi-card-list"></i><span>Public Archives</span></a></li>
				<?PHP
					if ($whereAmI == "dashboard"){
						echo '<li class="nav-item d-none"><a class="nav-link collapsed" href="#"><i class="bi bi-infinity"></i><span>DARA Social</span></a></li>';
						echo '<li class="nav-heading"><br></li>';
						echo '<li class="nav-item"><a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#manualUploader"><i class="bi bi-upload"></i><span>File Upload</span></a></li>';
						echo '<li class="nav-item d-none"><a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#urlSaver"><i class="bi bi-save"></i><span>Store URL</span></a></li>';
						if ((isset($_SESSION['zwiAdmin']))&&($_SESSION['zwiAdmin'] == TRUE)){
							echo '<li class="nav-item"><a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#ZWISaver"><i class="bi bi-mortarboard"></i><span>ZWI Saver</span></a></li>';
						}
					}
				?>
				<li class="nav-heading"><br></li>
				<?php 
					if ($whereAmI == "dashboard"){
						echo '<li class="nav-item"><a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#DARAProfile"><i class="bi bi-person"></i><span>Your Profile</span></a></li>';
						echo '<li class="nav-item"><a class="nav-link collapsed" href="#" data-bs-toggle="modal" data-bs-target="#DARACredits"><i class="bi bi-currency-exchange"></i><span>Manage Credits</span></a></li>';
					}
				?>
				<li class="nav-item"><a class="nav-link collapsed" href="/login.php"><i class="bi bi-box-arrow-in-right"></i><span>Sign Out</span></a></li>
				<li class="nav-heading"><br></li>
				<li class="nav-item"><a class="nav-link collapsed" href="https://encyclopedia.dara.global"><i class="bi bi-mortarboard"></i><span>DARA Encyclopedia</span></a></li>
				<li class="nav-item"><a class="nav-link collapsed" href="https://deathrow.dara.global"><i class="bi bi-calendar-x"></i><span>Wikipedia Deathrow</span></a></li>
				<li class="nav-item"><a class="nav-link collapsed" href="https://gutenberg.dara.global"><i class="bi bi-book"></i><span>Project Gutenberg</span></a></li>
				<li class="nav-heading"><br></li>
				<?PHP include($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.links.php'); ?>
	<?PHP }else{ ?>
				<li class="nav-item"><a class="nav-link collapsed" href="/login.php"><i class="bi bi-box-arrow-in-right"></i><span>Dashboard</span></a></li>
				<li class="nav-item"><a class="nav-link" href="/index.php"><i class="bi bi-card-list"></i><span>Public Archives</span></a></li>
				<?PHP 
				echo '<li class="nav-heading"><br></li>';
				echo '<li class="nav-item"><a class="nav-link collapsed" href="https://encyclopedia.dara.global"><i class="bi bi-mortarboard"></i><span>DARA Encyclopedia</span></a></li>';
				echo '<li class="nav-item"><a class="nav-link collapsed" href="https://deathrow.dara.global"><i class="bi bi-calendar-x"></i><span>Wikipedia Deathrow</span></a></li>';
				echo '<li class="nav-item"><a class="nav-link collapsed" href="https://gutenberg.dara.global"><i class="bi bi-book"></i><span>Project Gutenberg</span></a></li>';
				echo '<li class="nav-heading"><br></li>';
				include($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.links.php'); ?>
	<?PHP }	?>
	</ul>
<?PHP include($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.footer.php'); ?>
</aside>
