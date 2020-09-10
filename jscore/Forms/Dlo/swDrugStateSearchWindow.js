/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 23.04.15
 * Time: 12:42
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swDrugStateSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 763,
	id: 'DrugStateSearchWindow',
	title: lang['medikamentyi_zakuplennyie_po_zayavke'],
	width: 1500,
	doResetFilter: function(){
		var base_form = Ext.getCmp('DrugStateSearchWindow');
		base_form.FilterPanel.getForm().reset();
		var DrugStateGrid = base_form.findById('DSSW_Grid').ViewGridPanel;
		DrugStateGrid.getStore().removeAll();
		var drug_request_period_combo =  base_form.findById('DSSW_DrugRequestPeriod_id');
		drug_request_period_combo.setValue(drug_request_period_combo.getStore().getAt(0).get('DrugRequestPeriod_id'));
	},
	doFilter: function() {

		var base_form = Ext.getCmp('DrugStateSearchWindow');

		var filters = base_form.FilterPanel.getForm().getValues();
		var DrugStateGrid = base_form.findById('DSSW_Grid').ViewGridPanel;
		filters.limit = 100;
		filters.start = 0;

		DrugStateGrid.getStore().load({
			params: filters,
			callback: function() {
				if ( DrugStateGrid.getStore().getCount() > 0 )
				{
					DrugStateGrid.getView().focusRow(0);
				}
			}
		});
	},
	initComponent: function() {
		var form = this;
		this.FilterPanel = new Ext.form.FormPanel({
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 100,
			bodyStyle:'background:#DFE8F6;',
			border: false,
			keys:
				[{
					key: Ext.EventObject.ENTER,
					fn: function(e)
					{
						form.doFilter();
					},
					stopEvent: true
				}],
			items: [{
				title: lang['filtr'],
				titleCollapse: true,
				collapsible: true,
				floatable: false,
				autoHeight: true,
				labelWidth: 120,
				layout: 'form',
				defaults:{bodyStyle:'background:#DFE8F6;'},
				items:[{
					layout: 'column',
					defaults:{bodyStyle:'padding-top: 4px; background:#DFE8F6;', border: false}, //
					border: false,
					items: [{
						autoHeight: true,
						items: [
							{
								allowBlank: false,
								disabled: false,
								id: 'DSSW_DrugRequestPeriod_id',
								xtype: 'swdrugrequestperiodcombo'
							},
							{
								allowBlank: true,
								autoLoad: false,
								comboSubject: 'ReceptFinance',
								fieldLabel: lang['tip_finansirovaniya'],
								id: 'DSSW_ReceptFinance_id',
								hiddenName: 'ReceptFinance_id',
								lastQuery: '',
								listWidth: 200,
								validateOnBlur: true,
								width: 200,
								xtype: 'swcommonsprcombo'
							},
							{
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['kod_mnn_po_zayavke'],
								id: 'DrugProtoMnn_Code',
								xtype: 'textfield'
							},
							{
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['mnn'],
								id: 'DrugProtoMnn_Name',
								xtype: 'textfield'
							},
							{
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['kod_ges_medikamenta'],
								id: 'Drug_Code',
								xtype: 'textfield'
							},
							{
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['torgovoe_naim'],
								id: 'Drug_Name',
								xtype: 'textfield'
							}

						],
						buttons : [
							{
								text: lang['poisk'],//BTN_FILTER,
								handler: function() {
									form.PersonDataChecked = new Array();
									form.doFilter();
								},
								iconCls: 'search16'
							},
							{
								text: BTN_RESETFILTER,
								handler: function() {
									form.doResetFilter();
								},
								iconCls: 'resetsearch16'
							},
							'-',
							{
								text: '-'
							}
						],
						style: 'padding: 0px;',
						title: '',
						xtype : "fieldset"
					}]
				}]
			}]
		});
		this.DrugStateGrid = new sw.Promed.ViewFrame(
			{
				actions:
					[
						{
							name: 'action_add',
							hidden: false,
							handler: function(){
								var params = new Object();
								params.action = 'add';
								getWnd('swDrugStateEditWindow').show(params);
							}
						},
						{
							name: 'action_edit',
							hidden: false,
							handler: function(){
								var DrugStateGrid = form.findById('DSSW_Grid');
								var record = DrugStateGrid.getGrid().getSelectionModel().getSelected();

								var DrugState_id = record.get('DrugState_id');
								var params = new Object();
								params.DrugState_id = DrugState_id;
								params.action = 'edit';
								getWnd('swDrugStateEditWindow').show(params);
							}
						},
						{
							name: 'action_view',
							hidden: false,
							handler: function(){
								var DrugStateGrid = form.findById('DSSW_Grid');
								var record = DrugStateGrid.getGrid().getSelectionModel().getSelected();

								var DrugState_id = record.get('DrugState_id');
								var params = new Object();
								params.DrugState_id = DrugState_id;
								params.action = 'view';
								getWnd('swDrugStateEditWindow').show(params);
							}
						},
						{
							name: 'action_delete',
							hidden: false,
							handler: function(){
								var DrugStateGrid = form.findById('DSSW_Grid');
								var record = DrugStateGrid.getGrid().getSelectionModel().getSelected();

								var DrugState_id = record.get('DrugState_id');

								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'yes' ) {
											Ext.Ajax.request({
												callback: function(options, success, response) {
													if ( success ) {
														var obj = Ext.util.JSON.decode(response.responseText);
														if(!obj.success) {
															return false;
														}
														DrugStateGrid.ViewGridPanel.getStore().remove(record);

														if ( DrugStateGrid.ViewGridPanel.getStore().getCount() == 0 ) {
															LoadEmptyRow(DrugStateGrid);
														}

														DrugStateGrid.ViewGridPanel.getView().focusRow(0);
														DrugStateGrid.getGrid().getSelectionModel().selectFirstRow();
													}
													else {
														sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_voznikli_oshibki']);
													}
												},
												params: {
													DrugState_id: DrugState_id
												},
												url: '/?c=Drug&m=deleteDrugState'
											});
										}
									},
									icon: Ext.MessageBox.QUESTION,
									msg: lang['udalit_zapis'],
									title: lang['vopros']
								})
							}
						},
						{
							name: 'action_refresh',
							hidden: false
						},
						{
							name: 'action_print',
							hidden: true
						}
					],
				autoLoadData: false,
				border: false,
				anchor: '100%',
				autoexpand: 'expand',
				dataUrl: '?c=Drug&m=loadDrugStateGrid',
				editformclassname: 'swDrugStateEditWindow',
				id: 'DSSW_Grid',
				pageSize: 100,
				root: 'data',
				toolbar: true,
				totalProperty: 'totalCount',
				paging: true,
				region: 'center',

				onCellDblClick: function (grid, rowIdx, colIdx, event){

				},
				onLoadData: function() {
					var base_form = Ext.getCmp('DrugStateSearchWindow');
					var records = new Array();
				},
				stringfields:
					[
						{name: 'DrugState_id', type: 'int', hidden: true, key:true},
						{name: 'ReceptFinance_Name',  type: 'string', header: lang['finansirovanie'], width: 255},
						{name: 'DrugProtoMnn_Code',  type: 'string', header: lang['kod_mnn_v_zayavke'], width: 150},
						{name: 'DrugProtoMnn_Name',  type: 'string', header: lang['mnn_v_zayavke'], width: 250},
						{name: 'Drug_Code',type: 'string',header: lang['kod_ges'],width:150},
						{name: 'Drug_Name',  type: 'string', header: lang['torg_naimenovanie'], width: 250},
						{name: 'DrugState_Price',  type: 'string', header: lang['tsena'], width: 100},
						{name: 'DrugState_insDT',  type: 'string', header: lang['data_vneseniya'], width: 100},
						{name: 'DrugState_updDT',  type: 'string', header: lang['data_izmeneniya'], width: 100}
					]
			});
		Ext.apply(this, {
			layout: 'fit',
			buttons: [
				'-',
				HelpButton(this),
				{
					handler: function() {
						Ext.getCmp('DrugStateSearchWindow').hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			maximizable: true,
			items:
				[
					{
						height: Ext.isIE ? 500 : 700,
						id: 'pacient_tab',
						title: lang['prikrepleniya'],
						layout:'border',
						items: [
							this.FilterPanel,
							this.DrugStateGrid
						]
					}
				]
		});
		sw.Promed.swDrugStateSearchWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function(){
		sw.Promed.swDrugStateSearchWindow.superclass.show.apply(this, arguments);
		this.onHide = Ext.emptyFn;
		var form = Ext.getCmp('DrugStateSearchWindow');
		var DrugStateGrid = form.findById('DSSW_Grid').ViewGridPanel;
		DrugStateGrid.getStore().removeAll();
		this.restore();
		this.center();
		this.maximize();
		this.doLayout();
		var drug_request_period_combo =  form.findById('DSSW_DrugRequestPeriod_id');
		drug_request_period_combo.setValue(drug_request_period_combo.getStore().getAt(0).get('DrugRequestPeriod_id'));
		this.PersonDataChecked = new Array();
	}
});