Ext6.define('common.Timetable.ScheduleViewWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swTimetableScheduleViewWindow',
	requires: [
		'common.Timetable.models.LpuStructureNode',
		'common.Timetable.models.Subject'
	],
	maximized: true,
	autoShow: false,
	cls: 'arm-window-new splitter-border timetable-window',
	title: 'Работа с расписанием',
	renderTo: main_center_panel.body.dom,
	constrain: true,
	header: false,
	
	loadSubjects: function(force, callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		var store = me.SubjectSelectorGrid.store;
		var queryField = me.leftPanelTopBar.down('[name=SubjectQueryField]');
		var structureSelection = me.LpuStructureTree.selection;
	
		var params = {};
		
		if (!Ext.isEmpty(queryField.getValue().trim())) {
			params.query = queryField.getValue().trim();
		}
		if (structureSelection) {
			if (structureSelection.get('Lpu_id')) {
				params.Lpu_id = structureSelection.get('Lpu_id');
			}
			if (structureSelection.get('LpuUnitType_id')) {
				params.LpuUnitType_id = structureSelection.get('LpuUnitType_id');
			}
			if (structureSelection.get('LpuUnit_id')) {
				params.LpuUnit_id = structureSelection.get('LpuUnit_id');
			}
			if (structureSelection.get('LpuSection_id')) {
				params.LpuSection_id = structureSelection.get('LpuSection_id');
			}
		}
		if (me.ARMType == 'polka' && me.userMedStaffFact && me.userMedStaffFact.MedStaffFact_id) {
			params.Lpu_id = me.userMedStaffFact.Lpu_id;
			params.MedStaffFact_id = me.userMedStaffFact.MedStaffFact_id;
		}
		if (!params.Lpu_id && !params.query) {
			store.removeAll();
			return;
		}
		if (!params.Lpu_id && params.query) {
			params.Lpu_id = getGlobalOptions().lpu_id;
		}

		if (force || !store.lastOptions || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			store.load({params: params, callback: callback});
		}
	},
	
	createScheduleAnnotationsListeners: function() {
		var me = this;
		var rows = [];
		
		me.SchedulePanel.el.query('td.descriptionRowTrue', false).forEach(function(cell) {
			var row = cell.up('tr');
			if (row && !rows.includes(row)) {
				rows.push(row);
			}
		});
		
		rows.forEach(function(row) {
			var cells = [].concat(
				row.query('td.range-beg', false), 
				row.query('td.range-end', false)
			).filter(function(cell) {
				return (
					cell.hasCls('range-beg') && cell.prev() ||
					cell.hasCls('range-end') && cell.next()
				);
			});
			
			var _cells = [];
			
			cells.forEach(function(cell) {
				_cells.push(cell);
				cell.begCell = row.down('td.range-beg[data-id="'+cell.dom.dataset.id+'"]');
				cell.endCell = row.down('td.range-end[data-id="'+cell.dom.dataset.id+'"]');
				if (cell.hasCls('range-beg')) {
					_cells.push(cell.prev());
					cell.prev().begCell = cell.begCell;
					cell.prev().endCell = cell.endCell;
					cell.prev().addCls('range-beg drag-handler-right');
				}
				if (cell.hasCls('range-end')) {
					_cells.push(cell.next());
					cell.next().begCell = cell.begCell;
					cell.next().endCell = cell.endCell;
					cell.next().addCls('range-end drag-handler-left');
				}
			});
			
			_cells.forEach(function(cell) {
				cell.pressed = false;
				cell.dragged = false;
				cell.text = cell.begCell.down('div.x6-grid-cell-inner').getHtml();
				
				cell.on('mousedown', function(e) {
					if (e.parentEvent.button == 0) {
						cell.pressed = true;
						
						if (cell.overResizer) {
							row.query('td', false).forEach(function(_cell) {
								_cell.setStyle('cursor', 'col-resize');
							});
						
							e.stopEvent();
						}
					}
				});
				cell.on('mouseup', function(e) {
					if (e.parentEvent.button == 0) {
						cell.pressed = false;
					}
					if (cell.dragged) {
						cell.dragged = false;
						
						var id = cell.begCell.dom.dataset.id;
						
						var begDate = Ext6.Date.parse(cell.begCell.dom.dataset.date, 'd.m.Y');
						var endDate = Ext6.Date.parse(cell.endCell.dom.dataset.date, 'd.m.Y');
						var overDate = Ext6.Date.parse(cell.overCell.dom.dataset.date, 'd.m.Y');
						var _begDate = begDate;
						var _endDate = endDate;
						
						if (cell.hasCls('range-beg')) {
							_begDate = overDate < endDate ? overDate : endDate;
						}
						if (cell.hasCls('range-end')) {
							_endDate = overDate > begDate ? overDate : begDate;
						}
						
						if (_begDate - begDate != 0 || _endDate - endDate != 0) {
							me.setAnnotationRange(id, Ext6.Date.format(_begDate, 'd.m.Y'), Ext6.Date.format(_endDate, 'd.m.Y'));
						}
					}
				});
				cell.on('mousemove', function(e) {
					var id = cell.begCell.dom.dataset.id;
					var rect = cell.dom.getBoundingClientRect();
					var diff = 1000;
					
					if (cell.hasCls('drag-handler-left')) {
						diff = e.clientX - rect.x;
					}
					if (cell.hasCls('drag-handler-right')) {
						diff = rect.x + rect.width - e.clientX;
					}
					
					if (diff <= 5) {
						cell.overResizer = true;
						cell.dragged = cell.pressed;
						cell.setStyle('cursor', 'col-resize');
					} else {
						cell.overResizer = false;
						cell.setStyle('cursor', null);
					}
					
					if (cell.dragged) {
						var overCell = Ext6.Element.fromPagePoint(e.clientX, e.clientY);
						if (overCell.up('td')) {
							overCell = overCell.up('td');
						}
						
						begDate = Ext6.Date.parse(cell.begCell.dom.dataset.date, 'd.m.Y');
						endDate = Ext6.Date.parse(cell.endCell.dom.dataset.date, 'd.m.Y');
						overDate = Ext6.Date.parse(overCell.dom.dataset.date, 'd.m.Y');
						
						if (cell.hasCls('range-beg')) row.query('td', false).forEach(function(_cell) {
							var _id = _cell.dom.dataset.id || id;
							var _date = Ext6.Date.parse(_cell.dom.dataset.date, 'd.m.Y');
							
							if (_date < endDate) {
								if (_date >= overDate) {
									_cell.addCls('descriptionRowTrue');
								} else {
									_cell.removeCls('descriptionRowTrue');
								}
							}
							if (overDate <= endDate) {
								if (_date - overDate == 0) {
									_cell.down('div.x6-grid-cell-inner').setHtml(cell.text);
								} else {
									_cell.down('div.x6-grid-cell-inner').setHtml(null);
								}
							}
						});
						if (cell.hasCls('range-end')) row.query('td', false).forEach(function(_cell) {
							var _id = _cell.dom.dataset.id;
							var _date = Ext6.Date.parse(_cell.dom.dataset.date, 'd.m.Y');
							
							if (_date > begDate) {
								if (_date <= overDate) {
									_cell.addCls('descriptionRowTrue');
								} else {
									_cell.removeCls('descriptionRowTrue');
								}
							}
						});
						
						cell.overCell = overCell;
						e.stopEvent();
					}
				});
			});
		});
	},
	
	loadSchedule: function() {
		var me = this;
		var params = {};
		var subjectSelection = me.SubjectSelectorGrid.getSelection().pop();
		
		params.MedStaffFact_id = subjectSelection.get('id');
		params.Date = Ext6.Date.format(me.Date, 'd.m.Y');
		
		me.mask('Загрузка...');
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=loadTimetableSchedule',
			params: params,
			success: function(response) {
				me.unmask();
				var responseObj = Ext6.decode(response.responseText);
				
				var annotations = responseObj.Annotations;
				var schedule = me.createSchedule(responseObj.Timetables, responseObj.Annotations);
				
				me.AnnotationGridPanel.store.loadData(annotations);
				me.SchedulePanel.store.loadData(schedule);
				
				me.createScheduleAnnotationsListeners();
			},
			failure: function(response) {
				me.unmask();
			}
		});
	},
	
	createSchedule: function(timetables, annotations) {
		var me = this;
		var i = 0;
		var daysCount = 14;
		var startDate = new Date(me.Date);
		var lastDate = timetables[0] ? timetables[0].date : null;
		var schedule = [];
		
		var annotationsOnTimetables = annotations.filter(function(annotation) {
			return annotation.begDate == annotation.endDate && annotation.begTime && annotation.endTime;
		});
		var annotationsOnDays = annotations.filter(function(annotation) {
			return !annotation.begTime || !annotation.endTime;
		});
		
		schedule.push({type: 'annotation'});
		for (var j = 0; j < daysCount; j++) {
			var date = new Date(startDate).addDays(j);
			var dateStr = Ext6.Date.format(date, 'd.m.Y');
			schedule[i][dateStr] = null;
		}
		
		annotationsOnDays.forEach(function(annotation) {
			annotation.range = [
				annotation.begDate + ' ' + annotation.begTime,
				annotation.endDate + ' ' + annotation.endTime
			].join(' - ');
			
			var begDate = Ext6.Date.parse(annotation.begDate, 'd.m.Y');
			var endDate = Ext6.Date.parse(annotation.endDate, 'd.m.Y');
			
			for (var date = begDate; date <= endDate; date.addDays(1)) {
				var dateStr = Ext6.Date.format(date, 'd.m.Y');
				if (!schedule[i]) {
					schedule.push({type: 'annotation'});
				}
				schedule[i][dateStr] = {...annotation, date: dateStr};
			}
			
			i++;
		});
		
		var timetableStartIdx = i = schedule.length;
		
		timetables.forEach(function(timetable) {
			var ttDT = Ext6.Date.parse(timetable.date + ' ' + timetable.time, 'd.m.Y H:i');
			
			timetable.annotations = annotationsOnTimetables.filter(function(annotation) {
				var begDT = Ext6.Date.parse(annotation.begDate + ' ' + annotation.begTime, 'd.m.Y H:i');
				var endDT = Ext6.Date.parse(annotation.endDate + ' ' + annotation.endTime, 'd.m.Y H:i');
				
				return begDT <= ttDT && endDT > ttDT;
			});
			
			if (lastDate != timetable.date) {
				i = timetableStartIdx;
			}
			if (!schedule[i]) {
				schedule.push({type: 'timetable'});
			}
			schedule[i][timetable.date] = timetable;
			lastDate = timetable.date;
			i++;
		});
		
		return schedule;
	},
	
	createScheduleColumns: function() {
		var me = this;
		var columns = [];
		var daysCount = 14;
		var startDate = new Date(me.Date);
		
		var weekDayNames = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
		
		for (var i = 0; i < daysCount; i++) {
			var date = new Date(startDate).addDays(i);
			var dateStr = Ext6.Date.format(date, 'd.m.Y');
			var weekDayNumber = date.getDay();
			var weekDayName = weekDayNames[weekDayNumber];
			var html = '';
			
			if (weekDayNumber.inlist([0,6])) {
				html = '<span style="color: #FF0000; opacity: 0.6; text-align: center;display: block; margin-top: 5px;">' + Ext6.Date.format(date, 'd.m') + '</span>' + '<span style="color: #FF0000; opacity: 0.6; text-align: center;display: block">' + weekDayName.toLowerCase() + '</span>';
			} else {
				html = '<span style="color: #333; opacity: 0.8; text-align: center;display: block; margin-top: 5px;">' + Ext6.Date.format(date, 'd.m') + '</span>' + '<span style="color: #333; opacity: 0.6; text-align: center;display: block">' + weekDayName.toLowerCase() + '</span>';
			}
			
			columns.push({
				text: html,
				width: 77,
				resizable: false,
				height: 40,
				tooltip: '',
				dataIndex: dateStr,
				renderer: function(value, meta, record) {
					meta.tdAttr += ' data-row="' + meta.rowIndex + '"';
					meta.tdAttr += ' data-column="' + meta.columnIndex + '"';
					
					if (record.get('type') == 'annotation') {
						var text = '';
						meta.tdCls += ' descriptionRow';
						meta.tdAttr += ' data-date="' + meta.column.dataIndex + '"';
						
						if (value && value.text) {
							meta.tdCls += ' descriptionRowTrue';
							meta.tdAttr += ' data-id="' + value.id + '"';
							meta.tdAttr += ' data-date="' + value.date + '"';
							
							if (value.date == value.begDate || meta.columnIndex == 1) {
								text = value.text;
								meta.tdCls += ' range-beg drag-handler-left';
							}
							if (value.date == value.endDate || meta.columnIndex == daysCount) {
								meta.tdCls += ' range-end drag-handler-right';
							}
						}
						
						return text;
					}
					if (record.get('type') == 'timetable') {
						if (!value) {
							meta.tdAttr += ' data-date="' + meta.column.dataIndex + '"';
							return '';
						}
						var status = value.isFree ? 'Свободно' : 'Занято';

						var annotations = value.annotations.map(function (annotation) {
							return `<div><p class='header_tooltip'>Текст:</p><p class='body_tooltip'>` + annotation.text + `</p></div>`;
						});

						meta.tdAttr += ` data-qtip="<div class='container_tooltip'>` +
							`<div><p class='header_tooltip'>Статус:</p><p class='body_tooltip'>` + status + `</p></div>` +
							`<div><p class='header_tooltip'>Тип:</p><p class='body_tooltip'>` + value.typeName + `</p></div>` +
							annotations.join('') +
							`</div>"`;

						meta.tdAttr += ` data-id="` + value.id + `"`;
						meta.tdAttr += ` data-date="` + value.date + `"`;

						var classes = [
							'cell-timetable',
							value.typeCls,
							value.isFree ? '' : 'full',
							value.annotations.length > 0 ? 'description' : ''
						];

						meta.tdCls = classes.join(' ');
						return '<div style="display: flex"><div class="full-cell-color" style="width: 8px; margin-right: 13px;"></div>' + value.time + '</div>';
					}
				}
			});
		}
		
		return columns;
	},
	
	openScheduleEditWindow: function() {
		var me = this;
		var subjectSelection = me.SubjectSelectorGrid.getSelection().pop();
		
		var formParams = {};
		var BegDate = null;
		var EndDate = null;
		
		formParams.MedStaffFact_id = subjectSelection.get('id');
		BegDate = Ext6.Date.format(me.Date, 'd.m.Y');
		EndDate = Ext6.Date.format(me.Date, 'd.m.Y');
		
		if (me.SchedulePanel.selectedDate) {
			BegDate = me.SchedulePanel.selectedDate;
			EndDate = me.SchedulePanel.selectedDate;
		}
		
		if (!formParams.MedStaffFact_id) {
			return;
		}
		
		if (BegDate == EndDate) {
			formParams.Range = BegDate;
		} else {
			formParams.Range = BegDate + ' - ' + EndDate;
		}
	
		getWnd('swTimetableScheduleEditWindow').show({
			formParams: formParams,
			callback: function() {
				me.loadSchedule();
			}
		});
	},
	
	deleteSchedule: function() {
		var me = this;
		var el = me.SchedulePanel.getEl();
		var cells = el.query('td.x6-grid-cell-selected.cell-timetable');
		var ids = [];
		
		cells.forEach(function(cell) {
			if (cell.dataset.id) {
				ids.push(cell.dataset.id);
			}
		});
		
		if (ids.length == 0) {
			return;
		}
		
		sw.swMessageBox.show({
			buttons: sw.swMessageBox.YESCANCEL,
			fn: function(buttonId){
				if (buttonId == 'yes') {
					me.mask('Удаление...');
		
					Ext6.Ajax.request({
						url: '/?c=Timetable6E&m=deleteTimetableSchedule',
						params: {
							ids: Ext6.encode(ids)
						},
						success: function(response) {
							me.unmask();
							me.loadSchedule();
						},
						failure: function(response) {
							me.unmask();
						}
					});
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Очистить выделенные бирки?'
		});
	},
	
	startCopySchedule: function() {
		var me = this;
		var selected = me.SchedulePanel.selModel.getSelected();
		var el = me.SchedulePanel.getEl();
		var cells = el.query('.x6-grid-cell-selected');
		var ids = [];
		
		if (cells.length == 0) {
			return;
		}
		
		var date1 = selected.startCell.column.dataIndex;
		var date2 = selected.endCell.column.dataIndex;
		
		var fromRange = date1 + ' - ' + date2;
		
		cells.forEach(function(cell) {
			if (cell.dataset.id) {
				ids.push(cell.dataset.id);
			}
		});
		
		me.ScheduleCopyData = {
			ids: Ext6.encode(ids),
			fromRange: fromRange,
			toRange: null
		};
		
		me.copyMsg = sw4.showInfoMsg({
			panel: me,
			type: 'info',
			text: 'Скопировано. Выберите место для вставки',
			hideDelay: null
		});
	},
	
	finishCopySchedule: function(toDate) {
		var me = this;
		
		if (!toDate || !me.ScheduleCopyData) {
			return;
		}
		
		if (me.copyMsg) {
			me.copyMsg.hide();
		}
		
		var params = Ext6.apply({}, me.ScheduleCopyData);
		
		var format = 'd.m.Y';
		var fromRange = params.fromRange.split(' - ');
		var diff = Ext6.Date.diff(Ext6.Date.parse(fromRange[0], format), Ext6.Date.parse(fromRange[1], format), Ext6.Date.DAY);
		
		var toBegDate = toDate;
		var toEndDate = Ext6.Date.format(Ext6.Date.add(Ext6.Date.parse(toDate, format), Ext6.Date.DAY, diff), format);
		
		params.toRange = toBegDate + ' - ' + toEndDate;
		
		me.ScheduleCopyData = null;
		
		me.copySchedule(params);
	},
	
	copySchedule: function(params) {
		var me = this;
		
		var msgBox = sw.swMessageBox.show({
			buttons: sw.swMessageBox.YESCANCEL,
			fn: function(buttonId){
				if (buttonId == 'yes') {
					me.mask('Копирование...');
					
					Ext6.Ajax.request({
						url: '/?c=Timetable6E&m=copyTimetableSchedule',
						params: params,
						success: function(response) {
							me.unmask();
							me.loadSchedule();
						},
						failure: function(response) {
							me.unmask();
						}
					});
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Скопировать выделенные бирки?'
		});
		
		Ext6.defer(function() {
			msgBox.toFront();
		}, 1);
	},
	
	openAnnotationEditWindow: function(action, from) {
		var me = this
		
		var addTimeDuration = function(time, duration) {
			if (!time) return null;
			var dt = Ext6.Date.parse(time, 'H:i').addMinutes(duration || 0);
			return Ext6.Date.format(dt, 'H:i');
		};
		
		var formParams = {};
		formParams.RangeType = 'day';
		
		if (from == 'scheduleContextMenu') {
			var scheduleSelection = me.SchedulePanel.selModel.getSelected();
			if (!scheduleSelection) return;
			
			var startData = scheduleSelection.startCell.record.get(scheduleSelection.startCell.column.dataIndex);
			var endData = scheduleSelection.endCell.record.get(scheduleSelection.endCell.column.dataIndex);
			
			formParams.RangeType = 'timetable';
			formParams.Annotation_begDate = startData.date || scheduleSelection.startCell.column.dataIndex;
			formParams.Annotation_begTime = startData.time || null;
			formParams.Annotation_endDate = endData.date || scheduleSelection.endCell.column.dataIndex;
			formParams.Annotation_endTime = addTimeDuration(endData.time, endData.duration) || null;
		}
		
		if (action == 'add') {
			var subjectSelection = me.SubjectSelectorGrid.getSelection().pop();
			
			if (!subjectSelection) {
				return;
			}
			
			formParams.MedStaffFact_id = subjectSelection.get('id');
		} else {
			var annotationSelection = me.AnnotationGridPanel.getSelection().pop();
			
			if (!annotationSelection) {
				return;
			}
			
			formParams.Annotation_id = annotationSelection.get('id');
		}
	
		getWnd('swTimetableAnnotationEditWindow').show({
			action: action,
			formParams: formParams,
			callback: function() {
				me.loadSchedule();
			}
		});
	},
	
	setAnnotationRange: function(id, begDate, endDate) {
		var me = this;
		
		if (!id || !begDate || !endDate) {
			return;
		}
		
		me.mask('Изменение диапазона примечания...')
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=setAnnotationRange',
			params: {
				Annotation_id: id,
				Annotation_begDate: begDate,
				Annotation_endDate: endDate
			},
			success: function(response) {
				me.unmask();
				me.loadSchedule();
			},
			failure: function(response) {
				me.unmask();
			}
		});
	},
	
	deleteAnnotation: function(from) {
		var me = this;
		var id = null;
		
		if (from == 'scheduleContextMenu') {
			var selection = me.SchedulePanel.selModel.getSelected();
			if (!selection) return;
			var data = selection.startCell.record.get(selection.startCell.column.dataIndex);
			id = data.annotations[0].id;
		} else {
			var record = me.AnnotationGridPanel.selection;
			if (!record) return;
			id = record.get('id');
		}
		
		sw.swMessageBox.show({
			buttons: sw.swMessageBox.YESCANCEL,
			fn: function(buttonId){
				if (buttonId == 'yes') {
					me.mask('Удаление...');
					
					Ext6.Ajax.request({
						url: '/?c=Timetable6E&m=deleteAnnotation',
						params: {
							Annotation_id: id
						},
						success: function(response) {
							me.unmask();
							me.loadSchedule();
						},
						failure: function(response) {
							me.unmask();
						}
					});
				}
			},
			icon: Ext6.MessageBox.QUESTION,
			msg: 'Удалить примечание?'
		});
	},
	
	setTimetableType: function(typeId) {
		var me = this;
		var el = me.SchedulePanel.getEl();
		var cells = el.query('.x6-grid-cell-selected');
		var ids = [];
		
		if (cells.length == 0) {
			return;
		}
		
		cells.forEach(function(cell) {
			if (cell.dataset.id) {
				ids.push(cell.dataset.id);
			}
		});
		
		me.mask('Изменение типа бирки...');
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=setTimetableType',
			params: {
				ids: Ext6.encode(ids),
				typeId: typeId
			},
			success: function(response) {
				me.unmask();
				me.loadSchedule();
			},
			failure: function(response) {
				me.unmask();
			}
		});
	},
	
	loadTypeList: function() {
		var me = this;
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=loadTimetableTypeList',
			success: function(response) {
				var typeList = Ext6.decode(response.responseText);
				
				Object.values(me.setTypeMenu).forEach(function(menu) {
					menu.removeAll();
					typeList.forEach(function(type) {
						menu.add({
							text: type.name,
							cls: type.cls,
							nick: type.nick,
							handler: function() {
								me.setTimetableType(type.id);
							}
						});
					});
				});
			},
			failure: function(response) {
				
			}
		});
	},
	
	show: function() {
		var me = this;
		me.ARMType = null;
		me.userMedStaffFact = null;
		me.Date = new Date();
		
		me.leftPanelTopBar.down('[name=SubjectQueryField]').setValue(null);
		me.LpuStructureTree.getRootNode().removeAll();
		me.SubjectSelectorGrid.store.removeAll();
		me.SchedulePanel.store.removeAll();
		me.SchedulePanel.reconfigure(me.createScheduleColumns());
		
		me.callParent(arguments);
		
		if (arguments[0] && arguments[0].ARMType) {
			me.ARMType = arguments[0].ARMType;
		}
		if (arguments[0] && arguments[0].userMedStaffFact) {
			me.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		
		me.loadTypeList();
		
		if (me.ARMType == 'polka') {
			me.leftPanel.hide();
			me.loadSubjects(true, function(records) {
				if (records.length > 0) {
					me.SubjectSelectorGrid.selModel.select(records[0]);
				}
			});
		} else {
			me.leftPanel.show();
			me.LpuStructureTree.store.load({
				callback: function() {
					me.LpuStructureTree.getRootNode().expand();
				}
			});
		}
	},
	
	initComponent: function() {
		var me = this;
		
		me.LpuStructureTree = Ext6.create('Ext6.tree.Panel', {
			rootVisible: false,
			border: false,
			header: false,
			columns: [{
				xtype: 'treecolumn',
				dataIndex: 'text',
				flex: 1,
				renderer: function (val, meta, rec) {
					if (rec.get('isLayover')) {
						meta.tdStyle = 'color: gray; font-style: italic;';
					}
					return val;
				}
			}],
			store: Ext6.create('Ext6.data.TreeStore', {
				autoLoad: false,
				parentIdProperty: 'parentId',
				model: 'common.Timetable.models.LpuStructureNode',
				root: {leaf: false, expanded: false},
				listeners: {
					beforeload: function(store, operation) {
						var node = operation.node;
						var params = operation.getParams();
						
						params.parentNodeType = node.get('nodeType');
						
						if (node.id == 'root') {
							params.Lpu_id = getGlobalOptions().lpu_id;
							params.parentNodeType = 'Lpu';
						}
						if (node.get('Lpu_id')) {
							params.Lpu_id = node.get('Lpu_id');
						}
						if (node.get('LpuUnitType_id')) {
							params.LpuUnitType_id = node.get('LpuUnitType_id');
						}
						if (node.get('LpuUnit_id')) {
							params.LpuUnit_id = node.get('LpuUnit_id');
						}
						if (node.get('LpuSection_id')) {
							params.LpuSection_id = node.get('LpuSection_id');
						}
					}
				}
			}),
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						me.LpuStructureTree.setSelection(record);
						me.loadSubjects(true);
					}
				}
			}
		});
		
		me.LpuStructurePanel = Ext6.create('Ext6.Panel', {
			region: 'west',
			flex: 1,
			collapseDirection: 'left',
			height: '100%',
			border: false,
			scrollable: true,
			titlePosition: 'left',
			cls: 'custom-scroll',
			title: {
				style: {'fontSize': '14px', 'fontWeight': '500'},
				rotation: 2,
				textAlign: 'right'
			},
			preventHeader: true,
			items: [
				me.LpuStructureTree
			]
		});
		
		me.SubjectSelectorGrid = Ext6.create('Ext6.grid.Panel', {
			width: 'auto',
			height: '100%',
			cls: 'testDoctor',
			viewConfig: {deferEmptyText: false},
			emptyText: '<div class="empty-grid-text"><p>Нет результатов. Проверьте правильность введенных данных, либо измените параметры поиска</p></div>',
			features: [{
				ftype: 'grouping',
				groupHeaderTpl: '<p style="color: #404040; font-weight: 700">{name}</p>',
				showSummaryRow: false,
				collapsible: false
			}],
			store: Ext6.create('Ext6.data.Store', {
				storeId: 'simpsonsStore',
				model: 'common.Timetable.models.Subject',
				groupField: 'name'
			}),
			columns: [{
				dataIndex: 'place',
				summaryType: 'count',
				flex: 1,
				renderer: function(value) {
					return '<p class="lpu-text"><span class="name-lpu" style="background-color: #fff;">' + value + '<i class="ottochie"></i><div class="gradient-ottoch"></div></span><!--<div class="gradient-ottoch"></div>--></p>'
				}
			}, {
				dataIndex: 'count',
				summaryType: 'average',
				width: 38,
				renderer: function(value) {
					var status = 1;
					var qtip = 'Расписание не заполнено';
					
					if (value > 0 && value < 14) {
						status = 2;
						qtip = 'Расписание заполнено на ' + value + ' дней';
					} else if (value >= 14) {
						status = 3;
						qtip = 'Расписание заполнено на две или более недели';
					}
					
					return '<span data-qtip="' + qtip + '" style="height: 17px; width: 16px; display: block;" class="rasp-icon-' + status + '"></span>'
				}
			}],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						me.loadSchedule();
					}
				}
			}
		});
		
		me.SubjectSelectorPanel = Ext6.create('Ext6.Panel', {
			flex: 1,
			layout: 'card',
			id: 'DoctorAndServices',
			height: '100%',
			cls: 'custom-scroll DoctorAndServices',
			defaults: {
				border: false
			},
			scrollable: true,
			defaultListenerScope: true,
			activeItem: 0,
			items: [
				me.SubjectSelectorGrid
			],
			doCardNavigation: function (incr) {
				var me = this;
				var l = me.getLayout();
				l.setActiveItem(incr);
			}
		});
		
		me.leftPanelTopBar = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'top',
			height: 40,
			style: {
				background: '#ededed',
				padding: '7px 4px 7px 11px'
			},
			items: [{
				xtype: 'segmentedbutton',
				cls: 'segmentedButtonGroup segmentedButtonGroupMini',
				margin: '0 5 0 0',
				disabled: true,
				items: [{
					text: 'Специалисты',
					pressed: true,
					itemId: 'card-prev',
					handler: function () {
						
					},
				}, {
					text: 'Службы и услуги',
					itemId: 'card-next',
					handler: function () {
						
					}
				}],
			}, {
				xtype: 'swqueryfield',
				width: 'calc(100% - 242px)',
				style: {
					marginLeft: '5px',
					background: '#fff'
				},
				emptyText: '',
				name: 'SubjectQueryField',
				query: me.loadSubjects.bind(me)
			}]
		});
		
		me.leftPanelSplitter = Ext6.create('Ext6.resizer.Splitter', {
			collapseTarget: 'prev',
			collapsible: true,
			width: 11,
			style: {
				borderLeft: '1px solid #ccc',
				borderRight: '1px solid #ccc',
				borderTop: '0px !important'
			}
		});
		
		me.leftPanel = Ext6.create('Ext6.panel.Panel', {
			collapsible: true,
			border: false,
			split: true,
			header: false,
			layout: 'hbox',
			cls: 'custom-struct-mo',
			region: 'west',
			title: {
				text: 'СТРУКТУРА МО',
				style: {'fontSize': '14px', 'fontWeight': '500'},
				rotation: 2,
				textAlign: 'right'
			},
			minWidth: 540,
			maxWidth: 635,
			flex: 1,
			bodyStyle: {
				background: '#EDEDED'
			},
			dockedItems: [
				me.leftPanelTopBar
			],
			items: [
				me.LpuStructurePanel,
				me.leftPanelSplitter,
				me.SubjectSelectorPanel
			]
		});
		
		me.AnnotationGridPanel = Ext6.create('Ext6.grid.Panel', {
			dock: 'bottom',
			id: 'descriptionGrid',
			collapsible: true,
			collapsed: true,
			height: 250,
			cls: 'grid-common descriptionBottomGridPanel custom-scroll',
			store: Ext6.create('Ext6.data.Store', {
				autoLoad: false,
				fields: [
					{name: 'id', type: 'int'},
					{name: 'text', type: 'string'},
					{name: 'insDate', type: 'string'},
					{name: 'begDate', type: 'string'},
					{name: 'begTime', type: 'string'},
					{name: 'endDate', type: 'string'},
					{name: 'endTime', type: 'string'},
					{name: 'visionName', type: 'string'}
				],
				listeners: {
					datachanged: function(store){
						var title = '<p style="font-size: 17px; display: inline-block">ПРИМЕЧАНИЯ</p><p style="display: inline"><span id="length_description" class="length_description">' + store.count() + '</span></p>';
						me.AnnotationGridPanel.setTitle(title);
					}
				}
			}),
			header: {
				titlePosition: 1,
				height: 42,
				padding: '8px 15px'
			},
			title: {
				text: '<p style="font-size: 17px; display: inline-block">ПРИМЕЧАНИЯ</p><p style="display: inline"><span id="length_description" class="length_description">' + '0' + '</span></p>'
			},
			tools: [{
				xtype: 'button',
				text: 'Добавить',
				name: 'add',
				cls: 'description-header-button',
				iconCls: 'add-description',
				handler() {
					me.openAnnotationEditWindow('add');
				}
			}, {
				xtype: 'button',
				text: 'Редактировать',
				name: 'edit',
				cls: 'description-header-button',
				id: 'description_edit',
				iconCls: 'edit-description',
				disabled: true,
				handler() {
					me.openAnnotationEditWindow('edit');
				}
			}, {
				xtype: 'button',
				text: 'Удалить',
				name: 'delete',
				cls: 'description-header-button',
				id: 'description_delete',
				iconCls: 'delete-description',
				disabled: true,
				handler() {
					me.deleteAnnotation();
				}
			}],
			columns: [{
				text: 'Создан',
				width: 81,
				style: {
					'padding-left': '8px'
				},
				dataIndex: 'insDate'
			}, {
				text: 'Содержание',
				minWidth: 156,
				flex: 1,
				style: {
					'padding-left': '8px'
				},
				dataIndex: 'text'
			}, {
				text: 'Начало',
				width: 126,
				style: {
					'padding-left': '8px'
				},
				dataIndex: 'begDT',
				renderer: function(value, meta, record) {
					return record.get('begDate') + ' ' + record.get('begTime');
				}
			}, {
				text: 'Окончание',
				width: 121,
				style: {
					'padding-left': '8px'
				},
				dataIndex: 'endDT',
				renderer: function(value, meta, record) {
					return record.get('endDate') + ' ' + record.get('endTime');
				}
			}, {
				text: 'Видимость',
				width: 111,
				style: {
					'padding-left': '8px'
				},
				dataIndex: 'visionName'
			}],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					selectionchange: function(model, records) {
						var panel = me.AnnotationGridPanel;
						var addButton = panel.down('button[name=add]');
						var editButton = panel.down('button[name=edit]');
						var deleteButton = panel.down('button[name=delete]');
						
						editButton.setDisabled(records.length == 0);
						deleteButton.setDisabled(records.length == 0);
					}
				}
			}
		});
		
		me.setTypeMenu = {};
		me.createSetTypeMenu = function(key) {
			me.setTypeMenu[key] = Ext6.create('Ext6.menu.Menu', {
				cls: 'edit-rec-menu',
				width: 243
			});
			return me.setTypeMenu[key]
		};
		
		me.ScheduleToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'top',
			height: 40,
			padding: '5 15 6 8',
			cls: 'tool_bar_wra',
			style: {
				background: '#ededed'
			},
			items: [
				'->',
				{
					xtype: 'button',
					cls: 'sw-tool',
					userCls: 'add_rasp',
					margin: '0 0 0 24',
					iconCls: 'add-rec',
					tooltip: 'Добавить расписание',
					text: 'Создать расписание',
					listeners: {
						click: function() {
							me.openScheduleEditWindow();
						},
						render: function() {
							var button = this;
							if (document.body.clientWidth > 1650) {
								var el = document.querySelector('.' + button.userCls);
								el.dataset.qtip = '';
							}
						}
					}
				}, {
					xtype: 'button',
					cls: 'sw-tool',
					iconCls: 'edit-rec icon_menu_cust',
					margin: '0 0 0 24',
					text: 'Тип бирки',
					tooltup: 'Изменить тип бирки',
					menu: me.createSetTypeMenu('topBar')
				}, {
					xtype: 'button',
					cls: 'sw-tool',
					userCls: 'delete_desp',
					margin: '0 0 0 24',
					iconCls: 'delete-rec',
					tooltip: 'Очистить бирки',
					text: 'Очистить бирки',
					listeners: {
						click: function() {
							me.deleteSchedule();
						},
						render: function() {
							var button = this;
							if (document.body.clientWidth > 1650) {
								var el = document.querySelector('.' + button.userCls);
								el.dataset.qtip = '';
							}
						}
					}
				}, {
					xtype: 'button',
					cls: 'sw-tool',
					userCls: 'copy_desp',
					margin: '0 0 0 24',
					iconCls: 'copy-rec',
					tooltip: 'Скопировать расписание',
					text: 'Скопировать',
					menu: [],
					listeners: {
						click: function() {
							me.startCopySchedule();
						},
						render() {
							if (document.body.clientWidth > 1650) {
								var el = document.querySelector('.' + this.userCls);
								el.dataset.qtip = '';
							}
						}
					}
				}
			]
		});
		
		me.ScheduleContextMenu = Ext6.create('Ext6.menu.Menu', {
			items: [{
				name: 'copyTimetable',
				text: 'Копировать',
				handler: function() {
					me.startCopySchedule();
				}
			}, {
				name: 'setTimetableType',
				text: 'Тип бирки',
				menu: me.createSetTypeMenu('contextMenu')
			}, {
				name: 'addAnnotation',
				text: 'Добавить примечание',
				handler: function() {
					me.openAnnotationEditWindow('add', 'scheduleContextMenu');
				}
			}, {
				name: 'deleteAnnotation',
				text: 'Удалить примечание',
				handler: function() {
					me.deleteAnnotation('scheduleContextMenu');
				}
			}]
		});
		
		me.SchedulePanel = Ext6.create('Ext6.grid.Panel', {
			region: 'center',
			flex: 1,
			resizable: false,
			margin: '0 0 0 1',
			cls: 'schedule-table custom-scroll',
			emptyText: '<div class="empty-sheets-text"><p>Что бы добавить расписание, выберите нужную колонку и нажмите на кнопку</p><i class="add-button"></i></div>',
			selectedDate: null,
			selModel: {
				type: 'spreadsheet',
				rowNumbererHeaderWidth: 0,
				extensible: 'xy',
				multiSelect: true
			},
			plugins: {
				ptype: 'cellediting',
				clicksToEdit: 1,
				clipboard: true,
				selectionreplicator: true
			},
			store: Ext6.create('Ext6.data.Store', {
				autoLoad: false,
				proxy: {
					type: 'ajax',
					url: '/?c=Timetable6E&m=loadTimetableSchedule',
					reader: {type: 'json'}
				}
			}),
			columns: [],
			dockedItems: [
				me.ScheduleToolbar,
				me.AnnotationGridPanel
			],
			listeners: {
				headerclick(comp, column) {
					if (!column) return;
					
					me.SchedulePanel.selectedDate = column.dataIndex;
					
					if (me.ScheduleCopyData) {
						me.finishCopySchedule(column.dataIndex);
					}
				},
				selectionchange: function(comp, selection) {
					if (selection && selection.startCell) {
						me.SchedulePanel.selectedDate = selection.startCell.column.dataIndex;
						
						if (me.ScheduleCopyData) {
							me.finishCopySchedule(selection.startCell.column.dataIndex);
						}
					}
				},
				beforeselectionextend: function(comp, selection, extension, eOpts) {
					if (extension.type == 'columns' && extension.columns > 0) {
						var columns = me.SchedulePanel.columnManager.columns;
						var selectedCells = comp.el.query('td.x6-grid-cell-selected.cell-timetable', false);
						
						var copyFromCells = selectedCells.filter(function(cell) {
							return cell.dom.dataset.column < extension.start.colIdx;
						});
						
						var ids = copyFromCells.map(function(cell) {
							return Number(cell.dom.dataset.id);
						});
						
						if (ids.length == 0) {
							return;
						}
						
						var fromBegDate = selection.startCell.column.dataIndex;
						var fromEndDate = columns[extension.start.colIdx - 1].dataIndex;
						
						var toBegDate = extension.start.column.dataIndex;
						var toEndDate = extension.end.column.dataIndex;
						
						var params = {
							ids: Ext6.encode(ids),
							fromRange: fromBegDate + ' - ' + fromEndDate,
							toRange: toBegDate + ' - ' + toEndDate,
							repeatable: true
						};
						
						me.copySchedule(params);
					}
				},
				itemcontextmenu: function(comp, record, rowDom, index, e) {
					var selection = me.SchedulePanel.selModel.getSelected();
					var cell = Ext6.Element.fromPagePoint(e.clientX, e.clientY);
					if (cell.up('td')) cell = cell.up('td');
					
					var data = record.get(cell.dom.dataset.date);
					
					if (cell.hasCls('cell-timetable') && data) {
						var coord = [
							Number(cell.dom.dataset.column),
							Number(cell.dom.dataset.row)
						];
						me.SchedulePanel.selModel.selectCells(coord, coord);
						
						me.ScheduleContextMenu.down('[name=deleteAnnotation]').setVisible(data.annotations.length > 0);
						
						me.ScheduleContextMenu.showAt(e.clientX, e.clientY);
					}
					
					e.stopEvent();
				}
			}
		});
		
		Ext6.apply(me, {
			layout: 'border',
			border: false,
			style: 'padding: 0 !important;',
			defaults: {
				bodyStyle: {
					borderLeft: 0,
					borderRight: 0
				}
			},
			items: [
				me.leftPanel,
				me.SchedulePanel
			]
		});
		
		me.callParent(arguments);
	}
});