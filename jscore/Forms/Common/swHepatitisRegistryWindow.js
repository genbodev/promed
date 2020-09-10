/**
* swHepatitisRegistryWindow - окно регистра по Вирусному гепатиту
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      
* @comment      Префикс для id компонентов HRW (HepatitisRegistryWindow)
*
*/
sw.Promed.swHepatitisRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('HRW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('HepatitisRegistryFilterForm').getForm();
		base_form.reset();
		this.HepatitisRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.HepatitisRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.HepatitisRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.HepatitisRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.HepatitisRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.HepatitisRegistrySearchFrame.getGrid().getStore().removeAll();
		this.HepatitisRegistrySearchFrame.getGrid().getViewFrame().removeAll();// #138061 неправильное отображение количества записей и счетчика страниц
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('HepatitisRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('HepatitisRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.HepatitisRegistrySearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka'] + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? lang['po_sostoyaniyu_na_moment_sluchaya'] : lang['po_vsem_periodikam']) + lang['pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('HepatitisRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.HepatitisRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	height: 550,
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['person_register_dis','add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.HepatitisRegistrySearchFrame.getGrid();

		if ( action != 'add' && !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var params = new Object();
		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'person_register_dis':
				sw.Promed.personRegister.out({
                    MorbusType_SysNick: 'hepa'
					,PersonRegister_id: selected_record.get('PersonRegister_id')
					,Person_id: selected_record.get('Person_id')
					,Diag_Name: selected_record.get('Diag_Name')
					,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
					,callback: function(data) {
						grid.getStore().reload();
					}
				});
				break;
			case 'add':
				sw.Promed.personRegister.add({
                    MorbusType_SysNick: 'hepa',
                    viewOnly: (cur_win.editType=='onlyRegister')?true:false,
					callback: function(data) {
						form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);
						cur_win.doSearch();
					}
				});
				break;
            case 'edit':
			case 'view':
				if (getWnd('swMorbusHepatitisWindow').isVisible()) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
					return false;
				}
				if ( Ext.isEmpty(selected_record.get('MorbusHepatitis_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
					return false;
				}
				params.onHide = function(isChange) {
					if(isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};
				params.allowSpecificEdit = ('edit' == action);
				params.callback = Ext.emptyFn;
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				params.editType = cur_win.editType;
				params.action = cur_win.HepatitisRegistrySearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				getWnd('swMorbusHepatitisWindow').show(params);
				break;
		}
		

		
	},
	getRecordsCount: function() {
		var st = this.HepatitisRegistrySearchFrame.getGrid().getStore();
		var noLines = false;
		if(st.totalLength == 0){
			noLines = true;
		}else if(st.totalLength == 1){
			if(typeof(st.getAt(0)) == 'undefined'){// бывает после нажатия "Обновить"
				noLines = true;
			}else if(! st.getAt(0).get('PersonRegister_id')){// если запись пустая
				noLines = true;
			}
		}
		if(noLines){
			sw.swMsg.alert('Подсчет записей', 'Найдено записей: 0');
			return;
		}

		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	initComponent: function() {
		
		this.HepatitisRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', handler: function() { this.openWindow('edit'); }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'HRW_HepatitisRegistrySearchGrid',
			object: 'HepatitisRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'MorbusHepatitis_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150, id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150},
				{name: 'HepatitisDiagType_Name', type: 'string', header: lang['diagnoz'], width: 150},
				{name: 'HepatitisQueueType_Name', type: 'string', header: lang['tip_ocheredi'], width: 120},
				{name: 'MorbusHepatitisQueue_Num', type: 'string', header: lang['nomer_v_ocheredi'], width: 120},
				{name: 'MorbusHepatitisQueue_IsCure', type: 'string', header: lang['lechenie_provedeno'], width: 120},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170}
				,{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true}
				,{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled( false );
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('MorbusHepatitis_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('MorbusHepatitis_id')) );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'HepatitisRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'HepatitisRegistry',
			tabIndexBase: TABINDEX_HRW,
			tabPanelHeight: 225,
			tabPanelId: 'HRW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = this.getFilterForm().getForm();
						form.findField('PersonRegisterType_id').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['6_registr'],
				items: [{
					xtype: 'swpersonregistertypecombo',
					hiddenName: 'PersonRegisterType_id',
					fieldLabel: lang['tip_zapisi_registra'],
					width: 200
				}, {
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}]
				/*
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = this.getFilterForm().getForm();
						form.findField('DispLpuSection_id').getStore().load({
							params: {
								Lpu_id: form.findField('DispLpu_id').getValue()
							}
						});
						form.findField('DispMedPersonal_id').getStore().load({
							params: {
								ignoreWorkInLpu: true // 'получить список ВСЕХ докторов, в не зависимости от того работают они на данный момент в ЛПУ или нет'
							}
						});
						form.findField('DispLpuSection_id').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['6_dispansernyiy_uchet'],
				items: [{
					hiddenName: 'DispLpu_id',
					xtype: 'swlpucombo',
					width: 350,
					listeners: {
						'change': function(field, newValue, oldValue) {
							var form = this.getFilterForm().getForm();
							form.findField('DispLpuSection_id').clearValue();
							form.findField('DispLpuSection_id').getStore().load({
								params: {
									Lpu_id: newValue
								}
							});
						}.createDelegate(this)
					}
				}, {
					hiddenName: 'DispLpuSection_id',
					xtype: 'swlpusectioncombo',
					width: 350
				}, {
					fieldLabel: lang['vrach'],
					hiddenName: 'DispMedPersonal_id',
					xtype: 'swmedpersonalcombo',
					allowBlank: true,
					width: 350,
					anchor: 'auto'
				}, {
					fieldLabel: lang['diapazon_dat_postanovki_na_uchet'],
					name: 'PersonDisp_begDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['diapazon_dat_snyatiya_s_ucheta'],
					name: 'PersonDisp_endDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
					xtype: 'swdispouttypecombo',
					editable: false,
					hiddenName: 'DispOutType_id',
					codeField: 'DispOutType_Code',
					fieldLabel: lang['prichina_snyatiya_c_ucheta'],
					tpl:
						'<tpl for="."><div class="x-combo-list-item">'+
						'<font color="red">{DispOutType_Code}</font>&nbsp;{DispOutType_Name}' +
						'</div></tpl>',
					width: 350
				}, {
					fieldLabel: lang['postavlen_na_dispuchet_po_virusnomu_gepatitu_po_mestu_prikrepleniya'],
					hiddenName: 'isDispAttachAddress',
					xtype: 'swyesnocombo'
				}]
				*/
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 180,
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('MorbusHepatitisDiag_setDT_Range').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['7_diagnozyi'],
				items: [{
					fieldLabel: lang['data_ustanovki_diagnoza'],
					name: 'MorbusHepatitisDiag_setDT_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
                    name: 'HepatitisDiagType_id',
                    comboSubject: 'HepatitisDiagType',
                    fieldLabel: lang['diagnoz_nositel'],
                    xtype: 'swcommonsprcombo',
                    width: 250
                }, {
                    fieldLabel: lang['diagnoz_po_mkb-10'],
                    hiddenName: 'Diag_id',
                    listWidth: 620,
                    MorbusType_SysNick: 'hepa',
                    width: 290,
                    xtype: 'swdiagcombo'
                }, {
                    border: false,
                    layout: 'column',
                    items: [{
                        border: false,
                        layout: 'form',
                        items: [{
                            fieldLabel: lang['diagnoz_po_mkb-10_s'],
                            hiddenName: 'Diag_Code_From',
                            listWidth: 620,
                            valueField: 'Diag_Code',
                            MorbusType_SysNick: 'hepa',
                            width: 290,
                            xtype: 'swdiagcombo'
                        }]
                    }, {
                        border: false,
                        layout: 'form',
                        labelWidth: 35,
                        items: [{
                            fieldLabel: lang['po'],
                            hiddenName: 'Diag_Code_To',
                            listWidth: 620,
                            valueField: 'Diag_Code',
                            MorbusType_SysNick: 'hepa',
                            width: 290,
                            xtype: 'swdiagcombo'
                        }]
                    }]
                }, {
					name: 'HepatitisDiagActiveType_id',
					comboSubject: 'HepatitisDiagActiveType',
					fieldLabel: lang['aktivnost'],
					xtype: 'swcommonsprcombo',
					width: 250
				}, {
					name: 'HepatitisFibrosisType_id',
					comboSubject: 'HepatitisFibrosisType',
					fieldLabel: lang['fibroz'],
					xtype: 'swcommonsprcombo',
					width: 250
				}, {
					name: 'HepatitisEpidemicMedHistoryType_id',
					comboSubject: 'HepatitisEpidemicMedHistoryType',
					fieldLabel: lang['epidanamnez'],
					typeCode: 'int',
					xtype: 'swcommonsprcombo',
					width: 250
				}, {
					name: 'MorbusHepatitis_EpidNum',
					fieldLabel: lang['epidnomer'],
					autoCreate: {tag: "input", size:8, maxLength: "8", autocomplete: "off"},
					maskRe: /[0-9]/,
					xtype: 'textfield',
					width: 120
				}]
			}, {
				/*
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 250,
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('MorbusHepatitisLabConfirm_setDT_Range').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['8_labor_podtverjdeniya'],
				items: [{
					fieldLabel: lang['data_issledovaniya'],
					name: 'MorbusHepatitisLabConfirm_setDT_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
					name: 'HepatitisLabConfirmType_id',
					comboSubject: 'HepatitisLabConfirmType',
					fieldLabel: lang['tip'],
					xtype: 'swcommonsprcombo',
					width: 450
				}, {
					name: 'MorbusHepatitisLabConfirm_Result',
					fieldLabel: lang['rezultat'],
					xtype: 'textfield',
					width: 450
				}]
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 250,
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('MorbusHepatitisFuncConfirm_setDT_Range').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['9_instr_podtverjdeniya'],
				items: [{
					fieldLabel: lang['data_issledovaniya'],
					name: 'MorbusHepatitisFuncConfirm_setDT_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 170,
					xtype: 'daterangefield'
				}, {
					name: 'HepatitisFuncConfirmType_id',
					comboSubject: 'HepatitisFuncConfirmType',
					fieldLabel: lang['tip'],
					xtype: 'swcommonsprcombo',
					width: 450
				}, {
					name: 'MorbusHepatitisFuncConfirm_Result',
					fieldLabel: lang['rezultat'],
					xtype: 'textfield',
					width: 450
				}]
			}, {
				*/
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('MorbusHepatitisCure_begDT').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['10_lechenie'],
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_lecheniya_s'],
							name: 'MorbusHepatitisCure_begDT',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 40,
						layout: 'form',
						items: [{
							fieldLabel: lang['po'],
							name: 'MorbusHepatitisCure_endDT',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							xtype: 'swdatefield'
						}]
					}]
				}, {
					name: 'MorbusHepatitisCure_Drug',
					fieldLabel: lang['preparat'],
					xtype: 'textfield',
					width: 250
				}, {
					name: 'HepatitisResultClass_id',
					comboSubject: 'HepatitisResultClass',
					fieldLabel: lang['rezultat'],
					xtype: 'swcommonsprcombo',
					width: 250
				}, {
					name: 'HepatitisSideEffectType_id',
					comboSubject: 'HepatitisSideEffectType',
					fieldLabel: lang['pobochnyiy_effekt'],
					xtype: 'swcommonsprcombo',
					width: 250
				}]
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('HepatitisQueueType_id').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['11_ochered'],
				items: [{
					name: 'HepatitisQueueType_id',
					comboSubject: 'HepatitisQueueType',
					fieldLabel: lang['tip_ocheredi'],
					xtype: 'swcommonsprcombo',
					width: 250
				}, {
					name: 'MorbusHepatitisQueue_Num',
					fieldLabel: lang['nomer_v_ocheredi'],
					autoCreate: {tag: "input", size:4, maxLength: "4", autocomplete: "off"},
					maskRe: /[0-9]/,
					xtype: 'textfield',
					width: 120
				}, {
					fieldLabel: lang['lechenie_provedeno'],
					hiddenName: 'MorbusHepatitisQueue_IsCure',
					xtype: 'swyesnocombo'
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_HRW + 120,
				id: 'HRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_HRW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					var base_form = this.findById('HepatitisRegistryFilterForm').getForm();
					var record;
					base_form.findField('MedPersonal_cid').setValue(null);
					if ( base_form.findField('MedStaffFact_cid') ) {
						var med_personal_record = base_form.findField('MedStaffFact_cid').getStore().getById(base_form.findField('MedStaffFact_cid').getValue());

						if ( med_personal_record ) {
							base_form.findField('MedPersonal_cid').setValue(med_personal_record.get('MedPersonal_id'));
						}
					}
					base_form.submit();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_HRW + 122,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_HRW + 123,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('HRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('HRW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_HRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('HepatitisRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.HepatitisRegistrySearchFrame]
		});

		sw.Promed.swHepatitisRegistryWindow.superclass.initComponent.apply(this, arguments);
		
		/*
		var patient_dop_tab = this.findById('HRW_SearchFilterTabbar').items.items[1];
		//var patient_dop_tab = this.SearchFilters.items.items[0].items.items[1].items.items[1];
		patient_dop_tab.items.add({
			name: 'HepatitisEpidemicMedHistoryType_id',
			comboSubject: 'HepatitisEpidemicMedHistoryType',
			fieldLabel: lang['epidanamnez'],
			typeCode: 'int',
			xtype: 'swcommonsprcombo',
			width: 250
		});
		patient_dop_tab.items.add({
			name: 'MorbusHepatitis_EpidNum',
			fieldLabel: lang['epidnomer'],
			autoCreate: {tag: "input", size:8, maxLength: "8", autocomplete: "off"},
			maskRe: /[0-9]/,
			xtype: 'textfield',
			width: 120
		});
		*/
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('HepatitisRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('HepatitisRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			/*if (String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по гепатиту»');
				return false;
			}*/
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('HRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('HepatitisRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swHepatitisRegistryWindow.superclass.show.apply(this, arguments);
		
		this.HepatitisRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
            handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});
		
		this.HepatitisRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		var base_form = this.findById('HepatitisRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('HRW_SearchFilterTabbar').setActiveTab(0);
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		var minzdrav = getGlobalOptions().isMinZdrav;
        var lpu_attach_combo = base_form.findField('AttachLpu_id');
        if (String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0&&!minzdrav) {
        	if(getRegionNick() != 'kareliya')
            	lpu_attach_combo.setValue(getGlobalOptions().lpu_id);
            lpu_attach_combo.setDisabled(true);
        } else {
            lpu_attach_combo.setValue(null);
            lpu_attach_combo.setDisabled(false);
        }

		var ARMType = '';
		if(arguments[0].ARMType)
			ARMType = arguments[0].ARMType;
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
		if(ARMType == 'spesexpertllo' || ARMType == 'adminllo')
		{
			this.HepatitisRegistrySearchFrame.setActionHidden('action_add', true);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_delete', true);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_edit', true);
		}
		else {
			this.HepatitisRegistrySearchFrame.setActionHidden('action_add', false);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_delete', false);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_edit', false);
		}
		if(String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0)
		{
			this.HepatitisRegistrySearchFrame.setActionHidden('action_add', true);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_delete', true);
			this.HepatitisRegistrySearchFrame.setActionHidden('action_edit', true);	
			this.HepatitisRegistrySearchFrame.setActionHidden('person_register_dis', true);	
		}
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		//this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.HepatitisRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			readOnly: (this.editType == 'onlyRegister')?true:false,
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.HepatitisRegistrySearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis_registra'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	title: lang['registr_po_virusnomu_gepatitu'],
	width: 800
});