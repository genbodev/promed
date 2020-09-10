/**
* swPersonDispHistoryWindow - окно просмотра истории диспансеризации.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      03.07.2009
*/

sw.Promed.swPersonDispHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	addPersonDisp: function() {
		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = 'add';
		params.callback = Ext.emptyFn;
		params.Diag_id = this.Diag_id;
		params.isDopDisp = this.isDopDisp;
		params.LpuSection_id = this.LpuSection_id;
		params.MedPersonal_id = this.MedPersonal_id;
		params.onHide = function() {
			this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().removeAll();

			if ( this.personId ) {
				this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().load({
					params: {
						Person_id: this.personId
					}
				});
			}
		}.createDelegate(this);

		formParams.Person_id = this.personId;
		formParams.Server_id = this.serverId;

		params.formParams = formParams;

		getWnd('swPersonDispEditWindow').show(params);
	},
	editPersonDisp: function() {
		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = 'edit';
		params.callback = Ext.emptyFn;
		params.onHide = function() {
			this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().removeAll();

			if ( this.personId ) {
				this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().load({
					params: {
						Person_id: this.personId
					}
				});
			}
		}.createDelegate(this);

		formParams.Person_id = this.personId;
		formParams.PersonDisp_id = this.findById('PDHF_PersonDispHistoryGrid').getGrid().getSelectionModel().getSelected().get('PersonDisp_id');
		formParams.Server_id = this.serverId;

		params.formParams = formParams;

		getWnd('swPersonDispEditWindow').show(params);
	},
	viewPersonDisp: function() {
		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var formParams = new Object();

		params.action = 'view';
		params.callback = Ext.emptyFn;
		params.onHide = function() {
			this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().removeAll();

			if ( this.personId ) {
				this.findById('PDHF_PersonDispHistoryGrid').getGrid().getStore().load({
					params: {
						Person_id: this.personId
					}
				});
			}
		}.createDelegate(this);

		formParams.Person_id = this.personId;
		formParams.PersonDisp_id = this.findById('PDHF_PersonDispHistoryGrid').getGrid().getSelectionModel().getSelected().get('PersonDisp_id');
		formParams.Server_id = this.serverId;

		params.formParams = formParams;

		getWnd('swPersonDispEditWindow').show(params);
	},
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	deletePersonDisp: function() {
		var current_window = this;
		var grid = current_window.findById('PDHF_PersonDispHistoryGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonDispEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: C_PERSDISP_DEL,
						params: {PersonDisp_id: current_row.data.PersonDisp_id},
						callback: function() {
							current_window.findById('PDHF_PersonDispHistoryGrid').ViewGridPanel.getStore().load({ params: { Person_id: current_window.personId } })
						}
					});
				}
			}
		});
	},
	height: 400,
	id: 'PersonDispHistoryWindow',
	initComponent: function() {
		var win = this;

		Ext.apply(this, {
			buttons: [{
				disabled: true,
				handler: function() {
					Ext.Msg.alert(BTN_GRIDPRINT, lang['pechat_istorii_dispanserizatsii']);
				},
				iconCls: 'print16',
				text: BTN_GRIDPRINT
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.returnFunc();
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}
			],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'PDHF_PersonInformationFrame',
				region: 'north'
			}),
			new sw.Promed.ViewFrame(
			{
				actions:
				[
					{name: 'action_add', handler: function() {Ext.getCmp('PersonDispHistoryWindow').addPersonDisp(); }},
					{name: 'action_edit', handler: function() {Ext.getCmp('PersonDispHistoryWindow').editPersonDisp(); }},
					{name: 'action_view', handler: function() {Ext.getCmp('PersonDispHistoryWindow').viewPersonDisp(); }},
					{name: 'action_delete', handler: function() {Ext.getCmp('PersonDispHistoryWindow').deletePersonDisp(); }},
					{name: 'action_refresh', disabled: true},
					{name: 'action_print'}
				],
//					autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_PERSDISP_HIST,
				id: 'PDHF_PersonDispHistoryGrid',
				onRowSelect: function(sm, index, record) {
					if ( win.action != 'view' && !Ext.isEmpty(record.get('PersonDisp_id')) && record.get('IsOurLpu') != 1 )
					{
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_edit', false);
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_view', false);
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_delete', false);
					}
					else
					{
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_edit', true);
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_view', true);
						Ext.getCmp('PDHF_PersonDispHistoryGrid').setActionDisabled('action_delete', true);
					}
				},
//				focusOn: {name:'PCSW_SearchButton', type:'field'},
//					object: 'LpuUnit',
				region: 'center',
				//editformclassname: swLpuUnitEditForm,
				stringfields:
				[
					{name: 'PersonDisp_id', type: 'int', header: 'ID', key: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'Server_id', type: 'int', hidden: true},
					{name: 'Diag_Code',  type: 'string', header: lang['diagnoz']},
					{name: 'PersonDisp_begDate',  type: 'date', header: lang['vzyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'PersonDisp_endDate',  type: 'date', header: lang['snyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
					{name: 'Lpu_Nick',  type: 'string', header: lang['lpu']},
					{name: 'LpuSection_Name',  type: 'string', header: lang['otdelenie']},
					{name: 'LpuRegion_Name',  type: 'string', header: lang['uchastok']},
					{name: 'MedPersonal_FIO',  type: 'string', header: lang['vrach']},
					{name: 'IsOurLpu',  type: 'int', hidden: true}/*,
					{name: 'PersonCard_IsDisp',  type: 'string', header: lang['d-uchet']}*/
				],
				toolbar: true
			})]
		});
		sw.Promed.swPersonDispHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonDispHistoryWindow');
			current_window.hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	serverId: null,
	show: function() {
		sw.Promed.swPersonDispHistoryWindow.superclass.show.apply(this, arguments);

		this.Diag_id = null;
		this.LpuSection_id = null;
		this.MedPersonal_id = null;
		this.onHide = Ext.emptyFn;

		this.action = 'edit';

		if (arguments[0])
		{
			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
			}

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if (arguments[0].Diag_id)
			{
				this.Diag_id = arguments[0].Diag_id;
			}

			if (arguments[0].LpuSection_id)
			{
				this.LpuSection_id = arguments[0].LpuSection_id;
			}

			if (arguments[0].MedPersonal_id)
			{
				this.MedPersonal_id = arguments[0].MedPersonal_id;
			}

			if (arguments[0].onHide)
			{
				this.onHide = arguments[0].onHide;
			}

			if (arguments[0].Person_id)
			{
				this.personId = arguments[0].Person_id;
			}

			if (arguments[0].Server_id)
			{
				this.serverId = arguments[0].Server_id;
			}
			
			if ( arguments[0].isDopDisp )
			{
				this.isDopDisp = arguments[0].isDopDisp;
			}
			else
				this.isDopDisp = false;
		}

		this.findById('PDHF_PersonDispHistoryGrid').setReadOnly(this.action == 'view');

		this.findById('PDHF_PersonInformationFrame').load({
			Person_id: this.personId,
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.findById('PDHF_PersonDispHistoryGrid').ViewGridPanel.store.removeAll();

		if (this.personId)
		{
			this.findById('PDHF_PersonDispHistoryGrid').ViewGridPanel.getStore().load({ params: { Person_id: this.personId, Server_id: this.serverId } })
		}

		// this.setHeight(400);
		// this.setWidth(600);
		this.restore();
		this.center();
	},
	title: lang['istoriya_dispanserizatsii_patsienta'],
	width: 600
});
