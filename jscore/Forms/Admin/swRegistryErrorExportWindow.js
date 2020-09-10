/**
* swRegistryErrorExportWindow - окно настроек экспорта протоколов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Alexandr Chebukin
* @version      19.09.2012
*/
 
sw.Promed.swRegistryErrorExportWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swRegistryErrorExportWindow',
	objectSrc: '/jscore/Forms/Admin/swRegistryErrorExportWindow.js',
	closable: false,
	width : 330,
	height : 150,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['eksport_protokolov'],
	params: null,
	callback: Ext.emptyFn,
	mode: 'chbox',
	show: function() {
		sw.Promed.swRegistryErrorExportWindow.superclass.show.apply(this, arguments);		
		if (!arguments || !arguments[0] || !arguments[0].Registry_id) {
			Ext.Msg.alert(lang['oshibka_otkryitiya_formyi'], lang['ne_ukazanyi_neobhodimyie_dannyie']);
			this.hide();
			return false;
		}
		
		if (arguments[0].Registry_id) {
            this.Registry_id = arguments[0].Registry_id;
        } else {
			this.Registry_id = null;
		}
		
		if (arguments[0].RegistryErrorTFOMSType_id ) {
            this.RegistryErrorTFOMSType_id  = arguments[0].RegistryErrorTFOMSType_id ;
        } else {
			this.RegistryErrorTFOMSType_id = null;
		}		
		
		if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        } else {
			this.callback = Ext.emptyFn;
		}
		
		this.findById('rexmExportType').reset();
		this.findById('rexmExportFormat').reset();
		this.findById('rexmExportFormat').disable();
		this.findById('rexmErrorLevel').reset();
		this.findById('rexmErrorLevelPanel').hide();
		this.setHeight(150);
		this.buttons[0].enable();
	},
	initComponent: function() {
		var current_window = this;
	
    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
				id : 'RegistryErrorExportMenuForm',
				//height : 110,
				autoHeight: true,
				layout : 'form',
				border : false,
				frame : true,
				style : 'padding: 10px',
				labelWidth : 1,
				items : [{
					style : 'padding-left: 5px',
					layout : 'form',
					labelWidth : 120,
					items: [{
						id: 'rexmExportType',
						xtype:'combo',
						store: new Ext.data.SimpleStore({
							id: 0,
							fields: [
								'code',
								'name'
							],								
							data: [
								['1', lang['flk']],
								['2', lang['bdz']],
								['3', lang['mek']]
							]
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{code}</font>&nbsp;{name}',
							'</div></tpl>'
						),
						listeners: {
							change: function(combo, newValue) {
								if (newValue == 3) {
									current_window.findById('rexmErrorLevelPanel').show();
									current_window.setHeight(180);
								} else {
									current_window.findById('rexmErrorLevelPanel').hide();
									current_window.setHeight(150);									
								}
								
							}
						},
						displayField: 'name',
						valueField: 'code',
						editable: false,
						allowBlank: false,
						mode: 'local',
						forceSelection: true,
						triggerAction: 'all',
						fieldLabel: lang['tip_protokola'],							
						width:  150,
						value: '1',
						selectOnFocus: true
					}, {
						id: 'rexmExportFormat',
						xtype:'combo',
						store: new Ext.data.SimpleStore({
							id: 0,
							fields: [
								'code',
								'name'
							],								
							data: [
								['1', 'XLS'],
								['2', 'XML']
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
						fieldLabel: lang['format_vyigruzki'],							
						width:  150,
						value: '1',
						selectOnFocus: true
					}, {
						id: 'rexmErrorLevelPanel',
						layout : 'form',
						labelWidth : 120,
						items: [{
							id: 'rexmErrorLevel',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'code',
									'name'
								],								
								data: [
									['1', lang['oshibka']],
									['2', lang['preduprejdenie']]
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
							fieldLabel: lang['uroven_oshibki'],							
							width:  150,
							value: '1',
							selectOnFocus: true
						}]						
					}]
				}]
			})],
			buttons : [{
				text : lang['eksport'],
				iconCls : 'ok16',
				handler : function(button, event) {	
					
					var export_type = null;
					switch (this.findById('rexmExportType').getValue()) {
						case '1': //ФЛК
							export_type = 'ErrorFLK_List.rptdesign';
							break;
						case '2': //БДЗ
							export_type = 'ErrorBDZ_List.rptdesign';
							break;
						case '3': //МЭК
							export_type = 'ErrorMEK_List.rptdesign';
							break;
						default:
							Ext.Msg.alert(lang['oshibka'], lang['ne_ukazan_tip_protokola']);
							return false;
							break;
					}
					
					var format_type = null;
					var error_level = this.findById('rexmErrorLevel').getValue();
					switch (this.findById('rexmExportFormat').getValue()) {
						case '1': //XLS
							format_type = 'xls';
							break;
						case '2': //XML
							format_type = 'xml';
							break;
						default:
							format_type = 'xls';
							break;
					}

					printBirt({
						'Report_FileName': export_type,
						'Report_Params': '&paramRegistry_num=' + this.Registry_id + (( export_type == 'ErrorMEK_List.rptdesign' ) ? '&paramRegistryErrorTFOMSLevel=' + error_level : ''),
						'Report_Format': format_type
					});
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
		sw.Promed.swRegistryErrorExportWindow.superclass.initComponent.apply(this, arguments);
	} 
});