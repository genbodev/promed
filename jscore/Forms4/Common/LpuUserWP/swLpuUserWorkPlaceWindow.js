/**
 * swLpuAdminWorkPlaceWindow - АРМ администратора МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.LpuUserWP.swLpuUserWorkPlaceWindow', {
	noCloseOnTaskBar: true, // без кнопки закрытия на таксбаре
	extend: 'base.BaseForm',
	alias: 'widget.swLpuUserWorkPlaceWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'polkawp',
	findWindow: false,
	closable: false,
	frame: false,
	cls: 'arm-window-new PolkaWP',
	title: 'АРМ пользователя МО',
	header: true,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	show: function() {
		this.callParent(arguments);
		var win = this;
		sw.Promed.MedStaffFactByUser.setMenuTitle(win, arguments[0]);
	},
	initComponent: function() {
		var win = this;

		win.leftMenu = new Ext6.menu.Menu({
			xtype: 'menu',
			floating: false,
			dock: 'left',
			cls: 'leftPanelWP',
			border: false,
			padding: 0,
			defaults: {
				margin: 0
			},
			mouseLeaveDelay: 100,
			collapsedWidth: 30,
			collapseMenu: function() {
				if (!win.leftMenu.activeChild || win.leftMenu.activeChild.hidden) {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.collapsedWidth); // сужаем
					win.leftMenu.body.setWidth(win.leftMenu.collapsedWidth - 1); // сужаем
					win.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function() {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.items.items[0].getWidth());
					win.leftMenu.body.setWidth(win.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender : function(scope) {
					win.leftMenu.setWidth(win.leftMenu.collapsedWidth); // сразу сужаем
					win.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function() {
						// сужаем, если нет подменю
						clearInterval(win.leftMenu.collapseInterval); // сбрасывем
						win.leftMenu.collapseInterval = setInterval(win.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [ 
				{
				iconCls: 'spr16-2017',
				menu: [{
					text: langs('Тарифы и объемы'),
					handler: function () {
						getWnd('swTariffVolumesViewWindow').show({action: 'view'});
					}
				}, {
					text: langs('Справочник услуг'),
					handler: function() {
						getWnd('swUslugaTreeWindow').show({action: 'view'});
					},
				}, {
					text: langs('МКБ-10'),
					handler: function(){
						getWnd('swMkb10SearchWindow').show();
					}
				}, {
					text: langs('Справочники'),
					iconCls: 'spr16-2017',
					handler: function() {
						getWnd('swDirectoryViewWindow').show({action: 'view'});
					}
				}
				],
				text: 'Справочники'
			}, 
				{
					handler: function () {
						getWnd('swRegistryViewWindow').show();
					},
					iconCls: 'registers16-2017',
					nn: 'action_Registry',
					text: langs('Реестры счетов'),
					tooltip: langs('Реестры счетов')
				}]
		});

		win.cardPanel = new Ext6.Panel({
			dockedItems: [ win.leftMenu ],
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			items: [ win.cardPanel ]
		});

		Ext6.apply(win, {
			referenceHolder: true, // чтобы ЛУКап заработал по референсу
			reference: 'swLpuUserWorkPlaceWindowLayout_' + win.id,
			items: [win.mainPanel, win.FormPanel],
		});

		this.callParent(arguments);
	}
});
