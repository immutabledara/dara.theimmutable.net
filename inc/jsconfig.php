<?php require_once($_SERVER['DOCUMENT_ROOT'].'/inc/config.php'); ?>
<script type="text/javascript">
	window.manualUploadLimit = <?=$conf['manualUploadLimit']?>;
	window.picUploadLimit = <?=$conf['picUploadLimit']?>;
	window.zwiUploadLimit = <?=$conf['zwiUploadLimit']?>;
	window.daraURL = '<?=$conf['daraURL']?>';
	<?php
		echo "window.zwiUploadTypes = ". json_encode($conf['zwiUploadTypes']) . ";\n";
		echo "window.manualUploadTypes = ". json_encode($conf['manualUploadTypes']) . ";\n";
		echo "window.picUploadTypes = ". json_encode($conf['picUploadTypes']) . ";\n";
		echo "window.picMaxDims = ". json_encode($conf['picMaxDims']) . ";\n";
	?>
	window.numofPublicRecords = '<?=$conf['numofPublicRecords']?>';
	window.sessionExpirySecs = '<?=$conf['sessionExpirySecs']?>';
	window.ipfsGateway = '<?=$conf['ipfsGateway']?>';
	window.ipfsGateway = '<?=$conf['ipfsGateway']?>';
	window.bscExplorer = '<?=$activeBlockchainExplorer?>';
	window.blockchainID = <?=$conf['blockchainID']?>;
	window.blockchainName = '<?=$conf['blockchains'][''.$conf['blockchainID'].''][0]?>';
	window.bscChainParams = [{chainId: '0x38', chainName: 'Binance Smart Chain Mainnet', nativeCurrency: { name: 'BNB', symbol: 'BNB', decimals: 18 }, rpcUrls: ['https://bsc-dataseed1.binance.org/'], blockExplorerUrls: ['https://bscscan.com/']}];
</script>
