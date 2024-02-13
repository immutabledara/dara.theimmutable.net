#!/usr/bin/php
<?php
if (empty($_SERVER['DOCUMENT_ROOT']) && !empty(__DIR__)) { $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../'); }

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ipfs/ipfs.class.php');
$dateStamp=date("Ymd");
$zwipath="".$conf['deathrowdir']."/$dateStamp";
$deathrowFiles = scandir($zwipath, SCANDIR_SORT_NONE);
$deathrowZWIs= preg_grep("/.zwi$/", $deathrowFiles);
$status="Nominated";
$source="wikipedia";
foreach ($deathrowZWIs as $deathrowZWI) {
	$zwifile=$deathrowZWI;
	$title=substr(substr($deathrowZWI, 0, -4), 10);
	$url="https://en.wikipedia.org/wiki/$title";
	$title=urldecode(strtr($title, '_', ' '));
	echo "Storing $title ....";
	$ipfs = new eth_sign\IPFS();
	$deathrowZWIPath = ("$zwipath/$deathrowZWI");
	$filesize=filesize($deathrowZWIPath);
	if ((file_exists($deathrowZWIPath))&&($filesize>1000)) {
		$ipfsStoreResult=$ipfs->addFile($deathrowZWIPath, '');
		$ipfsIndexResult=$ipfs->cpFile('/ipfs/'.$ipfsStoreResult['Hash'].'', '/deathrow/'.$deathrowZWI.'');
		$ipfshash=$ipfsStoreResult['Hash'];
		$stmt = $conn->prepare("INSERT INTO ".$conf['db_deathrow']." (title, link, zwifile, filesize, source, ipfshash, status) VALUES (:title, :link, :zwifile, :filesize, :source, :ipfshash, :status) ON CONFLICT(link) DO NOTHING");
		$stmt->bindParam(':title', $title);
		$stmt->bindParam(':link', $url);
		$stmt->bindParam(':filesize', $filesize);
		$stmt->bindParam(':zwifile', $zwifile);
		$stmt->bindParam(':source', $source);
		$stmt->bindParam(':ipfshash', $ipfshash);
		$stmt->bindParam(':status', $status);
		if ($stmt->execute() !== TRUE) {
			$enc_archive_error ="$stmt->error";
			echo "ERROR: $enc_archive_error\n";
		}
	}
	echo "done!\n";
}
?>
