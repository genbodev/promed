/**
* swAddressEditWindow - окно редактирования адреса.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @version      04.12.2014
*/
/*NO PARSE JSON*/
	
Ext.define('common.tools.swAddressEditWindow', {
    extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: 700,
	refId: 'AddressEditWindow',
	closable: true,
	id: 'AddressEditWindow',
	//cls: 'PersonSearchWin',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
    title: 'Адрес',
    Address_begDateHidden: false,
    unformalized: false, // при редактировании неформализованного адреса, номера дома квартиры не нужны
    setFields: false, // флаг загрузки значений, что бы не срабатывал "change"
	callback: Ext.emptyFn,
	updateZipCode: function() {
		var me = this,
			getField= function(name){
				return me.AddressForm.getForm().findField(name);
			};

		var town_id = isNaN(parseInt(getField('KLTown_id').getValue()))?null:parseInt(getField('KLTown_id').getValue());// || getField('KLCity_id').getValue();
		var street_id = isNaN(parseInt(getField('KLStreet_id').getValue()))?null:parseInt(getField('KLStreet_id').getValue());
		var house = getField('House').getValue();
		
		

		if (!town_id || !street_id) {
			Ext.Ajax.request({
				url: '/?c=Address&m=getZipAddressByStreetAndHome',
				params: {
					town_id: town_id,
					street_id: street_id,
					house: house
				},
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var response_obj = Ext.JSON.decode(response.responseText);
						if (response_obj && response_obj.Address_Zip && !isNaN(parseInt(response_obj.Address_Zip))) {
							me.AddressForm.getForm().findField('Address_Zip').setValue(response_obj.Address_Zip);
						}
					}
				}
			})
		}

	},
	updateFullAddressField: function() {
		var me = this,
			fields= [
				'Address_Zip',
				'Country_id',
				'KLRegion_id',
				'KLSubRGN_id',
				'KLCity_id',
				'KLTown_id',
				'KLStreet_id',
				'SecondKLStreet_id',
				'Corpus',
				'House',
				'Flat',
			],
			addressArr = [],
			getField= function(name){
				return me.AddressForm.getForm().findField(name);
			},
			getFieldRawValue = function(name){
				if (getField(name)) {
					return getField(name).getRawValue();
				} else {
					return false;
				}
			},
			getFieldValue = function(name){
				if (getField(name)) {
					return getField(name).getRawValue();
				} else {
					return false;
				}
			},
			isString = function(par) {
				return (typeof par == 'string')
			},
			getStrValue = function(name) {
				var val = getFieldRawValue(name);
				return (isString(val)) ? val : ''
			},
			i,
			val;
			
		for (i = 0; i <fields.length; i++) {
			
			val = getStrValue(fields[i]);		

			if (val) {

				switch (fields[i]) {
					case 'Corpus':
						val = 'корп. '+getStrValue(fields[i]);
						break;
					case 'House':
						val = 'д. '+getStrValue(fields[i]);
						break;
					case 'Flat':
						val = 'кв. '+getStrValue(fields[i]);
						break;
					default:
						break;
				}

				addressArr.push(val);
			}
		};

		

		getField('full_address').setValue(addressArr.join(', '));
		
	},
	initComponent: function() {
		var me = this;
		
		me.AddressForm = Ext.create('sw.BaseForm', {
			flex: 1,
			id: 'AddressForm',
			// cls: 'mainFormNeptune',
			border: false,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelWidth: 120
			},
			padding:10,
			items: [{
				xtype: 'SwKLCountryCombo',
				name: 'Country_id',
				allowBlank: false,
				listeners: {
					change: function(combo, newValue, oldValue, eOpts ) {
						if(me.setFields) return false;
						var rgn = me.AddressForm.getForm().findField('KLRegion_id');
						
						if (!isNaN(parseInt(newValue))) {
							rgn.reset();
							rgn.getStore().load({
								params:{
									country_id:newValue
								}
							});
						} else {
							rgn.getStore().removeAll();
						}
						me.updateFullAddressField();
					}
				}
			},{
				xtype: 'klregioncombo',
				name: 'KLRegion_id',
				allowBlank: false,
				listeners: {
					change: function(combo, newValue, oldValue, eOpts ) {
						if (!oldValue && !newValue) {
							return false;
						}
						if(me.setFields) return false;

						var subrgn = me.AddressForm.getForm().findField('KLSubRGN_id');
						var city = me.AddressForm.getForm().findField('KLCity_id');
						var town = me.AddressForm.getForm().findField('KLTown_id');
						
						if (!isNaN(parseInt(newValue))) {
							
							subrgn.reset();
							city.reset();
							town.reset();
							
							subrgn.getStore().load({
								params:{
									region_id:newValue
								}
							});

							city.getStore().load({
								params: {
									subregion_id: newValue
								}
							});

							town.getStore().load({
								params: {
									city_id: newValue
								}
							});


						} else {
							subrgn.getStore().removeAll();
							city.getStore().removeAll();
							town.getStore().removeAll();
						}
						me.updateFullAddressField();
					}
				}
			}, {
				xtype: 'klsubrgncombo',
				name: 'KLSubRGN_id',
				listeners: {
					change: function(combo, newValue, oldValue, eOpts ) {
						if (!oldValue && !newValue) {
							return false;
						}					
						if(me.setFields) return false;
						var city = me.AddressForm.getForm().findField('KLCity_id');
						var town = me.AddressForm.getForm().findField('KLTown_id');
						if (!isNaN(parseInt(newValue))) {
							
							
							city.reset();
							town.reset();
								
							city.getStore().load({
								params: {
									subregion_id: newValue
								}
							});

							town.getStore().load({
								params: {
									city_id: newValue
								}
							});


						} else {
							city.getStore().removeAll();
							town.getStore().removeAll();
						}
						me.updateFullAddressField();
					}
				}
			}, {
				xtype: 'klcitycombo',
				name: 'KLCity_id',
				listeners: {
					change: function(combo, newValue, oldValue, eOpts ) {
						
						if (!oldValue && !newValue) {
							return false;
						}
						if(me.setFields) return false;
						var town = me.AddressForm.getForm().findField('KLTown_id');
						var street = me.AddressForm.getForm().findField('KLStreet_id');
						var secondStreet = me.AddressForm.getForm().findField('SecondKLStreet_id');

						if (!isNaN(parseInt(newValue))) {

							town.reset();
							street.reset();
							
							town.getStore().load({
								params: {
									city_id: newValue
								}
							});

							street.getStore().load({
								params: {
									town_id: newValue
								},
								callback: function(recs){
									secondStreet.getStore().loadData(recs);
								}
							});


						} else {
							town.getStore().removeAll();
							street.getStore().removeAll();
							secondStreet.getStore().removeAll();
						}
						me.updateFullAddressField();
						//me.updateZipCode()
					}
				}
			}, {
				xtype: 'kltowncombo',
				name: 'KLTown_id',
				listeners: {
					change: function(combo, newValue, oldValue, eOpts ) {
						if (!oldValue && !newValue) {
							return false;
						}
						if(me.setFields) return false;
						var street = me.AddressForm.getForm().findField('KLStreet_id');
						var secondStreet = me.AddressForm.getForm().findField('SecondKLStreet_id');
						
						if (!isNaN(parseInt(newValue))) {
							
							street.reset();
							secondStreet.reset();

							street.getStore().load({
								params: {
									town_id: newValue
								},
								callback: function(recs){
									secondStreet.getStore().loadData(recs);
								}
							});

						} else {
							street.getStore().removeAll();
							secondStreet.getStore().removeAll();
						}
						me.updateFullAddressField();
						me.updateZipCode()
					}
				}
			},
				{
					xtype: 'klstreetcombo',
					name: 'KLStreet_id',
					listeners: {
						change: function(field, newValue, oldValue, eOpts ) {
							me.updateFullAddressField();
							me.updateZipCode()
						}
					}
				},
				{
					xtype: 'klstreetcombo',
					name: 'SecondKLStreet_id',
					hidden: true,
					enableKeyEvents : true,
					listeners: {
						change: function(field, newValue, oldValue, eOpts ) {
							me.updateFullAddressField();
							me.updateZipCode();
						},
						keyup: function(c, e, o){
							me.checkCrossRoadsFields(true, e);
						},
						blur: function(){
							me.checkCrossRoadsFields();
						}
					}
				},
				{
				xtype: 'container',
				layout: {
					type: 'hbox',
					align: 'stretch'
				},
				defaults: {
					labelWidth: 60,
					padding: '0 0 5 10',
					listeners: {
						change: function(field, newValue, oldValue, eOpts ) {
							me.updateFullAddressField();
						}
					}
				},
				items: [{
					xtype: 'textfield',
					name: 'House',
					flex: 1,
					labelWidth: 120,
					padding: '0 0 5 0',
					fieldLabel: 'Дом',
					enableKeyEvents : true,
					listeners: {
						change: function(field,nV,oV,eOpts) {
							me.updateZipCode();
						},
						keyup: function(c, e, o){
							me.checkCrossRoadsFields(true, e);
						}
					}
				},{	
					xtype: 'textfield',
					name: 'Corpus',
					flex: 1,
					fieldLabel: 'Корпус',
					hidden: this.unformalized
				},{
					xtype: 'textfield',
					name: 'Address_Zip',
					flex: 1,
					fieldLabel: 'Индекс',
					regex: new RegExp('^[0-9]{1,10}$'),
					msgTarget: 'under',
					hidden: this.unformalized
				}]
			},{
				xtype: 'textarea',
				minHeight: 10,
				name: 'full_address',
				allowBlank: true,
				fieldLabel: 'Полный адрес',
				readOnly: true
			},{
				xtype: 'swdatefield',
				name: 'Address_begDate',
				fieldLabel: 'Дата',
				hidden: this.unformalized
			}]
		});
		
		
		Ext.applyIf(me,{
			items:[
				me.AddressForm
			],
			buttons: [{
				xtype: 'button',
				text: 'Сохранить',
				handler: function(){
					if (!me.AddressForm.isValid()) {
						Ext.Msg.alert('Ошибка','Заполнены не все обязательные поля');
						return false;
					}

					if (typeof me.callback == 'function') {
						me.callback(me.AddressForm.getForm().getValues());
					}
					me.close();

				}
			},'-',{
				xtype: 'button',
				text: 'Отменить',
				handler: function(){
					me.close();
				}
			}]
		})		
		
		me.callParent(arguments);
	},
	setFieldValuesWhenLoading: function(fields){
		var me = this;
		if(me.fields == undefined || !me.fields.KLRegion_id) return;
		var fields = me.fields;
		var form = me.AddressForm.getForm();

		var loadMask = new Ext.LoadMask(
			this,
			{msg: "Подождите, идет загрузка..."}
		);
		loadMask.show();
		me.setFields = true;
		var DefaultCountry_id = 643;
		if (getRegionNick() == 'kz') {
			DefaultCountry_id = 398;
		}
		
		var Country_id = form.findField('Country_id');
		var region = form.findField('KLRegion_id');
		var subrgn = form.findField('KLSubRGN_id');
		var city = form.findField('KLCity_id');
		var town = form.findField('KLTown_id');
		var street = form.findField('KLStreet_id');
		var secondStreet = form.findField('SecondKLStreet_id');
		var house = form.findField('House');
		var corpus = form.findField('Corpus');
		var full_address = form.findField('full_address');
		var flat = form.findField('Flat');

		region.getStore().load({
			params:{
				country_id: DefaultCountry_id
			},
			callback: function(){
				if(fields.KLRegion_id){				
					subrgn.getStore().load({
						params:{
							region_id: fields.KLRegion_id
						},
						callback: function(){
							Country_id.setValue(DefaultCountry_id);
							region.setValue(fields.KLRegion_id);
								if(fields.KLSubRGN_id) subrgn.setValue(fields.KLSubRGN_id);
								city.getStore().load({
									params:{
										subregion_id: (fields.KLSubRGN_id) ? fields.KLSubRGN_id : fields.KLRegion_id
									},
									callback: function(){
										if(fields.KLCity_id) {
											city.setValue(fields.KLCity_id);
											town.getStore().load({
												params:{
													city_id: fields.KLCity_id
												},
												callback: function(){
													if(fields.KLTown_id) town.setValue(fields.KLTown_id);
													street.getStore().load({
														params:{
															town_id: (fields.KLTown_id) ? fields.KLTown_id : fields.KLCity_id
														},
														callback: function(recs){
															if(fields.KLStreet_id) {
																street.setValue(fields.KLStreet_id);
																house.setValue(fields.House);
																corpus.setValue(fields.Corpus);
																//flat.setValue(fields.Flat);
															}
															me.updateFullAddressField();
															me.setFields = false;
															secondStreet.getStore().loadData(recs);
															loadMask.hide();
														}
													});
												}
											});
										}else if(fields.KLTown_id){
											town.getStore().load({
												params:{
													city_id: fields.KLSubRGN_id
												},
												callback: function(){
													if(fields.KLTown_id) town.setValue(fields.KLTown_id);
													street.getStore().load({
														params:{
															town_id: fields.KLTown_id
														},
														callback: function(recs){
															if(fields.KLStreet_id) {
																street.setValue(fields.KLStreet_id);
																house.setValue(fields.House);
																corpus.setValue(fields.Corpus);
																//flat.setValue(fields.Flat);
															}
															me.updateFullAddressField();
															me.setFields = false;
															secondStreet.getStore().loadData(recs);
															loadMask.hide();
														}
													});
												}
											});
										}else{
											me.updateFullAddressField();
											me.setFields = false;
											loadMask.hide();
										}
									}
								});
						}
					});
				}else{
					me.updateFullAddressField();
					me.setFields = false;
					loadMask.hide();
				}
			}
		});
	},
	show: function() {
		this.callParent(arguments);
		this.setFieldValuesWhenLoading(this.fields);
	},

	// функция отображения и фокусов полей для перекрестков
	// changeFocus - ставить фокус или нет
	checkCrossRoadsFields: function(changeFocus, e) {

		if(e && (e.getCharCode() == e.SHIFT)){return false;}

		var me = this,
			baseForm = me.AddressForm.getForm(),
			cmpCallCard_Dom = baseForm.findField('House'),
			secondStreetCombo = baseForm.findField('SecondKLStreet_id'),
			CmpCallCard_Korp = baseForm.findField('Corpus'),
			CmpCallCard_Kvar = baseForm.findField('Flat'),
			Address_Zip = baseForm.findField('Address_Zip'),
			crossRoadsMode = ((cmpCallCard_Dom.getValue() == '/' && !secondStreetCombo.isVisible()) || (secondStreetCombo.getValue() && secondStreetCombo.isVisible()));

		//начали вводить улицу - слэш удалили
		if(secondStreetCombo.getValue()) cmpCallCard_Dom.reset();

		//проверка на существующий режим
		if((crossRoadsMode && secondStreetCombo.isVisible()) || (!crossRoadsMode && !secondStreetCombo.isVisible())) return;

		secondStreetCombo.setVisible(crossRoadsMode);
		cmpCallCard_Dom.setVisible(!crossRoadsMode);

		CmpCallCard_Korp.setVisible(!crossRoadsMode);
		CmpCallCard_Kvar.setVisible(!crossRoadsMode);
		Address_Zip.setVisible(!crossRoadsMode);

		if(changeFocus){
			if(crossRoadsMode){
				secondStreetCombo.focus();
				cmpCallCard_Dom.reset();
				CmpCallCard_Korp.reset();
				CmpCallCard_Kvar.reset();
				Address_Zip.reset();
			}
			else{
				cmpCallCard_Dom.focus();
				if(secondStreetCombo.getPicker() && secondStreetCombo.getPicker().isVisible()){
					secondStreetCombo.collapse();
				}
			}
		}
	},
});