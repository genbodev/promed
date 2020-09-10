/**
* swSignaEditWindow - окно редактирования сигны
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IVP (ipshon@rambler.ru)
* @version      18.09.2009
*/

/**
 * swSignaEditWindow - окно выбора ЛПУ, в случае если человек прикреплен к нескольким ЛПУ
 *
 * @class sw.Promed.swSignaEditWindow
 * @extends Ext.Window
 */
sw.Promed.swSignaEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	border: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	/**
	 * Запрос к серверу после ввода Signa
	 */
	doSave: function() {
		var current_window = this;
		var form = current_window.findById('SEW_SignaEditForm');

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('SignaEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.Error_Msg)
					{
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.Signa_id)
					{
						var response = new Object();
						var signa_id = action.result.Signa_id;

						form.findById('SEW_Signa_id').setValue(signa_id);
						form.ownerCt.action = 'edit';
						form.ownerCt.setTitle(WND_DLO_SIGNAEDIT);

						response.Signa_id = signa_id;
						response.Signa_Name = form.findById('SEW_Signa_Name').getValue();

						current_window.callback({ SignaData: response });
						current_window.hide();
					}
					else
					{
						if (action.result.Error_Msg)
						{
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else
						{
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}
				else
				{
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	id: 'SignaEditWindow',
	/**
	 * Конструктор
	 */
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function(button, event) {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: lang['sohranit']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'SEW_SignaEditForm',
				items: [{
					id: 'SEW_Signa_id',
					name: 'Signa_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					anchor: '95%',
					fieldLabel: 'Signa',
					id: 'SEW_Signa_Name',
					name: 'Signa_Name',
					width: 420,
					xtype: 'textfield'
				}],
				labelWidth: 50,
				layout: 'form',
				url: C_SIGNA_SAVE
			})]
		});
		sw.Promed.swSignaEditWindow.superclass.initComponent.apply(this, arguments);
	}, //end initComponent()
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swSignaEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('SEW_SignaEditForm');

		current_window.action = null;
		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		form.getForm().reset();

		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
			return false;
		}

		form.getForm().setValues(arguments[0]);

		if ( arguments[0].action )
		{
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback )
		{
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide )
		{
			current_window.onHide = arguments[0].onHide;
		}

		switch ( current_window.action )
		{
			case 'add':
				current_window.buttons[0].enable();
				current_window.findById('SEW_Signa_Name').enable();
				current_window.setTitle(WND_DLO_SIGNAADD);

				form.findById('SEW_Signa_Name').focus(true, 100);
				break;

			case 'edit':
				current_window.buttons[0].enable();
				current_window.findById('SEW_Signa_Name').enable();
				current_window.setTitle(WND_DLO_SIGNAEDIT);

				form.findById('SEW_Signa_Name').focus(true, 100);
				break;

			case 'view':
				current_window.buttons[0].disable();
				current_window.findById('SEW_Signa_Name').disable();
				current_window.setTitle(WND_DLO_SIGNAVIEW);

				current_window.buttons[3].focus();
				break;

			default:
				current_window.hide();
				return false;
				break;
		}
	},
	title: WND_DLO_SIGNAADD,
	width: 500
});