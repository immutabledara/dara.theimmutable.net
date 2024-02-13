<?php

require('./exampleBase.php');

$eth = $web3->eth;

echo 'Eth Get Account and Balance' . PHP_EOL;
$eth->accounts(function ($err, $accounts) use ($eth) {
$accounts = array ('0xb68707D51fcf8A605D8e66d4A0799F529F14Edb4', '0xEFc7Cdf5C93C5b7ACdBA6A6C4603D561eae589cF');
    if ($err !== null) {
        echo 'Error: ' . $err->getMessage();
        return;
    }
    foreach ($accounts as $account) {
        echo 'Account: ' . $account . PHP_EOL;

        $eth->getBalance($account, function ($err, $balance) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo 'Balance: ' . $balance . PHP_EOL;
        });
    }
});
