/**
 * swImportWindow - окно импорта.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      июнь.2012
 */
sw.Promed.swImportWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Обновление регистров'),
	layout: 'border',
	id: 'ImportWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttons: [
		{
			text: '-'
		},
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function (/*button, event*/) {
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text: BTN_FRMCLOSE,
			tabIndex: -1,
			tooltip: langs('Закрыть'),
			iconCls: 'cancel16',
			handler: function () {
				this.ownerCt.hide();
			}
		}
	],
	show: function (){
		sw.Promed.swImportWindow.superclass.show.apply(this, arguments);
        var RegisterList_Name = null;
        var globalOptions = getGlobalOptions();
        var sysNick = globalOptions.CurMedServiceType_SysNick;

        if (globalOptions.superadmin){
            RegisterList_Name = null;
        }else if (sysNick == 'okadr'){
            RegisterList_Name = 'MedPersonal';
        }
		 if(arguments[0]&&arguments[0].RegisterList_Name){
			RegisterList_Name = arguments[0].RegisterList_Name;
			
		}
		this.ARMType = arguments[0] && arguments[0].ARMType ? arguments[0].ARMType : null;
		this.RegisterListDetailLogGrid.addActions({name:'action_exportlog', id: 'id_action_exportlog', handler: function() {this.exportLog();}.createDelegate(this),hidden:true,disabled:true, text:langs('Экспорт лог-файла'), tooltip: langs('Экспорт лог-файла')});

		this.RegisterListLogGrid.setActionDisabled('action_refresh',true);
		this.RegisterListDetailLogGrid.setActionDisabled('action_refresh',true);
		this.RegisterListLogGrid.getGrid().getStore().removeAll();
		this.RegisterListDetailLogGrid.getGrid().getStore().removeAll();
		
		if (RegisterList_Name == 'AttachJournal') {
			this.setTitle(langs('Журнал импорта/экспорта данных о прикреплении'));
		} else {
			this.setTitle(langs('Обновление регистров'));
		}
 
		this.RegisterListGrid.loadData({
            globalFilters: { RegisterList_Name: RegisterList_Name }
        });
		var form = this.filterPanel.getForm();
		if (getRegionNick() != 'vologda' || this.ARMType == 'lpuadmin' ) {
			form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id)
		}
		if(!isSuperAdmin() || (getRegionNick() == 'vologda' && this.ARMType == 'lpuadmin')){
			
			form.findField('Lpu_id').disable();
		}else{
			form.findField('Lpu_id').enable();
		}
		if ( getRegionNick() == 'vologda' ){
			var date1 = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			var date2 = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
				date1.setDate(date1.getDate()-30);
		} else {
			var date1 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y')).getFirstDateOfMonth();
			var date2 = date1.getLastDateOfMonth();
		}
		form.findField('importRange').setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	callback:function(){
		var act = this.RegisterListGrid
		sw.swMsg.alert('Информация',"Ваш запрос выполняется.");
		this.RegisterListLogGrid.loadData({
			callback:function(q,w,e){
				if(q[0]&&q[0].get('RegisterListResultType_id')==2){
					q[0].set('RegisterListResultType_id_Name',"<b>"+q[0].get('RegisterListResultType_id_Name')+"</b>");
					q[0].commit();
					if(!isSuperAdmin)act.ViewActions.action_add.disable()
				}else{
					act.ViewActions.action_add.enable()
				}
			},
			noFocusOnLoad: true
		});
	},
	exportLog:function(){
		var record = this.RegisterListLogGrid.getGrid().getSelectionModel().getSelected();
		if(record && record.get('RegisterListLog_id')){
			window.open('/export/ImportIns'+record.get('RegisterListLog_id')+'.zip','_blank');
		}
	},
	exportCSV:function(){
		Ext.Ajax.request({
				method: 'post',
				url: "/?c=ImportSchema&m=Export",
				callback: function(opt, success, r) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						window.open('/'+obj.url);
					}
				}.createDelegate(this)
			});
	},
	doReset: function(){
		var form = this.filterPanel.getForm();
			
		form.reset();
		//form.findField('SkladOstat_Sklad').focus(true, 250);
		
		this.doSearch();
	},
	enableField_RegisterListNameFile: function(){
		/*var grid = this.RegisterListLogGrid.getGrid();
		var grisStore = grid.getStore();
		var enable = (grisStore.baseParams.RegisterList_id && grisStore.baseParams.RegisterList_id == 1) ? false : true;*/

		var enable = true;
		var grid = this.RegisterListGrid.getGrid();
		if (grid){
			var rec = grid.getSelectionModel().getSelected();
			if(rec){
				enable = (rec.get('RegisterList_Name').inlist(['PersonPrivilege', 'PersonZL', 'PersonCardAttachStatus', 'PersonCard'])) ? false : true;
			}
		}
		this.RegisterListLogGrid.setColumnHidden('RegisterListLog_NameFile',enable);
	},
	doSearch: function(){
		var form = this.filterPanel.getForm();
		var grid = this.RegisterListLogGrid.getGrid();
		var grisStore = grid.getStore();
		var stringfields = this.RegisterListLogGrid.stringfields;
		var that = this;
		this.enableField_RegisterListNameFile();
		var	params = {}; //refs #20773 - выбираем грид с историей
		grid.getStore().removeAll(); //очищаем его
		params.Lpu_id = form.findField('Lpu_id').getValue()||0;
		params.RegisterListLog_begDT = form.findField('importRange').getValue1();
		params.RegisterListLog_endDT = form.findField('importRange').getValue2();
		this.RegisterListLogGrid.loadData({
			callback:function(q){
				if(q[0]&&q[0].get('RegisterListResultType_id')==2){
					q[0].set('RegisterListResultType_id_Name',"<b>"+q[0].get('RegisterListResultType_id_Name')+"</b>");
					q[0].commit();
					if(!isSuperAdmin)that.RegisterListLogGrid.ViewActions.action_add.disable();

				}else{
					that.RegisterListLogGrid.ViewActions.action_add.enable()
				}
			},
            globalFilters: params,
			noFocusOnLoad: true
        });
	},
	initComponent: function () {
		var that = this;
		this.RegisterListGrid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add', text: langs('Запуск'), tooltip: langs('Запустить импорт выбранного справочника/регистра'), icon: 'img/icons/actions16.png',
					handler: function () {
						//refs #20773 переменая для выбраного справочника/регистра
						if ( typeof that.RegisterListGrid.getGrid().getSelectionModel().getSelected() != 'object' ) {
							return false;
						}
						else if ( Ext.isEmpty(that.RegisterListGrid.getGrid().getSelectionModel().getSelected().get('RegisterList_id')) ) {
							return false;
						}
						
						var RegisterList_id = that.RegisterListGrid.getGrid().getSelectionModel().getSelected().get('RegisterList_id');
						var RegisterList_Name = that.RegisterListGrid.getGrid().getSelectionModel().getSelected().get('RegisterList_Name');
						var params = {};
						params.RegisterList_id = RegisterList_id;
						params.RegisterList_Name = RegisterList_Name;
						params.ARMType = that.ARMType;
						params.callback = function () {
							that.RegisterListLogGrid.loadData({
								noFocusOnLoad: true
							});
						};
						switch (RegisterList_Name) {
							case 'PersonPrivilege':
							case 'PersonDead':
							case 'Org':
							case 'CmpCallCard':
								getWnd('swDbfImportWindow').show(params);
								break;
							case 'MedPersonal':
								getWnd('swXmlImportWindow').show(params);
								break;
							case 'PersisFRMP':
								getWnd('swPersisFRMPImportWindow').show(params);
								break;
							case 'PersonZL': // Импорт регистра прикрепленного населения
								var wnd = getRegionNick() == 'vologda' ? 'swPersonCardImportRegisterWindow' : 'swImportZLWindow';
								getWnd(wnd).show(params);
								break;
							case 'PersonCardAttach': // Экспорт заявлений о прикреплении
								getWnd('swPersonCardAttachExportWindow').show(params);
								break;
							case 'PersonCardAttachStatus': // Импорт ответа по заявлениям о прикреплении
								getWnd('swPersonCardAttachImportResponseWindow').show(params);
								break;
							case 'PersonCard': // Импорт сведений об открепленном населении
								getWnd('swPersonCardImportDetachWindow').show();
								break;
						}
						
					}
				},
				{name: 'action_edit', hidden: true},
				{name: 'action_view',hidden:true,text: langs('Выгрузка ФРЛ в csv'), handler: function () {that.exportCSV()}},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'},
				{name: 'action_refresh', hidden: true}

			],
			/*refs #20773 обновление истории загрузок в зависимости от выбраного справочника*/
			onRowSelect: function (sm, index, record) {
				var rec = sm.getSelected();
				var act = this;
				var fp = that.findById('filterpanel');
				that.RegisterListLogGrid.setActionDisabled('action_refresh',false);
				that.RegisterListDetailLogGrid.setActionHidden('action_exportlog',true);
				that.RegisterListDetailLogGrid.setActionDisabled('action_exportlog',true);
				that.RegisterListDetailLogGrid.setActionDisabled('action_refresh',true);
				that.RegisterListDetailLogGrid.getGrid().getStore().removeAll();
				fp.setVisible(false);
				///that.filterPanel.setHidden(getRegionNick() != 'kareliya');
				if(record&&record.get('RegisterList_Name')!=0){
					switch (record.get('RegisterList_Name')) {
						case 'PersonZL':
						if(getRegionNick() == 'kareliya'){
							fp.setVisible(true);
						}
						break;
					}
					
					if(getRegionNick() == 'vologda'){
						fp.setVisible(true);
					}
					
					that.doLayout();
				}
				if (rec.get('RegisterList_id') > 0) {
					var grid = that.RegisterListLogGrid.getGrid();
					grid.getStore().baseParams = {RegisterList_id: rec.get('RegisterList_id')};//передаем в параметры id справочника
					that.doSearch();												//загружаем грид с выбраным справочником
				}
			},
			/**/
			onDblClick: function () {
				//отключаю функцию редактирования по даблклику
			},
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'stg',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=RegisterList&m=loadList',
			height: 180,
			region: 'west',
			width: '380',
			object: 'RegisterList',
			editformclassname: 'swRegisterListEditWindow',
			id: 'RegisterListGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'RegisterList_id', type: 'int', header: 'ID', key: true},
				{name: 'RegisterList_Name', type: 'string', header: langs('Объект'), width: 120},
				{name: 'RegisterList_Schema', type: 'string', hidden: true, header: langs('схема БД'), width: 120},
				{name: 'RegisterList_Descr', type: 'string', header: langs('Наименование'), id: 'autoexpand', width: 120},
				{name: 'Region_id_Name', type: 'string', hidden: true, header: langs('Идентификатор региона справочника территорий'), width: 120},
				{name: 'Region_id', type: 'int', hidden: true}
			],
			title: langs('Регистры/справочники'),//<-refs #20773
			toolbar: true
		});
		this.RegisterListLogGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'},
				{name:'action_refresh',handler: function () {
						that.RegisterListLogGrid.getGrid().getStore().load({callback:function(q){
							if(q[0]&&q[0].get('RegisterListResultType_id')==2){
								q[0].set('RegisterListResultType_id_Name',"<b>"+q[0].get('RegisterListResultType_id_Name')+"</b>");
								q[0].commit();
								if(!isSuperAdmin)that.RegisterListGrid.ViewActions.action_add.disable()
							}else{
								that.RegisterListGrid.ViewActions.action_add.enable()
							}
						}
					})
				}}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'stg',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=RegisterListLog&m=loadList',
			height: 180,
			region: 'center',
			object: 'RegisterListLog',
			id: 'RegisterListLogGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'RegisterListLog_id', type: 'int', header: 'ID', key: true},
				{name: 'RegisterListLog_begDT', type: 'datetimesec', header: langs('Запуск'), width: 120},
				{name: 'RegisterListLog_endDT', type: 'datetimesec', header: langs('Завершение'), width: 120},
				{name: 'RegisterListRunType_id_Name', type: 'string', header: langs('Тип запуска'), width: 120},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 120, hidden: getRegionNick() != 'vologda'},
				{name: 'RegisterListRunType_id', type: 'int', hidden: true},
				{name: 'RegisterListLog_AllCount', type: 'float', header: langs('К загрузке'), width: 80},
				{name: 'RegisterListLog_UploadCount', type: 'float', header: langs('Загружено'), width: 80},
				{name: 'RegisterListResultType_id', type: 'int', hidden: true},
				{name: 'RegisterListResultType_id_Name', id: 'autoexpand', type: 'string', header: langs('Результат'), width: 120},
				{name: '', type: 'string', header: 'Пользователь', width: 120},
				{name: 'RegisterList_id', type: 'int', hidden: true},
				{name: 'RegisterList_id_Name', type: 'string', hidden: true, header: langs('Загружаемый регистр/справочник'), width: 120},
				{name: 'RegisterListLog_NameFile', hidden: false, type: 'string', header: langs('Имя файла импорта'), width: 220},
			],
			title: langs('Лог запусков выбранного регистра/справочника'),
			toolbar: true,
			onRowSelect: function (sm, index, record) {
				var rec = sm.getSelected();
				that.RegisterListDetailLogGrid.setActionHidden('action_exportlog',true);
				that.RegisterListDetailLogGrid.setActionDisabled('action_exportlog',true);
				that.RegisterListDetailLogGrid.setActionDisabled('action_refresh',false);
				
				///that.filterPanel.setHidden(getRegionNick() != 'kareliya');
				if (record && record.get('RegisterList_id_Name') != 0) {
					switch (record.get('RegisterList_id_Name')) {
						case 'PersonPrivilege':
							this.setColumnHeader('RegisterListLog_AllCount', langs('Персон'));
							this.setColumnHeader('RegisterListLog_UploadCount', langs('Льгот'));
							break;
						case 'CmpCallCard':
						case 'MedPersonal':
						case 'PersisFRMP':
						case 'PersonDead':
							this.setColumnHeader('RegisterListLog_AllCount', langs('К загрузке'));
							this.setColumnHeader('RegisterListLog_UploadCount', langs('Загружено'));
							break;
						case 'PersonZL':
							that.RegisterListDetailLogGrid.setActionHidden('action_exportlog', getRegionNick() == 'vologda');
							break;
					}

					that.doLayout();
				}
				if(record&&record.get('RegisterListResultType_id')==1){
					that.RegisterListDetailLogGrid.setActionDisabled('action_exportlog',false);
				}
				var s = that.RegisterListDetailLogGrid.getGrid().getStore();
				s.removeAll();
				if (rec.get('RegisterListLog_id') > 0) {
					s.baseParams = {RegisterListLog_id: rec.get('RegisterListLog_id')};
					
					that.RegisterListDetailLogGrid.loadData({
						noFocusOnLoad: true
					});
				}
			}

		});
		this.RegisterListDetailLogGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			onRowSelect: function (sm, index, record) {
				/*/this.setActionDisabled('action_exportlog', true);
				//this.setActionHidden('action_exportlog',true);
				this.setActionDisabled('action_exportlog', false);
				this.setActionHidden('action_exportlog',false);*/
				if(record&&record.get('RegisterList_id_Name')!=0){
					
				}
			},
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'stg',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=RegisterListDetailLog&m=loadList',
			height: 180,
			region: 'south',
			object: 'RegisterListDetailLog',
			id: 'RegisterListDetailLogGrid',
			root: 'data',
			paging: true,
			style: 'margin-bottom: 10px',
			onDblClick: function () {
				if (that.RegisterListDetailLogGrid.getGrid().getSelectionModel().getSelected()) {
					var selectedData = that.RegisterListDetailLogGrid.getGrid().getSelectionModel().getSelected().data;
					Ext.Msg.minWidth = 600;
					Ext.Msg.alert(langs('Запись детального лога'), langs('Дата и время') + ': '+ selectedData.RegisterListDetailLog_setDT.format('d.m.Y') + '<br>' +
						langs('Тип: ') + selectedData.RegisterListLogType_id_Name + '<br>' +
						'Сообщение: <pre style="overflow: scroll; height: 200px; width: 100%;" >' + selectedData.RegisterListDetailLog_Message + '</pre>');
					Ext.Msg.minWidth = 100;
				}
			},
			stringfields: [
				{name: 'RegisterListDetailLog_id', type: 'int', header: 'ID', key: true},
				{name: 'RegisterListDetailLog_setDT', type: 'datetimesec', header: langs('Дата/время'), width: 120},
				{name: 'RegisterListLogType_id_Name', type: 'string', header: langs('Тип'), width: 80},
				{name: 'RegisterListLogType_id', type: 'int', hidden: true},
				{name: 'RegisterListDetailLog_Message', type: 'string', header: langs('Текст'), width: 120, id: 'autoexpand'},
				{name: 'RegisterListLog_id', type: 'int', hidden: true}
			],
			title: langs('Детальный лог загрузки'),
			toolbar: true
		});
		this.filterPanel = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			region: 'center',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					that.doSearch();
				},
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				id: 'ovFilterFieldSet',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						var fp = that.findById('filterpanel');
						fp.setHeight(90);
						that.doLayout();
					},
					collapse: function() {
						var fp = that.findById('filterpanel');
						fp.setHeight(40);
						that.doLayout();
					}
				},
				collapsible: true,
				collapsed: true,
				title: langs('Фильтр'),
				bodyStyle: 'background: #DFE8F6;',
				items: [{
					layout: 'column',
					items: [{
                            // Левая часть фильтров
                            labelAlign: 'top',
                            layout: 'form',
                            border: false,
                            bodyStyle:'background:#DFE8F6;padding-right:5px;',
                            columnWidth: .44,
                            items:
                                [{
									fieldLabel: langs('МО'),
									name: 'Lpu_id',
									id: 'WIN_Lpu_id',
									width: 200,
									enableKeyEvents: true,
									xtype: 'swlpucombo'
								},
                                    {
                                        xtype: 'hidden',
                                        anchor: '100%'
                                    }]
                        },
                            {
                                // Средняя часть фильтров
                                labelAlign: 'top',
                                layout: 'form',
                                border: false,
                                bodyStyle:'background:#DFE8F6;padding-left:5px;',
                                columnWidth: .44,
                                items:
                                    [ new Ext.form.DateRangeField({
										width: 180,
										showApply: false,
										name:'importRange',
										id:'dateRangeLis',
										fieldLabel: getRegionNick() == 'vologda' ? 'Дата запуска' :  langs('Период запуска'),
										plugins: 
										[
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										]
									}),
                                        {
                                            xtype: 'hidden',
                                            anchor: '100%'
                                        }]
                            },
                            {
                                // Правая часть фильтров (кнопка)
                                layout: 'form',
                                border: false,
                                bodyStyle:'background:#DFE8F6;padding-left:5px;',
                                columnWidth: .12,
                                items:
                                    [{
                                        xtype: 'button',
                                        text: langs('Установить'),
                                        tabIndex: 4217,
                                        minWidth: 110,
                                        disabled: false,
                                        topLevel: true,
                                        allowBlank:true,
                                        id: 'molwButtonSetFilter',
                                        handler: function ()
                                        {
											//that.doReset();
											that.doSearch();
                                        }
                                    },
                                        {
                                            xtype: 'button',
                                            text: langs('Отменить'),
                                            tabIndex: 4218,
                                            minWidth: 110,
                                            disabled: false,
                                            topLevel: true,
                                            allowBlank:true,
                                            id: 'molwButtonUnSetFilter',
                                            handler: function ()
                                            {
                                                that.doReset();
												that.doSearch();
                                            }
                                        }]
                            }
				]}]
				}
				
				],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			
			items: [
				that.RegisterListGrid,
				{
					layout: 'border',
					region: 'center',
					items: [
						{
							layout:'border',
							height:80,
							id:'filterpanel',
							border:false,
							hidden:true,
							region:'north',
							items:[that.filterPanel]
						},
						{
							defaults: {
								split: true
							},
							border:false,
							layout:'border',
							region:'center',
							items:[that.RegisterListDetailLogGrid, that.RegisterListLogGrid]
						}
						
					]
				}
			]
		});
		sw.Promed.swImportWindow.superclass.initComponent.apply(this, arguments);
		//this.RegisterListGrid.setReadOnly(true);
		this.RegisterListLogGrid.setReadOnly(true);
		this.RegisterListDetailLogGrid.setReadOnly(true);
	}
});

