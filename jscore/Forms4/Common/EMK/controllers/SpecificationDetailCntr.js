Ext6.define('common.EMK.controllers.SpecificationDetailCntr', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.SpecificationDetailCntr',
	data: {},
	getData: function(){
		return this.data;
	},
	onHide: function(){
		var cntr = this,
			view = cntr.getView();
		view.callback();
	},
	onExpandPrescribePanel: function(){
		var me = this;
		me.loadGrids();
		me.setTitleCounterGrids();
	},
	expandCollapseAll: function(pressed) {
		var cntr = this,
			allrowexpander,
			view = cntr.getView(),
			toolbar = view.lookup('tbar'),
			grids = view.query('grid');

		//toolbar.disable();
		view.mask('...');
		for(var i=0;i<grids.length;i++){

			grids[i].mask('...');
			allrowexpander =  grids[i].getPlugin('allrowexpander');
			if(allrowexpander){
				if (!pressed)
					allrowexpander.expandAll();
				else
					allrowexpander.collapseAll();
			}
			grids[i].unmask();

		}
		view.unmask();
		//toolbar.enable();
	},
	checkLoadStores: function(){
		if(this.loadGridsCount && this.loadGridsCount > 1){
			this.loadGridsCount --;
		}
		else{
			delete this.loadGridsCount;
			this.onLoadGrids();
		}
	},
	loadGrids: function(arrGrids) {
		var cntr = this,
			view = cntr.getView().ViewPrescrGridsPanel,
			allGrids = view.query('grid'),
			grids = [],
			grid, params, store, i;

		if(arrGrids)
			allGrids.forEach(function (g) {
				if (g.objectPrescribe && inlist(g.objectPrescribe, arrGrids)) {
					grids.push(g);
				}
			});
		else grids = allGrids;

		view.mask('Загрузка назначений');
		if (grids.length > 0) {
			if(cntr.prescrArrItems){
				for (i = 0; i < grids.length; i++) {
					store = grids[i].getStore();
					store.removeAll();
					store.loadData(cntr.prescrArrItems[grids[i].objectPrescribe]);
					//grids[i].reconfigure();
				}
				this.onLoadGrids();
				view.unmask();
			} else {
				cntr.loadGridsCount = grids.length;
				for (i = 0; i < grids.length; i++) {
					grid = grids[i];
					params = Ext6.apply({}, grid.params, {
						user_MedStaffFact_id: cntr.data.MedStaffFact_id,
						object: grid.objectPrescribe,
						object_id: grid.objectPrescribe+'_id',
						parent_object_id: 'EvnPrescr_pid',
						parent_object_value: cntr.data.Evn_id,
						param_name: 'section',
						param_value: 'EvnPrescrPolka'
					});
					grid.getStore().load({
						params: params,
						callback: function(records, operation, success) {
							cntr.checkLoadStores();
						}
					});
				}
			}
		}
	},
	onLoadGrids: function(){
		var cntr = this,
			view = cntr.getView(),
			show_treatPanel = view.data.showFirstItem,
			viewGrids = cntr.getView().ViewPrescrGridsPanel,
			rec = view.data.record,
			g;
		delete cntr.prescrArrItems;
		viewGrids.unmask();
		// Если к нам пришла запись, то ее нужно открыть, лишь когда загрузятся сторы
		if(rec && rec.get('object')){
			g = cntr.getGridByObject(rec.get('object'));
			var newRec = g.getStore().findRecord('EvnPrescr_id', rec.get('EvnPrescr_id'));
			if(newRec)
				g.getSelectionModel().select(newRec);
			delete view.data.record;
		}
		// После загрузки необходимых гридов, если присутсвует callback функция, исполняем ее
		if(!Ext6.isEmpty(cntr.cbFn)){
			cntr.cbFn();
			delete cntr.cbFn;
		}
		g = view.down('swGridEvnCourseTreat');
		if(show_treatPanel && g && g.getStore().getCount() > 0 )
		{
			// НЕ УДАЛЯТЬ ДАННЫЙ БЛОК!!! Нужен при первичном открытии формы
			//g.getSelectionModel().select(0);
			//cntr.openSpecification('EvnCourseTreat', g, g.getSelectionModel().getSelectedRecord());
			// НЕ УДАЛЯТЬ ДАННЫЙ БЛОК!!! Нужен при первичном открытии формы
			cntr.openSpecification();
			view.data.showFirstItem = false;
		}
	},
	loadData: function(data,evnParams) {
		this.data = data;
		this.data.evnParams = evnParams;
	},
	getCitoClass: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'grid-header-icon-cito';
		} else {
			return 'grid-header-icon-empty';
		}
	},
	getCitoTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isCito';
		} else {
			return '';
		}
	},
	getDirectionClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnCourseTreat':
				if(parseInt(rec.get('haveRecept')) > 1)
					return 'grid-header-icon-genrecept';
				else
					return 'grid-header-icon-empty';
				break;
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				var status = rec.get('EvnStatus_SysNick')?rec.get('EvnStatus_SysNick'):'';
				if (rec.get('EvnDirection_id') && status.inlist(['Queued','DirZap','Serviced'])) {
					return 'grid-header-icon-direction';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getDirectionTip: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnCourseTreat':
				var strReceipt = 'Без рецепта';
				if(parseInt(rec.get('haveRecept')) > 1){
					var arrDrug = rec.get('DrugListData'),
						ser = '', num = '';
					strReceipt = 'Рецепт: ';
					for(var key in arrDrug){
						if(arrDrug[key] && !Ext6.isEmpty(arrDrug[key].EvnReceptGeneralDrugLink_id)){
							ser = arrDrug[key].EvnReceptGeneral_Ser ? arrDrug[key].EvnReceptGeneral_Ser : '';
							num = arrDrug[key].EvnReceptGeneral_Num;
							strReceipt += '<br>'+ser+' '+num;
						}
					}
				}
				return strReceipt;
				break;
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				if (rec.get('EvnDirection_id')) {
					return 'Направление';
				} else {
					return '';
				}
				break;
			default:
				return '';
		}
	},
	getOtherMOClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			/*case 'EvnCourseTreat':
				if (rec.get('EvnPrescr_IsCito') > 1) {
					return 'grid-header-icon-otherMO';
				} else {
					return 'grid-header-icon-empty';
				}
				break;*/
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				if (rec.get('otherMO') > 1) {
					return 'grid-header-icon-otherMO';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getOtherMOTip: function(v, meta, rec) {
		switch(rec.get('object')) {
			//case 'EvnCourseTreat':
			//	break;
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				if (rec.get('otherMO') > 1) {
					return 'Место оказания - другая МО';
				} else {
					return '';
				}
				break;
			default:
				return '';
		}
	},
	getSelectDTClass: function(v, meta, rec) {
		var DTclass = 'grid-header-icon-empty';
		switch(rec.get('object')) {
			case 'EvnCourseTreat':
				if(parseInt(rec.get('isValid')) > 1)
					DTclass = 'grid-header-icon-selectDT';
				else
					DTclass = 'grid-header-icon-needSelectDT';
				break;
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				switch(rec.get('EvnStatus_SysNick')) {
					case 'Queued':
						DTclass = 'grid-header-icon-queued';
						break;
					case 'DirZap':
						DTclass = 'grid-header-icon-selectDT';
						break;
					case 'Serviced':
						DTclass = 'grid-header-icon-empty';
						break;
					default:
						DTclass = 'grid-header-icon-needSelectDT';
				}
				break;
		}
		return DTclass;
	},
	getSelectDTTip: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
				switch(rec.get('EvnStatus_SysNick')) {
					case 'Queued':
						return 'В очереди';
						break;
					case 'DirZap':
						return 'Определена дата и время. Услуга еще не оказана.';
						break;
					case 'Serviced':
						return '';
						break;
					default:
						return 'Требуется запись';
				}
				break;
			default:
				return '';
		}
	},
	getResultsClass: function(v, meta, rec) {
		switch(rec.get('EvnPrescr_IsExec')) {
			case '2':
			case 2:
				return 'grid-header-icon-results';
			default:
				return 'grid-header-icon-empty';
		}
	},
	getResultsTip: function(v, meta, rec) {
		switch(rec.get('EvnPrescr_IsExec')) {
			case '2':
			case 2:
				if (!Ext6.isEmpty(rec.get('EvnUslugaPar_id'))) {
					return 'Результаты';
				} else {
					return 'Назначение выполнено';
				}
			default:
				return '';
		}
	},
	onMenuClick: function(panel, rowIndex, colIndex, item, e, record){
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
		var isDrug = record.get('object') == 'EvnCourseTreat',
			me = this;
		var cbFn = function(arr){
			if(panel.grid && panel.grid.threeDotMenu){
				panel.grid.threeDotMenu.selRecord = record;
				var menu = panel.grid.threeDotMenu;
				if(isDrug){
					menu.remove(1); // @todo Исправить не на статику
					if (arr) {
						var isConsist = false,
							d = record.get('DrugListData');
						if (d && Object.keys(d).length > 1)
							isConsist = true;
						menu.add({
							text: 'Включить в рецепт',
							menu: arr,
							disabled: isConsist
						});
					} else {
						if (parseInt(record.get('haveRecept')) > 1) {
							menu.add({
								text: 'Исключить из рецепта',
								handler: function () {
									me.deleteEvnReceptGeneralDrugLink(record, panel);
								}
							});
						}
					}
				}
				var position = e.getXY();
				e.stopEvent();
				me.setDisabledMenuItems(menu, record, panel.grid.objectPrescribe);
				menu.showAt(position);
			}
		};
		if(record && isDrug && (!record.get('haveRecept') || parseInt(record.get('haveRecept'))<2)){
			var arr = [],
				EvnCourseTreatDrug_id = Object.keys(record.get('DrugListData'))[0];
			panel.grid.getStore().getRange().forEach(function(rec){
				if(parseInt(rec.get('haveRecept'))>1){
					var drugList = rec.get('DrugListData'),
						Drug_Name = false,
						ser = rec.get('EvnReceptGeneral_Ser')?rec.get('EvnReceptGeneral_Ser'):'',
						num = rec.get('EvnReceptGeneral_Num')?rec.get('EvnReceptGeneral_Num'):'';
					for(var key in drugList) {
						if(Drug_Name)
							break;
						Drug_Name = drugList[key].Drug_Name.split(',')[0];
					}
					arr.push({
						text: Drug_Name+' '+ser+' '+num,
						handler: function(){
							me.saveAddingDrugToReceptGeneral(rec,EvnCourseTreatDrug_id,panel);
						}
					});
				}
			});
			cbFn(arr.length?arr:false);
		}
		else
			cbFn();
	},
	/**
	 * Пройдемся по пунктам меню и заблочим по определенным условиям
	 * @param menu
	 * @param rec
	 * @param objectPrescribe
	 */
	setDisabledMenuItems: function(menu, rec, objectPrescribe){
		menu.items.each(function(menuItem){
			if(menuItem.name === 'TimeSeries')
				menuItem.setDisabled(rec.get('EvnPrescr_IsExec') != 2);
			if(menuItem.name === 'delFromDirect')
				menuItem.setDisabled(rec.get('couple') != 2);
			if(menuItem.name === 'delPrescribe')
				menuItem.setDisabled(objectPrescribe && objectPrescribe === 'EvnDirection');
			if(menuItem.name === 'cancelDirection'){
				var isAllowCancelDir = !!(rec.get('EvnStatus_id') && rec.get('EvnStatus_id').inlist([12,13]));
				menuItem.setDisabled(!rec.get('EvnDirection_id') || isAllowCancelDir || rec.get('EvnPrescr_IsExec') == 2);
			}
		});
	},
	deleteEvnReceptGeneralDrugLink: function(record,panel){
		var cntr = this,
			view = cntr.getView(),
			g = panel.grid,
			s = g.getStore(),
			sm = g.getSelectionModel(),
			selectedRec = sm.getSelectedRecord(),
			updateForm = false;
		if(selectedRec && selectedRec.get('EvnPrescr_id') == record.get('EvnPrescr_id'))
			updateForm = true;
		var EvnReceptGeneralDrugLink_id;
		if(record && !Ext6.isEmpty(record.get('EvnReceptGeneralDrugLink_id'))){
			EvnReceptGeneralDrugLink_id = record.get('EvnReceptGeneralDrugLink_id');
		} else {
			var arrDrug = record.get('DrugListData');
			for(var key in arrDrug){
				if(arrDrug[key] && !Ext6.isEmpty(arrDrug[key].EvnReceptGeneralDrugLink_id))
					EvnReceptGeneralDrugLink_id = arrDrug[key].EvnReceptGeneralDrugLink_id;
			}
		}
		panel.mask('Удаление из рецепта');
		Ext.Ajax.request({
			params: {
				EvnReceptGeneralDrugLink_id: EvnReceptGeneralDrugLink_id
			},
			callback: function(options, success, response) {
				panel.unmask();
				view.evnPrescrCntr.reloadReceptsPanels();
				s.reload({callback: function(){
					var activeForm = view.PrescribeSpecificationPanel.getLayout().getActiveItem();
					if(activeForm && activeForm.cardNumber == 3 && updateForm){ // Если открыта форма редактирования лек. назначения
						var sm = g.getSelectionModel(),
							rec = s.findRecord('EvnPrescr_id', record.get('EvnPrescr_id'));
						if(rec){
							cntr.openSpecification(g.objectPrescribe,g,rec);
							sm.select(rec);
						}
					}
				}});
			},
			url: '/?c=EvnRecept&m=deleteEvnReceptGeneralDrugLink'
		});
	},
	saveAddingDrugToReceptGeneral: function(rec,EvnCourseTreatDrug_id,panel){
		var view = this.getView();
		panel.mask('Добавление в рецепт');
		Ext.Ajax.request({
			params: {
				EvnCourseTreatDrug_id: EvnCourseTreatDrug_id,
				EvnReceptGeneral_id: rec.get('EvnReceptGeneral_id')
			},
			callback: function(options, success, response) {
				panel.unmask();
				view.evnPrescrCntr.reloadReceptsPanels();
				panel.grid.getStore().reload();
			},
			url: '/?c=EvnRecept&m=saveAddingDrugToReceptGeneral'
		});
	},
	addCitoInPrescr: function(panel, rowIndex, colIndex, item, e, record){
		//panel.mask('Обновление параметра');
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде

		var cito = (record.get('EvnPrescr_IsCito')>1)?1:2,
			id = record.get('EvnPrescr_id'),
			cntr = this,
			view = cntr.getView(),
			objectPrescribe = panel.grid.objectPrescribe,
			cb;
		var EvnDirection_id = record.get('EvnDirection_id');
		if (EvnDirection_id) {
			return false;
		}
		if(objectPrescribe.inlist(['EvnCourseProc','EvnPrescrOperBlock','EvnPrescrLabDiag','EvnPrescrFuncDiag','EvnPrescrConsUsluga'])) {
			if (id && record.get('EvnPrescr_IsCito') != 2) {
				getWnd('swSelectMedServiceForCitoWnd').show({
					objectPrescribe: objectPrescribe,
					userMedStaffFact: cntr.data.userMedStaffFact,
					record: record,
					EvnPrescr_id: id,
					callback: function (data) {
						view.mask('Пожалуйста, подождите, идет применение Cito!');
						cb = function (success,response) {
							view.unmask();
							record.set('EvnPrescr_IsCito',2);
							cntr.addTTMSDop(data,record,objectPrescribe);
							//Создание направления для назначения с новой биркой
						};
						cntr.setCito(id,cito,cb);
					}
				});
			}
		} else {
			view.mask('Пожалуйста, подождите, идет применение Cito!');
			cb = function (success,response) {
				view.unmask();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						record.set('EvnPrescr_IsCito', cito)
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при обновлении атрибута'));
					}
				}
			};
			cntr.setCito(id,cito,cb);
		}
	},
	addTTMSDop: function(data,rec,objectPrescribe){
		var cntr = this,
			view = cntr.getView(),
			Resource_id = null,
			url = '/?c=TimetableMedService&m=addTTMSDop',
			dt;

		var MedService_id = data.MedService_id;

		if (data.pzm_MedService_id) {
			MedService_id = data.pzm_MedService_id;
		}
		if (data.Resource_id) {
			Resource_id = data.Resource_id;
			url = '/?c=TimetableResource&m=addTTRDop';
		}
		var params = {
			Day: null,
			StartTime: null,
			MedService_id: MedService_id,
			Resource_id: Resource_id,
			UslugaComplexMedService_id: data.UslugaComplexMedService_id,
			TimetableExtend_Descr: ''
		};

		var RecParams = rec.getData();
		Ext6.apply(RecParams,data);
		//надо создать доп.бирку и записывать на неё
		view.mask('Пожалуйста, подождите, идет создание дополнительной бирки!');
		Ext6.Ajax.request({
			url: url,
			callback: function(opt, success, response) {
				view.unmask();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Error_Msg) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						return false;
					} else {
						if (response_obj.TimetableMedService_id && response_obj.TimetableMedService_begTime) {
							rec.set('TimetableMedService_id', response_obj.TimetableMedService_id);
							RecParams.TimetableMedService_id = response_obj.TimetableMedService_id;
							dt = Date.parseDate(response_obj.TimetableMedService_begTime, 'Y-m-d H:i:s');
							rec.set('TimetableMedService_begTime', dt.format('d.m.Y H:i'));
							RecParams.TimetableMedService_begTime = dt.format('d.m.Y H:i');
						}
						if (response_obj.TimetableResource_id && response_obj.TimetableResource_begTime) {
							rec.set('TimetableResource_id', response_obj.TimetableResource_id);
							RecParams.TimetableResource_id = response_obj.TimetableResource_id;
							dt = Date.parseDate(response_obj.TimetableResource_begTime, 'Y-m-d H:i:s');
							rec.set('TimetableResource_begTime', dt.format('d.m.Y H:i'));
							RecParams.TimetableResource_begTime = dt.format('d.m.Y H:i');
						}
						if(response_obj.TimetableMedService_id || response_obj.TimetableResource_id){
							rec.commit();
							view.mask('Запись на дополнительную бирку');
							RecParams.onSaveEvnDirection = function(data){
								view.unmask();
								cntr.loadGrids([objectPrescribe]);
							};
							cntr.saveEvnDirection(RecParams);
						}
						return true;
					}
				}
			},
			params: params
		});

		return true;
	},
	setCito: function(id,cito,cb){
		if(!cb) cb = Ext6.emptyFn;
		Ext.Ajax.request({
			params: {
				EvnPrescr_id: id,
				EvnPrescr_IsCito: cito
			},
			callback: function(options, success, response) {
				cb(success,response);
			},
			url: '/?c=EvnPrescr&m=setCitoEvnPrescr'
		});
	},
	onResultClick: function(panel, rowIndex, colIndex, item, e, record){
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
		if (!record || Ext6.isEmpty(record.get('EvnUslugaPar_id'))) {
			return false;
		}
		getWnd('uslugaResultWindow').show({
			Evn_id: record.get('EvnUslugaPar_id'),
			object: 'EvnUslugaPar',
			object_id: 'EvnUslugaPar_id',
			userMedStaffFact: this.data.userMedStaffFact.MedStaffFact_id
		});
	},
	onOtherMOClick: function(panel, rowIndex, colIndex, item, e, record){
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
	},
	onDirectionClick: function(panel, rowIndex, colIndex, item, e, record){
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
		var me = this,
			action = 'view',
			cbFn = function(data) {

			};
		if (!record) {
			return false;
		}
		var EvnDirection_id = record.get('EvnDirection_id');
		if (!EvnDirection_id) {
			return false;
		}

		var formParams = {
			EvnDirection_id: EvnDirection_id,
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			Lpu_gid: record.get('Lpu_gid'),
			EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
			DirType_Code: record.get('DirType_Code')
		};

		// если направление на МСЭ, открываем соответсвующую форму
		if (formParams.EvnPrescrMse_id) {
			action = (formParams.Lpu_gid == getGlobalOptions().lpu_id) ? 'edit' : 'view';
			var params = {
				EvnPrescrMse_id: formParams.EvnPrescrMse_id,
				Person_id: formParams.Person_id,
				Server_id: formParams.Server_id,
				onHide: Ext.emptyFn
			};
			getWnd('swDirectionOnMseEditForm').show(params);
			return true;
		}

		var my_params = new Object({
			Person_id: me.data.Person_id,
			EvnDirection_id: formParams.EvnDirection_id,
			callback: cbFn,
			formParams: formParams,
			action: action
		});

		my_params.onHide = Ext.emptyFn;
		getWnd('swEvnDirectionEditWindow'+(record.get('DirType_Code')==9?'Ext6':'') ).show(my_params);
	},
	openSpecificationByItem: function(panel, rowIndex, colIndex, item, e, record){
		panel.up('grid').getSelectionModel().select(rowIndex);
	},
	setTitleCounterGrids: function(panel){
		var cntr = this,
			view = cntr.getView(),
			grids = view.ViewPrescrGridsPanel.query('grid'),
			count = 0;

		for(var i=0;i<grids.length;i++){
			if(grids[i].prescribeGridType && grids[i].prescribeGridType == 'EvnPrescribeView')
				count += grids[i].getStore().getCount();
		}
		/*if(count > 0)
			panel.setTitleCounter(count);*/
		cntr.data.EvnPrescrCount = count;
	},
	openPacketPrescrSaveWindow: function(){
		var me = this;

		getWnd('swPacketPrescrCreateWindow').show({
			Evn_id: me.data.Evn_id,
			Diag_id: me.getDiagId(),
			callback: function() {
				// ну сохранили и сохранили :)
			}
		});
	},
	/**
	 * Возвращает грид по его objectPrescribe
	 * @param object
	 * @returns {boolean}
	 */
	getGridByObject: function(object) {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid'),
			grid = false;
		switch(object){
			case 'EvnCourseTreatEditPanel':
				object = 'EvnCourseTreat';
				break;
			case 'EvnPrescrUslugaInputPanel':
				object = 'EvnPrescrLabDiag';
				break;
			case 'EvnPrescrRegimePanel':
				object = 'EvnPrescrRegime';
				break;
			case 'EvnPrescrDietPanel':
				object = 'EvnPrescrDiet';
				break;
		}
		for(var i=0;i<grids.length;i++){
			if(grids[i].objectPrescribe && grids[i].objectPrescribe == object) {
				grid = grids[i];
			}
		}

		return grid;
	},
	clearAllSelection: function(grid, rec) {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid');
		for (var i = 0; i < grids.length; i++) {
			grids[i].removeCls('selectPrescrGrid');
			if ((Ext6.isObject(rec) && rec.get('object') && grids[i].objectPrescribe && grids[i].objectPrescribe != rec.get('object')) || !rec) {
				grids[i].getSelectionModel().deselectAll();
			}
		}
		if(grid)
			grid.addCls('selectPrescrGrid');
	},
	closeAllSpecification: function () {
		var cntr = this,
			view = cntr.getView();
		view.PrescribeSpecificationPanel.getLayout().setActiveItem(0);
	},
	/**
	 * метод возвращается видимую в данный момент форму или форму-подсказку
	 * @param retEmpty если true - возвратить форму-подсказку
	 * @returns {boolean|object}
	 */
	getVisibleSpecForm: function(retEmpty){
		var v = this.getView(),
			specPanels = v.PrescribeSpecificationPanel.query('[typePanel="SpecificationPanel"]'),
			retPan = false;
		specPanels.forEach(function(el, i, arr){
			if((el.findParentByType('panel').isVisible() && !el.emptyPanel && !retEmpty)
				|| (el.emptyPanel && retEmpty)) { //Если это панель видима, но не панель-подсказка
				retPan = el;
			}
		});
		return retPan;
	},
	openSpecification: function(prescribe, grid, rec){
		var cntr = this,
			view = cntr.getView(),
			panel, indexPanel,
			objectPrescribe = '';

		if(!grid && rec && rec.get('PrescriptionType_Code')){
			objectPrescribe = cntr.getObjectByCode(rec.get('PrescriptionType_Code'));
			grid = cntr.getGridByObject(objectPrescribe);
		}

		cntr.closeAllSpecification();
		cntr.clearAllSelection(grid, rec);

		if(grid && grid.objectPrescribe)
			objectPrescribe = grid.objectPrescribe;
		var arrGrids = [objectPrescribe];
		var params = {
			prescribe: prescribe,
			grid: grid,
			objectPrescribe: objectPrescribe,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			parentEvnClass_SysNick: 'EvnVizitPL',
			record: rec,
			parentCntr: cntr,
			callback: function(obj) {
				// Убрал по задаче #182880 вдруг снова понадобится
				//if(grid && Ext6.isEmpty(grid.getStore().lastOptions) && objectPrescribe)
				cntr.loadGrids(arrGrids);
				// После загрузки грида выполнится в onLoadGrids
				cntr.cbFn = function(){cntr.findAndActionRecord(obj,grid);};
			}
		};

		switch (prescribe) {
			case "EvnCourseTreat":
			case 'EvnCourseTreatEditPanel':
				panel = view.EvnCourseTreatEditPanel;
				indexPanel = 2;
				break;
			case 'EvnCourseProc':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnPrescrUslugaInputPanel':
			case 'EvnPrescrOperBlock':
				if (rec) {
					panel = view.TTMSScheduleRecordPanel;
					indexPanel = 1;
				}
				else {
					panel = view.EvnPrescrUslugaInputPanel;
					indexPanel = 3;
				}
				break;
			case 'TTMSScheduleRecordPanel':
				panel = view.TTMSScheduleRecordPanel;
				indexPanel = 1;
				break;
			case 'EvnPrescrRegime':
			case 'EvnPrescrRegimePanel':
				panel = view.EvnPrescrRegimePanel;
				indexPanel = 4;
				break;
			case 'EvnPrescrDiet':
			case 'EvnPrescrDietPanel':
				panel = view.EvnPrescrDietPanel;
				indexPanel = 5;
				break;
			default:
				indexPanel = 0;
				panel = view.InDevelopPanel;
		}
		// Выбрали, теперь показываем, причем сначала активируем нужную card-panel для верной отрисовки формы
		view.PrescribeSpecificationPanel.getLayout().setActiveItem(indexPanel);
		panel.show(params);
	},
	/**
	 * Возвращает object по его PrescriptionType_Code
	 * @param PrescriptionType_Code
	 * @returns {string}
	 */
	getObjectByCode: function(PrescriptionType_Code) {
		var object = '';
		switch (PrescriptionType_Code) {
			case 1:
				object = 'EvnPrescrRegime';
				break;
			case 2:
				object = 'EvnPrescrDiet';
				break;
			case 5:
				object = 'EvnCourseTreat';
				break;
			case 6:
				object = 'EvnCourseProc';
				break;
			case 7:
				object = 'EvnPrescrOperBlock';
				break;
			case 11:
				object = 'EvnPrescrLabDiag';
				break;
			case 12:
				object = 'EvnPrescrFuncDiag';
				break;
			case 13:
				object = 'EvnPrescrConsUsluga';
				break;
		}

		return object;
	},
	loadUslugaComplexComposition: function(params, callback) {
		var me = this;
		var view = me.getView();

		view.mask('Получение состава услуги...');
		Ext6.Ajax.request({
			params: {
				UslugaComplexMedService_pid: params.UslugaComplexMedService_id,
				MedService_pid: params.MedService_id,
				UslugaComplex_pid: params.UslugaComplex_id,
				Lpu_id: params.Lpu_id,
				isExt6: params.isExt6,
				EvnPrescr_id: params.EvnPrescr_id
			},
			callback: function(options, success, response) {
				view.unmask();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					callback(response_obj);
				}
			},
			url: '/?c=MedService&m=loadCompositionMenu'
		});
	},
	saveEvnDirection: function(prescrParams) {
		log('saveEvnDirection', prescrParams);
		var me = this;
		var view = me.getView();
		var studyTarget_id = 2;

		prescrParams.Person_Surname = this.Person_Surname;
		prescrParams.Person_Firname = this.Person_Firname;
		prescrParams.Person_Secname = this.Person_Secname;
		prescrParams.Person_Birthday = this.Person_Birthday;
		prescrParams.fdata =  me.data; // данные с формы
		prescrParams.fdata.Diag_id =  me.getDiagId() || null;
		
		createDirection(prescrParams, function(direction){
			var checked = []; //список услуг для заказа
			var params = { //параметры для функции создания направления
				person: {
					Person_id: me.data.Person_id
					,PersonEvn_id: me.data.PersonEvn_id
					,Server_id: me.data.Server_id
				},
				needDirection: false,
				mode: 'nosave',
				loadMask: false,
				windowId: 'EvnPrescrUslugaInputWindow',
				onFailure: function(code){
					view.unmask();
				},
				callback: function(responseData, realResponseData){
					view.unmask();
				},
				onCancel: function(){
					view.unmask();
				},
				onCancelQueue: function(evn_queue_id, callback) {
					view.unmask();
				}
			};

			if (prescrParams.PrescriptionType_Code == 11) {
				// нужен состав услуги (тесты)
				if (prescrParams.checked) {
					// если передан с формы назначения то используем его
					checked = prescrParams.checked;
				} else {
					// иначе тянем с сервера
					me.loadUslugaComplexComposition({
						UslugaComplexMedService_id: prescrParams.UslugaComplexMedService_id,
						UslugaComplex_id: prescrParams.UslugaComplex_id,
						Lpu_id: prescrParams.Lpu_id,
						EvnPrescr_id: prescrParams.EvnPrescr_id,
						isExt6: 1
					}, function(response_obj) {
						prescrParams.checked = [];
						if (response_obj.length > 0) {
							for (var i=0; i < response_obj.length; i++) {
								if(response_obj[i].checkedUsl)
									prescrParams.checked.push(response_obj[i].UslugaComplex_id);
							}
						}

						me.saveEvnDirection(prescrParams);
					});

					return false;
				}
			} else {
				checked.push(prescrParams.UslugaComplex_id);
			}

			var studyTarget = Ext6.getCmp('studyTarget');
			if(studyTarget && prescrParams.PrescriptionType_Code == 12) {
				studyTarget_id = studyTarget.getValue();
			}

			direction.EvnPrescr_id = prescrParams.EvnPrescr_id;
			direction.StudyTarget_id = studyTarget_id; //1;
			direction.MedService_pzid = prescrParams.pzm_MedService_id;
			params.order = {
				LpuSectionProfile_id: direction.LpuSectionProfile_id
				,UslugaComplex_id: prescrParams.UslugaComplex_id
				,checked: Ext.util.JSON.encode(checked)
				,Usluga_isCito: (prescrParams.UslugaComplex_IsCito)?2:1
				,UslugaComplex_Name: prescrParams.UslugaComplex_Name
				,UslugaComplexMedService_id: prescrParams.UslugaComplexMedService_id
				,MedService_id: prescrParams.MedService_id
				,Resource_id: prescrParams.Resource_id
				,MedService_pzNick: prescrParams.pzm_MedService_Nick
				,MedService_pzid: prescrParams.pzm_MedService_id
			};

			direction['order'] = Ext.util.JSON.encode(params.order);

			if (prescrParams.PrescriptionType_Code == 11 && !prescrParams.modeDirection) {
				Ext6.Ajax.request({
					url: '/?c=EvnDirection&m=checkEvnDirectionExists',
					params: {
						Person_id: direction.Person_id, // тот же чел
						MedService_id: prescrParams.MedService_id, // та же служба
						UslugaComplex_id: prescrParams.UslugaComplex_id, // тот же биоматериал (определим по услуге)
						EvnDirection_pid: direction.Evn_id, // направление создано в рамках одного случая лечения/движения
						EvnPrescr_id: direction.EvnPrescr_id
					},
					callback: function (swn, success, response) {
						view.getLoadMask().hide();
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							prescrParams.modeDirection = true;
							if (response_obj.EvnDirections) {
								// если нашли направление задаём вопрос
								getWnd('swPrescDirectionIncludeWindow').show({
									EvnDirections: response_obj.EvnDirections,
									callback: function (data) {
										if (data.include == 'yes') {
											prescrParams.IncludeInDirection = data.EvnDirection_id;
											prescrParams.EvnDirection_id = data.EvnDirection_id;
											prescrParams.UslugaList = response_obj.UslugaList;
											me.includeToDirection(prescrParams);
										} else if (data.include == 'no') {
											me.saveEvnDirection(prescrParams);
										}
									}
								});
							} else {
								// иначе записываем
								me.saveEvnDirection(prescrParams);
							}
						}
					}
				});
				return;
			}

			if (prescrParams.modeDirection && prescrParams.IncludeInDirection) {
				direction.IncludeInDirection = prescrParams.IncludeInDirection;
			}

			if (prescrParams.TimetableMedService_id > 0) {
				view.mask('Запись на бирку...');
				params.Timetable_id = prescrParams.TimetableMedService_id;
				params.order.TimetableMedService_id = prescrParams.TimetableMedService_id;
				direction['TimetableMedService_id'] = params.Timetable_id;
				sw.Promed.Direction.requestRecord({
					url: C_TTMS_APPLY,
					loadMask: params.loadMask,
					windowId: params.windowId,
					params: direction,
					Timetable_id: params.Timetable_id,
					fromEmk: false,
					mode: 'nosave',
					needDirection: false,
					Unscheduled: false,
					onHide: Ext.emptyFn,
					onSaveRecord: function(data, answer) {
						if (typeof prescrParams.onSaveEvnDirection == 'function') {
							prescrParams.onSaveEvnDirection(data);
						}
						var text = 'Запись сохранена';

						if (answer && answer.addingMsg && answer.UslugaList) {
							if(answer.EvnDirectionInfo){
								answer.EvnDirections = [answer.EvnDirectionInfo];
							}
							// если нашли направление задаём вопрос
							getWnd('swPrescDirectionIncludeWindow').show({
								EvnDirections: answer.EvnDirections || false,
								EvnDirection_id: answer.EvnDirection_id || false,
								addingMsg: answer.addingMsg,
								callback: function (data) {
									if (data.include == 'yes') {
										prescrParams.EvnDirection_id = answer.EvnDirection_id;
										prescrParams.UslugaList = answer.UslugaList;
										me.includeToDirection(prescrParams);
									}
								}
							});
						}

						sw4.showInfoMsg({
							panel: view,
							type: 'success',
							text: text
						});
						if (prescrParams.PrescriptionType_Code == 12 && direction.StudyTarget_id == 2) {
							me.showMessage(prescrParams.UslugaComplex_Code, prescrParams.UslugaComplex_Name);
						}
					},
					onFailure: params.onFailure,
					callback: params.callback,
					onCancel: params.onCancel,
					onCancelQueue: params.onCancelQueue
				});
			} else if (prescrParams.TimetableResource_id > 0) {
				view.mask('Запись на бирку...');
				params.Timetable_id = prescrParams.TimetableResource_id;
				params.order.TimetableResource_id = prescrParams.TimetableResource_id;
				direction['TimetableResource_id'] = params.Timetable_id;
				sw.Promed.Direction.requestRecord({
					url: C_TTR_APPLY,
					loadMask: params.loadMask,
					windowId: params.windowId,
					params: direction,
					//date: conf.date || null,
					Timetable_id: params.Timetable_id,
					fromEmk: false,
					mode: 'nosave',
					needDirection: false,
					Unscheduled: false,
					onHide: Ext.emptyFn,
					onSaveRecord: function(data, answer) {
						if (typeof prescrParams.onSaveEvnDirection == 'function') {
							prescrParams.onSaveEvnDirection(data);
						}
						var text = 'Запись сохранена';
						if(answer && answer.addingMsg)
							text += '<br>'+answer.addingMsg;
						sw4.showInfoMsg({
							panel: view,
							type: 'success',
							text: text
						});
						if (prescrParams.PrescriptionType_Code == 12 && direction.StudyTarget_id == 2) {
							me.showMessage(prescrParams.UslugaComplex_Code, prescrParams.UslugaComplex_Name);
						}
					},
					onFailure: params.onFailure,
					callback: params.callback,
					onCancel: params.onCancel,
					onCancelQueue: params.onCancelQueue
				});
			} else {
				view.mask('Постановка в очередь...');
				direction.UslugaComplex_did = direction.UslugaComplex_id;
				direction.MedService_did = direction.MedService_id;
				direction.Resource_did = direction.Resource_id;
				direction.LpuSectionProfile_did = direction.LpuSectionProfile_id;
				direction.EvnQueue_pid = direction.EvnDirection_pid;
				direction.MedStaffFact_id = null;
				direction.Prescr = "Prescr";
				sw.Promed.Direction.requestQueue({
					params: direction,
					loadMask: params.loadMask,
					windowId: params.windowId,
					onSaveQueue: function(answer) {
						if (typeof prescrParams.onSaveEvnDirection == 'function') {
							prescrParams.onSaveEvnDirection(answer);
						}
						var text = 'Пациент поставлен в очередь';
						if (answer && answer.addingMsg && answer.UslugaList) {
							if(answer.EvnDirectionInfo){
								answer.EvnDirections = [answer.EvnDirectionInfo];
							}
							// если нашли направление задаём вопрос
							getWnd('swPrescDirectionIncludeWindow').show({
								EvnDirections: answer.EvnDirections || false,
								EvnDirection_id: answer.EvnDirection_id || false,
								addingMsg: answer.addingMsg,
								callback: function (data) {
									if (data.include == 'yes') {
										prescrParams.EvnDirection_id = answer.EvnDirection_id;
										prescrParams.UslugaList = answer.UslugaList;
										me.includeToDirection(prescrParams);
									}
								}
							});
						}
						sw4.showInfoMsg({
							panel: view,
							type: 'success',
							text: text
						});
						if (prescrParams.PrescriptionType_Code == 12 && direction.StudyTarget_id == 2) {
							me.showMessage(prescrParams.UslugaComplex_Code, prescrParams.UslugaComplex_Name);
						}
					},
					onFailure: params.onFailure,
					callback: params.callback
				});
			}

			delete prescrParams.modeDirection;
			delete direction.IncludeInDirection;
		});
	},
	includeToDirection: function (prescrParams) {
		var me = this,
			view = me.getView();
		view.mask('Объединение назначений');
		Ext6.Ajax.request({
			url: '/?c=EvnDirection&m=includeToDirection',
			params: {
				EvnDirection_id: prescrParams.EvnDirection_id, // тот же чел
				pmUser_id: prescrParams.pmUser_id, // тот же чел
				Lpu_id: prescrParams.Lpu_id, // тот же чел
				UslugaList: prescrParams.UslugaList, // тот же чел
				MedService_id: prescrParams.MedService_id, // та же служба
				EvnPrescr_id: prescrParams.EvnPrescr_id, // то же назначение
				Evn_id: prescrParams.EvnPrescr_pid, // направление создано в рамках одного случая лечения/движения
				UslugaComplex_id: prescrParams.UslugaComplex_id, // идентификатор услуги
				UslugaComplexMedService_pid: prescrParams.UslugaComplexMedService_pid, // идентификатор родительской услуги для тестов
				checked: prescrParams.checked // список выделенных тестов в исследовании
			},
			callback: function (swn, success, response) {
				view.unmask();
				if (success && response &&  response.responseText) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						var text = 'Запись сохранена';
						if(response_obj && response_obj.addingMsg)
							text += '<br>'+response_obj.addingMsg;
						sw4.showInfoMsg({
							panel: view,
							type: 'success',
							text: text
						});
						me.loadGrids(['EvnPrescrLabDiag']);
					}
				}
			}
		});
	},
	showMessage: function(code, name, panel){
		var type;
		if (code.match(/(A06|A\.06)/gm)){
			type = 'error';
		} else {
			type = 'warning';
		}

		sw4.showInfoMsg({
			panel: panel,
			type: type,
			text: 'Внимание!<br>Для "' + name + '" цель исследования по умолчанию "2. Диагностическая"!',
			bottom: 55
		});
	},
	onDeletePrescribe: function(grid, selRec, recIsSelected){
		var me = this;
		var activePanel = me.getVisibleSpecForm();
		if(activePanel){
			if(recIsSelected){
				me.openSpecification();
			}
			else{
				if(selRec && selRec.get('object') == grid.objectPrescribe)
					me.findAndActionRecord({'EvnPrescr_id':selRec.get('EvnPrescr_id')},grid);
				if(activePanel.getXType() == "EvnPrescrUslugaInputPanel"
					&& typeof activePanel.loadUslugaComplexGrid == 'function'
					 && grid.objectPrescribe == activePanel.data.objectPrescribe) {
					activePanel.loadUslugaComplexGrid();
				}
				if(activePanel.getXType() == "TTMSScheduleRecordPanel"
					&& typeof activePanel.loadTimetable == 'function') {
					activePanel.loadTimetable();
				}
			}
		}
	},
	findAndActionRecord: function(obj,grid){
		if(obj && obj.EvnPrescr_id){
			var st = grid.getStore(),
				rec = st.findRecord('EvnPrescr_id',obj.EvnPrescr_id);
			if(rec){
				if(obj.action && obj.action == 'add'){
					// Анимация с добавленной строкой
					var row = grid.getView().getRow(st.indexOf(rec)),
						rowCt = Ext6.get(row);
					if(rowCt && rowCt.slideIn)
						Ext6.get(row).slideIn('l', {
							easing: 'easeOut',
							duration: 500 });
					//Ext6.get(row).highlight("#e3f2fd", { duration: 2000 }); // может быть хватит обычной игрой подсветки
				}
				else{

					if(grid.objectPrescribe == 'EvnCourseTreat')
						this.openSpecification();
					grid.getSelectionModel().select(rec);
				}

			}
		}
	},
	openTimeSeriesResults: function (selRec) {
		var cntr = this;

		var params = {
			evnPrescrCntr: cntr,
			UslugaComplex_id: selRec.get('UslugaComplex_id'),
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			EvnVizitPL_id: cntr.data.EvnVizitPL_id,
			EvnVizitPL_setDate: cntr.data.EvnVizitPL_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId(),
			callback: function() {

			}
		};
		getWnd('swTimeSeriesResultsWindow').show(params);
	},
	deleteFromDirection: function(selRec, grid){
		grid.mask('Обновление параметра');
		var cntr = this,
			view = cntr.getView(),
			id = selRec.get('EvnPrescr_id');
		if(id){
			Ext.Ajax.request({
				params: {
					EvnPrescr_id: id,
					EvnDirection_id: selRec.get('EvnDirection_id'),
					EvnPrescr_IsExec: selRec.get('EvnPrescr_IsExec'),
					EvnStatus_id: selRec.get('EvnStatus_id'),
					UslugaComplex_id: selRec.get('UslugaComplex_id'),
					couple: !!(selRec.get('couple')==2),
					DirType_id: 10, // Придет время, и статика сменится динамикой, но это будет другая история
					PrescriptionType_id: 11,
					parentEvnClass_SysNick: 'EvnVizitPL'
				},
				callback: function(options, success, response) {
					grid.unmask();
					cntr.loadGrids([grid.objectPrescribe]);
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							sw4.showInfoMsg({
								panel: view,
								type: 'success',
								text: 'Назначение удалено из направления'
							});
						}
					}
				},
				url: '/?c=EvnPrescr&m=deleteFromDirection'
			});
		}
		else
			grid.unmask();
	},
	getDiagId: function(){
		var cntr = this,
			view = cntr.getView(),
			diag_id = null;
		// На случай, если все-таки придется подставлять последний диагноз
		//if(cntr.data && cntr.data.Diag_id)
		//	diag_id = cntr.data.Diag_id;
		if(view.evnPrescrCntr.getDiagId && typeof view.evnPrescrCntr.getDiagId === 'function')
			diag_id = view.evnPrescrCntr.getDiagId();
		return diag_id;
	},
	cancelEvnDirection: function(record, grid) {

		var cntr = this,
			view = cntr.getView(),
			PersonInfoPanel = view.PersonInfoPanel;
		var EvnDirection_id = record.get('EvnDirection_id');
		if (EvnDirection_id) {
			if (!record) {
				return false;
			}
			if(record.get('timetable') && record.get('timetable_id')){
				record.set(record.get('timetable')+'_id', record.get('timetable_id'));
			}
			var params = {
				cancelType: 'cancel',
				ownerWindow: view,
				EvnDirection_id: EvnDirection_id,
				DirType_Code: record.get('DirType_Code'),
				TimetableGraf_id: record.get('TimetableGraf_id'),
				TimetableMedService_id: record.get('TimetableMedService_id'),
				TimetableResource_id: record.get('TimetableResource_id'),
				TimetableStac_id: record.get('TimetableStac_id'),
				EvnQueue_id: record.get('EvnQueue_id'),
				allowRedirect: false,
				userMedStaffFact: cntr.data.userMedStaffFact,
				personData: {
					Person_id: cntr.data.Person_id,
					Server_id: cntr.data.Server_id,
					PersonEvn_id: cntr.data.PersonEvn_id
				},
				callback: function() {
					grid.mask('Обновление списка');
					grid.getStore().reload({callback: function(){
						grid.unmask();
					}});
				}
			};
			// Костыль по задаче 176700, чтобы направление могло отменяться, даже при отсутствии бирки
			// чтобы при включенном usePostgreLis осуществлялась отмена на postgre БД ЛИС и направление было найдено
			// проверка в этом файле promed/controllers/EvnDirection.php метод cancel
			if (grid && grid.objectPrescribe) {
				if (grid.objectPrescribe === 'EvnPrescrLabDiag') {
					params.formType = 'labdiag';
					params.DirType_id = 10;
				}
				if (grid.objectPrescribe === 'EvnPrescrFuncDiag') {
					params.formType = 'funcdiag';
				}
			}
			if (PersonInfoPanel && PersonInfoPanel.getFieldValue('Person_Surname')) {
				params.personData.Person_Birthday = PersonInfoPanel.getFieldValue('Person_Birthday');
				params.personData.Person_Surname = PersonInfoPanel.getFieldValue('Person_Surname');
				params.personData.Person_Firname = PersonInfoPanel.getFieldValue('Person_Firname');
				params.personData.Person_Secname = PersonInfoPanel.getFieldValue('Person_Secname');
				params.personData.Person_IsDead = PersonInfoPanel.getFieldValue('Person_IsDead');
			} else {
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
				return false;
			}
			/*if(grid && grid.objectPrescribe && grid.objectPrescribe === 'EvnPrescrFuncDiag'){
				getWnd('swSelectDirFailTypeWindow').show({formType: 'funcdiag', LpuUnitType_SysNick: 'parka', onSelectValue: function(responseData) {
						if (!Ext.isEmpty(responseData.DirFailType_id)) {
							grid.mask('Отмена направления на функциональную диагностику...');
							Ext.Ajax.request({
								params: {
									EvnDirection_id: EvnDirection_id,
									DirFailType_id: responseData.DirFailType_id,
									EvnComment_Comment: responseData.EvnComment_Comment
								},
								url: '/?c=EvnFuncRequest&m=cancelDirection',
								callback: function(options, success, response) {
									grid.mask('Обновление списка');
									grid.getStore().reload({callback: function(){
											grid.unmask();
										}});
								}
							});
						}
					}});
			} else {*/
				sw.Promed.Direction.cancel(params);
			//}
		}
	}
});