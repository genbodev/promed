/**
* swPlanObsDispEditWindow - окно редактирования плана контр.посещений в рамках ДН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

/*NO PARSE JSON*/
sw.Promed.swPlanObsDispEditWindow = Ext.extend(sw.Promed.BaseForm, {
	//~ id: 'PlanObsDispEditWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	width: 470,
	height: 300,
	modal: true,
	codeRefresh: true,
	objectName: 'swPlanObsDispEditWindow',
	objectSrc: '/jscore/Forms/Common/swPlanObsDispEditWindow.js',	
	returnFunc: function(owner) {},
	action: 'add',
	show: function() {
		sw.Promed.swPlanObsDispEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();
		base_form.reset();
		win.DataPanel.getGrid().getStore().removeAll();
		win.ErrorPanel.getGrid().getStore().removeAll();
		win.isAutosaved = false;
		win.data = [];
		win.dataError = [];

		if (arguments[0]['action']) {
			win.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			win.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['PlanObsDisp_id']) {
			win.PlanObsDisp_id = arguments[0]['PlanObsDisp_id'];
		} else {
			win.PlanObsDisp_id = null;
		}
		/*
		if (arguments[0]['DispCheckPeriod_id']) {
			win.DispCheckPeriod_id = arguments[0]['DispCheckPeriod_id'];
			//~ base_form.findField('DispCheckPeriod_id').setValue(win.DispCheckPeriod_id);
		} else {
			win.DispCheckPeriod_id = null;
		}
		*/
		if(arguments[0])
			base_form.setValues(arguments[0]);
		
		if (arguments[0]['year']) {
			win.year = arguments[0]['year'];
		} else {
			win.year = null;
		}
	
		switch (this.action){
			case 'add':
				this.setTitle('План контрольных посещений в рамках ДН: Добавление');
				break;
			case 'edit':
				this.setTitle('План контрольных посещений в рамках ДН: Редактирование');
				break;
			case 'view':
				this.setTitle('План контрольных посещений в рамках ДН: Просмотр');
				break;
		}
		var periodcombo = base_form.findField('DispCheckPeriod_id');
		periodcombo.getStore().baseParams = {
			Year: win.year
		};
		periodcombo.getStore().load({
			callback: function() {
				periodcombo.setValue(periodcombo.getValue());
			}
		});
		var wdcombo = base_form.findField('TFOMSWorkDirection_id');
		wdcombo.setContainerVisible(getRegionNick()=='ekb');
		if(getRegionNick()=='ekb') {
			wdcombo.getStore().load({
				callback: function() {
					wdcombo.setValue(wdcombo.getValue());
				}
			});
		}

		base_form.findField('OrgSMO_id').setContainerVisible(getRegionNick()=='buryatiya');

		if (win.action=='view') {
			base_form.findField('DispCheckPeriod_id').disable();
			win.DataPanel.setReadOnly(true);
			win.DataPanel.getAction('action_refresh').setDisabled(true);
			win.buttons[0].disable();
			win.findById('PlanObsDispEditMakeButton').disable();
		} else {
			base_form.findField('DispCheckPeriod_id').enable();
			win.DataPanel.setReadOnly(false);
			win.DataPanel.getAction('action_refresh').setDisabled(false);
			win.buttons[0].enable();
			win.findById('PlanObsDispEditMakeButton').enable();
		}
		
		win.DataPanel.addActions({
			name: 'accept_tfoms',
			text: 'Принята ТФОМС',
			tooltip: 'Принята ТФОМС',
			//iconCls: ?
			hidden: !isSuperAdmin(),
			disabled: false,
			handler: function() {
				win.acceptTfoms();
			}
		});
		
		win.DataPanel.getAction('accept_tfoms').setHidden(getGlobalOptions().accept_tfoms_answer!=1);
		
		var cm = win.DataPanel.getGrid().getColumnModel();
		cm.setHidden(cm.findColumnIndex('Errors'), getGlobalOptions().accept_tfoms_answer!=1);
		
		win.doSearch();
	},
	acceptTfoms: function() {
		var win = this,
			grid = win.DataPanel.getGrid(),
			records = grid.getSelectionModel().getSelections();
		if(records && records[0]) {
			Ext.Ajax.request({
				params: {
					PlanObsDispLink_id: records[0].get('PlanObsDispLink_id'),
					PersonDisp_id: records[0].get('PersonDisp_id'),
					PlanObsDisp_id: win.PlanObsDisp_id,
					PlanPersonListStatusType_id: 3
				},
				url: '/?c=PlanObsDisp&m=setPlanObsDispLinkStatus',
				callback: function(opt, success, response)  {
					if (success) {
						win.getLoadMask().hide();
						win.doSearch();
					}
				}
			});
		}
	},
	exportPlanObsDisp: function() {
		var win = this,
			form = win.FormPanel.getForm(),
			period_field = form.findField('DispCheckPeriod_id');
		if(Ext.isEmpty(win.PlanObsDisp_id)) return;
		
		var params = {
			PlanObsDisp_id: win.PlanObsDisp_id,
			DispCheckPeriod_Year: win.year,
			//~ DispCheckPeriod_Month: records[0].get('DispCheckPeriod_Month'),
			Lpu_id: getGlobalOptions().lpu_id,
			callback: function() {
				win.doSearch();
			}
		};
		
		if(getRegionNick().inlist(['ekb','pskov'])) {
			win.doSearch();
			params.PlanObsDisp_Year = win.year;
			getWnd('swPlanObsDispExportWindow').show(params);
		} else {
			win.getLoadMask('Выполняется экспорт...').show();
			Ext.Ajax.request({
				params: {PlanObsDisp_id: win.PlanObsDisp_id, Lpu_id: getGlobalOptions().lpu_id},
				url: '/?c=PlanObsDisp&m=exportPlanObsDisp',
				callback: function(opt, success, response)  {
					if (success) {
						win.getLoadMask().hide();
						win.doSearch();
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.link) {
							sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a target="_blank" download="" href="' + response_obj.link + '">Скачать и сохранить файл экспорта</a>');
						} else sw.swMsg.alert(langs('Ошибка'), 'При экспорте данных произошла ошибка');
					}
				}
			});
		}
	},
	doSearch: function() {
		var win = this;
		if(Ext.isEmpty(win.PlanObsDisp_id)) return false;
		
		var grid = win.DataPanel.getGrid(),
			gridError = win.ErrorPanel.getGrid();
		var form = win.FormPanel.getForm();
		var params = form.getValues();
		params.PlanObsDisp_id = win.PlanObsDisp_id;
		params.start = 0;
		params.limit = 5000000;
		
		// 1. грузим всё в массив
		win.data = [];
		win.dataError = [];
		win.getLoadMask(langs('Загрузка данных..')).show();
		grid.getStore().removeAll();
		gridError.getStore().removeAll();
		
		win.DataPanel.pagingBBar.doLoad()
		win.ErrorPanel.pagingBBar.doLoad();
		Ext.Ajax.request({
			url: '/?c=PlanObsDisp&m=loadPlan',
			params: params,
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					win.data = Ext.util.JSON.decode(response.responseText);
					win.data = win.data.data;
					// 2. грид используем с временным хранилищем (грузим первые сто записей)
					var data_obj = new Object();
					data_obj.totalCount = win.data.length;
					data_obj.data = win.data.slice(0, win.pageSize);

					grid.getStore().loadData(data_obj);
				}
			}
		});
		
		var period_field = form.findField('DispCheckPeriod_id');
		params.DispCheckPeriod_id = period_field.getValue();
		params.DispCheckPeriod_begDate = Ext.util.Format.date(period_field.getFieldValue('DispCheckPeriod_begDate'), 'd.m.Y');
		params.DispCheckPeriod_endDate = Ext.util.Format.date(period_field.getFieldValue('DispCheckPeriod_endDate'), 'd.m.Y');
		params.Lpu_id = getGlobalOptions().lpu_id;
		
		Ext.Ajax.request({
			url: '/?c=PlanObsDisp&m=loadPlanErrorData',
			params: params,
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					win.dataError = Ext.util.JSON.decode(response.responseText);
					win.dataError = win.dataError.data;
					if(win.dataError) {
						// 2. грид используем с временным хранилищем (грузим первые сто записей)
						var resp_obj2 = new Object();
						resp_obj2.totalCount = win.dataError.length;
						resp_obj2.data = win.dataError.slice(0, win.pageSize);
						gridError.getStore().loadData(resp_obj2);
					}
				}
			}
		});
	},
	doReset: function() {
		var form = this.FormPanel.getForm();
		//~ form.reset();
		form.findField('Person_FIO').setValue('');
		form.findField('Person_Birthday').setValue('');
		form.findField('Diag_id').clearValue();
		form.findField('PlanPersonListStatusType_id').clearValue();
		this.doSearch();
	},
	makePlan: function(options) //doSave
	{
		var win = this,
			base_form = win.FormPanel.getForm(),
			period_field = base_form.findField('DispCheckPeriod_id'),
			loadMask = new Ext.LoadMask(win.el, { msg: "Подождите, идет формирование плана..." }),
			params = {};
		
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if(win.PlanObsDisp_id) params.PlanObsDisp_id = win.PlanObsDisp_id;
		params.DispCheckPeriod_id = period_field.getValue();
		params.DispCheckPeriod_begDate = Ext.util.Format.date(period_field.getFieldValue('DispCheckPeriod_begDate'), 'd.m.Y');
		params.DispCheckPeriod_endDate = Ext.util.Format.date(period_field.getFieldValue('DispCheckPeriod_endDate'), 'd.m.Y');
		
		params.Lpu_id = getGlobalOptions().lpu_id;
		if(getRegionNick()=='ekb') 
			params.TFOMSWorkDirection_id = base_form.findField('TFOMSWorkDirection_id').getValue();

		if(getRegionNick()=='buryatiya')
			params.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
		params.action = win.action;

		loadMask.show();
		Ext.Ajax.request({//создать, потом обновим грид чтобы показать данные
			url: '/?c=PlanObsDisp&m=makePlan',
			params: params,
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(!Ext.isEmpty(result.Error_Msg)) {
						sw.swMsg.alert(langs('Ошибка'),result.Error_Msg);
					} else {
						win.PlanObsDisp_id = result.PlanObsDisp_id;
						win.doSearch();
					}
				}
			}.createDelegate(this)
		});
	},
	editPlanPerson: function(action, gridtype) {
		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования диспансерной карты пациента уже открыто'));
			return false;
		}
		var win = this;
		var grid = win.DataPanel.getGrid();
		if(gridtype=='error') grid = win.ErrorPanel.getGrid();
		
		if(Ext.isEmpty(grid.getSelectionModel().getSelected())) return false;
			rec = grid.getSelectionModel().getSelected(),
			params = new Object(),
			formParams = new Object();

		params.action = action;
		params.callback = Ext.emptyFn;
		params.onHide = function() {
			win.doSearch();
		}.createDelegate(this);

		formParams.Person_id = rec.get('Person_id');
		formParams.Server_id = rec.get('Server_id');
		formParams.PersonDisp_id = rec.get('PersonDisp_id');

		params.formParams = formParams;

		getWnd('swPersonDispEditWindow').show(params);
	},
	deletePlanPerson: function() {
		var win = this;
		var grid = this.DataPanel.getGrid();
		var grid = win.DataPanel.getGrid();
		var rec = null;
		if(grid.getSelectionModel().getSelected()) rec = grid.getSelectionModel().getSelected();
		var loadMask = new Ext.LoadMask(win.el, { msg: "Подождите, идет удаление..." });
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=PlanObsDisp&m=deletePlanLink',
						params: {
							PlanObsDispLink_id: rec.get('PlanObsDispLink_id')
						},
						callback: function(options, success, response) {
							if (success) {
								loadMask.hide();
								win.doSearch();
							}
						}
					});
				}
			},
			msg: 'Удалить выбранную запись?',
			title: 'Вопрос'
		});
	},
	loadRecords: function(params) {
		var win = this;

		win.DataPanel.getGrid().getStore().baseParams.start = params.start;
		win.DataPanel.getGrid().getStore().baseParams.limit = params.limit;

		var response_obj = new Object();

		response_obj.totalCount = win.data.length;
		response_obj.data = win.data.slice(params.start, params.start+params.limit);
		win.DataPanel.getGrid().getStore().loadData(response_obj);
	},
	loadRecordsError: function(params) {
		var win = this;

		win.ErrorPanel.getGrid().getStore().baseParams.start = params.start;
		win.ErrorPanel.getGrid().getStore().baseParams.limit = params.limit;

		var response_obj = new Object();

		response_obj.totalCount = win.dataError.length;
		response_obj.data = win.dataError.slice(params.start, params.start+params.limit);
		win.ErrorPanel.getGrid().getStore().loadData(response_obj);
	},
	initComponent: function() {
	
		var win = this;
		
		win.FormPanel = new Ext.form.FormPanel({
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'north',
			labelAlign: 'right',
			labelWidth: 130,//112
			height: 220+(getRegionNick().inlist(['ekb', 'buryatiya'])?20:0),
			items: [
			{
				name: 'PlanObsDisp_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				allowBlank: false,
				width: 350,
				editable: false,
				hiddenName: 'DispCheckPeriod_id',
				fieldLabel: 'Период',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swbaselocalcombo',
				store: new Ext.data.JsonStore({
					key: 'DispCheckPeriod_id',
					autoLoad: false,
					fields: [
						{name:'DispCheckPeriod_id',type: 'int'},
						{name:'DispCheckPeriod_Name', type: 'string'},
						//~ {name:'DispCheckPeriod_Year', type: 'int'}, //год есть в win.year
						{name:'PeriodCap_id', type: 'int'},
						{name:'DispCheckPeriod_begDate', type: 'date', format:'d.m.Y'},
						{name:'DispCheckPeriod_endDate', type: 'date', format:'d.m.Y'}
					],
					url: '/?c=PlanObsDisp&m=getDispCheckPeriod'
				}),
				valueField: 'DispCheckPeriod_id',
				displayField: 'DispCheckPeriod_Name',
				listeners: {
					'change': function(combo, newVal, oldVal) {
						var base_form = win.FormPanel.getForm();
						var wdcombo = base_form.findField('TFOMSWorkDirection_id');
						if(getRegionNick()=='ekb') {
							//фильтрация направлений работы: вхождение в период
							wdcombo.getStore().clearFilter();
							wdcombo.getStore().filterBy(function(rec){
								return !Ext.isEmpty(combo.getValue()) 
									&& (
										(Ext.isEmpty(rec.get('TFOMSWorkDirection_endDT')) || rec.get('TFOMSWorkDirection_endDT')>combo.getFieldValue('DispCheckPeriod_begDate') )
										&& 
										(Ext.isEmpty(rec.get('TFOMSWorkDirection_begDT')) || rec.get('TFOMSWorkDirection_begDT')<combo.getFieldValue('DispCheckPeriod_endDate'))
									);
								});
							//доступно ли текущее значение направления работы:
							var ind = wdcombo.getStore().findBy(function(rec){return rec.get('TFOMSWorkDirection_id') == wdcombo.getValue()});
							if(ind<0) base_form.findField('TFOMSWorkDirection_id').clearValue();
						}
					}.createDelegate(this)
				}
			}, {
				allowBlank: getRegionNick()!='ekb',
				width: 350,
				editable: false,
				hiddenName: 'TFOMSWorkDirection_id',
				fieldLabel: 'Направление работы',
				lastQuery: '',
				typeCode: 'int',
				xtype: 'swbaselocalcombo',
				store: new Ext.data.JsonStore({
					key: 'TFOMSWorkDirection_id',
					autoLoad: false,
					fields: [
						{name:'TFOMSWorkDirection_id',type: 'int'},
						{name:'TFOMSWorkDirection_Name', type: 'string'},
						{name:'TFOMSWorkDirection_Code', type: 'int'},
						{name:'TFOMSWorkDirection_begDT', type: 'date', dateFormat: 'd.m.Y'},
						{name:'TFOMSWorkDirection_endDT', type: 'date', dateFormat: 'd.m.Y'}
					],
					url: '/?c=PlanObsDisp&m=getWorkDirectionSpr'
				}),
				valueField: 'TFOMSWorkDirection_id',
				displayField: 'TFOMSWorkDirection_Name',
				listeners: {
					'change': function() {
						
					}.createDelegate(this)
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{TFOMSWorkDirection_Code}</font>&nbsp;{TFOMSWorkDirection_Name}'+
					'</div></tpl>'
				)
			},{

					allowBlank: getRegionNick()!='buryatiya',
					width: 350,
					fieldLabel: 'СМО',
					hiddenName: 'OrgSMO_id',
					listWidth: 450,
					lastQuery: '',
					minChars: 1,
					withoutTrigger: true,
					xtype: 'sworgsmocombo'
				}, {
				xtype: 'button',
				id: 'PlanObsDispEditMakeButton',
				text: langs('Сформировать'),
				style: 'padding-left: 135px;',
				handler: function() {
					win.makePlan();
				}
			},
			{
				xtype: 'fieldset',
				style:'padding: 0px 3px 0px 6px;',
				region: 'north',
				autoHeight: true,
				listeners: {
					expand: function() {
						win.FormPanel.setHeight(220+(getRegionNick()=='ekb'?20:0));
						this.ownerCt.doLayout();
						win.syncSize();
					},
					collapse: function() {
						win.FormPanel.setHeight(90+(getRegionNick()=='ekb'?20:0));
						win.syncSize();
					}
				},
				collapsible: true,
				collapsed: false,
				title: langs('Фильтр'),
				bodyStyle: 'background: #DFE8F6; padding: 5px;',
				labelWidth: 100,
				items: [{
					fieldLabel: 'ФИО',
					name: 'Person_FIO',
					xtype: 'textfieldpmw',
					width: 350
				}, {
					layout: 'column',
					items: [{
						layout:'form',
						bodyStyle:'background: #DFE8F6;',
						labelWidth: 100,
						border: false,
						items: [{
							fieldLabel: 'Дата рождения',
							name: 'Person_Birthday',
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 140
						}]
					}]
				}, {
					xtype: 'swbaselocalcombo',
					fieldLabel: 'Диагноз',
					hiddenName: 'Diag_id',
					valueField: 'Diag_id',
					width: 350,
					xtype: 'swdiagcombo'
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'PlanPersonListStatusType',
					fieldLabel: 'Статус записи',
					hiddenName: 'PlanPersonListStatusType_id',
					width: 350
				}, {
					border: false,
					style: 'margin-top: 5px;',
					layout: 'column',
					items: [{
						border: false,
						style: 'margin-left: 315px;',
						layout: 'form',
						items: [{
							xtype: 'button',
							handler: function()
							{
								win.doSearch();
							},
							iconCls: 'search16',
							text: BTN_FRMSEARCH
						}]
					}, {
						border: false,
						style: 'margin-left: 5px;',
						layout: 'form',
						items: [{
							xtype: 'button',
							handler: function()
							{
								win.doReset();
							},
							iconCls: 'resetsearch16',
							text: BTN_FRMRESET
						}]
					}]
				}]
			}]
		});
		win.pageSize = 100;//в гридах - кол-во строк на страницу
		//TAG: грид
		win.DataPanel = new sw.Promed.ViewFrame({
			object: 'DispList',
			focusOnFirstLoad: false, 
			dataUrl: '/?c=PlanObsDisp&m=loadPlan',
			toolbar: true,
			useEmptyRecord: false,
			autoLoadData: false,
			pageSize: win.pageSize,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			border: false,
			autoExpandColumn: 'autoexpand',
			onRowSelect: function(sm,rowIdx,record) {
				win.DataPanel.getAction('action_delete').setDisabled(win.action=='view' || record.get('StatusType_id')!=1);
			},
			onLoadData: function(data) {
				//~ win.DataPanel.getGrid().getSelectionModel().clearSelections();
				var rec = win.DataPanel.getGrid().getSelectionModel().getSelected();
				win.DataPanel.getAction('action_delete').setDisabled(win.action=='view' || Ext.isEmpty(rec) || rec.get('StatusType_id')!=1);
			},
			stringfields:
			[
				{name: 'PlanObsDispLink_id', type: 'int', hidden: true},
				{name: 'PersonDisp_id', type: 'int', hidden: true},
				
				{name: 'Person_id', type: 'int', header: 'Id', hidden: false},
				{name: 'Person_FIO', header: 'ФИО', width: 300, id:'autoexpand'},
				{name: 'Person_Birthday', header: 'Дата рождения', width: 150},
				{name: 'WorkDirection', header: 'Направление работы', width: 150, hidden: getRegionNick()!='ekb'},
				{name: 'CardNumber', header: '№ карты', type: 'string'},
				{name: 'begDate', header: 'Взят', type: 'date', width: 150},
				{name: 'endDate', header: 'Снят', type: 'date', width: 150},
				{name: 'Diagnoz', header: 'Диагноз', type: 'string', width: 100},
				{name: 'VizitDate', header: 'Дата посещения', type: 'date', format: 'd.m.Y', width: 150},
				{name: 'StatusType_Name', header: 'Статус', type: 'string', width: 150},
				{name: 'StatusDate', header: 'Дата установки статуса', type: 'date', width: 150},
				{name: 'Errors', header: 'Ошибки', type: 'string', width: 150},
				
				{name: 'Errors_text', type: 'string', hidden: true},
				
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'StatusType_id', type: 'int', hidden: true}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{ field: 'Errors', tpl: '{Errors_text}' }
				])
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: false},
				{name:'action_edit', hidden: false, disabled: false, handler: function(){
					win.editPlanPerson('edit','data')
				}},
				{name:'action_view', hidden: false, disabled: false, handler: function(){
					win.editPlanPerson('view','data')
				}},
				{name:'action_delete', hidden: false, disabled: false, handler: function(){
					win.deletePlanPerson()
				}},
				{name:'action_refresh', hidden: true, disabled: false },
				{name:'action_print', hidden: true, disabled: false }
			]
		});
		
		this.DataPanel.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if ( row.get('StatusType_id')==4 ) {
					cls = cls + 'x-grid-rowred ';
				}

				if (cls.length == 0) {
					cls = 'x-grid-panel';
				}

				return cls;
			}
		});
		
		this.DataPanel.getGrid().getStore().addListener('beforeload', function(store, options) {
			win.loadRecords(options.params);
			return false;
		});
		
		win.ErrorPanel = new sw.Promed.ViewFrame(
		{
			object: 'ErrorDispList',
			dataUrl: '/?c=PlanObsDisp&m=loadPlanPersonList',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			noSelectFirstRowOnFocus: true,
			focusOnFirstLoad: false, 
			useEmptyRecord: false,		
			pageSize: win.pageSize,
			border: false,
			autoExpandColumn: 'autoexpand',
			stringfields:
			[
				{name: 'PersonDisp_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'person_id', hidden: true},
				{name: 'Person_FIO', header: 'ФИО', type: 'string', width: 300, id:'autoexpand'},
				{name: 'Person_Birthday', header: 'Дата рождения', type: 'date', width: 150},
				{name: 'CardNumber', header: '№ карты', type: 'string'},
				{name: 'begDate', header: 'Взят', type: 'date', width: 150},
				{name: 'endDate', header: 'Снят', type: 'date', width: 150},
				{name: 'Diagnoz', header: 'Диагноз', type: 'string', width: 100},
				{name: 'VizitDate', header: 'Дата посещения', type: 'date', format: 'd.m.Y', width: 150},
				{name: 'Errors', header: 'Ошибка', type: 'string', width: 100, hidden: getRegionNick() != 'ekb'}
			],
			
			actions:
			[
				{name:'action_add', hidden: true, disabled: false},
				{name:'action_edit', hidden: false, disabled: false, handler: function(){
					win.editPlanPerson('edit','error')
				}},
				{name:'action_view', hidden: false, disabled: false, handler: function(){
					win.editPlanPerson('view','error')
				}},
				{name:'action_delete', hidden: true, disabled: false },
				{name:'action_refresh', hidden: true, disabled: false },
				{name:'action_print', hidden: true, disabled: false }
			],
			onLoadData: function()
			{
			}
		});
		
		/*this.ErrorPanel.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('PlanPersonListStatusType_id') == 4) {
					cls = cls + 'x-grid-rowred ';
				}

				if (cls.length == 0) {
					cls = 'x-grid-panel';
				}

				return cls;
			}
		});*/
		
		this.ErrorPanel.getGrid().getStore().addListener('beforeload', function(store, options) {
			win.loadRecordsError(options.params);
			return false;
		});
		
		win.tabs = new Ext.TabPanel({
			activeTab: 0,
			layoutOnTabChange: true,
			plain: true,
			region: 'center',
			items: [{
				bodyStyle: 'background-color: #00f;',
				border: false,
				title: langs('Данные'),
				layout: 'fit',
				items: [ win.DataPanel ]
			}, {
				border: false,
				layout: 'fit',
				title: langs('Ошибки данных'),
				items: [ win.ErrorPanel ]
			}]
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [
				win.FormPanel,
				
				//~ win.DataPanel
				win.tabs
			],
			buttons:
			[{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(win.title);
				}.createDelegate(win)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					win.hide();
				}.createDelegate(win)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
				/*	if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						win.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						win.makePlan();
						return false;
					}*/
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: win,
				stopEvent: win
			}]
		});
		sw.Promed.swPlanObsDispEditWindow.superclass.initComponent.apply(win, arguments);
	}
});