/**
 * swRegistryEditWindow - окно редактирования/добавления реестра (счета) (Вологда).
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @region       Krasnoyarsk
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Stanislav Bykov
 * @version      07.12.2018
 * @comment      Префикс для id компонентов REF (RegistryEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              Registry_id - ID реестра
 */

sw.Promed.swRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	firstTabIndex: 15150,
	id: 'RegistryEditWindow',
	layout: 'form',
	modal: true,
	plain: true,
	resizable: false,
	split: true,
	width: 600,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function(options) {
		let
			base_form = this.RegistryForm.getForm(),
			win = this;

		if ( typeof options != 'object' ) {
			options = {};
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.RegistryForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		let
			begDate = base_form.findField('Registry_begDate').getValue(),
			endDate = base_form.findField('Registry_endDate').getValue();

		if ( typeof begDate == 'object' && typeof endDate == 'object' ) {
			if ( begDate > endDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('Registry_begDate').focus(false);
					},
					icon: Ext.Msg.ERROR,
					msg: 'Дата окончания не может быть меньше даты начала.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (!options.ignoreNotEqualPeriods && begDate.format('Ym') != endDate.format('Ym')) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreNotEqualPeriods = true;
							win.doSave(options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Рекомендуется формировать реестры за один календарный месяц. Продолжить формирование?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}

		win.doSubmit();

		return true;
	},
	doSubmit: function() {
		let
			base_form = this.RegistryForm.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование реестра..."}),
			win = this;

		loadMask.show();

		let Registry_accDate = base_form.findField('Registry_accDate').getValue().dateFormat('d.m.Y');

		let params = {
			RegistryType_id: base_form.findField('RegistryType_id').getValue(),
			Registry_accDate: Registry_accDate
		};

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.RegistryQueue_id ) {
						let records = {
							RegistryQueue_id: action.result.RegistryQueue_id,
							RegistryQueue_Position: action.result.RegistryQueue_Position
						};

						win.callback(win.owner, action.result.RegistryQueue_id, records);
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: langs('При выполнении операции сохранения произошла ошибка') + '.<br/>' + langs('Пожалуйста, повторите попытку позже') + '.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) {
		let base_form = this.RegistryForm.getForm();

		if ( enable ) {
			base_form.findField('DispClass_id').enable();
			base_form.findField('KatNasel_id').enable();
			base_form.findField('LpuBuilding_id').enable();
			base_form.findField('OrgRSchet_id').enable();
			base_form.findField('Registry_accDate').enable();
			base_form.findField('Registry_begDate').enable();
			base_form.findField('Registry_endDate').enable();
			base_form.findField('Registry_Num').enable();

			this.buttons[0].show();
		}
		else  {
			base_form.findField('DispClass_id').disable();
			base_form.findField('KatNasel_id').disable();
			base_form.findField('LpuBuilding_id').disable();
			base_form.findField('OrgRSchet_id').disable();
			base_form.findField('Registry_accDate').disable();
			base_form.findField('Registry_begDate').disable();
			base_form.findField('Registry_endDate').disable();
			base_form.findField('Registry_Num').disable();

			this.buttons[0].hide();
		}
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swRegistryEditWindow.superclass.show.apply(this, arguments);

		let
			base_form = this.RegistryForm.getForm(),
			form = this;

		if ( !arguments[0] || !arguments[0].RegistryType_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны нужные входные параметры.',
				title: 'Ошибка'
			});
			form.hide();
			return false;
		}

		base_form.reset();

		form.action = 'add';
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.owner = null;
		form.Registry_id = null;
		form.RegistryStatus_id = null;
		form.RegistryType_id = null;

		if ( arguments[0].action ) {
			form.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].owner ) {
			form.owner = arguments[0].owner;
		}

		if ( !Ext.isEmpty(arguments[0].Registry_id) ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if ( !Ext.isEmpty(arguments[0].RegistryStatus_id) ) {
			form.RegistryStatus_id = arguments[0].RegistryStatus_id;
		}

		if ( !Ext.isEmpty(arguments[0].RegistryType_id) ) {
			form.RegistryType_id = arguments[0].RegistryType_id;
		}

		base_form.findField('DispClass_id').setAllowBlank(!form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));
		base_form.findField('DispClass_id').setContainerVisible(form.RegistryType_id.toString().inlist([ '7', '9', '12' ]));

		if ( form.RegistryType_id.toString().inlist([ '7', '9', '12' ]) ) {
			let dispClassList = [];

			switch ( form.RegistryType_id ) {
				case 7: // Дисп-ция взр. населения
					dispClassList = [ '1', '2' ];
					break;

				case 9: // Дисп-ция детей-сирот
					dispClassList = [ '3', '4', '7', '8' ];
					break;

				case 12: // Медосмотры несовершеннолетних
					dispClassList = [ '10', '12' ];
					break;
			}

			base_form.findField('DispClass_id').getStore().clearFilter();
			base_form.findField('DispClass_id').lastQuery = '';
			base_form.findField('DispClass_id').getStore().filterBy(function(rec) {
				return (rec.get('DispClass_Code').toString().inlist(dispClassList));
			});
		}

		if ( form.action == 'edit' ) {
			form.buttons[0].setText('Переформировать');
		}
		else {
			form.buttons[0].setText('Сохранить');
		}

		// Если реестр уже помечен как оплачен, то не надо его переформировывать
		if ( form.RegistryStatus_id == 4 ) {
			form.action = "view";
		}

		base_form.setValues(arguments[0]);
		base_form.findField('DispClass_id').fireEvent('change', base_form.findField('DispClass_id'), base_form.findField('DispClass_id').getValue());

		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		form.syncSize();
		form.syncShadow();

		if ( base_form.findField('OrgRSchet_id').getStore().getCount() == 0 ) {
			base_form.findField('OrgRSchet_id').getStore().load({
				params: {
					object: 'OrgRSchet',
					OrgRSchet_id: '',
					OrgRSchet_Name: ''
				}
			});
		}

		let loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch ( form.action ) {
			case 'add':
				form.setTitle(WND_ADMIN_REGISTRYADD);
				form.enableEdit(true);

				loadMask.hide();

				base_form.findField('Registry_begDate').focus(true, 50);
				break;

			case 'edit':
				form.setTitle(WND_ADMIN_REGISTRYEDIT);
				form.enableEdit(true);
				break;

			case 'view':
				form.setTitle(WND_ADMIN_REGISTRYVIEW);
				form.enableEdit(false);
				break;
		}

		// устанавливаем дату счета и запрещаем для редактирования
		base_form.findField('Registry_accDate').disable();

		if ( form.action != 'add' ){
			base_form.load({
				params: {
					Registry_id: form.Registry_id,
					RegistryType_id: form.RegistryType_id
				},
				failure: function() {
					loadMask.hide();

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function() {
					loadMask.hide();

					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);

					if ( form.action == 'edit' ) {
						base_form.findField('Registry_begDate').focus(true, 50);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				},
				url: '/?c=Registry&m=loadRegistry'
			});
		}
		else {
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
		}
	},

	/* конструктор */
	initComponent: function() {
		// Форма с полями
		let form = this;

		this.RegistryForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'RegistryEditForm',
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryStatus_id',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				disabled: true,
				hiddenName: 'RegistryType_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swregistrytypecombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}, {
				anchor: '100%',
				comboSubject: 'DispClass',
				hiddenName: 'DispClass_id',
				fieldLabel: 'Тип диспансеризации',
				lastQuery: '',
				tabIndex: form.firstTabIndex++,
				typeCode: 'int',
				xtype: 'swcommonsprcombo'
			}, {
				anchor: '100%',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				linkedElements: [],
				tabIndex: form.firstTabIndex++,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Категория населения',
				hiddenName: 'KatNasel_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'swkatnaselcombo'
			}, {
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "10",
					autocomplete: "off"
				},
				fieldLabel: 'Номер счета',
				name: 'Registry_Num',
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				anchor: '100%',
				allowBlank: false,
				hiddenName: 'OrgRSchet_id',
				tabIndex: form.firstTabIndex++,
				xtype: 'sworgrschetcombo'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата счета',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: form.firstTabIndex++,
				width: 100,
				xtype: 'swdatefield'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if (form.action != 'view') {
								form.doSave(false);
							}
							break;

						case Ext.EventObject.J:
							form.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'DispClass_id' },
				{ name: 'KatNasel_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'OrgRSchet_id' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Registry_Num' },
				{ name: 'RegistryStatus_id' },
				{ name: 'RegistryType_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex++,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, form.firstTabIndex++),
				{
					handler: function() {
						form.hide();
					},
					iconCls: 'cancel16',
					tabIndex: form.firstTabIndex++,
					text: BTN_FRMCANCEL
				}],
			items: [
				form.RegistryForm
			]
		});

		sw.Promed.swRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});