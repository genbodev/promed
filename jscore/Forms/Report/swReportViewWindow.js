/**
* swReportViewWindow - окно просмотра и редактирования отчетов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      30.12.2015
* @comment      Префикс для id компонентов rptvf (ReportViewWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swReportViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'ReportViewWindow',
	title: lang['otchetyi_ochered_i_istoriya'], 
	layout: 'border',
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	firstTabIndex: TABINDEX_RVWF,
	onTreeClick: function(node,e) {
		var level = node.getDepth();
		var win = this;
		// Получаем фильтры если заполнены 
		var params = win.getFilterParams();
		var frm =  this.ReportRunDataFiltersPanel.getForm();
		
		params.limit = 100;
		params.start = 0;
		switch (node.id) {
			case 'queue': // Очередь
				params['isqueue'] = 1;
				win.ReportRunGrid.loadData({globalFilters: params});
				win.ReportRunGrid.setColumnHidden('row_num', false);
				win.ReportRunGrid.setColumnHidden('check', false);
				win.ReportRunGrid.getAction('action_delete').setHidden(false);
				//win.ReportRunGrid.setColumnHidden('Is_Checked', false);
			//	win.ReportRunGrid.setColumnHidden('ReportRun_StatusName', false);
			//	frm.findField('filterReportStatus').setContainerVisible(true);
				//win.ReportRunGrid.setColumnHidden('ReportRun_queueDT', false);
				break;
			case 'history':
				params['isqueue'] = 0;
				win.ReportRunGrid.loadData({globalFilters: params});
				win.ReportRunGrid.setColumnHidden('row_num', true);
				win.ReportRunGrid.setColumnHidden('check', true);
				win.ReportRunGrid.getAction('action_delete').setHidden(true);
				//win.ReportRunGrid.setColumnHidden('Is_Checked', true);
			//	win.ReportRunGrid.setColumnHidden('ReportRun_StatusName', true);
			//	frm.findField('filterReportStatus').setContainerVisible(false);
				//win.ReportRunGrid.setColumnHidden('ReportRun_queueDT', false);
				/*
				owner.ErrorGrid.setColumnHeader('LpuSection_name', lang['profil_brigadyi']);
				owner.ReportRunGrid.getAction('action_new').setHidden(!(isUserGroup([ 'ReportRunUser' ]) || isSuperAdmin()));
				*/
			break;
		}
		win.ReportRunListPanel.doLayout();
	},
	doSearch: function() {
		var node = this.Tree.getSelectionModel().getSelectedNode();
		this.onTreeClick(node);
	},

	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('report', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},
	
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},

	setFilterDefault: function() {
		// Даты фильтрации устанавливаем по умолчанию
		var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		var filterReportPeriod = this.ReportRunDataFiltersPanel.getForm().findField('filterReportPeriod');
		filterReportPeriod.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	
	getFilterParams: function() {
		var frm =  this.ReportRunDataFiltersPanel.getForm();
		var params = frm.getValues();
		return params;
	},
	
	openReport: function(object) {
		// открываем ссылку по выбранной строке в новом окне
		var record = object.getGrid().getSelectionModel().getSelected();
		if (record) {
			var filepath = record.get('ReportRun_FilePath');
		} else {
			return false;
		}
		if (filepath) {
			window.open(filepath, '_blank');
		}
	},
	show: function() {
		sw.Promed.swReportViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		// При открытии если Root Node уже открыта - перечитываем
		var win = this;
		var root = this.Tree.getRootNode();
		if (root)
		{
			if (root.isExpanded())
			{
				this.Tree.getLoader().load(root);
				// Дальше отрабатывает логика на load
			}
		}
		this.maximize();

		if (!this.ReportRunGrid.getAction('sign_actions')) {
			this.signAction = new Ext.Action({
				name: 'action_signReportRun',
				text: langs('Подписать'),
				tooltip: langs('Подписать'),
				handler: function() {
					var me = this;
					var rec = win.ReportRunGrid.getGrid().getSelectionModel().getSelected();
					if (rec && rec.get('ReportRun_id')) {
						getWnd('swEMDSignWindow').show({
							EMDRegistry_ObjectName: 'ReportRun',
							EMDRegistry_ObjectID: rec.get('ReportRun_id'),
							callback: function(data) {
								if (data.preloader) {
									me.disable();
								}

								if (data.success || data.error) {
									me.enable();
									win.ReportRunGrid.getGrid().getStore().reload();
								}
							}
						});
					}
				}
			});

			this.ReportRunGrid.addActions({
				name: 'sign_actions',
				text: langs('Подписать'),
				menu: [win.signAction, {
					name: 'action_showReportRunVersionList',
					text: langs('Версии документа'),
					tooltip: langs('Версии документа'),
					handler: function() {
						var rec = win.ReportRunGrid.getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('ReportRun_id')) {
							getWnd('swEMDVersionViewWindow').show({
								EMDRegistry_ObjectName: 'ReportRun',
								EMDRegistry_ObjectID: rec.get('ReportRun_id')
							});
						}
					}
				}],
				iconCls : 'x-btn-text',
				icon: 'img/icons/digital-sign16.png'
			});
		}

		if (isLpuAdmin() || isSuperAdmin()) {
			this.getReplicationInfo();
		}
		// Также грид сбрасываем
		this.ReportRunGrid.removeAll();
		
		this.setFilterDefault();
		
		this.getLoadMask().hide();

		this.ReportRunChecked = new Array();
	},
	initComponent: function()
	{
		var win = this;
		this.TreeToolbar = new Ext.Toolbar(
		{
			id : win.id+'Toolbar',
			items:
			[
				{
					xtype : "tbseparator"
				}
			]
		});

		this.Tree = new Ext.tree.TreePanel(
		{
			id: win.id+'ReportRunTree',
			animate: false,
			autoScroll: true,
			split: true,
			region: 'west',
			root:
			{
				id: 'root',
				nodeType: 'async',
				text: lang['reestryi'],
				expanded: true
			},
			rootVisible: false,
			tbar: win.TreeToolbar,
			//useArrows: false,
			width: 250,
			/*
				columns:[{
					dataIndex: 'leafName',
					header: '',
					width: 200
				}, {
					header: '',
					width: 50,
					dataIndex: 'regCount'
				}],
			*/
			/*listeners:
			{
				'expandnode': function(node)
				{
						if ( node.id == 'root' ) {
							this.getSelectionModel().select(node.firstChild);
							this.fireEvent('click', node.firstChild);
						}
					}
			},
			*/
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl: '/?c=ReportRun&m=loadReportTree',
				listeners:
				{
					beforeload: function (loader, node) {
						loader.baseParams.level = node.getDepth();
					},
					load: function (loader, node) {
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						var cnode = node.firstChild || node;
						cnode.getOwnerTree().fireEvent('click', cnode);
						cnode.select();
						
					}
				}
			})
		});
		// Выбор ноды click-ом
		this.Tree.on('click', function(node, e)
		{
			win.onTreeClick(node, e);
		});


		this.ReportRunGrid = new sw.Promed.ViewFrame(
		{
			useArchive: 1,
			id: win.id+'Grid',
			region: 'center',
			//height: 405,
			title:lang['ochered_istoriya'],
			object: 'ReportRun',
			editformclassname: 'swReportRunEditWindow',
			dataUrl: '/?c=ReportRun&m=loadReportGrid',
			paging: true,
			autoLoadData: false,
			useArchive: false,
			pageSize:100,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			stringfields:
			[
				{name:'check', 
					sortable: false, 
					width: 40, 
					renderer: this.checkRenderer,
					header: '<input type="checkbox" id="RVW_checkAll" onClick="getWnd(\'swReportViewWindow\').checkAll(this.checked);">'
				},
				{name: 'Is_Checked',type: 'int', header: 'is_checked', hidden: true},
				{name: 'row_num', type: 'int', header: lang['nomer'], hidden: !isSuperAdmin},
				{name: 'ReportRun_id', type: 'int', header: 'ReportRun_id', key: true, hidden:!isSuperAdmin()},
                {name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden:!isSuperAdmin()},
				{name: 'Report_id', type: 'int', header: 'Report_id', hidden:!isSuperAdmin()},
				{name: 'Report_Name', header: lang['otchet'], autoexpand: true},
				{name: 'Report_Params', hidden:true},
				{name: 'Report_paramBegDate', type:'date', header: lang['period_s'], width: 80},
				{name: 'Report_paramEndDate', type:'date', header: lang['period_po'], width: 80},
				{name: 'Report_paramLpu_id', type:'int', hidden:true},
				{name: 'pmUser_id', type: 'int', hidden: true},
				{name: 'pmUser_name', header: lang['polzovatel'], width: 80},
				{name: 'ReportRun_begDT', type:'datetime', header: lang['nachalo'], width: 100},
				{name: 'ReportRun_endDT', type:'datetime', header: lang['okonchanie'], width: 100},
				{name: 'ReportRun_queueDT', type:'datetime', header: lang['ochered'], width: 100},
				{name: 'ReportRun_StatusName', type:'string', header: lang['status'], width: 100},
				{name: 'ReportRun_IsSigned', renderer: function (v, p, r) {
					if (Ext.isEmpty(r.get('ReportRun_id')) || !r.get('ReportRun_Format') || r.get('ReportRun_Format').indexOf('pdf') < 0) {
						return '';
					}
					var val = '<span style="color: #000;">Не подписан</span>';
					if (!Ext.isEmpty(v)) {
						switch (parseInt(v)) {
							case 1:
								val = '<span style="color: #800;">Не актуален</span>';
								break;
							case 2:
								val = '<span style="color: #080;">&#10004; ' + r.get('pmUser_signName') + ' ' + r.get('ReportRun_signDT') + '</span>';
								break;
						}
					}
					return val;
				}, header: langs('ЭП'), width: 200},
				{name: 'pmUser_signName', hidden:true},
				{name: 'ReportRun_signDT', hidden:true},
				{name: 'ReportRun_FilePath', hidden:true},
				{name: 'ReportRun_Format', renderer: function(val) {
					if (val) {
						if (val.indexOf('pdf') >= 0) {
							return 'PDF';
						}
						if (val.indexOf('html') >= 0) {
							return 'HTML';
						}
						if (val.indexOf('word') >= 0) {
							return 'WORD';
						}
						if (val.indexOf('excel') >= 0) {
							return 'EXCEL';
						}
						if (val.indexOf('opendocument.spreadsheet') >= 0) {
							return 'ODS';
						}
					}

					return '';
				}, header: langs('Формат'), width: 100},
				{name: 'ReportRun_Status', hidden:true}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, enabled: false},
				{name:'action_view', text: lang['otkryit_otchet'], icon: 'img/icons/view16.png', handler: function() {win.openReport(win.ReportRunGrid);}, tooltip: lang['otkryit_otchet_enter']},
				{
					name:'action_delete', 
					disabled: false, 
					hidden: false, 
					handler: function(){
						win.deleteReportRuns();
					}
				}
			],
			afterSaveEditForm: function(ReportRunQueue_id, records)
			{
				var r = records.ReportRunQueue_Position;
				win.onIsRunQueue(r);
			},
			onLoadData: function()
			{
				//this.getAction('action_add').setDisabled(this.getParam('ReportRunStatus_id')!=3);
				if  (this.getAction('action_new')) {
					this.getAction('action_new').setDisabled(this.getCount()==0);
				}
				var base_form = Ext.getCmp('ReportViewWindow');
					var records = new Array();
					base_form.ReportRunGrid.getGrid().getStore().each(function (rec){
						if (!Ext.isEmpty(rec.get('ReportRun_id'))) {
							var index = base_form.ReportRunChecked.indexOf(rec.get('ReportRun_id'));
							if (index > -1) {
								rec.set('Is_Checked', 1);
							}
						}
					});
			},
			onRowSelect: function(sm,index,record) {
				win.signAction.disable();

				if (record && record.get('ReportRun_id')) {
					var node = win.Tree.getSelectionModel().getSelectedNode();
					//win.ReportRunGrid.setActionDisabled('action_delete', (node.id != 'queue')); // удаление доступно для очереди
					win.ReportRunGrid.setActionDisabled('action_view', (node.id != 'history' || !record.get('ReportRun_endDT'))); // открытие отчета доступно для истории и только

					if (record.get('ReportRun_IsSigned') != 2 && record.get('ReportRun_Format') == 'Content-Type: application/pdf' && record.get('ReportRun_Status') == 1 && !isUserGroup('PM')) {
						win.signAction.enable();
					}
				}
			},
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if (!this.getAction('action_view').isDisabled()) {
					this.runAction('action_view');
				}
			}

		});

		this.ReportRunGrid.ViewGridPanel.view.getRowClass = function (row, index) {
			var cls = '';

			if (!row.get('ReportRun_endDT')) {
				cls = cls+'x-grid-rowgray ';
			}
			if (row.get('ReportRun_queueDT')) {
				cls = cls+'x-grid-rowblue ';
			}
			if ( cls.length == 0 ) {
				cls = 'x-grid-panel';
			}
			return cls;
		};
		
		this.ReportRunDataFiltersPanel = new Ext.form.FormPanel(
		{
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			//height: 40,
			frame: true,
			autoHeight: true,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.doSearch();
				},
				stopEvent: true
			}],
			items:
			[{
				layout: 'column',
				bodyStyle:'padding: 4px;',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					//region: 'north',
					//columnWidth: .30,
					labelWidth: 140,
					items:[{
						anchor: '95%',
						fieldLabel: lang['naimenovanie_otcheta'],
						name: 'filterReportName',
						xtype: 'textfield',
						tabIndex:win.firstTabIndex+10
					},
					{
						fieldLabel: lang['period_sozdaniya'],
						name: 'filterReportPeriod',
						plugins: [
							new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
						],
						tabIndex: win.firstTabIndex + 11,
						width: 170,
						xtype: 'daterangefield'
					},
					{
                        xtype  : 'combo',
                        store  : new Ext.data.SimpleStore({
                            fields : ['id','value'],
                            data   : [
                                ['0' , 'Не сформирован'],
                                ['1' , 'Сформирован']
                            ]
                        }),
                        hiddenName  : 'filterReportStatus' ,
                        fieldLabel  : lang['status'],
                        displayField: 'value',
                        valueField  : 'id',
                        triggerAction : 'all',
                        mode        : 'local',
                        editable    : false,
                        width		: 170
                    },
					{
                        xtype  : 'combo',
                        store  : new Ext.data.SimpleStore({
                            fields : ['id','value'],
                            data   : [
                                ['1' , 'Нет'],
                                ['2' , 'Да']
                            ]
                        }),
                        hiddenName  : 'filterReportSign' ,
                        fieldLabel  : langs('Наличие ЭП'),
                        displayField: 'value',
                        valueField  : 'id',
                        triggerAction : 'all',
                        mode        : 'local',
                        editable    : false,
                        width		: 170
                    },
					{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls : 'x-btn-text',
						tabIndex: win.firstTabIndex+16,
						disabled: false,
						handler: function()
						{
							win.ReportRunChecked = new Array();
							win.doSearch();
						}
					}]
				}]
			}]
		});


		this.ReportRunListPanel = new sw.Promed.Panel({
			border: false,
			id: win.id+'ReportRunListPanel',
			layout:'border',
			defaults: {split: true},
			items: [win.ReportRunDataFiltersPanel, win.ReportRunGrid]
		});

		Ext.apply(this,
		{
			layout:'border',
			defaults: {split: true},
			buttons:
			[{
				id: 'rptvfBtnMirrorUpdTime',
				handler: function()
				{
					this.ownerCt.getReplicationInfo()
				},
				iconCls: 'ok16',
				text: langs('Актуальность данных: (неизвестно)')
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items:
			[
				win.Tree,
				{
					border: false,
					region: 'center',
					layout:'card',
					activeItem: 0,
					id: 'rptvfRightPanel',
					defaults: {split: true},
					items: [this.ReportRunListPanel]
				}
			]
		});
		sw.Promed.swReportViewWindow.superclass.initComponent.apply(this, arguments);
	},
	checkRenderer: function(v, p, record) {
		var id = record.get('ReportRun_id');
		var value = 'value="'+id+'"';
		var checked = record.get('Is_Checked')!=0 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swReportViewWindow\').checkOne(this.value);"';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+'>';

	},
	checkAll: function(check)
	{
		var form = this;
		var array_index = -1;
		if(check)
			this.ReportRunGrid.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 1);
				array_index = form.ReportRunChecked.indexOf(record.get('ReportRun_id'));
				if(array_index == -1){
					form.ReportRunChecked.push(record.get('ReportRun_id'));
				}
			});
		else
			this.ReportRunGrid.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 0);
				array_index = form.ReportRunChecked.indexOf(record.get('ReportRun_id'));
				if(array_index > -1){
					form.ReportRunChecked.splice(array_index, 1); //Убираем из массива отмеченные ReportRun_id
				}
			});
	},
	checkOne: function(id){
		var form = this;
		var ReportRun_id = id;
		var array_index = form.ReportRunChecked.indexOf(ReportRun_id);
		this.ReportRunGrid.getGrid().getStore().each(function(record){
			if(record.get('ReportRun_id') == ReportRun_id){
				if(record.get('Is_Checked') == 0) //Было 0, т.е. при нажатии устанавливаем галочку
				{
					record.set('Is_Checked',1);
					if(array_index == -1){
						form.ReportRunChecked.push(ReportRun_id);
					}
				}
				else{ //Было 1, т.е. при нажатии снимаем галочку
					record.set('Is_Checked',0);
					if(array_index > -1){
						form.ReportRunChecked.splice(array_index, 1); //Убираем из массива отмеченные ReportRun_id
					}
				}
			}
		});
		log(form.ReportRunChecked);
	},
	deleteReportRuns: function(){
		var form = this;
		if (form.ReportRunChecked.length < 1) {
			sw.swMsg.alert('Ошибка', 'Необходимо выбрать записи для удаления');
			return false;
		}
		var ReportRuns_Array = new Array();
		ReportRuns_Array = form.ReportRunChecked;
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId/*, unused text, obj*/) {
				if ('yes' == buttonId) {
					var params = {
						reportRuns_array: Ext.util.JSON.encode(ReportRuns_Array)
					};
					Ext.Ajax.request({
						params: params,
						url:'/?c=ReportRun&m=deleteReportRuns',
						callback: function(options,success,response){
							var response_obj = Ext.util.JSON.decode(response.responseText);
							form.ReportRunChecked = new Array();
							form.doSearch();
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_zapisi'],
			title: lang['vopros']
		});
	}
});