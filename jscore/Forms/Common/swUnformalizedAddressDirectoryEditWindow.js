/**
* swUnformalizedAddressDirectoryEditWindow - окно редактирования карты вызова (краткий вариант для операторов СМП)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author		Miyusov Alexandr
* @version      29 марта 2013
*/
/*NO PARSE JSON*/

sw.Promed.swUnformalizedAddressDirectoryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUnformalizedAddressDirectoryEditWindow',
	
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	id: 'swUnformalizedAddressDirectoryEditWindow',

	draggable: true,
	
	googleMapApiLoaded: false,
	reloadAllFields: function(data) {
		var frm = this.FormPanel.getForm(),
			rc = null;
		frm.findField('KLAreaStat_idEdit').getStore().each(function(r) {
			if( r.get('KLSubRGN_id') > 0 ) {
				if( data.KLSubRegion_id > 0 && data.KLSubRegion_id == r.get('KLSubRGN_id') ) {
					rc = r;
				}
			}
			else if( r.get('KLCity_id') > 0 ) {
				if( data.KLCity_id > 0 && data.KLCity_id == r.get('KLCity_id') ) {
					rc = r;
				}
			}
		});
		if( rc != null ) {
			frm.findField('KLAreaStat_idEdit').setValue(rc.get('KLAreaStat_id'));
			frm.findField('KLAreaStat_idEdit').fireEvent('beforeselect', frm.findField('KLAreaStat_idEdit'), rc);
		}
		frm.findField('KLTown_id').getStore().load({
			params: {city_id: data.KLSubRegion_id > 0 ? data.KLSubRegion_id : data.KLCity_id},
			callback: function() {
				this.each(function(r) {
					if( data.KLTown_id && data.KLTown_id == r.get('Town_id') ) {
						frm.findField('KLTown_id').setValue(r.get('Town_id'));
						frm.findField('KLTown_id').fireEvent('beforeselect', frm.findField('KLTown_id'), r);
					}
				});
			}
		});
	},
	
	initComponent: function() {
		var parentObject = this;
		var opts = getGlobalOptions();
		var mapPanelItems = [];		
		console.log(opts.region);
		switch( opts.region.number ) {
			
			// Псков
//			case 60:
////				var mapPanelItems = [{
////					xtype: "component",
////					autoEl: {
////						tag: 'iframe',
////						src: 'http://glonass.mis.pskov.ru'
////					},
////					style: 'width: 1000px; height: 500px;'
////				}];
//			break;
			
			// Пермь и Уфа? 
			case 59:
			case 63:
			case 60:
			case 2:
				//this.loadGoogleMapApi();
				//if ( this.googleMapApiLoaded ) {
				var parentObj = this;
				var mapPanelItems = [
					{
						xtype: 'gmappanel',
						id: 'UnformalizedAddressDirectory_Gmap',
						name:'UnformalizedAddressDirectory_Gmap',
						gmapType: 'map'  // map, panorama
						, fillLatLng: true
						, height: 450
						, width: 450
						,addMarkByClick: true
						,mapOptions: {
							zoom: 11,
							scaleControl: true,
							panControl: false,
							zoomControl: true,
							mapTypeControl: false,
							rotateControl: false,
							streetViewControl: false,
							overviewMapControl: true
						}
						,setCenter: {
							geoCodeAddr: opts.region.name+lang['rossiya'],
							marker: {title: opts.region.name+lang['rossiya']}
						}
						,markers: []
						,clickCallback: function(evt) {
//								console.log(this);
//								console.log(evt);
							this.geocoder.geocode({'location': evt.latLng}, function(results, status){
								var	streetAddressFound = false;
								console.log(results);
								if (jQuery.isArray(results)) {
									for (var i=0; (i<results.length)&&(!streetAddressFound); i++) {
										if (jQuery.isArray(results[i].types)) {
											for (var k=0; k<results.length; k++) {
												if (results[i].types[k] == 'street_address') {
													streetAddressFound = true;
												}
											}
										}
									}
									i--;
									var params = {};
									if (streetAddressFound) {
										if (jQuery.isArray(results[i].address_components)) {
											console.log({street:results[i].address_components});
											for (var j=0;j<results[i].address_components.length; j++) {
												if (jQuery.isArray(results[i].address_components[j].types)) {
													for (var l=0;l<results[i].address_components[j].types.length;l++) {
														type = results[i].address_components[j].types[l];
														switch(type){
															case 'street_number':
																params['street_number'] = results[i].address_components[j].long_name
																break;
															case 'route':
																params['route'] = results[i].address_components[j].long_name
																break;
															case 'administrative_area_level_1':
																params['administrative_area_level_1'] = results[i].address_components[j].long_name
																break;
															case 'administrative_area_level_2':
																params['administrative_area_level_2'] = results[i].address_components[j].long_name
																break;
															case 'country':
																if ((!results[i].address_components[j].short_name)||(results[i].address_components[j].short_name != 'RU')) {
																		sw.swMsg.alert(lang['oshibka'], lang['vyibrannaya_tochka_na_karte_nahoditsya_za_predelami_rf']);
																	return false;
																}
																break;
														}
													}
												}
											}
										}
										log({params:params});
										Ext.Ajax.request({
											params: params,
											callback: function(opt, success, response) {
												var base_form = parentObj.FormPanel.getForm();
												
												if ( success ) {
													var response_obj = Ext.util.JSON.decode(response.responseText);

													if ( !response_obj.success == false ) {
														if (!response_obj.KLStreet_id || !response_obj.KLCity_id) {
															return false;
														}
														var combo = base_form.findField('KLCity_id')
														combo.setValue(response_obj.KLCity_id);
														var store = base_form.findField('KLCity_id').getStore();
														var rec = store.getAt(store.findBy(function(rec) { return rec.get(combo.valueField) == combo.getValue(); }));
														combo.fireEvent('beforeselect', combo, rec);
														base_form.findField('KLStreet_id').setValue(response_obj.KLStreet_id)
														base_form.findField('UnformalizedAddressDirectory_Dom').setValue(params['street_number']);
													}
												}

												
											}.createDelegate(this),
											url: '/?c=CmpCallCard&m=getUnformalizedAddressStreetKladrParams'
										});
									}
								}
							});        
						}
					}
				];
				//}
			break;
			default:
				var mapPanelItems = [
						{
							xtype: 'gmappanel',
							id: 'UnformalizedAddressDirectory_Gmap',
							name:'UnformalizedAddressDirectory_Gmap',
							gmapType: 'map'  // map, panorama
							, fillLatLng: true
							, height: 450
							, width: 450
							,addMarkByClick: true
							,mapOptions: {
								zoom: 11,
								scaleControl: true,
								panControl: false,
								zoomControl: true,
								mapTypeControl: false,
								rotateControl: false,
								streetViewControl: false,
								overviewMapControl: true
							}
							,setCenter: {
								geoCodeAddr: lang['perm_rossiya'],
								marker: {title: lang['perm_rossiya']}
							}
							,markers: []
						}
					];
			break;
		}
		
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			anchor:'-0, 60%',
			border: false,
			frame: true,
			id: 'UnformalizedAddressDirectoryEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'accessType'},
				{name: 'KLRgn_id'},
				{name: 'KLSubRgn_id'},
				{name: 'KLCity_id'},
				{name: 'KLTown_id'},
				{name: 'KLStreet_id'},
				{name: 'UnformalizedAddress_id'},
				{name: 'UnformalizedAddress_Name'}
				
			]),
