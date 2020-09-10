/**
* Окно Извещение по нефрологии
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.EvnNotifyNephroEditWindow', {
	alias: 'widget.swEvnNotifyNephroEditWindowExt6',
	height: 750,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 842,
	resizable: true,
	cls: 'arm-window-new emk-forms-window PersonDispPanel person-disp-diag-edit-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	formMode: 'remote',
	formStatus: 'edit',
	conf: {
		dateW: 121+339,
		fs_column1: {
			width: 330+70,
			labelWidth: 327
		},
		fs_column2: {
			width: 174+70,
			labelWidth: 174
		}
	},
	
	title: 'Извещение по нефрологии',
	doSave: function()
	{
		if ( this.formStatus == 'save' || this.action != 'add' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = {};
		if(this.fromDispCard){
			params.fromDispCard = 1;
		}
		params.Diag_id = base_form.findField('Diag_id').getValue();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext6.LoadMask(win, {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		//params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		//params.EvnNotifyNephro_setDate = Ext.util.Format.date(base_form.findField('EvnNotifyNephro_setDate').getValue(), 'd.m.Y');
		//params.Diag_Name = base_form.findField('Diag_Name').getValue();
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext6.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				//~ showSysMsg(langs('Извещение создано'));
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
				}
				win.callback(data);
				win.hide();
			}
		});
		return true;
	},
	setFieldsDisabled: function(d) 
	{
		var win = this;
		var f = win.FormPanel.query('field');
		
		for(i in f) {
			if(typeof f[i].setDisabled == 'function' && f[i].changeDisabled!==false) {
				if(f[i].name && f[i].name == 'Diag_id'){
					f[i].setDisabled(true);
				} else {
					f[i].setDisabled(d);
				}
			}
		}
		win.queryById('button_save').setDisabled(d);
	},
	show: function() {
		this.callParent(arguments);
		var me = this;
		me.taskButton.hide();
		if (!arguments[0] || !arguments[0].formParams) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					me.hide();
				}
			});
			return false;
		}
		this.focus();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';

		this.EvnNotifyNephro_id = arguments[0].EvnNotifyNephro_id || null;
		this.callback = arguments[0].callback || Ext6.emptyFn;
		//~ this.onHide = arguments[0].onHide || Ext6.emptyFn;

		var url, params = {};
		if (this.EvnNotifyNephro_id) {
			this.action = 'view';
			//~ this.setTitle(langs('Извещение по нефрологии: Просмотр'));
			this.setFieldsDisabled(true);
			url = '/?c=MorbusNephro&m=doLoadEditFormEvnNotifyNephro';
			params.EvnNotifyNephro_id = this.EvnNotifyNephro_id;
		} else {
			this.action = 'add';
			//~ this.setTitle(langs('Извещение по нефрологии: Добавление'));
			this.setFieldsDisabled(false);
			if (!arguments[0].formParams.EvnNotifyNephro_setDate) {
				arguments[0].formParams.EvnNotifyNephro_setDate = getGlobalOptions().date;
			}
			if (!arguments[0].formParams.EvnNotifyNephro_diagDate) {
				arguments[0].formParams.EvnNotifyNephro_diagDate = getGlobalOptions().date;
			}
			if (!arguments[0].formParams.MedPersonal_id) {
				arguments[0].formParams.MedPersonal_id = getGlobalOptions().medpersonal_id;
			}
			if (!arguments[0].formParams.MedPersonal_hid) {
				arguments[0].formParams.MedPersonal_hid = getGlobalOptions().medpersonal_id;
			}
			url = '/?c=MorbusNephro&m=doLoadEditFormMorbusNephro';
			params.Morbus_id = arguments[0].formParams.Morbus_id;
			this.fromDispCard = arguments[0].formParams.fromDispCard;
		}
		
		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext6.LoadMask(me,{msg: LOAD_WAIT});
		loadMask.show();

		Ext6.Ajax.request({
			failure:function () {
				loadMask.hide();
				me.hide();
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
			},
			params: params,
			success:function (response) {
				var result = Ext6.util.JSON.decode(response.responseText);
				if ('add' == me.action) {
					base_form.findField('Person_id').setValue(result[0].Person_id);
					base_form.findField('PersonHeight_id').setValue(result[0].PersonHeight_id || null);
					base_form.findField('PersonWeight_id').setValue(result[0].PersonWeight_id || null);
					base_form.findField('PersonHeight_Height').setValue(result[0].PersonHeight_Height || null);
					base_form.findField('PersonWeight_Weight').setValue(result[0].PersonWeight_Weight || null);
					if (!base_form.findField('Diag_id').getValue()) {
						base_form.findField('Diag_id').setValue(result[0].Diag_id || null);
					}
					base_form.findField('NephroDiagConfType_id').setValue(result[0].NephroDiagConfType_id || null);
					base_form.findField('NephroCRIType_id').setValue(result[0].NephroCRIType_id || null);
					base_form.findField('EvnNotifyNephro_IsHyperten').setValue(result[0].MorbusNephro_IsHyperten || null);
					base_form.findField('EvnNotifyNephro_Treatment').setValue(result[0].MorbusNephro_Treatment || null);
					base_form.findField('EvnNotifyNephro_firstDate').setValue(result[0].MorbusNephro_firstDate || null);
				} else {
					base_form.setValues(result[0]);
				}
				if (base_form.findField('Diag_id').getValue()) {
					base_form.findField('Diag_id').getStore().load({
						params: {
							where: ' where Diag_id = ' + base_form.findField('Diag_id').getValue()
						},
						callback: function()
						{
							base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
							base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());
						}
					});
				}
				base_form.findField('MedPersonal_id').getStore().load({
					callback: function()
					{
						base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
						base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
					}
				});
				base_form.findField('MedPersonal_hid').getStore().load({
					callback: function()
					{
						base_form.findField('MedPersonal_hid').setValue(base_form.findField('MedPersonal_hid').getValue());
						base_form.findField('MedPersonal_hid').fireEvent('change', base_form.findField('MedPersonal_hid'), base_form.findField('MedPersonal_hid').getValue());
					}
				});
				loadMask.hide();
			},
			url: url
		});
		return true;
	},
	initComponent: function() {
		var win = this;
		win.PersonParameters = new Ext6.form.Panel({
			layout: 'hbox',
			border: false,
			defaults:{
				labelWidth: 335,
			},
			items:[{
				fieldLabel: langs('Рост'),
				name: 'PersonHeight_Height',
				width: 335+70,
				margin: '0 0 5 0',
				xtype: 'numberfield',
				allowNegative: false,
				allowDecimals: false,
				decimalPrecision: 0,
				regex:new RegExp('(^[0-9]{0,3})$'),
				maxValue: 999,
				maxLength: 3,
				maxLengthText: langs('Максимальная длина этого поля 3 символа')
			},{
				xtype:'label',
				html:'см',
				margin: '3 0 0 7',
			}, {
				fieldLabel: langs('Вес'),
				name: 'PersonWeight_Weight',
				labelWidth: 28,
				width: 28+70,
				margin: '0 0 5 35',
				xtype: 'numberfield',
				allowNegative: false,
				allowDecimals: false,
				decimalPrecision: 0,
				regex:new RegExp('(^[0-9]{0,3})$'),
				maxValue:999,
				maxLength: 3,
				maxLengthText: langs('Максимальная длина этого поля 3 символа')
			},{
				xtype:'label',
				html:'кг',
				margin: '3 0 0 7',
			}]
		});
		win.FormPanel = new Ext6.form.FormPanel({
			bodyPadding: '25 25 20 20',
			cls: 'dispcard',
			scrollable: true,
			region: 'center',
			border: false,
			msgTarget: 'side',
			items:[
			{
				region: 'north',
				border: false,
				layout: 'vbox',
				xtype: 'panel',
				defaults:{
					labelWidth: 335,
					maxWidth: 697
				},
				items: [{
					name: 'EvnNotifyNephro_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyNephro_pid',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonHeight_id',
					xtype: 'hidden'
				}, {
					name: 'PersonWeight_id',
					xtype: 'hidden'
				}, {
					layout: 'vbox',
					xtype: 'panel',
					margin: '0 0 0 16',
					width: '100%',
					defaults:{
						labelWidth: 335,
						maxWidth: 697
					},
					border: false,
					items: [{
						name: 'Diag_id',
						fieldLabel: langs('Диагноз'),
						xtype: 'swDiagCombo',
						//anchor:'100%',
						width: '100%',
						labelWidth: 335,
						userCls: 'notrigger',
						MorbusType_SysNick: 'nephro',
						allowBlank: false
					}, {
						fieldLabel: langs('Дата установления'),
						name: 'EvnNotifyNephro_diagDate',
						labelWidth: 335,
						width: 335 + 111,
						allowBlank: false,
						xtype: 'datefield',
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: langs('Дата заболевания до установления диагноза'),
						name: 'EvnNotifyNephro_firstDate',
						labelWidth: 335,
						allowBlank: false,
						width: 335 + 111,
						xtype: 'datefield',
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: langs('Способ установления диагноза'),
						name: 'NephroDiagConfType_id',
						allowBlank: false,
						width: '100%',
						xtype: 'commonSprCombo',
						sortField: 'NephroDiagConfType_Code',
						comboSubject: 'NephroDiagConfType'
					}, {
						fieldLabel: 'Стадия ХБП',
						name: 'NephroCRIType_id',
						allowBlank: false,
						width: '100%',
						xtype: 'commonSprCombo',
						sortField: 'NephroCRIType_Code',
						comboSubject: 'NephroCRIType'
					}, {
						name: 'EvnNotifyNephro_IsHyperten',
						allowBlank: false,
						boxLabel: 'Артериальная гипертензия',
						padding: '0px 0px 0px 340px',
						width: 300,
						xtype: 'checkbox',
						inputValue: '2',
						uncheckedValue: '1'
					}, win.PersonParameters,
						{
							fieldLabel: langs('Назначенное лечение (диета, препараты)'),
							name: 'EvnNotifyNephro_Treatment',
							width: '100%',
							maxLength: 100,
							maxLengthText: langs('Максимальная длина этого поля 100 символов'),
							xtype: 'textfield'
						}]
				}, {
					xtype: 'fieldset',
					width: 700+32,
					//~ autoHeight: true,
					title: langs('Последние лабораторные данные'),
					//~ style: 'padding: 0; padding-left: 10px',
					defaults: {
						border: false,
						layout: 'column',
						padding: '0 0 5 5'
					},
					userCls: 'notify-nephro-fieldset',
					hidden: false,
						width: '100%',
						maxWidth: 735,
						//style: 'padding: 0; padding-left: 10px',
					items: [{
						layout: 'hbox',
						xtype: 'panel',
						margin: '0 0 5 10',
						border:false,
						items:[{
							fieldLabel: langs('Креатинин крови'),
							name: 'EvnNotifyNephro_Kreatinin',
							allowBlank: false,
							labelWidth: 335,
							width: 411,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
					}, {
						fieldLabel: langs('Гемоглобин'),
						name: 'EvnNotifyNephro_Haemoglobin',
						anchor:'100%',
							margin: '0 0 0 40',
							labelWidth: 170,
							width: 246,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
					}]
					}, {
						layout: 'hbox',
						xtype: 'panel',
						margin: '0 0 5 10',
						border: false,
						items: [{
							fieldLabel: langs('Белок мочи'),
							name: 'EvnNotifyNephro_Protein',
							allowBlank: false,
							anchor: '100%',
							labelWidth: 335,
							width: 411,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}, {
							fieldLabel: langs('Удельный вес'),
							name: 'EvnNotifyNephro_SpecWeight',
							anchor: '100%',
							margin: '0 0 0 40',
							labelWidth: 170,
							width: 246,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}]
					}, {
						layout: 'hbox',
						xtype: 'panel',
						margin: '0 0 5 10',
						border: false,
						items: [{
							fieldLabel: langs('Цилиндры'),
							name: 'EvnNotifyNephro_Cast',
							anchor: '100%',
							labelWidth: 335,
							width: 411,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}, {
							fieldLabel: langs('Лейкоциты'),
							name: 'EvnNotifyNephro_Leysk',
							margin: '0 0 0 40',
							labelWidth: 170,
							width: 246,
							anchor: '100%',
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}]
					}, {
						layout: 'hbox',
						xtype: 'panel',
						margin: '0 0 5 10',
						border: false,
						items: [{
							fieldLabel: langs('Эритроциты'),
							name: 'EvnNotifyNephro_Erythrocyt',
							allowBlank: false,
							anchor: '100%',
							labelWidth: 335,
							width: 411,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}, {
							fieldLabel: langs('Соли'),
							name: 'EvnNotifyNephro_Salt',
							anchor: '100%',
							margin: '0 0 0 40',
							labelWidth: 170,
							width: 246,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}]
					}, {
						layout: 'hbox',
						xtype: 'panel',
						margin: '0 0 5 10',
						border: false,
						items: [{
							fieldLabel: 'Мочевина',
							name: 'EvnNotifyNephro_Urea',
							allowBlank: false,
							anchor: '100%',
							labelWidth: 335,
							width: 411,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}, {
							fieldLabel: 'Клубочковая фильтрация',
							name: 'EvnNotifyNephro_GFiltration',
							allowBlank: false,
							anchor: '100%',
							margin: '0 0 0 40',
							labelWidth: 170,
							width: 246,
							xtype: 'numberfield',
							allowNegative: false,
							allowDecimals: false,
							decimalPrecision: 0,
							regex:new RegExp('(^[0-9]{0,3})$'),
							maxValue:999,
							maxLength: 3,
							maxLengthText: langs('Максимальная длина этого поля 3 символа')
						}]
					}]
					}, {
					layout: 'vbox',
					xtype: 'panel',
					width: '100%',
					margin: '0 0 0 16',
					border: false,
					defaults:{
						labelWidth: 335,
						maxWidth: 697
					},
					items: [{
						fieldLabel: langs('Дата заполнения'),
						name: 'EvnNotifyNephro_setDate',
						allowBlank: false,
						width: 335 + 111,
						xtype: 'datefield',
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: langs('Лечащий врач'),
						name: 'MedPersonal_id',
						listWidth: 750,
						width: '100%',
						xtype: 'SwMedStaffFactGlobalCombo',
						allowBlank: false,
						anchor: false,
						matchFieldWidth: false,
						listConfig:{
							userCls: 'swMedStaffFactSearch MedStaffFact_nephro'
						},
						valueField: 'MedPersonal_id',
						/*tpl: new Ext6.XTemplate( //перенес в базовый
							'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
							'<table style="border: 0; width: 400px;">',
							'<tr>',
							'<td width="250px"><div style="font: 13px Roboto; font-weight: 700; text-transform: capitalize !important;">{MedPersonal_Fio} </div></td>',
							'<td width="20px">&nbsp;</td>',
							'<td width="60px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Таб. номер</nobr></div></td>',
							'<td width="60px" style="padding-left: 29px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Код ЛЛО</nobr></div></td>',
							'</tr>',
							'<tr>',
							'<td width="250px"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
							'<p class="postMedName" data-qtip="{PostMed_Name}" style="padding-top: 2px">{PostMed_Name}</p>',
							'<p class="lpuSectionName" data-qtip="{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}">{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</p>',
							'<nobr>{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? " ставка" : ""]} {MedStaffFact_Stavka}</nobr>',
							'</p>',
							'<p class="postMedName">',
							'<nobr>{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name+"/ &nbsp":""]}</nobr>',
							'<nobr data-qtip="{[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Уволен с: " + this.formatDate(values.WorkData_endDate):""]}"><span style="color: red"> {[!Ext.isEmpty(values.WorkData_endDate) ?"Уволен с: " + this.formatDate(values.WorkData_endDate):"</span>"+[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]]}</nobr>&nbsp;',
							'</p></td>',
							'<td width="20px">&nbsp;</td>',
							'<td style="width: 60px; vertical-align: top;"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_TabCode}&nbsp;</p></td>',
							'<td style="width: 60px; vertical-align: top; padding-left: 29px"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_DloCode}&nbsp;</p></td>',
							'</tr></table>',
							'</div></tpl>',
							{
								formatDate: function(date) {
									return Ext6.util.Format.date(date, 'd.m.Y');
								}
							}
						)*/
					}, {
						fieldLabel: langs('Заведующий отделением'),
						name: 'MedPersonal_hid',
						listWidth: 750,
						width: '100%',
						xtype: 'SwMedStaffFactGlobalCombo',
						allowBlank: false,
						anchor: false,
						matchFieldWidth: false,
						listConfig:{
							userCls: 'swMedStaffFactSearch MedStaffFact_nephro'
						},
						valueField: 'MedPersonal_id',
						/*tpl: new Ext6.XTemplate( //перенес в базовый
							'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
							'<table style="border: 0; width: 400px;">',
							'<tr>',
							'<td width="250px"><div style="font: 13px Roboto; font-weight: 700; text-transform: capitalize !important;">{MedPersonal_Fio} </div></td>',
							'<td width="20px">&nbsp;</td>',
							'<td width="60px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Таб. номер</nobr></div></td>',
							'<td width="60px" style="padding-left: 29px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Код ЛЛО</nobr></div></td>',
							'</tr>',
							'<tr>',
							'<td width="250px"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
							'<p class="postMedName" data-qtip="{PostMed_Name}" style="padding-top: 2px">{PostMed_Name}</p>',
							'<p class="lpuSectionName" data-qtip="{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}">{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</p>',
							'<nobr>{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? " ставка" : ""]} {MedStaffFact_Stavka}</nobr>',
							'</p>',
							'<p class="postMedName">',
							'<nobr>{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name+"/ &nbsp":""]}</nobr>',
							'<nobr data-qtip="{[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Уволен с: " + values.WorkData_endDate:""]}"><span style="color: red"> {[!Ext.isEmpty(values.WorkData_endDate) ?"Уволен с: " + this.formatDate(values.WorkData_endDate):"</span>"+[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]]}</nobr>&nbsp;',
							'</p></td>',
							'<td width="20px">&nbsp;</td>',
							'<td style="width: 60px; vertical-align: top;"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_TabCode}&nbsp;</p></td>',
							'<td style="width: 60px; vertical-align: top; padding-left: 29px"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_DloCode}&nbsp;</p></td>',
							'</tr></table>',
							'</div></tpl>',
							{
								formatDate: function(date) {
									return Ext6.util.Format.date(date, 'd.m.Y');
								}
							}
						)*/
					}]
				}]
				}],
			url:'/?c=MorbusNephro&m=doSaveEvnNotifyNephro'
		});

		Ext6.apply(win, {
			layout: 'border',
			items: [
				win.FormPanel
			],
			buttons: ['->',
			{
				xtype: 'SimpleButton',
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel'
			},
			{
				xtype: 'SubmitButton',
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save'
			}
			]
		});

		this.callParent(arguments);
	}
});