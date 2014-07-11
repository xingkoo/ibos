<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
    <div class="aside">
        <div class="sbbf">
            <ul class="nav nav-strip nav-stacked">
                <li class="active">
                    <a href="<?php echo $this->createUrl( 'default/index'); ?>">
                        <i class="o-art-doc"></i>
                        <?php echo $lang['Information center']; ?>
                    </a>
                    <ul id="tree" class="ztree posr">
                    </ul>
                </li>
            </ul>
        </div>
    </div>
	<!-- Sidebar -->

	<!-- Mainer right -->
	<div class="mcr">
		<form action="" class="form-horizontal">
			<div class="ct ctview ctview-art">
				<!-- 文章 -->
				<div class="art">
					<div class="art-container">
						<h1 class="art-title"><?php echo $subject; ?></h1>
						<div class="art-ct mb">
							<?php echo $content; ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>