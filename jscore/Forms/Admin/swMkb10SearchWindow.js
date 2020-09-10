/**
 * swMkb10SearchWindow - окно справочника МКБ-10
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.12.2013
 */

/*NO PARSE JSON*/

sw.Promed.swMkb10SearchWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMkb10SearchWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	maximized: true,
	layout: 'border',
	title: lang['spravochnik_mkb-10'],

	searchInProgress: false,

	doSearch: function(diag_pid)
	{
		var form = this;
		if (form.searchInProgress == true) {
			return;
		} else {
			form.searchInProgress = true;
		}

		var grid = form.DiagGrid.getGrid();
		var base_form = form.FilterPanel.getForm();
		var node = form.TreePanel.getSelectionModel().selNode;

		var params = base_form.getValues();

		if (diag_pid) {
			params.Diag_pid = diag_pid;
		} else
		if (node && node.attributes.Diag_id) {
			params.Diag_pid = node.attributes.Diag_id
		} else {
			form.searchInProgress = false;
			return;
		}

		if (params.Diag_Code) {
			params.Diag_Code = form.getDiagCode(params.Diag_Code);
		}

		grid.getStore().load({
			params: params,
			callback: function (data) {
				form.searchInProgress = false;
				if (data.length > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},

	doReset: function()
	{
		var form = this;
		var base_form = form.FilterPanel.getForm();

		base_form.reset();
		//form.doSearch();
	},

	getDiagCode: function(code) {
		var countSymbolsCode = (getGlobalOptions().region.nick == 'ufa')?6:5;
		// получаем количество возможных символов
		var q = code.slice(0, countSymbolsCode);
		// если в этом полученном количестве есть пробел, то обрезаем по пробел
		q = (q)?q.split(' ')[0]:'';
		// если там есть русские символы, то делаем их нерусскимми (код же в английской транскрипции)
		q = LetterChange(q.charAt(0)) + q.slice(1, q.length);
		// если нет точки в коде, и код больше трех символов, то добавляем точку
		if (q.charAt(3) != '.' && q.length > 3)
		{
			q = q.slice(0, 3) + '.' + q.slice(3, this.countSymbolsCode-1);
		}
		return q;
	},

	show: function()
	{
		sw.Promed.swMkb10SearchWindow.superclass.show.apply(this, arguments);
		/*if(arguments[0] && arguments[0].action && arguments[0].action == 'view')
		{
			this.DiagGrid.setActionHidden('action_add',true);
			this.DiagGrid.setActionHidden('action_edit',true);
			this.DiagGrid.setActionHidden('action_delete',true);
		}*/

	},

	initComponent: function()
	{
		var form = this;

		form.TreePanel = new Ext.tree.TreePanel({
			region: 'center',
			id: 'MSW_DiagLevelTreePanel',
			autoScroll: true,
			loaded: false,
			border: false,
			rootVisible: false,
			lastSelectedId: 0,
			root: {
				nodeType: 'async',
				text: lang['klassyi_diagnozov'],
				id: 'root',
				expanded: false
			},
			loader: new Ext.tree.TreeLoader({
				listeners:
				{
					load: function(loader, node, response)
					{
						//
					},
					beforeload: function (tl, node)
					{

					}
				},
				dataUrl:'/?c=Diag&m=getDiagTreeData'
			}),
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			listeners: {
				'click': function(node)
				{
					form.doReset();
					form.doSearch(node.attributes.Diag_id);
				}
			}
		});

		this.DiagGrid = new sw.Promed.ViewFrame({
			id: 'MSW_DiagGrid',
			region: 'center',
			dataUrl: '/?c=Diag&m=loadDiagGrid',
			paging: false,
			autoLoadData: false,
			root: 'data',
			toolbar: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			stringfields:
			[
				{name: 'Diag_id', type: 'int', header: 'ID', key: true},
				{name: 'DiagLevel_id', type: 'int', hidden: true},
				{name: 'DiagLevel_Code', type: 'int', hidden: true},
				{name: 'DiagLevel_SysNick', type: 'int', hidden: true},
				{name: 'DiagLevel_Name', header: lang['uroven'], type: 'string', width: 240},
				{name: 'Diag_Code', header: lang['kod'], type: 'string', width: 240},
				{name: 'Diag_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'}
				,{name: 'Diag_endDate', header: 'Дата закрытия', type: 'date', width: 100}
			]
		});
		
		this.DiagGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if ( !Ext.isEmpty(row.get('Diag_endDate')) && getValidDT(row.get('Diag_endDate'), '') < getValidDT(getGlobalOptions().date, '') ) {
					cls = cls+'x-grid-rowgray ';
				}
				return cls;
			}
		});

		this.FilterPanel = new Ext.form.FormPanel({
			region: 'center',
			border: false,
			frame: false,
			autoHeight: true,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;',
				defaults: {
					bodyStyle: 'background: #DFE8F6;'
				}
			},
			bodyStyle: 'background: #DFE8F6;',
			id: 'MSW_FilterForm',
			items: [
				{
					border: false,
					layout: 'column',
					items: [
						{
							border: false,
							layout: 'form',
							labelWidth: 40,
							items: [
								{
									id: 'diag',
									xtype: 'textfield',
									name: 'Diag_Code',
									fieldLabel: lang['kod'],
									width: 120,
									listeners: {
										'change': function(field, newValue) {
											field.setValue(form.getDiagCode(newValue));
										}
									}
								}
							]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 100,
							items: [
								{
									xtype: 'textfield',
									name: 'Diag_Name',
									fieldLabel: lang['naimenovanie'],
									width: 450
								}
							]
						}, {
							border: false,
							layout: 'form',
							style: 'margin-left: 20px;',
							items: [
								{
									xtype: 'button',
									iconCls: 'search16',
									id: 'MSW_SearchButton',
									text: BTN_FRMSEARCH,
									handler: function () {
										form.doSearch();
									}
								}
							]
						}, {
							border: false,
							layout: 'form',
							items: [
								{
									xtype: 'button',
									iconCls: 'reset16',
									id: 'MSW_ResetButton',
									text: lang['sbros'],
									handler: function () {
										form.doReset();
									}
								}
							]
						}
					]
				}
			],
			keys: [
				{
					fn: function () {
						form.doSearch();
					},
					key: [
						Ext.EventObject.ENTER
					],
					stopEvent: true
				}
			]
		});

		form.LeftPanel = new Ext.form.FormPanel({
			region: 'west',
			split: true,
			layout: 'border',
			width: 350,
			items: [
				{
					xtype: 'label',
					text: lang['kategorii_diagnozov'],
					region: 'north',
					style: 'padding: 5px'
				},
				form.TreePanel
			]
		});

		form.CenterPanel = new Ext.Panel({
			region: 'center',
			layout: 'border',
			items: [
				{
					xtype: 'panel',
					layout: 'border',
					border: false,
					region: 'north',
					id: 'MSW_FilterPanel',
					height: 70,
					items: [
						{
							xtype: 'panel',
							region: 'center',
							autoHeight: true,
							border: false,
							bodyStyle: 'background: #DFE8F6;',
							items: [
								{
									xtype: 'fieldset',
									style: 'margin: 3px 6px 6px 6px; background: #DFE8F6',
									title: lang['filtr'],
									region: 'north',
									collapsible: true,
									bodyStyle: 'background: #DFE8F6;',
									collapsed: false,
									autoHeight: true,
									listeners: {
										'expand': function() {
											form.findById('MSW_FilterPanel').setHeight(70);
											form.CenterPanel.doLayout();
										},
										'collapse': function() {
											form.findById('MSW_FilterPanel').setHeight(20);
											form.CenterPanel.doLayout();
										}
									},
									items: [form.FilterPanel]
								}
							]
						}
					]
				},
				form.DiagGrid
			]
		});

		Ext.apply(this, {
			items: [
				form.LeftPanel,
				form.CenterPanel
			],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'MSW_CancelButton',
				onTabAction: function () {
				},
				tabIndex: 1,
				text: lang['zakryit']
			}]
		});

		sw.Promed.swMkb10SearchWindow.superclass.initComponent.apply(this, arguments);
	}
});