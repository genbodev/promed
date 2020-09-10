/**
 * swMedicalCareKindLinkViewWindow - окно настройки кодов видов медицинской помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.11.2013
 */

sw.Promed.swMedicalCareKindLinkViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border : false,
	closeAction :'hide',
	height: 500,
	width: 800,
	id: 'MedicalCareKindLinkViewWindow',
	title: lang['nastroyka_kodov_vidov_meditsinskoy_pomoschi'],
	layout: 'border',
	modal: true,
	maximized: true,
	maximizable: true,

	openMedicalCareKindLinkEditWindow: function(action)
	{
		if (!action || !action.inlist(['add','edit','view'])) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swMedicalCareKindLinkEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_okno_nastroyki_kodov_vidov_meditsinskoy_pomoschi_uje_otkryito']);
			return false;
		}

		var grid = this.GridPanel.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			this.GridPanel.ViewActions.action_refresh.execute();
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		} else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('MedicalCareKindLink_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams.MedicalCareKindLink_id = record.get('MedicalCareKindLink_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swMedicalCareKindLinkEditWindow').show(params);
	},

	show: function()
	{
		sw.Promed.swMedicalCareKindLinkViewWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var grid = this.GridPanel.getGrid();

		grid.getStore().load();

		loadMask.hide();
	},

	initComponent: function()
	{
		var wnd = this;

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'MCKLVW_QueryGrid',
			region: 'center',
			object: 'MedicalCareKindLink',
			dataUrl: '/?c=MedicalCareKindLink&m=loadMedicalCareKindLinkGrid',
			paging: false,
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'MedicalCareKindLink_id', type: 'int', header: 'ID', key: true},
					{name: 'LpuUnitType_id', type: 'int', hidden: true},
					{name: 'PayType_id', type: 'int', hidden: true},
					{name: 'LpuSectionProfile_id', type: 'int', hidden: true},
					{name: 'EvnClass_id', type: 'int', hidden: true},
					{name: 'MedicalCareKind_id', type: 'int', hidden: true},
					{name: 'MedicalCareKind_Code', type: 'int', header: lang['kod_vida_mp'], width: 80},
					{name: 'MedicalCareKind_Name', type: 'string', header: lang['naimenovanie_vida_mp'], /*width: 120*/id: 'autoexpand'},
					{name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil_otdeleniya'], width: 200},
					{name: 'EvnClass_Name', type: 'string', header: lang['vid_dokumenta'], width: 200},
					{name: 'PayType_Name', type: 'string', header: lang['istochnik_finansirovaniya'], width: 200},
					{name: 'LpuUnitType_Name', type: 'string', header: lang['tip_gruppyi_otdeleniy'], width: 200}
				],
			actions:
				[
					{name:'action_add', handler: function (){wnd.openMedicalCareKindLinkEditWindow('add');}},
					{name:'action_edit', handler: function (){wnd.openMedicalCareKindLinkEditWindow('edit');}},
					{name:'action_view', handler: function (){wnd.openMedicalCareKindLinkEditWindow('view');}}
				],
			onDblClick: function()
			{
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			}
		});

		Ext.apply(this, {
			items : [
				this.GridPanel
			],
			buttons : [
				'-',
				HelpButton(this, -1),
				{
					handler: function()
					{
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}],
			buttonAlign : "right"
		});
		sw.Promed.swMedicalCareKindLinkViewWindow.superclass.initComponent.apply(this, arguments);
	}
});