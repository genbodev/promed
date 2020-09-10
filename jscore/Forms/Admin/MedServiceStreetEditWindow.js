/**
* swMedServiceStreetEditWindow - окно редактирования адреса на территории.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff, c'copy'пи$жено с AddressEditWindow
* @version      05.07.2009
*/

sw.Promed.swMedServiceStreetEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'fit',
	width       : 450,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	//closeAction : 'close',
	closable      : false,
	plain       : true,
	returnFunc: function(owner, kid) {},
	title: langs('Территория обслуживания'),
	id: 'swMedServiceStreetEditWindow',
	getNameValue: function(combo, fieldName)
	{
		var ret = '';
		combo.getStore().each(function(r) {
			if ( r.data[r.store.key] == combo.getValue() )
				if ( r.data[fieldName] )
					ret = r.data[fieldName];
		});
		return ret;
	},
	setSocr: function(combo, socrCombo) {
		var socr_id = '';
		combo.getStore().each(function(r) {
				if ( r.data[r.store.key] == combo.getValue())
				{
					socr_id = r.data['Socr_id'];
					return false;
				}
			}
		);
		socrCombo.setValue(socr_id);
	},
	findInAreaStat: function(fieldName, value) {
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		var ret = false;
		form.findField('KLAreaStat_Combo').getStore().each(function(r){
			if ( r.data[fieldName] == value )
			{
				ret = true;
				return ret;
			}
		});
		return ret;
	},
	getFromAreaStatById: function(fieldName, value) {
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		var ret = '';
		form.findField('KLAreaStat_Combo').getStore().each(function(r){
			if ( r.data['KLAreaStat_id'] == value )
			{
				ret = r.data[fieldName];
				return false;
			}
		});
		return ret;
	},
	getFromAreaStat: function(fieldName, value) {
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		var ret = '';
		form.findField('KLAreaStat_Combo').getStore().each(function(r){
			if ( r.data[fieldName] == value )
			{
				ret = r.data['KLAreaStat_id'];
				return false;
			}
		});
		return ret;
	},
	onKLAreaStatChange: function(value) {
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		var country = this.getFromAreaStatById('KLCountry_id', value);
		var region = this.getFromAreaStatById('KLRGN_id', value);
		var subregion = this.getFromAreaStatById('KLSubRGN_id', value);
		var city = this.getFromAreaStatById('KLCity_id', value);
		var town = this.getFromAreaStatById('KLTown_id', value);
		var aw = this;
		form.findField('Country_idCombo').clearValue();
		form.findField('Region_idCombo').clearValue();
		form.findField('SubRegion_idCombo').clearValue();
		form.findField('City_idCombo').clearValue();
		form.findField('Town_idCombo').clearValue();
		form.findField('Street_idCombo').clearValue();
		form.findField('Region_idCombo').getStore().removeAll();
		form.findField('SubRegion_idCombo').getStore().removeAll();
		form.findField('City_idCombo').getStore().removeAll();
		form.findField('Town_idCombo').getStore().removeAll();
		form.findField('Street_idCombo').getStore().removeAll();
		
		if(value != '') {
			form.findField('Country_idCombo').disable();
			form.findField('Region_idCombo').disable();
			form.findField('SubRegion_idCombo').disable();
			form.findField('City_idCombo').disable();
			form.findField('Town_idCombo').disable();
		} else {
			form.findField('Country_idCombo').enable();
			form.findField('Region_idCombo').enable();
			form.findField('SubRegion_idCombo').enable();
			form.findField('City_idCombo').enable();
			form.findField('Town_idCombo').enable();
		}
		
		form.findField('Country_idCombo').setValue(country);
		form.findField('Region_idCombo').store.load({params: {country_id: country}, callback: function() {if (region) form.findField('Region_idCombo').setValue(region);aw.setSocr(form.findField('Region_idCombo'), form.findField('KLRGN_Socr'));}});
		form.findField('SubRegion_idCombo').store.load({params: {region_id: region}, callback: function() {if (subregion) form.findField('SubRegion_idCombo').setValue(subregion);aw.setSocr(form.findField('SubRegion_idCombo'), form.findField('KLSubRGN_Socr'));}});
		var PID = 0;
		if ( subregion != '' )
			PID = subregion
		else
			if ( region != '' )
				PID = region;
		form.findField('City_idCombo').store.load({params: {subregion_id: PID}, callback: function() {if (city) form.findField('City_idCombo').setValue(city);aw.setSocr(form.findField('City_idCombo'), form.findField('KLCity_Socr'));}});
		PID = 0;
		if ( city != '' )
			PID = city;
		else
			if ( subregion != '' )
				PID = subregion
			else
				if ( region != '' )
					PID = region;
		form.findField('Town_idCombo').store.load({params: {city_id: PID}, callback: function() {if (town) form.findField('Town_idCombo').setValue(town);aw.setSocr(form.findField('Town_idCombo'), form.findField('KLTown_Socr'));}});
		PID = 0;
		if ( town != '' )
			PID = town;
		else
			if ( city != '' )
				PID = city;
			else
				if ( subregion != '' )
					PID = subregion
				else
					if ( region != '' )
						PID = region;
		if (PID>0)
		{
			form.findField('Street_idCombo').store.load({params: {town_id: PID}});
		}
		else 
		{
			form.findField('Street_idCombo').store.removeAll();
		}
		if ( town == '' )
		{
			form.findField('Town_idCombo').enable();
			if ( '' == city )
			{
				form.findField('City_idCombo').enable();
					if ( subregion == '' )
					{
						form.findField('SubRegion_idCombo').enable();
						if ( region == '' )
							form.findField('Region_idCombo').enable();
					}
			}
		}
	},
	listeners: {
		'hide': function() {
			//this.onWinClose();
			this.returnFunc(this.owner, -1);
		}
	},
	show: function() {
		sw.Promed.swMedServiceStreetEditWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swMedServiceStreetEditWindow'), {msg: "Подождите, идет загрузка..."});
		loadMask.show();
		
		if ( arguments[0] )
		{
			/*
			if ( arguments[0].fields )
				fields = arguments[0].fields;
			*/
			if (arguments[0].action)
				this.action = arguments[0].action;
			if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
			if ( arguments[0].MedService_id )
				this.MedService_id = arguments[0].MedService_id;
			else 
				this.MedService_id = null;
			if ( arguments[0].MedServiceStreet_id )
				this.MedServiceStreet_id = arguments[0].MedServiceStreet_id;
			else 
				this.MedServiceStreet_id = null;
		}
		var forms = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		
		switch (this.action)
		{
			case 'add':
				this.setTitle(langs('Территория обслуживания: Добавление'));
				break;
			case 'edit':
				this.setTitle(langs('Территория обслуживания: Редактирование'));
				break;
			case 'view':
				this.setTitle(langs('Территория обслуживания: Просмотр'));
				break;
		}
		var me = this;
		forms.reset();
		forms.findField('KLAreaStat_Combo').clearValue();
		forms.findField('KLAreaStat_Combo').getStore().clearFilter();
		//forms.findField('LpuRegion_Name').setValue(this.LpuRegion_Name);
		forms.findField('MedService_id').setValue(this.MedService_id);
		forms.findField('MedServiceStreet_id').setValue(this.MedServiceStreet_id);
		var aw = this;
		Ext.getCmp('gridHouse').ViewGridStore.removeAll();
		if (this.action=='view')
		{
			forms.findField('KLAreaStat_Combo').disable();
			forms.findField('Country_idCombo').disable();
			forms.findField('Region_idCombo').disable();
			forms.findField('SubRegion_idCombo').disable();
			forms.findField('City_idCombo').disable();
			forms.findField('Town_idCombo').disable();
			forms.findField('Street_idCombo').disable();			
			forms.findField('MedServiceStreet_id').disable();
			forms.findField('MedServiceStreet_HouseSet').disable();
			forms.findField('MedServiceStreet_isAll').disable();
			this.ownerCt.findById('gridHouse').disable();
			Ext.getCmp('swMedServiceStreetEditWindow').buttons[0].disable();
		}
		else
		{
			forms.findField('KLAreaStat_Combo').enable();
			forms.findField('Country_idCombo').enable();
			forms.findField('Region_idCombo').enable();
			forms.findField('SubRegion_idCombo').enable();
			forms.findField('City_idCombo').enable();
			forms.findField('Town_idCombo').enable();
			forms.findField('Street_idCombo').enable();
			forms.findField('MedServiceStreet_id').enable();
			forms.findField('MedServiceStreet_HouseSet').enable();
			Ext.getCmp('swMedServiceStreetEditWindow').buttons[0].enable();
		}
		if (this.action=='add')	
		{
			forms.findField('Street_idCombo').enable();
			

			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(langs('Ошибка'), langs('При получении значения признака "МО имеет приписное население" возникли ошибки'));
				},
				params: {
					param: 'PasportMO_IsAssignNasel',
					Lpu_id: getGlobalOptions().lpu_id
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof response_obj == 'object' && response_obj.length > 0 && getGlobalOptions().region.nick == 'perm') {						
						forms.findField('KLAreaStat_idEdit').setValue(43);
						me.onKLAreaStatChange(43);
					}
				},
				url: '/?c=LpuPassport&m=getLpuPassport'
			});
			
