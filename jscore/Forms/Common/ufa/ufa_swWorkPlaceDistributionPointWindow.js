/**
* АРМ пункта отпуска
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlaceDistributionPointWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: false,
	id: 'swWorkPlaceDistributionPointWindow',
	ARMType: 'dpoint',
	Marking: false,
	params: {
		
	}, 
        listeners: {
        'success': function(source, params) {
          //var $flag_update = 0;   
          
		var record =  Ext.getCmp('wpdpWorkPlaceGridPanel').getGrid().getSelectionModel().getSelected();
		record.set('Delay_info', params.Delay_info);
		record.commit();
        },
		'activate': function(){
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getReceptFieldsFromScanner.createDelegate(this), ARMType: 'dpoint', readObject: 'recept_code'});
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromUec.createDelegate(this)});

		},
		'deactivate': function() {
						sw.Applets.BarcodeScaner.stopBarcodeScaner();
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
		}
        },
	uuidv4: function () {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = Math.random() * 16 | 0,
					v = c == 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	},
	saveReceptNotification_phone: function(record) {
		if (!record) return;

		var params = {
			EvnRecept_id: record.get('EvnRecept_id'),
			receptNotification_phone: record.get('receptNotification_phone')
		};
		record.commit();
		Ext.Ajax.request({
			url: '/?c=Farmacy&m=saveReceptNotification',
			params: params
		});
	},
	getReceptFieldsFromScanner: function(recept_data) {
		var that = this;
        if(recept_data.evn_recept_set_year.length == 2)
            recept_data.evn_recept_set_year = '20' + recept_data.evn_recept_set_year;
        if(recept_data.evn_recept_set_day.length == 1)
            recept_data.evn_recept_set_day = '0' + recept_data.evn_recept_set_day;
        if(recept_data.evn_recept_set_month.length == 1)
            recept_data.evn_recept_set_month = '0' + recept_data.evn_recept_set_month;

		recept_data.evn_recept_set_date		= recept_data.evn_recept_set_day + '.' + recept_data.evn_recept_set_month + '.' + recept_data.evn_recept_set_year;

		var evn_recept_date = recept_data.evn_recept_set_date;
		this.FilterPanel.getForm().findField('EvnRecept_Ser').setValue(recept_data.evn_recept_ser);
		this.FilterPanel.getForm().findField('EvnRecept_Num').setValue(recept_data.evn_recept_num);
		this.dateMenu.setValue(evn_recept_date+' - '+evn_recept_date);
		var begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		var endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

		var search_params = new Object();
		search_params.EvnRecept_setDate = recept_data.evn_recept_set_year + '-' + recept_data.evn_recept_set_month + '-' + recept_data.evn_recept_set_day;
		search_params.EvnRecept_Ser = recept_data.evn_recept_ser;
		search_params.EvnRecept_Num = recept_data.evn_recept_num;

		var barcode_result = recept_data;

		Ext.Ajax.request({
			url: '/?c=EvnRecept&m=SearchReceptFromBarcode',
			params: search_params,
			success: function(response){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj[0]) {
					var query_result = response_obj[0];
					var errors_list = "";
					var error_postfix = " в штрих-коде рецепта и в БД не совпадает";

					//if (barcode_result.drug_is_kek != query_result.drug_is_kek)
					//	errors_list += "Признак Выписка через ВК <br>";// (данные штрих-кода - " + barcode_result.drug_is_kek + ", данные из БД - " + query_result.drug_is_kek + " <br>";

					if (barcode_result.evn_recept_set_date != query_result.evn_recept_set_date)
					//errors_list += "Дата выписки рецепта <br>";// (данные штрих-кода - " + barcode_result.evn_recept_set_date + ", данные из БД - " + query_result.evn_recept_set_date + " <br>";
						errors_list += "Дата выписки рецепта <br>";// (данные штрих-кода - " + barcode_result.evn_recept_set_date + ", данные из БД - " + query_result.evn_recept_set_date + " <br>";

					if (barcode_result.recept_valid_code != query_result.recept_valid_code)
						errors_list += "Срок действия <br>";// (данные штрих-кода - " + barcode_result.recept_valid_code + ", данные из БД - " + query_result.recept_valid_code + " <br>";

					if (barcode_result.privilege_type_code != query_result.privilege_type_code)
						errors_list += "Льгота пациента <br>";// (данные штрих-кода - " + barcode_result.privilege_type_code + ", данные из БД - " + query_result.privilege_type_code + " <br>";

					if (barcode_result.drug_dose_count != query_result.drug_dose_count)
						errors_list += "Количество единиц (данные штрих-кода - " + barcode_result.drug_dose_count + ", данные из БД - " + query_result.drug_dose_count + " <br>";

					if (barcode_result.drug_dose != query_result.drug_dose)
						errors_list += "Дозировка (данные штрих-кода - " + barcode_result.drug_dose + ", данные из БД - " + query_result.drug_dose + " <br>";

					if (barcode_result.person_snils != query_result.person_snils)
						errors_list += "СНИЛС пациента (данные штрих-кода - " + barcode_result.person_snils + ", данные из БД - " + query_result.person_snils + " <br>";

					if (barcode_result.drug_mnn_torg_code != query_result.drug_mnn_torg_code)
						errors_list += "Код МНН (данные штрих-кода - " + barcode_result.drug_mnn_torg_code + ", данные из БД - " + query_result.drug_mnn_torg_code + " <br>";

					if (barcode_result.drug_is_mnn != query_result.drug_is_mnn)
						errors_list += "Признак МНН <br>";// (данные штрих-кода - " + barcode_result.drug_is_mnn + ", данные из БД - " + query_result.drug_is_mnn + " <br>";

					if (barcode_result.recept_discount_code != query_result.recept_discount_code)
						errors_list += "Скидка <br>";// (данные штрих-кода - " + barcode_result.recept_discount_code + ", данные из БД - " + query_result.recept_discount_code + " <br>";

					if (barcode_result.recept_finance_code != query_result.recept_finance_code)
						errors_list += "Тип финансирования <br>";// (данные штрих-кода - " + barcode_result.recept_finance_code + ", данные из БД - " + query_result.recept_finance_code + " <br>";

					if (barcode_result.diag_code != query_result.diag_code)
						errors_list += "Диагноз (данные штрих-кода - " + barcode_result.diag_code + ", данные из БД - " + query_result.diag_code + " <br>";

					if (barcode_result.evn_recept_num != query_result.evn_recept_num)
						errors_list += "Номер рецепта (данные штрих-кода - " + barcode_result.evn_recept_num + ", данные из БД - " + query_result.evn_recept_num + " <br>";

					if (barcode_result.evn_recept_ser != query_result.evn_recept_ser)
						errors_list += "Серия рецепта (данные штрих-кода - " + barcode_result.evn_recept_ser + ", данные из БД - " + query_result.evn_recept_ser + " <br>";

					if (barcode_result.lpu_code != query_result.lpu_code)
						errors_list += "Код МО (данные штрих-кода - " + barcode_result.lpu_code + ", данные из БД - " + query_result.lpu_code + " <br>";

					if (barcode_result.lpu_ogrn != query_result.lpu_ogrn)
						errors_list += "ОГРН МО (данные штрих-кода - " + barcode_result.lpu_ogrn + ", данные из БД - " + query_result.lpu_ogrn + " <br>";

					if (barcode_result.medpersonal_code != query_result.medpersonal_code)
						errors_list += "Код лечащего врача (данные штрих-кода - " + barcode_result.medpersonal_code + ", данные из БД - " + query_result.medpersonal_code + " <br>";

					var success = true;
					if (errors_list != "")
						that.doSearch();
					else
						sw.swMsg.alert('Ошибка', "В ходе проверки рецепта обнаружены следующие несоответствия данных в штрих-коде и в БД: <br>" + errors_list);
				}
			}.createDelegate(this)
		});

	},
	setReceptType: function() { //переключение АРМ-а с одного типа рецептов на другой (общие либо льготные)
		var wnd = this;
		var type = this.FilterPanel.getForm().findField('ReceptType').getValue();

		if (type == 'lgot') {
			wnd.SearchFormType = 'EvnRecept';
		}
		if (type == 'general') {
			wnd.SearchFormType = 'EvnReceptGeneral';
		}
	},
	
	openReceptPullOffServiceWindow: function() {
		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = {};

		params.callback = function(data) {
			current_window.GridPanel.getAction('action_refresh').execute();
		};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		switch(Number(selected_record.get('ReceptDelayType_id'))) {
			case 2: 
				params.action = 'add';
				break;
			case 5: 
				params.action = 'edit';
				break;
			default: 
				return false;
				break;
		}

		var evn_recept_id = selected_record.get('EvnRecept_id');

		var person_id = selected_record.get('Person_id');
		var person_evn_id = selected_record.get('PersonEvn_id');
		var server_id = selected_record.get('Server_id');
		var evnrecept_ser = selected_record.get('EvnRecept_Ser');
		var evnrecept_num = selected_record.get('EvnRecept_Num');
		var evnrecept_setdate = Ext.util.Format.date(selected_record.get('EvnRecept_setDate'), 'd.m.Y');

		//пока не реализована отдельная форма, открытие общих рецептов заблокированно
		if (selected_record.get('EvnReceptGeneral_id') > 0) {
			return false;
		}
		if ( evn_recept_id ) {
			params.EvnRecept_id = evn_recept_id;
			params.parent_id = 'swWorkPlaceDistributionPointWindow';
			params.ReceptData = 'Серия '+evnrecept_ser +' Номер '+ evnrecept_num +' Выписан '+ evnrecept_setdate;
			params.Lpu_Nick = selected_record.get('Lpu_Nick');
			params.Lpu_id = selected_record.get('Lpu_id');
			params.EvnRecept_obrDT = Ext.util.Format.date(selected_record.get('EvnRecept_obrDT'), 'd.m.Y');
			
			getWnd('swReceptPullOffServiceWindow').show(params);
		}
	},

	deleteOtkazAct: function() {
		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = {};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		//пока не реализована отдельная форма, открытие общих рецептов заблокированно
		if (selected_record.get('EvnReceptGeneral_id') > 0) {
			return false;
		}
		if (selected_record.get('OrgFarmacy_oid') != getGlobalOptions().OrgFarmacy_id) {
			sw.swMsg.alert(lang['oshibka'], 'У Вас нет прав на выполнение операции');
			return false;
		}

		switch(selected_record.get('ReceptDelayType_id')) {
			case '3': 
				var params = {
					EvnRecept_id: selected_record.get('EvnRecept_id')
				};
				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: 'После удаления данных о признании рецепта не правильно выписанным рецепт иметь статус "Выписан".  Продолжить?',
					title: 'Подтверждение',
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							current_window.getLoadMask(LOAD_WAIT).show();
							Ext.Ajax.request({
								url: '/?c=EvnRecept&m=deleteReceptWrongRecord',
								params: params,
								callback: function(options, success, response) {
									current_window.getLoadMask().hide();
									var response_obj = Ext.util.JSON.decode(response.responseText);
									current_window.GridPanel.getAction('action_refresh').execute();
								}.createDelegate(this),
								failure: function()  {
									current_window.getLoadMask().hide();
								}
							});
						}
					}
				});
				break;
			case '5':
				var params = {
					EvnRecept_id: selected_record.get('EvnRecept_id')
				};
				this.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					url: '/?c=EvnRecept&m=checkOutDocumentStatus',
					params: params,
					callback: function(options, success, response) {
						this.getLoadMask().hide();
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0].WhsDocumentStatusType_id != 1) {
							Ext.Msg.alert(lang['soobschenie'], 'Информация о снятии рецепта с обслуживания передана в МО, удаление не возможно');
							return false;
						} else if(response_obj[0].WhsDocumentStatusType_id == 1){
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: 'После удаления данных о снятии рецепта с обслуживания рецепт будет включен в список рецептов, находящихся на отложенном обеспечении.  Продолжить?',
								title: 'Подтверждение',
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ('yes' == buttonId) {
										current_window.getLoadMask(LOAD_WAIT).show();
										Ext.Ajax.request({
											url: '/?c=EvnRecept&m=deletePullOfServiceRecord',
											params: params,
											callback: function(options, success, response) {
												current_window.getLoadMask().hide();
												var response_obj = Ext.util.JSON.decode(response.responseText);
												current_window.GridPanel.getAction('action_refresh').execute();
											}.createDelegate(this),
											failure: function()  {
												current_window.getLoadMask().hide();
											}
										});
									}
								}
							});
						}
					}.createDelegate(this),
					failure: function()  {
						this.getLoadMask().hide();
					}
				});
				break;
			default:
				return false;
				break;
		}
	},
	show: function() {
		this.SearchFormType = 'EvnRecept';
		this.setReceptType();

		sw.Promed.swWorkPlaceDistributionPointWindow.superclass.show.apply(this, arguments);
		var form = this;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		this.FilterPanel.getForm().findField('Drug_id').getStore().baseParams.searchFull = 1;
		/*
		this.FilterPanel.getForm().findField('ER_MedPersonal_id').getStore().load({
			callback: function(records, options, success) {
				if ( !success ) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке справочника мед. персонала (АРМ провизора)');
					return false;
				}
			}
		});
		*/
		this.hasPolka = false;
		this.getLpuUnitPolkaCount({callback: function(data){
			form.hasPolka = (data && data.LpuUnitCount > 0);
		}.createDelegate(this)});
	    
	    this.GridPanel.addActions({
			name: 'action_delete_wrong',
			text: 'Удалить отказ/акт',
			iconCls: 'delete16',
			//hidden: true,
			handler: function() {
				this.deleteOtkazAct();
			}.createDelegate(this)
		});

		this.GridPanel.addActions({
			name: 'action_pull_off_service',
			text: 'Снять с обслуживания',
			iconCls: 'delete16',
			//hidden: true,
			handler: function () {
				this.openReceptPullOffServiceWindow();
			}.createDelegate(this)
		});

		this.GridPanel.addActions({
			name: 'action_wrong',
			text: 'Неправильно выписанный рецепт',
			iconCls: 'delete16',
			handler: function () {
				//this.openEvnReceptViewWindow()
				this.EvnReceptWrongWindow()
			}.createDelegate(this)
		});

		this.signMenu = new Ext.menu.Menu({
			items: [{
				text: 'ЭЦП рецепта',
				menu: [
					new Ext.Action({
						text: lang['spisok_versiy_dokumenta']
						, scope: this
						, handler: function () {
							var selected_record = form.GridPanel.getGrid().getSelectionModel().getSelected();
							if (selected_record && selected_record.get('EvnRecept_id')) {
								getWnd('swEMDVersionViewWindow').show({
									EMDRegistry_ObjectName: 'EvnRecept',
									EMDRegistry_ObjectID: selected_record.get('EvnRecept_id')
								});
							}
						}
					})
				]
			}, {
				text: 'ЭЦП обеспечения рецепта',
				menu: [
					new Ext.Action({
						text: lang['podpisat_dokument']
						, scope: this
						, handler: function () {
							var selected_record = form.GridPanel.getGrid().getSelectionModel().getSelected();
							if (selected_record && selected_record.get('EvnRecept_id')) {
								// подпись эцп
								signDocEcp({
									win: form,
									Doc_Type: 'EvnReceptOtv',
									Doc_id: selected_record.get('EvnRecept_id'),
									callback: function (result) {
										if (result.success) {
											sw.swMsg.alert(lang['informatsiya'], lang['dokument_uspeshno_podpisan']);
											selected_record.set('EvnRecept_signotvDT', getGlobalOptions().date);
											selected_record.set('ROSignPmUser_Name', getGlobalOptions().pmuser_name);
											selected_record.set('EvnRecept_IsOtvSigned', 2);
											selected_record.set('Signatures_id', result.Signatures_id);
											selected_record.commit();
											form.checkSignMenuAvailable();
										}
									}
								});
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['dokument_ne_zapolnen_ili_ne_gotov_k_podpisaniyu']);
							}
						}
					}),
					new Ext.Action({
						text: lang['spisok_versiy_dokumenta']
						, scope: this
						, handler: function () {
							var selected_record = form.GridPanel.getGrid().getSelectionModel().getSelected();
							if (selected_record && selected_record.get('EvnRecept_id')) {
								if (selected_record && selected_record.get('Signatures_id')) {
									getWnd('swStickVersionListWindow').show({
										Signatures_id: selected_record.get('Signatures_id')
									});
								} else {
								getWnd('swDocVersionListWindow').show({
									Doc_id: selected_record.get('EvnRecept_id'),
									Doc_Type: 'EvnReceptOtv'
								});
							}
						}
						}
					}),
					new Ext.Action({
						text: lang['verifikatsiya']
						, scope: this
						, handler: function () {
							var selected_record = form.GridPanel.getGrid().getSelectionModel().getSelected();
							if (selected_record && selected_record.get('EvnRecept_id')) {
								signDocumentVerification({
									ownerWindow: form.GridPanel,
									Doc_Type: 'EvnReceptOtv',
									Doc_id: selected_record.get('EvnRecept_id'),
									callback: function(result) {
											if (!Ext.isEmpty(result.valid)) {
												if (result.valid == 2) {
													selected_record.set('EvnRecept_IsOtvSigned', 2);
													selected_record.commit();
												} else if (result.valid == 1) {
													selected_record.set('EvnRecept_IsOtvSigned', 1);
													selected_record.commit();
												}
											} else {
												selected_record.set('EvnRecept_IsOtvSigned', null);
												selected_record.commit();
											}
										}
								});
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['dokument_ne_zapolnen_i_ne_byil_podpisan']);
							}
						}
					})
				]
			}]
		});

		this.GridPanel.addActions({
			name: 'action_signmenu',
			text: 'Действия',
			iconCls: 'digital-sign16',
			menu: form.signMenu
		});
		
		var params = {};
		params.Org_id = getGlobalOptions().org_id; 
		Ext.getCmp('SEW_Lpu4FarmStorage').store.load({
			params: params
		})
		 Ext.getCmp('btn_InformationRv').fireEvent('success');
	},
	buttonPanelActions: {
		/*action_DrugRequest: {
			nn: 'action_DrugRequest',
			tooltip: 'Заявка на лекарственные средства',
			text: 'Заявка на лекарственные средства',
			iconCls : 'mp-drugrequest32',
			disabled: true, 
			handler: function(){
				getWnd('swWorkPlaceDistributionPointWindow').showDrugRequestEditForm();
			}.createDelegate(this)
		},*/
		action_PrivilegeSearch: {
			nn: 'action_PrivilegeSearch',
			tooltip: 'Поиск льготников',
			text: 'Льготники',
			iconCls : 'mse-journal32',
			disabled: false, 
			handler: function(){
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
        EvnReceptInCorrectFind: {
            text: 'Журнал отсрочки',
            tooltip: 'Журнал отсрочки',
            iconCls : 'receipt-incorrect32',
            handler: function()
            {
                getWnd('swReceptInCorrectSearchWindow').show();
            }
        },
		action_DocUc: {
			nn: 'action_DocUc',
			tooltip: 'Документы учета медикаментов',
			text: 'Документы учета медикаментов',
			iconCls : 'document32',
			disabled: false,
			handler: function(){
				getWnd('swDokUcLpuViewWindow').show();
			}
		},
		action_Contragents: {
			nn: 'action_Contragents',
			tooltip: 'Справочник "Контрагенты"',
			text: 'Контрагенты',
			iconCls : 'org32',
			disabled: false, 
			handler: function(){
				getWnd('swContragentViewWindow').show({
					ARMType: 'dpoint'
				});
			}
		},
		action_MedOstat: {
			nn: 'action_MedOstat',
			tooltip: 'Остатки медикаментов',
			text: 'Остатки медикаментов',
			iconCls : 'rls-torg32',
			disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [
					{
						text: 'Оборотная ведомость',
						tooltip: 'Оборотная ведомость',
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugTurnoverListWindow').show();//{mode: 'suppliers'});
						}
					},
                                        {
						text: 'Просмотр остатков организации пользователя',
						tooltip: 'Просмотр остатков организации пользователя',
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({
                                mode: 'suppliers',
                                userMedStaffFact: this.userMedStaffFact
                            });
						}.createDelegate(this)
					}, {
						text: 'Просмотр остатков по складам Аптек и РАС',
						tooltip: 'Просмотр остатков по складам Аптек и РАС',
						iconCls: 'pill16',
                        hidden: getGlobalOptions().region.nick == 'ufa',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
						}
					},
					{
						text: MM_DLO_MEDAPT,
						tooltip: 'Работа с остатками медикаментов по аптекам',
						iconCls : 'drug-farm16',
						hidden: (!getGlobalOptions().superadmin),
						handler: function()
						{
							getWnd('swDrugOstatByFarmacyViewWindow').show();
						}
					},
					{
						text: MM_DLO_MEDNAME,
						tooltip: 'Работа с остатками медикаментов по наименованию',
						iconCls : 'drug-name16',
						hidden: (!getGlobalOptions().superadmin),
						handler: function()
						{
							getWnd('swDrugOstatViewWindow').show();
						}
					}
                    //sw.Promed.Actions.OstAptekaViewAction,
                    //sw.Promed.Actions.OstDrugViewAction
                ]
            })
		},
        action_References: {
            nn: 'action_References',
            tooltip: 'Справочники',
            text: 'Справочники',
            iconCls : 'book32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: 'Просмотр РЛС',
                    text: 'Просмотр РЛС',
                    iconCls: 'rls16',
                    handler: function() {
                        if ( !getWnd('swRlsViewForm').isVisible() )
                            getWnd('swRlsViewForm').show();
                    }
                }, {
                    tooltip: 'МКБ-10',
                    text: 'Справочник МКБ-10',
                    iconCls: 'spr-mkb16',
                    handler: function() {
                        sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
                    }
                }, {
                    tooltip: 'Просмотр ' + getMESAlias(),
                    text: 'Просмотр ' + getMESAlias(),
                    iconCls: 'spr-mes16',
                    handler: function() {
                        if ( !getWnd('swMesOldSearchWindow').isVisible() )
                            getWnd('swMesOldSearchWindow').show();
                    }
                },
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
					name: 'action_DrugNomenSpr',
					text: 'Номенклатурный справочник',
					iconCls : '',
					handler: function() {
						getWnd('swDrugNomenSprWindow').show();
					}
				}, {
					name: 'action_PriceJNVLP',
					text: 'Цены на ЖНВЛП',
					iconCls : 'dlo16',
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					text: 'Предельные надбавки на ЖНВЛП',
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show();
					}
				},
				sw.Promed.Actions.swPrepBlockSprAction
				]
            })
        },
        action_StoragePlacement:
		{
			nn: 'action_StoragePlacement',
			tooltip: lang['razmechenie_na_skladah'],
			text: lang['razmechenie_na_skladah'],
			iconCls : 'storage-place32',
			handler: function()
			{
				getWnd('swStorageZoneViewWindow').show();
			}
		}
		/*action_Recipe: {
			nn: 'action_Recipe',
			tooltip: 'Рецепты',
			text: 'Рецепты',
			iconCls : 'receipt-new32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [					
					sw.Promed.Actions.EvnReceptProcessAction,
					sw.Promed.Actions.EvnReceptTrafficBookViewAction,
					sw.Promed.Actions.EvnReceptInCorrectFindAction
				]
			})
		}*/
	},
	showDrugRequestEditForm: function() {
		/*
		Получаем данные:
		является ли текущий пользователь врачом ЛЛО
		заявка на последний период, из имеющихся в системе или последняя имеющияся заявка.
		*/
		var params = {
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: getGlobalOptions().medpersonal_id
		};
		this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			url: '/?c=DrugRequest&m=getDrugRequestLast',
			params: params,
			callback: function(options, success, response) {
				this.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0].is_dlo != 1) {
					Ext.Msg.alert('Сообщение', 'Врач не имеет права на выписку рецептов по ЛЛО.');
					return false;
				}
				params.owner = this;
				params.Person_id = null;
				if (response_obj[0].next_DrugRequest_id > 0) {
					params.action = 'edit';
					params.DrugRequest_id = response_obj[0].next_DrugRequest_id;
					params.DrugRequestStatus_id = response_obj[0].next_DrugRequestStatus_id;
					params.DrugRequestPeriod_id = response_obj[0].next_DrugRequestPeriod_id;
					getWnd('swNewDrugRequestEditForm').show(params);
				} else {
					sw.swMsg.show({
						icon: Ext.MessageBox.QUESTION,
						msg: 'На следующий период '+ (response_obj[0].next_DrugRequestPeriod) +' заявка по врачу '+ (response_obj[0].MedPersonal_Fin) +' не найдена. Открыть последнюю имеющуюся или создать?',
						title: 'Заявка не найдена',
						buttons: {yes: 'Открыть', no: 'Создать', cancel: 'Отмена'},
						fn: function(buttonId, text, obj) {
							if ('yes' == buttonId) {
								if(response_obj[0].last_DrugRequest_id > 0) {
									params.action = 'edit';
									params.DrugRequest_id = response_obj[0].last_DrugRequest_id;
									params.DrugRequestStatus_id = response_obj[0].last_DrugRequestStatus_id;
									params.DrugRequestPeriod_id = response_obj[0].last_DrugRequestPeriod_id;
									getWnd('swNewDrugRequestEditForm').show(params);
								} else {
									Ext.Msg.alert('Сообщение', 'У врача нет ни одной заявки на лекарственные средства.');
								}
							}
							if ('no' == buttonId) {
								params.action = 'add';
								params.DrugRequest_id = 0;
								params.DrugRequestStatus_id = 1;
								params.DrugRequestPeriod_id = response_obj[0].next_DrugRequestPeriod_id;
								getWnd('swNewDrugRequestEditForm').show(params);
							}
						}
					});
				}
			}.createDelegate(this),
			failure: function()  {
				this.getLoadMask().hide();
			}
		});
	},
	getLpuUnitPolkaCount: function(params){
		params = Ext.applyIf(params, {callback: Ext.emptyFn});
		Ext.Ajax.request({
			params: {Lpu_id: getGlobalOptions().lpu_id, LpuUnitType_SysNick: 'polka'},
			url: '/?c=LpuStructure&m=getLpuUnitCountByType',
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					params.callback(response_obj);
				}
			}.createDelegate(this)
		});
	},
	openEvnReceptViewWindow: function() {
		var action = 'view';
		var wnd;

		if ( getGlobalOptions().region.nick.inlist([ 'pskov', 'saratov', 'khak', 'krym']) ) {
			wnd = 'swEvnReceptRlsEditWindow';
		}
		else {
			wnd = 'swEvnReceptEditWindow';
		}

		
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования рецепта уже открыто');
			return false;
		}

		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = new Object();

		params.action = action;
		params.ARMType = this.ARMType;
		params.viewOnly = !this.hasPolka;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				grid.getStore().reload();
			} else {
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var evn_recept_id = selected_record.get('EvnRecept_id');
		var person_id = selected_record.get('Person_id');
		var person_evn_id = selected_record.get('PersonEvn_id');
		var server_id = selected_record.get('Server_id');

		//пока не реализована отдельная форма, открытие общих рецептов заблокированно
		if (selected_record.get('EvnReceptGeneral_id') > 0) {
			return false;
		}

		if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
			params.EvnRecept_id = evn_recept_id;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			params.Person_id = person_id;
			params.PersonEvn_id = person_evn_id;
			params.Server_id = server_id;
			getWnd(wnd).show(params);
		}
	},
                
       EvnReceptWrongWindow: function() {
		var wnd;
                
		wnd = 'swEvnReceptWrongEditWindow';

		/*
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования рецепта уже открыто');
			return false;
		}
                */

		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = new Object();

		
		//params.ARMType = this.ARMType;
		//params.viewOnly = !this.hasPolka;
		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				grid.getStore().reload();
			} else {
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
               
               if(selected_record.get('ReceptDelayType_id') == 1)
                   //  Если рецепт обеспечен
                    return false;
               else if(selected_record.get('ReceptDelayType_id') == undefined  || selected_record.get('ReceptDelayType_id') == '')
                    params.action = 'add';
                else if(selected_record.get('ReceptDelayType_id') == 2)
                    params.action = 'add';
                else if(selected_record.get('ReceptDelayType_id') == 3)
                    params.action = 'edit';
                else
                    return false;
                
               // params.OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id;
                //params.Org_id = getGlobalOptions().org_id;
                    
		var evn_recept_id = selected_record.get('EvnRecept_id');
               
		var person_id = selected_record.get('Person_id');
		var person_evn_id = selected_record.get('PersonEvn_id');
		var server_id = selected_record.get('Server_id');

		//пока не реализована отдельная форма, открытие общих рецептов заблокированно
		if (selected_record.get('EvnReceptGeneral_id') > 0) {
			return false;
		}

		if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
			params.EvnRecept_id = evn_recept_id;
			params.parent_id = 'swWorkPlaceDistributionPointWindow';
			getWnd(wnd).show(params);
		}
	},
	     
       swEvnReceptNotificationEditWindow: function() {
		var wnd;
                
		wnd = 'swEvnReceptNotificationEditWindow';

		var current_window = this;
		var grid = current_window.GridPanel.getGrid();
		var params = new Object();

		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				grid.getStore().reload();
			} else {
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
               
               if(selected_record.get('ReceptDelayType_id') == 1)
                   //  Если рецепт обеспечен
                    return false;
               else if(selected_record.get('ReceptDelayType_id') == undefined  || selected_record.get('ReceptDelayType_id') == '')
                    params.action = 'add';
                else if(selected_record.get('ReceptDelayType_id') == 2)
                    params.action = 'add';
                else if(selected_record.get('ReceptDelayType_id') == 3)
                    params.action = 'edit';
                else
                    return false;
                
               // params.OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id;
                //params.Org_id = getGlobalOptions().org_id;
                    
		var evn_recept_id = selected_record.get('EvnRecept_id');
               
		var person_id = selected_record.get('Person_id');
		var person_evn_id = selected_record.get('PersonEvn_id');
		var server_id = selected_record.get('Server_id');

		//пока не реализована отдельная форма, открытие общих рецептов заблокированно
		if (selected_record.get('EvnReceptGeneral_id') > 0) {
			return false;
		}

		if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
			params.EvnRecept_id = evn_recept_id;
			params.parent_id = 'swWorkPlaceDistributionPointWindow';
			params.EvnRecept_obrDate = '01.01.1900';
			getWnd(wnd).show(params);
		}
	},
	                        
        doSave: function (selected_record) {
                        var current_window = this;
                        var params = new Object();
                        var m_store = sw.Promed.MedStaffFactByUser.store;
                  
			params.Contragent_id = getGlobalOptions().Contragent_id;
			params.EvnRecept_id = selected_record.get('EvnRecept_id');
			params.EvnReceptGeneral_id = selected_record.get('EvnReceptGeneral_id');
			params.Drug_id = selected_record.get('Drug_rlsid');
			params.Drug_Name = selected_record.get('Drug_Name');
			params.DrugNomen_Code = selected_record.get('DrugNomen_Code');
			params.EvnRecept_Kolvo = selected_record.get('EvnRecept_Kolvo');
			params.EvnRecept_setDate =  selected_record.get('EvnRecept_setDate');
			params.DocumentUc_id = selected_record.get('DocumentUc_id');
			params.WhsDocumentCostItemType_id = selected_record.get('WhsDocumentCostItemType_id');
			if (selected_record.get('ReceptDelayType_id') != 2) {
			    //  Если рецепт не отложен
			    params.EvnRecept_DateCtrl =  selected_record.get('EvnRecept_DateCtrl');
			    params.PersonPrivilege_begDate =  selected_record.get('PersonPrivilege_begDate');
			    params.PersonPrivilege_endDate =  selected_record.get('PersonPrivilege_endDate');
			} else if (selected_record.get('subAccountType_id') && selected_record.get('subAccountType_id') == 2) {
			    params.subAccountType_id = selected_record.get('subAccountType_id');
			    params.operation = 'receptNotification';
			} else {
			    params.subAccountType_id = null;
			}
			
			params.callback = function() {
				current_window.doSearch();
			}
			params.MedService_id = null;
			if (m_store) {
				var idx = m_store.findBy(function(rec) { return rec.get('MedServiceType_SysNick') == 'dpoint'; });
				if (idx >= 0) {
					params.MedService_id = m_store.getAt(idx).get('MedService_id');
				}
			}
            params.Marking = current_window.Marking;           
            if (selected_record.get('ReceptDelayType_id') == 2 && selected_record.get('receptNotification_setDate') != '') {
			    //  Если отсрочка с оповещением
			    params.subAccountType_id = 2;
			    getWnd('swEvnReceptRlsProvideWindow').show(params);
			} else {
                        //предварительная проверка наличия медикамента на остатках
								Ext.Ajax.request({
									callback: function(options, success, response) {
										if (success) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											var need = params.EvnRecept_Kolvo;
											var  Kolvo = 0;
											for(var i = 0; i < response_obj.length; i++) {
												need -= response_obj[i].DrugOstatRegistry_Kolvo;
												Kolvo += parseFloat(response_obj[i].DrugOstatRegistry_Kolvo);
												//Kolvo = Kolvo - 0;
												if (need <= 0) {
													break;
												}
											} 
											//if (need <= 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
											if (Kolvo > 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
											    if (params.subAccountType_id == 2) {
												//  Если просроченные рецепты, то убираем значение субсчета
												selected_record.set('subAccountType_id', 0);
											    }
											    getWnd('swEvnReceptRlsProvideWindow').show(params);
											} else { // иначе ставим на отсрочку
												if (selected_record.get('ReceptDelayType_id') == 2 && selected_record.get('WhsDocumentCostItemType_id') == 103) { //Если рецепт БСК на отсрочке
													current_window.putEvnReceptOnDelay({msg: 'На остатках БСК недостаточно медикамента. Проверить остатки на РЛО?',
														params: params});
												}
												else if (selected_record.get('ReceptDelayType_id') != 2) { //Если рецепт еще не на отсрочке
													current_window.putEvnReceptOnDelay({msg: 'На остатках недостаточно медикамента. Поставить рецепт на отсрочку?',
														params: params});
												} else {
													sw.swMsg.alert('Ошибка', 'На остатках недостаточно медикамента для обеспечения рецепта');
												}
											}
										} else {
											sw.swMsg.alert('Ошибка', 'При проверке наличия медикамента на остатках возникли ошибки');
										}
									},
									params: {
										EvnRecept_id: params.EvnRecept_id,
										EvnReceptGeneral_id: params.EvnReceptGeneral_id,
										MedService_id: params.MedService_id,
										//operation: params.operation,
										subAccountType_id: params.subAccountType_id,
										PersonPrivilege_begDate:  params.PersonPrivilege_begDate,
										PersonPrivilege_endDate:  params.PersonPrivilege_endDate
										
									},
									url: '/?c=Farmacy&m=getDrugOstatForProvide'
								});
		}
                                                                
       },
      
       
        provideEvnRecept: function(obj) {
            
		var form = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();
		if (obj == 'action_delete') {
		    //  Если это оповещение, то можно брать из резерва
		    selected_record.set('subAccountType_id', 2);
		    //selected_record.commit();
		    //console.log('subAccountType_id = ' + selected_record.get('subAccountType_id'));
		}

		if (selected_record) {
                    if (getGlobalOptions().region.nick != 'ufa') {
                        if (selected_record.get('Drug_rlsid') <= 0 && selected_record.get('DrugComplexMnn_id') <= 0) {
				Ext.Msg.alert('Сообщение', 'Обеспечение рецепта невозможно. Отсутствует информация о медикаменте.');
				return false;
			}
			if (selected_record.get('ReceptDelayType_id') == 2 && selected_record.get('OrgFarmacy_oid') > 0 && selected_record.get('OrgFarmacy_oid') != getGlobalOptions().OrgFarmacy_id) {
				Ext.Msg.alert('Сообщение', 'Обеспечение рецепта невозможно. Аптека отсрочки не равна аптеке обращения.');
				return false;
			}
                    }
                   
		params.onHide = function() {
				var index = grid.getStore().indexOf(selected_record);
				grid.focus();
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
			};
                       
                //sw.swMsg.alert('Ошибка', 'Рецепт не может быть обеспечен, так как срок его действия истек');
                var msg = 'Срок рецепта истек! Обеспечить рецепт?';
                if (selected_record.get('EvnRecept_Shelf')  == 1) {
                    sw.swMsg.show({
                        icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: 'Внимание',
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) 
                                    form.doSave(selected_record);
                        }
                    })
                }  
                else {
                    form.doSave(selected_record);
                }
            } 
                
        },    
        /*  	
	provideEvnRecept_old: function() {
		var current_window = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();
		var m_store = sw.Promed.MedStaffFactByUser.store;

		if (selected_record) {
                    if (getGlobalOptions().region.nick != 'ufa') {
                        		if (selected_record.get('Drug_rlsid') <= 0 && selected_record.get('DrugComplexMnn_id') <= 0) {
				Ext.Msg.alert('Сообщение', 'Обеспечение рецепта невозможно. Отсутствует информация о медикаменте.');
				return false;
			}
			if (selected_record.get('ReceptDelayType_id') == 2 && selected_record.get('OrgFarmacy_oid') > 0 && selected_record.get('OrgFarmacy_oid') != getGlobalOptions().OrgFarmacy_id) {
				Ext.Msg.alert('Сообщение', 'Обеспечение рецепта невозможно. Аптека отсрочки не равна аптеке обращения.');
				return false;
			}
                    }
		params.onHide = function() {
				var index = grid.getStore().indexOf(selected_record);
				grid.focus();
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
			};			
			params.Contragent_id = getGlobalOptions().Contragent_id;
			params.EvnRecept_id = selected_record.get('EvnRecept_id');
			params.EvnReceptGeneral_id = selected_record.get('EvnReceptGeneral_id');
			params.Drug_id = selected_record.get('Drug_rlsid');
			params.Drug_Name = selected_record.get('Drug_Name');
			params.DrugNomen_Code = selected_record.get('DrugNomen_Code');
			params.EvnRecept_Kolvo = selected_record.get('EvnRecept_Kolvo');
			params.EvnRecept_setDate =  selected_record.get('EvnRecept_setDate');
			params.callback = function() {
				current_window.doSearch();
			}
			params.MedService_id = null;
			if (m_store) {
				var idx = m_store.findBy(function(rec) { return rec.get('MedServiceType_SysNick') == 'dpoint'; });
				if (idx >= 0) {
					params.MedService_id = m_store.getAt(idx).get('MedService_id');
				}
			}

			//проверка рецепта по сроку годности
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						switch(response_obj) {
							case 'true':
								//предварительная проверка наличия медикамента на остатках
								Ext.Ajax.request({
									callback: function(options, success, response) {
										if (success) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											var need = params.EvnRecept_Kolvo;
											var  Kolvo = 0;
											for(var i = 0; i < response_obj.length; i++) {
												need -= response_obj[i].DrugOstatRegistry_Kolvo;
												Kolvo += parseFloat(response_obj[i].DrugOstatRegistry_Kolvo);
												if (need <= 0) {
													break;
												}
											}
											console.log('params0 = '); console.log(params);
											//if (need <= 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
											if (Kolvo > 0) { //если остатков достаточно для обеспечения медикаментов - открываем форму для обеспечения
												getWnd('swEvnReceptRlsProvideWindow').show(params);
											} else { // иначе ставим на отсрочку
												if (selected_record.get('ReceptDelayType_id') != 2) { //Если рецепт еще не отсрочке
													console.log('params1 = '); console.log(params);
													current_window.putEvnReceptOnDelay({msg: 'На остатках недостаточно медикамента. Поставить рецепт на отсрочку?', 
													params: params});
												} else {
													sw.swMsg.alert('Ошибка', 'На остатках недостаточно медикамента для обеспечения рецепта');
												}
											}
										} else {
											sw.swMsg.alert('Ошибка', 'При проверке наличия медикамента на остатках возникли ошибки');
										}
									},
									params: {
										EvnRecept_id: params.EvnRecept_id,
										EvnReceptGeneral_id: params.EvnReceptGeneral_id,
										MedService_id: params.MedService_id
									},
									url: '/?c=Farmacy&m=getDrugOstatForProvide'
								});
								break;
							case 'false':
								sw.swMsg.alert('Ошибка', 'Рецепт не может быть обеспечен, так как срок его действия истек');
								break;
							case 'error':
								sw.swMsg.alert('Ошибка', 'При проверке срока годности рецепта возникли ошибки');
								break;
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При проверке срока годности рецепта возникли ошибки');
					}
				},
				params: {
					EvnRecept_id: params.EvnRecept_id,
					EvnReceptGeneral_id: params.EvnReceptGeneral_id,
					Date: Ext.util.Format.date(new Date(), 'd.m.Y')
				},
				url: '/?c=EvnRecept&m=checkReceptValidByDate'
			});
		}
	},
	*/
	
	putEvnReceptOnDelay: function() { // Постановка рецепта на отсрочку
		var current_window = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();
		var selected_record = grid.getSelectionModel().getSelected();
		var evn_recept_id = 0;
		
		var msg = 'Рецепт попадает в разряд отсроченных. Продолжить?';
		var flag_refresh = 0;

		if (arguments[0] && arguments[0].msg && arguments[0].msg != '') {
			msg = arguments[0].msg;
		}

 		if (arguments[0] && arguments[0].params != undefined) {
			params = arguments[0].params;
			flag_refresh = 1
		}

		if (selected_record) {
			evn_recept_id = selected_record.get('EvnRecept_id');			
			params.EvnRecept_obrDate = '01.01.1900';
			params.EvnRecept_id = evn_recept_id;
			params.EvnReceptGeneral_id = selected_record.get('EvnReceptGeneral_id');
			params.receptNotification_phone = selected_record.get('receptNotification_phone');
			
			sw.swMsg.buttonText.refresh = "Продолжить";
			sw.swMsg.show({
				buttons:  { yes: !(selected_record.get('WhsDocumentCostItemType_id') == 103 && selected_record.get('ReceptDelayType_id') == 2), no: true, refresh: flag_refresh == 1},
				fn: function(buttonId, text, obj) {
				// alert('buttonId = ' + buttonId); return false;
					if ( buttonId == 'yes' ) {
					    this.swEvnReceptNotificationEditWindow()
					    
					    /*
						var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, выполняется постановка рецепта на отсрочку..." });
						loadMask.show();
						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
										sw.swMsg.alert('Ошибка', response_obj.Error_Msg, function() { this.hide(); }.createDelegate(this) );
										return false;
									}
									console.log('params2 = '); console.log(params);
									current_window.doSearch();
									//sw.swMsg.alert('Сообщение', 'Рецепт был успешно поставлен на отсрочку', function() { this.doReset(); }.createDelegate(this) );
									current_window.putEvnReceptOnDelay({msg: 'На остатках недостаточно медикамента. Поставить рецепт на отсрочку?', 
										params: params});
								} else {
									sw.swMsg.alert('Ошибка', 'Ошибка при постановке рецепта на отсрочку');
								}
							}.createDelegate(this),
							params: params,
							url: '/?c=Farmacy&m=putEvnReceptOnDelay'
						});
						*/
					       
					} else if ( buttonId == 'refresh' ) {
						params.selection = 1;
						//console.log('params = '); console.log(params);
						getWnd('swEvnReceptRlsProvideWindow').show(params);
					} else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: msg,
				title: 'Подтверждение'
			});
		}
		
		return true;
	},	
    
		doSearch: function(mode) {
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
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 100;
		params.start = 0;
		params.SearchFormType = this.SearchFormType;
        //params.WithDrugComplexMnn = 1;
		params.PersonCardStateType_id = 1;
		params.PersonPeriodicType_id = 1;
		params.PrivilegeStateType_id = 1;
		params.EvnRecept_setDate_Range = params.begDate + ' - ' + params.endDate;
		params.OrgFarmacyIndex_OrgFarmacy_id = getGlobalOptions().OrgFarmacy_id && !params.AllFarmacy ? getGlobalOptions().OrgFarmacy_id : null;
		params.ReceptDelayType_id = Ext.getCmp('Pr_ReceptDelayType').getValue(); 
		params.ReceptDateType_id = Ext.getCmp('Pr_DateType').getValue();
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
	},
	checkSignMenuAvailable: function() {
		var form = this;

		form.signMenu.items.items[0].disable(); // ЭЦП рецепта
		form.signMenu.items.items[0].menu.items.items[0].disable(); // Список версий рецепта
		form.signMenu.items.items[1].disable(); // ЭЦП обеспечения
		form.signMenu.items.items[1].menu.items.items[0].disable(); // Подписание обеспечения рецепта
		form.signMenu.items.items[1].menu.items.items[1].disable(); // Список версий обеспечения рецепта
		form.signMenu.items.items[1].menu.items.items[2].disable(); // Верификация обеспечения рецепта

		var record = form.GridPanel.getGrid().getSelectionModel().getSelected();
		if (record && record.get('EvnRecept_id')) {
			if (!Ext.isEmpty(record.get('EvnRecept_IsSigned'))) {
				form.signMenu.items.items[0].enable(); // ЭЦП рецепта
				form.signMenu.items.items[0].menu.items.items[0].enable(); // Список версий рецепта
			}

			if (!Ext.isEmpty(record.get('EvnRecept_otpDT'))) {
				form.signMenu.items.items[1].enable(); // ЭЦП обеспечения
				if (!Ext.isEmpty(record.get('EvnRecept_IsOtvSigned'))) {
					form.signMenu.items.items[1].menu.items.items[1].enable(); // Список версий обеспечения рецепта
					form.signMenu.items.items[1].menu.items.items[2].enable(); // Верификация обеспечения рецепта
					if (record.get('EvnRecept_IsOtvSigned') == 1) { // не актуальные точно можно подписывать
						form.signMenu.items.items[1].menu.items.items[0].enable(); // Подписание обеспечения рецепта
					}
				} else {
					form.signMenu.items.items[1].menu.items.items[0].enable(); // Подписание обеспечения рецепта
				}
			}
		}
	},
	initComponent: function() {
		var form = this;
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form, 
			filter: {
				title: 'Фильтр',
				layout: 'form',				
				items: [{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							name: 'ReceptType',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'code',
									'name'
								],
								data: [
									['lgot', 'Льготные'],
									['general', 'Общие']
								]
							}),
							displayField: 'name',
							valueField: 'code',
							editable: false,
							allowBlank: false,
							mode: 'local',
							forceSelection: true,
							triggerAction: 'all',
							fieldLabel: 'Тип рецепта',
							width:  300,
							value: 'lgot',
							selectOnFocus: true,
							listeners: {
								select: function(combo, store, index) {
									form.setReceptType();
								}
							}
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							name:'AllFarmacy',
							hiddenName:'AllFarmacy',
							fieldLabel:'Все аптеки',
							xtype: 'checkbox'
						}]
					}, {
						layout: 'form',
						style: "padding-left: 110px",
						items:[{
							name: 'Pr_ReceptDelayType',
							id: 'Pr_ReceptDelayType',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'ReceptDelayType_id',
									'ReceptDelayType_Name'
								],
								data: [
									['-1', 'Все'],
									['0', 'Не обслуженные'],
									['1', 'Обслуженные'],
									['2', 'Отложенные'],
									['3', 'Отказ']
								]
							}),
							displayField: 'ReceptDelayType_Name',
							valueField: 'ReceptDelayType_id',
							hiddenName:  'ReceptDelayType_id',
							editable: false,
							allowBlank: false,
							mode: 'local',
							forceSelection: true,
							triggerAction: 'all',
							fieldLabel: 'Статус рецептов',
							width:  120,
							value: '0',
							selectOnFocus: true,
							listeners: {
								select: function(combo, store, index) {
									if (combo.getValue() != 1 ) {
										Ext.getCmp('Pr_DateType').setValue(1);
										Ext.getCmp('Pr_DateType').disable();
									} else {
										Ext.getCmp('Pr_DateType').enable();
									}
                                                                            
								}
							}
						}]
					},{
						layout: 'form',
						style: "padding-left: 0px",
						items:[{
							name: 'Pr_DateType',
							id: 'Pr_DateType',
							xtype:'combo',
							store: new Ext.data.SimpleStore({
								id: 0,
								fields: [
									'ReceptDateType_id',
									'ReceptDateType_Name'
								],
								data: [
									['1', 'Выписки'],
									['2', 'Обеспечения']
								]
							}),
							displayField: 'ReceptDateType_Name',
							valueField: 'ReceptDateType_id',
							hiddenName:  'ReceptDateType_id',
							editable: false,
							disabled: true,
							mode: 'local',
							forceSelection: true,
							triggerAction: 'all',
							fieldLabel: 'Фильтр по дате',
							width:  120,
							value: '1',
							selectOnFocus: true,
							listeners: {
								select: function(combo, store, index) {
									form.setReceptType();
								}
							}
						}]
					   }
					]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Person_Surname',
							fieldLabel: 'Фамилия',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Person_Firname',
							fieldLabel: 'Имя',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Person_Secname',
							fieldLabel: 'Отчество',
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Person_Birthday',
							width: 120,
							fieldLabel: 'Дата рождения',
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							fieldLabel: 'Серия',
							enableKeyEvents: true,
							name: 'EvnRecept_Ser',
							width: 120,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
						}]
					}, {
						layout: 'form',
						items:[{
							fieldLabel: 'Номер',
							enableKeyEvents: true,
							name: 'EvnRecept_Num',
							width: 120,
							maskRe: /[^%]/,
							xtype: "textfield",
							listeners: {'keydown': form.onKeyDown}
						}]
					}]
				}, {
					layout: 'column',
					items:[
						{
							layout: 'form',
							id: 'Farm_Lpu4FarmStorageForm',
							 hidden: false,
							items: [{
									hiddenName: 'Lpu_id',
									fieldLabel: 'МО',
									//value: '35',
									id: 'SEW_Lpu4FarmStorage',
									autoload: false,
									allowBlank: true,
									width: 225,
									tabIndex: TABINDEX_EVNRECSF + 63,
									xtype: 'amm_Lpu4FarmStorageCombo' // amm_Lpu4FarmStorageCombo
							}]
						},{
						layout: 'form',
						labelWidth: 115,
						hidden: true,
						items:[{
							codeField: 'MedPersonal_Code',
							editable: false,
							displayField: 'MedPersonal_Fio',
							fieldLabel: 'Врач',
							hiddenName: 'ER_MedPersonal_id',
							hideTrigger: false,
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'MedPersonal_id'
								}, [
									{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
									{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
									{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
								]),
								url: C_MP_DLO_LOADLIST
							}),
							tabIndex: TABINDEX_EVNRECSF + 64,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'MedPersonal_id',
							width: 228,
							xtype: 'swbaselocalcombo',
							listeners: {'keydown': form.onKeyDown}
						}]
					}
                                            
				]
				},{
					layout: 'column',
					items:[{
						layout: 'form',
						items:[{
							allowBlank: true,
							fieldLabel: 'Медикамент',
							listeners:{'keydown': form.onKeyDown},
							listWidth: 800,
							loadingText: 'Идет поиск...',
							minLengthText: 'Поле должно быть заполнено',
							onTrigger2Click: Ext.emptyFn,
							tabIndex: TABINDEX_EVNRECSF + 74,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 592,
							xtype: 'swdrugcombo'
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function()	{form.doSearch();}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: 'Сброс',
							iconCls: 'clear16',
							handler: function() {form.doReset();}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Считать с карты',
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'wpdpWorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			actionDeleteDefaulText: 'Поставить на отсрочку',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_edit',
					text: 'Обеспечить',
					tooltip: 'Обеспечить',
					icon: 'img/icons/receipt-otov16.png',
					handler: function () {
						form.provideEvnRecept('action_edit')
					}.createDelegate(form)
				},
				{
					name: 'action_delete',
					text: 'Поставить на отсрочку',					
					tooltip: 'Отсроченные рецепты', 
					defaultText: 'Поставить на отсрочку',
					icon: 'img/icons/receipt-ondelay16.png',
					handler: function () {
						var grid =  Ext.getCmp('wpdpWorkPlaceGridPanel');
						if (grid.getAction('action_delete').initialConfig.text ==  grid.actionDeleteDefaulText) { 
						    //Поставить на отсрочку
						    form.putEvnReceptOnDelay()
						} else {
						    form.provideEvnRecept('action_delete')
						}
					}.createDelegate(form) /*hidden: true, disabled: true*/
				},
				{
					name: 'action_view', handler: function () {
					this.openEvnReceptViewWindow();
				}.createDelegate(this)
				},
				{name: 'action_refresh'},
				{name: 'action_print'}
				,{name: 'action_save', hidden: true, handler: function(o) {
					switch(o.field) {
						case 'receptNotification_phone':
							this.saveReceptNotification_phone(o.record);
							
							break;
					}
				}.createDelegate(this)}
			],
			onBeforeEdit: function(cell) {
				if (cell.field == 'receptNotification_phone')// && !Ext.isEmpty(cell.record.get('receptNotification_setDate'))) 
				{
					return cell.record.get('ReceptDelayType_id') == 2;
				}},
			autoLoadData: false,
			paging: true,
			pageSize: 100,
			stringfields: [
				// Поля для отображение в гриде
				{name: 'EvnRecept_id', type: 'int', header: 'ID', key: true},
				{name: 'Signatures_id', hidden: true},
				{name: 'EvnReceptGeneral_id', hidden: true},
				{name: 'Drug_id', hidden: true},
				{name: 'Drug_rlsid', hidden: true},
				{name: 'DrugComplexMnn_id', hidden: true},
				{name: 'Person_id', hidden: true},
				{name: 'Server_id', hidden: true},
				{name: 'PersonEvn_id', hidden: true},
				{name: 'OrgFarmacy_oid', hidden: true},
				{name: 'EvnRecept_signDT', hidden: true},
				{name: 'ERSignPmUser_Name', hidden: true},
				{
					name: 'EvnRecept_IsSigned', renderer: function (v, p, r) {
					if (Ext.isEmpty(r.get('EvnRecept_id'))) {
						return '';
					}
					var val = '<span style="color: #000;">Не подписан</span>';
					if (!Ext.isEmpty(v)) {
						switch (parseInt(v)) {
							case 1:
								val = '<span style="color: #800;">Не актуален</span>';
								break;
							case 2:
								val = '<span style="color: #080;">Подписан' + ' (' + r.get('EvnRecept_signDT') + ' ' + r.get('ERSignPmUser_Name') + ')</span>';
								break;
						}
					}
					return val;
				}, header: 'ЭЦП врача', width: 200,
				},
				{name: 'ReceptForm_Code', header: 'Форма рецепта', hidden: true},
				{name: 'EvnRecept_Num', header: 'Номер', width: 70},
				{name: 'EvnRecept_Ser', header: 'Серия', width: 70},
				/*
				 {name: 'Person_Surname', header: 'Фамилия'},
				 {name: 'Person_Firname', header: 'Имя'},
				 {name: 'Person_Secname', header: 'Отчество'},
				 */
				{name: 'Person_FIO', header: 'Фамилия'},
				{name: 'Person_Birthday', header: 'Дата рождения', type: 'date', width: 70, hidden: true},
				{name: 'EvnRecept_setDate', header: 'Дата выписки', type: 'date', width: 80},
				{name: 'EvnRecept_otpDT', header: ' Дата<br>обеспечения', type: 'date', width: 80},
				{name: 'EvnRecept_setDate', header: 'Дата срока рецепта', type: 'date', hidden: true},
				{name: 'EvnRecept_DateCtrl', header: 'Cрок окончания действия рецепта', type: 'date', hidden: false},
				{name: 'EvnRecept_Shelf', header: 'Превышение срока рецепта', type: 'int', hidden: true},
				{name: 'PersonPrivilege_begDate', header: 'Дата начала льготы', type: 'date', hidden: false},
				{name: 'PersonPrivilege_endDate', header: 'Дата окончания льготы', type: 'date', hidden: true},
				{name: 'PersonPrivilege4View_endDate', header: 'Дата окончания льготы', type: 'date', hidden: false},
				{name: 'ReceptDelayType_id', header: 'Статус рецепта', type: 'int', hidden: true},
				{name: 'EvnRecept_IsMnn', header: 'По<br>МНН', type: 'string', width: 40},
				{name: 'Drug_Name', header: 'Медикамент (выписано)', id: 'autoexpand', width: 150},
				{
					name: 'EvnRecept_Kolvo', header: 'Количество<br>(выписано)', width: 80, css: 'text-align: right;',
					renderer: function (v) {
						return v > 0 ? parseFloat(v).toFixed(3) : v;
					}
				},
				{name: 'Drug_NameOtp', header: 'Медикамент (выдано)', width: 150},
				{
					name: 'EvnRecept_KolvoOtp', header: 'Количество<br>(выдано)', width: 80, css: 'text-align: right;',
					renderer: function (v) {
						return v > 0 ? parseFloat(v).toFixed(3) : v;
					}
				},
				{name: 'WhsDocumentCostItemType_Name', header: 'Статья расхода', width: 100},
				{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 80},
				{name: 'MedPersonal_Fin', header: 'Врач', width: 200},
				{name: 'DrugNomen_Code', hidden: true},
				{name: 'Delay_info', header: 'Статус рецепта, Аптека обращения', width: 250},
				{name: 'EvnRecept_signotvDT', hidden: true},
				{name: 'ROSignPmUser_Name', hidden: true},
				{
					name: 'EvnRecept_IsOtvSigned', renderer: function (v, p, r) {
					if (Ext.isEmpty(r.get('EvnRecept_id'))) {
						return '';
					}
					var val = '<span style="color: #000;">Не подписан</span>';
					if (!Ext.isEmpty(v)) {
						switch (parseInt(v)) {
							case 1:
								val = '<span style="color: #800;">Не актуален</span>';
								break;
							case 2:
								val = '<span style="color: #080;">Подписан' + ' (' + r.get('EvnRecept_signotvDT') + ' ' + r.get('ROSignPmUser_Name') + ')</span>';
								break;
						}
					}
					return val;
				}, header: 'ЭЦП провизора', width: 200,
				},
				{name: 'ReceptDelayType_id', hidden: true},
				{name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkbox', width: 30, hidden: true},
				
				{
					name: 'DrugOstatRegistry_Kolvo', header: 'Остаток<br> на складе', width: 80, css: 'text-align: right;',
					renderer: function (v) {
						return (v != 0  && v != null )? parseFloat(v).toFixed(3) : '';
					}
				    }
				     ,{name: 'receptNotification_phone', header: 'Телефон', type: 'string', hidden: false, editor: new Ext.form.TextField()} //  
				     ,{name: 'receptNotification_setDate', header: 'Дата оповещения', type: 'date', hidden: false}
				     ,{name: 'DocumentUc_id', header: 'DocumentUc_id', type: 'int', hidden: true}
					 ,{name: 'WhsDocumentCostItemType_id', header: 'WhsDocumentCostItemType_id', type: 'int', hidden: true}
				
			],
			//editformclassname: 'swEvnReceptEditWindow',
			dataUrl: '/?c=EvnRecept&m=getEvnReceptList4Provider',
			//C_SEARCH,
			root: 'data',
			totalProperty: 'totalCount',
			title: 'Журнал рабочего места',
			onRowSelect: function (sm, index, record) {
				this.getAction('action_delete').setText('Поставить на отсрочку');
				if (record.get('ReceptDelayType_id') == 1) {
					this.setActionDisabled('action_edit', true);
					this.setActionDisabled('action_delete', true);
					if (record.get('ReceptDelayType_id') == 1)
						this.setActionDisabled('action_wrong', true)
					else
						this.setActionDisabled('action_wrong', false);
					this.setActionDisabled('action_pull_off_service', true);
					this.setActionDisabled('action_delete_wrong', true);
				} else {
					this.setActionDisabled('action_edit', Ext.isEmpty(record.get('EvnRecept_id')));
					if (record.get('EvnRecept_Shelf') == 1)
						this.setActionDisabled('action_wrong', true)
					else
						this.setActionDisabled('action_wrong', false)
					if (record.get('ReceptDelayType_id') == 2 && record.get('OrgFarmacy_oid') > 0) {
						this.setActionDisabled('action_delete', true);
						//this.getAction('action_delete').setText('Оповестить пациента');
					} else {
						this.setActionDisabled('action_delete', Ext.isEmpty(record.get('EvnRecept_id')));
					}
					if ((record.get('ReceptDelayType_id') == 2 || record.get('ReceptDelayType_id') == 5) && Ext.isEmpty(record.get('EvnReceptGeneral_id'))) {
						this.setActionDisabled('action_pull_off_service', false);
					} else {
						this.setActionDisabled('action_pull_off_service', true);
					}
					if ((record.get('ReceptDelayType_id') == 3 || record.get('ReceptDelayType_id') == 5) && Ext.isEmpty(record.get('EvnReceptGeneral_id'))) {
						this.setActionDisabled('action_delete_wrong', false);
					} else {
						this.setActionDisabled('action_delete_wrong', true);
					}
					if (record.get('ReceptDelayType_id') == 2 && record.get('DrugOstatRegistry_Kolvo') > 0 && record.get('receptNotification_setDate') == '') {
					    this.setActionDisabled('action_delete', false);
					    this.getAction('action_delete').setText('Оповестить пациента');
					}
					record.set('set', 1);
					record.commit();
					//this.setActionDisabled('action_delete_wrong', false);
				}

				form.checkSignMenuAvailable();
			},
			onRowDeSelect: function(sm, rowIndex, record) {
							record.set('set', 0);
							record.commit();
						},
			onLoadData: function (sm, index, record) {
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
				var view = this.getGrid().getView(),
				    store = this.getGrid().getStore(),
				    rows = view.getRows();
				    Ext.each(rows, function(row, idx) {
				    var record = store.getAt(idx);
				    if(record.get('receptNotification_phone') != '')  {
					new Ext.ToolTip({
					    html: (record.get('Person_FIO') != null) ? record.get('Person_FIO') + ' тел.' + record.get('receptNotification_phone'): '',
						    //lang['dannyie_byili_zagrujenyi_na_drugoy_veb-server_i_ne_mogut_byit_izmenenyi_ili_udalenyi'],
					    target: Ext.get(row).id
					});
                    }
                });
			}
		});

		this.GridPanel.getGrid().view = new Ext.grid.GridView(
			{
				getRowClass: function (row, index) {	
					var arrCls = [];  
					if (row.get('EvnRecept_Shelf') == 1)
						arrCls.push('x-grid-rowred');
					if (row.get('ReceptDelayType_id') == 1) {
						arrCls.push('x-grid-rowbold');
						arrCls.push('x-grid-rowgreen');
					} 
					if (row.get('ReceptDelayType_id') == 2) {
						//  Если рецепт отложен
						if (row.get('receptNotification_setDate') != '')
						// Если пациент оповещен
							if (row.get('set') == 0 || row.get('set') == undefined)   
								arrCls.push('x-grid-rowbackgreen'); 
							else {
								arrCls.push('x-grid-rowgreen');
								arrCls.push('x-grid-rowbold'); 
							}

						else if (row.get('DrugOstatRegistry_Kolvo') > 0) {
						// Если для отложенного рецепта есть препараты
							arrCls.push('x-grid-rowbold'); 
							if (row.get('set') == 0 || row.get('set') == undefined)  
								arrCls.push('x-grid-rowbackyellow');
						}
					}
					//return cls;
					return arrCls.join(' ');
				}
			});
			Ext.apply(this, {
		buttons: [{
					id: 'btn_InformationRv', //this.id+'BtnTest',
					name: 'btn_InformationRv',
					style: "margin-left: 50px",
					text: 'Регистратор выбытия',
					hidden: true,
					listeners: {
						'success': function() {
							form.getLoadMask(LOAD_WAIT).show();
							var params = {};
							params.Org_id = getGlobalOptions().org_id; 
							form.Marking = false;
							Ext.Ajax.request({
								callback: function (options, success, response) {
									form.getLoadMask().hide();
									log('response', response)
									if (response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										log('response_obj', response_obj, success)
										if (success && response_obj[0].success == true) {
											form.response_obj = response_obj;
											form.Marking = (response_obj[0].data.RV_isMDLP == 2) ? true:false;
											log('form.Marking = ', form.Marking, response_obj[0].data.RV_isMDLP);
											if (!form.Marking) {
												this.hide();
												form.buttons[1].hide();
											}
											else {
												this.show();
												log('this = ', this);
												form.buttons[1].show();
												var str = '';
												if (response_obj[0].data.timeBlock == undefined) {
													str = 'РВ заблокирован';
													form.buttons[1].hide();
												}
												else
													str = 'Время до блокировки РВ: ' + response_obj[0].data.timeBlock;
												
												this.setText(str);
											}
										} else {
											log('response_obj[0] = ', response_obj[0], response_obj[0]['Error_Msg']);
											this.setText('Регистратор выбытия не обнаружен');
											form.buttons[1].hide();
										}
									}
								}.createDelegate(this),
								url: '/?c=MDLP&m=GetInformationRv',
								params:params
							});
						}
					},	
					handler: function()	{
						this.fireEvent('success');	
					}
				}, {
						name: 'btn_RegisterMarksList',
						style: "margin-left: 50px",
						text: 'Зарегистрировать рецепты в МДЛП',
						hidden: true,
						handler: function()	{
							if (form.Marking) {
							form.getLoadMask(LOAD_WAIT).show();
							var params = {};
							params.rvRequestId = form.uuidv4();
							Ext.Ajax.request({
								callback: function (options, success, response) {
									form.getLoadMask().hide();
								},
								url: '/?c=MDLP&m=QueueUpRegisterMarksList',
								params:params
							});
							} 
						}

					}, {
					text: '-'
				}, {
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function (button, event) {
						ShowHelp(this.ownerCt.title);
					}
				}, {
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});
			  
		sw.Promed.swWorkPlaceDistributionPointWindow.superclass.initComponent.apply(this, arguments);
	}
});