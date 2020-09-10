/**
* swNarkoRegistryWindow - Регистр по наркологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A.Markoff <markov@swan.perm.ru> & Alexander Permyakov
* @version      2012/10
* @comment      Префикс для id компонентов NRZRW (NarkoRegistryWindow)
*
*/
sw.Promed.swNarkoRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('NRZRW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('NarkoRegistryFilterForm').getForm();
		base_form.reset();
		this.NarkoRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.NarkoRegistrySearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.NarkoRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.NarkoRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.NarkoRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.NarkoRegistrySearchFrame.getGrid().getStore().removeAll();
		this.NarkoRegistrySearchFrame.getGrid().getViewFrame().removeAll();// #138061 неправильное отображение количества записей и счетчика страниц

		var el;
		el = base_form.findField('PersonRegisterType_id');// Регистр - Тип записи регистра - Все
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PrivilegeStateType_id');// Льгота - Актуальность льготы - 1. Действующие льготы
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PersonCardStateType_id');// Прикрепление - Актуальность прикр-я - 1. Актуальные прикрепления
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('AddressStateType_id');// Адрес - Тип адреса - 1. Адрес регистрации
		if(typeof(el) !== 'undefined') el.setValue(1);

		var lpu_reg_combo = base_form.findField('RegLpu_id');

		if (isUserGroup("NarkoMORegistry")) {
			lpu_reg_combo.setValue(getGlobalOptions().lpu_id);
			lpu_reg_combo.setDisabled(true);
			base_form.findField('LpuAttachType_id').setValue(1);
			base_form.findField('LpuAttachType_id').setDisabled(true);
		}

		if (isUserGroup("NarkoRegistry")) {
			lpu_reg_combo.setValue(null);
			lpu_reg_combo.setDisabled(false);
			base_form.findField('LpuAttachType_id').setValue(null);
			base_form.findField('LpuAttachType_id').setDisabled(false);
		}
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('NarkoRegistryFilterForm').getForm();
		
		/*if ( !params.firstLoad && this.findById('NarkoRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}*/
		
		var grid = this.NarkoRegistrySearchFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('NarkoRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		post.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		post.LpuAttachType_id=(isUserGroup("NarkoRegistry"))?base_form.findField('LpuAttachType_id').getValue():1;
		//log(post);

		if ( base_form.isValid() ) {
			this.NarkoRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					var arr = [];
					var RegLpu='';
					for(var rec in records){
						if(records[rec].data){
							var inArr = false
							 for(var i = 0; i < arr.length; i++) 
								{
									if(arr[i] == records[rec].get('Lpu_iid')) inArr = true;
								}
							if(!inArr){
								arr.push(records[rec].get('Lpu_iid'));
								RegLpu+=','+records[rec].get('Lpu_iid');
							}
							
						}
					}
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
    getRecordsCount: function() {
		var st = this.NarkoRegistrySearchFrame.getGrid().getStore();
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
        post.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
        post.LpuAttachType_id=(isUserGroup("NarkoRegistry"))?base_form.findField('LpuAttachType_id').getValue():1;

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
	height: 550,
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['registry_export','person_register_dis','add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.NarkoRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
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
					PersonRegister_id: selected_record.get('PersonRegister_id')
					,MorbusType_SysNick: 'narc'
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
					searchMode:(isUserGroup("NarkoRegistry"))?null:'all',
					viewOnly: (cur_win.editType=='onlyRegister')?true:false,
                    MorbusType_SysNick: 'narc' // наркология
                    //,registryType: 'NarkoRegistry'
					,callback: function(data) {
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
				if (getWnd('swMorbusCrazyWindow').isVisible()) {
					getWnd('swMorbusCrazyWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
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
				params.type = 'narko';
				params.PersonRegister_setDate = selected_record.data.PersonRegister_setDate
				params.MedPersonal_did = selected_record.data.MedPersonal_iid
				params.Lpu_did = selected_record.data.Lpu_iid
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				params.editType = cur_win.editType;
				params.action = cur_win.NarkoRegistrySearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				getWnd('swMorbusCrazyWindow').show(params);
				break;
			
			case 'registry_export':
				if ( Ext.isEmpty(selected_record.get('Morbus_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
					return false;
				}
				params.callback = Ext.emptyFn;
				params.PersonRegister_setDate = selected_record.data.PersonRegister_setDate
				params.MedPersonal_did = selected_record.data.MedPersonal_iid
				params.Lpu_did = selected_record.data.Lpu_iid
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.Person_id = selected_record.data.Person_id;
				getWnd('swRegistryExportWindow').show(params);
				break;				
		}
		

		
	},
	initComponent: function() {
		
		this.NarkoRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() {this.openWindow('add');}.createDelegate(this)},
                {name: 'action_edit', handler: function() {this.openWindow('edit');}.createDelegate(this)},
                {name: 'action_view', handler: function() {this.openWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'NRZRW_NarkoRegistrySearchGrid',
			object: 'CrazyRegistry',
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
				{name: 'MorbusCrazy_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu2_Nick', type: 'string', header: lang['mo_vklyuchivshee_v_registr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150}
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
				this.getAction('person_register_dis').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false /*||!(record.get("Lpu2_Nick")==getGlobalOptions().lpu_nick)*/);
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id'))||!(record.get("Lpu2_Nick")==getGlobalOptions().lpu_nick) );
				if ('kareliya' == getRegionNick()) {
					// временно #69630
					this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
				} else {
					this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('Morbus_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false||!(record.get("Lpu2_Nick")==getGlobalOptions().lpu_nick) );
				}
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('Morbus_id')) );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_view').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'NarkoRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'NarkoRegistry',
			tabIndexBase: TABINDEX_CRZRW,
			tabPanelHeight: 215,
			tabPanelId: 'NRZRW_SearchFilterTabbar',
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
					width: 180,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				},
				new sw.Promed.SwLpuSearchCombo({
					fieldLabel:lang['mo_vklyuchivshee_v_registr'],
					hiddenName: 'RegLpu_id',
					listWidth: 400,
					tabIndex: this.tabIndexBase + 33,
					width: 310
				})
				]
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 180,
				listeners: {
					'activate': function(panel) {
						this.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['7_diagnozyi'],
				items: [{
					fieldLabel: lang['diagnoz_s'],
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
                    MorbusType_SysNick: 'narc',
					//registryType:'NarkoRegistry',
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['po'],
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
                    MorbusType_SysNick: 'narc',
					//registryType:'NarkoRegistry',
					xtype: 'swdiagcombo'
				/*
				}, {
					fieldLabel: lang['lpu_v_kotoroy_patsientu_vpervyie_ustanovlen_diagnoz_orfannogo_zabolevaniya'],
					hiddenName: 'Lpu_sid',//Lpu_oid 
					listWidth: 620,
					width: 350,
					xtype: 'swlpucombo'
					*/
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_CRZRW + 120,
				id: 'NRZRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
					this.doSearch({firstLoad: true});
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_CRZRW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
                    this.NarkoRegistrySearchFrame.printRecords();
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_CRZRW + 122,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_CRZRW + 123,
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
					this.findById('NRZRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('NRZRW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_CRZRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('NarkoRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ this.SearchFilters, this.NarkoRegistrySearchFrame]
		});

		sw.Promed.swNarkoRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('NarkoRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('NarkoRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('NRZRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('NarkoRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swNarkoRegistryWindow.superclass.show.apply(this, arguments);
		var win = this;
		
		this.NarkoRegistrySearchFrame.addActions({
			name:'action_export', 
			text:lang['vyigruzka_v_federalnyiy_registr'], 
			tooltip: lang['vyigruzka_v_federalnyiy_registr'],
			iconCls: '',
			hidden: true,
			disabled: true,
			handler: function() {
				this.openWindow('registry_export');
			}.createDelegate(this)
		});
		
		this.NarkoRegistrySearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			handler: function() {
				this.openWindow('person_register_dis');
			}.createDelegate(this)
		});
		
		this.NarkoRegistrySearchFrame.addActions({
			name:'revise_list', 
			text:lang['sverka_dannyih'],
			hidden:(getGlobalOptions().region.nick != 'khak'),
			tooltip: lang['sverka_dannyih'],
			iconCls: 'pers-curehist16',
			handler: function() {
				getWnd('swNarcoReviseListWindow').show();
			}.createDelegate(this)
		});
		
		this.NarkoRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});

		if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin') && !(arguments[0] && arguments[0].fromARM && arguments[0].fromARM !== null && arguments[0].fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])))
		{
			if (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0)
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по наркологии»');
				win.hide();
				return false;
			}
		}
		
		var base_form = this.findById('NarkoRegistryFilterForm').getForm();
		var Diag_Code_From = base_form.findField('Diag_Code_From');
		var Diag_Code_To = base_form.findField('Diag_Code_To');
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('NRZRW_SearchFilterTabbar').setActiveTab(0);
		
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
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
		if(String(getGlobalOptions().groups).indexOf('Narko', 0) < 0){
			this.NarkoRegistrySearchFrame.setActionHidden('action_add', true);
			this.NarkoRegistrySearchFrame.setActionHidden('action_delete', true);
			this.NarkoRegistrySearchFrame.setActionHidden('action_edit', true);	
			this.NarkoRegistrySearchFrame.setActionHidden('person_register_dis', true);		
		}

		var lpu_reg_combo = base_form.findField('RegLpu_id');

		if (isUserGroup("NarkoMORegistry")) {
			lpu_reg_combo.setValue(getGlobalOptions().lpu_id);
			lpu_reg_combo.setDisabled(true);
			base_form.findField('LpuAttachType_id').setValue(1);
			base_form.findField('LpuAttachType_id').setDisabled(true);
		}

		if (isUserGroup("NarkoRegistry")) {
			lpu_reg_combo.setValue(null);
			lpu_reg_combo.setDisabled(false);
			base_form.findField('LpuAttachType_id').setValue(null);
			base_form.findField('LpuAttachType_id').setDisabled(false);
		}
		
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function()
	{
		var grid = this.NarkoRegistrySearchFrame.getGrid();

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
			readOnly: (this.editType == 'onlyRegister')?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.NarkoRegistrySearchFrame.getGrid();
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
	title: lang['registr_po_narkologii'],
	width: 800
});
