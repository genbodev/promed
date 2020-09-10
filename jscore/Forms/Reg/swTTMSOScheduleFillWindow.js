/**
* swTTMSOScheduleFillWindow - окно создания расписания для службы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      28.12.2011
*/

/*NO PARSE JSON*/
sw.Promed.swTTMSOScheduleFillWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['sozdanie_raspisaniya'],
	id: 'TTMSOScheduleFillWindow',
	layout: 'border',
	maximizable: false,
	width: 450,
	height: 270,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTMSOScheduleFillWindow',
	objectSrc: '/jscore/Forms/Reg/swTTMSOScheduleFillWindow.js',
	
	/**
	 * Идентификатор службы, с расписанием которого мы работаем
	 */
	MedService_id: null,
	
	/**
	 * Идентификатор услуги, с расписанием которого мы работаем
	 */
	UslugaComplexMedService_id: null,
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTMSOScheduleFillWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['MedService_id']) {
			this.MedService_id = arguments[0]['MedService_id'];
		} else {
			this.MedService_id = null;
		}
		
		if (arguments[0]['UslugaComplexMedService_id']) {
			this.UslugaComplexMedService_id = arguments[0]['UslugaComplexMedService_id'];
		} else {
			this.UslugaComplexMedService_id = null;
		}
		
		var form = this.findById('TTMSOCreateScheduleForm');
		form.findById('TTMSOsfCopyToDate').setContainerVisible(form.getForm().findField('TTMSOsfScheduleCreationType').getValue() == 2);
		form.findById('TTMSOsfCopyToDate').setValue('');
		if (arguments[0]['date']) {
			form.findById('TTMSOsfCreateDate').setValue(arguments[0]['date'] + ' - ' + arguments[0]['date']);
		} else {
			form.findById('TTMSOsfCreateDate').setValue('');
		}
		form.findById('TTMSOsfDuration').setValue(15);
	},
	doSave: function() 
	{
		var form = this.findById('TTMSOCreateScheduleForm');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('TTMSOCreateScheduleForm'), { msg: "Подождите, идет создание новых бирок..." });
		loadMask.show();
		
		var post = [];
		post['MedService_id'] = this.MedService_id;
		post['UslugaComplexMedService_id'] = this.UslugaComplexMedService_id;
		form.getForm().submit({
			timeout: 1500, // 1500 секунд = 25 минут
			params: post,
			failure: function(result_form, action) 
			{
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
					else
					{
						//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				this.hide();
				this.returnFunc();
			}.createDelegate(this)
		});
		return true;
	},

	initComponent: function() 
	{
	var MainPanel = new sw.Promed.FormPanel(
		{
			id:'TTMSOCreateScheduleForm',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 150,
			items:
			[
			{
				anchor: '100%',
				name: 'ScheduleCreationType',
				tabIndex: TABINDEX_TTMSSF + 1,
				xtype: 'swschedulecreationtypecombo',
				id: 'TTMSOsfScheduleCreationType',
				allowBlank: false,
				fieldLabel: lang['variant_sozdaniya'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var form = this.findById('TTMSOCreateScheduleForm');
						form.findById('TTMSOsfCopyToDate').setAllowBlank(newValue != 2);
						if (newValue == 1) {
							form.findById('TTMSOsfCopyToDate').hideContainer();
							form.findById('TTMSOsfCreateSchedulePanel').show();
							form.findById('TTMSOsfCopySchedulePanel').hide();
							form.findById('TTMSOsfCreateDate').setFieldLabel(lang['sozdat_na_datyi']);
						}
							
						if (newValue == 2) {
							form.findById('TTMSOsfCopyToDate').showContainer();
							form.findById('TTMSOsfCreateSchedulePanel').hide();
							form.findById('TTMSOsfCopySchedulePanel').show();
							form.findById('TTMSOsfCreateDate').setFieldLabel(lang['kopirovat_iz_diapazona']);
						}
							
					}.createDelegate(this),
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.data.ScheduleCreationType, null);
					}
				},
				value: 1
			},
			{
				allowBlank: false,
				fieldLabel: lang['sozdat_na_datyi'],
				tabIndex: TABINDEX_TTMSSF + 2,
				id: 'TTMSOsfCreateDate',
				name: 'CreateDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				xtype: 'daterangefield'
			},
			{
				fieldLabel: 'Вставить в диапазон',
				id: 'TTMSOsfCopyToDate',
				name: 'CopyToDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				tabIndex: TABINDEX_TTMSSF + 3,
				xtype: 'daterangefield'
			},
			{
				hidden: true,
				xtype: 'fieldset',
				height: 70,
				id: 'TTMSOsfCopySchedulePanel',
				title: '',
				labelWidth: 200,
				items: [{
					fieldLabel: lang['kopirovat_primechaniya_na_den'],
					id: 'TTMSOsfCopyDayComments',
					name: 'CopyDayComments',
					tabIndex: TABINDEX_TTMSSF + 4,
					width: 180,
					xtype: 'checkbox'
				},
				{
					fieldLabel: lang['kopirovat_primechaniya_na_birku'],
					id: 'TTMSOsfCopyTTMSOComments',
					name: 'CopyTTMSOComments',
					tabIndex: TABINDEX_TTMSSF + 5,
					width: 180,
					xtype: 'checkbox'
				}]
			},
			{
				xtype: 'fieldset',
				height: 120,
				id: 'TTMSOsfCreateSchedulePanel',
				title: '',
				items: [{
					id: 'TTMSOsfStartDay',
					fieldLabel: lang['nachalo_rabotyi'],
					name: 'StartTime',
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					tabIndex: TABINDEX_TTMSSF + 5,
					validateOnBlur: false,
					value: '08:00',
					width: 60,
					xtype: 'swtimefield'
				},
				{
					id: 'TTMSOsfEndDay',
					fieldLabel: lang['okonchanie_rabotyi'],
					labelWidth: 100,
					name: 'EndTime',
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					tabIndex: TABINDEX_TTMSSF + 6,
					validateOnBlur: false,
					value: '17:00',
					width: 60,
					xtype: 'swtimefield'
				},
				{
					id: 'TTMSOsfDuration',
					xtype: 'textfield',
					maskRe: /\d/,
					fieldLabel: lang['dlitelnost_priema_min'],
					minLength: 1,
					autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
					width: 50,
					name: 'Duration',
					tabIndex: TABINDEX_TTMSSF + 7,
					value: 15
				}]
			}
			],
			url: C_TTMSO_CREATESCHED
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['sozdat_raspisanie'],
				id: 'TTMSOsfCreate',
				tabIndex: TABINDEX_TTMSSF + 10,
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this),
				tabIndex: TABINDEX_TTMSSF + 11
			},
			{
				text: BTN_FRMCANCEL,
				id: 'TTMSOsfCancel',
				tabIndex: TABINDEX_TTMSSF + 12,
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('TTMSOsfScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('TTMSOsfCreate').focus();
				}.createDelegate(this),
				handler: function()
				{
					this.hide();
					//this.returnFunc(this.owner, -1);
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
					return true;
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTTMSOScheduleFillWindow.superclass.initComponent.apply(this, arguments);
	}
});