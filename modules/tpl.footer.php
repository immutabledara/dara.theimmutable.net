<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.1/js/bootstrap.bundle.min.js" integrity="sha512-1TK4hjCY5+E9H3r5+05bEGbKGyK506WaDPfPe1s/ihwRjr6OtL43zJLzOFQ+/zciONEd+sp7LwrfOCnyukPSsg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?PHP if ((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == true)&&($whereAmI == "dashboard")){ ?>
	<script type="text/javascript">
		const web3 = new Web3(window.ethereum);
		const tokenTransferABI = <?= $conf['tokenTransferABI'] ?>;
		const tokenAddress = "<?= $conf['tokenAddress'] ?>";
		const contract = new web3.eth.Contract(tokenTransferABI, tokenAddress);
		const creditRecipient = "<?= $conf['creditRecipient'] ?>";
		const walletAddress = "<?= $_SESSION['walletAddress'] ?>";
		const operatingChain = <?= $conf['blockchainID'] ?>;
		const selectedChain = window.ethereum.networkVersion;
		var incorrectNetwork = false;
		$(document).ready(function() {
			$('#DARASearchResults').modal({ backdrop: 'static', keyboard: true });
			<?PHP $_SESSION['daraTxnFee'] = getConfValue('daraTxnFee'); ?>

			var url = new URL(window.location.href);
			var fileID = url.searchParams.get("fid");
			function storeIPFSData(){
				var postFormData = new FormData();
				axios.post( "/backend.php", { request: 'storeIPFS', fileID: fileID }, { headers: { "Content-Type": "application/json" } })
				.then(function(ipfsUploadResponse) {
					console.log(ipfsUploadResponse);
					if (ipfsUploadResponse['data']['status'] == 'OK') {
						ipfsUploadedHash = ipfsUploadResponse['data']['hash'];
						ipfsUploadedSize = ipfsUploadResponse['data']['size'];
						ipfsUploadedFileName = ipfsUploadResponse['data']['filename'];
						ipfsUploadedqtzCIDHash = ipfsUploadResponse['data']['qtzCIDHash'];
						updateSelector('span#ipfsStoreButtonRight', 'Storing '+pageURL+'');
						updateSelector('span#ipfsStoreButtonRight', 'Stored '+pageURL+' successfully.');
						updateSelector('span#ipfsStoreButtonBottom',  'DARA IPFS CID:&nbsp;<a href="#" onClick="showIPFSModal(\''+ipfsUploadedqtzCIDHash+'\')"><span class=\'toolong\'>'+ipfsUploadedHash+'</span> <span class=\'supertiny\'>(click to preview)</span></a>');
					} else {
						updateSelector('span#ipfsStoreButtonBottom', 'Failed to store on DARA IPFS.');
					}
					getCreditsBalance();
					getUserRecords();
					window.history.replaceState(null, null, window.location.pathname);
				});
			}
			if (fileID != null && fileID.length > 0) {
				$("#DARAUploader").modal('show');
				storeIPFSData();
				document.getElementById('storage_cont').style.display = 'block';
				var pageURL = "<?php if(!empty($_GET['fid'])) { print_r(getPageURL($_GET['fid'])); }?>";
				if (pageURL != null && pageURL.length > 0) {
					updateSelector('span#ipfsStoreButtonRight', 'Storing '+pageURL+'');
				}
			}
		});

		web3.eth.net.getId().then(selectedChain => {
			if ( selectedChain && selectedChain != operatingChain ){
				console.log ('ERROR: incorrect Chain selected')
				incorrectNetwork = true;
				window.ethereum.request({
					method: 'wallet_switchEthereumChain',
					params: [{ chainId: '0x'+window.blockchainID.toString(16)+'' }]
				});
				return false;
			}
		});

		function addCredits(txn, amount) {
		axios.post( "/backend.php", { request: 'addcredits', txn: txn, amount: amount }, { headers: { "Content-Type": "application/json" } })
			.then(function(topupResponse) {
				txnResult = 'Transaction <a href="<?= $blockchain[$activeBlockchain][1] ?>tx/'+topupResponse['data']['txnHash']+'" target="_blank">'+topupResponse['data']['txnHash']+'</a> to transfer '+topupResponse['data']['rpc_txnAmount']+' $DARA was successful! - Balance is updated';
				updateSelector('div#topUpArea', txnResult);
				getCreditsBalance();
				getWalletBalance();
				document.getElementById('topupButton').disabled = false;
				document.getElementById('topupAmount').disabled = false;
			});
		};

		const addDARACredits = document.querySelector('#topupButton');
		addDARACredits.addEventListener('click', () => {
			document.getElementById('topupButton').disabled = true;
			document.getElementById('topupAmount').disabled = true;
			var topupAmount = document.getElementById('topupAmount').value
			updateSelector('div#topUpArea', '');
			updateSelector('span#topupButtonRight', '<span>Please review and confirm transaction in Metamask.</span>');
			contract.methods.transfer(creditRecipient, web3.utils.toWei(topupAmount, 'ether')).send({ from: walletAddress }).on('transactionHash', function(hash){
					updateSelector('div#topUpArea', '<span>Transaction <a href="<?= $blockchain[$activeBlockchain][1] ?>tx/'+hash+'" target="_blank">'+hash+'</a> submitted.. Please wait');
					updateSelector('span#topupButtonRight', '<span>Waiting for block confirmation - Please do not close this window.</span>');
			}).then((res) => {
				addCredits(res, topupAmount);
				window.currentCreditBalance=getCreditsBalance();
				updateSelector('div#topUpArea', '<span>Success! - Balance Updated</span>');
				updateSelector('span#topupButtonRight', '');

			}).catch((err) => {
				if (err['code'] == 4001) {
					updateSelector('div#topUpArea', '<span>User aborted transaction</span>');
				} else if (err['code'] == -32602) {
					var urlparamquery = window.location.search;
					window.location.replace("dashboard.php" + urlparamquery);
				} else {
					updateSelector('div#topUpArea', '<span>Transaction aborted - Error:'+err['code']+'</span>');
				}
				updateSelector('span#topupButtonRight', '');
				document.getElementById('topupButton').disabled = false;
				document.getElementById('topupAmount').disabled = false;
			})
		});

		function getCreditsBalance(){
			axios.post( "/backend.php", { request: 'getCreditsBalance'}, { headers: { "Content-Type": "application/json" } })
				.then(function(response) {
					if (response.status == 200 && response['data']['status'] == 'OK') {
					document.getElementById('creditsBalance').innerHTML = ''+response['data']['balance']+' $DARA';
					currentCreditBalance = response['data']['balance'];
					window.currentCreditBalance = response['data']['balance'];
					return currentCreditBalance;
				}
			});
		}

		async function getWalletBalance(){
			if ( incorrectNetwork == true ) {
				console.log("incorrect network in gWB");
				return false;
			}
			var daraBalance = await contract.methods.balanceOf(walletAddress).call(function (err, daraBalance) {
				if (err) {
					console.log("ERROR: Failed to read wallet balance", err)
				} else {
					daraBalance = daraBalance / 1000000000000000000
					document.getElementById('liveBalance').innerHTML = daraBalance.toLocaleString()+' DARA';
					return daraBalance
				}
			})
		};

		const signHash = document.querySelector('#signHashButton');
		signHashButton.addEventListener('click', () => {
			document.getElementById('signHashButtonAnon').disabled = true;
			document.getElementById('signHashButton').disabled = true;
			var anonSign = document.getElementById('signHashButtonAnon').checked
			if (anonSign) { storeAddress = '' } else { storeAddress = walletAddress }
				axios.post( "/backend.php", { request: 'signhash', ipfsHash: ipfsUploadedHash, storeAddress: storeAddress })
				.then(function(signHashResponse) {
					console.log(signHashResponse);
					signHashStatus = signHashResponse['data']['status']
					signHashresult = signHashResponse['data']['message']
					if ( signHashStatus == 'OK' ) {
						updateSelector('span#signHashButtonBottom', '<span>Transaction Completed - <a href="<?= $blockchain[$activeBlockchain][1] ?>tx/'+signHashresult+'" target="_blank">'+signHashresult+'</a>')

					} else {
						updateSelector('span#signHashButtonBottom', 'Error: '+signHashresult+'');
					}
					getCreditsBalance();
					getUserRecords();
					getPublicRecords(numofPublicRecords);
			})
		});
	</script>
<?PHP
}
echo '<script src="/assets/js/main.js?v='.rand(10,99).'"></script>';
if ((isset($conf['injectLoginScripts']))&&($conf['injectLoginScripts'] == TRUE)){
	echo '<script src="/assets/js/web3-modal.js?v='.rand(10,99).'"></script>';
	echo '<script src="/assets/js/login.js?v='.rand(10,99).'"></script>';
}
if ((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == TRUE)){
	if ((isset($_SESSION['walletAddress']))&&(strlen($_SESSION['walletAddress'])==42)){
		echo "<script type='text/javascript'>document.getElementById('walleticon').innerHTML = getJazzicon(hash('$_SESSION[walletAddress]'), 36);</script>";
	}
}
?>
</section>
</body>
</html>
