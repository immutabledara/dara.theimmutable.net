<?PHP 
$conf['injectLoginScripts'] = true; ?>
<!-- Login Modal -->
<div class="modal fade" id="loginmodal" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-m">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Login to your DARA Profile</h5>
				<i class="bi bi-x-square" data-bs-dismiss="modal"></i>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col pb-1 order-2 order-lg-1 d-flex flex-column justify-content-center">
						<div id="user-login-msg" style="text-shadow: -2px 1px 2px #000000ad;"></div>
						<button class="btn btn-dara btn-login mt-3 d-none" onclick="userLoginOut()" id="loginButtonText"></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>