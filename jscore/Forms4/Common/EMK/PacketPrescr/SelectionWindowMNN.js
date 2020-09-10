/**
 * SelectionWindowMNN - Окно выбора МНН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.PacketPrescr.SelectionWindowMNN', {
	alias: 'widget.selectionWindowMNN',
	modal: true,
	closeAction: 'hide',
	
	extend: Ext6.window.Window,
	layout: 'border',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	title: 'Окно выбора МНН',
	width: 500,
	cbFn: false,
	Actmatters_id: null,
	listeners:{
		'hide':function(win){
			if(!win.selected_id && win.cbFn) win.cbFn(false);
		}
	},
	show: function() {
		var win = this;
		this.callParent(arguments);
		this.doReset();
		
		if(arguments && arguments.length > 0){
			if(arguments[0].Actmatters_id) win.Actmatters_id = arguments[0].Actmatters_id;
			if(arguments[0].DrugComplexMnn_id) win.DrugComplexMnn_id = arguments[0].DrugComplexMnn_id;
			if(arguments[0].cbFn && typeof arguments[0].cbFn == 'function') win.cbFn = arguments[0].cbFn;
		}
		win.getEl().el.dom.style.boxShadow = 'rgb(136, 136, 136) 10px 10px 6px';
		win.loadGrid();
	},
	doReset: function(){
		var win = this;
		win.Actmatters_id = null; 
		win.DrugComplexMnn_id = null;
		win.selected_id = null;
		win.cbFn = null;
		win.DetailGrid.getStore().removeAll();
	},
	loadGrid: function(){
		var win = this;
		if(!win.Actmatters_id) return false;
		var params = {Actmatters_id: win.Actmatters_id};
		win.mask.show();
		win.DetailGrid.getStore().load({
			params: params,
			callback: function(){
				this.mask.hide();
			}.bind(win)
		});
	},
	getSelectedParams: function(){
		var grid = this.DetailGrid;
		if( grid.getSelectionModel().hasSelection() ){
			var rec = grid.getSelectionModel().getSelection()[0];
			if(rec && rec.get('DrugComplexMnn_id') && this.cbFn){
				var id = rec.get('DrugComplexMnn_id');
				this.selected_id = id;
				this.cbFn({DrugComplexMnn_id: id});
				this.hide();
				return true;
			}
		}

		Ext6.Msg.alert('Ошибка', 'Не выбрана запись !!!');
	},
    initComponent: function() {
        var win = this;
		
		win.mask = new Ext6.LoadMask({
			msg: 'Подождите...',
			target: this
		});
		
		win.MNNGridStore = Ext6.create('Ext.data.Store', {
			fields: [
				{ name: 'DrugComplexMnn_id', type: 'int' },
				{ name: 'Drug_Name', type: 'string' },
				{ name: 'DrugComplexMnnName_Name', type: 'string' },
				{ name: 'LatName', type: 'string' },
				{ name: 'DrugComplexMnnName_Name', type: 'int' },
				{ name: 'LatName', type: 'string' },
				{ name: 'Actmatters_id', type: 'int' }
			],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=CureStandart&m=loadMNNbyACTMATTERS',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: {
				property: 'DrugComplexMnn_id',
				direction: 'ASC'
			},
			listeners: {
				load: function() {
					//...
				}
			}
		});
		
		win.DetailGrid = new Ext6.grid.Panel({
			columns: [
				{text: 'ID', width: 100, dataIndex: 'DrugComplexMnn_id'},
				{text: 'МНН', flex: 1, minWidth: 150, dataIndex: 'Drug_Name'},
				{text: 'Назвние', width: 100, dataIndex: 'LatName'}
			],
			features: [ ],
			region: 'center',
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						//...
					}
				}
			},
			store: win.MNNGridStore,
			viewConfig: {
				getRowClass: function (record, rowIndex, rowParams, store) {
					var cls = '';
					if (this.DrugComplexMnn_id && record.get('DrugComplexMnn_id') == this.DrugComplexMnn_id) {
						cls = cls + 'x-grid-rowbold x-grid-rowbacklightgreen x-grid-checkbox-disabled ';
					}
					return cls;
				}.createDelegate(this)
			},
			listeners: {
				itemdblclick: function (cmp, record) {
					this.getSelectedParams();
					/*
					var id = record.get('DrugComplexMnn_id');
					if(id && this.cbFn) {
						this.selected_id = id;
						this.cbFn({DrugComplexMnn_id: id});
						this.hide();
					}
					*/
				}.createDelegate(win)
			}
		});

		Ext6.apply(win, {
			items: [
				win.DetailGrid
			],
			buttons: [
				{
					handler:function () {
						this.getSelectedParams();
					}.createDelegate(this),
					text: 'Выбрать'
				},
				'->',
				{
					handler:function () {
						win.hide();
					},
					text: BTN_FRMCLOSE
				}
			]
		});

		this.callParent(arguments);
    }
});


