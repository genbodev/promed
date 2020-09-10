/**
* swWhsDocumentOrderAllocationDrugEditWindow - окно редактирования позиции сводной разнарядки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      06.2014
* @comment      
*/
sw.Promed.swWhsDocumentOrderAllocationDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Сводная разнарядка: Добавление',
	layout: 'border',
	id: 'WhsDocumentOrderAllocationDrugEditWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 150,
	resizable: false,
	maximizable: false,
	maximized: true,
	doSelect:  function() {
		var data = this.getChecked();
        this.onSelect(data);
		this.hide();
		return true;		
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentOrderAllocationDrugEditWindow.superclass.show.apply(this, arguments);		
		this.onSelect = Ext.emptyFn;
		this.DrugArray = new Array();
		this.DrugFinance_id = null;
		this.WhsDocumentCostItemType_id = null;

        if (!arguments[0]) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
			this.onSelect = arguments[0].onSelect;
		}
		if (arguments[0].DrugArray && arguments[0].DrugArray.length > 0) {
			this.DrugArray = arguments[0].DrugArray;
		}
		if (arguments[0].DrugFinance_id && arguments[0].DrugFinance_id > 0) {
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		}
		if (arguments[0].WhsDocumentCostItemType_id && arguments[0].WhsDocumentCostItemType_id > 0) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}

		var params = new Object();
		params.DrugFinance_id = this.DrugFinance_id;
		params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;

		this.DrugGrid.loadData({
			globalFilters: params,
			callback: function() {
				var store = wnd.DrugGrid.getGrid().getStore();
				for(var i = 0; i < wnd.DrugArray.length; i++) {
					store.each(function(record) {
						if (record.get('Drug_id') == wnd.DrugArray[i].Drug_id && record.get('WhsDocumentUc_pid') == wnd.DrugArray[i].WhsDocumentUc_pid && record.get('WhsDocumentOrderAllocationDrug_Price') == wnd.DrugArray[i].WhsDocumentOrderAllocationDrug_Price) {
							store.remove(record);
							return false;
						}
					});
				}
                if (store.getCount() == 0) { //для рендеринга горизонтального скролла
                    wnd.DrugGrid.removeAll();
                }
			}
		});
	},
	checkAll: function (check) {
		var wnd = this;
		var store = wnd.DrugGrid.getGrid().getStore();

		store.each(function(record){
            if (!Ext.isEmpty(record.get('DrugOstatRegistry_id'))) {
                if (check) {
                    if (!record.get('check')) {
                        record.set('check', true);
                        record.commit();
                    }
                } else {
                    if (record.get('check')) {
                        record.set('check', false);
                        record.commit();
                    }
                }
            }
		});
	},
	checkOne: function(id) {
		var wnd = this;
		var grid = wnd.DrugGrid.getGrid();

		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('DrugOstatRegistry_id') == id; }));
		if (record && !Ext.isEmpty(record.get('DrugOstatRegistry_id'))) {
			record.set('check', !record.get('check'));
			record.commit();
		}
	},
	getChecked: function () {
		var arr = new Array();

		this.DrugGrid.getGrid().getStore().each(function(record){
			if (record.get('check') && !Ext.isEmpty(record.get('DrugOstatRegistry_id'))) {
				arr.push({
					WhsDocumentUc_pid: record.get('WhsDocumentUc_pid'),
					WhsDocumentUc_Name: record.get('WhsDocumentUc_Name'),
					Supplier_Name: record.get('Supplier_Name'),
					Drug_id: record.get('Drug_id'),
					Okei_id: record.get('Okei_id'),
                    Actmatters_RusName: record.get('Actmatters_RusName'),
					Tradenames_Name: record.get('Tradenames_Name'),
                    DrugForm_Name: record.get('DrugForm_Name'),
                    Drug_Dose: record.get('Drug_Dose'),
                    Drug_Fas: record.get('Drug_Fas'),
					Kolvo: record.get('Kolvo'),
					WhsDocumentOrderAllocationDrug_Price: record.get('WhsDocumentOrderAllocationDrug_Price'),
                    Reg_Num: record.get('Reg_Num'),
                    Reg_Firm: record.get('Reg_Firm'),
                    Reg_Country: record.get('Reg_Country'),
                    Reg_Period: record.get('Reg_Period'),
                    Reg_ReRegDate: record.get('Reg_ReRegDate')
				});
			}
		});

		return arr;
	},
	initComponent: function() {
		var wnd = this;

		this.DrugGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadDrugOstatRegistryList',
			height: 180,
			object: 'DrugOstatRegistry',
			id: 'wdoadeDrugOstatRegistryGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'DrugOstatRegistry_id', type: 'int', header: 'ID', key: true},
				{
					name: 'false_check',
					width: 35,
					sortable: false,
					hideable: false,
					renderer: function(v, p, record) {
						var id = record.get('DrugOstatRegistry_id');
						return id > 0 ? '<input type="checkbox" value="'+id+'"'+(record.get('check') ? ' checked="checked"' : '')+'" onClick="getWnd(\'swWhsDocumentOrderAllocationDrugEditWindow\').checkOne(this.value);">' : '';
					},
					header: '<input type="checkbox" id="wdoade_checkAll_checkbox" onClick="getWnd(\'swWhsDocumentOrderAllocationDrugEditWindow\').checkAll(this.checked);">'
				},
				{name: 'check', type: 'checkcolumn', hidden: true},
				{name: 'WhsDocumentUc_pid', type: 'int', hidden: true},
				{name: 'WhsDocumentUc_Name', type: 'string', header: '№ ГК', width: 100},
				{name: 'Supplier_Name', type: 'string', header: 'Поставщик', width: 150},
				{name: 'Org_Name', type: 'string', header: 'Организация', width: 150},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'Okei_id', type: 'int', hidden: true},
                {name: 'Actmatters_RusName', type: 'string', header: 'МНН', width: 100, hidden: true},
                {name: 'Tradenames_Name', type: 'string', header: 'Торговое наименование', width: 100, id: 'autoexpand' },
                {name: 'DrugForm_Name', type: 'string', header: 'Форма выпуска', width: 100},
                {name: 'Drug_Dose', type: 'string', header: 'Дозировка', width: 100},
                {name: 'Drug_Fas', type: 'string', header: 'Фасовка', width: 100},
				{name: 'Kolvo', type: 'string', header: 'Кол-во.', width: 100},
				{name: 'WhsDocumentOrderAllocationDrug_Price', type: 'string', header: 'Цена', width: 100},
				{name: 'DrugFinance_Name', type: 'string', header: 'Источник финансирования', width: 150},
				{name: 'WhsDocumentCostItemType_Name', type: 'string', header: 'Статья расхода', width: 150},
                {name: 'Reg_Num', type: 'string', header: 'РУ', width: 100},
                {name: 'Reg_Firm', type: 'string', header: 'Держатель/Владелец РУ', width: 100},
                {name: 'Reg_Country', type: 'string', header: 'Страна держателя/владельца РУ', width: 100},
                {name: 'Reg_Period', type: 'string', header: 'Период действия РУ', width: 100},
                {name: 'Reg_ReRegDate', type: 'string', header: 'Дата переоформления РУ', width: 100}
			],
			title: false,
			toolbar: false,
			contextmenu: false
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
				text: 'Выбрать'
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.DrugGrid]
		});
		sw.Promed.swWhsDocumentOrderAllocationDrugEditWindow.superclass.initComponent.apply(this, arguments);
	}
});