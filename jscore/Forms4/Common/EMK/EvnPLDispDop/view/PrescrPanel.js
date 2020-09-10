﻿/**
 * Панель назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnPLDispDop.view.PrescrPanel', {
	extend: 'swPanel',
	requires: [
		//'common.EMK.EvnPLDispDop.controller.PrescrController',
		//'common.EMK.EvnPLDispDop.store.PrescrStore',
	],
	collapsed: false,
	title: 'Назначения',
	allTimeExpandable: false,
	refId: 'EvnPLDispScreenPrescrPanel',
	alias: 'widget.EvnPLDispDop_PrescrPanel',
	//cls: 'evnPrescribePanel',
	addSpacer: false,
	confirmGrid: {},
	PersonInfoPanel: {},
	lastUpdater: '',
	lastUpdateDateTime: '',
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	listeners: {
		expand: function() {
			this.ownerPanel.AccordionPanel.collapseOtherPanels(this);
		}
	},
	months: [
		'января',
		'февраля',
		'марта',
		'апреля',
		'мая',
		'июня',
		'июля',
		'августа',
		'сентября',
		'октября',
		'ноября',
		'декабря'
	],
	//bodyPadding: 10,
	EvnPLDispDop13_id: null,
	PersonEvn_id: null,
	Server_id: null,
	setParams: function (data) {
		this.EvnPLDispDop13_id = data.EvnPLDispDop13_id;
		this.Person_id = data.Person_id;
		this.PersonEvn_id = data.PersonEvn_id;
		this.Server_id = data.Server_id;
		this.EvnClass_id = data.EvnClass_id;
		this.data = data;
	},
	loadPrescribes: function(records){
		var me = this;
		if(!records && me.confirmGrid){
			records = me.confirmGrid.getStore().getRange();
		}

		var UslugaComplexList = [];
		records.forEach(function(r){
			if(r.get('SurveyType_RecNotNeeded') != '1' && r.get('DopDispInfoConsent_IsAgree') /*&& r.get('SurveyType_Code') != 2*/ && !Ext6.isEmpty(r.get('UslugaComplex_id'))) {
				UslugaComplexList.push(r.get('UslugaComplex_id'));
			}
		});
		if(UslugaComplexList.length){
			me.setTitleCounter(UslugaComplexList.length);
			//EvnPLDispDop13_id: 730023881307390
			this.PrescrStore.proxy.extraParams.userLpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
			this.PrescrStore.proxy.extraParams.UslugaComplexList = Ext6.util.JSON.encode(UslugaComplexList);
			this.PrescrStore.proxy.extraParams.EvnPLDispDop13_id = me.EvnPLDispDop13_id; //730023881307390;
			this.PrescrStore.load();
			this.DirectionStore.proxy.extraParams.EvnDirection_pid = me.EvnPLDispDop13_id;
			this.DirectionStore.load();
		}

	},
	/*setTitleCounter: function(count) {
		this.callParent(arguments);
	},*/
	openResult: function(key){
		var me = this;
		if(key){
			var rec = this.PrescrStore.findRecord('UslugaComplex_id', key);
			if (!rec) {
				return false;
			}
			getWnd('uslugaResultWindow').show({
				Evn_id: rec.get('EvnUslugaPar_id'),
				object: 'EvnUslugaPar',
				object_id: 'EvnUslugaPar_id',
				userMedStaffFact: me.userMedStaffFact.MedStaffFact_id
			});
		} else
			return false;

	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	stepDay: function(day)
	{
		var me = this;
		var date = (me.TimetableDate.getValue() || Date.parseDate(me.TimetableMenu.StartDay, 'd.m.Y')).add(Date.DAY, day).clearTime();
		me.TimetableDate.setValue(date);
	},
	openTimetableMenu: function(btn, UslugaComplex_id){

		var me = this;
		var rec = me.PrescrStore.findRecord('UslugaComplex_id', UslugaComplex_id);
		me.loadTimetable(rec);

		me.TimetableMenu.showBy(btn);
		me.TimetableMenu.showTarget = btn;
		me.TimetableMenu.UslugaComplex_id = UslugaComplex_id;
		
	},
	checkAllowDayPrev: function(){
		var allow = true,
			me = this;
		var now = new Date().format('d.m.Y');
		if(now === me.TimetableMenu.StartDay){
			allow = false;
			sw.swMsg.alert(ERR_WND_TIT, langs('Запись на прошедшие дни невозможна'));
		}
		return allow;
	},
	loadTimetable: function (rec) {
		var me = this,
			store = me.TimetableStore,
			tt = me.TimetableMenu;
		store.removeAll();
		if(rec){
			tt.MedService_id = rec.get('ttms_MedService_id');
			tt.UslugaComplexMedService_id = rec.get('by_Usl')?rec.get('UslugaComplexMedService_id'):null;
			tt.Resource_id = rec.get('Resource_id');
			if(rec.get('TimetableMedService_begTime'))
				tt.StartDay = rec.get('TimetableMedService_begTime').format('d.m.Y');
				//tt.StartDay = rec.get('TimetableMedService_begTime').substr(0,10);
			if(rec.get('TimetableResource_begTime'))
				tt.StartDay = rec.get('TimetableResource_begTime').format('d.m.Y')
				//tt.StartDay = rec.get('TimetableResource_begTime').substr(0,10);
		} else {
			tt.StartDay = me.TimetableDate.getValue().format('d.m.Y')
		}

		var extraParams = {
			UslugaComplexMedService_id: tt.UslugaComplexMedService_id || null,
			MedService_id: tt.MedService_id || null,
			Resource_id: tt.Resource_id || null,
			StartDay: tt.StartDay
		};
		store.proxy.extraParams = extraParams;
		
		if(Ext6.isEmpty(tt.StartDay) || (Ext6.isEmpty(tt.Resource_id) && Ext6.isEmpty(tt.MedService_id))){
			sw.swMsg.alert(ERR_WND_TIT, langs('Необходима служба и день'));
			return false;
		}

		var date = (Date.parseDate(tt.StartDay, 'd.m.Y')).clearTime();
		me.TimetableDate.setValue(date);

		store.load();
		me.loadAnnotation(extraParams);
	},
	loadAnnotation: function (extraParams) {
		var me = this;
		extraParams.Lpu_id = getGlobalOptions().lpu_id;
		Ext6.Ajax.request({
			url: '/?c=TimetableMedService&m=loadAnnotateByDay',
			callback: function(opt, success, response) {
				var annotateField = me.TimetableMenu.down('#annotate');
				var response_obj = Ext6.JSON.decode(response.responseText);
				if(response_obj && response_obj.success){
					if(!Ext6.isEmpty(response_obj.annotate)){
						annotateField.setValue('<p class="day-select-description" style="padding-left: 16px;">Примечание врача общее или на день: '+response_obj.annotate+'</p>');
						annotateField.show();
					} else {
						annotateField.hide();
					}
				} else {
					annotateField.hide();
				}
			},
			params: extraParams
		});
	},
	renderTimetableBegTime: function(rec) {
		var me = this,
			dt = '',
			key = rec.get('UslugaComplex_id');
		// Если назначение записано - отображаем время записи
		if(rec.get('EvnDirection_id') && rec.get('RecDate'))
			dt = rec.get('RecDate');
		else {
			// Если существует бирка для записи, отображаем ее
			if(rec.get('TimetableResource_begTime') || rec.get('TimetableMedService_begTime')){
				var time = rec.get('TimetableResource_begTime') || rec.get('TimetableMedService_begTime');
				dt = (time?time.format('d.m.Y H:i'):'')+' <a data-qtip="Выбрать время записи" href="#" ' +
					'onclick="Ext6.getCmp(\'' + me.id + '\').openTimetableMenu(this,' +
					"'" + key + "'" +
					')"><span class="timetable"></span></a>';
			}
			else{
				// Проверим доступность службы для записи
				if(!rec.get('MedService_id')){
					dt = '';
				} else {
					dt = 'В очередь';
				}
			}
		}

		return dt;
	},

	renderPrescrStatus: function(rec) {
		var me = this;
		var key = rec.get('UslugaComplex_id');
		var text = '';

		switch(rec.get('EvnStatus_SysNick')) {
			case 'Queued':
				text =  'В очереди';
				break;
			case 'DirZap':
				text = 'Записан';
				break;
			default:
				if (!rec.get('EvnDirection_id')) {

					if(!rec.get('PrescriptionType_Code')){
						text = '<span class="search-result-diag" ' +
							'data-qtip="Для услуги требуется атрибут. Обратитесь к администратору системы.">Запись невозможна</span>';
					} else if(!rec.get('MedService_id')){
						text = '<a href="#" ' +
							'onclick="Ext6.getCmp(\'' + me.id + '\').createDirectionByMaster('+key+')">К ВРАЧУ</a>';
						//text = 'Место оказания не найдено';
					} else {
						text = '<a href="#" ' +
							'onclick="Ext6.getCmp(\'' + me.id + '\').doApply({key:' +
							"'" + key + "'" +
							'})">ЗАПИСАТЬ</a>';
					}

				} else {
					text = 'Записан';
				}
		}
		if (rec.get('EvnPrescr_IsExec') == 2) {
			text = '<a href="#" ' +
			'onclick="Ext6.getCmp(\'' + me.id + '\').openResult(' +
			"'" + key + "'" +
			')"><span class="replaseability"></span>Результаты</a>';
		}

		return text;
	},
	createDirectionByMaster: function(UslugaComplex_id) {
		var me = this;
		if (!UslugaComplex_id) return false;
		var rec = this.PrescrStore.findRecord('UslugaComplex_id', UslugaComplex_id);
		if (!rec) return false;
		if (!rec.get('EvnPrescr_id')) {
			// сперва сохраняем назначение
			me.saveEvnPrescr({
				rec: rec,
				withoutInfoMsg: true,
				callback: function() {
					// затем снова направление пытаемся сохранить
					me.createDirectionByMaster(UslugaComplex_id);
				}
			});
			return false;
		}
		var EvnPrescr_id = rec.get('EvnPrescr_id');
		var dir_store = sw.Promed.Direction.getDirTypeStore();
		var index = dir_store.find('DirType_Code', 12);
		var dir_type_rec;
		if(index){
			dir_type_rec = dir_store.getAt(index);
		}
		var excList = ['12'];
		var personData = {};
		personData.Person_id = me.data.Person_id;
		personData.Server_id = me.data.Server_id;
		personData.PersonEvn_id = me.data.PersonEvn_id;

		var piPanel = me.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			personData.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			personData.Person_Surname = piPanel.getFieldValue('Person_Surname');
			personData.Person_Firname = piPanel.getFieldValue('Person_Firname');
			personData.Person_Secname = piPanel.getFieldValue('Person_Secname');
			personData.Person_IsDead = piPanel.getFieldValue('Person_IsDead');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		var directionData = {
			EvnDirection_pid: me.data.Evn_id || null
			,DopDispInfoConsent_id: me.data.DopDispInfoConsent_id || null
			//,Diag_id: me.ownerPanel.getDiagId()
			,DirType_id: 16 // Поиск среди стора типов направлений не сработал
			,MedService_id: me.userMedStaffFact.MedService_id
			,MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
			,MedPersonal_id: me.userMedStaffFact.MedPersonal_id
			,LpuSection_id: me.userMedStaffFact.LpuSection_id
			,ARMType_id: me.userMedStaffFact.ARMType_id
			,Lpu_sid: getGlobalOptions().lpu_id
			,withDirection: true
		};
		directionData.Person_id = personData.Person_id;
		directionData.PersonEvn_id = personData.PersonEvn_id;
		directionData.Server_id = personData.Server_id;
		directionData.fromEMK = true;
		var onDirection = function (data) {
			if(data && data.EvnDirection_id){
				var EvnDirection_id = false;
				if (data.EvnDirection_id) {
					EvnDirection_id = data.EvnDirection_id;
				} else if (data.evnDirectionData && data.evnDirectionData.EvnDirection_id) {
					EvnDirection_id = data.evnDirectionData.EvnDirection_id;
				}

				if(!EvnDirection_id){
					sw.swMsg.alert(ERR_WND_TIT, langs('Не передан идентификатор направления'));
					return false;
				}

				me.directEvnPrescr({
					EvnPrescr_id: EvnPrescr_id,
					EvnDirection_id: EvnDirection_id
				}, function () {
					me.PrescrStore.reload();
				});

			}
		};

		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: me.userMedStaffFact,
			personData: personData,
			dirTypeData: {
				'DirType_id': 16,
				'DirType_Code': 12,
				'DirType_Name': 'На поликлинический прием'
			},
			dirTypeCodeExcList: excList,
			directionData: directionData,
			onDirection: onDirection
		});
		return true;
	},
	createDirection: function(dir_type_data, excList) {
		var me = this;

		var personData = {};
		personData.Person_id = me.data.Person_id;
		personData.Server_id = me.data.Server_id;
		personData.PersonEvn_id = me.data.PersonEvn_id;

		var piPanel = me.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			personData.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			personData.Person_Surname = piPanel.getFieldValue('Person_Surname');
			personData.Person_Firname = piPanel.getFieldValue('Person_Firname');
			personData.Person_Secname = piPanel.getFieldValue('Person_Secname');
			personData.Person_IsDead = piPanel.getFieldValue('Person_IsDead');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}
		
		var directionData = {
			EvnDirection_pid: me.EvnPLDispDop13_id || null
			,DopDispInfoConsent_id: me.data.DopDispInfoConsent_id || null
			,DirType_id: 16 // Поиск среди стора типов направлений не сработал
			,MedService_id: me.userMedStaffFact.MedService_id
			,MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
			,MedPersonal_id: me.userMedStaffFact.MedPersonal_id
			,LpuSection_id: me.userMedStaffFact.LpuSection_id
			,ARMType_id: me.userMedStaffFact.ARMType_id
			,Lpu_sid: getGlobalOptions().lpu_id
			,withDirection: true
		};
		directionData.Person_id = personData.Person_id;
		directionData.PersonEvn_id = personData.PersonEvn_id;
		directionData.Server_id = personData.Server_id;
		
		if (!excList) {
			excList = [];
		}
		excList.push('3');
		excList.push('12');
		excList.push('13');

		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact:  me.data.userMedStaffFact,
			personData: personData,
			dirTypeData: dir_type_data,
			dirTypeCodeExcList: excList,
			directionData: directionData,
			onHide: onDirection
		});
		
		var onDirection = function (data) {
			me.DirectionStore.reload();
		};

		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: me.userMedStaffFact,
			personData: personData,
			dirTypeData: dir_type_data,
			dirTypeCodeExcList: excList,
			directionData: directionData,
			onDirection: onDirection
		});
		

		return true;
	},
	
	directEvnPrescr: function (options, cbFn) {
		var me = this;
		if(options.EvnPrescr_id && options.EvnDirection_id){
			me.mask('Обновление назначения');
			Ext.Ajax.request({
				url: '/?c=EvnPrescr&m=directEvnPrescr',
				params: {
					EvnDirection_id: options.EvnDirection_id,
					EvnPrescr_id: options.EvnPrescr_id
				},
				callback: function (options, success, response) {
					me.unmask();
					cbFn(options, success, response);
				}
			});
		}
		else {
			return false;
		}
	},
	/**
	 * @param options
	 * @param wnd
	 * @returns {boolean}
	 */
	saveEvnPrescr: function (options) {
		var me = this,
			save_url = null,
			prescr_code = '',
			rec = options.rec;

		var MedService_id;
		// Берем id службы на чью бирку записываемся
		if (rec.get('ttms_MedService_id'))
			MedService_id = rec.get('ttms_MedService_id');
		// Если бирки на службе нет, пробуем записаться на ПЗ
		if (!MedService_id && rec.get('pzm_MedService_id'))
			MedService_id = rec.get('pzm_MedService_id');
		// Если и пункта забора нет, только тогда записываем на саму службу
		if (!MedService_id)
			MedService_id = rec.get('MedService_id');
		var params = {
			PersonEvn_id: me.PersonEvn_id,
			Server_id: me.Server_id,
			parentEvnClassSysNick: "EvnPLDispDop13",
			DopDispInfoConsent_id: '',
			StudyTarget_id: '2', // Тип
			MedService_id: MedService_id,
			UslugaComplex_id: rec.get('UslugaComplex_id'),
			PrescriptionType_Code: rec.get('PrescriptionType_Code')
		};

		switch (params.PrescriptionType_Code) {
			case 11:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
				prescr_code = 'EvnPrescrLabDiag';
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				params.EvnPrescrLabDiag_uslugaList = rec.get('UslugaComplex_id');
				break;
			case 12:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
				prescr_code = 'EvnPrescrFuncDiag';
				params.EvnPrescrFuncDiag_uslugaList = rec.get('UslugaComplex_id');
				break;
			case 13:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
				prescr_code = 'EvnPrescrConsUsluga';
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				break;
		}

		if (!save_url) {
			return false;
		}

		var date = new Date();
		params[prescr_code + '_id'] = null;
		params[prescr_code + '_pid'] = me.EvnPLDispDop13_id;
		params[prescr_code + '_IsCito'] = rec.get('UslugaComplex_IsCito') ? 'on' : 'off';
		params[prescr_code + '_setDate'] = date.format('d.m.Y');
		params[prescr_code + '_Descr'] = '';

		me.mask('Сохранение назначения');
		if (params.PrescriptionType_Code == 11) {
			Ext6.Ajax.request({
				url: '/?c=MedService&m=loadCompositionMenu',
				success: function (response) {
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
						callback: function (opt, success, response) {
							if (response && response.responseText) {
								var response_obj = Ext6.JSON.decode(response.responseText);

								var EvnPrescr_id = response_obj[prescr_code + '_id'];

								if (EvnPrescr_id) {
									rec.set('EvnPrescr_id', EvnPrescr_id);
									if (typeof options.callback == 'function') {
										options.callback();
									}
									rec.set('UslugaComplexMedService_HasPrescr', true);
								} else {
									rec.set('UslugaComplexMedService_HasPrescr', false); // не удалось сохранить, убираем галку
								}
							}
							rec.commit();
							me.unmask();

							if (!options.withoutInfoMsg) {
								sw4.showInfoMsg({
									panel: me,
									type: 'warning',
									text: 'Услуга добавлена. Требуется запись.'
								});
							}
						},
						params: params
					});
				},
				params: {
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					UslugaComplex_pid: rec.get('UslugaComplex_id'),
					Lpu_id: rec.get('Lpu_id')
				}
			});
		} else{
			Ext6.Ajax.request({
				url: save_url,
				callback: function (opt, success, response) {
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);

						var EvnPrescr_id = response_obj[prescr_code + '_id'];

						if (EvnPrescr_id) {
							rec.set('EvnPrescr_id', EvnPrescr_id);
							//me.callback({'EvnPrescr_id': EvnPrescr_id, 'action': 'add'});
							if (typeof options.callback == 'function') {
								options.callback();
							}
							rec.set('UslugaComplexMedService_HasPrescr', true);
						} else {
							rec.set('UslugaComplexMedService_HasPrescr', false); // не удалось сохранить, убираем галку
						}
					}
					rec.commit();
					me.unmask();

					if (!options.withoutInfoMsg) {
						sw4.showInfoMsg({
							panel: me,
							type: 'warning',
							text: 'Услуга добавлена. Требуется запись.'
						});
					}
				},
				params: params
			});
		}
	},
	doApplyToQueue: function(link, key) {
		this.doApply({
			key: key,
			toQueue: true
		});
	},
	doApply: function(options) {
		if (!options) {
			options = {};
		}
		var me = this,
			recData = false,
			rec = options.rec;
		if (!rec) {
			rec = this.PrescrStore.findRecord('UslugaComplex_id', options.key);
			if (!rec) {
				return false;
			}
		}
		// @todo а нужна ли строка ниже?
		recData = rec.getData();
		if (options.toQueue) {
			rec.set('TimetableMedService_id', null);
			rec.set('TimetableMedService_begTime', null);
			rec.set('ttms_MedService_id', null);
			rec.set('TimetableResource_id', null);
			rec.set('TimetableResource_begTime', null);
			rec.set('Resource_id', null);
			rec.set('Resource_Name', null);
			rec.set('ttr_Resource_id', null);
			rec.commit();
		}
		if (!rec.get('EvnPrescr_id')) {
			// сперва сохраняем назначение
			me.saveEvnPrescr({
				rec: rec,
				withoutInfoMsg: true,
				callback: function() {
					// затем снова направление пытаемся сохранить
					me.doApply(options);
				}
			});

			return false;
		}
		var params = rec.data;
		//params.PrescriptionType_Code = me.PrescriptionType_Code;
		params.onSaveEvnDirection = function(data) {
			me.unmask();
			rec = me.PrescrStore.findRecord('UslugaComplex_id', options.key);
			if(rec && data && data.EvnDirection_id){
				rec.set('EvnDirection_id', data.EvnDirection_id);
				var time = rec.get('TimetableResource_begTime') || rec.get('TimetableMedService_begTime');
				rec.set('RecDate', (time?time.format('d.m.Y H:i'):''));
				rec.commit();
				me.PrescrStore.reload();
			}

			if(data && data.Error_Msg){
				// @todo Быстрое решение - нужно убрать
				if(recData){
					rec.set('TimetableMedService_id', recData.TimetableMedService_id);
					rec.set('TimetableMedService_begTime', recData.TimetableMedService_begTime);
					rec.set('ttms_MedService_id', recData.ttms_MedService_id);
					rec.set('TimetableResource_id', recData.TimetableResource_id);
					rec.set('TimetableResource_begTime', recData.TimetableResource_begTime);
					rec.set('Resource_id', recData.Resource_id);
					rec.set('Resource_Name', recData.Resource_Name);
					rec.set('ttr_Resource_id', recData.ttr_Resource_id);
				}
				rec.set('not_to_queue', true);
				rec.commit();
				sw4.showInfoMsg({
					panel: me.ownerWin,
					type: 'error',
					text: (data.Error_Msg?data.Error_Msg:'Постановка в очередь ограничена. Обратитесь к администратору МО')
				});
			}
		};

		me.saveEvnDirection(params);
	},
	saveEvnDirection: function(prescrParams) {
		log('saveEvnDirection', prescrParams);
		var me = this;

		prescrParams.Person_Surname = me.PersonInfoPanel.getFieldValue('Person_Surname');
		prescrParams.Person_Firname = me.PersonInfoPanel.getFieldValue('Person_Firname');
		prescrParams.Person_Secname = me.PersonInfoPanel.getFieldValue('Person_Secname');
		prescrParams.Person_Birthday = me.PersonInfoPanel.getFieldValue('Person_Birthday');
		me.data.Evn_id = me.EvnPLDispDop13_id;
		me.data.userMedStaffFact = me.userMedStaffFact;

		prescrParams.fdata =  me.data; // данные с формы

		createDirection(prescrParams, function(direction){
			var checked = []; //список услуг для заказа
			var params = { //параметры для функции создания направления
				person: {
					Person_id: me.Person_id
					,PersonEvn_id: me.PersonEvn_id
					,Server_id: me.Server_id
				},
				needDirection: false,
				mode: 'nosave',
				loadMask: false,
				windowId: 'EvnPrescrUslugaInputWindow',
				onFailure: function(code,answer){
					me.unmask();
					if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection(answer);
					}
				},
				callback: function(responseData, realResponseData){
					me.unmask();
					/*if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection();
					}*/
				},
				onCancel: function(){
					me.unmask();
					if (typeof prescrParams.onSaveEvnDirection == 'function') {
						prescrParams.onSaveEvnDirection();
					}
				},
				onCancelQueue: function(evn_queue_id, callback) {
					me.unmask();
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
			direction.StudyTarget_id = 1;
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
			
			if (prescrParams.TimetableMedService_id > 0) {
				me.mask('Запись на бирку...');
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
							panel: me.ownerPanel,
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
				me.mask('Запись на бирку...');
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
							panel: me.ownerPanel,
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

				me.mask('Постановка в очередь...');
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
							panel: me.ownerPanel,
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

		me.mask('Получение состава услуги...');
		Ext6.Ajax.request({
			params: {
				UslugaComplexMedService_pid: params.UslugaComplexMedService_id,
				UslugaComplex_pid: params.UslugaComplex_id,
				Lpu_id: params.Lpu_id
			},
			callback: function(options, success, response) {
				me.unmask();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					callback(response_obj);
				}
			},
			url: '/?c=MedService&m=loadCompositionMenu'
		});
	},
	initComponent: function() {
		var me = this;
		this.DirectionStore = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EvnDirection_id', type: 'int' },
				{
					name: 'SignHidden',
					type: 'boolean',
					convert: function(val, row) {
						if (me.accessType == 'edit' && row.get('DirType_Code') && row.get('DirType_Code').inlist([1, 2, 3, 4, 5, 6, 8, 9, 12, 13, 23])) {
							return false;
						} else {
							return true;
						}
					}
				},
				{ name: 'EMDRegistry_ObjectName', type: 'string' },
				{ name: 'EMDRegistry_ObjectID', type: 'int' },
				{ name: 'IsSigned', type: 'int' },
				{ name: 'EvnPrescrVK_id', type: 'int' },
				{ name: 'EvnStatus_epvkSysNick', type: 'string' },
				{ name: 'EvnStatus_id', type: 'int' },
				{ name: 'DirType_Name', type: 'string' },
				{ name: 'LpuSection_Name', type: 'string' },
				{ name: 'Lpu_Name', type: 'string' },
				{ name: 'Org_Name', type: 'string' },
				{ name: 'Lpu_Nick', type: 'string' },
				{ name: 'Org_Nick', type: 'string' },
				{ name: 'EvnDirection_setDate', type: 'string' },
				{ name: 'EvnDirection_Num', type: 'string' },
				{ name: 'TimetableGraf_id', type: 'string' },
				{ name: 'TimetableMedService_id', type: 'string' },
				{ name: 'TimetableResource_id', type: 'string' },
				{ name: 'TimetableStac_id', type: 'string' },
				{ name: 'EvnQueue_id', type: 'string' },
				{ name: 'DirType_Code', type: 'string' },
				{ name: 'EvnStatus_Name', type: 'string' },
				{ name: 'EvnDirection_statusDate', type: 'string' },
				{ name: 'EvnStatusCause_Name', type: 'string' },
				{ name: 'EvnPrescrMse_id', type: 'int' },
				{ name: 'EvnDirectionHistologic_id', type: 'int' },
				{ name: 'Lpu_gid', type: 'int' }
			],
			listeners: {
				'load': function(store, records) {
					//me.setTitleCounter(records.length);
				}
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=EvnDirection&m=loadEvnDirectionPanel_EvnPLDispDop',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: [
				'EvnDirection_id'
			]
		});
		
		this.PrescrStore = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'object', type: 'string'},
				{ name: 'UslugaComplex_id', type: 'int'},
				{ name: 'UslugaComplex_Name', type: 'string'},
				{ name: 'MedService_id', type: 'int'},
				{ name: 'Resource_id', type: 'int'},
				{ name: 'TimetableGraf_id', type: 'int'},
				{ name: 'EvnPrescr_IsExec', type: 'int'},
				{ name: 'EvnUslugaPar_id', type: 'int'},
				{ name: 'Lpu_id', type: 'int'},
				{ name: 'MedService_Nick', type: 'string'},
				{ name: 'MedService_Name', type: 'string'},
				{ name: 'LpuSection_Name', type: 'string'},
				{ name: 'EvnStatus_SysNick', type: 'string'},
				{ name: 'Lpu_Nick', type: 'string'},
				{ name: 'LpuUnit_id', type: 'int'},
				{ name: 'LpuSection_id', type: 'int'},
				{ name: 'LpuSectionProfile_id', type: 'int'},
				{ name: 'formatTime', type: 'string'},
				{ name: 'formatDate', type: 'string'},
				{ name: 'timetable', type: 'string'},
				{ name: 'EvnUsluga_Date', type: 'date', format: 'd.m.Y', dateFormat: 'd.m.Y'},//дата выполненной услуги
				{ name: 'TimetableResource_begTime', type: 'date', dateFormat: 'd.m.Y H:i'},//дата бирки ресурса
				{ name: 'TimetableMedService_begTime', type: 'date', dateFormat: 'd.m.Y H:i'}//дата бирки службы
			],
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13PrescrList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			listeners: {
				load: function(store, records, successful, operation, eOpts){
					me.unmask();
				}
			}
		});

		/*this.TitetableSelectionPanel = Ext6.create('common.EMK.tools.swTimetableSelectionWindow', {
			parentPanel: me,
			reference: 'TimetablePanel',
			onSelect: function(params){
				
			}
		});*/

		this.PrescrGrid = Ext6.create('Ext6.grid.Panel', {
			minHeight: 90,
			flex: 1,
			split: true,
			autoHeight: true,
			margin: 10,
			frame: false,
			draggable: false,
			cls: 'ScreenPrescrGrid',
			viewModel: true,
			panelForMsg: me.ownerPanel || me.findParentByType('panel'),
			bodyStyle: {
				borderTop: '0px'
			},
			plugins: {
				cellediting: {
					clicksToEdit: 1
				}
			},
			bind: {
				disabled: '{action == "view"}'
			},
			columns: [
				{
					text: 'Услуга',
					dataIndex: 'UslugaComplex_Name',
					minWidth: 250,
					maxWidth: 500,
					height: 40,
					flex: 2,
					autoSizeColumn: true,
					xtype: 'gridcolumn',
					renderer: function (value, meta, rec) {
						switch(rec.get('EvnStatus_SysNick')) {
							case 'Queued':
								meta.tdCls = 'icon-queued';
								break;
							case 'DirZap':
							case 'Serviced':
								meta.tdCls = 'icon-selectDT';
								break;
							default:
								if(!rec.get('EvnDirection_id')){
									meta.tdCls = 'icon-needSelectDT';
								} else {
									meta.tdCls = 'icon-selectDT';
								}
						}

						if (rec.get('EvnPrescr_IsExec') == '2') {
							meta.tdCls = 'icon-results';
						}

						return value;
					}
				},
				{
					xtype: 'gridcolumn',
					text: 'Место оказания',
					sortable: false,
					flex: 1,
					autoSizeColumn: true,
					minWidth: 190,
					height: 40,
					dataIndex: 'location',
					renderer: function (val, metadata, rec) {
						if (!rec.get('UslugaComplex_Name')) return '';
						if(rec.get('EvnDirection_id') && rec.get('RecTo'))
							return rec.get('RecTo');
						if (!rec.get('MedService_id') && !rec.get('Resource_id')) {
							return 'Место оказания не найдено';
						}
						//если есть одна служба, то в этой колонке должен быть текст
						var text = rec.get('MedService_Nick');
						var hint = rec.get('MedService_Name') + ' / ' + rec.get('Lpu_Nick') /*+ ' / ' +
							rec.get('LpuUnit_Name') + ' / ' + rec.get('LpuUnit_Address')*/;
						// если это назначение лабораторной диагностики и есть пункт забора
						if (rec.get('pzm_MedService_id')) {
							//то отображаем пункт забора как место оказания
							text = rec.get('pzm_MedService_Nick') + ' / ' + rec.get('MedService_Nick');
							hint = rec.get('Lpu_Nick') + ' / ' + rec.get('pzm_MedService_Name') + ' / ' + rec.get('MedService_Name');
						}
						if (rec.get('Resource_id')) {
							//то отображаем пункт забора как место оказания
							text = rec.get('Resource_Name') + ' / ' + rec.get('MedService_Nick');
							hint = rec.get('Lpu_Nick') + ' / ' + rec.get('Resource_Name') + ' / ' + rec.get('MedService_Name');
						}

						return '<span style="white-space: nowrap; text-overflow: ellipsis" data-qtip="' + hint.replace(new RegExp('"', 'g'), '&quot;') + '">' + text.replace(new RegExp('"', 'g'), '&quot;') + '</span>';
					},
					editor: {
						xtype: 'swMedServicePrescrCombo',
						hideLabel: true,
						valueField: 'UslugaComplexMedService_key',
						displayField: 'displayField',
						queryMode: 'local',
						typeAhead: true,
						triggerAction: 'all',
						listConfig: {
							minWidth: 400,
							width: 400,
							cls: 'choose-bound-list-menu'
						},
						id: me.id + '_MedServiceEditor'
					}
				},
				{
					text: 'Дата, время',
					dataIndex: 'formatTime',
					minWidth: 190,
					height: 40,
					flex: 1,
					autoSizeColumn: true,
					sortable: false,
					xtype: 'gridcolumn',
					renderer: function (val, metadata, rec) {
						return me.renderTimetableBegTime(rec);
					},
				},
				{
					text: 'Статус',
					dataIndex: 'timetable',
					minWidth: 190,
					height: 40,
					flex: 1,
					autoSizeColumn: true,
					sortable: false,
					xtype: 'gridcolumn',
					renderer: function (val, metadata, rec) {
						return me.renderPrescrStatus(rec);
					}
				},
				{
					xtype: 'actioncolumn',
					width: 30,
					sortable: false,
					menuDisabled: true,
					tooltip: 'Меню',
					items: ['@menuItem']
				}
			],
			actions: {
				menuItem: {
					userCls: 'button-without-frame',
					iconCls: 'grid-header-icon-menuItem',
					tooltip: 'Меню',
					tdCls: 'action-col-menuItem',
					handler: function(panel, rowIndex, colIndex, item, e, record){
						e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
						if(me.PrescrGrid.threeDotMenu){
							me.PrescrGrid.threeDotMenu.selRecord = record;
							var menu = me.PrescrGrid.threeDotMenu;
							var position = e.getXY();
							e.stopEvent();
							menu.items.each(function(menuItem){
								switch(menuItem.name){
									case 'cancelEvnDirection':
										//menuItem.setDisabled(!record.get('EvnDirection_id') || record.get('EvnPrescr_IsExec') == '2');
										break;
									case 'createDirectionByMaster':
										menuItem.setDisabled(record.get('EvnDirection_id') || !record.get('PrescriptionType_Code') || record.get('PrescriptionType_Code') != '13' );
										break;
									default:
										break;
								}
							});
							menu.showAt(position);
						}
					}
				}
			},
			threeDotMenu:  Ext6.create('Ext6.menu.Menu', {
				userCls: 'menuWithoutIcons',
				items: [
					{
						text: 'Отменить запись',
						name: 'cancelEvnDirection',
						handler: function (menuItem) {
							var grid = me.PrescrGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								grid.deleteItem(rec);
						}
					},{
						text: 'Запись к врачу',
						name: 'createDirectionByMaster',
						handler: function (menuItem) {
							var grid = me.PrescrGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								me.createDirectionByMaster(rec.get('UslugaComplex_id'));

						}
					}
				]
			}),
			params: {
				param_name: 'section',
				param_value: 'EvnPrescrPolka',
				param_del_value: 'EvnVizitPL'
			},
			/**
			 * Удаляет назначение и обновляет грид
			 * @param rec удаляемая запись
			 * @param cbFn функция выполняемая после обновления грида после удаления
			 */
			deleteItem: function(rec, cbFn){
				var grid = this,
					callbackFn = (typeof cbFn == 'function' ? cbFn : Ext6.emptyFn),
					params = {
						PrescriptionType_id: rec.get('PrescriptionType_Code'),
						parentEvnClass_SysNick: grid.params.param_del_value,
						EvnPrescr_id: rec.get('EvnPrescr_id')
					};

				if (rec.get('TimetableGraf_id')) {
					sw.Promed.Direction.cancel({
						//cancelType: 'decline',
						cancelType: 'cancel',
						userMedStaffFact: me.userMedStaffFact,
						EvnDirection_id: rec.get('EvnDirection_id'),
						TimetableGraf_id: rec.get('TimetableGraf_id'),

						ownerWindow: me,
						DirType_Code: rec.get('DirType_Code'),
						TimetableMedService_id: rec.get('TimetableMedService_id'),
						TimetableResource_id: rec.get('TimetableResource_id'),
						TimetableStac_id: rec.get('TimetableStac_id'),
						EvnQueue_id: rec.get('EvnQueue_id'),
						allowRedirect: false,
						personData: {
							Person_id: me.Person_id,
							Server_id: me.Server_id,
							PersonEvn_id: me.PersonEvn_id
						},
						callback: function (cfg) {
							grid.cancelEvnPrescr(params, callbackFn);
						}
					});
				} else  {
					this.cancelEvnPrescr(params, callbackFn);
				}
			},
			cancelEvnPrescr: function(params, callbackFn){
				var grid = this,
					panel = grid.panelForMsg;
				if(!grid.panelForMsg)
					panel = Ext6.ComponentQuery.query('panel[refId="polkawpr"]')[0];
				if(!panel)
					panel = me.up('panel').up('panel').up('panel');

				sw.Promed.EvnPrescr.cancel({
					ownerWindow: me,
					withoutQuestion: true,
					getParams: function(){
						return {
							EvnPrescr_id: params.EvnPrescr_id,
							parentEvnClass_SysNick: params.EvnPrescr_id,
							PrescriptionType_id: params.PrescriptionType_id
						};
					},
					callback: function(){
						/* // как вариант доотменить, если общий класс не справился
						* sw.Promed.Direction.cancel({
									cancelType: 'cancel',
									ownerWindow: conf.ownerWindow,
									EvnDirection_id: answer.EvnDirection_id,
									DirType_id: answer.DirType_id || null,
									callback: function(cfg) {
										cancelEvnPrescr(input_data);
									}
								});*/
						grid.getStore().reload({
							callback: function (records, operation, success) {
								callbackFn();
							}
						});
						sw4.showInfoMsg({
							panel: panel,
							type: 'success',
							text: 'Назначение удалено',
							hideDelay: 2000
						});
					}
				});
			},
			listeners: {
				beforeEdit: function(grid, context) {
					log('beforeEdit', grid, context);
					if (context.field == 'location') {
						if(context.record.get('EvnDirection_id'))
							return false;
						var MedServiceEditor = Ext6.getCmp(me.id + '_MedServiceEditor');
						MedServiceEditor.getStore().proxy.extraParams.filterByUslugaComplex_id = context.record.get('UslugaComplex_id');
						if (context.record.get('Lpu_id')) {
							MedServiceEditor.getStore().proxy.extraParams.filterByLpu_id = context.record.get('Lpu_id');
						}
						// Для показа на Перми, 101 - идентификатор тестовой МО "Медицинская организация"
						//MedServiceEditor.getStore().proxy.extraParams.filterByLpu_id = 101;
						MedServiceEditor.getStore().proxy.extraParams.userLpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
						MedServiceEditor.getStore().proxy.extraParams.PrescriptionType_Code = context.record.get('PrescriptionType_Code');
						MedServiceEditor.getStore().load({
							callback: function() {
								// выбрать запись с той же службой
								var rec = MedServiceEditor.getStore().findRecord('MedService_id', context.record.get('MedService_id'));
								if (rec) {
									MedServiceEditor.setValue(rec.get('UslugaComplexMedService_key'));
								}
							}
						});
					}
				},
				edit: function(grid, context) {
					log('edit', grid, context);
					if (context.field == 'location') {
						var MedServiceEditor = Ext6.getCmp(me.id + '_MedServiceEditor');
						var sel_rec = MedServiceEditor.getSelectedRecord();
						if (sel_rec && sel_rec.get('MedService_id')) {
							// возможно была выбрана другая служба и услуга
							if (sel_rec.get('UslugaComplexMedService_id') != context.record.get('UslugaComplexMedService_id')) {
								context.record.compositionMenu = null;
								context.record.set('isComposite', sel_rec.get('isComposite'));
							}
							context.record.set('UslugaComplexMedService_id', sel_rec.get('UslugaComplexMedService_id'));
							context.record.set('pzm_UslugaComplexMedService_id', sel_rec.get('pzm_UslugaComplexMedService_id'));
							// не должна меняться? context.record.set('UslugaComplex_2011id', sel_rec.get('UslugaComplex_2011id'));
							context.record.set('UslugaComplex_id', sel_rec.get('UslugaComplex_id'));
							context.record.set('UslugaComplex_Code', sel_rec.get('UslugaComplex_Code'));
							context.record.set('UslugaComplex_Name', sel_rec.get('UslugaComplex_Name')); // при смене места оказания показывать наименование из данного места оказания
							context.record.set('MedService_id', sel_rec.get('MedService_id'));
							context.record.set('MedServiceType_id', sel_rec.get('MedServiceType_id'));
							context.record.set('MedServiceType_SysNick', sel_rec.get('MedServiceType_SysNick'));
							context.record.set('MedService_Nick', sel_rec.get('MedService_Nick'));
							context.record.set('MedService_Name', sel_rec.get('MedService_Name'));
							context.record.set('Lpu_id', sel_rec.get('Lpu_id'));
							context.record.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
							context.record.set('LpuBuilding_id', sel_rec.get('LpuBuilding_id'));
							context.record.set('LpuBuilding_Name', sel_rec.get('LpuBuilding_Name'));
							context.record.set('LpuUnit_id', sel_rec.get('LpuUnit_id'));
							context.record.set('LpuUnit_Name', sel_rec.get('LpuUnit_Name'));
							context.record.set('LpuUnitType_id', sel_rec.get('LpuUnitType_id'));
							context.record.set('LpuUnitType_SysNick', sel_rec.get('LpuUnitType_SysNick'));
							context.record.set('LpuSection_id', sel_rec.get('LpuSection_id'));
							context.record.set('LpuSection_Name', sel_rec.get('LpuSection_Name'));
							context.record.set('LpuSectionProfile_id', sel_rec.get('LpuSectionProfile_id'));
							context.record.set('ttms_MedService_id', sel_rec.get('ttms_MedService_id'));
							context.record.set('TimetableMedService_id', sel_rec.get('TimetableMedService_id'));
							context.record.set('TimetableMedService_begTime', sel_rec.get('TimetableMedService_begTime'));
							context.record.set('TimetableResource_begTime', sel_rec.get('TimetableResource_begTime'));
							context.record.set('TimetableResource_id', sel_rec.get('TimetableResource_id'));
							context.record.set('Resource_id', sel_rec.get('Resource_id'));
							context.record.set('Resource_Name', sel_rec.get('Resource_Name'));
							context.record.set('ttr_Resource_id', sel_rec.get('ttr_Resource_id'));
							if (context.record.get('PrescriptionType_Code') == 11 || sel_rec.get('PrescriptionType_Code') == 11) {
								// возможно была выбрана другая лаборатория или другой пункт забора
								context.record.set('MedService_id', sel_rec.get('lab_MedService_id')); // лаборатория должна попасть в EvnDirection.
								context.record.set('pzm_Lpu_id', sel_rec.get('pzm_Lpu_id'));
								context.record.set('pzm_MedService_id', sel_rec.get('pzm_MedService_id'));
								context.record.set('pzm_MedServiceType_id', sel_rec.get('pzm_MedServiceType_id'));
								context.record.set('pzm_MedServiceType_SysNick', sel_rec.get('pzm_MedServiceType_SysNick'));
								context.record.set('pzm_MedService_Nick', sel_rec.get('pzm_MedService_Nick'));
								context.record.set('pzm_MedService_Name', sel_rec.get('pzm_MedService_Name'));
							}
							context.record.commit();
						}
					}
				}
			},

			store: me.PrescrStore
			/*store: {
				data: [
					{
						object: 'EvnPrescrFuncDiag',
						UslugaComplex_Name: 'Обший (клинический) анализ крови',
						UslugaComplex_id: '4427434',
						MedService_id: 568,
						MedService_Nick: 'ПЗ КДЛ (Гематология)',
						formatTime: '11:40',
						formatDate: '06.10.2019 пн',
						timetable: 'Записан',
						statusNazn: '1'
					},{
						object: 'EvnPrescrFuncDiag',
						UslugaComplex_Name: 'Обший (клинический) анализ крови',
						UslugaComplex_id: '4427434',
						MedService_id: 568,
						MedService_Nick: 'ПЗ КДЛ (Гематология)',
						formatTime: '11:40',
						formatDate: '06.10.2019 пн',
						timetable: 'Записан',
						statusNazn: '2'
					},{
						object: 'EvnPrescrLabDiag',
						UslugaComplex_Name: 'Обший (клинический) анализ крови',
						UslugaComplex_id: '4427434',
						MedService_id: 568,
						MedService_Nick: 'ПЗ КДЛ (Гематология)',
						formatTime: '11:40',
						formatDate: '06.10.2019 пн',
						timetable: 'Записан',
						statusNazn: '3'
					},{
						object: 'EvnPrescrLabDiag',
						UslugaComplex_Name: 'Обший (клинический) анализ крови',
						UslugaComplex_id: '4427434',
						MedService_id: 568,
						MedService_Nick: 'ПЗ КДЛ (Гематология)',
						formatTime: '11:40',
						formatDate: '06.10.2019 пн',
						timetable: 'Записан',
						statusNazn: '4'
					},{
						object: 'EvnPrescrLabDiag',
						UslugaComplex_Name: 'Обший (клинический) анализ крови',
						UslugaComplex_id: '4427434',
						MedService_id: 568,
						MedService_Nick: 'ПЗ КДЛ (Гематология)',
						formatTime: '11:40',
						formatDate: '06.10.2019 пн',
						timetable: 'Записан',
						statusNazn: '5'
					}
				]
			}*/

		});

		this.DirectionGrid = Ext6.create('Ext6.grid.Panel', {
			minHeight: 90,
			flex: 1,
			split: true,
			autoHeight: true,
			margin: 10,
			frame: false,
			draggable: false,
			cls: 'ScreenPrescrGrid',
			viewModel: true,
			panelForMsg: me.ownerPanel || me.findParentByType('panel'),
			bodyStyle: {
				borderTop: '0px'
			},
			plugins: {
				cellediting: {
					clicksToEdit: 1
				}
			},
			bind: {
				disabled: '{action == "view"}'
			},
			columns: [
				{
					text: '№ направления',
					dataIndex: 'EvnDirection_Num',
					minWidth: 130,
					height: 40,
					flex: 1,
					autoSizeColumn: true,
					sortable: false,
					xtype: 'gridcolumn'
				},
				{
					text: 'Тип направления',
					dataIndex: 'DirType_Name',
					//minWidth: 250,
					maxWidth: 500,
					height: 40,
					flex: 2,
					autoSizeColumn: true,
					xtype: 'gridcolumn'
				},
				{
					xtype: 'gridcolumn',
					text: 'МО',
					sortable: false,
					flex: 1,
					autoSizeColumn: true,
					//minWidth: 190,
					height: 40,
					dataIndex: 'Lpu',
					renderer: function (value, meta, rec) {
						return Ext6.isEmpty(rec.get('Org_Nick')) ? rec.get('Lpu_Nick') : rec.get('Org_Name');
					}
				},
				{
					text: 'Отделение',
					dataIndex: 'LpuSection_Name',
					minWidth: 100,
					height: 40,
					flex: 2,
					autoSizeColumn: true,
					sortable: false,
					xtype: 'gridcolumn'
				},
				{
					text: 'Статус',
					dataIndex: 'EvnStatus_Name',
					minWidth: 100,
					height: 40,
					//flex: 1,
					autoSizeColumn: true,
					sortable: false,
					xtype: 'gridcolumn'
				},
				{
					xtype: 'actioncolumn',
					width: 30,
					sortable: false,
					menuDisabled: true,
					tooltip: 'Меню',
					items: ['@menuItem']
				}
			],
			actions: {
				menuItem: {
					userCls: 'button-without-frame',
					iconCls: 'grid-header-icon-menuItem',
					tooltip: 'Меню',
					tdCls: 'action-col-menuItem',
					handler: function(panel, rowIndex, colIndex, item, e, record){
						e.onBtnClick = true; // Необходимо при любом нажатии на actioncolumn button в гриде
						if(me.DirectionGrid.threeDotMenu){
							me.DirectionGrid.threeDotMenu.selRecord = record;
							var menu = me.DirectionGrid.threeDotMenu;
							var position = e.getXY();
							e.stopEvent();
							menu.items.each(function(menuItem){
								switch(menuItem.name){
									case 'cancelEvnDirection':
										menuItem.setDisabled(!record.get('EvnDirection_id') && record.get('EvnStatus_SysNick')!='canceled');
										break;
									default:
										break;
								}
							});
							menu.showAt(position);
						}
					}
				}
			},
			threeDotMenu:  Ext6.create('Ext6.menu.Menu', {//Direction Menu
				userCls: 'menuWithoutIcons',
				items: [
					{
						text: 'Редактировать',
						name: 'openEvnDirectionEditWindow',
						handler: function (menuItem) {
							var grid = me.DirectionGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								grid.openEvnDirectionEditWindow(rec, 'edit');
						}
					},{
						text: 'Просмотр',
						name: 'openEvnDirectionViewWindow',
						handler: function (menuItem) {
							var grid = me.DirectionGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								grid.openEvnDirectionEditWindow(rec, 'view');
						}
					},{
						text: 'Отменить направление',
						name: 'cancelEvnDirection',
						handler: function (menuItem) {
							var grid = me.DirectionGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								grid.cancelEvnDirection(rec);
						}
					},{
						text: 'Печать',
						name: 'printEvnDirection',
						handler: function (menuItem) {
							var grid = me.DirectionGrid,
								rec = menuItem.ownerCt.selRecord;
							if (grid && rec)
								grid.printEvnDirection(rec);

						}
					}
				]
			}),
			params: {
				param_name: 'section',
				param_value: 'EvnPrescrPolka',
				param_del_value: 'EvnVizitPL'
			},
			/**
			 * Удаляет назначение и обновляет грид
			 * @param rec удаляемая запись
			 * @param cbFn функция выполняемая после обновления грида после удаления
			 */
			deleteItem: function(rec, cbFn){
				
			},
			printEvnDirection: function(record){
				var addParams = '';
				// включена опция печати тестов с мнемоникой
				if (Ext.globalOptions.lis.PrintMnemonikaDirections)
				{
					addParams += '&PrintMnemonikaDirections=1';
				} // или просто опция печати исследований
				else if (Ext.globalOptions.lis.PrintResearchDirections)
				{
					addParams += '&PrintResearchDirections=1';
				}

				if (record && record.get('EvnDirection_id')) {
					var EvnDirection_id = record.get('EvnDirection_id');
					if(record.get('DirType_Code')==9) {//"на исследование"
						var birtParams = {
							'Report_FileName': 'printEvnDirection.rptdesign',
							'Report_Params': '&paramEvnDirection=' + EvnDirection_id + addParams,
							'Report_Format': 'pdf'
						};

						if (
							getRegionNick() == 'perm' &&
							!Ext6.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
							Ext.globalOptions.lis.direction_print_form == 2
						) {
							Ext6.Ajax.request({
								url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
								params: {
									EvnDirection_id: params.EvnDirection_id
								},
								callback: function (options, success, response) {
									if (success) {
										var result = Ext6.util.JSON.decode(response.responseText);
										if (!Ext.isEmpty(result.MedServiceType_SysNick) && result.MedServiceType_SysNick != 'func') {
											birtParams.Report_FileName = 'printEvnDirectionCKDL.rptdesign';
										}
										printBirt(birtParams);
									}
								}
							});
						} else {
							printBirt(birtParams);
						}
					} else {
						sw.Promed.Direction.print({
							EvnDirection_id: EvnDirection_id
						});
					}
				}
			},
			cancelEvnDirection: function(record){
				var grid = this,
					panel = grid.panelForMsg;
				if(!grid.panelForMsg)
					panel = Ext6.ComponentQuery.query('panel[refId="polkawpr"]')[0];
				if(!panel)
					panel = me.up('panel').up('panel').up('panel');

				var params = {
					cancelType: 'cancel',
					ownerWindow: me,
					EvnDirection_id: record.get('EvnDirection_id'),
					DirType_Code: record.get('DirType_Code'),
					TimetableGraf_id: record.get('TimetableGraf_id'),
					TimetableMedService_id: record.get('TimetableMedService_id'),
					TimetableResource_id: record.get('TimetableResource_id'),
					TimetableStac_id: record.get('TimetableStac_id'),
					EvnQueue_id: record.get('EvnQueue_id'),
					allowRedirect: false,
					userMedStaffFact: me.userMedStaffFact,
					personData: {
						Person_id: me.Person_id,
						Server_id: me.Server_id,
						PersonEvn_id: me.PersonEvn_id
					},
					callback: function() {
						grid.getStore().reload();
						sw4.showInfoMsg({
							panel: panel,
							type: 'success',
							text: 'Направление удалено',
							hideDelay: 2000
						});
					}
				};

				var piPanel = me.ownerWin.PersonInfoPanel;
				if (piPanel && piPanel.getFieldValue('Person_Surname')) {
					params.personData.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
					params.personData.Person_Surname = piPanel.getFieldValue('Person_Surname');
					params.personData.Person_Firname = piPanel.getFieldValue('Person_Firname');
					params.personData.Person_Secname = piPanel.getFieldValue('Person_Secname');
					params.personData.Person_IsDead = piPanel.getFieldValue('Person_IsDead');
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
					return false;
				}

				sw.Promed.Direction.cancel(params);
			},
			openEvnDirectionEditWindow: function(record, action) {
				if (!record) {
					return false;
				}
				var EvnDirection_id = record.get('EvnDirection_id');
				if (!EvnDirection_id) {
					return false;
				}
				
				if (record.get('EvnPrescrVK_id')) {
					getWnd('swEvnPrescrVKWindow').show({
						EvnPrescrVK_id: record.get('EvnPrescrVK_id'),
						action: 'edit'
					});
					return true;
				}
				
				var onEvnDirectionSave = function(data) {
					if (!data || !data.evnDirectionData) {
						return false;
					}

					me.DirectionStore.reload();
				};

				formParams = {
					EvnDirection_id: record.get('EvnDirection_id'),
					Person_id: record.get('Person_id'),
					Server_id: record.get('Server_id'),
					Lpu_gid: record.get('Lpu_gid'),
					EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
					EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
					EvnDirectionCVI_id: record.get('EvnDirectionCVI_id'),
					DirType_Code: record.get('DirType_Code'),
					EvnDirectionHistologic_id: record.get('EvnDirectionHistologic_id')
				};

				if (formParams.EvnDirectionCVI_id) {
					var params = {
						action: (formParams.Lpu_gid != getGlobalOptions().lpu_id) ? 'view' : action,
						EvnDirectionCVI_id: formParams.EvnDirectionCVI_id,
						Person_id: me.Person_id,
						Server_id: me.Server_id,
						callback: onEvnDirectionSave,
						onHide: Ext.emptyFn
					};
					getWnd('swEvnDirectionCviEditWindow').show(params);
					return true;
				}

				// если направление на МСЭ, открываем соответсвующую форму
				if (formParams.EvnPrescrMse_id) {
					var params = {
						action: (formParams.Lpu_gid != getGlobalOptions().lpu_id) ? 'view' : action,
						EvnPrescrMse_id: formParams.EvnPrescrMse_id,
						Person_id: me.Person_id,
						Server_id: me.Server_id,
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
						Person_id: me.Person_id,
						Server_id: me.Server_id,
						onHide: Ext.emptyFn
					};
					getWnd('swDirectionOnHTMEditForm').show(params);
					return true;
				}

				var my_params = new Object({
					Person_id: me.Person_id,
					EvnDirection_id: formParams.EvnDirection_id,
					callback: onEvnDirectionSave,
					formParams: formParams,
					action: action
				});

				my_params.onHide = Ext.emptyFn;

				if ( formParams.EvnDirectionHistologic_id  ) {
					var action = (formParams.Lpu_gid == getGlobalOptions().lpu_id) ? 'edit' : 'view';
					formParams.Person_id = me.Person_id;
					getWnd('swEvnDirectionHistologicEditWindow').show({
						action: action,
						formParams: formParams,
						onHide: Ext.emptyFn,
						userMedStaffFact: this.userMedStaffFact,
						callback: function(){
							// --
						}.createDelegate(this)
					});
					return true;
				}

				//зачем из Ext6 вызывать формы Ext2 ?
				/*
				if(action=='view' && record.get('DirType_Code')==9)
					getWnd('swEvnDirectionEditWindowExt6').show(my_params);
				else
					getWnd('swEvnDirectionEditWindow').show(my_params);
				*/
				getWnd('swEvnDirectionEditWindowExt6').show(my_params);
			},
			listeners: {
				beforeEdit: function(grid, context) {
				
				},
				edit: function(grid, context) {
				}
			},
			store: me.DirectionStore
		});
		
		this.TimetableStore = Ext6.create('Ext6.data.Store', {
			fields: [
				// Общие поля
				{ name: 'UniqueId', type: 'int', calculate: function (data) {
					return data.TimetableResource_id ? data.TimetableResource_id : data.TimetableMedService_id;
				}},
				{ name: 'Person_id', type: 'int'},
				{ name: 'TimetableType_id', type: 'int'},
				{ name: 'IsDop', type: 'int'},
				{ name: 'class', type: 'string'},
				{ name: 'formatTime', type: 'string'},
				{ name: 'description', type: 'auto', defaultValue: null},
				{ name: 'notAccepted', mapping: 'Person_id', type: 'bool', /*defaultValue: true,*/ convert: function (Person_id) {
					return !Ext6.isEmpty(Person_id);
				}},
				{ name: 'class', mapping: 'Person_id', type: 'string', /*defaultValue: true,*/ convert: function (Person_id) {
					return Ext6.isEmpty(Person_id)?'free':'recorded';
				}},
				// Для ресурса
				{ name: 'TimetableResource_id', type: 'int'},
				{ name: 'TimetableResource_Day', type: 'int'},
				{ name: 'TimetableResource_begTime', type: 'date', dateFormat: 'Y-m-d H:i:s'},//дата выполненной услуги
				// Для службы
				{ name: 'TimetableMedService_id', type: 'int'},
				{ name: 'TimetableMedService_Day', type: 'int'},
				{ name: 'TimetableMedService_begTime', type: 'date', dateFormat: 'Y-m-d H:i:s'},//дата выполненной услуги
				],
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=TimetableMedService&m=loadTTListByDay',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			listeners: {
				load: function(store, records, successful, operation, eOpts){
					me.unmask();
				}
			}
		});

		this.TimetableDate = Ext6.create('swDateField',{
			format: 'd.m.Y',
			startDay: 1,
			hidden: true,
			minValue: new Date(),
			listeners: {
				'change': function(field,ndate,odate) {
					var displayField = me.TimetableMenu.down('#displayDate');
					displayField.setValue('<p style="text-align: center;margin: 0">' + ndate.getDate() + ' ' + me.months[ndate.getMonth()] + ', ' + ndate.toLocaleString('ru', {
						weekday: 'long'
					}) + '</p>');
				}
			}
		});

		this.TimetableMenu = Ext6.create('Ext6.menu.Menu', {
			//maxHeight: 340,
			//autoHeight: true,
			width: 570,
			layout: 'fit',
			parentStore: me.PrescrStore,
			UslugaComplex_id: null,
			onSelect: function(recTT){
				if(recTT){
					var setRec = me.PrescrStore.findRecord('UslugaComplex_id',this.UslugaComplex_id);
					if(setRec){
						setRec.set('TimetableResource_id',recTT.get('TimetableResource_id'));
						setRec.set('TimetableResource_Day',recTT.get('TimetableResource_Day'));
						setRec.set('TimetableResource_begTime',recTT.get('TimetableResource_begTime'));
						setRec.set('TimetableMedService_id',recTT.get('TimetableMedService_id'));
						setRec.set('TimetableMedService_Day',recTT.get('TimetableMedService_Day'));
						setRec.set('TimetableMedService_begTime',recTT.get('TimetableMedService_begTime'));
						setRec.commit();
						//me.PrescrStore.reconfigure();
						this.UslugaComplex_id = null;
					}
				}
			},
			items: [
				{
					xtype: 'panel',
					border: false,
					items: [
						{
							margin: '0 15 15',
							xtype: 'displayfield',
							itemId: 'annotate',
							hidden: true,
							value: '<p class="day-select-description" style="padding-left: 16px;">Примечание врача общее или на день: </p>'
						},
						{
							xtype: 'boundlist',
							cls: 'select-another-date',
							height: 166,
							border: false,
							itemId: 'timesBoundList',
							width: 570,
							store: me.TimetableStore,
							tpl: Ext6.create('Ext6.XTemplate',
								'<tpl for=".">',
								'<tpl if="notAccepted">',

								'<li role="option" class=" x-boundlist-item {class} ">',
								'<p class="date-time" style="margin: 0;">',
								'{formatTime}',
								'</p>',
								'</li>',

								'<tpl else>',

								'<tpl if="description == null">',
								'<li role="option" class=" x-boundlist-item {class} "><div data-id="{UniqueId}" class="accept-point" style="position: static;" data-qtip="Выбрать">',
								'<p class="date-time select-menu-day" style="margin: 0;">{formatTime}',
								'</p></div>',
								'<tpl else>',
								'<li role="option" class=" x-boundlist-item {class} "><div data-id="{UniqueId}" class="accept-point" style="position: static;" data-qtip="Выбрать"><p class="date-time select-menu-day" style="margin: 0;">{formatTime}</p></div>',
								'<div class="date-description" data-qtip="Текст примечания на бирку: {description}">',
								'</div>',
								'</li>',
								'</tpl>',

								'</tpl>',
								'</tpl>'),
							listeners: {
								render: function () {
									var body = this.getEl();
									body.on('click', function (e, t) {
										me.TimetableMenu.selRec = null;
										var deselectAllFn = function(){
											body.select('div.accept-point').elements.forEach(function (el, index) {
												el.classList.remove('accepted');
												el.setAttribute('data-qtip', 'Выбрать');
											});
										};
										if('accepted'.inlist(t.classList)){
											deselectAllFn();
										} else {
											deselectAllFn();
											t.classList.add('accepted');
											t.setAttribute('data-qtip', 'Отменить выбор');
											var UniqueId = t.getAttribute('data-id');
											me.TimetableMenu.selRec = me.TimetableStore.findRecord('UniqueId',UniqueId);
										}
										me.TimetableMenu.down("#acceptChanges").setDisabled(!('accepted'.inlist(t.classList)));

									}, null, {
										delegate: 'div.accept-point'
									})
								}
							}
						}
					]
				}],
			count: new Date().getDate(),
			dockedItems: [
				{
					xtype: 'toolbar',
					dock: 'top',
					border: false,
					items: [
						{
							xtype: 'tool',
							type: 'day-prev',
							handler: function () {
								if(me.checkAllowDayPrev()){
									me.prevDay();
									me.loadTimetable();
								}
							}
						},
						me.TimetableDate,
						{
							xtype: 'displayfield',
							value: '',
							itemId: 'displayDate',
							width: 154,
							height: 16,
							cls: 'line-date-text',
							style: {
								display: 'block'
							}
						},
						{
							xtype: 'tool',
							type: 'day-next',
							handler: function () {
								me.nextDay();
								me.loadTimetable();
							}
						}
					]
				}, {
					xtype: 'toolbar',
					dock: 'bottom',
					align: 'right',
					border: false,
					items: [{
						xtype: 'displayfield',
						hidden: true,
						value: ''
					}, '->', {
						xtype: 'button',
						itemId: 'acceptChanges',
						text: 'Выбрать',
						cls: 'btn-swMsg-main-super-flat',
						disabled: true,
						handler: function () {
							
							var selRec = me.TimetableMenu.selRec;

							//me.TimetableMenu.showTarget.innerHTML = data.dom.childNodes[0].innerHTML;
							me.TimetableMenu.onSelect(selRec);
							me.TimetableMenu.hide();

							/*Ext6.get(me.TimetableMenu.showTarget.parentNode).down('div.accept-point').addCls('accepted');
							Ext6.get(me.TimetableMenu.showTarget.parentNode).down('div.accept-point').dom.setAttribute('data-qtip', 'Отменить запись');*/
							me.TimetableMenu.selRec = null;
							this.setDisabled(true);
						}
					}, {
						xtype: 'button',
						text: 'Закрыть',
						cls: 'btn-swMsg-minor-super-flat',
						handler: function () {
							me.TimetableMenu.hide();
						}
					}]
				}]
		});
		//меню
		me.AddDirectionMenu = Ext6.create("Ext6.menu.Menu", {
			userCls: "menuWithoutIcons",
			items: [{
				text: 'На лабораторную диагностику ',
				disabled: true
			}, {
				text: 'На функциональную диагностику ',
				disabled: true,
			}, {
				text: 'На прием к врачу',
				handler: function() {
					me.createDirection({
						DirType_Code: 12,
						DirType_id: 16,
						DirType_Name: "На прием к врачу"
					}, false);
				}
			}, {
				text: 'На консультацию',
				handler: function() {
					me.createDirection({
						DirType_Code: 3,
						DirType_id: 3,
						DirType_Name: "На консультацию"
					}, false);
				}
			}]
		});

		//кнопка Добавить направление вне диспансеризации
		me.DirectionButton = Ext6.create("Ext6.Component", {
			autoEl: {
				tag: "a",
				html: "Добавить направление вне рамок диспансеризации ",
				style: "text-decoration:none; padding: 10px;"
			},
			listeners: {
				render: function(cmp){
					cmp.getEl().on({
						click: function(){
							me.AddDirectionMenu.showBy(cmp);
						}
					});
				}
			}
		});
		Ext6.apply(me, {
			tools: [
				{
					xtype: 'tbspacer',
					flex: 1
				},
				{
					type: 'prescr-print',
					tooltip: langs('Печать маршрутного листа'),
					width: 24,
					margin: 0,
					callback: function(panel, tool, event) {
						printBirt({
							'Report_FileName': 'DestinationRouteCard.rptdesign',
							'Report_Params': '&paramEvnPL=' + me.EvnPLDispDop13_id,
							//'Report_Params': '&paramEvnPL=' + this.getView().ownerPanel.EvnPL_id,
							'Report_Format': 'pdf'
						});
					}
				}
			],
			items: [me.PrescrGrid, me.DirectionButton, me.DirectionGrid]
		});
		this.callParent(arguments);
	}
});