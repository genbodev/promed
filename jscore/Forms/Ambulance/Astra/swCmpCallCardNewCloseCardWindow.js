/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
* Спецификация Астрахани
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
										allowBlank: false,
										disabled: false
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
									
									// проверка на уникальность введенного номера вызова за день и за год
									if( newValue && me.action == 'stream' ) me.existenceNumbersDayYear();
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
									me.getTheDistanceInATimeInterval();
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
									me.getTheDistanceInATimeInterval();
								}.createDelegate(this),
								xtype: 'swdatetimefield'
							}, 
							{
								dateLabel: 'Возвращения на станцию (подстанцию, отделение)',	
								hiddenName: 'BackTime',
								hiddenId: this.getId() + '-BackTime',
								xtype: 'swdatetimefield',
								onChange: function(field, newValue){
									me.getTheDistanceInATimeInterval();
								}.createDelegate(this)
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
								allowBlank: false,
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
								name: 'EmergencyTeam_id',
								xtype: 'hidden',
								hiddenId: 'EmergencyTeam_id'
							}, 
							{
								allowBlank: false,
								hiddenName: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								id: 'CCCNCCW_MedStaffFactCombo',
								lastQuery: '',
								listWidth: 600,						
								parentElementId: 'CCCNCCW_LpuSectionCombo',
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
											base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
											base_form.findField('CmpCloseCard_UlicSecond').reset();
											base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();
											
											if( record.get('KLRGN_id') ){
												base_form.findField('KLRgn_id').setValue(record.get('KLRGN_id'));
											}

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
													params: {
														town_id: record.get('SubRGN_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														base_form.findField('CmpCloseCard_UlicSecond').getStore().data = base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().data;
													}
												});
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
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').enable();
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').reset();
										base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
										base_form.findField('CmpCloseCard_UlicSecond').enable();
										base_form.findField('CmpCloseCard_UlicSecond').reset();
										base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();
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
									disabled: false,
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
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().removeAll();
												base_form.findField('CmpCloseCard_UlicSecond').getStore().removeAll();
												base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().load({
													params: {
														town_id: record.get('SubRGN_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														base_form.findField('CmpCloseCard_UlicSecond').getStore().data = base_form.findField('StreetAndUnformalizedAddressDirectory_id').getStore().data;
											}
												});
											}
										}.createDelegate(this)
									},
									xtype: 'swsubrgncombo'
								}, 
								{
									hiddenName: 'City_id',
									disabled: false,
									name: 'City_id',
									width: 180,
									//allowBlank: false,
									xtype: 'swcitycombo',
									onTrigger2Click: function() {
										me.showSearchCityWindow();
									},
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record && record.get('City_id') > 0 ) {
												var townField = base_form.findField('Town_id'),
													streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id'),
													secondStreetField = base_form.findField('CmpCloseCard_UlicSecond');

												townField.getStore().removeAll();
												townField.getStore().load({params: {city_id: record.get('City_id')}});
												streetField.getStore().removeAll();
												secondStreetField.getStore().removeAll();

												if(record.get('City_id')){
													streetField.getStore().load({
														params: {
															town_id: record.get('City_id'),
															showSocr: 1
														},
														callback: function(recs){
															//@todo loadData загружает пустые записи потом исправить
															secondStreetField.getStore().data = streetField.getStore().data;
											}
													});
												};

											}
										}.createDelegate(this)
									}
								}, 
								{
									minChars: 0,
									hiddenName: 'Town_id',
									name: 'Town_id',
									width: 250,
									xtype: 'swtowncombo',
									onTrigger2Click: function() {
										me.showTownSearchWindow();
									},
									disabled: false,
									enableKeyEvents: true,
									//allowBlank: false,
									listeners: {
										beforeselect: function(combo, record) {
											var base_form = this.FormPanel.getForm(),
												cityField = base_form.findField('City_id'),
												streetField = base_form.findField('StreetAndUnformalizedAddressDirectory_id'),
												secondStreetField = base_form.findField('CmpCloseCard_UlicSecond');
											
											combo.setValue(record.get(combo.valueField));

											streetField.clearValue();
											streetField.getStore().removeAll();
											secondStreetField.clearValue();
											secondStreetField.getStore().removeAll();

											if(combo.getValue()){
												streetField.getStore().load({
													params: {
														town_id: record.get('Town_id')
													},
													callback: function(recs){
														//@todo loadData загружает пустые записи потом исправить
														secondStreetField.getStore().data = streetField.getStore().data;
													}
												});
											};

										}.createDelegate(this)
									}									
								}, 
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: lang['ulitsa_object'],
									hiddenName: 'StreetAndUnformalizedAddressDirectory_id',
									listeners: {
										blur: function(c){
											var base_form = this.FormPanel.getForm();
											if(
												!c.store.getCount() || 
												c.store.findBy(function(rec) { return rec.get('StreetAndUnformalizedAddressDirectory_id') == c.getValue(); }) == -1 
											)
											{
												base_form.findField('UnformalizedAddressDirectory_id').setValue(null);
												base_form.findField('Street_id').setValue(null);
												base_form.findField('CmpCallCard_Ulic').setValue(c.getRawValue());
											}
										}.createDelegate(this),
										beforeselect: function(combo, record) {											
											if ( typeof record != 'undefined' ) { combo.setValue(record.get(combo.valueField)); }
											var base_form = this.FormPanel.getForm();
											base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
											base_form.findField('Street_id').setValue(record.get('KLStreet_id'));										
										}.createDelegate(this)
									},
									width: 250,
									editable: true
								},
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: langs('Улица'),
									hiddenName: 'CmpCloseCard_UlicSecond',
									trigger2Class: 'x-form-clear-trigger',
									listeners: {},
									width: 250,
									editable: true,
									onTrigger2Click: function() {
										var base_form = this.FormPanel.getForm(),
											CmpCallCard_Dom = base_form.findField('House'),
											CmpCloseCard_UlicSecond = base_form.findField('CmpCloseCard_UlicSecond');

										CmpCloseCard_UlicSecond.reset();
										this.checkCrossRoadsFields(true);
									}.createDelegate(this),
									listeners: {
										blur: function(combo){
											this.checkCrossRoadsFields(true);
										}.createDelegate(this),

										beforeselect: function(combo, record) {
											if ( typeof record != 'undefined' ) {
												combo.setValue(record.get(combo.valueField));
											}
										}.createDelegate(this),

										select: function(combo, rec) {
											this.checkCrossRoadsFields(true);
										}.createDelegate(this)
									}
								},
								{
									xtype: 'hidden',
									name: 'UnformalizedAddressDirectory_id'
								},
								{
									xtype: 'hidden',
									name: 'Street_id'
								},
								{
									xtype: 'hidden',
									name: 'CmpCallCard_Ulic'
								},
								{
									disabledClass: 'field-disabled',
									//disabled: true,
									fieldLabel: 'Дом',
									//name: 'CmpCallCard_Dom',
									name: 'House',
									width: 100,
									xtype: 'textfield',
									enableKeyEvents: true,
									listeners: {
										keyup: function(c, e) {
											this.checkCrossRoadsFields(true, e);
										}.createDelegate(this)
									}
								}, {
									disabledClass: 'field-disabled',
									//disabled: true,
									fieldLabel: 'Корпус',
									//name: 'CmpCallCard_Dom',
									name: 'Korpus',
									width: 100,
									xtype: 'textfield'
								}, {
									//disabled: true,
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
									//disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Комната',
									//name: 'CmpCallCard_Kvar',
									name: 'Room',
									width: 100,
									xtype: 'textfield'								
								}, {								
									//disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Подъезд',
									//name: 'CmpCallCard_Podz',
									name: 'Entrance',
									width: 100,
									xtype: 'textfield'
								}, {
									//disabled: true,
									disabledClass: 'field-disabled',
									fieldLabel: 'Этаж',
									//name: 'CmpCallCard_Etaj',					
									name: 'Level',					
									width: 100,
									xtype: 'textfield'
								}, {
									//disabled: true,
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
										allowBlank: false,
										disabled: true,
										name: 'Fam',										
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											},
											blur: function(){
												me.checkPersonIdentification();
											}
										}
									}, {
										fieldLabel: 'Имя',
										allowBlank: false,
										disabled: true,
										name: 'Name',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											},
											blur: function(){
												me.checkPersonIdentification();
											}
										}
									}, {
										fieldLabel: 'Отчество',
										disabled: true,
										name: 'Middle',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											},
											blur: function(){
												me.checkPersonIdentification();
											}
										}
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
											var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
											if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);

											me.setMKB();
										},
										blur: function(){
											me.checkPersonIdentification();
										}
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
								name: 'CmpCloseCard_PolisEdNum',
								width: 180,
								xtype: 'textfield',
								editable: false,
								disabled: true
							},	
							{
								fieldLabel: 'Номер пенсионного свидетельства',
								name: 'Person_Snils',
								width: 180,
								xtype: 'textfield',
								editable: false,
								disabled: true
							},
							/*
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
							*/
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
								width: 600,
								xtype: 'checkboxgroup',
								singleValue: true,
								items: this.getCombo('PersonSocial_id')
							}
						]
					}
				]
			}
		];
	},
	//получение списка компонентов для вкладки Повод к вызову
	getPovodFields: function(){
		var me = this;
		return [
			/*{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Причина обращения',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('AppealReason_id')
				}]
			},*/
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      :
				[
					{
						comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: ++me.panelNumber + '. Повод',
						allowBlank: false,
						hiddenName: 'CallPovod_id',
						id: this.id+'idCallPovod_id',
						width: 350,
						listWidth: 300,
						editable: true,
						xtype: 'swreasoncombo',
						listeners: {
							change: function(cmp, newVal){
								var base_form = me.FormPanel.getForm(),
									radioGroupResultTrip = base_form.findField('resultEmergencyTrip'),
									diagField = base_form.findField('Diag_id'),
									reasonRec = cmp.store.getById(newVal);

								//если повод - "ошибка" то делаем поле результат выезда необязательным и диагноз
								//иначе - обязательный
								if(reasonRec && reasonRec.get('CmpReason_Code').inlist(['01!'])){
									if(radioGroupResultTrip) radioGroupResultTrip.allowBlank = true;
									diagField.allowBlank = true;
								}
								else{
									if(radioGroupResultTrip) radioGroupResultTrip.allowBlank = false;
									diagField.allowBlank = false;
								}
								if(radioGroupResultTrip) radioGroupResultTrip.validate();
								diagField.validate();
							}
						}
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
                items      : [
                    {
                        fieldLabel: 'Причина обращения',
                        tableSubject: 'CmpReasonNew',
                        name: 'CallPovodNew_id',
                        xtype: 'swcustomobjectcheckboxgroup',
                        columns: 1,
                        singleValue: true,
                        vertical: true,
                        width: '100%'
                    }
                ]
            },
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel	   : ++me.panelNumber + '. Вызов',
						hiddenName: 'CallType_id',
						xtype: 'swcmpcalltypecombo',
						width: 300,
						listWidth: 300
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Место получения вызова бригадой скорой медицинской помощи',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('CallTeamPlace_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Причины выезда с опозданием',
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('Delay_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Состав бригады скорой медицинской помощи',
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('TeamComplect_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [
					new Ext.ux.RemoteCheckboxGroup({
						name: 'CmpCallPlaceType_id',
						columns: 1,
						vertical: true,
						fieldLabel	   : ++me.panelNumber + '. Тип места вызова',
						url: '/?c=CmpCallCard&m=getCmpCallPlaces',
						method: 'post',
						singleValue: true,
						reader: new Ext.data.JsonReader(
							{
								totalProperty: 'totalCount',
								root: 'data',
								fields: [{name: 'CmpCallPlaceType_id'}, {name: 'CmpCallPlaceType_Name'}, {name: 'is_checked'}]
							}),
						cbRenderer:function(){},
						cbHandler:function(){},
						items:[{boxLabel:'Loading'},{boxLabel:'Loading'}],
						fieldId: 'CmpCallPlaceType_id',
						fieldName: 'CmpCallPlaceType_Name',
						boxLabel: 'CmpCallPlaceType_Name',
						fieldValue: 'CmpCallPlaceType_id',
						fieldChecked: 'is_checked'
					})
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 2,
						fieldLabel	   : ++me.panelNumber + '. Причина несчастного случая',
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('AccidentReason_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						columns: 2,
						vertical: true,
						fieldLabel	   : 'Травма',
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Trauma_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
						fieldLabel: ++me.panelNumber + '. Наличие клиники опьянения',
						hiddenName: 'isAlco',
						width: 40,
						comboSubject: 'YesNo',
						xtype: 'swcommonsprcombo'
				}]
			}
		];
	},
	
	//получение списка компонентов для вкладки Манипуляции
	getProcedureFields: function(){
		var me = this;			

        /*
		me.UslugaPanel = new Ext.Panel({
			//title: ++me.panelNumber + '. Услуги тестовые не удалаять',
			layout:'table',
			cls: 'uslugaPanel',
			defaults: {				
				bodyStyle:'padding:20px',
				labelWidth: 150
			},
			layoutConfig: {
				columns: 6
			},
			items: []
		});
*/
		return [
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Оказанная помощь на месте вызова',
				frame	   : true,
				items      : [{
						name: 'HelpPlace',
						width: '99%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Оказанная помощь в автомобиле скорой медицинской помощи',
				frame	   : true,
				items      : [
					{

						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						width: '95%',
						labelWidth : 100,
						items: [
							{
									name: 'HelpAuto',
									width: '99%',
									xtype: 'textarea',
									fieldLabel: 'Оказанная помощь'
							}
						]
					}
				]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Эффективность проведенных мероприятий',
				frame	   : true,
				layout	   : 'form',
				items :[
					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'container',
								autoEl: {},
								layout: 'column',
								columnWidth: .25,
								items: [
								{
									xtype: 'fieldset',
									border: false,
									autoHeight: true,
									//width: 310,
									labelWidth : 120,
									items: [{
										fieldLabel: 'АД, мм.рт.ст.',
										name: 'sub1EAD',
										width: 50,
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3,
										listeners: {
											'blur': function(me){
												var baseform = this.FormPanel.getForm(),
													workadfield = baseform.findField('EfAD'),
													workad2field = baseform.findField('sub2EAD');

												workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
											}.createDelegate(this)
										}
									}]
								},
								{
									xtype: 'label',
									text: '/',
									style: 'padding: 0 0 0 10px;'
								},
								{
									xtype: 'numberfield',
									name: 'sub2EAD',
									width: 55,
									validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
									maxLength:3,
									style: 'margin: 0 0 0 10px;',
									listeners: {
										'blur': function(me){
											var baseform = this.FormPanel.getForm(),
												workadfield = baseform.findField('EfAD'),
												workad1field = baseform.findField('sub1EAD');

											workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
										}.createDelegate(this)
									}
								}
								]
							},
							{
								fieldLabel: 'АД, мм.рт.ст.',
								name: 'EfAD',
								xtype: 'hidden'
							},
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
									fieldLabel: 'Температура',
									name: 'EfTemperature',
									xtype: 'textfield',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
								}]
							},
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'ЧСС, мин.',
										name: 'EfChss',
										xtype: 'numberfield',
										//maskRe: /\d/,
										maxLength:3,
										allowDecimals: false,
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;}
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'Пульс, уд/мин',
										name: 'EfPulse',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3,
										allowDecimals: false
									}]
							}
						]
					},

					{
						xtype: 'container',
						autoEl: {},
						layout: 'column',
						items:
						[
							{
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'ЧД, мин.',
										name: 'EfChd',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3	,
										allowDecimals: false
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
										fieldLabel: 'Пульсоксиметрия',
										name: 'EfPulsks',
										xtype: 'numberfield',
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
										maxLength:3
									}]
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								labelWidth: "150px",
								columnWidth: .25,
								border: false,
								items: [{
									fieldLabel: 'Глюкометрия',
									name: 'EfGluck',
									xtype: 'textfield',
									plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
								}]
							}
						]
					}
				]
			},
            me.getUslugaPanel(++me.panelNumber)
			/*
			{
				xtype      : 'panel',
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'CCCNCC_SMPUslugaPanel',
				layout: 'form',
				style: 'margin-bottom: 0.5em;',
				title: ++me.panelNumber + '. Услуги',
				items: [
					me.UslugaPanel
				]
			}
			*/
		];
	},
	
	//получение списка компонентов для вкладки Результат
	getResultFields: function(){
		var me = this;

		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth: 500,
				items : [{
					fieldLabel: ++me.panelNumber + '. Согласие на медицинское вмешательство',
					hiddenName: 'isSogl',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++me.panelNumber + '. Отказ от медицинского вмешательства',
					hiddenName: 'isOtkazMed',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: ++me.panelNumber + '. Отказ от транспортировки для госпитализации в стационар',
					hiddenName: 'isOtkazHosp',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				}
				/*,{
					fieldLabel: 'Отказ от подписи',
					hiddenName: 'isOtkazSign',
					width: 40,
					comboSubject: 'YesNo',
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Причина отказа от подписи',
					name: 'OtkazSignWhy',
					width: 90,
					xtype: 'textfield'
				}
				*/
				]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат оказания скорой медицинской помощи',
				frame	   : true,
				items      : [{
					columns: 3,
					vertical: true,
					width: '100%',
					allowBlank: false,
					disabledClass: 'field-disabled',
					xtype: 'checkboxgroup',
					singleValue: true,
					name: 'Result_id',
					items :this.getCombo('Result_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Больной',
				frame	   : true,
				items      : [
					{
						columns: [600],
						width: '100%',
						vertical: true,
						name: 'Patient_id',
						xtype: 'checkboxgroup',
						singleValue: true,
						items : this.getCombo('Patient_id')
					},
					{
						columns: [600],
						width: '100%',
						vertical: true,
						xtype: 'checkboxgroup',
						items : [
							{
								name: 'CmpCloseCard_IsSignList',
								//id: this.id+'CmpCloseCard_IsSignList',
								boxLabel: 'Сигнальный лист вручен',
								xtype: 'checkbox',
								style: 'font-size: 12px;',
								labelStyle: 'font-size: 12px;'
							}
						]
					},
					
				]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Способ доставки больного в автомобиль скорой медицинской помощи',
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items :this.getCombo('TransToAuto_id')
				}]
			}, {
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат выезда',
				frame	   : true,
				items : [{
					columns: [600],
					vertical: true,
					width: '100%',
					xtype: 'checkboxgroup',
					singleValue: true,
					name: 'ResultEmergencyTrip',
					id: 'ResultUfa_id',
					allowBlank: false,
					items: this.getCombo('ResultUfa_id'),
					listeners:{
						change: function(radiogroup,field,some){

							if(!field && !field[0] && !field[0].code) {
								me.FormPanel.getForm().findField('ResultEmergencyTrip').validate();
								return false;
							}

							//#96465
							/**
							 * поля
							 * 231 - Больной не найден на месте
							 * 232 - Отказ от помощи (от осмотра)
							 * 233 - Адрес не найден
							 * 234 - Ложный вызов
							 * 235 - Смерть до приезда бригады СМП
							 * 236 - Больной увезен до прибытия СМП
							 * 237 - Больной обслужен врачом поликлиники до приезда СМП
							 * 238 - Вызов отменен
							 */
							var code = field[0].code,
								value = field[0].getValue();

							var allowBlankFlag = code.inlist([231, 232, 233, 234, 235, 236, 237, 238]) && value;

							me.FormPanel.getForm().findField('SocialCombo').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('SocialCombo').validate();

							me.FormPanel.getForm().findField('Diag_id').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('Diag_id').validate();

							me.FormPanel.getForm().findField('Result_id').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('Result_id').validate();

						}.createDelegate(this)
					}
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Километраж',
				labelWidth : 100,
				frame	   : true,
				items : [{
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: 'Километраж',
					maxValue: 9999.99,
					name: 'CmpCloseCard_UserKilo',
					xtype: 'numberfield',
					msgTarget: 'under'
				}, {
					name: 'Kilo',
					xtype: 'hidden'
				}]
			}, {
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Примечания',
				labelWidth : 100,
				frame	   : true,
				items : [{
					fieldLabel: 'Примечания',
					name: 'DescText',
					xtype: 'textarea',
					width: '90%'
				}]
			}
		]
	},

	isOsmUslugaComplex: function(code){
		if(!code) return false;
		var osmUslugaCodes = ['B01.044.002', 'B01.044.001'];

		return code.inlist(osmUslugaCodes);
	}
});