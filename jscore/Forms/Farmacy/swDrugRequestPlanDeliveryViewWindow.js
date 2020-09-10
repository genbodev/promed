/**
* swDrugRequestPlanDeliveryViewWindow - окно просмотра плана потребления
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      09.2014
* @comment      
*/
sw.Promed.swDrugRequestPlanDeliveryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['plan_potrebleniya_mo'],
	layout: 'border',
	id: 'DrugRequestPlanDeliveryViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params = form.getValues();

		params.start = 0;
		params.limit = 100;
		params.DrugRequest_id = wnd.FilterPanel.getForm().findField('DrugRequest_id').getValue();

		if (params.DrugRequest_id) {
			this.setPeriodColumns(function() {
				wnd.refreshGrid();

				var lpu_id = null;
				if (wnd.mo_request_combo.getValue() > 0) {
					var idx = wnd.mo_request_combo.getStore().findBy(function(rec) { return rec.get('DrugRequest_id') == wnd.mo_request_combo.getValue(); });
					if (idx > -1) {
						lpu_id = wnd.mo_request_combo.getStore().getAt(idx).get('Lpu_id');
					}
				}
				wnd.SearchGrid.setReadOnly(Ext.isEmpty(lpu_id) || lpu_id != getGlobalOptions().lpu_id);

				params.PeriodId_List = wnd.getPeriodIdList();
				if (params.PeriodId_List.length > 0) {
					wnd.SearchGrid.loadData({params: params, globalFilters: params});
				} else {
					sw.swMsg.alert("Ошибка", "Для выбранной заявки не указаны планово-отчетные периоды");
					return false;
				}
			});
		}
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll();
		wnd.hidePlanColumns();
	},
	setPeriodColumns: function(callback) {
		var wnd = this;
		var period_id = null;

		if (this.mo_request_combo.getValue() > 0) {
			var idx = this.mo_request_combo.getStore().findBy(function(rec) { return rec.get('DrugRequest_id') == this.mo_request_combo.getValue(); }.createDelegate(this));
			if (idx > -1) {
				period_id = this.mo_request_combo.getStore().getAt(idx).get('DrugRequestPeriod_id');
			}
		}

		if (Ext.isEmpty(period_id)) {
			return false;
		}

		if (!Ext.isEmpty(wnd.SearchGrid.Period_id) && wnd.SearchGrid.Period_id == period_id) {
			if (callback && typeof callback == 'function') {
				callback();
			}
		} else {
			wnd.SearchGrid.PeriodColumns = new Array();
			wnd.SearchGrid.Period_id = null;

			Ext.Ajax.request({
				params: {
					DrugRequestPeriod_id: period_id
				},
				callback: function (options, success, response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && Ext.isArray(result)) {
						wnd.SearchGrid.PeriodColumns = result;
						wnd.SearchGrid.Period_id = period_id;
					}
					if (callback && typeof callback == 'function') {
						callback();
					}
				},
				url:'/?c=MzDrugRequest&m=loadDrugRequestPlanPeriodList'
			});
		}
	},
	getPeriodIdList: function() {
		var result = '';
		if ('PeriodColumns' in this.SearchGrid) {
			var v_arr = this.SearchGrid.PeriodColumns;
			var period_arr = new Array();
			for	(var i = 0; i < v_arr.length; i++) {
				period_arr.push(v_arr[i].DrugRequestPlanPeriod_id);
			}
			result = period_arr.join(',');
		}
		return result;
	},
	refreshGrid: function(mode) {
		var wnd = this;
		var viewframe = wnd.SearchGrid;
		var grid = viewframe.getGrid();

		var con_insert_idx = 6; //индекс колонки, начиная с которой вставлюяютя динамические колонки

		var con = new Array();
		var con_start = new Array();
		var con_end = new Array();
		var store_key = null;

		for(var i = 0; i < viewframe.stringfields.length; i++) {
			var obj = new Object();
			Ext.apply(obj, viewframe.stringfields[i]);
			if (i < con_insert_idx) {
				con_start.push(obj);
			} else {
				con_end.push(obj);
			}
			if (viewframe.stringfields[i].key) {
				store_key = viewframe.stringfields[i].name;
			}
		}

		var v_arr = wnd.SearchGrid.PeriodColumns;
		con = con_start;
		for	(var i = 0; i < v_arr.length; i++) {
			var column = new Object();
			column.header = v_arr[i].DrugRequestPlanPeriod_Name;
			column.dataIndex = 'plan_'+v_arr[i].DrugRequestPlanPeriod_id;
			column.name = 'plan_'+v_arr[i].DrugRequestPlanPeriod_id;
			column.width = 120;
			column.type = 'float';
			column.sortable = true;
			column.editable = true;
			column.editor = new Ext.form.NumberField({ allowNegative:false });
			con.push(column);
		}
		con = con.concat(con_end);

		var st_arr = new Array();
		for (var i = 0; i < con.length; i++) {
			if(con[i].name) {
				st_arr.push(con[i].name);
			}
		}

		var cm = new Ext.grid.ColumnModel(con);
		var store = null;

		if (mode && mode == 'only_column') {
			var store = grid.getStore();
		} else {
			var store = new sw.Promed.Store({
				id: 0,
				fields: st_arr,
				idProperty: store_key,
				data: new Array(),
				listeners: {
					load: function(store, record, options) {
						wnd.SearchGrid.rowCount = store.getCount();
					}
				}
			});
			store.proxy = new Ext.data.HttpProxy({
				url: wnd.SearchGrid.dataUrl
			});
		}

		grid.reconfigure(store, cm);
	},
	hidePlanColumns: function() { //для скрытия колонок с периодами, алтернатива удаления
		var v_arr = this.SearchGrid.PeriodColumns;
		if (Ext.isArray(v_arr)) {
			for	(var i = 0; i < v_arr.length; i++) {
				this.SearchGrid.setColumnHidden('plan_'+v_arr[i].DrugRequestPlanPeriod_id, true);
			}
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestPlanDeliveryViewWindow.superclass.show.apply(this, arguments);

		this.doReset();
		this.SearchGrid.PeriodColumns = new Array();
		this.SearchGrid.Period_id = null;

		if (haveArmType('minzdravdlo') || haveArmType('adminllo')) {
			this.region_request_combo.getStore().baseParams.mode = 'with_mo';
			this.mo_request_combo.getStore().baseParams.Lpu_id = null;
		} else {
			this.region_request_combo.getStore().baseParams.mode = 'with_user_mo';
			this.mo_request_combo.getStore().baseParams.Lpu_id = getGlobalOptions().lpu_id;
		}
		this.region_request_combo.loadData();
	},
	initComponent: function() {
		var wnd = this;

		this.region_request_combo = new sw.Promed.SwBaseLocalCombo ({
			fieldLabel: lang['zayavochnaya_kampaniya'],
			hiddenName: 'RegionDrugRequest_id',
			displayField: 'DrugRequest_Name',
			valueField: 'DrugRequest_id',
			allowBlank: true,
			editable: true,
			anchor: '80%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{DrugRequest_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'DrugRequest_id', mapping: 'DrugRequest_id' },
					{ name: 'DrugRequest_Name', mapping: 'DrugRequest_Name' }
				],
				key: 'DrugRequest_id',
				sortInfo: { field: 'DrugRequest_Name' },
				url:'/?c=MzDrugRequest&m=loadRegionDrugRequestCombo'
			}),
			childrenList: ['DrugRequest_id'],
			listeners: {
				'select': function(combo) {
					combo.childrenList.forEach(function(field_name){
						var f_combo = wnd.FilterPanel.getForm().findField(field_name);
						if (!f_combo.disabled) {
							f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
							f_combo.loadData();
						}
					});
				}
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});

		this.mo_request_combo = new sw.Promed.SwBaseLocalCombo ({
			fieldLabel: lang['zayavka_mo'],
			hiddenName: 'DrugRequest_id',
			displayField: 'DrugRequest_Name',
			valueField: 'DrugRequest_id',
			allowBlank: true,
			editable: true,
			anchor: '80%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{DrugRequest_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'DrugRequest_id', mapping: 'DrugRequest_id' },
					{ name: 'DrugRequest_Name', mapping: 'DrugRequest_Name' },
					{ name: 'Lpu_id', mapping: 'Lpu_id' },
					{ name: 'DrugRequestPeriod_id', mapping: 'DrugRequestPeriod_id' }
				],
				key: 'DrugRequest_id',
				sortInfo: { field: 'DrugRequest_Name' },
				url:'/?c=MzDrugRequest&m=loadMoDrugRequestCombo'
			}),
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setFirst();
					}
				});
			},
			setFirst: function() {
				var store = this.getStore();
				if (store.getCount() > 0) {
					this.setValue(store.getAt(0).get(store.key));
				}
			}
		});

		this.FilterPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [
				this.region_request_combo,
				this.mo_request_combo
			]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['poisk'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['ochistit'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			saveAtOnce: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadDrugRequestPlanDeliveryGrid',
			height: 180,
			object: 'DrugRequestPlanDelivery',
			id: 'DrugRequestPlanDeliveryGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			enableColumnHide: false,
			stringfields: [
				{ name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true, hidden: true },
				{ name: 'DrugComplexMnn_id', hidden: true },
				{ name: 'Tradenames_id', hidden: true },
				{ name: 'Grid_Editor', type: 'string', editor: new Ext.form.NumberField(), hidden: true },
				{ name: 'DrugComplexMnn_RusName', dataIndex: 'DrugComplexMnn_RusName', type: 'string', header: lang['mnn'], width: 300, sortable: true },
				{ name: 'Tradenames_Name', dataIndex: 'Tradenames_Name', type: 'string', header: lang['torgovoe_naimenovanie'], id: 'autoexpand', sortable: true },
				{ name: 'DrugRequestRow_Kolvo', dataIndex: 'DrugRequestRow_Kolvo', type: 'string', header: lang['kolichestvo'], sortable: true },
				{ name: 'CheckColumn', dataIndex: 'CheckColumn', type: 'string', header: lang['kontrol'], sortable: false, renderer: function(v, p, r) {
					var sum = 0;
					if (wnd.SearchGrid.PeriodColumns) {
						var v_arr = wnd.SearchGrid.PeriodColumns;
						for	(var i = 0; i < v_arr.length; i++) {
							var val = r.get('plan_'+v_arr[i].DrugRequestPlanPeriod_id);
							sum += val > 0 ? val*1 : 0;
						}
					}
					if (Ext.isEmpty(r.get('DrugRequestRow_Kolvo'))) {
						sum = null;
					}
					return r.get('DrugRequestRow_Kolvo') != sum &&  !Ext.isEmpty(sum)? '<div style="color: #ff0000">'+sum+'</div>' : sum;
				}}
			],
			title: null,
			toolbar: false,
			onBeforeEdit: function(o) {
				if (Ext.isEmpty(o.record.get('DrugRequestRow_id'))) {
					return false;
				}
			},
			onAfterEdit: function(o) {
				var field = o.field.split('_');
				var period_id = field[1];
				var request_id = wnd.FilterPanel.getForm().findField('DrugRequest_id').getValue();

				Ext.Ajax.request({
					params: {
						DrugRequest_id: request_id,
						DrugRequestPlanPeriod_id: period_id,
						DrugComplexMnn_id: o.record.get('DrugComplexMnn_id'),
						Tradenames_id: o.record.get('Tradenames_id'),
						DrugRequestPlanDelivery_Kolvo: o.value
					},
					callback: function (options, success, response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result && 'DrugRequestPlanDelivery_Kolvo' in result) {
							o.record.set(o.field, result.DrugRequestPlanDelivery_Kolvo);
						} else {
							o.record.set(o.field, o.originalValue);
						}
						o.record.commit();
					},
					url:'/?c=MzDrugRequest&m=saveDrugRequestPlanDeliveryKolvo'
				});
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swDrugRequestPlanDeliveryViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});