/**
* swTTMSScheduleFillWindow - окно создания расписания для службы
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
sw.Promed.swTTMSScheduleFillWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['sozdanie_raspisaniya'],
	id: 'TTMSScheduleFillWindow',
	layout: 'border',
	maximizable: false,
	width: 450,
	height: 420,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTMSScheduleFillWindow',
	objectSrc: '/jscore/Forms/Reg/swTTMSScheduleFillWindow.js',
	
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
		sw.Promed.swTTMSScheduleFillWindow.superclass.show.apply(this, arguments);
		
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
		
		var form = this.findById('TTMSCreateScheduleForm');
		form.findById('ttmssfCopyToDate').setContainerVisible(form.getForm().findField('ttmssfScheduleCreationType').getValue() == 2);
		form.findById('ttmssfCopyToDate').setValue('');
		if (arguments[0]['date']) {
			form.findById('ttmssfCreateDate').setValue(arguments[0]['date'] + ' - ' + arguments[0]['date']);
		} else {
			form.findById('ttmssfCreateDate').setValue('');
		}
		form.findById('ttmssfDuration').setValue(15);
		this.setHeight(getRegionNick() != 'kareliya' ? 275 : 400);
		
		if ( !(form.getForm().findField('ttmssfTimetableType').getStore().place && form.getForm().findField('ttmssfTimetableType').getStore().place == 3) ) {
			form.getForm().findField('ttmssfTimetableType').getStore().load({
				params: {
					Place_id: 3
				},
				callback: function () {
					form.getForm().findField('ttmssfTimetableType').setValue(1);
					form.getForm().findField('ttmssfTimetableType').getStore().place = 3;
				}
			});
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTMSCreateScheduleForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTMSCreateScheduleForm'), { msg: "Подождите, идет создание новых бирок..." });
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
			id:'TTMSCreateScheduleForm',
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
				id: 'ttmssfScheduleCreationType',
				allowBlank: false,
				fieldLabel: lang['variant_sozdaniya'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var form = this.findById('TTMSCreateScheduleForm');
						form.findById('ttmssfTimetableType').setAllowBlank(newValue != 1);
						form.findById('ttmssfCopyToDate').setAllowBlank(newValue != 2);
						if (newValue == 1) {
							form.findById('ttmssfCopyToDate').hideContainer();
							form.findById('ttmssfCreateSchedulePanel').show();
							form.findById('ttmssfCopySchedulePanel').hide();
							form.findById('ttmssfCreateDate').setFieldLabel(lang['sozdat_na_datyi']);
						}
							
						if (newValue == 2) {
							form.findById('ttmssfCopyToDate').showContainer();
							form.findById('ttmssfCreateSchedulePanel').hide();
							form.findById('ttmssfCopySchedulePanel').show();
							form.findById('ttmssfCreateDate').setFieldLabel(lang['kopirovat_iz_diapazona']);
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
				id: 'ttmssfCreateDate',
				name: 'CreateDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				xtype: 'daterangefield'
			},
			{
				fieldLabel: 'Вставить в диапазон',
				id: 'ttmssfCopyToDate',
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
				id: 'ttmssfCopySchedulePanel',
				title: '',
				labelWidth: 200,
				items: [{
					fieldLabel: lang['kopirovat_primechaniya_na_den'],
					id: 'ttmssfCopyDayComments',
					name: 'CopyDayComments',
					tabIndex: TABINDEX_TTMSSF + 4,
					width: 180,
					xtype: 'checkbox'
				},
				{
					fieldLabel: lang['kopirovat_primechaniya_na_birku'],
					id: 'ttmssfCopyTTMSComments',
					name: 'CopyTTMSComments',
					tabIndex: TABINDEX_TTMSSF + 5,
					width: 180,
					xtype: 'checkbox'
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				id: 'ttmssfCreateSchedulePanel',
				title: '',
				items: [{
					id: 'ttmssfStartDay',
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
					id: 'ttmssfEndDay',
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
					id: 'ttmssfDuration',
					xtype: 'textfield',
					maskRe: /\d/,
					fieldLabel: lang['dlitelnost_priema_min'],
					minLength: 1,
					autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
					width: 50,
					name: 'Duration',
					tabIndex: TABINDEX_TTMSSF + 7,
					value: 15
				},
				{
					anchor: '100%',
					xtype: 'swtimetabletypecombo',
					tabIndex: TABINDEX_TTMSSF + 8,
					hiddenName: 'TimetableType_id',
					id: 'ttmssfTimetableType',
					allowBlank: false
				},
				{
					labelAlign: 'top',
					layout: 'form',
					border: false,
					hidden: (getRegionNick() != 'kareliya'),
					items: 
					[{
						anchor: '100%',
						fieldLabel : lang['primechanie'],
						height: 100,
						name: 'TimetableExtend_Descr',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'ttmssfTimetableExtend_Descr'
					}]
				}]
			}
			],
			url: C_TTMS_CREATESCHED
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['sozdat_raspisanie'],
				id: 'ttmssfCreate',
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
				id: 'ttmssfCancel',
				tabIndex: TABINDEX_TTMSSF + 12,
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttmssfScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttmssfCreate').focus();
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
		sw.Promed.swTTMSScheduleFillWindow.superclass.initComponent.apply(this, arguments);
	}
});