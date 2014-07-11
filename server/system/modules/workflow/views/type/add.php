<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl;?>/css/workflow.css?<?php echo VERHASH;?>">
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->widget("IWWfListSidebar", array("category" => $category, "catId" => $catId), true);?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="fill">
			<form action="<?php echo $this->createUrl("type/add");?>" id="type_form" method="post" class="form-horizontal">
				<fieldset>
					<legend><?php echo $lang["New flow"];?></legend>
					<!-- 基本信息 -->
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Flow name"];?><span class="xcr">*</span></label>
						<div class="controls">
							<input type="text" name="name" id="flow_name" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Flow category"];?></label>
						<div class="controls">
							<select name="catid" id="flow_catid" class="span3">
								<?php if (!empty($this->category)) : ?>
									<?php foreach ($this->category as $category ) : ?>
										<option <?php if ($category["catid"] == $this->catid) echo 'selected';?> value="<?php echo $category["catid"];?>"><?php echo $category["name"];?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Flow sort"];?></label>
						<div class="controls">
							<input type="text" value="0" name="sort" class="span3">
							<span class="tcm mls">(<?php echo $lang["Flow sort desc"];?>)</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Select form"];?></label>
						<div class="controls">
							<div class="row" id="existed_form_box">
								<div class="span5">
									<input type="hidden" name="formid" id="form_select">
								</div>
								<div class="span7">
									<button type="button" id="form_preview_btn" class="btn" ><?php echo $lang["Review"];?></button>
									<span><?php echo $lang["Could not find the form do you want?"];?><a href="javascript:;" class="anchor" data-toggle="display" data-toggle-show="#new_form_box" data-toggle-hide="#existed_form_box"><?php echo $lang["Add one"];?></a></span>
								</div>
							</div>
							<div id="new_form_box" style="display: none;">
								<input type="text" class="span7" name="formname" placeholder="<?php echo $lang["Enter form name"];?>">
								<a href="javascript:;"  class="anchor mls" data-toggle="display" data-toggle-show="#existed_form_box" data-toggle-hide="#new_form_box"><?php echo $lang["Choose an existing form"];?></a>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Subordinate departments"];?></label>
						<div class="controls">
							<div class="row">
								<div class="span5">
									<input type="text" name="deptid" id="type_setting_department" />
								</div>
								<span class="tcm mls">(<?php echo $lang["Belongs dept desc"];?>)</span>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Flow type"];?></label>
						<div class="controls" id="flow_type">
							<label class="radio radio-inline"><input type="radio" name="type" value="1" checked><?php echo $lang["Fixed flow"];?></label>
							<label class="radio radio-inline"><input type="radio" name="type" value="2"><?php echo $lang["Free flow"];?></label>
						</div>
					</div>
					<div class="control-group" id="free_set" style="display: none;">
						<label class="control-label"><?php echo $lang["Allow default step"];?></label>
						<div class="controls">
							<label class="radio radio-inline"><input type="radio" name="freepreset" value="1" checked><?php echo $lang["Yes"];?></label>
							<label class="radio radio-inline"><input type="radio" name="freepreset" value="0"><?php echo $lang["No"];?></label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Delegate type"];?>&nbsp;<i id="delegate_type" class="glyphicon-info-sign"></i></label>
						<div class="controls">
							<div class="row">
								<div class="span5">
									<select name="freeother" id="">
										<option value="2" selected><?php echo $lang["Free to entrust"];?></option>
										<option value="3"><?php echo $lang["Entrust by step"];?></option>
										<option value="1"><?php echo $lang["Entrust by current step agent"];?></option>
										<option value="0"><?php echo $lang["Prohibit to entrust"];?></option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Associated Settings"];?></label>
						<div class="controls">
							<label class="checkbox checkbox-inline"><input type="checkbox" name="allowattachment" value="1" checked /><?php echo $lang["Allow attachment"];?></label>
							<label class="checkbox checkbox-inline"><input type="checkbox" name="allowversion" value="1" /><?php echo $lang["Enable version control"];?></label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Using state"];?>&nbsp;<i id="using_state" class="glyphicon-info-sign"></i></label>
						<div class="controls">
							<label class="radio radio-inline"><input type="radio" value="1" name="usestatus" checked><?php echo $lang["Visible"];?></label>
							<label class="radio radio-inline"><input type="radio" value="2" name="usestatus"><?php echo $lang["Invisible"];?></label>
							<label class="radio radio-inline"><input type="radio" value="3" name="usestatus"><?php echo $lang["Lock"];?></label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Flow desc"];?></label>
						<div class="controls">
							<textarea name="desc" rows="5"></textarea>
						</div>
					</div>
					<!-- 工作名称/文号的设定 -->
					<div class="mb">
						<h4 class="dib"><?php echo $lang["Work run setting"];?></h4>
						<a href="javascript:;" class="mls" id="ref_ctrl"><?php echo $lang["Detail"];?></a>
					</div>
					<div id="ref_detail" style="display: none;">
						<div class="control-group">
							<label class="control-label"><?php echo $lang["Auto num exp"];?></label>
							<div class="controls">
								<input type="text" name="autoname" class="span8">
								<a href="javascript:;" class="mls" id="ref_eps"><?php echo $lang["Illustrations"];?></a>
							</div>
						</div>
						<div class="control-group" id="ref_eps_ins" style="display: none;">
							<label class="control-label"><?php echo $lang["Auto num desc"];?></label>
							<div class="controls">
								<blockquote>
									<?php echo $lang["Auto num detail"];?>
								</blockquote>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang["Auto num counter"];?><span class="xcr">*</span></label>
							<div class="controls">
								<input type="text" class="span4" name="autonum" value="0">
								<p class="help-block"><?php echo $lang["Auto num counter desc"];?></p>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang["Auto num length"];?><span class="xcr">*</span></label>
							<div class="controls">
								<input type="text" class="span4" name="autolen" value="0">
								<p class="help-block"><?php echo $lang["Auto num length desc"];?></p>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo $lang["Auto edit title"];?></label>
							<div class="controls">
								<select name="autoedit" id="auto_edit" class="span6">
									<option value="1"><?php echo $lang["Auto edit option 1"];?></option>
									<option value="0" selected><?php echo $lang["Auto edit option 0"];?></option>
									<option value="2"><?php echo $lang["Auto edit option 2"];?></option>
									<option value="3"><?php echo $lang["Auto edit option 3"];?></option>
									<option value="4"><?php echo $lang["Auto edit option 4"];?></option>
								</select>
								<label class="checkbox checkbox-inline" id="is_force" style="display: none;"><input id="force_pre_set" name="forcepreset" type="checkbox" value="1" /><?php echo $lang["Force input"];?></label>
							</div>
						</div>
					</div>
					<div>
						<button type="button" onclick="javascript:history.go(-1);" class="btn btn-large btn-submit"><?php echo $lang["Return"];?></button>
						<input name="typeSubmit" type="submit" class="btn btn-large pull-right btn-submit btn-primary" value="<?php echo $lang["Save"];?>">
						<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script>
	Ibos.app.setPageParam("formData", $.parseJSON('<?php echo addslashes(json_encode($formList));?>'));
</script>
<script src='<?php echo STATICURL;?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH;?>'></script>
<script src='<?php echo $assetUrl;?>/js/wftypeedit.js?<?php echo VERHASH;?>'></script>