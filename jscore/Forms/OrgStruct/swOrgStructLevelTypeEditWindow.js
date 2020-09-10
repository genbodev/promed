/**
* swOrgStructLevelTypeEditWindow - форма редактирования типа структурного уровня
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
sw.Promed.swOrgStructLevelTypeEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	autoHeight: true,
	width: 500,
	id: 'OrgStructLevelTypeEditWindow',
	title: WND_ORGSTRUCT_ORGSTRUCTLEVELTYPE_ADD, 
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
				
				if ( action.result && action.result.OrgStructLevelType_id > 0 ) {
					this.callback(this.owner, action.result.OrgStructLevelType_id);
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
				{ name: 'OrgStructLevelType_id' },
				{ name: 'OrgType_id' },
				{ name: 'OrgStructLevelType_Code' },
				{ name: 'OrgStructLevelType_Name' },
				{ name: 'OrgStructLevelType_Nick' },
				{ name: 'OrgStructLevelType_SysNick' },
				{ name: 'OrgStructLevelType_begDT' },
				{ name: 'OrgStructLevelType_endDT' },
				{ name: 'OrgStructLevelType_LevelNumber' }
			]),
			url: '/?c=OrgStruct&m=saveOrgStructLevelType',
			items: [{
				name: 'OrgStructLevelType_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'OrgType_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'OrgStructLevelType_Code',
				allowBlank: false,
				tabIndex: TABINDEX_OSLTEW + 0,
				fieldLabel: lang['kod'],
				xtype: 'numberfield',
				maxValue: 999999,
				minValue: 0,
				allowNegative: false,
				allowDecimals: false
			}, {
				name: 'OrgStructLevelType_Name',
				allowBlank: false,
				tabIndex: TABINDEX_OSLTEW + 1,
				width: 300,
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield'
			}, {
				name: 'OrgStructLevelType_Nick',
				allowBlank: false,
				tabIndex: TABINDEX_OSLTEW + 2,
				width: 300,
				fieldLabel: lang['kratkoe_naimenovanie'],
				triggerClass: 'x-form-equil-trigger',
				onTriggerClick: function() {
					var base_form = form.formPanel.getForm();
					if ( base_form.findField('OrgStructLevelType_Nick').disabled ) {						
						return false;
					}
					var fullname = base_form.findField('OrgStructLevelType_Name').getValue();
					base_form.findField('OrgStructLevelType_Nick').setValue(fullname);
				},
				xtype: 'trigger'
			}, {
				name: 'OrgStructLevelType_SysNick',
				allowBlank: false,
				tabIndex: TABINDEX_OSLTEW + 3,
				width: 300,
				fieldLabel: lang['sistemnoe_naimenovanie'],
				xtype: 'textfield'
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_otkryitiya'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_OSLTEW + 4,
				name: 'OrgStructLevelType_begDT',
				endDateField: 'OrgStructLevelType_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_zakryitiya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_OSLTEW + 5,
				name: 'OrgStructLevelType_endDT',
				begDateField: 'OrgStructLevelType_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				xtype: 'numberfield',
				name: 'OrgStructLevelType_LevelNumber',
				maxValue: 999999,
				minValue: 0,
				allowNegative: false,
				allowDecimals: false,
				allowBlank: false,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				tabIndex: TABINDEX_OSLTEW + 6,
				fieldLabel: lang['nomer_urovnya']
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

					if ( !base_form.findField('OrgStructLevelType_LevelNumber').disabled ) {
						base_form.findField('OrgStructLevelType_LevelNumber').focus();
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 1].focus();
				},
				tabIndex: TABINDEX_OSLTEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OSLTEW + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OSLTEW + 12,
				onTabAction: function()
				{
					var base_form = form.formPanel.getForm();
					if ( !base_form.findField('OrgStructLevelType_Code').disabled ) {
						base_form.findField('OrgStructLevelType_Code').focus(true);
					}
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swOrgStructLevelTypeEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgStructLevelTypeEditWindow.superclass.show.apply(this, arguments);
		
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
		
		if ( arguments[0].OrgType_id ) {
			base_form.findField('OrgType_id').setValue(arguments[0].OrgType_id);
		}
		
		if ( arguments[0].OrgStructLevelType_id ) {
			base_form.findField('OrgStructLevelType_id').setValue(arguments[0].OrgStructLevelType_id);
		}
		
		this.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		
		base_form.findField('OrgStructLevelType_begDT').setMinValue(undefined);
		base_form.findField('OrgStructLevelType_begDT').setMaxValue(undefined);
		base_form.findField('OrgStructLevelType_endDT').setMinValue(undefined);
		base_form.findField('OrgStructLevelType_endDT').setMaxValue(undefined);
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_ORGSTRUCT_ORGSTRUCTLEVELTYPE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_ORGSTRUCT_ORGSTRUCTLEVELTYPE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_ORGSTRUCT_ORGSTRUCTLEVELTYPE_VIEW);
					this.enableEdit(false);
				}
				base_form.clearInvalid();
				
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						this.getLoadMask().hide();
					}.createDelegate(this),
					params: {
						OrgStructLevelType_id: base_form.findField('OrgStructLevelType_id').getValue()
					},
					success: function() {						
						this.getLoadMask().hide();
					}.createDelegate(this),
					url: '/?c=OrgStruct&m=loadOrgStructLevelTypeEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('OrgStructLevelType_Code').disabled ) {
			base_form.findField('OrgStructLevelType_Code').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});