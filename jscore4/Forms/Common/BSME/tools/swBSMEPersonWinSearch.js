Ext.define('common.BSME.tools.swBSMEPersonWinSearch', {
    extend: 'Ext.window.Window',
    minHeight: 500,
    width: 1000,
    layout: 'fit',
    title: 'Поиск',
	id: 'PersonSearchWin',
	cls: 'PersonSearchWin',
	modal: true,
	defaultFocus: 'textfield[name=Person_SurName]',
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
		
		if (!conf.storePerson) {
				conf.storePerson = Ext.create('common.DispatcherCallWP.store.Person',{
				storeId:win.id+'_PersonStore'
			})
		}
		
		win.searchPersonForm = Ext.create('sw.BaseForm', {
			id: 'searchPersonForm',
			border: false,
			frame: true,
			layout: 'auto',
			bodyBorder: false,
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
					defaults: {
						padding: '0 5 5 5'
					},
					items: [
						{
							xtype: 'textfield',
							flex: 1,
							fieldLabel: 'Фамилия',
							name: 'Person_SurName',
							allowBlank: true,
							labelAlign: 'top',
							plugins: [new Ux.Translit(true, true)],
							enableKeyEvents : true,
							listeners: {
								keypress: function(c, e, o){
									if ( (e.getKey()==13) )
									{
										win.searchPerson(win.searchPersonForm, win);
									}	
								}
							}
						},
						{
							xtype: 'textfield',
							flex: 1,
							//margins: '0 10',
							fieldLabel: 'Имя',
							name: 'Person_FirName',
							labelAlign: 'top',
							plugins: [new Ux.Translit(true, true)],
							enableKeyEvents : true,
							listeners: {
								keypress: function(c, e, o){
									if ( (e.getKey()==13) )
									{
										win.searchPerson(win.searchPersonForm, win);
									}	
								}
							}
						},
						{
							xtype: 'textfield',
							flex: 1,
							fieldLabel: 'Отчество',
							name: 'Person_SecName',
							labelAlign: 'top',
							plugins: [new Ux.Translit(true, true)],
							enableKeyEvents : true,
							listeners: {
								keypress: function(c, e, o){
									if ( (e.getKey()==13) )
									{
										win.searchPerson(win.searchPersonForm, win);
									}	
								}
							}
						},
						{
							xtype: 'datefield',
							margins: '0 20 0 0',
							width: 120,
							fieldLabel: 'Дата рождения',
							name: 'Person_BirthDay',
							labelAlign: 'top',
							format: 'd.m.Y',
							plugins: [new Ux.InputTextMask('99.99.9999')],
							enableKeyEvents : true,
							listeners: {
								blur: function(cmp){
									if (!cmp.isValid()){
										cmp.reset()
									}
								},
								keypress: function(c, e, o){
									if ( (e.getKey()==13) )
									{
										win.searchPerson(win.searchPersonForm, win);
									}	
								}
							}
						},{
							labelAlign: 'top',
							xtype: 'sexCombo',
							name: 'PersonSex_id'
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
					defaults: {
						padding: '0 5 5 5'
					},
					items: [
						{
							flex: 2,
							labelAlign: 'top',
//										triggerClear: false,
							xtype: 'swDocumentTypeCombo',
							name: 'DocumentType_id'
						},
						{
							flex: 1,
							xtype: 'textfield',
							fieldLabel: 'Серия',
							name: 'Document_Ser',
							labelAlign: 'top'
						},
						{
							flex: 1,
							xtype: 'textfield',
							//margins: '0 10',
							fieldLabel: 'Номер',
							name: 'Document_Num',
							labelAlign: 'top'
						}
					]
				},
				{
					xtype: 'container',
					hidden: true, 
					items: [
						{
							xtype: 'hidden',
							name: 'UAddress_Zip'
						}, {
							xtype: 'hidden',
							name: 'UKLCountry_id'
						}, {
							xtype: 'hidden',
							name: 'UKLRGN_id'
						}, {
							xtype: 'hidden',
							name: 'UKLSubRGN_id'
						}, {
							xtype: 'hidden',
							name: 'UKLCity_id'
						}, {
							xtype: 'hidden',
							name: 'UKLTown_id'
						}, {
							xtype: 'hidden',
							name: 'UKLStreet_id'
						}, {
							xtype: 'hidden',
							name: 'UAddress_House'
						}, {
							xtype: 'hidden',
							name: 'UAddress_Corpus'
						}, {
							xtype: 'hidden',
							name: 'UAddress_Flat'
						},{
							xtype: 'hidden'
						},{
							name: 'UAddress_begDate',
							xtype: 'hidden'
						},{
							xtype: 'hidden',
							name: 'PKLCountry_id'
						}, {
							xtype: 'hidden',
							name: 'PKLRGN_id'
						}, {
							xtype: 'hidden',
							name: 'PKLSubRGN_id'
						}, {
							xtype: 'hidden',
							name: 'PKLCity_id'
						}, {
							xtype: 'hidden',
							name: 'PKLTown_id'
						}, {
							xtype: 'hidden',
							name: 'PKLStreet_id'
						}, {
							xtype: 'hidden',
							name: 'PAddress_House'
						}, {
							xtype: 'hidden',
							name: 'PAddress_Corpus'
						}, {
							xtype: 'hidden',
							name: 'PAddress_Flat'
						}, {
							xtype: 'hidden',
							name: 'PAddress_Zip'
						}, {
							name: 'PAddress_begDate',
							xtype: 'hidden'
						},
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
					defaults: {
						padding: '0 5 5 5'
					},
					items: [
						{
							xtype: 'triggerfield',
							name: 'UAddress_Address',
							fieldLabel: 'Адрес проживания',
							labelAlign: 'top',
							validationEvent:false, 
							validateOnBlur:false, 
							trigger1Cls:'equal16', 
							trigger2Cls:'search16', 
							trigger3Cls:'x-form-clear-trigger',
							configObj: {
								'Address_Zip':'UAddress_Zip',
								'Country_id':'UKLCountry_id',
								'KLRegion_id':'UKLRGN_id',
								'KLSubRGN_id':'UKLSubRGN_id',
								'KLCity_id':'UKLCity_id',
								'KLTown_id':'UKLTown_id',
								'KLStreet_id':'UKLStreet_id',
								'Corpus':'UAddress_Corpus',
								'House':'UAddress_House',
								'Flat':'UAddress_Flat',
								'Address_begDate':'UAddress_begDate',
								'full_address': 'UAddress_Address'
							},
							onTrigger1Click : function() 
							{ 
								var key,
									copyFrom,
									copyTo,
									field = this,
									copyFromFieldConfig = win.searchPersonForm.getForm().findField('PAddress_Address').configObj || {},
									setFieldValue = function(name,value) {

										var field = win.searchPersonForm.getForm().findField(name);

										if (field) {
											field.setValue(value);
										}
									};
									var getFieldValue = function(name) {

										var field = win.searchPersonForm.getForm().findField(name);

										if (field) {
											return field.getValue();
										}
									}

								for (key in field.configObj) {
									if (field.configObj.hasOwnProperty(key) && copyFromFieldConfig.hasOwnProperty(key)) {

										copyFrom = copyFromFieldConfig[key];
										copyTo = field.configObj[key];

										setFieldValue(copyTo, getFieldValue(copyFrom));

									}
								}
							}, 
							onTrigger2Click : function() 
							{ 
								var field = this;
								Ext.create('common.tools.swAddressEditWindow',{
									callback: function(data){
										var key,
											setFieldValue = function(name,value) {
												var field = win.searchPersonForm.getForm().findField(name);
												if (field) {
													field.setValue(value);
												}

											};
										for (key in field.configObj) {
											if (field.configObj.hasOwnProperty(key) && data.hasOwnProperty(key) && data[key]) {
												setFieldValue(field.configObj[key],data[key]);
											};
										};

									}
								});
							},
							onTrigger3Click: function() {
								var field = this,
									key,
									setFieldValue = function(name,value) {
										var field = win.searchPersonForm.getForm().findField(name);
										if (field) {
											field.setValue(value);
										}
									};

								for (key in field.configObj) {
									if (field.configObj.hasOwnProperty(key) && field.configObj[key]) {
										setFieldValue(field.configObj[key],null);
									};
								};
							},
							flex: 1,
							enableKeyEvents : true
						}, 
						{
							xtype: 'triggerfield',
							name: 'PAddress_Address',
							fieldLabel: 'Адрес регистрации',
							labelAlign: 'top',
							validationEvent:false, 
							validateOnBlur:false, 
							trigger1Cls:'equal16', 
							trigger2Cls:'search16', 
							trigger3Cls:'x-form-clear-trigger',
							configObj: {
								'Address_Zip':'PAddress_Zip',
								'Country_id':'PKLCountry_id',
								'KLRegion_id':'PKLRGN_id',
								'KLSubRGN_id':'PKLSubRGN_id',
								'KLCity_id':'PKLCity_id',
								'KLTown_id':'PKLTown_id',
								'KLStreet_id':'PKLStreet_id',
								'Corpus':'PAddress_Corpus',
								'House':'PAddress_House',
								'Flat':'PAddress_Flat',
								'Address_begDate':'PAddress_begDate',
								'full_address': 'PAddress_Address'
							},
							onTrigger1Click : function() 
							{ 
								var key,
									copyFrom,
									copyTo,
									field = this,
									copyFromFieldConfig = win.searchPersonForm.getForm().findField('UAddress_Address').configObj || {},
									setFieldValue = function(name,value) {

										var field = win.searchPersonForm.getForm().findField(name);

										if (field) {
											field.setValue(value);
										}
									};
									var getFieldValue = function(name) {

										var field = win.searchPersonForm.getForm().findField(name);

										if (field) {
											return field.getValue();
										}
									}

								for (key in field.configObj) {
									if (field.configObj.hasOwnProperty(key) && copyFromFieldConfig.hasOwnProperty(key)) {

										copyFrom = copyFromFieldConfig[key];
										copyTo = field.configObj[key];

										setFieldValue(copyTo, getFieldValue(copyFrom));

									}
								}
							}, 
							onTrigger2Click : function() 
							{ 
								var field = this;
								Ext.create('common.tools.swAddressEditWindow',{
									callback: function(data){
										var key,
											setFieldValue = function(name,value) {
												var field = win.searchPersonForm.getForm().findField(name);
												if (field) {
													field.setValue(value);
												}
											};

										for (key in field.configObj) {
											if (field.configObj.hasOwnProperty(key) && data.hasOwnProperty(key) && data[key]) {
												setFieldValue(field.configObj[key],data[key]);
											};
										};

									}
								});
							},
							onTrigger3Click: function() {
								var field = this,
									key,
									setFieldValue = function(name,value) {
										var field = win.searchPersonForm.getForm().findField(name);
										if (field) {
											field.setValue(value);
										}
									};

								for (key in field.configObj) {
									if (field.configObj.hasOwnProperty(key) && field.configObj[key]) {
										setFieldValue(field.configObj[key],null);
									};
								};
							},
							flex: 1,
							enableKeyEvents : true
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
							{ text: 'ID',  dataIndex: 'Person_id', width: 60, hidden: true, hideable: false },
							{ text: 'Фамилия',  dataIndex: 'PersonSurName_SurName', flex: 1 },
							{ text: 'Имя', dataIndex: 'PersonFirName_FirName', flex: 1 },
							{ text: 'Отчество', dataIndex: 'PersonSecName_SecName', flex: 1 },
							{ text: 'Дата рождения', dataIndex: 'PersonBirthDay_BirthDay', width: 90 },

//							{ text: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90, renderer: function(value){return this.renderIcon(value);}},
//							{ text: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90 },
//							{ text: 'Прикр. ДМС', dataIndex: 'PersonCard_IsDms', width: 70, renderer: function(value){return this.renderIcon(value);}},
//							{ text: 'БДЗ', dataIndex: 'Person_IsBDZ', width: 50, renderer: function(value){return this.renderIcon(value);}},
//							{ text: 'Фед. льг', dataIndex: 'Person_IsFedLgot', width: 70, renderer: function(value){return this.renderIcon(value);}},
//							{ text: 'Отказ', dataIndex: 'Person_IsRefuse', width: 50, renderer: function(value){return this.renderIcon(value);}},
//							{ text: 'Рег. льг', dataIndex: 'Person_IsRegLgot', width: 60, renderer: function(value){return this.renderIcon(value);}},
//							{ text: '7 ноз.', dataIndex: 'Person_Is7Noz', width: 50, renderer: function(value){return this.renderIcon(value);}},
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
				]
			}
		)
		

        Ext.applyIf(win, {
            items: [
                 win.searchPersonForm
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

										var values = win.searchPersonForm.getForm().getValues(),
											form = win.searchPersonForm.getForm(),
											addTriggerFieldValues = function(name){
												var key,
													triggerField = form.findField(name) || {},
													configObj = triggerField.configObj || {};

												for (key in configObj) {
													if (configObj.hasOwnProperty(key)) {

														values[ configObj[ key ] ] = form.findField(configObj[ key ]).getValue();

													}
												}

											}
										
										//По условию обязательным является только поле фамилии
										if (!values.Person_SurName) {
											Ext.Msg.alert('Ошибка','Поле Фамилия является обязательным')
											win.searchPersonForm.getForm().findField('Person_SurName').focus();
											return false;
										}


										addTriggerFieldValues('PAddress_Address');
										addTriggerFieldValues('UAddress_Address');


										Ext.MessageBox.confirm('','Вы уверены, что хотите добавить человека?',function(btn){
											if( btn === 'yes' ){
												
												Ext.Ajax.request({
													url: '/?c=Person&m=savePersonEditWindow',
													params: values,
													callback: function(opt, success, response) {
														if (success && response.responseText != '') {
															var response_obj = Ext.JSON.decode(response.responseText);
															if (!response_obj.success) {
																Ext.Msg.alert('Ошибка',response_obj.Error_Msg||'При сохранении раздела произошла ошибка. Обратитесь к администратору');
															} else {
																win.initialConfig.callback({
																	'PersonSurName_SurName': form.findField('Person_SurName').getValue(),
																	'PersonSecName_SecName': form.findField('Person_SecName').getValue(),
																	'PersonFirName_FirName': form.findField('Person_FirName').getValue(),
																	'Person_id': response_obj.Person_id
																});
																win.close();
															}
														}
													}
												});
											}
										},win);

									}
								},
								{
                                    xtype: 'button',
									margin: '0 5 0 0',
                                    text: 'Найти',
									iconCls: 'search16',
									handler: function(){
										win.searchPerson(win.searchPersonForm, win);
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
										win.resetFields(win.searchPersonForm, win);
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
											
											if (win.initialConfig.caller) {
												win.initialConfig.caller.clearPersonFields();										
												win.initialConfig.caller.setPatient(selRec.data);
												win.initialConfig.caller.searchPerson();
											}
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
									//id: 'helpEmergencyTeamDutyTimeGrid',
									text: 'Помощь',
									margin: '0 5 0 0',
									iconCls   : 'help16',
									handler   : function()
									{
										//ShowHelp(this.ownerCt.title);
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
//			Person_Snils: allParams.Person_Snils,
//			Person_id: allParams.Person_id,
			PersonSurName_SurName : allParams.Person_SurName,
			PersonFirName_FirName : allParams.Person_FirName,
			PersonSecName_SecName : allParams.Person_SecName,
			PersonBirthDay_BirthDay: Ext.Date.format(allParams.Person_BirthDay, 'd.m.Y'),
//			PersonAge_AgeFrom: allParams.Person_Age_From,
//			PersonAge_AgeTo: allParams.Person_Age_To,
//			Polis_EdNum: allParams.Polis_EdNum,
//			Polis_Num: allParams.Polis_Num,
//			Polis_Ser: allParams.Polis_Ser,
//			Sex_id: allParams.sexCombo,
//			PersonBirthYearFrom: allParams.PersonBirthYearFrom,
//			PersonBirthYearTo: allParams.PersonBirthYearTo,
//			ambulance_cardNum: allParams.ambulance_cardNum,
//			EvnPS_NumCard: allParams.EvnPS_NumCard,

			DocumentType_id: allParams.DocumentType_id,
			Document_Num: allParams.Document_Num,
			Document_Ser: allParams.Document_Ser,
			
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
				}
			}.bind(this)
		});		
	},
	resetFields: function(frm, win){
		frm.getForm().reset();
	}

});
