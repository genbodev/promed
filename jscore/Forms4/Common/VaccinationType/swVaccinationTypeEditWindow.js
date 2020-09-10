/**
 * swVaccinationTypeEditWindow - Форма редактирования прививки/реакции
 * common.VaccinationType.swVaccinationTypeEditWindow
 * widget.swVaccinationTypeEditWindow
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */
Ext6.define('common.VaccinationType.swVaccinationTypeEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVaccinationTypeEditWindow',
	autoShow: false,
	maximized: true,
	cls: 'arm-window-new',
	title: 'Вид прививки',
	constrain: true,
	header: true,
	layout: 'border',
	callback: Ext6.emptyFn,
	onDblClick: function(section, value) {
		var win = this;
		win.openEditWindow((this.action == 'view')?'view':'edit',section, value);
	},
	onRecordSelect: function() {
		var win = this;

		if (this.VaccinationPanel.getSelectionModel().hasSelection()) {
			var record = this.VaccinationPanel.getSelectionModel().getSelection()[0];

			if (record.get('VaccinationPanel')) {
				win.VaccinationPanel.down('#action_view').enable();
			}
		}
	},
	getGrid: function ()
	{
		return this.VaccinationPanel;
	},
	getSelectedRecord: function() {
		if (this.VaccinationPanel.getSelectionModel().hasSelection()) {
			var record = this.VaccinationPanel.getSelectionModel().getSelection()[0];
			if (record && record.get('pmUser_id')) {
				return record;
			}
		}
		return false;
	},
	show: function(data) {
		this.callParent(arguments);
		var win = this;
		var base_form = win.VaccinationGeneralPanel.getForm();
		win.doReset(arguments[0].fields);

		win.VaccinationGeneralPanel.queryById('VaccinationTypeisReaction_'+((arguments[0].fields.VaccinationType_isReaction && arguments[0].fields.VaccinationType_isReaction == '2') ? 'Reaction' : 'Vaccination')).setPressed(true);
		this.setTitle(arguments[0].fields.VaccinationType_Name+ ' / Вид '  + ((arguments[0].fields.VaccinationType_isReaction == '2') ? 'реакции' : 'прививки'));
		win.VaccinationGeneralPanel.queryById('VaccinationType_isVaccination').setDisabled(true);

		this.VaccinationType_isReaction = arguments[0].fields.VaccinationType_isReaction;

		this.callParent(arguments);
		if(arguments[0].action) {
			this.action = arguments[0].action;
			win.setViewMode(arguments[0].action);
		} else {
			this.hide();
			return false;
		}

		this.callback = arguments[0].callback;

		if(arguments[0].fields) {
			base_form.findField('VaccinationType_id').setValue(arguments[0].fields.VaccinationType_id);
			base_form.findField('VaccinationType_isReaction').setValue(arguments[0].fields.VaccinationType_isReaction);
			base_form.findField('VaccinationType_RangeDate').setValue(arguments[0].fields.VaccinationType_begDate+' - '+arguments[0].fields.VaccinationType_endDate);
		}

	},
	setViewMode: function(action){
		var win = this;
		var base_form = win.VaccinationGeneralPanel.getForm();
		this.action = action;
		win.down("#Vaccfbar").setVisible(action != 'view');
		win.down("#Riskfbar").setVisible(action != 'view');
		win.down("#Examfbar").setVisible(action != 'view');
		win.down("#Prepfbar").setVisible(action != 'view');
		base_form.findField('VaccinationType_RangeDate').setDisabled(action == 'view');
		if(action != 'view') {
			win.PrepStore.load();
		}
	},
	doSearch: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}
		var win = this;
		var base_form = this.VaccinationGeneralPanel.getForm();
		var extraParams = options;

		win.VaccinationPanel.getStore().proxy.extraParams = extraParams;
		win.VaccinationPanel.setTitle( (options.VaccinationType_isReaction == '2') ? 'Реакция' : 'Прививка');

		win.VaccinationPanel.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});

		win.VaccinationPrepPanel.getStore().proxy.extraParams = extraParams;

		win.VaccinationPrepPanel.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});

		win.RiskGroupPanel.getStore().proxy.extraParams = extraParams;

		win.RiskGroupPanel.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});

		win.PostVaccinationExamPanel.getStore().proxy.extraParams = extraParams;

		win.PostVaccinationExamPanel.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	doReset: function (options) {
		var base_form = this.VaccinationGeneralPanel.getForm();
		base_form.reset();

		this.VaccinationPanel.getStore().removeAll();
		this.RiskGroupPanel.getStore().removeAll();
		this.VaccinationPrepPanel.getStore().removeAll();
		this.PostVaccinationExamPanel.getStore().removeAll();

		this.doSearch(options);
	},
	openEditWindow: function (action , section, record) {
		var win = this;
		var base_form = win.VaccinationGeneralPanel.getForm();

		if (!record && action !== 'add')
			return false;

		var params = {
			action: action,
			Vaccination_id: (record) ? record : '',
			VaccinationTypePrep_id: (record) ? record : '',
			VaccinationType_id: base_form.findField('VaccinationType_id').getValue(),
			VaccinationType_isReaction: base_form.findField('VaccinationType_isReaction').getValue(),
			callback: function (owner) {
				switch(section){
					case 'Prep': win.VaccinationPrepPanel.getStore().reload(); break;
					case 'Vaccination': win.VaccinationPanel.getStore().reload(); break;
				}
			},
		};

		getWnd('swVaccinationType'+section+'EditWindow').show(params);

	},
	deleteItem: function(section,record){
		var win = this;
		if (!record) return false;

		var base_form = win.VaccinationGeneralPanel.getForm();

		if(section == 'Vaccination')
			var itemName = ((base_form.findField('VaccinationType_isReaction').getValue()=='2') ? 'реакцию' : 'прививку');
		else if(section == 'Prep')
			var itemName = 'препарат';

		Ext6.Msg.show({
			title: langs('Подтверждение удаления'),
			msg: langs('Вы действительно желаете удалить '+ itemName +'?'),
			buttons: Ext6.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					win.mask('Удаляем '+ itemName + '...');
					Ext6.Ajax.request({
						url: '/?c=VaccinationType&m=delete'+section,
						params: {
							Vaccination_id: record,
							VaccinationTypePrep_id: record
						},
						callback: function (o, s, r) {
							win.unmask();
							if (s) {
								switch(section){
									case 'Vaccination': win.VaccinationPanel.getStore().reload(); break;
									case 'Prep': win.VaccinationPrepPanel.getStore().reload(); break;
								}
							}
						}
					});
				}
			}
		});
	},
	unSetVaccination: function(params,link){
		var win = this;

		Ext6.Ajax.request({
			url: '/?c=VaccinationType&m=setVaccination'+link,
			params: params,
			failure: function(response, options) {
				Ext6.Msg.alert(langs('Ошибка'), langs('При записи профиля произошла ошибка!'));
			},
			success: function(response, action)
			{
				switch(link){
					case 'RiskGroup': win.RiskGroupPanel.getStore().reload(); break;
					case 'Exam': win.PostVaccinationExamPanel.getStore().reload(); break;
				}
			}
		});
	},
	savePrep: function(params){
		if(Ext6.isEmpty(params))
			return false;

		var win = this;
		var base_form = win.VaccinationGeneralPanel.getForm();
		var PrepData = win.VaccinationPrepPanel.getSelectionModel().getSelection()[0].getData();
		var requestParams = {
			VaccinationTypePrep_id: PrepData.VaccinationTypePrep_id,
			VaccinationTypePrep_begDate: (params.VaccinationTypePrep_begDate) ? params.VaccinationTypePrep_begDate : PrepData.VaccinationTypePrep_begDate,
			VaccinationTypePrep_endDate: (params.VaccinationTypePrep_endDate) ? params.VaccinationTypePrep_endDate : PrepData.VaccinationTypePrep_endDate,
			Prep_id: (params.Prep_id) ? params.Prep_id : PrepData.Prep_id,
			VaccinationType_id: base_form.findField('VaccinationType_id').getValue()
		};

		Ext6.Ajax.request({
			url: '/?c=VaccinationType&m=saveVaccinationPrep',
			params: requestParams,
			failure: function (form, action) {
				win.unmask();
				sw.swMsg.alert(langs('Ошибка'), langs('Во время сохранения препарата произошла ошибка.'));
			},
			success: function (form, action) {
				win.VaccinationPrepPanel.getStore().reload();
			}
		});
	},
	initComponent: function() {
		var win = this;

		win.VaccinationPanel = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'north',
			border: false,
			autoHeight: true,
			userCls:'vaccinationGroupPanel',
			title: 'Прививка',
			selModel: {
				mode: 'SINGLE',
			},
			listeners: {
				itemdblclick: function(v,c) {
					win.onDblClick('Vaccination',c.getData().Vaccination_id);
				}
			},
			store: {
				fields: [
					{name: 'Vaccination_id', type: 'int'},
					{name: 'Vaccination_Name'},
					{name: 'Vaccination_Code'},
					{name: 'Vaccination_Nick'},
					{name: 'Vaccination_isNacCal'},
					{name: 'Vaccination_isEpidemic'},
					{name: 'VaccinationRiskGroupAccess_Name'},
					{name: 'Vaccination_LastName'},
					{name: 'Vaccination_minAge'},
					{name: 'Vaccination_maxAge'},
					{name: 'Vaccination_isSingle'},
					{name: 'Vaccination_begDate'},
					{name: 'Vaccination_endDate'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=VaccinationType&m=loadVaccinationList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				}
			},
			columns: [
				{text: 'Наименование', tdCls: 'padLeft', width: 150, dataIndex: 'Vaccination_Name', flex: 3},
				{text: 'Код', width: 150, dataIndex: 'Vaccination_Code', flex: 1},
				{text: 'Наименование в Ф063у', width: 150, dataIndex: 'Vaccination_Nick', flex: 3},
				{text: 'Нац. календарь', width: 150, dataIndex: 'Vaccination_isNacCal', flex: 1, renderer: function(v) {
						if (v=='2') return "<div style='text-align: center;'><img src='/img/icons/checked16.png' /></div>";
					}},
				{text: 'По эпид. показаниям', width: 150, dataIndex: 'Vaccination_isEpidemic', flex: 1, renderer: function(v) {
						if (v=='2') return "<div style='text-align: center;'><img src='/img/icons/checked16.png' /></div>";
					}},
				{text: 'Доступна пациентам', width: 160, dataIndex: 'VaccinationRiskGroupAccess_Name'},
				{text: 'Выполняется после', width: 150, dataIndex: 'Vaccination_LastName', flex: 2},
				{text: 'Возраст вакцинации', width: 150, dataIndex: 'Vaccination_minAge', flex: 1},
				{text: 'Макс. возраст', width: 150, dataIndex: 'Vaccination_maxAge', flex: 1},
				{text: 'Не совместима с другими', width: 150, dataIndex: 'Vaccination_isSingle', flex: 1, renderer: function(v) {
						if (v=='2') return "<div style='text-align: center;'><img src='/img/icons/checked16.png' /></div>";
					}},
				{text: 'Начало', minWidth: 90, dataIndex: 'Vaccination_begDate'},
				{text: 'Окончание', minWidth: 90, dataIndex: 'Vaccination_endDate'},
				{
					xtype : 'actioncolumn',
					align : 'right',
					items : [
						{
							icon:'/img/icons/2017/edit16.png',
							tooltip : 'Редактировать',
							handler : function (grid, rowIndex, colIndex, item, e, record) {
								win.openEditWindow( 'edit', 'Vaccination', record.get('Vaccination_id') )
							},
							getClass: function(v, meta, rec) {
								if(this.action == "view") {
									return 'x-hide-display';
								}
							},
							scope : win
						}, {
							icon:'/img/icons/emk/panelicons/panelicon04_16.png',
							cls: 'vaccinationViewIcon',
							tooltip : 'Посмотреть',
							handler : function (grid, rowIndex, colIndex, item, e, record) {
								win.openEditWindow( 'view', 'Vaccination', record.get('Vaccination_id') )
							},
							getClass: function(v, meta, rec) {
								if(this.action !== "view") {
									return 'x-hide-display';
								}
							},
							scope : win
						}, {
							icon:'/img/icons/2017/delete16.png',
							tooltip : 'Удалить',
							handler : function (grid, rowIndex, colIndex, item, e, record) {
								win.deleteItem('Vaccination', record.get('Vaccination_id'));
							},
							getClass: function(v, meta, rec) {
								if(this.action == "view") {
									return 'x-hide-display';
								}
							},
							scope : win
						}
					],
				}
			],
			fbar: {
				xtype: 'toolbar',
				style:{
					backgroundColor:'#ffffff',
					backgroundImage:'none'
				},
				height: 40,
				itemId: 'Vaccfbar',
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					text: 'Добавить',
					margin: '0 0 0 0',
					itemId: 'action_add',
					style:{
						backgroundColor:'#ffffff',
						border:0,
						padding:'0 0',
						textTransform:'capitalize',
						color: '#2196F3 !important'
					},
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: '',
					handler: function () {
						win.openEditWindow('add','Vaccination');
					}
				},'->']
			}
		});

		win.RiskGroupPanel = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'north',
			border:'solid 1px #F5F5F5',
			autoHeight: true,
			userCls:'vaccinationGroupPanel noBorderInItemsPanel',
			title: 'Группы риска',
			store: {
				fields: [
					{name: 'VaccinationRiskGroup_id', type: 'int'},
					{name: 'VaccinationType_id', type: 'int'},
					{name: 'VaccinationRiskGroup_Name'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=VaccinationType&m=loadVaccinationRiskGroupList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				}
			},
			hideHeaders: true,
			columns:[
				{
					text: 'Группы риска',
					tdCls: 'padLeft',
					flex: 1,
					dataIndex: 'VaccinationRiskGroup_Name',
					align : 'left',
					renderer: function(val,meta,rec) {
						if(this.action != 'view') {
							var id = Ext6.id();
							Ext6.defer(function () {
								Ext6.widget('button', {
										renderTo: Ext6.query("#" + id)[0],
										icon: '/img/icons/2017/delete16.png',
										style: {
											backgroundColor: 'transparent',
											backgroundImage: 'none',
											border: '0'
										},
										text: 'Удалить',
										scale: 'small',
										cls: 'VaccinationButtonDelete',
										margin: '0 0 0 10px',
										padding: 0,
										bodyStyle: 'text-transform:capitalize;',
										getClass: function (v, meta, rec) {
											if (this.action == "view") {
												return 'x-hide-display';
											}
										},
										handler: function () {
											var params = {
												VaccinationType_id: rec.get('VaccinationType_id'),
												VaccinationRiskGroup_id: rec.get('VaccinationRiskGroup_id'),
												VaccinationRiskGroupLink_checked: '1'
											}
											win.unSetVaccination(params, 'RiskGroup');
										}
									});
							}, 50);
							}
						return Ext6.String.format('<div id="{0}"><span style="">'+val+'</span></div>', id);
					}.createDelegate(this)
				}
			],
			fbar: {
				xtype: 'toolbar',
				style:{
					backgroundColor:'#ffffff',
					backgroundImage:'none',
					border:false
				},
				height: 40,
				itemId: 'Riskfbar',
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					text: 'Добавить',
					margin: '0 0 0 0',
					itemId: 'action_add_VaccinationRiskGroup',
					style:{
						backgroundColor:'#ffffff',
						border:0,
						padding:'0 0',
						textTransform:'capitalize',
						color: '#2196F3 !important'
					},
					xtype: 'button',
					handler: function (e, c, d) {
						var win = this;
						var base_form = win.VaccinationGeneralPanel.getForm();
						var VaccinationType_id = base_form.findField('VaccinationType_id').getValue();

						Ext6.Ajax.request({
							url: '/?c=VaccinationType&m=loadVaccinationRiskGroupMenuList',
							params: { VaccinationType_id: VaccinationType_id },
							failure: function(response, options) {
								Ext.Msg.alert(langs('Ошибка'), langs('При получении списка осомтров!'));
							},
							success: function(response, action)
							{
								if (response.responseText) {
									var result = Ext6.util.JSON.decode(response.responseText);
									var menu = new Ext6.menu.Menu();
									if (result) {
										for(var i = 0;i < result.length;i++) {
											menu.add({
												text: result[i]['VaccinationRiskGroup_Name'],
												VaccinationRiskGroup_id:result[i]['VaccinationRiskGroup_id'],
												VaccinationRiskGroupLink_id:result[i]['VaccinationRiskGroupLink_id'],
												checked: (result[i]['VaccinationRiskGroupLink_id']) ? true : false,
												handler: function() {
													var params = {
															VaccinationType_id: VaccinationType_id,
															VaccinationRiskGroup_id:this.VaccinationRiskGroup_id,
															VaccinationRiskGroupLink_id:this.VaccinationRiskGroupLink_id,
															VaccinationRiskGroupLink_checked: (this.checked) ? '2' : '1'
														}
													win.unSetVaccination(params,'RiskGroup');
												}
											});
										}
									}
									menu.showBy(win.down('#action_add_VaccinationRiskGroup'));
								}
								else {
									Ext6.Msg.alert(langs('Ошибка'), langs('При получении списка профилей произошла ошибка! Отсутствует ответ сервера.'));
								}
							}
						});
					}.createDelegate(this)
				},'->']
			}
		});

		win.PostVaccinationExamPanel = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'north',
			autoHeight: true,
			userCls:'vaccinationGroupPanel noBorderInItemsPanel',
			title: 'Осмотры после вакцинации',
			store: {
				fields: [
					{name: 'VaccinationExamType_id', type: 'int'},
					{name: 'VaccinationType_id', type: 'int'},
					{name: 'VaccinationExamType_Name'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=VaccinationType&m=loadVaccinationExamList',
					reader: { type: 'json', rootProperty: 'data' }
				}
			},
			hideHeaders: true,
			columns:[
				{
					text: 'Осмотры после вакцинации',
					tdCls: 'padLeft',
					flex: '1',
					dataIndex: 'VaccinationExamType_Name',
					renderer: function(val,meta,rec) {
						if(this.action != 'view') {
							var id = Ext6.id();
							Ext6.defer(function () {
								Ext6.widget('button', {
									renderTo: Ext6.query("#" + id)[0],
									icon: '/img/icons/2017/delete16.png',
									style: {backgroundColor: 'transparent', backgroundImage: 'none', border: '0'},
									text: 'Удалить',
									scale: 'small',
									cls: 'VaccinationButtonDelete',
									margin: '0 0 0 10px',
									padding: 0,
									bodyStyle: 'text-transform:capitalize;',
									handler: function () {
										var params = {
											VaccinationType_id: rec.get('VaccinationType_id'),
											VaccinationExamType_id: rec.get('VaccinationExamType_id'),
											VaccinationExamTypeLink_checked: '1'
										}
										win.unSetVaccination(params, 'Exam');
									}
								});
							}, 50);
						}
						return Ext6.String.format('<div id="{0}"><span style="">'+val+'</span></div>', id);
					}.createDelegate(this)
				},
			],
			fbar: {
				xtype: 'toolbar',
				style: { backgroundColor:'#ffffff', backgroundImage:'none' },
				height: 40,
				itemId: 'Examfbar',
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					text: 'Добавить',
					margin: '0 0 0 0',
					itemId: 'action_add_VaccinationExam',
					style:{
						backgroundColor:'#ffffff',
						border:0,
						padding:'0 0',
						textTransform:'capitalize',
						bodyStyle: { color: '#2196F3 !important' }
					},
					xtype: 'button',
					handler: function (e, c, d) {
						var win = this;
						var base_form = win.VaccinationGeneralPanel.getForm();
						var VaccinationType_id = base_form.findField('VaccinationType_id').getValue();

						Ext6.Ajax.request({
							url: '/?c=VaccinationType&m=loadVaccinationExamMenuList',
							params: { VaccinationType_id: VaccinationType_id },
							failure: function(response, options) {
								Ext.Msg.alert(langs('Ошибка'), langs('При получении списка осмотров!'));
							},
							success: function(response, action)
							{
								if (response.responseText) {
									var result = Ext6.util.JSON.decode(response.responseText);
									var menu = new Ext6.menu.Menu({

									});
									if (result) {
										for(var i = 0;i < result.length;i++) {
											menu.add({
												text: result[i]['VaccinationExamType_Name'],
												VaccinationExamType_id:result[i]['VaccinationExamType_id'],
												VaccinationExamTypeLink_id:result[i]['VaccinationExamTypeLink_id'],
												checked: (result[i]['VaccinationExamTypeLink_id']) ? true : false,
												handler: function() {
													var params = {
														VaccinationType_id: VaccinationType_id,
														VaccinationExamType_id:this.VaccinationExamType_id,
														VaccinationExamTypeLink_id:this.VaccinationExamTypeLink_id,
														VaccinationExamTypeLink_checked: (this.checked) ? '2' : '1'
													}
													win.unSetVaccination(params,'Exam');
												}
											});
										}
									}
									menu.showBy(win.down('#action_add_VaccinationExam'));
								}
								else {
									Ext6.Msg.alert(langs('Ошибка'), langs('При получении списка профилей произошла ошибка! Отсутствует ответ сервера.'));
								}
							}
						});
					}.createDelegate(this)
				},'->']
			}

		});

		win.PrepStore = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'Prep_id', mapping: 'Prep_id', type: 'int', hidden: 'true'},
				{name: 'Prep_Name', mapping: 'Prep_Name'}
			],
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=VaccinationType&m=loadVaccinationTypePrepComboList',
				reader: {type: 'json'},
				extraParams: {VaccinationType_isReaction: (Ext6.isEmpty(win.VaccinationType_isReaction)) ? '1' : '2'}
			},
			mode: 'remote',
			autoLoad: false
		});

		win.VaccinationPrepPanel = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'north',
			autoHeight: true,
			userCls:'vaccinationGroupPanel',
			title: 'Препараты для вакцинации',
			plugins:[{
				ptype:'cellediting',
				clicksToEdit: 1,
				disabled: this.action == 'view'
			}],
			selModel: { mode: 'SINGLE', },
			listeners: {
				itemdblclick: function(v,c) {
					win.onDblClick('Prep',c.getData().VaccinationTypePrep_id);
				}
			},
			store: {
				fields: [
					{name: 'VaccinationTypePrep_id', type: 'int'},
					{name: 'Prep_id', type: 'int'},
					{name: 'Prep_Name'},
					{name: 'VaccinationTypePrep_FirmName'},
					{name: 'VaccinationTypePrep_begDate'},
					{name: 'VaccinationTypePrep_endDate'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=VaccinationType&m=loadVaccinationPrepList',
					reader: { type: 'json', rootProperty: 'data' }
				}
			},
			columns: [
				{text: 'Препарат', tdCls: 'padLeft firstCellPrepTable', width: 150, dataIndex: 'Prep_Name', data:'VaccinationTypePrep_id', flex: 5,
					editor: {
						xtype: 'combobox',
						padding:0,
						store: win.PrepStore,
						displayField: 'Prep_Name',
						valueField: 'Prep_id',
						queryMode: 'local',
						allowBlank: false,
						onChange: function (newValue,oldValue) {
							if (Number.isInteger(newValue)) {
								var win = this;
								win.savePrep({Prep_id: newValue});
							}
						}.createDelegate(this),
						onBlur:function (newValue,oldValue) {
							win.VaccinationPrepPanel.getStore().reload();
						}.createDelegate(this)
					},

				},
				{text: 'Производитель', width: 150,  dataIndex: 'VaccinationTypePrep_FirmName', flex: 5},
				{text: 'Начало', width: 120, minWidth: 120, dataIndex: 'VaccinationTypePrep_begDate', type:'date', format: 'd.m.Y' },
				{text: 'Окончание', width: 120, minWidth: 120, dataIndex: 'VaccinationTypePrep_endDate', type:'date', format: 'd.m.Y' },
				{
					xtype: 'actioncolumn',
					width: 90,
					align: 'right',
					items: [{
						icon:'/img/icons/2017/edit16.png',
						tooltip: 'Редактировать',
						handler: function (grid, rowIndex, colIndex, item, e, record) {
							win.openEditWindow( 'edit', 'Prep', record.get('VaccinationTypePrep_id') )
						},
						getClass: function(v, meta, rec) {
							if(this.action == "view") {
								return 'x-hide-display';
							}
						},
						scope: win
					},{
						icon:'/img/icons/2017/delete16.png',
						tooltip: 'Удалить',
						handler: function (grid, rowIndex, colIndex, item, e, record) {
							win.deleteItem('Prep', record.get('VaccinationTypePrep_id'));
						},
						getClass: function(v, meta, rec) {
							if(this.action == "view") {
								return 'x-hide-display';
							}
						},
						scope: win
					}],
				}
			],
			fbar: {
				xtype: 'toolbar',
				style:{ backgroundColor:'#ffffff', backgroundImage:'none' },
				height: 40,
				cls: 'grid-toolbar',
				itemId: 'Prepfbar',
				hidden: this.action == 'view',
				overflowHandler: 'menu',
				items: [{
					text: 'Добавить',
					margin: '0 0 0 0',
					itemId: 'action_add',
					color: '#2196F3 !important',
					style: {
						backgroundColor:'#ffffff',
						border:0,
						padding:'0 0',
						textTransform:'capitalize',
						color: '#2196F3 !important'
					},
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: '',
					handler: function () { win.openEditWindow('add','Prep'); }
				},'->']
			}
		});

		win.VaccinationGeneralPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 10px 10px 5px 0px;',
			cls: 'person-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					items: [{
						xtype: 'segmentedbutton',
						userCls: 'segmentedButtonGroup',
						itemId:'VaccinationType_isVaccination',
						width: 184,
						height:34,
						padding:'2px 2px 5px 2px',
						items: [
							{ text: 'Прививка', itemId:'VaccinationTypeisReaction_Vaccination', pressed: true, value:1, padding:0, width:90 },
							{ text: 'Реакция', itemId:'VaccinationTypeisReaction_Reaction', value:2, padding:0, width:90 }
						]
					},]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					items: [Ext6.create('Ext6.date.RangeField', {
						labelWidth: 55,
						width: 265,
						height:32,
						bodyStyle: 'background-color:transparent;',
						xtype: 'daterangefield',
						fieldLabel: 'Период',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'VaccinationType_RangeDate'
					})]
				},
				{ xtype: 'textfield', name: 'VaccinationType_id', hidden: true },
				{ xtype: 'textfield', name: 'VaccinationType_isReaction', hidden: true }
				]
			}]
		});

		win.itemsPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			autoScroll: true,
			items: [
				win.VaccinationPanel,
				win.RiskGroupPanel,
				win.PostVaccinationExamPanel,
				win.VaccinationPrepPanel
			]
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			items: [
				win.VaccinationGeneralPanel,
				win.itemsPanel
			]
		});

		Ext6.apply(win, {
			defaults: { width: '100%', padding:'0' },
			items: [ win.mainPanel ]
		});
		this.callParent(arguments);
	}
});
