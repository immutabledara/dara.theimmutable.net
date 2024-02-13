<?php
$whereAmI = "index";
$tpl['head.title'] = 'Project DARA';
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');
?>
<section class="index-background overflow-auto">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.php'); ?>
	<main id="main" class="main">
		<div class="pagetitle">
			<h1>Latest Stored Content</h1>
		</div>
		<!-- End Page Title -->
		<section class="section dashboard">
			<div class="row">
				<!-- Center Stage -->
				<div class="col-lg-12">
					<div class="row">
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.searchresults.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.showipfs.php'); ?>
						<div class="col-12">
							<div class="card recent-activity overflow-auto">
							<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/inc.recentpublic.php'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.footer.php');
?>
