"use strict";
const Web3Modal = window.Web3Modal.default;
const WalletConnectProvider = window.WalletConnectProvider.default;
let web3Modal
let provider;
let selectedAccount;
let web3ModalProv;

function web3ModalInit() {
	const providerOptions = {
		walletconnect: {
			package: WalletConnectProvider,
			options: {
				rpc: { 56: 'https://bsc-dataseed1.binance.org' },
				chainId: 56
			}
		}
	};
	web3Modal = new Web3Modal({
		network: "mainnet",
		cacheProvider: true,
		providerOptions,
		disableInjectedProvider: false,
		theme: "dark",
	});
}

async function fetchAccountData() {
	web3ModalProv = new Web3(provider);
	provider.on("accountsChanged", (accounts) => { console.log('accountsChanged'+accounts); });
	provider.on("chainChanged", (chainId) => { console.log('chainChanged'+chainId); });
	provider.on("disconnect", (code, reason) => { console.log(code, reason); });
}

async function refreshAccountData() { await fetchAccountData(provider); }

async function onConnectLoadWeb3Modal() {
	try {
		provider = await web3Modal.connect();
	} catch(e) {
		document.getElementById('loginButtonText').textContent  = 'Failed to connect to wallet: ('+e+') Please refresh page and try again.';
		console.log(e);
		return;
	}
	await refreshAccountData();
}

window.addEventListener('load', async () => { web3ModalInit(); });
