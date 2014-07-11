<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/recruit.css?<?php echo VERHASH;?>">
<link rel="stylesheet" href="<?php echo STATICURL;?>/js/lib/autoComplete/jquery.autocomplete.css?<?php echo VERHASH;?>">
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
            <div class="page-list">
                <div class="page-list-header">
                    <div class="btn-toolbar pull-left">
                        <button class="btn btn-primary pull-left" data-click="addContact"><?php echo $lang["Add"];?></button>
                        <div class="btn-group" id="art_more" style="display:block;">
                            <button class="btn dropdown-toggle" data-toggle="dropdown">
                                <?php echo $lang["More operation"];?>
                                <i class="caret"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:;" data-click="deleteContacts">
                                        <?php echo $lang["Delete"];?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-click="exportContact">
           	                            <?php echo $lang["Export"];?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <form  action="<?php echo $this->createUrl("contact/search");?>" method="post">
                        <div class="search search-config pull-right span3">
                            <input type="text" placeholder="Search"  id="mn_search" name="keyword" nofocus>
                            <a href="javascript:;">search</a>
                            <input type="hidden" name="type" value="normal_search">
                        </div>
                    </form>
                </div>
                <div class="page-list-mainer">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox">
                                        <input type="checkbox" name="" data-name="contact[]" id="all_select">
                                    </label>
                                </th>
                                <th width="70"><?php echo $lang["Name"];?></th>
                                <th width="100"><?php echo $lang["Contact date"];?></th>
                                <th width="70"><?php echo $lang["Contact staff"];?></th>
                                <th width="70"><?php echo $lang["Contact method"];?></th>
                                <th width="70"><?php echo $lang["Contact purpose"];?></th>
                                <th width="100"><?php echo $lang["More operation"];?></th>
                            </tr>
                        </thead>
                        <tbody id="contact_tbody">
                            <?php foreach ($resumeContactList as $resumeContact ) : ?>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" name="contact[]" value="<?php echo $resumeContact["contactid"];?>">
                                        </label>
                                    </td>
                                    <td>
                                        <a href="<?php echo $this->createUrl("resume/show", array("resumeid" => $resumeContact["resumeid"]));?>"><?php echo $resumeContact["realname"];?></a>
                                    </td>
                                    <td>
                                        <?php echo $resumeContact["inputtime"];?>
                                    </td>
                                    <td>
                                        <?php echo $resumeContact["input"];?>
                                    </td>
                                    <td>
                                        <?php echo $resumeContact["contact"];?>
                                    </td>
                                    <td>
                                        <?php echo $resumeContact["purpose"];?>
                                    </td>
                                    <td>
                                        <a href="javascript:" data-click="editContact" data-id="<?php echo $resumeContact["contactid"];?>" title="<?php echo $lang["Modify"];?>" class="cbtn o-edit"></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
					<div class="no-data-tip" <?php if (0 < count($resumeContactList)) echo ' style="display:none" '; ?> id="no_contact_tip"></div>
                </div>
                <div class="page-list-footer">
                    <div class="pull-right">
                        <?php $this->widget("IWPage", array("pages" => $pagination));?>
                    </div>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>
