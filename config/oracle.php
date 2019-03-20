<?php

return [
    'oracle' => [
        'driver'        => 'oracle',
        'tns'           => '  (DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = 172.30.31.15 )(PORT = 1521))
            (CONNECT_DATA =
              (SERVER = DEDICATED)
              (SERVICE_NAME = stgcj)
            )
          )',
        'host'          => '172.30.31.15',
        'port'          => '1521',
        'database'      =>  '',
        'username'      => 'fmsreader',
        'password'      => 'fmsreader2017',
        'charset'       => 'AL32UTF8',
        'prefix'        => '',
        'prefix_schema' => '',
        'edition'        => env('DB_EDITION', 'ora$base'),
        'server_version' => env('DB_SERVER_VERSION', '11g'),
    ],
];
