sw.Promed.swMorbusOnkoDrugSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMorbusOnkoDrugSelectWindow',
	layout: 'border',
	title: langs('Добавление препаратов из специфики'),
	maximizable: false,
	modal: true,
	width: 640,
	height: 420,

	doSelect: function() {
		var grid = this.GridPanel.getGrid();
		var ids = [];

		grid.getStore().each(function(record) {
			if (record.get('check')) {
				ids.push(record.get('MorbusOnkoDrug_id'));
			}
		});

		var params = {
			Evn_id: this.Evn_id,
			MorbusOnkoDrug_ids: Ext.util.JSON.encode(ids)
		};

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Добавление..." });
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=MorbusOnkoDrug&m=setEvn',
			params: params,
			callback: function(options, success, response) {
				loadMask.hide();
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (responseObj.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	toggleCheck: function(id) {
		var grid = this.GridPanel.getGrid();
		var record = grid.getStore().getById(id);
		if (!record) return;

		record.set('check', !record.get('check'));
		record.commit();
	},

	show: function() {
		sw.Promed.swMorbusOnkoDrugSelectWindow.superclass.show.apply(this, arguments);

		this.MorbusOnko_id = arguments[0].MorbusOnko_id;
		this.Evn_id = arguments[0].Evn_id;
		this.callback = Ext.emptyFn;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.GridPanel.loadData({
			globalFilters: {MorbusOnko_id: this.MorbusOnko_id}
		});
	},

	initComponent: function() {
		var wnd = this;

		this.checkRenderer = function(value, meta, record) {
			var checked = value?' checked="checked"':'';
			var onclick = 'onClick="Ext.getCmp(\''+wnd.id+'\').toggleCheck('+record.get('MorbusOnkoDrug_id')+');"';

			return '<input type="checkbox" '+checked+' '+onclick+'>';
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=MorbusOnkoDrug&m=loadSelectionList',
			border: false,
			autoLoadData: false,
			toolbar: false,
			paging: false,
			useEmptyRecord: false,
			stringfields: [
				{name: 'MorbusOnkoDrug_id', type: 'int', header: 'ID', key: true},
				{name: 'check', header: '', width: 30, renderer: this.checkRenderer},
				{name: 'MorbusOnkoDrug_begDate', header: langs('Дата начала'), type: 'date', width: 100},
				{name: 'MorbusOnkoDrug_endDate', header: langs('Дата окончания'), type: 'date', width: 100},
				{name: 'Prep_Name', header: langs('Препарат'), type: 'string', width: 180},
				{name: 'OnkoDrug_Name', header: langs('Медикамент'), type: 'string', id: 'autoexpand'}
			]
		});

		Ext.apply(this, {
			buttons: [{
				id: 'MODSW_SelectBtn',
				iconCls: 'add16',
				text: 'Добавить',
				handler: function() {
					this.doSelect();
				}.createDelegate(this)
			}, {
				text: '-'
			}, HelpButton(this), {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			items: [
				this.GridPanel
			]
		});

		sw.Promed.swMorbusOnkoDrugSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});