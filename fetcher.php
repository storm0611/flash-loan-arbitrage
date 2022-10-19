<?php

require_once('Pair.php');

$pair_logs_file = 'pair-logs.txt';
$processed_pair_logs_file = 'processed-pair-logs.txt';

$pair_lines = file_get_contents('pair-logs.txt');

$beep = false;

$dex_router_addresses = [
    'uniswap' => '0xE592427A0AEce92De3Edee1F18E0157C05861564',
    'mmfinance' => '0x51aBA405De2b25E5506DeA32A6697F450cEB1a17',
    'quickswap' => '0xa5E0829CaCEd8fFDD4De3c43696c57F7D7A678ff',
    'sushiswap' => '0x1b02dA8Cb0d097eB8D57A175b88c7D8b47997506',
    'apeswap' => '0xC0788A3aD43d79aa53B09c2EaCc313A787d1d607',
    'dfyn' => '0xA102072A4C07F06EC3B4900FDC4C7B80b6c57429',
    'radioshack' => '0xAf877420786516FC6692372c209e0056169eebAf',
    'vulcan' => '0xfE0E493564DB7Ae23a7b6Ea07F2C633Ee8f25f22',
    'jetswap' => '0x5C6EC38fb0e2609672BDf628B1fD605A523E5923',
    'elkfinance' => '0xf38a7A7Ac2D745E2204c13F824c00139DF831FFf',
    'polycat' => '0x94930a328162957FF1dd48900aF67B5439336cBD',
    'comethswap' => '0x93bcDc45f7e62f89a8e901DC4A0E2c6C427D9F25',
    'wault' => '0x3a1D87f206D12415f5b0A33E786967680AAb4f6d',
    'jamonswap' => '0xdBe30E8742fBc44499EB31A19814429CECeFFaA0',
    'nachofinance' => '0x4237a813604bD6815430d55141EA2C24D4543e44',
    'polyzap' => '0xE9c0Eb17b7B00dF30B34a538A4db249A5BD5ADd2',
    'dinoswap' => '0x6AC823102CB347e1f5925C634B80a98A3aee7E03'
];

if (!empty($pair_lines)) {
    $pair_lines = explode(PHP_EOL, $pair_lines);
    $profit = null;
    $swap_amount = null;
    foreach ($pair_lines as $index => $pair_line) {
        if (strpos($pair_line, 'Profit : ') === 0) {
            $profit = str_replace('Profit : ', '', $pair_line);
        }

        if (strpos($pair_line, 'Swap Amount : ') === 0) {
            $swap_amount = str_replace('Swap Amount : ', '', $pair_line);
        }

        if ($profit != null && $swap_amount != null) {
            if (!$beep && ($profit > ($swap_amount * 0.01))) {
                $beep = true;
            }
        }

    }
}

if (!empty($pair_lines)) {
    $pair_infos = [];
    $max_price_in_pair = null;
    $pair_to_swap_in_smart_contract = null;
    $processed_pair_logs = [];

    foreach ($pair_lines as $index => &$pair_line) {
        $pair_no_matches = preg_match('/Pair(\d+) : /', $pair_line, $matches);
        $pair_address = preg_replace('/Pair(\d+) : /', '', $pair_line);

        if (!empty($matches) && !empty($matches[0]))
            $pair_line = $matches[0] . $pair_address;

        if (!empty($pair_address) && is_string($pair_address)) {
            $pair = (new Pair($pair_address))->info();
            if ($pair && !empty($pair->chainId)) {
                if (!empty($matches) && !empty($matches[0])) {

                    $processed_pair_logs[] = $matches[0] . $pair_address;

                    $pair_block = [
                        'pairNo' => (!empty($matches[0])) ? $matches[0] : '',
                        'pairAddress' => $pair->pairAddress,
                        'dexId' => $pair->dexId,
                        'dexRouterAddress' => (!empty($dex_router_addresses) && !empty($dex_router_addresses[$pair->dexId])) ? $dex_router_addresses[$pair->dexId] : '',
                        'symbol' => $pair->baseToken->symbol . '/' . $pair->quoteToken->symbol,
                        'priceUSD' => $pair->priceUsd,
                    ];

                    if ($max_price_in_pair == null) {
                        $max_price_in_pair = $pair_block['priceUSD'];
                    }

                    if ($pair_block['priceUSD'] > $max_price_in_pair) {
                        $max_price_in_pair = $pair_block['priceUSD'];
                        $pair_to_swap_in_smart_contract = $pair_block['pairNo'];
                    }

                    $pair_infos[] = $pair_block;
                }
            }
        }

        unset($pair_lines[$index]);
    }

    $updated_pair_lines = implode(PHP_EOL, $pair_lines);
    if (!empty($updated_pair_lines))
        $updated_pair_lines .= PHP_EOL;

    $processed_pair_logs = implode(PHP_EOL, $processed_pair_logs);
    if (!empty($processed_pair_logs))
        $processed_pair_logs .= PHP_EOL;

    $file_handle = fopen($pair_logs_file, 'w');
    fwrite($file_handle, $updated_pair_lines);
    fclose($file_handle);

    file_put_contents($processed_pair_logs_file, $processed_pair_logs, FILE_APPEND);

    if ($beep) {
        foreach ($pair_infos as $pair_info) {
            echo $pair_info['pairNo'] . $pair_info['pairAddress'] . ' - ' . ucfirst($pair_info['dexId']) . ', ' . $pair_info['dexRouterAddress'] . ', ' . $pair_info['symbol'] . ', ' . $pair_info['priceUSD'] . PHP_EOL;
        }
    
        if (!empty($swap_amount)) {
            echo 'Swap Amount: ' . $swap_amount . PHP_EOL;
        }
        passthru('/usr/bin/play beep.wav > /dev/null 2>&1');
    }

    exit;
}