//			region: 'center',
			url: '/?c=CmpCallCard&m=saveUnformalizedAddress',
			items: [{
				name: 'accessType',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'KLRgn_id',
				value: opts.region.number,
				xtype: 'hidden'
			}, {

				layout: 'column',
				border: false,
				items:[{
					layout: 'anchor',
					border: false,
					width: 600,
					items: [{
						autoHeight: true,
						style: 'padding: 0; padding-top: 0px; margin-bottom: 0px;',
						title: lang['adres'],
						xtype: 'fieldset',
						collapsed: true,
						id: 'UADEW_AddressFieldset',
						collapsible :true,
						items: [{
							border: false,
							layout: 'column',
							style: 'padding: 0px;',
							items: [{
								border: false,
								layout: 'form',
								style: 'padding: 0px',
								items: [{									
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
											base_form.findField('KLSubRgn_id').reset();
											base_form.findField('KLCity_id').reset();
											base_form.findField('KLTown_id').reset();
											base_form.findField('KLStreet_id').reset();

											if( record.get('KLSubRGN_id') != '' ) {
												base_form.findField('KLSubRgn_id').setValue(record.get('KLSubRGN_id'));
												base_form.findField('KLSubRgn_id').getStore().removeAll();
												base_form.findField('KLSubRgn_id').getStore().load({
													params: {region_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('SubRGN_id') == this.getValue(); }.createDelegate(this))));
													}.createDelegate(base_form.findField('KLSubRgn_id'))
												});
											} else if( record.get('KLCity_id') != '' ) {
												base_form.findField('KLCity_id').setValue(record.get('KLCity_id'));
												base_form.findField('KLCity_id').getStore().removeAll();
												base_form.findField('KLCity_id').getStore().load({
													params: {subregion_id: record.get('KLRGN_id')},
													callback: function() {
														this.setValue(this.getValue());
														this.fireEvent('beforeselect', this, this.getStore().getAt(this.getStore().findBy(function(rec) { return rec.get('City_id') == this.getValue(); }.createDelegate(this))));
													}.createDelegate(base_form.findField('KLCity_id'))
												});
											}
											//KLTown_id
											}
										}.createDelegate(this)
									},
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('KLAreaStat_idEdit').clearValue();
										base_form.findField('KLSubRgn_id').enable();
										base_form.findField('KLCity_id').enable();
										base_form.findField('KLTown_id').enable();
										base_form.findField('KLTown_id').reset();
										base_form.findField('KLTown_id').getStore().removeAll();
										base_form.findField('KLStreet_id').enable();
										base_form.findField('KLStreet_id').reset();
										base_form.findField('KLStreet_id').getStore().removeAll();
									}.createDelegate(this),
									width: 180,
									xtype: 'swklareastatcombo'
								}, {
									hiddenName: 'KLSubRgn_id',
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('SubRGN_id') > 0 ) {
												base_form.findField('KLCity_id').reset();
												//base_form.findField('KLCity_id').disable();

												base_form.findField('KLAreaStat_idEdit').getStore().each(function(r) {
													if( r.get('KLSubRGN_id') > 0 ) {
														if( record.get('SubRGN_id') == r.get('KLSubRGN_id') ) {
															base_form.findField('KLAreaStat_idEdit').setValue(r.get('KLAreaStat_id'));
														}
													}
												});
												base_form.findField('KLTown_id').getStore().removeAll();
												base_form.findField('KLTown_id').getStore().load({params: {city_id: record.get('SubRGN_id')}});
												base_form.findField('KLStreet_id').getStore().removeAll();
												base_form.findField('KLStreet_id').getStore().load({params: {town_id: record.get('SubRGN_id'), showSocr: 1}});
											} else {
												base_form.findField('KLCity_id').enable();
											}
										}.createDelegate(this)
									},
									minChars: 0,
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();

										base_form.findField('KLCity_id').clearValue();
										base_form.findField('KLTown_id').clearValue();
										base_form.findField('KLStreet_id').clearValue();
										var PID = 0;

										base_form.findField('KLCity_id').getStore().removeAll();
										base_form.findField('KLCity_id').getStore().load({params: {subregion_id: PID}});

										base_form.findField('KLTown_id').getStore().removeAll();
										base_form.findField('KLTown_id').getStore().load({params: {city_id: PID}});

										base_form.findField('KLStreet_id').getStore().removeAll();
										base_form.findField('KLStreet_id').getStore().load({params: {town_id: PID, showSocr: 1}});
									}.createDelegate(this),
									/*onTrigger2Click: function() {
										if ( this.disabled ) return;

										this.clearValue();
										this.onClearValue();
									},*/
									width: 180,
									xtype: 'swsubrgncombo'
								}, {
									hiddenName: 'KLCity_id',
									listeners: {
										'beforeselect': function(combo, record) {
											combo.setValue(record.get(combo.valueField));
											var base_form = this.FormPanel.getForm();
											if( record.get('City_id') > 0 ) {
												base_form.findField('KLSubRgn_id').reset();
												//base_form.findField('KLSubRgn_id').disable();

												base_form.findField('KLAreaStat_idEdit').getStore().each(function(r) {
													if( r.get('KLCity_id') > 0 ) {
														if( record.get('City_id') == r.get('KLCity_id') ) {
															base_form.findField('KLAreaStat_idEdit').setValue(r.get('KLAreaStat_id'));
														}
													}
												});
												base_form.findField('KLTown_id').getStore().removeAll();
												base_form.findField('KLTown_id').getStore().load({params: {city_id: record.get('City_id')}});
												base_form.findField('KLStreet_id').getStore().removeAll();
												base_form.findField('KLStreet_id').getStore().load({params: {town_id: record.get('City_id'), showSocr: 1}, 
													callback:function(){
														base_form.findField('KLStreet_id').setValue(base_form.findField('KLStreet_id').getValue());
													}
												});
											} else {
												base_form.findField('KLSubRgn_id').enable();
											}
										}.createDelegate(this)
									},
									minChars: 0,
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('PersonSprTerrDop_idEdit').clearValue();
										base_form.findField('KLTown_idEdit').clearValue();
										base_form.findField('KLStreet_idEdit').clearValue();

										var PID = 0;

										if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
											PID = base_form.findField('KLSubRgn_idEdit').getValue();
										}
										else if ( base_form.findField('KLRgn_idEdit').getValue() ) {
											PID = base_form.findField('KLRgn_idEdit').getValue();
										}

										base_form.findField('KLTown_idEdit').getStore().removeAll();
										base_form.findField('KLTown_idEdit').getStore().load({params: {city_id: PID}});

										base_form.findField('KLStreet_idEdit').getStore().removeAll();
										base_form.findField('KLStreet_idEdit').getStore().load({params: {town_id: PID, showSocr: 1}});

										this.refreshFullAddress();
									}.createDelegate(this),
									onTrigger2Click: function() {
										if ( this.disabled ) return;

										this.clearValue();
										this.onClearValue();
									},
									width: 180,
									xtype: 'swcitycombo'
								}, {
									enableKeyEvents: true,
									hiddenName: 'KLTown_id',
									listeners: {
										beforeselect: function(combo, record) {
											combo.setValue(record.get(combo.valueField));	
											var base_form = this.FormPanel.getForm();
											base_form.findField('KLStreet_id').getStore().removeAll();
											base_form.findField('KLStreet_id').getStore().load({
												params: {town_id: combo.getValue(), showSocr: 1}
											});
										}.createDelegate(this),
										keydown: function (inp, e) {
											if ( e.shiftKey == false && e.getKey() == Ext.EventObject.F4 ) {
												e.stopEvent();
												inp.onTrigger2Click();
											}
										}
									},
									minChars: 0,
									onClearValue: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('KLStreet_id').clearValue();
										var PID = 0;

										if ( base_form.findField('KLCity_id').getValue()  ) {
											PID = base_form.findField('KLCity_id').getValue();
										}
										else if ( base_form.findField('KLSubRgn_id').getValue() ) {
											PID = base_form.findField('KLSubRgn_id').getValue();
										}

										base_form.findField('KLStreet_id').getStore().removeAll();
										base_form.findField('KLStreet_id').getStore().load({
											params: {town_id: PID, showSocr: 1}
										});
									}.createDelegate(this),
									onTrigger2Click: function() {
										var base_form = this.FormPanel.getForm(),
											klcity_id = 0,
											klcity_name = '',
											klsubrgn_id = 0,
											klsubrgn_name = '';

										if ( base_form.findField('KLCity_id').getValue() ) {
											klcity_id = base_form.findField('KLCity_id').getValue();
											klcity_name = base_form.findField('KLCity_id').getRawValue();
										}

										if ( base_form.findField('KLSubRgn_id').getValue() ) {
											klsubrgn_id = base_form.findField('KLSubRgn_id').getValue();
											klsubrgn_name = base_form.findField('KLSubRgn_id').getRawValue();
										}
										getWnd('swKLTownSearchWindow').show({
											onSelect: function(response_data) {
												base_form.findField('KLAreaStat_idEdit').onClearValue();
												this.reloadAllFields(response_data);
											}.createDelegate(this),
											params: {
												KLCity_id: klcity_id,
												KLSubRegion_id: klsubrgn_id,
												KLCity_Name: klcity_name,
												KLSubRegion_Name: klsubrgn_name
											}
										});
									}.createDelegate(this),
									width: 250,
									xtype: 'swtowncombo'
								},	{
									xtype: 'swstreetcombo',
									fieldLabel: lang['ulitsa'],
									hiddenName: 'KLStreet_id',
									width: 250,
									editable: true
								}, {
									disabledClass: 'field-disabled',
									fieldLabel: lang['dom'],
									name: 'UnformalizedAddressDirectory_Dom',
									width: 100,
									xtype: 'textfield'
								}, {
									name: 'UnformalizedAddressDirectory_id',
									xtype: 'hidden'
								}]
							},{
								border: false,
								id: 'UAD_GmapShowButton',
								layout: 'form',
								hidden: false,
								style: 'padding-left: 10px;',
								items: [{
									handler: function() {
										this.showOnMap();
									}.createDelegate(this),
									iconCls: 'search16',
									text: lang['ustanovit_tochku_na_karte'],
									xtype: 'button'
								}]
							}]
						}]
					},{
						autoHeight: true,
						style: 'padding: 0; padding-top: 0px; margin-bottom: 5px;',
						title: lang['neformalizovannyiy_adres'],
						xtype: 'fieldset',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								style: 'padding: 0px;',
								items: [{
									disabledClass: 'field-disabled',
									fieldLabel: lang['nazvanie'],
									allowBlank: false,
									name: 'UnformalizedAddressDirectory_Name',
									width: 200,
									toUpperCase: true,
									xtype: 'textfieldpmw'
								},{
									disabledClass: 'field-disabled',
									fieldLabel: lang['dolgota'],
									disabled:true,
									name: 'UnformalizedAddressDirectory_lng',
									id:'UnformalizedAddressDirectory_lng',
									width: 150,
									xtype: 'textfield'
								},{
									disabledClass: 'field-disabled',
									disabled:true,
									fieldLabel: lang['shirota'],
									name: 'UnformalizedAddressDirectory_lat',
									id:'UnformalizedAddressDirectory_lat',
									width: 150,
									xtype: 'textfield'
								}]
							},{
								border: false,
								layout: 'form',
								style: 'padding-left: 10px;',
								items: [{
									handler: function() {
										this.saveUnformalizedAddress();
									}.createDelegate(this),
									iconCls: 'save16',
									text: lang['sohranit'],
									xtype: 'button'
								}]
							}]
						}]
					}
				]
				}, {
					border: true,
					autoHeight: true,
					style: 'padding: 0; padding-top: 5px; margin-bottom: 5px; margin-right: 5px;margin-left: 5px;',
					//height: 400,
					xtype: 'fieldset',					
					id: 'UnformalizedAddressDirectory_GmapPanelFieldset',
					items: mapPanelItems
				}]
			}]
		});
		var parentObject = this;
		this.UnformalizedAddressDirectionPanel = new sw.Promed.ViewFrame(
		{
			title: lang['spravochnik_neformalizovannyih_adresov'],
			id: this.id+'_Grid',
			paging: true,
			anchor:'-0, 40%',
			dataUrl: '/?c=CmpCallCard&m=loadUnformalizedAddressDirectory',
			toolbar: true,
			root: 'data',
			pageSize: 10,
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				
				{name: 'UnformalizedAddressDirectory_id', type: 'int', header: 'ID', key: true},
				{name: 'UnformalizedAddressDirectory_Name', header: lang['nazvanie'], width: 150},
				{name: 'UnformalizedAddressDirectory_lng', header: lang['dolgota'], width: 100},
				{name: 'UnformalizedAddressDirectory_lat', header: lang['shirota'], width: 100},
				{name: 'UnformalizedAddressDirectory_Address', header: lang['adres'], width: 260, id:'autoexpand'},
				{name: 'UnformalizedAddressDirectory_Dom',hidden: true},
				{name: 'KLRgn_id',hidden: true},
				{name: 'KLSubRgn_id',hidden: true},
				{name: 'KLCity_id',hidden: true},
				{name: 'KLTown_id',hidden: true},
				{name: 'KLStreet_id',hidden: true}
			],
			actions:
			[
				{name:'action_add', tooltip: lang['dobavit'],func: this.addAddress.createDelegate(this) },
				{name:'action_edit', text:lang['redaktirovat'], tooltip: lang['redaktirovat'], handler: parentObject.editAddress.createDelegate(parentObject) },
				{name:'action_view', hidden: true },
				{name:'action_refresh', hidden: false},
				{name:'action_delete', handler: parentObject.deleteAddress.createDelegate(parentObject)}
			]
		});
		
		this.UnformalizedAddressDirectionPanel.getGrid().on('rowdblclick', function() {
			parentObject.editAddress.createDelegate(parentObject);
		});		
