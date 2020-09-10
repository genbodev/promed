/**
 * swChoiceVizitTimeEditWindow - Выбор времени приёма
 *
 * Форма предназначена для изменения времени приёма врача в соответствующем кабинете в выбранный день недели,
 * открывается по нажатию на время приёма в области данных формы «Расписание работы врачей».
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
sw.Promed.swChoiceVizitTimeEditWindow = Ext.extend(sw.Promed.BaseForm, {

	autoHeight: true,
	id: 'swChoiceVizitTimeEditWindow',
	layout: 'form',
	maximizable: false,
	modal: true,
	resizable: false,
	title: 'Выбор времени приёма',
	width: 600,

	FormPanel: null,
	formParams: null,
	Lpu_id: null,

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

	show: function() {
		sw.Promed.swChoiceVizitTimeEditWindow.superclass.show.apply(this, arguments);

		var me = this;


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

		me.formParams = arguments[0].formParams;
		me._resetForm();
		me._loadForm();


		return true;


	},

	_resetForm: function()
	{
		this.FormPanel.getForm().reset();
		// this.formMode = 'remote';
		this.FormPanel.getForm().setValues(this.formParams);
	},

	_loadForm: function(){
		var me = this;

		me.getLoadMask().show(LOAD_WAIT);
		me.getLoadMask().hide();
	},

	/* конструктор */
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
			url: '/?c=LpuBuildingOfficeMedStaffLink&m=saveChoiceVizitTime',

			items: [

				{
					name: 'LpuBuildingOfficeMedStaffLink_id',
					xtype: 'hidden'
				},

				{
					name: 'LpuBuildingOfficeVizitTime_id',
					xtype: 'hidden'
				},


				{
					name: 'CalendarWeek_id',
					xtype: 'hidden'
				},


				{
					name: 'curDate',
					xtype: 'hidden'
				},


				{
					border: false,
					layout: 'column',
					items: [

						// с
						{
							allowBlank: false,
							border: false,
							labelWidth: 20,
							layout: 'form',
							xtype: 'panel',
							items: [
								{
									fieldLabel: 'с',
									name: 'LpuBuildingOfficeVizitTime_begDate',
									plugins: [
										new Ext.ux.InputTextMask('99:99', true)
									],
									xtype: 'swtimefield'
								}
							]
						},

						// по
						{
							allowBlank: false,
							border: false,
							labelWidth: 30,
							layout: 'form',
							xtype: 'panel',
							items: [
								{
									fieldLabel: 'по',
									name: 'LpuBuildingOfficeVizitTime_endDate',
									plugins: [
										new Ext.ux.InputTextMask('99:99', true)
									],
									xtype: 'swtimefield'
								}
							]
						}
					]
				},


				// Период, на который необходимо изменить время приёма врача в выбранный день недели – <день недели>
				{
					xtype: 'radio',
					checked: true,
					hideLabel: true,
					inputValue: 1,
					boxLabel: langs('Один день'),
					name: 'LpuBuildingOfficeVizitTime_period'

				},

				{
					xtype: 'radio',
					hideLabel: true,
					inputValue: 2,
					boxLabel: langs('Весь период работы врача в кабинете'),
					name: 'LpuBuildingOfficeVizitTime_period'

				}
			] // ,

			// reader: new Ext.data.JsonReader({success: Ext.emptyFn},[
			// 	{name: 'LpuBuildingOfficeMedStaffLink_id'}
			// ])

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

		sw.Promed.swChoiceVizitTimeEditWindow.superclass.initComponent.apply(this, arguments);
	},


	doSave: function(){
		var me = this;

		var formFormPanel = me.FormPanel.getForm();
		var params = {};

		formFormPanel.submit({

			params: params,

			failure: function(result_form, action)
			{
				me.getLoadMask().hide();
				if (action.result) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
				} else {
					sw.swMsg.alert(langs('Ошибка'), 'При сохранении возникли ошибки');
				}

			},
			success: function(result_form, action)
			{

				me.getLoadMask().hide();
				getWnd('swLpuBuildingScheduleWorkDoctorWindow').doSearch();
				me.hide();
			}
		});
		return true;
	}

});