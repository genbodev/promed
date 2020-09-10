/*Расширенный календарь
 
Oбязательные входящие параметры: 
	data: данные с интервалами
	startDate: поле в данных, говорящих, где начало интервала
	endDate: поле в данных, говорящих, где конец интервала
	subject_id: самый важный объект, его уникальный id
	addedRec_id: указатель на id записанного элемента в базе(при его отсутствии изменения интервалов работать не будут)

Необязательные:
	dragViewFields: поля для отображения в окне при драге
	linkGrid: грид для связи	
	linkGridLengthIntervalField: поле указывающее интервал (часы)
	linkGrigMultipleDrag: мультидраг

Свойства:
	countUniqueSubj - кол-во объектов в календаре

События:
	intervalClick
	intervalDelete
	intervalAdd
	intervalChange
	refreshData
	intervalAddError
	multiTaskAddError
 **/



Ext.define('sw.lib.CalendarExt', {
	alias: 'widget.CalendarExt',
	extend: 'Ext.grid.Panel',
//	requires:['Ext.ux.CellDragDrop'],
	layout: {
		type: 'border'
	},
	cls: 'calendarExt',
	sortableColumns: false,
	startDate: null,
	endDate: null,
	subject_id: null,
	dragViewFields: {},
	linkGrid: null,
	linkGridLengthIntervalField: null,
	addedRec_id: null,
	linkGrigMultipleDrag: false,
	selModel: Ext.create('Ext.selection.CellModel', {}),
	columnLines: true,
	countUniqueSubj: 0,
	gridData: [],
	datesInterval: {},
//	viewConfig: {},
	colorArray: {},
	
	initComponent: function() {
		Ext.suspendLayouts();
		
		var me = this,
			config = me.initialConfig;
			
		if (config.linkGrid){
			config.linkGrid.addCls('linked-to-calendar-grid');
			me.reconfigLinkedGrid(config.linkGrid)			
		}
		
		me.datesInterval = me.getCurrentWeek();
		
		me.gridData = config.data;
		me.loadData(me.gridData, me.datesInterval);
		
		me.startDate = config.startDate;
		me.endDate = config.endDate;
		me.subject_id = config.subject_id;
		
		me.store = Ext.data.StoreManager.lookup('simpsonsStore');
		me.columns = me.createColumns(me.datesInterval);
		
		me.addEvents({
			intervalClick: true,
			intervalDelete: true,
			intervalAdd: true,
			intervalChange: true,
			refreshData: true,
			intervalAddError: true,
			multiTaskAddError: true
		});
		
		me.on('cellclick', function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
			var subjid = e.getTarget().getAttribute('subjid');
			if (subjid){				
				me.fireEvent('intervalClick', subjid);
			}
		});
		
		me.on('cellContextMenu', function(cmp, td, cellIndex, record, tr, rowIndex, evt, eOpts){
			me.showContextMenu(cmp, td, cellIndex, record, tr, rowIndex, evt, eOpts);
		});
			
		me.on('afterrender', function(cmp){
			me.initDragAndDrop(cmp);
		});
		
		me.on('itemmouseenter', function(cmp, record, item, index, e, eOpts ){
			var subjid = e.getTarget().getAttribute('subjid');
			if(subjid){
				cmp.getEl().addCls('time-interval-hover');
			}
			else{
				cmp.getEl().removeCls('time-interval-hover');
			}
		})
		
		//тулбар с кнопками
		me.tbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			items: [
				Ext.create('sw.datePrevDay', {width: 100, text: 'Пред. неделя'}),
				Ext.create('sw.datePickerRange', 
					{
						maxValue: 'unlimited',
						dateFrom: me.datesInterval.start,
						dateTo: me.datesInterval.end,
						intervalMode: 'week',
						reloadParentGrid: function(){
							var interval = {};
							interval.start = this.dateFrom;
							interval.end = this.dateTo;
							me.datesInterval = interval;
							me.reloadData();							
						}
					}
				),
				Ext.create('sw.dateNextDay', {width: 100, text: 'След. неделя'}),
				Ext.create('sw.dateCurrentDay', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentWeek', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentMonth', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				})
			]
		})
		//конец тулбара
		
		Ext.apply(me);
		me.callParent();
		Ext.resumeLayouts(true);
	},
	
	//D&D 
	initDragAndDrop: function(cmp){	
		
		//календарсикй драг
		var me = this,
			view = cmp.getView().normalView;

			//определяем зону драга
			me.dragZone = new Ext.view.DragZone({
			view: view,
			primaryButtonOnly: true,
			ddGroup: 'ddExtCalendar',
			dragText: '{0} selected row{1}',
			//этап 1
			//формирование данных драг элемента

			getDragData: function (e) {
				var view = this.view,
					item = e.getTarget(view.getItemSelector()),
					record = view.getRecord(item),
					clickedEl = e.getTarget(view.getCellSelector()),					
					columnName = view.getGridColumns()[clickedEl.cellIndex].dataIndex,
					subjid = e.getTarget().getAttribute('subjid'),
					currentCellData,
					linkedRec,
					dragEl;
				
				//текущий элемент
				if (item && subjid) {
					dragEl = document.createElement('div');
					if (cmp.linkGrid) {linkedRec = cmp.linkGrid.store.getAt(cmp.linkGrid.store.find(cmp.subject_id, subjid));}
					
					var recs = record.get(columnName),
						uni = Number(e.getTarget().getAttribute('class').match(/\d+/));
					for (var w in recs){
						if(recs[w].uniq == uni)
						{
							me.currentCellData = recs[w];
						}
					}
					

					//отображение
					//если нет настроек на отображение дд эл-та, то по умолчанию
					if( Ext.Object.getSize(cmp.dragViewFields) == 0 || typeof linkedRec == 'undefined')
						{dragEl.appendChild(document.createTextNode(clickedEl.textContent || clickedEl.innerText));}
					else
					{
						var span = document.createElement('table')
						Ext.Object.each(cmp.dragViewFields, function(key, value, myself) {								
							span.innerHTML += '<tr><td>'+key + "<td><td>" + linkedRec.get(value)+'</td></tr>';
							dragEl.appendChild(span);
						})
					}

					return {
						event: new Ext.EventObjectImpl(e),
						ddel: dragEl,
						item: e.target,
						subjid: subjid,
						columnName: columnName,
						record: record,
						currentCellData: me.currentCellData
					};
				}
			},
			//этап 2
			//обновление данных драг элемента
			onInitDrag: function (x, y) {
				var self = this,
					data = self.dragData,
					el = data.ddel;

				self.ddel.update(el.innerHTML);
				self.proxy.update(self.ddel.dom);
				self.onStartDrag(x, y);

				return true;
			},
			onStartDrag: function(e){
				var els = Ext.fly(view.getId()).select('td div.interval-'+me.currentCellData.uniq);					
				els.toggleCls('draggedfromzone');
			},
			beforeInvalidDrop: function( target, e, id ){
				var els = Ext.fly(view.getId()).select('td div.interval-'+me.currentCellData.uniq);					
				els.toggleCls('draggedfromzone');
			}
		});
		
		//!драг прилинкованого грида
		if (cmp.linkGrid){
			
			var linkGrid = cmp.linkGrid,
				linkView = cmp.linkGrid.getView(),
				linkLengthField = cmp.linkGridLengthIntervalField,
				multipleDrag = cmp.linkGrigMultipleDrag;		
				
			//определяем зону драга
			linkGrid.dragZone = new Ext.view.DragZone({
				view: linkView,
				ddGroup: 'ddExtCalendar',
				dragText: '{0} selected row{1}',
				
				getDragData: function (e) {		
					var view = this.view,
						item = e.getTarget(view.getItemSelector()),
						record = view.getRecord(item),
						multiRecords = new Ext.util.MixedCollection(),
						clickedEl = e.getTarget(view.getCellSelector()),
						countRecs = 0,
						dragEl;

					//если есть возможность мультидрага, проверяем выделенные записи
					if (multipleDrag)
						{							
							var records = linkGrid.getStore().queryBy(function(r) {
								return r.get('dragGroup') == true
							});
							countRecs = records.getCount();
						}

					//отображение перетаскиваемого элемента с прилинкованного грида
					//если нет настроек на отображение дд эл-та, то по умолчанию
					if ( item ){
						dragEl = document.createElement('div');
						if( Ext.Object.getSize(cmp.dragViewFields) == 0)
						{
							dragEl.appendChild(document.createTextNode(clickedEl.textContent || clickedEl.innerText));
						}
						else{
							//если перетаскиваемый элемент один
							if(countRecs<2)
							{
								var span = document.createElement('table');
								Ext.Object.each(cmp.dragViewFields, function(key, value, myself) {						
									span.innerHTML += '<tr><td>'+key + "<td><td>" + record.get(value)+'</td></tr>';
									dragEl.appendChild(span);
								});
								multiRecords.add(record);
							}
							//если их много
							if(countRecs>1)
							{
								dragEl.appendChild(document.createTextNode('записей: ' + countRecs));
								multiRecords = records;
							}
						}

					return {
						event: new Ext.EventObjectImpl(e),
						ddel: dragEl,
						item: e.target,
						columnName: view.getGridColumns()[clickedEl.cellIndex].dataIndex,
						record: multiRecords
						//countRecs: countRecs
						};
					}
				},
				
				onInitDrag: function (x, y) {
					var self = this,
						data = self.dragData,
						el = data.ddel;

					self.ddel.update(el.innerHTML);
					self.proxy.update(self.ddel.dom);
					self.onStartDrag(x, y);

					return true;
				}
			})
		}
		//! конец драга прилинкованого грида

		//определяем зону дропа			
		me.dropZone = new Ext.dd.DropZone(view.el, {
			view: view,
			ddGroup: 'ddExtCalendar',

			//этап 3 определяем место падения, собираем данные падения
			getTargetFromEvent: function (e) {					
				var self = this,
					v = self.view,
					cell = e.getTarget(v.cellSelector),
					row, columnIndex;



				if (cell) {
					row = v.findItemByChild(cell);
					columnIndex = cell.cellIndex;

					if (row && Ext.isDefined(columnIndex)) {
						return {
							node: cell,
							record: v.getRecord(row),
							columnName: self.view.up('grid').columns[columnIndex].dataIndex,
							columnDate: self.view.up('grid').columns[columnIndex].date
						};
					}
				}
			},

			//этап 4 определяем место падения
			onNodeEnter: function (target, dd, e, dragData) {				
				var self = this;

				delete self.dropOK;

				if (!target || target.node === dragData.item.parentNode) {
					return;
				}

				self.dropOK = true;



				if (me.dropCls) {
					Ext.fly(target.node).addCls(me.dropCls);
				} else {
					var c;
					if (dragData.currentCellData){c = dragData.currentCellData.color}
					else {c = '#cdeb8e'}
					Ext.fly(target.node).applyStyles({			
						backgroundColor: c
					});
				}
			},

			//можно кинуть или нет, 
			onNodeOver: function (target, dd, e, dragData) {
				return this.dropOK ? this.dropAllowed : this.dropNotAllowed;
			},

			//убираем подсветку ячейки
			onNodeOut: function (target, dd, e, dragData) {
				
				var cls = this.dropOK ? me.dropCls : me.noDropCls;
				//Ext.defer(function() {	
				if (cls) {
					Ext.fly(target.node).removeCls(cls);
				} else {
					Ext.fly(target.node).applyStyles({
						backgroundColor: ''
					});
				}
				//}, 100);
			},

			// последний этап - кидание
			onNodeDrop: function (target, dd, e, dragData) {
				
				if (this.dropOK) {
					
					//move					
					if (dragData.subjid)					
					{
						var targetId = dragData.subjid,
							targetDate = target.columnDate,
							targetHour = target.record.get('hour'),
							//fromDateInterval = dragData.record.get(dragData.columnName)[0],
							fromStartDate = Ext.Date.parse(dragData.currentCellData.startDate, "Y-m-d H:i:s"),
							fromEndDate = Ext.Date.parse(dragData.currentCellData.endDate, "Y-m-d H:i:s"),
							toStartDate = Ext.Date.parse( (Ext.Date.format(targetDate, 'Y-m-d')+' '+targetHour), "Y-m-d G:i"),
							toEndDate = Ext.Date.add(toStartDate, Ext.Date.HOUR, (Math.round((fromEndDate - fromStartDate)/3600000)));
							
							if(Ext.Date.format(toStartDate, 'H') == '00' ){
								toStartDate = Ext.Date.add(toStartDate, Ext.Date.MINUTE, 0)
							}

							if(Ext.Date.format(toEndDate, 'H') == '00' ){
								toEndDate = Ext.Date.add(toEndDate, Ext.Date.MINUTE, -1)
							}
							
						cmp.moveInterval(targetId, dragData.currentCellData.uniq, fromStartDate, fromEndDate, toStartDate, toEndDate);
					}
					
					//add				
					else
					{
						var count = dragData.record.getCount(),
							multiRecs = dragData.record,
							recordsToAdd = new Ext.util.MixedCollection(),
							targetDate = target.columnDate,
							targetHour = target.record.get('hour'),
							toStartDate = Ext.Date.parse( (Ext.Date.format(targetDate, 'Y-m-d')+' '+targetHour), "Y-m-d G:i"),
							alertMsg='';
							
							multiRecs.each(function(item, index, count){
								console.log('multiRecs', multiRecs, linkLengthField, item.get(linkLengthField));
								//если одна запись, то проверяем на ошибки, и если надо ругаемся
								if ( (!item.get(linkLengthField)) && (count==1) ){
									cmp.fireEvent('intervalAddError', dragData.record.getAt(0), toStartDate, 'Не указана продолжительность интервала');
								}
								//если потоковое добавление записей, то не проверяем на ошибки, просто пропускаем
								if (item.get(linkLengthField))
								{
									recordsToAdd.add(index, {
										record:item, hours:item.get(linkLengthField), startDate:toStartDate}
									);
								}
								else
								//формируем текст ошибки - в него данные о пропущенных записях 
								{
									if (cmp.dragViewFields)
									{
										alertMsg +='</br>';
										Ext.Object.each(cmp.dragViewFields, function(key, value, myself) {
											alertMsg += key + ' ' + item.get(value)+' / ' ;	
										})
									}		
								}
							})
							
						if (alertMsg && (count>1))
						{
							cmp.fireEvent('multiTaskAddError', alertMsg, 'Не указаны продолжительности интервалов');
						}					
						
						cmp.addInterval(recordsToAdd);
					}
					
					return true;				   
				}

			},

			onCellDrop: Ext.emptyFn
		});			
	},
	
	showContextMenu: function( cmp, td, cellIndex, record, tr, rowIndex, evt, eOpts) {
		if ( (cmp==this.getView().normalView) && (evt.getTarget().getAttribute('subjid')) )
		{
			var els = Ext.fly(cmp.getId()).select('td div.interval-'+this.currentCellData.uniq);					
				els.toggleCls('draggedfromzone');
				
			evt.stopEvent();	
			Ext.create('Ext.menu.Menu', {
				items: [
					{itemId: 'deleteInterval', text: 'Удалить интервал'}
				],
				listeners: {
					click: function( menu, item, e, eOpts)
					{
						var subjid = evt.getTarget().getAttribute('subjid');
						if (subjid){
							switch(item.itemId){
								case 'deleteInterval': {this.deleteInterval(record, rowIndex, cellIndex, subjid); break;}
						}
						}
					}.bind(this),
					
					hide: function(cmp){
						els.toggleCls('draggedfromzone');
					}
				}
			}).showAt(evt.getXY());
		}
	},
	
	getCurrentWeek: function(){
		var today = new Date();
		var currWeek = {};
		currWeek.start = Ext.Date.add(today, Ext.Date.DAY, 1-today.getDay());
		currWeek.end = Ext.Date.add(today, Ext.Date.DAY, 7-today.getDay());
		return (currWeek)
	},
	
	createColumns: function(interval){
		var daysHeaders = [],
			startDay = interval.start,
			endDay = interval.end;

		daysHeaders.push({
				text: '',
				locked   : true,
				dataIndex: 'hour',
				width: 50, 
				hideable: false, 
				align: 'center',
				cls: 'datesHours',
				sortable: false,
				renderer: function(v) {
					return '<div style="height: 13px; margin:4px">'+v+'</div>'
				}
			})
			
		var day = Ext.Date.clearTime(startDay, true),
			nextMonday = Ext.Date.add(Ext.Date.clearTime(endDay, true), Ext.Date.DAY, 1);
			
		//собираем дни в интервале
		for (var day, i = 0; day < nextMonday; day = Ext.Date.add(Ext.Date.clearTime(day, true), Ext.Date.DAY, 1), i++) {
			var shortDayName = '';
			switch(day.getDay())
			{
				case 0: {shortDayName = 'Вс'; break;}
				case 1: {shortDayName = 'Пн'; break;}
				case 2: {shortDayName = 'Вт'; break;}
				case 3: {shortDayName = 'Ср'; break;}
				case 4: {shortDayName = 'Чт'; break;}
				case 5: {shortDayName = 'Пт'; break;}
				case 6: {shortDayName = 'Сб'; break;}
			}
			//собираем заголовки Columns
			var dayHeaderName = shortDayName + ' ' + (Ext.Date.format(day, 'j/n'));
			daysHeaders.push({
				text: dayHeaderName,
				date: day,
				dataIndex: 'dataCells' +i,
				flex: 1, 
				hideable: false, 
				align: 'center',
				sortable: false,
				renderer: function(v, meta, rec, rowIndex, colIndex) {
					if (v.length>0)
					return this.renderCells(v, meta, rec, rowIndex, colIndex)
				}.bind(this)
			})
		}
		return daysHeaders
	},
	
	loadData: function(data, datesInterval){
		if (!data){
			return false
		}
		//здесь формирование стора
		var gridData = [],
			dataCells = [],
			subject_id = this.subject_id,
			startSubject = this.startDate,
			endSubject = this.endDate,
			day = datesInterval.start,
			nmond = Ext.Date.add(datesInterval.end, Ext.Date.DAY, 1),
			colorArray = this.colorArray,
			idInBase = this.addedRec_id;

		//формирование уникальных ids для каждого интервала
		//по умолчанию
		if(!idInBase){
			for (var n in data){data[n].id = n}
		}
		
		//перебираем дни в указанном интервале
		for (var day, i = 0; day < nmond; day = Ext.Date.add(day, Ext.Date.DAY, 1), i++) {
			
			var m = data.filter(function(element, index, array) {				
				var startDay = Ext.Date.parse(element[startSubject], "Y-m-d H:i:s"),
					endDay = Ext.Date.parse(element[endSubject], "Y-m-d H:i:s"),
					clCurrentDay = Ext.Date.clearTime(day, true),
					clStartDay = Ext.Date.clearTime(startDay, true),
					clEndDay = Ext.Date.clearTime(endDay, true);
				
				if(Ext.Date.between(clCurrentDay, clStartDay, clEndDay))				
				{
					return(element)
				}
			})			
			
			//формирование значений для ячейки
			var formatedCellData = [];
			
			m.forEach(function(element, index, array) {
				
				//установка уникальных значений цветов для каждого интервала 
				var ind = element[subject_id]
				if(colorArray[ind])
				{
					colorArray[ind].active = true;
				}
				else
				{
					var col = '#'+(Math.random()*0xFFFFFF<<0).toString(16);
					if (col.length != 7) col='#ffa84c';
					colorArray[ind] = {color: col, active: true};
				}
				//конец цвета
				formatedCellData.push({
					subject_id: element[subject_id],
					startDate: element[startSubject],
					endDate: element[endSubject],
					currentData: Ext.Date.clearTime(day, true),
					color: colorArray[ind].color,
					rowIndex: i,
					countIntervals: 0,
					posInCell: index,
					uniq : (idInBase)? element[idInBase] : element.id
				})
			}.bind(this))		
			
			dataCells.push(formatedCellData);			
		}

		//создаем данные-поля (столбцы)
		var colDataArray = [];
		colDataArray['hour'] = 0;
		
		this.fieldsNames = [];
		this.fieldsNames.push('hour');
		
		var countIntervalsInColumn = [];
		
		for (var i = 0; i < dataCells.length; i++) {	
			//здесь устанавливаем количество интервалов в столбцы			
			countIntervalsInColumn[i] = dataCells[i].length;
			
			colDataArray['dataCells'+i] = dataCells[i];
			this.fieldsNames.push('dataCells'+i);
		}
		
		//создаем строки (записи)
		for (var i = 0; i < 24; i++) {
			//строка
			colDataArray['hour'] = i+':00';
			//определяем принадлежность по часам
			gridData[i]=[];
			for(var j in colDataArray){
				if (colDataArray[j].length > 0 && typeof colDataArray[j]!='string')
				{					
					var activeTime = [];
					colDataArray[j].forEach(function(element, index, array) {
						var dayStart = Ext.Date.parse(element.startDate, "Y-m-d H:i:s"),
							dayHourStart = Ext.Date.format(dayStart, 'G'),						
							dayEnd = Ext.Date.parse(element.endDate, "Y-m-d H:i:s"),
							dayHourEnd = Ext.Date.format(dayEnd, 'G'),						
							curDay = Ext.Date.clearTime(element.currentData, true);	
							
						//устанавливаем значение кол-ва интервалов в значение ячейки
						element.countIntervals = countIntervalsInColumn[element['rowIndex']];

						//если интервал в одном дне
						if( (Ext.Date.isEqual(curDay, Ext.Date.clearTime(dayStart, true))) && (Ext.Date.isEqual(curDay, Ext.Date.clearTime(dayEnd, true))) )
						{
							if (((dayHourStart <= i && dayHourEnd > i) ) || ((dayHourStart == i && dayHourEnd == i)) || (dayHourStart <= i && dayHourEnd==23))
							{
								activeTime.push(colDataArray[j][index]);
							}
						}
						else{
							//интервал на несколько дней
							//первый день
							if(Ext.Date.isEqual(curDay, Ext.Date.clearTime(dayStart, true)))
							{

								if (dayHourStart <= i)
								{
									activeTime.push(colDataArray[j][index]);
								}
							}

							//последний день
							if(Ext.Date.isEqual(curDay, Ext.Date.clearTime(dayEnd, true)))
							{
								if (dayHourEnd > i)
								{
									activeTime.push(colDataArray[j][index]);
								}
							}

							//день посередине
							if( curDay > Ext.Date.clearTime(dayStart, true) && curDay < Ext.Date.clearTime(dayEnd, true) )
							{
								activeTime.push(colDataArray[j][index]);
							}
						}						
						gridData[i][j]=activeTime;
					})
				}
				else{gridData[i][j]=colDataArray[j];}
			}
		}
//создание / переопределение стора и модели
		Ext.create('Ext.data.Store', {
			storeId:'simpsonsStore',
			fields: this.fieldsNames,
			data:{
				items : gridData
			},
			proxy: {
				type: 'memory',
				reader: {
					type: 'json',
					root: 'items'
				}
			}
		});		
	},
	
	renderCells: function(v, meta, rec, rowIndex, colIndex){
		//отображение ячейки
		//!осторожно, этот участок кода проклят ... дважды
		
			var count = v.length,
				cellHtml = '',
				countInCell = v[0].countIntervals,
				margin = 1,
				intervalWidth = (  Math.round ( (100/countInCell)-margin )  );
			//рендеринг - берем все интервалы и бежим по ним
			for (var i = 0; i < countInCell; i++) {				
				if(v[i])
				{
					var curse = (v[i].posInCell*intervalWidth)+margin*v[i].posInCell;
					cellHtml += '<div class="dragEl interval-'+v[i].uniq+'" subjid="'+v[i].subject_id+'" style="position: absolute; top: 0; left:'+curse+'%; background:' + v[i].color + '; width: '+intervalWidth+'%; height: 21px;"></div>';
				}				
			}
			return cellHtml
	},
	
	reconfigLinkedGrid: function(){		
		if (this.linkGrid)
		{
			var b = [],
				id = this.subject_id,
				colors = this.colorArray,
				existLegend = false,
				grid = this.linkGrid,
				multiDrag = this.linkGrigMultipleDrag;
			
			//добавляем колонку чекеров
			if(multiDrag)
			{
				b.push({ dataIndex: 'dragGroup', text: '', hideable: false, width: 30, id: 'dGroup', xtype: 'checkcolumn', sortable: false, hideable: false,
					listeners: {
						//выделяем особо отмеченные строки
						checkchange: function(cmp, rowIndex, checked, eOpts) {
							var grid = cmp.up('grid'),
								view = grid.getView();

							if(checked)	{view.addRowCls(rowIndex, 'selected-for-drop');}
							else {view.removeRowCls(rowIndex, 'selected-for-drop');}
						},
						//обнуляем выделенные
						added: function(cmp) {
							grid.store.queryBy(function(r) {
								if (r.get('dragGroup') == true)
								{r.set('dragGroup', false)}								
							});	
						}
					}
				})
			}
			
			grid.columns.forEach(function( el,index, arr){
				b.push(el.initialConfig);
				if (el.initialConfig.id == 'colorLegend') existLegend = true;
			});
			
			//добавляем колонку легенд
			if(!existLegend)
			{
				b.push({ dataIndex: id, text: '', hideable: false, width: 30, id: 'colorLegend',
					renderer: function(value) {
						if (colors[value] && colors[value].active)
						{
							return Ext.String.format('<div style="height: 10px; width: 10px; background-color: '+colors[value].color+';"></div>');
						}
					}.bind(this)
				})				
			}
			grid.reconfigure(grid.store, b)
		}
	},

	reloadData: function(){
		Ext.suspendLayouts();
		var	colors = this.colorArray;
		
		Ext.Object.each(colors, function(key, value, myself) {			
			colors[key].active = false
		})
		this.loadData(this.gridData, this.datesInterval);
		this.reconfigure(Ext.data.StoreManager.lookup('simpsonsStore'), this.createColumns(this.datesInterval));
		this.reconfigLinkedGrid();
		this.fireEvent('refreshData');
		Ext.resumeLayouts(true);
	},
	
	deleteInterval: function(record, rowIndex, cellIndex, subjid){
		var data = this.gridData,
			usrStartDate = this.startDate,
			usrEndDate = this.endDate,
			usrSubject_id = this.subject_id,
			deletedRec = {},
			currRec = record.get('dataCells' + cellIndex),
			interval;
			
		interval = currRec.filter(function( obj ) {
			return obj.subject_id == subjid
		})[0]
		
		for(var n in data){
			if (
				( data[n][usrSubject_id] == subjid ) &&
				( data[n][usrStartDate] == interval.startDate ) &&
				( data[n][usrEndDate] == interval.endDate )
			){
				//удаляем вот эту вот запись
				deletedRec = data[n];
				Ext.Array.erase(data, n, 1);
			}
		}
		this.gridData = data;
		
		this.fireEvent('intervalDelete', deletedRec)
		this.reloadData();
	},
	
	moveInterval: function(intervalId, uniq, oldStartDate, oldEndDate, newStartDate, newEndDate){
		var data = this.gridData,
			usrStartDate = this.startDate,
			usrEndDate = this.endDate,
			usrSubject_id = this.subject_id,
			idInBase = this.addedRec_id,
			interval;
		
		for(var n in data){
			if (				
				( data[n][usrSubject_id] == intervalId ) &&
				(( data[n][idInBase] == uniq) || (data[n].id == uniq) )
			)
				{			
					data[n][usrStartDate] = Ext.Date.format(newStartDate, "Y-m-d H:i:s");
					data[n][usrEndDate] = Ext.Date.format(newEndDate, "Y-m-d H:i:s");
					newStartDate = Ext.Date.format(newStartDate, "Y-m-d H:i:s");
					newEndDate = Ext.Date.format(newEndDate, "Y-m-d H:i:s");
					this.fireEvent('intervalChange', intervalId, uniq, oldStartDate, oldEndDate, newStartDate, newEndDate);
				}
		}
		this.gridData = [];
		this.gridData = data;
		this.reloadData();
	},
	
	addInterval: function(mixIntervals){
		var data = this.gridData,
			usrStartDate = this.startDate,
			usrEndDate = this.endDate,
			usrSubject_id = this.subject_id,
			idInBase = this.addedRec_id,
			newIntervalsArray = [];
		
		if(mixIntervals)
		{
			mixIntervals.each(function(item, index, count){
				
				var idRec = item.record.get(this.subject_id),
					startDate = item.startDate,
					endDate = Ext.Date.add(startDate, Ext.Date.HOUR, (Math.round(item.hours)) ),
					interval = {};
					
				if(Ext.Date.format(endDate, 'H') == '00' ){
					endDate = Ext.Date.add(endDate, Ext.Date.MINUTE, -1)
				}
				
				if(Ext.Date.format(startDate, 'H') == '00' ){
					startDate = Ext.Date.add(startDate, Ext.Date.MINUTE, 0)
				}
				
				interval[usrSubject_id] = idRec;
				interval[usrStartDate] = Ext.Date.format(startDate, "Y-m-d H:i:s");
				interval[usrEndDate] = Ext.Date.format(endDate, "Y-m-d H:i:s");
				
				newIntervalsArray.push(interval);

				
			}.bind(this))

			//вызываем event на добавление и принимаем callback из num
			//и настраиваем новые интервалы id-шниками пришедшими из базы
			//после ответа обновляем грид
			this.fireEvent('intervalAdd', newIntervalsArray, function(num){
				for(var n in num){
					for(var j in newIntervalsArray){
						if( newIntervalsArray[j][usrSubject_id] == num[n][0][usrSubject_id])
						{
							if(idInBase){
								newIntervalsArray[j][idInBase] = num[n][0][idInBase];
							}								
							data.push(newIntervalsArray[j])
						}
					}					
				}
			this.gridData = [];
			this.gridData = data;
			this.reloadData();
				
			}.bind(this))			
		}
	}

})