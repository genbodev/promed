/**
* swOrgLicenceEditWindow - форма редактирования лицензий организации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      OrgStruct
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      09.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swOrgLicenceEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	autoHeight: true,
	width: 500,
	id: 'OrgLicenceEditWindow',
	title: WND_ORGSTRUCT_ORGLICENCE_ADD, 
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
				
				if ( action.result && action.result.OrgLicence_id > 0 ) {
					this.callback(this.owner, action.result.OrgLicence_id);
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
				{ name: 'OrgLicence_id' },
				{ name: 'Org_id' },
				{ name: 'OrgLicence_Ser' },
				{ name: 'OrgLicence_Num' },
				{ name: 'OrgLicence_setDate' },
				{ name: 'OrgLicence_RegNum' },
				{ name: 'OrgLicence_begDate' },
				{ name: 'OrgLicence_endDate' },
				{ name: 'Org_did' }
			]),
			url: '/?c=OrgStruct&m=saveOrgLicence',
			items: [{
				name: 'OrgLicence_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Org_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'OrgLicence_Ser',
				allowBlank: true,
				tabIndex: TABINDEX_OLEW + 0,
				fieldLabel: lang['seriya'],
				xtype: 'textfield'
			}, {
				name: 'OrgLicence_Num',
				allowBlank: false,
				tabIndex: TABINDEX_OLEW + 1,
				fieldLabel: lang['nomer'],
				xtype: 'textfield'
			}, {
				xtype: 'swdatefield',
				fieldLabel: lang['data_vyidachi'],
				format: 'd.m.Y',
				allowBlank: true,
				tabIndex: TABINDEX_OLEW + 2,
				name: 'OrgLicence_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				name: 'OrgLicence_RegNum',
				allowBlank: true,
				tabIndex: TABINDEX_OLEW + 3,
				fieldLabel: lang['registratsionnyiy_nomer'],
				xtype: 'textfield'
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['nachalo_deystviya'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_OLEW + 4,
				name: 'OrgLicence_begDate',
				endDateField: 'OrgLicence_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['okonchanie_deystviya'],
				format: 'd.m.Y',
				allowBlank: true,
				tabIndex: TABINDEX_OLEW + 5,
				name: 'OrgLicence_endDate',
				begDateField: 'OrgLicence_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}, {
				fieldLabel: lang['organizatsiya'],
				hiddenName: 'Org_did',
				allowBlank: true,
				xtype: 'sworgcombo',
				onTrigger1Click: function() {
					var combo = this;

					getWnd('swOrgSearchWindow').show({
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 ) {
								combo.getStore().load({
									params: {
										Object:'Org',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function() {
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
								});
							}

							getWnd('swOrgSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 200)}
					});
				}
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

					if ( !base_form.findField('Org_did').disabled ) {
						base_form.findField('Org_did').focus();
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 1].focus();
				},
				tabIndex: TABINDEX_OLEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OLEW + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OLEW + 12,
				onTabAction: function()
				{
					var base_form = form.formPanel.getForm();
					if ( !base_form.findField('OrgLicence_Ser').disabled ) {
						base_form.findField('OrgLicence_Ser').focus(true);
					}
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swOrgLicenceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgLicenceEditWindow.superclass.show.apply(this, arguments);
		
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
		
		base_form.findField('OrgLicence_begDate').setMinValue(undefined);
		base_form.findField('OrgLicence_begDate').setMaxValue(undefined);
		base_form.findField('OrgLicence_endDate').setMinValue(undefined);
		base_form.findField('OrgLicence_endDate').setMaxValue(undefined);
		
		if ( arguments[0].Org_id ) {
			base_form.findField('Org_id').setValue(arguments[0].Org_id);
		}
		
		if ( arguments[0].OrgLicence_id ) {
			base_form.findField('OrgLicence_id').setValue(arguments[0].OrgLicence_id);
		}
		
		this.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_ORGSTRUCT_ORGLICENCE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_ORGSTRUCT_ORGLICENCE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_ORGSTRUCT_ORGLICENCE_VIEW);
					this.enableEdit(false);
				}
				base_form.clearInvalid();
				
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						this.getLoadMask().hide();
					}.createDelegate(this),
					params: {
						OrgLicence_id: base_form.findField('OrgLicence_id').getValue()
					},
					success: function() {
						this.getLoadMask().hide();
						if ( base_form.findField('Org_did').getValue() ) {
							var Org_did = base_form.findField('Org_did').getValue();
							base_form.findField('Org_did').getStore().load({
								params: {
									Object:'Org',
									Org_id: Org_did,
									Org_Name:''
								},
								callback: function()
								{
									base_form.findField('Org_did').setValue(Org_did);
								}
							});
						}
					}.createDelegate(this),
					url: '/?c=OrgStruct&m=loadOrgLicenceEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('OrgLicence_Ser').disabled ) {
			base_form.findField('OrgLicence_Ser').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});