/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 09.09.14
 * Time: 13:45
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swPersonSearchPersonCardAutoWindow = Ext.extend(sw.Promed.BaseForm, {
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    draggable: true,
    height: 763,
    id: 'PersonSearchPersonCardAutoWindow',
    title: 'Групповое прикрепление',
    width: 1500,
    doAddPersonCards: function(){
        var ResultString = 'Результат прикреплений:\n';
        var base_form = Ext.getCmp('PersonSearchPersonCardAutoWindow');
        var PersonGrid = base_form.findById('PSPCA_Grid').ViewGridPanel;
        var Person_ids_array = new Array();
		Person_ids_array = this.PersonDataChecked;
        //Вызов формы
        var Log = Ext.getCmp('PSPCA_Log');
        Log.setValue('');
        var that = this;
		if (that.PersonDataChecked.length < 1) {
			sw.swMsg.alert('Ошибка', 'Необходимо выбрать людей');
			return false;
		}
        getWnd('swPersonCardAutoParamsWindow').show({
            callback: function(answer) {
                that.findById('pacient_tab_panel').setActiveTab(1);
                var params = {
                    Lpu_id: answer.PCAPLpu_id,
                    LpuRegionType_id: answer.PCAPLpuRegionType_id,
                    LpuRegion_id: answer.PCAP_LpuRegion_id,
                    IsAttachCondit: 0,
                    PersonCardAttach: 0,
					PC_type: answer.PC_type,
                    LpuRegion_Fapid: answer.LpuRegion_Fapid,
                    Person_ids_array: Ext.util.JSON.encode(Person_ids_array)
                };
                Ext.Ajax.request({
                    params: params,
                    url: '?c=PersonCard&m=savePersonCardAuto',
                    callback: function(options,success,response){
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        var i = 0;
                        var resultstring = '';
                        for( i = 0; i<=(response_obj.length-1); i++){
                            resultstring += response_obj[i]+'\n\n';
                        }
                        Log.setValue(resultstring);
                    }

                });
            }
        });
    },
    doResetFilter: function(){
        var base_form = Ext.getCmp('PersonSearchPersonCardAutoWindow');
        base_form.FilterPanel.getForm().reset();
        var PersonGrid = base_form.findById('PSPCA_Grid').ViewGridPanel;
    },
    doFilter: function() {

        var base_form = Ext.getCmp('PersonSearchPersonCardAutoWindow');

        var filters = base_form.FilterPanel.getForm().getValues();
        //filters.PSPCALpu_id = base_form.findById('PSPCA_Lpu_id').getValue();
		filters.PSPCAOrg_id = base_form.findById('PSPCA_Org_id').getValue();
        var PersonGrid = base_form.findById('PSPCA_Grid').ViewGridPanel;
        var loadMask = new Ext.LoadMask(Ext.get('PersonSearchPersonCardAutoWindow'), {msg: LOAD_WAIT});
        //loadMask.show();
        filters.limit = 100;
        filters.start = 0;
        PersonGrid.getStore().load({
            params: filters,
            callback: function() {
                if ( PersonGrid.getStore().getCount() > 0 )
                {
                    //PersonGrid.getSelectionModel().selectFirstRow();
                    PersonGrid.getView().focusRow(0);
                    //loadMask.hide();
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
            //height: 700,
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
                title: 'Фильтр',
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
								displayField:'Org_Name',
								editable:false,
								enableKeyEvents:true,
								fieldLabel:'МО прикрепления',
								id: 'PSPCA_Org_id',
								hiddenName:'PSPCAOrg_id',
								listeners:{
									'keydown':function (inp, e) {
										if (inp.disabled)
											return;
										if (e.F4 == e.getKey()) {
											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											inp.onTrigger1Click();

											return false;
										}
									},
									'keyup':function (inp, e) {
										if (e.F4 == e.getKey()) {
											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									}
								},
								mode:'local',
								onTrigger1Click:function () {
									//var base_form = this.findById('EvnSectionEditForm').getForm();
									var base_form = Ext.getCmp('PersonSearchPersonCardAutoWindow').FilterPanel.getForm();
									var combo = base_form.findField('PSPCAOrg_id');

									if (combo.disabled) {
										return false;
									}

									getWnd('swOrgSearchWindow').show({
										OrgType_id: 11,
										onClose:function () {
											combo.focus(true, 200)
										},
										onSelect:function (org_data) {
											if (org_data.Org_id > 0) {
												combo.getStore().loadData([
													{
														Org_id:org_data.Org_id,
														Org_Name:org_data.Org_Name
													}
												]);
												combo.setValue(org_data.Org_id);
												getWnd('swOrgSearchWindow').hide();
												combo.collapse();

												var form = this.ownerCt.ownerCt;
												var region_combo = form.findById('PSPCLpuRegion_id');
												var lpu_region_id = region_combo.getValue();

												form.findById('PSPCLpuRegion_id').getStore().removeAll();

												form.findById('PSPCLpuRegion_id').getStore().load({
													params: {
														//Lpu_id: lpuId,
														Org_id: org_data.Org_id,
														LpuRegionType_id: form.findById('PSPCA_LpuRegionTypeCombo').getValue(),
														Object: 'LpuRegion',
														showOpenerOnlyLpuRegions: 1,
                                                        add_without_region_line: true
													},
													callback: function() {
														form.findById('PSPCLpuRegion_id').clearValue();
													}
												});
                                                form.findById('PSPCAW_LpuRegion_Fapid').clearValue();
                                                form.findById('PSPCAW_LpuRegion_Fapid').getStore().removeAll();
                                                form.findById('PSPCAW_LpuRegion_Fapid').getStore().load({
                                                    params:{
                                                        Org_id: org_data.Org_id,
                                                        add_without_region_line: true
                                                    },
                                                    callback: function() {
                                                        form.findById('PSPCAW_LpuRegion_Fapid').clearValue();
                                                    }
                                                })


											}
										}
									});
								}.createDelegate(this),
								store:new Ext.data.JsonStore({
									autoLoad:false,
									fields:[
										{name:'Org_id', type:'int'},
										{name:'Org_Name', type:'string'}
									],
									key:'Org_id',
									sortInfo:{
										field:'Org_Name'
									},
									url:C_ORG_LIST
								}),
								tabIndex:this.tabIndex + 34,
								tpl:new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{Org_Name}',
									'</div></tpl>'
								),
								trigger1Class:'x-form-search-trigger',
								triggerAction:'none',
								valueField:'Org_id',
								width:500,
								xtype:'swbaseremotecombo'
							},
                            {
                                allowBlank: true,
                                enableKeyEvents: true,
                                hiddenName : "PSPCALpuRegionType_id",
                                id: 'PSPCA_LpuRegionTypeCombo',
                                listeners: {
                                    'change': function(combo, lpuRegionTypeId, oldLpuRegionTypeId) {
										var form = this.ownerCt.ownerCt;
										var region_combo = form.findById('PSPCLpuRegion_id');
										var lpu_region_id = region_combo.getValue();
										var org_id = getGlobalOptions().org_id;
										if ( getGlobalOptions().superadmin )
											org_id = form.findById('PSPCA_Org_id').getValue();

										form.findById('PSPCLpuRegion_id').getStore().removeAll();

										form.findById('PSPCLpuRegion_id').getStore().load({
											params: {
												//Lpu_id: lpu_id,
												Org_id: org_id,
												LpuRegionType_id: lpuRegionTypeId,
												Object: 'LpuRegion',
												showOpenerOnlyLpuRegions: 1,
                                                add_without_region_line: true
											},
											callback: function() {
												form.findById('PSPCLpuRegion_id').clearValue();
											}
										});

                                    }
                                },
                                tabIndex: 2104,
                                width: 400,
                                xtype : "swlpuregiontypecombo"
                            },
                            {
                                allowBlank: true,
                                displayField: 'LpuRegion_Name',
                                fieldLabel: 'Участок',
                                forceSelection: true,
                                hiddenName: 'PSPCALpuRegion_id',
                                id: 'PSPCLpuRegion_id',
                                listeners: {
                                    'blur': function(combo) {

                                        if (combo.getRawValue()=='')
                                            combo.clearValue();
                                    },
                                    'change': function(combo, lpuRegionId) {

                                        var lpu_region_type_id = 0;
                                        combo.getStore().each(
                                            function( record ) {
                                                if ( record.data.LpuRegion_id == lpuRegionId )
                                                {
                                                    lpu_region_type_id = record.data.LpuRegionType_id;
                                                    return true;
                                                }
                                            }
                                        );
                                    }
                                },
                                minChars: 1,
                                mode: 'local',
                                queryDelay: 1,
                                setValue: function(v) {
                                    var text = v;
                                    if(this.valueField){
                                        var r = this.findRecord(this.valueField, v);
                                        if(r){
                                            text = r.data[this.displayField];
                                            if ( !(String(r.data['LpuRegion_Descr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_Descr']) == "") )
                                            {
                                                if (r.data['LpuRegion_Descr']) {
                                                    text = text + ' ( '+ r.data['LpuRegion_Descr'] + ' )';
                                                }
                                            }
                                        } else if(this.valueNotFoundText !== undefined){
                                            text = this.valueNotFoundText;
                                        }
                                    }
                                    this.lastSelectionText = text;
                                    if(this.hiddenField){
                                        this.hiddenField.value = v;
                                    }
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    this.value = v;

                                },
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'LpuRegion_id'
                                    }, [
                                        {name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
                                        {name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
                                        {name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
                                        {name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
                                        {name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
                                    ]),
                                    url: C_LPUREGION_LIST
                                }),
                                tabIndex: 2106,
								//tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_Name}</div></tpl>',
                                tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_Name} {[ (!values.LpuRegion_Descr || String(values.LpuRegion_Descr).toUpperCase() == "NULL" || String(values.LpuRegion_Descr) == "") ? "" : "( "+ values.LpuRegion_Descr +" )"]}</div></tpl>',
                                triggerAction: 'all',
                                typeAhead: true,
                                typeAheadDelay: 1,
                                valueField: 'LpuRegion_id',
                                width : 400,
                                xtype: 'combo'
                            },
                            {
                                allowBlank: true,
                                displayField: 'LpuRegion_FapName',
                                fieldLabel: 'ФАП Участок',
                                forceSelection: true,
                                hiddenName: 'LpuRegion_Fapid',
                                hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),//getRegionNick() != 'perm',
                                hideLabel: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),//getRegionNick() != 'perm',
                                id: 'PSPCAW_LpuRegion_Fapid',
                                minChars: 1,
                                mode: 'local',
                                queryDelay: 1,
                                setValue: function(v) {
                                    var text = v;
                                    if(this.valueField){
                                        var r = this.findRecord(this.valueField, v);
                                        if(r){
                                            text = r.data[this.displayField];
                                            if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
                                            {
                                                if (r.data['LpuRegion_FapDescr']) {
                                                    text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
                                                }
                                            }
                                        } else if(this.valueNotFoundText !== undefined){
                                            text = this.valueNotFoundText;
                                        }
                                    }
                                    this.lastSelectionText = text;
                                    if(this.hiddenField){
                                        this.hiddenField.value = v;
                                    }
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    this.value = v;
                                },
                                lastQuery: '',
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'LpuRegion_Fapid'
                                    }, [
                                        {name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
                                        {name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
                                        {name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
                                    ]),
                                    url: '/?c=LpuRegion&m=getLpuRegionListFeld'
                                }),
                                tabIndex: 2106,
                                tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
                                triggerAction: 'all',
                                typeAhead: true,
                                typeAheadDelay: 1,
                                valueField: 'LpuRegion_Fapid',
                                width : 400,
                                xtype: 'combo'
                            },
                            {
                                fieldLabel: 'Пол',
                                hiddenName: 'Sex_id',
                                id: 'PSPCA_Sex_id',
                                tabIndex: this.tabIndexBase + 21,
                                width: 150,
                                xtype: 'swpersonsexcombo'
                            },
                            {
                                allowNegative: false,
                                fieldLabel: 'Возраст с',
                                name: 'PersonAge_Min',
                                id: 'PSPCA_PersonAge_Min',
                                tabIndex: this.tabIndexBase + 11,
                                width: 61,
                                xtype: 'numberfield'
                            },
                            {
                                allowNegative: false,
                                fieldLabel: 'Возраст по',
                                name: 'PersonAge_Max',
                                id: 'PSPCA_PersonAge_Max',
                                tabIndex: this.tabIndexBase + 11,
                                width: 61,
                                xtype: 'numberfield'
                            },

                            //{ name: 'PAddress_begDate', xtype: 'hidden', id: 'PSPCA_PAddress_begDate'},
                            { xtype: 'hidden', name: 'PAddress_Zip', id: 'PSPCA_PAddress_Zip'},
                            { xtype: 'hidden', name: 'PKLCountry_id', id: 'PSPCA_PKLCountry_id' },
                            { xtype: 'hidden', name: 'PKLRGN_id', id: 'PSPCA_PKLRGN_id'},
                            { xtype: 'hidden', name: 'PKLRGNSocr_id', id: 'PSPCA_PKLRGNSocr_id'},
                            { xtype: 'hidden', name: 'PKLSubRGN_id', id: 'PSPCA_PKLSubRGN_id'},
                            { xtype: 'hidden', name: 'PKLSubRGNSocr_id', id: 'PSPCA_PKLSubRGNSocr_id'},
                            { xtype: 'hidden', name: 'PKLCity_id', id: 'PSPCA_PKLCity_id'},
                            { xtype: 'hidden', name: 'PKLCitySocr_id', id: 'PSPCA_PKLCitySocr_id'},
                            { xtype: 'hidden', name: 'PPersonSprTerrDop_id', id: 'PSPCA_PPersonSprTerrDop_id'},
                            { xtype: 'hidden', name: 'PKLTown_id', id: 'PSPCA_PKLTown_id'},
                            { xtype: 'hidden', name: 'PKLTownSocr_id', id: 'PSPCA_PKLTownSocr_id'},
                            { xtype: 'hidden', name: 'PKLStreet_id', id: 'PSPCA_PKLStreet_id'},
                            { xtype: 'hidden', name: 'PKLStreetSocr_id', id: 'PSPCA_PKLStreetSocr_id'},
                            { xtype: 'hidden', name: 'PAddress_House', id: 'PSPCA_PAddress_House'},
                            { xtype: 'hidden', name: 'PAddress_Corpus', id: 'PSPCA_PAddress_Corpus'},
                            { xtype: 'hidden', name: 'PAddress_Flat', id: 'PSPCA_PAddress_Flat'},
                            { xtype: 'hidden', name: 'PAddress_Address', id: 'PSPCA_PAddress_Address'},

                            //{ name: 'UAddress_begDate', xtype: 'hidden', id: 'PSPCA_UAddress_begDate'},
                            { xtype: 'hidden', name: 'UAddress_Zip', id: 'PSPCA_UAddress_Zip'},
                            { xtype: 'hidden', name: 'UKLCountry_id', id: 'PSPCA_UKLCountry_id' },
                            { xtype: 'hidden', name: 'UKLRGN_id', id: 'PSPCA_UKLRGN_id'},
                            { xtype: 'hidden', name: 'UKLRGNSocr_id', id: 'PSPCA_UKLRGNSocr_id'},
                            { xtype: 'hidden', name: 'UKLSubRGN_id', id: 'PSPCA_UKLSubRGN_id'},
                            { xtype: 'hidden', name: 'UKLSubRGNSocr_id', id: 'PSPCA_UKLSubRGNSocr_id'},
                            { xtype: 'hidden', name: 'UKLCity_id', id: 'PSPCA_UKLCity_id'},
                            { xtype: 'hidden', name: 'UKLCitySocr_id', id: 'PSPCA_UKLCitySocr_id'},
                            { xtype: 'hidden', name: 'UPersonSprTerrDop_id', id: 'PSPCA_UPersonSprTerrDop_id'},
                            { xtype: 'hidden', name: 'UKLTown_id', id: 'PSPCA_UKLTown_id'},
                            { xtype: 'hidden', name: 'UKLTownSocr_id', id: 'PSPCA_UKLTownSocr_id'},
                            { xtype: 'hidden', name: 'UKLStreet_id', id: 'PSPCA_UKLStreet_id'},
                            { xtype: 'hidden', name: 'UKLStreetSocr_id', id: 'PSPCA_UKLStreetSocr_id'},
                            { xtype: 'hidden', name: 'UAddress_House', id: 'PSPCA_UAddress_House'},
                            { xtype: 'hidden', name: 'UAddress_Corpus', id: 'PSPCA_UAddress_Corpus'},
                            { xtype: 'hidden', name: 'UAddress_Flat', id: 'PSPCA_UAddress_Flat'},
                            { xtype: 'hidden', name: 'UAddress_Address', id: 'PSPCA_UAddress_Address'},

                            new sw.Promed.TripleTriggerField ({
                            enableKeyEvents: true,
                            fieldLabel: 'Адрес проживания',
                            id: 'PSPCA_PAddress_AddressText',
                            name: 'PAddress_AddressText',
                            readOnly: true,
                            tabIndex: TABINDEX_PEF + 10,
                            trigger1Class: 'x-form-search-trigger',
                            trigger2Class: 'x-form-equil-trigger',
                            trigger3Class: 'x-form-clear-trigger',
                            width: 600,
                                listeners: {
                                    'keydown': function(inp, e) {
                                        if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
                                            if ( e.F4 == e.getKey() )
                                                inp.onTrigger1Click();
                                            if ( e.F2 == e.getKey() )
                                                inp.onTrigger2Click();
                                            if ( e.DELETE == e.getKey() && e.altKey)
                                                inp.onTrigger3Click();
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
                                            if ( Ext.isIE ) {
                                                e.browserEvent.keyCode = 0;
                                                e.browserEvent.which = 0;
                                            }
                                            return false;
                                        }
                                    },
                                    'keyup': function( inp, e ) {
                                        if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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
                                            if ( Ext.isIE ) {
                                                e.browserEvent.keyCode = 0;
                                                e.browserEvent.which = 0;
                                            }
                                            return false;
                                        }
                                    }
                                },
                                onTrigger3Click: function() {
                                    var ownerForm = this.ownerCt;
                                    if (!ownerForm.findById('PSPCA_PAddress_AddressText').disabled)
                                    {
                                        ownerForm.findById('PSPCA_PAddress_Zip').setValue('');
                                        ownerForm.findById('PSPCA_PKLCountry_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLRGN_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLRGNSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLSubRGN_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLSubRGNSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLCity_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLCitySocr_id').setValue('');
                                        ownerForm.findById('PSPCA_PPersonSprTerrDop_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLTown_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLTownSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLStreet_id').setValue('');
                                        ownerForm.findById('PSPCA_PKLStreetSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_PAddress_House').setValue('');
                                        ownerForm.findById('PSPCA_PAddress_Corpus').setValue('');
                                        ownerForm.findById('PSPCA_PAddress_Flat').setValue('');
                                        ownerForm.findById('PSPCA_PAddress_Address').setValue('');
                                        ownerForm.findById('PSPCA_PAddress_AddressText').setValue('');
                                    }
                                },
                                onTrigger2Click: function() {
                                    var ownerForm = this.ownerCt;
                                    ownerForm.findById('PSPCA_PAddress_Zip').setValue(ownerForm.findById('PSPCA_UAddress_Zip').getValue());
                                    ownerForm.findById('PSPCA_PKLCountry_id').setValue(ownerForm.findById('PSPCA_UKLCountry_id').getValue());
                                    ownerForm.findById('PSPCA_PKLRGN_id').setValue(ownerForm.findById('PSPCA_UKLRGN_id').getValue());
                                    ownerForm.findById('PSPCA_PKLRGNSocr_id').setValue(ownerForm.findById('PSPCA_UKLRGNSocr_id').getValue());
                                    ownerForm.findById('PSPCA_PKLSubRGN_id').setValue(ownerForm.findById('PSPCA_UKLSubRGN_id').getValue());
                                    ownerForm.findById('PSPCA_PKLSubRGNSocr_id').setValue(ownerForm.findById('PSPCA_UKLSubRGNSocr_id').getValue());
                                    ownerForm.findById('PSPCA_PKLCity_id').setValue(ownerForm.findById('PSPCA_UKLCity_id').getValue());
                                    ownerForm.findById('PSPCA_PKLCitySocr_id').setValue(ownerForm.findById('PSPCA_UKLCitySocr_id').getValue());
                                    ownerForm.findById('PSPCA_PPersonSprTerrDop_id').setValue(ownerForm.findById('PSPCA_UPersonSprTerrDop_id').getValue());
                                    ownerForm.findById('PSPCA_PKLTown_id').setValue(ownerForm.findById('PSPCA_UKLTown_id').getValue());
                                    ownerForm.findById('PSPCA_PKLTownSocr_id').setValue(ownerForm.findById('PSPCA_UKLTownSocr_id').getValue());
                                    ownerForm.findById('PSPCA_PKLStreet_id').setValue(ownerForm.findById('PSPCA_UKLStreet_id').getValue());
                                    ownerForm.findById('PSPCA_PKLStreetSocr_id').setValue(ownerForm.findById('PSPCA_UKLStreetSocr_id').getValue());
                                    ownerForm.findById('PSPCA_PAddress_House').setValue(ownerForm.findById('PSPCA_UAddress_House').getValue());
                                    ownerForm.findById('PSPCA_PAddress_Corpus').setValue(ownerForm.findById('PSPCA_UAddress_Corpus').getValue());
                                    ownerForm.findById('PSPCA_PAddress_Flat').setValue(ownerForm.findById('PSPCA_UAddress_Flat').getValue());
                                    ownerForm.findById('PSPCA_PAddress_Address').setValue(ownerForm.findById('PSPCA_UAddress_Address').getValue());
                                    ownerForm.findById('PSPCA_PAddress_AddressText').setValue(ownerForm.findById('PSPCA_UAddress_AddressText').getValue());
                                },
                                onTrigger1Click: function() {
                                    var ownerForm = this.ownerCt;
                                    if (!ownerForm.findById('PSPCA_PAddress_AddressText').disabled)
                                    {
                                        getWnd('swAddressEditWindow').show({
                                            fields: {
                                                Address_ZipEdit: ownerForm.findById('PSPCA_PAddress_Zip').getValue(),
                                                KLCountry_idEdit: ownerForm.findById('PSPCA_PKLCountry_id').getValue(),
                                                KLRgn_idEdit: ownerForm.findById('PSPCA_PKLRGN_id').getValue(),
                                                KLSubRGN_idEdit: ownerForm.findById('PSPCA_PKLSubRGN_id').getValue(),
                                                KLCity_idEdit: ownerForm.findById('PSPCA_PKLCity_id').getValue(),
                                                PersonSprTerrDop_idEdit: ownerForm.findById('PSPCA_PPersonSprTerrDop_id').getValue(),
                                                KLTown_idEdit: ownerForm.findById('PSPCA_PKLTown_id').getValue(),
                                                KLStreet_idEdit: ownerForm.findById('PSPCA_PKLStreet_id').getValue(),
                                                Address_HouseEdit: ownerForm.findById('PSPCA_PAddress_House').getValue(),
                                                Address_CorpusEdit: ownerForm.findById('PSPCA_PAddress_Corpus').getValue(),
                                                Address_FlatEdit: ownerForm.findById('PSPCA_PAddress_Flat').getValue(),
                                                Address_AddressEdit: ownerForm.findById('PSPCA_PAddress_AddressText').getValue(),
                                                addressType: 1
                                            },
                                            callback: function(values) {
                                                ownerForm.findById('PSPCA_PAddress_Zip').setValue(values.Address_ZipEdit);
                                                ownerForm.findById('PSPCA_PKLCountry_id').setValue(values.KLCountry_idEdit);
                                                ownerForm.findById('PSPCA_PKLRGN_id').setValue(values.KLRgn_idEdit);
                                                ownerForm.findById('PSPCA_PKLRGNSocr_id').setValue(values.KLRGN_Socr);
                                                ownerForm.findById('PSPCA_PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
                                                ownerForm.findById('PSPCA_PKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
                                                ownerForm.findById('PSPCA_PKLCity_id').setValue(values.KLCity_idEdit);
                                                ownerForm.findById('PSPCA_PKLCitySocr_id').setValue(values.KLCity_Socr);
                                                ownerForm.findById('PSPCA_PPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
                                                ownerForm.findById('PSPCA_PKLTown_id').setValue(values.KLTown_idEdit);
                                                ownerForm.findById('PSPCA_PKLTownSocr_id').setValue(values.KLTown_Socr);
                                                ownerForm.findById('PSPCA_PKLStreet_id').setValue(values.KLStreet_idEdit);
                                                ownerForm.findById('PSPCA_PKLStreetSocr_id').setValue(values.KLStreet_Socr);
                                                ownerForm.findById('PSPCA_PAddress_House').setValue(values.Address_HouseEdit);
                                                ownerForm.findById('PSPCA_PAddress_Corpus').setValue(values.Address_CorpusEdit);
                                                ownerForm.findById('PSPCA_PAddress_Flat').setValue(values.Address_FlatEdit);
                                                ownerForm.findById('PSPCA_PAddress_Address').setValue(values.Address_AddressEdit);
                                                ownerForm.findById('PSPCA_PAddress_AddressText').setValue(values.Address_AddressEdit);
                                                ownerForm.findById('PSPCA_PAddress_AddressText').focus(true, 500);

                                            },
                                            onClose: function() {
                                                ownerForm.findById('PSPCA_PAddress_AddressText').focus(true, 500);
                                            }
                                        })
                                    }
                                }
                            }),

                            new sw.Promed.TripleTriggerField ({
                                enableKeyEvents: true,
                                fieldLabel: 'Адрес регистрации',
                                id: 'PSPCA_UAddress_AddressText',
                                name: 'UAddress_AddressText',
                                readOnly: true,
                                tabIndex: TABINDEX_PEF + 10,
                                trigger1Class: 'x-form-search-trigger',
                                trigger2Class: 'x-form-equil-trigger',
                                trigger3Class: 'x-form-clear-trigger',
                                width: 600,
                                listeners: {
                                    'keydown': function(inp, e) {
                                        if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
                                            if ( e.F4 == e.getKey() )
                                                inp.onTrigger1Click();
                                            if ( e.F2 == e.getKey() )
                                                inp.onTrigger2Click();
                                            if ( e.DELETE == e.getKey() && e.altKey)
                                                inp.onTrigger3Click();
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
                                            if ( Ext.isIE ) {
                                                e.browserEvent.keyCode = 0;
                                                e.browserEvent.which = 0;
                                            }
                                            return false;
                                        }
                                    },
                                    'keyup': function( inp, e ) {
                                        if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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
                                            if ( Ext.isIE ) {
                                                e.browserEvent.keyCode = 0;
                                                e.browserEvent.which = 0;
                                            }
                                            return false;
                                        }
                                    }
                                },
                                onTrigger3Click: function() {
                                    var ownerForm = this.ownerCt;
                                    if (!ownerForm.findById('PSPCA_UAddress_AddressText').disabled)
                                    {
                                        ownerForm.findById('PSPCA_UAddress_Zip').setValue('');
                                        ownerForm.findById('PSPCA_UKLCountry_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLRGN_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLRGNSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLSubRGN_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLSubRGNSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLCity_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLCitySocr_id').setValue('');
                                        ownerForm.findById('PSPCA_UPersonSprTerrDop_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLTown_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLTownSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLStreet_id').setValue('');
                                        ownerForm.findById('PSPCA_UKLStreetSocr_id').setValue('');
                                        ownerForm.findById('PSPCA_UAddress_House').setValue('');
                                        ownerForm.findById('PSPCA_UAddress_Corpus').setValue('');
                                        ownerForm.findById('PSPCA_UAddress_Flat').setValue('');
                                        ownerForm.findById('PSPCA_UAddress_Address').setValue('');
                                        ownerForm.findById('PSPCA_UAddress_AddressText').setValue('');
                                    }
                                },
                                onTrigger2Click: function() {
                                    var ownerForm = this.ownerCt;
                                    ownerForm.findById('PSPCA_UAddress_Zip').setValue(ownerForm.findById('PSPCA_PAddress_Zip').getValue());
                                    ownerForm.findById('PSPCA_UKLCountry_id').setValue(ownerForm.findById('PSPCA_PKLCountry_id').getValue());
                                    ownerForm.findById('PSPCA_UKLRGN_id').setValue(ownerForm.findById('PSPCA_PKLRGN_id').getValue());
                                    ownerForm.findById('PSPCA_UKLRGNSocr_id').setValue(ownerForm.findById('PSPCA_PKLRGNSocr_id').getValue());
                                    ownerForm.findById('PSPCA_UKLSubRGN_id').setValue(ownerForm.findById('PSPCA_PKLSubRGN_id').getValue());
                                    ownerForm.findById('PSPCA_UKLSubRGNSocr_id').setValue(ownerForm.findById('PSPCA_PKLSubRGNSocr_id').getValue());
                                    ownerForm.findById('PSPCA_UKLCity_id').setValue(ownerForm.findById('PSPCA_PKLCity_id').getValue());
                                    ownerForm.findById('PSPCA_UKLCitySocr_id').setValue(ownerForm.findById('PSPCA_PKLCitySocr_id').getValue());
                                    ownerForm.findById('PSPCA_UPersonSprTerrDop_id').setValue(ownerForm.findById('PSPCA_PPersonSprTerrDop_id').getValue());
                                    ownerForm.findById('PSPCA_UKLTown_id').setValue(ownerForm.findById('PSPCA_PKLTown_id').getValue());
                                    ownerForm.findById('PSPCA_UKLTownSocr_id').setValue(ownerForm.findById('PSPCA_PKLTownSocr_id').getValue());
                                    ownerForm.findById('PSPCA_UKLStreet_id').setValue(ownerForm.findById('PSPCA_PKLStreet_id').getValue());
                                    ownerForm.findById('PSPCA_UKLStreetSocr_id').setValue(ownerForm.findById('PSPCA_PKLStreetSocr_id').getValue());
                                    ownerForm.findById('PSPCA_UAddress_House').setValue(ownerForm.findById('PSPCA_PAddress_House').getValue());
                                    ownerForm.findById('PSPCA_UAddress_Corpus').setValue(ownerForm.findById('PSPCA_PAddress_Corpus').getValue());
                                    ownerForm.findById('PSPCA_UAddress_Flat').setValue(ownerForm.findById('PSPCA_PAddress_Flat').getValue());
                                    ownerForm.findById('PSPCA_UAddress_Address').setValue(ownerForm.findById('PSPCA_PAddress_Address').getValue());
                                    ownerForm.findById('PSPCA_UAddress_AddressText').setValue(ownerForm.findById('PSPCA_PAddress_AddressText').getValue());
                                },
                                onTrigger1Click: function() {
                                    var ownerForm = this.ownerCt;
                                    if (!ownerForm.findById('PSPCA_UAddress_AddressText').disabled)
                                    {
                                        getWnd('swAddressEditWindow').show({
                                            fields: {
                                                Address_ZipEdit: ownerForm.findById('PSPCA_UAddress_Zip').getValue(),
                                                KLCountry_idEdit: ownerForm.findById('PSPCA_UKLCountry_id').getValue(),
                                                KLRgn_idEdit: ownerForm.findById('PSPCA_UKLRGN_id').getValue(),
                                                KLSubRGN_idEdit: ownerForm.findById('PSPCA_UKLSubRGN_id').getValue(),
                                                KLCity_idEdit: ownerForm.findById('PSPCA_UKLCity_id').getValue(),
                                                PersonSprTerrDop_idEdit: ownerForm.findById('PSPCA_UPersonSprTerrDop_id').getValue(),
                                                KLTown_idEdit: ownerForm.findById('PSPCA_UKLTown_id').getValue(),
                                                KLStreet_idEdit: ownerForm.findById('PSPCA_UKLStreet_id').getValue(),
                                                Address_HouseEdit: ownerForm.findById('PSPCA_UAddress_House').getValue(),
                                                Address_CorpusEdit: ownerForm.findById('PSPCA_UAddress_Corpus').getValue(),
                                                Address_FlatEdit: ownerForm.findById('PSPCA_UAddress_Flat').getValue(),
                                                Address_AddressEdit: ownerForm.findById('PSPCA_UAddress_AddressText').getValue(),
                                                addressType: 1
                                            },
                                            callback: function(values) {
                                                ownerForm.findById('PSPCA_UAddress_Zip').setValue(values.Address_ZipEdit);
                                                ownerForm.findById('PSPCA_UKLCountry_id').setValue(values.KLCountry_idEdit);
                                                ownerForm.findById('PSPCA_UKLRGN_id').setValue(values.KLRgn_idEdit);
                                                ownerForm.findById('PSPCA_UKLRGNSocr_id').setValue(values.KLRGN_Socr);
                                                ownerForm.findById('PSPCA_UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
                                                ownerForm.findById('PSPCA_UKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
                                                ownerForm.findById('PSPCA_UKLCity_id').setValue(values.KLCity_idEdit);
                                                ownerForm.findById('PSPCA_UKLCitySocr_id').setValue(values.KLCity_Socr);
                                                ownerForm.findById('PSPCA_UPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
                                                ownerForm.findById('PSPCA_UKLTown_id').setValue(values.KLTown_idEdit);
                                                ownerForm.findById('PSPCA_UKLTownSocr_id').setValue(values.KLTown_Socr);
                                                ownerForm.findById('PSPCA_UKLStreet_id').setValue(values.KLStreet_idEdit);
                                                ownerForm.findById('PSPCA_UKLStreetSocr_id').setValue(values.KLStreet_Socr);
                                                ownerForm.findById('PSPCA_UAddress_House').setValue(values.Address_HouseEdit);
                                                ownerForm.findById('PSPCA_UAddress_Corpus').setValue(values.Address_CorpusEdit);
                                                ownerForm.findById('PSPCA_UAddress_Flat').setValue(values.Address_FlatEdit);
                                                ownerForm.findById('PSPCA_UAddress_Address').setValue(values.Address_AddressEdit);
                                                ownerForm.findById('PSPCA_UAddress_AddressText').setValue(values.Address_AddressEdit);
                                                ownerForm.findById('PSPCA_UAddress_AddressText').focus(true, 500);

                                            },
                                            onClose: function() {
                                                ownerForm.findById('PSPCA_UAddress_AddressText').focus(true, 500);
                                            }
                                        })
                                    }
                                }
                            })
                            ],
                        buttons : [
                            {
                                text: BTN_FILTER,
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
        this.PersonCardGrid = new sw.Promed.ViewFrame(
            {
                actions:
                    [
                        {
                            name: 'action_add',
                            hidden: true
                        },
                        {
                            name: 'action_edit',
                            hidden: true
                        },
                        {
                            name: 'action_view',
                            hidden: true
                        },
                        {
                            name: 'action_delete',
                            hidden: true
                        },
                        {
                            name: 'action_refresh',
                            hidden: true
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
                dataUrl: '?c=Person&m=getPersonGridPersonCardAuto',
                id: 'PSPCA_Grid',
                pageSize: 100,
                root: 'data',
                toolbar: true,
                totalProperty: 'totalCount',
                paging: true,
                region: 'center',
                onCellDblClick: function (grid, rowIdx, colIdx, event){
                    var Person_id = Number(grid.getStore().getAt(rowIdx).get('Person_id'));
                    var params = new Object();
                    params.Person_id = Person_id;
                    ShowWindow('swPersonCardHistoryWindow', params);
                },
				onLoadData: function() {
					var base_form = Ext.getCmp('PersonSearchPersonCardAutoWindow');
					var records = new Array();
					form.PersonCardGrid.getGrid().getStore().each(function (rec){
						if (!Ext.isEmpty(rec.get('Person_id'))) {
							var index = form.PersonDataChecked.indexOf(rec.get('Person_id'));
							if (index > -1) {
								rec.set('Is_Checked', 1);
							}
						}
					});
				},
                stringfields:
                    [
						{name: 'check', sortable: false, width: 40, renderer: this.checkRenderer,
							header: '<input type="checkbox" id="PSPCAW_checkAll" onClick="getWnd(\'swPersonSearchPersonCardAutoWindow\').checkAll(this.checked);">'
						},
						{name: 'Is_Checked',type: 'int', header: 'is_checked', hidden: true},
                        {name: 'Person_id', type: 'int', hidden: true, key:true},
                        {name: 'Person_FIO',  type: 'string', header: 'ФИО ЗЛ', width: 255},
                        {name: 'Person_BirthDay',  type: 'date', header: 'Дата рождения', width: 75},
                        {name: 'Sex_Name',  type: 'string', header: 'Пол', width: 70},
                        //{name: 'PersonStatus',  type: 'string', header: 'Прикреплен на текущий момент', width: 230},
                        {name: 'Person_IsBDZ',type: 'checkbox',header: 'БДЗ',width:40},
                        {name: 'Lpu_Name',  type: 'string', header: 'МО прикрепления', width: 150},
                        {name: 'LpuRegion_Name',  type: 'string', header: 'Участок', width: 120},
                        {name: 'LpuRegion_FapName', type: 'string', header: 'ФАП участок',width:100, hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza'])/*getRegionNick() != 'perm'*/},
                        {name: 'PAddress_Name',  type: 'string', header: 'Адрес проживания', width: 380},
                        {name: 'UAddress_Name',  type: 'string', header: 'Адрес регистрации', width: 380}
                    ]
            });
        Ext.apply(this, {
            layout: 'fit',
            buttons: [
                {
                    text: 'Прикрепление',
                    handler: function() {
                        form.doAddPersonCards();
                    }
                },
                {
                    text: '-'
                },
                HelpButton(this),
                {
                    handler: function() {
                        Ext.getCmp('PersonSearchPersonCardAutoWindow').hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCLOSE
                }
            ],
            maximizable: true,
            items: [
                new Ext.TabPanel({
                    id: 'pacient_tab_panel',
                    layoutOnTabChange: true,
                    plain: true,
                    defaults: {bodyStyle: 'padding:2px'},
                    items: [
                        {
                            height: Ext.isIE ? 500 : 700,
                            id: 'pacient_tab',
                            title: 'Прикрепления',
                            layout:'border',
                            items: [
                                this.FilterPanel,
                                this.PersonCardGrid
                            ]
                        },
                        {
                            height: Ext.isIE ? 500 : 700,
                            id: 'log_tab',
                            layout:'border',
                            title: 'Лог',
                            items: [{
                                region: 'center',
                                xtype: 'textarea',
                                hideLabel: true,
                                name: 'PSPCA_Log',
                                id: 'PSPCA_Log',
                                anchor:'100%',
                                height: 667
                            }]
                        }]
                })
            ]
        });
        sw.Promed.swPersonSearchPersonCardAutoWindow.superclass.initComponent.apply(this, arguments);
    },

	checkRenderer: function(v, p, record) {
		var id = record.get('Person_id');
		var value = 'value="'+id+'"';
		var checked = record.get('Is_Checked')!=0 ? ' checked="checked"' : '';
		var onclick = 'onClick="getWnd(\'swPersonSearchPersonCardAutoWindow\').checkOne(this.value);"';

		return '<input type="checkbox" '+value+' '+checked+' '+onclick+'>';

	},
	checkAll: function(check)
	{
		var form = this;
		var array_index = -1;
		if(check)
			this.PersonCardGrid.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 1);
				array_index = form.PersonDataChecked.indexOf(record.get('Person_id'));
				if(array_index == -1){
					form.PersonDataChecked.push(record.get('Person_id'));
				}
			});
		else
			this.PersonCardGrid.getGrid().getStore().each(function(record){
				record.set('Is_Checked', 0);
				array_index = form.PersonDataChecked.indexOf(record.get('Person_id'));
				if(array_index > -1){
					form.PersonDataChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
				}
			});
	},
	checkOne: function(id){
		var form = this;
		var Person_id = id;
		var array_index = form.PersonDataChecked.indexOf(Person_id);
		this.PersonCardGrid.getGrid().getStore().each(function(record){
			if(record.get('Person_id') == Person_id){
				if(record.get('Is_Checked') == 0) //Было 0, т.е. при нажатии устанавливаем галочку
				{
					record.set('Is_Checked',1);
					if(array_index == -1){
						form.PersonDataChecked.push(Person_id);
					}
				}
				else{ //Было 1, т.е. при нажатии снимаем галочку
					record.set('Is_Checked',0);
					if(array_index > -1){
						form.PersonDataChecked.splice(array_index, 1); //Убираем из массива отмеченных людей
					}
				}
			}
		});
		log(form.PersonDataChecked);
	},
    show: function(){
        sw.Promed.swPersonSearchPersonCardAutoWindow.superclass.show.apply(this, arguments);
        this.onHide = Ext.emptyFn;
        var form = Ext.getCmp('PersonSearchPersonCardAutoWindow');
		var combo = form.findById('PSPCA_Org_id');
        var region_fap_combo = form.findById('PSPCAW_LpuRegion_Fapid');
        if(getWnd('swWorkPlacePolkaRegWindow').isVisible() || getWnd('swWorkPlacePolkaRegWindowExt6').isVisible() || getWnd('swLpuAdminWorkPlaceWindow').isVisible() || getWnd('swLpuAdminWorkPlaceWindowExt6').isVisible())
        {
            //form.findById('PSPCA_Lpu_id').setValue(getGlobalOptions().lpu_id);
            //form.findById('PSPCA_Lpu_id').disable();
			combo.getStore().loadData([
				{
					Org_id:getGlobalOptions().org_id,
					Org_Name:getGlobalOptions().org_nick
				}
			]);
			combo.setValue(getGlobalOptions().org_id);
			combo.disable();
            region_fap_combo.setValue('');
            region_fap_combo.getStore().removeAll();
            region_fap_combo.getStore().load({
                params:{
                    Org_id: getGlobalOptions().org_id,
                    add_without_region_line: true
                },
                callback: function() {
                    form.findById('PSPCAW_LpuRegion_Fapid').clearValue();
                }
            })
        }
        else{
			form.findById('PSPCA_Org_id').setValue('');
			form.findById('PSPCA_Org_id').enable();
            region_fap_combo.getStore().removeAll();
        }

        form.findById('PSPCA_LpuRegionTypeCombo').setValue('');
        form.findById('PSPCA_LpuRegionTypeCombo').getStore().filterBy(
            function(record)
            {
                if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa'])/*getRegionNick() == 'perm'*/)
                    return false;
                else
                    return true;
            }
        );
        form.findById('PSPCLpuRegion_id').setValue('');
        form.findById('PSPCA_Sex_id').setValue('');
        form.findById('PSPCA_PersonAge_Min').setValue('');
        form.findById('PSPCA_PersonAge_Max').setValue('');


        form.findById('PSPCA_PAddress_Zip').setValue('');
        form.findById('PSPCA_PKLCountry_id').setValue('');
        form.findById('PSPCA_PKLRGN_id').setValue('');
        form.findById('PSPCA_PKLRGNSocr_id').setValue('');
        form.findById('PSPCA_PKLSubRGN_id').setValue('');
        form.findById('PSPCA_PKLSubRGNSocr_id').setValue('');
        form.findById('PSPCA_PKLCity_id').setValue('');
        form.findById('PSPCA_PKLCitySocr_id').setValue('');
        form.findById('PSPCA_PPersonSprTerrDop_id').setValue('');
        form.findById('PSPCA_PKLTown_id').setValue('');
        form.findById('PSPCA_PKLTownSocr_id').setValue('');
        form.findById('PSPCA_PKLStreet_id').setValue('');
        form.findById('PSPCA_PKLStreetSocr_id').setValue('');
        form.findById('PSPCA_PAddress_House').setValue('');
        form.findById('PSPCA_PAddress_Corpus').setValue('');
        form.findById('PSPCA_PAddress_Flat').setValue('');
        form.findById('PSPCA_PAddress_Address').setValue('');
        form.findById('PSPCA_PAddress_AddressText').setValue('');

        form.findById('PSPCA_UAddress_Zip').setValue('');
        form.findById('PSPCA_UKLCountry_id').setValue('');
        form.findById('PSPCA_UKLRGN_id').setValue('');
        form.findById('PSPCA_UKLRGNSocr_id').setValue('');
        form.findById('PSPCA_UKLSubRGN_id').setValue('');
        form.findById('PSPCA_UKLSubRGNSocr_id').setValue('');
        form.findById('PSPCA_UKLCity_id').setValue('');
        form.findById('PSPCA_UKLCitySocr_id').setValue('');
        form.findById('PSPCA_UPersonSprTerrDop_id').setValue('');
        form.findById('PSPCA_UKLTown_id').setValue('');
        form.findById('PSPCA_UKLTownSocr_id').setValue('');
        form.findById('PSPCA_UKLStreet_id').setValue('');
        form.findById('PSPCA_UKLStreetSocr_id').setValue('');
        form.findById('PSPCA_UAddress_House').setValue('');
        form.findById('PSPCA_UAddress_Corpus').setValue('');
        form.findById('PSPCA_UAddress_Flat').setValue('');
        form.findById('PSPCA_UAddress_Address').setValue('');
        form.findById('PSPCA_UAddress_AddressText').setValue('');

        this.findById('pacient_tab_panel').setActiveTab(1);
        this.findById('pacient_tab_panel').setActiveTab(0);
        var Log = Ext.getCmp('PSPCA_Log');
        Log.setValue('');
        var PersonGrid = form.findById('PSPCA_Grid').ViewGridPanel;
        //if(getRegionNick() == 'perm')
		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']))
        {
            Ext.getCmp('PSPCA_Grid').getGrid().getColumnModel().setColumnHeader(8,'Основной участок');
            form.findById('PSPCLpuRegion_id').setFieldLabel('Основной участок');
            form.findById('PSPCA_LpuRegionTypeCombo').setFieldLabel('Тип основного участка');
            //var region_fap_combo = form.findById('PSPCAW_LpuRegion_Fapid');
            //region_fap_combo.getStore().load();
        }
        else
        {
            Ext.getCmp('PSPCA_Grid').getGrid().getColumnModel().setColumnHeader(8,'Участок');
            form.findById('PSPCA_LpuRegionTypeCombo').setFieldLabel('Тип участка');
            form.findById('PSPCLpuRegion_id').setFieldLabel('Участок');
        }
        PersonGrid.getStore().removeAll();
        this.restore();
        this.center();
        this.maximize();
        this.doLayout();

		this.PersonDataChecked = new Array();
    }
});