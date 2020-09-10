/**
* swPolkaEvnPrescrConsEditWindow - окно добавления/редактирования назначения c типом «Консультация».
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      06.11.2012
* @comment      
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrConsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrConsEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrConsEditWindow.js',

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
	id: 'PolkaEvnPrescrConsEditWindow',
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
			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					this.doSave();
				break;

				case Ext.EventObject.J:
					this.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	doSave: function(options) {
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
					data.EvnPrescrConsData = base_form.getValues();
					data.EvnPrescrConsData.EvnPrescrCons_id = action.result.EvnPrescrCons_id;
					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
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

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PolkaEvnPrescrConsEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnPrescrCons_id' },
				{ name: 'EvnPrescrCons_pid' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'EvnPrescrCons_setDate' },
				{ name: 'EvnPrescrCons_IsCito' },
				{ name: 'EvnPrescrCons_Descr' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=savePolkaEvnPrescrCons',

			items: [{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrCons_id', // Идентификатор назначения
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrCons_pid', // Идентификатор события
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
				allowBlank: false,
				comboSubject: 'LpuSectionProfile',
				fieldLabel: lang['profil'],
				hiddenName: 'LpuSectionProfile_id',
				listeners: {
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				listWidth: 600,
				width: 350,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescrCons_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, {
				boxLabel: 'Cito',
				checked: false,
				labelSeparator: '',
				name: 'EvnPrescrCons_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrCons_Descr',
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
					this.FormPanel.getForm().findField('LpuSectionProfile_id').focus(true, 250);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPolkaEvnPrescrConsEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swPolkaEvnPrescrConsEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.parentEvnClass_SysNick = null;
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
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
		
		this.getLoadMask(LOAD_WAIT).show();
		var profil_combo = base_form.findField('LpuSectionProfile_id');
		switch ( this.action ) {
			case 'add':
				this.getLoadMask().hide();
				this.setTitle(lang['naznachenie_konsultatsii_dobavlenie']);
				this.setFieldsDisabled(false);
				base_form.clearInvalid();
				profil_combo.focus(true, 250);
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnPrescrCons_id': base_form.findField('EvnPrescrCons_id').getValue()
						,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
					},
					success: function(frm, act) {
						this.getLoadMask().hide();
						base_form.clearInvalid();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(lang['naznachenie_konsultatsii_redaktirovanie']);
							this.setFieldsDisabled(false);
						}
						else {
							this.setTitle(lang['naznachenie_konsultatsii_prosmotr']);
							this.setFieldsDisabled(true);
						}
						profil_combo.focus(true, 250);
					}.createDelegate(this),
					url: '/?c=EvnPrescr&m=loadEvnPrescrConsEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	}
});