/**
* swEmkDocumentsListWindow - Cписок документов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Permyakov Alexander
* @version      22.10.2012
*/
/*NO PARSE JSON*/
sw.Promed.swEmkDocumentsListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEmkDocumentsListWindow',
	objectSrc: '/jscore/Forms/Common/swEmkDocumentsListWindow.js',
	height: 500,
	width: 700,
	border: false,
	modal: false,
	plain: false,
	collapsible: false,
	resizable: false,
	maximized: true,
	bodyStyle: 'padding: 0px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	id: 'swEmkDocumentsListWindow',
	title: lang['cpisok_dokumentov'],
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	initComponent: function() {
		var th = this;
		
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			items: [{
				fieldLabel: lang['otobrajat'],
				id: 'EDLW_filterDoc',
				valueField: 'filterDoc',
				comboData: [
					['emk',lang['vse_dokumentyi_v_ramkah_emk_patsienta']],
					['evn',lang['vse_dokumentyi_v_ramkah_sluchaya']]
				],
				comboFields: [
					{name: 'filterDoc', type:'string'},
					{name: 'filterDoc_Name', type:'string'}
				],
				value: 'evn',
				width: 300,
				xtype: 'swstoreinconfigcombo'
			}, {
				name: 'Evn_rid',
				xtype: 'hidden'
			},
			{
				name: 'EvnXml_id',
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				xtype: 'hidden'
			}],
			keys: [{
				fn: function(e) {
					th.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.viewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=Template&m=loadEvnXmlList',
			paging: false,
			region: 'center',			
			stringfields: [
				{ name: 'EvnXml_id', type: 'int', hidden: true, key: true },
				{ name: 'XmlType_id', type: 'int', hidden: true },			
				/*
				{ name: 'XmlTemplate_id', type: 'int', hidden: true },
				{ name: 'XmlTemplateType_id', type: 'int', hidden: true },				
				{ name: 'Diag_id', type: 'int', hidden: true },				
				{ name: 'Lpu_id', type: 'int', hidden: true },				
				{ name: 'Evn_rid', type: 'int', hidden: true },				
				{ name: 'Evn_id', type: 'int', hidden: true },	
				*/
				{ name: 'EvnXml_updDT', header: langs('Дата'),  type: 'date', format: 'd.m.Y', width: 80 },
				{ name: 'XmlType_Name', header: langs('Категория'),  type: 'string', width: 150 },
				{ name: 'EvnXml_Name', header: langs('Название'),  type: 'string', width: 200, id: 'autoexpand' },
				{ name: 'pmUser_Name', header: langs('Сотрудник'),  type: 'string', width: 200, id: 'autoexpand' }	//имя сотрудника, добавившего документ
			],
			toolbar: false,
			onLoadData: function(flag) {
				th.buttons[2].setDisabled(!flag);
			},
			onRowSelect: function(sm,rowIdx,record) {				
				th.buttons[2].setDisabled(false);
				
				if ( record.get('EvnXml_id') ) {						
					//просмотр документа
					th.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dokumenta']).show();
					var tpl = new Ext.XTemplate('');
					tpl.overwrite(th.rightPanel.body, {});
					Ext.Ajax.request(
					{
						params: {
							EvnXml_id: record.get('EvnXml_id'),
							XmlType_id: record.get('XmlType_id')
						},
						url: '/?c=Template&m=loadEvnXmlViewData&EvnXml_id',
						callback: function(o, s, response) 
						{
							th.getLoadMask().hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.html) {
								var tpl = new Ext.XTemplate('<div style="background-color: #fff; padding: 10px;">'+response_obj.html+'</div>');
								tpl.overwrite(th.rightPanel.body, {});
							}
						}
					});
				} else {
					var tpl = new Ext.XTemplate('');
					tpl.overwrite(th.rightPanel.body, {});					
				}
			},
			onEnter: function()
			{
				th.onSelectButtonClick();
			},
			onDblClick: function(sm,index,record) {
				this.onEnter();
			}
		});
		
		this.leftPanel = 
		{
			animCollapse: false,
			region: 'west',
			layout: 'border',
			border: true,
			width: 550,
			titleCollapse: true,
			items: [
				this.filterPanel,
				this.viewFrame
			]
		};
		
		this.rightPanel = new Ext.Panel(
		{
			collapsed: false,
			region: 'center',
			autoScroll: true,
			animCollapse: false,
			bodyStyle: 'background-color: #e3e3e3; padding: 10px;',
			minSize: 400,
			floatable: false,
			collapsible: false,
			split: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border: 0px'
			},
			html: ''
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					th.doSearch()
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					th.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					th.onSelectButtonClick();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {					
					th.hide();
				},
				onTabElement: 'EDLW_filterDoc',
				text: BTN_FRMCLOSE
			}],
			keys: [{
				fn: function(inp, e) {
					th.hide();
				},
				key: [
					Ext.EventObject.ESC
				],
				stopEvent: true
			}],
			layout: 'border',
			items: [
				this.leftPanel,
				this.rightPanel
			]
		});
		sw.Promed.swEmkDocumentsListWindow.superclass.initComponent.apply(this, arguments);
	},
	onSelectButtonClick: function() 
	{
		var record = this.viewFrame.getGrid().getSelectionModel().getSelected();

		var data = {
			wholeDoc: this.rightPanel.body.dom.innerHTML,
			evnXmlData: record ? record.data : null
		};
		/*
		http://www.quirksmode.org/dom/execCommand.html
		document.execCommand 'copy', 'paste', 'cut' is not cross-browser
		try {
			document.execCommand('copy',false,null);
			data.isExecCommandCopy = true;
		} catch(err) {*/
			data.isExecCommandCopy = false;
			var s = (window.getSelection) ? window.getSelection() : document.selection;
			if(s && s.rangeCount > 0) {
				data.range = s.getRangeAt(0);
			}
		//}
		this.hide();
		this.callback(data);
	},
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		form.setValues(this.params);
		if (!this.params.EvnXml_id) {
			form.findField('filterDoc').setValue('emk');
		} else {
			form.findField('filterDoc').setValue('evn');
		}
		form.findField('filterDoc').focus(true, 250);
		grid.getStore().baseParams = this.params;
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
		this.buttons[2].setDisabled(true);
	},
	doSearch: function() 
	{
		var form = this.filterPanel.getForm(),
			params = form.getValues();

		this.viewFrame.removeAll(true);
		params.start = 0; 
		params.limit = 100;
		this.viewFrame.loadData({globalFilters: params});
	},
	show: function() {
		sw.Promed.swEmkDocumentsListWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0] || (!arguments[0].EvnXml_id && !arguments[0].Evn_rid && !arguments[0].Person_id) )
		{
			sw.swMsg.alert(lang['oshibka_otkryitiya_formyi'], lang['otsutstvuyut_parametryi']);
			this.onHide = (arguments[0] && arguments[0].onHide) ||  Ext.emptyFn;
			this.hide();
			return false;
		}

		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.params = {
			start: 0,
			limit: 100,
			Evn_rid: arguments[0].Evn_rid || null,
			EvnXml_id: arguments[0].EvnXml_id || null,
			Person_id: arguments[0].Person_id || null
		};
		
		this.doReset();
		this.center();
		this.doSearch();
	}
});