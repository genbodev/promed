/**
* swTTSScheduleFillWindow - окно создания расписания в стационаре
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      19.03.2012
*/

/*NO PARSE JSON*/
sw.Promed.swTTSScheduleFillWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['sozdanie_raspisaniya'],
	id: 'TTSScheduleFillWindow',
	layout: 'border',
	maximizable: false,
	width: 450,
	height: 570,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTSScheduleFillWindow',
	objectSrc: '/jscore/Forms/Reg/swTTSScheduleFillWindow.js',
	
	/**
	 * Идентификатор отделения, с которым мы работаем
	 */
	LpuSection_id: null,
	
	returnFunc: function(owner) {},
	recalcMaxRecords: function () {
		var form = this.findById('TTSCreateScheduleForm');
		startDay = Date.parse(form.findById('ttmssfStartDay').getValue());
		endDay = Date.parse(form.findById('ttmssfEndDay').getValue());
		duration = form.findById('ttmssfDuration').getValue();

		if (!Ext.isEmpty(startDay) && !Ext.isEmpty(endDay) && !Ext.isEmpty(duration) && (endDay >= startDay) && (duration > 0)) {
			form.findById('ttssfMaxRecordCount').setValue(
				((
					endDay - startDay
				) / 1000 / 60 / duration).toFixed(0)
			);
		}
		else {
			form.findById('ttssfMaxRecordCount').setValue(null);
		}

	},
	show: function() 
	{
		sw.Promed.swTTSScheduleFillWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['LpuSection_id']) {
			this.LpuSection_id = arguments[0]['LpuSection_id'];
		}
		
		var form = this.findById('TTSCreateScheduleForm');
		form.findById('ttssfCopyToDate').setContainerVisible(form.getForm().findField('ttssfScheduleCreationType').getValue() == 2);
		if (arguments[0]['date']) {
			form.findById('ttssfCreateDate').setValue(arguments[0]['date'] + ' - ' + arguments[0]['date']);
		} else {
			form.findById('ttssfCreateDate').setValue('');
		}

		if ((getRegionNick().inlist(['vologda', 'msk', 'kz', 'ufa']) && getStacOptions().stac_schedule_time_binding == 1)) {
			form.findById('ttmssfStartDay').setValue('08:00');
			form.findById('ttmssfEndDay').setValue('10:00');
			form.findById('ttmssfDuration').setValue('15');
			this.recalcMaxRecords();
			form.findById('ttmssfStartDay').addListener('change', this.recalcMaxRecords.createDelegate(this));
			form.findById('ttmssfEndDay').addListener('change', this.recalcMaxRecords.createDelegate(this));
			form.findById('ttmssfDuration').addListener('change', this.recalcMaxRecords.createDelegate(this));
		}
		else {
			form.findById('ttmssfStartDay').setValue('');
			form.findById('ttmssfEndDay').setValue('');
			form.findById('ttmssfDuration').setValue('');
		}
		if ( !(form.getForm().findField('ttssfTimetableType').getStore().place && form.getForm().findField('ttssfTimetableType').getStore().place == 2) ) {
			form.getForm().findField('ttssfTimetableType').getStore().load({
				params: {
					Place_id: 2
				},
				callback: function () {
					form.getForm().findField('ttssfTimetableType').setValue(1);
					form.getForm().findField('ttssfTimetableType').getStore().place = 2;
				}
			});
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTSCreateScheduleForm');
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
		if(form.findById('ttssfScheduleCreationType').getValue() == 1
			&& !getStacOptions().stac_schedule_auto_create
			&& (form.findById('ttssfManBeds').getValue()+form.findById('ttssfWomanBeds').getValue()+form.findById('ttssfCommonBeds').getValue())<=0)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_zapolneno_kol-vo_koyko_mest']);
			form.findById('ttssfCommonBeds').focus(false);
			return false;
		}
		if ((getStacOptions().stac_schedule_time_binding == 1) && !(form.findById('ttssfMaxRecordCount').getValue() > 0)) {
			Ext.Msg.alert(langs('Ошибка'), langs('В указанном промежутке времени не помещается ни одной бирки'));
			form.findById('ttmssfStartDay').focus(false);
			return false;
		}
		var loadMask = new Ext.LoadMask(Ext.get('TTSCreateScheduleForm'), { msg: "Подождите, идет создание новых бирок..." });
		loadMask.show();
		
		var post = [];
		post['LpuSection_id'] = this.LpuSection_id;
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
			id:'TTSCreateScheduleForm',
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
				tabIndex: TABINDEX_TTSSF + 1,
				xtype: 'swschedulecreationtypecombo',
				id: 'ttssfScheduleCreationType',
				allowBlank: false,
				fieldLabel: lang['variant_sozdaniya'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if ( Ext.isEmpty(newValue) ) {
							newValue = 1;
							combo.setValue(newValue);
						}

						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, index) {
						var form = this.findById('TTSCreateScheduleForm');
						form.findById('ttssfCopyToDate').setAllowBlank(record.get(combo.valueField) != 2);
						if ( typeof record == 'object' && record.get(combo.valueField) == 2 ) {
							form.findById('ttssfCopyToDate').showContainer();
							form.findById('ttssfCreateSchedulePanel').hide();
							form.findById('ttssfCopySchedulePanel').show();
							form.findById('ttssfCreateDate').setFieldLabel(lang['kopirovat_iz_diapazona']);
							form.findById('ttssfTimetableType').setAllowBlank(true);
						}
						else {
							form.findById('ttssfCopyToDate').hideContainer();
							form.findById('ttssfCreateSchedulePanel').show();
							form.findById('ttssfCopySchedulePanel').hide();
							form.findById('ttssfCreateDate').setFieldLabel(lang['sozdat_na_datyi']);
							form.findById('ttssfTimetableType').setAllowBlank(false);
						}
					}.createDelegate(this)
				},
				value: 1
			},
			{
				allowBlank: false,
				fieldLabel: lang['sozdat_na_datyi'],
				tabIndex: TABINDEX_TTSSF + 2,
				id: 'ttssfCreateDate',
				name: 'CreateDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				xtype: 'daterangefield'
			},
			{
				fieldLabel: 'Вставить в диапазон',
				id: 'ttssfCopyToDate',
				name: 'CopyToDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				tabIndex: TABINDEX_TTSSF + 3,
				xtype: 'daterangefield'
			},
			{
				hidden: true,
				xtype: 'fieldset',
				height: 70,
				id: 'ttssfCopySchedulePanel',
				title: '',
				labelWidth: 200,
				items: [
				{
					fieldLabel: lang['kopirovat_primechaniya_na_den'],
					id: 'ttssfCopyDayComments',
					name: 'CopyDayComments',
					tabIndex: TABINDEX_TTSSF + 4,
					width: 180,
					xtype: 'checkbox'
				},
				{
					fieldLabel: lang['kopirovat_primechaniya_na_birku'],
					id: 'ttgsfCopyTTSComments',
					name: 'CopyTTSComments',
					tabIndex: TABINDEX_TTSSF + 5,
					width: 180,
					xtype: 'checkbox'
				}]
			},
			{
				xtype: 'fieldset',
				height: 430,
				id: 'ttssfCreateSchedulePanel',
				title: '',
				items: [{
					xtype: 'fieldset',
					title: '',
					hidden: !(getRegionNick().inlist(['vologda', 'msk', 'kz', 'ufa']) && getStacOptions().stac_schedule_time_binding == 1),
					height: 140,
					items: [
						{
							id: 'ttmssfStartDay',
							fieldLabel: lang['nachalo_rabotyi'],
							name: 'StartTime',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_TTMSSF + 5,
							validateOnBlur: false,							
							width: 60,
							allowBlank: !(getRegionNick().inlist(['vologda','msk','kz', 'ufa']) && getStacOptions().stac_schedule_time_binding == 1),
							xtype: 'swtimefield'
						},
						{
							id: 'ttmssfEndDay',
							fieldLabel: lang['okonchanie_rabotyi'],
							labelWidth: 100,
							name: 'EndTime',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_TTMSSF + 5,
							validateOnBlur: false,							
							width: 60,
							allowBlank: !(getRegionNick().inlist(['vologda','msk','kz', 'ufa']) && getStacOptions().stac_schedule_time_binding == 1),
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
							allowBlank: !(getRegionNick().inlist(['vologda','msk','kz', 'ufa']) && getStacOptions().stac_schedule_time_binding == 1),
							tabIndex: TABINDEX_TTMSSF + 5
						},
						{
							xtype: 'textfield',
							fieldLabel: langs('Максимальное количество бирок'),
							tabIndex: TABINDEX_TTMSSF + 5,
							width: 50,
							name: 'MaxRecordCount',
							id: 'ttssfMaxRecordCount',
							disabled: true
						}
					]
				},
				{
					xtype: 'fieldset',
					height: 120,
					title: '',
					hidden: getStacOptions().stac_schedule_auto_create,
					items: [
						{
							id: 'ttssfManBeds',
							xtype: 'textfield',
							maskRe: /\d/,
							fieldLabel: lang['mujskih_koek'],
							minLength: 0,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 50,
							name: 'ManBeds',
							tabIndex: TABINDEX_TTSSF + 5,
							value: ''
						},
						{
							id: 'ttssfWomanBeds',
							xtype: 'textfield',
							maskRe: /\d/,
							fieldLabel: lang['jenskih_koek'],
							minLength: 0,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 50,
							name: 'WomanBeds',
							tabIndex: TABINDEX_TTSSF + 6,
							value: ''
						},
						{
							id: 'ttssfCommonBeds',
							xtype: 'textfield',
							maskRe: /\d/,
							fieldLabel: lang['obschih_koek'],
							minLength: 0,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 50,
							name: 'CommonBeds',
							tabIndex: TABINDEX_TTSSF + 7,
							value: ''
						},
						{
							anchor: '100%',
							xtype: 'swtimetabletypecombo',
							tabIndex: TABINDEX_TTSSF + 8,
							hiddenName: 'TimetableType_id',
							id: 'ttssfTimetableType',
							allowBlank: false
						}]
				},
				{
					xtype: 'fieldset',
					height: 70,
					title: '',
					hidden: !getStacOptions().stac_schedule_auto_create,
					items: [
						{
							id: 'ttssfFaster',
							xtype: 'textfield',
							maskRe: /\d/,
							fieldLabel: langs('Экстренные бирки'),
							minLength: 0,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 50,
							name: 'Faster',
							tabIndex: TABINDEX_TTSSF + 6,
							value: ''
						},
						{
							id: 'ttssfRegular',
							xtype: 'textfield',
							maskRe: /\d/,
							fieldLabel: langs('Обычные бирки'),
							minLength: 0,
							autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
							width: 50,
							name: 'Regular',
							tabIndex: TABINDEX_TTSSF + 6,
							value: ''
						}
					]
				}, {
					labelAlign: 'top',
					layout: 'form',
					border: false,
					items: 
					[{
						anchor: '100%',
						fieldLabel : lang['primechanie'],
						height: 100,
						name: 'TimetableExtend_Descr',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'ttssfTimetableExtend_Descr'
					}]
				}]
			}
			
			],
			url: C_TTS_CREATESCHED
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['sozdat_raspisanie'],
				id: 'ttssfCreate',
				tabIndex: TABINDEX_TTSSF + 10,
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
				tabIndex: TABINDEX_TTSSF + 11
			},
			{
				text: BTN_FRMCANCEL,
				id: 'ttssfCancel',
				tabIndex: TABINDEX_TTSSF + 12,
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttssfScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttssfCreate').focus();
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
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTTSScheduleFillWindow.superclass.initComponent.apply(this, arguments);
	}
});