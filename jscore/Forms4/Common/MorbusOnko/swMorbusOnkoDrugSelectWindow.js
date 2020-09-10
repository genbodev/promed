Ext6.define('common.MorbusOnko.swMorbusOnkoDrugSelectWindow', {
	/* свойства */
	alias: 'widget.swMorbusOnkoDrugSelectWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoDrugSelectsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: langs('Добавление препаратов из специфики'),
	width: 700,

	doSelect: function() {
		var win = this;
		var grid = this.GridPanel;
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

		win.mask(LOAD_WAIT_SAVE);

		Ext6.Ajax.request({
			url: '/?c=MorbusOnkoDrug&m=setEvn',
			params: params,
			callback: function(options, success, response) {
				win.unmask();
				var responseObj = Ext.util.JSON.decode(response.responseText);
				if (responseObj.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		this.callParent(arguments);
		
		this.MorbusOnko_id = arguments[0].MorbusOnko_id;
		this.Evn_id = arguments[0].Evn_id;
		this.callback = Ext.emptyFn;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.GridPanel.getStore().load({
			params: {MorbusOnko_id: this.MorbusOnko_id}
		});
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		win.GridPanel = Ext6.create('Ext6.grid.Panel', {
			height: 400,
			store: new Ext6.data.Store({
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data.MorbusOnkoDrug_id == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				fields: [
					'MorbusOnkoDrug_id',
					'check',
					'MorbusOnkoDrug_begDate',
					'MorbusOnkoDrug_endDate',
					'Prep_Name',
					'OnkoDrug_Name'
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					reader: {
						type: 'json',
						rootProperty: ''
					},
					url: '/?c=MorbusOnkoDrug&m=loadSelectionList',
				}
			}),
			columns: [
				{dataIndex: 'MorbusOnkoDrug_id', tdCls: 'nameTdCls', type: 'int', header: 'ID', hidden: true},
				{dataIndex: 'check', header: '', width: 30, xtype: 'checkcolumn', listeners: {
					'checkchange': function (column, rowIndex, checked, rec, e, eOpts) {
						rec.commit();
					}
				}},
				{dataIndex: 'MorbusOnkoDrug_begDate', header: langs('Дата начала'), tdCls: 'nameTdCls', type: 'date', width: 120},
				{dataIndex: 'MorbusOnkoDrug_endDate', header: langs('Дата окончания'), tdCls: 'nameTdCls', type: 'date', width: 120},
				{dataIndex: 'Prep_Name', header: langs('Препарат'), tdCls: 'nameTdCls', type: 'string', width: 180},
				{dataIndex: 'OnkoDrug_Name', header: langs('Медикамент'), tdCls: 'nameTdCls', type: 'string', flex: 1}
			],
			listeners: {
				
			}
		});

        Ext6.apply(win, {
			items: [
				win.GridPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				text: 'ДОБАВИТЬ',
				handler:function () {
					win.doSelect();
				}
			}]
		});

		this.callParent(arguments);
    }
});