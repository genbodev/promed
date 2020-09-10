/**
* swWhsDocumentOrderAllocationEditWindow - окно редактирования "Распоряжение на выдачу разнарядки для МО на выписку рецептов"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Alexandr Chebukin
* @version      27.02.2013
* @comment      
*/
sw.Promed.swWhsDocumentOrderAllocationEditWindow = Ext.extend(sw.Promed.BaseForm, { //getWnd('swWhsDocumentOrderAllocationEditWindow').show();
	autoHeight: false,
	title: 'Распоряжение на выдачу разнарядки для МО на выписку рецептов',
	layout: 'border',
	id: 'WhsDocumentOrderAllocationEditWindow',
	modal: true,
	//shim: false,
	width: 950,
	height: 465,
	resizable: false,
	maximizable: true,
	maximized: true,
	firstTabIndex: 10000,
	plain: true,
	debug_mode: false, //только для разработки
	demand: null,
	delivery_graph: null,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	findDrug: function(params) { //функция для поиска конкретного медикамента в списке заказанных
		var wnd = this;		
		var viewframe =  wnd.DrugGrid;
		if (params.DrugComplexMnn_id > 0 || params.Drug_id) {
			var item_index = viewframe.getGrid().getStore().findBy(function(r,id) {
				if(r.get('DrugComplexMnn_id') == params.DrugComplexMnn_id)
					return true;
			});
			viewframe.getGrid().getSelectionModel().selectRows([item_index]);
		}
	},
	doSign: function() {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderAllocationEditForm').getForm();
		
		this.getLoadMask('Подождите, идет подписание...').show();
		Ext.Ajax.request({
			url: '?c=WhsDocumentOrderAllocation&m=sign',
			params: {
				WhsDocumentOrderAllocation_id: form.findField('WhsDocumentOrderAllocation_id').getValue(),
				WhsDocumentType_id: form.findField('WhsDocumentType_id').getValue()
			},
			success: function(response, action) {
				wnd.getLoadMask().hide();
				wnd.buttons[2].enable();
				if (response && response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						Ext.Msg.alert('Сообщение', 'Документ успешно подписан');
						form.findField('WhsDocumentStatusType_id').setValue(2);
						wnd.buttons[2].hide();
						wnd.setDisabled(true);
					}
				} else {
					Ext.Msg.alert('Ошибка', 'Ошибка при подписании! Отсутствует ответ сервера.');
				}
			}
		});
	},
	doSave:  function(silent) {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderAllocationEditForm').getForm();
		var viewframe =  wnd.DrugGrid;
		var viewframe_mo = wnd.DrugGridMo;
		
		form.findField('OrderAllocationDrugJSON').setValue(viewframe.getJSONChangedData());
		form.findField('OrderAllocationDrugMoJSON').setValue(viewframe_mo.getJSONChangedData());
		
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentOrderAllocationEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		//проверка, не введено ли слишком большое количество медикамента (максимум 999999)
		if (viewframe.checkMaxKolvoOverflow()) {
			Ext.Msg.alert('Ошибка', 'Введено слишком большое количество медикамента. Суммарное количество медикамента не должно превышать 999999.');
			return false;
		}
		viewframe.updateSumm();		
		
		this.getLoadMask('Подождите, идет сохранение...').show();
		var params = new Object();
		params.action = wnd.action;
		params.WhsDocumentUc_Sum = wnd.form.findField('WhsDocumentUc_Sum').getValue();
		params.Org_id = wnd.form.findField('Org_id').getValue();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				wnd.callback();
				if ( silent == false ) {
					wnd.hide();
				} else {
					wnd.WhsDocumentUc_id = action.result.WhsDocumentUc_id;
					wnd.action = 'edit';
					form.findField('WhsDocumentUc_id').setValue(wnd.WhsDocumentUc_id);
					form.findField('WhsDocumentOrderAllocation_id').setValue(wnd.WhsDocumentUc_id);	
					viewframe.loadData({
						url: '/?c=WhsDocumentOrderAllocationDrug&m=loadList',
						params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
						globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
						callback: function() {
							viewframe.setPlanKolvo(false);
							viewframe.updateSumm();
							viewframe.selectFirstRow();
						}
					});
					viewframe_mo.loadData({
						url: '/?c=WhsDocumentOrderAllocationDrug&m=loadListMo',
						params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
						globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
						callback: function() {
							viewframe_mo.load_complete = true;
							viewframe_mo.filter_data = null;
							viewframe.setPlanKolvo(false);
							viewframe.updateSumm();
							viewframe.selectFirstRow();
						}
					});
					if ( typeof silent == 'function' ) {
						silent();
					}
				}
			}
		});
		
		return true;		
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.findById('WhsDocumentOrderAllocationEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'WhsDocumentOrderAllocation_Percent',
			'WhsDocumentUc_Date_Range',
			'DrugRequest_id'
		];
		
		for (var i in field_arr) if (form.findField(field_arr[i])) {
			if (disable)
				form.findField(field_arr[i]).disable();
			else
				form.findField(field_arr[i]).enable();
		}
		
		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.buttons[2].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			wnd.buttons[2].enable();
		}
		
		wnd.DrugGrid.setReadOnly(disable);
		wnd.DrugGridMo.setReadOnly(disable);
		wnd.DrugGridMo.getAction('action_wdoae_mo_actions').setDisabled(disable);
	},
	showSupplierAllocation: function() {
		var wnd = this;
		var order_id = wnd.form.findField('WhsDocumentOrderAllocation_id').getValue();
		var status_id = wnd.form.findField('WhsDocumentStatusType_id').getValue();

		if (order_id > 0) {
			if (status_id == 2) {
				//для подписанных распоряжений сразу открываем просмотр разнарядки
				getWnd('swSupplierAllocationViewWindow').show({
					action: 'add'
				});
			} else {
				//сохраняем распоряжение
				wnd.doSave(function() {
					//генерация разнарядки по поставщикам
					Ext.Ajax.request({
						params: {
							WhsDocumentOrderAllocation_id: order_id
						},
						callback: function(options, success, response) {
							loadMask.hide();
							if ( success ) {
								//открываем разнарядку по поставщикам
								getWnd('swSupplierAllocationViewWindow').show({
									action: 'add'
								});
							} else {
								sw.swMsg.alert('Ошибка', 'Ошибка при создании разнарядки по поставщикам', function() {
									wnd.form.findField('WhsDocumentUc_Num').focus(true);
								});
							}
						}.createDelegate(this),
						url: '/?c=WhsDocumentOrderAllocation&m=createSupplierAllocation'
					});
				});
			}
		}
	},
	printAllocation: function() {
		var order_id = this.form.findField('WhsDocumentOrderAllocation_id').getValue();
		if (order_id > 0) {
			printBirt({
				'Report_FileName': 'WhsDocumentOrderAllocation_Report.rptdesign',
				'Report_Params': '&WhsDocumentOrderAllocation_id=' + order_id,
				'Report_Format': 'xls'
			});
		}
	},
	setDistribution: function(mode) {
		var record = form.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
		if (record.get('DrugRequest_id') > 0) {
		}
	},
	addAll: function() {
		var record = form.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
		if (record.get('DrugRequest_id') > 0) {
		}
	},
	show: function() {
        var wnd = this;
		
		sw.Promed.swWhsDocumentOrderAllocationEditWindow.superclass.show.apply(this, arguments);
		
		var viewframe = wnd.DrugGrid;
		var viewframe_mo = wnd.DrugGridMo;

		if ( getGlobalOptions().org_id > 0 ) {
			this.form.findField('Org_id').getStore().load({
				params: {
					Object:'Org',
					Org_id: getGlobalOptions().org_id,
					Org_Name:''
				},
				callback: function() {
					wnd.form.findField('Org_id').setValue(getGlobalOptions().org_id);
				}
			});
		}		
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
        this.WhsDocumentType_Name = lang['dokument'];
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentType_id ) {
			this.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
		}
		
		this.form.reset();
		wnd.DrugGrid.removeAll({clearAll: true});
		wnd.DrugGridMo.removeAll({clearAll: true});
		wnd.DrugGrid.setReadOnly(true);
		wnd.DrugGridMo.setReadOnly(true);
		wnd.buttons[2].enable();
		
		if (this.WhsDocumentType_id == 7) {
            this.WhsDocumentType_Name = 'Распоряжение на выдачу разнарядки для МО на выписку рецептов';
		}
        if (this.WhsDocumentType_id == 8) {
            this.WhsDocumentType_Name = 'Распоряжение на отзыв разнарядки для МО на выписку рецептов';
		}
        if (this.WhsDocumentType_id == 23) {
            this.WhsDocumentType_Name = 'Распоряжение на ввод остатков по разнарядке';
		}
		
		this.form.findField('DrugRequest_id').setContainerVisible(this.WhsDocumentType_id != 23);
		this.form.findField('WhsDocumentOrderAllocation_Percent').ownerCt.ownerCt.setVisible(this.WhsDocumentType_id != 23);
		this.findById('WhsDocumentOrderAllocationDrugGrid').getAction('action_view').setHidden(this.WhsDocumentType_id != 23);
		
		var Available_Kolvo_index = this.findById('WhsDocumentOrderAllocationDrugGrid').getGrid().colModel.findColumnIndex('Available_Kolvo');
		var WhsDocumentOrderAllocation_Percent_index = this.findById('WhsDocumentOrderAllocationDrugGrid').getGrid().colModel.findColumnIndex('WhsDocumentOrderAllocation_Percent');
		this.findById('WhsDocumentOrderAllocationDrugGrid').getColumnModel().setHidden(Available_Kolvo_index, (this.WhsDocumentType_id == 23));
		this.findById('WhsDocumentOrderAllocationDrugGrid').getColumnModel().setHidden(WhsDocumentOrderAllocation_Percent_index, (this.WhsDocumentType_id == 23));
		
		// #74799 увеличить высоту списка «Разнарядка МО» так, чтобы не было пустого места на экране
        if (this.WhsDocumentType_id == 23) {
            this.DrugGridMo.setHeight(this.getEl().getHeight()-540);
		}
		else {
            this.DrugGridMo.setHeight(180);
		}

		wnd.DrugGridMo.addActions({
			name:'action_wdoae_mo_actions',
			text:'Действия',
			menu: [{
				name: 'action_delete_all',
				text: 'Удалить все',
				tooltip: 'Удалить все',
				handler: function() {
					wnd.DrugGridMo.deleteRecord('all');
				},
				iconCls: 'delete16'
			}],
			iconCls: 'actions16'
		});
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();
		
		switch (this.action) {
			case 'add':
                this.setTitle(this.WhsDocumentType_Name + lang['_dobavlenie']);
				Ext.Ajax.request({
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							this.form.findField('WhsDocumentUc_Num').setValue(response_obj.WhsDocumentUc_Num);
							this.form.findField('WhsDocumentUc_Date').setValue(getGlobalOptions().date);
							this.form.findField('WhsDocumentUc_Name').setValue(response_obj.WhsDocumentUc_Num + '. ' + getGlobalOptions().date + ' - ' + wnd.WhsDocumentType_Name);
						} else {
							sw.swMsg.alert('Ошибка', 'Ошибка при определении номера документа', function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderAllocation&m=getWhsDocumentOrderAllocationNumber'
				});
				wnd.form.findField('WhsDocumentOrderAllocation_Percent').fireEvent('change', wnd.form.findField('WhsDocumentOrderAllocation_Percent'), wnd.form.findField('WhsDocumentOrderAllocation_Percent').getValue());
				wnd.form.findField('WhsDocumentType_id').setValue(this.WhsDocumentType_id);
				wnd.form.findField('DrugRequest_id').getStore().load();
				wnd.findById('BtnGen').setVisible(this.WhsDocumentType_id != 23);
				wnd.buttons[2].show();
				this.setDisabled(false);
			break;
			case 'edit':
			case 'view':
                this.setTitle(this.WhsDocumentType_Name + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				wnd.findById('BtnGen').hide();
				Ext.Ajax.request({
					params:{
						WhsDocumentUc_id: wnd.WhsDocumentUc_id
					},
					callback: function(options, success, response) {
						loadMask.hide();
						if ( success ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result[0]) { return false}
							wnd.form.setValues(result[0]);
							viewframe.loadData({
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadList',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								callback: function() {
									viewframe.setPlanKolvo(false);
									viewframe.updateSumm();
									viewframe.selectFirstRow();
								}
							});
							viewframe_mo.loadData({
								url: '/?c=WhsDocumentOrderAllocationDrug&m=loadListMo',
								params:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								globalFilters:{WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue()},
								callback: function() {
									viewframe_mo.load_complete = true;
									viewframe_mo.filter_data = null;
									viewframe.setPlanKolvo(false);
									viewframe.updateSumm();
									viewframe.selectFirstRow();
								}
							});
							if (wnd.form.findField('WhsDocumentStatusType_id').getValue() > 1) {
								wnd.buttons[2].hide();
							} else {
								wnd.buttons[2].show();
							}
							var params = new Object();
							if (result[0].DrugRequest_id && result[0].DrugRequest_id > 0 && this.action == 'view') {
								params.DrugRequest_id = result[0].DrugRequest_id;
							}
							wnd.form.findField('DrugRequest_id').getStore().load({
								params: params
							});
						}
						else {
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных', function() { 
								this.form.findField('WhsDocumentUc_Num').focus(true); 
							});
						}
					}.createDelegate(this),
					url: '/?c=WhsDocumentOrderAllocation&m=load'
				});
				this.setDisabled(this.action=='view');
			break;	
		}
		
		
	},
	initComponent: function() {
		var wnd = this;

		this.DrugGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadList',
			height: 180,
			region: 'north',
			object: 'WhsDocumentOrderAllocation',
			id: 'WhsDocumentOrderAllocationDrugGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			actions: [
				{ name: 'action_add', handler: function() {wnd.DrugGrid.addRecord('add');} },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', handler: function() {wnd.DrugGrid.addRecord('view');}, disabled: false, hidden: true },
				{ name: 'action_delete', handler: function() {wnd.DrugGrid.deleteRecord();}},
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true }
			],
			stringfields: [
				{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentUc_pid', type: 'int', hidden: true},
				{name: 'WhsDocumentUc_Name', type: 'string', header: '№ ГК', width: 200},
				{name: 'Supplier_Name', type: 'string', header: 'Поставщик', width: 200},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'Okei_id', type: 'int', hidden: true},
                {name: 'Actmatters_RusName', type: 'string', header: 'МНН', width: 100},
                {name: 'Tradenames_Name', type: 'string', header: 'Торговое наименование', width: 100, id: 'autoexpand' },
                {name: 'DrugForm_Name', type: 'string', header: 'Форма выпуска', width: 100},
                {name: 'Drug_Dose', type: 'string', header: 'Дозировка', width: 100},
                {name: 'Drug_Fas', type: 'string', header: 'Фасовка', width: 100},
                {name: 'Kolvo', type: 'string', header: 'Кол-во.', width: 100, hidden: true},
				{name: 'Available_Kolvo', type: 'string', header: 'Кол-во дост.', width: 100},
				{name: 'WhsDocumentOrderAllocationDrug_Kolvo', header: 'Кол-во в разнарядке', width: 140,
					renderer: function(value, p, rec){
						var val = value*1;
						var available = rec.get('Available_Kolvo');
						if (val > 0 && available > 0 && val > available ) {
							return '<span style="color: red">'+value+'</span>';
						}
						return value;
					}
				},
				{name: 'WhsDocumentOrderAllocation_Percent', type: 'string', header: '%', width: 100},
				{name: 'WhsDocumentOrderAllocationDrug_Price', type: 'string', header: 'Цена', width: 100},
				{name: 'WhsDocumentUc_Sum', type: 'string', header: 'Сумма', width: 100},
                {name: 'Reg_Num', type: 'string', header: 'РУ', width: 100},
                {name: 'Reg_Firm', type: 'string', header: 'Держатель/Владелец РУ', width: 100},
                {name: 'Reg_Country', type: 'string', header: 'Страна держателя/владельца РУ', width: 100},
                {name: 'Reg_Period', type: 'string', header: 'Период действия РУ', width: 100},
                {name: 'Reg_ReRegDate', type: 'string', header: 'Дата переоформления РУ', width: 100},
                {name: 'state', hidden: true}
			],
			toolbar: true,
			contextmenu: true,
			addRecord: function(action){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var d_arr = new Array();

				store.each(function(record) {
					d_arr.push({
						Drug_id: record.get('Drug_id'),
						WhsDocumentUc_pid: record.get('WhsDocumentUc_pid'),
						WhsDocumentOrderAllocationDrug_Price: record.get('WhsDocumentOrderAllocationDrug_Price')
					});
				});

				if (wnd.WhsDocumentType_id == 23) {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (action == 'add') {
						var params = {
							DrugFinance_id: wnd.form.findField('DrugFinance_id').getValue(),
							WhsDocumentOrderAllocation_id: wnd.form.findField('WhsDocumentOrderAllocation_id').getValue(),
							WhsDocumentCostItemType_id: wnd.form.findField('WhsDocumentCostItemType_id').getValue(),
							WhsDocumentUc_id: wnd.form.findField('WhsDocumentUc_id').getValue()
						};
					}
					else {
						var params = selected_record.data;
						params.WhsDocumentSupply_id = selected_record.get('WhsDocumentUc_pid');
						params.Kolvo = selected_record.get('WhsDocumentOrderAllocationDrug_Kolvo');
						params.Price = selected_record.get('WhsDocumentOrderAllocationDrug_Price');
					}
					getWnd('swWhsDocumentOrderAllocationDrugOstatEditWindow').show({
						WhsDocumentOrderAllocationDrug_id: selected_record ? selected_record.get('WhsDocumentOrderAllocationDrug_id') : null,
						action: action,
						params: params,
						callback: function(rec) {
							var record_count = store.getCount();
							if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderAllocationDrug_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
								record_count = 0;
							}
							view_frame.clearFilter();
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);
							rec.WhsDocumentOrderAllocationDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
							rec.Okei_id = 120;
							rec.WhsDocumentOrderAllocationDrug_Kolvo = rec.Kolvo;
							rec.WhsDocumentOrderAllocationDrug_Price = rec.Price;
							rec.WhsDocumentUc_Sum = (rec.Kolvo * rec.Price).toFixed(2);
							rec.state = 'add';
							store.insert(record_count, new record(rec));
							view_frame.setFilter();
						}
					});
				} 
				else {
					getWnd('swWhsDocumentOrderAllocationDrugEditWindow').show({
						DrugFinance_id: wnd.form.findField('DrugFinance_id').getValue(),
						WhsDocumentCostItemType_id: wnd.form.findField('WhsDocumentCostItemType_id').getValue(),
						DrugArray: d_arr,
						onSelect: function(rec_arr) {
							view_frame.clearFilter();
							for(var i = 0; i < rec_arr.length; i++) {
								var record_count = store.getCount();
								if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderAllocationDrug_id') ) {
									view_frame.removeAll({ addEmptyRecord: false });
									record_count = 0;
								}
								var record = new Ext.data.Record.create(view_frame.jsonData['store']);
								var kolvo = rec_arr[i].Kolvo;
								rec_arr[i].WhsDocumentOrderAllocationDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
								rec_arr[i].Available_Kolvo = kolvo;
								rec_arr[i].WhsDocumentOrderAllocationDrug_Kolvo = 0;
								rec_arr[i].WhsDocumentOrderAllocation_Percent = 0;
								rec_arr[i].WhsDocumentUc_Sum = (kolvo * rec_arr[i].WhsDocumentOrderAllocationDrug_Price).toFixed(2);
								rec_arr[i].state = 'add';
								store.insert(record_count, new record(rec_arr[i]));
							}
							view_frame.setFilter();
						}
					});
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				var msg = (wnd.WhsDocumentType_id == 23) 
					? 'Медикамент будет удален из распоряжения на ввод остатков вместе с разнарядками МО. Удалить?' 
					: 'Выбранный медикамент будет удален из разнарядок на выписку рецептов. Подтверждаете выполнение действий?';
				
				sw.swMsg.show({
					title: 'Вопрос',
					msg: msg,
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							view_frame.clearFilter();
							if (selected_record.get('state') == 'add') {
								view_frame.getGrid().getStore().remove(selected_record);
							} else {
								selected_record.set('state', 'delete');
								selected_record.commit();
							}
							view_frame.setFilter();

							//удаляем связанные записи из разнарядки МО
							if (selected_record.get('WhsDocumentUc_pid') > 0 && selected_record.get('Drug_id') > 0 ) {
								wnd.DrugGridMo.deleteRecord('by_params', {
									'WhsDocumentUc_pid': selected_record.get('WhsDocumentUc_pid'),
									'Drug_id': selected_record.get('Drug_id')
								});
							}

							wnd.DrugGrid.updateSumm();
						}
					}
				});
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
						data.push(record.data);
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			updateSumm: function() {
				var summ = 0;
				var summ_field = wnd.findById('WhsDocumentOrderAllocationEditForm').getForm().findField('WhsDocumentUc_Sum');
				this.getGrid().getStore().each(function(record) {
					if(record.get('state') != 'delete' && record.get('WhsDocumentUc_Sum') && record.get('WhsDocumentUc_Sum') > 0) {
						summ += (record.data.WhsDocumentUc_Sum * 1);
					}
				});
				summ_field.setValue(summ);
			},
			onRowSelect: function(sm,index,rec) {
				var viewframe_mo = wnd.DrugGridMo;
				viewframe_mo.filter_data = null;
				if (rec.get('WhsDocumentUc_pid') > 0 && rec.get('Drug_id') > 0 ) {
					viewframe_mo.filter_data = {
						WhsDocumentUc_pid: rec.get('WhsDocumentUc_pid'),
						Drug_id:  rec.get('Drug_id')
					};
					viewframe_mo.getGrid().getStore().filterBy(function(record){
						return (record.get('state') != 'delete' && record.get('WhsDocumentUc_pid') == rec.get('WhsDocumentUc_pid') && record.get('Drug_id') == rec.get('Drug_id'));
					});
				}

				this.getAction('action_view').setDisabled(rec.get('WhsDocumentOrderAllocationDrug_id') <= 0);
				this.getAction('action_delete').setDisabled(this.readOnly || rec.get('WhsDocumentOrderAllocationDrug_id') <= 0);
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			},
			setPlanKolvo: function(delete_empty_row) { //устанавливает значения для столбца "Кол-во в разнарядке" исходя из содержимого спецификации распределения по МО		
				if (wnd.WhsDocumentType_id == 23) {
					return false;
				}
				var plan = new Object();
				var viewframe_spec = wnd.DrugGridMo;

				viewframe_spec.clearFilter();
				viewframe_spec.getGrid().getStore().each(function(record){
					if (record.get('state') != 'delete') {
						var key = record.get('Drug_id')+'_'+record.get('WhsDocumentUc_pid');
						var kolvo = record.get('WhsDocumentOrderAllocationDrug_Kolvo')*1;
						if (key != '' && kolvo > 0) {
							if (!plan[key]) { plan[key] = 0; }
							plan[key] += kolvo;
						}
					}
				});
				viewframe_spec.setFilter();

				this.clearFilter();
				this.getGrid().getStore().each(function(record){
					if (record.get('state') != 'delete') {
						var key = record.get('Drug_id')+'_'+record.get('WhsDocumentUc_pid');
						this.recalculationFieldsByKolvo(record, plan[key] > 0 ? plan[key] : 0);
					}
				}.createDelegate(this));
				if (delete_empty_row) {
					this.deleteRowWithoutPlanKolvo();	
				}
				this.setFilter();
			},
			recalculationFieldsByKolvo: function(record, kolvo) {
				if (record.get('Drug_id') > 0) {
					if (kolvo == null) {
						kolvo = record.get('WhsDocumentOrderAllocationDrug_Kolvo');
					}
					if (kolvo <= 0) {
						kolvo = 0;
					}
					record.set('WhsDocumentOrderAllocationDrug_Kolvo', kolvo);
					record.set('WhsDocumentUc_Sum', (kolvo*record.get('WhsDocumentOrderAllocationDrug_Price')).toFixed(2));
					record.set('WhsDocumentOrderAllocation_Percent', record.get('Available_Kolvo') > 0 ? Math.round((kolvo*10000)/record.get('Available_Kolvo'))/100.0 : 0);
					if (record.get('state') != 'add') {
						record.set('state', 'edit');
					};
					record.commit();
				}
			},
			deleteRowWithoutPlanKolvo: function() { //очищает сводную разнарядку от записей не попавших в разнарядку МО
				var viewframe = this;
				var viewframe_spec = wnd.DrugGridMo;
				if (viewframe_spec.load_complete) {
					viewframe.clearFilter();
					viewframe.getGrid().getStore().each(function(record){
						if (record.get('WhsDocumentOrderAllocationDrug_Kolvo') <= 0) {
							if (record.get('state') == 'edit') {
								record.set('state', 'delete');
								record.commit();
							} else if (record.get('state') != 'delete') {
								viewframe.getGrid().getStore().remove(record);
							}
						}
					});
					viewframe.setFilter();
				}
			},
			selectFirstRow: function() {
				var viewframe = this;
				if (viewframe.getGrid().getStore().getCount() > 0) {
					viewframe.getGrid().getView().focusRow(0);
					viewframe.getGrid().getSelectionModel().selectFirstRow();
				}
			},
			checkMaxKolvoOverflow: function() {
				var overflow = false;
				var viewframe = this;
				viewframe.getGrid().getStore().each(function(record){
					if (record.get('WhsDocumentOrderAllocationDrug_Kolvo') > 999999) {
						overflow = true;
						return false;
					}
				});
				return overflow;
			}
		});



		this.DrugGridMo =
			new sw.Promed.ViewFrame({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				border: true,
				dataUrl: '/?c=WhsDocumentOrderAllocationDrug&m=loadListMO',
				height: 180,
				region: 'north',
				object: 'WhsDocumentOrderAllocation',
				id: 'WhsDocumentOrderAllocationDrugGridMO',
				paging: false,
				saveAtOnce:false,
				style: 'margin-bottom: 0px',
				actions: [
					{ name: 'action_add', handler: function() {wnd.DrugGridMo.addRecord();} },
					{ name: 'action_edit', disabled: true, hidden: true },
					{ name: 'action_view', disabled: true, hidden: true },
					{ name: 'action_delete', handler: function() {wnd.DrugGridMo.deleteRecord('selected');}},
					{ name: 'action_refresh', disabled: true, hidden: true },
					{ name: 'action_print', disabled: true },
					{ name: 'action_save', disabled: true, hidden: true }
				],
				stringfields: [
					{name: 'WhsDocumentOrderAllocationDrug_id', type: 'int', header: 'ID', key: true},
					{name: 'WhsDocumentUc_pid', type: 'int', hidden: true},
					{name: 'WhsDocumentUc_Num', type: 'string', header: '№ ГК', width: 200},
					{name: 'Org_id', type: 'int', hidden: true},
					{name: 'Lpu_Name', type: 'string', header: 'МО', width: 200},
                    {name: 'Supplier_Name', type: 'string', header: 'Поставщик', width: 200},
					{name: 'WhsDocumentOrderAllocation_id', type: 'int', hidden: true},
					{name: 'Drug_id', type: 'int', hidden: true},
					{name: 'Okei_id', type: 'int', hidden: true},
                    {name: 'Tradenames_Name', type: 'string', header: 'Торговое наименование', width: 100, id: 'autoexpand' },
                    {name: 'DrugForm_Name', type: 'string', header: 'Форма выпуска', width: 100},
                    {name: 'Drug_Dose', type: 'string', header: 'Дозировка', width: 100},
                    {name: 'Drug_Fas', type: 'string', header: 'Фасовка', width: 100},
                    {name: 'Kolvo', type: 'string', header: 'Кол-во.', width: 100, hidden: true},
					{name: 'Kolvo_Request', type: 'string', header: 'Кол-во в заявке', width: 100},
					{name: 'WhsDocumentOrderAllocationDrug_Kolvo', type: 'string', header: 'Кол-во в разнарядке', width: 140, editor: new Ext.form.NumberField()},
					{name: 'WhsDocumentOrderAllocationDrug_Percent', type: 'string', header: '%', width: 100},
					{name: 'WhsDocumentOrderAllocationDrug_Price', type: 'string', header: 'Цена', width: 100},
					{name: 'WhsDocumentUc_Sum', type: 'string', header: 'Сумма', width: 100},
                    {name: 'Reg_Num', type: 'string', header: 'РУ', width: 100},
                    {name: 'Reg_Firm', type: 'string', header: 'Держатель/Владелец РУ', width: 100},
                    {name: 'Reg_Country', type: 'string', header: 'Страна держателя/владельца РУ', width: 100},
                    {name: 'Reg_Period', type: 'string', header: 'Период действия РУ', width: 100},
                    {name: 'Reg_ReRegDate', type: 'string', header: 'Дата переоформления РУ', width: 100},
					{name: 'state', hidden: true}
				],
				toolbar: true,
				contextmenu: true,
				onRowSelect: function(sm,index,rec) {
					this.getAction('action_wdoae_mo_actions').setDisabled(this.readOnly);
					this.getAction('action_delete').setDisabled(this.readOnly || rec.get('Drug_id') <= 0);
				},
				addRecord: function(){
					var view_frame = this;
					var store = view_frame.getGrid().getStore();

					var selected_record = wnd.DrugGrid.getGrid().getSelectionModel().getSelected();
					if (selected_record) {
						var kolvo = selected_record.get('Available_Kolvo');
						if (selected_record.get('WhsDocumentOrderAllocationDrug_Kolvo') > 0) {
							kolvo -= selected_record.get('WhsDocumentOrderAllocationDrug_Kolvo');
						}

						//вызов формы для выбора МО
						getWnd('swWhsDocumentOrderAllocationDrugMoEditWindow').show({
							FormParams: {
                                Tradenames_Name: selected_record.get('Tradenames_Name'),
                                DrugForm_Name: selected_record.get('DrugForm_Name'),
                                Drug_Dose: selected_record.get('Drug_Dose'),
                                Drug_Fas: selected_record.get('Drug_Fas'),
								WhsDocumentOrderAllocationDrug_Kolvo: kolvo > 0 ? kolvo : 0,
                                Reg_Num: selected_record.get('Reg_Num'),
                                Reg_Firm: selected_record.get('Reg_Firm'),
                                Reg_Country: selected_record.get('Reg_Country'),
                                Reg_Period: selected_record.get('Reg_Period'),
                                Reg_ReRegDate: selected_record.get('Reg_ReRegDate')
							},
							onSave: function(data) {
								view_frame.clearFilter();

								data.WhsDocumentOrderAllocationDrug_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
								data.WhsDocumentUc_pid = selected_record.get('WhsDocumentUc_pid');
								data.WhsDocumentUc_Num = selected_record.get('WhsDocumentUc_Name');
								data.Supplier_Name = selected_record.get('Supplier_Name');
								data.Drug_id = selected_record.get('Drug_id');
								data.Okei_id = selected_record.get('Okei_id');
                                data.Tradenames_Name = selected_record.get('Tradenames_Name');
                                data.DrugForm_Name = selected_record.get('DrugForm_Name');
                                data.Drug_Dose = selected_record.get('Drug_Dose');
                                data.Drug_Fas = selected_record.get('Drug_Fas');
                                data.WhsDocumentOrderAllocationDrug_Price = selected_record.get('WhsDocumentOrderAllocationDrug_Price');
                                data.WhsDocumentUc_Sum = (data.WhsDocumentOrderAllocationDrug_Kolvo*selected_record.get('WhsDocumentOrderAllocationDrug_Price')).toFixed(2);
                                data.Reg_Num = selected_record.get('Reg_Num');
                                data.Reg_Firm = selected_record.get('Reg_Firm');
                                data.Reg_Country = selected_record.get('Reg_Country');
                                data.Reg_Period = selected_record.get('Reg_Period');
                                data.Reg_ReRegDate = selected_record.get('Reg_ReRegDate');

								//ищем нет ли уже записи с заданными организацией, меддикаментом и ГК
								var idx =  store.findBy(function(rec){
									return (rec.get('state') != 'delete' && rec.get('Org_id') == data.Org_id && rec.get('WhsDocumentUc_pid') == selected_record.get('WhsDocumentUc_pid') && rec.get('Drug_id') == selected_record.get('Drug_id'));
								});

								if (idx > -1) { //запись в разнарядке МО найдена - обновляем запись
									var record = store.getAt(idx);
									if (record.get('WhsDocumentOrderAllocationDrug_Kolvo') > 0) {
										data.WhsDocumentOrderAllocationDrug_Kolvo = (data.WhsDocumentOrderAllocationDrug_Kolvo*1) + (record.get('WhsDocumentOrderAllocationDrug_Kolvo')*1);
                                        data.WhsDocumentUc_Sum = (data.WhsDocumentOrderAllocationDrug_Kolvo*selected_record.get('WhsDocumentOrderAllocationDrug_Price')).toFixed(2);
									}
									if (record.get('state') != 'add') {
										data.state = 'edit';
									}
									for (var key in data) {
										if (data.hasOwnProperty(key)) {
											record.set(key, data[key]);
										}
									}
									record.commit();
								} else { //запись не найдена, добавляем новую
									var record_count = store.getCount();
									if ( record_count == 1 && !store.getAt(0).get('WhsDocumentOrderAllocationDrug_id') ) {
										view_frame.removeAll({ addEmptyRecord: false });
										record_count = 0;
									}
									var record = new Ext.data.Record.create(view_frame.jsonData['store']);
									data.state = 'add';
									store.insert(record_count, new record(data));
								}

								view_frame.setFilter();
								wnd.DrugGrid.setPlanKolvo(false);
								wnd.DrugGrid.updateSumm();
							}
						});
					} else {
						Ext.Msg.alert('Ошибка', 'Для добавления строки в разнарядку МО, необходимо выбрать распределяемый медикамент.');
					}
				},
				deleteRecord: function(mode, params){
					var view_frame = this;

					if (mode == 'selected') {
						sw.swMsg.show({
							title: 'Вопрос',
							msg: 'Выбранный медикамент будет удален из разнарядок на выписку рецептов. Подтверждаете выполнение действий?',
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									view_frame.clearFilter();
									var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
									if (selected_record.get('state') == 'add') {
										view_frame.getGrid().getStore().remove(selected_record);
									} else {
										selected_record.set('state', 'delete');
										selected_record.commit();
									}
									view_frame.setFilter();
								}
							}
						});
					}

					if (mode == 'all') {
						sw.swMsg.show({
							title: 'Вопрос',
							msg: 'Вы действительно хотите удалить все записи?',
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									view_frame.clearFilter();
									view_frame.getGrid().getStore().each(function(record) {
										if (record.get('state') == 'add') {
											view_frame.getGrid().getStore().remove(record);
										} else {
											record.set('state', 'delete');
											record.commit();
										}
									});
									view_frame.setFilter();
								}
							}
						});
					}

					if (mode == 'by_params' && params && params.WhsDocumentUc_pid > 0 && params.Drug_id > 0) {
						this.clearFilter();
						this.getGrid().getStore().each(function(record){
							if (record.get('WhsDocumentUc_pid') == params.WhsDocumentUc_pid && record.get('Drug_id') == params.Drug_id) {
								if (record.get('state') == 'add') {
									view_frame.getGrid().getStore().remove(record);
								} else {
									record.set('state', 'delete');
									record.commit();
								}
							}
						});
						this.setFilter();
					}

					wnd.DrugGrid.setPlanKolvo(false);
					wnd.DrugGrid.updateSumm();
				},
				getChangedData: function(){ //возвращает новые и измненные показатели
					var data = new Array();
					this.clearFilter();
					this.getGrid().getStore().each(function(record) {
						if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
							data.push(record.data);
					});
					this.setFilter();
					return data;
				},
				getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
					var dataObj = this.getChangedData();
					return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
				},
				onAfterEdit: function(o) {
					var viewframe_mo = this;
					var viewframe = wnd.DrugGrid;

					/*if (o.value <= 0) {
						sw.swMsg.show({
							title: 'Ошибка',
							msg: 'Строка разнарядки с нулевым количеством будет удалена. Продолжить?',
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									viewframe.setPlanKolvo(true);
									viewframe.updateSumm();
									viewframe_mo.clearFilter();
									viewframe_mo.recalculationFieldsByKolvo(o.record);
									viewframe_mo.setFilter();
								} else {
									o.record.set('WhsDocumentOrderAllocationDrug_Kolvo', o.originalValue);
									o.record.commit();
								}
							}
						});
					} else {*/
						viewframe.setPlanKolvo(false);
						viewframe.updateSumm();
						viewframe_mo.clearFilter();
						viewframe_mo.recalculationFieldsByKolvo(o.record);
						viewframe_mo.setFilter();
					//}
				},
				recalculationFieldsByKolvo: function(record, kolvo) {
					if (kolvo == null) {
						kolvo = record.get('WhsDocumentOrderAllocationDrug_Kolvo');
					}
					if (kolvo <= 0) {
						kolvo = 0;
					}

					record.set('WhsDocumentOrderAllocationDrug_Kolvo', kolvo);
					record.set('WhsDocumentUc_Sum',  (kolvo*record.get('WhsDocumentOrderAllocationDrug_Price')).toFixed(2));
					record.set('WhsDocumentOrderAllocationDrug_Percent', record.get('Kolvo_Request') > 0 ? Math.round((kolvo*10000)/record.get('Kolvo_Request'))/100.0 : 0);

					/*if (record.get('state') == 'add') {
						if (kolvo == 0) {
							this.getGrid().getStore().remove(record);
						}
					} else {
						record.set('state', kolvo == 0 ? 'delete' : 'edit');
						record.commit();
					};*/
					if (record.get('state') != 'add') {
						record.set('state', 'edit');
					}
					record.commit();
				},
				clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
					this.getGrid().getStore().clearFilter();
				},
				setFilter: function() { //скрывает удаленные записи
					this.getGrid().getStore().filterBy(function(record){
						if (this.filter_data) {
							return (record.get('state') != 'delete' && record.get('WhsDocumentUc_pid') == this.filter_data.WhsDocumentUc_pid && record.get('Drug_id') == this.filter_data.Drug_id);
						} else {
							return (record.get('state') != 'delete');
						}
					}.createDelegate(this));
				}
			});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'background:#DFE8F6; padding: 0;',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentOrderAllocationEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url: '/?c=WhsDocumentOrderAllocation&m=save',
				layout: 'form',
				items: [{
					id: 'WDOAEW_WhsDocumentUc_id',
					name: 'WhsDocumentUc_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'WDOAEW_WhsDocumentOrderAllocation_id',
					name: 'WhsDocumentOrderAllocation_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'WDOAEW_WhsDocumentType_id',
					name: 'WhsDocumentType_id',
					value: 12,
					xtype: 'hidden'
				}, {
					name: 'OrderAllocationDrugJSON',
					xtype: 'hidden'
				}, {
					name: 'OrderAllocationDrugMoJSON',
					xtype: 'hidden'
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							xtype: 'textfield',
							fieldLabel : '№',
							width: 120,
							tabIndex: wnd.firstTabIndex + 10,
							name: 'WhsDocumentUc_Num',
							id: 'WDOAEW_WhsDocumentUc_Num',
							value: '',
							allowBlank:false
						}]						
					}, {
						layout: 'form',
						border: false,
						width: 235,
						labelWidth: 30,
						items: [{
							xtype: 'swdatefield',
							fieldLabel : 'от',
							tabIndex: wnd.firstTabIndex + 10,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'WhsDocumentUc_Date',
							id: 'WDOAEW_WhsDocumentUc_Date',
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 250,
						labelWidth: 115,
						items: [{
							fieldLabel: 'Статус документа',
							tabIndex: wnd.firstTabIndex + 10,
							hiddenName: 'WhsDocumentStatusType_id',
							id: 'WDOAEW_WhsDocumentStatusType_id',
							xtype: 'swcommonsprcombo',
							sortField:'WhsDocumentStatusType_Code',
							comboSubject: 'WhsDocumentStatusType',
							width: 130,
							disabled: true,
							allowBlank:true,
							value: 1
						}]
					}]
				}, {
					xtype: 'textfield',
					fieldLabel : 'Наименование',
					tabIndex: wnd.firstTabIndex + 10,
					width: 650,
					name: 'WhsDocumentUc_Name',
					id: 'WDOAEW_WhsDocumentUc_Name',						
					allowBlank:false
				}, {
					xtype: 'sworgcombo',
					fieldLabel : 'Организация',
					tabIndex: wnd.firstTabIndex + 10,
					hiddenName: 'Org_id',
					id: 'WDOAEW_Org_id',
					width: 650,
					disabled: true,
					editable: false,
					onTrigger1Click: function() {
						return false;
					}
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 450,
						items: [{
							fieldLabel: 'Источник финансирования',
							hiddenName: 'DrugFinance_id',
							xtype: 'swcommonsprcombo',
							tabIndex: wnd.firstTabIndex + 10,
							comboSubject: 'DrugFinance',
							width: 260,
							allowBlank: false
							, value: 27 //временно для тестов
						}]
					},{
						layout: 'form',
						border: false,
						labelWidth: 120,
						width: 450,
						items: [{
							fieldLabel: 'Статья расхода',
							hiddenName: 'WhsDocumentCostItemType_id',
							xtype: 'swcommonsprcombo',
							tabIndex: wnd.firstTabIndex + 10,
							comboSubject: 'WhsDocumentCostItemType',
							width: 260,
							typeCode: 'int',
							allowBlank: false
							, value: 2 //временно для тестов
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 450,
						items: [{
							fieldLabel: 'Период разнарядки',
							name: 'WhsDocumentUc_Date_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 170,
							tabIndex: wnd.firstTabIndex + 10,
							allowBlank: false,
							xtype: 'daterangefield'
						}]
					}, {
						layout: 'form',
						border: false,
						width: 450,
						labelWidth: 120,
						items: [{
							fieldLabel: 'Сводная заявка',
							hiddenName: 'DrugRequest_id',
							valueField: 'DrugRequest_id',
							displayField: 'DrugRequest_Name',
							xtype: 'swbaselocalcombo',
							width: 260,
							triggerAction: 'all',
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'DrugRequest_id', type: 'int'},
									{name: 'DrugRequest_Name', type: 'string'}
								],
								listeners: {
									load: function(s, rs, os) {
										var combo = this.form.findField('DrugRequest_id');
										var idx = s.findBy(function(rec) { return rec.get(combo.valueField) == combo.getValue(); });
										combo.reset();
										if ( !combo.disabled && idx < 0 ) { //если комбо не задисаблен и значение в нем не установлено, устанавливаем первую строку
											idx = 0;
										}
										if( s.getCount() ) {
											var record = s.getAt(idx);
											combo.setValue(record.get(combo.valueField));
											combo.fireEvent('select', combo, record, idx);
										}
									}.createDelegate(this)
								},
								key: 'DrugRequest_id',
								sortInfo: { field: 'DrugRequest_id' },
								url: '/?c=WhsDocumentOrderAllocation&m=loadSvodDrugRequestList'
							}),
							tpl: '<tpl for="."><div class="x-combo-list-item"><font color="red"></font>&nbsp;{DrugRequest_Name}</div></tpl>',
							allowBlank: false
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 260,
						items: [{
							fieldLabel: 'Величина разнарядки',
							name: 'WhsDocumentOrderAllocation_Percent',
							xtype: 'numberfield',
							tabIndex: wnd.firstTabIndex + 10,
							maxValue: 100,
							minValue: 0.01,
							value: 100,
							width: 70,
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						items: [{
							style: 'margin: 3px',
							html: '% от доступного количества'
						}]
					}]
				}, {
					layout: 'column',
					labelWidth: 180,
					width: 980,
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 260,
						items: [{
							fieldLabel: 'Сумма',
							name: 'WhsDocumentUc_Sum',
							xtype: 'numberfield',
							width: 70,
							tabIndex: wnd.firstTabIndex + 10,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 250,
						items: [{
							style: 'margin: 3px',
							html: 'руб.'
						}]
					}, {
						layout: 'form',
						border: false,
						width: 350,
						style: "padding-left: 182px; padding-bottom: 10px",
						items: [{
							xtype: 'button',
							id: 'BtnGen',
							text: 'Рассчитать разнарядку',
							//iconCls: 'resetsearch16',
							handler: function() {
								var base_form = wnd.findById('WhsDocumentOrderAllocationEditForm').getForm();
								var viewframe = wnd.DrugGrid;
								var viewframe_mo = wnd.DrugGridMo;
								
								if ( !base_form.isValid() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											wnd.findById('WhsDocumentOrderAllocationEditForm').getFirstInvalidEl().focus(true);
										},
										icon: Ext.Msg.WARNING,
										msg: ERR_INVFIELDS_MSG,
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}
								
								var params = {};
								params.WhsDocumentType_id = wnd.WhsDocumentType_id;
								params.begDate = Ext.util.Format.date(base_form.findField('WhsDocumentUc_Date_Range').getValue1(), 'd.m.Y');
								params.endDate = Ext.util.Format.date(base_form.findField('WhsDocumentUc_Date_Range').getValue2(), 'd.m.Y');
								params.DrugFinance_id = base_form.findField('DrugFinance_id').getValue();
								params.WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getValue();
								params.WhsDocumentOrderAllocation_Percent = base_form.findField('WhsDocumentOrderAllocation_Percent').getValue();
								params.DrugRequest_id = base_form.findField('DrugRequest_id').getValue();

								//флаг генерации разнарядки МО
								viewframe_mo.load_complete = false;

								viewframe.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWList',
									params: params, 
									globalFilters: params,
									callback: function () {
										viewframe.setPlanKolvo(true);
										viewframe.deleteRowWithoutPlanKolvo();
										viewframe.updateSumm();
										viewframe.selectFirstRow();
									}
								});
								
								viewframe_mo.loadData({
									url: '/?c=WhsDocumentOrderAllocationDrug&m=loadRAWListMO',
									params: params, 
									globalFilters: params,
									callback: function () {
										viewframe_mo.load_complete = true;
										viewframe_mo.filter_data = null;
										viewframe.setPlanKolvo(true);
										viewframe.deleteRowWithoutPlanKolvo();
										viewframe.updateSumm();
										viewframe.selectFirstRow();
									}
								});
							}
						}]
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					frame: true,
					collapsible: true,
					//id: 'WDOAEW__InfPanel2',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: 'Сводная разнарядка',
					items: [{
						layout: 'column',
						labelWidth: 40,
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 375,
							items: [{
								xtype: 'swdrugcomplexmnncombo',									
								fieldLabel: 'МНН',
								width: 300,
								tabIndex: wnd.firstTabIndex + 10,
								id: 'WDOAEW_DrugComplexMnn_id',
								value: '',
								allowBlank: true,
								listeners: {
									'select': function(combo, record) {
										if(typeof this.onSelectDrug == 'function') {
											this.onSelectDrug(combo, record);
										}
									}.createDelegate(this)
								}
							}]
						}, {
							layout: 'form',
							border: false,
							width: 375,
							items: [{
								xtype: 'swdrugsimplecombo',
								fieldLabel : 'Торг.',
								width: 300,
								tabIndex: wnd.firstTabIndex + 10,
								name: 'Drug_id',
								id: 'WDOAEW_Drug_id',
								value: '',
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								style: "padding-left: 0px",
								xtype: 'button',
								id: 'WDOAEW_BtnSearch',
								text: 'Поиск',
								iconCls: 'search16',
								handler: function() {
									var win = Ext.getCmp('WhsDocumentOrderAllocationEditWindow');
									var form = win.findById('WhsDocumentOrderAllocationEditForm').getForm();
									win.findDrug({
										Drug_id: form.findField('Drug_id').getValue(),
										DrugComplexMnn_id: form.findField('DrugComplexMnn_id').getValue()
									});
								}
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								style: "padding-left: 10px",
								xtype: 'button',
								id: 'WDOAEW_BtnClear',
								text: 'Сброс',
								iconCls: 'resetsearch16',
								handler: function() {
									var form = Ext.getCmp('WhsDocumentOrderAllocationEditForm').getForm();
									form.findField('Drug_id').reset();
									form.findField('DrugComplexMnn_id').reset();
								}
							}]
						}]
					},
					this.DrugGrid
					]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding: 0;',
					border: true,
					frame: true,
					collapsible: true,
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: 'Разнарядка МО',
					items: [
						this.DrugGridMo
					]
				})
				]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentUc_pid'}, 
				{name: 'WhsDocumentUc_Num'}, 
				{name: 'WhsDocumentUc_Name'}, 
				{name: 'WhsDocumentType_id'}, 
				{name: 'WhsDocumentUc_Date'}, 
				{name: 'Org_sid'}, 
				{name: 'Org_cid'}, 
				{name: 'Org_pid'}, 
				{name: 'Org_rid'}, 
				{name: 'WhsDocumentUc_Sum'}, 
				{name: 'WhsDocumentSupply_id'}, 
				{name: 'WhsDocumentUc_id'}, 
				{name: 'WhsDocumentSupply_ProtNum'}, 
				{name: 'WhsDocumentSupply_ProtDate'}, 
				{name: 'WhsDocumentSupplyType_id'}, 
				{name: 'WhsDocumentSupply_ExecDate'}, 
				{name: 'DrugFinance_id'}, 
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'WhsDocumentStatusType_id'}
			])
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{			
				hidden: true,
				handler: function() {},
				iconCls: null,
				text: 'Контроль поставки'
			}, {			
				handler: function() {
					this.ownerCt.doSave(false);
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {								
				handler: function() {
					this.disable();
					this.ownerCt.doSave(function() {
						this.ownerCt.doSign()
					}.createDelegate(this));
				},
				iconCls: null,
				text: 'Подписать'
			}/*, {
				handler: function() {
					this.ownerCt.showSupplierAllocation();
				},
				iconCls: null,
				text: 'Просмотр разнарядки по поставщикам'
			}*/, {
				handler: function() {
					this.ownerCt.printAllocation();
				},
				hidden: (getRegionNick() != 'saratov'),
				iconCls: null,
				text: 'Печать'
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swWhsDocumentOrderAllocationEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentOrderAllocationEditForm').getForm();
	}	
});