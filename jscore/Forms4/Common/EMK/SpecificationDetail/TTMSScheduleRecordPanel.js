/**
 * TTMSScheduleRecordPanel - Панель расписания служб/ресурсов для записи назначений в ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.TTMSScheduleRecordPanel', {
	alias: 'widget.TTMSScheduleRecordPanel',
	autoShow: false,
	constrain: true,
	extend: 'base.BaseFormPanel',
	cls: 'evn-detail-record-panel',
	header: false,
	modal: true,
	refId: 'TTMSScheduleRecordPanel',
	resizable: false,
	border: false,
	autoHeight: true,
	width: '100%',
	layout: 'fit',
	data: {},
	parentPanel: {},
	show: function (data) {
		this.callParent(arguments);

		var me = this;

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		me.data = data;
		me.PrescriptionType_Code = null;
		me.MedServiceType_SysNick = null;
		switch(me.data.objectPrescribe) {
			case 'EvnCourseProc':
				me.PrescriptionType_Code = 6;
				me.MedServiceType_SysNick = 'prock';
				break;
			case 'EvnPrescrLabDiag':
				me.PrescriptionType_Code = 11;
				me.MedServiceType_SysNick = 'lab';
				break;
			case 'EvnPrescrFuncDiag':
				me.PrescriptionType_Code = 12;
				break;
			case 'EvnPrescrConsUsluga':
				me.PrescriptionType_Code = 13;
				break;
			case 'EvnPrescrOperBlock':
				me.PrescriptionType_Code = 7;
				me.MedServiceType_SysNick = 'oper';
				break;
		}
		me.Resource_id = null;
		me.MedService_id = null;
		me.MedService_pzmid = null;
		me.UslugaComplexMedService_id = null;
		me.Lpu_id = getGlobalOptions().lpu_id; // по умолчанию своя МО
		if (me.data && me.data.record){
			var rec = me.data.record;
			if(rec.get('MedService_id'))
				me.MedService_id = rec.get('MedService_id');
			if(rec.get('UslugaComplexMedService_pid'))
				me.UslugaComplexMedService_id = rec.get('UslugaComplexMedService_pid');
			if(rec.get('Lpu_id'))
				me.Lpu_id = rec.get('Lpu_id');
			// Если служба бирки отличается от службы записи - подставляем службу бирки
			if(rec.get('ttms_MedService_id') && me.MedService_id != rec.get('ttms_MedService_id')){
				if(rec.get('ttms_MedService_id') == rec.get('pzm_MedService_id') && rec.get('pzm_Lpu_id')){
					me.Lpu_id = rec.get('pzm_Lpu_id');
					me.MedService_id = rec.get('ttms_MedService_id');
				}
			}
			if(rec.get('MedService_pzmid')) {
				me.MedService_pzmid = rec.get('MedService_pzmid');
			}
			if(rec.get('Resource_id'))
				me.Resource_id = rec.get('Resource_id');
			if(rec.get('UslugaComplex_id'))
				me.UslugaComplex_id = rec.get('UslugaComplex_id');
			if(rec.get('PrescriptionType_Code') && !me.PrescriptionType_Code)
				me.PrescriptionType_Code = rec.get('PrescriptionType_Code');
		}

		me.withResource = false;
		if (me.PrescriptionType_Code == 12) {
			me.withResource = true;
			me.StudyTargetFilterCombo.show();
			me.loadStudyTarget();
		}

		if(me.PrescriptionType_Code != 12) {
			me.StudyTargetFilterCombo.hide();
		}

		if (me.withResource) {
			me.ResourceFilterCombo.show();
		} else {
			me.ResourceFilterCombo.hide();
		}

		me.Panel.setHtml('');

		me.LpuFilterCombo.setValue(me.Lpu_id);

		me.MedServiceFilterCombo.clearValue();
		me.ResourceFilterCombo.clearValue();

		me.MedServiceFilterCombo.getStore().proxy.extraParams.filterByUslugaComplex_id = me.UslugaComplex_id;
		me.MedServiceFilterCombo.getStore().proxy.extraParams.userLpuSection_id = me.data.userMedStaffFact.LpuSection_id;
		me.MedServiceFilterCombo.getStore().proxy.extraParams.PrescriptionType_Code = me.PrescriptionType_Code;
		me.ResourceFilterCombo.getStore().proxy.extraParams.UslugaComplex_id = me.UslugaComplex_id;

		me.loadMedService();
	},
	loadStudyTarget: function() {
		var me = this;
		me.StudyTargetFilterCombo.getStore().load({
			callback:function () {
				if(me.data.record.data && me.data.record.data.StudyTarget_id) {
					me.StudyTargetFilterCombo.setValue(me.data.record.data.StudyTarget_id);
				}
			}
		});
	},
	loadMedService: function() {
		var me = this;
		// загружаем список служб, которые могут выполнять данную услугу, выбираем первую службу, если нам не передали конкретную
		me.mask(LOAD_WAIT);
		me.MedServiceFilterCombo.getStore().proxy.extraParams.filterByLpu_id = me.LpuFilterCombo.getValue();
		me.MedServiceFilterCombo.getStore().load({
			callback: function() {
				me.unmask();
				var record;
				if(me.MedService_pzmid){
					record = me.MedServiceFilterCombo.getStore().findRecord('MedService_id', me.MedService_pzmid,undefined,undefined,undefined,true);
					if(!record) {
						var ind = -1;
						if(me.UslugaComplexMedService_id) {
							var ind = me.MedServiceFilterCombo.getStore().findBy(function (rec) {
								return rec.get('UslugaComplexMedService_id')==me.UslugaComplexMedService_id && me.MedService_pzmid==rec.get('pzm_MedService_id');
							});
							if(ind>=0) record = me.MedServiceFilterCombo.getStore().getAt(ind);
						}
						if(ind<0) {
							record = me.MedServiceFilterCombo.getStore().findRecord('pzm_MedService_id', me.MedService_pzmid,undefined,undefined,undefined,true);
						}
					}
				} else if(me.withResource && me.MedService_id) {
					record = me.MedServiceFilterCombo.getStore().findRecord('Resource_id', me.Resource_id,undefined,undefined,undefined,true);
				} else if(me.MedService_id) {
					record = me.MedServiceFilterCombo.getStore().findRecord('MedService_id', me.MedService_id,undefined,undefined,undefined,true);
					if(!record) {
						var ind = -1;
						if(me.UslugaComplexMedService_id) {
							var ind = me.MedServiceFilterCombo.getStore().findBy(function (rec) {
								return rec.get('UslugaComplexMedService_id')==me.UslugaComplexMedService_id && me.MedService_id==rec.get('pzm_MedService_id');
							});
							if(ind>=0) record = me.MedServiceFilterCombo.getStore().getAt(ind);
						}
						if(ind<0) {
							record = me.MedServiceFilterCombo.getStore().findRecord('pzm_MedService_id', me.MedService_id,undefined,undefined,undefined,true);
						}
					}
				}
				if(!record)
					record = me.MedServiceFilterCombo.getFirstRecord();

				if (record)
					me.MedServiceFilterCombo.setValue(record.get('UslugaComplexMedService_key'));

				if (me.withResource && !Ext6.isEmpty(me.MedServiceFilterCombo.getValue())) {
					me.mask(LOAD_WAIT);
					me.ResourceFilterCombo.getStore().load({
						callback: function() {
							me.unmask();

							// берем первый ресурс в списке или данные из записи, с которой открыли форму
							var Resource_id = (me.Resource_id)?me.Resource_id:me.ResourceFilterCombo.getFirstRecord();
							if (Resource_id) {
								me.ResourceFilterCombo.setValue(Resource_id);
							}

						}
					});
				}
			}
		});
	},
	recordPerson: function(time_id, date, time) {
		// записываем пациента
		var me = this;
		var params = me.data.record.data;
		params.PrescriptionType_Code = me.PrescriptionType_Code;
		if (me.withResource) {
			params.TimetableResource_id = time_id;
		} else {
			params.TimetableMedService_id = time_id;
		}
		if (Ext6.isEmpty(me.MedServiceFilterCombo.getValue())) {
			Ext6.Msg.alert('Ошибка', 'Не выбрано место оказания');
			return false;
		}
		params.Lpu_id = me.MedServiceFilterCombo.getFieldValue('Lpu_id');
		params.LpuUnit_id = me.MedServiceFilterCombo.getFieldValue('LpuUnit_id');
		params.LpuSection_id = me.MedServiceFilterCombo.getFieldValue('LpuSection_id');
		params.LpuSectionProfile_id = me.MedServiceFilterCombo.getFieldValue('LpuSectionProfile_id');
		params.UslugaComplexMedService_id = me.MedServiceFilterCombo.getFieldValue('UslugaComplexMedService_id');
		params.MedService_id = me.MedServiceFilterCombo.getFieldValue('MedService_id');
		params.pzm_MedService_id = me.MedServiceFilterCombo.getFieldValue('pzm_MedService_id');
		params.onSaveEvnDirection = function() {
			if (time_id) {
				// если было постановка в очередь то нет смысла грузить заново расписание
				me.loadTimetable();
			}
			me.callback({'EvnPrescr_id': params.EvnPrescr_id});
		};

		me.parentPanel.getController().saveEvnDirection(params);
	},
	clearTime: function(time_id, EvnDirection_id) {
		var me = this;
		if (EvnDirection_id) {
			getWnd('swSelectDirFailTypeWindow').show({
				time_id: time_id,
				LpuUnitType_SysNick: (me.withResource ? 'resource' : 'medservice'),
				onClear: function() {
					me.loadTimetable();
					me.callback();
				}
			});
		} else {
			Ext6.Msg.show({
				title: langs('Подтверждение'),
				msg: langs('Вы действительно желаете освободить время приема?'),
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId) {
					if (buttonId == 'yes') {
						me.mask('Отмена записи на бирку...');
						submitClearTime({
								id: time_id,
								type: (me.withResource ? 'resource' : 'medservice'),
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(options, success, response) {
								me.unmask();
								me.loadTimetable();
								me.callback();
							}.createDelegate(this),
							function() {
								me.unmask();
							}
						);
					}
				}
			});
		}
	},
	clearOwnTime: function(time_id, EvnDirection_id) {
		var me = this;
		me.mask('Отмена записи на бирку...');
		submitClearTime({
				id: time_id,
				type: (me.withResource ? 'resource' : 'medservice'),
				DirFailType_id: null,
				EvnComment_Comment: null
			},
			function(options, success, response) {
				me.unmask();
				me.loadTimetable();
				me.callback();
			}.createDelegate(this),
			function() {
				me.unmask();
			}
		);
	},
	loadTimetable: function(startDay) {
		var me = this;

		me.Panel.setHtml('');

		// Ид. лаборатории и МО, в которой находится лаборатория:
		var MedService_id = me.MedServiceFilterCombo.getFieldValue('MedService_id');
		var lpu_id = me.MedServiceFilterCombo.getFieldValue('Lpu_id');
		
		// Ид. пункта забора и МО, в которой находится пункт забора:
		var pzm_MedService_id = me.MedServiceFilterCombo.getFieldValue('pzm_MedService_id');
		var pzm_lpu_id = me.MedServiceFilterCombo.getFieldValue('pzm_Lpu_id');

		var ttms_MedService_id = me.MedServiceFilterCombo.getFieldValue('ttms_MedService_id');

		// Если есть пункт забора, причем он в той же МО, что и лаборатория, возьмем расписание пункта забора:
		if (pzm_MedService_id && (pzm_lpu_id == lpu_id)) {
			MedService_id = pzm_MedService_id;
		}
		
		// если есть бирка на пункте забора, то возьмем расписание оттуда
		if (ttms_MedService_id) {
			MedService_id = ttms_MedService_id;
		}

		if (Ext6.isEmpty(MedService_id)) {
			return false;
		}
		if(!startDay)
			startDay = getGlobalOptions().date;
		this.startDay = startDay;
		var params = {
			StartDay: startDay,
			MedService_id: MedService_id,
			EvnPrescr_id: me.data.record.get('EvnPrescr_id'),
			PanelID: me.id,
			isExt6: 1,
			readOnly: false
		};

		var url = '/?c=TimetableMedService&m=getTimetableMedService';
		if (me.withResource) {
			url = '/?c=TimetableResource&m=getTimetableResource';

			var Resource_id = me.ResourceFilterCombo.getValue();
			if (Ext6.isEmpty(Resource_id)) {
				return false;
			}
			params.Resource_id = Resource_id;
		} else {
			// если есть бирка на услуге службы, то возьмем расписание оттуда
			var ttms_UslugaComplexMedService_id = me.MedServiceFilterCombo.getFieldValue('ttms_UslugaComplexMedService_id');
			if (ttms_UslugaComplexMedService_id) {
				params.UslugaComplexMedService_id = ttms_UslugaComplexMedService_id;
				url = '/?c=TimetableMedService&m=getTimetableUslugaComplex';
			}
		}

		me.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: url,
			callback: function(opt, success, response) {
				me.unmask();
				me.Panel.setHtml(response.responseText);

				var trTimeCount = me.Panel.getEl().query('tr.time').length;
				if (trTimeCount == 0 && !me.data.record.get('EvnDirection_id')) {
					getWnd('swEmptyTimetableWindow').show({
						callback: function() {
							me.recordPerson(null);
						}
					});
				}
			},
			params: params
		});
	},
	initComponent: function() {
		var me = this;

		var dateMenu = Ext6.create('Ext6.menu.DatePicker', {
			minDate: new Date(),
			handler: function(dp, date){
				me.loadTimetable(Ext6.util.Format.date(date, 'd.m.Y'));
			}
		});

		this.functionBar =  Ext6.create('Ext6.toolbar.Toolbar', {
			flex: 1,
			itemId: 'topMainBar',
			cls: 'topMainBar',
			padding: '3 8 6',
			border: false,
			height: 21,
			xtype: 'toolbar',
			items: [
				{
					xtype: 'tbtext',
					html: '<img src="../../../../img/icons/emk/doctor-alert.png"><span style="font-size: 12px; -webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none; margin-left: 3px">Примечание врача: </span>'
				},'->',{
					xtype: 'button',
					tooltip: 'На неделю назад',
					padding: 0,
					cls: 'button-without-frame',
					iconCls: 'panicon-back-to-list',
					margin: '0 5 0 0',
					handler: function() {
						// @todo привести в удобоваримый вид
						var dateFrom = Ext6.Date.parse(me.startDay, "d.m.Y");
						var dateTo = Ext6.Date.add(dateFrom, Ext6.Date.DAY, -7);
						if(dateTo >= Ext6.Date.parse(Ext6.util.Format.date(new Date(), 'd.m.Y'),"d.m.Y"))
							me.loadTimetable(Ext6.util.Format.date(dateTo, 'd.m.Y'));

					}
				},{
					xtype: 'button',
					tooltip: 'Выбрать дату',
					padding: 0,
					cls: 'button-without-frame',
					iconCls: 'icon-datepicker-btn',
					margin: '0 5 0 0',
					menu: dateMenu,
					arrowVisible: false
				},
				{
					tooltip: 'На неделю вперед',
					iconCls: 'panicon-next-to-list',
					cls: 'button-without-frame',
					padding: 0,
					margin: '0 5 0 0',
					handler: function() {
						// @todo привести в удобоваримый вид
						var date = Ext6.Date.parse(me.startDay, "d.m.Y");
						date = Ext6.Date.add(date, Ext6.Date.DAY, 7);
						me.loadTimetable(Ext6.util.Format.date(date, 'd.m.Y'));
					},
					xtype: 'button'
				}]
		});
		me.Panel = Ext6.create('Ext6.panel.Panel', {
			autoHeight: true,
			scrollable: true,
			frame: false,
			itemId: 'Panel',
			border: false,
			tbar: this.functionBar
		});
		me.LpuFilterCombo = Ext6.create('swLpuCombo', {
			labelWidth: 30,
			labelAlign: 'right',
			width: 300,
			filterFn: function(field) {
				if (field.data.Lpu_EndDate == null) {
					return true;
				} else {
					return false;
				}
			},
			listeners: {
				'change': function(LpuCombo, newValue, oldValue) {
					var MSCombo = me.MedServiceFilterCombo,
						MSComboStore = me.MedServiceFilterCombo.getStore();
					MSCombo.lastQuery = 'This query sample that is not will never appear';
					if (newValue > 0) {
						MSComboStore.proxy.extraParams.Lpu_id = newValue;
						MSComboStore.proxy.extraParams.filterByLpu_id = newValue;
						MSComboStore.proxy.extraParams.Lpu_isAll = 0;
						if(me.MedServiceType_SysNick)
							MSComboStore.proxy.extraParams.MedServiceType_SysNick = me.MedServiceType_SysNick;
					} else {
						MSComboStore.proxy.extraParams.Lpu_id = null;
						MSComboStore.proxy.extraParams.filterByLpu_id = null;
						MSComboStore.proxy.extraParams.Lpu_isAll = 1;
					}
					if (!MSCombo.getValue()
						|| (MSCombo.getFieldValue('Lpu_id') && MSCombo.getFieldValue('Lpu_id') != newValue)) {

						MSComboStore.removeAll();
						MSComboStore.load({callback: function(records,e,success){
							if(records.length && records[0])
								MSCombo.select(records[0]);
							else
								MSCombo.clearValue();
						}});
					}
				}
			},
			listConfig:{
				minWidth: 500
			},
			fieldLabel: 'МО',
			name: 'Lpu_id'
		});

		me.MedServiceFilterCombo = Ext6.create('swMedServicePrescrCombo', {
			labelWidth: 50,
			labelAlign: 'right',
			fieldLabel: 'Место',
			listeners: {
				'change': function (combo, newValue, oldValue) {
					var Lpu_id,
						resCombo = me.ResourceFilterCombo;
					if (combo.getValue() && combo.getSelectedRecord()) {
						Lpu_id = combo.getSelectedRecord().get('Lpu_id');
						if (Lpu_id)
							me.LpuFilterCombo.setValue(Lpu_id);
					}
					if (!me.LpuFilterCombo.getValue()) {
						Lpu_id = combo.getFieldValue('Lpu_id');
						if (Lpu_id)
							me.LpuFilterCombo.setValue(Lpu_id);
					}
					resCombo.getStore().proxy.extraParams.MedService_id = combo.getFieldValue('MedService_id');
					resCombo.lastQuery = 'This query sample that is not will never appear';

					if (!resCombo.getValue()
						|| (resCombo.getFieldValue('MedService_id') && resCombo.getFieldValue('MedService_id') != newValue)) {
						resCombo.clearValue();
						resCombo.getStore().removeAll();
						resCombo.getStore().load({
							callback: function (records, e, success) {
								if (records.length && records[0])
									resCombo.select(records[0]);
							}
						});
					}

					// загружаем расписание службы
					me.loadTimetable();
				}
			}
		});

		me.ResourceFilterCombo = Ext6.create('swResourceCombo', {
			labelWidth: 45,
			margin: '0 0 0 10',
			maxWidth: 230,
			width: '100%',
			//labelAlign: 'right',
			fieldLabel: 'Ресурс',
			name: 'Resource_id',
			xtype: 'combo',
			listeners: {
				'change': function(combo, newValue, oldValue) {
					// загружаем расписание службы
					me.loadTimetable();
				}
			}
		});

		me.StudyTargetFilterCombo = Ext6.create('swStudyTargetCombo', {
			labelWidth: 70,
			margin: '0 0 0 10',
			maxWidth: 210,
			width: '100%',
			fieldLabel: 'Цель иссл.',
			name: 'StudyTarget_id',
			xtype: 'combo',
			id: 'studyTarget',
			listeners: {
				'change': function(combo, newValue, oldValue) {
					if(newValue == undefined) {
						return;
					}
				}
			}
		});

		Ext6.apply(me, {
			tbar: {
				xtype: 'toolbar',
				style: {
					'backgroundColor':'#f5f5f5;'
				},
				items: [
					me.LpuFilterCombo,
					me.MedServiceFilterCombo,
					me.ResourceFilterCombo,
					me.StudyTargetFilterCombo,
					'->',
					{
						//tooltip: 'Поставить в очередь',
						userCls: 'button-queue-add',
						text: 'Поставить в очередь',
						iconCls: 'panicon-queue-add',
						margin: '0 5 0 0',
						handler: function() {
							me.recordPerson(null);
						},
						xtype: 'button'
					}
					// пока видимо выпилим, затем добавим, если понадобится
					/*,
					{
						tooltip: 'Меню',
						userCls: 'button-without-frame',
						iconCls: 'button-tree-dots',
						xtype: 'button',
						margin: '0 21 0 0'
					}*/
				]
			},
			items: [
				me.Panel
			]
		});

		this.callParent(arguments);
	}
});