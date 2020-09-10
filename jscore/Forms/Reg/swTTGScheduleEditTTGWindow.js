/**
* swTTGScheduleEditTTGWindow - окно редактирования отдельной бирки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2013 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      18.04.2013
*/

/*NO PARSE JSON*/
sw.Promed.swTTGScheduleEditTTGWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_birki_polikliniki'],
	id: 'swTTGScheduleEditTTGWindow',
	layout: 'border',
	maximizable: false,
	width: 380,
	height: 240,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTGScheduleEditTTGWindow',
	objectSrc: '/jscore/Forms/Reg/swTTGScheduleEditTTGWindow.js',
	
	/**
	 * Набор выбранных бирок для групповой работы с ними
	 */
	selectedTTG: [],
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTGScheduleEditTTGWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['selectedTTG']) {
			this.selectedTTG = arguments[0]['selectedTTG'];
		}
		
		var form = this.findById('TTGScheduleEditTTGForm');
		
		form.getForm().findField('ttgseChangeTimetableType').enable();
		form.getForm().findField('ttgseChangeTimetableType').setValue(false);
		form.getForm().findField('ttgseChangeTTGDescr').enable();
		form.getForm().findField('ttgseChangeTTGDescr').setValue(false);
		
		
		
		form.getForm().findField('ttgseTimetableExtend_Descr').setValue('');
		form.getForm().findField('ttgseTimetableExtend_Descr').disable();
		
		if ( form.getForm().findField('ttgseTimetableType').getStore().getCount() == 0 ) {
			form.getForm().findField('ttgseTimetableType').getStore().load({
				params: {
					Place_id: 1
				},
				callback: function () {
					form.getForm().findField('ttgseTimetableType').setValue(1);
					form.getForm().findField('ttgseTimetableType').disable();
				}
			});
		} else {
			form.getForm().findField('ttgseTimetableType').setValue(1);
			form.getForm().findField('ttgseTimetableType').disable();
		}
	},
	doSave: function() 
	{
		var form = this.findById('TTGScheduleEditTTGForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTGScheduleEditTTGForm'), { msg: "Подождите, идет сохранение бирок" });
		loadMask.show();
		
		var post = [];
		post['selectedTTG'] = Ext.util.JSON.encode(this.selectedTTG);

		if ( !form.getForm().findField('ttgseChangeTimetableType').checked ) {
			form.getForm().findField('ttgseTimetableType').disable();
		}
		if ( !form.getForm().findField('ttgseChangeTTGDescr').checked ) {
			form.getForm().findField('ttgseTimetableExtend_Descr').disable();
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
			id:'TTGScheduleEditTTGForm',
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
						id: 'ttgseChangeTimetableType',
						name: 'ChangeTTGType',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttgseTimetableType').setDisabled(!c.checked);
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
						id: 'ttgseTimetableType',
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
						id: 'ttgseChangeTTGDescr',
						name: 'ChangeTTGDescr',
						xtype: 'checkbox',
						listeners: {
							check: function(c) {
								this.findById('ttgseTimetableExtend_Descr').setDisabled(!c.checked);;
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
						id: 'ttgseTimetableExtend_Descr'
					}]
				}]
			}],
			url: C_TTG_EDITTTG
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: B_FORM_SAVE,
				id: 'ttgseCreate',
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
				id: 'ttgseCancel',
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttgseScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttgseCreate').focus();
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
		sw.Promed.swTTGScheduleEditTTGWindow.superclass.initComponent.apply(this, arguments);
	}
});