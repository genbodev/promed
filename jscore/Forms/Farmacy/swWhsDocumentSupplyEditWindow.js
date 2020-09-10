/**
* swWhsDocumentSupplyEditWindow - окно редактирования "Договор поставок (Контракт)"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      06.07.2012
* @comment      
*/
sw.Promed.swWhsDocumentSupplyEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: langs('Контракт'),
	layout: 'border',
	id: 'WhsDocumentSupplyEditWindow',
	modal: true,
	//shim: false,
	width: 400,
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
	setSupplyName: function() {
		var name = '';
		name += this.form.findField('WhsDocumentType_Name').getValue() ? this.form.findField('WhsDocumentType_Name').getValue() + ' ' : langs('Контракт');
		name += this.form.findField('WhsDocumentUc_Num').getValue() ? langs('№') + this.form.findField('WhsDocumentUc_Num').getValue() + ' ' : '';
		name += this.form.findField('WhsDocumentUc_Date').getValue() ? langs('От').toLowerCase() + ' ' + this.form.findField('WhsDocumentUc_Date').getValue().format('d.m.Y') : '';

		if (name != '' && (this.form.findField('WhsDocumentUc_Name').getValue() == '' || this.autoSupplyName)) {
			this.form.findField('WhsDocumentUc_Name').setValue(name);
			this.autoSupplyName = true;
		}
	},
	setOrgValueById: function(combo, id) {
		var wnd = this;
		if (id > 0) {
			Ext.Ajax.request({
				url: C_ORG_LIST,
				params: {
					Org_id: id,
					needOrgType: 1
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Org_id && result[0].Org_Name) {
						wnd.setOrgValueByData(combo, {
							Org_id: result[0].Org_id,
							Org_Name: result[0].Org_Name,
							OrgType_SysNick: result[0].OrgType_SysNick
						});
					} else {
						combo.reset();
					}
				}
			});
		}
	},
	setOrgValueByData: function(combo, data) {
		var win = this;
		combo.getStore().removeAll();
		combo.getStore().loadData([{
			Org_id: data.Org_id,
			Org_Name: data.Org_Name,
			OrgType_SysNick: data.OrgType_SysNick
		}]);
		combo.setValue(data[combo.valueField]);

		var index = combo.getStore().findBy(function(rec) { return rec.get(combo.valueField) == data[combo.valueField]; });
		if (index == -1) {
			return false;
		}

		var record = combo.getStore().getAt(index);
		combo.fireEvent('select', combo, record, 0);
		if(combo.hiddenName == 'Org_cid'){
			if(getGlobalOptions().orgtype == 'lpu'){
				win.setOrgValueByData(win.form.findField('Org_rid'),data);
			}
			var org_name = record.get('Org_Name');
			org_name = org_name.toLowerCase();
			if(record.get('OrgType_SysNick') == 'touz' && org_name.indexOf('минздрав') == 0){
				win.form.findField('DrugFinance_id').setAllowBlank(false);
				win.form.findField('WhsDocumentCostItemType_id').setAllowBlank(false);
				win.form.findField('BudgetFormType_id').setAllowBlank(false);
			}
		}
	},
	setDemand: function(id) { //функция для инициализации заявки
		var wnd = this;
		wnd.demand = new Object();
		if (id > 0) {
			Ext.Ajax.request({
				url: '?c=Farmacy&m=loadDocumentUcStrView',
				params: {
					DocumentUc_id: id
				},
				success: function(response){
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].DocumentUcStr_id) {
						wnd.demand.DocumentUc_id = id;
						wnd.demand.items = result;
					}
					//swalert(wnd.demand);
				}
			});
		}
	},
	setDeliveryData: function(data) { //функция для инициализации данных графика поставки
		var wnd = this;
		if (data && data.WhsDocumentDelivery_Data && data.WhsDocumentDelivery_Data.length > 0) {
			wnd.delivery_graph = new Object();				
			wnd.delivery_graph.data = new Ext.util.MixedCollection(true);
			wnd.delivery_graph.data.addAll(data.WhsDocumentDelivery_Data);
			wnd.delivery_graph.type =  (data.WhsDocumentDelivery_Data[0].WhsDocumentSupplySpec_id > 0) ? 'unit' : 'percent';
			wnd.delivery_graph.state = 'saved';
			
			//дописываем дополнительную информацию
			wnd.delivery_graph.data.each(function (item) {
				item.state = 'saved';
				item.delivery_id = item.WhsDocumentDelivery_id;
				item.grid_id = item.WhsDocumentSupplySpec_id;
			});
			
			wnd.form.findField('DeliveryGraphType').setValue(wnd.delivery_graph.type);
			wnd.oldDeliveryGraphType = wnd.delivery_graph.type;
		} else {
			wnd.delivery_graph = null;
			wnd.form.findField('DeliveryGraphType').setValue(null);
			wnd.oldDeliveryGraphType = null;
		}
	},
	setDefaultValues: function() {
		var combo = this.form.findField('WhsDocumentPurchType_id');
		var idx = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentPurchType_Code') == '1'; });
		if (idx > -1) {
			var record = combo.getStore().getAt(idx);
			combo.setValue(record.get('WhsDocumentPurchType_id'));
		}

		if (!Ext.isEmpty(getGlobalOptions().lpu_id) && getGlobalOptions().lpu_id > 0 && !this.isOstat) {
			this.setOrgValueById(this.form.findField('Org_cid'), getGlobalOptions().org_id);
			this.setOrgValueById(this.form.findField('Org_pid'), getGlobalOptions().org_id);
			this.setOrgValueById(this.form.findField('Org_rid'), getGlobalOptions().org_id);
		} else if (!Ext.isEmpty(getGlobalOptions().minzdrav_org_id)) {
			this.setOrgValueById(this.form.findField('Org_cid'), getGlobalOptions().minzdrav_org_id);
			this.setOrgValueById(this.form.findField('Org_pid'), getGlobalOptions().minzdrav_org_id);
		}
	},
	removeGridId: function(grid_id) {
		var wnd = this;
		if (wnd.delivery_graph && wnd.delivery_graph.data) {
			wnd.delivery_graph.data.filter('grid_id', new RegExp('^'+grid_id+'$', "i")).each(function(item){
				wnd.delivery_graph.data.remove(item);
			});
		}
	},
	changeDeliveryGraphType: function(combo, newValue) {
		var wnd = this;

		if (newValue != 'unit' && newValue != 'percent') {
			newValue = null;
		}

		if(wnd.oldDeliveryGraphType != newValue) {
			if(wnd.delivery_graph && wnd.delivery_graph.data && wnd.delivery_graph.data.getCount() > 0) {
				sw.swMsg.show({
					title: langs('Подтвердите действие'),
					msg: langs('Вы действительно хотите изменить тип графика? Это приведет к его удалению.'),
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							if (!wnd.delivery_graph) {
								wnd.delivery_graph = new Object();
							}
							wnd.delivery_graph.type = newValue;
							wnd.oldDeliveryGraphType = newValue;
							combo.setValue(newValue);
							//чистим график
							wnd.delivery_graph.data.each(function(item) {
								if (item.state == 'add') {
									wnd.delivery_graph.data.remove(item);
								} else {
									item.state = 'delete';
								}
							});
						} else {
							combo.setValue(wnd.oldDeliveryGraphType);
						}
					}
				});
			} else {
				if (!wnd.delivery_graph) {
					wnd.delivery_graph = new Object();
				}
				wnd.delivery_graph.type = newValue;
				wnd.oldDeliveryGraphType = newValue;
				combo.setValue(newValue);
			}
		}
	},
	prepareSaveDeliveryData: function() { //функция фильтрует, подготавливкает и упаковывает данные о графике поставки для дальнейшего сохранения
		var wnd = this;

		if(!wnd.delivery_graph || !wnd.delivery_graph.data)
			return false;

		var dd = wnd.delivery_graph.data;
		var grid = wnd.SpecGrid.getGrid();
		
		//очистка места для помещения будущей информации
		//чистим поле в форме
		this.form.findField('DeliveryData').setValue('');
		//чистим поля в гриде
		grid.getStore().each(function(r) {
			r.set('graph_data', '');
		});
		
		if (dd.getCount() < 1)
			return;

		// Хотя одновременно мы используем только один тип графика, обрабатывать нужно данные графиков обоих типов. В противном случае удаление графика неактуального типа не произойдет.

		// Обработка данных графика в долях
		var d_arr = new Array();
		//выбираем измененые данные
		dd.each(function(item) {
			if ((item.state == 'add' || item.state == 'edit' || item.state == 'delete') && item.grid_id <= 0) {
				d_arr.push({
					delivery_id: item.delivery_id > 0 ? item.delivery_id : null,
					date: item.WhsDocumentDelivery_setDT,
					amount: item.WhsDocumentDelivery_Kolvo,
					state: item.state
				});
			}
		});
		//сериализуем
		var d_str = d_arr.length > 0 ? Ext.util.JSON.encode(d_arr) : '';
		//пихаем в поле в форме
		this.form.findField('DeliveryData').setValue(d_str);

		// Обработка данных графика в долях
		var d_obj = new Object();
		//выбираем измененые данные
		dd.each(function(item) {
			if ((item.state == 'add' || item.state == 'edit' || item.state == 'delete') && item.grid_id > 0) {
				if (!d_obj[item.grid_id])
					d_obj[item.grid_id] = new Array();
				d_obj[item.grid_id].push({
					delivery_id: item.delivery_id > 0 ? item.delivery_id : null,
					date: item.WhsDocumentDelivery_setDT,
					amount: item.WhsDocumentDelivery_Kolvo,
					state: item.state
				});
			}
		});

		//сортируем по грид_ид
		grid.getStore().each(function(r) {
			if (d_obj[r.get('grid_id')] && d_obj[r.get('grid_id')].length > 0) {
				//сериализуем и пихаем в грид
				r.set('graph_data', Ext.util.JSON.encode(d_obj[r.get('grid_id')]));
			}
		});
	},
	findDrug: function(params) { //функция для поиска конкретного медикамента в списке заказанных
		var wnd = this;		
		if (params.DrugComplexMnn_id > 0 || params.Drug_id) {
			var item_index = wnd.SpecGrid.getGrid().getStore().findBy(function(r,id) {
				if(r.get('DrugComplexMnn_id') == params.DrugComplexMnn_id)
					return true;
			});
			wnd.SpecGrid.getGrid().getSelectionModel().selectRows([item_index]);
		}
	},
	setEnableFinCombo: function() {
		var combo = this.form.findField('WhsDocumentUc_pid');
		if (!combo.disabled) {
			var enable_fin_combo = false;
			var enable_cost_combo = false;

			if (combo.getValue() > 0) {
				var idx = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentProcurementRequest_id') == combo.getValue(); });
				if (idx > 0) {
					var record = combo.getStore().getAt(idx);
					enable_fin_combo = (record.get('DrugFinance_id') <= 0);
					enable_cost_combo = (record.get('WhsDocumentCostItemType_id') <= 0);
				}
			} else {
				enable_fin_combo = true;
				enable_cost_combo = true;
			}

			if (enable_fin_combo) {
				this.form.findField('DrugFinance_id').enable();
			} else {
				this.form.findField('DrugFinance_id').disable();
			}

			if (enable_cost_combo) {
				this.form.findField('WhsDocumentCostItemType_id').enable();
			} else {
				this.form.findField('WhsDocumentCostItemType_id').disable();
			}
		}
	},
	filterFinanceSource: function() {
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
		var combo = form.findField('FinanceSource_id');
		var store = combo.getStore();
		var value = combo.getValue();
		var arr = [];
		var vals = [];
		var field_arr = [
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'BudgetFormType_id'
		];
		for(var i = 0;i<field_arr.length;i++){
			if(form.findField(field_arr[i]).getValue() > 0){
				arr.push(field_arr[i]);
				vals.push(form.findField(field_arr[i]).getValue());
			}
		}
		if(!combo.disabled){
			store.load({
				callback: function(){
					if(store.data.length > 0 && arr.length > 0){
						store.each(function(rec){
							var remove = false;
							for(var i = 0;i<arr.length;i++){
								if(rec.get(arr[i]) != vals[i]){
									remove = true;
								}
							}
							if(remove){
								store.remove(rec);
							}
						});
					}
					if(value && store.getById(value)){
						combo.setValue(value);
					} else {
						combo.setValue(null);
					}
					if(!value && store.data.length > 0 && arr.length > 0){
						combo.setValue(store.getAt(0).get('FinanceSource_id'));
					}
				}
			});
		}
	},
	setEnableBlockedByDocumentStatus: function() {
		var blocked = false;
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
		var status_combo = form.findField('WhsDocumentStatusType_id');

		var idx = status_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentStatusType_id') == status_combo.getValue(); });
		if (idx > -1) {
			blocked = (status_combo.getStore().getAt(idx).get('WhsDocumentStatusType_Code') == 2); // 2 - Действующий
		}

		var field_arr = [
			'WhsDocumentSupply_ProtNum',
			'WhsDocumentSupply_ProtDate',
			'Org_sid',
			'Org_cid',
			'Org_pid',
			'Org_rid',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'BudgetFormType_id',
			'FinanceSource_id',
			'WhsDocumentPurchType_id',
			'WhsDocumentUc_pid',
			'DrugNds_id'
		];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
			form.findField(field_arr[i]).enable_blocked = blocked;
		}

		wnd.SpecGrid.enable_blocked = blocked;
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
		var field_arr = [
			'WhsDocumentUc_Num',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Name',
			'WhsDocumentSupply_ProtNum',
			'WhsDocumentSupply_ProtDate',
			'Org_sid',
			'Org_cid',
			'Org_pid',
			'Org_rid',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'BudgetFormType_id',
			'WhsDocumentPurchType_id',
			'WhsDocumentUc_pid',
			'DeliveryGraphType',
			'WhsDocumentSupply_Date_Range',
			'DrugNds_id'
		];
		var obj_arr = ['wdseFileUploadPanel', 'wdseBtnGraphEdit'];

		for (var i in field_arr) if (form.findField(field_arr[i])) {
			var field = form.findField(field_arr[i]);
			if (disable || field.enable_blocked) {
				field.disable();
			} else {
				field.enable();
			}
		}

		for (var i in obj_arr)  if (wnd.findById(obj_arr[i])) {
			var obj = wnd.findById(obj_arr[i]);
			if (disable || obj.enable_blocked) {
				obj.disable();
			} else {
				obj.enable();
			}
		}
		
		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.buttons[2].disable();
			wnd.buttons[3].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			if (wnd.WhsDocumentType_id != 19) {
				wnd.buttons[2].enable();
				wnd.buttons[3].enable();
			}
		}
		
		wnd.SpecGrid.setReadOnly(disable || wnd.WhsDocumentType_id == 19 || wnd.SpecGrid.enable_blocked); //Для контракта ввода остатков редактирование спецификации не доступно
	},
	doCheckSave: function() { //проверка данных перед сохраненеием
		var wnd = this;
		var err = '';
		
		var empty_graph = true;
		if(wnd.delivery_graph && wnd.delivery_graph.data) {
			wnd.delivery_graph.data.each(function(item){
				if (item.state != 'delete' && item.WhsDocumentDelivery_setDT != '') {
					empty_graph = false;
					return false;
				}					
			});
		}
		if (empty_graph 
			&& wnd.form.findField('WhsDocumentType_id').getValue() != 6 
			&& wnd.form.findField('WhsDocumentType_id').getValue() != 19 
			&& wnd.form.findField('WhsDocumentType_id').getValue() != 3) { // 6 - Контракт на поставку и отпуск; 19 - Контракт ввода остатков;
			err = langs('Отсутствует график поставки.');
		}
		
		if (err != '') {
			sw.swMsg.alert(langs('Ошибка'), err);
		}		
		return (err == '');
	},
	doSign: function() {
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
		
		// сначала сохраняем, потом подписываем.
		var options = new Object();
		options.onlySign = true;
		options.callback = function() {
			wnd.getLoadMask(langs('Подождите, идет подписание...')).show();
			Ext.Ajax.request({
				url: '?c=WhsDocumentSupply&m=sign',
				params: {
					WhsDocumentSupply_id: form.findField('WhsDocumentSupply_id').getValue()
				},
				callback: function(options, success, response) {
					var sign_success = false;
					var error_msg = 'При подписании произошла ошибка';
					wnd.getLoadMask().hide();
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
                        sign_success = answer.success;
						if (!Ext.isEmpty(answer.Error_Msg)) {
                            error_msg = answer.Error_Msg;
						}
					} else {
                        error_msg = langs('Ошибка при подписании! Отсутствует ответ сервера.');
					}

                    if (sign_success) {
                        Ext.Msg.alert(langs('Сообщение'), langs('Документ успешно подписан'));
						var id = form.findField('WhsDocumentSupply_id').getValue();
						form.findField('WhsDocumentUc_id').setValue(id);
                        form.findField('WhsDocumentStatusType_id').setValue(2);
                        wnd.buttons[3].show();
                        wnd.buttons[2].hide();
                        wnd.buttons[1].disable();
                        wnd.callback(wnd.owner, form.findField('WhsDocumentSupply_id').getValue());
                    } else {
                    	Ext.Msg.alert(langs('Ошибка'), error_msg, function () {
                            //если форма открыта в режиме добавления, а в процессе подписаняи возникла ошибка, нужно переоткрыть форму в режиме редактирования
                            var id = form.findField('WhsDocumentSupply_id').getValue();
                            if (wnd.action == 'add' && id > 0) {
                                wnd.show_params.action = 'edit';
                                wnd.show_params.WhsDocumentSupply_id = id;
                                wnd.show(wnd.show_params);
                            }
						});
					}
				}
			});
		}
		
		this.doSave(options);
	},
	doUnsign: function() {
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();

		//сначала сохраняем, потом отменяем подписание
		var options = new Object();
		options.onlySign = true;
		options.callback = function() {
			wnd.getLoadMask('Производится отмена подписания').show();
			Ext.Ajax.request({
				url: '?c=WhsDocumentSupply&m=unsign',
				params: {
					WhsDocumentSupply_id: form.findField('WhsDocumentSupply_id').getValue()
				},
				failure: function() {
					wnd.getLoadMask().hide();
				},
				success: function(response, action) {
					wnd.getLoadMask().hide();
					if (response && response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success) {
							Ext.Msg.alert(langs('Сообщение'), langs('Подпись отменена'));
							form.findField('WhsDocumentStatusType_id').setValue(1);
							wnd.buttons[3].hide();
							wnd.buttons[2].show();
							wnd.buttons[1].disable();
							wnd.callback(wnd.owner, form.findField('WhsDocumentSupply_id').getValue());
						}
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при отмене подписания! Отсутствует ответ сервера.'));
					}
				}
			});
		}

		this.doSave(options);
	},
	doImport:  function() {
		var wnd = this;
		var params = new Object();

		if (wnd.form.findField('WhsDocumentUc_pid').getValue() > 0) {
			params.WhsDocumentUc_pid = wnd.form.findField('WhsDocumentUc_pid').getValue();
		}

		params.callback = function(data) {
			var view_frame = wnd.SpecGrid;
			var store = view_frame.getGrid().getStore();
			var record_count = store.getCount();
			var pos = 0;

			if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSupplySpec_id') ) {
				view_frame.removeAll({ addEmptyRecord: false });
				record_count = 0;
			}

			store.each(function(rec){
				if (rec.get('WhsDocumentSupplySpec_PosCode') > pos) {
					pos = rec.get('WhsDocumentSupplySpec_PosCode');
				}
			});

			var record = new Ext.data.Record.create(view_frame.jsonData['store']);
			view_frame.clearFilter();
			for (var i = 0; i < data.length; i++) {
                var cnt = data[i].WhsDocumentSupplySpec_KolvoUnit > 0 ? data[i].WhsDocumentSupplySpec_KolvoUnit*1 : 0;
                var sum = 0;
                var sum_nds = 0;
                var nds = data[i].WhsDocumentSupplySpec_NDS > 0 ? data[i].WhsDocumentSupplySpec_NDS*1 : 0;
                var cost = data[i].WhsDocumentSupplySpec_Price > 0 ? data[i].WhsDocumentSupplySpec_Price*1 : 0;
                var cost_nds = 0;

                sum = Math.round(cnt * cost * 100)/100;
                sum_nds = Math.round((cnt * cost * ((100+nds)/100))*100)/100;
                cost_nds = Math.round((cost * ((100+nds)/100))*100)/100;

				data[i].WhsDocumentSupplySpec_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				data[i].grid_id = data[i].WhsDocumentSupplySpec_id;
				data[i].state = 'add';
				data[i].WhsDocumentSupplySpec_PosCode = ++pos;
				data[i].WhsDocumentSupplySpec_NDS = nds;
				data[i].WhsDocumentSupplySpec_Price = cost;
				data[i].WhsDocumentSupplySpec_PriceNDS = cost_nds;
				data[i].WhsDocumentSupplySpec_SumNDS = sum_nds;
				//в спецификации контракта, получаемой через импорт xls-файла, не предусмотрен параметр Цена поставщика (WhsDocumentSupplySpec_SuppPrice),
				// что вызывает в последующем ошибку в WhsDocumentUcPriceHistory_model. Добавляю этот параметр. Grigorev. 07.08.19. https://redmine.swan-it.ru/issues/165769
				data[i].WhsDocumentSupplySpec_SuppPrice = data[i].WhsDocumentSupplySpec_SuppPrice ? data[i].WhsDocumentSupplySpec_SuppPrice : '';

				store.insert(record_count++, new record(data[i]));
			}
			store.sort('WhsDocumentSupplySpec_PosCode','ASC');
			view_frame.setFilter();
			view_frame.updateSpecSumm(true);
		};

		getWnd('swWhsDocumentSupplySpecImportWindow').show(params);
	},
	doSave:  function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		var wnd = this;
		var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
		var viewframe =  wnd.SpecGrid;
		var viewframe_add =  wnd.findById('wdseWhsDocumentSupplyAdditionalGrid');

		if (!wnd.doCheckSave()) {return false;}
		
		wnd.prepareSaveDeliveryData();
		form.findField('SupplySpecJSON').setValue(viewframe.getJSONChangedData());
		form.findField('SupplyAdditionalJSON').setValue(viewframe_add.getJSONChangedData());

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentSupplyEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//проверка дат действия контракта
		var date_field = form.findField('WhsDocumentSupply_Date_Range');
		var err_msg = null;
		if (Ext.isEmpty(date_field.getValue1())) {
            err_msg = 'Не указана дата начала действия контракта';
		} else if (Ext.isEmpty(date_field.getValue2())) {
            err_msg = 'Не указана дата дата исполнения контракта';
		} else if (date_field.getValue1() < form.findField('WhsDocumentUc_Date').getValue()) {
            err_msg = 'Дата начала действия контракта не может быть раньше даты заключения контракта';
		}
		if (!Ext.isEmpty(err_msg)) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    date_field.focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: err_msg,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}

		viewframe.updateSumm();		
        this.submit(options);
		return true;		
	},
	submit: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		var wnd = this;
		wnd.getLoadMask(langs('Подождите, идет сохранение...')).show();
		var params = new Object();

		params.action = wnd.action;
		params.WhsDocumentUc_Sum = wnd.form.findField('WhsDocumentUc_Sum').getValue();
		params.WhsDocumentStatusType_id = wnd.form.findField('WhsDocumentStatusType_id').getValue();
		params.WhsDocumentSupply_ProtNum = wnd.form.findField('WhsDocumentSupply_ProtNum').getValue();
		params.WhsDocumentSupply_ProtDate = wnd.form.findField('WhsDocumentSupply_ProtDate').getValue() ? wnd.form.findField('WhsDocumentSupply_ProtDate').getValue().dateFormat('d.m.Y') : null;
		params.Org_sid = wnd.form.findField('Org_sid').getValue();
		params.Org_cid = wnd.form.findField('Org_cid').getValue();
		params.Org_pid = wnd.form.findField('Org_pid').getValue();
		params.Org_rid = wnd.form.findField('Org_rid').getValue();
		params.DrugFinance_id = wnd.form.findField('DrugFinance_id').getValue();
		params.WhsDocumentCostItemType_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();
		params.BudgetFormType_id = wnd.form.findField('BudgetFormType_id').getValue();
		params.WhsDocumentPurchType_id = wnd.form.findField('WhsDocumentPurchType_id').getValue();
		params.WhsDocumentUc_pid = wnd.form.findField('WhsDocumentUc_pid').getValue();
		params.DrugNds_id = wnd.form.findField('DrugNds_id').getValue();

		params.WhsDocumentUc_Num = wnd.form.findField('WhsDocumentUc_Num').getValue();
		params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue() ? wnd.form.findField('WhsDocumentUc_Date').getValue().dateFormat('d.m.Y') : null;

        var date_field = wnd.form.findField('WhsDocumentSupply_Date_Range');
        params.WhsDocumentSupply_BegDate = Ext.util.Format.date(date_field.getValue1(), 'd.m.Y');
        params.WhsDocumentSupply_ExecDate = Ext.util.Format.date(date_field.getValue2(), 'd.m.Y');

		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				
				if (action.result && action.result.WhsDocumentSupply_id > 0) {
					var id = action.result.WhsDocumentSupply_id;
					wnd.FileUploadPanel.listParams = {
						ObjectName: 'WhsDocumentSupply',
						ObjectID: id
					};
					wnd.FileUploadPanel.saveChanges();

					wnd.form.findField('WhsDocumentSupply_id').setValue(id);
					if (!options.onlySign) {
						wnd.callback(wnd.owner, id, params);
						wnd.hide();
					}
					
					if (typeof options.callback == 'function' ) {
						options.callback();
					}

					if (typeof wnd.afterSave == 'function' ) {
						wnd.afterSave({WhsDocumentUc_id: wnd.WhsDocumentUc_id || id});
					}
				}
			}
		});
	},
	show: function() {
        var wnd = this;

		sw.Promed.swWhsDocumentSupplyEditWindow.superclass.show.apply(this, arguments);		

		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentSupply_id = null;
		this.autoSupplyName = null;
		this.WhsDocumentType_id = null;
		this.delivery_graph = null;
		this.ImportDT_Exists = false;
		this.CommercialOffer_id = null;
		this.show_params = new Object();

		if ( !arguments[0] ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { wnd.hide(); });
            return false;
        } else {
            this.show_params = arguments[0];
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].afterSave && typeof arguments[0].afterSave == 'function' ) {
			this.afterSave = arguments[0].afterSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		this.isOstat = arguments[0].isOstat || false;
		if ( arguments[0].WhsDocumentSupply_id ) {
			this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
			var pidParams = {WhsDocumentSupply_id: wnd.WhsDocumentSupply_id};
		} else {
			var pidParams = {};
		}
		if(arguments[0].CommercialOffer_id) {
			this.CommercialOffer_id = arguments[0].CommercialOffer_id;
		}
		this.form.findField('WhsDocumentUc_pid').getStore().load({
			params: pidParams,
			callback: function(){
				if(getGlobalOptions().orgtype == 'lpu'){
					var org_id = getGlobalOptions().org_id;
					this.form.findField('WhsDocumentUc_pid').getStore().each(function(rec){
						if(rec.get('Org_id') != org_id){
							wnd.form.findField('WhsDocumentUc_pid').getStore().remove(rec);
						}
					});
				} else {
					this.form.findField('WhsDocumentUc_pid').getStore().each(function(rec){
						if(rec.get('OrgType_SysNick') == 'lpu'){
							wnd.form.findField('WhsDocumentUc_pid').getStore().remove(rec);
						}
					});
				}
			}.createDelegate(this)
		});
        
        
		if (arguments[0].WhsDocumentType_id) {
			wnd.WhsDocumentType_id = arguments[0].WhsDocumentType_id*1;
			switch(wnd.WhsDocumentType_id) {
				case 3:
					wnd.setTitle(langs('Контракт на поставку'));
					break;
				case 6:
					wnd.setTitle(langs('Контракт на поставку и отпуск'));
					break;
				case 19:
					wnd.setTitle(langs('Контракт ввода остатков'));
					break;
				default:
					wnd.setTitle(langs('Контракт на поставку'));
					break;
			}
		} else {
			wnd.setTitle(langs('Контракт'));
		}

		//определяем видимость кнопки подписания и снятия подписания
		if (wnd.WhsDocumentType_id == 3 || wnd.WhsDocumentType_id == 6) {
			wnd.buttons[2].show();
			wnd.buttons[3].show();
		} else {
			wnd.buttons[2].hide();
			wnd.buttons[3].hide();
		}

		this.form.reset();

		this.form.findField('Org_sid').getStore().baseParams.WithoutOrgEndDate = true;
		this.form.findField('Org_sid').getStore().baseParams.OrgType_Code = 16; //16 - Поставщик
		this.form.findField('Org_sid').getStore().baseParams.Org_pid = 0;  //исключаем дочерние организации
		this.form.findField('Org_cid').getStore().baseParams.WithoutOrgEndDate = true;
		this.form.findField('Org_pid').getStore().baseParams.WithoutOrgEndDate = true;
		this.form.findField('Org_rid').getStore().baseParams.WithoutOrgEndDate = true;

		this.form.findField('Org_sid').getStore().proxy.conn.url = C_ORG_LIST;
		this.form.findField('Org_cid').getStore().proxy.conn.url = C_ORG_LIST;
		this.form.findField('Org_pid').getStore().proxy.conn.url = C_ORG_LIST;
		this.form.findField('Org_rid').getStore().proxy.conn.url = C_ORG_LIST;

	//	wnd.getLoadMask('Загрузка...').show(); //закоментил - постоянно висит чё то
		var form_disabled = false;
		this.FileUploadPanel.reset();
		//this.form.findField('WhsDocumentUc_pid').getStore().removeAll();

		wnd.SpecGrid.addActions({
			name: 'action_wdssd_edit',
			text: 'Список медикаментов-синонимов',
			iconCls: 'edit16',
			handler: function() {
                var selected_record = wnd.SpecGrid.getGrid().getSelectionModel().getSelected();
                if (selected_record.get('WhsDocumentSupplySpec_id') > 0 && selected_record.get('state') != 'add') {
                    getWnd('swWhsDocumentSupplySpecDrugViewWindow').show({
                        'WhsDocumentSupply_id': selected_record.get('WhsDocumentSupply_id'),
                        'WhsDocumentSupplySpec_id': selected_record.get('WhsDocumentSupplySpec_id')
                    });
                }
			}
		});

        wnd.SpecGrid.addActions({
            name: 'action_import',
            text: langs('Импорт'),
            iconCls: 'add16',
            handler: function() {
                wnd.doImport();
            }
        });

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.title + langs(': Добавление'));
				var viewframe = wnd.SpecGrid;
				viewframe.removeAll();
				this.findById('wdseWhsDocumentSupplyAdditionalGrid').removeAll();
				this.setEnableBlockedByDocumentStatus();
				this.setDisabled(form_disabled);
				wnd.setDefaultValues();
				wnd.setDemand(0);
				this.form.setValues(arguments[0]);
				wnd.form.findField('WhsDocumentStatusType_id').setValue(1);
				wnd.form.findField('DrugNds_id').setFirstValue();
				wnd.form.findField('DeliveryGraphType').setAllowBlank(wnd.WhsDocumentType_id && (wnd.WhsDocumentType_id == 6 || wnd.WhsDocumentType_id == 19));
				//wnd.form.findField('WhsDocumentUc_pid').setAllowBlank((wnd.WhsDocumentType_id && wnd.WhsDocumentType_id == 19) || getRegionNick() == 'ufa');
				wnd.form.findField('DrugNds_id').setAllowBlank(wnd.WhsDocumentType_id && wnd.WhsDocumentType_id == 19);

				wnd.form.findField('DrugFinance_id').setValue(arguments[0].DrugFinance_id || null);
				wnd.form.findField('WhsDocumentCostItemType_id').setValue(arguments[0].WhsDocumentCostItemType_id || null);
				wnd.form.findField('BudgetFormType_id').setValue(1);
				
				wnd.setDeliveryData(null);
				if(arguments[0].DeliveryGraphType && arguments[0].DeliveryGraphType == 'percent'){
					wnd.changeDeliveryGraphType(wnd.form.findField('DeliveryGraphType'), 'percent');
				}
				//производим спец настройки графика поставки для типа "Контракт на поставку и отпуск"
				if (wnd.WhsDocumentType_id == '6') {
					wnd.changeDeliveryGraphType(wnd.form.findField('DeliveryGraphType'), 'percent');
				}

                wnd.FileUploadPanel.listParams = {
                    ObjectName: 'WhsDocumentSupply',
                    ObjectID: null
                };

                wnd.buttons[3].hide();
				wnd.getLoadMask().hide();
			break;
			case 'view':
				form_disabled = true;
			case 'edit':
				this.setTitle(this.title + (this.action == 'edit' ? langs(': Редактирование') : langs(': Просмотр')));
				//загружаем файлы				
				this.FileUploadPanel.listParams = {
					ObjectName: 'WhsDocumentSupply',
					ObjectID: wnd.WhsDocumentSupply_id,
					callback: function() {
						wnd.setEnableBlockedByDocumentStatus();
						wnd.setDisabled(form_disabled);
						wnd.setEnableFinCombo(); //повторный вызов на тот случай если файлы будут загружаться дольше основных данных
					}
				};	
				this.FileUploadPanel.loadData();
				
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						wnd.getLoadMask().hide();
						wnd.hide();
					},
					params:{
						WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						wnd.form.setValues(result[0]);
						
						wnd.setOrgValueById(wnd.form.findField('Org_sid'), result[0].Org_sid);
						wnd.setOrgValueById(wnd.form.findField('Org_cid'), result[0].Org_cid);
						wnd.setOrgValueById(wnd.form.findField('Org_pid'), result[0].Org_pid);
						wnd.setOrgValueById(wnd.form.findField('Org_rid'), result[0].Org_rid);

                        wnd.form.findField('WhsDocumentSupply_Date_Range').setValueByDates({
							BegDate: !Ext.isEmpty(result[0].WhsDocumentSupply_BegDate) ? result[0].WhsDocumentSupply_BegDate : result[0].WhsDocumentUc_Date,
							EndDate: result[0].WhsDocumentSupply_ExecDate
                        });
						
						wnd.SpecGrid.loadData({
							globalFilters: {
								WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
							}
						});
						wnd.findById('wdseWhsDocumentSupplyAdditionalGrid').loadData({
							globalFilters: {
								ParentWhsDocumentSupply_id: wnd.WhsDocumentSupply_id
							}
						});

						wnd.WhsDocumentType_id = result[0].WhsDocumentType_id;
						wnd.ImportDT_Exists = (result[0].ImportDT_Exists == 'true');//result[0].ImportDT_Exists;
						wnd.form.findField('DeliveryGraphType').setAllowBlank(wnd.WhsDocumentType_id && (wnd.WhsDocumentType_id == 6 || wnd.WhsDocumentType_id == 19));
						//wnd.form.findField('WhsDocumentUc_pid').setAllowBlank(wnd.WhsDocumentType_id && wnd.WhsDocumentType_id == 19);
						wnd.form.findField('DrugNds_id').setAllowBlank(wnd.WhsDocumentType_id && wnd.WhsDocumentType_id == 19);
						
						wnd.setDemand(wnd.form.findField('DocumentUc_id').getValue());
						wnd.setDeliveryData(result[0]);
						
						// если статус документа не новый, то убираем подписание.
						if (wnd.form.findField('WhsDocumentStatusType_id').getValue() != 1) {
							wnd.buttons[2].hide();
						}

						// если статус документа не действующий, то убираем снятие подписание.
						if (wnd.form.findField('WhsDocumentStatusType_id').getValue() != 2) {
							wnd.buttons[3].hide();
						}

						wnd.setEnableBlockedByDocumentStatus();
						wnd.setEnableFinCombo();
						wnd.setDisabled(form_disabled);

						if(wnd.ImportDT_Exists && getRegionNick() == 'kz') //Если есть дата и время импорта
						{
							wnd.form.findField('WhsDocumentPurchType_id').disable();
							wnd.form.findField('WhsDocumentUc_Num').disable();
							wnd.form.findField('WhsDocumentUc_Date').disable();
							wnd.form.findField('WhsDocumentSupply_Date_Range').disable();
							wnd.form.findField('Org_sid').disable();
							wnd.form.findField('Org_cid').disable();
							wnd.form.findField('Org_pid').disable();
							wnd.form.findField('Org_rid').disable();
							wnd.form.findField('WhsDocumentUc_Sum').disable();
							wnd.form.findField('DrugNds_id').disable();
							wnd.form.findField('Nds_Sum').disable();
							//wnd.SpecGrid.setActionHidden('action_add',true);
							//wnd.SpecGrid.setActionHidden('action_edit',true);
							wnd.SpecGrid.setActionHidden('action_delete',true);
							//wnd.SpecGrid.setActionHidden('action_import',true);
						}
						else
						{
							wnd.form.findField('WhsDocumentPurchType_id').enable();
							wnd.form.findField('WhsDocumentUc_Num').enable();
							wnd.form.findField('WhsDocumentUc_Date').enable();
							wnd.form.findField('WhsDocumentSupply_Date_Range').enable();
							wnd.form.findField('Org_sid').enable();
							wnd.form.findField('Org_cid').enable();
							wnd.form.findField('Org_pid').enable();
							wnd.form.findField('Org_rid').enable();
							wnd.form.findField('WhsDocumentUc_Sum').enable();
							wnd.form.findField('DrugNds_id').enable();
							wnd.form.findField('Nds_Sum').enable();
							wnd.SpecGrid.setActionHidden('action_add',false);
							wnd.SpecGrid.setActionHidden('action_edit',false);
							wnd.SpecGrid.setActionHidden('action_delete',false);
							//wnd.SpecGrid.setActionHidden('action_import',false);
						}
						
						wnd.getLoadMask().hide();
					},
					url:'/?c=WhsDocumentSupply&m=load'
				});				
			break;	
		}	

		if(getRegionNick() == 'kz')
			wnd.SpecGrid.setActionHidden('action_import',true);
		else
			wnd.SpecGrid.setActionHidden('action_import',false);
        //Дороботка Уфы - если форма открыта из грида с лотами
       
        //Кусок кода, отвечающий за создание контракта при клике из грида лотов  #68102
        if(arguments[0].WhsDocumentUc_id){
            
            this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
           	this.form.findField('WhsDocumentUc_pid').getStore().on({
            	   'load' : function(){
                        var rec = this.getById(wnd.WhsDocumentUc_id);
                        if(rec){
                        	wnd.form.findField('WhsDocumentUc_pid').setValue(rec.get('WhsDocumentProcurementRequest_id'));

	                        //Не смог заставить работать fireEvent на Select
	                        //this.form.findField('WhsDocumentUc_pid').fireEvent("select");
	                        
	                    	var fin_combo = wnd.form.findField('DrugFinance_id');
							var cost_combo = wnd.form.findField('WhsDocumentCostItemType_id');
							var fin_id = rec.get('DrugFinance_id');
							var cost_id = rec.get('WhsDocumentCostItemType_id');

							if (fin_id > 0) {
								fin_combo.disable();
								fin_combo.setValue(fin_id);
							} else {
								fin_combo.enable();
								fin_combo.setValue(null);
							}

							if (cost_id > 0) {
								cost_combo.disable();
								cost_combo.setValue(cost_id);
							} else {
								cost_combo.enable();
								cost_combo.setValue(null);
							}
                        } else {
                        	Ext.Msg.alert(langs('Сообщение'), 'Для данного лота уже существует контракт. Создание нового контракта невозможно.');
							wnd.hide();
                        }
                        
            	   }
            });  
        }
        
        if(getRegionNick() == 'kz')
        {
        	wnd.form.findField('CommercialOffer_id').getStore().removeAll();
        	wnd.form.findField('CommercialOffer_id').getStore().load({
        		callback: function() {
        			wnd.form.findField('CommercialOffer_id').setValue(wnd.CommercialOffer_id);
        			wnd.form.findField('CommercialOffer_id').fireEvent('change',wnd.form.findField('CommercialOffer_id'),wnd.form.findField('CommercialOffer_id').getValue());
        		}
        	});
        }       
        	
	},
    
	initComponent: function() {
		var wnd = this;
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			id: 'wdseFileUploadPanel',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile'
		});

		this.SpecGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.SpecGrid.editGrid('add') }},
				{name: 'action_edit', handler: function() { wnd.SpecGrid.editGrid('edit') }},
				{name: 'action_view', handler: function() { wnd.SpecGrid.editGrid('view') }},
				{name: 'action_delete', handler: function() { wnd.SpecGrid.deleteRecord() }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentSupplySpec&m=loadList',
			height: 180,
			region: 'north',
			object: 'WhsDocumentSupplySpec',
			editformclassname: 'swWhsDocumentSupplySpecEditWindow',
			id: 'wdseWhsDocumentSupplySpecGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'WhsDocumentSupplySpec_id', type: 'int', header: 'ID', key: true},
				{name: 'grid_id', type: 'int', header: 'grid_id', dataIndex: 'WhsDocumentSupplySpec_id', hidden: true},
				{name: 'state', type: 'string', header: 'state', hidden: !this.debug_mode},
				{name: 'WhsDocumentSupply_id', type: 'int', hidden: !this.debug_mode},
				{name: 'WhsDocumentProcurementRequestSpec_id', type: 'int', hidden: !this.debug_mode},
				{name: 'Drug_id', type: 'int', header: 'Drug_id', hidden: !this.debug_mode},
				{name: 'DrugComplexMnn_id', type: 'int', header: 'DrugComplexMnn_id', hidden: !this.debug_mode},
				{name: 'FIRMNAMES_id', type: 'int', header: 'FIRMNAMES_id', hidden: !this.debug_mode},
				{name: 'DRUGPACK_id', type: 'int', header: 'DRUGPACK_id', hidden: !this.debug_mode},
				{name: 'Okei_id', type: 'int', header: 'Okei_id', hidden: !this.debug_mode},
				{name: 'graph_data', type: 'string', header: 'graph_data', hidden: true},

				{name: 'WhsDocumentSupplySpec_KolvoForm', type: 'float', header: langs('Количество единиц форм выпуска в упаковке'), hidden: !this.debug_mode, width: 120},
				{name: 'Okei_id_Name', type: 'string', header: langs('Единица поставки (ОКЕИ)'), hidden: !this.debug_mode, width: 120},
				{name: 'WhsDocumentSupplySpec_KolvoMin', type: 'float', header: langs('Количество поставляемых минимальных упаковок'), hidden: !this.debug_mode, width: 120},
				{name: 'WhsDocumentSupplySpec_ShelfLifePersent', type: 'int', header: langs('Остаточный срок хранения не менее (%)'), hidden: true, width: 120},

				{name: 'WhsDocumentSupplySpec_PosCode', type: 'float', header: langs('№ п/п'), width: 50},
				{name: 'DrugNomen_Code', type: 'string', header: langs('Код'), width: 50},
				{name: 'Drug_Name', type: 'string', hidden: true},
				{name: 'Actmatters_id', hidden: true},
				{name: 'ActMatters_RusName', type: 'string', header: langs('МНН'), width: 125},
				{name: 'Tradename_Name', type: 'string', header: langs('Торг. наименование'), width: 125},
				{name: 'Firm_Name', type: 'string', header: langs('Производитель'), width: 125},
				{name: 'Reg_Num', type: 'string', header: langs('№ РУ'), width: 100},
				{name: 'DrugForm_Name', type: 'string', header: langs('Лекарственная форма'), width: 140},
				{name: 'Drug_Dose', type: 'string', header: langs('Дозировка'), width: 75},
				{name: 'Drug_Fas', type: 'string', header: langs('Фасовка'), width: 75},

				{name: 'CommercialOfferDrug_PriceDetail', header: 'CommercialOfferDrug_PriceDetail', type: 'string', hidden: 'true'},

				{name: 'WhsDocumentSupplySpec_KolvoUnit', type: 'float', header: langs('Кол-во (уп.)'), width: 75},
				{name: 'WhsDocumentSupplySpec_Price', type: 'float', header: langs('Цена без НДС'), width: 120},
				{name: 'WhsDocumentSupplySpec_NDS', type: 'float', header: 'НДС', width: 50}, //ставка НДС (%);
				{name: 'WhsDocumentSupplySpec_PriceNDS', type: 'float', header: 'Цена с НДС', width: 100}, //опт. цена с НДС;
				{name: 'WhsDocumentSupplySpec_SumNDS', type: 'float', header: 'Сумма с НДС', width: 100}, //сумма с НДС;
				{name: 'WhsDocumentSupplySpec_Count', hidden: true},
				{name: 'GoodsUnit_id', hidden: true},
				{name: 'WhsDocumentSupplySpec_GoodsUnitQty', hidden: true},
				{name: 'WhsDocumentSupplySpec_SuppPrice', hidden: true},
				{name: 'calc_dose_count', type: 'string', header: 'Кол-во доз', hidden: !this.debug_mode}, //кол-во доз - кол-во доз=кол-во уп.*кол-во доз в упковке);
				{name: 'calc_material_count', type: 'string', header: 'Кол-во действ.в-ва', hidden: !this.debug_mode} //кол-во действ.в-ва  - кол-во действ.в-ва в единице фасовки*кол-во ед.фасовки в упаковке*кол-во упаковок);
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('WhsDocumentSupplySpec_id') > 0) {
					this.ViewActions.action_edit.setDisabled(this.readOnly);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(this.readOnly);
					this.ViewActions.action_wdssd_edit.setDisabled(record.get('state') == 'add');
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
					this.ViewActions.action_wdssd_edit.setDisabled(true);
				}

				this.ViewActions.action_import.setDisabled(this.readOnly);
			},
			updateSumm: function() {
				var summ = 0;
				var nds_summ = 0;
				var form = wnd.findById('WhsDocumentSupplyEditForm').getForm();
				var summ_field = form.findField('WhsDocumentUc_Sum');
				var nds_summ_field = form.findField('Nds_Sum');
				this.getGrid().getStore().each(function(record) {
                    var nds = record.get('WhsDocumentSupplySpec_NDS') > 0 ? record.get('WhsDocumentSupplySpec_NDS')*1 : 0;
					if(record.get('WhsDocumentSupplySpec_SumNDS') && record.get('WhsDocumentSupplySpec_SumNDS') > 0) {
						summ += (record.get('WhsDocumentSupplySpec_SumNDS') * 1);
                        nds_summ += (record.get('WhsDocumentSupplySpec_SumNDS')*nds)/(100+(nds));
					}
				});
				summ_field.setValue(summ.toFixed(2));
				nds_summ_field.setValue(nds_summ.toFixed(2));
			},
			editGrid: function (action) {
				var proc_combo = wnd.form.findField('WhsDocumentUc_pid');

				if (action == null) {
					action = 'add';
				}

				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSupplySpec_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					var params = new Object();
					params.WhsDocumentProcurementRequest_id = proc_combo.getValue();
					params.WhsDocumentSupplySpec_PosCode = record_count + 1;
					params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
					params.DrugNds_id = wnd.form.findField('DrugNds_id').getValue();
					params.Org_cid = wnd.form.findField('Org_cid').getValue();

					params.showPriceDrugs = (wnd.form.findField('CommercialOffer_id').getRawValue('CommercialOffer_Name') == 'Прайс лист СК-Фармация на 2017 год');

					getWnd(view_frame.editformclassname).show({
                        owner: view_frame,
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('WhsDocumentSupplySpec_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);
							view_frame.clearFilter();
							data.WhsDocumentSupplySpec_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
							data.grid_id = data.WhsDocumentSupplySpec_id;
							data.state = 'add';
							store.insert(record_count, new record(data));
							store.sort('WhsDocumentSupplySpec_PosCode','ASC');
							view_frame.setFilter();
							view_frame.updateSumm();
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('WhsDocumentSupplySpec_id') > 0) {
						var params = selected_record.data;
						params.WhsDocumentProcurementRequest_id = wnd.form.findField('WhsDocumentUc_pid').getValue();
						params.WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
						params.showPriceDrugs = (wnd.form.findField('CommercialOffer_id').getRawValue('CommercialOffer_Name') == 'Прайс лист СК-Фармация на 2017 год');
						params.CommercialOfferDrug_PriceDetail = selected_record.get('CommercialOfferDrug_PriceDetail');
						getWnd(view_frame.editformclassname).show({
                            owner: view_frame,
							action: action,
							params: params,
							callback: function(data) {
								view_frame.clearFilter();
								for(var key in data) {
									selected_record.set(key, data[key]);
								}
								if (selected_record.get('state') != 'add') {
									selected_record.set('state', 'edit');
								}
								selected_record.commit();
								store.sort('WhsDocumentSupplySpec_PosCode','ASC');
								view_frame.setFilter();
								view_frame.updateSumm();
							}
						});
					}
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				wnd.removeGridId(selected_record.get('grid_id'));
				if (selected_record.get('state') == 'add') {
					view_frame.getGrid().getStore().remove(selected_record);
				} else {
					selected_record.set('state', 'delete');
					selected_record.commit();
					view_frame.setFilter();
					view_frame.updateSumm();
				}
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete' || record.data.graph_data != ''))
						data.push(record.data);
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
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
				id: 'WhsDocumentSupplyEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6; padding: 5px;',
				border: false,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				region: 'north',
				url:'/?c=WhsDocumentSupply&m=save',
				layout: 'form',
				items: [{
					id: 'wdseWhsDocumentUc_id',
					name: 'WhsDocumentUc_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'wdseWhsDocumentSupply_id',
					name: 'WhsDocumentSupply_id',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'wdseSupplySpecJSON',
					name: 'SupplySpecJSON',
					value: null,
					xtype: 'hidden'
				}, {
					id: 'wdseSupplyAdditionalJSON',
					name: 'SupplyAdditionalJSON',
					value: null,
					xtype: 'hidden'
				}, {
					layout: 'form',
					//xtype: 'fieldset',
					autoHeight: true,
					//title: 'Заголовок договора',
					style: 'background:#DFE8F6; padding: 3px; margin-bottom:7px; display:block;',
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'textfield',
								fieldLabel : langs('Тип'),
								width: 792,
								name: 'WhsDocumentType_Name',
								disabled: true
							}]
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Вид закупа'),
							tabIndex: wnd.firstTabIndex + 10,
							hiddenName: 'WhsDocumentPurchType_id',
							comboSubject: 'WhsDocumentPurchType',
							width: 250,
							allowBlank: false
						}]
					}, {
						layout: 'column',
						labelWidth: 180,
						width: 1000,
						border: false,
						items: [/*{
							layout: 'form',
							border: false,
							width: 385,
							items: [,
							{
								fieldLabel: langs('Тип'),
								tabIndex: wnd.firstTabIndex + 10,
								hiddenName: 'WhsDocumentType_id',
								id: 'wdseWhsDocumentType_id',
								xtype: 'swcommonsprcombo',
								sortField:'WhsDocumentType_Code',
								comboSubject: 'WhsDocumentType',
								width: 200,
								allowBlank:true,
								initComponent: function() {
									sw.Promed.SwCommonSprCombo.prototype.initComponent.apply(this, arguments);									
									this.store.addListener('load', function(store){
										store.removeAt(store.findBy(function(rec) { return rec.get('WhsDocumentType_Code') == 4; })); // График поставки
										store.removeAt(store.findBy(function(rec) { return rec.get('WhsDocumentType_Code') == 5; })); // Котировочная заявка
									});
								}
							}]
						}, */
						{
							layout: 'form',
							border: false,
							width: 304,
							//labelWidth: 30,
							items: [{
								xtype: 'hidden',
								name: 'WhsDocumentType_id'
							}, {
								xtype: 'textfield',
								fieldLabel : langs('№'),
								width: 114,
								tabIndex: wnd.firstTabIndex + 10,
								name: 'WhsDocumentUc_Num',
								id: 'wdseWhsDocumentUc_Num',
								value: '',
								allowBlank:false,
								listeners: {
									'change': function() {
										wnd.setSupplyName();
									}
								}
							}]						
						}, {
							layout: 'form',
							border: false,
							width: 135,
							labelWidth: 30,
							items: [{
								xtype: 'swdatefield',
								fieldLabel : langs('От'),
								tabIndex: wnd.firstTabIndex + 10,
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'WhsDocumentUc_Date',
								id: 'wdseWhsDocumentUc_Date',
								allowBlank: false,
								listeners: {
									'change': function() {
										wnd.setSupplyName();
										//в зависимости от даты корректируем варианты ставок НДС, задача https://redmine.swan-it.ru/issues/162095
										WhsDocumentUc_Date = wnd.form.findField('WhsDocumentUc_Date').getValue();
										DrugNdsCombo = wnd.form.findField('DrugNds_id');
										if (!Ext.isEmpty(WhsDocumentUc_Date)) {
											DrugNdsCombo.getStore().removeAll();
											DrugNdsCombo.getStore().load();
										}
										DrugNdsCombo.getStore().filterBy(function (rec) {
											if (WhsDocumentUc_Date > Date.parse('2018-12-31')) {
												return rec.get('DrugNds_id') != '3';           //если не равно 18%
											} else {
												return rec.get('DrugNds_id') != '4';           //если не равно 20%
											}
										});

										//если первая дата в периоде действия пуста или раньше даты контракта, то меняем её на дату равную дате контракта
                                        var range_field = wnd.form.findField('WhsDocumentSupply_Date_Range');
                                        var Range_Date = range_field.getValue1();
										if (!Ext.isEmpty(WhsDocumentUc_Date) && (Ext.isEmpty(Range_Date) || Range_Date < WhsDocumentUc_Date)) {
                                            range_field.setValueByDates({
                                                BegDate: WhsDocumentUc_Date.format('d.m.Y'),
                                            });
										}
									}
								}
							}]
						}, {
							layout: 'form',
							border: false,
							width: 291,
							labelWidth: 111,
							items: [{
								xtype: 'daterangefield',
								fieldLabel : langs('Период действия'),
								tabIndex: wnd.firstTabIndex + 10,
								format: 'd.m.Y',
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								name: 'WhsDocumentSupply_Date_Range',
								allowBlank: false,
                                width: 170,
								setValueByDates: function(dates) {
                                    var dates_str = "";
                                    if(!Ext.isEmpty(dates.BegDate)){
                                        dates_str += dates.BegDate;
                                    } else {
                                        var beg_str = "__.__.____";
                                        if (dates.BegDate === undefined && !Ext.isEmpty(this.getValue1())) {
                                            beg_str = Ext.util.Format.date(this.getValue1(),'d.m.Y');
                                        }
                                        dates_str += beg_str;
                                    }
                                    dates_str += " - ";
                                    if(!Ext.isEmpty(dates.EndDate)){
                                        dates_str += dates.EndDate;
                                    } else {
                                    	var end_str = "__.__.____";
                                    	if (dates.EndDate === undefined && !Ext.isEmpty(this.getValue2())) {
                                            end_str = Ext.util.Format.date(this.getValue2(),'d.m.Y');
										}
                                        dates_str += end_str;
                                    }
                                    this.setValue(dates_str);
								}
							}]
						}, {
							layout: 'form',
							border: false,
							width: 250,
							labelWidth: 115,
							items: [{
								fieldLabel: langs('Статус документа'),
								tabIndex: wnd.firstTabIndex + 10,
								hiddenName: 'WhsDocumentStatusType_id',
								id: 'wdseWhsDocumentStatusType_id',
								xtype: 'swcommonsprcombo',
								sortField:'WhsDocumentStatusType_Code',
								comboSubject: 'WhsDocumentStatusType',
								width: 127,
								disabled: true,
								allowBlank:true
							}]
						}]
					}, {
						xtype: 'textarea',
						fieldLabel : langs('Наименование'),
						tabIndex: wnd.firstTabIndex + 10,
						width: 792,
						name: 'WhsDocumentUc_Name',
						id: 'wdseWhsDocumentUc_Name',						
						allowBlank:false,
						listeners: {
							'change': function(field, newValue, oldValue) {
								if (newValue != '') {
									wnd.autoSupplyName = false;
								}
							}
						}
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					frame: true,
					id: 'WDSE_InfPanel1',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: langs('Контрагенты, оплата, стоимость'),
					items: [{
						xtype: 'hidden',
						name: 'DocumentUc_id',
						id: 'wdseDocumentUc_id'
					},
					{
						layout: 'column',
						labelWidth: 180,
						border: false,
						hidden: getRegionNick()!='kz',
						items: [{
							layout: 'form',
							border: false,
							width: 600,
							items: [{
								anchor: '100%',
								allowBlank: true,
								fieldLabel: 'Прайс',
								xtype:'swbaselocalcombo',
								hiddenName: 'CommercialOffer_id',
								//moreFields: [],
								store: new Ext.data.JsonStore({
									url: '/?c=CommercialOffer&m=loadCommercialOfferList',
									editable: false,
									key: 'CommercialOffer_id',
									autoLoad: false,
									fields: [
										{name: 'CommercialOffer_id',    type:'int'},
										{name: 'CommercialOffer_Name',  type:'string'}
									],
									sortInfo: {
										field: 'CommercialOffer_Name'
									}
								}),
								listeners: {
									'change': function(combo, value) {
										
										if(!Ext.isEmpty(value) && value > 0)
										{
											wnd.form.findField('WhsDocumentSupply_ProtNum').hideContainer();
											wnd.form.findField('WhsDocumentSupply_ProtDate').hideContainer();
											wnd.form.findField('WhsDocumentUc_pid').hideContainer();
										}
										else
										{
											wnd.form.findField('WhsDocumentSupply_ProtNum').showContainer();
											wnd.form.findField('WhsDocumentSupply_ProtDate').showContainer();
											wnd.form.findField('WhsDocumentUc_pid').showContainer();
										}
									}
								},
								triggerAction: 'all',
								displayField:'CommercialOffer_Name',
								tpl: '<tpl for="."><div class="x-combo-list-item">'+'{CommercialOffer_Name}'+'</div></tpl>',
								valueField: 'CommercialOffer_id'
							}]
						}]
					},

					{
						layout: 'column',
						labelWidth: 180,
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 330,
							items: [{
								xtype: 'textfield',
								fieldLabel : langs('Протокол №'),
								tabIndex: wnd.firstTabIndex + 10,
								name: 'WhsDocumentSupply_ProtNum',
								id: 'wdseWhsDocumentSupply_ProtNum',
								value: '',
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 140,
							labelWidth: 30,
							items: [{
								xtype: 'swdatefield',
								fieldLabel : langs('От'),
								tabIndex: wnd.firstTabIndex + 10,
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'WhsDocumentSupply_ProtDate',
								id: 'wdseWhsDocumentSupply_ProtDate',
								value: '',
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 540,
							labelWidth: 120,
							items: [{
								xtype: 'swwhsdocumentprocurementrequestcombo',
								fieldLabel : langs('Лот/Спецификация'),
								tabIndex: wnd.firstTabIndex + 10,
								hiddenName: 'WhsDocumentUc_pid',
								id: 'wdseWhsDocumentUc_pid',
								width: 379,
								listWidth: 500,
								editable: false,
								allowBlank: true,
								listeners: {
									'select': function(o, r, i) {
										var field_arr = [
											'DrugFinance_id',
											'WhsDocumentCostItemType_id',
											'BudgetFormType_id',
											'FinanceSource_id'
										];

										for(var i = 0; i < field_arr.length; i++) {
											var combo = wnd.form.findField(field_arr[i]);
											var id = r.get(field_arr[i]);

											if (id > 0) {
												combo.disable();
												combo.setValue(id);
											} else {
												if (combo.enable_blocked) {
													combo.disable();
												} else {
													combo.enable();
												}
												combo.setValue(null);
											}
										}
										//var data = {Org_id:r.get('Org_id'), Org_Name:''};
										wnd.setOrgValueById(wnd.form.findField('Org_cid'), r.get('Org_id'));
									},
									'beforequery': function(qe) { //для отключения фильтрации по выбранному значению
										qe.query = '';
									}
								}
							}]
						}]
					}, {
						xtype: 'sworgcomboex',
						fieldLabel : langs('Поставщик'),
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'Org_sid',
						id: 'wdseOrg_sid',
						width: 530,
						editable: true,
						allowBlank: false,
						tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
						emptyText: langs('Введите часть названия ...'),
						onTriggerClick: function() {
							if (this.disabled) {
								return false;
							}
							var combo = this;

							if (!this.formList) {
								this.formList = new sw.Promed.swListSearchWindow({
									title: langs('Поиск организации'),
									id: 'OrgSearch_' + this.id,
									object: 'Org',
									prefix: 'lsswdse1',
									editformclassname: 'swOrgEditWindow',
									disableActions: false,
									stringfields: [
										{name: 'Org_id',    type:'int'},
										{name: 'Org_Name',  type:'string'}
									],
									dataUrl: C_ORG_LIST
								});
							}
							this.formList.show({
								params: this.getStore().baseParams,
								onSelect: function(data) {
									wnd.setOrgValueByData(combo, data);
								}
							});
						}
					}, {
						xtype: 'sworgcomboex',
						fieldLabel : langs('Заказчик'),
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'Org_cid',
						id: 'wdseOrg_cid',
						width: 530,
						allowBlank: false,
						tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
						emptyText: langs('Введите часть названия ...'),
						onTriggerClick: function() {
							if (this.disabled) {
								return false;
							}
							var combo = this;

							if (!this.formList) {
								this.formList = new sw.Promed.swListSearchWindow({
									title: langs('Поиск организации'),
									id: 'OrgSearch_' + this.id,
									object: 'Org',
									prefix: 'lsswdse2',
									editformclassname: 'swOrgEditWindow',
									stringfields: [
										{name: 'Org_id',    type:'int'},
										{name: 'Org_Name',  type:'string'}
									],
									dataUrl: C_ORG_LIST
								});
							}
							this.formList.show({
								params: this.getStore().baseParams,
								onSelect: function(data) {
									wnd.setOrgValueByData(combo, data);
								}
							});
						}
					}, {
						xtype: 'sworgcomboex',
						fieldLabel : langs('Плательщик'),
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'Org_pid',
						id: 'wdseOrg_pid',
						width: 530,
						allowBlank: false,
						tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
						emptyText: langs('Введите часть названия ...'),
						onTriggerClick: function() {
							if (this.disabled) {
								return false;
							}
							var combo = this;

							if (!this.formList) {
								this.formList = new sw.Promed.swListSearchWindow({
									title: langs('Поиск организации'),
									id: 'OrgSearch_' + this.id,
									object: 'Org',
									prefix: 'lsswdse3',
									editformclassname: 'swOrgEditWindow',
									stringfields: [
										{name: 'Org_id',    type:'int'},
										{name: 'Org_Name',  type:'string'}
									],
									dataUrl: C_ORG_LIST
								});
							}
							this.formList.show({
								params: this.getStore().baseParams,
								onSelect: function(data) {
									wnd.setOrgValueByData(combo, data);
								}
							});
						}
					}, {
						xtype: 'sworgcomboex',
						fieldLabel : langs('Получатель'),
						tabIndex: wnd.firstTabIndex + 10,
						hiddenName: 'Org_rid',
						id: 'wdseOrg_rid',
						width: 530,
						allowBlank: true,
						tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
						emptyText: langs('Введите часть названия ...'),
						onTriggerClick: function() {
							if (this.disabled) {
								return false;
							}
							var combo = this;

							if (!this.formList) {
								this.formList = new sw.Promed.swListSearchWindow({
									title: langs('Поиск организации'),
									id: 'OrgSearch_' + this.id,
									object: 'Org',
									prefix: 'lsswdse4',
									editformclassname: 'swOrgEditWindow',
									stringfields: [
										{name: 'Org_id',    type:'int'},
										{name: 'Org_Name',  type:'string'}
									],
									dataUrl: C_ORG_LIST
								});
							}
							this.formList.show({
								params: this.getStore().baseParams,
								onSelect: function(data) {
									wnd.setOrgValueByData(combo, data);
								}
							});
						}
					}, {
						layout: 'column',						
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 440,
							items: [{
								fieldLabel: langs('Источник финансирования'),
								tabIndex: wnd.firstTabIndex + 10,
								hiddenName: 'DrugFinance_id',
								id: 'wdseDrugFinance_id',
								xtype: 'swcommonsprcombo',
								sortField:'DrugFinance_Code',
								comboSubject: 'DrugFinance',
								width: 250,
								allowBlank:false,
								listeners:{
									'change':function(){
										this.filterFinanceSource();
									}.createDelegate(this)
								}
							}]
						}, {
							layout: 'form',
							border: false,
							width: 400,
							labelWidth: 130,
							items: [{
								fieldLabel: langs('Статья расходов'),
								tabIndex: wnd.firstTabIndex + 10,
								hiddenName: 'WhsDocumentCostItemType_id',
								id: 'wdseWhsDocumentCostItemType_id',
								xtype: 'swcommonsprcombo',
								sortField:'WhsDocumentCostItemType_Code',
								comboSubject: 'WhsDocumentCostItemType',
								width: 250,
								allowBlank:true,
								listeners:{
									'change':function(){
										this.filterFinanceSource();
									}.createDelegate(this)
								}
							}]
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Целевая статья'),
							tabIndex: wnd.firstTabIndex + 10,
							hiddenName: 'BudgetFormType_id',
							comboSubject: 'BudgetFormType',
							width: 530,
							listeners:{
								'change':function(){
									this.filterFinanceSource();
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swfinancesourcecombo',
							fieldLabel: langs('Источник оплаты'),
							tabIndex: wnd.firstTabIndex + 10,
							hiddenName: 'FinanceSource_id',
							allowBlank: false,
							width: 530
						}]
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 336,
							items: [{
								xtype: 'textfield',
								fieldLabel : langs('Сумма') + ' (' + getCurrencyName() + ')',
								tabIndex: wnd.firstTabIndex + 10,
								name: 'WhsDocumentUc_Sum',
								value: '',
								allowBlank:true,
								disabled: true
							}]
						}, {
							layout: 'form',
							border: false,
							width: 316,
							items: [{
								xtype: 'swdrugndscombo',
								hiddenName: 'DrugNds_id',
								valueField: 'DrugNds_id',
								fieldLabel: 'Ставка НДС (%) по умолчанию',
								tabIndex: wnd.firstTabIndex + 10,
								allowBlank: false,
								width: 125,
                                setFirstValue: function() {
                                    if (this.getStore().getCount() > 0) {
                                        this.setValue(this.getStore().getAt(0).get(this.valueField));
                                    }
                                }
							}]
						}, {
							layout: 'form',
							border: false,
							width: 336,
							items: [{
								xtype: 'textfield',
								fieldLabel : langs('Сумма НДС'),
								name: 'Nds_Sum',
								value: '',
								allowBlank:true,
								disabled: true
							}]
						}]
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					frame: true,
					collapsible: true,
					id: 'WDSE_InfPanel2',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: langs('Спецификация контракта'),
					items: [{
						layout: 'column',
						labelWidth: 50,
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 405,
							items: [{
								xtype: 'swdrugcomplexmnncombo',									
								fieldLabel: langs('МНН'),
								width: 340,
								tabIndex: wnd.firstTabIndex + 10,								
								id: 'wdseDrugComplexMnn_id',
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
							width: 405,
							items: [{
								xtype: 'swdrugsimplecombo',
								fieldLabel : langs('Торг'),
								width: 340,
								tabIndex: wnd.firstTabIndex + 10,
								name: 'Drug_id',
								id: 'wdseDrug_id',
								value: '',
								allowBlank: true
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								style: "padding-left: 0px",
								xtype: 'button',
								id: 'wdseBtnSearch',
								text: langs('Поиск'),
								iconCls: 'search16',
								handler: function() {
									var win = Ext.getCmp('WhsDocumentSupplyEditWindow');
									var form = win.findById('WhsDocumentSupplyEditForm').getForm();
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
								id: 'wdseBtnClear',
								text: langs('Сброс'),
								iconCls: 'resetsearch16',
								handler: function() {
									var form = Ext.getCmp('WhsDocumentSupplyEditForm').getForm();
									form.findField('Drug_id').reset();
									form.findField('DrugComplexMnn_id').reset();
								}
							}]
						}]
					},
					wnd.SpecGrid
					]
				}),
				{
					xtype: 'fieldset',
					autoHeight: true,
					title: langs('График поставки'),
					style: 'padding: 3px; margin-bottom:2px; display:block;',
					items:
					[{
						layout: 'column',
						width: 950,
						border: false,
						items: [{
							layout: 'form',
							border: false,
							width: 480,
							labelWidth: 335,
							items: [{
								xtype: 'combo',
								width: 127,
								mode: 'local',
								typeAhead: true,
								triggerAction: 'all',
								lazyRender:true,
								store: new Ext.data.SimpleStore({
									id: 0,
									fields: [
										'code',
										'name'
									],
									data: [['percent', langs('в долях')], ['unit', langs('в количестве')]]
								}),
								valueField: 'code',
								displayField: 'name',
								fieldLabel : langs('График исполнения обязательств Поставщиком'),
								tabIndex: wnd.firstTabIndex + 10,
								name: 'DeliveryGraphType',
								id: 'wdseDeliveryGraphType',
								listeners: {
									'select': function(combo, newValue) {
										wnd.changeDeliveryGraphType(combo, newValue.id);
										return false;
									}
								},
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">{name}&nbsp;</div></tpl>'
								),
								allowBlank:false
							}, {
								xtype: 'hidden',								
								name: 'DeliveryData',
								id: 'wdseDeliveryData',
								value: '',
								allowBlank:true
							}]							
						}, {
							layout: 'form',
							border: false,
							width: 280,
								items: [{
								style: "padding-left: 0px",
								xtype: 'button',
								id: 'wdseBtnGraphEdit',
								text: langs('Редактировать'),
								iconCls: 'edit16',
								handler: function() {
									if (!wnd.delivery_graph || !wnd.delivery_graph.type) {
										sw.swMsg.alert(langs('Ошибка'), langs('Не выбран тип графика поставки'));
										return false;
									}										
									var grid = wnd.SpecGrid.getGrid();
									var spec_data = new Object();
									grid.getStore().each(function(r){
										if (r.get('state') != 'delete')
										spec_data[r.get('grid_id')] = {
											grid_id: r.get('grid_id'),
											spec_id: r.get('WhsDocumentSupplySpec_id'),
											name: r.get('Drug_Name'),
											dataIndex: r.get('Drug_Name'),
											total: r.get('WhsDocumentSupplySpec_KolvoUnit')
										}
									});
									spec_data.all = {
										grid_id: null,
										spec_id: null,
										name: langs('Все'),
										total: '100'
									};
																		
									var params = new Object();
									params.action = 'edit';
									params.graph_data = wnd.delivery_graph;
									params.spec_data = spec_data;
									params.onSave = function(graph_data) {										
										if (graph_data) {
											wnd.delivery_graph.data = new Ext.util.MixedCollection(true);
											graph_data.data.each(function(item){
												var obj = new Object();
												Ext.apply(obj, item);
												wnd.delivery_graph.data.add(obj);
											});
											wnd.delivery_graph.type = graph_data.type;
											wnd.delivery_graph.state = graph_data.state;
										}										
									};

									//для типа "Контракт на отпуск и поставку", при выборе графика в долях, при отсутствии какого либо ранее сохраненного графика устанавливаем график по умолчанию
									if (wnd.WhsDocumentType_id == '6' && !wnd.delivery_graph.data && wnd.delivery_graph.type == 'percent') {
										params.set_default_value = true;
									}

									getWnd('swWhsDocumentDeliveryGraphEditWindow').show(params);
								}
							}]
						}]
					}]
				},
				new sw.Promed.Panel({
					autoHeight: true,
					border: true,
					frame: true,
					collapsible: true,
					layout: 'form',
					style: 'margin-bottom: 0.5em; margin-top: 0.5em; padding-top: 0px;',
					title: langs('Дополнительные соглашения'),
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', hidden: true, disabled: true, handler: function() { this.findById('wdseWhsDocumentSupplyAdditionalGrid').editGrid('add') }.createDelegate(this)},
								{name: 'action_edit', hidden: true, disabled: true, handler: function() { this.findById('wdseWhsDocumentSupplyAdditionalGrid').editGrid('edit') }.createDelegate(this)},
								{name: 'action_view', handler: function() { this.findById('wdseWhsDocumentSupplyAdditionalGrid').editGrid('view') }.createDelegate(this)},
								{name: 'action_delete', hidden: true, disabled: true, handler: function() { this.findById('wdseWhsDocumentSupplyAdditionalGrid').askDeleteRecord() }.createDelegate(this)},
								{name: 'action_refresh', hidden: true},
								{name: 'action_print'}
							],
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							border: true,
							dataUrl: '/?c=WhsDocumentSupply&m=loadWhsDocumentSupplyAdditionalList',
							height: 180,
							object: 'WhsDocumentSupply',
							editformclassname: 'swWhsDocumentSupplyAdditionalEditWindow',
							id: 'wdseWhsDocumentSupplyAdditionalGrid',
							paging: false,
							saveAtOnce:false,
							style: 'margin-bottom: 0px; margin-top: 0px;',
							stringfields: [
								{name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true},
								{name: 'WhsDocumentSupply_id', type: 'int', hidden: true},
								{name: 'WhsDocumentUc_Num', type: 'string', header: langs('№'), width: 300},
								{name: 'WhsDocumentUc_Name', type: 'string', header: langs('Наименование'), id: 'autoexpand'},
								{name: 'WhsDocumentUc_Date', type: 'string', header: langs('Дата')},
								{name: 'state', type: 'string', header: 'state', hidden: true}
							],
							toolbar: true,
							onRowSelect: function(sm,rowIdx,record) {
								if (record.get('WhsDocumentSupply_id') > 0) {
									this.ViewActions.action_view.setDisabled(false);
								} else {
									this.ViewActions.action_view.setDisabled(true);
								}
							},
							askDeleteRecord: function() {
								var grid = this;
								sw.swMsg.show({
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Вы хотите удалить запись?'),
									title: langs('Подтверждение'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' == buttonId) {
											grid.deleteRecord();
										}
									}
								});
							},
							editGrid: function (action) {
								if (action == null)	action = 'add';

								var view_frame = this;
								var store = view_frame.getGrid().getStore();

								if (action == 'add') {
									var record_count = store.getCount();
									if ( record_count == 1 && !store.getAt(0).get('WhsDocumentUc_id') ) {
										view_frame.removeAll({ addEmptyRecord: false });
										record_count = 0;
									}

									var params = new Object();
									params.WhsDocumentUc_pid = wnd.form.findField('WhsDocumentUc_id').getValue();

									getWnd(view_frame.editformclassname).show({
										action: action,
										params: params,
										callback: function(data) {
											if ( record_count == 1 && !store.getAt(0).get('WhsDocumentUc_id') ) {
												view_frame.removeAll({ addEmptyRecord: false });
											}
											var record = new Ext.data.Record.create(view_frame.jsonData['store']);
											view_frame.clearFilter();
											data.WhsDocumentUc_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
											data.state = 'add';
											view_frame.getGrid().getStore().insert(record_count, new record(data));
											view_frame.setFilter();
										}
									});
								}
								if (action == 'edit' || action == 'view') {
									var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
									if (selected_record.get('WhsDocumentUc_id') > 0) {
										var params = selected_record.data;

										getWnd(view_frame.editformclassname).show({
											WhsDocumentSupply_id: selected_record.get('WhsDocumentSupply_id'),
											action: action,
											params: params,
											callback: function(data) {
												view_frame.clearFilter();
												for(var key in data)
													selected_record.set(key, data[key]);
												if (selected_record.get('state') != 'add')
													selected_record.set('state', 'edit');
												view_frame.setFilter();
											}
										});
									}
								}
							},
							deleteRecord: function(){
								var view_frame = this;
								var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
								if (selected_record.get('state') == 'add') {
									view_frame.getGrid().getStore().remove(selected_record);
								} else {
									selected_record.set('state', 'delete');
									selected_record.commit();
									view_frame.setFilter();
								}
							},
							getChangedData: function(){ //возвращает новые и измненные показатели
								var data = new Array();
								this.clearFilter();
								this.getGrid().getStore().each(function(record) {

									if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')
										data.push(record.data);
								});
								this.setFilter();
								return data;
							},
							getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
								var dataObj = this.getChangedData();
								return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
							},
							clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
								this.getGrid().getStore().clearFilter();
							},
							setFilter: function() { //скрывает удаленные записи
								this.getGrid().getStore().filterBy(function(record){
									return (record.get('state') != 'delete');
								});
							}
						})
					]
				})]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: langs('Файлы'),
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items:
					[this.FileUploadPanel]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentUc_pid'}, 
				{name: 'WhsDocumentUc_Num'}, 
				{name: 'WhsDocumentUc_Name'}, 
				{name: 'WhsDocumentType_id'},
				{name: 'WhsDocumentType_Name'},
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
				{name: 'WhsDocumentSupply_Date_Range'},
				{name: 'DrugFinance_id'}, 
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'WhsDocumentStatusType_id'}
			]),
			url: '/?c=WhsDocumentSupply&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{				
				hidden: true,
				hidden: true,
				handler: function() {},
				iconCls: null,
				text: langs('Контроль поставки')
			}, {				
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {				
				handler: function() {
					this.ownerCt.doSign();
				},
				iconCls: null,
				text: langs('Подписать')
			}, {
				handler: function() {
					this.ownerCt.doUnsign();
				},
				iconCls: null,
				text: 'Снять подпись'
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
		sw.Promed.swWhsDocumentSupplyEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentSupplyEditForm').getForm();
	}	
});