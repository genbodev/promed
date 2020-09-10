Ext6.define('common.EMK.controllers.EvnPrescribePanelCntr', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPrescribePanelCntr',
	data: {},
	specForm: false,
	onExpandPrescribePanel: function(){
		var me = this,
			data = this.data,
			PrescrPanel = me.getView();
		//me.checkDiag(data.evnParams);
		this.updateData();

		if(data && data.EvnPrescrCount && data.EvnPrescrCount > 0) {// если имеются назначения, подгружать клинические рекоммендации нет необходимости
			me.loadGrids();
		}
	},
	updateData: function() {//синхронизировать данные в контроллере с даннными панели. TODO: хранить данные только в панели.
		var cntr = this,
			view = cntr.getView(),
			data = view.data;

		this.data.userMedStaffFact = data.userMedStaffFact;
		this.data.Person_id = data.Person_id;
		this.data.PersonEvn_id = data.PersonEvn_id;
		this.data.Server_id = data.Server_id;
		this.data.Evn_id = data.Evn_id;
		this.data.Evn_setDate = data.Evn_setDate;
		this.data.LpuSection_id = data.LpuSection_id;
		this.data.MedPersonal_id = data.MedPersonal_id;
		this.data.Diag_id = null;
		if (data.isKVS) this.data.isKVS = data.isKVS;
		if(view.evnParams){
			this.data.evnParams = view.evnParams;
			this.data.Diag_id = view.evnParams.Diag_id?view.evnParams.Diag_id:null;
		}
		if (view.packetSelectPanel)
			view.packetSelectPanel.setParams(data);
		view.DrugSelectPanel.setParams(data);
		view.UslugaSelectPanel.setParams(data);
	},
	/*checkDiag: function(evnParams){
		var me = this,
			stdTextBtn = me.lookupReference('CureStandartText'),
			clrTrg = me.lookupReference('ClearTriggerStdText'),
			title = 'Клинические рекомендации';
		me.data.evnParams = evnParams;
		if(evnParams && evnParams.Diag_Name){
			clrTrg.setStyle('visibility','visible');
			stdTextBtn.setStyle('visibility','visible');
			title += ' для диагноза <b data-qtip="'+evnParams.Diag_Name+'">' + evnParams.Diag_Code + '</b>' ;
		}
		else{
			clrTrg.setStyle('visibility','hidden');
			stdTextBtn.setStyle('visibility','hidden');
		}
		stdTextBtn.setText(title);
	},*/
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
			if (!pressed)
				allrowexpander.expandAll();
			else
				allrowexpander.collapseAll();
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
			this.onLoadStores();
		}
	},
	loadGrids: function(arrGrids) {
		if (this.data.isKVS) return;
		var cntr = this,
			view = cntr.getView(),
			allGrids = view.query('grid[prescribeGridType="EvnPrescribeView"]'),
			grids = [],
			grid, params;

		if(arrGrids)
			allGrids.forEach(function (g) {
				// Если необходимо лишь выборочно обновить гриды
				// ищем по типу объекта или по типу грида (для конкретных типов направлений)
				if ((g.objectPrescribe && inlist(g.objectPrescribe, arrGrids))
					||
					(g.xtype && inlist(g.xtype, arrGrids))) {
					grids.push(g);
				}
			});
		else grids = allGrids;

		if(!view.collapsed && view.ViewPrescrGridsPanel)
			view.ViewPrescrGridsPanel.mask('Загрузка назначений');
		if (grids.length > 0) {
			cntr.loadGridsCount = grids.length;
			for (var i = 0; i < grids.length; i++) {
				grid = grids[i];
				params = Ext6.apply({}, grid.params, {
					user_MedStaffFact_id: cntr.data.MedStaffFact_id,
					object: grid.objectPrescribe,
					object_id: grid.objectPrescribe + '_id',
					parent_object_id: 'EvnPrescr_pid',
					parent_object_value: cntr.data.Evn_id,
					param_name: 'section',
					param_value: 'EvnPrescrPolka',
					EvnDirection_pid: cntr.data.Evn_id,
					//DopDispInfoConsent_id: me.DopDispInfoConsent_id || null
				});
				grid.getStore().load({
					params: params,
					callback: function (records, operation, success) {
						//grid.setVisible(records && records.length>0);
						cntr.checkLoadStores();
					}
				});
			}
		}
	},
	onLoadStores: function(){
		var cntr = this,
			view = cntr.getView();
		view.ViewPrescrGridsPanel.unmask();
		cntr.setTitleCounterGrids();
		if(cntr.onLoadStoresFn && typeof cntr.onLoadStoresFn == 'function'){
			cntr.onLoadStoresFn();
			delete cntr.onLoadStoresFn;
		}
	},
	loadOtherForms: function(){
		var cntr = this;
		if(!cntr.specForm){
			/*Ext6.create({
				xclass: 'common.EMK.tools.swSpecificationDetailWnd',
				//xtype: 'swSpecificationDetailWnd',
				userMedStaffFact: cntr.data.userMedStaffFact,
				Person_id: cntr.data.Person_id,
				PersonEvn_id: cntr.data.PersonEvn_id,
				Server_id: cntr.data.Server_id,
				Evn_id: cntr.data.Evn_id,
				Evn_setDate: cntr.data.Evn_setDate,
				LpuSection_id: cntr.data.LpuSection_id,
				MedPersonal_id: cntr.data.MedPersonal_id
			});*/
			Ext6.require('common.EMK.tools.swSpecificationDetailWnd', function () {
				cntr.specForm = true;
			});
		}

	},
	clearGrids: function(){
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid');

		view.mask('...');
		for(var i=0;i<grids.length;i++){
			if(grids[i].prescribeGridType && grids[i].prescribeGridType == 'EvnPrescribeView')
				grids[i].getStore().removeAll();
		}
		view.unmask();
	},
	getData: function(){
		return {
			'MedPersonal_id': this.data.MedPersonal_id,
			'Evn_id': this.data.Evn_id,
			'PersonEvn_id': this.data.PersonEvn_id,
			'Server_id': this.data.Server_id,
			'Person_id': this.data.Person_id,
			'Diag_id': this.getDiagId()
		};
	},
	getParam: function(param){
		return this.data[param];
	},
	loadData: function(data,evnParams) {
		var cntr = this,
			view = cntr.getView();
		view.data = data; view.evnParams = evnParams;
		cntr.updateData();
		/* убрал в updateData:
		this.data.userMedStaffFact = data.userMedStaffFact;
		this.data.Person_id = data.Person_id;
		this.data.PersonEvn_id = data.PersonEvn_id;
		this.data.Server_id = data.Server_id;
		this.data.Evn_id = data.Evn_id;
		this.data.Evn_setDate = data.Evn_setDate;
		this.data.LpuSection_id = data.LpuSection_id;
		this.data.MedPersonal_id = data.MedPersonal_id;
		this.data.evnParams = evnParams;
		if(evnParams && evnParams.Diag_id)
			this.data.Diag_id = evnParams.Diag_id;
		view.packetSelectPanel.setParams(data);
		view.DrugSelectPanel.setParams(data);
		view.UslugaSelectPanel.setParams(data);
		*/
		if (!cntr.data.isKVS)
			this.clearGrids();
		this.loadOtherForms();
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
			return 'Cito!';
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
			case 2:
			case '2':
				return 'grid-header-icon-results';
			default:
				return 'grid-header-icon-empty';
		}
	},
	getResultsTip: function(v, meta, rec) {
		switch(rec.get('EvnPrescr_IsExec')) {
			case 2:
			case '2':
				if (!Ext6.isEmpty(rec.get('EvnUslugaPar_id'))) {
					return 'Результаты';
				} else {
					return 'Назначение выполнено';
				}
			default:
				return '';
		}
	},
	addCitoInPrescr: function(panel, rowIndex, colIndex, item, e, record){
		//panel.mask('Обновление параметра');
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

		if(objectPrescribe.inlist(['EvnCourseProc','EvnPrescrOperBlock','EvnPrescrLabDiag','EvnPrescrFuncDiag','EvnPrescrConsUsluga'])){
			if(id && record.get('EvnPrescr_IsCito') != 2){
				getWnd('swSelectMedServiceForCitoWnd').show({
					objectPrescribe: objectPrescribe,
					userMedStaffFact: cntr.data.userMedStaffFact,
					record: record,
					EvnPrescr_id: id,
					callback: function(data) {
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
	printRecept: function(EvnReceptGeneral_id) {
		Ext.Ajax.request({
			url: '/?c=EvnRecept&m=saveEvnReceptGeneralIsPrinted',
			params: {
				EvnReceptGeneral_id: EvnReceptGeneral_id
			},
			callback: function () {
				if (getRegionNick() == 'kz') {
					printBirt({
						'Report_FileName': 'EvnReceptMoney_print.rptdesign',
						'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
						'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
						'Report_Format': 'pdf'
					});
				} else {
					Ext.Ajax.request({
						url: '/?c=EvnRecept&m=getReceptGeneralForm',
						params: {
							EvnReceptGeneral_id: EvnReceptGeneral_id
						},
						callback: function(options, success, response) {
							var ReceptForm_id = null;
							var EvnReceptGeneral_setDate = null;
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								ReceptForm_id = result.ReceptForm_id;
								EvnReceptGeneral_setDate = result.EvnReceptGeneral_setDate;

								if (ReceptForm_id == 3 && !Ext6.isEmpty(EvnReceptGeneral_setDate) && EvnReceptGeneral_setDate > '2019-04-06') {
									printBirt({
										'Report_FileName': 'EvnReceptGenprint2_new.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb_new.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
								} else if (ReceptForm_id == 3) {
									printBirt({
										'Report_FileName': 'EvnReceptGenprint2.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
								} else if (ReceptForm_id == 2) {
									printBirt({
										'Report_FileName': 'EvnReceptGenprint_1MI.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
								} else if (ReceptForm_id == 5 && !Ext6.isEmpty(EvnReceptGeneral_setDate) && EvnReceptGeneral_setDate > '2019-04-07') { //при дате выписки рецепта позже 07.04.2019, для рецептов с формой 148-1/у-88 используются отдельные шаблоны
									printBirt({
										'Report_FileName': 'EvnReceptGenprint_2019.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb_2019.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
								} else {
									printBirt({
										'Report_FileName': 'EvnReceptGenprint.rptdesign',
										'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
										'Report_Params': '',
										'Report_Format': 'pdf'
									});
								}
							}
						}
					});
				}
			}
		});
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
	onResultClick: function(panel, rowIndex, colIndex, item, e, record){
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
	},
	onDirectionClick: function(panel, rowIndex, colIndex, item, e, record){
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
	onMenuClick: function(panel, rowIndex, colIndex, item, e, record){
		e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
		var isDrug = record.get('object') == 'EvnCourseTreat',
			me = this;
		var cbFn = function(arr){
			if(panel.grid && panel.grid.threeDotMenu){
				panel.grid.threeDotMenu.selRecord = record;
				var menu = panel.grid.threeDotMenu;
				if(isDrug){
					var listForRemove = menu.query('[itemId/="AddInReceptMenuItem|DeleteFromReceptMenuItem|PrintReceptMenuItem"]');
					listForRemove.forEach(function(menuItem){
						menu.remove(menuItem);
					});
					if (arr) {
						var isConsist = false,
							d = record.get('DrugListData');
						if (d && Object.keys(d).length > 1)
							isConsist = true;
						menu.add({
							text: 'Включить в рецепт',
							itemId: 'AddInReceptMenuItem',
							menu: arr,
							disabled: isConsist
						});
					} else {
						if (parseInt(record.get('haveRecept')) > 1) {
							menu.add({
								text: 'Исключить из рецепта',
								itemId: 'DeleteFromReceptMenuItem',
								handler: function () {
									me.deleteEvnReceptGeneralDrugLink(record, panel);
								}
							});
							menu.add({
								text: 'Печать рецепта',
								itemId: 'PrintReceptMenuItem',
								handler: function () {
									var EvnRecept_id = record.get('EvnRecept_id');
									var EvnReceptGeneral_id = record.get('EvnReceptGeneral_id');
									if (EvnRecept_id) {
										var evn_recept = new sw.Promed.EvnRecept({EvnRecept_id: EvnRecept_id});
										evn_recept.print();
									} else if(EvnReceptGeneral_id) {
										me.printRecept(EvnReceptGeneral_id);
									}
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
					})
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
		var isDir = objectPrescribe && objectPrescribe === 'EvnDirection',
			isDirectZav = true;
		if (getRegionNick().inlist(['perm', 'vologda']) && rec.get('DirType_Code') == 8 && rec.get('EvnStatus_epvkSysNick') && rec.get('EvnStatus_epvkSysNick').inlist(['New', 'Rework'])) {
			isDirectZav = false;
		}
		menu.items.each(function(menuItem){
			if(menuItem.name === 'TimeSeries')
				menuItem.setDisabled(rec.get('EvnPrescr_IsExec') != 2);
			if(menuItem.name === 'delFromDirect')
				menuItem.setDisabled(rec.get('couple') != 2);
			if(menuItem.name === 'delPrescribe')
				menuItem.setDisabled(isDir);
			if(menuItem.name === 'cancelDirection'){
				var isAllowCancelDir = !!(rec.get('EvnStatus_id') && rec.get('EvnStatus_id').inlist([12,13]));
				menuItem.setDisabled(!rec.get('EvnDirection_id') || isAllowCancelDir || rec.get('EvnPrescr_IsExec') == 2);
			}
			if(menuItem.name === 'directZav'){
				menuItem.setDisabled(isDirectZav);
			}
			if(menuItem.name === 'EditDirection' && rec.get('DirType_Code')){
				menuItem.setVisible(rec.get('DirType_Code') != 8);
			}
		});
	},
	directZav: function(record,grid) {
		if (!record || !record.get('EvnPrescrVK_id')) {
			return false;
		}
		var me = this,
			view = me.getView();
		var EvnDirection_id = record.get('EvnDirection_id');
		if (!EvnDirection_id) {
			return false;
		}
		view.mask(LOAD_WAIT_SAVE);
		setEvnStatus({
			EvnClass_SysNick: 'EvnPrescrVK',
			EvnStatus_SysNick: 'Agreement',
			Evn_id: record.get('EvnPrescrVK_id'),
			callback: function() {
				sw4.showInfoMsg({
					panel: me.getView(),
					type: 'success',
					text: 'Направление было отправлено на согласование.'
				});
				view.unmask();
				grid.getStore().reload();
			}
		});
	},

	printEvnDirection: function(record) {
		if (!record) {
			return false;
		}
		var EvnDirection_id = record.get('EvnDirection_id'),
			addParams = '';
		// включена опция печати тестов с мнемоникой
		if (Ext.globalOptions.lis.PrintMnemonikaDirections) {
			addParams += '&PrintMnemonikaDirections=1';
		} else if (Ext.globalOptions.lis.PrintResearchDirections) {
			// или просто опция печати исследований
			addParams += '&PrintResearchDirections=1';
		}
		if (EvnDirection_id && record) {
			if (record.get('DirType_Code') === 9) {
				//"на исследование"
				var birtParams = {
					'Report_FileName': 'printEvnDirection.rptdesign',
					'Report_Params': '&paramEvnDirection=' + EvnDirection_id + addParams,
					'Report_Format': 'pdf'
				};
				if (
					getRegionNick() === 'perm' &&
					!Ext.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
					Ext.globalOptions.lis.direction_print_form === 2
				) {
					Ext6.Ajax.request({
						url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
						params: {
							EvnDirection_id: EvnDirection_id
						},
						callback: function (options, success, response) {
							if (success) {
								var result = Ext6.util.JSON.decode(response.responseText);
								if (!Ext.isEmpty(result.MedServiceType_SysNick) && result.MedServiceType_SysNick !== 'func') {
									birtParams.Report_FileName = 'printEvnDirectionCKDL.rptdesign';
								}
								printBirt(birtParams);
							}
						}
					});
				} else {
					printBirt(birtParams);
				}
			} else if(record.get('DirType_Code') === 7) {
				printBirt({
					'Report_FileName': 'f014u_DirectionHistologic.rptdesign',
					'Report_Params': '&paramEvnDirectionHistologic=' + EvnDirection_id,
					'Report_Format': 'pdf'
				});
			} else if(record.get('DirType_Code') === 29) {
				printBirt({
					'Report_FileName': 'f203u02_Directioncytologic.rptdesign',
					'Report_Params': '&paramEvnDirectioncytologic=' + EvnDirection_id,
					'Report_Format': 'pdf'
				});
			} else if(record.get('DirType_Code') == 30) {
				printBirt({
					'Report_FileName': 'EvnDirectionCVI_Print.rptdesign',
					'Report_Params': '&paramEvnDirectionCVI=' + EvnDirection_id,
					'Report_Format': 'pdf'
				});
			} else {
				sw.Promed.Direction.print({
					EvnDirection_id: EvnDirection_id
				});
			}
		}
	},

	deleteEvnReceptGeneralDrugLink: function(rec,panel){
		var EvnReceptGeneralDrugLink_id,
			me = this;
		if(rec && !Ext6.isEmpty(rec.get('EvnReceptGeneralDrugLink_id'))){
			EvnReceptGeneralDrugLink_id = rec.get('EvnReceptGeneralDrugLink_id');
		} else {
			var arrDrug = rec.get('DrugListData');
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
				me.reloadReceptsPanels();
				me.reloadLinkedGrids(panel.grid);
			},
			url: '/?c=EvnRecept&m=deleteEvnReceptGeneralDrugLink'
		});
	},
	reloadReceptsPanels: function() {
		var view = this.getView();
		if(view.ownerPanel && view.ownerPanel.EvnReceptPanel && view.ownerPanel.EvnReceptPanel.loadBothGrids
			&& typeof view.ownerPanel.EvnReceptPanel.loadBothGrids === 'function'){
			view.ownerPanel.EvnReceptPanel.loadBothGrids();
		} else {
			var ReceptPanels = Ext6.ComponentQuery.query('[refId=\"EvnReceptPanel\"]');
			ReceptPanels.forEach(function(panel){
				if(panel.loadBothGrids && typeof panel.loadBothGrids === 'function')
					panel.loadBothGrids();
			});
		}
	},
	saveAddingDrugToReceptGeneral: function(rec,EvnCourseTreatDrug_id,panel){
		var me = this;
		panel.mask('Добавление в рецепт');
		Ext.Ajax.request({
			params: {
				EvnCourseTreatDrug_id: EvnCourseTreatDrug_id,
				EvnReceptGeneral_id: rec.get('EvnReceptGeneral_id')
			},
			callback: function(options, success, response) {
				panel.unmask();
				me.reloadReceptsPanels();
				me.reloadLinkedGrids(panel.grid);
			},
			url: '/?c=EvnRecept&m=saveAddingDrugToReceptGeneral'
		});
	},
	getCountStores: function(grids, count) {
		var cntr = this,
			view = cntr.getView();
		if (grids.length > 0) {
			var grid = grids.pop();
			if(grid.prescribeGridType && grid.prescribeGridType == 'EvnPrescribeView')
				count += grid.getStore().getCount();
			cntr.getCountStores(grids,count);
		}
		else
			view.setTitleCounter(count)

	},
	setTitleCounterGrids: function() {
		var cntr = this,
			view = cntr.getView(),
			grids = view.ViewPrescrGridsPanel.query('grid'),
			count = 0;
		cntr.getCountStores(grids,count);
	},
	openTemplate: function(mode, packet){
		var me = this,
			view = me.getView(),
			evnPLForm = view.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		getWnd('swPacketPrescrSelectWindow').show({
			MedPersonal_id: me.data.MedPersonal_id,
			Person_id: me.data.Person_id,
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			Evn_id: me.data.Evn_id,
			Evn_setDate: me.data.Evn_setDate,
			EvnParams: me.data.evnParams,
			Diag_id: me.data.evnParams ? me.data.evnParams.Diag_id : me.getDiagId(),
			mode: mode,
			packet: packet,
			EvnPrescrPanelCntr: me,
			PersonInfoPanel: PersonInfoPanel,
			callback: function(params) {
				me.loadGrids();
				view.expand();

				/*if (params.CureStandart_id || params.PacketPrescr_id) {
					me.data.CureStandart_id = params.CureStandart_id;
					me.data.PacketPrescr_id = params.PacketPrescr_id;
					me.loadGrids();
					//var AddPrescrByCheckGridsCntr = me.getView().AddPrescrByCheckGridsPanel.getController();
					//AddPrescrByCheckGridsCntr.loadGrids(me.data);
				}*/
			}
		});
	},
	openPacketPrescrSaveWindow: function(){
		var me = this;
		me.updateData();
		getWnd('swPacketPrescrCreateWindow').show({
			Evn_id: me.data.Evn_id,
			Diag_id: me.getDiagId(),
			MedPersonal_id: me.data.MedPersonal_id,
			callback: function() {
				sw4.showInfoMsg({
					panel: me.getView(),
					type: 'success',
					text: 'Шаблон (пакет назначений) сохранен'
				});
				// ну сохранили и сохранили :)
			}
		});
	},
	clearAllSelection: function(grid, rec) {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid');

		for(var i=0;i<grids.length;i++){
			if(rec && rec.get('object') && grids[i].objectPrescribe && grids[i].objectPrescribe != rec.get('object')) {
				grids[i].getSelectionModel().deselectAll();
			}
		}

	},
	openSpecificationByItem: function(tableview, index, val, item, event, data, el){
		var prescribe = 'EvnCourseTreat';
		if(data)
			prescribe = data.get('object');
		this.openSpecification(prescribe,tableview.ownerCt,tableview.getStore().getAt(index));
	},
	/**
	 * Открываем форму детализации
	 * @param prescribe форма или тип открываемого в детализации объекта
	 * @param grid грид, из которого открывается объект
	 * @param rec открываемая запись, при наличии
	 * @param notSendPrescrArr флаг отмены копирования назначений @TODO ИЗБАВИТЬСЯ ОТ ПАРАМЕТРА
	 */
	openSpecification: function(prescribe, grid, rec, notSendPrescrArr) {
		var cntr = this,
			view = cntr.getView().ownerPanel;
		view.mask('Загрузка формы...');
		cntr.updateData();
		var params = {
			prescrArrItems: (notSendPrescrArr || Ext6.isEmpty(prescribe))?false:cntr.getPrescrArrItems(), //@TODO ИЗБАВИТЬСЯ ОТ ПАРАМЕТРА
			prescribe: prescribe,
			evnPrescrCntr: cntr,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			record: rec,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId(),
			callback: function() {
				cntr.loadGrids();
			},
			onLoadForm: function(){
				view.unmask();
			}
		};
		getWnd('swSpecificationDetailWnd').show(params);
		// Если не поможет открытие формы
		Ext6.defer(function(){view.unmask();},10000);
	},
	openWndSpecification: function () {
		var cntr = this,
			view = cntr.getView().ownerPanel;
		cntr.updateData();
		view.mask('Загрузка формы...');
		var prescrArrItems = cntr.getPrescrArrItems();
		getWnd('swSpecificationDetailWnd').show({
			prescrArrItems: prescrArrItems,
			evnPrescrCntr: cntr,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			showFirstItem: true,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId(),
			callback: function () {
				cntr.loadGrids();
			},
			onLoadForm: function(){
				view.unmask();
			}
		});
		// Если не поможет открытие формы
		Ext6.defer(function(){view.unmask();},10000);
	},
	getPrescrArrItems: function () {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid'),
			arrPrescr = {},
			noPrescr = true;

		for(var i=0;i<grids.length;i++){
			if(noPrescr && grids[i].getStore().getCount()>0)
				noPrescr = false;
			arrPrescr[grids[i].objectPrescribe] = grids[i].getStore().getRange();
		}
		return noPrescr?false:arrPrescr;
	},
	openWndAutoSelectDateTime: function() {
		var formParams = new Object(),
			params = new Object(),
			cntr = this;
		cntr.updateData();
		getWnd('swAutoSelectDateTimeWindow').show({
			evnPrescrCntr: cntr,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			showFirstItem: true,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : null

			/*callback: function() {
				cntr.loadGrids();
			}*/
		});
	},
	printEvnPLPrescr: function () {
		printBirt({
			'Report_FileName': 'DestinationRouteCard.rptdesign',
			'Report_Params': '&paramEvnPL=' + this.data.Evn_id,
			//'Report_Params': '&paramEvnPL=' + this.getView().ownerPanel.EvnPL_id,
			'Report_Format': 'pdf'
		});
		//Старое исполнение
		//window.open('/?c=EvnPrescr&m=printEvnPrescrList&Evn_pid=' + this.data.Evn_id, '_blank');
	},
	enableEdit: function(isEdit){
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid');
		for(var i=0;i<grids.length;i++){
			if(grids[i].prescribeGridType && grids[i].prescribeGridType == 'EvnPrescribeView')
				grids[i].setDisabled(!isEdit);
		}
	},
	getDiagId: function(){
		var cntr = this,
			view = cntr.getView(),
			EvnPLForm = view.ownerPanel,
			diag_id = null;
		// На случай, если все-таки придется подставлять последний диагноз
		//if(cntr.data && cntr.data.Diag_id)
		//	diag_id = cntr.data.Diag_id;
		if(EvnPLForm && EvnPLForm.getDiagId())
			diag_id = EvnPLForm.getDiagId();
		return diag_id;
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
				cntr.loadGrids();
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
					cntr.reloadLinkedGrids(grid);
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
	getPrescriptionType_Code: function(rec){
		if(!rec) return false;

		var PrescriptionType_Code = null;
		switch(rec.get('object')) {
			case 'EvnCourseProc':
				PrescriptionType_Code = 6;
				break;
			case 'EvnPrescrLabDiag':
				PrescriptionType_Code = 11;
				break;
			case 'EvnPrescrFuncDiag':
				PrescriptionType_Code = 12;
				break;
			case 'EvnPrescrConsUsluga':
				PrescriptionType_Code = 13;
				break;
			case 'EvnPrescrOperBlock':
				PrescriptionType_Code = 7;
				break;
		}
		return PrescriptionType_Code;
	},
	getAllowRecordPrescr: function(arrGrids){
		var me = this,
			view = me.getView(),
			grids = view.query('grid'),
			arrPrescr = [];

		grids.forEach(function (g) {
			if (g.objectPrescribe && inlist(g.objectPrescribe, arrGrids)) {
				var r = g.getStore().getRange();
				r.forEach(function (rec) {
					if(!Ext6.isEmpty(rec.get('MedService_id')) && !(rec.get('EvnDirection_id')))
						arrPrescr.push(rec)
				});
			}
		});
		log(arrPrescr);
		return arrPrescr;
	},
	autoRecordAllPrescribe: function(btn){
		var me = this;
		sw.swMsg.show({
			closable: true,
			msg: '<span class="msg-alert-text">Произвести автоподбор?</span>',
			buttons: Ext6.Msg.OKCANCEL,
			fn: function (btnId) {
				if (btnId == 'ok') {
					me.doAutoRecordAllPrescribe(btn);
				}
			}
		});
	},
	doAutoRecordAllPrescribe: function(btn){

		var me = this,
			view = me.getView(),
			cbFn,
			arrGrids = ['EvnPrescrConsUsluga','EvnPrescrFuncDiag','EvnPrescrLabDiag'],
			arrPrescr = me.getAllowRecordPrescr(arrGrids); // Берем только те услуги, которые можем записать
		if(arrPrescr.length>0){
			Ext6.MessageBox.show({
				title: 'Пожалуйста подождите',
				msg: 'Автоподбор времени...',
				progressText: 'запись...',
				width:300,
				progress:true,
				closable:true,
				animateTarget: btn,
				maskClickAction: function () {

				}
			});

			// Fake progress fn
			cbFn = function() {
				Ext6.defer(function(){Ext6.MessageBox.hide();},2000);
				me.showToast('Автоподбор произведён');
				me.loadGrids(arrGrids);
			};

			//считаем число служб
			me.recordPrescrCount = 0;
			var MedServiceFlag = '';
			arrPrescr.forEach(function(rec) {
				if (MedServiceFlag != rec.data.MedService_id) {
					MedServiceFlag = rec.data.MedService_id;
					me.recordPrescrCount++;

				}
			});

			me.recordPrescrCount++;
			me.allRecordPrescrCount = me.recordPrescrCount;

			// отправляем только! по одному направлению от каждой службы, чтобы не возникало ошибок, дублей
			//в бекэнде назначения в каждой службе должны сами объединиться
			MedServiceFlag = '';
			arrPrescr.forEach(function (rec) {
				if (rec.data.MedService_id != MedServiceFlag) {
					MedServiceFlag = rec.data.MedService_id;
					me.recordPrescr(rec, function(){me.checkRecordPrescr(cbFn)});
				}
			});
			MedServiceFlag = '';
			// После того, как отправили все запросы на запись, продвигаем прогресс
			me.checkRecordPrescr(cbFn, true);
		}
		else {
			if(view.collapsed){
				me.showToast('Необходимо загрузить назначения');
				me.loadGrids();
				view.expand();
			} else {
				me.showToast('Назначений, доступных для записи, не найдено');
			}
		}
	},
	checkRecordPrescr: function (cbFn) {
		if(this.recordPrescrCount && this.recordPrescrCount > 1){
			this.recordPrescrCount --;
			var val = 1 - (this.recordPrescrCount / this.allRecordPrescrCount);
			Ext6.MessageBox.updateProgress(val, Math.round(100 * val) + '% завершено');
		}
		else{
			Ext6.MessageBox.updateProgress(1,'100% завершено');
			delete this.recordPrescrCount;
			delete this.allRecordPrescrCount;
			cbFn();
		}
	},
	recordPrescr: function (rec, cbFn) {
		var me = this;
		var PrescriptionType_Code = me.getPrescriptionType_Code(rec);
		var withResource = false;
		if (PrescriptionType_Code == 12) {
			withResource = true;
		}

		Ext6.Ajax.request({
			url: '/?c=MedService&m=getTimetableNoLimitWithMedService',
			callback: function(opt, success, response) {

				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						me.recordPerson(null,rec,response_obj,function(){cbFn()})
					}
					else
						cbFn();
				}
			},
			params: {
				UslugaComplex_id: rec.get('UslugaComplex_id'),
				MedService_id: rec.get('MedService_id'),
				PrescriptionType_Code: PrescriptionType_Code
			}
		});
	},
	recordPerson: function(time_id, rec, data, cbFn) {

		// записываем пациента
		var me = this;
		var params = rec.getData();
		params.PrescriptionType_Code = me.getPrescriptionType_Code(rec);
		params = Ext6.apply(params, data);
		/*params.TimetableMedService_id = data.TimetableMedService_id;
		params.TimetableMedService_begTime = data.TimetableMedService_begTime;
		params.ttms_MedService_id = data.ttms_MedService_id;
		params.TimetableResource_id = data.TimetableResource_id;
		params.TimetableResource_begTime = data.TimetableResource_begTime;
		params.Resource_id = data.Resource_Name;
		params.Resource_Name = data.ttr_Resource_id;
		params.ttr_Resource_id = data.ttr_Resource_id;
		params.Lpu_id = data.Lpu_id;
		params.LpuUnit_id = data.LpuUnit_id;
		params.LpuSection_id = data.LpuSection_id;
		params.LpuSectionProfile_id = data.LpuSectionProfile_id;
		params.UslugaComplexMedService_id = data.UslugaComplexMedService_id;*/
		//params.MedService_id = rec.get('MedService_id');
		params.onSaveEvnDirection = function() {
			cbFn();
		};
		me.saveEvnDirection(params);
	},
	showToast: function(s) {
		Ext6.toast({
			html: s,
			closable: false,
			align: 'br',
			slideInDuration: 400
		});
	},
	saveEvnDirection: function(prescrParams) {
		log('saveEvnDirection', prescrParams);
		var me = this;
		var view = me.getView();

		prescrParams.Person_Surname = this.Person_Surname;
		prescrParams.Person_Firname = this.Person_Firname;
		prescrParams.Person_Secname = this.Person_Secname;
		prescrParams.Person_Birthday = this.Person_Birthday;
		prescrParams.fdata =  me.data; // данные с формы
		prescrParams.fdata.Diag_id = me.getDiagId() || null;

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
				onFailure: function(code,answer){
					//view.unmask();
					if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection(answer);
					}
				},
				callback: function(responseData, realResponseData){
					//view.unmask();
					/*if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection();
					}*/
				},
				onCancel: function(){
					//view.unmask();
					if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection();
					}
				},
				onCancelQueue: function(evn_queue_id, callback) {
					//view.unmask();
					if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection();
					}
				}
			};

			if (prescrParams.PrescriptionType_Code == 11) {
				// нужен состав услуги (тесты)
				if (prescrParams.checked) {
					// если передан с формы назначения то используем его
					checked = prescrParams.checked;
				} else if (prescrParams.UslugaComposition) {//если сохранен в гриде - тоже воспользуемся
					checked = prescrParams.UslugaComposition;
				} else {
					// иначе тянем с сервера

					me.loadUslugaComplexComposition({
						UslugaComplexMedService_id: prescrParams.UslugaComplexMedService_id,
						UslugaComplex_id: prescrParams.UslugaComplex_id,
						Lpu_id: prescrParams.Lpu_id
					}, function(response_obj) {
						prescrParams.checked = [];
						if (response_obj.length > 0) {
							for (var i=0; i < response_obj.length; i++) {
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

			direction.EvnPrescr_id = prescrParams.EvnPrescr_id;
			direction.IgnoreCheckAlreadyHasRecordOnThisTime = 1;
			direction.StudyTarget_id = prescrParams.StudyTarget_id ? prescrParams.StudyTarget_id : 2;//1;
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

			var panel = (me.data.isKVS) ? null : view;

			if (prescrParams.TimetableMedService_id > 0) {
				//view.mask('Запись на бирку...');
				params.Timetable_id = prescrParams.TimetableMedService_id;
				params.order.TimetableMedService_id = prescrParams.TimetableMedService_id;
				direction['TimetableMedService_id'] = params.Timetable_id;
				sw.Promed.Direction.requestRecord({
					withoutErrorMsgBox: true,
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
						if(answer && answer.addingMsg)
							text += '<br>'+answer.addingMsg;
						sw4.showInfoMsg({
							panel: panel,
							type: 'success',
							text: text
						});
					},
					onFailure: params.onFailure,
					callback: params.callback,
					onCancel: params.onCancel,
					onCancelQueue: params.onCancelQueue
				});
			} else if (prescrParams.TimetableResource_id > 0) {
				//view.mask('Запись на бирку...');
				params.Timetable_id = prescrParams.TimetableResource_id;
				params.order.TimetableResource_id = prescrParams.TimetableResource_id;
				direction['TimetableResource_id'] = params.Timetable_id;
				sw.Promed.Direction.requestRecord({
					withoutErrorMsgBox: true,
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
							panel: panel,
							type: 'success',
							text: text
						});
					},
					onFailure: params.onFailure,
					callback: params.callback,
					onCancel: params.onCancel,
					onCancelQueue: params.onCancelQueue
				});
			} else {

				//view.mask('Постановка в очередь...');
				direction.UslugaComplex_did = direction.UslugaComplex_id;
				direction.MedService_did = direction.MedService_id;
				direction.Resource_did = direction.Resource_id;
				direction.LpuSectionProfile_did = direction.LpuSectionProfile_id;
				direction.EvnQueue_pid = direction.EvnDirection_pid;
				direction.MedStaffFact_id = null;
				direction.Prescr = "Prescr";
				sw.Promed.Direction.requestQueue({
					withoutErrorMsgBox: true,
					params: direction,
					loadMask: params.loadMask,
					windowId: params.windowId,
					onSaveQueue: function(data) {
						if (typeof prescrParams.onSaveEvnDirection == 'function') {
							prescrParams.onSaveEvnDirection(data);
						}
						var text = 'Пациент поставлен в очередь';
						if(data && data.addingMsg)
							text += '<br>'+data.addingMsg;
						sw4.showInfoMsg({
							panel: panel,
							type: 'success',
							text: text
						});
					},
					onFailure: params.onFailure,
					callback: params.callback
				});
			}
		});
	},
	loadUslugaComplexComposition: function(params, callback) {
		var me = this;
		var view = me.getView();

		//view.mask('Получение состава услуги...');
		Ext6.Ajax.request({
			params: {
				UslugaComplexMedService_pid: params.UslugaComplexMedService_id,
				MedService_pid: params.MedService_id,
				UslugaComplex_pid: params.UslugaComplex_id,
				Lpu_id: params.Lpu_id
			},
			callback: function(options, success, response) {
				//view.unmask();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					callback(response_obj);
				}
			},
			url: '/?c=MedService&m=loadCompositionMenu'
		});
	},
	printAllEvnPLPrescr: function () {
		this.openPrintDoc('/?c=EvnPrescr&m=printEvnPrescrList&Evn_pid=' + this.data.Evn_id);
	},
	printOneDirectionLabResearch: function() {
		this.openPrintDoc('/?c=EvnPrescr&m=printLabDirections&Evn_id=' + this.data.Evn_id);
	},
	openPrintDoc: function(url)
	{
		window.open(url, '_blank');
	},
	/**
	 * @param g
	 * @param cbFn
	 * @param target - либо кнопка "Добавить", либо tool на заголовке панели назначений
	 * @param packet_id - если "привет-пакет" значит из пакентных назначений держит путь
	 */
	openQuickSelectWindow: function(g,cbFn,target,packet_id){
		var cntr = this,
			view = this.getView();
		if(!g || Ext6.isEmpty(g.objectPrescribe)) return false;
		var selectPanel = cntr.getPanelNameByObjectPrescribe(g.objectPrescribe);
		if(!selectPanel) return false;
		var arrGrids = ['EvnPrescrRegime','EvnPrescrDiet','RegimeData','DietData', 'EvnPrescrVaccination'];

		if (inlist(g.objectPrescribe, arrGrids)){
			getWnd(selectPanel).show({
				parentPanel: view,
				PacketPrescr_id: packet_id,
				Evn_id: cntr.data.Evn_id,
				Diag_id: cntr.getDiagId(),
				MedPersonal_id: cntr.data.MedPersonal_id,
				Person_id: cntr.data.Person_id,
				PersonEvn_id: cntr.data.PersonEvn_id,
				Server_id: cntr.data.Server_id,
				callback: function() {
					//getWnd(g.selectPanel).hide();
					if(cbFn && typeof cbFn == 'function'){
						cbFn();
					} else {
						sw4.showInfoMsg({
							panel: view,
							type: 'success',
							text: 'Назначение сохранено'
						});
						var arr = [g.objectPrescribe];
						cntr.loadGrids(arr);
						// ну сохранили и сохранили :)
					}

				}
			});
		}
		else {
			view[selectPanel].show({
				target: target,
				align: 'tr-br?',
				force: true,
				objectPrescribe: g.objectPrescribe,
				callback: cbFn,
				PacketPrescr_id: packet_id,
				Person_id: cntr.data.Person_id,
				checkPersonPrescrTreat: true //проверка только здесь
			});
		}
	},
	/**
	 * @param options
	 * @param wnd
	 * @returns {boolean}
	 */
	saveEvnPrescr: function(options,wnd) {
		var me = this,
			view = me.getView(),
			save_url = null,
			prescr_code;
		//var rec = options.rec;
		//var MedService_id = rec.get('MedService_id');

		var params = {
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			parentEvnClassSysNick: "EvnVizitPL",
			DopDispInfoConsent_id: '',
			StudyTarget_id: '2', // Тип
			MedService_id: options.MedService_id,
			UslugaComplex_id: options.UslugaComplex_id,
			MedService_pzmid: ''
		};
		var onSaveEvnPrescr = function(response,prescr_code){
			if (typeof options.callback == 'function') {
				options.callback();
			}

			if(options.withDate){
				if (response && (response.EvnPrescrLabDiag_id || response.EvnPrescrProc_id0 || response.EvnPrescrConsUsluga_id || response.EvnPrescrFuncDiag_id)) {
					me.loadGrids([prescr_code]);
					me.onLoadStoresFn = function(){
						var prescr_id = response.EvnPrescrLabDiag_id || response.EvnPrescrProc_id0 || response.EvnPrescrConsUsluga_id || response.EvnPrescrFuncDiag_id;
						var grid = me.getGridByObject(prescr_code);
						var rec = grid.getStore().findRecord('EvnPrescr_id', prescr_id);
						me.openSpecification(prescr_code,grid,rec);
					}
				}

			} else {
				if (!options.withoutInfoMsg) {
					sw4.showInfoMsg({
						panel: view,
						type: 'warning',
						text: 'Услуга добавлена. Требуется запись.'
					});
				}
				if(view.collapsed){
					me.loadGrids();
					view.expand();
				} else {
					me.loadGrids([prescr_code]);
				}
			}
		};
		switch (options.PrescriptionType_Code) {
			case 6:
				var date = new Date();


				var formParams = {
					EvnCourseProc_id: null,
					EvnCourseProc_pid: me.data.Evn_id,
					PersonEvn_id: params.PersonEvn_id,
					Server_id: params.Server_id,
					MedPersonal_id: me.data.MedPersonal_id,
					LpuSection_id: me.data.LpuSection_id,
					MedService_id: params.MedService_id,
					parentEvnClass_SysNick: params.parentEvnClassSysNick,
					UslugaComplex_id: params.UslugaComplex_id,
					StudyTarget_id: params.StudyTarget_id,
					EvnCourseProc_setDate: date.format('d.m.Y'),
					EvnCourseProc_setTime: date.format('H:i')
				};

				/*var callback = function(response) {
					if (response && response.EvnPrescrProc_id0) {

						if (typeof options.callback == 'function') {
							options.callback();
						}
						if (!options.withoutInfoMsg) sw4.showInfoMsg({
							panel: me,
							type: 'warning',
							text: 'Услуга добавлена. Требуется запись.'
						});
					}

				};*/
				prescr_code = 'EvnCourseProc';
				getWnd('swEvnCourseProcEditWindow').show({
					formParams: formParams,
					callback: onSaveEvnPrescr
				});
				break;
			case 7:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrOperBlock';
				prescr_code = 'EvnPrescrOperBlock';
				params.UslugaComplex_id = options.UslugaComplex_id;
				break;
			case 11:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
				prescr_code = 'EvnPrescrLabDiag';
				params.UslugaComplex_id = options.UslugaComplex_id;
				params.EvnPrescrLabDiag_uslugaList = options.UslugaComplex_id;
				params.UslugaComplexMedService_pid = options.UslugaComplexMedService_id;
				break;
			case 12:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
				prescr_code = 'EvnPrescrFuncDiag';
				params.EvnPrescrFuncDiag_uslugaList = options.UslugaComplex_id;
				break;
			case 13:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
				prescr_code = 'EvnPrescrConsUsluga';
				params.UslugaComplex_id = options.UslugaComplex_id;
				break;
		}

		if (!save_url) {
			return false;
		}

		params[prescr_code +'_id'] = null;
		params[prescr_code +'_pid'] = me.data.Evn_id;
		params[prescr_code +'_IsCito'] = 'off';
		params[prescr_code +'_setDate'] = me.data.Evn_setDate;
		params[prescr_code +'_Descr'] = '';

		view.mask('Сохранение назначения');
		if (options.PrescriptionType_Code == 11) {
			Ext6.Ajax.request({
				url: '/?c=MedService&m=loadCompositionMenu',
				success: function(response) {
					var list = [];
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							for (var i = 0; i < response_obj.length; i++) {
								list.push(response_obj[i].UslugaComplex_id);
							}
						}
					}
					if (list.length > 0) {
						params.EvnPrescrLabDiag_uslugaList = list.toString();
						params.EvnPrescrLabDiag_CountComposit = list.length;
					}
					Ext6.Ajax.request({
						url: save_url,
						callback: function(opt, success, response) {
							//if(options.withDate)
							wnd.hide();
							if (response && response.responseText) {
								var response_obj = Ext6.JSON.decode(response.responseText);
								onSaveEvnPrescr(response_obj,prescr_code);
							}
							view.unmask();
						},
						params: params
					});
				},
				params: {
					UslugaComplexMedService_pid: options.UslugaComplexMedService_id,
					MedService_pid: options.MedService_id,
					UslugaComplex_pid: options.UslugaComplex_id,
					Lpu_id: options.Lpu_id
				}
			});
		} else
			Ext6.Ajax.request({
				url: save_url,
				callback: function(opt, success, response) {
					//if(options.withDate)
					wnd.hide();
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						onSaveEvnPrescr(response_obj,prescr_code);
					}
					view.unmask();
				},
				params: params
			});
	},
	openAllUslugaInputWnd: function (conf) {
		if(!conf.objectPrescribe) return false;
		var cntr = this,
			view = cntr.getView(),
			grid = null,
			evnPLForm = view.ownerPanel,
			PersonInfoPanel;
		if (evnPLForm) {
			var personEmkWindow = evnPLForm.ownerWin;
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		} else {
			PersonInfoPanel = view.PersonInfoPanel;
		}
		cntr.updateData();
		if (!cntr.data.isKVS){
			grid = cntr.getGridByObject(conf.objectPrescribe);
		}
		getWnd('swEvnPrescrAllUslugaInputWnd').show({
			parentPanel: view,
			evnPrescrCntr: cntr,
			grid: grid,
			objectPrescribe: conf.objectPrescribe,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			showFirstItem: true,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId(),
			PersonInfoPanel: PersonInfoPanel,
			PacketPrescr_id: conf.PacketPrescr_id ? conf.PacketPrescr_id : null,
			isKVS: cntr.data.isKVS,
			callback: function () {
				if(conf.callback && typeof conf.callback === 'function')
					conf.callback();

				cntr.loadGrids();
			},
			onLoadForm: function(){
			}
		});
	},
	openEvnPrescrTreatCreateWnd: function (selectedDrug) {
		if(!selectedDrug) return false;
		var cntr = this,
			view = cntr.getView();
		cntr.updateData();
		var form = 'swEvnPrescrTreatCreateWindow';
		if(selectedDrug.PacketPrescr_id && getGlobalOptions().client && getGlobalOptions().client === 'ext2'){
			form = 'EvnCourseTreatInPacketWindowExt2';
		}
		getWnd(form).show({
			rec: selectedDrug,
			PacketPrescr_id: selectedDrug.PacketPrescr_id,
			parentPanel: view,
			parentCntr: cntr,
			userMedStaffFact: cntr.data.userMedStaffFact,
			Person_id: cntr.data.Person_id,
			PersonEvn_id: cntr.data.PersonEvn_id,
			Server_id: cntr.data.Server_id,
			Evn_id: cntr.data.Evn_id,
			Evn_setDate: cntr.data.Evn_setDate,
			LpuSection_id: cntr.data.LpuSection_id,
			MedPersonal_id: cntr.data.MedPersonal_id,
			showFirstItem: true,
			Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId(),
			callback: function () {
				if(selectedDrug.cbFn && typeof selectedDrug.cbFn === 'function')
					selectedDrug.cbFn();
				else
					cntr.loadGrids();
			},
			onLoadForm: function(){
			}
		});
	},
	getPanelNameByObjectPrescribe: function(objectPrescribe){
		var panelName = false;
		switch(objectPrescribe){
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnCourseProc':
			case 'EvnPrescrOperBlock':
			case 'LabDiagData':
			case 'FuncDiagData':
			case 'ConsUslData':
			case 'ProcData':
			case 'OperBlockData':
				panelName = 'UslugaSelectPanel';
				break;
			case 'EvnPrescrDiet':
			case 'DietData':
				panelName = 'swDietCreateWindow';
				break;
			case 'EvnPrescrRegime':
			case 'RegimeData':
				panelName = 'swRegimeCreateWindow';
				break;
			case 'EvnCourseTreat':
			case 'DrugData':
				panelName = 'DrugSelectPanel';
				break;
			case 'EvnPrescrVaccination':
				panelName = 'swVaccinationWindow';
				break;
		}
		return panelName;
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
	/**
	 * @param options
	 * @param wnd
	 * @returns {boolean}
	 */
	addPrescrToPacket: function(options,wnd) {
		var me = this,
			view = me.getView(),
			save_url = '/?c=PacketPrescr&m=createPacketPrescrUsl',
			prescr_code;
		me.updateData();

		var params = {
			PacketPrescr_id: options.PacketPrescr_id,
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			StudyTarget_id: '2', // Тип
			MedService_id: options.MedService_id,
			UslugaComplex_id: options.UslugaComplex_id
		};

		var onSaveEvnPrescr = function(){
			if (typeof options.callback == 'function') {
				options.callback();
			}

			sw4.showInfoMsg({
				panel: view,
				type: 'success',
				text: 'Пакет обновлен.'
			});
		};

		switch (options.PrescriptionType_Code) {
			case 6:
				var date = new Date();
				save_url = '/?c=PacketPrescr&m=createPacketPrescrProc';
				params.StudyTarget_id = 1;
				var formParams = {
					EvnCourseProc_id: null,
					EvnCourseProc_pid: me.data.Evn_id,
					PersonEvn_id: params.PersonEvn_id,
					Server_id: params.Server_id,
					MedPersonal_id: me.data.MedPersonal_id,
					LpuSection_id: me.data.LpuSection_id,
					MedService_id: params.MedService_id,
					parentEvnClass_SysNick: params.parentEvnClassSysNick,
					UslugaComplex_id: params.UslugaComplex_id,
					StudyTarget_id: params.StudyTarget_id,
					EvnCourseProc_setDate: date.format('d.m.Y'),
					EvnCourseProc_setTime: date.format('H:i')
				};
				params.PrescriptionType_Code = 'Proc';
				prescr_code = 'EvnCourseProc';
				/*getWnd('swEvnCourseProcEditWindow').show({
					formParams: formParams,
					callback: onSaveEvnPrescr
				});*/
				break;
			case 7:
				params.PrescriptionType_Code = 'Oper';
				prescr_code = 'EvnPrescrOperBlock';
				params.UslugaComplex_id = options.UslugaComplex_id;
				break;
			case 11:
				params.PrescriptionType_Code = 'LabDiag';
				prescr_code = 'EvnPrescrLabDiag';
				params.UslugaComplex_id = options.UslugaComplex_id;
				params.EvnPrescrLabDiag_uslugaList = options.UslugaComplex_id;
				params.UslugaComplexMedService_pid = options.UslugaComplexMedService_id;
				break;
			case 12:
				params.PrescriptionType_Code = 'FuncDiag';
				prescr_code = 'EvnPrescrFuncDiag';
				params.EvnPrescrFuncDiag_uslugaList = options.UslugaComplex_id;
				break;
			case 13:
				params.PrescriptionType_Code = 'Cons';
				prescr_code = 'EvnPrescrConsUsluga';
				params.UslugaComplex_id = options.UslugaComplex_id;
				break;
		}

		view.mask('Сохранение назначения');
		if (options.PrescriptionType_Code == 11) {
			Ext6.Ajax.request({
				url: '/?c=MedService&m=loadCompositionMenu',
				success: function(response) {
					var list = [];
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							for (var i = 0; i < response_obj.length; i++) {
								list.push(response_obj[i].UslugaComplex_id);
							}
						}
					}
					if (list.length > 0) {
						params.EvnPrescrLabDiag_uslugaList = list.toString();
						params.EvnPrescrLabDiag_CountComposit = list.length;
					}
					Ext6.Ajax.request({
						url: save_url,
						callback: function(opt, success, response) {
							//if(options.withDate)
							wnd.hide();
							if (response && response.responseText) {
								onSaveEvnPrescr();
							}
							view.unmask();
						},
						params: params
					});
				},
				params: {
					UslugaComplexMedService_pid: options.UslugaComplexMedService_id,
					UslugaComplex_pid: options.UslugaComplex_id,
					Lpu_id: options.Lpu_id
				}
			});
		} else
			Ext6.Ajax.request({
				url: save_url,
				callback: function(opt, success, response) {
					wnd.hide();
					if (response && response.responseText) {
						onSaveEvnPrescr();
					}
					view.unmask();
				},
				params: params
			});
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
	/**
	 * Создание пунктов меню для новой ЭМК по списку необходимых типов
	 * @param g - грид, для которого вызывается меню
	 * @param target - куда вставлять меню
	 * @param addToMe - добавлять ли пункт меню "Записать к себе"
	 */
	openDirMenu: function (g, target, addToMe)// openQuickSelectWindow
	{
		var cntr = this,
			view = cntr.getView(),
			dirTypeCodeExcList = [];
		cntr.updateData();

		if(view.dirTempMenu){
			view.dirTempMenu.removeAll();
		} else {
			view.dirTempMenu = Ext6.create('Ext6.menu.Menu', {
				userCls: 'menuWithoutIcons',
				items: [],
				listeners:{
					hide: function () {
						//view.dirTempMenu.setStyle('visibility','');
					}
				}
			});
		}

		if(addToMe){
			view.dirTempMenu.add({
				text: 'Записать к себе',
				handler: function() {
					cntr.openEvnDirectionEditWindow('addtome');
				}
			});
			view.dirTempMenu.add('-');
		}

		if(g && g.dirTypeCodeExcList){
			g.dirTypeCodeExcList.forEach(function(dirType){
				// Направление на ЭКО и на перенос эмбрионов только для женщин старше 18 лет
				var isOnlyFemale = dirType.inlist(['27','28']);
				if(!isOnlyFemale || (isOnlyFemale && cntr.checkIsFemale())){
					dirTypeCodeExcList.push(dirType);
				}
			});
		} else {
			// Для кнопки в заголовке раздела "Назначения"
			dirTypeCodeExcList = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '12', '13', '15', '25', '26', '29', '30'];
			if (getRegionNick() == 'buryatiya') {
				dirTypeCodeExcList.push('18');
			}
			if (cntr.checkIsFemale()){
				dirTypeCodeExcList.push('27', '28');
			}
		}
		sw.Promed.Direction.createDirTypeMenuByList({
			excList: dirTypeCodeExcList,
			id: 'EMKDirTypeListMenu',
			onSelect: function(rec) {
				cntr.createDirection(rec, this.excList);
			},
			onCreate: function() {
				// пока ничего делать не нужно, но это не точно
			},
			menu: view.dirTempMenu
		});
		if (view.dirTempMenu)
			view.dirTempMenu.showBy(target);
		/*if (view.dirTempMenu.hidden === false)
			view.dirTempMenu.setStyle('visibility', 'visible');*/
	},
	/**
	 * пациент - женщина старше 18 лет
	 * @returns {*|boolean}
	 */
	checkIsFemale: function() {
		var cntr = this,
			view = cntr.getView(),
			evnPLForm = view.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel,
			age = PersonInfoPanel.getFieldValue('Person_Age'),
			sex_id = PersonInfoPanel.getFieldValue('Sex_id');
		return ((age && age > 18) && sex_id === '2');
	},
	createDirection: function(dir_type_rec, excList, dirData) {
		var me = this,
			cntr = this,
			view = cntr.getView(),
			evnPLForm = view.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		cntr.updateData();

		var personData = {};
		personData.Person_id = cntr.data.Person_id;
		personData.Server_id = cntr.data.Server_id;
		personData.PersonEvn_id = cntr.data.PersonEvn_id;

		if (PersonInfoPanel && PersonInfoPanel.getFieldValue('Person_Surname')) {
			personData.Person_Birthday = PersonInfoPanel.getFieldValue('Person_Birthday');
			personData.Person_Surname = PersonInfoPanel.getFieldValue('Person_Surname');
			personData.Person_Firname = PersonInfoPanel.getFieldValue('Person_Firname');
			personData.Person_Secname = PersonInfoPanel.getFieldValue('Person_Secname');
			personData.Person_IsDead = PersonInfoPanel.getFieldValue('Person_IsDead');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		if (dir_type_rec.get('DirType_Code') == '18') {
			// На консультацию в другую МИС
			var Person_Fio = personData.Person_Surname + ' ' + personData.Person_Firname + ' ' + personData.Person_Secname;
			getWnd('swDirectionMasterMisRbWindow').show({
				personData: {
					Person_Fio: Person_Fio,
					Person_id: cntr.data.Person_id
				}
			});
			return true;
		}

		var directionData = {
			EvnDirection_pid: cntr.data.Evn_id || null
			,DopDispInfoConsent_id: me.DopDispInfoConsent_id || null
			,Diag_id: cntr.data.evnParams ? cntr.data.evnParams.Diag_id : cntr.getDiagId()
			,DirType_id: dir_type_rec.get('DirType_id')
			,MedService_id: me.data.userMedStaffFact.MedService_id
			,MedStaffFact_id: me.data.userMedStaffFact.MedStaffFact_id
			,MedPersonal_id: me.data.userMedStaffFact.MedPersonal_id
			,LpuSection_id: me.data.userMedStaffFact.LpuSection_id
			,ARMType_id: me.data.userMedStaffFact.ARMType_id
			,Lpu_sid: getGlobalOptions().lpu_id
			,withDirection: true
		};
		directionData.Person_id = personData.Person_id;
		directionData.PersonEvn_id = personData.PersonEvn_id;
		directionData.Server_id = personData.Server_id;

		var onDirection = function () {
			me.loadGrids(['EvnDirection']);
		};

		if (dir_type_rec.get('DirType_Code') == 30) {
			// Направление внешнюю лабораторию по КВИ
			directionData.EvnDirectionCVI_pid = me.data.Evn_id;
			directionData.EvnDirectionCVI_setDate = me.data.Evn_setDate;
			directionData.Diag_id = cntr.getDiagId();
			getWnd('swEvnDirectionCviEditWindow').show({
				action: 'add',
				formParams: directionData,
		 		callback: onDirection
			});
			return true;
		}

		if (dir_type_rec.get('DirType_Code') == 23) {
			checkEvnPrescrMseExists({
				Person_id: personData.Person_id,
				callback: function() {
					createEvnPrescrMse({
						personData: personData,
						userMedStaffFact: me.data.userMedStaffFact,
						directionData: directionData,
						callback: onDirection
					})
				}.createDelegate(this)
			});
			return true;
		}

		if (dir_type_rec.get('DirType_Code') === 15 && getRegionNick() != 'kz') {
				// окно мастера направлений #101026
				getWnd('swDirectionMasterWindow').show({
					type: 'HTM',
					dirTypeData: { DirType_id: 19, DirType_Code: 15, DirType_Name: 'На высокотехнологичную помощь' },
					personData: {
						Person_id:	personData.Person_id,
						PersonEvn_id:	personData.PersonEvn_id,
						Server_id:	personData.Server_id,
					},
					directionData: {
						action: 'add',
						EvnDirectionHTM_pid: me.data.Evn_id || null,
						EvnDirection_pid: me.data.Evn_id || null,
						Person_id: personData.Person_id,
						PersonEvn_id: personData.PersonEvn_id,
						Server_id: personData.Server_id,
						// MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
						LpuSection_id: directionData.LpuSection_id,
						LpuSection_did: directionData.LpuSection_id,
						withCreateDirection: false,
						// ARMType: 'htm',
					},
					MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
					onSave: onDirection
				});
				return true;
		}

		if (getRegionNick().inlist(['perm', 'vologda']) && dir_type_rec.get('DirType_Code') == 8) {
			createEvnPrescrVK({
				personData: personData,
				userMedStaffFact: me.data.userMedStaffFact,
				directionData: directionData,
				win: view,
				callback: onDirection
			});
			return true;
		} else if (dir_type_rec.get('DirType_Code').inlist([8,16])) {
			// Направление на ВК или МСЭ
			getWnd('swUslugaComplexMedServiceListWindow').show({
				userMedStaffFact: me.data.userMedStaffFact,
				personData: personData,
				dirTypeData: dir_type_rec.data,
				directionData: directionData,
				onDirection: onDirection
			});
			return true;
		}

		if (getRegionNick() == 'msk') {
			directionData.CVIConsultRKC_id = dirData.CVIConsultRKC_id || null;
			directionData.RepositoryObserv_sid = dirData.RepositoryObserv_sid || null;
			directionData.isRKC = dirData.isRKC || null;
		}
		
		if (13 == dir_type_rec.get('DirType_Code')) {
			// Направление на удаленную консультацию
			directionData.Lpu_did = me.data.userMedStaffFact.Lpu_id;
			getWnd('swEvnDirectionEditWindowExt6').show({
				action: 'add',
				disableQuestionPrintEvnDirection: true,
				callback: onDirection,
				Person_id: personData.Person_id,
				Server_id: personData.Server_id,
				PersonEvn_id: personData.PersonEvn_id,
				Person_IsDead: personData.Person_IsDead,
				Person_Firname: personData.Person_Firname,
				Person_Secname: personData.Person_Secname,
				Person_Surname: personData.Person_Surname,
				Person_Birthday: personData.Person_Birthday,
				formParams: directionData,
				/*	formParams: {
						Person_id: personData.Person_id,
						PersonEvn_id: personData.PersonEvn_id,
						Server_id: personData.Server_id,
						DirType_id: 17,
						//~ EvnDirection_IsReceive: 2,
						ARMType_id: this.userMedStaffFact.ARMType_id,
						Lpu_did: this.userMedStaffFact.Lpu_id
					}*/
			});
			return true;
		}

		if (5 == dir_type_rec.get('DirType_Code')) {
			// Направление на экстренную госпитализацию
			getWnd('swEvnDirectionEditWindow').show({
				action: 'add',
				EvnDirection_id: null,
				callback: onDirection,
				Person_id: personData.Person_id,
				Person_Firname: personData.Person_Firname,
				Person_Secname: personData.Person_Secname,
				Person_Surname: personData.Person_Surname,
				Person_Birthday: personData.Person_Birthday,
				personData: {
					Person_id: personData.Person_id,
					Server_id: personData.Server_id,
					PersonEvn_id: personData.PersonEvn_id,
					Person_Firname: personData.Person_Firname,
					Person_Secname: personData.Person_Secname,
					Person_Surname: personData.Person_Surname,
					Person_Birthday: personData.Person_Birthday
				},
				formParams: {
					EvnDirection_pid: me.data.Evn_id
					,Diag_id: cntr.getDiagId()
					,DirType_id: dir_type_rec.get('DirType_id')
					,MedService_id: me.data.userMedStaffFact.MedService_id
					,MedPersonal_id: me.data.userMedStaffFact.MedPersonal_id
					,LpuSection_id: me.data.userMedStaffFact.LpuSection_id
					,MedStaffFact_id: me.data.userMedStaffFact.MedStaffFact_id
					,Lpu_did: getGlobalOptions().lpu_id
					,Lpu_sid: getGlobalOptions().lpu_id
				}
			});
			return true;
		}

		if (7 == dir_type_rec.get('DirType_Code')) {
			// На патологогистологическое исследование
			directionData.EvnDirectionHistologic_pid = me.data.Evn_id || null;
			getWnd('swEvnDirectionHistologicEditWindow').show({
				action: 'add',
				formParams: directionData,
				callback: onDirection,
				userMedStaffFact: me.data.userMedStaffFact
			});
			return true;
		}

		if (29 == dir_type_rec.get('DirType_Code') && evnPLForm && evnPLForm.formPanel) {
			// На цитологическое диагностическое исследование
			directionData.EvnDirectionCytologic_pid = me.data.Evn_id || null;
			directionData.Diag_spid = null;
			directionData.EvnVizitPL_IsZNO = null;
			var formValues = evnPLForm.formPanel.getForm().getValues();
			if(formValues && formValues.EvnVizitPL_IsZNO && formValues.EvnVizitPL_IsZNO == 2){
				if(formValues.Diag_spid) {
					directionData.Diag_id = formValues.Diag_spid;
					directionData.EvnVizitPL_IsZNO = formValues.EvnVizitPL_IsZNO;
				}else{
					sw.swMsg.alert(langs('Сообщение'), 'Нельзя создать направление. Не указано подозрение на диагноз');
					return false;
				}
			}
			getWnd('swEvnDirectionCytologicEditWindow').show({
				action: 'add',
				formParams: directionData,
				callback: onDirection,
				callFromEmk: true,
				curentMedStaffFactByUser: me.data.userMedStaffFact
			});
			return true;
		}

		if (9 == dir_type_rec.get('DirType_Code')) {
			// Направление на исследование в другую МО
			var directionDataOtherMO = {
				ext6: true,
				userMedStaffFact: Ext.apply({},  me.data.userMedStaffFact),
				person: Ext.apply({}, personData),
				direction: Ext.apply({}, directionData),
				callback: function(data){
					onDirection();
					if (data.EvnDirection_id) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: langs('Вывести направление на печать?'),
							title: langs('Вопрос'),
							icon: Ext.MessageBox.QUESTION,
							fn: function(buttonId){
								if (buttonId === 'yes') {
									sw.Promed.Direction.print({
										EvnDirection_id: data.EvnDirection_id
									});
								}
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				mode: 'nosave',
				windowId: this.getId()
			};
			directionDataOtherMO.direction.LpuUnitType_SysNick = 'polka';
			directionDataOtherMO.direction.LpuUnit_did = null;
			directionDataOtherMO.direction.isNotForSystem = true;

			sw.Promed.Direction.queuePerson(directionDataOtherMO);

			return true;
		}

		if (!excList) {
			excList = [];
		}
		excList.push('8');
		excList.push('5');
		excList.push('13');

		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact:  me.data.userMedStaffFact,
			personData: personData,
			dirTypeData: dir_type_rec.data,
			dirTypeCodeExcList: excList,
			directionData: directionData,
			onHide: onDirection
		});

		return true;
	},
	openEvnDirectionEditWindow: function(action, rec) {
		var cntr = this,
			view = cntr.getView(),
			evnPLForm = view.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		cntr.updateData();
		var formParams = {},
			onEvnDirectionSave = function(data) {
				if (!data || !data.evnDirectionData) {
					return false;
				}

				cntr.loadGrids(['EvnDirection'])
			};

		if ( action == 'add' || action == 'addtome' ) {
			// запись пациента к другому врачу с выпиской электр.направления

			var my_params = new Object({
				EvnDirection_id: 0,
				EvnDirection_pid: cntr.data.Evn_id,
				Diag_id: cntr.getDiagId(),
				PersonEvn_id: cntr.data.PersonEvn_id,
				Person_id: cntr.data.Person_id,
				Server_id: cntr.data.Server_id,
				UserMedStaffFact_id: cntr.data.userMedStaffFact.MedStaffFact_id,
				userMedStaffFact: cntr.data.userMedStaffFact,
				formMode: 'vizit_PL'
			});

			if (PersonInfoPanel && PersonInfoPanel.getFieldValue('Person_Surname')) {
				my_params.Person_Birthday = PersonInfoPanel.getFieldValue('Person_Birthday');
				my_params.Person_Surname = PersonInfoPanel.getFieldValue('Person_Surname');
				my_params.Person_Firname = PersonInfoPanel.getFieldValue('Person_Firname');
				my_params.Person_Secname = PersonInfoPanel.getFieldValue('Person_Secname');
			} else {
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
				return false;
			}

			my_params.personData = {
				PersonEvn_id: cntr.data.PersonEvn_id,
				Person_id: cntr.data.Person_id,
				Server_id: cntr.data.Server_id
			};

			if (action == 'addtome') {
				my_params.isThis = true;
				my_params.type = 'HimSelf';
			}
			my_params.fromEmk = true;

			my_params.onHide = function(){
				onEvnDirectionSave({
					evnDirectionData: {
						EvnDirection_id: 0,
						EvnDirection_pid: cntr.data.Evn_id
					}
				});
			};
			if (action == 'addtome') {
				my_params.onClose = my_params.onHide;
				getWnd('swDirectionMasterWindow').show(my_params);
			} else {
				getWnd('swMPRecordWindow').show(my_params);
			}
		}
		else
		{
			var EvnDirection_id = rec.get('EvnDirection_id');
			if (!EvnDirection_id) {
				return false;
			}
			if (!rec) {
				return false;
			}

			if (rec.get('EvnPrescrVK_id')) {
				getWnd('swEvnPrescrVKWindow').show({
					EvnPrescrVK_id: rec.get('EvnPrescrVK_id'),
					action: 'view'
				});
				return true;
			}

			formParams = {
				EvnDirection_id: EvnDirection_id,
				Person_id: cntr.data.Person_id,
				Server_id: cntr.data.Server_id,
				Lpu_gid: rec.get('Lpu_gid'),
				EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
				EvnDirectionCVI_id: rec.get('EvnDirectionCVI_id'),
				DirType_Code: rec.get('DirType_Code'),
				EvnDirectionHTM_id: rec.get('EvnDirectionHTM_id')
			};

			if (formParams.EvnDirectionCVI_id) {
				var params = {
					action: (formParams.Lpu_gid != getGlobalOptions().lpu_id) ? 'view' : action,
					EvnDirectionCVI_id: formParams.EvnDirectionCVI_id,
					Person_id: cntr.data.Person_id,
					Server_id: cntr.data.Server_id,
					callback: onEvnDirectionSave,
					onHide: Ext.emptyFn
				};
				getWnd('swEvnDirectionCviEditWindow').show(params);
				return true;
			}

			// если направление на МСЭ, открываем соответсвующую форму
			if (formParams.EvnPrescrMse_id) {
				action = (formParams.Lpu_gid == getGlobalOptions().lpu_id) ? 'edit' : 'view';
				var params = {
					EvnPrescrMse_id: formParams.EvnPrescrMse_id,
					Person_id: cntr.data.Person_id,
					Server_id: cntr.data.Server_id,
					onHide: Ext.emptyFn
				};
				getWnd('swDirectionOnMseEditForm').show(params);
				return true;
			}

			// если направление на ВМП, открываем соответсвующую форму
			if (formParams.EvnDirectionHTM_id) {
				var params = {
					action: action,
					EvnDirectionHTM_id: formParams.EvnDirectionHTM_id,
					Person_id: cntr.data.Person_id,
					Server_id: cntr.data.Server_id,
					onHide: Ext.emptyFn
				};
				getWnd('swDirectionOnHTMEditForm').show(params);
				return true;
			}

			var my_params = new Object({
				Person_id: cntr.data.Person_id,
				EvnDirection_id: formParams.EvnDirection_id,
				callback: onEvnDirectionSave,
				formParams: formParams,
				action: action
			});

			my_params.onHide = Ext.emptyFn;

			//зачем из Ext6 вызывать формы Ext2 ?
			if(rec.get('DirType_Code') === 7) {
				getWnd('swEvnDirectionHistologicEditWindow').show({
					"action": action,
					"formParams":{
						"EvnDirectionHistologic_id": formParams.EvnDirection_id,
						"Person_id": cntr.data.Person_id,
						"Server_id": cntr.data.Server_id
					}
				});
				//getWnd('swEvnDirectionHistologicEditWindow').show(my_params);
			} else if(rec.get('DirType_Code') === 29) {
				getWnd('swEvnDirectionCytologicEditWindow').show({
					"action": action,
					"formParams":{
						"EvnDirectionCytologic_id": formParams.EvnDirection_id,
						"Person_id": cntr.data.Person_id,
						"Server_id": cntr.data.Server_id
					}
				});
			} else {
				getWnd('swEvnDirectionEditWindowExt6').show(my_params);
				/*if (action === 'view' && rec.get('DirType_Code') === 9)
					getWnd('swEvnDirectionEditWindowExt6').show(my_params);
				else
					getWnd('swEvnDirectionEditWindow').show(my_params);*/
			}
			
		}
	},
	cancelEvnDirection: function(record, grid) {

		var cntr = this,
			view = cntr.getView(),
			evnPLForm = view.ownerPanel,
			personEmkWindow = evnPLForm.ownerWin,
			PersonInfoPanel = personEmkWindow.PersonInfoPanel;
		cntr.updateData();
		var EvnDirection_id = record.get('EvnDirection_id');
		if (EvnDirection_id) {
			if (!record) {
				return false;
			}
			if(record.get('timetable') && record.get('timetable_id')){
				record.set(record.get('timetable')+'_id', record.get('timetable_id'));
			}
			var isFunc, isLab;
			// Если отменяемый объект принадлежит исследованию по лаб. диагностике, запомним это
			// Это может быть список лаб. диагностики, либо сам объект относится к лаб. диагностике
			if((record.get('object') && record.get('object').inlist(['EvnPrescrLabDiag','EvnDirectionLabDiag']))
				|| (grid && grid.objectPrescribe && grid.objectPrescribe === 'EvnPrescrLabDiag'))
				isLab = true;
			// Если отменяемый объект принадлежит исследованию по инстр. диагностике, запомним это
			if((record.get('object') && record.get('object').inlist(['EvnPrescrFuncDiag','EvnDirectionFuncDiag']))
				|| (grid.objectPrescribe && grid.objectPrescribe === 'EvnPrescrFuncDiag'))
				isFunc = true;
			
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
					cntr.reloadLinkedGrids(grid,record.get('DirType_Code'));
				}
			};
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
			// Костыль по задаче 176700, чтобы направление могло отменяться, даже при отсутствии бирки
			// чтобы при включенном usePostgreLis осуществлялась отмена на postgre БД ЛИС и направление было найдено
			// проверка в этом файле promed/controllers/EvnDirection.php метод cancel
			if(isLab){
				params.formType = 'labdiag';
				params.DirType_id = 10;
			}
			if(isFunc){
				params.formType = 'funcdiag';
			}
			// @todo переделать этот срам, на обычную отмену направления
			/*if(isFunc){
				getWnd('swSelectDirFailTypeWindow').show({formType: 'funcdiag', LpuUnitType_SysNick: 'parka', onSelectValue: function(responseData) {
						if (!Ext.isEmpty(responseData.DirFailType_id)) {
							//grid.mask('Отмена направления на функциональную диагностику...');
							Ext.Ajax.request({
								params: {
									EvnDirection_id: EvnDirection_id,
									DirFailType_id: responseData.DirFailType_id,
									EvnComment_Comment: responseData.EvnComment_Comment
								},
								url: '/?c=EvnFuncRequest&m=cancelDirection',
								callback: function(options, success, response) {
									//grid.unmask();
									cntr.reloadLinkedGrids(grid,record.get('DirType_Code'));
								}
							});
						}
					}});
			} else {*/
				sw.Promed.Direction.cancel(params);
			//}
		}
	},
	reloadLinkedGrids: function(grid, DirType_Code, cbFn){
		var cntr = this,
			objectPrescribe,
			arrLinkedGrids = ['EvnPrescrLabDiag','EvnPrescrFuncDiag','EvnPrescrConsUsluga','EvnCourseProc',"EvnPrescrOperBlock"];
		if(grid && grid.objectPrescribe)
			objectPrescribe = grid.objectPrescribe;
		
		if(cbFn && typeof cbFn === 'function'){
			cntr.onLoadStoresFn = function () {
				cbFn()
			};
		}
		if(objectPrescribe && grid.objectPrescribe.inlist(arrLinkedGrids)){
			cntr.loadGrids([grid.objectPrescribe, 'swGridEvnDirectionCommon'])
		} else if(objectPrescribe === 'EvnDirection' && DirType_Code){
			switch(DirType_Code){
				case 9:
					arrLinkedGrids = ['EvnPrescrLabDiag','EvnPrescrFuncDiag'];
					break;
				case 10:
					arrLinkedGrids = ['EvnPrescrConsUsluga'];
					break;
				case 11:
					arrLinkedGrids = ['EvnCourseProc'];
					break;
			}
			arrLinkedGrids.push(grid.xtype);
			cntr.loadGrids(arrLinkedGrids);
		} else if(objectPrescribe) {
			cntr.loadGrids([objectPrescribe]);
		} else {
			cntr.loadGrids();
		}
	}
});