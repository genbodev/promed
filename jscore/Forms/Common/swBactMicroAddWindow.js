/**
 * swBactMicroAddWindow - Выбор микроорганизма для исследования
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
sw.Promed.swBactMicroAddWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 650,
	height: 550,
	modal: true,
	plain: true,
	mode: 'Micro',
	autoScroll: true,
	draggable: true,
	formParams: null,
	resizable: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	title: 'Выбор микроорганизма для исследования',
	id: 'swBactMicroAddWindow',
	openedTabs: {
		'Micro': 1
	},
	listeners: {
		hide: function(wnd) {
			let microCB = Ext.getCmp(wnd.id + '-MicroCB');
			if (!Ext.isEmpty(microCB)) microCB.clearValue();
			let microTB = Ext.getCmp(wnd.id + '-MicroTB');
			if (!Ext.isEmpty(microTB)) microTB.setValue("");
			let mushroomCB = Ext.getCmp(wnd.id + '-MushroomCB');
			if (!Ext.isEmpty(mushroomCB)) mushroomCB.clearValue();
			let mushroomTB = Ext.getCmp(wnd.id + '-MushroomTB');
			if (!Ext.isEmpty(mushroomTB)) mushroomTB.setValue("");
		}
	},

	show: function () {
		var wnd = this;
		sw.Promed.swBactMicroAddWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].MedService_id || !arguments[0].EvnLabSample_id) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function () { wnd.hide(); }
			});
		}
		wnd.MedService_id = arguments[0].MedService_id;
		wnd.EvnLabSample_id = arguments[0].EvnLabSample_id;
		wnd.EvnDirection_id = arguments[0].EvnDirection_id;
		
		if(arguments[0].parentGrid) {
			wnd.parentGrid = arguments[0].parentGrid;
		}

		wnd.researchCB.store.baseParams = {
			EvnDirection_id: wnd.EvnDirection_id,
			EvnLabSample_id: wnd.EvnLabSample_id
		};
		wnd.researchCB.store.load();
		wnd.researchCB.setValue(null);

		wnd.refreshBtns();
		if (Ext.getCmp(wnd.id + 'MicroTree') === undefined) wnd.openTab('Micro');
		this.focus(); this.center();
	},

	initComponent: function () {
		var wnd = this;

		wnd.tabs = new Ext.TabPanel({
			activeTab: 0,
			height: 430,
			plain: true,
			defaults: {
				autoScroll: true
			},
			items: [{
					id: wnd.id + '-MicroTab',
					modeName: 'Micro',
					layout: 'fit',
					title: 'Бактерии',
					items: [
						new Ext.Panel({
							id: wnd.id + '-MicroPanel',
							border: true
						})
					]
				}, {
					id: wnd.id + '-MushroomTab',
					modeName: 'Mushroom',
					layout: 'fit',
					title: 'Грибы',
					items: [
						new Ext.Panel({
							id: wnd.id + '-MushroomPanel',
							border: true
						})
					]
				}
			],
			listeners: {
				tabchange: function(panel, tab) {
					wnd.changeMode(tab.modeName);
					if(wnd.openedTabs.hasOwnProperty(tab.modeName)) return;
					wnd.openedTabs[tab.modeName] = 1;
					wnd.openTab(tab.modeName);
					if (wnd.mode == 'Mushroom') {
						Ext.getCmp('swBactMicroAddWindow-MushroomCB').hideContainer();
						Ext.getCmp('swBactMicroAddWindow-MushroomTB').setWidth('570');
					}
				}
			}
		});

		let researchStore = new Ext.data.Store({
			autoLoad: false,
			url:'/?c=BactMicroProbe&m=getResearchList',
			reader: new Ext.data.JsonReader({
				fields: [
					{name:'EvnUslugaPar_id', type:'int'},
					{name:'Research_Name', type:'string'}
				]
			})
		});

		wnd.researchCB = new Ext.form.ComboBox({
			fieldLabel: 'Исследование',
			store: researchStore,
			editable: false,
			id: wnd.id + '-ResearchCB',
			emptyText: 'Выберите исследование',
			valueField: 'EvnUslugaPar_id',
			displayField: 'Research_Name',
			allowBlank: false,
			triggerAction: 'all',
			width: 215,
			mode: 'local'
		});

		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text: langs('Микроорганизмы не выявлены'),
					disabled: true,
					id: wnd.id + '-addEmptyMicroBtn',
					handler: function () {
						let researchCB = Ext.getCmp(wnd.id + '-ResearchCB');
						let research = researchCB.getValue();
						if (Ext.isEmpty(research)) {
							Ext.Msg.alert(lang['soobschenie'], 'Необходимо выбрать исследование');
							return;
						}
						wnd.addMicro(2);
					}
				}, {
					text: langs('Добавить'),
					id: wnd.id + '-addMicroBtn',
					handler: function () {
						let researchCB = Ext.getCmp(wnd.id + '-ResearchCB');
						let research = researchCB.getValue();
						if (Ext.isEmpty(research)) {
							Ext.Msg.alert(lang['soobschenie'], 'Необходимо выбрать исследование');
							return;
						}
						wnd.addMicro(1);
					}
				}, {
					text: langs('Отменить'),
					iconCls: 'cancel16',
					handler: function () { wnd.hide(); }
				}
			], items: [
				wnd.researchCB,
				this.tabs
			]
		});
		sw.Promed.swBactMicroAddWindow.superclass.initComponent.apply(this, arguments);
	},

	changeMode: function (mode) {
		this.mode = mode;
		let tb = Ext.getCmp(this.id + '-' + mode + 'TB');
		let cb = Ext.getCmp(this.id + '-' + mode + 'CB');

		if (tb) tb.setValue('');
		if (cb && mode == 'Micro') cb.setValue(0);
	},

	openTab: function (mode) {
		var wnd = this;
		var dataCB = [];
		var target = mode;

		if (wnd.mode === 'Micro') {
			dataCB.push([0, 'Все']);
			dataCB.push([1, 'Gram(+)']);
			dataCB.push([2, 'Gram(-)']);
			dataCB.push([3, 'Грамвариабельные']);
		} else {
			target = "Mushroom";
		}
		let baseParams = {
			mode: 'lab',
			MedService_id: wnd.MedService_id,
			target: target
		};
		let tree = Ext.getCmp(wnd.id + '-' + target + 'Tree');
		if (Ext.isEmpty(tree)) {
			wnd.renderMicroPanel({
				dataCB: dataCB,
				target: target,
				baseParams: baseParams
			});
		} else {
			tree.loader.baseParams = baseParams;
			tree.root.reload();
		}
	},

	renderMicroPanel: function (params) {
		var wnd = this;
		var tree = new Ext.tree.TreePanel({
			id: wnd.id + '-' + params.target + 'Tree',
			tableName: 'Micro',
			border: true,
			animate: false,
			collapsible: false,
			autoScroll: true,
			split: true,
			rootVisible: false,
			titleCollapse: false,
			hideCollapseTool: true,
			cls: 'x-tree-noicon',
			useArrows: true,
			width: 635,
			height: 385,
			root: new Ext.tree.TreeLoader({
				id: 'root',
				nodeType: 'async',
				expanded: true
			}),
			loader: new Ext.tree.TreeLoader({
				autoLoad: false,
				dataUrl: '/?c=BactMicro&m=getMicroList',
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
					if (this.changing) return;
					this.changing = false;
				}
			}
		});

		var panel = new Ext.Panel({
			border: false,
			layout: 'table',
			items: [
				new Ext.form.TextField({
					id: wnd.id + '-' + params.target + 'TB',
					width: 366
				}),
				new Ext.form.ComboBox({
					triggerAction: 'all',
					editable: false,
					id: wnd.id + '-' + params.target + 'CB',
					emptyText: 'Все',
					width: 215,
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
					target: wnd.id + '-' + params.target + 'Tree',
					width: 100,
					dataProviders: [
						{ elId: wnd.id + '-' + params.target + 'TB', colName: 'BactMicro_Name' },
						{ elId:  wnd.id + '-' + params.target + 'CB', colName: 'BactGramColor_Code' }
					],
					handler: wnd.filterTreeData
				})
			]
		});



		var container = Ext.getCmp(wnd.id + '-' + params.target + 'Panel');
		container.add(panel);
		container.add(tree);

		var tab = Ext.getCmp(wnd.id + '-' + params.target + 'Tab');
		tab.doLayout();
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

	addMicro: function (isNotShown) {
		var wnd = this;
		var tree = Ext.getCmp([wnd.id, '-', wnd.mode, 'Tree'].join(''));
		var checked = tree.getChecked();
		var idList = [];
		let researchCB = Ext.getCmp(wnd.id + '-ResearchCB');
		let EvnUslugaPar_id = researchCB.getValue();

		for (var i = 0; i < checked.length; i++) {
			idList.push(checked[i].attributes.BactMicro_id);
		}

		if (isNotShown !=2 && idList.length === 0) {
			sw.swMsg.show({
				icon: Ext.MessageBox.ERROR,
				title: 'Ошибка',
				msg: 'Не выбрано ни одного микроорганизма',
				buttons: Ext.Msg.OK
			});
			return;
		}

		wnd.el.mask('Подождите...');
		var params = {
			EvnLabSample_id: wnd.EvnLabSample_id,
			MicroList: idList.join(','),
			EvnUslugaPar_id: EvnUslugaPar_id,
			BactMicroProbe_IsNotShown: isNotShown
		};

		Ext.Ajax.request({
			url: '/?c=BactMicroProbe&m=addMicro',
			params: params,
			success: function(response, options) {
				wnd.el.unmask();
				var response = Ext.util.JSON.decode(response.responseText);

				if (wnd.parentGrid == "") return;
				var grid = Ext.getCmp(wnd.parentGrid);
				grid.ViewGridStore.reload({
					callback: function () {
						var flag = false;
						Ext.each(grid.ViewGridStore.data.items,
							function (record) { if (record.get('BactMicroProbe_IsNotShown') == 2) flag = true;
						});
						grid.setActionDisabled('action_prescr', flag);
						grid.getGrid().getSelectionModel().selectAll();
						wnd.refreshBtns();
					}
				});
				
			}
		});

		if (isNotShown == 2) wnd.hide();
	},

	refreshBtns: function () {
		var wnd = this;
		var grid = Ext.getCmp(wnd.parentGrid);

		if (!grid) return;
		var emptyFlag = false;
		var microFlag = false;

		grid.ViewGridStore.data.each(function (record) {
			if (record.get('BactMicroProbe_IsNotShown') == 2) microFlag = true;
			emptyFlag = true;
		});

		Ext.getCmp(wnd.id + '-addMicroBtn').setDisabled(microFlag);
		Ext.getCmp(wnd.id + '-addEmptyMicroBtn').setDisabled(emptyFlag);
	}
});
