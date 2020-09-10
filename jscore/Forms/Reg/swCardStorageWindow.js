/**
 * swCardStorageWindow - Картохранилище
 * Форма предназначена для поиска, просмотра амбулаторных карт и изменения их движения.
 */

sw.Promed.swCardStorageWindow = function(){
	var curWnd = this;
	this.pageSize = 50;
	this.AttachmentLpuBuilding_id = null;

	this.curDate = getGlobalOptions().date;
	this.FindByBarcode = false;
	this.divPopUpMessage = document.createElement('div');
	/*this.calendarDateRangeField = new Ext.form.DateRangeField({
		width: 150,
		fieldLabel: langs('Период'),
		plugins: [
			new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
		],
		listeners: {
			select: function () {
				this.doLoadPanelTableArea();
			}
		}
	});*/
	
	// if ( Ext.globalOptions.others.enable_barcodereader ) {
	// 	log(langs('Подключаем апплет для сканера штрих-кодов'));
	// 	sw.Applets.BarcodeScaner.initBarcodeScaner();
	// }
	this.formPanelFilter = new Ext.FormPanel({
		owner: curWnd,
		id: 'CSW_panelFilter',
		keys: [{
			key: Ext.EventObject.ENTER,
			fn: function(e) {
				debugger;
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
						labelWidth: 150,
						items: [
							{
								valueField: 'header',
								comboData: [
									['currentStorage','Текущее картохранилище'],
									['otherStorage','Другие картохранилища'],
									['allStorage','Все']
								],
								comboFields: [
									{name: 'header', type:'string'},
									{name: 'header_Name', type:'string'}
								],
								value: 'allStorage',
								fieldLabel: langs('Прикрепление карты'),
								width: 140,
								xtype: 'swstoreinconfigcombo',
								hiddenName: 'CardAttachment',
								name: 'CardAttachment'
							},{
								valueField: 'header',
								comboData: [
									['openCard','Открытые карты'],
									['closeCard','Закрытые карты'],
									['allCard','Все']
								],
								comboFields: [
									{name: 'header', type:'string'},
									{name: 'header_Name', type:'string'}
								],
								value: 'allCard',
								fieldLabel: langs('Карта открыта/закрыта '),
								width: 140,
								xtype: 'swstoreinconfigcombo',
								hiddenName: 'CardIsOpenClosed',
								name: 'CardIsOpenClosed'
							},{
								xtype: 'textfieldpmw',
								width: 120,
								id: 'field_numberCard',
								fieldLabel: langs('№ амб. карты '),
								listeners:
								{
									'keydown': function (inp, e)
									{
										if (e.getKey() == Ext.EventObject.ENTER)
										{
											e.stopEvent();
											this.doRefreshPanelTableArea();
										}
									}.createDelegate(this)
								},
								name: 'field_numberCard'
							}
						]
					},
					{
						layout: 'form',
						labelWidth: 150,
						width: 400,
						items: [
							{
								fieldLabel: langs('Местонахождение'),
								hiddenName: 'AmbulatCardLocatType_id',
								anchor:'100%',
								xtype: 'swambulatcardlocattypecombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var filter_form = this.formPanelFilter.getForm();
										var comboLpuBuilding = filter_form.findField('LpuBuilding_id');
										var comboMedStaffFact = filter_form.findField('MedStaffFact_id');
										comboLpuBuilding.disable(); comboLpuBuilding.clearValue();
										comboMedStaffFact.disable(); comboMedStaffFact.clearValue();
										if(newValue){
											if(newValue == 10){
												comboLpuBuilding.enable();
											}else if(newValue == 2){
												comboMedStaffFact.enable();
											}
										}
									}.createDelegate(this)
								}
							},{
								hiddenName: 'LpuBuilding_id',
								name: 'LpuBuilding_id',
								fieldLabel: langs('Картохранилище'),
								listeners: {
									'change': function(combo, newValue, oldValue) {
										//...
									}
								},
								listWidth: 300,
								anchor:'100%',
								xtype: 'swlpubuildingglobalcombo'
							},{
								fieldLabel: langs('Врач'),
								hiddenName: 'MedStaffFact_id',
								lastQuery: '',
								listWidth: 650,
								anchor: '100%',
								xtype: 'swmedstafffactglobalcombo'
							}
						]
					},
					{
						layout: 'form',
						id: 'blockSearchPerson',
						items: [
							{
								fieldLabel: 'Фамилия',
								name: 'Person_SurName',
								hiddenName: 'Person_SurName',
								maskRe: /[^_%]/,
								width: 120,
								xtype: 'textfieldpmw',
								listeners:
								{
									'keydown': function (inp, e)
									{
										if (e.getKey() == Ext.EventObject.ENTER)
										{
											e.stopEvent();
											this.doRefreshPanelTableArea();
										}
									}.createDelegate(this)
								}
							},{
								fieldLabel: 'Имя',
								name: 'Person_FirName',
								hiddenName: 'Person_FirName',
								maskRe: /[^_%]/,
								width: 120,
								xtype: 'textfieldpmw',
								listeners:
								{
									'keydown': function (inp, e)
									{
										if (e.getKey() == Ext.EventObject.ENTER)
										{
											e.stopEvent();
											this.doRefreshPanelTableArea();
										}
									}.createDelegate(this)
								}
							},{
								fieldLabel: 'Отчество',
								name: 'Person_SecName',
								hiddenName: 'Person_SecName',
								maskRe: /[^_%]/,
								width: 120,
								xtype: 'textfieldpmw',
								listeners:
								{
									'keydown': function (inp, e)
									{
										if (e.getKey() == Ext.EventObject.ENTER)
										{
											e.stopEvent();
											this.doRefreshPanelTableArea();
										}
									}.createDelegate(this)
								}
							},{
								fieldLabel: 'ДР',
								format: 'd.m.Y',
								name: 'Person_Birthday',
								hiddenName: 'Person_Birthday',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								width: 100,
								xtype: 'swdatefield'
							}
						]
					},
					{
						layout: 'form',
						labelWidth: 110,
						items: [
							/*
							// Врач посещения
							{
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
											text: 'Найти',
											id: 'searchButtonSearch',
											iconCls: 'search16',
											handler: this.doRefreshPanelTableArea.createDelegate(this)
										}]
									},
									{
										layout: 'form',
										items: [{
											style: "padding-left: 10px",
											xtype: 'button',
											text: 'Найти по штрих-коду',
											id: 'searchButtonByBarcode',
											iconCls: 'idcard16',
											handler: this.funGetAmbulatCardData.createDelegate(this)
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
											id: 'searchButtonClear',
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
	// Панель фильтров
	this.panelFilter = new Ext.Panel({
		region: 'north',
		frame: true,
		border: false,
		autoHeight: true,

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
	this.storeTableArea = null;
	
	this.tableAmbulatCardArea = new sw.Promed.ViewFrame({
		//autoExpandColumn: 'autoexpand',
		//autoExpandMin: 100,
		actions: [
			{ name: 'action_add', hidden: true},
			{ name: 'action_edit', hidden: true},
			{ name: 'action_view', hidden: true},
			{ name: 'action_delete', hidden: true},
			{ name: 'action_refresh', hidden: false},
			{ name: 'action_print', hidden: true}
		],
		autoLoadData: false,
		stripeRows: true,
		stringfields: [
			{ name: 'PersonAmbulatCard_id', type: 'int', hidden: true, header: 'ID', key: true },
			{ name: 'Person_SurName', type: 'string', hidden: true },
			{ name: 'Person_FirName', type: 'string', hidden: true },
			{ name: 'Person_SecName', type: 'string', hidden: true },
			{ name: 'Person_FIO', type: 'string', header: langs('ФИО пациента'), width: 200 },
			{ name: 'Person_Birthday', type: 'date', header: langs('Дата рождения пациента'), renderer: Ext.util.Format.dateRenderer('d.m.Y') },
			{ name: 'MainLpu_Nick', type: 'string', header: langs('МО прикрепления (осн.)'), width: 120 },
			{ name: 'LpuRegion_Name', type: 'string', header: langs('участок'), width: 100},
			{ name: 'GinLpu_Nick', type: 'string', header: langs('МО прикрепления (гинек.)'), width: 80},
			{ name: 'StomLpu_Nick', type: 'string', header: langs('МО прикрепления (стомат.)'), width: 80},
			{ name: 'AttachmentLpuBuilding_Name', type: 'string', header: langs('Подразделение прикрепления карты'), width: 200},
			{ name: 'PersonAmbulatCard_Num', type: 'string', header: langs('№ амб. карты'), width: 150},
			{ name: 'Location_Amb_Cards', type: 'string', header: langs('Местонахождение амб. карты'), width: 300 },
			{ name: 'PersonAmbulatCardLocat_begDate', type: 'date', header: langs('Дата и время движения '), renderer: Ext.util.Format.dateRenderer('d.m.Y') },
			{ name: 'EmployeeFIO', type: 'string', header: langs('Сотрудник'), width: 150 },
			{ name: 'MedStaffFact', type: 'string', header: langs('Должность сотрудника '), width: 150 },
			{ name: 'PersonAmbulatCardLocat_Desc', type: 'string', header: langs('Пояснение'), width: 150 },
		],
		onRowSelect: function(sm, index, record) {
			var personAmbulatCard_id = record.get('PersonAmbulatCard_id');
			if(!personAmbulatCard_id) {
				this.TableMovementsAmbulatoryCard.removeAll();
			}else{
				this.TableMovementsAmbulatoryCard.getGrid().getStore().load({params: {PersonAmbulatCard_id: personAmbulatCard_id}});
			}
		}.createDelegate(this),
		onLoadData: function(sm, index, record) {
			if(!sm) {
				this.tableAmbulatCardArea.removeAll();
				return false;
			}
		}.createDelegate(this),
		paging: true,
		pageSize: 50,
		stateful: true,
		region: 'center',
		dataUrl: '/?c=PersonAmbulatCard&m=loadInformationAmbulatoryCards',
		root: 'data',
		totalProperty: 'totalCount',
		title: 'Таблица амбулаторных карт',
	});
	//Таблица движений амбулаторной карты
	this.TableMovementsAmbulatoryCard = new sw.Promed.ViewFrame({	
			tbActions: true,
			region: 'south',
			actions: [
				{ name: 'action_add', hidden: false, handler: function(){
					this.openFormMovementsAmbulatoryCard('add');
				}.createDelegate(this) },
				{ name: 'action_edit', hidden: false, handler: function(){
					this.openFormMovementsAmbulatoryCard('edit');
				}.createDelegate(this) },
				{ name: 'action_view', hidden: false, handler: function(){
					this.openFormMovementsAmbulatoryCard('view');
				}.createDelegate(this) },
				{ name: 'action_delete', hidden: false, handler: function(){
					this.deleteMovementsAmbulatoryCard();
				}.createDelegate(this) },
				{ name: 'action_print', hidden: true },
				{ name: 'action_refresh', hidden: true}
			],
			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			groupTextTpl:'{text} ({[values.rs.length]} {[values.rs.length == 1 ? "запись": ( values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '?c=PersonAmbulatCard&m=getPersonAmbulatCardLocatList',
			root: 'data',
			//stateful: true,
			id: 'TableMovementsAmbulatoryCardGrid',
			/*onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				//..
			}.createDelegate(this),
			onLoadData: function(sm, index, record) {
				//debugger;
			},
			onRowSelect: function(sm, index, record) {
				//..
			}.createDelegate(this),*/
			stringfields: [
				// Поля для отображение в гриде
				{name: 'PersonAmbulatCardLocat_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonAmbulatCard_id', type: 'int', hidden: true},
				{name: 'PersonAmbulatCardLocat_begDate', type: 'string',  width: 200, header: langs('Дата и время движения')},
				{name: 'AmbulatCardLocatType', type: 'string',  width: 200, header: langs('Местонахождение')},
				{name: 'FIO', type: 'string', width: 200, header: langs('ФИО сотрудника')},
				{name: 'MedStaffFact', type: 'string', width: 200, header: langs('Должность сотрудника')},
				{name: 'LpuBuilding_Name', type: 'string', width: 200, header: langs('Подразделение')},
				{name: 'PersonAmbulatCardLocat_Desc', type: 'string', header: langs('Пояснение'), id: 'autoexpand'}
			],
			title: 'Таблица движений амбулаторной карты',
			//paging: true,
			//pageSize: 100,
			//root: 'data',
			//totalProperty: 'totalCount'
		});
	// -----------------------------------------------------------------------------------------------------------------


	sw.Promed.swCardStorageWindow.superclass.constructor.call(this, {
		title: langs('Картохранилище'),
		id: 'swCardStorageWindow',
		maximized: true,
		layout: 'border',
		items: [
			this.panelFilter,
			this.tableAmbulatCardArea,
			this.TableMovementsAmbulatoryCard 
		],
		listeners: {
	        'success': function(source, params) {
	        	//debugger;
	        },
			'activate': function(){
				// debugger;

			},
			'deactivate': function() {
				//sw.Applets.BarcodeScaner.stopBarcodeScaner();
				//this.ScanningBarcodeService.stop();
			},
			hide: function() {
				if(this.ScanningBarcodeService.running_in_shape().form == this.id){
					this.ScanningBarcodeService.stop();
				}
			}
        },
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

Ext.extend(sw.Promed.swCardStorageWindow, sw.Promed.BaseForm, {	
	_ambulatoryCards_view: function(pos){
		var pos = pos || false;
		this.tableAmbulatCardArea.addActions({
			name: 'ambulatoryCards_view',
			text: langs('Просмотреть'),
			iconCls: 'view16',
			handler: function(){
				this.issueCard('view');
			}.createDelegate(this)
		}, pos);
	},
	_ambulatoryCards_refresh: function(pos){
		var pos = pos || false;
		this.tableAmbulatCardArea.addActions({
			name: 'ambulatoryCards_refresh',
			text: langs('обновить'),
			iconCls: 'refresh16',
		}, pos);
	},
	_addButton_Print: function(pos){
		var pos = pos || false;
		this.tableAmbulatCardArea.addActions({
			name: 'action_print16',
			text: langs('Печать'),
			iconCls: 'print16',
			menu: [{
				disabled: false,
				handler: function() {
					this.schedulePrint('row');
				}.createDelegate(this),
				iconCls: 'print16',
				name: 'print_selected_line',
				text: 'Печать',
				tooltip: 'печать выбранной строки таблицы'
			},{
				disabled: false,
				name: 'print_all_table',
				tooltip: 'печать всей таблицы',
				text: 'Печать всего списка',
				iconCls: 'print16',
				handler: function() {
					this.schedulePrint();
				}.createDelegate(this)
			}]
		}, pos);
	},
	_ambulatoryCards_issueCard: function(pos){
		var pos = pos || false;
		this.tableAmbulatCardArea.addActions({
			name: 'ambulatoryCards_issueCard',
			text: langs('Выдать карту'),
			iconCls: 'pers-card16',
			handler: function(){
				this.issueCard('edit');
			}.createDelegate(this)
		}, pos);
	},

	_addButtons: function(){
		this._addButton_Print(4);
		this._ambulatoryCards_view(2);
		this._ambulatoryCards_issueCard(1);
	},
	/*getDataFromBarcode: function(barcodeData, person_data){
		debugger;
	},
	getDataFromUec: function(){
		debugger;
	},
	getDataFromBdz: function(){
		debugger;
	},*/
	show: function(){
		this._addButtons();

		sw.Promed.swCardStorageWindow.superclass.show.apply(this);

		var win = this;
		win.Lpu_id = getGlobalOptions().lpu_id;
		if(!Ext.isEmpty(arguments[0].LpuBuilding_id)){
			this.AttachmentLpuBuilding_id = arguments[0].LpuBuilding_id;
		}else{
			this.getLpuBuildingByMedServiceId();
		}
		
		var base_form = this.formPanelFilter.getForm();
		var StorageCard = base_form.findField('LpuBuilding_id');
		var comboMedStaffFact = base_form.findField('MedStaffFact_id');

		comboMedStaffFact.clearValue();
		StorageCard.clearValue();
		comboMedStaffFact.getStore().removeAll();
		var msfFilter ={
			Lpu_id: getGlobalOptions().lpu_id,
			//withoutLpuSection: true
		};
		//msfFilter.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
		setMedStaffFactGlobalStoreFilter(msfFilter);
		comboMedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));


		StorageCard.getStore().baseParams = {Lpu_id: win.Lpu_id};
		StorageCard.getStore().load();
		this.disableForm(false);

		this.doClearPanelFilter();
	},

	initComponent: function(){
		var curWnd = this;

		this.storeTableArea = this.tableAmbulatCardArea.getGrid().getStore();

		sw.Promed.swCardStorageWindow.superclass.initComponent.apply(this);

		//this.ScanningBarcodeService = new sw.Promed.ScanningBarcodeService({
		this.ScanningBarcodeService = sw.Promed.ScanningBarcodeService.init({
			interval: 1000,
			form: this.id,
			callback: function(ambulatCardObject) {
				if(!ambulatCardObject) this.disableForm(false, true);
				Ext.Ajax.request({
					params: {code: ambulatCardObject},
					failure: function (result_form, action) {
						log('Error decodeBarCode');
						this.disableForm(false, true);
					}.createDelegate(this),
					callback: function(options, success, response) {
						if (success && response.responseText != ''){
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(response_obj.success && response_obj.obj){
								this.getAmbulatFromScanner(response_obj.obj);
							}else{
								log('Ошибка при считывании штрих кода');
								if(this.divPopUpMessage.parentNode){
									this.divPopUpMessage.innerText = "Ошибка при считывании штрих-кода !!!"
								}
							}
						}
						else{
							log('Ошибка при считывании штрих кода');
							this.disableForm(false, true);
						}
					}.createDelegate(this),
					url: '?c=Barcode&m=decodeBarCode'
				});
			}.createDelegate(this)
		});

		this.divPopUpMessage.style.cssText = 'display: block; background-color: #feff00b0;z-index: 1000;position: fixed; margin: 22px 0 0 -10px;border: #15428b 1px solid;border-radius: 1em;opacity: 0.8; word-wrap: break-word;font-size: 1.4em;font-weight: bold;padding: 15px;';
		this.divPopUpMessage.innerText = "Отсканируйте штрих-код амбулаторной карты.";
	},

	// Помощь
	doOpenHelp: function(){
		ShowHelp(this.title);
	},

	// Закрыть
	doCloseWindow: function(){
		this.hide();
	},

	// Загрузка данных по штрих-коду
	funGetAmbulatCardData: function(){
		this.FindByBarcode = (this.FindByBarcode) ? false : true;
		if(this.FindByBarcode){
			if(this.ScanningBarcodeService.running_in_shape().scanning){
				sw.swMsg.alert(langs('Ошибка'), langs('Считывание штрих-кода уже запущено в другой форме !!!'));
				this.FindByBarcode = false;
				return false;
			}
			this.ScanningBarcodeService.start({form: this.id});
		}else{
			//sw.Applets.BarcodeScaner.stopBarcodeScaner();
			this.ScanningBarcodeService.stop();
		}
		this.disableForm(this.FindByBarcode);
	},
	getAmbulatFromScanner: function(data)
	{
		//- данные со сканера расшифрованные сервисом
		if(!data.CARD_ID || !data.MO_ID || !data.PERSON_ID) {
			this.disableForm(false, true);
			return false;
		}
		this.doClearPanelFilter();
		var params = {
			PersonAmbulatCard_id: data.CARD_ID,
			Lpu_id: data.MO_ID,
			Person_id: data.PERSON_ID,
			limit: this.pageSize,
			start: 0,
			CardAttachment:null,
			CardIsOpenClosed: null,
			field_numberCard: null,
			AmbulatCardLocatType_id:null,
			LpuBuilding_id: null,
			MedStaffFact_id:null,
			Person_SurName:null,
			Person_FirName:null,
			Person_SecName:null,
			Person_Birthday:null,
			AttachmentLpuBuilding_id: this.AttachmentLpuBuilding_id
		}
		var storetableAmbulatCardArea = this.tableAmbulatCardArea.getGrid().getStore();
		storetableAmbulatCardArea.load({
			params: params,
			callback: function(a,b,c){
				if(a.length > 0 && a[0].data && a[0].data.PersonAmbulatCard_id){
					var filter_form = this.formPanelFilter.getForm();
					if(a[0].data.Person_SurName) filter_form.findField('Person_SurName').setValue(a[0].data.Person_SurName);
					if(a[0].data.Person_FirName) filter_form.findField('Person_FirName').setValue(a[0].data.Person_FirName);
					if(a[0].data.Person_SecName) filter_form.findField('Person_SecName').setValue(a[0].data.Person_SecName);
				}else{
					showSysMsg(langs('Карта не найдена'),'', 'warning', {delay: 2000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
				}
			}.createDelegate(this)
		});

		this.disableForm(false);

		//sw.Applets.BarcodeScaner.stopBarcodeScaner();
		this.ScanningBarcodeService.stop();
	},
	disableForm: function(look, error){
		var error = error || false;
		var searchButtonByBarcode = Ext.getCmp('searchButtonByBarcode');
		var elButton = searchButtonByBarcode.getEl();
		var el = elButton.dom.getElementsByClassName('x-btn-text')[0];
		var filter_form = this.formPanelFilter.getForm();
		if(look){
			this.TableMovementsAmbulatoryCard.disable();
			this.tableAmbulatCardArea.disable();
			filter_form.findField('CardAttachment').disable();
			filter_form.findField('CardIsOpenClosed').disable();
			filter_form.findField('field_numberCard').disable();
			filter_form.findField('AmbulatCardLocatType_id').disable();
			filter_form.findField('LpuBuilding_id').disable();
			filter_form.findField('MedStaffFact_id').disable();
			filter_form.findField('Person_SurName').disable();
			filter_form.findField('Person_FirName').disable();
			filter_form.findField('Person_SecName').disable();
			filter_form.findField('Person_Birthday').disable();
			Ext.getCmp('searchButtonSearch').disable();
			Ext.getCmp('searchButtonClear').disable();

			searchButtonByBarcode.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Завершить&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			searchButtonByBarcode.setIconClass('stop_red16');
			el.insertBefore(this.divPopUpMessage, el.firstChild);
			this.doClearPanelFilter();
		}else{
			this.TableMovementsAmbulatoryCard.enable();
			this.tableAmbulatCardArea.enable();
			filter_form.findField('CardAttachment').enable();
			filter_form.findField('CardIsOpenClosed').enable();
			filter_form.findField('field_numberCard').enable();
			filter_form.findField('AmbulatCardLocatType_id').enable();
			filter_form.findField('LpuBuilding_id').enable();
			filter_form.findField('MedStaffFact_id').enable();
			filter_form.findField('Person_SurName').enable();
			filter_form.findField('Person_FirName').enable();
			filter_form.findField('Person_SecName').enable();
			filter_form.findField('Person_Birthday').enable();
			Ext.getCmp('searchButtonSearch').enable();
			Ext.getCmp('searchButtonClear').enable();

			searchButtonByBarcode.setText('Найти по штрих-коду');
			searchButtonByBarcode.setIconClass('idcard16');
			this.divPopUpMessage.innerText = "Отсканируйте штрих-код амбулаторной карты.";
			this.divPopUpMessage.remove();
		}

		if(error){
			sw.swMsg.alert(langs('Ошибка'), langs('При считывании штрих-кода произошла ошибка !!!'));
		}
	},

	// ищем амбулаторные карты
	doRefreshPanelTableArea: function(){
		var filter_form = this.formPanelFilter.getForm();
		var params = {};
		params.Lpu_id = this.Lpu_id;
		params.limit = this.pageSize;
		params.start = 0;
		params.CardAttachment = filter_form.findField('CardAttachment').getValue();
		params.CardIsOpenClosed = filter_form.findField('CardIsOpenClosed').getValue();
		params.field_numberCard = filter_form.findField('field_numberCard').getValue();
		params.AmbulatCardLocatType_id = filter_form.findField('AmbulatCardLocatType_id').getValue();
		params.LpuBuilding_id = filter_form.findField('LpuBuilding_id').getValue();
		params.MedStaffFact_id = filter_form.findField('MedStaffFact_id').getValue();
		params.Person_SurName = filter_form.findField('Person_SurName').getValue();
		params.Person_FirName = filter_form.findField('Person_FirName').getValue();
		params.Person_SecName = filter_form.findField('Person_SecName').getValue();
		params.Person_Birthday = Ext.util.Format.date(filter_form.findField('Person_Birthday').getValue(), 'd.m.Y');
		params.AttachmentLpuBuilding_id = this.AttachmentLpuBuilding_id;
		params.Person_id = null;
		params.PersonAmbulatCard_id = null;

		var storetableAmbulatCardArea = this.tableAmbulatCardArea.getGrid().getStore();
		storetableAmbulatCardArea.load({
			params: params
		});
		/*
		this.storeTableArea.removeAll();
		this.storeTableArea.clearFilter();
		this.doLoadPanelTableArea();

		return this;
		*/
	},

	// Сброс фильров
	doClearPanelFilter: function(){
		var filter_form = this.formPanelFilter.getForm();

		filter_form.findField('CardAttachment').setValue('allStorage');
		filter_form.findField('CardIsOpenClosed').setValue('allCard');
		filter_form.findField('field_numberCard').setValue();
		filter_form.findField('AmbulatCardLocatType_id').setValue();
		filter_form.findField('LpuBuilding_id').setValue();
		filter_form.findField('MedStaffFact_id').setValue();
		filter_form.findField('Person_SurName').setValue();
		filter_form.findField('Person_FirName').setValue();
		filter_form.findField('Person_SecName').setValue();
		filter_form.findField('Person_Birthday').setValue();

		filter_form.findField('LpuBuilding_id').disable();
		filter_form.findField('MedStaffFact_id').disable();
	},

	schedulePrint: function(action){
		var grid = this.tableAmbulatCardArea.getGrid();
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if (!record) {
            sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
            return false;
        }

        if (action && action == 'row') {
            Ext.ux.GridPrinter.print(grid, {rowId: record.id});
        } else {
            Ext.ux.GridPrinter.print(grid);
        }
	},
	issueCard: function(action){
		//выдать карту
		var action = action || 'view';
		var grid = this.tableAmbulatCardArea.getGrid();
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		params = {
			action: action,
			moveAmbulatCard: true,
			PersonAmbulatCard_id: record.get('PersonAmbulatCard_id')
		}
		params.callback= function(){
		    grid.getStore().reload();
		}
		getWnd('swPersonAmbulatCardEditWindow').show(params);
	},
	openFormMovementsAmbulatoryCard: function(action){
		var action = action || 'view';
		var win = this;
		var grid = (action == 'add') ? this.tableAmbulatCardArea.getGrid() : this.TableMovementsAmbulatoryCard.getGrid();
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCard_id')){
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		var PersonAmbulatCard_id = record.get('PersonAmbulatCard_id');
		var PersonAmbulatCardLocat_id = record.get('PersonAmbulatCardLocat_id');
		var  params = {};
		params.type='save';
	    if(action=='add'){
	    	if(!PersonAmbulatCard_id) return false;
		    params.formParams = {
				PersonAmbulatCard_id: PersonAmbulatCard_id
		    };
	    }else{
			if(!PersonAmbulatCardLocat_id) return false;
			params.formParams = {
				PersonAmbulatCardLocat_id: PersonAmbulatCardLocat_id
			}
	    }
	    params.callback= function (data) {
			var grid = this.TableMovementsAmbulatoryCard.getGrid();
			grid.getStore().reload();
			this.tableAmbulatCardArea.getGrid().getStore().reload();
		}.createDelegate(this)
	    params.action = action;
	    params.Lpu_id=getGlobalOptions().lpu_id;

	    getWnd('swPersonAmbulatCardLocatEditWindow').show(params);
	},
	deleteMovementsAmbulatoryCard: function(){
		var grid = this.TableMovementsAmbulatoryCard.getGrid();
		if(!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonAmbulatCardLocat_id')){
			return false;
		}
		var params = {};
		var record = grid.getSelectionModel().getSelected();
		params.PersonAmbulatCardLocat_id = record.get('PersonAmbulatCardLocat_id');
		sw.swMsg.show({
			title: 'Внимание!',
			msg: "Желаете удалить движение амбулаторной катры?",
			buttons: {yes: 'Да', no: 'Отмена'},
			icon: Ext.Msg.WARNING,
			scope: {win: this, grid: grid},
			fn: function(butn){
				if (butn == 'no'){
					return false;
				}else{
					Ext.Ajax.request({
						params: params,
						failure: function (result_form, action) {
							log('Ошибка при удалении движения АК');
						},
						callback: function(options, success, response) {
							if (success && response.responseText != ''){
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if(this.grid){
									this.grid.getStore().reload();
								}
							}
							else{
								log('Ошибка при удалении движения АК');
							}
						}.createDelegate(this),
						url: '?c=PersonAmbulatCard&m=deletePersonAmbulatCardLocat'
					});
				}
			}
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
					//this.AttachmentLpuBuilding_id = 1823; //test
					//this.loadComboLpuBuilding();
				}
				else{
					log('error getLpuBuildingByMedServiceId');
				}
			}.createDelegate(this),
			url: '?c=PersonAmbulatCard&m=getLpuBuildingByMedServiceId'
		});
	}
});
