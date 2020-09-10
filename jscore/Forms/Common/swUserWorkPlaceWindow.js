/**
 * swLpuAdminWorkPlaceWindow - окно рабочего места администратора ЛПУ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Melentiev Anatoliy
 * @prefix       lawpw
 * @version      март 2011
 */
/*NO PARSE JSON*/


sw.Promed.swUserWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swUserWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swUserWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: langs('АРМ пользователя МО'),
	iconCls: 'user16',
	id: 'swLpuUserWorkPlaceWindow',
	show: function()
	{
		sw.Promed.swUserWorkPlaceWindow.superclass.show.apply(this, arguments);

		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
		}
		this.ARMType = arguments[0].ARMType;
	},
	initComponent: function()
	{
		Ext.apply(sw.Promed.Actions, {
			TariffVolumeViewAction: {
				text: langs('Тарифы и объемы'),
				tooltip: langs('Тарифы и объемы'),
				handler: function () {
					getWnd('swTariffVolumesViewWindow').show({action: 'view'});
				}
			},
			UslugaTreeAction: {
				text: langs('Справочник услуг'),
				tooltip: langs('Справочник услуг'),
				iconCls: 'services-complex16',
				handler: function() {
					getWnd('swUslugaTreeWindow').show({action: 'view'});
				}
			},
			swMKB10Action: {
				text: langs('МКБ-10'),
				tooltip: langs('Справочник МКБ-10'),
				iconCls: 'spr-mkb16',
				handler: function(){
					getWnd('swMkb10SearchWindow').show();
				}
			},
			swSprPromedAction: {
				text: langs('Справочники'),
				tooltip: langs('Справочники'),
				iconCls: 'spr-promed16',
				handler: function() {
					getWnd('swDirectoryViewWindow').show({action: 'view'});
				}
			}
		});

		var form = this;
		// Формирование списка всех акшенов
		var configActions =
		{
			action_Spr:	{
				nn: 'action_Spr',
				tooltip: langs('Справочники'),
				text: langs('Справочники'),
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.TariffVolumeViewAction,
						sw.Promed.Actions.UslugaTreeAction,
						sw.Promed.Actions.swMKB10Action,
						sw.Promed.Actions.swSprPromedAction
					]
				})
			},
			action_Registry: {
				handler: function () {
					getWnd('swRegistryViewWindow').show();
				},
				iconCls: 'service-reestrs16',
				nn: 'action_Registry',
				text: langs('Реестры счетов'),
				tooltip: langs('Реестры счетов')
			}
		};

		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}

		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
			i++;
		}
		
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			id:form.id+'_hhd',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});

		this.leftPanel =
		{
			animCollapse: false,
			width: 52,
			minSize: 52,
			maxSize: 120,
			id: 'lawpwLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id+'_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [
				new Ext.Button(
				{
					cls:'upbuttonArr',
					disabled: false,
					iconCls: 'uparrow',
					handler: function()
					{
						var el = form.findById(form.id+'_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					layout:'border',
					id:form.id+'_slid',
					height:100,
					items:[this.leftMenu]
				},
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false,
				handler: function()
				{
					var el = form.findById(form.id+'_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;

				}
				})]
		};

		Ext.apply(this, {
			layout: 'border',
			items: [
				this.leftPanel,
				{
					layout: 'fit',
					region: 'center',
					border: false,
					bodyStyle: 'background:transparent;',
					items:	[]
				}
			],
			buttons: [{text: '-'},
			HelpButton(this, TABINDEX_MPSCHED + 98),
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}]
		});

		sw.Promed.swUserWorkPlaceWindow.superclass.initComponent.apply(this, arguments);

	}

});

