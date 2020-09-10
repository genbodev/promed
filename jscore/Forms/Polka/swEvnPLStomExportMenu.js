/**
* swEvnPLStomExportMenu - окно настроек экспорта ТАП стоматолоии в DBF
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       shorev
* @version      11.11.2016
*/

 /*NO PARSE JSON*/
 
sw.Promed.swEvnPLStomExportMenu = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPLStomExportMenu',
	objectSrc: '/jscore/Forms/Polka/swEvnPLStomExportMenu.js',
	closable: false,
	width : 500,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['nastroyki_eksporta'],
	params: null,
	callback: Ext.emptyFn,
	mode: 'chbox',

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swEvnPLStomExportMenu.superclass.show.apply(this, arguments);		
		if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        } else {
			this.callback = Ext.emptyFn;
		}
		this.ChGroup.reset();
		this.RdGroup.reset();
		Ext.getCmp('eplstomChSelector').reset();
		Ext.getCmp('eplstomDateType').reset();
		Ext.getCmp('EPLStom_UseFilterGroup').items.items[0].setValue(true);
		Ext.getCmp('EPLStom_UseFilterGroup').items.items[1].setValue(false);
		this.buttons[0].enable();
	},

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var current_window = this;
		var fields_array = [
			{name: 'PACIENT', description: lang['baza_dannyih_o_zastrahovannom'] },
			{name: 'EvnPL', description: 'Сведения о лечении в поликлинике' },
			{name: 'EvnVizitPL', description: 'Сведения о посещении поликлиники' },
			{name: 'EvnUsluga', description: 'Сведения об оказанных услугах застрахованному'},
			{name: 'EvnAgg', description: 'Сведения об осложнениях'}
		];
		var chgroup_array = new Array();
		var rdgroup_array = new Array();
		
		for (i = 0; i < fields_array.length; i++) {
			chgroup_array.push({boxLabel: fields_array[i].name + ' ' + fields_array[i].description, name: 'CB', value: fields_array[i].name});
			rdgroup_array.push({boxLabel: fields_array[i].name + ' ' + fields_array[i].description, name: 'RB', value: fields_array[i].name, disabled: (i == 0)});
		}
		
		this.ChGroup = new Ext.form.CheckboxGroup({
			id:'epsemChGroup',
			xtype: 'checkboxgroup',
			hidden: false,
			hideLabel: true,
			style : 'padding: 5px; padding-bottom:1px;',
			itemCls: 'x-check-group-alt',			
			columns: 1,
			items: chgroup_array,
			getValue: function() {
				var out = [];
				this.items.each(function(item){
					if(item.checked){
						out.push(item.value);
					}
				});
				return out.join(',');
			}
		});
		
		this.RdGroup = new Ext.form.RadioGroup({
			id:'epsemRdGroup',
			xtype: 'radiogroup',
			hidden: true,
			hideLabel: true,
			style : 'padding: 5px; padding-top:1px;',
			itemCls: 'x-radio-group-alt',			
			columns: 1,
			items: rdgroup_array,
			getValue: function() {
				var out = [];
				this.items.each(function(item){
					if(item.checked){
						out.push(item.value);
					}
				});
				return out.join(',');
			}
		});
		
		this.GroupSelector = new Object({
			xtype: 'checkbox',
			hideLabel: true,			
			tabIndex: 1000,
			name: 'SB',
			id: 'eplstomChSelector',
			boxLabel: lang['vklyuchit_svedeniya_o_zastrahovannom_v_bazyi'],
			listeners: {
				check: function(box, checked) {
					if (checked) {						
						current_window.RdGroup.show();
						current_window.ChGroup.hide();
						current_window.mode = 'radio';
					} else {
						current_window.RdGroup.hide();
						current_window.ChGroup.show();
						current_window.mode = 'chbox';
					}
				}
			}
		});
	
		this.UseFilterGroup = new Object({
			xtype: 'radiogroup',
			hideLabel: true,
			id: 'EPLStom_UseFilterGroup',
			name: 'EPLStom_UseFilterGroup',
			columns: 1,
			items: [								
				{boxLabel: 'Экспорт данных с учетом фильтрации', name: 'ch', value: 1},
				{boxLabel: 'Экспорт всех данных', name: 'ch', value: 2}
			]
		});

    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
						id : 'EvnPLStomExportMenuForm',
						height : 330,
						layout : 'form',
						border : false,
						frame : true,
						style : 'padding: 10px',
						labelWidth : 1,
						items : [
							this.ChGroup,
							this.RdGroup,
							{	
								style : 'padding-left: 5px',
								layout : 'form',
								items: [this.GroupSelector]
							}, 
							{	
								style : 'padding-left: 5px',
								layout : 'form',
								items: [this.UseFilterGroup]
							},
							{	
								style : 'padding-left: 5px',
								layout : 'form',
								labelWidth : 280,
								items: [{
									id: 'eplstomDateType',
									xtype:'combo',
									store: new Ext.data.SimpleStore({
										id: 0,
										fields: [
											'code',
											'name'
										],								
										data: [
											['1', lang['tekuschaya_data']],
											['2', lang['data_okonchaniya_sluchaya']]
										]
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<font color="red">{code}</font>&nbsp;{name}',
										'</div></tpl>'
									),
									displayField: 'name',
									valueField: 'code',
									editable: false,
									allowBlank: false,
									mode: 'local',
									forceSelection: true,
									triggerAction: 'all',
									fieldLabel: lang['vyigrujat_svedeniya_o_zastrahovannom_na_datu'],							
									width:  150,
									value: '1',
									selectOnFocus: true
								}]
							}
						]
					})],
			buttons : [{
						text : lang['vyibrat'],
						iconCls : 'ok16',
						handler : function(button, event) {							
							var data = new Object();
							data.table_list = '';
							data.date_type = '1';
							
							data.date_type = Ext.getCmp('eplstomDateType').getValue();
							data.filter_type = Ext.getCmp('EPLStom_UseFilterGroup').items.items[0].checked?1:2;
							if (current_window.mode == 'chbox') {
								data.table_list = current_window.ChGroup.getValue();
							} else {
								data.table_list = 'ADD_PERSON,' + current_window.RdGroup.getValue();
							}

							if (data.table_list != '') {
								current_window.callback(data);
								current_window.hide();
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['dlya_eksporta_ne_vyibrano_ni_odnogo_razdela']);
							}
						}.createDelegate(this)
					}, {
						text : "Структура DBF",
						iconCls : 'help16',
						handler : function(button, event) {							
							window.open('/documents/EvnPL structure.xlsx', '_blank');
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
		sw.Promed.swEvnPLStomExportMenu.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});