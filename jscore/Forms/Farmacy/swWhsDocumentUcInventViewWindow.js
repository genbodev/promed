/**
 * swWhsDocumentUcInventViewWindow - окно просмотра списка инв. ведомостей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      09.2014
 * @comment
 */
sw.Promed.swWhsDocumentUcInventViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['inventarizatsionnyie_vedomosti_spisok'],
	layout: 'border',
	id: 'WhsDocumentUcInventViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params = form.getValues();

		params.start = 0;
		params.limit = 100;
		params.ARMType = this.ARMType;
		params.Org_id = this.ARMType == 'merch' ? getGlobalOptions().org_id : null;
		params.MedService_id = this.MedService_id;

		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
    setDefaultFilters: function() {
        var def_values = new Object();
        var dt = new Date();
        var dd = dt.getDate() > 9 ? dt.getDate() : '0'+dt.getDate();
        var mm = dt.getMonth() > 8 ? dt.getMonth()+1 : '0'+(dt.getMonth()+1);
        var yyyy = dt.getFullYear();

        def_values.WhsDocumentUc_DateRange = '01.'+mm+'.'+yyyy+' - '+dd+'.'+mm+'.'+yyyy;

        this.FilterPanel.getForm().setValues(def_values);
    },
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
        wnd.setDefaultFilters();
		wnd.SearchGrid.removeAll();
	},
	createAllDrugList_cntrl: function($cur, $remake) {
	    var wnd = this;
	    var selected_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
	    if (Number(selected_record.get('kolDoc')) == 0)
		wnd.createAllDrugList($cur, $remake);
	    else {
		var msg = 'Есть неисполненные накладные в количестве ' + selected_record.get('kolDoc') + 'шт.\n\
		    на дату ' + selected_record.get('minDate') + ' <br /> Продолжить?'
		sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: 'Внимание',
					buttons: {yes: 'Да', no: 'Нет'},
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
						    console.log ('yes');
						    wnd.createAllDrugList($cur, $remake);
						}
				}
		});
	    };
	},
	createAllDrugList: function($cur, $remake) {
	    /*
	     * 
	     * $cur - сформировать все ведомости (1) или сформировать текущую (2) 
	     * $remake - Сформировать ведомость (0) или переформироватьведомость (1)
	     */
	    var wnd = this;
		var str = '';
	    
	    if ($cur == 1) {
		var Cnt = wnd.SearchGrid.ViewGridPanel.getStore().data.items.length;
		for (var r = 0; r <= Cnt - 1; r++) {
		    record =  wnd.SearchGrid.ViewGridPanel.getStore().data.items[r].data;
		    //console.log(record); 
		    if (record.WhsDocumentStatusType_Code == 1 && record.Drug_Count == 0) {
			str += record.WhsDocumentUc_id + ',';
		    }

		}
	    }
	    else {
		var selected_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
		//console.log(selected_record);
		str = selected_record.get('WhsDocumentUc_id');
	    }
	    
	    console.log('str = ' + str); 
	    if (str.length > 0) {
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInvent_List: str,
					Remake : $remake
				},
				url: '/?c=WhsDocumentUcInvent&m=createDocumentUcInventDrugListAll',
				callback: function(options, success, response) {
					if (response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.success) {
							Ext.Msg.alert(lang['soobschenie'], lang['spisok_medikamentov_uspeshno_sformirovan']);
							wnd.doSearch();
						}
					}
				}
			});
		}
	   
	},
	createDrugList: function() {
		var wnd = this;
		var selected_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();

		if (selected_record && selected_record.get('WhsDocumentUcInvent_id') > 0) {
			if (selected_record.get('kolDoc') > 0) {
		    	Ext.Msg.alert(lang['soobschenie'], 'На дату проведения инвентаризации все расходные документы учета должны быть исполнены.');
		    	return false;
		    }
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInvent_id: selected_record.get('WhsDocumentUcInvent_id')
				},
				url: '/?c=WhsDocumentUcInvent&m=createDocumentUcInventDrugList',
				callback: function(options, success, response) {
					if (response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.success) {
							Ext.Msg.alert(lang['soobschenie'], lang['spisok_medikamentov_uspeshno_sformirovan']);
							wnd.doSearch();
						}
					}
				}
			});
		}
	},
	createDrugListAll: function() {
		var wnd = this;
		var cnt = wnd.SearchGrid.ViewGridPanel.getStore().data.items.length;
		var rec_arr = [];
		for (var r = 0; r <= cnt - 1; r++) {
		    record =  wnd.SearchGrid.ViewGridPanel.getStore().data.items[r].data;
		    if (record.kolDoc > 0) {
		    	Ext.Msg.alert(lang['soobschenie'], 'На дату проведения инвентаризации все расходные документы учета должны быть исполнены.');
		    	return false;
		    }
		    if (record.WhsDocumentStatusType_Code == 1 && record.Drug_Count == 0) {
				rec_arr.push(record.WhsDocumentUcInvent_id);
		    }
		}
		if(rec_arr.length > 0){
			var records = rec_arr.join(',');
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInvent_ids: records
				},
				url: '/?c=WhsDocumentUcInvent&m=createDocumentUcInventDrugListAllCommon',
				callback: function(options, success, response) {
					if (response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.success) {
							Ext.Msg.alert(lang['soobschenie'], lang['spisok_medikamentov_uspeshno_sformirovan']);
							wnd.doSearch();
						}
					}
				}
			});
		}
	},
	setApproved: function(approved) {
		var wnd = this;
		var selected_record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
		if (selected_record && selected_record.get('WhsDocumentUcInvent_id') > 0) {
			Ext.Ajax.request({
				params: {
					WhsDocumentUcInvent_id: selected_record.get('WhsDocumentUcInvent_id'),
					sign: approved
				},
				url: '/?c=WhsDocumentUcInvent&m=signWhsDocumentUcInvent',
				callback: function(options, success, response) {
					if (response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.success) {
							wnd.doSearch();
						}
					}
				}
			});
		}
	},
    setLpuFields: function() {
        var wnd = this;
        var is_lpu = (getGlobalOptions().orgtype == 'lpu');
        //var mo_level = (wnd.LpuSection_id > 0 && wnd.LpuBuilding_id > 0); //если не передано ни отделение ни подразделние, считаем что АРМ прописан на верхнем уровне МО

        if (wnd.ARMType == 'merch' && !Ext.isEmpty(this.Storage_pid)/* && !mo_level*/) { //не редактируется, если форма была вызвана под службой АРМ Товаровед и склад, прописанный на этой службе, имеет родителя
            wnd.lb_combo.setDisabled(true);
        } else {
            wnd.lb_combo.setDisabled(false);
        }
        wnd.ls_combo.setDisabled(false);

        if (is_lpu) {
            wnd.lb_combo.showContainer();
            wnd.ls_combo.showContainer();
            wnd.SearchGrid.setColumnHidden('LpuSection_Name', false);

            /*wnd.ls_combo.getStore().load({callback: function(){
                if (wnd.LpuSection_id > 0) {
                    var idx = wnd.ls_combo.getStore().findBy(function(rec) { return rec.get('LpuSection_id') == wnd.LpuSection_id; });
                    if (idx >= 0) {
                        wnd.ls_combo.setValue(wnd.LpuSection_id);
                    }
                }
            }});*/
            if (wnd.LpuSection_id > 0) {
                wnd.ls_combo.setValueById(wnd.LpuSection_id);
            }
            wnd.lb_combo.getStore().load({callback: function(){
                if (wnd.LpuBuilding_id > 0) {
                    var idx = wnd.lb_combo.getStore().findBy(function(rec) { return rec.get('LpuBuilding_id') == wnd.LpuBuilding_id; });
                    if (idx >= 0) {
                        wnd.lb_combo.setValue(wnd.LpuBuilding_id);
                    }
                }
                wnd.filterLpuSectionCombo();
                wnd.filterStorageCombo();
            }});
        } else {
            wnd.lb_combo.hideContainer();
            wnd.ls_combo.hideContainer();
            wnd.SearchGrid.setColumnHidden('LpuSection_Name', true);
            wnd.os_combo.getStore().load({
            	params:{ Org_id: getGlobalOptions().org_id },
            	callback: function(){
	                if (wnd.OrgStruct_id > 0) {
	                    var idx = wnd.os_combo.getStore().findBy(function(rec) { return rec.get('OrgStruct_id') == wnd.OrgStruct_id; });
	                    if (idx >= 0) {
	                        wnd.os_combo.setValue(wnd.OrgStruct_id);
	                        wnd.os_combo.fireEvent('change',wnd.os_combo,wnd.OrgStruct_id);
	                    }
	                }
	            }
	        });
	        wnd.os_combo.setDisabled(wnd.ARMType == 'merch' && (!Ext.isEmpty(wnd.LpuBuilding_id) || !Ext.isEmpty(wnd.LpuSection_id)));
        }
    },
    filterStorageZoneCombo: function(){
    	var base_form = this.FilterPanel.getForm();
    	if(getRegionNick().inlist(['krym'])){
    		var sz = base_form.findField('StorageZone_id').getValue();
			var params = {};
			params.Storage_id = this.s_combo.getValue();

	    	var contragent = base_form.findField('Contragent_id').getValue();
	    	if(contragent > 0 && base_form.findField('Contragent_id').getById(contragent)){
	    		var orgtype = base_form.findField('Contragent_id').getById(contragent).get('OrgType_SysNick');
	    		if(orgtype == 'lpu'){
	    			params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
					params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
	    		} else {
	    			params.OrgStruct_id = base_form.findField('OrgStruct_id').getValue();
	    		}
	    	}
			base_form.findField('StorageZone_id').getStore().baseParams = params;
			base_form.findField('StorageZone_id').getStore().load({
				callback:function(){
					var index = base_form.findField('StorageZone_id').getStore().findBy(function(rec){
						return (rec.get('StorageZone_id') == sz);
					});
					if(index >= 0){
						base_form.findField('StorageZone_id').setValue(sz);
					} else {
						base_form.findField('StorageZone_id').setValue(null);
					}
				}
			});
		}
    },
    filterLpuSectionCombo: function(){
        var base_form = this.FilterPanel.getForm();
        var storage_id = null;
        var lpubuilding_id = null;

        if (!Ext.isEmpty(this.Storage_pid) && !Ext.isEmpty(this.Storage_id)) {
            storage_id = this.Storage_id;
            lpubuilding_id = null;
		} else {
            storage_id = null;
            lpubuilding_id = this.lb_combo.getValue();
		}

        this.ls_combo.setValue(null);
        delete this.ls_combo.lastQuery;
        this.ls_combo.getStore().removeAll();

        this.ls_combo.getStore().baseParams.Storage_id = storage_id;
        this.ls_combo.getStore().baseParams.LpuBuilding_id = lpubuilding_id;
    },
    filterStorageCombo: function(){
        var wnd = this;
        var lpubuilding_id = null;
        var lpusection_id = null;
        var medservice_id = null;

        if (getGlobalOptions().orgtype == 'lpu') {
        	//при открытии формы из АРМ Товаровед, фильтрация складов производится на основе службы и отделения (если оно задано)
            if (this.ARMType == 'merch') {
                medservice_id = this.MedService_id;
                lpusection_id = !Ext.isEmpty(this.ls_combo.getValue()) ? this.ls_combo.getValue() : null;
			} else {
                lpubuilding_id = !Ext.isEmpty(this.lb_combo.getValue()) ? this.lb_combo.getValue() : null;
                lpusection_id = !Ext.isEmpty(this.ls_combo.getValue()) ? this.ls_combo.getValue() : null;
			}

            this.s_combo.setValue(null);
            delete this.s_combo.lastQuery;
            this.s_combo.getStore().removeAll();

            this.s_combo.getStore().baseParams.LpuBuilding_id = lpubuilding_id;
            this.s_combo.getStore().baseParams.LpuSection_id = lpusection_id;
            this.s_combo.getStore().baseParams.MedService_id = medservice_id;
		} else /*if (!Ext.isEmpty(this.lb_combo.getValue()))*/ {
            this.s_combo.setValue(null);
            delete this.s_combo.lastQuery;
            this.s_combo.getStore().removeAll();

            this.s_combo.getStore().baseParams.OrgStruct_id = wnd.os_combo.getValue();

            /*var storage = this.s_combo.getValue();
            wnd.s_combo.getStore().load({
                callback:function(){
                    var index = wnd.s_combo.getStore().findBy(function(rec){
                        return (rec.get('Storage_id') == storage);
                    });
                    if(index >= 0){
                        wnd.s_combo.setValue(storage);
                    } else {
                        wnd.s_combo.setValue(null);
                    }
                    wnd.filterStorageZoneCombo();
                }
            });*/
        }
    },
	initDocumentStatusTypeComboFilter: function(){

		var combo = this.FilterCommonPanel.findById('WhsDocumentStatusTypeCombo'),
			// разрешенные статусы документов
			whsDocStatusTypeCode = [1, 2, 10, 11];

		combo.getStore().clearFilter();
		combo.lastQuery = '';

		combo.getStore().filterBy(function(record) {

			if (record.get('WhsDocumentStatusType_Code').inlist(whsDocStatusTypeCode))
				return true;
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swWhsDocumentUcInventViewWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		this.MedService_id = null;
        this.Lpu_id = null;
        this.LpuSection_id = null;
        this.LpuBuilding_id = null;
        this.OrgStruct_id = null;
        this.Storage_id = null;
        this.Storage_pid = null;

		if (arguments[0]) {
			if (arguments[0].ARMType) {
				this.ARMType = arguments[0].ARMType;
			}
			if (!Ext.isEmpty(arguments[0].MedService_id)) {
				this.MedService_id = arguments[0].MedService_id;
			}
            if (!Ext.isEmpty(arguments[0].Lpu_id)) {
                this.Lpu_id = arguments[0].Lpu_id;
            }
            if (!Ext.isEmpty(arguments[0].LpuSection_id)) {
                this.LpuSection_id = arguments[0].LpuSection_id;
            }
            if (!Ext.isEmpty(arguments[0].LpuBuilding_id)) {
                this.LpuBuilding_id = arguments[0].LpuBuilding_id;
            }
            if (!Ext.isEmpty(arguments[0].OrgStruct_id)) {
                this.OrgStruct_id = arguments[0].OrgStruct_id;
            }
            if (!Ext.isEmpty(arguments[0].Storage_id)) {
                this.Storage_id = arguments[0].Storage_id;
		}
            if (!Ext.isEmpty(arguments[0].Storage_pid)) {
                this.Storage_pid = arguments[0].Storage_pid;
            }
		}

		wnd.FilterCommonPanel.findById('WhsDocumentStatusTypeCombo').store.addListener('load',wnd.initDocumentStatusTypeComboFilter());

        wnd.SearchGrid.setParam('Lpu_id', this.Lpu_id, false);
        wnd.SearchGrid.setParam('MedService_id', this.MedService_id, false);

		wnd.SearchGrid.addActions({
			name:'wduiv_action_actions',
			text:lang['deystviya'],
			menu: [
			{
				name: 'action_createAll_list',
				text: 'Сформировать все',//lang['sformirovat'],
				hidden: (arguments[0].ARMType != 'merch'),
				handler: function() {
					if (getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().orgtype == 'farm') { 
						//  Если уфимская аптека ЛЛО
						wnd.createAllDrugList_cntrl(1, 0);
				    } else {
						wnd.createDrugListAll();
				    }
				},
				iconCls: 'add16'
			}, 
			{
				name: 'action_create_list',
				hidden: (getGlobalOptions().region.nick == 'ufa') && (arguments[0].ARMType != 'merch'),
				text: lang['sformirovat'],
				handler: function() {
				    if (getGlobalOptions().region.nick == 'ufa') {
					wnd.createAllDrugList_cntrl(2, 0);
				    }
				    else {
					wnd.createDrugList();
				    }			    
				},
				iconCls: 'add16'
			}, {
				name: 'action_recreate',
				text: 'Переформировать',//lang['sformirovat'],
				hidden: (getGlobalOptions().region.nick != 'ufa') || (arguments[0].ARMType != 'merch'),
				handler: function() {
				    wnd.createAllDrugList_cntrl(2, 1);},
				iconCls: 'edit16'
			}, {
				name: 'wduiv_action_approve',
				text: lang['utverdit'],
				hidden: (getGlobalOptions().region.nick == 'ufa') && (arguments[0].ARMType != 'merch'),
				iconCls: 'ok16',
				handler: function() {wnd.setApproved(true);}
			}, {
				name: 'wduiv_action_cancel_approve',
				hidden: (getGlobalOptions().region.nick == 'ufa') && (arguments[0].ARMType == 'merch'),
				text: lang['otmenit_utverjdenie'],
				iconCls: 'delete16',
				handler: function() {wnd.setApproved(false);}
			}],
			iconCls: 'actions16'
		});
		if (getGlobalOptions().region.nick != 'ufa')
//		{
//		    this.getAction('wduiv_action_actions').items[0].menu.items
//		}
//		else
		    this.SearchGrid.getAction('wduiv_action_actions').setDisabled(this.ARMType != 'merch'); //действия доступны только из АРМ Товароведа

		this.ls_combo.fullReset();
		this.s_combo.fullReset();

        this.setLpuFields();
		this.doReset();
		this.doSearch();
	},
	initComponent: function() {
		var wnd = this;

        this.lb_combo = new sw.Promed.SwLpuBuildingGlobalCombo({
            hiddenName: 'LpuBuilding_id',
            fieldLabel: 'Подразделение',
            xtype: 'swlpubuildingglobalcombo',
            width: 400
        });

        this.ls_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Отделение'),
            hiddenName: 'LpuSection_id',
            displayField: 'LpuSection_Name',
            valueField: 'LpuSection_id',
            editable: true,
            allowBlank: true,
            width: 400,
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{LpuSection_Code}</font>&nbsp;{LpuSection_Name}',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'LpuSection_id'
                }, [
                    {name: 'LpuSection_id', mapping: 'LpuSection_id'},
                    {name: 'LpuSection_Code', mapping: 'LpuSection_Code'},
                    {name: 'LpuSection_Name', mapping: 'LpuSection_Name'}
                ]),
                url: '/?c=WhsDocumentUcInvent&m=loadLpuSectionCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'change' || event_name == 'clear' || event_name == 'set_by_id') {
                    wnd.filterStorageCombo();
                    wnd.filterStorageZoneCombo();
                }
            }
        })

        this.s_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Склад'),
            hiddenName: 'Storage_id',
            displayField: 'Storage_Name',
            valueField: 'Storage_id',
            editable: true,
            allowBlank: true,
            width: 400,
            listWidth: 400,
            triggerAction: 'all',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{Storage_Code}</font>&nbsp;{Storage_Name}',
                '</div></tpl>'
            ),
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'Storage_id'
                }, [
                    {name: 'Storage_id', mapping: 'Storage_id'},
                    {name: 'Storage_Code', mapping: 'Storage_Code'},
                    {name: 'Storage_Name', mapping: 'Storage_Name'}
                ]),
                url: '/?c=WhsDocumentUcInvent&m=loadStorageCombo'
            }),
            setLinkedFieldValues: function(event_name) {
                if (event_name == 'change' || event_name == 'clear') {
                    wnd.filterStorageZoneCombo();
                }
            }
        });

        this.os_combo = new sw.Promed.SwOrgStructCombo({
            hiddenName: 'OrgStruct_id',
            fieldLabel: 'Подразделение',
            width: 400
        });

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 120,
			border: false,
			frame: true,
			items: [{
                layout: 'column',
                items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'daterangefield',
                        plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                        fieldLabel: lang['period'],
                        name: 'WhsDocumentUc_DateRange',
                        width: 200
                    }, {
                        xtype: 'swdrugfinancecombo',
                        fieldLabel: 'Финансирование',
                        name: 'DrugFinance_id',
                        width: 400
                    }, {
						xtype: 'swwhsdocumentcostitemtypecombo',
						fieldLabel: lang['statya_rashoda'],
						name: 'WhsDocumentCostItemType_id',
						width: 400
					}, {
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							fieldLabel: lang['kontragent'],
							hiddenName: 'Contragent_id', 
							id: 'WDUIVW_Contragent_id',
							width: 400,
							resetCombo: function() {
								this.setValue(null);
								this.lastQuery = '';
								this.getStore().removeAll();
								this.getStore().baseParams.query = '';
								this.getStore().baseParams.Contragent_id = null;
							},
							setValueById: function(id) {
								var combo = this;
								combo.store.baseParams.Contragent_id = id;
								combo.store.load({
									callback: function(){
										combo.setValue(id);
										combo.store.baseParams.Contragent_id = null;
									}
								});
							},
							listeners: {
								render: function(combo) {
									combo.getStore().proxy.conn.url = '/?c=DocumentUc&m=loadContragentList';
								},
								change: function(combo) {
									this.filterStorageZoneCombo();
								}.createDelegate(this)
							}
						}]
					}]
                }, {
                    layout: 'form',
                    labelWidth: 120,
                    items: [
                    	this.lb_combo,
                    	this.ls_combo,
                    	{
							layout: 'form',
							hidden:(getGlobalOptions().orgtype == 'lpu'),
							items: [this.os_combo]
						}, {
							layout: 'form',
							items: [this.s_combo]
						}, {
							layout: 'form',
							hidden: (!getRegionNick().inlist(['krym','perm']) || !getGlobalOptions().orgtype.inlist(['farm','reg_dlo'])),
							items: [{
								xtype: 'swstoragezonecombo',
								fieldLabel: 'Место хранения',
								hiddenName: 'StorageZone_id',
								width: 400,
								listeners: {
									change: function(combo) {
										
									}
								}
							}]
						},
						{
							layout: 'form',
							items: [{
								xtype: 'SwWhsDocumentStatusTypeCombo',
								fieldLabel: lang['status'],
								hiddenName: 'WhsDocumentStatusType_id',
								id: 'WhsDocumentStatusTypeCombo',
								width: 400,
							}]
						}
                    ]
                }]
            }]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['poisk'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: lang['ochistit'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
							wnd.doSearch();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', hidden: true/*, url: '/?c=WhsDocumentUcInvent&m=delete'*/},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentUcInvent&m=loadList',
			height: 180,
			object: 'WhsDocumentUcInvent',
			editformclassname: 'swWhsDocumentUcInventEditWindow',
			id: 'WhsDocumentUcInventGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			title: null,
			toolbar: true,
			stringfields: [
				{ name: 'WhsDocumentUcInvent_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentUc_id', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: lang['№_vedomosti'], width: 150 },
				{ name: 'WhsDocumentStatusType_Code', hidden: true, isparams: true },
				{ name: 'WhsDocumentStatusType_Name', type: 'string', header: lang['status'], width: 150 },
				{ name: 'WhsDocumentUc_Date', type: 'string', header: lang['data'], width: 150 },
				{ name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 150, id: 'autoexpand' },
				{ name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 150 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{ name: 'Storage_Name', type: 'string', header: lang['sklad'], width: 150 },
				{ name: 'StorageZone_Name', type: 'string', header: 'Место хранения', width: 150, 
					hidden: !(getRegionNick().inlist(['krym']) && getGlobalOptions().orgtype.inlist(['farm','reg_dlo'])) 
				},
				{ name: 'DrugFinance_Name', type: 'string', header: lang['istochnik_finans'], width: 150 },
				{ name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['statya_rashoda'], width: 150 }, 
				{ name: 'WhsDocumentUc_pName', type: 'string', header: lang['prikaz'], width: 150 },
				{ name: 'Drug_Count', type: 'string', header: lang['kol-vo_pozitsiy'], width: 150 },
				{ name: 'WhsDocumentUc_Sum', type: 'string', header: lang['summa'], width: 150 }, 
				// Информация по неисполненным документам
				{ name: 'kolDoc', type: 'string', header: 'kolDoc', hidden: true },
				{ name: 'minDate', type: 'string', header: 'minDate', hidden: true }
			],
			onRowSelect: function(sm, rowIdx, record) {
				this.setDisabledAction('action_create_list', record.get('Drug_Count') > 0);
				//this.setDisabledAction('action_createAll_list', record.get('Drug_Count') > 0);
				this.setDisabledAction('wduiv_action_approve', record.get('WhsDocumentStatusType_Code') != 1);
				this.setDisabledAction('action_recreate', record.get('WhsDocumentStatusType_Code') != 1);
				this.setDisabledAction('wduiv_action_cancel_approve', record.get('WhsDocumentStatusType_Code') != 2);
			},
			setDisabledAction: function(action, isDisable) {
				var actions = this.getAction('wduiv_action_actions').items[0].menu.items,
					idx = actions.findIndexBy(function(a) { return a.name == action; });
				if( idx == -1 ) {
					return;
				}
				actions.items[idx].setDisabled(isDisable);
				this.getAction('wduiv_action_actions').items[1].menu.items.items[idx].setDisabled(isDisable);
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					text: '-'
				},
					HelpButton(this, 0),
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swWhsDocumentUcInventViewWindow.superclass.initComponent.apply(this, arguments);

		this.findById('WDUIVW_Contragent_id').addListener('change', function(combo, newValue, oldValue) {
			this.filterStorageZoneCombo();
		}.createDelegate(this));

		this.lb_combo.addListener('change', function(combo, newValue, oldValue) {
            this.filterStorageCombo();
            this.filterLpuSectionCombo();
			this.filterStorageZoneCombo();
		}.createDelegate(this));

		this.os_combo.addListener('change', function(combo, newValue, oldValue) {
            this.filterStorageCombo();
		}.createDelegate(this));
	}
});