/**
 * swDrugListObjEditWindow - окно редактирования объектов, использующих перечни медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.10.2017
 */
/*NO PARSE JSON*/

sw.Promed.swDrugListObjEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListObjEditWindow',
	layout: 'form',
	modal: true,
	autoHeight: true,
	width: 560,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				base_form.findField('DrugListObj_id').setValue(action.result.DrugListObj_id);

				this.callback(action.result.DrugListObj_id);
				this.hide();
			}.createDelegate(this)
		});
	},

	refreshFieldsVisibility: function(fieldNames) {
		//Измения здесь правила отображения полей, возможно также понядобится менять в swDrugListUsedEditWindow
		var win = this;
		var base_form = win.FormPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var DrugListType_Code = win.DrugListType_Code;

		base_form.items.each(function(field) {
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var Org_id = base_form.findField('Org_id').getValue();
			var OrgType_SysNick = base_form.findField('Org_id').getFieldValue('OrgType_SysNick');
			var LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
			var LpuSection_id = base_form.findField('LpuSection_id').getValue();

			switch(field.getName()) {
				case 'Org_id':
					//allowBlank = !visible;
					if (Ext.isEmpty(value)) {
						value = getGlobalOptions().org_id;
						field.getStore().load({
							params: {Org_id: value},
							callback: function(){
								field.setValue(value);
								field.fireEvent('change', field, value);
							}
						});
					}
					break;
				case 'LpuBuilding_id':
					visible = (OrgType_SysNick == 'lpu');
					if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
						value = base_form.findField('LpuSection_id').getFieldValue('LpuBuilding_id');
					}
					break;
				case 'LpuSection_id':
					visible = (OrgType_SysNick == 'lpu');

					if (Ext.isEmpty(LpuBuilding_id)) {
						filter = function(){return true};
					} else {
						if (field.getFieldValue('LpuBuilding_id') != LpuBuilding_id) {
							value = null;
						}
						filter = function(rec) {
							return rec.get('LpuBuilding_id') == LpuBuilding_id;
						};
					}
					break;
				case 'EmergencyTeamSpec_id':
					visible = (DrugListType_Code == 3);
					break;
				case 'UslugaComplex_id':
					visible = (DrugListType_Code == 2);
					break;
				case 'UslugaComplexTariff_id':
					visible = (DrugListType_Code == 2);
					break;
				case 'Storage_id':
					visible = (DrugListType_Code == 4);
					if (visible) {
						var prevParams = Ext.apply({}, field.getStore().baseParams);

						field.getStore().baseParams.Org_id = Org_id;
						field.getStore().baseParams.LpuBuilding_id = LpuBuilding_id;
						field.getStore().baseParams.LpuSection_id = LpuSection_id;

						if (prevParams.Org_id != Org_id || prevParams.LpuBuilding_id != LpuBuilding_id || prevParams.LpuSection_id != LpuSection_id) {
							field.lastQuery = null;
							field.getStore().load({
								callback: function() {
									var record = field.getStore().getById(value);
									if (record && !Ext.isEmpty(record.get('Storage_id'))) {
										field.setValue(value);
									} else {
										field.setValue(null);
									}
								}
							});
						}
					}
					break;
			}

			if (visible === false && win.formLoaded) {
				value = null;
			}
			if (value != field.getValue()) {
				field.setValue(value);
				field.fireEvent('change', field, value);
			}
			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);
			}
			if (enable !== null) {
				field.setDisabled(!enable || action == 'view');
			}
			if (typeof filter == 'function' && field.store) {
				field.lastQuery = '';
				if (typeof field.setBaseFilter == 'function') {
					field.setBaseFilter(filter);
				} else {
					field.store.filterBy(filter);
				}
			}
		});

		this.syncShadow();
	},

	show: function() {
		sw.Promed.swDrugListObjEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;
		this.DrugListType_Code = null;

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments && arguments[0].DrugListType_Code) {
			this.DrugListType_Code = arguments[0].DrugListType_Code;
		}
		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}


		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Объект, использующий перечни медикаментов: Добавление');
				this.enableEdit(true);
				this.refreshFieldsVisibility();
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Объект, использующий перечни медикаментов: Изменение');
					this.enableEdit(true);
				} else {
					this.setTitle('Объект, использующий перечни медикаментов: Просмотр');
					this.enableEdit(false);
				}

				this.refreshFieldsVisibility();

				base_form.load({
					url: '/?c=DrugList&m=loadDrugListObjForm',
					params: {
						DrugListObj_id: base_form.findField('DrugListObj_id').getValue()
					},
					success: function() {
						loadMask.hide();

						this.refreshFieldsVisibility();
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'DLOEW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 110,
			url: '/?c=DrugList&m=saveDrugListObj',
			items: [{
				xtype: 'hidden',
				name: 'DrugListObj_id'
			}, {
				xtype: 'hidden',
				name: 'Region_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DrugListObj_Name',
				fieldLabel: 'Наименование',
				width: 380
			}, {
				xtype: 'sworgcomboex',
				editable: true,
				hiddenName: 'Org_id',
				displayField: 'Org_Nick',
				fieldLabel: 'Организация',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						base_form.findField('LpuBuilding_id').setValue(null);
						base_form.findField('LpuBuilding_id').getStore().removeAll();

						base_form.findField('LpuSection_id').setValue(null);
						base_form.findField('LpuSection_id').getStore().removeAll();

						if (combo.getFieldValue('OrgType_SysNick') == 'lpu' && !Ext.isEmpty(combo.getFieldValue('Lpu_id'))) {
							base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = combo.getFieldValue('Lpu_id');
							base_form.findField('LpuBuilding_id').getStore().load();

							base_form.findField('LpuSection_id').getStore().baseParams.Lpu_id = combo.getFieldValue('Lpu_id');
							base_form.findField('LpuSection_id').getStore().load();
						}

						this.refreshFieldsVisibility(['LpuBuilding_id','LpuSection_id','Storage_id']);
					}.createDelegate(this)
				},
				width: 380
			}, {
				xtype: 'swlpubuildingcombo',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.refreshFieldsVisibility(['LpuSection_id','Storage_id']);
					}.createDelegate(this)
				},
				width: 380
			}, {
				xtype: 'swlpusectioncombo',
				hiddenName: 'LpuSection_id',
				fieldLabel: 'Отделение',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.refreshFieldsVisibility(['LpuBuilding_id','Storage_id']);
					}.createDelegate(this)
				},
				width: 380
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'EmergencyTeamSpec',
				hiddenName: 'EmergencyTeamSpec_id',
				fieldLabel: 'Профиль бригады',
				width: 380
			}, {
				xtype: 'swuslugacomplexnewcombo',
				hiddenName: 'UslugaComplex_id',
				fieldLabel: 'Услуга',
				width: 380
			}, {
				xtype: 'swuslugacomplextariffcombo',
				hiddenName: 'UslugaComplexTariff_id',
				fieldLabel: 'Тариф',
				width: 380
			}, {
				xtype: 'swstoragecombo',
				hiddenName: 'Storage_id',
				fieldLabel: 'Склад',
				width: 380
			}],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'DrugListObj_id'}
			])
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DLOEW_SaveButton',
					text: BTN_FRMSAVE
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swDrugListObjEditWindow.superclass.initComponent.apply(this, arguments);
	}
});