<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl("contact/search");?>" method="post">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Name"];?></label>
                <div class="controls">
                    <input type="text" id="realname" name="search[realname]">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Contact date"];?></label>
                <div class="controls">
                    <select name="search[inputtime]"  id="inputtime">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="7"><?php echo $lang["Within a week"];?></option>
                        <option value="15"><?php echo $lang["Within two weeks"];?></option>
                        <option value="31"><?php echo $lang["within a month"];?></option>
                    </select>
                </div>
            </div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Contact staff"];?></label>
				<div class="controls">
					<input type="text" name="search[input]" data-toggle="userSelect" id="user_contact_search" value="">
				</div>
			</div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Contact method"];?></label>
                <div class="controls">
                    <select name="search[contact]"  id="contact">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="<?php echo $lang["Telephone"];?>"><?php echo $lang["Telephone"];?></option>
                        <option value="<?php echo $lang["Letters"];?>"><?php echo $lang["Letters"];?></option>
                        <option value="<?php echo $lang["Mail"];?>"><?php echo $lang["Mail"];?></option>
                        <option value="<?php echo $lang["Visit"];?>"><?php echo $lang["Visit"];?></option>
                        <option value="<?php echo $lang["Qq"];?>"><?php echo $lang["Qq"];?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang["Contact purpose"];?></label>
                <div class="controls">
                    <select name="search[purpose]"  id="purpose">
                        <option value="-1"><?php echo $lang["Please select"];?></option>
                        <option value="<?php echo $lang["Notification primaries"];?>"><?php echo $lang["Notification primaries"];?></option>
                        <option value="<?php echo $lang["Tracking contact"];?>"><?php echo $lang["Tracking contact"];?></option>
                        <option value="<?php echo $lang["Inform the interview"];?>"><?php echo $lang["Inform the interview"];?></option>
                        <option value="<?php echo $lang["Background investigation"];?>"><?php echo $lang["Background investigation"];?></option>
                        <option value="<?php echo $lang["Notification of results"];?>"><?php echo $lang["Notification of results"];?></option>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>
<!-- 添加/修改联系记录 -->
<div id="contact_dialog" style="width: 500px; display:none;">
    <form id="contact_dialog_form" method="get">
		<div class="form-horizontal form-compact">
			<div class="control-group" id="r_fullname">
				<label class="control-label"><?php echo $lang["Full name"];?></label>
				<div class="controls span6">
					<input type="text" name="fullname" id="fullname">
					<input type="hidden" name="check-fullname" id="check_fullname" value="1">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Contact time"];?></label>
				<div class="controls span6">
                    <div class="datepicker" id="contact_time">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="inputtime" value="<?php echo date("Y-m-d", time());?>">
                    </div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Contact staff"];?></label>
				<div class="controls span6">
					<input type="text" name="upuid" class='' data-toggle="userSelect" id="user_contact" value="">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Contact method"];?></label>
				<div class="controls span6">
					<select name="contact"  id="contact">
						<option value="<?php echo $lang["Telephone"];?>"><?php echo $lang["Telephone"];?></option>
						<option value="<?php echo $lang["Letters"];?>"><?php echo $lang["Letters"];?></option>
						<option value="<?php echo $lang["Mail"];?>"><?php echo $lang["Mail"];?></option>
						<option value="<?php echo $lang["Visit"];?>"><?php echo $lang["Visit"];?></option>
						<option value="<?php echo $lang["QQ"];?>"><?php echo $lang["QQ"];?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Purpose"];?></label>
				<div class="controls span6">
					<select name="purpose"  id="purpose">
						<option value="<?php echo $lang["Notification primaries"];?>"><?php echo $lang["Notification primaries"];?></option>
						<option value="<?php echo $lang["Tracking contact"];?>"><?php echo $lang["Tracking contact"];?></option>
						<option value="<?php echo $lang["Inform the interview"];?>"><?php echo $lang["Inform the interview"];?></option>
						<option value="<?php echo $lang["Background investigation"];?>"><?php echo $lang["Background investigation"];?></option>
						<option value="<?php echo $lang["Notification of results"];?>"><?php echo $lang["Notification of results"];?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang["Contact content"];?></label>
				<div class="controls">
					<textarea name="detail" id="detail" rows="4" cols="20"></textarea>
				</div>
			</div>
		</div>
        <input type="hidden" name="contactid" id="contactid" />
    </form>
</div>

