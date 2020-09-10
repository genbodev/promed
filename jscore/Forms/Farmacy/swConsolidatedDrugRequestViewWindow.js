/**
* swConsolidatedDrugRequestViewWindow - окно редактирования списка сводных заявок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      11.2012
* @comment      
*/
sw.Promed.swConsolidatedDrugRequestViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnyie_zayavki'],
	layout: 'border',
	id: 'ConsolidatedDrugRequestViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	loadGrid: function() {
		var wnd = this;
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		params.ConsolidatedDrugRequest_begDate = Ext.util.Format.date(wnd.dateMenu.getValue1(), 'd.m.Y');
		params.ConsolidatedDrugRequest_endDate = Ext.util.Format.date(wnd.dateMenu.getValue2(), 'd.m.Y');

		if (wnd.ConsolidatedDrugRequest_begDate != params.ConsolidatedDrugRequest_begDate || wnd.ConsolidatedDrugRequest_endDate != params.ConsolidatedDrugRequest_endDate) {			
			wnd.SearchGrid.removeAll();
			wnd.SearchGrid.loadData({
				globalFilters: params,
				callback: function() {
					wnd.ConsolidatedDrugRequest_begDate = params.ConsolidatedDrugRequest_begDate;
					wnd.ConsolidatedDrugRequest_endDate = params.ConsolidatedDrugRequest_endDate;
					wnd.ConsolidatedDrugRequest_endDate = params.ConsolidatedDrugRequest_endDate;
				}
			});
		}
	},
	doReset: function() {
		this.ConsolidatedDrugRequest_begDate = null;
		this.ConsolidatedDrugRequest_endDate = null;
		this.form.reset();
		this.loadGrid();
	},
	addRecord: function(action) {
		var wnd = this;
		var params = new Object();
		params.callback = function() {
			wnd.SearchGrid.refreshRecords(null,0)
		};
		getWnd('swConsolidatedDrugRequestAddWindow').show(params);
	},
	show: function() {
        var wnd = this;
		sw.Promed.swConsolidatedDrugRequestViewWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onlyView = false;
		
		this.doReset();

		if (arguments[0] && arguments[0].onlyView) {
			this.onlyView = arguments[0].onlyView;
		}

		wnd.SearchGrid.addActions({
			name:'action_cdrvw_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_close',
				text: lang['zakryit_zayavku'],
				tooltip: lang['zakryit_zayavku'],
				handler: function() {
					var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0 && (record.get('DrugRequestStatus_Code') == 1 || record.get('DrugRequestStatus_Code') == 2 || record.get('DrugRequestStatus_Code') == 4)) { //1 - Начальная; 2 - Сформированная; 4 - Нулевая;
						Ext.Ajax.request({
							failure:function () {
								sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
								loadMask.hide();
								wnd.hide();
							},
							params:{
								DrugRequest_id: record.get('DrugRequest_id'),
								DrugRequestStatus_Code: 3 //3 - Утвержденная
							},
							success: function (response) {
								wnd.SearchGrid.refreshRecords(null,0);
							},
							url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
						});
					}
				},
				iconCls: 'delete16'
			}, {
				name: 'action_create',
				text: lang['pereformirovat'],
				tooltip: lang['pereformirovat'],
				handler: function() {
					var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') != 3) {//3 - Утвержденная
						var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Переформирование заявки..."});
						loadMask.show();

						Ext.Ajax.request({
							failure: function(response, options) {
								loadMask.hide();
								sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_sohranit_zapis']);
							},
							params: {DrugRequest_id: record.get('DrugRequest_id')},
							success: function(response, options) {
								loadMask.hide();
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if ( response_obj.success == true ) {
									wnd.SearchGrid.refreshRecords(null,0);
								} else if(response_obj.Error_Msg){
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
								}
							}.createDelegate(this),
							url: '/?c=MzDrugRequest&m=reCreateConsolidatedDrugRequest'
						});
					}
				},
				iconCls: 'refresh16'
			}],
			iconCls: 'actions16'
		});

        this.SearchGrid.setReadOnly(this.onlyView);
		this.SearchGrid.setActionHidden('action_cdrvw_actions', this.onlyView);

		//установка валюты в названиях столбцов
        var currency_str = ' ('+getCurrencyName()+')';
        this.SearchGrid.setColumnHeader('Sum_Total', langs('Сумма')+currency_str);
        this.SearchGrid.setColumnHeader('Sum_pTotal', langs('Сумма закупа')+currency_str);
        this.DrugRequestGrid.setColumnHeader('DrugRequest_Sum', langs('Сумма')+currency_str);

        //по умолчанию отображаем заявки за текущий год
        var dt = new Date();
        this.dateMenu.setValue('01.01.'+dt.format('Y')+' - 31.12.'+dt.format('Y'))

		this.loadGrid();
	},
	initComponent: function() {
		var wnd = this;
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 180,
			fieldLabel: lang['rabochiy_period'],
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			enableKeyEvents: true,
			listeners: {
				'blur': function() {
					wnd.loadGrid();
				},
				'select': function() {
					wnd.loadGrid();
				},
				'keydown': function( inp, e ) {
					if ( e.F4 == e.getKey() ) {
						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;
						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;
						e.browserEvent.returnValue = false;
						e.returnValue = false;
						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}
						inp.onTriggerClick();
						inp.menu.picker.focus();
						return false;
					}
					if ( e.ENTER == e.getKey() ) {
						var v = this.getRawValue();
						this.setValue(v);
						wnd.loadGrid();
					}
				}
			}
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.addRecord(); }},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteConsolidatedDrugRequest'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadConsolidatedDrugRequestList',
			height: 180,
			region: 'center',
			object: 'ConsolidatedDrugRequest',
			editformclassname: 'swConsolidatedDrugRequestEditWindow',
			id: wnd.id + 'Grid',
            paging: true,
            root: 'data',
            totalProperty: 'totalCount',
			style: 'margin-bottom: 10px',
			stringfields: [
                {name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
                {name: 'DrugRequestStatus_Code', type: 'int', hidden: true},
                {name: 'DrugRequestStatus_Name', type: 'string', header: langs('Статус'), width: 120},
                {name: 'DrugRequest_Name', isparams: true, type: 'string', header: langs('Наименование'), id: 'autoexpand', width: 120},
                {name: 'Sum_Total', type: 'money', header: langs('Сумма'), width: 120},
                {name: 'Sum_pTotal', type: 'money', header: langs('Сумма закупа'), width: 120},
                {name: 'FinYear', isparams: true, type: 'string', header: 'Финансовый год', width: 120},
                {name: 'DrugRequest_updDT', type: 'datetime', header: lang['vremya_i_data_izmeneniya'], width: 150}
            ],
			title: null,
			toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('DrugRequest_id') > 0) {
                    this.ViewActions.action_edit.setDisabled(this.readOnly);
                    this.ViewActions.action_view.setDisabled(false);
                    this.ViewActions.action_delete.setDisabled(this.readOnly);
                    wnd.DrugRequestGrid.loadData({
                        globalFilters: {
                            DrugRequest_id: record.get('DrugRequest_id')
                        }
                    });
                } else {
                    this.ViewActions.action_edit.setDisabled(true);
                    this.ViewActions.action_view.setDisabled(true);
                    this.ViewActions.action_delete.setDisabled(true);
                    wnd.DrugRequestGrid.removeAll();
                }
            }
		});

        this.DrugRequestGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadConsolidatedRegionDrugRequestList',
			height: 280,
			region: 'south',
			editformclassname: null,
			id: wnd.id + 'DrugRequestGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
                {name: 'Org_Name', type: 'string', header: 'Организатор', width: 120},
                {name: 'DrugRequestPeriod_Name', type: 'string', header: 'Период', width: 120},
                {name: 'DrugRequest_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand', width: 120},
                {name: 'PersonRegisterType_Name', type: 'string', header: 'Тип регистра пациентов', width: 120},
                {name: 'DrugGroup_Name', type: 'string', header: 'Группа медикаментов', width: 120},
                {name: 'DrugRequest_Sum', type: 'money', header: langs('Сумма'), width: 120}
			],
			title: 'Список заявочных кампаний',
			toolbar: false,
            contextmenu: false
		});
		
		var form = new sw.Promed.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0;',
			border: false,			
			frame: true,
			labelAlign: 'right',
			items: [wnd.dateMenu]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				{
					region: 'north',
					layout: 'form',
					autoHeight: true,
					items: [form]
				},
				this.SearchGrid,
				this.DrugRequestGrid
			]
		});
		sw.Promed.swConsolidatedDrugRequestViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});