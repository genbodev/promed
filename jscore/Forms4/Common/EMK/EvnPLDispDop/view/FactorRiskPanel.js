Ext6.define("common.EMK.EvnPLDispDop.view.FactorRiskPanel", {//yl:
	extend: "swPanel",
	requires: [
		"common.EMK.EvnPLDispDop.controller.FactorRiskController",
		"common.EMK.EvnPLDispDop.store.FactorRiskStore"
	],
	controller: "EvnPLDispDop13FactorRiskController",
	alias: "widget.EvnPLDispDop13FactorRiskPanel",

	title: "Факторы риска",
	
	listeners: {
		expand: 'onExpand'
	},
	setParams: function() {
		this.getController().setParams();
	},
	initComponent: function () {
		var me = this;

		me.FactorRiskGrid = Ext6.create("Ext6.grid.Panel", {
			padding: 10,//наружные отступы
			cls: "FactorRiskGrid",//внутренние
			viewConfig: {
				minHeight: 33,
			},
			columns: [
				{
					header: "Фактор риска",
					dataIndex: "RiskFactorType_Name",
					minWidth: 100,
					flex: 1,
				}, {
					header: "Дата",
					dataIndex: "DispRiskFactor_insDT",
					minWidth: 150
				}, {
					width: 40,
					dataIndex: "FactorRisk_Action",
					bind: {
						hidden: '{action == "view"}'
					},
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" +me.FactorRiskGrid.id+ "\").showRecordMenu(this, " + record.get("DispRiskFactor_id") + ");'></div>";
					}
				}
			],
			recordMenu: Ext6.create("Ext6.menu.Menu", {
				userCls: "menuWithoutIcons",
				items: [
					{
						text: "Удалить запись",
						handler: me.getController().delFactorRisk,
						ownerPanel: me
					}
				]
			}),
			showRecordMenu: function(el, DispRiskFactor_id) {
				this.recordMenu.DispRiskFactor_id = DispRiskFactor_id;
				this.recordMenu.showBy(el);
			}
		});

		me.FactorRiskMenu = Ext6.create("Ext6.menu.Menu", {//менюшка
			height: 375,
			userCls: "menuWithoutIcons"
		});

		me.FactorRiskBtn = Ext6.create("Ext6.Component", {//кнопка
			autoEl: {
				tag: "a",
				html: "Добавить фактор риска",
				style: "text-decoration:none;display:inline-block;padding:0 10px 10px 15px;"
			},
			listeners: {
				render: function(cmp){
					cmp.getEl().on({//click только на элементе
						click: function(){
							me.FactorRiskMenu.showBy(cmp);
						}
					});
				}
			}
		});

		Ext6.apply(me, {
			items: [
				me.FactorRiskGrid,
				{
					xtype: 'container',
					border: false,
					bind: {
						hidden: '{action == "view"}'
					},
					items: [
						me.FactorRiskMenu,
						me.FactorRiskBtn
					]
				}
			]
		});

		this.callParent(arguments);
	}
});

