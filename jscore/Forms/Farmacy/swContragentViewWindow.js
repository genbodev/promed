/**
* swContragentViewWindow - форма просмотра организаций.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Андрей Марков
* @version      12.2009
* @comment      
*
*/
/*NO PARSE JSON*/
sw.Promed.swContragentViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['kontragentyi'],
	layout: 'border',
	id: 'FarmacyContragentViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	codeRefresh: true,
	objectName: 'swContragentViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swContragentViewWindow.js',
	buttonAlign : "right",
	buttons:
	[
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	openOrgPassportEditWindow: function()
	{
		var grid = this.ContragentGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		var org_id = record.get('Org_id');
		var action = 'view';
		var is_farmacy_admin = (getGlobalOptions().groups && getGlobalOptions().groups.toString().indexOf('FarmacyAdmin') != -1);
		var org_type = record.get('OrgType_SysNick');

		if (
			this.ARMType != 'dpoint' &&
			(
				isSuperAdmin() ||
				(getGlobalOptions().isMinZdrav && org_type != 'smo') ||
				(is_farmacy_admin && org_type == 'farm') ||
				//(this.ARMType != 'merch' && record.get('OrgServer_id') > 0 && org_type != 'smo' && org_type != 'farm') ||
				(this.ARMType == 'merch' && isOrgAdmin() && getGlobalOptions().org_id == org_id)
			) && !getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible()
		) {
			action = 'edit';
		}

		if (!Ext.isEmpty(org_id)) {
			getWnd('swOrgEditWindow').show({action: action,mode: 'passport',Org_id: org_id});
		}
	},
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	loadGridWithFilter: function(clear) {
		var wnd = this;
		var base_form = this.FiltersPanel.getForm();
		wnd.ContragentGrid.removeAll();
		if (clear){
			base_form.reset();
			base_form.findField('Contragent_id').getStore().baseParams.ContragentType_id = null;
		}
		var params = new Object();
		params.ContragentType_id = base_form.findField('ContragentType_id').getValue();
		params.Contragent_aid = base_form.findField('Contragent_id').getValue();
		params.start = 0;
		params.limit = 100;
		params.ContragentOrg_Org_id = (!Ext.isEmpty(this.ARMType) && this.ARMType.inlist(['adminllo', 'minzdravdlo']) ? 'minzdrav' : null);

		wnd.ContragentGrid.loadData({globalFilters: params});
	},
	openRecordEditWindow: function(action, gridCmp) {
		var grid = gridCmp.getGrid();
		if( !action.inlist(['add','edit','view']) )
			return false;

		var params = new Object();

		var record = grid.getSelectionModel().getSelected();
		if (!record && (action!='add')) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		} else if (action!='add') {
			params.Contragent_id = record.get('Contragent_id');
		}
		params.action = action;
		params.ARMType = this.ARMType;
		params.owner = gridCmp;
		params.callback = gridCmp.refreshRecords;
		getWnd(gridCmp.editformclassname).show(params);
	},
	deleteRecord: function(gridCmp) {
		var grid = gridCmp.getGrid();

		var params = {};

		var record = grid.getSelectionModel().getSelected();
		if (!record) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		params.Contragent_id = record.get('Contragent_id');

		Ext.Ajax.request({
			callback: function(opt, scs, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);log(response_obj);
				if (response_obj[0] && !Ext.isEmpty(response_obj[0].Error_Msg)) {
					sw.swMsg.alert(lang['oshibka'], response_obj[0].Error_Msg);
				} else if (!Ext.isEmpty(response_obj.Error_Msg)) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				} else {
					sw.swMsg.show({
						buttons:Ext.Msg.YESNO,
						fn:function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (Ext.isEmpty(response_obj.Error_Msg)) {
											gridCmp.getAction('action_refresh').execute();
										}
									}.createDelegate(this),
									params: params,
									url: '/?c=Farmacy&m=deleteContragent'
								});
							}
						}.createDelegate(this),
						icon:Ext.MessageBox.QUESTION,
						msg:lang['vyi_hotite_udalit_zapis'],
						title:lang['podtverjdenie']
					});
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=Farmacy&m=checkContragentOrgInDocs'
		});
	},
	show: function()
	{
		sw.Promed.swContragentViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('FarmacyContragentViewWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;

		this.ARMType = null;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		form.ContragentGrid.addActions({
			name: 'action_org_passport',
			text: lang['pasport_organizatsii'],
			disabled: true,
			handler: function() {
				form.openOrgPassportEditWindow();
			}
		});

		form.findById('ovFilterFieldSet').expand();
		form.FiltersPanel.getForm().findField('Contragent_id').getStore().load();

		// Установка фильтров при открытии формы просмотра
		// form.findById('ovOrgStatus_id').setValue('');
		if (!getGlobalOptions().OrgFarmacy_id)
		{
			// Если не аптека, то ввод отделений и редактирование разрешено 
			//form.ContragentGrid.setActionDisabled('action_add', false);
		}
		//TODO: временное решение для показа
		//form.ContragentGrid.setActionDisabled('action_add', false);

		var mode = null;

		// Если режим аптеки - показываем все контрагенты без ЛПУ
		if (getGlobalOptions().isFarmacy) {
			mode = 'all_without_lpu';
		}

		var params = new Object();
		params.start = 0;
		params.limit = 100;
		params.mode = mode;
		params.ContragentOrg_Org_id = ((this.ARMType&&this.ARMType.inlist(['adminllo', 'minzdravdlo'])) ? 'minzdrav' : null);

		form.ContragentGrid.loadData({globalFilters: params});
		
		//this.ContragentGrid.setReadOnly(!isSuperAdmin() && !getGlobalOptions().isMinZdrav);

		//в зависимости от типа АРМ для редактирования контрагента используются разные формы
		if (this.ARMType && this.ARMType.inlist(['merch', 'dpoint', 'mekllo', 'minzdravdlo', 'adminllo'])) {
			this.ContragentGrid.editformclassname = 'swDloContragentEditWindow';
		} else {
			this.ContragentGrid.editformclassname = 'swContragentEditWindow';
		}
        this.readOnly = false;
        if(arguments[0])
        {
            if(arguments[0].onlyView){
                this.readOnly = true;
            }
        }
		loadMask.hide();
	},
	clearFilters: function ()
	{
		// Гипотетически при наличии фильтров
	},
	initComponent: function()
	{
		var form = this;
		this.FiltersPanel = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadGridWithFilter();
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
						this.ownerCt.doLayout();
						form.syncSize();
					},
					collapse: function() {
						form.syncSize();
					}
				},
				collapsible: true,
				collapsed: true,
				title: lang['filtr'],
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
						items: [
						{
							hiddenName: 'ContragentType_id',
							anchor: '100%',
							fieldLabel: lang['tip_kontragenta'],
							xtype: 'contragenttypecombo',
							id: 'ovContragentType_id',
							listeners: {
								'select': function(field, rec) {
									var contragentcombo = form.FiltersPanel.getForm().findField('Contragent_id');
									contragentcombo.getStore().baseParams.ContragentType_id = rec.get('ContragentType_id');
									contragentcombo.getStore().load({
										callback: function() {
											contragentcombo.setValue();
											contragentcombo.fireEvent('change',contragentcombo,contragentcombo.getValue());
										}
									});
								},
								'expand': function() {
									this.getStore().clearFilter();
								}
							}
						},
						/*{
							name: 'Org_Name',
							anchor: '100%',
							disabled: false,
							fieldLabel: lang['naimenovanie_organizatsii'],
							tabIndex: 0,
							xtype: 'textfield',
							id: 'ovOrg_Name'
						},*/
						{
							xtype: 'hidden',
							anchor: '100%'
						}]
					}, {
						// Средняя часть фильтров
						labelAlign: 'top',
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .44,
						items: [
							{
								fieldLabel: lang['kontragent'],
								hiddenName: 'Contragent_id',
								anchor: '100%',
								xtype: 'swcontragentcombo'
							},
							/*{
								name: 'Org_Nick',
								anchor: '100%',
								disabled: false,
								fieldLabel: lang['kratkoe_naimenovanie'],
								tabIndex: 0,
								xtype: 'textfield',
								id: 'ovOrg_Nick'
							},*/
							{
								xtype: 'hidden',
								anchor: '100%'
							}]
					}, {
						// Правая часть фильтров (кнопка)
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .12,
						items: [
						{
							xtype: 'button',
							text: lang['ustanovit'],
							tabIndex: 4217,
							minWidth: 110,
							disabled: false,
							topLevel: true,
							allowBlank:true,
							id: 'ovButtonSetFilter',
							handler: function ()
							{
								form.loadGridWithFilter();
							}
						},
						{
							xtype: 'button',
							text: lang['otmenit'],
							tabIndex: 4218,
							minWidth: 110,
							disabled: false,
							topLevel: true,
							allowBlank:true,
							id: 'ovButtonUnSetFilter',
							handler: function ()
							{
								form.loadGridWithFilter(true);
							}
						}]
					}]
				}]
			}]
		});
		// Контаргенты
		this.ContragentGrid = new sw.Promed.ViewFrame(
		{
			id: 'ContragentGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'Contragent',
			editformclassname: 'swContragentEditWindow',
			dataUrl: '/?c=Farmacy&m=loadContragentView',
			toolbar: true,
			root: 'data',
			gFilters: {start: 0, limit: 100},
			totalProperty: 'totalCount',
			autoLoadData: false,
			/*filterPanel: new Ext.FormPanel({
				autoHeight: true,
				frame: true,
				region: 'north',
				border: false,
				items:  [
					new Ext.form.FieldSet({
						bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
						border: true,
						autoHeight: true,
						layout: 'column',
						title: lang['filtryi'],
						id: 'OrgFilterPanel',
						items: [{
							// Левая часть фильтров
							labelAlign: 'top',
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;padding-right:5px;',
							columnWidth: .30,
							items:
								[{
									name: 'Org_Name',
									anchor: '100%',
									disabled: false,
									fieldLabel: lang['naimenovanie_organizatsii'],
									tabIndex: 0,
									xtype: 'textfield',
									id: 'ovOrg_Name'
								}]
						},
						{
							// Средняя часть фильтров
							labelAlign: 'top',
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;padding-left:5px;',
							columnWidth: .30,
							items: [{
								name: 'Org_Nick',
								anchor: '100%',
								disabled: false,
								fieldLabel: lang['kratkoe_naimenovanie'],
								tabIndex: 0,
								xtype: 'textfield',
								id: 'ovOrg_Nick'
							}]
						}]})],
					keys: [{
						key: [
							Ext.EventObject.ENTER
						],
						fn: function(inp, e) {
							e.stopEvent();

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

							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							Ext.getCmp('OrgViewForm').loadGridWithFilter();
						},
						stopEvent: true
					}]}),*/

		stringfields:
			[
				// Поля для отображение в гриде
				{name: 'Contragent_id', type: 'int', header: 'ID', key: true},
				{name: 'ContragentType_id', hidden: true},
				{name: 'ContragentType_Name', header: lang['tip'], width: 100},
				{name: 'Org_id', hidden: true},
				{name: 'OrgType_SysNick', hidden: true},
				{name: 'OrgServer_id', hidden: true},
				{name: 'OrgFarmacy_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Contragent_Code', header: lang['kod'], width: 100},
				{name: 'Contragent_Name', id: 'autoexpand', header: lang['polnoe_naimenovanie']},
				{name: 'BegDate', type: 'date', header: lang['data_nachala'], width: 100},
				{name: 'EndDate', type: 'date', header: lang['data_okonchaniya'], width: 100}
			],
			actions:
			[
				{name:'action_add', disabled: true, handler: function(){form.openRecordEditWindow('add',form.ContragentGrid);}},
				{name:'action_edit', handler: function(){form.openRecordEditWindow('edit',form.ContragentGrid);}},
				{name:'action_view', handler: function(){form.openRecordEditWindow('view',form.ContragentGrid);}},
				{name:'action_delete', disabled: true, handler: function(){form.deleteRecord(form.ContragentGrid);}}
			], 
			onLoadData: function(result)
			{
				var win = Ext.getCmp('FarmacyContragentViewWindow');
				var data = win.ContragentGrid.getGrid().getStore().data;
				if (data && data.first() && data.first().id > 0) {
					var record = data.first();

					//определяем доступно ли добавление и удаление
					if (!win.readOnly && !isFarmacyInterface) {
						win.ContragentGrid.ViewActions.action_add.setDisabled(Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id <= 0);
						win.ContragentGrid.ViewActions.action_delete.setDisabled(Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id <= 0);
					} else {
						win.ContragentGrid.ViewActions.action_add.setDisabled(true);
						win.ContragentGrid.ViewActions.action_delete.setDisabled(true);
					}

					//определяем доступно ли редактирование
					if (
						!win.readOnly &&
						record.get('Contragent_id') != null &&
						(
							record.get('Contragent_id') == getGlobalOptions().Contragent_id ||  //выбран текущий контрагент
							!isFarmacyInterface ||
							getGlobalOptions().isMinZdrav || //текущая организация является минздравом
							win.ARMType == 'merch' //спиок контрагентов открыт через арм товароведа
						)
					) {
						win.ContragentGrid.ViewActions.action_edit.setDisabled(false);
					} else {
						win.ContragentGrid.ViewActions.action_edit.setDisabled(true);
					}
				}
			},
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('FarmacyContragentViewWindow');
				win.ContragentGrid.ViewActions.action_delete.setDisabled((record.get('Lpu_id')!=Ext.globalOptions.globals.lpu_id));

				//определяем доступно ли добавление и удаление
				if (!win.readOnly && !isFarmacyInterface) {
					win.ContragentGrid.ViewActions.action_add.setDisabled(Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id <= 0);
					win.ContragentGrid.ViewActions.action_delete.setDisabled(Ext.isEmpty(getGlobalOptions().lpu_id) || getGlobalOptions().lpu_id <= 0);
				} else {
					win.ContragentGrid.ViewActions.action_add.setDisabled(true);
					win.ContragentGrid.ViewActions.action_delete.setDisabled(true);
				}

				//определяем доступно ли редактирование
				if (
					!win.readOnly &&
						record.get('Contragent_id') != null &&
						(
							record.get('Contragent_id') == getGlobalOptions().Contragent_id ||  //выбран текущий контрагент
								!isFarmacyInterface ||
								getGlobalOptions().isMinZdrav || //текущая организация является минздравом
								win.ARMType == 'merch' //спиок контрагентов открыт через арм товароведа
							)
					) {
					win.ContragentGrid.ViewActions.action_edit.setDisabled(false);
				} else {
					win.ContragentGrid.ViewActions.action_edit.setDisabled(true);
				}

				//для контрагентов с типами "Организация", "Аптека" и "Региональный склад" делаем доступной кнопку Паспорт организации
				if (
					(record.get('Org_id') > 0) &&
					(record.get('ContragentType_id') == 1 || record.get('ContragentType_id') == 3 || record.get('ContragentType_id') == 6)
				) {
					win.ContragentGrid.ViewActions.action_org_passport.setDisabled(false);
				} else {
					win.ContragentGrid.ViewActions.action_org_passport.setDisabled(true);
				}
			}
		});
		
		this.ContragentGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Contragent_id')==getGlobalOptions().Contragent_id)
					cls = cls+'x-grid-rowselect ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items:
			[
				form.FiltersPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					defaults: {split: true},
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.ContragentGrid]
						}
					]
				}
			]
		});
		sw.Promed.swContragentViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
