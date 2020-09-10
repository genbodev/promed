/*
Terms:
stateCombo - комбобокс в предустановленными значениями (не загружается Н�?КОГДА)
localCombo - обычний комбо, загружается при загрузке
loadAfter - загрузка ручками
localComboMongo - справочник монго (грузится в BaseForm)
dynamicCombo - динамическая загрузка (при редактировании)
commonSprCombo - поле с автоматической подстановкой значений комбобокс
 */

//комбобокс общего типа (он сам по себе)
//при инициализации надо указать:
//-fields(они же baseparams)
//-tableName()
//-triggerFind(кнопка поиска)
//-triggerClear(кнопка очистки)
//-translate(транслитерация на русский zpsr)
//-autoFilter(фильтр при наборе)
//-getSelectedRecord(метод получения выбранной записи)

Ext.define('sw.commonSprCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.commonSprCombo',
	cls: '',
	queryMode: 'local',
	triggerClear: true,
	triggerFind: false,
	autoFilter: true,
	enableKeyEvents: true,
	store: null,
	fields: null,
	translate: true,
	typeAheadDelay: 0,
	trigger1Cls:'clearTextfieldButton',
	//trigger1Cls:'x-form-clear-trigger',
	trigger2Cls:'x-form-arrow-trigger',
	bigFonts: false,
	searchFn: Ext.emptyFn(),	
	getSelectedRecord: function(){
		var cmb = this,
			selVal = cmb.getValue();

		if(selVal){
			var selRec = cmb.getStore().findRecord(cmb.valueField, selVal, 0, false, false, true);

			if(selRec) return selRec;
			else return false;
		}
		else{
			return false;
		}
	},
	setValueByCode: function(code){
		var me = this;

		if(me.codeField){
			var	rec = me.store.findRecord(me.codeField,code);

			if(rec) me.setValue(rec.get(me.valueField));
		}
	},
	filterFn: function(){
		return true;
	},
	initComponent: function() {
		var me = this,
			setValue = this.setValue;

		/*this.addEvents({
			autoSelect: true
		});

		*/

		if (me.bigStore) {
			me.bigStore.on('load',function() {
				var v = me.getValue()
				if (v && me.store && me.bigStore.findRecord(me.valueField,v)) {
					me.store.add(me.bigStore.findRecord(me.valueField,v));
					me.setValue(v);
				}
			});
		}

		//изменяем поиск на typeAhead
		//setValue c учетом BigStore
		if (me.typeAhead && me.displayField){
			Ext.override( me, {
					setValue: function(value, doSelect) {
						var me = this,
							valueNotFoundText = me.valueNotFoundText,
							inputEl = me.inputEl,
							i, len, record,
							dataObj,
							matchedRecords = [],
							displayTplData = [],
							processedValue = [];

						if (!me.store || me.store.loading) {
							// Called while the Store is loading. Ensure it is processed by the onLoad method.
							me.value = value;
							me.setHiddenValue(me.value);
							return me;
						}

						// This method processes multi-values, so ensure value is an array.
						value = Ext.Array.from(value);

						// Loop through values, matching each from the Store, and collecting matched records
						for (i = 0, len = value.length; i < len; i++) {
							record = value[i];
							if (!record || !record.isModel) {
								record = me.findRecordByValue(record);
							}

							if (me.bigStore && !record){
								record = me.bigStore.findRecord( me.valueField, value[i], 0, true);
								me.store.removeAll();
								me.store.add(me.bigStore.query( me.valueField, value[i], true, false, true).items);
							}
							// record found, select it.
							if (record) {
								matchedRecords.push(record);
								displayTplData.push(record.data);
								processedValue.push(record.get(me.valueField));
							}
							// record was not found, this could happen because
							// store is not loaded or they set a value not in the store
							else {
								// If we are allowing insertion of values not represented in the Store, then push the value and
								// create a fake record data object to push as a display value for use by the displayTpl
								if (!me.forceSelection) {
									processedValue.push(value[i]);
									dataObj = {};
									dataObj[me.displayField] = value[i];
									displayTplData.push(dataObj);
									// TODO: Add config to create new records on selection of a value that has no match in the Store
								}
								// Else, if valueNotFoundText is defined, display it, otherwise display nothing for this value
								else if (Ext.isDefined(valueNotFoundText)) {
									displayTplData.push(valueNotFoundText);
								}
							}
						}

						// Set the value of this field. If we are multiselecting, then that is an array.
						me.setHiddenValue(processedValue);
						me.value = me.multiSelect ? processedValue : processedValue[0];
						if (!Ext.isDefined(me.value)) {
							me.value = null;
						}
						me.displayTplData = displayTplData; //store for getDisplayValue method
						me.lastSelection = me.valueModels = matchedRecords;

						if (inputEl && me.emptyText && !Ext.isEmpty(value)) {
							inputEl.removeCls(me.emptyCls);
						}

						// Calculate raw value from the collection of Model data
						me.setRawValue(me.getDisplayValue());
						me.checkChange();

						if (doSelect !== false) {
							me.syncSelection();
						}
						me.applyEmptyText();

						return me;
					},
					getValue: function() {
						var me = this,
							picker = me.picker,
							rawValue = me.getRawValue(),
							value = me.value;

						if (me.getDisplayValue().replace(/\r?\n/g, "") !== rawValue) {
							value = rawValue;
							me.value = me.displayTplData = me.valueModels = null;
							if (picker) {
								me.ignoreSelection++;
								picker.getSelectionModel().deselectAll();
								me.ignoreSelection--;
							}
						}

						return value;
					},
					onTypeAhead: function(key) {
						var me = this,
							displayField = me.displayField,
							boundList = me.getPicker(), record,
							newValue, len, selStart, indexInputSrting,
							searchField = me.codeField || me.displayField;

						if(!me.getRawValue() || me.store.isLoading()) return;

						if (me.bigStore){
							record = me.bigStore.findRecord( searchField, me.getRawValue(), 0, true);

							me.store.clearFilter();
							me.store.removeAll();
							var trec = me.bigStore.query( searchField, me.getRawValue(), true, false, true).items;
							me.store.add(me.bigStore.query( searchField, me.getRawValue(), true, false, true).items);
						}
						else{

							if (me.autoFilter){
								me.store.clearFilter();

								if(me.store.findRecord( searchField, me.getRawValue(), 0, true, false) || !me.store.findRecord( me.valueField, me.getValue())){
									me.store.filter(searchField, new RegExp(me.getRawValue(), "i"));
								}

							}
							record = me.store.findRecord( searchField, me.getRawValue(), 0, true, false);
						}

						if(!record/* && me.getRawValue().length>2*/){
							boundList.clearHighlight();
							if (me.bigStore){
								record = me.bigStore.findRecord( displayField, me.getRawValue(), 0, true);
								me.store.removeAll();
								me.store.add(me.bigStore.query(displayField, me.getRawValue(), true, false, true).items);
							}
							else{

								if (me.autoFilter){
									me.store.clearFilter();
									if(!me.store.findRecord( me.valueField, me.getValue())){
										record = me.store.findRecord( displayField, me.getRawValue(), 0, true, false);
										me.store.filter(displayField, new RegExp(me.getRawValue(), "i"));
									}

								}
							}
						}

						if (record) {
							newValue = record.get(displayField);

							indexInputSrting = newValue.indexOf(me.getRawValue());

							if ( indexInputSrting == 0 ){
//							len = newValue.length;
//							selStart = me.getRawValue().length;
//
//							if (selStart !== 0 && selStart !== len) {
//								me.setRawValue(newValue);
//								me.selectText(selStart, len);
//							}
							}
							else{
								len = newValue.length;
								selStart = indexInputSrting+me.getRawValue().length;
							}
							boundList.highlightItem(boundList.getNode(record));
//						if (me.store.count()==1){
//							me.setValue(record.get(me.valueField), true);
//							this.fireEvent('autoSelect', me, record);
//						}
						}
						else{
							if(key == 'enter'){
								me.store.clearFilter();
								me.clearValue();
							}
						}

						me.timeoutFocus =
							Ext.defer(function() {
								me.focus();
							}.bind(me), 200);

						me.selectText(me.getRawValue().length, me.getRawValue().length);
					}
				}
			);

		}

		if (me.translate)
		{me.plugins = [new Ux.Translit(true, true)]}
		//от сюда
		me.on('keyup', function(c,e){
			if(e.getKey() && (e.getKey() == 13) && me.forceSelection){
				if(!me.value)me.onTypeAhead('enter');
			}
			clearTimeout( this.timeoutFocus );
			me.checkTriggerButton(true);
		})
		me.on('change', function(field,nV,oV){
			if(!nV && me.autoFilter){
				me.store.clearFilter();
				me.clearValue();
			}
			else
			{
				if (Ext.isString(nV) && Ext.isString(oV) && (nV.length < oV.length)) {
					me.onTypeAhead();
				}
			}
			clearTimeout( this.timeoutFocus );
		})
		me.on('focus', function(){
			me.checkTriggerButton(true);
		})
		me.on('blur', function(){
			clearTimeout( me.timeoutFocus );
			me.checkTriggerButton(false);
			me.isExpanded = false;
		})
		me.on('render', function(cmp, opts){

			//this.triggerCell.elements[0].hide();
			me.checkTriggerButton(false);
			cmp.mon(cmp.el, 'mouseover', function (event, html, eOpts) {
				me.checkTriggerButton(true);
			});
			cmp.mon(cmp.el, 'mouseleave', function (event, html, eOpts) {
				me.checkTriggerButton(false);
			});
			me.inputEl.on('focus', function(){
				if(!me.inputEl.hasCls('x-form-focus')) {
					me.inputEl.addCls('x-form-focus');
					me.inputEl.addCls('x-field-form-focus');
					me.inputEl.addCls('x-field-default-form-focus');
				}
			});

			me.inputEl.on('blur', function(){
				me.inputEl.removeCls( 'x-form-focus' );
				me.inputEl.removeCls( 'x-field-form-focus' );
				me.inputEl.removeCls( 'x-field-default-form-focus' );
			});
		})

		var clrButt = '',
			findButt = '',
			inputInnerRightPadding = 0;

		if (me.fields)
		{
			me.store = Ext.create ('Ext.data.Store', {
				fields: me.fields,

				sorters: me.sorters || {
					property: me.codeField || me.valueField,
					direction: 'ASC'
				},
				params: me.params ? me.params : null,
				url : '/?c=MongoDBWork&m=getData'
			})
		}

		if (!me.tpl){
			me.tpl = Ext.create('Ext.XTemplate',
				'<tpl for=".">' +
				'<div class="x-boundlist-item'+
				(me.bigFont ? ' enlarged-font' : '') + ' ">' +
				' {'+ me.displayField + '}'+
				'</div></tpl>')
		}

		if (!me.tpl && me.codeField) {
			me.tpl = Ext.create('Ext.XTemplate',
				'<tpl for=".">' +
				'<div class="x-boundlist-item'+
				(me.bigFont ? ' enlarged-font' : '') + ' ">' +
				'<font color="red">{'+me.codeField+'}</font> {'+ me.displayField + '}'+
				'</div></tpl>')
		}



		if(me.triggerFind)
		{
			me.trigger3Cls='x-form-search-trigger';
		}

		me.fieldSubTpl = new Ext.XTemplate(
			'<div class="{hiddenDataCls}" role="presentation"></div>',
			'<input id="{id}" type="{type}" role="{role}" {inputAttrTpl} class="{fieldCls} {typeCls} {editableCls}" autocomplete="off"',
			'<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
			'<tpl if="name"> name="{name}"</tpl>',
			'<tpl if="placeholder"> placeholder="{placeholder}"</tpl>',
			'<tpl if="size"> size="{size}"</tpl>',
			'<tpl if="maxLength !== undefined"> maxlength="{maxLength}"</tpl>',
			'<tpl if="readOnly"> readonly="readonly"</tpl>',
			'<tpl if="disabled"> disabled="disabled"</tpl>',
			'<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
			'<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
			'/>' + findButt + clrButt,
			{
				compiled: true,
				disableFormats: true
			}
		)

		me.listConfig = {
			resizable: true,
			cls: 'choose-bound-list-menu update-scroller',
		}

		me.fieldStyle =	{padding: '1px '+inputInnerRightPadding+'px 1px 3px'};

		me.callParent(arguments);

	},

	checkTriggerButton: function(display) {
		if (!this.readOnly && this.triggerClear) {
			if (this.getRawValue().length > 0) {
				if (display) {
					this.triggerCell.elements[0].show();
					this.triggerCell.elements[0].removeCls('hiddenTriggerWrap');
					this.triggerCell.elements[0].addCls('visibleTriggerWrap');
				} else {
					this.triggerCell.elements[0].hide();
					this.triggerCell.elements[0].addCls('hiddenTriggerWrap');
					this.triggerCell.elements[0].removeCls('visibleTriggerWrap');
				}
			} else {
				this.triggerCell.elements[0].hide();
				this.triggerCell.elements[0].addCls('hiddenTriggerWrap');
				this.triggerCell.elements[0].removeCls('visibleTriggerWrap');
			}
		}
		else{
			this.triggerCell.elements[0].hide();
			this.triggerCell.elements[0].addCls('hiddenTriggerWrap');
			this.triggerCell.elements[0].removeCls('visibleTriggerWrap');
		}
	},
	onTrigger1Click: function(e) {
		this.clearValue();
		this.store.clearFilter();
		this.focus();
	},
	onTrigger2Click: function(e) {
		if(!this.isExpanded && this.getPicker().getEl())
			this.getPicker().getEl().setStyle('visibility', 'visible');
		this.onTriggerClick.apply(this,arguments);
	},
	onTrigger3Click: function(e) {
		this.store.clearFilter();
		if (typeof this.searchFn == 'function') {
			this.searchFn.apply(this,arguments);
		}
		// @TODO: Поиск

	}
});

