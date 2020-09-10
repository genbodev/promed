/**
* swPersonCardStateDetailViewWindow - окно просмотра конкретных записей журнала движения в картотеке.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      15.06.2009
*/

sw.Promed.swPersonCardStateDetailViewWindow = Ext.extend(sw.Promed.BaseForm, {
	addPersonCard: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_patsienta_uje_otkryito']);
			return false;
		}

		getWnd('swPersonSearchWindow').show({
            onClose: function() {
        		current_window.refreshPersonCardViewGrid();
            },
    		onSelect: function(person_data) {
                getWnd('swPersonCardEditWindow').show({
                	action: 'add',
                	callback: function() {
                		current_window.refreshPersonCardViewGrid();
                	},
                	onHide: function() {
		                    // TODO: Что то придумать с getWnd в таком варианте использования
                        getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
                	},
                	Person_id: person_data.Person_id,
                	PersonEvn_id: person_data.PersonEvn_id,
                	Server_id: person_data.Server_id
                });
            },
            searchMode: 'all'
        });
	},
	buttonAlign: 'left',
	doResetAll: function() {
		var grid = this.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		grid.getStore().removeAll();
	},
	editPersonCard: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_patsienta_uje_otkryito']);
			return false;
		}
       	getWnd('swPersonCardEditWindow').show({
       		action: 'edit',
	       	callback: function() {
    	   		current_window.refreshPersonCardViewGrid();
       		},
	       	onHide: function() {
				current_window.refreshPersonCardViewGrid();
       		},
			PersonCard_id: current_row.data.PersonCard_id,
			Person_id: current_row.data.Person_id,
			Server_id: current_row.data.Server_id
       	});
	},
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    draggable: true,
	deletePersonCard: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_patsienta_uje_otkryito']);
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
						url: C_PERSONCARD_DEL,
						params: {PersonCard_id: current_row.data.PersonCard_id},
						callback: function() {
							current_window.doSearch();
						}
					});
				}
			}
		});
	},
	doSearch: function() {
    	var grid = this.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		var form = this.findById('PersonCardStateDetailViewFilterForm');
		params = {};
		params.SearchFormType = 'PersonCardStateDetail';
		params.PCSD_mode = form.getForm().findField('mode').getValue();
		params.PCSD_LpuRegion_id = form.getForm().findField('PCSD_LpuRegion_id').getValue();
		params.PCSD_LpuAttachType_id = form.getForm().findField('PCSD_LpuAttachType_id').getValue();
		params.PCSD_LpuMotion_id = form.getForm().findField('PCSD_LpuMotion_id').getValue();
		params.PCSD_FromLpu_id = form.getForm().findField('PCSD_FromLpu_id').getValue();
		params.PCSD_ToLpu_id = form.getForm().findField('PCSD_ToLpu_id').getValue();
		params.PCSD_StartDate = form.getForm().findField('StartDate').getValue();
		params.PCSD_EndDate = form.getForm().findField('EndDate').getValue();
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().baseParams.start = 0;
		grid.getStore().baseParams.limit = 100;
		grid.getStore().load({
			params: params
		});
	},
    height: 550,
    id: 'PersonCardStateDetailViewWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.findById('PersonCardStateDetailViewFilterForm').getForm().submit();
				},
				iconCls: 'print16',
				tabIndex: 2034,
				text: lang['pechat']
			}, '-',
				HelpButton(this),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					id: 'PCSDVW_CancelButton',
					tabIndex: 2034,
					text: BTN_FRMCLOSE
				}
			],
			items: [
				new Ext.form.FormPanel({
					autoHeight: true,
					id: 'PersonCardStateDetailViewFilterForm',
					items: [{
						xtype: 'hidden',
						name: 'mode'
					},
					/*{
						xtype: 'hidden',
						name: 'LpuRegion_id'
					},*/
					{
						xtype: 'hidden',
						name: 'StartDate'
					},
					{
						xtype: 'hidden',
						name: 'EndDate'
					},{
						xtype: 'hidden',
						name: 'PCSD_mode'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_LpuAttachType_id'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_LpuRegion_id'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_LpuMotion_id'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_FromLpu_id'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_ToLpu_id'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_StartDate'
					},
					{
						xtype: 'hidden',
						name: 'PCSD_EndDate'
					},
					{
						xtype: 'hidden',
						name: 'SearchFormType',
						value: 'PersonCardStateDetail'
					}
					],
					labelAlign: 'right',
					region: 'north'
				}),
                new sw.Promed.ViewFrame(
				{
					actions:
					[
						{name: 'action_add', handler: function() {Ext.getCmp('PersonCardStateDetailViewWindow').addPersonCard(); }, disabled: true},
						{name: 'action_edit', handler: function() {Ext.getCmp('PersonCardStateDetailViewWindow').editPersonCard(); }, disabled: true},
						{name: 'action_view', handler: function() {Ext.getCmp('PersonCardStateDetailViewWindow').viewPersonCard(); }},
						{name: 'action_delete', handler: function() {Ext.getCmp('PersonCardStateDetailViewWindow').deletePersonCard(); }, disabled: true},
						{name: 'action_refresh'},
						{name: 'action_print'}
					],
					autoLoadData: false,
					dataUrl: C_SEARCH,
					id: 'PersonCardStateDetailViewGrid',
					height: 473,
					focusOn: {name:'PCSDVW_CancelButton', type:'field'},
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields:
					[
						{name: 'PersonCard_id', type: 'int', header: 'ID', key: true},
						{name: 'Person_id', type: 'int', hidden: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'PersonCard_Code',  type: 'string', header: lang['№_amb_kartyi']},
						{name: 'Person_Surname',  type: 'string', header: lang['familiya'], width: 100},
						{name: 'Person_Firname',  type: 'string', header: lang['imya'], width: 100},
						{name: 'Person_Secname',  type: 'string', header: lang['otchestvo'], width: 100},
						{name: 'Person_BirthDay',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{name: 'PAddress_Address',  type: 'string', header: lang['adres_projivaniya'], width: 250},
						{name: 'UAddress_Address',  type: 'string', header: lang['adres_registratsii'], width: 250},
						{name: 'PersonCard_begDate',  type: 'date', header: lang['prikreplenie'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{name: 'PersonCard_endDate',  type: 'date', header: lang['otkreplenie'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{name: 'LpuRegionType_Name',  type: 'string', header: lang['tip']},
						{name: 'LpuRegion_Name',  type: 'string', header: lang['uchastok']},
						{name: 'CardCloseCause_Name',  type: 'string', header: lang['prichina_zakryitiya']},
						{name: 'ActiveLpu_Nick',  type: 'string', header: lang['tek_prikr_lpu'], width: 150},
						{name: 'ActiveLpuRegion_Name',  type: 'string', header: lang['tek_prikr_uchastok'], width: 150},
						{name: 'PersonCard_IsAttachCondit',  type: 'checkbox', header: lang['usl_prikr']},
						{name: 'omsOrgSmo_Nick',  type: 'string', header: lang['smo_po_oms']},
						{name: 'dmsOrgSmo_Nick',  type: 'string', header: lang['smo_po_dms']},
						{name: 'isBDZ',  type: 'checkbox', header: lang['bdz']}/*,
						{name: 'PersonCard_IsDisp',  type: 'string', header: lang['d-uchet']}*/
					],
					toolbar: true,
					totalProperty: 'totalCount'
				})
			]
		});
        sw.Promed.swPersonCardStateDetailViewWindow.superclass.initComponent.apply(this, arguments);
	},
    keys: [],
    layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
    maximizable: true,
    minHeight: 550,
    minWidth: 900,
    modal: true,
    plain: true,
	  resizable: true,
	refreshPersonCardViewGrid: function() {
		this.doSearch();
	},
	show: function() {
		sw.Promed.swPersonCardStateDetailViewWindow.superclass.show.apply(this, arguments);

		var grid = this.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		grid.getStore().removeAll();
		
		grid.removeListener('rowdblclick');
		grid.on('rowdblclick', function(grd, index) {
			grid.ownerCt.ownerCt.ViewActions.action_view.items[0].handler();
		});

		var form = this.findById('PersonCardStateDetailViewFilterForm');

		this.doResetAll();

        form.getForm().setValues(arguments[0]);
        
        // исправлено, потому что при печати передавался LpuRegion_id и поисковый контроллер добавлял условие прикрепленности человека к ЛПУ
        form.getForm().findField('PCSD_LpuRegion_id').setValue(arguments[0]['LpuRegion_id']);		
		
		form.getForm().findField('PCSD_LpuAttachType_id').setValue(arguments[0]['LpuAttachType_id']);

		form.getForm().findField('PCSD_LpuMotion_id').setValue(arguments[0]['LpuMotion_id']);

		form.getForm().findField('PCSD_FromLpu_id').setValue(arguments[0]['FromLpu_id']);
		form.getForm().findField('PCSD_ToLpu_id').setValue(arguments[0]['ToLpu_id']);
		
		form.getForm().findField('PCSD_mode').setValue(form.getForm().findField('mode').getValue());
		//form.getForm().findField('PCSD_LpuRegion_id').setValue(form.getForm().findField('LpuRegion_id').getValue());
		form.getForm().findField('PCSD_StartDate').setValue(form.getForm().findField('StartDate').getValue());
		form.getForm().findField('PCSD_EndDate').setValue(form.getForm().findField('EndDate').getValue());

		if ( arguments[0] && arguments[0].onHide )
			this.onHide = arguments[0].onHide;
		else
			this.onHide = function() {};

		this.doSearch();
		
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;

	},
    title: WND_POL_PERSCARDSTATEDETAILVIEW,
	viewPersonCard: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonCardStateDetailViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_patsienta_uje_otkryito']);
			return false;
		}
       	getWnd('swPersonCardEditWindow').show({
       		action: 'view',
	       	callback: function() {
    	   		current_window.refreshPersonCardViewGrid();
       		},
	       	onHide: function() {
				current_window.refreshPersonCardViewGrid();
       		},
			PersonCard_id: current_row.data.PersonCard_id,
			Person_id: current_row.data.Person_id,
			Server_id: current_row.data.Server_id
       	});
	},
    width: 900
});