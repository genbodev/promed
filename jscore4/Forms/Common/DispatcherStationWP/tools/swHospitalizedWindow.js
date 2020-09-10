/*
	Госпитализация в стационар
*/

Ext.define('common.DispatcherStationWP.tools.swHospitalizedWindow', {
	alias: 'widget.swHospitalizedWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Госпитализация в стационар',
	width: 700,
	height: null,
	border: false,
	traumaReasonCode: ['СЛ197','СЛ198'],
	oksDiagCode: ['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9'],
	onmkDiagCode: ['I60.0','I60.1','I60.2','I60.3','I60.4','I60.5','I60.6','I60.7','I60.8','I60.9','I61.0','I61.1','I61.2','I61.3','I61.4','I61.5','I61.6','I61.8','I61.9','I62.0','I62.1','I62.9','I63.0','I63.1','I63.2','I63.3','I63.4','I63.5','I63.6','I63.8','I63.9','I64','G45.0','G45.1','G45.2','G45.3','G45.4','G45.8','G45.9','G46.0','G46.1','G46.2','G46.3','G46.4','G46.5','G46.6','G46.7','G46.8'],
	operationDiacCode: ['I24.0', 'I24.1', 'I24.8', 'I24.9'],

	show: function() {
		var win = this,
			baseForm = win.down('BaseForm').getForm();

		win.params = {};
		win.callParent();


		if(arguments[0] && arguments[0].params) {
			win.params = arguments[0].params;
			win.teamStore = arguments[0].teamStore;
		}

		if(getRegionNick() == 'ufa')
			win.setVisibleTraumaFieldset( win.params.CmpReason_Name );

		if(win.params.formType == 'traum'){
			win.setTitle('Травмпункт МО');
			win.selectMO.setFieldLabel('Травмпункт МО');
		}else{
			win.setTitle('Госпитализация в стационар');
			win.selectMO.setFieldLabel('МО госпитализации');
		}
		baseForm.findField('viewAllMO').setValue(win.params.formType == 'traum');
		baseForm.findField('viewAllMO').setReadOnly(win.params.formType == 'traum');


		baseForm.isValid();
	},

	initComponent: function() {
		var win = this,
			globals = getGlobalOptions(),
			conf = win.initialConfig;

		win.on('show', function(){});

		win.addEvents({
			//событие на выбор шаблонов
			selectAction: true
		});

		win.Diag_id = Ext.create('sw.Diag',{
				name: 'Diag_id',
				id: 'Hospitalized_Diag_id',
				autoFilter: false,
				translate: false,
				labelWidth: 150,
				labelAlign: 'right',
				triggerFind: true,
				minChars: 2,
				fieldLabel: 'Диагноз',
				onTrigger1Click: function(e) {
					this.clearValue();
					this.store.clearFilter();
					this.focus();
					win.setVisibleFieldsets();
				},
				getDiagType: function() {
					var value = this.getValue();
					if( !value ) return false;

					var rec = this.getStore().getById(value+'');
					if( !rec ) return false;

					var code = rec.get('Diag_Code');

					switch(true) {
						case code.inlist(win.oksDiagCode):
							return 'OKS';
						case code.inlist(win.onmkDiagCode):
							return 'ONMK';
						default:
							return false;
					}
				}
		});

		win.selectBtn = Ext.create('Ext.button.Button', {
			text: 'Сохранить',
			iconCls: 'ok16',
			refId: 'selectButton',
			disabled: true,
			handler: function(){
				var rec = win.selectMO.findRecordByValue(win.selectMO.getValue()),
					baseForm = win.down('BaseForm').getForm(),
					params = baseForm.getValues(),
					operationGridPanel = win.down('[refId=operationGridPanel]'),
					operationRecord = operationGridPanel.getSelectionModel().getSelection()[0];

				if(!baseForm.isValid()) {
					Ext.Msg.alert('Ошибка', 'Заполните все обязательные поля');
					return;
				}

				var loadMask = new Ext.LoadMask(win.getEl(), {msg: "Подождите, идет сохранение..."});
				loadMask.show();


				if(win.Diag_id.getDiagType() == 'OKS') {
					var inputFormat = /(\d{2})-(\d{2})-(\d{4})/,
						outputFormat = /$3-$2-$1/,
						Pain_date = params.Pain_date.replace( inputFormat, outputFormat ), //Y-m-d
						ECG_date = params.ECG_date.replace( inputFormat, outputFormat ),
						TLT_date = params.TLT_date.replace( inputFormat, outputFormat ),
						PainDT = (Pain_date && params.Pain_time) ? (Pain_date + ' ' + params.Pain_time) : '',
						ECGDT = (ECG_date && params.ECG_time) ? (ECG_date + ' ' + params.ECG_time) : '',
						TLTDT = (TLT_date && params.TLT_time) ? (TLT_date + ' ' + params.TLT_time) : '';

					params.PainDT = PainDT;
					params.ECGDT = ECGDT;
					params.TLTDT = TLTDT;
				}

				if(operationRecord && operationRecord.data) {
					var freeOperTime = new Date(operationRecord.data.resource.begdt);
					if(operationRecord.data.transtDateTime) {
						var transDate = Ext.Date.parse(operationRecord.data.transtDateTime, 'd.m.Y H:i');
						if(transDate > freeOperTime) {
							freeOperTime = transDate;
						}
					}
					freeOperTime = freeOperTime.setSeconds(freeOperTime.getSeconds() + 5);
					freeOperTime = new Date(freeOperTime);

					/*required parameter(s) medstafffact.id, timetable.begdt, timetable.time not found*/
					params['person.id'] = win.params.Person_id;
					params['diag.id'] = params.Diag_id;
					params['resource.id'] = operationRecord.data.resource.id;
					params['uslugacomplex.id'] = '200897';
					params['medstafffact.id'] = getGlobalOptions().medstafffact[0];
					params['lpu.id'] = operationRecord.data.lpu.id;
					params['lpusection.id'] = operationRecord.data.lpusection.id;
					params['lpubuilding.id'] = operationRecord.data.lpubuilding.id;
					params['medservice.id'] = operationRecord.data.medservice.id;
					params['timetable.begdt'] = freeOperTime;
					params['cmpcallcard.id'] = win.params.CmpCallCard_id;

					Ext.Ajax.request({
						url:'/?c=SwanApi&m=bookOperationTable',
						params: params,
						callback: function (options, success, response) {
							if(success) {
								var res = Ext.JSON.decode(response.responseText);
								if(res.success) {
									win.showYellowMsg("Операционный стол успешно забронирован", 3000);
								}
							}
						}
					});
				}

				if(rec) {
					params.Code = rec.get('code');
					params.Lpu_id = rec.get('lpu_id');
					params.diagType = win.Diag_id.getDiagType();
					params.formType = win.params.formType;
					loadMask.hide();
					win.fireEvent('selectAction', params);
				}

			}
		});
		win.selectMO = Ext.create('sw.selectMO', {
			labelAlign: 'right',
			labelWidth: 150,
			name: 'LpuSection_id',
			listConfig: {minWidth: 600, width: 600},
			allowBlank: false,
			listeners: {
				change: function(cmp, val){
					win.selectBtn.setDisabled(!(val));
				}
			}
		});

		var traumaChangeFunc = function() {
			win.setReadOnlyPointField('PrehospTraumaScale');
			win.setGeneralState();
		}

		var lamsChangeFunc = function() {
			win.setReadOnlyPointField('ScaleLams');
			win.setGeneralState();
		}

		//отправляем сборку
		win.configComponents = {
			center: [
				{
					border: false,
					style: 'padding: 5px;',
					layout: 'form',
					defaults: {
						labelAlign: 'right',
						labelWidth: 150
					},
					items: [
						win.Diag_id,
						{
							xtype:'checkbox',
							name: 'viewAllMO',
							labelSeparator: ':',
							fieldLabel: 'Показать все МО',
							margin: '0 5 0 0',
							listeners:{
								change: function(cmp, val){
										stopLoadingStore(win.selectMO.store);
										win.selectMO.enable();
										win.selectMO.store.getProxy().setExtraParam('viewAllMO',val);
										win.selectMO.store.load();
								},
								afterrender: function(cmp,e) {
									Ext.Ajax.request({
										url: '/?c=MedService4E&m=getVolumeTypeLpuHospitalizationSMPExist',
										params: {},
										callback: function(opt, success, response){
											if(win.params.formType != 'traum') {
												if (success) {
													var obj = Ext.decode(response.responseText),
														value = !(parseInt(obj) > 0);
													cmp.setValue(value);
													cmp.fireEvent('change', cmp, value)
												}
												else {
													cmp.setValue(true);
													cmp.fireEvent('change', cmp, true)
												}
											}
										}
									});
								}
							}
						},
						win.selectMO,
						{
							xtype: 'commonState',
							fieldLabel: 'Общее состояние',
							hidden: getRegionNick() != 'ufa',
							name: 'cmpcommonstate_id'
						},
						//окс
						{
							refId: 'oksFieldset',
							title: 'ОКС',
							xtype: 'fieldset',
							collapsible: true,
							hidden: true,
							disabled: true,
							items: [
								{
									margin: '0 0 5 0',
									border: false,
									layout: 'hbox',
									items: [
										{ 
											xtype: 'tbfill'
										}, {
											xtype: 'label',
											text: 'Время начала болевых симптомов:',
											style: 'padding-top: 4px; margin-right: 4px; !important'
										}, {
											xtype: 'datefield',
											name: 'Pain_date',
											width: 100,
											format: 'd.m.Y',
											listeners: {
												blur: function(field, value) {
													var painTime = win.down('[name=Pain_time]');
													painTime.allowBlank = !this.getValue();
													painTime.validate();
												}
											}
										}, {
											margin: '0 0 0 5',
											xtype: 'swtimefield',
											name: 'Pain_time',
											width: 70
										}
									]
								},
								{
									margin: '0 0 5 0',
									border: false,
									layout: 'hbox',
									items: [
										{
											xtype: 'tbfill'
										}, {
											fieldLabel: 'Результат ЭКГ',
											labelAlign: 'right',
											xtype: 'combo',
											tpl: new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item"><font color="red"><b>{ReferenceECGResult_code}</b></font>&nbsp;{ReferenceECGResult_Name}</div></tpl>'),
											queryMode: 'local',
											name: 'ResultECG',
											valueField: 'ReferenceECGResult_Name',
											displayField: 'ReferenceECGResult_Name',
											listConfig:{
												minWidth:500,
												maxHeight:400
											},
											width: 400,
											store: new Ext.data.Store({
												autoLoad: false,
												fields: [
													{ name: 'ReferenceECGResult_code' },
													{ name: 'ReferenceECGResult_Name' },
													{ name: 'subgroupOKC' }
												],
												proxy: {
													type: 'ajax',
													url: '/?c=BSK_Register_User&m=getReferenceECGResult',
													extraParams: { KLrgn_id: 2 },
													reader: {
														type: 'json',
														successProperty: 'success',
														root: 'data'
													},
													sorters: {
														property: 'ReferenceECGResult_code',
														direction: 'ASC'
													},
													actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
													limitParam: undefined,
													startParam: undefined,
													paramName: undefined,
													pageParam: undefined
												}
											})
										}, {
											xtype: 'datefield',
											name: 'ECG_date',
											margin: '0 0 0 5',
											width: 100,
											format: 'd.m.Y',
											listeners: {
												blur: function(field, value) {
													var ecgTime = win.down('[name=ECG_time]');
													ecgTime.allowBlank = !this.getValue();
													ecgTime.validate();
												}
											}
										}, {
											xtype: 'swtimefield',
											name: 'ECG_time',
											margin: '0 0 0 5',
											width: 70
										}
									]
								},
								{
									margin: '0 0 5 0',
									border: false,
									layout: 'hbox',
									items: [
										{
											xtype: 'label',
											flex: 8
										},{
											fieldLabel: 'ТЛТ',
											name: 'tltField',
											labelAlign: 'right',
											xtype: 'combo',
											valueField: 'id',
											displayField: 'name',
											value: 0,
											store: new Ext.data.Store({
												fields:[
													{ name: 'id', type: 'int' },
													{ name: 'name', type: 'string' }
												],
												data: [
													{ id: 0, name:'Не выполнено' },
													{ id: 1, name:'Выполнено' }
												]
											}),
											listeners: {
												change: function(combo,newValue) {
													var baseForm = win.down('BaseForm').getForm(),
														tltDate = baseForm.findField('TLT_date'),
														tltTime = baseForm.findField('TLT_time'),
														rejectReason = baseForm.findField('FailTLT'),
														isTlt = newValue;

													rejectReason.setVisible(!isTlt);
													rejectReason.setDisabled(isTlt);
													tltDate.setVisible( isTlt );
													tltTime.setVisible( isTlt );

													rejectReason.setValue(null);
													tltDate.setValue(null);
													tltTime.setValue(null);
												}
											}
										}, {
											fieldLabel: 'Причина',
											name: 'FailTLT',
											labelAlign: 'right',
											xtype: 'combo',
											valueField: 'name',
											displayField: 'name',
											store: new Ext.data.Store({
												fields: [
													{ name:'id', type:'int'},
													{ name:'name', type:'string'}
												],
												data: [
													{ id:1, name:'Противопоказания'},
													{ id:2, name:'Отказ пациента'},
													{ id:3, name:'Вышло время'},
													{ id:4, name:'Пациент направлен на ЧКВ'},
													{ id:5, name:'Отсутствие лекарственного средства'},
													{ id:6, name:'Нет показаний'}
												]
											
											})
										},{
											xtype: 'datefield',
											name: 'TLT_date',
											format: 'd.m.Y',
											hidden: true,
											margin: '0 0 0 5',
											width: 100,
											listeners: {
												blur: function(field, value) {
													var tltTime = win.down('[name=TLT_time]');
													tltTime.allowBlank = !this.getValue();
													tltTime.validate();
												}
											}
										}, {
											xtype: 'swtimefield',
											name: 'TLT_time',
											hidden: true,
											margin: '0 0 0 5',
											width: 70
										}
									]
								},
								{
									xtype: 'checkbox',
									boxLabel: 'Наличие риска, требующего инвазивной стратегии при ОКС без подъёма сегмента ST',
									refId: 'operationCheck',
									hidden: true,
									listeners: {
										change: function (cmp, newValue) {
											win.setVisibleFieldsets();
										}
									}
								}
							]
						},
						//Свободная операционная
						{
							refId: 'operationFieldset',
							title: 'Свободная операционная',
							xtype: 'fieldset',
							collapsible: true,
							hidden: true,
							disabled: true,
							items: [
								{
									xtype: 'gridpanel',
									scrollable: true,
									height: 200,
									refId: 'operationGridPanel',
									store: new Ext.data.Store({
										proxy: {
											type: 'ajax',
											url: '/?c=SwanApi&m=getEmergencyLpuHospitalization',
											reader: {
												type: 'json',
												root: 'responseData'
											},
										},
										fields: [
											{name: 'resource',  type: 'auto'},
											{name: 'lpu',  type: 'auto'},
											{name: 'lpubuilding',  type: 'auto'},
											{name: 'lpusection',  type: 'auto'},
											{name: 'resource',  type: 'auto'},
											{name: 'medservice',  type: 'auto'},
											{name: 'begDate',  type: 'int'},
										],
										listeners: {
											load: function (store, recs) {
												var gridView = win.down('[refId=operationGridPanel]').view;

												recs.forEach(function(rec){
													var address = rec.data.lpubuilding.address,
														emergencyteam = win.params.EmergencyTeam_id,
														mapPanel = Ext.ComponentQuery.query('swsmpmappanel')[0],
														etRecord = win.teamStore ?  win.teamStore.findRecord('EmergencyTeam_id', emergencyteam) : null;

													if(etRecord) {
														var wialon_id = etRecord.get('GeoserviceTransport_id'),
															wialonStore = Ext.StoreManager.lookup('GeoserviceTransportStore'),
															wialonRecord =	wialonStore ? wialonStore.findRecord('GeoserviceTransport_id', wialon_id) : null;
													}


													if(!address) return rec.set('transTime', 'Невозможно определить');

													if(wialonRecord) {
														var carCoords = [wialonRecord.data.lat, wialonRecord.data.lng];
													} else  {
														return rec.set('transTime', 'Невозможно определить');
													}

													mapPanel.geocode(address.nick, function (res) {
														if(res) {
															if(wialonRecord) {
																mapPanel.getRouteTime(carCoords, address.nick, function (result) {
																	if(result != null) {
																		getCurrentDateTime({callback: function(datetime) {
																			var serverDT = datetime.date+' '+datetime.time;

																			serverDT = Ext.Date.parse(serverDT, 'd.m.Y H:i');

																			var transportTime = Ext.Date.format(new Date(serverDT.getTime()+result.value*1000), 'd.m.Y H:i');
																			rec.set('transTime', transportTime+' ('+result.text+')');
																			rec.set('transtDateTime', transportTime);
																			gridView.refresh();
																		}});
																	} else {
																		rec.set('transTime', 'Невозможно определить');
																		gridView.refresh();
																	}
																});
															}

														} else {
															rec.set('transTime', 'Невозможно определить');
															gridView.refresh();
														}
													});
												});
												gridView.getSelectionModel().select(0)
											}
										}

									}),
									columns: [
										{
											dataIndex:'operation_name',
											text: 'Операционный стол',
											flex: 1,
											renderer: function (a,b, rec) {
												return rec.data.resource.name;
											}
										},
										{
											dataIndex: 'lpu_name',
											text: 'МО госпитализации',
											flex: 1,
											renderer: function (a,b, rec) {
												return rec.data.lpu.nick;
											}
										}, {
											dataIndex: 'transTime',
											text: 'Расчетное время доставки пациента в МО',
											flex: 1,
											value: 'Расчет...'
										}, {
											dataIndex: 'transtDateTime',
											hidden: true,
										},
										{
											dataIndex: 'operation_freetime',
											text: 'Расчетное время освобождения операционной',
											flex: 1,
											renderer: function (a,b, rec) {
												var now = Ext.Date.format(new Date(), 'd.m.Y H:i'),
													begDt = Ext.Date.format(new Date(rec.data.resource.begdt), 'd.m.Y H:i'),
													result = '';

												if(begDt == now) {
													result = 'Свободна';
												} else {
													result = begDt;
												}
												return result;
											}
										}
									],
									listeners: {
										selectionchange: function (cmp, record) {
											var selectedLpu = record[0].data.lpu.id;

										}
									}
								}
							]
						},
						//травма
						{
							refId: 'PrehospTraumaScaleFieldset',
							title: 'Травма',
							xtype: 'fieldset',
							collapsible: true,
							hidden: true,
							disabled: true,
							defaults: {
								forCalculating: true,
								labelWidth: 400,
								anchor: '100%'
							},
							items: [
								{
									//Реакция на боль
									xtype: 'painResponseCombo',
									name: 'PainResponse_id',
									listeners: {
										change: traumaChangeFunc
									}
								}, {
									//Характе внешнего дыхания
									xtype: 'externalRespirationCombo',
									name: 'ExternalRespirationType_id',
									listeners: {
										change: traumaChangeFunc
									}
								}, {
									//Систолическое АД
									xtype: 'arterialPressureCombo',
									name: 'SystolicBloodPressure_id',
									listeners: {
										change: traumaChangeFunc
									}
								}, {
									//Признаки внутреннего кровотечения
									xtype: 'signsOfInternalBleedingCombo',
									name: 'InternalBleedingSigns_id',
									listeners: {
										change: traumaChangeFunc
									}
								}, {
									//Отрыв конечности
									xtype: 'limbSeparationCombo',
									name: 'LimbsSeparation_id',
									listeners: {
										change: traumaChangeFunc
									}
								}, {
									xtype: 'numberfield',
									name: 'PrehospTraumaScale_Value',
									fieldLabel:'Итого баллов',
									allowBlank: false,
									forCalculating: false,
									minValue: 1,
									maxValue: 40,
									listeners: {
										change: function (cmp, val) {
											win.setGeneralState();
										}
									}
								}
							]
						},
						//онмк
						{
							refId: 'ScaleLamsFieldset',
							title: 'ОНМК',
							xtype: 'fieldset',
							collapsible: true,
							hidden: true,
							disabled: true,
							defaults: {
								forCalculating: true,
								labelWidth: 400,
								anchor: '100%'
							},
							items: [ {
									//Асиметрия лица
									xtype: 'faceAsymetryCombo',
									name: 'FaceAsymetry_id',
									listeners: {
										change: lamsChangeFunc
									}
								}, {
									//Удержание рук
									xtype: 'handHoldCombo',
									name: 'HandHold_id',
									listeners: {
										change: lamsChangeFunc
									}
								}, {
									//Сжимание в кисти
									xtype: 'squeezingBrushCombo',
									name: 'SqueezingBrush_id',
									listeners: {
										change: lamsChangeFunc
									}
								}, {
									xtype: 'numberfield',
									name: 'ScaleLams_Value',
									fieldLabel:'Итого баллов',
									forCalculating: false,
									allowBlank: false,
									minValue: 0,
									maxValue: 5
								}
							]
						},
						
					]
				}
			],
			leftButtons: win.selectBtn
		};

		if(getRegionNick() == 'ufa') {
			win.Diag_id.addListener( 'change', function(combo,recs) {
				win.setVisibleFieldsets();
			});
		}

		win.callParent(arguments);
	},

	setVisibleTraumaFieldset(CmpReason_Name) {
		var win = this,
			traumaFieldset = win.down('[refId=PrehospTraumaScaleFieldset]'),
			CmpReason_Code = '';

		if(CmpReason_Name)
			CmpReason_Code = CmpReason_Name.split('.')[0];

		var active = win.traumaReasonCode.includes(CmpReason_Code);

		traumaFieldset.setVisible(active);
		traumaFieldset.setDisabled(!active);
		win.center();
	},

	setVisibleFieldsets() {
		var win = this,
			oksFieldset = win.down('[refId=oksFieldset]'),
			onmkFieldset = win.down('[refId=ScaleLamsFieldset]'),
			operationFieldset = win.down('[refId=operationFieldset]'),
			operationGridPanel = win.down('[refId=operationGridPanel]'),
			operationCheck = win.down('[refId=operationCheck]'),
			viewAllMoCheck = win.down('[name=viewAllMO]'),
			baseForm = win.down('BaseForm').getForm(),
			isOks = win.Diag_id.getDiagType() == 'OKS',
			isOnmk = win.Diag_id.getDiagType() == 'ONMK',
			rec = win.Diag_id.getStore().getById(win.Diag_id.getValue()+'');

		oksFieldset.setVisible( isOks );
		oksFieldset.setDisabled( !isOks );
		onmkFieldset.setVisible( isOnmk );
		onmkFieldset.setDisabled( !isOnmk );

		if(rec) {
			var rec_code = rec.get('Diag_Code');
			if(isOks || rec_code.inlist(win.operationDiacCode)) {
				if(rec_code.inlist(['I20.0', 'I21.4'])) {
					operationCheck.setVisible(true);
					if(!operationCheck.getValue()) {
						operationFieldset.setVisible(false);
						viewAllMoCheck.setVisible(true);
						win.selectBtn.setDisabled(true);
					} else {
						operationFieldset.setVisible(true);
						operationGridPanel.store.load();
						viewAllMoCheck.setVisible(false);
						win.selectBtn.setDisabled(false);
					}
				} else {
					operationCheck.setVisible(false);
					operationFieldset.setVisible(true);
					operationGridPanel.store.load();
					viewAllMoCheck.setVisible(false);
					win.selectBtn.setDisabled(false);
				}
			} else {
				viewAllMoCheck.setVisible(true);
				operationFieldset.setVisible(false);
			}
		} else {
			viewAllMoCheck.setVisible(true);
			operationFieldset.setVisible(false);
		}

		if(isOks) {
			baseForm.findField('ResultECG').getStore().load();
		} else if(isOnmk) {
			//todo load stores
		}

		baseForm.isValid();
		win.center();
	},

	/**
	 * Устанавливает поле "Итого баллов" только для чтения если есть хотя бы одно поле из раздела заполнено
	 */
	setReadOnlyPointField(section) {
		var win = this,
			fields = win.down('[refId='+section+'Fieldset]').items.items,
			pointsField = win.down('[name='+section+'_Value]');

		var disable = false;
		fields.forEach( function(field) {
			if(field.forCalculating && field.getValue())
				disable = true;
		});
		pointsField.setReadOnly(disable);
	},

	//устанавливаем общее состояние на основе высчитанных шкал;
	setGeneralState: function() {
		var win = this,
			baseForm = win.down('BaseForm').getForm(),
			generalStateCombo = baseForm.findField('cmpcommonstate_id'),
			traumaPoints = win.calcScalePoints('PrehospTraumaScale'),
			scaleLamsPoints = win.calcScalePoints('ScaleLams');

		// todo меняем общее состояние
		switch(true) {
			//тяжелое состояние
			case scaleLamsPoints >= 5:
			case traumaPoints > 22:
				generalStateCombo.setValueByCode('1004');
				break;

			//средней степени тяжести
			case scaleLamsPoints >= 1 && scaleLamsPoints <=4:
			case traumaPoints >= 10 && traumaPoints <= 21:
				generalStateCombo.setValueByCode('1003');
				break;

			//удовлетворительное
			case scaleLamsPoints == 0:
			case traumaPoints >= 5 && traumaPoints <= 9:
				generalStateCombo.setValueByCode('1002');
				break;
		}
	},

	//оценка общего состояния на основе заполнения разделов "Травма" или "ОНМК"
	calcScalePoints: function(section) {
		var win = this,
			fields = win.down('[refId='+section+'Fieldset]').items.items,
			pointsField = win.down('[name='+section+'_Value]'),
			points = 0;

		fields.forEach( function(field) {
			if(field.forCalculating)
				points += field.getPoints();
		});
		pointsField.setValue( points );
		return points;
	},
	showYellowMsg: function(msg, delay){
		var div = document.createElement('div');

		div.style.width='300px';
		div.style.height='65px';
		div.style.background='#edcd4b';
		div.style.border='solid 2px #efefb3';
		div.style.position='absolute';
		div.style.padding='10px';
		div.style.zIndex='99999';
		div.innerHTML = msg;
		div.style.right = 0;
		div.style.bottom = '50px';
		document.body.appendChild(div);

		setTimeout(function(){
			div.parentNode.removeChild(div);
		}, delay);
	}
});

