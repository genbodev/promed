/**
* swRegistryMzViewWindow - окно просмотра и редактирования реестров.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      13.11.2018
*/

/*NO PARSE JSON*/
sw.Promed.swRegistryMzViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	height: 500,
	width: 800,
	title: langs('Реестры счетов МЗ'),
	layout: 'border',
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	onTreeClick: function(node, e) {
		var form = this;
		var level = node.getDepth();

		switch (level) {
			case 0:
			case 1:
			case 2:
				if (node.id.indexOf('type.0.2') >= 0) {
					// отображение справочника ошибок
					form.RightPanel.setVisible(true);
					form.RightPanel.getLayout().setActiveItem(1);
					form.loadRegistryHealDepErrorTypeGrid();
				} else {
					form.RightPanel.setVisible(false);
				}
				break;
			case 3:
				// отображение реестров
				form.RightPanel.setVisible(true);
				form.RightPanel.getLayout().setActiveItem(0);

				form.RegistryGrid.Status_SysNick = node.attributes.object_value;
				form.RegistryGrid.RegistryType_id = node.parentNode.attributes.object_value;

				form.RegistryGrid.setActionHidden('action_towork', form.RegistryGrid.Status_SysNick != 'new');
				form.RegistryGrid.setActionHidden('action_check', form.RegistryGrid.Status_SysNick != 'work');
				form.RegistryGrid.setActionHidden('action_finish', form.RegistryGrid.Status_SysNick != 'work');
				form.RegistryGrid.setActionHidden('action_export', form.RegistryGrid.Status_SysNick != 'work' && form.RegistryGrid.Status_SysNick != 'accepted');
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_sendHDDT', form.RegistryGrid.Status_SysNick != 'new');
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_sendDT', form.RegistryGrid.Status_SysNick != 'work');
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_endCheckDT', form.RegistryGrid.Status_SysNick != 'accepted');
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_AccRecCount', form.RegistryGrid.Status_SysNick && form.RegistryGrid.Status_SysNick.inlist(['new', 'journal']));
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_DecRecCount', form.RegistryGrid.Status_SysNick && form.RegistryGrid.Status_SysNick.inlist(['new', 'journal']));
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_UncRecCount', form.RegistryGrid.Status_SysNick && form.RegistryGrid.Status_SysNick.inlist(['new', 'accepted', 'journal']));
				form.RegistryGrid.setColumnHidden('RegistryHealDepCheckJournal_AccRecSum', form.RegistryGrid.Status_SysNick && form.RegistryGrid.Status_SysNick.inlist(['new', 'journal']));
				form.RegistryGrid.setColumnHidden('RegistryCheckStatus_Name', form.RegistryGrid.Status_SysNick != 'journal' && form.RegistryGrid.Status_SysNick != 'accepted');

				if (form.RegistryGrid.Status_SysNick == 'new') {
					form.DataTab.hide();
					form.RegistryPanel.setHeight(form.RegistryListPanel.getEl().getHeight());
				} else {
					form.DataTab.show();
					form.RegistryPanel.setHeight(280);
				}

				if (form.RegistryGrid.Status_SysNick == 'accepted') {
					form.DataTab.unhideTabStripItem('tab_data');
				} else {
					form.DataTab.hideTabStripItem('tab_data');
					if (form.DataTab.getActiveTab().id == 'tab_data') {
						form.DataTab.setActiveTab(0);
					}
				}

				var filtersForm = form.RegistryFilterPanel.getForm();

				if (form.RegistryGrid.Status_SysNick == 'new') {
					filtersForm.findField('RegistryHealDepCheckJournal_sendHDDT_Range').showContainer();
				} else {
					filtersForm.findField('RegistryHealDepCheckJournal_sendHDDT_Range').hideContainer();
					filtersForm.findField('RegistryHealDepCheckJournal_sendHDDT_Range').setValue(null);
				}

				if (form.RegistryGrid.Status_SysNick == 'work') {
					filtersForm.findField('RegistryHealDepCheckJournal_sendDT_Range').showContainer();
				} else {
					filtersForm.findField('RegistryHealDepCheckJournal_sendDT_Range').hideContainer();
					filtersForm.findField('RegistryHealDepCheckJournal_sendDT_Range').setValue(null);
				}

				if (form.RegistryGrid.Status_SysNick == 'accepted' || form.RegistryGrid.Status_SysNick == 'journal') {
					filtersForm.findField('RegistryHealDepCheckJournal_endCheckDT_Range').showContainer();
				} else {
					filtersForm.findField('RegistryHealDepCheckJournal_endCheckDT_Range').hideContainer();
					filtersForm.findField('RegistryHealDepCheckJournal_endCheckDT_Range').setValue(null);
				}

				form.loadRegistryGrid();
				break;
			case 4:
				form.RightPanel.setVisible(false);
				break;
		}

	},
	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('registry', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},
	onRegistrySelect: function (Registry_id, nofocus, record)
	{
		var form = this;

		switch (form.DataTab.getActiveTab().id)
		{
			case 'tab_registry':
				break;
			case 'tab_data':
				var filtersForm = form.RegistryDataFilterPanel.getForm();
				if (filtersForm.findField('RegistryHealDepErrorType_id').getStore().getCount() == 0) {
					filtersForm.findField('RegistryHealDepErrorType_id').getStore().load();
				}
				filtersForm.findField('Evn_id').setContainerVisible(isSuperAdmin());

				if (form.RegistryDataGrid.Registry_id != Registry_id || form.RegistryDataGrid.getCount() == 0) {
					form.RegistryDataGrid.removeAll(true);
					form.RegistryDataGrid.Registry_id = Registry_id;
					form.loadRegistryDataGrid();
				}
				break;
		}

		return true;
	},
	listeners:
	{
		beforeshow: function()
		{
			this.RightPanel.setVisible(false);
		}
	},
	show: function()
	{
		sw.Promed.swRegistryMzViewWindow.superclass.show.apply(this, arguments);

		var win = this;

		if ( this.firstRun == true ) {
			this.firstRun = false;
		}
		else {
			// При открытии если Root Node уже открыта - перечитываем
			var root = this.Tree.getRootNode();
			if (root)
			{
				if (root.isExpanded())
				{
					this.Tree.getLoader().load(root);
					// Дальше отрабатывает логика на load
				}
			}
		}

		this.maximize();
		this.getReplicationInfo();

		// Также грид "Счета" сбрасываем
		this.RegistryGrid.removeAll();

		if (!win.RegistryGrid.getAction('action_towork')) {
			win.RegistryGrid.addActions({
				name: 'action_towork',
				text: langs('Перевести на проверку'),
				handler: function() {
					win.setRegistryMzCheckStatus('CheckMZ');
				}
			}, 7);
		}

		if (!win.RegistryGrid.getAction('action_check')) {
			win.RegistryGrid.addActions({
				name: 'action_check',
				text: langs('Проверить реестр'),
				handler: function() {
					win.checkRegistry();
				}
			}, 8);
		}

		if (!win.RegistryGrid.getAction('action_finish')) {
			win.RegistryGrid.addActions({
				name: 'action_finish',
				text: langs('Завершить проверку'),
				menu: [{
					text: langs('Принять реестр'),
					handler: function() {
						win.acceptRegistry();
					}
				}, {
					text: langs('Отклонить реестр'),
					handler: function() {
						win.setRegistryMzCheckStatus('RejectMZ');
					}
				}]
			}, 9);
		}

		if (!win.RegistryGrid.getAction('action_export')) {
			win.RegistryGrid.addActions({
				name: 'action_export',
				text: langs('Экспорт в XML'),
				handler: function() {
					win.exportRegistryToXml();
				}
			}, 10);
		}
	},
	exportRegistryToXml: function() {
		var win = this;

		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('Registry_id'))
		{
			sw.swMsg.alert('Ошибка', 'Не выбран реестр');
			return false;
		}

		var Registry_id = record.get('Registry_id');

		getWnd('swRegistryXmlWindow').show({
			onHide: function() {
				win.RegistryGrid.loadData();
			},
			Registry_id: Registry_id,
			RegistryType_id: win.RegistryGrid.RegistryType_id,
			url: '/?c=Registry&m=exportRegistryToXml'
		});
	},
	checkRegistry: function() {
		var win = this;

		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('Registry_id'))
		{
			sw.swMsg.alert('Ошибка', 'Не выбран реестр');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			RegistryType_id = win.RegistryGrid.RegistryType_id;

		getWnd('swRegistryCheckMzWindow').show({
			Registry_id: Registry_id,
			RegistryType_id: RegistryType_id,
			onHide: function() {
				// Перечитываем грид, чтобы обновить данные по счетам
				win.loadRegistryGrid();
			}
		});
	},
	acceptRegistry: function() {
		var win = this;

		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('Registry_id'))
		{
			sw.swMsg.alert('Ошибка', 'Не выбран реестр');
			return false;
		}

		var Registry_id = record.get('Registry_id');

		// Если в реестре есть непроверенные случаи, то появляется сообщение: «Реестр содержит <количество непроверенных случаев> непроверенных случаев. Выберите дальнейшее действие:».
		if (record.get('RegistryHealDepCheckJournal_UncRecCount') > 0) {
			var buttons = {
				yes: "Принять все непроверенные случаи",
				cancel: "Отмена"
			};

			if (record.get('RegistryHealDepCheckJournal_AccRecCount') > 0) {
				buttons = {
					yes: "Принять все непроверенные случаи",
					no: "Отклонить все непроверенные случаи",
					cancel: "Отмена"
				};
			}

			sw.swMsg.show({
				buttons: buttons,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						win.doAcceptRegistry(Registry_id, 'acceptAll');
					} else if (buttonId == 'no') {
						win.doAcceptRegistry(Registry_id, 'rejectAll');
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: langs('Реестр содержит') + ' ' + record.get('RegistryHealDepCheckJournal_UncRecCount') + ' ' + langs('непроверенных случаев. Выберите дальнейшее действие:'),
				title: langs('Внимание')
			});
		} else if (record.get('RegistryHealDepCheckJournal_AccRecCount') > 0) {
			// Если в реестре нет непроверенных случаев И есть принятые случаи, проверка пройдена.
			win.doAcceptRegistry(Registry_id);
		} else {
			// Если отклонены все случаи реестра, появляется сообщение об ошибке: «Реестр не может быть принят, так как все его случаи отклонены от оплаты».
			sw.swMsg.alert('Ошибка', 'Реестр не может быть принят, так как все его случаи отклонены от оплаты');
		}
	},
	doAcceptRegistry: function(Registry_id, action) {
		var win = this;

		win.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=Registry&m=acceptRegistryMz',
			params: {
				Registry_id: Registry_id,
				action: action
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						// Перечитываем грид, чтобы обновить данные по счетам
						win.loadRegistryGrid();
					}
				}
			}
		});
	},
	setRegistryMzCheckStatus: function(RegistryCheckStatus_SysNick) {
		var win = this;

		var record = this.RegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('Registry_id'))
		{
			sw.swMsg.alert('Ошибка', 'Не выбран реестр');
			return false;
		}

		var Registry_id = record.get('Registry_id');

		var msg = '';
		switch(RegistryCheckStatus_SysNick) {
			case 'CheckMZ':
				msg = langs('Перевести выбранный реестр на проверку?');
				break;
			case 'RejectMZ':
				msg = langs('Отклонить от оплаты реестр №' + record.get('Registry_Num') + ' (' + record.get('Registry_accDate').format('d.m.Y') + ') от ' + record.get('Lpu_Nick') + '?');
				break;
			default:
				return;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					win.getLoadMask(LOAD_WAIT).show();
					Ext.Ajax.request({
						url: '/?c=Registry&m=setRegistryMzCheckStatus',
						params: {
							Registry_id: Registry_id,
							RegistryCheckStatus_SysNick: RegistryCheckStatus_SysNick
						},
						callback: function(options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									// Перечитываем грид, чтобы обновить данные по счетам
									win.loadRegistryGrid();
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: msg,
			title: langs('Внимание')
		});
	},
	loadRegistryHealDepErrorTypeGrid: function() {
		var form = this;
		var filtersForm = form.RegistryHealDepErrorTypeFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;

		form.RegistryHealDepErrorTypeGrid.loadData({
			globalFilters: params
		});
	},
	overwriteRegistryTpl: function(record) {
		var form = this;

		if (record && record.get('Registry_id')) {
			var sparams = {
				Registry_Num: record.get('Registry_Num'),
				Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'), 'd.m.Y'),
				Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'), 'd.m.Y'),
				Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'), 'd.m.Y'),
				Registry_Count: record.get('Registry_Count'),
				Registry_Sum: record.get('Registry_Sum'),
				PayType_Name: record.get('PayType_Name'),
				RegistryHealDepCheckJournal_sendHDDT: Ext.util.Format.date(record.get('RegistryHealDepCheckJournal_sendHDDT'), 'd.m.Y'),
				pmUser_sendHDName: record.get('pmUser_sendHDName'),
				RegistryHealDepCheckJournal_sendDT: Ext.util.Format.date(record.get('RegistryHealDepCheckJournal_sendDT'), 'd.m.Y'),
				pmUser_sendName: record.get('pmUser_sendName'),
				RegistryHealDepCheckJournal_endCheckDT: Ext.util.Format.date(record.get('RegistryHealDepCheckJournal_endCheckDT'), 'd.m.Y'),
				RegistryCheckStatus_Name: record.get('RegistryCheckStatus_Name'),
				pmUser_endCheckName: record.get('pmUser_endCheckName'),
				RegistryHealDepCheckJournal_AccRecCount: record.get('RegistryHealDepCheckJournal_AccRecCount'),
				RegistryHealDepCheckJournal_DecRecCount: record.get('RegistryHealDepCheckJournal_DecRecCount'),
				RegistryHealDepCheckJournal_UncRecCount: record.get('RegistryHealDepCheckJournal_UncRecCount'),
				RegistryHealDepCheckJournal_AccRecSum: record.get('RegistryHealDepCheckJournal_AccRecSum')
			};

			form.RegistryTpl.overwrite(form.RegistryInfoPanel.body, sparams);
		} else {
			form.RegistryInfoPanel.body.dom.innerHTML = '';
		}
	},
	loadRegistryGrid: function() {
		var form = this;
		var filtersForm = form.RegistryFilterPanel.getForm();
		var params = filtersForm.getValues();
		params.start = 0;
		params.limit = 100;

		params.Status_SysNick = form.RegistryGrid.Status_SysNick;
		params.RegistryType_id = form.RegistryGrid.RegistryType_id;

		form.RegistryGrid.loadData({
			globalFilters: params
		});
	},
	loadRegistryDataGrid: function() {
		var form = this;

		if (!Ext.isEmpty(form.RegistryDataGrid.Registry_id)) {
			var filtersForm = form.RegistryDataFilterPanel.getForm();
			var params = filtersForm.getValues();
			params.start = 0;
			params.limit = 100;
			params.Registry_id = form.RegistryDataGrid.Registry_id;

			form.RegistryDataGrid.loadData({
				globalFilters: params
			});
		}
	},
	initComponent: function()
	{
		var form = this;

		this.Tree = new Ext.tree.TreePanel(
		{
			id: form.id+'RegistryTree',
			animate: false,
			autoScroll: true,
			split: true,
			region: 'west',
			root:
			{
				id: 'root',
				nodeType: 'async',
				text: 'Реестры',
				expanded: true
			},
			rootVisible: false,
			width: 250,
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl: '/?c=Registry&m=loadRegistryMzTree',
				listeners:
				{
					beforeload: function (loader, node)
					{
						loader.baseParams.level = node.getDepth();
					},
					load: function (loader, node)
					{
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						if (node.id == 'root')
						{
							if ((node.getOwnerTree().rootVisible == false) && (node.hasChildNodes() == true))
							{
								var child = node.findChild('object', 'Lpu');
								if (child)
								{
									node.getOwnerTree().fireEvent('click', child);
									child.select();
									child.expand();
								}
							}
							else
							{
								node.getOwnerTree().fireEvent('click', node);
								node.select();
							}
						}
					}
				}
			})
		});

		// Выбор ноды click-ом
		this.Tree.on('click', function(node, e)
		{
			form.onTreeClick(node, e);
		});

		this.RegistryFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 60,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadRegistryGrid();
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
					labelWidth: 200,
					items: [{
						anchor: '100%',
						fieldLabel: 'МО',
						hiddenName: 'filterLpu_id',
						xtype: 'swlpucombo'
					}, {
						fieldLabel: 'Дата отправки',
						name: 'RegistryHealDepCheckJournal_sendHDDT_Range',
						anchor: '100%',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						xtype: 'daterangefield'
					}, {
						fieldLabel: 'Дата перевода на проверку',
						name: 'RegistryHealDepCheckJournal_sendDT_Range',
						anchor: '100%',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						xtype: 'daterangefield'
					}, {
						fieldLabel: 'Дата проверки',
						name: 'RegistryHealDepCheckJournal_endCheckDT_Range',
						anchor: '100%',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						xtype: 'daterangefield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 390,
					labelWidth: 190,
					items: [{
						fieldLabel: 'Начало отчётного периода',
						name: 'Registry_begDate_Range',
						anchor: '100%',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						xtype: 'daterangefield'
					}, {
						fieldLabel: 'Окончание отчётного периода',
						name: 'Registry_endDate_Range',
						anchor: '100%',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						xtype: 'daterangefield'
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
							form.loadRegistryGrid();
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
							var filtersForm = form.RegistryFilterPanel.getForm();
							filtersForm.reset();
							form.RegistryGrid.removeAll(true);
							form.loadRegistryGrid();
						}
					}]
				}]
			}]
		});

		this.RegistryGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'Registry',
			dataUrl: '/?c=Registry&m=loadRegistryMzGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {
				var Registry_id = record.get('Registry_id');
				form.overwriteRegistryTpl(record);
				form.onRegistrySelect(Registry_id, false, record);
			},
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: !isSuperAdmin()},
				{name: 'RegistryHealDepCheckJournal_sendHDDT', type: 'date', header: 'Дата отправки', width: 100},
				{name: 'Lpu_Nick', type: 'string', header: 'МО', width: 150},
				{name: 'RegistryHealDepCheckJournal_sendDT', type: 'date', header: 'Дата перевода на проверку', width: 120},
				{name: 'Registry_accDate', type: 'date', header: 'Дата реестра', width: 90},
				{name: 'Registry_begDate', type: 'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type: 'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Num', header: 'Номер реестра', width: 120},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 120},
				{name: 'Registry_Count', type: 'int', header: 'Количество случаев', width: 110},
				{name: 'Registry_Sum', type: 'money', header: 'Итоговая сумма', width: 100},
				{name: 'RegistryHealDepCheckJournal_endCheckDT', type: 'date', header: 'Дата проверки', width: 100},
				{name: 'RegistryCheckStatus_Name', type: 'string', header: 'Результат проверки', width: 140},
				{name: 'RegistryHealDepCheckJournal_AccRecCount', type: 'int', header: 'Принято случаев', width: 110},
				{name: 'RegistryHealDepCheckJournal_DecRecCount', type: 'int', header: 'Отклонено случаев', width: 110},
				{name: 'RegistryHealDepCheckJournal_UncRecCount', type: 'int', header: 'Не проверено случаев', width: 110},
				{name: 'RegistryHealDepCheckJournal_AccRecSum', type: 'money', header: 'Сумма к оплате', width: 100},
				{name: 'pmUser_sendHDName', type: 'string', hidden: true},
				{name: 'pmUser_sendName', type: 'string', hidden: true},
				{name: 'pmUser_endCheckName', type: 'string', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', type: 'string', hidden: true}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			]
		});

		this.RegistryGrid.ViewGridPanel.view.getRowClass = function(row, index) {
			var cls = '';

			if (row.get('RegistryCheckStatus_SysNick') == 'CheckMZ' && row.get('RegistryHealDepCheckJournal_UncRecCount') == 0) {
				cls = cls + 'x-grid-rowbackgreen ';
			}

			return cls;
		};

		var RegTplMark = [
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата реестра: {Registry_accDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Период формирования: {Registry_begDate} - {Registry_endDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Количество случаев: {Registry_Count}</div>'+
			'<div style="padding:2px;font-size: 12px;">Итоговая сумма: {Registry_Sum}</div>'+
			'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата отправки: {RegistryHealDepCheckJournal_sendHDDT}</div>'+
			'<div style="padding:2px;font-size: 12px;">Кто отправил: {pmUser_sendHDName}</div>'+
			'<div style="padding:2px;font-size: 12px;">Переведён на проверку: {RegistryHealDepCheckJournal_sendDT}</div>'+
			'<div style="padding:2px;font-size: 12px;">Кто перевёл: {pmUser_sendName}</div>'+
			'<div style="padding:2px;font-size: 12px;">Проверен: {RegistryHealDepCheckJournal_endCheckDT}</div>'+
			'<div style="padding:2px;font-size: 12px;">Результат проверки: {RegistryCheckStatus_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Кто проверил: {pmUser_endCheckName}</div>'+
			'<div style="padding:2px;font-size: 12px;">Принято случаев: {RegistryHealDepCheckJournal_AccRecCount}</div>'+
			'<div style="padding:2px;font-size: 12px;">Отклонено случаев: {RegistryHealDepCheckJournal_DecRecCount}</div>'+
			'<div style="padding:2px;font-size: 12px;">Не проверено случаев: {RegistryHealDepCheckJournal_UncRecCount}</div>'+
			'<div style="padding:2px;font-size: 12px;">Сумма к оплате: {RegistryHealDepCheckJournal_AccRecSum}</div>'
		];
		this.RegistryTpl = new Ext.XTemplate(RegTplMark);

		this.RegistryInfoPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 28,
			maxSize: 28,
			html: ''
		});

		this.RegistryDataFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 90,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadRegistryDataGrid();
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
					width: 300,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfield'
					}, {
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfield'
					}, {
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 390,
					labelWidth: 190,
					items: [{
						anchor: '100%',
						fieldLabel: 'Результат проверки',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[-1, 'Не проверен'],
							[1, 'Принят'],
							[2, 'Отклонён']
						],
						hiddenName: 'RegistryHealDepResType_id',
						xtype: 'combo'
					}, {
						anchor: '100%',
						fieldLabel: 'Ошибка',
						hiddenName: 'RegistryHealDepErrorType_id',
						xtype: 'swregistryhealdeperrortypecombo'
					}, {
						anchor: '100%',
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						xtype: 'numberfield'
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
							form.loadRegistryDataGrid();
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
							var filtersForm = form.RegistryDataFilterPanel.getForm();
							filtersForm.reset();
							form.RegistryDataGrid.removeAll(true);
							form.loadRegistryDataGrid();
						}
					}]
				}]
			}]
		});

		this.RegistryDataGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'RegistryData',
			dataUrl: '/?c=Registry&m=loadRegistryDataMzGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {

			},
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'RegistryHealDepResType_id',  header: 'Результат проверки', renderer: function(val) {
					if (val) {
						switch (parseInt(val)) {
							case 2: // Отклонён
								return "<img src='/img/icons/minus16.png' />";
								break;
							case 1: // Принят
								return "<img src='/img/icons/plus16.png' />";
								break;
						}
					}

					return '';
				}, width: 120},
				{name: 'RegistryHealDepErrorType_Name', type: 'string', header: 'Ошибка', width: 90},
				{name: 'Person_FIO', id: 'autoexpand', sort: true, header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Evn_setDate', type: 'date', header: 'Дата начала лечения', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Дата окончания лечения', width: 80},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{name: 'MedicalCareType_Name', type: 'string', header: 'Тип МП', width: 90}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			]
		});

		this.RegistryDataPanel = new Ext.Panel({
			border: false,
			layout: 'border',
			region: 'north',
			height: 280,
			items: [
				this.RegistryDataFilterPanel,
				this.RegistryDataGrid
			]
		});

		this.DataTab = new Ext.TabPanel({
			border: false,
			region: 'center',
			activeTab: 0,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle: 'width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel) {
					var record = form.RegistryGrid.getGrid().getSelectionModel().getSelected();
					if (record) {
						var Registry_id = record.get('Registry_id');
						form.onRegistrySelect(Registry_id, true, record);
					}
				}
			},
			items: [{
				title: 'Общие сведения',
				layout: 'fit',
				id: 'tab_registry',
				iconCls: 'info16',
				border: false,
				frame: true,
				items: [
					this.RegistryInfoPanel
				]
			}, {
				title: 'Данные',
				layout: 'fit',
				id: 'tab_data',
				border: false,
				frame: true,
				items: [
					this.RegistryDataPanel
				]
			}]
		});

		this.RegistryPanel = new Ext.Panel({
			border: false,
			layout: 'border',
			region: 'north',
			height: 280,
			title: 'Реестры',
			items: [
				this.RegistryFilterPanel,
				this.RegistryGrid
			]
		});

		this.RegistryListPanel = new sw.Promed.Panel({
			border: false,
			layout:'border',
			defaults: {split: true},
			items: [
				form.RegistryPanel,
				form.DataTab
			]
		});

		this.RegistryHealDepErrorTypeFilterPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			bodyStyle: 'background: transparent; padding: 5px;',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadRegistryHealDepErrorTypeGrid();
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
					columnWidth: .20,
					labelWidth: 50,
					items: [{
						anchor: '100%',
						fieldLabel: 'Код',
						name: 'RegistryHealDepErrorType_Code',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 130,
					items: [{
						width: 100,
						xtype: 'combo',
						hideLabel: true,
						hiddenName: 'filterRecords',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все'],
							[2, 'Открытые'],
							[3, 'Закрытые']
						],
						allowBlank: false,
						value: 1
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
							form.loadRegistryHealDepErrorTypeGrid();
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
							var filtersForm = form.RegistryHealDepErrorTypeFilterPanel.getForm();
							filtersForm.reset();
							form.RegistryHealDepErrorTypeGrid.removeAll(true);
							form.loadRegistryHealDepErrorTypeGrid();
						}
					}]
				}]
			}]
		});

		this.RegistryHealDepErrorTypeGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			title: '',
			object: 'RegistryHealDepErrorType',
			editformclassname: 'swRegistryHealDepErrorTypeEditWindow',
			dataUrl: '/?c=Registry&m=loadRegistryHealDepErrorTypeGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'RegistryHealDepErrorType_id', type: 'int', header: 'RegistryHealDepErrorType_id', key: true, hidden: true},
				{name: 'RegistryHealDepErrorType_Code', header: 'Код', width: 100},
				{name: 'RegistryHealDepErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryHealDepErrorType_Descr', header: 'Описание', width: 300, id: 'autoexpand'},
				{name: 'RegistryHealDepErrorType_begDate', type: 'date', header: 'Дата начала', width: 100},
				{name: 'RegistryHealDepErrorType_endDate', type: 'date', header: 'Дата окончания', width: 110}
			],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=Registry&m=deleteRegistryHealDepErrorType'}
			]
		});

		this.RegistryHealDepErrorTypePanel = new Ext.Panel({
			border: false,
			layout: 'border',
			region: 'center',
			title: 'Справочник ошибок',
			items: [
				this.RegistryHealDepErrorTypeFilterPanel,
				this.RegistryHealDepErrorTypeGrid
			]
		})

		this.RightPanel = new Ext.Panel({
			border: false,
			region: 'center',
			layout:'card',
			activeItem: 0,
			defaults: {split: true},
			items: [
				this.RegistryListPanel,
				this.RegistryHealDepErrorTypePanel
			]
		});

		Ext.apply(this,
		{
			layout:'border',
			defaults: {split: true},
			buttons:
			[{
				hidden: false,
				handler: function()
				{
					form.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			},
			{
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
				form.Tree,
				form.RightPanel
			]
		});
		sw.Promed.swRegistryMzViewWindow.superclass.initComponent.apply(this, arguments);
	}
});