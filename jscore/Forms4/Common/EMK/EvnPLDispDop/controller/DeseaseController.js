//yl:Заболевания
Ext6.define("common.EMK.EvnPLDispDop.controller.DeseaseController", {
	extend: "Ext6.app.ViewController",
	alias: "controller.EvnPLDispDop13DeseaseController",

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
		
		var view = this.getView(),
			ownerVM = view.ownerPanel.getViewModel(),
			vm = this.getViewModel();

		vm.set('EvnPLDispDop13_id', ownerVM.get('EvnPLDispDop13_id'));
		vm.set('EvnPLDispDop13_fid', ownerVM.get('EvnPLDispDop13_fid'));
		
		//вычислим этап: (DispClass_id=2) and (is not null EvnPLDispDop13_fid) => 2-й
		if (vm.get('DispClass_id') == 2 && vm.get('EvnPLDispDop13_fid')) {
			this.EvnPLDispDop13_fid = vm.get('EvnPLDispDop13_fid');
		}

		if(!this.view.collapsed){//если сразу открыт
			this.load();
		}
	},

	loaded: false,
	load: function() {//загрузка меню и грида
		if (!this.loaded) {
			this.loaded=true;
			this.loadSuspectMenu();//пункты в меню
			this.view.editorCombo.getStore().load();
			/*this.DeseaseStore = Ext6.create("common.EMK.EvnPLDispDop.store.DeseaseStore", {//основной стор для грида
				//proxy: {
					//extraParams: {
						//EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
						//EvnPLDispDop13_fid: this.EvnPLDispDop13_fid
					//}
				//}
			}).load();
			this.view.DeseaseGrid.bindStore(this.DeseaseStore);//привязка к гриду, т.к. указан классом
			*/
			
		}
		this.getView().DeseaseGrid.getStore().load();
	},

	delClick: function(itemMenu, el, e, eOpts) {//определимся что будем удалять из грида: подозрения или диагнозы
		var me=itemMenu.ownerPanel.getController();//this не передалось
		if(itemMenu.ownerCt.DispDeseaseSusp_id){
			me.delSuspect(itemMenu);
		}else{
			me.delDesease(itemMenu);
		}
	},

	/******************************************************************************************************************
	 * Подозрения
	 ******************************************************************************************************************/
	loadSuspectMenu: function() {//наполнение менюшки Подозрения
		var me=this;
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=loadEvnPLDispDop13DispDeseaseSuspType",
			params: {
				EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
				EvnPLDispDop13_fid: this.EvnPLDispDop13_fid
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						if(resp.data.length){
							me.view.SuspectMenu.removeAll();
							resp.data.forEach(function(record) {
								
								me.view.SuspectMenu.add(
									Ext6.create("Ext6.menu.Item", {
										//maxWidth: 400,//???
										text: record.DispDeseaseSuspType_Name,
										DispDeseaseSuspType_id: record.DispDeseaseSuspType_id,
										handler: me.addSuspect,
										scope: me
									})
								)
							});
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка загрузки меню Подозрений");
				}
			}
		});
	},

	filterSuspectMenu: function (field) {//фильтр меню Подозрения
		let val = Ext6.String.trim(field.getValue().toLowerCase());
		this.view.SuspectMenu.items.each(function (item) {
			if (val) {
				item.setHidden((item.text.toLowerCase().indexOf(val) != -1) ? false : true);
			} else {
				item.setHidden(false);
			}
		});
		this.view.SuspectMenu.showBy(this.view.SuspectBtn);
	},

	addSuspect: function(itemMenu, el, e, eOpts) {//добавить Подозрение
		var me=this,
			view = this.getView();
		
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=addEvnPLDispDop13DispDeseaseSuspType",
			params: {
				EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
				DispDeseaseSuspType_id: itemMenu.DispDeseaseSuspType_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
					if (Ext.isEmpty(resp.Error_Msg)) {
						if (resp.length && Ext.isEmpty(resp[0].Error_Msg)) {
							view.DeseaseGrid.getStore().insert(0,resp[0]);//добавить в грид
							me.view.SuspectMenu.remove(itemMenu);//удалить из меню
						}else{
							sw.swMsg.alert(langs("Ошибка"), resp[0].Error_Msg);
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка добавления Подозрения");
				}
			}
		});
	},

	delSuspect: function(itemMenu) {//удаление Подозрения
		var me=this,
			view = this.getView();
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=delEvnPLDispDop13DispDeseaseSusp",
			params: {
				DispDeseaseSusp_id: itemMenu.ownerCt.DispDeseaseSusp_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						var idx=view.DeseaseGrid.getStore().find("DispDeseaseSusp_id",itemMenu.ownerCt.DispDeseaseSusp_id);
						if(idx>=0){//нашли запись
							var record = view.DeseaseGrid.getStore().getAt(idx);
							me.view.SuspectMenu.add(//добавить её в меню
								Ext6.create("Ext6.menu.Item", {
									text: record.get("DispDeseaseSuspType_Name"),
									DispDeseaseSuspType_id: record.get("DispDeseaseSuspType_id"),
									handler: me.addSuspect,
									scope: me
								})
							);
							view.DeseaseGrid.getStore().removeAt(idx);//удалить из грида по индексу из-за bind
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка удаления Подозрения");
				}
			}
		});
	},

	/******************************************************************************************************************
	* Диагнозы
	******************************************************************************************************************/
	addDesease: function(Diag_id) {//добавить Диагноз
		var me=this,
			view = this.getView();
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=addEvnPLDispDop13EvnDiagDopDisp",
			params: {
				EvnPLDispDop13_id: this.vm_data.EvnPLDispDop13_id,
				Diag_id: Diag_id,
				PersonEvn_id: this.vm_data.PersonEvn_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
					if (Ext.isEmpty(resp.Error_Msg)) {
						if (resp.length && Ext.isEmpty(resp[0].Error_Msg)) {
							view.DeseaseGrid.getStore().insert(0,resp[0]);//добавить в грид
						}else{
							sw.swMsg.alert(langs("Ошибка"), resp[0].Error_Msg);
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка добавления Диагноза");
				}
			}
		});
	},

	delDesease: function(itemMenu) {//удаление Диагноза
		var me=this,
			view = this.getView();
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=delEvnPLDispDop13EvnDiagDopDisp",
			params: {
				EvnDiagDopDisp_id: itemMenu.ownerCt.EvnDiagDopDisp_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						var idx=view.DeseaseGrid.getStore().find("EvnDiagDopDisp_id",itemMenu.ownerCt.EvnDiagDopDisp_id);
						if(idx>=0){//нашли запись
							view.DeseaseGrid.getStore().removeAt(idx);//удалить из грида по индексу из-за bind
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка удаления Диагноза");
				}
			}
		});
	},

	updDeseaseDiagSetClass: function(EvnDiagDopDisp_id,DiagSetClass_id,record) {//изменить ТипДиагноза
		var me=this,
			view = this.getView();
		Ext.Ajax.request({
			url: "/?c=EvnPLDispDop13&m=updEvnPLDispDop13DeseaseDiagSetClass",
			params: {
				EvnDiagDopDisp_id: EvnDiagDopDisp_id,
				DiagSetClass_id: DiagSetClass_id
			},
			callback: function (options, success, response) {
				if (success && response && response.responseText) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (Ext.isEmpty(resp.Error_Msg)) {
						var idx=me.DeseaseGrid.getStore().find("EvnDiagDopDisp_id",EvnDiagDopDisp_id);
						if(idx>=0){//нашли запись
							if (resp.length && Ext.isEmpty(resp[0].Error_Msg)) {
								view.DeseaseGrid.getStore().removeAt(idx);//удалить из грида по индексу из-за bind
								view.DeseaseGrid.getStore().insert(idx,resp[0]);//обновить рекорд в гриде
							}else{
								sw.swMsg.alert(langs("Ошибка"), resp[0].Error_Msg);
							}
						}
					} else {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs("Ошибка"), "Ошибка изменения Типа Диагноза");
				}
			}
		});
	},
	
	saveSuspectZNO: function() {
		var view = this.getView(),
			vm = view.getViewModel();
		
		var params = {};
		params.EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id');
		params.Diag_spid = view.queryById('Diag_spid').getValue();
		params.EvnPLDispDop13_IsSuspectZNO = view.queryById('EvnPLDispDop13_IsSuspectZNO').getValue() ? 2 : 1;
		
		if(params.EvnPLDispDop13_IsSuspectZNO == 2) {
			
		}
		
		
		view.mask(langs('Сохранение подозрения на ЗНО'));
		Ext6.Ajax.request(
		{
			url: '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13_SuspectZNO',
			params: params,
			failure: function(response, options)
			{
				view.unmask();
			},
			success: function(response, action)
			{
				view.unmask();
				if (response.responseText)
				{
					var resp = Ext6.util.JSON.decode(response.responseText);
						if (!Ext6.isEmpty(resp.Error_Msg)) {
						sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
					}
				}
			}
		});
	}

});