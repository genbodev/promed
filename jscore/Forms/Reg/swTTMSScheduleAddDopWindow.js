/**
* swTTMSScheduleAddDopWindow - окно добавления дополнительной бирки для службы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      04.10.2011
*/

/*NO PARSE JSON*/
sw.Promed.swTTMSScheduleAddDopWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['dobavlenie_dopolnitelnoy_birki'],
	id: 'TTMSScheduleAddDopWindow',
	layout: 'border',
	maximizable: false,
	width: 280,
	height: 240,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTMSScheduleAddDopWindow',
	objectSrc: '/jscore/Forms/Reg/swTTMSScheduleAddDopWindow.js',
	
	/**
	 * Идентификатор места работы, с расписанием которого мы работаем
	 */
	MedService_id: null,
	
	/**
	 * Идентификатор услуги, с расписанием которого мы работаем
	 */
	UslugaComplexMedService_id: null,
	
	/**
	 * Дата, на которую создаётся дополнительная бирка
	 */
	date: null,
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTMSScheduleAddDopWindow.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0]['date']) {
			this.date = arguments[0]['date'];
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTMSScheduleAddDopForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTMSScheduleAddDopForm'), { msg: "Подождите, идет создание новых бирок..." });
		loadMask.show();
		
		var post = [];
		post['MedService_id'] = this.MedService_id;
		post['UslugaComplexMedService_id'] = this.UslugaComplexMedService_id;
		post['Day'] = this.date;
		form.getForm().submit({
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
			id:'TTMSScheduleAddDopForm',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 80,
			items:
			[{
				fieldLabel: lang['vremya_birki'],
				name: 'StartTime',
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				validateOnBlur: false,
				value: '08:00',
				width: 60,
				xtype: 'swtimefield'
			},
			{
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
					id: 'ttmssfTimetableExtend_Descr'
				}]
			}],
			url: C_TTMS_ADDDOP
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['dobavit'],
				id: 'TTMSadCreate',
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
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				id: 'TTMSadCancel',
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('TTMSadScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					Ext.getCmp('TTMSadCreate').focus();
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
		sw.Promed.swTTMSScheduleAddDopWindow.superclass.initComponent.apply(this, arguments);
	}
});