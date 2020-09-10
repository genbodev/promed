Ext6.define('common.EMK.SignalInfo.PersonAnthropometricPanel', {
	requires: [
		'common.EMK.SignalInfo.AnthropometricMeasures'
	],
	extend: 'swPanel',
	title: '',
	scrollable: true,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	collapsed: true,
	layout: 'anchor',
	userCls: 'remote-monitor-window observe-chart-panel mini-scroll',
	loaded: false,
	onBtnAddClick: function()
    {
        this.openPersonAnthropometricEditWindow();
    },
    openPersonAnthropometricEditWindow: function () {
        var me = this;
	    var data = me.params;
        data.ownerPanel = me;
        data.preload = false;
        data.action = 'add';
        if(me.ChartPanel && me.ChartPanel.chart && me.ChartPanel.chart.storeAD) {
            let todayStr = Ext6.util.Format.date(new Date(), 'd.m.Y');
            if(me.ChartPanel.chart.storeAD.findRecord('Measure_setDate', todayStr) != null) {
                Ext6.Msg.alert('Ошибка', 'Добавление двух замеров на одну и ту же дату ограничено, возможно только редактирование');
                return;
            }
        }
        getWnd('swAnthropometricMeasuresAddWindow').show(data);
    },
	createChart: function() {
		var me = this;

		if(me.ChartPanel.chart) {
			me.ChartPanel.chart.destroy();
		}

		var chart = Ext6.create('common.EMK.SignalInfo.AnthropometricMeasures', {
			border: false,
			ownerWin: me
		});

		chart.setParams({
			Person_id: me.params.Person_id,
			Server_id: me.params.Server_id,
			Evn_id: me.params.Evn_id,
			PersonEvn_id: me.params.PersonEvn_id,
			isEmk: true
		});

		me.ChartPanel.add(chart);
		me.ChartPanel.chart = chart;
		chart.load();
	},
	calcIMTAndPPT: function(personHeight, personWeight) {
		let ppt = Math.sqrt(personHeight * personWeight/3600).toFixed(2);
		let imt = (personWeight / Math.pow(0.01*personHeight, 2)).toFixed(2);
		return {
			Person_IMT: imt,
			Person_PPT: ppt
		};
	},
	setParams: function(params) {
		var me = this;
		me.params = params;
		var preloadData = me.params;
		preloadData.preload = true;
		getWnd('swAnthropometricMeasuresAddWindow').show(preloadData);
		me.createChart();
	},
	load: function() {
		var me = this;
		Ext6.Ajax.request({
			params: {
				Person_id: me.params.Person_id
			},
			url: '/?c=PersonHeight&m=loadLastPersonHeightMeasure',
			callback: function (rq, success, resp) {
				if(!success) me.setTitle('АНТРОПОМЕТРИЧЕСКИЕ ДАННЫЕ');
				if(success) {
					var response_obj = Ext6.decode(resp.responseText);
					var personLastHMeasure = response_obj[0];
					if(!personLastHMeasure) {
						me.setTitle('АНТРОПОМЕТРИЧЕСКИЕ ДАННЫЕ');
					} else {
						Ext6.Ajax.request({
							params: {
								Person_id: me.params.Person_id
							},
							url: '/?c=PersonWeight&m=loadLastPersonWeightMeasure',
							callback: function (rq, success, resp) {
								if (success) {
									var response_obj = Ext6.decode(resp.responseText);
									var personLastWMeasure = response_obj[0];
									var personIMTPPT = me.calcIMTAndPPT(personLastHMeasure.PersonHeight_Height, personLastWMeasure.PersonWeight_Weight);

									var imtQtipText = 'Норма';

									if (personIMTPPT.Person_IMT > 24.9 && personIMTPPT.Person_IMT <= 29.9) imtQtipText = 'Предожирение';
									else if (personIMTPPT.Person_IMT > 29.9 && personIMTPPT.Person_IMT <= 34.9) imtQtipText = 'Ожирение 1 степени';
									else if (personIMTPPT.Person_IMT > 34.9 && personIMTPPT.Person_IMT <= 39.9) imtQtipText = 'Ожирение 2 степени';
									else if (personIMTPPT.Person_IMT > 39.9) imtQtipText = 'Ожирение 3 степени';
									else if (personIMTPPT.Person_IMT < 18.5) imtQtipText = 'Ниже нормы';
									
									var titleTpl = new Ext6.Template(
										'<div>АНТРОПОМЕТРИЧЕСКИЕ ДАННЫЕ &nbsp;&nbsp;&nbsp;&nbsp; ',
										' <b>Рост: </b><span style="color: black;">{Person_Height} см</span>',
										' <b>Вес: </b><span style="color: black;">{Person_Weight} кг</span>',
										' <b>ИМТ: </b><span style="color: {Person_IMT_color};" data-qtip="{Person_IMT_qtip}">{Person_IMT}</span>',
										' <b>ППТ: </b><span style="color: black;">{Person_PPT} м<sup>2</sup></span></div>'
									);

									me.setTitle(titleTpl.apply({
										Person_Height: parseFloat(personLastHMeasure.PersonHeight_Height).toFixed(1),
										Person_Weight: personLastWMeasure.PersonWeight_Weight,
										Person_IMT_color: personIMTPPT.Person_IMT >= 18.5 && personIMTPPT.Person_IMT <= 24.9 ? 'black' : 'red',
										Person_IMT: personIMTPPT.Person_IMT,
										Person_PPT: personIMTPPT.Person_PPT,
										Person_IMT_qtip: imtQtipText
									}));
								}
							}
						});
					}
				}
			}
		});
		if(me.ChartPanel.chart) me.ChartPanel.chart.load();
		else me.createChart();
	},

	initComponent: function() {
		var me = this;

		me.ChartPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			autoHeight: true,
			bodyBorder: false,
			scrollable: 'y',
			layout: {
				titleCollapse: false,
				animate: true,
				multi: true,
				activeOnTop: false
			},
			items: []
		});

		me.ownPanel = new Ext6.Panel({
			border: false,
			region: 'center',
			items: [
				me.ChartPanel
			]
		});

		Ext6.apply(this, {
			items: [
				me.ownPanel
			],
			tools: [{
                type: 'plusmenu',
                tooltip: 'Добавить',
                width: 23,
                handler: function() {
                	me.openPersonAnthropometricEditWindow();
                }
            }]
		});

		this.callParent(arguments);
	}
});