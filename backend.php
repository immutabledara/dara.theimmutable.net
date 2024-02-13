<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/JWT/jwt_helper.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ethereum-tx/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/web3/vendor/autoload.php');
use Web3p\EthereumTx\Transaction;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

if ((isset($_SESSION['walletAddress']))&&($_SESSION['walletAddress'] != "")){
	if (in_arrayi($_SESSION['walletAddress'], $conf['tier1Wallets'])) {
	$conf['manualUploadLimit']=$conf['t1manualUploadLimit'];
		$conf['zwiUploadLimit']=$conf['t1zwiUploadLimit'];
		$conf['picUploadLimit']=$conf['t1picUploadLimit'];
		$conf['manualUploadTypes']=$conf['t1manualUploadTypes'];
	}
}


$postData = json_decode(file_get_contents("php://input"));
if (!empty($postData->request)) {
	header('Content-Type: application/json; charset=utf-8');
	$action = $postData->request;
	switch($action) {
		case 'deathrowSearch':
			print (deathrowSearch($postData->searchQuery));
			break;
		case 'encyclopediaSearch':
			print (encyclopediaSearch($postData->searchQuery));
			break;
		case 'genZWIFile':
			print (genZWIFile($postData->cidhash));
			break;
		case 'storeZWIIPFS':
			print (storeZWIIPFS($postData->cidhash));
			break;
		case 'getPublicRecords':
			print (getPublicRecords($postData->limit));
			break;
		case 'searchPublicRecords':
			print (searchPublicRecords($postData->searchQuery));
			break;
		case 'getIPFSData':
			print (getIPFSData($postData->qtzCIDHash));
			break;
		case 'getWPIPFSData':
			print (getWPIPFSData($postData->qtzCIDHash));
			break;
		case 'getDRIPFSData':
			print (getDRIPFSData($postData->qtzCIDHash));
			break;
		case 'getUserRecords':
			print (getUserRecords());
			break;
		case 'getCreditsBalance':
			print (getCreditsBalance());
			break;
		case 'togglePublic':
			print (togglePublic($postData->cidHash));
			break;
		case 'storeIPFS':
			print (storeIPFS($postData->fileID));
			break;
		case 'storeZWIURL':
			print (storeZWIURL($postData->zwiURL));
			break;
		case 'signhash':
			print (signHash($postData->ipfsHash, $postData->storeAddress));
			break;
		case 'updateProfile':
			print (updateProfile($postData->username, isset($postData->avatarIPFS) ? $postData->avatarIPFS : false, $postData->userEmail, $postData->userTwitter, $postData->userMedium, $postData->userBio, $postData->isPublicProfile, $postData->isDefaultPublic, $postData->isDefaultAnon));
			break;
		case 'getProfileData':
			print (getProfileData());
			break;
		case 'getPublicProfile':
			print (getPublicProfile(strtolower($postData->profileID)));
			break;
		case 'getPublicProfileRecords':
			print (getPublicProfileRecords(strtolower($postData->profileID)));
			break;
		case 'addcredits':
			if ((!empty($postData->txn))&&(!empty($postData->amount))) {
				print (addCredits($postData->txn, $postData->amount, "web top up"));
			} else {
				die('ERROR: Missing txn data');
			}
			break;
		default:
			print('Invalid Request 1');
			break;
	}
} elseif (!empty($_FILES['file']['name'])) {
	$action = $_POST['request'];
	$purpose = $_POST['purpose'];
	switch($action) {
		case 'uploadfile':
			print (uploadfile($purpose));
			break;
		default:
			print('Invalid Request 2');
			break;
	}
} else {
	die('Invalid Request 3');
}

function storeZWIURL($zwiURL){
	global $conn, $conf;
	$purpose=$contentsource="zwiurlupload";
	$userIP = encryptString($_SESSION['userIP']);
	$fid="".uniqid()."".time()."".uniqid()."";
	$filepath="".$conf['cachedir']."".$fid."";
	if (strpos($zwiURL, "encycloreader.org/db") === false) {
		$resultJSON = [ 'status' => 'Error: Invalid ZWI URL' ];
		$result = json_encode($resultJSON);
		return $result;
	}
	$url_components = parse_url($zwiURL);
	parse_str($url_components['query'], $params);
	$contentID=$params['id'];
	$sourceURL="https://encycloreader.org/db/zwiget.php?id=$contentID";
	if (!file_put_contents($filepath, file_get_contents($sourceURL))) {
		$resultJSON = [ 'status' => 'Error: Failed to retrieve ZWI File' ];
		$result = json_encode($resultJSON);
		return $result;
	} else {
		$mime_type = mime_content_type($filepath);
		if (
			(($purpose=='fileupload')&&(!in_arrayi($mime_type, $conf['manualUploadTypes'])))
			||
			(($purpose=='profilepic')&&(!in_arrayi($mime_type, $conf['picUploadTypes'])))
			||
			(($purpose=='zwiurlupload')&&(!in_arrayi($mime_type, $conf['zwiUploadTypes'])))
			) {
			$resultJSON = [ 'status' => 'Error: Invalid filetype' ];
			$result = json_encode($resultJSON);
			return $result;
		}
		$filesize = filesize($filepath);
		if (
			(($purpose=='fileupload')&&($filesize > $conf['manualUploadLimit']))
			||
			(($purpose=='profilepic')&&($filesize > $conf['picUploadLimit']))
			||
			(($purpose=='zwiurlupload')&&($filesize > $conf['zwiUploadLimit']))
			) {
			$resultJSON = [ 'status' => 'Error: File exceeds allowed limit.' ];
			$result = json_encode($resultJSON);
			return $result;
		}
		$zip = new ZipArchive();
		if(($purpose=='zwiurlupload')&&($zip->open($filepath) === TRUE )) {
			if ($zip->locateName('metadata.json') !== false) {
				$fp = $zip->getStream('metadata.json');
				if(!$fp) {
					$resultJSON = [ 'status' => 'Invalid ZWI File Structure' ];
					$result = json_encode($resultJSON);
					return $result;
				}
				$contents="";
				while (!feof($fp)) {
					$contents .= fread($fp, 2);
				}
				fclose($fp);
				$zwiMetaData=json_decode($contents, true);
				$contentsource="zwiurlupload";
				if ($zwiMetaData['ZWIversion']){
					if ($zwiMetaData['Title']){$zwiTitle=$zwiMetaData['Title'];}
				}
			}else{
				$resultJSON = [ 'status' => 'Invalid ZWI File' ];
				$result = json_encode($resultJSON);
				return $result;
			}
		}
		$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableIPFSData']." (pageurl, pagename, filename, filesize, contentsource, sourceaddress) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bindParam(1, $zwiURL);
		$stmt->bindParam(2, $zwiTitle);
		$stmt->bindParam(3, $fid);
		$stmt->bindParam(4, $filesize);
		$stmt->bindParam(5, $contentsource);
		$stmt->bindParam(6, $userIP);
		if ($stmt->execute() === TRUE) {
			$result = storeIPFS($fid, "zwiurlupload");
		} else {
			$resultJSON = [ 'status' => 'ERR 1' ];
			$result = json_encode($resultJSON);
		}
	}
	return($result);
	$conn = null;
}

