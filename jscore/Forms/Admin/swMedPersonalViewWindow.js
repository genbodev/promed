/**
* swMedPersonalViewWindow - окно просмотра и редактирования мед. персонала.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      04.03.2009
*/

function FindMedPersonal() {
	var field = Ext.getCmp('MedPersonalGrid').getTopToolbar().items.item('MPVW_FIOQuickFind');
	var exp = String(field.getValue()).toUpperCase();
	var grid = field.ownerCt.ownerCt;
	grid.getStore().filter('MedPersonal_FIO', new RegExp('^'+exp, "i"));
	grid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + grid.getStore().getCount();
	field.focus();
}

var tm = null;

sw.Promed.swMedPersonalViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	layout: 'border',
	maximized: true,
    modal: true,
	resizable: false,
	draggable: false,
    closeAction :'hide',
    plain       : true,
    title: lang['meditsinskiy_personal_staryiy_ermp'],//lang['meditsinskiy_personal'],
	id: 'MedPersonalViewWindow',
	show: function() {
		sw.Promed.swMedPersonalViewWindow.superclass.show.apply(this, arguments);
		Ext.getCmp('MPVW_FIOQuickFind').setValue('');
		var grid = this.findById('MedPersonalGrid');
		grid.getStore().load({callback: function() {if ( grid.getStore().getCount() > 0 ) {grid.getView().focusRow(0); grid.getSelectionModel().selectFirstRow();}}});
	},
	onEditSuccess: function(scope, MedPersonal_id) {
		var DetailGrid = scope.findById('med_personal_grid_detail');
		DetailGrid.getStore().removeAll();
        DetailGrid.getStore().load({
        	params: {
            	MedPersonal_id: MedPersonal_id
			}
		});
	},
	initComponent: function() {

		Ext.apply(this, {
			items: [
				new Ext.grid.GridPanel({
					autoExpandColumn: 'autoexpand',
					border: false,
					region: 'west',
					width: 365,
					split: true,
					title: lang['spisok_meditsinskogo_personala'],
					id: 'MedPersonalGrid',
					autoExpandMax: 2000,
					loadMask: true,
			        stripeRows: true,
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
//	                        Ext.EventObject.G,
//	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
	                        Ext.EventObject.INSERT,
	                        Ext.EventObject.TAB,
	    					Ext.EventObject.PAGE_UP,
	    					Ext.EventObject.PAGE_DOWN
//	    					Ext.EventObject.HOME,
//	    					Ext.EventObject.END
	                    ],
	                    fn: function(inp, e) {
	                    	e.stopEvent();

	                        if ( e.browserEvent.stopPropagation )
	                            e.browserEvent.stopPropagation();
	                        else
	                            e.browserEvent.cancelBubble = true;

	                        if ( e.browserEvent.preventDefault )
	                            e.browserEvent.preventDefault();
	                        else
	                            e.browserEvent.returnValue = false;

	                        e.browserEvent.returnValue = false;
	                        e.returnValue = false;

	                        if (Ext.isIE)
	                        {
	                            e.browserEvent.keyCode = 0;
	                            e.browserEvent.which = 0;
	                        }

	                    	var current_window = Ext.getCmp('MedPersonalViewWindow');
							var mpersonal_grid = current_window.findById('MedPersonalGrid');
							var detail_grid = current_window.findById('med_personal_grid_detail');
	                        switch (e.getKey())
	                        {
	                            case Ext.EventObject.END:
                                break;

	                            case Ext.EventObject.G:
/*									if (e.altKey)
									{
										mpersonal_grid.getTopToolbar().items.items[2].handler();
									}*/
                                break;

	                            case Ext.EventObject.ENTER:
	                        	case Ext.EventObject.F4:
								break;
	                        	case Ext.EventObject.F3:
								break;

	                        	case Ext.EventObject.F5:

                        	    break;

	                            case Ext.EventObject.HOME:
                                break;

	                        	case Ext.EventObject.INSERT:
	                       	    	detail_grid.getTopToolbar().items.items[0].handler();
								break;

	                        	case Ext.EventObject.DELETE:
								break;

	                            case Ext.EventObject.PAGE_DOWN:
                                break;

	                            case Ext.EventObject.PAGE_UP:
                                break;

	                        	case Ext.EventObject.TAB:
									if ( e.shiftKey )
									{
										mpersonal_grid.getTopToolbar().items.item('MPVW_FIOQuickFind').focus();
									}
									else
	                            	    if (detail_grid.getStore().getCount() > 0 && detail_grid.getSelectionModel().getSelected())
	                            	    {
	                            	    	var selected_record = detail_grid.getSelectionModel().getSelected();
	                            	    	var index = detail_grid.getStore().findBy(function(rec) { return rec.get('MedStaffFact_id') == selected_record.data.MedStaffFact_id; });
	                            	        detail_grid.getView().focusRow(index);

	                                    	if (index == 0)
		                                    {
	    	                                    detail_grid.getSelectionModel().selectFirstRow();
	        	                            }
										}
										else
											if ( detail_grid.getStore().getCount() > 0 )
											{
												detail_grid.getSelectionModel().selectFirstRow();
												detail_grid.getView().focusRow(0);
											}
	                       	    break;
	                        }
	                    },
	                    stopEvent: true
	                }],
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: C_MP_GRID,
						fields: [
							'MedPersonal_id',
							'MedPersonal_TabCode',
							'MedPersonal_FIO',
							'MedPersonal_DloCode'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('MedPersonalGrid');
								grid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + store.getCount();
							}
						}
					}),
					columns: [
						{dataIndex: 'MedPersonal_id', hidden: true, hideable: false},
						{header: lang['kod_vracha'], dataIndex: 'MedPersonal_TabCode', width: 70, sortable: true},
						{id: 'autoexpand', header: lang['fio_vracha'], dataIndex: 'MedPersonal_FIO', sortable: true},
						{header: lang['kod_llo'], dataIndex: 'MedPersonal_DloCode', sortable: true, width: 60}
					],
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'label',
							text: lang['filtr_fio'],
							style: 'margin-left: 5px; font-weight: bold'
						}, {
	                    	xtype: 'textfield',
							id: 'MPVW_FIOQuickFind',
							style: 'margin-left: 5px',
							enableKeyEvents: true,
							listeners: {
								'keyup': function(field, e) {
									if (e.getKey() == Ext.EventObject.DELETE)
										return true;
									if (tm) clearTimeout(tm);
									tm = setTimeout("FindMedPersonal()", 1000);
								},
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.DELETE)
										return true;
									var current_window = Ext.getCmp('MedPersonalViewWindow');
									var mpersonal_grid = current_window.findById('MedPersonalGrid');
									var detail_grid = current_window.findById('med_personal_grid_detail');
                                    if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB )
                                    {
										e.stopEvent();
										if ( mpersonal_grid.getStore().getCount() > 0 )
										{
											mpersonal_grid.getView().focusRow(0);
   	        	                            mpersonal_grid.getSelectionModel().selectFirstRow();
										}
									}
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB )
									{
 										e.stopEvent();
										if ( detail_grid.getStore().getCount() > 0 )
										{
											detail_grid.getView().focusRow(0);
   	        	                            detail_grid.getSelectionModel().selectFirstRow();
										}
									}
								}
							}
						},
						{
							iconCls: 'print16',
							text: lang['pechat'],
							minWidth: 90,
							disabled: false,
							handler: function() {
								var grid = this.ownerCt.ownerCt;
								Ext.ux.GridPrinter.print(grid);
							}
						}, {
                        	xtype: 'tbfill'
                        }, {
                           	text: '0 / 0',
                           	xtype: 'tbtext'
                        }]
					}),
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								this.grid.ownerCt.findById('med_personal_grid_detail').getStore().removeAll();
                            	this.grid.ownerCt.findById('med_personal_grid_detail').getStore().load({
                            		params: {
                            			MedPersonal_id: r.data.MedPersonal_id
									}
								});
								this.grid.getTopToolbar().items.items[4].el.innerHTML = (rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
								//this.grid.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[0].enable();
								this.grid.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[1].disable();
								this.grid.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[2].disable();
								this.grid.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[3].disable();
								this.grid.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[4].enable();
							}
						}
					})
				}),
				new Ext.grid.GridPanel({
					region: 'center',
					border: false,
					height: 200,
					id: 'med_personal_grid_detail',
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					loadMask: true,
					title: lang['mesto_rabotyi_meditsinskogo_personala'],
       				stripeRows: true,
					enableKeyEvents: true,
					keys: [{
	                    key: [
	                        Ext.EventObject.ENTER,
	                        Ext.EventObject.DELETE,
	                        Ext.EventObject.F3,
	                        Ext.EventObject.F4,
	                        Ext.EventObject.F5,
							Ext.EventObject.F6,
	                        Ext.EventObject.INSERT,
	                        Ext.EventObject.TAB,
//	    					Ext.EventObject.G,
	    					Ext.EventObject.PAGE_UP,
	    					Ext.EventObject.PAGE_DOWN,
	    					Ext.EventObject.HOME,
	    					Ext.EventObject.END
	                    ],
	                    fn: function(inp, e) {
	                    	e.stopEvent();

	                        if ( e.browserEvent.stopPropagation )
	                            e.browserEvent.stopPropagation();
	                        else
	                            e.browserEvent.cancelBubble = true;

	                        if ( e.browserEvent.preventDefault )
	                            e.browserEvent.preventDefault();
	                        else
	                            e.browserEvent.returnValue = false;

	                        e.browserEvent.returnValue = false;
	                        e.returnValue = false;

	                        if (Ext.isIE)
	                        {
	                            e.browserEvent.keyCode = 0;
	                            e.browserEvent.which = 0;
	                        }

	                    	var current_window = Ext.getCmp('MedPersonalViewWindow');
							var mpersonal_grid = current_window.findById('MedPersonalGrid');
							var detail_grid = current_window.findById('med_personal_grid_detail');
	                        switch (e.getKey())
	                        {
	                            case Ext.EventObject.END:
                                break;

	                            case Ext.EventObject.ENTER:
	                        	case Ext.EventObject.F4:
                        	    	detail_grid.getTopToolbar().items.items[1].handler();
								break;
	                        	case Ext.EventObject.F3:
                        	    	detail_grid.getTopToolbar().items.items[2].handler();
								break;
	                        	case Ext.EventObject.F5:
									detail_grid.getStore().reload();
                        	    break;
								case Ext.EventObject.F6:
									if (e.altKey) {
										var selected_record = detail_grid.getSelectionModel().getSelected();
										AddRecordToUnion(
											selected_record,
											'MedStaffFact',
											lang['mesto_rabotyi_vracha'],
											function () {
												detail_grid.getStore().reload();
											}
										)
									}
								break;

	                            case Ext.EventObject.HOME:
                                break;

	                        	case Ext.EventObject.INSERT:
	                       	    	detail_grid.getTopToolbar().items.items[0].handler();
								break;

	                        	case Ext.EventObject.DELETE:
                        	    	detail_grid.getTopToolbar().items.items[3].handler();
								break;

	                            case Ext.EventObject.G:
//									if ( e.altKey )
//										detail_grid.getTopToolbar().items.items[4].handler();
                                break;

	                            case Ext.EventObject.PAGE_DOWN:
                                break;

	                            case Ext.EventObject.PAGE_UP:
                                break;

	                        	case Ext.EventObject.TAB:

                            	    if (mpersonal_grid.getStore().getCount() > 0 && mpersonal_grid.getSelectionModel().getSelected())
                            	    {
                            	    	var selected_record = mpersonal_grid.getSelectionModel().getSelected();
                            	    	var index = mpersonal_grid.getStore().findBy(function(rec) { return rec.get('MedPersonal_id') == selected_record.data.MedPersonal_id; });
                                    	mpersonal_grid.getView().focusRow(index);
	                                    if (index == 0)
    	                                {
        	                                mpersonal_grid.getSelectionModel().selectFirstRow();
            	                        }
										detail_grid.getSelectionModel().clearSelections();
	                        	    	detail_grid.getTopToolbar().items.items[1].disable();
	                        	    	detail_grid.getTopToolbar().items.items[2].disable();
	                        	    	detail_grid.getTopToolbar().items.items[3].disable();
	                        	    	detail_grid.getTopToolbar().items.items[4].enable();
									}
									else
										if ( mpersonal_grid.getStore().getCount() > 0 )
										{
											mpersonal_grid.getSelectionModel().selectFirstRow();
											mpersonal_grid.getView().focusRow(0);
											detail_grid.getSelectionModel().clearSelections();
		                        	    	detail_grid.getTopToolbar().items.items[1].disable();
		                        	    	detail_grid.getTopToolbar().items.items[2].disable();
		                        	    	detail_grid.getTopToolbar().items.items[3].disable();
		                        	    	detail_grid.getTopToolbar().items.items[4].enable();
										}
	                       	    break;
	                        }
	                    },
	                    stopEvent: true
	                }],
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						buttons: [
						{
							text: BTN_GRIDADD,
							iconCls: 'add16',
							minWidth: 90,
							disabled: true,
							handler: function() {/*
								var window = this.ownerCt.ownerCt.ownerCt;
								var MedPersonal_id = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_id;
								var MedPersonal_TabCode = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_TabCode;
								var MedPersonal_FIO = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_FIO;
								var callback = window.onEditSuccess;
								getWnd('swMedStaffFactEditWindow').show({
									onClose: function()
									{
										if ( window.findById('med_personal_grid_detail').getStore().getCount() > 0 )
										{
											window.findById('med_personal_grid_detail').getSelectionModel().selectFirstRow();
											window.findById('med_personal_grid_detail').getView().focusRow(0);
										}
										else
										{
					                    	var current_window = window;
											var mpersonal_grid = current_window.findById('MedPersonalGrid');
											var detail_grid = current_window.findById('med_personal_grid_detail');
		                            	    if (mpersonal_grid.getStore().getCount() > 0 && mpersonal_grid.getSelectionModel().getSelected())
		                            	    {
		                            	    	var selected_record = mpersonal_grid.getSelectionModel().getSelected();
		                            	    	var index = mpersonal_grid.getStore().findBy(function(rec) { return rec.get('MedPersonal_id') == selected_record.data.MedPersonal_id; });
		                                    	mpersonal_grid.getView().focusRow(index);
			                                    if (index == 0)
		    	                                {
		        	                                mpersonal_grid.getSelectionModel().selectFirstRow();
		            	                        }
												detail_grid.getSelectionModel().clearSelections();
			                        	    	detail_grid.getTopToolbar().items.items[1].disable();
			                        	    	detail_grid.getTopToolbar().items.items[2].disable();
			                        	    	detail_grid.getTopToolbar().items.items[3].disable();
			                        	    	detail_grid.getTopToolbar().items.items[4].enable();
											}
											else
												if ( mpersonal_grid.getStore().getCount() > 0 )
												{
													mpersonal_grid.getSelectionModel().selectFirstRow();
													mpersonal_grid.getView().focusRow(0);
													detail_grid.getSelectionModel().clearSelections();
				                        	    	detail_grid.getTopToolbar().items.items[1].disable();
				                        	    	detail_grid.getTopToolbar().items.items[2].disable();
				                        	    	detail_grid.getTopToolbar().items.items[3].disable();
				                        	    	detail_grid.getTopToolbar().items.items[4].enable();
												}
										}
									},
									callback: callback,
									owner: window,
									fields: {
										MedPersonal_id: MedPersonal_id,
										MedPersonal_TabCode: MedPersonal_TabCode,
										MedPersonal_FIO: MedPersonal_FIO,
										action: 'add'
									}
								});
							*/}
						},
						{
							iconCls: 'edit16',
							text: BTN_GRIDEDIT,
							minWidth: 90,
							disabled: true,
							handler: function() {/*
								var window = this.ownerCt.ownerCt.ownerCt;
								if ( !window.findById('med_personal_grid_detail').getSelectionModel().getSelected() )
									return;
								var MedPersonal_id = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_id;
								var MedPersonal_TabCode = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_TabCode;
								var MedPersonal_FIO = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_FIO;
								var MedStaffFact_id = window.findById('med_personal_grid_detail').getSelectionModel().getSelected().data.MedStaffFact_id;
								var callback = window.onEditSuccess;
								getWnd('swMedStaffFactEditWindow').show({
									onClose: function()
									{
										if ( window.findById('med_personal_grid_detail').getStore().getCount() > 0 )
										{
											window.findById('med_personal_grid_detail').getSelectionModel().selectFirstRow();
											window.findById('med_personal_grid_detail').getView().focusRow(0);
										}
									},
									callback: callback,
									owner: window,
									fields: {
										MedPersonal_id:	MedPersonal_id,
										MedPersonal_TabCode: MedPersonal_TabCode,
										MedPersonal_FIO: MedPersonal_FIO,
										MedStaffFact_id: MedStaffFact_id,
										action: 'edit'
									}
								});
							*/}
						},
						{
							iconCls: 'view16',
							text: BTN_GRIDVIEW,
							minWidth: 90,
							disabled: true,
							handler: function() {
								var window = this.ownerCt.ownerCt.ownerCt;
								if ( !window.findById('med_personal_grid_detail').getSelectionModel().getSelected() )
									return;
								var MedPersonal_id = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_id;
								var MedPersonal_TabCode = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_TabCode;
								var MedPersonal_FIO = window.findById('MedPersonalGrid').getSelectionModel().getSelected().data.MedPersonal_FIO;
								var MedStaffFact_id = window.findById('med_personal_grid_detail').getSelectionModel().getSelected().data.MedStaffFact_id;
								getWnd('swMedStaffFactEditWindow').show({
									onClose: function()
									{
										if ( window.findById('med_personal_grid_detail').getStore().getCount() > 0 )
										{
											window.findById('med_personal_grid_detail').getSelectionModel().selectFirstRow();
											window.findById('med_personal_grid_detail').getView().focusRow(0);
										}
									},
									fields: {
										MedPersonal_id: MedPersonal_id,
										MedPersonal_TabCode: MedPersonal_TabCode,
										MedPersonal_FIO: MedPersonal_FIO,
										MedStaffFact_id: MedStaffFact_id,
										action: 'edit',
										readOnly: true
									}
								});
							}
						},
						{
							iconCls: 'delete16',
							text: BTN_GRIDDEL,
							minWidth: 90,
							disabled: true,
							handler: function() {/*
								var window = this.ownerCt.ownerCt.ownerCt;
								if ( !window.findById('med_personal_grid_detail').getSelectionModel().getSelected() )
									return;
								var MedStaffFact_id = window.findById('med_personal_grid_detail').getSelectionModel().getSelected().data.MedStaffFact_id;
								var MedPersonal_id = window.findById('med_personal_grid_detail').getSelectionModel().getSelected().data.MedPersonal_id;
								var MedStaffStore = window.findById('med_personal_grid_detail').getStore();
								if ( MedStaffFact_id == '')
								{
									Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_mesto_rabotyi']);
									return 0;
								}
								sw.swMsg.show({
									title: lang['podtverjdenie_udaleniya'],
									msg: lang['vyi_deystvitelno_jelaete_udalit_eto_mesto_rabotyi'],
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' )
										{
											var MedPersonalStaff_id = 0;
											Ext.Ajax.request({
												url: C_MSF_DROP,
												params: {MedStaffFact_id: MedStaffFact_id},
												callback: function() {
													MedStaffStore.removeAll();
                                                            	MedStaffStore.load({
                                                            		params: {MedPersonal_id: MedPersonal_id},
																	callback: function() {
												                    	var current_window = window;
																		var mpersonal_grid = current_window.findById('MedPersonalGrid');
																		var detail_grid = current_window.findById('med_personal_grid_detail');
																		if ( window.findById('med_personal_grid_detail').getStore().getCount() > 0 )
																			{
																				window.findById('med_personal_grid_detail').getSelectionModel().selectFirstRow();
																				window.findById('med_personal_grid_detail').getView().focusRow(0);
																			}
																		else
																			{
														                    	var current_window = window;
																				var mpersonal_grid = current_window.findById('MedPersonalGrid');
																				var detail_grid = current_window.findById('med_personal_grid_detail');
											                            	    if (mpersonal_grid.getStore().getCount() > 0 && mpersonal_grid.getSelectionModel().getSelected())
											                            	    {
											                            	    	var selected_record = mpersonal_grid.getSelectionModel().getSelected();
											                            	    	var index = mpersonal_grid.getStore().findBy(function(rec) { return rec.get('MedPersonal_id') == selected_record.data.MedPersonal_id; });
											                                    	mpersonal_grid.getView().focusRow(index);
												                                    if (index == 0)
											    	                                {
											        	                                mpersonal_grid.getSelectionModel().selectFirstRow();
											            	                        }
																					detail_grid.getSelectionModel().clearSelections();
												                        	    	detail_grid.getTopToolbar().items.items[1].disable();
												                        	    	detail_grid.getTopToolbar().items.items[2].disable();
												                        	    	detail_grid.getTopToolbar().items.items[3].disable();
												                        	    	detail_grid.getTopToolbar().items.items[4].enable();
																				}
																				else
																					if ( mpersonal_grid.getStore().getCount() > 0 )
																					{
																						mpersonal_grid.getSelectionModel().selectFirstRow();
																						mpersonal_grid.getView().focusRow(0);
																						detail_grid.getSelectionModel().clearSelections();
													                        	    	detail_grid.getTopToolbar().items.items[1].disable();
													                        	    	detail_grid.getTopToolbar().items.items[2].disable();
													                        	    	detail_grid.getTopToolbar().items.items[3].disable();
													                        	    	detail_grid.getTopToolbar().items.items[4].enable();
																					}
																			}
																	}
                                                            	});
												}
											});
										}
									}
								});
							*/}
						},
						{
							iconCls: 'print16',
							text: lang['pechat'],
							hidden: true,
							minWidth: 90,
							disabled: false,
							handler: function() {
								var grid = this.ownerCt.ownerCt.ownerCt.findById('med_personal_grid_detail');
								Ext.ux.GridPrinter.print(grid);
							}
						}, {
							handler: function() {
								var window = this.ownerCt.ownerCt.ownerCt;
								window.findById('med_personal_grid_detail').getStore().reload();
						},
							iconCls: 'refresh16',
							text: BTN_GRIDREFR
						}, {
   	                    	xtype: 'tbfill'
       	                }, {
           	               	text: '0 / 0',
               	           	xtype: 'tbtext'
                   	    }]
					}),
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: '/?c=MedPersonal&m=getMedStaffGridDetail_Ufa_Old_ERMP',
						fields: [
							'MedPersonal_id',
							'MedStaffFact_id',
							'MedPersonal_TabCode',
							'MedPersonal_FIO',
							'PostMed_Name',
							'LpuSection_Name',
							'MedStaffFact_Stavka',
							{name: 'MedStaffFact_setDate', type: 'date', dateFormat:'d.m.Y'},
							{name: 'MedStaffFact_disDate', type: 'date', dateFormat:'d.m.Y'},
							'LpuUnit_Name'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('med_personal_grid_detail');
								grid.getTopToolbar().items.items[7].el.innerHTML = '0 / ' + store.getCount();
							}
						}
					}),
					columns: [
						{dataIndex: 'MedPersonal_id', hidden: true, hideable: false},
						{dataIndex: 'MedStaffFact_id', hidden: true, hideable: false},
						{id: 'autoexpand', header: lang['doljnost'], dataIndex: 'PostMed_Name', sortable: true, width: 250},
						{header: lang['podrazdelenie'], dataIndex: 'LpuUnit_Name', sortable: true, width: 250},
						{header: lang['stavka'], dataIndex: 'MedStaffFact_Stavka', sortable: true, width: 50},
						{header: lang['nachalo'], dataIndex: 'MedStaffFact_setDate', sortable: true, width: 70, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{header: lang['okonchanie'], dataIndex: 'MedStaffFact_disDate', sortable: true, width: 70, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{header: lang['otdelenie'], dataIndex: 'LpuSection_Name', sortable: true, width: 130}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								this.grid.getTopToolbar().items.items[7].el.innerHTML = (rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
								//this.grid.ownerCt.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[0].enable();
								//this.grid.ownerCt.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[1].enable();
								this.grid.ownerCt.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[2].enable();
								//this.grid.ownerCt.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[3].enable();
								this.grid.ownerCt.ownerCt.findById('med_personal_grid_detail').getTopToolbar().items.items[4].enable();
							}
						}
					}),
					listeners: {
						'rowdblclick': function() {
							this.getTopToolbar().items.items[1].handler();
						}
					}
				})
			],
			buttonAlign : "right",
			buttons: [
				HelpButton(this),
				{
					text: lang['zakryit'],
			        iconCls: 'close16',
	                handler: function() { this.ownerCt.hide() }
				}
				],
			enableKeyEvents: true,
		    keys: [{
		    	alt: true,
		        fn: function(inp, e) {
		        	if (e.altKey) {
		        		Ext.getCmp('MedPersonalViewWindow').hide();
		        	}
		        	else {
		        		return true;
		        	}
		        },
		        key: [ Ext.EventObject.P ],
		        stopEvent: false
		    }]
		});
		
    	sw.Promed.swMedPersonalViewWindow.superclass.initComponent.apply(this, arguments);
    }
});