/**
 * swStorageCardWindow - Печать ТАП (бывшее Картохранилище)
 * Форма предназначена для поиска, просмотра и печати документов посещений пациентом поликлиники.
 */

sw.Promed.swStorageCardWindow = function(){

	this.pageSize = 100;
	this.AttachmentLpuBuilding_id = null;
	this.PersonEvnDataChecked = [];


	this.curDate = getGlobalOptions().date;
	this.calendarDateRangeField = new Ext.form.DateRangeField({
		width: 150,
		fieldLabel: langs('Период'),
		maxValue: new Date(),
		plugins: [
			new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
		],
		listeners: {
			select: function () {
				this.doLoadPanelTableArea();
			}
		}
	});

	// Периода дат
	this.toolbarDatePeriod = new Ext.Toolbar({
		items: [

			// Предыдущий
			new Ext.Action({
				text: langs('Предыдущий'),
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function() {
					// на один день назад
					this._calendarPrevDay();
					this.doLoadPanelTableArea();
				}.createDelegate(this)
			}),

			{
				xtype : "tbseparator"
			},

			// Период
			this.calendarDateRangeField,

			{
				xtype : "tbseparator"
			},

			// Следующий
			new Ext.Action({
				text: langs('Следующий'),
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function() {
					// на один день вперед
					this._calendarNextDay();
					this.doLoadPanelTableArea();
				}.createDelegate(this)
			}),

			{
				xtype: 'tbfill'
			},
			/*
			// День
			new Ext.Action({
				text: langs('День'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-day16',
				pressed: true,
				handler: function() {
					this._calendarCurrentDay();
					this.doLoadPanelTableArea();
				}.createDelegate(this)
			}),

			// Неделя
			new Ext.Action({
				text: langs('Неделя'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-week16',
				handler: function() {
					this._calendarCurrentWeek();
					this.doLoadPanelTableArea();
				}.createDelegate(this)
			}),

			// Месяц
			new Ext.Action({
				text: langs('Месяц'),
				xtype: 'button',
				toggleGroup: 'periodToggle',
				iconCls: 'datepicker-month16',
				handler: function() {
					this._calendarCurrentMonth();
					this.doLoadPanelTableArea();
				}.createDelegate(this)
			})
			*/
		]
	});


	this.formPanelFilter = new Ext.FormPanel({
		id: 'SCW_panelFilter',
		keys: [{
			key: Ext.EventObject.ENTER,
			fn: function(e) {
				// me.doSearch();
			},
			scope: this,
			stopEvent: true
		}],
		floatable: false,
		autoHeight: true,
		animCollapse: false,
		labelAlign: 'right',
		frame: true,
		border: false,
		xtype: 'form',
		items: [
			{
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight:true,
				collapsible:true,
				title: langs('Фильтр'),
				layout: 'column',
				bodyStyle:'background: #DFE8F6;',
				items: [
					{
						layout: 'form',
						labelWidth: 120,
						items: [
							// Врач посещения
							/*{
								layout: 'form',
								labelWidth: 120,
								items: [{
									fieldLabel: langs('Врач посещения'),
									hiddenName: 'MedPersonal_id',
									allowBlank: true,
									width: 400,
									xtype: 'swmedpersonalcombo',
									listeners: {
										render: function() {
											this.getStore().load({
												params: {
													LpuUnitType_SysNick: JSON.stringify(['polka', 'fap'])
												}
											});
										},
										change: this.doRefreshPanelTableArea.createDelegate(this)
									},
								}]

							},*/
							{
								fieldLabel: langs('Врач посещения'),
								hiddenName: 'MedStaffFact_id',
								lastQuery: '',
								labelWidth: 120,
								width: 400,
								listWidth: 650,
								anchor: '100%',
								xtype: 'swmedstafffactglobalcombo'
							},

							// Фамилия
							{
								layout: 'form',
								labelWidth: 120,
								items: [{

									fieldLabel: langs('Фамилия'),
									name: 'Person_Surname',
									maskRe: /[^_%]/,
									width: 200,
									xtype: 'textfieldpmw',
									listeners: {
										'keydown': function (inp, e){
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.doRefreshPanelTableArea();
											}
										}.createDelegate(this)
									}
								}]

							},

							// Имя
							{
								layout: 'form',
								labelWidth: 120,
								items: [{

									fieldLabel: langs('Имя'),
									name: 'Person_Firname',
									maskRe: /[^_%]/,
									width: 120,
									xtype: 'textfieldpmw',
									listeners: {
										'keydown': function (inp, e){
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.doRefreshPanelTableArea();
											}
										}.createDelegate(this)
									}
								}]

							},

							// Отчество
							{
								layout: 'form',
								labelWidth: 120,
								items: [{

									fieldLabel: langs('Отчество'),
									name: 'Person_Secname',
									maskRe: /[^_%]/,
									width: 200,
									xtype: 'textfieldpmw',
									listeners: {
										'keydown': function (inp, e){
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.doRefreshPanelTableArea();
											}
										}.createDelegate(this)
									}
								}]

							},

							// Дата рождения
							{
								layout: 'form',
								labelWidth: 120,
								items: [{

									fieldLabel: langs('Дата рождения'),
									format: 'd.m.Y',
									name: 'Person_Birthday',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									xtype: 'swdatefield',
									listeners: {
										//change: this.doRefreshPanelTableArea.createDelegate(this)
										'keydown': function (inp, e){
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.doRefreshPanelTableArea();
											}
										}.createDelegate(this)
									}
								}]

							},

							// Кнопки
							{
								layout: 'column',
								items: [

									// Найти
									// кнопка, при нажатии на кнопку производится поиск данных, удовлетворяющих выбранным на панели периода дат и панели фильтров параметрам.
									{
										layout: 'form',
										items: [{
											style: "padding-left: 20px",
											xtype: 'button',
											id: this.id + 'BtnSearch',
											text: 'Найти',
											iconCls: 'search16',
											handler: this.doRefreshPanelTableArea.createDelegate(this)
										}]
									},

									// Сброс
									// кнопка, при нажатии на кнопку значения, выбранные на панели фильтров, сбрасываются.
									{
										layout: 'form',
										items: [{
											style: "padding-left: 10px",
											xtype: 'button',
											id: this.id + 'BtnClear',
											text: 'С<u>б</u>рос',
											iconCls: 'reset16',
											handler: this.doClearPanelFilter.createDelegate(this)
										}]
									}
								]
							}
						]
					}
				]
			}
		]
	});



	// -----------------------------------------------------------------------------------------------------------------
	// Панель периода дат и Панель фильтров
	this.panelDatePeriodAndFilter = new Ext.Panel({
		region: 'north',
		frame: true,
		border: false,
		autoHeight: true,

		// Период дат
		tbar: this.toolbarDatePeriod,

		items: [

			// Фильтры
			this.formPanelFilter
		]
	});
	// -----------------------------------------------------------------------------------------------------------------





	// -----------------------------------------------------------------------------------------------------------------
	// Верхнюю панель инструментов + Табличную область
	this.optionsTableArea = {
		baseParams: {
			Lpu_id: getGlobalOptions().lpu_id
		},
		params:{
			start: 0,
			limit: this.pageSize
		}
	};


	// this.storeTableArea = new Ext.data.JsonStore({
	// 	url:'/?c=EvnVizit&m=loadEvnVizitPLGridAll',
	// 	root: 'data',
	// 	totalProperty: 'totalCount',
	// 	autoLoad: false,
	// 	remoteSort: true,
	// 	fields: [
	// 		{name: 'Person_id', mapping: 'Person_id'},
	// 		{name: 'PersonEvn_id', mapping: 'PersonEvn_id'},
	// 		{name: 'Server_id', mapping: 'Server_id'},
	// 		{name: 'EvnVizitPL_setDate', mapping: 'EvnVizitPL_setDate'},
	// 		{name: 'EvnVizitPL_setTime', mapping: 'EvnVizitPL_setTime'},
	// 		{name: 'Person_Fio', mapping: 'Person_Fio'},
	// 		{name: 'Person_Birthday', mapping: 'Person_Birthday'},
	// 		{name: 'LpuSection_Name', mapping: 'LpuSection_Name'},
	// 		{name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio'}
	// 	]
	// });
	this.storeTableArea = null;
	// this.panelTableArea = new Ext.grid.GridPanel({
	// 	loadMask: true,
	// 	stripeRows: true,
	// 	region: 'center',
	// 	store: this.storeTableArea,
	// 	bbar: new Ext.PagingToolbar ({
	// 		store: this.storeTableArea,
	// 		pageSize: this.pageSize,
	// 		displayInfo: true,
	// 		displayMsg: langs('Отображаемые строки {0} - {1} из {2}'),
	// 		emptyMsg: langs("Нет записей для отображения"),
	// 		listeners: {
	//
	// 			// sb == this (PagingToolbar)
	// 			change: function(sb, d){
	//
	// 				var jsonData = sb.store.reader.jsonData;
	//
	// 				if (jsonData.overLimit) {
	// 					sb.next.setDisabled(false);
	// 					sb.last.setDisabled(true);
	// 				} else {
	// 					sb.next.setDisabled(d.activePage == d.pages);
	// 					sb.last.setDisabled(d.activePage == d.pages);
	// 				}
	// 			}
	// 		},
	// 	}),
	//
	// 	columns: [
	// 		{header: langs("Дата"), sortable: true, dataIndex: 'EvnVizitPL_setDate'},
	// 		{header: langs("Время"), sortable: true, dataIndex: 'EvnVizitPL_setTime'},
	// 		{header: langs("Пациент"), sortable: true, dataIndex: 'Person_Fio'},
	// 		{header: langs("Дата рождения"),sortable: true, dataIndex: 'Person_Birthday'},
	// 		{header: langs("Отделение"), sortable: true, dataIndex: 'LpuSection_Name'},
	// 		{header: langs("Врач"), sortable: true, dataIndex: 'MedPersonal_Fio'}
	// 	],
	// 	viewConfig: {
	// 		forceFit: true,
	// 	},
	// 	tbar: [
	// 		new Ext.Action({
	// 			name: 'action_openemk',
	// 			text: langs('Открыть ЭМК'),
	// 			tooltip: langs('Открыть электронную медицинскую карту пациента'),
	// 			iconCls : 'open16',
	// 			handler: this.doOpenEMK.createDelegate(this)
	// 		}),
	// 		new Ext.Action({
	// 			name: 'action_refresh',
	// 			text: langs('Обновить'),
	// 			iconCls: 'refresh16',
	// 			handler: this.doRefreshPanelTableArea.createDelegate(this)
	// 		}),
	// 		new Ext.Action({
	// 			name: 'action_print',
	// 			text: langs('Печать'),
	// 			iconCls: 'print16',
	// 			handler: this.doPrint.createDelegate(this)
	// 		})
	// 	],
	// 	sm: new Ext.grid.RowSelectionModel({
	// 		singleSelect: true,
	// 		listeners: {
	// 			rowselect: function(sm, rowIdx, record) {
	// 				if ( ! record ) {
	// 					return false;
	// 				}
	// 			}
	// 		}
	// 	})
	// });
	this.panelTableArea = new sw.Promed.ViewFrame({
		autoExpandColumn: 'autoexpand',
		autoExpandMin: 100,
		region: 'center',
		pageSize: 100,
		actions: [
			{ name: 'action_add', hidden: true},
			{ name: 'action_edit', hidden: true},
			{ name: 'action_view', hidden: true},
			{ name: 'action_delete', hidden: true},
			{ name: 'action_refresh', hidden: true},
			{ name: 'action_print', hidden: true}
		],
		autoLoadData: false,
		stripeRows: true,
		stringfields: [
			{ name: 'Person_id', hidden: true},
			{ name: 'PersonEvn_id', hidden: true},
			{ name: 'Server_id', hidden: true},
			{ name: 'EvnVizitPL_pid', hidden: true},
			{ name: 'check', sortable: false, width: 40, renderer: this.checkRenderer,
				header: "<input type='checkbox' id='SCW_checkAll' onClick='getWnd(\"swStorageCardWindow\").checkAll(this.checked);'>"
			},
			{name: 'Is_Checked',type: 'int', header: 'is_checked', hidden: true},
			{ name: 'EvnVizitPL_setDate', header: langs("Дата"), width: 120},
			{ name: 'EvnVizitPL_setTime', header: langs('Время'), width: 120},
			{ name: 'Person_Fio', header: langs('Пациент'), width: 240},
			{ name: 'Person_Birthday', header: langs('Дата рождения'), width: 120},
			{ name: 'LpuSection_Name', header: langs('Отделение'), id: "autoexpand"},
			{ name: 'MedPersonal_Fio', header: langs('Врач'), width: 240}
		],
		onLoadData: function(sm, index, record) {
			if(!sm) {
				this.panelTableArea.removeAll();
				return false;
			}
		}.createDelegate(this),
		paging: true,
		dataUrl: '/?c=EvnVizit&m=loadEvnVizitPLGridAll',
		root: 'data',
		totalProperty: 'totalCount'
	});
	// -----------------------------------------------------------------------------------------------------------------


	sw.Promed.swStorageCardWindow.superclass.constructor.call(this, {
		title: langs('Печать ТАП'),
		id: 'swStorageCardWindow',
		maximized: true,
		layout: 'border',
		items: [
			this.panelDatePeriodAndFilter,
			this.panelTableArea
		],


		// Нижнюю панель инструментов
		buttons: [
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: this.doOpenHelp.createDelegate(this)
			}, {
				text      : langs('Закрыть'),
				tabIndex  : -1,
				tooltip   : langs('Закрыть'),
				iconCls   : 'cancel16',
				handler   : this.doCloseWindow.createDelegate(this)
			}
		]

	});
}



