/**
 * swTariffVolumesViewWindow - Тарифы и объемы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			25.01.2015
 */

/*NO PARSE JSON*/

sw.Promed.swTariffVolumesViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swTariffVolumesViewWindow',
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: lang['tarifyi_i_obyemyi'],
	callback: Ext.emptyFn,
	isView: false,
	recountGridHeight: function() {
		var win = this;

		if (win.TabPanel.getActiveTab().id == 'tab_tariffs') {
			/*var tabPanelHeight = win.TabPanel.getActiveTab().getEl().getHeight();
			var panelsHeight = win.TariffClassGridFilters.getEl().getHeight() + win.TariffClassGrid.getEl().getHeight() + win.TariffClassAttributeValueGridFilters.getEl().getHeight();

			if (tabPanelHeight - panelsHeight > 200) {
				win.TariffClassAttributeValueGrid.setHeight(tabPanelHeight - panelsHeight - 4);
			} else {
				win.TariffClassAttributeValueGrid.setHeight(200);
			}*/
		} else if (win.TabPanel.getActiveTab().id == 'tab_volumes') {
			var tabPanelHeight = win.TabPanel.getActiveTab().getEl().getHeight();
			var panelsHeight = win.VolumeTypeGridFilters.getEl().getHeight() + win.VolumeTypeGrid.getEl().getHeight() + win.VolumeTypeAttributeValueGridFilters.getEl().getHeight();

			if (tabPanelHeight - panelsHeight > 200) {
				win.VolumeTypeAttributeValueGrid.setHeight(tabPanelHeight - panelsHeight - 4);
			} else {
				win.VolumeTypeAttributeValueGrid.setHeight(200);
			}
		}
	},
	show: function() {
		sw.Promed.swTariffVolumesViewWindow.superclass.show.apply(this, arguments);

		//false - чтобы иметь права добавлять, редактировать, удалять
		this.readAdmin = false;
		if( !isSuperAdmin() && arguments[0] && arguments[0].readOnly ){
			this.TariffClassAttributeValueGrid.readOnly = true;
			this.VolumeTypeAttributeValueGrid.readOnly = true;
			this.readAdmin = true;
		}
		
		this.isView = (arguments[0] && arguments[0].action == 'view') ? true : false;
		
		this.TabPanel.setActiveTab(2);
		this.TabPanel.setActiveTab(1);
		this.TabPanel.setActiveTab(0);

		this.doResetFiltersUslugaComplexTariffGrid();
		this.doResetFiltersVolumeTypeAttributeValueGrid();
		this.doResetFiltersVolumeTypeGrid();
		this.doResetFiltersTariffClassAttributeValueGrid();
		this.doResetFiltersTariffClassGrid();

		if (!isSuperAdmin() && getGlobalOptions().lpu_id) {
			this.UslugaComplexTariffGridFilters.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			this.UslugaComplexTariffGridFilters.getForm().findField('Lpu_id').disable();
		}

		// сворачиваем панель динамических фильтров
		// this.TariffClassAttributeValueGridFilters.items.items[0].collapse();
		this.VolumeTypeAttributeValueGridFilters.items.items[0].collapse();
	},
	setViewMode: function(mode){
		var actionArr = ['action_add','action_edit','action_delete'];
		for(var i = 0; i < actionArr.length; i++ ) {
			this.TariffClassAttributeValueGrid.setActionDisabled(actionArr[i], mode);
			this.VolumeTypeAttributeValueGrid.setActionDisabled(actionArr[i], mode);
			this.UslugaComplexTariffGrid.setActionDisabled(actionArr[i], mode);
		}
	},
	addCloseFilterMenu: function(gridCmp){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_' + grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_vse']);
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['otkryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_otkryityie']);
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: lang['zakryityie'],
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(lang['pokazyivat_zakryityie']);
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			grid.addActions({
				isClose: 1,
				name: 'action_isclosefilter_'+grid.id,
				text: lang['pokazyivat_otkryityie'],
				menu: menuIsCloseFilter
			});
			grid.getGrid().getStore().baseParams.isClose = 1;
		}

		return true;
	},
	deleteAttributeValue: function(mode) {
		var attrviewframe, win = this;

		switch ( mode ) {
			case 'TariffClass':
				attrviewframe = this.TariffClassAttributeValueGrid;
				break;

			case 'VolumeType':
				attrviewframe = this.VolumeTypeAttributeValueGrid;
				break;

			default:
				return false;
				break;
		}

		var grid = attrviewframe.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record.get('AttributeValue_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().reload();
							}
						}.createDelegate(this),
						params: {
							AttributeValue_id: record.get('AttributeValue_id')
						},
						url: "/?c=TariffVolumes&m=deleteValue"
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannoe_znachenie'],
			title: lang['vopros']
		});
	},
	openAttributeValueEditWindow: function(action, mode) {
		var attrviewframe, mainviewframe, object;

		switch ( mode ) {
			case 'TariffClass':
				attrviewframe = this.TariffClassAttributeValueGrid;
				mainviewframe = this.TariffClassGrid;
				object = 'TariffClass';
				break;

			case 'VolumeType':
				attrviewframe = this.VolumeTypeAttributeValueGrid;
				mainviewframe = this.VolumeTypeGrid;
				object = 'VolumeType';
				break;

			default:
				return false;
				break;
		}

		var grid = attrviewframe.getGrid();

		var params = new Object(), record;
		params.action = action;

		if (action != 'add') {
			record = grid.getSelectionModel().getSelected();
			if (!record.get('AttributeValue_id')) { return false; }
			params.AttributeValue_id = record.get('AttributeValue_id');
		}

		params.callback = function(){
			attrviewframe.getAction('action_refresh').execute();
		}.createDelegate(this);

		record = mainviewframe.getGrid().getSelectionModel().getSelected();
		if (!record.get(object + '_id')) {
			return false;
		}

		params.AttributeVision_TableName = 'dbo.' + object;
		params.AttributeVision_TablePKey = record.get(object + '_id');

		if (getRegionNick().inlist(['vologda', 'ufa', 'adygeya'])) {
			if (!isSuperAdmin())
				params.lpu_id = getGlobalOptions().lpu_id;

			if (object == 'VolumeType')	{
				var it = mainviewframe.ViewGridPanel.getSelectionModel().getSelected();
				if (it.get('VolumeType_Code') == 'АИСУслугаСистем')
					params.AIS = true;
			}
		}

		getWnd('swAttributeValueEditWindow').show(params);
	},
	doResetFiltersTariffClassGrid: function () {
		var filtersForm = this.TariffClassGridFilters.getForm();
		filtersForm.reset();
	},
	doFilterTariffClassGrid: function () {
		var filtersForm = this.TariffClassGridFilters.getForm();
		var filters = filtersForm.getValues();
		filters.start = 0;
		filters.limit = 100;

		this.TariffClassGrid.loadData({globalFilters: filters});
	},
	doResetPanel: function(panel) {
		if (panel.items && panel.items.items) {
			var o = panel.items.items;
			for (var i = 0, len = o.length; i < len; i++) {
				if (o[i].clearValue) {
					o[i].clearValue();
				} else if (o[i].setValue) {
					o[i].setValue(null);
				} else if (o[i].items && o[i].items.items) {
					this.doResetPanel(o[i]);
				}
			}
		}
	},
	doResetFiltersTariffClassAttributeValueGrid: function () {
		var win = this;
		win.TariffClassAttributeValueGridDynamicFilters.doResetPanel();
	},
	doResetFiltersVolumeTypeAttributeValueGrid: function () {
		var win = this;
		var filtersForm = this.VolumeTypeAttributeValueGridFilters.getForm();
		filtersForm.reset();
		win.doResetPanel(win.VolumeTypeAttributeValueGridDynamicFilters);
		if(this.isView) this.VolumeTypeAttributeValueGridDynamicFilters.setDisabledField(getGlobalOptions().lpu_id);
	},
	setFormFields: function(panel, fields, type) {
		var win = this;
		var contId = this.id + '_Container' + type,
			items = [];

		var keyName;

		// Получаем название поля первичного ключа
		for(var i=0; i<fields.length; i++) {
			if ( fields[i].name == 'keyName' ) {
				keyName = fields[i].value;
			}
		}

		// Узнаём ширину панели, если она ширше чем x*350, тогда выстраиваем в несколько колонок
		var columnitems = [];

		for(var i=0; i<fields.length; i++) {
			//для поля "идентификатор" добавляю дизабленый контрол со значением, чисто для отображения
			if (keyName != fields[i].name) {
				fields[i].disabled = false;
				if (fields[i].name) {
					fields[i].id = win.id + '_' + fields[i].name + '_' + type;
				}
				columnitems.push({
					layout: 'form',
					labelWidth: 120,
					defaults: {
						width: 350
					},
					border: false,
					items: [
						fields[i]
					]
				});
			}
		}

		if (columnitems.length >= 0) {
			items.push({
				border: false,
				layout: 'column',
				anchor: '-10',
				items: columnitems
			});
		}

		var container = new sw.Promed.Panel({
			layout: 'form',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			id: contId,
			defaults: {
				width: 350
			},
			items: items
		});

		panel.add(container);
		panel.doLayout();
		this.doLayout();
		this.recountGridHeight();
	},
	createDynamicFiltersFor: function(type,Tariff_begDT="") {
		var win = this;

		var AttributeVision_TablePKey = false;
		var AttributeVision_TableName = false;
		if (type == 1) {
			var panel = win.TariffClassAttributeValueGridDynamicFilters;
			var selected_record = this.TariffClassGrid.getGrid().getSelectionModel().getSelected();
			if (selected_record && selected_record.get('TariffClass_id')) {
				AttributeVision_TablePKey = selected_record.get('TariffClass_id');
				AttributeVision_TableName = 'dbo.TariffClass';
				
				win.TariffClassAttributeValueGridDynamicFilters.loadFilters({
					url: '/?c=TariffVolumes&m=getValuesFields',
					params: {
						AttributeVision_TableName: AttributeVision_TableName,
						AttributeVision_TablePKey: AttributeVision_TablePKey,
						getFilters: 1
					}
				},Tariff_begDT);
			}

			return;
		} else {
			var panel = win.VolumeTypeAttributeValueGridDynamicFilters;
			var formPanel = win.VolumeTypeAttributeValueGridFilters;
			var selected_record = this.VolumeTypeGrid.getGrid().getSelectionModel().getSelected();
			if (selected_record && selected_record.get('VolumeType_id')) {
				AttributeVision_TablePKey = selected_record.get('VolumeType_id');
				AttributeVision_TableName = 'dbo.VolumeType';
			}
		}

		// очищаем панель
		panel.removeAll(true);

		if (panel.ownerCt.collapsed) {
			return false;
		}

		if (AttributeVision_TablePKey) {
			this.getLoadMask(lang['zagruzka_parametrov']).show();
			Ext.Ajax.request({
				url: '/?c=TariffVolumes&m=getValuesFields',
				params: {
					AttributeVision_TableName: AttributeVision_TableName,
					AttributeVision_TablePKey: AttributeVision_TablePKey,
					getFilters: 1
				},
				scope: this,
				callback: function (o, s, r) {
					this.getLoadMask().hide();
					if (s) {
						var obj = Ext.util.JSON.decode(r.responseText);
						if (obj.data) {
							this.setFormFields(panel, obj.data, type);
							if(this.isView && type == 2)
								this.VolumeTypeAttributeValueGridDynamicFilters.setDisabledField(getGlobalOptions().lpu_id);
							
							// прогрузить данные комбо-справочников
							this.getFieldsLists(formPanel, {
								needConstructComboLists: true,
								needConstructEditFields: true
							});
							this.loadDataLists({}, this.lists, true, function () {
								for (var k in obj.data) {
									if (!Ext.isEmpty(win.findById(win.id + '_' + obj.data[k].name))) {
										if (obj.data[k].table) {
											switch (obj.data[k].table) {
												case 'UslugaComplex':
													if (!Ext.isEmpty(obj.data[k].value)) {
														var combo = win.findById(win.id + '_' + obj.data[k].name);
														var combo_value = obj.data[k].value;
														combo.getStore().load({
															params: {
																'UslugaComplex_id': combo_value
															},
															callback: function () {
																combo.setValue(combo_value);
															}
														});
													}
													break;
												case 'MesOld':
													if (!Ext.isEmpty(obj.data[k].value)) {
														var combo = win.findById(win.id + '_' + obj.data[k].name);
														var combo_value = obj.data[k].value;
														combo.getStore().load({
															params: {
																'Mes_id': combo_value
															},
															callback: function () {
																combo.setValue(combo_value);
															}
														});
													}
													break;
												case 'Diag':
													if (!Ext.isEmpty(obj.data[k].value)) {
														var combo = win.findById(win.id + '_' + obj.data[k].name);
														var combo_value = obj.data[k].value;
														combo.getStore().load({
															params: {where: "where Diag_id = " + combo_value},
															callback: function () {
																combo.setValue(combo_value);
															}
														});
													}
													break;
												default:
													win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
													break;
											}
										} else {
											win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
										}
									}
								}
							}); // прогружаем все справочники (третий параметр noclose - без операций над формой)
						} else {
							this.hide();
						}
					}
				}
			});
		}
	},
	doFilterTariffClassAttributeValueGrid: function () {
		var params = {};
		var filters = this.TariffClassAttributeValueGridDynamicFilters.getValues();

		params.start = 0;
		params.limit = 100;

		var record = this.TariffClassGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('TariffClass_id')) {
			this.TariffClassAttributeValueGrid.getGrid().getStore().removeAll();
			return false;
		}
		if (this.isView)
			filters = { 'atrib_16' : getGlobalOptions().lpu_id };

		params.filters = Ext.util.JSON.encode(filters);

		params.AttributeVision_TableName = 'dbo.TariffClass';
		params.AttributeVision_TablePKey = record.get('TariffClass_id');
		if (this.lpu_id){
			params.Lpu_id = this.lpu_id;
		}
		this.TariffClassAttributeValueGrid.loadData({globalFilters: params});
	},
	doFilterVolumeTypeAttributeValueGrid: function () {
		var filtersForm = this.VolumeTypeAttributeValueGridFilters.getForm();
		var params = {};
		var filters = filtersForm.getValues();

		params.start = 0;
		params.limit = 100;

		var record = this.VolumeTypeGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('VolumeType_id')) {
			this.VolumeTypeAttributeValueGrid.getGrid().getStore().removeAll();
			return false;
		}
		if (this.isView)
			filters = { 'atrib_16' : getGlobalOptions().lpu_id };
		
		params.filters = Ext.util.JSON.encode(filters);

		if (this.lpu_id) {
			params.Lpu_id = this.lpu_id;
		}
		params.AttributeVision_TableName = 'dbo.VolumeType';
		params.AttributeVision_TablePKey = record.get('VolumeType_id');

		this.VolumeTypeAttributeValueGrid.loadData({globalFilters: params});
	},
	doResetFiltersUslugaComplexTariffGrid: function () {
		var filtersForm = this.UslugaComplexTariffGridFilters.getForm();
		if (filtersForm.findField('Lpu_id').disabled) {
			var lpu_id = filtersForm.findField('Lpu_id').value;
			filtersForm.reset();
			filtersForm.findField('Lpu_id').setValue(lpu_id);
		} else	filtersForm.reset();
	},
	doFilterUslugaComplexTariffGrid: function () {
		var filtersForm = this.UslugaComplexTariffGridFilters.getForm();
		var filters = filtersForm.getValues();
		if (filtersForm.findField('Lpu_id').disabled) 
			filters.Lpu_id = filtersForm.findField('Lpu_id').getValue();
		
		if (this.isView) {
			filters.Lpu_id = getGlobalOptions().lpu_id;
			filtersForm.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		}
		filtersForm.findField('Lpu_id').setDisabled(this.isView);
		
		filters.start = 0;
		filters.limit = 100;

		this.UslugaComplexTariffGrid.loadData({globalFilters: filters});
	},
	openUslugaComplexTariffEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swUslugaComplexTariffEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_tarifa_uslugi_uje_otkryito']);
			return false;
		}

		var wnd = this;
		var grid = this.UslugaComplexTariffGrid.getGrid();
		var params = new Object();

		params.Lpu_id = grid.getStore().baseParams.Lpu_id || null;
		params.LpuBuilding_id = grid.getStore().baseParams.LpuBuilding_id || null;
		params.LpuUnit_id = grid.getStore().baseParams.LpuUnit_id || null;
		params.LpuSection_id = grid.getStore().baseParams.LpuSection_id || null;

		params.mode = 'TariffVolumes';

		params.action = action;
		params.callback = function(data) {
			if ( typeof data == 'object' && typeof data.uslugaComplexTariffData == 'object' && !Ext.isEmpty(data.uslugaComplexTariffData.UslugaComplexTariff_id) ) {
				wnd.UslugaComplexTariffGrid.focusOnRecord = data.uslugaComplexTariffData.UslugaComplexTariff_id;
			}
			grid.getStore().reload();
		}.createDelegate(this);
		params.formMode = 'remote';
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swUslugaComplexTariffEditWindow').show(params);
	},
	doFilterVolumeTypeGrid: function () {
		var filtersForm = this.VolumeTypeGridFilters.getForm();
		var filters = filtersForm.getValues();
		filters.start = 0;
		filters.limit = 100;

		this.VolumeTypeGrid.loadData({globalFilters: filters});
	},
	doResetFiltersVolumeTypeGrid: function () {
		var filtersForm = this.VolumeTypeGridFilters.getForm();
		filtersForm.reset();
	},
	deleteUslugaComplexTariff: function() {
		var grid = this.UslugaComplexTariffGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var record = grid.getSelectionModel().getSelected();

					var params = new Object();
					var url = "/?c=UslugaComplex&m=deleteUslugaComplexTariff";
					params.UslugaComplexTariff_id = record.get('UslugaComplexTariff_id');

					if (!Ext.isEmpty(url)) {
						Ext.Ajax.request({
							callback: function(opt, scs, response) {
								if (scs) {
									var result = Ext.util.JSON.decode(response.responseText);

									if (result.success)
									{
										grid.getStore().reload();
									}
								}
							}.createDelegate(this),
							params: params,
							url: url
						});
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_tarif_uslugi'],
			title: lang['vopros']
		});
	},
	initComponent: function() {
		var win = this;

		this.TariffClassGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterTariffClassGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.recountGridHeight();
						win.doLayout();
					},
					expand: function (p) {
						win.recountGridHeight();
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 0px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_begDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 160,
						labelWidth: 30,
						items: [{
							xtype: 'textfield',
							width: 118,
							name: 'TariffClass_Code',
							fieldLabel: lang['kod']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'TariffClass_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterTariffClassGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersTariffClassGrid();
								win.doFilterTariffClassGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.VolumeTypeGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterVolumeTypeGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.recountGridHeight();
						win.doLayout();
					},
					expand: function (p) {
						win.recountGridHeight();
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 0px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_begDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 160,
						labelWidth: 30,
						items: [{
							xtype: 'textfield',
							width: 118,
							name: 'VolumeType_Code',
							fieldLabel: lang['kod']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 310,
						labelWidth: 200,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 125,
						labelWidth: 15,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'VolumeType_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterVolumeTypeGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersVolumeTypeGrid();
								win.doFilterVolumeTypeGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.TariffClassGrid = new sw.Promed.ViewFrame({
			height: 200,
			dataUrl: '/?c=TariffVolumes&m=loadTariffClassGrid',
			title: lang['vidyi_tarifov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			stringfields: [
				{name: 'TariffClass_id', type: 'int', header: 'ID', key: true},
				{name: 'TariffClass_Code', header: lang['kod'], type: 'string', width: 120},
				{name: 'TariffClass_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'TariffClass_begDT', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'TariffClass_endDT', header: lang['data_okonchaniya'], type: 'date', width: 100}
			],
			onRowSelect: function(sm, index, record) {
				win.createDynamicFiltersFor(1,record.data.TariffClass_begDT);
				win.doFilterTariffClassAttributeValueGrid();

				if (getRegionNick() == 'vologda' && !isSuperAdmin() && typeof record == 'object'
					&& !Ext.isEmpty(record.get('TariffClass_Code')) && record.get('TariffClass_Code').inlist(['8', '9', '10', '14','15','16', '18','19'])
				) {
					if (record.get('TariffClass_Code').inlist(['8', '9', '10'])) {
						win.TariffClassAttributeValueGrid.setActionDisabled('action_add', !isSuperAdmin() && win.readAdmin);
					} else {
						win.TariffClassAttributeValueGrid.setActionDisabled('action_add', true);
					}
					win.TariffClassAttributeValueGrid.setActionDisabled('action_edit', true);
					win.TariffClassAttributeValueGrid.setActionDisabled('action_delete', true);
				} else {
					win.TariffClassAttributeValueGrid.setActionDisabled('action_add', !isSuperAdmin() && win.readAdmin);
					win.TariffClassAttributeValueGrid.setActionDisabled('action_edit', !isSuperAdmin() && win.readAdmin);
					win.TariffClassAttributeValueGrid.setActionDisabled('action_delete', !isSuperAdmin() && win.readAdmin);
				}
			},
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});
		win.TariffClassGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.TariffClassGrid);}.createDelegate(this));

		this.VolumeTypeGrid = new sw.Promed.ViewFrame({
			height: 200,
			dataUrl: '/?c=TariffVolumes&m=loadVolumeTypeGrid',
			title: lang['vidyi_obyemov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			stringfields: [
				{name: 'VolumeType_id', type: 'int', header: 'ID', key: true},
				{name: 'VolumeType_Code', header: lang['kod'], type: 'string', width: 120},
				{name: 'VolumeType_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'VolumeType_begDate', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'VolumeType_endDate', header: lang['data_okonchaniya'], type: 'date', width: 100}
			],
			onRowSelect: function(sm, index, record) {
				win.createDynamicFiltersFor(2);
				win.doFilterVolumeTypeAttributeValueGrid();

				if (getRegionNick() == 'vologda' && !isSuperAdmin() && typeof record == 'object'
					&& !Ext.isEmpty(record.get('VolumeType_Code')) && record.get('VolumeType_Code') == 'АИСУслугаСистем') {
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_add', true);
					win.VolumeTypeAttributeValueGrid.getAction('action_edit').disabled = true;
					win.VolumeTypeAttributeValueGrid.getAction('action_delete').disabled = true;
				} else {
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_add',!isSuperAdmin() && win.readAdmin && (
						!isLpuAdmin() || record.get('VolumeType_Code') !== '2020-МСС_COVID'
					));
					win.VolumeTypeAttributeValueGrid.getAction('action_edit').disabled
						= win.VolumeTypeAttributeValueGrid.getAction('action_edit').initialDisabled;
					win.VolumeTypeAttributeValueGrid.getAction('action_delete').disabled
						= win.VolumeTypeAttributeValueGrid.getAction('action_delete').initialDisabled;
				}

				// @task https://redmine.swan.perm.ru/issues/108804
				if (
					getRegionNick().inlist([ 'ekb', 'adygeya' ]) && typeof record == 'object' && 
					(isSuperAdmin() || (isLpuAdmin() && !Ext.isEmpty(record.get('VolumeType_Code')) && record.get('VolumeType_Code').inlist([ 'МР_ОТК', 'МР_ОТК_Пол', '2020_ДСО' ]))) 
				) {
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_add', false);
				}
			},
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});
		win.VolumeTypeGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.VolumeTypeGrid);}.createDelegate(this));

		this.TariffClassAttributeValueGridDynamicFilters = new sw.Promed.DynamicFiltersPanel({
			frame: true,
			region: 'north',
			border: false,
			autoHeight: true,
			labelWidth: 120,
			setFilterValue: function(value, panel) {
				panel = (panel == undefined) ? this : panel;
				// очищаем все значения на панели
				if (panel.items && panel.items.items) {
					var o = panel.items.items;
					for (var i = 0, len = o.length; i < len; i++) {
						if (o[i].getValue && typeof o[i].getValue == 'function') {
							if (o[i].hiddenName && o[i].hiddenName == 'atrib_16') {
								o[i].setValue(value);
								o[i].setDisabled(value != '');
							} else if (o[i].name && o[i].name == 'atrib_16') {
								o[i].setValue(value);
								o[i].setDisabled(value != '');
							}
						} else if (o[i].items && o[i].items.items) {
							this.setFilterValue(value, o[i]);
						}
					}
				}
			},
			doFilter: function() {
				win.doFilterTariffClassAttributeValueGrid();
			},
			baseFilters: [{
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				width: 100,
				name: 'AttributeValue_begDate_From',
				fieldLabel: lang['data_nachala_ot']
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				width: 100,
				name: 'AttributeValue_begDate_To',
				fieldLabel: lang['do']
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				width: 100,
				name: 'AttributeValue_endDate_From',
				fieldLabel: lang['data_okonchaniya_ot']
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				width: 100,
				name: 'AttributeValue_endDate_To',
				fieldLabel: lang['do']
			}]
		});



		this.VolumeTypeAttributeValueGridDynamicFilters = new sw.Promed.Panel({
			border: false,
			labelWidth: 120,
			items: [],
			setDisabledField: function(value, panel) {
				panel = (panel == undefined) ? this : panel;
				// очищаем все значения на панели
				if (panel.items && panel.items.items) {
					var o = panel.items.items;
					for (var i = 0, len = o.length; i < len; i++) {
						if (o[i].getValue && typeof o[i].getValue == 'function') {
							if (o[i].hiddenName && o[i].hiddenName == 'atrib_16') {
								o[i].setValue(value);
								o[i].setDisabled(true);
							} else if (o[i].name && o[i].name == 'atrib_16') {
								o[i].setValue(value);
								o[i].setDisabled(true);
							}
						} else if (o[i].items && o[i].items.items) {
							this.setDisabledField(value, o[i]);
						}
					}
				}
			}
		});

		this.VolumeTypeAttributeValueGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterVolumeTypeAttributeValueGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.doLayout();
						win.recountGridHeight();
					},
					expand: function (p) {
						win.createDynamicFiltersFor(2);
					}
				},
				xtype: 'fieldset',
				style: 'margin: 0px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 100,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					width: 600,
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 120,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'AttributeValue_begDate_From',
							fieldLabel: lang['data_nachala_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 135,
						labelWidth: 25,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'AttributeValue_begDate_To',
							fieldLabel: lang['do']
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					width: 600,
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 120,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'AttributeValue_endDate_From',
							fieldLabel: lang['data_okonchaniya_ot']
						}]
					}, {
						layout: 'form',
						border: false,
						width: 135,
						labelWidth: 25,
						items: [{
							xtype: 'swdatefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							width: 100,
							name: 'AttributeValue_endDate_To',
							fieldLabel: lang['do']
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterVolumeTypeAttributeValueGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersVolumeTypeAttributeValueGrid();
								win.doFilterVolumeTypeAttributeValueGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}, this.VolumeTypeAttributeValueGridDynamicFilters]
			}]
		});

		this.TariffClassAttributeValueGrid = new sw.Promed.ViewFrame({
			height: 200,
			dataUrl: '/?c=TariffVolumes&m=loadValuesGrid',
			title: lang['znacheniya_tarifov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			onRowSelect: function(){
				win.setViewMode(win.isView);
				win.TariffClassAttributeValueGridDynamicFilters.setFilterValue((win.isView)?getGlobalOptions().lpu_id:'');
			},
			stringfields: [
				{name: 'AttributeValue_id', type: 'int', header: 'ID', key: true},
				{name: 'AttributeValue_Value', header: lang['znachenie_tarifa'], type: 'string', width: 120},
				{name: 'AttributeValue_begDate', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'AttributeValue_endDate', header: lang['data_okonchaniya'], type: 'date', width: 100},
				{name: 'AttributeValue_ValueText', header: lang['spisok_atributov'], type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.openAttributeValueEditWindow('add', 'TariffClass'); }.createDelegate(this)},
				{name:'action_edit', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.openAttributeValueEditWindow('edit', 'TariffClass'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openAttributeValueEditWindow('view', 'TariffClass'); }.createDelegate(this)},
				{name:'action_delete', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.deleteAttributeValue('TariffClass'); }.createDelegate(this)}
			]
		});
		win.TariffClassAttributeValueGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.TariffClassAttributeValueGrid);}.createDelegate(this));

		if (getRegionNick() == 'vologda' && !isSuperAdmin()) {
			this.TariffClassAttributeValueGrid.ViewGridPanel.on('rowclick', function (grid, rowIndex) {
				
				var selected = win.TariffClassAttributeValueGrid.ViewGridPanel.getSelectionModel().getSelected();
				if (!Ext.isEmpty(selected)) {
					var text = selected.data.AttributeValue_ValueText;
					if (!Ext.isEmpty(text)) {
						text = text.split(',');
						text = text[0].split(' = ');				
						var act = (text[1] != getGlobalOptions().lpu_nick);
						win.TariffClassAttributeValueGrid.setActionDisabled('action_edit', act);
						win.TariffClassAttributeValueGrid.setActionDisabled('action_delete', act);
					}
				}
			});

			var store = win.TariffClassAttributeValueGrid.ViewGridPanel.getStore();
			store.on('load', function(s, rs){
				var i = 0;
				var ar = [];
				store.each(function(rec){
					var text = rec.data.AttributeValue_ValueText;
					if (!Ext.isEmpty(text)) {
						text = text.split(',');
						text = text[0].split(' = ');
						if (text[0] == 'МО') {
							if (text[1].length > 0 && text[1] != getGlobalOptions().lpu_nick) {
								ar.unshift(i);
							}
						}
					}
					i++;
				});
				ar.forEach(function(item,k,arr){
					store.removeAt(item);
				})
			});
		}
		this.VolumeTypeAttributeValueGrid = new sw.Promed.ViewFrame({
			height: 200,
			dataUrl: '/?c=TariffVolumes&m=loadValuesGrid',
			title: lang['znacheniya_obyemov'],
			uniqueId: true,
			border: false,
			paging: true,
			totalProperty: 'totalCount',
			root: 'data',
			region: 'center',
			autoLoadData: false,
			onLoadData: function() {
				var record = win.VolumeTypeGrid.getGrid().getSelectionModel().getSelected();

				// @task https://redmine.swan.perm.ru/issues/108804
				if ( getRegionNick().inlist(['ekb', 'adygeya']) && typeof record == 'object' && (isSuperAdmin() || (isLpuAdmin() && !Ext.isEmpty(record.get('VolumeType_Code')) && record.get('VolumeType_Code').inlist([ 'МР_ОТК', 'МР_ОТК_Пол', '2020_ДСО' ]))) ) {
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_add', false);
				}
			},
			onRowSelect: function(sm, rowIdx, record) {
				// @task https://redmine.swan.perm.ru/issues/108804
				if ( getRegionNick().inlist(['ekb', 'ufa', 'adygeya']) ) {
					var disableActions = !(
						typeof record == 'object'
						&& !Ext.isEmpty(record.get('AttributeValue_id'))
						&& (
							isSuperAdmin()
							|| (isLpuAdmin(record.get('Lpu_id')) && win.VolumeTypeGrid.getGrid().getSelectionModel().getSelected().get('VolumeType_Code').inlist([ 'МР_ОТК', 'МР_ОТК_Пол', '2020-МСС_COVID', '2020_ДСО' ]))
						)
					);

					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_edit', disableActions);
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_delete', disableActions);
				}
				var baseParams = win.VolumeTypeAttributeValueGrid.getGrid().getStore().baseParams;
				if (getRegionNick() == 'vologda' && !this.readAdmin && !isSuperAdmin) {
					var enableActions = !(baseParams.Lpu_id == getGlobalOptions().lpu_id);
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_edit', enableActions);
					win.VolumeTypeAttributeValueGrid.setActionDisabled('action_delete', enableActions);
				}
				if (win.isView) {
					win.setViewMode(true);
				}
			},
			stringfields: [
				{name: 'AttributeValue_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'AttributeValue_Value', header: lang['znachenie_obyema'], type: 'string', width: 120},
				{name: 'AttributeValue_begDate', header: lang['data_nachala'], type: 'date', width: 100},
				{name: 'AttributeValue_endDate', header: lang['data_okonchaniya'], type: 'date', width: 100},
				{name: 'AttributeValue_ValueText', header: lang['spisok_atributov'], type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.openAttributeValueEditWindow('add', 'VolumeType'); }.createDelegate(this)},
				{name:'action_edit', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.openAttributeValueEditWindow('edit', 'VolumeType'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.openAttributeValueEditWindow('view', 'VolumeType'); }.createDelegate(this)},
				{name:'action_delete', disabled: !isSuperAdmin() && this.readAdmin, handler: function() { this.deleteAttributeValue('VolumeType'); }.createDelegate(this)}
			]
		});
		win.VolumeTypeAttributeValueGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.VolumeTypeAttributeValueGrid);}.createDelegate(this));

		//возможно будет нужно
		if (getRegionNick() == 'vologda' && !isSuperAdmin()) {
			var store2 = win.VolumeTypeAttributeValueGrid.ViewGridPanel.getStore();
			store2.on('load', function(s, rs){
				var i = 0;
				var ar = [];
				store2.each(function(rec){
					var text = rec.data.AttributeValue_ValueText;
					if (!Ext.isEmpty(text)) {
						text = text.split(',');
						text = text[0].split(' = ');
						if (text[0] == 'МО') {
							if (text[1].length > 0 && text[1] != getGlobalOptions().lpu_nick) {
								ar.unshift(i);
							}
						}
					}
					i++;
				});
				ar.forEach(function(item,k,arr){
					store2.removeAt(item);
				})
			});
		}

		this.UslugaComplexTariffGridFilters = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doFilterUslugaComplexTariffGrid();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function (p) {
						win.doLayout();
					},
					expand: function (p) {
						win.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 0px 5px',
				title: lang['filtryi'],
				collapsible: true,
				autoHeight: true,
				labelWidth: 200,
				anchor: '-10',
				layout: 'form',
				items: [{
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 410,
						labelWidth: 50,
						items: [{
							fieldLabel: lang['mo'],
							loadParams: {params: {where: ' where Lpu_endDate is null'}},
							hiddenName: 'Lpu_id',
							listWidth: 600,
							width: 350,
							xtype: 'swlpucombo'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					anchor: '-10',
					items: [{
						layout: 'form',
						border: false,
						width: 410,
						labelWidth: 50,
						items: [{
							fieldLabel: lang['usluga'],
							hiddenName: 'UslugaComplex_id',
							listWidth: 600,
							width: 350,
							xtype: 'swuslugacomplexallcombo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							text: BTN_FILTER,
							xtype: 'button',
							handler: function () {
								win.doFilterUslugaComplexTariffGrid();
							},
							iconCls: 'search16'
						}]
					}, {
						layout: 'form',
						bodyStyle: 'padding-left: 5px;',
						border: false,
						items: [{
							text: BTN_RESETFILTER,
							xtype: 'button',
							handler: function () {
								win.doResetFiltersUslugaComplexTariffGrid();
								win.doFilterUslugaComplexTariffGrid();
							},
							iconCls: 'resetsearch16'
						}]
					}]
				}]
			}]
		});

		this.UslugaComplexTariffGrid = new sw.Promed.ViewFrame(
			{
				dataUrl: '/?c=UslugaComplex&m=loadUslugaComplexTariffOnPlaceGrid',
				title: lang['tarifyi_uslug'],
				uniqueId: true,
				border: false,
				paging: true,
				totalProperty: 'totalCount',
				root: 'data',
				region: 'center',
				autoLoadData: false,
				onDblClick: function(grid, number, object){
					this.onEnter();
				},
				onEnter: function() {
					var action = this.getAction('action_edit').isDisabled() ? 'view':'edit';
					win.openUslugaComplexTariffEditWindow(action);
				},
				onRowSelect: function(sm, rowIdx, record) {
					if ( !record || !record.get('UslugaComplexTariff_id') ) {
						return false;
					}

					var baseParams = this.UslugaComplexTariffGrid.getGrid().getStore().baseParams;

					if(this.readAdmin){
						this.UslugaComplexTariffGrid.setActionDisabled('action_add', true);
						if(!isSuperAdmin()){
							this.UslugaComplexTariffGrid.setActionDisabled('action_edit', true);
							this.UslugaComplexTariffGrid.setActionDisabled('action_delete', true);
						}
					} else {
						if ( !isSuperAdmin() && record && (
								(Ext.isEmpty(record.get('Lpu_id')) && !Ext.isEmpty(baseParams.Lpu_id))
								|| (Ext.isEmpty(record.get('LpuBuilding_id')) && !Ext.isEmpty(baseParams.LpuBuilding_id))
								|| (Ext.isEmpty(record.get('LpuUnit_id')) && !Ext.isEmpty(baseParams.LpuUnit_id))
								|| (Ext.isEmpty(record.get('LpuSection_id')) && !Ext.isEmpty(baseParams.LpuSection_id))
							)) {
							this.UslugaComplexTariffGrid.setActionDisabled('action_edit', true);
							this.UslugaComplexTariffGrid.setActionDisabled('action_delete', true);
						} else {
							this.UslugaComplexTariffGrid.setActionDisabled('action_edit', false);
							this.UslugaComplexTariffGrid.setActionDisabled('action_delete', false);
						}
					}
					if (getRegionNick() == 'vologda' && !this.readAdmin && !isSuperAdmin){
						var enableActions = !(baseParams.lpu_id == getGlobalOptions().lpu_id);
						this.UslugaComplexTariffGrid.setActionDisabled('action_edit', enableActions);
						this.UslugaComplexTariffGrid.setActionDisabled('action_delete', enableActions);
					}
					win.setViewMode(win.isView);

				}.createDelegate(this),
				stringfields: [
					{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
					{ name: 'RecordStatus_Code', type: 'int', hidden: true },
					{ name: 'UslugaComplexTariffType_id', type: 'int', hidden: true },
					{ name: 'Lpu_id', type: 'int', hidden: true },
					{ name: 'LpuBuilding_id', type: 'int', hidden: true },
					{ name: 'LpuSection_id', type: 'int', hidden: true },
					{ name: 'LpuUnit_id', type: 'int', hidden: true },
					{ name: 'PayType_id', type: 'int', hidden: true },
					{ name: 'LpuLevel_id', type: 'int', hidden: true },
					{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
					{ name: 'LpuUnitType_id', type: 'int', hidden: true },
					{ name: 'MesAgeGroup_id', type: 'int', hidden: true },
					{ name: 'Sex_id', type: 'int', hidden: true },
					{ name: 'VizitClass_id', type: 'int', hidden: true },
					{ name: 'EvnUsluga_setDate', type: 'date', hidden: true },
					{ name: 'UslugaComplex_id', type: 'int', hidden: true },
					{ name: 'UslugaComplex_Code', type: 'string', header: lang['kod_uslugi'], width: 100 },
					{ name: 'UslugaComplex_Name', type: 'string', header: lang['naimenovanie_uslugi'], width: 200 },
					{ name: 'UslugaComplexTariff_Code', type: 'string', header: lang['kod'], width: 50 },
					{ name: 'UslugaComplexTariff_Name', type: 'string', header: lang['naimenovanie'], width: 100 },
					{ name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 80 },
					{ name: 'UslugaComplexTariffType_Name', type: 'string', header: lang['tip_tarifa'], width: 100 },
					{ name: 'LpuLevel_Name', type: 'string', header: lang['uroven_mo'], width: 100 },
					{ name: 'Lpu_Name', type: 'string', header: lang['mo'], width: 200 },
					{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
					{ name: 'LpuUnitType_Name', type: 'string', header: lang['vid_med_pomoschi'], width: 100 },
					{ name: 'MesAgeGroup_Name', type: 'string', header: lang['vozrastnaya_gruppa'], width: 100 },
					{ name: 'Sex_Name', type: 'string', header: lang['pol_patsienta'], width: 80 },
					{ name: 'UslugaComplexTariff_Tariff', type: 'float', header: lang['tarif'], width: 80 },
					{ name: 'UslugaComplexTariff_UED', type: 'float', header: lang['uet_vracha'], width: 80 },
					{ name: 'UslugaComplexTariff_UEM', type: 'float', header: lang['uet_sr_medpersonala'], width: 80 },
					{ name: 'UslugaComplexTariff_begDate', type: 'date', header: lang['data_nachala'], width: 80 },
					{ name: 'UslugaComplexTariff_endDate', type: 'date', header: lang['data_okonchaniya'], width: 80 },
					{ name: 'pmUser_Name', type: 'string', header: lang['polzovatel'], id: 'autoexpand' }
				],
				actions: [
					{ name: 'action_add', handler: function() { win.openUslugaComplexTariffEditWindow('add'); } },
					{ name: 'action_edit', handler: function() { win.openUslugaComplexTariffEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { win.openUslugaComplexTariffEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { win.deleteUslugaComplexTariff(); } },
					{ name: 'action_refresh' },
					{ name: 'action_print' }
				]
			});
		win.UslugaComplexTariffGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(win.UslugaComplexTariffGrid);}.createDelegate(this));

		if (getRegionNick() == 'vologda' && !isSuperAdmin()) {
			this.UslugaComplexTariffGrid.ViewGridPanel.on('rowclick', function (grid, rowIndex) {
				var lpu = grid.store.data.items[rowIndex].data.Lpu_id;
				var act = (lpu != getGlobalOptions().lpu_id);

				win.UslugaComplexTariffGrid.setActionDisabled('action_edit', act);
				win.UslugaComplexTariffGrid.setActionDisabled('action_delete', act);
			});
		}

		if (getRegionNick() == 'msk' && !isSuperAdmin()) {
				win.UslugaComplexTariffGrid.setActionDisabled('action_add', true);
		}

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			region: 'center',
			layoutOnTabChange: true,
			items: [{
				id: 'tab_tariffs',
				layout: 'form',
				autoScroll: true,
				title: lang['tarifyi'],
				items: [
					{
						border: 'false',
						region: 'center',
						layout: 'form',
						items: [
							win.TariffClassGridFilters,
							win.TariffClassGrid
						]
					},
					{
						border: 'false',
						region: 'south',
						autoHeight: true,
						layout: 'form',
						items: [
							win.TariffClassAttributeValueGridDynamicFilters,
							win.TariffClassAttributeValueGrid
						]
					}
				]
			}, {
				id: 'tab_volumes',
				layout: 'form',
				autoScroll: true,
				title: lang['obyemyi'],
				items: [
					{
						border: 'false',
						region: 'center',
						layout: 'form',
						items: [
							win.VolumeTypeGridFilters,
							win.VolumeTypeGrid
						]
					},
					{
						border: 'false',
						region: 'south',
						autoHeight: true,
						layout: 'form',
						items: [
							win.VolumeTypeAttributeValueGridFilters,
							win.VolumeTypeAttributeValueGrid
						]
					}
				]
			}, {
				id: 'tab_uslugatariffs',
				layout: 'border',
				// disabled: true, // пока убрал, чтобы на рабочую не попало, надо доделать функционал (в ТЗ описан слишком размыто)
				title: lang['tarifyi_uslug'],
				items: [
					win.UslugaComplexTariffGridFilters,
					win.UslugaComplexTariffGrid
				]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {
					switch(panel.id) {
						case 'tab_tariffs':
							win.TariffClassGrid.loadData();
							break;

						case 'tab_volumes':
							win.VolumeTypeGrid.loadData();
							break;

						case 'tab_uslugatariffs':
							win.doFilterUslugaComplexTariffGrid();
							break;
					}
					win.doLayout();
					win.recountGridHeight();
				}
			}
		});

		Ext.apply(this, {
			items: [
				win.TabPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						win.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swTariffVolumesViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
