Ext6.define('common.EMK.SignalInfo.AnthropometricMeasures', {
	requires: [
		'Ext6.draw.engine.Svg'
	],
	extend: 'Ext6.Panel',
	cls: 'observe_chart_measures',
	region: 'center',
	layout: 'anchor',
	border: false,
	params: {},
	isEmk: false,
	setParams: function(data) {
		var me = this;
		me.Person_id = data.Person_id;
		me.Server_id = data.Server_id;
		me.Evn_id = data.Evn_id;
		me.PersonEvn_id = data.PersonEvn_id;
		me.isEmk = true;

		me.params = {
			Person_id: data.Person_id,
			Server_id: data.Server_id,
			Evn_id: data.Evn_id,
			PersonEvn_id: data.PersonEvn_id,
			isEmk: true
		};
	},
	load: function(nolimit) {
		var me = this;
		var params = me.params;
		if(!nolimit) params.limiter = 6;
		else params.limiter = null;
		
		Ext6.Ajax.request({
			url: '/?c=Person&m=getAnthropometryPanel',
			params: params,
			callback: function(options, success, response) {
				if (success) {
					var data = Ext6.decode(response.responseText);
					if(data.length == 0) {
						me.ownerWin.ChartPanel.hide();
						return;
					} else {
						me.ownerWin.ChartPanel.show();
					}
					if(!nolimit) {
						me.storeAD.removeAll();
						me.storeAD.addData(data);
					}
					me.gridADStore.removeAll();
					me.gridADStore.addData(data);
					me.queryById('allMeasures').hidden = me.gridADStore.getData().length < 6 && !nolimit || nolimit;
					me.chartAD.makeArea();
					me.grid.setStore(me.gridADStore);
					me.chartAD.setStore(me.storeAD);
					me.chartAD.redraw();
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		var personDataModel = Ext6.create('Ext.data.Model', {
			fields: [{
				name: 'Person_id',
				type: 'int'
			},{
				name: 'PersonWeight_id',
				type: 'int'
			},{
				name: 'PersonWeight_Weight',
				type: 'float'
			},{
				name: 'PersonHeight_id',
				type: 'int'
			},{
				name: 'PersonHeight_Height',
				type: 'float'
			},{
				name: 'Person_Imt',
				type: 'float'
			},{
				name: 'Person_Ppt',
				type: 'float'
			},{
				name: 'MeasureType_id',
				type: 'int'
			},{
				name: 'MeasureType_Name',
				type: 'string'
			},{
				name: 'Measure_setDate',
				type: 'date',
				dateFormat: 'd.m.Y'
			}]
		});

		me.storeAD = Ext6.create('Ext6.data.Store', {
			model: personDataModel,
			autoLoad: false,
			data: [],
			sorters: [{
				property: 'Measure_setDate',
				transform: function(val){
				    return Ext6.Date.parse(val,"d.m.Y");
				}
			}],
			addData: function (data) {
				if(typeof data != 'object') return false;
				this.setData(data);
			}
		});

		me.gridADStore = Ext6.create('Ext6.data.Store', {
			model: personDataModel,
			autoLoad: false,
			data: [],
			sorters: [{
				property: 'Measure_setDate',
				direction: 'DESC',
				transform: function(val){
					return Ext6.Date.parse(val,"d.m.Y");
				}
			}],
			addData: function (data) {
				if(typeof data != 'object') return false;
				this.setData(data);
			}
		});

		me.chartAD = Ext6.create('Ext6.chart.CartesianChart', {
			itemId: 'chartAD',
			engine: Ext6.draw.engine.Svg,
			border: true,
			animation: false,
			hideHeader: true,
			store: me.storeAD,
			innerPadding: {
				left: 50,
				right: 150,
				top: 10,
				bottom: 10
			},
			grid: true,
			margin: '10 30 5 30',
			height: 300,
			collapsible: false,
			makeArea: function() {
				//нарисовать прямоугольник (область нормы)
				var graf = me.chartAD;

				var surface = graf.getSurface();

				var setka = [];
				surface.getItems().forEach(function(item){
					setka.push(item);
				});
				surface.removeAll();

				me.chartAD.area = surface.add({
					type: 'rect',
					x: 0,
					y: 150,
					width: 1300,
					height: 50,
					fillStyle: '#e0f2f1'
				});
				setka.forEach(function(item){
					surface.add(item);
				});
				surface.renderFrame();
			},
			axes: [{
				name: 'y',
				//hidden: true,
				type: 'numeric',
				fields: ['PersonWeight_Weight'],
				position: 'left',
				grid: {
					lineDash: [2, 2],
					fillOpacity: 1,
					lineWidth: 1
				},
				minimum: 0,
				maximum: 250
			}, {
				name: 'x',
				type: 'category',
				fields: ['Measure_setDate'],
				position: 'bottom',
				grid: {
					lineDash: [2, 2],
					fillOpacity: 1,
					lineWidth: 1
				},
				style: {
					axisLine: false
				}
			}],
			series: [{
				type: 'line',
				xField: 'Measure_setDate',
				yField: 'PersonWeight_Weight',
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
				colors: ['#33BBFF'],
				tooltip: {
					trackMouse: false,
					style: 'background: #fff',
					showDelay: 0,
					dismissDelay: 0,
					hideDelay: 0,
					align: 't',
					renderer: function (tooltip, rec, item) {
						var tooltipTpl = new Ext6.Template(
							'<div><p><b>Вес: </b><span style="color: black;">{Person_Weight} кг</span></p>',
							'<p><b>Рост: </b><span style="color: black;">{Person_Height} см</span></p>',
							'<p><b>Замер: </b><span style="color: black;">{MeasureType_Name}</span></p>',
							'<p><b>ИМТ: </b><span style="color: {Person_IMT_color};">{Person_IMT}</span></p>',
							'<p><b>ППТ: </b><span style="color: black;">{Person_PPT} м<sup>2</sup></span></p></div>'
						);

						tooltip.setHtml(tooltipTpl.apply({
							Person_Height: parseFloat(rec.get('PersonHeight_Height')).toFixed(2),
							Person_Weight: parseFloat(rec.get('PersonWeight_Weight')).toFixed(2),
							MeasureType_Name: rec.get('MeasureType_Name'),
							Person_IMT: rec.get('Person_Imt'),
							Person_IMT_color: rec.get('Person_Imt') >= 18.5 && rec.get('Person_Imt') <= 24.9? 'black': 'red',
							Person_PPT: Math.sqrt(rec.get('PersonHeight_Height') * rec.get('PersonWeight_Weight')/3600).toFixed(2)
						}));
					}
				}
			}]
		});
		
		me.grafPanel = new Ext6.Panel({
			border: false,
			title: 'Вес',
			region: 'center',
			collapsible: true,
			items: [
				me.chartAD
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				width: 23,
				handler: function() {
					var data = me.params;
					data.ownerPanel = me.ownerWin;
					data.preload = false;
					data.action = 'add';
					if(me.storeAD) {
						let todayStr = Ext6.util.Format.date(new Date(), 'd.m.Y');
						if(me.storeAD.findRecord('Measure_setDate', todayStr) != null) {
							Ext6.Msg.alert('Ошибка', 'Добавление двух замеров на одну и ту же дату ограничено, возможно только редактирование');
							return;
						}
					}
					getWnd('swAnthropometricMeasuresAddWindow').show(data);
				}
			}]
		});

		me.gridcolumns = [{
			text: langs('Дата замера'), dataIndex: 'Measure_setDate', xtype: 'datecolumn', width: 105, minWidth: 105,
			format: 'd.m.Y',
			editable: false,
			renderer: function (value, metaData, record) {
				return value;
			}
		}, {
			header: langs('Идентификатор типа измерения'), dataIndex: 'MeasureType_id', itemId: 'MeasureType_id', type: 'int', hidden: true
		}, {
			header: langs('Наименование типа измерения'), dataIndex: 'MeasureType_Name', itemId: 'MeasureType_Name', type: 'string', hidden: true
		}, {
			header: langs('Вес, кг'), dataIndex: 'PersonWeight_Weight', type: 'float', width: 70, minWidth: 70, hidden: false,
			sortable: false,
			renderer: function (value, metaData, record) {
				return value.toFixed(2);
			}
		}, {
			header: langs('Идентификатор измерения веса'), dataIndex: 'PersonWeight_id', itemId: 'PersonWeight_id', type: 'int', hidden: true
		}, {
			header: langs('Рост, см'), dataIndex: 'PersonHeight_Height', type: 'float', width: 78, minWidth: 78, hidden: false,
			sortable: false,
			renderer: function (value, metaData, record) {
				return parseFloat(value).toFixed(2);
			}
		}, {
			header: langs('Идентификатор измерения роста'), dataIndex: 'PersonHeight_id', itemId: 'PersonHeight_id', type: 'int', hidden: true
		}, {
			header: langs('ИМТ'), dataIndex: 'Person_Imt', type: 'string', width: 65, minWidth: 65, hidden: false,
			sortable: false,
			renderer: function (value, metaData, record) {
				var personIMT = parseFloat(value);
				var imtQtipText = 'Норма';
				if(personIMT > 24.9 && personIMT <= 29.9) imtQtipText = 'Предожирение';
				else if(personIMT > 29.9 && personIMT <= 34.9) imtQtipText = 'Ожирение 1 степени';
				else if(personIMT > 34.9 && personIMT <= 39.9) imtQtipText = 'Ожирение 2 степени';
				else if(personIMT > 39.9) imtQtipText = 'Ожирение 3 степени';
				else if (personIMT < 18.5) imtQtipText = 'Ниже нормы';

				var imtTemplate = new Ext6.Template(
					'<span style="color: {Person_IMT_color};" data-qtip="{Person_IMT_qtip}">{Person_IMT}</span>'
				);
				
				return imtTemplate.apply({
					Person_IMT: personIMT,
					Person_IMT_color: personIMT >= 18.5 && personIMT <= 24.9? 'black': 'red',
					Person_IMT_qtip: imtQtipText
				});
			}
		}, {
			header: 'ППТ, м<sup>2</sup>', dataIndex: 'Person_Ppt', type: 'string', hidden: false, flex: 1,
			sortable: false,
			tdCls: 'dmGridTd',
			renderer: function (value, metaData, record) {
				value = Math.sqrt(record.get('PersonHeight_Height') * record.get('PersonWeight_Weight')/3600).toFixed(2);
				var s="<span class='dm-row-toolblock' style='float: right;'>";
				s+="<span class='dm-row-tool dm-row-tool-edit' data-qtip='Редактировать' onclick='Ext6.getCmp(\"" + me.grid.id + "\").doEdit("+record.get('id')+")'></span>";
				s+="<span class='dm-row-tool dm-row-tool-del' data-qtip='Удалить' onclick='Ext6.getCmp(\"" + me.grid.id + "\").doDelete("+record.get('id')+")'></span>";
				s+="</span>";

				return "<span>"+ value +"</span>" + s;
			}
		}
		];

		me.grid = new Ext6.grid.Panel({
			xtype: 'row',
			border: true,
			margin: '20 30 5 30',
			autoScroll: true,
			editIndex: -1,
			doDelete: function(rec_id) {
				var grid = this;
				var rec = grid.getStore().findRecord('id', rec_id);
				var params = {
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					PersonWeight_id: rec.get('PersonWeight_id'),
					PersonHeight_id: rec.get('PersonHeight_id'),
					MeasureType_id: rec.get('MeasureType_id'),
					Measure_setDate: rec.get('Measure_setDate'),
					PersonHeight_Height: rec.get('PersonHeight_Height'),
					PersonWeight_Weight: rec.get('PersonWeight_Weight'),
					Okei_id: 37//кг
				};
				Ext6.Ajax.request({
					url: '/?c=Person&m=removeAnthropometryData',
					params: params,
					callback: function(options, success, response) {
						if(success) {
                            var todayStr = Ext6.util.Format.date(new Date(), 'd.m.Y'),
								record = me.storeAD.findRecord('Measure_setDate', todayStr);
                            if(record != null) {
								var itemIndex = me.storeAD.data.indexOf(record);
								me.storeAD.removeAt(itemIndex);
                            }
							me.ownerWin.load();
						}
					}
				});
			},
			doEdit: function(rec_id) {
				var grid = this;
				var rec = grid.getStore().findRecord('id', rec_id);
				var params = {
					Person_id: me.Person_id,
					action: 'edit',
					PersonWeight_id: rec.get('PersonWeight_id'),
					PersonWeight_Weight: rec.get('PersonWeight_Weight'),
					PersonHeight_id: rec.get('PersonHeight_id'),
					PersonHeight_Height: rec.get('PersonHeight_Height'),
					MeasureType_id: rec.get('MeasureType_id'),
					Measure_setDate: rec.get('Measure_setDate'),
					ownerPanel: me.ownerWin,
					Server_id: me.Server_id,
					preload: false
				};
				getWnd('swAnthropometricMeasuresAddWindow').show(params);
			},
			//TAG: выключатель меток для коммита
			viewConfig:{
				markDirty:false
			},
			cls:'anthropometricMeasures_grid',
			store: me.gridADStore,
			columns: me.gridcolumns
		});
		
		me.gridPanel = new Ext6.Panel({
			region: 'center',
			border: false,
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
							text: 'Показать предыдущие замеры',
							userCls: 'button-next',
							width: 310,
							padding: 4,
							itemId: 'allMeasures',
							handler: function() {
								me.load(true);
							}
						}
					]
				}
			]
		});
		
		Ext6.apply(this, {
			items: [
				me.grafPanel,
				me.gridPanel
			],
			border: false
		});

		me.callParent(arguments);
	}
});
