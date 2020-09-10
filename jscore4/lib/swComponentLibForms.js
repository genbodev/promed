/**
* sw.Promed.FormPanelWithChangeEvents - класс формы с обработкой события изменения поля.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*/

/*
Terms:
stateCombo - комбобокс в предустановленными значениями (не загружается НИКОГДА)
localCombo - обычний комбо, загружается при загрузке
loadAfter - загрузка ручками
localComboMongo - справочник монго (грузится в BaseForm)
dinamicCombo - динамическая загрузка (при редактировании)
 */


//это комбик динамической загрузки городов и нас пунктов и тп
Ext.define('sw.dCityCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.dCityCombo',
	plugins: [new Ux.Translit(true, true)],
	name: 'dCityCombo',
	cls: 'dinamicCombo',
	displayField: 'Town_Name',
	valueField:'Town_id',
	fieldLabel: 'Нас. пункт',
	labelAlign: 'right',
	
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Town_Name', type:'string'},
			{name: 'Town_id', type:'int'},
			{name: 'Region_Socr', type:'string'},
			{name: 'Region_Name', type:'string'},
			{name: 'Socr_Name', type: 'string'},
			{name: 'Socr_Nick', type: 'string'},
			{name: 'Region_id', type: 'string'}
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
			}
		},
		filter: Ext.emptyFn,
		clearFilter: Ext.emptyFn,
		regionFilter: function (regionCode) {
			Ext.data.Store.prototype.clearFilter.call(this);
			Ext.data.Store.prototype.filter.call(this, 'Town_id', regionCode);
		}
	}),

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
        var me = this
		me.displayTpl = new Ext.XTemplate(
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
	cls: 'dinamicCombo',
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
//		sorters: {
//			property: 'StreetAndUnformalizedAddressDirectory_Name'
//			,direction: 'ASC'
//		},
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
        var me = this
		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				//'{[typeof values === "string" ? values : values["' + me.displayField + '"]]}' +
				'{[(values.StreetAndUnformalizedAddressDirectory_Name && values.Socr_Nick != "") ? values.Socr_Nick + " " + values.StreetAndUnformalizedAddressDirectory_Name : values.Town_Name ]}',
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );
	me.callParent();
	}
})


//это комбик лпу прикрепления

Ext.define('sw.lpuLocalCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.lpuLocalCombo',
	name: 'lpuLocalCombo',
	cls: 'localCombo',
	queryMode: 'local',
	store: new Ext.data.JsonStore({
		autoLoad: false,
		fields: [
			{name: 'Lpu_id', type:'int'},
			{name: 'Lpu_Name', type:'string'},
			{name: 'Lpu_Nick', type:'string'}
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
				'Object': 'LpuWithMedServ', 				
				'MedServiceType_id': 18
			}
		}
	}),
	listeners: {
		change: function(c, newV, oldV, o){
			if (!oldV){
				rec = c.store.findRecord('Lpu_id', newV)
				if (rec){c.setValue(rec.get('Lpu_id'))}
			}
		}
	},
	displayField:'Lpu_Nick',
	valueField: 'Lpu_id',
	labelAlign: 'right',
	fieldLabel: 'ЛПУ передачи',
//	disabled: true,
	tpl: '<tpl for=".">' +
		'<div class="x-boundlist-item">' +
		'{Lpu_Nick}' +
		'</div></tpl>'
})


