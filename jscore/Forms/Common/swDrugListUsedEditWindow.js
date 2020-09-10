/**
 * swDrugListUsedEditWindow - окно редактирования применения перечня
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

sw.Promed.swDrugListUsedEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListUsedEditWindow',
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
			return;
		}

		var params = {};

		if (this.mode == 'select') {
			base_form.items.each(function(field) {
				var name = field.getName();
				if (name.inlist(['DrugListUsed_id','DrugListObj_id','DrugList_id'])) {
					params[name] = field.getValue();
				}
			});

			if (Ext.isEmpty(params.DrugListObj_id)) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('DrugList_Name').focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не выбран объект, использующий перечень',
					title: ERR_INVFIELDS_TIT
				});
				return;
			}
		} else {
			base_form.items.each(function(field) {
				var name = field.getName();
				if (!name.inlist(['DrugListObj_id'])) {
					params[name] = field.getValue();
				}
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=DrugList&m=saveDrugListUsed',
			params: params,
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.DrugListObj_id) {
					base_form.findField('DrugListObj_id').setValue(response_obj.DrugListObj_id);
					this.callback();
					this.hide();
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},

	setMode: function(mode) {
		var base_form = this.FormPanel.getForm();
		this.mode = mode;

		var drug_list_obj_combo = base_form.findField('DrugListObj_Name');

		switch(this.mode) {
			case 'select':
				this.DrugListObjFieldsPanel.hide();
				this.findById('DLUEW_ToggleModeBtn').setText('Добавить');

				base_form.findField('DrugListObj_Name').forceSelection = true;
				if (Ext.isEmpty(base_form.findField('DrugListObj_id').getValue())) {
					base_form.findField('DrugListObj_Name').setValue(null);
				}
				break;

			case 'edit':
				this.DrugListObjFieldsPanel.show();
				this.findById('DLUEW_ToggleModeBtn').setText('Отмена');
				drug_list_obj_combo.forceSelection = false;
				break;
		}
		this.syncShadow();
	},

	refreshFieldsVisibility: function(fieldNames) {
		//Измения здесь правила отображения полей, возможно также понядобится менять в swDrugListObjEditWindow
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
		sw.Promed.swDrugListUsedEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.mode = 'select';
		this.callback = Ext.emptyFn;
		this.DrugListType_Code = null;

		var base_form = this.FormPanel.getForm();

		base_form.reset();
		this.setMode(this.mode);

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments && arguments[0].DrugListType_Code) {
			this.DrugListType_Code = arguments[0].DrugListType_Code;
		}
		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Перечень медикаментов: Добавление');
				this.enableEdit(true);
				this.refreshFieldsVisibility();
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Применение перечня: Изменение');
					this.enableEdit(true);
				} else {
					this.setTitle('Применение перечня: Просмотр');
					this.enableEdit(false);
				}

				this.refreshFieldsVisibility();

				base_form.load({
					url: '/?c=DrugList&m=loadDrugListUsedForm',
					params: {DrugListUsed_id: base_form.findField('DrugListUsed_id').getValue()},
					success: function() {
						loadMask.hide();

						base_form.findField('DrugList_id').setValue(DrugList_id);

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
		this.DrugListObjSelectPanel = new Ext.Panel({
			layout: 'column',
			border: false,
			style: 'margin-top: 5px;',
			items: [{
				layout: 'form',
				border: false,
				items: [{
					allowBlank: false,
					forceSelection: false,
					xtype: 'swdruglistobjcombo',
					//hiddenName: 'DrugListObj_id',
					valueField: 'DrugListObj_Name',
					hiddenName: 'DrugListObj_Name',
					fieldLabel: 'Наименование',
					withoutTrigger: true,
					listeners: {
						'select': function(combo, record, index) {
							combo.lastQuery = null;
							var base_form = this.FormPanel.getForm();
							var id = (record && !Ext.isEmpty(record.get('DrugListObj_id'))) ? record.get('DrugListObj_id') : null;
							base_form.findField('DrugListObj_id').setValue(id);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var index = combo.getStore().findBy(function(rec) {
								return rec.get('DrugListObj_Name') == newValue;
							});
							if (Ext.isEmpty(newValue) || index < 0) {
								base_form.findField('DrugListObj_id').setValue(null);
							}
						}.createDelegate(this)
					},
					width: 300,
					listWidth: 400
				}]
			}, {
				layout: 'form',
				border: false,
				items: [{
					id: 'DLUEW_ToggleModeBtn',
					style: 'margin-left: 10px;',
					xtype: 'button',
					text: 'Добавить',
					minWidth: 70,
					handler: function () {
						this.setMode(this.mode=='edit'?'select':'edit');
					}.createDelegate(this)
				}]
			}]
		});

		this.DrugListObjFieldsPanel = new Ext.Panel({
			layout: 'form',
			border: false,
			items: [{
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
			}]
		});

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'DLOEW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 110,
			url: '/?c=DrugList&m=saveDrugListUsed',
			items: [
				{
					xtype: 'hidden',
					name: 'DrugListUsed_id'
				}, {
					xtype: 'hidden',
					name: 'DrugListObj_id'
				}, {
					xtype: 'hidden',
					name: 'DrugList_id'
				}, {
					xtype: 'hidden',
					name: 'Region_id'
				},
				this.DrugListObjSelectPanel,
				this.DrugListObjFieldsPanel
			],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'DrugListUsed_id'},
				{name: 'DrugListObj_id'},
				{name: 'DrugList_id'},
				{name: 'DrugListObj_Name'},
				{name: 'Org_id'},
				{name: 'LpuBuilding_id'},
				{name: 'LpuSection_id'},
				{name: 'EmergencyTeamSpec_id'},
				{name: 'UslugaComplex_id'},
				{name: 'UslugaComplexTariff_id'},
				{name: 'Region_id'}
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

		sw.Promed.swDrugListUsedEditWindow.superclass.initComponent.apply(this, arguments);
	}
});