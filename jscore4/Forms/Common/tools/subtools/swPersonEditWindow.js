Ext.define('sw.tools.subtools.swPersonEditWindow', {
    extend: 'Ext.window.Window',
    title: 'Добавление пациента',
    layout: 'fit',
	itemId: 'PersonEditWindow', // id не нужен, чтобы не было ошибок при повторном вызове окна
	modal: true,
    width: 800,
    minHeight: 500,
	action: 'add',
	
	// Функция возвращаемая после сохранения формы
	returnFunc: Ext.emptyFn,
	
	C_PERSON_SAVE: '/?c=Person4e&m=savePersonEditWindow',
	
	disablePolisFields: function( disable, clear ){
		if ( typeof disable != 'boolean' ) {
			disable = true;
		}
		if ( typeof clear != 'boolean' ) {
			clear = false;
		}
		
		//  id or name
		var fields = [
				'PolisType_id',
				'Polis_Ser',
				'Polis_Num',
				'Federal_Num',
				'OrgSMO_id',
				'Polis_begDate',
				'Polis_endDate',
				'PolisFormType_id'
			],
			form = this.getPersonFormPanel();
		
		for( var i=0,cnt=fields.length; i<cnt; i++ ){
			var field = form.findField( fields[i] );
			if ( field === null ) {
				log( "Field "+fields[i]+" is null." );
				continue;
			}
			form.findField( fields[i] ).setDisabled( disable );
			if ( disable && clear ) {
				form.findField( fields[i] ).setRawValue('');
			}
		}
	},

	/**
	 * Установка параметров группе элементов
	 * @param params Object {fields: Array/String}
	 */
	setFieldsParams: function(params){
		if(! params) return;
		if(! Array.isArray(params)){
			params = [params];
		}
		var form = this.getPersonFormPanel();
		for(var i = 0; i < params.length; i++){
			var obj = params[i];
			if(! obj.fields) continue;
			if(! Array.isArray(obj.fields)){
				obj.fields = [obj.fields];
			}
			for(var j = 0; j < obj.fields.length; j++){
				var field = form.findField(obj.fields[j]);
				if(! field){
					//console.log('--field not exists!');
					continue;
				}
				if((typeof obj.regex) !== 'undefined'){
					if(obj.regex){
						try{
							field.regex = new RegExp(obj.regex);
						}catch(e){
							//console.log('--ошибка при создании RegExp:', e);
						}finally{
							obj.regex = undefined;
						}
					}else{
						obj.regex = undefined;
					}
				}
				if((typeof obj.disable) === 'boolean'){
					field.setDisabled(obj.disable);
				}
				if((typeof obj.enable) === 'boolean'){
					field.setDisabled(! obj.enable);
				}
				if((typeof obj.allowBlank) === 'boolean'){
					field.allowBlank = obj.allowBlank;
				}
				if((typeof obj.clear) === 'boolean' && obj.clear){
					field.reset();
				}
				if((typeof obj.reset) === 'boolean' && obj.reset){
					field.reset();
				}
				if(obj.val){
					field.setValue(obj.val);
				}
				if(obj.value){
					field.setValue(obj.value);
				}
				if((typeof obj.validate) === 'boolean' && obj.validate){
					field.validate();
				}
			}
		}
	},

	disableDocumentFields: function( disable, clear ){
		if ( typeof disable != 'boolean' ) {
			disable = true;
		}
		if ( typeof clear != 'boolean' ) {
			clear = false;
		}
		
		//  id or name
		var fields = [
				'Document_Ser',
				'Document_Num',
				'OrgDep_id',
				'Document_begDate',
				'KLCountry_id',
				'Document_IsTwoNation'
			],
			form = this.getPersonFormPanel();
		
		for( var i=0,cnt=fields.length; i<cnt; i++ ){
			var field = form.findField( fields[i] );
			if ( field === null ) {
				log( "Field "+fields[i]+" is null." );
				continue;
			}
			form.findField( fields[i] ).setDisabled( disable );
			if ( disable && clear ) {
				form.findField( fields[i] ).setRawValue('');
			}
		}
	},
	
	showSearchPersonWindow: function(callback){
		var win = Ext.create('sw.tools.subtools.swPersonWinSearch',{
			callback: callback
		});
		win.show();
		
		return win;
	},
	
	getAddressField: function(prefix,fieldLabel,copyFromField){
		var me = this;
	
		var setFieldValue = function(name,value){
			var field = me.getPersonFormPanel().findField(name);
			if (field) {
				field.setValue(value);
			}
		};
		var getFieldValue = function(name) {
			var field = me.getPersonFormPanel().findField(name);
			if (field) {
				return field.getValue();
			}
		};		
		
		return {
			xtype: 'triggerfield',
			name: prefix + 'Address_AddressText',
			fieldLabel: fieldLabel,
			labelAlign: 'top',
			validationEvent: false, 
			validateOnBlur: false, 
			trigger1Cls: 'equal16',
			trigger2Cls: 'x-form-search-trigger',// был search16, картинка лупы слишком маленькая
			trigger3Cls: 'x-form-clear-trigger',
			configObj: {
				Address_Zip: prefix + 'Address_Zip',
				Country_id: prefix + 'KLCountry_id',
				KLRegion_id: prefix + 'KLRGN_id',
				KLSubRGN_id: prefix + 'KLSubRGN_id',
				KLCity_id: prefix + 'KLCity_id',
				KLTown_id: prefix + 'KLTown_id',
				KLStreet_id: prefix + 'KLStreet_id',
				Corpus: prefix + 'Address_Corpus',
				House: prefix + 'Address_House',
				Flat: prefix + 'Address_Flat',
				Address_begDate: prefix + 'Address_begDate',
				full_address: prefix + 'Address_Address'
			},
			onTrigger1Click: function(){
				var copyFromFieldConfig = me.getPersonFormPanel().findField(copyFromField).configObj || {};
				for( var key in copyFromFieldConfig ){
					if ( !this.configObj.hasOwnProperty(key) ) {
						continue;
					}
					setFieldValue( this.configObj[key], getFieldValue( copyFromField ) );
				}
			},
			onTrigger2Click: function(){ 
				var field = this;
				Ext.create('common.tools.swAddressEditWindow',{
					callback: function(data){
						for (var key in data) {
							if (!field.configObj.hasOwnProperty(key)) {
								continue;
							}
							setFieldValue(field.configObj[key],data[key]);
							
							// Дополнительно записываем адрес в основное поле
							if ( key == 'full_address' ) {
								field.setValue(data[key]);
							}
						};
					}
				});
			},
			onTrigger3Click: function() {
				for( var key in this.configObj ){
					setFieldValue(this.configObj[key],null);
				};
			},
			flex: 1,
			enableKeyEvents: true
		};
	},
	
	generateAddressHiddenFields: function(configObj){
		var result = [];
		for( var key in configObj ){
			result.push({
				xtype: 'hidden',
				name: configObj[ key ]
			});
		}
		return result;
	},

	/**
	 * Установка полей в зависимости от страны документа
	 * Поля:
	 * Document_Ser - серия
	 * Document_Num - номер
	 * OrgDep_id - организация, выдавшая документ
	 * Document_begDate - дата выдачи
	 * KLCountry_id - гражданство
	 * Document_IsTwoNation - гражданин имеет два гражданства
	 * @param record Object из комбобокса
	 */
	setDocumentFields: function(record){
		switch(record.get('DocumentType_Code')){
			case 1:// Паспорт гражданина СССР
			case 3:// Свидетельство о рождении Российской Федерации
			case 4:// Удостоверение личности офицера
			case 6:// Паспорт Минморфлота
			case 7:// Военный билет
			case 8:// Дипломатический паспорт гражданина Российской Федерации
			case 14:// Паспорт гражданина Российской Федерации
			case 15:// Заграничный паспорт гражданина Российской Федерации
			case 16:// Паспорт моряка
			case 17:// Военный билет офицера запаса
			case 99:// Удостоверение личности военнослужащего
				this.setFieldsParams([{
					fields: 'Document_Ser',// серия важна
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskSer')
				},{
					fields: 'Document_Num',// номер важен
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskNum')
				},{
					fields: 'KLCountry_id',
					enable: true,
					val: 643// по умолчанию гражданство российское
				},{
					fields: 'Document_IsTwoNation',
					enable: true
				}]);
				break;

			case 2:// Загранпаспорт гражданина СССР
			case 5:// Справка об освобождении из места лишения свободы
			case 13:// Временное удостоверение личности гражданина Российской Федерации
			case 27:// Копия жалобы о лишении статуса беженца
			case 28:// Иной документ, соответствующий свид-ву о предост. убежища на территории РФ
				this.setFieldsParams([{
					fields: 'Document_Ser',// серия не важна
					enable: true,
					allowBlank: true,
					validate: true,
					regex: record.get('DocumentType_MaskSer')
				},{
					fields: 'Document_Num',// номер важен
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskNum')
				},{
					fields: 'KLCountry_id',
					enable: true,
					val: 643// по умолчанию гражданство российское
				},{
					fields: 'Document_IsTwoNation',
					enable: true
				}]);
				break;

			case 9:// Паспорт иностранного гражданина
			case 10:// Свидетельство о регистрации ходатайства о признании иммигранта беженцем
			case 11:// Вид на жительство
			case 12:// Удостоверение беженца в Российской Федерации
			case 18:// Иные документы
			case 21:// Документ иностранного гражданина
			case 23:// Разрешение на временное проживание
			case 24:// Свидетельство о рождении, выданное не в Российской Федерации
			case 26:// Удостоверение сотрудника Евразийской экономической комиссии
				this.setFieldsParams([{
					fields: 'Document_Ser',// серия не важна
					enable: true,
					allowBlank: true,
					validate: true,
					regex: record.get('DocumentType_MaskSer')
				},{
					fields: 'Document_Num',// номер важен
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskNum')
				},{
					fields: 'KLCountry_id',
					enable: true,
					clear: true
				},{
					fields: 'Document_IsTwoNation',
					disable: true
				}]);
				break;

			case 22:// Документ лица без гражданства
				this.setFieldsParams([{
					fields: 'Document_Ser',// серия не важна
					enable: true,
					allowBlank: true,
					validate: true,
					regex: record.get('DocumentType_MaskSer')
				},{
					fields: 'Document_Num',// номер важен
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskNum')
				},{
					fields: 'KLCountry_id',
					disable: true,
					clear: true
				},{
					fields: 'Document_IsTwoNation',
					disable: true
				}]);
				break;

			case 25:// Свидетельство о предоставлении временного убежища на территории РФ
				this.setFieldsParams([{
					fields: 'Document_Ser',// серия важна
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskSer')
				},{
					fields: 'Document_Num',// номер важен
					enable: true,
					allowBlank: false,
					validate: true,
					regex: record.get('DocumentType_MaskNum')
				},{
					fields: 'KLCountry_id',
					enable: true,
					clear: true
				},{
					fields: 'Document_IsTwoNation',
					disable: true
				}]);
				break;

			default:
				this.setFieldsParams([{
					fields: ['Document_Ser', 'Document_Num'],
					disable: true,
					clear: true,
					regex: undefined,
					allowBlank: true
				},{
					fields: ['OrgDep_id', 'Document_begDate', 'KLCountry_id', 'Document_IsTwoNation'],
					disable: true,
					clear: true,
					allowBlank: true
				}]);
		}
	},
	
	personFormPanel: function(){
		var me = this,
			UAddress_Address = this.getAddressField('U','Адрес регистрации','PAddress_AddressText'),
			PAddress_Address = this.getAddressField('P','Адрес проживания','UAddress_AddressText'),
			BAddress_Address = this.getAddressField('B','Адрес рождения','UAddress_AddressText'),
			UAddress_hidden = this.generateAddressHiddenFields(UAddress_Address.configObj),
			PAddress_hidden = this.generateAddressHiddenFields(PAddress_Address.configObj),
			BAddress_hidden = this.generateAddressHiddenFields(BAddress_Address.configObj),
			addresses_fields = [ UAddress_Address, PAddress_Address, BAddress_Address ],
			addresses_hidden = UAddress_hidden.concat(PAddress_hidden,BAddress_hidden);
	
		this._person_form_panel = Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',
			cls: 'mainFormNeptune',
			id: false, // auto generate
			autoScroll: true,
			flex: 1,
			width: '100%',
			height: '100%',
			url: this.C_PERSON_SAVE,
			items: [{
				xtype: 'container',
				autoHeight: true,
				items: [{
					xtype: 'container',
					layout: 'column',
					width: '100%',
					items: [{
						xtype: 'container',
						margin: '0 10 0 0',
						columnWidth: 0.6,
						defaults: {
							labelAlign: 'top',
							width: '100%'
						},
						items: [{
							// + (Плюс означает, что поле реализовано)
							xtype: 'textfield',
							name: 'Person_SurName',
							fieldLabel: 'Фамилия',
							allowBlank: false,
							plugins: [new Ux.Translit(true, true)]
						},{
							// +
							xtype: 'textfield',
							name: 'Person_FirName',
							fieldLabel: 'Имя',
							plugins: [new Ux.Translit(true, true)]
						},{
							// +
							xtype: 'textfield',
							name: 'Person_SecName',
							fieldLabel: 'Отчество',
							plugins: [new Ux.Translit(true, true)]
						}]
					},{
						xtype: 'container',
						margin: '0 10 0 0',
//						columnWidth: 0.3,
						defaults: {
							labelAlign: 'top'
						},
						items: [{
							// +
							xtype: 'swdatefield',// datefield
							name: 'Person_BirthDay',
							fieldLabel: 'Дата рождения',
							allowBlank: true,
							format: 'd.m.Y',
							maxValue: getGlobalOptions().date,
							minValue: getMinBirthDate()
						},{
							// +
							xtype: 'sexCombo',
							name: 'PersonSex_id',
							forceSelection: true,
							allowBlank: true
						}]
					},{
						xtype: 'container',
						columnWidth: 0.4,
						defaults: {
							labelAlign: 'top',
							width: '100%'
						},
						items: [{
							xtype: 'textfield',
							name: 'PersonPhone_Phone',
							fieldLabel: 'Номер телефона',
							plugins: [new Ux.InputTextMask('+7(999)-999-99-99', true)],
							allowBlank: true
							//maskRe: /[0-9\-\s]{1,16}/
						},{
							// Изменяется на сайте записи
							xtype: 'textfield',
							name: 'PersonInfo_InternetPhone',
							fieldLabel: 'Номер телефона с сайта записи',
							readOnly: true
						}]
					}]
				},
				new Ext.tab.Panel({
					activeTab: 0,
					items: [{
						title: '1. Пациент.',
						height: 250,
						overflowY: 'scroll',
						bodyPadding: '10 0',
						items: [{
							xtype: 'container',
							layout: 'column',
							defaults: {
								labelAlign: 'top'
							},
							items: [{
								margin: '0 10 0 0',
								// +
								xtype: 'swSnilsField',
								name: 'Person_SNILS',
								columnWidth: 0.5
							},{
								// +
								xtype: 'swSocStatusCombo',
								name: 'SocStatus_id',
								forceSelection: true,
								columnWidth: 0.5
							}]
						},{
							// Дополнительные поля адресных полей в отдельном блоке,
							// из-за того что в том же блоке съезжает верстка
							// https://www.sencha.com/forum/showthread.php?259781-extjs-4.2.0-hiddenfield-occupies-the-visile-place-in-the-form
							xtype: 'container',
							hidden: true, 
							items: addresses_hidden
						},{
							xtype: 'fieldset',
							title: 'Адрес',
							collapsible: true,
							defaults: {
								anchor: '100%',
								labelWidth: 150
							},
							items: addresses_fields
						},{
							xtype: 'fieldset',
							title: 'Полис',
							collapsible: true,
							padding: '0 10 6',
							items: [{
								layout: 'column',
								border: 0,
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'swOmsSprTerrCombo',
									forceSelection: true,
									name: 'OMSSprTerr_id',
									columnWidth: 0.4,
									allowBlank: true,
									editable: true,
									tpl: '<tpl for="."><div class="x-boundlist-item "><font color="red">{OMSSprTerr_Code}</font>&nbsp;{OMSSprTerr_Name}</div></tpl>',
									listeners: {
										select: function(combo, record){
											if(record.length && record.length > 0) record = record[0];
											if(record.index < 0){
												this.disablePolisFields(true, true);
												return;
											}
											//this.disablePolisFields(false);

											//var form = this.getPersonFormPanel();

											// первое переключение:
											// тип полиса: показать, не пустой, проверить
											// форма полиса: показать, не пустой, проверить
											// ед. номер: показать, не пустой, проверить
											// кем выдан
											// дата выдачи

											this.setFieldsParams([{
												fields: 'PolisType_id',// тип полиса
												enable: true,
												allowBlank: false,
												validate: true,
												val: 4
											},{
												fields: ['PolisFormType_id','Federal_Num','OrgSMO_id', 'Polis_begDate'],// форма полиса, ед.номер, организация, дата регистрации
												enable: true,
												allowBlank: false,
												validate: true
											},{
												fields: 'Polis_endDate',
												enable: true
											}]);


										}.bind(this),
										change: function(combo, newValue){
											if(! newValue) {
												this.disablePolisFields(true, true);// заблокировать с бросить
											}
										}.bind(this)

										/*,
										change: function(obj, newValue, oldValue, eOpts){
											if ( !newValue ) {
												this.disablePolisFields();
												return;
											}
											
											this.disablePolisFields(false);
											
											var form = this.getPersonFormPanel();
											
											// Фильтр СМО в зависимости от выбранного региона
											var idx = obj.getStore().find(obj.name,obj.value);
											var KLRgn_id = obj.getStore().getAt(idx).get('KLRgn_id');
											var OrgSMOCombo = form.findField('OrgSMO_id');
											OrgSMOCombo.clearValue();
											OrgSMOCombo.getStore().clearFilter();
											OrgSMOCombo.getStore().filter('KLRgn_id',KLRgn_id);

											// @todo Реализация из двойки функционала выбора территории полиса для регионов
										}.bind(this)*/
									}
								},{
									xtype: 'swPolisTypeCombo',
									name: 'PolisType_id',
									columnWidth: 0.3,
									allowBlank: false,
									forceSelection: true,
									editable: false,
									tpl: '<tpl for="."><div class="x-boundlist-item "><font color="red">{PolisType_Code}</font>&nbsp;{PolisType_Name}</div></tpl>',
									listeners: {
										select: function(combo, record){
											if(combo.getValue() == 4){
												this.setFieldsParams([{
													fields: ['Polis_Ser', 'Polis_Num'],
													disable: true,
													clear: true
												},{
													fields: 'Federal_Num',
													enable: true,
													allowBlank: false,
													validate: true
												}]);
											}else{
												this.setFieldsParams([{
													fields: 'Polis_Ser',
													enable: true
												},{
													fields: 'Polis_Num',
													enable: true,
													allowBlank: false,
													validate: true
												},{
													fields: 'Federal_Num',
													disable: true,
													clear: true
												}]);
											}
										},
										change: function(obj, newValue){
											if(! newValue){
												this.setFieldsParams([{
													fields: ['Polis_Ser', 'Polis_Num', 'Federal_Num'],
													disable: true,
													clear: true
												}]);
											}
										},
										scope: this
									}
								},{
									margin: '0 0 0 10',
									xtype: 'swPolisFormTypeCombo',
									name: 'PolisFormType_id',
									fieldLabel: 'Форма полиса',
									editable: false,
									allowBlank: false,
									forceSelection: true,
									columnWidth: 0.3,
									tpl: '<tpl for="."><div class="x-boundlist-item "><font color="red">{PolisFormType_Code}</font>&nbsp;{PolisFormType_Name}</div></tpl>'
								}]
							},{
								layout: 'column',
								border: 0,
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									// +
									xtype: 'textfield',
									name: 'Polis_Ser',
									fieldLabel: 'Серия',
									width: 200
								},{
									margin: '0 10',
									// +
									xtype: 'textfield',
									name: 'Polis_Num',
									fieldLabel: 'Номер',
									maskRe: /\d/,
									columnWidth: 0.5
								},{
									// +
									xtype: 'textfield',
									name: 'Federal_Num',
									fieldLabel: 'Ед. номер',
									maskRe: /\d/,
									maxLength: 16,
									minLength: 16,
									enforceMaxLength: true,
									labelWidth: 80,
									columnWidth: 0.5,
									allowBlank: false
								}]
							},{
								layout: 'column',
								border: 0,
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									xtype: 'swOrgSMOCombo',
									name: 'OrgSMO_id',
									allowBlank: false,
									typeAhead: false,
									triggerClear: true,
									editable: true,
									translate: false,
									forceSelection: true,
									width: 300
								},{
									margin: '0 10',
									xtype: 'datefield',
									name: 'Polis_begDate',
									fieldLabel: 'Дата выдачи',
									format: 'd.m.Y',
									maxValue: getGlobalOptions().date,
									allowBlank: false,
									//plugins: [new Ux.InputTextMask('99.99.9999')],// раз внизу откл, то и тут для общего стиля
									columnWidth: 0.5
								},{
									xtype: 'datefield',
									name: 'Polis_endDate',
									fieldLabel: 'Дата закрытия',
									format: 'd.m.Y',
									maxValue: getGlobalOptions().date,
									allowBlank: true,
									//plugins: [new Ux.InputTextMask('99.99.9999')],// не валидно после потери фокуса, нужно доделывать компонент
									columnWidth: 0.5
								}]
							}]
						},{
							xtype: 'fieldset',
							title: 'Документ',
							collapsible: true,
							padding: '0 10 6',
							defaults: {
								labelAlign: 'left',
								labelWidth: 150
							},
							items: [{
								layout: 'column',
								border: 0,
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									xtype: 'swDocumentTypeCombo',
									name: 'DocumentType_id',
									width: 300,
									forceSelection: true,
									tpl: '<tpl for="."><div class="x-boundlist-item "><font color="red">{DocumentType_Code}</font>&nbsp;{DocumentType_Name}</div></tpl>',
									listeners: {
										select: function(combo, record){
											if(record.length && record.length > 0) record = record[0];
											if(record.index < 0){
												this.disableDocumentFields(true, true);
												return;
											}

											// активировать поля Выдан и Дата выдачи
											this.setFieldsParams([{
												fields: ['OrgDep_id', 'Document_begDate'],
												enable: true,
												allowBlank: true,
												clear: true,
												validate: true
											}]);

											this.setDocumentFields(record);

										}.bind(this),
										change: function(obj, newValue){
											if ( !newValue ) {
												this.disableDocumentFields(true, true);
												return;
											}
										}.bind(this)
									}
								},{
									margin: '0 10',
									// +
									xtype: 'textfield',
									name: 'Document_Ser',
									fieldLabel: 'Серия',
									columnWidth: 0.5
								},{
									// +
									xtype: 'textfield',
									name: 'Document_Num',
									fieldLabel: 'Номер',
									columnWidth: 0.5
								}]
							},{
								layout: 'column',
								border: 0,
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									// @todo Реализовать вызов окна с формой поиска организации выдавшей документ
									xtype: 'dOrgCombo',//swOrgDepCombo
									name: 'OrgDep_id',
									fieldLabel: 'Организация',
									triggerClear: true,
									forceSelection: true,
									width: 300,
									listeners: {
										change: function(combo, newValue){
											combo.store.getProxy().extraParams = {
												OrgType: 'dep',
												Org_Nick: newValue
											};
										}
									}
								},{
									margin: '0 0 0 10',
									// +
									xtype: 'datefield',
									name: 'Document_begDate',
									fieldLabel: 'Дата выдачи',
									format: 'd.m.Y',
									maxValue: getGlobalOptions().date,
									columnWidth: 0.5
								}]
							},{
								layout: 'column',
								border: 0,
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'SwKLCountryCombo',
									name: 'KLCountry_id',
									fieldLabel: 'Гражданство',
									labelAlign: 'top',
									columnWidth: 0.5,
									tpl: '<tpl for="."><div class="x-boundlist-item "><font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}</div></tpl>',
									listeners: {
										select: function(combo, records){
											var form = this.getPersonFormPanel(),
												code = records[0].get('KLCountry_Code');

											if ( code == 643 ) {
												form.findField('Document_IsTwoNation').enable();
											} else {
												form.findField('Document_IsTwoNation').disable();
												form.findField('Document_IsTwoNation').setValue(false);
											}
										}.bind(this)
									}
								},{
									// +
									margin: '18 5 4 5',
									xtype: 'checkbox',
									name: 'Document_IsTwoNation',
									boxLabel: 'Гражданин Российской Федерации и иностранного государства (двойное гражданство)',
									labelSeparator: '',
									columnWidth: 0.5
								}]
							}]
						},{
							xtype: 'fieldset',
							title: 'Место работы',
							collapsible: true,
							defaults: {
								labelAlign: 'left',
								labelWidth: 150,
							},
							items: [{
								// @todo Реализовать вызов окна с формой поиска организации
								xtype: 'dOrgCombo',
								name: 'Org_id',
								fieldLabel: 'Место работы/учебы',
								listeners: {
									change: function(combo, newValue, oldValue, eOpts){
										var union = combo.up('form').getForm().findField('OrgUnion_id');
										union.clearValue();
										union.getStore().load({
											params: {
												Org_id: newValue
											}
										});										
									}
								}
							},{
								// +
								xtype: 'swOrgUnionCombo',
								name: 'OrgUnion_id',
								tpl: '<tpl for="."><div class="x-boundlist-item">{OrgUnion_Name}</div></tpl>'
							},{
								// +
								xtype: 'swPostCombo',
								name: 'Post_id',
								tpl: '<tpl for="."><div class="x-boundlist-item">{Post_Name}</div></tpl>'
							}]
						},{
							xtype: 'container',
							items: [{
								// +
								xtype: 'swOnkoOccupationClassCombo',
								name: 'OnkoOccupationClass_id',
								labelWidth: 250
							},{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'datefield',
									name: 'Person_deadDT',
									fieldLabel: 'Дата смерти',
									readOnly: true,
									format: 'd.m.Y',
									width: 250
								},{
									// +
									xtype: 'datefield',
									name: 'Person_closeDT',
									fieldLabel: 'Дата закрытия',
									readOnly: true,
									format: 'd.m.Y',
									width: 250
								}]
							}]
						}]
					},{
						title: '2. Дополнительно.',
						height: 250,
						autoScroll: true,
						defaults: {
							labelWidth: 150
						},
						items: [{
							xtype: 'fieldset',
							title: 'Представитель',
							collapsible: true,
							defaults: {
								labelWidth: 150
							},
							items: [{
								// +
								xtype: 'swDeputyKindCombo',
								name: 'DeputyKind_id'
							},{
								xtype:'container',
								margin: '0 0 5 0',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [{
									flex: 1,
									xtype: 'textfield',
									name: 'DeputyPerson_FIO',
									fieldLabel: 'Представитель',
									labelWidth: 150,
									readOnly: true,
									margin: '0 5 0 0',
									listeners: {
										focus: function(field,focusEvt,evtOpts){
											var form = field.up('form').getForm();
											var win = this.showSearchPersonWindow(function(result){
												if ( !result ) {
													return;
												}
												if ( result.Person_id ) {
													form.findField('DeputyPerson_id').setValue( result.Person_id );
												}
												var fio = '';
												if ( result.PersonSurName_SurName ) {
													fio += result.PersonSurName_SurName;
												}
												if ( result.PersonFirName_FirName ) {
													fio += ' ' + result.PersonFirName_FirName;
												}
												if ( result.PersonSecName_SecName ) {
													fio += ' ' + result.PersonSecName_SecName;
												}
												if ( fio != '' ) {
													form.findField('DeputyPerson_FIO').setValue( fio );
												}
											});
										}.bind(this)
									}
								},{
									xtype: 'hidden',
									name: 'DeputyPerson_id',
									value: 0,
								},{
									margin: '0 0 0 5',
									xtype: 'button',
									iconCls: 'search16',
									name: 'searchbutton',
									tooltip: 'Поиск человека',
									handler: function(btn,evnt){
										var form = btn.up('form').getForm();
										var win = this.showSearchPersonWindow(function(result){
											if ( !result ) {
												return;
											}
											if ( result.Person_id ) {
												form.findField('DeputyPerson_id').setValue( result.Person_id );
											}
											var fio = '';
											if ( result.PersonSurName_SurName ) {
												fio += result.PersonSurName_SurName;
											}
											if ( result.PersonFirName_FirName ) {
												fio += ' ' + result.PersonFirName_FirName;
											}
											if ( result.PersonSecName_SecName ) {
												fio += ' ' + result.PersonSecName_SecName;
											}
											if ( fio != '' ) {
												form.findField('DeputyPerson_FIO').setValue( fio );
											}
										});
									}.bind(this)
								}]
							}]
						},{
							// +
							xtype: 'textfield',
							name: 'PersonSocCardNum_SocCardNum',
							fieldLabel: 'Номер соц. карты',
							maskRe: /\d/,
							maxLength: 30,
							inputAttrTpl: 'maxlength=30'
						},{
							// +
							xtype: 'swYesNoCombo',
							name: 'PersonRefuse_IsRefuse',
							fieldLabel: 'Отказ от льготы'
						},{
							xtype: 'textfield',
							name: 'PersonInn_Inn',
							fieldLabel: 'ИНН',
							maskRe: /\d/,
							maxLength: 12,
							minLength: 12,
							inputAttrTpl: 'maxlength=12'
						},{
							xtype: 'fieldset',
							title: 'Семейное положение',
							collapsible: true,
							defaults: {
								labelAlign: 'left',
								labelWidth: 300
							},
							items: [{
								// +
								xtype: 'swYesNoCombo',
								name: 'PersonFamilyStatus_IsMarried',
								fieldLabel: 'Состоит в зарегистрированном браке',
								listeners: {
									change: function(combo,newValue,oldValue){
										if ( newValue != 1 ) {
											combo.up('form').getForm().findField('FamilyStatus_id').clearValue();
										}
									}
								}
							},{
								// +
								xtype: 'swFamilyStatusCombo',
								name: 'FamilyStatus_id'
							}]
						},{
							// +
							xtype: 'swYesNoCombo',
							name: 'PersonChildExist_IsChild',
							fieldLabel: 'Есть дети до 16-ти',
						},{
							// +
							xtype: 'swYesNoCombo',
							name: 'PersonCarExist_IsCar',
							fieldLabel: 'Есть автомобиль',
						},{
							// +
							xtype: 'swEthnosCombo',
							name: 'Ethnos_id'
						}]
					},{
						title: '3. Специфика. Детство.',
						height: 250,
						autoScroll: true,
						items: [{
							layout: 'column',
							defaults: {
								labelAlign: 'top'
							},
							items: [{
								margin: '0 10 0 0',
								// +
								xtype: 'swResidPlaceCombo',
								name: 'ResidPlace_id',
								columnWidth: 0.5
							},{
								// +
								xtype: 'swPersonSprTerrDopCombo',
								name: 'PersonSprTerrDop_id',
								columnWidth: 0.5
							}]
						},{
							xtype: 'fieldset',
							title: 'Семья',
							collapsible: true,
							items: [{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'swYesNoCombo',
									name: 'PersonChild_IsManyChild',
									fieldLabel: 'Многодетная',
									columnWidth: 0.5
								},{
									// +
									xtype: 'swYesNoCombo',
									name: 'PersonChild_IsBad',
									fieldLabel: 'Неблагополучная',
									columnWidth: 0.5
								}]
							},{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'swYesNoCombo',
									name: 'PersonChild_IsIncomplete',
									fieldLabel: 'Неполная',
									columnWidth: 0.5
								},{
									// +
									xtype: 'swYesNoCombo',
									name: 'PersonChild_IsTutor',
									fieldLabel: 'Опекаемая',
									columnWidth: 0.5
								}]
							},{
								margin: '10 10 0 0',
								// +
								xtype: 'swYesNoCombo',
								name: 'PersonChild_IsMigrant',
								fieldLabel: 'Вынужденные переселенцы',
								labelWidth: 200
							}]
						},{
							layout: 'column',
							defaults: {
								labelAlign: 'top'
							},
							items: [{
								margin: '0 10 0 0',
								// +
								xtype: 'swHealthKindCombo',
								name: 'HealthKind_id',
								columnWidth: 0.5
							},{
								// +
								xtype: 'swYesNoCombo',
								name: 'PersonChild_IsYoungMother',
								fieldLabel: 'Юная мать',
								columnWidth: 0.5
							}]
						},{
							margin: '10 10 0 0',
							// +
							xtype: 'swFeedingTypeCombo',
							name: 'FeedingType_id',
							fieldLabel: 'Способ вскармливания',
							labelWidth: 200
						},{
							xtype: 'fieldset',
							title: 'Инвалидность',
							collapsible: true,
							items: [{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'swYesNoCombo',
									name: 'PersonChild_IsInvalid',
									fieldLabel: 'Инвалидность',
									columnWidth: 0.5,
									listeners: {
										change: function(combo,newValue){
											var form = this.getPersonFormPanel(),
												// record = combo.getStore().getById(newValue) // этот способ почему-то не работает
												record = combo.getStore().getAt(combo.getStore().findExact('YesNo_id',newValue))
											;
											
											if ( record && record.get('YesNo_Code') == 1 ) {
												form.findField('PersonChild_invDate').enable();
												form.findField('InvalidKind_id').enable();
											} else {
												form.findField('InvalidKind_id').disable();
												form.findField('PersonChild_invDate').disable();
												form.findField('PersonChild_invDate').setRawValue('');
											}
										}.bind(this)
									}
								},{
									// +
									xtype: 'swInvalidKindCombo',
									name: 'InvalidKind_id',
									columnWidth: 0.5
								}]
							},{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									margin: '0 10 0 0',
									// +
									xtype: 'swHealthAbnormCombo',
									name: 'HealthAbnorm_id',
									columnWidth: 0.5
								},{
									// +
									xtype: 'swHealthAbnormVitalCombo',
									name: 'HealthAbnormVital_id',
									columnWidth: 0.5
								}]
							},{
								layout: 'column',
								defaults: {
									labelAlign: 'top'
								},
								items: [{
									// +
									margin: '0 10 0 0',
									xtype: 'swDiagCombo',
									name: 'Diag_id',
									fieldLabel: 'Диагноз',
									columnWidth: 0.5
								},{
									// +
									xtype: 'datefield',
									name: 'PersonChild_invDate',
									fieldLabel: 'Дата установки',
									format: 'd.m.Y',
									maxValue: getGlobalOptions().date,
									columnWidth: 0.5
								}]
							}]
						}]
					}]
				}) // new Ext.tab.Panel
				]
			}]
		});
		
		return this._person_form_panel;
	},
	
	_person_form_panel: null, // ссылка на панель
	
	getPersonFormPanel: function(){
		return this._person_form_panel.getForm();
	},

	doSave: function(){
		var form = this.getPersonFormPanel();
		if ( !form.isValid() ) {
			Ext.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}
		
		// @todo Добавить условия проверки формы в зависимости от региона
		// @todo Добавить проверку дубликатов
		
		this.doSubmit();
	},
	
	doSubmit: function() {
		var me = this,
			form = this.getPersonFormPanel();
		
		var mask = new Ext.LoadMask(this,{msg:'Подождите, идет сохранение...'});
		mask.show();
		
		form.submit({
			waitTitle: 'Сохранение данных...',
			success: function(form, action){
				mask.hide();
				if ( action.result.success ) {
					Ext.Msg.alert('Сообщение', 'Данные успешно сохранены.');
					me.hide();
					me.returnFunc({
						Person_id: action.result.Person_id,
						Server_id: action.result.Server_id,
						PersonEvn_id: action.result.PersonEvn_id,
						PersonData: {
							Person_id: action.result.Person_id,
							Server_id: action.result.Server_id,
							PersonEvn_id: action.result.PersonEvn_id,
							Person_FirName: form.findField('Person_FirName').getValue(),
							Person_SurName: form.findField('Person_SurName').getValue(),
							Person_SecName: form.findField('Person_SecName').getValue(),
							Person_BirthDay: form.findField('Person_BirthDay').getValue(),
							Person_Snils: form.findField('Person_SNILS').getValue()
						}
					});
				} else {
					Ext.Msg.alert('Ошибка', action.result.Error_Msg);
				}
			},
			failure: function(form, action){
				mask.hide();
				if (action.result && action.result.Error_Msg == 'db_unable_to_connect') {
					var msg = 'Нет связи с основным сервером.';
					if (me.forObject == 'CmpCallCard') {
						msg += ' Человек будет добавлен в систему после восстановления связи.';
					} else {
						msg += ' Добавление человека невозможно.';
					}
					Ext.Msg.alert('Ошибка', msg);
				} else {
					Ext.Msg.alert('Ошибка', action.result ? action.result.Error_Msg : 'Не удалось получить ответ от сервера.');
				}
				me.afterTryAdd(form, action.result);
			}
		});
	},

	initComponent: function(){
		var me = this;
		
        Ext.applyIf(this,{
            items: [ this.personFormPanel() ],
            dockedItems: [{
				xtype: 'container',
				dock: 'bottom',
				layout: {
					type: 'hbox',
					align: 'stretch',
					padding: 4
				},
				items: [{
					xtype: 'container',
					layout: 'column',
					items: [{
						xtype: 'button',
						iconCls: 'add16',
						text: 'Добавить',
						handler: function(){
							this.doSave();
						}.bind(this)
					},{
						xtype: 'button',
						text: 'Сброс',
						margin: '0 5',
						iconCls: 'resetsearch16',
						handler: function(){
						}
					}]
				},{
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'end'
					},
					items: [{
						xtype: 'button',
						iconCls: 'cancel16',
						text: 'Закрыть',
						margin: '0 5',
						handler: function(){
							me.close()
						}
					}]
				}]
			}]
		});
	   
	   this.callParent(arguments);
	},
	
	show: function(){
		this.callParent(arguments);

		this.afterTryAdd = Ext.emptyFn;
		this.forObject = null;
		
		if ( typeof arguments[0] != 'undefined' && typeof arguments[0].callback == 'function' ) {
			this.returnFunc = arguments[0].callback;
		}
		if (arguments[0] && arguments[0].afterTryAdd) {
			this.afterTryAdd = arguments[0].afterTryAdd;
		}
		if (arguments[0] && arguments[0].forObject) {
			this.forObject = arguments[0].forObject;
		}
		
		this.disablePolisFields();
		this.disableDocumentFields();
	}
});
