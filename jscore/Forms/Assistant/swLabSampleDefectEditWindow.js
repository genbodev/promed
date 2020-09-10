/**
* swLabSampleDefectEditWindow - редактирование дефекта пробы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Assistant
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      25.11.2013
* @comment      Префикс для id компонентов LSDEW (LabSampleDefectEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swLabSampleDefectEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLabSampleDefectEditWindow',
	objectSrc: '/jscore/Forms/Assistant/swLabSampleDefectEditWindow.js',

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
		if ( base_form.findField('EvnLabSample_BarCode').disabled ) {
			params.EvnLabSample_BarCode = base_form.findField('EvnLabSample_BarCode').getValue();
		}
		
		win.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						if (Ext.isEmpty(action.result.YesNo)) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.OKCANCEL,
								buttonText: {
									ok: 'OK',
									cancel: 'Отмена'
								},
								fn: function (buttonId, text, obj) {
									if (buttonId == 'ok') {
										base_form.findField('EvnLabSample_BarCode').focus(true);
									} else {
										win.hide();
					}
								}.createDelegate(this),
								icon: Ext.MessageBox.ERROR,
								msg: action.result.Error_Msg,
								title: 'Ошибка'
							});
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				win.getLoadMask().hide();

				if ( action.result && action.result.EvnLabSample_id > 0 ) {
					base_form.findField('EvnLabSample_id').setValue(action.result.EvnLabSample_id);

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
	id: 'LabSampleDefectEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'UslugaComplexAttributeEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'EvnLabSample_id' },
				{ name: 'MedService_sid'},
				{ name: 'MedServiceType_SysNick'},
				{ name: 'EvnLabSample_BarCode' },
				{ name: 'DefectCauseType_id' }
			]),
			url: '/?c=EvnLabSample&m=saveEvnLabSampleDefect',
			items: [{
				name: 'EvnLabSample_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_sid',
				xtype: 'hidden'
			}, {
				name: 'MedServiceType_SysNick',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Штрих-код пробы'),
				tabIndex: TABINDEX_LSDEW + 1,
				allowBlank: false,
				name: 'EvnLabSample_BarCode',
				disabled: false,
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
				anchor: '100%',
				maskRe: /[0-9]/
			}, {
				fieldLabel: lang['prichina_otbrakovki'],
				tabIndex: TABINDEX_LSDEW + 2,
				allowBlank: false,
				typeCode: 'int',
				hiddenName: 'DefectCauseType_id',
				prefix: 'lis_',
                comboSubject: 'DefectCauseType',
				anchor: '100%',
				xtype: 'swcommonsprcombo'
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

					if ( !base_form.findField('DefectCauseType_id').disabled ) {
						base_form.findField('DefectCauseType_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_LSDEW + 92,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_LSDEW + 93),
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
					if ( !base_form.findField('EvnLabSample_BarCode').disabled ) {
						base_form.findField('EvnLabSample_BarCode').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_LSDEW + 94,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swLabSampleDefectEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LabSampleDefectEditWindow');

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
		sw.Promed.swLabSampleDefectEditWindow.superclass.show.apply(this, arguments);

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

		if ( arguments[0].formParams.MedServiceType_SysNick) {
			this.MedServiceType_SysNick = arguments[0].formParams.MedServiceType_SysNick;
		}
		if ( arguments[0].formParams.MedService_sid) {
			this.MedService_sid = arguments[0].formParams.MedService_sid;
		}
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
				this.setTitle(lang['prichina_otbrakovki_dobavlenie']);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(lang['prichina_otbrakovki_redaktirovanie']);
					this.enableEdit(true);
					base_form.findField('EvnLabSample_BarCode').disable();
				}
				else {
					this.setTitle(lang['prichina_otbrakovki_prosmotr']);
					this.enableEdit(false);
				}

				this.getLoadMask().hide();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('EvnLabSample_BarCode').disabled ) {
			base_form.findField('EvnLabSample_BarCode').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 500
});