function uploadfile($purpose){
	global $conn, $conf;
	$contentsource="website";
	$userIP = encryptString($_SESSION['userIP']);
	$pageurl = $_FILES['file']['name'];
	$filename = $_FILES['file']['name'];
	$fid="".uniqid()."".time()."".uniqid()."";
	$filepath="".$conf['cachedir']."".$fid."";

	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
		$mime_type = mime_content_type($_FILES['file']['tmp_name']);
		if (
			(($purpose=='fileupload')&&(!in_arrayi($mime_type, $conf['manualUploadTypes'])))
			||
			(($purpose=='profilepic')&&(!in_arrayi($mime_type, $conf['picUploadTypes'])))
			||
			(($purpose=='zwiupload')&&(!in_arrayi($mime_type, $conf['zwiUploadTypes'])))
			) {
			$resultJSON = [ 'status' => 'Error: Invalid filetype' ];
			error_log($mime_type);
			$result = json_encode($resultJSON); return $result;
			}
	}
	if(move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
		$filesize = filesize($filepath);
		if (
			(($purpose=='fileupload')&&($filesize > $conf['manualUploadLimit']))
			||
			(($purpose=='profilepic')&&($filesize > $conf['picUploadLimit']))
			||
			(($purpose=='zwiupload')&&($filesize > $conf['zwiUploadLimit']))
			) {
			$resultJSON = [ 'status' => 'Error: File exceeds allowed limit.' ];
			$result = json_encode($resultJSON);
			return $result;
		}
		$zip = new ZipArchive();
		if(($purpose=='zwiupload')&&($zip->open($filepath) === TRUE )) {
			if ($zip->locateName('metadata.json') !== false) {
				$fp = $zip->getStream('metadata.json');
				if(!$fp) {
					$resultJSON = [ 'status' => 'Invalid ZWI File Structure' ];
					$result = json_encode($resultJSON);
					return $result;
				}
				$contents="";
				while (!feof($fp)) {
					$contents .= fread($fp, 2);
				}
				fclose($fp);
				$zwiMetaData=json_decode($contents, true);
				$contentsource="zwifileupload";
				if ($zwiMetaData['ZWIversion']){
					if ($zwiMetaData['Title']){$zwiTitle=$zwiMetaData['Title'];}
				}
			}else{
				$resultJSON = [ 'status' => 'Invalid ZWI File' ];
				$result = json_encode($resultJSON);
				return $result;
			}
		}
		$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableIPFSData']." (pageurl, pagename, filename, filesize, contentsource, sourceaddress) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bindParam(1, $pageurl);
		if(strlen($zwiTitle)){
			$stmt->bindParam(2, $zwiTitle);
		}else{
			$stmt->bindParam(2, $filename);
		}
		$stmt->bindParam(3, $fid);
		$stmt->bindParam(4, $filesize);
		$stmt->bindParam(5, $contentsource);
		$stmt->bindParam(6, $userIP);
		if ($stmt->execute() === TRUE) {
			$result = storeIPFS($fid);
		} else {
			$resultJSON = [ 'status' => 'ERR 1' ];
			$result = json_encode($resultJSON);
		}
	} else {
		error_log(print_r($_FILES,true));
		$resultJSON = [ 'status' => 'ERR 2a' ];
		$result = json_encode($resultJSON);
	}
	return($result);
	$conn = null;
}

function searchPublicRecords($searchQuery=''){
	global $conn, $conf;
	$data = array();
	if((isset($searchQuery))&&(strlen($searchQuery))){
		$condition = preg_replace('/[^A-Za-z0-9\- ]/', '%', $searchQuery);
		$condition = trim($condition);
		$condition = str_replace(" ", "%", $condition);
		if(strlen($condition)>2){
			$sample_data = array(
				':pagename'		=>	'%' . $condition . '%',
				':pageurl'		=>	'%' . $condition . '%'
			);
			$query = "SELECT id, pagename, pageurl, ipfshash, txnhash, created::timestamp(0), walletaddress, ispublic, anonymous FROM ".$conf['db_tableIPFSData']." WHERE walletaddress != '0x000000000000000000000000000000000000dead' AND ispublic=true AND (LOWER(pagename) LIKE LOWER(:pagename) OR LOWER(pageurl) LIKE LOWER(:pageurl)) ORDER BY id DESC";
			$stmt = $conn->prepare($query);
			$stmt->execute($sample_data);
			$total_data = $stmt->rowCount();
			$result = $stmt->fetchAll();
			foreach($result as $row) {
				$row['anonymous'] = (bool)$row['anonymous'];
				$row['ispublic'] = (bool)$row['ispublic'];
				if ((strlen($row['txnhash']))&&($row['anonymous'] == TRUE)){
					$row['walletaddress'] = '';
				}elseif (!strlen($row['txnhash'])){
					$row['walletaddress'] = '';
				}
				$data[] = array(
					'cidhash'	=>	encryptString($row["id"]),
					'created'	=>	$row['created'],
					'pagename'	=>	$row['pagename'],
					'pageurl'	=>	$row['pageurl'],
					'ipfshash'	=>	$row['ipfshash'],
					'walletaddress'	=>	$row['walletaddress'],
					'txnhash'	=>	$row['txnhash'],
					'anonymous' => $row['anonymous']
				);
			}
		}
	}
	if (!isset($data)) {
			$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
	}else{
			$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	}
	return json_encode($resultJSON);
}

function getPublicRecords($limit = 10){
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT id, walletaddress, pagename, pageurl, ipfshash, txnhash, updated::timestamp(0), ispublic, anonymous FROM ".$conf['db_tableIPFSData']." WHERE walletaddress != '0x000000000000000000000000000000000000dead' AND (ispublic IS NULL OR ispublic = TRUE) AND ipfshash IS NOT NULL ORDER BY updated DESC LIMIT ?");
	$stmt->bindParam(1, $limit);
	$stmt->execute();
	$total_data = $stmt->rowCount();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
		$row['anonymous'] = (bool)$row['anonymous'];
		$row['ispublic'] = (bool)$row['ispublic'];
		if ((strlen($row['txnhash']))&&($row['anonymous'] == TRUE)){
			$row['walletaddress'] = '';
		}elseif (!strlen($row['txnhash'])){
			$row['walletaddress'] = '';
		}
		$data[] = array(
			'cidhash'		=>	encryptString($row["id"]),
			'walletaddress'	=>	$row["walletaddress"],
			'created'		=>	$row['updated'],
			'pagename'		=>	$row['pagename'],
			'pageurl'		=>	$row['pageurl'],
			'ipfshash'		=>	$row['ipfshash'],
			'txnhash'		=>	$row['txnhash'],
			'anonymous'		=>	$row['anonymous'],
			'ispublic'		=>	$row['ispublic']
		);
	}
	if (!isset($data)) {
			$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
	}else{
			$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	}
	return json_encode($resultJSON);
}

