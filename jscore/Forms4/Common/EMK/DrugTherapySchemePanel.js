/**
 * Панель с возможностью добавления комбобоксов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.DrugTherapySchemePanel', {
	extend: 'swPanel',
	title: 'СХЕМЫ ЛЕКАРСТВЕННОЙ ТЕРАПИИ',
	collapsed: true,
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		this.openDrugTherapySchemeForm();
	},
	setParams: function(params) {
		this.EvnVizitPL_id = params.EvnVizitPL_id || null;
		this.FilterIds = params.FilterIds || [];
	},
	setIds: function(params) {
		var me = this;
		
	},
	getIds: function(params) {
		var me = this;
		
	},
	openDrugTherapySchemeForm: function() {
		
		if (!this.EvnVizitPL_id) return false;
		
		var params = {},
			me = this;
		
		params.EvnVizitPL_id = me.EvnVizitPL_id;
		params.FilterIds = me.FilterIds;
		params.callback = function(data) {
			if (!data) return false;
			
			me.expand();
			
			me.DrugTherapySchemeGrid.getStore().loadData([data], true);
		};
			
		getWnd('swDrugTherapySchemeAddWindow').show(params);
	},
	deleteDrugTherapyScheme: function(EvnVizitPLDrugTherapyLink_id) {
		var me = this,
			gridStore = me.DrugTherapySchemeGrid.getStore();
		
		if (!EvnVizitPLDrugTherapyLink_id) return false;

		checkDeleteRecord({
			callback: function () {
				me.mask('Удаление записи...');
				Ext6.Ajax.request({
					url: '/?c=EMK&m=deleteDrugTherapyScheme',
					params: {
						EvnVizitPLDrugTherapyLink_id: EvnVizitPLDrugTherapyLink_id
					},
					callback: function () {
						me.unmask();
						var record = gridStore.findRecord('EvnVizitPLDrugTherapyLink_id', EvnVizitPLDrugTherapyLink_id);
						if (record) {
							gridStore.remove(record);
						}
					}
				})
			}
		});
	},
	initComponent: function() {
		var me = this;
		
		this.DrugTherapySchemeGrid = Ext6.create('Ext6.grid.Panel', {
			hideHeaders: true,
			border: false,
			columns: [{
				dataIndex: 'DrugTherapyScheme_Code',
				width: 140
			}, {
				dataIndex: 'DrugTherapyScheme_Name',
				flex: 1
			}, {
				hidden: true,
				text: 'DrugTherapyScheme_id',
				dataIndex: 'DrugTherapyScheme_id'
			}, {
				hidden: true,
				text: 'EvnVizitPLDrugTherapyLink_id',
				dataIndex: 'EvnVizitPLDrugTherapyLink_id'
			}, {
				width: 40,
				renderer: function (value, metaData, record) {
					if (me.accessType == 'edit') {
						return "<div class='x6-tool-delete' onclick='Ext6.getCmp(\"" + me.getId() + "\").deleteDrugTherapyScheme(" + record.get('EvnVizitPLDrugTherapyLink_id') + ");'></div>";
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnVizitPLDrugTherapyLink_id', type: 'int' },
					{ name: 'DrugTherapyScheme_id', type: 'int' },
					{ name: 'DrugTherapyScheme_Code', type: 'string'},
					{ name: 'DrugTherapyScheme_Name', type: 'string'},
				]
			})
		});

		Ext6.apply(this, {
			bodyPadding: 10,
			items: [
				this.DrugTherapySchemeGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function () {
					me.openDrugTherapySchemeForm();
				}
			}]
		});

		this.callParent(arguments);
	}
});