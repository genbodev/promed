/**
* swEgissoReceptExportListWindow - Журнал ручного экспорта МСЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Admin
* @access		public
* @copyright	Copyright (c) 2019 Swan Ltd.
*
*/
/*NO PARSE JSON*/
sw.Promed.swEgissoReceptExportListWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	maximizable: false,
	title: 'Журнал ручного экспорта МСЗ',
	buttonAlign: 'left',
	searchInProgress: false,
	doExport: function() {
		var win = this;
		getWnd('swEgissoReceptExportWindow').show({
			callback: function() {
				win.doSearch();
			}
		});
	},
	doReset: function() {
		this.findById('EgissoReceptExportFilterForm').getForm().reset();
		this.findById(this.id + 'EgissoReceptExportGrid').getGrid().getStore().removeAll();
		this.doSearch();
	},
	doSearch: function(params) {
		if (this.searchInProgress) {
			return false;
		}
		
		var win = this;
		var filter_form = win.findById('EgissoReceptExportFilterForm');
		
		var grid = win.findById(win.id + 'EgissoReceptExportGrid').getGrid();
		var params = filter_form.getForm().getValues();
		
		params.start = 0;
		params.limit = 100;
        params.EGISSOReceptExport_isNew = params.EGISSOReceptExport_isNew ? 2 : null;

		this.searchInProgress = true;
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function (){
				win.searchInProgress = false;
			}
		});
	},
	initComponent: function() {
		
		var win = this;

		this.mainFilters = {
			autoHeight: true,
			autoScroll: true,
			bodyStyle: 'margin: 10px 5px;',
			layout: 'form',
			border: false,
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 320,
					labelWidth: 180,
					items: [{
						fieldLabel: 'Дата экспорта',
						name: 'EGISSOReceptExport_setDT',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 120,
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Начало периода экспорта',
						name: 'EGISSOReceptExport_begDT',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 120,
						xtype: 'swdatefield'
					}, {
						fieldLabel: 'Окончание периода экспорта',
						name: 'EGISSOReceptExport_endDT',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 120,
						xtype: 'swdatefield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 260,
					labelWidth: 120,
					items: [{
						xtype: 'checkbox',
						labelSeparator: '',
						name: 'EGISSOReceptExport_isNew',
						boxLabel: 'Только новые'
					}, {
						xtype: 'swcommonsprcombo',
						width: 120,
						fieldLabel: 'Статус экспорта',
						comboSubject: 'EGISSOReceptExportStatus',
					}]
				}]
			}]
		};
		
		this.mainFilterPanel = new Ext.Panel({
			height: 250,
			autoHeight: true,
			id: win.id + 'SearchFilterPanel',
			region: 'north',
			items: [
				new sw.Promed.Panel({
					autoHeight: true,
					border: false,
					collapsible: true,
					title: langs('Нажмите на заголовок чтобы свернуть/развернуть панель фильтров'),
					region: 'center',
					items: [
					new Ext.form.FormPanel({
						afterRender : function() {
							var map = new Ext.KeyMap(this.getEl(), [{
								key: [13],
								fn: function() {
									win.doSearch();
								},
								scope: this
							}]);
						},
						autoScroll: true,
						bodyBorder: false,
						labelAlign: 'right',
						labelWidth: 130,
						id: 'EgissoReceptExportFilterForm',
						items: [
							this.mainFilters
						]
					})],
					listeners: {
						collapse: function(p) {
							win.doLayout();
							win.syncSize();
						},
						expand: function(p) {
							win.doLayout();
							win.syncSize();
						}
					}
				})
			]
		});
		
		this.mainViewFrame = new sw.Promed.ViewFrame({
			useArchive: 0,
			actions: [
				{ name: 'action_add', text: 'Экспорт', icon: 'img/icons/database-export16.png', handler: function() { win.doExport(); } },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', handler: function() { win.doSearch(); } },
				{ name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=EGISSOReceptExport&m=loadList',
			id: win.id + 'EgissoReceptExportGrid',
			layout: 'fit',
			object: 'EvnPLDD',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount', 
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'EGISSOReceptExport_id', type: 'int', header: 'ID', key: true },
				{ name: 'EGISSOReceptExportStatus_id', type: 'int', hidden: true },
				{ name: 'EGISSOReceptExport_setDT', type: 'date', format: 'd.m.Y', header: 'Дата экспорта', width: 100 },
				{ name: 'EGISSOReceptExport_begDT', type: 'date', format: 'd.m.Y', header: 'Начало периода экспорта', width: 150 },
				{ name: 'EGISSOReceptExport_endDT', type: 'date', format: 'd.m.Y', header: 'Окончание периода экспорта', width: 180 },
				{ name: 'EGISSOReceptExport_isNew', type: 'checkbox', header: 'Только новые', width: 100 },
				{ name: 'EGISSOReceptExportStatus_Name', type: 'string', header: 'Статус экспорта', width: 120 },
				{ name: 'EGISSOReceptExport_Error', type: 'string', header: 'Ошибки экспорта', width: 250, id: 'autoexpand' },
				{ name: 'EGISSOReceptExport_Result', type: 'string', header: 'Файл', width: 250, renderer: function(v, p, r){
					if (!v) return ''; 
					return '<a target="_blank" href="'+v+'">'+v.split(/[\/]+/).pop()+'</a>';
				}}
			]
		});
		
		Ext.apply(this, {
			items: [
				this.mainFilterPanel,
				this.mainViewFrame
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEgissoReceptExportListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swEgissoReceptExportListWindow.superclass.show.apply(this, arguments);

		this.searchInProgress = false;
		this.center();
		this.maximize();
		this.doReset();
	}
});
