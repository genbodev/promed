/**
* swJournalLeaveWindow - окно журнала выбывших из профильного отделения стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      7.2013
* @comment
**/
/*NO PARSE JSON*/
sw.Promed.swJournalLeaveWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swJournalLeaveWindow',
	objectSrc: '/jscore/Forms/Hospital/swJournalLeaveWindow.js',

	title: lang['jurnal_vyibyivshih'],
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	plain: true,
	id: 'swJournalLeaveWindow',

	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,

	show: function() {
		sw.Promed.swJournalLeaveWindow.superclass.show.apply(this, arguments);

		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		this.doReset();

		let form = this.filterPanel.getForm();
		let fieldMedPersonal = form.findField('MedPersonal_iid');
		
		fieldMedPersonal.setContainerVisible(getRegionNick() === 'msk');
		
		// Load storage's.
		fieldMedPersonal.getStore().load();
	},

	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	doReset: function() {
        var form = this.filterPanel.getForm(),
            grid = this.EvnSectionGrid.getGrid();
        form.reset();
        form.findField('EvnSection_disDate_Range').setValue(getGlobalOptions().date +' - '+ getGlobalOptions().date);
        form.findField('EvnSection_disDate_Range').focus(true, 250);
        grid.getStore().baseParams = {};
        this.EvnSectionGrid.removeAll(true);
        grid.getStore().removeAll();
		//this.loadGridWithFilter(true);
	},
	loadGridWithFilter: function(clear) {
		var viewFrame = this.EvnSectionGrid;
        viewFrame.removeAll();
        var params = getAllFormFieldValues(this.filterPanel);
        params.limit = 100;
        params.start = 0;
        params.LpuSection_cid = this.userMedStaffFact.LpuSection_id;
		if (clear)
		{
			//default filter
		}
		else
		{
			//doSearch
		}
        viewFrame.loadData({
			globalFilters: params
		});
	},
	getSelectedRecord: function()
	{
		var record = this.EvnSectionGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.data.EvnSection_pid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		return record;
	},
	openEmk: function()
	{
		var record = this.getSelectedRecord();
		if (record == false) return false;
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			getWnd('swPersonEmkWindow').hide();
		}
		// чтобы при открытии ЭМК загрузилась форма просмотра КВС
		var searchNodeObj = false;
		if(record.data.EvnSection_pid) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPS',
				Evn_id: record.data.EvnSection_pid
			};
		}

		var emk_params = {
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			addStacActions: ['action_New_EvnPS', 'action_StacSvid'],
			searchNodeObj: searchNodeObj,
			callback: function() {
				//
			}.createDelegate(this)
		};

		if(this.userMedStaffFact.ARMType == 'headnurse') {
			emk_params.readOnly = true;
			emk_params.addStacActions = ['action_StacSvid'];
			emk_params.ARMType = 'headnurse';
		}

		Ext.Ajax.request({
			url: '/?c=EvnPS&m=beforeOpenEmk',
			params: {
				Person_id: record.get('Person_id')
			},
			failure: function(response, options) {
				showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka']);
			},
			success: function(response, action) {
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if(!Ext.isArray(answer) || !answer[0])
					{
						showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
						return false;
					}
					if (answer[0].countOpenEvnPS > 0)
					{
						//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
						emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
					}
					getWnd('swPersonEmkWindow').show(emk_params);
				}
				else {
					showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});

        return true;
    },
	/** Удаление исхода госпитализации в профильное отделение */
	deleteLeave: function()
	{
		var record = this.getSelectedRecord();
		if (record == false) return false;
		var win = this;
		return sw.Promed.Leave.deleteLeave({
			EvnSection_id: record.data.EvnSection_id,
			ownerWindow: win,
			callback: function(){
				win.EvnSectionGrid.refreshRecords(win.EvnSectionGrid,0);
			}
		});
	},

	openAddHomeVisit: function () {
		var record = this.getSelectedRecord();
		if (!Ext.isEmpty(record)) {
			getWnd('swHomeVisitAddWindow').show({
				Person_id: record.data.Person_id,
				Server_id: record.data.Server_id,
				HomeVisitCallType_id: 4,
				HomeVisitStatus_id: 1
			});
		}
	},

	initComponent: function() {
        var win = this;
        this.filterPanel = new Ext.form.FormPanel({
            autoHeight: true,
            buttonAlign: 'left',
            frame: true,
            labelAlign: 'right',
            labelWidth: 100,
            region: 'north',
            layout: 'column',
            items: [{
                layout: 'form',
                items: [{
                    fieldLabel: lang['familiya'],
                    name: 'Person_Surname',
                    width: 170,
                    xtype: 'textfield'
                }, {
                    fieldLabel: lang['data_vyipiski'],
                    id: 'JLW_EvnSection_disDate_Range',
                    name: 'EvnSection_disDate_Range',
                    plugins: [
                        new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                    ],
                    tabIndex: TABINDEX_EPSSW + 88,
                    width: 170,
                    xtype: 'daterangefield'
                }, {
                    name: 'LpuSection_cid',
                    xtype: 'hidden'
                }, {
                    name: 'PersonCardStateType_id',
                    xtype: 'hidden',
                    value: 1
                }, {
                    name: 'PersonPeriodicType_id',
                    xtype: 'hidden',
                    value: 1
                }, {
                    name: 'PrivilegeStateType_id',
                    xtype: 'hidden',
                    value: 1
                }, {
                    name: 'SearchFormType',
                    xtype: 'hidden',
                    value: 'EvnSection'
                }]
            }, {
                layout: 'form',
                items: [{
                    fieldLabel: lang['imya'],
                    name: 'Person_Firname',
                    width: 170,
                    xtype: 'textfield'
                }, {
                    fieldLabel: lang['data_rojdeniya'],
                    id: 'JLW_Person_Birthday_Range',
                    name: 'Person_Birthday_Range',
                    plugins: [
                        new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                    ],
                    width: 170,
                    xtype: 'daterangefield'
                }]
            }, {
                layout: 'form',
                labelWidth: 70,
                items: [{
                    fieldLabel: lang['otchestvo'],
                    name: 'Person_Secname',
                    width: 170,
                    xtype: 'textfield'
                },{
					// Врач.
					xtype: 'swmedpersonalcombo',
					hiddenName: 'MedPersonal_iid',
					width: 330,
					isValid: function() {
						//PROMEDWEB-3192
						//Валидациядля поля не требуется
						return true;
					}
				}, {
                	layout:'column',
                	items:[{
                		layout:'form',
                		items:[{
		                    style: "padding-left: 75px",
		                    xtype: 'button',
		                    text: lang['nayti'],
		                    iconCls: 'search16',
		                    handler: function()
		                    {
		                        win.doSearch();
		                    }
		                }]
                	}, {
                		layout:'form',
                		items:[{
		                    style: "padding-left: 20px",
		                    xtype: 'button',
		                    text: lang['sbros'],
		                    iconCls: 'resetsearch16',
		                    handler: function()
		                    {
		                        win.doReset();
		                    }
		                }]
                	}]
                }]
            }],
            keys: [{
                fn: function() {
                    win.doSearch();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
        });

		this.EvnSectionGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{
					name: 'action_add',
					disabled: true,
					text: 'Вызов на дом',
					tooltip: 'Вызов на дом',
					handler: function() {
						win.openAddHomeVisit();
					},
					hidden: getRegionNick() != 'ufa'
				},
				{ name: 'action_view', text: lang['otkryit_emk'], tooltip: lang['otkryit_emk'], handler: function() { win.openEmk(); } },
				{ name: 'action_edit', text: lang['otmenit_vyipisku'], tooltip: lang['otmenit_vyipisku'], handler: function() { win.deleteLeave(); } },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_cancel', hidden: true, disabled: true},
				{ name: 'action_refresh' },
				{
					name: 'action_print',
					menuConfig: {
						printKvs: {
							name: 'printKvs',
							text: lang['pechat_kvs'],
							hidden: !getRegionNick().inlist(['khak', 'msk']),
							handler: function()
							{
								var v,
									record = (v = win) && (v = v.EvnSectionGrid) &&
										(v = v.ViewGridPanel) && (v = v.getSelectionModel()) &&
										v.getSelected();
								if (!record)
									return (false);

								printEvnPS({
									EvnPS_id: record.get("EvnSection_pid"),
									EvnSection_id: record.get("EvnSection_id")
								});
							}
						},
						printF005U: {
							name: 'printF005U',
							text: 'Лист регистрации переливания трансфузионных сред (005/у)',
							handler: function() {
								var record = win.EvnSectionGrid.ViewGridPanel.getSelectionModel().getSelected();
								if (!record) return false;
								printBirt({
									'Report_FileName': 'f005u.rptdesign',
									'Report_Params': '&paramEvnPs='+record.get("EvnSection_pid"),
									'Report_Format': 'pdf'
								});
							}
						}
					}
				}
			],
			stringfields: [
                {name: 'EvnSection_id', type: 'int', header: 'ID', key: true},
                {name: 'EvnSection_pid', type: 'int', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'PersonEvn_id', type: 'int', hidden: true},
                {name: 'Server_id', type: 'int', hidden: true},
                {name: 'EvnSection_isLast', type: 'int', hidden: true},
                {name: 'EvnPS_NumCard', type: 'string', header: lang['№_kartyi'], width: 70},
                {name: 'Person_Surname', type: 'string', header: lang['familiya'], id: 'autoexpand'},
                {name: 'Person_Firname', type: 'string', header: lang['imya'], width: 200},
                {name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 200},
                {name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
                {name: 'EvnSection_setDate', type: 'date', format: 'd.m.Y', header: lang['postuplenie'], width: 90},
                {name: 'EvnSection_disDate', type: 'date', format: 'd.m.Y', header: lang['vyipiska'], width: 90},
                {name: 'LeaveType_Name', type: 'string', header: lang['ishod'], width: 100 },
                //{name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 150 },
                {name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },
                {name: 'EvnSection_KoikoDni', type: 'int', header: lang['k_dni'], width: 90},
                {name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 50},
                {name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 100 }
			],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            dataUrl: C_SEARCH,
			id: 'JLW_EvnSectionGrid',
            object: 'EvnSection',
            pageSize: 100,
            paging: true,
            region: 'center',
            root: 'data',
            toolbar: true,
            totalProperty: 'totalCount',
			border: false,
			onLoadData: function(flag) {
				//this.setActionDisabled('action_view',!(this.getCount()>0));
				this.setActionDisabled('action_add', !flag);
			},
			onRowSelect: function(sm,rowIdx,record) {
				//нельзя отменить исход из отделения
				//если движение не последнее
				//или движение закончено не сегодня
				//если архивная запись
				this.setActionDisabled('action_edit', !record.data.EvnSection_disDate || record.data.EvnSection_disDate.format('d.m.Y') != getGlobalOptions().date || record.get('archiveRecord') == 1);
				this.setActionDisabled('action_add', false);
			},
			onDblClick: function() {
                win.openEmk();
			},
			onEnter: function() {
				this.onDblClick();
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
            layout: 'border',
			items: [
                this.filterPanel,
				this.EvnSectionGrid
			]
		});
		sw.Promed.swJournalLeaveWindow.superclass.initComponent.apply(this, arguments);
        //var date_range_cmp = this.filterPanel.getForm().findField('EvnSection_disDate_Range');
        var date_range_cmp = this.findById('JLW_EvnSection_disDate_Range');
        date_range_cmp.on('select', function(){
            win.doSearch();
        });
	}
});
