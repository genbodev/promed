Ext6.define("common.EMK.EvnPLDispDop.controller.FactorRiskController", {//yl:
	extend: "Ext6.app.ViewController",
	alias: "controller.EvnPLDispDop13FactorRiskController",

	init: function() {
		this.callParent(arguments);
		this.view = this.getView();//панель
		this.vm_data = this.view.ownerPanel.getViewModel().getData();//главная vm ещё без данных
		this.EvnPLDispDop13_fid = null;//id первого этапа, EvnPLDispDop13_id - id текущего

		/*this.view.on({
			expand: this.load,//первое ручное открытие
			scope: this
		});*/
	},
	onExpand: function() {
		this.load();
		this.getView().ownerPanel.AccordionPanel.collapseOtherPanels(this.getView());
	},

	//start с данными vm и полями
	setParams: function () {
		this.loaded=false;//обновили ЭМК

		//вычислим этап: (DispClass_id=2) and (is not null EvnPLDispDop13_fid) => 2-й
		if (this.vm_data.DispClass_id == 2 && this.vm_data.EvnPLDispDop13_fid) {
			this.EvnPLDispDop13_fid = this.vm_data.EvnPLDispDop13_fid;
		}

		if(!this.view.collapsed){//если сразу открыт
			this.load();
		}
	},

	loaded: false,
	load: function() {//загрузка меню и грида
		if (!this.loaded) {
			this.loaded=true;
			this.loadMenu();//пункты в меню
			this.FactorRiskStore = Ext6.create("common.EMK.EvnPLDispDop.store.FactorRiskStore", {//основной стор для грида
				proxy: {
					extraParams: {
						EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
						EvnPLDispDop13_fid: this.EvnPLDispDop13_fid
					}
				}
			}).load();
			this.view.FactorRiskGrid.bindStore(this.FactorRiskStore);//привязка к гриду, т.к. указан классом
		}
	},

	loadMenu: function() {//наполнение менюшки
		var me=this;
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=loadEvnPLDispDop13FactorType",
			params: {
				EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
				EvnPLDispDop13_fid: this.EvnPLDispDop13_fid
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						if(resp.data.length){
							me.view.FactorRiskMenu.removeAll();
							resp.data.forEach(function(record) {
								me.view.FactorRiskMenu.add(
									Ext6.create("Ext6.menu.Item", {
										text: record.RiskFactorType_Name,
										RiskFactorType_id: record.RiskFactorType_id,
										handler: me.addFactorRisk,
										scope: me
									})
								)
							});
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка загрузки меню факторов риска");
				}
			}
		});
	},

	addFactorRisk: function(itemMenu, el, e, eOpts) {//добавление
		var me=this;
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=addEvnPLDispDop13FactorRisk",
			params: {
				EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
				RiskFactorType_id: itemMenu.RiskFactorType_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
					if (Ext.isEmpty(resp.Error_Msg)) {
						if (resp.length && Ext.isEmpty(resp[0].Error_Msg)) {
							me.FactorRiskStore.insert(0,resp[0]);//добавить в грид
							me.view.FactorRiskMenu.remove(itemMenu);//удалить из меню
						}else{
							sw.swMsg.alert(langs("Ошибка"), resp[0].Error_Msg);
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка добавления фактора риска");
				}
			}
		});
	},

	delFactorRisk: function(itemMenu, el, e, eOpts) {//удаление
		var me=itemMenu.ownerPanel.getController();//this не передалось
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=delEvnPLDispDop13FactorRisk",
			params: {
				DispRiskFactor_id: itemMenu.ownerCt.DispRiskFactor_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						var idx=me.FactorRiskStore.find("DispRiskFactor_id",itemMenu.ownerCt.DispRiskFactor_id);
						if(idx>=0){//нашли запись
							var record=me.FactorRiskStore.getAt(idx);
							me.view.FactorRiskMenu.add(//добавить её в меню
								Ext6.create("Ext6.menu.Item", {
									text: record.get("RiskFactorType_Name"),
									DispRiskFactor_id: record.get("DispRiskFactor_id"),
									RiskFactorType_id: record.get("RiskFactorType_id"),
									handler: me.addFactorRisk,
									scope: me
								})
							);
							me.FactorRiskStore.removeAt(idx);//удалить из грида по индексу из-за bind
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка удаления фактора риска");
				}
			}
		});
	},
});