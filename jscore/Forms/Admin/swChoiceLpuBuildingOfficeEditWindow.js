/**
 * swChoiceLpuBuildingOfficeEditWindow - Выбор кабинета
 *
 * Форма предназначена для изменения выбранной на форме «Расписание работы врачей» связи Кабинет – Место работы,
 * открывается по нажатию на номер кабинета в области данных формы «Расписание работы врачей».
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author      Bykov Stanislav
 * @version     11.2017
 */
sw.Promed.swChoiceLpuBuildingOfficeEditWindow = Ext.extend(sw.Promed.BaseForm, {

	title: 'Выбор кабинета',
	autoHeight: true,
	id: 'swChoiceLpuBuildingOfficeEditWindow',
	layout: 'form',
	maximizable: false,
	modal: true,
	resizable: false,

	width: 600,

	FormPanel: null,
	LpuBuildingOffice_id: null,
	formParams: null,

	// нужен для того, чтобы не было множества одновременных запросов на сохранение данных
	formStatus: 'edit',
	Lpu_id: null,
	CurrentLpuBuildingOffice_id: null,

	_filterFieldCombo: function(field, data){

		field.getStore().removeAll();
		field.clearValue();
		field.getStore().load(data);

		return true;
	},



	getLpuId: function(){
		var me = this;

		if(Ext.isEmpty(me.Lpu_id)){
			me.setLpuId(getGlobalOptions().lpu_id);
		}

		return me.Lpu_id;
	},

	setLpuId: function(Lpu_id){
		var me = this;

		me.Lpu_id = Lpu_id;

		return me;
	},

	// Фильтр выпадающего списка "Кабинет"
	filterLpuBuildingOfficeCombo: function(params) {
		var me = this;

		var FormPanel = me.FormPanel.getForm();
		me._filterFieldCombo(FormPanel.findField('LpuBuildingOffice_id'), {params: params});


		return true;
	},

	doSave: function(options) {

		if (typeof options != 'object'){options = new Object();}

		var me = this;

		// Если сохранение уже запущено
		if ( me.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var params = {};
		if(options.checkDatesToChangeOfficeNumber == true){
			params.checkDatesToChangeOfficeNumber = 1;
		}



		var formFormPanel = me.FormPanel.getForm();

		var newLpuBuildingOffice_id = formFormPanel.findField('LpuBuildingOffice_id').getValue();

		// Если кабинет не меняется, то ничего не делаем т.к. даты мы можем изменить только если изменим кабинет
		if(me.CurrentLpuBuildingOffice_id == newLpuBuildingOffice_id){
			me.formStatus = 'edit';
			me.getLoadMask().hide();
			getWnd('swLpuBuildingScheduleWorkDoctorWindow').doSearch();
			me.hide();
		} else {
			formFormPanel.submit({

				params: params,

				failure: function(result_form, action)
				{
					me.formStatus = 'edit';
					me.getLoadMask().hide();
					if (action.result) {
						if ( ! Ext.isEmpty(action.result.Alert_Msg) ) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										switch ( true ) {
											case (1 == action.result.Error_Code):
												options.checkDatesToChangeOfficeNumber = true;
												break;
										}

										me.doSave(options);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: langs('Вопрос')
							});
						} else {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
					} else {
						sw.swMsg.alert(langs('Ошибка'), 'При сохранении возникли ошибки');
					}

				},
				success: function(result_form, action)
				{

					me.formStatus = 'edit';

					// Если изменили кабинет (с сохранением), то указываем его в качестве текущего
					me.CurrentLpuBuildingOffice_id = newLpuBuildingOffice_id;

					me.getLoadMask().hide();
					getWnd('swLpuBuildingScheduleWorkDoctorWindow').doSearch();
					// var data = {};
					// if (action.result) {
					// 	data = action.result;
					// }
					// me.callback(data);
					me.hide();
				}
			});
		}


		return true;

	},

	_resetForm: function()
	{
		this.FormPanel.getForm().reset();
		// this.formMode = 'remote';
		this.FormPanel.getForm().setValues(this.formParams);
	},

	show: function() {



		sw.Promed.swChoiceLpuBuildingOfficeEditWindow.superclass.show.apply(this, arguments);

		var me = this;

		var FormPanel = me.FormPanel.getForm();

		me.callback = Ext.emptyFn;

		if ( ! arguments[0] || typeof arguments[0].formParams != 'object' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: langs('Ошибка'),
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				fn: function() {
					me.hide();
				}
			});

			return false;
		}

		me.filterLpuBuildingOfficeCombo({Lpu_id: me.getLpuId()});

		me.formParams = arguments[0].formParams;

		// запоминаем текущий кабинет
		me.CurrentLpuBuildingOffice_id = me.formParams.LpuBuildingOffice_id;

		me._resetForm();
		me._loadForm();



		return true;
	},

	_loadForm: function(){
		var me = this;

		me.getLoadMask().show(LOAD_WAIT);
		me.getLoadMask().hide();
	},

	initComponent: function() {
		var me = this;

		me.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 200,
			region: 'north',
			url: '/?c=LpuBuildingOfficeMedStaffLink&m=saveChoiceLpuBuildingOffice',

			items: [

				{
					name: 'LpuBuildingOffice_Number',
					xtype: 'hidden'
				},

				{
					name: 'Person_Fio',
					xtype: 'hidden'
				},

				{
					name: 'LpuBuildingOfficeMedStaffLink_id',
					xtype: 'hidden'
				},

				{
					name: 'MedStaffFact_id',
					xtype: 'hidden'
				},

				// Кабинет
				{
					allowBlank: false,
					fieldLabel: 'Кабинет',
					name: 'LpuBuildingOffice_id',
					width: 350,
					xtype: 'swlpubuildingofficecombo',
					listeners: {
						'change': function(combo, newValue, oldValue){
							var formFormPanel = me.FormPanel.getForm();


							var record = combo.getStore().getById(newValue);
							var LpuBuildingOffice_Display = record.get('LpuBuildingOffice_Display');
							formFormPanel.findField('LpuBuildingOffice_Number').setValue(LpuBuildingOffice_Display);

							if(newValue == me.CurrentLpuBuildingOffice_id){
								formFormPanel.findField('LpuBuildingOfficeMedStaffLink_begDate').disable();
								formFormPanel.findField('LpuBuildingOfficeMedStaffLink_endDate').disable();
							} else {
								formFormPanel.findField('LpuBuildingOfficeMedStaffLink_begDate').enable();
								formFormPanel.findField('LpuBuildingOfficeMedStaffLink_endDate').enable();
							}

						}
					}
				},




				// Дата начала
				{
					allowBlank: false,
					fieldLabel: 'Дата начала',
					name: 'LpuBuildingOfficeMedStaffLink_begDate',
					format: 'd.m.Y',
					width: 100,
					disabled: 'disabled',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					xtype: 'swdatefield'
				},


				// Дата окончания
				{
					allowBlank: true,
					fieldLabel: 'Дата окончания',
					name: 'LpuBuildingOfficeMedStaffLink_endDate',
					format: 'd.m.Y',
					width: 100,
					disabled: 'disabled',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					xtype: 'swdatefield'
				}
			] ,

			reader: new Ext.data.JsonReader({success: Ext.emptyFn},[
				{name: 'LpuBuildingOffice_Number'},
				{name: 'Person_Fio'},
				{name: 'LpuBuildingOfficeMedStaffLink_id'},
				{name: 'MedStaffFact_id'},
				{name: 'LpuBuildingOffice_id'},
				{name: 'LpuBuildingOfficeMedStaffLink_begDate'},
				{name: 'LpuBuildingOfficeMedStaffLink_endDate'}
			])

		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					me.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					me.hide();
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				me.FormPanel
			]
		});

		sw.Promed.swChoiceLpuBuildingOfficeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});