<form action="<?php echo $this->createUrl("dashboard/param");?>" class="form-horizontal" method="post">
	<div class="ct">
		<div class="clearfix">
			<h1 class="mt"><?php echo $lang["Workflow"];?></h1>
			<ul class="mn">
				<li>
					<span><?php echo $lang["Param setup"];?></span>
				</li>
				<li>
					<a href="<?php echo $this->createUrl("dashboard/category");?>"><?php echo $lang["Category setup"];?></a>
				</li>
			</ul>
		</div>
		<div>
			<!-- 微博设置 -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang["Param setup"];?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Sources of electronic signature"];?></label>
						<div class="controls">
							<label class="radio"><input type="radio" name="seal_from" value="1" <?php if ( $param["sealfrom"] == "1" ): ?> checked <?php endif; ?> /><?php echo $lang["File"];?></label>
							<label class="radio"><input type="radio" name="seal_from" value="2" <?php if ( $param["sealfrom"] == "2" ): ?> checked <?php endif; ?> /><?php echo $lang["Database"];?></label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang["Workflow remind overtime"];?></label>
						<div class="controls">
							<div><?php echo $lang["Before timeout"];?>&nbsp;&nbsp;<input name="work_remind_before" value="<?php echo $param["wfremindbeforedesc"];?>" style="width: 60px;" maxlength="2" type="text"> &nbsp;
								<select name="unit_before" style="width: 100px;">
									<option value="m" <?php if ( $param["wfremindbeforeunit"] == "m" ): ?> selected <?php endif; ?> ><?php echo $lang["Minutes"];?></option>
									<option value="h" <?php if ( $param["wfremindbeforeunit"] == "h" ): ?> selected <?php endif; ?> ><?php echo $lang["Hours"];?></option>
									<option value="d" <?php if ( $param["wfremindbeforeunit"] == "d" ): ?> selected <?php endif; ?> ><?php echo $lang["Day"];?></option>
									<option value="%" <?php if ( $param["wfremindbeforeunit"] == "%" ): ?> selected <?php endif; ?> >%</option>
								</select> &nbsp;
								<?php echo $lang["To remind"];?>
							</div><br/>
							<div><?php echo $lang["After timeout"];?>&nbsp;&nbsp;<input name="work_remind_after" value="<?php echo $param["wfremindafterdesc"];?>" style="width: 60px;" maxlength="2" type="text"> &nbsp;
								<select name="unit_after" style="width: 100px;">
									<option value="m" <?php if ( $param["wfremindafterunit"] == "m" ): ?> selected <?php endif; ?> ><?php echo $lang["Minutes"];?></option>
									<option value="h" <?php if ( $param["wfremindafterunit"] == "h" ): ?> selected <?php endif; ?> ><?php echo $lang["Hours"];?></option>
									<option value="d" <?php if ( $param["wfremindafterunit"] == "d" ): ?> selected <?php endif; ?> ><?php echo $lang["Day"];?></option>
									<option value="%" <?php if ( $param["wfremindafterunit"] == "%" ): ?> selected <?php endif; ?> >%</option>
								</select> &nbsp;
								<?php echo $lang["End remind"];?>
							</div>
							<br/>
							<p class="help-block"><?php echo $lang["Workflow remind tips"];?></p>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"></label>
						<div class="controls">
							<button type="submit" class="btn btn-primary btn-large btn-submit"> <?php echo $lang["Submit"];?> </button>
						</div>
					</div>
					<input type="hidden" name="formhash" value="<?php echo FORMHASH;?>" />
				</div>
			</div>
		</div>
	</div>
</form>
