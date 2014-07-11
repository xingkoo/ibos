<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/recruit.css?<?php echo VERHASH;?>">
<!-- load css end-->

<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $sidebar;?>
        <!-- Sidebar end -->

        <!-- Mainer right -->
        <div class="mcr">
            <!-- Mainer nav -->
            <div class="mc-header">
                <ul class="mnv nl clearfix">
                    <?php echo $type = EnvUtil::getRequest("type");?>
                    <li <?php if ($type == "arrange") echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index", array("type" => "arrange"));?>">
                            <i class="o-rsm-arrange"></i>
                            <?php echo $lang["To be arranged"];?>
                        </a>
                    </li>
                    <li <?php if ($type == "audition") echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index", array("type" => "audition"));?>">
                            <i class="o-rsm-audition"></i>
                            <?php echo $lang["Audition"];?>
                        </a>
                    </li>
                    <li <?php if ($type == "hire") echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index", array("type" => "hire"));?>">
                            <i class="o-rsm-hire"></i>
                            <?php echo $lang["Hire"];?>
                        </a>
                    </li>
                    <li <?php if ($type == "eliminate") echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index", array("type" => "eliminate"));?>">
                            <i class="o-rsm-eliminate"></i>
                            <?php echo $lang["Eliminate"];?>
                        </a>
                    </li>
                    <li <?php if ($type == "flag") echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index", array("type" => "flag"));?>">
                            <i class="o-rsm-flag"></i>
                            <?php echo $lang["Marked"];?>
                        </a>
                    </li>
                    <li <?php if (!isset($type)) echo 'class="active"';?> >
                        <a href="<?php echo $this->createUrl("resume/index");?>">
                            <i class="o-rsm-all"></i>
                            <?php echo $lang["Entire"];?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="page-list">
                <div class="page-list-header">
                    <div class="btn-toolbar pull-left">
						<!-- 导入简历 -->
                        <a href="javascript:;" class="btn btn-primary pull-left" id="imp_rsm"><?php echo $lang["Importing resume"];?></a>
                        <a href="<?php echo $this->createUrl("resume/add");?>" class="btn btn-primary pull-left" ><?php echo $lang["New"];?></a>
						<a href="javascript:;" class="btn pull-left" id="del_rsm" data-click="deleteResumes"><?php echo $lang["Delete"];?></a>
						<!-- 发送邮件-->
                        <a href="javascript:;" class="btn  pull-left" data-click="sendMail"><?php echo $lang["Send mail"];?></a>	
                        <div class="btn-group">
                            <a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown">
                                <?php echo $lang["Status"];?>
                                <i class="caret"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:;" data-click="moveToArranged"><?php echo $lang["To be arranged"];?></a></li>
                                <li><a href="javascript:;" data-click="moveToInterview"><?php echo $lang["Audition"];?></a></li>
                                <li><a href="javascript:;" data-click="moveToEmploy"><?php echo $lang["Hire"];?></a></li>
                                <li><a href="javascript:;" data-click="moveToEliminate"><?php echo $lang["Eliminate"];?></a></li>
                            </ul>
                        </div>
                    </div>
                    <form action="<?php echo $this->createUrl("resume/search");?>" method="post">
                        <div class="search search-config pull-right span3">
                            <input type="text" placeholder="Search"  id="mn_search" name="keyword" nofocus>
                            <a href="javascript:;">search</a>
                            <input type="hidden" name="type" value="normal_search">
                        </div>
                    </form>
                </div>
                <div class="page-list-mainer">
				<?php if (0 < count($resumeList)) : ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox">
                                        <input type="checkbox" name="" data-name="resume[]">
                                    </label>
                                </th>
                                <th><?php echo $lang["Full name"];?></th>
                                <th width="100"><?php echo $lang["Job candidates"];?></th>
                                <th width="30"><?php echo $lang["Sex"];?></th>
                                <th width="30"><?php echo $lang["Age"];?></th>
                                <th width="40"><?php echo $lang["Record of formal schooling"];?></th>
                                <th width="70"><?php echo $lang["Work years"];?></th>
                                <th width="70"><?php echo $lang["CV status"];?></th>
                                <th width="80"><?php echo $lang["Mark"];?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumeList as $resume ) : ?>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" name="resume[]" value="<?php echo $resume["resumeid"];?>">
                                        </label>
                                    </td>
                                    <td>
                                        <a href="<?php echo $this->createUrl("resume/show", array("resumeid" => $resume["resumeid"]));?>"><?php echo $resume["realname"];?></a>
                                    </td>
                                    <td>
                                        <?php echo $resume["targetposition"];?>
                                    </td>
                                    <td>
                                        <?php echo $resume["gender"];?>
                                    </td>
                                    <td>
                                        <?php echo $resume["age"];?>
                                    </td>
                                    <td>
                                        <?php echo $resume["education"];?>
                                    </td>
                                    <td>
                                        <?php echo $resume["workyears"];?>
                                    </td>
                                    <td>                                        
										<?php echo $resume["status"];?>
									</td>
                                    <td>
                                        <?php if ($resume["flag"] == 1) : ?>
											<a href="javascript:" data-click="toggleResumeMark" data-id="<?php echo $resume["resumeid"];?>" data-flag="0" title="<?php echo $lang["Unmark"];?>"><i class="o-rsm-mark"></i></a>
                                        <?php else : ?>
                                            <a href="javascript:" data-click="toggleResumeMark" data-id="<?php echo $resume["resumeid"];?>" data-flag="1" title="<?php echo $lang["Mark"];?>"><i class="o-rsm-unmark"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
				<?php else : ?>
					<div class="no-data-tip"></div>
				<?php endif; ?>
                </div>
                <div class="page-list-footer">
                    <div class="pull-right">
                        <?php $this->widget("IWPage", array("pages" => $pages));?>
                    </div>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>