Ext.extend(sw.Promed.swStorageCardWindow, sw.Promed.BaseForm, {

	_addButton_OpenEmk: function(){
		this.panelTableArea.addActions({
			name: 'action_openemk',
			text: langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls : 'open16',
			handler: this.doOpenEMK.createDelegate(this)
		});
	},
	_addButton_Print: function(){
		this.panelTableArea.addActions({
			name: 'action_print16',
			text: langs('Печать списка'),
			iconCls: 'print16',
			handler: this.doPrint.createDelegate(this)
		});
	},
	_addButton_GroupPrintTAP: function(){
		this.panelTableArea.addActions({
			name: 'action_grouprint16',
			text: langs('Групповая печать ТАП'),
			iconCls: 'print16',
			handler: this.doGroupPrintTAP.createDelegate(this)
		});
	},
	_addButton_Refresh: function(){
		this.panelTableArea.addActions({
			name: 'action_refresh16',
			text: langs('Обновить'),
			iconCls: 'refresh16',
			handler: this.doRefreshPanelTableArea.createDelegate(this)
		});
	},

	_addButtons: function(){

		// Последовательность важна!
		this._addButton_Print();
		this._addButton_GroupPrintTAP();
		this._addButton_Refresh();
		this._addButton_OpenEmk();
	},

	show: function(){

		this._addButtons();

		sw.Promed.swStorageCardWindow.superclass.show.apply(this);

		if(!Ext.isEmpty(arguments[0].LpuBuilding_id)){
			this.AttachmentLpuBuilding_id = arguments[0].LpuBuilding_id;
			this.loadComboMedStaffFact();
		}else{
			this.getLpuBuildingByMedServiceId(function(){
				this.loadComboMedStaffFact();
			}.createDelegate(this));
		}
		this.PersonEvnDataChecked = [];
	},

	initComponent: function(){
		this.curDate = new Date();
		this.calendarDateRangeField.setValue(Ext.util.Format.date(this.curDate, 'd.m.Y') + ' - ' + Ext.util.Format.date(this.curDate, 'd.m.Y'));

		this.storeTableArea = this.panelTableArea.getGrid().getStore();


		sw.Promed.swStorageCardWindow.superclass.initComponent.apply(this);
	},




	// Помощь
	doOpenHelp: function(){
		ShowHelp(this.title);
	},

	// Закрыть
	doCloseWindow: function(){
		this.hide();
	},

	// Загрузка данных
	doLoadPanelTableArea: function(){

		var formPanelFilter = this.formPanelFilter.getForm();

		//this.optionsTableArea.baseParams.MedPersonal_id = formPanelFilter.findField('MedPersonal_id').getValue();
		this.optionsTableArea.baseParams.MedStaffFact_id = formPanelFilter.findField('MedStaffFact_id').getValue();
		this.optionsTableArea.baseParams.Person_Surname = formPanelFilter.findField('Person_Surname').getValue();
		this.optionsTableArea.baseParams.Person_Firname = formPanelFilter.findField('Person_Firname').getValue();
		this.optionsTableArea.baseParams.Person_Secname = formPanelFilter.findField('Person_Secname').getValue();
		this.optionsTableArea.baseParams.Person_Birthday = formPanelFilter.findField('Person_Birthday').getValue();
		this.optionsTableArea.baseParams.begDate = Ext.util.Format.date(this.calendarDateRangeField.getValue1(), 'd.m.Y');
		this.optionsTableArea.baseParams.endDate = Ext.util.Format.date(this.calendarDateRangeField.getValue2(), 'd.m.Y');
		this.optionsTableArea.callback = function(){}.createDelegate(this);

		this.storeTableArea.baseParams = this.optionsTableArea.baseParams;
		this.storeTableArea.load(this.optionsTableArea);


		return this;
	},

	// Обновляем вывод данных
	doRefreshPanelTableArea: function(){
		this.storeTableArea.removeAll();
		this.storeTableArea.clearFilter();
		this.doLoadPanelTableArea();

		return this;
	},

	// Сброс фильров
	doClearPanelFilter: function(){

		var formPanelFilter = this.formPanelFilter.getForm();

		// formPanelFilter.findField('MedPersonal_id').setValue(null);
		formPanelFilter.findField('MedStaffFact_id').setValue(null);
		formPanelFilter.findField('Person_Surname').setValue(null);
		formPanelFilter.findField('Person_Firname').setValue(null);
		formPanelFilter.findField('Person_Secname').setValue(null);
		formPanelFilter.findField('Person_Birthday').setValue(null);

		this.doLoadPanelTableArea();

		return this;
	},

	// Открыть ЭМК
	doOpenEMK: function(){
		if ( ! this.panelTableArea.getGrid().getSelectionModel().getSelected()){
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}

		var record = this.panelTableArea.getGrid().getSelectionModel().getSelected();

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			// userMedStaffFact: this.userMedStaffFact,
			// MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			showOnlyId: 'EvnPL_' + record.get('EvnVizitPL_pid'),
			ARMType: 'common',
			readOnly: true
			// callback: function() {}
		});
	},

	getLpuBuildingByMedServiceId: function(cb){
		var params = {};
		var callback = cb || false;
		Ext.Ajax.request({
			params: params,
			failure: function (result_form, action) {
				log('error getLpuBuildingByMedServiceId');
			},
			callback: function(options, success, response) {
				if (success && response.responseText != ''){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj && parseInt(response_obj[0]['LpuBuilding_id'])){
						this.AttachmentLpuBuilding_id = parseInt(response_obj[0]['LpuBuilding_id']);
					}
					
					if(callback && typeof callback == 'function') callback();
				}
				else{
					log('error getLpuBuildingByMedServiceId');
				}
			}.createDelegate(this),
			url: '?c=PersonAmbulatCard&m=getLpuBuildingByMedServiceId'
		});
	},

	loadComboMedStaffFact: function(){
		var win = this;
		var formPanelFilter = win.formPanelFilter.getForm();
		var comboMedStaffFact = formPanelFilter.findField('MedStaffFact_id');

		comboMedStaffFact.clearValue();
		comboMedStaffFact.getStore().removeAll();
		var msfFilter ={
			Lpu_id: getGlobalOptions().lpu_id,
			arrayLpuUnitType: [1,11]
		};
		if(this.AttachmentLpuBuilding_id) msfFilter.LpuBuilding_id = this.AttachmentLpuBuilding_id;
		setMedStaffFactGlobalStoreFilter(msfFilter);
		comboMedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
	},

	doPrint: function(){
		this.panelTableArea.printRecords();
	},

	doGroupPrintTAP: function(){
		var arrEvn = [];
		this.panelTableArea.getGrid().getStore().each(function(record){
			if(record.get('EvnVizitPL_pid') && record.get('Is_Checked') == 1){
				arrEvn.push(record.get('EvnVizitPL_pid'))
			}
		});	
		if(arrEvn.length == 0){
			Ext.Msg.alert(langs('Ошибка'), langs('Нет выбранных записей!'));
		}else{
			var ReportParams = '';
			var ReportFileName = 'f025-1u_all.rptdesign';
			if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
				//двусторонняя
				ReportParams = '&prmFntPnt=1&prmBckPnt=1';
				printBirt({
					'Report_FileName': ReportFileName,
					'Report_Params': ReportParams + '&s=' + arrEvn.join(','),
					'Report_Format': 'pdf'
				});
			}else{
				ReportParams = '&prmFntPnt=1&prmBckPnt=0';
				printBirt({
					'Report_FileName': ReportFileName,
					'Report_Params': ReportParams + '&s=' + arrEvn.join(','),
					'Report_Format': 'pdf'
				});
				ReportParams = '&prmFntPnt=0&prmBckPnt=1';
				printBirt({
					'Report_FileName': ReportFileName,
					'Report_Params': ReportParams + '&s=' + arrEvn.join(','),
					'Report_Format': 'pdf'
				});
			}
			return true;
		}
	},
	checkRenderer: function(v, p, record) {
		var id = record.get('PersonEvn_id');
		if(id) {
			var value = 'value="'+id+'"';
			var checked = record.get('Is_Checked')!=0 ? ' checked="checked"' : '';
			var onclick = 'onClick="getWnd(\'swStorageCardWindow\').checkOne(this.value);"';

			return '<input id="checkbox_'+id+'" type="checkbox" '+value+' '+checked+' '+onclick+'>';
		}
	},
	checkOne: function(id){
		var form = this;
		var form = this;
		var PersonEvn_id = id;
		var array_index = form.PersonEvnDataChecked.indexOf(PersonEvn_id);
		this.panelTableArea.getGrid().getStore().each(function(record){
			if(record.get('PersonEvn_id') == PersonEvn_id){
				if(record.get('Is_Checked') == 0) //Было 0, т.е. при нажатии устанавливаем галочку
				{
					record.set('Is_Checked',1);
					if(array_index == -1){
						form.PersonEvnDataChecked.push(PersonEvn_id);
					}
				}
				else{ //Было 1, т.е. при нажатии снимаем галочку
					record.set('Is_Checked',0);
					if(array_index > -1){
						form.PersonEvnDataChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
					}
				}
			}
		});
	},
	checkAll: function(check){
		var form = this;
		var array_index = -1;

		if(check){
			this.panelTableArea.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 1);
				array_index = form.PersonEvnDataChecked.indexOf(record.get('PersonEvn_id'));
				if(array_index == -1){
					form.PersonEvnDataChecked.push(record.get('PersonEvn_id'));
				}
			});
		}else{
			this.panelTableArea.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 0);
				array_index = form.PersonEvnDataChecked.indexOf(record.get('PersonEvn_id'));
				if(array_index > -1){
					form.PersonEvnDataChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
				}
			});
		}
	},

	_calendarStepDay: function(day){
		var frm = this;
		var date1 = (frm.calendarDateRangeField.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.calendarDateRangeField.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		if(date2 <= frm.curDate) {
			frm.calendarDateRangeField.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		}
	},
	_calendarPrevDay: function(){
		this._calendarStepDay(-1);
	},
	_calendarNextDay: function(){
		this._calendarStepDay(1);
	},
	_calendarCurrentDay: function(){
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.calendarDateRangeField.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	_calendarCurrentWeek: function(){
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.calendarDateRangeField.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	_calendarCurrentMonth: function(){
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.calendarDateRangeField.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
});
