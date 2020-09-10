Ext6.define('common.PolkaWP.RemoteMonitoring.ObserveChartMeasures', {
	requires: [
		'Ext6.draw.engine.Svg'
	],
	extend: 'Ext6.Panel',
	cls: 'observe_chart_measures',
	region: 'center',
	layout: 'anchor',
	//~ scrollable: true,
	border: false,
	params: {},
	paramsgraf: {},
	totalCount: 0,
	isEmk: false,
	grafDay: '', //id
	setParams: function(data) {
		
		var me = this;
		me.Label_id = (data.Label_id) ? data.Label_id : null;
		me.Person_id = data.Person_id;
		me.Chart_id = (data.Chart_id) ? data.Chart_id : null;
		me.params = {
			Person_id: data.Person_id,
			Chart_id: (data.Chart_id) ? data.Chart_id : null,
			start: 0,
			limit: 1 // неделя, 2=2недели , 3=месяц
		};
		me.paramsgraf = {
			Person_id: data.Person_id,
			Chart_id: (data.Chart_id) ? data.Chart_id : null,
			start: 0,
			limit: 1 // неделя, 2=2недели , 3=месяц
		};
		
		if (data.isEmk) {
			if(me.queryById('addMeasures')) {
				me.queryById('addMeasures').disable();
			}
			
			me.isEmk = data.isEmk;
		}
	},
	getRateByTypeId: function(id) {
		var me = this;
		return me.rates.find(function(el) { return (el.RateType_id==id); });
	},
	updateMinMaxLabel: function(rate) {
		var t = this.grid.queryById('ratetype'+rate.RateType_id+'minmaxlabel');
		if(t) t.setHtml(
			((rate.ChartRate_Min != null) ? rate.ChartRate_Min : '') 
			+((rate.ChartRate_Max != null) ? '-'+rate.ChartRate_Max : '')
		);
	},
	showColumns: function(rates) {
		var me = this;
		
		this.grid.columns.forEach(function(column){
			if(column.rt_id) {
				if(rates.includes(column.rt_id)) column.show();
				else column.hide();
			}
			
			if (column.dataIndex === 'TimeOfDay_id' && me.isEmk ) {
				column.hide();
			}
		});
	},
	listeners: {
		resize: function() {
			var me = this;
			if(me.cardPanel.getLayout().getActiveItem().itemId == 'grafcard') {
				var w = me.grafPanel.getWidth();
				if(me.grafPulse.getMainRect())
					me.grafPulse.setWidth(w);
				if(me.chartAD.getMainRect())
					me.chartAD.setWidth(w);
				if(me.grafTemperature.getMainRect())
					me.grafTemperature.setWidth(w);
			}
		}
	},
	setRates: function(rates) {
		var me = this;
		me.rates = rates;
		rt_ids = [];
		me.rates.forEach(function(rate) {
			rt_ids.push(Number(rate.RateType_id));
			me.updateMinMaxLabel(rate);
		});

		me.showColumns(rt_ids);
	},
	setTotalCount: function(n) {
		var me = this;
		me.totalCount = n;
		if(me.ownerWin.queryById('tabMeasures')) {
			me.ownerWin.queryById('tabMeasures').setTitle('Показания <span class="number-indicator">'+(n>0 ? n : '')+'</span>');
		}
	},
	load: function() {
		var me = this;
		if(me.ownerWin && me.ownerWin.LoadMask)
			me.ownerWin.LoadMask.show();

		if(me.isEmk) {
			me.gridToolbar.show();
			me.queryById('toggleGridGrafForEmk2').show();
			
			var plugins = me.grid.getPlugins();
			if (plugins.length && plugins.length > 0) {
				if (plugins[0].ptype && plugins[0].ptype == 'rowediting') {
					// отключаем возможность построчного редактирования
					plugins[0].disable();
				}
			}
		}
		
		me.queryById('periodtabs').setActiveTab(2);
		
		var header = me.chartAD.getHeader();
		header.remove(me.chartAD.collapseTool, false);
		header.insert(0, me.chartAD.collapseTool);
		header = me.grafPulse.getHeader();
		header.remove(me.grafPulse.collapseTool, false);
		header.insert(0, me.grafPulse.collapseTool);
		header = me.grafTemperature.getHeader();
		header.remove(me.grafTemperature.collapseTool, false);
		header.insert(0, me.grafTemperature.collapseTool);
		
		if(!me.rates) {
			Ext6.Ajax.request({
				params: {
					Person_id: me.Person_id,
					Chart_id: me.Chart_id
				},
				callback: function(options, success, response) {
					if (success) {
						var rdata = Ext6.JSON.decode(response.responseText);

						me.rates = [];
						if(rdata.rates.length>0) {
							me.setRates(rdata.rates);
						}
						me.grid.load();
					} else if(me.ownerWin && me.ownerWin.LoadMask) me.ownerWin.LoadMask.hide();
				},
				url: '/?c=PersonDisp&m=getPersonChartInfo'
			});
		} else me.grid.load();
		
		if(me.cardPanel.getLayout().getActiveItem().itemId=='grafcard') me.loadchart();
	},
	CellRenderer: function (value, metaData, record) {
		var rate = this.ownerGrid.ownerCt.ownerCt.ownerCt.getRateByTypeId(metaData.column.rt_id);
		
		if (rate.RateValueType_id == 5) {
			value = (value == 2)? 'Да' : ''
		}
		
		var val = parseFloat(value),
			rmax = parseFloat(rate.ChartRate_Max),
			rmin = parseFloat(rate.ChartRate_Min);
		metaData.tdCls = "";
		if(!Ext6.isEmpty(rate) && !Ext6.isEmpty(val)) {
			if(val > rmax) {
				if(val - rmax > (metaData.column.rt_id==203 ? 0.4 :4) )
					metaData.tdCls = "observe-chart-rate-overtoo";
				else
					metaData.tdCls = "observe-chart-rate-over";
			}
			if(val < rmin) {
				if (rmin - val > (metaData.column.rt_id==203 ? 0.4 :4) )
					metaData.tdCls = "observe-chart-rate-overtoo-low";
				else
					metaData.tdCls = "observe-chart-rate-over-low";
			}
		}
		return value;
	},
	initComponent: function() {
		var me = this;
		me.gridcolumns = [
			{	text: langs('День'), dataIndex: 'ObserveDate', xtype: 'datecolumn', width: 105, minWidth: 105,
				sortable: false,
				format: 'd.m.Y',
				renderer: function (value, metaData, record) {
					var v=value;
					if(typeof v=='object') v=Ext6.Date.format(v, 'd.m.Y');
					var dt = Date.now();
					dt.setDate(dt.getDate()-1);
					if(v==Ext6.Date.format(Date.now(), 'd.m.Y')) return "Сегодня";
					else if(v==Ext6.Date.format(dt, 'd.m.Y')) return "Вчера";
					else return v;
				},
				editor: {
					//xtype: 'swDateField',
					xtype: 'datefield',
					startDay: 1,
					format: 'd.m.Y',
					invalidText: 'Неправильная дата',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
					formatText: null,
					listeners: {
						expand: function ( field, eOpts ) {
							field.setMaxValue(Date.now());
						}
					}
				}
			},

			{	header: langs(''), dataIndex: 'TimeOfDay_id', type: 'int', width: 70, minWidth: 70, hidden: false, //disabled: true,
				sortable: false,
				cls: 'timecolumn',
				renderer: function (value, metaData, record) {
					if(value==1) return 'Утро';
					else if(value==2) return 'Вечер';
					else return '...';
				},
				listeners: {
					resize: function( el, info, eOpts ) {
					}
				},
				editor:  
				{
					xtype: 'combo',
					allowBlank: false,
					displayField: 'timename',
					valueField: 'id',
					disabled: true,
					hideTrigger: true,
					store: Ext6.create('Ext6.data.Store', {
						fields: ['id', 'timename'],
						data : [
							{"id": 1, "timename":"Утро"},
							{"id": 2, "timename":"Вечер"}
						]
					})
				}
				/*{	//двойное поле (комбо / время)
					xtype: 'container',
					type: 'combo',
					flex : 1,
					border: false,
					owner: me.grid,
					setType: function(name) {
						switch(name) {
							case 'time':
								this.items.getAt(0).show();
								this.items.getAt(0).focus();
								this.items.getAt(1).hide();
								break;
							case 'combo':
								this.items.getAt(1).show();
								this.items.getAt(1).focus();
								this.items.getAt(0).hide();
								break;
						}
						this.type = name;
					},
					setValue: function(x) {
						return x;
					},
					getValue: function() {
						return this.items.getAt(1).getValue();
					},
					setOriginalValue: function() {
						return 1;
					},
					resetOriginalValue: function(x) {
						if(!Ext6.isEmpty(this.items.getAt(0).getValue()))
							this.setType('time');
						else
							this.setType('combo');
					},
					isValid: function() {
						return 1;
					},
					items: [
						{
							xtype: 'swTimeField',
							allowBlank: true,
							width: '100%',
							userCls:'vizit-time',
							hideLabel: true,
							hidden: true
						},
						{
							xtype: 'baseCombobox',
							allowBlank: false,
							width: '100%',
							matchFieldWidth: false,
							displayField: 'timename',
							valueField: 'id',
							store: Ext6.create('Ext6.data.Store', {
								fields: ['id', 'timename'],
								data : [
									{"id": 1, "timename":"Утро"},
									{"id": 2, "timename":"Вечер"},
									{"id": 0, "timename":"Точное время"},
								]
							}),
							listeners: {
								change: function(field, newVal, oldVal) {
									if(newVal==0) {
										field.ownerCt.setType('time');
									}
								}
							}
						}
					]
				} */ //двойное поле
				
			}, {
				header: langs('Время'), dataIndex: 'ObserveTime', type: 'string', width: 78, minWidth: 78, hidden: false,
				sortable: false,
				//~ cls: 'timecolumn',
				renderer: function (value, metaData, record) {
					return Ext6.Date.format(record.get('ObserveDate'), 'H:i');
				},
				editor:  
				{
					xtype: 'swTimeField',
					allowBlank: true,
					width: '100%',
					userCls:'vizit-time',
					hideLabel: true
				}
			},
			{
				header: langs('Источник'), dataIndex: 'LabelObserveChartSource_Name', type: 'string', width: 120, hidden: false,
				sortable: true,
				listeners: {
					resize: function( el, info, eOpts ) {
					}
				},
			},
			{	header: langs('САД'), dataIndex: 'ratetype53value', type: 'int', width: 65, minWidth: 65,
				sortable: false,
				rt_id: 53,
				editor: {
					xtype: 'numberfield',
					itemId: 'numberfield53',
					hideTrigger: true,
					minValue: 0,
					maxValue: 10000,
					allowDecimals: false
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype53minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('ДАД'), dataIndex: 'ratetype54value', type: 'int', width: 65, minWidth: 65,
				sortable: false,
				rt_id: 54,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 10000,
					allowDecimals: false
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype54minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('ЧСС'), dataIndex: 'ratetype38value', type: 'int', width: 65, minWidth: 65,
				sortable: false,
				rt_id:38,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 10000,
					allowDecimals: false
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype38minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},
			
			{	header: langs('Температура'), dataIndex: 'ratetype203value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:203,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 10000,
					allowDecimals: true
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype203minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('SpO2'), dataIndex: 'ratetype209value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:209,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype209minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('Повышенная температура'), dataIndex: 'ratetype210value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:210,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Одышка'), dataIndex: 'ratetype211value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:211,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Кашель'), dataIndex: 'ratetype212value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:212,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Насморк'), dataIndex: 'ratetype213value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:213,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Боль в горле'), dataIndex: 'ratetype214value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:214,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Мокрота'), dataIndex: 'ratetype215value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:215,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 100,
					allowDecimals: true
				},
				renderer: me.CellRenderer
			},

			{	header: langs('Сахар в крови'), dataIndex: 'ratetype193value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:193,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 20,
					allowDecimals: true
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype193minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('Холестерин'), dataIndex: 'ratetype192value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:192,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 0,
					maxValue: 20,
					allowDecimals: true
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype192minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},

			{	header: langs('Гемоглобин'), dataIndex: 'ratetype13value', type: 'int', width: 100, minWidth: 90,
				sortable: false,
				rt_id:13,
				editor: {
					xtype: 'numberfield',
					hideTrigger: true,
					minValue: 100,
					maxValue: 200,
					allowDecimals: true
				},
				items: [
					{
						xtype: 'label',
						itemId: 'ratetype13minmaxlabel',
						html: '',
						padding: '0 0 0 10',
						style: 'font: 400 11px/14px Roboto, Helvetica, Arial, Geneva, sans-serif;'
					}
				],
				renderer: me.CellRenderer
			},
			
			{	header: langs('Примечание'), dataIndex: 'Complaint', type: 'string', width: 170,
				sortable: false,
				editor: {
					xtype: 'textfield'
				},
				tdCls: 'dmGridTd',
				renderer: function(value, metaData, record) {
					var s="<span class='dm-row-toolblock' style='float: right;'>";
					s+="<span class='dm-row-tool dm-row-tool-edit' data-qtip='Редактировать' onclick='Ext6.getCmp(\"" + me.grid.id + "\").doEdit("+record.get('id')+")'></span>";
					//~ s+="<span class='dm-row-tool dm-row-tool-copy' data-qtip='Копировать' onclick='alert(1);'></span>";
					//~ s+="<span class='dm-row-tool dm-row-tool-del' data-qtip='Удалить' onclick='Ext6.getCmp(\"" + me.grid.id + "\").doDelete("+record.get('id')+")'></span>";
					s+="</span>";
					
					return "<span>"+(value ? value : '')+"</span>" + s
				}
			},
			{	header: langs('Способ обратной связи'), dataIndex: 'FeedbackMethod_id', type: 'int', hidden: true },
			{	header: langs('id измерения'), dataIndex: 'ChartInfo_id', type: 'int', hidden: true }
		];

		me.gridToolbar = new Ext6.Toolbar({
			xtype: 'toolbar',
			cls: 'grid-toolbar',
			border: false,
			hidden: false,
			dock: 'top',
			items: [{
				xtype: 'button',
				text: langs('Добавить показания'),
				iconCls: 'panicon-add',
				itemId: 'addMeasures',
				handler: function() {
					var rec = new Ext6.data.Record();
					var	now = Date.now();
					rec.set('ObserveDate', now);
					rec.set('ObserveTime', Ext6.Date.format(now, 'H:i'));
					rec.set('TimeOfDay_id', now.getHours()<13 ? 1 : 2);
					//~ me.grid.getStore().add(rec);
					//~ me.grid.setSelection(rec);
					me.setTotalCount(me.totalCount+1);
					me.grid.getStore().insert(0, rec);
					me.grid.findPlugin('rowediting').startEdit(rec, 2);//селект на поле САД
				}
			},{
				xtype: 'button',
				text: langs('Обновить'),
				iconCls: 'action_refresh',
				handler: function() {
					me.load();
				}
			}, {
				xtype: 'button',
				text: langs('Удалить показания'),
				itemId: 'deleteMeasure',
				disabled: true,
				hidden:true,
				iconCls: 'panicon-delete-minus',
				handler: function() {
					//TAG: Удаление замера
					if(me.grid.getSelection().length>0) {
						var rec = me.grid.getSelection()[0];
						
						if(Ext6.isEmpty(rec.get('ChartInfo_id'))) { //если не успели сохранить
							me.grid.getStore().remove(rec);
							me.setTotalCount(me.totalCount-1);
						} else
						Ext6.Ajax.request({
							url: '?c=PersonDisp&m=deleteLabelObserveChartMeasure',
							params: {
								ChartInfo_id: rec.get('ChartInfo_id'),
							},
							success: function(response){
								var res = Ext6.JSON.decode(response.responseText);
								if(!Ext6.isEmpty(res.Error_Msg)) {
									Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
								} else {
									me.grid.getStore().remove(rec);
									me.setTotalCount(me.totalCount-1);
								}
							}
						});
					}
				}
			}, '->', {
				xtype: 'segmentedbutton',
				userCls: 'segmentedButtonGroup',
				itemId: 'toggleGridGrafForEmk1',
				items: [{
					text: 'Таблица',
					pressed: true,
					handler: function() {
						me.cardPanel.setActiveItem(0);
						me.queryById('toggleGridGrafForEmk2').setValue(0);
					}
				}, {
					text: 'График',
					handler: function() {
						me.cardPanel.setActiveItem(1);
						me.queryById('toggleGridGrafForEmk2').setValue(1);
						me.loadchart();
					}
				}]
			}]
		});

		me.grid = new Ext6.grid.Panel({
			border: false,
			xtype: 'row-editing',
			plugins: {
				//~ ptype: 'cellediting',
				ptype: 'rowediting',
				//~ clicksToMoveEditor: 1,
				autoCancel: true,
				//~ clicksToEdit: 1
			},
			autoScroll: true,
			editIndex: -1,
			load: function() {
				Ext6.Ajax.request({
					url: '/?c=PersonDisp&m=loadLabelObserveChartMeasure',
					params: me.params,
					callback: function(options, success, response)
					{
						if ( success )
						{
							//TAG: загрузка таблицы измерений
							var data = Ext6.util.JSON.decode(response.responseText);

							if(me.params.start==0) {
								me.grid.getStore().removeAll();
								me.queryById('deleteMeasure').disable();
							}
							
							me.setTotalCount(data.data.totalCount);
							me.grid.getStore().add_data(data.data);
							me.queryById('nextMeasure').setVisible(data.data.totalCount > me.grid.getStore().getCount());
							if(me.ownerWin && me.ownerWin.LoadMask && me.ownerWin.LoadMask.isVisible()) me.ownerWin.LoadMask.hide();
						}
						if(me.ownerWin && me.ownerWin.LoadMask) me.ownerWin.LoadMask.hide();
					}
				});
			},
			doCopy: function() {
				var grid = this;	
				var index = grid.editIndex;
				if(index>=0) {
					var rec1 = grid.getStore().getAt(index);
					grid.findPlugin('rowediting').cancelEdit(index);
					var data = rec1.data;
					data.id = null;
					var rec = new Ext6.data.Record();
					rec.set('Complaint', data['Complaint']);
					rec.set('FeedbackMethod_id', data['FeedbackMethod_id']);
					rec.set('ratetype38value', data['ratetype38value']);
					rec.set('ratetype53value', data['ratetype53value']);
					rec.set('ratetype54value', data['ratetype54value']);
					rec.set('ratetype203value', data['ratetype203value']);
					var now = Date.now();
					rec.set('ObserveDate', now);
					rec.set('ObserveTime', Ext6.Date.format(now, 'H:i'));
					rec.set('TimeOfDay_id', now.getHours()<13 ? 1 : 2);
					me.setTotalCount(me.totalCount+1);
					grid.getStore().insert(0, rec);
					grid.findPlugin('rowediting').startEdit(rec, 2);//селект на поле САД
				}
			},
			doDelete: function() {
				var grid = this;	
				var index = grid.editIndex;
				var plugin = grid.findPlugin('rowediting');
				
				if(plugin.context.record) {
					var rec = plugin.context.record;
					grid.findPlugin('rowediting').cancelEdit(rec);
					if(Ext6.isEmpty(rec.get('ChartInfo_id'))) { //если не успели сохранить
						grid.getStore().remove(rec);
						me.setTotalCount(me.totalCount-1);
					} else
					Ext6.Ajax.request({
						url: '?c=PersonDisp&m=deleteLabelObserveChartMeasure',
						params: {
							ChartInfo_id: rec.get('ChartInfo_id'),
						},
						success: function(response){
							var res = Ext6.JSON.decode(response.responseText);
							if(!Ext6.isEmpty(res.Error_Msg)) {
								Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
							} else {
								grid.getStore().remove(rec);
								me.setTotalCount(me.totalCount-1);
							}
						}
					});
				}
			},
			doEdit: function(id) {
				var grid = this;
				var index = grid.store.findBy(function(record) { return record.get('id')==id })
				if(index>=0) {
					grid.editIndex = index;
					grid.findPlugin('rowediting').startEdit(index);
				}
			},
			//TAG: выключатель меток для коммита
			viewConfig:{
				markDirty:false
			},
			dockedItems: me.gridToolbar,
			cls:'panel_create_cust',
			listeners: {
				select: function() {
					me.queryById('deleteMeasure').enable();
				},
				deselect: function() {
					me.queryById('deleteMeasure').disable();
				},
				edit: function (editor, e, eOpts) {
					//TAG: сохранение измерения
					e.record.set('ObserveTime', e.newValues['ObserveTime'] );
					var RateMeasures = [];
					me.rates.forEach(function(r){
						val=e.newValues['ratetype'+r.RateType_id+'value'];
						
						measure_id=e.record.get('ratetype'+r.RateType_id+'measureid');

						if(val) {
							RateMeasures.push({
								ChartRate_id: r.ChartRate_id,
								Measure_value: val,
								Measure_id: measure_id
							});
						}
						
						e.record.set('ratetype'+r.RateType_id+'value', val);
					});

					Ext6.Ajax.request({
						url: '?c=PersonDisp&m=saveLabelObserveChartMeasure',
						params: {
							ChartInfo_id: e.record.get('ChartInfo_id'),
							Chart_id: me.Chart_id,
							ObserveDate: Ext6.Date.format(e.newValues['ObserveDate'], 'Y-m-d') 
								+ ' ' + (!Ext6.isEmpty(e.newValues['ObserveTime']) ? e.newValues['ObserveTime']+':00' : ''),
							ObserveTime_id: e.newValues['TimeOfDay_id'],
							FeedbackMethod_id: e.newValues['FeedbackMethod_id'],
							Complaint: e.newValues['Complaint'],
							RateMeasures: Ext6.util.JSON.encode(RateMeasures)
						},
						success: function(response){
							var res = Ext6.JSON.decode(response.responseText);
							if(!Ext6.isEmpty(res.Error_Msg)) {
								Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
							} else {
								//сохранить строку
								e.record.set('ObserveDate', Ext6.Date.parseDate(Ext6.Date.format(e.newValues['ObserveDate'],'Y-m-d')+' '+e.newValues['ObserveTime'], 'Y-m-d H:i' ));
								e.record.set('ObserveTime', e.newValues['ObserveTime'] );
								e.record.set('TimeOfDay_id', e.record.get('ObserveDate').getHours()<13 ? 1 : 2 /*e.newValues['TimeOfDay_id']*/ );
								e.record.set('Complaint', e.newValues['Complaint']);
					
								if(Ext6.isEmpty(e.record.get('ChartInfo_id')) && !Ext6.isEmpty(res[0].LabelObserveChartInfo_id))
									e.record.set('ChartInfo_id', res[0].LabelObserveChartInfo_id);
								
								if(me.ownerWin && me.ownerWin.ownerWin) {//из окна мониторинга - обновим и там в таблице
									var toprecord = me.grid.getStore().getAt(0);
									var rt_ids=[53,54,38,203];
									var sel = me.ownerWin.ownerWin.grid.getSelection();
									if(sel.length>0) {
										var rec = sel[0];
										rec.set('lastObserveDate', toprecord.get('ObserveDate').dateFormat('d.m.Y') );
										rt_ids.forEach(function(id){
											if(toprecord.get('ratetype'+id+'value')) {
												var LabelRate_id = rt_ids.indexOf(id)+1;
												rec.set('Rate'+LabelRate_id+'_Value', toprecord.get('ratetype'+id+'value'));
												rec.commit();
											}
										});
									}
								}
								if(res['measure'] && res['measure'][0] && res['measure'][0].length==2 && res['measure'][0][1]['ChartRate_id'] && res['measure'][0][0]['LabelObserveChartMeasure_id']) {
									var measure = me.rates.find(function(rec) {return rec['ChartRate_id']==res['measure'][0][1]['ChartRate_id'];  });
									if(measure && measure['RateType_id']) {
										var fieldname = 'ratetype'+measure['RateType_id']+'measureid';
										
										if(Ext6.isEmpty(e.record.get(fieldname)) && Ext6.isEmpty(e.record.get(fieldname))) {
											e.record.set(fieldname, res['measure'][0][0]['LabelObserveChartMeasure_id']);
										}
										e.record.commit();
									}
								}
							}
						}
					});
				}
			},
			store: new Ext6.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'ObserveDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'ObserveTime', type: 'string' },
					{ name: 'TimeOfDay', type: 'int' },
					{ name: 'ratetype53value', type: 'int' },
					{ name: 'ratetype54value', type: 'int' },
					{ name: 'ratetype38value', type: 'int' },
					{ name: 'Complaint', type: 'string' }
				],
				data: [],
					sorters: [{
						property: 'ObserveDate',
						direction: 'DESC'
					},{
						property: 'TimeOfDay_id',
						direction: 'DESC'
					}, {
						property: 'ChartInfo_id',
						direction: 'DESC'
					}
				],
				add_data: function(data) {
					if (typeof(data) != 'object') return false;
					infodata = data.info;
					var record = null;
					for (var i = 0; i < infodata.length; i++) {
						record = new Ext6.data.Record(infodata[i]);
						
						record.set('ObserveDate', Ext6.Date.parseDate(record.get('ObserveDate'),'Y-m-d H:i'));
						record.set('ObserveTime', record.get('ObserveDate').dateFormat('H:i'));
						
						var time = new Date(record.get('ObserveDate').getTime());
						
						time.setHours( record.get('TimeOfDay_id')=="1" ? 12 : 23); 
						record.set('GrafDate', time);
						
						var ChartInfo_id = record.get('ChartInfo_id');
						if(data.measures)
						data.measures.forEach(function (measure) {
							var chartrate = me.getRateByTypeId(measure.RateType_id);
							
							
							if(measure.ChartInfo_id == ChartInfo_id) {
								if(me.grid.query('[dataIndex=ratetype'+measure.RateType_id+'value]').length) {
									record.set('ratetype'+measure.RateType_id+'value', measure.Value);
								}
								//~ if(me.grid.query('[dataIndex=ratetype'+measure.RateType_id+'measureid]').length)
									record.set('ratetype'+measure.RateType_id+'measureid', measure.Measure_id);
							}
						});
						record.commit();
						this.add(record);

					}
				}
			}),
			columns: me.gridcolumns
		});
		
		me.loadchart = function() {
			Ext6.Ajax.request({
				url: '/?c=PersonDisp&m=loadLabelObserveChartMeasure',
				params: me.paramsgraf,
				callback: function(options, success, response)
				{
					if ( success )
					{
						//TAG: загрузка графиков
						var data = Ext6.util.JSON.decode(response.responseText);
						if(data.data.totalCount==0) {
							me.chartAD.hide();
							me.grafPulse.hide();
							me.grafTemperature.hide();
							me.monthPanel.hide();
							me.queryById('doRight').setDisabled(true);
							me.queryById('doLeft').setDisabled(true);
							return;
						} else {
							var w = me.grafPanel.getWidth();
							me.chartAD.setWidth(w);
							me.grafPulse.setWidth(w);
							me.grafTemperature.setWidth(w);
							
							if (me.isEmk) {
								me.chartAD.setVisible(true);
								me.grafPulse.setVisible(true);
								me.grafTemperature.setVisible(true);
							} else {
								me.chartAD.setVisible(me.Label_id=='1');
								me.grafPulse.setVisible(me.Label_id=='1');
								me.grafTemperature.setVisible(me.Label_id=='7');
							}
						}
						
						//todo: это всю содомию нужно потом переделать нормально
						if (me.isEmk) {
							
							me.storeAD.removeAll();
							me.storeAD.add_data(data.data);
							me.storePulse.removeAll();
							me.storePulse.add_data(data.data);

							me.storeTemperature.removeAll();
							me.storeTemperature.add_data(data.data);
							
						} else {
							if(me.Label_id=='1') {
								me.storeAD.removeAll();
								me.storeAD.add_data(data.data);
								me.storePulse.removeAll();
								me.storePulse.add_data(data.data);
							}
							if(me.Label_id=='7') {
								me.storeTemperature.removeAll();
								me.storeTemperature.add_data(data.data);
							}
						}
						
						
						var minDT = Date.parse(data.data.minimax.minObserveDate);
						var maxDT = Date.parse(data.data.minimax.maxObserveDate);
											
						var dt0 = Date.parse(data.data.minimax.maxObserveDate);
						var dt1 = Date.parse(data.data.minimax.maxObserveDate);
						
						var i0 = me.paramsgraf.start,
							i1 = me.paramsgraf.start+1;
						
						switch(me.paramsgraf.limit) {
							case 1: 
								dt0.setDate(dt0.getDate()-7*i0+1);
								dt1.setDate(dt1.getDate()-7*i1+1);
								break;
							case 2: 
								dt0.setDate(dt0.getDate()-14*i0+1);
								dt1.setDate(dt1.getDate()-14*i1+1);
								break;
							case 3:
								dt0.setDate(dt0.getDate()-30*i0+1);//будем считать месяцем 30 дней. Как в ТЗ.
								dt1.setDate(dt1.getDate()-30*i1+1);
								
								//~ dt0.setMonth(dt0.getMonth()-i0);
								//~ dt1.setMonth(dt1.getMonth()-i1);
								break;
						}						
					
						var dayAxisAD = me.chartAD.getAxis(1);
						var dayAxisFSS = me.grafPulse.getAxis(1);
						var dayAxisTemperature = me.grafTemperature.getAxis(1);
						var dataTemperatureRange = me.grafTemperature.getSeries()[0].dataRange;
						
						
						
						me.chartAD.valuePadding = 5;//добавочные поля сверху и снизу (не пиксели, а значения). Иначе при одинаковых значениях методы setMaximum и setMinimum "съедят" весь график
						me.grafPulse.valuePadding = 5;
						me.grafTemperature.valuePadding = 5;
						
						var dataPulseRange = me.grafPulse.getSeries()[0].dataRange;
						var numAxisFSS = me.grafPulse.getAxis(0);
						me.grafPulse.maxval = dataPulseRange[3]+me.grafPulse.valuePadding;
						me.grafPulse.minval = dataPulseRange[1]-me.grafPulse.valuePadding;
						numAxisFSS.setMaximum(me.grafPulse.maxval);
						numAxisFSS.setMinimum(me.grafPulse.minval);
												
						me.grafTemperature.maxval = dataTemperatureRange[3]+me.grafTemperature.valuePadding;
						me.grafTemperature.minval = dataTemperatureRange[1]-me.grafTemperature.valuePadding;
						dayAxisTemperature.setMaximum(me.grafTemperature.maxval);
						dayAxisTemperature.setMinimum(me.grafTemperature.minval);
						
						numAxisTemperature = me.grafTemperature.getAxis(0);
						numAxisTemperature.setMaximum(me.grafTemperature.maxval);
						numAxisTemperature.setMinimum(me.grafTemperature.minval);
						
						var dataSadRange = me.chartAD.getSeries()[0].dataRange;
						var dataDadRange = me.chartAD.getSeries()[1].dataRange;
						numAxisAD = me.chartAD.getAxis(0);
						me.chartAD.maxval = dataSadRange[3] > dataDadRange[3] ? dataSadRange[3] : dataDadRange[3];
						me.chartAD.maxval+= me.grafPulse.valuePadding;
						me.chartAD.minval = dataSadRange[1] < dataDadRange[1] ? dataSadRange[1] : dataDadRange[1];
						me.chartAD.minval-= me.grafPulse.valuePadding;
						numAxisAD.setMaximum(me.chartAD.maxval);
						numAxisAD.setMinimum(me.chartAD.minval);
						
						dayAxisAD.setToDate(dt0); dayAxisAD.setFromDate(dt1);
						dayAxisFSS.setToDate(dt0); dayAxisFSS.setFromDate(dt1);
						dayAxisTemperature.setToDate(dt0); dayAxisTemperature.setFromDate(dt1);
						me.chartAD.redraw();
						me.grafPulse.redraw();
						me.grafTemperature.redraw();
						
						me.queryById('doRight').setDisabled(dt0 >= maxDT);
						me.queryById('doLeft').setDisabled(dt1 <= minDT);
						
						//рисуем диапазоны норм:
						me.chartAD.makeArea();
						me.grafPulse.makeArea();
						me.grafTemperature.makeArea();
						
						//скрываем метки горизонтальных координат:
						Ext6.getCmp(me.chartAD.getId()+'-'+me.chartAD.getAxes()[1].getId()).hide();
						Ext6.getCmp(me.grafPulse.getId()+'-'+me.grafPulse.getAxes()[1].getId()).hide();
						Ext6.getCmp(me.grafTemperature.getId()+'-'+me.grafTemperature.getAxes()[1].getId()).hide();
						
						me.chartAD.setTitle("<span class='dm-title-graf'>Артериальное давление</span> &nbsp;&nbsp; <span class='dm-title-graf-light'>норма "+me.chartAD.SADmin+" - "+me.chartAD.SADmax+" / "+me.chartAD.DADmin+" - "+me.chartAD.DADmax+"</span>");
						me.grafPulse.setTitle("<span class='dm-title-graf'>ЧСС</span> &nbsp;&nbsp; <span class='dm-title-graf-light'>норма "+me.grafPulse.min+" - "+me.grafPulse.max+"</span>");
						me.grafTemperature.setTitle("<span class='dm-title-graf'>Температура</span> &nbsp;&nbsp; <span class='dm-title-graf-light'>норма "+me.grafTemperature.min+" - "+me.grafTemperature.max+"</span>");
						
						var n = Math.floor((dt0.getTime() - dt1.getTime())/(1000*3600*24));
						var s='<table class="daysline"><tr>';
						
						var month = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
						for (i=0; i<n; i++) {
							var d = new Date(dt1.getTime()+i*(1000*3600*24));
							if(i==0) s+='<td>'+month[dt1.getMonth()]+'</td>';
							else if(d.getDate()==1 && dt0.getMonth()!=dt1.getMonth()) {
								s+='<td>'+month[dt0.getMonth()]+'</td>';
							} else s+='<td></td>';
						}
						
						s+='</tr><tr>';
						for (i=0; i<n; i++) {
							var d = new Date(dt1.getTime()+i*(1000*3600*24));
							s+='<td id="'+me.getId()+'-day-'+d.dateFormat('d-m')+'">'+d.getDate()+'</td>';
						}
						s+='<tr><table>';
						
						me.monthPanel.setHtml(s);
						me.monthPanel.show();
						
						setTimeout(function() {
							//пока так, т.к. иначе с axe по времени столбцы изначально прорисовываются разной ширины
							//причем выравниваются только после ресайза в ширину:
							var w = me.grafPanel.getWidth();
							if(me.grafTemperature.getMainRect()) {
								if(w!=me.grafTemperature.getWidth()) me.grafTemperature.setWidth(w);
								else me.grafTemperature.setWidth(me.grafTemperature.getWidth()-1);
							}
							if(me.grafPulse.getMainRect()) {
								if(w!=me.grafPulse.getWidth()) me.grafPulse.setWidth(w);
								else me.grafPulse.setWidth(me.grafPulse.getWidth()-1);
							}
							if(me.chartAD.getMainRect()) {
								if(w!=me.chartAD.getWidth()) me.chartAD.setWidth(w);
								else me.chartAD.setWidth(me.chartAD.getWidth()-1);
							}
						}, 500);
					}
				}
			});
		}
		
		me.storeAD = new Ext6.data.SimpleStore({
			autoLoad: false,
			fields: [],
			data: [],
				sorters: [{
					property: 'ObserveDate',
					direction: 'DESC'
				},{
					property: 'TimeOfDay_id',
					direction: 'DESC'
				}, {
					property: 'ChartInfo_id',
					direction: 'DESC'
				}
			],
			
			add_data: function(data) {
				if (typeof(data) != 'object') return false;
				infodata = data.info;
				var record = null;
				for (var i = 0; i < infodata.length; i++) {
					
					data.rates.forEach(function(rate) {
						if(rate.RateType_id == 53) {
							me.chartAD.SADmin = rate['ChartRate_Min'];
							me.chartAD.SADmax = rate['ChartRate_Max'];
						}
						if(rate.RateType_id == 54) {
							me.chartAD.DADmin = rate['ChartRate_Min'];
							me.chartAD.DADmax = rate['ChartRate_Max'];
						}
					});
					
					//TAG: заполнение Store
					record = new Ext6.data.Record(infodata[i]);
					record.set('ObserveDate', Date.parse(record.get('ObserveDate')));
					var time = new Date(record.get('ObserveDate').getTime());

					time.setHours( record.get('TimeOfDay_id')=="1" ? 8 : 16); 
					record.set('GrafDate', time);
					
					var ChartInfo_id = record.get('ChartInfo_id');
					
					data.measures.forEach(function (measure) {
						var chartrate = me.getRateByTypeId(measure.RateType_id);
						if(measure.RateType_id==53) {
							me.chartAD.SADmin = chartrate['ChartRate_Min'];
							me.chartAD.SADmax = chartrate['ChartRate_Max'];
						} else if(measure.RateType_id==54) {
							me.chartAD.DADmin = chartrate['ChartRate_Min'];
							me.chartAD.DADmax = chartrate['ChartRate_Max'];
						}
						record.set('grafratetype'+measure.RateType_id+'min', chartrate['ChartRate_Min']);
						record.set('grafratetype'+measure.RateType_id+'max', chartrate['ChartRate_Max']);
						
						
						if(measure.ChartInfo_id == ChartInfo_id) {
							if(me.grid.query('[dataIndex=ratetype'+measure.RateType_id+'value]').length) {
								record.set('ratetype'+measure.RateType_id+'value', measure.Value);
								record.set('grafratetype'+measure.RateType_id+'value', measure.Value);
							}
						}
					});
					record.commit();
					this.add(record);
				}
			}
		});
		
		
		
		me.chartAD = Ext6.create('Ext6.chart.CartesianChart', {
			itemId: 'chartAD',
			plugins: [ new Ext6.chart.plugin.ItemEvents() ],
			userCls: 'remote-monitoring-chart-ad',
			engine: Ext6.draw.engine.Svg,
			border: false,
			hideHeader: true,
			width: 100,
			animation: false,
			store: me.storeAD,
			collapsible: true,
			innerPadding: {
				left: 0,
				right: 0,
				top: 10,
				bottom: 10
			},
			makeArea: function() {
				//нарисовать прямоугольник (область нормы)
				var graf = me.chartAD;
				
				var area = graf.getMainRect(); //область графика [*,*, Xmax, Ymax]
				var Hscr = area[3] - graf.innerPadding.top - graf.innerPadding.bottom;
				var Hdata = graf.maxval-graf.minval;//высота в значениях

				var surface = graf.getSurface();
				
				if(graf.area1) graf.area1.remove();
				if(graf.area2) graf.area2.remove();
				
				var setka = [];
				surface.getItems().forEach(function(item){
					setka.push(item);
				});
				surface.removeAll();
				
				//полоса верхнего давления
				if(graf.minval > graf.SADmax || graf.maxval<graf.DADmin) {//полоса не пересекается с графиком
					if(graf.maxval < graf.DADmin) {//полоса выше
						graf.area1 = surface.add({
							type: 'rect',
							x: 0,
							y: 0,
							width: 2000,
							height:  graf.innerPadding.top,
							fillStyle: '#e0f2f1'
						});
					} else {//полоса ниже
						graf.area1 = surface.add({
							type: 'rect',
							x: 0,
							y: Hscr-graf.innerPadding.bottom,
							width: 2000,
							height: graf.innerPadding.bottom*5,
							fillStyle: '#e0f2f1'
						});
					}
				} else {
					graf.area1 = surface.add({
						type: 'rect',
						x: 0,
						y: graf.innerPadding.top + (graf.maxval-graf.SADmax)/Hdata*Hscr,
						width: 2000,
						height: (graf.SADmax-graf.SADmin)/Hdata*Hscr,
						fillStyle: '#e0f2f1'
					});
				}
				
				//полоса нижнего давления
				if(graf.maxval<graf.DADmin) {//полоса не пересекается с графиком
					if(graf.maxval < graf.DADmin) {//полоса выше
						graf.area2 = surface.add({
							type: 'rect',
							x: 0,
							y: 0,
							width: 2000,
							height:  graf.innerPadding.top,
							fillStyle: '#e0f2f1'
						});
					} else {//полоса ниже
						graf.area2 = surface.add({
							type: 'rect',
							x: 0,
							y: Hscr-graf.innerPadding.bottom,
							width: 2000,
							height: graf.innerPadding.bottom*5,
							fillStyle: '#e0f2f1'
						});
					}
				} else {
					graf.area2 = surface.add({
						type: 'rect',
						x: 0,
						y: graf.innerPadding.top + (graf.maxval-graf.DADmax)/Hdata*Hscr,
						width: 2000,
						height: (graf.DADmax-graf.DADmin)/Hdata*Hscr,
						fillStyle: '#e0f2f1'
					});
				}
				setka.forEach(function(item){
					surface.add(item);
				});
				surface.renderFrame();
			},
			height: 220,
			animate: false,
			title: 'Артериальное давление',
			axes: [{
				name: 'y',
				hidden: true,
				type: 'numeric',
				fields: ['ratetype53value','ratetype54value','grafratetype53value','grafratetype53min','grafratetype53max','grafratetype54value','grafratetype54min','grafratetype54max'],
				position: 'left',
			}, {
				name: 'x',
				type: 'time',
				fields: 'GrafDate',
				grid: {
					lineDash: [2, 2],
					fillOpacity: 1,
					lineWidth: 1
				},
				position: 'bottom',
				dateFormat: 'd',
				title: '',
				style: {
					axisLine: false
				},
				label: {
					fontStyle: 'color: #fff;'
				},
				segmenter: {
					type: 'time',
					step: {
						unit: 'd',
						step: 1
					}
				}
			}],
			series: [
				{
					type: 'line',
					xField: 'GrafDate',
					yField: 'grafratetype53value',
					style: {
						lineWidth: 1
					},
					marker: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#fff',
						strokeStyle: '#2196F3',
					},
					highlight: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#2196F3',
						strokeStyle: '#2196F3',
					},
					colors: ['#33BBFF'], //['#2196F3'],
					tooltip: {
						trackMouse: false,
						style: 'background: #fff',
						showDelay: 0,
						dismissDelay: 0,
						hideDelay: 0,
						align: 't',
						cls: 'chart-tooltip',
						renderer: function(tooltip, rec, item) {
							var max = rec.get('grafratetype53max'),
								min = rec.get('grafratetype53min'),
								val = rec.get('grafratetype53value');
							if (!Ext6.isEmpty(min) && !Ext6.isEmpty(max) && (val < min || val > max))
								tooltip.addCls('over-limit');
							else tooltip.removeCls('over-limit');
							tooltip.setHtml('<span class="tooltip-text">'+rec.get('ratetype53value')+'</span> <div class="tooltip-arrow"></div>');
						}
					}
				}, {
					type: 'line',
					xField: 'GrafDate',
					yField: 'grafratetype54value',
					style: {
						lineWidth: 1
					},
					marker: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#fff',
						strokeStyle: '#2196F3',
					},
					highlight: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#2196F3',
						strokeStyle: '#2196F3',
						showDelay: 0,
						dismissDelay: 0,
						hideDelay: 0,
					},
					colors: ['#33BBFF'], //['#2196F3'],
					tooltip: {
						trackMouse: false,
						style: 'background: #fff',
						showDelay: 0,
						dismissDelay: 0,
						hideDelay: 0,
						align: 't',
						cls: 'chart-tooltip',
						renderer: function(tooltip, rec, item) {
							var max = rec.get('grafratetype54max'),
								min = rec.get('grafratetype54min'),
								val = rec.get('grafratetype54value');
							if (!Ext6.isEmpty(min) && !Ext6.isEmpty(max) && (val < min || val > max))
								tooltip.addCls('over-limit');
							else tooltip.removeCls('over-limit');
							tooltip.setHtml('<span class="tooltip-text">'+rec.get('ratetype54value')+'</span> <div class="tooltip-arrow"></div>');
						}
					}
				}
			],
			listeners: {
				itemmousemove: function( chart, item, event, eOpts )  {
					var datarange = chart.getSeries()[0].dataRange;
					var date1 = new Date(chart.getAxis(1).getFromDate());
					var date2 = new Date(chart.getAxis(1).getToDate());
					
					var scr = chart.getMainRect();
					var dt = item.record.get('GrafDate');
					var x = (dt.getTime()-date1.getTime())*scr[2]/(date2.getTime()-date1.getTime());
					
					var m = [me.chartAD, me.grafPulse];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						graf.dayline = surface.add({
							type: 'line',
							fromX: x,
							fromY: -1000,
							toX: x,
							toY: 1000,
							fillStyle: '#33BBFF',
							strokeStyle: '#33BBFF',
							lineWidth: 1
						});
						
						surface.renderFrame();
					});
				},
				itemmouseout: function( chart, item, event, eOpts )  {
					var m = [me.chartAD, me.grafPulse];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						surface.renderFrame();
					});
				},
				itemhighlight: function (graf, item) {
					
					if(!item) return;
					var dt = item.record.get('GrafDate');
					var dayEl = Ext6.getElementById(me.getId()+'-day-'+me.grafDay);
					if(dayEl) {
						dayEl.setAttribute('class','');
					}
					dayEl = Ext6.getElementById(me.getId()+'-day-'+dt.dateFormat('d-m'));
					if(dayEl) {
						me.grafDay = dt.dateFormat('d-m');
						dayEl.setAttribute('class','dm-selected-day');
					}
					
					var i = me.grafPulse.getStore().findBy(function(rec) {
						return (rec.get('GrafDate')==dt);
					});
					if(i>=0) {
						me.grafPulse.setHighlightItem(me.grafPulse.getSeries()[0].getItemByIndex(i));
					}
				}
			}
		});
		//TAG: Пульс / ЧСС
		//хранилище:
		me.storePulse = new Ext6.data.SimpleStore({
			autoLoad: false,
			fields: [],
			data: [],
				sorters: [{
					property: 'ObserveDate',
					direction: 'DESC'
				},{
					property: 'TimeOfDay_id',
					direction: 'DESC'
				}, {
					property: 'ChartInfo_id',
					direction: 'DESC'
				}
			],
			
			add_data: function(data) {
				if (typeof(data) != 'object') return false;
				infodata = data.info;
				var record = null;
				for (var i = 0; i < infodata.length; i++) {
					var ChartInfo_id = infodata[i].ChartInfo_id;
					var value = null;
					
					data.rates.forEach(function(rate) {
						if(rate.RateType_id == 38) {
							me.grafPulse.min = rate['ChartRate_Min'];
							me.grafPulse.max = rate['ChartRate_Max'];
						}
					});
					data.measures.forEach(function (measure) {
						if(measure.ChartInfo_id == ChartInfo_id && measure.RateType_id==38) {
							value = measure.Value;
						}
					});
					if(value) { //есть точка
						record = new Ext6.data.Record(infodata[i]);
						record.set('value', value);
						if(typeof record.get('ObserveDate') == 'string')
							record.set('ObserveDate', Date.parse(record.get('ObserveDate')));
						var time = new Date(record.get('ObserveDate').getTime());
						time.setHours( record.get('TimeOfDay_id')=="1" ? 8 : 16 );
						record.set('GrafDate', time);
						record.set('min', me.grafPulse.min);
						record.set('max', me.grafPulse.max);
						record.commit();
						this.add(record);
					}
				}
			}
		});
		//график Пульса (ЧСС):
		me.grafPulse = Ext6.create('Ext6.chart.CartesianChart', {
			itemId: 'grafPulse',
			plugins: [ new Ext6.chart.plugin.ItemEvents() ],
			engine: Ext6.draw.engine.Svg,
			store: me.storePulse,
			border: false,
			animation: false,
			hideHeader: true,
			width: 100,
			collapsible: true,
			innerPadding: {
				left: 0,
				right: 0,
				top: 10,
				bottom: 10
			},
			height: 150,
			maxHeight: 150,
			title: 'Пульс',
			makeArea: function() {
				//нарисовать прямоугольник (область нормы)
				var graf = me.grafPulse;

				var area = graf.getMainRect(); //область графика [*,*, Xmax, Ymax]
				var Hscr = area[3] - graf.innerPadding.top - graf.innerPadding.bottom;
				
				var Hdata = graf.maxval-graf.minval;

				var surface = graf.getSurface();
				
				if(me.grafPulse.area) me.grafPulse.area.remove();
				
				var setka = [];
				surface.getItems().forEach(function(item){
					setka.push(item);
				});
				surface.removeAll();
				
				if(graf.minval > graf.max || graf.maxval<graf.min) {//полоса не пересекается с графиком
					if(graf.maxval < graf.min) {//полоса выше
						me.grafPulse.area = surface.add({
							type: 'rect',
							x: 0,
							y: 0,
							width: 2000,
							height:  graf.innerPadding.top,
							fillStyle: '#e0f2f1'
						});
					} else {//полоса ниже
						me.grafPulse.area = surface.add({
							type: 'rect',
							x: 0,
							y: Hscr-graf.innerPadding.bottom,
							width: 2000,
							height: graf.innerPadding.bottom*5,
							fillStyle: '#e0f2f1'
						});
					}
				} else {//полоса в графике
						me.grafPulse.area = surface.add({
							type: 'rect',
							x: 0,
							y: graf.innerPadding.top + (graf.maxval-graf.max)/Hdata*Hscr,
							width: 2000,
							height: (graf.max-graf.min)/Hdata*Hscr,
							fillStyle: '#e0f2f1'
						});					
				}
				setka.forEach(function(item){
					surface.add(item);
				});
				surface.renderFrame();
			},
			axes: [{
				name: 'y',
				hidden: true,
				type: 'numeric',
				fields: 'value',
				position: 'left'
			}, {
				name: 'x',
				type: 'time',
				fields: 'GrafDate',
				grid: {
					lineDash: [2, 2],
					fillOpacity: 1,
					lineWidth: 1
				},
				position: 'bottom',
				dateFormat: 'd',
				title: '',
				segmenter: {
					type: 'time',
					step: {
						unit: 'd',
						step: 1
					}
				}
			}],
			series: [
				{
					type: 'line',
					xField: 'GrafDate',
					yField: 'value',
					style: {
						lineWidth: 1
					},
					marker: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#fff',
						strokeStyle: '#2196F3'
					},
					highlight: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#2196F3',
						strokeStyle: '#2196F3'
					},
					colors: ['#33BBFF'], //['#2196F3'],
					tooltip: {
						trackMouse: false,
						style: 'background: #fff',
						showDelay: 0,
						dismissDelay: 0,
						hideDelay: 0,
						cls: 'chart-tooltip',
						align: 't',
						renderer: function(tooltip, rec, item) {
							var max = rec.get('max'),
								min = rec.get('min'),
								val = rec.get('value');
							if (min && max && (val < min || val > max))
								tooltip.addCls('over-limit');
							else tooltip.removeCls('over-limit');
							tooltip.setHtml('<span class="tooltip-text">'+val+'</span><div class="tooltip-arrow"></div>');
						}
					}
				}
			],
			listeners: {
				itemmousemove: function( chart, item, event, eOpts )  {
					var datarange = chart.getSeries()[0].dataRange;
					var date1 = new Date(chart.getAxis(1).getFromDate());
					var date2 = new Date(chart.getAxis(1).getToDate());
					
					var scr = chart.getMainRect();
					var dt = item.record.get('GrafDate');
					var x = (dt.getTime()-date1.getTime())*scr[2]/(date2.getTime()-date1.getTime());
					
					var m = [me.chartAD, me.grafPulse];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						graf.dayline = surface.add({
							type: 'line',
							fromX: x,
							fromY: -1000,
							toX: x,
							toY: 1000,
							fillStyle: '#33BBFF',
							strokeStyle: '#33BBFF',
							lineWidth: 1
						});
						
						surface.renderFrame();
					});
				},
				itemmouseout: function( chart, item, event, eOpts )  {
					var m = [me.chartAD, me.grafPulse];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						surface.renderFrame();
					});
				},
				itemhighlight: function (graf, item) {
					
					if(!item) return;
					var dt = item.record.get('GrafDate');
					var dayEl = Ext6.getElementById(me.getId()+'-day-'+me.grafDay);
					if(dayEl) {
						dayEl.setAttribute('class','');
					}
					dayEl = Ext6.getElementById(me.getId()+'-day-'+dt.dateFormat('d-m'));
					if(dayEl) {
						me.grafDay = dt.dateFormat('d-m');
						dayEl.setAttribute('class','dm-selected-day');
					}
					var i = me.chartAD.getStore().findBy(function(rec) {
						return (rec.get('GrafDate')== dt);
					});
					if(i>=0) {
						me.chartAD.setHighlightItem(me.chartAD.getSeries()[1].getItemByIndex(i));
						//~ me.chartAD.setHighlightItem(me.chartAD.getSeries()[2].getItemByIndex(i));
						//~ me.chartAD.getSeries()[1].showTooltipAt(me.chartAD.getSeries()[1].getItemByIndex(i), 10, 10);
					}
				}
			}
		});
		//Хранилище температуры
		me.storeTemperature = new Ext6.data.SimpleStore({
			autoLoad: false,
			fields: [],
			data: [],
				sorters: [{
					property: 'ObserveDate',
					direction: 'DESC'
				},{
					property: 'TimeOfDay_id',
					direction: 'DESC'
				}, {
					property: 'ChartInfo_id',
					direction: 'DESC'
				}
			],
			
			add_data: function(data) {
				if (typeof(data) != 'object') return false;
				infodata = data.info;
				var record = null;
				for (var i = 0; i < infodata.length; i++) {
					var ChartInfo_id = infodata[i].ChartInfo_id;
					var value = null;
					
					data.rates.forEach(function(rate) {
						if(rate.RateType_id == 203) {
							me.grafTemperature.min = rate['ChartRate_Min'];
							me.grafTemperature.max = rate['ChartRate_Max'];
						}
					});
					data.measures.forEach(function (measure) {
						if(measure.ChartInfo_id == ChartInfo_id && measure.RateType_id==203) {
							value = measure.Value;
						}
					});
					if(value) { //есть точка
						record = new Ext6.data.Record(infodata[i]);
						record.set('value', value);
						if(typeof record.get('ObserveDate') == 'string')
							record.set('ObserveDate', Date.parse(record.get('ObserveDate')));
						var time = new Date(record.get('ObserveDate').getTime());
						time.setHours( record.get('TimeOfDay_id')=="1" ? 8 : 16 );
						record.set('GrafDate', time);
						record.set('min', me.grafTemperature.min);
						record.set('max', me.grafTemperature.max);
						record.commit();
						this.add(record);
					}
				}
			}
		});
		//график температуры:
		me.grafTemperature = Ext6.create('Ext6.chart.CartesianChart', {
			itemId: 'grafTemperature',
			plugins: [ new Ext6.chart.plugin.ItemEvents() ],
			engine: Ext6.draw.engine.Svg,
			store: me.storeTemperature,
			border: false,
			hideHeader: true,
			width: 100,
			collapsible: true,
			innerPadding: {
				left: 0,
				right: 0,
				top: 10,
				bottom: 10
			},
			height: 150,
			maxHeight: 150,
			title: 'Температура',
			makeArea: function() {
				//нарисовать прямоугольник (область нормы)
				var graf = me.grafTemperature;

				var area = graf.getMainRect(); //область графика [*,*, Xmax, Ymax]
				var Hscr = area[3] - graf.innerPadding.top - graf.innerPadding.bottom;
				
				var Hdata = graf.maxval-graf.minval;

				var surface = graf.getSurface();
				
				if(graf.area) graf.area.remove();
				
				var setka = [];
				surface.getItems().forEach(function(item){
					setka.push(item);
				});
				surface.removeAll();
				
				if(graf.minval > graf.max || graf.maxval<graf.min) {//полоса не пересекается с графиком
					if(graf.maxval < graf.min) {//полоса выше
						graf.area = surface.add({
							type: 'rect',
							x: 0,
							y: 0,
							width: 2000,
							height:  graf.innerPadding.top,
							fillStyle: '#e0f2f1'
						});
					} else {//полоса ниже
						graf.area = surface.add({
							type: 'rect',
							x: 0,
							y: Hscr-graf.innerPadding.bottom,
							width: 2000,
							height: graf.innerPadding.bottom*5,
							fillStyle: '#e0f2f1'
						});
					}
				} else {//полоса в графике
						graf.area = surface.add({
							type: 'rect',
							x: 0,
							y: graf.innerPadding.top + (graf.maxval-graf.max)/Hdata*Hscr,
							width: 2000,
							height: (graf.max-graf.min)/Hdata*Hscr,
							fillStyle: '#e0f2f1'
						});					
				}
				setka.forEach(function(item){
					surface.add(item);
				});
				
				surface.renderFrame();
			},
			axes: [{
				name: 'y',
				hidden: true,
				type: 'numeric',
				fields: 'value',
				position: 'left'
			}, {
				name: 'x',
				type: 'time',
				fields: 'GrafDate',
				grid: {
					lineDash: [2, 2],
					fillOpacity: 1,
					lineWidth: 1
				},
				position: 'bottom',
				dateFormat: 'd',
				title: '',
				segmenter: {
					type: 'time',
					step: {
						unit: 'd',
						step: 1
					}
				}
			}],
			series: [
				{
					type: 'line',
					xField: 'GrafDate',
					yField: 'value',
					style: {
						lineWidth: 1
					},
					marker: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#fff',
						strokeStyle: '#2196F3'
					},
					highlight: {
						radius: 3,
						lineWidth: 1,
						fillStyle: '#2196F3',
						strokeStyle: '#2196F3'
					},
					colors: ['#33BBFF'], //['#2196F3'],
					tooltip: {
						trackMouse: false,
						style: 'background: #fff',
						showDelay: 0,
						dismissDelay: 0,
						hideDelay: 0,
						align: 't',
						cls: 'chart-tooltip',
						renderer: function(tooltip, rec, item) {
							var max = rec.get('max'),
								min = rec.get('min'),
								val = rec.get('value');
							if (min && max && (val < min || val > max))
								tooltip.addCls('over-limit');
							else tooltip.removeCls('over-limit');
							tooltip.setHtml('<span class="tooltip-text">'+val+'</span><div class="tooltip-arrow"></div>');
						}
					}
				}
			],
			listeners: {
				itemmousemove: function( chart, item, event, eOpts )  {
					var datarange = chart.getSeries()[0].dataRange;
					var date1 = new Date(chart.getAxis(1).getFromDate());
					var date2 = new Date(chart.getAxis(1).getToDate());
					
					var scr = chart.getMainRect();
					var dt = item.record.get('GrafDate');
					var x = (dt.getTime()-date1.getTime())*scr[2]/(date2.getTime()-date1.getTime());
					
					var m = [me.grafTemperature];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						graf.dayline = surface.add({
							type: 'line',
							fromX: x,
							fromY: -1000,
							toX: x,
							toY: 1000,
							fillStyle: '#33BBFF',
							strokeStyle: '#33BBFF',
							lineWidth: 1
						});
						
						surface.renderFrame();
					});
				},
				itemmouseout: function( chart, item, event, eOpts )  {
					var m = [me.grafTemperature];
					m.forEach(function(graf) {
						var surface = graf.getSurface();
						if(graf.dayline) graf.dayline.remove();
						surface.renderFrame();
					});
				},
				itemhighlight: function (graf, item) {
					
					if(!item) return;
					var dt = item.record.get('GrafDate');
					var dayEl = Ext6.getElementById(me.getId()+'-day-'+me.grafDay);
					if(dayEl) {
						dayEl.setAttribute('class','');
					}
					dayEl = Ext6.getElementById(me.getId()+'-day-'+dt.dateFormat('d-m'));
					if(dayEl) {
						me.grafDay = dt.dateFormat('d-m');
						dayEl.setAttribute('class','dm-selected-day');
					}
				}
			}
		});
				
		me.grafPanel = new Ext6.Panel({
			border: false,
			items: [
				me.chartAD,
				me.grafPulse,
				me.grafTemperature
			]
		});
		
		me.monthPanel = new Ext6.Panel({
			border: false,
			cls: 'month-toolbar',
			layout: 'anchor',
			html: ''
		});
		
		me.cardPanel = new Ext6.Panel({
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'card',
			activeItem: 0,
			border: false,
			
			items: [{
				border: false,
				itemId: 'gridcard',
				layout: 'anchor',
				items: [
					me.grid,
					{
						layout: {
									type: 'vbox',
									align: 'center'
								},
						border: false,
						padding: 14,
						items: [
							{
								xtype: 'button',
								text: 'ПОКАЗАТЬ ЕЩЁ 7 ДНЕЙ',
								userCls: 'button-next',
								width: 310,
								padding: 4,
								hidden: true,
								itemId: 'nextMeasure',
								handler: function() {
									me.params.start += 1;
									me.load();
								}
							}
						]
					}
				]
			}, 
			{
				border: false,
				hidden: true,
				itemId: 'grafcard',
				layout: 'anchor',
				items: [
					{
						region: 'north',
						itemId: 'periodtabs',
						xtype: 'tabpanel',
						border: false,
						cls: 'chart_nav',
						activeItem: 2,
						tabBar: {
							cls: 'white-tab-bar',
							border: false,
							defaults: {
								cls: 'simple-tab'
							},
							items: [
								{ xtype: 'tbfill' },
								{
									xtype: 'button',
									text: '',
									itemId: 'doLeft',
									iconCls: 'icon-arrow-left',
									userCls: 'button-without-frame',
									handler: function() {
										me.paramsgraf.start = me.paramsgraf.start+1;
										me.loadchart();
									}
								}, {
									xtype: 'button',
									text: '',
									itemId: 'doRight',
									iconCls: 'icon-arrow-right',
									userCls: 'button-without-frame',
									handler: function() {
										me.paramsgraf.start = me.paramsgraf.start-1;
										me.loadchart();
									}
								}, {
									xtype: 'segmentedbutton',
									itemId: 'toggleGridGrafForEmk2',
									hidden: true,
									height: 20,
									userCls: 'segmentedButtonGroup segmentedButtonGroupTabGraf',
									items: [{
										text: 'Таблица',
										pressed: true,
										handler: function() {
											me.cardPanel.setActiveItem(0);
											me.queryById('toggleGridGrafForEmk1').setValue(0);
										}
									}, {
										text: 'График',
										handler: function() {
											me.cardPanel.setActiveItem(1);
											me.queryById('toggleGridGrafForEmk1').setValue(1);
											me.loadchart();
										}
									}]
								}
							]
						},
						items: [
							{
								title: 'Месяц',
								itemId: 'tabMonth',
								listeners: {
									activate: function (tab_id, flag) {
										me.paramsgraf.limit = 3;
										me.paramsgraf.start = 0;
										if(me.cardPanel.layout.getActiveItem().itemId=='grafcard')
											me.loadchart();
									}
								}
							},
							{
								title: '2 недели',
								itemId: 'tab2week',
								listeners: {
									activate: function (tab_id, flag) {
										me.paramsgraf.limit = 2;
										me.paramsgraf.start = 0;
										if(me.cardPanel.layout.getActiveItem().itemId=='grafcard')
											me.loadchart();
									}
								}
							},
							{
								title: 'Неделя',
								itemId: 'tabWeek',
								listeners: {
									activate: function (tab_id, flag) {
										me.paramsgraf.limit = 1;
										me.paramsgraf.start = 0;
										if(me.cardPanel.layout.getActiveItem().itemId=='grafcard')
											me.loadchart();
									}
								}
							},
						]
					}, 
					me.monthPanel,
					{
						xtype: 'toolbar',
						itemId: 'daystool',
						hidden: true,
						cls: 'month-toolbar',
						border: false,
						dock: 'top',
						padding: '15 15 15 15',
						items: [{
							xtype: 'label',
							itemId: 'monthlabel1',
							html: '',
						}, '->', {
							xtype: 'label',
							itemId: 'monthlabel2',
							html: '',
						}]
					},
					
					me.grafPanel
				]
			}
			]
		});
		
		Ext6.apply(this, {
			border: false,
			items: [
				me.cardPanel
			]
		});

		me.callParent(arguments);
	}
});
