/**
 * swFindRegionsWindow - поисков участков и участковых врачей по адресу проживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @prefix       rmw
 * @tabindex     TABINDEX_FR
 * @version      July 2013
 */
 
/*NO PARSE JSON*/

sw.Promed.swFindRegionsWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swFindRegionsWindow',
	objectSrc: '/jscore/Forms/Reg/swFindRegionsWindow.js',
	
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_FRW,
	iconCls: 'workplace-mp16',
	id: 'swFindRegionsWindow',
	readOnly: false,

	onDirection: Ext.emptyFn,
	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},

	/**
	 * Панель фильтров
	 */
	Filters: null,
	
	/**
	 * Грид результатов
	 */
	RegionsList: null,
	

	/**
	 * Применить фильтр
	 */
	applyFilter: function(){
		
		var params = this.Filters.getFilters(); 
					
		this.RegionsList.loadData({
			globalFilters: params
		});
	},
	
	/**
	 * Получение ссылки на окно, на котором находится форма
	 * @return {Ext.Window}
	 */
	getOwnerWindow: function () {
		return this.ownerWindow
	},
	
	show: function()
	{
		Ext.Ajax.request({
			url: C_REG_GETCURLPUDATA,
			params: {},
			failure: function(response, options)
			{
				this.getLoadMask().hide();
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_podrazdeleniya_s_servera']);
			}.createDelegate(this),
			success: function(response, action)
			{

				this.getLoadMask().hide();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);

					// Загружаем адреса по территории обслуживания ЛПУ
					this.loadAllCombos(
						{
							'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
							'City': this.Filters.getForm().findField('KLCity_id'),
							'Town': this.Filters.getForm().findField('KLTown_id'),
							'Street': this.Filters.getForm().findField('KLStreet_id')
						},
						{
							'Rgn_id': answer[0].KLRgn_id,
							'SubRgn_id': answer[0].KLSubRgn_id,
							'City_id': answer[0].KLCity_id,
							'Town_id': answer[0].KLTown_id
						}
					);
				}
			}.createDelegate(this)
		});

		sw.Promed.swFindRegionsWindow.superclass.show.apply(this, arguments);
		if (arguments[0] && arguments[0].ARMType && arguments[0].ARMType == 'callcenter'){
			this.Filters.getForm().findField('KLStreet_id').doQuery = function (q, forceAll){
		        if(q === undefined || q === null){
		            q = '';
		        }
		        var qe = {
		            query: q,
		            forceAll: forceAll,
		            combo: this,
		            cancel:false
		        };
		        if(this.fireEvent('beforequery', qe)===false || qe.cancel){
		            return false;
		        }
		        q = qe.query;
		        forceAll = qe.forceAll;
		        if(forceAll === true || (q.length >= this.minChars)){
		            if(this.lastQuery !== q){
		                this.lastQuery = q;
		                if(this.mode == 'local'){
		                    this.selectedIndex = -1;
		                    if(forceAll){
		                        this.store.clearFilter();
		                    }else{
		                        //this.store.filter(this.displayField, q);
		                        var cnt = 0;
								this.getStore().filterBy(function(record, id) {
									var result = true;
									if(this.maxCount && this.maxCount!=null&&cnt>this.maxCount){
										return false;
									}

									if (result)
									{
										var patt = new RegExp(String(q).toLowerCase());
										result = patt.test(String(record.get(this.displayField)).toLowerCase());
									}
									if(result)cnt++;
									return result;
								}, this);
		                    }
		                    this.onLoad();
		                }else{
		                    this.store.baseParams[this.queryParam] = q;
		                    this.store.load({
		                        params: this.getParams(q)
		                    });
		                    this.expand();
		                }
		            }else{
		                this.selectedIndex = -1;
		                this.onLoad();
		            }
		        }
		    }
		}
	},
	
	/**
	 * Очистка комбобоксов во фрейме адреса
	 *
	 * @param {Integer} level Уровень, который мы очищаем
	 * @param {Object} combos Массив с комобобоксами
	}
	 */
	clearAddressCombo: function(
		level,
		combos
		) {
		var subregion_combo = combos['SubRegion'];
		var city_combo = combos['City'];
		var town_combo = combos['Town'];
		var street_combo = combos['Street'];

		var klarea_pid = 0;

		switch (level)
		{

			case 2:
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				if (street_combo) street_combo.clearValue();

				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				if (street_combo) street_combo.getStore().removeAll();

				this.loadAddressCombo(level, combos, klarea_pid, true);
			break;

			case 3:
				city_combo.clearValue();
				town_combo.clearValue();
				if (street_combo) street_combo.clearValue();

				town_combo.getStore().removeAll();
				if (street_combo) street_combo.getStore().removeAll();

				if (subregion_combo.getValue() != null)
				{
					klarea_pid = subregion_combo.getValue();
				}

				this.loadAddressCombo(level, combos, klarea_pid, true);
			break;

			case 4:
				town_combo.clearValue();
				if (street_combo) street_combo.clearValue();

				if (street_combo) street_combo.getStore().removeAll();

				if (city_combo.getValue() != null)
				{
					klarea_pid = city_combo.getValue();
				}
				else if (subregion_combo.getValue() != null)
				{
					klarea_pid = subregion_combo.getValue();
				}

				this.loadAddressCombo(level, combos, klarea_pid, true);
			break;
		}
	},

	/**
	 * Загрузка комбобокса в фрейме адреса
	 *
	 * @param {Integer} level Уровень, который мы загружаем
	 * @param {Object} combos Массив с комобобоксами
	 * @param {Integer} value Значение выбранного комбобокса
	 * @param {Boolean} recursion Загружаем рекурсивно?
	 */
	loadAddressCombo: function(level, combos, value, recursion) {

		var target_combo = null;

		switch (level)
		{

			case 1:
				target_combo = combos['SubRegion'];
			break;

			case 2:
				target_combo = combos['City'];
			break;

			case 3:
				target_combo = combos['Town'];
			break;

			case 4:
				target_combo = combos['Street'];
			break;

			default:
				return false;
			break;
		}
		if (target_combo == null)
			return;
		target_combo.clearValue();
		target_combo.getStore().removeAll();
		target_combo.getStore().load({
			params: {
				country_id: 0,
				level: level + 1,
				value: value
			},
			callback: function(store, records, options) {
				if (level >= 0 && level <= 3 && recursion == true)
				{
					this.loadAddressCombo(level + 1, combos, value, recursion);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Загрузка всех комбобоксов по территории обслуживания ЛПУ
	 *
	 */
	loadAllCombos: function(combos, ids) {

		combos['SubRegion'].getStore().load({
				callback: function() {
						combos['SubRegion'].setValue(ids['SubRgn_id']);
				},
				params: {
						country_id: 0,
						level: 2,
						value: ids['Rgn_id']
				}
		});

		if ( ids['SubRgn_id'] && ids['SubRgn_id'].toString().length > 0 ) {
			klarea_pid = ids['SubRgn_id'];
			level = 2;
		} else {
			klarea_pid = ids['Rgn_id'];
		}

		combos['City'].getStore().load({
				callback: function() {
						combos['City'].setValue(ids['City_id']);
				},
				params: {
						country_id: 0,
						level: 3,
						value: klarea_pid
				}
		});

		if ( ids['City_id'] && ids['City_id'].toString().length > 0 ) {
				klarea_pid = ids['City_id'];
				level = 3;
		}

		combos['Town'].getStore().load({
				callback: function() {
						combos['Town'].setValue(ids['Town_id']);
				},
				params: {
						country_id: 0,
						level: 4,
						value: klarea_pid
				}
		});

		if ( ids['Town_id'] && ids['Town_id'].toString().length > 0 ) {
				klarea_pid = ids['Town_id'];
				level = 4;
		}

		combos['Street'].getStore().load({
				params: {
						country_id: 0,
						level: 5,
						value: klarea_pid
				}
		});
	},
	
	initComponent: function()
	{
		
		this.Filters = new Ext.FormPanel(
		{
			region: 'north',
			border: false,
			frame: true,
			//defaults: {bodyStyle:'background:#DFE8F6;'},
			xtype: 'form',
			autoHeight: true,
			layout: 'column',
			//style: 'padding: 5px;',
			bbar:
			[
				{
					tabIndex: TABINDEX_FR+11,
					xtype: 'button',
					id: 'rmwBtnMPSearch',
					text: lang['nayti'],
					iconCls: 'search16',
					handler: function()
					{
						this.applyFilter();
					}.createDelegate(this)
				},
				{
					tabIndex: TABINDEX_FR+12,
					xtype: 'button',
					id: 'rmwBtnMPClear',
					text: lang['sbros'],
					iconCls: 'resetsearch16',
					handler: function()
					{
						// Очистка полей фильтра И перезагрузка
						this.Filters.clearFilters(true);
					}.createDelegate(this)
				},
				{
					xtype: 'tbseparator'
				}
			],
			items: [{
				layout: 'form',
				columnWidth: .5,
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
						codeField: 'KLAreaStat_Code',
						disabled: false,
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: lang['territoriya'],
						hiddenName: 'KLAreaStat_id',
						listeners: {
								'select': function(combo, record) {
										var newValue = record.get('KLAreaStat_id');
										var current_window = this.getOwnerWindow();
										var current_record = combo.getStore().getById(newValue);
										var form = this.Filters;

										var klareastat_combo = form.getForm().findField('KLAreaStat_id');
										var sub_region_combo = form.getForm().findField('KLSubRgn_id');
										var city_combo = form.getForm().findField('KLCity_id');
										var town_combo = form.getForm().findField('KLTown_id');
										var street_combo = form.getForm().findField('KLStreet_id');
										
										
										sub_region_combo.enable();
										city_combo.enable();
										town_combo.enable();
										street_combo.enable();
									
										this.clearAddressCombo(
												2,
												{
														'SubRegion': sub_region_combo,
														'City': city_combo,
														'Town': town_combo,
														'Street': street_combo
												}
										);

										if ( !current_record ) {
												return false;
										}
										
										this.loadAllCombos(
											{
												'SubRegion': sub_region_combo,
												'City': city_combo,
												'Town': town_combo,
												'Street': street_combo
											},
											{
												'Rgn_id': current_record.get('KLRGN_id'),
												'SubRgn_id': current_record.get('KLSubRGN_id'),
												'City_id': current_record.get('KLCity_id'),
												'Town_id': current_record.get('KLTown_id')
											}
										);
								}.createDelegate(this)
						},
						store: new Ext.db.AdapterStore({
								autoLoad: false,
								dbFile: 'Promed.db',
								fields: [
										{ name: 'KLAreaStat_id', type: 'int' },
										{ name: 'KLAreaStat_Code', type: 'int' },
										{ name: 'KLArea_Name', type: 'string' },
										{ name: 'KLCountry_id', type: 'int' },
										{ name: 'KLRGN_id', type: 'int' },
										{ name: 'KLSubRGN_id', type: 'int' },
										{ name: 'KLCity_id', type: 'int' },
										{ name: 'KLTown_id', type: 'int' }
								],
								key: 'KLAreaStat_id',
								sortInfo: {
										field: 'KLAreaStat_Code',
										direction: 'ASC'
								},
								tableName: 'KLAreaStat'
						}),
						tabIndex: TABINDEX_FR + 1,
						tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
								'</div></tpl>'
						),
						valueField: 'KLAreaStat_id',
						width: 300,
						xtype: 'swbaselocalcombo'
				}, {
						areaLevel: 2,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: lang['rayon'],
						hiddenName: 'KLSubRgn_id',
						listeners: {
								'change': function(combo, newValue, oldValue) {
										
										if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
												this.loadAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														},
														combo.getValue(),
														true
												);
										}
										else {
												this.clearAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														}
												);
										}
								}.createDelegate(this),
								'keydown': function(combo, e) {
										if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
												combo.fireEvent('change', combo, null, combo.getValue());
										}
								}.createDelegate(this),
								'keypress': function(combo, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										this.applyFilter();
									}
								}.createDelegate(this),
								'select': function(combo, record, index) {
										if ( record.get('KLArea_id') == combo.getValue() ) {
												combo.collapse();
												return false;
										}
										combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
										{ name: 'KLArea_id', type: 'int' },
										{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
										field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: TABINDEX_FR + 2,
						tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
				}, {
						areaLevel: 3,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: lang['gorod'],
						hiddenName: 'KLCity_id',
						listeners: {
								'change': function(combo, newValue, oldValue) {
										if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
												this.loadAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														},
														combo.getValue(),
														true
												);
										}
										else {
												this.clearAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														}
												);
										}
								}.createDelegate(this),
								'keydown': function(combo, e) {
										if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
												combo.fireEvent('change', combo, null, combo.getValue());
										}
								}.createDelegate(this),
								'keypress': function(combo, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										this.applyFilter();
									}
								}.createDelegate(this),
								'select': function(combo, record, index) {
										if ( record.get('KLArea_id') == combo.getValue() ) {
												combo.collapse();
												return false;
										}
										combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
										{ name: 'KLArea_id', type: 'int' },
										{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
										field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: TABINDEX_FR + 3,
						tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
				}, {
						areaLevel: 4,
						disabled: false,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: lang['naselennyiy_punkt'],
						hiddenName: 'KLTown_id',
						listeners: {
								'change': function(combo, newValue, oldValue) {
										if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
												this.loadAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														},
														combo.getValue(),
														true
												);
										}
										else {
												this.clearAddressCombo(
														combo.areaLevel,
														{
																'SubRegion': this.Filters.getForm().findField('KLSubRgn_id'),
																'City': this.Filters.getForm().findField('KLCity_id'),
																'Town': this.Filters.getForm().findField('KLTown_id'),
																'Street': this.Filters.getForm().findField('KLStreet_id')
														}
												);
										}
								}.createDelegate(this),
								'keydown': function(combo, e) {
										if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
												combo.fireEvent('change', combo, null, combo.getValue());
										}
								}.createDelegate(this),
								'keypress': function(combo, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										this.applyFilter();
									}
								}.createDelegate(this),
								'select': function(combo, record, index) {
										if ( record.get('KLArea_id') == combo.getValue() ) {
												combo.collapse();
												return false;
										}
										combo.fireEvent('change', combo, record.get('KLArea_id'));
								}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
										{ name: 'KLArea_id', type: 'int' },
										{ name: 'KLArea_Name', type: 'string' }
								],
								key: 'KLArea_id',
								sortInfo: {
										field: 'KLArea_Name'
								},
								url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: TABINDEX_FR + 4,
						tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLArea_id',
						width: 300,
						xtype: 'combo'
				}, {
						allowBlank: false,
						disabled: false,
						displayField: 'KLStreet_Name',
						enableKeyEvents: true,
						fieldLabel: lang['ulitsa'],
						hiddenName: 'KLStreet_id',
						listeners: {
							'keypress': function(combo, e) {
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									this.applyFilter();
								}
							}.createDelegate(this)
						},
						minChars: 0,
						mode: 'local',
						editable: true,
						queryDelay: 250,
						store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
										{ name: 'KLStreet_id', type: 'int' },
										{ name: 'KLStreet_Name', type: 'string' }
								],
								key: 'KLStreet_id',
								sortInfo: {
										field: 'KLStreet_Name'
								},
								url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: TABINDEX_FR + 5,
						tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLStreet_Name}',
								'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'KLStreet_id',
						width: 300,
						xtype: 'combo'
				}, {
					allowBlank: false,
					disabled: false,
					enableKeyEvents: true,
					fieldLabel: lang['dom'],
					listeners: {
						'keypress': function(field, e) {
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					},
					name: 'Address_House',
					tabIndex: TABINDEX_FR + 6,
					width: 100,
					xtype: 'textfield'
				}]
			}],
			
			/**
			 * Очистка фильтров с применением к спискам
			 */
			clearFilters: function(scheduleLoad)
			{
				this.Filters.getForm().reset();
				this.applyFilter();
				
			}.createDelegate(this),
			
			/**
			 * Получаем установленные фильтры
			 */
			getFilters: function(){
				return new Object({
					KLStreet_id: this.Filters.getForm().findField('KLStreet_id').getValue(),
					Address_House: this.Filters.getForm().findField('Address_House').getValue()
				});
			}.createDelegate(this)
		});
		
		this.RegionsList = new sw.Promed.ViewFrame(
		{
			id: 'frRegionsList',
			region: 'center',
			object: 'LpuUnit',
			border: true,
			dataUrl: C_REG_REGIONSLIST,
			toolbar: true,
			autoLoadData: false,
			isScrollToTopOnLoad: false,

			stringfields:
			[
				{name: 'LpuRegion_id', type: 'int', header: 'ID', key: true},
				{name: 'MedStaffFact_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuUnitType_SysNick', hidden: true, isparams: true},
				{name: 'Person_Fin', width: 250, header: lang['fio_vracha']},
				{name: 'LpuRegion_Name', width: 80, header: lang['№_uchastka']},
				{name: 'LpuRegionType_Name', width: 150, header: lang['tip_uchastka']},
				{name: 'LpuUnit_Name', width: 200, header: lang['podrazdelenie']},
				{name: 'LpuUnit_Address', id: 'autoexpand', header: lang['adres']},
				{name: 'Lpu_Nick', width: 250, header: lang['lpu']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.Filters,
				this.RegionsList
			],
			buttons: 
			[
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_FRW);
				}.createDelegate(this),
				tabIndex: TABINDEX_FR + 98
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		sw.Promed.swFindRegionsWindow.superclass.initComponent.apply(this, arguments);
	}
});