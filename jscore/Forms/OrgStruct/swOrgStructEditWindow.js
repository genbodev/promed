/**
* swOrgStructEditWindow - форма редактирования структурного уровня
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      OrgStruct
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      08.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swOrgStructEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	autoHeight: true,
	width: 500,
	modal: true,
	id: 'OrgStructEditWindow',
	title: WND_ORGSTRUCT_ORGSTRUCT_ADD, 
	layout: 'form',
	resizable: true,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this;
		var base_form = this.formPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.formPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var os_numlevel = base_form.findField('OrgStruct_NumLevel').getValue();
		var ost_numlevel = base_form.findField('OrgStructLevelType_id').getFieldValue('OrgStructLevelType_LevelNumber');
		
		if (os_numlevel != ost_numlevel) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('OrgStructLevelType_id').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['nomer_strukturnogo_urovnya']+os_numlevel+lang['ne_sovpadaet_s_trebuemyim_nomerom_v_tipe_strukturnogo_urovnya']+ost_numlevel+lang['vyiberite_drugie_znacheniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;		
		}
		
		form.getLoadMask(LOAD_WAIT_SAVE).show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				form.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				form.getLoadMask().hide();
				
				if ( action.result && action.result.OrgStruct_id > 0 ) {
					this.callback(this.owner, action.result.OrgStruct_id);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	formStatus: 'edit',
	initComponent: function() 
	{
		var form = this;
		
		this.formPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'OrgStruct_id' },
				{ name: 'Org_id' },
				{ name: 'OrgStruct_pid' },
				{ name: 'OrgStruct_NumLevel' },
				{ name: 'OrgStruct_Code' },
				{ name: 'OrgStruct_Name' },
				{ name: 'OrgStruct_Nick' },
				{ name: 'OrgStruct_begDT' },
				{ name: 'OrgStruct_endDT' },
				{ name: 'OrgStruct_LeftNum' },
				{ name: 'OrgStruct_RightNum' },
				{ name: 'OrgStructLevelType_id' }
			]),
			url: '/?c=OrgStruct&m=saveOrgStruct',
			items: [{
				name: 'OrgStruct_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Org_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'OrgStruct_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'OrgStruct_NumLevel',
				value: 0,
				xtype: 'hidden'			
			}, {
				name: 'OrgStruct_LeftNum',
				value: 0,
				xtype: 'hidden'			
			}, {
				name: 'OrgStruct_RightNum',
				value: 0,
				xtype: 'hidden'			
			}, {
				name: 'OrgStruct_Code',
				allowBlank: false,
				tabIndex: TABINDEX_OSEW + 0,
				fieldLabel: lang['kod'],
				xtype: 'numberfield',
				maxValue: 999999,
				minValue: 0,
				allowNegative: false,
				allowDecimals: false
			}, {
				name: 'OrgStruct_Name',
				allowBlank: false,
				tabIndex: TABINDEX_OSEW + 1,
				width: 300,
				maxLength: 50,
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield'
			}, {
				name: 'OrgStruct_Nick',
				allowBlank: false,
				tabIndex: TABINDEX_OSEW + 2,
				width: 300,
				maxLength: 50,
				fieldLabel: lang['kratkoe_naimenovanie'],
				triggerClass: 'x-form-equil-trigger',
				onTriggerClick: function() {
					var base_form = form.formPanel.getForm();
					if ( base_form.findField('OrgStruct_Nick').disabled ) {						
						return false;
					}
					var fullname = base_form.findField('OrgStruct_Name').getValue();
					base_form.findField('OrgStruct_Nick').setValue(fullname);
				},
				xtype: 'trigger'
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_otkryitiya'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_OSEW + 4,
				name: 'OrgStruct_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_zakryitiya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_OSEW + 5,
				name: 'OrgStruct_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				xtype: 'sworgstructleveltypecombo',
				hiddenName: 'OrgStructLevelType_id',
				tabIndex: TABINDEX_OSEW + 6,
				width: 300,
				allowBlank: false,
				fieldLabel: lang['tip_strukturnogo_urovnya']
			}]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( !base_form.findField('OrgStructLevelType_id').disabled ) {
						base_form.findField('OrgStructLevelType_id').focus();
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 1].focus();
				},
				tabIndex: TABINDEX_OSEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OSEW + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OSEW + 12,
				onTabAction: function()
				{
					var base_form = form.formPanel.getForm();
					if ( !base_form.findField('OrgStruct_Code').disabled ) {
						base_form.findField('OrgStruct_Code').focus(true);
					}
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swOrgStructEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgStructEditWindow.superclass.show.apply(this, arguments);
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.owner = null;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		var base_form = this.formPanel.getForm();
		base_form.reset();
		
		if ( arguments[0].Org_id ) {
			base_form.findField('Org_id').setValue(arguments[0].Org_id);
			base_form.findField('OrgStructLevelType_id').setOrgId(arguments[0].Org_id);
		}
		
		if ( arguments[0].OrgStruct_id ) {
			base_form.findField('OrgStruct_id').setValue(arguments[0].OrgStruct_id);
		}
		
		if ( arguments[0].OrgStruct_pid ) {
			base_form.findField('OrgStruct_pid').setValue(arguments[0].OrgStruct_pid);
		}
		
		if ( arguments[0].OrgStruct_NumLevel ) {
			base_form.findField('OrgStruct_NumLevel').setValue(Number(arguments[0].OrgStruct_NumLevel) + 1);
		} else {
			base_form.findField('OrgStruct_NumLevel').setValue(1);
		}
		
		base_form.findField('OrgStructLevelType_id').setLevelNumber(base_form.findField('OrgStruct_NumLevel').getValue());
		base_form.findField('OrgStructLevelType_id').lastQuery = null;

		this.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_ORGSTRUCT_ORGSTRUCT_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_ORGSTRUCT_ORGSTRUCT_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_ORGSTRUCT_ORGSTRUCT_VIEW);
					this.enableEdit(false);
				}
				base_form.clearInvalid();
				
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						this.getLoadMask().hide();
					}.createDelegate(this),
					params: {
						OrgStruct_id: base_form.findField('OrgStruct_id').getValue()
					},
					success: function() {						
						this.getLoadMask().hide();
						
						var combo = base_form.findField('OrgStructLevelType_id');
						if (combo.getValue()) {
							combo.getStore().load({
								params: {
									'OrgStructLevelType_id': combo.getValue()
								},
								callback: function() {
									if (combo.getStore().getCount() > 0) {
										combo.setRawValue(combo.getFieldValue('OrgStructLevelType_Code') + '. ' + combo.getFieldValue('OrgStructLevelType_Name'));
									}
									combo.getStore().baseParams.OrgStructLevelType_id = null;
									combo.lastQuery = null;
								}
							});
						}
					}.createDelegate(this),
					url: '/?c=OrgStruct&m=loadOrgStructEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('OrgStruct_Code').disabled ) {
			base_form.findField('OrgStruct_Code').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});