/**
* swLpuRegionStreetEditWindow - окно редактирования адреса на участке.
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

sw.Promed.swLpuRegionStreetEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'fit',
	width       : 450,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	closeAction : 'hide',
	plain       : true,
	returnFunc: function(owner, kid) {},
	title: lang['ulitsa_na_uchastke'],
	id: 'swLpuRegionStreetEditWindow',
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
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
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
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
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
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
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
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
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
		form.findField('Country_idCombo').disable();
		form.findField('Region_idCombo').disable();
		form.findField('SubRegion_idCombo').disable();
		form.findField('City_idCombo').disable();
		form.findField('Town_idCombo').disable();
		form.findField('Country_idCombo').setValue(country);
		form.findField('Region_idCombo').store.load({params: {country_id: country}, callback: function() {if (region) form.findField('Region_idCombo').setValue(region); aw.setSocr(form.findField('Region_idCombo'), form.findField('KLRGN_Socr'));}});
		form.findField('SubRegion_idCombo').store.load({params: {region_id: region}, callback: function() {if (subregion) form.findField('SubRegion_idCombo').setValue(subregion); aw.setSocr(form.findField('SubRegion_idCombo'), form.findField('KLSubRGN_Socr'));}});
		var PID = 0;
		if ( subregion != '' )
			PID = subregion
		else
			if ( region != '' )
				PID = region;
		form.findField('City_idCombo').store.load({params: {subregion_id: PID}, callback: function() {if (city) form.findField('City_idCombo').setValue(city); aw.setSocr(form.findField('City_idCombo'), form.findField('KLCity_Socr'));}});
		PID = 0;
		if ( city != '' )
			PID = city;
		else
			if ( subregion != '' )
				PID = subregion
			else
				if ( region != '' )
					PID = region;
		form.findField('Town_idCombo').store.load({params: {city_id: PID}, callback: function() {if (town) form.findField('Town_idCombo').setValue(town); aw.setSocr(form.findField('Town_idCombo'), form.findField('KLTown_Socr'));}});
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
		sw.Promed.swLpuRegionStreetEditWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swLpuRegionStreetEditWindow'), { msg: "Подождите, идет загрузка..." });
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
				
			if ( arguments[0].LpuRegion_Name )
				this.LpuRegion_Name = arguments[0].LpuRegion_Name;
			else 
				this.LpuRegion_Name = null;
			if ( arguments[0].LpuRegion_id )
				this.LpuRegion_id = arguments[0].LpuRegion_id;
			else 
				this.LpuRegion_id = null;
			if ( arguments[0].LpuRegionStreet_id )
				this.LpuRegionStreet_id = arguments[0].LpuRegionStreet_id;
			else 
				this.LpuRegionStreet_id = null;
		}
		var forms = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
		
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['ulitsa_uchastka_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['ulitsa_uchastka_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['ulitsa_uchastka_prosmotr']);
				break;
		}

		forms.reset();
		forms.findField('KLAreaStat_Combo').clearValue();
		forms.findField('KLAreaStat_Combo').getStore().clearFilter();
		forms.findField('LpuRegion_Name').setValue(this.LpuRegion_Name);
		forms.findField('LpuRegion_id').setValue(this.LpuRegion_id);
		forms.findField('LpuRegionStreet_id').setValue(this.LpuRegionStreet_id);
		var aw = this;

		if (this.action=='view')
		{
			forms.findField('KLAreaStat_Combo').disable();
			forms.findField('Country_idCombo').disable();
			forms.findField('Region_idCombo').disable();
			forms.findField('SubRegion_idCombo').disable();
			forms.findField('City_idCombo').disable();
			forms.findField('Town_idCombo').disable();
			forms.findField('Street_idCombo').disable();
			forms.findField('LpuRegionStreet_id').disable();
			forms.findField('LpuRegionStreet_HouseSet').disable();
			Ext.getCmp('swLpuRegionStreetEditWindow').buttons[0].disable();
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
			forms.findField('LpuRegionStreet_id').enable();
			forms.findField('LpuRegionStreet_HouseSet').enable();
			Ext.getCmp('swLpuRegionStreetEditWindow').buttons[0].enable();
		}

		if (this.action!='add')
		{
			forms.load(
			{
				url: C_LPUREGIONSTREET_GET,
				params:
				{
					object: 'LpuRegionStreet',
					LpuRegionStreet_id: this.LpuRegionStreet_id,
					LpuRegion_id: this.LpuRegion_id,
					KLCountry_id: '',
					KLRGN_id: '',
					KLSubRGN_id: '',
					KLCity_id: '',
					KLTown_id: '',
					KLStreet_id: '',
					LpuRegionStreet_HouseSet: ''
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
						forms.findField('Region_idCombo').store.load({params: {country_id: value}, callback: function() {if ( forms.findField('Region_idCombo').getValue()) forms.findField('Region_idCombo').setValue(forms.findField('Region_idCombo').getValue()); aw.setSocr(forms.findField('Region_idCombo'), forms.findField('KLRGN_Socr'));}});
						//forms.findField('SubRegion_idCombo').store.load({params: {region_id: value}, callback: function() {if (forms.findField('SubRegion_idCombo').getValue()) forms.findField('SubRegion_idCombo').setValue(forms.findField('SubRegion_idCombo').getValue()); aw.setSocr(forms.findField('SubRegion_idCombo'), forms.findField('KLSubRGN_Socr'));}});
						//forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue()); aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
					}
					
					if ( forms.findField('Region_idCombo').getValue() != null )
					{
						value = forms.findField('Region_idCombo').getValue();
						forms.findField('SubRegion_idCombo').store.load({params: {region_id: value}, callback: function() {if (forms.findField('SubRegion_idCombo').getValue()) forms.findField('SubRegion_idCombo').setValue(forms.findField('SubRegion_idCombo').getValue()); aw.setSocr(forms.findField('SubRegion_idCombo'), forms.findField('KLSubRGN_Socr'));}});
						forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue()); aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue()); aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue()); aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
					}
					
					if ( forms.findField('SubRegion_idCombo').getValue() != null )
					{
						value = forms.findField('SubRegion_idCombo').getValue();
						forms.findField('City_idCombo').store.load({params: {subregion_id: value}, callback: function() {if (forms.findField('City_idCombo').getValue()) forms.findField('City_idCombo').setValue(forms.findField('City_idCombo').getValue()); aw.setSocr(forms.findField('City_idCombo'), forms.findField('KLCity_Socr'));}});
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue()); aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
					}
					if ( forms.findField('City_idCombo').getValue() != null )
			    	{
						value = forms.findField('City_idCombo').getValue();
						forms.findField('Town_idCombo').store.load({params: {city_id: value}, callback: function() {if (forms.findField('Town_idCombo').getValue()) forms.findField('Town_idCombo').setValue(forms.findField('Town_idCombo').getValue()); aw.setSocr(forms.findField('Town_idCombo'), forms.findField('KLTown_Socr'));}});
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue()); aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
			    	}

					if ( forms.findField('Town_idCombo').getValue() != null )
			    {
						value = forms.findField('Town_idCombo').getValue();
						forms.findField('Street_idCombo').store.load({params: {town_id: value}, callback: function() {if (forms.findField('Street_idCombo').getValue()) forms.findField('Street_idCombo').setValue(forms.findField('Street_idCombo').getValue()); aw.setSocr(forms.findField('Street_idCombo'), forms.findField('KLStreet_Socr'));}});
			    }
					if ( forms.findField('Town_idCombo').getValue() != null &&  aw.findInAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLTown_id', forms.findField('Town_idCombo').getValue()));
						forms.findField('Country_idCombo').disable();
						forms.findField('Region_idCombo').disable();
						forms.findField('SubRegion_idCombo').disable();
						forms.findField('City_idCombo').disable();
						forms.findField('Town_idCombo').disable();
						loadMask.hide();
						return false;
					}
					
					if ( forms.findField('City_idCombo').getValue() != null &&  aw.findInAreaStat('KLCity_id',forms.findField('City_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLCity_id', forms.findField('City_idCombo').getValue()));
						forms.findField('Country_idCombo').disable();
						forms.findField('Region_idCombo').disable();
						forms.findField('SubRegion_idCombo').disable();
						forms.findField('City_idCombo').disable();
						loadMask.hide();
						return false;
					}
					if ( forms.findField('SubRegion_idCombo').getValue() != null &&  aw.findInAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLSubRGN_id', forms.findField('SubRegion_idCombo').getValue()));
						forms.findField('Country_idCombo').disable();
						forms.findField('Region_idCombo').disable();
						forms.findField('SubRegion_idCombo').disable();
						loadMask.hide();
						return false;
					}
					if ( forms.findField('Region_idCombo').getValue() != null && aw.findInAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()) )
					{
						forms.findField('KLAreaStat_Combo').setValue(aw.getFromAreaStat('KLRGN_id', forms.findField('Region_idCombo').getValue()));
						forms.findField('Country_idCombo').disable();
						forms.findField('Region_idCombo').disable();
						loadMask.hide();
						return false;
					}
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
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
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
		var KLTown_id = form.findField('Town_idCombo').getValue();
		var KLStreet_id = form.findField('Street_idCombo').getValue();
		
		if ((!KLTown_id) && (!KLStreet_id))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findField('Town_idCombo').focus(false)
				},
				icon: Ext.Msg.WARNING,
				msg: lang['sleduet_ukazat_kak_minimum_naselennyiy_punkt_ili_ulitsu'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		return true;
	},
	submit: function()
	{
		var form = this.items.items[this.items.findIndex('id', 'lpuregionstreet_edit_window')].getForm();
		var loadMask = new Ext.LoadMask(Ext.get('swLpuRegionStreetEditWindow'), { msg: "Подождите, идет сохранение..." });
		if (this.doSave())
		{
			loadMask.show();
			form.submit(
				{
					params: 
					{
						//LpuRegionStreet_id: form.findField('LpuRegionStreet_id').getValue(),
						//LpuRegion_id: form.findField('LpuRegion_id').getValue(),
						KLCountry_id: form.findField('Country_idCombo').getValue(),
						KLRGN_id: form.findField('Region_idCombo').getValue(),
						KLSubRGN_id: form.findField('SubRegion_idCombo').getValue(),
						KLCity_id: form.findField('City_idCombo').getValue(),
						KLTown_id: form.findField('Town_idCombo').getValue(),
						KLStreet_id: form.findField('Street_idCombo').getValue()
						//LpuRegionStreet_HouseSet: form.findField('LpuRegionStreet_HouseSet')
					},
					failure: function(result_form, action) 
					{
						if (action.result)
						{
							if (action.result.Error_Code)
							{
								Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
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
							if (action.result.LpuRegionStreet_id)
							{
								Ext.getCmp('swLpuRegionStreetEditWindow').hide();
								Ext.getCmp('swLpuRegionStreetEditWindow').returnFunc(Ext.getCmp('swLpuRegionStreetEditWindow').owner, action.result.LpuRegionStreet_id);
							}
							else
								Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
						}
						else
							Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
					}
				});
		}
	},
	initComponent: function() {
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
					id: 'lpuregionstreet_edit_window',
					labelWidth: 95,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [
					{
						name: 'LpuRegionStreet_id',
						id: 'lrsLpuRegionStreet_id',
						xtype: 'hidden'
					},
					{
						name: 'LpuRegion_id',
						id: 'lrsLpuRegion_id',
						xtype: 'hidden'
					},
					{
						name: 'LpuRegion_Name',
						disabled: true,
						fieldLabel: lang['uchastok'],
						tabIndex: -1,
						xtype: 'descfield',
						id: 'lrsLpuRegion_Name'
					}
							,
							{
								xtype: 'fieldset',
								autoHeight: true,
								title: lang['spravochnik_teritoriy'],
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
											if ( this.value == '' || this.disabled )
												return;
											this.clearValue();
											this.ownerCt.findById('Town_idCombo').clearValue();
											this.ownerCt.findById('Street_idCombo').clearValue();
											this.ownerCt.ownerCt.findById('KLCity_Socr').clearValue();
											this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
											this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
											var PID = '';
											if ( this.ownerCt.findById('SubRegion_idCombo').getValue() != '' )
												PID = this.ownerCt.findById('SubRegion_idCombo').getValue();
											else
												if ( this.ownerCt.findById('Region_idCombo').getValue() != '' )
													PID = this.ownerCt.findById('Region_idCombo').getValue();
											this.ownerCt.findById('Town_idCombo').store.removeAll();
											this.ownerCt.findById('Town_idCombo').store.load({params: {city_id: PID}});
											this.ownerCt.findById('Street_idCombo').store.removeAll();
											this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: PID}});
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
											if ( this.value == '' || this.disabled )
												return;
											this.clearValue();
											this.ownerCt.findById('Street_idCombo').clearValue();
											this.ownerCt.ownerCt.findById('KLTown_Socr').clearValue();
											this.ownerCt.ownerCt.findById('KLStreet_Socr').clearValue();
											var PID = '';
   											if ( this.ownerCt.findById('City_idCombo').getValue() != '' )
												PID = this.ownerCt.findById('City_idCombo').getValue();
											else
												if ( this.ownerCt.findById('SubRegion_idCombo').getValue() != '' )
													PID = this.ownerCt.findById('SubRegion_idCombo').getValue();
												else
													if ( this.ownerCt.findById('Region_idCombo').getValue() != '' )
														PID = this.ownerCt.findById('Region_idCombo').getValue();
											this.ownerCt.findById('Street_idCombo').store.removeAll();
											this.ownerCt.findById('Street_idCombo').store.load({params: {town_id: PID}});
										}
									},{
										xtype: 'checkbox',
										fieldLabel: langs('Вся указанная территория'),
										name: 'LpuRegionStreet_IsAll',
										listeners: {
											check: function(checkbox, checked){
												if(checked) {
													this.ownerCt.findById('Street_idCombo').clearValue();
													this.ownerCt.findById('Street_idCombo').disable();
													this.ownerCt.findById('LpuRegionStreet_HouseSet').setValue('');
													this.ownerCt.findById('LpuRegionStreet_HouseSet').disable();
												} else{
													this.ownerCt.findById('Street_idCombo').enable();
													this.ownerCt.findById('LpuRegionStreet_HouseSet').enable();
												}
											}.createDelegate(this)
										}
									},{
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
									id: 'KLStreet_Socr',
									style: {
										'margin-top': '38px'
									}
								}
								]
							}
							]
						},
						{
							xtype: 'textfield',
							tabIndex: 1211,
							fieldLabel: lang['nomera_domov'],
							anchor: '100%',
							id: 'LpuRegionStreet_HouseSet',
							name: 'LpuRegionStreet_HouseSet',
							listeners: 
							{
								'change': function()
								{
									//
								}
							}
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
						{ name: 'LpuRegionStreet_id' },
						{ name: 'LpuRegion_id' },
						{ name: 'KLCountry_id' },
						{ name: 'KLRGN_id' },
						{ name: 'KLSubRGN_id' },
						{ name: 'KLCity_id' },
						{ name: 'KLTown_id' },
						{ name: 'KLStreet_id' },
						{ name: 'LpuRegionStreet_HouseSet' },
						{ name: 'LpuRegionStreet_IsAll'}
					]
					),
					url: C_LPUREGIONSTREET_SAVE,
					enableKeyEvents: true,
				    keys: [{
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('swLpuRegionStreetEditWindow').hide();
				        },
				        key: [ Ext.EventObject.J ],
				        stopEvent: true
				    }, {
				    	alt: true,
				        fn: function(inp, e) {
				        	Ext.getCmp('swLpuRegionStreetEditWindow').buttons[0].handler();
				        },
				        key: [ Ext.EventObject.C ],
				        stopEvent: true
				    }]
				})
			]
		});
		sw.Promed.swLpuRegionStreetEditWindow.superclass.initComponent.apply(this, arguments);
	}
});