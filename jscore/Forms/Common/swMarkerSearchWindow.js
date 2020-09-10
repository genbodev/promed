/**
* swMarkerSearchWindow - окно поиска, просмотра маркеров и связей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Salakhov R.
* @version      26.01.2012
*/

/*NO PARSE JSON*/

sw.Promed.swMarkerSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMarkerSearchWindow',
	objectSrc: '/jscore/Forms/Common/swMarkerSearchWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		},
		'beforeShow': function(win) {
			if ( !isSuperAdmin() )
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Cуперадминистратор»');
				return false;
			}
		}
	},
	title: lang['spisok_markerov'],
	draggable: true,
	id: 'swMarkerSearchWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	EvnClass_id: null,
	MarkerSynonym_id: null,
	MarkerTagType_id: null,
	doReset: function(num) {
		var base_wnd = this,
			tab_num = num ? num : base_wnd.markerTabPanel.getActiveTab().num,
			filterpanel = tab_num == 1 ? base_wnd.filterPanelMarker : base_wnd.filterPanelRelationship,
			viewframe = tab_num == 1 ? base_wnd.viewFrameMarker : base_wnd.viewFrameRelationship;
        filterpanel.getForm().reset();
        filterpanel.getForm().findField('EvnClass_id').getStore().baseParams = {
            withBase: 1
        };
		viewframe.getGrid().getStore().baseParams = {};
		viewframe.removeAll(true);
		viewframe.ViewGridPanel.getStore().removeAll();
		base_wnd.doSearch(tab_num);
	},
	doSearch: function(num) {
		var base_wnd = this,
			tab_num = num ? num : base_wnd.markerTabPanel.getActiveTab().num,
			filterpanel = tab_num == 1 ? base_wnd.filterPanelMarker : base_wnd.filterPanelRelationship,
			viewframe = tab_num == 1 ? base_wnd.viewFrameMarker : base_wnd.viewFrameRelationship,
			params = filterpanel.getForm().getValues();
		viewframe.removeAll(true);
		params.start = 0; 
		params.limit = 100;
		viewframe.loadData({globalFilters:params});
	},
	initComponent: function() {

		this.filterPanelMarker = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MarkerSearchForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			layout: 'column',
			items: [{
				layout: 'form',
				items: [{
					allowBlank: true,
					fieldLabel: lang['klass_sobyitiya'],
					name: 'EvnClass_id',
					id: 'MSW_M_EvnClass_id',
					listWidth: 400,
					enableKeyEvents: true,
					xtype: 'swevnclasscombo'
				}]
			}, {
				layout: 'form',
				items: [{
					allowBlank: true,
					fieldLabel: lang['nazvanie_markera'],
					name: 'FreeDocMarker_Name',
					id: 'MSW_M_FreeDocMarker_Name',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}, {
				layout: 'form',
				items: [{
					fieldLabel: lang['opisanie_markera'],
					name: 'FreeDocMarker_Description',
					id: 'MSW_M_FreeDocMarker_Description',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}, {
				layout: 'form',
				items: [{
					fieldLabel: lang['svyazannyiy_alias'],
					name: 'FreeDocMarker_TableAlias',
					id: 'MSW_M_FreeDocMarker_TableAlias',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		
		this.filterPanelRelationship = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'RelationshipSearchForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			layout: 'column',
			items: [{
				layout: 'form',
				items: [{
					fieldLabel: lang['klass_sobyitiya'],
					name: 'EvnClass_id',
					id: 'MSW_R_EvnClass_id',
					listWidth: 400,
					enableKeyEvents: true,
					xtype: 'swevnclasscombo'
				}]
			}, {
				layout: 'form',
				items: [{
					fieldLabel: lang['alias_svyazi'],
					name: 'FreeDocRelationship_AliasName',
					id: 'MSW_R_FreeDocRelationship_AliasName',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}, {
				layout: 'form',
				items: [{
					fieldLabel: lang['tablitsa'],
					name: 'FreeDocRelationship_AliasTable',
					id: 'MSW_R_FreeDocRelationship_AliasTable',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}, {
				layout: 'form',
				items: [{
					fieldLabel: lang['svyazannyiy_alias'],
					name: 'FreeDocRelationship_LinkedAlias',
					id: 'MSW_R_FreeDocRelationship_LinkedAlias',
					enableKeyEvents: true,
					xtype: 'textfield'
				}]
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.viewFrameMarker = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=FreeDocument&m=loadMarkerListByFilters',
			id: 'MarkerViewFrame',
			object: 'FreeDocMarker',
			editformclassname: 'swMarkerEditWindow',
			actions: [
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'FreeDocMarker_id', key: true },
				{ header: lang['data_sozdaniya'],  type: 'date', name: 'FreeDocMarker_insDT' },
				{ header: lang['data_izmeneniya'],  type: 'date', name: 'FreeDocMarker_updDT' },
				{ header: lang['klass_sobyitiya'],  type: 'string', name: 'EvnClass_Name', width: 150 },
				{ header: lang['nazvanie_markera'],  type: 'string', name: 'FreeDocMarker_Name', width: 200  },
				{ header: lang['opisanie_markera'],  type: 'string', name: 'FreeDocMarker_Description', id: 'autoexpand'  },
				{ header: lang['svyazannyiy_alias'],  type: 'string', name: 'FreeDocMarker_TableAlias' },
				{ header: lang['nalichie_zaprosa'],  type: 'string', name: 'FreeDocMarker_Query' },
				{ header: lang['tablichnyiy_marker'],  type: 'string', name: 'FreeDocMarker_IsTableValue' },
				{ header: lang['nalichie_dop_nastroek'],  type: 'string', name: 'FreeDocMarker_Options' }
			],
			toolbar: true,
			onLoadData: function() {
				if (this.ViewGridPanel.getStore().getCount()>0) {
					this.ViewGridPanel.getView().focusRow(0);
					if (this.selectionModel!='cell') {
						this.ViewGridPanel.getSelectionModel().selectFirstRow();						
					}
				}
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			},
			onEnter: function() {
				this.runAction('action_edit');
			}
		});
		
		this.viewFrameRelationship = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=FreeDocument&m=loadRelationshipListByFilters',
			id: 'RelationshipViewFrame',
			object: 'FreeDocRelationship',
			editformclassname: 'swRelationshipEditWindow',
			actions: [
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'FreeDocRelationship_id', key: true },
				{ header: lang['data_sozdaniya'],  type: 'date', name: 'FreeDocRelationship_insDT' },
				{ header: lang['data_izmeneniya'],  type: 'date', name: 'FreeDocRelationship_updDT' },
				{ header: lang['klass_sobyitiya'],  type: 'string', name: 'EvnClass_Name', width: 150 },
				{ header: lang['alias_svyazi'],  type: 'string', name: 'FreeDocRelationship_AliasName', id: 'autoexpand'  },
				{ header: lang['tablitsa'],  type: 'string', name: 'FreeDocRelationship_AliasTable'  },
				{ header: lang['svyazannyiy_alias'],  type: 'string', name: 'FreeDocRelationship_LinkedAlias' },
				{ header: lang['nalichie_zaprosa'],  type: 'string', name: 'FreeDocRelationship_AliasQuery' }
			],
			toolbar: true,
			onLoadData: function() {
				if (this.ViewGridPanel.getStore().getCount()>0) {
					this.ViewGridPanel.getView().focusRow(0);
					if (this.selectionModel!='cell') {
						this.ViewGridPanel.getSelectionModel().selectFirstRow();						
					}
				}
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			},
			onEnter: function() {
				this.runAction('action_edit');
			}
		});

		this.markerTabPanel = new Ext.TabPanel({
			id: 'markerTabPanel',
			activeTab: 0,
			autoScroll: true,
			bodyStyle:'padding:0px;',
			layoutOnTabChange: true,
			border: false,
			region: 'center',			
			items: [{
				num: 1,
				title: lang['markeryi'],
				layout: 'border',					
				labelWidth: 150,
				border: false,
				items: [
					this.filterPanelMarker,
					this.viewFrameMarker
				]
			}, {
				num: 2,
				title: lang['svyazi'],
				layout: 'border',					
				labelWidth: 150,
				border: false,
				items: [
					this.filterPanelRelationship,
					this.viewFrameRelationship
				]
			}]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch()
				},
				iconCls: 'search16',
				tabIndex: 10,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				tabIndex: 11,
				text: BTN_FRMRESET
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'GSF_Marker_Word',
				tabIndex: 15,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.markerTabPanel
			]
		});
		sw.Promed.swMarkerSearchWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swMarkerSearchWindow.superclass.show.apply(this, arguments);
		this.markerTabPanel.setActiveTab(1);
		this.doReset(2);
		this.markerTabPanel.setActiveTab(0);
		this.doReset(1);
		this.syncSize();
		this.doLayout();
	}
});