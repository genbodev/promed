Ext6.define('common.EMK.PacketPrescrExt2.AddPrescrByCheckGridsPanelExt2', {
	extend: 'swPanel',
	requires: [
		'Ext6.layout.container.VBox',
		'Ext6.data.*',
		'Ext6.grid.*',
		'Ext6.tree.*',
		'Ext6.grid.column.Check',
		'common.EMK.PacketPrescrExt2.controllers.AddPrescrByCheckGridsCntrExt2',
		'common.EMK.models.AddPrescrByCheckGridsModel'
	],
	refId: 'AddPrescrByCheckGridsPanelExt2',
	alias: 'widget.AddPrescrByCheckGridsPanelExt2',
	controller: 'AddPrescrByCheckGridsCntrExt2',

	cls: 'addEvnPrescribePanel',
	parentPanel: '',
	layout: 'fit',
	//autoHeight: true,

	//scrollable: true,
	cbFn: Ext6.emptyFn,
	reCountSelect: function(){
		var me = this,
			arrDrugSelCount = this.DrugDataGrid.getView().getSelectionModel().getCount(),
			arrLabDiagSelCount = this.LabDiagDataGrid.getView().getSelectionModel().getCount(),
			arrFuncDiagSelCount = this.FuncDiagDataGrid.getView().getSelectionModel().getCount();

	},
	doSave: function(){
		var me = this,
			arrDrugSelected = this.DrugDataGrid.getView().getSelectionModel().getSelected(),
			arrLabDiagSelected = this.LabDiagDataGrid.getView().getSelectionModel().getSelected(),
			arrFuncDiagSelected = this.FuncDiagDataGrid.getView().getSelectionModel().getSelected(),
			arrPrescr = new Object(),
			save_url = '/?c=EvnPrescr&m=saveCureStandartForm',
			arrDrug = [],
			labdiag = new Object(),
			arrFuncDiag = [],
			funcdiag = new Object(),
			str,
			data = me.getController().data;

		arrDrugSelected.each(function (el) {
			arrDrug.push(el.get('ActMatters_id'));
		});
		arrLabDiagSelected.each(function (el) {
			str = el.get('UslugaComplex_id').toString();
			labdiag[str] = [el.get('UslugaComplex_id')];
		});
		arrFuncDiagSelected.each(function (el) {
			arrFuncDiag.push(el.get('UslugaComplex_id'));
			/*str = el.get('UslugaComplex_id').toString();
			funcdiag[str] = [el.get('UslugaComplex_id')];*/
		});
		arrPrescr.oper = [];
		arrPrescr.proc = [];
		arrPrescr.funcdiag = arrFuncDiag;
		arrPrescr.drug = arrDrug;
		arrPrescr.labdiag = labdiag;
		var order_uslugalist_str = Ext6.JSON.encode(arrPrescr).toString();
		me.mask('Сохранение назначений');

		Ext6.Ajax.request({
			url: save_url,
			callback: function(opt, success, response) {
				me.parentPanel.close();
				//me.parentPanel.callback();
				me.unmask();
				//me.parentPanel.getController().loadGrids();
				me.cbFn();
			},
			params: {
				PersonEvn_id: data.PersonEvn_id,
				Person_id: data.Person_id,
				Server_id: data.Server_id,
				Evn_pid: data.Evn_id,
				Evn_id: data.Evn_id,
				save_data: order_uslugalist_str,
				parentEvnClass_SysNick: 'EvnSection'
			}
		});
		/*
		/?c=EvnPrescr&m=saveCureStandartForm
		PersonEvn_id: 87059333
		Server_id: 10010833
		Evn_pid: 730023881069718
		save_data: {"oper":[],"proc":[],"funcdiag":[],"drug":[],"labdiag":{"206126":["206126"],"206287":["206287"],"206288":["206288"]}}
		parentEvnClass_SysNick: EvnVizitPL
		*/
	},
	initComponent: function() {
		var me = this;

		this.DrugDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addDrugStandartGrid addPrescribeGrid',
			objectPrescribe: 'DrugData',
			frame: false,
			border: false,
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'ЛЕКАРСТВЕННЫЕ НАЗНАЧЕНИЯ',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 52,
				checkOnly: true
			}),
			//requires: [
				//'Ext6.grid.feature.Grouping'
			//],
			//features: [{
				//ftype: 'grouping',
				//startCollapsed: false,
				//groupHeaderTpl: '{columnName}: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})'
				//groupHeaderTpl: '{name}'
			//}],
			/*tools: [{
				//defaultAlign: 'l-l',
				//dock: 'left',
				//floating: true,
				//anchor: 'left top',
				type: 'plus',
				pressed: true,
				callback: function(panel, tool, event) {
					//me.parentPanel.getController().addEvnPrescr(me.addPrescribeWndName);
				}
			}],*/
			columns: [
				{
					dataIndex: 'ActMatters_Name',
					flex: 5,
					renderer: function(val, metaData, record) {
						var s = '';
						s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span>'+record.get('ActMatters_Name')+'</span></div></div>';
						return s;
					}
				}, {
					dataIndex: 'ClsAtc_Name',
					//flex: 1
					width: 254,
					tdCls:'atc-column',
				},
				{
					dataIndex: 'FreqDelivery',
					width: 89,
					/*renderer: function(val, metaData, record) {
						var s = '';
						s += record.get('ActMatters_id') + ' ' + record.get('ActMatters_Name');
						return s;
					},*/
					renderer: function (val, metaData, record) {
						return val ? 1 : '';
					}
				}, {
					dataIndex: 'Replaseability',
					width: 143,
					renderer: function (val, metaData, record) {
						var s = '';
						if (val == 'true')
							s += '<span class="replaseability"></span>';
						return s;
					}
				}
			],
			store: {
				model: 'common.EMK.models.CureStandDrug',
				autoLoad: false,
				folderSort: true,
				//groupField: 'ClsAtc_Name',
				sorters: [
					{
						property: 'FreqDelivery',
						direction: 'DESC'
					},
					//'ClsAtc_Name',
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					extraParams: {
						"objectPrescribe" : this.objectPrescribe
					},
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners: {
				rowclick: 'selectItem'
			}
		});

		this.LabDiagDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,

			columnLines: false,
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			objectPrescribe: 'LabDiagData',
			frame: false,
			border: false,
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'ЛАБОРАТОРНАЯ ДИАГНОСТИКА',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 52,
				checkOnly: true
			}),
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				flex: 5,
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span>'+record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				}
			},{
				text: '',
				dataIndex: 'FreqDelivery',
				renderer: function(val, metaData, record) {
					return val?1:'';
				},
				width: 89
			}, {
				dataIndex: 'Replaseability',
				renderer: function(val, metaData, record) {
					var s = '';
					if(val == 'true')
					s += '<span class="replaseability"></span>';
					return s;
				},
				width: 143
			}],
			store: {
				model: 'common.EMK.models.CureStandLabDiag',
				autoLoad: false,
				folderSort: true,
				sorters: [
					{
						property: 'FreqDelivery',
						direction: 'DESC'
					}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					extraParams: {
						"objectPrescribe" : this.objectPrescribe
					},
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners: {
				rowclick: 'selectItem'
			}
		});

		this.FuncDiagDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			objectPrescribe: 'FuncDiagData',
			frame: false,
			border: false,
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'ИНСТРУМЕНТАЛЬНАЯ ДИАГНОСТИКА',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 52,
				checkOnly: true
			}),
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				flex: 5,
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span>'+record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				}
			},{
				text: '',
				dataIndex: 'FreqDelivery',
				width: 89
			}, {
				dataIndex: 'Replaseability',
				renderer: function(val, metaData, record) {
					var s = '';
					if(val == 'true')
					s += '<span class="replaseability"></span>';
					return s;
				},
				width: 143
			}],
			store: {
				model: 'common.EMK.models.CureStandFuncDiag',
				autoLoad: false,
				folderSort: true,
				sorters: [
					{
						property: 'FreqDelivery',
						direction: 'DESC'
					}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					extraParams: {
						"objectPrescribe" : this.objectPrescribe
					},
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners: {
				rowclick: 'selectItem'
			}
		});

		this.headerTableBar =  Ext6.create('Ext6.toolbar.Toolbar', {
			refId: 'topMainBar',
			userCls: 'gridHeader',
			items: [
				{
					xtype: 'tbtext',
					padding: '0 0 0 34',
					text: 'Назначение',
					flex: 5,
				},{
					xtype: 'tbtext',
					text: 'АТХ - группа',
					//flex: 1
					width: 254
				},{
					xtype: 'tbtext',
					text: 'Частота',
					width: 89
				},{
					xtype: 'tbtext',
					text: 'Заменяемость',
					width: 145
				}]
		});

		Ext6.apply(me, {
			cls: 'evn-prescribe-panel-footer-button',
			buttons: [
				{
					text: 'ПРИМЕНИТЬ',
					handler: function () {
						me.doSave();
					},
					cls: 'button-primary'

				},{
					text: 'ОТМЕНА',
					cls: 'button-secondary',
					handler: 'onCancel'
				},'->',
				{
				xtype: 'tbtext',
				itemId: 'EvnPrescrTBarText',
				reference: 'EvnPrescrTBarText',
				//text: 'Words: 0'
				html: ''
				}],
			tbar: me.headerTableBar,
			items: [{
				layout: 'fit',
				itemId: 'swAddPrescrGridsPanel',
				cls:'evn-prescribe-panel',
				items: [{
					autoHeight: true,
					scrollable: true,
					layout: {
						type: 'vbox',
						align: 'stretch'
						},
					items:[
						me.LabDiagDataGrid,
						me.FuncDiagDataGrid,
						me.DrugDataGrid
					]
				}]
			}]
		});

		this.callParent(arguments);
	}

});

