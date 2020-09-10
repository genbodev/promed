/**
 * swDrugListSprWindow - окно справочника «Перечни медикаментов»
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.10.2017
 */
/*NO PARSE JSON*/

sw.Promed.swDrugListSprWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListSprWindow',
	layout: 'border',
	title: 'Справочник «Перечни медикаментов»',
	maximizable: false,
	maximized: true,

	doSearch: function() {
		base_form = this.FilterPanel.getForm();

		this.DrugListGridPanel.removeAll();

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		this.DrugListGridPanel.loadData({params: params, globalFilters: params});
	},

	doReset: function() {
		base_form = this.FilterPanel.getForm();

		var date = new Date().format('d.m.Y');

		base_form.reset();
		base_form.findField('DrugListRange').setValue(date+' - '+date);
		base_form.findField('DrugListObj_id').getStore().baseParams.isPublisher = 1;

		this.refreshFieldsVisibility();

		this.DrugListGridPanel.removeAll();
		this.DrugListUsedGridPanel.removeAll();

		this.DrugListGridPanel.getAction('action_list_str').disable();
		this.DrugListUsedGridPanel.getAction('action_add').disable();
		this.DrugListUsedGridPanel.getAction('action_refresh').disable();
	},

	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.FilterPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		base_form.items.each(function(field) {
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var OrgType_SysNick = base_form.findField('Org_id').getFieldValue('OrgType_SysNick');
			var LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();

			switch(field.getName()) {
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
	},

	checkDrugListAccess: function(record) {
		var go = getGlobalOptions();
		var ARMType = String(this.ARMType);
		return (
			this.ARMType == 'superadmin' ||
			(ARMType.inlist(['lpuadmin','orgadmin']) && (record.get('Lpu_id') == go.lpu_id || record.get('Org_id') == go.org_id)) ||
			(ARMType == 'spec_mz' && Ext.isEmpty(record.get('DrugListObj_id')))
		);
	},

	checkDrugListUsedAccess: function(record) {
		var go = getGlobalOptions();
		var ARMType = String(this.ARMType);
		return (
			this.ARMType == 'superadmin' ||
			(ARMType.inlist(['lpuadmin','orgadmin']) && (record.get('Lpu_id') == go.lpu_id || record.get('Org_id') == go.org_id)) ||
			(ARMType == 'spec_mz' && Ext.isEmpty(record.get('Lpu_id')) && Ext.isEmpty(record.get('Org_id')))
		);
	},

	openDrugListEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}

		var grid_panel = this.DrugListGridPanel;
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;
		params.ARMType = this.ARMType;
		params.formParams = {};

		params.callback = function() {
			grid_panel.getAction('action_refresh').execute();
		};

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('DrugList_id'))) {
				return;
			}
			if (action == 'edit' && !this.checkDrugListAccess(record)) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'У Вас нет прав на редактирование перечня',
					title: 'Сообщение'
				});
				return;
			}
			params.formParams.DrugList_id = record.get('DrugList_id');
		}

		getWnd('swDrugListEditWindow').show(params);
	},

	openDrugListUsedEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}

		var grid_panel = this.DrugListGridPanel;
		var grid = grid_panel.getGrid();

		var used_grid_panel = this.DrugListUsedGridPanel;
		var used_grid = used_grid_panel.getGrid();

		var drug_list_record = grid.getSelectionModel().getSelected();
		if (!drug_list_record || Ext.isEmpty(drug_list_record.get('DrugList_id'))) {
			return;
		}

		var params = {};
		params.action = action;
		params.ARMType = this.ARMType;
		params.DrugListType_Code = drug_list_record.get('DrugListType_Code');
		params.formParams = {};

		params.callback = function() {
			used_grid_panel.getAction('action_refresh').execute();
		};

		if (action == 'add') {
			params.formParams.DrugList_id = drug_list_record.get('DrugList_id');
		} else {
			var record = used_grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('DrugListUsed_id'))) {
				return;
			}
			if (action == 'edit' && !this.checkDrugListUsedAccess(record)) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'У Вас нет прав на редактирование данных',
					title: 'Сообщение'
				});
				return;
			}
			params.formParams.DrugListUsed_id = record.get('DrugListUsed_id');
		}

		getWnd('swDrugListUsedEditWindow').show(params);
	},

	openDrugListStrViewWindow: function() {
		var grid = this.DrugListGridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('DrugList_id'))) {
			return;
		}

		getWnd('swDrugListStrViewWindow').show({
			DrugList_id: record.get('DrugList_id'),
			DrugListType_Code: record.get('DrugListType_Code'),
			ARMType: this.ARMType
		});
	},

	deleteDrugList: function() {
		var grid_panel = this.DrugListGridPanel;
		var grid = grid_panel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('DrugList_id'))) {
			return;
		}
		if (!this.checkDrugListAccess(record)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'У Вас нет прав на удаление перечня',
				title: 'Сообщение'
			});
			return;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {DrugList_id: record.get('DrugList_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=DrugList&m=deleteDrugList'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	deleteDrugListUsed: function() {
		var grid_panel = this.DrugListUsedGridPanel;
		var grid = grid_panel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('DrugListUsed_id'))) {
			return;
		}
		if (!this.checkDrugListUsedAccess(record)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'У Вас нет прав на удаление данных о применении перечня',
				title: 'Сообщение'
			});
			return;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {DrugListUsed_id: record.get('DrugListUsed_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=DrugList&m=deleteDrugListUsed'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swDrugListSprWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.DrugListGridPanel.addActions({
			name:'action_list_str',
			text: 'Медикаменты перечня',
			handler: function() {
				this.openDrugListStrViewWindow();
			}.createDelegate(this)
		});

		var cm = this.DrugListGridPanel.getGrid().getColumnModel();
		cm.setHidden(cm.findColumnIndex('Region'), this.ARMType != 'superadmin');

		this.FilterTabPanel.setActiveTab(1);
		this.FilterTabPanel.setActiveTab(0);

		if (isUserGroup('PM')) {
			this.readOnly = true;
			this.DrugListGridPanel.setActionDisabled('action_add', true);
			this.DrugListUsedGridPanel.setActionDisabled('action_add', true);
		} else {
			this.readOnly = false;
			this.DrugListGridPanel.setActionDisabled('action_add', false);
			this.DrugListUsedGridPanel.setActionDisabled('action_add', false);
		}

		this.doReset();
	},

	initComponent: function() {
		this.DrugListFilterPanel = new Ext.Panel({
			labelWidth: 120,
			layout: 'form',
			border: false,
			items: [
				{
					border: false,
					layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							items: [
								{
								xtype: 'daterangefield',
								name: 'DrugListRange',
								fieldLabel: 'Период',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 180
								}
							]
						}
					]
				},{
				border: false,
				layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							items: [
								{
									xtype: 'textfield',
									name: 'DrugList_Name',
									fieldLabel: 'Наименование',
									width: 400
								},
							]
						},
						{
							border: false,
							layout: 'form',
							items: [
								{
									xtype: 'swpaytypecombo',
									hiddenName: 'PayType_id',
									fieldLabel: 'Вид оплаты',
									width: 400
								}
							]
						}
					]
				},
				{
					border: false,
					layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							items: [
								{
									xtype: 'swcommonsprcombo',
									comboSubject: 'DrugListType',
									hiddenName: 'DrugListType_id',
									fieldLabel: 'Тип',
									width: 400
								},
							]
						},
						{
							border: false,
							layout: 'form',
							items: [
								{
									xtype: 'swdruglistobjcombo',
									hiddenName: 'DrugListObj_id',
									fieldLabel: 'Издатель',
									width: 400
								}
							]
						}
					]
				}
			]
		});

		this.DrugListUsedFilterPanel = new Ext.Panel({
			labelWidth: 120,
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
						var base_form = this.FilterPanel.getForm();

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

						this.refreshFieldsVisibility(['LpuBuilding_id','LpuSection_id']);
					}.createDelegate(this),
				},
				width: 400
			}, {
				xtype: 'swlpubuildingcombo',
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.refreshFieldsVisibility(['LpuSection_id']);
					}.createDelegate(this)
				},
				width: 400
			}, {
				xtype: 'swlpusectioncombo',
				hiddenName: 'LpuSection_id',
				fieldLabel: 'Отделение',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.refreshFieldsVisibility(['LpuBuilding_id']);
					}.createDelegate(this)
				},
				width: 400
			}, {
				xtype: 'textfield',
				name: 'DrugList_Profile',
				fieldLabel: 'Профиль',
				width: 400
			}, {
				xtype: 'swuslugacomplexnewcombo',
				hiddenName: 'UslugaComplex_id',
				fieldLabel: 'Услуга',
				width: 400
			}, {
				xtype: 'swstoragecombo',
				hiddenName: 'Storage_id',
				fieldLabel: 'Склад',
				width: 400
			}]
		});

		this.FilterTabPanel = new Ext.TabPanel({
			border: false,
			activeTab: 0,
			id: 'DLSW_FilterTabPanel',
			height: 190,
			bodyStyle: 'padding-top: 5px;',
			layoutOnTabChange: true,
			items: [{
				id: 'DrugList',
				title: 'Перечень',
				items: [this.DrugListFilterPanel]
			}, {
				id: 'DrugListUsed',
				title: 'Применение перечня',
				items: [this.DrugListUsedFilterPanel]
			}],
			keys: [{
				fn: function() {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			bodyStyle: 'padding-bottom: 5px;',
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout:'form',
					border: false,
					items: [{
						style: 'margin-left: 10px;',
						xtype: 'button',
						id: 'DLSW_BtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							this.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					border: false,
					items: [{
						style: 'margin-left: 10px;',
						xtype: 'button',
						id: 'DLSW_BtnReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							this.doReset();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FilterPanel = new Ext.FormPanel({
			frame: false,
			id: 'DLSW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			layout: 'form',
			items: [
				this.FilterTabPanel,
				this.FilterButtonsPanel
			]
		});

		this.DrugListGridPanel = new sw.Promed.ViewFrame({
			id: 'DLSW_DrugListGrid',
			title: 'Перечни медикаментов',
			dataUrl: '/?c=DrugList&m=loadDrugListGrid',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'DrugList_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugListType_id', type: 'int', hidden: true},
				{name: 'DrugListType_Code', type: 'int', hidden: true},
				{name: 'PayType_id', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'KLCountry_id', type: 'int', hidden: true},
				{name: 'Region_id', type: 'int', hidden: true},
				{name: 'DocNormative_id', type: 'int', hidden: true},
				{name: 'DrugListObj_id', type: 'int', hidden: true},
				{name: 'DrugList_Name', type: 'string', header: 'Наименование', id: 'autoexpand'},
				{name: 'DrugListType_Name', type: 'string', header: 'Тип', width: 220},
				{name: 'DrugList_begDate', type: 'date', header: 'Начало', width: 80},
				{name: 'DrugList_endDate', type: 'date', header: 'Окончание', width: 80},
				{name: 'PayType_Name', type: 'string', header: 'Вид оплаты', width: 140},
				{name: 'DocNormative_Num', type: 'string', header: 'Номер нормативного документа', width: 140},
				{name: 'DocNormative_Name', type: 'string', header: 'Наименование нормативного документа', width: 140},
				{name: 'Region', type: 'string', header: 'Регион', width: 140},
				{name: 'Publisher', type: 'string', header: 'Издатель', width: 200}
			],
			actions: [
				{name:'action_add', handler: function(){this.openDrugListEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openDrugListEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openDrugListEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteDrugList()}.createDelegate(this)}
			],
			onRowSelect: function(sm, index, record){
				this.DrugListUsedGridPanel.removeAll();

				if (!record || Ext.isEmpty(record.get('DrugList_id'))) {
					this.DrugListGridPanel.getAction('action_list_str').disable();

					this.DrugListUsedGridPanel.getAction('action_add').disable();
					this.DrugListUsedGridPanel.getAction('action_refresh').disable();
					return;
				}

				this.DrugListGridPanel.getAction('action_list_str').enable();

				if (!this.readOnly) {
					this.DrugListUsedGridPanel.getAction('action_add').enable();
				}
				this.DrugListUsedGridPanel.getAction('action_refresh').enable();

				var params = {
					DrugList_id: record.get('DrugList_id')
				};

				this.DrugListUsedGridPanel.loadData({params: params, globalFilters: params});
			}.createDelegate(this)
		});

		this.DrugListUsedGridPanel = new sw.Promed.ViewFrame({
			id: 'DLSW_DrugListUsedGrid',
			title: 'Применение перечня',
			dataUrl: '/?c=DrugList&m=loadDrugListUsedGrid',
			autoLoadData: false,
			paging: false,
			root: 'data',
			stringfields: [
				{name: 'DrugListUsed_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugList_id', type: 'int', hidden: true},
				{name: 'DrugListObj_id', type: 'int', hidden: true},
				{name: 'KLCountry_id', type: 'int', hidden: true},
				{name: 'Region_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'EmergencyTeamSpec_id', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
				{name: 'DrugListObj_Name', type: 'string', header: 'Наименование', width: 300},
				{name: 'DrugListObj_Nick', type: 'string', header: 'Объект, использующий перечень ', id: 'autoexpand'},
				{name: 'DrugListObj_Profile', type: 'string', header: 'Профиль ', width: 200},
				{name: 'Region', type: 'string', header: 'Регион, Страна', width: 260}
			],
			actions: [
				{name:'action_add', handler: function(){this.openDrugListUsedEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openDrugListUsedEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openDrugListUsedEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteDrugListUsed()}.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			buttons: [
				{
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
			items: [
				this.FilterPanel,
				{
					region: 'center',
					layout: 'border',
					border: false,
					items: [
						{
							region: 'center',
							layout: 'border',
							border: false,
							items: [this.DrugListGridPanel]
						}, {
							region: 'south',
							layout: 'border',
							height: 250,
							border: false,
							items: [this.DrugListUsedGridPanel]
						}
					]
				}
			]
		});

		sw.Promed.swDrugListSprWindow.superclass.initComponent.apply(this, arguments);
	}
});