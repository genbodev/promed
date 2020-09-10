/**
* swOrgRSchetEditWindow - окно редактирования/добавления счета.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      27.01.2010
* @comment      Префикс для id компонентов ORSEW (OrgRSchetEditWindow)
*/

sw.Promed.swOrgRSchetEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	allowDuplicateOpening: true,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'OrgRSchetEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();

			if ( this.isWindowCopy == true ) {
				this.destroy();
			}
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		var form = this.findById(this.id + '_' + 'OrgRSchetEditForm');
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
	submit: function(onlySave) 
	{
		var form = this.findById(this.id + '_' + 'OrgRSchetEditForm');
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{
				action: current_window.action
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						sw.swMsg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.OrgRSchet_id)
					{
						if (!onlySave)
						{
							current_window.hide();
							
							if(current_window.owner && current_window.owner.id == 'OrgEditWindowOrgRSchetGrid')
							{
								current_window.callback(current_window.owner,action.result.OrgRSchet_id);
							} else {
								current_window.callback({
									OrgRSchet_id: action.result.OrgRSchet_id,
									OrgRSchet_Name: form.getForm().findField('OrgRSchet_Name').getValue(),
									OrgRSchet_RSchet: form.getForm().findField('OrgRSchet_RSchet').getValue(),
									OrgBank_Name: form.getForm().findField('OrgBank_id').getRawValue()
								});	
							}
						}
						else
						{
							form.findById(current_window.id + '_' + 'OrgRSchet_id').setValue(action.result.OrgRSchet_id);
							form.findById(current_window.id + '_' + 'OrgRSchetKBK').params = 
							{
								OrgRSchet_id: action.result.OrgRSchet_id
							};
							form.findById(current_window.id + '_' + 'OrgRSchetKBK').gFilters = 
							{
								OrgRSchet_id: action.result.OrgRSchet_id
							};
							current_window.action = 'edit';
							form.findById(current_window.id + '_' + 'OrgRSchetKBK').run_function_add = false;
							form.findById(current_window.id + '_' + 'OrgRSchetKBK').ViewActions.action_add.execute();
						}
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
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
		var form = this;
		if (enable) 
		{
			var form = this.findById(this.id + '_' + 'OrgRSchetEditForm');
			form.getForm().findField('OrgRSchet_Name').enable(),
			form.getForm().findField('OrgRSchet_RSchet').enable(),
			form.getForm().findField('OrgBank_id').enable()
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById(this.id + '_' + 'OrgRSchetEditForm');
			form.getForm().findField('OrgRSchet_Name').disable(),
			form.getForm().findField('OrgRSchet_RSchet').disable(),
			form.getForm().findField('OrgBank_id').disable()
			this.buttons[0].disable();			
		}
	},
	setOrg: function(data) 
	{
		var combo = this.findById(this.id + '_' + 'OrgBank_id');
		if (data['Org_id'])
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.setValue(data['Org_id']);
					//combo.focus(true, 250);
					combo.fireEvent('change', combo);
				},
				params: 
				{
					Org_id: data['Org_id'],
					OrgType: 'bank',
					Lpu_id: current_window.Lpu_id
				}
			});
		}
		/*
		combo.setValue(data['Org_id']);
		combo.setRawValue(data['Org_Name']);
		*/
	},
	show: function() 
	{
		sw.Promed.swOrgRSchetEditWindow.superclass.show.apply(this, arguments);
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
		var base_form = this.findById(this.id + '_' + 'OrgRSchetEditForm').getForm();
		base_form.reset();
		this.findById(this.id + '_' + 'OrgRSchetKBK').removeAll({clearAll: true});
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].OrgRSchet_id) 
			this.OrgRSchet_id = arguments[0].OrgRSchet_id;
		else 
			this.OrghRSchet_id = null;
		if (arguments[0].Lpu_id) 
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
			
		if (arguments[0].Org_id) 
		{
			this.orig_Org_id = arguments[0].Org_id;
			base_form.findField('Org_id').setValue(arguments[0].Org_id);
		}
		
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
			if ( ( this.OrgRSchet_id ) && ( this.OrgRSchet_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		// по умолчанию ставим валюту рубли.
		var rur_id = base_form.findField('Okv_id').getStore().findBy(function(rec) { return rec.get('Okv_Nick') == 'RUB'; });
		if (rur_id > 0) 
		{
			var row = base_form.findField('Okv_id').getStore().getAt(rur_id);
			base_form.findField('Okv_id').setValue(row.get('Okv_id')); 
		}
		
		base_form.findField('OrgRSchet_begDate').setMinValue(undefined);
		base_form.findField('OrgRSchet_begDate').setMaxValue(undefined);
		base_form.findField('OrgRSchet_endDate').setMinValue(undefined);
		base_form.findField('OrgRSchet_endDate').setMaxValue(undefined);
		
		base_form.setValues(arguments[0]);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['raschetnyiy_schet_dobavlenie']);
				this.enableEdit(true);
				this.findById(this.id + '_' + 'OrgRSchetKBK').setReadOnly(false);
				loadMask.hide();
				base_form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['raschetnyiy_schet_redaktirovanie']);
				this.enableEdit(true);
				this.findById(this.id + '_' + 'OrgRSchetKBK').setReadOnly(false);
				break;
			case 'view':
				this.setTitle(lang['raschetnyiy_schet_prosmotr']);
				this.enableEdit(false);
				this.findById(this.id + '_' + 'OrgRSchetKBK').setReadOnly(true);
				break;
		}
		
		if (this.action != 'add')
		{
			base_form.load(
			{
				params: 
				{
					OrgRSchet_id: current_window.OrgRSchet_id,
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
					var combo = current_window.findById(current_window.id + '_' + 'OrgBank_id');
					combo.getStore().load(
					{
						callback: function() 
						{
							combo.setValue(combo.getValue());
							combo.fireEvent('change', combo);
						},
						params: 
						{
							Org_id: combo.getValue(),
							OrgType: 'bank'
						}
					});
					
					current_window.findById(current_window.id + '_' + 'OrgRSchetKBK').loadData({globalFilters:{OrgRSchet_id: current_window.OrgRSchet_id}, params:{OrgRSchet_id: current_window.OrgRSchet_id} });
					
					if (current_window.action=='edit')
					{
						//current_window.findById('regeRegistry_begDate').focus(true, 50);
					}
					else 
						current_window.buttons[3].focus();
				},
				url: '/?c=Org&m=loadOrgRSchet'
			});
		}
		
		if ( !base_form.findField('OrgRSchet_RSchet').disabled ) {
			base_form.findField('OrgRSchet_RSchet').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	
	initComponent: function() 
	{
		var current_window = this;

		// Сохранение основной формы
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp(current_window.id + '_' + 'OrgRSchetEditForm');
			var tw = current_window;
			if (tf.getForm().isValid())
			{
				tw.submit(true);
			}
			return false;
		}
		
		// Форма с полями 
		this.orgRSchetEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: current_window.id + '_' + 'OrgRSchetEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Org_id',
				value: 0,
				xtype: 'hidden'
			}, {
				id: current_window.id + '_' + 'OrgRSchet_id',
				name: 'OrgRSchet_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['nomer_scheta'],
				name: 'OrgRSchet_RSchet',
				tabIndex: TABINDEX_ORSEW + 0,
				xtype: 'textfield'
			},
			{
				comboSubject: 'OrgRSchetType',
				enableKeyEvents: true,
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo',
				hiddenName: 'OrgRSchetType_id',
				allowBlank: false,
				tabIndex: TABINDEX_ORSEW + 1,
				fieldLabel: lang['tip_scheta']
			},
			{
				anchor: '100%',
				allowBlank: false,
				border: true,
				enableKeyEvents: true,
				fieldLabel: lang['bank'],
				id: current_window.id + '_' + 'OrgBank_id',
				//name: 'OrgBank_id',
				hiddenName: 'OrgBank_id',
				editable: false,
				tabIndex: TABINDEX_ORSEW + 2,
				triggerAction: 'none',
				xtype: 'sworgcombo',
				listeners: 
				{
					'change': function() 
					{
						var base_form = current_window.findById(current_window.id + '_' + 'OrgRSchetEditForm').getForm();
						base_form.findField('Org_id').setValue(current_window.orig_Org_id);
					},
					keydown: function(inp, e) 
					{
						if ( e.shiftKey == true && e.getKey() == e.TAB ) {
							e.stopEvent();
							inp.ownerCt.findById(current_window.id + '_' + 'OrgRSchet_Name').focus(true, 50);
						}
						if (e.getKey() == e.DELETE || e.getKey() == e.F4 )
						{
							e.stopEvent();
							if (e.browserEvent.stopPropagation)
							{
								e.browserEvent.stopPropagation();
							}
							else
							{
								e.browserEvent.cancelBubble = true;
							}
							if (e.browserEvent.preventDefault)
							{
								e.browserEvent.preventDefault();
							}
							else
							{
								e.browserEvent.returnValue = false;
							}
							e.returnValue = false;
							
							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							switch (e.getKey())
							{
								case e.DELETE:
									inp.clearValue();
									inp.ownerCt.ownerCt.findById(current_window.id + '_' + 'OrgBank_id').setRawValue(null);
									break;
								case e.F4:
									inp.onTrigger1Click();
									break;
							}
						}
					}
				},
				onTrigger1Click: function() 
				{
					var
						combo = this,
						searchWnd = getWnd('swOrgSearchWindow');

					searchWnd.show({
						object: 'bank',
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 && typeof combo == 'object' )
							{
								combo.getStore().load({
									params: {
										OrgType:'bank',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function()
									{
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
								});
							}
							searchWnd.hide();
						},
						onClose: function() {combo.focus(true, 200)}
					});
				}
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_otkryitiya'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_ORSEW + 3,
				name: 'OrgRSchet_begDate',
				endDateField: 'OrgRSchet_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_zakryitiya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_ORSEW + 4,
				name: 'OrgRSchet_endDate',
				begDateField: 'OrgRSchet_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				comboSubject: 'Okv',
				moreFields: [
					{ name: 'KLCountry_id', mapping: 'KLCountry_id' },
					{ name: 'Okv_Nick', mapping: 'Okv_Nick' }
				],
				enableKeyEvents: true,
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo',
				forceSelection: true,
				editable: true,
				codeAlthoughNotEditable: true,
				listeners: {
					blur: function(combo, eOpts) {
						if ( combo.getRawValue() == '' ) {
							combo.setValue('');
						}
					}
				},
				hiddenName: 'Okv_id',
				allowBlank: false,
				tabIndex: TABINDEX_ORSEW + 5,
				fieldLabel: lang['valyuta']
			},
			{
				anchor: '100%',
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['naimenovanie'],
				tabIndex: TABINDEX_ORSEW + 6,
				id: current_window.id + '_' + 'OrgRSchet_Name',
				listeners: {
					'keydown': function (inp, e) {
						if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							inp.ownerCt.findById(current_window.id + '_' + 'OrgBank_id').focus(true, 50);
						}
					}
				},
				name: 'OrgRSchet_Name',
				xtype: 'textfield'
			},
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', func: current_window.MainRecordAdd},
					{ name: 'action_edit' },
					{ name: 'action_view' },
					{ name: 'action_delete' },
					{ name: 'action_print' }
				],
				autoExpandColumn: 'autoexpand',
				object: 'OrgRSchetKBK',
				editformclassname: 'swOrgRSchetKBKEditWindow',
				autoExpandMin: 150,
				autoLoadData: false,
				border: false,
				dataUrl: '/?c=Org&m=loadOrgRSchetKBKGrid',
				id: current_window.id + '_' + 'OrgRSchetKBK',
				paging: false,
				region: 'center',
				title: lang['kbk'],
				stringfields: [
					{ name: 'OrgRSchetKBK_id', type: 'int', header: 'ID', key: true },
					{ name: 'OrgRSchet_KBK', type: 'string', header: lang['kbk'], width: 240 }
				]
			})
			],
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
				{ name: 'Org_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'OrgRSchet_Name' },
				{ name: 'OrgBank_id' },
				{ name: 'OrgRSchet_RSchet' },
				{ name: 'OrgRSchet_begDate' },
				{ name: 'OrgRSchet_endDate' },
				{ name: 'Okv_id' },
				{ name: 'OrgRSchetType_id' }
			]),
			url: '/?c=Org&m=saveOrgRSchet'
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
				tabIndex: TABINDEX_ORSEW + 10,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_ORSEW + 11),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_ORSEW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [this.orgRSchetEditForm]
		});
		sw.Promed.swOrgRSchetEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});