function getIPFSData($encid = ''){
        global $conn, $conf;
		if ((!isset($encid))||((isset($encid))&&($encid==''))) {
			die('Invalid Request 3');
		}
		$ipfsDBid = decryptString($encid);
        $stmt = $conn->prepare("SELECT id, walletaddress, pagename, pageurl, ipfshash, txnhash, updated::timestamp(0), ispublic, anonymous FROM ".$conf['db_tableIPFSData']." WHERE id = ? ORDER BY updated DESC LIMIT 1");
        $stmt->bindParam(1, $ipfsDBid);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
			$row['anonymous'] = (bool)$row['anonymous'];
			$row['ispublic'] = (bool)$row['ispublic'];
			if ((strlen($row['txnhash']))&&($row['anonymous'] == TRUE)){
				$row['walletaddress'] = '';
				$row['saveinfo'] = "<a href='".$conf['activeBlockchainExplorer']."tx/".$row['txnhash']."' target='_blank'>Signed anonymously</a>";
			}elseif ((strlen($row['txnhash']))&&($row['anonymous'] == FALSE)){
				$row['saveinfo'] = "<a href='".$conf['activeBlockchainExplorer']."tx/".$row['txnhash']."' target='_blank'>Signed by ".$row['walletaddress']."</a>";
			} else {
				$row['walletaddress'] = '';
				$row['saveinfo'] = 'Unsigned';
			}

			if(filter_var($row['pageurl'], FILTER_VALIDATE_URL)) {
				$data[] = array(
					'qtzhead' =>	"Snapshot of: [<a href=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" target=\"_blank\">".$row['pagename']."</a>]",
					'qtzbody' =>	"<iframe src=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" frameborder=0 allowtransparency=true scrolling=yes class=\"ipfsiframe\"></iframe>",
					'qtzfooter' =>	"<div class=\"container\"><div class=\"row\"><div class=\"col-md-3 text-md-start text-end mb-md-0\"><a href='".$row['pageurl']."' target='_blank'><p>View Source</p></a></div><div class=\"col-md-9 text-md-end text-end\"><p>Saved on ".$row['updated']."</p><p>".$row['saveinfo']."</p></div></div>",
					'qtzhash' => $row['ipfshash']
				);
			} else {
				$data[] = array(
					'qtzhead' =>	"Snapshot of file: [<a href=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" target=\"_blank\">".$row['pagename']."</a>]",
					'qtzbody' =>	"<iframe src=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" frameborder=0 allowtransparency=true scrolling=yes class=\"ipfsiframe\"></iframe>",
					'qtzfooter' =>	"<div class=\"container\"><div class=\"row\"><div class=\"col text-md-end text-end\"><p>Saved on ".$row['updated']."</p><p>".$row['saveinfo']."</p></div></div>",
					'qtzhash' => $row['ipfshash']
				);
			}
        }
        if (!isset($data)) {
                $resultJSON = [ 'status' => 'ERR', 'results' => ''];
        }else{
                $resultJSON = [ 'status' => 'OK', 'results' => $data];
        }
        return json_encode($resultJSON);
}

function getUserRecords(){
	global $conn, $conf;
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }
	$stmt = $conn->prepare("SELECT id, walletaddress, pagename, pageurl, ipfshash, contentsource, txnhash, updated::timestamp(0), ispublic, anonymous FROM ".$conf['db_tableIPFSData']." WHERE ipfshash is NOT NULL AND LOWER(walletaddress) = ? ORDER BY id DESC");
	$stmt->bindParam(1, $_SESSION['walletAddress']);
	$stmt->execute();
	$total_data = $stmt->rowCount();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
		$row['anonymous'] = (bool)$row['anonymous'];
		$row['ispublic'] = (bool)$row['ispublic'];
		if ((strlen($row['txnhash']))&&($row['anonymous'] == TRUE)){
			$row['walletaddress'] = '';
		}elseif (!strlen($row['txnhash'])){
			$row['walletaddress'] = '';
		}
		$data[] = array(
			'cidhash'		=>	encryptString($row["id"]),
			'walletaddress'	=>	$row["walletaddress"],
			'created'		=>	$row['updated'],
			'pagename'		=>	$row['pagename'],
			'pageurl'		=>	$row['pageurl'],
			'ipfshash'		=>	$row['ipfshash'],
			'txnhash'		=>	$row['txnhash'],
			'anonymous'		=>	$row['anonymous'],
			'contentsource'	=>	$row['contentsource'],
			'ispublic'		=>	$row['ispublic']
		);
	}
	if (!isset($data)) {
		$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
		}else{
		$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	}
	return json_encode($resultJSON);
}

function togglePublic($encid = ''){
	global $conn, $conf;
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }
	$id=decryptString($encid);
	$data = array();
	$stmt = $conn->prepare("UPDATE ".$conf['db_tableIPFSData']." SET ispublic = NOT ispublic, updated = now() WHERE id = ? AND LOWER(walletaddress) = ?");
	$stmt->bindParam(1, $id);
	$stmt->bindParam(2, $_SESSION['walletAddress']);
	if ($stmt->execute() === TRUE) {
		$resultJSON = [ 'status' => 'OK'];
	} else {
		$resultJSON = [ 'status' => 'ERROR'];
	}
	return json_encode($resultJSON);
}

function getCreditsBalance(){
	global $conn, $conf;
	if ((!isset($_SESSION['is_loggedin']))||((isset($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] != true))){ return json_encode([ 'status' => 'ERR', 'balance' => 0]); }
	$stmt = $conn->prepare("SELECT SUM(txnValue) FROM ".$conf['db_tableUsersTxns']." WHERE LOWER(walletaddress) = ?");
	$stmt->bindParam(1, $_SESSION['walletAddress']);
	$stmt->execute();
	$creditsBalance=round($stmt->fetchColumn(),2);
	if (!$creditsBalance){
		$creditsBalance = 0; 
	}
	$resultJSON = [ 'status' => 'OK', 'balance' => ''.$creditsBalance.''];
	return json_encode($resultJSON);
}

function addCredits($txn, $amount, $txn_source){
	global $conn, $conf;
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }	
	$txnHash = strtolower($txn->transactionHash);
	$txnFrom = strtolower($txn->from);
	$rpc_response = eth_getTransactionByHash($conf['blockchainRPC'], $txnHash);
	$rpc_txnFrom = $rpc_response['result']['from'];
	$rpc_txnCurrency = $rpc_response['result']['to'];
	$rpc_txnData = $rpc_response['result']['input'];
	$rpc_txnRecipient = "0x".substr($rpc_txnData, 34, -64);
	$rpc_txnAmount = hexdec(substr($rpc_txnData, -64, 64))/10**18;

	$stmt = $conn->prepare("SELECT COUNT(txnHash) FROM ".$conf['db_tableUsersTxns']." WHERE LOWER(txnHash) = ?");
	$stmt->bindParam(1, $txnHash);
	$stmt->execute();
	$existing_hashes=$stmt->fetchColumn();
	if ($existing_hashes > 0){ die('Duplicate txn'); }

	if (strtolower($rpc_txnFrom) != strtolower($_SESSION['walletAddress'])) { die('wallet mismatch'); }
	if (strtolower($rpc_txnRecipient) != strtolower($conf['tokenDeployerAddress'])) { die('server mismatch'); }
	if (strtolower($rpc_txnCurrency) != strtolower($conf['tokenAddress'])) { die('token mismatch'); }
	$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableUsersTxns']." (walletAddress, serverAddress, txnHash, txnCurrency, txnValue, txnNote, txnData) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$stmt->bindParam(1, $rpc_txnFrom);
	$stmt->bindParam(2, $rpc_txnRecipient);
	$stmt->bindParam(3, $txnHash);
	$stmt->bindParam(4, $rpc_txnCurrency);
	$stmt->bindParam(5, $rpc_txnAmount);
	$stmt->bindParam(6, $txn_source);
	$stmt->bindParam(7, $rpc_txnData);
	if ($stmt->execute() === TRUE) {
		$topupResponse ='{"rpc_txnFrom": "'.$rpc_txnFrom.'", "rpc_txnRecipient": "'.$rpc_txnRecipient.'", "txnHash": "'.$txnHash.'", "rpc_txnCurrency": "'.$rpc_txnCurrency.'", "rpc_txnAmount": "'.$rpc_txnAmount.'", "rpc_txnData2": "'.$rpc_txnData.'"}';
		return ($topupResponse);
	} else {
		echo "Error" . $stmt->error;
		return ("Error" . $stmt->error);
	}
	$conn = null;
}

