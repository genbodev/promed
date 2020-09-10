/**
* swEvnPLStomSearchWindow - окно поиска талона амбулаторного пациента для стоматологии.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-12.01.2010
* @comment      Префикс для id компонентов EPLStomSW (EvnPLStomSearchWindow)
*
*
* Использует: окно редактирования талона амбулаторного пациента (swEvnPLStomEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*             окно поиска человека (swPersonSearchWindow)
*/

sw.Promed.swEvnPLStomSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') ) {
			return false;
		}

		var form = this;
		var grid = this.Wizard.EvnPLStomSearchFrame.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		var params = {
			Evn_id: record.get('EvnPLStom_id')
		};

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				form.setAnotherPersonForDocument(params);
			},
			personFirname: form.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Firname').getValue(),
			personSecname: form.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Secname').getValue(),
			personSurname: form.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Surname').getValue(),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var grid = this.Wizard.EvnPLStomSearchFrame.getGrid();

		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_pereoformlenii_dokumenta_na_drugogo_cheloveka']);
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									form.setAnotherPersonForDocument(params);
								}
							},
							msg: response_obj.Alert_Msg,
							title: lang['vopros']
						});
					}
					else {
						grid.getStore().remove(grid.getSelectionModel().getSelected());

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}

						getWnd('swPersonSearchWindow').hide();

                        var info_msg = lang['dokument_uspeshno_pereoformlen_na_drugogo_cheloveka'];
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
						sw.swMsg.alert(lang['soobschenie'], info_msg, function() {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						});
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_pereoformlenii_dokumenta_na_drugogo_cheloveka_proizoshli_oshibki']);
				}
			},
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	setEvnIsTransit: function() {
		if ( !lpuIsTransit() ) {
			return false;
		}

		var grid = this.Wizard.EvnPLStomSearchFrame.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') || grid.getSelectionModel().getSelected().get('EvnPLStom_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLStom_id'),
			Evn_IsTransit: Evn_IsTransit
		};

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['ustanovka_priznaka_perehodnyiy_sluchay_mejdu_mo'] });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
					}
					else {
						record.set('EvnPLStom_IsTransit', Evn_IsTransit);
						record.commit();
						this.Wizard.EvnPLStomSearchFrame.onRowSelect(null, null, record);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
				}
			}.createDelegate(this),
			params: params,
			url: C_SETEVNISTRANSIT
		});
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLStom: function(searchFormType, options) {
		options = options || {};
		var grid = this.getActiveViewFrame().getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
        var url = '';
        var params = [];
        var msg = '';

        if (searchFormType == 'EvnPLStom') {
            params = {Evn_id: record.get('EvnPLStom_id')};
            url = C_EVN_DEL;
            msg = lang['udalit_talon'];
        } else if (searchFormType == 'EvnVizitPLStom') {
            params = {EvnVizitPLStom_id: record.get('EvnVizitPLStom_id')};
            url = C_EVN_VIZIT_DEL;
            msg = lang['udalit_poseschenie'];
        }

		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPLStom(searchFormType, options);
					}
				}
			},
			'703': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						options.ignoreCheckEvnUslugaChange = true;
						scope.deleteEvent(event, options);
					}
				}
			},
			'809': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							params: params,
							url: '/?c=HomeVisit&m=RevertHomeVizitStatusesTAP',
							callback: function (opts, success, response) {
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(resp.Error_Msg)) {
										options.ignoreHomeVizit = true;
										scope.deleteEvnPLStom(options);
									} else {
										sw.swMsg.alert(langs('Ошибка'), resp.Error_Msg);
									}
								} else {
									sw.swMsg.alert(langs('Ошибка'), 'При измененении статусов вызовов на дом возникли ошибки');
								}
							}
						});
					}
				}
			}
		};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreCheckEvnUslugaChange) {
			params.ignoreCheckEvnUslugaChange = options.ignoreCheckEvnUslugaChange;
		}

		if (options.ignoreHomeVizit) {
			params.ignoreHomeVizit = options.ignoreHomeVizit;
		}

		var doDelete = function() {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							if (response_obj.Alert_Msg) {
								var a_params = alert[response_obj.Alert_Code];
								sw.swMsg.show({
									buttons: a_params.buttons,
									fn: function(buttonId) {
										a_params.fn(buttonId, this, params);
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									icon: Ext.MessageBox.QUESTION,
									title: lang['vopros']
								});
							} else {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_talona']);
							}
						}
						else {
							grid.getStore().remove(record);

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid, 'data');
							}
						}

						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki']);
					}
				}.createDelegate(this),
				params: params,
				url: url
			});
		}.createDelegate(this);

		if (options.ignoreQuestion) {
			doDelete();
		} else {
			this.checkEvnPlOnDelete(function () {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreQuestion = true;
							doDelete();
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: lang['vopros']
				});
			}, params.Evn_id, searchFormType);
		}
	},
	showWarningMessage: function() {
		sw.swMsg.show({
			title: 'Информация',
			msg: 'У Вас нет прав на редактирование/удаление данного случая!',
			buttons: Ext.Msg.OK,
			icon: Ext.MessageBox.WARNING,
		});
	},
	checkEvnPlOnDelete: function(cbFn, Evn_id, Evn_type) {
		var me = this;
		Ext.Ajax.request({
			url: '/?c=EvnPL&m=checkEvnPlOnDelete',
			params: {
				Evn_id: Evn_id,
				Evn_type: Evn_type
			},
			success: function (res, success) {
				if (success) {
					var response = Ext.util.JSON.decode(res.responseText);

					if ( response.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response.Error_Msg ? response.Error_Msg : 'Ошибка проверки прав');
					} else {
						if (response.canDelete) {
							cbFn();
						} else {
							me.showWarningMessage();
						}
					}
				}
			}
		});
	},
	doReset: function() {
		var form = this.findById('EPLStomSW_EvnPLSearchFilterForm');
		var base_form = form.getForm();

		base_form.reset();

		if ( base_form.findField('AttachLpu_id') != null ) {
			base_form.findField('AttachLpu_id').fireEvent('change', base_form.findField('AttachLpu_id'), 0, 1);
		}

		if ( base_form.findField('LpuRegion_id') != null ) {
			base_form.findField('LpuRegion_id').lastQuery = '';
			base_form.findField('LpuRegion_id').getStore().clearFilter();
		}

		if ( base_form.findField('PrivilegeType_id') != null ) {
			base_form.findField('PrivilegeType_id').lastQuery = '';
			base_form.findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});
		}

		if ( base_form.findField('LpuRegionType_id') != null ) {
			base_form.findField('LpuRegionType_id').getStore().clearFilter();
		}

		if ( base_form.findField('DirectClass_id') != null ) {
			base_form.findField('DirectClass_id').fireEvent('change', base_form.findField('DirectClass_id'), null, 1);
		}

		if ( base_form.findField('PersonCardStateType_id') != null ) {
			base_form.findField('PersonCardStateType_id').fireEvent('change', base_form.findField('PersonCardStateType_id'), 1, 0);
		}

		form.getForm().findField('Diag_IsNotSet').fireEvent('check', form.getForm().findField('Diag_IsNotSet'), false);

		if ( base_form.findField('PrivilegeStateType_id') != null ) {
			base_form.findField('PrivilegeStateType_id').fireEvent('change', base_form.findField('PrivilegeStateType_id'), 1, 0);
		}

		form.findById('EPLStomSW_SearchFilterTabbar').setActiveTab(0);
		form.findById('EPLStomSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('EPLStomSW_SearchFilterTabbar').getActiveTab());

		this.getActiveViewFrame().getGrid().getStore().removeAll();
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var form = this.findById('EPLStomSW_EvnPLSearchFilterForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		grid = this.getActiveViewFrame().getGrid();

		if ( form.getForm().findField('PersonPeriodicType_id').getValue() == 2 && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						thisWindow.searchInProgress = false;
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka_po_sostoyaniyu_na_moment_sluchaya_pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		if(getRegionNick().inlist(['ekb', 'penza'])){
			switch(form.getForm().findField('EvnPLStom_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
				case 1:
					post.EvnPLStom_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
					break;
				case 2:
					post.EvnPLStom_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
					break;
				default:
					post.EvnPLStom_InRegistry = 0;// все ТАП
			}
		}
		else if(post.EvnPLStom_InRegistry){
			delete post.EvnPLStom_InRegistry;
		}

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		if ( form.getForm().findField('MedStaffFactViz_id') != null ) {
			var med_personal_viz_record = form.getForm().findField('MedStaffFactViz_id').getStore().getById(form.getForm().findField('MedStaffFactViz_id').getValue());

			if ( med_personal_viz_record ) {
				post.MedPersonalViz_id = med_personal_viz_record.get('MedPersonal_id');
			}
		}

		if ( form.getForm().findField('MedStaffFactViz_sid') != null ) {
			var med_personal_viz_record = form.getForm().findField('MedStaffFactViz_sid').getStore().getById(form.getForm().findField('MedStaffFactViz_sid').getValue());

			if ( med_personal_viz_record ) {
				post.MedPersonalViz_sid = med_personal_viz_record.get('MedPersonal_sid');
			}
		}

		post.SearchFormType = this.getActiveViewFrame().object;

		if ( soc_card_id )
		{
			var post = {
				soc_card_id: soc_card_id,
				SearchFormType: post.SearchFormType
			};
		}

		post.limit = 100;
		post.start = 0;

		if (!Ext.isEmpty(post.autoLoadArchiveRecords)) {
			this.getActiveViewFrame().showArchive = true;
		} else {
			this.getActiveViewFrame().showArchive = false;
		}

		grid.getStore().baseParams = post;

		if ( form.getForm().isValid() ) {
			this.getActiveViewFrame().ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			//grid.getStore().baseParams = '';
			grid.getStore().load({
				callback: function(records, options, success) {
					thisWindow.searchInProgress = false;
					loadMask.hide();
				},
				params: post
			});
		} else {
			loadMask.hide();
			thisWindow.searchInProgress = false;
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
		}
	},
	draggable: true,
	exportRecordsToDbf: function(options) {
		var form = this.findById('EPLStomSW_EvnPLSearchFilterForm');
		var base_form = form.getForm();
		var record;

		if ( base_form.findField('MedStaffFactViz_id') ) {
			record = base_form.findField('MedStaffFactViz_id').getStore().getById(base_form.findField('MedStaffFactViz_id').getValue());

			if ( record && base_form.findField('MedPersonalViz_id') ) {
				base_form.findField('MedPersonalViz_id').setValue(record.get('MedPersonal_id'));
			}
		}

		if ( base_form.findField('MedStaffFactViz_sid') ) {
			var record = base_form.findField('MedStaffFactViz_sid').getStore().getById(base_form.findField('MedStaffFactViz_sid').getValue());

			if ( record && base_form.findField('MedPersonalViz_sid') ) {
				base_form.findField('MedPersonalViz_sid').setValue(record.get('MedPersonal_id'));
			}
		}

		if ( base_form.findField('MedPersonalViz_id') ) {
			base_form.findField('MedPersonalViz_id').setValue(0);
		}

		if ( base_form.findField('MedPersonalViz_sid') ) {
			base_form.findField('MedPersonalViz_sid').setValue(0);
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет формирование архива..." });
		loadMask.show();

		//var params = getAllFormFieldValues(form);
		if(options.filter_type == 1){//https://redmine.swan.perm.ru/issues/96310
			var params = getAllFormFieldValues(form);

			if(getRegionNick().inlist(['ekb', 'penza'])){
				switch(form.getForm().findField('EvnPLStom_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
					case 1:
						params.EvnPLStom_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
						break;
					case 2:
						params.EvnPLStom_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
						break;
					default:
						params.EvnPLStom_InRegistry = 0;// все ТАП
				}
			}
			else if(params.EvnPLStom_InRegistry){
				delete params.EvnPLStom_InRegistry;
			}
		}
		else // Если ищем без учета фильтра, то оставляем только ЛПУ и тип поиска
		{
			var params = {};
			params.lpu_id = getGlobalOptions().lpu_id;
			params.SearchFormType = this.getActiveViewFrame().object;
		}
		if ( params.PersonCardStateType_id == null ) {
			params.PersonCardStateType_id = 1;
		}

		if ( params.PrivilegeStateType_id == null ) {
			params.PrivilegeStateType_id = 1;
		}
		if (options && options.table_list && options.table_list != '') { //указываем список таблиц для экспорта
			params.table_list = options.table_list;
		}
		
		if (options && options.date_type && options.date_type != '') { //тип выгрузки данных по застрахованному
			params.date_type = options.date_type;
		}
		var atout = Ext.Ajax.timeout;
		Ext.Ajax.timeout = 1200000;
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				loadMask.hide();
				Ext.Ajax.timeout = atout;
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success ) {
						sw.swMsg.alert('Экспорт талонов', '<a target="_blank" href="' + response_obj.url + '">Скачать архив с талонами</a>');
					}
					else {
						if ( response_obj.Error_Msg ) {
							sw.swMsg.alert('Экспорт талонов', response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert('Экспорт талонов', 'При формировании архива произошли ошибки');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При формировании архива произошли ошибки');
				}
			},
			params: params,
			url: '/?c=Search&m=exportSearchResultsToDbf'
		});
	},
	getRecordsCount: function() {
		var form = this.findById('EPLStomSW_EvnPLSearchFilterForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);
		post.SearchFormType = this.getActiveViewFrame().object;

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		// Надо добавить передачу параметров по умолчанию для специфических вкладок

		var record = form.getForm().findField('MedStaffFactViz_id').getStore().getById(post.MedStaffFactViz_id);
		if ( record ) {
			post.MedPersonalViz_id = record.get('MedPersonal_id');
		}

		record = form.getForm().findField('MedStaffFactViz_sid').getStore().getById(post.MedStaffFactViz_sid);
		if ( record ) {
			post.MedPersonalViz_sid = record.get('MedPersonal_id');
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
	id: 'EvnPLStomSearchWindow',
	Wizard: {EvnPLStomSearchFrame:null, EvnVizitPLStomSearchFrame: null, Panel: null},
	getActiveViewFrame: function () 
	{
		return this.Wizard[this.Wizard.Panel.layout.activeItem.id];
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLStomSW_SearchButton');
	},
	printCost: function(ByDay) {
		var grid = this.Wizard.EvnPLStomSearchFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLStom_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPLStom_id'),
				Person_id: selected_record.get('Person_id'), //https://redmine.swan.perm.ru/issues/55589
				type: 'EvnPLStom',
				ByDay: ByDay,
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.Wizard.EvnPLStomSearchFrame.getGrid();
		var menuPrint = this.Wizard.EvnPLStomSearchFrame.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLStom_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLStom_IsFinish') != lang['da']);
			}
		}
	},
	initComponent: function() {
		var win = this;
		var hiddenToAis25 = true;
		if(getRegionNick() == 'kz' && getGlobalOptions().AisPolkaEvnPLsync
			&& getGlobalOptions().AisPolkaEvnPLsync.lpu255and259list
			&& getGlobalOptions().lpu_id.inlist(getGlobalOptions().AisPolkaEvnPLsync.lpu255and259list))
			hiddenToAis25 = false;
		var hiddenToAis259 = true;
		if(getRegionNick() == 'kz' && getGlobalOptions().AisPolkaEvnPLsync
			&& getGlobalOptions().AisPolkaEvnPLsync.lpu259list
			&& getGlobalOptions().lpu_id.inlist(getGlobalOptions().AisPolkaEvnPLsync.lpu259list))
			hiddenToAis259 = false;
		this.Wizard.EvnPLStomSearchFrame = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').deleteEvnPLStom('EvnPLStom'); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLStomSW_EvnPLStomSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print', /*handler: function() { Ext.getCmp('EvnPLStomSearchWindow').printEvnPLStom(); }*/
					menuConfig: {
						printObject: {handler: function() { Ext.getCmp('EvnPLStomSearchWindow').printEvnPLStom(); } },
						printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost(0) }},
						printCostByDay: {name: 'printCostByDay', hidden: true, text: langs('Справка о стоимости лечения за день'), handler: function () { win.printCost(1) }} //https://redmine.swan.perm.ru/issues/55589
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLStomSW_SearchButton',
				type: 'button'
			},/*
			focusOn: {
				name: 'EvnPLStom',
				type: 'grid'
			},*/
			id: 'EPLStomSW_EvnPLStomSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			object: 'EvnPLStom',
			root: 'data',
			stringfields: [
				{ name: 'EvnPLStom_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPLStom_IsTransit', type: 'int', hidden: true },
				{ name: 'EvnPLStom_NumCard', type: 'string', header: langs('№ талона'), width: 70 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 70 },
				{ name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 70 },
				{ name: 'EvnPLStom_VizitCount', type: 'int', header: langs('Посещений'), width: 80 },
				{ name: 'EvnPLStom_IsFinish', type: 'string', header: langs('Законч'), width: 70 },
				{ name: 'Diag_Name', type: 'string', header: langs('Основной диагноз'), width: 110 },
				{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), id: 'autoexpand', width: 70 },
				{ name: 'EvnPLStom_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала'), width: 90 },
				{ name: 'EvnPLStom_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания'), width: 90 },
				{ name: 'Person_IsBDZ',  header: langs('БДЗ'), type: 'checkbox', width: 30 },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'toAis25', type: 'checkcolumn', header: 'АИС Пол-ка (25-5у)', width: 150, hidden: hiddenToAis25 },
				{ name: 'toAis259', type: 'checkcolumn', header: 'АИС Пол-ка (25-9у)', width: 150, hidden: hiddenToAis259 },
				{ name: 'Person_AdrReg', header: langs('Адрес Регистрации'), type: 'string', width: 160, hidden: ! getRegionNick().inlist(['buryatiya']), hideable: true },
				{ name: 'Person_AdrProj', header: langs('Адрес Проживания'), type: 'string', width: 160, hidden: ! getRegionNick().inlist(['buryatiya']), hideable: true }
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				if(win.viewOnly == true)
				{
					this.getAction('action_add').setDisabled(true);
					this.getAction('action_edit').setDisabled(true);
					this.getAction('action_delete').setDisabled(true);
					this.setActionDisabled('action_changeperson', true);
					this.setActionDisabled('action_setevnistransit', true);
				}
				else
				{
					this.getAction('action_add').setDisabled(false);
					this.getAction('action_edit').setDisabled(false);
					this.getAction('action_delete').setDisabled(false);
					this.setActionDisabled('action_changeperson', false);
					this.setActionDisabled('action_setevnistransit', false);
					// Запретить редактирование/удаление архивных записей
					if (getGlobalOptions().archive_database_enable) {
						this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
						this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
					}

					if ( getRegionNick() == 'perm' ) {
						if ( record.get('EvnPLStom_id') ) {
							var disabled = false;
							if (getGlobalOptions().archive_database_enable) {
								disabled = disabled || (record.get('archiveRecord') == 1);
							}
							this.setActionDisabled('action_changeperson', disabled);
						}
						else {
							this.setActionDisabled('action_changeperson', true);
						}
					}

					if ( record.get('EvnPLStom_id') ) {
						this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLStom_IsTransit') == 1));
					}
					else {
						this.setActionDisabled('action_setevnistransit', true);
					}
				}

				win.checkPrintCost();
			}
		});

		this.Wizard.EvnVizitPLStomSearchFrame = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').deleteEvnPLStom('EvnVizitPLStom'); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLStomSW_EvnVizitPLStomSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').printEvnPLStom(); } }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLStomSW_SearchButton',
				type: 'button'
			},
			id: 'EPLStomSW_EvnVizitPLStomSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			object: 'EvnVizitPLStom',
			root: 'data',
			stringfields: [
				{ name: 'EvnVizitPLStom_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPLStom_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPLStom_NumCard', type: 'string', header: langs('№ талона'), width: 70 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 70 },
				{ name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 70 },
				{ name: 'Diag_Name', type: 'string', header: langs('Основной диагноз'), id: 'autoexpand' },
				{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 100 },
				{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 160 },
				{ name: 'EvnVizitPLStom_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата посещения'), width: 90 },
				{ name: 'ServiceType_Name', type: 'string', header: langs('Место обслуживания'), width: 100 },
				{ name: 'VizitType_Name', type: 'string', header: langs('Цель посещения'), width: 100 },
				{ name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 70 },
				{ name: 'EvnVizitPLStom_Uet', type: 'string', header: langs('Количество УЕТ'), width: 100 },
				{ name: 'Person_IsBDZ',  header: langs('БДЗ'), type: 'checkbox', width: 30 },
				{ name: 'Person_AdrReg', header: langs('Адрес Регистрации'), type: 'string', width: 160, hidden: ! getRegionNick().inlist(['perm'])},
				{ name: 'Person_AdrProj', header: langs('Адрес Проживания'), type: 'string', width: 160, hidden: ! getRegionNick().inlist(['perm']) }
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				// Запретить редактирование/удаление архивных записей
				if(win.viewOnly == true)
				{
					this.getAction('action_add').setDisabled(true);
					this.getAction('action_edit').setDisabled(true);
					this.getAction('action_delete').setDisabled(true);
				}
				else
				{
					this.getAction('action_add').setDisabled(false);
					this.getAction('action_edit').setDisabled(false);
					this.getAction('action_delete').setDisabled(false);
					if (getGlobalOptions().archive_database_enable) {
						this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
						this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
					}
				}
			}
		});
		
		this.Wizard.Panel = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[{
				id: 'EvnPLStomSearchFrame',
				layout:'fit',
				items:[this.Wizard.EvnPLStomSearchFrame]
			},{
				id: 'EvnVizitPLStomSearchFrame',
				layout: 'fit',
				items:[this.Wizard.EvnVizitPLStomSearchFrame]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				id: 'EPLStomSW_SearchButton',
				iconCls: 'search16',
				tabIndex: TABINDEX_EPLSTOMSW + 109,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLSTOMSW + 110,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					var base_form = this.findById('EPLStomSW_EvnPLSearchFilterForm').getForm();
					var record;

					if ( base_form.findField('MedStaffFactViz_id') ) {
						record = base_form.findField('MedStaffFactViz_id').getStore().getById(base_form.findField('MedStaffFactViz_id').getValue());

						if ( record && base_form.findField('MedPersonalViz_id') ) {
							base_form.findField('MedPersonalViz_id').setValue(record.get('MedPersonal_id'));
						}
					}

					if ( base_form.findField('MedStaffFactViz_sid') ) {
						var record = base_form.findField('MedStaffFactViz_sid').getStore().getById(base_form.findField('MedStaffFactViz_sid').getValue());

						if ( record && base_form.findField('MedPersonalViz_sid') ) {
							base_form.findField('MedPersonalViz_sid').setValue(record.get('MedPersonal_id'));
						}
					}

					base_form.submit();

					if ( base_form.findField('MedPersonalViz_id') ) {
						base_form.findField('MedPersonalViz_id').setValue(0);
					}

					if ( base_form.findField('MedPersonalViz_sid') ) {
						base_form.findField('MedPersonalViz_sid').setValue(0);
					}
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EPLSTOMSW + 111,
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLSTOMSW + 112,
				text: BTN_FRMCOUNT
			}, 
			{
				handler: function() {
					//this.exportRecordsToDbf();
					var wnd = this;
					getWnd('swEvnPLStomExportMenu').show({
						callback: function (data) {
							wnd.exportRecordsToDbf(data);
						}
					});
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSW + 112,
				hidden: !getRegionNick() == 'kareliya',
				text: lang['vyigruzit_v_dbf']
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_EPLSTOMSW + 113),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EPLStomSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EPLStomSW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSTOMSW + 114,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EPLStomSW_EvnPLSearchFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				useArchive: 1,
				allowPersonPeriodicSelect: true,
				ownerWindowWizardPanel: this.Wizard.Panel,
				id: 'EPLStomSW_EvnPLSearchFilterForm',
				ownerWindow: this,
				searchFormType: 'EvnPLStom',
				tabIndexBase: TABINDEX_EPLSTOMSW,
				tabPanelId: 'EPLStomSW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
/*
							if ( this.getFilterForm().getForm().findField('LpuSectionDiag_id').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('LpuSectionDiag_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedPersonalDiag_id').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('MedPersonalDiag_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}
*/
							this.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_diagnoz_i_uslugi'],

					// tabIndexStart: TABINDEX_EPLSTOMSW + 68
					items: [{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: lang['diagnoz'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							fieldLabel: lang['ne_ustanovlen'],
							listeners: {
								'check': function(field, value) {
									var base_form = this.getFilterForm().getForm();

									if ( value == true ) {
										base_form.findField('Diag_Code_From').clearValue();
										base_form.findField('Diag_Code_From').disable();
										base_form.findField('Diag_Code_To').clearValue();
										base_form.findField('Diag_Code_To').disable();
										base_form.findField('DeseaseType_id').clearValue();
										base_form.findField('DeseaseType_id').disable();
									}
									else {
										base_form.findField('Diag_Code_From').enable();
										base_form.findField('Diag_Code_To').enable();
										base_form.findField('DeseaseType_id').enable();
									}
								}.createDelegate(this)
							},
							name: 'Diag_IsNotSet',
							tabIndex: TABINDEX_EPLSW + 68,
							xtype: 'checkbox'
						}, {
							checkAccessRights: true,
							fieldLabel: lang['kod_diagnoza_s'],
							hiddenName: 'Diag_Code_From',
							listWidth: 620,
							tabIndex: TABINDEX_EPLSTOMSW + 68,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, {
							checkAccessRights: true,
							fieldLabel: lang['po'],
							hiddenName: 'Diag_Code_To',
							listWidth: 620,
							tabIndex: TABINDEX_EPLSTOMSW + 69,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, /*{
							hiddenName: 'LpuSectionDiag_id',
							id: 'EPLStomSW_LpuSectionDiagCombo',
							lastQuery: '',
							linkedElementId: 'EPLStomSW_MedStaffFactDiagCombo',
							tabIndex: TABINDEX_EPLSTOMSW + 70,
							width: 590,
							xtype: 'swlpusectionglobalcombo'
						}, {
							hiddenName: 'MedStaffFactDiag_id',
							id: 'EPLStomSW_MedStaffFactDiagCombo',
							lastQuery: '',
							linkedElementId: 'EPLStomSW_LpuSectionDiagCombo',
							tabIndex: TABINDEX_EPLSTOMSW + 71,
							width: 590,
							xtype: 'swmedstafffactglobalcombo'
						}, */{
							fieldLabel: lang['harakter_zabolev'],
							tabIndex: TABINDEX_EPLSTOMSW + 72,
							width: 450,
							xtype: 'swdeseasetypecombo'
						}]
					}, {
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: lang['vyipolnennaya_usluga'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							allowBlank: true,
							fieldLabel: lang['kategoriya_uslugi'],
							hiddenName: 'UslugaCategory_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index));
								}.createDelegate(this),
								'select': function (combo, record) {
									var base_form = this.getFilterForm().getForm();

									//base_form.findField('UslugaComplex_Code_From').clearValue();
									base_form.findField('UslugaComplex_Code_From').getStore().removeAll();
									//base_form.findField('UslugaComplex_Code_To').clearValue();
									base_form.findField('UslugaComplex_Code_To').getStore().removeAll();

									if ( !record ) {
										var uslugaCategoryList = [];

										combo.getStore().each(function(rec) {
											if ( !Ext.isEmpty(rec.get('UslugaCategory_SysNick')) ) {
												uslugaCategoryList.push(rec.get('UslugaCategory_SysNick'));
											}
										});

										base_form.findField('UslugaComplex_Code_From').setUslugaCategoryList(uslugaCategoryList);
										base_form.findField('UslugaComplex_Code_To').setUslugaCategoryList(uslugaCategoryList);

										return false;
									}

									base_form.findField('UslugaComplex_Code_From').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);
									base_form.findField('UslugaComplex_Code_To').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

									return true;
								}.createDelegate(this)
							},
							listWidth: 400,
							tabIndex: TABINDEX_EPLSTOMSW + 73,
							width: 250,
							xtype: 'swuslugacategorycombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['usluga_s'],
									hiddenName: 'UslugaComplex_Code_From',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPLSTOMSW + 74,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 30,
								items: [{
									fieldLabel: lang['po'],
									hiddenName: 'UslugaComplex_Code_To',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPLSTOMSW + 75,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['kod_posescheniya'],
								hiddenName: 'UslugaComplex_uid',
								listWidth: 590,
								showUslugaComplexEndDate: true,
								tabIndex: TABINDEX_EPLSTOMSW + 76,
								width: 590,
								xtype: 'swuslugacomplexnewcombo'
							}, {
								hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),// Открыто для Уфы
								hideLabel: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),
								fieldLabel: langs('Код посещения (шаблон)'),
								name: 'UslugaComplex_Code',
								tabIndex: TABINDEX_EPLSW + 76,
								width: 100,
								xtype: 'textfield'
							}]
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('LpuSectionViz_id').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('LpuSectionViz_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedStaffFactViz_id').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('MedStaffFactViz_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedStaffFactViz_sid').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('MedStaffFactViz_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							this.getFilterForm().getForm().findField('EvnPL_NumCard').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['7_poseschenie'],

					// tabIndexStart: TABINDEX_EPLSTOMSW + 77
					items: [{
						name: 'MedPersonalViz_id',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'MedPersonalViz_sid',
						value: 0,
						xtype: 'hidden'
					}, {
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								enableKeyEvents: true,
								fieldLabel: lang['№_talona'],						
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnPL_NumCard',
								tabIndex: TABINDEX_EPLSTOMSW + 77,
								width: 150,
								maskRe: /[^%]/,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 220,
							layout: 'form',
							items: [{
								fieldLabel: lang['pervichno_v_tekuschem_godu'],
								hiddenName: 'EvnVizitPLStom_IsPrimaryVizit',
								tabIndex: TABINDEX_EPLSTOMSW + 77,
								validateOnBlur: false,
								width: 65,
								xtype: 'swyesnocombo'
							}]
						}]
					}, {
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_nachala_sluchaya'],
								name: 'EvnPL_setDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLSTOMSW + 77,
								width: 200,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_okonchaniya_sluchaya'],
								name: 'EvnPL_disDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLSTOMSW + 77,
								width: 200,
								xtype: 'daterangefield'
							}]
						},
							{
								border: false,
								layout: 'form',
								items:[{
									fieldLabel:'Вид посещения',
									name: 'VizitClass',
									tabIndex: TABINDEX_EPLSTOMSW + 77,
									xtype: 'swvizitclasscombo'
								}]
							}]
					}, {
						hiddenName: 'PrehospTrauma_id',
						tabIndex: TABINDEX_EPLSTOMSW + 78,
						width: 450,
						xtype: 'swprehosptraumacombo'
					}, {
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['protivopravnaya'],
								hiddenName: 'EvnPL_IsUnlaw',
								tabIndex: TABINDEX_EPLSTOMSW + 79,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								fieldLabel: lang['netransportabelnost'],
								hiddenName: 'EvnPL_IsUnport',
								tabIndex: TABINDEX_EPLSTOMSW + 80,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}, {
							// #107998
							// Екатеринбург. Признак вхождения в реестр для ТАП
							// Пенза. Признак вхождения в реестр для одного из заболеваний
							border: false,
							layout: 'form',
							labelWidth: 150,
							hidden: ! getRegionNick().inlist(['ekb', 'penza']),
							items:[{
								name: 'EvnPLStom_InRegistry',
								fieldLabel: langs('Включение в реестр'),
								displayField: 'EvnPLStom_InRegistry_Name',
								valueField: 'EvnPLStom_InRegistry_id',
								editable: false,
								disabled: ! getRegionNick().inlist(['ekb', 'penza']),
								hidden: ! getRegionNick().inlist(['ekb', 'penza']),
								store: new Ext.data.SimpleStore({
									autoLoad: true,
									data: [
										[0, ''],// все ТАП
										[2, langs('Да')],// да: ekb - признак вхождения в реестр, penza - одно из заболеваний в реестре
										[1, langs('Нет')]// нет: ekb - не установлен признак вхождения в реестр, penza - ни одно из заболеваний в реестре
									],
									fields: [
										{name: 'EvnPLStom_InRegistry_id', type: 'int'},
										{name: 'EvnPLStom_InRegistry_Name', type: 'string'}
									],
									key: 'EvnPLStom_InRegistry_id'
								}),
								width: 72,
								listWidth: 70,
								tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">&nbsp;{EvnPLStom_InRegistry_Name}</div></tpl>'),
								xtype: 'swbaselocalcombo'
							}]
						}]
					}, {
						hiddenName: 'LpuSectionViz_id',
						id: 'EPLStomSW_LpuSectionVizCombo',
						lastQuery: '',
						linkedElements: [
							'EPLStomSW_MedStaffFactVizCombo',
							'EPLStomSW_MidMedStaffFactVizCombo'
						],
						listWidth: 700,
						tabIndex: TABINDEX_EPLSTOMSW + 81,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						hiddenName: 'MedStaffFactViz_id',
						id: 'EPLStomSW_MedStaffFactVizCombo',
						lastQuery: '',
						parentElementId: 'EPLStomSW_LpuSectionVizCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EPLSTOMSW + 82,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						fieldLabel: lang['sredniy_m_pers'],
						hiddenName: 'MedStaffFactViz_sid',
						id: 'EPLStomSW_MidMedStaffFactVizCombo',
						lastQuery: '',
						listWidth: 700,
						parentElementId: 'EPLStomSW_LpuSectionVizCombo',
						tabIndex: TABINDEX_EPLSTOMSW + 83,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								hiddenName: 'ServiceType_id',
								fieldLabel: lang['mesto_obsluj-ya'],
								tabIndex: TABINDEX_EPLSTOMSW + 84,
								width: 200,
								xtype: 'swservicetypecombo'
							}, {
								useCommonFilter: true,
								tabIndex: TABINDEX_EPLSTOMSW + 86,
								width: 200,
								xtype: 'swpaytypecombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								EvnClass_id: 13,
								tabIndex: TABINDEX_EPLSTOMSW + 85,
								width: 200,
								xtype: 'swvizittypecombo'
							}, {
								fieldLabel: lang['data_posescheniya'],
								name: 'Vizit_Date_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPLSTOMSW + 87,
								width: 200,
								xtype: 'daterangefield'
							}]
						}]
					}, {
						tabIndex: TABINDEX_EPLSTOMSW + 88,
						fieldLabel: lang['sluchay_oplachen'],
						hiddenName: 'EvnVizitPLStom_isPaid',
						xtype: 'swyesnocombo'
					},{
						tabIndex: TABINDEX_EPLSW + 88,
						fieldLabel: 'Вид обращения',
						comboSubject: 'TreatmentClass',
						hiddenName: 'TreatmentClass_id',
						width: 200,
						xtype: 'swcommonsprcombo'
					}, {
						border: false,
						layout: 'form',
						hidden : !getRegionNick().inlist(['perm','vologda']),
						items: [{
							fieldLabel: 'КСГ',
							hiddenName: 'EvnPLStom_KSG',
							tabIndex: TABINDEX_EPLSTOMSW + 77,
							validateOnBlur: false,
							width: 65,
							xtype: 'swyesnocombo',
							disabled: false,
						},{
							enableKeyEvents: true,
							fieldLabel: "№ КСГ",
							id : 'EvnPLStom_KSG_Num',
							listeners: {
								'keyup': function (inp, e) {
									var node = $('#EvnPLStom_KSG_Num');
									if(node.val() > 30) {
										node.val(30);

										node.hint({
											title : '',
											text  : 'Максимальное значение номера "30"',
											delay : 2000
										});
									}
								}.createDelegate(this)
							},
							name: 'EvnPLStom_KSG_Num',
							tabIndex: TABINDEX_EPLSTOMSW + 77,
							width: 65,
							maskRe:  /\d/,
							xtype: 'textfield',
							maxLength: 2,
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('LpuSection_oid').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									isStom: true,
									regionCode: getGlobalOptions().region.number
								});
								this.getFilterForm().getForm().findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							this.getFilterForm().getForm().findField('EvnPL_IsFinish').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['8_rezultatyi'],

					// tabIndexStart: TABINDEX_EPLSTOMSW + 89
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPL_IsFinish',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
										}
									}
								},
								tabIndex: TABINDEX_EPLSTOMSW + 89,
								width: 200,
								xtype: 'swyesnocombo'
							}, {
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var record = combo.getStore().getById(newValue);

										var lpu_combo = this.getFilterForm().getForm().findField('Lpu_oid');
										var lpu_section_combo = this.getFilterForm().getForm().findField('LpuSection_oid');

										if ( !lpu_combo.rendered || !lpu_section_combo.rendered ) {
											return false;
										}

										lpu_combo.clearValue();
										lpu_section_combo.clearValue();

										if ( !record ) {
											lpu_combo.disable();
											lpu_section_combo.disable();
											return false;
										}

										if ( record.get('DirectClass_Code') == 1 ) {
											lpu_combo.disable();
											lpu_section_combo.enable();
										}
										else if ( record.get('DirectClass_Code') == 2 ) {
											lpu_combo.enable();
											lpu_section_combo.disable();
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EPLSTOMSW + 91,
								width: 200,
								xtype: 'swdirectclasscombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								tabIndex: TABINDEX_EPLSTOMSW + 90,
								width: 200,
								xtype: 'swresultclasscombo'
							}, {
								tabIndex: TABINDEX_EPLSTOMSW + 92,
								width: 200,
								xtype: 'swdirecttypecombo'
							}]
						}]
					}, {
						disabled: true,
						fieldLabel: lang['otdelenie_lpu'],
						hiddenName: 'LpuSection_oid',
						lastQuery: '',
						tabIndex: TABINDEX_EPLSTOMSW + 93,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						disabled: true,
						displayField: 'Lpu_Nick',
						fieldLabel: lang['drugoe_lpu'],
						hiddenName: 'Lpu_oid',
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Lpu_Nick', type: 'string' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Lpu_Nick'
							},
							tableName: 'Lpu'
						}),
						tabIndex: TABINDEX_EPLSTOMSW + 94,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						valueField: 'Lpu_id',
						width: 450,
						xtype: 'swbaselocalcombo'
					}, {
						comboSubject: 'InterruptLeaveType',
						fieldLabel: 'Случай прерван',
						hiddenName: 'InterruptLeaveType_id',
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						autoHeight: true,
						labelWidth: 120,
						style: 'padding: 0px;',
						title: lang['vyipiska_lista_netrudosposobnosti'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Открыт',
									name: 'EvnStick_begDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPLSTOMSW + 95,
									width: 200,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Закрыт',
									name: 'EvnStick_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPLSTOMSW + 96,
									width: 200,
									xtype: 'daterangefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									tabIndex: TABINDEX_EPLSTOMSW + 97,
									width: 200,
									xtype: 'swsticktypecombo'
								}, {
									tabIndex: TABINDEX_EPLSTOMSW + 98,
									width: 200,
									xtype: 'swstickcausecombo'
								}]
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Случай передан в<br>АИС-Пол-ка (25-5у)',
								hiddenName: 'toAis25',
								tabIndex: TABINDEX_EPLSTOMSW + 98,
								width: 200,
								hidden:hiddenToAis25,
								hideLabel: hiddenToAis25,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Случай передан в<br>АИС-Пол-ка (25-9у)',
								hiddenName: 'toAis259',
								tabIndex: TABINDEX_EPLSTOMSW + 98,
								width: 200,
								hidden:hiddenToAis259,
								hideLabel: hiddenToAis259,
								xtype: 'swyesnocombo'
							}]
						}]
					}]
				},{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('PL_LpuSection_id').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter();
								this.getFilterForm().getForm().findField('PL_LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							//this.getFilterForm().getForm().findField('EvnPL_IsFinish').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['9_napravlenie'],

					// tabIndexStart: TABINDEX_EPLSW + 91
					items: [{
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
						autoHeight: true,
						labelWidth: 170,
						style: 'padding: 0px;',
						title: lang['napravlenie'],
						width: 755,
						xtype: 'fieldset',

						items: [{
								xtype: 'checkbox',
								id: 'PL_ElDirection',
								labelSeparator: '',
								boxLabel: lang['bez_elektronnogo_napravleniya']
							},{
								comboSubject: 'PrehospDirect',
								typeCode: 'int',
								fieldLabel: lang['kem_napravlen'],
								hiddenName: 'PL_PrehospDirect_id',
								lastQuery: '',
								listeners:{
									'change':function(c,n,o){
										var LSf = true;
										var Orgf = true;
										var Diagf = true;
										if(n){										
											switch(n){
												case 1:
													LSf = false;
												break;
												case 2:
												case 3:
												case 4:
												case 5:
												case 6:
													Orgf = false;
												break;
												case 7:
													Diagf = false;
												break;
											default:
												break;
											}
										}
											this.getFilterForm().getForm().findField('PL_Org_id').setDisabled(Orgf);
											this.getFilterForm().getForm().findField('PL_LpuSection_id').setDisabled(LSf);
											this.getFilterForm().getForm().findField('PL_Diag_id').setDisabled(Diagf);
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EEPLEF + 6,
								width: 200,
								xtype: 'swcommonsprcombo'
							},{
								fieldLabel: lang['№_napravleniya'],
								name: 'PL_NumDirection',
								tabIndex: TABINDEX_EPLSW + 76,
								width: 200,
								xtype: 'textfield'
							},{
								fieldLabel: lang['data_napravleniya'],
								format: 'd.m.Y',
								name: 'PL_DirectionDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								tabIndex: TABINDEX_EDHEF + 14,
								width: 100,
								xtype: 'swdatefield'
							},{
								disabled: true,
								fieldLabel: lang['otdelenie'],
								hiddenName: 'PL_LpuSection_id',
								lastQuery: '',
								tabIndex: TABINDEX_EPLSW + 95,
								width: 450,
								xtype: 'swlpusectionglobalcombo'
							},{
								displayField: 'Org_Name',
								editable: false,
								enableKeyEvents: true,
								fieldLabel: lang['organizatsiya'],
								hiddenName: 'PL_Org_id',
								listeners: {
									'keydown': function( inp, e ) {
										if ( inp.disabled )
											return;

										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											inp.onTrigger1Click();
											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() ) {
											if ( e.browserEvent.stopPropagation )
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if ( e.browserEvent.preventDefault )
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									}
								},
								mode: 'local',
								onTrigger1Click: function() {
									var base_form = this.getFilterForm().getForm();
									var combo = base_form.findField('PL_Org_id');

									if ( combo.disabled ) {
										return false;
									}

									var prehosp_direct_combo = base_form.findField('PL_PrehospDirect_id');
									var prehosp_direct_id = prehosp_direct_combo.getValue();
									var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

									if ( !record ) {
										return false;
									}

									var prehosp_direct_code = record.get('PrehospDirect_Code');
									var org_type = '';

									switch ( prehosp_direct_code ) {
										case 2:
											org_type = 'lpu';
										break;

										case 4:
											org_type = 'military';
										break;

										case 3:
										case 5:
										case 6:
											org_type = 'org';
										break;

										default:
											return false;
										break;
									}

									getWnd('swOrgSearchWindow').show({
										object: org_type,
										onClose: function() {
											combo.focus(true, 200)
										},
										onSelect: function(org_data) {
											if ( org_data.Org_id > 0 ) {
												combo.getStore().loadData([{
													Org_id: org_data.Org_id,
													Lpu_id: org_data.Lpu_id,
													Org_Name: org_data.Org_Name
												}]);
												combo.setValue(org_data.Org_id);
												getWnd('swOrgSearchWindow').hide();

												combo.collapse();
											}
										}
									});
								}.createDelegate(this),
								store: new Ext.data.JsonStore({
									autoLoad: false,
									fields: [
										{name: 'Org_id', type: 'int'},
										{name: 'Lpu_id', type: 'int'},
										{name: 'Org_Name', type: 'string'}
									],
									key: 'Org_id',
									sortInfo: {
										field: 'Org_Name'
									},
									url: C_ORG_LIST
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{Org_Name}',
									'</div></tpl>'
								),
								trigger1Class: 'x-form-search-trigger',
								triggerAction: 'none',
								valueField: 'Org_id',
								width: 450,
								disabled: true,
								xtype: 'swbaseremotecombo'
							},{
								checkAccessRights: true,
								disabled: true,
								hiddenName: 'PL_Diag_id',
								id: 'PL_DiagCombo',
								fieldLabel: lang['diagnoz_napr_uchrejdeniya'],
								tabIndex: TABINDEX_EEPLEF + 35,
								width: 450,
								xtype: 'swdiagcombo'
							}]
						}]
							
							}
						]
					}]
				}]
			}),
			this.Wizard.Panel
			/*
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('add'); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').openEvnPLStomEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').deleteEvnPLStom(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLStomSW_EvnPLStomSearchGrid').ViewGridPanel.getStore().reload(); } },
					{ name: 'action_print', handler: function() { Ext.getCmp('EvnPLStomSearchWindow').printEvnPLStom(); } }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EvnPLStom',
					type: 'grid'
				},
				id: 'EPLStomSW_EvnPLStomSearchGrid',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'EvnPLStom_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'EvnPLStom_NumCard', type: 'string', header: lang['№_talona'], width: 70 },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 70 },
					{ name: 'EvnPLStom_VizitCount', type: 'int', header: lang['posescheniy'], width: 80 },
					{ name: 'EvnPLStom_IsFinish', type: 'string', header: lang['zakonch'], width: 70 },
					{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], id: 'autoexpand' },
					{ name: 'EvnPLStom_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'], width: 90 },
					{ name: 'EvnPLStom_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'], width: 90 },
					{ name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30 }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})*/]
		});

		sw.Promed.swEvnPLStomSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLStomSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					current_window.openEvnPLStomEditWindow('add');
					break;
			}
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLStomSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLStomSW_SearchFilterTabbar');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doReset();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					search_filter_tabbar.setActiveTab(6);
				break;

				case Ext.EventObject.NUM_EIGHT:
				case Ext.EventObject.EIGHT:
					search_filter_tabbar.setActiveTab(7);
				break;

				case Ext.EventObject.NUM_NINE:
				case Ext.EventObject.NINE:
					search_filter_tabbar.setActiveTab(8);
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.EIGHT,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NINE,
			Ext.EventObject.NUM_EIGHT,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_NINE,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SEVEN,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SEVEN,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('EPLStomSW_EvnPLSearchFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EPLStomSW_EvnPLSearchFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('EPLStomSW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('EPLStomSW_EvnPLSearchFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLStomEditWindow: function(action) {
		var win = this;

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPLStomEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_ambulatornogo_patsienta_uje_otkryito']);
			return false;
		}

		var params = {};
		var grid = this.getActiveViewFrame().getGrid();

		params.action = action;
		params.streamInput = true;
		// params.callback только при поиске талонов!!! т.к. при поиске посещений будет другой грид - с посещениями!
		if ('EvnPLStom' != this.getActiveViewFrame().object)
		{
			params.callback = Ext.emptyFn;
		}
		else
		{
			params.callback = function(data) {
				if ( !data || !data.evnPLStomData ) {
					return false;
				}

				// Обновить запись в grid
				var index = grid.getStore().findBy(function(rec) {
					return (rec.get('EvnPLStom_id') == data.evnPLStomData.EvnPLStom_id);
				});
				var record = grid.getStore().getAt(index);

				if ( typeof record == 'object' ) {
					if ( data.evnPLStomData.lastEvnDeleted == true ) {
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}
					}
					else {
						record.set('Diag_Name', data.evnPLStomData.Diag_Name);
						record.set('EvnPLStom_disDate', data.evnPLStomData.EvnPLStom_disDate);
						record.set('EvnPLStom_id', data.evnPLStomData.EvnPLStom_id);
						record.set('EvnPLStom_IsFinish', data.evnPLStomData.EvnPLStom_IsFinish);
						record.set('EvnPLStom_NumCard', data.evnPLStomData.EvnPLStom_NumCard);
						record.set('EvnPLStom_setDate', data.evnPLStomData.EvnPLStom_setDate);
						record.set('EvnPLStom_VizitCount', data.evnPLStomData.EvnPLStom_VizitCount);
						record.set('MedPersonal_Fio', data.evnPLStomData.MedPersonal_Fio);
						record.set('Person_Birthday', data.evnPLStomData.Person_Birthday);
						record.set('Person_Surname', data.evnPLStomData.Person_Surname);
						record.set('Person_Firname', data.evnPLStomData.Person_Firname);
						record.set('Person_Secname', data.evnPLStomData.Person_Secname);
						record.set('Person_id', data.evnPLStomData.Person_id);
						record.set('PersonEvn_id', data.evnPLStomData.PersonEvn_id);
						record.set('Server_id', data.evnPLStomData.Server_id);
						record.set('EvnCostPrint_setDT', data.evnPLStomData.EvnCostPrint_setDT);
						record.set('EvnCostPrint_IsNoPrintText', data.evnPLStomData.EvnCostPrint_IsNoPrintText);

						record.commit();
					}

					win.checkPrintCost();
				}
				else {
					grid.getStore().loadData([ data.evnPLStomData ], true);
				}

				grid.getStore().each(function(record) {
					if ( record.get('Person_id') == data.evnPLStomData.Person_id && record.get('Server_id') == data.evnPLStomData.Server_id ) {
						record.set('Person_Birthday', data.evnPLStomData.Person_Birthday);
						record.set('Person_Surname', data.evnPLStomData.Person_Surname);
						record.set('Person_Firname', data.evnPLStomData.Person_Firname);
						record.set('Person_Secname', data.evnPLStomData.Person_Secname);

						record.commit();
					}
				});
			};
		}

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Здесь надо будет переделать использование getWnd
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swEvnPLStomEditWindow').show(params);
				},
				personFirname: this.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Firname').getValue(),
				personSecname: this.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Secname').getValue(),
				personSurname: this.findById('EPLStomSW_EvnPLSearchFilterForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			var evn_pl_stom_id = selected_record.get('EvnPLStom_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = selected_record.get('archiveRecord');
			}

			if ( evn_pl_stom_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.EvnPLStom_id = evn_pl_stom_id;
				params.onHide = function() {
					if ( grid.getStore().indexOf(selected_record) >= 0 ) {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};
				params.Person_id =  person_id;
				params.Server_id = server_id;

				getWnd('swEvnPLStomEditWindow').show(params);
			}
		}
	},
	plain: true,
	printEvnPLStom: function() {
		var grid = this.getActiveViewFrame().getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') ) {
			return false;
		}

		var evn_pl_stom_id = grid.getSelectionModel().getSelected().get('EvnPLStom_id');

		if ( evn_pl_stom_id > 0 ) {
			printEvnPL({
				type: 'EvnPLStom',
				EvnPL_id: evn_pl_stom_id
			});
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLStomSearchWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EPLStomSW_EvnPLSearchFilterForm').getForm();

		if ( getRegionNick() == 'perm' ) {
			if ( !this.Wizard.EvnPLStomSearchFrame.getAction('action_changeperson') ) {
				this.Wizard.EvnPLStomSearchFrame.addActions({
					disabled: true,
					handler: function() {
						this.changePerson();
					}.createDelegate(this),
					iconCls: 'doubles16',
					name: 'action_changeperson',
					text: lang['smenit_patsienta_v_uchetnom_dokumente']
				});
			}
		}

		if ( !this.Wizard.EvnPLStomSearchFrame.getAction('action_setevnistransit') ) {
			this.Wizard.EvnPLStomSearchFrame.addActions({
				disabled: true,
				handler: function() {
					this.setEvnIsTransit();
				}.createDelegate(this),
				iconCls: 'actions16',
				id: this.id + 'action_setevnistransit',
				name: 'action_setevnistransit',
				text: lang['perehodnyiy_sluchay']
			});
		}

		this.Wizard.EvnPLStomSearchFrame.setActionHidden('action_setevnistransit', !lpuIsTransit());

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		}
		this.Wizard.EvnPLStomSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		this.Wizard.EvnVizitPLStomSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		
		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) //https://redmine.swan.perm.ru/issues/78988
		{
			var params = {};
			params.Lpu_id = getGlobalOptions().lpu_id;
			base_form.findField('LpuRegion_Fapid').getStore().load({
				params: params
			});
		}
		
		// Устанавливаем фильтры на категории выбираемых услуг
		base_form.findField('UslugaComplex_Code_From').setAllowedUslugaComplexAttributeList([ 'stom' ]);
		base_form.findField('UslugaComplex_Code_To').setAllowedUslugaComplexAttributeList([ 'stom' ]);
/*
		if ( base_form.findField('UslugaCategory_id').getStore().getCount() == 1 ) {
			base_form.findField('UslugaCategory_id').disable();
			base_form.findField('UslugaCategory_id').setValue(base_form.findField('UslugaCategory_id').getStore().getAt(0).get('UslugaCategory_id'));
		}
		else {
			base_form.findField('UslugaCategory_id').enable();
		}
*/
		base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
		base_form.findField('PL_PrehospDirect_id').getStore().loadData([{PrehospDirect_id:99,PrehospDirect_Name:lang['neizvestno'],PrehospDirect_Code:'0'}],true);
		base_form.findField('DeseaseType_id').getStore().loadData([{DeseaseType_id:99,DeseaseType_Name:lang['neizvestno'],DeseaseType_Code:'0'}],true);
		
		if ( getGlobalOptions().region ) {
			switch ( getGlobalOptions().region.nick ) {
				case 'ufa':
					base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
				break;
			}
		}
	},
	title: WND_POL_EPLSTOMSEARCH,
	width: 800
});