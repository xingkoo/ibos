<div style="width: 800px;">
    <form id="step_edit_form">
        <ul class="nav nav-skid" id="flow_edit_tab">
            <li <?php if ($op == "base") echo ' class="active"'; ?> ><a href="#step_basic_info" data-toggle="tab"><?php echo $lang["Base info"];?></a></li>
            <li <?php if ($op == "field") echp ' class="active"'; ?> ><a href="#step_form_field" data-toggle="tab"><?php echo $lang["Form fields"];?></a></li>
            <li <?php if ($op == "handle") echo ' class="active"'; ?> ><a href="#step_operator" data-toggle="tab"><?php echo $lang["Handle role"];?></a></li>
            <li <?php if ($op == "condition") echo 'class="active"'; ?> ><a href="#step_condition" data-toggle="tab"><?php echo $lang["Condition setup"];?></a></li>
            <li <?php if ($op == "setting") echo 'class="active"'; ?> ><a href="#step_settings" data-toggle="tab"><?php echo $lang["Other setup"];?></a></li>
        </ul>
        <div class="tab-content">
            <!-- 基本信息 -->
            <div class="tab-pane <?php if ($op == "base") : ?>active<?php else : ?>fade<?php endif; ?>" id="step_basic_info" >
                <fieldset class="form-horizontal form-narrow fill">
                    <div class="control-group">
                        <label for="step_index" class="control-label"><?php echo $lang["Serial number"];?></label>
                        <div class="controls span6">
                            <input type="text" id="step_index" name="processid" value="<?php echo $prcs["processid"];?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="node_type" class="control-label"><?php echo $lang["Node type"];?></label>
                        <div class="controls">
                            <select name="type" id="node_type" class="span6">
                                <option value="0" <?php if ($prcs["type"] == "0") echo "selected";?> ><?php echo $lang["Step node"];?></option>
                                <option value="1" <?php if ($prcs["type"] == "1") echo "selected";?> ><?php echo $lang["Child flow node"];?></option>
                            </select>
                        </div>
                    </div>
                    <div id="node_type_step">
                        <div class="control-group">
                            <label for="step_name" class="control-label"><?php echo $lang["Node name"];?></label>
                            <div class="controls span6">
                                <input type="text" id="step_name" name="name" value="<?php echo $prcs["name"];?>" />
                            </div>
                        </div>
                    </div>
                    <div id="node_type_subflow" style="display: none;">
                        <div class="control-group">
                            <label for="subflow_type" class="control-label"><?php echo $lang["Child flow type"];?></label>
                            <div class="controls">
                                <select name="childflow" class="span6" id="subflow_type">
                                    <?php foreach ($flows as $flow ) : ?>
                                        <option value="<?php echo $flow["flowid"];?>" <?php if ($prcs["childflow"] == $flow["flowid"]) echo "selected"; ?> ><?php echo $flow["name"];?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label"><?php echo $lang["Copy form field"];?></label>
                            <div class="controls">
                                <div class="row mb">
                                    <div class="span6">
                                        <label for="subflow_parent_field"><?php echo $lang["Parent flow field"];?></label>
                                        <select id="subflow_parent_field" size="5">
                                            <?php foreach ($parentField as $field ) : ?>
                                                <option value="<?php echo $field;?>"><?php echo $field;?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="span6">
                                        <label for="subflow_child_field"><?php echo $lang["Child flow field"];?></label>
                                        <select id="subflow_child_field" size="5"></select>
                                    </div>
                                </div>
                                <button type="button" class="btn" id="subflow_relative_btn" disabled><?php echo $lang["Establish corresponding relationship"];?></button>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="subflow_field_map" class="control-label"><?php echo $lang["Field mapping"];?></label>
                            <div class="controls controls-content span6">
                                <ul id="field_map_list">
                                    <?php if (!empty($prcs["relationout"])) : ?>
                                        <?php foreach (explode(",", $prcs["relationout"]) as $out ) : ?>
                                            <?php if (!empty($out)) : ?>
                                                <li data-value="<?php echo str_replace("=>", "_", $out);?>"><?php echo $out;?><a href="javascript:;" class="close" data-type="removeMap" data-id="<?php echo str_replace("=>", "_", $out);?>">x</a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                                <input type="hidden" name="map" value="<?php echo $prcs["relationout"];?>" id="field_map_value">
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="subflow_action" class="control-label"><?php echo $lang["End of the action"];?></label>
                            <div class="controls">
                                <label class="radio radio-inline">
                                    <input type="radio" name="over_act" data-act="overAct" value="0" <?php if ($prcs["processto"] == "") echo "checked";?> />
                                    <?php echo $lang["End action 1"];?>
                                </label>
                                <label class="radio radio-inline">
                                    <input type="radio" name="over_act" data-act="overAct" value="1" <?php if ($prcs["processto"] !== "") echo "checked";?> />
                                    <?php echo $lang["End action 2"];?>
                                </label>
                            </div>
                        </div>
                        <div id="subflow_prcs_back" <?php if ($prcs["processto"] == "") echo ' style="display: none;"'; ?> >
                            <div class="control-group">
                                <label for="subflow_return_step" class="control-label"><?php echo $lang["Return step"];?></label>
                                <div class="controls">
                                    <select name="prcsback" id="subflow_return_step" class="span6">
                                        <option value=""><?php echo $lang["Select return step"];?></option>
                                        <?php foreach ($backprcs as $back ) : ?>
                                            <option value="<?php echo $back["processid"];?>" <?php if ($prcs["processto"] == $back["processid"]) echo "selected";?> ><?php echo sprintf("%d、%s", $back["processid"], $back["name"]);?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="subflow_return_sponsor" class="control-label"><?php echo $lang["Return step host"];?></label>
                                <div class="controls span6">
                                    <input type="text" name='backuserop' value="<?php echo $prcs["autouserop"];?>" id="subflow_return_sponsor">
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="subflow_return_handler" class="control-label"><?php echo $lang["Return step agent"];?></label>
                                <div class="controls span6">
                                    <input type="text" name='backuser' value="<?php echo $prcs["autouser"];?>" id="subflow_return_handler">
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            <!-- 表单字段 -->
            <div class="tab-pane <?php if ($op == "field") : ?>active<?php else : ?>fade<?php endif; ?>" id="step_form_field">
                <?php if (!empty($structure)) : ?>
                    <table class="table table-crowd table-striped table-head-condensed mbz">
                        <thead>
                            <tr>
                                <th width="20"></th>
                                <th><?php echo $lang["Field name"];?></th>
                                <th width="70">
                                    <label for="write" data-toggle="tooltip" title="本步骤可写字段" class="db curp">
                                        <input type="checkbox" id="write" />
                                        <?php echo $lang["Can write"];?>
                                    </label>
                                </th>
                                <th width="70">
                                    <label for="secret" data-toggle="tooltip" title="保密字段对于本步骤主办人、经办人均为不可见" class="db curp">
                                        <input type="checkbox" id="secret" />
                                        <?php echo $lang["Secret"];?>
                                    </label>
                                </th>
                                <th width="140">
                                    <label for="check" data-toggle="tooltip" title="决定使用哪个规则对输入进行验证,或者锁定宏控件的值" class="db curp">
                                        <input type="checkbox" id="check" />
                                        <?php echo $lang["Rules"];?>
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
	                            $checkArr = array();?>
	                            foreach ($checkList as $rule ) {
									$checkArr[] = sprintf("<option value="%s">%s</option>", $rule["type"], $rule["desc"]);
								}
							?>
                            <tr>
                                <td width="20"></td>
                                <td>
                                    <span class="xwb fss"><?php echo $lang["Global attach"];?></span>
                                </td>
                                <td>
                                    <label for="write_a" class="db curp">
                                        <input type="checkbox" data-id="a" name="write[]" value="[A@]" id="write_a">
                                    </label>
                                </td>
                                <td>
                                    <label for="secret_a>" class="db curp">
                                        <input type="checkbox" data-id="a" name="secret[]" value="[A@]" id="secret_a">
                                    </label>
                                </td>
                                <td><?php echo $lang["None"];?></td>
                            </tr>
                            <?php foreach ($structure as $item ) : ?>
                                <?php if ($item["data-type"] !== "label") : ?>
                                    <tr>
                                        <td width="20">
                                            <i class="o-ctrl-<?php echo $item["data-type"];?>" title="<?php echo $item["desc"];?>" data-toggle="tooltip"></i>
                                        </td>
                                        <td>
                                            <span class="xwb fss"><?php echo $item["data-title"];?></span>
                                        </td>
                                        <td>
                                            <label for="write_<?php echo $item["itemid"];?>" class="db curp">
                                                <input type="checkbox" data-id="<?php echo $item["itemid"];?>" name="write[]" value="<?php echo $item["data-title"];?>" id="write_<?php echo $item["itemid"];?>">
                                            </label>
                                        </td>
                                        <td>
                                            <label for="secret_<?php echo $item["itemid"];?>" class="db curp">
                                                <input type="checkbox" data-id="<?php echo $item["itemid"];?>" name="secret[]" value="<?php echo $item["data-title"];?>" id="secret_<?php echo $item["itemid"];?>">
                                            </label>
                                        </td>
                                        <td>
                                            <?php if ($item["data-type"] == "auto") : ?>
                                            	<label for="micro_<?php echo $item["itemid"];?>" title="<?php echo $lang["Lock desc"];?>" data-toggle="tooltip" class="db curp">
                                                    <input type="checkbox" <?php echo $item["itemid"];?> data-id="<?php echo $item["itemid"];?>" value="<?php echo $item["data-title"];?>" name="micro[]" id="micro_<?php echo $item["itemid"];?>" disabled />
                                                    <span class="fss"><?php echo $lang["Lock"];?></span>
                                                </label>
                                            <?php else : ?>
                                                <label for="regex_<?php echo $item["itemid"];?>">
                                                    <input type="checkbox" <?php echo $item["itemid"];?> data-id="<?php echo $item["itemid"];?>" value="<?php echo $item["data-title"];?>" name="check[]" disabled id="regex_<?php echo $item["itemid"];?>" />
                                                </label>
                                                <select name="check_select[]" id="regexList_<?php echo $item["itemid"];?>" style="width: 100px; display: none;" class="input-small" disabled>
                                                    <?php echo implode("\n", $checkArr);?>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <!-- 经办角色 -->
            <div class="tab-pane <?php if ($op == "handle") : ?>active<?php else : ?>fade<?php endif; ?>" id="step_operator">
                <fieldset class="form-horizontal form-narrow fill">
                    <div class="control-group">
                        <label for="operator_allow" class="control-label"><?php echo $lang["Orgnaization of permissions"];?></label>
                        <div class="controls span6"><input type="text" name="prcsuser" value="<?php echo $prcs["prcsuser"];?>" id="operator_allow"></div>
                    </div>
                    <div class="control-group">
                        <label for="operator_filters" class="control-label"><?php echo $lang["The candidates filtering rules"];?></label>
                        <div class="controls">
                            <select name="userfilter" id="operator_filters" class="span6">
                                <option value="" <?php if (empty($prcs["userfilter"])) echo "selected"; ?> ><?php echo $lang["Filtering rules 1"];?></option>
                                <option value="1" <?php if ($prcs["userfilter"] == "1") echo "selected"; ?> ><?php echo $lang["Filtering rules 2"];?></option>
                                <option value="3" <?php if ($prcs["userfilter"] == "3") echo "selected"; ?> ><?php echo $lang["Filtering rules 3"];?></option>
                                <option value="4" <?php if ($prcs["userfilter"] == "4") echo "selected"; ?> ><?php echo $lang["Filtering rules 4"];?></option>
                                <option value="2" <?php if ($prcs["userfilter"] == "2") echo "selected"; ?> ><?php echo $lang["Filtering rules 5"];?></option>
                            </select>
                            <span class="help-inline fss"><?php echo $lang["Filtering rules desc"];?></span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="operator_automatic" class="control-label"><?php echo $lang["Automatic candidate rules"];?></label>
                        <div class="controls">
                            <select name="autotype" id="operator_automatic" class="span6">
                                <option value="" <?php if (empty($prcs["autotype"])) echo "selected";?> ><?php echo $lang["Automatic rules 1"];?></option>
                                <option value="1" <?php if ($prcs["autotype"] == "1") echo "selected";?> ><?php echo $lang["Automatic rules 2"];?></option>
                                <option value="2" <?php if ($prcs["autotype"] == "2") echo "selected";?> ><?php echo $lang["Automatic rules 3"];?></option>
                                <option value="4" <?php if ($prcs["autotype"] == "4") echo "selected";?> ><?php echo $lang["Automatic rules 4"];?></option>
                                <option value="6" <?php if ($prcs["autotype"] == "6") echo "selected";?> ><?php echo $lang["Automatic rules 5"];?></option>
                                <option value="5" <?php if ($prcs["autotype"] == "5") echo "selected";?> ><?php echo $lang["Automatic rules 6"];?></option>
                                <option value="3" <?php if ($prcs["autotype"] == "3") echo "selected";?> ><?php echo $lang["Automatic rules 7"];?></option>
                                <option value="7" <?php if ($prcs["autotype"] == "7") echo "selected";?> ><?php echo $lang["Automatic rules 8"];?></option>
                                <option value="8" <?php if ($prcs["autotype"] == "8") echo "selected";?> ><?php echo $lang["Automatic rules 9"];?></option>
                                <option value="9" <?php if ($prcs["autotype"] == "9") echo "selected";?> ><?php echo $lang["Automatic rules 10"];?></option>
                                <option value="10" <?php if ($prcs["autotype"] == "10") echo "selected";?> ><?php echo $lang["Automatic rules 11"];?></option>
                            </select>
                            <span class="help-inline fss"><?php echo $lang["Automatic rules desc"];?></span>
                        </div>
                    </div>
                    <div>
                        <!-- 自动选择本部门主管 | 自动选择上级主管领导 | 自动选择上级分管领导 -->
                        <div class="control-group" id="dept_for" style="display: none;">
                            <label for="dept_for_select" class="control-label"><?php echo $lang["Department for object"];?></label>
                            <div class="controls">
                                <select name="autobaseuser" id="operator_for_select" class="span6">
                                    <option value="0"><?php echo $lang["Current step"];?></option>
                                    <?php foreach ($autoprcs as $id => $name ) : ?>
                                       <option value="<?php echo $id;?>" <?php if ($prcs["autobaseuser"] == $id) echo "selected";?> ><?php echo sprintf("%d、%s", $id, $name);?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-inline fss"><?php echo $lang["Department for desc"];?></span>
                            </div>
                        </div>

                        <!-- 按表单字段选择 -->
                        <div class="control-group" id="form_field" style="display: none;">
                            <label for="form_field_select" class="control-label"><?php echo $lang["Form fields"];?></label>
                            <div class="controls">
                                <select name="itemid" id="form_field_select" class="span6"></select>
                                <span class="help-inline fss"><?php echo $lang["Form field desc"];?></span>
                            </div>
                        </div>

                        <!-- 自动选择指定步骤主办人 -->
                        <div class="control-group" id="assign_step" style="display: none;">
                            <label for="assign_step_select" class="control-label"><?php echo $lang["Step"];?></label>
                            <div class="controls">
                                <select name="autoprcsuser" id="assign_step_select" class="span6">
                                    <?php foreach ($autoprcs as $id => $name ) : ?>
                                        <option value="<?php echo $id;?>" <?php if (($prcs["autotype"] == "8") && ($prcs["autouser"] == $id)) echo "selected";?> ><?php echo sprintf("%d、%s", $id, $name);?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-inline fss"><?php echo $lang["Auto prcs user desc"];?></span>
                            </div>
                        </div>

                        <!-- 指定自动选择默认人员 -->
                        <div id="assign_user" <?php if ($prcs["autotype"] !== "3") echo 'style="display: none;"';?> >
                            <div class="control-group span6">
                                <label for="assign_sponsor" class="control-label"><?php echo $lang["Host"];?></label>
                                <div class="controls">
                                    <input type="text" name='autouserop' id="assign_sponsor" value="<?php echo $prcs["autouserop"];?>" />
                                    <div id="assign_sponsor_box"></div>
                                </div>
                            </div>
                            <div class="control-group span6">
                                <label for="assign_handler" class="control-label"><?php echo $lang["Agent"];?></label>
                                <div class="controls">
                                    <input type="text" name='autouser' id="assign_handler" value="<?php echo $prcs["autouser"];?>" />
                                    <div id="assign_handler_box"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
            <!-- 条件设置 -->
            <div class="tab-pane <?php if ($op == "condition") : ?>active<?php else : ?>fade<?php endif; ?>" id="step_condition">
                <?php if (isset($con)) : ?>
                    <table class="table table-crowd table-head-condensed mbz">
                        <thead>
                            <tr>
                                <th width="100"><?php echo $lang["Roll out steps"];?></th>
                                <th><?php echo $lang["Transfer conditions set"];?></th>
                            </tr>
                        </thead>
                        <?php foreach ($con as $id => $condition ) : ?>
                            <tbody style="border-top: 1px solid #D8D8D8;">
                                <tr>
                                    <td style='text-align: center;padding-left: 0;'>
                                        <span class="label"><?php echo $id;?></span><br/><br/><?php echo $condition["name"];?>                                 </td>
                                    <td>
                                        <div id="step_condition_select_<?php echo $id;?>" class="mbs">
                                            <div class="row mbs">
                                                <div class="span3">
                                                    <select class="input-small" id="condition_field_<?php echo $id;?>"><?php echo implode("\n", $conItem);?></select>
                                                </div>
                                                <div class="span2">
                                                    <select class="input-small" id="condition_operator_<?php echo $id;?>">
                                                        <option value="="><?php echo $lang["Equal"];?></option>
                                                        <option value="<>"><?php echo $lang["Not equal to"];?></option>
                                                        <option value=">"><?php echo $lang["Greater than"];?></option>
                                                        <option value="<"><?php echo $lang["Less than"];?></option>
                                                        <option value=">="><?php echo $lang["Greater than or equal to"];?></option>
                                                        <option value="<="><?php echo $lang["Less than or equal to"];?></option>
                                                        <option value="include"><?php echo $lang["Contains"];?></option>
                                                        <option value="exclude"><?php echo $lang["Not contain"];?></option>
                                                    </select>
                                                </div>
                                                <div class="span2">
                                                    <input type="text" class="input-small" id="condition_value_<?php echo $id;?>">
                                                </div>
                                                <div class="span5">
                                                    <div class="pull-right">
                                                        <button type="button" class="btn btn-small btn-primary" data-id="<?php echo $id;?>" data-condition="addCondition"><?php echo $lang["Add condition"];?></button>
                                                        <button type="button" class="btn btn-small btn-fix" data-id="<?php echo $id;?>" data-condition="removeCondition">
															<i class="glyphicon-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="btn-group" id="condition_logic_<?php echo $id;?>" data-toggle="buttons-radio">
                                                        <button type="button" class="btn btn-small btn-fix active" data-value="AND"><?php echo $lang["With"];?></button>
                                                        <button type="button" class="btn btn-small btn-fix" data-value="OR"><?php echo $lang["Or"];?></button>
                                                    </div>
                                                    <input type="hidden" id="condition_logic_input_<?php echo $id;?>">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-small btn-fix" data-id="<?php echo $id;?>" data-condition="addLeftParenthesis">(</button>
                                                        <button type="button" class="btn btn-small btn-fix" data-id="<?php echo $id;?>" data-condition="addRightParenthesis">)</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <select id="condition_select_<?php echo $id;?>" data-select='condition' data-id='<?php echo $id;?>' multiple>
                                                    <?php if (!empty($condition["options"])) : ?>
                                                        <?php foreach ($condition["options"] as $conOpt ) : ?>
                                                            <option><?php echo $conOpt;?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <input type="hidden" name="conresult[<?php echo $id;?>]" id="condition_result_<?php echo $id;?>" <?php if (!empty($condition["options"])) : ?>value="<?php echo implode("", $condition["options"]);?>"<?php endif; ?> >
                                            </div>
                                        </div>
                                        <input type="text" value="<?php echo $condition["desc"];?>" name="condesc[<?php echo $id;?>]" class="input-small" placeholder="<?php echo $lang["Not suitable for condition tips"];?>" id="mismatch_condition_tip_<?php echo $id;?>">
                                    </td>
                                </tr>
                            </tbody>
                        <?php endforeach; ?>
                    </table>
                <?php else : ?>
                    <?php echo $lang["Not set conditions for next step"];?>
                <?php endif; ?>
            </div>
            <!-- 其他设置 -->
            <div class="tab-pane fade" id="step_settings" >
                <fieldset  class="form-horizontal form-narrow fill">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Host option"];?></label>
                        <div class="controls">
                            <select name="topdefault" class="span6">
                                <option value="0" <?php if ($prcs["topdefault"] == "0") echo "selected";?> ><?php echo $lang["Host option 1"];?></option>
                                <option value="2" <?php if ($prcs["topdefault"] == "2") echo "selected";?> ><?php echo $lang["Host option 2"];?></option>
                                <option value="1" <?php if ($prcs["topdefault"] == "1") echo "selected";?> ><?php echo $lang["Host option 3"];?></option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label id="allowed_change_label" for="allowed_change" class="control-label"><?php echo $lang["Allowed modify host related options"];?></label>
                        <div class="controls">
                            <label></label>
                            <input type="checkbox" name="userlock" id="allowed_change" value="1" <?php if ($prcs["userlock"] == "1") echo "checked";?> >
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Sign option"];?></label>
                        <div class="controls">
                            <select name="feedback" class="span4" id="sign_options">
                                <option value="0" <?php if ($prcs["feedback"] == "0") echo "selected";?> ><?php echo $lang["Sign option 1"];?></option>
                                <option value="1" <?php if ($prcs["feedback"] == "1") echo "selected";?> ><?php echo $lang["Sign option 2"];?></option>
                                <option value="2" <?php if ($prcs["feedback"] == "2") echo "selected";?> ><?php echo $lang["Sign option 3"];?></option>
                            </select>
                            <span class="help-inline"><?php echo $lang["Sign option desc"];?></span>
                        </div>
                    </div>
                    <div class="control-group" id="sign_visibility">
                        <label class="control-label"><?php echo $lang["Sign opinion"];?></label>
                        <div class="controls">
                            <select name="signlook" class="span6">
                                <option value="0" <?php if ($prcs["signlook"] == "0") echo "selected";?> ><?php echo $lang["Sign opinion 1"];?></option>
                                <option value="1" <?php if ($prcs["signlook"] == "1") echo "selected";?> ><?php echo $lang["Sign opinion 2"];?></option>
                                <option value="2" <?php if ($prcs["signlook"] == "2") echo "selected";?> ><?php echo $lang["Sign opinion 3"];?></option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Forced to"];?></label>
                        <div class="controls">
                            <input type="checkbox" name="turnpriv" value="1" <?php if ($prcs["turnpriv"] == "1") echo "checked";?> id="force_forward" />
                            <span class="help-inline"><?php echo $lang["Forced to desc"];?></span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Fallback options"];?></label>
                        <div class="controls">
                            <label class="radio radio-inline">
                                <input type="radio" value="0" name="allowback" <?php if ($prcs["allowback"] == "0") echo "checked";?> />
                                <?php echo $lang["Not allow"];?>
                            </label>
                            <label class="radio radio-inline">
                                <input type="radio" value="1" name="allowback" <?php if ($prcs["allowback"] == "1") echo "checked";?> />
                                <?php echo $lang["Return to prev step"];?>
                            </label>
                            <label class="radio radio-inline">
                                <input type="radio" value="2" name="allowback" <?php if ($prcs["allowback"] == "2") echo "checked";?> />
                                <?php echo $lang["Return all step"];?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Concurrent options"];?></label>
                        <div class="controls">
                            <label class="radio radio-inline">
                                <input type="radio" value="0" name="syncdeal" <?php if ($prcs["syncdeal"] == "0") echo "checked";?> />
                                <?php echo $lang["Concurrent option 1"];?>
                            </label>
                            <label class="radio radio-inline">
                                <input type="radio" value="1" name="syncdeal" <?php if ($prcs["syncdeal"] == "1") echo "checked";?> />
                                <?php echo $lang["Concurrent option 2"];?>
                            </label>
                            <label class="radio radio-inline">
                                <input type="radio" value="2" name="syncdeal" <?php if ($prcs["syncdeal"] == "2") echo "checked";?> />
                                <?php echo $lang["Concurrent option 3"];?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Concurrent merger"];?></label>
                        <div class="controls">
                            <label class="radio radio-inline">
                                <input type="radio" name="gathernode" value="0" <?php if ($prcs["gathernode"] == "0") echo "checked";?> />
                                <?php echo $lang["Concurrent merger 1"];?>
                            </label>
                            <label class="radio radio-inline">
                                <input type="radio" name="gathernode" value="1" <?php if ($prcs["gathernode"] == "1") echo "checked";?> />
                                <?php echo $lang["Concurrent merger 2"];?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Deal time limit"];?></label>
                        <div class="controls">
                            <div class="input-group" style="width:200px;">
                                <input type="text" value="<?php echo $prcs["timeout"];?>" name="timeout" />
                                <span class="input-group-addon"><?php echo $lang["Hour"];?></span>
                            </div>
                            <span class="help-inline"><?php echo $lang["Time out desc"];?></span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Save plugin"];?></label>
                        <div class="controls">
                            <input type="text" class="span6" name='pluginsave' readonly>
                            <!-- TODO: 插件对话框: 形式与界面皆不清楚，暂时简单地使用对话框 -->
                            <a href="javascript:;" class="ilsep anchor" id="select_save_plugin"><?php echo $lang["Select"];?></a>
                            <a href="javascript:;" class="ilsep anchor" id="clear_save_plugin"><?php echo $lang["Clear"];?></a>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Trans plugin"];?></label>
                        <div class="controls">
                            <input type="text" class="span6" name='plugin' readonly>
                            <!-- TODO: 插件对话框 -->
                            <a href="javascript:;" class="ilsep anchor" id="select_forward_plugin"><?php echo $lang["Select"];?></a>
                            <a href="javascript:;" class="ilsep anchor" id="clear_forward_plugin"><?php echo $lang["Clear"];?></a>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang["Global attach"];?></label>
                        <div class="controls">
                            <div>
                                <label class="checkbox checkbox-inline">
                                    <input type="checkbox" value="1" name="attachpriv[]" <?php if (in_array("1", $prcs["attachpriv"])) echo "checked";?> />
                                    <?php echo $lang["New permissions"];?>
                                </label>
                                <label class="checkbox checkbox-inline">
                                    <input type="checkbox" value="2" name="attachpriv[]" <?php if (in_array("2", $prcs["attachpriv"])) echo "checked";?> />
                                    <?php echo $lang["Edit permissions"];?>
                                </label>
                                <label class="checkbox checkbox-inline">
                                    <input type="checkbox" value="3" name="attachpriv[]" <?php if (in_array("3", $prcs["attachpriv"])) echo "checked";?> />
                                    <?php echo $lang["Delete permissions"];?>
                                </label>
                                <label class="checkbox checkbox-inline">
                                    <input type="checkbox" value="4" name="attachpriv[]" <?php if (in_array("4", $prcs["attachpriv"])) echo "checked";?> />
                                    <?php echo $lang["Download permissions"];?>
                                </label>
                                <label class="checkbox checkbox-inline">
                                    <input type="checkbox" value="5" name="attachpriv[]" <?php if (in_array("5", $prcs["attachpriv"])) echo "checked";?> />
                                    <?php echo $lang["Print permissions"];?>
                                </label>
                            </div>
                            <span class="help-inline"><?php echo $lang["Global attach desc"];?></span>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </form>