<!-- 插入联系信息模板 -->
<script type="text/ibos-template" id="contact_template">
<tr>
    <td>
        <label class="checkbox">
           <input type="checkbox" value="<%=contactid%>" name="contact[]">
        </label>
    </td>
    <td>
        <%=fullname%>
    </td>
    <td>
        <%=inputtime%>
    </td>
    <td>
        <%=input%>
    </td>
    <td>
        <%=contact%>
    </td>
    <td>
        <%=purpose%>
    </td>
    <td>
        <a href="javascript:;" data-click="editContact" data-id="<%=contactid%>" title="<?php echo $lang["Update"];?>" class="cbtn o-edit"></a>
    </td>
</tr>
</script>
<script>
    var PAGE_PARAM = {
        CONTACT_DELETE_URL: "<?php echo $this->createUrl("contact/del");?>",
        CONTACT_EDIT_URL:   "<?php echo $this->createUrl("contact/edit", array("op" => "getEditData"));?>",
        CONTACT_UPDATE_URL: "<?php echo $this->createUrl("contact/edit", array("op" => "update"));?>",
        CONTACT_SAVE_URL:   "<?php echo $this->createUrl("contact/add");?>",
		CONTACT_EXPORT_URL: "<?php echo $this->createUrl("contact/export");?>"
    }
</script>
<script src='<?php echo STATICURL;?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/lang/zh-cn.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/recruit.js?<?php echo VERHASH;?>'></script>
<script>
    var resumes = <?php echo $resumes;?>;
    $(function() {
        //输入简历姓名自动完成
//        $('#fullname').autocomplete(resumes, {
//            max: 10,    //列表里的条目数
//            minChars: 0,    //自动完成激活之前填入的最小字符
//            width: 398,     //提示的宽度，溢出隐藏
//            scrollHeight: 300,   //提示的高度，溢出显示滚动条
//            matchContains: true,    //包含匹配，就是data参数里的数据，是否只要包含文本框里的数据就显示
//            autoFill: true,    //自动填充
//            formatItem: function(row, i, max) {
//                var sex;
//                if ( row.gender === 1 ) {
//                    sex = "";
//                } else if ( row.gender === 2 ) {
//                    sex = "";
//                } else {
//                    sex = "";
//                }
//                return row.realname + '  ' + sex + "  [ ":" + row.targetposition + ' ]';
//            },
//             formatMatch: function(row, i, max) {
//                return row.realname;
//            },
//            formatResult: function(row) {
//                return row.realname;
//            }
//        }).result(function(event, row, formatted) {
//            $('#resumeid').val(row.resumeid);
//        });
             
        //验证用户名是否存在于简历表
        $.formValidator.initConfig({ formID:"form_add_contact", errorFocus:true});
		$("#fullname").formValidator()
			.inputValidator({
				min:1,
				onError: U.lang("REC.PLEASE_FILL_IN_THE_NAME_OF_EXISTING_RESUME")
			})
		.ajaxValidator({
			type : 'get',
			datatype : "json",
			async : true,
			url : Ibos.app.url('recruit/contact/checkRealname'),
			success : function(res){
				var isExist = $.parseJSON(res);
				$('#check_fullname').val(!!isExist.statu ? '1': '0');
                return !!isExist.statu
			},
			buttons: $(".btn btn-primary"),
			onError : U.lang("REC.THIS_NAME_DOSNOT_EXIT_RESUME")
		});
        

        //高级搜索
        $("#mn_search").search(null, function(){
            Ui.dialog({
                id: "d_advance_search",
                title: U.lang("ADVANCED_SETTING"),
                content: document.getElementById("mn_search_advance"),
                cancel: true,
                init: function(){
                    var form = this.DOM.content.find("form")[0];
                    form && form.reset();
                    // 初始化日期选择
                    $("#date_start").datepicker({ target: $("#date_end") });
                },
                ok: function(){
                    this.DOM.content.find("form").submit();
                },
            })
        })
		
		// 联系人选择框
		$("#user_contact, #user_contact_search").userSelect({
			type: "user",
			maximumSelectionSize: "1",
			data: Ibos.data.get("user")
		});

		
    });
    
</script>
<!-- load script end -->