<div class="modal fade" id="showIPFS" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-xxl">
		<div class="modal-content embeded-ipfs">
			<div class="modal-header"><h6 class="modal-title"></h6></span><i class="bi bi-x-square" data-bs-dismiss="modal"></i></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
function showIPFSModal(requestCIDHash=''){
	var targetModal = document.getElementById('showIPFS');
	var targetHeader = targetModal.getElementsByClassName('modal-title');
	var targetBody = targetModal.getElementsByClassName('modal-body');
	var targetFooter = targetModal.getElementsByClassName('modal-footer');
	axios.post( "/backend.php", { request: 'getIPFSData', qtzCIDHash: requestCIDHash}, { headers: { "Content-Type": "application/json" } })
	.then(function(response) {
		if (response.status == 200 && response['data']['status'] == 'OK') {
			var IPFSModalContent = response['data']['results'][0];
			targetHeader[0].innerHTML = IPFSModalContent['qtzhead'];
			targetBody[0].innerHTML = IPFSModalContent['qtzbody'];
			targetFooter[0].innerHTML = IPFSModalContent['qtzfooter'];
			$(targetModal).modal('show');
		} else {
			targetHeader[0].innerHTML = 'Error';
			targetBody[0].innerHTML = 'Error: Invalid Request';
			$(targetModal).modal('show');
		}
	}).catch(
		function (error) {
			targetHeader[0].innerHTML = 'Error';
			targetBody[0].innerHTML = 'Error: Invalid Request';
			$(targetModal).modal('show');
			console.log(error);
		}
	)
};
</script>
