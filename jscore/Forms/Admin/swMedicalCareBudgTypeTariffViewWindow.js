/**
* swMedicalCareBudgTypeTariffViewWindow - окно просмотра и редактирования тарифов по бюджету.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swMedicalCareBudgTypeTariffViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	height: 500,
	width: 800,
	title: langs('Тарифы (бюджет)'),
	layout: 'border',
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	show: function()
	{
		sw.Promed.swMedicalCareBudgTypeTariffViewWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.maximize();

		this.MedicalCareBudgTypeTariffGrid.removeAll();
		this.loadMedicalCareBudgTypeTariffGrid();
	},
	loadMedicalCareBudgTypeTariffGrid: function() {
		var form = this;
		var filtersForm = form.MedicalCareBudgTypeTariffFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;

		form.MedicalCareBudgTypeTariffGrid.loadData({
			globalFilters: params
		});
	},
	addCloseFilterMenu: function(gridCmp){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: langs('Все'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Все</b>'));
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Открытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Открытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Закрытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Закрытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			grid.addActions({
				isClose: null,
				name: 'action_isclosefilter_'+grid.id,
				text: langs('Показывать: <b>Все</b>'),
				menu: menuIsCloseFilter
			});
			grid.getGrid().getStore().baseParams.isClose = null;
		}

		return true;
	},
	initComponent: function()
	{
		var form = this;

		this.MedicalCareBudgTypeTariffFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 60,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadMedicalCareBudgTypeTariffGrid();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'background: transparent;',
				defaults: {
					labelAlign: 'right',
					bodyStyle: 'background: transparent; padding-left: 10px;'
				},
				items: [{
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 120,
					items: [{
						anchor: '100%',
						comboSubject: 'MedicalCareBudgType',
						ctxSerach: true,
						editable: true,
						enableKeyEvents: true,
						fieldLabel: 'Тип мед. помощи',
						hiddenName: 'MedicalCareBudgType_id',
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '100%',
						fieldLabel: 'Вид оплаты',
						hiddenName: 'PayType_id',
						loadParams: {
							params: {where: getRegionNick() == 'kareliya' ? " where PayType_SysNick in ('bud', 'fbud', 'subrf')" : " where PayType_SysNick in ('bud', 'fbud')"}
						},
						xtype: 'swpaytypecombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 400,
					labelWidth: 130,
					items: [{
						anchor: '100%',
						fieldLabel: 'МО',
						editable: true,
						hiddenName: 'Lpu_id',
						xtype: 'swlpucombo',
						ctxSerach: true

					}, {
						anchor: '100%',
						comboSubject: 'QuoteUnitType',
						fieldLabel: 'Единица измерения',
						hiddenName: 'QuoteUnitType_id',
						loadParams: {
							params: {where: " where QuoteUnitType_Code in (1, 2, 3)"}
						},
						xtype: 'swcommonsprcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						handler: function() {
							form.loadMedicalCareBudgTypeTariffGrid();
						}
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						handler: function() {
							var filtersForm = form.MedicalCareBudgTypeTariffFilterPanel.getForm();
							filtersForm.reset();
							form.MedicalCareBudgTypeTariffGrid.removeAll(true);
							form.loadMedicalCareBudgTypeTariffGrid();
						}
					}]
				}]
			}]
		});

		this.MedicalCareBudgTypeTariffGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'MedicalCareBudgTypeTariff',
			editformclassname: 'swMedicalCareBudgTypeTariffEditWindow',
			dataUrl: '/?c=LpuPassport&m=loadMedicalCareBudgTypeTariffGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {

			},
			stringfields: [
				{name: 'MedicalCareBudgTypeTariff_id', type: 'int', header: 'ID', key: true},
				{name: 'MedicalCareBudgType_Name', type: 'string', header: langs('Тип мед. помощи'), width: 300},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 150},
				{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 120},
				{name: 'QuoteUnitType_Name', type: 'string', header: langs('Единица измерения'), width: 120},
				{name: 'MedicalCareBudgTypeTariff_Value', type: 'float', header: langs('Значение'), width: 180},
				{name: 'MedicalCareBudgTypeTariff_begDT', type: 'date', header: langs('Начало действия'), width: 120},
				{name: 'MedicalCareBudgTypeTariff_endDT', type: 'date', header: langs('Окончание действия'), width: 120}
			],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=LpuPassport&m=deleteMedicalCareBudgTypeTariff'}
			]
		});

		this.MedicalCareBudgTypeTariffGrid.ViewToolbar.on('render', function(vt) {
			return this.addCloseFilterMenu(this.MedicalCareBudgTypeTariffGrid);
		}.createDelegate(this));

		Ext.apply(this,
		{
			layout:'border',
			defaults: {split: true},
			buttons:
			[{
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
				this.MedicalCareBudgTypeTariffFilterPanel,
				this.MedicalCareBudgTypeTariffGrid
			]
		});
		sw.Promed.swMedicalCareBudgTypeTariffViewWindow.superclass.initComponent.apply(this, arguments);
	}
});