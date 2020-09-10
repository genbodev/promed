Ext.define('sw.tools.swCmpCloseCardShortWindow', {
	extend: 'Ext.window.Window',
	alias: 'widget.swCmpCloseCardShortWindow',
	maximized: true,
//	refId : 'CmpCloseCardShortWindow',
	id:  'swCmpCloseCardShortWindow',
	renderTo: Ext.getCmp('inPanel').body,
	constrain: true,
	closable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	action: null,
	storePerson: {},
	callback: Ext.emptyFn,
	loadFarmacyGrid: function() {
		this.farmacyRegisterGrid.getStore().load({
			params:{
				CmpCallCard_id: this.down('BaseForm').getForm().findField('CmpCallCard_id').getValue()
			}
		});
	},
	//Метод удаления записи из грида медикаментов
	removeSelectedDrugRecord: function(data) {
		var selection = this.farmacyRegisterGrid.getSelectionModel().getSelection();
		if (!selection.length) {
			return false;
		}
		
		var record = selection[0];
		
		if (record.get('EmergencyTeamDrugPackMove_id') < 0) {
			this.farmacyRegisterGrid.getStore().remove(record);
		} else {
			record.set('status','deleted');
		}
		
	},
	setDisabledFields: function(form){
		var allCmps = form.getFields();
		allCmps.filterBy(function(o, k){
			o.setReadOnly(true)
			Ext.EventManager.purgeElement(o.getEl())
		});
		
		Ext.ComponentQuery.query('button[name="clearPersonFields"]', this)[0].setDisabled(true);
		Ext.ComponentQuery.query('button[name="searchPersonBtn"]', this)[0].setDisabled(true);
		Ext.ComponentQuery.query('button[name="unknowPersonBtn"]', this)[0].setDisabled(true);
	},
	//Метод добавления записи в грид медикаментов
	addDrugRecord: function(data) {
		
		data.EmergencyTeamDrugPackMove_id = null;
		
		data['status'] = 'added';
		data['EmergencyTeamDrugPackMove_id'] = Math.floor(Math.random() * (-100000));
		this.farmacyRegisterGrid.getStore().loadData([data],true);
	},
	
	//Метод редактирования записи в гриде медикаментов
	editDrugRecord: function(data) {
		if (!data.EmergencyTeamDrugPackMove_id) {
			return false;
		}
		
		var grid = this.farmacyRegisterGrid,
			rec = grid.getStore().findRecord('EmergencyTeamDrugPackMove_id',data.EmergencyTeamDrugPackMove_id);
			
		if (!rec) {
			return false;
		}
		
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				rec.set(key,data[key]);
			}
		}
		
		rec.set('status','edited');
		rec.commit();
	},
	initComponent: function() {
		
		this.storePerson = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.Person');
		
		var cityCombo = Ext.create('sw.dCityCombo', {
			labelWidth: 150,
			//disabled: true,
		}),
		streetsCombo = Ext.create('sw.streetsSpeedCombo', {
			name:'dStreetsCombo',
			labelAlign: 'right',
			labelWidth: 150,
			//disabled: true,
		}),
		cmpCallPlaceCombo = Ext.create('sw.CmpCallPlaceType', 
		{
			labelAlign: 'right', 
			value: 1,
			labelWidth: 150,
			triggerClear: true,
			hideTrigger:true
		}),

		sexCombo = Ext.create('sw.sexCombo', {
			name: 'Sex_id',
			labelAlign: 'right',
			enableKeyEvents : true,
			triggerClear: true,
			hideTrigger:true
		}),

		callTypeCombo = Ext.create('swCmpCallTypeCombo', {
			labelWidth: 150
		}),

		smpUnitsCombo = Ext.create('sw.SmpUnits',{
			labelWidth: 150
		}),

		smpCallerTypeCombo = Ext.create('sw.CmpCallerTypeCombo',{
			labelWidth: 150,
			labelAlign: 'right',
			triggerClear: true,
			hideTrigger:true
		});
			
		this.lpuLocalCombo = Ext.create('sw.lpuLocalCombo', {
			labelWidth: 150
		});
		
		var pacientSearchResText = Ext.create('Ext.panel.Panel', {
			width: 370,
			height: 30,
			name: 'status_panel',
			refId: 'pacientSearchResText',
			html: '',
			hidden: true
		});
		
		// var pacientIdentText = Ext.create('Ext.panel.Panel', {
			// width: 370,
			// height: 30,
			// name: 'pacientIdentTextPanel',
			// refId: 'pacientIdentTextPanel',
			// html: '<div><span style="margin-top: 10px; float: right; color: #BF2626;">Необходима идентификация пациента</span></div>',
			// hidden: true,
			// border: false
		// });
		
		
		
		var wnd = this;
		
		this.farmacyRegisterGridAdd = Ext.create('Ext.Button',{
			xtype: 'button',
			itemId: 'addDrugButton',
			text: 'Добавить',
			disabled: true,
			iconCls: 'add16',
			handler: function(){
				Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamDrugPackMoveEditWindow').show({
					callback: function(data)  {
						wnd.addDrugRecord(data);
					},
					CmpCallCard_id: this.down('BaseForm').getForm().findField('CmpCallCard_id').getValue(),
					action: 'add'
				});
			}.bind(this)
		});
		
		this.farmacyRegisterGridEdit = Ext.create('Ext.Button',{
			xtype: 'button',
			itemId: 'editDrugButton',
			text: 'Редактировать',
			iconCls: 'edit16',
			handler: function(){
				
				var selection = this.farmacyRegisterGrid.getSelectionModel().getSelection();
				if (!selection.length) {
					return false;
				}
				
				var record = selection[0];
				
				Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamDrugPackMoveEditWindow').show({
					callback: function(data)  {
						wnd.editDrugRecord(data);
					},
					formParams: record.data,
					CmpCallCard_id: this.down('BaseForm').getForm().findField('CmpCallCard_id').getValue(),
					action: 'edit'
				});
			}.bind(this)
		});
		
		this.farmacyRegisterGridDelete = Ext.create('Ext.Button',{
			xtype: 'button',
			itemId: 'deleteDrugButton',
			text: 'Удалить',
			iconCls: 'delete16',
			handler: function(){
				this.removeSelectedDrugRecord();
				
				
				
			}.bind(this)
		});
		
		this.farmacyRegisterGridButtons = [
			this.farmacyRegisterGridAdd,
			this.farmacyRegisterGridEdit,
			this.farmacyRegisterGridDelete
		];
		
		this.farmacyRegisterGrid = Ext.create('Ext.grid.Panel', {
			//region: 'south',
			title: 'История списания медикаментов',
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			viewConfig: {
				//loadingText: 'Загрузка'
			},
			tbar: this.farmacyRegisterGridButtons,
			store: new Ext.data.JsonStore({
				autoLoad: false,
				storeId: 'drugHistoryCallCardListGridStore',
				fields: [
					{name: 'EmergencyTeamDrugPackMove_id', type: 'int'},
					{name: 'EmergencyTeamDrugPack_id', type: 'int'},
					{name: 'Drug_id', type: 'int'},
					{name: 'DrugTorg_Name', type: 'string'},
					{name: 'EmergencyTeamDrugPackMove_Quantity', type: 'string'},
					{name: 'status', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadEmergencyTeamDrugPackMoveList',
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
					}
				}
			}),
			columns: [
				{ dataIndex: 'DrugTorg_Name', text: 'Название медикамента', flex: 1 },
				{ dataIndex: 'EmergencyTeamDrugPackMove_Quantity', text: 'Кол-во (доз)', width: 120 }
				
			]
		});

		this.farmacyRegisterGrid.getStore().on('add',function(store){
			
			this.farmacyRegisterGridEdit.setDisabled(store.find('status',/added|edited|unchanged/) == -1);
			this.farmacyRegisterGridDelete.setDisabled(store.find('status',/added|edited|unchanged/) == -1);
				
		}.bind(this));
		
		this.farmacyRegisterGrid.getStore().on('update',function(store){
			
			this.farmacyRegisterGridEdit.setDisabled(store.find('status',/added|edited|unchanged/) == -1);
			this.farmacyRegisterGridDelete.setDisabled(store.find('status',/added|edited|unchanged/) == -1);
			
			this.farmacyRegisterGrid.getStore().filterBy(function(rec,ind){
				return (rec.get('status')!=='deleted');
			});
			
		}.bind(this));

		
		Ext.applyIf(this,{
			xtype: 'container',
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			margin: '0 0 0 0',
			style: {
				'z-index': 90000
			},
			items: [{
				xtype:'BaseForm',
				id: this.id+'_BaseForm',
				flex: 1,
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				url:'/?c=CmpCallCard4E&m=saveCmpCallCardClose',
				items:[{
					xtype: 'container',
					flex: 1,
					layout: {
						align: 'stretch',
						type: 'hbox'
					},
					margin: '0 0 0 0',
					style: {
						'z-index': 90000
					},
					items: [
						{
							xtype: 'hidden',
							value: 0,
							name: 'CmpCallCard_id'
						},
						{
							name: 'CmpLpu_Name',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'CmpLpu_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_Age',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_isOftenCaller',
							value: 1,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_rid',
							value: null,
							xtype: 'hidden'
						},
						{
							xtype: 'fieldset',
							autoRender: false,
							margin: '0 0 10 10',
							flex: 1,
							id: this.id+'callPalceFS',
							defaultAlign: 'left',
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
							autoScroll: true,
							title: 'Место вызова',
							items: [
								cityCombo,
								streetsCombo,
								{
									xtype: 'fieldcontainer',
									layout: {
										type: 'hbox'
									},
									items:[
										{
											xtype: 'textfield',
											flex: 1,
											//disabled: true,
											plugins: [new Ux.Translit(true, true)],
											fieldLabel: 'Дом',
											labelAlign: 'right',
											name: 'CmpCallCard_Dom',
											enableKeyEvents : true,
											labelWidth: 150,
										},
										{
											xtype: 'textfield',
											flex: 1,
											//maskRe: /[0-9:]/,
											//disabled: true,
											plugins: [new Ux.Translit(true, true)],
											fieldLabel: 'Квартира',
											enforceMaxLength: true,
											maxLength: 5,
											labelAlign: 'right',
											name: 'CmpCallCard_Kvar',
											enableKeyEvents : true,
											labelWidth: 100
										},
									]
								},
								{
									xtype: 'fieldcontainer',
									layout: {
										type: 'hbox'
									},
									items:[
										{
											xtype: 'textfield',
											flex: 1,
											maskRe: /[0-9:]/,
											//disabled: true,
											fieldLabel: 'Подъезд',
											labelAlign: 'right',
											name: 'CmpCallCard_Podz',
											enableKeyEvents : true,
											labelWidth: 150
										},
										{
											xtype: 'textfield',
											maskRe: /[0-9:]/,
											//disabled: true,
											flex: 1,
											fieldLabel: 'Этаж',
											labelAlign: 'right',
											name: 'CmpCallCard_Etaj',
											enableKeyEvents : true,
											labelWidth: 100
										}
									]
								},
								{
									xtype: 'textfield',
									//maskRe: /[0-9:]/,
									fieldLabel: 'Код в подъезде / домофон',
									labelAlign: 'right',
									name: 'CmpCallCard_Kodp',
									enableKeyEvents : true,
									labelWidth: 150
								},
								cmpCallPlaceCombo,
								{
									xtype: 'textfield',
									width: 300,
									fieldLabel: 'Телефон',
									enableKeyEvents : true,
									maskRe: /[0-9:]/,
									labelAlign: 'right',
									name: 'CmpCallCard_Telf',
									labelWidth: 150
								},
								smpCallerTypeCombo,
								{
									xtype: 'textareafield',
									plugins: [new Ux.Translit(true)],
									minHeight: 15,
									fieldLabel: 'Дополнительная информация/ Уточненный адрес:',
									enableKeyEvents : true,
									labelAlign: 'right',
									name: 'CmpCallCard_Comm',
									labelWidth: 150
								}				
							]
						},{
							xtype: 'fieldset',
							autoRender: false,
							margin: '0 0 10 10',
							flex: 1,
							defaultAlign: 'left',
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
							id: this.id+'clientInfoFS',
							title: 'Пациент',
							autoScroll: true,
							items: [
								{
									xtype: 'container',
									layout: {
										type: 'hbox',
										align: 'stretch'
									},
									items: [
										{
											xtype: 'container',
											flex: 1,
											//margin: '0 10',
											layout: {
												type: 'vbox',
												align: 'stretch'
											},
											items: [
												{
													xtype: 'cmpReasonCombo',
													width: 250,
													fieldLabel: 'Повод',
													name: 'CmpReason_id',
													labelAlign: 'right',
													enableKeyEvents : true,
												},
												//pacientIdentText,
												{
													xtype: 'textfield',
													plugins: [new Ux.Translit(true, true)],
													width: 250,
													fieldLabel: 'Фамилия',
													labelAlign: 'right',
													name: 'Person_Surname',
													enableKeyEvents : true,
													listeners: {
														blur: function(cmp){
															if (cmp.getValue()){
																this.searchPerson();
															}					
														}.bind(this)
													}
												},
												{
													xtype: 'textfield',
													plugins: [new Ux.Translit(true, true)],
													width: 250,
													fieldLabel: 'Имя',
													labelAlign: 'right',
													name: 'Person_Firname',
													enableKeyEvents : true,
													listeners: {
														blur: function(cmp){
															if (cmp.getValue()){
																this.searchPerson();
															}					
														}.bind(this),
													}
												},
												{
													xtype: 'textfield',
													plugins: [new Ux.Translit(true, true)],
													width: 250,
													fieldLabel: 'Отчество',
													labelAlign: 'right',
													name: 'Person_Secname',
													listeners: {
														blur: function(cmp){
															if (cmp.getValue()){
																this.searchPerson();
															}					
														}.bind(this),
													}
												},
												{
													xtype: 'textfield',
													width: 202,
													fieldLabel: 'Возраст',
													enableKeyEvents : true,
													labelAlign: 'right',
													name: 'Person_Birthday',
												},
												sexCombo,
												{
													xtype: 'textfield',
													plugins: [new Ux.Translit(true, true)],
													width: 450,
													fieldLabel: 'Серия полиса',
													labelAlign: 'right',													
													name: 'Polis_Ser',
													enableKeyEvents : true,
													hideTrigger: true,
													keyNavEnabled: false,
													mouseWheelEnabled: false,
													//disabled: true
												},
												{
													xtype: 'numberfield',
													width: 450,
													fieldLabel: 'Номер полиса',
													labelAlign: 'right',
													name: 'Polis_Num',
													enableKeyEvents : true,
													hideTrigger: true,
													keyNavEnabled: false,
													mouseWheelEnabled: false,
													//disabled: true
												},
												{
													xtype: 'numberfield',
													width: 450,
													fieldLabel: 'Единый номер',
													labelAlign: 'right',
													name: 'Polis_EdNum',
													enableKeyEvents : true,
													hideTrigger: true,
													keyNavEnabled: false,
													mouseWheelEnabled: false,
													//disabled: true
												},
												{
													xtype: 'container',
													flex: 1,
													layout: {
														type: 'vbox'
					//									,align: 'stretch'
													},
													items: [

														pacientSearchResText
													]
												},
											]
										},
										{
											xtype: 'container',
											//width: 150,
											margin: '0 10',
											layout: {
												type: 'vbox'																
											},
											items: [
												{
													xtype: 'label',
													text: ' ',
													margin: '2 0',
													height: 27
												},
												{
													xtype: 'button',
													name: 'clearPersonFields',
													text: 'Сброс',
													//id: 'CCCSEF_PersonResetBtn',
													iconCls: 'delete16',
													handler: function(){
														Ext.Ajax.abortAll();
														this.storePerson.removeAll();
														this.clearPersonFields(this);
													}.bind(this)

												},
												{
													xtype: 'button',
													name: 'searchPersonBtn',
													text: 'Поиск',
													iconCls: 'search16',
													margin: '5 0',
													handler: function(){
														Ext.Ajax.abortAll();
															Ext.create('sw.tools.subtools.swPersonWinSearch', 
															{
																personform: this.down('BaseForm').getForm(), 
																storePerson: this.storePerson, 
																caller: this
															}).show()
													}.bind(this)
												},
												{
													xtype: 'button',
													name: 'unknowPersonBtn',
													text: 'Неизвестен',
													iconCls: 'warning16',
													handler: function(){
														Ext.Ajax.abortAll();
														this.storePerson.removeAll();
														this.clearPersonFields();
														var baseForm = this.down('BaseForm').getForm()
														var f = baseForm.findField('Person_Surname'),
															i = baseForm.findField('Person_Firname'),
															o = baseForm.findField('Person_Secname');

														f.setValue('Неизвестен');
														i.setValue('Неизвестен');
														o.setValue('Неизвестен');
														f.disable();
														i.disable();
														o.disable();
													}.bind(this)
												}
											]
										}
									]
								},
								
							]
						},
						{
							xtype: 'fieldset',
							autoRender: false,
							margin: '0 0 10 10',
							flex: 1,
							defaultAlign: 'left',
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
							autoScroll: true,
							//ДОПОЛНИТЬ и ИЗМЕНИТЬ ПОСЛЕ УТОЧНЕНИЯ
							title: 'Вызов',
							items: [
								callTypeCombo,
								{
									xtype: 'swCmpCallReasonType',
									fieldLabel: 'Результат',
									labelAlign: 'right',
									allowBlank: false,
									//hidden: getGlobalOptions().region.nick == 'pskov',
									enableKeyEvents : true,
									labelWidth: 150,
									listeners: {
										change: function(combo, newValue, oldValue, eOpts ) {
											var rec = combo.getStore().findRecord(combo.valueField,newValue),
												form = this.down('BaseForm').getForm(),
												resultCodeList = ['11','12','13','14','15','16','17','18'];

											if (rec && (resultCodeList.indexOf(rec.get(combo.codeField))!=-1)) {
												form.findField('CmpCallCard_Tsta').allowBlank = false;
												form.findField('CmpCallCard_Tsta').validate();
												form.findField('CmpCallCard_Tgsp').allowBlank = false;
												form.findField('CmpCallCard_Tgsp').validate();
												form.findField('LpuTransmit_id').setDisabled( false );
											} else {
												form.findField('CmpCallCard_Tsta').allowBlank = true;
												form.findField('CmpCallCard_Tsta').validate();
												form.findField('CmpCallCard_Tgsp').allowBlank = true;
												form.findField('CmpCallCard_Tgsp').validate();
												form.findField('LpuTransmit_id').setDisabled( true );
											}
										}.bind(this)
									}
								},
								{
									xtype: 'lpuLocalCombo',
									fieldLabel: 'ЛПУ передачи',
									name: 'LpuTransmit_id',
									//disabled:true,
									//allowBlank: false,
									labelAlign: 'right',
									enableKeyEvents : true,
									labelWidth: 150,

								},
								{
									xtype: 'swCmpDiseaseAndAccidentType',
									//hidden: getGlobalOptions().region.nick == 'pskov',									
									plugins: [new Ux.Translit(true, true)],
									labelAlign: 'right',
									//allowBlank: false,
									enableKeyEvents : true,
									labelWidth: 150
								},
								{
									xtype: 'swDiag',
									fieldLabel: 'Диагноз первичный',
									labelAlign: 'right',
									name: 'CmpDiag_oid',
									allowBlank: false,
	//								enableKeyEvents : true,
									labelWidth: 150,
								},
								{
									xtype: 'swDiag',
									fieldLabel: 'Диагноз вторичный',
									labelAlign: 'right',
									name: 'CmpDiag_aid',
									enableKeyEvents : true,
									labelWidth: 150,
								},
								{
									xtype: 'swIsAlco',
									plugins: [new Ux.Translit(true, true)],
									fieldLabel: 'Алкоголь',
									labelAlign: 'right',
									name: 'CmpCallCard_IsAlco',
									enableKeyEvents : true,
									labelWidth: 150,
								},
								{
									xtype: 'textfield',
									plugins: [new Ux.Translit(true, true)],
									fieldLabel: 'Километраж',
									labelAlign: 'right',
									maskRe: /[0-9:]/,
									name: 'CmpCallCard_Kilo',
									enableKeyEvents : true,
									labelWidth: 150
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Выезд',
									labelAlign: 'right',
									name: 'CmpCallCard_Vyez',
									plugins: [new Ux.InputTextMask('99:99')],
								//	allowBlank: false,
									allowBlank: true,
									labelWidth: 150,
									width: 260
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Прибытие',
									labelAlign: 'right',
									name: 'CmpCallCard_Przd',
									plugins: [new Ux.InputTextMask('99:99')],
								//	allowBlank: false,
									allowBlank: true,
									labelWidth: 150
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Начало эвакуации',
									labelAlign: 'right',
									name: 'CmpCallCard_Tgsp',
									plugins: [new Ux.InputTextMask('99:99')],									
									allowBlank: true,
									labelWidth: 150
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Стационар',
									labelAlign: 'right',									
									allowBlank: true,
									name: 'CmpCallCard_Tsta',
									plugins: [new Ux.InputTextMask('99:99')],
									labelWidth: 150
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Исполнено',
									labelAlign: 'right',
									name: 'CmpCallCard_Tisp',
									plugins: [new Ux.InputTextMask('99:99')],
								//	allowBlank: false,
									allowBlank: true,
									labelWidth: 150
								},
								{
									xtype: 'timeGetCurrentTimeCombo',
									fieldLabel: 'Возвращение',
									labelAlign: 'right',
									name: 'CmpCallCard_Tvzv',
									plugins: [new Ux.InputTextMask('99:99')],
								//	allowBlank: false,
									allowBlank: true,
									labelWidth: 150
								},
								/*
								 *var timeFromGarage = Ext.create('sw.timeGetCurrentTimeCombo', {
									labelAlign: 'right',
									fieldLabel: 'Время выезда из гаража',
									name: 'Waybill_TimeStart',
									allowBlank: false,
									labelWidth: 260,
									width: 370,
									plugins: [new Ux.InputTextMask('99:99')]
								});
								 **/
							]
						}
					]},
					this.farmacyRegisterGrid
				]
			}],
			bbar: [
				{
					xtype: 'button',
					id: this.id+'_helpBtn',
					text: 'Помощь',
					iconCls   : 'help16',
					handler   : function()
					{
						ShowHelp(this.ownerCt.title);
					}
				},
				'->',
				{
					xtype: 'button',
					id: this.id+'_saveBtn',
					iconCls: 'save16',
					text: 'Сохранить',
					handler: function(){
						var form = this.down('BaseForm').getForm();
						if (!form.isValid()) {
							Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
							return;
						}
						
						var drug_items = this.farmacyRegisterGrid.getStore().query('EmergencyTeamDrugPackMove_id',/[^0]/).items;
		
						var drug_data_array = [];
						
						for (var i = 0; i < drug_items.length; i++) {
							drug_data_array.push(drug_items[i].data);
						};
						
						
						var EmergencyTeamDrugPackMoveList_Json = JSON.stringify(drug_data_array);
						
						
						var params = this.down('BaseForm').getForm().getFieldValues();
						var form = this.down('BaseForm').getForm();
						//Невидимки
						params = {
							CmpCallCard_id:form.findField('CmpCallCard_id').getValue(),
							CmpLpu_Name:form.findField('CmpLpu_Name').getValue(),
							CmpLpu_id:form.findField('CmpLpu_id').getValue(),
							Person_Age:form.findField('Person_Age').getValue(),
							Person_id:form.findField('Person_id').getValue(),
							Person_isOftenCaller:form.findField('Person_isOftenCaller').getValue(),
							CmpCallCard_rid:form.findField('CmpCallCard_rid').getValue(),
							EmergencyTeamDrugPackMoveList: EmergencyTeamDrugPackMoveList_Json
						}
						form.submit({
							params:params,
							success: function(opts,response){
								var res = JSON.parse(response.response.responseText);
								if (res.Error_Msg) {
									Ext.MessageBox.alert('Ошибка', res.Error_Msg);
									return;
								}
								this.callback(res);
								this.close();
							}.bind(this),
							failure: function(opts, response ){
								Ext.MessageBox.alert('Ошибка', 'При сохранении талона вызова произошла ошибка. Обратитесь к администратору');
							}
							
						});

					}.bind(this)
				},
				{
					xtype: 'button',
					id: this.id+'_cancelBtn',
					iconCls: 'cancel16',
					text: 'Отменить',
					margin: '0 5',
					handler: function(){
						this.close()
					}.bind(this)
				},
				
				
			]
		});
		
		
		this.callParent();
	},
	show: function() {
		this.callParent();
		var  me = this;
		var baseForm = this.down('BaseForm');
		
		if (!arguments || !arguments[0] ||(typeof arguments[0].callback != 'function')) {
			Ext.Msg.alert('Ошибка открытия формы', 'Не переданы необходисые параметры формы');
			Ext.defer(function() {
				Ext.MessageBox.hide();
			}, 3000);
			this.close();
			return false;
		}
		
		baseForm.getForm().findField('CmpCallCard_id').setValue(arguments[0].CmpCallCard_id);
		this.callback = arguments[0].callback;
		

		this.loadFarmacyGrid();
		
		var loadMask = new Ext.LoadMask(this, {msg:"Пожалуйста, подождите..."});
		loadMask.show();
		
		baseForm.on('storeloaded', function(){
			loadMask.hide();
		});
		
		this.action = arguments[0].action;
		if (arguments[0].action == 'show') {
			Ext.getCmp(this.id+'_saveBtn').setDisabled(true);
			me.setDisabledFields(baseForm.getForm());
		}
		
		Ext.Ajax.request({
			method: 'POST',
			url:'/?c=CmpCallCard4E&m=loadCmpCloseCardShort',
			params: {
				CmpCallCard_id:arguments[0].CmpCallCard_id
			},
			success: function(response, opts) {
				var res = JSON.parse(response.responseText);
				if (res.Error_Msg) {
					Ext.MessageBox.alert('Ошибка', res.Error_Msg);
					loadMask.hide();
					return;
				}
				me.setValues(res);
			},
			failure: function() {
				Ext.MessageBox.alert('Ошибка', 'При загрузке карты закрытия вызова произошла ощибка. Обратитесь к администратору');
				loadMask.hide();
			}
		});
		this.down('BaseForm').getForm().isValid();
	},
	setValues: function(data) {
		var baseForm = this.down('BaseForm').getForm(),
			me = this;
		
		for (key in data) {
			if (data.hasOwnProperty(key) && baseForm.findField(key)) {
//				if (baseForm.findField(key).xtypesMap.combobox) {
//					baseForm.findField(key).setValue(parseInt(data[key]));
//				} else {
//					baseForm.findField(key).setValue(data[key]);
//				}
				baseForm.findField(key).setValue((baseForm.findField(key).xtypesMap.combobox&&(data[key]!==null))?parseInt(data[key]):data[key]);
			}
		}
		
		
		
		var cityCombo = baseForm.findField('dCityCombo'),
			streetsCombo = baseForm.findField('dStreetsCombo'),
			diagFirstCombo = baseForm.findField('CmpDiag_oid');
//			diagSecondCombo = baseForm.findField('Diag_sid');
			
			diagFirstCombo.bigStore.load();
			

	//место вызова
		cityCombo.store.getProxy().extraParams = {
			'region_id' : data.KLRgn_id,
			'region_name' : getGlobalOptions().region.name,
			'city_default' : data.KLCity_id
		};

		cityCombo.store.load({
			callback: function(rec, operation, success) {
			if (this.getCount() == 1)
				{
					cityCombo.setValue(rec[0].get('Town_id'));

					streetsCombo.bigStore.getProxy().extraParams = {
						'town_id' : rec[0].get('Town_id'),
						'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
					};

					streetsCombo.bigStore.load({
						callback: function(rec, operation, success) {
							var rec = streetsCombo.bigStore.findRecord('StreetAndUnformalizedAddressDirectory_id', data.StreetAndUnformalizedAddressDirectory_id);
							if (rec){
								streetsCombo.store.removeAll();
								streetsCombo.store.add(rec);
								streetsCombo.setValue(rec.get('StreetAndUnformalizedAddressDirectory_id'));
							}										
						}
					});
				}
			}
		});

	},
	
	checkPersonValidInfo: function(params){
		var identyfyButton = this.down('panel[name=pacientIdentTextPanel]');
		if ( 
				(params.Person_Surname != params.PersonIdent_Surname) ||
				(params.Person_Firname != params.PersonIdent_Firname) ||
				(params.Person_Secname != params.PersonIdent_Secname) ||
				(params.Person_Age != params.PersonIdent_Age)
			)
		{
			//console.log('данные пациента изменены/пациент не идентифицирован');
			identyfyButton.show();
		}
		else{
			//console.log('пациент не тронут');
		}
	},
	
	clearPersonFields: function(){
		
		var baseForm = this.down('BaseForm').getForm(),
			wnd = this,
			f = baseForm.findField('Person_Surname'),
			i = baseForm.findField('Person_Firname'),
			o = baseForm.findField('Person_Secname'),
			pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]');
	
		f.reset();
		i.reset();
		o.reset();