//		var form = this;
		Ext.apply(this, {
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [{
				layout: 'anchor',
				items:[
					this.FormPanel,
					this.UnformalizedAddressDirectionPanel
				]
			}],
			layout: 'fit'
		});

		sw.Promed.swUnformalizedAddressDirectoryEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UnformalizedAddressDirectory');

			switch ( e.getKey() ) {

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: Ext.emptyFn,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	showOnMap:function () {
		var opts = getGlobalOptions();
		if ( opts.region.number == 59 ) {
			var map = this.findById('UnformalizedAddressDirectory_Gmap');
			map.show();
			var address = this.getCurrentAddress();
			map.setCenter.marker={title: address};
			log(address);
			map.geoCodeLookup(address);
			console.log(map.getCenterLatLng());
			this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue(map.getCenterLatLng().lat);
			this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue(map.getCenterLatLng().lng);
		}
	},	
	getCurrentAddress:function () {
		var opts = getGlobalOptions();
		var address= (this.FormPanel.getForm().findField('KLCity_id').getRawValue() != opts.region.name)?opts.region.name:'';
		address += (this.FormPanel.getForm().findField('KLSubRgn_id').getRawValue()=='')?'':(' '+this.FormPanel.getForm().findField('KLSubRgn_id').getRawValue()+' район') ;
		address += (this.FormPanel.getForm().findField('KLCity_id').getRawValue()=='')?'':(' '+this.FormPanel.getForm().findField('KLCity_id').getRawValue()) ;
		address += (this.FormPanel.getForm().findField('KLTown_id').getRawValue()=='')?'':(', '+this.FormPanel.getForm().findField('KLTown_id').getRawValue()) ;
		address += (this.FormPanel.getForm().findField('KLStreet_id').getRawValue()=='')?'':(', '+this.FormPanel.getForm().findField('KLStreet_id').getRawValue()) ;
		address += (this.FormPanel.getForm().findField('UnformalizedAddressDirectory_Dom').getRawValue()=='')?'':(', '+this.FormPanel.getForm().findField('UnformalizedAddressDirectory_Dom').getRawValue()) ;
		return address;
	},
	saveUnformalizedAddress: function() {
		var base_form = this.FormPanel.getForm();
		var parentObject = this;
		
		if (base_form.findField('UnformalizedAddressDirectory_Name').getValue()=='') {
			sw.swMsg.alert(lang['oshibka'], lang['neformalizovannyiy_adres_doljen_imet_nazvanie']);
			return false;	
		}
		if (base_form.findField('UnformalizedAddressDirectory_lng').getValue()=='' || base_form.findField('UnformalizedAddressDirectory_lat').getValue()=='') {
			sw.swMsg.alert(lang['oshibka'], lang['neformalizovannyiy_adres_doljen_imet_shirotu_i_dolgotu']);
			return false;	
		}
		
		var params = {
			'UnformalizedAddressDirectory_lng':base_form.findField('UnformalizedAddressDirectory_lng').getValue(),
			'UnformalizedAddressDirectory_lat':base_form.findField('UnformalizedAddressDirectory_lat').getValue()
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение неформализованного адреса..."});
		loadMask.show();
		
		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.UnformalizedAddressDirectory_id > 0 ) {
						parentObject.resetAllFields();
						parentObject.UnformalizedAddressDirectionPanel.runAction('action_refresh');
						parentObject.showOnMap();
						base_form.findField('UnformalizedAddressDirectory_id').setValue(0);
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
		
		
	},
	getBrigs: function() {
//		Ext.Ajax.request({
//			params: someparams,
//			callback: function(opt, success, response) {
//				
//			}.createDelegate(this),
//			url: ''
//		});
	},
	
	addAddress: function() {
//		this.reloadAllFields(data);
		this.resetAllFields();
		if (this.FormPanel.getForm().findField('UnformalizedAddressDirectory_id').getValue() != 0) {
			this.showOnMap();
		} else {
			var map = this.findById('UnformalizedAddressDirectory_Gmap');
			this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue(map.getCenterLatLng().lat);
			this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue(map.getCenterLatLng().lng);
		}
	},
	
	editAddress: function() {
		var opts = getGlobalOptions();
		var grid = this.UnformalizedAddressDirectionPanel.getGrid();
		var viewFrame = this.UnformalizedAddressDirectionPanel;
		var base_form = this.FormPanel.getForm();
		
		var uadId = '';
		if ( !grid.getSelectionModel().getSelected() || ! (grid.getSelectionModel().getSelected().get('UnformalizedAddressDirectory_id')) ) {
			return false;
		}
		
		this.resetAllFields();
		
		var parentObject = this;
		
		var record = grid.getSelectionModel().getSelected();
		base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
		base_form.findField('KLSubRgn_id').setValue(record.get('KLSubRgn_id'));
		base_form.findField('KLCity_id').setValue(record.get('KLCity_id'));
		base_form.findField('KLTown_id').setValue(record.get('KLTown_id'));
		
		console.log(record);
		
		if( base_form.findField('KLSubRgn_id').getValue() != null ) {
			base_form.findField('KLSubRgn_id').getStore().load({
				params: {
					'no':true
				},
				callback: function() {
					base_form.findField('KLSubRgn_id').setValue(base_form.findField('KLSubRgn_id').getValue());

				}
			})
		}

		if( base_form.findField('KLCity_id').getValue() != null ) {
			base_form.findField('KLCity_id').getStore().load({
				params: {
					subregion_id: (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : opts.region.number
				},
				callback: function() {
					base_form.findField('KLCity_id').setValue(base_form.findField('KLCity_id').getValue());
				}
			})
		}
		if( base_form.findField('KLTown_id').getValue() != null ) {							
			base_form.findField('KLTown_id').getStore().load({
				params: {
					city_id: (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() :  base_form.findField('KLCity_id').getValue()
				},
				callback: function() {
					base_form.findField('KLTown_id').setValue(base_form.findField('KLTown_id').getValue());
				}
			})
		}
		base_form.findField('KLStreet_id').setValue(record.get('KLStreet_id'));
		
		base_form.findField('KLStreet_id').getStore().load({
			params: {
				town_id: (base_form.findField('KLTown_id').getValue() > 0) ? base_form.findField('KLTown_id').getValue() : (base_form.findField('KLSubRgn_id').getValue() > 0) ? base_form.findField('KLSubRgn_id').getValue() : base_form.findField('KLCity_id').getValue(),
				showSocr: 1
			},
			callback: function() {
				base_form.findField('KLStreet_id').setValue(base_form.findField('KLStreet_id').getValue());
			}
		})
		

		
		
		base_form.findField('UnformalizedAddressDirectory_Dom').setValue( record.get('UnformalizedAddressDirectory_Dom') );
		base_form.findField('UnformalizedAddressDirectory_lat').setValue(record.get('UnformalizedAddressDirectory_lat'));
		base_form.findField('UnformalizedAddressDirectory_lng').setValue(record.get('UnformalizedAddressDirectory_lng'));
		base_form.findField('UnformalizedAddressDirectory_Name').setValue(record.get('UnformalizedAddressDirectory_Name'));
		
		var map = this.findById('UnformalizedAddressDirectory_Gmap');
		map.setCurrentMarkerLatLng(record.get('UnformalizedAddressDirectory_lat'),record.get('UnformalizedAddressDirectory_lng'));
		
//		map.currentMarker.getPosition().lat(record.get('UnformalizedAddressDirectory_lat'));
//		map.currentMarker.getPosition().lng(record.get('UnformalizedAddressDirectory_lng'));
//		console.log(map.currentMarker.getPosition().lat());
//		map.currentMarker.setPosition();
//		map.currentMarker.setPosition({lat:record.get('UnformalizedAddressDirectory_lat'),lng: record.get('UnformalizedAddressDirectory_lng')})
//		this.findById('UnformalizedAddressDirectory_lng').setValue(gmap.currentMarker.getPosition().lng());
		
		
	},
	deleteAddress: function() {
		var grid = this.UnformalizedAddressDirectionPanel.getGrid();
		var viewFrame = this.UnformalizedAddressDirectionPanel;

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UnformalizedAddressDirectory_id') ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_vyizova']);
								}
								else {
									viewFrame.runAction('action_refresh');
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
							}
						},
						params: {
							UnformalizedAddressDirectory_id: record.get('UnformalizedAddressDirectory_id')
						},
						url: '/?c=CmpCallCard&m=deleteUnformalizedAddress'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_neformalizovannyiy_adres'],
			title: lang['vopros']
		});
	
	},
		
	resetAllFields: function() {
		var opts = getGlobalOptions();
		var base_form = this.FormPanel.getForm();	
		var parentObject = this;
		base_form.reset();
		
		base_form.clearInvalid();		
		if ( opts.region.number == 59 ) {
			base_form.findField('KLAreaStat_idEdit').setValue(43); // г.Пермь
		} 

		var idx = base_form.findField('KLAreaStat_idEdit').getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); }),
			record = base_form.findField('KLAreaStat_idEdit').getStore().getAt(idx);
		if( record ) {
			base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), record);
		} else {
			base_form.findField('KLAreaStat_idEdit').getStore().load({
				callback: function() {
					base_form.findField('KLAreaStat_idEdit').setValue(base_form.findField('KLAreaStat_idEdit').getValue());
					var idx = this.findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); });
					base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), this.getAt(idx));
				}
			});
		}
	},
	
	show: function() {
		sw.Promed.swUnformalizedAddressDirectoryEditWindow.superclass.show.apply(this, arguments);
		this.doLayout();
		this.restore();
		this.center();
		this.maximize();
		var base_form = this.FormPanel.getForm();	
		var parentObject = this;
		base_form.reset();
		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		var opts = getGlobalOptions();
		
		var gmap = this.findById('UnformalizedAddressDirectory_Gmap');
		this.findById('UnformalizedAddressDirectory_Gmap').latField = this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat');
		this.findById('UnformalizedAddressDirectory_Gmap').lngField = this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng');
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		
		this.setTitle(lang['redaktirovanie_neformalizovannogo_spravochnika_adresov_smp']);
		loadMask.hide();
		base_form.clearInvalid();

		// Псков				 
		if ( opts.region.number == 59 ) {
			base_form.findField('KLAreaStat_idEdit').setValue(43); // г.Пермь
		} 
		
		base_form.findField('UnformalizedAddressDirectory_id').setValue(0);
		this.FormPanel.findById('UADEW_AddressFieldset').collapse(false);
		
		var idx = base_form.findField('KLAreaStat_idEdit').getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); }),
			record = base_form.findField('KLAreaStat_idEdit').getStore().getAt(idx);
		if( record ) {
			base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), record);
		} else {
			base_form.findField('KLAreaStat_idEdit').getStore().load({
				callback: function() {
					base_form.findField('KLAreaStat_idEdit').setValue(base_form.findField('KLAreaStat_idEdit').getValue());
					var idx = this.findBy(function(rec) { return rec.get('KLAreaStat_id') == base_form.findField('KLAreaStat_idEdit').getValue(); });
					base_form.findField('KLAreaStat_idEdit').fireEvent('beforeselect', base_form.findField('KLAreaStat_idEdit'), this.getAt(idx));
				}
			});
		}
		
		var opts=getGlobalOptions();
		//Пока только для Перми - координаты статичные
		switch( opts.region.number ) {
				// Псков
				case 63:
					//		53.196571,50.103383
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue('53.196571');
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue('50.103383');
				break;
				case 2:
					//54.739045,55.95805
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue('54.739045');
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue('55.95805');
				break;
				case 59:
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue('58.0138889');
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue('56.2488889');
				break;
				default:
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lat').setValue('58.0138889');
					this.FormPanel.getForm().findField('UnformalizedAddressDirectory_lng').setValue('56.2488889');
				break;
			}

		
		
		this.UnformalizedAddressDirectionPanel.runAction('action_refresh');

	},
	width: 750
});
