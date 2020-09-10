/**
* swOrgFarmacyViewWindow - окно просмотра списка аптек.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      15.10.2009
*/

sw.Promed.swOrgFarmacyViewWindow = Ext.extend(sw.Promed.BaseForm, {
	closeAction: 'hide',
	draggable: false,
	id: 'OrgFarmacyViewWindow',
	initComponent: function() {
		Ext.apply(this, {
		buttonAlign: 'right',
			buttons: [{
				handler: function(button, event) {
					ShowHelp(WND_DLO_OFVIEW);
				}.createDelegate(this),
				iconCls: 'help16',
				text: BTN_FRMHELP
			}, {
				handler: function() { this.ownerCt.hide() },
				iconCls: 'close16',
				id: 'OFVW_CloseButton',
				onShiftTabAction: function(field) {
					var grid = Ext.getCmp('OFVW_OrgFarmacyGrid');
					if ( grid.getStore().getCount() > 0 ) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				},
				onTabAction: function(field) {
					var grid = Ext.getCmp('OFVW_OrgFarmacyGrid');
					if ( grid.getStore().getCount() > 0 ) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				},
				text: lang['zakryit']
			}],
			enableKeyEvents: true,
			items: [ new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('OrgFarmacyViewWindow').openOrgFarmacyEditWindow('add'); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('OrgFarmacyViewWindow').openOrgFarmacyEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('OrgFarmacyViewWindow').openOrgFarmacyEditWindow('view'); } },
					{ name: 'action_delete', handler: Ext.emptyFn, disabled: true },
					{ name: 'action_refresh' },
					{ name: 'action_print' }
				],
				autoLoadData: true,
				dataUrl: '/?c=Org&m=loadOrgFarmacyList',
				id: 'OFVW_OrgFarmacyViewGrid',
				focusOn: { name: 'PCSW_SearchButton', type: 'field' },
				focusPrev: { name: 'PCSW_SearchButton', type: 'field' },
				object: 'OrgFarmacy',
				region: 'center',
				stringfields: [
					{ name: 'OrgFarmacy_id', type: 'int', header: 'ID', key: true },
					{ name: 'Org_id', type: 'int', header: 'Org_id', hidden: true },
					{ name: 'OrgFarmacy_Name', type: 'string', header: lang['apteka'], width: 250 },
					{ name: 'OrgFarmacy_Nick', type: 'string', header: lang['kratkoe_naimenovanie'], width: 150 },
					{ name: 'OrgFarmacy_Address', type: 'string', header: lang['adres'], width: 150 },
					{ name: 'OrgFarmacy_HowGo', type: 'string', header: lang['kak_dobratsya'], width: 150 },
					{ name: 'OrgFarmacy_ACode', type: 'string', header: lang['kod'], width: 60 },
					{ name: 'OrgFarmacy_Phone', type: 'string', header: lang['telefon'], width: 150 },
					{ name: 'OrgFarmacy_IsDisabled', type: 'checkbox', header: lang['zakryita'], width: 60 },
					{ name: 'OrgFarmacy_IsFedLgot', type: 'checkbox', header: lang['fed_lg'], width: 60 },
					{ name: 'OrgFarmacy_IsRegLgot', type: 'checkbox', header: lang['reg_lg'], width: 60 },
					{ name: 'OrgFarmacy_IsNozLgot', type: 'checkbox', header: lang['7_noz'], width: 60 }
				],
				toolbar: true
			})],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('OrgFarmacyViewWindow').hide();
				},
				key: [ Ext.EventObject.P ],
				stopEvent: true
			}]
		});
		sw.Promed.swOrgFarmacyViewWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.findById('OFVW_OrgFarmacyViewGrid').removeAll();
		}
	},
	maximized: true,
	modal: true,
	openOrgFarmacyEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var current_window = this;

		if ( getWnd('swOrgEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_organizatsii_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var grid = current_window.findById('OFVW_OrgFarmacyViewGrid').ViewGridPanel;

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.OrgData ) {
				return false;
			}

			// Обновить запись в grid
			var index = grid.getStore().findBy(function(record, id) {
				if ( record.get('OrgFarmacy_id') == data.OrgData.OrgFarmacy_id )
					return true;
				else
					return false;
			});
			var record = grid.getStore().getAt(index);

			if ( record ) {
				// Обновить запись
				record.set('OrgFarmacy_ACode', data.OrgData.OrgFarmacy_ACode);
				record.set('OrgFarmacy_Address', data.OrgData.OrgFarmacy_Address);
				record.set('OrgFarmacy_HowGo', data.OrgData.OrgFarmacy_HowGo);
				record.set('OrgFarmacy_id', data.OrgData.OrgFarmacy_id);
				record.set('OrgFarmacy_IsDisabled', data.OrgData.OrgFarmacy_IsDisabled);
				record.set('OrgFarmacy_IsFedLgot', data.OrgData.OrgFarmacy_IsFedLgot);
				record.set('OrgFarmacy_IsNozLgot', data.OrgData.OrgFarmacy_IsNozLgot);
				record.set('OrgFarmacy_IsRegLgot', data.OrgData.OrgFarmacy_IsRegLgot);
				record.set('OrgFarmacy_Name', data.OrgData.OrgFarmacy_Name);
				record.set('OrgFarmacy_Nick', data.OrgData.OrgFarmacy_Nick);
				record.set('OrgFarmacy_Phone', data.OrgData.OrgFarmacy_Phone);

				record.commit();
			}
			else {
				grid.getStore().loadData([ data.OrgData ], true);
			}
		};
		params.onHide = Ext.emptyFn;
		params.orgType = 'farm';

		if ( action == 'edit' || action == 'view' ) {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.Org_id = selected_record.get('Org_id');
		}

		getWnd('swOrgEditWindow').show( params );
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swOrgFarmacyViewWindow.superclass.show.apply(this, arguments);
	},
	title: WND_DLO_OFVIEW
});