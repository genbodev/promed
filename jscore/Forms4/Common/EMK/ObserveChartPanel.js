
Ext6.define('common.EMK.ObserveChartPanel', {
	requires: [
		'common.PolkaWP.RemoteMonitoring.ObserveChartMeasures'
	],
	extend: 'swPanel',
	title: 'МОНИТОРИНГ',
	scrollable: true,
	layout: 'anchor',
	userCls: 'remote-monitor-window observe-chart-panel mini-scroll',
	charts: null,
	showRates: function(data) {

		var me = this,
			isVisible = !me.ownerCt.collapsed && me.isVisible();

		if (me.charts) {
			me.charts.destroy();	
		}

		me.charts = new Ext6.create('common.PolkaWP.RemoteMonitoring.ObserveChartMeasures', {
			border: false,
			ownerWin: me
		});

		me.charts.Server_id = me.params.Server_id;
		me.charts.Evn_id = me.params.Evn_id;
		me.charts.PersonEvn_id = me.params.PersonEvn_id;
		
		me.charts.setParams({
			Person_id: me.params.Person_id,
			isEmk: me.isEmk
		});

		me.ChartsPanel.add(me.charts);
		if (isVisible) {
			me.charts.load();
		} 
		
		//me.ownerWin.queryById('ObserveChartPanel').setTitleCounter(me.blocks.length);
		//me.ownerWin.queryById('ObserveChartPanel').setDisabled(me.blocks.length==0);
	},
	setParams: function(params) {
		var me = this;
		me.params = params;
		me.loaded = false;
		me.reload();
	},
	reload: function() {
		var me = this;
		me.LoadMask.show();
		Ext6.Ajax.request({
			params: {
				Person_id: me.params.Person_id,
			},
			callback: function(options, success, response) {
				me.LoadMask.hide();
				if (success) {
					var res = Ext6.JSON.decode(response.responseText);
					if(!Ext6.isEmpty(res.Error_Msg)) {
						Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					} else {
						if (!Ext6.isEmpty(res.data)) {
							me.showRates();
						} else {
							log('no person chart rates')							
						}
					}
				}
			},
			url: '/?c=PersonDisp&m=checkPersonLabelObserveChartRates'
		});
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		me.charts.load();
	},
	
	initComponent: function() {
		var me = this;
		me.LoadMask = new Ext6.LoadMask(me, {msg: LOAD_WAIT});
		
		me.ChartsPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			autoHeight: true,
			bodyBorder: false,
			scrollable: 'y',
			layout: {
				//type: 'accordion',
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
				me.ChartsPanel
			]
		});
		
		Ext6.apply(this, {
			items: [
				me.ownPanel
			]
		});

		this.callParent(arguments);
	}
});