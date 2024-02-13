<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/secureconfig.php');
$conf['mybasedir'] = "/var/www/html";
$conf['zwimb'] = "/var/www/html/masterblaster";
$conf['cachedir'] = "/var/www/misc/livecache/";
$conf['zwicachedir'] = "/var/www/misc/zwicache/";
$conf['deathrowdir'] = "/var/www/misc/deathrow/";
$conf['zwibuildercachedir'] = "/var/www/misc/zwibuildcache/";
$conf['db_wp_archive'] = 'wp_archive';
$conf['db_wp_search'] = 'wp_search';
$conf['db_deathrow'] = 'wp_deathrow';
$conf['searchLimit']=10000;
# Keep wpsearch high else wikipedia returns 10
$conf['wpsearchLimit']=5000;
$conf['dbsearchLimit']=5000;
$conf['encycloreaderBaseURL']="https://encycloreader.org/db/zwiget.php";
## Check ipfs.class.php
$conf['ipfsServerIP']="ipfs.theimmutable.net.vhosts";
$conf['publicPages'] = array ('login.php', 'index.php', 'profile.php', 'encyclopedia.php', 'deathrow.php', 'drcron.php', 'drdeletedcron.php');
$conf['blockchainID'] = 56;
$conf['numofPublicRecords'] = 14;
$conf['sessionExpirySecs'] = 3660;
$conf['manualUploadLimit'] = 10485760; #10MB
$conf['zwiUploadLimit'] = 10485760; #10MB
$conf['zwiUploadTypes'] = array('application/zip', 'application/x-zip-compressed');
$conf['manualUploadTypes'] = array('application/pdf', 'text/plain', 'image/png', 'image/jpeg', 'image/gif', 'application/epub+zip');
$conf['picUploadLimit'] = 524288; #512KB
$conf['picUploadTypes'] = array('image/png', 'image/jpeg');
$conf['enablegzip'] = false;
$conf['picMaxDims'] = array(512, 512);
$conf['daraURL'] = 'dara.theimmutable.net';

$conf['reservedUsernames'] = array('gig', 'ggmesh', 'gigamesh', 'admin', 'administrator', 'support', 'louie', 'dxblouie', 'bgnlouie', 'root', 'dara', 'projectdara', 'dara_proj', 'theimmutable', 'theimmutabledao', 'gutenberg', 'pg', 'project_gutenberg', 'gutenberg_project');
$conf['adminWallets'] = array()
$conf['zwiAdmins'] = array()

# Tier 1 Limits
$conf['tier1Wallets'] = array()
$conf['t1manualUploadLimit'] = 104857600; #100MB
$conf['t1zwiUploadLimit'] = 104857600; #100MB
$conf['t1picUploadLimit'] = 2097152; #2MB
$conf['t1manualUploadTypes'] = array('application/pdf', 'text/plain', 'image/png', 'image/jpeg', 'image/gif', 'application/epub+zip', 'video/mp4');

##############################################################################
$conf['blockchainRPC'] = "https://bsc-dataseed.binance.org/";
$conf['blockchainRPC2'] = "https://bsc-dataseed1.ninicoin.io/";
$conf['blockchains'] = array (
	97 => array("BSC Testnet", "https://testnet.bscscan.com/"),
	56 => array("BSC Mainnet", "https://bscscan.com/")
);

$conf['tokenAddress'] = "0x0255af6c9f86F6B0543357baCefA262A2664f80F";
$conf['tokenDeployerAddress'] = "0xa218542FC3845D58Edc9DBE6635ffF0b1A9f8f05";
$conf['tokenTransferABI'] = '[{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"}]';

$conf['contractAddress'] = "0xCE033B558EB468B4ccAf88Fe6C9FA89434f0A0aC";
$conf['contractDeployerAddress'] = "0xa218542FC3845D58Edc9DBE6635ffF0b1A9f8f05";

$conf['contractABI'] = json_decode('[{"inputs":[],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"inputs":[{"internalType":"uint256","name":"key","type":"uint256"}],"name":"get","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"valueHash","type":"string"}],"name":"set","outputs":[{"internalType":"uint256","name":"key","type":"uint256"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"totalEntries","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"}]');

$conf['creditRecipient'] = "0xa218542FC3845D58Edc9DBE6635ffF0b1A9f8f05";

$conf['txnMarkup'] = 2;

$conf['coingeckoAPI'] = "https://api.coingecko.com/api/v3/coins/immutable?localization=false&community_data=false&developer_data=false&sparkline=false";
$conf['pancakeAPI'] = "https://api.pancakeswap.info/api/v2/tokens/0x0255af6c9f86f6b0543357bacefa262a2664f80f";

$conf['db_serverType'] = "pgsql";
$conf['db_serverName'] = "postgres13";
$conf['db_serverPort'] = 5432;
$conf['db_tableUsersTxns'] = "dara_users_txns";
$conf['db_confTable'] = 'dara_config';
$conf['db_tableIPFSData'] = 'ipfs_data';
$conf['db_tableUsers'] = "dara_users";

$blockchain=$conf['blockchains'];
$activeBlockchain=$conf['blockchainID'];
$activeBlockchainExplorer=$conf['blockchains'][$conf['blockchainID']][1];

$conf['activeBlockchainExplorer'] = $conf['blockchains'][$conf['blockchainID']][1];
$conf['ipfsGateway'] = 'https://opfs.dara.global/ipfs/';

$conf['metadata'] = [
	'description' => 'The Uncensorable Publishing Platform to Save, Share and immortalize knowledge.',
	'og_site_name' => 'Project DARA',
	'og_title' => 'Project DARA',
	'og_url' => 'https://dara.theimmutable.net/',
	'og_type' => 'website',
	'og_description' => 'The Uncensorable Publishing Platform to Save, Share and immortalize knowledge.',
	'og_image' => 'https://dara.theimmutable.net/assets/images/immutablecard500.png',
	'twitter_card' => 'summary',
	'twitter_site' => '@dara_proj',
	'twitter_title' => 'The Uncensorable Publishing Platform to Save, and share knowledge',
	'twitter_description' => 'The Uncensorable Publishing Platform to Save, Share and immortalize knowledge.',
	'twitter_image' => 'https://dara.theimmutable.net/assets/images/immutablecard500.png',
];

?>
