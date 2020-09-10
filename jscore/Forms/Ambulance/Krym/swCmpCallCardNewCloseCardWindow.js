/**
* swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
* Спецификация Крыма
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
										xtype: 'swcmpclosecardisprofilecombo',
										hiddenName: 'CmpCloseCard_IsProfile',
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
												cmp.checkAllowLinkedFields(cmp.store.getById(newVal).get('PayType_Code'));
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
									var date = new Date(newValue);
									if (me.action != 'view') {
										base_form.findField('Diag_id').setFilterByDate(date);
										base_form.findField('CallType_id').setFilterByDate(date);
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
								dateLabel: 'Окончания транспортировки больного',
								hiddenName: 'CmpCloseCard_TranspEndDT',
								hiddenId: this.getId() + '-TranspEndDT',
								onChange: function(field, newValue){
									this.calcSummTime();
									//if(getRegionNick().inlist(['ufa', 'krym']))
										//me.FormPanel.getForm().findField('EndTime').setValue(newValue);
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
								fieldLabel: 'Затраченное на выполнения вызова',
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
								xtype: 'swsmpunitscombo',
								fieldLabel: 'Станция (подстанция), отделения',
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
										var MedStaffFact_id = base_form.findField('MedStaffFact_id');
										var EmergencyTeam_id = base_form.findField('EmergencyTeam_id');

										//фильтр врачей по подстанции
										if (base_form.findField('TransTime').getStringValue() != '') {
											var time_start = Date.parseDate( base_form.findField('TransTime').getStringValue(), 'd.m.Y H:i' );
										} else {
											var time_start = new Date();
										}
										var onDate = Ext.util.Format.date(time_start, 'd.m.Y');

										var filterParams = {
											LpuBuildingType_id: 27,
											withoutLpuSection: true,
											LpuBuilding_id: newLpuBuilding,
											onDate: onDate // не уволены
										};
										MedStaffFact_id.reset();
										MedStaffFact_id.baseFilterFn = setMedStaffFactGlobalStoreFilter(filterParams, MedStaffFact_id.store, true);
										//end фильтр врачей по подстанции

										//фильр бригад по подстанции
										EmergencyTeam_id.getStore().filterBy(function(record){
											return (record.get('LpuBuilding_id') == newLpuBuilding);
										});
										//end фильр бригад по подстанции

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
								xtype: 'hidden',
								name: 'LpuBuilding_IsPrint'
							},
							{
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
							},
							{
								xtype: 'container',
								autoEl: {},
								layout: 'form',
								items: [{
									xtype: 'swEmergencyTeamCCC',
									fieldLabel:	'Бригада скорой медицинской помощи',
									hiddenName: 'EmergencyTeam_id',
									allowBlank: true,
									width: 350,
									listWidth: 350,
									listeners: {
										select: function(combo,record,index){
											this.setByTypeOfPosition(record.get('EmergencyTeam_id'));
											var EmergencyTeamNum = me.FormPanel.getForm().findField('EmergencyTeamNum'),
												EmergencyTeamSpec = me.FormPanel.getForm().findField('EmergencyTeamSpec_id');

											if(EmergencyTeamSpec){
												rec = EmergencyTeamSpec.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));
												EmergencyTeamSpec.setValue(rec.get('EmergencyTeamSpec_id'));
											}

											if(EmergencyTeamNum) EmergencyTeamNum.setValue(record.get('EmergencyTeam_Num'));
										}.createDelegate(this)
									}
								}]
							},
							{
								xtype: 'hidden',
								name: 'EmergencyTeamNum'
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
								allowBlank: false,
								dateFieldId: 'EVPLEF_EvnVizitPL_setDate',
								enableOutOfDateValidation: true,
								hiddenName: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								id: 'CMP_MedStaffFactRecCmb',
								lastQuery: '',
								listWidth: 600,
								parentElementId: 'CMP_LpuSectionCmb',
								width: 350,
								xtype: 'swmedstafffactglobalcombo',
								listeners: {
									select: function(combo, record, index){
										if (record.data.MedPersonal_id > 0) {
											var MedPersonalField = me.FormPanel.getForm().findField('MedPersonal_id');
											if(MedPersonalField) MedPersonalField.setValue(record.data.MedPersonal_id);
										}
									}

								}
							},
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
									allowTextInput: true,
									forceSelection: getRegionNick() != 'krym',
									listeners: {
										blur: function(c){
											var base_form = me.FormPanel.getForm();
											if(
												!c.store.getCount() || 
												c.store.findBy(function(rec) { return rec.get('StreetAndUnformalizedAddressDirectory_id') == c.getValue(); }) == -1 
											)
											{
												base_form.findField('UnformalizedAddressDirectory_id').setValue(null);
												base_form.findField('Street_id').setValue(null);
												base_form.findField('CmpCallCard_Ulic').setValue(c.getRawValue());
											}
										},
										beforeselect: function(combo, record) {
											if ( typeof record != 'undefined' ) { combo.setValue(record.get(combo.valueField)); }
											var base_form = me.FormPanel.getForm();
											base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
											base_form.findField('Street_id').setValue(record.get('KLStreet_id'));										
										}
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
										disabled: false,
										name: 'Fam',
										width: 180,
										toUpperCase: true,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											}
										}
									}, {
										fieldLabel: 'Имя',
										disabled: false,
										name: 'Name',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											}
										}
									}, {
										fieldLabel: 'Отчество',
										disabled: false,
										name: 'Middle',
										toUpperCase: true,
										width: 180,
										xtype: 'textfieldpmw',
										listeners: {
											change: function(){
												var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
												if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											}
										}
									},
									{
										fieldLabel: 'Серия полиса',
										disabled: false,
										name: 'Person_PolisSer',
										width: 180,
										xtype: 'textfield',
										editable: false
									},
									{
										fieldLabel: 'Номер полиса',
										disabled: false,
										name: 'Person_PolisNum',
										width: 180,
										xtype: 'textfield',
										editable: false
									},
									{
										fieldLabel: 'Единый номер',
										disabled: false,
										name: 'CmpCloseCard_PolisEdNum',
										width: 180,
										xtype: 'textfield',
										editable: false
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
											me.setMKB();
											
											var PersonFields_IsDirty = me.FormPanel.getForm().findField('PersonFields_IsDirty');
											if(PersonFields_IsDirty) PersonFields_IsDirty.setValue(true);
											
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
								disabled: false,
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
								disabled: false,
								width: 180,
								name: 'Work',
								fieldLabel: 'Место работы'
							}, {
								xtype: 'textfield',
								disabled: false,
								width: 180,
								name: 'DocumentNum',
								fieldLabel: 'Серия и номер документа, удостоверяющего личность'
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
						width: 350,
						listWidth: 300,
						editable: true,
						xtype: 'swreasoncombo',
						listeners: {
							change: function(cmp, newVal){
								var base_form = me.FormPanel.getForm(),
									radioGroupResultTrip = base_form.findField('resultEmergencyTrip'),
									diagField = base_form.findField('Diag_id'),
									reasonCode = this.store.getById(newVal).get('CmpReason_Code');

								//если повод - "ошибка" то делаем поле результат выезда необязательным и диагноз
								//иначе - обязательный
								if(reasonCode.inlist(['01!'])){
									radioGroupResultTrip.allowBlank = true;
									diagField.allowBlank = true;
								}
								else{
									radioGroupResultTrip.allowBlank = false;
									diagField.allowBlank = false;
								}
								radioGroupResultTrip.validate();
								diagField.validate();
							}
						}
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
                items      : [{
                    columns: 1,
                    vertical: true,
                    width: '100%',
                    xtype: 'checkboxgroup',
                    singleValue: true,
                    fieldLabel: ++me.panelNumber+ '. Место получения вызова бригадой скорой медицинской помощи',
                    items: this.getCombo('CallTeamPlace_id')
                }]
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
					listWidth: 300,
					allowBlank: false
				}]
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
				xtype: 'fieldset',
				autoHeight: true,
				frame: true,
				items: [{
					columns: 1,
					vertical: true,
					fieldLabel: ++me.panelNumber + '. Причина длительного доезда',
					width: '100%',
					xtype: 'checkboxgroup',
					items: this.getCombo('LongDirect_id')
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				frame: true,
				items: [{
					columns: 1,
					vertical: true,
					fieldLabel: ++me.panelNumber + '. Безрезультативный выезд',
					width: '100%',
					xtype: 'checkboxgroup',
					items: this.getCombo('DeportFail_id'),
					listeners:{
                        change: function(obj, el){
							//#96465
							var allowBlankFlag = false;
							if (el.length > 0) {
								allowBlankFlag = true;
							}
							me.FormPanel.getForm().findField('SocialCombo').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('SocialCombo').validate();

							me.FormPanel.getForm().findField('Diag_id').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('Diag_id').validate();

							me.FormPanel.getForm().findField('Result_id').allowBlank = allowBlankFlag;
							me.FormPanel.getForm().findField('Result_id').validate();
						}.createDelegate(this)
                    }
				}]
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
					fieldLabel   :  ++me.panelNumber + '. Травма',
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
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				hidden: true,
				frame	   : true,
				items      : [{
					columns: 1,
					vertical: true,
					fieldLabel	   : ++me.panelNumber + '. Причины выезда с опозданием',
					width: '100%',
					xtype: 'radiogroup',
					items: this.getCombo('Delay_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items      : [{
					columns: [500],
					vertical: true,
					fieldLabel	   : ++me.panelNumber + '. Состав бригады скорой медицинской помощи',
					width: '100%',
					xtype: 'checkboxgroup',
					items: this.getCombo('TeamComplect_id'),
					listeners: {
						render:function(cmp){
							var status = 'render';
							var ss=me;
							var base_form = me.FormPanel.getForm()
							cmp.items.each(function(a){
								if(a.xtype == 'swmedpersonalcombo'){
									a.store.load({
										params: {
											All_Rec: 2
										}
									});
								}
							})
						}
					}
				}]
			}

		];
	},

	//получение списка компонентов для вкладки Жалобы
	getJalobFields: function(){
		var me = this;

		return [
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items : [{
					fieldLabel	   : 'Аллергические реакции в анамнезе',
					columns: 1,
					vertical: true,
					width: '100%',
					xtype: 'checkboxgroup',
					singleValue: true,
					items: this.getCombo('Allergic_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				items : [{
					fieldLabel	   : 'Осмотр на педикулез',
					columns: 1,
					vertical: true,
					width: '100%',
					xtype: 'checkboxgroup',
					singleValue: true,
					items: this.getCombo('OsmPed_id')
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel: ++me.panelNumber + '. Тема беседы',
						name: 'CmpCloseCard_Topic',
						width: '90%',
						xtype: 'textarea',
						maxLength: 50
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				frame	   : true,
				labelWidth: 100,
				items      : [{
						fieldLabel	   : ++me.panelNumber + '. Жалобы',
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
						fieldLabel: ++me.panelNumber + '. Анамнез',
						name: 'Anamnez',
						width: '90%',
						xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Эпид. анамнез',
				frame	   : true,
				items      : [
					{
						layout	   : 'column',
						items: [
							{
								xtype      : 'panel',
								title	   : 'Эпид. анамнез',
								frame	   : true,
								width : '25%',
								height : 200,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('EpAnamez_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Посещение эпид. неблаг. стран и регионов за 3 года',
								frame	   : true,
								width : '25%',
								height : 200,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('EpCountry_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Инфекционные заболевания в анамнезе',
								frame	   : true,
								width : '25%',
								height : 200,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('EpInfect_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Инъекции, опер. вмешательства за последние 6 мес.',
								frame	   : true,
								width : '25%',
								height : 200,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('EpInject_id')
								}]
							}

						]
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Объективные данные',
				frame	   : true,
				items      : [
					{
						layout	   : 'column',
						items: [
							{
								xtype      : 'panel',
								title	   : 'Общее состояние',
								frame	   : true,
								width : '20%',
								height : 300,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Condition_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Поведение',
								frame	   : true,
								width : '20%',
								height : 300,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Behavior_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Сознание',
								frame	   : true,
								width : '20%',
								height : 300,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Cons_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Запах алкоголя',
								frame	   : true,
								width : '20%',
								height : 300,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('SmellOfAlc_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Кожные покровы ',
								frame	   : true,
								width : '20%',
								height : 300,
								items : [{
										columns: 2,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										items: this.getCombo('Kozha_id')
									},
									{
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 100,
										items : [
											{
												fieldLabel: 'Отеки',
												columns: 2,
												vertical: true,
												width: '100%',
												xtype: 'checkboxgroup',
												singleValue: true,
												items: this.getCombo('Hypostas_id')
											},
											{
												fieldLabel: 'Сыпь',
												columns: 2,
												vertical: true,
												width: '100%',
												xtype: 'checkboxgroup',
												singleValue: true,
												items: this.getCombo('Crop_id')
											},
											/*{
												fieldLabel: 'Отеки',
												hiddenName: 'ComboCheck_Hypostas_id',
												width: 40,
												comboSubject: 'YesNo',
												xtype: 'swcommonsprcombo'
											},
											{
												fieldLabel: 'Сыпь',
												hiddenName: 'Crop_id',
												width: 40,
												comboSubject: 'YesNo',
												xtype: 'swcommonsprcombo'
											}
											*/
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'Зрачки',
								frame	   : true,
								width : '20%',
								height : 200,
								layout: 'form',
								labelWidth: 20,
								items : [
									{
										frame: true,
										fieldLabel:'',
										labelSeparator: '',
										labelWidth: 20,
										columns: [
											100,
											20,
											47,
											20
										],
										xtype: 'checkboxgroup',
										singleValue: true,
										items: this.getCombo('Pupil_id')
									},
									{
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 100,
										items : [{
											fieldLabel: 'Нистагм',
											hiddenName: 'isNist',
											width: 40,
											comboSubject: 'YesNo',
											xtype: 'swcommonsprcombo'
										},
										{
											fieldLabel: 'Реакция на свет',
											hiddenName: 'isLight',
											width: 40,
											comboSubject: 'YesNo',
											xtype: 'swcommonsprcombo'
										}
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'Мышечный тонус',
								frame	   : true,
								width : '20%',
								height : 200,
								layout: 'form',
								labelWidth: 20,
								items : [{
									frame: true,
									fieldLabel:'',
									labelSeparator: '',
									labelWidth: 20,
									columns: [
										100,
										20,
										47,
										20
									],
									xtype: 'checkboxgroup',
									singleValue: true,
									items: this.getCombo('Muscular_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Очаговые неврологические симптомы',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
										columns: 1,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										singleValue: true,
										items: this.getCombo('Nevro_id')
									}, {
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 100,
										items : [{
											fieldLabel: 'Менингеальные знаки',
											hiddenName: 'isMenen',
											width: 40,
											comboSubject: 'YesNo',
											xtype: 'swcommonsprcombo'
										}]
									}
								]
							},
							{
							xtype      : 'panel',
								title	   : 'Одышка',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
										columns: 1,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										singleValue: true,
										items: this.getCombo('Shortwind_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Дыхание',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
										columns: 1,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										singleValue: true,
										items: this.getCombo('Hale_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Аускультация',
								frame	   : true,
								width : '20%',
								height : 350,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Auscultation_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Хрипы',
								frame	   : true,
								width : '20%',
								height : 350,
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
								title	   : 'Перкуссия',
								frame	   : true,
								width : '20%',
								height : 350,
								items : [
									{
										columns: 1,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										singleValue: true,
										items: this.getCombo('Percussion_id')
									},
									{
										xtype      : 'fieldset',
										autoHeight: true,
										title: 'Границы сердца',
										labelWidth: 20,
										items : [
											{
												columns: 1,
												hideLabel:true,
												vertical: true,
												width: '100%',
												xtype: 'checkboxgroup',
												singleValue: true,
												items: this.getCombo('BordersHeart_id')
											}
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'Пульс',
								frame	   : true,
								width : '20%',
								height : 350,
								items : [
									{
										columns: 2,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										items: this.getCombo('Pulse_id')
									},
									{
										xtype      : 'fieldset',
										autoHeight: true,
										title: 'Сердцебиение',
										labelWidth: 20,
										items : [
											{
												columns: 1,
												hideLabel:true,
												vertical: true,
												width: '100%',
												xtype: 'checkboxgroup',
												singleValue: true,
												items: this.getCombo('Heartbeat_id')
											}
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'Тоны сердца',
								frame	   : true,
								width : '20%',
								height : 350,
								items : [
									{
										columns: 1,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										items: this.getCombo('Heart_id')
									},
									{
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 100,
										items : [{
											fieldLabel: 'Шум',
											hiddenName: 'CmpCloseCard_IsHeartNoise',
											width: 40,
											comboSubject: 'YesNo',
											xtype: 'swcommonsprcombo'
										}]
									}

								]
							},
							{
								xtype      : 'panel',
								title	   : 'Язык',
								frame	   : true,
								width : '20%',
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
								title	   : 'Ротоглотка',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
									columns: 1,
									vertical: true,
									width: '100%',
									xtype: 'checkboxgroup',
									items: this.getCombo('Fauces_id')
								}]
							},
							{
								xtype      : 'panel',
								title	   : 'Живот',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
										columns: 2,
										vertical: true,
										width: '100%',
										xtype: 'checkboxgroup',
										items: this.getCombo('Gaste_id')
									},
									{
										xtype      : 'fieldset',
										labelWidth: 160,
										autoHeight: true,
										items : [
											{
												fieldLabel: 'Участвует в акте дыхания',
												hiddenName: 'isHale',
												comboSubject: 'YesNo',
												width: 40,
												xtype: 'swcommonsprcombo'
											},
											{
												fieldLabel: 'Кишечные шумы',
												hiddenName: 'CmpCloseCard_IsIntestinal',
												comboSubject: 'YesNo',
												width: 40,
												xtype: 'swcommonsprcombo'
											},
											{
												fieldLabel: 'Симптомы раздр. брюшины',
												hiddenName: 'isPerit',
												comboSubject: 'YesNo',
												width: 40,
												xtype: 'swcommonsprcombo'
											}
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'Печень',
								frame	   : true,
								width : '20%',
								height : 200,
								items: this.getCombo('Liver_id')
							},
							{
								xtype      : 'panel',
								title	   : 'Прочие нарушения',
								frame	   : true,
								width : '20%',
								height : 200,
								items : [{
									xtype      : 'fieldset',
									labelWidth: 160,
									autoHeight: true,
									items : [
										{
											fieldLabel: 'Рвота',
											hiddenName: 'CmpCloseCard_IsVomit',
											comboSubject: 'YesNo',
											width: 40,
											xtype: 'swcommonsprcombo'
										},
										{
											fieldLabel: 'Нарушения диуреза',
											hiddenName: 'CmpCloseCard_IsDiuresis',
											comboSubject: 'YesNo',
											width: 40,
											xtype: 'swcommonsprcombo'
										},
										{
											fieldLabel: 'Нарушения дефекации',
											hiddenName: 'CmpCloseCard_IsDefecation',
											comboSubject: 'YesNo',
											width: 40,
											xtype: 'swcommonsprcombo'
										},
										{
											fieldLabel: 'Травмы, повреждения',
											hiddenName: 'CmpCloseCard_IsTrauma',
											comboSubject: 'YesNo',
											width: 40,
											xtype: 'swcommonsprcombo'
										}
									]
								}]
							}
						]
					}
				]
			},
			{
				xtype      	: 'fieldset',
				autoHeight	: true,
				frame	  	: true,
				labelWidth: 100,
				items : [{
					fieldLabel: 'Другие симптомы',
					name: 'OtherSympt',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight : true,
				frame	  	: true,
				labelWidth: 100,
				items : [{
					fieldLabel: 'Дополнительные данные',
					name: 'CmpCloseCard_AddInfo',
					width: '90%',
					xtype: 'textarea'
				}]
			},
			{
				xtype      : 'fieldset',
				autoHeight : true,
				title	   : ++me.panelNumber + '. Обследования в динамике',
				frame	   : true,
				items      : [
					{
						layout	   : 'column',
						items: [
							{
								xtype      : 'panel',
								title	   : 'До лечения',
								frame	   : true,
								width : '50%',
								height : 450,
								items : [
									{
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 250,
										items : [
											{
												fieldLabel: 'Время до лечения',
												timeLabelWidth1: 250,
												name: 'CmpCloseCard_BegTreatDT',
												//id: this.id+'_'+'CmpCloseCardWhere_DT',
												plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
												xtype: 'swtimefield'
											},
											{
												xtype: 'container',
												autoEl: {},
												layout: 'column',
												items:
												[
													{
														xtype: 'fieldset',
														border: false,
														autoHeight: true,
														width: 355,
														items: [{
															fieldLabel: 'Рабочее АД, мм.рт.ст.',
															name: 'sub1WorkAD',
															width: 60,
															xtype: 'textfield',
															maskRe: /\d/,
															maxLength:3,
															listeners: {
																'blur': function(me){
																	var baseform = this.FormPanel.getForm(),
																		workadfield = baseform.findField('WorkAD'),
																		workad2field = baseform.findField('sub2WorkAD');

																	workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
																}.createDelegate(this)
															}
														}]
													},
													{
														xtype: 'label',
														text: '/'
														//style: 'padding: 0 10px;'
													},
													{
														xtype: 'textfield',
														name: 'sub2WorkAD',
														width: 65,
														maskRe: /\d/,
														maxLength:3,
														style: 'margin: 0 0 0 10px;',
														listeners: {
															'blur': function(me){
																var baseform = this.FormPanel.getForm(),
																	workadfield = baseform.findField('WorkAD'),
																	workad1field = baseform.findField('sub1WorkAD');

																workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
															}.createDelegate(this)
														}
													}
												]
											},
											{
												name: 'WorkAD',
												xtype: 'hidden'
											},
											{
												xtype: 'container',
												autoEl: {},
												layout: 'column',
												items:
												[
													{
														xtype: 'fieldset',
														border: false,
														autoHeight: true,
														width: 355,
														items: [{
															fieldLabel: 'АД, мм.рт.ст.',
															name: 'sub1AD',
															width: 60,
															xtype: 'textfield',
															maskRe: /\d/,
															maxLength:3,
															listeners: {
																'blur': function(me){
																	var baseform = this.FormPanel.getForm(),
																		workadfield = baseform.findField('AD'),
																		workad2field = baseform.findField('sub2AD');

																	workadfield.setValue(me.getValue()+'/'+workad2field.getValue());
																}.createDelegate(this)
															}
														}]
													},
													{
														xtype: 'label',
														text: '/'
														//style: 'padding: 0 10px;'
													},
													{
														xtype: 'textfield',
														name: 'sub2AD',
														width: 65,
														maskRe: /\d/,
														maxLength:3,
														style: 'margin: 0 0 0 10px;',
														listeners: {
															'blur': function(me){
																var baseform = this.FormPanel.getForm(),
																	workadfield = baseform.findField('AD'),
																	workad1field = baseform.findField('sub1AD');

																workadfield.setValue(workad1field.getValue()+'/'+me.getValue());
															}.createDelegate(this)
														}
													}
												]
											},
											{
												fieldLabel: 'АД, мм.рт.ст.',
												name: 'AD',
												xtype: 'hidden'
											},
											{
												fieldLabel: 'ЧСС, мин.',
												name: 'Chss',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:3
											},
											{
												fieldLabel: 'Температура',
												name: 'Temperature',
												xtype: 'textfield',
												plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
											},
											{
												fieldLabel: 'ЧД, мин.',
												name: 'Chd',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:3
											},
											{
												fieldLabel: 'Глюкометрия',
												name: 'Gluck',
												xtype: 'textfield',
												plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
											},
											{
												fieldLabel: 'ЭКГ',
												name: 'Ekg1',
												maxLength:150,
												width: '90%',
												xtype: 'textarea'
											},
											{
												fieldLabel: 'SatO2, % ',
												name: 'CmpCloseCard_Sat',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:4
											},
											{
												fieldLabel: 'Ритм',
												name: 'CmpCloseCard_Rhythm',
												xtype: 'textfield',
												maxLength:25
											},
											{
												fieldLabel: 'Оценка по шкале Глазго',
												name: 'CmpCloseCard_Glaz',
												width: 90,
												xtype: 'textfield',
												editable: false
											},
											{
												xtype: 'combo',
												fieldLabel: 'E',
												name: 'CmpCloseCard_e1',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4]]}),
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_Glaz'),
															eField = baseform.findField('CmpCloseCard_e1').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m1').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v1').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												},
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90
											},
											{
												xtype: 'combo',
												fieldLabel: 'М',
												name: 'CmpCloseCard_m1',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4],[5],[6]]}),
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90,
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_Glaz'),
															eField = baseform.findField('CmpCloseCard_e1').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m1').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v1').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												}
											},
											{
												xtype: 'combo',
												fieldLabel: 'V',
												name: 'CmpCloseCard_v1',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4],[5]]}),
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90,
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_Glaz'),
															eField = baseform.findField('CmpCloseCard_e1').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m1').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v1').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												}
											}
										]
									}
								]
							},
							{
								xtype      : 'panel',
								title	   : 'После лечения',
								frame	   : true,
								width : '50%',
								height : 450,
								items : [
									{
										xtype      : 'fieldset',
										autoHeight: true,
										labelWidth: 250,
										items : [
											{
												fieldLabel: 'Время после лечения',
												timeLabelWidth1: 250,
												plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
												name: 'CmpCloseCard_EndTreatDT',
												//id: this.id+'_'+'CmpCloseCardWhere_DT',
												xtype: 'swtimefield'
											},
											{
												xtype: 'container',
												autoEl: {},
												height: 20,
												layout: 'column',
												items: []
											},
											{
												xtype: 'container',
												autoEl: {},
												layout: 'column',
												items:
												[
													{
														xtype: 'fieldset',
														border: false,
														autoHeight: true,
														width: 355,
														items: [{
															fieldLabel: 'АД, мм.рт.ст.',
															name: 'sub1EAD',
															width: 60,
															xtype: 'textfield',
															maskRe: /\d/,
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
														text: '/'
														//style: 'padding: 0 10px;'
													},
													{
														xtype: 'textfield',
														name: 'sub2EAD',
														width: 65,
														maskRe: /\d/,
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
												fieldLabel: 'ЧСС, мин.',
												name: 'EfChss',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:3
											},
											{
												fieldLabel: 'Температура',
												name: 'EfTemperature',
												xtype: 'textfield',
												plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
											},
											{
												fieldLabel: 'ЧД, мин.',
												name: 'EfChd',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:3
											},
											{
												fieldLabel: 'Глюкометрия',
												name: 'EfGluck',
												xtype: 'textfield',
												plugins: [ new Ext.ux.InputTextMask('99.9', true) ]
											},
											{
												fieldLabel: 'ЭКГ',
												name: 'Ekg2',
												maxLength:150,
												width: '90%',
												xtype: 'textarea'
											},
											{
												fieldLabel: 'SatO2, % ',
												name: 'CmpCloseCard_AfterSat',
												xtype: 'textfield',
												maskRe: /\d/,
												maxLength:4
											},
											{
												fieldLabel: 'Ритм',
												name: 'CmpCloseCard_AfterRhythm',
												xtype: 'textfield',
												maxLength:25
											},
											{
												fieldLabel: 'Оценка по шкале Глазго',
												name: 'CmpCloseCard_GlazAfter',
												width: 90,
												xtype: 'textfield',
												editable: false
											},
											{
												xtype: 'combo',
												fieldLabel: 'E',
												name: 'CmpCloseCard_e2',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4]]}),
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90,
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_GlazAfter'),
															eField = baseform.findField('CmpCloseCard_e2').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m2').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v2').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												}
											},
											{
												xtype: 'combo',
												fieldLabel: 'М',
												name: 'CmpCloseCard_m2',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4],[5],[6]]}),
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90,
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_GlazAfter'),
															eField = baseform.findField('CmpCloseCard_e2').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m2').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v2').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												}
											},
											{
												xtype: 'combo',
												fieldLabel: 'V',
												name: 'CmpCloseCard_v2',
												store: new Ext.data.SimpleStore({fields: ['num'],data : [[1],[2],[3],[4],[5]]}),
												displayField:'num',
												triggerAction: 'all',
												selectOnFocus:true,
												editable: false,
												mode: 'local',
												width: 90,
												listeners: {
													'change': function(c, newValue, oldValue){
														var baseform = this.FormPanel.getForm(),
															mainField = baseform.findField('CmpCloseCard_GlazAfter'),
															eField = baseform.findField('CmpCloseCard_e2').getValue() || 0,
															mField = baseform.findField('CmpCloseCard_m2').getValue() || 0,
															vField = baseform.findField('CmpCloseCard_v2').getValue() || 0;

														mainField.setValue( parseInt(eField)+parseInt(mField)+parseInt(vField) );
													}.createDelegate(this)
												}
											}
										]
									}
								]
							}
						]
					}
				]
			}
		];
	},

	//получение списка компонентов для вкладки Диагноз
	getDiagnozFields: function(){
		var me = this;
		this.diag_sid_panel = new sw.Promed.swMultiFieldPanel({
			label: lang['soputstvuyuschiy_diagnoz'],
			deleteBtnText: '',
			id: 'diag_sid_panel',
			panelFiledName: 'Diag_sid',
			hiddenDelAll: true,
			firstColWidth: 434,
			//border: false,
			frame: false,
			buttons: [{
				style: 'margin-left: 97px;',
				handler: function() {
					this.ownerCt.addField();
				},
				iconCls: 'add16',
				text: lang['dobavit']
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: lang['soputstvuyuschiy_diagnoz'],
					hiddenName: 'Diag_sid'+counter,
					labelStyle: 'width: 141px;',
					width: 290,
					checkAccessRights: true,
					xtype: 'swdiagcombo',
					autoShow: true,
					withGroups: false,
					disabledClass: 'field-disabled',
					MKB: {
						isMain: true
					}
				};

				if (this.firstTabIndex) {
					conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
				}
				if (this.PrescriptionType_Code) {
					conf_combo.PrescriptionType_Code = this.PrescriptionType_Code;
					var c = new sw.Promed.SwDiagCombo(conf_combo);
				} else {
					conf_combo.onTrigger2Click = function () {
						if (this.disabled) {
							return;
						}
						this.clearValue();
						this.fireEvent('change', this, this.getValue());
					};

					var c = new sw.Promed.SwDiagCombo(conf_combo);
				}
				return c;
			},
			onFieldAdd: function (data) {
				if(data.value > 0){
					data.getStore().load({
						scope: data,
						params: {where: "where Diag_id = " + data.value},
						callback: function() {
							this.setValue(data.value);
						}
					})
				};
			},
			onFieldDelete: function (data) {},
			onResetPanel: function () {},
			getData: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push(arrfields[i].getValue());
				}
				return res_arr;
			},
			getIDsAndCodes: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					var field = arrfields[i];
					var rec = field.getStore().getById(arrfields[i].getValue());
					if(rec){
						var diag_code = rec.get('Diag_Code').substr(0, 3); }
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push({id: arrfields[i].getValue(), code: diag_code});
				}
				return res_arr;
			}
		});
		this.diag_ooid_panel = new sw.Promed.swMultiFieldPanel({
			label: lang['oslojnenie_osnovnogo'],
			deleteBtnText: '',
			id: 'diag_ooid_panel',
			hiddenDelAll: true,
			panelFiledName: 'Diag_ooid',
			firstColWidth: 434,
			frame: false,
			buttons: [{
				style: 'margin-left: 97px;',
				handler: function() {
					this.ownerCt.addField();
				},
				iconCls: 'add16',
				text: lang['dobavit']
			}, {
				text: '-'
			}],
			createField: function (counter) {
				var conf_combo = {
					value: null,
					fieldLabel: lang['oslojnenie_osnovnogo'],
					hiddenName: 'Diag_ooid'+counter,
					labelStyle: 'width: 141px;',
					width: 290,
					checkAccessRights: true,
					xtype: 'swdiagcombo',
					autoShow: true,
					withGroups: false,
					disabledClass: 'field-disabled',
					MKB: {
						isMain: true
					}
				};

				if (this.firstTabIndex) {
					conf_combo.tabIndex = this.firstTabIndex + this.lastItemsIndex;
				}
				if (this.PrescriptionType_Code) {
					conf_combo.PrescriptionType_Code = this.PrescriptionType_Code;
					var c = new sw.Promed.SwDiagCombo(conf_combo);
				} else {
					conf_combo.onTrigger2Click = function () {
						if (this.disabled) {
							return;
						}
						this.clearValue();
						this.fireEvent('change', this, this.getValue());
					};

					var c = new sw.Promed.SwDiagCombo(conf_combo);
				}
				return c;
			},
			onFieldAdd: function (data) {
				if(data.value > 0){
					data.getStore().load({
						scope: data,
						params: {where: "where Diag_id = " + data.value},
						callback: function() {
							this.setValue(data.value);
						}
					})
				};
			},
			onFieldDelete: function (data) {
			},
			onResetPanel: function () {
			},
			getData: function () {
				var res_arr = new Array();
				var arrfields = this.findByType('swdiagcombo');
				for(var i=0;i<arrfields.length;i++){
					if(!Ext.isEmpty(arrfields[i].getValue()))
						res_arr.push(arrfields[i].getValue());
				}
				return res_arr;
			}
		});
		return [
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Диагноз',
				cls		   : 'diags-panel-close-card',
				frame	   : true,
				labelWidth: 200,
				items: [{
					columns: 2,
					layout	   : 'column',
					width:'100%',
					items: [
						{
							xtype: 'fieldset',
							style: "padding-left: 50px;",
							border: false,
							autoHeight: true,
							width: 700,
							items:[
								{
								checkAccessRights: true,
								hiddenName: 'Diag_id',
								xtype: 'swdiagcombo',
									labelStyle: 'width: 50px;',
									width: 274,
								allowBlank: false,
								withGroups: false,
								disabledClass: 'field-disabled',
								MKB: {
									isMain: true
								}
						},
								this.diag_sid_panel,
								this.diag_ooid_panel
							]
								}
					]
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Осложнения',
				frame	   : true,
				items      : [{
						columns: 3,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						items: this.getCombo('Complicat_id')
				}]
			},
            {
                xtype      : 'panel',
                title	   : ++me.panelNumber + '. Эффективность мероприятий при осложнении',
                frame	   : true,
                items      : [{
                    columns: 3,
                    vertical: true,
                    width: '100%',
                    xtype: 'checkboxgroup',
                    singleValue: true,
                    items :this.getCombo('ComplicatEf_id')
                }]
			}
		]
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
				title	   : ++me.panelNumber + '. Оказанная помощь',
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
								fieldLabel: 'Время',
								timeLabelWidth1: 150,
								name: 'CmpCloseCard_HelpDT',
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								xtype: 'swtimefield'
							},
							{
									name: 'HelpAuto',
									width: '99%',
									xtype: 'textarea',
									fieldLabel: 'Оказанная помощь'
							},
							{
									name: 'CmpCloseCard_ClinicalEff',
									width: '99%',
									xtype: 'textarea',
									fieldLabel: 'Клинический эффект'
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
				items: [me.UslugaPanel]
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
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Отказ от медицинского вмешательства',
				frame	   : true,
				items      : [{
					columns: 1,
					vertical: true,
					width: '100%',
					xtype: 'checkboxgroup',
					items: this.getCombo('RejectionMed_id')
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат оказания скорой медицинской помощи',
				frame	   : true,
				items      : [{
					//columns: [100, 100, 100, 150, 200, 600],
					columns: [600],
					vertical: true,
					width: '100%',
					allowBlank: false,
					xtype: 'checkboxgroup',
					singleValue: true,
					items: this.getCombo('Result_id'),
					name: 'Result_id'
					//убииить id: this.id+'_ResultSmp'
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Способ доставки больного в автомобиль скорой медицинской помощи',
				frame	   : true,
				items      : [{
						columns: 1,
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items :this.getCombo('TransToAuto_id')
				}]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Результат выезда',
				frame	   : true,
				autoHeight: true,
				items      : [
					{
						columns: [600],
						vertical: true,
						width: '100%',
						allowBlank: false,
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('ResultUfa_id'),
						disabledClass: 'field-disabled',
						id: this.id+'_ResultId'
					}
				]
			},
			{
				xtype      : 'panel',
				title	   : ++me.panelNumber + '. Прочее',
				frame	   : true,
				autoHeight: true,
				items      : [
					{
						columns: [600],
						vertical: true,
						width: '100%',
						xtype: 'checkboxgroup',
						singleValue: true,
						items: this.getCombo('ResultOther_id')
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				labelWidth : 100,
				border: true,
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
			},
			{

				xtype: 'fieldset',
				border: false,
				autoHeight: true,
				width: '95%',
				labelWidth : 100,
				items: [
					{
						name: 'DescText',
						width: '99%',
						xtype: 'textarea',
						fieldLabel: 'Примечание'
					}
				]
			},
			{
				xtype: 'fieldset',
				border: false,
				autoHeight: true,
				width: '95%',
				labelWidth : 100,
				items: [{					
					hiddenName: 'MedStaffFact_cid',
					allowBlank: true,
					name: 'MedStaffFact_cid',					
					fieldLabel: 'Проверяющий врач',
					listWidth: 600,					
					width: 350,
					xtype: 'swmedstafffactglobalcombo'
				}]
			}
		]
	},

	// Валидация формы перед сохранением
	/* Не должно быть здесь
	doValidate: function(callback){
		var me = this,
			has_error = false,
			error = '',
			base_form = me.FormPanel.getForm(),
			diagField = base_form.findField('Diag_id'),
			payTypeCombo = base_form.findField('PayType_id');

		//номер телефона обязателен при выборе "подлежит активному посещению врачем" во вкладке "Результат"
		var chekActiv = Ext.getCmp(this.id+'_'+'CMPCLOSE_CB_645').checked;

		base_form.findField('Phone').allowBlank = !chekActiv;
		base_form.findField('Phone').validate();


		if ( !base_form.isValid() ) {
			has_error = true;
			error = ERR_INVFIELDS_MSG;
		}
		//там был еще else

		error += this.timeBlockValidate();

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
		} else {
			var Person_IsUnknown = base_form.findField('Person_IsUnknown'),
				PersonFields_IsDirty = base_form.findField('PersonFields_IsDirty');

			if(Person_IsUnknown && PersonFields_IsDirty && Person_IsUnknown.getValue() == '2' && PersonFields_IsDirty.getValue() == 'true'){
				me.checkPersonIdentification();
				this.formStatus = 'edit';
				callback(false);
				return;
			}

			// Проверка указанного пациента непосредственно перед сохранением
			if ( base_form.findField('Person_id').getValue() == 0 ||
				base_form.findField('Person_id').getValue() == null ||
				base_form.findField('Person_id').getValue() == ''
			) {
				if ( !confirm('Данный пациент не обнаружен в базе данных пациентов РМИАС. Для оплаты карты вызова СМП, пациента необходимо добавить в базу данных пациентов РМИАС. Продолжить сохранение?') )
				{
					this.formStatus = 'edit';
					callback(false);
				}else{
					callback(true);
				}
			} else {
				if (!Ext.isEmpty(diagField.getFieldValue('Sex_Code')) && diagField.getFieldValue('Sex_Code') != base_form.findField('Sex_id').getFieldValue('Sex_Code')) {
					sw.swMsg.alert(lang['oshibka'], 'Указанный диагноз не соответствует полу пациента. Сохранение невозможно!');
					callback(false);
					return;
				}
				if ( (payTypeCombo.getValue() == 171) && (diagField.getValue())	){
					Ext.Ajax.request({
						params: {
							Diag_id: base_form.findField('Diag_id').getValue(),
							Person_id: base_form.findField('Person_id').getValue(),
							PayType_id: base_form.findField('PayType_id').getValue()
						},
						url: '/?c=CmpCallCard&m=checkDiagFinance',
						callback: function (obj, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.DiagFinance_IsOms == 0 || (response_obj.Diag_Sex != null && response_obj.Diag_Sex != response_obj.Sex_id)) {
									if (!confirm('Внимание! Введенный диагноз для данного пациента не оплачивается по ОМС. Продолжить сохранение?')) {
										callback(false);
										//return false;
									}
									else {
										callback(true);
									}
								} else {
									callback(true);
								}
							}
							else{
								callback(false);
							}
						}
					});
				}else{
					callback(true);
				}
			}
		}

	},

	timeBlockValidate: function(){
		var timeBlock = Ext.getCmp('timeBlock'),
			timeBlockItems = timeBlock.items.items,
			error = '',
			errfields = '',
			allowBlankFields = ['TransportTime','CmpCloseCard_TranspEndDT','BackTime'];

		if (
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_128').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_129').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_130').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_135').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_602').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_603').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_604').getValue() ||
			Ext.getCmp(this.id + '_' + 'CMPCLOSE_CB_605').getValue()
		) {
			allowBlankFields.push('TransTime');
			allowBlankFields.push('GoTime');
			allowBlankFields.push('ArriveTime');
			allowBlankFields.push('TransportTime');
			allowBlankFields.push('CmpCloseCard_TranspEndDT');
			allowBlankFields.push('EndTime');
			allowBlankFields.push('BackTime');
		};
		for(var i=0;i<timeBlockItems.length-1; i++){
			var dateTimeField = timeBlockItems[i],
				fieldLabel = dateTimeField.fieldLabel || dateTimeField.dateLabel,
				fieldDate = new Date(dateTimeField.hiddenField.value),
				now = new Date();

			if(fieldDate > now)
				errfields += (errfields.length?', ':'') + 'Дата ' + fieldLabel.toLowerCase().replace('время ', '');
			if(!dateTimeField.hiddenName.inlist(allowBlankFields)){
				if ( !fieldDate.isValid() ) {
					error += (error.length?'<br />':'') + 'Заполните поле ' + fieldLabel;
				}
				//сравнение с предыдущими полями
				if(i>0){
					var prevField = timeBlockItems[i-1],
						prevDate = new Date(prevField.hiddenField.value),
						prevFieldLabel = prevField.fieldLabel || prevField.dateLabel;

					if( prevDate > fieldDate ){
						dateTimeField.ensureVisible().focus();
						prevFieldLabel = prevFieldLabel.toLowerCase().replace('время', 'времени');
						error += (error.length?'<br />':'')+ fieldLabel.toLowerCase()+' не может совершиться раньше ' + prevFieldLabel;
					}
				}
			}
		}
		if(errfields.length)
			errfields += ' не может быть больше текущей даты.<br />';
		error = errfields + error;
		return error;
	},
	*/

	//где пояснение функции? setByTypeOfPosition - "установить по типу позиции" что это?
	setByTypeOfPosition: function(EmergencyTeam_id){
		if(!EmergencyTeam_id) return false;
		var me = this;
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=getEmergencyTeamPostKind',
			params: {
				EmergencyTeam_id: EmergencyTeam_id
			},
			success: function(response, opts){
				var response_obj = Ext.util.JSON.decode(response.responseText)
				if(response_obj[0]){
					var associate = response_obj[0]

					var check713 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_713');
					var check174 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_174');
					var check606 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_606');
					var check607 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_607');
					var check178 = Ext.getCmp(me.id+'_'+'CMPCLOSE_CB_178');

					var	cb714 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_714'); // Фельдшер (Старший бригады) /вкладка "повод к вызову"/
					var	cb674 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_674'); // Врач /вкладка "повод к вызову"/
					var	cb675 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_675'); // Фельдшер /вкладка "повод к вызову"/
					var	cb676 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_676'); // Фельдшер,мед. сестра /вкладка "повод к вызову"/
					var	cb677 = Ext.getCmp(me.id+'_'+'CMPCLOSE_ComboValue_677'); // Водитель /вкладка "повод к вызову"/

					if(cb714 && cb714.hidden == false) cb714.clearValue();
					if(cb674 && cb674.hidden == false) cb674.clearValue();
					if(cb675 && cb675.hidden == false) cb675.clearValue();
					if(cb676 && cb676.hidden == false) cb676.clearValue();

					if(associate.EmergencyTeam_HeadShift && associate.PostKindHeadShift_Code){
						if(associate.PostKindHeadShift_Code == 1){
							//если вид должности сотрудника "врач"
							if(check713) check713.setValue(false);
							if(check174) check174.setValue(true);
							if(cb674) cb674.setValue(associate.EmergencyTeam_HeadShift);
						}else{
							if(check174) check174.setValue(false);
							if(check713) check713.setValue(true);
							if(cb714) cb714.setValue(associate.EmergencyTeam_HeadShift);
						}
					}else{
						if(check174) check174.setValue(false);
						if(check713) check713.setValue(false);
					}

					if(associate.EmergencyTeam_HeadShift2){
						// помошник1
						if(check606) check606.setValue(true);
						if(cb675) cb675.setValue(associate.EmergencyTeam_HeadShift2);
					}else{
						if(check606) check606.setValue(false);
					}

					if(associate.EmergencyTeam_Assistant1){
						if(check607) check607.setValue(true);
						if(cb676) cb676.setValue(associate.EmergencyTeam_Assistant1);
					}else{
						if(check607) check607.setValue(false);
					}

					if(associate.EmergencyTeam_Driver){
						if(check178) check178.setValue(true);
						if(cb677) cb677.setValue(associate.EmergencyTeam_Driver);
					}else{
						if(check178) check178.setValue(false);
					}
				}
			}
		});
	}
});