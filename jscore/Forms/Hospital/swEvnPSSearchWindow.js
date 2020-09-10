/**
* swEvnPSSearchWindow - окно поиска карт выбывших из стационара.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-05.08.2009
* @comment      Префикс для id компонентов EPSSW (EvnPSSearchWindow)
*
*
* Использует: окно редактирования талона амбулаторного пациента (swEvnPSEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
sw.Promed.swEvnPSSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	buttonAlign: 'left',
	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') ) {
			return false;
		}

		var form = this;
		var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPS_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		var params = {
			Evn_id: record.get('EvnPS_id')
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				form.setAnotherPersonForDocument(params);
			},
			personFirname: form.findById('EvnPSSearchFilterForm').getForm().findField('Person_Firname').getValue(),
			personSecname: form.findById('EvnPSSearchFilterForm').getForm().findField('Person_Secname').getValue(),
			personSurname: form.findById('EvnPSSearchFilterForm').getForm().findField('Person_Surname').getValue(),
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при переоформлении документа на другого человека'));
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
							title: langs('Вопрос')
						});
					}
					else {
						grid.getStore().remove(grid.getSelectionModel().getSelected());

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid, 'data');
						}

						getWnd('swPersonSearchWindow').hide();

                        var info_msg = langs('Документ успешно переоформлен на другого человека');
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
                        sw.swMsg.alert(langs('Сообщение'), info_msg, function() {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						});
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При переоформлении документа на другого человека произошли ошибки'));
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

		var grid = this.Wizard.EvnPSSearchFrame.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPS_id') || grid.getSelectionModel().getSelected().get('EvnPS_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPS_id'),
			Evn_IsTransit: Evn_IsTransit
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: langs('Установка признака "Переходный случай между МО"...') });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при установке признака "Переходный случай между МО"'));
					}
					else {
						record.set('EvnPS_IsTransit', Evn_IsTransit);
						record.commit();
						this.Wizard.EvnPSSearchFrame.onRowSelect(null, null, record);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при установке признака "Переходный случай между МО"'));
				}
			}.createDelegate(this),
			params: params,
			url: C_SETEVNISTRANSIT
		});
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPS: function(options) {
		options = options || {};
		var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPS_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_id = record.get('EvnPS_id');

		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPS(options);
					}
				}
			},
			'702': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						options.ignoreEvnDrug = true;
						scope.deleteEvnPS(options);
					}
				}
			}
		};

		//BOB - 21.01.2019  контроль наличия РП
		if (!options.ignoreReanimatPeriodClose) {
			//alert("111");
			var that = this;
			Ext.Ajax.request({
				callback: function (opt, success, response) {
					if (success && response.responseText != 'false') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						//console.log('BOB_response_obj=', response_obj);
						if (response_obj.success == true) {
							options.ignoreReanimatPeriodClose = true;
							that.deleteEvnPS(options);
						} else {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
							return false;
						} 
					}
					else {
						that.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при проверке закрытия Реанимационного периода.');
					}
				},
				params: {
					Object_id: Evn_id,
					Object: 'EvnPS'
				},
				url: '/?c=EvnReanimatPeriod&m=checkBeforeDelEvn'
			});
			return false;
		}
		//BOB - 21.01.2019

		var params = {Evn_id: Evn_id};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreEvnDrug) {
			params.ignoreEvnDrug = options.ignoreEvnDrug;
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
										a_params.fn(buttonId, this);
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									icon: Ext.MessageBox.QUESTION,
									title: langs('Вопрос')
								});
							} else {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при удалении КВС'));
							}
						}
						else {
							grid.getStore().remove(record);

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid, 'data');
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении КВС возникли ошибки'));
					}
				}.createDelegate(this),
				params: params,
				url: C_EVN_DEL
			});
		}.createDelegate(this);

		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreQuestion = true;
						doDelete();
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Удалить карту?'),
				title: langs('Вопрос')
			});
		}
	},
	EvnPSCopy: function(evnParams) {
		var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid(),
			selected_record = grid.getSelectionModel().getSelected(),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: "Создание копии КВС..." }),
			evnOptions = {},
			_this = this;

		evnOptions.ignoreEvnPSDoublesCheck = (!Ext.isEmpty(evnParams.ignoreEvnPSDoublesCheck) && evnParams.ignoreEvnPSDoublesCheck === 1) ? 1 : 0;
		evnOptions.ignoreUslugaComplexTariffCountCheck = 1;
		evnOptions.vizit_direction_control_check = 1;
		evnOptions.ignoreParentEvnDateCheck = 1;

		evnOptions.date = Ext.util.Format.date(evnParams.date, 'd.m.Y');
		evnOptions.EvnPS_id = selected_record.get('EvnPS_id');

		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						if (response_obj.Error_Msg && response_obj.Error_Msg === 'YesNo' && !Ext.isEmpty(response_obj.Alert_Msg)) {
							var msg = getMsgForCheckDoubles(response_obj);

							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										evnOptions.date = evnParams.date;

										switch (response_obj.Error_Code) {
											case 102:
												evnOptions.ignoreUslugaComplexTariffCountCheck = 1;
												break;
											case 109:
												evnOptions.ignoreParentEvnDateCheck = 1;
												break;
											case 112:
												evnOptions.vizit_direction_control_check = 1;
												break;
											case 113:
												evnOptions.ignoreEvnPSDoublesCheck = 1;
												break;
										}

										_this.EvnPSCopy(evnOptions);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: msg,
								title: langs(' Продолжить сохранение?')
							});
							return false;
						} else {
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при копировании КВС'));
						}
					}
					else {
						var params = {};

						params.action = 'edit';
						params.callback = function(data) {
							if ( !data || !data.evnPSData ) {
								return false;
							}

							// Обновить запись в grid
							var index = grid.getStore().findBy(function(rec) {
								return (rec.get('EvnPS_id') == data.evnPSData.EvnPS_id);
							});
							if ( index >= 0 ) {
								var record = grid.getStore().getAt(index);

								var grid_fields = new Array();
								var i = 0;

								grid.getStore().fields.eachKey(function(key, item) {
									grid_fields.push(key);
								});

								for ( i = 0; i < grid_fields.length; i++ ) {
									record.set(grid_fields[i], data.evnPSData[grid_fields[i]]);
								}

								record.commit();
							}
							else {
								if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPS_id') ) {
									grid.getStore().removeAll();
								}

								grid.getStore().loadData({'data': [ data.evnPSData ]}, true);
							}

							grid.getStore().each(function(record) {
								if ( record.get('Person_id') == data.evnPSData.Person_id && record.get('Server_id') == data.evnPSData.Server_id ) {
									record.set('Person_Birthday', data.evnPSData.Person_Birthday);
									record.set('Person_Surname', data.evnPSData.Person_Surname);
									record.set('Person_Firname', data.evnPSData.Person_Firname);
									record.set('Person_Secname', data.evnPSData.Person_Secname);

									record.commit();
								}
							});
						};

						var evn_ps_id = response_obj.EvnPS_id;
						var person_id = selected_record.get('Person_id');
						var server_id = selected_record.get('Server_id');

						params.EvnPS_id = evn_ps_id;
						params.isCopy = true;
						params.onHide = function() {
							grid.getStore().reload();
							grid.getView().focusRow(0);
						};
						params.Person_id = person_id;
						params.Server_id = server_id;

						if ( response_obj.Alert_Msg ) {
							sw.swMsg.alert(langs('Предупреждение'), response_obj.Alert_Msg, function() {
								getWnd('swEvnPSEditWindow').show(params);
							});
						}
						else {
							getWnd('swEvnPSEditWindow').show(params);
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При копировании КВС возникли ошибки'));
				}
			},
			params: evnOptions,
			url: '/?c=EvnPS&m=copyEvnPS'
		});
	},
	doEvnPSCopy: function() {
		var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid(),
			_this = this;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		if ( getWnd('swDateSetWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно выбора даты уже открыто'));
			return false;
		}

		if ( getWnd('swEvnPSEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования карты выбывшего из стационара уже открыто'));
			return false;
		}

		getWnd('swDateSetWindow').show({
			callback: function(date) {
				var evnParams = {
					date: date
				};
				_this.EvnPSCopy(evnParams)
			},
			onHide: Ext.emptyFn
		});
	},
	doReset: function() {
		var base_form = this.findById('EvnPSSearchFilterForm').getForm();

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

		if ( base_form.findField('PrehospDirect_id') != null ) {
			base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), null);
		}

		base_form.findField('EvnLeave_IsNotSet').fireEvent('check', base_form.findField('EvnLeave_IsNotSet'), false);

		if ( base_form.findField('PrivilegeStateType_id') != null ) {
			base_form.findField('PrivilegeStateType_id').fireEvent('change', base_form.findField('PrivilegeStateType_id'), 1, 0);
		}

		this.findById('EPSSW_SearchFilterTabbar').setActiveTab(5);
		this.findById('EPSSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EPSSW_SearchFilterTabbar').getActiveTab());

		this.findById('EPSSW_EvnPSSearchGrid').getGrid().getStore().removeAll();

		this.leaveTypeFilter();
		this.resultDeseaseFilter();
	},
	doSearch: function(params) {
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
	
		var base_form = this.findById('EvnPSSearchFilterForm').getForm();
		
		if ( this.findById('EvnPSSearchFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}
		
		var grid = this.getActiveViewFrame().ViewGridPanel;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					log(this.findById('EvnPSSearchFilterForm').getFirstInvalidEl());
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
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnPSSearchFilterForm'));

		if(getRegionNick().inlist(['ekb', 'penza'])){
			switch(base_form.findField('EvnPS_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
				case 1:
					post.EvnPS_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
					break;
				case 2:
					post.EvnPS_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
					break;
				default:
					post.EvnPS_InRegistry = 0;// все КВС
			}
		}
		else if(post.EvnPS_InRegistry){
			delete post.EvnPS_InRegistry;
		}

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		if ( base_form.findField('MedStaffFact_cid') ) {
			var med_personal_record = base_form.findField('MedStaffFact_cid').getStore().getById(base_form.findField('MedStaffFact_cid').getValue());

			if ( med_personal_record ) {
				post.MedPersonal_cid = med_personal_record.get('MedPersonal_id');
			}
		}

		if ( base_form.findField('PrehospDirect_id') ) {
			var prehosp_direct_record = base_form.findField('PrehospDirect_id').getStore().getById(base_form.findField('PrehospDirect_id').getValue());

			if ( prehosp_direct_record ) {
				switch ( parseInt(prehosp_direct_record.get('SFPrehospDirect_Code')) ) {
					case 2:
						post.Lpu_did = base_form.findField('Org_did').getFieldValue('Lpu_id');
					break;

					case 4:
						post.OrgMilitary_did = base_form.findField('Org_did').getValue();
					break;

					case 3:
					case 5:
					case 6:
						post.Org_did = base_form.findField('Org_did').getValue();
					break;
				}
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

		if ( base_form.isValid() ) {
			this.findById('EPSSW_EvnPSSearchGrid').ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			//grid.getStore().baseParams = '';
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
	},
	draggable: true,
	exportRecordsToDbf: function(data) {
		var form = this.findById('EvnPSSearchFilterForm');
		var base_form = form.getForm();
		var record;
		
		/*if ( base_form.findField('MedStaffFactViz_id') ) {
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
		}*/
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование архива..."});
		loadMask.show();
		if(data.filter_type == 1){//https://redmine.swan.perm.ru/issues/96310
			var params = getAllFormFieldValues(form);

			if(getRegionNick().inlist(['ekb', 'penza'])){
				switch(base_form.findField('EvnPS_InRegistry').getValue()){// #107998 поиск с учетом включения в реестр
					case 1:
						params.EvnPS_InRegistry = 1;// нет: ekb - не установлен признак вхождения в реестр, penza - движения не отмечены в реестре
						break;
					case 2:
						params.EvnPS_InRegistry = 2;// да: ekb - признак вхождения в реестр, penza - движение в реестре
						break;
					default:
						params.EvnPS_InRegistry = 0;// все КВС
				}
			}
			else if(params.EvnPS_InRegistry){
				delete params.EvnPS_InRegistry;
			}
		}
		else // Если ищем без учета фильтра, то оставляем только ЛПУ и тип поиска
		{
			var params = new Object();
			params.lpu_id = getGlobalOptions().lpu_id;
			params.SearchFormType = this.getActiveViewFrame().object;
		}
		
		if ( params.PersonCardStateType_id == null ) {
			params.PersonCardStateType_id = 1;
		}

		if ( params.PrivilegeStateType_id == null ) {
			params.PrivilegeStateType_id = 1;
		}
		
		if (data && data.table_list && data.table_list != '') { //указываем список таблиц для экспорта
			params.table_list = data.table_list;
		}
		
		if (data && data.date_type && data.date_type != '') { //тип выгрузки данных по застрахованному
			params.date_type = data.date_type;
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
						sw.swMsg.alert('Экспорт КВС', '<a target="_blank" href="' + response_obj.url + '">Скачать архив с КВС</a>');
					}
					else {
						if ( response_obj.Error_Msg ) {
							sw.swMsg.alert(langs('Экспорт КВС'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Экспорт КВС'), langs('При формировании архива произошли ошибки'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При формировании архива произошли ошибки'));
				}
			},
			params: params,
			url: '/?c=Search&m=exportSearchResultsToDbf'
		});
	},
	getRecordsCount: function() {
		var base_form = this.findById('EvnPSSearchFilterForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnPSSearchFilterForm'));
		post.SearchFormType = this.getActiveViewFrame().object;

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		// Надо добавить передачу параметров по умолчанию для специфических вкладок

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	id: 'EvnPSSearchWindow',
	Wizard: {EvnPSSearchFrame:null, EvnSectionSearchFrame: null, Panel: null},
	getActiveViewFrame: function () 
	{
		return this.Wizard[this.Wizard.Panel.layout.activeItem.id];
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPSSW_SearchButton');
	},
	printCost: function() {
		var grid = this.Wizard.EvnPSSearchFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPS_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPS_id'),
				type: 'EvnPS',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.Wizard.EvnPSSearchFrame.getGrid();
		var menuPrint = this.Wizard.EvnPSSearchFrame.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPS_id')) {
				var disabledCodes = ['5', '104', '204'];
				if (getRegionNick() == 'perm') {
					disabledCodes = [];
				}
				var allowPrint = (
					(/*getRegionNick().inlist([ 'kareliya' ]) &&*/ !getRegionNick().inlist([ 'buryatiya', 'ufa', 'penza', 'pskov' ]) && !Ext.isEmpty(selected_record.get('PrehospWaifRefuseCause_id')))
					|| (getRegionNick().inlist([ 'buryatiya', 'pskov' ]) && selected_record.get('LeaveType_Code') == 603)
					|| (!Ext.isEmpty(selected_record.get('LeaveType_Code')) && !selected_record.get('LeaveType_Code').inlist(disabledCodes))
				);
				menuPrint.printCost.setDisabled(!allowPrint);
			}
		}
	},
	deleteEvent: function(event, options) {
		options = options || {};

        if ( !event.inlist(['EvnSection']) ) {
            return false;
        }

        var win = this;
        //var base_form = this.findById('EvnPSEditForm').getForm();
        var error = '';
        var grid = null;
        var question = '';
        var params = new Object();
        var url = '';

        switch ( event ) {
            case 'EvnSection':
                grid = this.findById('EPSSW_EvnSectionSearchGrid').getGrid();
                break;
        }

        if ( !grid || !grid.getSelectionModel().getSelected() ) {
            return false;
        }

        var selected_record = grid.getSelectionModel().getSelected();

        switch ( event ) {
            case 'EvnSection':
                error = 'При удалении случая движения пациента в стационаре возникли ошибки';
                question = 'Удалить случай движения пациента в стационаре?';
                url = '/?c=Evn&m=deleteEvn';

				if ( getRegionNick() == 'ufa' ) {
					if ( selected_record.get('EvnSection_IsPaid') == 2 ) {
						question = 'Данный случай оплачен, Вы действительно хотите удалить данный случай движения пациента в стационаре?';
					}
				}
				
                params['Evn_id'] = selected_record.get('EvnSection_id');
                break;
        }

		var alert = {
			EvnSection: {
				'701': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreDoc = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'702': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreEvnDrug = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'703': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreCheckEvnUslugaChange = true;
							scope.deleteEvent(event, options);
						}
					}
				}
			}
		};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreEvnDrug) {
			params.ignoreEvnDrug = options.ignoreEvnDrug;
		}

		if (options.ignoreCheckEvnUslugaChange) {
			params.ignoreCheckEvnUslugaChange = options.ignoreCheckEvnUslugaChange;
		}

		var doDelete = function() {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							if (response_obj.Alert_Msg) {
								var a_params = alert[event][response_obj.Alert_Code];
								sw.swMsg.show({
									buttons: a_params.buttons,
									fn: function(buttonId) {
										a_params.fn(buttonId, this);
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									icon: Ext.MessageBox.QUESTION,
									title: 'Вопрос'
								});
							} else {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
						} else {
							grid.getStore().remove(selected_record);

							if ( grid.getStore().getCount() == 0 ) {
								grid.getTopToolbar().items.items[1].disable();
								grid.getTopToolbar().items.items[2].disable();
								grid.getTopToolbar().items.items[3].disable();
								LoadEmptyRow(grid);
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
						
					}
					else {
						sw.swMsg.alert('Ошибка', error);
					}
				}.createDelegate(this),
				params: params,
				url: url
			});
		}.createDelegate(this);


		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreQuestion = true;
						doDelete();
					} else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: 'Вопрос'
			});
		}
    },
	initComponent: function() {
        var win = this;

		this.Wizard.EvnPSSearchFrame = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{name: 'action_add', handler: function() {this.openEvnPSEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function() {this.openEvnPSEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function() {this.openEvnPSEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function() {this.deleteEvnPS();}.createDelegate(this)},
				{name: 'action_refresh'},
				{
					name: 'action_print',
					menuConfig: {
						printObject: {text: langs('Печать КВС'), handler: function(){ this.doPrintEvnPS(); }.createDelegate(this)},
						printObject003: {text: langs('Печать "Форма №003/у"'), handler: function(){ this.doPrintEvnPS003(); }.createDelegate(this)},
						//printObjectListFull: {handler: function(){ this.printList(); }.createDelegate(this)},
						printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }},
						printObjectSpr: {name:'printObjectSpr', text: langs('Справка о фактической себестоимости'), hidden: (getRegionNick() != 'kz'), handler: function(){ this.doPrintEvnPSSpr(); }.createDelegate(this)}
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'EPSSW_EvnPSSearchGrid',
			object: 'EvnPS',
			onRowSelect: function(sm, index, record) {
				var lpu_id = getGlobalOptions().lpu_id;
				if(win.viewOnly == true){
					this.Wizard.EvnPSSearchFrame.getAction('action_add').setDisabled(win.viewOnly);
					this.Wizard.EvnPSSearchFrame.getAction('action_edit').setDisabled(win.viewOnly);
					this.Wizard.EvnPSSearchFrame.getAction('action_view').setDisabled(false);
					this.Wizard.EvnPSSearchFrame.getAction('action_delete').setDisabled(win.viewOnly);
					this.Wizard.EvnPSSearchFrame.getAction('action_changeperson').setDisabled(win.viewOnly);
					//this.Wizard.EvnPSSearchFrame.getAction('action_copy').setDisabled(win.viewOnly);
					//this.Wizard.EvnPSSearchFrame.getAction('action_setevnistransit').setDisabled(win.viewOnly);
				}
				else
				{
					this.Wizard.EvnPSSearchFrame.getAction('action_add').setDisabled(false);
					this.Wizard.EvnPSSearchFrame.getAction('action_edit').setDisabled(false);
					this.Wizard.EvnPSSearchFrame.getAction('action_view').setDisabled(false);
					this.Wizard.EvnPSSearchFrame.getAction('action_delete').setDisabled(false);
					this.Wizard.EvnPSSearchFrame.getAction('action_changeperson').setDisabled(false);
					//this.Wizard.EvnPSSearchFrame.getAction('action_copy').setDisabled(false);
					//this.Wizard.EvnPSSearchFrame.getAction('action_setevnistransit').setDisabled(false);
					// Запретить редактирование/удаление архивных записей
					if (getGlobalOptions().archive_database_enable) {
						this.Wizard.EvnPSSearchFrame.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
						this.Wizard.EvnPSSearchFrame.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
					}

					if ( record.get('EvnPS_id') ) {
						var disabled = false;
						if (getGlobalOptions().archive_database_enable) {
							disabled = disabled || (record.get('archiveRecord') == 1);
						}

						this.Wizard.EvnPSSearchFrame.setActionDisabled('action_changeperson', disabled);

						this.Wizard.EvnPSSearchFrame.setActionDisabled('action_setevnistransit', !(record.get('EvnPS_IsTransit') == 1));

						if ( !Ext.isEmpty(lpu_id) && lpu_id.toString().inlist(getLpuListForEvnPSCopy()) ) {
							this.Wizard.EvnPSSearchFrame.setActionDisabled('action_copy', disabled);
						}
					}
					else {
						this.Wizard.EvnPSSearchFrame.setActionDisabled('action_changeperson', true);

						this.Wizard.EvnPSSearchFrame.setActionDisabled('action_setevnistransit', true);

						if ( !Ext.isEmpty(lpu_id) && lpu_id.toString().inlist(getLpuListForEvnPSCopy()) ) {
							this.Wizard.EvnPSSearchFrame.setActionDisabled('action_copy', true);
						}
					}
				}

				win.checkPrintCost();
			}.createDelegate(this),
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnPS_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'EvnPS_IsTransit', type: 'int', hidden: true},
				{name: 'PrehospWaifRefuseCause_id', type: 'int', hidden: true},
				{name: 'EvnPS_NumCard', type: 'string', header: langs('№ карты'), width: 70},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 200},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 200},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 90},
				{name: 'Person_deadDT', type:'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 90},
				{name: 'EvnPS_setDate', type: 'date', format: 'd.m.Y', header: langs('Поступление'), width: 90},
				{name: 'EvnPS_disDate', type: 'date', format: 'd.m.Y', header: langs('Выписка'), width: 90},
				{name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 150 },
				{name: 'Diag_Name', type: 'string', header: langs('Диагноз'), width: 150 },
				{name: 'EvnPS_KoikoDni', type: 'int', header: langs('К/дни'), width: 90},
				{name: 'Person_IsBDZ',  header: langs('БДЗ'), type: 'checkcolumn', width: 30},
				{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 100 },
				{name: 'LeaveType_Name', type: 'string', header: langs('Исход'), width: 100 },
				{name: 'LeaveType_Code', type: 'string', hidden: true },
				{name: 'DeadSvid', type: 'checkbox', header: langs('Наличие свидетельства о смерти'), width: 100},
                {name: 'EvnSection_KSG', type: 'string', header: langs('КСГ'), width: 100, hidden: getRegionNick().inlist(['astra', 'krym'])},
                {name: 'EvnSection_KPG', type: 'string', header: 'КПГ', width: 100, hidden: !getRegionNick().inlist(['krym','kareliya'])},
				{name: 'EvnSection_KSGKPG', type: 'string', header: langs('КСГ/КПГ для расчета'), width: 250, hidden: getRegionNick() != 'astra'},
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), header: langs('Справка о стоимости лечения'), width: 150 },
				{ name: 'Evn_sendERSB', header: langs('Передано в ЭРСБ '), hidden: getRegionNick() != 'kz', width: 150, renderer: function(v, p, r) {
						return !Ext.isEmpty(v) && String(v)[0]=='6' ? '<a href="#" onClick="getWnd(\'swInfoKVSFromERSBWindow\').show({ARMType: this.ARMType,EvnID:'+String(v).substring(1)+'});">Да</a>' : '';
					} },
				{name: 'Hospitalization_id', header: langs('Передано в БГ'), width: 150, hidden: getRegionNick() !== 'kz', renderer: Ext.getCmp('EvnPSSearchWindow').renderHospitalization},
				{name: 'EvnPSLink_insDT', hidden: true, type: 'date'},
				{name: 'fedservice_iemk', type: 'checkcolumn', header: 'ИЭМК', width: 50 }
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this)
		});

		this.Wizard.EvnSectionSearchFrame = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{name: 'action_add', handler: function() {this.openEvnPSEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function() {this.openEvnPSEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function() {this.openEvnPSEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function() {this.deleteEvent('EvnSection');}.createDelegate(this)},
				{name: 'action_refresh'},
				{
					name: 'action_print',
					menuConfig: {
						printObject: {text: langs('Печать КВС'), handler: function(){ this.printEvnPS(); }.createDelegate(this)},
						printObject003: {text: langs('Печать "Форма №003/у"'), handler: function(){ this.doPrintEvnPS003(); }.createDelegate(this)},
						printObjectSpr: {name:'printObjectSpr', text: langs('Справка о фактической себестоимости'), hidden: (getRegionNick() != 'kz'), handler: function(){ this.doPrintEvnPSSpr(); }.createDelegate(this)}
						/*printObjectListFull: {handler: function(){ this.printList(); }.createDelegate(this)}*/
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'EPSSW_EvnSectionSearchGrid',
			object: 'EvnSection',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnSection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnSection_pid', type: 'int', header: 'ID', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PrehospWaifRefuseCause_id', type: 'int', hidden: true},
				{name: 'EvnPS_NumCard', type: 'string', header: langs('№ карты'), width: 70},
				{name: 'LpuSectionWard_Name', type: 'string', header: langs('Палата'), width: 120},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 200},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 200},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 90},
				{name: 'Person_deadDT', type:'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 90},
				{name: 'EvnSection_setDate', type: 'date', format: 'd.m.Y', header: langs('Поступление'), width: 90},
				{name: 'EvnSection_disDate', type: 'date', format: 'd.m.Y', header: langs('Выписка'), width: 90},
				{name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 150 },
				{name: 'Diag_Name', type: 'string', header: langs('Диагноз'), width: 150 },
				{name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 200 },
				{name: 'EvnSection_KoikoDni', type: 'int', header: langs('К/дни'), width: 90},
				{name: 'Mes_Code', type: 'string', header: getMESAlias(), width: 110},
				{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 100 },
				{name: 'EvnSection_IsAdultEscort', type: 'string', header: langs('Сопровождается взрослым'), width: 100 },
				{name: 'LeaveType_Name', type: 'string', header: langs('Исход'), width: 100 },
				{name: 'EvnSection_KSG', type: 'string', header: langs('КСГ'), width: 250, hidden: getRegionNick().inlist(['astra', 'krym'])},
				{name: 'EvnSection_KSGKPG', type: 'string', header: langs('КСГ/КПГ для расчета'), width: 250, hidden: getRegionNick() != 'astra'},
				{name: 'EvnSection_KPG', type: 'string', header: langs('КПГ'), width: 250, hidden: getRegionNick() == 'ufa'},
				{name: 'fedservice_iemk', type: 'checkcolumn', header: 'ИЭМК', width: 50 }
				
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
				if(win.viewOnly == true){
					this.getAction('action_add').setDisabled(true);
					this.getAction('action_edit').setDisabled(true);
					this.getAction('action_delete').setDisabled(true);
				}
				else
				{
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
				id: 'EvnPSSearchFrame',
				layout:'fit',
				items:[this.Wizard.EvnPSSearchFrame]
			},{
				id: 'EvnSectionSearchFrame',
				layout: 'fit',
				items:[this.Wizard.EvnSectionSearchFrame]
			}]
		});
		
		this.tabs = [{
					autoHeight: true,
//					autoScroll: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							this.getFilterForm().getForm().findField('EvnPS_NumCard').focus(250, true);
						}.createDelegate(this)
					},
					title: langs('<u>6</u>. Приемное и госпитализация'),

					// tabIndexStart: TABINDEX_EPSSW + 68
					items: [
						{
						border:false,
						layout:'column',
						items:[
							{
							border:false,
							layout:'form',
							items:[{
								enableKeyEvents: true,
								fieldLabel: langs('№ карты'),
								listeners: {
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											// Переход к последней кнопке в окне
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnPS_NumCard',
								tabIndex: TABINDEX_EPSSW + 68,
								width: 300,
								maskRe: /[^%]/,
								xtype: 'textfield'
							}]
							},{
								border:false,
								layout:'form',
								items:[{
										enableKeyEvents: true,
										fieldLabel: langs('Внутр. № карты'),
										name: 'EvnSection_insideNumCard',
										width: 100,
										maskRe: /[^%]/,
										xtype: 'textfield'
									}]
							}
						]
				}, {
						autoHeight: true,
						labelWidth: 130,
						style: 'padding: 0px;',
						title: langs('Направление'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							boxLabel: langs('Без электронного направления'),
							checked: false,
							fieldLabel: '',
							labelSeparator: '',
							listeners: {
								'check': function(field, checked) {
									var base_form = this.findById('EvnPSSearchFilterForm').getForm();

									if ( checked === true ) {
										base_form.findField('PrehospDirect_id').setValue(2);
										base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), 2);
									}
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPSSW + 69,
							name: 'EvnPS_IsWithoutDirection',
							xtype: 'checkbox'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									additionalRecord: {
										value: -1,
										text: langs('(неизвестно)'),
										code: 0
									},
									comboSubject: 'SFPrehospDirect',
									fieldLabel: langs('Кем направлен'),
									hiddenName: 'PrehospDirect_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSSearchFilterForm').getForm();
											var record = combo.getStore().getById(newValue);

											var lpu_section_combo = base_form.findField('LpuSection_did');
											var org_combo = base_form.findField('Org_did');

											lpu_section_combo.disable();
											org_combo.disable();
											base_form.findField('Lpu_IsFondHolder').disable();
											// base_form.findField('Lpu_IsFondHolder').clearValue();

											if ( record == undefined || record == null ) {
												return false;
											}
											
											switch ( parseInt(record.get('SFPrehospDirect_Code')) ) {
												case 1:
													lpu_section_combo.enable();
													org_combo.disable();
												break;

												case 2:
													base_form.findField('Lpu_IsFondHolder').enable();
												case 3:
												case 4:
												case 5:
												case 6:
													lpu_section_combo.disable();
													org_combo.enable();
												break;
											}
										}.createDelegate(this),
										'select': function(combo, record, index) {
											combo.fireEvent('change', combo, record.get(combo.valueField));
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EPSSW + 70,
									width: 200,
									xtype: 'swcommonsprcombo'
								}, {
									fieldLabel: langs('№ направления'),
									name: 'EvnDirection_Num',
									tabIndex: TABINDEX_EPSSW + 72,
									width: 100,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: langs('Фондодержатель'),
									hiddenName: 'Lpu_IsFondHolder',
									tabIndex: TABINDEX_EPSSW + 71,
									width: 100,
									xtype: 'swcommonsprcombo'
								}, {
									fieldLabel: langs('Дата направления'),
									name: 'EvnDirection_setDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPSSW + 73,
									width: 200,
									xtype: 'daterangefield'
								}]
							}]
						}, {
							hiddenName: 'LpuSection_did',
							tabIndex: TABINDEX_EPSSW + 74,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							name: 'Lpu_did',
							xtype: 'hidden'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('Организация'),
							hiddenName: 'Org_did',
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
								var base_form = this.findById('EvnPSSearchFilterForm').getForm();
								var combo = base_form.findField('Org_did');

								if ( combo.disabled ) {
									return false;
								}

								var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
								var prehosp_direct_id = prehosp_direct_combo.getValue();
								var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

								if ( !record ) {
									return false;
								}

								var prehosp_direct_code = parseInt(record.get('SFPrehospDirect_Code'));
								var org_type = '';

								switch ( prehosp_direct_code ) {
									case 2:
									case 5:
										org_type = 'lpu';
									break;

									case 4:
										org_type = 'military';
									break;

									case 3:
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
												Org_Name: org_data.Org_Name,
												Lpu_id: org_data.Lpu_id
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
							tabIndex: TABINDEX_EPSSW + 75,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 350,
							items: [{
								fieldLabel: langs('Тип госпитализации'),
								hiddenName: 'PrehospType_id',
								listWidth: 300,
								tabIndex: TABINDEX_EPSSW + 76,
								width: 200,
								xtype: 'swprehosptypecombo'
							}]
						}, {
							border: false,
							layout: 'form',
							width: 300,
							items: [{
								fieldLabel: langs('Способ доставки'),
								hiddenName: 'PrehospArrive_id',
								tabIndex: TABINDEX_EPSSW + 77,
								width: 150,
								xtype: 'swprehosparrivecombo'
							}]
						}, {
							border: false,
							layout: 'form',
							width: 300,
							items: [{
								fieldLabel: langs('Вид транспортировки'),
								hiddenName: 'LpuSectionTransType_id',
								tabIndex: TABINDEX_EPSSW + 	77,
								width: 150,
								xtype: 'swlpusectiontranstypecombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 350,
							items: [{
								//loadParams: {params: {where: ' where PayType_Code in (1,2,3,4,5,6,9)'}},
								listWidth: 300,
								tabIndex: TABINDEX_EPSSW + 78,
								width: 200,
								xtype: 'swpaytypecombo'
							}]
						}, {
							border: false,
							layout: 'form',
							width: 300,
							items: [{
								fieldLabel: langs('Вид опьянения'),
								hiddenName: 'PrehospToxic_id',
								tabIndex: TABINDEX_EPSSW + 79,
								width: 150,
								xtype: 'swprehosptoxiccombo'
							}]
						}]
					}, {
						fieldLabel: langs('Вид травмы'),
						hiddenName: 'PrehospTrauma_id',
						tabIndex: TABINDEX_EPSSW + 80,
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
								fieldLabel: langs('Противоправная'),
								hiddenName: 'EvnPS_IsUnlaw',
								tabIndex: TABINDEX_EPSSW + 81,
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								fieldLabel: langs('Нетранспортабельность'),
								hiddenName: 'EvnPS_IsUnport',
								tabIndex: TABINDEX_EPSSW + 82,
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}, {
							// #107998
							// Екатеринбург. Признак вхождения в реестр для КВС
							// Пенза. Признак вхождения в реестр для одного из движений из КВС
							border: false,
							layout: 'form',
							labelWidth: 303,
							hidden: ! getRegionNick().inlist(['ekb', 'penza']),
							items:[{
								name: 'EvnPS_InRegistry',
								fieldLabel: langs('Включение в реестр'),
								displayField: 'EvnPS_InRegistry_Name',
								valueField: 'EvnPS_InRegistry_id',
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
										{name: 'EvnPS_InRegistry_id', type: 'int'},
										{name: 'EvnPS_InRegistry_Name', type: 'string'}
									],
									key: 'EvnPS_InRegistry_id'
								}),
								width: 72,
								listWidth: 70,
								tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">&nbsp;{EvnPS_InRegistry_Name}</div></tpl>'),
								xtype: 'swbaselocalcombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: {
								allowNegative: false,
								// allowDecimals: false,
								fieldLabel: langs('Кол-во госп-ций с'),
								name: 'EvnPS_HospCount_Min',
								tabIndex: TABINDEX_EPSSW + 83,
								width: 61,
								xtype: 'numberfield'
							}
						}, {
							border: false,
							labelWidth: 40,
							layout: 'form',
							items: {
								allowNegative: false,
								// allowDecimals: false,
								fieldLabel: langs('по'),
								name: 'EvnPS_HospCount_Max',
								tabIndex: TABINDEX_EPSSW + 84,
								width: 61,
								xtype: 'numberfield'
							}
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: {
								fieldLabel: langs('Время от нач. заб. с'),
								hiddenName: 'Okei_id',
								displayField: 'Okei_Name',
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{Okei_Name}',
									'</div></tpl>'
								),
								tabIndex: TABINDEX_EPSSW + 85,
								width: 80,
								xtype: 'swokeicombo',
								loadParams: {params: {where: ' where Okei_id in (100,101,102,104,107)'}},
								hidden: !(getRegionNick().inlist(['perm']))
							}
						}, {
							border: false,
							layout: 'form',
							items: {
								allowNegative: false,
								hideLabel: true,
								name: 'EvnPS_TimeDesease_Min',
								tabIndex: TABINDEX_EPSSW + 86,
								width: 61,
								xtype: 'numberfield'
							}
						}, {
							border: false,
							layout: 'form',
							labelWidth: 40,
							items: {
								allowNegative: false,
								fieldLabel: langs('по'),
								name: 'EvnPS_TimeDesease_Max',
								tabIndex: TABINDEX_EPSSW + 86,
								width: 61,
								xtype: 'numberfield'
							}
						}]
						}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата поступления'),
								name: 'EvnPS_setDate_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPSSW + 87,
								width: 180,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата выписки'),
								name: 'EvnPS_disDate_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPSSW + 88,
								width: 200,
								xtype: 'daterangefield'
							}]
						},
                            {
                                border: false,
                                layout: 'form',
                                items: [{
                                    xtype  : 'combo',
                                    store  : new Ext.data.SimpleStore({
                                        fields : ['id','value'],
                                        data   : [
                                            ['1' , langs('По календарным суткам')],
                                            ['2' , langs('По статистическим суткам ')]
                                        ]
                                    }),
                                    name        : 'Date_Type' ,
                                    hiddenName  : 'Date_Type' ,
                                    fieldLabel  : langs('Тип'),
                                    displayField: 'value',
                                    valueField  : 'id',
                                    triggerAction : 'all',
                                    mode        : 'local',
                                    editable    : false,
                                    width: 200
                            }]
                            }
                        ]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								hiddenName: 'LpuSection_hid',
								fieldLabel: langs('Госпитализирован в'),
								id: 'EPSSW_LpuSectionComboGosp',
								width: 450,
								xtype: 'swlpusectionglobalcombo', 
								listeners: 
								{
									'select': function (combo,record,index) 
									{
										if( !Ext.isEmpty(record.get('LpuSection_hid')) )
										{
											var rc_combo = this.findById('EPSSW_PrehospWaifRefuseCause_id');
											var oldValue = rc_combo.getValue();
											rc_combo.clearValue();
											rc_combo.fireEvent('change',rc_combo,'',oldValue);
										}
									}.createDelegate(this)
								}
							}]
						}, {
							border: false,
							labelWidth: 50,
							layout: 'form',
							items: [{
								hiddenName: 'PrehospWaifRefuseCause_id',
								id: 'EPSSW_PrehospWaifRefuseCause_id',
								fieldLabel: langs('Отказ'),
								width: 250,
								comboSubject: 'PrehospWaifRefuseCause',
								autoLoad: true,
								xtype: 'swcommonsprcombo', 
								listeners: 
								{
									'change': function (combo,newValue,oldValue) 
									{
										if(!Ext.isEmpty(newValue))
										{
											this.findById('EPSSW_LpuSectionComboGosp').clearValue();
										}
									}.createDelegate(this)
								}
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Тип стационара'),
								hiddenName: 'LpuUnitType_did',
								listeners: {
									'render': function(combo) {
										combo.lastQuery = '';
										combo.getStore().filterBy(function(rec) {
											if ( rec.get('LpuUnitType_Code') >= 2 && rec.get('LpuUnitType_Code') <= 5 ) {
												return true;
											}
											else {
												return false;
											}
										});
									}
								},
								tabIndex: TABINDEX_EPSSW + 89,
								width: 300,
								xtype: 'swlpuunittypecombo'
							}]
						}, {
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [{
								fieldLabel: 'Форма помощи',
								hiddenName: 'MedicalCareFormType_id',
								comboSubject: 'MedicalCareFormType',
								tabIndex: TABINDEX_EPSSW + 89,
								width: 250,
								prefix: 'nsi_',
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						hidden: getRegionNick() != 'kz',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								xtype  : 'combo',
								store  : new Ext.data.SimpleStore({
									fields : ['id','value'],
									data   : [
										['1' , langs('Да')],
										['2' , langs('Нет')]
									]
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{value}&nbsp;',
									'</div></tpl>'
								),
								name        : 'toERSB' ,
								hiddenName  : 'toERSB' ,
								fieldLabel  : langs('Передано в ЭРСБ'),
								displayField: 'value',
								valueField  : 'id',
								triggerAction : 'all',
								mode        : 'local',
								editable    : false,
								width: 200
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								fieldLabel: 'Передано в БГ',
								hiddenName: 'Hospitalization_id',
								tabIndex: TABINDEX_EPSSW + 90,
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}]
					},
						{
							autoHeight: true,
							labelWidth: 130,
							style: 'padding: 10px 0px 5px 0px;',
							title: langs('Реанимационный период'),
							width: 755,
							xtype: 'fieldset',

							items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: langs('Начало периода'),
											name: 'EvnReanimatPeriod_setDate',
											plugins: [
												new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
											],
											tabIndex: TABINDEX_EPSSW + 91,
											width: 160,
											xtype: 'daterangefield'
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: langs('Окончание периода'),
											name: 'EvnReanimatPeriod_disDate',
											plugins: [
												new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
											],
											tabIndex: TABINDEX_EPSSW + 92,
											width: 160,
											xtype: 'daterangefield'
										}]
									}
									]
								}
							]
						}
					]
				}, {
					autoHeight: true,
//					autoScroll: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('LpuBuilding_cid').getStore().getCount() == 0 ) {
								swLpuBuildingGlobalStore.clearFilter();
								this.getFilterForm().getForm().findField('LpuBuilding_cid').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
							}
							
							if ( this.getFilterForm().getForm().findField('LpuSection_cid').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									allowLowLevel: 'yes',
									isStac: true
								});
								this.getFilterForm().getForm().findField('LpuSection_cid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							if ( this.getFilterForm().getForm().findField('MedStaffFact_cid').getStore().getCount() == 0 ) {
								setMedStaffFactGlobalStoreFilter({
									allowLowLevel: 'yes',
									isStac: true
								});
								this.getFilterForm().getForm().findField('MedStaffFact_cid').ignoreDisableInDoc = true;
								this.getFilterForm().getForm().findField('MedStaffFact_cid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							this.getFilterForm().getForm().findField('MedStaffFact_cid').focus(250, true);
						}.createDelegate(this)
					},
					title: langs('<u>7</u>. Лечение'),

					// tabIndexStart: TABINDEX_EPSSW + 95
					items: [{
						name: 'MedPersonal_cid',
						xtype: 'hidden'
					},{
						fieldLabel: langs('Лечащий врач'),
						hiddenName: 'MedStaffFact_cid',
						id: 'EPSSW_MedStaffFactCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EPSSW_LpuSectionCombo',
						tabIndex: TABINDEX_EPSSW + 95,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Установленный диагноз'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									disabled: false,
									listeners: {
										'render': function(combo) {
											combo.getStore().load();
										}
									},
									listWidth: 300,
									tabIndex: TABINDEX_EPSSW + 96,
									width: 200,
									xtype: 'swdiagsetclasscombo'
								}, {
									checkAccessRights: true,
									disabled: false,
									fieldLabel: langs('Код диагноза с'),
									hiddenName: 'Diag_Code_From',
									listWidth: 650,
									tabIndex: TABINDEX_EPSSW + 98,
									valueField: 'Diag_Code',
									width: 200,
									xtype: 'swdiagcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									disabled: false,
									listeners: {
										'render': function(combo) {
											combo.getStore().load();
										}
									},
									listWidth: 300,
									tabIndex: TABINDEX_EPSSW + 97,
									width: 200,
									xtype: 'swdiagsettypecombo'
								}, {
									checkAccessRights: true,
									disabled: false,
									fieldLabel: langs('по'),
									hiddenName: 'Diag_Code_To',
									listWidth: 650,
									tabIndex: TABINDEX_EPSSW + 99,
									valueField: 'Diag_Code',
									width: 200,
									xtype: 'swdiagcombo'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Отделение'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							hiddenName: 'LpuBuilding_cid',
							fieldLabel: langs('Подразделение'),
							id: 'EPSSW_LpuBuildingCombo',
							lastQuery: '',
							linkedElements: [
								'EPSSW_LpuSectionCombo'
							],
							listWidth: 650,
							tabIndex: TABINDEX_EPSSW + 100,
							width: 500,
							xtype: 'swlpubuildingglobalcombo'
						},{
							hiddenName: 'LpuSection_cid',
							id: 'EPSSW_LpuSectionCombo',
							lastQuery: '',
							linkedElements: [
								'EPSSW_MedStaffFactCombo'
							],
							parentElementId: 'EPSSW_LpuBuildingCombo',
							listWidth: 650,
							tabIndex: TABINDEX_EPSSW + 101,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Выполненная услуга'),
						width: 780,
						xtype: 'fieldset',

						items: [{
							fieldLabel: langs('Дата выполнения'),
							name: 'EvnUsluga_setDate_Range',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: TABINDEX_EPSSW + 102,
							width: 200,
							xtype: 'daterangefield'
						}, {
							allowBlank: true,
							fieldLabel: langs('Категория услуги'),
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
										var uslugaCategoryList = new Array();

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
							loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus', 'MedOp')"}} : null),
							tabIndex: TABINDEX_EPSSW + 103,
							width: 250,
							xtype: 'swuslugacategorycombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: langs('Услуга с'),
									hiddenName: 'UslugaComplex_Code_From',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPSSW + 104,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 80,
								items: [{
									fieldLabel: langs('по'),
									hiddenName: 'UslugaComplex_Code_To',
									valueField: 'UslugaComplex_Code',
									listWidth: 590,
									tabIndex: TABINDEX_EPSSW + 105,
									width: 200,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
                            hidden: getGlobalOptions().region.nick != 'ufa',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'HTMedicalCareType',
									hiddenName: 'HTMedicalCareType_id',
									fieldLabel: langs('Вид ВМП'),
									listWidth: 650,
									tabIndex: TABINDEX_EPSSW + 104,
									width: 250,
									xtype: 'swcommonsprcombo',
                                    listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = win.findById('EvnPSSearchFilterForm').getForm(),
                                                HTMedicalCareClass_id = base_form.findField('HTMedicalCareClass_id').getValue();

                                            base_form.findField('HTMedicalCareClass_id').getStore().filterBy(function(record) {
                                                if (Ext.isEmpty(newValue)) {
                                                    return true;
                                                } else {
                                                    return (record.get('HTMedicalCareType_id') == newValue);
                                                }
                                            });

                                            if (!Ext.isEmpty(HTMedicalCareClass_id)) {
                                                var index = base_form.findField('HTMedicalCareClass_id').getStore().findBy(function(record) {
                                                   return (record.get('HTMedicalCareClass_id') == HTMedicalCareClass_id);
                                                });

                                                if ( index == -1 ) { // если индекса нет - очищаем
                                                    HTMedicalCareClass_id = null;
                                                }

                                            }

                                            //простановка первого значения
                                            /*if (Ext.isEmpty(HTMedicalCareClass_id) && base_form.findField('HTMedicalCareClass_id').getStore().getCount() > 0) {
                                                HTMedicalCareClass_id = base_form.findField('HTMedicalCareClass_id').getStore().getAt(0).get('HTMedicalCareClass_id');
                                            }*/
                                            base_form.findField('HTMedicalCareClass_id').setValue(HTMedicalCareClass_id);

										},
										'select': function(combo, record, index) {
											combo.fireEvent('change', combo, record.get(combo.valueField));
										}
									}
								}]
							}, {
								border: false,
                                layout: 'form',
                                labelWidth: 80,
								items: [{
									comboSubject: 'HTMedicalCareClass',
									fieldLabel: langs('Метод ВМП'),
									hiddenName: 'HTMedicalCareClass_id',
									lastQuery: '',
									listWidth: 650,
									moreFields: [
										{ name: 'HTMedicalCareClass_begDate', type: 'date', dateFormat: 'd.m.Y' },
										{ name: 'HTMedicalCareClass_endDate', type: 'date', dateFormat: 'd.m.Y' },
										{ name: 'HTMedicalCareClass_fid', type: 'int' },
										{ name: 'HTMedicalCareType_id', type: 'int' }
									],
									tabIndex: TABINDEX_EPSSW + 105,
									typeCode: 'int',
									width: 200,
									xtype: 'swcommonsprcombo'
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
								fieldLabel: langs('Дата поступления в отделение'),
								name: 'EvnSection_setDate_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPSSW + 106,
								width: 200,
								xtype: 'daterangefield'
							}, {
								tabIndex: TABINDEX_EPSSW + 108,
								fieldLabel: langs('Случай оплачен'),
								hiddenName: 'EvnSection_isPaid',
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата исхода из отделения'),
								name: 'EvnSection_disDate_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPSSW + 107,
								width: 200,
								xtype: 'daterangefield'
							}, {
								hidden: getRegionNick() == 'krym',
								border: false,
								layout: 'form',
								items: [{
									id: 'KsgCombo',
									xtype: 'swbaselocalcombo',
									mode: 'local',
									width: 200,
									listWidth: 500,
									fieldLabel: getRegionNick() == 'kz' ? langs('Код КЗГ') : langs('Код КСГ'),
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'Ksg_id', type: 'int'},
											{name: 'Ksg_Code', type: 'string'},
											{name: 'Ksg_Name', type: 'string'},
											{name: 'MesOld_Num', type:'string'},
											{name: 'MesType_Name', type: 'string'}
										],
										key: 'Ksg_id',
										sortInfo: {
											field: 'Ksg_Code'
										},
										url: C_KSG_CODE
									}),
									hiddenName: 'Ksg_id',
									tpl: new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
										'<td style="padding: 2px; width: 15%;">№</td>',
										'<td style="padding: 2px; width: 20%;">Код</td>',
										'<td style="padding: 2px; width: 50%;">Наименование</td>',
										'<td style="padding: 2px; width: 30%;">Тип</td>',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{Ksg_Code}&nbsp;</td>',
										'<td style="padding: 2px;">{MesOld_Num}&nbsp;</td>',
										'<td style="padding: 2px;">{Ksg_Name}&nbsp;</td>',
										'<td style="padding: 2px;">{MesType_Name}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									),
									valueField: 'Ksg_id',
									codeField: 'Ksg_Code',
									displayField: 'Ksg_Name',
									forceSelection: true,
									editable: true
								}]
							}, {
								hidden: getRegionNick() != 'krym',
								border: false,
								layout: 'form',
								items: [{
									xtype: 'swbaselocalcombo',
									mode: 'local',
									width: 200,
									listWidth: 500,
									fieldLabel: 'КПГ',
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'Kpg_id', type: 'int'},
											{name: 'Kpg_Code', type: 'string'},
											{name: 'Kpg_Name', type: 'string'},
											{name: 'MesType_Name', type: 'string'}
										],
										key: 'Kpg_id',
										sortInfo: {
											field: 'Kpg_Code'
										},
										url: C_KPG_CODE
									}),
									hiddenName: 'Kpg_id',
									tpl: new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
										'<td style="padding: 2px; width: 20%;">Код</td>',
										'<td style="padding: 2px; width: 50%;">Наименование</td>',
										'<td style="padding: 2px; width: 30%;">Тип</td>',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{Kpg_Code}&nbsp;</td>',
										'<td style="padding: 2px;">{Kpg_Name}&nbsp;</td>',
										'<td style="padding: 2px;">{MesType_Name}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									),
									valueField: 'Kpg_id',
									codeField: 'Kpg_Code',
									displayField: 'Kpg_Name',
									forceSelection: true,
									editable: true
								}]
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: langs('Сопровождается взрослым'),
								hiddenName: 'EvnSection_IsAdultEscort',
								tabIndex: TABINDEX_EPSSW + 107,
								width: 150,
								xtype: 'swcommonsprcombo'
							}, {
								hidden: getRegionNick() == 'krym',
								border: false,
								layout: 'form',
								items: [{
									id: 'yearcombo',
									xtype: 'combo',
									store: new Ext.data.SimpleStore({
										id: 0,
										fields: [
											'year'
										],
										data: []
									}),
									displayField: 'year',
									valueField: 'year',
									editable: false,
									mode: 'local',
									forceSelection: true,
									triggerAction: 'all',
									fieldLabel: getRegionNick() == 'kz' ? langs('Год КЗГ') : langs('Год КСГ'),
									width: 150,
									value: '2013',
									name: 'Ksg_Year',
									selectOnFocus: true,
									listeners: {
										'render': function (combo) {
											var data = [], i = 2013, year = getGlobalOptions().date.substr(6, 4);

											while (i <= year) {
												data.push([i]);
												i++;
											}

											combo.getStore().loadData(data);
										},
										'change': function (combo, nv, ov) {
											this.findById('KsgCombo').clearValue();
											this.findById('KsgCombo').getStore().load({params: {year: nv}});
										}.createDelegate(this)
									}
								}]
							}]
						}]
					}]
				}, {
					autoHeight: true,
                    //autoScroll: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 140,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							if ( this.getFilterForm().getForm().findField('LpuSection_oid').getStore().getCount() == 0 ) {
								setLpuSectionGlobalStoreFilter({
									isStac: true
								});
								this.getFilterForm().getForm().findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							}

							this.getFilterForm().getForm().findField('LeaveType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: langs('<u>8</u>. Результат лечения'),

					// tabIndexStart: TABINDEX_EPSSW + 108
					items: [{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: langs('Итог лечения'),
						hidden: !(getRegionNick().inlist([ 'astra', 'krasnoyarsk', 'krym', 'perm' ])),
						width: 755,
						xtype: 'fieldset',
						items: [{
							comboSubject: 'CureResult',
							fieldLabel: langs('Итог лечения'),
							hiddenName: 'CureResult_id',
							listWidth: 670,
							typeCode: 'int',
							width: 300,
							xtype: 'swcommonsprcombo'
						}]
					},{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: langs('Исход'),
						width: 755,
						xtype: 'fieldset',
						items: [{
							fieldLabel: langs('Не указан'),
							listeners: {
								'check': function(field, value) {
									var base_form = this.getFilterForm().getForm();

									if ( value == true ) {
										base_form.findField('LeaveType_id').clearValue();
										base_form.findField('LeaveType_id').disable();
										base_form.findField('ResultDesease_id').clearValue();
										base_form.findField('ResultDesease_id').disable();
										base_form.findField('LeaveCause_id').clearValue();
										base_form.findField('LeaveCause_id').disable();
										base_form.findField('Org_oid').clearValue();
										base_form.findField('Org_oid').disable();
										base_form.findField('LpuUnitType_oid').clearValue();
										base_form.findField('LpuUnitType_oid').disable();
										base_form.findField('LpuSection_oid').clearValue();
										base_form.findField('LpuSection_oid').disable();
										base_form.findField('EvnLeaveBase_UKL').setValue('');
										base_form.findField('EvnLeaveBase_UKL').disable();
										base_form.findField('EvnLeave_IsAmbul').clearValue();
										base_form.findField('EvnLeave_IsAmbul').disable();
									}
									else {
										base_form.findField('LeaveType_id').enable();
										base_form.findField('ResultDesease_id').enable();
										base_form.findField('LeaveCause_id').enable();
										base_form.findField('Org_oid').enable();
										base_form.findField('LpuUnitType_oid').enable();
										base_form.findField('LpuSection_oid').enable();
										base_form.findField('EvnLeaveBase_UKL').enable();
										base_form.findField('EvnLeave_IsAmbul').enable();
									}
								}.createDelegate(this)
							},
							name: 'EvnLeave_IsNotSet',
							tabIndex: TABINDEX_EPSSW + 107,
							xtype: 'checkbox'
						}, {
							comboSubject: 'LeaveType',
							fieldLabel: langs('Исход госпит-ции'),
							hiddenName: 'LeaveType_id',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										// Переход к последней кнопке в окне
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPSSW + 108,
							typeCode: 'int',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							comboSubject: 'ResultDesease',
							fieldLabel: langs('Исход заболевания'),
							hiddenName: 'ResultDesease_id',
							listWidth: 670,
							tabIndex: TABINDEX_EPSSW + 109,
							typeCode: 'int',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							comboSubject: 'LeaveCause',
							fieldLabel: langs('Прич. вып. / перевода'),
							hiddenName: 'LeaveCause_id',
							tabIndex: TABINDEX_EPSSW + 110,
							typeCode: 'int',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('ЛПУ'),
							hiddenName: 'Org_oid',
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
								var combo = base_form.findField('Org_oid');

								if ( combo.disabled ) {
									return false;
								}

								getWnd('swOrgSearchWindow').show({
									OrgType_id: 11,
									onClose: function() {
										combo.focus(true, 200)
									},
									onSelect: function(org_data) {
										if ( org_data.Org_id > 0 ) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
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
									{name: 'Org_Name', type: 'string'}
								],
								key: 'Org_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EPSSW + 111,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}, {
							autoLoad: false,
							comboSubject: 'LpuUnitType',
							fieldLabel: langs('Тип стационара'),
							hiddenName: 'LpuUnitType_oid',
							listeners: {
								// TODO: Оптимизировать рендер - по новым правилам возможно сразу передать условие при открытии формы (через params или loadParams)
								'render': function(combo) {
									combo.getStore().load({
										params: {
											where: 'where LpuUnitType_Code in (2, 3, 4, 5)'
										}
									})
								}
							},
							tabIndex: TABINDEX_EPSSW + 112,
							typeCode: 'int',
							width: 300,
							xtype: 'swcommonsprcombo'
						}, {
							hiddenName: 'LpuSection_oid',
							lastQuery: '',
							listWidth: 700,
							tabIndex: TABINDEX_EPSSW + 113,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowDecimals: true,
									allowNegative: false,
									fieldLabel: langs('УКЛ'),
									maxValue: 1,
									minValue: 0,
									name: 'EvnLeaveBase_UKL',
									tabIndex: TABINDEX_EPSSW + 114,
									width: 70,
									value: 1,
									xtype: 'numberfield'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 270,
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: langs('Направлен на амбулаторное долечивание'),
									hiddenName: 'EvnLeave_IsAmbul',
									tabIndex: TABINDEX_EPSSW + 114,
									width: 80,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: 'Требуется экспертиза',
									hiddenName: 'EvnDie_IsAnatom',
									tabIndex: TABINDEX_EPSSW + 115,
									width: 80,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						labelWidth: 140,
						style: 'padding: 0px;',
						title: langs('Выписка листа нетрудоспособности'),
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'StickType',
									fieldLabel: langs('Тип листа'),
									hiddenName: 'StickType_id',
									tabIndex: TABINDEX_EPSSW + 115,
									typeCode: 'int',
									width: 200,
									xtype: 'swcommonsprcombo'
								}, {
									comboSubject: 'StickCause',
									fieldLabel: langs('Причина выдачи'),
									hiddenName: 'StickCause_id',
									listWidth: 300,
									tabIndex: TABINDEX_EPSSW + 116,
									width: 200,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: 'Открыт',
									name: 'EvnStick_begDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPSSW + 117,
									width: 200,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Закрыт',
									name: 'EvnStick_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPSSW + 118,
									width: 200,
									xtype: 'daterangefield'
								}]
							}]
						}]
					}]
				}, {
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 140,
					layout: 'form',
					title: langs('<u>9</u>. Фед.сервисы'),
					items: [{
						comboSubject: 'ServiceEvnStatus',
						fieldLabel: 'ИЭМК',
						hiddenName: 'Service1EvnStatus_id',
						tabIndex: TABINDEX_EPSSW + 119,
						width: 200,
						xtype: 'swcommonsprcombo'
					}]
				}];

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_EPSSW + 120,
				id: 'EPSSW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPSSW + 121,
				text: BTN_FRMRESET
			}, /*{
				handler: function() {
					var base_form = this.findById('EvnPSSearchFilterForm').getForm();
					var record;

					base_form.findField('Lpu_did').setValue(null);
					
					if ( base_form.findField('PrehospDirect_id') ) {
						var prehosp_direct_record = base_form.findField('PrehospDirect_id').getStore().getById(base_form.findField('PrehospDirect_id').getValue());

						if ( prehosp_direct_record && parseInt(prehosp_direct_record.get('SFPrehospDirect_Code')) == 2) {
							base_form.findField('Lpu_did').setValue(base_form.findField('Org_did').getFieldValue('Lpu_id'));
						}
					}
					
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
				tabIndex: TABINDEX_EPSSW + 122,
				text: langs('Печать списка')
			},*/ {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPSSW + 123,
				text: BTN_FRMCOUNT
			}, {
				handler: function() {
					var ts = this;
					getWnd('swEvnPSExportMenu').show({
						callback: function (data) {
							ts.exportRecordsToDbf(data);
						}
					});
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSSW + 123,
				text: langs('Выгрузить в dbf')
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
					this.findById('EPSSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EPSSW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSSW + 124,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnPSSearchFilterForm');
				}
				return this.filterForm;
			},