//это комбик динамической загрузки городов и нас пунктов и тп
Ext.define('sw.dCityCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.dCityCombo',
	plugins: [new Ux.Translit(true, true)],
	name: 'dCityCombo',
	cls: 'dynamicCombo',
	displayField: 'Town_Name',
	valueField:'Town_id',
	fieldLabel: 'Нас. пункт',
	labelAlign: 'right',

	queryMode: 'remote',
	minChars:2,
	hideTrigger:true,
	forceSelection:true,
	typeAhead:true,
	allowBlank: true,
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
			//'<span style="color:gray; font-size: 10px">{Socr_Nick} </span>'+
			'{Town_Name}'+
			'<span style="color:gray; font-size: 12px"> {Socr_Name}</span>'+
			'</br><span style="color:gray; font-size: 10px"> {Region_Name}</span>'+
			'<span style="color:gray; font-size: 10px"> {Region_Socr}</span>'+
			'</div></tpl>',
	listeners: {
		change: function( c, newV, oldV, o ){
			c.store.clearFilter()
		},
		beforeselect: function( c, record, index, eOpts )
		{
			c.store.regionFilter(record.get('Town_id'))
		}
	},

	initComponent: function() {
        var me = this;

        me.on('render', function(cmp, opts){
            me.inputEl.on('focus', function(){
                if(!me.inputEl.hasCls('x-form-focus')) {
                    me.inputEl.addCls('x-form-focus');
                    me.inputEl.addCls('x-field-form-focus');
                    me.inputEl.addCls('x-field-default-form-focus');
                }
            });
            me.inputEl.on('blur', function(){
            	if (me.store.loading && ((me.getValue() ? me.getValue().length: 0) > 0)){
					var streets = me.up('fieldset').down('[name=dStreetsCombo]');

					me.setRawValue();
					me.lastQuery = null;
					if (streets) {
						streets.setValue();
						streets.setRawValue();
						streets.lastValue = null;
					}
				}
                me.inputEl.removeCls( 'x-form-focus' );
                me.inputEl.removeCls( 'x-field-form-focus' );
                me.inputEl.removeCls( 'x-field-default-form-focus' );
            });
        });

		me.store = new Ext.data.JsonStore({
			autoLoad: false,
			fields: [
				{name: 'Town_Name', type:'string'},
				{name: 'Town_id', type:'int'},
				{name: 'Region_Socr', type:'string'},
				{name: 'Region_Name', type:'string'},
				{name: 'Region_Nick', type:'string'},
				{name: 'Socr_Name', type: 'string'},
				{name: 'Socr_Nick', type: 'string'},
				{name: 'Area_pid', type: 'int'},
				{name: 'KLAreaStat_id', type: 'int'},
				{name: 'KLAreaLevel_id', type: 'int'},
				{name: 'Region_id', type: 'int'},
				{name: 'UAD_id', type: 'int'}
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=Address4E&m=getCitiesFromName',
				reader: {
					type: 'json',
					successProperty: 'success',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				extraParams: {
					'region_id' : getGlobalOptions().region.number,
					'region_name' : getGlobalOptions().region.name
				}
			},
			filter: Ext.emptyFn,
			clearFilter: Ext.emptyFn,
			regionFilter: function (regionCode) {
				Ext.data.Store.prototype.clearFilter.call(this);
				Ext.data.Store.prototype.filter.call(this, 'Town_id', regionCode);
			}
		});
		me.displayTpl = me.initialConfig.displayTpl ? me.initialConfig.displayTpl : new Ext.XTemplate(
			'<tpl for=".">' +
				//'{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
			'{[(values.Town_Name && values.Socr_Nick != "") ? values.Socr_Nick + " " + values.Town_Name : values.Town_Name ]}',
			'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
		);
	me.callParent();
	}
})


//это комбик для динамической загрузки улиц и неформалов-адресов

Ext.define('sw.dStreetsCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.dStreetsCombo',
	plugins: [new Ux.Translit(true, true)],
	name: 'dStreetsCombo',
	cls: 'dynamicCombo',
	labelAlign: 'right',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'StreetAndUnformalizedAddressDirectory_id',    type:'string'},
			{name: 'UnformalizedAddressDirectory_id',    type:'int'},
			{name: 'StreetAndUnformalizedAddressDirectory_Name',  type:'string'},			
			{name: 'KLStreet_id',    type:'int'},
			{name: 'Socr_Nick',  type:'string'},
			{name: 'lat',  type:'string'},
			{name: 'lng',  type:'string'},
			{name: 'Lpu_id', type: 'int'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=Address4E&m=getStreetsFromName',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		},
		filter: Ext.emptyFn,
		clearFilter: Ext.emptyFn,
		streetFilter: function (streetCode) {
			Ext.data.Store.prototype.clearFilter.call(this);
			Ext.data.Store.prototype.filter.call(this, 'StreetAndUnformalizedAddressDirectory_id', streetCode);
		}
	}),

	queryMode: 'remote',
	minChars:1,
	hideTrigger:true,
	forceSelection:true,
	typeAhead:true,
	typeAheadDelay: 0,
	displayField:'StreetAndUnformalizedAddressDirectory_Name',
	valueField: 'StreetAndUnformalizedAddressDirectory_id',
	fieldLabel: 'Улица / Объект',
	allowBlank: false,
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
				'{StreetAndUnformalizedAddressDirectory_Name} <span style="color:gray">{Socr_Nick}</span>'+
		'</div></tpl>',

	listeners: {
		change: function( c, newV, oldV, o ){
			c.store.clearFilter()
		},
		beforeselect: function( c, record, index, eOpts )
		{	
			c.store.streetFilter(record.get('StreetAndUnformalizedAddressDirectory_id'))
		}
	},

	initComponent: function() {
        var me = this;
		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				//'{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
				'{[(values.StreetAndUnformalizedAddressDirectory_Name && values.Socr_Nick != "") ? values.Socr_Nick + " " + values.StreetAndUnformalizedAddressDirectory_Name : values.StreetAndUnformalizedAddressDirectory_Name ]}',
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );
		me.callParent();

	}
});


//поле для ввода с возможностью конвертации символов и с кнопкой удалить
//translate(bool) - параметр для конвертации
//помимо этого добавлен функционал typeAhead, с возможностью подключения store через storeId(store дб загружен)
Ext.define('sw.transFieldDelbut', {
	extend: 'Ext.form.field.Trigger', 
	alias: 'widget.transFieldDelbut',
	fieldLabel: 'label',
	cls: 'transFieldDelbut',
	hideTrigger:true,
	triggerClear: true,
	displayField: '',
	valueField: null,
	labelAlign: 'right',
	labelWidth: 60,
	translate: true,
	fieldStyle:	{padding: '1px 21px 1px 3px'},	
	triggerBaseCls: 'clearTextfieldButton',
	enableKeyEvents: true,
	autocompleteField: true,

	onTriggerClick: function() {
		this.setValue('');
    },
	checkTriggerButton: function(display){

		if(!this.readOnly)
		{
			if ((this.getRawValue().length > 0))
			{
				display?this.triggerEl.elements[0].show():this.triggerEl.elements[0].hide()
			}
			else{
				this.triggerEl.elements[0].hide()
			}
		}
	},
	initComponent: function() {
        var me = this,
			clrButt;
		
		me.addEvents({
			addFullText: true,
			triggerClick: true,
			changeTranslit: true
		});

		if (me.translate)
		{me.plugins = [new Ux.Translit(true, true)]}
		
		if(me.triggerClear)
		{
			clrButt = '<div style="position: absolute; height: 100%; display: table-cell; right: 6px; top: 3px; border: none;" >'
				+ '<div style="vertical-align: middle; display: none;" class="x-trigger-index-2 x-form-trigger clearTextfieldButton"></div>'
				+ '<div style="display: inline-block; vertical-align: middle; height: 100%; width: 0px;"></div>'
			+'</div>';
		}
		
		me.fieldSubTpl = new Ext.XTemplate(
		'<div class="{hiddenDataCls}" role="presentation"></div>',
		'<input id="{id}" type="{type}" role="{role}" {inputAttrTpl} class="{fieldCls} {typeCls} {editableCls}" autocomplete="off"',
			'<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
			'<tpl if="name"> name="{name}"</tpl>',
			'<tpl if="placeholder"> placeholder="{placeholder}"</tpl>',
			'<tpl if="size"> size="{size}"</tpl>',
			'<tpl if="maxLength !== undefined"> maxlength="{maxLength}"</tpl>',
			'<tpl if="readOnly"> readonly="readonly"</tpl>',
			'<tpl if="disabled"> disabled="disabled"</tpl>',
			'<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
			'<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
			'/>' + clrButt,
			{
				compiled: true,
				disableFormats: true
			}
		)
		
		me.on('keydown', function(c, e){
			me.checkTriggerButton(true);			
		})
		me.on('focus', function(){
			me.checkTriggerButton(true);
		})
		me.on('blur', function(){
			me.checkTriggerButton(false);
		})
		me.on('render', function(cmp, opts){
			cmp.mon(cmp.el, 'mouseover', function (event, html, eOpts) {
				me.checkTriggerButton(true);
			})
			cmp.mon(cmp.el, 'mouseleave', function (event, html, eOpts) {
				me.checkTriggerButton(false);
			})
		})
		Ext.applyIf(me)
		
		me.callParent(arguments)	
	}
})



/*это комбобокс динамической загрузки медикаментов
у него 3 триггера:
-всплывающая, 
-закрывающая 
-и еще одна всплывающая штучка-окно
 **/

Ext.define('sw.dDrugsCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.dDrugsCombo',
	//plugins: [new Ux.Translit(true, false)],
	queryMode: 'remote',
	minChars:2,
	//hideTrigger:true,
	forceSelection:true,
	typeAhead:true,
	valueField: 'DrugPrepFas_id',	
	allowBlank: false,	
	displayField: 'DrugPrep_Name',
	enableKeyEvents: true,
	fieldLabel: 'Медикамент',	
	name: 'dDrugsCombo',
	cls: 'dynamicCombo',
	labelAlign: 'right',
//	padding: '1 38 1 2',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'DrugPrep_id', type:'int'},
			{name: 'DrugPrep_Name', type:'string'},
			{name: 'DrugPrepFas_id', type:'int'}
		],
//		sorters: {
//			property: 'StreetAndUnformalizedAddressDirectory_Name'
//			,direction: 'ASC'
//		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			load: '',
			mode: 'income',
			//noCache:false,
			type: 'ajax',
			url: '/?c=Farmacy4E&m=loadDrugPrepList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		},
		filter: Ext.emptyFn
//		clearFilter: Ext.emptyFn,
//		streetFilter: function (streetCode) {
//			Ext.data.Store.prototype.clearFilter.call(this);
//			Ext.data.Store.prototype.filter.call(this, 'StreetAndUnformalizedAddressDirectory_id', streetCode);
//		}
	}),
	onTrigger2Click: function(e) {
		Ext.create('sw.tools.subtools.swDrugPrepWinSearch').show()
	},
	onTrigger3Click: function(e) {
		this.setValue('')
		this.store.removeAll()
	},
	
	checkTriggerButton: function(){
		if (typeof this.triggerEl!='undefined')
		{
			if (this.getRawValue().length > 0) 
			{
				if (!this.triggerEl.elements[1].isVisible())
				{this.triggerEl.elements[1].show(true)}
			}
			else{this.triggerEl.elements[1].hide(true)}
		}
	},	

	fieldSubTpl: [
		'<div class="{hiddenDataCls}" role="presentation"></div>',
		'<input id="{id}" type="{type}" role="{role}" {inputAttrTpl} class="{fieldCls} {typeCls} {editableCls}" autocomplete="off"',
			'<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
			'<tpl if="name"> name="{name}"</tpl>',
			'<tpl if="placeholder"> placeholder="{placeholder}"</tpl>',
			'<tpl if="size"> size="{size}"</tpl>',
			'<tpl if="maxLength !== undefined"> maxlength="{maxLength}"</tpl>',
			'<tpl if="readOnly"> readonly="readonly"</tpl>',
			'<tpl if="disabled"> disabled="disabled"</tpl>',
			'<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
			'<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
			'/><div style="position: absolute; right: -17px; top: 0;" class="x-trigger-index-1 x-form-trigger x-form-search-trigger" ></div>',
			'<div style="position: absolute; right: 21px; top: 3px; border: none;" class="x-trigger-index-2 x-form-trigger clearTextfieldButton" ></div>',
		{
			compiled: true,
			disableFormats: true
		}
	],
	
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
				'{DrugPrep_Name}'+
			'</div></tpl>',
		
	initComponent: function() {
        var me = this
		
		me.on('change', function(){
			me.checkTriggerButton()
		})
		me.on('render', function(){
			me.checkTriggerButton()
			me.store.getProxy().extraParams = {
				'mode' : 'income'
			}
		})
	
		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				 '{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );
	me.callParent();
	}
})


/*это комбик для упаковки медикамента*/

Ext.define('sw.dPacksDrugsCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.dPacksDrugsCombo',
	queryMode: 'local',
	minChars:2,
	forceSelection:true,
	typeAhead:true,
	valueField: 'Drug_id',	
	allowBlank: false,	
	displayField: 'Drug_Name',
	fieldLabel: 'Упаковка',	
	name: 'dPacksDrugsCombo',
	cls: 'dynamicCombo',
	labelAlign: 'right',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Drug_Fas', type:'string'},
			{name: 'DrugPrepFas_id', type:'int'},
			{name: 'Drug_id', type:'int'},
			{name: 'Drug_Code', type:'int'},
			{name: 'Drug_Name', type:'string'},
			{name: 'Drug_FullName', type:'string'},
			{name: 'DrugForm_Name', type:'string'},
			{name: 'DrugUnit_Name', type:'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '/?c=Farmacy4E&m=loadDrugList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
				'{Drug_Name}'+
			'</div></tpl>',
		
	initComponent: function() {
        var me = this

		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				 '{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );
	me.callParent();
	}
})



//это комбик лпу прикрепления

Ext.define('sw.lpuLocalCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.lpuLocalCombo',
	name: 'lpuLocalCombo',
	cls: 'localCombo',
	typeAhead: true,
	triggerClear: true,
	queryMode: 'local',
	bigFont: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Lpu_id', type:'int'},
			{name: 'Lpu_Name', type:'string'},
			{name: 'Lpu_Nick', type:'string'},
			{name: 'MedService_Nick', type:'string'},
			{name: 'LpuBuilding_id', type:'int'},
			{name: 'LpuBuildingType_id', type:'int'}
		],
		sorters: {
			property: 'Lpu_Nick',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=MedService4E&m=getLpusWithMedService',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			},
			extraParams: {
				'MedServiceType_id': 18
			}
		}
	}),
	displayField:'Lpu_Nick',
	valueField: 'Lpu_id',
	labelAlign: 'right',
	fieldLabel: 'НМП',
	
	initComponent: function() {
		var cmp = this;
		
		cmp.tpl = Ext.create('Ext.XTemplate', 
			'<tpl for=".">' +
			'<div class="x-boundlist-item' +
			(cmp.bigFont ? ' enlarged-font' : '') + ' ">' +
			'{[(values.MedService_Nick) ? values.MedService_Nick+" / ": "" ]}' +
			'{Lpu_Nick}' +
			'</div></tpl>'
		);
		
		cmp.displayTpl = '<tpl for=".">{[(values.MedService_Nick) ? values.MedService_Nick+" / ": "" ]}{Lpu_Nick}</tpl>';
	
		Ext.applyIf(cmp);
		cmp.callParent(arguments)
	}
});

//комбобокс отображения лпу к которым принадлежат подчиненные подстанции
//lockRemoteLpuBuilding - флаг если подстанция удаленная (подчиненная), то дизаблим комбобокс и устанавливаем значение текущей мо
Ext.define('sw.lpuWithNestedSmpUnitsCombo',{
	extend: 'sw.lpuLocalCombo',
	alias: 'widget.lpuWithNestedSmpUnitsCombo',
	lockRemoteLpuBuilding: false,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Lpu_id', type:'int'},
			{name: 'Lpu_Name', type:'string'},
			{name: 'Lpu_Nick', type:'string'},
			{name: 'MedService_Nick', type:'string'}
		],
		sorters: {
			property: 'Lpu_Nick',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadLpuWithNestedSmpUnits',
			reader: {
				type: 'json',
				successProperty: 'success',
				idProperty: 'Lpu_id',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			},
			extraParams: {				
				'Object': 'LpuWithMedServ', 				
				'MedServiceType_id': 19
			}
		}
	}),
	initComponent: function() {
		var cmp = this;
		
		//если подстанция удаленная (подчиненная), то дизаблим комбобокс и устанавливаем значение текущей мо
		if(cmp.lockRemoteLpuBuilding){
			
			var group_list = getGlobalOptions().groups.split('|');
			
			//если ползователь состоит в группе аудита записей, то комбик доступен
			if(Ext.Array.contains(group_list,'recordCallsAudit')){
				cmp.setDisabled(false);
			}
			else{
				Ext.Ajax.request({
					url: '/?c=CmpCallCard4E&m=getLpuBuildingOptions',
					callback: function(opt, success, response) {
						if (success){
							
							var res = Ext.JSON.decode(response.responseText);
							
							if(res[0] && res[0]['SmpUnitType_Code'] && res[0]['SmpUnitType_Code']==2){
								cmp.store.on('load', function(){
									cmp.setValue(parseInt(res[0]['Lpu_id']));
									cmp.setDisabled(true);
								})							
							}
						}
					}
				});
			}
		}
		
		cmp.callParent(arguments);		
	},
});