function eth_getTransactionByHash($server, $txnHash) {
    $request='{ "jsonrpc":"2.0", "method":"eth_getTransactionByHash", "params":[ "'.$txnHash.'" ], "id":0 }';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$server");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$rpc_response = curl_exec($ch);
	if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); }
	curl_close($ch);
	$rpc_response = json_decode($rpc_response, true);
	return $rpc_response;
}

function storeIPFS($fileID, $fileSource="") {
	global $conf;
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR 1' ]); }
	if (((!strlen($fileID))&&(!strlen($_FILES["file"]["name"])))||((!strlen($fileID))&&(!$fileSource=="zwiurlupload"))) { return json_encode([ 'status' => 'ERR 2' ]); }
	global $conn, $conf;
	require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ipfs/ipfs.class.php');
	$ipfs = new eth_sign\IPFS();
	$uploadedFilePath = $conf['cachedir'].$fileID;
	if (!ctype_alnum($fileID)) {
		$resultJSON = [ 'status' => 'ERROR: Illegal Filename' ];
		return json_encode($resultJSON);
	} elseif (file_exists($uploadedFilePath)) {
		# disable GZIP
		if ($conf['enablegzip'] == true) {
			gzCompressFile($uploadedFilePath);
			$uploadedFilePath="".$uploadedFilePath.".gz";
		}
		$ipfsFolder = '';
		$ipfsStoreResult=$ipfs->addFile($uploadedFilePath, $ipfsFolder);
		$qtzCIDHash = encryptString(updateIPFSTable($fileID, $ipfsStoreResult['Hash']));
		$resultJSON = [ 'status' => 'OK', 'hash' => ''.$ipfsStoreResult['Hash'].'', 'qtzCIDHash' => ''.$qtzCIDHash.'', 'size' => $ipfsStoreResult['Size'], 'filename' => ''.$ipfsStoreResult['Name'].'' ];
		return json_encode($resultJSON);
	} else {
		$resultJSON = [ 'status' => 'ERROR: Missing File' ];
		return json_encode($resultJSON);
	}
}

function updateIPFSTable($filename, $ipfsHash){
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }	
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT \"isDefaultPublic\" FROM ".$conf['db_tableUsers']." WHERE LOWER(walletaddress) = ? LIMIT 1");
	$stmt->bindParam(1, $_SESSION['walletAddress']);
	$stmt->execute();
	$isPublic=$stmt->fetchColumn();
	$stmt = $conn->prepare("UPDATE ".$conf['db_tableIPFSData']." set ipfshash = ?, walletaddress = ?, ispublic = ?, updated = now() WHERE filename = ?");
	$stmt->bindParam(1, $ipfsHash);
	$stmt->bindParam(2, $_SESSION['walletAddress']);
	$stmt->bindParam(3, $isPublic, PDO::PARAM_BOOL);
	$stmt->bindParam(4, $filename);
	if ($stmt->execute() === TRUE) {
		$stmt = $conn->prepare("select id from ".$conf['db_tableIPFSData']." where ipfshash = ? AND LOWER(walletaddress) = LOWER(?) AND filename = ?");
		$stmt->bindParam(1, $ipfsHash);
		$stmt->bindParam(2, $_SESSION['walletAddress']);
		$stmt->bindParam(3, $filename);
		$stmt->execute();
		$qtzCIDHash=$stmt->fetchColumn();
		return ($qtzCIDHash);
	} else {
		echo "Error" . $stmt->error;
		return ("Error" . $stmt->error);
	}
	$conn = null;
}
#################

function signHash($ipfsHash, $signAnonymously) {
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR 1' ]); }	
	global $conn, $conf;

	if ($signAnonymously != "") {
		$ipfsHashPayload = "$ipfsHash~".$_SESSION['walletAddress'];
		$anonymous = "false";
	} else {
		$ipfsHashPayload = $ipfsHash;
		$anonymous = "true";
	}

	# verify IPFS Hash belongs to wallet!!
	$stmt = $conn->prepare("SELECT COUNT(id) FROM ".$conf['db_tableIPFSData']." WHERE LOWER(ipfshash) = ? AND LOWER(walletaddress) = ?");
	$testhash = strtolower($ipfsHash);
	$testwallet = strtolower($_SESSION['walletAddress']);
	$stmt->bindParam(1, $testhash);
	$stmt->bindParam(2, $testwallet);
	$stmt->execute();
	$existing_hashes=$stmt->fetchColumn();
	if ($existing_hashes == 0){
        $resultJSON = [ 'status' => 'ERR', 'message' => 'Invalid Ownership Record' ];
        echo json_encode($resultJSON);
        return json_encode($resultJSON);
		die('Invalid Ownership Record');
	}

	$web3 = new Web3(new HttpProvider(new HttpRequestManager($conf['blockchainRPC'],5)));
	$contract = new Contract($web3->provider, $conf['contractABI'], 'latest');
	$eth = $web3->eth;

	// Get current Gas Price
	$eth->gasPrice(function ($err, $result) use (&$gasPrice) {
		if ($err !== null) { throw $err; }
		if (isset($result)) { $gasPrice = intval($result->toString()); }
	});


	// Get Gas Fee to use for 'set' $ipfsHashPayload
	$gasEstimate = $contract->at($conf['contractAddress'])->estimateGas('set', $ipfsHashPayload, ["from" => $conf['contractDeployerAddress']], function($err, $result) use (&$txnGas){
		if ($err !== null) { throw $err; }
		if (isset($result)) { $txnGas = intval($result->toString())*1.5; }
	});

   # UPDATE
   $stmt = $conn->prepare("SELECT SUM(txnValue) FROM ".$conf['db_tableUsersTxns']." WHERE LOWER(walletaddress) = ?");
   $stmt->bindParam(1, $_SESSION['walletAddress']);
   $stmt->execute();
   $creditsBalance=round($stmt->fetchColumn(),2);
   if (!$creditsBalance){
      $creditsBalance = 0;
   }
	error_log("$creditsBalance");
	error_log("$txnGas");

   if ($creditsBalance < $txnGas) {
      $resultJSON = [ 'status' => 'ERR', 'message' => 'Insufficient Credits to Sign Record' ];
      echo json_encode($resultJSON);
      return json_encode($resultJSON);
      die('Insufficient Credits to Sign Record');
   }

	// Get Nonce
	$eth->getTransactionCount($conf['contractDeployerAddress'], function ($err, $result) use (&$txnNonce) {
		if ($err !== null) { throw $err; }
		if (isset($result)) { $txnNonce = intval($result->toString()); }
	});

	// Generate Payload
	$ipfsPayload = "0x".$contract->at($conf['contractAddress'])->getData('set', $ipfsHashPayload);

	// Generate and Sign txn
	$txParams = [ "chainId" => $conf['blockchainID'], "nonce" => $txnNonce, "from" => $conf['contractDeployerAddress'], "to" => $conf['contractAddress'], "gas" => $txnGas, "gasPrice" => $gasPrice, "data" => $ipfsPayload, ];
	$transaction = new Transaction($txParams);
	$signedTransaction = $transaction->sign($conf['contractDeployerKey']);

	// Push Txn and get Hash
	$eth->sendRawTransaction("0x". $signedTransaction, function ($err, $result) use (&$txnHash) {
		if ($err !== null) {
			$resultJSON = [ 'status' => 'ERR', 'message' => 'already known' ];
		}
		if (isset($result)) {
			$txnHash = $result;
		}
	});
	if (strlen($txnHash)) {
		$_SESSION['daraTxnFee'] = getConfValue('daraTxnFee');
		deductCredits($txnHash, $_SESSION['daraTxnFee'], "Signed TXN", $ipfsHashPayload);
		updateSignedIPFSTable($ipfsHash, $txnHash, $anonymous);
		$resultJSON = [ 'status' => 'OK', 'message' => $txnHash ];
		return json_encode($resultJSON);
	}
}

