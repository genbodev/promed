/**
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
* @comment      Префикс для id компонентов EPRJF (EvnPrescrJournalForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrJournalWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,/*sw.Promed.BaseForm,*/ {
	codeRefresh: true,
	objectName: 'swEvnPrescrJournalWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrJournalWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	execEvnPrescr: function(coords) {
		var grid = this.GridPanel.getGrid();
		var win = this;
		var selected_record = grid.getSelectionModel().getSelected();
        var conf = this._createExecParams(selected_record);
		if ( !sw.Promed.EvnPrescr.isExecutable(conf) ) {
			return false;
		}
		conf.userMedStaffFact = this.userMedStaffFact;
        conf.btnId = 'id_EvnPrescrJournalWindowExecEvnPrescr';
        conf.coords = coords;
		conf.onExecSuccess = function(){
			selected_record.set('EvnPrescr_IsExec', 2);
			selected_record.set('IsExec_Name', lang['da']);
			selected_record.commit();
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
			win.doSearch();
		};
		conf.onExecCancel = function(){
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
			win.doSearch();
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
        //for 10
        if (10 == conf.PrescriptionType_id) {
            conf.ObservTimeType_id = selected_record.get('ObservTimeType_id');
            conf.EvnPrescr_id = selected_record.get('EvnPrescr_id');
        }
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
		var win = this;
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
			win.doSearch();
        };
        conf.onCancel = function(){
            grid.getSelectionModel().selectRow(grid.getStore().indexOf(rec));
			win.doSearch();
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
	id: 'EvnPrescrJournalWindow',
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
							}, {
								layout: 'form',
								labelWidth: 130,
								items: [
									{
										autoLoad: false,
										comboSubject: 'PrescriptionType',
										fieldLabel: lang['tip_naznacheniya'],
										hiddenName: 'PrescriptionType_id',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												combo.fireEvent('select', combo, combo.getStore().getById(newValue));
											},
											'select': function(combo, record) {
												var base_form = thas.FilterPanel.getForm();

												if ( !record || !record.get('PrescriptionType_Code') || !record.get('PrescriptionType_Code').toString().inlist([ '3', '4', '5', '6', '7' ]) ) {
													// base_form.findField('Drug_id').clearValue();
													// base_form.findField('Drug_id').setContainerVisible(false);
													// base_form.findField('DrugPrepFas_id').clearValue();
													// base_form.findField('DrugPrepFas_id').setContainerVisible(false);
													base_form.findField('PrescriptionIntroType_id').clearValue();
													base_form.findField('PrescriptionIntroType_id').setContainerVisible(false);
                                                    thas.doLayout();
													return false;
												}
												
												base_form.findField('PrescriptionIntroType_id').clearValue();
												base_form.findField('PrescriptionIntroType_id').setContainerVisible(false);

												switch ( Number(record.get('PrescriptionType_Code')) ) {
													case 3:
													case 6:
													case 7:
														// base_form.findField('Drug_id').clearValue();
														// base_form.findField('Drug_id').setContainerVisible(false);
														// base_form.findField('DrugPrepFas_id').clearValue();
														// base_form.findField('DrugPrepFas_id').setContainerVisible(false);
													break;

													case 5:
														base_form.findField('PrescriptionIntroType_id').setContainerVisible(true);
														// base_form.findField('Drug_id').setContainerVisible(true);
														// base_form.findField('DrugPrepFas_id').setContainerVisible(true);
													break;
												}
                                                thas.doLayout();
											},
											'render': function(combo) {
												var where = 'where PrescriptionType_id in (1, 2, 5, 6, 7, 10, 11, 12, 13)';
												combo.getStore().load({
													params: {
														where: where
													}
												});
											}.createDelegate(this)
										},
										typeCode: 'int',
										width: 250,
										xtype: 'swcommonsprcombo'
									}, {
										fieldLabel: lang['ochered'],
										mode: 'local',
										allowBlank: false,
										value: 1,
										store: new Ext.data.SimpleStore(
											{
												key: 'EvnQueueShow_id',
												fields:
													[
														{name: 'EvnQueueShow_id', type: 'int'},
														{name: 'EvnQueueShow_Name', type: 'string'}
													],
												data: [[0, lang['ne_pokazyivat']], [1, lang['pokazyivat']]]
											}),
										editable: false,
										enableKeyEvents: true,
										width: 250,
										triggerAction: 'all',
										lastQuery: '',
										displayField: 'EvnQueueShow_Name',
										valueField: 'EvnQueueShow_id',
										tpl: '<tpl for="."><div class="x-combo-list-item">{EvnQueueShow_Name}</div></tpl>',
										hiddenName: 'EvnQueueShow_id',
										xtype: 'combo'
									}, {
										xtype: 'daterangefield',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										name: 'EvnPrescr_insDT',
										fieldLabel: lang['data_formirovaniya_naznacheniya'],
										width: 180
									}, {
										fieldLabel: lang['profil'],
										hiddenName: 'LpuSectionProfile_id',
										listWidth: 600,
										width: 250,
										xtype: 'swlpusectionprofiledopremotecombo'
									}, {
										hiddenName: 'PrescriptionIntroType_id',
										width: 250,
										fieldLabel: lang['metod_vvedeniya'],
										comboSubject: 'PrescriptionIntroType',
										xtype: 'swcommonsprcombo'
									}, {
										xtype: 'swlpusectionwardglobalcombo'
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
                    id: 'EvnPrescrJournalWindowExecEvnPrescr',
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
            autoLoadData: false,
            dataUrl: '/?c=EvnPrescr&m=loadEvnPrescrJournalGrid',
            id: 'EvnPrescrJournalWindowEvnPrescrJournalFrame',
            object: 'EvnPrescrDay',
            pageSize: 100,
            paging: true,
            totalProperty: 'totalCount',
            root: 'data',
            sortInfo: {field:'EvnPrescr_planDate'},
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
				{ name: 'PersonQuarantine_IsOn', type: 'string', hidden: true },
                { name: 'EvnPrescr_IsCito', type: 'int', hidden: true },
                { name: 'EvnPrescr_IsExec', type: 'int', hidden: true },
                { name: 'EvnPrescr_setDate', type: 'date', hidden: true },
                { name: 'EvnPrescr_planDate', renderer: function(v, p, row) {
					if (Ext.isEmpty(v)) {
						return lang['ochered'];
					}

					return v;
				}, header: "Плановые дата и время назначения", width: 150},
				//{ name: 'EvnPrescr_setTime', type: 'string', header: "Плановое время назначения", width: 150, hidden: getGlobalOptions().region.nick != 'ufa'  },
                { name: 'EvnPrescr_execDate', type: 'datetime', header: "Дата, время выполнения", width: 150, hidden: getGlobalOptions().region.nick == 'ufa'  },	
                { name: 'EvnPrescr_execTime', type: 'string', header: "Время выполнения", hidden: getGlobalOptions().region.nick != 'ufa'  },
                { name: 'IsExec_Name', type: 'string', header: lang['vyipolneno'], width: 45 },
                { name: 'PrescriptionType_Name', type: 'string', header: "Тип назначения", width: 100 },
                { name: 'Person_FIO', type: 'string', header: lang['patsient'], width: 200 },
                { name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: "Дата рождения", width: 100 },
                { name: 'LpuSectionWard_id', type: 'int', hidden: true },
                { name: 'Sex_id', type: 'int', hidden: true },
                { name: 'LpuSectionWard_Name', type: 'string', header: lang['palata'], width: 100 },
                { name: 'EvnPrescr_Name', type: 'string', header: lang['naznachenie'], id: 'autoexpand' },
                { name: 'EvnSection_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_pid', type: 'int', hidden: true },
                { name: 'EvnPrescr_rid', type: 'int', hidden: true },
                { name: 'Diag_id', type: 'int', hidden: true },
                { name: 'UslugaId_List', type: 'string', hidden: true },
                { name: 'pmUser_insName', type: 'string', dataIndex: 'pmUser_insName', header: "Назначил врач", width: 200 },
                { name: 'EvnPrescr_insDT', type: 'string', header: "Дата, время формирования назначения", width: 150 },
                { name: 'pmUser_execName', type: 'string', dataIndex: 'pmUser_execName', header: "Выполнил врач", width: 200 },
                { name: 'PrescrFailureType_id', type: 'int', hidden: true },
                { name: 'PrescrFailureType_Name', type: 'string', hidden: getRegionNick() != 'vologda', dataIndex: 'PrescrFailureType_Name', header: "Причина невыполнения", width: 200 },
            ],
            plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{ field: 'EvnPrescr_Name', tpl: '{EvnPrescr_Name}' }
				])
			],
            onBeforeLoadData: function() {
                //this.setActionDisabled('open_emk', true);
                this.setActionDisabled('update_ward', true);
                if (getRegionNick() == 'vologda') {
					this.setActionDisabled('reason_unexec', true);
				}
                this.setActionDisabled('action_view', true);
                //thas.getButtonSearch().disable();
            },
            onLoadData: function() {
                //thas.getButtonSearch().enable();
				if (this.getGrid().getStore().reader.jsonData['countPerson']) {
					this.getGrid().getBottomToolbar().displayMsg = langs('Отображаемые строки {0} - {1} из {2}, пациентов на странице: ') + this.getGrid().getStore().reader.jsonData['countPerson'];
				} else {
					this.getGrid().getBottomToolbar().displayMsg = langs('Отображаемые строки {0} - {1} из {2}');
				}
				this.getGrid().getStore().sort('EvnPrescr_planDate','ASC');
            },
            onRowSelect: function(sm,index,record) {
                var isActionable = (typeof record == 'object');
                //this.setActionDisabled('open_emk', !isActionable );
                this.setActionDisabled('update_ward', !isActionable );
                this.setActionDisabled('action_view', !isActionable );
                var isExecutable = (isActionable && sw.Promed.EvnPrescr.isExecutable(thas._createExecParams(record)));
                var isUnExecutable = (isActionable && sw.Promed.EvnPrescr.isUnExecutable(thas._createUnExecParams(record)));
                this.setActionDisabled('action_edit', !(isExecutable && (getRegionNick() != 'vologda' || (getRegionNick() == 'vologda' && Ext.isEmpty(record.get('PrescrFailureType_id')))) ));

				if (getRegionNick() == 'vologda') {
					this.setActionDisabled('reason_unexec', !isActionable);
					this.getAction('reason_unexec').items[0].menu.items.items[0].setDisabled(
						!(isExecutable && record.get('PrescriptionType_Code') == 5 && Ext.isEmpty(record.get('PrescrFailureType_id')))
					); //добавить
					this.getAction('reason_unexec').items[0].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('PrescrFailureType_id'))); //изменить
					this.getAction('reason_unexec').items[0].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('PrescrFailureType_id'))); //удалить
				}
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
		
		this.GridPanel.getGrid().getView().getRowClass = function(row, index) {
			var cls = '';
			if (row.get('PersonQuarantine_IsOn') == 'true') {
				cls = cls + 'x-grid-rowbackred ';
			}
			return cls;
		};

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
					if(thas.userMedStaffFact.ARMType == 'stacnurse'){
						ShowHelp(lang['arm_postovoy_medsestryi']);
					}else{
						ShowHelp(lang['jurnal_naznacheniy']);
					}
                    
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

		sw.Promed.swEvnPrescrJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrJournalWindow');

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
				/*var menu = this.GridPanel.getAction('update_ward').menu,
					sel_record = this.GridPanel.getGrid().getSelectionModel().getSelected();
				menu.items.each(function(item) {
					if( item.LpuSectionWard_id == params.LpuSectionWard_id ) {
						sel_record.set('LpuSectionWard_id', item.LpuSectionWard_id);
						sel_record.set('LpuSectionWard_Name', item.text);
					}
				});
				sel_record.commit();*/
				
				//обновляем весь грид, т.к. палата могла поменяться в нескольких назначениях
				this.GridPanel.getGrid().getStore().reload();
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
		params.MedService_id = this.MedService_id;
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

		params.isClose = this.isClose;
		params.EvnPrescr_setDate_Range = begDate + ' - ' + endDate;
		params.limit = 100;
        params.start = 0;
		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}
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
        var pt_combo = this.FilterPanel.getForm().findField('PrescriptionType_id');

        this.FilterPanel.getForm().reset();
        //var date = new Date();
        //base_form.findField('EvnPrescr_setDate_Range').setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' + Ext.util.Format.date(date, 'd.m.Y'));
        pt_combo.setValue(null);
        pt_combo.fireEvent('change', pt_combo, pt_combo.getValue());
        this.GridPanel.removeAll({addEmptyRecord: false, clearAll: true});
        this.GridPanel.setActionDisabled('action_edit', true);
        this.GridPanel.setActionDisabled('action_delete', true);
        //this.GridPanel.setActionDisabled('open_emk', true);
        this.GridPanel.setActionDisabled('update_ward', true);
		if (getRegionNick() == 'vologda') {
			this.GridPanel.setActionDisabled('reason_unexec', true);
		}
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
            PrescriptionType_id: record.get('PrescriptionType_id'),
            PrescriptionType_Code: record.get('PrescriptionType_Code'),
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

	setUnExecReason: function(edit) {
		var grid = this.GridPanel.getGrid();
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;

		getWnd('swEvnPrescrUnExecReasonWindow').show({
			callback: function() {
				grid.getStore().reload();
			},
			EvnPrescr_id: record.get('EvnPrescr_id'),
			PrescrFailureType_id: edit ? record.get('PrescrFailureType_id'):null,
			onHide: function() {

			}
		});
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
		action_PersonEvnPSList: {
			nn:'action_PersonEvnPSList',
			text:lang['kontrol_dvijeniya_originalov_ib'],
			tooltip: lang['otkryit_kontrol_dvijeniya_originalov_ib'],
			iconCls : 'patient-data32',
			handler: function() { getWnd('swPersonEvnPSListWindow').show(); }.createDelegate(this)
		},
		action_OstatRegistry: {
			text: 'Просмотр остатков отделения',
			tooltip: 'Просмотр остатков отделения',
			iconCls: 'rls-torg32',
			handler: function() {
				var wnd  = Ext.getCmp('EvnPrescrJournalWindow');

				getWnd('swDrugOstatRegistryListWindow').show({
					mode: 'mo',
					userMedStaffFact: wnd.userMedStaffFact
				});
			}
		},
		
		action_JourNotice: {
			nn:'action_JourNotice',
			text:lang['jurnal_uvedomleniy'],
			tooltip: lang['otkryit_jurnal_uvedomleniy'],
			iconCls : 'notice32',
			handler: function() { getWnd('swMessagesViewWindow').show(); }.createDelegate(this)
		},
		
		// #175117. Располагаем стандартную кнопку "Просмотр отчетов" перед
		// кнопкой открытия формы "Журнал учета рабочего времени сотрудников".
		// Конфигурация этой стандартной кнопки заполняется в родительском классе:
		action_Report: {},

		// #175117. Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
		action_TimeJournal:
		{
			nn: 'action_TimeJournal',
			text: langs('Журнал учета рабочего времени сотрудников'),
			tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
			iconCls: 'report32',
			disabled: false,

			handler:
				function()
				{
					var cur = sw.Promed.MedStaffFactByUser.current;

					getWnd('swTimeJournalWindow').show(
						{
							ARMType: (cur ? cur.ARMType : undefined),
							MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
							Lpu_id: (cur ? cur.Lpu_id : undefined)
						});
				}
		},

		action_Ers: {
			nn: 'action_Ers',
			text: 'ЭРС',
			menuAlign: 'tr',
			tooltip: 'ЭРС',
			// todo
			//iconCls: 'reports32',
			//hidden: !(isUserGroup('WorkGraph')), 
			/*Кнопка доступна для пользователей, включенных в любую из групп доступа:
			•	ЭРС. Оформление документов
			•	ЭРС. Руководитель МО
			•	ЭРС. Бухгалтер*/
			menu: [{
				text: 'Журнал Родовых сертификатов',
				handler: function () {
					getWnd('swEvnErsJournalWindow').show();
				}
			}, {
				text: 'Журнал Талонов',
				handler: function () {
					
				}
			}, {
				text: 'Журнал учета детей',
				handler: function () {
					
				}
			}, {
				text: 'Журнал талонов и счета на оплату',
				handler: function () {
					
				}
			}]
		},

		// #175117. Располагаем стандартную кнопку "Просмотр отчетов" перед
		// кнопкой открытия формы "Журнал учета рабочего времени сотрудников".
		// Конфигурация этой стандартной кнопки заполняется в родительском классе:
		action_Report: {},
		
		// #175117. Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
		action_TimeJournal:
		{
			nn: 'action_TimeJournal',
			text: langs('Журнал учета рабочего времени сотрудников'),
			tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
			iconCls: 'report32',
			disabled: false,

			handler:
				function()
				{
					var cur = sw.Promed.MedStaffFactByUser.current;

					getWnd('swTimeJournalWindow').show(
						{
							ARMType: (cur ? cur.ARMType : undefined),
							MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
							Lpu_id: (cur ? cur.Lpu_id : undefined)
						});
				}
		}
	},
	
	show: function() {
		sw.Promed.swEvnPrescrJournalWindow.superclass.show.apply(this, arguments);
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
		if (!this.GridPanel.getAction('reason_unexec') && getRegionNick() == 'vologda') {
			this.GridPanel.addActions({
				name:'reason_unexec',
				tooltip: langs('Указать или изменить причину невыполнения назначения'),
				menu: new Ext.menu.Menu({
					items: [
						new Ext.Action({
							text: langs('Добавить'),
							handler: function() {
								//открываем форму добавления причины невыполнения на добавление
								this.setUnExecReason(false);
							}.createDelegate(this)
						}),
						new Ext.Action({
							text: langs('Изменить'),
							handler: function() {
								//открываем форму добавления причины невыполнения на редактирование
								this.setUnExecReason(true);
							}.createDelegate(this)
						}),
						new Ext.Action({
							text: langs('Удалить'),
							handler: function() {
								that = this;
								//удаляем причину невыполнения
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'yes' ) {
											var grid = that.GridPanel.getGrid();
											var record = that.GridPanel.getGrid().getSelectionModel().getSelected();
											if( !record ) return false;

											that.getLoadMask().show();
											Ext.Ajax.request({
												callback: function(options, success, response) {
													that.getLoadMask().hide();
													grid.getStore().reload();
												}.createDelegate(that),
												params: {
													EvnPrescr_id: record.get('EvnPrescr_id')
												},
												url: '/?c=EvnPrescr&m=saveEvnPrescrUnExecReason'
											});
										}
									},
									icon: Ext.MessageBox.QUESTION,
									msg: 'Удалить причину невыполнения?',
									title: langs('Вопрос')
								});
							}.createDelegate(this)
						})
					]
				}),
				text: langs('Причина невыполнения'),
				handler: null,
				iconCls: 'info16'
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
			this.setTitle(WND_PRESCR_REG + ' - ' + arguments[0].userMedStaffFact.LpuSection_Name);
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		else {
			this.setTitle(WND_PRESCR_REG);
			this.userMedStaffFact = null;
		}
		
		var base_form = this.FilterPanel.getForm();

		var LpuSection_id = null;
		if (this.userMedStaffFact && !Ext.isEmpty(this.userMedStaffFact.LpuSection_id)) {
			LpuSection_id = this.userMedStaffFact.LpuSection_id;
		} else {
			LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		}
		
		this.FilterPanel.fieldSet.expand();
		base_form.findField('LpuSectionWard_id').setContainerVisible(false);
		base_form.findField('LpuSectionProfile_id').setContainerVisible(false);
		if (getRegionNick() == 'kz') {
			if(arguments[0].userMedStaffFact.ARMType && arguments[0].userMedStaffFact.ARMType == 'stacnurse') {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().removeAll();
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						LpuSection_id: LpuSection_id
					},
					callback: function () {
						if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 1) {
							base_form.findField('LpuSectionProfile_id').setContainerVisible(true);
						}
					}
				});
			}
		}
		
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
					base_form.findField('LpuSectionWard_id').setContainerVisible(true);
					base_form.findField('LpuSectionWard_id').getStore().load({
						params: {
							LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id
						}
					});
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
		this.MedService_id = arguments[0].userMedStaffFact.MedService_id;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

        this.doReset();

		base_form.findField('MedPersonal_id').getStore().load();

		this.GridPanel.ViewGridStore.sortData = function (f, direction){
	        direction = direction || 'ASC';
	        var st = this.fields.get(f).sortType;
	     	var multipleSortInfo = this.fields.get(f).multipleSortInfo;
	     	var caseInsensitively = this.fields.get(f).caseInsensitively;
			if (typeof direction == 'object') {
				multipleSortInfo = direction;
				direction = 'ASC';
			}
			if(f == 'EvnPrescr_planDate') {
				var fn = function(r1, r2){
		            var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
	            	if(v1 == null && v2 == null){
	            		var ret = 0;
	            	} else if(v1 == null){
	            		var ret = (direction == 'ASC') ? 1 : -1;
	            	} else if(v2 == null){
	            		var ret = (direction == 'ASC') ? -1 : 1;
	            	} else {
	            		var av1 = v1.split(' ');
	            		var av2 = v2.split(' ');
	            		if(av1[0] == av2[0]){
	            			var ret = av1[1] > av2[1] ? 1 : (av1[1] < av2[1] ? -1 : 0);
	            		} else {
	            			abv1 = av1[0].split('.');
	            			abv2 = av2[0].split('.');
	            			if(abv1[2] == abv2[2]){
	            				if(abv1[1] == abv2[1]){
		            				var ret = abv1[0] > abv2[0] ? 1 : (abv1[0] < abv2[0] ? -1 : 0);
		            			} else {
		            				var ret = abv1[1] > abv2[1] ? 1 : (abv1[1] < abv2[1] ? -1 : 0);
		            			}
	            			} else {
	            				var ret = abv1[2] > abv2[2] ? 1 : (abv1[2] < abv2[2] ? -1 : 0);
	            			}
	            		}
	            	}

		            return ret;
		        };
			} else {
				var fn = function(r1, r2){
		            var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
	            	if (caseInsensitively !== undefined && v1.toLowerCase) {
						v1 = v1.toLowerCase();
						v2 = v2.toLowerCase();
					}
		            var ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
					if (multipleSortInfo !== undefined) {
						ret = 0;
					}
					for (i = 0 ; (multipleSortInfo !== undefined && ret == 0 && i < multipleSortInfo.length); i++) {
						var x1 = r1.data[multipleSortInfo[i].field], x2 = r2.data[multipleSortInfo[i].field];
						var dir = (direction != multipleSortInfo[i].direction) ? direction.toggle("ASC", "DESC") : direction;
						   ret = (x1 > x2) ? 1 : ((x1 < x2) ? -1 : 0);
						   if (dir == 'DESC') ret = -ret;
					};
					
		            return ret;
		        };
			}

	        this.data.sort(direction, fn);
	        if(this.snapshot && this.snapshot != this.data){
	            this.snapshot.sort(direction, fn);
	        }
	    };

        this.syncSize();
        this.doLayout();

		// При открытии АРМ постовой медсестры осуществляется поиск при нажатии кнопки "Найти"
        if(arguments[0].userMedStaffFact.ARMType != 'stacnurse'){
	        this.doSearch();
	    }
	},
	title: WND_PRESCR_REG,
	width: 850
});
