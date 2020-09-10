/**
* swWhsDocumentUcInventOrderStorageSelectWindow - окно для выбора списка складов при формировании инв. ведомостей.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov Rustam
* @version      10.2014
*/
sw.Promed.swWhsDocumentUcInventOrderStorageSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentUcInventOrderStorageSelectWindow',
	title: lang['vyibor_sklada'],
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	selection: null,
	OrgType_id: null,
	onSelect: Ext.emptyFn,
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},	
	show: function() {
		sw.Promed.swWhsDocumentUcInventOrderStorageSelectWindow.superclass.show.apply(this, arguments);
		this.params = new Object();
		this.onSelect = Ext.emptyFn;
		this.ARMType = null;
		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}
		if (arguments[0].params) {
			this.params = arguments[0].params;
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (getRegionNick() == 'krym' && ((getGlobalOptions().orgtype.inlist(['farm','reg_dlo'])) || this.ARMType == 'adminllo')) {
			this.FilterPanel.show();
		} else {
			this.FilterPanel.hide();
		}
		this.doLayout();

		this.SearchGrid.loadData({params: this.params, globalFilters: this.params});
		document.getElementById('wduioss_checkAll_checkbox').checked = false;
	},
	checkOne: function(storage_id) {
		var wnd = this;
		var grid = wnd.SearchGrid.getGrid();
		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('Storage_id') == storage_id; }));
		if (record) {
			record.set('check', !record.get('check'));
			record.commit();
		}
	},
	checkAll: function (check) {
		var wnd = this;
		var store = wnd.SearchGrid.getGrid().getStore();
		store.each(function(r){
			if (check) {
				if (!r.get('check')) {
					r.set('check', true);
				}
			} else {
				if (r.get('check')) {
					r.set('check', false);
				}
			}
			r.commit();
		});
	},
	getSelectedStores: function() {
		var result = new Array();

		this.SearchGrid.getGrid().getStore().each(function(record) {
			if (record.get('check')) {
				result.push((record.get('Storage_id')+'|'+record.get('StorageZone_id')));
			}
		})

		return result;
	},
	initComponent: function() {
		var wnd = this;

		//Фильтры
		this.FilterFieldsPanel = new sw.Promed.Panel({
			region: 'north',
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 300,
			border: false,
			frame: true,
			height:40,
			items: [
				{
					fieldLabel: 'Выборочная инвентаризация по местам хранения',
					xtype: 'checkbox',
					anchor: '100%',
					name: 'withStorageZones',
					listeners: {
						check:function(c,value){
							if(value){
								this.params.withStorageZones = 1;
							} else {
								this.params.withStorageZones = null;
							}
							this.SearchGrid.setColumnHidden('StorageZone_Name',!value);
							this.SearchGrid.loadData({params: this.params, globalFilters: this.params});
						}.createDelegate(this)
					}
				}
			]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterFieldsPanel
			]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentUcInvent&m=LoadStorageList',
			height: 180,
			object: 'Storage',
			id: 'wduiossWhsDocumentSupplyGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'GridKey', type: 'int', header: 'ID', key: true},	
				{name: 'Storage_id', type: 'int', hidden: true},				
				{name: 'state', header: 'state', hidden: true},
				{
					name: 'false_check',
					width: 35,
					sortable: false,
					hideable: false,
					renderer: function(v, p, record) {
						var storage_id = record.get('GridKey');
						return '<input type="checkbox" value="'+storage_id+'"'+(record.get('check') ? ' checked="checked"' : '')+'" onClick="getWnd(\'swWhsDocumentUcInventOrderStorageSelectWindow\').checkOne(this.value);">';
					},
					header: '<input type="checkbox" id="wduioss_checkAll_checkbox" onClick="getWnd(\'swWhsDocumentUcInventOrderStorageSelectWindow\').checkAll(this.checked);">'
				},
				{name: 'check', type: 'checkcolumn', hidden: true},
				{name: 'Org_Nick', type: 'string', header: lang['organizatsiya'], width: 250},
				{name: 'Storage_Name', type: 'string', header: lang['sklad'], width: 250, id: 'autoexpand'},
				{name: 'StorageZone_Name', type: 'string', header: 'Место хранения', width: 200, hidden:true},
				{name: 'PAddress_Address', type: 'string', header: lang['territoriya_obslujivaniya'], width: 250},
				{name: 'StorageZone_id', type: 'int', hidden: true}
			],			
			title: null,
			toolbar: false,
			contextmenu: false,
			onDblClick: function(grid, rowIdx, object) {
				var record = grid.getStore().getAt(rowIdx);
				if ( record ) {
					record.set('check', !record.get('check'));
					record.commit();
				}
			},
			updateRowCount: function() { //так как инсертим данные мы не через loadData, необходима такая корректировка данных
				var store = this.getGrid().getStore(); 
				var cnt = store.getCount();
				if (cnt == 1 && this.isEmpty())
					cnt = 0;
				this.rowCount = cnt;
			}
		});
		
		Ext.apply(this, {
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				handler: function() {
					var s_arr = this.ownerCt.getSelectedStores();
					if (s_arr.length > 0) {
						this.ownerCt.onSelect({
							Storage_List: s_arr
						});
						this.ownerCt.hide();
					}
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				id: 'wdsvRightPanel',
				items: [
					wnd.FilterPanel,
					wnd.SearchGrid
				]
			}]
		});
		sw.Promed.swWhsDocumentUcInventOrderStorageSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});