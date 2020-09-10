/**
* swOrganizationEditWindow - редактирование дефекта пробы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      23.06.2014
* @comment      Префикс для id компонентов ORGEW (OrganizationEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swOrganizationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swOrganizationEditWindow',
	objectSrc: '/jscore/Forms/Assistant/swOrganizationEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';
		
		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = new Object();		
		if ( base_form.findField('Organization_id').disabled ) {
			params.Organization_id = base_form.findField('Organization_id').getValue();
		}
		params.Organization_Name = base_form.findField('Organization_id').getFieldValue('Organization_Name');
		params.action = win.action;
		
		win.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				win.getLoadMask().hide();

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
				win.getLoadMask().hide();

				if ( action.result && action.result.Organization_id > 0 ) {
					base_form.findField('Organization_id').setValue(action.result.Organization_id);

					this.callback();
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'OrganizationEditWindow',
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'UslugaComplexAttributeEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Organization_id' },
				{ name: 'Organization_Code' },
				{ name: 'Org_id' }
			]),
			url: '/?c=Organization&m=save',
			items: [{
				name: 'Organization_Code',
				fieldLabel: lang['kod'],
				anchor: '100%',
				readOnly: true,
				xtype: 'textfield'
			}, {
				hiddenName: 'Organization_id',
				fieldLabel: lang['organizatsiya_v_lis'],
				allowBlank: false,
				anchor: '100%',
				readOnly: true,
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.FormPanel.getForm();
						base_form.findField('Organization_Code').setValue(combo.getFieldValue('Organization_Code'));
					}
				},
				xtype: 'swlisorganizationcombo'
			}, {
				fieldLabel: lang['mo'],
				anchor: '100%',
				hiddenName: 'Org_id',
				allowBlank: false,
				xtype: 'sworgcombo',
				onTrigger1Click: function() {
					if(!this.disabled){
						var combo = this;
						getWnd('swOrgSearchWindow').show({
							object: 'lpu',
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
				}
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('Org_id').disabled ) {
						base_form.findField('Org_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ORGEW + 92,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_ORGEW + 93),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('Organization_id').disabled ) {
						base_form.findField('Organization_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ORGEW + 94,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swOrganizationEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('OrganizationEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swOrganizationEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['svyazi_mo_s_organizatsiyami_v_lis_dobavlenie']);
				this.enableEdit(true);
				
				base_form.findField('Organization_id').getStore().load();
					
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(lang['svyazi_mo_s_organizatsiyami_v_lis_redaktirovanie']);
					this.enableEdit(true);
					base_form.findField('Organization_id').disable();
				}
				else {
					this.setTitle(lang['svyazi_mo_s_organizatsiyami_v_lis_prosmotr']);
					this.enableEdit(false);
				}
				
				if (!Ext.isEmpty(base_form.findField('Organization_id').getValue())) {
					base_form.findField('Organization_id').getStore().load({
						params: {
							Organization_id: base_form.findField('Organization_id').getValue()
						},
						callback: function() {
							base_form.findField('Organization_id').setValue(base_form.findField('Organization_id').getValue());
							base_form.findField('Organization_id').fireEvent('change', base_form.findField('Organization_id'), base_form.findField('Organization_id').getValue());
						}
					});
				}
				
				if (!Ext.isEmpty(base_form.findField('Org_id').getValue())) {
					base_form.findField('Org_id').getStore().load({
						params: {
							Object:'Org',
							Org_id: base_form.findField('Org_id').getValue(),
							Org_Name:''
						},
						callback: function() {
							base_form.findField('Org_id').setValue(base_form.findField('Org_id').getValue());
							base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'), base_form.findField('Org_id').getValue());
						}
					});
				}

				this.getLoadMask().hide();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('Organization_id').disabled ) {
			base_form.findField('Organization_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 500
});