//		baseForm.findField('Person_Birthday').reset();
//		baseForm.findField('Person_Age_From').reset();
//		baseForm.findField('Person_Age_To').reset();
		baseForm.findField('Polis_EdNum').reset();
		baseForm.findField('Polis_Num').reset();
		baseForm.findField('Polis_Ser').reset();
		baseForm.findField('Sex_id').reset();
//		baseForm.findField('CmpReason_id').reset();
//		baseForm.findField('CmpReason_Name').reset();
		baseForm.findField('Person_Birthday').reset();
		pacientSearchResText.el.setHTML('');
		
		f.setDisabled(false);
		i.setDisabled(false);
		o.setDisabled(false);
		baseForm.findField('Polis_EdNum').disable();
		baseForm.findField('Polis_Num').disable();
		baseForm.findField('Polis_Ser').disable();
		baseForm.findField('Sex_id').enable();
		baseForm.findField('Person_Birthday').enable();
	},
	
	converterPersonBirthdayToAge: function(personDateAge){
		var personBirthYearFrom, 
				personBirthYearTo, 
				personYearsInterval,
				currentYear = Ext.Date.format(new Date,'Y');
		if (personDateAge){
			if (Ext.Date.parse(personDateAge,'Y'))
			{
			//указан год рождения
				personBirthYearFrom = personDateAge;
				personBirthYearTo = personDateAge;
//				storePerson.filter({
//					filterFn: function(item) { 
//						var s = Ext.Date.parse(item.get('PersonBirthDay_BirthDay'),'d.m.Y'),
//							d = Ext.Date.format(new Date(), 'd.m'),
//							sd = Ext.Date.format(s, 'd.m');					
//						return (Ext.Date.parse(d, 'd.m') -  Ext.Date.parse(sd, 'd.m') > 0)
//					}
//				})
			}
			else if(Ext.Date.parse(personDateAge,'d.m.Y'))
			{
				//указана дата
				var date = Ext.Date.parse(personDateAge, "d.m.Y");
				personBirthYearFrom = Ext.Date.format(date, 'Y');
				personBirthYearTo = Ext.Date.format(date, 'Y');
			}
			else
			{
			//указан возраст
				personBirthYearFrom = currentYear - personDateAge-1;
				personBirthYearTo = currentYear - personDateAge;
			}
			
			personYearsInterval = [personBirthYearFrom, personBirthYearTo];
			
			return personYearsInterval;
		}
		else return null;
		
	},
	
	searchPerson: function(){
		// if (this.action = 'show') {
			// return;
		// }
		
		var storePerson =  this.storePerson,
			baseForm = this.down('BaseForm').getForm(),
			allParams = baseForm.getFieldValues(),
			parms = {},
			personDateAge, personAgeFrom, personAgeTo;
			
		storePerson.clearFilter()
		
		baseForm.findField('Person_Age').setValue(0)
		baseForm.findField('Person_id').setValue(0)
		baseForm.findField('Person_isOftenCaller').setValue(1)
		
		personDateAge = this.converterPersonBirthdayToAge(baseForm.findField('Person_Birthday').getValue());
		//console.log(personDateAge);

		
		parms = {
			'PersonSurName_SurName' : allParams.Person_Surname,
			'PersonFirName_FirName' : allParams.Person_Firname,
			'PersonSecName_SecName' : allParams.Person_Secname,
			'PersonBirthDay_BirthDay': Ext.Date.format(allParams.Person_Birthday, 'd.m.Y'),
			'PersonBirthYearFrom': personDateAge ? personDateAge[0] : null,
			'PersonBirthYearTo': personDateAge ? personDateAge[1] : null,
			'Polis_EdNum': allParams.Polis_EdNum,
			'Polis_Num': allParams.Polis_Num,
			'Polis_Ser': allParams.Polis_Ser,
			'Sex_id': allParams.Sex_id
		}
		
		
		storePerson.getProxy().extraParams = parms;
		var msg='Идентификация пациента...',
			status='load';
			
		this.showPersonSearchMessage(msg, status);
		storePerson.load({
			callback: function(rec, operation, success) {
				if (success) {
					var dopInfo = null
					if (storePerson.getCount() == 0)
					{
						this.showPersonSearchMessage(msg='Пациент не идентифицирован', status='noone');
						baseForm.findField('Polis_EdNum').disable();
						baseForm.findField('Polis_Num').disable();
						baseForm.findField('Polis_Ser').disable();
						
						
						baseForm.findField('Sex_id').enable();
//						baseForm.findField('Person_Birthday').enable();
					}
					if (storePerson.getCount() == 1){
						var unoPacient = rec[0].getData()

						baseForm.findField('Person_Age').setValue(unoPacient.Person_Age);
						baseForm.findField('Person_id').setValue(unoPacient.Person_id);
						baseForm.findField('Person_isOftenCaller').setValue(unoPacient.Person_isOftenCaller);
						
						
						storePerson.filter('Person_id', unoPacient.Person_id)
						this.showPersonSearchMessage(msg='Пациент идентифицирован', status='uno', dopInfo=unoPacient.Person_isOftenCaller);
						this.setPatient(unoPacient);
						
						baseForm.findField('Sex_id').disable();
//						baseForm.findField('Person_Birthday').disable();
					}
					if (storePerson.getCount() > 1)
					{
						this.showPersonSearchMessage(msg='Найдено '+ storePerson.getCount()+' пациентов', status='many');
						baseForm.findField('Polis_EdNum').disable();
						baseForm.findField('Polis_Num').disable();
						baseForm.findField('Polis_Ser').disable();
						
						baseForm.findField('Sex_id').enable();
//						baseForm.findField('Person_Birthday').enable();
					}
				}
				else
				{
//					this.showPersonSearchMessage(msg='Пациент не идентифицирован', status='noone');
//					baseForm.findField('Polis_EdNum').hide(true);
//					baseForm.findField('Polis_Num').hide(true);
//					baseForm.findField('Polis_Ser').hide(true);
				}

			}.bind(this)
		});
		
	},
	
	setPatient: function(personInfo){
		
		//console.log("personInfo", personInfo)
		var baseForm = this.down('BaseForm').getForm();
		
		if (personInfo.PersonSurName_SurName){baseForm.findField('Person_Surname').setValue(personInfo.PersonSurName_SurName)}
		if (personInfo.PersonFirName_FirName){baseForm.findField('Person_Firname').setValue(personInfo.PersonFirName_FirName)}
		if (personInfo.PersonSecName_SecName){baseForm.findField('Person_Secname').setValue(personInfo.PersonSecName_SecName)}
		if (personInfo.PersonBirthDay_BirthDay){
			//console.log(baseForm.findField('PersonBirthDay'), personInfo.PersonBirthDay_BirthDay)
			baseForm.findField('Person_Birthday').setValue(personInfo.PersonBirthDay_BirthDay)
		}
//		if (personInfo.PersonAge_AgeFrom){baseForm.findField('Person_Age_From').setValue(personInfo.PersonAge_AgeFrom)}
//		if (personInfo.PersonAge_AgeTo){baseForm.findField('Person_Age_To').setValue(personInfo.PersonAge_AgeTo)}
		if (personInfo.Polis_EdNum){baseForm.findField('Polis_EdNum').setValue(personInfo.Polis_EdNum)}
		if (personInfo.Polis_Num){baseForm.findField('Polis_Num').setValue(personInfo.Polis_Num)}
		if (personInfo.Polis_Ser){baseForm.findField('Polis_Ser').setValue(personInfo.Polis_Ser)}
		if (personInfo.Sex_id){baseForm.findField('Sex_id').setValue(personInfo.Sex_id)}
		
		if (personInfo.CmpLpu_id){baseForm.findField('CmpLpu_id').setValue(personInfo.CmpLpu_id)}
		if (personInfo.Lpu_Nick){baseForm.findField('CmpLpu_Name').setValue(personInfo.Lpu_Nick)}
		if (personInfo.Person_id){baseForm.findField('Person_id').setValue(personInfo.Person_id)}
		if (personInfo.Person_Age){baseForm.findField('Person_Age').setValue(personInfo.Person_Age)}
		
	},
	
	showPersonSearchMessage: function(msg, status, dopInfo){
		var baseForm = this.down('BaseForm').getForm(),
			wnd = this,
			lpuLocalCombo = baseForm.findField('LpuTransmit_id'),
			pacientSearchResText = wnd.down('panel[refId=pacientSearchResText]'),
			src = null,
			dopPanel = '',
			parentWdth = 200,
			storePerson =  this.storePerson;
	
			pacientSearchResText.setVisible(true);

			switch (status){
					case 'load': {src = 'extjs4/resources/themes/images/default/grid/loading.gif'; break}
					case 'noone': {src = 'extjs4/resources/themes/images/default/grid/drop-no.gif'; break}
					case 'uno': {src = 'extjs4/resources/themes/images/default/grid/drop-yes.gif'; break}
					case 'many': {src = 'extjs4/resources/themes/images/default/grid/columns.gif'; break}
						
			}			

			if (dopInfo == 2){
				dopPanel = '<div style="height: 16px; float: left;'+
				'padding-left: 23px; margin: 0 10px; background-image: url(extjs4/resources/themes/images/default/shared/warning.gif);' + 
				'background-repeat: no-repeat">Часто обращающийся</div>'
				parentWdth = 350;
			}
			pacientSearchResText.el.setHTML('<div class="clientDopInfo" style="margin: 10px auto 0; width: '+ parentWdth +'px;">' + 
				'<div style="height: 16px; float: left;'+
				'padding-left: 23px;  background-image: url('+src+');' + 
				'background-repeat: no-repeat">'+ msg+'</div>' + dopPanel +
				'</div>')
			
			if ((status == 'noone') || (status == 'many'))
			{
				lpuLocalCombo.reset()
				lpuLocalCombo.disable(true)
				Ext.fly(lpuLocalCombo.getId()).select('.small-tip').setVisible(false, true)
			}
			
			if (status == 'many')
			{
				
				Ext.create('Ext.Button', {
					text: 'Выбрать',
					renderTo: pacientSearchResText.el,
					style: 'margin: -3px 6px;',
					handler: function() {
						var pacientSearchRes = Ext.create('Ext.window.Window', {
							alias: 'widget.pacientSearchRes',
							height: 250,
							modal: true,
							width: 925,
							layout: 'fit',
							//renderTo: 'clientInfoFS'
							items: {
								xtype: 'grid',
								//id: 'personsList',
								border: false,
								renderIcon: function(val) {
									if (val != 'false'){
										if (val=='true'){val='on'}
										return '<div class="x-grid3-check-'+val+' x-grid3-cc-ext-gen2118"></div>'
									}
									//return <div class="x-grid3-check-col-on-non-border-gray x-grid3-cc-ext-gen2121">&nbsp;</div>
								},
								columns: [
									{ text: 'Фамилия',  dataIndex: 'PersonSurName_SurName', width: 90 },
									{ text: 'Имя', dataIndex: 'PersonFirName_FirName', width: 80 },
									{ text: 'Отчество', dataIndex: 'PersonSecName_SecName', width: 100 },
									{ text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90 },
									
									{ text: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90, renderer: function(value){return this.renderIcon(value);}},
									{ text: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90 },
									{ text: 'Прикр. ДМС', dataIndex: 'PersonCard_IsDms', width: 70, renderer: function(value){return this.renderIcon(value);}},
									{ text: 'БДЗ', dataIndex: 'Person_IsBDZ', width: 50, renderer: function(value){return this.renderIcon(value);}},
									{ text: 'Фед. льг', dataIndex: 'Person_IsFedLgot', width: 70, renderer: function(value){return this.renderIcon(value);}},
									{ text: 'Отказ', dataIndex: 'Person_IsRefuse', width: 50, renderer: function(value){return this.renderIcon(value);}},
									{ text: 'Рег. льг', dataIndex: 'Person_IsRegLgot', width: 60, renderer: function(value){return this.renderIcon(value);}},
									{ text: '7 ноз.', dataIndex: 'Person_Is7Noz', width: 50, renderer: function(value){return this.renderIcon(value);}},
								],
								store: storePerson,
								listeners: {
									beforecellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts )
									{
										this.setPatient(record.getData());
										pacientSearchRes.close();
										this.searchPerson()
									}.bind(this)
								}
							}
						}).show()
					}.bind(this)
				})
			}
	},
	
})