//			forms.findField('Region_idCombo').getStore().load({
//				params: {
//					subregion_id: getGlobalOptions().region.number
//				},
//				callback: function() {
//					forms.findField('Region_idCombo').setValue(opts.region.number);
//				}
//			})
		
//			if ( forms.findField('Region_idCombo').getValue() != null )
//			{
//				value = forms.findField('Region_idCombo').getValue();
//				forms.findField('SubRegion_idCombo').store.load({params: {region_id: value}, callback: function() {if (forms.findField('SubRegion_idCombo').getValue()) forms.findField('SubRegion_idCombo').setValue(forms.findField('SubRegion_idCombo').getValue());aw.setSocr(forms.findField('SubRegion_idCombo'), forms.findField('KLSubRGN_Socr'));}});
//				forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue());aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
//				forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
//				forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
//			}
//
//			if ( forms.findField('SubRegion_idCombo').getValue() != null )
//			{
//				value = forms.findField('SubRegion_idCombo').getValue();
//				forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue());aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
//				forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
//			}
//			if ( forms.findField('City_idCombo').getValue() != null )
//			{
//				value = forms.findField('City_idCombo').getValue();
//				forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
//				forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
//			}
//
//			if ( forms.findField('Town_idCombo').getValue() != null )
//			{
//				value = forms.findField('Town_idCombo').getValue();
//				forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
//			}
//			if ( forms.findField('Town_idCombo').getValue() != null &&  aw.findInAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()) )
//			{
//				forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()));
//				forms.findField('Country_idCombo').disable();
//				forms.findField('Region_idCombo').disable();
//				forms.findField('SubRegion_idCombo').disable();
//				forms.findField('City_idCombo').disable();
//				forms.findField('Town_idCombo').disable();
//				loadMask.hide();
//				return false;
//			}
//
//			if ( forms.findField('City_idCombo').getValue() != null &&  aw.findInAreaStat('KLCity_id',forms.findField('City_idCombo').getValue()) )
//			{
//				forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLCity_id', forms.findField('City_idCombo').getValue()));
//				forms.findField('Country_idCombo').disable();
//				forms.findField('Region_idCombo').disable();
//				forms.findField('SubRegion_idCombo').disable();
//				forms.findField('City_idCombo').disable();
//				loadMask.hide();
//				return false;
//			}
//			if ( forms.findField('SubRegion_idCombo').getValue() != null &&  aw.findInAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()) )
//			{
//				forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()));
//				forms.findField('Country_idCombo').disable();
//				forms.findField('Region_idCombo').disable();
//				forms.findField('SubRegion_idCombo').disable();
//				loadMask.hide();
//				return false;
//			}
//			if ( forms.findField('Region_idCombo').getValue() != null && aw.findInAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()) )
//			{
//				forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()));
//				forms.findField('Country_idCombo').disable();
//				forms.findField('Region_idCombo').disable();
//				loadMask.hide();
//				return false;
//			}			
		}
		
		if (this.action!='add')
		{
			forms.load(
			{
				url: '/?c=LpuStructure&m=GetMedServiceStreet',
				params:
				{
					object: 'MedServiceStreet',
					MedServiceStreet_id: this.MedServiceStreet_id,
					MedService_id: this.MedService_id,
					KLCountry_id: '',
					KLRGN_id: '',
					KLSubRGN_id: '',
					KLCity_id: '',
					KLTown_id: '',
					KLStreet_id: '',
					MedServiceStreet_HouseSet: '',
					MedServiceStreet_isAll: ''
				},
				success: function ()
				{
					if (forms.action!='view')
					{
						forms.findField('KLAreaStat_Combo').focus(true, 100);
					}
					
					if ( forms.findField('Country_idCombo').getValue())
					{
						value = forms.findField('Country_idCombo').getValue();
						forms.findField('Region_idCombo').store.load({params: {country_id: value}, callback: function() {if ( forms.findField('Region_idCombo').getValue()) forms.findField('Region_idCombo').setValue(forms.findField('Region_idCombo').getValue());aw.setSocr(forms.findField('Region_idCombo'), forms.findField('KLRGN_Socr'));}});
						//forms.findField('SubRegion_idCombo').store.load({params: {region_id: value}, callback: function() {if (forms.findField('SubRegion_idCombo').getValue()) forms.findField('SubRegion_idCombo').setValue(forms.findField('SubRegion_idCombo').getValue()); aw.setSocr(forms.findField('SubRegion_idCombo'), forms.findField('KLSubRGN_Socr'));}});
						//forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue()); aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
					}
					
					var ret = forms.findField('MedServiceStreet_HouseSet').value;
					var arrayOfStrings = ret.split(',');
					var gr = Ext.getCmp('gridHouse');
					Ext.getCmp('gridHouse').ViewGridStore.removeAll();
					$.each(arrayOfStrings, function(key, val) {
						var id = swGenTempId( gr.ViewGridStore );						
						if (val[0] == langs('Ч') || val[0] == langs('Н')) {
							var arr = val.match(/\d+/g);
							var record = new Ext.data.Record({
								gID: id,
								gType: val[0],
								gFrom: arr[0],
								gTo: arr[1],
								gValue: ''
							});		
						} else {
							var record = new Ext.data.Record({
								gID: id,
								gType: '',
								gFrom: '',
								gTo: '',
								gValue: val
							});						
						}
						gr.ViewGridStore.add([ record ]);
					});

					if(arguments[1] && arguments[1].response.responseText){
						var MedServiceStreet_isAll = forms.findField('MedServiceStreet_isAll');
						var response_obj = Ext.util.JSON.decode(arguments[1].response.responseText);	
						if(response_obj[0].MedServiceStreet_isAll && response_obj[0].MedServiceStreet_isAll == 'true'){
							MedServiceStreet_isAll.setValue(true);
						}else{
							MedServiceStreet_isAll.setValue(false);
						}
					}
							
					if ( forms.findField('Region_idCombo').getValue() != null )
					{
						value = forms.findField('Region_idCombo').getValue();
						forms.findField('SubRegion_idCombo').store.load({params: {region_id: value}, callback: function() {if (forms.findField('SubRegion_idCombo').getValue()) forms.findField('SubRegion_idCombo').setValue(forms.findField('SubRegion_idCombo').getValue());aw.setSocr(forms.findField('SubRegion_idCombo'), forms.findField('KLSubRGN_Socr'));}});
						forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue());aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
					}
					
					if ( forms.findField('SubRegion_idCombo').getValue() != null )
					{
						value = forms.findField('SubRegion_idCombo').getValue();
						forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue());aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
					}
					if ( forms.findField('City_idCombo').getValue() != null )
			    	{
						value = forms.findField('City_idCombo').getValue();
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue());aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
			    	}

					if ( forms.findField('Town_idCombo').getValue() != null )
					{
						value = forms.findField('Town_idCombo').getValue();
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue());aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
					}
					if ( forms.findField('Town_idCombo').getValue() != null &&  aw.findInAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()) )
					{						
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()));
						//forms.findField('Country_idCombo').disable();
						//forms.findField('Region_idCombo').disable();
						//forms.findField('SubRegion_idCombo').disable();
						//forms.findField('City_idCombo').disable();
						//forms.findField('Town_idCombo').disable();
						loadMask.hide();
						return false;
					}
					
					if ( forms.findField('City_idCombo').getValue() != null &&  aw.findInAreaStat('KLCity_id',forms.findField('City_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLCity_id', forms.findField('City_idCombo').getValue()));
						//forms.findField('Country_idCombo').disable();
						//forms.findField('Region_idCombo').disable();
						//forms.findField('SubRegion_idCombo').disable();
						//forms.findField('City_idCombo').disable();
						loadMask.hide();
						return false;
					}
					if ( forms.findField('SubRegion_idCombo').getValue() != null &&  aw.findInAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()));
						//forms.findField('Country_idCombo').disable();
						//forms.findField('Region_idCombo').disable();
						//forms.findField('SubRegion_idCombo').disable();
						loadMask.hide();
						return false;
					}
					if ( forms.findField('Region_idCombo').getValue() != null && aw.findInAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()));
						//forms.findField('Country_idCombo').disable();
						//forms.findField('Region_idCombo').disable();
						loadMask.hide();
						return false;
					}					
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
				}
			});
			
		}
		else
		{
		forms.findField('KLAreaStat_Combo').focus(true, 100);
		loadMask.hide();
		}

	},
	doSave: function() 
	{
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm(),
			KLTown_id = form.findField('Town_idCombo').getValue(),
			KLStreet_id = form.findField('Street_idCombo').getValue(),
			HouseSet = form.findField('MedServiceStreet_HouseSet').getValue(),
			allTerritory = form.findField('MedServiceStreet_isAll').getValue();
		if (HouseSet == '') {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,				
				icon: Ext.Msg.WARNING,
				msg: langs('Следует указать дома, диапазоны домов или выбрать всю улицу.'),
				title: ERR_INVFIELDS_TIT
			});
			return false;			
		}

		if (!allTerritory && (KLStreet_id == "") && (KLTown_id == "")) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: langs("Если флаг «Вся указанная территория» не установлен, то обязательно укажите населенный пункт и/или улицу. При необходимости добавьте номера или диапазоны домов"),
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		
		if (KLTown_id != "" && (form.findField('Street_idCombo').getStore().getCount() > 1) && (KLStreet_id == "") && !allTerritory){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: langs("Если не установлен флаг «Все указанная территория», то обязательно укажите улицу (при наличии улиц) и добавьте номера или диапазоны домов."),
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		/*if ((!KLTown_id) && (!KLStreet_id))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findField('Town_idCombo').focus(false)
				},
				icon: Ext.Msg.WARNING,
				msg: langs('Следует указать как минимум населенный пункт или улицу.'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/
		return true;
	},
	submit: function()
	{
		var form = this.items.items[this.items.findIndex('id', 'MedServicestreet_edit_window')].getForm();
		var loadMask = new Ext.LoadMask(Ext.get('swMedServiceStreetEditWindow'), {msg: "Подождите, идет сохранение..."});
		if (this.doSave())
		{
			loadMask.show();
			form.submit(
				{
					params: 
					{
						//MedServiceStreet_id: form.findField('MedServiceStreet_id').getValue(),
						//MedService_id: form.findField('MedService_id').getValue(),
						KLCountry_id: form.findField('Country_idCombo').getValue(),
						KLRGN_id: form.findField('Region_idCombo').getValue(),
						KLSubRGN_id: form.findField('SubRegion_idCombo').getValue(),
						KLCity_id: form.findField('City_idCombo').getValue(),
						KLTown_id: form.findField('Town_idCombo').getValue(),
						KLStreet_id: form.findField('Street_idCombo').getValue()
						//MedServiceStreet_HouseSet: form.findField('MedServiceStreet_HouseSet')
					},
					failure: function(result_form, action) 
					{
						if (action.result)
						{
							if (action.result.Error_Code)
							{
								Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
							}
							else
							{
								//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
							}
						}
						loadMask.hide();
					},
					success: function(result_form, action)
					{
						loadMask.hide();
						if (action.result)
						{
							if (action.result.MedServiceStreet_id)
							{
								Ext.getCmp('swMedServiceStreetEditWindow').hide();
								Ext.getCmp('swMedServiceStreetEditWindow').returnFunc(Ext.getCmp('swMedServiceStreetEditWindow').owner, action.result.MedServiceStreet_id);
							}
							else
								Ext.Msg.alert(langs('Ошибка #100004'), langs('При сохранении произошла ошибка'));
						}
						else
							Ext.Msg.alert(langs('Ошибка #100005'), langs('При сохранении произошла ошибка'));
					}
				});
		}
	},
	initComponent: function() {
		var store =  new Ext.data.Store();		
    	Ext.apply(this, {
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: 1214,
					id: 'lrsOk',
					iconCls: 'ok16',
					handler: function()
					{
						this.ownerCt.submit();
					}
				},
				{
					text:'-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCANCEL,
					tabIndex: 1215,
					iconCls: 'cancel16',
					handler: function()
					{
						this.ownerCt.hide();
						this.ownerCt.destroy();
						window[this.ownerCt.objectName] = null;
						delete sw.Promed[this.ownerCt.objectName];
						this.ownerCt.returnFunc(this.ownerCt.owner, -1);
					},
					onTabAction: function()
					{
						this.findById('KLAreaStat_Combo').focus();
					},
					onShiftTabAction: function()
					{
						Ext.getCmp('lrsOk').focus();
					}
				}
			],
 			items: [
				new Ext.form.FormPanel({
					frame: true,
            		autoHeight: true,
            		labelAlign: 'right',
					id: 'MedServicestreet_edit_window',
					labelWidth: 95,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [
					{
						name: 'MedServiceStreet_id',
						id: 'lrsMedServiceStreet_id',
						xtype: 'hidden'
					},
					{
						name: 'MedService_id',
						id: 'lrsMedService_id',
						xtype: 'hidden'
					},
					
							{
								xtype: 'fieldset',
								autoHeight: true,
								title: langs('Справочник территорий'),
       							style: 'padding: 0; padding-top: 5px; margin-bottom: 5px',
								items: [
									{
 										xtype: 'swklareastatcombo',
										tabIndex: 1199,
										hiddenName: 'KLAreaStat_idEdit',
										id: 'KLAreaStat_Combo',
										width: 300,
										enableKeyEvents: true,
										listeners: 
										{
											'beforeselect': function(combo, record) 
											{
												var value = record.data[combo.valueField];												
												this.ownerCt.ownerCt.ownerCt.onKLAreaStatChange(value);
											}
											/*'keydown': function (inp, e) {
		                                       	if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
                                		       	{
    												e.stopEvent();
												}
											}*/
										},
										onClearValue: function() {
											this.clearValue();
											this.ownerCt.ownerCt.getForm().findField('Country_idCombo').enable();
											this.ownerCt.ownerCt.getForm().findField('Region_idCombo').enable();
											this.ownerCt.ownerCt.getForm().findField('SubRegion_idCombo').enable();
											this.ownerCt.ownerCt.getForm().findField('City_idCombo').enable();
											this.ownerCt.ownerCt.getForm().findField('Town_idCombo').enable();
										}
 									}
								]
							},
							{
								xtype: 'swklcountrycombo',
								hiddenName: 'KLCountry_id',
								name: 'KLCountry_id',
								tabIndex: 1200,
								id: 'Country_idCombo',
								width: 300,
								listeners: {
									'beforeselect': function(combo, record) {
										var value = record.data[combo.valueField];
										this.ownerCt.findById('Region_idCombo').store.removeAll();
                            			this.ownerCt.findById('Region_idCombo').store.load({params: {country_id: value}});
										this.ownerCt.findById('SubRegion_idCombo').store.removeAll();
                            			this.ownerCt.findById('SubRegion_idCombo').store.load({params: {region_id: 0}});
										this.ownerCt.findById('City_idCombo').store.removeAll();
										this.ownerCt.findById('City_idCombo').store.load({params: {subregion_id: 0}});
										this.ownerCt.findById('Town_idCombo').store.removeAll();
										this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: 0}});
										this.ownerCt.findById('Street_idCombo').store.removeAll();
										this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: 0}});
										this.ownerCt.findById('Region_idCombo').clearValue();
                            			this.ownerCt.findById('SubRegion_idCombo').clearValue();
										this.ownerCt.findById('City_idCombo').clearValue();
										this.ownerCt.findById('Town_idCombo').clearValue();
										this.ownerCt.findById('Street_idCombo').clearValue();
										this.ownerCt.ownerCt.findById('KLRGN_Socr').clearValue();
                            			this.ownerCt.ownerCt.findById('KLSubRGN_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
										combo.setValue(value);
									}
								},
								onClearValue: function() {
//										if ( this.value == '' )
//											return;
										this.clearValue();
                            			this.ownerCt.findById('Region_idCombo').store.removeAll();
                            			this.ownerCt.findById('SubRegion_idCombo').store.removeAll();
										this.ownerCt.findById('City_idCombo').store.removeAll();
										this.ownerCt.findById('Town_idCombo').store.removeAll();
										this.ownerCt.findById('Street_idCombo').store.removeAll();
										this.ownerCt.findById('Region_idCombo').clearValue();
                            			this.ownerCt.findById('SubRegion_idCombo').clearValue();
										this.ownerCt.findById('City_idCombo').clearValue();
										this.ownerCt.findById('Town_idCombo').clearValue();
										this.ownerCt.findById('Street_idCombo').clearValue();
										this.ownerCt.ownerCt.findById('KLRGN_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLSubRGN_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
										this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
								}
							},
                        	{
                        		layout: 'column',
								items:[
								{
									layout: 'form',
									items: [
									{
										xtype: 'swregioncombo',
										tabIndex: 1201,
										minChars: 0,
										queryDelay: 1,
										name: 'KLRGN_id',
										hiddenName: 'KLRGN_id',
										id: 'Region_idCombo',
										width: 200,
										listeners: {
											'beforeselect': function(combo, record) {
												var value = record.data[combo.valueField];
												this.ownerCt.findById('SubRegion_idCombo').store.removeAll();
												this.ownerCt.findById('SubRegion_idCombo').store.load({params: {region_id: value}});
												this.ownerCt.findById('City_idCombo').store.removeAll();
												this.ownerCt.findById('City_idCombo').store.load({params: {subregion_id: value}});
												this.ownerCt.findById('Town_idCombo').store.removeAll();
												this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: value}});
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
												this.ownerCt.findById('SubRegion_idCombo').clearValue();
												this.ownerCt.findById('City_idCombo').clearValue();
												this.ownerCt.findById('Town_idCombo').clearValue();
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLSubRGN_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
												combo.setValue(value);
												this.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(combo, this.ownerCt.ownerCt.findById('KLRGN_Socr'));
												
											}
										},
										onTrigger2Click: function() {
												if ( this.value == '' || this.disabled )
													return;
												this.clearValue();
												this.ownerCt.findById('SubRegion_idCombo').store.removeAll();
												this.ownerCt.findById('SubRegion_idCombo').store.load({params: {region_id: 0}});
												this.ownerCt.findById('City_idCombo').store.removeAll();
												this.ownerCt.findById('City_idCombo').store.load({params: {subregion_id: 0}});
												this.ownerCt.findById('Town_idCombo').store.removeAll();
												this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: 0}});
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: 0}});
                            					this.ownerCt.findById('SubRegion_idCombo').clearValue();
												this.ownerCt.findById('City_idCombo').clearValue();
												this.ownerCt.findById('Town_idCombo').clearValue();
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLRGN_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLSubRGN_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
										}
									},
									{
										xtype: 'swsubrgncombo',
										tabIndex: 1202,
										hiddenName: 'KLSubRGN_id',
										name: 'KLSubRGN_id',
										minChars: 0,
										queryDelay: 1,
										id: 'SubRegion_idCombo',
										width: 200,
										listeners: {
											'beforeselect': function(combo, record) {
												var value = record.data[combo.valueField];
												this.ownerCt.findById('City_idCombo').store.removeAll();
                            					this.ownerCt.findById('City_idCombo').store.load({params: {subregion_id: value}});
												this.ownerCt.findById('Town_idCombo').store.removeAll();
												this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: value}});
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
												this.ownerCt.findById('City_idCombo').clearValue();
												this.ownerCt.findById('Town_idCombo').clearValue();
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
												combo.setValue(value);
												this.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(combo, this.ownerCt.ownerCt.findById('KLSubRGN_Socr'));
											}
										},
										onTrigger2Click: function() {
												if ( this.value == '' || this.disabled )
													return;
												this.clearValue();
												this.ownerCt.findById('City_idCombo').clearValue();
												this.ownerCt.findById('Town_idCombo').clearValue();
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLSubRGN_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
												var PID = '';
												if ( this.ownerCt.findById('Region_idCombo').getValue() != '' )
													PID = this.ownerCt.findById('Region_idCombo').getValue();
												this.ownerCt.findById('City_idCombo').store.removeAll();
                            					this.ownerCt.findById('City_idCombo').store.load({params: {subregion_id: PID}});
												this.ownerCt.findById('Town_idCombo').store.removeAll();
												this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: PID}});
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: PID}});
										}
									},
									{
										xtype: 'swcitycombo',
										tabIndex: 1203,
										hiddenName: 'KLCity_id',
										minChars: 0,
										queryDelay: 1,
										name: 'KLCity_id',
										id: 'City_idCombo',
										width: 200,
										listeners: {
											'beforeselect': function(combo, record) {
												var value = record.data[combo.valueField];
												this.ownerCt.findById('Town_idCombo').store.removeAll();
                            					this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: value}});
												this.ownerCt.findById('Street_idCombo').store.removeAll();
												this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
												this.ownerCt.findById('Town_idCombo').clearValue();
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
												combo.setValue(value);
												this.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(combo, this.ownerCt.ownerCt.findById('KLCity_Socr'));
											}
										},
										onTrigger2Click: function() {
											var cmb = this,
												KLRegion_combo = cmb.ownerCt.findById('Region_idCombo'),
												KLSubRegion_combo = cmb.ownerCt.findById('SubRegion_idCombo');

											if(!KLRegion_combo.getValue() && !KLSubRegion_combo.getValue())
												return;

											getWnd('swKLCitySearchWindow').show({
												onSelect: function(response_data) {

													var value = response_data.KLCity_id;
													cmb.ownerCt.findById('Town_idCombo').store.removeAll();
													cmb.ownerCt.findById('Town_idCombo').store.load({params: {city_id: value}});
													cmb.ownerCt.findById('Street_idCombo').store.removeAll();
													cmb.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
													cmb.ownerCt.findById('Town_idCombo').clearValue();
													cmb.ownerCt.findById('Street_idCombo').clearValue();
													cmb.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
													cmb.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
													cmb.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(cmb, this.ownerCt.ownerCt.findById('KLCity_Socr'));
													cmb.setValue(value);

												}.createDelegate(this),
												params: {
													KLSubRegion_id: KLSubRegion_combo.getValue(),
													KLSubRegion_Name: KLSubRegion_combo.getRawValue(),
													KLRegion_id: KLRegion_combo.getValue(),
													KLRegion_Name: KLRegion_combo.getRawValue()
												}
											});
										}
									},
									{
										xtype: 'swtowncombo',
										tabIndex: 1204,
										minChars: 0,
										queryDelay: 1,
										hiddenName: 'KLTown_id',
										name: 'KLTown_id',
										id: 'Town_idCombo',
										width: 200,
										listeners: {
											'beforeselect': function(combo, record) {
												var value = record.data[combo.valueField];
												this.ownerCt.findById('Street_idCombo').store.removeAll();
                            					this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
												this.ownerCt.findById('Street_idCombo').clearValue();
												this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
												combo.setValue(value);
												this.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(combo, this.ownerCt.ownerCt.findById('KLTown_Socr'));
											}
										},
										onTrigger2Click: function() {
											var cmb = this,
												KLRegion_combo = cmb.ownerCt.findById('Region_idCombo'),
												KLSubRegion_combo = cmb.ownerCt.findById('SubRegion_idCombo'),
												KLCity_combo = cmb.ownerCt.findById('City_idCombo');

											if(!KLCity_combo.getValue() && !KLSubRegion_combo.getValue())
												return;

											getWnd('swKLTownSearchWindow').show({
												onSelect: function(response_data) {
													var value = response_data.KLTown_id;
													cmb.ownerCt.findById('Street_idCombo').store.removeAll();
													cmb.ownerCt.findById('Street_idCombo').store.load({params: {town_id: value}});
													cmb.ownerCt.findById('Street_idCombo').clearValue();
													cmb.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
													cmb.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(cmb, this.ownerCt.ownerCt.findById('KLTown_Socr'));
													cmb.setValue(value);
												}.createDelegate(this),
												params: {
													KLRegion_id: KLRegion_combo.getValue(),
													KLRegion_Name: KLRegion_combo.getRawValue(),
													KLSubRegion_id: KLSubRegion_combo.getValue(),
													KLSubRegion_Name: KLSubRegion_combo.getRawValue(),
													KLCity_id: KLCity_combo.getValue(),
													KLCity_Name: KLCity_combo.getRawValue()
												}
											});
										}
									},
									{
										xtype: 'swstreetcombo',
										tabIndex: 1205,
										minChars: 0,
										queryDelay: 1,
										hiddenName: 'KLStreet_id',
										name: 'KLStreet_id',
										id: 'Street_idCombo',
										width: 200,
										listeners: {
											'beforeselect': function(combo, record) {
												var value = record.data[combo.valueField];
												combo.setValue(value);
												this.ownerCt.ownerCt.ownerCt.ownerCt.setSocr(combo, this.ownerCt.ownerCt.findById('KLStreet_Socr'));
											}
										},
										onTrigger2Click: function() {
											if ( this.value == '' || this.disabled )
												return;
											this.clearValue();
											this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
										}
									}
								]
							},
							{
								layout: 'form',
								items: [
								{
                                	xtype: 'swklsocrcombo',
									tabIndex: 1206,
									labelWidth: 5,
									hideLabel: true,
									width: 100,
									disabled: true,
									hideTrigger: true,									
									id: 'KLRGN_Socr'
								},
								{
                                	xtype: 'swklsocrcombo',
									tabIndex: 1207,
									labelWidth: 5,
									hideLabel: true,
									width: 100,
									disabled: true,									
									hideTrigger: true,									
									id: 'KLSubRGN_Socr'
								},
								{
                                	xtype: 'swklsocrcombo',
									tabIndex: 1208,
									labelWidth: 5,
									hideLabel: true,
									width: 100,
									disabled: true,
									hideTrigger: true,									
									id: 'KLCity_Socr'
								},
								{
                                	xtype: 'swklsocrcombo',
									tabIndex: 1209,
									labelWidth: 5,
									hideLabel: true,
									width: 100,
									disabled: true,
									hideTrigger: true,									
									id: 'KLTown_Socr'
								},
								{
                                	xtype: 'swklsocrcombo',
									tabIndex: 1210,
									labelWidth: 5,
									hideLabel: true,
									width: 100,
									disabled: true,
									hideTrigger: true,									
									id: 'KLStreet_Socr'
								}
								]
							}
							]
						},
						{
							xtype: 'checkbox',
							tabIndex: 1211,
							fieldLabel: langs('Вся указанная территория'),
							anchor: '100%',
							name: 'MedServiceStreet_isAll',
							listeners: 
							{
								check: function(checkbox,checked){
									var medServiceStreet_HouseSet = this.ownerCt.findById('MedServiceStreet_HouseSet');
									if (checked) {
										this.ownerCt.findById('gridHouse').disable();
//										this.ownerCt.findById('MedServiceStreet_isChet').disable();
//										this.ownerCt.ownerCt.findById('MedServiceStreet_fp').disable();
										medServiceStreet_HouseSet.setValue(langs('Ч(1-999),Н(1-999)'));
										medServiceStreet_HouseSet.disable();
									} else {
										this.ownerCt.ownerCt.findById('gridHouse').enable();
//										this.ownerCt.ownerCt.findById('MedServiceStreet_isChet').enable();
//										this.ownerCt.ownerCt.findById('MedServiceStreet_fp').enable();
										medServiceStreet_HouseSet.enable();
										this.ownerCt.findById('gridHouse').onAfterEdit();
									}
								}.createDelegate(this)
							}
						},
