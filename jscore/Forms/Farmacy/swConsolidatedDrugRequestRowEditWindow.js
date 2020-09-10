/**
* swConsolidatedDrugRequestRowEditWindow - окно редактирования заявки на закуп МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      11.2012
* @comment      
*/
sw.Promed.swConsolidatedDrugRequestRowEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['pozitsiya_spetsifikatsii_svodnoy_zayavki_redaktirovanie_zakupa'],
	layout: 'border',
	id: 'ConsolidatedDrugRequestRowEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doCalculate: function() {
		var wnd = this;
		var panel = wnd.InformationPanel;

		var count = 0; //Количество заявлено
		var sum = 0; //Сумма
		var buy_count = 0; //Количество к закупу
		var buy_sum = 0; //Сумма закупа

		panel.clearData();

		wnd.DataGrid.clearFilter();
		wnd.DataGrid.getGrid().getStore().each(function(item) {
			if (item.get('DrugRequestRow_Kolvo') > 0)
				count += item.get('DrugRequestRow_Kolvo')*1;
			if (item.get('DrugRequestRow_Summa') > 0)
				sum += item.get('DrugRequestRow_Summa')*1;
			if (item.get('DrugRequestRow_KolDrugBuy') > 0)
				buy_count += item.get('DrugRequestRow_KolDrugBuy')*1;
			if (item.get('DrugRequestRow_SumBuy') > 0)
				buy_sum += item.get('DrugRequestRow_SumBuy')*1;
		});
		wnd.DataGrid.setFilter();

		var drug_inf = "";

		if (!Ext.isEmpty(wnd.Atc_Name)) {
			drug_inf += "<tr><td>АТХ: "+wnd.Atc_Name+"</td></tr>";
		}
		if (!Ext.isEmpty(wnd.ClsDrugForms_Name)) {
			drug_inf += "<tr><td>Лекарственная форма: "+wnd.ClsDrugForms_Name+"</td></tr>";
		}
		if (!Ext.isEmpty(wnd.DrugComplexMnnDose_Name)) {
			drug_inf += "<tr><td>Дозировка: "+wnd.DrugComplexMnnDose_Name+"</td></tr>";
		}
		if (!Ext.isEmpty(wnd.DrugComplexMnnFas_Name)) {
			drug_inf += "<tr><td>Фасовка: "+wnd.DrugComplexMnnFas_Name+"</td></tr>";
		}

		panel.setData('request_name', wnd.DrugRequest_Name);
		panel.setData('finance_name', wnd.DrugFinance_Name);
		panel.setData('drug_name', wnd.DrugComplexMnnName_Name+(!Ext.isEmpty(wnd.TRADENAMES_Name) ? ', '+wnd.TRADENAMES_Name : ''));
		panel.setData('price', wnd.DrugRequestPurchaseSpec_Price);
		panel.setData('drug_inf', drug_inf);
		panel.setData('count', count);
		panel.setData('sum', sum);
		panel.setData('buy_count', buy_count);
		panel.setData('buy_sum', buy_sum);
		panel.showData();
	},
	loadGrid: function() {
		var wnd = this;
		var params = new Object();
		params.DrugRequestPurchaseSpec_id = wnd.DrugRequestPurchaseSpec_id;
		
		wnd.DataGrid.removeAll();
		wnd.DataGrid.loadData({
			globalFilters: params,
			callback: function(){
				wnd.doCalculate();
			}
		});
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('ConsolidatedDrugRequestRowEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		//wnd.getLoadMask('Подождите, идет сохранение...').show();

		var params = new Object();
		params.DrugRequestPurchaseSpec_id = wnd.DrugRequestPurchaseSpec_id;
		params.JsonData = wnd.DataGrid.getJSONChangedData();

		//если данные не менялись прекращаем выполненеие сохранения
		if (params.JsonData == '') {
			wnd.callback(wnd.owner);
			wnd.hide();
			return;
		}

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					wnd.callback(wnd.owner);
					wnd.hide();
				}
			}
		});
	},
	enableEdit: function(enable) {
		if (!this.checkRole('edit')) {
			enable = false;
		}

		this.hideEditButtons(enable);

		if (this.onEnableEdit && typeof this.onEnableEdit == 'function') {
			this.onEnableEdit(enable);
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swConsolidatedDrugRequestRowEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestPurchaseSpec_id = null;
		this.DrugRequest_Name = null;
		this.DrugFinance_Name = null;
		this.DrugComplexMnnName_Name = null;
		this.TRADENAMES_Name = null;
		this.DrugRequestPurchaseSpec_Price = null;
		this.Atc_Name = null;
		this.ClsDrugForms_Name = null;
		this.DrugComplexMnnDose_Name = null;
		this.DrugComplexMnnFas_Name = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestPurchaseSpec_id ) {
			this.DrugRequestPurchaseSpec_id = arguments[0].DrugRequestPurchaseSpec_id;
		}
		if ( arguments[0].DrugRequest_Name ) {
			this.DrugRequest_Name = arguments[0].DrugRequest_Name;
		}
		if ( arguments[0].DrugFinance_Name ) {
			this.DrugFinance_Name = arguments[0].DrugFinance_Name;
		}
		if ( arguments[0].DrugComplexMnnName_Name ) {
			this.DrugComplexMnnName_Name = arguments[0].DrugComplexMnnName_Name;
		}
		if ( arguments[0].TRADENAMES_Name ) {
			this.TRADENAMES_Name = arguments[0].TRADENAMES_Name;
		}
		if ( arguments[0].DrugRequestPurchaseSpec_Price ) {
			this.DrugRequestPurchaseSpec_Price = arguments[0].DrugRequestPurchaseSpec_Price;
		}
		if ( arguments[0].Atc_Name ) {
			this.Atc_Name = arguments[0].Atc_Name;
		}
		if ( arguments[0].ClsDrugForms_Name ) {
			this.ClsDrugForms_Name = arguments[0].ClsDrugForms_Name;
		}
		if ( arguments[0].DrugComplexMnnDose_Name ) {
			this.DrugComplexMnnDose_Name = arguments[0].DrugComplexMnnDose_Name;
		}
		if ( arguments[0].DrugComplexMnnFas_Name ) {
			this.DrugComplexMnnFas_Name = arguments[0].DrugComplexMnnFas_Name;
		}
		this.form.reset();
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (wnd.action) {
			case 'edit':
			case 'view':
				wnd.setTitle(arguments[0].action == 'edit'
					?lang['pozitsiya_spetsifikatsii_svodnoy_zayavki_redaktirovanie_zakupa']
					:lang['pozitsiya_spetsifikatsii_svodnoy_zayavki_prosmotr_zakupa']
				);
				wnd.enableEdit(wnd.action == 'edit');
				wnd.loadGrid();
				wnd.InformationPanel.showData();
				loadMask.hide();
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;		

		this.DataGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadConsolidatedDrugRequestRowList',
			height: 180,
			region: 'center',
			object: 'ConsolidatedDrugRequestRow',
			id: wnd.id + 'Grid',
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},				
				{name: 'KLAreaStat_List', type: 'string', hidden: true},
				{name: 'Lpu_id', type: 'string', hidden: true},
				{name: 'Lpu_Name', type: 'string', header: lang['mo'], width: 120, id: 'autoexpand'},
				{name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 200},
				{name: 'Person_Fio', type: 'string', header: lang['patsient'], width: 200, hidden: true},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo_zayavleno'], width: 120},
				{name: 'DrugRequestRow_Summa', type: 'float', header: lang['summa_zayavki'], width: 120},
				{name: 'DrugRequestRow_KolDrugBuy', type: 'float', header: lang['kol-vo_k_zakupu'], editor: new Ext.form.NumberField(), width: 120},
				{name: 'DrugRequestRow_SumBuy', type: 'float', header: lang['summa_zakupa'], width: 120},
				{name: 'state', type: 'string', hidden: true}
			],
			paging: false,
			title: false,
			toolbar: false,
			contextmenu: false,
			editable: true,
			saveAtOnce: false,
			onBeforeEdit: function(o) {
				return (wnd.action != 'view');
			},
			onAfterEdit: function(o) {
				var price = 0;
				if (o.record.get('DrugRequestRow_Kolvo') > 0 && o.record.get('DrugRequestRow_Summa') > 0) {
					price = o.record.get('DrugRequestRow_Summa')/o.record.get('DrugRequestRow_Kolvo');
					o.record.set('DrugRequestRow_SumBuy', price*o.record.get('DrugRequestRow_KolDrugBuy'));
				}
				o.record.set('state', 'edit');
				wnd.doCalculate();
			},
			getChangedData: function(){ //возвращает измненные строки
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					//if (record.get('state') == 'edit')
					data.push({
						DrugRequestRow_id: record.get('DrugRequestRow_id'),
						DrugRequestRow_KolDrugBuy: record.get('DrugRequestRow_KolDrugBuy'),
						DrugRequestRow_SumBuy: record.get('DrugRequestRow_SumBuy'),
						state: record.get('state')
					});
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() {
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() {
				var store = this.getGrid().getStore();
				
				var terr = wnd.form.findField('KLAreaStat_id').getValue();
				var lpu = wnd.form.findField('Lpu_id').getValue();

				store.filterBy(function(record){
					return (
						(terr <= 0 || record.get('KLAreaStat_List').indexOf(','+terr+',') > -1) &&
						(lpu <= 0 || record.get('Lpu_id') == lpu)
					);
				});
			},
			doSearch: function(clear) {
				var store = wnd.DataGrid.getGrid().getStore();
				if (clear) {
					wnd.form.reset();
					this.clearFilter();
				} else {
					this.setFilter();
				}
				wnd.doCalculate();
			}
		});

		var form = new sw.Promed.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0;',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			title: lang['filtryi'],
			url:'/?c=MzDrugRequest&m=saveDrugRequestRowBuyDataFromJSON',
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						codeField: 'KLAreaStat_Code',
						disabled: false,
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: lang['territoriya'],
						hiddenName: 'KLAreaStat_id',
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLAreaStat_id', type: 'int' },
								{ name: 'KLAreaStat_Code', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' },
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLRGN_id', type: 'int' },
								{ name: 'KLSubRGN_id', type: 'int' },
								{ name: 'KLCity_id', type: 'int' },
								{ name: 'KLTown_id', type: 'int' }
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
							'</div></tpl>'
						),
						valueField: 'KLAreaStat_id',
						width: 250,
						xtype: 'swbaselocalcombo'
					}]
				}, {
					layout: 'form',
					items: [{
						hiddenName: 'Lpu_id',
						fieldLabel: lang['mo'],
						width: 250,
						xtype: 'swlpucombo'
					}]
				}, {
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-left:15px;',
					items: [{
						xtype: 'button',
						text: lang['poisk'],
						minWidth: 80,
						handler: function () {
							wnd.DataGrid.doSearch();
						}
					}]
				}, {
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-left:5px;',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						minWidth: 80,
						handler: function () {
							wnd.DataGrid.doSearch(true);
						}
					}]
				}]
			}]
		});
		
		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			bodyBorder: false,
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: this,
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},			
			showData: function() {
				var html = this.html_tpl;				
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');				
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});
		
		var tpl = "";
		tpl += "<table style='margin: 5px;'>";
		tpl += "<tr><td>{request_name} {finance_name}</td></tr>";
		tpl += "<tr><td>{drug_name}, цена в заявке {price} руб.</td></tr>";
		tpl += "{drug_inf}";
		tpl += "<tr><td>В сводной заявке - {count} уп. на {sum} руб.</td></tr>";
		tpl += "<tr><td>К закупу для МО - {buy_count} уп. на {buy_sum} руб.</td></tr>";
		tpl += "</table>";
		this.InformationPanel.setTpl(tpl);
		
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
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
			items:[{
				autoHeight: true,
				region: 'north',
				layout: 'form',
				items:[
					this.InformationPanel,
					form
				]
			},
			this.DataGrid]
		});
		sw.Promed.swConsolidatedDrugRequestRowEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});