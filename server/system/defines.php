<?php

/**
 * 全局常量定义文件 
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
if ( !defined( 'PATH_ROOT' ) ) {
    exit();
}
$debug = true;
// --------------------------------------------------------------
// 调试模式下，报告所有错误
// --------------------------------------------------------------
if ( $debug ) {
    error_reporting( E_ALL | E_STRICT );
} else {
    error_reporting( E_ERROR );
}
// 字符编码
define( 'CHARSET', 'utf-8' );
// 调试模式
define( 'YII_DEBUG', $debug );
// 错误等级
define( 'YII_TRACE_LEVEL', $debug ? 3 : 0  );
define( 'YII_ENABLE_EXCEPTION_HANDLER', $debug );
// 是否本地环境
define( 'LOCAL', strtolower( ENGINE ) === 'local' ? true : false  );
// 断言配置
// 启用断言
assert_options( ASSERT_ACTIVE, FALSE );
// 为每个失败的断言产生一个 PHP 警告
assert_options( ASSERT_WARNING, FALSE );
// 在断言失败时中止执行
assert_options( ASSERT_BAIL, FALSE );
// 在断言表达式求值时不禁用error_reporting
assert_options( ASSERT_QUIET_EVAL, FALSE );
//assert_options(ASSERT_CALLBACK, ''); //断言出错回调函数
require 'version.php';
