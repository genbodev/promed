/**
* swReportSettingsWindow - окно настроек отчёта.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Report
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      0.001-05.12.2011
* @comment      Префикс для id компонентов RSettF (ReportSettingsForm)
*
*
* @input data: Report_id - ID отчёта
*/

sw.Promed.swReportSettingsWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 300,
	id: 'ReportSettingsWindow',
	initComponent: function() {
		this.ReportFormatsGrid = new sw.Promed.ViewFrame({
			actions: [
			{name:'action_add', disabled: true },
			{name:'action_edit', disabled: true },
			{name:'action_view', disabled: true },
			{name:'action_delete', disabled: true },
			{name:'action_refresh'},
			{name:'action_print'}
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=ReportEngine&m=getFormatsAll',
			focusOn: {
				name: 'RSettF_CloseButton',
				type: 'button'
			},
			focusPrev: {
				name: 'RSettF_CloseButton',
				type: 'button'
			},
			id: 'RSettF_ReportFormatsGrid',
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'ReportFormat_id', type: 'int', header: 'ID', key: true },
				{ name: 'ReportFormat_Ext', type: 'int', hidden: true },
				{ name: 'ReportFormat_Name', type: 'string', header: lang['format'], id: 'autoexpand', autoExpandMin: 150, sortable: false },
				{ name: 'ReportFormat_Sort', type: 'int', header: lang['pozitsiya'], width: 100, sortable: false, hidden: true },
				{ name: 'YesNo_id', header: lang['vklyuchen'], width: 100, sortable: false, renderer: function(val) {
					switch(val) {
						case '2':
						case 2:
							return langs('Да');
							break;
						case '1':
						case 1:
							return langs('Нет');
							break;
						default:
							return val;
							break;
					}
				}}
			],
			sortInfo: {
				field: 'ReportFormat_Sort'
			},
			toolbar: false,
			tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'button',
							id: 'RSettF_OnButton',
							text: lang['vkl'],
							disabled: false,
							handler: function() {
								var wnd = Ext.getCmp('ReportSettingsWindow');
                            	wnd.disableFormat(0);
								//wnd.reloadReportFormatGrid();
							}
						}, {
							xtype: 'button',
							text: lang['otkl'],
							disabled: false,
							id: 'RSettF_OffButton',
							handler: function() {
								var wnd = Ext.getCmp('ReportSettingsWindow');
                            	wnd.disableFormat(1);
								//wnd.reloadReportFormatGrid();
							}
						}, {
							xtype: 'button',
							id: 'RSettF_UpButton',
							text: lang['vverh'],
							disabled: false,
							handler: function() {
								var wnd = Ext.getCmp('ReportSettingsWindow');
                            	wnd.changePositionFormat(0);
								//wnd.reloadReportFormatGrid();
							}
						}, {
							xtype: 'button',
							text: lang['vniz'],
							disabled: false,
							id: 'RSettF_DownButton',
							handler: function() {
								var wnd = Ext.getCmp('ReportSettingsWindow');
                            	wnd.changePositionFormat(1);
								//wnd.reloadReportFormatGrid();
							}
						}]
			}),
			title: lang['formatyi_vyivoda_otcheta'],
			root: 'items'
		});
		
		
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			{
				handler: function() {
					this.callback();
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'RSettF_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [ 
			this.ReportFormatsGrid
			]
		});
		sw.Promed.swReportSettingsWindow.superclass.initComponent.apply(this, arguments);

		this.ReportFormatsGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('ReportSettingsWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 200,
	minWidth: 300,
	modal: true,
	reportId: null,
	plain: true,
	resizable: true,
	serverId: null,
	openFormatEditWindow: function(){
		alert('add/edit');
	},
	reloadReportFormatGrid: function(){
		var grid = this.findById('RSettF_ReportFormatsGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();
	},
	// изменение позиции формата для отчёта
	changePositionFormat: function(changePositionflag){
		var grid = this.findById('RSettF_ReportFormatsGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_format']);
			return false;
		}
		
		var reportId = this.reportId;
		var serverId = this.serverId;
		var formatId = record.get('ReportFormat_id');

		Ext.Ajax.request({
			url: '/?c=ReportEngine&m=changePositionFormat',
			params: {
				changePositionflag: changePositionflag,
				reportId: reportId,
				serverId: serverId,
				formatId: formatId
			},
			success: function() {
				grid.getStore().reload({
					callback: function() {
						// устанавливаем значение поля для сортировки
						var i = grid.getStore().findBy(function(rec) { return rec.get('ReportFormat_id') == formatId; });
						grid.getSelectionModel().suspendEvents();
						grid.getView().refresh();
						grid.getSelectionModel().selectRow(i);
						grid.getView().focusRow(i);
						grid.getStore().sort('ReportFormat_Sort', 'ASC');
						grid.getSelectionModel().resumeEvents();
					}
				});
			},
			failure: function() {
				Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_proizvesti_operatsiyu']);
			}
		});
		
	},
	// включение / отключение формата для отчёта
	disableFormat: function(disableflag){
		var grid = this.findById('RSettF_ReportFormatsGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_format']);
			return false;
		}
		
		var reportId = this.reportId;
		var serverId = this.serverId;
		var formatId = record.get('ReportFormat_id');
		
		if (disableflag == 1){
			// проверяем не все ли выключены
			//record.data.YesNo_Name = '...';
			var i = grid.getStore().findBy(function(rec) { return rec.get('YesNo_id') == 2; });//да
			if (i == -1){
			Ext.Msg.alert(lang['oshibka'], lang['nelzya_otklyuchit_vse_formatyi']);
			record.data.YesNo_id = 2;//да
			return false;
			}
		}
		
		Ext.Ajax.request({
			url: '/?c=ReportEngine&m=disableReportFormat',
			params: {
				disableflag: disableflag,
				reportId: reportId,
				serverId: serverId,
				formatId: formatId
			},
			success: function() {
				var i = grid.getStore().findBy(function(rec) { return rec.get('ReportFormat_id') == formatId; });
				if (disableflag == 1){
					record.data.YesNo_id = 1; //нет
				} else {
					record.data.YesNo_id = 2; //да
				}
				// устанавливаем значение поля для сортировки
				grid.getSelectionModel().suspendEvents();
				grid.getView().refresh();
			    grid.getSelectionModel().selectRow(i);
			    grid.getView().focusRow(i);
				grid.getStore().sort('ReportFormat_Sort', 'ASC');
				grid.getSelectionModel().resumeEvents();
			},
			failure: function() {
				Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_proizvesti_operatsiyu']);
			}
		});
	},
	show: function() {
		sw.Promed.swReportSettingsWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.reportId = null;
		this.serverId = null;

		if ( arguments[0] ) {
			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].reportId ) {
				this.reportId = arguments[0].reportId;
			}

			if ( arguments[0].serverId ) {
				this.serverId = arguments[0].serverId;
			}
		}
		var grid = this.findById('RSettF_ReportFormatsGrid');
		
		grid.removeAll();

		if ( this.reportId ) {
			grid.loadData({
				globalFilters: {
					reportId: this.reportId,
					serverId: this.serverId
				}
			});
			
			grid.getGrid().getStore().sort('ReportFormat_Sort', 'ASC');
		}
	},
	title: lang['nastroyki_otcheta'],
	width: 500
});