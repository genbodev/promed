/**
* swJournalDirectionsWindow - форма журнала направлений
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swJournalDirectionsWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      06.10.2010
* @comment      Префикс для id компонентов EJDW. 
*/

sw.Promed.swJournalDirectionsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'EJDW',
	//maximized: true,
	//autoHeight: true,
	//autoWidth: true,
	height: 500,
	width: 750,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: lang['jurnal_napravleniy'],
	Form_data: null,
	PersonInformationFrame_data: null,
	initComponent: function() 
	{
		var current_window = this;
		this.grid = new sw.Promed.ViewFrame(
		{
			id: 'EJDW_EvnDirectionGrid',
			object: 'EvnDirection',
			dataUrl: '/?c=EvnDirection&m=loadEvnDirectionGrid',
			layout: 'fit',
			region: 'center',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoExpandMin: 100,
			autoLoadData: false,
			stringfields:
			[
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirection_pid', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'DirType_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'MedPersonal_zid', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
				{name: 'EvnDirection_Descr', type: 'string', hidden: true},
				{name: 'EvnDirection_setDate', type: 'date', dateFormat: 'd.m.Y', header: lang['data_vyipiski_napravleniya'], width: 150},
				{name: 'EvnDirection_Num', type: 'int', header: lang['nomer_napravleniya'], width: 120},
				{name: 'DirType_Name', type: 'string', header: lang['tip_napravleniya'], width: 250},
				{name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], autoexpand: true}
			],
			actions:
			[
				{
					name:'action_add',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP,
					handler: function() { 
						// запись пациента к другому врачу с выпиской электр.направления
						var cr_wd = this;
						var params = 
						{
							Person_id: cr_wd.Form_data.Person_id,
							PersonEvn_id: cr_wd.Form_data.PersonEvn_id,
							Server_id: cr_wd.Form_data.Server_id,
							UserMedStaffFact_id: cr_wd.Form_data.UserMedStaffFact_id,
							Person_Firname: cr_wd.PersonInformationFrame_data.person_firname,
							Person_Surname: cr_wd.PersonInformationFrame_data.person_surname,
							Person_Secname: cr_wd.PersonInformationFrame_data.person_secname,
							Person_Birthday: cr_wd.PersonInformationFrame_data.person_birthday,							
							formMode: 'jodirection',
							EvnDirection_pid: cr_wd.Form_data.EvnDirection_pid,
							EvnDirection_id: 0
						}
						getWnd('swMPRecordWindow').show(params);
					}.createDelegate(this)
				},
				{name:'action_edit', text: BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT_TIP, handler: function() { this.openEvnDirectionEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', text: BTN_GRIDVIEW, tooltip: BTN_GRIDVIEW_TIP, handler: function() { this.openEvnDirectionEditWindow('view'); }.createDelegate(this)},
				{name:'action_delete', text: BTN_GRIDDEL, tooltip: BTN_GRIDDEL_TIP, handler: function() { this.deleteEvent('EvnDirection'); }.createDelegate(this)}
			],
			onDblClick: function(grid, number, obj) {
				current_window.openEvnDirectionEditWindow('edit');
			}
		});

		Ext.apply(this, 
		{
			region: 'center',
			layout: 'border',
			items: [
			this.grid
			],
			buttons: [{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'EJDW_HelpButton',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_EJDW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('EJDW').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swJournalDirectionsWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() 
	{
		sw.Promed.swJournalDirectionsWindow.superclass.show.apply(this, arguments);
		this.center();
		cr_wd = this;
		//log(arguments);
		if (arguments[0] && arguments[0].Form_data)
			cr_wd.Form_data = arguments[0].Form_data;
		if (arguments[0] && arguments[0].PersonInformationFrame_data)
			cr_wd.PersonInformationFrame_data = arguments[0].PersonInformationFrame_data;
		if (!cr_wd.Form_data && !cr_wd.PersonInformationFrame_data)
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: lang['otsutstvuyut_neobhodimyie_parametryi'],
				title: ERR_WND_TIT
			});
			this.hide();
			return false;
		}
		
		this.grid.loadData({
			globalFilters: {
				limit: 100,
				start: 0,
				EvnDirection_pid: cr_wd.Form_data.EvnDirection_pid
			}
		});
	},
	deleteEvent: function(event) {
		if ( this.action && this.action == 'view' ) {
			return false;
		}

		if ( event != 'EvnDirection') {
			return false;
		}

		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDirection':
				error = lang['pri_udalenii_napravleniya_voznikli_oshibki'];
				grid = this.grid.getGrid();
				question = lang['udalit_napravlenie'];
				url = '/?c=EvnDirection&m=deleteEvnDirection';
			break;

		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnDirection':
				params['EvnDirection_id'] = selected_record.get('EvnDirection_id');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();
							
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);
								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}
							
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}.createDelegate(this),
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	openEvnDirectionEditWindow: function(action) {
		var grid = this.grid.getGrid();
		var cr_wd = this;

		if ( getWnd('swEvnDirectionEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyipiski_napravleniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		if ( action == 'add' ) {
			params.EvnDirection_id = 0;
			params.EvnDirection_pid = cr_wd.Form_data.EvnDirection_pid;
			params.Person_id = cr_wd.Form_data.Person_id;
			params.PersonEvn_id = cr_wd.Form_data.PersonEvn_id;
			params.Server_id = cr_wd.Form_data.Server_id;
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDirection_id') ) {
				return false;
			}
			params = selected_record.data;
		}

		getWnd('swEvnDirectionEditWindow').show({
			action: action,
			EvnDirection_id: params.EvnDirection_id,
			callback: function(data) {
				if ( !data || !data.evnDirectionData ) {
					return false;
				}

				var record = grid.getStore().getById(data.evnDirectionData.EvnDirection_id);

				if ( !record ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDirection_id') ) {
						grid.getStore().removeAll();
					}

					data.evnDirectionData.limit = 100;
					data.evnDirectionData.start = 0;
					grid.loadData({
						globalFilters: data.evnDirectionData
					});
				}
				else {
					var evn_direction_fields = new Array();
					var i = 0;

					grid.getStore().fields.eachKey(function(key, item) {
						evn_direction_fields.push(key);
					});

					for ( i = 0; i < evn_direction_fields.length; i++ ) {
						record.set(evn_direction_fields[i], data.evnDirectionData[evn_direction_fields[i]]);
					}

					record.commit();
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				//grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_id: cr_wd.PersonInformationFrame_data.person_id,
			Person_Birthday: cr_wd.PersonInformationFrame_data.person_birthday,
			Person_Firname: cr_wd.PersonInformationFrame_data.person_firname,
			Person_Secname: cr_wd.PersonInformationFrame_data.person_secname,
			Person_Surname: cr_wd.PersonInformationFrame_data.person_surname
		});
	}
});
