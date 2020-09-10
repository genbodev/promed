/**
* swUslugaPriceListEditWindow - окно редактирования/добавления позиции в парйс-лист услуг.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-19.01.2010
* @comment      Префикс для id компонентов UPLEF (UslugaPriceListEditForm)
*               tabIndex: 5201
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swUslugaPriceListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		var base_form = this.findById('UslugaPriceListEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('Usluga_id').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result && action.result.UslugaPriceList_id > 0 ) {
					base_form.findField('UslugaPriceList_id').setValue(action.result.UslugaPriceList_id);

					var usluga_code = '';
					var usluga_id = base_form.findField('Usluga_id').getValue();
					var usluga_name = '';

					var record = base_form.findField('Usluga_id').getStore().getById(usluga_id);
					if ( record ) {
						usluga_code = record.get('Usluga_Code');
						usluga_name = record.get('Usluga_Name');
					}

					var response = {
						'Usluga_Code': usluga_code,
						'Usluga_id': usluga_id,
						'Usluga_Name': usluga_name,
						'UslugaPriceList_id': base_form.findField('UslugaPriceList_id').getValue(),
						'UslugaPriceList_UET': base_form.findField('UslugaPriceList_UET').getValue()
					};

					this.callback({ uslugaPriceListData: response });
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('UslugaPriceListEditForm').getForm();

		if ( enable ) {
			base_form.findField('Usluga_id').enable();
			base_form.findField('UslugaPriceList_UET').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('Usluga_id').disable();
			base_form.findField('UslugaPriceList_UET').disable();
			this.buttons[0].hide();
		}
	},
	id: 'UslugaPriceListEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 5203,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
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
					if ( this.action != 'view' ) {
						this.findById('UslugaPriceListEditForm').getForm().findField('Usluga_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: 5204,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'UslugaPriceListEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'UslugaPriceList_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					codeField: 'Usluga_Code',
					displayField: 'Usluga_Name',
					emptyText: lang['vvedite_kod_uslugi'],
					enableKeyEvents: true,
					fieldLabel: lang['usluga'],
					hiddenName: 'Usluga_id',
					listeners: {
						'render': function(uslugaCombo) {
							uslugaCombo.addListener('keydown', function(combo, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this));
						}.createDelegate(this)
					},
					listWidth: 600,
					selectOnFocus: true,
					store: new Ext.db.AdapterStore({
						autoLoad: false,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'Usluga_Name', mapping: 'Usluga_Name' },
							{ name: 'Usluga_id', mapping: 'Usluga_id' },
							{ name: 'Usluga_Code', mapping: 'Usluga_Code' }
						],
						key: 'Usluga_id',
						sortInfo: { field: 'Usluga_Code' },
						tableName: 'Usluga'
					}),
					tabIndex: 5201,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 70px;"><font color="red">{Usluga_Code}.</font></td><td><h3>{Usluga_Name}</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'Usluga_id',
					width: 500,
					xtype: 'swbaselocalcombo'
				}, {
					allowBlank: false,
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['tsena_uslugi_uet'],
					name: 'UslugaPriceList_UET',
					tabIndex: 5202,
					width: 100,
					xtype: 'numberfield'
				}],
				layout: 'form',
				url: '/?c=Usluga&m=saveUslugaPriceList'
			})]
		});
		sw.Promed.swUslugaPriceListEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaPriceListEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					if ( current_window.action != 'view' ) {
						current_window.doSave();
					}
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaPriceListEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('UslugaPriceListEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( base_form.findField('Usluga_id').getStore().getCount() == 0 ) {
			var filter = 'where UslugaType_id = 2';
			var clause = '(record["UslugaType_id"] == 2)';

			if ( getGlobalOptions().region ) {
				switch ( getGlobalOptions().region.nick ) {
					case 'perm':
						filter = filter + " and SUBSTR(Usluga_Code, 3, 2) = '18'";
						clause = clause + ' && (record["Usluga_Code"].substr(2,2) == "18")';
					break;

					case 'ufa':
						filter = filter + " and Usluga_Code in (529000, 530000, 629000, 630000, 829000, 830000, 527000, 528000, 559000, 560000, 561000, 627000, 628000, 659000, 660000, 661000, 827000, 828000, 859000, 860000, 861000, 526000, 626000, 826000, 562000, 662000, 862000)";
						clause = clause + ' and record["Usluga_Code"].inlist([529000, 530000, 629000, 630000, 829000, 830000, 527000, 528000, 559000, 560000, 561000, 627000, 628000, 659000, 660000, 661000, 827000, 828000, 859000, 860000, 861000, 526000, 626000, 826000, 562000, 662000, 862000])';
					break;
				}
			}

			base_form.findField('Usluga_id').getStore().load({
				params: {
					where: filter,
					clause: {where: clause, limit: null}
				}
			});
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['spravochnik_uet_dobavlenie']);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('Usluga_id').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(lang['spravochnik_uet_redaktirovanie']);
				this.enableEdit(true);

				base_form.findField('Usluga_id').setValue(arguments[0].formParams.Usluga_id);

				loadMask.hide();
			break;

			case 'view':
				this.setTitle(lang['spravochnik_uet_prosmotr']);
				this.enableEdit(false);

				base_form.findField('Usluga_id').setValue(arguments[0].formParams.Usluga_id);

				loadMask.hide();
			break;
		}

		base_form.clearInvalid();
	},
	width: 700
});