/**
* swEvnPLSearchWindow - окно поиска талона амбулаторного пациента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-05.08.2009
* @comment      Префикс для id компонентов EPLSW (EvnPLSearchWindow)
*
*
* Использует: окно редактирования талона амбулаторного пациента (swEvnPLEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*             окно поиска человека (swPersonSearchWindow)
*/

sw.Promed.swEvnPLSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') ) {
			return false;
		}

		var form = this;
		var grid = this.Wizard.EvnPLSearchFrame.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPL_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		var params = {
			Evn_id: record.get('EvnPL_id')
		};

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				form.setAnotherPersonForDocument(params);
			},
			personFirname: form.findById('EvnPLSearchFilterForm').getForm().findField('Person_Firname').getValue(),
			personSecname: form.findById('EvnPLSearchFilterForm').getForm().findField('Person_Secname').getValue(),
			personSurname: form.findById('EvnPLSearchFilterForm').getForm().findField('Person_Surname').getValue(),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var grid = this.Wizard.EvnPLSearchFrame.getGrid();

		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при переоформлении документа на другого человека');
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
							title: 'Вопрос'
						});
					}
					else {
						grid.getStore().remove(grid.getSelectionModel().getSelected());

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}

						getWnd('swPersonSearchWindow').hide();

                        var info_msg = 'Документ успешно переоформлен на другого человека';
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
						sw.swMsg.alert('Сообщение', info_msg, function() {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						});
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При переоформлении документа на другого человека произошли ошибки');
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

		var grid = this.Wizard.EvnPLSearchFrame.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPL_id') || grid.getSelectionModel().getSelected().get('EvnPL_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPL_id'),
			Evn_IsTransit: Evn_IsTransit
		};

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: 'Установка признака "Переходный случай между МО"...' });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при установке признака "Переходный случай между МО"');
					}
					else {
						record.set('EvnPL_IsTransit', Evn_IsTransit);
						record.commit();
						this.Wizard.EvnPLSearchFrame.onRowSelect(null, null, record);
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при установке признака "Переходный случай между МО"');
				}
			}.createDelegate(this),
			params: params,
			url: C_SETEVNISTRANSIT
		});
	},
	closable: true,
	onEsc: function () {
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		var store = this.getActiveViewFrame().ViewGridPanel.getStore();
		if(loadMask && loadMask.hide){
			loadMask.hide();
		}

		var loadMask = this.getActiveViewFrame().getGrid().loadMask;
		if(loadMask && loadMask.hide){
			loadMask.hide();
		}

		this.searchInProgress = false;
		Ext.Ajax.abort(store.proxy.activeRequest);
		Ext.getCmp('EvnPLSearchWindow').hide();
	},
	collapsible: true,
	deleteEvnPL: function(options) {
		options = options || {};
		var grid = this.getActiveViewFrame().getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPL_id') ) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		var Evn_id = (record.get('EvnVizitPL_id')&&record.get('EvnVizitPL_id')>0)?record.get('EvnVizitPL_id'):record.get('EvnPL_id');
        var ArmType = (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null;
		var msgText = (record.get('EvnVizitPL_id')&&record.get('EvnVizitPL_id')>0)?'Удалить посещение?':'Удалить талон?';
		var Evn_type = (record.get('EvnVizitPL_id')&&record.get('EvnVizitPL_id')>0)?'EvnVizitPL':'EvnPL';
		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPL(options);
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
							callback: function(opts, success, response) {
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(resp.Error_Msg)) {
										options.ignoreHomeVizit = true;
										scope.deleteEvnPL(options);
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

		var params = {
			Evn_id: Evn_id,
			ArmType: ArmType
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
									title: 'Вопрос'
								});
							} else {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при удалении ТАП');
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
						sw.swMsg.alert('Ошибка', 'При удалении талона возникли ошибки');
					}
				}.createDelegate(this),
				params: params,
				url: C_EVN_DEL
			});
		}.createDelegate(this);

		if (options.ignoreQuestion) {
			doDelete();
		} else {
			this.checkEvnPlOnDelete(function () {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreQuestion = true;
							doDelete();
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: msgText,
					title: 'Вопрос'
				});
			}, Evn_id, Evn_type);
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
		var current_window = this;
		var form = current_window.findById('EvnPLSearchFilterForm');

		form.getForm().reset();

		if ( form.getForm().findField('AttachLpu_id') != null ) {
			form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), 0, 1);
		}

		if ( form.getForm().findField('LpuRegion_id') != null ) {
			form.getForm().findField('LpuRegion_id').lastQuery = '';
			form.getForm().findField('LpuRegion_id').getStore().clearFilter();
		}

		if ( form.getForm().findField('PrivilegeType_id') != null ) {
			form.getForm().findField('PrivilegeType_id').lastQuery = '';
			form.getForm().findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});
		}

		if ( form.getForm().findField('LpuRegionType_id') != null ) {
			form.getForm().findField('LpuRegionType_id').getStore().clearFilter();
		}

		if ( form.getForm().findField('DirectClass_id') != null ) {
			form.getForm().findField('DirectClass_id').fireEvent('change', form.getForm().findField('DirectClass_id'), null, 1);
		}

		if ( form.getForm().findField('PersonCardStateType_id') != null ) {
			form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
		}

		form.getForm().findField('Diag_IsNotSet').fireEvent('check', form.getForm().findField('Diag_IsNotSet'), false);

		if ( form.getForm().findField('PrivilegeStateType_id') != null ) {
			form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
		}

		form.findById('EPLSW_SearchFilterTabbar').setActiveTab(0);
		form.findById('EPLSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('EPLSW_SearchFilterTabbar').getActiveTab());

		current_window.getActiveViewFrame().ViewGridPanel.getStore().removeAll();
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log('Поиск уже выполняется!');
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		
		var base_form = this.findById('EvnPLSearchFilterForm').getForm();
		var form = this.findById('EvnPLSearchFilterForm');
		
		if ( form.isEmpty() && !(params && params['soc_card_id']) ) {
			sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var grid = this.getActiveViewFrame().ViewGridPanel;

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
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
				msg: 'Выбран тип поиска человека ' + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? 'по состоянию на момент случая' : 'по всем периодикам') + '.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?',
				title: 'Предупреждение'
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		if(getRegionNick().inlist(['ekb', 'penza'])){
			switch(base_form.findField('EvnPL_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
				case 1:
					post.EvnPL_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
					break;
				case 2:
					post.EvnPL_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
					break;
				default:
					post.EvnPL_InRegistry = 0;// все ТАП
			}
		}
		else if(post.EvnPL_InRegistry){
			delete post.EvnPL_InRegistry;
		}

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		if ( form.getForm().findField('UslugaCategory_id').disabled ) {
			post.UslugaCategory_id = form.getForm().findField('UslugaCategory_id').getValue();
		}

		if ( form.getForm().findField('MedStaffFactViz_id').getValue() > 0 ) {
			var med_personal_viz_record = false;
			form.getForm().findField('MedStaffFactViz_id').getStore().each(function(record){
				if ( record.get('MedStaffFact_id') == form.getForm().findField('MedStaffFactViz_id').getValue() )
				{
					med_personal_viz_record = record;
				}
			});

			if ( med_personal_viz_record ) {
				post.MedPersonalViz_id = med_personal_viz_record.get('MedPersonal_id');
			}
		}
	
		if ( form.getForm().findField('MedStaffFactViz_sid').getValue() > 0 ) {
			var med_personal_viz_record = false;
			form.getForm().findField('MedStaffFactViz_sid').getStore().each(function(record){
				if ( record.get('MedStaffFact_id') == form.getForm().findField('MedStaffFactViz_sid').getValue() )
				{
					med_personal_viz_record = record;
				}
			});

			if ( med_personal_viz_record ) {
				post.MedPersonalViz_sid = med_personal_viz_record.get('MedPersonal_id');
			}
		}

		/*if ( form.getForm().findField('MedStaffFactViz_sid') > 0 ) {
			var med_personal_viz_record = form.getForm().findField('MedStaffFactViz_sid').getStore().getById(form.getForm().findField('MedStaffFactViz_sid').getValue());

			if ( med_personal_viz_record ) {
				post.MedPersonalViz_sid = med_personal_viz_record.get('MedPersonal_sid');
			}
		}*/

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
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	draggable: true,
	exportRecordsToDbf: function(options) {
		var form = this.findById('EvnPLSearchFilterForm');
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
		if(getRegionNick() == 'kareliya')
		{
			if(options && options.filter_type == 1) //https://redmine.swan.perm.ru/issues/96310
				var params = getAllFormFieldValues(form);
			else // Если ищем без учета фильтра, то оставляем только ЛПУ и тип поиска
			{
				var params = {};
				params.lpu_id = getGlobalOptions().lpu_id;
				params.SearchFormType = this.getActiveViewFrame().object;
			}
		}
		else
			var params = getAllFormFieldValues(form);

		if(getRegionNick().inlist(['ekb', 'penza']) && options && options.filter_type == 1){
			switch(base_form.findField('EvnPL_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
				case 1:
					params.EvnPL_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
					break;
				case 2:
					params.EvnPL_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
					break;
				default:
					params.EvnPL_InRegistry = 0;// все ТАП
			}
		}
		else if(params.EvnPL_InRegistry){
			delete params.EvnPL_InRegistry;
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
		/*
		alert('1');
		log(params);
		alert('2');
		loadMask.hide();
		*/
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
		var form = this.findById('EvnPLSearchFilterForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
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
						sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert('Подсчет записей', response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При подсчете количества записей произошли ошибки');
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	id: 'EvnPLSearchWindow',
	Wizard: {EvnPLSearchFrame:null, EvnVizitPLSearchFrame: null, Panel: null},
	getActiveViewFrame: function () 
	{
		return this.Wizard[this.Wizard.Panel.layout.activeItem.id];
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLSW_SearchButton');
	},
	printList: function() {
		var base_form = this.findById('EvnPLSearchFilterForm').getForm();
		var baseParams = this.getActiveViewFrame().getGrid().getStore().baseParams;
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

		var baseParams = this.getActiveViewFrame().getGrid().getStore().baseParams;

		base_form.submit();

		if ( base_form.findField('MedPersonalViz_id') ) {
			base_form.findField('MedPersonalViz_id').setValue(0);
		}

		if ( base_form.findField('MedPersonalViz_sid') ) {
			base_form.findField('MedPersonalViz_sid').setValue(0);
		}
	},
	printCost: function(ByDay) {
		var grid = this.Wizard.EvnPLSearchFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPL_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPL_id'),
				Person_id: selected_record.get('Person_id'), //https://redmine.swan.perm.ru/issues/55589
				type: 'EvnPL',
				ByDay: ByDay,
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.Wizard.EvnPLSearchFrame.getGrid();
		var menuPrint = this.Wizard.EvnPLSearchFrame.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPL_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPL_IsFinish') != 'Да');
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
		this.Wizard.EvnPLSearchFrame = new sw.Promed.ViewFrame({
			EXCEL: true,
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLSearchWindow').deleteEvnPL(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLSW_EvnPLSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print',
					menuConfig: {
						printObject: { text: 'Печать ТАП', handler: function() { Ext.getCmp('EvnPLSearchWindow').printEvnPL(); } },
						//printObjectListFull: { handler: function() { Ext.getCmp('EvnPLSearchWindow').printList(); } },
						printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: 'Справка о стоимости лечения', handler: function () { win.printCost(0) }},
						printCostByDay: {name: 'printCostByDay', hidden: true, text: 'Справка о стоимости лечения за день', handler: function () { win.printCost(1) }} //https://redmine.swan.perm.ru/issues/55589
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLSW_SearchButton',
				type: 'button'
			},
			id: 'EPLSW_EvnPLSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			object: 'EvnPL',
			root: 'data',
			stringfields: [
				{ name: 'EvnPL_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPL_IsTransit', type: 'int', hidden: true },
				{ name: 'EvnPL_NumCard', type: 'string', header: '№ талона', width: 70 },
				{ name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 100 },
				{ name: 'Person_Firname', type: 'string', header: 'Имя', width: 100 },
				{ name: 'Person_Secname', type: 'string', header: 'Отчество', width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 70 },

				{ name: 'Polis_Num', type: 'string', header: 'Номер полиса', hideable: true, hidden: !(getRegionNick().inlist(['astra','ufa','vologda','krym','khak'])) },

				{ name: 'Person_deadDT', type:'date', format: 'd.m.Y', header: 'Дата смерти', width: 90 },
				{ name: 'EvnPL_VizitCount', type: 'int', header: 'Посещений', width: 80 },
                { name: 'VizitType_Name', type: 'string', header: 'Цель посещения', width: 160 },
				{ name: 'EvnPL_IsFinish', type: 'string', header: 'Законч', width: 70 },
				{ name: 'Diag_Name', type: 'string', header: 'Основной диагноз', id: 'autoexpand' },
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 160 },
				{ name: 'EvnPL_setDate', type: 'date', format: 'd.m.Y', header: 'Дата начала', width: 90 },
				{ name: 'EvnPL_disDate', type: 'date', format: 'd.m.Y', header: 'Дата окончания', width: 90 },
				{ name: 'HealthKind_Name', type: 'string', header: 'Группа здоровья', width: 70, hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ? false : true },
				{ name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkcolumn', width: 30 },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: 'Дата выдачи справки/отказа', width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: 'Справка о стоимости лечения', width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'toAis25', type: 'checkcolumn', header: 'АИС Пол-ка (25-5у)', width: 150, hidden: hiddenToAis25 },
				{ name: 'toAis259', type: 'checkcolumn', header: 'АИС Пол-ка (25-9у)', width: 150, hidden: hiddenToAis259 },
				{ name: 'fedservice_iemk', type: 'checkcolumn', header: 'ИЭМК', width: 50 }
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
						if ( record.get('EvnPL_id') ) {
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

					if ( record.get('EvnPL_id') ) {
						this.setActionDisabled('action_setevnistransit', !(record.get('EvnPL_IsTransit') == 1));
					}
					else {
						this.setActionDisabled('action_setevnistransit', true);
					}
				}

				win.checkPrintCost();
			}
		});
		
		this.Wizard.EvnVizitPLSearchFrame = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLSearchWindow').openEvnPLEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLSearchWindow').deleteEvnPL(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLSW_EvnVizitPLSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print', /*handler: function() { Ext.getCmp('EvnPLSearchWindow').printEvnPL(); }*/
					menuConfig: {
						printObject: {handler: function() { Ext.getCmp('EvnPLSearchWindow').printEvnPL(); } }
						//printObjectList: { handler: function() { Ext.getCmp('EvnPLSearchWindow').printList(false); } }
						//printObjectListFull: { handler: function() { Ext.getCmp('EvnPLSearchWindow').printList(true); } }
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLSW_SearchButton',
				type: 'button'
			},
			id: 'EPLSW_EvnVizitPLSearchGrid',
			pageSize: 100,
			paging: true,
			region: 'center',
			object: 'EvnVizitPL',
			root: 'data',
			stringfields: [
				{ name: 'EvnVizitPL_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPL_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPL_NumCard', type: 'string', header: '№ талона', width: 70 },
				{ name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 100 },
				{ name: 'Person_Firname', type: 'string', header: 'Имя', width: 100 },
				{ name: 'Person_Secname', type: 'string', header: 'Отчество', width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 70 },
				{ name: 'Person_deadDT', type:'date', format: 'd.m.Y', header: 'Дата смерти', width: 90 },
				{ name: 'Diag_Name', type: 'string', header: 'Основной диагноз', id: 'autoexpand' },
				
				{ name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 100 },
				
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 160 },
				{ name: 'UslugaComplex_Code', type: 'string', header: 'Код посещения', width: 100, hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ? false : true },
				{ name: 'EvnVizitPL_setDate', type: 'date', format: 'd.m.Y', header: 'Дата посещения', width: 90 },
				{ name: 'ServiceType_Name', type: 'string', header: 'Место обслуживания,', width: 100 },
				{ name: 'VizitType_Name', type: 'string', header: 'Цель посещения', width: 160 },
				{ name: 'PayType_Name', type: 'string', header: 'Вид оплаты', width: 70 },
				{ name: 'HealthKind_Name', type: 'string', header: 'Группа здоровья', width: 70, hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ? false : true },
				
				{ name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkcolumn', width: 30 },
				{ name: 'fedservice_iemk', type: 'checkcolumn', header: 'ИЭМК', width: 50 }
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
				}
				else{
					// Запретить редактирование/удаление архивных записей
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
				id: 'EvnPLSearchFrame',
				layout:'fit',
				items:[this.Wizard.EvnPLSearchFrame]
			},{
				id: 'EvnVizitPLSearchFrame',
				layout: 'fit',
				items:[this.Wizard.EvnVizitPLSearchFrame]
			}]
		});

		this.tabs = [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
/*
							if ( this.getFilterForm().getForm().findField('LpuSectionDiag_id').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									isPolka: true
								});
								this.getFilterForm().getForm().findField('LpuSectionDiag_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedPersonalDiag_id').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									isPolka: true
								});
								this.getFilterForm().getForm().findField('MedPersonalDiag_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}
*/
							this.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
						}.createDelegate(this)
					},
					title: '<u>6</u>. Диагноз и услуги',

					// tabIndexStart: TABINDEX_EPLSW + 68
					items: [{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: 'Диагноз',
						width: 755,
						xtype: 'fieldset',

						items: [{
							fieldLabel: 'Не установлен',
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
							fieldLabel: 'Код диагноза с',
							hiddenName: 'Diag_Code_From',
							listWidth: 620,
							tabIndex: TABINDEX_EPLSW + 68,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, {
							checkAccessRights: true,
							fieldLabel: 'по',
							hiddenName: 'Diag_Code_To',
							listWidth: 620,
							tabIndex: TABINDEX_EPLSW + 69,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, /*{
							hiddenName: 'LpuSectionDiag_id',
							id: 'EPLSW_LpuSectionDiagCombo',
							lastQuery: '',
							linkedElements: [
								'EPLSW_MedStaffFactDiagCombo'
							],
							tabIndex: TABINDEX_EPLSW + 70,
							width: 620,
							xtype: 'swlpusectionglobalcombo'
						}, {
							hiddenName: 'MedStaffFactDiag_id',
							id: 'EPLSW_MedStaffFactDiagCombo',
							lastQuery: '',
							parentElementId: 'EPLSW_LpuSectionDiagCombo',
							tabIndex: TABINDEX_EPLSW + 71,
							width: 620,
							xtype: 'swmedstafffactglobalcombo'
						}, */{
							comboSubject: 'DeseaseType',
							fieldLabel: 'Характер заболев.',
							hiddenName: 'DeseaseType_id',
							tabIndex: TABINDEX_EPLSW + 72,
							width: 420,
							xtype: 'swcommonsprcombo'
						}]
					}, 
					{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: 'Услуги',
						width: 755,
						xtype: 'fieldset',
						items: [{
							allowBlank: true,
							fieldLabel: 'Категория услуги',
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
							loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}} : null),
							tabIndex: TABINDEX_EPLSW + 73,
							width: 250,
							xtype: 'swuslugacategorycombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Услуга с',
									hiddenName: 'UslugaComplex_Code_From',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPLSW + 74,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 30,
								items: [{
									fieldLabel: 'по',
									hiddenName: 'UslugaComplex_Code_To',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPLSW + 75,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Код посещения',
								hiddenName: 'UslugaComplex_uid',
								listWidth: 590,
								showUslugaComplexEndDate: true,
								tabIndex: TABINDEX_EPLSW + 75,
								width: 590,
								xtype: 'swuslugacomplexnewcombo'
							}]
						}, {
							border: false,
							hidden: !(getRegionNick().inlist([ 'ufa' ])), // Открыто для Уфы и Перми
							layout: 'form',
							items: [{
								fieldLabel: 'Код посещения (шаблон)',
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
							if ( this.getFilterForm().getForm().findField('LpuBuildingViz_id').getStore().getCount() == 0 ) {
								swLpuBuildingGlobalStore.clearFilter();
								this.getFilterForm().getForm().findField('LpuBuildingViz_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
							}
							
							if ( this.getFilterForm().getForm().findField('LpuSectionViz_id').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									allowLowLevel: 'yes',
									isPolka: true
								});
								this.getFilterForm().getForm().findField('LpuSectionViz_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedStaffFactViz_id').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									allowLowLevel: 'yes',
									isPolka: true
								});
								this.getFilterForm().getForm().findField('MedStaffFactViz_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedStaffFactViz_sid').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									allowLowLevel: 'yes',
									isPolka: true
								});
								this.getFilterForm().getForm().findField('MedStaffFactViz_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							this.getFilterForm().getForm().findField('EvnPL_NumCard').focus(250, true);
						}.createDelegate(this)
					},
					title: '<u>7</u>. Посещение',
					labelWidth: 220,
					// tabIndexStart: TABINDEX_EPLSW + 77
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
						labelWidth: 220,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								enableKeyEvents: true,
								fieldLabel: '№ талона',						
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnPL_NumCard',
								tabIndex: TABINDEX_EPLSW + 77,
								width: 150,
								maskRe: /[^%]/,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 180,
							layout: 'form',
							items: [{
								fieldLabel: 'Дата начала случая',
								name: 'EvnPL_setDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLSW + 77,
								width: 200,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							labelWidth: 160,
							layout: 'form',
							items: [{
								fieldLabel: 'Дата окончания случая',
								name: 'EvnPL_disDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLSW + 77,
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
									tabIndex: TABINDEX_EPLSW + 77,
									xtype: 'swvizitclasscombo'
								}]
							}]
					}, {
						comboSubject: 'PrehospTrauma',
						fieldLabel: 'Вид травмы (внешнего воздействия)',
						hiddenName: 'PrehospTrauma_id',
						tabIndex: TABINDEX_EPLSW + 78,
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						border: false,
						labelWidth: 220,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo', // TODO: Зачем справочник от swcommonsprcombo, а не от yesno
								fieldLabel: 'Противоправная',
								hiddenName: 'EvnPL_IsUnlaw',
								tabIndex: TABINDEX_EPLSW + 79,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: 'Нетранспортабельность',
								hiddenName: 'EvnPL_IsUnport',
								tabIndex: TABINDEX_EPLSW + 80,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}, {
							// #107998
							// Екатеринбург. Признак вхождения в реестр для ТАП
							// Пенза. Признак вхождения в реестр для одного из движений ТАП
							border: false,
							layout: 'form',
							labelWidth: 180,
							hidden: ! getRegionNick().inlist(['ekb', 'penza']),
							items:[{
								name: 'EvnPL_InRegistry',
								fieldLabel: langs('Включение в реестр'),
								displayField: 'EvnPL_InRegistry_Name',
								valueField: 'EvnPL_InRegistry_id',
								editable: false,
								disabled: ! getRegionNick().inlist(['ekb', 'penza']),
								hidden: ! getRegionNick().inlist(['ekb', 'penza']),
								store: new Ext.data.SimpleStore({
									autoLoad: true,
									data: [
										[0, ''],// все КВС
										[2, langs('Да')],// да: ekb - признак вхождения в реестр, penza - одно из движений в реестре
										[1, langs('Нет')]// нет: ekb - не установлен признак вхождения в реестр, penza - ни одно из движений в реестре
									],
									fields: [
										{name: 'EvnPL_InRegistry_id', type: 'int'},
										{name: 'EvnPL_InRegistry_Name', type: 'string'}
									],
									key: 'EvnPL_InRegistry_id'
								}),
								width: 72,
								listWidth: 70,
								tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">&nbsp;{EvnPL_InRegistry_Name}</div></tpl>'),
								xtype: 'swbaselocalcombo'
							}]
						}]
					}, {
						hiddenName: 'LpuBuildingViz_id',
						fieldLabel: 'Подразделение',
						id: 'EPLSW_LpuBuildingVizCombo',
						lastQuery: '',
						linkedElements: [
							'EPLSW_LpuSectionVizCombo'
						],
						listWidth: 700,
						tabIndex: TABINDEX_EPLSW + 81,
						width: 450,
						xtype: 'swlpubuildingglobalcombo'
					}, {
						hiddenName: 'LpuSectionViz_id',
						id: 'EPLSW_LpuSectionVizCombo',
						lastQuery: '',
						linkedElements: [
							'EPLSW_MedStaffFactVizCombo'/*,
							'EPLSW_MidMedStaffFactVizCombo'*/
						],
						parentElementId: 'EPLSW_LpuBuildingVizCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EPLSW + 82,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						hiddenName: 'MedStaffFactViz_id',
						id: 'EPLSW_MedStaffFactVizCombo',
						lastQuery: '',
						parentElementId: 'EPLSW_LpuSectionVizCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EPLSW + 83,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						fieldLabel: 'Средний м/перс.',
						hiddenName: 'MedStaffFactViz_sid',
						id: 'EPLSW_MidMedStaffFactVizCombo',
						lastQuery: '',
						listWidth: 700,
						//parentElementId: 'EPLSW_LpuSectionVizCombo',
						tabIndex: TABINDEX_EPLSW + 84,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						border: false,
						labelWidth: 220,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'ServiceType',
								hiddenName: 'ServiceType_id',
								fieldLabel: 'Место обслуж-я',
								tabIndex: TABINDEX_EPLSW + 85,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								listWidth: 300,
								tabIndex: TABINDEX_EPLSW + 86,
								width: 200,
								useCommonFilter: true,
								xtype: 'swpaytypecombo'
							}, {
								tabIndex: TABINDEX_EPLSW + 87,
								fieldLabel: 'Случай оплачен',
								hiddenName: 'EvnVizitPL_isPaid',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == newValue);
										});
										combo.fireEvent('select', combo, combo.getStore().getAt(index));
									},
									'select': function(combo, record, index) {
										if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) && this.getActiveViewFrame().object == 'EvnVizitPL' ) {
											this.findById('EPLSW_IsPaidLabel').show();
										}
										else {
											this.findById('EPLSW_IsPaidLabel').hide();
										}
									}.createDelegate(this)
								},
								width: 200,
								xtype: 'swyesnocombo'
							}, {
								tabIndex: TABINDEX_EPLSW + 88,
								fieldLabel: 'Вид обращения',
								comboSubject: 'TreatmentClass',
								hiddenName: 'TreatmentClass_id',
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								listWidth: 400,
								tabIndex: TABINDEX_EPLSW + 89,
								width: 200,
								EvnClass_id: 11,
								xtype: 'swvizittypecombo'
							}, {
								fieldLabel: 'Дата посещения',
								name: 'Vizit_Date_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPLSW + 90,
								width: 200,
								xtype: 'daterangefield'
							}, {
								border: false,
								hidden: (getGlobalOptions().region.nick != 'ufa'),
								layout: 'form',
								items: [{
									comboSubject: 'HealthKind',
									hiddenName: 'HealthKind_id',
									fieldLabel: 'Группа здоровья',
									tabIndex: TABINDEX_EPLSW + 91,
									width: 200,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								id: 'EPLSW_IsPaidLabel',
								layout: 'form',
								border: false,
								hidden: true,
								style: 'padding-top: 3px;',
								items: [{
									xtype: 'label',
									style: 'margin-left: 10px; color: red;',
									text: 'Внимание! Медленный поиск!'
								}]
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
							if ( this.getFilterForm().getForm().findField('LpuSection_oid').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter();
								this.getFilterForm().getForm().findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							this.getFilterForm().getForm().findField('EvnPL_IsFinish').focus(250, true);
						}.createDelegate(this)
					},
					title: '<u>8</u>. Результаты',

					// tabIndexStart: TABINDEX_EPLSW + 91
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: 'Случай закончен',
								hiddenName: 'EvnPL_IsFinish',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											inp.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
										}
									}
								},
								tabIndex: TABINDEX_EPLSW + 91,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'DirectClass',
								fieldLabel: 'Куда направлен',
								hiddenName: 'DirectClass_id',
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
								tabIndex: TABINDEX_EPLSW + 92,
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'ResultClass',
								loadParams: (
									getRegionNick() == 'ekb' ? {params: {where: 'where ResultClass_fedid is null'}} : 
									(getRegionNick() == 'kaluga' ? {params: {where: "where ResultClass_Code in ('301','305','308','314')"}} : null)
								),
								fieldLabel: ( getRegionNick().inlist(['kareliya','ekb']) ) ? 'Результат обращения' : 'Результат лечения',
								hiddenName: 'ResultClass_id',
								tabIndex: TABINDEX_EPLSW + 93,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'DirectType',
								fieldLabel: 'Направление',
								hiddenName: 'DirectType_id',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSW + 94,
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						disabled: true,
						fieldLabel: 'Отделение ЛПУ',
						hiddenName: 'LpuSection_oid',
						lastQuery: '',
						tabIndex: TABINDEX_EPLSW + 95,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						disabled: true,
						displayField: 'Lpu_Nick',
						fieldLabel: 'Другое ЛПУ',
						hiddenName: 'Lpu_oid',
						store: new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Lpu_Name', type: 'string' },
								{ name: 'Lpu_Nick', type: 'string' },
								{ name: 'Lpu_RegNomC2', type: 'int' },
								{ name: 'Lpu_RegNomN2', type: 'int' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Lpu_Nick'
							},
							tableName: 'Lpu'
						}),
						tabIndex: TABINDEX_EPLSW + 96,
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
						title: 'Выписка листа нетрудоспособности',
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
									tabIndex: TABINDEX_EPLSW + 97,
									width: 200,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Закрыт',
									name: 'EvnStick_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPLSW + 98,
									width: 200,
									xtype: 'daterangefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'StickType',
									fieldLabel: 'Тип листа',
									hiddenName: 'StickType_id',
									tabIndex: TABINDEX_EPLSW + 99,
									width: 200,
									xtype: 'swcommonsprcombo'
								}, {
									tabIndex: TABINDEX_EPLSW + 100,
									listWidth: 450,
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
								tabIndex: TABINDEX_EPLSW + 100,
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
								tabIndex: TABINDEX_EPLSW + 100,
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
					title: '<u>9</u>. Направление',

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
						title: 'Направление',
						width: 755,
						xtype: 'fieldset',

						items: [{
								xtype: 'checkbox',
								id: 'PL_ElDirection',
								labelSeparator: '',
								boxLabel: 'Без электронного направления'
							},{
								comboSubject: 'PrehospDirect',
								typeCode: 'int',
								fieldLabel: 'Кем направлен',
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
								fieldLabel: '№ направления',
								name: 'PL_NumDirection',
								tabIndex: TABINDEX_EPLSW + 76,
								width: 200,
								xtype: 'textfield'
							},{
								fieldLabel: 'Дата направления',
								format: 'd.m.Y',
								name: 'PL_DirectionDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								tabIndex: TABINDEX_EDHEF + 14,
								width: 100,
								xtype: 'swdatefield'
							},{
								disabled: true,
								fieldLabel: 'Отделение',
								hiddenName: 'PL_LpuSection_id',
								lastQuery: '',
								tabIndex: TABINDEX_EPLSW + 95,
								width: 450,
								xtype: 'swlpusectionglobalcombo'
							},{
								displayField: 'Org_Name',
								editable: false,
								enableKeyEvents: true,
								fieldLabel: 'Организация',
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
								fieldLabel: 'Диагноз напр. учреждения',
								tabIndex: TABINDEX_EEPLEF + 35,
								width: 450,
								xtype: 'swdiagcombo'
							}]
						}]
							
							}
						]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					title: langs('<u>10</u>. Фед.сервисы'),
					items: [{
						comboSubject: 'ServiceEvnStatus',
						fieldLabel: 'ИЭМК',
						hiddenName: 'Service1EvnStatus_id',
						tabIndex: TABINDEX_EPLSW + 97,
						width: 200,
						xtype: 'swcommonsprcombo'
					}]
				}];
		Ext.apply(this, {
			buttons: [
			{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EPLSW_SearchButton',
				tabIndex: TABINDEX_EPLSW + 109,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLSW + 110,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					var base_form = this.findById('EvnPLSearchFilterForm').getForm();
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
				tabIndex: TABINDEX_EPLSW + 111,
				text: 'Печать списка'
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLSW + 112,
				text: BTN_FRMCOUNT
			}, {
				handler: function() {
					//this.exportRecordsToDbf();
					var wnd = this;
					if(getRegionNick() == 'kareliya')
					{
						getWnd('swEvnPLExportMenu').show({
							callback: function (data) {
								wnd.exportRecordsToDbf(data);
							}
						});
					}
					else
						wnd.exportRecordsToDbf();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSW + 112,
				text: (getRegionNick()=='kareliya')?langs('Выгрузить в dbf'):'Экспорт найденных талонов в DBF'
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_EPLSW + 113),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EPLSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EPLSW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSW + 114,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnPLSearchFilterForm');
				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
				useArchive: 1,
				allowPersonPeriodicSelect: true,
				id: 'EvnPLSearchFilterForm',
				ownerWindow: this,
				searchFormType: 'EvnPL',
				ownerWindowWizardPanel: this.Wizard.Panel,
				tabIndexBase: TABINDEX_EPLSW,
				tabPanelId: 'EPLSW_SearchFilterTabbar',
				tabPanelHeight: 280,
				tabs: this.tabs
			}),
			this.Wizard.Panel
			]
		});

		sw.Promed.swEvnPLSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					current_window.openEvnPLEditWindow('add');
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
			var current_window = Ext.getCmp('EvnPLSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLSW_SearchFilterTabbar');

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
			win.findById('EvnPLSearchFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EvnPLSearchFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('EPLSW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('EvnPLSearchFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var current_window = this;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
			return false;
		}

		if ( getWnd('swEvnPLEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования талона амбулаторного пациента уже открыто');
			return false;
		}

		var params = {};
		var grid = current_window.getActiveViewFrame().ViewGridPanel;

		params.action = action;
		params.streamInput = true;
		// params.callback только при поиске талонов!!! т.к. при поиске посещений будет другой грид - с посещениями!
		if ('EvnPL' != current_window.getActiveViewFrame().object)
		{
			params.callback = Ext.emptyFn;
		}
		else
		{
			params.callback = function(data) {
				if ( typeof data != 'object' || typeof data.evnPLData != 'object' ) {
					return false;
				}

				// Обновить запись в grid
				var index = grid.getStore().findBy(function(rec) {
					return (rec.get('EvnPL_id') == data.evnPLData.EvnPL_id);
				});
				var record = grid.getStore().getAt(index);

				if ( typeof record == 'object' ) {
					if ( data.evnPLData.lastEvnDeleted == true ) {
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}
					}
					else {
						record.set('Diag_Name', data.evnPLData.Diag_Name);
						record.set('EvnPL_disDate', data.evnPLData.EvnPL_disDate);
						record.set('EvnPL_id', data.evnPLData.EvnPL_id);
						record.set('EvnPL_IsFinish', data.evnPLData.EvnPL_IsFinish);
						record.set('EvnPL_NumCard', data.evnPLData.EvnPL_NumCard);
						record.set('EvnPL_setDate', data.evnPLData.EvnPL_setDate);
						record.set('EvnPL_VizitCount', data.evnPLData.EvnPL_VizitCount);
						record.set('MedPersonal_Fio', data.evnPLData.MedPersonal_Fio);
						record.set('Person_Birthday', data.evnPLData.Person_Birthday);
						record.set('Person_Surname', data.evnPLData.Person_Surname);
						record.set('Person_Firname', data.evnPLData.Person_Firname);
						record.set('Person_Secname', data.evnPLData.Person_Secname);
						record.set('Person_id', data.evnPLData.Person_id);
						record.set('PersonEvn_id', data.evnPLData.PersonEvn_id);
						record.set('Server_id', data.evnPLData.Server_id);
						record.set('EvnCostPrint_setDT', data.evnPLData.EvnCostPrint_setDT);
						record.set('EvnCostPrint_IsNoPrintText', data.evnPLData.EvnCostPrint_IsNoPrintText);

						record.commit();
					}

					current_window.checkPrintCost();
				}
				else {
					grid.getStore().loadData([ data.evnPLData ], true);
				}

				grid.getStore().each(function(rec) {
					if ( rec.get('Person_id') == data.evnPLData.Person_id && rec.get('Server_id') == data.evnPLData.Server_id ) {
						rec.set('Person_Birthday', data.evnPLData.Person_Birthday);
						rec.set('Person_Surname', data.evnPLData.Person_Surname);
						rec.set('Person_Firname', data.evnPLData.Person_Firname);
						rec.set('Person_Secname', data.evnPLData.Person_Secname);

						rec.commit();
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

					getWnd('swEvnPLEditWindow').show(params);
				},
				personFirname: current_window.findById('EvnPLSearchFilterForm').getForm().findField('Person_Firname').getValue(),
				personSecname: current_window.findById('EvnPLSearchFilterForm').getForm().findField('Person_Secname').getValue(),
				personSurname: current_window.findById('EvnPLSearchFilterForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			var evn_pl_id = selected_record.get('EvnPL_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = selected_record.get('archiveRecord');
			}

			if ( evn_pl_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.onPersonChange = function(data) {
					if (data.Evn_id) {
						selected_record.set('EvnPL_id', data.Evn_id);
						selected_record.set('PersonEvn_id', data.PersonEvn_id);
						selected_record.set('Person_id', data.Person_id);
						selected_record.set('Server_id', data.Server_id);
						selected_record.set('Person_Surname', data.Person_SurName);
						selected_record.set('Person_Firname', data.Person_FirName);
						selected_record.set('Person_Secname', data.Person_SecName);
						selected_record.commit();
					}
				};

				params.EvnPL_id = evn_pl_id;
				params.onHide = function() {
					if ( grid.getStore().indexOf(selected_record) >= 0 ) {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};
				params.Person_id =  person_id;
				params.Server_id = server_id;

				getWnd('swEvnPLEditWindow').show(params);
			}
		}
	},
	plain: true,
	printEvnPL: function() {
		var current_window = this;
		var grid = current_window.getActiveViewFrame().ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_pl_id = grid.getSelectionModel().getSelected().get('EvnPL_id');

		if ( evn_pl_id > 0 ) {
			printEvnPL({
				type: 'EvnPL',
				EvnPL_id: evn_pl_id
			});
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLSearchWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('EvnPLSearchFilterForm').getForm();

		if ( getRegionNick() == 'perm' ) {
			if ( !this.Wizard.EvnPLSearchFrame.getAction('action_changeperson') ) {
				this.Wizard.EvnPLSearchFrame.addActions({
					disabled: true,
					handler: function() {
						this.changePerson();
					}.createDelegate(this),
					iconCls: 'doubles16',
					name: 'action_changeperson',
					text: 'Сменить пациента в учетном документе'
				});
			}
		}

		if ( !this.Wizard.EvnPLSearchFrame.getAction('action_setevnistransit') ) {
			this.Wizard.EvnPLSearchFrame.addActions({
				disabled: true,
				handler: function() {
					this.setEvnIsTransit();
				}.createDelegate(this),
				iconCls: 'actions16',
				id: this.id + 'action_setevnistransit',
				name: 'action_setevnistransit',
				text: 'Переходный случай'
			});
		}

		this.Wizard.EvnPLSearchFrame.setActionHidden('action_setevnistransit', !lpuIsTransit());

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
		this.Wizard.EvnPLSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		this.Wizard.EvnVizitPLSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;

		var tabKeys = this.findById('EPLSW_SearchFilterTabbar').items.keys;
		for(var i=tabKeys.length-1; i>=0; i--) {
			this.findById('EPLSW_SearchFilterTabbar').setActiveTab(tabKeys[i]);
		}

		this.findById('EPLSW_IsPaidLabel').hide();

		base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
		base_form.findField('PL_PrehospDirect_id').getStore().loadData([{PrehospDirect_id:99,PrehospDirect_Name:'(неизвестно)',PrehospDirect_Code:'0'}],true);
		base_form.findField('DeseaseType_id').getStore().loadData([{DeseaseType_id:99,DeseaseType_Name:'(неизвестно)',DeseaseType_Code:'0'}],true);

		switch ( getRegionNick() ) {
			case 'ufa':
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
			break;
			case 'perm':
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'gost2011' ]);
			break;
		}

		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) //https://redmine.swan.perm.ru/issues/78988
		{
			var params = {};
			params.Lpu_id = getGlobalOptions().lpu_id;
			base_form.findField('LpuRegion_Fapid').getStore().load({
				params: params
			});
		}
	},
	title: WND_POL_EPLSEARCH,
	width: 800
});