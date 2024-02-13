<?PHP
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/gatekeeper.php');
$conf['injectLoginScripts'] = true;
if ((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] != FALSE)){
	flushSession();
}else{
	$whereAmI="login";
	$tpl['head.title'] = 'Project DARA - Login';
	require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.htmlheaders.php');
	?>
	<section class="index-background overflow-auto">
		<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/nav.top.php'); ?>
		<main id="main" class="main">
			<section class="section dashboard">
				<div class="row">
					<div class="col-lg-12">
						<div class="row">
							<?PHP require_once($_SERVER['DOCUMENT_ROOT'].'/modules/modal.login.php'); ?>
						</div>
					</div>
				</div>
			</section>
		</main>

<?PHP
	require_once($_SERVER['DOCUMENT_ROOT'].'/modules/tpl.footer.php');
?>
<script>
	$(document).ready(function(){
		$("#loginmodal").modal('show');
		showSelectors("#loginButtonText");
	});
</script>
<?php
}
?>