//это комбик выбора НМП

Ext.define('sw.selectNmpCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.selectNmpCombo',
	name: 'selectNmpCombo',
	cls: 'localCombo',
	typeAhead: true,
	queryMode: 'local',
	triggerClear: true,
	isClose: 0, // 0 -показать все, 1-открытые, 2-закрытые
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'MedService_id', type:'int'},
			{name: 'MedService_Nick', type:'string'},
			{name: 'Lpu_id', type:'int'},
			{name: 'Lpu_Nick', type:'string'},
			{name: 'NMP_Full_name', type:'string'}
		],
		sorters: [{
			property: 'NMP_Full_name',
			direction: 'ASC'
		}],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=MedService4E&m=loadNmpMedServiceList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	displayField:'NMP_Full_name',
	valueField: 'MedService_id',
	labelAlign: 'right',
	fieldLabel: 'НМП',
	tpl: [
		'<tpl for=".">',
		'<div class="x-boundlist-item">',
		'{MedService_Nick}',
		'</div></tpl>'
	],
	initComponent: function() {
        var npm = this;
		npm.on('render', function(){
			npm.checkTriggerButton()
			npm.store.getProxy().extraParams = {
				'isClose': npm.isClose
			}
		});

        npm.callParent(arguments)
    }
});

//это комбик выбора МО
Ext.define('sw.selectMO', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.selectMO',
	name: 'selectMO',
	cls: 'localCombo',
	typeAhead: true,
	queryMode: 'local',
	triggerClear: true,
	displayField:'name',
	valueField: 'id',
	//codeField: 'code',
	labelAlign: 'right',
	fieldLabel: 'МО госпитализации',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'id', type:'int'},
			{name: 'name', type:'string'},
			{name: 'code', type:'string'},
			{name: 'lpu_id', type:'int'}
		],
		sorters: [{
			property: 'code',
			direction: 'ASC'
		}],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=MedService4E&m=loadLpu',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),	
	/*tpl: [
		'<tpl for=".">',
		'<div class="x-boundlist-item">',
		'{name}',
		'</div></tpl>'
	],*/
	initComponent: function() {
		this.callParent(arguments)
    }
});
//это комбик выбора профиля

Ext.define('sw.selectSectionProfileByMO', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.selectSectionProfileByMO',
	name: 'selectSectionProfileByMO',
	cls: 'localCombo',
	editable: false,
	typeAhead: true,
	queryMode: 'local',
	triggerClear: true,
	displayField:'LpuSectionProfile_Name',
	valueField: 'LpuSectionProfile_id',
	labelAlign: 'right',
	fieldLabel: 'Профиль',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'LpuSectionProfile_id', type:'int'},
			{name: 'LpuSectionProfile_Name', type:'string'},
			{name: 'LpuSectionProfile_Code', type:'int'}
		],
		sorters: [{
			property: 'LpuSectionProfile_Name',
			direction: 'ASC'
		}],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=MedService4E&m=loadSectionProfileByMO',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	tpl: [
		'<tpl for=".">',
		'<div class="x-boundlist-item">',
		'{LpuSectionProfile_Name}',
		'</div></tpl>'
	],
	initComponent: function() {
		this.callParent(arguments)
	}
});

/*
	autoFilter: false,
	alias: 'widget.smpUnits',
	cls:'localCombo',
	fieldLabel: 'Подразделение СМП',
	typeAhead: true,
	forceSelection: 'false',
	triggerClear: true,
	translate: false,
	

,
	
	valueField: 'EmergencyTeam_id',
	labelAlign: 'right',
	fieldLabel: 'Бригада СМП',
//	disabled: true,
	tpl: '<tpl for=".">' +
		'<div class="x-boundlist-item">' +
		'<font color="red">{EmergencyTeam_Code}</font>' +' '+ '{EmergencyTeam_Name}'+
		'</div></tpl>'
*/

//это комбик бригады смп

Ext.define('sw.smpAmbulanceTeamCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.smpAmbulanceTeamCombo',
	name: 'smpAmbulanceTeamCombo',
	cls: 'localCombo',
	queryMode: 'local',
	displayField:'EmergencyTeam_Name',
	valueField: 'EmergencyTeam_id',
	codeField: 'EmergencyTeam_Code',
	fieldLabel: 'Бригада СМП',
	typeAhead: true,
	forceSelection: 'false',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'EmergencyTeam_id', type:'int'},
			{name: 'EmergencyTeam_Code', type:'int'},
			{name: 'EmergencyTeam_Name', type:'string'}
		],
		sorters: {
			property: 'EmergencyTeam_Code',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamCombo',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
});


//это комбик занятых работой бригад смп

Ext.define('sw.smpDutyAmbulanceTeamCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.smpDutyAmbulanceTeamCombo',
	name: 'smpDutyAmbulanceTeamCombo',
	cls: 'localCombo',
	queryMode: 'local',
	displayField:'Person_Fin',
	valueField: 'EmergencyTeam_id',
	codeField: 'EmergencyTeam_Num',
	fieldLabel: 'Бригада СМП',
	typeAhead: true,
	forceSelection: false,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'EmergencyTeam_id', type:'int'},
			{name: 'EmergencyTeam_Num', type:'string'},
			{name: 'Person_Fin', type:'string'}
		],
		sorters: {
			property: 'EmergencyTeam_Num',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnv',
			//url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamCombo',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
})

Ext.define('sw.EmergencyTeamWithWialonCombo', {
	extend: 'sw.smpAmbulanceTeamCombo', 
	alias: 'widget.emergencyteamwithwialoncombo',
	name: 'EmergencyTeam_id',
	cls: 'localCombo',
	getWialonID: function() {
		var val = this.getValue(),
			valueField = this.valueField,
			store = this.getStore();
		
		if (!val || !valueField || !store) {
			return false;
		}
		
		var selRec = store.findRecord(valueField,val);
		
		return (selRec) ? selRec.get( 'WialonID' ) : false;
		
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'EmergencyTeam_id', type:'int'},
			{name: 'EmergencyTeam_Code', type:'int'},
			{name: 'EmergencyTeam_Name', type:'string'},
			{name: 'WialonID', type:'string'}
		],
		sorters: {
			property: 'EmergencyTeam_Code',
			direction: 'ASC'
		},
		proxy: {
			type: 'ajax',
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamComboWithWialonID',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			}
		}
	})
})

Ext.define('sw.MedPersonalCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.swmedpersonalcombo',
	name: 'swmedpersonalcombo',
	cls: 'localCombo',
	queryMode: 'local',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'MedPersonal_id', type:'int'},
			{name: 'MedPersonal_Code', type:'int'},
			{name: 'MedPersonal_Fio', type:'string'},
			{name: 'Person_Fin', type:'string'}
		],
		sorters: {
			property: 'MedPersonal_Fio',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=MedService4E&m=loadMedServiceMedPersonalList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	displayField:'MedPersonal_Fio',
	valueField: 'MedPersonal_id',
	labelAlign: 'right',
	fieldLabel: 'Врач'
//	,
//	tpl: '<tpl for=".">' +
//	'<div class="x-boundlist-item">' +
//	'{MedPersonal_Fio}' +
//	'</div></tpl>'
})

Ext.define('sw.MedPersonalExpertsCombo', {
	extend: 'sw.MedPersonalCombo',
	alias: 'widget.swMedPersonalExpertsCombo',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'MedPersonal_id', type:'int'},
			{name: 'MedPersonal_Code', type:'int'},
			{name: 'MedPersonal_Fio', type:'string'}
		],
		sorters: {
			property: 'MedPersonal_Fio',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: C_MEDSERVICE4E_MP_ER_LIST,
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
});


//***
//производные от commonSprCombo
//***

//профиль бригады СМП

Ext.define('swEmergencyTeamSpecCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swEmergencyTeamSpecCombo',
	name: 'swEmergencyTeamSpecCombo',
	//
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'EmergencyTeamSpec',
	displayField:'EmergencyTeamSpec_Name',
	valueField: 'EmergencyTeamSpec_id',
	codeField: 'EmergencyTeamSpec_Code',
	triggerClear: true,
	editable: false,
	activeProfiles: true,
	initComponent: function() {
		var cmp = this,
			now = Ext.Date.format(new Date(), "Y-m-d");
		
		if(cmp.activeProfiles){
			cmp.params =  {
			  where: " where ("
				+ " (  EmergencyTeamSpec_begDT is null OR  ( EmergencyTeamSpec_begDT < '" + now + "' )  ) "
				+ " AND "
				+ " (  EmergencyTeamSpec_endDT is null OR  ( EmergencyTeamSpec_endDT > '" + now + "' )  ) "
			  + ")"
			};
		};
		
		Ext.applyIf(cmp);
		cmp.callParent(arguments);		
	},
	
	fields: [
		{name: 'EmergencyTeamSpec_id', type:'int'},
		{name: 'EmergencyTeamSpec_Name', type:'string'},
		{name: 'EmergencyTeamSpec_Code', type:'string'}
	],
	//
	fieldLabel: 'Профиль'	
})

//Состав бригады (старший бригады, помощники и водитель)

Ext.define('swEmergencyFIOCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swEmergencyFIOCombo',
	//
	cls: 'loadAfter',
	displayField: 'MedPersonal_Fio',
	valueField: 'MedPersonal_id',
	trigger2Cls: 'x-form-search-trigger',
	triggerClear: true,
	editable: false,
	typeAhead: true,
	tpl: new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item">',
		'<p style="line-height: 0;"><b>{MedPersonal_Fio}</b></p><p style="line-height: 0;">{LpuBuilding_Name}</p><p style="line-height: 0;">{PostMed_Name} ст. {MedStaffFact_Stavka} <p style="line-height: 0;font-style:italic;">Дата начала работы: {WorkData_begDate}</p>'+
		'</div></tpl>'
	)
});



Ext.define('swFindBSMEDCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swFindBSMEDCombo',
	cls: 'loadAfter',
	translate: false,
	displayField: 'val',
	valueField: 'val',
	triggerClear: true,
	//triggerFind: true,
	editable: true,
	directions: [],
	store: new Ext.data.Store({
		fields: ['val', 'dir', 'fieldName'],
		data : [
			// {"val":"абаба", "dir":"Alabama"},
			// {"val":"вавава", "dir":"Alaska"},
			// {"val":"бабаба", "dir":"Arizona"}
		]
	}),
	tpl: new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item">',
		'{val} <span style="color:gray">в {dir}</span>'+
		'</div></tpl>'
	)
})


//Тип организации

Ext.define('swOrgTypeCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOrgTypeCombo',
	name: 'swOrgTypeCombo',
	cls: 'localComboMongo',
	tableName: 'OrgType',
	displayField: 'OrgType_Name',
	valueField: 'OrgType_id',
	codeField: 'OrgType_Code',
	xtype: 'commonSprCombo',
	triggerClear: true,
	fields: [
		{name: 'OrgType_id', type:'int'},
		{name: 'OrgType_Name', type:'string'},
		{name: 'OrgType_Code', type:'string'}
	],
	editable: false
})


//ОКФС

Ext.define('swOkfsCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOkfsCombo',
	name: 'swOkfsCombo',
	cls: 'localComboMongo',
	tableName: 'Okfs',
	displayField: 'Okfs_Name',
	valueField: 'Okfs_id',
	codeField: 'Okfs_Code',
	triggerClear: true,
	fields: [
		{name: 'Okfs_id', type:'int'},
		{name: 'Okfs_Name', type:'string'},
		{name: 'Okfs_Code', type:'string'}
	],
	editable: false
})

//ОКОПФ

Ext.define('swOkopfCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOkopfCombo',
	name: 'swOkopfCombo',
	cls: 'localComboMongo',
	tableName: 'Okopf',
	displayField: 'Okopf_Name',
	valueField: 'Okopf_id',
	codeField: 'Okopf_Code',
	triggerClear: true,
	fields: [
		{name: 'Okopf_id', type:'int'},
		{name: 'Okopf_Name', type:'string'},
		{name: 'Okopf_Code', type:'string'}
	],
	editable: false
})

//ОКВЭД

Ext.define('swOkvedCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOkvedCombo',
	name: 'swOkvedCombo',
	cls: 'localComboMongo',
	tableName: 'Okved',
	displayField: 'Okved_Name',
	valueField: 'Okved_id',
	codeField: 'Okved_Code',
	triggerClear: true,
	fields: [
		{name: 'Okved_id', type:'int'},
		{name: 'Okved_Name', type:'string'},
		{name: 'Okved_Code', type:'string'}
	],
	editable: false
})



//марка горючего
Ext.define('swWaybillGasCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swWaybillGasCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	fieldLabel: 'Марка горючего',
	labelAlign: 'right',
	triggerClear: true,
	tableName: 'WaybillGas',
	valueField: 'WaybillGas_id',
	displayField: 'WaybillGas_Name',
	codeField: 'WaybillGas_Code',
	fields: [
		{name: 'WaybillGas_id', type:'int'},
		{name: 'WaybillGas_Code', type:'int'},
		{name: 'WaybillGas_Name', type:'string'}
	]
});

//тестовый тип вызова

Ext.define('swCmpCallTypeCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpCallTypeCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	autoFilter: false,
	fieldLabel: 'Тип вызова (F7-повт.)',
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	tableName: 'CmpCallType',
	valueField: 'CmpCallType_id',
	name: 'CmpCallType_id',
	displayField: 'CmpCallType_Name',
	codeField: 'CmpCallType_Code',
	fields: [
		{name: 'CmpCallType_id', type:'int'},
		{name: 'CmpCallType_Name', type:'string'},
		{name: 'CmpCallType_Code', type:'int'}
	],
	displayTpl: '<tpl for=".">{CmpCallType_Code}. {CmpCallType_Name}</tpl>',
	initComponent: function() {
		var cmp = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc']);

		if(!isNmpArm){
			cmp.params = {where: "where CmpCallType_Code != 19"};
		}

		Ext.applyIf(cmp);
		cmp.callParent(arguments)
	}
});

Ext.define('swCmpCallCard112StatusTypeCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpCallCard112StatusTypeCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	autoFilter: false,
	fieldLabel: 'Статус карточки',
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	tableName: 'CmpCallCard112StatusType',
	valueField: 'CmpCallCard112StatusType_id',
	name: 'CmpCallCard112StatusType_id',
	displayField: 'CmpCallCard112StatusType_Name',
	codeField: 'CmpCallCard112StatusType_Code',
	fields: [
		{name: 'CmpCallCard112StatusType_id', type:'int'},
		{name: 'CmpCallCard112StatusType_Name', type:'string'},
		{name: 'CmpCallCard112StatusType_Code', type:'int'}
	],
	displayTpl: '<tpl for="."> {CmpCallCard112StatusType_Code}. {CmpCallCard112StatusType_Name} </tpl>'
});

//это комбик для пола
Ext.define('sw.sexCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.sexCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	autoFilter: false,
	forceSelection: true,
	tableName: 'Sex',
	translate: false,
	name: 'sexCombo',
	displayField: 'Sex_Name',
	valueField: 'Sex_id',
	codeField: 'Sex_Code',
	//editable: true,	
	fieldLabel: 'Пол',
	fields: [
		{name: 'Sex_id', type:'int'},
		{name: 'Sex_Name', type:'string'},
		{name: 'Sex_Code', type:'string'}
	]
});


//повод

