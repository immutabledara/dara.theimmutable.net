<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/JWT/jwt_helper.php');

if ((session_status() == PHP_SESSION_ACTIVE)&&(!in_array(basename($_SERVER['PHP_SELF']), $conf['publicPages']))) {
	$phpSessionID = session_id();
	$currentTimestamp = time();
	$phpSessionStart = $_SESSION['start_time'];
	$phpSessionEnd = $_SESSION['start_time'] + $conf['sessionExpirySecs'];
	if ((isset($_COOKIE['token']))&&(strlen($_COOKIE['token'])>0)){
		$decodedToken =  JWT::decode($_COOKIE['token'], $conf['JWT_secret'], 'HS256');
		$decodedWalletAddress = $decodedToken->walletAddress;
		$decodedSessionID = decryptString($decodedToken->sessionID);
		$decodedUserIP = decryptString($decodedToken->bip);
		$tokenExpiry = $decodedToken->exp;
		if (($phpSessionEnd <= $currentTimestamp)||((isset($tokenExpiry))&&($tokenExpiry <= $currentTimestamp))||((isset($decodedSessionID))&&($decodedSessionID != $phpSessionID))) {
			error_log("Tampering Detected");
			flushSession();
		}else{
			$_SESSION['is_loggedin'] = true;
			if ((isset($decodedWalletAddress))&&(strlen($decodedWalletAddress)==42)){
				$_SESSION['walletAddress'] = $decodedWalletAddress;
				$_SESSION['walletAddress_s'] = substr_replace($_SESSION['walletAddress'], ".....", 6,30);
				if (in_arrayi($_SESSION['walletAddress'], $conf['zwiAdmins'])) {
					$_SESSION['zwiAdmin']=true;
				}
			}
		}
	} elseif (isset($_GET['fid'])&&($_GET['fid']!="")){
		header("Location: login.php?fid=".$_GET['fid']);
	} else {
		$_SESSION['is_loggedin'] = false;
		unset($_SESSION['walletAddress']);
		unset($_SESSION['walletAddress_s']);
		flushSession();
	}
}
if ((isset($_SESSION['walletAddress']))&&($_SESSION['walletAddress'] != "")){
	if (in_arrayi($_SESSION['walletAddress'], $conf['tier1Wallets'])) {
		$conf['manualUploadLimit']=$conf['t1manualUploadLimit'];
		$conf['zwiUploadLimit']=$conf['t1zwiUploadLimit'];
		$conf['picUploadLimit']=$conf['t1picUploadLimit'];
		$conf['manualUploadTypes']=$conf['t1manualUploadTypes'];
		$conf['kaka']="VIP!!";
   }
}
?>
