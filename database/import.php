<?php

$sql = file_get_contents(__DIR__ . '/mytasks.sql');

$filename = __DIR__ . '/mytasks.sql';
$mysqlHost = 'mariadb';
$mysqlUser = 'user';
$mysqlPassword = '123';
$mysqlDatabase = 'api_db';

$mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase);

$mysqli->multi_query($sql);
