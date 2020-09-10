/**
* swWhsDocumentRightRecipientListEditWindow - окно редактирования списка правополучателей.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      08.08.2012
*/
sw.Promed.swWhsDocumentRightRecipientListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'WhsDocumentRightRecipientListEditWindow',
	title: lang['vyibor_organizatsii'], 
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
		sw.Promed.swWhsDocumentRightRecipientListEditWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.center();
		this.maximize();		
		this.onSelect = Ext.emptyFn;
		this.endDate = null;

		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}

		if (arguments[0].selection) {
			this.setSelection(arguments[0].selection);
		} else if (arguments[0].owner && arguments[0].owner.getGrid()) {
			 this.setSelection(arguments[0].owner.getGrid().getStore());
		}

		if (!Ext.isEmpty(arguments[0].endDate)) {
			this.endDate = arguments[0].endDate;
		}

		if (arguments[0].OrgType_id && arguments[0].OrgType_id > 0) {
			this.OrgType_id = arguments[0].OrgType_id;
			this.SearchPanel.getForm().findField('OrgType_id').setValue(this.OrgType_id);
			this.SearchPanel.getForm().findField('OrgType_id').disable();
			this.doSearch();
		} else {
			this.OrgType_id = null;
			this.SearchPanel.getForm().findField('OrgType_id').enable();
			this.doSearch(true);
			this.unionStores();
		}
		
		this.getLoadMask().hide();		
	},
	setSelection: function(store) { //копирование данных из внешнего store в selection
		var wnd = this;
		if (!wnd.selection) {
			wnd.selection = new Ext.data.Store({
				proxy: null,
				autoLoad: false
			});		
		} else {
			this.selection.removeAll();
		}

		if (store) {
			store.each(function(r) {
				if (r.get('Org_id') > 0) {
					var rec = new Ext.data.Record.create(wnd.SearchGrid.jsonData['store']);
					var data = new Object();
					Ext.apply(data, r.data);
					data.check = (r.get('state') == 'add' || r.get('state') == 'edit' || r.get('state') == 'saved');
					wnd.selection.insert(wnd.selection.getCount(), new rec(data));
				}
			});
		}
	},
	moveRecord: function(record, direction, sort) {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var store = view_frame.getGrid().getStore();
		var record_count = store.getCount();
		
		var item_index = wnd.selection.findBy(function(r,id) {				
			if(r.get('Org_id') == record.data.Org_id)
				return true;
		});
		
		if (direction == 'selection') { //перемещение в селекшен
			if (item_index < 0)	{ //запись не обнаружена в селекшене
				var rec = new Ext.data.Record.create(view_frame.jsonData['store']);
				var data = new Object();
				Ext.apply(data, record.data);
				data.WhsDocumentRightRecipient_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				data.state = 'add';
				wnd.selection.insert(wnd.selection.getCount(), new rec(data));
			} else { //запись найдена в селекшене
				var rec = wnd.selection.getAt(item_index)
				if (rec.get('state') == 'delete')
					rec.set('state', 'saved');
				rec.set('check', 'true');
			}
		}
		
		if (direction == 'list') {//перемещение в лист
			if (item_index >= 0) { //запись найдена в селекшене
				var rec = wnd.selection.getAt(item_index);
				if (rec.get('state') == 'saved')
					rec.set('state', 'delete');
				if (rec.get('state') == 'add')
					wnd.selection.removeAt(item_index);
			}
		}
		if (sort)
			wnd.setGridSort();
	},
	unionStores: function() {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var store = view_frame.getGrid().getStore();
		
		if ( store.getCount() == 1 && !store.getAt(0).get('Org_id') ) {
			view_frame.removeAll({ addEmptyRecord: false });
		}
		
		wnd.selection.each(function(r) {
			var rec = new Ext.data.Record.create(wnd.SearchGrid.jsonData['store']);
			var data = new Object();
			var item_index = store.findBy(function(rr,id) {
				if(rr.get('Org_id') == r.get('Org_id'))
					return true;
			});
			if (item_index >= 0)
				store.removeAt(item_index);
			Ext.apply(data, r.data);			
			store.insert(0/*store.getCount()*/, new rec(data));
			view_frame.updateRowCount();
		});
		wnd.setGridSort();
	},
	setGridSort: function() {
		this.SearchGrid.getGrid().getStore().sort('check','DESC'/*, ['Org_Name','ASC']*/);
	},
	checkAll: function (check) {
		var wnd = this;
		var store = wnd.SearchGrid.getGrid().getStore();
		store.each(function(r){
			if (check) {
				if (!r.get('check')) {
					r.set('check', true);
					wnd.moveRecord(r, 'selection');
				}
			} else {
				if (r.get('check')) {
					r.set('check', false);
					wnd.moveRecord(r, 'list');
				}
			}
		});
	},
	initComponent: function() {
		var wnd = this;
		
		this.SearchPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			id: 'wdrrleSearchForm',
			labelWidth: 105,
			labelAlign: 'right',
			items: 
			[{
				fieldLabel: lang['naimenovanie'],
				name: 'Org_Name',
				width: 275,
				xtype: 'textfieldpmw',
				//value: 'Березники',
				id: 'wdrrleOrg_Name',
				listeners: {
					'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
								Ext.getCmp('WhsDocumentRightRecipientListEditWindow').doSearch();
								}
							}
				}
			}, {
				fieldLabel: lang['tip_organizatsii'],
				hiddenName: 'OrgType_id',
				width: 275,
				comboSubject: 'OrgType',
				xtype: 'swcommonsprcombo',
				//value: 4,
				id: 'wdrrleOrg_Type',
				listeners: {
					'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
								Ext.getCmp('WhsDocumentRightRecipientListEditWindow').doSearch();
								}
							}
				}
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					items: [{
						xtype: 'button',
						text: lang['poisk'],
						minWidth: 80,
						id: 'wdrrleButtonSetFilter',
						handler: function () {
							Ext.getCmp('WhsDocumentRightRecipientListEditWindow').doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						minWidth: 80,
						id: 'wdrrleButtonUnSetFilter',
						handler: function () {
							Ext.getCmp('WhsDocumentRightRecipientListEditWindow').doSearch(true);
						}
					}]
				}/*, {
					layout: 'form',
					bodyStyle:'padding-left:5px;',
					items: [{
						xtype: 'button',
						text: 'Show selection',						
						minWidth: 80,
						id: 'wdrrleButtonUnSetFilter1',
						handler: function () {
							swalert(wnd.selection.data.items);
						}
					}]
				}, {
					layout: 'form',
					bodyStyle:'padding-left:5px;',
					items: [{
						xtype: 'button',
						text: 'unionStores',
						minWidth: 80,
						id: 'wdrrleButtonUnSetFilter2',
						handler: function () {
							wnd.unionStores();
						}
					}]
				}*/]
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', handler: function()
					{
						getWnd('swOrgEditWindow').show({
							action: 'add',
							orgType: 'all'
						});
					}
				},
				{name:'action_edit', handler: function()
					{
						getWnd('swOrgEditWindow').show({
							action: 'edit',
							Org_id: wnd.SearchGrid.getGrid().getSelectionModel().getSelected().get('Org_id'),
							orgType: 'all'
						});
					}
				},
				{name:'action_view', handler: function()
					{
						getWnd('swOrgEditWindow').show({
							action: 'view',
							Org_id: wnd.SearchGrid.getGrid().getSelectionModel().getSelected().get('Org_id'),
							orgType: 'all'
						});
					}
				},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Org&m=getOrgForContragents',
			height: 180,
			object: 'Org',
			editformclassname: 'swOrgEditForm',
			id: 'wdrrleWhsDocumentSupplyGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'Org_id', type: 'int', header: 'ID', key: true},				
				{name: 'state', header: 'state', hidden: true},
				{name: 'check', header: '<input type="checkbox" id="checkAll_checkbox" onClick="getWnd(\'swWhsDocumentRightRecipientListEditWindow\').checkAll(this.checked);">', type: 'checkcolumn', width: 35, sortable: false, hideable: false},
				{name: 'Org_Name', id: 'autoexpand', header: lang['polnoe_naimenovanie']},
				{name: 'struct_level', header: lang['strukturnyiy_uroven'], width: 200},				
				{name: 'PAddress_Address', header: lang['adres'], width: 400},
				{name: 'Contragent_Code', type: 'string', header: lang['kontragent'], hidden: true},
				{name: 'WhsDocumentRightRecipient_begDate', type: 'date', header: lang['data_nachala_deystviya'], hidden: true},
				{name: 'WhsDocumentRightRecipient_endDate', type: 'date', header: lang['data_okonchaniya_deystviya'], hidden: true}
			],			
			title: lang['spisok_organizatsiy'],
			toolbar: true,
			onLoadData: function() {
				wnd.unionStores();
			},
			onDblClick: function(grid, rowIdx, object) {
				var record = grid.getStore().getAt(rowIdx);
				if ( record ) {
					record.set('check', !record.get('check'));
					wnd.moveRecord(record, (record.get('check') ? 'selection' : 'list'), true);
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
				handler: function() 
				{
					this.ownerCt.onSelect(this.ownerCt.selection);
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
			[this.SearchPanel,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				id: 'wdsvRightPanel',
				items: [wnd.SearchGrid]
			}]
		});
		sw.Promed.swWhsDocumentRightRecipientListEditWindow.superclass.initComponent.apply(this, arguments);
	},	
	doSearch: function(clear) {
		var wnd = this;
		var form = wnd.SearchPanel.getForm();
		
		if (clear) {
			form.findField('Org_Name').setValue(null);
			form.findField('OrgType_id').setValue(wnd.OrgType_id > 0 ? wnd.OrgType_id : null);
		}
			
		var OrgName = form.findField('Org_Name').getValue();
		var OrgType = form.findField('OrgType_id').getValue();
		if (OrgName != '' || OrgType > 0) {
			var filters = {Nick: null, Name: OrgName, Type: OrgType, endDate: this.endDate, start: 0, limit: 1000, mode: null};
			wnd.SearchGrid.loadData({
				globalFilters: filters
			});
		} else {
			wnd.SearchGrid.removeAll();
			wnd.unionStores();
		}
						
		document.getElementById('checkAll_checkbox').checked = false;
	}
});