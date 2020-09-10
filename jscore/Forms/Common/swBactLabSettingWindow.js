/**
 * swBactLabSettingWindow - Настройка микробиологической лаборатории
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Assistant
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Qusijue
 * @version
 */
sw.Promed.swBactLabSettingWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 1000,
	height: 500,
	modal: true,
	plain: true,
	mode: 'Antibiotic',
	autoScroll: true,
	draggable: true,
	formParams: null,
	resizable: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	title: 'Настройка микробиологической лаборатории',
	id: 'swBactLabSettingWindow',
	openedTabs: {},
	listeners: {
		hide: function(wnd) {
		}
	},

	show: function () {
		var wnd = this;
		sw.Promed.swBactLabSettingWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function () { wnd.hide(); }
			});
		}
		wnd.MedService_id = arguments[0].MedService_id;
		if (Ext.getCmp(wnd.mode + 'Tree') === undefined) wnd.tabs.fireEvent('tabchange', wnd.tabs, Ext.getCmp('Antibiotic'));
		this.focus(); this.center();

		//var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		//loadMask.show();
	},

	initComponent: function () {
		var wnd = this;
		var buttonPanel = function () {
			return new Ext.form.FormPanel({
				hideBorders: true,
				items: [
					new Ext.Button({
						text: '>>',
						tooltip: 'Добавить',
						handler: function () { wnd.updateTree(true); }
					}),
					new Ext.Button({
						text: '<<',
						tooltip: 'Удалить',
						handler: function () { wnd.updateTree(false); }
					})
				]
			});
		};

		// Область антибиотиков
		wnd.AntibioticPanel = new Ext.Panel({
			id: 'AntibioticPanel',
			border: true,
			title: 'Общий перечень антибиотиков'
		});
		wnd.LabAntibioticPanel = new Ext.Panel({
			id: 'LabAntibioticPanel',
			title: 'Перечень антибиотиков в лаборатории',
			border: true
		});
		var antibioticContainer = new Ext.Panel({
			hideBorders: true,
			border: false,
			layout : 'table',
			items: [
				wnd.AntibioticPanel,
				buttonPanel(),
				wnd.LabAntibioticPanel
			]
		});

		// Область бактерий
		wnd.MicroPanel = new Ext.Panel({
			id: 'MicroPanel',
			title: 'Общий перечень бактерий',
			border: true
		});
		wnd.LabMicroPanel = new Ext.Panel({
			id: 'LabMicroPanel',
			title: 'Перечень бактерий в лаборатории',
			border: true
		});
		var microContainer = new Ext.Panel({
			hideBorders: true,
			border: false,
			layout : 'table',
			items: [
				wnd.MicroPanel,
				buttonPanel(),
				wnd.LabMicroPanel
			]
		});

		// Область грибов
		wnd.MushroomPanel = new Ext.Panel({
			id: 'MushroomPanel',
			title: 'Общий перечень грибов',
			border: true
		});
		wnd.LabMushroomPanel = new Ext.Panel({
			id: 'LabMushroomPanel',
			title: 'Перечень грибов в лаборатории',
			border: true
		});
		var mushroomContainer = new Ext.Panel({
			hideBorders: true,
			border: false,
			layout : 'table',
			items: [
				wnd.MushroomPanel,
				buttonPanel(),
				wnd.LabMushroomPanel
			]
		});

		wnd.tabs = new Ext.TabPanel({
			activeTab: 0,
			height: 430,
			plain: true,
			defaults: {
				autoScroll: true
			},
			items: [{
					id: 'Antibiotic',
					layout: 'fit',
					title: 'Антибиотики',
					items: [antibioticContainer]
				}, {
					id: 'Micro',
					layout: 'fit',
					title: 'Бактерии',
					items: [microContainer]
				}, {
					id: 'Mushroom',
					layout: 'fit',
					title: 'Грибы',
					items: [mushroomContainer]
				},
			],
			listeners: {
				tabchange: function(panel, tab) {
					if (wnd.MedService_id == undefined) return;
					wnd.changeMode(tab.id);
					if(wnd.openedTabs.hasOwnProperty(tab.id)) return;
					wnd.openedTabs[tab.id] = 1;
					wnd.openTab(tab.id);

					if (tab.id == 'Mushroom') {
						Ext.getCmp('MushroomCB').hideContainer();
						Ext.getCmp('MushroomTB').setWidth('409');
						Ext.getCmp('LabMushroomCB').hideContainer();
						Ext.getCmp('LabMushroomTB').setWidth('409');
					}
				}
			}
		});

		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text: langs('Обновить'),
					handler: wnd.refreshPanel
				},
				HelpButton(this, 0), {
					text: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: function () { wnd.hide(); }
				}
			],
			items: [
				this.tabs
			]
		});
		sw.Promed.swBactLabSettingWindow.superclass.initComponent.apply(this, arguments);
	},

	createTree: function (params) {
		var panel = new Ext.Panel({
			border: false,
			layout: 'table',
			items: [
				new Ext.form.TextField({
					id: params.prefix + 'TB',
					width: 269
				}),
				new Ext.form.ComboBox({
					triggerAction: 'all',
					editable: false,
					id: params.prefix + 'CB',
					emptyText: 'Все',
					width: 150,
					mode: 'local',

					displayField: 'name',
					valueField: 'id',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'name'],
						data: params.dataCB
					})
				}),
				new Ext.Button({
					text: 'Поиск',
					target: params.prefix + 'Tree',
					width: 50,
					dataProviders: [
						{ elId: params.prefix + 'TB', colName: 'Bact' + params.tableName + '_Name' },
						{ elId: params.prefix + 'CB', colName: params.dataSuffix }
					],
					handler: this.filterTreeData
				})
			]
		});

		var tree = new Ext.tree.TreePanel({
			id: params.prefix + 'Tree',
			tableName: params.tableName,
			border: false,
			animate: false,
			collapsible: false,
			autoScroll: true,
			split: true,
			rootVisible: false,
			titleCollapse: false,
			hideCollapseTool: true,
			cls: 'x-tree-noicon',
			useArrows: true,
			width: 470,
			height: 357,
			root: new Ext.tree.TreeLoader({
				id: 'root',
				nodeType: 'async',
				expanded: true
			}),
			loader: new Ext.tree.TreeLoader({
				autoLoad: false,
				dataUrl: params.dataUrl,
				baseParams: params.baseParams,
				uiProviders: {
					'default': Ext.tree.TreeNodeUI,
					tristate: Ext.tree.TreeNodeTriStateUI
				}, listeners: {
					beforeload: function(loader, node) {
						node.ownerTree.el.mask('Подождите...');
					}, load: function (loader, node, response) {
						node.ownerTree.el.unmask();
					}
				}
			}),
			listeners: {
				'checkchange': function (node, checked) {
					if (!this.changing) {
						this.changing = true;
						node.expand(true, false);

						node.cascade( function(node){node.getUI().toggleCheck(checked)} );
						//node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
						this.changing = false;

					}
				},
				'checkparent': function (node, checked) {
					if (!checked) return;
					node.parentNode.check = true;
					node.parentNode.fireEvent('checkparent', node, checked);
				}
			}
		});

		var container = Ext.getCmp(params.prefix + 'Panel');
		container.add(panel);
		container.add(tree);
		container.doLayout();
		Ext.getCmp(this.mode).doLayout();
	},

	changeMode: function (mode) {
		this.mode = mode;
	},

	updateTree: function (isInsert) {
		var wnd = Ext.getCmp('swBactLabSettingWindow');
		var mode = wnd.mode;
		var MedService_id = wnd.MedService_id;

		var treeName = mode + 'Tree';
		if (!isInsert) treeName = 'Lab' + treeName;
		var tree = Ext.getCmp(treeName);
		var checked = tree.getChecked();

		var idList = [];
		for (var i = 0; i < checked.length; i++) {
			var id = checked[i].attributes['Bact' + tree.tableName + '_id'];
			if (Ext.isEmpty(id)) continue;
			idList.push(id);
		}

		if (idList.length == 0) {
			sw.swMsg.alert(langs('Сообщение'), 'Необходимо выбрать хотя бы одну запись');
			return;
		}

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		loadMask.show();
		var params = {};
		params['MedService_id'] = MedService_id;
		params['target'] = mode;
		params[tree.tableName + 'List'] = idList.join(',');
		var url = '/?c=Bact' + tree.tableName + '&m=insert' + tree.tableName + 'ToLab';
		if (!isInsert) url = '/?c=Bact' + tree.tableName + '&m=delete' + tree.tableName + 'FromLab';

		Ext.Ajax.request({
			url: url,
			params: params,
			success: function(response, options) {
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if ( typeof responseObj == 'object' && Ext.isEmpty(responseObj.Error_Msg)) {
					var mode = responseObj.mode;
					var treeName = mode + 'Tree';
					Ext.getCmp(treeName).root.reload();
					treeName = 'Lab' + treeName;
					Ext.getCmp(treeName).root.reload();
				}
				loadMask.hide();
			},
			failure: function (response, options) {
				loadMask.hide();
			}
		 });
	},

	openTab: function (mode) {
		var wnd = this;
		var dataCB = [];
		var tableName = mode;
		var dataSuffix = "";
		var target = mode;
		if (wnd.mode === 'Antibiotic') {
			dataCB.push([0, 'Все']);
			dataCB.push([1, 'CLSI']);
			dataCB.push([2, 'EUCAST']);
			dataSuffix = 'BactGuideline_Code';
		} else if (wnd.mode === 'Micro') {
			dataCB.push([0, 'Все']);
			dataCB.push([1, 'Gram(+)']);
			dataCB.push([2, 'Gram(-)']);
			dataCB.push([3, 'Грамвариабельные']);
			dataSuffix = 'BactGramColor_Code';
		} else {
			dataSuffix = 'BactGramColor_Code';
			if (wnd.mode === 'Mushroom') {
				tableName = 'Micro';
				target = "Mushroom";
			}
		}
		wnd.createTree({
			prefix: mode,
			tableName: tableName,
			dataSuffix: dataSuffix,
			dataUrl: '/?c=Bact' + tableName + '&m=get' + mode + 'List',
			dataCB: dataCB,
			baseParams: {
				mode: 'available',
				MedService_id: wnd.MedService_id,
				target: target
			}
		});
		wnd.createTree({
			prefix: 'Lab' + mode,
			tableName: tableName,
			dataSuffix: dataSuffix,
			dataUrl: '/?c=Bact' + tableName + '&m=get' + mode + 'List',
			dataCB: dataCB,
			baseParams: {
				mode: 'lab',
				MedService_id: wnd.MedService_id,
				target: target
			}
		});
	},

	filterTreeData: function () {
		var data = {};
		var btn = this;
		for (var i = 0; i < btn.dataProviders.length; i++) {
			var elId = btn.dataProviders[i].elId;
			var colName = btn.dataProviders[i].colName;
			data[colName] = Ext.getCmp(elId).getValue();
		}

		var tree = Ext.getCmp(btn.target);
		for (var param in data) {
			tree.loader.baseParams[param] = data[param];
		}
		tree.root.reload();
	},

	refreshPanel: function () {
		var wnd = Ext.getCmp('swBactLabSettingWindow');
		var treeName = wnd.mode + 'Tree';
		Ext.getCmp(treeName).root.reload();
		Ext.getCmp('Lab' + treeName).root.reload();
	}
});