Ext.define('sw.cmpReasonCombo',{
	extend: 'sw.commonSprCombo',
	//plugins: [new Ux.Translit(true, true)],
	alias: 'widget.cmpReasonCombo',
	cls: 'localComboMongo',
	typeAhead: true,
//	typeAheadDelay: 300,
	fieldLabel: 'Повод',
	labelAlign: 'right',
	triggerClear: true,	
	translate: false,
	tableName: 'CmpReason',
	valueField: 'CmpReason_id',
	name: 'CmpReason_id',
	codeField: 'CmpReason_Code',
	displayField: 'CmpReason_Name',
	//displayTpl: '<tpl for="."><tpl if="display">{CmpReason_Code}. {CmpReason_Name}</tpl></tpl>',
	fields: [
		{name: 'CmpReason_id', type:'int'},
		{name: 'CmpReason_Name', type:'string'},
		{name: 'CmpReason_Code', type:'string'},
		{name: 'CmpReason_isCmp', type:'string'},
		{name: 'display', type:'boolean', defaultValue: true}
	],
	onTrigger1Click: function(e) {
		this.focus();
		this.clearValue();
		this.store.clearFilter();		
	},
	initComponent: function() {
		var curArm = '';
		if(sw.Promed.MedStaffFactByUser.current){
			curArm =  sw.Promed.MedStaffFactByUser.current.ARMType
		}
		if(!curArm && sw.Promed.MedStaffFactByUser.last){
			curArm = sw.Promed.MedStaffFactByUser.last.ARMType;
		}
		var cmp = this,
			isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc']),
			now = Ext.Date.format(new Date(), "Y-m-d");

			cmp.params =  {
				where: " where ("
				+ " (  CmpReason_begDate is null OR  ( CmpReason_begDate < '" + now + "' )  ) "
				+ " AND "
				+ " (  CmpReason_endDate is null OR  ( CmpReason_endDate > '" + now + "' )  ) "
				+ ")"
			};

		cmp.tpl = Ext.create('Ext.XTemplate', 
			'<tpl for="."><tpl if="display">' +
			'<div class="x-boundlist-item'+
			(cmp.bigFont ? ' enlarged-font' : '') + ' ">' +
			'<font color="red">{'+cmp.codeField+'}</font> {'+ cmp.displayField + '}'+
			'</div></tpl>'+
			'<tpl if="display == false"><div class="x-boundlist-item"></div></tpl>'+
			'</tpl>'
		);
		cmp.displayTpl = new Ext.XTemplate(
			'<tpl for="."><tpl if="display">{[ this.getdisplayFieldTrim(values) ]}</tpl></tpl>',
			{
				getdisplayFieldTrim: function(val){
					var res = val.CmpReason_Code + '. ' +val.CmpReason_Name.trim();
					return res;
				}
			}
		);

		if(!isNmpArm){
			cmp.params.where += " and CmpReason_isCmp = '2'";
		}

		Ext.applyIf(cmp);
		cmp.callParent(arguments)

		//Удалим поводы с одинаковым кодом
		cmp.store.on('load', function(q,w,e,r){
			cmp.store.each(function(rec){
				var finded = -1;
				if(rec && rec.get('CmpReason_isCmp') == '1'){
					finded = cmp.store.findBy(function(r){
						return (r.get('CmpReason_Code') == rec.get('CmpReason_Code')) && (r.get('CmpReason_isCmp') != rec.get('CmpReason_isCmp'))
					}, cmp.store)

					if(finded != -1){
						cmp.store.removeAt(finded)
					}
				}

			})
		})
	}
});




//тестовый улиц

Ext.define('sw.streetsSpeedCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swStreetsSpeedCombo',
	cls: 'loadAfter',
	triggerClear: true,
	editable: true,
	minChars: 2,
	hideTrigger:true,
	forceSelection: true,
	typeAhead:true,
	typeAheadDelay: 0,
	displayField:'StreetSearch_Name',
	valueField: 'StreetAndUnformalizedAddressDirectory_id',
	fieldLabel: 'Улица / Объект',
	queryMode: 'local',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="enlarged-font x-boundlist-item">'+
		'{[ this.addressObj(values) ]}'+
		'</div></tpl>',
		{
			addressObj: function(val){
				//var city = ( getRegionNick().inlist(['perm', 'ekb', 'ufa']) ) ? val.Address_Name+' ' : '';
				var city = val.Address_Name+' ';

				if(val.UnformalizedAddressDirectory_id){
					var nameUnformalizedStreet = '';

					nameUnformalizedStreet += val.AddressOfTheObject ? val.AddressOfTheObject + ', ' : '';
					nameUnformalizedStreet += val.StreetAndUnformalizedAddressDirectory_Name ? val.StreetAndUnformalizedAddressDirectory_Name : '';

					return nameUnformalizedStreet;
				}else{
					return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
					//return city + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
				}
			}
		}
	),
	displayTpl: new Ext.XTemplate(
		'<tpl for=".">' +
		'{[ this.getDateFinish(values) ]}',
		'</tpl>',
		{
			getDateFinish: function(val){
				if (val.UnformalizedAddressDirectory_id){
					var nameUnformalizedStreet = '';

					nameUnformalizedStreet += val.AddressOfTheObject ? val.AddressOfTheObject + ', ' : '';
					nameUnformalizedStreet += val.StreetAndUnformalizedAddressDirectory_Name ? val.StreetAndUnformalizedAddressDirectory_Name : '';

					return nameUnformalizedStreet;
				}
				else{
					return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
				}
			}
		}
	),
	listeners: {
		'keydown': function(inp, e) {
			if ( e.shiftKey == true && e.getKey() == e.HOME) {
				this.inKeyMode = true;
				this.selectText();
			}
		},																
		'change': function (c, newValue, oldValue, eOpts){		
			if (oldValue != null){
				var store = this.store;
				store.clearFilter();
				store.filter('StreetSearch_Name', oldValue.replace('Ё', 'Е').replace('ё', 'е'));
			}	
		}
	},
	initComponent: function() {
        var me = this;

		me.store = new Ext.data.Store({
			fields: [
				{name: 'StreetAndUnformalizedAddressDirectory_id', type:'string'},
				{name: 'UnformalizedAddressDirectory_id', type:'int'},
				{name: 'UnformalizedAddressType_id', type:'int'},
				{name: 'StreetAndUnformalizedAddressDirectory_Name', type:'string'},
				{name: 'KLStreet_id', type:'int'},
				{name: 'Address_Name', type:'string'},
				{name: 'Socr_Nick', type:'string'},
				{name: 'lat', type:'string'},
				{name: 'lng', type:'string'},
				{name: 'Lpu_id', type: 'int'},
				{name: 'KLTown_id', type: 'int'},
				{name: 'KLSubRGN_id', type: 'int'},
				{name: 'KLTown_id', type: 'int'},
				{name: 'LpuBuilding_id', type: 'int'},
				{name: 'AddressOfTheObject', type:'string'},
				{name: 'UnformalizedAddressDirectory_StreetDom', type:'string'},
				{name: 'StreetSearch_Name', type:'string'}	
			]
		});

		if(!me.displayTpl) {
			me.displayTpl = new Ext.XTemplate(
				'<tpl for=".">' +
					'{[ this.getDateFinish(values) ]} ',
					'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
				'</tpl>',
				{
					getDateFinish: function(val){
						if (val.UnformalizedAddressDirectory_id){
							return val.StreetAndUnformalizedAddressDirectory_Name + " " + val.Socr_Nick;
						}
						else{
							return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
						}
					}
				}	
	        );
		}
	
		me.bigStore = new Ext.data.JsonStore({
			autoLoad: false,
			fields: [
				{name: 'StreetAndUnformalizedAddressDirectory_id',    type:'string'},
				{name: 'UnformalizedAddressDirectory_id',    type:'int'},
				{name: 'UnformalizedAddressType_id', type:'int'},
				{name: 'StreetAndUnformalizedAddressDirectory_Name',  type:'string'},
				{name: 'Address_Name',  type:'string'},
				{name: 'KLStreet_id',    type:'int'},
				{name: 'Socr_Nick',  type:'string'},
				{name: 'lat',  type:'string'},
				{name: 'lng',  type:'string'},
				{name: 'Lpu_id', type: 'int'},
				{name: 'KLTown_id', type: 'int'},
				{name: 'KLSubRGN_id', type: 'int'},
				{name: 'KLTown_id', type: 'int'},
				{name: 'LpuBuilding_id', type: 'int'},
				{name: 'AddressOfTheObject', type:'string'},
				{name: 'UnformalizedAddressDirectory_StreetDom', type:'string'},
				{name: 'StreetSearch_Name', type:'string'}	
			],
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=Address4E&m=getAllStreetsFromCity',
				reader: {
					type: 'json',
					successProperty: 'success',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		})

		me.callParent();		
	}
})

Ext.define('sw.EmergencyTeamWialonCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swEmergencyTeamWialonCombo',
	cls: 'localCombo',
	triggerClear: true,
	editable: true,
	translate: false,
	hideTrigger:false,
	minChars: 2,
	typeAhead:true,
	typeAheadDelay: 0,
	displayField:'nm',
	valueField: 'id',
	fieldLabel: 'Объекты Wialon',
	queryMode: 'local',
	store: new Ext.data.Store({
		fields: [
			{name:'id', type:'int'},
			{name:'nm', type:'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '?c=Wialon&m=getAllAvlUnitsForMerge',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
})
//Комбобокс для выбора идентификатора траспортного средства службы глежения GPS-ГЛОНАСС ТНЦ для Башкирии
Ext.define('sw.EmergencyTeamTNCCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swEmergencyTeamTNCCombo',
	cls: 'localCombo',
	triggerClear: true,
	editable: true,
	translate: false,
	hideTrigger:false,
	minChars: 2,
	typeAhead:true,
	typeAheadDelay: 0,
	displayField:'name',
	valueField: 'id',
	fieldLabel: 'Список ТС (ТНЦ)',
	queryMode: 'local',
	store: new Ext.data.Store({
		fields: [
			{name:'id', type:'int'},
			{name:'name', type:'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '?c=TNC&m=getTransportList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
})


Ext.define('sw.TypeOfUnformalizedAddress',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swTypeOfUnformalizedAddress',
	name: 'UnformalizedAddressTypeCombo',
	displayField:'UnformalizedAddressType_Name',
	valueField: 'UnformalizedAddressType_id',
	cls:'localCombo',
	store: new Ext.data.JsonStore({
		autoLoad: true,
		storeId: 'unformalizedAdressTypeStore',
		fields: [
			{name: 'UnformalizedAddressType_id', type: 'int'},
			{name: 'UnformalizedAddressType_Name', type: 'string'},					
			{name: 'UnformalizedAddressType_SocrNick', type: 'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadUnformalizedAddressType',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			},
			sorters: {
				property: 'UnformalizedAddressType_id',
				direction: 'ASC'
			}
		}
	})
})

Ext.define('sw.SmpUnits',{
	extend: 'sw.commonSprCombo',
	displayField:'LpuBuilding_Name',
	codeField: 'LpuBuilding_Code',	
	valueField: 'LpuBuilding_id',
	name: 'LpuBuilding_id',
	autoFilter: false,
	alias: 'widget.smpUnits',
	cls:'localCombo',
	fieldLabel: 'Подразделение СМП',
	typeAhead: true,
	forceSelection: 'false',
	triggerClear: true,
	translate: false,
	loadSelectSmp: false,
	setCurrentLpuBuilding: function(){
		var combo = this;
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getLpuBuildingBySessionData',			
			callback: function(opt, success, response) {
				if (success){
					var resp =  Ext.decode(response.responseText);

					if(resp && resp[0] && resp[0].LpuBuilding_id)
						combo.setValue(parseInt(resp[0].LpuBuilding_id));
				}
			}
		});
	},
	defaultValueCurrentLpuBuilding: false,
	
	initComponent: function(){
		var combo = this;
		if(combo.loadSelectSmp){
			// загрузим список подразделения СМП, которые были выбраны в качестве подстанций для управления  при входе в АРМ диспетчера подстанции
			var proxy=combo.getStore().getProxy();
			proxy.extraParams = {
				loadSelectSmp: true
			};
		}
		if (combo.defaultValueCurrentLpuBuilding) {
			combo.getStore().on('load', function(){
				if(!combo.getValue()) combo.setCurrentLpuBuilding();
			})			
		}

		combo.callParent(arguments);
	},
	
	store: new Ext.data.JsonStore({
		autoLoad: false,		
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Code', type: 'int'},	
			{name: 'LpuBuilding_Name', type: 'string'},	
			{name: 'LpuBuilding_Nick', type: 'string'},
			{name: 'Lpu_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadSmpUnits',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			sorters: {
				property: 'LpuBuilding_Code',
				direction: 'ASC'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});


Ext.define('sw.SmpUnitsNested',{
	extend: 'sw.SmpUnits',
	alias: 'widget.smpUnitsNestedCombo',
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Code', type: 'int'},
			{name: 'LpuBuilding_Name', type: 'string'},
			{name: 'LpuBuilding_fullName', type: 'string'},
			{name: 'LpuBuilding_filterName', type: 'string'},
			{name: 'LpuBuilding_Nick', type: 'string'},
			{name: 'Lpu_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadSmpUnitsNested',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			extraParams: {
				loadSelectSmp: true
			}
		}
	})
});

Ext.define('sw.commonState', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.commonState',
	cls: 'localCombo',
	valueField: 'CmpCommonState_id',
	codeField: 'CmpCommonState_Code',
	displayField: 'CmpCommonState_Name',
	store: new Ext.data.JsonStore({
		fields: [
			{name: 'CmpCommonState_id', type: 'int'},
			{name: 'CmpCommonState_Code', type: 'int'},
			{name: 'CmpCommonState_Name', type: 'string'},
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadCmpCommonStateCombo',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			extraParams: {
				loadSelectSmp: true
			}
		}
	})
});

Ext.define('sw.NestedLpuBuildingCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.nestedLpuBuildingCombo',
	labelAlign: 'right',
	fieldLabel:'МО',
	typeAhead: true,
	valueField: 'LpuBuilding_id',
	displayField: 'Lpu_Nick',
	displayTpl: '',
	tableName: 'NestedLpuBuilding',
	tpl: '<tpl for="."><div class="x-boundlist-item">'
		+ '<b>{Lpu_Nick}</b>'+' ({LpuBuilding_Name})'
		+ '</div></tpl>',
	cls: 'localCombo',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'LpuBuilding_id',   type: 'int'},
			{name: 'LpuBuilding_Name', type: 'string'},
			{name: 'Lpu_id',   type: 'int' },
			{name: 'Lpu_Nick', type: 'string' }
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadNestedLpuBuildings',
			reader: { type: 'json' },
			sorters: {
				property: 'Lpu_Nick',
				direction: 'ASC'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});

//комбобокс список статусов бригад
Ext.define('sw.SmpEmergencyTeamStatuses',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swEmergencyTeamStatuses',
	cls: 'localCombo',
	typeAhead: true,
	autoFilter: false,
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	valueField: 'EmergencyTeamStatus_id',
	name: 'EmergencyTeamStatus_id',
	displayField: 'EmergencyTeamStatus_Name',
	codeField: 'EmergencyTeamStatus_Code',
	displayTpl: '<tpl for=".">{EmergencyTeamStatus_Name}</tpl>',
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
        '{EmergencyTeamStatus_Name}'+
        '</div></tpl>',
	forceSelection: 'false',
	initComponent: function(){
		var combo = this;
		combo.callParent(arguments);
	},
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'EmergencyTeamStatus_id', type: 'int'},
			{name: 'EmergencyTeamStatus_Code', type: 'int'},
			{name: 'EmergencyTeamStatus_Name', type: 'string'},
		],
		proxy: {
			type: 'ajax',
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamStatuses',
			reader: {
				type: 'json'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});

//комбобокс подразделений СМП выбранных пользователем при входе в АРМ диспетчера подстанции
Ext.define('sw.SmpUnitsSelectedUser',{
	extend: 'sw.SmpUnits',
	alias: 'widget.SmpUnitsSelectedUser',
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Code', type: 'int'},	
			{name: 'LpuBuilding_Name', type: 'string'},	
			{name: 'LpuBuilding_fullName', type: 'string'},
			{name: 'LpuBuilding_Nick', type: 'string'},
			{name: 'Lpu_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadSmpUnitsNested',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			extraParams: {
				loadSelectSmp: true
			}
		}
	})
});

