/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
* Спецификация Казахстана
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      21.01.2013
*/

sw.Promed.swCmpCallCardNewCloseCardWindow = Ext.extend(sw.Promed.swMainCloseCardWindow,{
	objectName: 'swCmpCallCardNewCloseCardWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardNewCloseCardWindow.js',
	cls: 'swCmpCallCardNewCloseCardWindow',

	initComponent: function(){
		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function(){
		sw.Promed.swCmpCallCardNewCloseCardWindow.superclass.show.apply(this, arguments);
	},

	initActions: function(){

		var me = this;

		me.panelNumber = 0;

		me.initRadiosAndChecks();

		me.buttons = [
			{
				handler: function() {
					me.doSave();
				},
				iconCls: 'save16',
				onTabAction: function() {
					me.buttons[me.buttons.length - 1].focus();
				},
				text: 'Сохранить'
			},
			{
				text: '-'
			},
				HelpButton(me, -1),
			{
				handler: function() {
					me.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					if ( me.action != 'view' ) {
						me.buttons[0].focus();
					}
				},
				onTabAction: function() {
					if ( !me.FormPanel.getForm().findField('Person_Surname').disabled ) {
						me.FormPanel.getForm().findField('Person_Surname').focus(true);
					}
				},
				text: BTN_FRMCANCEL
			}
		];

		me.tabPanel = new Ext.TabPanel({
			name: 'CMPCLOSE_TabPanel',
			activeTab: 0,
			deferredRender: false,
			cls: 'x-tab-panel-autoscroll',
			defaults: {	border: false },
			items: [
				{
					//title: '<b>1.</b> Общие сведения',
					title: lang['1_obschie_svedeniya'],
					itemId: 'CMPCLOSE_TabPanel_FirstShowedTab',
					items: me.getCommonGroupFields(),
					autoHeight: true
				},
				{
					//title: '<b>2.</b> Экспертная оценка',
					title: lang['2_ekspertnaya_otsenka'],
					items: me.getExpertMarkFields(),
					autoHeight: true
				},
				{
					//title: '<b>3.</b> Объективные данные',
					title: lang['3_obyektivnyie_dannyie'],
					items: me.getJalobFields(),
					autoHeight: true
				}
			]
		});

		me.hiddenItems = [
			{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'AgeType_id2',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'SocStatusNick',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCallCard_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'ARMType',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_id',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'CmpCloseCard_Street',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_deadDT',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'Person_IsUnknown',
				value: '',
				xtype: 'hidden'
			},
			{
				name: 'PersonFields_IsDirty',
				value: false,
				xtype: 'hidden'
			}
		];

		var flds = [];

		for(var i = 0 ; i < me.hiddenItems.length; i++){
			flds.push({'name': me.hiddenItems[i].name});
		}

		me.tabPanel.findBy(function(a,b,c){
			var name = a.hiddenName || a.name;
			if(name){
				flds.push({'name': name});
			}
		});

		me.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 220,
			layout: 'fit',
			region: 'center',
			reader: new Ext.data.JsonReader( {success: Ext.emptyFn}, flds ),
			url: '/?c=CmpCallCard&m=saveCmpCloseCard110',
			items: [
				{xtype: 'container', items: me.hiddenItems, autoEl: {}, hidden: true},
				me.tabPanel
			]
		});
	},

	//поля общие сведения
	getCommonGroupFields: function(){
		var me = this;
		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth : 400,
				items      : [
					{
						xtype      : 'fieldset',
						autoHeight : true,
						labelWidth : 400,
						items : [
							{
								fieldLabel: lang['nomer_kartyi'],
								name: 'CardNum',
								xtype: 'textfield',
								labelWidth : 400
							},
							{								
								name: 'Day_num',
								xtype: 'hidden',
								
							}, 
							{								
								name: 'Year_num',
								xtype: 'hidden'								
							}
						]
					},
					{
						title: ++me.panelNumber + '. ' + lang['vremya'],
						id : 'timeBlock',
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								//dateLabel: 'Приема вызова',
								dateLabel: lang['priema_vyizova'],
								hiddenName: 'AcceptTime',
								hiddenId: me.getId() + '-AcceptTime',
								allowBlank: false,
								onChange: function(field, newValue){
									var base_form = me.FormPanel.getForm(),
										diagField = base_form.findField('Diag_id'),
										date = new Date(newValue);

									if (me.action != 'view' && diagField) {
										base_form.findField('Diag_id').setFilterByDate(date);
									}
									if (me.action != 'view') {
										base_form.findField('CallType_id').setFilterByDate(date);
									}

									// проверка на уникальность введенного номера вызова за день и за год
									if( newValue && me.action == 'stream' ) me.existenceNumbersDayYear();
								},
								onTriggerClick: Ext.emptyFn,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['peredachi_vyizova_brigade_smp'],
								hiddenName: 'TransTime',
								hiddenId: me.getId() + '-TransTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['vyiezda_na_vyizov'],
								hiddenName: 'GoTime',
								hiddenId: me.getId() + '-GoTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['pribyitiya_na_mesto_vyizova'],
								hiddenName: 'ArriveTime',
								hiddenId: me.getId() + '-ArriveTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['pribyitiya_v_meditsinskuyu_organizatsiyu'],
								hiddenName: 'ToHospitalTime',
								hiddenId: me.getId() + '-ToHospitalTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['okonchaniya_vyizova'],
								hiddenName: 'EndTime',
								hiddenId: me.getId() + '-EndTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: lang['sleduyuschiy_vyizov'],
								hiddenName: 'NextTime',
								hiddenId: me.getId() + '-NextTime',
								labelWidth : 400,
								xtype: 'swdatetimefield'
							}
						]
					},
					{
						title : ++me.panelNumber + '. ' + lang['svedeniya_o_bolnom'],
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								layout: 'column',
								items :[
									{
										border: false,
										layout: 'form',
										items : [{
											handler: function() { me.personSearch(); },
											iconCls: 'search16',
											id: 'CCCSEF_PersonSearchBtn',
											text:  lang['poisk'],
											xtype: 'button'
										},
										{
											handler: function() { me.personReset(); },
											iconCls: 'reset16',
											id: 'CCCSEF_PersonResetBtn',
											text: lang['sbros'],
											xtype: 'button'
										},
										{
											handler: function() { me.personUnknown(); },
											iconCls: 'reset16',
											id: 'CCCSEF_PersonUnknownBtn',
											text: lang['neizvesten'],
											xtype: 'button'
										}]
									},
									{
										border: false,
										layout: 'form',
										labelWidth : 295,
										items : [
											{
												fieldLabel: lang['familiya'],
												disabled: true,
												name: 'Fam',
												toUpperCase: true,
												width: 180,
												toUpperCase: true,
												xtype: 'textfieldpmw'
											},
											{
												fieldLabel: lang['imya'],
												disabled: true,
												name: 'Name',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw'
											},
											{
												fieldLabel: lang['otchestvo'],
												disabled: true,
												name: 'Middle',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw'
											},
											{
												name: 'Birthday',
												maxValue: (new Date()),
												plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
												listeners: {
													change: function() {
														me.showAgeLabel();
													}
												},
												fieldLabel: lang['data_rojdeniya'],
												xtype: 'swdatefield'
											},
											{
												text: '',
												name: 'AgeText',
												id: this.id+'AgeText',
												width: 280,
												style:'padding-left:300px; font-weight: bold;',
												xtype: 'label'
											},
											{
												name: 'Age',
												hidden:true,
												hideLabel: true,
												xtype: 'numberfield'
											},
											{
												comboSubject: 'Sex',
												disabledClass: 'field-disabled',
												fieldLabel: lang['pol'],
												hiddenName: 'Sex_id',
												allowBlank: false,
												width: 130,
												xtype: 'swcommonsprcombo'
											}
										]
									}
								]
							},

						]
					},
					{
						title : ++me.panelNumber + '. ' + lang['adres_vyizova'],
						xtype : 'fieldset',
						id : 'addressBlock',
						autoHeight: true,
						items : [
							{
								enableKeyEvents: true,
								disabled: false,
								hiddenName: 'KLAreaStat_idEdit',
								listeners: {
									beforeselect: function(combo, record) {
										if ( typeof record != 'undefined' ) {
											if( record.get('KLAreaStat_id') == '' ) {
												combo.onClearValue();
												return;
											}

											var base_form = this.FormPanel.getForm();
											base_form.findField('Area_id').reset();
											base_form.findField('City_id').reset();
											base_form.findField('Town_id').reset();
											base_form.findField('Street_id').reset();

											if( record.get('KLRGN_id') ){
												base_form.findField('KLRgn_id').setValue(record.get('KLRGN_id'));
											}

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('Area_id').setValue(record.get('KLSubRGN_id'));
												base_form.findField('Area_id').getStore().removeAll();
												base_form.findField('Area_id').getStore().load({
													params: {region_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('SubRGN_id') == this.getValue();}.createDelegate(this))));
													}.createDelegate(base_form.findField('Area_id'))
												});
											} else if( record.get('KLCity_id') != '' ) {
												base_form.findField('City_id').setValue(record.get('KLCity_id'));
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({
													params: {subregion_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) {return rec.get('City_id') == this.getValue();}.createDelegate(this))));
													}.createDelegate(base_form.findField('City_id'))
												});
											}
											//KLTown_id
										}
									}.createDelegate(this)
								},
								onClearValue: function() {
									var base_form = this.FormPanel.getForm();
									base_form.findField('KLAreaStat_idEdit').clearValue();
									base_form.findField('Area_id').enable();
									base_form.findField('City_id').enable();
									base_form.findField('Town_id').enable();
									base_form.findField('Town_id').reset();
									base_form.findField('Town_id').getStore().removeAll();
									base_form.findField('Street_id').enable();
									base_form.findField('Street_id').reset();
								}.createDelegate(this),
								width: 180,
								xtype: 'swklareastatcombo'
							},
							{
								name: 'KLRgn_id',
								value: 0,
								xtype: 'hidden'
							},
							{
								//disabled: true,
								enableKeyEvents: true,
								fieldLabel: lang['rayon'],
								hiddenName: 'Area_id',
								width: 180,
								listeners: {
									'beforeselect': function(combo, record) {

										var base_form = this.FormPanel.getForm(),
											streetCombo = base_form.findField('Street_id');

										combo.setValue(record.get(combo.valueField));

										if( record.get('SubRGN_id') > 0 ) {
											base_form.findField('City_id').reset();
											base_form.findField('City_id').getStore().removeAll();
											base_form.findField('City_id').getStore().load({params: {subregion_id: record.get('SubRGN_id')}});
											base_form.findField('Town_id').getStore().removeAll();
											base_form.findField('Town_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});
											streetCombo.getStore().removeAll();
											streetCombo.reset();
											streetCombo.getStore().load({params: {town_id: record.get('SubRGN_id')}});
										}
									}.createDelegate(this)
								},
								xtype: 'swsubrgncombo'
							},
							{
								hiddenName: 'City_id',
								//disabled: true,
								name: 'City_id',
								width: 180,
								xtype: 'swcitycombo',
								listeners: {
									'beforeselect': function(combo, record) {

										var base_form = this.FormPanel.getForm(),
											streetCombo = base_form.findField('Street_id');

										combo.setValue(record.get(combo.valueField));

										if( record.get('City_id') > 0 ) {

											base_form.findField('Town_id').getStore().removeAll();
											base_form.findField('Town_id').getStore().load({params: {city_id: record.get('City_id')}});
											streetCombo.reset();
											streetCombo.getStore().removeAll();
											streetCombo.getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}});
										}
									}.createDelegate(this)
								}
							},
							{
								//disabled: true,
								enableKeyEvents: true,
								listeners: {
									beforeselect: function(combo, record) {
										if ( typeof record != 'undefined' ) { combo.setValue(record.get(combo.valueField));	}

										var base_form = this.FormPanel.getForm(),
											streetCombo = base_form.findField('Street_id');

										combo.setValue(record.get(combo.valueField));

										streetCombo.reset();
										streetCombo.getStore().removeAll();
										streetCombo.getStore().load({
											params: {town_id: combo.getValue()}
										});
									}.createDelegate(this)
								},
								minChars: 0,
								hiddenName: 'Town_id',
								name: 'Town_id',
								width: 250,
								xtype: 'swtowncombo'
							},
							{
								disabled: true,
								xtype: 'swstreetcombo',
								fieldLabel: lang['ulitsa'],
								hiddenName: 'Street_id',
								name: 'Street_id',
								width: 250,
								editable: true
							},
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['dom'],
								name: 'House',
								width: 100,
								xtype: 'textfield'
							}, {
								disabledClass: 'field-disabled',
								fieldLabel: lang['kvartira'],
								maxLength: 5,
								autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
								//maskRe: /^([а-яА-Я0-9]{1,5})$/,
								name: 'Office',
								width: 100,
								xtype: 'textfieldpmw'
							}
						]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								xtype: 'textfield',
								width: 200,
								name: 'Work',
								fieldLabel: lang['mesto_rabotyi']
							},
							{
								fieldLabel	   : lang['tip_vyizova'],
								hiddenName: 'CallType_id',
								xtype: 'swcmpcalltypecombo',
								width: 300,
								listWidth: 300
							},
							{
								dateLabel: lang['data_obslujivaniya'],
								hiddenName: 'ServiceDT',
								xtype: 'swdatetimefield'
							},
							{
								fieldLabel: lang['nomer_brigadyi'],
								width: 200,
								name: 'EmergencyTeamNum',
								xtype: 'textfield'
							},
							{
								fieldLabel: lang['vrach'],
								width: 200,
								name: 'Doctor',
								xtype: 'textfield'
							},
							{
								fieldLabel: lang['feldsher'],
								width: 200,
								name: 'Feldsher',
								xtype: 'textfield'
							},
							{
								fieldLabel: lang['voditel'],
								width: 200,
								name: 'Driver',
								xtype: 'textfield'
							},
							{
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
							},
							{
								xtype: 'swsmpunitscombo',
								fieldLabel: 'Станция (подстанция), отделения',
								hiddenName:'LpuBuilding_id',
								disabledClass: 'field-disabled',
								width: 350,
								allowBlank: true,
								listWidth: 300,
								hidden: true,
								listeners: {
									beforeselect: function (combo,record,index) {
										var base_form = me.FormPanel.getForm();
										// форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова
										var idLpuBuilding = combo.getValue();
										var newLpuBuilding = record.get("LpuBuilding_id");
										var LBIsWithoutBalance = base_form.findField('LpuBuilding_IsWithoutBalance').getValue();
										Ext.Ajax.request({
											params: {LpuBuilding_id: record.get("LpuBuilding_id")},
											url: '/?c=LpuStructure&m=getLpuBuildingData',
											callback: function (obj, success, response) {
												if (success) {
													var response_obj = Ext.util.JSON.decode(response.responseText);
													//если в настройках пришел LpuBuilding_IsWithoutBalance
													if(response_obj[0] && response_obj[0].LpuBuilding_IsWithoutBalance){
														if(LBIsWithoutBalance != response_obj[0].LpuBuilding_IsWithoutBalance)
														{
															base_form.findField('LpuBuilding_IsWithoutBalance').setValue(response_obj[0].LpuBuilding_IsWithoutBalance);
															var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6;
															var tabMed=me.tabPanel.getItem(indTab);
															var view_frame;

															if(LBIsWithoutBalance != '') {

																view_frame = Ext.getCmp('CCCNCC_CmpCallCardEvnDrugGrid');
																if(Ext.isEmpty(view_frame))
																	view_frame = Ext.getCmp('CCCNCC_CmpCallCardDrugGrid');
																var store = view_frame.getGrid().getStore(),
																	grid = view_frame.getGrid();
																if(store.getCount() > 0) {
																	sw.swMsg.show({
																		icon: Ext.MessageBox.QUESTION,
																		msg: lang['izmenenie_podstancii_udalit_medicamenty'],
																		title: lang['podtverjdenie'],
																		buttons: Ext.Msg.YESNO,
																		fn: function (buttonId, text, obj) {
																			var dataDrug = new Array();
																			if ('yes' == buttonId) {
																				if(me.action == 'stream'){
																					store.each(function (rec) {
																						grid.getStore().remove(rec);
																					});
																					tabMed.removeAll();
																					tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																					me.tabPanel.doLayout();
																					combo.setValue(newLpuBuilding);
																					return false;
																				}
																				var CmpCallCard_id = me.FormPanel.getForm().findField('CmpCallCard_id').getValue();
																				if (!(CmpCallCard_id > 0)) {
																					Ext.Msg.alert('Карта не определена');
																					return false;
																				}
																				if (Ext.isEmpty(LBIsWithoutBalance)) {
																					Ext.Msg.alert('Не удается определить параметр учета остатков на складе');
																					return false;
																				}
																				store.each(function (rec) {
																					if (rec.get('state') == 'add') {
																						grid.getStore().remove(rec);
																					}
																					else {
																						rec.set('state', 'delete');
																						rec.commit();
																						dataDrug.push(rec.data);
																					}
																				});
																				// view_frame.setFilter(); интересно, зачем это?
																				var drugGridJsonData = dataDrug.length > 0 ? Ext.util.JSON.encode(dataDrug) : "";
																				Ext.Ajax.request({
																					params: {
																						CmpCallCard_id: CmpCallCard_id,
																						CmpCallCardDrugJSON: drugGridJsonData,
																						LpuBuilding_id: idLpuBuilding,
																						LpuBuilding_IsWithoutBalance: LBIsWithoutBalance
																					},
																					url: '/?c=CmpCallCard&m=deleteCmpCallCardEvnDrug',
																					callback: function (obj, success, response) {
																						if (success) {
																							var response_obj = Ext.util.JSON.decode(response.responseText);
																							tabMed.removeAll();
																							tabMed.add({items: me.getDrugFields(idLpuBuilding)});
																							me.tabPanel.doLayout();
																							combo.setValue(newLpuBuilding);

																						}
																					}
																				});
																			} else {
																				base_form.findField('LpuBuilding_IsWithoutBalance').setValue(LBIsWithoutBalance);
																				combo.setValue(idLpuBuilding);
																				return false
																			}
																		}
																	});
																}
																else {
																	combo.setValue(newLpuBuilding);
																	if (tabMed) {
																		tabMed.removeAll();
																		tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																		me.tabPanel.doLayout();
																	}
																}
															}
															else {
																if (tabMed) {
																	tabMed.removeAll();
																	tabMed.add({items: me.getDrugFields(newLpuBuilding)});
																	me.tabPanel.doLayout();
																}
															}
															combo.setValue(newLpuBuilding);
														}
														else{
															combo.setValue(newLpuBuilding);
														}

													}
												}
											}
										});
									}
								}
							},
							{
								comboSubject: 'CmpReason',
								disabledClass: 'field-disabled',
								fieldLabel: lang['povod'],
								hiddenName: 'Reason_id',
								id: this.id+'idCallPovod_id',
								width: 350,
								listWidth: 300,
								xtype: 'swcommonsprcombo',
								listeners: {
									change: function(cmp, newVal){
										var base_form = me.FormPanel.getForm(),
											diagField = base_form.findField('Diag_id'),
											reasonCode = this.store.getById(newVal).get('CmpReason_Code');
										//если повод - "ошибка" то делаем поле результат выезда необязательным и диагноз
										//иначе - обязательный
										if(reasonCode.inlist(['01!'])){
											diagField.allowBlank = true;
										}
										else{
											diagField.allowBlank = false;
										}
										diagField.validate();
									}
								}
							},
							{
								fieldLabel: lang['alkogol'],
								hiddenName: 'isAlco',
								width: 100,
								comboSubject: 'YesNo',
								xtype: 'swcommonsprcombo'
							}
						]
					}
				]
			}
		]
	},

	//поля экспертная оценка
	getExpertMarkFields: function(){
		var me = this;
		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['etap_ekspertizyi'],
					name: 'ExpEtap',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['starshiy_vrach'],
					name: 'ExpDoctor',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['zav_otdeleniem'],
					name: 'ExpZav',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['zam_glavnogo_vracha'],
					name: 'ExpGlav',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['jalobyi'],
					name: 'Complaints',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['anamnez_nastoyaschego_zabolevaniya'],
					name: 'Anamnez',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: lang['anamnez_jizni'],
					name: 'AnamnezLife',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
					fieldLabel: 'Status Localis',
					name: 'LocalStatus',
					width: '90%',
					xtype: 'textarea'
				}]
			}
		]
	},
	
	//получение списка компонентов для вкладки Объективные данные
	getJalobFields: function(){
		var me = this;

		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [
					{
					layout	   : 'column',
					items: [
						{
							xtype      : 'panel',
							title	   : lang['obschee_sostoyanie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'radiogroup',
									items: this.getCombo('Condition_id')
							}]						
						},
						{
							xtype      : 'panel',
							title	   : lang['soznanie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
									columns: 2,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Cons_id')
							}]						
						},
						{
							xtype      : 'panel',
							title	   : lang['povedenie'],
							frame	   : true,
							width : '25%',
							height : 200,							
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Behavior_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['zrachki'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Pupil_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['reaktsiya_na_svet'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Light_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['anizokariya'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Aniz_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['kojnyie_pokrovyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 2,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Kozha_id')
							}]	
						},						
						{
							xtype      : 'panel',
							title	   : lang['tonyi_serdtsa'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Heart_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['shumyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Noise_id')
							}]	
						},
						{
							xtype      : 'panel',
							title	   : lang['puls'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Pulse_id')
							}]	
						},
						{
							xtype      : 'panel',
							title	   : lang['ekskursiya_grudnoy_kletki'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 2,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Exkurs_id')
							}]	
						}, {
							xtype      : 'panel',
							title	   : lang['dyihanie_auskult'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Hale_id')
							}]	
						}, {
							xtype      : 'panel',
							title	   : lang['hripyi'],
							frame	   : true,						
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Rattle_id')
							}]	
						},					
						{
							xtype      : 'panel',
							title	   : lang['odyishka'],
							frame	   : true,						
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Shortwind_id')
							}]	
						},					
						{
							xtype      : 'panel',
							title	   : lang['nevrologicheskiy_status'],
							frame	   : true,						
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Nev_id')
							}]	
						},					
						{
							xtype      : 'panel',
							title	   : lang['meningealnyie_simptomyi'],
							frame	   : true,						
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Menen_id')
							}]	
						},					
						{
							xtype      : 'panel',
							title	   : lang['glaznyie_yabloki'],
							frame	   : true,						
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Eye_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['chmn'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 2,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Chmn_id')
							}]	
						}, 
						{
							xtype      : 'panel',
							title	   : lang['suhojilnyie_refleksyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 2,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Reflex_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['dvigatelna_sfera'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 2,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Move_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['bolevaya_chuvstvitelnost'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Bol_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['afaziya'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Afaz_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['s_babinskogo'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Sbabin_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['s_oppengeyma'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Soppen_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['zev'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Zev_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['mindalinyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Mindal_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['yazyik'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Lang_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['jivot'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Gaste_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['simptomyi'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Sympt_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['pechen'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Liver_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['selezenka'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Selez_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['mocheotdelenie'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Moch_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['menstrualnyiy_tsikl'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								items: this.getCombo('Menst_id')
							}]
						},
						{
							xtype      : 'panel',
							title	   : lang['perifericheskie_oteki'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Per_id')
							}]
						},						
						{
							xtype      : 'panel',
							title	   : lang['rezultat_lecheniya'],
							frame	   : true,
							width : '25%',
							height : 200,
							items : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'radiogroup',
								items: this.getCombo('Result_id')
							}]
						}
						]
					}, {
						height: 20				
					}, 
					{
						layout: 'column',
						items :[{
							xtype      : 'fieldset',
							title	   : lang['do_prinyatiya'],
							frame	   : false,
							width : '25%',
							height : 250,
							items : [{
									fieldLabel: lang['stul'],
									name: 'Shit',
									xtype: 'textfield'
							}, {
									fieldLabel: lang['chdd'],
									name: 'Chd',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3	
							}, {
									fieldLabel: 't º  С',
									name: 'Temperature',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ],
									xtype: 'textfield'
							}, {
									fieldLabel: lang['puls'],
									name: 'Pulse',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3							
							}, {
									fieldLabel: lang['chss'],
									name: 'Chss',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3							
							}, {
									fieldLabel: lang['ad'],
									name: 'AD',
									xtype: 'textfield',
									maskRe: /\d|\//
									//plugins: [ new Ext.ux.InputTextMask('999/999', true) ]
							}, {
									fieldLabel: lang['prav'],
									name: 'WorkAD',
									xtype: 'textfield'
							}, {
									fieldLabel: 'SaO',
									name: 'SaO',
									xtype: 'textfield'
							}, {
									fieldLabel: lang['sahar_krovi'],
									name: 'Gluck',
									xtype: 'textfield',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ]									
							}]						
						}, {
							xtype      : 'fieldset',
							title	   : lang['posle_prinyatiya'],
							frame	   : false,
							width : '25%',
							height : 250,
							items : [{							
									fieldLabel: lang['stul'],
									name: 'AfterShit',
									xtype: 'textfield'
							}, {
									fieldLabel: lang['chdd'],
									name: 'AfterChd',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3	
							}, {
									fieldLabel: 't º  С',
									name: 'AfterTemperature',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ],
									xtype: 'textfield'
							}, {
									fieldLabel: lang['puls'],
									name: 'AfterPulse',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3							
							}, {
									fieldLabel: lang['chss'],
									name: 'AfterChss',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength:3							
							}, {
									fieldLabel: lang['ad'],
									name: 'AfterAD',
									xtype: 'textfield',
									maskRe: /\d|\//
									//plugins: [ new Ext.ux.InputTextMask('999/999', true) ]
							}, {
									fieldLabel: lang['prav'],
									name: 'AfterWorkAD',
									xtype: 'textfield'
							}, {
									fieldLabel: 'SaO',
									name: 'AfterSaO',
									xtype: 'textfield'
							}, {
									fieldLabel: lang['sahar_krovi'],
									name: 'AfterGluck',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ],
									xtype: 'textfield'
							}]						
						}]
					}, {
						height: 20				
					}, {
						xtype      : 'panel',
						title	   : lang['diagnoz_skoroy_pomoschi'],
						frame	   : true,
						labelWidth: 100,				
						items      : [{
								name: 'Diag_id',
								xtype: 'swdiagcombo',
								allowBlank: false,
								//style: 'border: 1px solid #ff0000;',
								disabledClass: 'field-disabled'
						}]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						labelWidth: 300,
						items      : [{
							fieldLabel: lang['instrumentalnyie_metodyi_diagnostiki'],
							name: 'Instrument',
							width: '90%',
							xtype: 'textarea'
						}]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						labelWidth: 300,
						items      : [{
							fieldLabel: lang['lechebnyie_meropriyatiya'],
							name: 'Lecheb',
							width: '90%',
							xtype: 'textarea'
						}]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						labelWidth: 300,
						items      : [{
							fieldLabel: lang['rashod'],
							name: 'Rashod',
							width: '90%',
							xtype: 'textarea'
						}]
					}
				]
			}
		];
	},
	
	// Валидация формы перед сохранением
	// Валидации на ориг. форме не нашлось
	doValidate: function(callback){
		var me = this,
			has_error = false,
			error = '',
			base_form = me.FormPanel.getForm(),
			diagField = base_form.findField('Diag_id'),
			payTypeCombo = base_form.findField('PayType_id');

		if ( !base_form.isValid() ) {
			has_error = true;
			error = ERR_INVFIELDS_MSG;
		}
		
		if ( error.length ) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					var invalid = this.FormPanel.getInvalid()[0];
					if ( invalid ) {
						invalid.ensureVisible().focus();
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: error,
				title: ERR_INVFIELDS_TIT
			});
			callback(false);
		}
		else{
			callback(true);
		}
	},
	
	//метод отображения надписи возраста
	showAgeLabel: function(){
		var me = this,
			base_form = me.FormPanel.getForm(),
			birthField = base_form.findField('Birthday');

		if( birthField.isValid() ) {
			var b_days = Math.floor(swGetPersonAgeDay(birthField.getValue(), new Date()));
			var b_month = swGetPersonAgeMonth(birthField.getValue(), new Date());
			var b_year = swGetPersonAge(birthField.getValue(), new Date());

			if (b_days >= 0 && b_days <= 30) Ext.getCmp(me.id+'AgeText').setText(lang['vozrast'] +' '+ b_days+lang['dney']);
			if (b_days > 30 && b_year == 0) {
				var b_day_minus = Math.floor(swGetPersonAgeDay(birthField.getValue(), new Date().add(Date.MONTH, -1*b_month)));
				Ext.getCmp(me.id+'AgeText').setText(lang['vozrast'] +' '+ b_month+lang['mesyatsev'] +' '+ b_day_minus +' '+ lang['dney']);
			}
			if (b_year > 0 && b_year <= 3) {
				var b_month_minus = Math.floor(swGetPersonAgeMonth(birthField.getValue(), new Date().add(Date.YEAR, -1*b_year)));
				Ext.getCmp(me.id+'AgeText').setText(lang['vozrast'] +' '+ b_year+lang['let'] +' '+ b_month_minus +' '+ lang['mesyatsev']);
			}
			if (b_year > 3) Ext.getCmp(me.id+'AgeText').setText(lang['vozrast'] +' '+ b_year +' '+ lang['let']);

		} else {
			var ag = me.FormPanel.getForm().findField('Age').getValue();
			if (ag > 0) {
				Ext.getCmp(me.id+'AgeText').setText('01.01.'+(getFullYear()-ag) +' '+ ' ('+ag+'лет)');
			} else {
				Ext.getCmp(me.id+'AgeText').setText('');
			}
		}
	}
});
