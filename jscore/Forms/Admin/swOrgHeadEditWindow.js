/**
* swOrgHeadEditWindow - окно редактирования руководства.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      28.01.2010
* @comment      Префикс для id компонентов OHEW (OrgHeadEditWindow)
*/

sw.Promed.swOrgHeadEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 800,
	layout: 'form',
	id: 'OrgHeadEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.findById('OrgHeadEditForm');
		if ( !form.getForm().isValid() ) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.findById('OrgHeadEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{
				action: current_window.action,
				LpuUnit_id: current_window.LpuUnit_id
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.OrgHead_id)
					{
						current_window.hide();
						Ext.getCmp('OrgHeadEditWindow').callback({
							OrgHead_id: action.result.OrgHead_id,
							Person_id: form.getForm().findField('Person_id').getValue(),
							OrgHeadPerson_Fio: current_window.findById('OHEW_PersonInformationFrame').getFieldValue("Person_Surname") + ' ' + current_window.findById('OHEW_PersonInformationFrame').getFieldValue("Person_Firname") + ' ' + current_window.findById('OHEW_PersonInformationFrame').getFieldValue("Person_Secname"),
							OrgHeadPost_Name: form.getForm().findField('OrgHeadPost_id').getRawValue().substring(3, form.getForm().findField('OrgHeadPost_id').getRawValue().length),
							OrgHead_Phone: form.getForm().findField('OrgHead_Phone').getValue(),
							OrgHead_Fax: form.getForm().findField('OrgHead_Fax').getValue()
						});
						
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								current_window.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) 
	{
		if (enable) 
		{
			var form = this.findById('OrgHeadEditForm');
			form.getForm().findField('OrgHeadPost_id').enable(),
			form.getForm().findField('OrgHead_Phone').enable(),
			form.getForm().findField('OrgHead_Fax').enable()
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('OrgHeadEditForm');
			form.getForm().findField('OrgHeadPost_id').disable(),
			form.getForm().findField('OrgHead_Phone').disable(),
			form.getForm().findField('OrgHead_Fax').disable()
			this.buttons[0].disable();
		}
	},
	setPost: function(data) 
	{
		/*var combo = this.findById('OHEW_Post_id');
		if (data['Post_id'])
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.setValue(data['Post_id']);
					//combo.focus(true, 250);
					combo.fireEvent('change', combo);
				},
				params: 
				{
					Post_id: data['Post_id']
				}
			});
		}*/
		/*
		combo.setValue(data['Org_id']);
		combo.setRawValue(data['Org_Name']);
		*/
	},
	show: function() 
	{
		sw.Promed.swOrgHeadEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('OrgHeadEditForm').getForm().reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].OrgHead_id) 
			this.OrgHead_id = arguments[0].OrgHead_id;
		else 
			this.OrgHead_id = null;
			
		if (arguments[0].Lpu_id) 
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
			
		if (arguments[0].Person_id) 
			this.Person_id = arguments[0].Person_id;
		else 
			this.Person_id = null;
			
		if (arguments[0].LpuUnit_id) 
			this.LpuUnit_id = arguments[0].LpuUnit_id;
		else 
			this.LpuUnit_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.OrgHead_id ) && ( this.OrgHead_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		var form = this.findById('OrgHeadEditForm');
		form.getForm().setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['rukovodstvo_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.getForm().clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['rukovodstvo_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['rukovodstvo_prosmotr']);
				this.enableEdit(false);
				break;
		}

		this.findById('OHEW_PersonInformationFrame').setTitle('...');
		this.findById('OHEW_PersonInformationFrame').clearPersonChangeParams();
		this.findById('OHEW_PersonInformationFrame').load({
			callback: function() {
				this.findById('OHEW_PersonInformationFrame').setPersonTitle();
			}.createDelegate(this),
			onExpand: true,
			Person_id: this.Person_id
		});

		if (this.action != 'add')
		{
			form.getForm().load(
			{
				params: 
				{
					OrgHead_id: current_window.OrgHead_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
					current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);
				},
				url: '/?c=Org&m=loadOrgHead'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('OHEW_OrgHeadPost_id').focus(true, 100);
		else
			this.buttons[3].focus();
	},
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		this.orgHeadEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'OrgHeadEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			region: 'center',
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: 'OHEW_OrgHead_id',
				name: 'OrgHead_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				id: 'OHEW_Person_id',
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			},			
			{
				anchor: '100%',
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['doljnost'],
				tabIndex: TABINDEX_OHEW + 5,
				id: 'OHEW_OrgHeadPost_id',
				listeners: {
					'keydown': function (inp, e) {
						if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							inp.ownerCt.findById('OHEW_OrgHead_Phone').focus(true, 50);
						}
					}
				},
				minChars: 0,
				queryDelay: 1,
				selectOnFocus: true,
				hiddenName: 'OrgHeadPost_id',
				xtype: 'sworgheadpostcombo'
			},
			{
				anchor: '100%',
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: lang['telefon_yi'],
				id: 'OHEW_OrgHead_Phone',
				listeners: {
					'keydown': function (inp, e) {
						if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							inp.ownerCt.findById('OHEW_OrgHeadPost_id').focus(true, 50);
						}
					}
				},
				name: 'OrgHead_Phone',
				tabIndex: TABINDEX_OHEW + 1,
				xtype: 'textfield'
			},
			{
				anchor: '100%',
				allowBlank: true,
				fieldLabel: lang['faks_yi'],
				id: 'OHEW_OrgHead_Fax',
				name: 'OrgHead_Fax',
				tabIndex: TABINDEX_OHEW + 2,
				xtype: 'textfield'
			}, {
				anchor: '100%',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
				fieldLabel: 'e-mail',
				name: 'OrgHead_Email',
				tabIndex: TABINDEX_OHEW + 2,
				id: 'OHEW_OrgHead_Email'
			}, {
				anchor: '100%',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
				fieldLabel: lang['mobilnyiy_telefon'],
				name: 'OrgHead_Mobile',
				tabIndex: TABINDEX_OHEW + 2,
				id: 'OHEW_OrgHead_Mobile'
			}, {
				anchor: '100%',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				fieldLabel: lang['№_prikaza_o_naznachenii'],
				name: 'OrgHead_CommissNum',
				tabIndex: TABINDEX_OHEW + 2,
				id: 'OHEW_OrgHead_CommissNum'
			}, {
				xtype: 'swdatefield',
				fieldLabel: lang['data_prikaza_o_naznachenii'],
				name: 'OrgHead_CommissDate',
				tabIndex: TABINDEX_OHEW + 2,
				id: 'OHEW_OrgHead_CommissDate'
			}, {
				anchor: '100%',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
				fieldLabel: lang['adres_№_rabochego_kabineta'],
				name: 'OrgHead_Address',
				tabIndex: TABINDEX_OHEW + 2,
				id: 'OHEW_OrgHead_Address'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'OrgHead_id' },
				{ name: 'Person_id' },
				{ name: 'OrgHeadPost_id' },
				{ name: 'OrgHead_Phone' },
				{ name: 'OrgHead_Fax' },
				{ name: 'OrgHead_Email' },
				{ name: 'OrgHead_Mobile' },
				{ name: 'OrgHead_CommissNum' },
				{ name: 'OrgHead_CommissDate' },
				{ name: 'OrgHead_Address' }
			]),
			url: '/?c=Org&m=saveOrgHead'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_OHEW + 3,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_OHEW + 4,
				text: BTN_FRMCANCEL
			}],
			items: [
				new sw.Promed.PersonInfoPanel({
					button2Callback: function(callback_data) {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						current_window.findById('OHEW_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
					},
					button1OnHide: function() {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						var form = current_window.findById('OrgHeadEditForm');
						if (current_window.action == 'view')
						{
							current_window.buttons[current_window.buttons.length - 1].focus();
						}
						else
						{
							form.getForm().findField('OrgHeadPost_id').focus(true, 100);
						}
					},
					button2OnHide: function() {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						var form = current_window.findById('OrgHeadEditForm');
						if (current_window.action == 'view')
						{
							current_window.buttons[current_window.buttons.length - 1].focus();
						}
						else
						{
							form.getForm().findField('OrgHeadPost_id').focus(true, 100);
						}
					},
					button3OnHide: function() {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						var form = current_window.findById('OrgHeadEditForm');
						if (current_window.action == 'view')
						{
							current_window.buttons[current_window.buttons.length - 1].focus();
						}
						else
						{
							form.getForm().findField('OrgHeadPost_id').focus(true, 100);
						}
					},
					button4OnHide: function() {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						var form = current_window.findById('OrgHeadEditForm');
						if (current_window.action == 'view')
						{
							current_window.buttons[current_window.buttons.length - 1].focus();
						}
						else
						{
							form.getForm().findField('OrgHeadPost_id').focus(true, 100);
						}
					},
					button5OnHide: function() {
						var current_window = Ext.getCmp('OrgHeadEditWindow');
						var form = current_window.findById('OrgHeadEditForm');
						if (current_window.action == 'view')
						{
							current_window.buttons[current_window.buttons.length - 1].focus();
						}
						else
						{
							form.getForm().findField('OrgHeadPost_id').focus(true, 100);
						}
					},
					id: 'OHEW_PersonInformationFrame',
					region: 'north'
				}),
				this.orgHeadEditForm
			]
		});
		sw.Promed.swOrgHeadEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});