/**
* swForm250u - форма “Журнал регистрации анализов и их результатов (форма 250У)”
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
**
* @package	Common
* @access	public
* @author	Arslanov
* @version	21.11.2018
* @comment
*/
sw.Promed.swForm250u = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Журнал регистрации анализов и их результатов',
	layout: 'border',
	region:'center',
	id: 'Form250u',
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
	
	approveIsAllowed: function() {//Проверка прав на "Одобрение"
		var wnd = this;
		if (wnd.MedServiceMedPersonal_isNotApproveRights) {
			return 0;
		} else return 1;
	},
	
	show: function(param) {
		var wnd = this;
		wnd.uslugaTestCombo.clearValue();
		wnd.uslugaTestCombo.setDisabled(true);
		wnd.MedServiceMedPersonal_isNotApproveRights = false;
		
		//получаем права пользователя на одобрение заявок/проб
		Ext.Ajax.request({
			url: '/?c=MedService&m=getApproveRights',
			params:{
				MedPersonal_id: getGlobalOptions().medpersonal_id,
				MedService_id: arguments[0].MedService_id,
				armMode: 'Lis'
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					wnd.MedServiceMedPersonal_isNotApproveRights = result[0].MedServiceMedPersonal_isNotApproveRights;
				}
			}
		});
		
		sw.Promed.swForm250u.superclass.show.apply(this, arguments);

		var curr_date = new Date();
		var curMonth = curr_date.getMonth() + 1;
		curMonth = (curMonth < 10 ? '0'+curMonth.toString() : curMonth.toString());

		if (param != undefined && param.MedService_id != undefined) {
			this.MedService_id = param.MedService_id;
			this.MedServiceType_SysNick = param.MedServiceType_SysNick;
		}
		//this.Lpu_id = param.Lpu_id;

		wnd.uslugaComplexCombo.getStore().removeAll();
		wnd.uslugaComplexCombo.getStore().baseParams.MedService_id = wnd.MedService_id;
		//wnd.uslugaComplexCombo.getStore().baseParams.Lpu_id = wnd.Lpu_id;
		wnd.uslugaComplexCombo.getStore().baseParams.linkedMesServiceOnly = param.MedServiceType_SysNick == "reglab";
		wnd.uslugaComplexCombo.getStore().baseParams.medServiceComplexOnly = true; // только комплексные
		wnd.uslugaComplexCombo.getStore().baseParams.level = 0;
		wnd.uslugaComplexCombo.getStore().load();
		wnd.searchData();
	},
	
	refreshGrid: function(mode) {
		var wnd = this;
		var viewframe = wnd.DataGridForm250u;
		var grid = viewframe.getGrid();
		var columnsConfig = grid.getColumnModel().config;

		var con_insert_idx = 8; //индекс колонки, начиная с которой вставляются динамические колонки

		var con = new Array();
		var con_start = new Array();
		var con_end = new Array();
		var store_key = null;

		if (columnsConfig[0] && columnsConfig[0].dataIndex === 'MultiSelectValue') {
			con_start.push(columnsConfig[0]);//копируем столбец-селектор (иначе пропадает)
		}
		
		for(var i = 0; i < viewframe.stringfields.length; i++) {
			var obj = new Object();
			Ext.apply(obj, viewframe.stringfields[i]);
			if (i < con_insert_idx) {
				con_start.push(obj);
			} else {
				con_end.push(obj);
			}
			if (viewframe.stringfields[i].key) {
				store_key = viewframe.stringfields[i].name;
			}
		}

		wnd.DataGridForm250u.allQualityTests = {};
		var v_obj = wnd.DataGridForm250u.varColumns;
		//console.log('DataGrid Columns (v_obj):'); console.log(v_obj);
		con = con_start;
		
		if (Ext.isArray(v_obj)) {
			
			var listStore = {};
			
			for	(var i = 0; i < v_obj.length; i++) {
				var column = new Object();
				column.header = v_obj[i].TestName;
				column.dataIndex = 'UslugaComplex_'+v_obj[i].UslugaComplex_id;
				column.name = 'UslugaComplex_'+v_obj[i].UslugaComplex_id;
				column.width = 120;
				//column.type = 'float';
				column.type = 'string';
				column.sortable = true;
				column.editable = wnd.approveIsAllowed();//права на редактирование результата
				//column.editor = new Ext.form.NumberField({ allowNegative:false });
				if (column.editable){
					if (v_obj[i].AnalyzerTestType_id === '2') {
						
						wnd.DataGridForm250u.allQualityTests[v_obj[i].UslugaComplex_id] = 2;
						if (!listStore[v_obj[i].AnalyzerTest_id]) {
							//одно store для столбца
							listStore[v_obj[i].AnalyzerTest_id] = new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'QualitativeTestAnswerAnalyzerTest_id', type: 'int'},
									{name: 'QualitativeTestAnswerAnalyzerTest_Answer', type: 'string'}
								],
								key: 'QualitativeTestAnswerAnalyzerTest_id',
								sortInfo: {
									field: 'QualitativeTestAnswerAnalyzerTest_Answer'
								},
								url: '/?c=QualitativeTestAnswerAnalyzerTest&m=loadList'
							});
						}
						
						//console.log('listStore:'); console.log(listStore);
						
						column.editor = new sw.Promed.SwQualitativeTestAnswerAnalyzerTestCombo({
							//id: wnd.id + '_ResultCombo' + ..,
							editable: true,
							forceSelection: false,
							allowTextInput: true,
							useRawValueForGrid: true,
							AnalyzerTest_id: v_obj[i].AnalyzerTest_id,
							store: listStore[v_obj[i].AnalyzerTest_id],
							listeners: {
								'beforeshow': function(combo) {
									if (combo.getStore().getCount() <= 0) {//однократная загрузка хранилища
										combo.getStore().removeAll();
										combo.getStore().load({
											params: {
												AnalyzerTest_id: combo.AnalyzerTest_id
											}
										});
									}
								},
								'select': function(combo, record) {
									combo.setValue(record.get('QualitativeTestAnswerAnalyzerTest_id'));
									//combo.fireEvent('blur', combo);
								},
								'blur': function(combo) {
									wnd.DataGridForm250u.getGrid().stopEditing();
								}
							},
							allowBlank: true,
							listWidth: 300
						});
						
					} else {
						column.editor = new Ext.form.TextField({
							listeners: {
								'focus': function(elem){
									elem.selectText();
									//if (elem.getRawValue() === '') {
									//}
								}
							}
						});
						
					}
				}
				con.push(column);
			}
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Неверный формат данных (перечень тестов).'));
		}
		
		//con = con.concat(con_end); //столбцы справа
		column = {};
		column.header = langs('Примечание');
		column.dataIndex = 'EvnLabSample_Comment';
		column.name = 'EvnLabSample_Comment';
		column.width = 120;
		column.type = 'string';
		column.sortable = true;
		column.editable = wnd.approveIsAllowed();//права на редактирование результата
		if (column.editable){
			column.editor = new Ext.form.TextField({
				listeners: {
					'focus': function(elem){
						elem.selectText();
					}
				}
			});
		}
		con.push(column);

		var st_arr = new Array();
		for (var i = 0; i < con.length; i++) {
			if(con[i].name) {
				var fieldConf = {};
				fieldConf.name = con[i].name;
				if (i > con_insert_idx) fieldConf.sortType = "asInt";
				st_arr.push(fieldConf);
			}
		}

		var cm = new Ext.grid.ColumnModel(con);
		var store = null;

		if (mode && mode === 'only_column') {
			var store = grid.getStore();
		} else {
			var store = new sw.Promed.Store({
				id: 0,
				fields: st_arr,
				idProperty: store_key,
				data: new Array(),
				listeners: {
					load: function(store, record, options) {
						wnd.DataGridForm250u.rowCount = store.getCount();
					}
				}
			});
			store.proxy = new Ext.data.HttpProxy({
				url: wnd.DataGridForm250u.dataUrl
			});
		}
		grid.reconfigure(store, cm);
	},
	
	setVarColumns: function(callback) {
		var wnd = this;
		
		wnd.DataGridForm250u.varColumns = new Array();

		Ext.Ajax.request({
			params: {
				UslugaComplex_id: (wnd.UslugaComplex_id ? wnd.UslugaComplex_id : ''),
				MedService_id: wnd.MedService_id,
				Date: wnd.calendar.value,
				MedServiceType_SysNick: wnd.MedServiceType_SysNick
			},
			callback: function (options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);

				if (result && Ext.isArray(result)) {
					wnd.DataGridForm250u.varColumns = result;
					wnd.TestStore.loadData(result);

					wnd.uslugaComplexCombo.clearValue();
				}

				if (callback && typeof callback == 'function') {
					callback();
				}
			},
			url: '/?c=EvnLabSample&m=getTestListForm250'
		});
	},
	
	searchData: function () {
		var wnd = this;
		var params = {};
		
		if (wnd.UslugaComplex_id) {
			params.UslugaComplex_id = wnd.UslugaComplex_id;
		} else {
			params.UslugaComplex_id = '';
		}
		
		params.MedService_id = wnd.MedService_id;
		params.MedServiceType_SysNick = wnd.MedServiceType_SysNick;
		params.Date = wnd.calendar.value;
		
		wnd.DataGridForm250u.removeAll();
		
		this.setVarColumns(function() {
			//wnd.refreshGrid('only_column');
			wnd.refreshGrid();

			wnd.DataGridForm250u.loadData({params: params, globalFilters: params});
		});
	},
	
	doReset: function() {

	},
	stepDay: function(day)
	{
		var date = (this.calendar.getValue() || Date.parseDate(this.date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.calendar.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		this.date = Ext.util.Format.date(date, 'd.m.Y');
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	
	createFormActions: function() {
		/**
		 * Поле ввода даты для движения по календарю
		 */
		this.calendar = new sw.Promed.SwDateField(
		{
			id: this.id + '_calendar',
			fieldLabel: langs('Дата журнала'),
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			xtype: 'swdatefield',
			format: 'd.m.Y',
			listeners:
			{
				'keydown': function (inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ENTER) {
						e.stopEvent();
						this.stepDay(0);
						this.searchData();
					}
				}.createDelegate(this),
				'select': function () 
				{
					this.searchData();
				}.createDelegate(this)
			},
			value: new Date()
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			//text: langs('Предыдущий'),
			text: '',
			id:'prevArrowLis',
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.searchData();
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			//text: langs('Следующий'),
			text: '',
			id:'nextArrowList',
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
				this.searchData();
			}.createDelegate(this)
		});
		
		/**
		 * Поле фильтра по услугам
		 */
	this.uslugaComplexCombo = new sw.Promed.SwUslugaComplexPidCombo({
			fieldLabel:langs('Услуга'),
			emptyText: langs('Все исследования'),
			onTrigger2Click: function () {
				this.uslugaComplexCombo.clearValue();
				this.uslugaTestCombo.clearValue();
				this.uslugaTestCombo.setDisabled(true);
				this.filterGrid();
			}.createDelegate(this),
			listeners:{
				change: function(combo, newValue, oldValue) {
					this.uslugaTestCombo.clearValue();
					this.filterUslugaTestCombo(newValue);
				}.createDelegate(this)
			},
			width:500
		});
		this.uslugaTestCombo = new Ext.ux.Andrie.Select({
			allowBlank: true,
			disabled: true,
			anchor: '100%',
			xtype: 'swbaselocalcombo',
			displayField: 'TestName',
			valueField: 'UslugaComplex_id',
			store: this.TestStore,
			emptyText: langs('Все тесты исследования'),
			mode: 'local',
			width: 270,
			resizable: true,
			multiSelect: true,
			queryAction: 'all',
			lastQuery: ''
		});
	},

	filterGrid: function() {
		var store = this.DataGridForm250u.ViewGridModel.grid.store;
		store.clearFilter();

		var uslugaComplexId = this.uslugaComplexCombo.getValue();
		var uslugaTestList = this.uslugaTestCombo.getValue().split(",");

		if (uslugaComplexId == "") {
			this.uslugaTestCombo.setValue("");
			this.uslugaTestCombo.setDisabled(true);
			this.showAllColumns();
			return;
		}

		if (uslugaTestList == "") {
			uslugaTestList = [];
			var tests = this.uslugaTestCombo.store.data.items;
			for (var i = 0; i < tests.length; i++) uslugaTestList.push(tests[i].id);
		}

		store.filterBy(function(record) {
			var recordData = record.data;
			var flag = true;

			if (uslugaComplexId != "") flag &= recordData.UslugaComplexTest_pid.indexOf(uslugaComplexId) != -1;
			if (uslugaTestList.length != 0) {
				for (var i = 0; i < uslugaTestList.length; i++)
					flag &= recordData['UslugaComplex_' + uslugaTestList[i]] != "";
			}

			return flag;
		});

		this.hideColumns(uslugaTestList);
	},

	hideColumns: function(columnList) {
		var gridColumns = this.DataGridForm250u.getColumnModel().config;
		var patt = new RegExp(columnList.join("|"));
		for (var i = 9; i < gridColumns.length; i++) {
			if (!patt.test(gridColumns[i].name))
				this.DataGridForm250u.getColumnModel().setHidden(i, true);
			else this.DataGridForm250u.getColumnModel().setHidden(i, false);
		}
	},
	showAllColumns: function() {
		var gridColumns = this.DataGridForm250u.getColumnModel().config;
		for (var i = 9; i < gridColumns.length; i++) {
			this.DataGridForm250u.getColumnModel().setHidden(i, false);
		}
	},

	filterUslugaTestCombo: function(uslugaComplexId) {
		this.uslugaTestCombo.store.clearFilter();
		this.uslugaTestCombo.store.filter('UslugaComplex_pid', parseInt(uslugaComplexId));
		
		if (this.uslugaTestCombo.store.data.items.length > 0) {
			this.uslugaTestCombo.setDisabled(false);
		} else {
			this.uslugaTestCombo.setDisabled(true);
		}
	},

	export2Excel: function(grid, items) {
		var headers = [];
		for (var i in  grid.colModel.lookup) {
			if (grid.colModel.lookup[i].hidden) continue;
			if (!parseInt(i) || 0) continue;
			headers.push({
				name: grid.colModel.lookup[i].name,
				header: grid.colModel.lookup[i].header
			});
		}

		this.generateExcel(headers, items);
	},
	generateTable: function(headers, items) {
		var table = document.createElement("table");
		table.style.border = "1px solid black";
		var thead = document.createElement("thead");
		var tbody = document.createElement("tbody");
		var headRow = document.createElement("tr");
		headers.forEach(function(el) {
			var th = document.createElement("th");
			th.style.border = "1px solid black";
			th.appendChild(document.createTextNode(el.header));
			headRow.appendChild(th);
		});
		thead.appendChild(headRow);
		table.appendChild(thead); 
		items.forEach(function(el) {
			var tr = document.createElement("tr");
			for (var i = 0; i < headers.length; i++) {  
				var td = document.createElement("td");
				td.style.border = "1px solid black";
				td.appendChild(document.createTextNode(el.data[headers[i].name] || ""));
				tr.appendChild(td);
			}
			tbody.appendChild(tr);
		});
		table.appendChild(tbody);
		return table;
	},
	generateExcel: function(headers, items) {
		var dataType = 'application/vnd.ms-excel';
		var tableSelect = this.generateTable(headers, items);
		var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
		
		var filename = 'excel_data.xls';
		var downloadLink = document.createElement("a");
		document.body.appendChild(downloadLink);
		
		if (navigator.msSaveOrOpenBlob) {
			var blob = new Blob(['\ufeff', tableHTML], {
				type: dataType
			});
			navigator.msSaveOrOpenBlob( blob, filename);
		} else {
			downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
			downloadLink.download = filename;
			downloadLink.click();
		}
	},
	
    updateEvnLabSample: function(params, o) {
        var wnd = this;

        // добавляем в очередь
        wnd.queueUpdateEvnLabSample.push({
            params: params,
            o: o
        });
		var Sample_id = o.record.json.EvnLabSample_id;
		
		//запоминаем для каждой пробы сколько тестов в очереди на обработку
		if (!wnd.testsUpdateList[Sample_id]) wnd.testsUpdateList[Sample_id] = 0;
		wnd.testsUpdateList[Sample_id] += 1;
		
        // если в очереди уже что то было, выходим
        if (wnd.queueUpdateEvnLabSample.length > 1) {
            return false;
        }

        wnd.processQueueUpdateEvnLabSample();
    },
	queueUpdateEvnLabSample: [],
	testsUpdateList: [],
    processQueueUpdateEvnLabSample: function() {
        var wnd = this;

        // работаем с очередью
        if (wnd.queueUpdateEvnLabSample.length < 1) {
            return false;
        }

        // берём первые параметры из очереди
        var params = wnd.queueUpdateEvnLabSample[0].params;
        var o = wnd.queueUpdateEvnLabSample[0].o;
        
        // признак АРМ Лаборанта для расчетных тестов
        params.EvnLabSample_id = Number(o.record.json.EvnLabSample_id);
        params.UslugaTest_code = o.record.json.UslugaComplex_Code;

        Ext.Ajax.request({
            url: '/?c=EvnLabSample&m=updateResult',
            params: params,
            failure: function(response, options) {
                // убираем из очереди первый элемент и снова обрабатываем
                wnd.queueUpdateEvnLabSample.shift();
                wnd.processQueueUpdateEvnLabSample();
            },
            success: function(response, action) {
                // убираем из очереди первый элемент и снова обрабатываем
                wnd.queueUpdateEvnLabSample.shift();
                wnd.processQueueUpdateEvnLabSample();

                var result = Ext.util.JSON.decode(response.responseText);

                if (result[0].Error_Code === null && result[0].Error_Msg === null) {
					
                    if (o.record) {
						//отображаем отредактированный результат (дописываем если надо ед.изм.):
						if (o.rawvalue.trim() !== '+' && o.rawvalue.trim() !== '') {
							var uslugaComplexId = o.field.split('_')[1];
							if (uslugaComplexId) {
								var testInfo = o.record.json.testList[uslugaComplexId];
								if (testInfo) {
									if ( !wnd.DataGridForm250u.allQualityTests[uslugaComplexId] ) {
										o.record.set(o.field, ( o.rawvalue.trim().split(/\s+/)[0] + ' ' + (testInfo['UslugaTest_ResultUnit'] || '') ).trim() );
									} else {//"качественный" тест
										o.record.set( o.field, o.rawvalue.trim() );
									}
								}
							}
						}
						//коммитим (убираем красн треуг-ки) только после обработки всех тестов пробы:
						var Sample_id = o.record.json.EvnLabSample_id;
						wnd.testsUpdateList[Sample_id] -= 1;
						if (wnd.testsUpdateList[Sample_id] <= 0) {
							o.record.commit();
						}
                    }

                } else {
					
                    sw.swMsg.show({
                        icon: Ext.MessageBox.WARNING,
                        buttons: Ext.Msg.OK,
                        msg: langs('Ошибка при сохранении результатов. Проверьте данные, при необходимости исправьте и повторите попытку сохранения.'),
                        title: langs('Ошибка'),
                        fn: function() {
                            if (o.grid) {
                                o.grid.getStore().reload();
                            }
                        }
                    });
					
                }
            }
        });
    },
	
	initComponent: function() {
		var wnd = this;		
		this.TestStore = new Ext.data.Store({
			id: 'TestStore',
			extend: 'Ext.data.Store',
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'UslugaComplex_id'
			}, [
					{ mapping: 'AnalyzerTestType_id', name: 'AnalyzerTestType_id', type: 'int' },
					{ mapping: 'AnalyzerTest_SortCode', name: 'AnalyzerTest_SortCode', type: 'int' },
					{ mapping: 'AnalyzerTest_SysNick', name: 'AnalyzerTest_SysNick', type: 'string' },
					{ mapping: 'AnalyzerTest_id', name: 'AnalyzerTest_id', type: 'int' },
					{ mapping: 'TestName', name: 'TestName', type: 'string' },
					{ mapping: 'UslugaComplex_Name', name: 'UslugaComplex_Name', type: 'string' },
					{ mapping: 'UslugaComplex_Name_Gost', name: 'UslugaComplex_Name_Gost', type: 'string' },
					{ mapping: 'UslugaComplex_id', name: 'UslugaComplex_id', type: 'int' },
					{ mapping: 'UslugaComplex_pid', name: 'UslugaComplex_pid', type: 'int' }
				])
		});

		this.createFormActions();
		this.WindowToolbar = new Ext.Toolbar({
			id: this.id + '_Toolbar',
			//height: 50,
			items: [
				this.formActions.prev, 
				this.calendar,
				this.formActions.next,
				'-', 
				this.uslugaComplexCombo,
				'-',
				this.uslugaTestCombo,
				{
					text: langs('Сформировать'),
					handler: function() {
						wnd.filterGrid();
					},
					iconCls: 'search16'
				},
				'-',
				{
					iconCls: 'refresh16',
					text: langs('Обновить'),
					tooltip : langs("Обновить <b>(F5)</b>"),
					handler: function () {
						wnd.searchData();
					}.createDelegate(this)
				},
				'-', 
				{		
					xtype: 'button',
					iconCls: 'print16',
					//name: 'actions',
					text: langs('Печать'),
					tooltip : langs("Печать формы <b>(F9)</b>"),
					menu: new Ext.menu.Menu({
						items: [
							{name: 'printObjectListFull', text: langs('Печать всего списка'), 
								handler: function(){
									var grid = wnd.DataGridForm250u.getGrid();
									Ext.ux.GridPrinter.print(grid);
							}},
							{name: 'printObjectListSelected', text: langs('Печать списка выбранных'), 
								handler: function(){
									var grid = wnd.DataGridForm250u.getGrid();
									var params = {};
									params.selections = grid.getSelectionModel().getSelections();
									Ext.ux.GridPrinter.print(grid, params);
									//wnd.DataGridForm250u.printObjectListSelected();
							}},
							{name: 'exportObjectListFull', text: langs('Печать всего списка (xls)'), 
								handler: function(){
									var grid = wnd.DataGridForm250u.getGrid();
									wnd.export2Excel(grid, grid.store.data.items);
							}},
							{name: 'exportObjectListSelected', text: langs('Печать списка выбранных (xls)'), 
								handler: function(){
									var grid = wnd.DataGridForm250u.getGrid();
									wnd.export2Excel(grid, grid.getSelectionModel().getSelections());
							}}
						]
					})
				}
			]
		});
		
		wnd.DataGridForm250u = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			selectionModel: 'multiselect',
			//forcePrintMenu: true,
			//autoLoadData: true,
			border: true,
			root: 'data',
			dataUrl: '/?c=EvnLabSample&m=loadSampleListForm250',
			region: 'center',
			id: this.id + '_grid',
			saveAtOnce:false,
			//style: 'margin-bottom: 0px',
			
			stringfields: [
				{name: 'Grid_Editor', type: 'string', editor: new Ext.form.NumberField(), hidden: true },
				{name: 'EvnLabSample_id', header: 'EvnLabSample_id', key: true, hidden: true},
				{name: 'EvnLabSample_DelivDT', dataIndex: 'EvnLabSample_DelivDT', header: langs('Дата поступления биоматериала'), width: 80, sortable: true},
				{name: 'EvnLabSample_Num', dataIndex: 'EvnLabSample_Num', header: langs('№ пробы'), width: 80},
				{name: 'PatientName', dataIndex: 'PatientName', header: langs('ФИО'), width: 80, sortable: true},
				{name: 'LpuSection', dataIndex: 'LpuSection', header: langs('Отделение, палата, участок'), width: 80, sortable: true},
				{name: 'DiagName', dataIndex: 'DiagName', header: langs('Диагноз'), width: 80},
				{name: 'UslugaComplexTest_pid', header: 'UslugaComplexTest_pid', hidden: true},
				{name: 'EvnLabSample_Comment', dataIndex: 'EvnLabSample_Comment', header: langs('Примечание'), width: 80}
				
			],
			
			//title: '',
			toolbar: false,

			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
			],
			contextmenu: false,
			
			listeners: {
				'render': function(panel) {

				}
			},
			onAfterEdit: function(o) {
				
				if (o.field === 'EvnLabSample_Comment') {//поле "Примечание"
					
					if ( o.rawvalue.trim() === (o.originalValue || '') ) {
						o.record.commit(); //поле "Примечание" не изменилось
						return;
					}
					
					Ext.Ajax.request({
						url: '/?c=EvnLabSample&m=saveEvnLabSampleComment',
						params: {
							EvnLabSample_id: Number(o.record.json.EvnLabSample_id),
							EvnLabSample_Comment: o.rawvalue.trim()
						},
						failure: function(response, options) {

							sw.swMsg.show({
								icon: Ext.MessageBox.WARNING,
								buttons: Ext.Msg.OK,
								msg: langs('Ошибка при сохранении результатов. Проверьте данные, при необходимости исправьте и повторите попытку сохранения.'),
								title: langs('Ошибка'),
								fn: function() {
									if (o.grid) {
										o.grid.getStore().reload();
									}
								}
							});

						},
						success: function(response, action) {

							var result = Ext.util.JSON.decode(response.responseText);
							if (!result.Error_Msg) {
								if (o.record) {
									//коммитим (убираем красн треуг-ки) только после обработки всех тестов пробы:
									var Sample_id = o.record.json.EvnLabSample_id;
									if (!wnd.testsUpdateList[Sample_id] || wnd.testsUpdateList[Sample_id] <= 0) {
										o.record.commit();
									}
								}

							} else {

								sw.swMsg.show({
									icon: Ext.MessageBox.WARNING,
									buttons: Ext.Msg.OK,
									msg: langs('Ошибка при сохранении поля "Примечание". Проверьте данные, при необходимости исправьте и повторите попытку сохранения.'),
									title: langs('Ошибка'),
									fn: function() {
										if (o.grid) {
											o.grid.getStore().reload();
										}
									}
								});

							}
						}
					});
					
				} else {//результаты тестов
					
					if (
						( o.rawvalue.trim() === '' && o.originalValue.trim() === '+' ) ||
						( o.rawvalue.trim() === o.originalValue.trim() ) ||
						( o.originalValue.trim() === '' )
					) {
						if ( o.originalValue.trim() === '' ) Ext.Msg.alert(langs('Ошибка'), langs('Нет такого теста!'));
						o.record.set(o.field, o.originalValue);
						return;
					}

					if ( o.rawvalue.trim() === '' && o.originalValue.trim().length > 0 )
						o.record.set(o.field, '+');

					o.grid.stopEditing(true);

					if (o.field && o.record) {
						var field = o.field.split('_');
						var UslugaComplex_id = field[1];

						var resVal = o.rawvalue.trim();
						if (!wnd.DataGridForm250u.allQualityTests[UslugaComplex_id]) {//НЕ "качественный" тест
							//ячейка результата может содержать ед. измерения:
							resVal = resVal.split(/\s+/)[0];
						}
						params = {
							UslugaComplex_id: UslugaComplex_id,
							EvnLabSample_id: o.record.get('EvnLabSample_id'),
							UslugaTest_ResultValue: resVal,
							UslugaTest_id: o.record.json.testList[UslugaComplex_id].UslugaTest_id,
							UslugaTest_code: o.record.json.testList[UslugaComplex_id].UslugaComplex_Code,
							updateType: 'value',
							sourceName: 'form250'
						}
						wnd.updateEvnLabSample(params, o);
					}
				}
			}
		});
		
		Ext.apply(this, {
			layout: 'border',
			//region:'center',
			buttons:
			[{
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
			tbar: this.WindowToolbar,
			items:[
				wnd.DataGridForm250u
			]
		});
		sw.Promed.swForm250u.superclass.initComponent.apply(this, arguments);
	}
});