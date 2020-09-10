Ext6.define('common.EvnXml.ItemsPanel', {
	requires: [
		'common.XmlTemplate.DropdownSelectPanel',
		'common.EvnXml.EditorPanel'
	],
	extend: 'swPanel',
	alias: 'widget.evnxmlitemspanel',
	title: 'ДОКУМЕНТЫ',
	cls: 'evn-xml-panel',
	layout: 'fit',
	collapsed: true,
	autoScroll: true,
	isLoaded: false,
	allowT9: false, //опция на уровне кода - в каких разделах использовать Т9
	collapseOnOnlyTitle: true,
	maxCount: null,

	afterExpand: function() {
		var me = this;
		me.callParent(arguments);

		me.editorPanel.setVisible(me.titleCounter > 0);
		me.editorPanel.refreshObserveScrollForToolbar();
		if (!me.isLoaded) me.loadTabList();
	},

	setAccessType: function() {
		var me = this;
		me.callParent(arguments);
		me.refreshTools();
		me.editorPanel.setReadOnly(me.accessType != 'edit');
	},

	copyEvnXml: function() {
		var me = this;
		var dt = new Date();
		var params = me.params;
		
		params.callback = function(data) {
			me.loadTabList();
		};
		
		getWnd('swEvnXmlPreviousWindow').show(params);
	},

	addEvnXml: function() {
		var me = this;
		var dt = new Date();

		me.store.add({
			EvnXml_id: null,
			EvnXml_Name: 'Новый осмотр',
			EvnXml_Date: Ext6.Date.format(dt, 'd.m.Y'),
			EvnXml_Time: Ext6.Date.format(dt, 'H:i'),
			XmlTemplate_id: null
		});
		me.onTabListLoad();
		me.editorPanel.setEditing(true);
		me.editorPanel.onContentChange();
		me.editorPanel.saveDocument();
	},

	setParams: function(params) {
		var me = this;
		me.isLoaded = false;
		me.hasLast = true;

		me.params = {
			XmlType_id: params.XmlType_id ? params.XmlType_id : 3,
			Person_id: params.Person_id,
			Evn_id: params.Evn_id,
			EvnClass_id: params.EvnClass_id,
			LpuSection_id: params.LpuSection_id,
			MedPersonal_id: params.MedPersonal_id,
			MedStaffFact_id: params.MedStaffFact_id,
			MedService_id: params.MedService_id
		};

		me.editorPanel.reset();
		me.editorPanel.setParams(me.params);
		me.templateSelectPanel.setParams(me.params);
	},

	loadTabList: function() {
		var me = this;
		me.mask('Загрузка...');
		me.store.load({
			params: me.params,
			callback: function() {
				me.unmask();
			}
		});
		
		Ext6.Ajax.request({
			url: '/?c=EvnXml6E&m=loadEvnXmlList',
			params: {
				Evn_id: me.params.Evn_id,
				cnt: 1
			},
			success: function(response) {				
				var response_obj = Ext6.decode(response.responseText);
				me.hasLast = ( response_obj[0] && response_obj[0].cnt && response_obj[0].cnt > 0 );
			}
		});
	},

	onTabListLoad: function() {
		var me = this;
		me.isLoaded = true;

		me.setTitleCounter(me.store.getCount());
		me.tabPanel.removeAll();

		me.store.each(function(item) {
			me.tabPanel.add({
				title: me.tabTitleTpl.apply(item.data),
				EvnXml_id: item.get('EvnXml_id'),
				XmlTemplate_id: item.get('XmlTemplate_id'),
				border: false
			});
		});

		if (me.store.getCount() > 0) {
			me.tabPanel.setActiveTab(0);
		}
	},

	onTabChange: function(tab) {
		var me = this;

		me.refreshTools();
		me.editorPanel.reset();
		me.editorPanel.setParams(me.params);
		me.store.clearData();

		if (tab && tab.EvnXml_id) {
			me.templateSelectPanel.setParams(Ext6.apply(me.params, {
				EvnXml_id: tab.EvnXml_id
			}));
			me.editorPanel.setParams({
				EvnXml_id: tab.EvnXml_id,
				XmlTemplate_id: tab.XmlTemplate_id
			});
			me.editorPanel.load({
				resetState: true
			});
		}
		me.refreshTools();
	},

	refreshTools: function() {
		var me = this;
		var addTemplateTool = me.queryById(me.getId()+'-template-add');
		var selectTemplateTool = me.queryById(me.getId()+'-template-select');
		var templateFromLastTool = me.queryById(me.getId()+'-template-from-last');
		
		if (me.accessType == 'edit') {
			addTemplateTool.setDisabled(me.titleCounter >= me.maxCount);
			selectTemplateTool.setDisabled(me.titleCounter >= me.maxCount && !me.tabPanel.getActiveTab());
			templateFromLastTool.setDisabled((me.titleCounter >= me.maxCount && !me.tabPanel.getActiveTab()) || !me.hasLast);
		}
	},

	setTitleCounter: function(count) {
		var me = this;
		me.callParent(arguments);
		me.refreshTools();
	},

	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'EvnXml_id', type: 'int'},
				{name: 'EvnXml_Name', type: 'string'},
				{name: 'EvnXml_Date', type: 'string'},
				{name: 'EvnXml_Time', type: 'string'},
				{name: 'pmAuth_Name', type: 'string'},
				{name: 'XmlTemplate_id', type: 'int'}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=EvnXml&m=loadEvnXmlPanel',
				reader: {type: 'json'}
			},
			sorters: [
				'EvnXml_Date',
				'EvnXml_Time'
			],
			listeners: {
				load: function() {
					me.onTabListLoad();
				}
			}
		});

		me.templateSelectPanel = Ext6.create('common.XmlTemplate.DropdownSelectPanel', {
			onSelect: function(data) {
				if (
					me.titleCounter < me.maxCount &&
					(!me.tabPanel.getActiveTab() || me.tabPanel.getActiveTab().EvnXml_id != me.editorPanel.params.EvnXml_id)
				) {
					me.addEvnXml();
				}

				if (me.tabPanel.getActiveTab()) {
					me.expand();
					me.editorPanel.setParams({XmlTemplate_id: data.XmlTemplate_id});
					me.editorPanel.load({
						resetTemplate: true
					});
				}
			}
		});

		me.tabTitleTpl = new Ext6.Template('{EvnXml_Date} <span style="color: grey">{EvnXml_Time}</span>');

		me.tabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			bodyBorder: false,
			cls: 'light-tab-panel',
			hidden: me.maxCount <= 1,
			listeners: {
				tabchange: function(panel, tab) {
					me.onTabChange(tab);
				}
			}
		});

		me.editorPanel = Ext6.create('common.EvnXml.EditorPanel', {
			autoHeight: true,
			isAutoSave: true,
			toolbarSticky: true,
			maximizedContainer: me.maximizedContainer,
			style: 'border-width: 1px 0;',
			onSaveDocument: function(response) {
				var tab = me.tabPanel.activeTab;
				if (tab && response.EvnXml_id) {
					tab.EvnXml_id = response.EvnXml_id;
					tab.XmlTemplate_id = response.XmlTemplate_id;
				}
			},
			onChangeTemplate: function(data) {
				me.editorPanel.setParams({XmlTemplate_id: data.XmlTemplate_id});
				me.editorPanel.load({resetTemplate: true});
			},
			allowT9: me.allowT9
		});

		var tooltip = function(text) {
			return {text: text, align: 'tc-br?'};
		};

		Ext6.apply(me, {
			tools: [{
				id: me.getId()+'-template-select',
				type: 'template-select',
				userCls: 'sw-tool',
				tooltip: tooltip('Выбрать из шаблона'),
				minWidth: 23,
				padding: '0 0 0 18',
				margin: '0 18 0 0',
				callback: function(panel, tool, event) {
					me.templateSelectPanel.show({
						target: tool, align: 'tr-br?'
					});
				}
			}, {
				id: me.getId()+'-template-from-last',
				type: 'template-from-last',
				userCls: 'sw-tool',
				tooltip: tooltip('Создать документ на основе предыдушего'),
				padding: '0 0 0 18',
				margin: '0 18 0 0',
				callback: function(event, tool, event) {
					me.copyEvnXml();
					me.expand();
				}
			}, {
				id: me.getId()+'-template-add',
				type: 'template-add',
				userCls: 'sw-tool',
				tooltip: tooltip('Создать новый осмотр'),
				padding: '0 0 0 18',
				margin: '0 18 0 0',
				callback: function(event, tool, event) {
					me.addEvnXml();
					me.expand();
				}
			}],
			dockedItems: [
				me.tabPanel
			],
			items: [
				me.editorPanel
			]
		});

		this.callParent(arguments);
	},

	setAllowed: function(evnClassList, xmlTypeEvnClassLink)
	{
		this.templateSelectPanel.allowedEvnClassList =
			this.editorPanel.allowedEvnClassList = evnClassList;

		this.templateSelectPanel.allowedXmlTypeEvnClassLink =
			this.editorPanel.allowedXmlTypeEvnClassLink = xmlTypeEvnClassLink;
	}
});