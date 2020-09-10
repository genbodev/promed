Ext6.define('common.MorbusOnko.swPersonDispSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swMorbusOnkoPersonDispSelectWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new ',
	title: 'Поиск открытых карт наблюдения пациента',
	width: 740,
	height: 300,
	modal: true,

	doSelect: function(PersonDisp_id) {
		var me = this;
		
		if (!PersonDisp_id) return false;
		
		me.body.mask(LOAD_WAIT);

        Ext6.Ajax.request({
			url: '/?c=PersonDisp&m=savePersonRegisterDispLink',
			params: {
				PersonRegister_id: me.PersonRegister_id,
				PersonDisp_id: PersonDisp_id
			},
			callback: function(options, success, response) {
				me.body.unmask();
				if (success) {
					me.callback();
					me.hide();
				}
			}
		});
	},

	show: function() {
		var me = this;

		me.callParent(arguments);
		
		if (!arguments[0].formParams) {
			me.hide();
			return false;
		}

		me.Person_id = arguments[0].formParams.Person_id || null;
		me.PersonRegister_id = arguments[0].formParams.PersonRegister_id || null;
		me.callback = arguments[0].callback || Ext6.emptyFn;
		
		if (!me.PersonRegister_id || !me.Person_id) {
			me.hide();
			return false;
		}

		me.PersonDispGridPanel.getStore().removeAll();
		me.PersonDispGridPanel.getStore().load({
			params: {
				Person_id: me.Person_id,
				PersonRegister_id: me.PersonRegister_id
			}
		});
	},

	initComponent: function() {
		var me = this;

		me.PersonDispGridPanel = Ext6.create('Ext6.grid.Panel', {
			cls: 'EmkGrid',
			border: true,
			columns: [{
				text: 'Взят',
				dataIndex: 'PersonDisp_begDate',
				width: 100,
				renderer: function (value) {return value ? value.format('d.m.Y') : '';}
			}, {
				text: 'Поставивший врач',
				dataIndex: 'MedPersonal_Fio',
				width: 160
			}, {
				text: 'Ответственный врач',
				dataIndex: 'MedPersonalH_Fio',
				width: 160
			}, {
				text: 'МО',
				dataIndex: 'Lpu_Nick',
				flex: 1
			}, {
				width: 100,
				dataIndex: 'PersonDispHist_Action',
				renderer: function (value, metaData, record) {
					return "<a style='cursor: pointer' onclick='Ext6.getCmp(\"" + me.id + "\").doSelect(" + record.get('PersonDisp_id') + ");'>Добавить</a>";
				}
			}, {
				hidden: true,
				text: 'PersonDisp_id',
				dataIndex: 'PersonDisp_id'
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonDisp_id', type: 'int' },
					{ name: 'PersonDisp_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{ name: 'MedPersonal_Fio', type: 'string' },
					{ name: 'MedPersonalH_Fio', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadMorbusOnkoSelectList',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'PersonDisp_begDate',
					direction: 'DESC'
				}
			})
		});

		Ext6.apply(me, {
			layout: 'hbox',
			defaults: {height: '100%'},
			items: [{
				layout: 'fit',
				style: 'margin: 15px;',
				cls: 'sw-panel-gray',
				flex: 1,
				items: me.PersonDispGridPanel
			}]
		});

		me.callParent(arguments);
	}
});