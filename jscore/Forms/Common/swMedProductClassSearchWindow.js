/**
* swMedProductClassSearchWindow - окно поиска класса медицинского изделия.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Samir Abakhri
* @version      03.07.2014
*/

sw.Promed.swMedProductClassSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction: 'hide',
	disableAllActions: function(disable) {
		if ( (disable === true) || (disable == undefined) ) {
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[0].disable();
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[1].disable();
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[2].disable();
		}
		else {
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[0].enable();
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[1].enable();
			this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[2].enable();
		}
	},
	doReset: function() {
		this.findById('MPCSW_MedProductClassSearchGrid').getStore().removeAll();
		this.findById('MedProductClassSearchForm').getForm().reset();
		this.findById('MPCSW_MedProductClass_Name').focus(true, 250);
		this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[1].disable();
		this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[2].disable();
		this.findById('MPCSW_MedProductClassSearchGrid').getTopToolbar().items.items[6].el.innerHTML = '0 / 0';
        this.findById('MPCSW_Lpu_id').setValue(this.Lpu_id);
	},
	searchInProgress: false,
	doSearch: function() {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var grid = this.findById('MPCSW_MedProductClassSearchGrid');
		var Mask = new Ext.LoadMask(Ext.get('swMedProductClassSearchWindow'), { msg: SEARCH_WAIT});
		var params = this.findById('MedProductClassSearchForm').getForm().getValues();

		if ( !params.MedProductClass_Model && !params.MedProductClass_Name && !params.MedProductType_id && !params.CardType_id && !params.ClassRiskType_id && !params.FuncPurpType_id && !params.UseAreaType_id && !params.UseSphereType_id && !params.FRMOEquipment_id) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('MPCSW_MedProductClass_Name').focus(true, 250); });
			thisWindow.searchInProgress = false;
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();

		grid.getStore().load({
			callback: function() {
				thisWindow.searchInProgress = false;
				Mask.hide();
				if ( grid.getStore().getCount() > 0 ) {
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			},
			params: params
		});
	},
	draggable: true,
	height: 600,
	id: 'swMedProductClassSearchWindow',
    editMedProductClass: function(action){
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

		if ( !this.action.inlist([ 'add', 'edit' ]) && action.inlist([ 'add', 'edit' ]) ) {
			return false;
		}

        if ( getWnd('swMedProductClassEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_klassa_meditsinskogo_izdeliya_uje_otkryito']);
            return false;
        }

        var formParams = {},
            grid = this.findById('MPCSW_MedProductClassSearchGrid'),
            params = {},
            _this = this,
            selectedRecord;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('MedProductClass_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        params.Lpu_id = this.findById('MPCSW_Lpu_id').getValue();
        params.action = action;
        params.callback = function(data) {
			if (!Ext.isEmpty(data.MedProductClass_Name)) {
				_this.findById('MPCSW_MedProductClass_Name').setValue(data.MedProductClass_Name);
				_this.doSearch();
			}

            getWnd('swMedProductClassEditWindow').hide();
        };

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            sw.swMsg.show({
                title: lang['podtverjdenie_dobavleniya_klassa_mi'],
                msg: lang['vnimatelno_proverte_vvedennuyu_informatsiyu_po_poisku_klassa_mi_vyi_tochno_hotite_dobavit_novoe_znachenie_v_spravochnik_klass_mi'],
                buttons: Ext.Msg.YESNO,
                fn: function ( buttonId ) {
                    if ( buttonId == 'yes' )
                    {
                        getWnd('swMedProductClassEditWindow').show(params);
                    }
                }
            });
            
        } else {
            if ( !selectedRecord ) {
                return false;
            }

            params.MedProductClass_id = selectedRecord.get('MedProductClass_id');

            if (!Ext.isEmpty(params.MedProductClass_id )) {
                getWnd('swMedProductClassEditWindow').show(params);
            } else {
                sw.swMsg.alert(lang['oshibka'], lang['u_vyibrannoy_zapisi_otsutstvuet_identifikator']);
                return false;
            }
        }
    },
	initComponent: function() {
        
        var _this =  this;
        
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					_this.doSearch()
				},
				iconCls: 'search16',
                tabIndex: TABINDEX_MPCSW + 35,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					_this.doReset();
				},
				iconCls: 'resetsearch16',
                tabIndex: TABINDEX_MPCSW + 40,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					_this.onOkButtonClick();
				},
				iconCls: 'ok16',
                tabIndex: TABINDEX_MPCSW + 45,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_MPCSW + 50),
			{
				handler: function() {
					_this.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'MPCSW_MedProductClass_Name',
                tabIndex: TABINDEX_MPCSW + 55,
				text: BTN_FRMCANCEL
			}],
			items: [
                new Ext.form.FormPanel({
                    autoHeight: true,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'MedProductClassSearchForm',
                    labelAlign: 'top',
                    region: 'north',
                    items: [{
                        name: 'Lpu_id',
                        tabIndex: -1,
                        xtype: 'hidden',
                        id: 'MPCSW_Lpu_id'
                    },{
                        xtype: 'fieldset',
                        style: 'margin: 5px 5px 5px 5px',
                        title: lang['filtryi'],
                        //collapsible: true,
                        autoHeight: true,
                        labelWidth: 150,
                        anchor: '-10',
                        layout: 'form',
                        items:
                        [{
                            border: false,
                            layout: 'column',
                            labelWidth: 150,
                            items: [{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    enableKeyEvents: true,
                                    fieldLabel: lang['naimenovanie_meditsinskogo_izdeliya'],
                                    id: 'MPCSW_MedProductClass_Name',
                                    name: 'MedProductClass_Name',
                                    tabIndex: TABINDEX_MPCSW,
                                    width: 350,
                                    xtype: 'textfield'
                                }]
                            },{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    enableKeyEvents: true,
                                    fieldLabel: lang['model'],
                                    id: 'MPCSW_MedProductClass_Model',
                                    name: 'MedProductClass_Model',
                                    tabIndex: TABINDEX_MPCSW + 5,
                                    width: 350,
                                    xtype: 'textfield'
                                }]
                            }]
                        },{
                            border: false,
                            layout: 'column',
                            labelWidth: 150,
                            items: [{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'MedProductType',
                                    fieldLabel: lang['vid_mi'],
                                    hiddenName: 'MedProductType_id',
                                    id: 'MPCSW_MedProductType_id',
                                    width: 350,
                                    editable: true,
                                    tabIndex: TABINDEX_MPCSW + 10,
                                    prefix: 'passport_',
                                    xtype: 'swcommonsprcombo',
                                    anyMatch: true
                                }]
                            },{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'CardType',
                                    fieldLabel: lang['tip_meditsinskogo_izdeliya'],
                                    hiddenName: 'CardType_id',
                                    id: 'MPCSW_CardType_id',
                                    width: 350,
                                    editable: true,
                                    tabIndex: TABINDEX_MPCSW + 15,
                                    prefix: 'passport_',
                                    xtype: 'swcommonsprcombo'
                                }]
                            }]
                        },{
                            border: false,
                            layout: 'column',
                            labelWidth: 150,
                            items: [{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'ClassRiskType',
                                    fieldLabel: lang['klass_potentsialnogo_riska_primeneniya'],
                                    hiddenName: 'ClassRiskType_id',
                                    id: 'MPCSW_ClassRiskType_id',
                                    width: 350,
                                    editable: true,
                                    prefix: 'passport_',
                                    tabIndex: TABINDEX_MPCSW + 20,
                                    xtype: 'swcommonsprcombo'
                                }]
                            },{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'FuncPurpType',
                                    fieldLabel: lang['funktsionalnoe_naznachenie'],
                                    hiddenName: 'FuncPurpType_id',
                                    id: 'MPCSW_FuncPurpType_id',
                                    width: 350,
                                    tabIndex: TABINDEX_MPCSW + 20,
                                    prefix: 'passport_',
                                    editable: true,
                                    xtype: 'swcommonsprcombo'
                                }]
                            }]
                        },{
                            border: false,
                            layout: 'column',
                            labelWidth: 150,
                            items: [{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'UseAreaType',
                                    fieldLabel: lang['oblast_primeneniya'],
                                    hiddenName: 'UseAreaType_id',
                                    id: 'MPCSW_UseAreaType_id',
                                    width: 350,
                                    tabIndex: TABINDEX_MPCSW + 25,
                                    prefix: 'passport_',
                                    editable: true,
                                    xtype: 'swcommonsprcombo'
                                }]
                            },{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    comboSubject: 'UseSphereType',
                                    fieldLabel: lang['sfera_primeneniya'],
                                    hiddenName: 'UseSphereType_id',
                                    id: 'MPCSW_UseSphereType_id',
                                    width: 350,
                                    tabIndex: TABINDEX_MPCSW + 30,
                                    prefix: 'passport_',
                                    editable: true,
                                    xtype: 'swcommonsprcombo'
                                }]
                            }]
                        },{
                            border: false,
                            layout: 'column',
                            labelWidth: 150,
                            items: [{
                                border: false,
                                columnWidth: .50,
                                layout: 'form',
                                items: [{
                                    xtype: 'swcommonsprcombo',
									fieldLabel: lang['FRMO_Perecheni_apparatov_i_oborudovania_otdelenei_mo'],
									tabIndex: TABINDEX_SPEF + 31,
									width: 350,
									hiddenName: 'FRMOEquipment_id',
									name: 'FRMOEquipment_Name',
									id: 'MPCSW_FRMOEquipment_id',
									comboSubject: 'FRMOEquipment',
									prefix: 'passport_'
                                }]
                            }]
                        }]
                    }],
                    keys: [{
                        fn: function(e) {
                            _this.doSearch();
                        },
                        key: Ext.EventObject.ENTER,
                        stopEvent: true
                    }]
            }),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				border: false,
				columns: [
                    { dataIndex: 'MedProductClass_id', hidden: true, hideable: false},
                    { dataIndex: 'MedProductClass_Name', header: lang['naimenovanie_klassa_meditsinskogo_izdeliya'], width: 150, sortable: true},
                    { dataIndex: 'MedProductType_Name', header: lang['vid_klassa_meditsinskogo_izdeliya'], width: 150, sortable: true},
                    { dataIndex: 'MedProductClass_Model', header: lang['model_klassa_meditsinskogo_izdeliya'], sortable: true, width: 100},
                    { dataIndex: 'CardType_Name', header: lang['tip_medintsiskogo_izdeliya'], width: 100},
                    { dataIndex: 'ClassRiskType_Name', header: lang['klass_potentsialnogo_riska'], width: 100},
                    { dataIndex: 'FuncPurpType_Name', header: lang['funktsionalnoe_znchaenie'], width: 100},
                    { dataIndex: 'FZ30Type_Name', header: lang['spravochnik_30y_fz'], width: 100},
                    { dataIndex: 'GMDNType_Name', header: lang['spravochnika_gmdn'], width: 100},
                    { dataIndex: 'MT97Type_Name', header: lang['klassifikator_mt_po_97_prikazu'], width: 100},
                    { dataIndex: 'OKOFType_Name', header: lang['spravochnik_okof_oborudovaniya'], width: 100},
                    { dataIndex: 'OKPType_Name', header: lang['spravochnik_okp_oborudovaniya'], width: 100},
                    { dataIndex: 'OKPDType_Name', header: lang['spravochnik_okpd_oborudovaniya'], width: 100},
                    { dataIndex: 'TNDEDType_Name', header: lang['spravochnik_tn_ved'], width: 100},
                    { dataIndex: 'UseAreaType_Name', header: lang['oblast_meditsinskogo_primeneniya'], width: 100},
					{ dataIndex: 'UseSphereType_Name', header: lang['sfera_primeneniya'], id: 'autoexpand', width: 100},
					{ dataIndex: 'FRMOEquipment_Name', header: lang['FRMO_Perecheni_apparatov_i_oborudovania_otdelenei_mo'], id: 'autoexpand', width: 100},
                ],
				id: 'MPCSW_MedProductClassSearchGrid',
				keys: [{
					key: [
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F3,
						Ext.EventObject.F4,
						Ext.EventObject.HOME,
						Ext.EventObject.INSERT,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation ) {
							e.browserEvent.stopPropagation();
						}
						else {
							e.browserEvent.cancelBubble = true;
						}

						if ( e.browserEvent.preventDefault ) {
							e.browserEvent.preventDefault();
						}

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = Ext.getCmp('MPCSW_MedProductClassSearchGrid');

						switch ( e.getKey() ) {
							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
								if ( !grid.getSelectionModel().getSelected() ) {
									return false;
								}

								grid.ownerCt.onOkButtonClick();
							break;

							case Ext.EventObject.F3:
								grid.getTopToolbar().items.items[2].handler();
							break;

							case Ext.EventObject.F4:
								grid.getTopToolbar().items.items[1].handler();
							break;

							case Ext.EventObject.HOME:
								GridHome(grid);
							break;

							case Ext.EventObject.INSERT:
								grid.getTopToolbar().items.items[0].handler();
							break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
							break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
							break;
 
							case Ext.EventObject.TAB:
								getWnd('swMedProductClassSearchWindow').buttons[0].focus(false, 100);
							break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function( grid, rowIndex ) {
						this.ownerCt.onOkButtonClick();
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							this.grid.getTopToolbar().items.items[6].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();

							if ( !_this.action.inlist([ 'add', 'edit' ]) || ( record.get('Server_id') && record.get('Server_id') == 0 && !isSuperAdmin() ) || (Ext.isEmpty(record) || Ext.isEmpty(record.get('MedProductClass_id')))) {
                                this.grid.getTopToolbar().items.items[1].disable();
                            } else {
                                this.grid.getTopToolbar().items.items[1].enable();
                            }

							if ( typeof record == 'object' && !Ext.isEmpty(record.get('MedProductClass_id')) ) {
                                this.grid.getTopToolbar().items.items[2].enable();
                            }
							else {
                                this.grid.getTopToolbar().items.items[2].disable();
                            }
						}
					},
					singleSelect: true
				}),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						'MedProductClass_id',
						'MedProductClass_Model',
						'MedProductClass_Name',
						'MedProductType_id',
						'CardType_id',
						'ClassRiskType_id',
						'FuncPurpType_id',
						'FZ30Type_id',
						'GMDNType_id',
						'MT97Type_id',
						'OKOFType_id',
						'OKPType_id',
						'OKPDType_id',
						'TNDEDType_id',
						'UseAreaType_id',
						'UseSphereType_id',
						'MedProductType_Name',
						'CardType_Name',
						'ClassRiskType_Name',
						'FuncPurpType_Name',
						'FZ30Type_Name',
						'GMDNType_Name',
						'MT97Type_Name',
						'OKOFType_Name',
						'OKPType_Name',
						'OKPDType_Name',
						'TNDEDType_Name',
						'UseAreaType_Name',
						'UseSphereType_Name',
						'MedProductType_Code',
						'FRMOEquipment_id',
						'FRMOEquipment_Name'
				    ],
					listeners: {
						'load': function(store, records, options) {
							var grid = Ext.getCmp('MPCSW_MedProductClassSearchGrid');
							
							if ( store.getCount() > 0 ) {
								grid.getTopToolbar().items.items[4].el.innerHTML = '0 / ' + store.getCount();
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}
					},
					url: '/?c=LpuPassport&m=getMedProductClassList'
				}),
				stripeRows: true,
				tbar: new sw.Promed.Toolbar({
					autoHeight: true,
					buttons: [{
						iconCls: 'add16',
						text: BTN_GRIDADD,
                        //hidden: false,
						handler: function() {
                            _this.editMedProductClass('add')
						}
					}, {
						iconCls: 'edit16',
						text: BTN_GRIDEDIT,
                        //hidden: true,
						handler: function() {
							if ( getRegionNick() == 'perm' ) {
								_this.editMedProductClass('edit');
							}
							else {
								Ext.Ajax.request({
									params: {
										MedProductClass_id: _this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductClass_id')
									},
									success: function(response, options) {
										var result = Ext.util.JSON.decode(response.responseText);
										if ( result[0] ) {
											sw.swMsg.alert(lang['oshibka'], result[0].Error_Msg);
										} else {
											_this.editMedProductClass('edit');
										}
									},
									url: '?c=LpuPassport&m=checkMedProductCardHasClass'
								});
							}
						}
					}, {
						iconCls: 'view16',
						text: BTN_GRIDVIEW,
                        //hidden: true,
						handler: function() {
                            _this.editMedProductClass('view')
						}
					}, {
						iconCls: 'refresh16',
						text: BTN_GRIDREFR,
                        //hidden: true,
						handler: function() {
                            _this.doSearch();
						}
					},{
						xtype: 'tbseparator'
					}, {
                        xtype: 'tbfill'
                    },{
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				})
			})]
		});
		sw.Promed.swMedProductClassSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	modal: true,
	onOkButtonClick: function() {
		if ( this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected() ) {
			this.onSelect({
                MedProductClass_id: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductClass_id'),
                MedProductClass_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductClass_Name'),
                MedProductClass_Model: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductClass_Model'),
                MedProductType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductType_Name'),
                MedProductType_Code: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MedProductType_Code'),
                CardType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('CardType_Name'),
                ClassRiskType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('ClassRiskType_Name'),
                FuncPurpType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('FuncPurpType_Name'),
                FZ30Type_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('FZ30Type_Name'),
                GMDNType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('GMDNType_Name'),
                MT97Type_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('MT97Type_Name'),
                OKOFType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('OKOFType_Name'),
                OKPType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('OKPType_Name'),
                OKPDType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('OKPDType_Name'),
                TNDEDType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('TNDEDType_Name'),
                UseAreaType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('UseAreaType_Name'),
				UseSphereType_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('UseSphereType_Name'),
				//FRMOEquipment_id: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('FRMOEquipment_id'),
				FRMOEquipment_Name: this.findById('MPCSW_MedProductClassSearchGrid').getSelectionModel().getSelected().get('FRMOEquipment_Name')
            });
		} else {
			this.hide();
		}
	},
	onSelect: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swMedProductClassSearchWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.Lpu_id = getGlobalOptions().lpu_id;
		this.onWinClose = Ext.emptyFn;
		this.doReset();

		var base_form = this.findById('MedProductClassSearchForm').getForm();

		if ( arguments[0] ) {
			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].Lpu_id ) {
				this.Lpu_id = arguments[0].Lpu_id;
			}

			if ( arguments[0].onSelect ) {
				this.onSelect = arguments[0].onSelect;
			}

			if ( arguments[0].onClose ) {
				this.onWinClose = arguments[0].onClose;
			}
		}

        base_form.setValues(arguments[0]);

		this.grid = Ext.getCmp('MPCSW_MedProductClassSearchGrid');

		if ( this.action.inlist([ 'add', 'edit' ]) ) {
			this.grid.getTopToolbar().items.items[0].enable();
		}
		else {
			this.grid.getTopToolbar().items.items[0].disable();
		}
	},
	title: lang['okno_poiska_klassa_meditsinskogo_izdeliya'],
	width: 800
});