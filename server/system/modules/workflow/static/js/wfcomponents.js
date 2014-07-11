/**
 * wfcomponents.js
 * 工作流使用，展示组件初始化通用JS
 * IBOS
 * @author		banyan Cheung
 * @version	$Id: wfcomponents.js 2784 2014-03-17 06:03:27Z zhangrong $
 */
var wfComponents = (function() {
	/**
	 * 表单容器
	 * @type object
	 */
	var $container;
	/**
	 * 
	 * @type Array
	 */
	var richTextEditors = [];
	/**
	 * 计算组件
	 * @type object
	 */
	var calc = {
		/**
		 * 人民币计算大写
		 * @param {type} currencyDigits
		 * @returns {String}
		 */
		'rmb': function(currencyDigits) {
			// Constants:
			var MAXIMUM_NUMBER = 99999999999.99;
			// Predefine the radix characters and currency symbols for output:
			var CN_ZERO = L.YUANCAPITAL.ZERO;
			var CN_ONE = L.YUANCAPITAL.ONE;
			var CN_TWO = L.YUANCAPITAL.TWO;
			var CN_THREE = L.YUANCAPITAL.THREE;
			var CN_FOUR = L.YUANCAPITAL.FOUR;
			var CN_FIVE = L.YUANCAPITAL.FIVE;
			var CN_SIX = L.YUANCAPITAL.SIX;
			var CN_SEVEN = L.YUANCAPITAL.SEVEN;
			var CN_EIGHT = L.YUANCAPITAL.EIGHT;
			var CN_NINE = L.YUANCAPITAL.NINE;
			var CN_TEN = L.YUANCAPITAL.TEN;
			var CN_HUNDRED = L.YUANCAPITAL.HUNDRED;
			var CN_THOUSAND = L.YUANCAPITAL.THOUSAND;
			var CN_TEN_THOUSAND = L.YUANCAPITAL.TEN_THOUSAND;
			var CN_HUNDRED_MILLION = L.YUANCAPITAL.HUNDRED_MILLION;
			var CN_DOLLAR = L.YUANCAPITAL.DOLLAR;
			var CN_TEN_CENT = L.YUANCAPITAL.TEN_CENT;
			var CN_CENT = L.YUANCAPITAL.CENT;
			var CN_INTEGER = L.YUANCAPITAL.INTEGER;
			// Variables:
			var integral; // Represent integral part of digit number.
			var decimal; // Represent decimal part of digit number.
			var outputCharacters; // The output result.
			var parts;
			var digits, radices, bigRadices, decimals;
			var zeroCount;
			var i, p, d;
			var quotient, modulus;
			// Validate input string:
			currencyDigits = currencyDigits.toString();
			if (currencyDigits == "") {
				return "";
			}
			if (currencyDigits.match(/[^,.\d]/) != null) {
				return "";
			}
			if ((currencyDigits).match(/^((\d{1,3}(,\d{3})*(.((\d{3},)*\d{1,3}))?)|(\d+(.\d+)?))$/) == null) {
				return "";
			}
			// Normalize the format of input digits:
			currencyDigits = currencyDigits.replace(/,/g, ""); // Remove comma delimiters.
			currencyDigits = currencyDigits.replace(/^0+/, ""); // Trim zeros at the beginning.
			// Assert the number is not greater than the maximum number.
			if (Number(currencyDigits) > MAXIMUM_NUMBER) {
				return "";
			}
			// Process the coversion from currency digits to characters:
			// Separate integral and decimal parts before processing coversion:
			parts = currencyDigits.split(".");
			if (parts.length > 1) {
				integral = parts[0];
				decimal = parts[1];
				// Cut down redundant decimal digits that are after the second.
				decimal = decimal.substr(0, 2);
			}
			else {
				integral = parts[0];
				decimal = "";
			}
			// Prepare the characters corresponding to the digits:
			digits = new Array(CN_ZERO, CN_ONE, CN_TWO, CN_THREE, CN_FOUR, CN_FIVE, CN_SIX, CN_SEVEN, CN_EIGHT, CN_NINE);
			radices = new Array("", CN_TEN, CN_HUNDRED, CN_THOUSAND);
			bigRadices = new Array("", CN_TEN_THOUSAND, CN_HUNDRED_MILLION);
			decimals = new Array(CN_TEN_CENT, CN_CENT);
			// Start processing:
			outputCharacters = "";
			// Process integral part if it is larger than 0:
			if (Number(integral) > 0) {
				zeroCount = 0;
				for (i = 0; i < integral.length; i++) {
					p = integral.length - i - 1;
					d = integral.substr(i, 1);
					quotient = p / 4;
					modulus = p % 4;
					if (d == "0") {
						zeroCount++;
					}
					else {
						if (zeroCount > 0) {
							outputCharacters += digits[0];
						}
						zeroCount = 0;
						outputCharacters += digits[Number(d)] + radices[modulus];
					}
					if (modulus == 0 && zeroCount < 4) {
						outputCharacters += bigRadices[quotient];
					}
				}
				outputCharacters += CN_DOLLAR;
			}
			// Process decimal part if there is:
			if (decimal != "") {
				for (i = 0; i < decimal.length; i++) {
					d = decimal.substr(i, 1);
					if (d != "0") {
						outputCharacters += digits[Number(d)] + decimals[i];
					}
				}
			}
			// Confirm and return the final output string:
			if (outputCharacters == "") {
				outputCharacters = CN_ZERO + CN_DOLLAR;
			}
			if (decimal == "") {
				outputCharacters += CN_INTEGER;
			}
			//outputCharacters = CN_SYMBOL + outputCharacters;
			return outputCharacters;
		},
		/**
		 * 最大值
		 * @returns {unresolved}
		 */
		'max': function() {
			if (arguments.length == 0) {
				return;
			}
			var maxNum = arguments[0];
			for (var i = 0; i < arguments.length; i++) {
				maxNum = Math.max(maxNum, arguments[i]);
			}
			return parseFloat(maxNum);
		},
		/**
		 * 最小值
		 * @returns {unresolved}
		 */
		'min': function() {
			if (arguments.length == 0) {
				return;
			}
			var minNum = arguments[0];
			for (var i = 0; i < arguments.length; i++) {
				minNum = Math.min(minNum, arguments[i]);
			}
			return parseFloat(minNum);
		},
		/**
		 * 取模运算
		 * @returns {String}
		 */
		'mod': function() {
			if (arguments.length == 0) {
				return;
			}
			var firstNum = arguments[0];
			var secondNum = arguments[1];
			var result = firstNum % secondNum;
			result = isNaN(result) ? "" : parseFloat(result);
			return result;
		},
		/**
		 * 绝对值
		 * @param {type} val
		 * @returns {@exp;Math@call;abs}
		 */
		'abs': function(val) {
			return Math.abs(parseFloat(val));
		},
		/**
		 * 天数计算
		 * @param {type} val
		 * @returns {Number|@exp;Math@call;floor}
		 */
		'day': function(val) {
			return val == 0 ? 0 : Math.floor(val / 86400);
		},
		/**
		 * 小时
		 * @param {type} val
		 * @returns {@exp;Math@call;floor|Number}
		 */
		'hour': function(val) {
			return val == 0 ? 0 : Math.floor(val / 3600);
		},
		/**
		 * 日期计算
		 * @param {type} val
		 * @returns {String}
		 */
		'date': function(val) {
			return (val >= 0) ? Math.floor(val / 86400) + L.TIME.DAY + Math.floor((val % 86400) / 3600) + L.TIME.HOUR + Math.floor((val % 3600) / 60) + L.TIME.MIN + Math.floor(val % 60) + L.TIME.SEC : L.TIME.INVALID_DATE;//'日期格式无效'
		},
		/**
		 * 列表控件计算
		 * @param {type} olist
		 * @param {type} col
		 * @returns {Number}
		 */
		'list': function(olist, col) {
			var output = 0;
			var tableID = olist.getAttribute("id");
			var table = listView.getTable(tableID);
			var row_length = table.children[1].rows.length;
			if (document.getElementById(tableID + '_sum')) {
				row_length--;
			}
			for (var i = 0; i < row_length; i++) {
				for (var j = 0; j < table.children[1].rows[i].cells.length - 1; j++) {
					if (j == col) {
						var child_obj = table.children[1].rows[i].cells[j].firstChild;
						if (child_obj && child_obj.tagName) {
							var olist_val = child_obj.value;
							olist_val = (olist_val == "" || olist_val.replace(/\s/g, '') == "") ? 0 : olist_val;
							olist_val = (isNaN(olist_val)) ? NaN : olist_val;
							output += parseFloat(olist_val);
						} else {
							output += parseFloat(child_obj.data);
						}
					}
				}
			}
			return parseFloat(output);
		},
		/**
		 * 计算控件获取item的值
		 * @param {type} $item
		 * @returns {@exp;d@call;getTime|Number|@exp;document@call;getElementById}
		 */
		'getVal': function(id) {
			var $item = getItem(id);
			if ($item.length == 0) {
				return 0;
			}
			if ($item.data('flag') == 'listview') {
				return document.getElementById('lv_' + id);
			} else if ($item.data('flag') == 'date') {
				var val = $item.parent().data("datetimepicker").getDate();
				var d = new Date(val);
				return d.getTime() / 1000;
			} else {
				var val = $item.val();
				if (val == "") {
					val = 0;
				}
				return val;
			}
		}
	};
	/**
	 * 列表控件事件
	 * @type object
	 */
	var listView = {
		_itemCount: 0,
		/**
		 * 获得表格DOM对象
		 * @param {type} tableID
		 * @returns {@exp;@call;$@pro;find@call;@call;get}
		 */
		'getTable': function(tableID) {
			return $('#' + tableID).find('table').get(0);
		},
		/**
		 * 添加列表控件行，实际上，此处有 rowValue 时，根据 rowValue 还原出一行，没有时，新增空行
		 * @param {type} tableID
		 * @param {type} readonly
		 * @param {type} rowValue
		 * @returns {undefined}
		 */
		'add': function(tableID, readonly, rowValue) {
			this._itemCount++;
			var myTable = this.getTable(tableID)
			var sum = myTable.getAttribute("data-sum"); //合计字段
			rowValue = rowValue.replace(/&lt;BR&gt;/g, "\r\n").replace(/&lt;br&gt;/g, "\r\n");
			var valArr = rowValue.split("`");
			//是否有合计字段？
			var sumFlag = 0;
			if (sum !== '') {
				var sumArr = sum.split("`");
				for (var i = 0; i < sumArr.length; i++) {
					if (sumArr[i] == 1) {
						sumFlag = 1;
						break;
					}
				}
			}
			//数据类型
			var colTypeArr = myTable.getAttribute("data-coltype").split("`");
			//数据值
			var colValArr = myTable.getAttribute("data-colvalue").split("`");
			var maxCell = myTable.rows[0].cells.length;
			if (myTable.rows.length == 2 || sumFlag == 0) {
				var myNewRow = myTable.children[1].insertRow(-1);
			} else {
				var myNewRow = myTable.children[1].insertRow(myTable.children[1].rows.length - 1);
			}
			//标识行id
			var rowID = tableID + "_r" + myNewRow.rowIndex;
			myNewRow.setAttribute("id", rowID);
			//---序号------
			var myNewCell = myNewRow.insertCell(-1);
			myNewCell.innerHTML = '<span class="label">' + myNewRow.rowIndex + '</span>';

			// 控件模板
			var tplMap = {
				'text': '<input class="input input-small" type="text" value="<%=value%>"/>',
				'textarea': '<textarea rows="2"><%=value%></textarea>',
				'select': '<select class="input input-small">' +
					'<% for (var i = 0; i < options.length; i++) { %>' +
						'<option value="<%= options[i] %>" <%= options[i] == value ? "selected" : ""%> ><%= options[i] %></option>' +
					'<% } %>' +
				'</select>',
				'radio': '<% for(var i = 0; i < radios.length; i++) { %>' +
					'<input type="radio" name="<%= name %>" value="<%= radios[i] %>" <%= radios[i] == value ? "checked" : "" %> >' +
					'<%= radios[i] %>' +
					'<% } %>',

				'default': '<input type="text" class="disabled input input-small" value="<%=value%>" readonly>'
			}

			for (var i = 0; i < maxCell - 2; i++) {
				myNewCell = myNewRow.insertCell(-1);
				//标识列id
				var nCellId = rowID + "_c" + myNewCell.cellIndex;
				myNewCell.setAttribute("id", nCellId);
				if (colTypeArr && colTypeArr[i] !== "") {
					myNewCell.setAttribute("field", colTypeArr[i]);
				}
				//列表项空数据使用getRunData获取后是不进行处理的，在这里取到的是undefined
				if (valArr[i] == 'undefined') {
					valArr[i] = "";
				}
				if (readonly == "1") {
					myNewCell.innerText = valArr[i];
				} else {
					var html = '';
					// 根据类型生成对应模板，插入视图
					switch (colTypeArr[i]) {
						case 'text':
							myNewCell.innerHTML = $.template(tplMap["text"], {
								value: valArr[i] || colValArr[i] || ""
							});
							break;
						case 'textarea':
							myNewCell.innerHTML = $.template(tplMap["textarea"], {
								value: valArr[i] || colValArr[i] || ""
							});
							break;
						case 'select':
							myNewCell.innerHTML = $.template(tplMap["select"], {
								value: valArr[i] || "",
								options: (colValArr[i] !== '' ? colValArr[i].split(",") : [])
							});
							break;
						case 'radio':
							myNewCell.innerHTML = $.template(tplMap["radio"], {
								name: "radio" + this._itemCount + i,
								radios: colValArr[i] !== '' ? colValArr[i].split(",") : [],
								value: valArr[i]
							});
							break;
						case 'checkbox':
							var nFlag = 0;
							if (rowValue !== "") {
								var aValue = valArr[i].split(',');
							}

							// 如果 Checkbox 默认值不为空，则根据默认值创建对应个数的复选框
							if (colValArr[i] !== '') {
								var colValueInner = colValArr[i].split(",");
								for (var j = 0; j < colValueInner.length; j++) {
									// 还原行时，根据原来的值默认选中对应的复选框
									if (rowValue !== "") {
										for (var k = 0; k < aValue.length; k++) {
											// 默认选中
											if (aValue[k] == colValueInner[j].replace(/(^\s*)|(\s*$)/g, "")) {
												html += "<input type='checkbox' value=\"" + colValueInner[j] + "\" checked>" + colValueInner[j];
												nFlag = 1;
											}
										}
									}
									if (nFlag == 0) {
										html += "<input type='checkbox' value=\"" + colValueInner[j] + "\">" + colValueInner[j];
									}
									nFlag = 0;
								}
							// 否则创建一个空复选框，不具有实际意义
							} else {
								html += "<input type='checkbox'>"
							}
							myNewCell.innerHTML = html;
							break;
						case 'date':
							myNewCell.innerHTML = $.template(tplMap["text"], {
								value: valArr[i] || colValArr[i] || ""
							});
							$(myNewCell).find("input").datepicker({
								orientation: "right"
							});
							break;
						default:
							myNewCell.innerHTML = $.template(tplMap["default"], {
								value: valArr[i] || colValArr[i] || ""
							});
					}
					html = "";
				}
			}
			myNewCell = myNewRow.insertCell(-1);
			var optHtml = '';
			//删除
			if (!readonly) {
				optHtml += "<a class='cbtn o-trash mls' href='javascript:void(0);' title='删除' data-click='lvDel' data-id='" + tableID + "'></a>";
			}
			myNewCell.innerHTML = optHtml;
			//确保只初始化一次合计栏
			if (sumFlag == 1 && myTable.rows.length == 3) {
				this.addSum(tableID, sum, sumFlag);
			}
		},
		/**
		 * 在表格的最后增加一合计行。
		 * @param string tableID 当前控件生成的表格id
		 * @param obj sum 已经分割好的数组合计字段数组
		 * @param bool 是否有合计字段的标识
		 */
		'addSum': function(tableID, sum, sum_flag) {
			var myTable = this.getTable(tableID);
			//第一行是序号，因此加上0
			var sSum = "0`" + sum;
			var aSum = sSum.split("`");
			var nMaxCell = myTable.rows[0].cells.length;
			//增加合计
			var oSumRow = myTable.children[1].insertRow(-1);
			var sHtml = '';
			oSumRow.setAttribute('id', tableID + '_sum');
			var firstCell = oSumRow.insertCell(-1);
			firstCell.innerHTML = "合计";
			for (var i = 1; i < nMaxCell - 1; i++) {
				var oSumCell = oSumRow.insertCell(-1);
				if (aSum && aSum[i] == 1) {
					sHtml = "<input type='text' readonly class='input input-small disabled'>";
					oSumCell.innerHTML = sHtml;
				}
			}
			oSumCell = oSumRow.insertCell(-1);
			//合计
			oSumCell.innerHTML = "";
			setInterval(function() {
				listSumInterval(tableID, sum);
			}, 2000);
		},
		/**
		 * 删除一行
		 * @param int lv_tb_id 列表控件id
		 * @param obj del_btn 用户点击的对象
		 */
		'del': function(tableID, del_btn) {
			var myTable = this.getTable(tableID);
			myTable.deleteRow(del_btn.parentNode.parentNode.rowIndex);
			if (myTable.rows.length == 2 && document.all(tableID + "_sum")) {
				myTable.deleteRow(1);
			}
		},
		/**
		 * 
		 * @param {type} tableID
		 * @returns {undefined}
		 */
		'output': function(tableID) {
			var dataStr = '';
			var myTable = this.getTable(tableID);
			var length = myTable.children[1].rows.length;
			if (document.getElementById(tableID + '_sum')) {
				length--;
			}
			for (var i = 0; i < length; i++) {
				for (var j = 1; j < (myTable.children[1].rows[i].cells.length) - 1; j++) {
					if (myTable.children[1].rows[i].cells[j].firstChild) {
						dataStr += myTable.children[1].rows[i].cells[j].firstChild.value + "`";
					}
				}
				dataStr += "\n";
			}
			var item = getItem(tableID.substr(3));
			item.val(dataStr);
		}
	};
	/**
	 * 初始化处理器
	 * @type Object
	 */
	var initItemHandler = {
		/**
		 * 初始化日期控件
		 * @param {Object} param
		 * @param {Object} $ctx
		 * @returns {undefined}
		 */
		'date': function(param, $ctx) {
			var format = param.dateFormat;
			var dateTimeParam = { format: format };
			// 如果格式是年月日，那么可选的最小范围就为年月日，反之即可精确到时分
			switch(format) {
				case "yyyy":
					dateTimeParam.viewMode = dateTimeParam.minViewMode = 2;
					break;
				case "yyyy-mm":
					dateTimeParam.viewMode = dateTimeParam.minViewMode  = 1;
					break;
				case "yyyy-mm-dd hh":
				case "yyyy-mm-dd hh:ii":
					dateTimeParam.pickTime = true;
					dateTimeParam.pickSeconds = false;
					break;
				case "yyyy-mm-dd hh:ii:ss":
					dateTimeParam.pickTime = true;
					break;
			}
			$ctx.datepicker(dateTimeParam);
		},
		/**
		 * 初始化计算控件
		 * @param {Object} param
		 * @param {Object} $ctx
		 * @returns {undefined}
		 */
		'calc': function(param, $ctx) {
			// 激活定时器
			var timer = setInterval(function() {
				calcInterval(param, $ctx);
			}, 1000);

		},
		/**
		 * 初始化用户选择框组件
		 * @param {Object} param
		 * @param {Object} $ctx
		 * @returns {undefined}
		 */
		'user': function(param, $ctx) {
			var type = param.selectType, single = param.single;
			var $obj = $ctx.find('input');
			var opt = {
				// box: $obj.next(),
				type: type,
				data: Ibos.data.get(type)
			};
			// 单选
			if (single == 1) {
				opt.maximumSelectionSize = "1";
			}
			$obj.userSelect(opt);
			if (param.disabled == 1) {
				$obj.userSelect('setReadOnly');
			}
		},
		/**
		 * 初始化列表控件与其定时器
		 * @param {Object} param
		 * @param {Object} $ctx
		 * @returns {undefined}
		 */
		'listview': function(param, $ctx) {
			var value = $ctx.find('input[data-flag=listview_data]').val();
			var tableID = $ctx.attr('id');
			if (!param.readonly) {
				var columNums = $ctx.find('table>thead>tr>th').length;
				var tpl = "<tfoot>" +
						"<tr><td colspan='" + (columNums) + "'>" +
						"<a href='javascript:;' data-id='" + tableID + "' data-click='lvAdd' class='cbtn o-plus mls' title='添加'></a>" +
						"</td></tr></tfoot>";
				$(tpl).insertAfter($ctx.find('table>tbody').eq(0));
			}
			if ($.trim(value) !== '') {
				var valueArr = value.split("\n");
				$.each(valueArr, function(i, val) {
					if (val !== '') {
						listView.add(tableID, param.readonly, val);
					}
				});
			}
			setInterval(function() {
				listCalcInterval(tableID, param.coltype, param.colvalue);
			}, 1000);
		},
		/**
		 * 初始化多行文本框组件的富文本模式
		 * @param {Object} param
		 * @param {Object} $ctx
		 * @returns {undefined}
		 */
		'rich': function(param, $ctx) {
			var id = $ctx.attr('id');
			richTextEditors[id] = new UE.ui.Editor({
				initialFrameWidth: param.width,
				initialFrameHeight: param.height,
				toolbars: UEDITOR_CONFIG.mode.mini
			}).render(id);
		},
		/**
		 * 初始化图片上传控件
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'imgupload': function(param, $ctx) {
			var input = "<input type=\"file\" style='position:absolute;filter:alpha(opacity=0);opacity:0;' size='1' hideFocus='' name=\"data_" + param.id + "\">";
			var defTpl = "<span data-click=\"imgupload\" style=\"width:" + param.width + "px;height:" +
					param.height + "px;display:inline-block;background:#F8F8F8 url('" + param.bg +
					"') no-repeat center center\"></span>\n";
			var imgTpl = "<img data-click=\"imgupload\" style=\"width:" + param.width + "px;height:" + param.height + "px;\" src=\"" + param.src + "\" />\n";
			if (param.src === '') {
				$ctx.append(defTpl + (param.read === '1' ? '' : input));
			} else {
				$ctx.append(imgTpl + (param.read === '1' ? '' : input));
			}
		},
		/**
		 * 进度条
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'progressbar': function(param, $ctx) {

		},
		/**
		 * 
		 * @param {type} param
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'qrcode': function(param, $ctx) {
			var id = param.id, $container = $("#qrcode_preview_" + id), text = Ibos.string.utf16to8($ctx.val());
			var size = param.size, width, height;
			if (size == '0') {
				width = height = 80;
			} else if (size == '1') {
				width = height = 120;
			} else {
				width = height = 180;
			}
			if ($.trim(text) !== "") {
				$container.empty().qrcode({
					text: text,
					width: width,
					height: height,
					render: ($.browser.msie && +$.browser.version < 9) ? 'table' : 'canvas'
				})//.prev().hide();
			} else {
				$container.empty()//.prev().show();
			}
		}
	};

	/**
	 * 对外点击事件接口
	 */
	Ibos.events.add({
		/**
		 * 列表控件：增加一条
		 * @param {type} parms
		 * @param {type} $ctx
		 * @returns {@exp;listView@call;add}
		 */
		'lvAdd': function(parms, $ctx) {
			return listView.add($ctx.data('id'), 0, '');
		},
		/**
		 * 列表控件：删除一条
		 * @param {type} params
		 * @param {type} $ctx
		 * @returns {@exp;listView@call;del}
		 */
		'lvDel': function(params, $ctx) {
			return listView.del($ctx.data('id'), $ctx.get(0));
		},
		/**
		 * 图片控件:点击上传
		 * @param {type} params
		 * @param {type} $ctx
		 * @returns {undefined}
		 */
		'imgupload': function(params, $ctx) {
			var parent = $ctx.parent();
			if (parent.data('read') == '0') {
				var input = $ctx.next();
				if (input) {
					input.click();
				}
			}
		}
	});
	/**
	 * 列表控件 ：合计项定时器
	 * @param {string} tableID
	 * @param {string} sum
	 * @returns {unresolved}
	 */
	function listSumInterval(tableID, sum) {
		var myTable = listView.getTable(tableID);
		if (myTable.rows.length == 2) {
			return;
		}
		var sumRow = myTable.children[1].rows[myTable.children[1].rows.length - 1];
		var sumArr = sum.split("`");
		for (var i = 0; i < sumArr.length; i++) {
			var sumVal = 0;
			if (sumArr[i] == 1) {
				for (var j = 0; j < myTable.children[1].rows.length - 1; j++) {
					var child = myTable.children[1].rows[j].cells[i + 1].firstChild;
					if (child && child.tagName) {
						sumVal += parseFloat(myTable.children[1].rows[j].cells[i + 1].firstChild.value == '' ? 0 : myTable.children[1].rows[j].cells[i + 1].firstChild.value);
					} else {
						sumVal += parseFloat(myTable.children[1].rows[j].cells[i + 1].innerText == '' ? 0 : myTable.rows[j].children[1].cells[i + 1].innerText);
					}
				}
				if (isNaN(sumVal)) {
					sumRow.sumVal[i + 1].firstChild.value = "0";
				} else {
					sumRow.cells[i + 1].firstChild.value = Math.round(sumVal * 10000) / 10000;
				}
			}
		}
	}

	/**
	 * 列表控件：计算公式定时器
	 * @param {string} tableID
	 * @returns {Boolean}
	 */
	function listCalcInterval(tableID) {
		var cellValue;
		var myTable = listView.getTable(tableID);
		if (!myTable) {
			return false;
		}
		//---- 数据类型数组 ----
		var colTypeArr = myTable.getAttribute('data-coltype').split("`"),
				colValArr = myTable.getAttribute('data-colvalue').split("`");
		// 还没有插入计算行，返回
		if (myTable.rows.length == 2) {
			return;
		}
		//第一个遍历：表格内所有行
		for (var i = 0; i < myTable.children[1].rows.length; i++) {
			//合计行除外
			if (myTable.children[1].rows[i].id == tableID + "_sum") {
				continue;
			}
			//第二个遍历，数据类型，取出数据类型为计算公式的数组索引
			for (var k = 0; k < colTypeArr.length; k++) {
				var col = colTypeArr[k];
				if (col !== "calc" || !myTable.children[1].rows[i].cells[k].firstChild.tagName) {
					continue;
				}
				//取得对应的计算公式
				var colValue = colValArr[k];
				//第三个遍历，遍历该行内所有单元格，序号除外，所以j从1开始
				for (var j = 1; j < myTable.children[1].rows[i].cells.length - 1; j++) {
					var re = new RegExp("\\[" + j + "\\]", "ig");
					var cell = myTable.children[1].rows[i].cells[j];
					//类型为单选及多选的值处理
					if (colTypeArr[j] == "radio" || colTypeArr[j] == "checkbox") {
						if ($("input:radio:checked,input:checkbox:checked", cell).length > 0) {
							cellValue = parseFloat($("input:radio:checked,input:checkbox:checked", cell).get(0).value);
						} else {
							cellValue = 0;
						}
					} else {
						cellValue = parseFloat(cell.firstChild.value);
						if (isNaN(cellValue)) {
							cellValue = 0;
						}
					}
					colValue = colValue.replace(re, cellValue);
				}
				//赋值给对应的单元格
				myTable.children[1].rows[i].cells[k + 1].firstChild.value = isNaN(eval(colValue)) ? 0 : Math.round(parseFloat(eval(colValue)) * 10000) / 10000;
			}
		}
	}
	/**
	 * 计算控件定时执行器
	 * @param {Object} param
	 * @param {Object} $ctx
	 * @returns {undefined}
	 */
	function calcInterval(param, $ctx) {
		var value = eval(param.exp);
		if (value == Infinity) {
			$ctx.val(L.WF.INVALID_OPERATION);
		} else if (!isNaN(value)) {
			var prec = !param.prec ? 10000 : Math.pow(10, param.prec);
			var result = new Number(parseFloat(Math.round(value * prec) / prec));
			$ctx.val(result.toFixed(param.prec));
		} else {
			$ctx.val(value);
		}
	}

	/**
	 * 获取name为data前缀的对象
	 * @param {string} itemID
	 * @returns {unresolved}
	 */
	function getItem(itemID) {
		return $container.find('[name=data_' + itemID + ']').eq(0);
	}
	/**
	 * 构造函数
	 * @param {type} $form
	 * @returns {wf._L1.wf}
	 */
	function wfComponents($form) {
		if (!(this instanceof wfComponents)) {
			return new wfComponents($form);
		}
		$container = $form || $(document.forms[0]);
	}

	wfComponents.prototype = {
		constructor: wfComponents,
		/**
		 * 初始化页面控件
		 * @returns {undefined}
		 */
		initItem: function() {
			var that = this;
			$('[data-item]').each(function() {
				var itemType = $(this).attr('data-item'), params = $(this).data();
				if (initItemHandler.hasOwnProperty(itemType)) {
					initItemHandler[itemType].call(initItemHandler, params, $(this), that);
				} else {
					Ui.tip(U.lang('WF.FORM_CONTROL') + itemType + U.lang('WF.FAILED_TO_INITALIZE'), 'danger');
				}
			});
		},
		/**
		 * 列表控件提交前预处理
		 * @returns {undefined}
		 */
		lvBeforeSubmit: function() {
			$container.find('[data-item="listview"]').each(function() {
				var ID = $(this).attr('id');
				listView.output(ID);
			});
		},
		/**
		 * 富文本模式提交前预处理
		 * @returns {undefined}
		 */
		richBeforeSubmit: function() {
			$.each(richTextEditors, function(i, o) {
				richTextEditors[i].sync();
			});
		}
	};
	return wfComponents;
}());