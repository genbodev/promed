/**
* swAddressEditWindow - окно редактирования адреса.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
*               Stanislav "Savage" Bykov (savage@swan.perm.ru)
* @version      04.05.2011
* @include      "/PromedWeb/jscore/controllers.js"
*/
/*NO PARSE JSON*/

sw.Promed.swAddressEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swAddressEditWindow',
	objectSrc: '/jscore/Forms/Common/swAddressEditWindow.js',

	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction : 'hide',
	doSave: function() { // TODO: #3600 Part 2, Сохраняем данные
	    
		var base_form = this.FormPanel.getForm();
		var form = this;

		var klcountry_code = 0;

		    
		base_form.findField('KLCountry_idEdit').getStore().each(function(rec) {
			if ( rec.get('KLCountry_id') == base_form.findField('KLCountry_idEdit').getValue() ) {
				klcountry_code = Number(rec.get('KLCountry_Code'));
			}
		});
		
	

		if ( klcountry_code == 0  && this.fields.addressType == 0 ){
			Ext.Msg.alert(lang['oshibka'], lang['vyiberite_stranu_libo_territoriyu']);
			return false;
		}

		
			// проверки на обязательность заполнености полей
		if ( klcountry_code == 643 || klcountry_code == 398) {
		    if (this.fields.addressType == 0){ // если это не аддресс рождения провероки выполняются	
			// 
			// должен быть заполнен регион
			/*
			if ( !base_form.findField('KLRgn_idEdit').getValue() ) {
				sw.swMsg.alert(
					lang['oshibka'],
					lang['ne_zapolnen_region'],
					function() {
						base_form.findField('KLRgn_idEdit').focus();
						return false;
					}
				);
				return false;
			}
			*/
			// для России
			// должен быть заполнен или район, или город, или населенный пункт
			if ( klcountry_code == 643 && !base_form.findField('KLSubRgn_idEdit').getValue() && !base_form.findField('KLCity_idEdit').getValue() && !base_form.findField('KLTown_idEdit').getValue() ) {
				// Если не Мск 
				if (!swFederalKladrGlobalStore.getById(base_form.findField('KLRgn_idEdit').getValue())) {
					sw.swMsg.alert(
						lang['oshibka'],
						lang['doljen_byit_zapolnen_ili_rayon_ili_gorod_ili_naselennyiy_punkt'],
						function() {
							base_form.findField('KLSubRgn_idEdit').focus();
							return false;
						}
					);
					return false;
				}
			}
			
			//Если поле «Город» содержит непустое значение и не указан Нас. пункт, поля «Улица» и «Дом» являются обязательными для заполнения
			if(klcountry_code != 398 && getRegionNick() != 'buryatiya' && base_form.findField('KLCity_idEdit').getValue() && !base_form.findField('KLTown_idEdit').getValue() && (!base_form.findField('KLStreet_idEdit').getValue() || !base_form.findField('Address_HouseEdit').getValue())){
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Укажите улицу и номер дома.'),
					function() {
						if(!base_form.findField('KLStreet_idEdit').getValue()){
							base_form.findField('KLStreet_idEdit').focus();
						} else if(!base_form.findField('Address_HouseEdit').getValue()){
							base_form.findField('Address_HouseEdit').focus();
						}
						return false;
					}
				);
				return false;
			}

			// для Казахстана
			// должен быть заполнен или район, или город, или населенный пункт
			if ( klcountry_code == 398 && this.findById('KLRgn_Socr').getValue() != '162' && !base_form.findField('KLSubRgn_idEdit').getValue() && !base_form.findField('KLCity_idEdit').getValue() && !base_form.findField('KLTown_idEdit').getValue() ) {
				// Если не Мск
				if (!swFederalKladrGlobalStore.getById(base_form.findField('KLRgn_idEdit').getValue())) {
					sw.swMsg.alert(
						lang['oshibka'],
						lang['doljen_byit_zapolnen_ili_rayon_ili_gorod_ili_naselennyiy_punkt'],
						function() {
							base_form.findField('KLSubRgn_idEdit').focus();
							return false;
						}
					);
					return false;
				}
			}

			// Для УФы: должен быть заполнен район города Уфы
			this.isAllowPersonSprTerrDop = this.checkPersonSprTerrDopCombo(base_form.findField('KLCity_idEdit').getValue());
			if ( this.isAllowPersonSprTerrDop && !(base_form.findField('PersonSprTerrDop_idEdit').getValue() > 0) ) {
				sw.swMsg.alert(
					lang['oshibka'],
					lang['ne_zapolnen_rayon_goroda'],
					function() {
						base_form.findField('PersonSprTerrDop_idEdit').focus();
						return false;
					}
				);
				return false;
			}

				// Если это военная часть
				if (base_form.findField('IsSpecObj').getValue()) {
					if (base_form.findField('AddressSpecObject_idEdit').getValue() === ''
						&& base_form.findField('KLCity_idEdit').getValue() && !base_form.findField('KLTown_idEdit').getValue() && this.findById('KLCity_Socr').getValue() == '84'
					) {
						sw.swMsg.alert(
							lang['oshibka'],
							'Не заполнена военная часть',
							function() {
								base_form.findField('AddressSpecObject_idEdit').focus();
								return false;
							}
						);
						return false;
					}
				} else {
				    // должна быть заполнена улица
				    /*
				    if (klcountry_code == 643
							&& (!this.allowBlankStreet
							&& (base_form.findField('KLCity_idEdit').getValue()
							&& !base_form.findField('KLTown_idEdit').getValue()
							&& !base_form.findField('KLStreet_idEdit').getValue()
							&& this.findById('KLCity_Socr').getValue() == '84')
							&& ( base_form.findField('KLCity_idEdit').getValue() != 3656 ))
						 || klcountry_code == 398
							&& !this.allowBlankStreet
							&& !base_form.findField('KLStreet_idEdit').getValue()
							&& this.findById('Street_idCombo').getStore().data.items.length > 0
								&& (this.findById('KLRgn_Socr').getValue() == '162'
								|| base_form.findField('KLTown_idEdit').getValue())
					)
					{
					    sw.swMsg.alert(
						    lang['oshibka'],
						    lang['ne_zapolnena_ulitsa'],
						    function() {
							    base_form.findField('KLStreet_idEdit').focus();
							    return false;
						    }
					    );
					    return false;
				    }
				    */

				    // должен быть заполнен дом
				    /*
				    if (
						!this.allowBlankHouse
						&& base_form.findField('KLCity_idEdit').getValue() && !base_form.findField('KLTown_idEdit').getValue()
						&& String(base_form.findField('Address_HouseEdit').getValue()).replace(/\s/g, '') == ''
						&& this.findById('KLCity_Socr').getValue() == '84'
					) {
					    sw.swMsg.alert(
						    lang['oshibka'],
						    lang['ne_zapolnen_nomer_doma'],
						    function() {
							    base_form.findField('Address_HouseEdit').focus();
							    return false;
						    }
					    );
					    return false;
				    }
				    */
				}
			}
			
			base_form.findField('Country_idCombo').enable();
			base_form.findField('Region_idCombo').enable();
			base_form.findField('SubRegion_idCombo').enable();
			base_form.findField('City_idCombo').enable();
			base_form.findField('PersonSprTerrDop_idCombo').enable();
			base_form.findField('Town_idCombo').enable()
			var specobj = base_form.findField('AddressSpecObject_idEdit');

			var values = {
				Address_ZipEdit: base_form.findField('Address_ZipEdit').getValue(),
				KLCountry_idEdit: base_form.findField('KLCountry_idEdit').getValue(),
				KLRgn_idEdit: base_form.findField('KLRgn_idEdit').getValue(),
				KLRGN_Socr: this.findById('KLRgn_Socr').getValue(),
				KLSubRGN_idEdit: base_form.findField('KLSubRgn_idEdit').getValue(),
				KLSubRGN_Socr: this.findById('KLSubRgn_Socr').getValue(),
				KLCity_idEdit: base_form.findField('KLCity_idEdit').getValue(),
				KLCity_Socr: this.findById('KLCity_Socr').getValue(),
				PersonSprTerrDop_idEdit: base_form.findField('PersonSprTerrDop_idEdit').getValue(),
				KLTown_idEdit: base_form.findField('KLTown_idEdit').getValue(),
				KLTown_Socr: this.findById('KLTown_Socr').getValue(),
				KLStreet_idEdit: base_form.findField('KLStreet_idEdit').getValue(),
				KLStreet_Socr: this.findById('KLStreet_Socr').getValue(),
				Address_HouseEdit: base_form.findField('Address_HouseEdit').getValue(),
				Address_CorpusEdit: base_form.findField('Address_CorpusEdit').getValue(),
				Address_FlatEdit: base_form.findField('Address_FlatEdit').getValue(),
				Address_AddressEdit: base_form.findField('Address_AddressEdit').getValue()
				//Address_begDateEdit: base_form.findField('Address_begDateEdit').getValue()
				
			};
				if(!specobj.disabled){
					values.AddressSpecObject_idEdit=specobj.getRawValue();
					values.AddressSpecObject_id=(specobj.getValue()==specobj.getRawValue())?base_form.findField('AddressSpecObject_id').getValue():specobj.getValue();
				}else{
					values.AddressSpecObject_idEdit=null;
					values.AddressSpecObject_id=null;
				}
			if ( this.ignoreOnClose === true ) {
				this.onWinClose = Ext.emptyFn;
			}

			this.callback(values);
			this.hide();
		}
		else {


                var checkParameters = {
                    KLRgn_idEdit: base_form.findField('KLRgn_idEdit').getValue(),
                    KLRgn_Socr: form.findById('KLRgn_Socr').getValue(),
                    KLSubRgn_idEdit: base_form.findField('KLSubRgn_idEdit').getValue(),
                    KLSubRgn_Socr: form.findById('KLSubRgn_Socr').getValue(),
                    KLCity_idEdit: base_form.findField('KLCity_idEdit').getValue(),
                    KLCity_Socr: form.findById('KLCity_Socr').getValue(),
                    KLTown_idEdit: base_form.findField('KLTown_idEdit').getValue(),
                    KLTown_Socr: form.findById('KLTown_Socr').getValue(),
                    KLStreet_idEdit: base_form.findField('KLStreet_idEdit').getValue(),
                    KLStreet_Socr: form.findById('KLStreet_Socr').getValue()
                };

                
                if (form.checkIsNotForRussia(checkParameters) || Ext.getCmp('manual_input_id').getValue() == true){
					var params = getAllFormFieldValues(this.FormPanel);
                    Ext.Ajax.request({
                        url: '/?c=Address&m=saveChildLists',
						params: params,
                        success: function(result_form, action){
							var response_obj = Ext.util.JSON.decode(result_form.responseText);

                            base_form.findField('Country_idCombo').enable();
                            base_form.findField('Region_idCombo').enable();
                            base_form.findField('SubRegion_idCombo').enable();
                            base_form.findField('City_idCombo').enable();
                            base_form.findField('PersonSprTerrDop_idCombo').enable();
                            base_form.findField('Town_idCombo').enable();
							var specobj = base_form.findField('AddressSpecObject_idEdit');

                            var values = {
                                Address_ZipEdit: base_form.findField('Address_ZipEdit').getValue(),
                                KLCountry_idEdit: base_form.findField('KLCountry_idEdit').getValue(),
                                KLRgn_idEdit: response_obj.AddressId['KLRgn_idEdit'],
                                KLRGN_Socr: form.findById('KLRgn_Socr').getValue(),
                                KLSubRGN_idEdit: response_obj.AddressId['KLSubRGN_idEdit'],
                                KLSubRGN_Socr: form.findById('KLSubRgn_Socr').getValue(),
                                KLCity_idEdit: response_obj.AddressId['KLCity_idEdit'],
                                KLCity_Socr: form.findById('KLCity_Socr').getValue(),
                                PersonSprTerrDop_idEdit: base_form.findField('PersonSprTerrDop_idEdit').getValue(),
                                KLTown_idEdit: response_obj.AddressId['KLTown_idEdit'],
                                KLTown_Socr: form.findById('KLTown_Socr').getValue(),
                                KLStreet_idEdit: response_obj.AddressId['KLStreet_idEdit'],
                                KLStreet_Socr: form.findById('KLStreet_Socr').getValue(),
                                Address_HouseEdit: base_form.findField('Address_HouseEdit').getValue(),
                                Address_CorpusEdit: base_form.findField('Address_CorpusEdit').getValue(),
                                Address_FlatEdit: base_form.findField('Address_FlatEdit').getValue(),
                                Address_AddressEdit: base_form.findField('Address_AddressEdit').getValue()
								//Address_begDateEdit: base_form.findField('Address_begDateEdit').getValue()
                            };
							if(!specobj.disabled){
								values.AddressSpecObject_idEdit=specobj.getRawValue();
								values.AddressSpecObject_id=(specobj.getValue()==specobj.getRawValue())?base_form.findField('AddressSpecObject_id').getValue():specobj.getValue();
							}else{
								values.AddressSpecObject_idEdit=null;
								values.AddressSpecObject_id=null;
							}
							log(values);
                            form.callback(values);

                            form.hide();

                        },
                        failure: function(result_form, action){
                            sw.swMsg.alert(lang['oshibka'], action.result.Message);
                        }
                    });
                } else sw.swMsg.alert(lang['oshibka'], lang['proverte_vvod_dannyih_v_vyidelennyih_polyah']);
		}
	},

    // Проверка значений формы, перед отправкой, не для России
    checkIsNotForRussia: function(checkParameters){
		var base_form = this.FormPanel.getForm();
        var i = 0;
		var lastparameter_notEmpty = true;

		/* 
		 * первый параметр должен быть заполнен (регион)
		 * и каждый второй параметр при заполненном первом тоже должен быть заполнен
		 * (refs #12187)
		 * "Для всех регионов: Для адреса где страна не Россия убрать обязательность ввода поля "Регион" refs #17967
		 */
		var isErrors = false;
		
        for (var parameter in checkParameters) {
            if (checkParameters[parameter] == '') {
				if ((i % 2 == 1 && lastparameter_notEmpty) /*|| (i == 0 && this.fields.addressType != 1)*/) {
					base_form.findField(parameter).markInvalid(lang['trebuetsya_zapolnit']);
					isErrors = true;
				}
				lastparameter_notEmpty = false;
			} else {
				lastparameter_notEmpty = true;
			}
            i++;
        }

        return !isErrors;
    },


	isAllowPersonSprTerrDop: false,
	checkPersonSprTerrDopCombo: function(klarea_id) {
		var base_form = this.FormPanel.getForm();

		var index = base_form.findField('PersonSprTerrDop_idEdit').getStore().findBy(function(rec) { return rec.get('KLArea_id') == klarea_id; });

		var ufaCond = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && !Ext.isEmpty(klarea_id) && klarea_id.toString().inlist([ '1976', '244440' ]));

		return ufaCond;
	},

	refreshPersonSprTerrDopAllowBlank: function() {
		var base_form = this.FormPanel.getForm();

		var KLStreet_idEdit = base_form.findField('KLStreet_idEdit').getValue();
		var ocat = base_form.findField('KLStreet_idEdit').getFieldValue('KLAdr_Ocatd');
		var person_spr_combo = base_form.findField('PersonSprTerrDop_idEdit');
		var val = null;

		person_spr_combo.getStore().findBy(function(rec) {
			if ( rec.get('KLAdr_Ocatd') == ocat ) {
				val = rec.get('PersonSprTerrDop_id');
			}
		});
		if ( val ) {
			person_spr_combo.setAllowBlank(false);
			person_spr_combo.setValue(val);
		}
		else {
			var allowBlank = (KLStreet_idEdit > 0);
			person_spr_combo.clearValue();
			person_spr_combo.setAllowBlank(allowBlank);
		}
	},

	draggable: false,


	//TODO: #3600 Поиск на Территории
	findInAreaStat: function(fieldName, value) {
		var base_form = this.FormPanel.getForm();
		var ret = false;

		base_form.findField('KLAreaStat_idEdit').getStore().each(function(r){
			if ( r.get(fieldName) == value ) {
				ret = true;
				return false;
			}
		});

		return ret;
	},


	//TODO: #3600 Вернуть Территорию
	getFromAreaStat: function(fieldName, value) {
		var base_form = this.FormPanel.getForm();
		var ret = '';

		base_form.findField('KLAreaStat_idEdit').getStore().each(function(r){
			if ( r.get(fieldName) == value ) {
				ret = r.get('KLAreaStat_id');
				return false;
			}
		});

		return ret;
	},


	
	getFromAreaStatById: function(fieldName, value) {
		var base_form = this.FormPanel.getForm();
		var ret = '';

		base_form.findField('KLAreaStat_idEdit').getStore().each(function(r){
			if ( r.get('KLAreaStat_id') == value ) {
				ret = r.get(fieldName);
				return false;
			}
		});

		return ret;
	},


	
	getNameValue: function(combo, fieldName) {
		var ret = '';

		combo.getStore().each(function(r) {
			if ( r.get(r.store.key) == combo.getValue() ) {
				if ( r.get(fieldName) ) {
					ret = r.get(fieldName);
				}
			}
		});

		return ret;
	},


	id: 'address_edit_window',


	
	initComponent: function() {
		var subRgnSocrParams = {where: ' where KLAreaLevel_id = 2'};
		if (getRegionNick() == 'kz') {
			subRgnSocrParams = {where: ' where KLAreaLevel_id = 2 or KLSocr_id = 164'};
		}

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'address_edit_form',
			labelAlign: 'right',
			labelWidth: getRegionNick()=='kz' ? 135 : 95,

			items: [{
				autoHeight: true,
				style: 'padding: 0; padding-top: 5px; margin-bottom: 5px',
				title: lang['spravochnik_territoriy'],
				xtype: 'fieldset',

				items: [{
					enableKeyEvents: true,
					hiddenName: 'KLAreaStat_idEdit',
					id: 'KLAreaStat_Combo',
					listeners: {
						'beforeselect': function(combo, record) {
							var base_form = this.FormPanel.getForm();
							var value = record.get(combo.valueField);

							this.onKLAreaStatChange(value);

							if ( !value || value == '' ) {
								combo.onClearValue();
							}
							/*
							this.findById('Region_idCombo').forceSelection = true;
							this.findById('SubRegion_idCombo').forceSelection = true;
							this.findById('City_idCombo').forceSelection = true;
							this.findById('Town_idCombo').forceSelection = true;
							this.findById('Street_idCombo').forceSelection = true;
							this.findById('KLRgn_Socr').disable();
							this.findById('KLSubRgn_Socr').disable();
							this.findById('KLCity_Socr').disable();
							this.findById('KLTown_Socr').disable();
							this.findById('KLStreet_Socr').disable();
							*/
							this.setDisableFieldsSocr(true);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue){

							var base_form = Ext.getCmp('address_edit_form');

							if ( !newValue || newValue == '' ) {

							    combo.onClearValue();
							    /*
							    base_form.findById('Street_idCombo').enable();
							    base_form.findById('KLRgn_Socr').enable();
							    base_form.findById('KLSubRgn_Socr').enable();
							    base_form.findById('KLCity_Socr').enable();
							    base_form.findById('KLTown_Socr').enable();
							    base_form.findById('KLStreet_Socr').enable();
							    */
							    this.setDisableFieldsSocr(false);
							}

						}.createDelegate(this),
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					onClearValue: function() {
						var base_form = this.FormPanel.getForm();

						//base_form.findField('KLAreaStat_idEdit').clearValue();

						base_form.findField('KLCountry_idEdit').enable();
						base_form.findField('KLRgn_idEdit').enable();
						base_form.findField('KLSubRgn_idEdit').enable();
						base_form.findField('KLCity_idEdit').enable();
						base_form.findField('KLTown_idEdit').enable();
					}.createDelegate(this),
					tabIndex: TABINDEX_ADDREF + 1,
					width: 300,
					xtype: 'swklareastatcombo'
				}]
			}, {
				enableKeyEvents: true,
				fieldLabel: lang['indeks'],
				listeners: {
					'change': function() {
						this.refreshFullAddress();
					}.createDelegate(this)
				},
				name: 'Address_ZipEdit',
				hiddenName: 'Address_ZipEdit',
                onClearValue: function() {
                    this.refreshFullAddress();
                }.createDelegate(this),
				regex: new RegExp('^[0-9]{1,10}$'),
				tabIndex: TABINDEX_ADDREF + 2,
				xtype: 'textfield'
			}, {
				hiddenName: 'KLCountry_idEdit',
				id: 'Country_idCombo',
				allowBlank: false,
				listeners: {
					'beforeselect': function(combo, record) {
						var base_form = this.FormPanel.getForm();
						var value = record.get(combo.valueField);
						
						/*
						if ( Number(value) == 643 || Number(value) == 398 ) {
							base_form.findField('KLRgn_idEdit').forceSelection = true;
							base_form.findField('KLSubRgn_idEdit').forceSelection = true;
							base_form.findField('KLCity_idEdit').forceSelection = true;
							base_form.findField('KLTown_idEdit').forceSelection = true;
							base_form.findField('KLStreet_idEdit').forceSelection = true;
							this.findById('KLRgn_Socr').disable();
							this.findById('KLSubRgn_Socr').disable();
							this.findById('KLCity_Socr').disable();
							this.findById('KLTown_Socr').disable();
							this.findById('KLStreet_Socr').disable();
						}
						else {
							base_form.findField('KLRgn_idEdit').forceSelection = false;
							base_form.findField('KLSubRgn_idEdit').forceSelection = false;
							base_form.findField('KLCity_idEdit').forceSelection = false;
							base_form.findField('KLTown_idEdit').forceSelection = false;
							base_form.findField('KLStreet_idEdit').forceSelection = false;
							this.findById('KLRgn_Socr').enable();
							this.findById('KLSubRgn_Socr').enable();
							this.findById('KLCity_Socr').enable();
							this.findById('KLTown_Socr').enable();
							this.findById('KLStreet_Socr').enable();
						}
						*/
						//this.setDisableFieldsSocr(false);
						if ( Number(value) == 643 ){
							base_form.findField('Address_ZipEdit').disable();
						}else{
							base_form.findField('Address_ZipEdit').enable();
						}

						this.loadChildLists({
							KLAreaLevel_id: 0,
							KLArea_id: value,
							callback: function(){
								//устанавливаем регион пользователя
								var region_combo = this.findField('KLRgn_idEdit');
								var country_combo = this.findField('Country_idCombo');
								if( !region_combo.getValue() && region_combo.findRecord('Region_id', getRegionNumber()) ) {
									region_combo.setValue(getRegionNumber());
									region_combo.fireEvent('beforeselect', region_combo, region_combo.getStore().getById(region_combo.getValue()) );
								}
							}.bind(base_form)
						});
						combo.getStore().clearFilter();
						combo.onClearValue();
						this.refreshFullAddress();
					}.createDelegate(this),
					'change': function() {
						this.setDisableFieldsSocr(false);
						this.refreshFullAddress();
					}.createDelegate(this)
				},
				onClearValue: function() {
					var base_form = this.FormPanel.getForm();

					//base_form.findField('KLCountry_idEdit').clearValue();

					base_form.findField('KLRgn_idEdit').clearValue();
					base_form.findField('KLSubRgn_idEdit').clearValue();
					base_form.findField('KLCity_idEdit').clearValue();
					base_form.findField('PersonSprTerrDop_idEdit').clearValue();
					base_form.findField('KLTown_idEdit').clearValue();
					base_form.findField('KLStreet_idEdit').clearValue();
					base_form.findField('KLRgn_idEdit').getStore().removeAll();
					base_form.findField('KLSubRgn_idEdit').getStore().removeAll();
					base_form.findField('KLCity_idEdit').getStore().removeAll();
					base_form.findField('KLTown_idEdit').getStore().removeAll();
					base_form.findField('KLStreet_idEdit').getStore().removeAll();

					this.findById('KLRgn_Socr').clearValue();
					this.findById('KLSubRgn_Socr').clearValue();
					this.findById('KLCity_Socr').clearValue();
					this.findById('KLTown_Socr').clearValue();
					this.findById('KLStreet_Socr').clearValue();

					this.refreshFullAddress();
				}.createDelegate(this),
				tabIndex: TABINDEX_ADDREF + 3,
				width: 300,
				xtype: 'swklcountrycombo'
			}, {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{
						hiddenName: 'KLRgn_idEdit',
						id: 'Region_idCombo',
						listeners: {
							'beforeselect': function(combo, record) {
								var value = record.get(combo.valueField);

								this.loadChildLists({
									KLAreaLevel_id: 1,
									KLArea_id: value
								});

								this.refreshFullAddress();
								combo.setValue(value);
								this.setSocr(combo, this.findById('KLRgn_Socr'));
							}.createDelegate(this),
							'change': function(){
								this.refreshFullAddress();
								this.cleaningLowerFields('KLRgn_idEdit');
							}.createDelegate(this)
						},
						minChars: 0,
						onClearValue: function() {
							var base_form = this.FormPanel.getForm();

							base_form.findField('KLSubRgn_idEdit').clearValue();
							base_form.findField('KLCity_idEdit').clearValue();
							base_form.findField('PersonSprTerrDop_idEdit').clearValue();
							base_form.findField('KLTown_idEdit').clearValue();
							base_form.findField('KLStreet_idEdit').clearValue();

							base_form.findField('KLSubRgn_idEdit').getStore().removeAll();
							base_form.findField('KLSubRgn_idEdit').getStore().load({
								params: {
									region_id: 0
								}
							});

							base_form.findField('KLCity_idEdit').getStore().removeAll();
							base_form.findField('KLCity_idEdit').getStore().load({
								params: {
									subregion_id: 0
								}
							});

							base_form.findField('KLTown_idEdit').getStore().removeAll();
							base_form.findField('KLTown_idEdit').getStore().load({
								params: {
									city_id: 0
								}
							});

							base_form.findField('KLStreet_idEdit').getStore().removeAll();
							base_form.findField('KLStreet_idEdit').getStore().load({
								params: {
									town_id: 0
								}
							});

							this.findById('KLRgn_Socr').clearValue();
							this.findById('KLSubRgn_Socr').clearValue();
							this.findById('KLCity_Socr').clearValue();
							this.findById('KLTown_Socr').clearValue();
							this.findById('KLStreet_Socr').clearValue();

							this.refreshFullAddress();
						}.createDelegate(this),
						onTrigger2Click: function() {
							if ( this.disabled ) {
								return;
							}

							this.clearValue();
							this.onClearValue();
						},
						queryDelay: 1,
						tabIndex: TABINDEX_ADDREF + 4,
						width: 200,
						xtype: 'swregioncombo'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						loadParams: {params: {where: ' where KLAreaLevel_id = 1'}},
						hiddenName: 'Rgn_Socr',
						hideLabel: true,
						hideTrigger: false,
						id: 'KLRgn_Socr',
						labelWidth: 5,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ADDREF + 5,
						width: 100,
						xtype: 'swklsocrcombo'
					}]
				}]
			},  {
				layout: 'form',
				bodyStyle: 'padding-top: 5px;padding-bottom: 5px;',
				border: false,
				items: [{
					fieldLabel: lang['poisk'],
					name: 'SearchText',
					hiddenName: 'SearchText',
					xtype: 'swsearchaddresscombo',
					listWidth: 600,
					tabIndex: TABINDEX_ADDREF + 6,
					width: 300,
					listeners: {
						'select': function (combo, record) {
							var base_form = this.FormPanel.getForm();
							// здесь мы должны прогрузить по идентификаторам все поля и очистить поле поиска
							var fields = {};
							fields.KLCountry_idEdit = record.get('KLCountry_id');
							fields.KLRgn_idEdit = record.get('KLRgn_id');
							fields.KLSubRgn_idEdit = record.get('KLSubRgn_id');
							fields.KLCity_idEdit = record.get('KLCity_id');
							fields.KLTown_idEdit = record.get('KLTown_id');
							fields.KLStreet_idEdit = record.get('KLStreet_id');

							//передаем id полей для заполнения
							this.loadAllListsAndSetValuesByFields(fields);
							//обновляет строку итогового адреса
							this.refreshFullAddress();
							//очищаем строку поиска
							combo.setValue(null);
						}.createDelegate(this),
						//отвечает за то, чтобы поиск был в конкретной стране, в выбраном регионе
						'beforequery': function(){
							var base_form = this.FormPanel.getForm();
							base_form.findField('SearchText').getStore().baseParams.KLCountry_id = base_form.findField('KLCountry_idEdit').getValue();
							base_form.findField('SearchText').getStore().baseParams.KLRgn_id = base_form.findField('KLRgn_idEdit').getValue();
						}.createDelegate(this)
					}
				}]
			},  {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{
						fieldLabel: getRegionNick()=='kz' ? lang['rayon_gorod_oblastnogo_znacheniya'] : lang['rayon'],
						hiddenName: 'KLSubRgn_idEdit',
						id: 'SubRegion_idCombo',
						listeners: {
							'beforeselect': function(combo, record) {
								var value = record.get(combo.valueField);

								this.loadChildLists({
									KLAreaLevel_id: 2,
									KLArea_id: value
								});

								this.refreshFullAddress();
								combo.setValue(value);
								this.setSocr(combo, this.findById('KLSubRgn_Socr'));
							}.createDelegate(this),
							'change': function(){
								this.refreshFullAddress();
								this.cleaningLowerFields('KLSubRgn_idEdit');
							}.createDelegate(this)
						},
						minChars: 0,
						onClearValue: function() {
							var base_form = this.FormPanel.getForm();

							base_form.findField('KLCity_idEdit').clearValue();
							base_form.findField('PersonSprTerrDop_idEdit').clearValue();
							base_form.findField('KLTown_idEdit').clearValue();
							base_form.findField('KLStreet_idEdit').clearValue();

							this.findById('KLSubRgn_Socr').clearValue();
							this.findById('KLCity_Socr').clearValue();
							this.findById('KLTown_Socr').clearValue();
							this.findById('KLStreet_Socr').clearValue();

							var PID = 0;

							if ( base_form.findField('KLRgn_idEdit').getValue() ) {
								PID = base_form.findField('KLRgn_idEdit').getValue();
							}

							base_form.findField('KLCity_idEdit').getStore().removeAll();
							base_form.findField('KLCity_idEdit').getStore().load({
								params: {
									subregion_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							base_form.findField('KLTown_idEdit').getStore().removeAll();
							base_form.findField('KLTown_idEdit').getStore().load({
								params: {
									city_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							base_form.findField('KLStreet_idEdit').getStore().removeAll();
							base_form.findField('KLStreet_idEdit').getStore().load({
								params: {
									town_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							this.refreshFullAddress();
						}.createDelegate(this),
						onTrigger2Click: function() {
							if ( this.disabled ) {
								return;
							}

							this.clearValue();
							this.onClearValue();
						},
						queryDelay: 1,
						tabIndex: TABINDEX_ADDREF + 7,
						width: 200,
						xtype: 'swsubrgncombo'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						loadParams: {params: subRgnSocrParams},
						hiddenName: 'SubRgn_Socr',
						hideLabel: true,
						hideTrigger: false,
						id: 'KLSubRgn_Socr',
						labelWidth: 5,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ADDREF + 8,
						width: 100,
						xtype: 'swklsocrcombo'
					}]
				}]
			}, {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{ //TODO: 1
						fieldLabel: getRegionNick()=='kz' ? lang['gorod_okrug'] : lang['gorod'],
						hiddenName: 'KLCity_idEdit',
						id: 'City_idCombo',
						listeners: {
							'beforeselect': function(combo, record) {
								var base_form = this.FormPanel.getForm();
								var value = (typeof record == 'object' ? record.get(combo.valueField) : '');

								this.loadChildLists({
									KLAreaLevel_id: 3,
									KLArea_id: value
								});
								this.refreshFullAddress();

								combo.setValue(value);

								this.isAllowPersonSprTerrDop = this.checkPersonSprTerrDopCombo(value);

								if (this.isAllowPersonSprTerrDop) {
									base_form.findField('PersonSprTerrDop_idEdit').enable();
									base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(false);
								}
								else {
									base_form.findField('PersonSprTerrDop_idEdit').getStore().clearFilter();
									base_form.findField('PersonSprTerrDop_idEdit').clearValue();
									base_form.findField('PersonSprTerrDop_idEdit').disable();
									base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);
								}

								this.setSocr(combo, this.findById('KLCity_Socr'));
							}.createDelegate(this),
							'change': function(){
								this.refreshFullAddress();
								this.cleaningLowerFields('KLCity_idEdit');
							}.createDelegate(this)
						},
						minChars: 0,
						onClearValue: function() {
							var base_form = this.FormPanel.getForm();

							base_form.findField('PersonSprTerrDop_idEdit').clearValue();
							base_form.findField('KLTown_idEdit').clearValue();
							base_form.findField('KLStreet_idEdit').clearValue();

							this.findById('KLCity_Socr').clearValue();
							this.findById('KLTown_Socr').clearValue();
							this.findById('KLStreet_Socr').clearValue();

							var PID = 0;

							if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
								PID = base_form.findField('KLSubRgn_idEdit').getValue();
							}
							else if ( base_form.findField('KLRgn_idEdit').getValue() ) {
								PID = base_form.findField('KLRgn_idEdit').getValue();
							}

							base_form.findField('KLTown_idEdit').getStore().removeAll();
							base_form.findField('KLTown_idEdit').getStore().load({
								params: {
									city_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							base_form.findField('KLStreet_idEdit').getStore().removeAll();
							base_form.findField('KLStreet_idEdit').getStore().load({
								params: {
									town_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							this.refreshFullAddress();
						}.createDelegate(this),
						onTrigger2Click: function() {
							var base_form = this.FormPanel.getForm();

							var klsubrgn_id = 0;
							var klsubrgn_name = '';
							var klrgn_id = 0;
							var klrgn_name = '';

							if ( base_form.findField('KLRgn_idEdit').getValue() ) {
								klrgn_id = base_form.findField('KLRgn_idEdit').getValue();
								klrgn_name = base_form.findField('KLRgn_idEdit').getRawValue();
							}

							if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
								klsubrgn_id = base_form.findField('KLSubRgn_idEdit').getValue();
								klsubrgn_name = base_form.findField('KLSubRgn_idEdit').getRawValue();
							}

							getWnd('swKLCitySearchWindow').show({
								onSelect: function(response_data) {
									var fields = {};

									fields.KLCountry_idEdit = response_data.KLCountry_id;
									fields.KLRgn_idEdit = response_data.KLRegion_id;
									fields.KLSubRgn_idEdit = response_data.KLSubRegion_id;
									fields.KLCity_idEdit = response_data.KLCity_id;

									base_form.findField('KLAreaStat_idEdit').clearValue();
									base_form.findField('KLAreaStat_idEdit').onClearValue();

									this.loadAllListsAndSetValuesByFields(fields);
								}.createDelegate(this),
								params: {
									KLSubRegion_id: klsubrgn_id,
									KLSubRegion_Name: klsubrgn_name,
									KLRegion_id: klrgn_id,
									KLRegion_Name: klrgn_name
								}
							});
						}.createDelegate(this),
						queryDelay: 1,
						tabIndex: TABINDEX_ADDREF + 9,
						width: 200,
						xtype: 'swcitycombo'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						loadParams: {params: {where: ' where KLAreaLevel_id = 3'}},
						hideLabel: true,
						hiddenName: 'City_Socr',
						hideTrigger: false,
						id: 'KLCity_Socr',
						labelWidth: 5,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ADDREF + 10,
						width: 100,
						xtype: 'swklsocrcombo'
					}]
				}]
			}, {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{
						disabled: true,
						lastQuery: '',
						fieldLabel: lang['rayon_goroda'],
						hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ufa'])),
						hiddenName: 'PersonSprTerrDop_idEdit',
						hideLabel: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ufa'])),
						id: 'PersonSprTerrDop_idCombo',
						listeners: {
							'change': function() {
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						listWidth: 400,
						onClearValue: function() {
							this.refreshFullAddress();
						}.createDelegate(this),
						tabIndex: TABINDEX_ADDREF + 11,
						width: 200,
						xtype: 'swpersonsprterrdop'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['ufa'])),
						hideLabel: true,
						id: 'PersonSprTerrDop_Socr',
						labelWidth: 5,
						value: lang['r-n_goroda'],
						width: 100,
						xtype: 'textfield'
					}]
				}]
			}, {
				layout: 'column',

				items: [{
					layout: 'form',
					items: [{
						enableKeyEvents: true,
						fieldLabel: getRegionNick()=='kz' ? lang['nas_punkt_poselok_aul_razyezd'] : lang['nas_punkt'],
						hiddenName: 'KLTown_idEdit',
						id: 'Town_idCombo',
						listeners: {
							'beforeselect': function(combo, record) {
								var value = record.get(combo.valueField);

								this.loadChildLists({
									KLAreaLevel_id: 4,
									KLArea_id: value
								});
								//Автозаполнение индекса
								this.setZipAddressByStreetAndHome(combo);
								this.refreshFullAddress();

								combo.setValue(value);

								this.setSocr(combo, this.findById('KLTown_Socr'));
							}.createDelegate(this),
							'change': function(combo){
								//Автозаполнение индекса
								this.setZipAddressByStreetAndHome(combo);
								this.refreshFullAddress();
								this.cleaningLowerFields('KLTown_idEdit');
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == false && e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTrigger2Click();
								}
							}
						},
						minChars: 0,
						onClearValue: function() {
							var base_form = this.FormPanel.getForm();

							base_form.findField('KLStreet_idEdit').clearValue();

							this.findById('KLTown_Socr').clearValue();
							this.findById('KLStreet_Socr').clearValue();

							var PID = 0;

							if ( base_form.findField('KLCity_idEdit').getValue()  ) {
								PID = base_form.findField('KLCity_idEdit').getValue();
							}
							else if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
								PID = base_form.findField('KLSubRgn_idEdit').getValue();
							}
							else if ( base_form.findField('KLRgn_idEdit').getValue() ) {
								PID = base_form.findField('KLRgn_idEdit').getValue();
							}

							base_form.findField('KLStreet_idEdit').getStore().removeAll();
							base_form.findField('KLStreet_idEdit').getStore().load({
								params: {
									town_id: PID,
									Form: 'swAddressEditWindow'
								}
							});

							this.refreshFullAddress();
						}.createDelegate(this),
						onTrigger2Click: function() {
							var base_form = this.FormPanel.getForm();

							var klcity_id = 0;
							var klcity_name = '';
							var klsubrgn_id = 0;
							var klsubrgn_name = '';
							var klrgn_id = 0;
							var klrgn_name = '';

							if ( base_form.findField('KLRgn_idEdit').getValue() ) {
								klrgn_id = base_form.findField('KLRgn_idEdit').getValue();
								klrgn_name = base_form.findField('KLRgn_idEdit').getRawValue();
							}

							if ( base_form.findField('KLCity_idEdit').getValue() ) {
								klcity_id = base_form.findField('KLCity_idEdit').getValue();
								klcity_name = base_form.findField('KLCity_idEdit').getRawValue();
							}

							if ( base_form.findField('KLSubRgn_idEdit').getValue() ) {
								klsubrgn_id = base_form.findField('KLSubRgn_idEdit').getValue();
								klsubrgn_name = base_form.findField('KLSubRgn_idEdit').getRawValue();
							}

							getWnd('swKLTownSearchWindow').show({
								onSelect: function(response_data) {
									var fields = {};

									fields.KLCountry_idEdit = response_data.KLCountry_id;
									fields.KLRgn_idEdit = response_data.KLRegion_id;
									fields.KLSubRgn_idEdit = response_data.KLSubRegion_id;
									fields.KLCity_idEdit = response_data.KLCity_id;
									fields.KLTown_idEdit = response_data.KLTown_id;

									base_form.findField('KLAreaStat_idEdit').clearValue();
									base_form.findField('KLAreaStat_idEdit').onClearValue();

									this.loadAllListsAndSetValuesByFields(fields);
								}.createDelegate(this),
								params: {
									KLCity_id: klcity_id,
									KLSubRegion_id: klsubrgn_id,
									KLCity_Name: klcity_name,
									KLSubRegion_Name: klsubrgn_name,
									KLRegion_id: klrgn_id,
									KLRegion_Name: klrgn_name
								}
							});
						}.createDelegate(this),
						queryDelay: 1,
						tabIndex: TABINDEX_ADDREF + 12,
						width: 200,
						xtype: 'swtowncombo'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						//loadParams: {params: {where: ' where KLAreaLevel_id = 4'}},
						loadParams: {params: {where: ' where KLAreaLevel_id in (4, 5)'}},
						hiddenName: 'Town_Socr',
						hideLabel: true,
						hideTrigger: false,
						id: 'KLTown_Socr',
						labelWidth: 5,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ADDREF + 13,
						width: 100,
						xtype: 'swklsocrcombo'
					}]
				}]
			}, {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{
						ctxSerach: (getRegionNick() == 'krym'),
						editable: true,
						hiddenName: 'KLStreet_idEdit',
						id: 'Street_idCombo',
						lastQuery: '',
						fieldLabel: langs('Улица'),
						listWidth: 400,
						store: new Ext.data.JsonStore({
							url: C_LOAD_STREETCOMBO,
							key: 'Street_id',
							autoLoad: false,
							fields: [
								{name: 'Street_id',    type:'int'},
								{name: 'Socr_id', type: 'int'},
								{name: 'Street_Name',  type:'string'},
								{name: 'Socr_Nick',  type:'string'},
								{name: 'KLAdr_Ocatd',  type:'string'},
								{name: 'AreaPID_Name',  type:'string'},
								{name: 'KLAreaLevel_pid', type: 'int'},
								{name: 'KLArea_pid', type: 'int'},
							],
							sortInfo: {
								field: 'Street_Name'
							}
						}),
						tpl: '<tpl for="."><div class="x-combo-list-item">'+
							'{Street_Name} <span style="color:gray">{Socr_Nick}</span> <span style="color: grey;font-size: 0.7em">{AreaPID_Name}</span>'+
						'</div></tpl>',
						listeners: {
							'beforeselect': function(combo, record) {
								var base_form = this.FormPanel.getForm();
								var value = record.get(combo.valueField);
								
								combo.setValue(value);

								//Автозаполнение индекса
								this.setZipAddressByStreetAndHome(combo);
								// улицу район города
								this.setSprTerrDopByStreetAndHouse(combo);


								//TODO: #3600 Установка сокращения
								this.setSocr(combo, this.findById('KLStreet_Socr'));

								this.refreshFullAddress();
								
								if(combo.getFieldValue('KLAreaLevel_pid') == 4 && combo.getFieldValue('KLArea_pid')){
									//нас. пункт
									var town_combo = base_form.findField('KLTown_idEdit');
									if(town_combo.getValue() != combo.getFieldValue('KLArea_pid') && town_combo.getStore().getById(combo.getFieldValue('KLArea_pid'))){
										town_combo.setValue(combo.getFieldValue('KLArea_pid'));
										town_combo.fireEvent('beforeselect', town_combo, town_combo.getStore().getById(town_combo.getValue()));
									}
								}
							}.createDelegate(this),
							'change': function(){
								this.cleaningLowerFields('KLStreet_idEdit');
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						minChars: 0,
						onClearValue: function() {
							this.findById('KLStreet_Socr').clearValue();
							this.refreshFullAddress();
						}.createDelegate(this),
						onTrigger2Click: function() {
							if ( !this.getValue() || this.disabled ) {
								return;
							}

							this.clearValue();
							this.onClearValue();
						}.createDelegate(this),
						queryDelay: 1,
						tabIndex: TABINDEX_ADDREF + 14,
						width: 200,
						xtype: 'swstreetcombo'
					}]
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						loadParams: {params: {where: ' where KLAreaLevel_id = 5'}},
						hiddenName: 'Street_Socr',
						hideLabel: true,
						hideTrigger: false,
						id: 'KLStreet_Socr',
						labelWidth: 5,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ADDREF + 15,
						width: 100,
						xtype: 'swklsocrcombo'
					}]
				}]
			}, {
				layout: 'column',

				items: [{
					layout: 'form',

					items: [{
						fieldLabel: lang['dom'],
						listeners: {
							'change': function(combo){
								
								// район города устанавливаем
								this.setSprTerrDopByStreetAndHouse(this.FormPanel.getForm().findField('Address_HouseEdit'));
                                //индекс проставляем
                                this.setZipAddressByStreetAndHome(combo,true);
								this.refreshFullAddress();
							}.createDelegate(this),
                            'keypress': function (inp, e){
                                if(8!=e.getKey()&& (44==e.getKey()||(inp.getValue().length>=inp.maxLength)&&getGlobalOptions().region.nick == 'ufa')) {//44 - запятая или больше 6 знаков
                                    e.stopEvent();
                                }
                            }
						},
						maxLength: 6,
                        enableKeyEvents: true,
						name: 'Address_HouseEdit',
						tabIndex: TABINDEX_ADDREF + 16,
						width: 53,
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					labelWidth: 60,

					items: [{
						fieldLabel: lang['korpus'],
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						name: 'Address_CorpusEdit',
						tabIndex: TABINDEX_ADDREF + 17,
						width: 53,
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					labelWidth: 70,

					items: [{
						fieldLabel: lang['kvartira'],
						maxLength: 5,
						autoCreate: {tag: "input", size:14, maxLength: "5", autocomplete: "off"},
						//maskRe: /^([а-яА-Я0-9]{1,5})$/,
						listeners: {
							'change': function(){
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						name: 'Address_FlatEdit',
						tabIndex: TABINDEX_ADDREF + 18,
						width: 53,
						xtype: 'textfieldpmw'
					}]
				}]
			},{
				layout: 'column',
				hidden:!Ext.globalOptions.address.specobject_show,
				items: [{
						hideLabel: false,
						columnWidth: .35,
						name:'IsSpecObj',
						hiddenName:'IsSpecObj',
						listeners: {
							'check': function(checkbox, checked) {
								log(checkbox,checked)
								var base_form = this.FormPanel.getForm();
								base_form.findField('AddressSpecObject_idEdit').setDisabled(!checked);
								
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						boxLabel:lang['spets_obyekt'],
						xtype: 'checkbox'
					},
					{
						name: 'AddressSpecObject_id',
						hiddenName: 'AddressSpecObject_id',
						value:0,
						xtype: 'hidden'
					},
					{
						editable: true,
						hideLabel: true,
						disabled:true,
						minChars : 1,
						listeners: {
							/*'beforequery': function(){
								this.refreshFullAddress();
							}.createDelegate(this),*/
							'select': function(cmb,n,o){
								var base_form = this.FormPanel.getForm();
								if(n)base_form.findField('AddressSpecObject_id').setValue(n.get('AddressSpecObject_id'))
								this.refreshFullAddress();
							}.createDelegate(this),
							'change': function(cmb,n,o){
								var base_form = this.FormPanel.getForm();
								if(cmb.getValue()==cmb.getRawValue()){
									base_form.findField('AddressSpecObject_id').setValue(0);
								}
								this.refreshFullAddress();
							}.createDelegate(this)
						},
						name: 'AddressSpecObject_idEdit',
						hiddenName: 'AddressSpecObject_idEdit',
						tabIndex: TABINDEX_ADDREF + 18,
						width: 150,
						xtype: 'swaddressspecobjectcombo'
					
					}
				
				]
			},{//TODO: Manual Input
			    layout: 'form',
			    border: false,
			    items: [{
					id: 'manual_input_id',
					fieldLabel: lang['ruchnoy_vvod'],
					name: 'hmanual_input',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, checked) {
							var base_form = this.FormPanel.getForm();

							if (checked == true){
								this.clearAllFields();
								this.disableAllFields();

								base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);
								base_form.findField('Address_AddressEdit_id').setReadOnly(false);
								base_form.findField('Address_AddressEdit_id').focus(true, 100);
							}
							else {
								this.enableAllFields();

								base_form.findField('Address_AddressEdit_id').setReadOnly(true);
								base_form.findField('Address_AddressEdit_id').setValue('');
							}
						}.createDelegate(this)
					}
			    }]
			}, {
				autoHeight: true,
				style: 'padding: 5px; margin-bottom: 5px',
				title: lang['polnyiy_adres'],
				xtype: 'fieldset',
				items: [{
					height: 50,
					hideLabel: true,
					setReadOnly: function(readOnly){
					    if(this.rendered){
						this.el.dom.readOnly = readOnly;
					    }
					    this.readOnly = readOnly;				    
					},					
					name: 'Address_AddressEdit',
					id: 'Address_AddressEdit_id',
					readOnly: true,
					width: getRegionNick()=='kz' ? 435 : 400,
					xtype: 'textarea'
				}]
			}/*, {
				allowBlank: true,
				fieldLabel: lang['data'],
				name: 'Address_begDateEdit',
				id: 'Address_begDateEdit_id',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_ADDREF + 19,
				xtype: 'swdatefield',
				width: 100
			}*/],
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('address_edit_window').hide();
				},
				key: [ Ext.EventObject.J ],
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('address_edit_window').doSave();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				tabIndex: TABINDEX_ADDREF + 20,
				text: BTN_FRMSAVE
			},  {
				handler: function() {
					this.defaultFields();
				}.createDelegate(this),
				iconCls: 'clear16',
				tabIndex: TABINDEX_ADDREF + 21,
				text: 'Очистить'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.FormPanel.getForm().findField('KLAreaStat_idEdit').focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ADDREF + 22,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swAddressEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	/**
	 * Соответствие номеров уровней в КЛАДР названиям
	 * @type {Object}
	 */
	levelsList: {
		0: 'Country',
		1: 'KLRgn',
		2: 'KLSubRgn',
		3: 'KLCity',
		4: 'KLTown',
		5: 'KLStreet'
	},
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	setLoadChildLists: function(fields, LevelID, fieldsID, cb){
		//Загрузка значений в поля
		var cal = cb
		var arr = {
			0: 'KLCountry_idEdit',
			1: 'KLRgn_idEdit',
			2: 'KLSubRgn_idEdit',
			3: 'KLCity_idEdit',
			4: 'KLTown_idEdit',
			5: 'KLStreet_idEdit'
		}
		this.loadChildLists({
			KLAreaLevel_id: LevelID,
			KLArea_id: fieldsID,
			callback: function() {
				value = ( fields[arr[LevelID+1]] ) ? fields[arr[LevelID+1]] : -1;
				this.setAddressValues( fields );
				if(LevelID >= 4){
					cal(true);
				}else{
					this.setLoadChildLists(fields, LevelID+1, value, cal);
				}
			}.createDelegate(this)
		});
	},
	//TODO: #3600 Part 4, Подгрузка значений в поля
	loadAllListsAndSetValuesByFields: function( fields ) {
		var value;
		var base_form = this.FormPanel.getForm();
		//функция обратного вызова, которая устанавливает значение страны
		var callback = function(){
			if(fields && fields.KLCountry_idEdit && !base_form.findField('KLCountry_idEdit').getValue()) {
				base_form.findField('KLCountry_idEdit').setValue(fields.KLCountry_idEdit);
			}
		}
		if ( fields.KLCountry_idEdit ) {
			var loadMask = new Ext.LoadMask(
				this.getEl(),
				{msg: "Подождите, идет загрузка...", removeMask: true}
			);
			loadMask.show();

			value = fields.KLCountry_idEdit;

			this.setLoadChildLists(fields, 0, value, function(){
				this.loadMask.hide();
				if(this.callback) this.callback();
			}.bind({loadMask: loadMask, callback: callback}));
			/*
			this.loadChildLists({
				KLAreaLevel_id: 0,
				KLArea_id: value,
				callback: function() {
					if ( fields.KLRgn_idEdit ) {
						value = fields.KLRgn_idEdit;
					}
					else {
						value = -1;
					}

					loadMask.show();

					this.loadChildLists({
						KLAreaLevel_id: 1,
						KLArea_id: value,
						callback: function() {
							if ( fields.KLSubRgn_idEdit ) {
								value = fields.KLSubRgn_idEdit;
							}
							else {
								value = -1;
							}

							loadMask.show();

							this.loadChildLists({
								KLAreaLevel_id: 2,
								KLArea_id: value,
								callback: function() {
									if ( fields.KLCity_idEdit ) {
										value = fields.KLCity_idEdit;
									}
									else {
										value = -1;
									}

									loadMask.show();

									this.loadChildLists({
										KLAreaLevel_id: 3,
										KLArea_id: value,
										callback: function() {
											if ( fields.KLTown_idEdit ) {
												value = fields.KLTown_idEdit;
											}
											else {
												value = -1;
											}

											loadMask.show();

											this.loadChildLists({
												KLAreaLevel_id: 4,
												KLArea_id: value,
												callback: function() {
													if ( fields.KLStreet_idEdit ) {
														value = fields.KLStreet_idEdit;
													}
													else {
														value = -1;
													}
													loadMask.hide();
													this.setAddressValues( fields );
												}.createDelegate(this)
											});

											loadMask.hide();
											this.setAddressValues( fields );
										}.createDelegate(this)
									});

									loadMask.hide();
									this.setAddressValues( fields );
								}.createDelegate(this)
							});

							loadMask.hide();
							this.setAddressValues( fields );
						}.createDelegate(this)
					});

					loadMask.hide();
					this.setAddressValues( fields );
				}.createDelegate(this),
				loadMask: loadMask
			});
			*/
		}
	},
	//конец loadAllListsAndSetValuesByFields
	loadChildLists: function(params) {
		if  ( params.KLArea_id == -1 ) {
			if ( params.callback ) {
				params.callback();
			}
			return;
		}

		var base_form = this.FormPanel.getForm();

		var region_combo = base_form.findField('KLRgn_idEdit');
		var subregion_combo = base_form.findField('KLSubRgn_idEdit');
		var city_combo = base_form.findField('KLCity_idEdit');
		var city_subregion_combo = base_form.findField('PersonSprTerrDop_idEdit');
		var town_combo = base_form.findField('KLTown_idEdit');
		var street_combo = base_form.findField('KLStreet_idEdit');

		// сначала чистим все значения и списки, в зависимости от level
		switch ( params.KLAreaLevel_id ) {
			// страна
			case 0:
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				city_subregion_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				region_combo.getStore().removeAll();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
			break;

			// регион
			case 1:

				subregion_combo.clearValue();
				city_combo.clearValue();
				city_subregion_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
			break;

			// район
			case 2:
				city_combo.clearValue();
				city_subregion_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				street_combo.getStore().removeAll();
			break;

			// город
			case 3:
				city_subregion_combo.clearValue();
				town_combo.clearValue();
				street_combo.clearValue();

				town_combo.getStore().removeAll();    //https://redmine.swan.perm.ru/issues/22165
				street_combo.getStore().removeAll();  //https://redmine.swan.perm.ru/issues/22120
			break;

			// населенный пункт
			case 4:
				street_combo.clearValue();
				street_combo.getStore().removeAll();
			break;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( response && response.responseText ) {
					var child_lists = Ext.util.JSON.decode(response.responseText);

					if ( child_lists.data ) {
						// разбираем и запихиваем в списки
						for ( klarea_level in child_lists.data ) {
							var combo = base_form.findField(this.levelsList[klarea_level] + '_idEdit');

							/** @type {Array} Строка данных для загрузки в комбобокс*/
							var comboData = [];

							for ( index in child_lists.data[klarea_level] ) {
								if ( index == 'remove' ) {
									continue;
								}

								var insRecord = {};
								var record = child_lists.data[klarea_level][index];

								insRecord[combo.valueField] = record['KLArea_id'];
								insRecord[combo.displayField] = record['KLArea_Name'];
								insRecord['Socr_id'] = record['KLSocr_id'];

								// еще и KLAdr_Ocatd записываем
								if ( combo.valueField == 'Street_id' ) {
									insRecord['KLAdr_Ocatd'] = record['KLAdr_Ocatd'];
									insRecord['Socr_Nick'] = record['KLSocr_Nick'];
									insRecord['AreaPID_Name'] = record['AreaPID_Name'];
									insRecord['KLAreaLevel_pid'] = record['KLAreaLevel_pid'];
									insRecord['KLArea_pid'] = record['KLArea_pid'];
								}

								comboData.push(insRecord);
							}

							combo.getStore().loadData(comboData, true);
							combo.collapse();
						}
					}
				}
				if (params.loadMask) {
					params.loadMask.hide();
				}
				if ( params.callback ) {
					params.callback();
				}
			}.createDelegate(this),
			params: {
				KLArea_id: params.KLArea_id,
				KLAreaLevel_id: params.KLAreaLevel_id,
				Form: 'swAddressEditWindow'
			},
			url: C_LOAD_CHILDS
		});
	},
	//конец loadChildLists
	modal: true,
	onKLAreaStatChange: function(value) {
		var base_form = this.FormPanel.getForm();

		var country = this.getFromAreaStatById('KLCountry_id', value),
			region = this.getFromAreaStatById('KLRGN_id', value),
			subregion = this.getFromAreaStatById('KLSubRGN_id', value),
			city = this.getFromAreaStatById('KLCity_id', value),
			city_subregion = this.getFromAreaStatById('PersonSprTerrDop_id', value),
			town = this.getFromAreaStatById('KLTown_id', value),
			fields = {};

		fields.KLCountry_idEdit = country;
		fields.KLRgn_idEdit = region;
		fields.KLSubRgn_idEdit = subregion;
		fields.KLCity_idEdit = city;
		fields.PersonSprTerrDop_idEdit = city_subregion;
		fields.KLTown_idEdit = town;

		base_form.findField('KLCountry_idEdit').disable();
		base_form.findField('KLRgn_idEdit').disable();
		base_form.findField('KLSubRgn_idEdit').disable();
		base_form.findField('KLCity_idEdit').disable();
		//base_form.findField('PersonSprTerrDop_idEdit').disable();
		base_form.findField('KLTown_idEdit').disable();

		if ( country && country > 0 ) {
			base_form.findField('KLCountry_idEdit').setValue(country);
		}

		this.loadAllListsAndSetValuesByFields(fields);

		if ( !town ) {
			base_form.findField('KLTown_idEdit').enable();

			if ( !city ) {
				base_form.findField('KLCity_idEdit').enable();

				if ( !subregion ) {
					base_form.findField('KLSubRgn_idEdit').enable();

					if ( !region ) {
						base_form.findField('KLRgn_idEdit').enable();
					}
				}
			}
		}
	},
	refreshManualInput: function() {
		var base_form = this.FormPanel.getForm();
		if (this.fields.addressType == 0){
			Ext.getCmp('address_edit_window').enableAllFields();
			base_form.findField('manual_input_id').ownerCt.hide();
			Ext.getCmp('address_edit_window').syncSize();
		} else {
			if (
				Ext.isEmpty(this.fields.Address_ZipEdit) &&
					Ext.isEmpty(this.fields.KLCountry_idEdit) &&
					Ext.isEmpty(this.fields.KLRgn_idEdit) &&
					Ext.isEmpty(this.fields.KLSubRGN_idEdit) &&
					Ext.isEmpty(this.fields.KLCity_idEdit) &&
					Ext.isEmpty(this.fields.PersonSprTerrDop_idEdit) &&
					Ext.isEmpty(this.fields.KLTown_idEdit) &&
					Ext.isEmpty(this.fields.KLStreet_idEdit) &&
					Ext.isEmpty(this.fields.Address_HouseEdit) &&
					Ext.isEmpty(this.fields.Address_CorpusEdit) &&
					Ext.isEmpty(this.fields.Address_FlatEdit) &&
					!Ext.isEmpty(this.fields.Address_AddressEdit)
				){
				base_form.findField('manual_input_id').setValue(true);
			}

			base_form.findField('manual_input_id').ownerCt.show();
			Ext.getCmp('address_edit_window').syncSize();
		}
	},
	onShowActions: function() {
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.findField('PersonSprTerrDop_idEdit').enable();
		base_form.findField('KLAreaStat_idEdit').clearValue();
		base_form.setValues(this.fields);
		base_form.findField('KLAreaStat_idEdit').focus(true, 500);

		if (getRegionNick() == 'kz') {
			this.refreshManualInput();
			if (base_form.findField('manual_input_id').getValue()) {
				return false;
			}
		}

        this.loadAllListsAndSetValuesByFields(this.fields);

		this.isAllowPersonSprTerrDop = this.checkPersonSprTerrDopCombo(base_form.findField('KLCity_idEdit').getValue());

		if (this.isAllowPersonSprTerrDop) {
			base_form.findField('PersonSprTerrDop_idEdit').enable();
			base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(false);
		}
		else {
			base_form.findField('PersonSprTerrDop_idEdit').getStore().clearFilter();
			base_form.findField('PersonSprTerrDop_idEdit').clearValue();
			base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);
			base_form.findField('PersonSprTerrDop_idEdit').disable();
		}

		//this.loadAllListsAndSetValuesByFields(this.fields);
		var KLCountry_id = (getRegionNick() == 'kz') ? 398 : 643;
		
		// если не задана ни территория, ни страна, то выбираем страну по умолчанию
		if (KLCountry_id && Ext.isEmpty(base_form.findField('KLCountry_idEdit').getValue())
			&& Ext.isEmpty(base_form.findField('KLAreaStat_idEdit').getValue()))
		{
			base_form.findField('KLCountry_idEdit').setValue(KLCountry_id);

			var index = base_form.findField('KLCountry_idEdit').getStore().findBy(function(rec) {
				return (rec.get('KLCountry_id') == KLCountry_id);
			});

			if ( index >= 0 ) {
				base_form.findField('KLCountry_idEdit').fireEvent('beforeselect', base_form.findField('KLCountry_idEdit'), base_form.findField('KLCountry_idEdit').getStore().getAt(index));
			}
		}
		/*
		if ( !base_form.findField('KLAreaStat_idEdit').getValue() ) {
			base_form.findField('KLCountry_idEdit').enable();
			base_form.findField('KLRgn_idEdit').enable();
			base_form.findField('KLSubRgn_idEdit').enable();
			base_form.findField('KLCity_idEdit').enable();
			base_form.findField('KLTown_idEdit').enable();

			var index = base_form.findField('KLCity_idEdit').getStore().findBy(function(rec) {
				return (rec.get('KLArea_id') == base_form.findField('KLCity_idEdit').getValue());
			});
			if(index > -1){
				base_form.findField('KLCity_idEdit').fireEvent('beforeselect', base_form.findField('KLCity_idEdit'), base_form.findField('KLCity_idEdit').getStore().getAt(index));
			}

			if ( base_form.findField('KLCountry_idEdit').getValue() != '643' ||
				 base_form.findField('KLCountry_idEdit').getValue() != '398')
			{
				base_form.findField('KLRgn_idEdit').forceSelection = false;
				base_form.findField('KLSubRgn_idEdit').forceSelection = false;
				base_form.findField('KLCity_idEdit').forceSelection = false;
				base_form.findField('KLTown_idEdit').forceSelection = false;
				base_form.findField('KLStreet_idEdit').forceSelection = false;
				this.findById('KLRgn_Socr').enable();
				this.findById('KLSubRgn_Socr').enable();
				this.findById('KLCity_Socr').enable();
				this.findById('KLTown_Socr').enable();
				this.findById('KLStreet_Socr').enable();
			}
		}

		var KLCountry_id = 643;
		if (getRegionNick() == 'kz') {
			KLCountry_id = 398;
		}
		// если не задана ни территория, ни страна, то выбираем страну по умолчанию
		if (Ext.isEmpty(base_form.findField('KLCountry_idEdit').getValue())
			&& Ext.isEmpty(base_form.findField('KLAreaStat_idEdit').getValue()))
		{
			base_form.findField('KLCountry_idEdit').setValue(KLCountry_id);

			var index = base_form.findField('KLCountry_idEdit').getStore().findBy(function(rec) {
				return (rec.get('KLCountry_id') == KLCountry_id);
			});

			if ( index >= 0 ) {
				base_form.findField('KLCountry_idEdit').fireEvent('beforeselect', base_form.findField('KLCountry_idEdit'), base_form.findField('KLCountry_idEdit').getStore().getAt(index));
			}
		}
		
		if ( base_form.findField('KLCountry_idEdit').getValue() == '643' ||
			 base_form.findField('KLCountry_idEdit').getValue() == '398') {
			base_form.findField('KLRgn_idEdit').forceSelection = true;
			base_form.findField('KLSubRgn_idEdit').forceSelection = true;
			base_form.findField('KLCity_idEdit').forceSelection = true;
			base_form.findField('KLTown_idEdit').forceSelection = true;
			base_form.findField('KLStreet_idEdit').forceSelection = true;
			this.findById('KLRgn_Socr').disable();
			this.findById('KLSubRgn_Socr').disable();
			this.findById('KLCity_Socr').disable();
			this.findById('KLTown_Socr').disable();
			this.findById('KLStreet_Socr').disable();
		}
		*/
		this.setDisableFieldsSocr(false);

		if ( this.fields.KLTown_idEdit && this.findInAreaStat('KLTown_id', this.fields.KLTown_idEdit) ) {
			base_form.findField('KLAreaStat_idEdit').setValue(this.getFromAreaStat('KLTown_id', this.fields.KLTown_idEdit));
			base_form.findField('KLCountry_idEdit').disable();
			base_form.findField('KLRgn_idEdit').disable();
			base_form.findField('KLSubRgn_idEdit').disable();
			base_form.findField('KLCity_idEdit').disable();
			base_form.findField('PersonSprTerrDop_idEdit').disable();
			base_form.findField('KLTown_idEdit').disable();
			this.onKLAreaStatChange(this.getFromAreaStat('KLTown_id', this.fields.KLTown_idEdit));
			return false;
		}

		if ( this.fields.KLCity_idEdit && this.findInAreaStat('KLCity_id', this.fields.KLCity_idEdit) ) {
			base_form.findField('KLAreaStat_idEdit').setValue(this.getFromAreaStat('KLCity_id', this.fields.KLCity_idEdit));
			base_form.findField('KLCountry_idEdit').disable();
			base_form.findField('KLRgn_idEdit').disable();
			base_form.findField('KLSubRgn_idEdit').disable();
			base_form.findField('KLCity_idEdit').disable();
			this.onKLAreaStatChange(this.getFromAreaStat('KLCity_id', this.fields.KLCity_idEdit));
			return false;
		}

		if ( this.fields.KLSubRgn_idEdit != '' && this.findInAreaStat('KLSubRgn_id', this.fields.KLSubRgn_idEdit) ) {
			base_form.findField('KLAreaStat_idEdit').setValue(this.getFromAreaStat('KLSubRgn_id', this.fields.KLSubRgn_idEdit));
			base_form.findField('KLCountry_idEdit').disable();
			base_form.findField('KLRgn_idEdit').disable();
			base_form.findField('KLSubRgn_idEdit').disable();
			this.onKLAreaStatChange(this.getFromAreaStat('KLSubRgn_id', this.fields.KLSubRgn_idEdit));
			return false;
		}
		if ( this.fields.KLSubRgn_idEdit != '' && this.findInAreaStat('KLSubRGN_id', this.fields.KLSubRgn_idEdit) ) {
			base_form.findField('KLAreaStat_idEdit').setValue(this.getFromAreaStat('KLSubRGN_id', this.fields.KLSubRgn_idEdit));
			base_form.findField('KLCountry_idEdit').disable();
			base_form.findField('KLRgn_idEdit').disable();
			base_form.findField('KLSubRgn_idEdit').disable();
			this.onKLAreaStatChange(this.getFromAreaStat('KLSubRGN_id', this.fields.KLSubRgn_idEdit));
			return false;
		}
		if ( this.fields.KLRgn_idEdit != '' && this.findInAreaStat('KLRgn_id', this.fields.KLRgn_idEdit) ) {
			base_form.findField('KLAreaStat_idEdit').setValue(this.getFromAreaStat('KLRgn_id', this.fields.KLRgn_idEdit));
			base_form.findField('KLCountry_idEdit').disable();
			base_form.findField('KLRgn_idEdit').disable();
			this.onKLAreaStatChange(this.getFromAreaStat('KLRgn_id', this.fields.KLRgn_idEdit));
			return false;
		}

		this.setDisableCountryField(false);
	},
	plain: true,

    // TODO: Очистка полей
    cleaningLowerFields: function (id) {

        var base_form = this.FormPanel.getForm();

        //if ( base_form.findField('KLCountry_idEdit').getValue() != '643' ) {

            switch (id) {
                case 'KLRgn_idEdit':
                        base_form.findField('KLSubRgn_idEdit').clearValue();
                        this.findById('KLSubRgn_Socr').clearValue();
                        base_form.findField('KLCity_idEdit').clearValue();
                        this.findById('KLCity_Socr').clearValue();
                        base_form.findField('KLTown_idEdit').clearValue();
                        this.findById('KLTown_Socr').clearValue();
                        base_form.findField('KLStreet_idEdit').clearValue();
                        this.findById('KLStreet_Socr').clearValue();

						//base_form.findField('KLCity_idEdit').fireEvent('beforeselect', base_form.findField('KLCity_idEdit'));

						base_form.findField('PersonSprTerrDop_idEdit').getStore().clearFilter();
                        base_form.findField('PersonSprTerrDop_idEdit').clearValue();
                        base_form.findField('PersonSprTerrDop_idEdit').disable();
                        base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);

                        base_form.findField('Address_HouseEdit').setValue('');
                        base_form.findField('Address_CorpusEdit').setValue('');
                        base_form.findField('Address_FlatEdit').setValue('');


                    break;
                case 'KLSubRgn_idEdit':
                        base_form.findField('KLCity_idEdit').clearValue();
                        this.findById('KLCity_Socr').clearValue();
                        base_form.findField('KLTown_idEdit').clearValue();
                        this.findById('KLTown_Socr').clearValue();
                        base_form.findField('KLStreet_idEdit').clearValue();
                        this.findById('KLStreet_Socr').clearValue();

						//base_form.findField('KLCity_idEdit').fireEvent('beforeselect', base_form.findField('KLCity_idEdit'));
						base_form.findField('PersonSprTerrDop_idEdit').getStore().clearFilter();
                        base_form.findField('PersonSprTerrDop_idEdit').clearValue();
                        base_form.findField('PersonSprTerrDop_idEdit').disable();
                        base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);

                        base_form.findField('Address_HouseEdit').setValue('');
                        base_form.findField('Address_CorpusEdit').setValue('');
                        base_form.findField('Address_FlatEdit').setValue('');

                    break;
                case 'KLCity_idEdit':
                        base_form.findField('KLTown_idEdit').clearValue();
                        this.findById('KLTown_Socr').clearValue();
                        base_form.findField('KLStreet_idEdit').clearValue();
                        this.findById('KLStreet_Socr').clearValue();

                        base_form.findField('Address_HouseEdit').setValue('');
                        base_form.findField('Address_CorpusEdit').setValue('');
                        base_form.findField('Address_FlatEdit').setValue('');
                        

                    break;
                case 'KLTown_idEdit':
                        base_form.findField('KLStreet_idEdit').clearValue();
                        this.findById('KLStreet_Socr').clearValue();

                        base_form.findField('Address_HouseEdit').setValue('');
                        base_form.findField('Address_CorpusEdit').setValue('');
                        base_form.findField('Address_FlatEdit').setValue('');
                        


                    break;
                case 'KLStreet_idEdit':
                        base_form.findField('Address_HouseEdit').setValue('');
                        base_form.findField('Address_CorpusEdit').setValue('');
                        base_form.findField('Address_FlatEdit').setValue('');
                    break;
            };
	    
	    
		var str = '';
		
		
		
		var index = base_form.findField('Address_ZipEdit').getValue();
		var country = base_form.findField('KLCountry_idEdit').getRawValue();
		
		var region = base_form.findField('KLRgn_idEdit').getRawValue();
		var region_socr = this.findById('KLRgn_Socr').getRawValue();
		var subregion = base_form.findField('KLSubRgn_idEdit').getRawValue();
		var subregion_socr = this.findById('KLSubRgn_Socr').getRawValue();
		var city = base_form.findField('KLCity_idEdit').getRawValue();
		var city_socr = this.findById('KLCity_Socr').getRawValue();
		var town = base_form.findField('KLTown_idEdit').getRawValue();
		var town_socr = this.findById('KLTown_Socr').getRawValue();
		var city_rjon = '';
		var street = base_form.findField('KLStreet_idEdit').getRawValue();
                var street_socr = this.findById('KLStreet_Socr').getRawValue();
		var house = base_form.findField('Address_HouseEdit').getRawValue();
		var corpus = base_form.findField('Address_CorpusEdit').getRawValue();
		var flat = base_form.findField('Address_FlatEdit').getRawValue();
		var specObject = base_form.findField('AddressSpecObject_idEdit')
		str = index + (country == '' ? '' : (index == '' ? '' : ', ') + country) +
			(region == '' ? '' : ', ' + region + ' ' + region_socr) +
			(subregion == '' ? '' : ', ' + subregion + ' ' + subregion_socr) +
			(city == '' ? '' : ', ' + city_socr + ' ' + city ) +
			(city_rjon == '' ? '' : ', ' + city_rjon ) +
			(town == '' ? '' : ', ' + town + ' ' + town_socr) +
			(street == '' ? '' : ', ' + street + ' ' + street_socr) +
			(house == '' ? '' : ', '+lang['d']+ ' ' + house) +
			(corpus == '' ? '' : ', '+lang['korp']+ ' '  + corpus) +
			(flat == '' ? '' : ', '+lang['kv']+ ' ' + flat)+
			((specObject.disabled||specObject.getRawValue()=='')?'':', '+specObject.getRawValue());

		base_form.findField('Address_AddressEdit').setValue(str);
	    
	    
            
       // }

    },
    
    //TODO: Очистка всех полей
    clearAllFields: function() {
	
	var base_form = this.FormPanel.getForm();
	
	base_form.findField('KLAreaStat_idEdit').clearValue();
	base_form.findField('Address_ZipEdit').setValue('');
	base_form.findField('KLCountry_idEdit').clearValue();
	base_form.findField('KLRgn_idEdit').clearValue();
	this.findById('KLRgn_Socr').clearValue();	
	base_form.findField('KLSubRgn_idEdit').clearValue();
	this.findById('KLSubRgn_Socr').clearValue();
	base_form.findField('KLCity_idEdit').clearValue();
	this.findById('KLCity_Socr').clearValue();
	base_form.findField('KLTown_idEdit').clearValue();
	this.findById('KLTown_Socr').clearValue();
	base_form.findField('KLStreet_idEdit').clearValue();
	this.findById('KLStreet_Socr').clearValue();
	base_form.findField('AddressSpecObject_idEdit').clearValue();
	base_form.findField('AddressSpecObject_id').setValue(0);
	base_form.findField('Address_HouseEdit').setValue('');
	base_form.findField('Address_CorpusEdit').setValue('');
	base_form.findField('Address_FlatEdit').setValue('');	
	log('clear')
    },
    
        //TODO: disable всех полей
    disableAllFields: function() {
	
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('KLAreaStat_idEdit').disable();
		base_form.findField('Address_ZipEdit').disable();
		base_form.findField('KLCountry_idEdit').disable();
		base_form.findField('KLRgn_idEdit').disable();
		this.findById('KLRgn_Socr').disable();	
		base_form.findField('KLSubRgn_idEdit').disable();
		this.findById('KLSubRgn_Socr').disable();
		base_form.findField('KLCity_idEdit').disable();
		this.findById('KLCity_Socr').disable();
		base_form.findField('KLTown_idEdit').disable();
		this.findById('KLTown_Socr').disable();
		base_form.findField('KLStreet_idEdit').disable();
		this.findById('KLStreet_Socr').disable();

		base_form.findField('Address_HouseEdit').disable();
		base_form.findField('Address_CorpusEdit').disable();
		base_form.findField('Address_FlatEdit').disable();
		base_form.findField('AddressSpecObject_idEdit').disable();
		base_form.findField('IsSpecObj').disable();
	
    },
    
            //TODO: enable всех полей
    enableAllFields: function() {
	
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('KLAreaStat_idEdit').enable();
		//base_form.findField('Address_ZipEdit').enable();		
		base_form.findField('KLCountry_idEdit').enable();
		base_form.findField('KLRgn_idEdit').enable();
		base_form.findField('KLSubRgn_idEdit').enable();
		base_form.findField('KLCity_idEdit').enable();
		base_form.findField('KLTown_idEdit').enable();
		base_form.findField('KLStreet_idEdit').enable();

		// this.findById('KLRgn_Socr').enable();
		// this.findById('KLSubRgn_Socr').enable();
		// this.findById('KLCity_Socr').enable();
		// this.findById('KLTown_Socr').enable();
		// this.findById('KLStreet_Socr').enable();
		this.setDisableFieldsSocr(false);
		this.setDisableCountryField(false);

		base_form.findField('Address_HouseEdit').enable();
		base_form.findField('Address_CorpusEdit').enable();
		base_form.findField('Address_FlatEdit').enable();
		base_form.findField('AddressSpecObject_id').enable();
		base_form.findField('IsSpecObj').enable();
    },
    

	refreshFullAddress: function(){
		var base_form = this.FormPanel.getForm();
		var str = '';

		var index = base_form.findField('Address_ZipEdit').getValue();
		var country = this.getNameValue(base_form.findField('KLCountry_idEdit'), 'KLCountry_Name');

		var region = this.getNameValue(base_form.findField('KLRgn_idEdit'), 'Region_Name');
		region = (region == '' && base_form.findField('KLRgn_idEdit').getRawValue() ? base_form.findField('KLRgn_idEdit').getRawValue() : region);
		var region_socr = this.getNameValue(this.findById('KLRgn_Socr'), 'KLSocr_Nick');

		var subregion = this.getNameValue(base_form.findField('KLSubRgn_idEdit'), 'SubRgn_Name');
		subregion = (subregion == '' && base_form.findField('KLSubRgn_idEdit').getRawValue() != '' ? base_form.findField('KLSubRgn_idEdit').getRawValue() : subregion);
		var subregion_socr = this.getNameValue(this.findById('KLSubRgn_Socr'), 'KLSocr_Nick');

		var city = this.getNameValue(base_form.findField('KLCity_idEdit'), 'City_Name');
		city = (city == '' && base_form.findField('KLCity_idEdit').getRawValue() != '' ? base_form.findField('KLCity_idEdit').getRawValue() : city);
		var city_socr = this.getNameValue(this.findById('KLCity_Socr'), 'KLSocr_Nick');

		var city_rjon = '';
		var city_rjon_socr = '';
		if ( base_form.findField('PersonSprTerrDop_idEdit') && base_form.findField('PersonSprTerrDop_idEdit').getValue() > 0  ) {
			city_rjon = this.getNameValue(base_form.findField('PersonSprTerrDop_idEdit'), 'PersonSprTerrDop_Name');
		}

		var town = this.getNameValue(base_form.findField('KLTown_idEdit'), 'Town_Name');
		town = (town == '' && base_form.findField('KLTown_idEdit').getRawValue() != '' ? base_form.findField('KLTown_idEdit').getRawValue() : town);
		var town_socr = this.getNameValue(this.findById('KLTown_Socr'), 'KLSocr_Nick');

		var street = this.getNameValue(base_form.findField('KLStreet_idEdit'), 'Street_Name');
		street = (street == '' && base_form.findField('KLStreet_idEdit').getRawValue() != '' ? base_form.findField('KLStreet_idEdit').getRawValue() : street);
		var street_socr = this.getNameValue(this.findById('KLStreet_Socr'), 'KLSocr_Nick');

		var house = base_form.findField('Address_HouseEdit').getValue();
		var corpus = base_form.findField('Address_CorpusEdit').getValue();
		var flat = base_form.findField('Address_FlatEdit').getValue();
		var specObject = base_form.findField('AddressSpecObject_idEdit')
		str = index + (country == '' ? '' : (index == '' ? '' : ', ') + country) +
			(region == '' ? '' : ', ' + region + ' ' + region_socr) +
			(subregion == '' ? '' : ', ' + subregion + ' ' + subregion_socr) +
			(city == '' ? '' : ', ' + city_socr + ' ' + city ) +
			(city_rjon == '' ? '' : ', ' + city_rjon + (city_rjon_socr == '' ? '' : (' ' + city_rjon_socr)) ) +
			(town == '' ? '' : ', ' + town + ' ' + town_socr) +
			(street == '' ? '' : ', ' + street + ' ' + street_socr) +
			(house == '' ? '' : ', '+lang['d']+ ' ' + house) +
			(corpus == '' ? '' : ', '+lang['korp']+ ' ' + corpus) +
			(flat == '' ? '' : ', '+lang['kv']+ ' ' + flat) +
			((specObject.disabled||specObject.getRawValue()=='')?'':', '+specObject.getRawValue());

		base_form.findField('Address_AddressEdit').setValue(str);
	},
	resizable: false,
	setAddressValues: function( fields ) {
		var base_form = this.FormPanel.getForm();

		if ( fields.KLRgn_idEdit ) {
			base_form.findField('KLRgn_idEdit').setValue(fields.KLRgn_idEdit);
		}
		this.setSocr(base_form.findField('KLRgn_idEdit'), this.findById('KLRgn_Socr'));

		if ( fields.KLSubRgn_idEdit ) {
			base_form.findField('KLSubRgn_idEdit').setValue(fields.KLSubRgn_idEdit);
		}
		this.setSocr(base_form.findField('KLSubRgn_idEdit'), this.findById('KLSubRgn_Socr'));

		if ( fields.KLCity_idEdit ) {
			base_form.findField('KLCity_idEdit').setValue(fields.KLCity_idEdit);
		}
		this.setSocr(base_form.findField('KLCity_idEdit'), this.findById('KLCity_Socr'));

		if ( fields.PersonSprTerrDop_idEdit ) {
			base_form.findField('PersonSprTerrDop_idEdit').setValue(fields.PersonSprTerrDop_idEdit);
		}

		if ( fields.KLTown_idEdit ) {
			base_form.findField('KLTown_idEdit').setValue(fields.KLTown_idEdit);
		}
		this.setSocr(base_form.findField('KLTown_idEdit'), this.findById('KLTown_Socr'));

		if ( fields.KLStreet_idEdit ) {
			base_form.findField('KLStreet_idEdit').setValue(fields.KLStreet_idEdit);
		}
		this.setSocr(base_form.findField('KLStreet_idEdit'), this.findById('KLStreet_Socr'));

		this.isAllowPersonSprTerrDop = this.checkPersonSprTerrDopCombo(base_form.findField('KLCity_idEdit').getValue());

		if ( this.isAllowPersonSprTerrDop ) {
			base_form.findField('PersonSprTerrDop_idEdit').enable();
			base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(false);
		}
		else {
			base_form.findField('PersonSprTerrDop_idEdit').getStore().clearFilter();
			base_form.findField('PersonSprTerrDop_idEdit').clearValue();
			base_form.findField('PersonSprTerrDop_idEdit').disable();
			base_form.findField('PersonSprTerrDop_idEdit').setAllowBlank(true);
			base_form.findField('PersonSprTerrDop_idEdit').clearInvalid();
		}
	},
	//TODO: #3600 Возвращает сокращения
	setSocr: function(combo, socrCombo) {
		var socr_id = '';

		combo.getStore().each(function(r) {
			if ( r.get(combo.valueField) == combo.getValue() ) {
				socr_id = r.get('Socr_id');
				return false;
			}
		});

		socrCombo.setValue(socr_id);

		this.refreshFullAddress();
	},
    /**
     * getZipAddressByStreetAndHome
     * Метод устанавливает значение индекса в зависимости от выбранной улицы
     *
     * @param field
     * @returns {boolean}
     */
    setZipAddressByStreetAndHome: function(field,block_focus){
        var base_form = this.FormPanel.getForm();
        //Должны быть выбраны улица и дом ИЛИ населенный пункт
        if ( /*Ext.isEmpty(base_form.findField('KLCity_idEdit').getValue()) ||*/ (Ext.isEmpty(base_form.findField('Address_HouseEdit').getValue()) || Ext.isEmpty(base_form.findField('KLStreet_idEdit').getValue())) && ( Ext.isEmpty(base_form.findField('KLTown_idEdit').getValue()) ) )    {
        	return false;
        }

        var street_id = base_form.findField('KLStreet_idEdit').getValue();
        var town_id = base_form.findField('KLTown_idEdit').getValue();
        if(!base_form.findField('KLTown_idEdit').findRecord('Town_id', town_id)
			&& (Ext.isEmpty(base_form.findField('Address_HouseEdit').getValue()) || Ext.isEmpty(base_form.findField('KLStreet_idEdit').getValue()))) {
        	//в случае если у нас нет данных в поле населенный пункт, но есть данные в полях улица и дом, индекс должен выставляться все равно
			return false;
		}
        var house = String(base_form.findField('Address_HouseEdit').getValue()).replace(/\s/g, '');
        var loadMask = new Ext.LoadMask(
            this.getEl(),
            {msg: "Подождите, идет определение индекса...", removeMask: true}
        );

        base_form.findField('Address_ZipEdit').clearValue;
        this.refreshFullAddress();
        loadMask.show();

        Ext.Ajax.request({
            callback: function(options, success, response) {
                loadMask.hide();

                if ( response && response.responseText ) {
                    var response_data = Ext.util.JSON.decode(response.responseText);

                    if ( response_data['success'] && response_data['success'] === true && response_data['Address_Zip'] && response_data['Address_Zip'] > 0 ) {
                        base_form.findField('Address_ZipEdit').setValue(response_data['Address_Zip']);
                        this.refreshFullAddress();

                        if ( field && !block_focus) {
                            field.focus();
                        }
                    }
                }
                else {
                    sw.swMsg.alert(
                        lang['oshibka'],
                        lang['ne_udalos_avtomaticheski_opredelit_indeks'],
                        function() {
                            if ( field ) {
                                field.focus();
                            }
                        }
                    );
                }
            }.createDelegate(this),
            params: {
                street_id: street_id,
                town_id: town_id,
                house: house
            },
            url: '/?c=Address&m=getZipAddressByStreetAndHome'
        });
    },
	/**
	* getSprTerrDopByStreetAndHouse
	* 
	* Метод устанавливает район города Уфы по значениям полей ввода улицы и дома
	*
	* @param Object
	*
	* @return void
	*/
	setSprTerrDopByStreetAndHouse: function(field) {
		var base_form = this.FormPanel.getForm();

		// обязвательно должен быть сервер Уфы и город должен быть выбран - Уфа!
		if ( !getGlobalOptions()['region'] || getGlobalOptions()['region']['number'] != 2 || Ext.isEmpty(base_form.findField('KLCity_idEdit').getValue())
			|| !base_form.findField('KLCity_idEdit').getValue().toString().inlist([ '1976', '244440' ]) ) {
			return false;
		}

		// так же улица не должна быть пустой и дом
		if ( !base_form.findField('KLStreet_idEdit').getValue() || String(base_form.findField('Address_HouseEdit').getValue()).replace(/\s/g, '') == '' ) {
			return false;
		}
		
		var street_id = base_form.findField('KLStreet_idEdit').getValue();
		var house = String(base_form.findField('Address_HouseEdit').getValue()).replace(/\s/g, '');
		
		var loadMask = new Ext.LoadMask(
			this.getEl(),
			{msg: "Подождите, идет определение района города...", removeMask: true}
		);

		base_form.findField('PersonSprTerrDop_idEdit').clearValue();
		this.refreshFullAddress();

		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( response && response.responseText ) {
					var response_data = Ext.util.JSON.decode(response.responseText);

					if ( response_data['success'] && response_data['success'] === true && response_data['PersonSprTerrDop_id'] && response_data['PersonSprTerrDop_id'] > 0 ) {
						base_form.findField('PersonSprTerrDop_idEdit').setValue(response_data['PersonSprTerrDop_id']);
						this.refreshFullAddress();

						if ( field ) {
							field.focus();
						}
					}
					else {
						// пробуем поставить только по улице
						var person_spr_combo = base_form.findField('PersonSprTerrDop_idEdit');
						var street_okat = base_form.findField('KLStreet_idEdit').getFieldValue('KLAdr_Ocatd');
						var val = null;

						person_spr_combo.getStore().findBy(function(rec) {
							if ( rec.get('KLAdr_Ocatd') == street_okat ) {
								val = rec.get('PersonSprTerrDop_id')
							}
						});

						if ( val ) {
							person_spr_combo.setValue(val);
							this.refreshFullAddress();

							if ( field ) {
								field.focus();
							}
						}
						else {
							sw.swMsg.alert(
								lang['oshibka'],
								lang['ne_udalos_avtomaticheski_opredelit_rayon_goroda'],
								function() {
									if ( field ) {
										field.focus();
									}
								}
							);
						}
					}
				}
				else {
					sw.swMsg.alert(
						lang['oshibka'],
						lang['ne_udalos_avtomaticheski_opredelit_rayon_goroda'],
						function() {
							if ( field ) {
								field.focus();
							}
						}
					);
				}
			}.createDelegate(this),
			params: {
				street_id: street_id,
				house: house
			},
			url: '/?c=Address&m=getSprTerrDopByStreetAndHouse'
		});
	},
	show: function() {
		sw.Promed.swAddressEditWindow.superclass.show.apply(this, arguments);

		this.action = '';
		this.callback = Ext.emptyFn;
		this.fields = {};
		this.ignoreOnClose = false;
		this.deathSvid = false;
		this.allowBlankStreet = false;
		this.allowBlankHouse = false;
		this.onWinClose = Ext.emptyFn;
        var _this = this;

		var base_form = this.FormPanel.getForm();
		//base_form.findField('Address_begDateEdit').setContainerVisible(false);
		
		if ( arguments[0] ) {

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].fields ) {
				this.fields = arguments[0].fields;
				
				this.fields.KLRgn_idEdit = this.fields.KLRgn_idEdit;
				this.fields.KLSubRgn_idEdit = this.fields.KLSubRGN_idEdit;
				
				/*if ( arguments[0].fields.showDate ) {
					base_form.findField('Address_begDateEdit').setContainerVisible(true);
				}*/

				if ( arguments[0].fields.allowBlankStreet ) {
					this.allowBlankStreet = arguments[0].fields.allowBlankStreet;
				}

				if ( arguments[0].fields.allowBlankHouse ) {
					this.allowBlankHouse = arguments[0].fields.allowBlankHouse;
				}
			}

			if ( arguments[0].ignoreOnClose ) {
				this.ignoreOnClose = arguments[0].ignoreOnClose;
			}

			if ( arguments[0].deathSvid ) {
				this.deathSvid = arguments[0].deathSvid;
			}


			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}

		}

		// если это редактирование с загрузкой данных, то загружаем данные
		if ( this.action && this.action == 'edit_with_load' ) {
			var loadMask = new Ext.LoadMask(
				this.getEl(),
				{msg: "Подождите, идет загрузка...", removeMask: true}
			);
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( response && response.responseText ) {
						var resp = Ext.util.JSON.decode(response.responseText);

						if ( resp && resp[0] ) {
							_this.fields = resp[0];
							_this.onShowActions();
						}
					}
				}.createDelegate(this),
				params: {
					Address_id: _this.fields.Address_id
				},
				url: '/?c=Address&m=loadAddressData'
			});
		}
		else {
			_this.onShowActions();
		}

		
		//Возможность ручного ввода доступна только адресу рождения
		/*
		if (this.fields.bdz == 0 && this.fields.addressType == 1 && (!isAdmin) ){
		    
		    Ext.getCmp('address_edit_window').disableAllFields();		    

		    base_form.findField('manual_input_id').ownerCt.hide();
		    Ext.getCmp('address_edit_window').syncSize();
		    
		} else	*/
		this.refreshManualInput();
        base_form.findField('Address_AddressEdit').setValue(this.fields.Address_AddressEdit);//https://redmine.swan.perm.ru/issues/22050
		if(!Ext.globalOptions.address&&Ext.globalOptions.address.specobject_show){
			base_form.findField('IsSpecObj').toggleValue(false);
			base_form.findField('IsSpecObj').setDisabled(true);
		}else{
			if(base_form.findField('AddressSpecObject_idEdit').getValue()){
				base_form.findField('IsSpecObj').toggleValue(true);
				base_form.findField('IsSpecObj').setDisabled(false);
			}
		}

		this.FormPanel.getForm().findField('hmanual_input').setDisabled(this.deathSvid);
		if (arguments[0].disableManualInput && arguments[0].disableManualInput == true && getRegionNick().inlist(['ufa'])) {
			base_form.findField('manual_input_id').disable();
		}
	},
	setDisableFieldsSocr: function (dis){
		var dis = (dis === false || dis) ? dis : true;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var countryCombo = base_form.findField('Country_idCombo');

		var code = countryCombo.getFieldValue('KLCountry_Code');
		if(!code) return false;
		//var code = countryCombo.getStore().getAt(countryCombo.selectedIndex).get('KLCountry_Code');
		var disable = (code.inlist([643, 398]) || dis) ? true : false;
		var arrFielfs = ['KLSubRgn', 'KLCity', 'KLTown', 'KLStreet', 'KLRgn'];
		arrFielfs.forEach(function(item, i, arr) {
			var field = base_form.findField(item+'_idEdit');
			var field_socr = win.findById(item+'_Socr');
			if(field && field_socr) {
				base_form.findField('KLSubRgn_idEdit').forceSelection = (disable) ? false : true;
				if(disable){
					field_socr.disable();
				}else{
					field_socr.enable();
				}
			}
		});

		//После загрузки данных - грузим город и улицу
		base_form.findField('KLCity_idEdit').getStore().addListener("load",
			function(t, r, s, o, eOpts) {
				var base_form = this.FormPanel.getForm();
				base_form.findField('KLCity_idEdit').setValue(this.fields.KLCity_idEdit);
			}, this);

		base_form.findField('KLStreet_idEdit').getStore().addListener("load",
			function(t, r, s, o, eOpts) {
				var base_form = this.FormPanel.getForm();
				base_form.findField('KLStreet_idEdit').setValue(this.fields.KLStreet_idEdit);
			}, this);

		this.setFieldsForceSelection();
	},
	setFieldsForceSelection: function(){
		//debugger;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var countryCombo = base_form.findField('Country_idCombo');
		var code = countryCombo.getFieldValue('KLCountry_Code');
		var fields = [
			'Region_idCombo',
			'SubRegion_idCombo',
			'City_idCombo',
			'Town_idCombo',
			'Street_idCombo'
		]
		var forceSelection = (code.inlist([643, 398])) ? true : false;
		fields.forEach(function(item, i, arr){
			var field = win.findById(item);
			if(field) field.forceSelection = forceSelection;
		});

	},
	setDisableCountryField: function(dis){
		var dis = (dis === false || dis) ? dis : true;
		var base_form = this.FormPanel.getForm();
		var countryCombo = base_form.findField('Country_idCombo');
		var addressCombo = base_form.findField('Address_ZipEdit');
		var code = countryCombo.getFieldValue('KLCountry_Code');
		if(dis || code == 643){
			addressCombo.disable();
		}else{
			addressCombo.enable();
		}
	},
	defaultFields: function(){
		this.onShowActions();
		this.refreshManualInput();
	},
	title: WND_ADDR_EDIT,
	width: getRegionNick()=='kz' ? 480 : 450
});