function updateSignedIPFSTable($ipfsHash, $txnHash, $anonymous){
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }
	global $conn, $conf;
	$stmt = $conn->prepare("UPDATE ".$conf['db_tableIPFSData']." set txnhash = ?, anonymous = ?, updated = now() WHERE LOWER(ipfshash) = ? AND LOWER(walletaddress) = ?");
	$stmt->bindParam(1, $txnHash);
	$stmt->bindParam(2, $anonymous);
	$stmt->bindParam(3, strtolower($ipfsHash));
	$stmt->bindParam(4, strtolower($_SESSION['walletAddress']));
	if ($stmt->execute() === TRUE) {
		$updateIPFSDBResponse ='{"ipfsHash": "'.$ipfsHash.'", "walletAddress": "'.$_SESSION['walletAddress'].'", "txnhash":"'.$txnHash.'", "anonymous":"'.$anonymous.'"}';
		return ($updateIPFSDBResponse);
	} else {
		return ("Error" . $stmt->error);
	}
	$conn = null;
}

function deductCredits($txnHash, $txnFee, $txnSource, $txnData){
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }	
	global $conn, $conf;
	$txnRecipient = strtolower($conf['tokenDeployerAddress']);
	$txnCurrency = strtolower($conf['tokenAddress']);
	$txnFee = -$txnFee;
	if ($txnData == "") { $txnData = "undefined"; }
	$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableUsersTxns']." (walletAddress, serverAddress, txnHash, txnCurrency, txnValue, txnNote, txnData) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$stmt->bindParam(1, $_SESSION['walletAddress']);
	$stmt->bindParam(2, $txnRecipient);
	$stmt->bindParam(3, $txnHash);
	$stmt->bindParam(4, $txnCurrency);
	$stmt->bindParam(5, $txnFee);
	$stmt->bindParam(6, $txnSource);
	$stmt->bindParam(7, $txnData);
	if ($stmt->execute() === TRUE) {
		$topupResponse ='{"rpc_txnFrom": "'.$_SESSION['walletAddress'].'", "rpc_txnRecipient": "'.$txnRecipient.'", "txnHash": "'.$txnHash.'", "rpc_txnCurrency": "'.$txnCurrency.'", "rpc_txnAmount": "'.$txnFee.'", "rpc_txnData": "'.$txnData.'"}';
		return ($topupResponse);
	} else {
		return ("Error" . $stmt->error);
	}
	$conn = null;
}

function updateProfile($username='', $avatarIPFS='', $userEmail='', $userTwitter='', $userMedium='', $userBio='', $isPublicProfile=FALSE, $isDefaultPublic=FALSE, $isDefaultAnon=TRUE){
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }	
	global $conn, $conf, $anoncheck;
	if (strlen($username)&&(!preg_match("/^[A-Za-z0-9\-_]{3,128}$/",$username))) { return json_encode([ 'status' => 'ERR', 'message' => 'Invalid Username' ]); }
	if (strlen($userEmail)&&(!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$/",$userEmail))) { return json_encode([ 'status' => 'ERR', 'message' => 'Invalid Email Address' ]); }	
	if (strlen($userTwitter)&&(!preg_match("/^(((http:\/\/|https:\/\/)?(twitter.com\/|mobile.twitter.com\/))|(@))?([A-Za-z0-9_]{4,15})\/?$/", $userTwitter, $userTwitterMatches))) { return json_encode([ 'status' => 'ERR', 'message' => 'Invalid Twitter Handle' ]); }
	if (strlen($userMedium)&&(!preg_match("/^(((http:\/\/|https:\/\/)?([A-Za-z0-9_]{1,30})(\.medium.com))|(((http:\/\/|https:\/\/)?(medium.com\/@))?([A-Za-z0-9_]{1,30})))\/?$/",$userMedium, $userMediumMatches))) { return json_encode([ 'status' => 'ERR', 'message' => 'Invalid Medium Profile' ]); }
	if (strlen($userBio)&&(strlen($userBio)>160)) { return json_encode([ 'status' => 'ERR', 'message' => 'Invalid Bio' ]); }
	if (strlen($username)&&((!in_arrayi($_SESSION['walletAddress'], $conf['adminWallets']))&&(in_array(strtolower($username), $conf['reservedUsernames'])))) { return json_encode([ 'status' => 'ERR', 'message' => 'Reserved Username' ]); }

	if ((is_array($userMediumMatches))&&(end($userMediumMatches)=='.medium.com')){ 
	error_log("\n\n############# found \n\n");
		array_pop($userMediumMatches);
	} 
	foreach($userMediumMatches as $userMediumMatch){
		error_log("\n".$userMediumMatch."\n");
	}
	if (strlen($userTwitter)) { $userTwitter = end($userTwitterMatches); }
	if (strlen($userMedium)) { $userMedium = end($userMediumMatches); }
	if (!strlen($username)) { $username = null; }
	$anoncheck=$isDefaultAnon;
	$stmt = $conn->prepare("SELECT id FROM ".$conf['db_tableUsers']." WHERE username = ? AND username != '' AND username is NOT NULL AND LOWER(walletaddress) != ?");
	$stmt->bindParam(1, $username);
	$stmt->bindParam(2, $_SESSION['walletAddress']);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		return json_encode([ 'status' => 'ERR', 'message' => 'Username Taken' ]);
	}
	$stmt = $conn->prepare("UPDATE ".$conf['db_tableUsers']." SET username = ?, \"avatarIPFS\" = ?, \"userEmail\" = ?, \"userTwitter\" = ?, \"userMedium\" = ?, \"isPublicProfile\" = ?, \"isDefaultPublic\" = ?, \"isDefaultAnon\" = ?, updated = now(), \"userBio\" = ? WHERE LOWER(walletaddress) = ?");
	$stmt->bindParam(1, $username);
	$stmt->bindParam(2, $avatarIPFS);
	$stmt->bindParam(3, $userEmail);
	$stmt->bindParam(4, $userTwitter);
	$stmt->bindParam(5, $userMedium);
	$stmt->bindParam(6, $isPublicProfile, PDO::PARAM_BOOL);
	$stmt->bindParam(7, $isDefaultPublic, PDO::PARAM_BOOL);
	$stmt->bindParam(8, $isDefaultAnon, PDO::PARAM_BOOL);
	$stmt->bindParam(9, $userBio);
	$stmt->bindParam(10, $_SESSION['walletAddress']);
	if ($stmt->execute() === TRUE) {
		$resultJSON = [ 'status' => 'OK'];
	} else {
		$resultJSON = [ 'status' => 'ERR'];
	}
	return json_encode($resultJSON);
}

