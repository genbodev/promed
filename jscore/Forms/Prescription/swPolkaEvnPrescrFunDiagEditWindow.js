/**
* swPolkaEvnPrescrFunDiagEditWindow - окно добавления/редактирования назначения c типом Инструментальная диагностика.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRFDEF (PolkaEvnPrescrFunDiagEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrFunDiagEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrFunDiagEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrFunDiagEditWindow.js',

	action: null,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	autoHeight: true,
	width: 550,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	id: 'PolkaEvnPrescrFunDiagEditWindow',
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PolkaEvnPrescrFunDiagEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
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
		scope: this,
		stopEvent: false
	}],
	doSave: function(options) {
		var thas = this;
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

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

		params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
		params.signature = (options.signature)?1:0;

		var uslugaList = this.UslugaComplexPanel.getUslugaComboTextValues();

		base_form.findField('EvnPrescrFuncDiag_uslugaList').setValue(this.UslugaComplexPanel.getValues().toString());
		if(IS_DEBUG && base_form.findField('EvnPrescrFuncDiag_id').getValue()==''||base_form.findField('EvnPrescrFuncDiag_id').getValue()==null){

			var data = base_form.getValues();
			data.Usluga_List = uslugaList;

			this.callback(data);
			this.hide();

		}else{

			this.formStatus = 'save';
			this.getLoadMask(LOAD_WAIT_SAVE).show();

			base_form.submit({
				failure: function(result_form, action) {
					this.formStatus = 'edit';
					this.getLoadMask().hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {

					this.formStatus = 'edit';
					this.getLoadMask().hide();

					if ( action.result ) {

						var data = new Object();

						if(thas.winForm=='uslugaInput'){

							data = base_form.getValues();
							data.Usluga_List = uslugaList;

						}else{
							data.EvnPrescrFunDiagData = base_form.getValues();
							data.EvnPrescrFunDiagData.EvnPrescrFuncDiag_id = action.result.EvnPrescrFuncDiag_id;
						}

						this.callback(data);
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}.createDelegate(this)
			});
		}
	},
	setFieldsDisabled: function(d) 
	{
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		this.buttons[0].setDisabled(d);
	},
	initComponent: function() {

		this.UslugaComplexPanel = new sw.Promed.UslugaComplexPanel({
			win: this,
			PrescriptionType_Code: 12
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PolkaEvnPrescrFunDiagEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnPrescrFuncDiag_id' },
				{ name: 'EvnPrescrFuncDiag_pid' },
				{ name: 'EvnPrescrFuncDiag_uslugaList' },
				{ name: 'EvnPrescrFuncDiag_setDate' },
				{ name: 'EvnPrescrFuncDiag_IsCito' },
				{ name: 'EvnPrescrFuncDiag_Descr' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag',

			items: [{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrFuncDiag_id', // Идентификатор назначения
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrFuncDiag_pid', // Идентификатор события
				value: null,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrFuncDiag_uslugaList',//список ид услуг в строке через запятую
				value: null,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescrFuncDiag_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				onChange: function(field, newValue, oldValue) {
					var date_str = field.getRawValue() || null;
					this.UslugaComplexPanel.setUslugaComplexDate(date_str);
				}.createDelegate(this),
				xtype: 'swdatefield'
			},
			this.UslugaComplexPanel
			,{
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrFuncDiag_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrFuncDiag_Descr',
				width: 390,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
                hidden: true,
				handler: function() {
					this.doSave({signature: true});
				}.createDelegate(this),
				iconCls: 'signature16',
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.FormPanel.getForm().findField('EvnPrescrFuncDiag_setDate').focus(true, 250);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPolkaEvnPrescrFunDiagEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swPolkaEvnPrescrFunDiagEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.parentEvnClass_SysNick = null;
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.mode = 'save';
		this.winForm = null;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].winForm && typeof arguments[0].winForm == 'string' ) {
            this.winForm = arguments[0].winForm;
        }
		if ( arguments[0].formParams.mode && typeof arguments[0].formParams.mode == 'string' ) {
            this.mode = arguments[0].formParams.mode;
        }
		this.UslugaComplexPanel.UslugaComplex_Date = null;
		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
			case 'add':
				this.getLoadMask().hide();
				this.setTitle(lang['naznachenie_diagnosticheskih_protsedur_dobavlenie']);
				this.setFieldsDisabled(false);
				base_form.clearInvalid();
				this.UslugaComplexPanel.setValues([null]);
				base_form.findField('EvnPrescrFuncDiag_setDate').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				if(this.mode=='nosave'){
					this.getLoadMask().hide();
					base_form.clearInvalid();
					this.setTitle(lang['naznachenie_diagnosticheskih_protsedur_redaktirovanie']);
					this.setFieldsDisabled(false);
					this.UslugaComplexPanel.UslugaComplex_Date = base_form.findField('EvnPrescrFuncDiag_setDate').getRawValue();
					base_form.findField('EvnPrescrFuncDiag_setDate').focus(true, 250);
					var uslugalist_str = base_form.findField('EvnPrescrFuncDiag_uslugaList').getValue();
					var uslugalist_arr = (typeof uslugalist_str == 'string')?uslugalist_str.split(','):[null];
					this.UslugaComplexPanel.setValues(uslugalist_arr);
				}else{
					base_form.load({
						failure: function() {
							this.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'EvnPrescrFuncDiag_id': base_form.findField('EvnPrescrFuncDiag_id').getValue()
							,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
						},
						success: function(frm, act) {
							this.getLoadMask().hide();
							base_form.clearInvalid();
							if ( base_form.findField('accessType').getValue() == 'view' ) {
								this.action = 'view';
							}

							if ( this.action == 'edit' ) {
								this.setTitle(lang['naznachenie_diagnosticheskih_protsedur_redaktirovanie']);
								this.setFieldsDisabled(false);
								this.UslugaComplexPanel.UslugaComplex_Date = base_form.findField('EvnPrescrFuncDiag_setDate').getRawValue();
								base_form.findField('EvnPrescrFuncDiag_setDate').focus(true, 250);
							}
							else {
								this.setTitle(lang['naznachenie_diagnosticheskih_protsedur_prosmotr']);
								this.setFieldsDisabled(true);
							}
							var uslugalist_str = base_form.findField('EvnPrescrFuncDiag_uslugaList').getValue();
							var uslugalist_arr = (typeof uslugalist_str == 'string')?uslugalist_str.split(','):[null];
							this.UslugaComplexPanel.setValues(uslugalist_arr);
							if(this.winForm=='uslugaInput'){
								this.UslugaComplexPanel.disable();
							}else{
								this.UslugaComplexPanel.enable();
							}
						}.createDelegate(this),
						url: '/?c=EvnPrescr&m=loadEvnPrescrFuncDiagEditForm'
					});
				}
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	}
});