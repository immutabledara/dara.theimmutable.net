<div class='modal fade' id='DARACredits' tabindex='-1'>
	<div class='modal-dialog modal-dialog-centered modal-xl'>
		<div class='modal-content'>
			<div class='modal-header'>
				<h5 class='modal-title'>Your DARA Platform Credits</h5>
				<i class="bi bi-x-square" data-bs-dismiss="modal"></i>
			</div>
			<div class='modal-body'>
				<div id='accounting'>
					<div id='topUpButtonArea'>
						<input type='number' id='topupAmount' name='topupAmount' min=10 value=10>
						<button type='button' id='topupButton' class='btn btn-sm btn-dark btn-outline-gold'>Top up Credits</button><span id='topupButtonRight'></span>
					</div>
					<div id='topUpArea'>Current BSC Gas (transaction) fee is <b><?=getConfValue('daraTxnFee');?> $DARA</b></div>
				</div>
			</div>
		</div>
	</div>
</div>