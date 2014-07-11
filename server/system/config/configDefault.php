<?php

/* * *******************************************************************************
 * IBOS, Inc. Copyright (C) 2013 IBOS Team.
 * VISIT : www.ibos.com.cn
 * $Id: configDefault.php 3652 2014-06-11 02:18:18Z zhangrong $
 * ****************************************************************************** */
 
return array(
    // ----------------------------  CONFIG ENV  -----------------------------//
    'env' => array(
        'installed' => '{installed}',
        'language' => 'zh_cn',
        'theme' => 'default'
    ),
    // ----------------------------  CONFIG DB  ----------------------------- //
    'db' => array(
        'host' => '{host}',
        'port' => '{port}',
        'dbname' => '{dbname}',
        'username' => '{username}',
        'password' => '{password}',
        'tableprefix' => '{tableprefix}',
        'charset' => '{charset}'
    ),
    // --------------------------  CONFIG CACHE  --------------------------- //
    'cache' => array(
        'prefix' => 'CV8IbM_',
        'eaccelerator' => 1,
        'apc' => 1,
        'xcache' => 1,
        'wincache' => 1,
        'filecache' => 1,
        /*'memcache' => array(
            array(
                'host' => '127.0.0.1',
                'port' => 11211,
                'timeout' => 1,
            )
        )*/
    ),
// -------------------------  CONFIG SECURITY  -------------------------- //
    'security' => array(
        'authkey' => '{authkey}'
    ),
// --------------------------  CONFIG COOKIE  --------------------------- //
    'cookie' => array(
        'cookiepre' => '{cookiepre}_',
        'cookiedomain' => '',
        'cookiepath' => '/',
    )
);