<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl("resume/search");?>" method="post">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Full name"];?></label>
                <div class="controls">
                    <input type="text" id="realname" name="search[realname]">					
                </div>
            </div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Job candidates"];?></label>
				<div class="controls">
					<input type="text" name="search[positionid]" id="user_position">
				</div>
			</div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Sex"];?></label>
                <div class="controls">
                    <select name="search[gender]"  id="gender">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="1"><?php echo $lang["Male"];?></option>
                        <option value="2"><?php echo $lang["Female"];?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Age"];?></label>
                <div class="controls">
                    <select name="search[ageRange]"  id="ageRange">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="18-30">18-30</option>
                        <option value="30-40">30-40</option>
                        <option value="40-50">40-50</option>
                        <option value="50-60">50-60</option>
                        <option value="60-80">60-80</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Record of formal schooling"];?></label>
                <div class="controls">
                    <select name="search[education]"  id="education">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="JUNIOR_HIGH"><?php echo $lang["Junior high school"];?></option>
                        <option value="SENIOR_HIGH"><?php echo $lang["Senior middle school"];?></option>
                        <option value="TECHNICAL_SECONDARY"><?php echo $lang["Secondary"];?></option>
                        <option value="COLLEGE"><?php echo $lang["College"];?></option>
                        <option value="BACHELOR_DEGREE"><?php echo $lang["Undergraduate course"];?></option>
                        <option value="MASTER"><?php echo $lang["Master"];?></option>
                        <option value="DOCTOR"><?php echo $lang["Doctor"];?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Work years"];?></label>
                <div class="controls">
                    <select name="search[workyears]"  id="workyears">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="0"><?php echo $lang["Graduates"];?></option>
                        <option value="1"><?php echo $lang["More than one year"];?></option>
                        <option value="2"><?php echo $lang["More than two years"];?></option>
                        <option value="3"><?php echo $lang["More than three years"];?></option>
                        <option value="5"><?php echo $lang["More than five years"];?></option>
                        <option value="10"><?php echo $lang["More than a decade"];?></option>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>
<!-- 导入简历 -->
<div id="d_recruit_import" style="width: 400px; display:none;">
    <form id="recruit_import_form" enctype="multipart/form-data" action="<?php echo $this->createUrl("resume/add", array("op" => "analysis"));?>" method="post">
        <div class="form-horizontal form-compact">
			<div>
				
			</div>
			<div>
				<div>
					<label class="radio radio-inline checked">
						<span class="icon"></span>
						<span class="icon-to-fade"></span>
						<input type="radio" name="importType" value="1" checked="checked"><?php echo $lang["Upload file import"];?>
					</label>
				</div>
				<div>
					<input type="file" name="importFile" style="line-height: 20px; margin-top: 5px; margin-left: 20px;" />
				</div>
				<div>
					<p style="margin-left: 20px;"><?php echo $lang["Import files currently only supports TXT format"];?></p>
				</div>
			</div>
			<div>
				<hr style="color:#F5F7F8; margin-left: -25px; margin-right: -25px;" />
			</div>
			<div>
				<div>
					<label class="radio radio-inline checked">
						<span class="icon"></span>
						<span class="icon-to-fade"></span>
						<input type="radio" name="importType" value="2"><?php echo $lang["Paste your resume import content"];?>
					</label>
				</div>
			</div>
            <div style="padding-left: 20px;">
				<textarea name="importContent" id="importContent" rows="4" cols="20" placeholder="<?php echo $lang["Please paste your resume content"];?>" ></textarea>
			</div>
        </div>
    </form>
</div>
<script>
    var PAGE_PARAM = {
        RESUME_DELETE_URL: "<?php echo $this->createUrl("resume/del");?>",
        RESUME_MARK_URL:   "<?php echo $this->createUrl("resume/edit", array("op" => "mark"));?>",
        SEND_MAIL_URL:     "<?php echo $this->createUrl("resume/sendEmail");?>",
		RESUME_STATUS_URL: "<?php echo $this->createUrl("resume/edit", array("op" => "status"));?>"
    };
</script>
<script src='<?php echo $assetUrl;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/recruit.js?<?php echo VERHASH;?>'></script>
<script>
    $(function() {
		// 导入简历
		$('#imp_rsm').on("click", function(){
			Ui.dialog({
				title: "导入简历",
				content: Dom.byId("d_recruit_import"),
				ok: function(){
					$('#recruit_import_form').submit();
				},
				okVal: "导入",
				cancel: true
			});
		});
		// 粘贴栏获得焦点时改变方法二（粘贴简历）选中状态
		$('#importContent').on('focus', function(){
			$("[name='importType'][value='2']").label("check");
		});
        
        //高级搜索
        Ibos.search.init();
        Ibos.search.setAdvanceSubmit(function($form){
            $form.submit();
        });
		
		// 岗位选择 		
		var posData = Ibos.data.get("position");
		$("#user_position").userSelect({
			type: "position",
			maximumSelectionSize: "1",
			data: posData
		});
    });
</script>
<!-- load script end -->