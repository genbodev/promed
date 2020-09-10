/**
 * swCoeffIndexEditWindow - окно редактирования коэффициентов индексации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.10.2013
 */

sw.Promed.swCoeffIndexEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swCoeffIndexEditWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 600,

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var coeff_index_min = base_form.findField('CoeffIndex_Min').getValue();
		var coeff_index_max = base_form.findField('CoeffIndex_Max').getValue();

		if ( !Ext.isEmpty(coeff_index_max) && !Ext.isEmpty(coeff_index_min) && coeff_index_min > coeff_index_max ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['maksimalnoe_znachenie_doljno_byit_ne_menshe_minimalnogo'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask().show();

		var params = new Object();

		params = base_form.getValues();

		base_form.submit({
		failure: function(result_form, action) {
			wnd.getLoadMask().hide();
		},
		params: params,
		success: function(result_form, action) {
			wnd.getLoadMask().hide();

			if ( action.result ) {
				wnd.callback();
				wnd.hide();
			}
			else {
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki']);
			}
		}
		});
	},

	show: function() {
		sw.Promed.swCoeffIndexEditWindow.superclass.show.apply(this, arguments);

		this.action = null;
		var form = this;
		var base_form = form.FormPanel.getForm();

		if ( arguments && arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		base_form.reset();

		if ( this.action != 'add' && arguments[0].formParams ) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['koeffitsient_indeksatsii_dobavlenie']);

				base_form.clearInvalid();
				base_form.findField('CoeffIndex_Code').focus(true, 250);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				var coeff_index_id = base_form.findField('CoeffIndex_id').getValue();

				if ( !coeff_index_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				var afterFormLoad = function() {
					loadMask.hide();
					if ( form.action == 'edit' ) {
						form.setTitle(lang['koeffitsient_indeksatsii_redaktirovanie']);
						form.enableEdit(true);
					}
					else {
						form.setTitle(lang['koeffitsient_indeksatsii_prosmotr']);
						form.enableEdit(false);
					}
					base_form.clearInvalid();
					if ( form.action == 'edit' ) {
						base_form.findField('CoeffIndex_Code').focus(true, 250);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				};

				base_form.load({
					params: {CoeffIndex_id: coeff_index_id},
					failure: function() {
						afterFormLoad();
					},
					success: function() {
						afterFormLoad();
					},
					url: '/?c=CoeffIndex&m=loadCoeffIndexEditForm'
				});

				break;

			default:
				this.hide();
				break;
		}
	},

	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			bodyStyle: '{padding-top: 0.5em;}',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			id: 'CoeffIndexEditForm',
			url: '/?c=CoeffIndex&m=saveCoeffIndex',
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'CoeffIndex_id' },
				{ name: 'CoeffIndex_Code' },
				{ name: 'CoeffIndex_SysNick' },
				{ name: 'CoeffIndex_Name' },
				{ name: 'CoeffIndex_Min' },
				{ name: 'CoeffIndex_Max' }
			]),
			items: [
				{
					name: 'CoeffIndex_id',
					xtype: 'hidden'
				}, {
					allowBlank: false,
					fieldLabel: lang['kod'],
					name: 'CoeffIndex_Code',
					xtype: 'numberfield',
					width: 100
				}, {
					allowBlank: false,
					fieldLabel: lang['kratkoe_naimenovanie'],
					name: 'CoeffIndex_SysNick',
					xtype: 'textfield',
					width: 250
				}, {
					allowBlank: false,
					fieldLabel: lang['polnoe_naimenovanie'],
					name: 'CoeffIndex_Name',
					xtype: 'textfield',
					width: 250
				}, {
					fieldLabel: lang['minimalnoe_znachenie'],
					allowDecimals: true,
					allowNegative: false,
					name: 'CoeffIndex_Min',
					xtype: 'numberfield',
					width: 250
				}, {
					fieldLabel: lang['maksimalnoe_znachenie'],
					allowDecimals: true,
					allowNegative: false,
					name: 'CoeffIndex_Max',
					xtype: 'numberfield',
					width: 250
				}
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'CIEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'CIEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swCoeffIndexEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
