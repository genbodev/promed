/**
* swOnkoRegistryWindow - окно регистра по онкологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @package      MorbusOnko
* @author       Пермяков Александр
* @version      06.2013
* @comment      Префикс для id компонентов ORW (OnkoRegistryWindow)
*
*/
sw.Promed.swOnkoRegistryWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ORW_SearchButton');
	},
	doReset: function() {
		
		var base_form = this.findById('OnkoRegistryFilterForm').getForm();
		base_form.reset();
		this.OnkoRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.OnkoRegistrySearchFrame.ViewActions.person_register_out.setDisabled(true);
		this.OnkoRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.OnkoRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.OnkoRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.OnkoRegistrySearchFrame.removeAll({
			clearAll: true
		});// #138061 неправильное отображение количества записей и счетчика страниц
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('OnkoRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('OnkoRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.OnkoRegistrySearchFrame.getGrid();

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

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (!params.ignorePersonPeriodicType ) ) {
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

		var post = getAllFormFieldValues(this.findById('OnkoRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.OnkoRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	getRecordsCount: function() {
		var st = this.OnkoRegistrySearchFrame.getGrid().getStore();
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
	height: 550,
	openViewWindow: function(action) {
		if (getWnd('swMorbusOnkoWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
			return false;
		}
		
		var grid = this.OnkoRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
			
		if ( Ext.isEmpty(selected_record.get('MorbusOnko_id')) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
			return false;
		}
		
		var params = new Object();
		params.onHide = function(isChange) {
			if(isChange) {
				grid.getStore().reload();
			} else {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
		};
		params.allowSpecificEdit = ('edit' == action);
		params.Person_id = selected_record.data.Person_id;
		params.PersonEvn_id = selected_record.data.PersonEvn_id;
		params.Server_id = selected_record.data.Server_id;
		params.PersonRegister_id = selected_record.data.PersonRegister_id;
		params.EvnOnkoNotifyNeglected_id = selected_record.data.EvnOnkoNotifyNeglected_id;
		params.MorbusOnkoVizitPLDop_id = selected_record.data.MorbusOnkoVizitPLDop_id;
		params.Morbus_id = selected_record.data.Morbus_id;
		params.MorbusOnkoLeave_id = selected_record.data.MorbusOnkoLeave_id;
		params.userMedStaffFact = this.userMedStaffFact;
		params.editType = this.editType;
		params.action = this.OnkoRegistrySearchFrame.getAction('action_edit').isHidden()?'view':'edit';
		params.ARMType = this.ARMType;
		getWnd('swMorbusOnkoWindow').show(params);
	},
	openWindow: function(action) {
			
		var form = this.findById('OnkoRegistryFilterForm').getForm();
		var grid = this.OnkoRegistrySearchFrame.getGrid();
		
		var cur_win = this;
		if (action == 'include') {
			sw.Promed.personRegister.add({
				viewOnly: (cur_win.editType=='onlyRegister')?true:false,
                MorbusType_SysNick: 'onko',
                PersonRegisterType_SysNick: 'onko',
				callback: function(data) {
					form.findField('Person_Firname').setValue(data.Person_Firname);
					form.findField('Person_Secname').setValue(data.Person_Secname);
					form.findField('Person_Surname').setValue(data.Person_Surname);
					form.findField('Person_Birthday').setValue(data.Person_Birthday);
					cur_win.doSearch();
				}
			});
		} else if (action == 'out') {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
			{
				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			sw.Promed.personRegister.out({
                MorbusType_SysNick: 'onko'
				,PersonRegister_id: record.get('PersonRegister_id')
				,Person_id: record.get('Person_id')
				,Diag_Name: record.get('Diag_Name')
				,PersonRegister_setDate: record.get('PersonRegister_setDate')
				,callback: function(data) {
					grid.getStore().reload();
				}
			});
		}
		
	},
	printHtml: function(prms)
	{
		Ext.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			params: prms,
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result.success && result.html){
						var id_salt = Math.random();
						var win_id = 'printEvent' + Math.floor(id_salt*10000);
						var win = window.open('', win_id);
						win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ result.html +'</body></html>');
						var i, el;
						// нужно показать скрытые области для печати
						var printonly_list = Ext.query("div[class=printonly]",win.document);
						for(i=0; i < printonly_list.length; i++)
						{
							el = new Ext.Element(printonly_list[i]);
							el.setStyle({display: 'block'});
						}
						// нужно скрыть элементы управления
						var tb_list = Ext.query("*[class*=section-toolbar]",win.document);
						tb_list = tb_list.concat(Ext.query("*[class*=sectionlist-toolbar]",win.document));
						tb_list = tb_list.concat(Ext.query("*[class*=item-toolbar]",win.document));
						//tb_list = tb_list.concat(Ext.query("*[class=section-button]",win.document));
						//log(tb_list);
						for(i=0; i < tb_list.length; i++)
						{
							el = new Ext.Element(tb_list[i]);
							el.setStyle({display: 'none'});
						}
						win.document.close();
					} else {
						Ext.Msg.alert(lang['soobschenie'], 'Ошибка при получении формы для печати');
 						return false;
					}
				}
			}
		});
	},
	initComponent: function() {
		var win = this;
		this.selected_record = '';
		this.OnkoRegistrySearchFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'ORW_OnkoRegistrySearchGrid',
			object: 'OnkoRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusOnko_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},	
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150, id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150},
				{name: 'OnkoDiag_Name', type: 'string', header: lang['gistologiya_opuholi'], width: 250},
				{name: 'MorbusOnko_IsMainTumor', type: 'string', header: lang['priznak_osnovnoy_opuholi'], width: 150},
				{name: 'TumorStage_Name', type: 'string', header: lang['stadiya'], width: 60},
                {name: 'MorbusOnko_setDiagDT', type: 'date', format: 'd.m.Y', header: lang['data_ustanovleniya_diagnoza'], width: 150},
                {name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
                {name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 150},
                {name: 'MedPersonal_Fio', type: 'string', header: 'Пользователь (врач), включивший в регистр', width: 200},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'EvnOnkoNotifyNeglected_id', type: 'int', hidden: true},
				{name: 'MorbusOnkoVizitPLDop_id', type: 'int', hidden: true},
				{name: 'MorbusOnkoLeave_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
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
				this.getAction('person_register_out').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) || !isUserGroup('OnkoRegistryFullAccess') );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) );
				this.getAction('action_print').menu.print030GRR.setDisabled(!record.data.Morbus_id);
				this.getAction('action_print').menu.print0271U.setDisabled(!record.data.MorbusOnkoLeave_id);
				this.getAction('action_print').menu.print0272U.setDisabled(!record.data.EvnOnkoNotifyNeglected_id);
				this.getAction('action_print').menu.print0306TD.setDisabled(!record.data.MorbusOnkoVizitPLDop_id);
				this.getAction('action_print').menu.print0306U.setDisabled(!record.data.Morbus_id);
			},
			onDblClick: function(x,c,v) {
				if (!this.ViewActions.action_edit.isDisabled()) {
					win.openViewWindow('edit')
				}else{
					win.openViewWindow('view')
				}
			},
			actions: [
				{name: 'action_add', handler: function() { this.openWindow('include'); }.createDelegate(this)},
				{name: 'action_edit', handler: function() { this.openViewWindow('edit'); }.createDelegate(this)},
				{name: 'action_view', handler: function() { this.openViewWindow('view'); }.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)  },
				{name: 'action_refresh'},
				{name: 'action_print',
					menuConfig: {
						//printObjectList: {hidden: true, name: 'printCost', text: lang['spravka_o_stoimosti_lecheniya'], handler: function () { that.printCost() }},
						//printObjectListFull: {name:'printObjectSpr', text: lang['spravka_o_facticheskoy_sebestoimosti'], hidden: true},
						//printObjectListSelected: {hidden: true},
						print030GRR: {
							text: 'Печать в формате «№ 030-ГРР»',
							iconCls: 'print16',
							hidden: (getRegionNick() == 'kz'),
							handler: function () {
								var selected_record = win.OnkoRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
								printBirt({
									'Report_FileName': 'f030grr.rptdesign',
									'Report_Params': '&paramMorbus=' + selected_record.data.Morbus_id,
									'Report_Format': 'pdf'
								});
							}.createDelegate(this)
						},
						print0271U: {
							text: 'Печать в формате «№ 027-1/У»',
							iconCls: 'print16',
							sectionCode: 'MorbusOnkoLeave',
							hidden: (getRegionNick() == 'kz'),
							handler: function() {
								var selected_record = win.OnkoRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
								win.printHtml({
									archiveRecord: 0,
									object:	'MorbusOnkoLeave',
									object_id: 'MorbusOnkoLeave_id',
									object_value: selected_record.data.MorbusOnkoLeave_id
								});
							}
						},
						print0272U: {
							text: 'Печать в формате «№ 027-2/У»',
							iconCls: 'print16',
							hidden: (getRegionNick() == 'kz'),
							handler: function() {
								var selected_record = win.OnkoRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
								printBirt({
									'Report_FileName': 'OnkoNotifyNeglected.rptdesign',
									'Report_Params': '&paramEvnOnkoNotifyNeglected=' + selected_record.data.EvnOnkoNotifyNeglected_id,
									'Report_Format': 'pdf'
								});
							}
						},
						print0306TD: {
							text: 'Печать в формате «№ 030-6/ТД»',
							iconCls: 'print16',
							sectionCode: 'MorbusOnkoVizitPLDop',
							hidden: (getRegionNick() == 'kz'),
							handler: function() {
								var selected_record = win.OnkoRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
								win.printHtml({
									archiveRecord: 0,
									object:	'MorbusOnkoVizitPLDop',
									object_id: 'MorbusOnkoVizitPLDop_id',
									object_value: selected_record.data.MorbusOnkoVizitPLDop_id
								});
							}
						},
						print0306U: {
							text: 'Печать в формате «№ 030-6/У',
							iconCls: 'print16',
							//hidden: getRegionNick().inlist([ 'kz' ]),
							handler: function() {
								var selected_record = win.OnkoRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
								printBirt({
									'Report_FileName': 'f030_6u_onko.rptdesign',
									'Report_Params': '&paramMorbus=' + selected_record.get('Morbus_id'),
									'Report_Format': 'pdf'
								});
							}
						}
					}
				}
			]
		});
		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_ORW + 120,
				id: 'ORW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					var base_form = this.findById('OnkoRegistryFilterForm').getForm();
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
				tabIndex: TABINDEX_ORW + 122,
				text: lang['pechat_spiska']
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORW + 123,
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
					this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ORW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('OnkoRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
                isDisplayPersonRegisterRecordTypeField: true,
				allowPersonPeriodicSelect: true,
				id: 'OnkoRegistryFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'OnkoRegistry',
				tabIndexBase: TABINDEX_ORW,
				tabPanelHeight: 225,
				tabPanelId: 'ORW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function() {
							this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_registr'],
					items: [{
						border: false,
						layout: 'column',
						labelWidth: 220,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								xtype: 'swpersonregistertypecombo',
								hiddenName: 'PersonRegisterType_id',
								width: 180
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 170,
							items: [{
								fieldLabel: lang['data_gospitalizatsii'],
								name: 'PersonRegister_evnSection_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						labelWidth: 220,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_vklyucheniya_v_registr'],
								name: 'PersonRegister_setDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 180,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 170,
							hidden: !(getRegionNick()=='krym'),
							items: [{
								fieldLabel: lang['data_smerti'],
								name: 'PersonRegister_onkoDeathDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						labelWidth: 220,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_isklyucheniya_iz_registra'],
								name: 'PersonRegister_disDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 180,
								xtype: 'daterangefield'
								/*}, {
								fieldLabel: lang['zapisi_registra'],
								hiddenName: 'PersonRegisterRecordType_id',
								valueField: 'PersonRegisterRecordType_id',
								displayField: 'PersonRegisterRecordType_Name',
								storeKey: 'PersonRegisterRecordType_id',
								comboData: [
									[1,lang['vse']],
									[2,lang['vse_sostoyaschie_na_uchete']],
									[3,lang['vse_vyiehavshie']],
									[4,lang['vse_u_kotoryih_diagnoz_ne_podtverdilsya']],
									[5,'все, «снятые по базалиоме»'],
									[6,lang['vse_umershie']]
								],
								comboFields: [
									{name: 'PersonRegisterRecordType_id', type:'int'},
									{name: 'PersonRegisterRecordType_Name', type:'string'}
								],
								width: 200,
								xtype: 'swstoreinconfigcombo'*/
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 170,
							hidden: !(getRegionNick()=='krym'),
							items: [{
								fieldLabel: lang['prichina_smerti'],
								hiddenName: 'PersonRegister_onkoDiagDeath',
								listWidth: 620,
								valueField: 'Diag_Code',
								MorbusType_SysNick: 'onko',
								width: 170,
								xtype: 'swdiagcombo'
							}]
						}]
					}]
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
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',									
							items: [{
								fieldLabel: lang['diagnoz_s'],
								hiddenName: 'Diag_Code_From',
								listWidth: 620,
								valueField: 'Diag_Code',
                                MorbusType_SysNick: 'onko',
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
                                MorbusType_SysNick: 'onko',
								width: 290,
								xtype: 'swdiagcombo'
							}]
						}]
					}, {
						fieldLabel: lang['data_ustanovleniya_diagnoza'],
						name: 'MorbusOnko_setDiagDT_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['osnovnaya_opuhol'],
						hiddenName: 'MorbusOnko_IsMainTumor',
                        comboSubject: 'YesNo',
                        xtype:'swcommonsprlikecombo'
					}, {
						fieldLabel:lang['gistologiya_opuholi'],
						hiddenName:'Diag_mid',
                        xtype:'swonkodiagcombo',
						width: 350
					}, {
						fieldLabel:lang['stadiya_opuholevogo_protsessa'],
						hiddenName:'TumorStage_id',
						xtype:'swtumorstagenewcombo',
						loadParams:  getRegionNumber().inlist([101]) ? {mode: 1} : {mode: 2} // region_id is null, kz свои
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					labelWidth: 180,
					listeners: {
						'activate': function(panel) {
							this.getFilterForm().getForm().findField('MorbusOnkoSpecTreat_begDate_Range').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['8_spets_lechenie'],
					items: [{
						fieldLabel: lang['data_nachala_lecheniya'],
						name: 'MorbusOnkoSpecTreat_begDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['data_okonchaniya_lecheniya'],
						name: 'MorbusOnkoSpecTreat_endDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						comboSubject: 'TumorPrimaryTreatType',
						fieldLabel: lang['provedennoe_lechenie_pervichnoy_opuholi'],
						hiddenName: 'TumorPrimaryTreatType_id',
						width: 350,
						xtype: 'swcommonsprlikecombo'
					}, {
						comboSubject: 'TumorRadicalTreatIncomplType',
						fieldLabel: lang['prichinyi_nezavershennosti_radikalnogo_lecheniya'],
						hiddenName: 'TumorRadicalTreatIncomplType_id',
						width: 350,
						xtype: 'swcommonsprlikecombo'
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					labelWidth: 220,
					listeners: {
						'activate': function(panel) {
							this.getFilterForm().getForm().findField('OnkoTumorStatusType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['9_kontrol_sostoyaniya'],
					items: [{
						comboSubject: 'OnkoTumorStatusType',
						fieldLabel: lang['sostoyanie_opuholevogo_protsessa'],
						hiddenName: 'OnkoTumorStatusType_id',
						width: 350,
						xtype: 'swcommonsprlikecombo'
					}, {
						comboSubject: 'OnkoPersonStateType',
						fieldLabel: lang['obschee_sostoyanie_patsienta'],
						hiddenName: 'OnkoPersonStateType_id',
						width: 350,
						xtype: 'swcommonsprlikecombo'
					}, {
						comboSubject: 'OnkoStatusYearEndType',
						fieldLabel: lang['klinicheskaya_gruppa'],
						hiddenName: 'OnkoStatusYearEndType_id',
						width: 350,
						xtype: 'swcommonsprlikecombo'
					}]
				}]
			}),
			this.OnkoRegistrySearchFrame]
		});
		
		sw.Promed.swOnkoRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin')){
				if (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
				{
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по онкологии»');
					return false;
				}
			}
		},
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('OnkoRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('OnkoRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ORW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('OnkoRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swOnkoRegistryWindow.superclass.show.apply(this, arguments);

		this.OnkoRegistrySearchFrame.addActions({
			name:'person_register_out', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'delete16',
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
            handler: function() {
				this.openWindow('out');
			}.createDelegate(this)
		});
		
		this.OnkoRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
            hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
			handler: function() {
				this.emkOpen(!isUserGroup('OnkoRegistryFullAccess'));
			}.createDelegate(this)
		});
		
		var base_form = this.findById('OnkoRegistryFilterForm').getForm();
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('ORW_SearchFilterTabbar').setActiveTab(0);
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
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
		
		if(getRegionNick() != 'kareliya')
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		
		if ( String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}

		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
		{
			base_form.findField('AttachLpu_id').setValue(null);	
			base_form.findField('AttachLpu_id').setDisabled(false);
		}

		this.ARMType = null;
		if(arguments[0].ARMType){
			this.ARMType = arguments[0].ARMType;
		}
		//if((this.ARMType == 'spesexpertllo' || this.ARMType == 'adminllo') || (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0))
		if(isUserGroup('OnkoRegistryFullAccess'))
		{
			this.OnkoRegistrySearchFrame.setActionHidden('action_add', false);
			this.OnkoRegistrySearchFrame.setActionHidden('action_delete', false);
			this.OnkoRegistrySearchFrame.setActionHidden('action_edit', false);
		}
		else {
			this.OnkoRegistrySearchFrame.setActionHidden('action_add', true);
			this.OnkoRegistrySearchFrame.setActionHidden('action_delete', true);
			this.OnkoRegistrySearchFrame.setActionHidden('action_edit', true);
		}
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
		//this.doSearch({firstLoad: true});
	},
	emkOpen: function(readOnly)
	{
		var grid = this.OnkoRegistrySearchFrame.getGrid();

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
			readOnly: readOnly || (this.editType == 'onlyRegister')?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.OnkoRegistrySearchFrame.getGrid();

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
	title: lang['registr_po_onkologii'],
	width: 800
});