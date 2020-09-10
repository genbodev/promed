/**
 * swBactAntibioticWindow - Управление антибиотиками в выбранном микроорганизме
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Assistant
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Qusijue
 * @version      Сентябрь 2019
 */
sw.Promed.swBactAntibioticWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 1100,
	height: 500,
	modal: true,
	plain: true,
	autoScroll: true,
	draggable: true,
	formParams: null,
	resizable: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	title: 'Список антибиотиков ',
	id: 'swBactAntibioticWindow',
	listeners: {
		hide: function(wnd) {
			let tb = Ext.getCmp('LabAntibiotic_TB');
			let cb = Ext.getCmp('LabAntibiotic_CB');

			if (!Ext.isEmpty(tb)) tb.setValue("");
			if (!Ext.isEmpty(cb)) cb.clearValue();

			var params = Ext.getCmp(wnd.id + '-LabAntibioticTree').loader.baseParams;
			params.BactAntibiotic_Name = "";
			params.BactGuideline_Code = "";
		}
	},

	show: function () {
		var wnd = this;
		sw.Promed.swBactAntibioticWindow.superclass.show.apply(this, arguments);
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
		wnd.setTitle('Список антибиотиков / Микроорганизм ' + arguments[0].BactMicro_Name);
		wnd.MedService_id = arguments[0].MedService_id;
		wnd.BactMicroProbe_id = arguments[0].BactMicroProbe_id;
		wnd.EvnLabSample_id = arguments[0].EvnLabSample_id;
		wnd.BactMicro_id = arguments[0].BactMicro_id;
		wnd.parentGrid = arguments[0].parentGrid;

		this.AntibioticGrid.setParam('BactMicroProbe_id', wnd.BactMicroProbe_id);
		wnd.AntibioticGrid.loadData({
			callback: function() {
				wnd.AntibioticGrid.getGrid().getSelectionModel().selectAll();
			}
		});

		var tree = Ext.getCmp(wnd.id + '-LabAntibioticTree');
		if (tree === undefined) wnd.createTree();
		else {
			tree.loader.baseParams.BactMicroProbe_id = wnd.BactMicroProbe_id;
			tree.loader.baseParams.EvnLabSample_id= wnd.EvnLabSample_id;
			tree.root.reload();
		}
		this.focus(); this.center();
	},

	initComponent: function () {
		var wnd = this;

		var buttonPanel = new Ext.form.FormPanel({
			hideBorders: true,
			items: [
				new Ext.Button({
					text: '>>',
					tooltip: 'Добавить',
					handler: function () { wnd.addAntibiotic(); }
				}),
				new Ext.Button({
					text: '<<',
					tooltip: 'Удалить',
					handler: function () { wnd.delAntibiotic(); }
				})
			]
		});

		var methodCB = new sw.Promed.SwCommonSprCombo({
			allowBlank: false,
			autoLoad: true,
			comboSubject: 'BactMicroABPSens',
			displayField: 'BactMicroABPSens_ShortName',
			editable: true,
			type: 'int',
			moreFields: [
				{ name: 'BactMicroABPSens_ShortName', mapping: 'BactMicroABPSens_ShortName'},
			],
		});
		this.AntibioticGrid = new sw.Promed.ViewFrame({
			checkBoxWidth: 25,
			saveAtOnce: true,
			id: 'AntibioticGrid',
			editing: true,
			showCountInTop: false,
			focusOnFirstLoad: false,
			stateful: true,
			saveAllParams: false,
			selectionModel: 'multiselect',
			dataUrl: '/?c=BactAntibiotic&m=getMicroAntibioticList',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: true,
			width: 650,
			height: 380,
			border: false,
			stringfields: [
				{
					name: 'BactMicroProbeAntibiotic_id',
					hidden: true,
					key: true,
					isparams: true
				}, {
					name: 'UslugaTest_id',
					hidden: true
				}, {
					header: "Наименование антибиотика",
					name: 'BactAntibiotic_Name'
				}, {
					header: "Результат",
					name: 'UslugaTest_ResultValue',
					width: 40,
					editor: new Ext.form.NumberField({ maxLength: 7, maskRe: /[0-9.]/,  minValue: 1 }),
					isparams: true
				}, {
					header: "BactMicroABPSens_ShortName",
					name: 'BactMicroABPSens_ShortName',
					hidden: true
				}, {
					header: "Чувствительность",
					name: 'BactMicroABPSens_id',
					editor: methodCB,
					renderer: function (value, params, store) {
						var v = "";
						if (value == 1) v = "S";
						if (value == 2) v = "I";
						if (value == 3) v = "R";
						return v;
					},
					isparams: true,
					width: 40
				}, {
					header: "Метод",
					name: 'BactMethod_Name',
					width: 50
				}, {
					header: "UslugaTest_ResultLower",
					name: 'UslugaTest_ResultLower',
					hidden: true
				}, {
					header: "UslugaTest_ResultUpper",
					name: 'UslugaTest_ResultUpper',
					hidden: true
				}, {
					header: "Реф. значения",
					name: 'BactMethod_Code',
					align: 'center',
					renderer: function (value, metadata, record) {
						var min = record.get('UslugaTest_ResultLower');
						var max = record.get('UslugaTest_ResultUpper');
						var method = record.get('BactMethod_Code');

						var result = "";
						if (Ext.isEmpty(min) || Ext.isEmpty(max)) return result;
						if (method == 1) {
							result = [
								'<' + min,
								min + '-' + max,
								'>' + max
							];
						} else if (method == 2) {
							result = [
								'>' + max,
								min + '-' + max,
								'<' + min
							];
						} else result = [];
						return result.join('   ');
					}
				}, {
					name: 'UslugaTest_setDT',
					type: 'string',
					header: 'Время выполнения',
					width: 80
				}, {
					header: "Комментарий",
					name: 'UslugaTest_Comment',
					editor: new Ext.form.TextField(),
					isparams: true
				}, {
					header: "Статус",
					name: 'UslugaTest_Status'
				}, {
					name: 'UslugaTest_ResultApproved',
					hidden: true
				}
			], actions: [
				{ name: 'action_save', url: '/?c=BactAntibiotic&m=updateOne', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_add', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			onLoadData: function () {
				var sm = this.getGrid().getSelectionModel();
				sm.grid.getSelectionModel().selectAll()
			},
			onAfterEdit: function(o) {
				if (o.field !== "UslugaTest_ResultValue") return;
				var method = parseInt(o.record.get('BactMethod_Code'));
				var min = parseFloat(o.record.get('UslugaTest_ResultLower'));
				var max = parseFloat(o.record.get('UslugaTest_ResultUpper'));
				var value = parseFloat(o.value);

				if (Ext.isEmpty(method) || Ext.isEmpty(min) || Ext.isEmpty(max) || Ext.isEmpty(value)) return;

				var sens = null;
				if (value < min) sens = (method === 1) ? 1 : 3;
				else if (value > max) sens = (method === 1) ? 3 : 1;
				else sens = 2;

				o.record.set('BactMicroABPSens_id', sens);
				o.record.set('UslugaTest_Status', 'Выполнен');

				Ext.Ajax.request({
					url: '/?c=BactAntibiotic&m=updateOne',
					params: {
						BactMicroProbeAntibiotic_id: o.record.id,
						BactMicroABPSens_id: sens
					}
				});
				
			},
			onRenderGrid: function () {
				if (!wnd.AntibioticGrid.getAction('action_unapproveone')) {
					wnd.AntibioticGrid.addActions({
						name: 'action_unapproveone',
						iconCls: 'archive16',
						disabled: true,
						cls: 'newInGridButton',
						text: langs('Снять одобрение'),
						tooltip: langs('Снять одобрение'),
						handler: function () {
							var selection = wnd.AntibioticGrid.getMultiSelections();
							var list = [];
							for (let i = 0; i < selection.length; i++) {
								var status = selection[i].get('UslugaTest_Status');
								if (status != 'Одобрен') continue;
								list.push({
									BactMicroProbeAntibiotic_id: selection[i].get('BactMicroProbeAntibiotic_id')
								});
							}

							if (list.length == 0) {
								sw.swMsg.alert(langs('Сообщение'), 'Необходимо выбрать хотя бы один одобренный антибиотик');
								return;
							}

							wnd.el.mask('Подождите...');
							Ext.Ajax.request({
								url: '/?c=BactAntibiotic&m=unapproveResult',
								params: {
									AntibioticList: Ext.util.JSON.encode(list)
								},
								success: function(response, options) {
									wnd.el.unmask();
									wnd.AntibioticGrid.ViewGridStore.reload();
								},
								failure: function(response, options) {
									wnd.el.unmask();
									wnd.AntibioticGrid.ViewGridStore.reload();
								}
							 });
						}
					});
				}
				if (!wnd.AntibioticGrid.getAction('action_approveone')) {
					wnd.AntibioticGrid.addActions({
						name: 'action_approveone',
						iconCls: 'archive16',
						disabled: true,
						cls: 'newInGridButton',
						text: langs('Одобрить'),
						tooltip: langs('Одобрить результат'),
						handler: function () {
							var selection = wnd.AntibioticGrid.getMultiSelections();
							var list = [];
							for (let i = 0; i < selection.length; i++) {
								var result = selection[i].get('UslugaTest_ResultValue');
								if (Ext.isEmpty(result)) continue;
								list.push({
									BactMicroProbeAntibiotic_id: selection[i].get('BactMicroProbeAntibiotic_id'),
									UslugaTest_ResultValue: result
								});
							}

							if (list.length == 0) {
								sw.swMsg.alert(langs('Сообщение'), 'Необходимо выбрать хотя бы один выполненный антибиотик');
								return;
							}

							wnd.el.mask('Подождите...');
							Ext.Ajax.request({
								url: '/?c=BactAntibiotic&m=approveResult',
								params: {
									AntibioticList: Ext.util.JSON.encode(list)
								},
								success: function(response, options) {
									wnd.el.unmask();
									wnd.AntibioticGrid.ViewGridStore.reload();
								},
								failure: function(response, options) {
									wnd.el.unmask();
									wnd.AntibioticGrid.ViewGridStore.reload();
								}
							 });
						}
					});
				}
			},
			onRowSelect: function(sm, rowIdx, record) {
				this.onRowSelectionChange();
			}, onRowDeSelect: function(sm, rowIdx, record) {
				this.onRowSelectionChange();
			},
			onRowSelectionChange: function() {
				var approveFlag = true;
				var unapproveFlag = true;

				var records = this.getGrid().getSelectionModel().getSelections();
				//if (records.length == 1) {
					for (var i = 0; i < records.length; i++) {
						if (records[i].get('UslugaTest_Status') == langs('Выполнен')) {
							approveFlag  = false;
						}
						if (records[i].get('UslugaTest_Status') == langs('Одобрен')) {
							unapproveFlag  = false;
						}
					}
				//}
				
				this.setActionDisabled('action_approveone', approveFlag);
				this.setActionDisabled('action_unapproveone', unapproveFlag);
			}
		});

		wnd.LabAntibioticPanel = new Ext.Panel({
			id: wnd.id + '-LabAntibioticPanel',
			border: true,
			title: 'Перечень антибиотиков в лаборатории'
		});
		wnd.MicroAntibioticPanel = new Ext.Panel({
			id: wnd.id + '-MicroAntibioticPanel',
			border: true,
			title: 'Перечень антибиотиков для микроорганизма',
			items: [ this.AntibioticGrid ]
		});
		var container = new Ext.Panel({
			hideBorders: true,
			border: false,
			layout : 'table',
			items: [
				wnd.LabAntibioticPanel,
				buttonPanel,
				wnd.MicroAntibioticPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text: langs('Обновить'),
					handler: function() {
						Ext.getCmp(wnd.id + '-LabAntibioticTree').root.reload();
						wnd.AntibioticGrid.getGrid().getStore().reload();
					}
				}, {
					text: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: function () { wnd.hide(); }
				}
			],
			items: [
				container
			]
		});

		sw.Promed.swBactAntibioticWindow.superclass.initComponent.apply(this, arguments);
	},

	createTree: function (params) {
		var wnd = this;
		var panel = new Ext.Panel({
			border: false,
			layout: 'table',
			items: [
				new Ext.form.TextField({
					id: 'LabAntibiotic_TB',
					width: 199
				}),
				new Ext.form.ComboBox({
					triggerAction: 'all',
					editable: false,
					id: 'LabAntibiotic_CB',
					emptyText: 'Все',
					width: 150,
					mode: 'local',
					
					displayField: 'name',
					valueField: 'id',
					store: new Ext.data.SimpleStore({
						fields: ['id', 'name'],
						data: [
							[0, 'Все'],
							[1, 'CLSI'],
							[2, 'EUCAST']
						]
					})
				}),
				new Ext.Button({
					text: 'Поиск',
					target: wnd.id + '-LabAntibioticTree',
					width: 50,
					dataProviders: [
						{ elId: 'LabAntibiotic_TB', colName: 'BactAntibiotic_Name' },
						{ elId: 'LabAntibiotic_CB', colName: 'Antibiotic' }
					],
					handler: this.filterTreeData
				})
			]
		});

		var tree = new Ext.tree.TreePanel({
			id: wnd.id + '-LabAntibioticTree',
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
			width: 400,
			height: 357,
			root: new Ext.tree.TreeLoader({
				id: 'root',
				nodeType: 'async',
				expanded: true
			}),
			loader: new Ext.tree.TreeLoader({
				autoLoad: false,
				dataUrl: '/?c=BactAntibiotic&m=getSampleAllowAntibioticList',
				baseParams: {
					mode: 'lab',
					MedService_id: wnd.MedService_id,
					EvnLabSample_id: wnd.EvnLabSample_id,
					BactMicroProbe_id: wnd.BactMicroProbe_id
				},
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

		var container = Ext.getCmp(wnd.id + '-LabAntibioticPanel');
		container.add(panel);
		container.add(tree);
		container.doLayout();
	},

	addAntibiotic: function () {
		var wnd = this;

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		loadMask.show();


		var tree = Ext.getCmp(wnd.id + '-LabAntibioticTree');
		var checked = tree.getChecked();

		var AntibioticList = [];
		for (var i = 0; i < checked.length; i++) {
			if (checked[i].attributes['level'] != 3) continue;
			var id = checked[i].attributes['BactAntibiotic_id'];
			var method = checked[i].attributes['BactMethod_id'];
			if (id === "" || method === "") continue;
			AntibioticList.push({
				BactAntibiotic_id: id,
				BactMethod_id: method,
				BactMicro_id: wnd.BactMicro_id
			});
		}

		var params = {
			BactMicroProbe_id: wnd.BactMicroProbe_id,
			EvnLabSample_id: wnd.EvnLabSample_id,
			AntibioticList: Ext.util.JSON.encode(AntibioticList)
		};
		var url = '/?c=BactAntibiotic&m=insertAntibioticToMicro';

		Ext.Ajax.request({
			url: url,
			params: params,
			success: function(response, options) {
				Ext.getCmp(wnd.id + '-LabAntibioticTree').root.reload();
				Ext.getCmp('AntibioticGrid').ViewGridStore.reload();
				loadMask.hide();

				if (wnd.parentGrid == "") return;
				var grid = Ext.getCmp(wnd.parentGrid);
				grid.ViewGridStore.reload();
			}
		 });
	},

	delAntibiotic: function () {
		var wnd = this;

		var checked = wnd.AntibioticGrid.getMultiSelections();
		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		var AntibioticList = [];
		for (var i = 0; i < checked.length; i++) {
			var id = checked[i].id;
			if (id === "" || checked[i].get('UslugaTest_ResultApproved') == 2) continue;
			AntibioticList.push({
				BactMicroProbeAntibiotic_id: id,
				UslugaTest_id: checked[i].get('UslugaTest_id')
			});
		}

		if (AntibioticList.length == 0) {
			sw.swMsg.alert(langs('Сообщение'), 'Необходимо выбрать хотя бы один неодобренный антибиотик');
			return;
		}

		var params = {
			AntibioticList: Ext.util.JSON.encode(AntibioticList)
		};
		var url = '/?c=BactAntibiotic&m=deleteAntibioticFromMicro';

		Ext.Ajax.request({
			url: url,
			params: params,
			success: function(response, options) {
				Ext.getCmp(wnd.id + '-LabAntibioticTree').root.reload();
				Ext.getCmp('AntibioticGrid').ViewGridStore.reload();
				loadMask.hide();
			}, failure: function(response, options) {
				Ext.getCmp(wnd.id + '-LabAntibioticTree').root.reload();
				Ext.getCmp('AntibioticGrid').ViewGridStore.reload();
				loadMask.hide();
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

		var tree = Ext.getCmp('swBactAntibioticWindow-LabAntibioticTree');
		tree.loader.baseParams['BactAntibiotic_Name'] = Ext.getCmp('LabAntibiotic_TB').getValue();
		tree.loader.baseParams['BactGuideline_Code'] = Ext.getCmp('LabAntibiotic_CB').getValue();
		tree.root.reload();
	}
});
