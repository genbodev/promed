/**
 * swMorbusNephroDrugWindow - Лекарственное лечение.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 */
sw.Promed.swMorbusNephroDrugWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 450,
	modal: true,
	action: null,
	storeActive: 0,
	closable: true,
	draggable: true,
	autoHeight: true,
	formMode: 'remote',
	formStatus: 'edit',
	buttonAlign: 'left',
	closeAction: 'hide',
	shadow: false,
	
	callback: Ext.emptyFn,
	winTitle: langs('Курс лекарственного лечения'),
	listeners: {
		hide: function() {
			this.storeActive = 0;
			this.setUnavailable('MorbusNephroDrug_Dose');
			this.setUnavailable('MorbusNephroDrug_Multi');
			this.setUnavailable('MorbusNephroDrug_SumDose');
			this.setUnavailable('Unit_id');
			this.setUnavailable('DrugComplexMnn_id');
			this.setUnavailable('MorbusNephroDrug_begDT');
			this.setUnavailable('MorbusNephroDrug_endDT');
			this.setUnavailable('SchemeDescription');
			this.FormPanel.getForm().findField('NoEffectFromSchemeId').setContainerVisible(false);
			//this.FormPanel.getForm().findField('EvnVK_FieldSet').setContainerVisible(false);
			Ext.getCmp('EvnVK_FieldSet').setVisible(false);
		}
	},
	checkStoreLoaded: function () {
		if (this.storeActive == 8 && this.action != "view") {
			this.getAllowedScheme();
		}
	},
	doSave: function () {
		var wnd = this;
		if (wnd.formStatus == 'save') {
			return false;
		}

		wnd.formStatus = 'save';

		var form = wnd.FormPanel;
		var base_form = form.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					wnd.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var params = {};
		var data = {};

		switch (wnd.formMode) {
			case 'local':
				data.BaseData = {
					'MorbusNephro_id': base_form.findField('MorbusNephro_id').getValue(),
					'MorbusNephroDrug_id': base_form.findField('MorbusNephroDrug_id').getValue(),
					'MorbusNephroDrug_begDT': base_form.findField('MorbusNephroDrug_begDT').getValue(),
					'MorbusNephroDrug_endDT': base_form.findField('MorbusNephroDrug_endDT').getValue(),
					'NephroDrugScheme_id': base_form.findField('NephroDrugScheme_id').getValue(),
					'EvnVK_id': base_form.findField('EvnVK_id').getValue()
				};
				wnd.callback(data);
				wnd.formStatus = 'edit';
				loadMask.hide();
				wnd.hide();
				break;
			case 'remote':
				base_form.submit({
					params: params,
					success: function (result_form, action) {
						wnd.formStatus = 'edit';
						loadMask.hide();
						if (action.result) {
							if (action.result.MorbusNephroDrug_id > 0) {
								base_form.findField('MorbusNephroDrug_id').setValue(action.result.MorbusNephroDrug_id);

								data.BaseData = {
									'MorbusNephro_id': base_form.findField('MorbusNephro_id').getValue(),
									'MorbusNephroDrug_id': base_form.findField('MorbusNephroDrug_id').getValue(),
									'MorbusNephroDrug_begDT': base_form.findField('MorbusNephroDrug_begDT').getValue(),
									'MorbusNephroDrug_endDT': base_form.findField('MorbusNephroDrug_endDT').getValue(),
									'NephroDrugScheme_id': base_form.findField('NephroDrugScheme_id').getValue(),
									'EvnVK_id': base_form.findField('EvnVK_id').getValue()
								};
								wnd.storeActive = 0;
								wnd.callback(data);
								wnd.hide();
							} else {
								if (action.result.Error_Msg) {
									sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								}
							}
						} else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
					},
					failure: function (result_form, action) {
						wnd.formStatus = 'edit';
						loadMask.hide();
						if (action.result) {
							if (action.result.Error_Msg) {
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
							}
						}
					}
				});
				break;

			default:
				loadMask.hide();
				break;

		}
	},
	setFieldsDisabled: function (d) {
		var form = this;
		this.FormPanel.items.each(function (f) {
			if (f && (f.xtype != 'hidden') && (f.xtype != 'fieldset') && (f.changeDisabled !== false)) {
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	incCheckStore: function() {
		this.storeActive += 1;
		this.checkStoreLoaded();
	},
	show: function () {

		sw.Promed.swMorbusNephroDrugWindow.superclass.show.apply(this, arguments);

		var that = this;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function () {
					that.hide();
				}
			});
		}
		this.focus();
		this.center();

		var base_form = this.FormPanel.getForm();

		base_form.reset();
		this.formStatus = 'edit';
		this.action = arguments[0].action || null;
		this.MorbusNephroDrug_id = arguments[0].MorbusNephroDrug_id || null;
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;

		if (!this.action) {
			if ((this.MorbusNephroDrug_id) && (this.MorbusNephroDrug_id > 0))
				this.action = "edit";
			else
				this.action = "add";
		}

		base_form.setValues(arguments[0].formParams);
		that.el.mask('Подождите ...');
		Ext.getCmp('EvnVK_FieldSet').setVisible(false);

		switch (this.action) {
			case 'add':
				this.setTitle(this.winTitle + langs(': Добавление'));
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle + langs(': Редактирование'));
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle + langs(': Просмотр'));
				this.setFieldsDisabled(true);
				break;
		}

		this.DispStore.load({
			params: {
				MorbusNephro_id: base_form.findField('MorbusNephro_id').value
			},
			callback: function () { that.incCheckStore(); }
		});

		this.ProtocolStore.load({
			params: {
				Person_id: arguments[0].formParams.Person_id
			},
			callback: function () { that.incCheckStore(); }
		});

		this.SchemeStore.load({
			callback: function () { that.incCheckStore(); }
		});

		this.SchemeRuleStore.load({
			callback: function () { that.incCheckStore(); }
		});

		this.UsedSchemeStore.load({
			params: {
				MorbusNephro_id: base_form.findField('MorbusNephro_id').value
			},
			callback: function () { that.incCheckStore(); }
		});

		this.MnnStore.load({
			callback: function () { that.incCheckStore(); }
		});

		this.ParentStore.load({
			callback: function () { that.incCheckStore(); }
		});

		this.NoeffectStore.load({
			callback: function () { that.incCheckStore(); }
		});
		
		if (this.action != 'add') {
			Ext.Ajax.request({
				failure: function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					that.getLoadMask().hide();
				},
				params: {
					MorbusNephroDrug_id: that.MorbusNephroDrug_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					
					base_form.findField('AdditionalCondition_id').setContainerVisible(false);
					if (result[0].EvnVK_Description != null) {
						Ext.getCmp('EvnVK_FieldSet').setVisible(true);
						base_form.findField('EvnVK_id').setValue(result[0].EvnVK_Description);
					}
				},
				url: '/?c=MorbusNephro&m=doLoadEditFormMorbusNephroDrug'
			});
		} else {
			//this.getLoadMask().hide();
		}
		base_form.findField('AdditionalCondition_id').setContainerVisible(true);
	},
	initComponent: function () {
		var me = this;

		this.DispStore = new Ext.data.Store({
			id: 'DispStore',
			extend: 'Ext.data.Store',
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadDispList',
			reader: new Ext.data.JsonReader({
				id: 'rateTypeId'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'begDT', name: 'begDT', type: 'string' },
					{ mapping: 'endDT', name: 'endDT', type: 'string' },
					{ mapping: 'value', name: 'value', type: 'float' },
					{ mapping: 'rateTypeId', name: 'rateTypeId', type: 'float' },
					{ mapping: 'rateUnitTypeId', name: 'rateUnitTypeId', type: 'int' }
				])
		});

		this.SchemeStore = new Ext.data.Store({
			id: 'SchemeStore',
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadSchemeList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'schemeGroup', name: 'schemeGroup', type: 'int' },
					{ mapping: 'schemeSubGroup', name: 'schemeSubGroup', type: 'int' },
					{ mapping: 'schemeName', name: 'schemeName', type: 'string' },
					{ mapping: 'schemeDescription', name: 'schemeDescription', type: 'string' },
					{ mapping: 'dose', name: 'dose', type: 'string' },
					{ mapping: 'multi', name: 'multi', type: 'string' },
					{ mapping: 'sumDose', name: 'sumDose', type: 'string' },
					{ mapping: 'unitTypeId', name: 'unitTypeId', type: 'int' },
					{ mapping: 'isResistance', name: 'isResistance', type: 'int' },
					{ mapping: 'isNoControl', name: 'isNoControl', type: 'int' },
					{ mapping: 'isSideEffect', name: 'isSideEffect', type: 'int' },
					{ mapping: 'noEffectLC', name: 'noEffectLC', type: 'int' },
					{ mapping: 'resistanceLC', name: 'resistanceLC', type: 'int' },
					{ mapping: 'noTabletLC', name: 'noTabletLC', type: 'int' },
					{ mapping: 'sideEffectLC', name: 'sideEffectLC', type: 'int' }
				])
		});

		this.SchemeRuleStore = new Ext.data.Store({
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadSchemeRuleList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'minValue', name: 'minValue', type: 'float' },
					{ mapping: 'maxValue', name: 'maxValue', type: 'float' },
					{ mapping: 'schemeId', name: 'schemeId', type: 'int' },
					{ mapping: 'rateUnitTypeId', name: 'rateUnitTypeId', type: 'int' },
					{ mapping: 'rateTypeId', name: 'rateTypeId', type: 'int' },
					{ mapping: 'isVK', name: 'isVK', type: 'int' }
				])
		});

		this.UsedSchemeStore = new Ext.data.Store({
			id: 'UsedSchemeStore',
			extend: 'Ext.data.Store',
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadUsedSchemeList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'schemeId', name: 'schemeId', type: 'int' },
					{ mapping: 'schemeName', name: 'schemeName', type: 'string' },
					{ mapping: 'begDT', name: 'begDT', type: 'string' },
					{ mapping: 'schemeGroup', name: 'schemeGroup', type: 'int' }
				])
		});

		this.MnnStore = new Ext.data.Store({
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadMnnList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'schemeId', name: 'schemeId', type: 'int' },
					{ mapping: 'drugId', name: 'drugId', type: 'int' },
					{ mapping: 'drugName', name: 'drugName', type: 'string' }
				])
		});

		this.ParentStore = new Ext.data.Store({
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadParentList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'schemeId', name: 'schemeId', type: 'int' },
					{ mapping: 'pid', name: 'pid', type: 'int' }
				])
		});

		this.NoeffectStore = new Ext.data.Store({
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadNoeffectList',
			reader: new Ext.data.JsonReader({
				id: 'EvnVK_id'
			}, [
					{ mapping: 'id', name: 'id', type: 'int' },
					{ mapping: 'schemeId', name: 'schemeId', type: 'int' },
					{ mapping: 'nid', name: 'nid', type: 'int' }
				])
		});

		this.ProtocolStore = new Ext.data.Store({
			autoLoad: false,
			url: '/?c=MorbusNephro&m=doLoadVKProtocolList',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
					{ mapping: 'EvnVK_id', name: 'EvnVK_id', type: 'int' },
					{ mapping: 'EvnVK_NumProtocol', name: 'EvnVK_NumProtocol', type: 'int' },
					{ mapping: 'EvnVK_Description', name: 'EvnVK_Description', type: 'string' },
					{ mapping: 'EvnVK_setDate', name: 'EvnVK_setDate', type: 'string' }
				])
		});

		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'north',
			labelWidth: 120,
			autoScroll: true,
			autoHeight: false,
			labelAlign: 'right',
			bodyStyle: 'padding: 5px',
			items: [{
				name: 'MorbusNephroDrug_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				minHeight: 130,
				valueField: 'id',
				allowBlank: false,
				displayField: 'name',
				xtype: 'swbaselocalcombo',
				name: 'AdditionalCondition_id',
				fieldLabel: langs('Дополнительное условие'),
				store: new Ext.data.SimpleStore({
					fields: [
						{ name: 'id', type: 'string' },
						{ name: 'name', type: 'string' }
					],
					data: [
						['empty', 'Не выбрано'],
						['noEffect', 'Отсутствие эффекта от предыдущей терапии'],
						['resistance', 'Резистентность к терапии'],
						['noControl', 'Отсутствие контроля приёма в таблетированной форме'],
						['sideEffect', 'Побочные эффекты от предыдущей терапии'],
						['protocolVK', 'Составлен протокол ВК']
					]
				}),
				listeners: {
					render: function () {
						this.setValue('empty');
					},
					change: function (field, value) {
						var base_form = me.FormPanel.getForm();
						base_form.findField('NephroDrugScheme_id').setValue("");
						me.setUnavailable('MorbusNephroDrug_Dose');
						me.setUnavailable('Unit_id');
						me.setUnavailable('MorbusNephroDrug_Multi');
						me.setUnavailable('MorbusNephroDrug_SumDose');
						me.setUnavailable('DrugComplexMnn_id');
						me.setUnavailable('MorbusNephroDrug_begDT');
						me.setUnavailable('MorbusNephroDrug_endDT');
						me.setUnavailable('SchemeDescription');

						if (value == 'noEffect') {
							me.setUnavailable('NephroDrugScheme_id');
							me.FormPanel.getForm().findField('NoEffectFromSchemeId').setContainerVisible(true);
							
							Ext.getCmp('EvnVK_FieldSet').setVisible(false);
							me.setUnavailable('EvnVK_id');
							Ext.getCmp('EvnVK_viewbtn').setDisabled(true);
							me.setAvailable('NoEffectFromSchemeId');
							return;
						}
						if (value == 'protocolVK') {
							me.setUnavailable('NephroDrugScheme_id');
							me.FormPanel.getForm().findField('EvnVK_id').setDisabled(false);
							Ext.getCmp('EvnVK_FieldSet').setVisible(true);
							base_form.findField('NoEffectFromSchemeId').setContainerVisible(false);
							return;
						}
						me.setUnavailable('NoEffectFromSchemeId');
						base_form.findField('NoEffectFromSchemeId').setContainerVisible(false);
						
						me.setUnavailable('EvnVK_id');
						Ext.getCmp('EvnVK_viewbtn').setDisabled(true);
						Ext.getCmp('EvnVK_FieldSet').setVisible(false);
						
						
						me.getAllowedScheme();
					}
				}
			}, {
				anchor: '96%',
				valueField: 'schemeId',
				allowBlank: true,
				displayField: 'schemeName',
				xtype: 'swbaselocalcombo',
				fieldLabel: langs('Нет эффекта от схемы'),
				hiddenName: 'NoEffectFromSchemeId',
				listWidth: '270',
				store: this.UsedSchemeStore,
				listeners: {
					render: function () {
						this.setContainerVisible(false);
					},
					change: function(field, value) {
						if (value == "") return;
						me.getAllowedScheme();
					}
				}
			},
			{
				autoHeight: true,
				id: 'EvnVK_FieldSet',
				title: 'Протокол ВК',
				xtype: 'fieldset',
				layout: 'column',
				labelWidth: 180,
				hidden: false,
				items:
				[{
					xtype: 'swbaselocalcombo',
					tabIndex: 1110,
					listWidth: '100%',
					width: 260,
					name: 'EvnVK_id',
					hiddenName: 'EvnVK_id',
					valueField: 'EvnVK_id',
					displayField: 'EvnVK_Description',
					allowBlank: true,
					store: this.ProtocolStore,
						listeners: {
							change: function(field, value) {
								if (value == "") {
									Ext.getCmp('EvnVK_viewbtn').setDisabled(true);
									return;
								}
								me.getAllowedScheme();
								Ext.getCmp('EvnVK_viewbtn').setDisabled(false);
							}
						}
				},{
					xtype: 'button',
					style: 'margin-left: 5px;',
					text: 'Просмотр протокола',
					id: 'EvnVK_viewbtn',
					disabled: true,
					handler: function () {
						var EvnVK_id = me.FormPanel.getForm().findField('EvnVK_id').getValue();
						if (EvnVK_id == '') return;
						getWnd('swClinExWorkEditWindow').show({
							EvnPrescrVK_id: null,
							showtype: "view",
							EvnVK_id: EvnVK_id
						});
					}
				}]
			}, {
				anchor: '100%',
				valueField: 'id',
				allowBlank: false,
				disabled: true,
				displayField: 'schemeName',
				xtype: 'swbaselocalcombo',
				fieldLabel: langs('Схема'),
				name: 'NephroDrugScheme_id',
				hiddenName: 'NephroDrugScheme_id',
				store: this.SchemeStore,
				listeners: {
					change: function (field, value) {
						if (value == "") return;

						var base_form = me.FormPanel.getForm();
						var comboId = me.SchemeStore.find('id', value);
						var scheme = me.SchemeStore.data.items[comboId].data;

						me.setAvailable('MorbusNephroDrug_Dose', scheme.dose);
						me.setAvailable('MorbusNephroDrug_Multi', scheme.multi);
						me.setAvailable('MorbusNephroDrug_SumDose', scheme.sumDose);
						me.setAvailable('Unit_id', scheme.unitTypeId);
						me.setAvailable('DrugComplexMnn_id', '');
						me.setAvailable('MorbusNephroDrug_begDT', '');
						me.setAvailable('MorbusNephroDrug_endDT', '');
						me.setAvailable('SchemeDescription', scheme.schemeDescription);

						base_form.findField('DrugComplexMnn_id').getStore().clearFilter();
						base_form.findField('DrugComplexMnn_id').lastQuery = '';
						base_form.findField('DrugComplexMnn_id').getStore().filterBy(function (rec) {
							return (rec.get('schemeId') == value);
						});
					}
				}
			}, {
				disabled: true,
				anchor: '100%',
				xtype: 'textarea',
				name: 'SchemeDescription',
				fieldLabel: langs('Описание схемы'),
				listeners: {
					render: function() {
						this.setDisabled(true);
						this.setContainerVisible(false);
					}
				}
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: 'Параметры схемы лечения',
				items: [{
					anchor: '100%',
					disabled: true,
					valueField: 'drugId',
					allowBlank: false,
					displayField: 'drugName',
					xtype: 'swbaselocalcombo',
					fieldLabel: langs('Медикамент'),
					hiddenName: 'DrugComplexMnn_id',
					store: this.MnnStore
				}, {
					disabled: true,
					allowBlank: false,
					xtype: 'swdatefield',
					fieldLabel: langs('Дата начала'),
					name: 'MorbusNephroDrug_begDT',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					listeners: {
						change: function(field, value) {
							if(me.UsedSchemeStore.getTotalCount() !== 0 && !me.validateDate(value)) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										field.setValue("");
									},
									icon: Ext.Msg.WARNING,
									msg: "Дата начала лечения должна входить в диапазон срока годности анализов",
									title: "Некорректная дата начала лечения"
								});
							}

							var base_form = me.FormPanel.getForm();
							var endDT = base_form.findField('MorbusNephroDrug_endDT').getValue();
							if (endDT == "") return;
							if (endDT < value) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									fn: function() {
										field.setValue("");
									},
									msg: "Дата начала лечения должна должна быть меньше даты окончания лечения",
									title: "Некорректная дата начала лечения"
								});
							}
						}
					}
				}, {
					disabled: true,
					allowBlank: false,
					xtype: 'swdatefield',
					fieldLabel: langs('Дата окончания'),
					name: 'MorbusNephroDrug_endDT',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					listeners: {
						change: function(field, value) {
							if(me.UsedSchemeStore.getTotalCount() !== 0 && !me.validateDate(value)) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									fn: function() {
										field.setValue("");
									},
									msg: "Дата окончания лечения должна должна входить в диапазон срока годности анализов ",
									title: "Некорректная дата окончания лечения"
								});
							}
							var base_form = me.FormPanel.getForm();
							var begDT = base_form.findField('MorbusNephroDrug_begDT').getValue();
							if (begDT == "") return;
							if (begDT > value) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									fn: function() {
										field.setValue("");
									},
									msg: "Дата окончания лечения должна должна быть больше даты начала лечения",
									title: "Некорректная дата окончания лечения"
								});
							}
						}
					}
				}, {
					maxLength: 10,
					anchor: '100%',
					disabled: true,
					allowBlank: false,
					xtype: 'textfield',
					name: 'MorbusNephroDrug_Dose',
					fieldLabel: langs('Разовая доза')
				}, {
					disabled: true,
					valueField: 'id',
					allowBlank: false,
					displayField: 'name',
					xtype: 'swbaselocalcombo',
					hiddenName: 'Unit_id',
					fieldLabel: langs('Единицы измерения'),
					store: new Ext.data.SimpleStore({
						fields: [
							{ name: 'id', type: 'int' },
							{ name: 'name', type: 'string' }
						],
						data: [
							[12, 'ЕД'],
							[13, 'мкг'],
							[14, 'мг'],
							[15, 'таб']
						]
					}),
					listeners: {
						change: function (field, value) {
						}
					}
				}, {
					maxLength: 30,
					anchor: '100%',
					disabled: true,
					allowBlank: false,
					xtype: 'textfield',
					name: 'MorbusNephroDrug_Multi',
					fieldLabel: langs('Кратность')
				}, {
					maxLength: 10,
					anchor: '100%',
					disabled: true,
					allowBlank: false,
					xtype: 'textfield',
					name: 'MorbusNephroDrug_SumDose',
					fieldLabel: langs('Суммарная доза')
				}
				]
			}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
					{ name: 'MorbusNephroDrug_id' },
					{ name: 'MorbusNephro_id' },
					{ name: 'Unit_id' },
					{ name: 'MorbusNephroDrug_begDT' },
					{ name: 'MorbusNephroDrug_endDT' },
					{ name: 'MorbusNephroDrug_Dose' },
					{ name: 'MorbusNephroDrug_Multi' },
					{ name: 'MorbusNephroDrug_SumDose' },
					{ name: 'DrugComplexMnn_id' },
					{ name: 'NephroDrugScheme_id' }
				]),
			url: '/?c=MorbusNephro&m=doSaveMorbusNephroDrug'
		});
		Ext.apply(this, {
			buttons: [{
				handler: function () { me.doSave(); },
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function () { me.hide(); },
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusNephroDrugWindow.superclass.initComponent.apply(this, arguments);
	},
	getAllowedScheme: function () {
		var win = this;
		win.el.mask('Подождите...');
		var base_form = this.FormPanel.getForm();

		var additionalCondition = base_form.findField('AdditionalCondition_id').getValue();

		var dispStore = this.DispStore;
		var schemeStore = this.SchemeStore;
		var ruleStore = this.SchemeRuleStore;

		var usedSchemeList = [];
		this.UsedSchemeStore.each(function (items) {
			usedSchemeList.push(items.data.schemeId);
		});

		var skipedGroupList = win.getSkipedGroup();

		var allowedScheme = [];

		schemeStore.clearFilter();
		var schemeList = schemeStore.data.items;

		dispStore.clearFilter();
		if (dispStore.getCount() === 0 && this.UsedSchemeStore.getCount() > 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.INFO,
				msg: 'Невозможно получить список схем из-за отсутствия необходимых анализов',
				title: 'Внимание'
			});
			win.hide();
			return;
		}

		for (var i = 0; i < schemeList.length; i++) {
			var scheme = schemeList[i].data;
			if (skipedGroupList.indexOf(scheme.schemeGroup) != -1) continue;

			ruleStore.clearFilter();
			ruleStore.filterBy(function (rec) {
				var flag = rec.get('schemeId') == scheme.id;
				if (additionalCondition == 'protocolVK') flag &= rec.get('isVK') == 2;
				else flag &= rec.get('isVK') != 2;
				return flag;
			});
			if (ruleStore.getCount() === 0) {
				ruleStore.filter('schemeId', new RegExp('^' + scheme.id + '$'));
			}
			var ruleList = ruleStore.data.items;
			var dispCount = 0;

			var mainFlag = false;
			for (var j = 0; j < ruleList.length; j++) {
				var rule = ruleList[j].data;

				dispStore.clearFilter();
				dispStore.filter('rateTypeId', new RegExp('^' + rule.rateTypeId +'$'));
				var dispList = dispStore.data.items;

				if (dispList.length == 0) {
					if (scheme.schemeGroup != 2) mainFlag &= false;
					continue;
				} 
				var disp = dispList[0].data;
				dispCount++;

				var tempFlag = (disp.rateUnitTypeId == rule.rateUnitTypeId)
					&& (disp.value >= rule.minValue && disp.value < rule.maxValue);

				if (j == 0 || scheme.schemeGroup == 2) mainFlag |= tempFlag;
				else mainFlag &= tempFlag;
			}
			
			mainFlag = win.checkAdditionalCondition(mainFlag, scheme, usedSchemeList);
			if (ruleList.length != dispCount && scheme.schemeGroup != 2) mainFlag = false;

			if (mainFlag) allowedScheme.push({
				name: schemeList[i].data.schemeName,
				id: schemeList[i].data.id
			});
		}

		base_form.findField('NephroDrugScheme_id').getStore().clearFilter();
		base_form.findField('NephroDrugScheme_id').lastQuery = '';

		if (this.UsedSchemeStore.data.items.length == 0) {
			for (var i = 0; i < schemeList.length; i++) {
				allowedScheme.push({
					name: schemeList[i].data.schemeName,
					id: schemeList[i].data.id
				});
			}
		}

		var allowedSchemeIds = [], allowedSchemeNames = [];
		for (var i = 0; i < allowedScheme.length; i++) {
			if (allowedSchemeNames.indexOf(allowedScheme[i].name) != -1) continue;
			allowedSchemeIds.push(allowedScheme[i].id);
			allowedSchemeNames.push(allowedScheme[i].name);
		}

		base_form.findField('NephroDrugScheme_id').getStore().filterBy(function (rec) {
			return (rec.get('id').inlist(allowedSchemeIds));
		});

		base_form.findField('NephroDrugScheme_id').setDisabled(false);
		win.el.unmask();
	},
	checkAdditionalCondition(mainFlag, scheme, usedSchemeList) {
		const AND = 2, OR = 1, YES = 2, NO = 1;
		var base_form = this.FormPanel.getForm();
		var additionalCondition = base_form.findField('AdditionalCondition_id').getValue();

		this.ParentStore.filter('schemeId', new RegExp('^' + scheme.id + '$'));
		this.NoeffectStore.filter('schemeId', new RegExp('^' + scheme.id + '$'));
		var parents = this.ParentStore.data.items;

		var addCondFlag = false;
		switch (additionalCondition) {
			case 'resistance': addCondFlag = scheme.isResistance == 2; break;
			case 'sideEffect': addCondFlag = scheme.isSideEffect == 2; break;
			case 'noControl': addCondFlag = scheme.isNoControl == 2; break;
		}

		if (scheme.schemeGroup == 3 && scheme.schemeSubGroup == 7 && additionalCondition == 'empty' && !usedSchemeList.includes(scheme.id)) mainFlag = false;
		if (scheme.schemeGroup == 4 && [2, 3, 4].includes(scheme.schemeSubGroup)) {
			if (['empty', 'protocolVK', 'noControl', 'resistance'].includes(additionalCondition) && !usedSchemeList.includes(scheme.id)) mainFlag = false;
			//if (['noEffect'].includes(additionalCondition) && usedSchemeList.includes(scheme.id))
		}

		if (!addCondFlag && parents.length != 0) {
			var tempFlag = false;
			for (var i = 0; i < parents.length; i++) {
				tempFlag |= usedSchemeList.includes(parents[i].data.pid);
			}
			mainFlag &= tempFlag;
		}

		switch (additionalCondition) {
			case 'noEffect':
				var selectedNid = base_form.findField('NoEffectFromSchemeId').getValue();
				var param = false;
				this.NoeffectStore.each(function(items) {
					param |= selectedNid == items.get('nid');
				});

				if (scheme.noEffectLC == OR) mainFlag |= param;
				if (scheme.noEffectLC == AND) mainFlag &= param;
				break;
			case 'resistance':
				var param = scheme.isResistance == YES;
				if (scheme.resistanceLC == OR) mainFlag |= param;
				if (scheme.resistanceLC == AND) mainFlag &= param;
				break;
			case 'noControl':
				var param = scheme.isNoControl == YES;
				if (scheme.noTabletLC == OR) mainFlag |= param;
				if (scheme.noTabletLC == AND) mainFlag &= param;
				break;
			case 'sideEffect':
				var param = scheme.isSideEffect == YES;
				if (scheme.sideEffectLC == OR) mainFlag |= param;
				if (scheme.sideEffectLC == AND) mainFlag &= param;
				break;
		}

		return mainFlag;
	},
	setAvailable(fieldName, value) {
		this.FormPanel.getForm().findField(fieldName).setContainerVisible(true);
		this.FormPanel.getForm().findField(fieldName).setDisabled(false);
		if (fieldName == 'SchemeDescription') this.FormPanel.getForm().findField(fieldName).setDisabled(true);
		this.FormPanel.getForm().findField(fieldName).setValue(value);
	},
	setUnavailable(fieldName) {
		this.FormPanel.getForm().findField(fieldName).setDisabled(true);
		if (fieldName == 'SchemeDescription') this.FormPanel.getForm().findField(fieldName).setContainerVisible(false);
		this.FormPanel.getForm().findField(fieldName).setValue("");
	},
	validateDate(value) {
		var me = this;
		var ruleStore = me.SchemeRuleStore;
		var base_form = me.FormPanel.getForm();
		ruleStore.clearFilter();
		ruleStore.filter('schemeId', new RegExp('^' + base_form.findField('NephroDrugScheme_id').value + '$'));

		var rateTypeIds = [];
		ruleStore.each(function(rule) {
			rateTypeIds.push(rule.data.rateTypeId);
		});

		var dispStore = me.DispStore;
		dispStore.filterBy(function(rec) {
			return rec.get('rateTypeId').inlist(rateTypeIds);
		});
		
		var flag = true;
		var dispList = dispStore.data.items;
		for (var i = 0; i < dispList.length; i++) {
			var begDT = dispList[i].data.begDT.split(".");
			var begYear = begDT[0].length == 2 ? "20" + begDT[0] : begDT[0];
			begDT = new Date(begYear, begDT[1] - 1, begDT[2]);

			var endDT = dispList[i].data.endDT.split(".");
			var endYear = endDT[0].length == 2 ? "20" + endDT[0] : endDT[0];
			endDT = new Date(endYear, endDT[1] - 1, endDT[2]);

			flag &= value >= begDT && value <= endDT;
		}
		return flag;
	},

	getSkipedGroup: function() {
		var skipedList = [], today = new Date();
		this.UsedSchemeStore.each(function (item) {
			var group = item.get('schemeGroup');
			if (skipedList.indexOf(group) != -1) return;
			var diff = Math.abs(new Date(item.get('begDT')) - today) / (1000 * 60 * 60 * 24);
			if (diff < 30) skipedList.push(group)
		});
		return skipedList;
	}
});