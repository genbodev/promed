/**
 * swSURPersonalWorkListWindow - окно просмотра списка сотрудников СУР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.02.2017
 */
/*NO PARSE JSON*/

sw.Promed.swSURPersonalWorkListWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSURPersonalWorkListWindow',
	layout: 'border',
	title: 'Список сотрудников "СУР"',
	maximizable: true,
	maximized: true,
	modal: true,
	width: 960,

	selectPersonalWork: function(options) {
		options = options || {};
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('ID')) || Ext.isEmpty(this.MedStaffFact_id)) {
			return;
		}

		var params = {
			ID: record.get('ID'),
			MedStaffFact_id: this.MedStaffFact_id
		};

		if (options.ignoreExistsLinkCheck) {
			params.ignoreExistsLinkCheck = 1;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Связывание идентификаторов...'});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=ServiceSUR&m=savePersonalHistoryWP',
			params: params,
			success: function(response) {
				loadMask.hide();

				var answer = Ext.util.JSON.decode(response.responseText);

				if (answer.success) {
					this.callback();
					this.hide();
				} else if (answer.Error_Msg == 'YesNo') {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								if (answer.Error_Code == 121) {
									options.ignoreExistsLinkCheck = 1;
								}
								this.selectPersonalWork(options);
							}
						}.createDelegate(this),
						msg: answer.Alert_Msg,
						title: langs('Вопрос')
					});
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	openSURPersonalWorkViewWindow: function() {
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('ID'))) return;

		getWnd('swSURPersonalWorkViewWindow').show({ID: record.get('ID')});
	},

	doSearch: function(reset) {
		var grid = this.GridPanel.getGrid();
		var base_form = this.FilterPanel.getForm();

		if (reset) {
			base_form.reset();

			for(var fieldName in this.defaultValues) {
				base_form.findField(fieldName).setValue(this.defaultValues[fieldName]);
			}
		}

		var params = {
			Lpu_id: this.Lpu_id
		};
		base_form.items.each(function(field){
			if (field.getValue() instanceof Date) {
				params[field.getName()] = field.getValue().format('d.m.Y');
			} else {
				params[field.getName()] = field.getValue();
			}
		});

		grid.getStore().load({params: params});
	},

	show: function() {
		sw.Promed.swSURPersonalWorkListWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();

		this.action = 'view';
		this.Lpu_id = null;
		this.MedStaffFact_id = null;
		this.defaultValues = {};
		this.callback = Ext.emptyFn;

		if (arguments[0].action && arguments[0].action.inlist(['view', 'select'])) {
			this.action = arguments[0].action;
		}
		if (typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].Lpu_id) {
			this.Lpu_id = arguments[0].Lpu_id;
		}
		if (arguments[0].MedStaffFact_id) {
			this.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}
		if (arguments[0].filterParams) {
			this.defaultValues = arguments[0].filterParams;
		}

		if (this.action == 'select' && Ext.isEmpty(this.MedStaffFact_id)) {
			sw.swMsg.alert(lang['oshibka'], 'Не был передан сотрудник');
			this.hide();
			return;
		}

		Ext.getCmp('SPLW_SelectPersonWorkButton').setVisible(this.action == 'select');

		this.doSearch(true);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			autoHeight: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			enableKeyEvents: true,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 300,
					labelWidth: 80,
					style: 'margin-left: 0;',
					defaults: {
						anchor: '100%'
					},
					items: [{
						xtype: 'textfield',
						name: 'LastName',
						fieldLabel: 'Фамилия',
					}, {
						xtype: 'textfield',
						name: 'FirstName',
						fieldLabel: 'Имя',
					}, {
						xtype: 'textfield',
						name: 'SecondName',
						fieldLabel: 'Отчество'
					}, {
						xtype: 'textfield',
						name: 'IIN',
						fieldLabel: 'ИИН'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 300,
					style: 'margin-left: 35px;',
					defaults: {
						anchor: '100%'
					},
					items: [{
						xtype: 'textfield',
						name: 'PostFuncRU',
						fieldLabel: 'Должность',
					}, {
						xtype: 'textfield',
						name: 'OrderNum',
						fieldLabel: '№ приказа',
					}, {
						xtype: 'swdatefield',
						name: 'BeginDate',
						fieldLabel: 'Дата начала'
					}, {
						xtype: 'swdatefield',
						name: 'EndDate',
						fieldLabel: 'Дата окончания'
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					style: 'margin-left: 483px;',
					items: [{
						xtype: 'button',
						text: 'Найти',
						iconCls: 'search16',
						handler: function() {
							this.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					border: false,
					style: 'margin-left: 10px;',
					items: [{
						xtype: 'button',
						text: 'Сброс',
						iconCls: 'reset16',
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=ServiceSUR&m=loadPersonalWorkGrid',
			border: true,
			autoLoadData: false,
			paging: false,
			toolbar: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{name: 'ID', type: 'string', header: 'ID', key: true},
				{name: 'PersonalID', type: 'string', hidden: true},
				{name: 'PersonFIO', header: 'ФИО', type: 'string', width: 200},
				{name: 'IIN', header: 'ИИН', type: 'string', width: 90},
				{name: 'PersonalTypeRU', header: 'Тип персонала', type: 'string', width: 160},
				{name: 'PostCategoryRu', header: 'Категория должности', type: 'string', width: 90},
				{name: 'PostFuncRU', header: 'Наименование должности', type: 'string', id: 'autoexpand'},
				{name: 'PostCount', header: 'Кол-во занимаемых ставок', type: 'float', width: 80},
				{name: 'PostTypeRU', header: 'Тип должности', type: 'string', width: 100},
				{name: 'SpecialityRU', header: 'Специальность', type: 'string', width: 120},
				{name: 'StatusPostRu', header: 'Состояние должности', type: 'string', width: 100},
				{name: 'TypSrcFinRu', header: 'Источник финансирования', type: 'string', width: 100},
				{name: 'BeginDate', header: 'Дата начала', type: 'date', width: 80},
				{name: 'EndDate', header: 'Дата окончания', type: 'date', width: 80},
				{name: 'OrderNum', header: 'Номер приказа', type: 'string', width: 100}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', handler: function(){this.openSURPersonalWorkViewWindow()}.createDelegate(this)},
				{name:'action_delete', hidden: true}
			],
			onDblClick: function(grid, index, record) {
				this.openSURPersonalWorkViewWindow();
			}.createDelegate(this)
		});

		Ext.apply(this,{
			buttons: [
				{
					id: 'SPLW_SelectPersonWorkButton',
					handler: function() {
						this.selectPersonalWork();
					}.createDelegate(this),
					iconCls: 'ok16',
					text: lang['vyibrat']
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.FilterPanel,
				this.GridPanel
			]
		});

		sw.Promed.swSURPersonalWorkListWindow.superclass.initComponent.apply(this, arguments);
	}
});