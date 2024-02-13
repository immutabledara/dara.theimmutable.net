let authData = { state: false, walletAddress: '', JWT: '' }

let loginModalText = {
	loggedOut: 'Connect Wallet and Login',
	needMetamask: 'To login, first install the <a href="https://metamask.io/" style="color:#ff7300" target="_blank">MetaMask</a> browser extension',
	loggedIn: 'Successfully authenticated with web3 Address:<br><span id="loginModalAddress"></span>',
	needLogInToMetaMask: 'Please Unlock / Login to your wallet account first',
	IncorrectChain: 'Incorrect Network Selected - Please switch to '+window.blockchainName+' Network',
	signTheMessage: 'Please sign the request to authenticate your login',
	newAddress: 'Address Changed, Please login',	
	userAborted: 'User aborted operation',
	switchingNetwork: 'Switching Networks',
}

if (authData.state != true && typeof authData.state !== "undefined")  {
	showMsg('loggedOut', '', 'Login');
} else {
	showMsg('loggedIn', web3address, 'Logout');
}

function showMsg(msgid, walletAddress = '', btntxt = '') {
	document.getElementById('user-login-msg').innerHTML = loginModalText[msgid];
	document.getElementById('loginButtonText').innerText = btntxt;
	if (msgid == 'loggedIn') {
		document.getElementById('loginModalAddress').innerHTML = walletAddress;
	}
	document.getElementById('user-login-msg').style.display = 'block';
}

if (window.web3) {
	try {
		var ethereumChainId = ethereum.chainId.toString(16);
		window.ethereum.on('accountsChanged', (_chainId) => ethNetworkUpdate());
		window.ethereum.on('chainChanged', (_chainId) => window.location.reload());
		if (ethereumChainId != window.blockchainID && typeof ethereumChainId !== "undefined")  {
			authData = { state: false, walletAddress: '', JWT: '' };
			document.getElementById('loginButtonText').disabled = true;
			showMsg('IncorrectChain', '', 'Login');
			window.ethereum.request({ method: 'wallet_switchEthereumChain', params: [{ chainId: '0x'+window.blockchainID.toString(16)+'' }] })
			.catch((err) => {
				if (err['code'] == 4001) {
					showMsg('userAborted', '', 'Login');
					var urlparamquery = window.location.search;
					window.location.replace("/" + urlparamquery);
				} else if (err['code'] == -32002) {
					showMsg('switchingNetwork', '', 'Network Switch Operation Pending... Please check your Metamask Wallet.');
				} else if (err['code'] == -32603 || err['code'] == 4902) {
					try {
						const addBSC = ethereum.request({
							method: 'wallet_addEthereumChain',
							params: window.bscChainParams,
						})
					} catch (error) { 
						console.log(error); 
					}
				} else {
					showMsg('userAborted', '', 'Login');
				}
			})
		}

		async function ethNetworkUpdate() {
			let accountsOnEnable = await web3.eth.getAccounts();
			let address = accountsOnEnable[0];
			address = address.toLowerCase();
			if (authData.walletAddress != address) {
				authData.walletAddress = address;
				if (authData.state == true) {
					authData.JWT = "";
					authData.state = false;
					authData.buttonText = "Log in";
				}
			}
			if (authData.ethAddress != null && authData.state == true) {
				authData.state = false;
				showMsg('newAddress', '', 'Login');
			}
		}
	} catch (e) {
		if (e instanceof ReferenceError) {
			showMsg('needMetamask', '', 'Login');
			document.getElementById('loginButtonText').disabled = true;
		}
	}
}

async function userLoginOut() {
	document.getElementById('loginButtonText').disabled = true;
	if(authData.state == true) {

		authData = { state: false, walletAddress: '', JWT: '' };
		showMsg('loggedOut', '', 'Login');
		document.getElementById('loginButtonText').disabled = false;
		return;
	}else{
		await onConnectLoadWeb3Modal();
	}
	if (ethereumChainId != window.blockchainID && typeof ethereumChainId !== "undefined")  {
		authData.state = false;
		showMsg('IncorrectChain', '', 'Login');
		return;
	}
	if (web3ModalProv) {
		window.web3 = web3ModalProv;
		try {
			userLogin();
		}
		catch (error) {
			console.log(error);
			authData.state = false;
			showMsg('loggedOut', '', 'Login');
			return;
		}
	} else {
		authData.state = false;
		//showMsg('needMetamask', '', 'Login');
		$("#loginmodal").modal('hide');
		return;
	}
}

async function userLogin() {
	if (authData.state == true) {
		authData.state = false;
		showMsg('loggedOut', '', 'Login');
		authData = { state: false, walletAddress: '', JWT: '' };
		return;
	}
	if (typeof window.web3 === 'undefined') {
		showMsg('needMetamask', '', '');
		authData = { state: false, walletAddress: '', JWT: '' };
		return;
	}
	
	let accountsOnEnable = await web3.eth.getAccounts();
	let web3address = accountsOnEnable[0];
	web3address = web3address.toLowerCase();
	if (web3address == null) {
		authData = { state: false, walletAddress: '', JWT: '' };
		showMsg('needLogInToMetaMask', '', 'Login');
		return;
	}
	authData.state = 'signTheMessage';
	document.getElementById('loginButtonText').disabled = true;
	showMsg('signTheMessage', '', '...');
	axios.post( '/auth.php', { request: 'login', walletAddress: web3address }, { headers: { 'Content-Type': 'application/json' } })
	.then(function(response) {
		if (response.data.substring(0, 5) != 'Error') {
			let message = response.data;
			let publicAddress = web3address;
			handleSignMessage(message, publicAddress).then(handleAuthenticate);

			function handleSignMessage(message, publicAddress) {
				return new Promise((resolve, reject) =>
					web3.eth.personal.sign( web3.utils.utf8ToHex(message), publicAddress, (err, signature) => {
						if (err) {
							authData = { state: false, walletAddress: '', JWT: '' };
							document.getElementById('loginButtonText').disabled = false;
							showMsg('loggedOut', '', 'Login');
							$("#loginmodal").modal('hide');
						}
						return resolve({ publicAddress, signature });
					})
				);
			}

			function handleAuthenticate({ publicAddress, signature }) {
				axios.post( '/auth.php', { request: 'auth', walletAddress: arguments[0].publicAddress, signature: arguments[0].signature }, { headers: { 'Content-Type': 'application/json' } })
				.then(function(response) {
					//console.log(response);
					if (response.data.result == 'OK') {
						authData.state = true;
						showMsg('loggedIn', web3address, 'Logout');
						document.getElementById('loginButtonText').disabled = false;
						authData.walletAddress = web3address;
						authData.JWT = response.data.token;
						authData.loggedAddress = response.data.address;
						//localStorage.clear();
						var date = new Date();
						var cookieSeconds = window.sessionExpirySecs;
						date.setTime(date.getTime()+(cookieSeconds*1000));
						var cookieExpiry = "; expires="+date.toGMTString();
						document.cookie = "token=" + authData.JWT + cookieExpiry + "; path=/; domain=" + window.daraURL;
						sleep(1000).then(() => { window.location.href = "/dashboard.php"+window.location.search; });
					}
				})
				.catch(function(error) {
					console.error(error);
				});
			}
		} else {
			console.log('Error: ' + response.data);
		}
	})
	.catch(function(error) {
		console.error(error);
	});
}
