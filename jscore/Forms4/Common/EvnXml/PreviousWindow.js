Ext6.define('common.EvnXml.PreviousWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEvnXmlPreviousWindow',
	renderTo: Ext6.getBody(),
	cls: 'arm-window-new arm-window-new-without-padding',
	title: 'Предыдущий документ',
	maximized: false,
	width: 960,
	height: 600,
	modal: true,

	doFilter: function() {
		var me = this;
		var base_form = this.filterPanel.getForm();
		
		base_form.findField('EvnXml_insDT').checkManual();
		
		if (!base_form.findField('EvnXml_insDT').getDates().length) return false;
		
		var params = {
			Evn_id: me.Evn_id,
			EvnXml_insDT: base_form.findField('EvnXml_insDT').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue(),
		};
		
		me.templateGrid.getStore().load({
			params: params
		});
	},

	doReset: function() {
		var me = this;
		me.templateGrid.getStore().load({
			params: {
				'Evn_id': me.Evn_id
			}
		});
	},

	loadEvnXml: function(EvnXml_id) {
		var me = this;
		
		me.mask(LOADING_MSG);
		Ext6.Ajax.request({
			url: '/?c=EvnXml&m=doLoadData',
			params: {
				EvnXml_id: EvnXml_id
			},
			success: function(response) {
				me.unmask();
				
				var response_obj = Ext6.decode(response.responseText);
				if ( response_obj.success ) {
					var tpl = new Ext6.XTemplate(response_obj.html);
					tpl.overwrite(me.htmlPanel.body, {});
				}
			}
		});
	},

	save: function() {
		var me = this;
		
		if (!me.EvnXml_id) return false;
		
		me.mask(LOAD_WAIT_SAVE);
		Ext6.Ajax.request({
			url: '/?c=EvnXml6E&m=copyEvnXml',
			params: {
				Evn_id: me.Evn_id,
				XmlType_id: me.XmlType_id,
				XmlTemplate_id: me.XmlTemplate_id,
				EvnXml_id: me.EvnXml_id
			},
			success: function(response) {
				me.unmask();
				
				var response_obj = Ext6.decode(response.responseText);
				if ( response_obj.success ) {
					me.callback();
					me.hide();
				}
			}
		});
		
	},

	show: function() {
		var me = this;
		var params = arguments[0];

		me.callParent(arguments);
		
		if (!params.Evn_id || !params.Person_id || !params.XmlType_id) {
			me.hide();
			return false;
		}
		
		var tpl = new Ext6.XTemplate('');
		tpl.overwrite(me.htmlPanel.body, {});
		
		me.callback = params.callback || Ext6.emptyFn;
		me.Evn_id = params.Evn_id;
		me.Person_id = params.Person_id;
		me.XmlType_id = params.XmlType_id;
		me.XmlTemplate_id = null;
		me.EvnXml_id = null;
		
		me.queryById(me.id + 'filterFieldset').collapse();
		me.filterPanel.getForm().reset();
	},

	initComponent: function() {
		var me = this;
		
		var textRenderer = function(value, meta, record) {
			if (Ext6.isEmpty(value)) {
				return '';
			}
			
			if (record.get('Diag_Code')) {
				meta.tdAttr = 'data-qtip="Диагноз: <b>' + record.get('Diag_Code') + '</b> ' + record.get('Diag_Name') + '"';
			}
			
			var text = '<p><b>' + record.get('EvnXml_insDate').format('j.m.y ') + record.get('EvnXml_insTime') + '</b></p>';
				text += !!record.get('Diag_Code') ? ('Диагноз: <b>' + record.get('Diag_Code') + '</b> ' + record.get('Diag_Name')) + '<br>' : '';
				text += !!record.get('LpuSectionProfile_Name') ? ('Профиль: ' + record.get('LpuSectionProfile_Name')) : '';

			return text;
		};
		
		me.templateGrid = Ext6.create('Ext6.grid.Panel', {
			region: 'center',
			border: false,
			userCls: 'template-search-grid',
			store: {
				fields: [
					{name: 'EvnXml_id', type: 'int'},
					{name: 'EvnXml_Name', type: 'string'},
					{name: 'XmlTemplate_id', type: 'int'},
					{name: 'EvnXml_insDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'EvnXml_insTime', type: 'string'},
					{name: 'Diag_Code', type: 'string'},
					{name: 'Diag_Name', type: 'string'},
					{name: 'LpuSectionProfile_Name', type: 'string'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=EvnXml6E&m=loadEvnXmlList',
					reader: {type: 'json'}
				},
				sorters: {
					property: 'EvnXml_id',
					direction: 'DESC'
				}
			},
			columns: [
				{dataIndex: 'EvnXml_Name', flex: 1, renderer: textRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						me.templateGrid.setSelection(record);
						if (record.get('EvnXml_id')) {
							me.XmlTemplate_id = record.get('XmlTemplate_id');
							me.EvnXml_id = record.get('EvnXml_id');
							me.loadEvnXml(record.get('EvnXml_id'));
						}
					}
				}
			},
			listeners: {
				itemdblclick: function (cmp, record) {
					me.templateGrid.setSelection(record);
					if (record.get('EvnXml_id')) {
						me.XmlTemplate_id = record.get('XmlTemplate_id');
						me.EvnXml_id = record.get('EvnXml_id');
						me.loadEvnXml(record.get('EvnXml_id'));
						me.save();
					}
				}
			}
		});

		me.htmlPanel = new Ext6.Panel({
			scrollable: true,
			border: false,
			bodyStyle: 'padding: 10px 15px',
			html: ''
		});

		me.editorContainerPanel = new Ext6.Panel({
			region: 'center',
			layout: 'card',
			cls: 'rightEmkPanel',
			items: [
				me.htmlPanel
			]
		});
		
		me.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			region: 'north',
			border: false,
			margin: 0,
			bodyStyle: {
				backgroundColor: '#f5f5f5;'
			},
			items: [{
				id: me.id + 'filterFieldset',
				xtype: 'fieldset',
				margin: '0 10',
				padding: '0 0 20',
				border: false,
				title: 'Фильтры',
				cls: 'fieldset-default',
				style: {
					borderLeft: 'none',
					borderRight: 'none'
				},
				listeners: {
					collapse: function() {
						me.doReset();
					},
					expand: function() {
						me.doFilter();
					}
				},
				collapsible: true,
				defaults: {
					padding: '0 0 0 10',
					margin: 0,
					labelAlign: 'top',
					listeners: {
						change: function (c, val) {
							me.doFilter();
						}
					}
				},
				items: [{
					xtype: 'swDateRangeField',
					width: 250,
					fieldLabel: 'Период',
					name: 'EvnXml_insDT',
					preventFutureDates: true,
					value: new Date(),
					maxDate: new Date(),
					minDate: new Date().add(Date.MONTH, -6)
				}, {
					xtype: 'swDiagCombo',
					width: 250,
					fieldLabel: 'Диагноз',
					name: 'Diag_id'
				}]
			}]
		});

		me.leftPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'west',
			layout: 'border',
			width: 300,
			items: [
				me.filterPanel,
				me.templateGrid
			]
		});

		Ext6.apply(me, {
			layout: 'border',
			border: false,
			style: 'padding: 0 !important;',
			items: [
				me.leftPanel,
				me.editorContainerPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					me.hide();
				}
			}, {
				xtype: 'SubmitButton',
				text: 'Выбрать',
				handler:function () {
					me.save();
				}
			}]
		});

		me.callParent(arguments);
	}
});