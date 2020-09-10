/**
* swWhsDocumentUcInventOrderEditWindow - окно редактирования приказа на проведение инвентаризации
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
* @comment      
*/
sw.Promed.swWhsDocumentUcInventOrderEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['prikaz_na_provedenie_inventarizatsii_redaktirovanie'],
	id: 'WhsDocumentUcInventOrderEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'Org_aid',
			'WhsDocumentUc_Date',
			'WhsDocumentUc_Num',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var field = wnd.form.findField(field_arr[i]);
			if (disable) {
				field.disable();
			} else {
				field.enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
			wnd.FileUploadPanel.disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
			wnd.FileUploadPanel.enable();
		}

		wnd.SupplyGrid.setReadOnly(disable);
		wnd.DocGrid.setReadOnly(disable);

		if (disable) {
			wnd.SupplyGrid.getAction('action_add').setDisabled(disable);
			wnd.SupplyGrid.getAction('action_delete').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_add_all').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_delete_all').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_storage_select').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_delete').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_approve').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_approve_all').setDisabled(disable);
		} else {
			wnd.DocGrid.getAction('wduioe_action_add_all').setDisabled(disable);
			wnd.DocGrid.getAction('wduioe_action_storage_select').setDisabled(disable);
		}

	},
	setDefaultValues: function() {
		var wnd = this;
		var type_combo = wnd.form.findField('WhsDocumentType_id');
		var current_date = new Date();

		if (wnd.WhsDocumentType_Code && wnd.WhsDocumentType_Code > 0) {
			var idx = type_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentType_Code') == wnd.WhsDocumentType_Code; });
			if (idx >= 0) {
				var id = type_combo.getStore().getAt(idx).get('WhsDocumentType_id');
				type_combo.setValue(id);
			}
		}

		wnd.form.findField('WhsDocumentUc_Date').setValue(current_date);
		if (wnd.ARMType == 'merch' || this.ARMType == 'lpupharmacyhead') {
			wnd.form.findField('Org_aid').setValueById(getGlobalOptions().org_id);
		} else {
			wnd.form.findField('Org_aid').setMinzdravId();
		}
	},
	checkDocList: function() { //проверка списка ведомостей
		var res = false;
		this.DocGrid.getGrid().getStore().each(function(r){
			if (!res && r.get('WhsDocumentUc_id') > 0) {
				res = true;
				return false;
			}
		});
		return res;
	},
	doSign: function() {
		var wnd = this;

		if (this.checkDocList()) {
			this.doSave({
				callback: function(doc_id) {
					Ext.Ajax.request({
						params: {
							WhsDocumentUc_id: doc_id
						},
						url: '/?c=WhsDocumentUcInvent&m=signWhsDocumentUcInventOrder',
						callback: function(options, success, response) {
							if (response.responseText) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.success) {
									wnd.callback(wnd.owner, doc_id);
									wnd.hide();
								}
							}
						}
					});
				}
			});
		} else {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_vedomostey_pust_podpisanie_dokumenta_ne_vozmojno']);
		}
	},
	doSave:  function(options) {
		var wnd = this;
		if (!wnd.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('WhsDocumentUcInventOrderEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		wnd.submit(options);
		return true;
	},
	submit: function(options) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = new Object();
		params.WhsDocumentType_id = wnd.form.findField('WhsDocumentType_id').getValue();
		params.WhsDocumentStatusType_id = wnd.form.findField('WhsDocumentStatusType_id').getValue();
		params.WhsDocumentUc_Name = lang['prikaz_na_provedenie_inventarizatsii_№']+wnd.form.findField('WhsDocumentUc_Num').getValue();
		params.WhsDocumentSupplyInventListJSON = wnd.SupplyGrid.getJSONChangedData();
		params.WhsDocumentUcInventListJSON = wnd.DocGrid.getJSONChangedData();

		loadMask.show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result)  {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) {
				var id = action.result.WhsDocumentUc_id;
				wnd.FileUploadPanel.listParams = {
					ObjectName: 'WhsDocumentUc',
					ObjectID: id					
				};
				wnd.FileUploadPanel.saveChanges();				
				loadMask.hide();
				if (options && options.callback && typeof options.callback == 'function') {
					options.callback(action.result.WhsDocumentUc_id);
				} else {
					wnd.callback(wnd.owner, action.result.WhsDocumentUc_id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentUcInventOrderEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentUc_id = null;
		this.WhsDocumentType_Code = null;
		this.WhsDocumentStatusType_Code = null;
		this.ARMType = null;

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
		if ( arguments[0].WhsDocumentUc_id ) {
			this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
		}
		if ( arguments[0].WhsDocumentType_Code ) {
			this.WhsDocumentType_Code = arguments[0].WhsDocumentType_Code;
		}
		if ( arguments[0].WhsDocumentStatusType_Code ) {
			this.WhsDocumentStatusType_Code = arguments[0].WhsDocumentStatusType_Code*1;
			if (this.WhsDocumentStatusType_Code == 2) { //2 - Действующий
				this.action = 'view';
			}
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		this.DocGrid.addActions({
			name: 'wduioe_action_add_all',
			text: lang['sozdat_dlya_vseh'],
			iconCls: 'add16',
			handler: function() {wnd.DocGrid.createInventList();}
		}, 0);
		this.DocGrid.addActions({
			name: 'wduioe_action_delete_all',
			text: lang['udalit_vse'],
			iconCls: 'delete16',
			handler: function() {wnd.DocGrid.deleteRecord('all');}
		}, 1);
		this.DocGrid.addActions({
			name: 'wduioe_action_storage_select',
			text: lang['vyibrat_skladyi'],
			iconCls: 'edit16',
			handler: function() {
				wnd.DocGrid.selectStorage({
					callback: function(data) {
						wnd.DocGrid.createInventList(data.Storage_List);
					}
				});
			}
		}, 2);
		this.DocGrid.addActions({
			name: 'wduioe_action_delete',
			text: lang['udalit'],
			iconCls: 'delete16',
			handler: function() {wnd.DocGrid.deleteRecord();}
		}, 3);
		this.DocGrid.addActions({
			name: 'wduioe_action_approve',
			text: lang['utverdit_vedomost'],
			iconCls: 'ok16',
			handler: function() {wnd.DocGrid.setApproved();}
		}, 4);
		this.DocGrid.addActions({
			name: 'wduioe_action_approve_all',
			text: lang['utverdit_vse_vedomosti'],
			iconCls: 'ok16',
			handler: function() {wnd.DocGrid.setApproved('all');}
		}, 5);

		this.form.reset();
		this.SupplyGrid.params.ARMType = this.ARMType;
		this.SupplyGrid.params.params = new Object();
		this.SupplyGrid.removeAll({addEmptyRecord: false});
		this.DocGrid.removeAll({addEmptyRecord: false});
		this.DocGrid.setActionDeleteAllDisabled();
		this.DocGrid.setActionApproveAllDisabled();
		this.setTitle(lang['prikaz_na_provedenie_inventarizatsii']);

		var hideCol = true;
		if(this.ARMType == 'adminllo' || (this.ARMType == 'merch' && getGlobalOptions().orgtype.inlist(['farm','reg_dlo']))){
			hideCol = false;
		}
		this.SupplyGrid.setColumnHidden('DrugRequestPurchaseSpec_string',hideCol);

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				wnd.setDisabled(false);
				wnd.FileUploadPanel.reset();
				wnd.setDefaultValues();
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.setDisabled(this.action == 'view');
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						WhsDocumentUc_id: wnd.WhsDocumentUc_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						wnd.form.setValues(result[0]);
						if (result[0].Org_aid) {
							wnd.form.findField('Org_aid').setValueById(result[0].Org_aid);
						}
						wnd.SupplyGrid.loadData({
							globalFilters: {
								WhsDocumentUc_id: wnd.WhsDocumentUc_id
							},
							options: {
								addEmptyRecord: false
							}
						});
						wnd.DocGrid.loadData({
							globalFilters: {
								WhsDocumentUc_pid: wnd.WhsDocumentUc_id
							},
							options: {
								addEmptyRecord: false
							},
							callback: function() {
								wnd.DocGrid.setFinParams();
								wnd.DocGrid.setActionDeleteAllDisabled();
								wnd.DocGrid.setActionApproveAllDisabled();
							}
						});
						
						//загружаем файлы
						wnd.FileUploadPanel.reset();
						wnd.FileUploadPanel.listParams = {
							ObjectName: 'WhsDocumentUc',
							ObjectID: wnd.WhsDocumentUc_id,
							callback: function() {
								wnd.setDisabled(wnd.action == 'view');
							}
						};
						wnd.FileUploadPanel.loadData();
						loadMask.hide();
					},
					url:'/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventOrder'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;
	
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 97,
			labelWidth: 150,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile',
			listParams: {
				ObjectName: 'WhsDocumentUc',
				ObjectID: null
			}
		});
		
		this.SupplyGrid = new sw.Promed.ViewFrame({
			region: 'center',
			actions: [
				{name: 'action_add', handler: function() {wnd.SupplyGrid.editRecord('add');}},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', text: lang['isklyuchit'], handler: function() { wnd.SupplyGrid.deleteRecord() }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadWhsDocumentSupplyInventList',
			height: 140,
			object: 'WhsDocumentSupplyInvent',
			editformclassname: 'swWhsDocumentSupplySelectWindow',
			id: 'wduioeWhsDocumentSupplyInventGrid',
			paging: false,
			style: 'margin: 0px',
			params: {
				onSelect: function(data) {
					wnd.SupplyGrid.addRecord(data);
				},
				CustomFilterPanelEnabled: true
			},
			stringfields: [
				{name: 'WhsDocumentSupplyInvent_id', type: 'int', header: 'ID', key: true},
				{name: 'WhsDocumentSupply_id', type: 'int', hidden: true},
				{name: 'state', hidden: true},
				{name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_kontrakta'], width: 150},
				{name: 'WhsDocumentUc_Date', type: 'string', header: lang['data'], width: 150},
				{name: 'WhsDocumentUc_Year', type: 'string', header: lang['god'], width: 150},
				{name: 'Org_sid_Nick', type: 'string', header: lang['postavschik'], width: 150},
				{name: 'WhsDocumentUc_Name', type: 'string', header: lang['naimenovanie'], width: 250, id: 'autoexpand'},
				{name: 'DrugFinance_id', hidden: true},
				{name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width:175},
				{name: 'WhsDocumentCostItemType_id', hidden: true},
				{name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashodov'], width:125},
				{name: 'DrugRequestPurchaseSpec_string', type: 'string', header: 'Заявка и лот', width:250, hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('WhsDocumentSupplyInvent_id') > 0 && !this.readOnly) {
					this.ViewActions.action_add.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_add.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			addRecord: function(data){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var record_count = store.getCount();

				if (record_count == 1 && !store.getAt(0).get('WhsDocumentSupplyInvent_id')) {
					view_frame.removeAll({ addEmptyRecord: false });
				}
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);
				view_frame.clearFilter();
				data.WhsDocumentSupplyInvent_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				data.state = 'add';
				if(!Ext.isEmpty(data.WhsDocumentUc_Date)){
					var dateArr = data.WhsDocumentUc_Date.split('.');
					if(typeof dateArr == 'object' && dateArr.length == 3){
						data.WhsDocumentUc_Year = dateArr[2];
					}
				}
				store.insert(record_count, new record(data));
				view_frame.setFilter();
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vyi_hotite_udalit_zapis'],
					title: lang['podtverjdenie'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes') {
							if (selected_record.get('state') == 'add') {
								view_frame.getGrid().getStore().remove(selected_record);
							} else {
								selected_record.set('state', 'delete');
								selected_record.commit();
								view_frame.setFilter();
							}
						}
					}
				});
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var item = record.data;
						item.FileData = null;
						data.push(item);
					}
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

		this.DocGrid = new sw.Promed.ViewFrame({
			region: 'south',
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventList',
			height: 220,
			object: 'WhsDocumentUcInvent',
			id: 'wduioeWhsDocumentUcInventGrid',
			paging: false,
			style: 'margin: 0px',
			params: {
				onSelect: function(data) {
					wnd.SupplyGrid.addRecord(data);
				}
			},
			stringfields: [
				{name: 'WhsDocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'state', hidden: true},
				{name: 'WhsDocumentUcInvent_id', hidden: true},
				{name: 'WhsDocumentUc_pid', hidden: true},
				{name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_dokumenta'], width: 150},
				{name: 'WhsDocumentUc_Name', hidden: true},
				{name: 'WhsDocumentType_id', hidden: true},
				{name: 'WhsDocumentUc_Date', type: 'string', header: lang['data'], width: 150},
				{name: 'WhsDocumentUcInvent_begDT', hidden: true},
				{name: 'WhsDocumentStatusType_id', hidden: true},
				{name: 'WhsDocumentStatusType_Code', hidden: true},
				{name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status_dokumenta'], width: 150},
				{name: 'Contragent_id', hidden: true},
				{name: 'Contragent_Name', type: 'string', header: lang['kontragent'], width: 150},
				{name: 'Storage_id', hidden: true},
				{name: 'StorageZone_id', hidden: true},
				{name: 'Storage_Name', type: 'string', header: lang['sklad'], width: 150},
				{name: 'StorageZone_Name', type: 'string', header: 'Место хранения', width: 150},
				{name: 'Org_id', hidden: true},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 150, id: 'autoexpand'},
				{name: 'DrugFinance_id', hidden: true},
				{name: 'WhsDocumentCostItemType_id', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm, rowIdx, record) {
				if (record.get('WhsDocumentUc_id') > 0 && !this.readOnly) {
					this.getAction('wduioe_action_delete').setDisabled(record.get('WhsDocumentStatusType_Code') != 1);
					this.getAction('wduioe_action_approve').setDisabled(record.get('WhsDocumentStatusType_Code') != 1);
				} else {
					this.getAction('wduioe_action_delete').setDisabled(true);
					this.getAction('wduioe_action_approve').setDisabled(true);
				}
			},
			createInventList: function(storage_list) {
				var view_frame = this;
				var params = new Object();
				params.Storage_List = storage_list && Ext.isArray(storage_list) && storage_list.length > 0 ? storage_list.join(',') : null;

				if ((params.Storage_List == null || params.Storage_List == '') && (wnd.ARMType == 'merch' || wnd.ARMType == 'lpupharmacyhead')) {
					params.Org_List = getGlobalOptions().org_id;
				}

				Ext.Ajax.request({
					params: params,
					url: '/?c=WhsDocumentUcInvent&m=createDocumentUcInventList',
					callback: function(options, success, response) {
						if (response.responseText) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
                            if (Ext.isArray(response_obj) && response_obj.length > 0) {
                                if (!Ext.isEmpty(response_obj[0]) && !Ext.isEmpty(response_obj[0]['Error_Msg'])) {
                                    Ext.Msg.alert(lang['oshibka'], response_obj[0]['Error_Msg']);
                                } else {
                                    view_frame.addRecords(response_obj);
                                }
							}
						}
					}
				});
			},
			setApproved: function(mode) {
				var apr_status_id = 0;
				var apr_status_code = 2; //2 - Действующий
				var apr_status_name = null;
				var store = this.getGrid().getStore();
				var status_combo = wnd.form.findField('WhsDocumentStatusType_id');
				var selected_record = this.getGrid().getSelectionModel().getSelected();
				var idx = status_combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentStatusType_Code') == apr_status_code; });
				if (idx >= 0) {
					apr_status_id = status_combo.getStore().getAt(idx).get('WhsDocumentStatusType_id');
					apr_status_name = status_combo.getStore().getAt(idx).get('WhsDocumentStatusType_Name');
				}

				if (apr_status_id > 0) {
					if (mode && mode == 'all') {
						store.each(function(rec) {
							if (rec.get('WhsDocumentStatusType_Code') == 1) {
								rec.set('WhsDocumentStatusType_id', apr_status_id);
								rec.set('WhsDocumentStatusType_Code', apr_status_code);
								rec.set('WhsDocumentStatusType_Name', apr_status_name);
								if (rec.get('state') != 'add') {
									rec.set('state', 'edit');
								}
								rec.commit();
							}
						});
					} else if (selected_record && selected_record.get('WhsDocumentStatusType_Code') == 1) {
						selected_record.set('WhsDocumentStatusType_id', apr_status_id);
						selected_record.set('WhsDocumentStatusType_Code', apr_status_code);
						selected_record.set('WhsDocumentStatusType_Name', apr_status_name);
						if (selected_record.get('state') != 'add') {
							selected_record.set('state', 'edit');
						}
						selected_record.commit();
					}

					if (selected_record) {
						this.getGrid().getSelectionModel().selectRow(this.getIndexOnFocus());
					}

					this.setActionDeleteAllDisabled();
					this.setActionApproveAllDisabled();
				}
			},
			selectStorage:function(params) {
				getWnd('swWhsDocumentUcInventOrderStorageSelectWindow').show({
					params: {
						//Org_id: wnd.ARMType == 'merch' ? getGlobalOptions().org_id : null
						Org_aid: wnd.form.findField('Org_aid').getValue(),
                        WhsDocumentUc_Date: !Ext.isEmpty(wnd.form.findField('WhsDocumentUc_Date').getValue()) ? wnd.form.findField('WhsDocumentUc_Date').getValue().format('d.m.Y') : null,
						DrugFinance_id: wnd.form.findField('DrugFinance_id').getValue(),
                        WhsDocumentCostItemType_id: wnd.form.findField('WhsDocumentCostItemType_id').getValue()
					},
					onSelect: function(data) {
						if (params && params.callback && typeof params.callback == 'function') {
							params.callback(data);
						}
					},
					ARMType: wnd.ARMType
				});
			},
			setActionDeleteAllDisabled: function() {
				var store = this.getGrid().getStore();
				var disable = (store.getCount() < 1 || !store.getAt(0).get('WhsDocumentUc_id'));
				if (!this.readOnly) {
					store.each(function(rec) {
						if (rec.get('WhsDocumentStatusType_Code') != 1 && rec.get('WhsDocumentUc_id') > 0) {
							disable = true;
							return false;
						}
					});
				} else {
					disable = true;
				}
				this.getAction('wduioe_action_delete_all').setDisabled(disable);
			},
			setActionApproveAllDisabled: function() {
				var store = this.getGrid().getStore();
				var disable = true;
				if (!this.readOnly) {
					store.each(function(rec) {
						if (rec.get('WhsDocumentStatusType_Code') == 1 && rec.get('WhsDocumentUc_id') > 0) {
							disable = false;
							return false;
						}
					});
				} else {
					disable = true;
				}
				this.getAction('wduioe_action_approve_all').setDisabled(disable);
			},
			setOrderParams: function() {
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var doc_date = wnd.form.findField('WhsDocumentUc_Date').getValue() ? wnd.form.findField('WhsDocumentUc_Date').getValue().format('d.m.Y') : null ;
				var finance_id = wnd.form.findField('DrugFinance_id').getValue();
				var cost_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();

				wnd.SupplyGrid.params.params = {
					DrugFinance_id: finance_id,
					WhsDocumentCostItemType_id: cost_id
				}

				store.each(function(rec) {
					if (rec.get('state') == 'add') {
						rec.set('WhsDocumentUc_Date', doc_date);
						rec.set('WhsDocumentUcInvent_begDT', doc_date);
						rec.set('DrugFinance_id', finance_id);
						rec.set('WhsDocumentCostItemType_id', cost_id);
						rec.commit();
					}
				});
			},
			setFinParams: function() {
				var store = this.getGrid().getStore();

				wnd.SupplyGrid.params.params = new Object();

				if (store.getCount() > 0 && store.getAt(0).get('WhsDocumentUc_id') > 0) {
					var finance_id = store.getAt(0).get('DrugFinance_id');
					var cost_id = store.getAt(0).get('WhsDocumentCostItemType_id');

					wnd.form.findField('DrugFinance_id').setValue(finance_id);
					wnd.form.findField('WhsDocumentCostItemType_id').setValue(cost_id);
					wnd.SupplyGrid.params.params = {
						DrugFinance_id: finance_id,
						WhsDocumentCostItemType_id: cost_id
					};
				}
			},
			addRecords: function(data_arr){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var record_count = store.getCount();
				var record_num_count = 0;
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);
				var start_num = 0;

				var doc_date = wnd.form.findField('WhsDocumentUc_Date').getValue() ? wnd.form.findField('WhsDocumentUc_Date').getValue().format('d.m.Y') : null ;
				var finance_id = wnd.form.findField('DrugFinance_id').getValue();
				var cost_id = wnd.form.findField('WhsDocumentCostItemType_id').getValue();

				if ( record_count == 1 && !store.getAt(0).get('WhsDocumentUc_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
					record_count = 0;
				}

				//проверяем нет ли уже записи в гриде с такимже сочетанием организации и склада, если есть - удаляем их из массива для добавления
				//паралельно подсчет максимального номера документа
				store.each(function(rec) {
					for (var i = 0; i < data_arr.length; i++) {
						if (rec.get('Org_id') == data_arr[i].Org_id && rec.get('Storage_id') == data_arr[i].Storage_id && rec.get('StorageZone_id') == data_arr[i].StorageZone_id) {
							data_arr.splice(i,1);
							break;
						}
					}
					if (rec.get('WhsDocumentUc_Num') > start_num) {
						start_num = rec.get('WhsDocumentUc_Num');
					}
				});
				record_num_count = data_arr.length;

				//так как отсчет нужно начинать не с максимального, а со следующего номера, увеличиваем стартовый номер на 1
				start_num++;
				//проверяем, возможно стартовый номер меньше допустимого начального номера из бд, в таком случае стартовый номер берем из бд
				if (data_arr.length > 0 && start_num <= data_arr[0].WhsDocumentUc_Num*1) {
					start_num = data_arr[0].WhsDocumentUc_Num*1;
				}

				this.clearFilter();
				record_count = store.getCount();
				for (var i = 0; i < data_arr.length; i++) {
					data_arr[i].WhsDocumentUc_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					data_arr[i].state = 'add';
					data_arr[i].WhsDocumentUc_Num = start_num+(--record_num_count);
					data_arr[i].WhsDocumentUc_Name = data_arr[i].WhsDocumentType_Name+lang['№']+data_arr[i].WhsDocumentUc_Num;
					data_arr[i].WhsDocumentUc_Date = doc_date;
					data_arr[i].WhsDocumentUcInvent_begDT = doc_date;
					data_arr[i].DrugFinance_id = finance_id;
					data_arr[i].WhsDocumentCostItemType_id = cost_id;
					store.insert(record_count, new record(data_arr[i]));
				}
				this.setFilter();
				this.setActionDeleteAllDisabled();
				this.setActionApproveAllDisabled();
			},
			deleteRecord: function(mode){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: 'Вы хотите удалить '+(mode && mode == 'all' ? 'все записи?' : 'запись?'),
					title: lang['podtverjdenie'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes') {
							if (mode && mode == 'all') {
								store.each(function(rec) {
									if (rec.get('WhsDocumentStatusType_Code') == 1) {
										if (rec.get('state') == 'add') {
											view_frame.getGrid().getStore().remove(rec);
										} else {
											rec.set('state', 'delete');
											rec.commit();
										}
									}
								});
							} else if (selected_record && selected_record.get('WhsDocumentStatusType_Code') == 1) {
								if (selected_record.get('state') == 'add') {
									view_frame.getGrid().getStore().remove(selected_record);
								} else {
									selected_record.set('state', 'delete');
									selected_record.commit();
								}
							}
							view_frame.setFilter();
							view_frame.setActionDeleteAllDisabled();
							view_frame.setActionApproveAllDisabled();
						}
					}
				});
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var item = record.data;
						item.FileData = null;
						data.push(item);
					}
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
		
		this.statusType = new Ext.form.Label({
			fieldLabel: lang['status_gk'],
			name: 'statusType',
			text: '',
			width:200
		});
		
		var form = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'background:#DFE8F6; padding-top: 10px;',
			border: false,
			bodyBorder: false,
			frame: false,
			region: 'north',
			labelWidth: 180,
			labelAlign: 'right',
			layout: 'form',
			id: 'WhsDocumentUcInventOrderEditForm',
			items: [{
				name: 'WhsDocumentUc_id',
				xtype: 'hidden',
				value: 0
			}, {
				fieldLabel : lang['organizatsiya'],
				hiddenName: 'Org_aid',
				xtype: 'swbaseremotecombo',
				valueField: 'Org_id',
				displayField: 'Org_Name',
				allowBlank: false,
				editable: true,
				lastQuery: '',
				validateOnBlur: true,
				anchor: '90%',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'Org_id'
					}, [
						{name: 'Org_id', mapping: 'Org_id'},
						{name: 'Org_Name', mapping: 'Org_Name'}
					]),
					url: '/?c=WhsDocumentUcInvent&m=loadOrgCombo'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0;"><tr><td>{Org_Name}</td></tr></table>',
					'</div></tpl>'
				),
				setValueById: function(org_id) {
					var combo = this;
					combo.store.baseParams.Org_id = org_id;
					combo.store.load({
						callback: function(){
							combo.setValue(org_id);
							combo.store.baseParams.Org_id = null;
						}
					});
				},
				setMinzdravId: function() {
					var combo = this;
					Ext.Ajax.request({
						url: '/?c=Farmacy&m=getMinzdravDloOrgId',
						callback: function(options, success, response) {
							if (response.responseText) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Org_id && response_obj.Org_id > 0) {
									combo.setValueById(response_obj.Org_id);
								}
							}
						}
					});
				}
			}, {
				layout: 'column',
				border: false,
				bodyStyle: 'background:#DFE8F6;',
				items: [{
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						xtype: 'swdatefield',
						fieldLabel: 'Дата приказа/инвентаризации',
						name: 'WhsDocumentUc_Date',
						allowBlank: false,
						width: 140,
						listeners: {
							change: function() {
								wnd.DocGrid.setOrderParams();
							}
						}
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						fieldLabel: lang['status_dokumenta'],
						hiddenName: 'WhsDocumentStatusType_id',
						xtype: 'swcommonsprcombo',
						allowBlank: true,
						disabled: true,
						sortField:'WhsDocumentStatusType_Code',
						comboSubject: 'WhsDocumentStatusType',
						value: 1,
						width: 200
					}]
				}]
			}, {
				fieldLabel: lang['tip'],
				hiddenName: 'WhsDocumentType_id',
				xtype: 'swcommonsprcombo',
				disabled: true,
				sortField:'WhsDocumentType_Code',
				comboSubject: 'WhsDocumentType',
				width: 525
			},
			this.statusType,
			{
				fieldLabel: lang['№_dokumenta'],
				name: 'WhsDocumentUc_Num',
				allowBlank:false,
				xtype: 'textfield',
				width: 525
			},	{
				layout: 'column',
				border: false,
				bodyStyle: 'background:#DFE8F6;',
				items: [{
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					items: [{
						xtype: 'swdrugfinancecombo',
						fieldLabel: lang['ist_finansirovaniya'],
						name: 'DrugFinance_id',
						width: 200,
						allowBlank: false,
						listeners: {
							change: function() {
								wnd.DocGrid.setOrderParams();
							}
						}
					}]
				},  {
					layout: 'form',
					border: false,
					bodyStyle: 'background:#DFE8F6;',
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						xtype: 'swwhsdocumentcostitemtypecombo',
						fieldLabel: lang['statya_rashoda'],
						name: 'WhsDocumentCostItemType_id',
						width: 200,
						allowBlank: false,
						listeners: {
							change: function() {
								wnd.DocGrid.setOrderParams();
							}
						}
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocumentUc_id'},
				{name: 'WhsDocumentUc_Num'},
				{name: 'WhsDocumentType_id'}, 
				{name: 'WhsDocumentStatusType_id'},
				{name: 'Org_aid'}
			]),
			url: '/?c=WhsDocumentUcInvent&m=saveWhsDocumentUcInventOrder'
		});

		Ext.apply(this, {
			layout: 'fit',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.ownerCt.doSign();
				},
				iconCls: 'ok16',
				text: lang['podpisat']
			},  {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[{
				xtype: 'panel',
				layout: 'fit',
				farme: true,
				border: false,
				height: 900,
				width: 200,
				autoScroll: true,
				bodyStyle: 'background:#DFE8F6;',
				items: [
					form,
					{
						xtype: 'panel',
						title: lang['spisok_kontraktov'],
						border: true,
						style: 'padding: 0px; margin: 5px 5px 0px 5px;',
						bodyStyle: 'padding: 0px; margin: 0px;',
						collapsible: true,
						items: [this.SupplyGrid]
					}, {
						xtype: 'panel',
						title: lang['inventarizatsionnyie_vedomosti'],
						border: true,
						style: 'padding: 0px; margin: 5px 5px 0px 5px;',
						collapsible: true,
						items: [this.DocGrid]
					}, {
						xtype: 'panel',
						title: lang['faylyi'],
						border: true,
						frame: true,
						style: 'padding: 0px; margin: 5px 5px 0px 5px; display:block;',
						collapsible: true,
						items: [this.FileUploadPanel]
					}
				]
			}

			]
		});
		sw.Promed.swWhsDocumentUcInventOrderEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentUcInventOrderEditForm').getForm();
	}	
});