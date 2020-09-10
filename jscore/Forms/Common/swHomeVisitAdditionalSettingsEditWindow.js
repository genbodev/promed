/**
 * swHomeVisitAdditionalSettingsEditWindow - окно редактирования дополнительных дней вызова врача на дом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.10.2013
 */

sw.Promed.swHomeVisitAdditionalSettingsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swHomeVisitAdditionalSettingsEditWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 600,

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDateFieldValue = base_form.findField('HomeVisitAdditionalSettings_begDate'),
			endDateFieldValue = base_form.findField('HomeVisitAdditionalSettings_endDate'),
			begTimeFieldValue = base_form.findField('HomeVisitAdditionalSettings_begTime'),
			endTimeFieldValue = base_form.findField('HomeVisitAdditionalSettings_endTime'),
			firstDate = Date.parseDate((begDateFieldValue.value + ' ' + begTimeFieldValue.getValue()), 'd.m.Y H:s'),
			secondDate = Date.parseDate((endDateFieldValue.value + ' ' + endTimeFieldValue.getValue()), 'd.m.Y H:s');

		if ( !Ext.isEmpty(begTimeFieldValue.getValue()) && !Ext.isEmpty(endTimeFieldValue.getValue()) && begTimeFieldValue.getValue() >= endTimeFieldValue.getValue() ) {
			sw.swMsg.alert('Ошибка', 'Время окончания работы сервиса должно быть больше Времени начала работы сервиса', function(){
				begTimeFieldValue.markInvalid();
				endTimeFieldValue.markInvalid();
			});
			return false;
		}

		if ( !Ext.isEmpty(begDateFieldValue.getValue()) && !Ext.isEmpty(endDateFieldValue.getValue()) && begDateFieldValue.getValue() > endDateFieldValue.getValue() ) {
			sw.swMsg.alert('Ошибка', 'Дата окончания работы сервиса должна быть больше или равна Дате начала работы сервиса', function(){
				begDateFieldValue.markInvalid();
				endDateFieldValue.markInvalid();
			});
			return false;
		}

		if ( begDateFieldValue.getValue() < new Date().clearTime() && endDateFieldValue.getValue() < new Date().clearTime()){
			sw.swMsg.alert('Ошибка', 'Дата начала и дата окончания работы сервиса должны быть больше или равны текущей дате', function(){
				begDateFieldValue.markInvalid();
				endDateFieldValue.markInvalid();
			});
			return false;
		}

		wnd.getLoadMask().show();

		var params = new Object();

		//params = base_form.getValues();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
			},
			//params: params,
			success: function(result_form, action) {
				wnd.getLoadMask().hide();

				if ( action.result ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			}
		});
	},

	show: function() {
		sw.Promed.swHomeVisitAdditionalSettingsEditWindow.superclass.show.apply(this, arguments);

		this.action = null;
		var form = this;
		var base_form = form.FormPanel.getForm();

		if ( arguments && arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		base_form.reset();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var begTime = base_form.findField('HomeVisitAdditionalSettings_begTime'),
			endTime = base_form.findField('HomeVisitAdditionalSettings_endTime');

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(langs('Дополнительный период работы/выходных: Добавление'));

				base_form.clearInvalid();

				this.typeCombo.validate();
				base_form.findField('HomeVisitAdditionalSettings_begDate').validate();
				base_form.findField('HomeVisitAdditionalSettings_endDate').validate();

				begTime.disable();
				endTime.disable();
				loadMask.hide();
				break;
//
			case 'edit':
			case 'view':
				this.setTitle(langs('Дополнительный период работы/выходных: Редактирование'));

				if(arguments[0].formParams){
					form.loadForm(arguments[0].formParams.HomeVisitAdditionalSettings_id, base_form)
				}

				base_form.clearInvalid();

				this.typeCombo.validate();
				base_form.findField('HomeVisitAdditionalSettings_begDate').validate();
				base_form.findField('HomeVisitAdditionalSettings_endDate').validate();

				begTime.enable();
				endTime.enable();

//				var coeff_index_id = base_form.findField('CoeffIndex_id').getValue();
//
//				if ( !coeff_index_id ) {
//					loadMask.hide();
//					this.hide();
//					return false;
//				}
//
//				var afterFormLoad = function() {
//					loadMask.hide();
//					if ( form.action == 'edit' ) {
//						form.setTitle(langs('Коэффициент индексации: Редактирование'));
//						form.enableEdit(true);
//					}
//					else {
//						form.setTitle(langs('Коэффициент индексации: Просмотр'));
//						form.enableEdit(false);
//					}
//					base_form.clearInvalid();
//					if ( form.action == 'edit' ) {
//						base_form.findField('CoeffIndex_Code').focus(true, 250);
//					}
//					else {
//						form.buttons[form.buttons.length - 1].focus();
//					}
//				};
//
//				base_form.load({
//					params: {CoeffIndex_id: coeff_index_id},
//					failure: function() {
//						afterFormLoad();
//					},
//					success: function() {
//						afterFormLoad();
//					},
//					url: '/?c=CoeffIndex&m=loadCoeffIndexEditForm'
//				});

				loadMask.hide();
				break;

			default:
				this.hide();
				break;
		}
	},

	loadForm: function(id, form){
		Ext.Ajax.request({
			params: {
				HomeVisitAdditionalSettings_id: id
			},
			callback: function (opt, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if(response_obj.data){
						var formData = response_obj.data[0];

						form.setValues(formData);
					}

				}
			}.createDelegate(this),
			url: '/?c=HomeVisit&m=loadHomeVisitAdditionalSettings'
		});
	},

	initComponent: function() {
		var form = this;
		
		this.typeCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Тип периода',
			width: 250,
			triggerAction: 'all',
			store: [
				[1, langs('Дополнительные дни работы')],
				[2, langs('Дополнительные выходные')]				
			],
			hiddenName: 'HomeVisitPeriodType_id',
			name: 'HomeVisitPeriodType_Name',
			listeners: {
				'change': function(element, newValue){
					var base_form = form.FormPanel.getForm();

					var begTime = base_form.findField('HomeVisitAdditionalSettings_begTime'),
						endTime = base_form.findField('HomeVisitAdditionalSettings_endTime');

					begTime.reset();
					endTime.reset();

					if(newValue == 1){// поле "Время с" обязательно при выборе "Дополнительные дни работы"
						begTime.reset();
						endTime.reset();
						begTime.enable();
						endTime.enable();
					}else{
						begTime.disable();
						endTime.disable();
					}
					begTime.validate();
					endTime.validate();
				}
			}
		});

		this.FormPanel = new Ext.form.FormPanel({
			bodyStyle: '{padding-top: 0.5em;}',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
			//layout: 'form',
			id: 'HomeVisitAdditionalSettingsEditForm',
			url: '/?c=HomeVisit&m=saveHomeVisitAdditionalSettings',
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}),
			items: [
				{
					xtype: 'hidden',
					name: 'HomeVisitAdditionalSettings_id'
				},
				form.typeCombo,
				{
					xtype: 'container',
					autoEl: {},
					layout: 'column',
					items: [
						{
							xtype: 'container',
							autoEl: {},
							layout: 'form',
							items:[
								{
									fieldLabel: langs('Дата с'),
									allowDecimals: true,
									allowNegative: false,
									name: 'HomeVisitAdditionalSettings_begDate',
									xtype: 'swdatefield',
									allowBlank: false,
									width: 90
								}
							]
						},
						{
							xtype: 'container',
							autoEl: {},
							layout: 'form',
							labelWidth: 65,
							items:[
								{
									fieldLabel: langs('Дата по'),
									allowDecimals: true,
									allowNegative: false,
									name: 'HomeVisitAdditionalSettings_endDate',
									xtype: 'swdatefield',
									allowBlank: false,
									width: 90
								}
							]
						},
					]
				},
				{
					xtype: 'fieldset',
					autoHeight: true,
					style: 'margin: 0; padding: 0;',
					title: 'Расписание работы сервиса',
					items: [
						{
							xtype: 'container',
							autoEl: {},
							layout: 'column',
							items: [
								{
									xtype: 'container',
									autoEl: {},
									layout: 'form',
									items:[
										{
											fieldLabel: langs('Время начала'),
											allowDecimals: true,
											allowNegative: false,
											name: 'HomeVisitAdditionalSettings_begTime',
											xtype: 'swtimefield',
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											allowBlank: false,
											width: 60
										}
									]
								},
								{
									xtype: 'container',
									autoEl: {},
									layout: 'form',
									labelWidth: 125,
									items:[
										{
											fieldLabel: langs('Время окончания'),
											allowDecimals: true,
											allowNegative: false,
											name: 'HomeVisitAdditionalSettings_endTime',
											xtype: 'swtimefield',
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											allowBlank: false,
											width: 60
										}
									]
								},
							]
						},
					]
				}
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'CIEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'CIEW_HelpButton',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'CIEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swHomeVisitAdditionalSettingsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
