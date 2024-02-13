<?PHP
$whereAmI = "dashboard";
$tpl['head.title'] = 'Project DARA - Dashboard';
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');

?>
<section class="dara-background overflow-auto">
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
	<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.left.php'); ?>
	<main id="main" class="main">
		<div class="pagetitle">
			<h1>&nbsp;</h1>
		</div>
		<!-- End Page Title -->
		<section class="section dashboard">
			<div class="row">
				<!-- Center Stage -->
				<div class="col-xxl-9 col-lg-12">
					<div class="row">
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/dash.topcards.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.topup.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.searchresults.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.showipfs.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.upload.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.signhash.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.profile.php'); ?>
						<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.manupload.php'); ?>
						<?PHP if ((isset($_SESSION['zwiAdmin']))&&($_SESSION['zwiAdmin']==true)){ require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.zwisaver.php'); } ?>
						<div class="col-12">
							<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/inc.useruploads.php'); ?>
						</div>
					</div>
				</div>
				<div class="col-xxl-3 col-lg-12">
					<!-- Recent Content Top Right -->
					<div class="card" sdtyle="min-width: 300px;">
					<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/inc.recentpublic.php'); ?>
					</div>
				</div>
			</div>
		</section>
	</main>
<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.footer.php');
?>
