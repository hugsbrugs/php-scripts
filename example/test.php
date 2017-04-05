<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Scripts\Scripts as Scripts;


$cmd = 'ls -lsa';
$log_file = __DIR__ . '/test.log';
$res = Scripts::run($cmd, $log_file);
error_log(print_r($res, true));

$running = Scripts::is_running($res['data']['pid']);
error_log('running : ' . var_dump($running));
sleep(1);
$running = Scripts::is_running($res['data']['pid']);
error_log('running : ' . var_dump($running));




$cmd = 'tail -f ' . $log_file;
$res = Scripts::run($cmd, $log_file);
print_r($res, true);

$running = Scripts::is_running($res['data']['pid']);
error_log('running : ' . var_dump($running));
sleep(1);
$running = Scripts::is_running($res['data']['pid']);
error_log('running : ' . var_dump($running));

$cpu_mem = Scripts::get_pid_cpu_mem($res['data']['pid']);
error_log('cpu_mem : ' . print_r($cpu_mem, true));

$killed = Scripts::kill($res['data']['pid']);
error_log('killed : ' . var_dump($killed));


// Scripts::run_live($cmd)