</div>
<script>
    // @Todo: 这里的代码必须整理
    $(function() {
        $("#flow_edit_tab li:has('.active') a").tab("show");
        $(".checkbox input[type='checkbox'], .radio input[type='radio']").label();
        $("[data-toggle='tooltip']").tooltip();
        /**
         * 会签选项
         */
        $("#sign_options").on("change", function() {
            $("#sign_visibility").toggle(this.value === "0" || this.value === "2");
        });
        /**
         * 步骤经办人权限
         */
        $("#operator_allow").userSelect({
            data: Ibos.data.get(),
            type: "all"
        });

        $("#step_form_field").on("change", "input[type='checkbox']", function() {
            var elemId = this.id,
                    values = elemId.split("_", 2),
                    type = values[0], // 
                    id = values[1],
                    isChecked = this.checked;
            if (id) {
                if (type === "write") {
                    formCheck.writeCheck($(this));
                    $('#write').prop('disabled', false);
                    if ($('#write').prop('checked') == true) {
                        $('#write').prop('checked', false);
                    }
                } else if (type === "secret") {
                    formCheck.secretCheck($(this));
                    $('#secret').prop('disabled', false);
                    if ($('#secret').prop('checked') == true) {
                        $('#secret').prop('checked', false)
                    }
                } else if (type === "regex") {
                    formCheck.ruleCheck($(this));
                    $('#check').prop('disabled', false);
                    if ($('#check').prop('checked') == true) {
                        $('#check').prop('checked', false);
                    }
                } else if (type === 'micro') {
                    $('#check').prop('disabled', false);
                    if ($('#check').prop('checked') == true) {
                        $('#check').prop('checked', false);
                    }
                }
            } else {
                if (elemId === 'write') {
                    if (isChecked) {
                        formCheck.checkAll('write', true);
                        $('#secret').prop('disabled', true).prop('checked', false);
                        $('#check').prop('checked', false).prop('disabled', false);
                    } else {
                        formCheck.checkAll('write', false);
                        $('#secret').prop('checked', false).prop('disabled', false);
                        $('#check').prop('disabled', true).prop('checked', false);
                    }
                } else if (elemId === 'secret') {
                    if (isChecked) {
                        formCheck.checkAll('secret', true);
                        $('#write').prop('disabled', true).prop('checked', false);
                    } else {
                        formCheck.checkAll('secret', false);
                        $('#write').prop('checked', false).prop('disabled', false);
                    }
                } else if (elemId === 'check') {
                    if (isChecked) {
                        formCheck.checkAll('check', true);
                        formCheck.checkAll('micro', true);
                    } else {
                        formCheck.checkAll('check', false);
                        formCheck.checkAll('micro', false);
                    }
                }
            }
        });

        var formCheck = {
            $writeSelector: $("input[name='write[]']"),
            $secretSelector: $("input[name='secret[]']"),
            $checkSelector: $("input[name='check[]']"),
            $microSelector: $("input[name='micro[]']"),
            writeCheck: function($obj) {
                var id = $obj.data('id'), isChecked = $obj.prop('checked');
                if ($obj.prop('disabled') !== true) {
                    if (isChecked) {
                        $('#secret_' + id).prop('disabled', true);
                        $('#regex_' + id).prop('checked', false).prop('disabled', false);
                        $('#regexList_' + id).hide();
                        $('#micro_' + id).prop('checked', false).prop('disabled', false).parent().css('color', '#000');
                    } else {
                        $('#secret_' + id).prop('checked', false).prop('disabled', false);
                        $('#regex_' + id).prop('disabled', true).prop('checked', false);
                        $('#regexList_' + id).prop('disabled', true).hide();
                        $('#micro_' + id).prop('disabled', true).prop('checked', false).parent().css('color', '#a0a0a0');
                    }
                }
            },
            ruleCheck: function($obj) {
                var id = $obj.data('id'), isChecked = $obj.prop('checked');
                if ($obj.prop('disabled') !== true) {
                    if (isChecked) {
                        $('#regexList_' + id).prop('disabled', false).show();
                    } else {
                        $('#regexList_' + id).prop('disabled', true).hide();
                    }
                }
            },
            secretCheck: function($obj) {
                var id = $obj.data('id'), isChecked = $obj.prop('checked');
                if ($obj.prop('disabled') !== true) {
                    if (isChecked) {
                        $('#write_' + id).prop('disabled', true).prop('checked', false);
                    } else {
                        $('#write_' + id).prop('disabled', false).prop('checked', false);
                    }
                }
            },
            checkAll: function(type, checked) {
                var that = this;
                switch (type) {
                    case 'write':
                        that.$writeSelector.each(function() {
                            if ($(this).prop('disabled') !== true) {
                                $(this).prop('checked', checked);
                            }
                            that.writeCheck($(this));
                        });
                        break;
                    case 'secret':
                        that.$secretSelector.each(function() {
                            if ($(this).prop('disabled') !== true) {
                                $(this).prop('checked', checked);
                            }
                            that.secretCheck($(this));
                        });
                        break;
                    case 'check':
                        that.$checkSelector.each(function() {
                            if ($(this).prop('disabled') !== true) {
                                $(this).prop('checked', checked);
                            }
                            that.ruleCheck($(this));
                        });
                        break;
                    case 'micro':
                        that.$microSelector.each(function() {
                            if ($(this).prop('disabled') !== true) {
                                $(this).prop('checked', checked);
                            }
                        });
                        break;
                }
            }
        };
        /**
         * 自动选人规则
         * @type type
         */
        var automaticRule = {
            user: function() {
                $("#assign_user").show().siblings().hide();
            },
            field: function() {
                if ($("#form_field_select").html() == '') {
                    var formItemUrl = Ibos.app.url('workflow/api/getitem'), param = {flowid: <?php echo $prcs["flowid"];?>};
                    $.get(formItemUrl, param, function(res) {
                        if (res.isSuccess) {
                            var options = "",
                                    autotype = "<?php echo $prcs["autotype"];?>",
                                    autouser = "<?php echo $prcs["autouser"];?>";
                            $.each(res.data, function(i, n) {
                                options += "<option value='" + i + "'>" + n + "</option>";
                            });
                            $("#form_field_select").html(options);
                            if (autotype == "7") {
                                console.log(autouser);
                                $("#form_field_select").find("option[value=" + (autouser ? +autouser.replace(/\D*/g, "") : '') + "]").attr("selected", "selected");
                            }
                        }
                    }, 'json');
                }
                $("#form_field").show().siblings().hide();
            },
            step: function() {
                $("#assign_step").show().siblings().hide();
            },
            dept: function() {
                $("#dept_for").show().siblings().hide();
            },
            none: function() {
                $("#assign_user, #form_field, #assign_step, #dept_for").hide();
            }
        };
        $('#operator_automatic').on("change", function() {
            var value = this.value;
            // 部门对象针对
            if (value === "2" || value === "4" || value === "6" || value === "9") {
                automaticRule.dept();
                // 表单字段指定
            } else if (value === "7") {
                automaticRule.field();
                // 步骤指定
            } else if (value === "8") {
                automaticRule.step();
                // 人员指定
            } else if (value === "3") {
                automaticRule.user();
                // 其它
            } else {
                automaticRule.none();
            }
        });
        $("#force_forward, #rollback,#allowed_change").iSwitch();
        // 插件选择
        // @Todo: 坑爹呐这是。。
        var showPluginsDialog = function() {
            Ui.dialog({
                id: "d_plugin_dialog",
                title: U.lang("WF.PLUGIN_SELECT"),
                content: U.lang("WF.NO_PLUGIN_AVAILABLE"),
                cancel: true,
                cancelVal: U.lang("CLOSE"),
                width: 300
            });
        };
        $("#select_save_plugin, #select_forward_plugin").click(showPluginsDialog);
        //
        // @Todo: 表单条件选择器
        $("#step_condition").on("click", "[data-condition]", function() {
            var id = $.attr(this, 'data-id');
            var $conditionLogic = $("#condition_logic_" + id), $conditionLogicInput = $("#condition_logic_input_" + id);
            $conditionLogicInput.val($conditionLogic.find('button.active').attr('data-value'));
            var logic = $conditionLogicInput.val();
            $conditionLogic.on("click", "button", function() {
                var value = $.attr(this, "data-value");
                $conditionLogicInput.val(value);
                logic = value;
            });
            var formCondition = new FormCondition(document.getElementById("condition_select_" + id));
            var type = $.attr(this, "data-condition");
            if (type === "addCondition") {
                var field = $("#condition_field_" + id).val(), operator = $("#condition_operator_" + id).val(), value = $("#condition_value_" + id).val();
                formCondition.addCondition(field, operator, value, logic);
            } else {
                formCondition[type](logic);
            }
            $("#condition_result_" + id).val(formCondition.getConditions());
        });

        // 基本信息

        var changeNodeType = function(type) {

        };
        /**
         * 切换节点类型
         */
        $("#node_type").on("change", function() {
            var isStepNode = (this.value === "0");
            $step = $("#node_type_step"), $subflow = $("#node_type_subflow");
            $step.toggle(isStepNode);
            $subflow.toggle(!isStepNode);
            // 子流程只能配置基本信息
            $('#flow_edit_tab').find('li:first').siblings('li').toggle(isStepNode)
        }).trigger('change');
        /**
         *  子流程返回步骤主办人
         */
        $("#subflow_return_sponsor").userSelect({
            data: Ibos.data.get("user"),
            type: "user",
            maximumSelectionSize: 1
        });
        /**
         * 子流程返回步骤经办人
         */
        $("#subflow_return_handler").userSelect({
            data: Ibos.data.get("user"),
            type: "user"
        });
        // 子流程类型
        // 对应关系
        var $subflowType = $("#subflow_type"),
                $parentFlowSelect = $("#subflow_parent_field"),
                $childFlowSelect = $("#subflow_child_field"),
                $relativeBtn = $("#subflow_relative_btn"),
                parentFlowValue,
                childFlowValue;

        var refreshRelativeBtnStatus = function() {
            parentFlowValue = $parentFlowSelect.val();
            childFlowValue = $childFlowSelect.val();
            $relativeBtn.prop("disabled", !(parentFlowValue && childFlowValue));
        };
        $subflowType.on("change", function(evt, data) {
            var val = this.value;
            data = data || {};
            $.get(Ibos.app.url("workflow/api/getField"), {flowid: val}, function(res) {
                if (res.isSuccess) {
                    var options = "";
                    for (var i = 0; i < res.data.length; i++) {
                        options += "<option value='" + res.data[i] + "'>" + res.data[i] + "</option>";
                    }
                    $("#subflow_child_field").html(options);
                    refreshRelativeBtnStatus();
                    // 除了初始化时的change事件，改变子流程类型时，清空映射关系
                    if (!data.init) {
                        fieldMatchUp.clear();
                    }
                }
            }, 'json');
        });
        $parentFlowSelect.on("change", refreshRelativeBtnStatus);
        $childFlowSelect.on("change", refreshRelativeBtnStatus);

        var $mapList = $("#field_map_list");
        var fieldMatchUp = {
            $container: $mapList,
            $input: $("#field_map_value"),
            tpl: "<li data-value='<%=id%>'><%=parent%>=&gt;<%=child%><a href='javascript:;' class='close' data-type='removeMap' data-id=<%=id%>>x</a></li>",
            refreshValue: function() {
                var val = "",
                        $items = this.$container.find("li"),
                        rel;

                val = $items.map(function($item) {
                    return $.attr(this, "data-value").replace("_", "=>")
                }).get().join(",");

                this.$input.val(val);
            },
            add: function(parent, child) {
                var id = parent + "_" + child;
                if (this.$container.find("li[data-value='" + id + "']").length) {
                    Ui.tip(U.lang("WF.EXISTING_CORRESPONDENCE"), "warning");
                    return;
                }

                $.tmpl(this.tpl, {id: id, parent: parent, child: child}).appendTo(this.$container);
                this.refreshValue();
            },
            remove: function(id) {
                this.$container.find("li[data-value='" + id + "']").remove();
                this.refreshValue();
            },
            clear: function() {
                this.$container.empty();
                this.refreshValue();
            }
        };

        $relativeBtn.on("click", function() {
            fieldMatchUp.add(parentFlowValue, childFlowValue);
        });
        $mapList.on("click", "[data-type='removeMap']", function() {
            fieldMatchUp.remove($.attr(this, "data-id"));
        });
        // 子流程返回结果交互
        $("[data-act='overAct']").on('change', function() {
            $('#subflow_prcs_back').toggle(this.value === '1');
        });
    });
    $(document).ready(function() {
        $("#assign_sponsor").userSelect({
            data: Ibos.data.get("user"),
            type: "user",
            maximumSelectionSize: 1
        });
        $("#assign_handler").userSelect({
            data: Ibos.data.get("user"),
            type: "user"
        });
        $("#subflow_type, #operator_automatic,#sign_options").trigger('change', {init: true});
        // 以下这段为实现表单字段的数据还原。欢迎吐槽及优化。。。

<?php if (!empty($prcsItem)) : ?>
            var processStr = '<?php echo implode(",", $prcsItem);?>';
            var pArr = processStr.split(',');
            for (var i = 0; i < pArr.length; i++) {
                $('#write_' + pArr[i]).prop('checked', true).trigger('change');
            }
<?php endif; ?>

<?php if (!empty($hiddenItem)) : ?>
            var hiddenStr = '<?php echo implode(",", $hiddenItem);?>';
            var hArr = hiddenStr.split(',');
            for (var i = 0; i < hArr.length; i++) {
                $('#secret_' + hArr[i]).prop('checked', true).trigger('change');
            }
<?php endif; ?>

<?php if (!empty($microItem)) : ?>
            var microStr = '<?php echo implode(",", $microItem);?>';
            var mArr = microStr.split(',');
            for (var i = 0; i < mArr.length; i++) {
                $('#micro_' + mArr[i]).prop('checked', true);
            }
<?php endif; ?>

<?php if (isset($checkStr)) : ?>
            var checkJson = $.parseJSON('<?php echo $checkStr;?>');
            $.each(checkJson, function(i, n) {
                $('#regex_' + i).prop('checked', true).trigger('change');
                $('#regexList_' + i).val(n);
            });
<?php endif; ?>

    });
</script>
