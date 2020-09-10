/**
* swReportDBStructureOptionsWindow - окно настройки отчета о структуре базы данных промеда
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      27.05.2011
*/
/*NO PARSE JSON*/
sw.Promed.swReportDBStructureOptionsWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swReportDBStructureOptionsWindow',
	objectSrc: '/jscore/Forms/Common/swReportDBStructureOptionsWindow.js',
	
	border : false,
	closeAction :'hide',
	height: 500,
	width: 800,
	id: 'ReportDBStructureOptionsWindow',
	title: lang['otchet_o_strukture_bd'],
	layout: 'border',	
	//maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swReportDBStructureOptionsWindow.superclass.show.apply(this, arguments);
		this.center();
		this.maximize();	
		
		this.TablesGrid.loadData({
			globalFilters: {
				limit: 100,
				start: 0
			}
		});
		
		var base_form = Ext.getCmp('SelectTablesForm').getForm();		
        base_form.getEl().dom.action = "/?c=ReportDBStructure&m=getReport";
        base_form.getEl().dom.method = "post";
        base_form.getEl().dom.target = "_blank";
        base_form.standardSubmit = true;
	}, //end show()

	/**
	 * Конструктор
	 */
	initComponent: function() {
		this.TablesGrid = new sw.Promed.ViewFrame({
			id: 'rdbsowTablesGrid',
			region: 'center',
			title:lang['spisok_tablits'],
			dataUrl: '/?c=ReportDBStructure&m=getTablesGrid',
			paging: false,
			autoLoadData: false,
			stringfields:			
			[
				{ name: 'object_id', type: 'int', header: 'ID', key: true },
				{ name: 'checkbox', type: 'string', header: '', width:25  },
				{ name: 'schema_name', type: 'string', header: lang['shema'], width:75 },
				{ name: 'table_name', type: 'string', header: lang['imya_tablitsyi'], width:200 },
				{ name: 'description', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' }
			],
			actions:
			[
				{name:'action_add',  hidden: true, func:  function() {}.createDelegate(this)},
				{name:'action_edit', text:lang['otmetit_vse'], handler: function() { Ext.getCmp('SelectTablesForm').selectCheckboxAll(true); }.createDelegate(this)},
				{name:'action_view',  hidden: true, func:  function() {}.createDelegate(this)},
				{name:'action_delete', text:lang['snyat_vyidelenie'], handler: function() { Ext.getCmp('SelectTablesForm').selectCheckboxAll(false); }.createDelegate(this)}
			],
			listeners: {
				'resize': function() {}
			},
			onLoadData: function() { },
			onDblClick: function() { }
		});
		
    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
						id : 'SelectTablesForm',
						layout : 'border',
						region: 'center',
						border : false,
						labelWidth : 400,
						items : [{
								border: false,
								xtype: 'panel',
								region: 'center',
								layout: 'border',
								items: [this.TablesGrid]
							}, {
								border: false,
								xtype: 'panel',
								height: 65,
								region: 'south',
								layout: 'form',
								plain: false,
								bodyStyle : 'background:#DFE8F6; padding: 10px;',
								items: [
									{	
										name: 'SelectedObjects',
										xtype: 'hidden'
									}, {
										fieldLabel: lang['porog_otobrajeniya_dannyih_maksimalnoe_kolichestvo_strok'],
										name: 'MaxRows',
										width: 100,
										value: 10,
										maskRe: /[\d]+/,
										xtype: 'textfield',
										listeners: {
											'change': function(field, newValue, oldValue) {
												if(newValue > 500) {
													field.setValue(500);
												}
											}
										}
									}, {
										fieldLabel: lang['esli_dannyie_v_tablitse_prevyishayut_porog_otobrajeniya_to_vyivodit'],
										name: 'ShowRows',
										width: 100,
										value: 10,
										maskRe: /[\d]+/,
										xtype: 'textfield',
										listeners: {
											'change': function(field, newValue, oldValue) {
												if(newValue > 500) {
													field.setValue(500);
												}
											}
										}
									}
								]								
							}
						],
						selectCheckboxAll: function(select) {
							var input_array = document.getElementById('SelectTablesForm').getElementsByTagName('input');
							for(var key in input_array) {
								input_array[key].checked = select;
							}
						},
						collectCheckboxValues: function() {
							var input_array = document.getElementById('SelectTablesForm').getElementsByTagName('input');
							var tbl_str = '';
							for(var key in input_array) {
								var inp = input_array[key];
								if(inp.checked && inp.value) {
									tbl_str += (tbl_str != '' ? ',' : '') + inp.value;
								}
							}
							return tbl_str;
						}
					})],
			buttons : [{
						text : lang['vyibrat'],
						iconCls : 'ok16',
						handler : function(button, event) {
							var form = Ext.getCmp('SelectTablesForm');							
							form.getForm().findField('SelectedObjects').setValue(form.collectCheckboxValues());
							form.getForm().submit();
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
		sw.Promed.swReportDBStructureOptionsWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});