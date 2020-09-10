/**
* Форма «Лоты на поставку медикаментов»
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Vasinsky Igor
* @copyright    
* @version      25.05.2015
*/

sw.Promed.swUnitOfTradingDistributeViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['raspredelenie_lotov'],
	maximized: true,
	plain: true,
	autoScroll: true,
	id: 'swUnitOfTradingDistributeViewWindow',
	
	show: function() {
		sw.Promed.swUnitOfTradingDistributeViewWindow.superclass.show.apply(this, arguments);
            
        this.EmployesPanel.getGrid().getStore().baseParams = {
            Org_id : arguments[0].org_id,
            pmUser_id : arguments[0].pmUser_id            
        };    
        this.EmployesPanel.getGrid().getStore().load();

        var actions = {
            name: 'uotdv_actions',
            iconCls: 'actions16',
            
            text: lang['deystviya'],
            menu: [
            {
                name: 'unassign',
                //scope: this,
                text: lang['snyat_naznachenie'],
                handler : function(){
                    Ext.getCmp('swUnitOfTradingDistributeViewWindow').unassignLot();
                }
            }
            
            ]
        };
			
        this.GridPanel.addActions(actions);

		this.getCurrentDateTime();
		this.onChangeDates('day');
		//this.defineActionsVisible();

        //Экспорт, Заявка на размещение закупки, Маркетинговое исследование.
        if(!arguments[0] || !arguments[0].disableAdd || arguments[0].disableAdd != true)  {
            this.GridPanel.getAction('action_add').setDisabled(false);
        }

        if(!arguments[0] || !arguments[0].disableEdit || arguments[0].disableEdit != true)  {
            this.GridPanel.getAction('action_edit').setDisabled(false);
        }

        if(!arguments[0] || !arguments[0].disableDelete || arguments[0].disableDelete != true)  {
            this.GridPanel.getAction('action_delete').setDisabled(false);
        }
    },
    setCheckedLotsByEmployee: function(){
        var employeeSel = Ext.getCmp('EmployesPanel').getGrid().getSelectionModel().getSelected();
        var rows = this.GridPanel.getGrid().getStore().data.items;
        
        var recs = [];
        
        for(var key in rows){
            if(typeof rows[key] == 'object'){ 
                if(employeeSel.get('PMUser_id') == rows[key].get('PMUser_did')){
                    recs.push(this.GridPanel.getGrid().getStore().getById(rows[key].id));
                }
            }  
        }
        
        this.GridPanel.getGrid().getSelectionModel().clearSelections();
        
        if(recs.length>0)
            Ext.getCmp('GridPanel_CenterGrid').getGrid().getSelectionModel().selectRecords(recs, true);        
    },
    
    unassignLot : function(){
        var employeeSel = Ext.getCmp('EmployesPanel').getGrid().getSelectionModel().getSelected();
        var sm = this.GridPanel.getGrid().getSelectionModel().getSelected();
        var WhsDocumentUcPMUser_id = sm.get('WhsDocumentUcPMUser_id');
        var WhsDocumentUc_id = sm.get('WhsDocumentUc_id');
        var pmUser_did = sm.get('PMUser_did');
        var pmUser_id = getGlobalOptions().pmuser_id; 
        
        if(WhsDocumentUcPMUser_id){
            Ext.Ajax.request({
            	url: '/?c=Gku&m=manageLot',
            	params: {
                    WhsDocumentUc_id : WhsDocumentUc_id,
                    WhsDocumentUcPMUser_id : WhsDocumentUcPMUser_id,
                    pmUser_id        : pmUser_id,
                    pmUser_did       : pmUser_did,
                    unassign         : 1  
            	},
            	callback: function(options, success, response) {
                     
                    if (success === true) {   
                         Ext.getCmp('GridPanel_CenterGrid').getGrid().getStore().load();   
                         Ext.getCmp('swUnitOfTradingDistributeViewWindow').setCheckedLotsByEmployee();
                    }
                }
            }); 
        }        
       
    },        
	
	onChangeDates: function(mode) {
		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {}),
			btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		
		this.FilterPanel.getForm().findField('DrugRequest_id').getStore().load({params: params});
		
		this.GridPanel.removeAll({clearAll:true});
	},
	
	doSearch: function() {
	    
		var form = this.FilterPanel;
		var base_form = this.FilterPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = Ext.apply(base_form.getValues(), {
			start: 0,
			begDate: Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate: Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y')
		});
		this.GridPanel.loadData({globalFilters: params});
	},
	
	getPeriodToggle: function(mode) {
		switch(mode) {
			case 'day':
				return this.WindowToolbar.items.items[9];
				break;
			case 'week':
				return this.WindowToolbar.items.items[10];
				break;
			case 'month':
				return this.WindowToolbar.items.items[11];
				break;
			case 'range':
				return this.WindowToolbar.items.items[12];
				break;
			default:
				return null;
				break;
		}
	},
	
	getCurrentDateTime: function() {

		if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
						this.curDate = result.begDate;
						// Проставляем время и режим
						this.mode = 'day';
						this.currentDay();

						if ( this.gridPanelAutoLoad == true ) {
							this.onChangeDates('day');
						}

						this.getLoadMask().hide();
					}
				}.createDelegate(this)
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.mode = 'day';
			this.currentDay();

			if ( this.gridPanelAutoLoad == true ) {
				this.onChangeDates('day');
			}
		}
	},
	
	stepDay: function(day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	prevDay: function() {
		this.stepDay(-1);
	},
	
	nextDay: function() {
		this.stepDay(1);
	},
	
	currentDay: function() {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	currentWeek: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	currentMonth: function() {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	
	deleteUnitOfTrading: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: lang['vyi_deystvitelno_hotite_udalit_lot'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie_lota']).show();
					Ext.Ajax.request({
						scope: this,
						params: { WhsDocumentProcurementRequest_id: record.get('WhsDocumentUc_id')},
						url: '/?c=UnitOfTrading&m=deleteUnitOfTrading',
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if(success) {
								this.GridPanel.ViewActions.action_refresh.execute();
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},

	setSignUnitOfTrading: function(mode, issign) { //проверки и подготовки перед подписанием
		if( !mode || !mode.inlist(['current', 'all']) ) {
			return false;
		}
		var wnd = this;
		var selRecords = [];
		switch(mode) {
			case 'current':
				var recs = this.GridPanel.getGrid().getSelectionModel().getSelections();
				if( recs.length == 0 ) return false;
				selRecords = recs;
				break;
			case 'all':
				this.GridPanel.getGrid().getStore().each(function(r) {
					selRecords.push(r);
				});
				if( selRecords.length == 0 ) return false;
				break;
		}
		var i = 0,
			rs = [];
		for(; i<selRecords.length; i++) {
			rs.push(selRecords[i].get('WhsDocumentUc_id'));
		}


		if (issign) {
			this.doSignUnitOfTrading(rs, issign);
		} else {
			var id_array = new Array();

			this.GridPanel.getGrid().getStore().each(function(r) {
				id_array.push(r.get('WhsDocumentUc_id'));
			});

			//проверяем лот на наличие дочерних контрактов
			Ext.Ajax.request({
				scope: this,
				url: '/?c=UnitOfTrading&m=getWhsDocumentSupplyByUotId',
				params: { UotList: escape(id_array.join('|')) },
				callback: function(o, s, r) {
					if(s) {
						var arr = Ext.util.JSON.decode(r.responseText);
						if (arr.length > 0) {
							var exsist = false;
							for(var i = 0; i < arr.length; i++) {
								for(var j = 0; j < rs.length; j++) {
									log('check arr('+i+'):'+arr[i].WhsDocumentProcurementRequest_id+' rs('+j+'):'+rs[j]);
									if (arr[i].WhsDocumentProcurementRequest_id == rs[j]) {
										exsist = true;
										break;
									}
								}
								if (exsist) break;
							}

							if (exsist) {
								sw.swMsg.alert(lang['oshibka'], lang['s_lota_nevozmojno_snyat_podpisanie_suschestvuet_docherniky_kontrakt']);
							} else {
								Ext.Msg.show({
									title: lang['vnimanie'],
									msg: lang['v_ramkah_dannoy_svodnoy_zayavki_suschestvuet_lot_s_dochernim_kontraktom_prodoljit_snyatie_podpisaniya'],
									buttons: Ext.Msg.YESNO,
									fn: function(btn) {
										if (btn === 'yes') {
											wnd.doSignUnitOfTrading(rs, issign);
										}
									},
									icon: Ext.MessageBox.WARNING
								});
							}
						} else {
							wnd.doSignUnitOfTrading(rs, issign);
						}
					}
				}
			});
		}
	},
	
	issetUnSignedUot: function(store) {
		var f = false;
		store.each(function(r) {
			if(r.get('isSigned') == 0) {
				f = true;
			}
		});
		return f;
	},
	
	setDisabledAction: function(grid, action, isDisable) {
		var actions = grid.getAction('uotdv_actions').items[0].menu.items,
			idx = actions.findIndexBy(function(a) { return a.name == action; });
		if( idx == -1 ) {
			return;
		}
		actions.items[idx].setDisabled(isDisable);
		grid.getAction('uotdv_actions').items[1].menu.items.items[idx].setDisabled(isDisable);
	},
	
	initComponent: function()
	{
		this.timeMenu = new Ext.form.TimeField ({
			//disabled: true,
			fieldLabel: lang['vremya_do_kontsa_ojidaniya'],
			name: 'PPD_WaitingTime',
			id: 'PPD_WaitingTime',
			format: 'H:i',
			plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
			validateOnBlur: false,
			width: 40,
			xtype: 'swtimefield',
			triggerAction: function () {
				alert('click');
			},
			triggerClass: 'hidden-class',
			hidden:true,
			listeners: {
				focus: function(){
					this.disable();
					var parent_object = this;
					var SetWaitingPPDTimeWindow = new Ext.Window({
						width:400,
						heigth:300,
						title:lang['vvedite_novoe_vremya_ojidaniya'],
						modal: true,
						draggable:false,
						resizable:false,
						closable : false,
						items:[{
							xtype: 'form',
							bodyStyle: {padding: '10px'},
							disabledClass: 'field-disabled',
							items:
							[{																	
							//comboSubject: 'CmpReason',
								disabledClass: 'field-disabled',
								fieldLabel: lang['vremya_ojidaniya_prinyatiya_vyizova_v_ppd_min'],
								allowBlank: false,
								xtype: 'textfield',
								autoCreate: {tag: "input",  maxLength: "3", autocomplete: "off"},
								maskRe: /[0-9]/,
								id:'SetWaitingPPDTimeWindow_time',
								width:250
							},
							{
								disabledClass: 'field-disabled',
								fieldLabel: lang['vash_parol'],
								allowBlank: false,
								id: 'refuse_comment',
								// tabIndex: TABINDEX_PEF + 5,
								width: 250,
								inputType:'password',
								xtype: 'textfield',
								id:'SetWaitingPPDTimeWindow_pass'
							}]
						}],
						buttons:[{
							text:lang['ok'],
							handler:function(){
								var time = Ext.getCmp('SetWaitingPPDTimeWindow_time').getValue();
								var password = Ext.getCmp('SetWaitingPPDTimeWindow_pass').getValue();

								if ((!time)||(!password)) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: lang['vse_polya_doljnyi_byit_zapolnenyi'],
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}
								
								Ext.Ajax.request({
									params: {
										PPD_WaitingTime: time,
										Password: password
									},
									callback: function(options, success, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (success) {
											if ((!response_obj.success) ) {
												sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
											}
											else {
												SetWaitingPPDTimeWindow.close();
											}
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_vremeni_ojidaniya_prinyatiya_vyizova']);
										}
									},
									url: '/?c=CmpCallCard&m=setPPDWaitingTime'
								});
								SetWaitingPPDTimeWindow.close();
							}
						},
						{
							text: lang['otmena'],
							handler: function(){
								SetWaitingPPDTimeWindow.close();
							}
						}]
					})
					SetWaitingPPDTimeWindow.show();
	
					this.enable();//TODO: Убрать этот комментарий потом
				}
			}
		});
		
		this.timeMenuLabel = new Ext.form.Label({
			disabled: false,
			text: lang['vremya_do_kontsa_ojidaniya'],
			width: 180,
			hidden:true
		});
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) {
		   
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.onChangeDates('period');
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function () {
			// Читаем расписание за период
			this.onChangeDates('period');
		}.createDelegate(this));
		
		this.formActions = [];
		this.formActions.selectDate = new Ext.Action({
			text: ''
		});
		this.formActions.prev = new Ext.Action({
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function() {
				// на один день назад
				this.prevDay();
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function() {
				// на один день вперед
				this.nextDay();
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action({
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function() {
				this.currentDay();
				this.onChangeDates('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action({
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function() {
				this.currentWeek();
				this.onChangeDates('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action({
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function() {
				this.currentMonth();
				this.onChangeDates('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action({
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function() {
				this.onChangeDates('range');
			}.createDelegate(this)
		});
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype: 'tbfill'
				},
				this.timeMenuLabel,
				this.timeMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range
			]
		});
		
		this.FilterPanel = getBaseFiltersFrame({
			ownerWindow: this,
			toolBar: this.WindowToolbar,
			items: [{
				layout: 'column',
				border: false,
				defaults: { border: false },
				autoHeight: true,
				labelWidth: 200,
				items: [{
					layout: 'form',
					defaults: {
						width: 250
					},
					items: [{
						xtype: 'swbaselocalcombo',
						triggerAction: 'all',
						hiddenName: 'DrugRequest_id',
						valueField: 'DrugRequest_id',
						displayField: 'DrugRequest_Name',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'DrugRequest_id', type: 'int'},
								{name: 'DrugRequest_Name', type: 'string'}
							],
							listeners: {
								load: function(s, rs, os) {
									var combo = this.FilterPanel.getForm().findField('DrugRequest_id');
									combo.reset();
									if( s.getCount() ) {
										combo.setValue(rs[0].get(combo.valueField));
										combo.fireEvent('select', combo, rs[0], s.indexOf(rs[0]));
									}
                                    
								}.createDelegate(this)
							},
							key: 'DrugRequest_id',
							sortInfo: { field: 'DrugRequest_id' },
							url: '/?c=UnitOfTrading&m=loadDrugRequest'
						}),
						listeners: {
							select: function(c) {
								if( !Ext.isEmpty(c.getValue()) ) {
									/*this.GridPanel.loadData({globalFilters: {
										start: 0,
										DrugRequest_id: c.getValue()
									}});*/
									this.GridPanel.loadData({globalFilters: Ext.apply({
										start: 0
									}, this.FilterPanel.getForm().getValues())});
									
									this.GridPanel.setParam(c.valueField, c.getValue(), 0);
									//this.GridPanel2.setParam(c.valueField, c.getValue(), 0);
									//this.GridPanel2.getGrid().getStore().baseParams[c.valueField] = c.getValue();
								} else {
									this.GridPanel.removeAll({clearAll:true});
								}
							}.createDelegate(this)
						},
						tpl: '<tpl for="."><div class="x-combo-list-item"><font color="red"></font>&nbsp;{DrugRequest_Name}</div></tpl>',
						allowBlank: false,
						fieldLabel: lang['svodnaya_zayavka_na_zakup']
					}, {
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(c) {
								if( Ext.isEmpty(c.getValue()) )
									return false;
								this.GridPanel.setParam(c.valueField, c.getValue(), 0);
							}.createDelegate(this)
						},
						comboSubject: 'DrugFinance',
						fieldLabel: lang['istochnik_finansirovaniya']
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'swcommonsprcombo',
						listeners: {
							select: function(c) {
								if( Ext.isEmpty(c.getValue()) )
									return false;
								this.GridPanel.setParam(c.valueField, c.getValue(), 0);
							}.createDelegate(this)
						},
						comboSubject: 'WhsDocumentCostItemType',
						fieldLabel: lang['statya_rashoda']
					}]
				}]
			}]
		});

		this.EmployesPanel = new sw.Promed.ViewFrame({
			title: lang['sotrudniki'],
            //columnWidth : 0.2,
			id: 'EmployesPanel',
			autoScroll: true,
			height: 250,
            //height: Ext.getBody().getHeight()-230,
			autoLoadData: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true},
				{ name: 'action_refresh', disabled: false},
				{ name: 'action_print', hidden: true}
			],  
			stringfields: [
				{ name: 'PMUser_id', type: 'int', hidden: true, key: true },
				{ name: 'PMUser_Name', width: 250, type: 'string', header: lang['fio']}
            ],
            dataUrl: '/?c=Gku&m=getListEmployes'
        });  
        
        this.EmployesPanel.getGrid().on({
            'rowclick' : function(){
                var form = Ext.getCmp('swUnitOfTradingDistributeViewWindow');
                form.setCheckedLotsByEmployee();
            }        
        });
        
		this.GridPanel = new sw.Promed.ViewFrame({
		    //columnWidth : 0.8,
            height: Ext.getBody().getHeight()-230,
			title: lang['lotyi'],
            collapsible: false,
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: 'GridPanel_CenterGrid',
			pageSize: 50,
			paging: true,
			editformclassname: 'GridPanel_CenterGrid',
			autoScroll: true,
			selectionModel: 'multiselect',
            multi : true,
			listeners: {
				resize: function() {
					if( this.layout.layout ) this.doLayout();
				}              
			},
			autoLoadData: false,
			root: 'data',
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view' },
				{ name: 'action_delete', disabled: true, handler: this.deleteUnitOfTrading.createDelegate(this), hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'WhsDocumentUc_id', type: 'int', hidden: true, key: true },
				{ name: 'WhsDocumentUc_pid', type: 'int', hidden: true },
				{ name: 'WhsDocumentUc_Num', header: lang['№_lota'], type: 'string', isparams: true },
				{ name: 'WhsDocumentUc_Name', header: lang['naimenovanie_lota'], type: 'string', isparams: true, width: 200 },
				{ name: 'WhsDocumentUc_Sum', header: lang['summa_lota'], type: 'string' },
                { name: 'PMUser_Name', header: lang['sotrudnik'], type: 'string', width: 210 }, 
				{ name: 'WhsDocumentUc_Date', header: lang['data_izmeneniya'], type: 'string', width: 110 },               
				{ name: 'Supply_Data', header: lang['gk'], type: 'string', width: 180 },
				{ name: 'isSigned', header: lang['podpisan'], type: 'checkbox', width: 80 },
                
                { name: 'WhsDocumentUcPMUser_id', header: 'WhsDocumentUcPMUser_id', type: 'int', width: 110, hidden: true}, 
                { name: 'PMUser_did', header: 'PMUser_did', type: 'int', width: 110, hidden: true}
                
                
			],
			dataUrl: '/?c=Gku&m=loadUnitOfTradingList',
			totalProperty: 'totalCount'
		});
        
        this.GridPanel.getGrid().getStore().on({
            'load' :
            function(){
                Ext.getCmp('swUnitOfTradingDistributeViewWindow').setCheckedLotsByEmployee();
            }
        })
        this.GridPanel.getGrid().on({
            'rowclick': function(grid, rowIndex, e ){
                    //return false;
                    var employeeSel = Ext.getCmp('EmployesPanel').getGrid().getSelectionModel().getSelected();
                    var record = grid.getStore().getAt(rowIndex);  
                    var WhsDocumentUc_id = record.get('WhsDocumentUc_id'); 
                    var WhsDocumentUcPMUser_id = record.get('WhsDocumentUcPMUser_id');
                    var pmUser_id = getGlobalOptions().pmuser_id;
                    var pmUser_did = employeeSel.get('PMUser_id');  
                    
                    if(employeeSel.get('PMUser_id') != record.get('PMUser_did') && record.get('PMUser_did') != ''){
                        sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_snyat_naznachenie_s_lota']);
                        Ext.getCmp('swUnitOfTradingDistributeViewWindow').setCheckedLotsByEmployee();
                        return false;
                    }
                    
                    if(WhsDocumentUc_id){
                        Ext.Ajax.request({
                        	url: '/?c=Gku&m=manageLot',
                        	params: {
                                WhsDocumentUc_id : WhsDocumentUc_id,
                                WhsDocumentUcPMUser_id : WhsDocumentUcPMUser_id,
                                pmUser_id        : pmUser_id,
                                pmUser_did       : pmUser_did,
                                unassign         : 0  
                        	},
                        	callback: function(options, success, response) {
                                 
                                if (success === true) {   
                                     Ext.getCmp('GridPanel_CenterGrid').getGrid().getStore().load();   
                                    //var responseText = Ext.util.JSON.decode(response.responseText);
                                }
                            }
                        }); 
                    }




            }       
        });

        
		/*
		Ext.apply(this.GridPanel.ViewGridPanel, {
			collapsible: true,
			titleCollapse: true,
			animCollapse: false
		});
		*/
		this.GridPanel.getGrid().getSelectionModel().on('rowselect', function(sm, rIdx, rec) {

			//var isMz = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) > -1);
			//this.setDisabledAction(this.GridPanel, 'unsign', rec.get('isSigned') != 1 || !isMz);
			//this.setDisabledAction(this.GridPanel, 'sign', rec.get('isSigned') == 1);
			//this.setDisabledAction(this.GridPanel, 'sign_all', !this.issetUnSignedUot(this.GridPanel.getGrid().getStore()));
			//console.log('----------------------->', this.GridPanel.getGrid().getSelectionModel());
			//this.GridPanel2.setActionDisabled('action_add', rec.get('isSigned') == 1);
			//this.GridPanel2.setActionDisabled('action_delete', rec.get('isSigned') == 1);
			//this.GridPanel2.setActionDisabled('action_print_spec', rec.get('isSigned') != 1);
			//this.GridPanel2.setActionDisabled('add_in_other_uot', rec.get('isSigned') == 1);
		}, this);
		
        
        /*
		this.GridPanel.getGrid().on('collapse', function(p) {
			this.GridPanelWrap.getEl().setHeight(p.header.getHeight());
			this.doLayout();
		}, this);
		this.GridPanel.getGrid().on('expand', function(p) {
			this.GridPanelWrap.getEl().setHeight(p.getEl().getHeight());
			this.doLayout();
		}, this);
		
		this.GridPanel.getGrid().getStore().on('load', function(s) {
			s.each(function(r, i) {
				var row = this.GridPanel.getGrid().getView().getRow(i);
				this.createDDTarget(row, r);
			}, this);
		}, this);
		*/
		this.GridPanelWrap = new Ext.Panel({
			region: 'north',
			autoHeight: true,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			border: false,
			layout: 'fit',
			items: [this.GridPanel]
		});

		this.CenterPanel = new Ext.form.FormPanel({
			region: 'center',
			border: false,
			//layout: 'column',
			items: [this.GridPanelWrap, this.EmployesPanel, this.GridPanel]
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [this.FilterPanel, this.CenterPanel],
			buttons: [{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_UOTVW + 96,
					text: BTN_FRMSEARCH
				}, {
					handler: function() {
						this.FilterPanel.getForm().reset();
						this.GridPanel.removeAll({clearAll:true});
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_UOTVW + 97,
					text: BTN_FRMRESET
				},
				'-',
				HelpButton(this, TABINDEX_UOTVW + 98),
				{
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swUnitOfTradingDistributeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});