Ext.define('sw.tools.subtools.swPersonWinSearch', {
    extend: 'Ext.window.Window',
    minHeight: 500,
    width: 1000,
    layout: 'fit',
    title: 'Поиск пациента',
	itemId: 'PersonSearchWin', // id не нужен, чтобы не было ошибок при повторном вызове окна
	cls: 'PersonSearchWin',
	modal: true,
	defaultFocus: 'textfield[name=Person_Surname]',
	listeners: {
		close: function(win) {
			if  (typeof win.initialConfig.callback == 'function') {
				win.initialConfig.callback()
			}
		}
	},
    initComponent: function() {
        var win = this,
			conf = win.initialConfig;
	
		win.on('afterrender', function(cmp){
			if(conf.personform){
				var pform = conf.personform,
					vals = pform.getValues(),
					currentYear = Ext.Date.format(new Date,'Y');
				
				if(vals.Person_Birthday_YearAge){
					switch(vals.ageUnit){
						case 'years': {
							vals.PersonBirthYearFrom = currentYear - vals.Person_Birthday_YearAge - 1;
							vals.PersonBirthYearTo = currentYear - vals.Person_Birthday_YearAge;
							break;
						}
						//case null:{console.log('null');},
						default: {
							vals.PersonBirthYearFrom = currentYear - 1;
							vals.PersonBirthYearTo = currentYear;
							break;
						}
					}
				}
				/*
				год рожденья кто-то затер
				if(vals.Person_Birthday_YearAge){
					console.log('vals.ageUnit', vals.ageUnit);
					if (Ext.Date.parse(vals.Person_Birthday_YearAge,'Y'))
					{
					//указан год рождения
						vals.PersonBirthYearFrom = vals.Person_Birthday_YearAge - 1;
						vals.PersonBirthYearTo = vals.Person_Birthday_YearAge;
					}
					else
					{
					//указан возраст
						vals.PersonBirthYearFrom = currentYear - vals.Person_Birthday_YearAge - 1;
						vals.PersonBirthYearTo = currentYear - vals.Person_Birthday_YearAge;
					}
				}*/
				searchPersonForm.getForm().reset();
				searchPersonForm.getForm().setValues(vals);
				
				/**
				Идея с передачей формы в окно как параметра мне оч не нравится, но переделывать времени нет
				Поэтому обойдёмся проверкой на наличие заполненных полей в форме
				По задаче необходимо обязательность наличия двух полей
				*/
				var searchVals = searchPersonForm.getForm().getValues();
				
				var notEmptyFieldsCount = 0; //Количество непустых полей в форме
				
				for (var key in searchVals) {
					notEmptyFieldsCount += ( searchVals.hasOwnProperty(key) && ( searchVals[key] != 0 ) && ( searchVals[key] != null ) );
				}
				win.initialConfig.storePerson.removeAll();
				log({notEmptyFieldsCount:notEmptyFieldsCount});
				if (notEmptyFieldsCount > 1) {
					win.searchPerson(searchPersonForm, win);
				}
				
			}
		});
		
		if (!conf.storePerson) {
			conf.storePerson = Ext.create('common.DispatcherCallWP.store.Person',{
				storeId: this.id + '_PersonStore'
			});
		}
		
		var searchPersonForm = Ext.create('sw.BaseForm', {
			id: false, // auto generate
			border: false,
			frame: true,
			layout: 'auto',
			bodyBorder: false,
			items: [
					{
						xtype: 'fieldset',
						collapsible: true,
						title: 'Пациент',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'textfield',
										flex: 1,
										fieldLabel: 'Фамилия',
										name: 'Person_Surname',
										labelAlign: 'top',
										mapping: '1212',
										plugins: [new Ux.Translit(true, true)],
										enableKeyEvents : true
									},
									{
										xtype: 'textfield',
										flex: 1,
										margins: '0 10',
										fieldLabel: 'Имя',
										name: 'Person_Firname',
										labelAlign: 'top',
										plugins: [new Ux.Translit(true, true)],
										enableKeyEvents : true
									},
									{
										xtype: 'textfield',
										flex: 1,
										fieldLabel: 'Отчество',
										name: 'Person_Secname',
										labelAlign: 'top',
										plugins: [new Ux.Translit(true, true)],
										enableKeyEvents : true
									}
								]
							},
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'datefield',
										margins: '0 20 0 0',
										width: 120,
										fieldLabel: 'Дата рождения',
										name: 'Person_Birthday',
										labelAlign: 'top',
										format: 'd.m.Y',
										plugins: [new Ux.InputTextMask('99.99.9999')],
										enableKeyEvents : true,
										listeners: {
											blur: function(cmp){
												if (!cmp.isValid()){
													cmp.reset()
												}
											}
										}
									},
									{
										xtype: 'numberfield',
										width: 120,
										fieldLabel: 'Возраст с',
										name: 'Person_Age_From',
										labelAlign: 'top',
										minValue: 0,
										maxValue: 120,
										enableKeyEvents: true
									},
									{
										xtype: 'numberfield',
										margins: '0 20 0 10',
										width: 120,
										fieldLabel: 'по',
										name: 'Person_Age_To',
										labelAlign: 'top',
										minValue: 0,
										maxValue: 120,
										enableKeyEvents: true
									},
									{
										xtype: 'numberfield',
										margins: '0 10 0 0',
										width: 120,
										fieldLabel: 'Год рождения с',
										name: 'PersonBirthYearFrom',
										labelAlign: 'top',
										minValue: 1900,
										enableKeyEvents: true
									},
									{
										xtype: 'numberfield',
										width: 120,
										fieldLabel: 'по',
										name: 'PersonBirthYearTo',
										labelAlign: 'top',
										minValue: 1900,
										enableKeyEvents: true
									}
								]
							},
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'numberfield',
										fieldLabel: 'ID пользователя',
										name: 'Person_id',
										labelAlign: 'top',
										enableKeyEvents: true
									},
									{
										xtype: 'numberfield',
										margins: '0 10',
										fieldLabel: 'СНИЛС',
										name: 'Person_Snils',
										labelAlign: 'top',
										minLength: 11,
										maxLength: 11,
										enforceMaxLength: true,
										enableKeyEvents : true
									}
								]
							}
						]
					},
					{
						xtype: 'fieldset',
						collapsible: true,
						collapsed: true,
						title: 'Полис',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'textfield',
										flex: 1,
										fieldLabel: 'Серия',
										name: 'Polis_Ser',
										labelAlign: 'top',
										maxLength: 12,
										enforceMaxLength: true,
										enableKeyEvents: true
									},
									{
										xtype: 'textfield',
										flex: 1,
										margins: '0 10',
										fieldLabel: 'Номер',
										name: 'Polis_Num',
										labelAlign: 'top',
										maxLength: 12,
										enforceMaxLength: true,
										maskRe: /\d/,
										enableKeyEvents: true
									},
									{
										xtype: 'textfield',
										flex: 1,
										fieldLabel: 'Единый номер',
										name: 'Polis_EdNum',
										labelAlign: 'top',
										maxLength: 12,
										enforceMaxLength: true,
										maskRe: /\d/,
										enableKeyEvents: true
									}
								]
							}
						]
					},
					{
						xtype: 'fieldset',
						collapsible: true,
						collapsed: true,
						title: 'Мед. документы',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'textfield',
										fieldLabel: 'Номер амб. карты',
										name: 'ambulance_cardNum',
										labelAlign: 'top',
										enableKeyEvents: true
									},
									{
										xtype: 'textfield',
										margins: '0 10',
										fieldLabel: 'Номер КВС',
										name: 'EvnPS_NumCard',
										labelAlign: 'top',
										enableKeyEvents: true
									}
								]
							}
						]
					},
					{
						xtype: 'fieldset',
						collapsible: true,
						collapsed: true,
						title: 'Удостоверения',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: [
							{
								xtype: 'container',
								flex: 1,
								margin: '0 10',
								layout: {
									type: 'hbox',
									align: 'stretch',
									padding: 5
								},
								items: [
									{
										xtype: 'textfield',
										fieldLabel: 'Серия',
										name: 'EvnUdost_Ser',
										labelAlign: 'top',
										enableKeyEvents: true
									},
									{
										xtype: 'textfield',
										margins: '0 10',
										fieldLabel: 'Номер',
										name: 'EvnUdost_Num',
										labelAlign: 'top',
										maskRe: /\d/,
										enableKeyEvents: true
									}
								]
							}
						]
					},
					{
						xtype: 'grid',
						maxHeight: 250,
						border: false,
						autoScroll: true,
						viewConfig: {
							loadingText: 'Загрузка',
							listeners:{
								itemkeydown:function(view, record, item, index, e){
									if ( (e.getKey()==13) )
									{
										win.down('button[refId=choosePatient]').handler();
									}
								}
							}
						},
        
    
						renderIcon: function(val) {
							if (val != 'false'){
								if (val=='true'){val='on'}
								return '<div class="x-grid3-check-'+val+' x-grid3-cc-ext-gen2118"></div>'
							}
							//return <div class="x-grid3-check-col-on-non-border-gray x-grid3-cc-ext-gen2121">&nbsp;</div>
						},
						columns: [
							{text: 'Фамилия',  dataIndex: 'PersonSurName_SurName', flex: 1},
							{text: 'Имя', dataIndex: 'PersonFirName_FirName', width: 80},
							{text: 'Отчество', dataIndex: 'PersonSecName_SecName', width: 100},
							{text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90},

							{text: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90},
							{text: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90},
							{text: 'Прикр. ДМС', dataIndex: 'PersonCard_IsDms', width: 70, renderer: function(value){return this.renderIcon(value);}},
							{text: 'БДЗ', dataIndex: 'Person_IsBDZ', width: 50, renderer: function(value){return this.renderIcon(value);}},
							{text: 'Фед. льг', dataIndex: 'Person_IsFedLgot', width: 70, renderer: function(value){return this.renderIcon(value);}},
							{text: 'Отказ', dataIndex: 'Person_IsRefuse', width: 50, renderer: function(value){return this.renderIcon(value);}},
							{text: 'Рег. льг', dataIndex: 'Person_IsRegLgot', width: 60, renderer: function(value){return this.renderIcon(value);}},
							{text: '7 ноз.', dataIndex: 'Person_Is7Noz', width: 50, renderer: function(value){return this.renderIcon(value);}}
						],
						store: conf.storePerson,
						listeners: {
							beforecellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts )
							{
								win.down('button[refId=choosePatient]').setDisabled(false);
							}.bind(this),
							itemdblclick: function(grid, record, item, index, e, eOpts) {
								win.down('button[refId=choosePatient]').handler();
							}
						}
					}
				],
				listeners: {
					render: function(p){
						// Обновление формы по нажатию Enter
						new Ext.util.KeyMap({
							target: p.body,
							key: Ext.EventObject.ENTER,
							fn: function(){
								win.searchPerson(searchPersonForm, win);
							}
						});						
					}
				}
			}
		)

		
        Ext.applyIf(win, {
            items: [
                 searchPersonForm
            ],
            dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',
                    layout: {
                        type: 'hbox',
                        align: 'stretch',
                        padding: 4
                    },
                    items: [
                        {
                            xtype: 'container',
                            layout: 'column',
                            items: [
								{
                                    xtype: 'button',
									iconCls: 'add16',
									margin: '0 5 0 0',
									text: 'Добавить',
									handler: function(){
										var w = Ext.create('sw.tools.subtools.swPersonEditWindow');
										w.show({
											forObject: win.forObject,
											afterTryAdd: function(form, result) {
												if (win.initialConfig.forObject == 'CmpCallCard' && result.Error_Msg == 'db_unable_to_connect') {
													win.initialConfig.caller.setPatient({
														Person_id: null,
														Person_IsUnknown: 1,
														PersonSurName_SurName: form.findField('Person_SurName').getValue(),
														PersonFirName_FirName: form.findField('Person_FirName').getValue(),
														PersonSecName_SecName: form.findField('Person_SecName').getValue(),
														PersonBirthDay_BirthDay: Ext.Date.format(form.findField('Person_BirthDay').getValue(), 'd.m.Y'),
														Sex_id: form.findField('PersonSex_id').getValue(),
														Polis_Num: form.findField('Polis_Num').getValue(),
														Polis_Ser: form.findField('Polis_Ser').getValue(),
														Polis_EdNum: form.findField('Federal_Num').getValue()
													});

													w.hide();
													win.hide();
												}
											},
											callback: function(data){
												var form = searchPersonForm.getForm();
												if ( data.PersonData ) {
													if (data.PersonData.Person_FirName) {
														form.findField('Person_Firname').setValue(data.PersonData.Person_FirName);
													}
													if (data.PersonData.Person_SurName) {
														form.findField('Person_Surname').setValue(data.PersonData.Person_SurName);
													}
													if (data.PersonData.Person_SecName) {
														form.findField('Person_Secname').setValue(data.PersonData.Person_SecName);
													}
													if (data.PersonData.Person_BirthDay) {
														form.findField('Person_Birthday').setValue(data.PersonData.Person_BirthDay);
													}
													if (data.PersonData.Person_Snils) {
														form.findField('Person_Snils').setValue(data.PersonData.Person_Snils);
													}
												}
												win.searchPerson(searchPersonForm, win);
												//win.onOkButtonClick(data);
											}
										});
									}
								},
								{
                                    xtype: 'button',
									margin: '0 5 0 0',
                                    text: 'Найти',
									iconCls: 'search16',
									handler: function(){
										win.searchPerson(searchPersonForm, win);
									}
                                },
								{
                                    xtype: 'button',
                                    text: 'Сброс',
									margin: '0 5 0 0',
									iconCls: 'resetsearch16',									
									handler: function(){
										var storePerson = win.initialConfig.storePerson;
										
										storePerson.removeAll();
										win.resetFields(searchPersonForm, win);
									}
                                },
								{
                                    xtype: 'button',
									refId: 'choosePatient',
                                    text: 'Выбрать',
									disabled: true,
									iconCls: 'ok16',
									handler: function(){
										var grd = win.down('grid'),
											selRec = grd.getSelectionModel().getSelection()[0],
											storePerson = win.initialConfig.storePerson;									
											/*
											все должно быть в каллбэке
											if (win.initialConfig.caller) {
												win.initialConfig.caller.clearPersonFields();										
												win.initialConfig.caller.setPatient(selRec.data);
												//win.initialConfig.caller.searchPerson();
											}
											*/
											if  (typeof win.initialConfig.callback == 'function') {
												win.initialConfig.callback(selRec.data);
												win.initialConfig.callback = Ext.emptyFn(); //Чтобы callback не вызывался дважды при обработке win.close
											}
//											
											//Передачу данных в callback повесил в обработчик события close
											win.close();
									}
                                }
                            ]
                        },
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'stretch',
                                pack: 'end'
                            },
                            items: [								
								{
									xtype: 'button',
									text: 'Помощь',
									margin: '0 5 0 0',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(this.up('window').title);
										//window.open('/wiki/main/wiki/Карта_вызова:_Добавление');										
									}
								},
								{
									xtype: 'button',
									//id: 'cancelEmergencyTeamDutyTimeGrid',
									iconCls: 'cancel16',
									text: 'Закрыть',
									handler: function(){
										win.close()
									}
								}
                            ]
                        }
                    ]
                }
            ]
        });

        win.callParent(arguments);
    },
	
	searchPerson: function(frm, win){
		var baseForm = frm.getForm(),		
			storePerson =  win.initialConfig.storePerson,
			allParams = baseForm.getFieldValues(),
			params = null;
	
		storePerson.clearFilter();
		
		allParams.searchMode = 'all';

		//ах если бы, ах если бы, не жизнь была б а песня бы
		storePerson.getProxy().extraParams = {
			Person_Snils: allParams.Person_Snils,
			Person_id: allParams.Person_id,
			PersonSurName_SurName : allParams.Person_Surname,
			PersonFirName_FirName : allParams.Person_Firname,
			PersonSecName_SecName : allParams.Person_Secname,
			PersonBirthDay_BirthDay: Ext.Date.format(allParams.Person_Birthday, 'd.m.Y'),
			PersonAge_AgeFrom: allParams.Person_Age_From,
			PersonAge_AgeTo: allParams.Person_Age_To,
			Polis_EdNum: allParams.Polis_EdNum,
			Polis_Num: allParams.Polis_Num,
			Polis_Ser: allParams.Polis_Ser,
			Sex_id: allParams.sexCombo,
			PersonBirthYearFrom: allParams.PersonBirthYearFrom,
			PersonBirthYearTo: allParams.PersonBirthYearTo,
			ambulance_cardNum: allParams.ambulance_cardNum,
			EvnPS_NumCard: allParams.EvnPS_NumCard,
			EvnUdost_Ser: allParams.EvnUdost_Ser,
			EvnUdost_Num: allParams.EvnUdost_Num,
			searchMode:'all'
		};	
		
		
		
		storePerson.load({
			callback: function(rec, operation, success) {
				if (success) {
					var grid = win.down('grid');
					if (grid.getStore().getCount()) {
						grid.getSelectionModel().select(grid.getStore().getAt(0));
					}
				}
				else {
					storePerson.removeAll();
					if (operation && operation.error && operation.error.status === 0) {
						Ext.Msg.alert('Ошибка', 'Запрос выполняется больше 30 секунд. Указано слишком мало параметров поиска. Пожалуйста, укажите больше параметров поиска');
					}
				}
			}.bind(this)
		});		
	},
	resetFields: function(frm, win){
		frm.getForm().reset();
	}

});
