Ext6.define('common.EMK.tools.swAutoSelectDateTimeWindow', {
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.EMK.models.EvnPrescribePanelModel',
		'common.EMK.controllers.AutoSelectDateTimeCntr'
	],
	extend: 'base.BaseForm',
	maximized: true,
	itemId: 'autodt',
	callback: Ext6.emptyFn,
	isLoading: false,
	historyGroupMode: null,
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	controller: 'AutoSelectDateTimeCntr',
	listeners: {
		'hide': 'onHide'
	},
	/* свойства */
	alias: 'widget.swAutoSelectDateTimeWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new auto-select-date-time-window',
	constrain: true,
	autoHeight: true,
	findWindow: false,
	header: false,
	modal: false,
	layout: 'border',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	width: 1000,
	manyDrug: false,
	title: 'подбор времени записи',
	iconCls: 'auto-dt-select-icon',
	evnPrescrCntr: null,
	selectTTRec: null,
	/*Методы*/
	show: function (data) {
		this.callParent(arguments);
		var win = this,
			cntr = win.getController();
		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "' + this.title + '".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		win.action = (typeof data.action == 'string' ? data.action : 'add');
		win.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		win.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
		win.data = data;
		//log(arguments);

		this.historyGroupMode = null;
		this.currentNode = null;
		this.historyFilterClassChecked = [];

		this.Person_id = data.Person_id;
		this.PersonEvn_id = data.PersonEvn_id;
		this.Server_id = data.Server_id;
		this.userMedStaffFact = data.userMedStaffFact;
		this.openEvn = data.openEvn || null;
		this.evnPrescrCntr = data.evnPrescrCntr || null;
		this.useArchive = false;

		this.TimetableGraf_id = null;
		if (data.TimetableGraf_id) {
			this.TimetableGraf_id = data.TimetableGraf_id;
		}


		this.PersonInfoPanel.load({
			noToolbar: true,
			Person_id: this.Person_id,
			Server_id: this.Server_id,
			userMedStaffFact: this.userMedStaffFact,
			PersonEvn_id: this.PersonEvn_id,
			callback: function () {
				// эх
			}
		});

		var conf = {
			userMedStaffFact: data.userMedStaffFact,
			Person_id: data.Person_id,
			PersonEvn_id: data.PersonEvn_id,
			Server_id: data.Server_id,
			Evn_id: data.Evn_id,
			Evn_setDate: data.Evn_setDate,
			LpuSection_id: data.LpuSection_id,
			MedPersonal_id: data.MedPersonal_id,
			Diag_id: data.Diag_id,
			/*callback: function() {
				cntr.loadGrids();
			}*/
		};
		// cntr.loadData(conf);

		//win.center();
		//win.setTitle('Тариф ТФОМС');

		//var base_form = win.FormPanel.getForm();
		//base_form.reset();
		//base_form.setValues(win.formParams);

		//this.ViewPrescrPanel.setHeight(ViewPrescrGridsPanel.getHeight())
		/*cntr.onExpandPrescribePanel();
		if(data.prescribe){
			var grid = cntr.getGridByObject(data.prescribe);
			cntr.openSpecification(data.prescribe, grid, data.record)
		}
		else{
			cntr.openSpecification();
		}*/
		cntr.loadUslugaGrid(conf);
	},
	addToQueue: function(e,t){
		var me = this;
		var text = Ext6.get(t.previousSibling);
		var parent = Ext6.get(t.parentNode);
		var link = Ext6.get(t);
		if(me.selectTTRec){
			var params = me.selectTTRec.getData();
			// Чтобы вдруг на бирку не угодить, удаляем (мы пишем в очередь)
			delete(params.TimetableMedService_id);
			delete(params.TimetableResource_id);
			params.onSaveEvnDirection = function(){
				text.setHtml('Пациент поставлен в очередь ' + new Date().toLocaleDateString());
				text.setStyle({
					color: '#fff'
				});
				parent.addCls('add-in-queue accepted');
				parent.last().remove();
				link.remove();
				parent.createChild('<a href="#" class="cancel-changes" style="color: #fff">Отмена</a>')
			};
			me.evnPrescrCntr.saveEvnDirection(params)
		}

	},
	cancelQueue: function(e,t){
		var me = this;
		var EvnPrescr_id = me.selectTTRec.get('EvnPrescr_id');
		var cbFn = function () {
			var rec = me.UslugaGridStore.findRecord('EvnPrescr_id', EvnPrescr_id);
			me.UslugaGridStore.remove(rec);
		};

		me.cancelPrescr(me.selectTTRec,cbFn);
		me.selectTTRec = null;
	},
	cancelTTRecord: function(rec){
		var me = this,
			EvnPrescr_id = rec.get('EvnPrescr_id'),
			cbFn = function () {
				rec = me.UslugaGridStore.findRecord('EvnPrescr_id', EvnPrescr_id);
				me.UslugaGridStore.remove(rec);
			};
		me.cancelPrescr(rec,cbFn);
	},
	cancelPrescr: function (rec, cbFn) {
		var me = this;
		if(!rec)
			return false;
		//parentEvnClass_SysNick: EvnVizitPL
		var params = {
			PrescriptionType_id: rec.get('PrescriptionType_id'),
			parentEvnClass_SysNick: 'EvnVizitPL',
			EvnPrescr_id: rec.get('EvnPrescr_id')
		};

		me.mask('Отмена направления...');

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
				me.unmask();
				cbFn();
			}
		});
	},
	getTimetableNoLimit: function(e, t) {
		var me = this;

		if(me.selectTTRec){
			me.mask('Получение первой свободной бирки...');
			Ext6.Ajax.request({
				url: '/?c=MedService&m=getTimetableNoLimit',
				callback: function(opt, success, response) {
					me.unmask();
					me.selectTTRec = me.UslugaGridStore.findRecord('EvnPrescr_id', me.selectTTRec.get('EvnPrescr_id'));
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.success) {
							me.selectTTRec.set('TimetableMedService_id', response_obj.TimetableMedService_id,{silent: true});
							me.selectTTRec.set('TimetableMedService_begTime', response_obj.TimetableMedService_begTime,{silent: true});
							me.selectTTRec.set('ttms_MedService_id', response_obj.ttms_MedService_id,{silent: true});
							me.selectTTRec.set('TimetableResource_id', response_obj.TimetableResource_id,{silent: true});
							me.selectTTRec.set('TimetableResource_begTime', response_obj.TimetableResource_begTime,{silent: true});
							me.selectTTRec.set('Resource_id', response_obj.Resource_id,{silent: true});
							me.selectTTRec.set('Resource_Name', response_obj.Resource_Name,{silent: true});
							me.selectTTRec.set('ttr_Resource_id', response_obj.ttr_Resource_id,{silent: true});
						}
						me.selectTTRec.set('nolimit', 1);
						//me.selectTTRec.commit();
					}
				},
				params: {
					UslugaComplexMedService_id: me.selectTTRec.get('UslugaComplexMedService_id'),
					pzm_MedService_id: me.selectTTRec.get('pzm_MedService_id'),
					Resource_id: me.selectTTRec.get('Resource_id'),
					PrescriptionType_Code: me.PrescriptionType_Code
				}
			});
		}
	},
	showSelCellMenu: function(rec,StartDay,el){
		var me = this;

		if(typeof rec === 'number')
			rec = me.UslugaGridStore.getAt(rec);
		me.selectedCellsMenu.clickTTRec = rec || null;
		me.selectedCellsMenu.StartDay = StartDay;

		me.selectedCellsMenu.showBy(el);
		me.selectedCellsMenu.items.each(function(menuItem){
			switch (menuItem.name) {
				case 'recToDT':
					menuItem.setDisabled(rec.get('EvnDirection_id'));
					break;
				case 'cancelRec':
					menuItem.setDisabled(!rec.get('EvnDirection_id'));
					break;
				case 'openTT':
					menuItem.setDisabled(rec.get('EvnDirection_id'));
					break;
			}
		});
	},
	// отрисовка каждой из ячеек
	renderCell: function(o){
		var data = o.data;
		var cellDOM = '<div class="accept-point date-time '+(o.accepted?'accepted':'')+'" data-qtip="Отменить запись"></div>';
		cellDOM +=
			'<p data-UniqueId="'+data.EvnPrescr_id+'" class="date-time custome-naznach" style="margin: 0;" data-qtip="'+o.qT+'">' +
			o.dt +
			'</p>';
		if(o.annotate){
			cellDOM += '<div class="date-description" data-qtip="' + o.annotate + '"></div>';
		}
		return cellDOM;
	},
	// отрисовка строки без ячеек (во всю ширину normalgrid)
	renderRow: function(o){
		var me = this;
		var data = o.data;

		var cellDOM = '<div ' + (o.cls?('class="'+o.cls+'"'):'') + ' style="width: 100%;">';
		cellDOM += '<p style="' + (o.cancel?'color: #fff;':'') + '  margin: 0 10px 0 7px; display: inline-block" class="no-range">'+o.label+'</p>';

		if(o.cancel)
			cellDOM += '<a href="#" class="cancel-changes" style="color: #fff">Отмена</a>';
		if(o.queue)
			cellDOM += '<a href="#" class="add-to-queue">Поставить в очередь</a>';
		if(o.over){
			switch(o.over){
				case 'over':
					cellDOM += '<a href="#" style="margin-right: 10px;float: right" class="load-new-TTObjects">Уточнить далее 14 дней</a>';
					break;
				case 'overNoTT':
					cellDOM += '<p style="float: right; margin-right: 10px">Нет бирок</p>';
					break;
				default:
					cellDOM += '<a style="margin-right: 10px;float: right" href="#" ' +
						'onclick="Ext6.getCmp(\'' + me.id + '\').showSelCellMenu('+o.rowIndex+', \''
						+ o.dt.format('d.m.Y') +
						'\', this)">' + o.dt.format('d.m.Y D H:i').toLowerCase() + '</a>';
			}
		}

		cellDOM +='</div>';

		return cellDOM;
	},
	rendererTTCells: function(value, meta, rec, rowIndex, colIndex, store, view){
		var me = this;
		var data = rec.getData();
		var hasData = Ext6.isObject(value);
		var result = '', annotate = '',
			dt, begTime;
		if(meta.column && meta.column.dataIndex && rec.get(meta.column.dataIndex) && rec.get(meta.column.dataIndex).annotate){
			annotate = rec.get(meta.column.dataIndex).annotate;
		}
		if (data.EvnStatus_SysNick === 'DirZap' && (rec.get('ED_TimetableResource_begTime') || rec.get('ED_TimetableMedService_begTime'))) {

			begTime = (rec.get('ED_TimetableResource_begTime'))?rec.get('ED_TimetableResource_begTime'):rec.get('ED_TimetableMedService_begTime');
			//dt = Date.parseDate(begTime, 'd.m.Y H:i').format('Ymd');
			dt = Date.parseDate(begTime, 'd.m.Y H:i');

			if(meta.column && meta.column.dataIndex === dt.format('Ymd')){
				// День записи
				meta.tdStyle = 'background-color: #e0f1e1; text-align: center;';
				meta.tdCls = 'accept-date';
				return me.renderCell({
					dt: dt.format('H:i'),
					accepted: true,
					data: data,
					qT: 'Открыть расписание на день',
					annotate: annotate
				});
			} else {
				// Остальное расписание
				if (hasData) {
					meta.tdStyle = 'background-color: #e0f1e1; text-align: center;';
					meta.tdCls = 'accept-date dirZap';
					result = me.renderCell({
						dt: value.formatTime,
						data: data,
						qT: 'Услуга уже записана',
						annotate: annotate
					});
				} else {
					var now = new Date();
					now.setDate(now.getDate() + 14);
					if(dt > now){
						if(colIndex === 0) {
							meta.tdCls = 'no-data';
							result = me.renderRow({
								cls: 'accepted add-in-queue',
								label: ('Пациент записан на ' + begTime),
								cancel: true
							});
						}
					} else {
						meta.tdStyle = 'background-color: #ffcdd2';
					}

				}
				return result;
			}
		}
		if (data.EvnStatus_SysNick === 'Queued' && colIndex === 0) {
			meta.tdCls = 'no-data';
			return me.renderRow({
				cls: 'accepted add-in-queue',
				label: 'Пациент поставлен в очередь',
				cancel: true
			});
		}
		if (data.noTT && colIndex === 0) {
			var params = {
				label: 'Нет расписания',
				queue: true,
				over: 'over',
				rowIndex: rowIndex
			};
			meta.tdCls = 'no-data';
			if(data.nolimit){
				begTime = data.TimetableResource_begTime ? data.TimetableResource_begTime : data.TimetableMedService_begTime;
				if(begTime){
					params.over = 'overTT';
					params.dt = Date.parseDate(begTime, 'd.m.Y H:i');
				}
				else
					params.over = 'overNoTT';
			}
			return me.renderRow(params)
		}

		if (data.phone === null && colIndex === 0 || data.phone === '' && colIndex === 0) {
			result = '<div style="width: 100%;"><p style="margin: 0 10px 0 7px; display: inline-block" class="text-alert">Не выбрано место оказания услуги</p></div>';
			meta.tdCls = 'no-data';
			return result
		}

		if (data.noFreeTTObjects && colIndex === 0) {
			result = '<div style="width: 100%"><p style="margin: 0 10px 0 7px; display: inline-block" class="no-free-TTObjects">Нет свободных бирок</p><a href="#" class="add-to-queue">Поставить в очередь</a><a href="#" style="margin-right: 10px;float: right" class="load-new-TTObjects">Уточнить далее 14 дней</a></div>';
			meta.tdCls = 'no-data';
			return result
		}
		if (data.onlyInReg && colIndex === 0) {
			result = '<div style="width: 100%;"><p style="margin: 0 10px 0 7px; display: inline-block" class="text-alert">Запись только в регистратуре</p></div>';
			meta.tdCls = 'no-data';
			return result
		}
		if (data.noTT || data.noFreeTTObjects || data.onlyInReg || data.phone === null || data.phone === '') {
			return result
		}


		// Пока публикуем с выходными
		// if (dates[meta.cellIndex].day === 0 || dates[meta.cellIndex].day === 6) {
		// 	return result
		// } else {
		if (hasData) {
			meta.tdStyle = 'background-color: #e0f1e1; text-align: center;';
			meta.tdCls = 'accept-date allowRec';
			return me.renderCell({
				dt: value.formatTime,
				data: data,
				qT: 'Открыть расписание на день',
				annotate: annotate
			});
		} else {
			meta.tdStyle = 'background-color: #ffcdd2';
			return result;
		}
		// }
	},
	addColums: function () {
		var me = this,
			grid = me.AutoDateTimeUslugaGrid,
			names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
			date = new Date(),
			dates = [], col = {};
		if (grid) {
			col = {
				resizable: false,
				width: 70,
				cls: 'with-right-border date-header',
				tdCls: 'with-right-border',
				//bodyPadding: '6px 10px 7px 10px',
				xtype: 'gridcolumn',
				//toolColType: 'dynamic',
				html: '',
				sortable: false,
				style: {
					height: '40px'
				},
				menuDisabled: true,
				number: 1,
				getEditor: function () {
					return;
				},
				listeners: {
					click: function (view,el,recIndex,colIndex,eOpts,rec,domEl) {
						var cols = view.getGridColumns(),
							col, StartDay;
						if(rec.get('noTT')){
							me.selectTTRec = rec;
						} else {
							if(cols.length > 0)
								col = cols[colIndex];
							if(col && col.dataIndex)
								StartDay = Ext6.Date.parseDate(col.dataIndex,'Ymd');

							var begTime = (rec.get('ED_TimetableResource_begTime'))?rec.get('ED_TimetableResource_begTime'):rec.get('ED_TimetableMedService_begTime');
							//dt = Date.parseDate(begTime, 'd.m.Y H:i').format('Ymd');
							var dt = Date.parseDate(begTime, 'd.m.Y H:i');
							if((!rec.get('EvnDirection_id') || col.dataIndex === dt.format('Ymd')) && rec.get(col.dataIndex)){
								me.showSelCellMenu(rec,StartDay,el);
							} else {
							}

						}

						//stopEvent: true,
						// stopPropagation: true

					}
				},
				renderer: function (value, meta, rec, rowIndex, colIndex, store, view) {
					return me.rendererTTCells(value, meta, rec, rowIndex, colIndex, store, view);
				}
			};
			while (dates.length !== 14) {
				dates.push({
					date: date.getDate(),
					month: date.toLocaleString('ru', {
						month: 'short'
					}),
					dateName: names[date.getDay()],
					day: date.getDay(),
					dataIndex: date.dateFormat('Ymd')
				});
				date.setDate(date.getDate() + 1);
			}

			dates.forEach(function (el) {
				if (el.date === new Date().getDate()) {
					if (el.day === 0 || el.day === 6) {
						col.html = '<p style="text-align: center; color: #ff0000; margin-bottom: 0px;">' + el.date + ' ' + el.month + '</p><p style="text-align: center; color: #ff0000"> сегодня </p>'
					} else {
						col.html = '<p style="text-align: center; margin-bottom: 0px;">' + el.date + ' ' + el.month + '</p><p style="text-align: center;"> сегодня </p>';
					}
				} else {
					if (el.day === 0 || el.day === 6) {
						col.html = '<p style="text-align: center; color: #ff0000; margin-bottom: 0px;">' + el.date + ' ' + el.month + '</p><p style="text-align: center; color: #ff0000">' + el.dateName + '</p>'
					} else {
						col.html = '<p style="text-align: center; margin-bottom: 0px; " >' + el.date + ' ' + el.month + '</p><p style="text-align: center;">' + el.dateName + '</p>';
					}
				}
				col.dataIndex = el.dataIndex;
				var column = Ext6.create('Ext6.grid.column.Column', col);
				me.normalGrid.headerCt.insert(me.normalGrid.getColumns().length, column);

			});
		}
	},
	loadTimetable: function(records) {
		var me = this;
		var arrRes = [],
			arrMS = [],
			arrUsl = [];
		if(Ext6.isEmpty(records)){
			me.unmask();
			return false;
		}
		// Формирование массива идентификаторов служб, ресурсов или услуг, для которых грузим расписание
		records.forEach(function(rec){
			// Если ресурс есть - 100% функц.диагн. добавляем
			if (!Ext6.isEmpty(rec.get('Resource_id'))) {
				if (rec.get('Resource_id') && !rec.get('Resource_id').inlist(arrRes))
					arrRes.push(rec.get('Resource_id'));
			} else {
				// Кроме функциональной есть лаб.диаг. и остальные
				if (!!(rec.get('is_pzm'))) {
					// Значит бирка на пункте забора

					if (!!(rec.get('by_Usl'))) { // Если бирка из пункта забора да еще и на услуге пункта забора
						if (rec.get('pzm_UslugaComplexMedService_id') && !rec.get('pzm_UslugaComplexMedService_id').inlist(arrUsl))
							arrUsl.push(rec.get('pzm_UslugaComplexMedService_id'));   // тащим расписание с услуги пункта забора
					} else {
						if (rec.get('pzm_MedService_id') && !rec.get('pzm_MedService_id').inlist(arrMS))
							arrMS.push(rec.get('pzm_MedService_id'));  // тащим расписание с пункта забора
					}

				} else {
					// Значит бирка на службе

					if (!!(rec.get('by_Usl'))) { // Если бирка со службы да еще и на услуге службы
						if (rec.get('UslugaComplexMedService_id') && !rec.get('UslugaComplexMedService_id').inlist(arrUsl))
							arrUsl.push(rec.get('UslugaComplexMedService_id'));   // тащим расписание с услуги службы
					} else {
						if (rec.get('MedService_id') && !rec.get('MedService_id').inlist(arrMS))
							arrMS.push(rec.get('MedService_id'));  // тащим расписание со службы
					}

				}
			}
		});


		var params = {
			StartDay: getGlobalOptions().date,
			arrRes:  Ext6.encode(arrRes),
			arrMS: Ext6.encode(arrMS),
			arrUsl: Ext6.encode(arrUsl)
		};
		var url = '/?c=TimetableMedService&m=loadAllUslugaTTList';

		me.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: url,
			callback: function(opt, success, response) {
				me.unmask();
				me.loadTTDataToStore(response,records);

			},
			params: params
		});
	},
	loadTTDataToStore: function(response,records){
		var me = this,
			res;
		var tt = Ext6.JSON.decode(response.responseText);
		if(tt && tt.success && tt.data){
			var data = tt.data;
			if(!records)
				records = me.UslugaGridStore.getRange();
			var ngrid = me.normalGrid,
				cm = ngrid.getColumnManager(),
				cols = cm.getColumns();
			records.forEach(function(rec){
				var ms = null,
					ucms = null;
				switch(rec.get('object')){
					case 'EvnPrescrFuncDiag':
						res = rec.get('Resource_id');
						if(data.arrRes && !Ext6.isEmpty(data.arrRes[res])){
							rec.set('noTT',false,{silent: true});
							cols.forEach(function (col) {
								if (col.dataIndex) {
									if (!Ext6.isEmpty(data.arrRes[res][col.dataIndex])){
										rec.set(col.dataIndex, data.arrRes[res][col.dataIndex],{silent: true});
									}
									else {
										if(!Ext6.isEmpty(rec.get(col.dataIndex)))
											rec.set(col.dataIndex,'',{silent: true});
									}
								}
							});
						} else {
							// нет бирок на ресурсе
							rec.set('noTT',true,{silent: true});
						}
						break;
					/*case 'EvnPrescrLabDiag':
						res = rec.get('MedService_id');
						var pzm = rec.get('pzm_MedService_id');
						var tt = rec.get('ttms_MedService_id');
						var v = '';
						cols.forEach(function (col) {
							if(data.arrMS[res])
								v = data.arrMS[res][col.dataIndex];
							if(data.arrMS[pzm])
								v = data.arrMS[pzm][col.dataIndex];
							if(data.arrMS[tt])
								v = data.arrMS[tt][col.dataIndex];
							rec.set(col.dataIndex,data.arrMS[res][col.dataIndex]);
						});
						rec.commit();
						break;*/
					default:
						if(!!(rec.get('is_pzm'))){
							// Значит бирка на пункте забора
							if(!!(rec.get('by_Usl'))) // Если бирка из пункта забора да еще и на услуге пункта забора
								ucms = rec.get('pzm_UslugaComplexMedService_id'); // тащим расписание с услуги пункта забора
							else
								ms = rec.get('pzm_MedService_id'); // тащим расписание с пункта забора
						} else {
							// Значит бирка на службе
							if(!!(rec.get('by_Usl'))) // Если бирка со службы да еще и на услуге службы
								ucms = rec.get('UslugaComplexMedService_id');   // тащим расписание с услуги службы
							else
								ms = rec.get('MedService_id');  // тащим расписание со службы
						}

						/*if((data.arrMS && !Ext6.isEmpty(data.arrMS[ms])) || (data.arrMS && !Ext6.isEmpty(data.arrMS[ms]))){

						}*/
						if(ms){
							if(data.arrMS && !Ext6.isEmpty(data.arrMS[ms])){
								rec.set('noTT',false,{silent: true});
								// Бирки на службе имеются
								cols.forEach(function (col) {
									if (col.dataIndex) {
										if (!Ext6.isEmpty(data.arrMS[ms][col.dataIndex]))
											rec.set(col.dataIndex, data.arrMS[ms][col.dataIndex],{silent: true});
										else {
											if(!Ext6.isEmpty(rec.get(col.dataIndex)))
												rec.set(col.dataIndex,{silent: true});
										}
									}
								});
							} else {
								// нет бирок на службе
								rec.set('noTT',true,{silent: true});
							}
						}
						else{
							if(data.arrMS && !Ext6.isEmpty(data.arrMS[ms])){
								rec.set('noTT',false,{silent: true});
								// Бирки на услуге имеются
								cols.forEach(function (col) {
									if (col.dataIndex) {
										if (!Ext6.isEmpty(data.arrUsl[ucms][col.dataIndex]))
											rec.set(col.dataIndex, data.arrUsl[ucms][col.dataIndex],{silent: true});
										else{
											if(!Ext6.isEmpty(rec.get(col.dataIndex)))
												rec.set(col.dataIndex,{silent: true});
										}
									}
								});
							} else {
								// нет бирок на услуге
								rec.set('noTT',true,{silent: true});
							}
						}
				}
				//rec.commit();
			});
			ngrid.reconfigure();
			ngrid.unmask();
		}
	},
	convertDate: function(type,date){
		var newDate;
		switch(type){
			case 'StartDay': {
				switch (typeof date) {
					case 'object':
						newDate = date;
						break;
					case 'string':
						newDate = Date.parseDate(date, 'd.m.Y');
						break;
				}
				break;
			}
			case 'Timetable_begTime': {
				switch (typeof date) {
					case 'object':
						newDate = date.format('d.m.Y H:i');
						break;
					case 'string':
						newDate = date;
						break;
				}
				break;
			}
			default:
				newDate = date;
		}
		return newDate;
	},
	doApply: function(options) {
		if (!options) {
			options = {};
		}
		var me = this;
		var rec = options.rec;
		if (!rec) {
			return false;
		}

		var StartDay = me.convertDate('StartDay',options.StartDay);

		var params = rec.getData();

		if(StartDay && StartDay.format('Ymd') && params[StartDay.format('Ymd')]){
			var index = StartDay.format('Ymd');
			Ext6.apply(params,params[index]);
			if(params[index].MedService_id)
				params.ttms_MedService_id = params[index].MedService_id;
			// Сохраняем в record время записи (выбранное время для записи)
			if(params[index].TimetableResource_begTime){
				rec.set('TimetableResource_begTime',me.convertDate('Timetable_begTime',params[index].TimetableResource_begTime),{silent: true});
			}
			if(params[index].TimetableMedService_begTime ){
				rec.set('TimetableMedService_begTime',me.convertDate('Timetable_begTime',params[index].TimetableMedService_begTime),{silent: true});
			}
		}
		me.AutoDateTimeUslugaGrid.mask('Запись на бирку');
		params.onSaveEvnDirection = function(data){
			me.AutoDateTimeUslugaGrid.unmask();
			// Так как грид обновлять не будет, все важные для отображения значения меняем вручную
			rec = me.UslugaGridStore.findRecord('EvnPrescr_id',rec.get('EvnPrescr_id'));
			rec.set('EvnDirection_id', data.EvnDirection_id,{silent: true});
			rec.set('EvnStatus_SysNick', 'DirZap',{silent: true});
			var begTime = (rec.get('TimetableResource_begTime'))?rec.get('TimetableResource_begTime'):rec.get('TimetableMedService_begTime');
			// Не важно куда записать время текущей записи - хоть для ресурсов, хоть для служб - оно отобразится
			rec.set('ED_TimetableResource_begTime', begTime,{silent: true});
			rec.commit();
		};
		me.evnPrescrCntr.saveEvnDirection(params);

	},
	updateRowTT: function(rec){
		var me = this,
			records = [];
		records.push(rec);
		me.loadTimetable(records);
	},
	/*Констурктор*/
	initComponent: function () {
		var me = this,
			cntr = me.getController();
		var messageTpl = '<div>' +
			'<p>Предлагаемые бирки выделены синей рамкой. Если какие то из них вас не устраивают выберите бирки вручную и снова включите автоподбор</p>' +
			'</div>';
		var selectTpl = '<tpl for="."><div>' +
			'<p>После нажатия применить Будет создано: 9 записей с указанием времени приема и два направления с постановкой в очередь</p>' +
			'</div></tpl>';
		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			buttonPanel: false,
			border: false,
			height: 60,
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});
		this.HeaderToolbarPanel = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'top',
			height: 60,
			cls: 'auto-select-date-time-header-panel',
			style: {
				background: '#f5f5f5'
			},
			defaults: {
				labelStyle: ' padding-top:4px'
			},
			items: [{
				xtype: 'button',
				text: 'Добавить услугу',
				iconCls: 'usluga-add',
				cls: 'button-without-frame',
				menu: []
			}, '-', {
				xtype: 'checkboxfield',
				boxLabel: 'Искать по всем местам оказания'
			}, '-', {
				xtype: 'textfield',
				labelWidth: 113,
				width: 113 + 52,
				fieldLabel: 'Интервал (дней)',
				value: 14,
				listeners: {
					'keyup': function (field, e) {}
				}
			}, '-', {
				xtype: 'combobox',
				fieldLabel: 'Сортировать',
				labelWidth: 90,
				width: 90 + 200
			}, {
				xtype: 'button',
				cls: 'button-primary',
				text: 'Автоподбор',
				iconCls: 'start-auto-complete',
				handler: function () {
					me.FooterToolbarPanel.show();
					me.CompleteMsg.show();
				}
			}]
		});

		this.CompleteMsg = Ext6.create('Ext6.panel.Panel', {
			flex: 1,
			dock: 'bottom',
			hidden: true,
			height: 32,
			html: '<div class="auto-date-time-select-complete-text-container">' +
			'<div class="auto-date-time-select-complete-icon"></div>' +
			'<span class="auto-date-time-select-complete-text">Автоподбор выполнен</span>' +
			'</div>'
		});

		this.FooterToolbarPanel = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'bottom',
			ui: 'footer',
			height: 100,
			hidden: true,
			cls: 'auto-select-date-time-window-footer',
			layout: {
				pack: 'center'
			},
			border: false,
			padding: 0,
			defaults: {
				margin: '0 30',
				bodyStyle: {
					backgroundColor: 'transparent'
				},
				border: false
			},
			items: [
				{
					xtype: 'button',
					text: 'Обновить',
					handler: function(){
						me.UslugaGridStore.reload();
					}
				},
				{
				xtype: 'panel',
				width: 326,
				html: messageTpl
			}, '-', {
				xtype: 'panel',
				width: 340,
				html: selectTpl
			}, '-', {
				xtype: 'panel',
				items: [{
					xtype: 'button',
					text: 'Применить',
					cls: 'button-primary',
					handler: function () {
						me.FooterToolbarPanel.hide();
						me.CompleteMsg.hide();
						me.close()
					}
				}, {
					xtype: 'button',
					margin: '0 0 0 10',
					text: 'Отмена',
					cls: 'button-secondary',
					handler: function () {
						me.FooterToolbarPanel.hide();
						me.CompleteMsg.hide();
					}
				}]
			}]
		});
		this.UslugaGridStore = Ext6.create('Ext6.data.Store', {
			model: 'common.EMK.models.EvnPrescrUsluga',
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=MedService&m=loadEvnPrescrUslugaList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			listeners: {
				load: function(store, records, successful, operation, eOpts){
					me.unmask();
					me.normalGrid.mask('Загрузка расписания');
					setTimeout(function(){
						me.normalGrid.unmask();
					},10000);
					me.loadTimetable(records);
				}
			}
		});

		this.TitetableSelectionPanel = Ext6.create('common.EMK.tools.swTimetableSelectionWindow', {
			parentPanel: me,
			reference: 'TimetablePanel',
			onSelect: function(selRec,EvnPrescr_id){
				var dataIndex = selRec.get('dataIndex');
				if(!dataIndex){
					// Если все плохо...
					dataIndex = this.StartDay.replace(/(\d+)[.]?(\d+)[.]?(\d+)/, '$3$2$1');
				}
				var clickTTRec = this.clickTTRec;
				if(EvnPrescr_id){
					// вдруг все снова плохо
					clickTTRec = me.UslugaGridStore.findRecord('EvnPrescr_id',EvnPrescr_id);
				}
				clickTTRec.set(dataIndex,selRec.getData());
			}
		});

		this.AutoDateTimeUslugaGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			draggable: false,
			frame: false,
			cls: 'auto-select-window-usluga-grid evnPrescribeGrid',
			viewModel: true,
			forceFit: true,
			enableLocking: true,
			height: 200,
			width: 400,
			plugins: {
				cellediting: {
					clicksToEdit: 1
				}
			},
			lockedGridConfig: {
				minWidth: 640,
				maxWidth: 935
			},
			normalGridConfig: {
				minWidth: 360,
				userCls: 'schedule-custome'
			},
			columns: [
				{
					text: 'Добавить услугу:<div class="icon-prescr-add icon-EvnPrescrLabDiag"></div><div class="icon-prescr-add icon-EvnPrescrFuncDiag"></div><div class="icon-prescr-add icon-EvnPrescrConsUsluga"></div>',
					dataIndex: 'UslugaComplex_Name',
					minWidth: 250,
					maxWidth: 500,
					flex: 2,
					autoSizeColumn: true,
					locked: true,
					xtype: 'gridcolumn',
					renderer: function (value, meta, record) {
						var data = record.getData();
						switch (data.object) {
							case 'EvnPrescrProc':
								return '<div class="icon-prescr-add icon-EvnPrescrProc" style="margin: 0 10px 0 15px;position: relative; top: 3px;"></div>' + value;
							case 'EvnPrescrFuncDiag':
								return '<div class="icon-prescr-add icon-EvnPrescrFuncDiag" style="margin: 0 10px 0 15px;position: relative; top: 3px;"></div>' + value;
							case 'EvnPrescrLabDiag':
								return '<div class="icon-prescr-add icon-EvnPrescrLabDiag" style="margin: 0 10px 0 15px;position: relative; top: 3px;"></div>' + value;
							case 'EvnPrescrConsUsluga':
								return '<div class="icon-prescr-add icon-EvnPrescrConsUsluga" style="margin: 0 10px 0 15px;position: relative; top: 3px;"></div>' + value;
							default:
								return value;
						}
					}
				},
				{
					xtype: 'actioncolumn',
					cls: 'header-icon',
					dataIndex: 'EvnPrescr_IsCito',
					resizable: false,
					width: 20,
					text: '<div class="auto-select-date-grid-header-icon-cito"></div>',
					//dataIndex: 'EvnPrescr_IsCito',
					sortable: false,
					menuDisabled: true,
					locked: true,
					items: ['@isCito']
				},
				{
					xtype: 'actioncolumn',
					cls: 'header-icon',
					width: 20,
					sortable: false,
					menuDisabled: true,
					resizable: false,
					text: '<div class="auto-select-date-grid-header-icon-direction"></div>',
					locked: true,
					items: ['@isDirection']
				},
				{
					xtype: 'actioncolumn',
					cls: 'header-icon',
					width: 20,
					sortable: false,
					menuDisabled: true,
					resizable: false,
					text: '<div class="auto-select-date-grid-header-icon-other-mo"></div>',
					locked: true,
					items: ['@isOtherMO']
				},
				{
					xtype: 'gridcolumn',
					text: 'Место оказания',
					cls: 'after-icons-column',
					tdCls: 'usluga-place',
					flex: 1,
					autoSizeColumn: true,
					minWidth: 190,
					//maxWidth: 350,
					locked: true,
					dataIndex: 'location',
					renderer: function (val, metadata, rec) {
						if (!rec.get('UslugaComplex_Name')) return '';
						if (!rec.get('MedService_id') && !rec.get('Resource_id')) {
							return '';
						}
						if(rec.get('EvnDirection_id') && rec.get('RecTo'))
							return rec.get('RecTo');
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

						return '<span style="white-space: nowrap; text-overflow: ellipsis" data-qtip="' + hint + '">' + text + '</span>';
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
							minWidth: 500,
							width: 500,
							cls: 'choose-bound-list-menu'
						},
						id: me.id + '_MedServiceEditor'
					}
				},
				/*{
					xtype: 'gridcolumn',
					text: '',
					//autoSizeColumn: true,
					hideHeaders: true,
					dataIndex: '',
					defaults: {
						resizable: false,
						width: 70,
						cls: 'with-right-border date-header',
						tdCls: 'with-right-border',
						bodyPadding: '6px 10px 7px 10px'
					},
					listeners: {
						render: function (view) {
							me.addColums(this);
						},
					},
					columns: []
				}*/
			],
			actions: {
				isCito: {
					getClass: 'getCitoClass',
					userCls: 'button-without-frame',
					getTip: 'getCitoTip',
					handler: 'addCitoInPrescr'
				},
				isDirection: {
					getClass: 'getDirectionClass',
					userCls: 'button-without-frame',
					getTip: 'getDirectionTip',
					handler: 'onDirectionClick'
				},
				isOtherMO: {
					getClass: 'getOtherMOClass',
					userCls: 'button-without-frame',
					getTip: 'getOtherMOTip',
					handler: 'onOtherMOClick'
				}
			},
			listeners: {
				render: function () {
					this.getEl().on('click', function (e, t) {
						me.addToQueue(e,t)
					}, null, {
						delegate: 'a.add-to-queue',
						stopPropagation: true,
						stopEvent: true
					});
					this.getEl().on('click', function (e, t) {
						me.cancelQueue(e,t);
					}, null, {
						delegate: 'a.cancel-changes',
						stopPropagation: true,
						stopEvent: true
					});
					this.getEl().on('click', function (e, t) {
						me.getTimetableNoLimit(e,t);
					}, null, {
						delegate: 'a.load-new-TTObjects',
						stopPropagation: true,
						stopEvent: true
					})
				},
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
						MedServiceEditor.getStore().proxy.extraParams.userLpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
						MedServiceEditor.getStore().proxy.extraParams.PrescriptionType_Code = cntr.getPrescriptionTypeCodeByObject(context.record.get('object'));
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
							me.updateRowTT(context.record);
						}
					}
				},
				/*resize: function(){
					//me.updateLayout();
				}*/
			},
			/*viewConfig : {
				forceFit: true,
				listeners : {
					refresh : function (dataview) {
						Ext6.each(dataview.panel.columns, function (column) {
							if (column.autoSizeColumn === true)
								column.autoSize();
						})
					}
				}
			},*/
			store: me.UslugaGridStore
		});
		me.lockedGrid = me.AutoDateTimeUslugaGrid.lockedGrid;
		me.normalGrid = me.AutoDateTimeUslugaGrid.normalGrid;

		me.addColums();
		me.normalGrid.updateLayout();

		me.lockedGrid.flex = 1;
		me.normalGrid.flex = 1;
		me.AutoDateTimeUslugaGrid.reconfigure();
		me.AutoDateTimeUslugaGrid.updateLayout();
		this.viewAutoDateSelectPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'center',
			layout: 'fit',
			flex: 1,
			dockedItems: [ /*me.HeaderToolbarPanel,*/ me.CompleteMsg],
			items: [me.AutoDateTimeUslugaGrid]
		});
		this.selectedCellsMenu = Ext6.create('Ext6.menu.Menu', {
			autoSize: true,
			resizable: false,
			minWidth: 16,
			maxWidth: 250,
			clickTTRec: null,
			StartDay: null,
			//targetTo: '',
			shadow: false,
			cls: 'selectedDateTime',
			style: {
				borderRadius: '2px;',
				border: '1px solid #C5C5C5;',
				boxShadow: '0 3px 6px rgba(0,0,0, .16) !important'
			},
			listeners: {
				beforeshow : function( sender, eOpts ){
					//debugger;
				}
			},
			defaults: {},
			items: [
				{
					text: 'Записать на предложенное время',
					name: 'recToDT',
					cls: 'selectedDateTime-menu',
					style:
						{
							'padding-left': '6px',
							'padding-right': '10px'
						},
					handler: function(e){
						if(me.selectedCellsMenu.clickTTRec && me.selectedCellsMenu.StartDay)
							me.doApply({
								rec: me.selectedCellsMenu.clickTTRec,
								StartDay: me.selectedCellsMenu.StartDay
							});
					}
				},
				{
					text: 'Отменить запись',
					name: 'cancelRec',
					cls: 'selectedDateTime-menu',
					style:
						{
							'padding-left': '6px',
							'padding-right': '10px'
						},
					handler: function(e){
						if(me.selectedCellsMenu.clickTTRec)
							me.cancelTTRecord(me.selectedCellsMenu.clickTTRec);
					}
				},
				{
					text: 'Открыть расписание на день',
					name: 'openTT',
					cls: 'selectedDateTime-menu',
					style:
						{
							'padding-left': '6px',
							'padding-right': '10px'
						},
					handler: function (e) {
						var params = {
							//showTarget: me,
							rec: me.selectedCellsMenu.clickTTRec,
							StartDay: me.selectedCellsMenu.StartDay,
							target: me,
							align: 'c-c?',

							//align: 'middle',
						};
						me.TitetableSelectionPanel.show(params);
					}
				}, '-',
				{
					text: 'Врач: "Примечание на день"',
					cls: 'selectedDateTime-description',
					disabled: true,
					padding: '6 10 6 6'
				}
			]
		});
		
		Ext6.apply(me, {
			items: [me.PersonInfoPanel, me.viewAutoDateSelectPanel],
			dockedItems: [me.FooterToolbarPanel]
		});

		this.callParent(arguments);
	}
});
