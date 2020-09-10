/**
* swDrugPackageBarCodeViewWindow - окно просмотра списка списка ШК
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Salakhov R.
* @version      12.2017
* @comment      
*/

sw.Promed.swDrugPackageBarCodeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Список штрих-кодов',
	layout: 'border',
	id: 'DrugPackageBarCodeViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		activate: function(){
			this.ScanCodeService.start();
		},
		deactivate: function() {
			this.ScanCodeService.stop();
		}
	},
    processBarCode: function(bar_code_data, mode) { //функция обработки штрих-кода. mode (режим): 'test_str' - тестовый код длинной 5 символов, 'scanner_data' - данные со сканера расшифрованные сервисом
        if (!Ext.isEmpty(bar_code_data)) {
            //расшифровка

            //формирование массива данных
            var bc_data = new Object();

            switch(mode) {
                case 'test_str':
                    bc_data.DrugPackageBarCode_BarCode = bar_code_data;
                    bc_data.DrugPackageBarCodeType_id = '1'; //sgtin - Идентификатор потребительской упаковки
                    bc_data.DrugPackageBarCode_GTIN = 'GTIN_'+bar_code_data;
                    bc_data.DrugPackageBarCode_SeriesNum = 'SER_NUM_'+bar_code_data;
                    bc_data.DrugPackageBarCode_expDT = (new Date).format('d.m.Y');
                    bc_data.DrugPackageBarCode_TNVED = 'TNVD';
                    bc_data.DrugPackageBarCode_FactNum = 1;
                    break
                case 'scanner_data':
                    var dt_str = bar_code_data.dateIssue;
                    if (dt_str.length == 6) { //данные прихоят в формате ггммдд
                        var dt_arr = new Array();
                        var date_str = null;
                        dt_arr.push(dt_str.substr(0, 2));
                        dt_arr.push(dt_str.substr(2, 2));
                        dt_arr.push(dt_str.substr(4, 2));

                        if (dt_arr[2] == '00') {
                            dt_arr[2] = '01';
                        }
                        dt_arr[0] = '20'+dt_arr[0];

                        date_str = dt_arr.reverse().join('.');
                    }

                    bc_data.DrugPackageBarCode_BarCode = bar_code_data.indSerNum;
                    bc_data.DrugPackageBarCodeType_id = '1'; //sgtin - Идентификатор потребительской упаковки
                    bc_data.DrugPackageBarCode_GTIN = bar_code_data.idendNum;
                    bc_data.DrugPackageBarCode_SeriesNum = bar_code_data.serNum;
                    bc_data.DrugPackageBarCode_expDT = date_str;
                    bc_data.DrugPackageBarCode_TNVED = null;
                    bc_data.DrugPackageBarCode_FactNum = 1;
                    break
            }

            this.BarCodeGrid.clearFilter();

            //поиск данного ШК в существущем списке кодов
            var idx = this.BarCodeGrid.getGrid().getStore().findBy(function(record) {
                return (record.get('DrugPackageBarCode_BarCode') == bc_data.DrugPackageBarCode_BarCode);
            })

            if (idx > -1) { //если нашли
                record = this.BarCodeGrid.getGrid().getStore().getAt(idx);
                if (record.get('state') == 'delete') { //если запись удаленеа - восстанавливаем
                    record.set('state', 'edit');
                    record.commit();
                }
                //устанавливаем фокус на запись
                this.BarCodeGrid.getGrid().getSelectionModel().selectRow(idx);
            } else { //если не нашли - добавляем запись в список
                this.BarCodeGrid.addRecords([bc_data]);
                this.BarCodeGrid.getGrid().getSelectionModel().selectLastRow();
            }
            this.BarCodeGrid.setFilter();
        }
    },
    loadData: function() {
        var wnd = this;
        var params = new Object();

        wnd.BarCodeGrid.removeAll();
        if (wnd.DocumentUcStr_id > 0) {
            params.DocumentUcStr_id = wnd.DocumentUcStr_id;

            wnd.BarCodeGrid.loadData({
                params: params,
                globalFilters: params,
                callback: function() {
                    if (!Ext.isEmpty(wnd.BarCodeChangedData)) { //если при открытии передан сереализованный массив данных - то обьединям этот массив с загружеными данными
                        wnd.BarCodeGrid.setChangedData(wnd.BarCodeChangedData);
                    }
                }
            });
        } else if (!Ext.isEmpty(wnd.BarCodeChangedData)) { //если при открытии передан сереализованный массив данных - то обьединям этот массив с загружеными данными
            wnd.BarCodeGrid.setChangedData(wnd.BarCodeChangedData);
        }
    },
    doSave: function() {
        var data = this.BarCodeGrid.getChangedData();
        var cnt = this.BarCodeGrid.getAddedBarCodeCount();
        this.onSave({
            'ChangedData': data,
            'AddedBarCode_Count': cnt
        });
        this.hide();
    },
    setDisabled: function(disable) {
        var bc_field = this.BarCodeInputPanel.getForm().findField('BarCodeInput_Field');
        if (disable) {
            this.buttons[0].disable();
            bc_field.disable();
        } else {
            this.buttons[0].enable();
            bc_field.enable();
        }
        this.BarCodeGrid.setReadOnly(disable);
    },
	show: function() {
        var wnd = this;
		sw.Promed.swDrugPackageBarCodeViewWindow.superclass.show.apply(this, arguments);

        this.action = '';
        this.onSave = Ext.emptyFn;
        this.DocumentUcStr_id = null;
        this.BarCodeChangedData = null; //для хранения изменений в списке штрих-кодов
		this.scanCheckTimer = null;
		this.getDrugDataInProcess = false;

        if (!arguments[0]) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { wnd.hide(); });
            return false;
        }
        if (arguments[0].action) {
            this.action = arguments[0].action;
        }
        if (arguments[0].onSave && typeof arguments[0].onSave == 'function') {
            this.onSave = arguments[0].onSave;
        }
        if (arguments[0].DocumentUcStr_id) {
            this.DocumentUcStr_id = arguments[0].DocumentUcStr_id;
        }
        if (arguments[0].BarCodeChangedData) {
            this.BarCodeChangedData = arguments[0].BarCodeChangedData;
        }

        this.BarCodeInputPanel.getForm().reset();
		this.loadData();
        this.setDisabled(this.action == 'view');
	},
	initComponent: function() {
		var wnd = this;

		this.ScanCodeService = new sw.Promed.ScanCodeService({
			onGetDrugPackData: function(drugPackObject) {
				wnd.processBarCode(drugPackObject, 'scanner_data');
			}
		});

		/*this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
				xtype: 'textfield',
				label: 'Наименование',
				name: 'DrugPackageBarCode_Name'
			}]
		});

		this.FilterTabs = new Ext.TabPanel({
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'north',
			enableTabScroll: true,
			height: 170,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				title: 'Общие',
				layout: 'fit',
				border: false,
				items: [this.FilterCommonPanel]
			}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Поиск',
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: 'Очистить',
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterTabs,
				this.FilterButtonsPanel
			]
		});*/
        this.BarCodeInputPanel = new Ext.FormPanel({
            height: 50,
            frame: true,
            bodyStyle: 'margin: 5px 0',
            labelAlign: 'right',
            defaults: {
                width: 400
            },
            items: [{
                xtype: 'textfield',
                name: 'BarCodeInput_Field',
                fieldLabel: 'Штрих-код',
                listeners: {
                    change: function(field, newValue) {
                        if (newValue.length == 5 || newValue.length == 27) {
                            wnd.processBarCode(newValue, 'test_str');
                        }
                    }
                }
            }]
        });

		this.BarCodeGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', handler: function() { wnd.BarCodeGrid.deleteRecord() }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=DocumentUc&m=loadDrugPackageBarCodeList',
			height: 180,
			object: 'DrugPackageBarCode',
			id: 'dpbcBarCodeGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugPackageBarCode_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugPackageBarCode_BarCode', type: 'string', header: 'Штрихкод', id: 'autoexpand'},
                {name: 'DrugPackageBarCodeType_id', hidden: true},
                {name: 'DrugPackageBarCode_GTIN', type: 'string', header: 'GTIN', width: 100},
                {name: 'DrugPackageBarCode_SeriesNum', type: 'string', header: 'Номер серии', width: 100},
                {name: 'DrugPackageBarCode_expDT', type: 'string', header: 'Срок годности', width: 100},
                {name: 'DrugPackageBarCode_TNVED', type: 'string', header: 'ТНВЭД', width: 100},
                {name: 'DrugPackageBarCode_FactNum', type: 'string', header: 'Фактическое кол-во', width: 100},
                {name: 'state', type: 'string', header: 'state', hidden: true}
			],
			title: null,
			toolbar: true,
            onRowSelect: function(sm,rowIdx,record) {
                if (record.get('DrugPackageBarCode_id') > 0 && !this.readOnly) {
                    this.ViewActions.action_delete.setDisabled(false);
                } else {
                    this.ViewActions.action_delete.setDisabled(true);
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
                view_frame.rowCount = view_frame.getGrid().getStore().getCount();
                if (view_frame.getCount() == 0) {
                    view_frame.ViewActions.action_delete.setDisabled(true);
                }
            },
            addRecords: function(data_arr){
                var view_frame = this;
                var store = view_frame.getGrid().getStore();
                var record_count = store.getCount();
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);

                if ( record_count == 1 && !store.getAt(0).get('DrugPackageBarCode_id') ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                view_frame.clearFilter();
                for (var i = 0; i < data_arr.length; i++) {
                    data_arr[i].DrugPackageBarCode_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                    data_arr[i].state = 'add';
                    store.insert(record_count, new record(data_arr[i]));
                }
                view_frame.setFilter();
                view_frame.rowCount = store.getCount();
            },
            /*updateRecordById: function(record_id, data) {
                var index = this.getGrid().getStore().findBy(function(rec) { return rec.get('DocumentUcStr_id') == record_id; });
                if (index == -1) {
                    return false;
                }
                var record = this.getGrid().getStore().getAt(index);

                for(var key in data) {
                    if (key != 'DocumentUcStr_id') {
                        record.set(key, data[key]);
                    }
                }
                if (record.get('state') != 'add') {
                    record.set('state', 'edit');
                }
                record.commit();
            },*/
            getChangedData: function(){ //возвращает новые и измненные показатели
                var data = new Array();
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
                        data.push(record.data);
                    }
                });
                this.setFilter();
                return data;
            },
            getAddedBarCodeCount: function(){ //возвращает количество добавленных записей за вычетом удаленных
                var cnt = 0;
                this.clearFilter();
                this.getGrid().getStore().each(function(record) {
                    if (record.data.state == 'add') {
                        cnt++;
                    }
                    if (record.data.state == 'delete') {
                        cnt--;
                    }
                });
                this.setFilter();
                return cnt;
            },
            setChangedData: function(data_arr){ //накладывает измененные данные поверх текущих
                var data = new Array();
                var view_frame = this;
                var store = view_frame.getGrid().getStore();

                this.clearFilter();

                //изменение существующих строк
                for (var i = 0; i < data_arr.length; i++) {
                    if (data_arr[i].state == 'edit' || data_arr[i].state == 'delete') {
                        var rec = store.getById(data_arr[i].DrugPackageBarCode_id);
                        rec.set('DrugPackageBarCode_BarCode', data_arr[i].DrugPackageBarCode_BarCode);
                        rec.set('DrugPackageBarCodeType_id', data_arr[i].DrugPackageBarCodeType_id);
                        rec.set('DrugPackageBarCode_GTIN', data_arr[i].DrugPackageBarCode_GTIN);
                        rec.set('DrugPackageBarCode_SeriesNum', data_arr[i].DrugPackageBarCode_SeriesNum);
                        rec.set('DrugPackageBarCode_expDT', data_arr[i].DrugPackageBarCode_expDT);
                        rec.set('DrugPackageBarCode_TNVED', data_arr[i].DrugPackageBarCode_TNVED);
                        rec.set('DrugPackageBarCode_FactNum', data_arr[i].DrugPackageBarCode_FactNum);
                        rec.set('state', data_arr[i].state);
                        rec.commit();
                    }
                }

                //очистка грида от пустой строки  при отсутствии данных
                var record_count = store.getCount();
                if ( record_count == 1 && !store.getAt(0).get('DrugPackageBarCode_id') ) {
                    view_frame.removeAll({addEmptyRecord: false});
                    record_count = 0;
                }

                //добавление новых строк
                var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                for (i = 0; i < data_arr.length; i++) {
                    if (data_arr[i].state == 'add') {
                        store.insert(record_count+i, new record(data_arr[i]));
                    }
                }

                store.each(function(record) {

                    if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')
                        data.push(record.data);
                });
                this.setFilter();
                return data;
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

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
                handler: function() {
                    var wnd = this.ownerCt;
                    wnd.doSave();
                },
                iconCls: 'save16',
                text: BTN_FRMSAVE
            },
            {
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				//wnd.FilterPanel,
                {
                    region: 'north',
                    height: 50,
                    items: [this.BarCodeInputPanel]
                }, {
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.BarCodeGrid]
					}]
				}
			]
		});
		sw.Promed.swDrugPackageBarCodeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});