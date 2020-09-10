/**
* swPersonCardStateViewWindow - журнал движения по картотеке(ЕРПН)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      11.06.2009
* tabIndex: 2200
*/




sw.Promed.swPersonCardStateViewWindow = Ext.extend(sw.Promed.BaseForm, {
    
	buttonAlign: 'left',
	doResetAll: function() {
  		var form = this.findById('PersonCardStateViewFilterForm');
		form.getForm().reset();
		var grid = this.findById('PersonCardStateViewGrid');
		grid.getStore().removeAll();
	},
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    draggable: true,
	doSearch: function() {
    	var grid = this.findById('PersonCardStateViewGrid');
		var form = this.findById('PersonCardStateViewFilterForm');
		if ( !form.getForm().findField('Period').isValid() )
		{
		   Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
					form.getForm().findField('Period').focus(true, 100);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['period_ukazan_neverno_zapolnite_korrektno_eto_pole'],
                title: ERR_INVFIELDS_TIT
           });
		   return false;
		}
		var params = form.getForm().getValues();
		var loadMask = new Ext.LoadMask(Ext.get('PersonCardStateViewWindow'), {msg: "Подождите, идет поиск..."});
   		loadMask.show();
		grid.getStore().removeAll();
		grid.getStore().baseParams = '';
		grid.getStore().load({
			params: params,
			callback: function() {
				loadMask.hide();
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getView().focusCell(0, 6);
					grid.getSelectionModel().select(0, 6);
				}

			}
		});
	},
	setLpuId: function() {
		var current_window = this;
		var lpu_combo = current_window.findById('PersonCardStateViewFilterForm').getForm().findField('Lpu_id');
		lpu_combo.setValue(Ext.globalOptions.globals.lpu_id);
		lpu_combo.fireEvent('change', lpu_combo, Ext.globalOptions.globals.lpu_id, 0);
		lpu_combo.disable();
	},
    height: 550,
    id: 'PersonCardStateViewWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'PCSVW_SearchButton',
				tabIndex: 2205,
				text: lang['pokazat']
			}, {
				handler: function() {
					this.ownerCt.viewPersonCardStateDetails();
				},
				iconCls: 'view16',
				id: 'PCSVW_DetailVIewButton',
				tabIndex: 2205,
				text: lang['prosmotr_detaley']
			}, {
				handler: function() {
					this.ownerCt.printPersonCardStateGrid();
				},
				iconCls: 'print16',
				id: 'PCSVW_PrintButton',
				tabIndex: 2205,
                text: BTN_FRMPRINTALL
			}, {
				handler: function() {
					this.ownerCt.printPersonCardStateRecord();
				},
				iconCls: 'print16',
				id: 'PCSVW_PrintCurButton',
				tabIndex: 2205,
                text: BTN_FRMPRINTCUR
			}, '-',
				HelpButton(this, 2206), 
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				tabIndex: 2207,
				text: BTN_FRMCLOSE
			}
			],
			items: [
				new Ext.form.FormPanel({
					autoHeight: true,
					id: 'PersonCardStateViewFilterForm',
					labelWidth: 120,
					items: [{
							hiddenName: 'Lpu_id',
							hidden: true,
							hideLabel: true,
							width: 400,
							listeners: {
								'change': function(combo, lpuId) {
									var base_form = this.ownerCt.getForm();

									if (
										base_form.findField('LpuRegion_id').getStore().getCount() == 0
										|| base_form.findField('LpuRegion_id').getStore().getAt(base_form.findField('LpuRegion_id').getStore().getCount() - 1).get('Lpu_id') != lpuId
									) {
										base_form.findField('LpuRegion_id').clearValue();
										base_form.findField('LpuRegion_id').getStore().removeAll();
										base_form.findField('LpuRegion_id').getStore().load({
											params: {
												add_without_region_line: 1
											}
										});
									}
								}
							},
	   						xtype: 'swlpulocalcombo'
						}, {
							enableKeyEvents: true,
							hiddenName : "LpuAttachType_id",
							listeners: {
								'keydown': function (inp, e) {
		                            if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
		                            {
		 								e.stopEvent();
										inp.ownerCt.getForm().findField("LpuRegionType_id").focus();
									}
								},
								'change': function(combo, newValue, oldValue) {
									var
										base_form = this.ownerCt.getForm(),
										lpu_region_type_combo = base_form.findField('LpuRegionType_id'),
										lpu_region_type_id = lpu_region_type_combo.getValue();

									lpu_region_type_combo.clearValue();
									lpu_region_type_combo.getStore().clearFilter();

									if ( newValue ) {
										var LpuRegionTypeArray = [];

										switch ( newValue ) {
											case 1:
												LpuRegionTypeArray = [ 'ter', 'ped', 'vop' ];

												if ( getRegionNick() == 'perm' ) {
													LpuRegionTypeArray = [ 'ter', 'ped', 'vop', 'comp', 'prip', 'feld' ];
												}
											break;

											case 2:
												LpuRegionTypeArray = [ 'gin' ];
											break;

											case 3:
												LpuRegionTypeArray = [ 'stom' ];
											break;

											case 4:
												LpuRegionTypeArray = [ 'slug' ];
											break;
										}

										lpu_region_type_combo.getStore().filterBy(function(rec) {
											return (!Ext.isEmpty(rec.get('LpuRegionType_SysNick')) && rec.get('LpuRegionType_SysNick').inlist(LpuRegionTypeArray));
										});
									}

									var record = lpu_region_type_combo.getStore().getById(lpu_region_type_id);

									if ( newValue != 4 || getRegionNick() != 'ufa' ) {
										if ( record ) {
											lpu_region_type_combo.setValue(lpu_region_type_id);
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_id, null);
										}
										else if ( lpu_region_type_combo.getStore().getCount() == 1 ) {
											lpu_region_type_combo.setValue(lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'));
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'), null);
										}
										else {
											lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
										}
									}
									else {
										lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
									}
								}
							},
							tabIndex: 2208,
							value: 1,
							width: 400,
							xtype : "swlpuattachtypecombo"
		                }, {
							enableKeyEvents: true,
							hiddenName : "LpuRegionType_id",
							listeners: {
								'keydown': function (inp, e) {
		                            if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
		                            {
		 								e.stopEvent();
										inp.ownerCt.getForm().findField("LpuRegion_id").focus();
									}
								},
								'change': function(combo, lpuRegionTypeId) {
									var index = combo.getStore().findBy(function(rec) {
										return rec.get(combo.valueField) == lpuRegionTypeId;
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									var
										base_form = this.ownerCt.getForm(),
										lpu_attach_type_id = base_form.findField('LpuAttachType_id').getValue(),
										lpu_region_combo = base_form.findField('LpuRegion_id'),
										lpu_region_id = lpu_region_combo.getValue();

									lpu_region_combo.clearValue();
									lpu_region_combo.getStore().clearFilter();

									var regionTypeList = new Array();

									if ( !Ext.isEmpty(lpu_attach_type_id) ) {
										var LpuRegionTypeArray = [];

										switch ( lpu_attach_type_id ) {
											case 1:
												LpuRegionTypeArray = [ 'ter', 'ped', 'vop' ];

												if ( getRegionNick() == 'perm' ) {
													LpuRegionTypeArray = [ 'ter', 'ped', 'vop', 'comp', 'prip', 'feld' ];
												}
											break;

											case 2:
												LpuRegionTypeArray = [ 'gin' ];
											break;

											case 3:
												LpuRegionTypeArray = [ 'stom' ];
											break;

											case 4:
												LpuRegionTypeArray = [ 'slug' ];
											break;
										}
									}

									lpu_region_combo.getStore().filterBy(function(rec) {
										if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
											return (rec.get('LpuRegionType_id') == record.get(combo.valueField));
										}
										else if ( !Ext.isEmpty(lpu_attach_type_id) ) {
											if ( rec.get('LpuRegion_id') == -1 ) {
												return true;
											}

											if ( LpuRegionTypeArray.length > 0 )  {
												return (!Ext.isEmpty(rec.get('LpuRegionType_SysNick')) && rec.get('LpuRegionType_SysNick').inlist(LpuRegionTypeArray));
											}
										}

										return false;
									});
								}
							},
							tabIndex: 2209,
							width: 400,
							xtype : "swlpuregiontypecombo"
		                }, 				 
						{
							listeners: {
								'keydown': function (inp, e) {
		                            if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
		                            {
		 								e.stopEvent();
										inp.ownerCt.getForm().findField("LpuRegionType_id").focus();
									}
								}
							},
							mode: 'local',
							tabIndex: 2201,
							width : 400,
							xtype: 'swlpuregioncombo'
						}, {
						  xtype: 'swlpumotioncombo',
						  name: 'LpuMotion_id',
						  tabIndex: 2201,
						  width: 400
						 },
						{
							allowBlank: true,
							fieldLabel: lang['prikrepilsya_iz'],
							hiddenName: 'FromLpu_id',
							tabIndex: 2202,
							width: 400,
	   						xtype: 'swlpulocalcombo'
						},
						{
							allowBlank: true,
							fieldLabel: lang['otkrepilsya_v'],
							hiddenName: 'ToLpu_id',
							tabIndex: 2202,
							width: 400,
	   						xtype: 'swlpulocalcombo'
						},
						{
							allowBlank: false,
							fieldLabel : lang['period'],
							name : "Period",
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							tabIndex: 2202,
							width : 240,
							xtype : "daterangefield"
						}
					],
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							Ext.getCmp('PersonCardStateViewWindow').doSearch();
						},
						stopEvent: true
					}],
					labelAlign: 'right',
					region: 'north'
				}),
				new Ext.grid.GridPanel({
					region: 'center',
					id: 'PersonCardStateViewGrid',
					autoExpandColumn: 'autoexpand',
//					autoExpandMax: 2000,
					autoExpandMin: 140,
					enableColumnMove: false,
			       	stripeRows: true,
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: '?c=PersonCard&m=getPersonCardStateGrid',
						fields: [
							'LpuRegionType_id',
							'LpuRegion_id',
							'LpuRegionType_Name',
							'LpuRegion_Name',
							{
								name: 'StartDate',
								dateFormat: 'd.m.Y',
								type: 'date'
							},
							{
								name: 'EndDate',
								dateFormat: 'd.m.Y',
								type: 'date'
							},
							'BegCount',
							'BegCountBDZ',
							'BegCountNotInBDZ',
							'AttachCount',
							'AttachIncomeBDZ',
							'AttachOutcomeBDZ',
							'AttachCountBDZ',
							'DettachCount',
							'DettachCountBDZ',
							'EndCount',
							'EndCountBDZ',
							'EndCountNotInBDZ'
						]
					}),
					columns: [
                                              
						{dataIndex: 'LpuRegionType_id', hidden: true, hideable: false},
						{dataIndex: 'LpuRegion_id', hidden: true, hideable: false},
						{dataIndex: 'LpuMovBdz_id', hidden: true, hideable: false},
						{id: 'autoexpand', header: lang['tip_uchastka'], dataIndex: 'LpuRegionType_Name', sortable: true, width: 100, fixed: true},
						{header: lang['uchastok'], dataIndex: 'LpuRegion_Name', sortable: true, width: 100, fixed: true},
						{header: lang['sost_na_nachalo'], dataIndex: 'BegCount', sortable: true, width: 100, fixed: true},
                        {header: lang['sost_na_nachalo_v_t_ch_zastrahovano_po_oms'], dataIndex: 'BegCountBDZ', sortable: true, width: 250, fixed: true},
						{header: lang['costoit_na_nachalo_nezastrahovano_po_oms'], dataIndex: 'BegCountNotInBDZ', sortable: true, width: 250, fixed: true},
						{header: lang['prikrepleno'], dataIndex: 'AttachCount', sortable: true, width: 100, fixed: true, hidden: true, hiddable: false},
                        {header: lang['prikrepleno_v_t_ch_zastrahovano_po_oms'], dataIndex: 'AttachCountBDZ', sortable: true, width: 180, fixed: true, hidden: true, hiddable: false},
						{header: lang['zastrahovano_po_oms'], dataIndex: 'AttachIncomeBDZ', sortable: true, width: 180, fixed: true, hidden: true, hiddable: false},
						{header: lang['nezastrahovano_po_oms'], dataIndex: 'AttachOutcomeBDZ', sortable: true, width: 180, fixed: true, hidden: true, hiddable: false},
						{header: lang['otkrepleno'], dataIndex: 'DettachCount', sortable: true, width: 100, fixed: true, hidden: true, hiddable: false},
                        {header: lang['otkrepleno_v_t_ch_zastrahovano_po_oms'], dataIndex: 'DettachCountBDZ', sortable: true, width: 180, fixed: true, hidden: true, hiddable: false},
						{header: lang['sost_na_konets'], dataIndex: 'EndCount', sortable: true, width: 100, fixed: true},
                        {header: lang['sost_na_konets_v_t_ch_zastrahovano_po_oms'], dataIndex: 'EndCountBDZ', sortable: true, width: 250, fixed: true},
						{header: lang['costoit_na_konets_nezastrahovano_po_oms'], dataIndex: 'EndCountNotInBDZ', sortable: true, width: 250, fixed: true}
					],
					sm: new Ext.grid.CellSelectionModel({
						singleSelect: true
					}),
					listeners: {
						celldblclick : function( grid, rowIndex, columnIndex, e )
						{
                        	var wnd = grid.ownerCt;
							wnd.viewPersonCardStateDetails();
						}
					},
                    keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							var wnd = Ext.getCmp('PersonCardStateViewWindow');
							wnd.viewPersonCardStateDetails();
						},
						stopEvent: true
                    }],
					tabIndex: 2203,
					title: lang['jurnal_dvijeniya_po_kartoteke']
				})
			]
		});
        sw.Promed.swPersonCardStateViewWindow.superclass.initComponent.apply(this, arguments);
	},
    keys: [{
    	alt: true,
        fn: function(inp, e) {
        	var current_window = Ext.getCmp('PersonCardStateViewWindow');
        	switch (e.getKey())
        	{
        		case Ext.EventObject.J:
        			current_window.hide();
        		break;
				case Ext.EventObject.C:
        			current_window.doResetAll();
        		break;
        	}
        },
        key: [ Ext.EventObject.J, Ext.EventObject.C ],
        stopEvent: true
    }],
    layout: 'border',
	loadListsAndFormData: function() {
		var current_window = this;
		var form = this.findById('PersonCardStateViewFilterForm');
		form.getForm().findField('LpuRegion_id').clearValue();
  		this.setLpuId();
	},
    maximizable: true,
    minHeight: 550,
    minWidth: 900,
    modal: false,
    plain: true,
	personSearchWindow: null,
	personCardEditWindow: null,
	printPersonCardStateGrid: function() {
		var grid = this.findById('PersonCardStateViewGrid');
		Ext.ux.GridPrinter.print(grid);
	},
	printPersonCardStateRecord: function() {
		var grid = this.findById('PersonCardStateViewGrid'),
            rec = grid.getSelectionModel().getSelected();

        if (!rec) return true;
		var params = {
			rowId: rec.id
		};
		//Ext.ux.GridPrinter.print(grid, rec.id);
		Ext.ux.GridPrinter.print(grid, params);
	},
    resizable: true,
	refreshPersonCardStateViewGrid: function() {
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
//		this.findById('PersonCardViewFilterForm').getForm().findField('Person_SurName').focus(true, 100);
	},
	show: function() {
		sw.Promed.swPersonCardStateViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var form = this.findById('PersonCardStateViewFilterForm');

		this.doResetAll();

//        this.enableEdit(true);
		this.loadListsAndFormData();

		form.getForm().findField('LpuRegionType_id').focus(true, 500);
	},
	title: WND_POL_PERSCARDSTATEVIEW,
	viewPersonCardStateDetails: function() {
		var grid = this.findById('PersonCardStateViewGrid');
		var form = this.findById('PersonCardStateViewFilterForm');
		var selectedCell = grid.getSelectionModel().getSelectedCell();
		if ( !selectedCell )
			return;		
		var rowIndex = selectedCell[0];
		var columnIndex = selectedCell[1];
		// по Итого не считаем
		if ( rowIndex >= (this.findById('PersonCardStateViewGrid').getStore().getCount() - 1) )
			return false;
		var current_window = this;
		var columnIndex = grid.getColumnModel().getDataIndex(columnIndex);
		if ( columnIndex != 'AttachCount' && columnIndex != 'EndCount' && columnIndex != 'EndCountBDZ' && columnIndex != 'EndCountNotInBDZ' && columnIndex != 'BegCount' && columnIndex != 'BegCountBDZ' && columnIndex != 'BegCountNotInBDZ' && columnIndex != 'DettachCount' && columnIndex != 'DettachCountBDZ' && columnIndex != 'AttachCountBDZ' && columnIndex != 'AttachIncomeBDZ' && columnIndex != 'AttachOutcomeBDZ' )
			return false;
		getWnd('swPersonCardStateDetailViewWindow').show({
			LpuAttachType_id: Number(form.getForm().findField('LpuAttachType_id').getValue()),
			LpuRegion_id: grid.getStore().getAt(rowIndex).data.LpuRegion_id,
			LpuMotion_id: form.getForm().findField('LpuMotion_id').getValue(),
			FromLpu_id: form.getForm().findField('FromLpu_id').getValue(),
			ToLpu_id: form.getForm().findField('ToLpu_id').getValue(),
			mode: columnIndex,
			StartDate: Ext.util.Format.date(grid.getStore().getAt(rowIndex).data.StartDate, 'd.m.Y'),
			EndDate: Ext.util.Format.date(grid.getStore().getAt(rowIndex).data.EndDate, 'd.m.Y'),
			onHide: function() {
    			//current_window.doSearch();
			}
		});
	},
    width: 900
});