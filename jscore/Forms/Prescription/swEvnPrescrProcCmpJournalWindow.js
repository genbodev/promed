/**
* swEvnPrescrProcCmpJournalWindow - журнал выполненных процедур.
* swEvnPrescrJournalWindow - журнал назначений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-19.10.2011
* @comment      Префикс для id компонентов EPPCJF (EvnPrescrProcCmpJournalForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrProcCmpJournalWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,/*sw.Promed.BaseForm,*/ {
	codeRefresh: true,
	objectName: 'swEvnPrescrProcCmpJournalWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrProcCmpJournalWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	execEvnPrescr: function(coords) {
		var grid = this.GridPanel.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
        var conf = this._createExecParams(selected_record);
		if ( !sw.Promed.EvnPrescr.isExecutable(conf) ) {
			return false;
		}
        conf.btnId = 'id_EvnPrescrProcCmpJournalWindowExecEvnPrescr';
        conf.coords = coords;
		conf.onExecSuccess = function(){
			selected_record.set('EvnPrescr_IsExec', 2);
			selected_record.set('IsExec_Name', lang['da']);
			selected_record.commit();
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
		};
		conf.onExecCancel = function(){
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
		};
		var person_fio = selected_record.get('Person_FIO');
		var person_fio_arr = person_fio.split(' ');
		conf.Person_Surname = person_fio_arr[0] || '';
		conf.Person_Firname = person_fio_arr[1] || '';
		conf.Person_Secname = person_fio_arr[2] || '';
		sw.Promed.EvnPrescr.exec(conf);
        return true;
    },
    _createExecParams: function(selected_record) {
        if ( !selected_record || !selected_record.get('EvnPrescrDay_id')) {
            return false;
        }
        var conf = {
            ownerWindow: this
            ,allowChangeTime: false
            //,parentEvnClass_SysNick: 'EvnSection'
            ,EvnPrescr_setDate: selected_record.get('EvnPrescr_setDate')
            ,Person_id: selected_record.get('Person_id')
            ,PersonEvn_id: selected_record.get('PersonEvn_id')
            ,Server_id: selected_record.get('Server_id')
        };
        conf.Person_Birthday = selected_record.get('Person_Birthday');
        conf.EvnPrescr_id = selected_record.get('EvnPrescrDay_id');
        conf.PrescriptionType_id = selected_record.get('PrescriptionType_id');
        conf.EvnPrescr_IsExec = selected_record.get('EvnPrescr_IsExec');
        //conf.PrescriptionStatusType_id = selected_record.get('PrescriptionStatusType_id');
        
        //for 6,7,11,12
        conf.EvnPrescr_rid  = selected_record.get('EvnPrescr_rid');
        conf.EvnPrescr_pid = selected_record.get('EvnPrescr_pid');
        conf.Diag_id = selected_record.get('Diag_id');
        conf.UslugaId_List = selected_record.get('UslugaId_List');
        conf.TableUsluga_id = selected_record.get('TableUsluga_id');
        conf.PrescriptionType_Code = selected_record.get('PrescriptionType_Code');
        //log(conf);
        conf.EvnDirection_id = selected_record.get('EvnDirection_id');
        return conf;
	},
    /**
     * Отменить выполнение назначения
     * @return {Boolean}
     */
    unExecEvnPrescr: function() {
        var grid = this.GridPanel.getGrid();
        var rec = grid.getSelectionModel().getSelected();
        var conf = this._createUnExecParams(rec);
        if ( !sw.Promed.EvnPrescr.isUnExecutable(conf) ) {
            return false;
        }
        conf.onSuccess = function(){
            rec.set('EvnPrescr_IsExec', 1);
            rec.set('IsExec_Name', lang['net']);
            rec.commit();
            grid.getSelectionModel().selectRow(grid.getStore().indexOf(rec));
        };
        conf.onCancel = function(){
            grid.getSelectionModel().selectRow(grid.getStore().indexOf(rec));
        };
        sw.Promed.EvnPrescr.unExec(conf);
        return true;
    },
    _createUnExecParams: function(rec) {
        if ( !rec || !rec.get('EvnPrescrDay_id')) {
            return false;
        }
        var conf = {
            ownerWindow: this
            ,EvnPrescrDay_id: rec.get('EvnPrescrDay_id')
            ,PrescriptionType_id: rec.get('PrescriptionType_id')
            ,EvnDirection_id: rec.get('EvnDirection_id')
            ,EvnPrescr_IsHasEvn: rec.get('EvnPrescr_IsHasEvn')
            ,EvnPrescr_IsExec: rec.get('EvnPrescr_IsExec')
            //,PrescriptionStatusType_id: rec.get('PrescriptionStatusType_id')
        };
        return conf;
    },
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	height: 550,
	id: 'EvnPrescrProcCmpJournalWindow',
	initComponent: function() {
        var thas = this;
		
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: this,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			filter: {
				title: lang['filtr'],
				collapsed: false,
				layout: 'form',
				border: false,
				defaults: {
					border: false
				},
				items: [
					{
						layout: 'column',
						items: [
							{
								layout: 'form',
								defaults: {
									anchor: '100%'
								},
								width: 300,
								labelWidth: 70,
								bodyStyle: 'padding-right: 5px;',
								items: [
									{
										fieldLabel: lang['familiya'],
										name: 'Person_SurName',
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: lang['imya'],
										name: 'Person_FirName',
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: lang['otchestvo'],
										name: 'Person_SecName',
										xtype: 'textfieldpmw'
									}
								]
							}, {
								bodyStyle: 'padding-right: 5px;',
								width: 300,
								labelWidth: 90,
								layout: 'form',
								items: [
									{
										xtype: 'swdatefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'Person_BirthDay',
										fieldLabel: lang['d_r']
									}, {
										anchor: '100%',
										listWidth: 400,
										allowBlank: true,
										xtype: 'swmedpersonalcombo'
									}, {
										comboSubject: 'YesNo',
										fieldLabel: lang['vyipolneno'],
										hiddenName: 'EvnPrescr_IsExec',
										width: 100,
										xtype: 'swcommonsprcombo'
									}
								]
							}
						]
					}, {
						layout: 'column',
						style: 'padding: 3px;',
						items: [
							{
								layout: 'form',
								items: [
									{
										handler: function() {
                                            thas.doSearch();
										},
										xtype: 'button',
										iconCls: 'search16',
										text: BTN_FRMSEARCH
									}
								]
							}, {
								layout: 'form',
								style: 'margin-left: 5px;',
								items: [
									{
										handler: function() {
                                            thas.doReset();
                                            thas.doSearch();
										},
										xtype: 'button',
										iconCls: 'resetsearch16',
										text: lang['sbros']
									}
								]
							}
						]
					}
				]
			}
		});

        this.GridPanel = new sw.Promed.ViewFrame({
            toolbar: true,
            actions: [
                {name: 'action_add', hidden: true, disabled: true },
                {name: 'action_edit',
                    handler: function(btn, e) {
                        thas.execEvnPrescr(e.getXY());
                    },
                    icon: '/img/icons/tick16.png',
                    iconCls: 'exec16',
                    id: 'EvnPrescrProcCmpJournalWindowExecEvnPrescr',
                    text: lang['vyipolnit'],
                    tooltip: lang['otmetit_naznachenie_kak_vyipolnenoe'] },
                {name: 'action_view', text: lang['prosmotr'], tooltip: lang['smotret_naznachenie'],
                    iconCls: 'view16',
                    handler: this.openEvnPrescr.createDelegate(this, ['view']) },
                {name: 'action_delete',
                    handler: this.unExecEvnPrescr.createDelegate(this),
                    iconCls: 'delete16',
                    text: lang['otmenit_vyipolnenie'],
                    tooltip: lang['otmenit_vyipolnenie_naznacheniya'] },
                {name: 'action_refresh' }/*,
                {name: 'action_print',
                    handler: function() {
                        Ext.ux.GridPrinter.print(thas.GridPanel.getGrid(), { tableHeaderText: lang['jurnal_naznacheniy'], pageTitle: lang['pechat_jurnala_naznacheniy'] });
                    },
                    iconCls: 'print16',
                    text: lang['pechat_jurnala'],
                    tooltip: lang['pechat_jurnala_naznacheniy'] }*/
            ],
            autoExpandColumn: 'autoexpand_prescr',
            autoExpandMin: 300,
            autoLoadData: false,
            dataUrl: '/?c=EvnPrescr&m=loadEvnPrescrJournalGrid',
            id: 'EvnPrescrProcCmpJournalWindowEvnPrescrProcCmpJournalFrame',
            object: 'EvnPrescrDay',
            pageSize: 100,
            paging: true,
            totalProperty: 'totalCount',
            root: 'data',
            //grouping: true,
            //groupField: 'EvnPrescr_setDate',
            region: 'center',
            stringfields: [
                { name: 'EvnPrescrDay_id', type: 'int', header: 'ID', key: true },
                { name: 'EvnPrescr_id', type: 'int', hidden: true },
                { name: 'EvnDirection_id', type: 'int', hidden: true },
                { name: 'Person_id', type: 'int', hidden: true },
                { name: 'PersonEvn_id', type: 'int', hidden: true },
                { name: 'Server_id', type: 'int', hidden: true },
                { name: 'PrescriptionType_id', type: 'int', hidden: true },
                { name: 'PrescriptionType_Code', type: 'int', hidden: true },
                { name: 'EvnPrescr_IsHasEvn', type: 'int', hidden: true },
                { name: 'ObservTimeType_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_IsCito', type: 'int', hidden: true },
                { name: 'EvnPrescr_IsExec', type: 'int', hidden: true },
                { name: 'EvnPrescr_setDate', type: 'date', format: 'd.m.Y', header: lang['data'], width: 70 },
                { name: 'EvnPrescr_setTime', type: 'string', header: lang['vremya'], width: 45 },
                { name: 'IsExec_Name', type: 'string', header: lang['vyipolneno'], width: 45 },
                { name: 'PrescriptionType_Name', type: 'string', header: "Тип назначения", width: 100 },
                { name: 'Person_FIO', type: 'string', header: lang['patsient'], width: 250 },
                { name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: "Дата рождения", width: 100 },
                { name: 'LpuSectionWard_id', type: 'int', hidden: true },
                { name: 'Sex_id', type: 'int', hidden: true },
                { name: 'LpuSectionWard_Name', type: 'string', header: lang['palata'], width: 100 },
                { name: 'EvnPrescr_Name', type: 'string', header: lang['naznachenie'], id: 'autoexpand_prescr' },
                { name: 'EvnSection_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_pid', type: 'int', hidden: true },
                { name: 'EvnPrescr_rid', type: 'int', hidden: true },
                { name: 'Diag_id', type: 'int', hidden: true },
                { name: 'UslugaId_List', type: 'string', hidden: true },
                { name: 'pmUser_insName', type: 'string', dataIndex: 'pmUser_insName', header: "Назначил врач", width: 200 },
                { name: 'EvnPrescr_insDT', type: 'string', header: "Дата, время формирования назначения", width: 100 }
            ],
            onBeforeLoadData: function() {
                //this.setActionDisabled('open_emk', true);
                this.setActionDisabled('update_ward', true);
                this.setActionDisabled('action_view', true);
                //thas.getButtonSearch().disable();
            },
            onLoadData: function() {
                //thas.getButtonSearch().enable();
				if (this.getGrid().getStore().reader.jsonData['countPerson'])
					this.getGrid().getBottomToolbar().displayMsg = lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}_vsego_patsientov']+this.getGrid().getStore().reader.jsonData['countPerson'];
            },
            onRowSelect: function(sm,index,record) {
                var isActionable = (typeof record == 'object');
                //this.setActionDisabled('open_emk', !isActionable );
                this.setActionDisabled('update_ward', !isActionable );
                this.setActionDisabled('action_view', !isActionable );
                var isExecutable = (isActionable && sw.Promed.EvnPrescr.isExecutable(thas._createExecParams(record)));
                var isUnExecutable = (isActionable && sw.Promed.EvnPrescr.isUnExecutable(thas._createUnExecParams(record)));
                this.setActionDisabled('action_edit', !isExecutable);
                this.setActionDisabled('action_delete', !isUnExecutable);
                if ( record && this.getAction('update_ward').menu ) {
                    this.getAction('update_ward').menu.items.each(function(item,i,l) {
                        var equalityWard = item.LpuSectionWard_id == record.get('LpuSectionWard_id');
                        item.setVisible( (item.Sex_id == record.get('Sex_id') && !equalityWard) ||	( item.Sex_id == null && !equalityWard ) );
                    });
                }
            },
            onDblClick: function(sm,index,record) {
                this.getAction('action_view').execute();
            }
        });

		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			collapsible: true,
			titleCollapse: true,
			floatable: false,
			animCollapse: false,
			region: 'west',
			enableDefaultActions: true, 
			panelActions: this.buttonPanelActions
		});
		
		Ext.apply(this, {
			buttons: [/*{
			    handler: function() {
					this.doSearch();
				}.createDelegate(this),
			    iconCls: 'search16',
			    text: BTN_FRMSEARCH
			}, {
			    handler: function() {
					this.doReset();
					this.doSearch();
			    }.createDelegate(this),
			    iconCls: 'resetsearch16',
			    text: lang['sbros']
			}, */{
				text: '-'
			},
            {
                text: BTN_FRMHELP,
                iconCls: 'help16',
                handler: function() {
                    ShowHelp(lang['jurnal_naznacheniy']);
                }
            },
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_ESTEF + 36,
				text: BTN_FRMCLOSE
			}],
			items: [
				this.leftPanel,
				this.FilterPanel,
				this.GridPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrProcCmpJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrProcCmpJournalWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	
	createListLpuSectionWard: function() {
		sw.Promed.LpuSectionWard.createListLpuSectionWard({
			LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id,
			date: (new Date()).format('d.m.Y'),
			id: 'EPJW_WardMenu',
			getParams: function(){
				var params = {},
					sel_record = this.GridPanel.getGrid().getSelectionModel().getSelected();
					
					if( sel_record ) {
						params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
						params.ignore_sex = false;
						params.EvnSection_id = sel_record.get('EvnSection_id');
						params.Sex_id = sel_record.get('Sex_id');
						params.Person_id = sel_record.get('Person_id');
						params.LpuSectionWardCur_id = sel_record.get('LpuSectionWard_id');
					}
				return params;
			}.createDelegate(this),
			callback: function(menu){
                //log(this.GridPanel.getAction('update_ward'));
                this.GridPanel.getAction('update_ward').menu = menu;
                this.GridPanel.getAction('update_ward').each(function(item){
                    item.menu = menu;
                });
				var sm = this.GridPanel.getGrid().getSelectionModel(),
					sel_record = sm.getSelected();
				if( sel_record ) {
					sm.fireEvent('rowselect', sm, this.GridPanel.getGrid().getStore().indexOf(sel_record), sel_record); 
				}
			}.createDelegate(this),
			onSuccess: function(params) {
				var menu = this.GridPanel.getAction('update_ward').menu,
					sel_record = this.GridPanel.getGrid().getSelectionModel().getSelected();
				menu.items.each(function(item) {
					if( item.LpuSectionWard_id == params.LpuSectionWard_id ) {
						sel_record.set('LpuSectionWard_id', item.LpuSectionWard_id);
						sel_record.set('LpuSectionWard_Name', item.text);
					}
				});
				sel_record.commit();
				//также обновляем меню палат LpuSectionWard_id_10
				this.createListLpuSectionWard();
			}.createDelegate(this)
		});
	},
	
	openEmk: function() {
		var grid = this.GridPanel.getGrid(),
			record = grid.getSelectionModel().getSelected();
			
		if ( !record ) return false;
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			//TimetableGraf_id: record.get('TimetableGraf_id'),
			mode: 'workplace',
			//isMyOwnRecord: isMyOwnRecord,
			ARMType: 'common'//'stacnurse'
		});
	},
	
	doSearch: function(mode)
	{
		var params = this.FilterPanel.getForm().getValues();
		var btn = this.getPeriodToggle(mode);
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
		
		var begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.PrescriptionType_id = 6;
		params.EvnPrescr_setDate_Range = begDate + ' - ' + endDate;
		params.limit = 100;
        params.start = 0;
		params.isClose = this.isClose;
        params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
        this.GridPanel.removeAll({addEmptyRecord: false, clearAll: true});
        this.GridPanel.getGrid().getStore().load({
            callback: function(records, options, success) {
                //loadMask.hide();
            },
            params: params
        });
	},
    doReset: function()
    {
        
        this.FilterPanel.getForm().reset();
        //var date = new Date();
        //base_form.findField('EvnPrescr_setDate_Range').setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' + Ext.util.Format.date(date, 'd.m.Y'));
       
        this.GridPanel.removeAll({addEmptyRecord: false, clearAll: true});
        this.GridPanel.setActionDisabled('action_edit', true);
        this.GridPanel.setActionDisabled('action_delete', true);
        //this.GridPanel.setActionDisabled('open_emk', true);
        this.GridPanel.setActionDisabled('update_ward', true);
        this.GridPanel.setActionDisabled('action_view', true);
    },
	
	openEvnPrescr: function( action ) {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;
        if( action != 'view' ) return false;
        var conf = {
            parentEvnClass_SysNick: 'EvnSection',
            userMedStaffFact: this.userMedStaffFact,
            action: action,
            PrescriptionType_id:6,
            PrescriptionType_Code: 6,
            data: {
                Evn_pid: record.get('EvnPrescr_pid'),
                EvnPrescr_id: record.get('EvnPrescr_id')
            },
            callbackEditWindow: function() {
                //
            },
            onHideEditWindow: function() {
                //
            }
        };
        sw.Promed.EvnPrescr.openEditWindow(conf);
        return true;
	},

	loadMask: null,
	maximizable: true,
    maximized: false,
    gridPanelAutoLoad: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	buttonPanelActions: {
		action_JourNotice: {
			nn:'action_JourNotice',
			text:lang['jurnal_uvedomleniy'],
			tooltip: lang['otkryit_jurnal_uvedomleniy'],
			iconCls : 'notice32',
			handler: function() { getWnd('swMessagesViewWindow').show(); }.createDelegate(this)
		}
		
	},
	
	show: function() {
		sw.Promed.swEvnPrescrProcCmpJournalWindow.superclass.show.apply(this, arguments);
		if (!this.GridPanel.getAction('action_isclosefilter')) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: lang['vse'],
						handler: function() {
							this.GridPanel.getAction('action_isclosefilter').setText(lang['sluchay_zakonchen_vse']);
							this.isClose = null;
							this.doSearch();
						}.createDelegate(this)
					}),
					new Ext.Action({
						text: lang['net'],
						handler: function() {
							this.GridPanel.getAction('action_isclosefilter').setText(lang['sluchay_zakonchen_net']);
							this.isClose = 1;
							this.doSearch();
						}.createDelegate(this)
					}),
					new Ext.Action({
						text: lang['da'],
						handler: function() {
							this.GridPanel.getAction('action_isclosefilter').setText(lang['sluchay_zakonchen_da']);
							this.isClose = 2;
							this.doSearch();
						}.createDelegate(this)
					})
				]
			});

			this.GridPanel.addActions({
				name: 'action_isclosefilter',
				text: lang['sluchay_zakonchen_net'],
				menu: menuIsCloseFilter
			});
			this.isClose = 1;
		}
        /*if (!this.GridPanel.getAction('open_emk')) {
            this.GridPanel.addActions({
                name:'open_emk',
                iconCls: 'open16',
                text: lang['otkryit_emk'],
                tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
                handler: this.openEmk.createDelegate(this)
            });
        }*/
        if (!this.GridPanel.getAction('update_ward')) {
            this.GridPanel.addActions({
                name:'update_ward',
                handler: null,
                iconCls: 'update-ward16',
                text: lang['perevod_v_palatu'],
                tooltip: lang['perevesti_patsienta_v_druguyu_palatu'],
                menu: new Ext.menu.Menu()
            });
        }

		this.restore();
		this.center();
		this.maximize();

		if(!arguments[0]) {
			arguments = [{}];
		}
		if(!arguments[0].userMedStaffFact) {
			arguments[0].userMedStaffFact = {};
		}

		if ( arguments[0].userMedStaffFact.LpuSection_Name ) {
			this.setTitle(lang['jurnal_vyipolnennyih_protsedur_-'] + arguments[0].userMedStaffFact.LpuSection_Name);
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		else {
			this.setTitle(lang['jurnal_vyipolnennyih_protsedur']);
			this.userMedStaffFact = null;
		}
		
		var base_form = this.FilterPanel.getForm();
		
		this.FilterPanel.fieldSet.expand();
		//base_form.findField('LpuSectionWard_id').setContainerVisible(false);
		
		
		/*
		* Форма может работать в нескольких режимах:
		* 1. как АРМ постовой медсестры
		* 2. как АРМ мед. сестры процедурного кабинета (стац. и пол-ка) - как служба уровеня отделения
		* 3. как журнал назначений для врача
		*/
		if(arguments[0].userMedStaffFact.ARMType) {
			switch(arguments[0].userMedStaffFact.ARMType) {
				case 'stacnurse':
					this.formMode = 'workplace';
					//this.setTitle('АРМ постовой медсестры - ' + this.userMedStaffFact.LpuSection_Name);
					sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
					this.LeftPanel.setVisible(true);
                    //this.GridPanel.setActionHidden('open_emk', false);
                    this.GridPanel.setActionHidden('update_ward', false);
					
					base_form.findField('LpuSectionWard_id').setContainerVisible(true);
					base_form.findField('LpuSectionWard_id').getStore().load({
						params: {
							LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id
						}
					});
					this.createListLpuSectionWard();
					
					break;
				default://'common','stac',null
					this.formMode = 'journal';
					this.LeftPanel.setVisible(false);
                    //this.GridPanel.setActionHidden('open_emk', true);
                    this.GridPanel.setActionHidden('update_ward', true);
					break;
			}
		} else if( arguments[0].MedService_id && arguments[0].MedService_id > 0 ) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, {
				MedService_id: arguments[0].MedService_id,
				MedService_Name: arguments[0].MedService_Name,
				MedPersonal_id: getGlobalOptions().CurMedPersonal_id
			});
			this.LeftPanel.setVisible(true);
            this.GridPanel.setActionHidden('update_ward', true);
		}

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

        this.doReset();

		base_form.findField('MedPersonal_id').getStore().load();

        this.syncSize();
        this.doLayout();

        this.doSearch();
	},
	title: "Журнал выполненных процедур",
	width: 850
});