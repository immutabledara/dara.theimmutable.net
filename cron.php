#!/usr/bin/php
<?php
if (empty($_SERVER['DOCUMENT_ROOT']) && !empty(__DIR__)) { $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/'); }
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/common.php');

#foreach (getLiveDaraPricePKS() as $configItem => $configValue) {
foreach (getLiveDaraPrice() as $configItem => $configValue) {
        echo "$configItem -> $configValue \n";
        setConfValue($configItem, $configValue);
}

foreach (getLiveNetworkFees() as $configItem => $configValue) {
        echo "$configItem -> $configValue \n";
        setConfValue($configItem, $configValue);
}
$daraPrice = getConfValue('daraBNB');
$networkGas = getConfValue('gas');
$networkGasPrice = getConfValue('gasPrice');
$daraTxnFee = ceil((( $networkGas * $networkGasPrice / 10**19 ) / $daraPrice) * $conf['txnMarkup']);
echo "txnFee -> $daraTxnFee \n";
setConfValue('daraTxnFee', $daraTxnFee);

# Check uncredited stuff
?>
