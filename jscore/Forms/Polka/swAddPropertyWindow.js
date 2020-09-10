/**
* swAddPropertyWindow - окно добавления показателя
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Salakhov Rustam
* @version      06.07.2010
*
*
*
* Используется: RateGrid
*/

/**
 * swAddPropertyWindow - окно добавления показателя
 *
 * @class sw.Promed.swAddPropertyWindow
 * @extends Ext.Window
 */
sw.Promed.swAddPropertyWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 600,
	height : 120,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['dobavlenie_pokazatelya'],

	params: null,
	
	onSelect: function(params) { alert(0); },
	
	referenceData: new Object(), //массив для сохранения справочников
	
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swAddPropertyWindow.superclass.show.apply(this, arguments);		
		if (arguments[0]) {			
			if (arguments[0].onSelect) {				
				this.onSelect = arguments[0].onSelect;
			}
			if (arguments[0].params) {				
				this.params = arguments[0].params;
			}
		}
		
		/*this.AddPropertyForm.getForm().findField('APW_PropertyType').getStore().load({
			url: '?c=Rate&m=index&method=loadRateList',
			params: {
				//parmname: 'value'
			},
			success: function (result_form, action) {}
		});*/
		
		this.buttons[0].enable();
	}, //end show()

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var ths_el = this;
	
		this.AddPropertyForm = new Ext.form.FormPanel({
			id : 'AddPropertyForm',
			height : 60,
			layout : 'form',
			border : false,
			frame : true,
			style : 'padding: 10px',
			labelWidth : 120,
			items : [{
				layout: 'column',
				border: false,								
				autoHeight: true,
				labelWidth: 70,
				items: [{								
					layout: 'form',
					border: false,
					items: [
						new Ext.form.ComboBox({
							fieldLabel: lang['pokazatel'],
							id: 'APW_PropertyId',
							name: 'PropertyId',
							typeAhead: true,
							allowBlank: false,
							triggerAction: 'all',
							lazyRender: true,
							mode: 'local',
							store: new Ext.data.Store({
								autoLoad: true,//false,
								reader: new Ext.data.JsonReader({
									id: 'rate_id'
								}, [
									{ name: 'rate_id', mapping: 'rate_id' },									
									{ name: 'rate_name', mapping: 'rate_name' },
									{ name: 'rate_type', mapping: 'rate_type' }
								]),								
								sortInfo: {
									field: 'rate_name'
								},
								url: '/?c=Rate&m=loadRateList'
							}),
							listeners: {
								select: function(f,r,i){
									Ext.getCmp('APW_Type_int').hide();
									Ext.getCmp('APW_Type_float').hide();
									Ext.getCmp('APW_Type_string').hide();
									Ext.getCmp('APW_Type_template').hide();
									Ext.getCmp('APW_Type_reference').hide();									
									var fld = Ext.getCmp('APW_Type_' + r.get('rate_type'));									
									if (fld) {
										fld.setValue(null);
										if (r.get('rate_type') == 'reference') {
											fld.getStore().load({ params: { ratetype_id: r.get('rate_id') } });
										}
										fld.show();
										Ext.getCmp('APW_PropertyValueText').show();
										Ext.getCmp('APW_PropertyName').setValue(r.get('rate_name'));
										Ext.getCmp('APW_PropertyType').setValue(r.get('rate_type'));
									} else {
										Ext.getCmp('APW_PropertyValueText').hide();
									}									
								}
							},						
							valueField: 'rate_id',
							displayField: 'rate_name'
						})
					]
				}, {
					id: 'APW_PropertyValueText',
					hidden: true,
					html: '<div style="display; inline; width:66px; padding: 3px 3px 3px 15px; font-size:12px;">Значение :</div>'
				}, {
					//layout: 'form',
					border: false,
					items: [{
							id: 'APW_PropertyType',
							hidden: true,
							xtype: 'hidden'
						}, {
							id: 'APW_PropertyName',
							hidden: true,
							xtype: 'hidden'
						}, {
							id: 'APW_Type_int', //целое
							hidden: true,
							xtype: 'textfield',
							value: 'text',
							maskRe: /[0-9]/
						}, {
							id: 'APW_Type_float', //вещественное
							hidden: true,
							xtype: 'textfield',
							value: '',
							maskRe: /[0-9\.]/
						}, {
							id: 'APW_Type_string', //строка
							hidden: true,
							xtype: 'textfield'
						}, {
							id: 'APW_Type_template', //ввод по шаблону
							hidden: true,
							xtype: 'textfield'
						}, 
						new Ext.form.ComboBox({
							id: 'APW_Type_reference', //справочник							
							width: 125,
							hidden: true,							
							typeAhead: true,
							triggerAction: 'all',
							lazyRender: true,
							readOnly: true,
							mode: 'local',
							store: new Ext.data.Store({
							autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'value_id'
								}, [
									{ name: 'value_id', mapping: 'value_id' },									
									{ name: 'value_name', mapping: 'value_name' }
								]),
								sortInfo: {
									field: 'value_name'
								},
								listeners: {
									load: function(data) {
										var property_id = Ext.getCmp('APW_PropertyId').getValue();
										if (property_id > 0 && ths_el.referenceData[property_id] == null) {
											var arr = data.data.items;
											var valarr = new Object();
											for(var i = 0; i < arr.length; i++) {
												valarr[arr[i].data['value_id']] = arr[i].data['value_name'];
											}											
											ths_el.referenceData[property_id] = valarr;
										}
									}
								},
								url: '/?c=Rate&m=loadRateValueList'
							}),
							valueField: 'value_id',
							displayField: 'value_name'
						})						
					]
				}]
			}]
		});
	
    	Ext.apply(this, {
			items : [this.AddPropertyForm],
			buttons : [{
						text : "Выбрать",
						iconCls : 'ok16',
						handler : function(button, event) {							
							var params = new Object();
							params.type = Ext.getCmp('APW_PropertyType').getValue();
							params.value = params.type != '' ? Ext.getCmp('APW_Type_' + params.type).getValue() : null;
							params.name = Ext.getCmp('APW_PropertyName').getValue();
							params.id = Ext.getCmp('APW_PropertyId').getValue();
							params.state = 'add';
							
							switch(params.type) {
								case 'int':										
									params.editor = new Ext.form.TextField({
										maskRe: /[0-9]/
									});
									break;
								case 'float':									
									params.editor = new Ext.form.TextField({
										maskRe: /[0-9\.]/
									});
									break;
								case 'reference':
									params.editor = new Ext.form.ComboBox({															
										typeAhead: true,
										triggerAction: 'all',
										lazyRender: true,
										readOnly: true,
										mode: 'local',
										store: new Ext.data.Store({
											autoLoad: true,
											reader: new Ext.data.JsonReader({
												id: 'value_id'
											}, [
												{ name: 'value_id', mapping: 'value_id' },									
												{ name: 'value_name', mapping: 'value_name' }
											]),
											sortInfo: {
												field: 'value_name'
											},
											url: '/?c=Rate&m=autoLoadRateValueList&ratetype_id=' + params.id
										}),
										valueField: 'value_id',
										displayField: 'value_name'
									});
									
									var property_id = Ext.getCmp('APW_PropertyId').getValue();
									params.refdata = ths_el.referenceData[property_id];									
									break;
								case 'reference':
									params.editor = new Ext.form.TextField({});
									break;
								default:
									params.editor = new Ext.form.TextField({});
							}
							
							if (this.params.rate_names) { //проверяем есть ли уже в гриде выбранный показатель
								if(params.id > 0) {
									if (('||' + this.params.rate_names.join('||') + '||').indexOf('||' + params.name + '||') >= 0) {
										Ext.MessageBox.show({
											title: "Предупреждение",
											msg: "Данный показатель уже добавлен в таблицу.",
											buttons: Ext.Msg.OK,
											icon: Ext.Msg.WARNING,
											fn: function() {
												//
											}
										});
										//alert('||' + this.params.rate_names.join('||') + '||');
										return false;
									}
								} else {
									Ext.MessageBox.show({
										title: "Ошибка",
										msg: "Необходимо выбрать показатель из списка.",
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										fn: function() { }
									});
								}
							}
							
							if (params.value != null) {								
								this.onSelect(params);
								this.hide();
							}
						}.createDelegate(this)
					}, {
						text: '-'
					}, {
						handler: function() 
						{
							this.ownerCt.hide();
						},
						iconCls: 'close16',
						text: BTN_FRMCLOSE
					}],
			buttonAlign : "right"
		});
		sw.Promed.swAddPropertyWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});