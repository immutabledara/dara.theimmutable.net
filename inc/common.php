<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ethereum-tx/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/web3/vendor/autoload.php');
use Web3p\EthereumTx\Transaction;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

if (session_status() == PHP_SESSION_NONE) {
	session_start([ 'cookie_lifetime' => $conf['sessionExpirySecs'], ]);
	if(!isset($_SESSION['start_time'])){
		$_SESSION['start_time'] = time();
	}
}

try {
   $dsn = "".$conf['db_serverType'].":host=".$conf['db_serverName'].";port=".$conf['db_serverPort'].";dbname=".$conf['db_dbname'].";";
   $conn = new PDO($dsn, $conf['db_username'], $conf['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}
catch (PDOException $e) {
	error_log($e->getMessage());
	die();
#   die($e->getMessage());
}

######################################################################################################

if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$_SESSION['userIP'] = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$_SESSION['userIP'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
	$_SESSION['userIP'] = $_SERVER['REMOTE_ADDR'];
} elseif (basename($_SERVER["SCRIPT_FILENAME"]) != 'cron.php') {
	$_SESSION['is_loggedin'] = false;
	unset($_SESSION['walletAddress']);
	unset($_SESSION['walletAddress_s']);
	flushSession();
}

function flushSession() {
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time()-1000);
			setcookie($name, '', time()-1000, '/');
		}
	}
	if (session_status() == PHP_SESSION_ACTIVE) { session_destroy(); }
	echo '<script type="text/javascript">window.location.href = "index.php"+window.location.search;</script>';
	#header("location:index.php?".$_SERVER['QUERY_STRING']);
}

function encryptString($data){
    global $conf;
    $encryption_key = base64_decode($conf['encKey']);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function encryptString2($data){
    global $conf;
    $encryption_key = base64_decode($conf['encKey2']);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}


function decryptString($data){
    global $conf;
    $encryption_key = base64_decode($conf['encKey']);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

function getConfValue($confItem){
	global $conn, $conf, $_SESSION;
	$stmt = $conn->prepare("SELECT config_value FROM ".$conf['db_confTable']." WHERE config_item = ?");
	$stmt->bindParam(1, $confItem);
	$stmt->execute();
	$confValue = $stmt->fetchColumn();
	$_SESSION[''.$confItem.''] = $confValue;
	return ($confValue);
}

function setConfValue($configItem, $configValue) {
	global $conn, $conf, $_SESSION;
	$stmt = $conn->prepare("INSERT INTO ".$conf['db_confTable']." (config_item, config_value) VALUES (?, ?) ON CONFLICT (config_item) DO UPDATE set config_value = excluded.config_value, updated = now()");
	$stmt->bindParam(1, $configItem);
	$stmt->bindParam(2, $configValue);
	if ($stmt->execute() === TRUE) {
		$updateConfDBResponse ='{"config_item": "'.$configItem.'", "config_value": "'.$configValue.'"}';
		return ($updateConfDBResponse);
	} else {
		echo "Error" . $stmt->error;
		return ("Error" . $stmt->error);
	}
	$conn = null;
}

function getLiveDaraPrice () {
	global $conf, $_SESSION;
	$coingecko = curl_init();
	curl_setopt($coingecko, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($coingecko, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($coingecko, CURLOPT_URL, $conf['coingeckoAPI']);
	$coingeckoResult = curl_exec($coingecko);
	curl_close($coingecko);
	$daraBNB = json_decode($coingeckoResult)->market_data->current_price->bnb;
	$daraUSD = json_decode($coingeckoResult)->market_data->current_price->usd;
	return (['daraBNB' => $daraBNB, 'daraUSD' => $daraUSD]);
}

function getLiveDaraPricePKS () {
	global $conf, $_SESSION;
	$pancakeAPI = curl_init();
	curl_setopt($pancakeAPI, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($pancakeAPI, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($pancakeAPI, CURLOPT_URL, $conf['pancakeAPI']);
	$pancakePrice = curl_exec($pancakeAPI);
	curl_close($pancakeAPI);
	$daraBNB = json_decode($pancakePrice)->data->price_BNB;
	$daraUSD = json_decode($pancakePrice)->data->price;
	return (['daraBNB' => $daraBNB, 'daraUSD' => $daraUSD]);
}

function getLiveNetworkFees(){
	global $conf;
	$web3 = new Web3(new HttpProvider(new HttpRequestManager($conf['blockchainRPC'],5)));
	$contract = new Contract($web3->provider, $conf['contractABI'], 'latest');
	$eth = $web3->eth;
	$sampleTxnData ="0x4ed3885e00000000000000000000000000000000000000000000000000000000000000200000000000000000000000000000000000000000000000000000000000000059516d5548773944333878517a7476586471426765473564793450555468783755584c65394a6932684b45715142467e30783735303334336638333237666335306237376364383035393734633038663038373030663739663900000000000000";
	$eth->gasPrice(function ($err, $result) use (&$gasPrice) {
		if ($err !== null) { throw $err; }
		if (isset($result)) { $gasPrice = intval($result->toString()); }
	});
	$gasEstimate = $contract->at($conf['contractAddress'])->estimateGas('set', $sampleTxnData, ["from" => $conf['contractDeployerAddress']], function($err, $result) use (&$txnGas){
		if ($err !== null) { throw $err; }
		if (isset($result)) { $txnGas = intval($result->toString()); }
	});
	return (['gasPrice' => $gasPrice, 'gas' => ($txnGas)]);
}

function gzCompressFile($source, $level = 9){
    $dest = $source . '.gz';
    $orig = $source . '.orig';
    $mode = 'wb' . $level;
    $error = false;
    if ($fp_out = gzopen($dest, $mode)) {
        if ($fp_in = fopen($source,'rb')) {
            while (!feof($fp_in))
                gzwrite($fp_out, fread($fp_in, 1024 * 512));
            fclose($fp_in);
        } else {
            $error = true;
        }
        gzclose($fp_out);
    } else {
        $error = true;
    }
    if ($error)
        return false;
    else
        return $dest;
}

function getPageURL($fileID){
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT pageurl FROM ".$conf['db_tableIPFSData']." WHERE filename = ? LIMIT 1");
	$stmt->bindParam(1, $fileID);
	$stmt->execute();
	return ($stmt->fetchColumn());
}
function getPageName($fileID){
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT pagename FROM ".$conf['db_tableIPFSData']." WHERE filename = ? LIMIT 1");
	$stmt->bindParam(1, $fileID);
	$stmt->execute();
	return ($stmt->fetchColumn());
}

function in_arrayi($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

function fetchPublicProfile($profileID) {
	$url = "http://127.0.0.1/backend.php";
	$data = json_encode([
		"request" => "getPublicProfile",
		"profileID" => $profileID
	]);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data)
	]);
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response, true);
}
