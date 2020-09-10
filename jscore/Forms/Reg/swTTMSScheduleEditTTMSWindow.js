/**
* swTTMSScheduleEditTTMSWindow - окно редактирования отдельной бирки службы
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
sw.Promed.swTTMSScheduleEditTTMSWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_birki_slujbyi'],
	id: 'swTTMSScheduleEditTTMSWindow',
	layout: 'border',
	maximizable: false,
	width: 380,
	height: 240,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTMSScheduleEditTTMSWindow',
	objectSrc: '/jscore/Forms/Reg/swTTMSScheduleEditTTMSWindow.js',
	
	/**
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTMS: [],
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTMSScheduleEditTTMSWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['selectedTTMS']) {
			this.selectedTTMS = arguments[0]['selectedTTMS'];
		}
		
		var form = this.findById('TTMSScheduleEditTTMSForm');
		
		form.getForm().findField('ttmsseChangeTimetableType').enable();
		form.getForm().findField('ttmsseChangeTimetableType').setValue(false);
		form.getForm().findField('ttmsseChangeTTMSDescr').enable();
		form.getForm().findField('ttmsseChangeTTMSDescr').setValue(false);
		
		form.getForm().findField('ttmsseTimetableExtend_Descr').setValue('');
		form.getForm().findField('ttmsseTimetableExtend_Descr').disable();
		
		if ( form.getForm().findField('ttmsseTimetableType').getStore().getCount() == 0 ) {
			form.getForm().findField('ttmsseTimetableType').getStore().load({
				params: {
					Place_id: 3
				},
				callback: function () {
					form.getForm().findField('ttmsseTimetableType').setValue(1);
					form.getForm().findField('ttmsseTimetableType').disable();
				}
			});
		} else {
			form.getForm().findField('ttmsseTimetableType').setValue(1);
			form.getForm().findField('ttmsseTimetableType').disable();
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTMSScheduleEditTTMSForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTMSScheduleEditTTMSForm'), { msg: "Подождите, идет сохранение бирок" });
		loadMask.show();
		
		var post = [];
		post['selectedTTMS'] = Ext.util.JSON.encode(this.selectedTTMS);

		if ( !form.getForm().findField('ttmsseChangeTimetableType').checked ) {
			form.getForm().findField('ttmsseTimetableType').disable();
		}
		if ( !form.getForm().findField('ttmsseChangeTTMSDescr').checked ) {
			form.getForm().findField('ttmsseTimetableExtend_Descr').disable();
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
			id:'TTMSScheduleEditTTMSForm',
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
						id: 'ttmsseChangeTimetableType',
						name: 'ChangeTTMSType',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttmsseTimetableType').setDisabled(!c.checked);
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
						id: 'ttmsseTimetableType',
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
						id: 'ttmsseChangeTTMSDescr',
						name: 'ChangeTTMSDescr',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttmsseTimetableExtend_Descr').setDisabled(!c.checked);;
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
						id: 'ttmsseTimetableExtend_Descr'
					}]
				}]
			}],
			url: C_TTMS_EDITTTMS
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: B_FORM_SAVE,
				id: 'ttmsseCreate',
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
				id: 'ttmsseCancel',
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttmsseScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttmsseCreate').focus();
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
		sw.Promed.swTTMSScheduleEditTTMSWindow.superclass.initComponent.apply(this, arguments);
	}
});