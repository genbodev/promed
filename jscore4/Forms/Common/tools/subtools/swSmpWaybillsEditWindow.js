/*
	Оперативная обстановка по диспетчерам СМП
*/


Ext.define('sw.tools.subtools.swSmpWaybillsEditWindow', {
	alias: 'widget.swSmpWaybillsEditWindow',
	extend: 'Ext.window.Window',
	title: 'Путевой лист',
	width: 900,
	viewType: 'view',
	modal: true,
	autoScroll: true,
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	/*
	Создание шаблона для получения отчета о ГСМ с Wialon
	*/
	createWialonGasReportTemplate: function() {
		
		var win = this;
		
		win.WialonReportFieldset.setPreloader( 'Подождите, идёт создание шаблона. Это может занять некоторое время' );
		
		Ext.Ajax.request({
			url: '/?c=Wialon&m=createWayBillGasReportTemplate',
			timeout: 180000,
			callback: function(opt, success, response) {
				if (success){					
					var res = Ext.JSON.decode(response.responseText);
					
					if (!Ext.isObject(res)) {
						win.WialonReportFieldset.setError( null );
						return;
					}
					
					if (res.success && res.data && !res.data.error) {						
						win.getWialonGasData();
					}
					else {
						win.WialonReportFieldset.setError( 'При создании шаблона произошла ошибка. Обратитесь к администратору' , true );
					}
				} else {
					win.WialonReportFieldset.setError( 'При создании шаблона произошла ошибка. Обратитесь к администратору' , true );
				}
			}
		});
	},
	// Проверка наличия в WialonPro шаблона для отчета по ГСМ
	// Если шаблон не обнаружен, предлагаем создать
	// Если обнаружен и корректен, получаем данные по ГСМ
	getWialonGasData: function() {
		var win = this;
		if(getGlobalOptions().region.nick == 'krym'){
			// на крыму сервис недоступен
			win.WialonReportFieldset.setError( 'на данный момент сервис недоступен' );
			return false;
		}
		//
		// Получаем параметры
		//
		var	wb_date = win.BaseForm.getForm().findField('Waybill_Date').getRawValue(),
			wialon_object_id = win.BaseForm.getForm().findField('EmergencyTeam_id').getWialonID(),
			wb_begtime = win.timeFromGarage.getValue(),
			wb_endtime = win.timeBacktoGarage.getValue();
		
		//
		// Проверяем параметры и приводим их к нужному виду
		//
		
		if (!wb_date || !wialon_object_id || !wb_begtime || !wb_endtime) {
			var msg = (!wialon_object_id) ? 
				'У указанной бригады нет привязки к идентификатору Wialon':
				'Обязательно заполнение полей: бригада СМП, Дата ПЛ, Время выезда из гаража, Время возвращения в гараж';
				
			//win.WialonReportFieldset.setError( msg , true );
			win.WialonReportFieldset.setError( msg );
			return false;
		}
			
		var wb_begDT = Ext.Date.parse( wb_date+' '+wb_begtime, 'd.m.Y H:i' ),
			wb_endDT = Ext.Date.parse( wb_date+' '+wb_endtime, 'd.m.Y H:i' );
		
		if (wb_begDT <= wb_endDT) {
			wb_endDT = Ext.Date.add(wb_endDT, Ext.Date.DAY, 1);
		}
		
		win.WialonReportFieldset.setPreloader();
		
		//
		// Посылаем запрос на получение данных по отчёту Wialon
		//
				
		Ext.Ajax.request({
			url: '/?c=Wialon&m=getWayBillGasReport',
			timeout: 180000,
			params: {
				reportObjectId: wialon_object_id,
				from: wb_begDT,
				to: wb_endDT
			},
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText, true);
					
					if (!Ext.isObject(res)) {
						win.WialonReportFieldset.setError( null );
						return;
					}
					
					if (res.success && res.data) {
						
						win.WialonReportFieldset.setData(res.data);

					} else {
						
						if (res.Error_Code) {
							
							/*
								Кодов ошибок этот метод может возвращать много. Возможно, в будущем потребуется не только
								создавать шаблон в случае, если его не существует, но и корректировать неверно сформированный
								или отредактированный шаблон.
							*/
							
							switch (res.Error_Code) {
								
								// Если шаблон не найден, попытаемся его создать
								case 'tplnotfound': 
									win.WialonReportFieldset.setError( res.Error_Msg || 'При создании шаблона произошли ошибки. Обратитесь к администратору' , true )
									break;
									
								// В остальных случаях (пока) просто будем выводить ошибку и кнопку для перезагрузки
								default:
									win.WialonReportFieldset.setError( res.Error_Msg );
									break;
							}
							return;
						} 
						
						win.WialonReportFieldset.setError( res.Error_Msg );											
						
					}
				} else {
					win.WialonReportFieldset.setError( null );
				}
			}
		});
	},
	initComponent: function() {
		var me = this,
			conf = me.initialConfig;

		var licenseCard = Ext.create('sw.commonSprCombo',{
			cls: 'stateCombo',
			fieldLabel: 'Лицензионная карточка',
			valueField: 'Waybill_LicenseCard_Code',
			name:	'Waybill_LicenseCard',
			labelAlign: 'right',
			labelWidth: 170,
			triggerClear: true,
			codeField: 'Waybill_LicenseCard_Code',
			displayField: 'Waybill_LicenseCard_Name',
			store: new Ext.data.Store({
				key: 'Waybill_LicenseCard',
				autoLoad: true,
				fields:	[
					{name:'Waybill_LicenseCard_Code', type:'int'},
					{name:'Waybill_LicenseCard_Name', type:'string'}
				],
				data : [
					{Waybill_LicenseCard_Code: 1, Waybill_LicenseCard_Name: 'Стандартная'},
					{Waybill_LicenseCard_Code: 2, Waybill_LicenseCard_Name: 'Ограниченная'}
				]
			})

		});

		var gasCombo = Ext.create('swWaybillGasCombo', {
			labelWidth: 260, 
			width: 370,
			name: 'WaybillGas_id'
		});

		this.timeFromGarage = Ext.create('sw.timeGetCurrentTimeCombo', {
			labelAlign: 'right',
			fieldLabel: 'Время выезда из гаража',
			name: 'Waybill_TimeStart',
			allowBlank: false,
			labelWidth: 170,
			plugins: [new Ux.InputTextMask('99:99')]
		});

		this.timeBacktoGarage = Ext.create('sw.timeGetCurrentTimeCombo', {
			labelAlign: 'right',
			fieldLabel: 'Время возвращения в гараж',
			name: 'Waybill_TimeFinish',
			labelWidth: 170,
			allowBlank: true,
			plugins: [new Ux.InputTextMask('99:99')]
		});

		var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 1
		});

		var wayRouteGrid = Ext.create('Ext.grid.Panel', {
			viewConfig: {
				loadingText: 'Загрузка',
				minHeight: 30
			},
			plugins: (conf.action=='view')? [] : cellEditing,

			flex: 1,
			autoScroll: true,
			stripeRows: true,
			refId: 'wayRouteGrid',
			tbar: [
				{
					xtype: 'button',
					itemId: 'addWayRouteButton',
					disabled: (conf.action=='view')? true : false,
					text: 'Добавить',
					iconCls: 'add16',
					handler: function(){
						wayRouteGrid.store.add({WaybillRoute_id: null});
						var rowsCnt = wayRouteGrid.getStore().getCount() - 1;
						var rowSel = 1;
						wayRouteGrid.getSelectionModel().select( rowsCnt, rowSel );
						cellEditing.startEditByPosition({row: rowsCnt, column: rowSel});
					}
				},
				{
					xtype: 'button',
					itemId: 'deleteWayRouteButton',
					disabled: true,
					text: 'Удалить',
					iconCls: 'delete16',
					handler: function(){
						var rec = wayRouteGrid.getSelectionModel().getSelection( );
						wayRouteGrid.store.remove(rec);
					}
				}
			],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				storeId: 'WayRouteStore',
				fields: [
					{name: 'WaybillRoute_id'},
					{name: 'WaybillRoute_CustCode', type: 'string', cls: 'persist'},
					{name: 'WaybillRoute_PointStart', type: 'string'},					
					{name: 'WaybillRoute_PointFinish', type: 'string'},
					{name: 'WaybillRoute_TimeStart', type: 'string'},
					{name: 'WaybillRoute_TimeFinish', type: 'string'},
					{name: 'WaybillRoute_Trip', type: 'string'}
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=Waybill4E&m=loadWaybillRoute',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					},
					extraParams :{
						Waybill_id: conf.wayBillsId
						//dateFinish:	Ext.Date.format(cald.dateTo, 'd.m.Y'),
						//dateStart:	Ext.Date.format(cald.dateFrom, 'd.m.Y')
					}
				},
				listeners: {
					load: function(cmp){
						if (cmp.getCount()>0 && conf.action!='view'){
							wayRouteGrid.down('toolbar button[itemId=deleteWayRouteButton]').enable();
						}
						else{
							wayRouteGrid.down('toolbar button[itemId=deleteWayRouteButton]').disable();
						}
					},
					datachanged: function( cmp, eOpts ) {
						if (cmp.getCount()>0){
							wayRouteGrid.down('toolbar button[itemId=deleteWayRouteButton]').enable();
						}
						else{
							wayRouteGrid.down('toolbar button[itemId=deleteWayRouteButton]').disable();
						}
					}
				}
//				sorters: {
//					property: 'EmergencyTeamDuty_DTStart',
//					direction: 'ASC'
//				}
			}),
			columns: [
				{ dataIndex: 'WaybillRoute_id', text: 'ID', hidden: true, hideable: false },
				{ dataIndex: 'WaybillRoute_CustCode', text: 'Код заказчика', 
					editor: {
						maskRe:/[0-9]/i
					}
				},
				{ dataIndex: 'WaybillRoute_PointStart', text: 'Место отправления', flex: 1,	editor:{}},
				{ dataIndex: 'WaybillRoute_PointFinish', text: 'Место назначения', flex: 1,	editor:{}},
				{ dataIndex: 'WaybillRoute_TimeStart', text: 'Время выезда', width: 100, 
					editor: Ext.create('sw.timeIntervalQuadHourCombo', {
						plugins: [new Ux.InputTextMask('99:99')]
				})},
				{ dataIndex: 'WaybillRoute_TimeFinish', text: 'Время возвращения', width: 130, 
					editor: Ext.create('sw.timeIntervalQuadHourCombo', {
						plugins: [new Ux.InputTextMask('99:99')]
				})},
				{ dataIndex: 'WaybillRoute_Trip', text: 'Пройдено, км.', width: 100, editor:{maskRe:/[0-9.,]/i} }
			]
		});
		
		this.WialonReportFieldset = Ext.create('Ext.form.FieldSet',{
			title: 'Данные Wialon',
			collapsed: true,
			collapsible: true,
			hidden: (getGlobalOptions().region.nick == 'krym') ? true : false, // на Крыму сервис недоступен, спрячем его от глаз
			loaded: false, //Была ли произведена загрузка с Wialon
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			margin: '0 10 0 0',
			padding: '5 10 10 10',
			items: [  ],
			listeners: {
				expand: function( fieldset, eOpts ) {
					
					if (fieldset.loaded) {
						return false;
					}
					
					me.getWialonGasData();
				}
			},
			data: [],
			setError: function ( Error_Msg , create_template_button ) {
				this.removeAll();
				
				if (!Error_Msg) {
					Error_Msg = 'При получении информации произошла ошибка. Попробуйте снова или обратитесь к администратору.';
				}
				
				var btnCfg = (create_template_button) ? 
					{
						xtype: 'button',
						text: 'Создать шаблон',
						//hidden: true,
						handler: function () {
							me.createWialonGasReportTemplate();
						}
					} : {
						xtype: 'button',
						text: 'Попробовать ещё раз',
						hidden: typeof create_template_button == 'undefined',
						handler: function () {
							me.getWialonGasData();
						}
					};
				
				
				this.add({
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'center'
					},
					items: [
						{
							padding: '4 0 0 0',
							margin: '0 5 0 0',
							xtype:'label',
							text: 'Ошибка: '+Error_Msg
						},						
						btnCfg						
					]
				})
			},
			//
			// ни loadMask ни setLoading не применяется по не определённой причине
			//
			setPreloader: function ( msg ) {
				this.removeAll();
				this.add({
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'center'
					},
					items: [
						{
							xtype:'label',
							text: (msg)||'Подождите, идёт получение информации с Wialon. Это может занять некоторое время...'
						},{
							xtype: 'image',
							src: '../extjs4/resources/ext-theme-classic/images/grid/loading.gif'
						}
					]
				})
			},
			/*
			Загружаем данные из Wialon
			@params data - [ {name: <системное имя поля>, display_name: <отображаемое имя поля>, value: <значение> }, ... ]
			*/
			setData: function (data) {
				
				var fieldset = this;
				
				if (!Ext.isObject(data)) {
					return false;
				}
				
				// Сохраним данные, для последующего использования при подстановки в в поля
				this.data = data;
				
				// Списох обязательных элементов data
				var required_items_list = [
					'fuel_consumption_abs',
					'fuel_consumption_fls',
					'fuel_consumption_imp',
					'fuel_consumption_ins',
					'fuel_consumption_math',
					'fuel_consumption_rates'
				];
				
				// Проверка полученных данных
				
				for (var i = 0; i < required_items_list.length; i++) {
					
					if ( !Ext.isObject( data[ required_items_list[i] ] ) || !data[ required_items_list[i] ].display_name || !data[ required_items_list[i] ].value ) {
						this.setError('Запрос вернул неверный набор данных. Обратитесь к администратору');
						return false;
					}
				}
				
				this.loaded = true;
				
				this.removeAll();
				this.add([
					
					{
						xtype: 'container', 
						flex: 1, 
						layout: {
							align: 'stretch', 
							type: 'vbox'
						}, 
						items: [
							// { xtype: 'label',  /*Потрачено по ДАРТ*/ text: data.fuel_consumption_abs.display_name+': '+data.fuel_consumption_abs.value },
							// { xtype: 'label', /*Потрачено по ДУТ*/ text: data.fuel_consumption_fls.display_name+': '+data.fuel_consumption_fls.value },
							// { xtype: 'label',  /*Потрачено по ДИРТ*/  text: data.fuel_consumption_imp.display_name+': '+data.fuel_consumption_imp.value }
							
							//Данные датчиков пока учитывать не будем
							
							{
								padding: '4 0 0 0',
								margin: '0 5 0 0',
								xtype: 'label',   //Потрачено по нормам
								text: data.fuel_consumption_rates.display_name+': '+data.fuel_consumption_rates.value
							}
						]
					}, {
						xtype: 'container',
						flex: 1,
						layout: {
							align: 'stretch',
							type: 'vbox'
						},
						items: [
							{
								padding: '4 0 0 0',
								margin: '0 5 0 0',
								xtype: 'label', //Потрачено по расчету
								text: data.fuel_consumption_math.display_name+': '+data.fuel_consumption_math.value
							}
							
							//Данные датчиков пока учитывать не будем
							
							// { xtype: 'label', /*Потрачено по ДМРТ*/ text: data.fuel_consumption_ins.display_name+': '+data.fuel_consumption_ins.value },
							// { xtype: 'label', /*Потрачено по расчету*/ text: data.fuel_consumption_math.display_name+': '+data.fuel_consumption_math.value },
							// { xtype: 'label',   /*Потрачено по нормам*/ text: data.fuel_consumption_rates.display_name+': '+data.fuel_consumption_rates.value }, 
						] 
					}, {
						xtype: 'button',
						text: 'Подставить в форму',
						handler: function() {
							me.BaseForm.getForm().findField('Waybill_FuelConsumption').setValue(parseFloat(fieldset.data.fuel_consumption_rates.value));
							me.BaseForm.getForm().findField('Waybill_FuelFact').setValue(parseFloat(fieldset.data.fuel_consumption_math.value));
						}
					}
				]);
					
				
				
			}
		});
		
		var emergencyTeamCombo = Ext.create('sw.EmergencyTeamWithWialonCombo', {
			name: 'EmergencyTeam_id',
			allowBlank: false			
		});
		emergencyTeamCombo.store.getProxy().extraParams = {
			'dateStart' : Ext.Date.format(conf.dateTo, 'd.m.Y'),
			'workComing': true			
		};
		
		emergencyTeamCombo.store.on('load', function(a,b,c){
			if(b.length<1){
				Ext.Function.defer(function(){Ext.Msg.alert('Внимание', 'На выбранный день не обнаружено ни одной смены бригады СМП');}, 500);
			}
		})
		
		this.BaseForm = Ext.create('sw.BaseForm',{
			border: false,
			frame: true,
			//id: 'swSmpWaybillsEditForm',
			layout: 'auto',
			bodyBorder: false,
			items: [
				{
					xtype: 'fieldset',
					title: 'Бригада',
					margin: '0 10 0 0',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'container',
							flex: 1,
							margin: '0 10',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: [
								emergencyTeamCombo
							]					

						}
					]
				},
				{
					xtype: 'container',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [							
						{
							xtype: 'fieldset',
							title: 'Состав бригады',
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							margin: '0 10 0 0',
							flex:1, 
							//width: 320,
							items: [
								{
									xtype: 'container',
									layout: 'vbox',
									defaults: {
										labelWidth: 170,
										labelAlign: 'right'
									},
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Серия ПЛ',
											name: 'Waybill_Series'
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Номер ПЛ',
											name: 'Waybill_Num',
											allowBlank: false,
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'datefield',
											fieldLabel: 'Дата ПЛ',
											name: 'Waybill_Date',
											allowBlank: false,	
											format: 'd.m.Y',
											plugins: [new Ux.InputTextMask('99.99.9999')],
											listeners: {
												render: function(){
													var dateWB = new Date();
													if(conf && conf.dateTo && conf.action == 'add'){
														dateWB = conf.dateTo; this.setValue(dateWB);
													}												
												}
											},
											validator: function(val){
												var dt = Ext.Date.parse(val, 'd.m.Y');
													if (!dt){
														return 'Неправильный формат данных';
													}
													return true;
											}
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Гаражный номер',
											name: 'Waybill_GarageNum'
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Табельный номер',
											name: 'Waybill_EmployeeNum',
											allowBlank: false,
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Номер удостоверения',
											name: 'Waybill_IdentityNum'
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Класс',
											name: 'Waybill_Class'
										},
										licenseCard,
										{
											xtype: 'textfield',
											fieldLabel: 'Регистрационный №',
											name: 'Waybill_RegNum'
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Серия',
											name: 'Waybill_RegSeries'
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Номер',
											name: 'Waybill_RegNum2'
										}
									]
								}
							]
						},
						{
							xtype: 'fieldset',
							title: 'Учет ГСМ',
							flex: 1,
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							margin: '0 10 0 0',
							items: [
								{
									xtype: 'container',
									layout: {
										type: 'vbox'
									},
									defaults: {
										width: 370,
										labelAlign: 'right',
										labelWidth: 260
									},
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Показания спидометра при выезде, км',
											name: 'Waybill_OdometrBefore',
											allowBlank: false,
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Показания спидометра при возвращении, км',
											name: 'Waybill_OdometrAfter',													
											maskRe:/[0-9.,]/i
										},
										gasCombo,
										{
											xtype: 'textfield',
											fieldLabel: '№ заправочного листа',
											name: 'Waybill_RefillCardNum',													
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Выдано по заправочному листу, л',
											name: 'Waybill_FuelGet',													
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Остаток при выезде, л',
											name: 'Waybill_FuelBefore',											
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Остаток при возвращении, л',
											name: 'Waybill_FuelAfter',											
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Расход по норме, л',
											name: 'Waybill_FuelConsumption',											
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Расход фактический, л',
											name: 'Waybill_FuelFact',											
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Экономия, л',
											name: 'Waybill_FuelEconomy',											
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Перерасход, л',
											name: 'Waybill_FuelOverrun',											
											maskRe:/[0-9.,]/i
										}
									]
								}
							]
						},
					]
				},
				{
					xtype: 'container',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'fieldset',
							title: 'Задание водителю',
							flex: 1,
							layout: {
								type: 'hbox'
							},
							margin: '0 10 0 0',
							items: [
								{
									xtype: 'container',
									layout: 'vbox',

									defaults: {
										labelWidth: 170,
										labelAlign: 'right'
									},
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Адрес подачи',
											name: 'Waybill_Address',
											allowBlank: false
										},
										me.timeFromGarage,
										me.timeBacktoGarage,
										{
											xtype: 'textareafield',
											fieldLabel: 'Опоздания, ожидания, простои, заезды в гараж и т.п.',
											name: 'Waybill_Justification'
										}
									]
								}
							]
						},
						{
							xtype: 'fieldset',
							title: 'Дополнительные сведения',
							flex: 1,
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							margin: '0 10 0 0',
							items: [
								{
									xtype: 'container',
									layout: {
										type: 'vbox'
									},
									defaults: {
										labelAlign: 'right',
										labelWidth: 260,
										width: 370
									},
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Всего в наряде, часов',
											name: 'Waybill_PersonCnt',
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Пройдено, км',
											name: 'Waybill_Trip',
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'За километраж, руб. коп',
											name: 'Waybill_PaymentOdometr',
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'За часы, руб. коп',
											name: 'Waybill_PaymentTime',
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Итого, руб. коп',
											name: 'Waybill_PaymentTotal',
											maskRe:/[0-9.,]/i
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Должность производившего расчет',
											name: 'Waybill_CalcMakePost',
											width: 400
										},
										{
											xtype: 'textfield',
											fieldLabel: 'ФИО производившего расчет',
											name: 'Waybill_CalcMakeName',
											width: 400
										}                                   
									]
								}
							]
						}
					]
				},
				
				this.WialonReportFieldset,
				
				{
					xtype: 'fieldset',
					title: 'Маршрут',
					margin: '0 10 0 0',
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'center',
						padding: '0 10'
					},
					items: [
						wayRouteGrid
					]
				},

			]
		})
		
		Ext.applyIf(me, {
			items: [
				this.BaseForm
			],
			dockedItems: [
				{
                    xtype: 'container',
                    flex: 1,
					padding: 3,
                    dock: 'bottom',
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    items: [
                        {
                            xtype: 'container',
                            layout: {
                                type: 'vbox',
                                align: 'stretch'
                            },
                            items: [
                                {
									xtype: 'button',
									//id: 'saveBtn',
									disabled: (conf.action=='view')? true : false,
									iconCls: 'save16',
									text: 'Сохранить',
									handler: function(){
										me.saveSmpWaybills(me);
									}
								}
                            ]
                        },
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'right',
                                pack: 'end'
                            },
                            items: [                               
								{
									xtype: 'button',
									//id: 'helpBtn',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										//ShowHelp(this.ownerCt.title);
									}
								},
								{
									xtype: 'button',
									//id: 'cancelBtn',
									iconCls: 'cancel16',
									text: 'Закрыть',
									margin: '0 5',
									handler: function(){
										me.close();
									}
								}
                            ]
                        }
                    ]
                }
			]
		});

		me.callParent(arguments);
	},	
	listeners: {
		show: function(){
			var me = this,
				conf = me.initialConfig,
				wayRouteGrid = me.down('grid[refId=wayRouteGrid]');

			this.loadSmpWaybills(me, conf, wayRouteGrid);
		}
	},

	loadSmpWaybills: function(cmp, conf, wayRouteGrid){
		var form = cmp.down('form').getForm(),
			mytitle = '';

		form.isValid();
		switch(conf.action){
			case 'add' :  { mytitle +=': Добавление'; break; }
			case 'edit' : { mytitle +=': Редактирование'; break; }
			case 'view' : {
				mytitle +=': Просмотр';	

				form.getFields().each(function(item){
					//item.setReadOnly(true);
					item.inputEl.dom.disabled = true;
				})
				//не знаю, почему, но срабатывает только на комбобоксы
				form.applyToFields({
					hideTrigger: true
				});
				break;
			}
		}
		cmp.setTitle(cmp.title + mytitle);

		if (conf.action!='add'){
			Ext.Ajax.request({
				url: '/?c=Waybill4E&m=loadWaybill',
				params: {Waybill_id: conf.wayBillsId},
				callback: function(opt, success, response) {
					if (success){
						var res = Ext.JSON.decode(response.responseText);
						form.setValues(res[0]);
					}
				}
			});

			wayRouteGrid.store.load();
		}
	},

	saveSmpWaybills : function(cmp){
		var form = cmp.down('form').getForm(),
			fields = form.getFields(),
			data = form.getValues(),
			wayRouteGridstore = cmp.down('grid[refId=wayRouteGrid]').getStore(),
			WaybillRoute = [],
			waybillGrid = Ext.ComponentQuery.query('grid[refId=waybillGrid]')[0];

		wayRouteGridstore.each(function(rec){
			WaybillRoute.push(rec.data)
		})

		data.WaybillRoute = Ext.encode(WaybillRoute);
		data.Waybill_id = cmp.initialConfig.wayBillsId;

		if (!form.isValid()){
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
		}
		else{
			Ext.Ajax.request({
				url: '/?c=Waybill4E&m=saveWaybill',
				params: data,
				callback: function(opt, success, response) {
					if (success){
						var res = Ext.JSON.decode(response.responseText);

						if (res.success) {
							cmp.close();
							waybillGrid.store.reload();

							waybillGrid.store.on('load', function(){
								var record = waybillGrid.store.findRecord('Waybill_id', res.Waybill_id);
								waybillGrid.getSelectionModel().select(record);
							})
						} else {
							Ext.Msg.alert('Ошибка', (res.Error_Msg) || 'При сохранении путевого листа произошла ошибка. Обратитесь к администратору!');
						}
					}
				}
			})
		}
	}
})

