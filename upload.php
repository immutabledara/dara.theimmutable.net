<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');

if (!empty($_POST['request'])) {
	$action = strtolower($_POST['request']);
} else {
	die('Invalid Request');
}

if (($action == 'uploadpage')&&(!strlen($_POST['data']))) {
	die('Transfer Interrupted 1');
}

switch($action) {
	case 'uploadpage':
		$contentsource="extension";
		$userIP = encryptString($_SESSION['userIP']);
		$pageurl = $_POST['pageurl'];
		$pagename = $_POST['pagename'];
		$fid="".uniqid()."".time()."".uniqid()."";
		$filepath="".$conf['cachedir']."".$fid."";
		if (!empty($_POST['data'])){
			$data = $_POST['data'];
			$fp = fopen($filepath, "wb");
			fwrite($fp, $data);
			fclose($fp);
			$filesize = filesize($filepath);
			$stmt = $conn->prepare("INSERT INTO ".$conf['db_tableIPFSData']." (pageurl, pagename, filename, filesize, contentsource, sourceaddress) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt->bindParam(1, $pageurl);
			$stmt->bindParam(2, $pagename);
			$stmt->bindParam(3, $fid);
			$stmt->bindParam(4, $filesize);
			$stmt->bindParam(5, $contentsource);
			$stmt->bindParam(6, $userIP);
			if ($stmt->execute() === TRUE) {
				echo $fid;
				$conn = null;
			}
		}
		break;

		default: die('error');
}
?>
