/**
 * Панель документов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnXmlPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	title: 'ДОКУМЕНТЫ',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		this.addEvnXml('add');
	},
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.userMedStaffFact = params.userMedStaffFact;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		this.EvnXmlGrid.getStore().load({
			params: {
				Evn_id: me.Evn_id,
				XmlType_id: sw.Promed.EvnXml.MULTIPLE_DOCUMENT_TYPE_ID
			}
		});
	},
	deleteEvnXml: function() {
		var me = this;

		var EvnXml_id = me.EvnXmlGrid.recordMenu.EvnXml_id;
		if (EvnXml_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '/?c=EvnXml&m=destroy',
						params: {
							EvnXml_id: EvnXml_id
						},
						callback: function () {
							me.unmask();
							me.EvnXmlGrid.getStore().reload();
						}
					})
				}
			});
		}
	},
	addEvnXml: function() {
		var me = this;
		var XmlType_id = sw.Promed.EvnXml.MULTIPLE_DOCUMENT_TYPE_ID;

		var onSelect = function(XmlTemplate_id) {
			var params = {
				Evn_id: me.Evn_id,
				XmlTemplate_id: XmlTemplate_id,
				XmlType_id: XmlType_id
			};

			me.mask('Создание нового документа...');

			Ext6.Ajax.request({
				url: '/?c=EvnXml6E&m=createEmptyEvnXml',
				params: params,
				callback: function(options, success, response) {
					me.unmask();
					var responseObj = Ext6.decode(response.responseText);

					if (responseObj.success) {
						me.EvnXmlGrid.store.load({params: params});
					}
				}
			});
		};

		getWnd('swXmlTemplateEditorWindow').show({
			allowedEvnClassList: me.allowedEvnClassList,
			allowedXmlTypeEvnClassLink: me.allowedXmlTypeEvnClassLink,
			XmlType_id: XmlType_id,
			EvnClass_id: me.EvnClass_id,
			LpuSection_id: me.userMedStaffFact.LpuSection_id,
			MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
			MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
			MedService_id: me.userMedStaffFact.MedService_id,
			onSelect: onSelect
		});
	},
	openEvnXmlEditWindow: function(action) {
		var me = this;
		var XmlType_id = sw.Promed.EvnXml.MULTIPLE_DOCUMENT_TYPE_ID;

		var EvnXml_id = me.EvnXmlGrid.recordMenu.EvnXml_id;
		if (!EvnXml_id) {
			return false;
		}

		var record = me.EvnXmlGrid.store.findRecord('EvnXml_id', EvnXml_id);

		getWnd('swEvnXmlEditorWindow').show({
			EvnXml_id: EvnXml_id,
			allowedEvnClassList: me.allowedEvnClassList,
			allowedXmlTypeEvnClassLink: me.allowedXmlTypeEvnClassLink,
			XmlTemplate_id: record.get('XmlTemplate_id'),
			XmlType_id: XmlType_id,
			EvnClass_id: me.EvnClass_id,
			Evn_id: me.Evn_id,
			LpuSection_id: me.userMedStaffFact.LpuSection_id,
			MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
			MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
			MedService_id: me.userMedStaffFact.MedService_id
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnXmlGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openEvnXmlEditWindow('edit');
					}
				}, {
					text: 'Удалить запись',
					handler: function() {
						me.deleteEvnXml();
					}
				}]
			}),
			showRecordMenu: function(el, EvnXml_id) {
				this.recordMenu.EvnXml_id = EvnXml_id;
				this.recordMenu.showBy(el);
			},
			openEvnXmlEditWindow: function(el, EvnXml_id) {
				this.recordMenu.EvnXml_id = EvnXml_id;
				me.openEvnXmlEditWindow('edit');
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'EvnXml_Data',
				renderer: function (value, metaData, record) {
					return '<span class="documentInfoIcon"></span>' + "<a href='#' onclick='Ext6.getCmp(\"" + me.EvnXmlGrid.id + "\").openEvnXmlEditWindow(this, " + record.get('EvnXml_id') + ");'>" + record.get('EvnXml_Name') + "</a> Дата: " + record.get('EvnXml_Date') + " Автор: " + record.get('pmUser_Name');
				}
			}, {
				width: 60,
				dataIndex: 'EvnXml_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: 'EvnXml',
						EMDRegistry_ObjectID: '{record.EvnXml_id}',
						IsSigned: '{record.EvnXml_IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			}, {
				width: 40,
				dataIndex: 'EvnXml_Action',
				tdCls: 'vertical-middle',
				renderer: function (value, metaData, record) {
					if (me.accessType == 'edit') {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnXmlGrid.id + "\").showRecordMenu(this, " + record.get('EvnXml_id') + ");'></div>";
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnXml_id', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (me.accessType == 'edit') {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'EvnXml_Name', type: 'string' },
					{ name: 'EvnXml_Date', type: 'string' },
					{ name: 'pmUser_Name', type: 'string' },
					{ name: 'EvnXml_IsSigned', type: 'int' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnXml&m=loadEvnXmlPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnXml_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnXmlGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.addEvnXml('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});