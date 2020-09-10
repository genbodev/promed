/**
 * swOrderServicePointsListWindow - порядок пунктов обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2015 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swOrderServicePointsListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: true,
	height: 600,
	width: 900,
	id: 'swOrderServicePointsListWindow',
	title: 'Порядок пунктов обслуживания',
	layout: 'border',
	resizable: true,
	deleteOrderServicePoints: function() {
		var win = this;
		var AgeGroupDisp_id = this.ServicePointsGrid.getGrid().getSelectionModel().getSelected().get('AgeGroupDisp_id');
		var AgeGroupDisp_Name = this.ServicePointsGrid.getGrid().getSelectionModel().getSelected().get('AgeGroupDisp_Name');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							win.doSearch();
						},
						params: {
							ElectronicQueueInfo_id: win.ElectronicQueueInfo_id,
							AgeGroupDisp_id: AgeGroupDisp_id
						},
						url: '/?c=ElectronicService&m=deleteOrderServicePoints'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить порядок пунктов обслуживания<br>для возрастной группы "' + AgeGroupDisp_Name + '"?'),
			title: langs('Вопрос')
		});

	},
	openOrderServicePointsEditWindow: function(action) {
		var win = this,
			grid = this.ServicePointsGrid.getGrid();

		var params = {
			action: action,
			ElectronicQueueInfo_id: this.ElectronicQueueInfo_id
		}

		if(action != 'add') {
			params.AgeGroupDisp_id = this.ServicePointsGrid.getGrid().getSelectionModel().getSelected().get('AgeGroupDisp_id');
		}
		params.callback = function() {
			win.doSearch();
		};
		if(!getWnd('swOrderServicePointsEditWindow').isVisible()){
			getWnd('swOrderServicePointsEditWindow').show(params);
		} else {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
		}
	},

	doSearch: function() {

		var win = this,
			filterForm = win.FilterPanel.getForm();

		var params = filterForm.getValues();

		params.ElectronicQueueInfo_id = this.ElectronicQueueInfo_id;
		params.start = 0;
		params.limit = 100;

		// Ставим заголовок фильтра
		this.setTitleFieldSet();
		win.ServicePointsGrid.loadData({globalFilters: params});
	},

	doReset: function() {

		var form = this;
		this.FilterPanel.getForm().reset();

	},

	setTitleFieldSet: function() {
		var fieldSet = this.FilterPanel.find('xtype', 'fieldset')[0],
			enableFilter = false,
			title = langs('Поиск: фильтр ');

		fieldSet.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfield', 'swlpusearchcombo', 'swlpubuildingcombo', 'daterangefield', 'swcommonsprcombo', 'numberfield']) ) {
				if( f.getValue() != '' && f.getValue() != null ) {
					enableFilter = true;
				}
			}
		});

		fieldSet.setTitle( title + ( enableFilter == true ? '' : 'не ' ) + 'установлен' );
	},

	initComponent: function()
	{
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			frame: true,
			items: [
				{
					layout: 'form',
					xtype: 'fieldset',
					autoHeight: true,
					collapsible: true,
					listeners: {
						collapse: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this),
						expand: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					labelAlign: 'right',
					title: langs('Поиск: фильтр не установлен'),
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									labelWidth: 110,
									width: 500,
									items: [
										{
											editable: true,
											fieldLabel: langs('Возрастная группа'),
											hiddenName: 'AgeGroupDisp_id',
											comboSubject: 'AgeGroupDisp',
											xtype: 'swcommonsprcombo'
										}
									]
								}, {
									layout: 'form',
									width: 450,
									labelWidth: 150,
									defaults: {
										anchor: '100%'
									},
									items: [
										{
											editable: true,
											fieldLabel: langs('Осмотр / исследование'),
											hiddenName: 'SurveyType_id',
											comboSubject: 'SurveyType',
											xtype: 'swcommonsprcombo'
										}
									]
								}, {
									layout: 'form',
									width: 200,
									labelWidth: 100,
									defaults: {
										anchor: '100%'
									},
									items: [
										{
											fieldLabel: langs('Порядок'),
											name: 'ElectronicServiceOrder_Num',
											xtype: 'numberfield'
										}
									]
								}
							]
						}, {
							layout: 'column',
							style: 'padding: 3px;',
							items: [
								{
									layout: 'form',
									items: [
										{
											handler: function() {
												this.doSearch();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 5px;',
									items: [
										{
											handler: function() {
												this.doReset();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'resetsearch16',
											text: langs('Сброс')
										}
									]
								}
							]
						}
					]
				}
			],
			keys: [{
				fn: function(inp, e) {
					this.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}]
		});
		this.ServicePointsGrid = new sw.Promed.ViewFrame({
			id: win.id + 'OrderServiceGrid',
			title:'',
			object: 'ElectronicService',
			dataUrl: '/?c=ElectronicService&m=loadElectronicServiceOrder',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
		
			stringfields: [
				{name: 'ElectronicServiceOrder_id', hidden: true, key: true, type: 'int'},
				{name: 'AgeGroupDisp_id', hidden: true, type: 'int'},
				{name: 'AgeGroupDisp_Name', header: 'Возрастная группа', width: 400},
				{name: 'SurveyType_name',  header: 'Осмотр / исследование', width: 300, id: 'autoexpand'},
				{name: 'ElectronicServiceOrder_Num', header: 'Порядок', width: 300, type: 'int'}
			],
			actions: [
				{name:'action_add', handler: function() { win.openOrderServicePointsEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openOrderServicePointsEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openOrderServicePointsEditWindow('view'); }},
				{name:'action_delete', handler: function() { win.deleteOrderServicePoints(); }},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		Ext.apply(this, {
		items: [
			win.FilterPanel,
			win.ServicePointsGrid
		],
		buttons: [{
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swOrderServicePointsListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOrderServicePointsListWindow.superclass.show.apply(this, arguments);
		var win = this,
			grid = this.ServicePointsGrid;
		
		grid.getGrid().getStore().baseParams = {};
		grid.getGrid().getStore().removeAll();

		if( arguments[0]['ElectronicQueueInfo_id']) {
			this.ElectronicQueueInfo_id = arguments[0]['ElectronicQueueInfo_id'];
		} else {
			this.ElectronicQueueInfo_id = null;
		}

		var params = {
			ElectronicQueueInfo_id: this.ElectronicQueueInfo_id,
			start: 0,
			limit: 100
		};
		this.ServicePointsGrid.loadData({
			globalFilters: params
		});


		this.doReset();
	}
});