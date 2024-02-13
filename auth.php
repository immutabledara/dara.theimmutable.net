<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/Keccak/Keccak.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/Elliptic/EC.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/Elliptic/Curves.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/JWT/jwt_helper.php');

use Elliptic\EC;
use kornrunner\Keccak;

$data = json_decode(file_get_contents("php://input"));
if((!isset($data))||(empty($data))){
   header("HTTP/1.0 404 Not Found");
   die();
}
$request = $data->request;
if (!empty($data->walletAddress)) { $data->walletAddress = strtolower($data->walletAddress); }

if ($request == "login") {
	$walletAddress = $data->walletAddress;
	$stmt = $conn->prepare("SELECT nonce FROM ".$conf['db_tableUsers']." WHERE walletAddress = ?");
		$stmt->bindParam(1, $walletAddress);
		$stmt->execute();
	$nonce = $stmt->fetchColumn();
	if ($nonce) {
		echo("Sign this message (free transaction)\n to Login to DARA Dashboard\n\n(ref:" . $nonce . ")");
	} else {
        $nonce = bin2hex(openssl_random_pseudo_bytes(16)) . uniqid();
		$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableUsers']." (walletAddress, nonce) VALUES (?, ?)");
		$stmt->bindParam(1, $walletAddress);
		$stmt->bindParam(2, $nonce);
		if ($stmt->execute() === TRUE) {
			echo("Sign this message (free transaction)\n to Login to DARA Dashboard\n\n(ref:" . $nonce . ")");
		} else {
			echo "Error" . $stmt->error;
		}
		$conn = null;
	}
	exit;
}

if ($request == "auth") {
	$walletAddress = $data->walletAddress;
   if(isset($data->signature)){
      $signature = $data->signature;
   }else{
      header("HTTP/1.0 404 Not Found");
      die();
   }
	if($stmt = $conn->prepare("SELECT nonce FROM ".$conf['db_tableUsers']." WHERE walletAddress = ?")) {
		$stmt->bindParam(1, $walletAddress);
		$stmt->execute();
		$nonce = $stmt->fetchColumn();
		$message = "Sign this message (free transaction)\n to Login to DARA Dashboard\n\n(ref:" . $nonce . ")";
	}

	function pubKeyToAddress($pubkey) {
		return "0x" . substr(Keccak::hash(substr(hex2bin($pubkey->encode("hex")), 1), 256), 24);
	}

	function verifySignature($message, $signature, $walletAddress) {
		$msglen = strlen($message);
		$hash= Keccak::hash("\x19Ethereum Signed Message:\n{$msglen}{$message}", 256);
		$sign= [
			"r" => substr($signature, 2, 64),
			"s" => substr($signature, 66, 64)
		];
		$recid= ord(hex2bin(substr($signature, 130, 2))) - 27;
		if ($recid != ($recid & 1))
		return false;
		$ec = new EC('secp256k1');
		$pubkey = $ec->recoverPubKey($hash, $sign, $recid);
		return $walletAddress == pubKeyToAddress($pubkey);
	}

	header('Content-Type: application/json; charset=utf-8');
	if (verifySignature($message, $signature, $walletAddress)) {
		$stmt = $conn->prepare("SELECT id FROM ".$conf['db_tableUsers']." WHERE walletAddress = ?");
			$stmt->bindParam(1, $walletAddress);
			$stmt->execute();
			$userID = $stmt->fetchColumn();
			$oldNonce = $nonce;
		$stmt = $conn->prepare("UPDATE ".$conf['db_tableUsers']." SET oldNonce = '".$oldNonce."' WHERE walletAddress = ?");
			$stmt->bindParam(1, $walletAddress);
			$stmt->execute();
			$nonce = bin2hex(openssl_random_pseudo_bytes(16)) . uniqid();
			$stmt = $conn->prepare("UPDATE ".$conf['db_tableUsers']." SET nonce = '".$nonce."', \"lastLogin\" = now() WHERE walletAddress = ?");
			$stmt->bindParam(1, $walletAddress);
			$stmt->execute();
		$token = array();
		$tokeniat = $_SESSION['start_time']; // or time();
		$tolenexp = $tokeniat + $conf['sessionExpirySecs'];
		$token = array(
			'walletAddress' => $walletAddress,
			'sessionID' => encryptString(session_id()),
			'bip' => encryptString($_SESSION['userIP']),
			'iat' => $tokeniat,
			'exp' => $tolenexp
		);
        $JWT = JWT::encode($token, $conf['JWT_secret'], 'HS256');
		echo(json_encode(['result' => 'OK', 'token' => $JWT]));
	} else {
		echo(json_encode(['result' => 'FAIL']));
	}
	$conn = null;
	exit;
}
?>
