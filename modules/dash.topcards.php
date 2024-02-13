<div class="col-xxl-4 col-md-4">
	<div class="card info-card daratopcards gasfee-card">
		<div class="card-body">
			<h5 class="card-title">BSC TXN Fee (gas)</h5>
			<div class="d-flex align-items-center">
				<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
					<i class="bi bi-cloud-upload"></i>
				</div>
				<div class="ps-3 card-contents">
					<h5 class="card-title d-none">BSC TXN Fee (gas): </h5>
					<h6><?=getConfValue('daraTxnFee');?> $DARA</h6>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-xxl-4 col-md-4" data-bs-toggle="modal" data-bs-target="#DARACredits">
	<div class="card info-card daratopcards creditbalance-card">
		<div class="card-body">
			<h5 class="card-title">Platform Credits Balance</h5>
			<div class="d-flex align-items-center">
				<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
					<i class="bi bi-bank"></i>
				</div>
				<div class="ps-3 card-contents">
					<h5 class="card-title d-none">Credits Balance: </h5>
					<h6 id="creditsBalance"></h6>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-xxl-4 col-md-4" style="cursor:default!important;">
	<div class="card info-card daratopcards walletbalance-card">
		<div class="card-body">
			<h5 class="card-title">Token Holdings</h5>
			<div class="d-flex align-items-center">
				<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
					<i class="bi bi-wallet"></i>
				</div>
				<div class="ps-3 card-contents">
					<h5 class="card-title d-none">Token Holdings: </h5>
					<h6><span id="liveBalance"></span></h6>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() { 
	getCreditsBalance();
	getWalletBalance();
	const topcardsInterval = setInterval(function() { 
		getCreditsBalance();
		getWalletBalance();
	}, 60000);
});
</script>