//комбобокс выбора подстанций из сохраненных в опциях
Ext.define('sw.SmpUnitsFromOptions',{
	extend: 'sw.SmpUnits',
	alias: 'widget.SmpUnitsFromOptions',
	loadSelectSmp: false,
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Code', type: 'int'},	
			{name: 'LpuBuilding_Name', type: 'string'},	
			{name: 'LpuBuilding_Nick', type: 'string'},
			{name: 'Lpu_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadSmpUnitsFromOptions',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			extraParams: {
				loadSelectSmp: this.loadSelectSmp
			}
		}
	})
});


//тип вызывающего

Ext.define('sw.CmpCallerTypeCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swCmpCallerTypeCombo',
	name: 'swCmpCallerTypeCombo',
	//
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'CmpCallerType',
	displayField:'CmpCallerType_Name',
	valueField: 'CmpCallerType_id',
	name: 'CmpCallerType_id',
	typeAhead: true,	
	autoFilter: false,
	triggerClear: true,	
	editable: true,
	translate: false,
	fields: [
		{name: 'CmpCallerType_id', type:'int'},
		{name: 'CmpCallerType_Name', type:'string'},
	],
	//
	fieldLabel: 'Кто вызывает'	
})


// Тип места вызова

Ext.define('sw.CmpCallPlaceType',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpCallPlaceType',
	name: 'CmpCallPlaceType_id',
	fieldLabel: 'Тип места вызова', 
	displayField:'CmpCallPlaceType_Name',
	codeField: 'CmpCallPlaceType_Code',
	valueField: 'CmpCallPlaceType_id',
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'CmpCallPlaceType',
	typeAhead: true,
	triggerClear: false,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'CmpCallPlaceType_id', type:'int'},
		{name: 'CmpCallPlaceType_Code', type:'string'},
		{name: 'CmpCallPlaceType_Name', type:'string'},
	]
})

Ext.define('sw.CrymeStudyType',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCrymeStudyType',
	name: 'CrymeStudyType_id',
	fieldLabel: 'Тип экспертизы', 
	displayField:'CrymeStudyType_Name',
	codeField: 'CrymeStudyType_Code',
	valueField: 'CrymeStudyType_id',
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'CrymeStudyType',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'CrymeStudyType_id', type:'int'},
		{name: 'CrymeStudyType_Code', type:'string'},
		{name: 'CrymeStudyType_Name', type:'string'},
	]
});

// Тип результата вызова

Ext.define('sw.CmpCallReasonType',{
	extend: 'sw.commonSprCombo',
	fieldLabel: 'Результат', 
	alias: 'widget.swCmpCallReasonType',
	name: 'CmpCallReasonType_id',
	displayField:'CmpCallReasonType_Name',
	codeField: 'CmpCallReasonType_Code',
	valueField: 'CmpCallReasonType_id',
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'CmpCallReasonType',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'CmpCallReasonType_id', type:'int'},
		{name: 'CmpCallReasonType_Code', type:'string'},
		{name: 'CmpCallReasonType_Name', type:'string'},
	]
})

// Вид заболеваний и несчатных случаев

Ext.define('sw.CmpDiseaseAndAccidentType',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpDiseaseAndAccidentType',
	name: 'CmpDiseaseAndAccidentType_id',
	fieldLabel: 'Вид', 
	displayField:'CmpDiseaseAndAccidentType_Name',
	codeField: 'CmpDiseaseAndAccidentType_Code',
	valueField: 'CmpDiseaseAndAccidentType_id',
	cls: 'localComboMongo',
	xtype: 'commonSprCombo',
	tableName: 'CmpDiseaseAndAccidentType',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'CmpDiseaseAndAccidentType_id', type:'int'},
		{name: 'CmpDiseaseAndAccidentType_Code', type:'string'},
		{name: 'CmpDiseaseAndAccidentType_Name', type:'string'},
	]
});

Ext.define('sw.Diag',{
	extend: 'sw.commonSprCombo',
	name: 'Diag_id',
	alias: 'widget.swDiag',
	displayField:'Diag_Name',
	valueField: 'Diag_id',
	codeField: 'Diag_Code',
//	cls:'localCombo',
	fieldLabel: 'Диагноз',
	typeAhead: true,
	triggerClear: true,
	expandHide:true,
	cls: 'localCombo',
	editable: true,
	minChars: 2,
	//hideTrigger:true,
	typeAheadDelay: 100,
	queryMode: 'remote',
	translate: false,
	onLoadStore: Ext.emptyFn,
	onEmptyResults: Ext.emptyFn,
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-boundlist-item">',
		'<font color="red">{Diag_Code}</font>&nbsp;{Diag_Name}',
		'</div></tpl>'
	),
	displayTpl: '<tpl for=".">{Diag_Code}. {Diag_Name}</tpl>',
/*
	clearBaseFilter: function()
	{
		this.baseFilterFn = null;
		this.baseFilterScope = null;
		this.lastQuery = null;
	},
	setBaseFilter: function(fn, scope)
	{
		this.baseFilterFn = fn;
		this.baseFilterScope = scope || this;
		this.store.filterBy(fn, scope);
		this.lastQuery = null;
	},
	*/
	/**
	 * Получение "чистого" кода диагноза, при вводе и поиске
	 */
	/*
	getDiagCode: function(code) {
		// получаем количество возможных символов
		q = code.slice(0, this.countSymbolsCode);
		// если в этом полученном количестве есть пробел, то обрезаем по пробел
		q = (q)?q.split(' ')[0]:'';
		// если там есть русские символы, то делаем их нерусскимми (код же в английской транскрипции)
		q = LetterChange(q.charAt(0)) + q.slice(1, q.length);
		// если нет точки в коде, и код больше трех символов, то добавляем точку
		if (q.charAt(3) != '.' && q.length > 3)
		{
			q = q.slice(0, 3) + '.' + q.slice(3, this.countSymbolsCode-1);
		}
		// все пробелы заменяем на пусто // upd: после строки q = (q)?q.split(' ')[0]:''; уже не имеет актуальности
		// q = q.replace(' ', '');
		return q;
	},
	*/
	onTrigger3Click: function(){
		var me = this;

		Ext.create('sw.tools.swDiagSearchTreeWindow').show({
			onSelectDiag: function(record){

				var rec = me.store.findRecord('Diag_id', record.get('Diag_id'));
				if(rec){
					me.setValue(rec.get('Diag_id'));
				}
				else{
					me.store.add(record);
					rec = me.store.findRecord('Diag_id', record.get('Diag_id'));

					me.setValue(rec);
				}
			}
		});
	},
	listeners:{

		render: function(){
			this.store.clearFilter();
			//пока так, позже скрывать стрелку нормально
			if(this.expandHide)
				this.triggerWrap.dom.rows[0].childNodes[2].style.setProperty('display','none');
		},
		change:function(cmb,newVal){
			if(!this.autoFilter && newVal){
				var q = newVal.toString(),
					rec = cmb.store.findRecord('Diag_id', newVal);
				if(rec) return true;

				q = (q)?q.split(' ')[0]:'';
				// если там есть русские символы, то делаем их нерусскимми (код же в английской транскрипции)
				q = LetterChange(q.charAt(0)) + q.slice(1, q.length);

				cmb.store.filterBy(function(rec){
					return (rec.get('Diag_Code').indexOf(q.toUpperCase()) != -1);
				});
				cmb.setRawValue(q);
				if(cmb.getPicker().isVisible()){
					cmb.expand();
				}
			}else{
				return false
			}
		}
	},

	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Diag_id', type: 'int'},
			{name: 'Diag_Name', type: 'string'},
			{name: 'Diag_Code', type: 'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=getDiags',
			reader: {
				type: 'json',
				idProperty: 'Diag_id'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		},
		listeners: {
			beforeload: function(store, operation, eOpts) {
				var params = operation.params;
				if(!params || Ext.isEmpty(params.query)) return false;
			}
		}
	})
})

// Наличие алкогольного опьянения

Ext.define('sw.IsAlco',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swIsAlco',
	name: 'YesNo_id',
	fieldLabel: 'Вид', 
	displayField:'YesNo_Name',
	codeField: 'YesNo_Code',
	valueField: 'YesNo_id',
	cls: 'localComboMongo',
//	xtype: 'commonSprCombo', // ТАК ДЕЛАТЬ НЕ НАДО!
	tableName: 'YesNo',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'YesNo_id', type:'int'},
		{name: 'YesNo_Code', type:'string'},
		{name: 'YesNo_Name', type:'string'},
	],
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-boundlist-item">',
		'<font color="red">{YesNo_Code}</font>&nbsp;{YesNo_Name}',
		'</div></tpl>'
	)
});



