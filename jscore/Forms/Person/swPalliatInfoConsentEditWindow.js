/**
* swPalliatInfoConsentEditWindow - Информированное согласие/отказ в рамках паллиативной помощи
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      11.12.2018
* @comment      Префикс для id компонентов PICEF (PalliatInfoConsentEditForm)
*/

sw.Promed.swPalliatInfoConsentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		if (!options) {
			options = {};
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var win = this;
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var params = new Object();

		var PalliatMedCareTypeLinkData = [];
		win.PalliatMedCareTypeLinkPanel.items.items.forEach(function(item) {
			if (item.checked && item.PalliatMedCareType_id) {
				PalliatMedCareTypeLinkData.push(item.PalliatMedCareType_id);
			}
		});
		params.PalliatMedCareTypeLinkData = Ext.util.JSON.encode(PalliatMedCareTypeLinkData);

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.PalliatInfoConsent_id > 0 ) {
						base_form.findField('PalliatInfoConsent_id').setValue(action.result.PalliatInfoConsent_id);
						base_form.findField('PalliatInfoConsentType_id').setValue(action.result.PalliatInfoConsentType_id);

						this.callback();
						this.hide();

						if (options.print) {
							var template = 'PersonPalliatConsent.rptdesign';
							if (base_form.findField('PalliatInfoConsentType_id').getValue() == 1 || base_form.findField('PalliatInfoConsentType_id').getValue() == 4) {
								template = 'PersonPalliatOtkaz.rptdesign';
							}
							printBirt({
								'Report_FileName': template,
								'Report_Params': '&paramPalliatInfoConsent=' + base_form.findField('PalliatInfoConsent_id').getValue(),
								'Report_Format': 'pdf'
							});

							if (base_form.findField('PalliatInfoConsentType_id').getValue() == 3) {
								template = 'PersonCardInfoOtkaz.rptdesign';
								printBirt({
									'Report_FileName': template,
									'Report_Params': '&paramPalliatInfoConsent=' + base_form.findField('PalliatInfoConsent_id').getValue(),
									'Report_Format': 'pdf'
								});
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	id: 'PalliatInfoConsentEditWindow',
	fillPalliatMedCareTypeLinkPanel: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		win.PalliatMedCareTypeLinkPanel.removeAll();
		base_form.findField('PalliatMedCareType_id').getStore().each(function(rec) {
			win.PalliatMedCareTypeLinkPanel.add(new Ext.form.Checkbox({
				hideLabel: true,
				PalliatMedCareType_id: rec.get('PalliatMedCareType_id'),
				boxLabel: rec.get('PalliatMedCareType_Name')
			}));
		});
	},
	initComponent: function() {
		var win = this;

		win.PalliatMedCareTypeLinkPanel = new Ext.form.FieldSet({
			autoHeight: true,
			title: 'Список медицинских мероприятий',
			layout: 'form'
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PalliatInfoConsentEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'PalliatInfoConsentType_id' },
				{ name: 'PalliatInfoConsent_id' },
				{ name: 'PalliatInfoConsent_consDT' },
				{ name: 'PalliatInfoConsent_isSelf' },
				{ name: 'Person_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'PalliatMedCareTypeLinkData' }
			]),
			url: '/?c=PalliatInfoConsent&m=savePalliatInfoConsent',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'PalliatInfoConsent_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				comboSubject: 'PalliatInfoConsentType',
				fieldLabel: langs('Тип события'),
				hiddenName: 'PalliatInfoConsentType_id',
				listeners: {
					change: function(combo, newValue) {
						var base_form = win.FormPanel.getForm();
						if (newValue == 1) {
							base_form.findField('PalliatInfoConsent_IsReject').hide();
						} else {
							base_form.findField('PalliatInfoConsent_IsReject').show();
						}
					}
				},
				tabIndex: TABINDEX_PICEF + 1,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				comboSubject: 'PalliatMedCareType',
				onLoadStore: function() {
					win.fillPalliatMedCareTypeLinkPanel();
				},
				fieldLabel: langs('Медицинское мероприятие'),
				hiddenName: 'PalliatMedCareType_id',
				tabIndex: TABINDEX_PICEF + 1,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата согласия'),
				format: 'd.m.Y',
				name: 'PalliatInfoConsent_consDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_PICEF + 2,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: langs('От лица'),
				hiddenName: 'PalliatInfoConsent_isSelf',
				triggerAction: 'all',
				forceSelection: true,
				editable: false,
				store: [
					[2, langs('Пациента')],
					[1, langs('Представителя')]
				],
				allowBlank: false,
				value: 2,
				tabIndex: TABINDEX_PICEF + 3,
				width: 400,
				xtype: 'combo'
			}, {
				bodyStyle: 'padding-left: 255px;',
				border: false,
				items: [{
					hideLabel: true,
					name: 'PalliatInfoConsent_IsReject',
					listeners: {
						change: function(field, checked) {
							if (checked) {
								win.PalliatMedCareTypeLinkPanel.show();
							} else {
								win.PalliatMedCareTypeLinkPanel.hide();
								win.PalliatMedCareTypeLinkPanel.items.items.forEach(function(item) {
									if (item.PalliatMedCareType_id) {
										item.setValue(false);
									}
								});
							}
							win.center();
							win.syncShadow();
						}
					},
					tabIndex: TABINDEX_PICEF + 4,
					boxLabel: 'Отказ от некоторых видов медицинских вмешательств',
					xtype: 'checkbox'
				}]
			}, win.PalliatMedCareTypeLinkPanel, {
				name: 'MedStaffFact_id',
				xtype: 'hidden'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'PICEF_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						print: true
					});
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						this.FormPanel.getForm().findField('PalliatInfoConsent_consDT').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PICEF + 5,
				text: BTN_FRMPRINT
			}, {
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_PICEF + 6,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_PICEF + 7),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('PalliatInfoConsent_consDT').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PICEF + 8,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPalliatInfoConsentEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PalliatInfoConsentEditWindow');

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
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPalliatInfoConsentEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
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

		this.PersonInfo.load({
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'PICEF_PersonInformationFrame', base_form.findField('PalliatInfoConsent_consDT'));
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		setMedStaffFactGlobalStoreFilter();
		base_form.findField('PalliatInfoConsent_consDT').setMaxValue(getGlobalOptions().date);
		base_form.findField('PalliatInfoConsentType_id').hideContainer();
		base_form.findField('PalliatMedCareType_id').hideContainer();
		this.center();

		this.syncShadow();

		switch ( this.action ) {
			case 'add':
				this.setTitle('Информированное согласие/отказ в рамках паллиативной помощи: Добавление');
				this.enableEdit(true);

				if (getGlobalOptions().CurMedStaffFact_id) {
					base_form.findField('MedStaffFact_id').setValue(getGlobalOptions().CurMedStaffFact_id);
				}

				if (getGlobalOptions().date) {
					base_form.findField('PalliatInfoConsent_consDT').setValue(getGlobalOptions().date);
				}

				if (base_form.findField('PalliatInfoConsentType_id').getValue() == 4) {
					win.PalliatMedCareTypeLinkPanel.items.items.forEach(function(item) {
						if (item.PalliatMedCareType_id) {
							item.setValue(false);
						}
					});
					base_form.findField('PalliatInfoConsent_IsReject').setValue(true);
				}

				loadMask.hide();

				base_form.findField('PalliatInfoConsentType_id').fireEvent('change', base_form.findField('PalliatInfoConsentType_id'), base_form.findField('PalliatInfoConsentType_id').getValue());
				base_form.findField('PalliatInfoConsent_IsReject').fireEvent('change', base_form.findField('PalliatInfoConsent_IsReject'), base_form.findField('PalliatInfoConsent_IsReject').getValue());
				base_form.findField('PalliatInfoConsent_consDT').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var PalliatInfoConsent_id = base_form.findField('PalliatInfoConsent_id').getValue();

				if ( !PalliatInfoConsent_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'PalliatInfoConsent_id': PalliatInfoConsent_id
					},
					success: function(form, action) {
						var PalliatMedCareTypeLinkData = [];
						if (action.result && action.result.data && action.result.data.PalliatMedCareTypeLinkData) {
							PalliatMedCareTypeLinkData = action.result.data.PalliatMedCareTypeLinkData;
						}

						var isReject = false;
						win.PalliatMedCareTypeLinkPanel.items.items.forEach(function(item) {
							if (item.PalliatMedCareType_id && item.PalliatMedCareType_id.inlist(PalliatMedCareTypeLinkData)) {
								isReject = true;
								item.setValue(true);
							} else {
								item.setValue(false);
							}
						});

						if (isReject) {
							base_form.findField('PalliatInfoConsent_IsReject').setValue(true);
						}

						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle('Информированное согласие/отказ в рамках паллиативной помощи: Редактирование');
							this.enableEdit(true);
						}
						else {
							this.setTitle('Информированное согласие/отказ в рамках паллиативной помощи: Просмотр');
							this.enableEdit(false);
						}

						loadMask.hide();

						base_form.findField('PalliatInfoConsentType_id').fireEvent('change', base_form.findField('PalliatInfoConsentType_id'), base_form.findField('PalliatInfoConsentType_id').getValue());
						base_form.findField('PalliatInfoConsent_IsReject').fireEvent('change', base_form.findField('PalliatInfoConsent_IsReject'), base_form.findField('PalliatInfoConsent_IsReject').getValue());

						if ( this.action == 'view' ) {
							this.buttons[this.buttons.length - 1].focus();
						}
						else {
							base_form.findField('PalliatInfoConsent_consDT').focus(true, 250);
						}
					}.createDelegate(this),
					url: '/?c=PalliatInfoConsent&m=loadPalliatInfoConsentEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 700
});