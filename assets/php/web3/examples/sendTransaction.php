<?php

require('./exampleBase.php');
$accounts = array ('0xb68707D51fcf8A605D8e66d4A0799F529F14Edb4', '0xEFc7Cdf5C93C5b7ACdBA6A6C4603D561eae589cF');
$eth = $web3->eth;

echo 'Eth Send Transaction' . PHP_EOL;
$eth->accounts(function ($err, $accounts) use ($eth) {
$accounts = array ('0xb68707D51fcf8A605D8e66d4A0799F529F14Edb4', '0xEFc7Cdf5C93C5b7ACdBA6A6C4603D561eae589cF');
    if ($err !== null) {
        echo 'Error: ' . $err->getMessage();
        return;
    }
    $fromAccount = $accounts[0];
    $toAccount = $accounts[1];

    // get balance
    $eth->getBalance($fromAccount, function ($err, $balance) use($fromAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo $fromAccount . ' Balance: ' . $balance . PHP_EOL;
    });
    $eth->getBalance($toAccount, function ($err, $balance) use($toAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo $toAccount . ' Balance: ' . $balance . PHP_EOL;
    });

    // send transaction
    $eth->sendTransaction([
        'from' => $fromAccount,
        'to' => $toAccount,
        'value' => '0x11'
    ], function ($err, $transaction) use ($eth, $fromAccount, $toAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo 'Tx hash: ' . $transaction . PHP_EOL;

        // get balance
        $eth->getBalance($fromAccount, function ($err, $balance) use($fromAccount) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo $fromAccount . ' Balance: ' . $balance . PHP_EOL;
        });
        $eth->getBalance($toAccount, function ($err, $balance) use($toAccount) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo $toAccount . ' Balance: ' . $balance . PHP_EOL;
        });
    });
});