Ext.define('sw.dOrgCombo', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.dOrgCombo',
	cls: 'dynamicCombo',
	triggerClear: true,
	editable: true,
	hideTrigger:false,
	minChars: 2,
	typeAhead:true,
	autoFilter: false,
	typeAheadDelay: 0,
	displayField:'Org_Nick',
	valueField: 'Org_id',
	fieldLabel: 'Организация',
	queryMode: 'remote',
	store: new Ext.data.Store({
		fields: [
			{name: 'Org_id', type:'int'},
			{name: 'Org_Nick', type:'string'},
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '?c=Org&m=getOrgList',
			reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
})

Ext.define('sw.emergencyTempalteNames', {
	extend: 'sw.commonSprCombo', 
	alias: 'widget.emergencyTempalteNames',
	cls: 'localCombo',
	triggerClear: true,
	editable: false,
	minChars: 2,
	typeAhead:true,
	displayField:'EmergencyTeam_TemplateName',
	valueField: 'EmergencyTeam_TemplateName',
	fieldLabel: 'Имя шаблона',
	store: new Ext.data.Store({
		fields: [
			{name: 'EmergencyTeam_TemplateName', type:'string'},
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '?c=EmergencyTeam4E&m=getEmergencyTeamTemplatesNames',
			reader: {
			type: 'json',
			successProperty: 'success',
			root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	})
})


/*
Ext.define('sw.dOrgCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.dOrgCombo',
	queryMode: 'remote',
	minChars:2,
	forceSelection:true,
	typeAhead:true,
	valueField: 'Org_id',
	displayField: 'Org_Nick',
	enableKeyEvents: true,
	fieldLabel: 'Организация',
	name: 'Org_id',
	cls: 'dynamicCombo',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Org_id', type:'int'},
			{name: 'Org_Nick', type:'string'},
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			load: '',
			mode: 'income',
			type: 'ajax',
			url: '/?c=Org&m=getOrgList',
			reader: {
				type: 'json'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		},
		filter: Ext.emptyFn
	}),
//	onTrigger2Click: function(e) {
//		Ext.create('sw.tools.swOrgSearchWindow').show()
//	},
	onTrigger3Click: function(e) {
		this.setValue('')
		this.store.removeAll()
	},
	
	checkTriggerButton: function(){
		if (typeof this.triggerEl!='undefined')
		{
			if (this.getRawValue().length > 0) 
			{
				if (!this.triggerEl.elements[1].isVisible())
				{this.triggerEl.elements[1].show(true)}
			}
			else{this.triggerEl.elements[1].hide(true)}
		}
	},	

	fieldSubTpl: [
		'<div class="{hiddenDataCls}" role="presentation"></div>',
		'<input id="{id}" type="{type}" role="{role}" {inputAttrTpl} class="{fieldCls} {typeCls} {editableCls}" autocomplete="off"',
			'<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
			'<tpl if="name"> name="{name}"</tpl>',
			'<tpl if="placeholder"> placeholder="{placeholder}"</tpl>',
			'<tpl if="size"> size="{size}"</tpl>',
			'<tpl if="maxLength !== undefined"> maxlength="{maxLength}"</tpl>',
			'<tpl if="readOnly"> readonly="readonly"</tpl>',
			'<tpl if="disabled"> disabled="disabled"</tpl>',
			'<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
			'<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
			'/>',
			//'<div style="position: absolute; right: -23px; top: 1px;" class="x-trigger-index-1 x-form-trigger x-form-search-trigger" ></div>',
			'<div style="position: absolute; right: 21px; top: 3px; border: none;" class="x-trigger-index-2 x-form-trigger clearTextfieldButton" ></div>',
		{
			compiled: true,
			disableFormats: true
		}
	],
	
	tpl: '<tpl for="."><div class="x-boundlist-item">'+
				'{Org_Nick}'+
			'</div></tpl>',
		
	initComponent: function() {
        var me = this
		
		me.on('change', function(){
			me.checkTriggerButton()
		})
		me.on('render', function(){
			me.checkTriggerButton()
			me.store.getProxy().extraParams = {
				'mode' : 'income'
			}
		})
	
		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				 '{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );
	me.callParent();
	}
})
*/
// Комбобокс с выбором социального статуса
Ext.define('sw.SocStatusCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swSocStatusCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Соц. статус',
	valueField: 'SocStatus_id',
	codeField: 'SocStatus_Code',
	displayField: 'SocStatus_Name',
	tableName: 'SocStatus',
	fields: [
		{name: 'SocStatus_id', type: 'int'},
		{name: 'SocStatus_Code', type: 'string'},
		{name: 'SocStatus_Name', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором территории страхования
Ext.define('sw.OmsSprTerrCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOmsSprTerrCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Территория',
	valueField: 'OMSSprTerr_id',
	codeField: 'OMSSprTerr_Code',
	displayField: 'OMSSprTerr_Name',
	tableName: 'OMSSprTerr',
	fields: [
		{name: 'OMSSprTerr_id', type: 'int'},
		{name: 'OMSSprTerr_Code', type: 'string'},
		{name: 'OMSSprTerr_Name', type: 'string'},
		{name: 'KLRgn_id', type: 'int'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором типа полиса
Ext.define('sw.PolisTypeCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swPolisTypeCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Тип полиса',
	valueField: 'PolisType_id',
	codeField: 'PolisType_Code',
	displayField: 'PolisType_Name',
	tableName: 'PolisType',
	fields: [
		{name: 'PolisType_id', type: 'int'},
		{name: 'PolisType_Code', type: 'string'},
		{name: 'PolisType_Name', type: 'string'},
		{name: 'PolisType_SysNick', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором формы полиса
Ext.define('sw.PolisFormTypeCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swPolisFormTypeCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Форма полиса',
	valueField: 'PolisFormType_id',
	codeField: 'PolisFormType_Code',
	displayField: 'PolisFormType_Name',
	tableName: 'PolisFormType',
	dbFile: 'Promed.db',
	key: 'PolisFormType_id',
	fields: [
		{name: 'PolisFormType_id', type: 'int'},
		{name: 'PolisFormType_Code', type: 'string'},
		{name: 'PolisFormType_Name', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором организации выдавшей полис
Ext.define('sw.OrgSMOCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOrgSMOCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Выдан',
	valueField: 'OrgSMO_id',
	displayField: 'OrgSMO_Nick',
	tableName: 'OrgSMO',
	fields: [
		{name: 'OrgSMO_id', type: 'int'},
		{name: 'Org_id', type: 'int'},
		{name: 'OrgSMO_RegNomC', type: 'int'},
		{name: 'OrgSMO_RegNomN', type: 'int'},
		{name: 'OrgSMO_Nick', type: 'string'},
		{name: 'OrgSMO_isDMS', type: 'int'},
		{name: 'KLRgn_id', type: 'int'},
		{name: 'OrgSMO_endDate', type: 'string'},
		{name: 'OrgSMO_IsTFOMS', type: 'int'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором типа документа
Ext.define('sw.DocumentTypeCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swDocumentTypeCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Тип',
	valueField: 'DocumentType_id',
	codeField: 'DocumentType_Code',
	displayField: 'DocumentType_Name',
	tableName: 'DocumentType',
	fields: [
		{name: 'DocumentType_id', type: 'int'},
		{name: 'DocumentType_Code', type: 'int'},
		{name: 'DocumentType_Name', type: 'string'},
		{name: 'DocumentType_MaskSer', type: 'string'},
		{name: 'DocumentType_MaskNum', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

Ext.define('sw.OrgDepCombo',{
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.swOrgDepCombo',
	cls: 'dynamicCombo',
	displayField: 'OrgDep_Name',
	valueField:'OrgDep_id',
	fieldLabel: 'Выдан',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'OrgDep_id', type: 'int'},
			{name: 'OrgDep_Name', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Utils&m=GetObjectList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			extraParams: {
				Object: 'OrgDep',
				OrgDep_id: '',
				OrgDep_Name: ''
//				Server_id: 'check_it'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	}),
	queryMode: 'local',
	forceSelection: true,
	typeAhead: true,
	allowBlank: true,
	tpl: '<tpl for="."><div class="x-boundlist-item">{OrgDep_Name}</div></tpl>'
});

// Комбобокс выбора должности
Ext.define('sw.PostCombo',{
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.swPostCombo',
	cls: 'dynamicCombo',
	displayField: 'Post_Name',
	valueField:'Post_id',
	table: 'Post',
	fieldLabel: 'Должность',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Post_id', type: 'int'},
			{name: 'Post_Name', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Utils&m=GetObjectList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			extraParams: {
				Object: 'Post',
				Post_id: '',
				Post_Name: '',
				Server_id: 'check_it'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	}),
	queryMode: 'local',
	forceSelection: true,
	typeAhead: true,
	allowBlank: true,
	tpl: '<tpl for="."><div class="x-boundlist-item">{Post_Name}</div></tpl>'
});

// Комбобокс с выбором Социально-профессиональная группа
Ext.define('sw.OnkoOccupationClassCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swOnkoOccupationClassCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Социально-профессиональная группа',
	valueField: 'OnkoOccupationClass_id',
	codeField: 'OnkoOccupationClass_Code',
	displayField: 'OnkoOccupationClass_Name',
	tableName: 'OnkoOccupationClass',
	fields: [
		{name: 'OnkoOccupationClass_id', type: 'int'},
		{name: 'OnkoOccupationClass_Code', type: 'int'},
		{name: 'OnkoOccupationClass_Name', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором гражданства
Ext.define('sw.KLCountryCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.SwKLCountryCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Страна',
	valueField: 'KLCountry_id',
	codeField: 'KLCountry_Code',
	displayField: 'KLCountry_Name',
	tableName: 'KLCountry',
	fields: [
		{name: 'KLCountry_id', type: 'int'},
		{name: 'KLCountry_Code', type: 'int'},
		{name: 'KLCountry_Name', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false,
	forceSelection: true
});

// Комбобокс выбора должности
Ext.define('sw.OrgUnionCombo',{
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.swOrgUnionCombo',
	cls: 'dynamicCombo',
	displayField: 'OrgUnion_Name',
	valueField:'OrgUnion_id',
	table: 'OrgUnion',
	fieldLabel: 'Подразделение',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'OrgUnion_id', type: 'int'},
			{name: 'OrgUnion_Name', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Utils&m=GetObjectList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			extraParams: {
				Object: 'OrgUnion',
				OrgUnion_id: '',
				OrgUnion_Name: ''
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	}),
	queryMode: 'local',
	forceSelection: true,
	typeAhead: true,
	allowBlank: true,
	tpl: '<tpl for="."><div class="x-boundlist-item">{OrgUnion_Name}</div></tpl>'
});

// Комбобокс выбора человека
Ext.define('sw.PersonCombo',{
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.swPersonCombo',
	cls: 'dynamicCombo',
	displayField: 'Person_Fio',
	valueField:'Person_id',
	fieldLabel: 'Представитель',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Person_id', type: 'int'},
			{name: 'Person_Fio', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Person&m=getPersonCombo',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	}),
	queryMode: 'local',
	forceSelection: true,
	typeAhead: true,
	allowBlank: true,
	tpl: '<tpl for="."><div class="x-boundlist-item">{Person_Fio}</div></tpl>'
});

// Комбобокс с выбором Статус представителя
Ext.define('sw.DeputyKindCombo',{
	extend: 'sw.commonSprCombo', 
	alias: 'widget.swDeputyKindCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Статус представителя',
	valueField: 'DeputyKind_id',
	codeField: 'DeputyKind_Code',
	displayField: 'DeputyKind_Name',
	tableName: 'DeputyKind',
	fields: [
		{name: 'DeputyKind_id', type: 'int'},
		{name: 'DeputyKind_Code', type: 'string'},
		{name: 'DeputyKind_Name', type: 'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: true,
	translate: false
});

// Комбобокс с выбором Да или Нет
Ext.define('sw.YesNoCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swYesNoCombo',
	cls: 'localComboMongo',
	valueField: 'YesNo_id',
	codeField: 'YesNo_Code',
	displayField:'YesNo_Name',
	idProperty: 'YesNo_id',
	tableName: 'YesNo',
	fields: [
		{name: 'YesNo_id', type:'int'},
		{name: 'YesNo_Code', type:'string'},
		{name: 'YesNo_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Семейное положение
Ext.define('sw.FamilyStatusCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swFamilyStatusCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Семейное положение',
	valueField: 'FamilyStatus_id',
	codeField: 'FamilyStatus_Code',
	displayField:'FamilyStatus_Name',
	tableName: 'FamilyStatus',
	fields: [
		{name: 'FamilyStatus_id', type:'int'},
		{name: 'FamilyStatus_Code', type:'string'},
		{name: 'FamilyStatus_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Этническая группа
Ext.define('sw.EthnosCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swEthnosCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Этническая группа',
	valueField: 'Ethnos_id',
	codeField: 'Ethnos_Code',
	displayField:'Ethnos_Name',
	tableName: 'Ethnos',
	fields: [
		{name: 'Ethnos_id', type:'int'},
		{name: 'Ethnos_Code', type:'string'},
		{name: 'Ethnos_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Место воспитания
Ext.define('sw.ResidPlaceCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swResidPlaceCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Место воспитания',
	valueField: 'ResidPlace_id',
	codeField: 'ResidPlace_Code',
	displayField:'ResidPlace_Name',
	tableName: 'ResidPlace',
	fields: [
		{name: 'ResidPlace_id', type:'int'},
		{name: 'ResidPlace_Code', type:'string'},
		{name: 'ResidPlace_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Район города
Ext.define('sw.PersonSprTerrDopCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swPersonSprTerrDopCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Район города',
	valueField: 'PersonSprTerrDop_id',
	codeField: 'PersonSprTerrDop_Code',
	displayField:'PersonSprTerrDop_Name',
	tableName: 'PersonSprTerrDop',
	fields: [
		{name: 'PersonSprTerrDop_id', type:'int'},
		{name: 'PersonSprTerrDop_Code', type:'string'},
		{name: 'PersonSprTerrDop_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Группа здоровья
Ext.define('sw.HealthKindCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swHealthKindCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Группа здоровья',
	valueField: 'HealthKind_id',
	codeField: 'HealthKind_Code',
	displayField:'HealthKind_Name',
	tableName: 'HealthKind',
	fields: [
		{name: 'HealthKind_id', type:'int'},
		{name: 'HealthKind_Code', type:'string'},
		{name: 'HealthKind_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Способ вскармливания
Ext.define('sw.FeedingTypeCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swFeedingTypeCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Способ вскармливания',
	valueField: 'FeedingType_id',
	codeField: 'FeedingType_Code',
	displayField:'FeedingType_Name',
	tableName: 'FeedingType',
	fields: [
		{name: 'FeedingType_id', type:'int'},
		{name: 'FeedingType_Code', type:'string'},
		{name: 'FeedingType_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Категория инвалидности
Ext.define('sw.InvalidKindCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swInvalidKindCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Категория',
	valueField: 'InvalidKind_id',
	codeField: 'InvalidKind_Code',
	displayField:'InvalidKind_Name',
	tableName: 'InvalidKind',
	fields: [
		{name: 'InvalidKind_id', type:'int'},
		{name: 'InvalidKind_Code', type:'string'},
		{name: 'InvalidKind_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Ведущее ограничение здоровья
Ext.define('sw.HealthAbnormVitalCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swHealthAbnormVitalCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Ведущее ограничение здоровья',
	valueField: 'HealthAbnormVital_id',
	codeField: 'HealthAbnormVital_Code',
	displayField:'HealthAbnormVital_Name',
	tableName: 'HealthAbnormVital',
	fields: [
		{name: 'HealthAbnormVital_id', type:'int'},
		{name: 'HealthAbnormVital_Code', type:'string'},
		{name: 'HealthAbnormVital_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});

// Комбобокс с выбором Главное нарушение здоровья
Ext.define('sw.HealthAbnormCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swHealthAbnormCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Главное нарушение здоровья',
	valueField: 'HealthAbnorm_id',
	codeField: 'HealthAbnorm_Code',
	displayField:'HealthAbnorm_Name',
	tableName: 'HealthAbnorm',
	fields: [
		{name: 'HealthAbnorm_id', type:'int'},
		{name: 'HealthAbnorm_Code', type:'string'},
		{name: 'HealthAbnorm_Name', type:'string'}
	],
	typeAhead: true,
	triggerClear: true,
	editable: false,
	autoFilter: false,
	translate: false
});


Ext.define('sw.DiagCombo',{
//	extend: 'Ext.form.ComboBox',
	extend: 'sw.commonSprCombo',
	alias: 'widget.swDiagCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Диагноз',
	valueField: 'Diag_id',
	codeField: 'Diag_Code',
	displayField:'Diag_Name',
	tableName: 'Diag',
	fields: [
		{name: 'Diag_id', type:'int'},
		{name: 'Diag_Code', type:'string'},
		{name: 'Diag_Name', type:'string'}
	],
	initComponent: function(){
		this.callParent(arguments);
		
		this.getStore().autoLoad = false;
	}
});

// Комбобокс территорий
//Ext.define('sw.KLAreaStatCombo',{
//	extend: 'sw.commonSprCombo', 
//	alias: 'widget.klareastatcombo',
//	cls: 'localComboMongo',
//	fieldLabel: 'Территория',
//	valueField: 'KLAreaStat_id',
//	codeField: 'KLAreaStat_Code',
//	displayField: 'PolisType_Name',
//	tableName: 'PolisType',
//	fields: [
//		{name: 'KLAreaStat_id',    type:'int'},
//		{name: 'KLAreaStat_Code', type:'int'},
//		{name: 'KLArea_Name',  type:'string'},
//		{name: 'KLCountry_id',  type:'int'},
//		{name: 'KLRGN_id',  type:'int'},
//		{name: 'KLSubRGN_id',  type:'int'},
//		{name: 'KLCity_id',  type:'int'},
//		{name: 'KLTown_id',  type:'int'}
//	],
//	typeAhead: true,
//	triggerClear: true,
//	editable: true,
//	translate: false
//});

Ext.define('sw.KLRegionCombo',{
	translate: true,
	alias: 'widget.klregioncombo',
	fieldLabel: 'Регион',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	extend: 'sw.commonSprCombo',
	displayField: 'Region_Name',
	valueField:'Region_id',
	allowBlank: true,
	queryMode: 'local',
	forceSelection: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Region_id',    type:'int'},
			{name: 'Socr_id', type: 'int'},
			{name: 'Region_Name',  type:'string'},
			{name: 'Socr_name', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Address4E&m=getRegions',
			reader: {
				type: 'json'
			},
			extraParams: {
				KLCountry_id: null
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
	//,tpl: '<tpl for="."><div class="x-boundlist-item">{OrgDep_Name}</div></tpl>'
});

Ext.define('sw.KLSubRgnCombo',{
	translate: true,
	alias: 'widget.klsubrgncombo',
	fieldLabel: 'Район',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	extend: 'sw.commonSprCombo',
	displayField: 'SubRGN_Name',
	valueField:'SubRGN_id',
	allowBlank: true,
	queryMode: 'local',
	forceSelection: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'SubRGN_id',    type:'int'},
			{name: 'Socr_id', type: 'int'},
			{name: 'SubRGN_Name',  type:'string'},
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Address4E&m=getSubRegions',///?c=Address&m=getSubRegions//C_LOAD_SUBREGIONCOMBO
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.KLCityCombo',{
	translate: true,
	alias: 'widget.klcitycombo',
	fieldLabel: 'Город',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	extend: 'sw.commonSprCombo',
	displayField: 'City_Name',
	valueField:'City_id',
	allowBlank: true,
	queryMode: 'local',
	forceSelection: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'City_id',    type:'int'},
			{name: 'Socr_id', type: 'int'},
			{name: 'City_Name',  type:'string'},
		],
		proxy: {
			type: 'ajax',
			url: '?c=Address4E&m=getCities',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.KLTownCombo',{
	translate: true,
	alias: 'widget.kltowncombo',
	fieldLabel: 'Населенный пункт',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	extend: 'sw.commonSprCombo',
	displayField: 'Town_Name',
	valueField:'Town_id',
	allowBlank: true,
	queryMode: 'local',
	forceSelection: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Town_id',    type:'int'},
			{name: 'Socr_id', type: 'int'},
			{name: 'Town_Name',  type:'string'},
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Address4E&m=getTowns',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.KLStreetCombo',{
	translate: true,
	extend: 'sw.commonSprCombo',
	alias: 'widget.klstreetcombo',
	fieldLabel: 'Улица',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	//extend: 'Ext.form.ComboBox', 
	displayField: 'Street_Name',
	valueField:'Street_id',
	allowBlank: true,
	queryMode: 'local',
	forceSelection: true,
	
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Street_id',    type:'int'},
			{name: 'Socr_id', type: 'int'},
			{name: 'Street_Name',  type:'string'},
		],
		proxy: {
			type: 'ajax',
			url: '/?c=Address4E&m=getStreets',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.ForensicIniciatorPostCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swForensicIniciatorPostCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Должность инициатора',
	valueField: 'ForensicIniciatorPost_id',
	codeField: 'ForensicIniciatorPost_Code',
	displayField:'ForensicIniciatorPost_Name',
	tableName: 'ForensicIniciatorPost',
	fields: [
		{name: 'ForensicIniciatorPost_id', type:'int'},
		{name: 'ForensicIniciatorPost_Code', type:'string'},
		{name: 'ForensicIniciatorPost_Name', type:'string'}
	]
});

Ext.define('sw.swXmlTypeCombo',{
	extend: 'sw.commonSprCombo',
	cls: 'localComboMongo',
	alias: 'widget.swxmltypecombo',
	fieldLabel: 'Тип экспертизы',
	name: 'XmlType_id',
	valueField: 'XmlType_id',
	codeField: 'XmlType_Code',
	displayField:'XmlType_Name',
	tableName: 'XmlType',
	editable: false,
	fields: [
		{name: 'XmlType_id', type:'string'},
		{name: 'XmlType_Code', type:'int'},
		{name: 'XmlType_Name', type:'string'}
	]
});

Ext.define('sw.forensicValuationInjuryCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.swForensicValuationInjuryCombo',
	name: 'ForensicValuationInjury_id',
	forceSelection: false, // Необходимо для выбора пустого значения
	queryMode: 'local',
	fieldLabel: 'Оценка вреда здоровью',
	displayField:'ForensicValuationInjury_Name',
	valueField: 'ForensicValuationInjury_id',
	fields: [
		{name: 'ForensicValuationInjury_id'},
		{name: 'ForensicValuationInjury_Name', type: 'string'}
	],
	listeners: {
		select: function (comp, record, index) {
			// Необходимо для выбора пустого значения
			if (comp.getValue() === 0 || comp.getValue() == '&nbsp;' || comp.getValue() === null ) {
				comp.setValue(null);
			}
		}
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'ForensicValuationInjury_id', type:'int'},
			{name: 'ForensicValuationInjury_Name', type:'string'}
		],
		listeners: {
			load: function (store, records) {
				// Необходимо для выбора пустого значения
				store.insert(0, [{ ForensicValuationInjury_id: null, ForensicValuationInjury_Name: '&nbsp;' }]);
			}
		},
		proxy: {
			type: 'ajax',
			url: '/?c=BSME&m=loadForensicValuationInjury',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.forensicDefinitionSexualOffensesCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.swForensicDefinitionSexualOffensesCombo',
	name: 'ForensicDefinitionSexualOffenses_id',
	forceSelection: false, // Необходимо для выбора пустого значения
	queryMode: 'local',
	fieldLabel: 'Определение половых состояний',
	displayField:'ForensicDefinitionSexualOffenses_Name',
	valueField: 'ForensicDefinitionSexualOffenses_id',
	fields: [
		{name: 'ForensicDefinitionSexualOffenses_id'},
		{name: 'ForensicDefinitionSexualOffenses_Name', type: 'string'}
	],
	listeners: {
		select: function (comp, record, index) {
			// Необходимо для выбора пустого значения
			if (comp.getValue() === 0 || comp.getValue() == '&nbsp;' || comp.getValue() === null ) {
				comp.setValue(null);
			}
		}
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'ForensicDefinitionSexualOffenses_id', type:'int'},
			{name: 'ForensicDefinitionSexualOffenses_Name', type:'string'}
		],
		listeners: {
			load: function (store, records) {
				// Необходимо для выбора пустого значения
				store.insert(0, [{ ForensicDefinitionSexualOffenses_id: null, ForensicDefinitionSexualOffenses_Name: '&nbsp;' }]);
			}
		},
		proxy: {
			type: 'ajax',
			url: '/?c=BSME&m=loadForensicDefinitionSexualOffenses',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});

Ext.define('sw.forensicSubDefinitionCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.swForensicSubDefinitionCombo',
	name: 'ForensicSubDefinition_id',
	forceSelection: false, // Необходимо для выбора пустого значения
	queryMode: 'local',
	fieldLabel: 'Определение',
	displayField:'ForensicSubDefinition_Name',
	valueField: 'ForensicSubDefinition_id',
	fields: [
		{name: 'ForensicSubDefinition_id'},
		{name: 'ForensicSubDefinition_Name', type: 'string'}
	],
	listeners: {
		select: function (comp, record, index) {
			// Необходимо для выбора пустого значения
			if (comp.getValue() === 0 || comp.getValue() == '&nbsp;' || comp.getValue() === null ) {
				comp.setValue(null);
			}
		}
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'ForensicSubDefinition_id', type:'int'},
			{name: 'ForensicSubDefinition_Name', type:'string'}
		],
		listeners: {
			load: function (store, records) {
				// Необходимо для выбора пустого значения
				store.insert(0, [{ ForensicSubDefinition_id: null, ForensicSubDefinition_Name: '&nbsp;' }]);
			}
		},
		proxy: {
			type: 'ajax',
			url: '/?c=BSME&m=loadForensicSubDefinition',
			reader: {
				type: 'json'
			},
			actionMethods: { read: 'POST' },
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			filterParam: undefined
		}
	})
});






// Попытка переписать commonSprCombo, т.к. он работает некорректно:
// - после фокуса, если нажать вниз, не выбирается первый элемент;
// - после ввода, по табу значение выбирается, по клику мыши вне комбо, слетает;
// - при клике мышкой не отображаются значения

//-- от автора commonSprCombo-- 
// п.п 1 скорей всего, компонент тестировался на унаследованном комбобоксе streetsSpeedCombo
// 		 если выбрана запись, то в инпут подставляются значения из 2 полей, а поиск идет по одному полю, соотв. он их не находит
//		 если запись не выбрана и пользователь вводит значения, то в выпадающем списке все норм выделяется 
//		 --данное поведение не считаю ошибкой
// п.п 2 значение выбирается, если находится в списке, как по табу, так и при потере фокуса. произвольные значения для комбобокса не допустимы
// п.п 3 при клике мышки значения корректоно вставляются
// вывод - создание нового класса не лучшее решение от всех проблем, если возникают вопросы по компоненту - пишите автору (те мне) 
//		   или исправляйте сами, но не пишите другой компонент, который будет заменять существующий.
//		 

// Базовый класс комбобокса для справочников
// Новые унаследованные комбобоксы должны начинаться с префикса d,
// например, dSexCombo

// @todo Добавить кнопку поиска
// @todo Добавить флаг "Выводить поле с кодом в шаблоне", "Выводить поле с кодом после выбора"
// @todo Поиск по коду
Ext.define('sw.baseDirectoryCombo',{
	extend: 'Ext.form.ComboBox',
	queryMode: 'local',
	store: null,
	typeAhead: true, // Включает автокомплит
	forceSelection: true, // Принудительный выбор значения при потере фокуса
	listConfig: {
		cls: 'sw-directory-combo-bigger'
	},
	initComponent: function(){
		if (this.fields) {
			this.store = Ext.create('Ext.data.Store',{
				url : '/?c=MongoDBWork&m=getData',
				fields: this.fields,
				sorters: {
					property: this.valueField,
					direction: 'ASC'
				}
			});
		}
		
		if ( !this.tpl ) {
			this.tpl = new Ext.XTemplate(
				'<tpl for=".">',
					'<div class="x-boundlist-item">',
						'<tpl if="' + this.codeField + '">',
							'<span style="color: red;">{' + this.codeField + '}</span> ',
						'</tpl>',
						'{' + this.displayField + '}',
					'</div>',
				'</tpl>'
			);
		}
		
		this.callParent(arguments);
	},
	listeners: {
		buffer: 50,
		change: function(){
			var store = this.store,
				value = this.getValue();
		
			/*
			 * @todo Альтернативный вариант автокомплита
			 * Отфильтровывет значения в соответствии с введенным значением
			store.clearFilter();
			if ( value && this.codeField ) {
				store.filter({
					property: this.codeField,
					exactMatch: true,
					value: this.getValue()
				});
				if ( store.data.length ) {
					return;
				}
			}
			store.clearFilter();
			store.filter({
				property: this.displayField,
				anyMatch: true, // подумать как сделать
				value: this.getValue()
			});
			*/
		   
			// Ищем совпадение по коду справочника
			var record;
			if ( this.codeField ) {
				record = store.findRecord(this.codeField, value);
			}
			
			// Ищем совпадение по имени из справочника
			if ( !record ) {
				record = store.findRecord(this.displayField, value);
			}
			
			// Если есть совпадения подсветим их
			if ( record ) {
				this.getPicker().highlightItem(this.getPicker().getNode(record));
			} else {
				this.getPicker().clearHighlight();
			}
		}
	},
	trigger1Cls: 'sw-form-arrow-trigger',
	plugins: ['clearbutton']
});

// Комбобокс выбора "Пола"
Ext.define('sw.dSexCombo',{
	extend: 'sw.baseDirectoryCombo',
	alias: 'widget.swDSexCombo',
	fieldLabel: 'Пол',
	name: 'Sex_id',
	// Параметры инициализации справочника Mongo
	cls: 'localComboMongo',
	tableName: 'Sex',
	// @todo Оптимизировать количество указываемых полей.
	// Можно сделать на основе имени таблицы.
	valueField: 'Sex_id',
	displayField: 'Sex_Name',
	codeField: 'Sex_Code',
	fields: [
		{name: 'Sex_id', type: 'int'},
		{name: 'Sex_Name', type: 'string'},
		{name: 'Sex_Code', type: 'string'}
	]
	// -/-
});

// Комбобокс выбора "Типа возраста"
Ext.define('sw.dAgeUnitCombo',{
    extend: 'sw.commonSprCombo',
	autoFilter: false,
	alias: 'widget.swDAgeUnitCombo',
	queryMode: 'local',	
	store: Ext.create('Ext.data.Store', {
		fields: ['ageUnit_id','ageUnit_name'],
		data: [
			{
			ageUnit_id: 1,
			//'ageUnit_id': 'years',
			'ageUnit_name': 'Лет'
			},
			{
			ageUnit_id: 2,
			//'ageUnit_id': 'months',
			'ageUnit_name': 'Месяцев'
			},
			{
			ageUnit_id: 3,
			//'ageUnit_id': 'days',
			'ageUnit_name': 'Дней'
			}
		]
	}),
	typeAhead: true,
	forceSelection: true,
	listConfig: {
		//cls: 'sw-directory-combo-bigger'
	},
    initComponent: function() {
        var me = this;

        me.on('render', function(cmp, opts){
            me.inputEl.on('focus', function(){
                if(!me.inputEl.hasCls('x-form-focus')) {
                    me.inputEl.addCls('x-form-focus');
                    me.inputEl.addCls('x-field-form-focus');
                    me.inputEl.addCls('x-field-default-form-focus');
                }
            });
            me.inputEl.on('blur', function(){
                me.inputEl.removeCls( 'x-form-focus' );
                me.inputEl.removeCls( 'x-field-form-focus' );
                me.inputEl.removeCls( 'x-field-default-form-focus' );
            });
        });
        Ext.applyIf(me);
        me.callParent(arguments);
    }
});


// Комбобокс нмп
Ext.define('sw.CmpCallCardTypeCombo',{
	extend: 'Ext.form.ComboBox',
	alias: 'widget.swCmpCallCardTypeCombo',
	queryMode: 'local',	
	valueField: 'CmpCallCardType_id',
	displayField: 'CmpCallCardType_Name',
	name: 'CmpCallCard_IsNMP',
	store: Ext.create('Ext.data.Store', {
		fields:
		[
			{name: 'CmpCallCardType_id', type: 'int'},
			{name: 'CmpCallCardType_Name', type: 'string'}
		],
		data: [
			{
			'CmpCallCardType_id': 1,
			'CmpCallCardType_Name': 'СМП'
			},
			{
			'CmpCallCardType_id': 2,
			'CmpCallCardType_Name': 'НМП'
			}	
		]
	}),
	typeAhead: true,
	forceSelection: true,
	listConfig: {
		cls: 'sw-directory-combo-bigger'
	}
});


// Комбобокс нмп
Ext.define('sw.CmpCallTypeIsExtraCombo',{
	extend: 'sw.commonSprCombo',
	autoFilter: false,
	alias: 'widget.swCmpCallTypeIsExtraCombo',
	queryMode: 'local',	
	valueField: 'CmpCallTypeIsExtraType_id',
	displayField: 'CmpCallCardIsExtraType_Name',
	name: 'CmpCallCard_IsExtra',
	typeAhead: true,
	forceSelection: true,
	listConfig: {
		cls: 'sw-directory-combo-bigger'
	},
	initComponent: function() {
		var cmp = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp','nmpgranddoc']);

		var dataArray = [
			{
				'CmpCallTypeIsExtraType_id': 1,
				'CmpCallCardIsExtraType_Name': 'Экстренный'
			},
			{
				'CmpCallTypeIsExtraType_id': 2,
				'CmpCallCardIsExtraType_Name': 'Неотложный'
			}
		];

		if(isNmpArm){
			dataArray = dataArray.concat(
				[
					{
						'CmpCallTypeIsExtraType_id': 3,
						'CmpCallCardIsExtraType_Name': 'Вызов врача на дом'
					},
					{
						'CmpCallTypeIsExtraType_id': 4,
						'CmpCallCardIsExtraType_Name': 'Обращение в поликлинику'
					}
				]
			);
		};

		cmp.store = Ext.create('Ext.data.Store', {
			fields:
				[
					{name: 'CmpCallTypeIsExtraType_id', type: 'int'},
					{name: 'CmpCallCardIsExtraType_Name', type: 'string'}
				],
			data: dataArray
		}),

		Ext.applyIf(cmp);
		cmp.callParent(arguments)
	}
});

//комбобокс выбора планшета
Ext.define('sw.CMPTabletPC',{
	extend: 'sw.commonSprCombo',
	displayField:'CMPTabletPC_Name',
	codeField: 'CMPTabletPC_Code',
	valueField: 'CMPTabletPC_id',
	name: 'CMPTabletPC_id',
	autoFilter: false,
	alias: 'widget.CMPTabletPC',
	cls:'localCombo',
	fieldLabel: 'Планшетный компьютер',
	typeAhead: true,
	forceSelection: 'false',
	triggerClear: true,
	translate: false,
	listeners:{
		expand: function(){
			this.getStore().suspendEvents()
		}
	},
	tpl: '<tpl for="."><div class=" x-boundlist-item">' +
		'{CMPTabletPC_Name}' + ' {CMPTabletPC_SIM}' +
		'</div></tpl>',
	checkTriggerButton: function(display){
		if(!this.readOnly && this.triggerClear)
		{
			if ((this.getRawValue().length > 0) || (this.store.data.length>0))
			{
				this.triggerCell.elements[0].setVisible(display);
				if(display)
				{
					this.triggerCell.elements[0].removeCls('hiddenTriggerWrap');
					this.triggerCell.elements[0].addCls('visibleTriggerWrap');
				}
				else{
					this.triggerCell.elements[0].addCls('hiddenTriggerWrap');
					this.triggerCell.elements[0].removeCls('visibleTriggerWrap');
				}
			}
			else{
				this.triggerCell.elements[0].hide();
				this.triggerCell.elements[0].addCls('hiddenTriggerWrap');
				this.triggerCell.elements[0].removeCls('visibleTriggerWrap');
			}
		}
	},
	onTrigger1Click: function(e) {
		this.clearValue();
		this.focus();
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'CMPTabletPC_id', type: 'int'},
			{name: 'CMPTabletPC_Code', type: 'int'},
			{name: 'CMPTabletPC_Name', type: 'string'},
			{name: 'CMPTabletPC_SIM', type: 'string'},
			{name: 'LpuBuilding_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=TabletComputers&m=loadTabletComputersList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			sorters: {
				property: 'CMPTabletPC_Code',
				direction: 'ASC'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});

//комбобокс автомобилей
Ext.define('sw.EmergencyCars',{
	extend: 'sw.commonSprCombo',
	displayField:'displayString',
	codeField: 'displayString',
	valueField: 'MedProductCard_id',
	name: 'MedProductCard_id',
	autoFilter: false,
	alias: 'widget.EmergencyCars',
	cls:'localCombo',
	fieldLabel: 'Автомобиль',
	typeAhead: true,
	forceSelection: false,
	triggerClear: true,
	translate: false,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'MedProductCard_id', type: 'int'},
			{name: 'MedProductCard_BoardNumber', type: 'string'},
			{name: 'AccountingData_RegNumber', type: 'string'},
			{name: 'MedProductClass_Model', type: 'string'},
			{name: 'MedProductClass_Name', type: 'string'},
			{name: 'AccountingData_setDate', type: 'string'},
			{name: 'AccountingData_endDate', type: 'string'},
			{name: 'EmergencyTeam_Num', type: 'string'},
			{name: 'EmergencyTeamDuty_DTStart', type: 'datetime'},
			{name: 'EmergencyTeamDuty_DTFinish', type: 'datetime'},
			{name: 'GeoserviceTransport_id', type: 'int'},
            {
                name: 'displayString',
                convert: function(value, record) {
                    var fullName  = record.get('MedProductCard_BoardNumber') + ' ' + record.get('MedProductClass_Model') + ' ' + record.get('AccountingData_RegNumber');

                    return fullName;
                }
            },
		],
		proxy: {
			type: 'ajax',
			url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamAutoList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			sorters: {
				property: 'LpuBuilding_Code',
				direction: 'ASC'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	}),
	displayTpl: new Ext.XTemplate(
		'<tpl for=".">' +
		'{displayString}' +
		'</tpl>'
	),
    tpl: new Ext.XTemplate(
        '<tpl for=".">' +
        '<div class="x-boundlist-item">' +
        '<font color="red">{MedProductCard_BoardNumber}</font> {MedProductClass_Model} {AccountingData_RegNumber}'+
        '<tpl if="EmergencyTeam_Num">' +
        '<font color="red"> Наряд №{EmergencyTeam_Num}, {[ this.getDate(values) ]} - {[ this.getTime(values) ]}</font>'+
        '</tpl>'+
        '</div></tpl>',
        {
            getDate: function (val) {
                return Ext.Date.format(new Date(val.EmergencyTeamDuty_DTStart), 'd.m.Y H:i');
            },
            getTime: function(val){
                return Ext.Date.format(new Date(val.EmergencyTeamDuty_DTFinish), 'H:i');
            }
        }
    ),
});

Ext.define('sw.AddressCombo',{
	extend: 'Ext.form.field.Trigger',
	name: 'addressCombo',
	alias: 'widget.AddressCombo',
	validationEvent: false, 
	validateOnBlur: false, 
	trigger1Cls: 'x-form-search-trigger',
	trigger2Cls:'x-form-clear-trigger',
	showAddressWindow: function(addressObj, callbackFn){
		Ext.create('common.tools.swAddressEditWindow',{
			fields: addressObj,
			Address_begDateHidden: true,
			callback: callbackFn
		});
	},
	onTrigger2Click : function(){},
	onTrigger1Click : function(){}
});

Ext.define('sw.lpuAllLocalCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.lpuAllLocalCombo',
	name: 'lpuAllLocalCombo',
	cls: 'localCombo',
	typeAhead: true,
	triggerClear: true,
	queryMode: 'local',
	bigFont: true,
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Lpu_id', type:'int'},
			{name: 'Org_Name', type:'string'},
			{name: 'Org_Nick', type:'string'}
		],
		sorters: {
			property: 'Org_Nick',
			direction: 'ASC'
		},
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=LpuStructure&m=getLpuListByRegion',
			//url: '/?c=Org4E&m=getOrgList',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			},
			extraParams: {
				'OrgType': 'lpu'
			}
		}
	}),
	displayField:'Org_Nick',
	valueField: 'Lpu_id',
	labelAlign: 'right',
	fieldLabel: 'МО',

	initComponent: function() {
		var cmp = this;

		cmp.tpl = Ext.create('Ext.XTemplate',
			'<tpl for=".">' +
			'<div class="x-boundlist-item' +
			(cmp.bigFont ? ' enlarged-font' : '') + ' ">' +
			'{Org_Nick}' +
			'</div></tpl>'
		);

		cmp.displayTpl = '<tpl for=".">{Org_Nick}</tpl>';

		Ext.applyIf(cmp);
		cmp.callParent(arguments)
	}
});

Ext.define('sw.RegionSmpUnits',{
	extend: 'sw.commonSprCombo',
	displayField:'LpuBuilding_Name',
	codeField: 'LpuBuilding_Code',
	valueField: 'LpuBuilding_id',
	name: 'LpuBuilding_id',
	autoFilter: false,
	alias: 'widget.regionSmpUnits',
	cls:'localCombo',
	fieldLabel: 'Подразделение СМП',
	typeAhead: true,
	forceSelection: 'false',
	triggerClear: true,
	translate: false,

	initComponent: function(){
		var combo = this;

		combo.callParent(arguments);
	},

	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Code', type: 'int'},
			{name: 'LpuBuilding_Name', type: 'string'},
			{name: 'LpuBuilding_Nick', type: 'string'},
			{name: 'Lpu_Nick', type: 'string'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadRegionSmpUnits',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});

Ext.define('sw.CmpResultCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpResultCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Результат выезда',
	valueField: 'CmpResult_id',
	codeField: 'CmpResult_Code',
	displayField:'CmpResult_Name',
	tableName: 'CmpResult',
	fields: [
		{name: 'CmpResult_id', type:'int'},
		{name: 'CmpResult_Code', type:'string'},
		{name: 'CmpResult_Name', type:'string'}
	],
	initComponent: function(){
		this.callParent(arguments);

		this.getStore().autoLoad = false;
	}
});

/**
 * Статус вызова
 */
Ext.define('swCmpCallCardStatusTypeCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpCallCardStatusTypeCombo',
	cls: 'localCombo',
	typeAhead: true,
	autoFilter: false,
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	valueField: 'CmpCallCardStatusType_id',
	name: 'CmpCallCardStatusType_id',
	displayField: 'CmpCallCardStatusType_Name',
	codeField: 'CmpCallCardStatusType_Code',
	displayTpl: '<tpl for=".">{CmpCallCardStatusType_Name}</tpl>',

	forceSelection: 'false',
	initComponent: function(){
		var combo = this;

		combo.callParent(arguments);
	},
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'CmpCallCardStatusType_id', type: 'int'},
			{name: 'CmpCallCardStatusType_Name', type: 'string'},
			{name: 'CmpCallCardStatusType_Code', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardStatusTypes',
			reader: {
				type: 'json'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined
		}
	})
});

//Комбик всех объектов СМП с возможностью фильтрации по типу
Ext.define('swUnformalizedAddressDirectoryCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.swUnformalizedAddressDirectoryCombo',
	cls: 'localCombo',
	typeAhead: true,
	autoFilter: false,
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	valueField: 'UnformalizedAddressDirectory_id',
	name: 'UnformalizedAddressDirectory_id',
	displayField: 'UnformalizedAddressDirectory_Name',
	displayTpl: '<tpl for=".">{UnformalizedAddressDirectory_Name}</tpl>',
	type_id: null, //UnformalizedAddressType_id
	forceSelection: 'false',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			 {name: 'KLCity_id', type: 'int'},
			 {name: 'KLRgn_id', type: 'int'},
			 {name: 'KLStreet_id', type: 'int'},
			 {name: 'KLSubRgn_id', type: 'int'},
			 {name: 'KLTown_id', type: 'int'},
			 {name: 'LpuBuilding_Name', type: 'string'},
			 {name: 'LpuBuilding_id', type: 'int'},
			 {name: 'Lpu_id', type: 'int'},
			 {name: 'Lpu_aid', type: 'int'},
			 {name: 'UnformalizedAddressDirectory_Address', type: 'string'},
			 {name: 'UnformalizedAddressDirectory_Dom', type: 'string'},
			 {name: 'UnformalizedAddressDirectory_Name', type: 'string'},
			 {name: 'UnformalizedAddressDirectory_id', type: 'int'},
			 {name: 'UnformalizedAddressDirectory_lat', type: 'string'},
			 {name: 'UnformalizedAddressDirectory_lng', type: 'string'},
			 {name: 'UnformalizedAddressType_Name', type: 'string'},
			 {name: 'UnformalizedAddressType_SocrNick', type: 'string'},
			 {name: 'UnformalizedAddressType_id', type: 'int'}
		],
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadUnformalizedAddressDirectory',
			reader: {
				type: 'json',
				root: 'data'
			},
			actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined

		}
	}),
	initComponent: function(){
		var combo = this;

		combo.store.proxy.extraParams = {
			'UnformalizedAddressType_id': combo.type_id
		};

		combo.callParent(arguments);
	}
});

//это комбик фильтра по ключевым событиям
Ext.define('swCmpCallCardEventTypeCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpCallCardEventTypeCombo',
	name: 'CmpCallCardEventType',
	cls: 'localCombo',
	typeAhead: true,
	queryMode: 'local',
	triggerClear: true,
	displayField:'CmpCallCardEventType_Name',
	valueField: 'CmpCallCardEventType_Name',
	//codeField: 'CmpCallCardEventType_Code',
	displayTpl: '<tpl for=".">{CmpCallCardEventType_Name}</tpl>',
	labelAlign: 'right',
	fieldLabel: 'Типы событий вызова',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'CmpCallCardEvent_id', type:'int'},
			{name: 'CmpCallCardEventType_Name', type:'string'},
			//{name: 'CmpCallCardEventType_Code', type:'string'}
		],
		sorters: [{
			property: 'code',
			direction: 'ASC'
		}],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			//noCache:false,
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEventType',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	/*tpl: [
		'<tpl for=".">',
		'<div class="x-boundlist-item">',
		'{name}',
		'</div></tpl>'
	],*/
	initComponent: function() {
		this.callParent(arguments)
	}
});

//Комбик результатов обслуживания НМП
Ext.define('swCmpPPDResultCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.swCmpPPDResultCombo',
	cls: 'localCombo',
	typeAhead: true,
	queryMode: 'local',
	triggerClear: true,
	autoFilter: false,
	allowBlank: false,
	displayField:'CmpPPDResult_Name',
	valueField: 'CmpPPDResult_id',
	displayTpl: '<tpl for=".">{CmpPPDResult_Name}</tpl>',
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'CmpPPDResult_id', type:'int'},
			{name: 'CmpPPDResult_Name', type:'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '/?c=CmpCallCard&m=getResults',
			reader: {
				type: 'json',
				successProperty: 'success',
				root: 'data'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	initComponent: function() {
		this.callParent(arguments)
	}
});

//класс переориентации компонентов из 6-ки
Ext.define('commonSprCombo', {
	extend: 'sw.commonSprCombo',
	alias: 'widget.commonSprCombo',
	cls: 'localComboMongo',
	initComponent: function() {
		var me = this;

		me.tableName = me.comboSubject;

		me.displayField = me.comboSubject + '_Name';

		if (Ext.isEmpty(me.valueField)) {
			me.valueField = me.comboSubject + '_id';
		}

		if (Ext.isEmpty(me.codeField)) {
			me.codeField = me.comboSubject + '_Code';
		}

		if (Ext.isEmpty(me.sysNickField)) {
			me.sysNickField = me.comboSubject + '_SysNick';
		}

		if (Ext.isEmpty(me.sortField)) {
			if (me.displayCode) {
				me.sortField = me.codeField;
			} else {
				me.sortField = me.displayField;
			}
		}

		if (Ext.isEmpty(me.fields)) {
			if (me.displayCode) {
				var displayField = me.displayField;
				me.fields = [
					{name: me.valueField, type:'int'},
					{name: me.displayField, type:'string'},
					{name: 'displayField', type:'string', convert: function(val,row) {
						if (row.get(me.valueField)) {
							return row.get(me.codeField) + '. ' + row.get(displayField);
						} else {
							return '';
						}
					}},
					{name: me.codeField, type:me.typeCode},
					{name: me.sysNickField, type:'string'}
				];

				me.displayField = 'displayField';
			} else {
				me.fields = [
					{name: me.valueField, type:'int'},
					{name: me.displayField, type:'string'},
					{name: me.codeField, type:me.typeCode},
					{name: me.sysNickField, type:'string'}
				];
			}
		}


		me.callParent(arguments);
	}
});

//Комбик для монгодб
Ext.define('sw.commonMongodbCombo',{
	extend: 'sw.commonSprCombo',
	alias: 'widget.commonMongodbCombo',
	cls: 'localComboMongo',
	initComponent: function() {
		var combo = this;
		if(combo.tableName) {
			combo.valueField = combo.tableName + '_id';
			combo.key = combo.valueField;
			combo.displayField = combo.tableName + '_Name';
			combo.codeField = combo.tableName + '_Code';
			combo.fields = [
				{ name: combo.valueField, type: 'int' },
				{ name: combo.codeField, type: 'string' },
				{ name: combo.displayField, type: 'string' }
			];

			if(combo.addFields) {
				combo.fields = combo.fields.concat(combo.addFields);
			}

			if(!combo.tpl) {
				combo.tpl = new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item">',
						'<font color="red">{'+combo.codeField+'}</font>&nbsp{',combo.displayField,'}</div></tpl>');
			}

		}

		combo.callParent(arguments);
	}
});

//Комбик для шкалл
Ext.define('sw.commonScaleCombo',{
	extend: 'sw.commonMongodbCombo',
	alias: 'widget.commonScaleCombo',
	cls: 'localComboMongo',
	autoFilter: false,
	forceSelection: true,
	editable: false,
	initComponent: function() {
		var combo = this;
		if(combo.tableName) {
			combo.pointField = combo.tableName + '_Value';
			combo.addFields = [ { name: combo.pointField, type: 'float' } ];
			combo.callParent(arguments);
			combo.tpl = new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item">{'
				,combo.displayField, '}&nbsp;({',combo.pointField, '})</div></tpl>');
		}
	},
	getPoints: function() {
		var combo = this,
			value = combo.getValue();
		if( !value ) return 0;

		var rec = combo.findRecord(combo.valueField, value);
		if( !rec ) return 0;

		return rec.get(combo.tableName + '_Value');
	}
});

Ext.define('sw.painResponseCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.painResponseCombo',
	tableName: 'PainResponse',
	fieldLabel: 'Реакция на боль'
});

Ext.define('sw.externalRespirationCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.externalRespirationCombo',
	cls: 'localComboMongo',
	tableName: 'ExternalRespiration',
	fieldLabel: 'Характер внешнего дыхания'
});

Ext.define('sw.arterialPressureCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.arterialPressureCombo',
	tableName: 'ArterialPressure',
	fieldLabel: 'Систолическое АД, мм рт. ст.'
});

Ext.define('sw.signsOfInternalBleedingCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.signsOfInternalBleedingCombo',
	tableName: 'SignsOfInternalBleeding',
	fieldLabel: 'Признаки внутреннего кровотечения'
});

Ext.define('sw.limbSeparationCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.limbSeparationCombo',
	tableName: 'LimbSeparation',
	fieldLabel: 'Отрыв конечности'
});

Ext.define('sw.faceAsymetryCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.faceAsymetryCombo',
	tableName: 'FaceAsymetry',
	fieldLabel: 'Ассиметрия лица'
});

Ext.define('sw.handHoldCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.handHoldCombo',
	tableName: 'HandHold',
	fieldLabel: 'Удержание рук'
});

Ext.define('sw.squeezingBrushCombo',{
	extend: 'sw.commonScaleCombo', 
	alias: 'widget.squeezingBrushCombo',
	tableName: 'SqueezingBrush',
	fieldLabel: 'Сжимание в кисти'
});


Ext.define('sw.PlaceArrivalCombo',{
	extend: 'sw.commonMongodbCombo',
	alias: 'widget.swPlaceArrivalCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Место прибытия',
	tableName: 'PlaceArrival',
});
Ext.define('sw.DyspneaCombo',{
	extend: 'sw.commonMongodbCombo',
	alias: 'widget.swDyspneaCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Место прибытия',
	tableName: 'Dyspnea',
});

Ext.define('sw.CoughCombo',{
	extend: 'sw.commonMongodbCombo',
	alias: 'widget.swCoughCombo',
	cls: 'localComboMongo',
	fieldLabel: 'Место прибытия',
	tableName: 'Cough',
});