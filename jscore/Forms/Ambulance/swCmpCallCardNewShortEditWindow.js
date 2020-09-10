/**
 * swCmpCallCardNewShortEditWindow - окно редактирования карты вызова (краткий вариант для операторов СМП)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author		Bykov Stas aka Savage (savage@swan.perm.ru)
 * @version      апрель.2012
 */

sw.Promed.swCmpCallCardNewShortEditWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swCmpCallCardNewShortEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardNewShortEditWindow.js',
	closable: true,
	closeAction: 'hide',
	cls: 'swCmpCallCardNewShortEditWindow',
	maximizable: true,
	maximized: true,
	plain: false,
	width: 750,
	layout: 'form',

	initComponent: function() {
		var me = this,
			opts = getGlobalOptions(),
			defaultCmpWidth = 350;

		//нумерация филдсетов
		me.panelNumber = 0;

		me.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			toolbar: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 350,
			region: 'center',
			tbar: new Ext.Toolbar({
			items: [
					{
						iconCls: 'edit16',
						text: langs('Карта вызова'),
						listeners: {
							click: function(){
								me.closeCmpCallCard()
							}
						}
					}
				]
			}),
			items: [
				//скрытые поля
				{
					xtype: 'hidden',
					name: 'CmpCallCard_id',
				},
				{
					xtype: 'hidden',
					name: 'CmpCloseCard_id',
				},
				{
					xtype: 'hidden',
					name: 'CmpCallCardStatusType_Code',
				},
				{
					xtype: 'hidden',
					name: 'CmpCallCard_Ktov',
				},
				{
					xtype: 'hidden',
					name: 'ARMType',
				},
				/** Скрытые поля для сохранения анкеты КВИ */
				{
					xtype: 'fieldset',
					id: 'ApplicationCVI',
					hidden: true,
					defaults: {
						xtype: 'hidden'
					},
					items: [
						{name: 'PlaceArrival_id'},
						{name: 'isSavedCVI'},
						{name: 'KLCountry_id'},
						{name: 'OMSSprTerr_id'},
						{name: 'ApplicationCVI_arrivalDate'},
						{name: 'ApplicationCVI_flightNumber'},
						{name: 'ApplicationCVI_isContact'},
						{name: 'ApplicationCVI_isHighTemperature'},
						{name: 'Cough_id'},
						{name: 'Dyspnea_id'},
						{name: 'ApplicationCVI_Other'}
					]
				},
				/** Скрытые поля для сохранения анкеты КВИ */
				{
					xtype: 'fieldset',
					id: 'ApplicationCVI',
					hidden: true,
					defaults: {
						xtype: 'hidden'
					},
					items: [
						{name: 'PlaceArrival_id'},
						{name: 'isSavedCVI'},
						{name: 'KLCountry_id'},
						{name: 'OMSSprTerr_id'},
						{name: 'ApplicationCVI_arrivalDate'},
						{name: 'ApplicationCVI_flightNumber'},
						{name: 'ApplicationCVI_isContact'},
						{name: 'ApplicationCVI_isHighTemperature'},
						{name: 'Cough_id'},
						{name: 'Dyspnea_id'},
						{name: 'ApplicationCVI_Other'}
					]
				},
				//
				{
					border: false,
					layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							labelWidth: 360,
							items: [
								{
									fieldLabel: langs('Дата вызова'),
									format: 'd.m.Y',
									name: 'CmpCallCard_prmDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									//maxValue: new Date(),
									xtype: 'swdatefield',
									listeners: {
										'blur': function(){
											var base_form = me.FormPanel.getForm();

											me.setPersonAgeFields('CmpCallCard_prmDate');
											me.getCmpCallCardNumber(me, base_form);
										}
									}

								},
								{
									xtype: 'textfield',
									fieldLabel: langs('№ вызова за день'),
									name: 'CmpCallCard_Numv',
									maskRe: /[0-9]/,
									maxLength: 12,
									listeners: {
										blur: function(){
											me.existenceNumbersDayYear()
										}
									}
								},
								{
									xtype: 'textfield',
									fieldLabel: langs('Признак вызова за день'),
									name: 'CmpCallCard_NumvPr',
									maskRe: /[0-9]/,
									maxLength: 12
								}
							]
						},
						{
							border: false,
							labelWidth: 120,
							layout: 'form',
							items: [
								{
									fieldLabel: langs('Время'),
									name: 'CmpCallCard_prmTime',
									plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
									validateOnBlur: false,
									width: 60,
									xtype: 'swtimefield'
								},
								{
									xtype: 'textfield',
									fieldLabel: langs('№ вызова (за год)'),
									name: 'CmpCallCard_Ngod',
									maskRe: /[0-9]/,
									maxLength: 12,
									listeners: {
										blur: function(){
											me.existenceNumbersDayYear()
										}
									}
								},
								{
									xtype: 'textfield',
									fieldLabel: langs('Признак вызова за год'),
									name: 'CmpCallCard_NgodPr',
									maskRe: /[0-9]/,
									maxLength: 12
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
					autoHeight: true,
					title: ++me.panelNumber + '. '+langs('Пациент'),
					xtype: 'fieldset',
					layout: 'column',
					name: 'personFieldset',
					items: [
						{
							border: false,
							layout: 'form',
							defaults: {
								width: defaultCmpWidth,
								disabledClass: 'field-disabled'
							},
							items: [
								//скрытни
								{
									xtype: 'hidden',
									name: 'Person_Age'
								},
								{
									xtype: 'hidden',
									name: 'Person_IsUnknown'
								},
								{
									xtype: 'hidden',
									name: 'Person_id'
								},
								{
									xtype: 'hidden',
									name: 'Person_isOftenCaller'
								},
								{
									xtype: 'hidden',
									name: 'CmpLpu_id'
								},
								{
									xtype: 'hidden',
									name: 'Person_deadDT'
								},
								{
									xtype: 'hidden',
									name: 'Lpu_Nick' // лпу прикрепления
								},
								//
								{
									fieldLabel: langs('Фамилия'),
									name: 'Person_SurName',
									toUpperCase: true,
									xtype: 'textfieldpmw',
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									fieldLabel: langs('Имя'),
									name: 'Person_FirName',
									toUpperCase: true,
									xtype: 'textfieldpmw',
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									fieldLabel: langs('Отчество'),
									name: 'Person_SecName',
									toUpperCase: true,
									xtype: 'textfieldpmw',
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									name: 'Person_BirthDay',
									maxValue: (new Date()),
									fieldLabel: langs('Дата рождения'),
									width: 120,
									xtype: 'swdatefield',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									listeners: {
										blur: function(f) {
											me.setPersonAgeFields('Person_BirthDay');
											if(me.action == 'add'){
												me.checkUrgencyAndProfile();
											}
										}
									}
								},
								{
									layout: 'column',
									border: false,
									width: 600,
									items: [
										{
											layout: 'form',
											border: false,
											items: [{
												allowDecimals: false,
												allowNegative: false,
												fieldLabel: langs('Возраст'),
												name: 'Person_Age_Inp',
												width: 160,
												xtype: 'numberfield',
												listeners: {
													blur: function(cmp) {
														if(cmp.originalValue != cmp.getValue()){
															me.setPersonAgeFields('Person_Age_Inp');
															if(me.action == 'add'){
																me.checkUrgencyAndProfile();
															}
														}
													}
												}
											}]
										},
										{
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'swstoreinconfigcombo',
													hideLabel: true,
													hiddenName: 'AgeUnit_id',
													valueField: 'AgeUnit_id',
													displayField: 'AgeUnit_Name',
													comboData: [
														['years', 'лет'],
														['months', 'месяцев'],
														['days', 'дней']
													],
													comboFields: [
														{name: 'AgeUnit_id', type:'string'},
														{name: 'AgeUnit_Name', type:'string'}
													],
													value: 'years',
													width: 80,
													listeners: 	{
														'select': function(combo, record, index) {
															me.setPersonAgeFields('AgeUnit_id');
															if(me.action == 'add'){
																me.checkUrgencyAndProfile();
															}
														}
													}
												}
											]
										}
									]
								},
								{
									fieldLabel: langs('Пол'),
									hiddenName: 'Sex_id',
									xtype: 'swcommonsprcombo',
									comboSubject: 'Sex',
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									fieldLabel: langs('Серия полиса'),
									name: 'Polis_Ser',
									xtype: 'textfield',
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									fieldLabel: langs('Номер полиса'),
									name: 'Polis_Num',
									xtype: 'textfield',
									maskRe: /[0-9]/,
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									fieldLabel: langs('Единый номер'),
									name: 'Polis_EdNum',
									xtype: 'textfield',
									maskRe: /\d/,
									maxLength: 16,
									minLength: 16,
									listeners: {
										blur: function () {
											me.checkPersonIdentification();
										}
									}
								},
								{
									border: false,
									layout: 'form',
									style: 'margin-left: 350px;',
									items: [
										{
											xtype: 'panel',
											frame: true,
											border: false,
											hidden: true,
											name: 'status_panel',
											style: 'margin-left: 5px; margin-bottom: 5px;',
											bodyStyle: 'padding: 3px;',
											items: [{
												html: '',
												style: 'text-align: center;',
												name: 'status_field'
											}]
										} , {
											xtype: 'panel',
											frame: true,
											border: false,
											hidden: true,
											name: 'addinfo_panel',
											style: 'margin-left: 5px; margin-bottom: 5px;',
											bodyStyle: 'padding: 3px;',
											items: [{
												html: langs('Дополнительная информация</br>'),
												style: 'text-align: center;',
												name: 'addinfo_header'
											}, {
												html: '',
												style: 'text-align: center;',
												name: 'addinfo_field'
											}]
										}
									]
								}, {
									id: 'wrapperIsQuarantine',
									layout: 'form',
									xtype: 'panel',
									border: false,
									width: '100%',
									hidden: true,
									items: [{
										xtype: 'swcheckbox',
										fieldLabel: 'Карантин',
										name: 'CmpCallCard_IsQuarantine',
										listeners: {
											check: function (box, newValue) {
												if (me.action != 'add') {
													me.FormPanel.findById('wrapperIsQuarantine').setVisible(true);
													return;
												}
												var baseForm = me.FormPanel.getForm();
												var combo = baseForm.findField('CmpReason_id');
												if (newValue && combo.getCode() == 'НГ1' && !baseForm.findField('isSavedCVI').getValue()) {
													me.FormPanel.findById('wrapperApplicationCVI').setVisible(true);
													getWnd('swApplicationCVIWindow').show({
														Person_id: baseForm.findField('Person_id').getValue(),
														forObject: me.objectName,
														action: me.action
													});
												} else {
													if (!Ext.isEmpty(getWnd('swApplicationCVIWindow').FormPanel)) {
														getWnd('swApplicationCVIWindow').reset();
														baseForm.findField('isSavedCVI').reset();
														me.FormPanel.findById('ApplicationCVI').findBy(function (obj) {obj.reset()});
													}
													me.FormPanel.findById('wrapperApplicationCVI').setVisible(false);
												}
											}
										}
									}]
								}, {
									id: 'wrapperApplicationCVI',
									layout: 'form',
									xtype: 'panel',
									border: false,
									width: '100%',
									hidden: true,
									items:[{
										xtype: 'button',
										text: 'Анкета по КВИ',
										id: 'btnApplicationCVI',
										handler: function () {
											var values = me.getAllValues(me.FormPanel.find('id','ApplicationCVI')[0]);log('<>---v', values);
											getWnd('swApplicationCVIWindow').show({
												Person_id: me.FormPanel.getForm().findField('Person_id').getValue(),
												forObject: me.objectName,
												action: me.action,
												fields: values
											})
										}
									}]
								}
							]
						},
						{
							border: false,
							layout: 'form',
							refId: 'personBtnPanel',
							style: 'padding-left: 10px;',
							items: [
								{
									handler: function() {
										me.personSearch(me);
									},
									iconCls: 'search16',
									refId: 'searchPersonBtn',
									text: langs('Поиск'),
									xtype: 'button'
								},
								{
									handler: function() {
										me.personReset();
									},
									iconCls: 'reset16',
									text: langs('Сброс'),
									refId: 'resetPersonBtn',
									xtype: 'button'
								},
								{
									handler: function() {
										me.personUnknown();
									},
									iconCls: 'reset16',
									text: langs('НЕИЗВЕСТЕН'),
									refId: 'unknownPersonBtn',
									xtype: 'button'
								}
							]
						}
					]
				},
				{
					autoHeight: true,
					title: ++me.panelNumber + '. '+langs('Место вызова'),
					xtype: 'fieldset',
					name: 'addressFieldset',
					layout: 'form',
					defaults: {
						width: defaultCmpWidth,
						disabledClass: 'field-disabled'
					},
					items: [
						//спрятанные поля
						{
							xtype: 'hidden',
							name: 'Lpu_hid'
						},
						{
							xtype: 'hidden',
							name: 'KLStreet_id'
						},
						{
							xtype: 'hidden',
							name: 'UnformalizedAddressDirectory_id'
						},
						{
							xtype: 'hidden',
							name: 'CmpCallCard_Ulic'
						},
						//
						{
							enableKeyEvents: true,
							hiddenName: 'KLAreaStat_idEdit',
							listeners: {},
							xtype: 'swklareastatcombo',
							listeners: {
								beforeselect: function(combo, record) {
									me.setAddress(combo, record);
								}
							}
						},
						{
							hiddenName: 'KLSubRgn_id',
							listeners: {},
							xtype: 'swsubrgncombo',
							listeners: {
								beforeselect: function(combo, record) {
									me.setAddress(combo, record);
								}
							}
						},
						{
							hiddenName: 'KLCity_id',
							listeners: {},
							trigger2Class: 'x-form-clear-trigger',
							xtype: 'swcitycombo',
							listeners: {
								beforeselect: function(combo, record) {
									me.setAddress(combo, record);
								}
							},
							onTrigger2Click: function() {
								var base_form = me.FormPanel.getForm();

								base_form.findField('KLCity_id').clearValue();
								base_form.findField('KLTown_id').clearValue();

							}
						},
						{
							hiddenName: 'KLTown_id',
							enableKeyEvents: true,
							listeners: {},
							xtype: 'swtowncombo',
							listeners: {
								beforeselect: function(combo, record) {
									me.setAddress(combo, record);
								}
							},
							onTrigger2Click: function() {
								var base_form = me.FormPanel.getForm();

								getWnd('swKLTownSearchWindow').show({
									onSelect: function(response_data) {
										base_form.findField('KLAreaStat_idEdit').onClearValue();
									},
									params: {
										KLCity_id: base_form.findField('KLCity_id').getValue() || '0',
										KLSubRegion_id: base_form.findField('KLSubRgn_id').getValue() || '0',
										KLCity_Name: base_form.findField('KLCity_id').getRawValue() || '',
										KLSubRegion_Name: base_form.findField('KLSubRgn_id').getRawValue() || '',
										KLRegion_id: opts.region.number,
										KLRegion_Name: (opts.region.number == 2) ? langs('БАШКОРТОСТАН') : opts.region.name
									}
								});
							}
						},
						{
							xtype: 'swstreetandunformalizedaddresscombo',
							fieldLabel: langs('Улица'),
							trigger2Class: 'x-form-clear-trigger',
							hiddenName: 'StreetAndUnformalizedAddressDirectory_id',
							forceSelection: getRegionNick() != 'krym',
							onTrigger2Click: function() {
								this.clearValue();
							},
							listeners: {
								blur: function(combo){

									var base_form = me.FormPanel.getForm();

									if(
										!combo.store.getCount() ||
										combo.store.findBy(function(rec) { return rec.get('StreetAndUnformalizedAddressDirectory_id') == combo.getValue(); }) == -1
									)
									{
										if(getRegionNick().inlist(['krym'])){
											base_form.findField('UnformalizedAddressDirectory_id').setValue(null);
											base_form.findField('KLStreet_id').setValue(null);
											base_form.findField('CmpCallCard_Ulic').setValue(combo.getRawValue());
											base_form.findField('StreetAndUnformalizedAddressDirectory_Name').setValue(combo.getRawValue());
										}
										else{
											combo.reset();
										}
									}

								},
								beforeselect: function(combo, record) {
									if ( typeof record != 'undefined' ) {
										combo.setValue(record.get(combo.valueField));
									}

									var base_form = me.FormPanel.getForm();
									base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
									base_form.findField('KLStreet_id').setValue(record.get('KLStreet_id'));
								}
							}
						},
						{
							xtype: 'swstreetandunformalizedaddresscombo',
							fieldLabel: langs('Улица'),
							enableKeyEvents: true,
							hiddenName: 'UnformalizedCmpCallCard_UlicSecond',
							trigger2Class: 'x-form-clear-trigger',
							onTrigger2Click: function() {
								this.clearValue();
								me.setEnableFields(this);
							},
							listeners: {
								blur: function(cmp){
									me.setEnableFields(cmp);
								},
								beforeselect: function(combo, record) {
									if ( typeof record != 'undefined' ) {
										combo.setValue(record.get(combo.valueField));
									}
								},
								keydown: function(a,b,c) {

								}
							}
						},
						{
							fieldLabel: langs('Дом'),
							enableKeyEvents: true,
							name: 'CmpCallCard_Dom',
							toUpperCase: true,
							xtype: 'textfield',
							listeners: {
								keyup: function(cmp) {
									me.setEnableFields(cmp);
								}
							}
						},
						{
							fieldLabel: langs('Корпус'),
							name: 'CmpCallCard_Korp',
							toUpperCase: true,
							xtype: 'textfield'
						},
						{
							fieldLabel: langs('Квартира'),
							name: 'CmpCallCard_Kvar',
							maxLength: 5,
							xtype: 'textfieldpmw'
						},
						{
							fieldLabel: langs('Комната'),
							name: 'CmpCallCard_Room',
							toUpperCase: true,
							xtype: 'textfield'
						},
						{
							fieldLabel: langs('Подъезд'),
							name: 'CmpCallCard_Podz',
							xtype: 'textfield'
						},
						{
							fieldLabel: langs('Этаж'),
							name: 'CmpCallCard_Etaj',
							xtype: 'textfield'
						},
						{
							fieldLabel: langs('Код в подъезде (домофон)'),
							name: 'CmpCallCard_Kodp',
							xtype: 'textfield'
						},
						{
							comboSubject: 'CmpCallPlaceType',
							fieldLabel: langs(' Тип места вызова'),
							hiddenName: 'CmpCallPlaceType_id',
							name: 'CmpCallPlaceType_id',
							xtype: 'swcommonsprcombo',
							listeners: {
								blur: function(){
									if(me.action == 'add'){
										me.checkUrgencyAndProfile();
									}
								}
							}
						}
					]
				},
				{
					autoHeight: true,
					title: ++me.panelNumber + '. ' + langs('Вызов'),
					xtype: 'fieldset',
					name: 'placeFieldset',
					defaults: {
						width: defaultCmpWidth,
						disabledClass: 'field-disabled'
					},
					items: [
						{
							fieldLabel: langs('Тип вызова'),
							listeners: {
								change: function(combo, newValue, oldValue){
									var baseForm = me.FormPanel.getForm();
									baseForm.findField('CmpReason_id').fireEvent('change', baseForm.findField('CmpReason_id'));
								},
								select: function(combo, rec){
									me.setEnableFields(combo);
								}
							},
							hiddenName: 'CmpCallType_id',
							displayField: 'CmpCallType_Name',
							xtype: 'swcmpcalltypecombo'
						},
						{
							comboSubject: 'CmpReason',
							fieldLabel: langs('Повод'),
							hiddenName: 'CmpReason_id',
							xtype: 'swreasoncombo',
							editable: true,
							listeners: {
								blur: function(){
									if(me.action == 'add'){
										me.checkUrgencyAndProfile();
									}
								},
								change: function(combo) {
									var baseForm = me.FormPanel.getForm();
									if (combo.getCode() == 'НГ1') {
										me.FormPanel.findById('wrapperIsQuarantine').setVisible(true);
										if (!baseForm.findField('isSavedCVI').getValue()) {
											if (baseForm.findField('CmpCallType_id').getCode() == 1 || baseForm.findField('CmpCallType_id').getCode() == 2) {
												baseForm.findField('CmpCallCard_IsQuarantine').setValue(true);
												baseForm.findField('CmpCallCard_IsQuarantine').setDisabled(true);
												me.FormPanel.findById('wrapperApplicationCVI').setVisible(true);
												getWnd('swApplicationCVIWindow').show({
													Person_id: baseForm.findField('Person_id').getValue(),
													forObject: me.objectName,
													action: me.action
												});
											} else {
												if (!Ext.isEmpty(getWnd('swApplicationCVIWindow').FormPanel)) {
													getWnd('swApplicationCVIWindow').reset();
													baseForm.findField('isSavedCVI').reset();
													me.FormPanel.findById('ApplicationCVI').findBy(function (obj) {obj.reset()});
												}
												baseForm.findField('CmpCallCard_IsQuarantine').setValue(false);
												baseForm.findField('CmpCallCard_IsQuarantine').setDisabled(false);
												me.FormPanel.findById('wrapperApplicationCVI').setVisible(false);
											}
										} else if (baseForm.findField('CmpCallType_id').getCode() == 1 || baseForm.findField('CmpCallType_id').getCode() == 2) {
											baseForm.findField('CmpCallCard_IsQuarantine').setDisabled(true);
										}
									} else {
										baseForm.findField('CmpCallCard_IsQuarantine').setValue(false);
										me.FormPanel.findById('wrapperIsQuarantine').setVisible(false);
									}
								}
							}
						},
						{
							border: false,
							fieldLabel: langs('Вид вызова'),
							xtype: 'swcmpclosecardisextracombo',
							hiddenName: 'CmpCallCard_IsExtra',
							listeners: {
								select: function(cmp) {
									me.setEnableFields(cmp);
								}
							}
						},
						/*
						темные поля, темные лошадки
						{
							comboSubject: 'CmpReason',
							fieldLabel: langs('Доп. повод'),
							hiddenName: 'CmpSecondReason_id',
							xtype: 'swreasoncombo',
							listeners: {
								change: function(cmp, newVal){
								}
							}
						},
						{
							comboSubject: 'CmpReasonNew',
							fieldLabel: langs('Дополнительно'),
							hiddenName: 'CmpReasonNew_id',
							autoLoad: true,
							xtype: 'swcustomobjectcombo'
						},
						*/
						{
							xtype: 'checkbox',
							labelSeparator: '',
							boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
							name: 'CmpCallCard_IsPoli',
							listeners: {
								check: function(cmp, checked){
									var base_form = me.FormPanel.getForm(),
										lpuLocalCombo = base_form.findField('lpuLocalCombo'),
										lpuLocalComboStore = lpuLocalCombo.getStore();

									if(checked){
										lpuLocalComboStore.baseParams = {
											Object: 'LpuWithMedServ'
										};
									}
									else{
										lpuLocalComboStore.baseParams = {
											Object: 'LpuWithMedServ',
											MedServiceType_id: 18
										};
									}

									lpuLocalComboStore.load({
										callback: function() {
											if(lpuLocalCombo.getValue()){

												var lpuRec = lpuLocalComboStore.getById(lpuLocalCombo.getValue());
												if(!lpuRec){
													lpuLocalCombo.clearValue();
												}
											}
										}
									});
								},
								change: function(cmp){
									me.setEnableFields(cmp);
								}
							}
						},
						{
							fieldLabel: 'МО передачи (НМП)',
							valueField: 'Lpu_id',
							autoLoad: true,
							editable: true,
							hiddenName: 'lpuLocalCombo',
							displayField: 'Lpu_Nick',
							medServiceTypeId: 18,
							comAction: 'AllAddress',
							xtype: 'swlpuwithmedservicecombo',
							listeners: {
								select: function(combo, record){
									var base_form = me.FormPanel.getForm(),
										medServiceCombo = base_form.findField('MedService_id'),
										medServiceComboStore = medServiceCombo.getStore(),
										LpuBuildingCombo = base_form.findField('LpuBuilding_id'),
										Lpu_smpidCombo = base_form.findField('Lpu_smpid'),
										IsPassSSMPField = base_form.findField('CmpCallCard_IsPassSSMP');

									if(record.get('Lpu_id')){
										medServiceComboStore.baseParams = {
											'MedServiceType_id' : 18,
											'Lpu_id' : record.get('Lpu_id'),
											'isClose' : 1
										};
									}
									else{
										medServiceComboStore.baseParams = {
											'MedServiceType_id' : 18,
											'isClose' : 1
										};
									}

									medServiceComboStore.load({
										callback: function() {
											if(medServiceCombo.getValue()){
												var medServiceRec = medServiceComboStore.getById(medServiceCombo.getValue());
												if(!medServiceRec){
													medServiceCombo.clearValue();
												}
											}
										}
									});

									me.setEnableFields(combo);
								}
							}
						},
						{
							fieldLabel: 'Служба НМП',
							xtype: 'swmedservicecombo',
							autoLoad: true,
							params: {MedServiceType_id : 18, isClose: 1},
							hiddenName: 'MedService_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{values.MedService_Name}</div></tpl>'
						},
						{
							xtype: 'checkbox',
							labelSeparator: '',
							boxLabel: 'Вызов передан в другую ССМП по телефону (рации)',
							name: 'CmpCallCard_IsPassSSMP',
							listeners: {
								change: function(cmp){
									me.setEnableFields(cmp);
								}

							}
						},
						{
							fieldLabel: 'МО передачи (СМП)',
							valueField: 'Lpu_id',
							name: 'Lpu_smpid',
							hiddenName: 'Lpu_smpid',
							autoLoad: true,
							displayField: 'Lpu_Nick',
							comAction: 'AllAddress',
							xtype: 'swlpucombo',
							listeners: {}
						},
						{
							xtype: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? 'swsmpunitscombo' : 'swregionsmpunitscombo' ,
							fieldLabel: 'Подразделение СМП',
							hiddenName: 'LpuBuilding_id',
							showOperDpt: 1,
							listeners: {
								select: function(cmp) {
									me.setEnableFields(cmp);
								},
								blur: function(cmp) {
									me.setEnableFields(cmp);
								}
							}
						},
						{
							xtype: 'panel',
							frame: true,
							border: false,
							hidden: true,
							name: 'lpu_panel',
							style: 'margin: 5px; margin-left: 355px;',
							bodyStyle: 'padding: 3px;',
							items: [{
								html: '',
								style: 'text-align: center;',
								name: 'lpu_field'
							}]
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Кто вызывает'),
							comboSubject: 'CmpCallerType',
							hiddenName: 'CmpCallerType_id',
							displayField: 'CmpCallerType_Name',
							forceSelection: false,
							editable: true,
							listeners: {
								blur: function(field){
									if(me.action == 'add'){
										me.checkUrgencyAndProfile();
									}

									var raw_value = field.getRawValue(),
										rec = field.findRecord( field.displayField, raw_value ),
										base_form = me.FormPanel.getForm(),
										cmpCallCard_Ktov = base_form.findField('CmpCallCard_Ktov');

									// Запись в комбобоксе присутствует
									if ( rec ) {
										field.setValue( rec.get( field.valueField ) );
										cmpCallCard_Ktov.reset();
									}
									// Пользователь указал свое значение
									else {
										field.setValue(raw_value);
										cmpCallCard_Ktov.setValue(raw_value);
										//field.setValue(null);
									}

								}
							}
						},
						{
							fieldLabel: langs('Телефон'),
							name: 'CmpCallCard_Telf',
							xtype: 'textfield'
						},
						{
							fieldLabel: langs('Дополнительная информация/ Уточненный адрес'),
							height: 100,
							name: 'CmpCallCard_Comm',
							xtype: 'textarea'
						},
						{
							allowBlank: false,
							fieldLabel: 'Причина отмены',
							xtype:'swbaselocalcombo',
							hiddenName: 'CmpRejectionReason_id',
							store: new Ext.data.JsonStore({
								url: '/?c=CmpCallCard4E&m=getRejectionReason',
								editable: false,
								key: 'CmpRejectionReason_id',
								autoLoad: true,
								fields: [
									{name: 'CmpRejectionReason_id', type: 'int'},
									{name: 'CmpRejectionReason_code', type: 'string'},
									{name: 'CmpRejectionReason_name', type: 'string'}
								],
								sortInfo: {
									field: 'CmpRejectionReason_name'
								}
							}),
							triggerAction: 'all',
							displayField:'CmpRejectionReason_name',
							tpl: '<tpl for="."><div class="x-combo-list-item">'+
								'{CmpRejectionReason_name}'+
								'</div></tpl>',
							valueField: 'CmpRejectionReason_id'
						},
						{
							fieldLabel: 'Комментарий',
							name: 'CmpCallCardRejection_Comm',
							xtype: 'textfield'
						}
					]
				}
			]
		});

		Ext.apply(me, {
			buttons: [
				{
					handler: function() {
						me.doSave();
					},
					iconCls: 'save16',
					refId: 'saveBtn',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(me, -1),
				{
					handler: function() {
						me.hide();
						me.closeWindow();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				me.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardNewShortEditWindow.superclass.initComponent.apply(me, arguments);
	},

	listeners: {},

	//ОСНОВНЫЕ ФУНКЦИИ ФОРМЫ

	show: function(opts) {
		var me = this,
			base_form = me.FormPanel.getForm(),
			defaultTitle = 'Талон вызова';

		base_form.reset();

		if ( arguments[0].action ) {
			me.action = arguments[0].action;
		}

		me.FormPanel.tbar.hide();

		switch(me.action){
			case 'add' : {
				me.setTitle(defaultTitle+ ': Добавление');
				break;
			}
			case 'view' : {
				for (i = 0; i < base_form.items.items.length; i++){
					base_form.items.items[i].setDisabled(true);
				}
				me.setTitle(defaultTitle+ ': Просмотр');
				break;
			}
			case 'edit' : {
				me.setTitle(defaultTitle+ ': Редактирование');
				break;
			}
		};

		me.loadData(me, base_form, opts);

		base_form.isValid();

		//установление активности нижних кнопок (они не в formPanel)
		me.buttons.find(function(button){
			if(button.refId == 'saveBtn'){
				button.setDisabled( me.action == 'view' );
				button.setVisible(me.action != 'view');
			}
		})

		me.FormPanel.findById('wrapperApplicationCVI').setVisible(false);
		me.FormPanel.findById('wrapperIsQuarantine').setVisible(false);
		me.FormPanel.getForm().findField('CmpCallCard_IsQuarantine').setValue(false);

		sw.Promed.swCmpCallCardNewShortEditWindow.superclass.show.apply(me, arguments);
	},

	closeWindow: function(){
		//нужно убедится, что мы во iframe АРМа ДП и тогда закрыть iframe
		var arm = getGlobalOptions().curARMType;
		// var panel = parent.Ext.getCmp('inPanel');
		var body1 = parent.Ext.getBody();
		var body2 = Ext.getBody();

		if(/*arm == 'smpdispatchstation' && */body1.id != body2.id){
			parent.Ext.WindowManager.getActive().close();
		}
	},

	loadData: function(me, form, opts){

		var formParams = {};

		me.loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка талона вызова..."});

		me.loadMask.show();

		//гарантия скрытия маски
		//@todo придумать способ исправить и почистить карму
		setTimeout(function () {
			if (me.loadMask.el.isVisible()) {me.loadMask.hide(); }
		}, 5000);

		me.loadCounter = {
			countLoadingStores: 0,
			countLoadedStores: 0
		};

		if(me.action != 'add'){
			Ext.Ajax.request({
				url: '/?c=CmpCallCard&m=loadCmpCallCardEditForm',
				params: {
					CmpCallCard_id: opts.formParams.CmpCallCard_id
				},
				success: function (response){
					formParams = Ext.util.JSON.decode(response.responseText);

					var values = formParams[0];

					values.ARMType = !Ext.isEmpty(opts.formParams.ARMType) ?  opts.formParams.ARMType : getGlobalOptions().curARMType;

					if (me.action != 'view'){
						me.setEnableFields(null, values);
					}

					me.setValues(me, form, values);

					//проставляем отказ в комментарий для этих регионов
					if (getRegionNick().inlist(['astra', 'buryatiya', 'vologda', 'perm', 'khak'])) {
						me.checkRejection(values);
					}

					if (!Ext.isEmpty(me.FormPanel.getForm().findField('PlaceArrival_id').getValue())) {
						me.FormPanel.findById('wrapperApplicationCVI').setVisible(true);
						me.FormPanel.getForm().findField('CmpCallCard_IsQuarantine').setValue(true);
					}

					me.checkIsCallControllFlag();
				},
				failure: function (a,b,c) {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function () {});
				}

			});
		}
		else {
			formParams = {
				CmpLeaveType_id: 1,
				CmpLeaveTask_id: 1,
				CmpMedicalCareKind_id: 1,
				CmpCallCard_prmDT: new Date(),
				ARMType : !Ext.isEmpty(opts.formParams.ARMType) ?  opts.formParams.ARMType : getGlobalOptions().curARMType
			};
			me.setValues(me, form, formParams);
			me.setEnableFields();
			me.checkIsCallControllFlag();
			me.getCmpCallCardNumber(me, form);
			me.getLpuAddressTerritory();
		};
	},

	//загрузка полей и установка значений, зависимостей
	setValues: function(me, form, formParams){
		var opts = getGlobalOptions(),
			fields = me.getAllFields(this.FormPanel.getForm()),
			formParams = formParams || {};

		for(var i = 0; i < fields.length; i++){
			var fieldCmp = fields[i],
				fieldName = fieldCmp.getName(),
				fieldVal = fieldCmp.getValue();

			switch(fieldName){
				//чеки
				case 'CmpCallCard_isControlCall': {
					fieldCmp.setValue(formParams.CmpCallCard_isControlCall == 2);
					break;
				}
				case 'CmpCloseCard_id': {
					if(formParams.CmpCloseCard_id){
						me.FormPanel.tbar.show();
					}
					else{
						me.FormPanel.tbar.hide();
					}
					break;
				}
				case 'EmergencyTeam_id' : {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.EmergencyTeam_id,
						{
							AcceptTime: Ext.util.Format.date(new Date(), 'd.m.Y H:i:s'),
							CmpCallCard_id: null
						}
					);
					break;
				}
				case 'CmpCallCard_prmDate': {
					if(formParams.CmpCallCard_prmDate){
						fieldCmp.setValue(formParams.CmpCallCard_prmDate);
					}
					else{
					fieldCmp.setValue(new Date().format("d.m.Y"));
					}
					break;
				}
				case 'CmpCallCard_prmTime': {
					if(formParams.CmpCallCard_prmTime){
						fieldCmp.setValue(formParams.CmpCallCard_prmTime);
					}
					else{
					fieldCmp.setValue(new Date().format("H:i"));
					}
					break;
				}
				case 'Person_Age_Inp': {
					fieldCmp.originalValue = null;
					fieldCmp.reset();
					if(formParams.Person_Age){
						fieldCmp.setValue(formParams.Person_Age);
						fieldCmp.originalValue = formParams.Person_Age;
						fieldCmp.maxValue = 120;
						fieldCmp.validate();
						me.setPersonAgeFields('Person_BirthDay');
					};

					//иногда приходит возраст или дата рождения, тогда нужно проставлять недостающее значение
					if(!formParams.Person_Age || !formParams.Person_BirthDay){
						if(formParams.Person_Age || formParams.Person_BirthDay){
							var issetField = formParams.Person_Age ? 'Person_Age' : 'Person_BirthDay';
							me.setPersonAgeFields(issetField);
						};
					};

					break;
				}
				case 'Diag_sid':{
					if(formParams.Diag_sid){
						me.setValueAfterStoreLoad(fieldCmp, formParams.Diag_sid, null);
					}
					break;
				}
				case 'Person_id':{

					//люблю кейсы, хоть хлебом не корми
					switch (true){
						case ( formParams.Person_IsUnknown == 2 ) : {
							me.setStatusPanelMessage('none');
							break;
						}
						case ( formParams.Person_id > 0 ) : {
							me.setStatusPanelMessage('uno');
							break;
						}
						default : {
							me.setStatusPanelMessage('hide');
						}
					};
					fieldCmp.setValue(formParams[fieldName]);

					break;
				}
				case 'KLAreaStat_idEdit': {
					var rec = -1;

					switch(true){
						case ( !Ext.isEmpty(formParams.KLTown_id) ) :{
							rec = fieldCmp.getStore().find( 'KLTown_id', formParams.KLSubRgn_id, 0, false );
							break;
						}
						case ( !Ext.isEmpty(formParams.KLCity_id) ) :{
							rec = fieldCmp.getStore().find( 'KLCity_id', formParams.KLCity_id, 0, false );
							break;
						}
						case ( !Ext.isEmpty(formParams.KLSubRGN_id) ) :{
							rec = fieldCmp.getStore().find( 'KLSubRGN_id', formParams.KLTown_id, 0, false );
							break;
						}
					};

					if(rec != -1) {
						fieldCmp.setValue( fieldCmp.getStore().getAt(rec).get('KLAreaStat_id') );
					}
					else fieldCmp.reset();
					break;
				}
				case 'KLSubRgn_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLSubRgn_id,
						{ region_id: opts.region.number }
					);

					break;
				}
				case 'KLCity_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLCity_id,
						{ subregion_id: ( formParams.KLSubRgn_id || opts.region.number ) }
					);
					break;
				}
				case 'KLTown_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLTown_id,
						{ city_id: ( formParams.KLCity_id || formParams.KLSubRgn_id ) }
					);
					break;
				}
				case 'StreetAndUnformalizedAddressDirectory_id' : {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.StreetAndUnformalizedAddressDirectory_id,
						{ town_id: ( formParams.KLTown_id || formParams.KLCity_id) },
						function(fieldCmp){
							var UnformalizedCmpCallCard_UlicSecond = this.FormPanel.getForm().findField('UnformalizedCmpCallCard_UlicSecond');
							var streetName = this.FormPanel.getForm().findField('CmpCallCard_Ulic').getValue();


							//@todo loadData не отрабатывает, потом переделать кусок ниже
							UnformalizedCmpCallCard_UlicSecond.getStore().data = fieldCmp.getStore().data;
/*
							if(fieldCmp.getStore().getCount()){
								fieldCmp.allowBlank = false;
								fieldCmp.validate();
							}
*/
							if(formParams.CmpCallCard_UlicSecond){
								UnformalizedCmpCallCard_UlicSecond.setFieldValue('KLStreet_id', +formParams.CmpCallCard_UlicSecond)
							}
							if((formParams.KLStreet_id == null) && getRegionNick() == 'krym') fieldCmp.setRawValue(streetName);
						}.createDelegate(me)
					);
					break;
				}
				case 'CmpCallPlaceType_id':{
					if(me.action == 'add'){
						fieldCmp.setFieldValue('CmpCallPlaceType_Code', 1);
					}
					else{
						fieldCmp.setValue(formParams[fieldName]);
					}
					break;
				}
				case 'CmpCallCard_IsPoli':
				case 'CmpCallCard_IsPassSSMP': {
					//on
					if(formParams[fieldName] && formParams[fieldName] == 2){
						fieldCmp.setValue(true);
					}
					else{//off
						fieldCmp.setValue(false);
					}
					break;
				}
				case 'lpuLocalCombo':{
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.Lpu_ppdid || formParams.LpuTransmit_id
					);
					break;
				}
				case 'MedService_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.MedService_id
					);
					break;
				}
				case 'LpuBuilding_id' : {
					if(me.action == 'add' && formParams.ARMType != 'slneotl') {
						var currentLpuBuilding = sw.Promed.MedStaffFactByUser.current.LpuBuilding_id
							|| (typeof sw.Promed.MedStaffFactByUser.last == 'object' && sw.Promed.MedStaffFactByUser.last.LpuBuilding_id);

						me.setValueAfterStoreLoad(
							fieldCmp,
							currentLpuBuilding
						);

					}
					else{
						me.setValueAfterStoreLoad(
							fieldCmp,
							formParams[fieldName]
						);
					}
					break;
				}
				case 'CmpCallerType_id': {
					var value = formParams.CmpCallerType_id || formParams.CmpCallCard_Ktov;
					fieldCmp.setValue(value);
					break;
				}
				default :{
					//если в параметрах есть одноименный пункт со значением - значит это значение компонента
					if(formParams && formParams[fieldName])
						fieldCmp.setValue(formParams[fieldName]);

					break;
				}
			};
		};
	},

	//метод получения всех дочерних компонентов с указанного компонента
	getAllFields: function(parentEl){
		var me = this,
			parentEl = parentEl || me.FormPanel.getForm(),
			fieldsTop = parentEl.items.items,
			allFields = [];

		var getAllFields = function(cmps){
			for(var i = 0; i < cmps.length; i++){
				//заметка: собираем только поля может понадобится условие
				if(cmps[i].isXType('field')) {
					allFields.push(cmps[i]);
				}
				if(cmps[i].items && cmps[i].items.items.length){
					getAllFields(cmps[i].items.items)
				};
			}
		};

		getAllFields(fieldsTop);

		return allFields;
	},

	getAllValues: function(parentEl){
		var me = this,
			parentEl = parentEl || null,
			fields = me.getAllFields(parentEl),
			values = {};

		for(var i = 0; i < fields.length; i++) {

			var fieldCmp = fields[i],
				fieldVal = fieldCmp.getValue(),
				fieldName = fieldCmp.getName();

			switch(true) {
				case ( fieldCmp.ownerCt.xtype == "swdatetimefield" ):{
					fieldVal = fieldCmp.getStringValue();
					values[fieldName] = fieldVal;
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "swdatefield" ):{
					values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y');
					break;
				}
				case (fieldVal instanceof Date):
				{
					//просто дата пришла
					values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y H:i');
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "checkbox" ):{
					values[fieldName] = fieldVal ? 2 : 1;
					break;
				}
				default : {
					values[fieldName] = fieldVal;
				}
			}

		}
		return values;
	},


	doSave: function(){
		var me = this,
			values = me.getAllValues(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение талона вызова..."}),
			base_form = me.FormPanel.getForm(),
			CmpCallTypeField = base_form.findField('CmpCallType_id'),
			CmpCallTypeRec = CmpCallTypeField.store.getById(values.CmpCallType_id),
			callTypeWithoutLpu = (CmpCallTypeRec && CmpCallTypeRec.get(CmpCallTypeField.codeField).inlist([6,15,16,17])),
			acceptDate = base_form.findField('CmpCallCard_prmDate').getValue();
		if(!base_form.isValid()){
			sw.swMsg.alert(langs('Ошибка'), langs('Проверьте обязательные для заполнения поля'));
			return false;
		}

		if ((values.CmpCallType_id == 33 || values.CmpCallType_id == 34) && base_form.findField('CmpReason_id').getCode() == 'НГ1') {
			values.CmpCallCard_IsQuarantine = true;
			base_form.findField('CmpCallCard_IsQuarantine').setValue(true);
		}

		if (base_form.findField('CmpReason_id').getCode() == 'НГ1' && !values.isSavedCVI && base_form.findField('CmpCallCard_IsQuarantine').getValue()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для вызова должна быть заполнена анкета по КВИ'));
			return false;
		}

		if(
			(values.CmpCallCard_IsExtra == 2) &&
			!callTypeWithoutLpu &&
			values.CmpCallCard_IsPassSSMP != 2 &&
			Ext.isEmpty(values.LpuBuilding_id) &&
			Ext.isEmpty(values.lpuLocalCombo)
		){
			Ext.Msg.alert('Ошибка', 'Если вызов неотложный, то хотя бы одно из полей «МО передачи (НМП)» или «Подразделение СМП» должно быть заполнено');
			return false;
		}

		loadMask.show();

		values.CmpCallCard_prmDate = Ext.util.Format.date(acceptDate, 'd.m.Y');

		//values.Person_Age = values.Person_Age_Inp;
		values.Lpu_ppdid = values.lpuLocalCombo;
		values.LpuTransmit_id = values.lpuLocalCombo;

		values.CmpCallerType_id = +values.CmpCallerType_id;

		var secondStreetCombo = base_form.findField('UnformalizedCmpCallCard_UlicSecond'),
			secStreetRec = secondStreetCombo.getFieldValue('KLStreet_id');

		if (secStreetRec){
			values.CmpCallCard_UlicSecond = secStreetRec;
		}

		if(values.CmpCallCard_Ktov){
			values.CmpCallerType_id = null;
		}

		if(getRegionNick().inlist(['penza'])){
			values.setDay_num = values.CmpCallCard_Numv;
			values.setYear_num = values.CmpCallCard_Ngod;
		}

		/*проверка на будущую дату #107631*/
		me.checkAcceptDateOnSave(base_form, function(dateSuccess){
			if(dateSuccess){
				me.checkDuplicate(values, function(success){
					if(success){
						Ext.Ajax.request({
							url: '/?c=CmpCallCard&m=saveCmpCallCard',
							params: values,
							failure: function (response, opts) {
								loadMask.hide();
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							},
							callback: function (opt, success, response) {
								loadMask.hide();
								if (!success) {
									sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								var request = Ext.util.JSON.decode(response.responseText);

								if(request.success)
								{
									sw.swMsg.alert(langs('Сохранение'), langs('Талон вызова сохранён'), function(){
										if (!Ext.isEmpty(getWnd('swApplicationCVIWindow').FormPanel)) {
											getWnd('swApplicationCVIWindow').reset();
											base_form.findField('isSavedCVI').reset();
											me.FormPanel.findById('wrapperApplicationCVI').setVisible(false);
											me.FormPanel.findById('ApplicationCVI').findBy(function (obj) {obj.reset()});
										}
										me.hide();
										if(!Ext.isEmpty(me.lastArguments.callback))me.lastArguments.callback({cmpCallCardData:request});
									});
								}
							}
						});
					}
					else{
						loadMask.hide();
					}
				});
			}
			else{
				sw.swMsg.alert(langs('Ошибка'), 'Дата приема вызова не может быть больше текущей даты. Сохранение невозможно.');
				loadMask.hide();
			}
		});
	},

	//проверка на будущую дату при сохранении
	//true - все норм
	checkAcceptDateOnSave: function(base_form, callback){

		var acceptDate = base_form.findField('CmpCallCard_prmDate').getRawValue() + ' ' + base_form.findField('CmpCallCard_prmTime').getValue();

		acceptDate = Date.parseDate(acceptDate,'d.m.Y H:i');

		getCurrentDateTime({
			callback: function(response){
				var dt;

				if(!Ext.isDate(Date.parseDate(response.date+' '+response.time,'d.m.Y H:i'))){
					dt = new Date;
				}
				else{
					dt = Date.parseDate(response.date+' '+response.time,'d.m.Y H:i');
				}

				if(acceptDate > dt){
					callback(false);
				}else{
					callback(true);
				}
			}
		});
	},

	//номера вызова за день и за год
	getCmpCallCardNumber: function(me, form) {

		var url = '/?c=CmpCallCard&m=getCmpCallCardNumber',
			cmpCallCard_prmDate = form.findField('CmpCallCard_prmDate').getValue(),
			cmpCallCard_prmTime = form.findField('CmpCallCard_prmTime').getValue(),
			params = {
				CmpCallCard_prmDT: Ext.util.Format.date(cmpCallCard_prmDate, 'Y-m-d') + ' ' + cmpCallCard_prmTime,//2018-04-18 11:53
			},
			CmpCallCard_Ngod = form.findField('CmpCallCard_Ngod'),
			CmpCallCard_Numv = form.findField('CmpCallCard_Numv');

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if(response_obj.success === false){
						me.hide();
					}

					if(me.action == 'edit'){
						CmpCallCard_Ngod.setValue(response_obj.nextNumberYear);
						CmpCallCard_Numv.setValue(response_obj.nextNumberDay);
					}else{
						CmpCallCard_Ngod.setValue(response_obj[0].CmpCallCard_Ngod);
						CmpCallCard_Numv.setValue(response_obj[0].CmpCallCard_Numv);
					}

				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера вызова'), function() {this.FormPanel.find('name', 'Person_SurName')[0].focus(true, 250);}.createDelegate(this) );
				}
			},
			url: url,
			params: params
		});
	},
	existenceNumbersDayYear: function(){
		// проверка на уникальность введенного номера вызова за день и за год
		var me = this,
			base_form = me.FormPanel.getForm(),
			cmpCallCard_prmDate = base_form.findField('CmpCallCard_prmDate').getValue(),
			cmpCallCard_prmTime = base_form.findField('CmpCallCard_prmTime').getValue(),
			CmpCallCard_prmDT = Ext.util.Format.date(cmpCallCard_prmDate, 'Y-m-d') + ' ' + cmpCallCard_prmTime,
			CmpCallCard_Numv = base_form.findField('CmpCallCard_Numv'),
			CmpCallCard_Ngod = base_form.findField('CmpCallCard_Ngod');

		if(!CmpCallCard_Numv.getValue() || !CmpCallCard_Ngod.getValue()) return;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=existenceNumbersDayYear',
			params: {
				Day_num: CmpCallCard_Numv.getValue(),
				Year_num: CmpCallCard_Ngod.getValue(),
				Lpu_id: getGlobalOptions().lpu_id,
				CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue(),
				AcceptTime: CmpCallCard_prmDT
			},
			callback: function (opt, success, response) {
				if(success){
					var res = Ext.util.JSON.decode(response.responseText);
					if(res['existenceNumbersDay'] && !CmpCallCard_Numv.disabled ){
						//если такой номер вызова за день уже существует
						//то поставим значение предложенное системой
						CmpCallCard_Numv.setValue(res['nextNumberDay']);
					}
					if(res['existenceNumbersYear'] && !CmpCallCard_Ngod.disabled ){
						//если такой номер вызова за год уже существует
						CmpCallCard_Ngod.setValue(res['nextNumberYear']);
					}
				}
			}
		});
	},

	//ПАЦИЕНТ
	//поиск
	personSearch: function(){

		var me = this;

		if ( me.action == 'view' ) {
			return false;
		}

		var searchPersonWindow = getWnd('swPersonSearchWindow'),
			base_form = me.FormPanel.getForm(),
			personFirname = base_form.findField('Person_FirName').getValue(),
			personSecname = base_form.findField('Person_SecName').getValue(),
			personSurname = base_form.findField('Person_SurName').getValue();

		searchPersonWindow.show({
			onSelect: function(person_data) {
				searchPersonWindow.hide();
				me.setPersonData(person_data);
			},
			forObject: 'CmpCallCard',
			personFirname: personFirname,
			personSecname: personSecname,
			personSurname: personSurname,
			Person_Age: base_form.findField('Person_Age').getValue(),
			searchMode: 'all'
		});

		if( personFirname || personSecname || personSurname ){
			searchPersonWindow.doSearch();
		}
	},

	getPersonFields: function(){
		var personFieldset = this.FormPanel.find('name','personFieldset')[0];
		return this.getAllFields(personFieldset);
	},

	getAddressFields: function(){
		var addressFieldset = this.FormPanel.find('name','addressFieldset')[0];
		return this.getAllFields(addressFieldset);
	},

	//установка данных пациента в поля формы
	setPersonData: function(data){
		var me = this,
			personFields = me.getPersonFields(),
			base_form = me.FormPanel.getForm();

		//проверка на умершего
		if(me.personIsDead(data)){
			me.setStatusPanelMessage('dead');
			return false;
		}

		for(var index = 0; index < personFields.length; index++){

			var cmp = personFields[index],
				cmpName = cmp.name || cmp.hiddenName;

			if(cmpName == 'AgeUnit_id'){
				continue;
			}

			if(cmpName){
				//all
				cmp.reset();
				if(cmp.isVisible()) cmp.disable();

				//byName
				switch (cmpName){
					case 'Person_FirName': {
						cmp.setValue(data.Person_Firname);
						break;
					}
					case 'Person_SecName': {
						cmp.setValue(data.Person_Secname);
						break;
					}
					case 'Person_SurName': {
						cmp.setValue(data.Person_Surname);
						break;
					}
					case 'Person_Age_Inp':{
						if(swGetPersonAge(data.Person_Birthday, new Date())){
							cmp.setValue(swGetPersonAge(data.Person_Birthday, new Date()));
							cmp.originalValue = swGetPersonAge(data.Person_Birthday, new Date());
							base_form.findField('AgeUnit_id').reset()
							cmp.maxValue = 120;
							cmp.validate();
						}
						else{
							data.Person_Age = +data.Person_Age;

							//иногда приходит возраст или дата рождения, тогда нужно проставлять недостающее значение
							if(!data.Person_Age || !data.Person_Birthday){
								if(data.Person_Age || data.Person_Birthday){
									var issetField = data.Person_Age ? 'Person_Age' : 'Person_BirthDay';
									me.setPersonAgeFields(issetField);
								};
							};
						};
						break;
					}
					case 'Person_Age': {
						cmp.setValue(swGetPersonAge(data.Person_Birthday, new Date()));
						break;
					}
					case 'Person_BirthDay':{
						cmp.setValue(data.Person_Birthday);
						break;
					}
					default: {
						if(data[cmpName]){
							cmp.setValue(data[cmpName]);
						}
					}
				}
			}
		};
		me.setStatusPanelMessage('uno');

		me.checkUrgencyAndProfile();
		me.setEnableFields();


	},

	personReset: function(){
		var me = this,
			personFields = me.getPersonFields();

		if (me.action == 'view'){
			return false;
		}

		me.setStatusPanelMessage('hide');

		for(var index = 0; index < personFields.length; index++){
			var cmp = personFields[index],
				cmpName = cmp.name || cmp.hiddenName;

			if(cmpName){
				cmp.reset();
				if(cmp.isVisible()) cmp.enable();
			}
		};

		me.checkUrgencyAndProfile();

	},

	personUnknown: function(){
		var me = this,
			personFields = me.getPersonFields();

		if (me.action == 'view'){
			return false;
		}

		me.setStatusPanelMessage('none');

		for(var index = 0; index < personFields.length; index++){
			var cmp = personFields[index],
				cmpName = cmp.name || cmp.hiddenName;

			if(cmpName){

				cmp.reset();
				//if(cmp.isVisible()) cmp.disable();

				switch (cmpName){
					case 'Person_FirName':
					case 'Person_SecName':
					case 'Person_SurName':
					{
						cmp.setValue(langs('НЕИЗВЕСТЕН'));
						break;
					}
					case 'Person_IsUnknown':
					{
						cmp.setValue(2);
						break;
					}
					case 'Sex_id':{
						cmp.setValue(3);
						break;
					}
				}
			}
		}
	},

	//Расчет возраста на основе даты рождения
	//Устанавливает связку 4 полей: приема вызова, даты рождения, возраста b ед. возраста
	//editedField - редактируемое поле - от него зависит, в какую сторону будут заноситься изменения
	setPersonAgeFields: function(editedField) {
		var base_form = this.FormPanel.getForm(),
			prmDate = base_form.findField('CmpCallCard_prmDate').getValue(),//дата вызова
			birthdayField = base_form.findField('Person_BirthDay'),//дата рождения поле
			birthday = birthdayField.getValue(),//дата рождения значение
			Person_Age = base_form.findField('Person_Age'),//скрытый возраст в годах
			Person_Age_Inp = base_form.findField('Person_Age_Inp'),//возраст
			AgeUnit_id = base_form.findField('AgeUnit_id'),//ед. возраста
			date = new Date();

		switch (editedField){
			case 'CmpCallCard_prmDate':
			case 'Person_BirthDay': {
				if (Ext.isEmpty(birthday)) {
					Person_Age.setValue(null);
					Person_Age_Inp.setValue(null);
					Person_Age_Inp.originalValue = null;
					AgeUnit_id.setValue('years');
				} else {
					var years = swGetPersonAge(birthday, prmDate);

					Person_Age.setValue(years);

					if (years > 0) {
						Person_Age_Inp.setValue(years);
						Person_Age_Inp.originalValue = years;
						AgeUnit_id.setValue('years');
						Person_Age_Inp.maxValue = 120;
					} else {
						var days = Math.floor(Math.abs((prmDate - birthday)/(1000 * 3600 * 24)));
						var months = Math.floor(Math.abs(prmDate.getMonthsBetween(birthday)));

						if (months > 0) {
							Person_Age_Inp.setValue(months);
							Person_Age_Inp.originalValue = months;
							AgeUnit_id.setValue('months');
							Person_Age_Inp.maxValue = 11;
						} else {
							Person_Age_Inp.setValue(days);
							Person_Age_Inp.originalValue = days;
							AgeUnit_id.setValue('days');
							Person_Age_Inp.maxValue = 30;
						}
					}
				}
				break;
			}
			case 'Person_Age_Inp':
			case 'AgeUnit_id': {

				var inp = Person_Age_Inp.getValue(),
					type = AgeUnit_id.getValue(),
					calcBirthday;

				if(!inp){
					return;
				}

				switch (type){
					case 'years': {
						if(!Ext.isEmpty(inp)){
							Person_Age.setValue( inp );
						}
						//calcBirthday = date.add(Date.YEAR, -inp);
						calcBirthday = new Date((date.getFullYear() - inp).toString());
						calcBirthday.setMonth(0);
						calcBirthday.setDate(1);
						Person_Age_Inp.maxValue = 120;
						break;
					}
					case 'months': {
						//calcBirthday = date.add(Date.MONTH, -inp);
						calcBirthday = new Date(date.setMonth(date.getMonth() - inp));
						//calcBirthday.setDate(1); убрал сброс на 1 день
						Person_Age_Inp.maxValue = 11;
						Person_Age.setValue(0);
						break;
					}
					case 'days': {
						//calcBirthday = date.add(Date.DAY, -inp);
						calcBirthday = new Date(date.setDate(date.getDate() - inp));
						Person_Age_Inp.maxValue = 30;
						Person_Age.setValue(0);
						break;
					}
				}
				Person_Age_Inp.validate();

				birthdayField.setValue(calcBirthday);

				break;
			}

			default: return;
		}
	},

	//Функция идентификация персона
	checkPersonIdentification: function(){

		var me = this,
			base_form = me.FormPanel.getForm(),
			personFieldset = me.FormPanel.find('name','personFieldset')[0],
			personData = me.getAllValues(personFieldset),
			personBirtDayFrom, personBirtDayTo;

		if(me.getPersonSearchGridTransactionId && Ext.Ajax.isLoading(me.getPersonSearchGridTransactionId)){
			Ext.Ajax.abort(me.getPersonSearchGridTransactionId);
		};

		//если форма уже открыта или незаполнены фамилия имя и возраст
		if(
			me.selectPersonWin && me.selectPersonWin.isVisible()
		|| !(personData.Person_SurName && personData.Person_FirName && personData.Person_Age)
		){
			return;
		}

		var personBirtDay = Date.parseDate(personData.Person_BirthDay, 'd.m.Y');

		switch(personData.AgeUnit_id){
			case 'months':
			case 'days': {
				personBirtDayFrom =  Ext.util.Format.date(new Date(personBirtDay.setMonth(personBirtDay.getMonth()-1)), 'd.m.Y');
				personBirtDayTo =  Ext.util.Format.date(new Date(personBirtDay.setMonth(personBirtDay.getMonth()+1)), 'd.m.Y');
				break;
			}
			default: {
				break;
			}
		}

		personData.Person_BirthDay
		//перед идентификаией выводим сообщение о начале
		me.setStatusPanelMessage('loading');

		me.getPersonSearchGridTransactionId = Ext.Ajax.request({
			url: '/?c=Person&m=getPersonSearchGrid',
			autoAbort: true,
			params: {
				PersonSurName_SurName: personData.Person_SurName,
				PersonFirName_FirName: personData.Person_FirName,
				PersonSecName_SecName: personData.Person_SecName,
				PersonAge_AgeFrom: personData.Person_Age,
				PersonAge_AgeTo: personData.Person_Age,
				personBirtDayFrom : personBirtDayFrom,
				personBirtDayTo : personBirtDayTo,
				Sex_id: personData.Sex_id,
				checkForMainDB: true,
				limit: 100,
				searchMode: 'all',
				start: 0,
				ParentARM: base_form.findField('ARMType').getValue()
			},
			callback: function(o, success, r) {
				if( success ) {
					var response = Ext.util.JSON.decode(r.responseText);

					switch(true){
						case (response.totalCount == 0): {
							me.setStatusPanelMessage('none');
							break;
						}
						case (response.totalCount == 1): {
							var unoPersonData = response.data[0];

							//везде разные наименования блин
							unoPersonData.Person_Firname = response.data[0].PersonFirName_FirName;
							unoPersonData.Person_Secname = response.data[0].PersonSecName_SecName;
							unoPersonData.Person_Surname = response.data[0].PersonSurName_SurName;
							unoPersonData.Person_Birthday = response.data[0].PersonBirthDay_BirthDay;

							me.setPersonData(unoPersonData);

							break;
						}
						case (response.totalCount > 1): {
							me.selectPersonAfterRequest(response);
							break;
						}
						default: {
							me.setStatusPanelMessage('none');
							break;
						}
					};
				}
				else{
					me.setStatusPanelMessage('none');
				}
			}
		});

	},

	//выбор одного из списка пациентов
	selectPersonAfterRequest: function(personsData){

		var me = this;

		//если окноу уже есть просто переписываем данные и открываем
		if(me.selectPersonWin){
			var grid = me.selectPersonWin.find('xtype', 'grid')[0];

			grid.getStore().loadData(personsData);

			me.selectPersonWin.show();

			return;
		};

		me.selectPersonWin = new Ext.Window({
			width:980,
			heigth:600,
			title:'Выбор пациента',
			modal : false,
			closeAction: 'hide',
			draggable : false,
			resizable : false,
			items:[
				{
					xtype: 'grid',
					columns: [
						{ header: 'Person_id',  dataIndex: 'Person_id', width: 60, hidden: true },
						{ header: 'Фамилия',  dataIndex: 'Person_Surname', flex: 1 },
						{ header: 'Имя', dataIndex: 'Person_Firname', width: 80 },
						{ header: 'Отчество', dataIndex: 'Person_Secname', width: 100 },
						{ header: 'Дата рождения', dataIndex: 'Person_Birthday', width: 90 },
						{ header: 'Дата смерти', dataIndex: 'Person_deadDT', width: 90 },
						{ header: 'Адрес регистрации', dataIndex: 'UAddress_AddressText', width: 140 },
						{ header: 'Адрес проживания', dataIndex: 'PAddress_AddressText', width: 140 },
						{ header: 'ЛПУ прикрепления', dataIndex: 'Lpu_Nick', width: 90 }
					],
					store: new Ext.data.GroupingStore({
						data: personsData,
						fields: [
							{name: 'Person_id'},
							{name: 'Person_Surname'},
							{name: 'Person_Firname'},
							{name: 'Person_Secname'},
							{name: 'Person_Birthday'},
							{name: 'Person_deadDT'},
							{name: 'UAddress_AddressText'},
							{name: 'PAddress_AddressText'},
							{name: 'Lpu_Nick'},
							{name: 'Sex_id'},
							{name: 'Polis_Ser'},
							{name: 'Polis_Num'},
							{name: 'Polis_EdNum'}
						],
						reader: new Ext.data.JsonReader({
								root: 'data'
							},
							Ext.data.Record.create([
								{name: 'Person_id'},
								{name: 'Person_Surname', mapping: 'PersonSurName_SurName'},
								{name: 'Person_Firname', mapping: 'PersonFirName_FirName'},
								{name: 'Person_Secname', mapping: 'PersonSecName_SecName'},
								{name: 'Person_Birthday', mapping: 'PersonBirthDay_BirthDay'},
								{name: 'Person_deadDT'},
								{name: 'UAddress_AddressText'},
								{name: 'PAddress_AddressText'},
								{name: 'Lpu_Nick'},
								{name: 'Sex_id'},
								{name: 'Polis_Ser'},
								{name: 'Polis_Num'},
								{name: 'Polis_EdNum'}
							])
						)
					}),
					height: 350,
					view: new Ext.grid.GridView({
						forceFit: false
					}),
					listeners: {
						rowdblclick: function(e) {
							var grid = this,
								selected = grid?grid.getSelectionModel().getSelected():null;

							if(selected)
							{
								me.setPersonData(selected.data);
								me.selectPersonWin.close();
							}
						}
					}
				}
			],
			buttons:[
				{
					handler: function(){
						var grid = me.selectPersonWin.findByType('grid')[0],
							selected = grid?grid.getSelectionModel().getSelected():null;
						if(selected)
						{
							me.setPersonData(selected.data);
							me.selectPersonWin.close();
						}
					},
					iconCls: 'ok16',
					text: langs('Выбрать')
				},
				{
					text: '-'
				},
				{
					handler: function(){ShowHelp(this.ownerCt.title);},
					text: BTN_FRMHELP,
					iconCls: 'help16'
				},
				{
					handler: function() {
						me.selectPersonWin.close();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			]
		});

		me.selectPersonWin.show();

	},

	setStatusPanelMessage: function(mode){
		var me = this,
			statuspanel = me.FormPanel.find('name', 'status_panel')[0],
			statusfield = statuspanel.find('name', 'status_field')[0],
			lpupanel = me.FormPanel.find('name', 'lpu_panel')[0],
			lpufield = lpupanel.find('name', 'lpu_field')[0],
			addinfopanel = me.FormPanel.find('name', 'addinfo_panel')[0],
			addinfofield = addinfopanel.find('name', 'addinfo_field')[0],
			lpuAttach = me.FormPanel.getForm().findField('Lpu_Nick').getValue(),
			isOftenCaller = me.FormPanel.getForm().findField('Person_isOftenCaller').getValue(),
			msg;

		statuspanel.setVisible(true);

		switch(mode){
			case 'loading' :{
				msg = '<div style="margin-left: 10px; width: 200px; height: 16px; background-image: url(/extjs/resources/images/default/grid/loading.gif); background-repeat: no-repeat">Идентификация пациента...</div>'
				break;
			}
			case 'uno' :{
				msg = langs('Пациент идентифицирован');

				//доп инфо панели
				if (lpuAttach) {
					lpupanel.show();
					lpufield.getEl().update('<div style="margin-left: 5px; width: 250px; height: 32px;">Прикреплён к '+lpuAttach+'</div>');

				}else {
					lpupanel.hide();
				}

				if (isOftenCaller != 2 /* и, возможно, какие-то другие поля доп.информации */) {
					addinfopanel.hide();
				} else {
					var addInfo = '';
					addInfo += (isOftenCaller==2)? langs('Часто обращающийся</br>'): '';
					addinfopanel.show();
					addinfofield.getEl().update('<div>'+addInfo+'</div>');
				}
				break;
			}
			case 'none' :{
				msg = langs('Пациент не идентифицирован');
				break;
			}
			case 'dead' :{
				msg = langs('Пациент не идентифицирован');
				sw.swMsg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Выбор невозможен', function() {});
				break;
			}
			case 'hide': {
				statuspanel.setVisible(false);
				addinfopanel.hide();
				lpupanel.hide();
				break;
			}
			default:{
				statuspanel.setVisible(false);
				lpupanel.hide();
			}
		};

		statusfield.getEl().update(msg);
	},

	//проверка на умершего
	personIsDead: function(data){
		var opts = getGlobalOptions(),
			deathDate = data.Person_deadDT,
			base_form = this.FormPanel.getForm();

		function addDays(date, days) {
			var result = new Date(date);
			result.setDate(result.getDate() + days);
			return result;
		}

		if(data.Person_IsDead == 'true' && deathDate){
			if(deathDate.length != '' && deathDate.length<=10)
				deathDate = new Date(deathDate.replace(/^(\d{2}).(\d{2}).(\d{4})/,'$3-$2-$1'));
			if(!Ext.isEmpty(opts.limit_days_after_death_to_create_call) && parseInt(opts.limit_days_after_death_to_create_call,10)>0)
				deathDate = addDays(deathDate,parseInt(opts.limit_days_after_death_to_create_call,10))

			if(deathDate <= base_form.findField('CmpCallCard_prmDate').getValue())
			{
				return true;
			}
		}
		return false;
	},

	//АДРЕС
	// взаимосвязь адресных комбобоксов при редактировании
	// cmpChanged - измененный компонент
	// changedRecord - record измененного компонента
	setAddress: function(cmpChanged, changedRecord){
		var me = this,
			changedCmpName = cmpChanged.name || cmpChanged.hiddenName,
			addressFields = me.getAddressFields();

		for(var index = 0; index < addressFields.length; index++) {
			var cmp = addressFields[index],
				cmpName = cmp.name || cmp.hiddenName;

			if (cmpName) {
				switch(cmpName){
					//территория
					case 'KLAreaStat_idEdit': {
						//установка значения территории
						if(changedCmpName != cmpName){

							var rec = -1;

							switch(true){
								case ( !Ext.isEmpty(changedRecord.get('Town_id')) ) :{
									rec = cmp.getStore().find( 'KLTown_id', changedRecord.get('Town_id'), 0, false );
									break;
								}
								case ( !Ext.isEmpty(changedRecord.get('City_id')) ) :{
									rec = cmp.getStore().find( 'KLCity_id', changedRecord.get('City_id'), 0, false );
									break;
								}
								case ( !Ext.isEmpty(changedRecord.get('SubRGN_id')) ) :{
									rec = cmp.getStore().find( 'KLSubRGN_id', changedRecord.get('SubRGN_id'), 0, false );
									break;
								}
							};

							if(rec != -1) {
								cmp.setValue( cmp.getStore().getAt(rec).get('KLAreaStat_id') );
							}
							else cmp.reset();
						}
						break;
					}
					//район
					case 'KLSubRgn_id': {
						//поменялась территория и есть значение которое поставим
						if( changedCmpName.inlist(['KLAreaStat_idEdit']) ){
							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLSubRGN_id'),
								{ region_id: changedRecord.get('KLRGN_id') }
							);
						}
						break;
					}
					//город
					case 'KLCity_id': {
						//поменялась территория или район
						if( changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id']) ){
							var KLSubRGN_id = (
								changedRecord.get('SubRGN_id') //значение с комбика
								|| changedRecord.get('KLSubRGN_id') || changedRecord.get('KLRGN_id') //значение с территории
							);

							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLCity_id'),
								{ subregion_id: KLSubRGN_id }
							);

						}
						break;
					}
					//населенный пункт
					case 'KLTown_id': {
						//поменялся район или город
						if(changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id', 'KLCity_id'])){
							var city_id = (
								changedRecord.get('City_id') || changedRecord.get('SubRGN_id')//значения с комбиков
								|| changedRecord.get('KLCity_id') || changedRecord.get('KLSubRGN_id') ||  changedRecord.get('KLRGN_id')//значение с территории
							);
							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLTown_id'),
								{ city_id: city_id }
							);
						}
						break;
					}
					//улица
					case 'StreetAndUnformalizedAddressDirectory_id': {
						//поменялся район или город или нас. пункт
						if(changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id', 'KLCity_id', 'KLTown_id'])){
							var town_id = (
								changedRecord.get('SubRGN_id') || changedRecord.get('City_id') || changedRecord.get('Town_id') //поля с формы
								|| changedRecord.get('KLSubRGN_id') || changedRecord.get('KLCity_id') || changedRecord.get('KLTown_id') //поля комбика территория
							);

							me.setValueAfterStoreLoad(
								cmp,
								null,
								{ town_id: town_id },
								function(fieldCmp){
									var UnformalizedCmpCallCard_UlicSecond = this.FormPanel.getForm().findField('UnformalizedCmpCallCard_UlicSecond');
/*
									if(fieldCmp.getStore().getCount()){
										fieldCmp.allowBlank = false;
										fieldCmp.validate();
									}
									*/
									//@todo loadData не отрабатывает, потом переделать кусок ниже
									UnformalizedCmpCallCard_UlicSecond.getStore().data = fieldCmp.getStore().data;
								}.createDelegate(me)
							);
						}
						break;
					}
					case 'CmpCallCard_Dom': {
						break;
					}
					case 'CmpCallCard_Korp': {
						break;
					}
				}
			}
		}

	},

	//насколько я понял - функция устанавливает адрес на форму текущего подразделения / мо
	getLpuAddressTerritory: function() {
		var me = this,
			opts = getGlobalOptions(),
			base_form = this.FormPanel.getForm(),
			addressFields = me.getAddressFields();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getLpuAddressTerritory',
			callback: function(opt, success, response) {

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					var respData = response_obj[0];

					for (var index = 0; index < addressFields.length; index++) {
						var cmp = addressFields[index],
							cmpName = cmp.name || cmp.hiddenName;

						if (cmpName) {
							switch (cmpName) {
								case 'KLSubRgn_id':
								{
									if(+respData.KLSubRgn_id){
										me.setValueAfterStoreLoad(
											cmp,
											respData.KLSubRgn_id,
											{region_id: opts.region.number},
											function(cmp, rec){
												if(rec){
													cmp.fireEvent('beforeselect', cmp, rec);
												}
											}
										);
									};
									break;
								}
								case 'KLCity_id':
								{
									if(+respData.KLCity_id) {
										me.setValueAfterStoreLoad(
											cmp,
											respData.KLCity_id,
											{subregion_id: ( respData.KLSubRgn_id || opts.region.number )},
											function(cmp, rec){
												if(rec){
													cmp.fireEvent('beforeselect', cmp, rec);
												}
											}
										);
									};
									break;
								}
								case 'KLTown_id':
								{
									if(+respData.KLTown_id) {
										me.setValueAfterStoreLoad(
											cmp,
											respData.KLTown_id,
											{city_id: ( respData.KLCity_id || respData.KLSubRgn_id )},
											function(cmp, rec){
												if(rec){
													cmp.fireEvent('beforeselect', cmp, rec);
												}
											}
										);
									};
									break;
								}
							};
						};
					};
				};
			}
		});
	},

	//получение срочности вызова и профиля бригады
	//устанавливает значение поля вид вызова
	checkUrgencyAndProfile: function(){

		var me = this,
			base_form = me.FormPanel.getForm(),
			reasonCombo = base_form.findField('CmpReason_id'),
			age = base_form.findField('Person_Age').getValue(),
			callplace_value = base_form.findField('CmpCallPlaceType_id').getValue(),
			extraField = base_form.findField('CmpCallCard_IsExtra');

		if(reasonCombo.getValue() && callplace_value && base_form.findField('ARMType').getValue() != 'slneotl' && !extraField.disabled){

			//для Перми и Крыма свой набор захардкоженых поводов
			if (!getRegionNick().inlist(['perm', 'ekb'])) {
				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=getCallUrgencyAndProfile',
					callback: function (opt, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText),
								type_service_reason = response_obj.CmpCallCardAcceptor_SysNick;

							if (response_obj.Error_Msg) {
								Ext.Msg.alert('Ошибка', response_obj.Error_Msg);
							}
							else {
								if ("nmp" == type_service_reason) {
									extraField.setValue(2);
								}
								else {
									extraField.setValue(1);
								}
								me.setEnableFields();
							}
						}

					}.bind(this),
					params: {
						CmpReason_id: reasonCombo.getValue(),
						Person_Age: age || 0,
						CmpCallPlaceType_id: callplace_value
					}
				});
			}
			else{
				var selReasonRec = reasonCombo.store.query('CmpReason_id', reasonCombo.getValue(),false).get(0);

				if (selReasonRec) {
					var cmpFieldReasonCode = selReasonRec.get('CmpReason_Code'),
						IsNMP = cmpFieldReasonCode.inlist(['04Г', '04Д', '09Я', '11Л', '11Я', '12Г', '12К', '12Р', '12У', '12Э', '12Я', '13Л', '13М', '15Н', '17А', '13С', '40Ц']);

					extraField.setValue(IsNMP?2:1);
					me.setEnableFields();
				}
			}
		}
	},

	// функция проверки на дубль
	// clback - возвратка(true/false)
	// true - продолжить сохранение
	checkDuplicate: function(allParams, clback) {
		var me = this,
			base_form = this.FormPanel.getForm();

		if(me.action == 'add'){

			Ext.Ajax.request({
				url: '/?c=CmpCallCard&m=checkDuplicateCmpCallCard',
				params: {
					CmpCallCard_Comm : allParams.CmpCallCard_Comm,
					CmpCallCard_Dom : allParams.CmpCallCard_Dom,
					CmpCallCard_Etaj : allParams.CmpCallCard_Etaj,
					CmpCallCard_id : allParams.CmpCallCard_id,
					CmpCallCard_Kvar : allParams.CmpCallCard_Kvar,
					CmpCallCard_Podz : allParams.CmpCallCard_Podz,
					CmpCallCard_prmDate : allParams.CmpCallCard_prmDate,
					CmpCallCard_prmTime : allParams.CmpCallCard_prmTime,
					CmpReason_id : allParams.CmpReason_id,
					Person_Age : allParams.Person_Age,
					Person_BirthDay : allParams.Person_BirthDay,
					Person_FirName : allParams.Person_FirName,
					Person_PolisSer : allParams.Person_PolisSer,
					Person_PolisNum : allParams.Person_PolisNum,
					Person_SecName : allParams.Person_SecName,
					Person_SurName : allParams.Person_SurName,
					Sex_id : allParams.Sex_id,
					KLSubRgn_id : allParams.KLSubRgn_id,
					KLCity_id : allParams.KLCity_id,
					KLTown_id : allParams.KLTown_id,
					KLStreet_id : allParams.KLStreet_id
				},
				callback: function(opt, success, response) {
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj.data && response_obj.data.length > 0) {

							var ConfirmDublicateWin = new Ext.Window({
								width: 980,
								heigth: 600,
								title: langs('Возможно дублирование вызова'),
								modal: true,
								draggable: false,
								resizable: false,
								closable: false,
								items: [{
									xtype: 'grid',
									columns: [
										{
											dataIndex: 'CmpCallCard_prmDate',
											type: 'date',
											renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'),
											header: langs('Дата/время'),
											width: 110
										},
										{dataIndex: 'CmpCallCard_Ngod', header: langs('№ вызова (за год)'), width: 100},
										{dataIndex: 'Person_FIO', header: langs('Пациент'), width: 180},
										{dataIndex: 'CmpCallType_Name', header: langs('Тип вызова'), width: 120},
										{dataIndex: 'CmpReason_Name', header: langs('Повод'), width: 200},
										{dataIndex: 'Adress_Name', header: langs('Место вызова'), width: 250}
									],
									store: new Ext.data.GroupingStore({
										data: response_obj,
										fields: [{name: 'CmpCallCard_prmDate'}, {name: 'CmpCallCard_Ngod'}, {name: 'Person_FIO'}, {name: 'CmpCallType_Name'}, {name: 'CmpReason_Name'}, {name: 'Adress_Name'}],
										reader: new Ext.data.JsonReader({
												root: 'data'
											},
											Ext.data.Record.create([
												{name: 'CmpCallCard_prmDate'},
												{name: 'CmpCallCard_Ngod'},
												{name: 'Person_FIO'},
												{name: 'CmpCallType_Name'},
												{name: 'CmpReason_Name'},
												{name: 'Adress_Name'}
											])
										)
									}),
									height: 350,
									view: new Ext.grid.GridView({
										forceFit: false
									}),
									listeners: {}
								}],
								buttons: [
									{
										text: langs(' Продолжить сохранение?'),
										id: 'save',
										handler: function () {
											ConfirmDublicateWin.close();
											clback(true);
										}
									},
									{
										text: langs('Отменить сохранение'),
										handler: function () {
											ConfirmDublicateWin.close();
											clback(false);
										}
									}
								]
							});

							ConfirmDublicateWin.show();
						}
						else {
							clback(true);
						};
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при проверке дублирования вызова'));
						clback(false);
					}
				}
			});
		}
		else{
			clback(true);
		};

	},

	// функция открытия карты 110
	closeCmpCallCard: function() {

		var me = this,
			base_form = me.FormPanel.getForm(),
			wnd = 'swCmpCallCardNewCloseCardWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования талона вызова уже открыто'));
			return false;
		}

		var params = {
			action: 'edit',
			formParams: {
				CmpCallCard_id: base_form.findField('CmpCallCard_id').getValue(),
				ARMType: base_form.findField('ARMType').getValue(),
				CmpCloseCard_id: base_form.findField('CmpCloseCard_id').getValue()
			},
			callback: function(data) {
				if ( !data || !data.cmpCloseCardData ) {
					return false;
				};
				this.autoEvent = false;
			}
		};

		getWnd(wnd).show(params);

	},

	// функция простановки активности, редактируемости и видимости полей
	// эх, начнем...
	// field - изменяемый компонент
	// params - данные формы
	// переменные:
	// fields - изменяемый компонент или все
	// nextFocusCmp - компонент в который требуется поставить фокус
	// пока расширенная (обкаточная) версия

	/*
	* Пометка "если меняется этот компонент" нужна для случаев когда 2 элемента взаимосвязаны (чтобы не произошло цикличного взаимодействия)
	* или когда при установке значения в компонент оно само не очищалось
	* */
	setEnableFields: function(field, params){

		var me = this,
			region = getRegionNick(),
			base_form = me.FormPanel.getForm(),
			fields = (!Ext.isEmpty(field)) ? me.getAssignedComponents(field) : me.getAllFields(),
			isNmpArmType = base_form.findField('ARMType').getValue() == 'slneotl',
			isMStatArmType = base_form.findField('ARMType').getValue() == 'mstat',
			params = params ? params : {},
			nextFocusCmp = false,
			//статус вызова
			cmpCallCardStatusType_Code = base_form.findField('CmpCallCardStatusType_Code').getValue() || params.CmpCallCardStatusType_Code || 0,
			statusTypeEnable = me.action == 'add' || ( cmpCallCardStatusType_Code.inlist([1,3]) && region.inlist(['perm','astra']) ),
			//тип вызова
			cmpCallTypeCode = base_form.findField('CmpCallType_id').getFieldValue('CmpCallType_Code'),
			cmpCallTypeCodeAllowBlank = cmpCallTypeCode ? cmpCallTypeCode.inlist([6,15,16,17]) : true, //Тип вызова любое значение, кроме «Консультативное», «Консультативный», «Справка», «Абонент отключился».
			//региональная активность
			cmpCloseCard_id = base_form.findField('CmpCloseCard_id').getValue() || params.CmpCloseCard_id,
			regionalEnabled = (( Ext.isEmpty(cmpCloseCard_id) && region == 'perm') || region == 'astra' || me.action == 'add'), //Пермь и Астра у нас особые регионы
			//вторая улица
			cmpCallCard_Dom = base_form.findField('CmpCallCard_Dom'),
			secondStreetCombo = base_form.findField('UnformalizedCmpCallCard_UlicSecond'),
			crossRoadsMode = (!Ext.isEmpty(secondStreetCombo.getRawValue()) || !Ext.isEmpty(params.CmpCallCard_UlicSecond) || cmpCallCard_Dom.getValue() == '/'),
			//пациент
			//@todo выяснить когда пациент может редактироваться - когда нет.. можно ли неидентифицированного идентифицировать
			person_IsUnknown = base_form.findField('Person_IsUnknown').getValue(),
			personIsset = (!Ext.isEmpty(base_form.findField('Person_id').getValue()) || !Ext.isEmpty(params.Person_id) ),
			//вызов
			сmpCallCard_IsExtra = base_form.findField('CmpCallCard_IsExtra').getValue() == 1,
			lpuLocalCombo = base_form.findField('lpuLocalCombo'),
			lpuBuilding_id = base_form.findField('LpuBuilding_id'),
			lpu_smpid = base_form.findField('Lpu_smpid'),
			medService_id = base_form.findField('MedService_id'),
			cmpCallCard_IsPassSSMP = base_form.findField('CmpCallCard_IsPassSSMP'),
			cmpCallCard_IsPoli = base_form.findField('CmpCallCard_IsPoli'),
			AgeUnit_id = base_form.findField('AgeUnit_id');

		//золотые функции (взять на заметку)
		//setFieldValue
		//getFieldValue

		// поля
		for(var i = 0; i < fields.length; i++){
			var fieldCmp = fields[i],
				fieldName = fieldCmp.getName(),
				fieldVal = fieldCmp.getValue(),
				setHidden = false,
				clearFieldValue = false,
				setAllowBlank = true,
				setEnabled = true;

			//кнопки поиск сброс неизвестен

			switch(fieldName){
				//неактивные всегда
				case 'CmpCallCard_Numv':
				case 'CmpCallCard_Ngod': {
					setEnabled = (isNmpArmType && (me.action == 'add')) || (region == 'astra' && me.action == 'edit' && isMStatArmType);
					setAllowBlank = false;
					break;
				}
				case 'CmpCallCard_NumvPr':
				case 'CmpCallCard_NgodPr':{
					setHidden = !getRegionNick().inlist(['penza']);
					setEnabled = isNmpArmType && (me.action == 'add');
					break;
				}
				case 'CmpCallCard_prmTime':
				case 'CmpCallCard_prmDate': {
					setEnabled = me.action == 'add';
					break;
				}
				//пациент
				case 'Person_SurName': {
					setEnabled = (regionalEnabled || !personIsset);
					setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Person_FirName': {
					setEnabled = (regionalEnabled || !personIsset);
					//setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Person_SecName': {
					setEnabled = (regionalEnabled || !personIsset);
					//setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Person_BirthDay': {
					setEnabled = (regionalEnabled || !personIsset);
					//setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Person_Age_Inp': {

					switch (AgeUnit_id.getValue()){
						case 'years': {
							fieldCmp.maxValue = 120;
							break;
						}
						case 'months': {
							fieldCmp.maxValue = 11;
							break;
						}
						case 'days': {
							fieldCmp.maxValue = 30;
							break;
						}
					}
					setEnabled = (regionalEnabled || !personIsset);
					setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'AgeUnit_id': {
					setEnabled = (regionalEnabled || !personIsset);
					setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Sex_id': {
					setEnabled = (regionalEnabled && !personIsset) && person_IsUnknown != 2;
					setAllowBlank = cmpCallTypeCodeAllowBlank;
					break;
				}
				case 'Polis_Ser': {
					setEnabled = (regionalEnabled || !personIsset);
					break;
				}
				case 'Polis_Num': {
					setEnabled = (regionalEnabled || !personIsset);
					break;
				}
				case 'Polis_EdNum': {
					setEnabled = (regionalEnabled || !personIsset);
					break;
				}

				//территория
				case 'KLAreaStat_idEdit': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					break;
				}
				case 'KLSubRgn_id': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					break;
				}
				case 'KLCity_id': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					break;
				}
				case 'KLTown_id': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					break;
				}
				case 'StreetAndUnformalizedAddressDirectory_id': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					setAllowBlank = ( !fieldCmp.getStore().getCount() && !params['StreetAndUnformalizedAddressDirectory_id'] ) || cmpCallTypeCodeAllowBlank || me.action == "view";

					//setAllowBlank = fieldCmp.getStore().getCount() ? false : true;
					break;
				}
				case 'UnformalizedCmpCallCard_UlicSecond': {
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');

					if(!crossRoadsMode){
						fieldCmp.reset();
						//если меняется этот компонент
						if(field && field.getName() == fieldName){
							nextFocusCmp = 'CmpCallCard_Dom';
						}
					};

					setHidden = !crossRoadsMode;
					break;
				}
				case 'CmpCallCard_Dom': {

					if(crossRoadsMode){
						fieldCmp.reset();
						//если меняется этот компонент
						if(field && field.getName() == fieldName){
							nextFocusCmp = 'UnformalizedCmpCallCard_UlicSecond';
						}
					};

					setHidden = crossRoadsMode;
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm');
					break;
				}
				case 'CmpCallCard_Korp': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallCard_Kvar': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallCard_Room': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallCard_Podz': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallCard_Etaj': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallCard_Kodp': {
					if(crossRoadsMode){
						fieldCmp.reset();
					};
					setEnabled = (!( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm') && !crossRoadsMode);
					break;
				}
				case 'CmpCallPlaceType_id': {
					//не доступно только для перми при закрытой карте
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm' );
					break;
				}
				//вызов
				case 'CmpCallType_id': {
					setAllowBlank = false;
					//доступно только для перми при отсутствии закрытой
					setEnabled = ( Ext.isEmpty(cmpCloseCard_id) && region == 'perm' ) || me.action == 'add';
					break;
				}
				case 'CmpReason_id': {
					setAllowBlank = cmpCallTypeCodeAllowBlank;
					setEnabled = ( Ext.isEmpty(cmpCloseCard_id) && region == 'perm' ) || me.action == 'add';
					break;
				}
				case 'CmpCallCard_IsExtra': {
					if(isNmpArmType){
						setEnabled = false;
						if(me.action == 'add'){
							fieldCmp.setValue(2);
						};
					}else{
						//setEnabled = regionalEnabled;
						setEnabled = statusTypeEnable;
					}
					break;
				}
				//Вызов передан в поликлинику по телефону (рации)
				case 'CmpCallCard_IsPoli': {
					setHidden = сmpCallCard_IsExtra && !isNmpArmType
					//setEnabled = regionalEnabled;
					setEnabled = statusTypeEnable;

					clearFieldValue = сmpCallCard_IsExtra;
					//если меняется этот компонент
					if(field && field.getName() == fieldName && fieldVal) {
						cmpCallCard_IsPassSSMP.setValue(false);
					};
					break;
				}
				// МО передачи (НМП)
				case 'lpuLocalCombo': {
					setHidden = сmpCallCard_IsExtra && !isNmpArmType;
					//setEnabled = regionalEnabled && (!сmpCallCard_IsExtra || isNmpArmType); //&& Ext.isEmpty(lpuBuilding_id.getValue());
					setEnabled = statusTypeEnable && (!сmpCallCard_IsExtra || isNmpArmType); //&& Ext.isEmpty(lpuBuilding_id.getValue());
					//если меняется этот компонент
					if(field && field.getName() == fieldName && !Ext.isEmpty(fieldVal)) {
						cmpCallCard_IsPassSSMP.setValue(false);
					}
					else{
						clearFieldValue = ( сmpCallCard_IsExtra || cmpCallCard_IsPassSSMP.getValue() || !Ext.isEmpty(lpuBuilding_id.getValue()) );
					}
					break;
				}
				// Служба НМП
				case 'MedService_id': {
					//setEnabled = ((!Ext.isEmpty(lpuLocalCombo.getValue()) || !Ext.isEmpty(params.Lpu_ppdid) || !Ext.isEmpty(params.LpuTransmit_id)) && !cmpCallCard_IsPassSSMP.getValue() && regionalEnabled);
					setEnabled = ((!Ext.isEmpty(lpuLocalCombo.getValue()) || !Ext.isEmpty(params.Lpu_ppdid) || !Ext.isEmpty(params.LpuTransmit_id)) && !cmpCallCard_IsPassSSMP.getValue() && statusTypeEnable);
					setHidden = сmpCallCard_IsExtra && !isNmpArmType;
					clearFieldValue = сmpCallCard_IsExtra || cmpCallCard_IsPassSSMP.getValue() || !Ext.isEmpty(lpuBuilding_id.getValue());
					break;
				}
				case 'CmpCallCard_IsPassSSMP': {
					//setEnabled = !isNmpArmType && regionalEnabled;
					setEnabled = !isNmpArmType && statusTypeEnable;
					setHidden = getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1;
					//если меняется этот компонент
					if(field && field.getName() == fieldName && fieldVal) {
						cmpCallCard_IsPoli.setValue(false);
					};
					break;
				}
				// МО передачи (СМП)
				case 'Lpu_smpid': {
					setHidden = !cmpCallCard_IsPassSSMP.getValue();
					//setEnabled = regionalEnabled;
					setEnabled = statusTypeEnable;
					setAllowBlank = !cmpCallCard_IsPassSSMP.getValue();
					clearFieldValue = ( cmpCallCard_IsPoli.getValue() || !Ext.isEmpty(lpuLocalCombo.getValue()) || !cmpCallCard_IsPassSSMP.getValue() );
					break;
				}
				// Подразделение СМП
				case 'LpuBuilding_id': {
					//setEnabled = regionalEnabled || region.inlist(['krym','astra']);

					setEnabled = !isNmpArmType && (me.action == 'add' || ( cmpCallCardStatusType_Code.inlist([1,3]) && region.inlist(['perm','astra','krym']) ));
					//если меняется этот компонент
					if(field && field.getName() == fieldName && fieldVal) {
						cmpCallCard_IsPoli.setValue(false);
					}
					else{
						clearFieldValue = cmpCallCard_IsPoli.getValue() || !Ext.isEmpty(lpuLocalCombo.getValue());
						setAllowBlank = !(!cmpCallTypeCodeAllowBlank && сmpCallCard_IsExtra && !cmpCallCard_IsPassSSMP.getValue());
					}
					break;
				}
				case 'CmpCallerType_id' :{
					setEnabled = regionalEnabled;
					break;
				}
				case 'CmpCallCard_Telf' :{
					setHidden = getRegionNick().inlist(['ufa']);
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm' );
					break;
				}
				case 'CmpCallCard_Comm' :
				{
					setEnabled = !( !Ext.isEmpty(cmpCloseCard_id) && region == 'perm' );
					break;
				}
				case 'CmpCallCard_isControlCall' :
				{
					//Оставляем так, как есть. Видимость меняется отдельным методом
					setHidden = !fieldCmp.isVisible();
					break;
				}
				case 'CmpRejectionReason_id':
				case 'CmpCallCardRejection_Comm':
				{
					setHidden = !fieldCmp.isVisible();
				}
			};

			if(fieldCmp.getXType() != 'hidden')
				setHidden ? fieldCmp.hideContainer() : fieldCmp.showContainer();

			fieldCmp.allowBlank = setAllowBlank;
			fieldCmp.setDisabled( !setEnabled );

			if(clearFieldValue){
				fieldCmp.clearValue ? fieldCmp.clearValue() : fieldCmp.setValue(false);
			}

			fieldCmp.validate();
		};


		// остальное
		// кнопки персона
		var personBtnPanel = me.FormPanel.find('refId', 'personBtnPanel');

		if(personBtnPanel[0]){
			personBtnPanel[0].items.each(function(button){
				button.setDisabled( !(regionalEnabled || !personIsset) );
			});
		};

		//доп. обработка
		//смена фокуса
		if(nextFocusCmp){
			base_form.findField(nextFocusCmp).focus();
		}

	},

	// функция возвращает массив взаимосвязанных компонентов
	// объединенных по правилам активности / обязательности / скрытости
	// field - компонент
	getAssignedComponents: function(field){
		var me = this,
			cmpName = field.name || field.hiddenName,
			currentGroup,
			reverseAssignedFields = [
				'CmpCallCard_Dom', 'UnformalizedCmpCallCard_UlicSecond',
				'CmpCallCard_IsPoli', 'lpuLocalCombo', 'MedService_id', 'CmpCallCard_IsPassSSMP', 'Lpu_smpid', 'LpuBuilding_id'
			]; //поля которые влияют на друг друга

		if(!cmpName) return;

		// заметка

		// Тип вызова влияет на
		// CmpCallType_id -> Person_SurName, Person_BirthDay, Person_Age_Inp, AgeUnit_id, Sex_id, CmpReason_id, LpuBuilding_id, StreetAndUnformalizedAddressDirectory_id
		// обязательное для заполнения, если Тип вызова любое значение, кроме «Консультативное», «Консультативный», «Справка», «Абонент отключился».

		// двусторонняя связка
		// дом <-> вторая улица CmpCallCard_Dom <-> UnformalizedCmpCallCard_UlicSecond + сопутствующие поля

		// Группа взаимосвязанных полей раздела Вызов c двусторонней связкой
		// CmpCallCard_IsExtra -> CmpCallCard_IsPoli -> lpuLocalCombo  -> MedService_id -> CmpCallCard_IsPassSSMP -> Lpu_smpid
		// Вид вызова -> Флаг Вызов передан в пол-ку -> МО передачи (НМП) -> Служба НМП -> Флаг Вызов передан в другую ССМП по телефону (рации) -> МО передачи (СМП)

		// LpuBuilding_id поле, состоящее в нескольких группах зависимостей

		//взаимосвязь полей
		var dependingsArrays = {
			CmpCallType_id: ['CmpCallType_id', 'Person_SurName', 'Person_FirName', 'Person_SecName', 'Person_BirthDay', 'Person_Age_Inp', 'AgeUnit_id', 'Sex_id', 'CmpReason_id', 'LpuBuilding_id', 'StreetAndUnformalizedAddressDirectory_id'],
			CmpCallCard_Dom: ['CmpCallCard_Dom','CmpCallCard_Korp', 'CmpCallCard_Kvar', 'CmpCallCard_Room','CmpCallCard_Podz','CmpCallCard_Etaj','CmpCallCard_Kodp','UnformalizedCmpCallCard_UlicSecond'],
			CmpCallCard_IsExtra: ['CmpCallCard_IsPoli', 'lpuLocalCombo', 'MedService_id', 'CmpCallCard_IsPassSSMP', 'Lpu_smpid', 'LpuBuilding_id'],
			//LpuBuilding_id: ['CmpCallCard_IsPoli', 'lpuLocalCombo', 'MedService_id']
		};

		//если двусторонняя взаимосвязь - ищем по значениям объекта
		if(cmpName.inlist(reverseAssignedFields)){
			var fieldNames = [];
			for (var group in dependingsArrays){
				if(cmpName.inlist(dependingsArrays[group])){
					for (var i = 0; i < dependingsArrays[group].length; i++) {
						if(!dependingsArrays[group][i].inlist(fieldNames)){
							fieldNames.push(dependingsArrays[group][i]);
				}
					}
				}
			};

			return me.getComponentsByName(fieldNames);
			};

		if(dependingsArrays[cmpName]){
			return me.getComponentsByName(dependingsArrays[cmpName]);
		};
	},

	//возвращает массив компонентов по массиву имен
	getComponentsByName: function(fields){
		var me = this,
			base_form = me.FormPanel.getForm(),
			arrayCmps = [];

		for(var i = 0; i < fields.length; i++){
			//var cmp = me.FormPanel.find('name', fields[i]) || me.FormPanel.find('hiddenName', fields[i]);
			var cmp = base_form.findField( fields[i] );

			if(cmp){
				arrayCmps.push(cmp);
			};
		};

		return arrayCmps;
	},

	//Вспомогательные функции

	//установка значения в комбик просле загрузки стора
	// cmp - компонент
	// val - значение
	// params - параметры загрузки
	// clb - возвратка
	setValueAfterStoreLoad: function(cmp, val, params, clb){
		var me = this,
			connection = cmp.getStore().proxy.getConnection(),
			transId = connection.transId ? connection.transId.tId : false,
			storeIsLoading = connection.isLoading(transId);

		if(!storeIsLoading){
			me.loadCounter.countLoadingStores++;
		}

		cmp.getStore().load({
			params: params,
			callback: function(o, success){

				me.loadCounter.countLoadedStores++;

				if(me.loadCounter.countLoadingStores == me.loadCounter.countLoadedStores && me.loadMask.el.isVisible()){
					me.loadMask.hide();
				}

				if(o && o.length){
					var record = this.findRecord(this.valueField, val);

					if(val && record){
					this.setValue(val);
						if(clb) clb(cmp, record);
					}
					else{
						this.setValue(null);
					if(clb) clb(cmp);
				}
				}
				else{
					this.getStore().removeAll();
					this.reset();
				}

			}.createDelegate(cmp)
		});


	},
	checkIsCallControllFlag: function(){
		var me = this;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getIsCallControllFlag',
			success: function (response){
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if(responseObj.length > 0){
					responseObj = responseObj[0];
				}
				me.FormPanel.getForm().findField('CmpCallCard_isControlCall').setVisible(responseObj.SmpUnitParam_IsCallControll == 'true');
			}

		});
	},

	checkRejection: function (values) {
		var me = this,
			baseForm = me.FormPanel,
			commentField = baseForm.find('name', 'CmpCallCardRejection_Comm')[0],
			rejectReasonField = baseForm.find('hiddenName', 'CmpRejectionReason_id')[0],
			rec = rejectReasonField.store.getAt(rejectReasonField.store.find('CmpRejectionReason_name', values? values.CmpRejectionReason_Name: ''));

		commentField.hideContainer();
		rejectReasonField.hideContainer();

		if (!values || !values.CmpRejectionReason_Name) return;

		commentField.showContainer();
		rejectReasonField.showContainer();
		rejectReasonField.setFieldLabel('Причина отказа');
		commentField.setFieldLabel('Комментарий');
		rejectReasonField.setValue(rec.get('CmpRejectionReason_id'));

		commentField.setValue(values.CmpCallCardStatus_Comment? values.CmpCallCardStatus_Comment: '');
	}

});


