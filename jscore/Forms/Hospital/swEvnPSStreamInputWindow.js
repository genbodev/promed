/**
* swEvnPSStreamInputWindow - окно потокового ввода КВС.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-09.03.2010
* @comment      Префикс для id компонентов EPSSIF (EvnPSStreamInputForm)
*/
sw.Promed.swEvnPSStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPSStreamInputWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnPSStreamInputWindow.js',

	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPS: function(options) {
		options = options || {};
		var grid = this.findById('EPSSIF_EvnPSGrid');

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kvs_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_karta_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var Evn_id = selected_record.get('EvnPS_id');

		if ( Ext.isEmpty(Evn_id) ) {
			return false;
		}

		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPL(options);
					}
				}
			},
			'702': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						options.ignoreEvnDrug = true;
						scope.deleteEvnPL(options);
					}
				}
			}
		};

		//BOB - 21.01.2019  контроль наличия РП
		if (!options.ignoreReanimatPeriodClose) {
			//alert("111");
			var that = this;
			Ext.Ajax.request({
				callback: function (opt, success, response) {
					if (success && response.responseText != 'false') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//console.log('BOB_response_obj=', response_obj);
						if (response_obj.success == true) {
							options.ignoreReanimatPeriodClose = true;
							that.deleteEvnPS(options);
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
							return false;
						} 
					}
					else {
						that.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при проверке закрытия Реанимационного периода.');
					}
				},
				params: {
					Object_id: Evn_id,
					Object: 'EvnPS'
				},
				url: '/?c=EvnReanimatPeriod&m=checkBeforeDelEvn'
			});
			return false;
		}
		//BOB - 21.01.2019


		var params = {Evn_id: Evn_id};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreEvnDrug) {
			params.ignoreEvnDrug = options.ignoreEvnDrug;
		}

		var doDelete = function() {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kvs_voznikli_oshibki_[tip_oshibki_2]']);
				},
				params: params,
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						if (response_obj.Alert_Msg) {
							var a_params = alert[response_obj.Alert_Code];
							sw.swMsg.show({
								buttons: a_params.buttons,
								fn: function(buttonId) {
									a_params.fn(buttonId, this);
								}.createDelegate(this),
								msg: response_obj.Alert_Msg,
								icon: Ext.MessageBox.QUESTION,
								title: lang['vopros']
							});
						} else {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kvs']);
						}
					}
					else {
						grid.getGrid().getStore().remove(selected_record);

						if (grid.getGrid().getStore().getCount() == 0) {
							grid.addEmptyRecord(grid.getGrid().getStore());
						}
					}

					grid.focus();
				},
				url: C_EVN_DEL
			});
		};

		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( 'yes' == buttonId ) {
						options.ignoreQuestion = true;
						doDelete();
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: lang['udalit_kartu_vyibyivshego_iz_statsionara'],
				title: lang['vopros']
			});
		}
	},
	draggable: true,
	height: 550,
	id: 'EvnPSStreamInputWindow',
	printCost: function() {
		var grid = this.findById('EPSSIF_EvnPSGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPS_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPS_id'),
				type: 'EvnPS',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	initComponent: function() {
		var that = this;
        var onCollapseExpandListeners = {
            'collapse': function(){
                that.doLayout();
                return true;
            },
            'expand': function(){
                that.doLayout();
                return true;
            }
        };
        Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPSSIF_CancelButton',
				onTabAction: function () {
					this.findById('EvnPSStreamInputParams').getForm().findField('PayType_id').focus(true);
				}.createDelegate(this),
				onShiftTabAction: function () {
					this.focusOnGrid();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSSIF + 16,
				text: '<u>З</u>акрыть'
			}],
			items: [{
				autoHeight: true,
				layout: 'form',
				region: 'north',
				items: [
                    new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'EPSSIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'EPSSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'EPSSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 140
				}),
                    new Ext.form.FormPanel({
                        animCollapse: false,
                        autoHeight: true,
                        bodyStyle: 'padding: 5px 5px 0',
                        border: false,
                        buttonAlign: 'left',
                        // collapsible: true,
                        frame: false,
                        id: 'EvnPSStreamInputParams',
                        items: [{
                            autoHeight: true,
                            style: 'padding: 0px;',
                            title: lang['gospitalizatsiya'],
                            width: 780,
                            xtype: 'fieldset',
                            collapsible: true,
                            listeners: onCollapseExpandListeners,
                            items: [{
                                border: false,
                                layout: 'column',
                                items: [{
                                    border: false,
                                    layout: 'column',
                                    items: [
                                        {
                                            border: false,
                                            layout: 'form',
                                            items: [
                                                {
                                                    listeners: {
                                                        'keydown': function(inp, e) {
                                                            if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
                                                                e.stopEvent();
                                                                this.buttons[this.buttons.length - 1].focus();
                                                            }
                                                        }.createDelegate(this)
                                                    },
                                                    listWidth: 300,
                                                    tabIndex: TABINDEX_EPSSIF + 1,
                                                    typeCode: 'int',
                                                    width: 200,
                                                    useCommonFilter: true,
                                                    xtype: 'swpaytypecombo'
                                                }, {
                                                    comboSubject: 'PrehospArrive',
                                                    fieldLabel: lang['sposob_dostavki'],
                                                    hiddenName: 'PrehospArrive_id',
                                                    listWidth: 300,
                                                    tabIndex: TABINDEX_EPSSIF + 2,
                                                    typeCode: 'int',
                                                    width: 200,
                                                    xtype: 'swcommonsprcombo'
                                                }
                                            ]
                                        },
                                        {
                                            border: false,
                                            layout: 'form',
                                            items: [
                                                {
                                                    comboSubject: 'PrehospToxic',
                                                    fieldLabel: lang['vid_opyaneniya'],
                                                    hiddenName: 'PrehospToxic_id',
                                                    listWidth: 300,
                                                    tabIndex: TABINDEX_EPSSIF + 3,
                                                    typeCode: 'int',
                                                    width: 200,
                                                    xtype: 'swcommonsprcombo'
                                                },
                                                {
                                                    comboSubject: 'PrehospType',
                                                    fieldLabel: lang['tip_gospitalizatsii'],
                                                    hiddenName: 'PrehospType_id',
                                                    listWidth: 300,
                                                    tabIndex: TABINDEX_EPSSIF + 4,
                                                    typeCode: 'int',
                                                    width: 200,
                                                    xtype: 'swcommonsprcombo'
                                                }
                                            ]
                                        }]
                                }, {
                                    border: false,
                                    layout: 'column',
                                    items: [
                                        {
                                            border: false,
                                            layout: 'form',
                                            items: [
                                                {
                                                    fieldLabel: lang['priemnoe_otdelenie'],
                                                    hiddenName: 'LpuSection_pid',
                                                    id: 'EPSSIF_LpuSectionRecCombo',
                                                    listWidth: 600,
                                                    tabIndex: TABINDEX_EPSSIF + 6,
                                                    width: 300,
                                                    xtype: 'swlpusectionglobalcombo'
                                                }, {
                                                    fieldLabel: lang['vrach_priem_otd'],
                                                    hiddenName: 'MedStaffFact_pid',
                                                    id: 'EPSSIF_MedStaffFactRecCombo',
                                                    listWidth: 600,
                                                    tabIndex: TABINDEX_EPSSIF + 7,
                                                    width: 300,
                                                    xtype: 'swmedstafffactglobalcombo'
                                                }
                                            ]
                                        },
                                        {
                                            border: false,
                                            layout: 'form',
                                            items: [
                                                {
                                                fieldLabel: lang['data_postupleniya'],
                                                format: 'd.m.Y',
                                                listeners: {
                                                    'change': function(field, newValue, oldValue) {
                                                        var base_form = this.findById('EvnPSStreamInputParams').getForm();

                                                        var lpu_section_id = base_form.findField('LpuSection_id').getValue();
                                                        var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();
                                                        var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
                                                        var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();

                                                        base_form.findField('LpuSection_id').clearValue();
                                                        base_form.findField('LpuSection_pid').clearValue();
                                                        base_form.findField('MedStaffFact_id').clearValue();
                                                        base_form.findField('MedStaffFact_pid').clearValue();

                                                        if ( !newValue ) {
                                                            setMedStaffFactGlobalStoreFilter({
																EvnClass_SysNick: 'EvnSection',
                                                                isStac: (getRegionNick() == 'krym') ? false : true
                                                            });

                                                            base_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
                                                            base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                                                            setLpuSectionGlobalStoreFilter({
                                                                isStacReception: true
                                                            });

                                                            base_form.findField('LpuSection_pid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

                                                            setLpuSectionGlobalStoreFilter({
                                                                isStac: true
                                                            });

                                                            base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
                                                        }
                                                        else {
                                                            setMedStaffFactGlobalStoreFilter({
																EvnClass_SysNick: 'EvnSection',
                                                                isStac: (getRegionNick() == 'krym') ? false : true,
                                                                onDate: Ext.util.Format.date(newValue, 'd.m.Y')
                                                            });

                                                            base_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
                                                            base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                                                            setLpuSectionGlobalStoreFilter({
                                                                isStacReception: true,
                                                                onDate: Ext.util.Format.date(newValue, 'd.m.Y')
                                                            });

                                                            base_form.findField('LpuSection_pid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

                                                            setLpuSectionGlobalStoreFilter({
                                                                isStac: true,
                                                                onDate: Ext.util.Format.date(newValue, 'd.m.Y')
                                                            });

                                                            base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
                                                        }

                                                        if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
                                                            base_form.findField('LpuSection_id').setValue(lpu_section_id);
                                                        }

                                                        if ( base_form.findField('LpuSection_pid').getStore().getById(lpu_section_pid) ) {
                                                            base_form.findField('LpuSection_pid').setValue(lpu_section_pid);
                                                        }

                                                        if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
                                                            base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
                                                        }

                                                        if ( base_form.findField('MedStaffFact_pid').getStore().getById(med_staff_fact_pid) ) {
                                                            base_form.findField('MedStaffFact_pid').setValue(med_staff_fact_pid);
                                                        }
                                                    }.createDelegate(this)
                                                },
                                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
                                                name: 'EvnPS_setDate',
                                                tabIndex: TABINDEX_EPSSIF + 5,
                                                width: 100,
                                                xtype: 'swdatefield'
                                            }
                                            ]
                                        }
                                    ]
                                }]
                            }]
                        }, {
                            autoHeight: true,
                            style: 'padding: 0px;',
                            title: lang['dvijenie'],
                            width: 780,
                            xtype: 'fieldset',
                            collapsible: true,
                            listeners: onCollapseExpandListeners,
                            items: [{
                                hiddenName: 'LpuSection_id',
                                id: 'EPSSIF_LpuSectionCombo',
                                linkedElements: [
                                    'EPSSIF_MedStaffFactCombo'
                                ],
                                listWidth: 600,
                                tabIndex: TABINDEX_EPSSIF + 8,
                                width: 450,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										that.leaveTypeFedFilter();
										that.leaveTypeFilter();
										that.leaveCauseFilter();
										that.resultDeseaseFilter();
									}
								},
                                xtype: 'swlpusectionglobalcombo'
                            }, {
                                autoLoad: false,
                                comboSubject: 'TariffClass',
                                fieldLabel: lang['vid_tarifa'],
                                hiddenName: 'TariffClass_id',
                                lastQuery: '',
                                listeners: {
                                    'render': function(combo) {
                                        var params = new Object();

                                        if ( getGlobalOptions().region && getGlobalOptions().region.nick != 'ufa' ) {
                                            params.where = 'where TariffClass_Code in (5, 6, 8, 10)';
                                        }

                                        combo.getStore().load({
                                            params: params
                                        });
                                    }
                                },
                                tabIndex: TABINDEX_EPSSIF + 9,
                                typeCode: 'int',
                                width: 300,
                                xtype: 'swcommonsprcombo'
                            }, {
                                fieldLabel: lang['vrach'],
                                hiddenName: 'MedStaffFact_id',
                                id: 'EPSSIF_MedStaffFactCombo',
                                listWidth: 600,
                                parentElementId: 'EPSSIF_LpuSectionCombo',
                                tabIndex: TABINDEX_EPSSIF + 10,
                                width: 450,
                                xtype: 'swmedstafffactglobalcombo'
                            }]
                        }, {
                            autoHeight: true,
                            style: 'padding: 0px;',
                            title: lang['ishod_gospitalizatsii'],
                            width: 780,
                            xtype: 'fieldset',
                            collapsible: true,
                            listeners: onCollapseExpandListeners,
                            items: [{
                                border: false,
                                layout: 'column',
                                items: [{
                                    border: false,
                                    layout: 'form',
                                    items: [{
                                        fieldLabel: ('kareliya' == getGlobalOptions().region.nick)?lang['rezultat_gospitalizatsii']:lang['ishod_gospitalizatsii'],
										hiddenName:'LeaveTypeFed_id',
										listeners: {
											'change':function (combo, newValue, oldValue) {
												var index = combo.getStore().findBy(function (rec) {
													if (rec.get('LeaveType_id') == newValue) {
														return true;
													}
													else {
														return false;
													}
												});
												var record = combo.getStore().getAt(index);

												combo.fireEvent('beforeselect', combo, record);
											},
											'beforeselect':function (combo, record) {
												var base_form = that.findById('EvnPSStreamInputParams').getForm();
												var LeaveTypeCombo = base_form.findField('LeaveType_id');
												LeaveTypeCombo.clearValue();
												
												if (record) {
													LeaveTypeCombo.setFieldValue('LeaveType_Code',record.get('LeaveType_Code'));
												}
												
												var index = LeaveTypeCombo.getStore().findBy(function (rec) {
													if (rec.get('LeaveType_id') == LeaveTypeCombo.getValue()) {
														return true;
													}
													else {
														return false;
													}
												});
												var record = LeaveTypeCombo.getStore().getAt(index);
												that.leaveCauseFilter();
											}
										},
										tabIndex: TABINDEX_EPSSIF + 11,
										width: 200,
										listWidth: 300,
										xtype: 'swleavetypefedcombo'
									}, {
                                        comboSubject: 'LeaveType',
                                        fieldLabel: ('kareliya' == getRegionNick())?lang['rezultat_gospitalizatsii']:lang['ishod_gospitalizatsii'],
                                        hiddenName: 'LeaveType_id',
                                        listWidth: 300,
                                        tabIndex: TABINDEX_EPSSIF + 11,
                                        typeCode: 'int',
                                        width: 200,
                                        xtype: 'swleavetypecombo'
                                    }, {
                                        comboSubject: 'ResultDesease',
                                        fieldLabel:('kareliya' == getGlobalOptions().region.nick)?lang['ishod_gospitalizatsii']:lang['ishod_zabolevaniya'],
                                        hiddenName: 'ResultDesease_id',
                                        listWidth: 700,
                                        tabIndex: TABINDEX_EPSSIF + 12,
                                        typeCode: 'int',
                                        width: 200,
                                        xtype: 'swcommonsprcombo'
                                    }, {
                                        comboSubject: 'LeaveCause',
                                        fieldLabel: lang['prichina_vyipiski'],
                                        hiddenName: 'LeaveCause_id',
                                        listWidth: 300,
                                        tabIndex: TABINDEX_EPSSIF + 13,
                                        typeCode: 'int',
                                        width: 200,
                                        xtype: 'swcommonsprcombo'
                                    }]
                                }, {
                                    border: false,
                                    layout: 'form',
                                    items: [{
                                        fieldLabel: lang['data_ishoda'],
                                        format: 'd.m.Y',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
                                        name: 'EvnLeave_setDate',
                                        tabIndex: TABINDEX_EPSSIF + 14,
                                        width: 100,
                                        xtype: 'swdatefield'
                                    }, {
                                        allowDecimals: true,
                                        allowNegative: false,
                                        fieldLabel: lang['ukl'],
                                        id: 'EPSSIF_EvnLeave_UKL',
                                        maxValue: 1,
                                        name: 'EvnLeave_UKL',
                                        tabIndex: TABINDEX_EPSSIF + 15,
                                        width: 70,
                                        value: 1,
                                        xtype: 'numberfield',
	                                    enableKeyEvents: true,
	                                    listeners:{
		                                    'keydown':function (inp, e) {
			                                    if (e.getKey() == Ext.EventObject.TAB) {
				                                    if (!e.shiftKey) {
					                                    e.stopEvent();
					                                    that.focusOnGrid();
				                                    }
			                                    }
		                                    }
	                                    }

                                    }]
                                }]
                            }]
                        }],
                        labelAlign: 'right',
                        labelWidth: 140,
                        title: lang['parametryi_vvoda']
                    })
                ]
			},
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { this.openEvnPSEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', handler: function() { this.openEvnPSEditWindow('edit'); }.createDelegate(this) },
					{ name: 'action_view', handler: function() { this.openEvnPSEditWindow('view'); }.createDelegate(this) },
					{ name: 'action_delete', handler: function() { this.deleteEvnPS(); }.createDelegate(this) },
					{ name: 'action_refresh', handler: function() { this.refreshEvnPSGrid(); }.createDelegate(this), disabled: true },
					{
						name: 'action_print',
						menuConfig: {
							printObject: {handler: function(){ this.printEvnPS(); }.createDelegate(this)},
							printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { that.printCost() }},
							printObjectSpr: {name:'printObjectSpr', text: langs('Справка о фактической себестоимости'), hidden: (getRegionNick() != 'kz'), handler: function(){ this.doPrintEvnPSSpr(); }.createDelegate(this)}
						}
					}
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: '/?c=EvnPS&m=loadEvnPSStreamList',
				focusOn: {
					name: 'EPSSIF_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'EPSSIF_EvnLeave_UKL',
					type: 'field'
				},
				id: 'EPSSIF_EvnPSGrid',
				pageSize: 100,
				paging: false,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'EvnPS_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'EvnPS_NumCard', type: 'string', header: langs('№ карты'), width: 70 },
					{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), id: 'autoexpand' },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100 },
					{ name: 'EvnPS_KoikoDni', type: 'int', header: langs('К/дни'), width: 100 },
					{ name: 'EvnPS_setDate', type: 'date', format: 'd.m.Y', header: langs('Поступление'), width: 100 },
					{ name: 'EvnPS_disDate', type: 'date', format: 'd.m.Y', header: langs('Выписка'), width: 100 },
					{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swEvnPSStreamInputWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EPSSIF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnPSStreamInputParams').getForm();

			var record = combo.getStore().getById(newValue);
			var tariff_class_id = base_form.findField('TariffClass_id').getValue();

			base_form.findField('TariffClass_id').clearValue();

			if ( record && record.get('LpuUnitType_id') == 7 ) { // Стационар на дому
				if ( this.action != 'view' ) {
					base_form.findField('TariffClass_id').enable();
				}

				if ( tariff_class_id ) {
					base_form.findField('TariffClass_id').setValue(tariff_class_id);
				}
			}
			else {
				base_form.findField('TariffClass_id').disable();
			}

			this.leaveTypeFedFilter();
			this.leaveTypeFilter();
		}.createDelegate(this));

		this.findById('EPSSIF_MedStaffFactCombo').addListener('change', function(combo, newValue, oldValue) {
			this.leaveTypeFedFilter();
			this.leaveTypeFilter();
		}.createDelegate(this));

		//focusing viewframes
		var grid = this.findById('EPSSIF_EvnPSGrid');
		grid.focusPrev = this.findById('EPSSIF_EvnLeave_UKL');
		grid.focusPrev.type = 'field';
		grid.focusPrev.name = grid.focusPrev.id;
		grid.focusOn = this.buttons[2];
		grid.focusOn.type = 'field';
		grid.focusOn.name = grid.focusOn.id;

		this.focusOnGrid = function () {
			var grid = that.findById('EPSSIF_EvnPSGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnPSStreamInputWindow').openEvnPSEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPSStreamInputWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	leaveTypeFedFilter: function() {
		var base_form = this.findById('EvnPSStreamInputParams').getForm();
		if ( getRegionNick().inlist([ 'buryatiya', 'penza' ]) ) {
			var LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();

			var fedIdList = new Array();

			// Получаем список доступных исходов из федерального справочника
			base_form.findField('LeaveType_id').getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList) ) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});

			base_form.findField('LeaveTypeFed_id').clearFilter();
			base_form.findField('LeaveTypeFed_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if (LpuUnitType_SysNick == 'stac') {
					// круглосуточный
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
						);
					});
				} else {
					// https://redmine.swan.perm.ru/issues/18318
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
					return (rec.get('LeaveType_id').toString().inlist(fedIdList));
				});
			}

			if ( !Ext.isEmpty(LeaveTypeFed_id) ) {
				var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveTypeFed_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveTypeFed_id').clearValue();
					base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
				}
			}
		}
	},
	leaveCauseFilter: function() {
		var base_form = this.findById('EvnPSStreamInputParams').getForm();

		if ( getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza' ]) ) {
			var oldValue = base_form.findField('LeaveCause_id').getValue();

			base_form.findField('LeaveCause_id').clearFilter();
			base_form.findField('LeaveCause_id').lastQuery = '';

			switch ( base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick') ) {
				case 'stac': // Круглосуточный стационар
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (!rec.get('LeaveCause_Code').inlist([ 210, 211, 212 ]));
					});
				break;

				default:
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (rec.get('LeaveCause_Code').inlist([ 1, 6, 7, 27, 28, 29, 210, 211, 212 ]));
					});
				break;
			}

			var index = base_form.findField('LeaveCause_id').getStore().findBy(function (rec) {
				return (rec.get('LeaveCause_id') == oldValue);
			});

			if ( index == -1 ) {
				base_form.findField('LeaveCause_id').clearValue();
			}

			if ( base_form.findField('LeaveCause_id').getStore().getCount() == 1 ) {
				base_form.findField('LeaveCause_id').setValue(base_form.findField('LeaveCause_id').getStore().getAt(0).get('LeaveCause_id'));
			}
		}
	},
	resultDeseaseFilter: function() {
		var base_form = this.findById('EvnPSStreamInputParams').getForm();
		if ( getRegionNick().inlist([ 'buryatiya', 'kareliya', 'penza' ]) ) {
			var oldValue = base_form.findField('ResultDesease_id').getValue();
			base_form.findField('ResultDesease_id').clearFilter();
			base_form.findField('ResultDesease_id').lastQuery = '';
			if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 2) {
				// круглосуточный
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					if (rec.get('ResultDesease_Code') > 100 && rec.get('ResultDesease_Code') < 200 ) {
						return true;
					}
					else {
						return false;
					}
				});
			} else {
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					if (rec.get('ResultDesease_Code') > 200 && rec.get('ResultDesease_Code') < 300 ) {
						return true;
					}
					else {
						return false;
					}
				});
			}

			var index = base_form.findField('ResultDesease_id').getStore().findBy(function (rec) {
				if (rec.get('ResultDesease_id') == oldValue) {
					return true;
				}
				else {
					return false;
				}
			});

			if (index == -1) {
				base_form.findField('ResultDesease_id').clearValue();
			}
		}
	},
	layout: 'border',
	leaveTypeFedFilter: function() {
		var base_form = this.findById('EvnPSStreamInputParams').getForm();

		if ( getRegionNick().inlist([ 'buryatiya', 'penza', 'pskov', 'vologda' ]) ) {
			var LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();

			var fedIdList = new Array();

			// Получаем список доступных исходов из федерального справочника
			base_form.findField('LeaveType_id').getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList) ) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});

			base_form.findField('LeaveTypeFed_id').clearFilter();
			base_form.findField('LeaveTypeFed_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if ( LpuUnitType_SysNick == 'stac' ) {
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
						);
					});
				} else {
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& (getRegionNick() == 'buryatiya' || !(LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ])))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
					return (rec.get('LeaveType_id').toString().inlist(fedIdList));
				});
			}

			if ( !Ext.isEmpty(LeaveTypeFed_id) ) {
				var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveTypeFed_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveTypeFed_id').clearValue();
					base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
				}
			}
		}
	},
	leaveTypeFilter: function() {
		var base_form = this.findById('EvnPSStreamInputParams').getForm();

		var
			LeaveType_id = base_form.findField('LeaveType_id').getValue(),
			LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

		base_form.findField('LeaveType_id').clearFilter();
		base_form.findField('LeaveType_id').lastQuery = '';

		if ( getRegionNick().inlist([ 'kareliya', 'krym' ]) ) {
			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if ( LpuUnitType_SysNick == 'stac' ) {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
							&& !(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115' ]))
						);
					});
				} else {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(getRegionNick() != 'kareliya' && LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
							&& !(rec.get('LeaveType_Code').toString().inlist([ '210', '211', '212', '213', '215' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
					return (
						!(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115', '210', '211', '212', '213', '215' ]))
					);
				});
			}

			if ( !Ext.isEmpty(LeaveType_id) ) {
				var index = base_form.findField('LeaveType_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveType_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveType_id').clearValue();
					base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'));
				}
			}
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	openEvnPSEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPSEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyibyivshego_iz_statsionara_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnPSStreamInputParams').getForm();
		var grid = this.findById('EPSSIF_EvnPSGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_kvs']);
			return false;
		}

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnPSData ) {
				return false;
			}

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('EvnPS_id') == data.evnPSData.EvnPS_id);
			});
			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				var evn_ps_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_ps_fields.push(key);
				});

				for ( i = 0; i < evn_ps_fields.length; i++ ) {
					record.set(evn_ps_fields[i], data.evnPSData[evn_ps_fields[i]]);
				}

				record.commit();
			} else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPS_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ 'data': [ data.evnPSData ]}, true);
			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.onHide = function() {
				// TODO: Продумать использование getWnd в таких случаях
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};

			if ( base_form.findField('EvnLeave_setDate').getValue() ) {
				params.EvnLeave_setDate = base_form.findField('EvnLeave_setDate').getValue();
			}

			if ( base_form.findField('EvnPS_setDate').getValue() ) {
				params.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
			}

			if ( base_form.findField('EPSSIF_EvnLeave_UKL').getValue() ) {
				params.EvnLeave_UKL = base_form.findField('EPSSIF_EvnLeave_UKL').getValue();
			}

			if ( base_form.findField('LeaveCause_id').getValue() ) {
				params.LeaveCause_id = base_form.findField('LeaveCause_id').getValue();
			}

			if ( base_form.findField('LeaveType_id').getValue() ) {
				params.LeaveType_id = base_form.findField('LeaveType_id').getValue();
			}
			
			if ( base_form.findField('LeaveTypeFed_id').getValue() ) {
				params.LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();
			}

			if ( base_form.findField('LpuSection_id').getValue() ) {
				params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			}

			if ( base_form.findField('LpuSection_pid').getValue() ) {
				params.LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
			}

			if ( base_form.findField('MedStaffFact_id').getValue() ) {
				var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					if ( rec.get('MedStaffFact_id') == base_form.findField('MedStaffFact_id').getValue() ) {
						return true;
					}
					else {
						return false;
					}
				});
				var record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

				if ( record ) {
					params.MedPersonal_id = record.get('MedPersonal_id');
				}
			}

			if ( base_form.findField('MedStaffFact_pid').getValue() ) {
				params.MedStaffFact_pid = base_form.findField('MedStaffFact_pid').getValue();
			}

			if ( base_form.findField('PayType_id').getValue() ) {
				params.PayType_id = base_form.findField('PayType_id').getValue();
			}

			if ( base_form.findField('PrehospArrive_id').getValue() ) {
				params.PrehospArrive_id = base_form.findField('PrehospArrive_id').getValue();
			}

			if ( base_form.findField('PrehospToxic_id').getValue() ) {
				params.PrehospToxic_id = base_form.findField('PrehospToxic_id').getValue();
			}

			if ( base_form.findField('PrehospType_id').getValue() ) {
				params.PrehospType_id = base_form.findField('PrehospType_id').getValue();
			}

			if ( base_form.findField('ResultDesease_id').getValue() ) {
				params.ResultDesease_id = base_form.findField('ResultDesease_id').getValue();
			}

			if ( base_form.findField('TariffClass_id').getValue() ) {
				params.TariffClass_id = base_form.findField('TariffClass_id').getValue();
			}

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swEvnPSEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPS_id') ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_kvs_iz_spiska']);
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.onPersonChange = function(data) {
				if (data.Evn_id) {
					record.set('EvnPS_id', data.Evn_id);
					record.set('PersonEvn_id', data.PersonEvn_id);
					record.set('Person_id', data.Person_id);
					record.set('Server_id', data.Server_id);
					record.set('Person_Surname', data.Person_SurName);
					record.set('Person_Firname', data.Person_FirName);
					record.set('Person_Secname', data.Person_SecName);
					record.commit();
				}
			};

			params.EvnPS_id = record.get('EvnPS_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnPSEditWindow').show(params);
		}
	},
	plain: true,
	pmUser_Name: null,
	printEvnPS: function() {
		var grid = this.findById('EPSSIF_EvnPSGrid').getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');

		if ( evn_ps_id ) {
			if ( getRegionNick() == 'kz' ) {
				// Нужно определять тип стационара, для этого нужно добавить поле LpuUnitType_id или LpuUnitType_SysNick в гриды
				// Пока печатаем КВС для круглосуточного стационара
				// https://redmine.swan.perm.ru/issues/39955
                printBirt({
                    'Report_FileName': 'han_EvnPS_f066u.rptdesign',
                    'Report_Params': '&paramEvnPS=' + evn_ps_id,
                    'Report_Format': 'pdf'
                });
			}
			else {
				window.open('/?c=EvnPS&m=printEvnPS&EvnPS_id=' + evn_ps_id + '&Parent_Code=3', '_blank');
			}
		}
	},
	doPrintEvnPSSpr: function() {
		var grid = this.findById('EPSSIF_EvnPSGrid').getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], 'Не выбран КВС');
			return false;
		}

		var evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');

		if ( evn_ps_id ) {
			if ( getRegionNick() == 'kz' ) {
				printBirt({
					'Report_FileName': 'hosp_Spravka_KSG.rptdesign',
					'Report_Params': '&paramEvnPS=' + evn_ps_id,
					'Report_Format': 'pdf'
				});
			}
		}
	},
	refreshEvnPSGrid: function() {
		return false;

		var grid = this.findById('EPSSIF_EvnPSGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('EPSSIF_StreamInformationForm').findById('EPSSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('EPSSIF_StreamInformationForm').findById('EPSSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EPSSIF_EvnPSGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('EPSSIF_EvnPSGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		var that = this;
		sw.Promed.swEvnPSStreamInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		var base_form = this.findById('EvnPSStreamInputParams').getForm();
		base_form.reset();

		if ( getRegionNick() == 'buryatiya' ) {
			base_form.findField('TariffClass_id').hideContainer();
		}

		if ( getRegionNick().inlist([ 'buryatiya', 'penza' ]) ) {
			// убираем исход гостиптализации и показываем федеральный спрвочник
			base_form.findField('LeaveType_id').hideContainer();
			base_form.findField('LeaveTypeFed_id').showContainer();

			this.leaveTypeFedFilter();
		} else {
			base_form.findField('LeaveTypeFed_id').hideContainer();
			base_form.findField('LeaveType_id').showContainer();

			this.leaveTypeFilter();
		}
		
		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		setCurrentDateTime({
			callback: function(date) {
				base_form.findField('EvnLeave_setDate').setMaxValue(date);
			},
			dateField: base_form.findField('EvnPS_setDate'),
			loadMask: false,
			setDate: false,
			setDateMaxValue: true,
			windowId: this.id
		});

		base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), null);
		base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), null);

		var gr = this.findById('EPSSIF_EvnPSGrid');
		gr.getGrid().getStore().removeAll();
		gr.addEmptyRecord(gr.getGrid().getStore());
		setTimeout(that.focusOnGrid, 500)
	},
	title: WND_HOSP_EPSSTIN,
	width: 800
});