function getProfileData(){
	global $conn, $conf;
	if ((strlen($_SESSION['is_loggedin']))&&($_SESSION['is_loggedin'] == false)){ return json_encode([ 'status' => 'ERR' ]); }
	$stmt = $conn->prepare("SELECT * FROM ".$conf['db_tableUsers']." WHERE LOWER(walletaddress) = ? LIMIT 1");
	$stmt->bindParam(1, $_SESSION['walletAddress']);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
		$row['isPublicProfile']	= (bool)$row['isPublicProfile'];
		$row['isDefaultPublic']	= (bool)$row['isDefaultPublic'];
		$row['isDefaultAnon'] 	= (bool)$row['isDefaultAnon'];
		$data[] = array(
			'userID'			=>	encryptString($row["id"]),
			'walletaddress'		=>	$row["walletaddress"],
			'username'			=>	$row['username'],
			'avatarIPFS'		=>	$row['avatarIPFS'],
			'userEmail'			=>	$row['userEmail'],			
			'userTwitter'		=>	$row['userTwitter'],
			'userMedium'		=>	$row['userMedium'],
			'userBio'			=>	$row['userBio'],
			'isPublicProfile'	=>	$row['isPublicProfile'],
			'isDefaultPublic'	=>	$row['isDefaultPublic'],
			'isDefaultAnon'		=>	$row['isDefaultAnon'],
		);
	}
	if (!isset($data)) {
		$resultJSON = [ 'status' => 'ERR', 'result' => ''];
		}else{
		$resultJSON = [ 'status' => 'OK', 'result' => $data];
	}
	return json_encode($resultJSON);
}

function getPublicProfile($profileID){
	if ((!isset($profileID))||((isset($profileID))&&(!strlen($profileID)))){
		return json_encode([ 'status' => 'ERR', 'message' => 'invalid request' ]);
	}
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT * FROM ".$conf['db_tableUsers']." WHERE \"isPublicProfile\"=true AND ( LOWER(walletaddress) = ? OR LOWER(username) = ? ) LIMIT 1");
	$stmt->bindParam(1, $profileID);
	$stmt->bindParam(2, $profileID);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
		$data[] = array(
			'username'			=>	$row['username'],
			'avatarIPFS'		=>	$row['avatarIPFS'],
			'userEmail'			=>	$row['userEmail'],
			'userTwitter'		=>	$row['userTwitter'],
			'userMedium'		=>	$row['userMedium'],
			'userBio'			=>	$row['userBio'],
		);
	}
	if (!isset($data)) {
		$resultJSON = [ 'status' => 'ERR', 'result' => ''];
		}else{
		$resultJSON = [ 'status' => 'OK', 'result' => $data];
	}
	return json_encode($resultJSON);
}

function getPublicProfileRecords($profileID=''){
	if ((!isset($profileID))||((isset($profileID))&&(!strlen($profileID)))){
		return json_encode([ 'status' => 'ERR', 'message' => 'invalid request' ]);
	}
	global $conn, $conf;
	$stmt = $conn->prepare("SELECT * FROM ".$conf['db_tableUsers']." WHERE \"isPublicProfile\"=true AND ( LOWER(walletaddress) = ? OR LOWER(username) = ? ) LIMIT 1");
	$stmt->bindParam(1, $profileID);
	$stmt->bindParam(2, $profileID);
	$stmt->execute();
	if ($stmt->rowCount() != 1) {
		return json_encode([ 'status' => 'ERR', 'message' => 'Profile Not Found' ]);
	}
	$stmt = $conn->prepare("SELECT a.id, a.walletaddress, a.pagename, a.pageurl, a.ipfshash, a.txnhash, a.contentsource, a.updated::timestamp(0), a.ispublic, a.anonymous, b.username FROM ".$conf['db_tableIPFSData']." a, ".$conf['db_tableUsers']." b  WHERE a.walletaddress=b.walletaddress AND a.ipfshash is NOT NULL AND a.\"ispublic\"=true AND ( LOWER(a.walletaddress) = ? OR LOWER(b.username) = ? ) ORDER BY a.id ASC");
	$stmt->bindParam(1, $profileID);
	$stmt->bindParam(2, $profileID);
	$stmt->execute();
	$total_data = $stmt->rowCount();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
		$row['anonymous'] = (bool)$row['anonymous'];
		$row['ispublic'] = (bool)$row['ispublic'];
		if ((strlen($row['txnhash']))&&($row['anonymous'] == TRUE)){
			$row['txnhash'] = '0x0001';
		}
		$data[] = array(
			'cidhash'		=>	encryptString($row["id"]),
			'created'		=>	$row['updated'],
			'pagename'		=>	$row['pagename'],
			'pageurl'		=>	$row['pageurl'],
			'ipfshash'		=>	$row['ipfshash'],
			'txnhash'		=>	$row['txnhash'],
			'contentsource'	=>	$row['contentsource'],
			'anonymous'		=>	$row['anonymous']
		);
	}
	if (!isset($data)) {
		$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
		}else{
		$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	}
	return json_encode($resultJSON);
}

function deathrowSearch($searchQuery=''){
	global $conn, $conf;
	$data = array();
	if((isset($searchQuery))&&(strlen($searchQuery))){
		$condition = preg_replace('/[^A-Za-z0-9\- ]/', '%', $searchQuery);
		$condition = trim($condition);
		$condition = str_replace(" ", "%", $condition);
		if(strlen($condition)>2){
			$sample_data = array(
				':title'		=>	'%' . $condition . '%'
			);
			$query = "SELECT id, title, link, zwifile, ipfshash, created, stored, filesize, torrenthash, source, status, updated FROM ".$conf['db_deathrow']." WHERE (LOWER(title) LIKE LOWER(:title)) ORDER BY created DESC LIMIT ".$conf['dbsearchLimit']."";
			$stmt = $conn->prepare($query);
			$stmt->execute($sample_data);
		}
	}else{
		$query = "SELECT id, title, link, zwifile, ipfshash, created, stored, filesize, torrenthash, source, status, updated FROM ".$conf['db_deathrow']." ORDER BY created DESC LIMIT ".$conf['dbsearchLimit']."";
		$stmt = $conn->prepare($query);
		$stmt->execute();
	}
	$total_data = $stmt->rowCount();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		$data[] = array(
			'cidhash'		=>	encryptString($row["id"]),
			'title'	=>	$row['title'],
			'link'	=>	$row['link'],
			'zwifile'	=>	$row['zwifile'],
			'ipfshash'	=>	$row['ipfshash'],
			'created'	=>	$row['created'],
			'stored'	=>	$row['stored'],
			'filesize'	=>	$row['filesize'],
			'torrenthash'	=>	$row['torrenthash'],
			'status'	=>	$row['status'],
			'source'	=>	$row['source'],
			'updated'	=>	$row['updated']
		);
	}
	$conn = null;
	if (!isset($data)) {
			$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
	}else{
			$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	error_log(print_r($data, true));
	}
	return json_encode($resultJSON);
}

