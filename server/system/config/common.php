<?php

/**
 * 程序启动配置文件 
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
return array(
    // 程序根目录
    'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system',
    // 程序名称
    'name' => 'IBOS',
    // 默认控制器 - 主模块下的index
    'defaultController' => 'main/default/index',
    // 框架核心语言
    'sourceLanguage' => 'en_us',
    // 定义所用组件
    'components' => array(
        // --------- 全局与系统组件 ---------
        // 浏览器组件，检测用户浏览器版本及信息
        'browser' => array(
            'class' => 'application.core.components.ICBrowser',
        ),
        'category' => array(
            'class' => 'ICCategory'
        ),
        'db' => array(
            'enableProfiling' => YII_DEBUG,
            'emulatePrepare' => true
        ),
        // 日志记录组件
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error',
                ),
                array(
                    'class' => 'ICLog',
                    'levels' => 'admincp,illegal,login,action,db',
                )
            ),
        ),
        // 全局认证组件
        'authManager' => array(
            'class' => 'ICAuthManager'
        ),
        // 主题管理组件
        'themeManager' => array(
            'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system/theme',
            'class' => 'ICThemeManager',
        ),
        // IBOS资源管理组件
        'assetManager' => array(
            'class' => 'ICAssetManager'
        ),
        // URL资源管理器
        'urlManager' => array(
            'urlFormat' => 'get',
            'caseSensitive' => false,
            'showScriptName' => false,
            'rules' => array(
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>', // Not Coding Standard
            )
        ),
        //语言包基本目录和扩展目录配置
        'messages' => array(
            'class' => 'ICMessageSource',
            'basePath' => PATH_ROOT . DIRECTORY_SEPARATOR . 'system/language'
        ),
        // 性能检测组件，部署模式可删除掉这行
        'performance' => array(
            'class' => 'application.core.components.ICPerformanceMeasurement'
        ),
        'cache' => array(
            'class' => 'application.core.components.ICCache'
        )
    ),
    // 自动加载文件路径
    'import' => array(
        // 核心组件库
        'application.core.components.*',
        // 核心控制器
        'application.core.controllers.*',
        // 全局异常处理
        'application.core.exceptions.*',
        // 全局数据层抽象基类
        'application.core.model.*',
        // 全局组件库
        'application.core.utils.*',
        // 全局模块处理
        'application.core.modules.*',
        // 全局widget
        'application.core.widgets.*',
        // 核心驱动引擎
        'application.core.engines.*',
        'ext.*',
        // 扩展：缓存驱动
        'ext.cachedriver.*',
        // 扩展：更新缓存
        'ext.cacheprovider.*',
        'ext.enginedriver.*'
    ),
    'params' => array(
        // Yii版本
        'yiiVersion' => '1.1.13',
        'supportedLanguages' => array(
            'en' => 'English',
            'cn' => 'zh-cn',
        ),
        // 默认翻每页的页数
        'basePerPage' => 10,
        // 等待跳转时间
        'timeout' => 3,
        'cacheopen' => 1
    ),
    'preload' => array(
        'db', 'cache', 'log'
    ),
);
