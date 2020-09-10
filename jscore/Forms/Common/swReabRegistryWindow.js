/**
 * swReabRegistryWindow - окно регистра Реабилитации
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @package      MorbusReab
 * @author       Артамонов И.Г.
 * @version      01.2017
 * @comment      Префикс для id компонентов ORW (ReabRegistryWindow)
 *
 */
sw.Promed.swReabRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['registr_reability'],
	width: 800,
	codeRefresh: true,
	objectName: 'swReabRegistryWindow',
	id: 'swReabRegistryWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 550,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	getButtonSearch: function () {
		//return Ext.getCmp('ORW_SearchButton');
		return Ext.getCmp('Reab_SearchButton');
	},

	doReset: function () {

		var base_form = this.findById('ReabRegistryFilterForm').getForm();
		base_form.reset();
		this.ReabRegistrySearchFrame.ViewActions.open_emk.setDisabled(true); //электронная карта
		//this.ReabRegistrySearchFrame.ViewActions.person_register_out.setDisabled(true); // удаление
		this.ReabRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.ReabRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.ReabRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true); // Обновление
		//this.ReabRegistrySearchFrame.ViewActions.ReabObjectButton.setDisabled(true); // Профиль наблюдения
		this.ReabRegistrySearchFrame.ViewActions.action_edit.setDisabled(true); // Переход на форму редактирования

		this.ReabRegistrySearchFrame.getGrid().getStore().removeAll(); // Обнуление GRIDa

	},

	doSearch: function (params) {

		//console.log('Ищем='); 
		if (typeof params != 'object') {
			params = {};
			// console.log('params=',params); 
		}

		var base_form = this.findById('ReabRegistryFilterForm').getForm();

		if (!params.firstLoad && this.findById('ReabRegistryFilterForm').isEmpty()) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function () {
			});
			return false;
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		/*
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
		 */

		//Загрузка GRIDa
		var grid = this.ReabRegistrySearchFrame.getGrid();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();
		var post = getAllFormFieldValues(this.findById('ReabRegistryFilterForm'));
		post.limit = 100;
		post.start = 0;
		post.Reabquest_yn = this.findById('Reabquest_yn').getValue() ? 'on' : ''; //значение чекбокса Анкеты
		post.ReabScale_yn = this.findById('ReabScale_yn').getValue() ? 'on' : ''; //значение чекбокса "Шкалы"

		if (base_form.isValid()) {
			//this.ReabRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			//  this.ReabRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}

	},

	getRecordsCount: function () {
		var base_form = this.getFilterForm().getForm();

		if (!base_form.isValid()) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if (post.PersonPeriodicType_id == null) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Records_Count != undefined) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					} else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},

	//Страничная форма
	openPersonWindow: function (action) {
		var grid = this.ReabRegistrySearchFrame.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		// Ext.getCmp('ReabRegistry').ViewActions.action_edit.setDisabled(true); // 
		var params =
		{
			Person_id: rec.data.Person_id,
			Server_id: rec.data.Server_id,
			ARMType: this.userMedStaffFact.ARMType
		}
		//alert('Выход на новое окно - страничная форма');
		getWnd('ufa_personReabRegistryWindow').show(params);
	},

	openViewWindow: function (action) {

		if (action == 'edit')
		{
			alert('Переход на форму редактирования?????');
		} else {
			alert('Переход на форму просмотра?????');
		}


//		if (getWnd('swMorbusOnkoWindow').isVisible()) {
//			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
//			return false;
//		}
////		
//		var grid = this.ReabRegistrySearchFrame.getGrid();
//		if (!grid.getSelectionModel().getSelected()) {
//			return false;
//		}
//		var selected_record = grid.getSelectionModel().getSelected();
//			
//		if ( Ext.isEmpty(selected_record.get('MorbusOnko_id')) ) {
//			sw.swMsg.alert(lang['soobschenie'], lang['zabolevanie_na_cheloveka_ne_zavedeno']);
//			return false;
//		}
//		
//		var params = new Object();
//		params.onHide = function(isChange) {
//			if(isChange) {
//				grid.getStore().reload();
//			} else {
//				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
//			}
//		};
//		params.allowSpecificEdit = ('edit' == action);
//		params.Person_id = selected_record.data.Person_id;
//		params.PersonEvn_id = selected_record.data.PersonEvn_id;
//		params.Server_id = selected_record.data.Server_id;
//		params.PersonRegister_id = selected_record.data.PersonRegister_id;
//		params.userMedStaffFact = this.userMedStaffFact;
//		getWnd('swMorbusOnkoWindow').show(params);
	},

	// Постановка пациента на учет 
	ReabRegistrIns: function (paramIn) {

		//console.log('paramIn = ',paramIn);
		//console.log('PersonReab = ',Ext.getCmp('swReabRegistryWindow').PersonReab);
		//Запись в регистры с контролем ошибок
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		Ext.Ajax.request({
			url: '?c=Ufa_Reab_Register_User&m=saveInReabRegister',
			params: {
				Person_id: Ext.getCmp('swReabRegistryWindow').PersonReab,
				DirectType_id: paramIn.DirectType_id,
				StageType_id: paramIn.StageId,
				ReabEvent_setDate: paramIn.DateIn,
				MedPersonal_iid: getGlobalOptions().medpersonal_id,
				Lpu_iid: getGlobalOptions().lpu_id

			},
			callback: function (options, success, response)
			{
				// console.log('Поперло сохранение');
				loadMask.hide();
				if (success == true)
				{ // Проверяем далее
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success == true)
					{

						//alert('Сохранение произведено - запуск GRIDa');
						Ext.getCmp('ReabRegistry').getGrid().getStore().load({
							params: {
								Person_id: Ext.getCmp('swReabRegistryWindow').PersonReab,
								SearchFormType: 'ReabRegistry'
							}
						});
					}

				} else {
					sw.swMsg.alert(lang['soobschenie'], ['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}

//
//                     var response_obj = Ext.util.JSON.decode(response.responseText);
//                      console.log('response_obj=',response_obj);
//                     console.log('response_obj.success=',response_obj.success);
//
//
//                     if(response.responseText == "" && success == true)
//                     {
//                        alert('переход на GRID');
//                     }

				//console.log(response.responseText);
				//var rrr = Ext.util.JSON.decode(response.responseText);
				//console.log(rrr);
//                  if(success == true && response.responseText == 'true')
//                  {
//                      //Запускаем окно выбора профиля
//                     getWnd('swReabSelectWindow').show(person_data);
//                  }else
//                  {
//                      //Какое-то сообщение о косяке
//                       alert('55555');
//                  }

			}
		})

	},

	//Формирование даты включения и этапа реабилитации
	ReabDateStage: function (person_data) {
		//Поиск пациента в регистре
		Ext.Ajax.request({
			url: '?c=Ufa_Reab_Register_User&m=SeekRegistr',
			params: {
				Person_id: person_data.Person_id
			},
			callback: function (options, success, response)
			{

				if (success == true)
				{ // Проверяем далее
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success == true)
					{
						//Запускаем окно выбора профиля
						person_data.inp = 0; // Признак, что осуществляется включение в регистр
						getWnd('swReabSelectWindow').show(person_data);
					} else
					{
						Ext.getCmp('ReabRegistry').getGrid().getStore().load({
							params: {
								Person_id: person_data.Person_id, //  Ext.getCmp('FormPanel').form.items.items[2].value,
								Person_Firname: person_data.PersonFirName_FirName, //    Ext.getCmp('FormPanel').form.items.items[5].value,
								Person_Secname: person_data.PersonSecName_SecName, //Ext.getCmp('FormPanel').form.items.items[6].value,
								Person_Surname: person_data.PersonSurName_SurName, //getCmp('FormPanel').form.items.items[7].value,
								Person_Birthday: person_data.PersonBirthDay_BirthDay.format("d.m.Y"), //Ext.getCmp('FormPanel').form.items.items[8].value,
								SearchFormType: 'ReabRegistry'
							}
						});
					}
				} else {
					sw.swMsg.alert(lang['soobschenie'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}

			}
		})
	},

	openWindow: function (action) {

//		var form = this.findById('ReabRegistryFilterForm').getForm();
//		var cur_win = this;
		if (action == 'include')
		{
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				Ext.Msg.alert(lang['registr_reability'], 'Окно выбора пациента уже открыто');
			} else {
				getWnd('swPersonSearchWindow').show({
					onSelect: function (person_data) {
						var params = new Object();
						this.hide();
//                                this.close();
						params.person_id = person_data.Person_id;
						//alert(params.person_id);
						//console.log('person_data = ',person_data);
						Ext.getCmp('swReabRegistryWindow').PersonReab = person_data.Person_id;
						Ext.getCmp('swReabRegistryWindow').doReset(); // обнуление поисковика и GRIDa
						Ext.getCmp('swReabRegistryWindow').ReabDateStage(person_data);
					}
				})
			}
		}
//                else if (action == 'out')
//                {
//			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
//			{
//				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
//				return false;
//			}
//			var record = grid.getSelectionModel().getSelected();
//			sw.Promed.personRegister.out({
//				PersonRegister_id: record.get('PersonRegister_id')
//				,Person_id: record.get('Person_id')
//				,Diag_Name: record.get('Diag_Name')
//				,PersonRegister_setDate: record.get('PersonRegister_setDate')
//				,callback: function(data) {
//					grid.getStore().reload();
//				}
//			});
//		}

	},
	initComponent: function () {
		var win = this;

		this.ReabRegistrySearchFrame = new sw.Promed.ViewFrame(
				{
					actions: [
						{name: 'action_add', handler: function () {
							this.openWindow('include');
						}.createDelegate(this)},
						//  {name:'action_view',  handler: function() { this.openViewWindow('view'); }.createDelegate(this)},
						{name: 'action_view', hidden: true},
						{name: 'action_delete', hidden: true, text: 'Удалить', disabled: true, },
						{name: 'action_edit', handler: function () {
							this.openPersonWindow('edit');
						}.createDelegate(this)}, // Выход на списочную форму

						//{name: 'action_delete',  hidden: true, handler: this.deletePersonRegister.createDelegate(this)  },

						{name: 'action_refresh'},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
					id: 'ReabRegistry',
					object: 'ReabRegistry',
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields: [
						//{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
						{name: 'Person_id', type: 'int', hidden: true, header: 'ID', key: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'PersonEvn_id', type: 'int', hidden: true},
						{name: 'Lpu_iid', type: 'int', hidden: true},
						{name: 'MedPersonal_iid', type: 'int', hidden: true},
						{name: 'MorbusType_id', type: 'int', hidden: true},
						//{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
						//{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
						//{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: lang['prichina_isklyucheniya_iz_registra'], width: 190},

						{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 250}, //1
						{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 250},
						{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 250},
						{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
						{name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: lang['data_smerti'], width: 90},
						{name: 'Lpu_Nick', type: 'string', header: lang['lpu_prikr'], width: 150},
						//{name: 'Diag_id', type: 'int', hidden: true},
						//{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, hidden: true},
						//{name: 'OnkoDiag_Name', type: 'string', header: lang['gistologiya_opuholi'], width: 250, hidden: true},
						//{name: 'MorbusOnko_IsMainTumor', type: 'string', header: lang['priznak_osnovnoy_opuholi'], width: 150, hidden: true},
						//{name: 'TumorStage_Name', type: 'string', header: lang['stadiya'], width: 60,hidden: true},
						// {name: 'MorbusOnko_setDiagDT', type: 'date', format: 'd.m.Y', header: lang['data_ustanovleniya_diagnoza'], width: 150, hidden: true},
						// {name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y',  header: lang['data_vklyucheniya_v_registr'], width: 150},
						// {name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', hidden: true, header: lang['data_isklyucheniya_iz_registra'], width: 150},
						//{name: 'PMUser_Name', type: 'string', header: 'Кем создана анкета', hidden: true},
						{name: 'Diag', type: 'int', id: 'autoexpand', header: ''}
					],
					toolbar: true,
					totalProperty: 'totalCount',
					focusOnFirstLoad: false,

					onBeforeLoadData: function () {
						this.getButtonSearch().disable();
					}.createDelegate(this),

					onLoadData: function () {
						this.getButtonSearch().enable();
					}.createDelegate(this),

					onRowSelect: function (sm, index, record) {
						//alert('Выбор записи');
						//console.log('getAction=', this.getAction('open_emk'));
						//console.log('index=', index);
						//console.log('sm',sm.getSelected(),'index',index,'record',record);
						//this.getAction('open_emk').setDisabled( false );
//				this.getAction('ReabObjectButton').setDisabled( record.get('PersonRegister_id') == null );
//                                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
					}
				});

		//Событие выбор записи
		var ReabStore = this.ReabRegistrySearchFrame.getGrid().on(
				'rowclick',
				function () {
					//Здесь и начнем - есть запись-есть активность
					Ext.getCmp('ReabRegistry').ViewActions.open_emk.setDisabled(false);
					//Ext.getCmp('ReabRegistry').ViewActions.person_register_out.setDisabled(false); // удаление
					Ext.getCmp('ReabRegistry').ViewActions.action_view.setDisabled(false); // Просмотр чего-то
					//Ext.getCmp('ReabRegistry').ViewActions.ReabObjectButton.setDisabled(false);
					Ext.getCmp('ReabRegistry').ViewActions.action_edit.setDisabled(false); // Переход на форму редактирования
				}
		);

		//Метод LOAD для GRIDa
		var ReabStore = this.ReabRegistrySearchFrame.getGrid().getStore().on(
				'load',
				function () {
					var unicRecs = [];
					var Person_ids = [];
					var recs = this.data.items;
					//console.log('RECS=', recs);

//                for(var k in recs){
//                    if(typeof recs[k] == 'object')
//                    {
//                          if(recs[k].data.Person_id){
//                              if(Ext.getCmp('swReabRegistryWindow').inArray(recs[k].get('Person_id'), Person_ids) === false){
//                                   Person_ids.push(recs[k].data.Person_id);
//                                   unicRecs.push(recs[k]);
//                              }
//                          }
//                    }
//                }
//                this.removeAll();
//
//
//                for(var k in unicRecs){
//                   if(typeof unicRecs[k] == 'object'){
//                      this.add(unicRecs[k]);
//                   }
//                }

					//Ext.getCmp('BskRegistry').getGrid().bbar.dom.innerText = '';

				}
		);

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'Reab_SearchButton',
					text: BTN_FRMSEARCH
				},
				{
					handler: function () {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 121,
					text: BTN_FRMRESET
				},
				{
					handler: function () {
						this.getRecordsCount();
					}.createDelegate(this),
					// iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 123,
					text: BTN_FRMCOUNT
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function () {
						alert('Что-то с панелью');
						//this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
						this.findById('Reab_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('Reab_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}
			],
			getFilterForm: function () {
				if (this.filterForm == undefined)
				{
					this.filterForm = this.findById('ReabRegistryFilterForm');
				}
				return this.filterForm;
			},

			items: [
				getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: true,
					allowPersonPeriodicSelect: true,
					id: 'ReabRegistryFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'ReabRegistry',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					tabPanelId: 'Reab_SearchFilterTabbar',
					tabs: [
						{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 220,
							layout: 'form',
							listeners: {
								'activate': function () {
									this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
								}.createDelegate(this)
							},

							title: lang['6_registr'],
							items: [
								{
									xtype: 'swpersonregistertypecombo',
									hiddenName: 'PersonRegisterType_id',
									labelWidth: 220,
									width: 200
								},
								{
									fieldLabel: lang['data_vklyucheniya_v_registr'],
									labelWidth: 220,
									name: 'PersonRegister_setDate_Range',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170,
									xtype: 'daterangefield'
								},
								{
									fieldLabel: lang['data_isklyucheniya_iz_registra'],
									name: 'PersonRegister_disDate_Range',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 170,
									xtype: 'daterangefield'
								},
								{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											items: [
												{
													fieldLabel: 'Профиль',
													mode: 'local',
													store: new Ext.data.JsonStore({
														url: '?c=Ufa_Reab_Register_User&m=SeekProfReab',
														autoLoad: true,
														fields: [
															{name: 'DirectType_id', type: 'int'},
															{name: 'DirectType_name', type: 'string'}
														],
														key: 'DirectType_id',
													}),
													editable: false,
													triggerAction: 'all',
													hiddenName: 'DirectType_id',
													displayField: 'DirectType_name',
													valueField: 'DirectType_id',
													width: 150,
													xtype: 'combo',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'{DirectType_name} ' + '&nbsp;' +
													'</div></tpl>',
													listeners:
													{
														//'change': function(cb,newValue, oldValue ) {
														//	if (newValue == '')
														//{
														////													Ext.getCmp('pmUser_docupd').setValue('');
														////													Ext.getCmp('pmUser_docupd').setDisabled(true);
														//													//Ext.getCmp('ReabRegistry').getGrid().getColumnModel().setHidden(23,true)// Предоставление доступа к полю "Кем создана анкета"
														//												} else {
														////													Ext.getCmp('pmUser_docupd').setDisabled(false);
														//													//Ext.getCmp('ReabRegistry').getGrid().getColumnModel().setHidden(23,false)
														//												}
														//											},
														'select': function (combo, record, index)
														{
															if (record.data.DirectType_id == '')
															{
																Ext.getCmp('Reabquest_yn').setValue('');
																Ext.getCmp('Reabquest_yn').setDisabled(true);
																Ext.getCmp('ReabScale_yn').setValue('');
																Ext.getCmp('ReabScale_yn').setDisabled(true);
																Ext.getCmp('StageType').setValue('');
																Ext.getCmp('StageType').setDisabled(true);
															} else
															{
																Ext.getCmp('Reabquest_yn').setDisabled(false);
																Ext.getCmp('ReabScale_yn').setDisabled(false);
																Ext.getCmp('StageType').setDisabled(false);
															}
														}
													}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 100,
											items: [
												{
													fieldLabel: 'Этап',
													mode: 'local',
													disabled: true,
													store: new Ext.data.JsonStore({
														url: '?c=Ufa_Reab_Register_User&m=SeekStageReab',
														autoLoad: true,
														fields: [
															{name: 'StageType_id', type: 'int'},
															{name: 'StageType', type: 'string'}
														],
														key: 'StageType_id',
													}),
													editable: false,
													triggerAction: 'all',
													hiddenName: 'StageType_id',
													listWidth: 50,
													style: 'text-align:center;',
													displayField: 'StageType',
													valueField: 'StageType_id',
													id: "StageType",
													width: 50,
													xtype: 'combo',
													tpl: '<tpl for="."><div class="x-combo-list-item">' +
													'&nbsp&nbsp;' + '{StageType} ' + '&nbsp;' +
													'</div></tpl>'
												}
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 100,
											items: [
												{
													fieldLabel: 'Анкеты',
													// hideLabel : false,
													// labelStyle: 'width:100px;font-style:italic;font-size:1.2em;color:blue;' ,
													id: 'Reabquest_yn',
													disabled: true,
													xtype: 'checkbox',
													listeners: {
														check: function (checked) {
															// console.log('checked1 = ', checked.checked);

														}
													}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											labelWidth: 100,
											items: [
												{
													fieldLabel: 'Шкалы',
													id: 'ReabScale_yn',
													disabled: true,
													xtype: 'checkbox'
												}
											]
										},
										{
											layout: 'form',
											border: false,
											items: [
												/*
												 {
												 xtype: 'combo',
												 displayField: 'pmUser_FioL',
												 editable: true,
												 enableKeyEvents: true,
												 fieldLabel: 'Пользователь',
												 hiddenName: 'pmUser_docupdID',
												 id: 'pmUser_docupd',
												 disabled: true,
												 minChars: 1,
												 width: 300,
												 name : "pmUser_docupdID",
												 minLength: 1,
												 mode: 'local',
												 resizable: true,
												 selectOnFocus: true,
												 store: new Ext.data.Store({
												 autoLoad: false,
												 reader: new Ext.data.JsonReader({
												 id: 'pmUser_id'
												 }, [
												 {name: 'pmUser_id', mapping: 'pmUser_id'},
												 {name: 'pmUser_FioL', mapping: 'pmUser_FioL'},
												 {name: 'pmUser_Fio', mapping: 'pmUser_Fio'},
												 {name: 'pmUser_Login', mapping: 'pmUser_Login'},
												 {name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'}
												 ]),
												 sortInfo: {
												 direction: 'ASC',
												 field: 'pmUser_Fio'
												 },
												 url: '/?c=BSK_Register_User&m=getCurrentOrgUsersList'
												 }),
												 triggerAction: 'all',
												 valueField: 'pmUser_id',
												 listeners: {
												 change: function() {

												 },
												 keydown: function(inp, e) {
												 if ( e.getKey() == e.END ) {
												 this.inKeyMode = true;
												 this.select(this.getStore().getCount() - 1);
												 }

												 if ( e.getKey() == e.HOME ) {
												 this.inKeyMode = true;
												 this.select(0);
												 }

												 if ( e.getKey() == e.PAGE_UP ) {
												 this.inKeyMode = true;
												 var ct = this.getStore().getCount();

												 if ( ct > 0 ) {
												 if ( this.selectedIndex == -1 ) {
												 this.select(0);
												 }
												 else if ( this.selectedIndex != 0 ) {
												 if ( this.selectedIndex - 10 >= 0 )
												 this.select(this.selectedIndex - 10);
												 else
												 this.select(0);
												 }
												 }
												 }

												 if ( e.getKey() == e.PAGE_DOWN ) {
												 if ( !this.isExpanded() ) {
												 this.onTriggerClick();
												 }
												 else {
												 this.inKeyMode = true;
												 var ct = this.getStore().getCount();

												 if ( ct > 0 ) {
												 if ( this.selectedIndex == -1 ) {
												 this.select(0);
												 }
												 else if ( this.selectedIndex != ct - 1 ) {
												 if ( this.selectedIndex + 10 < ct - 1 )
												 this.select(this.selectedIndex + 10);
												 else
												 this.select(ct - 1);
												 }
												 }
												 }
												 }

												 if ( e.altKey || e.ctrlKey || e.shiftKey )
												 return true;

												 if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE) {
												 inp.setValue('');
												 inp.setRawValue("");
												 inp.selectIndex = -1;
												 if ( inp.onClearValue ) {
												 this.onClearValue();
												 }
												 e.stopEvent();
												 return true;
												 }
												 },
												 beforequery: function(q) {
												 if ( q.combo.getStore().getCount() == 0 ) {
												 q.combo.getStore().removeAll();
												 q.combo.getStore().load();
												 }
												 }
												 }
												 }
												 */
											]

										}

									]
								}
							]
						}
					]
				}),
				this.ReabRegistrySearchFrame
			]
		});

		sw.Promed.swReabRegistryWindow.superclass.initComponent.apply(this, arguments);

	},

	listeners: {
		/*
		 'beforeShow': function(win) {
		 if (String(getGlobalOptions().groups).indexOf('BskRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
		 {
		 sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «'+ win.title +'»');
		 return false;
		 }
		 },
		 */
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('ReabRegistryFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('ReabRegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('Reab_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('ReabRegistryFilterForm').setWidth(nW - 5);
		}
	},

	show: function () {

		/*		this.ReabRegistrySearchFrame.addActions({
		 //			name:'person_register_out',
		 //			text:lang['isklyuchit_iz_registra'],
		 //			tooltip: lang['isklyuchit_iz_registra'],
		 //			iconCls: 'delete16',
		 //			handler: function() {
		 //
		 //				Ext.Msg.alert('Исключение из регистра', 'Исключение из регистра временно отключено.');
		 //			}.createDelegate(this)
		 //		});
		 */
//
		this.ReabRegistrySearchFrame.addActions({
			name: 'open_emk',
			text: lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		});

		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			} else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function (data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		;

		if (this.userMedStaffFact.ARMType == 'spec_mz')
		{
			Ext.getCmp('swReabRegistryWindow').ReabRegistrySearchFrame.ViewActions.action_add.setHidden(true);
		}

		sw.Promed.swReabRegistryWindow.superclass.show.apply(this, arguments);

		/*               this.ReabRegistrySearchFrame.addActions({
		 //			name:'ReabObjectButton',
		 //			text:lang['Profil'],
		 //			disabled: true,
		 //			tooltip: 'Профиль наблюдения',
		 //			iconCls: 'address-book16',
		 //			handler: function() {
		 //				var sm = this.ReabRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
		 //				if (typeof sm != 'undefined') {
		 //					var Person_id = sm.get('Person_id');
		 //				}
		 //				else {
		 //					Ext.Msg.alert('Ошибка ввода','Для просмотра профиля наблюдения выделите строку с пациентом и нажмите "Предмет наблюдения"');
		 //					return false;
		 //				}
		 //
		 //				//console.log('Person_id',Person_id);
		 //                                alert('Просмотр по профилю?????');
		 //
		 ////				getWnd('swBSKPreviewWindow').show({
		 ////						Person_id: Person_id
		 ////				});
		 //			}.createDelegate(this)
		 //		},4);
		 */
		// Тип поиска человека и записи регистра
		Ext.getCmp('swReabRegistryWindow').findById('ReabRegistryFilterForm').items.items[0].items.items[0].hide();
		var base_form = this.findById('ReabRegistryFilterForm').getForm();
//
		this.restore();
		this.center();
		this.maximize();
		this.doReset();


		this.doLayout();

		base_form.findField('PersonRegisterType_id').setValue(1);
		this.doSearch({firstLoad: true});
	},
	emkOpen: function ()
	{
		var grid = this.ReabRegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
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
			callback: function ()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function () {
		var grid = this.ReabRegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		Ext.Msg.show({
			title: lang['vopros'],
			msg: lang['udalit_vyibrannuyu_zapis_registra'],
			buttons: Ext.Msg.YESNO,
			fn: function (btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function (options, success, response) {
							this.getLoadMask().hide();
							if (success) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if (obj.success)
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
	}
});