//						{
//							xtype: 'radiogroup',
//							fieldLabel: 'Сторона улицы',
//							id: 'MedServiceStreet_isChet',
//							name: 'MedServiceStreet_isChet',
//							columns: [100, 100, 100],
//							items: [								
//								{boxLabel: 'Чётная', name: 'ch', value: 1},
//								{boxLabel: 'Нечётная', name: 'ch', value: 2},
//								{boxLabel: 'Другое', name: 'ch', value: 0}
//							],
//							listeners: 
//							{
//								change: function(rb,checked)
//								{
//									
//									if (checked.value==1) {
//										this.ownerCt.findById('MedServiceStreet_HouseSet').setValue('все чётные');
//										this.ownerCt.findById('MedServiceStreet_HouseSet').disable();
//										this.ownerCt.findById('MedServiceStreet_fp').disable();
//									}
//									if (checked.value==2) {
//										this.ownerCt.findById('MedServiceStreet_HouseSet').setValue('все нечётные');
//										this.ownerCt.findById('MedServiceStreet_HouseSet').disable();
//										this.ownerCt.findById('MedServiceStreet_fp').disable();
//									}
//									if (checked.value==0) {
//										this.ownerCt.findById('MedServiceStreet_HouseSet').setValue('');
//										this.ownerCt.findById('MedServiceStreet_HouseSet').enable();
//										this.ownerCt.findById('MedServiceStreet_fp').enable();
//									}
//									// Вызываем перерисовку окна 
//									//this.syncSize();
//								}.createDelegate(this)
//							}
//						},
//						{						
//							border: false,
//							id: 'MedServiceStreet_fp',
//							layout: 'form',
//							style: 'padding: 0px',						
//							items: [								
//								{
//									xtype: 'textfield',
//									width: 100,
//									fieldLabel: 'От',
//									id: 'MedServiceStreet_from',
//									listeners: {
//										change: function(t, val) {											
//
//											this.ownerCt.ownerCt.findById('MedServiceStreet_HouseSet').setValue(val+'-'+this.ownerCt.ownerCt.findById('MedServiceStreet_to').getValue());
//										}
//									}
//								}, {
//									xtype: 'textfield',
//									width: 100,
//									fieldLabel: 'До',
//									id: 'MedServiceStreet_to',
//									listeners: {
//										change: function(t, val) {											
//											this.ownerCt.ownerCt.findById('MedServiceStreet_HouseSet').setValue(this.ownerCt.ownerCt.findById('MedServiceStreet_from').getValue()+'-'+val);
//										}
//									}
//								}
//							]
//						},
						
						new sw.Promed.ViewFrame({							
							autoLoadData: false,
							autoexpand: 'expand',
							isScrollToTopOnLoad:false,
							noFocusOnLoad:true,
							border: true,
							store: store,
							height: 300,
							id: 'gridHouse',
							region: 'center',
							saveAtOnce: false,
							selectionModel: 'cell',
							stringfields: [
								{name: 'gID', editor: new Ext.form.TextField(), type: 'int', header: 'ID', key: true },
								{header: langs('Четность'), dataIndex: 'gType',  name: 'gType', editor: new  Ext.form.ComboBox({
									hideEmptyRow: true,
									allowBlank: true,
									width: 230,
									triggerAction: 'all',
									value: '',
									store: [										
										[langs('Ч'), langs('Четная')],
										[langs('Н'), langs('Нечетная')]
									]
								})},
								{header: langs('От'), dataIndex: 'gFrom', name: 'gFrom', editor: new Ext.form.TextField()},
								{header: langs('До'), dataIndex: 'gTo', name: 'gTo', editor: new Ext.form.TextField()},
								{header: langs('Дом'), dataIndex: 'gValue', name: 'gValue', editor: new Ext.form.TextField()}
							],
							actions: [
								{ name: 'action_add', handler: function(){ this.findById('gridHouse').addEmptyRow(); }.createDelegate(this), hidden: false },
								{ name: 'action_edit', handler: function(){ this.findById('gridHouse').editSelectedCell(); }.createDelegate(this), disabled: true },
								{ name: 'action_view', disabled: true, hidden: true },
								{ name: 'action_delete', handler: function(){ this.findById('gridHouse').deleteRow(); }.createDelegate(this), disabled: true },
								{ name: 'action_refresh', disabled: true, hidden: true },
								{ name: 'action_print', disabled: true, hidden: true },
								{ name: 'action_save', disabled: true, hidden: true }
							],
							listeners: {
								click: function(o) {									
									alert(1);
									console.log(o);
								}								
							},
							onLoadData: function(){

							},
							addEmptyRow: function() {
								var grid = this.getGrid();

								// Генерируем значение идентификатора с отрицательным значением
								// чтобы оперировать несохраненными записями
								var id = - swGenTempId( grid.getStore() );
								grid.getStore().loadData([{ gID: id }], true);

								var rowsCnt = grid.getStore().getCount() - 1;
								var rowSel = 1;
								grid.getSelectionModel().select( rowsCnt, rowSel );
								grid.getView().focusCell( rowsCnt, rowSel );

								var cell = grid.getSelectionModel().getSelectedCell();
								if ( !cell || cell.length == 0 || cell[1] != rowSel ) {
									return false;
								}

								var record = grid.getSelectionModel().getSelected();
								if ( !record ) {
									return false;
								}

								grid.getColumnModel().setEditable( rowSel, true );
								grid.startEditing( cell[0], cell[1] );
							},
							onAfterEdit: function(o){
								var gridStore = this.getGrid().getStore();
								var str = '';
								gridStore.each(function(rec) {
									var gType = rec.get('gType');
									var gFrom = rec.get('gFrom');
									var gTo = rec.get('gTo') || '';
									var gValue = rec.get('gValue');
									if (gValue.trim() != '') {										
										str = str + gValue.trim() + ',';										
									}									
									if (gType.trim() != '' && gFrom.trim() != '' && gTo.trim() != '') {
										str = str + gType.trim() + '(' + gFrom.trim() + '-' + gTo.trim() + ')' + ',';
									} else if (gType.trim() == '' && gFrom.trim() != '' && gTo.trim() != '') {
										str = str + 'Ч(' + gFrom.trim() + '-' + gTo.trim() + ')' + ',' + 'Н(' + gFrom.trim() + '-' + gTo.trim() + ')' + ',';											
									} else if (gType.trim() == '' && gFrom.trim() == '' && gTo.trim() != '') {
										str = str + 'Ч(1-' + gTo.trim() + ')' + ',' + 'Н(1-' + gTo.trim() + ')' + ',';											
									} else if (gType.trim() == '' && gFrom.trim() != '' && gTo.trim() == '') {
										str = str + 'Ч(' + gFrom.trim() + '-999)' + ',' + 'Н(' + gFrom.trim() + '-999)' + ',';											
									} else if (gType.trim() != '' && gFrom.trim() != '' && gTo.trim() == '') {
										str = str + gType.trim() + '(' + gFrom.trim() + '-999)' + ',';											
									} else if (gType.trim() != '' && gFrom.trim() == '' && gTo.trim() != '') {
										str = str + gType.trim() + '(1-' + gTo.trim() + ')' + ',';											
									} 
									
								});	
								if (str.length > 0) {
									str = str.substring(0, str.length - 1);	
								}
							this.ownerCt.ownerCt.findById('MedServiceStreet_HouseSet').setValue(str);
							},

							onCellSelect: function(sm,rowIdx,colIdx){
								var grid = this.getGrid();
								grid.getStore().loadData([], true);
								var record = grid.getSelectionModel().getSelected();
								this.getAction('action_edit').setDisabled( record.get('gID') === null );
								this.getAction('action_delete').setDisabled( record.get('gID') === null );								
							},
							editSelectedCell: function(){
								var grid = this.getGrid();

								var rowsCnt = grid.getStore().getCount() - 1;
								var rowSel = 1;
								var cell = grid.getSelectionModel().getSelectedCell();
								if ( !cell || cell.length == 0 ) {
									return false;
								}

								var record = grid.getSelectionModel().getSelected();
								if ( !record ) {
									return false;
								}

								grid.getColumnModel().setEditable( rowSel, true );
								grid.startEditing( cell[0], cell[1] );								
							},
							deleteRow: function() {
								var grid = this.getGrid();

								var record = grid.getSelectionModel().getSelected();
								if ( !record ) {
									alert('no record');
									return false;
								}

								var id = record.get('gID');
								if ( !id ) {
									sw.swMsg.alert( langs('Ошибка'), langs('Не удалось получить идентификатор.') );
									return false;
								}

								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Удалить запись?'),
									title: langs('Удаление записи'),
									fn: function( buttonId ) {
										if ( buttonId != 'yes' ) {
											return false;
										}

										// Запись еще не сохранена? Просто вычеркиваем
										if ( id < 1 ) {
											grid.getStore().remove(record);
											if ( grid.getStore().getCount() > 0 ) {
												grid.getView().focusRow(0);
												grid.getSelectionModel().selectFirstRow();
											}
										} else {
											// Здесь мы можем удалить лишнее через аякс
											// Но я хочу чтобы это было только через общую
											// кнопку сохранить. Иниипет
											grid.getStore().remove(record);
											if ( grid.getStore().getCount() > 0 ) {
												grid.getView().focusRow(0);
												grid.getSelectionModel().selectFirstRow();
											}
										}
										this.onAfterEdit();
									}.createDelegate(this)
								});
							}
						}),						
						{
							xtype: 'textfield',
							tabIndex: 1211,
							fieldLabel: langs('Номера домов'),
							anchor: '100%',							
							name: 'MedServiceStreet_HouseSet',
							id: 'MedServiceStreet_HouseSet'							
						}
					],
					reader: new Ext.data.JsonReader(
					{
						success: function()
						{
						//alert('success');
						}
					},
					[
						{name: 'MedServiceStreet_id'},
						{name: 'MedService_id'},
						{name: 'KLCountry_id'},
						{name: 'KLRGN_id'},
						{name: 'KLSubRGN_id'},
						{name: 'KLCity_id'},
						{name: 'KLTown_id'},
						{name: 'KLStreet_id'},
						{name: 'MedServiceStreet_HouseSet'},
						{name: 'MedServiceStreet_isAll'}
					]
					),
					url: '/?c=LpuStructure&m=SaveMedServiceStreet',
					enableKeyEvents: true,
				    keys: [{
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('swMedServiceStreetEditWindow').hide();
				        },
				        key: [ Ext.EventObject.J ],
				        stopEvent: true
				    }, {
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('swMedServiceStreetEditWindow').buttons[0].handler();
				        },
				        key: [ Ext.EventObject.C ],
				        stopEvent: true
				    }]
				})
			]
		});
		sw.Promed.swMedServiceStreetEditWindow.superclass.initComponent.apply(this, arguments);
	}
});