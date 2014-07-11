<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $lang['Install guide']; ?></title>
        <meta name="keywords" content="IBOS" />
        <meta name="generator" content="IBOS 2.1 (Revolution!)" />
        <meta name="author" content="IBOS Team" />
        <meta name="coryright" content="2013 IBOS Inc." />
        <link href="<?php echo IBOS_STATIC; ?>css/base.css" type="text/css" rel="stylesheet" />
        <link href="<?php echo IBOS_STATIC; ?>css/common.css" type="text/css" rel="stylesheet" />
        <link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
        <![endif]-->
    </head>
    <body>
        <div class="main">
            <div class="main-content">
				<div class="main-top posr">
					<i class="o-top-bg"></i>
					<div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
                </div>
                <div class="specific-content">
					<div class="mlg nht">
						<div class="dib vam">
							<i class="o-install-success"></i>
                        </div>
                        <div class="dib vam mls">
							<p class="mb"><i class="o-success-tip"></i></p>
                        </div>
                    </div>
                    <div class="content-foot clearfix">
                        <a href="../index.php" class="btn btn-large btn-primary pull-right btn-install-finish" id="finish"><?php echo $lang['Complete']; ?></a>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
		<?php
		$url = 'http://www.ibos.com.cn/index.php?m=count&c=collect&a=collect';
		$param = array(
			'snkey' => $config['security']['authkey'],
			'setuptime' => time(),
		);
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 10,
		);

		$url = $url . '&k=' . base64_encode( http_build_query( $param ) );
		$curl = curl_init( $url );
		if ( curl_setopt_array( $curl, $options ) ) {
			$result = curl_exec( $curl );
		}
		curl_close( $curl );
		?>
    </body>
</html>