//			width: this.getSize().width,
			items: [ getBaseSearchFiltersFrame({
				useArchive: 1,
				allowPersonPeriodicSelect: true,
				id: 'EvnPSSearchFilterForm',
				labelWidth: 130,
//				height: 245,
				ownerWindow: this,
				searchFormType: 'EvnPS',
				tabIndexBase: TABINDEX_EPSSW,
				tabPanelHeight: 385,
				tabPanelId: 'EPSSW_SearchFilterTabbar',
				ownerWindowPSWizardPanel: this.Wizard.Panel,
//				boxMaxWidth: 1920,
//				autoWidth: true,
//				collapseMode: 'mini',
//				plugins: [ Ext.ux.PanelCollapsedTitle ],
				tabs: this.tabs
			}),
			this.Wizard.Panel]
		});

		sw.Promed.swEvnPSSearchWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EPSSW_MedStaffFactCombo').addListener('keydown', function(inp, e) {
			if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
				e.stopEvent();
				// Переход к последней кнопке в окне
				this.buttons[this.buttons.length - 1].focus();
			}
		}.createDelegate(this));

		this.findById('EPSSW_LpuSectionCombo').addListener('change', function(inp, nv, ov) {
			this.leaveTypeFilter();
			this.resultDeseaseFilter();
		}.createDelegate(this));

		this.findById('EPSSW_MedStaffFactCombo').addListener('change', function(inp, nv, ov) {
			this.leaveTypeFilter();
			this.resultDeseaseFilter();
		}.createDelegate(this));
	},
	keys: [{
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPSSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					current_window.openEvnPSEditWindow('add');
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
			var current_window = Ext.getCmp('EvnPSSearchWindow');
			var search_filter_tabbar = current_window.findById('EPSSW_SearchFilterTabbar');

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
	resultDeseaseFilter: function () {
		var base_form = this.findById('EvnPSSearchFilterForm').getForm(), 
			ResultDesease_id = base_form.findField('ResultDesease_id').getValue();

		base_form.findField('ResultDesease_id').clearFilter();
		base_form.findField('ResultDesease_id').lastQuery = '';
		if ( getRegionNick().inlist([ 'adygeya']) ) {
			base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
				return (
					rec.get('ResultDesease_Code') < 400	
				);
			});
		}

	} ,
	leaveTypeFilter: function() {
		var base_form = this.findById('EvnPSSearchFilterForm').getForm();

		var
			LeaveType_id = base_form.findField('LeaveType_id').getValue(),
			LpuUnitType_SysNick = base_form.findField('LpuSection_cid').getFieldValue('LpuUnitType_SysNick');

		base_form.findField('LeaveType_id').clearFilter();
		base_form.findField('LeaveType_id').lastQuery = '';
		if ( getRegionNick().inlist([ 'kareliya', 'krym', 'adygeya']) ) {
			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
				if ( LpuUnitType_SysNick == 'stac' ) {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
							&& !(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115' ]))
						);
					});
				} else {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(getRegionNick() != 'kareliya' && LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
							&& !(rec.get('LeaveType_Code').toString().inlist([ '210', '211', '212', '213', '215' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
					return (
						(getRegionNick() == 'adygeya' && rec.get('LeaveType_Code') < 316) &&
						!(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115', '210', '211', '212', '213', '215' ]))
					);
				});
			}

			if ( !Ext.isEmpty(LeaveType_id) ) {
				var index = base_form.findField('LeaveType_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveType_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveType_id').clearValue();
					base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'));
				}
			}
		}
	},
	listeners: {
		'hide': function(win) {
			win.doReset();
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EvnPSSearchFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EvnPSSearchFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('EPSSW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('EvnPSSearchFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPSEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			//sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
			getWnd('swPersonSearchWindow').hide();
			//return false;
		}
/*
		if ( getWnd('swEvnPSEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования карты выбывшего из стационара уже открыто'));
			return false;
		}
*/
		var params = {};//окно редкатирования КВС вызовется с этими параметрами
		var grid = this.getActiveViewFrame().getGrid();
		var psserch = this;
		var evnPSIdField = 'EvnPS_id';

		params.action = action;

		if ( this.getActiveViewFrame().object == 'EvnPS' ) {
			params.callback = function(data) {//каллбэк по умолчанию после сохранения КВС
				if ( !data || !data.evnPSData ) {
					return false;
				}
				if (!psserch.isVisible()) {
					return false;
				}
				// Обновить запись в grid
				var index = grid.getStore().findBy(function(rec) {
					return (rec.get('EvnPS_id') == data.evnPSData.EvnPS_id);
				});
				if ( index >= 0 ) {
					var record = grid.getStore().getAt(index);

					var grid_fields = new Array();
					var i = 0;
					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});
					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.evnPSData[grid_fields[i]]);
					}
					record.commit();

					psserch.checkPrintCost();
				}
				else if ( Ext.isEmpty(grid.getStore().baseParams.SearchFormType) ) {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPS_id') ) {
						grid.getStore().removeAll();
					}
					grid.getStore().loadData({'data': [ data.evnPSData ]}, true);
				}
				else {
					grid.getStore().reload();
				}

				grid.getStore().each(function(record) {
					if ( record.get('Person_id') == data.evnPSData.Person_id && record.get('Server_id') == data.evnPSData.Server_id ) {
						record.set('Person_Birthday', data.evnPSData.Person_Birthday);
						record.set('Person_Surname', data.evnPSData.Person_Surname);
						record.set('Person_Firname', data.evnPSData.Person_Firname);
						record.set('Person_Secname', data.evnPSData.Person_Secname);
						record.set('EvnSection_KSGKPG', data.evnPSData.EvnSection_KSGKPG);
						record.commit();
					}
				});
			};
		}
		else {
			evnPSIdField = 'EvnSection_pid';

			if ( action == 'edit' ) {
				params.callback = function(data) {
					this.getActiveViewFrame().getGrid().getStore().reload();
				}.createDelegate(this);
			}
		}
		var childEvnPsSearchWindow = this;
		if (this.childPS) {
			//функция открытия окна создания/редактирования КВС ребенка
			var openPSEditWindowForChild = function (opener) {
				params.childPS = true;
				params.ChildTermType_id = childEvnPsSearchWindow.objectToReturn.ChildTermType_id;
				params.BirthSpecStac_CountChild = childEvnPsSearchWindow.objectToReturn.BirthSpecStac_CountChild;
				if (childEvnPsSearchWindow.objectToReturn.PersonChild_IsAidsMother) {
					params.PersonChild_IsAidsMother = childEvnPsSearchWindow.objectToReturn.PersonChild_IsAidsMother;
				}
				params.opener = opener; //запоминаю окно поиска человека, чтобы при сохранении КВС ребенка скрыть его
				getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'},{params:{id:'EvnPSEditWindow2'}}).show(params);
			};
		}
		if ( action == 'add' ) {
			var childPS = false;
			if (this.childPS) {
				childPS = true;
				params.callback = function(){//каллбэк на случай редактирования КВС ребенка
					//Этот каллбэк вызывается при удачном сохранении в окне КВС
					arguments[1].opener.hide();//скрываю окно поиска ребенка
					childEvnPsSearchWindow.hide();//скрываю окно поиска КВС
					childEvnPsSearchWindow.objectToReturn.result = arguments[0];//записываю результаты сохранения, некоторые данные о КВС ребенка в переданный объект
					childEvnPsSearchWindow.objectToReturn.callback();//в этом же объекте указан каллбэк, который надо выполнить после сохранения КВС ребенка. Выполняю его
				};
			}
			var personSearchParams = {
				onSelect: function(person_data) {
					//сработает при выборе человека
					params.onHide = function() {
						if (!psserch.isVisible()) {
							return false;
						}
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
						return true;
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					if (childPS){
						//выбираем ребенка для добавления в специфику беременности и родов
						var that = this;
						//проверим, может ли выбранный человек быть ребенком этой женщины
						//посылаем запрос на сервер
						Ext.Ajax.request({
							method:'post',
							params:{
								BirthSpecStac_OutcomeDate: Ext.util.Format.date(childEvnPsSearchWindow.objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y'),
								motherEvnSection_id: childEvnPsSearchWindow.objectToReturn.EvnSection_id,
								mother_Person_id: childEvnPsSearchWindow.objectToReturn.Person_id,
								child_Person_id: params.Person_id
							},
							success:function (response, options) {
								var resp = Ext.util.JSON.decode(response.responseText);
								var ok = resp[0].Success;//resp[0]....
								if (ok) {
									//все хорошо, продолжаю добавление
									openPSEditWindowForChild(that);
								} else {
									//показываю сообщение об ошибке
									sw.swMsg.alert(langs('Ошибка'), resp[0].Error_Msg);
								}
							},
							url:'/?c=BirthSpecStac&m=checkChild'
						});

					} else {
						if ( getRegionNick() == 'kz' ) {
							var conf = {
								Person_id: person_data.Person_id,
								PersonEvn_id: person_data.PersonEvn_id,
								Server_id: person_data.Server_id,
								win: this,
								callback: function() {
									getWnd('swEvnPSEditWindow').show(params);
								}.createDelegate(this)
							};
							sw.Promed.PersonPrivilege.checkExists(conf);
						} else {
							getWnd('swEvnPSEditWindow').show(params);
						}
					}
				},
				personFirname: this.findById('EvnPSSearchFilterForm').getForm().findField('Person_Firname').getValue(),
				personSecname: this.findById('EvnPSSearchFilterForm').getForm().findField('Person_Secname').getValue(),
				personSurname: this.findById('EvnPSSearchFilterForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			};
			getWnd('swPersonSearchWindow').show(personSearchParams);
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			var evn_ps_id = selected_record.get(evnPSIdField);
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');
			var ok = (evn_ps_id > 0) && (person_id > 0) && (server_id >= 0);

			if ( ok ) {
				params.onPersonChange = function(data) {
					if (data.Evn_id) {
						selected_record.set(evnPSIdField, data.Evn_id);
						selected_record.set('PersonEvn_id', data.PersonEvn_id);
						selected_record.set('Person_id', data.Person_id);
						selected_record.set('Server_id', data.Server_id);
						selected_record.set('Person_Surname', data.Person_SurName);
						selected_record.set('Person_Firname', data.Person_FirName);
						selected_record.set('Person_Secname', data.Person_SecName);
						selected_record.commit();
					}
				};

				params.EvnPS_id = evn_ps_id;
				params.onHide = function() {
					if (!psserch.isVisible()) {
						return false;
					}
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.Server_id = server_id;
				if (getGlobalOptions().archive_database_enable) {
					params.archiveRecord = selected_record.get('archiveRecord');
				}

				if (this.childPS) {

					params.callback = function(){
						childEvnPsSearchWindow.hide();//скрываю окно поиска КВС
						childEvnPsSearchWindow.objectToReturn.result = arguments[0];//возвращаю результат в объекте, который мне передали
						childEvnPsSearchWindow.objectToReturn.callback();
					};
					// окно поиска КВС вызывано для добавления ребенка,
					// следовательно окно редактирования
					// КВС будет уже вторым, поэтому его надо открывать с другим идентификатором
					params.childPS = true;
					params.ChildTermType_id = childEvnPsSearchWindow.objectToReturn.ChildTermType_id;
					params.BirthSpecStac_CountChild = childEvnPsSearchWindow.objectToReturn.BirthSpecStac_CountChild;
					//params.PersonChild_IsAidsMother = childEvnPsSearchWindow.objectToReturn.PersonChild_IsAidsMother;
					params.opener = this; //запоминаю окно поиска человека, чтобы при сохранении КВС ребенка скрыть его
					Ext.Ajax.request({
						method:'post',
						params:{
							BirthSpecStac_OutcomeDate: Ext.util.Format.date(childEvnPsSearchWindow.objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y'),
							motherEvnSection_id: childEvnPsSearchWindow.objectToReturn.EvnSection_id,
							mother_Person_id: childEvnPsSearchWindow.objectToReturn.Person_id,
							child_Person_id: params.Person_id
						},
						success:function (response, options) {
							var resp = Ext.util.JSON.decode(response.responseText);
							var ok = resp[0].Success;//resp[0]....
							if (ok) {
								//все хорошо, продолжаю добавление
								getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'},{params:{id:'EvnPSEditWindow2'}}).show(params);
							} else {
								//показываю сообщение об ошибке
								sw.swMsg.alert('Ошибка', resp[0].Error_Msg);
							}
						},
						url:'/?c=BirthSpecStac&m=checkChild'
					});

				} else {
					getWnd('swEvnPSEditWindow').show(params);
				}
			}
		}
	},
	plain: true,
	printEvnPS: function() {

		var KVS_Type = '';
		var evn_ps_id = '';
		var evn_section_id = '';

		if ( getRegionNick() == 'kz' ) {
			if ( this.getActiveViewFrame().object == 'EvnSection' ) {
				var grid = this.findById('EPSSW_EvnSectionSearchGrid').getGrid();

				if ( !grid.getSelectionModel().getSelected() ) {
					return false;
				}

				evn_ps_id = grid.getSelectionModel().getSelected().get('EvnSection_pid');
			}
			else if ( this.getActiveViewFrame().object == 'EvnPS' ) {
				var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

				if ( !grid.getSelectionModel().getSelected() ) {
					return false;
				}

				evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');
			}

			if ( !Ext.isEmpty(evn_ps_id) ) {
				// Нужно определять тип стационара, для этого нужно добавить поле LpuUnitType_id или LpuUnitType_SysNick в гриды
				// Пока печатаем КВС для круглосуточного стационара
				// https://redmine.swan.perm.ru/issues/39955
				printBirt({
					'Report_FileName': 'han_EvnPS_f066u.rptdesign',
					'Report_Params': '&paramEvnPS=' + evn_ps_id,
					'Report_Format': 'pdf'
				});
			}
		}
		else {
			if (this.getActiveViewFrame().object == 'EvnSection') //Список составлен по движениям
			{
				var grid = this.findById('EPSSW_EvnSectionSearchGrid').getGrid();
				if ( !grid.getSelectionModel().getSelected() ) {
					return false;
				}
				KVS_Type = 'VG';
				evn_section_id = grid.getSelectionModel().getSelected().get('EvnSection_id');
				if ( evn_section_id > 0 ) {
					window.open('/?c=EvnPS&m=printEvnPS&Parent_Code=2' + '&KVS_Type=' + KVS_Type + '&EvnSection_id=' + evn_section_id, '_blank');
				}
			}
			if (this.getActiveViewFrame().object == 'EvnPS') //Список составлен по КВС
			{
				var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();
				if ( !grid.getSelectionModel().getSelected() ) {
					return false;
				}
				KVS_Type = 'AB';
				evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');
				if ( evn_ps_id > 0 ) {
					var params = {};
					params.EvnPS_id = evn_ps_id;
					params.Parent_Code = 2;
					params.KVS_Type = KVS_Type;
					printEvnPS(params);
				}
			}
		}
	},
	doPrintEvnPS003: function() {
		var evn_ps_id = 0;
		if ( this.getActiveViewFrame().object == 'EvnSection' ) {
			var grid = this.findById('EPSSW_EvnSectionSearchGrid').getGrid();

			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			evn_ps_id = grid.getSelectionModel().getSelected().get('EvnSection_pid');
		}
		else if ( this.getActiveViewFrame().object == 'EvnPS' ) {
			var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');
		}

		if ( evn_ps_id > 0 ) {
			printEvnPS003({
				EvnPS_id: evn_ps_id
			});
		}
	},
	doPrintEvnPSSpr: function() {
		var evn_ps_id = 0;
		if ( this.getActiveViewFrame().object == 'EvnSection' ) {
			var grid = this.findById('EPSSW_EvnSectionSearchGrid').getGrid();

			if ( !grid.getSelectionModel().getSelected() ) {
				sw.swMsg.alert(langs('Ошибка'), 'Не выбрано движение КВС');
				return false;
			}

			evn_ps_id = grid.getSelectionModel().getSelected().get('EvnSection_pid');
		}
		else if ( this.getActiveViewFrame().object == 'EvnPS' ) {
			var grid = this.findById('EPSSW_EvnPSSearchGrid').getGrid();

			if ( !grid.getSelectionModel().getSelected() ) {
				sw.swMsg.alert(langs('Ошибка'), 'Не выбран КВС');
				return false;
			}

			evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');
		}

		if ( evn_ps_id > 0 ) {
			printBirt({
				'Report_FileName': 'hosp_Spravka_KSG.rptdesign',
				'Report_Params': '&paramEvnPS=' + evn_ps_id,
				'Report_Format': 'pdf'
			});
		} else {
			sw.swMsg.alert(langs('Ошибка'), 'Не выбран КВС');
		}
	},
	doPrintEvnPS: function() {
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			var that = this;
			var dialog_wnd = Ext.Msg.show({
				title: langs('Выбор варианта печати'),
				//msg: 'Выберите вариант печати формы заявления о выборе МО. Нажмите: <b>Лично</b> - от имени гражданина, лично. <b>Представитель</b> - от имени законного представителя. <b>Отмена</b> - отмена действия.',
				buttons: {yes: "Печать КВС", no: "Справка для следственных органов", cancel: "Отмена"},
				icon: Ext.MessageBox.QUESTION,
				fn: function(btn) {
					if( btn == 'cancel') {
						return;
					}
					if (btn == 'yes'){
						that.printEvnPS();
					}

					if (btn == 'no'){
						if (that.getActiveViewFrame().object == 'EvnPS'){ //Если список составлен по КВС
							var grid = that.findById('EPSSW_EvnPSSearchGrid').getGrid();
							if ( !grid.getSelectionModel().getSelected() ) {
								return langs('Не выбрана КВС');
							}
							var evn_ps_id = grid.getSelectionModel().getSelected().get('EvnPS_id');
							if(evn_ps_id > 0) {
								printBirt({
									'Report_FileName': 'hosp_Section_print.rptdesign',
									'Report_Params': '&paramEvnPS=' + evn_ps_id,
									'Report_Format': 'pdf'
								});
							}
						}
					}
				}
			});
		}
		else{
			this.printEvnPS();
		}
	},
	printList: function() {
		var base_form = this.findById('EvnPSSearchFilterForm').getForm();
		var record;

		base_form.findField('Lpu_did').setValue(null);

		if ( base_form.findField('PrehospDirect_id') ) {
			var prehosp_direct_record = base_form.findField('PrehospDirect_id').getStore().getById(base_form.findField('PrehospDirect_id').getValue());

			if ( prehosp_direct_record && parseInt(prehosp_direct_record.get('SFPrehospDirect_Code')) == 2) {
				base_form.findField('Lpu_did').setValue(base_form.findField('Org_did').getFieldValue('Lpu_id'));
			}
		}

		base_form.findField('MedPersonal_cid').setValue(null);
		if ( base_form.findField('MedStaffFact_cid') ) {
			var med_personal_record = base_form.findField('MedStaffFact_cid').getStore().getById(base_form.findField('MedStaffFact_cid').getValue());

			if ( med_personal_record ) {
				base_form.findField('MedPersonal_cid').setValue(med_personal_record.get('MedPersonal_id'));
			}
		}

		var baseParams = this.getActiveViewFrame().getGrid().getStore().baseParams;

		base_form.submit();
	},
	resizable: true,
	renderHospitalization: function (value, p, record)
	{
		if (getRegionNick() !== 'kz' || Ext.isEmpty(value))
		{
			return value;
		}

		var params = {scenario: "EvnPS",id: record.get('Hospitalization_id')};

		var ref = '<a href="#" onclick=\'getWnd("swTransfreredToBgInfoWindow").show(' + Ext.util.JSON.encode(params) + ')\'>Да</a>';

		return ref;

	},
	show: function() {
		sw.Promed.swEvnPSSearchWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnPSSearchFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		if ( !this.findById('EPSSW_EvnPSSearchGrid').getAction('action_copy') && !Ext.isEmpty(getGlobalOptions().lpu_id) && getGlobalOptions().lpu_id.toString().inlist(getLpuListForEvnPSCopy()) ) {
			this.findById('EPSSW_EvnPSSearchGrid').addActions({
				disabled: true,
				handler: function() {
					this.doEvnPSCopy();
				}.createDelegate(this),
				iconCls: 'copy16',
				name: 'action_copy',
				text: langs('Копия КВС')
			});
		}

		if ( !this.Wizard.EvnPSSearchFrame.getAction('action_changeperson') ) {
			this.Wizard.EvnPSSearchFrame.addActions({
				hidden: getRegionNick() != 'perm',
				disabled: true,
				handler: function() {
					this.changePerson();
				}.createDelegate(this),
				iconCls: 'doubles16',
				name: 'action_changeperson',
				text: langs('Сменить пациента в учетном документе')
			});
		}

		if ( !this.Wizard.EvnPSSearchFrame.getAction('action_setevnistransit') ) {
			this.Wizard.EvnPSSearchFrame.addActions({
				disabled: true,
				handler: function() {
					this.setEvnIsTransit();
				}.createDelegate(this),
				iconCls: 'actions16',
				id: this.id + 'action_setevnistransit',
				name: 'action_setevnistransit',
				text: langs('Переходный случай')
			});
		}

		this.Wizard.EvnPSSearchFrame.setActionHidden('action_setevnistransit', !lpuIsTransit());

		if ( getRegionNick() == 'ufa' ) {
			base_form.findField('HTMedicalCareType_id').getStore().load();
			base_form.findField('HTMedicalCareClass_id').getStore().load();
		}

		setLpuSectionGlobalStoreFilter();
		base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		setLpuSectionGlobalStoreFilter({
			isStac: true
		});
		base_form.findField('LpuSection_hid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		var currentYear = new Date().getFullYear();
		this.findById('yearcombo').setValue(currentYear);
		if (getRegionNick() == 'krym') {
			base_form.findField('Kpg_id').getStore().load({params:{year:currentYear}});
		} else {
			base_form.findField('Ksg_id').getStore().load({params: {year: currentYear}});
		}

		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) //https://redmine.swan.perm.ru/issues/78988
		{
			var params = new Object();
			params.Lpu_id = getGlobalOptions().lpu_id;
			base_form.findField('LpuRegion_Fapid').getStore().load({
				params: params
			});
		}
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		//не забываем обнулять переменные
		this.childPS = false;
		this.objectToReturn = null;
		this.onHide = Ext.emptyFn;
		this.viewOnly = false;
		if (arguments){
			if (arguments[0]) {
				if (arguments[0].childPS) {
					//открывается окно поиска карты ребенка
					this.childPS = true;
					this.objectToReturn = arguments[0].objectToReturn;
				}
				if (arguments[0].opener) {
					this.opener = arguments[0].opener;
				}
				if (arguments[0].onHide) {
					this.onHide = arguments[0].onHide;
				}
				if (arguments[0].viewOnly) {
					this.viewOnly = arguments[0].viewOnly;
				}
			}
		}
		this.doLayout();
		this.leaveTypeFilter();
		this.resultDeseaseFilter();

		this.Wizard.EvnPSSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		this.Wizard.EvnSectionSearchFrame.getAction('action_add').setDisabled(this.viewOnly);
		if (arguments){
			if (arguments[0]) {
				if (arguments[0].Person_Birthday) {
					this.findById('EvnPSSearchFilterForm').getForm().findField('Person_Birthday').setValue(arguments[0].Person_Birthday);
				}
				if (arguments[0].Person_Surname) {
					this.findById('EvnPSSearchFilterForm').getForm().findField('Person_Surname').setValue(arguments[0].Person_Surname);
					this.findById('EPSSW_SearchFilterTabbar').setActiveTab(0);
				}
				if (arguments[0].Person_Surname || arguments[0].Person_Birthday) {
					this.doSearch();
				}
			}
		}
        this.findById('EvnPSSearchFilterForm').getForm().findField('Date_Type').setValue('1');
/*
		// Устанавливаем фильтры на категории выбираемых услуг
		base_form.findField('UslugaComplex_Code_From').setDisallowedUslugaComplexAttributeList([ 'stom' ]);
		base_form.findField('UslugaComplex_Code_To').setDisallowedUslugaComplexAttributeList([ 'stom' ]);
*/
		base_form.findField('UslugaComplex_Code_From').getStore().baseParams.ignoreUslugaComplexDate = 1;
		base_form.findField('UslugaComplex_Code_To').getStore().baseParams.ignoreUslugaComplexDate = 1;
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
		var okei_combo = base_form.findField('Okei_id');
		okei_combo.setValue(100); // По умолчанию: час
	},
	title: WND_HOSP_EPSSEARCH,
	width: 800
});