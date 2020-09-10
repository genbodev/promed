/**
 * swLpuBuildingStreetEditWindow - окно редактирования территорий обслуживаемых подразделением
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Demin D. доработано
 * @version      17.02.2016
 */

sw.Promed.swLpuBuildingStreetEditWindow = Ext.extend(sw.Promed.BaseForm, swop = {
	id: 'swLpuBuildingStreetEditWindow',
	title: lang['territorii_obslujivaemyie_podrazdeleniem'],
	layout: 'fit',
	width: 640,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	plain: true,
	
	/**
	 * @param string fieldName Имя поля из массива записи KLAreaStat_id
	 * @param int id ID KLAreaStat_id
	 * @returns string значение поля для указанной записи KLAreaStat_id
	 */
	getFromAreaStatById: function(fieldName, id) {
		var result = '';
		
		if (id) {
			var row = this.formPanel.getForm().findField('KLAreaStat_id').getStore().getById(id);
			if (row) {
				result = row.data[fieldName];
			}
		}
		
		return result;
	},
	
	/*
	* получение значения для комбобокса территория по юр адресу
	*/
	getTerritoryId: function(){
		Ext.Ajax.request({
			params: {},
			url: '/?c=TerritoryService&m=getKLAreaStatLpuByUAddress',
			callback: function(opt, success, response) {
				if ( success ) {
					
					var res = Ext.util.JSON.decode(response.responseText)[0],
					form = this.formPanel.getForm();

					if(res && res.KLAreaStat_id){
						form.findField('KLAreaStat_id').clearFilter();
						form.findField('KLAreaStat_id').setValue(res.KLAreaStat_id);
						this.onKLAreaStatIdChange();
					}
				}
				else {}
			}.createDelegate(this)
			
		});
	},

	/**
	 * Вызывается при выборе значения в поле "Территория"
	 */
	onKLAreaStatIdChange: function(){
		var win = this,
			form = this.formPanel.getForm(),
			value = form.findField('KLAreaStat_id').getValue(),
			enable = value ? true : false;
		
		var Country_idCombo = form.findField('KLCountry_id'),
			Region_idCombo = form.findField('KLRegion_id'),
			SubRegion_idCombo = form.findField('KLSubRegion_id'),
			City_idCombo = form.findField('KLCity_id'),
			Town_idCombo = form.findField('KLTown_id'),
			Street_idCombo = form.findField('KLStreet_id');
//			KLRGN_Socr = form.findField('KLRGN_Socr'),
//			KLSubRGN_Socr = form.findField('KLSubRGN_Socr'),
//			KLCity_Socr = form.findField('KLCity_Socr'),
//			KLTown_Socr = form.findField('KLTown_Socr');
		
		Country_idCombo.clearValue();
		Region_idCombo.clearValue();
		SubRegion_idCombo.clearValue();
		City_idCombo.clearValue();
		Town_idCombo.clearValue();
		Street_idCombo.clearValue();
//		KLRGN_Socr.clearValue();
//		KLSubRGN_Socr.clearValue();
//		KLCity_Socr.clearValue();
//		KLTown_Socr.clearValue();
		
		Region_idCombo.getStore().removeAll();
		SubRegion_idCombo.getStore().removeAll();
		City_idCombo.getStore().removeAll();
		Town_idCombo.getStore().removeAll();
		Street_idCombo.getStore().removeAll();
		
		if (!enable) {
			Country_idCombo.enable();
			Region_idCombo.enable();
			SubRegion_idCombo.enable();
			City_idCombo.enable();
			Town_idCombo.enable();
			form.findField('LpuBuildingStreet_IsAll').disable();
			return;
		}
		
		Country_idCombo.disable();
		Region_idCombo.disable();
		SubRegion_idCombo.disable();
		City_idCombo.disable();
		Town_idCombo.disable();
		
		var country = this.getFromAreaStatById('KLCountry_id', value),
			region = this.getFromAreaStatById('KLRGN_id', value),
			subregion = this.getFromAreaStatById('KLSubRGN_id', value),
			city = this.getFromAreaStatById('KLCity_id', value),
			town = this.getFromAreaStatById('KLTown_id', value);
	
		Country_idCombo.setValue(country);
		
		Region_idCombo.store.load({params: {country_id: country}, callback: function(){
			if (region) {
				Region_idCombo.setValue(region);
				win.switchTerritoryServiceAll();
			}
		}});
		
		SubRegion_idCombo.store.load({params: {region_id: region}, callback: function(){
			if (subregion) {
				SubRegion_idCombo.setValue(subregion);
				win.switchTerritoryServiceAll();
			}
		}});
	
		// Определяем родительскую запись для загрузки значений
		var city_pid = 0,
			town_pid = 0,
			street_pid = 0;
	
		if (region != '') {
			city_pid = town_pid = street_pid = region;
		}
		if (subregion != '') {
			city_pid = town_pid = street_pid = subregion;
		}
		if (city != '') {
			town_pid = street_pid = city;
		}
		if (town != '') {
			street_pid = town;
		}

		City_idCombo.store.load({params: {subregion_id: city_pid}, callback: function(){
			if (city && City_idCombo.store.findBy(function(rec){return rec.get('City_id') == city}) != -1) {
				console.warn(city)
				City_idCombo.setValue(city);
				win.switchTerritoryServiceAll();
			}
			else{
				City_idCombo.enable();
			}
		}});
		
		Town_idCombo.store.load({params: {city_id: town_pid}, callback: function(){
			if (town) {
				Town_idCombo.setValue(town);
				win.switchTerritoryServiceAll();
			}
		}});
		
		if (street_pid) {
			Street_idCombo.store.load({params: {town_id: street_pid}});
		} else {
			Street_idCombo.store.removeAll();
		}
		
		// Не понял эту логику, поэтому оставляю, как есть
		if (town == '') {
			Town_idCombo.enable();
			if (city == '') {
				City_idCombo.enable();
				if (subregion == '') {
					SubRegion_idCombo.enable();
					if (region == '') {
						Region_idCombo.enable();						
					}
				}
			}
		}
		
	},
	
	listeners: {
		hide: function() {
			//this.onWinClose();
			this.returnFunc(this.owner, -1);
		}
	},

	show: function(){
		sw.Promed.swLpuBuildingStreetEditWindow.superclass.show.apply(this, arguments);
		
		this.showLoadMask(LOAD_WAIT);
		
		if (!arguments[0] || !arguments[0].LpuBuilding_id) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор подстанции.');
			this.hide();
			return;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.returnFunc = arguments[0].callback;
		}

		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}

		this.LpuBuilding_id = arguments[0].LpuBuilding_id;
		this.LpuBuildingStreet_id = arguments[0].LpuBuildingStreet_id;
		this.LpuBuildingTerritoryServiceRel_id = arguments[0].LpuBuildingTerritoryServiceRel_id ? arguments[0].LpuBuildingTerritoryServiceRel_id : null;
		this.TerritoryService_id = arguments[0].TerritoryService_id ? arguments[0].TerritoryService_id : null;
		
		var forms = this.formPanel.getForm();

		// @todo Обновлять хранилища комбобоксов, т.к. при повторном открытии окна, они остаются загруженными
		forms.reset();
		forms.findField('LpuBuilding_id').setValue(this.LpuBuilding_id);
		forms.findField('TerritoryService_id').setValue(this.TerritoryService_id);
//		forms.findField('KLAreaStat_Combo').clearValue();
//		forms.findField('KLAreaStat_Combo').getStore().clearFilter();
		
		var me = this;
			//houses_grid = Ext.getCmp(this.id + '_houses_view_frame');
	
		//houses_grid.ViewGridStore.removeAll();
		
		var loadFormsData = function(callback){
			var params = {
				object: 'LpuBuildingTerrotoryService',
				LpuBuilding_id: me.LpuBuilding_id,
				LpuBuildingStreet_id: me.LpuBuildingStreet_id,
				LpuBuildingTerritoryServiceRel_id: me.LpuBuildingTerritoryServiceRel_id
			};
			
			forms.load({
				url: C_LPUBUILDINGTERRITORYSERVICE_GET4EDIT,
				params: params,
				success: function(form,basicForm){
					// Загрузка данными грида
					/*houses_grid.loadData({
						globalFilters: params,
						noFocusOnLoad: true
					});
					*/
					me.updateRelatedKLCombosStoreAferFormLoad(basicForm.result.data);
					
					me.hideLoadMask();
					
					if (typeof callback === 'function') {
						callback();
					}
				},
				failure: function(){
					me.hideLoadMask();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_povtorite_popyitku_v_sluchae_vozniknoveniya_oshibki_obratites_k_administratoru']);
				}
			});
		}
		
		this.title = this.title.replace(lang['_dobavlenie'],'');
		this.title = this.title.replace(lang['_redaktirovanie'],'');
		this.title = this.title.replace(lang['_prosmotr'],'');
		this.formPanel.getForm().findField('KLAreaStat_id').setAllowBlank(this.action!='add');
		switch(this.action){
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
				me.hideLoadMask();
				
				this.getTerritoryId();
			
				this.updateFormFieldsOnShow();
			break;
			case 'edit':
				this.setTitle(this.title + lang['_redaktirovanie']);
				
				loadFormsData(function(){
					me.updateFormFieldsOnShow();
				});
			break;
			case 'view':
				this.setTitle(this.title + lang['_prosmotr']);
				loadFormsData(function(){
					
				});
			break;
		}
		
		me.updateFormElementsActivity();
		// Событие проверки активности чекбокса LpuBuildingStreet_IsAll
		this.on('onChangeContentRelatedToTerritoryServiceAll', this.switchTerritoryServiceAll, this);
		/*forms.findField('KLCountry_id').on('select', this.switchTerritoryServiceAll, this);
		forms.findField('KLRegion_id').on('select', this.switchTerritoryServiceAll, this);
		forms.findField('KLSubRegion_id').on('select', this.switchTerritoryServiceAll, this);
		forms.findField('KLCity_id').on('select', this.switchTerritoryServiceAll, this);
		forms.findField('KLTown_id').on('select', this.switchTerritoryServiceAll, this);
		forms.findField('KLStreet_id').on('select', this.switchTerritoryServiceAll, this);
		*/
		forms.findField('KLStreet_id').on('select', function(){			
			//houses_grid.getAction('action_add').setDisabled(false);
		}, this);
		forms.findField('KLAreaStat_id').on('select', this.onKLAreaStatIdChange, this);
	},

	
	/**
	 * Изменяет состояние полей в зависимости от выбранного действия
	 */
	updateFormElementsActivity: function(){
		var disabled = this.action == 'view' ? true : false;
			//houses_grid = Ext.getCmp(this.id + '_houses_view_frame');
		
		this.formPanel.ownerCt.buttons[0].setDisabled(disabled);
		
		//houses_grid.getAction('action_add').setDisabled(true);
		//this.formPanel.ownerCt.buttons.forEach(function(btn){btn.setDisabled(disabled);}); //disable all buttons
		this.formPanel.getForm().items.each(function(itm){
			if(itm.name == 'LpuBuildingStreet_IsAll' && disabled == false) return;
			itm.setDisabled(disabled)}
		); //disable all fields
		
	},
	
	/**
	 * Список hiddenName связаных комбобоксов в иерархическом порядке
	 */
	relatedKLCombos: ['KLCountry_id','KLRegion_id','KLSubRegion_id', 'KLCity_id', 'KLTown_id', 'KLStreet_id'],
	
	/**
	 * Метод проверяет значения в связанных комбобоксах и загружает необходимые хранилища
	 */
	updateRelatedKLCombosStoreAferFormLoad: function(data){
		var form = this.formPanel.getForm(),
			combos_hn_list = this.relatedKLCombos,
			combos_values = {},
			//combos_values = data||form.getValues(),
			combos_update_params = {};
			
		var fieldsTop = form.items.items;
		for(var i = 0; i < fieldsTop.length; i++){
			combos_values[fieldsTop[i].getName()] = fieldsTop[i].getValue();
		}
		
		if (combos_values.KLRegion_id) {
			combos_update_params.region_id =
			combos_update_params.subregion_id =
			combos_update_params.city_id =
			combos_update_params.town_id =
			combos_values.KLRegion_id;
		}
		if (combos_values.KLSubRegion_id) {
			combos_update_params.subregion_id =
			combos_update_params.city_id =
			combos_update_params.town_id =
			combos_values.KLSubRegion_id;
		}
		if (combos_values.KLCity_id) {
			combos_update_params.city_id =
			combos_update_params.town_id =
			combos_values.KLCity_id;
		}
		if (combos_values.KLTown_id) {
			combos_update_params.town_id = combos_values.KLTown_id;
		}
		
		if (combos_values.KLCountry_id) {
			form.findField('KLRegion_id').getStore().load({
				params: {country_id: combos_values.KLCountry_id},
				callback: function(){
					if (combos_values.KLRegion_id) {
						form.findField('KLRegion_id').setValue(combos_values.KLRegion_id);
					}
				}
			});
		}
		
		if (combos_update_params.region_id) {
			form.findField('KLSubRegion_id').getStore().load({
				params: {region_id: combos_update_params.region_id},
				callback: function(){
					if (combos_values.KLSubRegion_id) {
						form.findField('KLSubRegion_id').setValue(combos_values.KLSubRegion_id);
					}
				}
			});
		}
		
		if (combos_update_params.subregion_id) {
			form.findField('KLCity_id').getStore().load({
				params: {subregion_id: combos_update_params.subregion_id},
				callback: function(){
					if (combos_values.KLCity_id) {						
						form.findField('KLCity_id').setValue(combos_values.KLCity_id);
					}
				}
			});
		}
		
		if (combos_update_params.city_id) {
			form.findField('KLTown_id').getStore().load({
				params: {city_id: combos_update_params.city_id},
				callback: function(){
					if (combos_values.KLTown_id) {
						form.findField('KLTown_id').setValue(combos_values.KLTown_id);
					}
					else{
						form.findField('KLTown_id').reset();
					}
				}
			});
		}
		
		
		if (combos_update_params.town_id) {
			form.findField('KLStreet_id').getStore().load({
				params: {town_id: combos_update_params.town_id},
				callback: function(){
					if (combos_values.KLStreet_id) {
						form.findField('KLStreet_id').setValue(combos_values.KLStreet_id);						
					}
					else{						
						form.findField('KLStreet_id').reset();
					}
					//houses_grid.getAction('action_add').setDisabled(!combos_values.KLStreet_id);
				}
			});
		}
	},
	
	/**
	 * @param {type} combo Измененный комбобокс
	 * @param {type} record Выбранная запись комбобокса
	 * @param {type} index Выбранный индекс записи комбобокса
	 * @returns {Boolean}
	 */
	updateRelatedKLCombos: function(combo, record, index){
		/**
		 * Очищает значение и удаляет данные их хранилища указанного поля
		 * @param Ext.form.ComboBox field
		 */
		var clearCombo = function(field){
			field.clearValue();
			field.getStore().removeAll();
		};
		
		var form = this.formPanel.getForm(),
			combo_hn = combo.hiddenName,
			combo_val = combo.getValue(),
			combos_hn_list = this.relatedKLCombos,
			combos_cache_vals = {};
		
		// Очищаем значения комбобоксов стоящих ниже в нашей иерархии
		var clear = false,
			combo,
			cnt,
			i;
		for (i=0, cnt=combos_hn_list.length; i<cnt; i++) {
			if (clear === true) {
				combo = form.findField(combos_hn_list[i]);
				if (combo) {
					// Перед тем как очистить комбобокс, запомним значение
					combos_cache_vals[combo.hiddenName] = combo.getValue();
					clearCombo(combo);
				}
			}
			if (combos_hn_list[i] == combo_hn) {
				clear = true;
			}
		}
		
		// Загружаем необходимые
		switch (combo_hn) {
			case 'KLCountry_id':
				form.findField('KLRegion_id').getStore().load({params: {country_id: combo_val}});
			break;
			
			case 'KLRegion_id':
				form.findField('KLSubRegion_id').getStore().load({params: {region_id: combo_val}});
				form.findField('KLCity_id').getStore().load({params: {subregion_id: combo_val}});
				form.findField('KLTown_id').getStore().load({params: {city_id: combo_val}});
				form.findField('KLStreet_id').getStore().load({params: {town_id: combo_val}});
			break;
			
			case 'KLSubRegion_id':
				form.findField('KLCity_id').getStore().load({params: {subregion_id: combo_val}});
				form.findField('KLTown_id').getStore().load({params: {city_id: combo_val}});
				form.findField('KLStreet_id').getStore().load({params: {town_id: combo_val}});
			break;
			
			case 'KLCity_id':
				form.findField('KLTown_id').getStore().load({params: {city_id: combo_val}});
				form.findField('KLStreet_id').getStore().load({params: {town_id: combo_val}});
			break;
			
			case 'KLTown_id':
				form.findField('KLStreet_id').getStore().load({params: {town_id: combo_val}});
			break;
		}
		
		return true;
	},
	
	/**
	 * Обновление полей при загрузке
	 */
	updateFormFieldsOnShow: function(){
		var me = this,
			form = this.formPanel.getForm();
		
		/**
		 * Очищает значение и удаляет данные их хранилища указанного поля
		 * @param Ext.form.ComboBox field
		 */
		var clearCombo = function(field){
			field.clearValue();
			field.getStore().removeAll();
		};
		
		form.findField('LpuBuildingStreet_IsAll').enable();

		/**
		 * Очищает значение и удаляет данные их хранилища указанного поля. Обновляет
		 * хранилище если передан параметр storeLoadParams
		 * @param Ext.form.ComboBox field
		 * @param object storeLoadParams
		 */
		var changeCombo = function(field, storeLoadParams){
			clearCombo(field);
			
			if (typeof storeLoadParams === 'object') {
				field.getStore().load({params: storeLoadParams});
			}
		};

		form.findField('KLCountry_id').on({
			select: function(combo, record, index){
				me.updateRelatedKLCombos(combo, record, index);
			},
			clearValue: function(){
				clearCombo(form.findField('KLRegion_id'));
			}
		});
		
		form.findField('KLRegion_id').on({
			select: function(combo, record, index){
				me.updateRelatedKLCombos(combo, record, index);
			},
			clearValue: function(){
				clearCombo(form.findField('KLSubRegion_id'));
			}
		});
		
		form.findField('KLSubRegion_id').on({
			select: function(combo, record, index){
				me.updateRelatedKLCombos(combo, record, index);
			},
			clearValue: function(){
				clearCombo(form.findField('KLCity_id'));
			}
		});
		
		form.findField('KLCity_id').on({
			select: function(combo, record, index){
				me.updateRelatedKLCombos(combo, record, index);
			},
			clearValue: function(){
				clearCombo(form.findField('KLTown_id'));
			}
		});
		
		form.findField('KLTown_id').on({
			select: function(combo, record, index){
				me.updateRelatedKLCombos(combo, record, index);
			},
			clearValue: function(){
				clearCombo(form.findField('KLStreet_id'));
			}
		});
	},
	
	/**
	 * Переключение активности чекбокса LpuBuildingStreet_IsAll
	 * Если одно из указанных полей в checkFields заполнено, значит чекбокс активен
	 */
	switchTerritoryServiceAll: function(){
		// При изменении вложенности заменить этот путь
		var form = this.formPanel.getForm(),
			checkFields = [
				'KLRegion_id', // Регион
				'KLSubRegion_id', // Район
				'KLCity_id', // Город
				/*'KLTown_id', // Нас. пункт
				'KLStreet_id' // Улица */
			],
			enable = false,
			field;
		for (var i=0, cnt=checkFields.length; i<cnt; i++) {		
			field = form.findField(checkFields[i]);
			if (field && field.getValue()) {
				enable = true;
				break;
			}
		}
		form.findField('LpuBuildingStreet_IsAll')[(enable ? 'enable' : 'disable')]();
	},
	
	/**
	 * Валидация
	 */
	doValidate: function() {
		var form = this.formPanel.getForm(),
			isAll = form.findField('LpuBuildingStreet_IsAll').getValue();
	
		// Отмечена вся территория?
		if (isAll) {
			// Предполагается что флажок нельзя отметить, не выбрав необходимые поля.
			// Состояние флажка изменяется по событиям. При необходимости можно дополнительно вставить сюда валидацию.
			return true;
		}
		
		var KLTown_id = form.findField('KLTown_id').getValue(),
			KLStreet_id = form.findField('KLStreet_id').getValue();
//			HouseSet = form.findField('LpuBuildingStreet_HouseSet').getValue();
		
		if (!KLTown_id && !KLStreet_id) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, 'Необходимо указать город или установить флаг «Вся указанная территория».');
			return false;
		}
		
