/**
* Окно управления просмотра специфики регистра БСК
* пользовательская часть 
*
*
* @package      BSK
* @access       All
* @autor        harabrinaM
* @version      16.10.2019
*/

sw.Promed.personBskRegistryDataWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Анкеты регистра болезней системы кровообращения'),
	maximized: true,
	closable: false,
	editableForm : true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	maximizable: true,
	minHeight: 550,
	minWidth: 1150,
	width: 1150,
	height: 550,
	modal: true,
	layout: 'border',
	onHide: Ext.emptyFn,
	objectName: 'personBskRegistryDataWindow',
	id: 'personBskRegistryDataWindow',
	objectSrc: '/jscore/Forms/Admin/personBskRegistryDataWindow.js',
	MorbusType_id: 0,
	MorbusTypeNoSaveAnket: [0,19,110,111,112],//недоступные для сохранения анкет ПН
	clickToPN: 0,//проверка на несохраненные данные
	createBSKDate: function(item) {

		var form = this;
		var panel = form.BSKRegistryData;
		if (!form.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) && form.formParams.Person_IsDead !== '2') {
			var BSKRegistry_setDate = (item.BSKRegistry_setDateFormat)? item.BSKRegistry_setDateFormat : new Date();
		} else 
			var BSKRegistry_setDate = (item.BSKRegistry_setDateFormat)? item.BSKRegistry_setDateFormat : '';
		var MorbusType_Name = (item.MorbusType_Name)? item.MorbusType_Name : item.questions[0].MorbusType_Name;
		//проверка на дату проведения
		var maxDate = new Date();
		var minDate = new Date();
		minDate.setDate(minDate.getDate()-30);
		var disabledDates = [];
		if (item.BSKRegistry.length>0) disabledDates = item.BSKRegistry.map(function (el) { return !Ext.isEmpty(el.BSKRegistry_setDate)? el.BSKRegistry_setDate:null });
		if (parseInt(form.MorbusType_id) == 19)
			var label = langs('Дата создания случая');
		else 
			var label = langs('Дата анкетирования');

		var TextFieldDate = new sw.Promed.SwDateField({
			id: 'PBRW_BSKRegistry_setDate',
			forSaveId: 'PBRW_BSKRegistry_setDate',
			isEdit: 1,
			fieldLabel: label,
			labelSeparator: ':',
			labelWidth: 70,
			disabled: item.disabled,
			width: 100,
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			format: 'd.m.Y',
			maxValue: maxDate,
			minValue: minDate,
			disabledDates: disabledDates.length>0? disabledDates:null,
			disabledDatesText: langs('Регистр по данному предмету наблюдения, для данного пациента на указанную дату уже существует!'),
			value: BSKRegistry_setDate,
			allowBlank: false,
			listeners: {
				'render' : function(){
					this.focus();
				},
				'change' : function(){
					var answerAge = 0;
					switch(parseInt(form.MorbusType_id)){
						case 84 : answerAge = 25;  break;
						case 88 : answerAge = 174; break;
						case 89 : answerAge = 206; break;
						case 50 : answerAge = 316; break;
					}
					var PersonAge = swGetPersonAge(form.findById('PBRW_infoPacient').getFieldValue('Person_Birthday'),this.getValue());
					if (panel.findById('PBRW_Answer_'+answerAge)) {
						panel.findById('PBRW_Answer_'+answerAge).setValue(PersonAge);
					}

					//Дата следующего осмотра автоматически должна рассчитываться от значения в поле «Дата анкетирования» по алгоритму #196897
					panel.findById('PBRW_BSKRegistry_nextDate').minValue = this.getValue();
					if (parseInt(form.MorbusType_id) == 84) {
						panel.findById('PBRW_BSKRegistry_nextDate').maxValue = this.getValue().add(Date.MONTH,18);
					} else panel.findById('PBRW_BSKRegistry_nextDate').maxValue = this.getValue().add(Date.MONTH,6);
					if (panel.findById('PBRW_BSKRegistry_nextDate').getValue() > panel.findById('PBRW_BSKRegistry_nextDate').maxValue) {
						panel.findById('PBRW_BSKRegistry_nextDate').setValue(panel.findById('PBRW_BSKRegistry_nextDate').maxValue);
					}
				}
			}
		});

		//Дата следующего осмотра автоматически должна рассчитываться от значения в поле «Дата анкетирования» по алгоритму #196897
		var maxNextD;
		var NextD;
		if (form.formParams.action == 'add') {
			if (parseInt(form.MorbusType_id) == 84) {
				maxNextD = BSKRegistry_setDate.add(Date.MONTH,18);
			} else maxNextD = BSKRegistry_setDate.add(Date.MONTH,6);
			NextD = maxNextD;
			item.disabled = false;
		} else {
			var setDate = new Date(item.BSKRegistry_setDate);
			if (parseInt(form.MorbusType_id) == 84) {
				switch (parseInt(item.BSKRegistry_riskGroup)) {
					case 1:
						maxNextD = setDate.add(Date.MONTH,18);
						break;
					case 2:
						maxNextD = setDate.add(Date.MONTH,12);
						break;
					case 3:
						maxNextD = setDate.add(Date.MONTH,6);
						break;
							
					default:
						maxNextD = setDate.add(Date.MONTH,18);
						break;
				}
			} else maxNextD = setDate.add(Date.MONTH,6);
			NextD = item.BSKRegistry_nextDate;
			item.disabled = true;
		}
		var TextFieldNextDate = new sw.Promed.SwDateField({
			id: 'PBRW_BSKRegistry_nextDate',
			forSaveId: 'PBRW_BSKRegistry_nextDate',
			isEdit: 2,
			fieldLabel: langs('Дата следующего осмотра'),
			labelSeparator: ':',
			labelWidth: 70,
			disabled: item.disabled,
			width: 100,
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			format: 'd.m.Y',
			maxValue: maxNextD,
			minValue: BSKRegistry_setDate,
			value: NextD,
			allowBlank: false,
			listeners: {
				'render' : function(){
					if (form.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) || parseInt(form.MorbusType_id) == 113)
					this.setContainerVisible(false);
				}
			}
		});
		
		panel.add(new Ext.form.FormPanel(
			{
				autoHeight: true,
				title: langs('Предмет наблюдения: ')+MorbusType_Name,
				collapsible: false,
				labelAlign: 'right',
				border: true,
				bodyStyle: 'padding-top: 0.5em;padding-left: 0.5em;',
				style: 'margin-bottom: 0.5em;',
				labelWidth: 300,
				items:[
					TextFieldDate,
					TextFieldNextDate
				]
			})
		);
		panel.doLayout();
	},
	createBSKGroup: function(item) {
		var BSKGroup_id = item.id;
		var BSKGroup_title = item.group;

		var panel = this.BSKRegistryData;

		var BSKGroup = new sw.Promed.Panel(
			{
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				bodyStyle: 'padding-top: 0.5em;',
				labelAlign: 'right',
				autoHeight: true,
				title: BSKGroup_title,
				collapsible: true,
				items: [{
					id: 'PBRW_BSKRegistryData_QuestionPanel_'+BSKGroup_id,
					border: false,
					layout: 'form',
					labelWidth: 300,
					style: 'margin-left: 0.5em;',
					items: []
				}]
			});
		
		panel.add(BSKGroup);
		panel.doLayout();
	},
	createCombobox: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		var arr = item.resultValue;
		var Store = new Ext.data.SimpleStore({
			fields: [
				{name: 'id', type: 'int'},
				{name: 'name', type: 'string'},
				{name: 'sign_id', type: 'int'},
				{name: 'sign_name', type: 'string'},
				{name: 'Diag_id', type: 'int'}
			],
			data: arr
		});
		//id ответа
		if (item.Diag_id)
			var value = item.Diag_id;
		else if (item.BSKRegistry_id && item.action !== 'add')
			var value = (item.BSKObservElementValues_id) ? item.BSKObservElementValues_id : item.BSKRegistryData_data;
		else var value = '';
		var ComboField = new Ext.form.ComboBox({
			fieldLabel: item.BSKObservElement_name,
			disabled: item.disabled,
			allowBlank: item.allowBlank,
			forSaveId: item.BSKObservElement_id,
			format_id: item.BSKObservElementFormat_id,
			unit_Name: item.Unit_Name,
			BSKRegistryData_id: item.BSKRegistryData_id,
			isEdit: item.isEdit,
			id: 'PBRW_Answer_' + item.BSKObservElement_id,//нельзя менять id т.к. используется в swGraceCalculator
			labelSeparator: '',
			width: 400,
			listWidth: 400,
			mode: 'local',
			//value: value,
			editable: false,
			triggerAction: 'all',
			enableKeyEvents: true,
			store: Store,
			displayField: 'name',
			valueField: 'id',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">'+
				'{name} '+ '&nbsp;' +
				'</div></tpl>'
			),
			listeners: {
				'render': function(combo){
					combo.setValue(value);
					//для Лекарственное лечение визуально отделить группы и Дозировка
					if (item.BSKObservElementGroup_id.inlist([23,28,42]) || item.BSKObservElement_id.inlist([116,121,126,131])) {
						panel.findById('PBRW_itemsQuestionPanel_'+item.BSKObservElement_id).getEl().setStyle('padding-top','1em');
					}
					if (item.BSKObservElement_id.inlist([115,120,125,130])) {
						panel.findById('PBRW_itemsQuestionPanel_'+item.BSKObservElement_id).getEl().setStyle('border-bottom','1px solid #b2b2b2');
					}
				}
			}
		});
		//Калькулятор Grace степень риска для артериальной гипертензии
		if (item.BSKObservElement_id == 269 /*&& item.disabled == false*/) {
			ComboField.fieldLabel = item.BSKObservElement_name+' <span style="font-weight: bold; color: blue; text-decoration: underline;">(Калькулятор Grace)</span>';
			//ComboField.id = 'PBRW_Answer_269';
			ComboField.addListener('expand',function () {
				var sisLeftHand;
				var sisRightHand;
				var sisHand;
				if (typeof panel.findById('PBRW_Answer_212') != 'undefined' && typeof panel.findById('PBRW_Answer_213') != 'undefined') {
					if (panel.findById('PBRW_Answer_212').getValue()>0 && panel.findById('PBRW_Answer_213').getValue()>0) {
						sisLeftHand = Number(panel.findById('PBRW_Answer_212').getValue());
						sisRightHand = Number(panel.findById('PBRW_Answer_213').getValue());
						sisHand = Math.ceil((sisLeftHand + sisRightHand)/2);
					}
				}
				var paramsWindow = {
					age: swGetPersonAge(form.findById('PBRW_infoPacient').getFieldValue('Person_Birthday'),panel.findById('PBRW_BSKRegistry_setDate').getValue()),
					sis: sisHand
				}
				getWnd('swGraceCalculator').show(paramsWindow);
			});
		}
		//Лекарственное лечение Защита от ввода дозировки без препарата
		if (item.BSKObservElementGroup_id.inlist([23,28,42])) {
			ComboField.addListener('change', function() {
				form.manageLLquestions(parseInt(item.BSKObservElement_id),parseInt(item.BSKObservElement_pid),1);
			});
		}
		//Скрининг Лекарственное лечение
		if (item.BSKObservElementGroup_id.inlist([10])) {
			ComboField.addListener('select', function() {
				//Управление группой комбобоксов
				//1 - принято накануне
				//2 - прописано на текущем
				//3 - дозировка накануне
				//4 - дозировка на текужем
				//5 - причина отмены
				switch (parseInt(item.BSKObservElement_id)) {
					//Статины
					case 111:
					case 112:
					case 113:
					case 114:
					case 115: 
						var paramsData = {
							ids : {
								1:'PBRW_Answer_111',
								2:'PBRW_Answer_112',
								3:'PBRW_Answer_113',
								4:'PBRW_Answer_114',
								5:'PBRW_Answer_115'
							}
						}
						break;
					//Эзетемиб 116,117,118,119,120,
					case 116:
					case 117:
					case 118:
					case 119:
					case 120: 
						var paramsData = {
							ids : {
								1:'PBRW_Answer_116',
								2:'PBRW_Answer_117',
								3:'PBRW_Answer_118',
								4:'PBRW_Answer_119',
								5:'PBRW_Answer_120'
							}
						}
						break;
					//Фибраты  121,122,123,124,125,
					case 121:
					case 122:
					case 123:
					case 124:
					case 125: 
						var paramsData = {
							ids : {
								1:'PBRW_Answer_121',
								2:'PBRW_Answer_122',
								3:'PBRW_Answer_123',
								4:'PBRW_Answer_124',
								5:'PBRW_Answer_125'
							}
						}
						break;
					//секверстанты 126,127,128,129,130,
					case 126:
					case 127:
					case 128:
					case 129:
					case 130: 
						var paramsData = {
							ids : {
								1:'PBRW_Answer_126',
								2:'PBRW_Answer_127',
								3:'PBRW_Answer_128',
								4:'PBRW_Answer_129',
								5:'PBRW_Answer_130'
							}
						}
						break;
					//никотиновая кислота 
					case 131:
					case 132:
					case 136:
					case 133:
					case 137: 
						var paramsData = {
							ids : {
								1:'PBRW_Answer_131',
								2:'PBRW_Answer_132',
								3:'PBRW_Answer_136',
								4:'PBRW_Answer_133',
								5:'PBRW_Answer_137'
							}
						}
						break;
					default: paramsData = {}
						break;
				}
			form.manageDrugSelect(paramsData, this);
			});
		}
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(ComboField);
	},
	createComboLeaveType: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		var ComboField = new sw.Promed.SwCommonSprCombo({
			comboSubject: 'LeaveType',
			fieldLabel: item.BSKObservElement_name,
			hiddenName: 'LeaveType_id_'+item.BSKObservElement_id,
			typeCode: 'int',
			width: 400,
			//xtype: 'swcommonsprcombo',
			listeners: {
				'render': function(combo) {
					combo.getStore().load();
				}
			}
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(ComboField);
	},
	createComboLpuSection: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		var ComboField = new sw.Promed.SwLpuSectionLiteCombo({
			fieldLabel: item.BSKObservElement_name,
			hiddenName: 'LpuSection_id_'+item.BSKObservElement_id,
			typeCode: 'int',
			width: 400,
			//xtype: 'swcommonsprcombo',
			listeners: {
				'focus': function(combo) {
					combo.getStore().load({
						params: 
						{
							Lpu_id: panel.findById('PBRW_Answer_304').getValue(),
							mode: 'combo'
						}
					});
				}
			}
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(ComboField);
	},
	createComboDiag: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		var ComboField = new sw.Promed.SwDiagCombo({
			fieldLabel: item.BSKObservElement_name,
			hiddenName: 'Diag_Code_'+item.BSKObservElement_id,
			listWidth: 650,
			valueField: 'Diag_Code',
			width: 400,
			//xtype: 'swdiagcombo',
			listeners: {
				'render': function(combo) {
					combo.getStore().load();
				}
			}
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(ComboField);
	},
	createCheckbox: function(item) {
		var panel = this.BSKRegistryData;
		var Checkbox = new Ext.form.Checkbox({
			forSaveId: item.BSKObservElement_id,
			isEdit: item.isEdit,
			boxLabel: (item.BSKRegistryData_data == 'Да'||item.BSKRegistryData_data == 'Да.')?'<span style="color:red!important">'+item.BSKObservElement_name+'</span>':item.BSKObservElement_name,
			fieldLabel: '',
			labelSeparator: '',
			readOnly: true,
			tabIndex : -1,
			style: 'padding: 5px;',
			checked: (item.BSKRegistryData_data == 'Да'||item.BSKRegistryData_data == 'Да.')?true:false
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(Checkbox);
	},
	createTextField: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		//маски ввода
		var ids = {
			//id текстовых полей, длина которых не должна превышать 3 символа
			3: [
				//Легочная гипертензия
				159, 160, 163, 164, 165, 166, 167, 168, 169, 170,
				//рост, вес, индекс массы тел, объём талии 
				107,108,109,142,143,208,209,211,318,319,321
			], 
			//id текстовых полей, длина которых не должна превышать 4 символа
			4: [
				//Легочная гипертензия
				156, 157, 158,
				//Артериальная гепиртензия
				235, 236, 237, 238, 239, 240, 242, 244, 245,
				//Ишемическая болезнь сердца
				392, 393, 394, 395, 396, 397
			],
			//id текстовых полей, длина которых не должна превышать 5 символов
			5: [
				//Скрининг 84
				50, 51, 54, 55, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 104, 106,
				//Легочная гипертензия
				144, 145, 146, 147, 148, 149, 152, 153, 154, 155, 186, 192, 194, 196, 198, 200, 202, 204,
				//Артериальная гепиртензия
				212, 213, 214, 215, 216, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 
				260, 261, 262, 263, 264, 265, 266, 267, 268,
				//Ишемическая болезнь сердца
				366, 368, 370, 372, 374, 376, 378, 380, 382
			], 
			//id текстовых полей, длина которых не должна превышать 6 символов
			6: [
				//Артериальная гепиртензия
				247, 248, 249
			]
		};
		var plugins;
		switch(Number(item.BSKObservElement_id)){
			//Артериальная гипертензия
			//Лаборатороная диагностика
			case 224:
			case 225:
			case 226:
			case 227:
			case 228:
			case 229:
			case 230:
			case 231: plugins = [new Ext.ux.InputTextMask('99.99', false)]; 
				break; 
			case 232: plugins = [new Ext.ux.InputTextMask('999.9', false)];
				break;
			case 233:
			case 234: plugins = [new Ext.ux.InputTextMask('9999', false)];
				break;
			
			//Ишемическая болезнь сердца
			//Лаборатороная диагностика
			case 333:
			case 334:
			case 335:
			case 336:
			case 337:
			case 338:
			case 339:
			case 340:
			case 341:
			case 385: plugins = [new Ext.ux.InputTextMask('99.99', false)];
				break;
			//Коронароангиография
			case 350:
			case 351:
			case 352:
			case 353:
			case 354:
			case 355:
			case 356:
			case 357:
			case 358:
			case 359:
			case 360:
			case 361:
			case 362:
			case 363:
			case 364: plugins = [new Ext.ux.InputTextMask('X[1-9]X9', false)];
				break;
			//Эхокардиография
			case 392:
			case 393:
			case 394:
			case 395:
			case 396:
			case 397: plugins = [new Ext.ux.InputTextMask('9.9', false)];
				break;
			default: plugins = ''; 
				break;
		}
		var TextField = new Ext.form.TextField({
			id: 'PBRW_Answer_'+item.BSKObservElement_id,
			forSaveId: item.BSKObservElement_id,
			format_id: item.BSKObservElementFormat_id,
			unit_Name: item.Unit_Name,
			BSKRegistryData_id: item.BSKRegistryData_id,
			isEdit: item.isEdit,
			fieldLabel: item.BSKObservElement_name,
			disabled: item.disabled,
			enableKeyEvents : true,
			allowBlank: item.allowBlank,
			width: (item.Unit_id)? 150 : 300,
			maskRe: /[\d.\/]/,
			plugins: plugins,
			value: item.BSKRegistryData_data,
			listeners: {
				'focus': function() {
					if (this.plugins != '') {
						var mask = String(this.plugins[0].viewMask);
						var str = this.getValue()
						var pointpos = mask.search(/\./);
						if (pointpos != -1) {
							var mas = str.split(/\./);
							if (mas[1] == undefined) mas[1] = '_';
							var masm = mask.split(/\./);
							while (masm[0].length > mas[0].length) {
								mas[0] = '_'+ mas[0];
							}
							while (masm[1].length > mas[1].length) {
								mas[1] = mas[1] + '_';
							}
							this.setValue(mas[0]+'.'+mas[1]);
						}
						else {
							if (str.length < mask.length) {
								while (str.length < mask.length) {
									str = str + '_'; 
								}
								this.setValue(str);
							}
							else {
								this.setValue(str);
							}
						}
						this.selectText(0,0);
					}
					//Лекарственное лечение Защита от ввода дозировки без препарата
					if (item.BSKObservElementGroup_id.inlist([23,28,42])) {
						form.manageLLquestions(parseInt(item.BSKObservElement_id),parseInt(item.BSKObservElement_pid),2);
					}
				},
				'blur': function(){
					if (this.plugins != '') {
						this.setValue(this.getValue().replace(/_/g, '').replace(/\.$/,''));
					}
				},
				'render': function(){
					//Ограничение длины полей до 5 символов
					if (parseInt(item.BSKObservElement_id).inlist(ids[5])) {
						this.getEl().dom.maxLength = 5;
					}
					else if (parseInt(item.BSKObservElement_id).inlist(ids[6])) {
						this.getEl().dom.maxLength = 6;
					}
					else if (parseInt(item.BSKObservElement_id).inlist(ids[4])) {
						this.getEl().dom.maxLength = 4;
					}
					else if (parseInt(item.BSKObservElement_id).inlist(ids[3])) {
						this.getEl().dom.maxLength = 3;
					}
					if (item.BSKObservElementGroup_id.inlist([23,28,42])) {
						panel.findById('PBRW_itemsQuestionPanel_'+item.BSKObservElement_id).getEl().setStyle('border-bottom','1px solid #b2b2b2');
					}
				}
			}
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(TextField);
	},
	createDateField: function(item) {
		var panel = this.BSKRegistryData;
		var DateField = new sw.Promed.SwDateField({
			forSaveId: item.BSKObservElement_id,
			isEdit: item.isEdit,
			fieldLabel: item.BSKObservElement_name,
			labelSeparator : ':',
			labelWidth : '70px',
			disabled: item.disabled,
			allowBlank: item.allowBlank,
			width: '100px',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			format: 'd.m.Y',
			value : item.BSKRegistryData_AnswerDT
		});
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(DateField);
	},
	createTextFieldInt: function(item) {
		var form = this;
		var panel = form.BSKRegistryData;
		var TextFieldInt = new Ext.form.NumberField({
			id: 'PBRW_Answer_'+item.BSKObservElement_id,
			forSaveId: item.BSKObservElement_id,
			format_id: item.BSKObservElementFormat_id,
			unit_Name: item.Unit_Name,
			BSKRegistryData_id: item.BSKRegistryData_id,
			isEdit: item.isEdit,
			fieldLabel: item.BSKObservElement_name,
			labelSeparator: ':',
			disabled: item.disabled,
			allowBlank: item.allowBlank,
			enableKeyEvents: true,
			allowDecimals: false,
			minValue: 0,
			maxValue: 999,
			width: '100px',
			validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
			value: parseInt(item.BSKRegistryData_AnswerInt)
		});
		//пересчёт ИМТ на лету
		if (parseInt(item.BSKObservElement_id).inlist([107,108,142,143,208,209,318,319])) {
			TextFieldInt.addListener('keyup',function () {
				form.getIMT(item);
			});
		}
		panel.findById('PBRW_ElementAnswer_'+item.BSKObservElement_id).add(TextFieldInt);
	},
	createQuestion: function(item) {
		var panel = this.BSKRegistryData;
		var form = this;
		var ElementFormat = item.BSKObservElementFormat_id;
		if (ElementFormat==9) 
			var labelWidth = 10;
		else 
			var labelWidth = 300;
		var QuestionPanel = {
			id : 'PBRW_itemsQuestionPanel_'+item.BSKObservElement_id,
			border: false,
			layout: 'column',
			width: 800,
			items : [
				{
					id: 'PBRW_ElementAnswer_'+item.BSKObservElement_id,
					border: false,
					layout: 'form',
					labelWidth: labelWidth,
					labelAlign: 'right',
					items: []
				},{
					id: 'PBRW_ElementUnit_'+item.BSKObservElement_id,
					border: false,
					layout: 'form',
					style: 'margin-left: 1px;',
					items: []
				},{
					id: 'PBRW_ElementTime_'+item.BSKObservElement_id,
					border: false,
					layout: 'form',
					style: 'margin-left: 1px;',
					items: []
				}
			]
		
		};
		panel.findById('PBRW_BSKRegistryData_QuestionPanel_'+item.BSKObservElementGroup_id).add(QuestionPanel);
		panel.doLayout();
		switch(true) {
			case (ElementFormat==8):  form.createCombobox(item);     break;
			case (ElementFormat==9):  form.createCheckbox(item);     break;
			case (ElementFormat==13): form.createDateField(item);    break;
			case (ElementFormat==11): form.createTextFieldInt(item); break;
			//элементы из sw Combo
			case (ElementFormat=='LeaveType'):  form.createComboLeaveType(item);   break;
			case (ElementFormat=='Diag'):       form.createComboDiag(item);             break;
			case (ElementFormat=='LpuSection'): form.createComboLpuSection(item); break;
			default: 
				if (!item.BSKRegistryData_data) {
					item.BSKRegistryData_data = (item.BSKRegistryData_AnswerFloat)? item.BSKRegistryData_AnswerFloat : item.BSKRegistryData_AnswerText;
				}
				form.createTextField(item);
				break;
		}

		if(item.Unit_id) {
			var UnitField = new Ext.form.TextField({
				disabled : true,
				tabIndex : -1,
				width : 80,
				hideLabel: true,
				value : item.Unit_Name
			});
			panel.findById('PBRW_ElementUnit_'+item.BSKObservElement_id).add(UnitField);
		}
		if(item.BSKObservElementFormat_id == 13 && (item.BSKRegistryData_dataTime || form.formParams.BSKRegistry_id == null)) {
			var TimeField = {
				disabled : item.action == 'add'? false:true,
				width : 80,
				hideLabel: true,
				forSaveId: 'AnswerTime_'+item.BSKObservElement_id,
				value : item.BSKRegistryData_dataTime,
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				xtype: 'swtimefield'
			};
			panel.findById('PBRW_ElementTime_'+item.BSKObservElement_id).add(TimeField);
		}
		panel.doLayout();
	},
	showMsg : function(msg){
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING,
			width : 600,
			msg: msg,
			title: ERR_INVFIELDS_TIT
		});
	},
	showMsgLostData: function(params){
		var form = this;
		sw.swMsg.show({
			id: 'PBRW_Question_LostData',
			buttons: Ext.Msg.YESNO,
			width : 600,
			fn: function(buttonId, text, obj) {
				if ( buttonId != 'yes' ) {
					Ext.getCmp('PBRW_saveBskDataButton').setDisabled(false);
					switch (params.clickAction) {
						case 'onTreePanelClick':
							if (form.formParams.node) {
								form.MorbusType_id = form.formParams.MorbusType_id;
								form.TreePanel.selModel.select(form.formParams.node);
								}
							return false;
							break;
						default:
							form.findById('PBRW_tabpanelBSK').setActiveTab(form.findById('PBRW_infotab'));
							return false;
							break;
					}
				} else {
					switch (params.clickAction) {
						case 'addBskDataButton':
							form.loadBskRegistryData(form.formParams);
							break;
						case 'onTreePanelClick':
							form.formParams.action = 'load';
							form.formParams.node = params.node;
							form.clickToPN = 0;
							form.loadBskRegistryData(form.formParams);
							Ext.getCmp('PBRW_saveBskDataButton').setDisabled(true);
							break;
						case 'addBskObjectButton':
							getWnd('swBSKSelectWindow').show(params);
							break;
						case 'closeBskDataButton':
							form.MorbusType_id = 0;
							form.hide();
							break;
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Все несохранённые данные будут утеряны, продолжить?'),
			title: langs('Вопрос')
		}); 
	},
	getIMT: function(item) {
		var info = this.BSKRegistryData;
		var fields = [];
		switch (item.BSKObject_SysNick) {
			//Скрининг
			case 'screening': 
				fields = [107,108,110];
				break;
			//Лёгочная гипертензия
			case 'lung_hypert': 
				fields = [142,143,172];
				break;
			//Артериальная гипертензия
			case 'Arter_hypert': 
				fields = [208,209,210];
				break;
			case 'ibs': 
				fields = [318,319,320];
				break;
		}
		var height = info.findById('PBRW_Answer_' + fields[0]);
		var weight = info.findById('PBRW_Answer_' + fields[1]);
		var imt = info.findById('PBRW_Answer_' + fields[2]);
		if(height && weight && imt){
			var height = parseInt(height.getValue())/100;
			var weight = parseInt(weight.getValue());
			var indexBody = (weight / (height*height)).toFixed(1);
			imt.setValue(indexBody);
			if(imt.getValue() == 'NaN'){
				imt.setValue(0);
			}
		}

	},
	manageLLquestions: function(id_question, pid_question, isDose) {
		var panel = this.BSKRegistryData;
		if (id_question != false && pid_question != false) {
			switch (isDose) {
				case 1:
					var question = panel.findById('PBRW_Answer_' + id_question).getRawValue();
					var dose = panel.findById('PBRW_Answer_' + pid_question);
					if (question == 'Не принимает' || question == '' || question == 'Нет сведений') {
						question == ''? dose.setValue('') : dose.setValue('-');
						question == ''? dose.setDisabled(false) : dose.setDisabled(true);
						dose.allowBlank = true;
					} else {
						dose.setValue('');
						dose.setDisabled(false);
						dose.allowBlank = false;
					}
					dose.validate();
					break;
				case 2:
					//дозировка без ЛС
					var question = panel.findById('PBRW_Answer_' + pid_question).getRawValue();
					var dose = panel.findById('PBRW_Answer_' + id_question);
					if (question == '') {
						this.showMsg(langs('Необходимо ввести препарат'));
						dose.setValue('');
					}
					break;
				default:
					break;
			}
		}
	},
	//В лекарственном лечении организованы зависимые списки
	manageDrugSelect: function(params, combo){
		var form = this;
		var panel = form.BSKRegistryData;
		//убираем значение 'Не принимает' из дозировок, работает не очень правильно здесь, додумать
		if (!panel.findById(params.ids[3]).getStore().isFiltered()) {
			panel.findById(params.ids[1]).addListener('beforeselect', function() {
				panel.findById(params.ids[3]).getStore().filterBy(function (rec) {
					return (!rec.get('name').inlist(['Не принимает']));
				})
			});
		}
		if (!panel.findById(params.ids[4]).getStore().isFiltered()) {
			panel.findById(params.ids[2]).addListener('beforeselect', function() {
				panel.findById(params.ids[4]).getStore().filterBy(function (rec) {
					return (!rec.get('name').inlist(['Не принимает']));
				})
			});
		}
		//бликировка дозировки при ['Нет сведений', 'Не принимает', 'Не назначена']
		switch (combo.id) {
			case params.ids[1]:
				if (panel.findById(params.ids[1]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена'])) {
					panel.findById(params.ids[3]).getStore().clearFilter();
					panel.findById(params.ids[3]).setDisabled(true);
					var index = panel.findById(params.ids[3]).store.find('name',panel.findById(params.ids[1]).getRawValue());
					if (index == -1)
						index = panel.findById(params.ids[3]).store.find('name','Не принимает');
					var id = panel.findById(params.ids[3]).store.getAt(index).data.id;
					panel.findById(params.ids[3]).setValue(id);
					if (panel.findById(params.ids[1]).getRawValue().inlist(['Не принимает', 'Не назначена']) && panel.findById(params.ids[2]).getRawValue().inlist(['Не принимает', 'Не назначена'])) {
						var index = panel.findById(params.ids[5]).store.find('name','Не назначено');
						var id = panel.findById(params.ids[5]).store.getAt(index).data.id;
						panel.findById(params.ids[5]).setDisabled(true);
						panel.findById(params.ids[5]).setValue(id);
					} else {
						panel.findById(params.ids[5]).clearValue();
						panel.findById(params.ids[5]).setDisabled(false);
					}
				} else {
					panel.findById(params.ids[3]).clearValue();
					panel.findById(params.ids[3]).setDisabled(false);
					panel.findById(params.ids[5]).clearValue();
					panel.findById(params.ids[5]).setDisabled(false);
				}
				break;
			case params.ids[2]:
				if (panel.findById(params.ids[2]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена'])) {
					panel.findById(params.ids[4]).getStore().clearFilter();
					var index = panel.findById(params.ids[4]).store.find('name',panel.findById(params.ids[2]).getRawValue());
					if (index == -1)
						index = panel.findById(params.ids[4]).store.find('name','Не принимает');
					var id = panel.findById(params.ids[4]).store.getAt(index).data.id;
					panel.findById(params.ids[4]).setDisabled(true);
					panel.findById(params.ids[4]).setValue(id);
					if (panel.findById(params.ids[1]).getRawValue().inlist(['Не принимает', 'Не назначена']) && panel.findById(params.ids[2]).getRawValue().inlist(['Не принимает', 'Не назначена'])) {
						var index = panel.findById(params.ids[5]).store.find('name','Не назначено');
						var id = panel.findById(params.ids[5]).store.getAt(index).data.id;
						panel.findById(params.ids[5]).setDisabled(true);
						panel.findById(params.ids[5]).setValue(id);
					} else {
						panel.findById(params.ids[5]).clearValue();
						panel.findById(params.ids[5]).setDisabled(false);
					}
				} else {
					panel.findById(params.ids[4]).clearValue();
					panel.findById(params.ids[4]).setDisabled(false);
					panel.findById(params.ids[5]).clearValue();
					panel.findById(params.ids[5]).setDisabled(false);
				}
				break;
			case params.ids[3]:
				if (panel.findById(params.ids[1]).getValue() == ''
					||(panel.findById(params.ids[1]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена'])
					&& !panel.findById(params.ids[3]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена']))) {
						//при попытке указать дозировку накануне приёма
						form.showMsg(langs('Укажите группу препаратов, принятую накануне текущего осмотра!'));
						panel.findById(params.ids[3]).clearValue();
					}
				break;
			case params.ids[4]:
				if (panel.findById(params.ids[2]).getValue() == ''
					||(panel.findById(params.ids[2]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена'])
					&& !panel.findById(params.ids[4]).getRawValue().inlist(['Нет сведений', 'Не принимает', 'Не назначена']))) {
						//при попытке указать дозировку на текущем осмотре
						form.showMsg(langs('Укажите группу препаратов, рекомендованную на текущем визите!'));
						panel.findById(params.ids[4]).clearValue();
					}
				break;
			case params.ids[5]:
				break;
		}
	},
	loadPersonValuesDiag(dataQuestions,resultValue,groups) {
		var form = this;
		var PersonSex_id = form.findById('PBRW_infoPacient').getFieldValue('Sex_id');
		var PersonAge = swGetPersonAge(form.findById('PBRW_infoPacient').getFieldValue('Person_Birthday'),form.findById('PBRW_BSKRegistry_setDate').getValue());
		var PrivilegeType_Name = form.findById('PBRW_infoPacient').getFieldValue('PrivilegeType_Name');
		var params = {
			'Person_id' : form.findById('PBRW_infoPacient').personId,
			'MorbusType_id': dataQuestions[0].MorbusType_id
		};

		Ext.Ajax.request({
			url: '/?c=BSK_RegisterData&m=getPersonElementValuesDiag',
			params,
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					form.formParams.BSKRegistry_id = null;
					dataQuestions.forEach(function(item, i) {
						//проверка вопроса на пол-возраст
						if ((item.Sex_id == 3 || item.Sex_id == PersonSex_id) && (PersonAge >= item.minAge && PersonAge <= item.maxAge)) {
							var ElementGroup = item.BSKObservElementGroup_id;
							if (ElementGroup == '29') { //Общую информацию по ОКС в первую найденную группу
								ElementGroup = groups[0];
								item.BSKObservElementGroup_id = groups[0];
							}
							if (item.BSKObservElement_id == '302' && item.BSKObservElementGroup_id == '44') {
								//убираем 302 вопрос ЧКВ из скорой, актуально для отображения старых анкет, т.к. в этот вопрос шел ответ из скорой ZonaCHKV = 'нет'
							} else if (ElementGroup.inlist(groups)) {
								//удаляем старые значения кроме рост, вес, индекс массы тел, объём талии 
								if (!item.BSKObservElement_id.inlist(['107','108','109','110','142','143','172','208','209','211','210','318','319','321','320'])) {
									item.BSKRegistryData_AnswerDT = null;
									item.BSKRegistryData_AnswerInt = null;
									item.BSKRegistryData_AnswerText = null;
									item.BSKRegistryData_data = null;
									item.BSKRegistryData_dataTime = null;
									item.BSKRegistryData_AnswerFloat = null;
								}
								//значения для комбо
								item.resultValue = (resultValue[item.BSKObservElement_id])? resultValue[item.BSKObservElement_id] : [];
								item.action = 'add';
								//данные из БД, Индекс массы тела недоступны
								if (item.isEdit == '1') item.disabled = true;
								else item.disabled = false;
								if (item.BSKObservElement_stage == 1) item.allowBlank = false;
								else item.allowBlank = true;
								//Пол Возраст Инвалидность, устанавливать значения получается только напрямую
								if (item.BSKObservElement_id.inlist(['24','173','205','315'])) {
									item.BSKRegistryData_data = form.findById('PBRW_infoPacient').getFieldValue('Sex_Name');
								}
								if (item.BSKObservElement_id.inlist(['25','174','206','316'])) item.BSKRegistryData_data = PersonAge;
								if (item.BSKObservElement_id.inlist(['26','175','207','317'])) {
									item.BSKRegistryData_data = PrivilegeType_Name? PrivilegeType_Name : 'Нет';
								}
								//диагнозы
								if (result[item.BSKObservElement_id]) {
									item.BSKRegistryData_data = result[item.BSKObservElement_id].Diag_FullName;
									item.Diag_id = result[item.BSKObservElement_id].BSKObservElementValues_id;
									item.disabled = true;
									//добавить установленный диагноз в список выбора
									if (result[item.BSKObservElement_id].Diag_id) {
										var Diag = [result[item.BSKObservElement_id].BSKObservElementValues_id,result[item.BSKObservElement_id].Diag_FullName];
										item.resultValue.push(Diag);
									}
								}
								//элементы из sw Combo
								if (resultValue[item.BSKObservElement_id] && resultValue[item.BSKObservElement_id].length == 1) {
									if (resultValue[item.BSKObservElement_id][0][5])
										item.BSKObservElementFormat_id = resultValue[item.BSKObservElement_id][0][1];
								}
								form.createQuestion(item);
							}
						}
					});
					form.getLoadMask().hide();
				}
			},
			failure: function() {
				form.getLoadMask().hide();
			}
		});
	},
	loadBskElementValues: function(result) {
		var form = this;
		var panel = form.BSKRegistryData;
		var dataQuestions = result.questions;
		var params = {
			'MorbusType_id': result.questions[0].MorbusType_id
		};
		Ext.Ajax.request({
			url: '/?c=BSK_RegisterData&m=getBSKRegistryElementValues',
			params,
			callback: function(options, success, response) {
				if (success) {
					var resultValue = Ext.util.JSON.decode(response.responseText);
					var groups = []; 
					for(var k in result.groups) {
						if (result.groups[k].id !== '29') {
							form.createBSKGroup(result.groups[k]);
							groups.push(result.groups[k].id);
						}
					}

					if(result.action == 'add' && !params.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) && form.formParams.Person_IsDead !== '2') {
						form.loadPersonValuesDiag(dataQuestions,resultValue,groups);
					} else {
						//Изменить — Кнопка активна в течение 30 дней от даты анкетирования
						if (!params.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) && form.formParams.Person_IsDead !== '2') {
							var dateRegister = panel.findById('PBRW_BSKRegistry_setDate').getValue();
							var now = new Date();
							var timeDiff = Math.abs(dateRegister.getTime() - now.getTime());
							var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24))-1;
							Ext.getCmp('PBRW_editBskDataButton').setDisabled((diffDays <= 30)?false:true);
						} else Ext.getCmp('PBRW_editBskDataButton').setDisabled(true);
						var PersonSex_id = form.findById('PBRW_infoPacient').getFieldValue('Sex_id');
						var PersonAge = swGetPersonAge(form.findById('PBRW_infoPacient').getFieldValue('Person_Birthday'),panel.findById('PBRW_BSKRegistry_setDate').getValue());
						var PrivilegeType_Name = form.findById('PBRW_infoPacient').getFieldValue('PrivilegeType_Name');
						dataQuestions.forEach(function(item, i) {
							var ElementGroup = item.BSKObservElementGroup_id;
							if (ElementGroup == '29') { //Общую информацию по ОКС в первую найденную группу
								ElementGroup = groups[0];
								item.BSKObservElementGroup_id = groups[0];
							}
							if (item.BSKObservElement_id == '302' && item.BSKObservElementGroup_id == '44') {
								//убираем 302 вопрос ЧКВ из скорой, актуально для отображения старых анкет, т.к. в этот вопрос шел ответ из скорой ZonaCHKV = 'нет'
							} else if (ElementGroup && ElementGroup.inlist(groups)) {
								//проверка на пол-возраст
								if (((item.Sex_id == 3 || item.Sex_id == PersonSex_id) && (PersonAge >= item.minAge && PersonAge <= item.maxAge) || item.MorbusType_id == '19')) {
									//нередактируемые данные если не сохранились
									if (!item.BSKRegistryData_data) {
										item.BSKRegistryData_data = '';
										//Пол Возраст Инвалидность, устанавливать значения получается только напрямую
										if (item.BSKObservElement_id.inlist(['24','173','205','315'])) {
											item.BSKRegistryData_data = form.findById('PBRW_infoPacient').getFieldValue('Sex_Name');
										}
										if (item.BSKObservElement_id.inlist(['25','174','206','316'])) item.BSKRegistryData_data = PersonAge;
										if (item.BSKObservElement_id.inlist(['26','175','207','317'])) {
											item.BSKRegistryData_data = PrivilegeType_Name? PrivilegeType_Name : 'Нет';
										}
										if (item.noDiag) item.BSKRegistryData_data = item.noDiag;
									}
									item.resultValue = (resultValue[item.BSKObservElement_id])? resultValue[item.BSKObservElement_id] : [];
									item.disabled = true;
									if (item.BSKObservElement_stage == 1) item.allowBlank = false;
									else item.allowBlank = true;
									form.createQuestion(item);
								}
							}
						});
						form.getLoadMask().hide();
						//Сроки заполнения анкет в предметах наблюдения при открытии последней анкеты 
						if (form.TreePanel.getSelectionModel().getSelectedNode().attributes.isLast == 2 && !params.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) && form.formParams.Person_IsDead !== '2') {
							var today = new Date();
							var BSKRegistry_setDate = new Date(form.TreePanel.getSelectionModel().getSelectedNode().attributes.BSKRegistry_setDate);
							var riskGroup = form.TreePanel.getSelectionModel().getSelectedNode().attributes.riskGroup;
							var diffDays = (form.TreePanel.getSelectionModel().getSelectedNode().attributes.BSKRegistry_setDate)?Math.floor((today-BSKRegistry_setDate)/(1000*60*60*24)):0;
							if (parseInt(form.TreePanel.getSelectionModel().getSelectedNode().attributes.MorbusType_id) == 84) {
								switch (parseInt(riskGroup)) {
									case 1:  var limit = 18*30; break;
									case 2:  var limit = 12*30; break;
									case 3:  var limit = 6*30;  break;
									default: var limit = 18*30; break;
								}
							} else var limit = 6*30;
							if (limit < diffDays) {
								sw.swMsg.show({
									id: 'PBRW_Question_limit',
									width: 500,
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId != 'yes' ) {
											return;
										}
										else {
											var params = {
												action: 'add',
												clickAction: 'addBskDataButton'
											};
											form.formParams.action = 'add';
											form.loadBskRegistryData(params);
											form.clickToPN = 1;
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Последняя анкета на данного пациента больше не актуальна, создать новую анкету?'),
									title: langs('Вопрос')
								}); 
							}
						}
					}
				}
			},
			failure: function() {
				form.getLoadMask().hide();
			}
		});

	},
	//для ПН без анкет - ХСН, пороки сердца
	getBSKObjectWithoutAnket : function(params){
		var form = this;
		//81,82,83,84,85 Инвалиды
		var PrivilegeType_id = (form.findById('PBRW_infoPacient').getFieldValue('PrivilegeType_id'))? form.findById('PBRW_infoPacient').getFieldValue('PrivilegeType_id'):0;
		var PrivilegeType_Name = PrivilegeType_id.inlist([81,82,83,84,85])? form.findById('PBRW_infoPacient').getFieldValue('PrivilegeType_Name'):'Нет';
		var PersonSex = form.findById('PBRW_infoPacient').getFieldValue('Sex_Name');
		var PersonAge = form.findById('PBRW_infoPacient').getFieldValue('Person_Age');
		var HSNhidden = parseInt(params.MorbusType_id)==110? false:true;
		form.formParams.MorbusType_id = params.MorbusType_id;
		form.formParams.BSKRegistry_id = null;

		Ext.Ajax.request({
			url: '/?c=BSK_RegisterData&m=getBSKObjectWithoutAnket',
			params,
			callback: function(options, success, response) {
				
				if (success === true) {
					var result = Ext.util.JSON.decode(response.responseText);
					var TextFieldDate = new sw.Promed.SwDateField({
						fieldLabel: langs('Дата включения в предмет наблюдения'),
						labelSeparator : ':',
						labelWidth : '270px',
						disabled: true,
						width: '100px',
						plugins: [
							new Ext.ux.InputTextMask('99.99.9999', false)
						],
						format: 'd.m.Y',
						value : result[0].PersonRegister_setDate
					});

					var BSKEvnGrid = new sw.Promed.ViewFrame({
						border: true,
						id: 'PBRW_BSKEvnGrid',
						dataUrl: '/?c=BSK_RegisterData&m=loadBSKEvnGrid',
						autoLoadData: false,
						focusOnFirstLoad: false,
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 150,
						region: 'center',
						pageSize: 100,
						contextmenu: false,
						paging: false,
						toolbar: false,
						border: false,
						stringfields: [
							{name: 'Evn_id', type: 'int', header: 'ID'},
							{name: 'EvnDiagPS_setDate', header: 'Дата', type: 'date', width:100, format: 'd.m.Y'},
							{name: 'Diag_FullName', header: 'Диагноз', type: 'string', id: 'autoexpand'},
							{name: 'HSNStage_Name', header: 'Стадия ХСН', type: 'string', hidden: HSNhidden},
							{name: 'HSNFuncClass_Name', header: 'Функциональный класс', type: 'string', width:160, hidden: HSNhidden},
							{name: 'Lpu_Nick', header: 'Медицинская организация', width:200},
							{name: 'LpuSection_Name', header: 'Отделение', type: 'string', width:160},
							{name: 'Person_Fio', header: 'Врач', width:160}
						],
						onRowSelect: function(){
							this.setReadOnly(true);
						}
					}); 
					
					form.BSKRegistryData.add(
						new Ext.form.FormPanel({
							autoHeight: true,
							title: langs('Предмет наблюдения: ')+result[0].MorbusType_Name,
							collapsible: false,
							labelAlign: 'right',
							border: true,
							bodyStyle: 'padding-top: 0.5em;padding-left: 0.5em;',
							style: 'margin-bottom: 0.5em;',
							labelWidth: 300,
							items:[TextFieldDate]
						}),
						new sw.Promed.Panel({
							layout: 'form',
							style: 'margin-bottom: 0.5em;',
							bodyStyle: 'padding-top: 0.5em;',
							autoHeight: true,
							title: langs('Общие сведения'),
							collapsible: true,
							labelAlign: 'right',
							items:{
								layout: 'form',
								border: false,
								labelWidth: 300,
								style: 'margin-left: 0.5em;',
								items:[
									{
										fieldLabel: langs('Пол пациента'),
										disabled: true,
										width: 300,
										xtype: 'textfield',
										value: PersonSex
									},{
										fieldLabel: langs('Возраст пациента'),
										disabled: true,
										width: 300,
										xtype: 'textfield',
										value: PersonAge
									},{
										fieldLabel: langs('Инвалидность'),
										disabled: true,
										width: 300,
										xtype: 'textfield',
										value: PrivilegeType_Name
									}
								]
							}
						}),
						new sw.Promed.Panel({
							layout: 'form',
							autoHeight: true,
							title: result[0].MorbusType_Name,
							collapsible: true,
							items:{
								xtype: 'panel',
								layout: 'fit',
								border: false,
								autoScroll : true,
								items:[BSKEvnGrid]
							}
						})
					);
					form.getLoadMask().hide();

					form.BSKRegistryData.doLayout();
					BSKEvnGrid.getGrid().getStore().baseParams = {
						MorbusType_id : params.MorbusType_id,
						Person_id : params.Person_id
					}
					BSKEvnGrid.getGrid().getStore().load({
						MorbusType_id : params.MorbusType_id,
						Person_id : params.Person_id
					});
				}
			},
			failure: function() {
				form.getLoadMask().hide();
			}
		});
	},
	loadBskRegistryData: function(params) {
		//var Person_id = node.attributes.loader.baseParams.Person_id;
		var form = this;
		form.BSKRegistryData.removeAll();
		form.getLoadMask("Получение данных по пациенту...").show();
		switch (params.action) {
			case 'load':
			case 'edit':
				form.formParams.node = params.node;
				var params = {
					MorbusType_id: params.node.attributes.MorbusType_id,
					BSKRegistry_id: params.node.attributes.BSKRegistry_id,
					Person_id: params.node.attributes.Person_id,
					action: params.action
				};
				break;
			case 'add':
				var params = {
					MorbusType_id: form.TreePanel.getSelectionModel().getSelectedNode().attributes.MorbusType_id,
					BSKRegistry_id: null,
					Person_id: form.findById('PBRW_infoPacient').personId,
					action: params.action
				};
				Ext.getCmp('PBRW_saveBskDataButton').setDisabled(false);
				Ext.getCmp('PBRW_editBskDataButton').setDisabled(true);
				break;
			default:
				return false;
				break;
		}
		if (parseInt(params.MorbusType_id).inlist(form.MorbusTypeNoSaveAnket)) {
			form.findById('PBRW_infotab').getTopToolbar().setDisabled(true);
			form.findById('PBRW_compare').setDisabled(true);
			form.findById('PBRW_recommendations').setDisabled(true);
		} else if (parseInt(params.MorbusType_id) == 84) {
			Ext.getCmp('PBRW_printBskDataButton').setDisabled(false);
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(false);
			form.findById('PBRW_compare').setDisabled(false);
			form.findById('PBRW_recommendations').setDisabled(false);
		} else {
			Ext.getCmp('PBRW_printBskDataButton').setDisabled(true);
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(false);
			form.findById('PBRW_compare').setDisabled(true);
			form.findById('PBRW_recommendations').setDisabled(false);
		}
		if (form.formParams.Person_IsDead == '2') form.findById('PBRW_infotab').getTopToolbar().setDisabled(true);
		//для ПН без анкет - ХСН, пороки сердца
		if (parseInt(params.MorbusType_id).inlist([110,111,112])) {
			form.getBSKObjectWithoutAnket(params);
		} else {
			Ext.Ajax.request({
				url: '/?c=BSK_RegisterData&m=getBSKRegistryFormTemplate',
				params: {
					MorbusType_id: params.MorbusType_id,
					BSKRegistry_id : params.BSKRegistry_id,
					Person_id : params.Person_id
				},
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success==false) {
							form.getLoadMask().hide();
						} else {
							//установление признака просмотра анкеты
							if (result.BSKRegistry_id && result.BSKRegistry_isBrowsed == '1') {
								Ext.Ajax.request({
									url: '/?c=BSK_RegisterData&m=setIsBrowsed',
									params: {
										'BSKRegistry_id': result.BSKRegistry_id
									},
									callback: function(options, success, response) {
										if (success) {	
											var obj = Ext.util.JSON.decode(response.responseText);
										} else {
											sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при установлении признака просмотра анкеты'));
										}
									}
								});
							}
							//основные параметры для сохранения
							form.formParams.MorbusType_id = result.questions[0].MorbusType_id;
							form.formParams.PersonRegister_id = result.questions[0].PersonRegister_id;
							form.formParams.BSKRegistryFormTemplate_id = result.questions[0].BSKRegistryFormTemplate_id;
							form.formParams.BSKRegistry_id = result.questions[0].BSKRegistry_id;

							if (!result.BSKRegistry_id && !params.MorbusType_id.inlist(form.MorbusTypeNoSaveAnket) && form.formParams.Person_IsDead !== '2') {
								params.action = 'add';
								form.formParams.action = 'add';
								form.clickToPN = 1;
								Ext.getCmp('PBRW_saveBskDataButton').setDisabled(false);
								Ext.getCmp('PBRW_editBskDataButton').setDisabled(true);
							}

							if(params.action == 'add') {
								result.disabled = false;
								result.BSKRegistry_setDateFormat = new Date();
							} else result.disabled = true;

							form.createBSKDate(result);
							result.action = params.action;
							form.loadBskElementValues(result);
						}
					} else form.getLoadMask().hide();
				},
				failure: function() {
					form.getLoadMask().hide();
				}
			});
		}

	},
	saveBskRegistryData: function() {
		var form = this;
		var answers = [];
		var allowBlankNo = 0;
		var contentError = '<ol>';
		form.BSKRegistryData.findBy(function(el) {
			if (el.forSaveId) {
				var value = el.getValue();
				var valueName = el.getRawValue();
				if ((!el.allowBlank && value == '') || !el.isValid()) {
					allowBlankNo++;
					contentError = contentError + '<li>- ' + el.fieldLabel + '</li>';
				}
				if (el.forSaveId !== 'PBRW_BSKRegistry_setDate' && el.forSaveId !== 'PBRW_BSKRegistry_nextDate') {
					var sign_id = '';
					var sign_name = '';
					if (el.format_id == 8) {
						sign_id = el.getFieldValue('sign_id');
						sign_name = el.getFieldValue('sign_name');
					}
					sign_id = sign_id ? sign_id : '';
					sign_name = sign_name ? sign_name : '';
					answers.push([el.forSaveId, value, valueName, el.format_id, el.unit_Name, sign_id, sign_name, el.BSKRegistryData_id]);
				}
			}
		});
		if (allowBlankNo > 0) {
			form.showMsg('<b>Необходимо ответить на обязательные вопросы:</b><p>&nbsp;</p><br/>'+contentError+'</ol>');
			return false;
		}
		var params = form.formParams;
		if (answers.length > 0) {
			params.QuestionAnswer = Ext.util.JSON.encode(answers);
			params.BSKRegistry_setDate = form.findById('PBRW_BSKRegistry_setDate').getValue();
			params.BSKRegistry_nextDate = parseInt(form.MorbusType_id) == 113? null:form.findById('PBRW_BSKRegistry_nextDate').getValue();
			params.Sex_id = form.findById('PBRW_infoPacient').getFieldValue('Sex_id');
			params.Age = swGetPersonAge(form.findById('PBRW_infoPacient').getFieldValue('Person_Birthday'),form.findById('PBRW_BSKRegistry_setDate').getValue());
			form.getLoadMask("Подождите, идет сохранение...").show();
			if (params.action == 'add' || params.action == 'edit') {
				Ext.Ajax.request({
					url: '/?c=BSK_RegisterData&m=saveBSKRegistry',
					method: 'POST',
					params: params,
					failure: function() {
						form.getLoadMask().hide();
						form.showMsg(langs('При сохранении данных возникли ошибки!'));
					},
					success: function(response, opts) {
						var responseText = Ext.util.JSON.decode(response.responseText);
						if (responseText[0].success !== true) {
							form.getLoadMask().hide();
							form.showMsg(langs('Не удалось сохранить данные регистра! ')+responseText[0].Error_Msg);
						} else {
							form.clickToPN = 0;
							params.action = 'load';
							if (responseText[0].BSKRegistry_id) var BSKRegistry_id = responseText[0].BSKRegistry_id;
							else if (params.BSKRegistry_id) var BSKRegistry_id = params.BSKRegistry_id;
							else var BSKRegistry_id = null;
							form.formParams.node.attributes.BSKRegistry_id = BSKRegistry_id;
							form.TreePanel.selModel.selNode = null;
							var root = form.TreePanel.getRootNode();
							form.TreePanel.getLoader().baseParams.Person_id = params.Person_id;
							form.TreePanel.getRootNode().loaded = true;
							form.TreePanel.getRootNode().loading = false;
							form.TreePanel.getLoader().load(root,function(){
								form.TreePanel.getRootNode().expand(false);
								var node = form.TreePanel.getRootNode().findChild('MorbusType_id',form.formParams.MorbusType_id);
								node.expand(true, true, function(n){
									var anketNode = node.findChild('BSKRegistry_id',BSKRegistry_id);
									if (anketNode) {
										anketNode.select();
										var MorbusType_id = anketNode.attributes.MorbusType_id;
										if (!MorbusType_id) {
											return;
										}
										form.MorbusType_id = MorbusType_id;
										form.getLoadMask().hide();
										form.onTreePanelClick(anketNode);
									}
								});
							});
							Ext.getCmp('PBRW_saveBskDataButton').setDisabled(true);
						}
					}
				});
			} else {
				form.getLoadMask().hide();
				form.showMsg(langs('Не определено действие для анкеты!'));
			}
		} else {
			form.showMsg(langs('Нет данных для сохранения!'));
		}
	},
	editBskRegistryData: function() {
		var form = this;
		Ext.getCmp('PBRW_saveBskDataButton').setDisabled(false);
		Ext.getCmp('PBRW_addBskDataButton').setDisabled(true);
		Ext.getCmp('PBRW_editBskDataButton').setDisabled(true);
		form.BSKRegistryData.findBy(function(el) {
			if (el.forSaveId) {
				if (parseInt(el.isEdit) == 1) {
					el.setDisabled(true);
				} else {
					el.setDisabled(false);
				}
			}
		});
	},
	onTreePanelClick: function(node) {
		var form = this;
		var params = {
			action: (form.formParams.action)?form.formParams.action:'load',
			node: node,
			clickAction: 'onTreePanelClick'
		};
		if (form.clickToPN !==0) {
			form.showMsgLostData(params);
		} else {
			form.loadBskRegistryData(params);
		}
	},
	onNodeSelect : function(selModel,node){
		var form = this;
		var MorbusType_id = form.TreePanel.getSelectionModel().getSelectedNode().attributes.MorbusType_id;
		Ext.getCmp('PBRW_editBskDataButton').setDisabled(true);
		if (parseInt(MorbusType_id).inlist(form.MorbusTypeNoSaveAnket)) {
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(true);
			Ext.getCmp('PBRW_saveBskDataButton').setDisabled(true);
			Ext.getCmp('PBRW_printBskDataButton').setDisabled(true);
		} else if (form.formParams.action == 'edit') {
			Ext.getCmp('PBRW_saveBskDataButton').setDisabled(false);
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(true);
		} else if (form.formParams.action == 'load') {
			Ext.getCmp('PBRW_saveBskDataButton').setDisabled(true);
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(false);
		} else {
			Ext.getCmp('PBRW_addBskDataButton').setDisabled(false);
		}
		if (form.formParams.Person_IsDead == '2') form.findById('PBRW_infotab').getTopToolbar().setDisabled(true);
	},
	initComponent: function() {

		var win = this;
		//Панель с перс данными
		win.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: langs('zagruzka'),
			plugins: [Ext.ux.PanelCollapsedTitle],
			titleCollapse: true,
			collapsible: true,
			id: 'PBRW_infoPacient'
		});
		//Дерево Предметы наблюдения
		win.TreePanel = new Ext.tree.TreePanel({
			split: true,
			region: 'west',
			title: langs('Предметы наблюдения'),
			autoScroll: true,
			collapsible: true,
			width: 280,
			tbar: [
				{
					xtype: 'button',
					text: langs('Добавить'),
					iconCls: 'add16',
					disabled: true,
					handler: function(){
						var params = {
							listMorbusType_id: win.formParams.listMorbusType_id,
							Person_id: win.formParams.Person_id,
							clickAction: 'addBskObjectButton'
						};
						if (win.clickToPN !==0) {
							win.showMsgLostData(params);
						} else {
							getWnd('swBSKSelectWindow').show(params);
						}
					}
				}
			],
			/*keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					var node = this.TreePanel.getSelectionModel().getSelectedNode();
					if ( node.id == 'root' )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
						return;
					}
					if ( node.isExpandable() )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
					}

					this.TreePanel.onSelectNode(node, e);
				}.createDelegate(this),
				stopEvent: true
			}],*/
			root: {
				id: 'root',
				expanded: false
			},
			rootVisible: false,
			listeners: {
				'beforeload': function(node) {
					this.TreePanel.getLoader().baseParams.object = node.attributes.object || null;
				}.createDelegate(this),
				'load': function(node) {
					if (node.id == 'root') {
						if ((node.getOwnerTree().rootVisible == false) && (node.hasChildNodes() == true)) {
							var child = node.findChild('object', 'BSKObject');
							if (child) {
								node.getOwnerTree().fireEvent('click', child);
								child.select();
								child.expand();
							}
						} else {
							node.getOwnerTree().fireEvent('click', node);
							node.select();
						}
					}
				}.createDelegate(this)
			},
			selModel : new Ext.tree.DefaultSelectionModel({
				listeners : {
					selectionchange : {
						fn : this.onNodeSelect,
						scope : this
					}
				}
			})/*,
			loader: new Ext.tree.TreeLoader({
				url: '/?c=BSK_RegisterData&m=loadBSKObjectTree'
			})*/
		});
		// Выбор ноды click-ом
		win.TreePanel.on('click', function(node) {
			win.findById('PBRW_tabpanelBSK').setActiveTab(win.findById('PBRW_infotab'));
			var BSKRegistry_id = node.attributes.BSKRegistry_id;
			var MorbusType_id = node.attributes.MorbusType_id;
			if (!BSKRegistry_id && !MorbusType_id) {
				return;
			}
			win.MorbusType_id = MorbusType_id;
			win.onTreePanelClick(node);
		});
		// основная панель Сведения
		win.BSKRegistryData = new Ext.Panel({
			border: false,
			autoScroll: true
		});
	
		Ext.apply(this,{ 
			//layout: 'border',
			items: [
				win.PersonInfoPanel,
				{
					xtype: 'panel',
					region: 'center',
					layout:'border',
					items: [
						win.TreePanel,
						{
							xtype: 'tabpanel',
							id: 'PBRW_tabpanelBSK',
							plain: false,
							border: false,
							bodyBorder: false,
							autoScroll: false,
							activeTab: 0,
							region: 'center',
							items: [
								{
									title: langs('Сведения'),
									xtype: 'panel',
									id: 'PBRW_infotab',
									autoScroll: true,
									tbar: [
										{
											xtype: 'button',
											text: langs('Добавить'),
											id: 'PBRW_addBskDataButton',
											iconCls: 'add16',
											disabled: true,
											handler: function(){
												if (!win.MorbusType_id.inlist(win.MorbusTypeNoSaveAnket) && win.formParams.Person_IsDead !== '2') {
													if (!win.TreePanel.getSelectionModel().getSelectedNode().attributes.MorbusType_id) {
														sw.swMsg.show({
															buttons: Ext.Msg.OK,
															icon: Ext.Msg.WARNING,
															msg: langs('Необходимо выбрать предмет наблюдения'),
															title: ERR_INVFIELDS_TIT
														});
														return;
													}
													var params = {
														action: 'add',
														clickAction: 'addBskDataButton'
													};
													win.formParams.action = 'add';
													if (win.clickToPN !==0) {
														win.showMsgLostData(params);
													} else {
														win.loadBskRegistryData(params);
														win.clickToPN = 1;
													}
												}
											}
										},{
											xtype: 'button',
											text: langs('Сохранить'),
											id: 'PBRW_saveBskDataButton',
											iconCls: 'save16',
											disabled: true,
											handler: function(){
												if (!win.MorbusType_id.inlist(win.MorbusTypeNoSaveAnket)) {
													win.saveBskRegistryData();
												}
											}
										},{
											xtype: 'button',
											text: langs('Изменить'),
											id: 'PBRW_editBskDataButton',
											iconCls: 'edit16',
											disabled: true,
											handler: function(){
												if (!win.MorbusType_id.inlist(win.MorbusTypeNoSaveAnket)) {
													var params = {
														action: 'edit',
														clickAction: 'editBskDataButton'
													};
													win.formParams.action = 'edit';
													if (win.clickToPN !==0) {
														win.showMsgLostData(params);
													} else {
														win.editBskRegistryData();
														win.clickToPN = 1;
													}
												}
											}
										},{
											xtype: 'button',
											text: langs('Печать анкеты'),
											id: 'PBRW_printBskDataButton',
											iconCls: 'print16',
											disabled: true,
											handler: function(){
												switch(win.MorbusType_id){
													case 84 : var report = '/AnketBSKScreening.rptdesign'; break;
													default : var report = '/AnketBSKScreening.rptdesign'; break;
												}
		
												var paramStr = report +'&__format=pdf';
												
												var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
												window.open(url+paramStr, '_blank');
											}
										}
									],
									items : [win.BSKRegistryData],
									listeners: {
										'activate': function(p){
											
										}
									}
								},{
									title: langs('События'),
									xtype: 'panel',
									id: 'PBRW_eventtab',
									autoScroll: true,
									border: false,
									items : [
											new sw.Promed.Panel({
												layout: 'form',
												autoHeight: true,
												style: 'margin-bottom: 0.5em;',
												title: langs('Услуги'),
												collapsible: true,
												items:[
													{
														xtype: 'panel',
														layout: 'fit',
														border: false,
														autoScroll : true,
														items:[
															new sw.Promed.ViewFrame({
																autoExpandColumn: 'autoexpand',
																autoExpandMin: 110,
																dataUrl: '/?c=BSK_RegisterData&m=getListUslugforEvents',
																autoLoadData: false,
																id: 'PBRW_eventBSKRegistryUsluga',
																region: 'center',
																autoWidth: true,
																pageSize: 100,
																contextmenu: false,
																//autoScroll: true,
																paging: false,
																toolbar: false, 
																border: false, 
																focusOnFirstLoad: false,
																stringfields: [ 
																	{name: 'EvnUsluga_id', header: 'Идентификатор услуги', width: 100, type:'int', hidden: true},
																	{name: 'EvnUsluga_setDate', header: 'Дата', width: 70, type:'string'},
																	{name: 'EvnUsluga_setTime', header: 'Время', width: 50, type:'string'},
																	{name: 'Usluga_Code', header: 'Код', width: 150, type:'string'},
																	{name: 'Usluga_Name', header: 'Наименование', id: 'autoexpand', type:'string'},
																	{name: 'Lpu_Nick', header: 'МО', width: 200, type:'string', },
																	{name: 'EvnPS_id', header: 'Просмотр услуги', width: 110, renderer: function (value, cellEl, rec) {
																		if (Ext.isEmpty(rec.get('EvnPS_id'))) {
																			return value;
																		} else {
																			return "<a href='#' onClick='getWnd(\"swEvnPSEditWindow\").show({\"action\": \"view\", EvnPS_id: " + rec.get('EvnPS_id') + " });'>" + "Просмотреть" + "</a>";
																		}
																	}}
																],
																onRowSelect: function(){
																	this.setReadOnly(true);
																}
															})
														]
													}
												]
											}),
											new sw.Promed.Panel({
												layout: 'form',
												autoHeight: true,
												style: 'margin-bottom: 0.5em;',
												title: langs('Случаи оказания амбулаторно-поликлинической медицинской помощи'),
												collapsible: true,
												items:[
													{
														xtype: 'panel',
														layout: 'fit',
														border: false,
														autoScroll : true,
														items:[
															new sw.Promed.ViewFrame({
																autoExpandColumn: 'autoexpand',
																autoExpandMin: 110,
																dataUrl: '/?c=BSK_RegisterData&m=getListPersonCureHistoryPL',
																autoLoadData: false,
																id: 'PBRW_eventBSKRegistryTAP',
																region: 'center',
																autoWidth: true,
																pageSize: 100,
																contextmenu: false,
																//autoScroll: true,
																paging: false,
																toolbar: false, 
																border: false, 
																focusOnFirstLoad: false,
																stringfields: [ 
																	{name: 'Evn_id', header: 'Идентификатор', width: 10, type:'int', hidden: true},
																	{name: 'EvnPL_setDate', header: 'Дата', width: 70, type:'string'},
																	{name: 'Diag_Name', header: 'Диагноз', id: 'autoexpand', type:'string'},
																	{name: 'Lpu_Nick', header: 'МО', width: 200, type:'string', align:'left'},
																	{name: 'LpuSection_Name', header: 'Отделение', width: 200, type:'string'},
																	{name: 'Person_Fio', header: 'Врач', width: 150, type:'string'},
																	{name: 'EvnPL_NumCard', header: 'ТАП №', width: 90, type:'string'},
																	{name: 'EvnPL_id', header: 'Просмотр ТАП', width: 110, renderer: function (value, cellEl, rec) {
																		if (Ext.isEmpty(rec.get('EvnPL_id'))) {
																			return value;
																		} else {
																			return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
																		}
																	}}
																],
																onRowSelect: function(){
																	this.setReadOnly(true);
																}
															})
														]
													}
												]
											}),
											new sw.Promed.Panel({
												layout: 'form',
												autoHeight: true,
												style: 'margin-bottom: 0.5em;',
												title: langs('Случаи оказания стационарной медицинской помощи'),
												collapsible: true,
												items:[{
													xtype: 'panel',
													layout: 'fit',
													border: false,
													autoScroll : true,
													items:[
														new sw.Promed.ViewFrame({
															autoExpandColumn: 'autoexpand',
															autoExpandMin: 110,
															dataUrl: '/?c=BSK_RegisterData&m=getListPersonCureHistoryPS',
															autoLoadData: false,
															id: 'PBRW_eventBSKRegistryKVS',
															region: 'center',
															autoWidth: true,
															pageSize: 100,
															contextmenu: false,
															//autoScroll: true,
															paging: false,
															toolbar: false, 
															border: false, 
															stringfields: [ 
																{name: 'Evn_id', header: 'Идентификатор', width: 10, type:'int', hidden: true},
																{name: 'EvnPS_setDate', header: 'Дата', width: 70, type:'string'},
																{name: 'Diag_Name', header: 'Диагноз', id: 'autoexpand', type:'string'},
																{name: 'Lpu_Nick', header: 'МО', width: 200, type:'string', align:'left'},
																{name: 'LpuSection_Name', header: 'Отделение', width: 200, type:'string'},
																{name: 'Person_Fio', header: 'Врач', width: 150, type:'string'},
																{name: 'EvnPS_NumCard', header: 'КВС №', width: 90, type:'string'},
																{name: 'EvnPS_id', header: 'Просмотр КВС', width: 110, renderer: function (value, cellEl, rec) {
																	if (Ext.isEmpty(rec.get('EvnPS_id'))) {
																		return value;
																	} else {
																		return "<a href='#' onClick='getWnd(\"swEvnPSEditWindow\").show({\"action\": \"view\", EvnPS_id: " + rec.get('EvnPS_id') + " });'>" + "Просмотреть" + "</a>";
																	}
																}}
															],
															onRowSelect: function(){
																this.setReadOnly(true);
															}
														})
													]
												}]
											}),
											new sw.Promed.Panel({
												layout: 'form',
												autoHeight: true,
												style: 'margin-bottom: 0.5em;',
												title: langs('Сопутствующие диагнозы'),
												collapsible: true,
												items:[{
													xtype: 'panel',
													layout: 'fit',
													border: false,
													autoScroll : true,
													items:[
														new sw.Promed.ViewFrame({
															autoExpandColumn: 'autoexpand',
															autoExpandMin: 110,
															dataUrl: '/?c=BSK_RegisterData&m=getListPersonCureHistoryDiagSop',
															autoLoadData: false,
															id: 'PBRW_eventBSKRegistryDiag',
															region: 'center',
															autoWidth: true,
															pageSize: 100,
															contextmenu: false,
															//autoScroll: true,
															paging: false,
															toolbar: false, 
															border: false, 
															stringfields: [ 
																{name: 'Diag_Code', header: 'Код', hidden: true},
																{name: 'Evn_id', header: 'Идентификатор', width: 10, type:'int', hidden: true},
																{name: 'EvnClass_SysNick', header: 'EvnClass_SysNick', hidden: true, type:'string'},
																{name: 'Diag_setDate', header: 'Дата', width: 70, type:'string'},
																{name: 'Diag_FullName', header: 'Диагноз', id: 'autoexpand', type:'string'},
																{name: 'Lpu_Nick', header: 'МО', width: 200, type:'string', align:'left'},
																{name: 'LpuSection_Name', header: 'Отделение', width: 200, type:'string'},
																{name: 'Person_Fio', header: 'Врач', width: 150, type:'string'},
																{name: 'NumCard', header: 'КВС/ТАП №', width: 90, type:'string'},
																{name: 'Evn_id', header: 'Просмотр', width: 110, renderer: function (value, cellEl, rec) {
																	if (Ext.isEmpty(rec.get('Evn_id'))) {
																		return value;
																	}
																	if(rec.get('EvnClass_SysNick') == 'EvnDiagPLSop'){
																		return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('Evn_id') + " });'>" + "Просмотреть" + "</a>";
																	} else{
																		return "<a href='#' onClick='getWnd(\"swEvnPSEditWindow\").show({\"action\": \"view\", EvnPS_id: " + rec.get('Evn_id') + " });'>" + "Просмотреть" + "</a>";
																	}
																	
																}}
															],
															onRowSelect: function(){
																this.setReadOnly(true);
															}
														})
													]
												}]
											}),
											new sw.Promed.Panel({
												layout: 'form',
												autoHeight: true,
												style: 'margin-bottom: 0.5em;',
												title: langs('Постинфарктный кардиосклероз'),
												collapsible: true,
												items:[{
													xtype: 'panel',
													layout: 'fit',
													border: false,
													autoScroll : true,
													items:[
														new sw.Promed.ViewFrame({
															autoExpandColumn: 'autoexpand',
															autoExpandMin: 110,
															dataUrl: '/?c=BSK_RegisterData&m=getListPersonCureHistoryDiagKardio',
															autoLoadData: false,
															id: 'PBRW_eventBSKRegistryPostCardio',
															region: 'center',
															autoWidth: true,
															pageSize: 100,
															contextmenu: false,
															//autoScroll: true,
															paging: false,
															toolbar: false, 
															border: false, 
															stringfields: [ 
																{name: 'Person_id', header: 'Person_id', hidden: true},
																{name: 'Evn_id', header: 'Идентификатор', type:'int', hidden: true},
																{name: 'EvnClass_SysNick', header: 'EvnClass_SysNick', hidden: true, type:'string'},
																{name: 'EvnDiagPS_setDate', header: 'Дата', width: 70, type:'string'},
																{name: 'Diag_FullName', header: 'Диагноз', id: 'autoexpand',type:'string'},
																{name: 'Lpu_Nick', header: 'МО', width: 200, type:'string', align:'left'},
																{name: 'LpuSection_Name', header: 'Отделение', width: 200, type:'string'},
																{name: 'Person_Fio', header: 'Врач', width: 150, type:'string'},
																{name: 'NumCard', header: 'КВС/ТАП №', width: 90, type:'string'},
																{name: 'Evn_id', header: 'Просмотр', width: 100, renderer: function (value, cellEl, rec) {
																	if (Ext.isEmpty(rec.get('Evn_id'))) {
																		return value;
																	}
																	if(rec.get('EvnClass_SysNick') == 'EvnDiagPLSop'){
																		return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('Evn_id') + " });'>" + "Просмотреть" + "</a>";
																	} else{
																		return "<a href='#' onClick='getWnd(\"swEvnPSEditWindow\").show({\"action\": \"view\", EvnPS_id: " + rec.get('Evn_id') + " });'>" + "Просмотреть" + "</a>";
																	}
																	
																}}
															],
															onRowSelect: function(){
																this.setReadOnly(true);
															}
														})
													]
												}]
											})
									],
									listeners : {
										'activate' : function(p){
											var Person_id = win.findById('PBRW_infoPacient').personId;
											this.findById('PBRW_eventBSKRegistryUsluga').getGrid().getStore().load({
												params : {
													Person_id: Person_id
												}
											});
											this.findById('PBRW_eventBSKRegistryTAP').getGrid().getStore().load({
												params : {
													Person_id: Person_id
												}
											});
											
											this.findById('PBRW_eventBSKRegistryKVS').getGrid().getStore().load({
												params : {
													Person_id: Person_id
												}
											});
											this.findById('PBRW_eventBSKRegistryDiag').getGrid().getStore().load({
												params : {
													Person_id: Person_id,
													type: 0
												}
											});
											this.findById('PBRW_eventBSKRegistryPostCardio').getGrid().getStore().load({
												params : {
													Person_id: Person_id,
													type: 1
												}
											});
											this.doLayout();
										}
									}
								},
								{
									title: langs('Исследования'),
									xtype: 'panel',
									disabled: false,
									autoScroll : true,
									html: '' ,
									layout: 'fit',
									listeners : {
										'activate' : function(p){

											var gridBSKRegistryResearch = new sw.Promed.ViewFrame({
												dataUrl: '/?c=BSK_RegisterData&m=getLabResearch',
												autoLoadData: false,
												id: 'PBRW_gridBSKRegistryResearch',
												region: 'center',
												contextmenu: false,
												border: true, 
												focusOnFirstLoad: false,
												stringfields: [ 
													{name: 'EvnUslugaPar_id', header: 'Идентификатор услуги', type:'int', hidden: true},
													{name: 'XmlTemplate_HtmlTemplate', type: 'string', hidden: true},
													{name: 'ResultValueColor', header: 'Результат', type: 'string', hidden: true},
													{name: 'EvnUslugaPar_ResultUnit', type: 'string', hidden: true},
													{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 100, type:'string'},
													{name: 'UslugaComplex_Code', header: 'Код', width: 100, type:'string'},
													{name: 'UslugaComplex_Name', header: 'Наименование', width: 200, type:'string'},
													{name: 'Lpu_Nick', header: 'МО', width: 150, type:'string'},
													{name: 'EvnUslugaPar_ResultValue', header: 'Результат', type: 'string', width: 120, renderer: function (value, meta, rec) {
														var red = rec.get('XmlTemplate_HtmlTemplate');
														var red2 = rec.get('ResultValueColor');
														var result = rec.get('EvnUslugaPar_ResultValue');
														if (red2 != null && red2.includes('#F00')){
															meta.css = "x-grid-cell-reded";
														}
														if (result != null) {
															result = parseFloat(result.replace(/\s/g, "").replace(",", "."));
															if (rec.get('UslugaComplex_Code') == 'A09.05.026') {
																var norm1 = 5.4 / 100 * 40 + 5.4; //норма
																var medium1 = 5.4 / 100 * 70 + 5.4; //средний 
																if (result > norm1 && result <= medium1) {
																	meta.css = "x-grid-cell-reded";
																} else if (result > medium1) {
																	meta.css = "x-grid-cell-red";
																} else {
																	meta.css = 'x-grid-panel';
																}
															} else if (rec.get('UslugaComplex_Code') == 'A09.05.028') {
																var norm2 = 3.3 / 100 * 40 + 3.3; //норма
																var medium2 = 3.3 / 100 * 70 + 3.3; //средний 
																if (result > norm2 && result <= medium2) {
																	meta.css = "x-grid-cell-reded";
																} else if (result > medium2) {
																	meta.css = "x-grid-cell-red";
																} else {
																	meta.css = 'x-grid-panel';
																}
															}
														}
														if (red != null) return rec.get('EvnUslugaPar_ResultValue') + ' ' + rec.get('EvnUslugaPar_ResultUnit')
													}},
													{name: 'prosmotr', header: 'Просмотр результата', width: 150,id: 'autoexpand', renderer: function (value, meta, rec) {
														if (Ext.isEmpty(rec.get('prosmotr'))) {
															return value;
														} else {
															return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('prosmotr') + " });'>" + "Просмотреть" + "</a>";
														}
													}}
												],
												onRowSelect: function(){
													this.setReadOnly(true);
												},
												listeners : {
													'render' : function(){
														this.getGrid().getTopToolbar().hidden = true;
													}
												}
											});
											this.removeAll();
											this.add(gridBSKRegistryResearch);
											this.doLayout();

											gridBSKRegistryResearch.getGrid().getStore().load({
												params : {
													Person_id : win.findById('PBRW_infoPacient').personId
												}
											});
										}
									}
								},
								{
									title: langs('Обследования'),
									disabled: false,
									autoScroll : true,
									html: '' ,
									layout: 'fit',
									listeners : {
										'activate' : function(p){
											var gridBSKRegistrySurveys = new sw.Promed.ViewFrame({
												dataUrl: '/?c=BSK_RegisterData&m=getLabSurveys',
												autoLoadData: false,
												id: 'PBRW_gridBSKRegistrySurveys',
												region: 'center',
												border: true, 
												focusOnFirstLoad: false,
												contextmenu: false,
												stringfields: [ 
													{name: 'EvnUsluga_id', header: 'Идентификатор услуги', type:'int', hidden: true},
													{name: 'EvnUsluga_setDate', header: 'Дата', width: 100, type:'string'},
													{name: 'UslugaComplex_Code', header: 'Код', width: 100, type:'string'},
													{name: 'UslugaComplex_Name', header: 'Наименование', width: 200, type:'string'},
													{name: 'Lpu_Nick', header: 'МО', width: 150, type:'string'},
													{name: 'prosmotr', header: 'Просмотр результата', width: 120,id: 'autoexpand', renderer: function (value, cellEl, rec) {
														if (Ext.isEmpty(rec.get('prosmotr'))) {
															return value;
														} else {
															return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('prosmotr') + " });'>" + "Просмотреть" + "</a>";
														}
													}}
												],
												onRowSelect: function(){
													this.setReadOnly(true);
												},
												listeners : {
													'render' : function(){
														this.getGrid().getTopToolbar().hidden = true;
													}
												}
											});
											this.removeAll();
											this.add(gridBSKRegistrySurveys);
											this.doLayout();
											gridBSKRegistrySurveys.getGrid().getStore().load({
												params : {
													Person_id : win.findById('PBRW_infoPacient').personId
												}
											});
										}
									}
								},
								{
									title: langs('ЭКГ'),
									disabled: false,
									autoScroll : true,
									html: langs('Вкладка находится в разработке'),
									style: 'padding:10px',
									listeners : {
										'activate' : function(p){
										}
									}
								},
								{
									title: langs('Рекомендации'),
									xtype: 'panel',
									id: 'PBRW_recommendations',
									disabled: true,
									autoScroll : true,
									items: [
										new sw.Promed.Panel({
											layout: 'form',
											autoHeight: true,
											style: 'margin-bottom: 0.5em;',
											title: langs('Рекомендации для врача'),
											collapsible: true,
											items:[{
												xtype: 'panel',
												layout: 'fit',
												border: false,
												autoScroll : true,
												items:[
													new sw.Promed.ViewFrame({
														autoExpandColumn: 'autoexpand',
														autoExpandMin: 110,
														dataUrl: '/?c=BSK_RegisterData&m=getRecomendationByDate',
														autoLoadData: false,
														id: 'PBRW_DoctorRecomendations',
														region: 'center',
														autoWidth: true,
														pageSize: 100,
														contextmenu: false,
														paging: false,
														toolbar: true, 
														border: false, 
														focusOnFirstLoad: false,
														hideHeaders:true,
														stringfields: [
															{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden : true},
															{name: 'BSKObservRecomendation_text', type: 'string', header: 'Текст рекомендации', id: 'autoexpand'},
														],
														actions: [
															{name:'action_add',hidden: true,text: 'Создать',disabled: true},
															{name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
															{name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
															{name:'action_view', hidden: true},
															{name:'action_refresh', hidden: true},
															{name:'action_print', hidden: false, handler : function(){
																var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
																var BSKRegistry_setDate = win.findById('PBRW_BSKRegistry_setDate').getValue().format('Y-m-d');
																var paramStr = '/getRecomendationByDate.rptdesign'
																			+'&Person_id='+win.findById('PBRW_infoPacient').personId
																			+'&Morbus_id='+win.MorbusType_id
																			+'&Sex_id='+win.findById('PBRW_infoPacient').getFieldValue('Sex_id')
																			+'&BSKObservRecomendationType_id=1'
																			+'&BSKRegistry_id='+win.formParams.BSKRegistry_id
																			+'&__format=pdf';
																window.open(url+paramStr, '_blank');
															}}
														]
													})
												]
											}]
										}),
										new sw.Promed.Panel({
											layout: 'form',
											autoHeight: true,
											style: 'margin-bottom: 0.5em;',
											title: langs('Рекомендации для пациента'),
											collapsible: true,
											items:[{
												xtype: 'panel',
												layout: 'fit',
												border: false,
												autoScroll : true,
												items:[
													new sw.Promed.ViewFrame({
														autoExpandColumn: 'autoexpand',
														autoExpandMin: 110,
														dataUrl: '/?c=BSK_RegisterData&m=getRecomendationByDate',
														autoLoadData: false,
														id: 'PBRW_PacientRecomendations',
														region: 'center',
														autoWidth: true,
														pageSize: 100,
														contextmenu: false,
														paging: false,
														toolbar: true, 
														border: false, 
														focusOnFirstLoad: false,
														hideHeaders:true,
														stringfields: [
															{name: 'BSKObservRecomendation_id', type: 'int', header: 'ID', hidden : true},
															{name: 'BSKObservRecomendation_text', type: 'string', header: 'Текст рекомендации', id: 'autoexpand'},
														],
														actions: [
															{name:'action_add',hidden: true,text: 'Создать',disabled: true},
															{name:'action_edit', hidden: true,text: 'Изменить', disabled: true },
															{name:'action_delete', hidden: true,text: 'Удалить', disabled: true,},
															{name:'action_view', hidden: true},
															{name:'action_refresh', hidden: true},
															{name:'action_print', hidden: false, handler : function(){
																var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
																var BSKRegistry_setDate = win.findById('PBRW_BSKRegistry_setDate').getValue().format('Y-m-d');
																var paramStr = '/getRecomendationByDate.rptdesign'
																			+'&Person_id='+win.findById('PBRW_infoPacient').personId
																			+'&Morbus_id='+win.MorbusType_id
																			+'&Sex_id='+win.findById('PBRW_infoPacient').getFieldValue('Sex_id')
																			+'&BSKObservRecomendationType_id=2'
																			+'&BSKRegistry_id='+win.formParams.BSKRegistry_id
																			+'&__format=pdf';
																window.open(url+paramStr, '_blank');
															}}
														]
													})
												]
											}]
										})
									],
									listeners : {
										'activate' : function(p){
											this.findById('PBRW_DoctorRecomendations').getGrid().getStore().removeAll();
											this.findById('PBRW_PacientRecomendations').getGrid().getStore().removeAll();
											if (win.formParams.BSKRegistry_id) {
												//var node = form.TreePanel.getSelectionModel().getSelectedNode();
												var params = {
													BSKRegistry_id : win.formParams.BSKRegistry_id,//node.attributes.BSKRegistry_id,
													MorbusType_id : win.formParams.MorbusType_id,
													Person_id : win.findById('PBRW_infoPacient').personId,
													Sex_id : win.findById('PBRW_infoPacient').getFieldValue('Sex_id'),
													BSKObservRecomendationType_id : null
												}
												params.BSKObservRecomendationType_id = 1;
												this.findById('PBRW_DoctorRecomendations').getGrid().getStore().load({
													params : params
												});
												params.BSKObservRecomendationType_id = 2;
												this.findById('PBRW_PacientRecomendations').getGrid().getStore().load({
													params : params
												});
												this.doLayout();
											} else {
												//form.showMsg('Не выбран предмет наблюдения');
												this.doLayout();
											}
										}
									}
								},
								{
									title: langs('Лекарственное лечение'),
									xtype: 'panel',
									id: 'PBRW_drug',
									disabled: false,
									autoScroll : true,
									html: '',
									layout: 'fit',
									listeners : {
										'activate' : function(p){
											var GridPanel = new sw.Promed.ViewFrame({
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 100,
												dataUrl: '/?c=BSK_RegisterData&m=getDrugs',
												autoLoadData: false,
												region: 'center',
												id: 'PBRW_drug_Grid',
												pageSize: 20,
												contextmenu: false,
												toolbar: true, 
												border: true, 
												focusOnFirstLoad: false,
												grouping: true,
												groupTextTpl: '<span style="color: rgb(113,0,0); font-size: 14px;">{[values.rs[0].data.MorbusType_Name]}</span> ({[!values.rs[0].data.BSKRegistry_setDate? 0 : values.rs.length]} {[values.rs.length == 1 && values.rs[0].data.BSKRegistry_setDate ? "запись" : (values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})',
												groupingView: {
													showGroupName: false,
													showGroupsText: true
												},
												actions: [
													{name:'action_add', hidden: true, text: 'Создать', disabled: true},
													{name:'action_edit', hidden: true, text: 'Изменить', disabled: true },
													{name:'action_delete', hidden: true, text: 'Удалить', disabled: true},
													{name:'action_view', hidden: true},
													{name:'action_refresh', hidden: true},
													{name: 'action_print', hidden: false}
												],
												stringfields: [
													{ name: 'BSKRegistryData_id', type: 'string', header: 'ID', key: true },
													{ name: 'MorbusType_id', type: 'int', hidden: true },
													{ name: 'MorbusType_Name', header: langs('Предмет наблюдения'), type: 'string', hidden: false, group: true, sort: true, direction: [
														{field: 'MorbusType_Name', direction:'DESC'}
													]},
													{ name: 'BSKRegistry_setDate', dateFormat: 'd.m.Y', type: 'date', header: langs('Дата анкетирования'), width: 120 },
													{ name: 'BSKRegistry_prDate', dateFormat: 'd.m.Y', type: 'date', header: langs('Дата назначения'), width: 120 },
													{ name: 'BSKObservElement_name', type: 'string', header: langs('Группа'), id: 'autoexpand'},
													{ name: 'BSKRegistryData_data', type: 'string', header: langs('МНН'), width: 140 },
													{ name: 'Unit_name', header: langs('Дозировка'), width: 120 },
													{ name: 'ReasonCancel', header: 'Причина отмены/смены', width: 200 }
												],
												onRowSelect: function(){
													this.setReadOnly(true);
												}
											});
											this.removeAll();
											this.add(GridPanel);
											this.doLayout();
											GridPanel.getGrid().getStore().load({
												params : {
													Person_id : win.findById('PBRW_infoPacient').personId
												}
											});
										}
									}
								},
								{
									title: langs('Сравнение'),
									xtype: 'panel',
									id: 'PBRW_compare',
									disabled: true,
									layout: 'fit',
									listeners : {
										'activate' : function(p){
											
											var DatesGrid = new sw.Promed.ViewFrame({
												enableColumnHide: false,
												selectionModel: 'multiselect',
												contextmenu: false,
												border: true,
												region: 'center',
												object: 'DatesGrid',
												id: 'PBRW_compare_DatesGrid',
												dataUrl: '/?c=BSK_RegisterData&m=getCompare',
												autoLoadData: false,
												focusOnFirstLoad: false,
												toolbar: true,
												stringfields: [
													{name: 'BSKRegistry_id', type: 'int', header: 'ID'},
													{name: 'BSKRegistry_setDate', width: 70,type: 'string',hidden:false, header: 'Дата'},
													{name: 'BSKRegistry_riskGroup',width: 90, type: 'int',hidden:false, header: 'Группа риска', align: 'center'},
												],
												actions: [
													{name:'action_add', hidden: true, text: 'Создать', disabled: true},
													{name:'action_edit', hidden: true, disabled: true},
													{name:'action_delete', hidden: true, text: 'Удалить', disabled: true},
													{name:'action_view', hidden: true, text: 'Изменить', disabled: true},
													{name:'action_refresh', hidden: true},
													{name:'action_print', hidden: true}
												], 
												onLoadData: function() {
											
												},
												onDblClick: function() {
													return;
												},
												listeners : {
													'render' : function(){
													}
												}
											}); 
											
											DatesGrid.getGrid().on('rowclick',
												function(){
													var sel = this.getSelectionModel().getSelections();
													if(sel.length>9){
														win.showMsg(langs('Указано максимальное количество дат для сравнения!'));
													}
												}
											);
											
											this.removeAll();
											this.add(DatesGrid);
											this.doLayout();
											DatesGrid.getGrid().getStore().load({
												params : {
													Person_id : win.findById('PBRW_infoPacient').personId,
													MorbusType_id : win.MorbusType_id
												}
											});
											DatesGrid.addActions({name:'action_compare', text: langs('Сравнить данные'), iconCls: 'edit16', handler: function (){
												var sel = DatesGrid.getGrid().getSelectionModel().getSelections();
	
												if(sel.length == 0){
													win.showMsg(langs('Для формирования сравнения необходимо указать даты проведения обследования!')); 
													return false;
												}
												if(sel.length>9){
													win.showMsg(langs('Указано максимальное количество дат для сравнения!'));
													return false;
												}
												else {
													var datesForCompare = '';
													for(var k in sel){
														if(typeof sel[k] == 'object'){
															var preDate = sel[k].get('BSKRegistry_setDate').split('.');
															var dateStr = preDate[2]+'-'+preDate[1]+'-'+preDate[0];
															datesForCompare +=dateStr+",";
														}
													}
													
													var dates = datesForCompare.substr(0,(datesForCompare.length)-1).replace(/\./g,'-');
													var Person_id = win.findById('PBRW_infoPacient').personId;
													var MorbusType_id = win.MorbusType_id;
													var Age = win.findById('PBRW_infoPacient').getFieldValue('Person_Age');
													
													var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'run?__report=report';
													switch(win.MorbusType_id){
														case 84 : var report = '/getBSKCompare.rptdesign'; break;
														default : var report = '/getBSKCompare.rptdesign';
													}
																
													var paramStr = report
																+"&Dates='"+dates+"'"
																+'&Person_id='+Person_id
																+'&Sex_id='+win.findById('PBRW_infoPacient').getFieldValue('Sex_id')
																+'&MorbusType_id='+MorbusType_id
																+'&Age='+Age
																+'&__format=html';
													window.open(url+paramStr, '_blank');
												}
											}});
										}
									}
								},
								{
									title: langs('Прогнозируемые осложнения заболевания'),
									xtype: 'panel',
									id: 'PBRW_PrognosDiseasesTab',
									autoScroll : true,
									html: '' ,
									layout: 'fit',
									listeners : {
										'activate' : function(p){
											var PrognosDiseases = new sw.Promed.DiagListPanelWithDescr({
												autoWidth: true,
												id: 'PBRW_PrognosDiseases',
												buttonAlign: 'left',
												labelAlign: 'top',
												buttonLeftMargin: 0,
												labelWidth: 140,
												showOsl: false,
												fieldWidth: 270,
												showDescr: false,
												style: 'padding: 0.5em;',
												fieldLabel: langs('Прогнозируемые осложнения заболевания по МКБ'),
												fieldDescLabel: langs('Прогнозируемые осложнения заболевания'),
												onChange: function() {

												}
											});
											this.removeAll();
											this.add(PrognosDiseases);
											this.doLayout();
											Ext.Ajax.request({
												url: '/?c=BSK_RegisterData&m=loadPrognosDiseases',
												params: {
													Person_id : win.findById('PBRW_infoPacient').personId
												},
												callback: function(options, success, response) {
													if (success === true) {
														var obj = Ext.util.JSON.decode(response.responseText);
														win.findById('PBRW_PrognosDiseases').setValues(obj);
													}
												}
											});
											PrognosDiseases.reset();
											if (win.PersonInfoPanel.getFieldValue('Person_IsDead') !== '2') {
												win.findById('PBRW_PrognosDiseasesTab').getTopToolbar().setDisabled(false);
											} else 
												win.findById('PBRW_PrognosDiseasesTab').getTopToolbar().setDisabled(true);
										}
									},
									tbar: [
										{
											xtype: 'button',
											text: langs('Сохранить'),
											id: 'PBRW_saveBskPrognosDiseases',
											iconCls: 'save16',
											disabled: true,
											handler: function(){
												win.getLoadMask(langs('Сохранение прогнозируемых осложнений основного заболевания')).show();
												Ext.Ajax.request({
													url: '/?c=BSK_RegisterData&m=savePrognosDiseases',
													params: {
														PrognosOslDiagList : Ext.util.JSON.encode(win.findById('PBRW_PrognosDiseases').getValues()),
														Person_id : win.findById('PBRW_infoPacient').personId
													},
													callback: function(options, success, response) {
														win.getLoadMask().hide();
														var responseText = Ext.util.JSON.decode(response.responseText);
														if (responseText.success === true) {
															win.showMsg(langs('Данные прогнозируемых осложнений основного заболевания успешно сохранены!'));
														}
													}
												});
											}
										}
									]
								}
							]
						}
					],
					buttons: [
						{
							xtype: 'button',
							id: 'closef',
							text: langs('Закрыть'),
							iconCls: 'close16',
							handler: function () {
								var win = this;
								var params = {
									action: 'close',
									clickAction: 'closeBskDataButton'
								};
								if (win.clickToPN !==0) {
									win.showMsgLostData(params);
								} else {
									win.MorbusType_id = 0;
									win.hide();
								}
							}.createDelegate(this)
						}
					]
				}

			],
			buttons : []
		});

		sw.Promed.personBskRegistryDataWindow.superclass.initComponent.apply(this, arguments);
	},
	show : function(params) {
		sw.Promed.personBskRegistryDataWindow.superclass.show.apply(this, arguments);
		this.formParams = params;
		this.formParams.action = 'load';
		this.BSKRegistryData.removeAll();
		this.clickToPN = 0;
		this.findById('PBRW_infotab').getTopToolbar().setDisabled(true);
		this.findById('PBRW_tabpanelBSK').setActiveTab(this.findById('PBRW_infotab'));
		this.findById('PBRW_compare').setDisabled(true);
		this.findById('PBRW_recommendations').setDisabled(true);
		this.TreePanel.getTopToolbar().setDisabled(true);
		this.PersonInfoPanel.personId = params.Person_id;
		this.PersonInfoPanel.setTitle('...');
		this.PersonInfoPanel.load({
			callback: function () {
				this.PersonInfoPanel.setPersonTitle();
				this.formParams.Person_IsDead = this.PersonInfoPanel.getFieldValue('Person_IsDead');
				if (this.PersonInfoPanel.getFieldValue('Person_IsDead') !== '2') 
					this.TreePanel.getTopToolbar().setDisabled(false);
			}.createDelegate(this),
			Person_id: this.PersonInfoPanel.personId
		});
		this.TreePanel.loader = new Ext.tree.TreeLoader({
			url: '/?c=BSK_RegisterData&m=loadBSKObjectTree'
		})
		this.TreePanel.selModel.selNode = null;
		var root = this.TreePanel.getRootNode();
		this.TreePanel.getLoader().baseParams.Person_id = arguments[0].Person_id;
		var w = this;
		this.TreePanel.getRootNode().loaded = true;
		this.TreePanel.getRootNode().loading = false;
		this.TreePanel.getLoader().load(root,function(){
			w.TreePanel.getRootNode().expand(false);
			w.formParams.listMorbusType_id = [];
			w.TreePanel.getRootNode().eachChild(function(child){
				w.formParams.listMorbusType_id.push(child.attributes.MorbusType_id);
			});
			//Переход из ЭМК и Сигн инфо на конкретную анкету
			if (params.BSKObject_id) {
				var objNode = w.TreePanel.getNodeById(params.BSKObject_id);
				objNode.expand(true, true, function(n){
					if (params.BSKRegistry_id) {
						var anketNode = objNode.findChild('BSKRegistry_id',params.BSKRegistry_id);
						if (anketNode) {
							anketNode.select();
							var MorbusType_id = anketNode.attributes.MorbusType_id;
							if (!MorbusType_id) {
								return;
							}
							w.MorbusType_id = MorbusType_id;
							w.onTreePanelClick(anketNode);
						}
					}
				});
			}
		});

	},
 
	listeners : {
		'render' : function(){

		},
		'hide': function() {
			if (this.refresh)
				this.onHide();
		}
		
	}
});