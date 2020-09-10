/**
 * swEvnObservEditWindow - окно для редактирования данных наблюдения за пациентом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Prescription
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version      0.001-05.10.2011
 * @comment      Префикс для id компонентов EOBSEF (EvnObservEditForm)
 */
/*NO PARSE JSON*/

sw.Promed.swEvnObservEditWindow = Ext.extend(sw.Promed.BaseForm, {
	doSave: function(options) {
		options = options || {};
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.formStatus = 'save';

		var params = {};

		var EvnObservDataList = [];
		base_form.items.each(function(field) {
			var name = field.getName(), value = field.getValue();
			if (match = name.match(/^val_(\d+)$/)) {
				EvnObservDataList.push({
					ObservParamType_id: match[1],
					EvnObservData_Value: value
				});
			}
		});

		params.EvnObservDataList = Ext.util.JSON.encode(EvnObservDataList);

		if ( base_form.findField('ObservTimeType_id').disabled ) {
			params.ObservTimeType_id = base_form.findField('ObservTimeType_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		var url = '/?c=EvnObserv&m=saveEvnObserv';

		base_form.submit({
			url: url,
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnObserv_id ) {
					base_form.findField('EvnObserv_id').setValue(action.result.EvnObserv_id);
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
	//height: 450,
	autoHeight: true,
	id: 'EvnObservEditWindow',

	refreshFieldsVisibility: function() {
		var base_form = this.FormPanel.getForm();
		var hasAge = !Ext.isEmpty(this.Person_Age);

		var val_10 = base_form.findField('val_10');
		var val_10_allow = (!hasAge || this.Person_Age >= 1);
		val_10.setContainerVisible(val_10_allow);
		if (!val_10_allow) {
			val_10.setAllowBlank(true);
			val_10.setValue(null);
		}

		var val_11 = base_form.findField('val_11');
		var val_11_allow = (!hasAge || this.Person_Age >= 1);
		val_11.setContainerVisible(val_11_allow);
		if (!val_11_allow) {
			val_11.setAllowBlank(true);
			val_11.setValue(null);
		}

		var val_12 = base_form.findField('val_12');
		var val_12_allow = (hasAge && this.Person_Age < 1);
		val_12.setContainerVisible(val_12_allow);
		if (!val_12_allow) {
			val_12.setAllowBlank(true);
			val_12.setValue(null);
		}

		var val_13 = base_form.findField('val_13');
		var val_13_allow = (hasAge && this.Person_Age < 1);
		val_13.setContainerVisible(val_13_allow);
		if (!val_13_allow) {
			val_13.setAllowBlank(true);
			val_13.setValue(null);
		}

		this.syncShadow();
	},

	loadEvnObservData: function(callback) {
		callback = callback || Ext.emptyFn;
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		var url = '/?c=EvnObserv&m=loadEvnObservForm';
		var params = {
			EvnObserv_id: base_form.findField('EvnObserv_id').getValue()
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		base_form.load({
			params: params,
			failure: function() {
				loadMask.hide();
			},
			success: function(form, action) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(action.response.responseText);

				var Person_Birthday = base_form.findField('Person_Birthday').getValue();
				var EvnObserv_setDate = base_form.findField('EvnObserv_setDate').getValue();

				if (!Ext.isEmpty(Person_Birthday)) {
					Person_Birthday = Date.parseDate(Person_Birthday, 'd.m.Y');
					wnd.Person_Age = swGetPersonAge(Person_Birthday, EvnObserv_setDate);
				}
				wnd.refreshFieldsVisibility();

				callback();
			},
			url: url
		});
	},

	initComponent: function() {
		var thas = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnObservEditForm',
			labelAlign: 'right',
			labelWidth: 160,

			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'EvnObserv_id'},
				{name: 'EvnObserv_pid'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'Person_Birthday'},
				{name: 'EvnObserv_setDate'},
				{name: 'ObservTimeType_id'},
				{name: 'val_1'},
				{name: 'val_2'},
				{name: 'val_3'},
				{name: 'val_4'},
				{name: 'val_5'},
				{name: 'val_6'},
				{name: 'val_7'},
				{name: 'val_8'},
				{name: 'val_9'},
				{name: 'val_10'},
				{name: 'val_11'},
				{name: 'val_12'},
				{name: 'val_13'}
			]),

			items: [{
				name: 'EvnObserv_id',
				xtype: 'hidden'
			}, {
				name: 'EvnObserv_pid', // Идентификатор родительского события
				xtype: 'hidden'
			}, {
				name: 'Person_id', // Идентификатор человека
				xtype: 'hidden'
			}, {
				name: 'Person_Birthday', // Дата рождения человека
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				xtype: 'hidden'
			}, {
				name: 'PersonNewBorn_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['data'],
				name: 'EvnObserv_setDate',
				value: getGlobalOptions().date,
				xtype: 'swdatefield',
				listeners:{
					'change': function(field, newValue, oldValue){
						var base_form = thas.FormPanel.getForm();

						var Person_Birthday = base_form.findField('Person_Birthday').getValue();

						if (!Ext.isEmpty(Person_Birthday)) {
							Person_Birthday = Date.parseDate(Person_Birthday, 'd.m.Y');

							thas.Person_Age = swGetPersonAge(Person_Birthday, newValue);
						}
						thas.refreshFieldsVisibility();
					}
				}
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'ObservTimeType',
				hiddenName: 'ObservTimeType_id',
				fieldLabel: 'Время'
			}, {
				layout:'column',
				width:500,
				items:[{
					layout:'form',
					width:270,
					items:[{
						layout:'column',
						items:[{
							layout:'form',
							items:[{
								fieldLabel: lang['art_davlenie'],
								name: 'val_1',
								width: 30,
								allowDecimals: false,
								allowNegative: false,
								xtype: 'textfield',
								regex: new RegExp("^[0-9]{2,3}$"),
								regexText:'80-140',
								maskRe: /[0-9]/
							}]
						},{
							xtype:'label',
							html: '/',
							style: 'padding:2px;font-size:13px;'
						},{
							layout:'form',
							items:[{
								hideLabel:true,
								name: 'val_2',
								width: 30,
								allowDecimals: false,
								allowNegative: false,
								xtype: 'textfield',
								regex: new RegExp("^[0-9]{2,3}$"),
								regexText:'110-60',
								maskRe: /[0-9]/
							}]
						}]
					}, {
						layout:'column',
						items:[{
							layout:'form',
							items:[{
								fieldLabel: lang['temperatura'],
								//hideLabel:true,
								name: 'val_4',
								plugins: [new Ext.ux.InputTextMask('99.9',true)],
								width: 70,
								allowDecimals: true,
								allowNegative: false,
								xtype: 'textfield'
							}]
						},{
							xtype:'label',
							html: '°C',
							style: 'margin-left:7px;font-size:13px;'
						}]
					},{
						fieldLabel: lang['puls'],
						name: 'val_3',
						width: 70,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}]
				}]
			},{
				xtype:'label',
				html: '<hr>'
			},{layout:'column',items:[{layout:'form',items:[
				{
					fieldLabel: lang['chastota_dyihaniya'],
					name: 'val_5',
					width: 50,
					allowDecimals: false,
					allowNegative: false,
					xtype: 'numberfield'
				}]},{
				xtype:'label',
				html: lang['v_minutu'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
				{
					fieldLabel: lang['ves'],
					name: 'val_6',
					width: 50,
					allowDecimals: true,
					allowNegative: false,
					xtype: 'numberfield'
				}]},{
				xtype:'label',
				html: lang['kg'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
				{
					fieldLabel: lang['vyipito_jidkosti'],
					name: 'val_7',
					width: 50,
					allowDecimals: true,
					allowNegative: false,
					xtype: 'numberfield'
				}]},{
				xtype:'label',
				html: lang['ml'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
				{
					fieldLabel: lang['sutochnoe_kol-vo_mochi'],
					name: 'val_8',
					width: 50,
					allowDecimals: false,
					allowNegative: false,
					xtype: 'numberfield'
				}]},{
				xtype:'label',
				html: lang['ml'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{
				xtype:'label',
				html: '<hr>'
			},{
				xtype:'swyesnocombo',
				fieldLabel: lang['stul'],
				hiddenName: 'val_9'
			},{
				xtype:'swyesnocombo',
				fieldLabel: lang['vanna'],
				hiddenName: 'val_10'
			},{
				xtype:'swyesnocombo',
				fieldLabel: lang['smena_belya'],
				hiddenName: 'val_11'
			},{
				xtype:'swcommonsprcombo',
				comboSubject: 'ObservPesultType',
				fieldLabel: lang['reaktsiya_na_osmotr'],
				hiddenName: 'val_13',
			},{
				xtype:'swcommonsprcombo',
				comboSubject: 'ObservPesultType',
				fieldLabel: lang['reaktsiya_zrachka'],
				hiddenName: 'val_12',
			}]
		});


		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					text: BTN_FRMSAVE
				}, {
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide()
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.FormPanel
			]

		});

		sw.Promed.swEvnObservEditWindow.superclass.initComponent.apply(this, arguments);
	},

	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnObservEditWindow');

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

	layout: 'form',
	loadMask: null,
	maximizable: false,
	maximized: false,
	minHeight: 450,
	minWidth: 450,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,

	show: function() {
		sw.Promed.swEvnObservEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.action = 'view';
		this.Person_Age = null;

		if ( !arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if (arguments[0].action && arguments[0].action.inlist(['add','edit','view'])) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		// Тип наблюдения 1-утро, 2-день, 3-вечер
		if ( arguments[0].formParams && arguments[0].formParams.ObservTimeType_id ) {
			this.ObservTimeType_id = arguments[0].formParams.ObservTimeType_id;
		}
		// Кем открыта форма врачом или м/с
		if ( arguments[0].disableChangeTime ) {
			this.OpenedByMS = true;
		} else {
			this.OpenedByMS = false;
		}

		var Person_Birthday = base_form.findField('Person_Birthday').getValue();
		var EvnObserv_setDate = base_form.findField('EvnObserv_setDate').getValue();

		if (!Ext.isEmpty(Person_Birthday)) {
			Person_Birthday = Date.parseDate(Person_Birthday, 'd.m.Y');

			this.Person_Age = swGetPersonAge(Person_Birthday, EvnObserv_setDate);
		}
		this.refreshFieldsVisibility();

		this.enableEdit(this.action != 'view');

		switch(this.action) {
			case 'add':
				this.setTitle('Наблюдение: Добавление');
				break;
			case 'edit':
				this.setTitle('Наблюдение: Редактирование');
				break;
			case 'view':
				this.setTitle('Наблюдение: Просмотр');
				break;
		}

		if (this.action != 'add') {
			this.loadEvnObservData();
		}

		base_form.clearInvalid();
	},

	width: 550
});