#!/usr/bin/php
<?php
if (empty($_SERVER['DOCUMENT_ROOT']) && !empty(__DIR__)) { $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../'); }
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');

#if (empty($_SERVER['DOCUMENT_ROOT']) && !empty(__DIR__)) { $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../'); }

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');
$wikipediaAPI="https://en.wikipedia.org/w/api.php";
$deletedresults=array();
$finaldataset=array();
$deletedtitle='';

$query = "SELECT id, link FROM ".$conf['db_deathrow']." WHERE ipfshash IS NOT NULL AND status = 'Nominated' ORDER BY stored";
$stmt = $conn->prepare($query);
$stmt->execute();
$total_data = $stmt->rowCount();
$result = $stmt->fetchAll();
foreach($result as $row) {
	$data[] = array(
		'title' =>      str_replace('https://en.wikipedia.org/wiki/', '', $row['link'])
	);
}
$batch = array_chunk($data, 50);

foreach ($batch as $batchkey => $data) {
	$api_query='';
	foreach ($data as $datakey => $datacontent) {
		$api_query.= urldecode($datacontent['title'])."|";
	}
	$api_query=rtrim($api_query, "|");
	$ch = curl_init();
	$params = array( "action" => "query", "prop" => "revisions", "rvprop" => "timestamp", "format" => "json", "titles" => "$api_query" );
	$url = $wikipediaAPI . "?" . http_build_query($params);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$resp = curl_exec($ch);
	curl_close($ch);
	$deletedresults = json_decode($resp,true);
	$finaldataset[] = $deletedresults['query']['pages'];
}

foreach ($finaldataset as $dskey => $dsdata) {
	foreach ($dsdata as $pages => $pagesarray) {
		if ( $pages < 0 ) {
			$deletedtitle="https://en.wikipedia.org/wiki/".rawurlencode(str_replace(' ', '_', $pagesarray['title']));
			$stmt = $conn->prepare("UPDATE ".$conf['db_deathrow']." SET status = 'Deleted', updated = now() WHERE LOWER(link) = LOWER(:link)");
			echo "Deleted: $deletedtitle\n";
			$stmt->bindParam(':link', $deletedtitle);
         if ($stmt->execute() !== TRUE) {
		   	$enc_archive_error ="$stmt->error";
      	   echo "ERROR: $enc_archive_error\n";
         }
		}
	}
}
?>
