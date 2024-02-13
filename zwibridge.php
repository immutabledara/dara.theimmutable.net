<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/assets/php/ipfs/ipfs.class.php');
$encycloreaderBaseURL=$conf['encycloreaderBaseURL'];

if(isset($_GET['id'])) {
        if (stripos($_GET['id'], "encycloreader.org/db") !== false) {
                $url_components = parse_url($_GET['id']);
                parse_str($url_components['query'], $params);
                $_GET['id']=$params['id'];
        }
        $encycloreaderCID = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['id']), 0, 10);
        if (strlen($encycloreaderCID) != 10){
                header("HTTP/1.0 404 Not Found");
                echo '<html><head><meta name="color-scheme" content="light dark"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">404 page not found.</pre></body></html>';
                die;
        }
}else{
        header("HTTP/1.0 404 Not Found");
        echo '<html><head><meta name="color-scheme" content="light dark"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">404 page not found..</pre></body></html>';
        die;
}

$encycloreaderFile="".$conf['zwicachedir']."$encycloreaderCID";
$encycloreaderURL="".$encycloreaderBaseURL."?id=".$encycloreaderCID."";
if(!is_file($encycloreaderFile)){
        $ipfsFile = fopen($encycloreaderFile, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$encycloreaderURL);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FILE, $ipfsFile);
        if(curl_exec($ch) === false) {
                echo 'Failed to retrieve content: ' . curl_error($ch);
        } else {
                $ipfs = new eth_sign\IPFS();
                $ipfsFolder='';
                $ipfsStoreResult=$ipfs->addFile($encycloreaderFile, $ipfsFolder);
        }
        curl_close($ch);
        fclose($ipfsFile);
}else{
        $ipfs = new eth_sign\IPFS();
        $ipfsFolder='';
        $ipfsStoreResult=$ipfs->addFile($encycloreaderFile, $ipfsFolder);
}

if (strlen($ipfsStoreResult['Hash']) == 46){
        header("Location: ".$conf['ipfsGateway']."".$ipfsStoreResult['Hash']."");
        exit();
}else{
        header("HTTP/1.0 404 Not Found");
        echo '<html><head><meta name="color-scheme" content="light dark"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">404 page not found...</pre></body></html>';
        die;
}

?>
