/**
* swMedStaffFactLinkViewWindow - окно просмотра списка связанных мест работы среднего мед. персонала
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      08.10.2013
*/

sw.Promed.swMedStaffFactLinkViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteMedStaffFactLink: function() {
		var grid = this.MedStaffFactLinkGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_svyazki_mest_rabotyi_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() || Ext.isEmpty(grid.getGrid().getSelectionModel().getSelected().get('MedStaffFactLink_id')) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zapis_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var MedStaffFactLink_id = selected_record.get('MedStaffFactLink_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_svyazki_mest_rabotyi_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							MedStaffFactLink_id: MedStaffFactLink_id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], (!Ext.isEmpty(response_obj.Error_Msg) ? response_obj.Error_Msg : lang['pri_udalenii_svyazki_mest_rabotyi_voznikli_oshibki_[tip_oshibki_3]']));
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=MedStaffFactLink&m=deleteMedStaffFactLink'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_svyazku_mest_rabotyi'],
			title: lang['vopros']
		});
	},
	draggable: true,
	height: 500,
	id: 'swMedStaffFactLinkViewWindow',
	initComponent: function() {
		var form = this;

		form.MedStaffFactLinkGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { form.openMedStaffFactLinkEditForm('add'); } },
				{ name: 'action_edit', handler: function() { form.openMedStaffFactLinkEditForm('edit'); } },
				{ name: 'action_view', handler: function() { form.openMedStaffFactLinkEditForm('view'); } },
				{ name: 'action_delete', handler: function() { form.deleteMedStaffFactLink(); } },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=MedStaffFactLink&m=loadMedStaffFactLinkGrid',
			id: 'MSFLVW_MedStaffFactLinkGrid',
			onDblClick: function() {
				form.openMedStaffFactLinkEditForm('edit');
			}.createDelegate(this),
			onEnter: function() {
				form.openMedStaffFactLinkEditForm('edit');
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				//
			}.createDelegate(this),
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'MedStaffFactLink_id', type: 'int', header: 'ID', key: true },
				{ name: 'MedStaffFact_id', type: 'int', hidden: true },
				{ name: 'MedStaffFact_sid', type: 'int', hidden: true },
				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], id: 'autoexpand' },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 150 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 150 },
				{ name: 'MedStaffFactLink_begDT', type: 'date', header: lang['data_nachala'], width: 100 },
				{ name: 'MedStaffFactLink_endDT', type: 'date', header: lang['data_okonchaniya'], width: 100 }
			],
			toolbar: true
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				tabIndex: 1505,
				text: BTN_FRMCANCEL
			}],
			items: [
				form.MedStaffFactLinkGrid
			]
		});

		sw.Promed.swMedStaffFactLinkViewWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	openMedStaffFactLinkEditForm: function(action) {
		if ( Ext.isEmpty(action) || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( getWnd('swMedStaffFactLinkEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_svyazki_mest_rabotyi_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var params = new Object();
		var grid = this.MedStaffFactLinkGrid.getGrid();

		if ( action == 'add' ) {
			formParams.MedStaffFactLink_id = 0;
			formParams.MedStaffFact_id = this.MedStaffFact_id;
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('MedStaffFactLink_id')) ) {
				return false;
			}

			formParams.MedStaffFactLink_id = grid.getSelectionModel().getSelected().get('MedStaffFactLink_id');
			formParams.MedStaffFact_id = grid.getSelectionModel().getSelected().get('MedStaffFact_id');
			formParams.MedStaffFact_sid = grid.getSelectionModel().getSelected().get('MedStaffFact_sid');
			formParams.MedStaffFactLink_begDT = grid.getSelectionModel().getSelected().get('MedStaffFactLink_begDT');
			formParams.MedStaffFactLink_endDT = grid.getSelectionModel().getSelected().get('MedStaffFactLink_endDT');
		}

		params.action = action;
		params.callback = function() {
			this.MedStaffFactLinkGrid.ViewActions.action_refresh.execute();
		}.createDelegate(this)
		params.formParams = formParams;

		getWnd('swMedStaffFactLinkEditWindow').show(params);
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swMedStaffFactLinkViewWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.MedStaffFact_id = null;
		form.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || Ext.isEmpty(arguments[0].MedStaffFact_id) ) {
			sw.swMsg.alert('', '', function() { form.hide(); });
			return false;
		}

		form.MedStaffFact_id = arguments[0].MedStaffFact_id;

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		form.MedStaffFactLinkGrid.loadData({
			globalFilters: {
				MedStaffFact_id: form.MedStaffFact_id
			}
		});
	},
	title: lang['sredniy_medpersonal'],
	width: 750
});