//поле для ввода с возможностью конвертации символов и с кнопкой удалить
//translate(bool) - параметр для конвертации
//помимо этого добавлен функционал typeAhead, с возможностью подключения store через storeId(store дб загружен)
Ext.define('sw.transFieldDelbut', {
	requires: ['Ext.data.StoreManager'], 
	extend: 'Ext.form.field.Trigger', 
	alias: 'widget.transFieldDelbut',
	fieldLabel: 'label',
	displayField: '',
	valueField: null,
	labelAlign: 'right',
	labelWidth: 60,
	translate: true,
	fieldStyle:	{padding: '1px 21px 1px 3px'},	
	triggerBaseCls: 'clearTextfieldButton',
	enableKeyEvents: true,

	onTriggerClick: function() {
		this.setValue('');
		this.selectedRecord = null;
		this.fireEvent('triggerClick');
    },
	getTriggerMarkup: function() {
		var trig = '<td role="presentation" valign="top" class=" x-unselectable"><div class="x-trigger-index-0 clearTextfieldButton" role="presentation" style="display: none;"></div></td>'
		return trig;
    },
	typeAhead: function(){
		if (typeof this.store != 'undefined')
		{
			var me = this,
				displayField = me.displayField,
				record = me.store.findRecord(displayField, me.getRawValue(), 0, true);
			
			if (record)
			{
				var newValue = record.get(displayField),
					len = newValue.length,
					selStart = me.getRawValue().length;
					
					if (selStart>2){						
						Ext.defer(function() {
							if (selStart < me.getRawValue().length)
							{
								
								me.setRawValue(newValue);
								me.selectText(selStart, newValue.length);
								if (me.valueField){

									me.selectedRecord = record
								}
								me.fireEvent('addFullText', newValue);
							}else{		
						me.selectedRecord = null
					}
						}, 300)
					}
					else{		
						me.selectedRecord = null
					}
			}
			else{		
				me.selectedRecord = null
			}
		}
	},
	checkTriggerButton: function(){
		if(!this.readOnly)
		{
			if ((this.getRawValue().length > 0))
			{
				if (!this.triggerEl.elements[0].isVisible())
				{this.triggerEl.show(true)}
			}
			else{this.triggerEl.hide(true)}
		}
	},
	initComponent: function() {
        var me = this
		
		me.addEvents({
			addFullText: true,
			triggerClick: true
		});
		
		if (me.store)
		{me.store = Ext.data.StoreManager.lookup(me.store)}
		if (me.translate)
		{me.plugins = [new Ux.Translit(true, true)]}
		
		me.on('keydown', function(){
			me.typeAhead()
			me.checkTriggerButton()
		})
		me.on('focus', function(){
			me.checkTriggerButton()
		})
		me.on('blur', function(){
			if (this.triggerEl.elements[0].isVisible())
				this.triggerEl.hide(true)
		})
		me.on('render', function(cmp, opts){
			cmp.mon(cmp.el, 'mouseover', function (event, html, eOpts) {
				me.checkTriggerButton()
			})
			cmp.mon(cmp.el, 'mouseleave', function (event, html, eOpts) {
				if (me.triggerEl.elements[0].isVisible())
				me.triggerEl.hide(true)
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
	cls: 'dinamicCombo',
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
	cls: 'dinamicCombo',
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

//это комбик бригады смп

Ext.define('sw.smpAmbulanceTeamCombo', {
	extend: 'Ext.form.ComboBox', 
	alias: 'widget.smpAmbulanceTeamCombo',
	name: 'smpAmbulanceTeamCombo',
	cls: 'localCombo',
	queryMode: 'local',
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
	}),
	listeners: {
		change: function(c, newV, oldV, o){
			if (!oldV){
				var rec = c.store.findRecord('Lpu_id', newV);
				if (rec){c.setValue(rec.get('Lpu_id'))}
			}
		}
	},
	displayField:'EmergencyTeam_Name',
	valueField: 'EmergencyTeam_id',
	labelAlign: 'right',
	fieldLabel: 'Бригада СМП',
//	disabled: true,
	tpl: '<tpl for=".">' +
		'<div class="x-boundlist-item">' +
		'<font color="red">{EmergencyTeam_Code}</font>' +' '+ '{EmergencyTeam_Name}'+
		'</div></tpl>'
})


//комбобокс общего типа (он сам по себе)
//при инициализации надо указать:
//-fields(они же baseparams)
//-tableName()
//-triggerFind(кнопка поиска)
//-triggerClear(кнопка очистки)
//-translate(транслитерация на русский zpsr)

Ext.define('sw.сommonSprCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.сommonSprCombo',
	cls: '',
	queryMode: 'local',
	triggerClear: true,
	triggerFind: false,
	autoFilter: true,
	enableKeyEvents: true,
	store: null,
	translate: true,

	initComponent: function() {
        var me = this;
		
		this.addEvents({
			autoSelect: true
		});
		
		//изменяем поиск на typeAhead
		if (me.typeAhead && me.displayField){
			Ext.override( me, {
				onTypeAhead: function() {
					var me = this,
						displayField = me.displayField,
						boundList = me.getPicker(), record,
						newValue, len, selStart, indexInputSrting;
					
					if (me.bigStore){
						record = me.bigStore.findRecord( me.codeField, me.getRawValue(), 0, true);
						
						me.store.removeAll();
						me.store.add(me.bigStore.query(me.codeField, me.getRawValue(), true, false, true).items);
					}
					else{
						me.store.clearFilter();
						if (me.autoFilter){							
							me.store.filter(me.codeField, new RegExp(me.getRawValue(), "i"));
						}						
						record = me.store.findRecord( me.codeField, me.getRawValue(), 0, true);
					}

					if(!record){
						if (me.bigStore){
							record = me.bigStore.findRecord( displayField, me.getRawValue(), 0, true);						
							me.store.removeAll();
							me.store.add(me.bigStore.query(displayField, me.getRawValue(), true, false, true).items);
						}
						else{
							me.store.clearFilter();
							record = me.store.findRecord( displayField, me.getRawValue());
							console.log(me.autoFilter)
							if (me.autoFilter){
								me.store.filter(displayField, new RegExp(me.getRawValue(), "i"));
							}
						}
						
					}
					
					if (record) {
						newValue = record.get(displayField);
						
						indexInputSrting = newValue.indexOf(me.getRawValue());
						
						if ( indexInputSrting == 0 ){
							len = newValue.length;
							selStart = me.getRawValue().length;
							
							if (selStart !== 0 && selStart !== len) {							
								me.setRawValue(newValue);
								me.selectText(selStart, len);
							}
						}
						else{
							len = newValue.length;
							selStart = indexInputSrting+me.getRawValue().length;							
						}						
						
						boundList.highlightItem(boundList.getNode(record));
						if (me.store.count()==1){
							me.setValue(record.get(me.valueField), true);
							this.fireEvent('autoSelect', me, record);
						}
					}
					else{
						if (newValue==''){
							me.store.clearFilter();
						}						
					}
				}
			}
			)
		}	
		
		if (me.translate)
		{me.plugins = [new Ux.Translit(true, true)]}
		
		me.on('keydown', function(cmp, e, eOpts){
			if(cmp.getRawValue())
			{	if (!me.triggerEl.elements[0].isVisible())
				{me.triggerEl.elements[0].show(true)}
			}
			else{
				me.triggerEl.elements[0].hide(true)
				this.store.clearFilter()
			}
		})
		

		
		me.on('focus', function(){
			me.checkTriggerButton()
		})
		me.on('blur', function(){
			if (this.triggerEl.elements[0].isVisible())
				this.triggerEl.elements[0].hide(true)
		})
		me.on('render', function(cmp, opts){
			cmp.mon(cmp.el, 'mouseover', function (event, html, eOpts) {
				me.checkTriggerButton()
			})
			cmp.mon(cmp.el, 'mouseleave', function (event, html, eOpts) {
				if (me.triggerEl.elements[0].isVisible())
				me.triggerEl.elements[0].hide(true)
			})
		})
		//
		var clrButt = '',
		findButt = '',
		inputInnerRightPadding = 0;
		
		if (me.fields)
		{
			me.store = Ext.create ('Ext.data.Store', {
				fields: me.fields,

				sorters: {
					property: me.valueField,
					direction: 'ASC'
				},
				url : '/?c=MongoDBWork&m=getData'
			})
		}

		if (!me.tpl && me.codeField){
		me.tpl = Ext.create('Ext.XTemplate', 
			'<tpl for=".">' +
			'<div class="x-boundlist-item">' +
			'<font color="red">{'+me.codeField+'}</font> {'+ me.displayField + '}'+
		'</div></tpl>')
		}
		
		//создаем кастомный темплэйт
		if(me.triggerClear)
		{
			if (me.hideTrigger) {inputInnerRightPadding += 6}
			else {inputInnerRightPadding += 21}
			clrButt = '<div style="position: absolute; height: 100%; display: table-cell; right: '+inputInnerRightPadding+'px; top: 3px; border: none;" >'
				+ '<div style="vertical-align: middle; display: none;" class="x-trigger-index-2 x-form-trigger clearTextfieldButton"></div>'
				+ '<div style="display: inline-block; vertical-align: middle; height: 100%; width: 0px;"></div>'
			+'</div>';
		}
		
		if(me.triggerFind)
		{
			inputInnerRightPadding += 21
			findButt = '<div style="position: absolute; right: -17px; top: 0; " class="x-trigger-index-1 x-form-trigger x-form-search-trigger" ></div>'
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
			resizable: true
		}
		
		me.fieldStyle =	{padding: '1px '+inputInnerRightPadding+'px 1px 3px'}
		
		Ext.applyIf(me)
		
		me.callParent(arguments)
	},
	
	checkTriggerButton: function(){
		if(!this.readOnly)
		{
			if ((this.getRawValue().length > 0))
			{
				if (!this.triggerEl.elements[0].isVisible())
				{this.triggerEl.elements[0].show(true)}
			}
			else{
				this.triggerEl.elements[0].hide(true)
				this.store.clearFilter()
			}
		}
	},	
	onTrigger2Click: function(e) {
		//Ext.create('sw.tools.subtools.swDrugPrepWinSearch').show()
	},
	onTrigger3Click: function(e) {
		this.clearValue();
		this.store.clearFilter();
		this.focus();
	}
})


//***
//производные от сommonSprCombo
//***

//профиль бригады СМП

Ext.define('swEmergencyTeamSpecCombo', {
	extend: 'sw.сommonSprCombo', 
	alias: 'widget.swEmergencyTeamSpecCombo',
	name: 'swEmergencyTeamSpecCombo',
	//
	cls: 'localComboMongo',
	xtype: 'сommonSprCombo',
	tableName: 'EmergencyTeamSpec',
	displayField:'EmergencyTeamSpec_Name',
	valueField: 'EmergencyTeamSpec_id',
	codeField: 'EmergencyTeamSpec_Code',
	triggerClear: true,
	editable: false,
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
	extend: 'sw.сommonSprCombo', 
	alias: 'widget.swEmergencyFIOCombo',
	//
	cls: 'loadAfter',
	displayField: 'MedPersonal_Fio',
	valueField: 'MedPersonal_id',
	triggerClear: true,
	editable: true,
	typeAhead: true
})


//марка горючего
Ext.define('swWaybillGasCombo',{
	extend: 'sw.сommonSprCombo', 
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

Ext.define('swСmpCallTypeCombo',{
	extend: 'sw.сommonSprCombo',
	alias: 'widget.swСmpCallTypeCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	autoFilter: false,
	fieldLabel: 'Тип вызова',
	labelAlign: 'right',
	triggerClear: true,
	translate: false,
	tableName: 'CmpCallType',
	valueField: 'CmpCallType_id',
	displayField: 'CmpCallType_Name',
	codeField: 'CmpCallType_Code',
	fields: [
		{name: 'CmpCallType_id', type:'int'},
		{name: 'CmpCallType_Name', type:'string'},
		{name: 'CmpCallType_Code', type:'int'}
	],
	displayTpl: '<tpl for="."> {CmpCallType_Code}. {CmpCallType_Name} </tpl>'
});

//это комбик для пола
Ext.define('sw.sexCombo', {
	extend: 'sw.сommonSprCombo',
	alias: 'widget.sexCombo',
	cls: 'localComboMongo',
	typeAhead: true,
	tableName: 'Sex',
	translate: false,
	name: 'sexCombo',
	displayField: 'Sex_Name',
	valueField: 'Sex_id',
	codeField: 'Sex_Code',
	editable: true,	
	fieldLabel: 'Пол',
	labelAlign: 'right',
	fields: [
		{name: 'Sex_id', type:'int'},
		{name: 'Sex_Name', type:'string'},
		{name: 'Sex_Code', type:'string'}
	],
})

//повод

Ext.define('sw.cmpReasonCombo',{
	extend: 'sw.сommonSprCombo',
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
	codeField: 'CmpReason_Code',
	displayField: 'CmpReason_Name',
	displayTpl: '<tpl for="."> {CmpReason_Code}. {CmpReason_Name} </tpl>',
	fields: [
		{name: 'CmpReason_id', type:'int'},
		{name: 'CmpReason_Name', type:'string'},
		{name: 'CmpReason_Code', type:'string'}
	],
	initComponent: function() {
		var cmp = this;
		
//		cmp.on('change', function(c, newV, oldV, o){
//			c.store.clearFilter()
//			if (!oldV){
//				rec = c.store.findRecord('CmpReason_id', newV)
//				if (rec){c.setValue(rec.get('CmpReason_id'))}
//			}
//		});

//		cmp.on('keyup', function( c, e, eOpts ){
//			var rec = c.store.findRecord('CmpReason_Code', c.getValue())
//			if (rec && ((e.getKey() != 9) && (e.getKey() != 13)))
//			{
//				//console.log(rec)
//				c.setValue(rec.get('CmpReason_id'))
//			}
//		});
		
				
		Ext.applyIf(cmp);
		cmp.callParent(arguments)
	}
});

//тестовый улиц

Ext.define('sw.streetsSpeedCombo', {
	extend: 'sw.сommonSprCombo', 
	alias: 'widget.swStreetsSpeedCombo',
	cls: 'loadAfter',
	triggerClear: true,
	editable: true,
	minChars: 2,
	hideTrigger:true,

	typeAhead:true,
	typeAheadDelay: 0,
	displayField:'StreetAndUnformalizedAddressDirectory_Name',
	valueField: 'StreetAndUnformalizedAddressDirectory_id',
	fieldLabel: 'Улица / Объект',
	queryMode: 'local',
	store: new Ext.data.Store({
		fields: [
			{name: 'StreetAndUnformalizedAddressDirectory_id', type:'string'},
			{name: 'UnformalizedAddressDirectory_id', type:'int'},
			{name: 'UnformalizedAddressType_id', type:'int'},
			{name: 'StreetAndUnformalizedAddressDirectory_Name', type:'string'},
			{name: 'KLStreet_id', type:'int'},
			{name: 'Socr_Nick', type:'string'},
			{name: 'lat', type:'string'},
			{name: 'lng', type:'string'},
			{name: 'Lpu_id', type: 'int'}
		]
	}),
	tpl: new Ext.XTemplate('<tpl for="."><div class="x-boundlist-item">',
		'{StreetAndUnformalizedAddressDirectory_Name} <span style="color:gray">{Socr_Nick}</span>'+
		'</div></tpl>'
	),

	initComponent: function() {
        var me = this;
		me.displayTpl = new Ext.XTemplate(
			'<tpl for=".">' +
				'{[(values.StreetAndUnformalizedAddressDirectory_Name && values.Socr_Nick != "") ? values.Socr_Nick + " " + values.StreetAndUnformalizedAddressDirectory_Name : values.Town_Name ]}',
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
			'</tpl>'
        );

	
	me.bigStore = new Ext.data.JsonStore({
			autoLoad: false,
			fields: [
				{name: 'StreetAndUnformalizedAddressDirectory_id',    type:'string'},
				{name: 'UnformalizedAddressDirectory_id',    type:'int'},
				{name: 'UnformalizedAddressType_id', type:'int'},
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
	extend: 'sw.сommonSprCombo', 
	alias: 'widget.swEmergencyTeamWialonCombo',
	cls: 'localCombo',
	triggerClear: true,
	editable: true,
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


Ext.define('sw.TypeOfUnformalizedAddress',{
	extend: 'sw.сommonSprCombo',
	alias: 'widget.swTypeOfUnformalizedAddress',
	name: 'UnformalizedAddressTypeCombo',
	displayField:'UnformalizedAddressType_Name',
	valueField: 'UnformalizedAddressType_Name',
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
	extend: 'sw.сommonSprCombo',
	name: 'SmpUnitsCombo',
	displayField:'LpuBuilding_Name',
	valueField: 'LpuBuilding_id',
	cls:'localCombo',
	fieldLabel: 'Подразделение СМП',
	typeAhead: true,
	triggerClear: true,
	translate: false,
	store: new Ext.data.JsonStore({
		autoLoad: true,
		fields: [
			{name: 'LpuBuilding_id', type: 'int'},
			{name: 'LpuBuilding_Name', type: 'string'},					
			{name: 'LpuBuilding_Nick', type: 'string'}
		],
		proxy: {
			limitParam: undefined,
			startParam: undefined,
			paramName: undefined,
			pageParam: undefined,
			type: 'ajax',
			url: '/?c=CmpCallCard4E&m=loadSmpUnits',
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
				property: 'LpuBuilding_Nick',
				direction: 'ASC'
			}
		}
	})
})


//тип вызывающего

Ext.define('sw.CmpCallerTypeCombo', {
	extend: 'sw.сommonSprCombo', 
	alias: 'widget.swCmpCallerTypeCombo',
	name: 'swCmpCallerTypeCombo',
	//
	cls: 'localComboMongo',
	xtype: 'сommonSprCombo',
	tableName: 'CmpCallerType',
	displayField:'CmpCallerType_Name',
	valueField: 'CmpCallerType_id',
	typeAhead: true,
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
	extend: 'sw.сommonSprCombo',
	alias: 'widget.swCmpCallPlaceType',
	name: 'CmpCallPlaceType',
	displayField:'CmpCallPlaceType_Name',
	codeField: 'CmpCallPlaceType_Code',
	valueField: 'CmpCallPlaceType_id',
	cls: 'localComboMongo',
	xtype: 'сommonSprCombo',
	tableName: 'CmpCallPlaceType',
	typeAhead: true,
	triggerClear: true,
	editable: true,
	autoFilter: false,
	translate: false,
	fields: [
		{name: 'CmpCallPlaceType_id', type:'int'},
		{name: 'CmpCallPlaceType_Code', type:'string'},
		{name: 'CmpCallPlaceType_Name', type:'string'},
	]
})