function encyclopediaSearch($searchQuery=''){
	global $conf, $conn;
	$cz_purge=array('/WorkingDraft','/Related Articles','/Catalogs','/Signed Articles','/Definition','/Test article not approved','/Approval','/Metadata', '/Bibliography', '/External Links');
	$encyclopedias=array("citizendium" => "https://en.citizendium.org/wiki/api.php", "wikipedia" => "https://en.wikipedia.org/w/api.php", "wikisource" => "https://en.wikisource.org/w/api.php" , "handwiki" => "https://handwiki.org/wiki/api.php");
	$data = array();
	if ($searchQuery!='#~#') {
		$wp_db_idrand = rand(0,9999);
		$wp_db_idrand = str_pad($wp_db_idrand,4,0,STR_PAD_LEFT);
		$wp_search_id = "".time()."".$wp_db_idrand."";
		$params = array( "action" => "opensearch", "namespace" => 0, "search" => "$searchQuery", "profile" => "normal", "limit" => $conf['wpsearchLimit'] );
		$ch1 = curl_init();
		$ch2 = curl_init();
		$ch3 = curl_init();
		$ch4 = curl_init();
		curl_setopt($ch1, CURLOPT_URL, "https://en.citizendium.org/wiki/api.php?".http_build_query($params)."");
		curl_setopt($ch2, CURLOPT_URL, "https://en.wikipedia.org/w/api.php?".http_build_query($params)."");
		curl_setopt($ch3, CURLOPT_URL, "https://en.wikisource.org/w/api.php?".http_build_query($params)."");
		curl_setopt($ch4, CURLOPT_URL, "https://handwiki.org/wiki/api.php?".http_build_query($params)."");
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		curl_setopt($ch2, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		curl_setopt($ch3, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		curl_setopt($ch4, CURLOPT_HTTPHEADER, array("Accept: application/json"));

		$mh = curl_multi_init();
		curl_multi_add_handle($mh, $ch1);
		curl_multi_add_handle($mh, $ch2);
		curl_multi_add_handle($mh, $ch3);
		curl_multi_add_handle($mh, $ch4); 

		do {
			$status = curl_multi_exec($mh, $active);
			if ($active) {
				curl_multi_select($mh);
			}
		} while ($active && $status == CURLM_OK);

		curl_multi_remove_handle($mh, $ch1);
		curl_multi_remove_handle($mh, $ch2);
		curl_multi_remove_handle($mh, $ch3);
		curl_multi_remove_handle($mh, $ch4);
		curl_multi_close($mh);
		 
		// all of our requests are done, we can now access the results
		$response['citizendium'] = curl_multi_getcontent($ch1);
		$response['wikipedia'] = curl_multi_getcontent($ch2);
		$response['wikisource'] = curl_multi_getcontent($ch3);
		$response['handwiki'] = curl_multi_getcontent($ch4);
		foreach ($encyclopedias as $encyclopediaName => $encyclopediaAPI) {
			$encresult = json_decode($response[$encyclopediaName],true);
			if($encyclopediaName == "citizendium"){
				foreach ($encresult[1] as $key => $value){
					if (preg_match('('.implode('|',$cz_purge).')', $value)){
						unset($encresult[1][$key]);
						unset($encresult[3][$key]);
					}
				}
			}
			$encresults=array_combine($encresult[1],$encresult[3]);
			$stmt = $conn->prepare("INSERT INTO ".$conf['db_wp_archive']." (title, link, wp_search_id, source) VALUES (:title, :link, :wp_search_id, :enc_source) ON CONFLICT(link) DO UPDATE SET wp_search_id = :wp_search_id");
			foreach ($encresults as $enctitle => &$enclink){
				$stmt->bindParam(':title', $enctitle);
				$stmt->bindParam(':link', $enclink);
				$stmt->bindParam(':enc_source', $encyclopediaName);
				$stmt->bindParam(':wp_search_id', $wp_search_id);
				if ($stmt->execute() !== TRUE) {
					$enc_archive_error ="$stmt->error";
				}
			}
		}
		$query = "SELECT id, title, link, zwifile, ipfshash, created::timestamp(0), stored::timestamp(0), filesize, torrenthash, source, updated::timestamp(0) FROM ".$conf['db_wp_archive']." WHERE wp_search_id = :wp_search_id ORDER BY title LIMIT ".$conf['dbsearchLimit']."";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':wp_search_id', $wp_search_id);
	} else {
		$query = "SELECT id, title, link, zwifile, ipfshash, created::timestamp(0), stored::timestamp(0), filesize, torrenthash, source, updated::timestamp(0) FROM ".$conf['db_wp_archive']." WHERE ipfshash IS NOT NULL ORDER BY stored DESC LIMIT ".$conf['dbsearchLimit']."";
		$stmt = $conn->prepare($query);
	}
	$stmt->execute();
	$total_data = $stmt->rowCount();
	$result = $stmt->fetchAll();
	foreach($result as $row) {
			$data[] = array(
				'cidhash'		=>	encryptString($row["id"]),
				'title'	=>	$row['title'],
				'link'	=>	$row['link'],
				'zwifile'	=>	$row['zwifile'],
				'ipfshash'	=>	$row['ipfshash'],
				'created'	=>	$row['created'],
				'stored'	=>	$row['stored'],
				'filesize'	=>	$row['filesize'],
				'torrenthash'	=>	$row['torrenthash'],
				'source'	=>	$row['source'],
				'updated'	=>	$row['updated']				
			);
	}
	$conn = null;
	if (!isset($data)) {
		$resultJSON = [ 'status' => 'ERR', 'count' => 0, 'results' => ''];
	}else{
		$resultJSON = [ 'status' => 'OK', 'count' => ''.$total_data.'', 'results' => $data];
	}
	return json_encode($resultJSON);
}

function genZWIFile($encid = ''){
		global $conn, $conf;
		if ((!isset($encid))||((isset($encid))&&($encid==''))) {
			die('Invalid Request 3');
		}
		$ipfsDBid = decryptString($encid);
        $stmt = $conn->prepare("SELECT id, title, link, zwifile, ipfshash, created, stored, torrenthash, source, updated FROM ".$conf['db_wp_archive']." WHERE id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(1, $ipfsDBid);
        $stmt->execute();
		$result=$stmt->fetch();
		$article=$result['title'];
		$source=$result['source'];
		if ((isset($article))&&(strlen($article))){
			$article=escapeshellarg($article);
			$source=escapeshellarg($source);
			$curts=microtime(true);
			$builddir="".$conf['zwibuildercachedir']."/$curts";
			chdir($conf['mybasedir']);
			mkdir($builddir, 0755);
			copy("".$conf['zwimb']."/html_footer.html", "".$builddir."/html_footer.html");
			copy("".$conf['zwimb']."/html_header.html", "".$builddir."/html_header.html");
			copy("".$conf['zwimb']."/short_description.py", "".$builddir."/short_description.py");
			copy("".$conf['zwimb']."/zwi_producer.py", "".$builddir."/zwi_producer.py");
			copy("".$conf['zwimb']."/zwi_mediawiki.py", "".$builddir."/zwi_mediawiki.py");
			copy("".$conf['zwimb']."/zwi_signature.py", "".$builddir."/zwi_signature.py");
			copy("".$conf['zwimb']."/auth.pub", "".$builddir."/auth.pub");
			copy("".$conf['zwimb']."/auth.pem", "".$builddir."/auth.pem");
			chdir($builddir);
			$cmd = 'python3 ./zwi_mediawiki.py -s '."$source".' -t '."$article".'';
			exec($cmd, $output, $resultCode);
			$qtzresult="";
			foreach($output as $key => $item){
				$qtzresult .= "$item<br>";
			}
			if (strpos($item, '<b>Created</b>:  ') === 0) {
				$finalFile = str_replace('<b>Created</b>:  ', '', $item);
				chmod("$finalFile", 0666);
				$signcmd = 'python3 ./zwi_signature.py -q -f -z '."$finalFile".' -i auth.pem -p DARA -a BULGARIA -k did:psqr:encycloreader.org/ksf/#publish';
				exec($signcmd, $signoutput, $signresultCode);
				$finalFileSize = filesize($finalFile);
				$finalFile=trim(escapeshellarg($finalFile), "'");
				$finalFile=urlencode($finalFile);
				#shell_exec('mv *.zwi '.$finalFile.'');
				// $b64finalFile=base64_encode($finalFile);
				// $b64finalFile=preg_replace('/=+$/', '', $b64finalFile);
				// shell_exec('mv *.zwi '.$b64finalFile.'');
				$stmt = $conn->prepare("UPDATE ".$conf['db_wp_archive']." set zwipath = ?, zwifile = ?, filesize = ?, updated = now() WHERE id = ?");
				$stmt->bindParam(1, $curts);
				$stmt->bindParam(2, $finalFile);
				$stmt->bindParam(3, $finalFileSize);
				$stmt->bindParam(4, $ipfsDBid);
				$stmt->execute();
			} else {
				$finalFile = "";
			}
			$data[] = array(
				'qtzlog' =>	$qtzresult,
				'qtzfile' =>	$finalFile,
			);
			shell_exec('rm -Rf html_footer.html html_header.html __pycache__ short_description.py zwi_producer.py zwi_mediawiki.py zwi_signature.py auth.pub auth.pem');
			chdir($conf['mybasedir']);
		}
        if ((isset($data))&&(isset($finalFile)&&(strlen($finalFile)))) {
                $resultJSON = [ 'status' => 'OK', 'results' => $data];
		}elseif (isset($data)) {
                $resultJSON = [ 'status' => 'WARN', 'results' => $data];
        }else{
                $resultJSON = [ 'status' => 'ERR', 'results' => ''];
        }
        return json_encode($resultJSON);
}

function storeZWIIPFS($encid = ''){
		global $conn, $conf;
		if ((!isset($encid))||((isset($encid))&&($encid==''))) {
			die('Invalid Request 3');
		}
		$ipfsDBid = decryptString($encid);
        $stmt = $conn->prepare("SELECT id, zwifile, zwipath, source FROM ".$conf['db_wp_archive']." WHERE id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(1, $ipfsDBid);
        $stmt->execute();
		$result=$stmt->fetch();
		$zwiFileName=urldecode($result['zwifile']);
		$zwiFilePath="".$conf['zwibuildercachedir']."".$result['zwipath']."";
		$zwiFileSource=$result['source'];
		require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ipfs/ipfs.class.php');
		$ipfs = new eth_sign\IPFS();
		$uploadedFilePath = ("$zwiFilePath/$zwiFileName");
		error_log($uploadedFilePath);
		if (file_exists($uploadedFilePath)) {
			$zwiFileName=urlencode($zwiFileName);
			$ipfsStoreResult=$ipfs->addFile("$uploadedFilePath", '');
			$ipfsHash=$ipfsStoreResult['Hash'];
			$ipfsIndexResult=$ipfs->cpFile('/ipfs/'.$ipfsHash.'', '/'.$zwiFileSource.'/'.$zwiFileName.'');
			$newWikiPediaIPNS=array_values($ipfs->stat('/'.$zwiFileSource.''))[0];
			$ipfsTorrentFileName=encryptString2($zwiFileName);
			$ipfsTorrentPath=encryptString2($ipfsHash);
			$ipfsTorrentCID=encryptString2($newWikiPediaIPNS);
			$wtEndpoint = "https://opfs.theimmutable.net/wt.php";
			$wtParams = array(
				"ifn" => "$ipfsTorrentFileName",
				"itp" => "$ipfsTorrentPath",
				"itc" => "$ipfsTorrentCID"
			);
			$ch = curl_init($wtEndpoint);
			$wtPayload = json_encode($wtParams);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $wtPayload );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$wtResult = curl_exec($ch);
			curl_close($ch);
			$result=json_decode($wtResult);
			$torrentHash=$result->torrent;
			$stmt = $conn->prepare("UPDATE ".$conf['db_wp_archive']." set ipfshash = ?, torrenthash = ?, stored = now() WHERE id = ?");
			$stmt->bindParam(1, $ipfsHash);
			$stmt->bindParam(2, $torrentHash);
			$stmt->bindParam(3, $ipfsDBid);
			$stmt->execute();
			$resultJSON = [ 'status' => 'OK', 'hash' => ''.$ipfsHash.'','torrenthash' => ''.$torrentHash.'', 'filename' => ''.$zwiFileName.''];
		} else {
			$resultJSON = [ 'status' => 'ERR', 'error' => 'Missing File' ];
		}
    return json_encode($resultJSON);
}

function getDRIPFSData($encid = ''){
        global $conn, $conf;
		if ((!isset($encid))||((isset($encid))&&($encid==''))) {
			die('Invalid Request 3');
		}
		$ipfsDBid = decryptString($encid);
		$stmt = $conn->prepare("SELECT id, title, link, zwifile, ipfshash, created, stored, torrenthash, updated FROM ".$conf['db_deathrow']." WHERE id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(1, $ipfsDBid);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
			$row['saveinfo'] = '<a href="'.$conf["ipfsGateway"].''.$row["ipfshash"].'?download&filename='.$row["zwifile"].'">Download '.$row["zwifile"].'';
			$data[] = array(
				'qtzhead' =>	"Snapshot of Article: [<a href=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" target=\"_blank\">".$row['title']."</a>]",
				'qtzbody' =>	"<iframe src=".$conf['ipfsGateway']."".$row['ipfshash']." frameborder=0 allowtransparency=true scrolling=yes class=\"ipfsiframe\"></iframe>",
				'qtzfooter' =>	"<div class=\"container\"><div class=\"row\"><div class=\"col-md-3 text-md-start text-end mb-md-0\"><a href='".$row['link']."' target='_blank'><p>View Source</p></a></div><div class=\"col-md-9 text-md-end text-end\"><p>Stored on ".$row['created']."</p><p>".$row['saveinfo']."</p></div></div>",
				'qtzhash' => $row['ipfshash']
			);
        }
        if (!isset($data)) {
                $resultJSON = [ 'status' => 'ERR', 'results' => ''];
        }else{
                $resultJSON = [ 'status' => 'OK', 'results' => $data];
        }
        return json_encode($resultJSON);
}

function getWPIPFSData($encid = ''){
        global $conn, $conf;
		if ((!isset($encid))||((isset($encid))&&($encid==''))) {
			die('Invalid Request 3');
		}
		$ipfsDBid = decryptString($encid);
		$stmt = $conn->prepare("SELECT id, title, link, zwifile, ipfshash, created, stored, torrenthash, updated FROM ".$conf['db_wp_archive']." WHERE id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(1, $ipfsDBid);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach($result as $row) {
			$row['saveinfo'] = '<a href="'.$conf["ipfsGateway"].''.$row["ipfshash"].'?download&filename='.$row["zwifile"].'">Download '.$row["zwifile"].'';
			$data[] = array(
				'qtzhead' =>	"Snapshot of Article: [<a href=\"".$conf['ipfsGateway']."".$row['ipfshash']."\" target=\"_blank\">".$row['title']."</a>]",
				'qtzbody' =>	"<iframe src=".$conf['ipfsGateway']."".$row['ipfshash']." frameborder=0 allowtransparency=true scrolling=yes class=\"ipfsiframe\"></iframe>",
				'qtzfooter' =>	"<div class=\"container\"><div class=\"row\"><div class=\"col-md-3 text-md-start text-end mb-md-0\"><a href='".$row['link']."' target='_blank'><p>View Source</p></a></div><div class=\"col-md-9 text-md-end text-end\"><p>Stored on ".$row['stored']."</p><p>".$row['saveinfo']."</p></div></div>",
				'qtzhash' => $row['ipfshash']
			);
        }
        if (!isset($data)) {
                $resultJSON = [ 'status' => 'ERR', 'results' => ''];
        }else{
                $resultJSON = [ 'status' => 'OK', 'results' => $data];
        }
        return json_encode($resultJSON);
}
?>
