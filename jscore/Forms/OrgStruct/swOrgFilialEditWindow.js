/**
* swOrgFilialEditWindow - форма редактирования филиала
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
sw.Promed.swOrgFilialEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: false,
	autoHeight: true,
	width: 500,
	id: 'OrgFilialEditWindow',
	title: WND_ORGSTRUCT_ORGFILIAL_ADD, 
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
				
				if ( action.result && action.result.OrgFilial_id > 0 ) {
					this.callback(this.owner, action.result.OrgFilial_id);
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
				{ name: 'OrgFilial_id' },
				{ name: 'Org_id' },
				{ name: 'OrgFilial_oldid' }
			]),
			url: '/?c=OrgStruct&m=saveOrgFilial',
			items: [{
				name: 'OrgFilial_oldid',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['organizatsiya'],
				hiddenName: 'Org_id',
				allowBlank: false,
				xtype: 'sworgcombo',
				width: 300,
				readOnly: true,
				onTrigger1Click: function() {
					return false;
				},
				onTrigger2Click: function() {
					return false;
				}
			}, {
				fieldLabel: lang['filial'],
				hiddenName: 'OrgFilial_id',
				allowBlank: false,
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
				},
				width: 300
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

					if ( !base_form.findField('OrgFilial_id').disabled ) {
						base_form.findField('OrgFilial_id').focus();
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 1].focus();
				},
				tabIndex: TABINDEX_OFEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OFEW + 11),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_OFEW + 12,
				onTabAction: function()
				{
					var base_form = form.formPanel.getForm();
					if ( !base_form.findField('OrgFilial_id').disabled ) {
						base_form.findField('OrgFilial_id').focus(true);
					}
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swOrgFilialEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrgFilialEditWindow.superclass.show.apply(this, arguments);
		
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
			var Org_id = arguments[0].Org_id;
			base_form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: Org_id,
					Org_Name:''
				},
				callback: function()
				{
					base_form.findField('Org_id').setValue(Org_id);
				}
			});
		}
		
		if ( arguments[0].OrgFilial_id ) {
			var OrgFilial_id = arguments[0].OrgFilial_id;
			base_form.findField('OrgFilial_oldid').setValue(OrgFilial_id);
			base_form.findField('OrgFilial_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: OrgFilial_id,
					Org_Name:''
				},
				callback: function()
				{
					base_form.findField('OrgFilial_id').setValue(OrgFilial_id);
				}
			});
		}
		
		this.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_ORGSTRUCT_ORGFILIAL_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_ORGSTRUCT_ORGFILIAL_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_ORGSTRUCT_ORGFILIAL_VIEW);
					this.enableEdit(false);
				}
				base_form.clearInvalid();
				
				this.getLoadMask().hide();
				/* получаем все необходимые данные параметрами, загрузка с сервера не нужна.
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						this.getLoadMask().hide();
					}.createDelegate(this),
					params: {
						OrgFilial_id: base_form.findField('OrgFilial_id').getValue()
					},
					success: function() {						
						this.getLoadMask().hide();
					}.createDelegate(this),
					url: '/?c=OrgStruct&m=loadOrgFilialEditForm'
				});*/
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('Org_id').disabled ) {
			base_form.findField('Org_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	}
});