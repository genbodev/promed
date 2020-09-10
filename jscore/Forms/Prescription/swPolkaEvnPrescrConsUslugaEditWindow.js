/**
* swPolkaEvnPrescrConsUslugaEditWindow - окно добавления/редактирования назначения
 * c типом Консультационная услуга.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRLDEF (PolkaEvnPrescrConsUslugaEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrConsUslugaEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrConsUslugaEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrConsUslugaEditWindow.js',

	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	autoHeight: true,
	width: 550,
	closable: true,
	closeAction: 'hide',
	split: true,
	layout: 'form',
	id: 'PolkaEvnPrescrConsUslugaEditWindow',
	modal: true,
	plain: true,
	resizable: false,
	listeners: 
	{
		hide: function(win) 
		{
			win.onHide();
		}
	},
	doSave: function(options) 
	{
		var thas = this;
        if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = {};
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};
		params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
		params.signature = (options.signature)?1:0;
		params.UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
		if(this.mode=='nosave'){
			var data = new Object();
			data = base_form.getValues();
			data.Usluga_List = base_form.findField('UslugaComplex_id').lastSelectionText;
			data.UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
			thas.callback(data);
			thas.hide();
		}else{
			this.formStatus = 'save';
			this.getLoadMask(LOAD_WAIT_SAVE).show();
			base_form.submit({
				failure: function(result_form, action) {
					thas.formStatus = 'edit';
					thas.getLoadMask().hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				},
				params: params,
				success: function(result_form, action) {
					thas.formStatus = 'edit';
					thas.getLoadMask().hide();

					if ( action.result ) {
						var data = {};
						if(thas.winForm=='uslugaInput'){
							data = base_form.getValues();
							data.Usluga_List = base_form.findField('UslugaComplex_id').lastSelectionText;
							data.UslugaComplex_id= base_form.findField('UslugaComplex_id').getValue();
					}
						else{
							data.EvnPrescrConsUslugaData = base_form.getValues();
							data.EvnPrescrConsUslugaData.EvnPrescrConsUsluga_id = action.result.EvnPrescrConsUsluga_id;
						}
						thas.callback(data);
						thas.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}
			});
			return true;
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.FormPanel.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				combo.getStore().each(function(record) 
				{
					if (record && record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, combo.getStore().indexOfId(value));
					}
				});
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
	},
	
	show: function() 
	{
		sw.Promed.swPolkaEvnPrescrConsUslugaEditWindow.superclass.show.apply(this, arguments);
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
        var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
        uslugacomplex_combo.getStore().removeAll();
        uslugacomplex_combo.clearBaseParams();

		this.parentEvnClass_SysNick = null;
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.mode = 'save';
		this.winForm = null;
		
		var thas = this;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); });
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
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['naznachenie_konsultatsionnoy_uslugi_dobavlenie']);
				uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrConsUsluga_setDate').getRawValue());
				this.setFieldsDisabled(false);
				base_form.findField('EvnPrescrConsUsluga_setDate').focus(true, 250);
				break;
			case 'edit':
			case 'view':
				if(this.mode=='nosave'){
					thas.getLoadMask().hide();
					base_form.clearInvalid();
					thas.loadSpr('UslugaComplex_id', {UslugaComplex_id: uslugacomplex_combo.getValue()}, function() {
						//
					});
					thas.setTitle(lang['naznachenie_konsultatsionnoy_uslugi_redaktirovanie']);
					uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrConsUsluga_setDate').getRawValue());
					thas.setFieldsDisabled(false);
				}else{
					this.getLoadMask(LOAD_WAIT).show();
					base_form.load({
						failure: function() {
							thas.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { thas.hide(); } );
						},
						params: {
							'EvnPrescrConsUsluga_id': base_form.findField('EvnPrescrConsUsluga_id').getValue()
							,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
						},
						success: function() {
							thas.getLoadMask().hide();
							base_form.clearInvalid();
							if ( base_form.findField('accessType').getValue() == 'view' ) {
								thas.action = 'view';
							}
							thas.loadSpr('UslugaComplex_id', {UslugaComplex_id: uslugacomplex_combo.getValue()}, function() {
								//
							});

							if ( thas.action == 'edit' ) {
								thas.setTitle(lang['naznachenie_konsultatsionnoy_uslugi_redaktirovanie']);
								uslugacomplex_combo.setUslugaComplexDate(base_form.findField('EvnPrescrConsUsluga_setDate').getRawValue());
								thas.setFieldsDisabled(false);
								base_form.findField('EvnPrescrConsUsluga_setDate').focus(true, 250);
							}
							else {
								thas.setTitle(lang['naznachenie_konsultatsionnoy_uslugi_prosmotr']);
								thas.setFieldsDisabled(true);
							}
							if(this.winForm=="uslugaInput"){
									base_form.findField('UslugaComplex_id').disable();
									base_form.findField('UslugaComplex_id').enable();
								}
						}.createDelegate(this),
						url: '/?c=EvnPrescr&m=loadEvnPrescrConsUslugaEditForm'
					});
				}
				break;
			default:
				this.hide();
				break;
		}
        return true;
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PolkaEvnPrescrConsUslugaEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'center',
			items: 
			[{
				name: 'accessType', // Режим доступа
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrConsUsluga_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'EvnPrescrConsUsluga_pid',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'Server_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescrConsUsluga_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				onChange: function(field) {
					var date_str = field.getRawValue() || null;
                    form.FormPanel.getForm().findField('UslugaComplex_id').setUslugaComplexDate(date_str);
				},
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				value: null,
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				anchor:'99%',
				PrescriptionType_Code: 13,
				xtype: 'swuslugacomplexevnprescrcombo'
			} ,{
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrConsUsluga_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrConsUsluga_Descr',
				width: 390,
				xtype: 'textarea'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'accessType' },
				{name: 'EvnPrescrConsUsluga_id'},
				{name: 'EvnPrescrConsUsluga_pid'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'EvnPrescrConsUsluga_setDate'},
				{name: 'EvnPrescrConsUsluga_IsCito'},
				{name: 'EvnPrescrConsUsluga_Descr'},
				{name: 'UslugaComplex_id'}
			]),
			timeout: 600,
			url: '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga'
		});
		
		Ext.apply(this, 
		{
			buttons: [{
				handler: function() {
                    form.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
                hidden: true,
				handler: function() {
                    form.doSave({signature: true});
				},
				iconCls: 'signature16',
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
                    form.hide();
				},
				onTabAction: function () {
                    form.FormPanel.getForm().findField('EvnPrescrConsUsluga_setDate').focus(true, 250);
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});
		sw.Promed.swPolkaEvnPrescrConsUslugaEditWindow.superclass.initComponent.apply(this, arguments);
	}
});