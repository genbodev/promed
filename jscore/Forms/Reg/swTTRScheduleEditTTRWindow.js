/**
* swTTRScheduleEditTTRWindow - окно редактирования отдельной бирки ресурса
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2013 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      24.04.2013
*/

/*NO PARSE JSON*/
sw.Promed.swTTRScheduleEditTTRWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_birki_slujbyi'],
	id: 'swTTRScheduleEditTTRWindow',
	layout: 'border',
	maximizable: false,
	width: 380,
	height: 240,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTRScheduleEditTTRWindow',
	objectSrc: '/jscore/Forms/Reg/swTTRScheduleEditTTRWindow.js',
	
	/**
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTR: [],
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTRScheduleEditTTRWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['selectedTTR']) {
			this.selectedTTR = arguments[0]['selectedTTR'];
		}
		
		var form = this.findById('TTRScheduleEditTTRForm');
		
		form.getForm().findField('ttrseChangeTimetableType').enable();
		form.getForm().findField('ttrseChangeTimetableType').setValue(false);
		form.getForm().findField('ttrseChangeTTRDescr').enable();
		form.getForm().findField('ttrseChangeTTRDescr').setValue(false);
		
		form.getForm().findField('ttrseTimetableExtend_Descr').setValue('');
		form.getForm().findField('ttrseTimetableExtend_Descr').disable();
		
		if ( form.getForm().findField('ttrseTimetableType').getStore().getCount() == 0 ) {
			form.getForm().findField('ttrseTimetableType').getStore().load({
				params: {
					Place_id: 3
				},
				callback: function () {
					form.getForm().findField('ttrseTimetableType').setValue(1);
					form.getForm().findField('ttrseTimetableType').disable();
				}
			});
		} else {
			form.getForm().findField('ttrseTimetableType').setValue(1);
			form.getForm().findField('ttrseTimetableType').disable();
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTRScheduleEditTTRForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTRScheduleEditTTRForm'), { msg: "Подождите, идет сохранение бирок" });
		loadMask.show();
		
		var post = [];
		post['selectedTTR'] = Ext.util.JSON.encode(this.selectedTTR);

		if ( !form.getForm().findField('ttrseChangeTimetableType').checked ) {
			form.getForm().findField('ttrseTimetableType').disable();
		}
		if ( !form.getForm().findField('ttrseChangeTTRDescr').checked ) {
			form.getForm().findField('ttrseTimetableExtend_Descr').disable();
		}

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
			id:'TTRScheduleEditTTRForm',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 80,
			items:
			[{
				layout: 'column',
				items:
				[{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 0,
					columnWidth: 0.1,
					border: false,
					items: 
					[{
						hideLabel: true,
						id: 'ttrseChangeTimetableType',
						name: 'ChangeTTRType',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttrseTimetableType').setDisabled(!c.checked);
							}.createDelegate(this)
						}
					}]
				},
				{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 70,
					columnWidth: 0.90,
					border: false,
					style: 'margin-top: 2px',
					items: 
					[{
						anchor: '100%',
						disabled: true,
						xtype: 'swtimetabletypecombo',
						hiddenName: 'TimetableType_id',
						id: 'ttrseTimetableType',
						allowBlank: false
					}]
				}]
			},
			{
				layout: 'column',
				hidden: (getRegionNick() != 'kareliya'),
				items:
				[{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 0,
					columnWidth: 0.1,
					border: false,
					items: 
					[{
						hideLabel: true,
						id: 'ttrseChangeTTRDescr',
						name: 'ChangeTTRDescr',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttrseTimetableExtend_Descr').setDisabled(!c.checked);;
							}.createDelegate(this)
						}
					}]
				},{
					labelAlign: 'top',
					layout: 'form',
					border: false,
					columnWidth: 0.90,
					style: 'margin-top: 5px',
					items: 
					[{
						anchor: '100%',
						disabled: true,
						fieldLabel : lang['primechanie'],
						height: 100,
						name: 'TimetableExtend_Descr',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'ttrseTimetableExtend_Descr'
					}]
				}]
			}],
			url: C_TTR_EDITTTR
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: B_FORM_SAVE,
				id: 'ttrseCreate',
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
				id: 'ttrseCancel',
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttrseScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttrseCreate').focus();
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
		sw.Promed.swTTRScheduleEditTTRWindow.superclass.initComponent.apply(this, arguments);
	}
});