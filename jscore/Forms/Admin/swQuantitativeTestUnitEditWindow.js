/**
 * swQuantitativeTestUnitEditWindow - окно редактирования "Соответствия конкретных ответов конкретному качественному тесту"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      29.10.2013
 * @comment
 */
sw.Promed.swQuantitativeTestUnitEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	objectName: 'swQuantitativeTestUnitEditWindow',
	objectSrc: '/jscore/Forms/Admin/swQuantitativeTestUnitEditWindow.js',
	title: lang['edinitsyi_izmereniya'],
	layout: 'form',
	id: 'QuantitativeTestUnitEditWindow',
	modal: true,
	shim: false,
	width:600,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.findById('QuantitativeTestUnitEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		var form = this.form;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = win.action;
		
		if (win.form.findField('QuantitativeTestUnit_IsBase').disabled) {
			params.QuantitativeTestUnit_IsBase = win.form.findField('QuantitativeTestUnit_IsBase').getValue();
		} else {
			if (win.form.findField('QuantitativeTestUnit_IsBase').getValue() && !options.ignoreIsBaseCheck) {
				// Если при добавлении / редактировании был проставлен чекбокс «Базовая», то выводить сообщение «Изменить базовую единицу измерения? Да/Отмена»
				loadMask.hide();
				
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId)
						{
							options.ignoreIsBaseCheck = true;
							win.submit(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['izmenit_bazovuyu_edinitsu_izmereniya'],
					title: lang['vopros']
				});
				
				return false;
			}
		}
		
		if (win.form.findField('QuantitativeTestUnit_CoeffEnum').disabled) {
			params.QuantitativeTestUnit_CoeffEnum = win.form.findField('QuantitativeTestUnit_CoeffEnum').getValue();
		}
		
		form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				win.callback(win.owner, action.result.QuantitativeTestUnit_id);
				win.hide();
			}
		});
	},
	show: function() {
		var win = this;
		sw.Promed.swQuantitativeTestUnitEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.QuantitativeTestUnit_id = null;
		this.AnalyzerTestType_Code = 1;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].AnalyzerTestType_Code ) {
			this.AnalyzerTestType_Code = arguments[0].AnalyzerTestType_Code;
		}
		if ( arguments[0].AnalyzerTest_begDT ) {
			this.AnalyzerTest_begDT = arguments[0].AnalyzerTest_begDT;
		}
		if ( arguments[0].addedUnits ) {
			this.addedUnits = arguments[0].addedUnits;
		} else {
			this.addedUnits = '';
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].isBase ) {
			this.isBase = arguments[0].isBase;
		} else {
			this.isBase = false;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].QuantitativeTestUnit_id ) {
			this.QuantitativeTestUnit_id = arguments[0].QuantitativeTestUnit_id;
		}
		if ( undefined != arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzertest_id'], function() { win.hide(); });
		}
		this.form.reset();
		win.form.findField('AnalyzerTest_id').setValue(win.AnalyzerTest_id);
		win.findById('qtuewUnit').getStore().reload({params: {where: " where (Unit_endDate >= '"+this.AnalyzerTest_begDT+"' OR Unit_endDate = NULL) "+this.addedUnits}});
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				win.setTitle(lang['edinitsyi_izmereniya_dobavlenie']);
				win.enableEdit(true);
				win.form.findField('QuantitativeTestUnit_CoeffEnum').fireEvent('change', win.form.findField('QuantitativeTestUnit_CoeffEnum'), win.form.findField('QuantitativeTestUnit_CoeffEnum').getValue());
				win.form.findField('QuantitativeTestUnit_IsBase').fireEvent('check', win.form.findField('QuantitativeTestUnit_IsBase'), win.form.findField('QuantitativeTestUnit_IsBase').getValue());
				if (win.isBase) {
					win.form.findField('QuantitativeTestUnit_IsBase').setValue(true);
					win.form.findField('QuantitativeTestUnit_IsBase').disable();
					win.form.findField('QuantitativeTestUnit_CoeffEnum').setValue(1);
					win.form.findField('QuantitativeTestUnit_CoeffEnum').disable();
				}
				if (win.AnalyzerTestType_Code == 3) {
					win.form.findField('QuantitativeTestUnit_CoeffEnum').setValue(1);
					win.form.findField('QuantitativeTestUnit_CoeffEnum').disable();
				}
				win.form.findField('Unit_id').focus();
				break;
			case 'edit':
			case 'view':
				if (arguments[0].action == 'edit') {
					win.setTitle(lang['edinitsyi_izmereniya_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['edinitsyi_izmereniya_prosmotr']);
					win.enableEdit(false);
				}
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.hide();
					},
					params:{
						QuantitativeTestUnit_id: win.QuantitativeTestUnit_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						win.form.setValues(result[0]);
						win.AnalyzerTest_id = result[0].AnalyzerTest_id;
						loadMask.hide();
						win.form.findField('QuantitativeTestUnit_CoeffEnum').fireEvent('change', win.form.findField('QuantitativeTestUnit_CoeffEnum'), win.form.findField('QuantitativeTestUnit_CoeffEnum').getValue());
						win.form.findField('QuantitativeTestUnit_IsBase').fireEvent('check', win.form.findField('QuantitativeTestUnit_IsBase'), win.form.findField('QuantitativeTestUnit_IsBase').getValue());
						// Если проставлен чекбокс в поле «Базовая», то данное поле недоступно для редактирования (снять чекбокс нельзя) 
						if (win.form.findField('QuantitativeTestUnit_IsBase').getValue()) {
							win.form.findField('QuantitativeTestUnit_IsBase').disable();
						}
						if (win.isBase) {
							win.form.findField('QuantitativeTestUnit_IsBase').setValue(true);
							win.form.findField('QuantitativeTestUnit_IsBase').disable();
							win.form.findField('QuantitativeTestUnit_CoeffEnum').setValue(1);
							win.form.findField('QuantitativeTestUnit_CoeffEnum').disable();
						}
						if (win.AnalyzerTestType_Code == 3) {
							win.form.findField('QuantitativeTestUnit_CoeffEnum').setValue(1);
							win.form.findField('QuantitativeTestUnit_CoeffEnum').disable();
						}
						win.form.findField('Unit_id').focus();
					},
					url:'/?c=QuantitativeTestUnit&m=load'
				});
				break;
		}
	},
	initComponent: function() {
		var win = this;
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'QuantitativeTestUnitEditForm',
				labelAlign: 'right',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 160,
				collapsible: true,
				region: 'north',
				url:'/?c=QuantitativeTestUnit&m=save',
				items: [{
					name: 'QuantitativeTestUnit_id',
					xtype: 'hidden',
					value: 0
				},
				{
					fieldLabel: lang['naimenovanie'],
					hiddenName: 'Unit_id',
					editable: true,
					xtype: 'swcommonsprcombo',
					listeners: {
						'beforeselect': function(combo, record) {
							if (win.AnalyzerTestType_Code != 3) {
								var newValue = record.get('Unit_id');
								if (!Ext.isEmpty(newValue) && !win.form.findField('QuantitativeTestUnit_CoeffEnum').disabled) {
									// подгрузка коэффициента пересчёта
									win.getLoadMask(lang['podgruzka_koeffitsienta_perescheta']).show();
									Ext.Ajax.request({
										failure:function () {
											win.getLoadMask().hide();
										},
										params:{
											Unit_id: newValue,
											AnalyzerTest_id: win.AnalyzerTest_id
										},
										success: function (response) {
											win.getLoadMask().hide();
											var result = Ext.util.JSON.decode(response.responseText);
											
											if (!Ext.isEmpty(result.coeff)) {
												win.form.findField('QuantitativeTestUnit_CoeffEnum').setValue(result.coeff);
												win.form.findField('QuantitativeTestUnit_CoeffEnum').fireEvent('change', win.form.findField('QuantitativeTestUnit_CoeffEnum'), win.form.findField('QuantitativeTestUnit_CoeffEnum').getValue());
											}
										},
										url:'/?c=QuantitativeTestUnit&m=loadCoeff'
									});
								}
							}
						}
					},
					prefix:'lis_',
					allowBlank:false,
					sortField:'Unit_Name',
					comboSubject: 'Unit',
					id: 'qtuewUnit',
					width: 400
				},
				{
					name: 'QuantitativeTestUnit_IsBase',
					fieldLabel: lang['bazovaya'],
					listeners: {
						'check': function(checkbox, value) {
							if ( value == true ) {
								win.form.findField('QuantitativeTestUnit_CoeffEnum').setAllowBlank(true);
								win.form.findField('QuantitativeTestUnit_CoeffEnum').disable();
							} else {
								win.form.findField('QuantitativeTestUnit_CoeffEnum').setAllowBlank(false);
								if (win.action != 'view') {
									if (win.AnalyzerTestType_Code != 3) {
										win.form.findField('QuantitativeTestUnit_CoeffEnum').enable();
									}
								}
							}
						}
					},
					xtype: 'checkbox'
				},
				{
					name: 'QuantitativeTestUnit_CoeffEnum',
					listeners: {
						'change': function() {
							if (win.AnalyzerTestType_Code != 3) {
								if (Ext.isEmpty(this.getValue())) {
									win.form.findField('QuantitativeTestUnit_IsBase').setValue(null);
									win.form.findField('QuantitativeTestUnit_IsBase').disable();
								} else {
									win.form.findField('QuantitativeTestUnit_IsBase').enable();
								}
							}
						}
					},
					autoCreate: {
						tag: "input",
						type: "text",
						maxLength: "12",
						autocomplete: "off"
					},
					decimalPrecision: 6,
					minValue: 0.000001,
					maxValue: 1000000,
					fieldLabel: lang['koeffitsient_perescheta'],
					xtype: 'numberfield'
				},
				{
					name: 'AnalyzerTest_id',
					xtype: 'hidden',
					value: 0
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'QuantitativeTestUnit_id'},
				{name: 'Unit_id'},
				{name: 'QuantitativeTestUnit_IsBase'},
				{name: 'QuantitativeTestUnit_CoeffEnum'},
				{name: 'AnalyzerTest_id'}
			]),
			url: '/?c=QuantitativeTestUnit&m=save'
		});
		Ext.apply(this, {
			buttons:
			[{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				onTabAction: function() {
					win.form.findField('Unit_id').focus();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swQuantitativeTestUnitEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('QuantitativeTestUnitEditForm').getForm();
	}
});