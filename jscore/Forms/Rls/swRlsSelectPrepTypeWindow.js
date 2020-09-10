/**
* Справочник РЛС: добавление
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      14.11.2011
*/

sw.Promed.swRlsSelectPrepTypeWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['spravochnik_medikamentov_dobavlenie'],
	modal: true,
	height: 181,
	width: 500,
	shim: false,
	resizable: false,
	plain: true,
	onSelect: Ext.emptyFn,
	layout: 'border',
	buttonAlign: "right",
	objectName: 'swRlsSelectPrepTypeWindow',
	closeAction: 'hide',
	id: 'swRlsSelectPrepTypeWindow',
	objectSrc: '/jscore/Forms/Rls/swRlsSelectPrepTypeWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSelect();
			},
			iconCls: 'ok16',
			text: lang['vyibrat']
		},
		'-',
		{
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		
	},
	
	show: function()
	{
		sw.Promed.swRlsSelectPrepTypeWindow.superclass.show.apply(this, arguments);
		
		if(arguments[0].onSelect){
			this.onSelect = arguments[0].onSelect;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		if(arguments[0].mode == 'old'){
			var data = [
				[2, lang['dobavit_lekarstvennoe_sredstvo']],
				[3, lang['dobavit_meditsinskiy_tovar']]
			];
		} else {
			var data = [
				[2, 'Добавить лекарственное средство из ГРЛС'],
				[3, lang['dobavit_meditsinskiy_tovar']],
				[4, 'Добавить экстемпоральное лекарственное средство'],
				[5, 'Добавить лекарственное средство, не прошедшее государственную регистрацию'],
				[6, 'Добавить лекарственное средство из регистра таможенного союза и ЕЭП']
			];
		}
		
		this.CommonGrid.getStore().loadData(data, false);
		
		this.center();
	},
	
	doSelect: function()
	{
		var rec = this.CommonGrid.getSelectionModel().getSelected();
		if(!rec) return false;
		this.onSelect(rec.data);
		this.hide();
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.gridStore = new Ext.data.Store({
			reader: new Ext.data.ArrayReader({
				idIndex: 0
			}, [
				{mapping: 0, name: 'PrepType_id'},
				{mapping: 1, name: 'PrepType_Name'}
			])
		});
		
		this.CommonGrid = new Ext.grid.GridPanel({
			autoScroll: true,
			autoExpandMin: 384,
			autoHeight: true,
			region: 'center',
			hideHeaders: true,
			autoExpandColumn: 'autoexpand',
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: false
				},
				columns: [
					{key: true, hidden: true, dataIndex: 'PrepType_id'},
					{dataIndex: 'PrepType_Name', id: 'autoexpand'}
				]
			}),
			store: this.gridStore
		});
		
		this.CommonGrid.on('rowdblclick', function(){
			this.doSelect();
		}.createDelegate(this));
		
		this.CommonGrid.getStore().on('load', function(){
			var sm = cur_win.CommonGrid.getSelectionModel();
			sm.selectFirstRow();
			// Фикс для первого открытия=)
			if(!cur_win.CommonGrid.getView().firstRowCls.match(/selected/))
				cur_win.CommonGrid.getView().firstRowCls += ' x-grid3-row-selected ';
		});
		
		Ext.apply(this,	{
			items: [this.CommonGrid]
		});
		sw.Promed.swRlsSelectPrepTypeWindow.superclass.initComponent.apply(this, arguments);
	}
});