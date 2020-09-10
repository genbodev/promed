/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
* Спецификация Пензы
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

        setLpuSectionGlobalStoreFilter({
            arrayLpuUnitType: [12]
        });

	},
	
	//далее - переопределение набора полей 
	//получение списка компонентов для вкладки Информация о вызове
	getPersonFields: function(){
		var me = this;
		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,	
				labelWidth : 400,
				items      : [
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								width: 'auto',
								style: 'padding: 0px',
								items: [
									{
										xtype: 'swcmpclosecardisextracombo',
										hiddenName: 'CmpCloseCard_IsExtra',
										allowBlank: true,
										disabled: true,
										hideLabel: true,
										hidden: true
									},
									{
										fieldLabel: 'Номер вызова за день',
										name: 'Day_num',
										xtype: 'textfield',
										allowBlank: false,
										regex: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}									
									}, {
										fieldLabel: 'Номер вызова за год',
										name: 'Year_num',
										xtype: 'textfield',
										allowBlank: false,
										regex: /\d/,
										validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
									}
								]
							},
							{
								border: false,
								layout: 'form',
								width: 600,
								labelWidth : 150,
								style: 'padding: 4px 0 0 0',
								items: [
									{
										xtype: 'textfield',
										fieldLabel: langs('Признак вызова за день'),
										name: 'CmpCloseCard_DayNumPr',
										maskRe: /[0-9]/,
										labelWidth : 100,
										hidden: !getRegionNick().inlist(['penza']),
										maxLength: 12
									},
									{
										xtype: 'textfield',
										fieldLabel: langs('Признак вызова за год'),
										name: 'CmpCloseCard_YearNumPr',
										maskRe: /[0-9]/,
										labelWidth : 100,
										hidden: !getRegionNick().inlist(['penza']),
										maxLength: 12
									}
								]
							},
							{
								border: false,
								layout: 'form',
								width: 400,
								labelWidth: 100,
								items: [
									{
										xtype: 'swpaytypecombo',
										allowBlank: false,
										disabledClass: 'field-disabled',
										checkAllowLinkedFields: function(payType_Code){
											var base_form = me.FormPanel.getForm(),
												diagField = base_form.findField('Diag_id');
												
												//если полис не ОМС то делаем поле результат выезда необязательным
												//иначе - обязательный												
												diagField.allowBlank = !payType_Code.inlist([1]);
												diagField.validate();
												
										},
										listeners: {
											change: function(cmp, newVal){
												cmp.checkAllowLinkedFields(this.store.getById(newVal).get('PayType_Code'));
											},
											select: function(cmp, rec, ind){
												cmp.checkAllowLinkedFields(rec.get('PayType_Code'));
											}
										}
									},
									{
										xtype: 'checkbox',
										labelSeparator: '',
										boxLabel: 'На контроле',
										name: 'CmpCallCard_isControlCall'
									}
								]
							}
						]
					},
					{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [
							{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								items: []
							}
							]

					},
					{
						title: ++me.panelNumber + '. Время',
						id : 'timeBlock',
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								dateLabel: 'Приема вызова',	
								hiddenName: 'AcceptTime',
								hiddenId: this.getId() + '-AcceptTime',
								onChange: function(field, newValue){					
									me.calcSummTime();
									var base_form = me.FormPanel.getForm();
									if (me.action != 'view') {
										base_form.findField('Diag_id').setFilterByDate(new Date(newValue));
									}
									me.loadEmergencyTeamsWorkedInATime();
								}.createDelegate(this),
								onTriggerClick: Ext.emptyFn,
								xtype: 'swdatetimefield'
							}, 							
							{
								dateLabel: 'Передачи вызова бригаде СМП',
								hiddenName: 'TransTime',
								hiddenId: this.getId() + '-TransTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Выезда на вызов',	
								hiddenName: 'GoTime',
								hiddenId: this.getId() + '-GoTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Прибытия на место вызова',	
								hiddenName: 'ArriveTime',
								hiddenId: this.getId() + '-ArriveTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Начала транспортировки больного',	
								hiddenName: 'TransportTime',
								hiddenId: this.getId() + '-TransportTime',
								onChange: function(field, newValue){
									me.calcSummTime();									
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							},
							{
								dateLabel: 'Прибытия в медицинскую организацию',	
								hiddenName: 'ToHospitalTime',
								hiddenId: this.getId() + '-ToHospitalTime',								
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Окончания вызова',	
								hiddenName: 'EndTime',								
								hiddenId: this.getId() + '-EndTime',
								onChange: function(field, newValue){
									me.calcSummTime();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Возвращения на станцию (подстанцию, отделение)',	
								hiddenName: 'BackTime',
								hiddenId: this.getId() + '-BackTime',
								xtype: 'swdatetimefield'
							}, 
							{
								fieldLabel: 'Затраченное на выполнения вызова (считается автоматически)',	
								name: 'SummTime',								
								width: 90,
								xtype: 'textfield',
								maskRe: /[0-9:]/,
								regex:/^\d{1,}(:\d{1,2})?$/
							}
						]
					}, 
					{
						title: ++me.panelNumber + '. Подразделение СМП',
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								fieldLabel: 'Фельдшер по приему вызова',
								name: 'FeldsherAcceptCall',
								width: 350,
								allowBlank: true,
								xtype: 'swmedpersonalcombo',
								hiddenName: 'FeldsherAcceptCall',
								listeners: {
									render: function(){
										this.getStore().load();
									},
									select: function(combo,record,index){
										var appendCombo = this.FormPanel.getForm().findField('FeldsherAccept');
										if(appendCombo)appendCombo.setValue(combo.getValue());
									}.createDelegate(this)
								}
							},
							{
								xtype: 'swsmpunitscombo',
								fieldLabel: 'Станция (подстанция), отделение',
								hiddenName:'LpuBuilding_id',
								disabledClass: 'field-disabled',
								width: 350,
								allowBlank: true,
								listWidth: 300,
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
								xtype: 'numberfield',
								fieldLabel: 'Номер бригады скорой медицинской помощи',
								name: 'EmergencyTeamNum',						
								allowBlank: false,
								maskRe: /\d/
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',					
								//width: 750,
								items: [
								{
									fieldLabel: 'Профиль бригады скорой медицинской помощи',						
									comboSubject: 'EmergencyTeamSpec',						
									hiddenName: 'EmergencyTeamSpec_id',
									id: this.id+'EmergencyTeamSpec_id',
									allowBlank: false,
									width: 350,
									listWidth: 300,						
									xtype: 'swcustomobjectcombo'						
								}]
							},
							{
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
							},
							{
								allowBlank: false,
								hiddenName: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								id: 'CCCNCCW_MedStaffFactCombo',
								lastQuery: '',
								listWidth: 600,
								width: 350,
								xtype: 'swmedstafffactglobalcombo',
								listeners: {
									select: function(combo, record, index){
										if (record.data.MedPersonal_id > 0) {
											this.FormPanel.getForm().findField('MedPersonal_id').setValue(record.data.MedPersonal_id);
										}
									}.createDelegate(this)
								}
							},
							/*
							поле находится в mainCloseCardWindow
							{
								xtype: 'hidden',
								name: 'MedPersonal_id',
								listeners: {
									'change': function(t, n, o){
										this.FormPanel.getForm().findField('MedStaffFact_id').setValue(o);
									}.createDelegate(this)
								}
							},*/
							{
								text: 'Выбрать', 
								xtype: 'button',
								id: 'BrigSelectBtn',								
								handler: function() {
									var parentObject = this;									
									getWnd('swSelectEmergencyTeamWindow').show({
										CmpCallCard: parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(),
										AcceptTime: Ext.util.Format.date(parentObject.FormPanel.getForm().findField('AcceptTime').getValue(), 'd.m.Y H:i:s'),
										callback: function(data) {
											parentObject.setEmergencyTeam(parentObject.FormPanel.getForm().findField('CmpCallCard_id').getValue(), data);
										}
										
									});
								}.createDelegate(this)													
							}
						]
					},
					{									
						title : ++me.panelNumber + '. Адрес вызова',									
						xtype      : 'fieldset',
						refId : 'addressBlock',
						autoHeight: true,
						items : [
								{								
									enableKeyEvents: true,
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

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('Area_id').setValue(record.get('KLSubRGN_id'));
												base_form.findField('Area_id').getStore().removeAll();
												base_form.findField('Area_id').getStore().load({
													params: {region_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
													}.createDelegate(base_form.findField('Area_id'))
												});
											} else if( record.get('KLCity_id') != '' ) {
												base_form.findField('City_id').setValue(record.get('KLCity_id'));
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({
													params: {subregion_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('City_id') == this.getValue(); }.createDelegate(this))));
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
										base_form.findField('Street_id').getStore().removeAll();
									}.createDelegate(this),
									width: 180,
									xtype: 'swklareastatcombo'
								},
								{
									name: 'KLRgn_id',
									value: 0,
									xtype: 'hidden'
								},{
									enableKeyEvents: true,					
									fieldLabel: 'Район',
									hiddenName: 'Area_id',
									width: 180,
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('SubRGN_id') > 0 ) {
												base_form.findField('City_id').reset();
												base_form.findField('City_id').getStore().removeAll();
												base_form.findField('City_id').getStore().load({params: {subregion_id: record.get('SubRGN_id')}});
												base_form.findField('Town_id').getStore().removeAll();
												base_form.findField('Town_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});
												base_form.findField('Street_id').getStore().removeAll();
												base_form.findField('Street_id').getStore().load({params: {town_id: record.get('SubRGN_id')}});
											}
										}.createDelegate(this)
									},
									xtype: 'swsubrgncombo'
								}, {
									hiddenName: 'City_id',
									name: 'City_id',
									width: 180,
									xtype: 'swcitycombo',
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('City_id') > 0 ) {

												base_form.findField('Town_id').getStore().removeAll();
												base_form.findField('Town_id').getStore().load({params: {city_id: record.get('City_id')}});
												base_form.findField('Street_id').getStore().removeAll();
												base_form.findField('Street_id').getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}});
											}					
										}.createDelegate(this)
									}
								}, {
									enableKeyEvents: true,
									listeners: {
										beforeselect: function(combo, record) {
											combo.setValue(record.get(combo.valueField));	
											var base_form = this.FormPanel.getForm();
											base_form.findField('Street_id').getStore().removeAll();
											base_form.findField('Street_id').getStore().load({
												params: {town_id: combo.getValue()}
											});
										}.createDelegate(this)
									},
									minChars: 0,
									hiddenName: 'Town_id',
									name: 'Town_id',
									width: 250,
									xtype: 'swtowncombo'
								}, {
									xtype: 'swstreetcombo',
									fieldLabel: 'Улица',
									hiddenName: 'Street_id',
									name: 'Street_id',
									width: 250,
									editable: true
								},
								
								{
									disabledClass: 'field-disabled',
									fieldLabel: 'Дом',
									//name: 'CmpCallCard_Dom',
									name: 'House',
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: 'Корпус',
									//name: 'CmpCallCard_Dom',
									name: 'Korpus',
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: 'Квартира',
									maxLength: 5,
									autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
									//maskRe: /^([а-яА-Я0-9]{1,5})$/,
									//name: 'CmpCallCard_Kvar',
									name: 'Office',
									width: 100,
									xtype: 'textfieldpmw'								
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: 'Комната',
									//name: 'CmpCallCard_Kvar',
									name: 'Room',
									width: 100,
									xtype: 'textfield'								
								}, {								
									disabledClass: 'field-disabled',
									fieldLabel: 'Подъезд',
									//name: 'CmpCallCard_Podz',
									name: 'Entrance',
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: 'Этаж',
									//name: 'CmpCallCard_Etaj',					
									name: 'Level',					
									width: 100,
									xtype: 'textfield'
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: 'Код замка в подъезде (домофон)',
									//name: 'CmpCallCard_Kodp',			
									name: 'CodeEntrance',			
									width: 100,
									xtype: 'textfield'
								}
						]
					}, {						
						title : ++me.panelNumber + '. Сведения о больном',												
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{								
								layout: 'column',
								items :[{
									border: false,
									layout: 'form',
									items : [{
										handler: function() {
											this.personSearch();
										}.createDelegate(this),
										iconCls: 'search16',
										id: 'CCCSEF_PersonSearchBtn',
										text: 'Поиск',
										xtype: 'button'
									},
									{
										handler: function() {
											this.personReset();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonResetBtn',
										text: 'Сброс',
										xtype: 'button'
									},
									{
										handler: function() {
											this.personUnknown();
										}.createDelegate(this),
										iconCls: 'reset16',
										id: 'CCCSEF_PersonUnknownBtn',
										text: 'Неизвестен',
										xtype: 'button'
									}]
								}, {
									border: false,
									layout: 'form',
									items : [{
										fieldLabel: 'Фамилия',
										disabled: true,
										name: 'Fam',										
										toUpperCase: true,
										width: 180,
										toUpperCase: true,
										xtype: 'textfieldpmw',
										allowBlank: false
									}, {
										fieldLabel: 'Имя',
										disabled: true,
										name: 'Name',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										allowBlank: false
									}, {
										fieldLabel: 'Отчество',
										disabled: true,
										name: 'Middle',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw'
									}
									]
								}]
							},							  
							{
								xtype      : 'fieldset',
								autoHeight: true,
								items      : [
								{
									allowDecimals: false,
									allowNegative: false,
									disabledClass: 'field-disabled',
									fieldLabel: 'Возраст',
									disabled: false,
									allowBlank: false,
									name: 'Age',
									toUpperCase: true,
									width: 180,
									xtype: 'numberfield',
									validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;},
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
									}
								}, {			
									columns: 1,
									vertical: true,
									width: 600,
									xtype: 'checkboxgroup',
									singleValue: true,	
									disabled: false,
									allowBlank: false,
									fieldLabel: 'Единица измерения возраста',
									items: this.getCombo('AgeType_id'),
									listeners: {
										change: function() {
											this.setMKB();
										}.createDelegate(this)
									}
								}]
							},
							{
								comboSubject: 'Sex',
								disabledClass: 'field-disabled',
								fieldLabel: 'Пол',
								disabled: true,
								hiddenName: 'Sex_id',
								allowBlank: false,								
								width: 130,
								xtype: 'swcommonsprcombo',
								listeners: {
									change: function() {
										this.setMKB();
									}.createDelegate(this)
								}
							}, {
								xtype: 'textfield',
								disabled: true,
								width: 180,
								name: 'Work',
								fieldLabel: 'Место работы'
							}, {
								xtype: 'textfield',
								disabled: true,
								width: 180,
								name: 'DocumentNum',
								fieldLabel: 'Серия и номер документа, удостоверяющего личность'
							},
							{
								fieldLabel: 'Серия полиса',
								name: 'Person_PolisSer',
								width: 180,
								xtype: 'textfield',
								editable: false,
								disabled: true
							},
							{
								fieldLabel: 'Номер полиса',
								name: 'Person_PolisNum',
								width: 180,
								xtype: 'textfield',
								editable: false,
								disabled: true
							},
							{
								fieldLabel: 'Единый номер',
								name: 'Person_PolisEdNum',
								width: 180,
								xtype: 'textfield',
								editable: false,
								disabled: true
							},
							{
								valueField: 'Lpu_id',
								//allowBlank: false,
								//disabled: true,
								autoLoad: true,
								width: 350,
								listWidth: 350,
								fieldLabel: lang['lpu_peredachi'],
								disabledClass: 'field-disabled',
								hiddenName: 'Lpu_ppdid',
								displayField: 'Lpu_Nick',
								medServiceTypeId: 18,
								handler: function() {
									this.selectLpuTransmit();
								}.createDelegate(this),
								comAction: 'AllAddress',
								listeners: {
									beforeselect: function(combo, record) {
										var base_form = this.FormPanel.getForm();
										if(record.get('Lpu_id') == '0')
										{
											combo.getStore().load({params:
											{
												Object: 'LpuWithMedServ',
												comAction: 'AllAddress',
												MedServiceType_id: 18,
												KLAreaStat_idEdit: base_form.findField('KLAreaStat_idEdit').getValue(),
												KLSubRgn_id: base_form.findField('Area_id').getValue(),
												KLCity_id: base_form.findField('City_id').getValue(),
												KLTown_id: base_form.findField('Town_id').getValue(),
												KLStreet_id: base_form.findField('Street_id').getValue(),
												CmpCallCard_Dom: base_form.findField('House').getValue(),
												Person_Age: base_form.findField('Age').getValue()
											}
											});
											return false;
										}
										//определяем метод загрузки лпу передачи
										//this.selectLpuTransmit();
										}.createDelegate(this),
									select: function(combo, record){
										if (record.data.Lpu_id == null)
										{
											combo.setValue('');
										}
									}
								},

								xtype: 'swlpuwithmedservicecombo'
							},
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['dopolnitelnaya_informatsiya_utochnennyiy_adres'],
								toUpperCase: true,
								height: 100,
								name: 'CmpCloseCard_DopInfo',
								width: 350,
								xtype: 'textarea'
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						items: [ 
								// @todo Сделать компонент и вынести в библиотеку
								{
									xtype: 'swcommonsprcombo',
									fieldLabel: ++me.panelNumber + '. Кто вызывает',
									comboSubject: 'CmpCallerType',
									hiddenName: 'Ktov',
									displayField: 'CmpCallerType_Name',
									disabledClass: 'field-disabled',
									editable: true,
									forceSelection: false,
									width: 350,
									listeners: {
										blur: function(el){
											var base_form = me.FormPanel.getForm(),
												CmpCallerTypeField = base_form.findField('CmpCallerType_id'),
												raw_value = el.getRawValue(),
												rec = el.findRecord( el.displayField, raw_value );
										
											// Запись в комбобоксе присутствует
											if ( rec ) {
												CmpCallerTypeField.setValue( rec.get( el.valueField ) );
											}
											// Пользователь указал свое значение
											else {
												CmpCallerTypeField.setValue(null);
											}
											el.setValue(raw_value);
										}
									}
								},
								{
									xtype: 'hidden',
									name: 'CmpCallerType_id'
								}, 
								{
									fieldLabel: '№ телефона вызывающего',
									name: 'Phone',
									width: 250,
									xtype: 'textfield'
								}
							]
					}, 
					{
						xtype      : 'fieldset',
						autoHeight: true,
						items : [
							{
								xtype: 'swmedpersonalcombo',
								fieldLabel: ++me.panelNumber + '. Фельдшер, принявший вызов',
								hiddenName: 'FeldsherAccept',
								allowBlank:true,
								width: 250,
								listeners: {
									select: function(combo,record,index){
										var appendCombo = this.FormPanel.getForm().findField('FeldsherAcceptCall');
										if(appendCombo)appendCombo.setValue(combo.getValue());
									}.createDelegate(this)
								}
							},
							{
								xtype: 'swmedpersonalcombo',
								fieldLabel: ++me.panelNumber + '. Фельдшер, передавший вызов',
								hiddenName: 'FeldsherTrans',
								allowBlank:true,
								width: 250
							},
							{
								xtype: 'textfield',
								disabled: true,
								fieldLabel: ++me.panelNumber + '. Пользователь, закрывший карту вызова',
								name: 'pmUser_insName',
								allowBlank:true,
								width: 250
							}
						]
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						//width: '100%',
						items      : [{
								columns: 1,
								vertical: true,
								width: '100%',
								xtype: 'checkboxgroup',
								singleValue: true,					
								fieldLabel: ++me.panelNumber + '. Место регистрации больного',			
								items: this.getCombo('PersonRegistry_id')
						}]
					}, 
					{
						xtype      : 'fieldset',
						autoHeight: true,
						frame	   : true,
						//width: '100%',
						items      : [
							{
								name: "SocialCombo",
								columns: 2,
								vertical: true,
								allowBlank: false,
								fieldLabel	   : ++me.panelNumber + '. Социальное положение больного',
								width: 850,
								xtype: 'checkboxgroup',
								singleValue: true,	
								items: this.getCombo('PersonSocial_id'),
                                listeners:{
                                    change: function(){
                                        me.FormPanel.getForm().findField('SocialCombo').allowBlank = true;
                                        me.FormPanel.getForm().findField('SocialCombo').validate();
                                    }.createDelegate(this),
                                }
							}
						]
					}
				]
			}
		];
	}
});