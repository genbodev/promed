/**
 * swCmpCallCardNewCloseCardWindow Наследник карты закрытия вызова
 * Спецификация Пермь
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
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
								width: 800,
								style: 'padding: 0px',
								items: [
									{
										fieldLabel: lang['povtornaya_podacha'],
										listeners: {
											'check': function(checkbox, value) {
												if ( getRegionNick() != 'perm' ) {
													return false;
												}

												var base_form = this.FormPanel.getForm();

												var
													CmpCallCard_IndexRep = parseInt(base_form.findField('CmpCallCard_IndexRep').getValue()),
													CmpCallCard_IndexRepInReg = parseInt(base_form.findField('CmpCallCard_IndexRepInReg').getValue()),
													CmpCloseCard_IsPaid = parseInt(base_form.findField('CmpCallCard_IsPaid').getValue());

												var diff = CmpCallCard_IndexRepInReg - CmpCallCard_IndexRep;

												if ( CmpCloseCard_IsPaid != 2 || CmpCallCard_IndexRepInReg == 0 ) {
													return false;
												}

												if ( value == true ) {
													if ( diff == 1 || diff == 2 ) {
														CmpCallCard_IndexRep = CmpCallCard_IndexRep + 2;
													}
													else if ( diff == 3 ) {
														CmpCallCard_IndexRep = CmpCallCard_IndexRep + 4;
													}
												}
												else if ( value == false ) {
													if ( diff <= 0 ) {
														CmpCallCard_IndexRep = CmpCallCard_IndexRep - 2;
													}
												}

												base_form.findField('CmpCallCard_IndexRep').setValue(CmpCallCard_IndexRep);

											}.createDelegate(this)
										},
										name: 'CmpCallCard_RepFlag',
										xtype: 'checkbox'
									},
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
										regex: /\d/,
										allowBlank: false,
										autoCreate: {tag: "input", type: "text", maxLength: "15", autocomplete: "off"},
										validator: function(a){return (a.match(/^[1-9]\d{0,15}$/))?true:false;}
									}, {
										fieldLabel: 'Номер вызова за год',
										name: 'Year_num',
										xtype: 'textfield',
										regex: /\d/,
										allowBlank: false,
										autoCreate: {tag: "input", type: "text",  maxLength: "15", autocomplete: "off"},
										validator: function(a){return (a.match(/^[1-9]\d{0,15}$/))?true:false;}
									}
								]
							},
							{
								border: false,
								labelWidth: 50,
								layout: 'form',
								items: [
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
						xtype      : 'fieldset',
						id : 'timeBlock',
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
									me.loadEmergencyTeamsWorkedInATime();
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
									blur: function(cmp, b, c) {
										if (!cmp.getValue()) {
											var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6,
												tabMed = me.tabPanel.getItem(indTab),
												txt = 'Для ввода медикаментов необходимо заполнить поле “Номер станции (подстанции), отделения"';

											if (getRegionNick().inlist(['perm', 'krym', 'buryatiya', 'astra', 'kareliya', 'hakasiya'])) {
												txt = 'Для ввода медикаментов необходимо заполнить поле “Станция (подстанция), отделение"';
											};

											tabMed.removeAll();
											tabMed.add({
												html: txt,
												style: 'margin-top: 10px; text-align: center; font-size: 16px;',
												height: 700
											});
											tabMed.doLayout();
										}
									},
									beforeselect: function (combo,record,index) {
										var base_form = me.FormPanel.getForm();
										// форма расхода медикаментов должна зависеть от настроек подразделения, которое выбрано в Карте вызова
										var idLpuBuilding = combo.getValue();
										var newLpuBuilding = record.get("LpuBuilding_id");
										var LBIsWithoutBalance = base_form.findField('LpuBuilding_IsWithoutBalance').getValue();

										var indTab = ( getRegionNick().inlist(['ufa']) ) ? 7 : 6;
										var tabMed=me.tabPanel.getItem(indTab);

										if (tabMed) {
											tabMed.removeAll();
											tabMed.add({items: me.getDrugFields(newLpuBuilding)});
											me.tabPanel.doLayout();
										}

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
								items: [{
									xtype: 'swEmergencyTeamCCC',
									fieldLabel:	'Бригада скорой медицинской помощи',
									hiddenName: 'EmergencyTeam_id',
									allowBlank: true,
									width: 350,
									listWidth: 350,
									listeners: {
										select: function(combo,record,index){
											var EmergencyTeamNum = me.FormPanel.getForm().findField('EmergencyTeamNum'),
												EmergencyTeamSpec = me.FormPanel.getForm().findField('EmergencyTeamSpec_id'),
												rec = EmergencyTeamSpec.findRecord('EmergencyTeamSpec_Code', record.get('EmergencyTeamSpec_Code'));

											if(rec)EmergencyTeamSpec.setValue(rec.get('EmergencyTeamSpec_id'));

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
								name: 'LpuBuilding_IsWithoutBalance',
								xtype: 'hidden',
								hiddenId: 'LpuBuilding_IsWithoutBalance'
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
										xtype: 'swcustomobjectcombo',
										listeners: {
											change: function() {
												if(!me.existOsmUslugaComplex()){
													me.addOsmUslugaComplex()
												}
											}
										}
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
											var medPersField = this.FormPanel.getForm().findField('MedPersonal_id');
											if(medPersField) medPersField.setValue(record.data.MedPersonal_id);
										}
									}.createDelegate(this)
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
								xtype: 'swregioncombo',
								name: 'KLRgn_id',
								allowBlank: true,
								hiddenName: 'KLRgn_id',
								width: 300
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
								disabled: false,
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
								disabled: false,
								fieldLabel: 'Корпус',
								//name: 'CmpCallCard_Dom',
								name: 'Korpus',
								width: 100,
								xtype: 'textfield'
							}, {
								disabled: false,
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
								disabled: false,
								disabledClass: 'field-disabled',
								fieldLabel: 'Комната',
								//name: 'CmpCallCard_Kvar',
								name: 'Room',
								width: 100,
								xtype: 'textfield'
							}, {
								disabled: false,
								disabledClass: 'field-disabled',
								fieldLabel: 'Подъезд',
								//name: 'CmpCallCard_Podz',
								name: 'Entrance',
								width: 100,
								xtype: 'textfield'
							}, {
								disabled: false,
								disabledClass: 'field-disabled',
								fieldLabel: 'Этаж',
								//name: 'CmpCallCard_Etaj',
								name: 'Level',
								width: 100,
								xtype: 'textfield'
							}, {
								disabled: false,
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
								items :[
									{
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
									},
									{
										border: false,
										layout: 'form',
										items : [
											{
												fieldLabel: 'Фамилия',
												disabled: false,
												name: 'Fam',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw',
												allowBlank: false,
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
												disabled: false,
												name: 'Name',
												toUpperCase: true,
												width: 180,
												xtype: 'textfieldpmw',
												allowBlank: false,
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
												disabled: false,
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
											},
											{
												fieldLabel: 'Серия полиса',
												name: 'Person_PolisSer',
												width: 180,
												xtype: 'textfield',
												editable: false,
												disabled: false
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
									}
								]
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
										validator: function(a){return (a.match(/^[1-9]\d*$/))?true:false;},
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
    //получение списка компонентов для вкладки Диагноз
    getDiagnozFields: function(){
        var me = this;
		this.diag_combo = new sw.Promed.SwDiagCombo({
			hiddenName: 'Diag_uid',
			labelStyle: 'width: 122px;',
			width: 274,
			fieldLabel: 'Уточненный диагноз',
			disabled: true,
			hideTrigger: false,
			autoShow: true,
			checkAccessRights: false,
			allowBlank: false,
			disabledClass: 'field-disabled',
			MKB: {
				isMain: true
			}
		});
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
					checkAccessRights: false,
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
					checkAccessRights: false,
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
							items:
								[
									{
										checkAccessRights: false,
										hiddenName: 'Diag_id',
										xtype: 'swdiagcombo',
										allowBlank: false,
										labelStyle: 'width: 50px;',
										width: 274,
								selectionDepth: 3,
                                withGroups: true,
                                disabledClass: 'field-disabled',
                                MKB: {
                                    isMain: true
                                },
                                listeners: {
                                    select: function(combo, select_item){

                                        var diag_uid = me.FormPanel.getForm().findField('Diag_uid');

                                        if (diag_uid) {
                                            if(select_item.get('DiagLevel_id') == 3){
                                                diag_uid.setDisabled(false);
                                                diag_uid.Diag_level3_code =  select_item.get('Diag_Code');
                                                diag_uid.doQuery(); //обновляем данные поля "Уточненный диагноз"
                                            }else{
                                                diag_uid.clearValue();
                                                diag_uid.setDisabled(true);
                                            }
                                        };
										me.setDefaultPayType()

                                    }

                                }
                        },
									this.diag_combo,
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
                    columns: [400,400,400],
                    vertical: true,
                    width: '100%',
                    name: 'GroupComplicat',
                   // xtype: 'radiogroup',
                    xtype: 'checkboxgroup',
                    items: this.getCombo('Complicat_id')
                }]
            }, {
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
					}
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
			}, 
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Результат выезда',
				labelWidth : 150,
				frame	   : true,				
				items : [
					{
						xtype: 'swcmpresultcombo',
						fieldLabel: 'Результат выезда',
						labelWidth : 100,
						listWidth: 300,
						allowBlank: false,
						listeners:{
							select: function(cmp,rec){								
								if(rec){
									me.FormPanel.getForm().findField('LeaveType_id').setValue(rec.get('LeaveType_id'));
									if(rec.get('CmpResult_Code').inlist([11,12,13,14,15]) ){
										//госпитализирован
										me.FormPanel.getForm().findField('Lpu_hid').showContainer();
									}
									else{
										me.FormPanel.getForm().findField('Lpu_hid').hideContainer()
									}

									me.FormPanel.find('refId', 'emergencyTeamSpecFields')[0].setVisible(rec.get('CmpResult_Code').inlist([31]));

								}
							},
							change: function(){
								if(!me.existOsmUslugaComplex()){
									me.addOsmUslugaComplex()

								me.setDefaultPayType()
							}
						}
						}
					},
					{
						autoLoad: true,			
						fieldLabel:"МО госпитализации",
						hiddenName:"Lpu_hid",
						listWidth:400,
						width: 300,
						xtype: "swlpucombo"
					},
					{
						xtype      : 'fieldset',
						autoHeight: true,
						hidden: true,
						refId: 'emergencyTeamSpecFields',
						items: [
							{
								code: 245,
								dateFieldWidth: 80,
								dateLabel: "Время передачи бригаде СМП ",
								dateLabelStyle: "width: 115px;",
								dateLabelWidth1: "235px",
								hiddenName: "ComboValue_245",
								id: "CMPCLOSE_ComboValue_245",
								name: "ComboValue_245",
								parent_code: 227,
								timeLabel: "Время",
								timeLabelWidth: 50,
								timeLabelWidth1: "145px",
								xtype: "swdatetimefield",
								onChange: function(field, newValue){
									var date = Ext.util.Format.date(new Date(newValue),'d.m.Y H:i:s'),
										cmpCallCard_id = me.FormPanel.getForm().findField('CmpCallCard_id').getValue(),
										team_combo = me.FormPanel.getForm().findField('ComboValue_244');

									if(newValue){
										team_combo.getStore().load({
											params: {
												AcceptTime: date,
												CmpCallCard_id: cmpCallCard_id
											},
											callback: function(recs) {
												team_combo.enable()
											}
										})
									}else{
										team_combo.disable()
									}

								}
							},
							{
								code: 244,
								fieldLabel: "Номер бригады СМП",
								hiddenName: "ComboValue_244",
								id: "CMPCLOSE_ComboValue_244",
								//labelStyle: "width: 200px",
								listWidth:400,
								width: 300,
								name: "ComboValue_244",
								parent_code: 227,
								allowBlank: true,
								disabled: true,
								xtype: "swEmergencyTeamCCC"
							}
						]
					},
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Федеральный результат',
				labelWidth : 150,
				frame	   : true,
				items : [
					{
						xtype: 'swfedleavetypecombo',
						hiddenName: 'LeaveType_id',
						labelWidth : 100,
						listWidth: 300
					}
				]
			},
			{
				xtype      : 'fieldset',
				autoHeight: true,
				title	   : ++me.panelNumber + '. Километраж',
				labelWidth : 150,
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
				labelWidth : 150,
				frame	   : true,
				items : [{
					fieldLabel: 'Примечания',
					name: 'DescText',
					xtype: 'textarea',
					width: '90%'
				}]
			}
		]

	}

});