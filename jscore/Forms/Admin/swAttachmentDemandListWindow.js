/**
* swAttachmentDemandListWindow - окно просмотра списка заявлений на смену прикрепления к МО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Salackhov Rustam
* @version      0.001-18.03.2010
*/

sw.Promed.swAttachmentDemandListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteRecord: Ext.emptyFn,
	draggable: true,
	height: 400,
	id: 'AttachmentDemandListWindow',
	initComponent: function() {
		this.FiltersPanel = new Ext.form.FormPanel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			height: 80,
			region: 'north',
			labelWidth: 110,
			layout: 'column',
			id: 'DrugRequestFiltersPanel',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .30,
				labelWidth: 110,
				items: 
				[{
					allowBlank: true,
					xtype: 'swdemandstatecombo',
					width: 200,
					id: 'adlDemandState_id',
					fieldLabel: lang['status_zayavleniya'],
					name: 'DemandState_id',
					hiddenName: 'hDemandState_id',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						},
						'load': function(store) {
							alert('load');
						}
					}
				}, {
					xtype: 'swdatefield',
					width: 200,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					fieldLabel: lang['nachalo_perioda'],
					format: 'd.m.Y',
					id: 'adlStart_Date', 
					name: 'Start_Date',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						}
					}
				}, {
					xtype: 'swdatefield',
					width: 200,
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					fieldLabel: lang['konets_perioda'],
					format: 'd.m.Y',
					id: 'adlEnd_Date', 
					name: 'End_Date',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						}
					}	
				}]
			}, {
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .30,
				labelWidth: 110,
				items: 
				[{
					xtype: 'textfield',
					maxLength: 30,
					width: 200,
					plugins: [ new Ext.ux.translit(true, true) ],
					fieldLabel: lang['familiya'],
					id: 'adlPerson_Surname',
					name: 'Person_Surname',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						}
					}
				}, {
					xtype: 'textfield',
					maxLength: 30,
					width: 200,
					plugins: [ new Ext.ux.translit(true, true) ],
					fieldLabel: lang['imya'],
					id: 'adlPerson_Firname',
					name: 'Person_Firname',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						}
					}
				}, {
					xtype: 'textfield',
					maxLength: 30,
					width: 200,
					plugins: [ new Ext.ux.translit(true, true) ],
					fieldLabel: lang['otchestvo'],
					id: 'adlPerson_Secname',
					name: 'Person_Secname',
					listeners: {
						'keydown': function (f,e){
							if (e.getKey() == e.ENTER) Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
						}
					}
				}]
			}, {
				//кнопки
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .12,
				items:
				[{
					xtype: 'button',
					text: lang['ustanovit_filtr'],
					tabIndex: 4217,
					minWidth: 125,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'adlButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter();
					}
				},
				{
					xtype: 'button',
					text: lang['snyat_filtr'],
					tabIndex: 4218,
					minWidth: 125,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'adlButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('AttachmentDemandListWindow').loadGridWithFilter(true);
					}
				}]
			}],
			listeners: {
				'keydown': function (f,e){
					alert(1);
					if ( e.getKey() == e.ENTER ) {
						alert('press enter!');
					}
				}
			}
		});
		
		this.AttachmentDemandListGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true, handler: function() { this.openAttachmentDemandListEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openAttachmentDemandListEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() {  }.createDelegate(this) },
				{ name: 'action_delete', hidden: true, handler: function() { this.deleteAttacmentDemand(); }.createDelegate(this) },
				{ name: 'action_refresh' }
			],
			autoLoadData: false,
			dataUrl: '/?c=Demand&m=loadAttachmentDemandListGrid',
			focusOn: {
				name: 'ADLVW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'ADLVW_CancelButton',
				type: 'button'
			},
			id: 'ADLVW_AttachmentDemandListGrid',
			object: 'AttachmentDemandList',
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'AttachmentDemand_id', type: 'int', header: 'ID', key: true },
				{ name: 'DemandState_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 130 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 130 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 130 },
				{ name: 'DemandState_Name', type: 'string', header: lang['sostoyanie_zayavleniya'], width: 190 },
				{ name: 'Insert_Date', type: 'string', header: lang['data'], width: 130 },
				{ name: 'Lpu_Name', type: 'string', header: lang['mo'], id: 'autoexpand', autoExpandMin: 250 }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'ADLVW_CancelButton',
				text: '<u>З</u>акрыть'
			}],
			items: [ 
				this.FiltersPanel,
				this.AttachmentDemandListGrid
			]
		});
		sw.Promed.swAttachmentDemandListWindow.superclass.initComponent.apply(this, arguments);

		this.AttachmentDemandListGrid.addListenersFocusOnFields();
		
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('AttachmentDemandListViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.P:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.AttachmentDemandListGrid.removeAll();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: false,
	openAttachmentDemandListEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('ADLVW_AttachmentDemandListGrid').getGrid();

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			} else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		/*if ( getWnd('swAttachmentDemandListEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_zapisi_spravochnika_uet_uje_otkryito']);
			return false;
		}*/

		var params = new Object();
		if ( action == 'add' ) {
			params.onHide = function() {
				// TODO: getWnd
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id 	= person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id 	= person_data.Server_id;

					getWnd('swAttachmentDemandEditWindow').show({
						action: action,
						formParams: params
					});
				},
				searchMode: 'all'
			});
		} else {
			if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AttachmentDemand_id') ) {
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			
			params = record.data;
			
			getWnd('swAttachmentDemandEditWindow').show({
				action: action,
				formParams: params
			});
		}
	},
	deleteAttacmentDemand: function() {
		var grid = this.findById('ADLVW_AttachmentDemandListGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AttachmentDemand_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var demand_id = record.get('AttachmentDemand_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								grid.getStore().remove(record);
								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_zayavleniya_voznikli_oshibki']);
							}
						},
						params: {
							AttachmentDemand_id: demand_id
						},
						url: '/?c=Demand&m=deleteAttachmentDemand'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_zayavlenie'],
			title: lang['vopros']
		});
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear) {
			form.clearFilters();
			var state_combo = this.findById('adlDemandState_id');
			//state_combo.setValue(state_combo.getStore().getAt(0).get(state_combo.valueField));
			
			this.AttachmentDemandListGrid.removeAll();
			this.AttachmentDemandListGrid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					DemandState_id: 1,
					Start_Date: '',
					End_Date: '',
					Person_Surname: '',
					Person_Firname: '',
					Person_Secname: ''
				}
			});
		} else {
			var DemandState_id = this.findById('adlDemandState_id').getValue() || '';
			var Start_Date = this.findById('adlStart_Date').getValue() || '';
			var End_Date = this.findById('adlEnd_Date').getValue() || '';
			var Person_Surname = this.findById('adlPerson_Surname').getValue() || '';
			var Person_Firname = this.findById('adlPerson_Firname').getValue() || '';
			var Person_Secname = this.findById('adlPerson_Secname').getValue() || '';
			
			this.AttachmentDemandListGrid.removeAll();
			this.AttachmentDemandListGrid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					DemandState_id: DemandState_id,
					Start_Date: Start_Date,
					End_Date: End_Date,
					Person_Surname: Person_Surname,
					Person_Firname: Person_Firname,
					Person_Secname: Person_Secname
				}
			});
		}
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swAttachmentDemandListWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.maximize();

		this.FiltersPanel.getForm().findField('hDemandState_id').setValue(1);

		this.AttachmentDemandListGrid.removeAll();
		this.AttachmentDemandListGrid.loadData({
			globalFilters: {
				limit: 100,
				start: 0,
				DemandState_id: 1 
			}
		});		
		
		Ext.getCmp('adlDemandState_id').getStore().addListener('load', function(store){ Ext.getCmp('adlDemandState_id').setValue(1); });
	},
	onLoad: function(result) {
		alert('load');
	},
	clearFilters: function () {
		var state_combo = Ext.getCmp('adlDemandState_id');
		if (state_combo) state_combo.setValue(1);
	
		this.findById('adlStart_Date').setValue('');
		this.findById('adlEnd_Date').setValue('');
		this.findById('adlPerson_Surname').setValue('');
		this.findById('adlPerson_Firname').setValue('');
		this.findById('adlPerson_Secname').setValue('');
	},
	title: lang['zayavleniya_na_prikreplenie_mo'],
	width: 700
});