//		if (!HouseSet) {
//			sw.swMsg.alert(ERR_INVFIELDS_TIT, 'Необходимо указать номер дома, диапазон домов, указывается через дефис, или отметить всю указанную территорию.');
//			return false;			
//		}

		return true;
	},
	
	submit: function(){
//		if (!this.doValidate()) {
//			return;
//		}

		var form = this.formPanel.getForm(),
			me = this,
			params = form.getValues();
		
		//проверка на обязательные поля
		//если не заполнено вся территория и номера домов
		if (!form.findField('LpuBuildingStreet_IsAll').getValue() && !form.findField('LpuBuildingStreet_HouseSet').getValue()) {
			Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG, function(btn, text){
				if (btn == 'ok'){
					//t.hide();
				}
			});
			return false;
		}
		
		this.showLoadMask(LOAD_WAIT_SAVE);
		
		params.KLCountry_id = form.findField('KLCountry_id').getValue();
		params.KLRegion_id = form.findField('KLRegion_id').getValue();
		params.KLCity_id = form.findField('KLCity_id').getValue();
		params.KLSubRegion_id = form.findField('KLSubRegion_id').getValue()||null;
		params.LpuBuildingStreet_id = me.LpuBuildingStreet_id;
		if (form.findField('LpuBuildingStreet_IsAll').getValue()) {
			params.LpuBuildingStreet_HouseSet = null;
		}
		
		//на всякий случай заменяем англ H на русскую Н
		if(params.LpuBuildingStreet_HouseSet)
		{
			params.LpuBuildingStreet_HouseSet = params.LpuBuildingStreet_HouseSet.replace(new RegExp('H', 'g'), 'Н');
		}
			
		Ext.Ajax.request({
			params: params,
			url: C_LPUBUILDINGTERRITORYSERVICE_SAVE,
			callback: function(opt, success, response) {
				if ( success ) {
					me.hideLoadMask();
					Ext.getCmp('LpuBuildingTerritoryServiceGrid').store.reload()
					Ext.Msg.alert(INF_MSG, INF_SAVED_DATA, function(btn, text){
						if (btn == 'ok'){
							me.hide();
						}
					});
				}
				else {
					me.hideLoadMask();
					switch (action.failureType) {
						case Ext.form.Action.CLIENT_INVALID:
							Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG, function(){});
						break;
						case Ext.form.Action.CONNECT_FAILURE:
							Ext.Msg.alert(ERR_WND_TIT, ERR_CONNECT_FAILURE);
						break;
						case Ext.form.Action.SERVER_INVALID:
							Ext.Msg.alert(ERR_WND_TIT, action.result.Error_Msg + ' (код ' + action.result.Error_Code + ')');
						break;
					}
				}
			}.createDelegate(this)
			
		});
	},
	
	initComponent: function() {
		var me = this;

		this.addEvents(
			/**
			 * @event onChangeContentRelatedToTerritoryServiceAll
			 * Событие вызываемое при изменении связанных полей с полем LpuBuildingStreet_IsAll
			 */
			'onChangeContentRelatedToTerritoryServiceAll'
		);

		this.formPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			id: 'lpubuildingstreet_edit_window',
			labelWidth: 95,
			buttonAlign: 'left',
			bodyStyle:'padding: 5px',
			items: [
				{
					xtype: 'hidden',
					name: 'TerritoryService_id'
				},
				{
					xtype: 'hidden',
					name: 'LpuBuilding_id'
				},
				{
					xtype: 'fieldset',
					title: 'Справочник территорий',
					autoHeight: true,
					style: 'padding: 5px 0 0; margin-bottom: 5px;',
					layout: 'form',
					items: [
						{
							xtype: 'swklareastatcombo',
							hiddenName: 'KLAreaStat_id'
						},
						{
							xtype: 'swklcountrycombo',
							name: 'KLCountry_id',
							hiddenName: 'KLCountry_id',
							allowBlank: false,
							width: 300
						},
						{
							xtype: 'swregioncombo',
							name: 'KLRegion_id',
							allowBlank: false,
							hiddenName: 'KLRegion_id',
							width: 300,
							clearValue: function(){
								sw.Promed.SwRegionCombo.superclass.clearValue.apply(this, arguments);
								this.fireEvent('clearValue');
								me.fireEvent('onChangeContentRelatedToTerritoryServiceAll');
							}
						},
						{
							xtype: 'swsubrgncombo',
							name: 'KLSubRegion_id',
							hiddenName: 'KLSubRegion_id',
							width: 300
						},
						{
							xtype: 'swcitycombo',
							name: 'KLCity_id',
							hiddenName: 'KLCity_id',
							width: 300,
							onTrigger2Click: function() {
								
								var klsubrgnField = me.formPanel.getForm().findField('KLSubRegion_id'),
									klrgnField = me.formPanel.getForm().findField('KLRegion_id'),
									klcountryField = me.formPanel.getForm().findField('KLCountry_id'),
									klcityField = this,
									klareastatField = me.formPanel.getForm().findField('KLAreaStat_id'),
									
									klsubrgn_id = klsubrgnField.getValue(),
									klsubrgn_name = klsubrgnField.getRawValue(),
									klrgn_id = klrgnField.getValue(),
									klrgn_name = klrgnField.getRawValue();
								
								getWnd('swKLCitySearchWindow').show({
									onSelect: function(response_data) {
										
										klareastatField.getStore().clearFilter();
										
										//если нет города ставим район
										var klareastat = klareastatField.getStore().findBy(function(rec){

											return rec.get('KLCity_id') == response_data.KLCity_id;

										});

										if(klareastat == -1){
											klareastat = klareastatField.getStore().findBy(function(rec){

												return rec.get('KLSubRGN_id') == response_data.KLSubRegion_id;

											});

										}

										if(klareastat != -1){
											var klareastatrec = klareastatField.getStore().getAt(klareastat)
											klareastatField.setValue(klareastatrec.get('KLAreaStat_id'));
											me.onKLAreaStatIdChange();
										}

										klcountryField.setValue(response_data.KLCountry_id||null);
										klrgnField.setValue(response_data.KLRegion_id||null);
										klsubrgnField.setValue(response_data.KLSubRegion_id||null);
										klcityField.setValue(response_data.KLCity_id||null);
										me.formPanel.getForm().findField('KLStreet_id').reset();
										me.formPanel.getForm().findField('KLTown_id').reset();
										me.updateRelatedKLCombosStoreAferFormLoad();
										me.switchTerritoryServiceAll();
									}.createDelegate(this),
									params: {
										KLSubRegion_id: klsubrgn_id,
										KLSubRegion_Name: klsubrgn_name,
										KLRegion_id: klrgn_id,
										KLRegion_Name: klrgn_name
									}
								});
							}						
						},
						{
							xtype: 'swtowncombo',
							name: 'KLTown_id',
							hiddenName: 'KLTown_id',
							width: 300,
							onTrigger2Click: function() {
								
								var klsubrgnField = me.formPanel.getForm().findField('KLSubRegion_id'),
									klrgnField = me.formPanel.getForm().findField('KLRegion_id'),
									klcountryField = me.formPanel.getForm().findField('KLCountry_id'),
									klcityField = me.formPanel.getForm().findField('KLCity_id'),
									kltownField = me.formPanel.getForm().findField('KLTown_id'),
									klareastatField = me.formPanel.getForm().findField('KLAreaStat_id'),
									
									klcity_id = klcityField.getValue(),
									klcity_name = klcityField.getRawValue(),
									klsubrgn_id = klsubrgnField.getValue(),
									klsubrgn_name = klsubrgnField.getRawValue(),
									klrgn_id = klrgnField.getValue(),
									klrgn_name = klrgnField.getRawValue();									
								
								getWnd('swKLTownSearchWindow').show({
									onSelect: function(response_data) {
										klareastatField.getStore().clearFilter();
										
										//если нет пункта ставим район
										var klareastat = klareastatField.getStore().findBy(function(rec){
											if(
												(rec.get('KLTown_id') == response_data.KLTown_id)
											)
											{
												klareastatField.setValue(rec.get('KLAreaStat_id'));
												me.onKLAreaStatIdChange();
											}
										});
										
										if(klareastat == -1){
											klareastatField.getStore().findBy(function(rec){
												if(
													(rec.get('KLSubRGN_id') == response_data.KLSubRegion_id)
												)
												{
													klareastatField.setValue(rec.get('KLAreaStat_id'));
													me.onKLAreaStatIdChange();
												}
											})
										};
										
										klcountryField.setValue(response_data.KLCountry_id||null);
										klrgnField.setValue(response_data.KLRegion_id||null);
										klsubrgnField.setValue(response_data.KLSubRegion_id||null);
										klcityField.setValue(response_data.KLCity_id||null);
										kltownField.setValue(response_data.KLTown_id||null);
										me.formPanel.getForm().findField('KLStreet_id').reset();
										me.updateRelatedKLCombosStoreAferFormLoad();
										//me.onKLAreaStatIdChange();
									},
									params: {
										KLCity_id: klcity_id,
										KLSubRegion_id: klsubrgn_id,
										KLCity_Name: klcity_name,
										KLSubRegion_Name: klsubrgn_name,
										KLRegion_id: klrgn_id,
										KLRegion_Name: klrgn_name
									}
								});
							}
						},
						{
							xtype: 'swstreetcombo',
							name: 'KLStreet_id',
							hiddenName: 'KLStreet_id',
							width: 300
						}
					]
				},
				{
					xtype: 'checkbox',
					fieldLabel: lang['vsya_ukazannaya_territoriiya'],
					//name: 'LpuBuildingStreet_IsAll',
					name: 'LpuBuildingStreet_IsAll',
					disabled: true,
//					tabIndex: 1211,
					anchor: '100%',
					listeners: {
						check: function(checkbox, checked){
							if(checked)	me.formPanel.getForm().findField('LpuBuildingStreet_HouseSet').disable();
							else me.formPanel.getForm().findField('LpuBuildingStreet_HouseSet').enable();							
						}
					}
				},
				{
					//xtype: 'textfield',
					xtype: 'textfield',
					tabIndex: 1211,
					fieldLabel: lang['nomera_domov'],
					anchor: '100%',
					//name: 'LpuRegionStreet_HouseSet',
					name: 'LpuBuildingStreet_HouseSet',
					listeners: 
					{
						'change': function()
						{
							//
						},
						render: function(c){
						  Ext.QuickTips.register({
							target: c.getEl(),
							text:  'Пример: 1а/2,3-10,Ч(12-16),Н(15-29),100А'
							})
						}
					}
				}
				/*
				new sw.Promed.ViewFrame({				
					id: this.id + '_houses_view_frame',
					autoexpand: 'expand',
					border: true,
					store: new Ext.data.Store(),
					dataUrl: C_LPUBUILDINGTERRITORYSERVICE_GETGRID4EDIT,
					autoLoadData: false,
					height: 300,
					saveAtOnce: false,
					selectionModel: 'cell',
					stringfields: [
						{name: 'TerritoryService_id', type: 'int', key: true},
						{name: 'TerritoryServiceHouseRange_id', header: 'House Range ID', type: 'int', hidden: true},
						
						{name: 'TerritoryServiceHouseRange_OddEvenStr', header: 'Чётность', dataIndex: 'TerritoryServiceHouseRange_OddEvenStr', editor: new Ext.form.ComboBox({
								store: new Ext.data.JsonStore({
									fields: ['short', 'title'],
									data: [
										{short: 'Ч', title: 'Чётные'},
										{short: 'Н', title: 'Нечётные'}
									]
								}),
								displayField: 'title',
								valueField: 'short',
								value: '',
								typeAhead: true,
								mode: 'local',
								forceSelection: true,
								triggerAction: 'all',
								selectOnFocus: true
							})
						},
						{name: 'TerritoryServiceHouseRange_From', header: 'От', editor: new Ext.form.NumberField()},
						{name: 'TerritoryServiceHouseRange_To', header: 'До', editor: new Ext.form.NumberField()},
						
						{name: 'TerritoryServiceHouse_id', header: 'House ID', type: 'int', hidden: true},
						{name: 'TerritoryServiceHouse_Name', header: 'Номер', editor: new Ext.form.TextField()}
					],
					actions: [
						{ name: 'action_add', handler: function(){ this.findById(this.id + '_houses_view_frame').addEmptyRow(); }.createDelegate(this), hidden: false, disabled: true },
						{ name: 'action_edit', handler: function(){ this.findById(this.id + '_houses_view_frame').editSelectedCell(); }.createDelegate(this), disabled: true },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', handler: function(){ this.findById(this.id + '_houses_view_frame').deleteRow(); }.createDelegate(this), disabled: true },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print', disabled: true, hidden: true },
						{ name: 'action_save', disabled: true, hidden: true }
					],
					addEmptyRow: function(){
						var grid = this.getGrid(),
							store = grid.getStore();
						
						grid.stopEditing();
						store.insert(0, [new Ext.data.Record({
							TerritoryService_id: null,
							TerritoryServiceHouseRange_id: '',
							TerritoryServiceHouseRange_OddEvenStr: '',
							TerritoryServiceHouseRange_From: '',
							TerritoryServiceHouseRange_To: '',
							TerritoryServiceHouse_id: '',
							TerritoryServiceHouse_Name: ''
						})]);
						grid.startEditing(0, 2);					
					},
					
					deleteRecord: function(record){
						Ext.Ajax.request({
							params: {
								id: record.get('TerritoryService_id'),
								linkedTables: '',
								obj_isEvn: true,
								object: 'LpuBuildingTerritoryServiceRel',
								scheme: 'dbo'
							},
							url: '/?c=Utils&m=ObjectRecordDelete',
							callback: function(opt, success, response) {
								if ( success ) {Ext.getCmp('LpuBuildingTerritoryServiceGrid').store.reload();}
								else {}
							}.createDelegate(this)
							
						});
					},

					onCellSelect: function(sm, rowIdx, colIdx){
						var grid = this.getGrid();
						grid.getStore().loadData([], true);
						var record = grid.getSelectionModel().getSelected();
						this.getAction('action_edit').setDisabled( record.get('TerritoryService_id') === null );
						this.getAction('action_delete').setDisabled( record.get('TerritoryService_id') === null );
						
						// @todo Заблокировать ввода диапазона, если выбран дом у сохраненной (т.е. есть идентификатор) записи и наоборот
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
						if (!record) {
							Ext.Msg.alert(INF_MSG, lang['vyiberite_zapis_kotoruyu_hotite_udalit']);
							return false;
						}

						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: lang['udalit_zapis'],
							title: lang['udalenie_zapisi'],
							fn: function( buttonId ) {
								if ( buttonId != 'yes' ) {
									return false;
								}

								grid.getStore().remove(record);
								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
								this.deleteRecord(record);
							}.createDelegate(this)
						});
					}
				})
				*/
			],
			reader: new Ext.data.JsonReader({}, [
				{name: 'TerritoryService_id'},
				{name: 'KLCountry_id'},
				{name: 'KLRegion_id'},
				{name: 'KLSubRegion_id'},
				{name: 'KLCity_id'},
				{name: 'KLTown_id'},
				{name: 'KLStreet_id'},
				{name: 'LpuBuildingStreet_IsAll'},
				{name: 'LpuBuildingStreet_HouseSet'},
				/*
				{name: 'TerritoryServiceHouse_id'},
				{name: 'TerritoryServiceHouse_Name'},
				
				{name: 'TerritoryServiceHouseRange_id'},
				{name: 'TerritoryServiceHouseRange_OddEvenStr'},
				{name: 'TerritoryServiceHouseRange_From'},
				{name: 'TerritoryServiceHouseRange_To'}
				*/
			]),
			url: C_LPUBUILDINGTERRITORYSERVICE_SAVE,
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('swLpuBuildingStreetEditWindow').hide();
				},
				key: [ Ext.EventObject.J ],
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('swLpuBuildingStreetEditWindow').buttons[0].handler();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});

    	Ext.apply(this, {
			buttons: [
				{
					text: BTN_FRMSAVE,
//					tabIndex: 1214,
//					id: 'lrsOk',
					iconCls: 'ok16',
					handler: function(){
						me.submit();
					}
				},
				{
					text:'-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCANCEL,
//					tabIndex: 1215,
					iconCls: 'cancel16',
					handler: function(){
						me.hide();
						me.returnFunc(this.ownerCt.owner, -1);
					},
					onTabAction: function(){
//						this.findById('KLAreaStat_Combo').focus();
					},
					onShiftTabAction: function(){
//						Ext.getCmp('lrsOk').focus();
					}
				}
			],
 			items: [
				this.formPanel
			]
		});
		sw.Promed.swLpuBuildingStreetEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
