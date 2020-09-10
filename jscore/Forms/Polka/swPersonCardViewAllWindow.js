/**
 * swPersonCardViewAllWindow - ЕРПН:Поиск(Картотека:Прикрепление)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version      27.09.2009
 * tabIndex: 3000
 */

sw.Promed.swPersonCardViewAllWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	doResetAll: function () {
		var form = this.findById('PersonCardViewAllFilterForm');
		form.getForm().reset();
		var person_search_grid = this.findById('PCVAW_PersonSearchGrid').getGrid();
		person_search_grid.getStore().removeAll();
		Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_add', true);
		var person_card_grid = this.findById('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
		person_card_grid.getStore().removeAll();
		if(getGlobalOptions().region.nick == 'khak'){
			if(this.showPersonCardAdd==2)
			{
				var form = this.findById('PersonCardViewAllFilterForm');
				form.getForm().findField('Person_SurName').setValue(this.Person_SurName);
				form.getForm().findField('Person_FirName').setValue(this.Person_FirName);
				form.getForm().findField('Person_SecName').setValue(this.Person_SecName);
				form.getForm().findField('Person_BirthDay').setValue(this.Person_BirthDay);
				this.doSearch();

				var params = new Object();
				params.Person_id = this.Person_id;
				params.PersonEvn_id = this.PersonEvn_id;
				params.Server_id = this.Server_id;
				params.action = 'add';
				params.attachType = "common_region";
				params.lastAttachIsNotInOurLpu = false;
				params.setIsAttachCondit = this.setIsAttachCondit;
				params.oldLpu_id = null;
				params.callback = function(){
					Ext.getCmp('PCVAW_regions_tab_panel').setActiveTab('common_region');
				};
				params.AllowcheckAttach = isSuperAdmin()?1:2;
				getWnd('swPersonCardEditWindow').show(params);
			}
		}
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	listeners: {
		'beforehide': function () {
			sw.Applets.uec.stopUecReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		}
	},
	addPerson: function() {
		var current_window = this;
		var grid = this.PersonCardGridPanel.getGrid();
		if (current_window.isSearched && grid.getStore().getCount() == 0) {
			sw.swMsg.show({
				title: 'Подтверждение добавления человека',
				msg: 'Внимательно проверьте введенную информацию по поиску человека! Вы точно хотите добавить нового человека? Для подтверждения вам необходимо будет ввести все поля заново.',
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						getWnd('swPersonEditWindow').show({
							action: 'add',
							fields: {
								// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
								'Person_SurNameEdit': '',
								'Person_FirNameEdit': '',
								'Person_SecNameEdit': ''
							},
							callback: function (callback_data) {
								getWnd('swPersonEditWindow').hide();
								var form = current_window.findById('PersonCardViewAllFilterForm');
								/*
								if (!form.isEmpty()) {
									current_window.doSearch();
								}*/
								form.getForm().findField('Person_SurName').setValue(callback_data.PersonData.Person_SurName);
								form.getForm().findField('Person_FirName').setValue(callback_data.PersonData.Person_FirName);
								form.getForm().findField('Person_SecName').setValue(callback_data.PersonData.Person_SecName);
								form.getForm().findField('Person_BirthDay').setValue(callback_data.PersonData.Person_BirthDay + ' - ' + callback_data.PersonData.Person_BirthDay);
								current_window.doSearch();
							},
							onClose: function () {
								Ext.getCmp('PCVAW_Person_SurName').focus(true, 500);
							}
						});
					}
					else {
						Ext.getCmp('PCVAW_Person_SurName').focus(true, 500);
					}
				}
			});
			return false;
		}
		getWnd('swPersonEditWindow').show({
			action: 'add',
			fields: {
				// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
				'Person_SurNameEdit': '',
				'Person_FirNameEdit': '',
				'Person_SecNameEdit': ''
			},
			callback: function (callback_data) {
				getWnd('swPersonEditWindow').hide();
				var form = current_window.findById('PersonCardViewAllFilterForm');
				form.getForm().findField('Person_SurName').setValue(callback_data.PersonData.Person_SurName);
				form.getForm().findField('Person_FirName').setValue(callback_data.PersonData.Person_FirName);
				form.getForm().findField('Person_SecName').setValue(callback_data.PersonData.Person_SecName);
				form.getForm().findField('Person_BirthDay').setValue(callback_data.PersonData.Person_BirthDay + ' - ' + callback_data.PersonData.Person_BirthDay);
				current_window.doSearch();
				/*if (!form.isEmpty()) {
					current_window.doSearch();
				}*/
			},
			onClose: function () {
				Ext.getCmp('PCVAW_Person_SurName').focus(true, 500);
			}
		});
	},
	printEvnPL: function() {
		var params = new Object();
		params.type = 'EvnPL';

		var selected_record = this.findById('PCVAW_PersonSearchGrid').getGrid().getSelectionModel().getSelected();

		if (selected_record) {
			params.personId = selected_record.get('Person_id');
		}

		switch ( getRegionNick() ) {
			case 'ufa':
				getWnd('swEvnPLBlankSettingsWindow').show(params);
				break;

			default:
				printEvnPLBlank(params);
				break;
		}
	},
	printEvnPLOld: function() {
		var selected_record = this.findById('PCVAW_PersonSearchGrid').getGrid().getSelectionModel().getSelected();
		var url = "";
        url = '/?c=EvnPL&m=printEvnPLBlank';
        if ( selected_record ) {
            url = url + '&Person_id=' + selected_record.get('Person_id');
        }
        window.open(url, '_blank');
        return true;
	},
	getDataFromUec: function (uecData, person_data) {

		var f = this.findById('PersonCardViewAllFilterForm');
		f.getForm().findField('Person_SurName').setValue(uecData.surName);
		f.getForm().findField('Person_FirName').setValue(uecData.firName);
		f.getForm().findField('Person_SecName').setValue(uecData.secName);
		f.getForm().findField('Person_BirthDay').setValue(uecData.birthDay + ' - ' + uecData.birthDay);
		//f.getForm().findField('Polis_Num').setValue(uecData.polisNum);
		this.doSearch();
	},
	getDataFromBarcode: function (barcodeData, person_data) {
		var f = this.findById('PersonCardViewAllFilterForm');
		f.getForm().findField('Person_SurName').setValue(barcodeData.Person_Surname);
		f.getForm().findField('Person_FirName').setValue(barcodeData.Person_Firname);
		f.getForm().findField('Person_SecName').setValue(barcodeData.Person_Secname);
		f.getForm().findField('Person_BirthDay').setValue(barcodeData.Person_Birthday + ' - ' + barcodeData.Person_Birthday);
		//f.getForm().findField('Polis_Num').setValue(barcodeData.Polis_Num);
		log(barcodeData);
		this.doSearch();
	},
	doSearch: function (params) {
		if ( this.searchInProgressFlag == true ) {
			return false;
		}

		if (params && params['soc_card_id'])
			var soc_card_id = params['soc_card_id'];
		var form = this.findById('PersonCardViewAllFilterForm');
		if (!soc_card_id && form.isEmpty()) {
			Ext.Msg.alert('Ошибка', 'Не заполнено ни одно поле', function () {
				Ext.getCmp('PersonCardViewAllFilterTabPanel').setActiveTab(0);
				form.getForm().findField('Person_SurName').focus();
			});
			return false;
		}
		var person_card_grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
		person_card_grid.getStore().removeAll();
		var grid = this.findById('PCVAW_PersonSearchGrid').getGrid();
		var params = form.getForm().getValues();
		var baseParams = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (i = 0; i < arr.length; i++) {
			if (arr[i].getValue) {
				params[arr[i].hiddenName] = arr[i].getValue();
				baseParams[arr[i].hiddenName] = arr[i].getValue();
			}
		}

		if (soc_card_id) {
			var params = {
				soc_card_id: soc_card_id,
				SearchFormType: params.SearchFormType
			};
			var baseParams = {
				soc_card_id: soc_card_id,
				SearchFormType: params.SearchFormType
			};
		}

		if ( (!Ext.isEmpty(params.PersonCard_IsAttachCondit) || !Ext.isEmpty(params.LpuRegion_id) || !Ext.isEmpty(params.LpuRegionType_id))
			&& Ext.isEmpty(params.AttachLpu_id)
		) {
			var CurrentLpu_id = getGlobalOptions().lpu_id;

			var index = form.getForm().findField('AttachLpu_id').getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == CurrentLpu_id);
			});

			if ( index >= 0 ) {
				form.getForm().findField('AttachLpu_id').setValue(CurrentLpu_id);
				baseParams.AttachLpu_id = CurrentLpu_id;
				params.AttachLpu_id = CurrentLpu_id;
			}
			else {
				sw.swMsg.alert('Поиск по картотеке', 'Поле "ЛПУ прикрепления" обязательно для заполнения, если указан один из фильтров "Участок", "Тип участка", "Усл. прикрепл."', function() {
					this.findById('PCVAW_regions_tab_panel').setActiveTab(0);
					form.getForm().findField('AttachLpu_id').focus();
				}.createDelegate(this));
				return false;
			}
		}

		grid.getStore().removeAll();
		grid.getStore().baseParams = baseParams;
		params.start = 0;
		params.limit = 100;
		params.dontShowUnknowns = 1;// #158923 не показывать неизвестных
		this.isSearched = true;
		this.searchInProgressFlag = true;
		this.buttons[0].disable();

		grid.getStore().load({
			params: params,
			failure: function() {
				this.buttons[0].enable();
				this.searchInProgressFlag = false;
			}.createDelegate(this),
			callback: function (r) {
				this.buttons[0].enable();
				this.searchInProgressFlag = false;

				if (r.length > 0) {
					var len = r.length;
					if (len > 100) {
						new Ext.ux.window.MessageWindow({
							title: 'Поиск по картотеке',
							autoDestroy: true, //default = true
							autoHeight: true,
							autoHide: true, //default = true
							help: false,
							bodyStyle: 'text-align:center;',
							closable: false,
							//pinState: null,
							//pinOnClick: false,
							hideFx: {
								delay: 2000,
								//duration: 0.25,
								mode: 'standard', //null,'standard','custom',or default ghost
								useProxy: false //default is false to hide window instead
							},
							html: '<br/><b>Найдено больше 100 записей.</b><br/>Показаны первые 100 записей.<br/>Пожалуйста уточните параметры запроса.<br/><br/>',
							iconCls: 'info16',
							showFx: {
								delay: 0,
								//duration: 0.5, //defaults to 1 second
								mode: 'standard', //null,'standard','custom',or default ghost
								useProxy: false //default is false to hide window instead
							},
							width: 250 //optional (can also set minWidth which = 200 by default)
						}).show(Ext.getDoc());
						grid.getStore().removeAt(len - 1);
						len--;
					}
					/*
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
					*/
				}
			}.createDelegate(this)
		});
	},
    /* printPersonCardGrid: function (params) {
        var form = this.findById('PersonCardViewAllFilterForm');
        var params = form.getForm().getValues();
        var arr = form.find('disabled', true);
        for (i = 0; i < arr.length; i++) {
            if (arr[i].getValue) {
                params[arr[i].hiddenName] = arr[i].getValue();
            }
        }
        Ext.Ajax.request({
            url: '/?c=Person&m=printPersonCardGrid',
            params: params,
            method: 'POST',
            callback: function (options, success, response) {
                if (success) {
                    openNewWindow(response.responseText);
                }
            }
        });
    },*/
	getRecordsCount: function () {
		var current_window = this;

		var form = current_window.findById('PersonCardViewAllFilterForm');

		if (!form.getForm().isValid()) {
			sw.swMsg.alert('Поиск по картотеке', 'Проверьте правильность заполнения полей на форме поиска');
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PersonCardViewAllWindow'), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(form);

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Records_Count != undefined) {
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
			url: '/?c=Person&m=getCountPersonCardGrid'
		});
	},
	printMedCard: function () {
		var record = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected();
		var PersonCard_id = (record) ? record.get('PersonCard_id') : null;
		if (PersonCard_id !== null) {
			if (getRegionNick() =='ufa'){
				printMedCard4Ufa(PersonCard_id);
				return;
			}
			if(getRegionNick().inlist([ 'adygeya', 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak','penza', 'kaluga'])){
				var PersonCard = 0;
				if(!Ext.isEmpty(PersonCard_id)){
					var PersonCard = PersonCard_id;
				}
				printBirt({
                    'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
                    'Report_Params': '&paramPerson=' + record.get('Person_id') + '&paramPersonCard=' + PersonCard + '&paramLpu=' + getLpuIdForPrint(),
                    'Report_Format': 'pdf'
                });
			} else {
				Ext.Ajax.request(
					{
						url: '/?c=PersonCard&m=printMedCard',
						params: {
							PersonCard_id: PersonCard_id
						},
						callback: function (options, success, response) {
							if ( success ) {
								var responseData = Ext.util.JSON.decode(response.responseText);

								if ( getRegionNick() == 'ekb' ) {
									if ( !Ext.isEmpty(responseData.result1) ) {
										openNewWindow(responseData.result1);
									}

									if ( !Ext.isEmpty(responseData.result2) ) {
										openNewWindow(responseData.result2);
									}
								}
								else if ( !Ext.isEmpty(responseData.result) ) {
									openNewWindow(responseData.result);
								}
								else {
									sw.swMsg.alert('Ошибка', 'Ошибка при получении данных для печати');
								}
							}
							else {
								sw.swMsg.alert('Ошибка', 'Ошибка при получении данных для печати');
							}
						}
					});
			}
		} else {
			sw.swMsg.alert('Сообщение', 'Не выбран пациент!');
		}
	},
	checkAttachAllow: function(){
		if (Ext.getCmp('PCVAW_regions_tab_panel').getActiveTab().id == 'common_region' && (
				(!Ext.isEmpty(getGlobalOptions().check_attach_allow) && getGlobalOptions().check_attach_allow == 1 && !isUserGroup('CardEditUser')) ||
				((Ext.isEmpty(getGlobalOptions().check_attach_allow) || getGlobalOptions().check_attach_allow != 1) && !haveArmType('regpol') && !getRegionNick().inlist(['perm']))
			)){
			Ext.getCmp('PCVAW_PersonCardHistoryGrid').setReadOnly(true);
		} else {
			Ext.getCmp('PCVAW_PersonCardHistoryGrid').setReadOnly(false);
		}
		//Добавил отдельно (мало ли что, чтобы не громоздить большое условие) для https://redmine.swan.perm.ru/issues/64839
		if((isSuperAdmin() || isLpuAdmin()) && (Ext.isEmpty(getGlobalOptions().check_attach_allow) || getGlobalOptions().check_attach_allow != 1))
		{
			Ext.getCmp('PCVAW_PersonCardHistoryGrid').setReadOnly(false);
		}
		if(isUserGroup('CardCloseUser')){ //https://redmine.swan.perm.ru/issues/64998
			Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_edit', false);
		}
        //Отдельно для Пскова https://redmine.swan.perm.ru/issues/71434
        if(Ext.getCmp('PCVAW_regions_tab_panel').getActiveTab().id == 'common_region' && getRegionNick()== 'pskov' && isUserGroup('CardEditUser')){
            Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_add', false);
        }
        if(this.allowEditLpuRegion == 1)
        {
            Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_edit', false);
        }
	},
	getMedicalInterventPrintParams: function(options) {
		var callback = Ext.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}

		var record = this.findById('PCVAW_PersonCardHistoryGrid').getGrid().getSelectionModel().getSelected();

		var processParams = function(data) {
			var params = new Object();

			params.person_card_id = record.get('PersonCard_id');
			params.med_personal_id = (getGlobalOptions().medpersonal_id) ? getGlobalOptions().medpersonal_id : 0;

			params.total_count = 0;
			params.refuse_count = 0;

			data.forEach(function(item) {
				params.total_count++;
				if (item.PersonMedicalIntervent_IsRefuse) {
					params.refuse_count++;
				}
			});

			params.is_refuse = (params.refuse_count > 0);

			return params;
		};

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadPersonCardMedicalInterventGrid',
			params: {PersonCard_id: record.get('PersonCard_id')},
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					callback(processParams(response_obj));
				}
			}
		});
	},
	printAttachBlank: function(){
		this.getMedicalInterventPrintParams({callback: function(mi_params){
			var params = new Object();

			params.mi_params = mi_params;

			var record = this.findById('PCVAW_PersonSearchGrid').getGrid().getSelectionModel().getSelected();
			params.Person_id = record.get('Person_id');

			record = this.findById('PCVAW_PersonCardHistoryGrid').getGrid().getSelectionModel().getSelected();
			params.Server_id = record.get('Server_id');
			params.Lpu_id = record.get('Lpu_id');
			var type_panel = Ext.getCmp('PCVAW_regions_tab_panel');
			params.printAgreementOnly = 0;
			if(type_panel.getActiveTab().id == 'service_region' && getRegionNick()=='ufa'){
				params.printAgreementOnly = 1;
			}
			getWnd((getRegionNick()=='kz')?'swPersonCardPrintDialogWindowKz':'swPersonCardPrintDialogWindow').show({params: params}); //https://redmine.swan.perm.ru/issues/56487
		}.createDelegate(this)});
	},
	height: 550,
	id: 'PersonCardViewAllWindow',
	initComponent: function () {
		var _this = this;
		/*var gridStore = new Ext.data.JsonStore({
			autoLoad: false,
			root: 'data',
			totalProperty: 'totalCount',
			url: C_PERSONCARD_GRID,
			fields: [
				'Person_id',
				'Server_id',
				'PersonEvn_id',
				'Person_SurName',
				'Person_FirName',
				'Person_SecName',
				'PersonCard_IsDmsForCheck',
				{name: 'PersonBirthDay', type: 'date', dateFormat: 'd.m.Y'},
				{name: 'Person_deadDT', type: 'date', dateFormat: 'd.m.Y'},
				'Lpu_Nick',
				{name: 'PersonCard_IsDms', type: 'string'},
				{name: 'Person_IsBDZ', type: 'string'},
				{name: 'Person_IsFedLgot', type: 'string'},
				{name: 'Person_IsRegLgot', type: 'string'},
				{name: 'Person_Is7Noz', type: 'string'},
				{name: 'Person_IsRefuse', type: 'string'},
				{name: 'Person_UAddress', type: 'string'},
				{name: 'Person_PAddress', type: 'string'}
			]
		});*/
		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.ownerCt.doSearch();
					},
					iconCls: 'search16',
					id: 'PCVAW_SearchButton',
					tabIndex: 3032,
					text: BTN_FRMSEARCH
				},
				{
					handler: function () {
						this.ownerCt.doResetAll();
					},
					iconCls: 'resetsearch16',
					tabIndex: 3033,
					text: BTN_FRMRESET
				},
				/*{
					handler: function () {
						var params = new Object();
						params.type = 'EvnPL';

						var selected_record = this.findById('PCVAW_PersonSearchGrid').getGrid().getSelectionModel().getSelected();

						if (selected_record) {
							params.personId = selected_record.get('Person_id');
						}

						if (getGlobalOptions().region) {
							switch (getGlobalOptions().region.nick) {
								case 'perm':
									printEvnPLBlank(params);
									break;

								case 'ufa':
									getWnd('swEvnPLBlankSettingsWindow').show(params);
									break;
							}
						}
						else {
							printEvnPLBlank(params);
						}
					}.createDelegate(this),
					iconCls: 'print16',
					tabIndex: 3034,
					text: 'Печать бланка ТАП'
				},*/
				/*{
					handler: function () {
						this.ownerCt.findById('PersonCardViewAllFilterForm').getForm().submit();
                        //this.printPersonCardGrid();
					}.createDelegate(this),
					iconCls: 'print16',
					tabIndex: 3034,
					text: 'Печать всего списка'
				},*/
				{
					handler: function () {
						this.printMedCard();
					}.createDelegate(this),
					iconCls: 'print16',
					tabIndex: 3034,
					text: 'Печать мед. карты',
					hidden: false
				},
				{
					handler: function () {
						this.getRecordsCount();
					}.createDelegate(this),
					tabIndex: 3034,
					text: 'Показать количество записей'
				},
				'-',
				HelpButton(this, -1),
				{
					handler: function () {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					tabIndex: 3034,
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.FilterPanel = new Ext.form.FormPanel({
					height: 245,
					id: 'PersonCardViewAllFilterForm',
					items: [
						{
							xtype: 'hidden',
							name: 'start'
						}, {
							xtype: 'hidden',
							name: 'limit'
						}, {
							xtype: 'hidden',
							name: 'printAll'
						},
						new Ext.TabPanel({
							activeTab: 0,
							id: 'PersonCardViewAllFilterTabPanel',
							items: [
								{
									height: 220,
									items: [
										{
											border: false,
											layout: 'column',
											width: 1010,
											items: [
												{
													border: false,
													layout: 'form',
													columnWidth: 1,
													items: [
														{
															xtype: 'fieldset',
															autoHeight: true,
															title: 'Ф. И. О.',
															style: 'padding: 0;',
															items: [
																{
																	border: false,
																	layout: 'column',
																	items: [
																		{
																			border: false,
																			layout: 'form',
																			columnWidth: .33,
																			items: [
																				{
																					enableKeyEvents: true,
																					fieldLabel: 'Фамилия',
																					id: 'PCVAW_Person_SurName',
																					listeners: {
																						'keydown': function (inp, e) {
																							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
																								e.stopEvent();
																								Ext.getCmp("PCVAW_Person_FirName").focus(true);
																							}
																						}
																					},
																					name: 'Person_SurName',
																					tabIndex: 3035,
																					width: 180,
																					maskRe: /[^%]/,
																					xtype: 'swtranslatedtextfield'
																				}
																			]
																		},
																		{
																			border: false,
																			layout: 'form',
																			columnWidth: .33,
																			labelWidth: 125,
																			items: [
																				{
																					fieldLabel: 'Имя',
																					id: 'PCVAW_Person_FirName',
																					listeners: {
																						'keydown': function (inp, e) {
																							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
																								e.stopEvent();
																								Ext.getCmp("PCVAW_Person_SurName").focus(true);
																							}
																						}
																					},
																					name: 'Person_FirName',
																					tabIndex: 3000,
																					width: 170,
																					maskRe: /[^%]/,
																					xtype: 'swtranslatedtextfield'
																				}
																			]
																		},
																		{
																			border: false,
																			layout: 'form',
																			columnWidth: .33,
																			labelWidth: 125,
																			items: [
																				{
																					fieldLabel: 'Отчество',
																					id: 'PCVAW_Person_SecName',
																					name: 'Person_SecName',
																					tabIndex: 3001,
																					width: 170,
																					maskRe: /[^%]/,
																					xtype: 'swtranslatedtextfield'
																				}
																			]
																		}
																	]
																}
															]
														}
													]
												},
												{
													border: false,
													width: 65,
													style: 'padding: 5px;',
													layout: 'form',
													items: [
														{
															xtype: 'button',
															hidden: !getGlobalOptions()['card_reader_is_enable'],
															cls: 'x-btn-large',
															iconCls: 'idcard32',
															tooltip: 'Считать с карты',
															handler: function () {
																_this.readFromCard();
															}
														}
													]
												}
											]
										},
										{
											border: false,
											layout: 'column',
											width: 1020,
											items: [
												{
													border: false,
													columnWidth: .33,
													layout: 'form',
													items: [
														{
															fieldLabel: "Дата рождения",
															name: "Person_BirthDay",
															plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
															tabIndex: 3002,
															width: 180,
															xtype: "daterangefield"
														},
														{
															border: false,
															items: [
																{
																	border: false,
																	layout: 'form',
																	items: [
																		{
																			autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
																			fieldLabel: 'Возраст с',
																			maskRe: /\d/,
																			name: 'PersonAge_From',
																			tabIndex: 3003,
																			width: 70,
																			xtype: 'textfield'
																		}
																	]
																},
																{
																	border: false,
																	labelWidth: 35,
																	layout: 'form',
																	items: [
																		{
																			autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
																			fieldLabel: 'по',
																			maskRe: /\d/,
																			name: 'PersonAge_To',
																			tabIndex: 3004,
																			width: 70,
																			xtype: 'textfield'
																		}
																	]
																}
															],
															layout: 'column'
														},
														{
															fieldLabel: 'Номер амб. карты',
															name: 'PersonCard_Code',
															tabIndex: 3005,
															width: 180,
															maskRe: /[^%]/,
															xtype: 'textfield'
														},
														{
															fieldLabel: 'ЛПУ прикрепления',
															hiddenName: 'AttachLpu_id',
															tabIndex: 3005,
															width: 180,
															xtype: 'swlpucombo',
                                                            listeners: {
                                                                'change': function(combo,value){
                                                                    var form = _this.findById('PersonCardViewAllFilterForm');
                                                                    var params = new Object();
                                                                    params.add_without_region_line = true;
                                                                    form.getForm().findField('LpuRegion_id').clearValue();
                                                                    form.getForm().findField('LpuRegion_id').getStore().removeAll();
                                                                    form.getForm().findField('LpuRegion_Fapid').clearValue();
                                                                    form.getForm().findField('LpuRegion_Fapid').getStore().removeAll();
                                                                    var lpu_region_type_combo = form.getForm().findField('LpuRegionType_id');
                                                                    var lpu_region_type_id = lpu_region_type_combo.getValue();
                                                                    if(!Ext.isEmpty(value) && value > 0)
                                                                    {
                                                                        params.Lpu_id = value;
                                                                        form.getForm().findField('LpuRegion_Fapid').getStore().load({
                                                                            params: params
                                                                        });
                                                                        if(!Ext.isEmpty(lpu_region_type_id) && lpu_region_type_id > 0)
                                                                        {
                                                                            params.LpuRegionType_id = lpu_region_type_id;
                                                                        }
                                                                        form.getForm().findField('LpuRegion_id').getStore().load({
                                                                            params: params
                                                                        });
                                                                    }
                                                                }
                                                            }
														},
														{
															displayField: 'LpuRegion_Name',
															fieldLabel: 'Участок',
															tabIndex: 3007,
															triggerAction: 'all',
															typeAhead: true,
															typeAheadDelay: 1,
															valueField: 'LpuRegion_id',
															width: 180,
															xtype: 'swlpuregioncombo'
														},
                                                        {
                                                            allowBlank: true,
                                                            displayField: 'LpuRegion_FapName',
                                                            fieldLabel: 'ФАП Участок',
                                                            forceSelection: true,
                                                            hiddenName: 'LpuRegion_Fapid',
                                                            //hideLabel: getRegionNick() != 'perm',
                                                            //hidden: getRegionNick() != 'perm',
															hideLabel: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),
															hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb', 'ufa','penza','vologda']),
                                                            id: 'PSPCAW_LpuRegion_Fapid',
                                                            minChars: 1,
                                                            mode: 'local',
                                                            queryDelay: 1,
                                                            setValue: function(v) {
                                                                var text = v;
                                                                if(this.valueField){
                                                                    var r = this.findRecord(this.valueField, v);
                                                                    if(r){
                                                                        text = r.data[this.displayField];
                                                                        if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
                                                                        {
                                                                            if (r.data['LpuRegion_FapDescr']) {
                                                                                text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
                                                                            }
                                                                        }
                                                                    } else if(this.valueNotFoundText !== undefined){
                                                                        text = this.valueNotFoundText;
                                                                    }
                                                                }
                                                                this.lastSelectionText = text;
                                                                if(this.hiddenField){
                                                                    this.hiddenField.value = v;
                                                                }
                                                                Ext.form.ComboBox.superclass.setValue.call(this, text);
                                                                this.value = v;
                                                            },
                                                            lastQuery: '',
                                                            store: new Ext.data.Store({
                                                                autoLoad: false,
                                                                reader: new Ext.data.JsonReader({
                                                                    id: 'LpuRegion_Fapid'
                                                                }, [
                                                                    {name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
                                                                    {name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
                                                                    {name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
                                                                ]),
                                                                url: '/?c=LpuRegion&m=getLpuRegionListFeld'
                                                            }),
                                                            tabIndex: 2106,
                                                            tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
                                                            triggerAction: 'all',
                                                            typeAhead: true,
                                                            typeAheadDelay: 1,
                                                            valueField: 'LpuRegion_Fapid',
                                                            width : 180,
                                                            xtype: 'combo'
                                                        }
													]
												},
												{
													border: false,
													columnWidth: .33,
													labelWidth: 105,
													layout: 'form',
													items: [
														{
															border: false,
															hidden: (getRegionNick() != 'kz'),
															layout: 'form',
															style: 'padding: 0px;',
															items: [{
																allowBlank: true,
																autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
																fieldLabel: 'ИИН',
																maskRe: /\d/,
																maxLength: 12,
																minLength: 12,
																name: 'Person_Inn',
																width: 150,
																xtype: 'textfield'
															}]
														},
														{
															border: false,
															hidden: (getRegionNick() == 'kz'),
															layout: 'form',
															style: 'padding: 0px;',
															items: [{
																autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
																fieldLabel: 'СНИЛС',
																maskRe: /\d/,
																name: 'Person_Snils',
																tabIndex: 3009,
																width: 170,
																xtype: 'textfield'
															}]
														},
														{
															fieldLabel: "Прикреплен",
															name: "PersonCard_begDate",
															plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
															tabIndex: 3010,
															width: 170,
															xtype: "daterangefield"
														},
														{
															fieldLabel: "Откреплен",
															name: "PersonCard_endDate",
															plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
															tabIndex: 3010,
															width: 170,
															xtype: "daterangefield"
														},
														{
															hiddenName: "LpuRegionType_id",
															tabIndex: 3011,
															width: 170,
															xtype: "swlpuregiontypecombo",
															enableKeyEvents: true,
															listeners: {
																'keydown': function (inp, e) {
																	if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
																		var grid = Ext.getCmp("PCVAW_PersonSearchGrid").getGrid();
																		if (grid.getStore().getCount() == 0)
																			return;
																		e.stopEvent();
																		grid.getView().focusRow(0);
																		grid.getSelectionModel().selectFirstRow();
																	}
																},
                                                                'change': function(combo,value){
                                                                    var form = _this.findById('PersonCardViewAllFilterForm');
                                                                    var params = new Object();
                                                                    params.add_without_region_line = true;
                                                                    var lpu_combo = form.getForm().findField('AttachLpu_id');
                                                                    var lpu_region_combo = form.getForm().findField('LpuRegion_id');
                                                                    lpu_region_combo.clearValue();
                                                                    lpu_region_combo.getStore().removeAll();
                                                                    var Lpu_id = lpu_combo.getValue();
                                                                    if(!Ext.isEmpty(value) && value > 0)
                                                                        params.LpuRegionType_id = value;
                                                                    if(!Ext.isEmpty(Lpu_id) && Lpu_id > 0)
                                                                    {
                                                                        params.Lpu_id = Lpu_id;
                                                                        lpu_region_combo.getStore().load({params: params});
                                                                    }
                                                                }
															}
														},
														{
															allowBlank: true,
															hiddenName: "PersonCard_IsAttachCondit",
															fieldLabel: 'Усл. прикрепл.',
															tabIndex: 3011,
															width: 170,
															xtype: "swyesnocombo"
														}
													]
												},
												{
													border: false,
													columnWidth: .34,
													labelWidth: 130,
													layout: 'form',
													items: [
														{
															border: false,
															hidden: (getRegionNick() == 'kz'),
															layout: 'form',
															style: 'padding: 0px;',
															items: [{
																allowBlank: true,
																hiddenName: "PersonCard_IsActualPolis",
																fieldLabel: 'Есть действ. полис',
																tabIndex: 3008,
																width: 170,
																xtype: "swyesnocombo"
															}]
														},
														{
															allowBlank: false,
															codeField: 'PersonCardStateType_Code',
															displayField: 'PersonCardStateType_Name',
															ignoreIsEmpty: true,
															editable: false,
															fieldLabel: 'Актуальность прикр-я',
															hiddenName: 'PersonCardStateType_id',
															store: new Ext.data.SimpleStore({
																autoLoad: true,
																data: [
																	[ 1, 1, 'Актуальные прикрепления' ],
																	[ 2, 2, 'Вся история прикреплений' ],
																	[ 3, 3, 'Все' ]
																],
																fields: [
																	{ name: 'PersonCardStateType_id', type: 'int'},
																	{ name: 'PersonCardStateType_Code', type: 'int'},
																	{ name: 'PersonCardStateType_Name', type: 'string'}
																],
																key: 'PersonCardStateType_id',
																sortInfo: { field: 'PersonCardStateType_Code' }
															}),
															tabIndex: TABINDEX_PERSCARDSW + 38,
															tpl: new Ext.XTemplate(
																'<tpl for="."><div class="x-combo-list-item">',
																'<font color="red">{PersonCardStateType_Code}</font>&nbsp;{PersonCardStateType_Name}',
																'</div></tpl>'
															),
															value: 3,
															valueField: 'PersonCardStateType_id',
															width: 210,
															xtype: 'swbaselocalcombo'
														}
													]
												}
											]
										}
									],
									border: false,
									id: 'PCVAFTP_FirstTab',
									labelWidth: 120,
									layout: 'form',
									style: 'padding: 2px; margin-top: 5px;',
									title: '<u>1</u>. Основной фильтр'
								},
								{
									border: false,
									height: 290,
									items: [
										{
											codeField: 'RegisterSelector_Code',
											displayField: 'RegisterSelector_Name',
											editable: false,
											fieldLabel: 'Регистр льготников',
											hiddenName: 'RegisterSelector_id',
											store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
													[ 1, 1, 'Федеральный' ],
													[ 2, 2, 'Региональный' ]
												],
												fields: [
													{name: 'RegisterSelector_id', type: 'int'},
													{name: 'RegisterSelector_Code', type: 'int'},
													{name: 'RegisterSelector_Name', type: 'string'}
												],
												key: 'RegisterSelector_id',
												sortInfo: {field: 'RegisterSelector_Code'}
											}),
											tabIndex: 3013,
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{RegisterSelector_Code}</font>&nbsp;{RegisterSelector_Name}',
												'</div></tpl>'
											),
											valueField: 'RegisterSelector_id',
											width: 170,
											xtype: 'swbaselocalcombo'
										},
										new sw.Promed.SwYesNoCombo({
											fieldLabel: 'Отказник',
											hiddenName: 'Refuse_id',
											tabIndex: 3014,
											width: 170
										}),
										new sw.Promed.SwYesNoCombo({
											fieldLabel: 'Отказ на след. год',
											hiddenName: 'RefuseNextYear_id',
											tabIndex: 3015,
											width: 170
										})
									],
									id: 'PCVAFTP_SecondTab',
									labelWidth: 120,
									layout: 'form',
									listeners: {
										'activate': function(panel) {
											if ( getRegionNick().inlist([ 'kz' ]) ) {
												panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').setContainerVisible(false);
												panel.ownerCt.ownerCt.getForm().findField('Refuse_id').focus(250, true);
											}
											else {
												panel.ownerCt.ownerCt.getForm().findField('RegisterSelector_id').focus(250, true);
											}
										}
									},
									style: 'padding: 2px; margin-top: 5px;',
									title: '<u>2</u>. Льгота'
								},
								{
									autoHeight: true,
									bodyStyle: 'margin-top: 5px; padding: 2px;',
									border: false,
									layout: 'form',
									listeners: {
										'activate': function (panel) {
											panel.ownerCt.ownerCt.getForm().findField('AddressStateType_id').focus(250, true);
											/*
											 if (panel.ownerCt.ownerCt.getForm().findField('KLAreaStat_id').getValue() == '')
											 panel.ownerCt.ownerCt.getForm().findField('KLAreaStat_id').setValue(1);
											 */
										}
									},
									title: '<u>3</u>. Адрес',
									labelWidth: 150,
									layout: 'column',

									items: [
										{
											border: false,
											layout: 'form',
											columnWidth: .5,
											items: [
												{
													allowBlank: true,
													ignoreIsEmpty: true,
													codeField: 'AddressStateType_Code',
													displayField: 'AddressStateType_Name',
													editable: false,
													fieldLabel: 'Тип адреса',
													hiddenName: 'AddressStateType_id',
													ignoreIsEmpty: true,
													listeners: {
														'keydown': function (inp, e) {
															if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
																e.stopEvent();
																inp.ownerCt.ownerCt.ownerCt.ownerCt.buttons[6].focus();
															}
														}
													},
													store: new Ext.data.SimpleStore({
														autoLoad: true,
														data: [
															[ 1, 1, 'Адрес регистрации' ],
															[ 2, 2, 'Адрес проживания' ]
														],
														fields: [
															{name: 'AddressStateType_id', type: 'int'},
															{name: 'AddressStateType_Code', type: 'int'},
															{name: 'AddressStateType_Name', type: 'string'}
														],
														key: 'AddressStateType_id',
														setValue: 1,
														sortInfo: {field: 'AddressStateType_Code'}
													}),
													tabIndex: TABINDEX_PERSCARDSW + 46,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'<font color="red">{AddressStateType_Code}</font>&nbsp;{AddressStateType_Name}',
														'</div></tpl>'
													),
													value: 1,
													valueField: 'AddressStateType_id',
													width: 180,
													xtype: 'swbaselocalcombo'
												},
												{
													codeField: 'KLAreaStat_Code',
													disabled: false,
													displayField: 'KLArea_Name',
													editable: true,
													enableKeyEvents: true,
													fieldLabel: 'Территория',
													hiddenName: 'KLAreaStat_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															var current_window = Ext.getCmp('PersonCardViewAllWindow');
															var current_record = combo.getStore().getById(newValue);
															var form = current_window.findById('PersonCardViewAllFilterForm');

															var country_combo = form.getForm().findField('KLCountry_id');
															var region_combo = form.getForm().findField('KLRgn_id');
															var sub_region_combo = form.getForm().findField('KLSubRgn_id').enable();
															var city_combo = form.getForm().findField('KLCity_id').enable();
															var town_combo = form.getForm().findField('KLTown_id').enable();
															var street_combo = form.getForm().findField('KLStreet_id').enable();

															country_combo.enable();
															region_combo.enable();
															sub_region_combo.enable();
															city_combo.enable();
															town_combo.enable();
															street_combo.enable();

															if (!current_record) {
																return false;
															}

															var country_id = current_record.get('KLCountry_id');
															var region_id = current_record.get('KLRGN_id');
															var subregion_id = current_record.get('KLSubRGN_id');
															var city_id = current_record.get('KLCity_id');
															var town_id = current_record.get('KLTown_id');
															var klarea_pid = 0;
															var level = 0;

															clearAddressCombo(
																country_combo.areaLevel,
																{
																	'Country': country_combo,
																	'Region': region_combo,
																	'SubRegion': sub_region_combo,
																	'City': city_combo,
																	'Town': town_combo,
																	'Street': street_combo
																}
															);

															if (country_id != null) {
																country_combo.setValue(country_id);
																//country_combo.disable();
															}
															else {
																return false;
															}

															region_combo.getStore().load({
																callback: function () {
																	region_combo.setValue(region_id);
																},
																params: {
																	country_id: country_id,
																	level: 1,
																	value: 0
																}
															});

															if (region_id.toString().length > 0) {
																klarea_pid = region_id;
																level = 1;
															}

															sub_region_combo.getStore().load({
																callback: function () {
																	sub_region_combo.setValue(subregion_id);
																},
																params: {
																	country_id: 0,
																	level: 2,
																	value: klarea_pid
																}
															});

															if (subregion_id.toString().length > 0) {
																klarea_pid = subregion_id;
																level = 2;
															}

															city_combo.getStore().load({
																callback: function () {
																	city_combo.setValue(city_id);
																},
																params: {
																	country_id: 0,
																	level: 3,
																	value: klarea_pid
																}
															});

															if (city_id.toString().length > 0) {
																klarea_pid = city_id;
																level = 3;
															}

															town_combo.getStore().load({
																callback: function () {
																	town_combo.setValue(town_id);
																},
																params: {
																	country_id: 0,
																	level: 4,
																	value: klarea_pid
																}
															});

															if (town_id.toString().length > 0) {
																klarea_pid = town_id;
																level = 4;
															}

															street_combo.getStore().load({
																params: {
																	country_id: 0,
																	level: 5,
																	value: klarea_pid
																}
															});

															/*switch (level) {
																case 1:
																	region_combo.disable();
																	break;

																case 2:
																	region_combo.disable();
																	sub_region_combo.disable();
																	break;

																case 3:
																	region_combo.disable();
																	sub_region_combo.disable();
																	city_combo.disable();
																	break;

																case 4:
																	region_combo.disable();
																	sub_region_combo.disable();
																	city_combo.disable();
																	town_combo.disable();
																	break;
															}*/
														}
													},
													store: new Ext.db.AdapterStore({
														autoLoad: true,
														dbFile: 'Promed.db',
														fields: [
															{name: 'KLAreaStat_id', type: 'int'},
															{name: 'KLAreaStat_Code', type: 'int'},
															{name: 'KLArea_Name', type: 'string'},
															{name: 'KLCountry_id', type: 'int'},
															{name: 'KLRGN_id', type: 'int'},
															{name: 'KLSubRGN_id', type: 'int'},
															{name: 'KLCity_id', type: 'int'},
															{name: 'KLTown_id', type: 'int'}
														],
														key: 'KLAreaStat_id',
														sortInfo: {
															field: 'KLAreaStat_Code',
															direction: 'ASC'
														},
														tableName: 'KLAreaStat'
													}),
													tabIndex: TABINDEX_PERSCARDSW + 47,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
														'</div></tpl>'
													),
													valueField: 'KLAreaStat_id',
													width: 300,
													xtype: 'swbaselocalcombo'
												},
												{
													areaLevel: 0,
													codeField: 'KLCountry_Code',
													disabled: false,
													displayField: 'KLCountry_Name',
													editable: true,
													fieldLabel: 'Страна',
													hiddenName: 'KLCountry_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															if (newValue != null && combo.getRawValue().toString().length > 0) {
																loadAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	},
																	combo.getValue(),
																	combo.getValue(),
																	true
																);
															}
															else {
																clearAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	}
																);
															}
														},
														'keydown': function (combo, e) {
															if (e.getKey() == e.DELETE) {
																if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
																	combo.fireEvent('change', combo, null, combo.getValue());
																}
															}
														},
														'select': function (combo, record, index) {
															if (record.get('KLCountry_id') == combo.getValue()) {
																combo.collapse();
																return false;
															}
															combo.fireEvent('change', combo, record.get('KLArea_id'), null);
														}
													},
													store: new Ext.db.AdapterStore({
														autoLoad: true,
														dbFile: 'Promed.db',
														fields: [
															{name: 'KLCountry_id', type: 'int'},
															{name: 'KLCountry_Code', type: 'int'},
															{name: 'KLCountry_Name', type: 'string'}
														],
														key: 'KLCountry_id',
														sortInfo: {
															field: 'KLCountry_Name'
														},
														tableName: 'KLCountry'
													}),
													tabIndex: TABINDEX_PERSCARDSW + 48,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
														'</div></tpl>'
													),
													valueField: 'KLCountry_id',
													width: 300,
													xtype: 'swbaselocalcombo'
												},
												{
													areaLevel: 1,
													disabled: false,
													displayField: 'KLArea_Name',
													enableKeyEvents: true,
													fieldLabel: 'Регион',
													hiddenName: 'KLRgn_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															if (newValue != null && combo.getRawValue().toString().length > 0) {
																loadAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	},
																	0,
																	combo.getValue(),
																	true
																);
															}
															else {
																clearAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	}
																);
															}
														},
														'keydown': function (combo, e) {
															if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
																combo.fireEvent('change', combo, null, combo.getValue());
															}
														},
														'select': function (combo, record, index) {
															if (record.get('KLArea_id') == combo.getValue()) {
																combo.collapse();
																return false;
															}
															combo.fireEvent('change', combo, record.get('KLArea_id'));
														}
													},
													minChars: 0,
													mode: 'local',
													queryDelay: 250,
													store: new Ext.data.JsonStore({
														autoLoad: false,
														fields: [
															{name: 'KLArea_id', type: 'int'},
															{name: 'KLArea_Name', type: 'string'}
														],
														key: 'KLArea_id',
														sortInfo: {
															field: 'KLArea_Name'
														},
														url: C_LOAD_ADDRCOMBO
													}),
													tabIndex: TABINDEX_PERSCARDSW + 49,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{KLArea_Name}',
														'</div></tpl>'
													),
													triggerAction: 'all',
													valueField: 'KLArea_id',
													width: 300,
													xtype: 'combo'
												},
												{
													areaLevel: 2,
													disabled: false,
													displayField: 'KLArea_Name',
													enableKeyEvents: true,
													fieldLabel: 'Район',
													hiddenName: 'KLSubRgn_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															if (newValue != null && combo.getRawValue().toString().length > 0) {
																loadAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	},
																	0,
																	combo.getValue(),
																	true
																);
															}
															else {
																clearAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCountry_id'),
																		'Region': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLRgn_id'),
																		'SubRegion': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLSubRgn_id'),
																		'City': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLCity_id'),
																		'Town': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLTown_id'),
																		'Street': combo.ownerCt.ownerCt.ownerCt.getForm().findField('KLStreet_id')
																	}
																);
															}
														},
														'keydown': function (combo, e) {
															if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
																combo.fireEvent('change', combo, null, combo.getValue());
															}
														},
														'select': function (combo, record, index) {
															if (record.get('KLArea_id') == combo.getValue()) {
																combo.collapse();
																return false;
															}
															combo.fireEvent('change', combo, record.get('KLArea_id'));
														}
													},
													minChars: 0,
													mode: 'local',
													queryDelay: 250,
													store: new Ext.data.JsonStore({
														autoLoad: false,
														fields: [
															{name: 'KLArea_id', type: 'int'},
															{name: 'KLArea_Name', type: 'string'}
														],
														key: 'KLArea_id',
														sortInfo: {
															field: 'KLArea_Name'
														},
														url: C_LOAD_ADDRCOMBO
													}),
													tabIndex: TABINDEX_PERSCARDSW + 50,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{KLArea_Name}',
														'</div></tpl>'
													),
													triggerAction: 'all',
													valueField: 'KLArea_id',
													width: 300,
													xtype: 'combo'
												}
											]
										},
										{
											border: false,
											layout: 'form',
											columnWidth: .5,
											items: [
												{
													areaLevel: 3,
													disabled: false,
													displayField: 'KLArea_Name',
													enableKeyEvents: true,
													fieldLabel: 'Город',
													hiddenName: 'KLCity_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															var form = this.findById('PersonCardViewAllFilterForm').getForm();
															if (newValue != null && combo.getRawValue().toString().length > 0) {
																loadAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': form.findField('KLCountry_id'),
																		'Region': form.findField('KLRgn_id'),
																		'SubRegion': form.findField('KLSubRgn_id'),
																		'City': form.findField('KLCity_id'),
																		'Town': form.findField('KLTown_id'),
																		'Street': form.findField('KLStreet_id')
																	},
																	0,
																	combo.getValue(),
																	true
																);
															}
															else {
																clearAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': form.findField('KLCountry_id'),
																		'Region': form.findField('KLRgn_id'),
																		'SubRegion': form.findField('KLSubRgn_id'),
																		'City': form.findField('KLCity_id'),
																		'Town': form.findField('KLTown_id'),
																		'Street': form.findField('KLStreet_id')
																	}
																);
															}
														}.createDelegate(this),
														'keydown': function (combo, e) {
															if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
																combo.fireEvent('change', combo, null, combo.getValue());
															}
														},
														'select': function (combo, record, index) {
															if (record.get('KLArea_id') == combo.getValue()) {
																combo.collapse();
																return false;
															}
															combo.fireEvent('change', combo, record.get('KLArea_id'));
														}
													},
													minChars: 0,
													mode: 'local',
													queryDelay: 250,
													store: new Ext.data.JsonStore({
														autoLoad: false,
														fields: [
															{name: 'KLArea_id', type: 'int'},
															{name: 'KLArea_Name', type: 'string'}
														],
														key: 'KLArea_id',
														sortInfo: {
															field: 'KLArea_Name'
														},
														url: C_LOAD_ADDRCOMBO
													}),
													tabIndex: TABINDEX_PERSCARDSW + 51,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{KLArea_Name}',
														'</div></tpl>'
													),
													triggerAction: 'all',
													valueField: 'KLArea_id',
													width: 300,
													xtype: 'combo'
												},
												{
													areaLevel: 4,
													disabled: false,
													displayField: 'KLArea_Name',
													enableKeyEvents: true,
													fieldLabel: 'Населенный пункт',
													hiddenName: 'KLTown_id',
													listeners: {
														'change': function (combo, newValue, oldValue) {
															var form = this.findById('PersonCardViewAllFilterForm').getForm();
															if (newValue != null && combo.getRawValue().toString().length > 0) {
																loadAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': form.findField('KLCountry_id'),
																		'Region': form.findField('KLRgn_id'),
																		'SubRegion': form.findField('KLSubRgn_id'),
																		'City': form.findField('KLCity_id'),
																		'Town': form.findField('KLTown_id'),
																		'Street': form.findField('KLStreet_id')
																	},
																	0,
																	combo.getValue(),
																	true
																);
															}
															else {
																clearAddressCombo(
																	combo.areaLevel,
																	{
																		'Country': form.findField('KLCountry_id'),
																		'Region': form.findField('KLRgn_id'),
																		'SubRegion': form.findField('KLSubRgn_id'),
																		'City': form.findField('KLCity_id'),
																		'Town': form.findField('KLTown_id'),
																		'Street': form.findField('KLStreet_id')
																	}
																);
															}
														}.createDelegate(this),
														'keydown': function (combo, e) {
															if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
																combo.fireEvent('change', combo, null, combo.getValue());
															}
														},
														'select': function (combo, record, index) {
															if (record.get('KLArea_id') == combo.getValue()) {
																combo.collapse();
																return false;
															}
															combo.fireEvent('change', combo, record.get('KLArea_id'));
														}
													},
													minChars: 0,
													mode: 'local',
													queryDelay: 250,
													store: new Ext.data.JsonStore({
														autoLoad: false,
														fields: [
															{name: 'KLArea_id', type: 'int'},
															{name: 'KLArea_Name', type: 'string'}
														],
														key: 'KLArea_id',
														sortInfo: {
															field: 'KLArea_Name'
														},
														url: C_LOAD_ADDRCOMBO
													}),
													tabIndex: TABINDEX_PERSCARDSW + 52,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{KLArea_Name}',
														'</div></tpl>'
													),
													triggerAction: 'all',
													valueField: 'KLArea_id',
													width: 300,
													xtype: 'combo'
												},
												{
													disabled: false,
													displayField: 'KLStreet_Name',
													enableKeyEvents: true,
													fieldLabel: 'Улица',
													hiddenName: 'KLStreet_id',
													minChars: 0,
													mode: 'local',
													queryDelay: 250,
													store: new Ext.data.JsonStore({
														autoLoad: false,
														fields: [
															{name: 'KLStreet_id', type: 'int'},
															{name: 'KLStreet_Name', type: 'string'}
														],
														key: 'KLStreet_id',
														sortInfo: {
															field: 'KLStreet_Name'
														},
														url: C_LOAD_ADDRCOMBO
													}),
													tabIndex: TABINDEX_PERSCARDSW + 53,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{KLStreet_Name}',
														'</div></tpl>'
													),
													triggerAction: 'all',
													valueField: 'KLStreet_id',
													width: 300,
													xtype: 'combo'
												},
												{
													border: false,
													layout: 'column',
													items: [
														{
															border: false,
															layout: 'form',
															items: [
																{
																	disabled: false,
																	fieldLabel: 'Дом',
																	name: 'Address_House',
																	tabIndex: TABINDEX_PERSCARDSW + 54,
																	width: 100,
																	maskRe: /[^%]/,
																	xtype: 'textfield'
																}
															]
														},
														{
															border: false,
															labelWidth: 220,
															layout: 'form',
															items: [
																{
																	tabIndex: TABINDEX_PERSCARDSW + 55,
																	width: 100,
																	xtype: 'swklareatypecombo'
																}
															]
														}
													]
												},
												{
													disabled: false,
													fieldLabel: 'Корпус',
													name: 'Address_Corpus',
													tabIndex: TABINDEX_PERSCARDSW + 54,
													width: 100,
													maskRe: /[^%]/,
													xtype: 'textfield'
												}
											]
										}
									]
								}
							],
							layoutOnTabChange: true,
							listeners: {
								'tabchange': function (tab, panel) {
									var els = panel.findByType('textfield', false);
									if (els == undefined)
										els = panel.findByType('combo', false);
									var el = els[0];
									if (el != undefined && el.focus)
										el.focus(true, 200);
								}
							}
						})
					],
					keys: [
						{
							key: Ext.EventObject.ENTER,
							fn: function (e) {
								Ext.getCmp('PersonCardViewAllWindow').doSearch();
							},
							stopEvent: true
						},
						{
							key: Ext.EventObject.INSERT,
							fn: function () {
								Ext.getCmp('PersonCardViewAllWindow').addPerson();
							},
							stopEvent: true
						}
					],
					labelAlign: 'right',
					region: 'north'
				}),
				this.PersonCardGridPanel = new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: getWnd('swWorkPlacePolkaLLOWindow').isVisible() || getRegionNick()=='kz', handler: function(){ this.addPerson(); }.createDelegate(this) },
						{ name: 'action_edit', disabled: getWnd('swWorkPlacePolkaLLOWindow').isVisible(), handler: function(){ this.openPersonEdit(!this.viewOnly); }.createDelegate(this) },
						{ name: 'action_view', handler: function(){ this.openPersonEdit(false); }.createDelegate(this) },
						{ name: 'action_delete', disabled: true, hidden: true },
						{
							name: 'action_print',
							menuConfig: {
								printObject: { text: 'Печать бланка ТАП', handler: function(){ this.printEvnPL(); }.createDelegate(this) },
								printEvnPLOld: { name: 'printEvnPLOld', hidden: getRegionNick() == 'kareliya', text: 'Печать бланка ТАП (до 2015г)', handler: function(){ this.printEvnPLOld(); }.createDelegate(this) }
							}
						}
					],
					id: 'PCVAW_PersonSearchGrid',
					editing: true,
					autoLoadData: false,
					dataUrl: C_PERSONCARD_GRID,
					pageSize: 100,
					paging: true,
					root: 'data',
					totalProperty: 'totalCount',
					region: 'center',
					stringfields: [
						{name: 'Person_id', type: 'int', header: 'ID', key: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'PersonEvn_id', type: 'int', hidden: true},
						{name: 'PersonCard_IsDmsForCheck',type: 'int', hidden: true},
						{name: 'Person_SurName', type: 'string', header: 'Фамилия', id: 'autoexpand'},
						{name: 'Person_FirName', type: 'string', header: 'Имя', width: 120},
						{name: 'Person_SecName', type: 'string', header: 'Отчество', width: 120},
						{name: 'PersonBirthDay', type: 'date', format: 'd.m.Y', header: 'Дата рождения', width: 70},
						{name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: 'Дата смерти', width: 70},
						{name: 'Lpu_Nick', type: 'string', header: 'ЛПУ прикрепления', width: 120},
						{name: 'PersonCard_IsDms', type: 'checkbox', header: 'Прикреплен по ДМС', width: 120},
						{name: 'Person_IsBDZ', type: 'checkbox', header: 'БДЗ', width: 35,
							qtip: function(value, metadata, record) {
								var qtip = null;
								switch (record.get('Person_IsBDZ')) {
									case 'true': qtip = 'Человек идентифицирован в РС ЕРЗ';break;
									case 'false': qtip = 'Человек идентифицирован в ЦС ЕРЗ';break;
									case 'yellow': qtip = 'Требуется уточнение данных страхования';break;
									case 'red': qtip = 'У человека указана дата смерти';break;
									case 'blue': qtip = 'Человек не идентифицирован в ЦС ЕРЗ';break;
								}
								return qtip;
							}
						},
						{name: 'Person_IsFedLgot', type: 'checkbox', header: (getRegionNick().inlist([ 'kz' ]) ? 'Льгота' : 'Фед.льг.'), width: 50},
						{name: 'Person_IsRefuse', type: 'checkbox', header: 'Отказ', width: 50},
						{name: 'Person_IsRegLgot', type: 'checkbox', header: 'Рег. льг', width: 50, hidden: getRegionNick().inlist([ 'kz' ])},
						{name: 'Person_Is7Noz', type: 'checkbox', header: '7 ноз.', width: 50},
						{name: 'Person_IsDead', type: 'checkbox', header: 'Умер', width: 50},
						{name: 'Person_UAddress', type: 'string', header: 'Адрес регистрации', width: 240},
						{name: 'Person_PAddress', type: 'string', header: 'Адрес проживания', width: 240}
					],
					onBeforeLoadData: function() {
						//
					}.createDelegate(this),
					onLoadData: function() {
						//
					}.createDelegate(this),
					onRowSelect: function(sm, index, record) {
						var tab_panel = Ext.getCmp('PCVAW_regions_tab_panel');
						tab_panel.setActiveTab(0);
					}
				}),
				new Ext.Panel({
					region: 'south',
					layout: 'border',
					border: false,
					height: 150,
					split: true,
					items: [
						new Ext.TabPanel({
							id: 'PCVAW_regions_tab_panel',
							activeTab: 0,
							border: false,
							layoutOnTabChange: true,
							listeners: {
								'beforetabchange': function (panel, tab) {
                                    //if(tab.id == 'common_region' && getRegionNick()=='perm')
									var LpuRegionType_Name_index = Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().colModel.findColumnIndex('LpuRegionType_Name');
									var LpuRegion_Name_index = Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().colModel.findColumnIndex('LpuRegion_Name');
									var LpuRegionFap_Name_index = Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().colModel.findColumnIndex('LpuRegion_FapName');
									if(tab.id == 'common_region' && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']))
                                    {
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(LpuRegionType_Name_index,'Тип основного участка');
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(LpuRegion_Name_index,'Основной участок');
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setHidden(LpuRegionFap_Name_index,false);
                                    }
                                    else
                                    {
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(LpuRegionType_Name_index,'Тип участка');
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(LpuRegion_Name_index,'Участок');
                                        Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid().getColumnModel().setHidden(LpuRegionFap_Name_index,true);
                                    }

									var current_window = Ext.getCmp('PersonCardViewAllWindow');
									var card_history_grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
									card_history_grid.getStore().removeAll();
									var person_search_grid = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid();
									/*if ( tab.id == 'dms_region' )
									 Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_delete', true);
									 else
									 Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_delete', false);
									 */
									var selected_person_row = person_search_grid.getSelectionModel().getSelected();
									if (selected_person_row && selected_person_row.data.Person_id > 0) {
										Ext.getCmp('PCVAW_PersonCardHistoryGrid').loadData({
											globalFilters: {
												Person_id: selected_person_row.data.Person_id,
												AttachType: tab.id
											},
											noFocusOnLoad: true
										});
									}
								}
							},
							region: 'north',
							items: [
								{
									id: 'common_region',
									title: '1. Основное',
									height: 0,
									style: 'padding: 0px',
									layout: 'form',
									items: []
								},
								{
									title: '2. Гинекология',
									id: 'ginecol_region',
									height: 0,
									style: 'padding: 0px',
									layout: 'form',
									items: []
								},
								{
									title: '3. Стоматология',
									id: 'stomat_region',
									height: 0,
									style: 'padding: 0px',
									layout: 'form',
									items: []
								},
								{
									title: '4. Служебный',
									id: 'service_region',
									height: 0,
									style: 'padding: 0px',
									layout: 'form',
									items: []
								},
								{
									title: '5. ДМС',
									id: 'dms_region',
									hidden: !getGlobalOptions().lpu_is_dms,
									height: 0,
									style: 'padding: 0px',
									layout: 'form',
									items: []
								}
							]
						}),
						new sw.Promed.ViewFrame(
							{
								actions: [
									{name: 'action_add', tooltip: 'Новое прикрепление пациента можно создавать только один раз в день', handler: function () {
                                        _this.allowEditLpuRegion = 0;
										Ext.getCmp('PersonCardViewAllWindow').openPersonCardEditWindow({action: 'add'});
									}},
									{name: 'action_edit', handler: function () {
                                        _this.allowEditLpuRegion = 0;
										Ext.getCmp('PersonCardViewAllWindow').openPersonCardEditWindow({action: 'edit'});
									}},
									{name: 'action_view', handler: function () {
                                        _this.allowEditLpuRegion = 0;
										Ext.getCmp('PersonCardViewAllWindow').openPersonCardEditWindow({action: 'view'});
									}},
									{name: 'action_delete', handler: function () {
										Ext.getCmp('PersonCardViewAllWindow').deletePersonCard();
									}},
									{name: 'action_refresh'},
									{name: 'action_print', menuConfig: {
										printObject: {
											text: (getRegionNick()=='kz')?'Печать заявления о прикреплении':'Печать заявления о выборе МО/Инф. согласия',//https://redmine.swan.perm.ru/issues/56487
											handler: function(){this.printAttachBlank();}.createDelegate(this)
										}
									}}
								],
								//					autoExpandColumn: 'autoexpand',
								autoLoadData: false,
								border: false,
								dataUrl: C_PERSONCARD_HIST,
								id: 'PCVAW_PersonCardHistoryGrid',
								focusOnFirstLoad: false,
								focusOn: {name: 'PCVAW_SearchButton', type: 'field'},
								//					object: 'LpuUnit',
								region: 'center',
								//editformclassname: swLpuUnitEditForm,
								stringfields: [
									{name: 'PersonCard_id', type: 'int', header: 'ID', key: true},
									{name: 'Person_id', type: 'int', hidden: true},
									{name: 'Server_id', type: 'int', hidden: true},
									{name: 'CardCloseCause_id', type: 'int', hidden: true},
									{name: 'CardCloseCause_SysNick', type: 'string', hidden: true},
									{name: 'PersonCard_Code', type: 'string', header: '№ амб карты'},
									{name: 'PersonCard_insDate', type: 'date', hidden: true},
									{name: 'PersonCard_begDate', type: 'date', header: 'Прикрепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
									{name: 'PersonCard_endDate', type: 'date', header: 'Открепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
									{name: 'Lpu_id', type: 'int', hidden: true},
									{name: 'Lpu_Nick', type: 'string', header: 'ЛПУ прикрепления', width: 200},
									{name: 'isPersonCardAttach', header: 'Заявление', type: 'checkbox', sortable: true, width: 80},
									{name: 'PersonCardAttach_insDT', type: 'date', hidden: true, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
									{name: 'PersonCard_IsDms', type: 'checkbox', header: 'Прикреплен по ДМС', width: 120},
									{name: 'Is_OurLpu', type: 'string', hidden: true},
									{name: 'PersonCard_IsDmsForCheck', type: 'string', hidden: true},
									{name: 'Person_HasPolis', type: 'string', hidden: true},
									{name: 'Person_HasDmsOtherLpu', type: 'string', hidden: true},
									{name: 'LpuRegionType_Name', type: 'string', header: 'Тип участка', width: 150},
									{name: 'LpuRegion_Name', type: 'string', header: 'Участок', width: 150},
                                    {name: 'LpuRegion_FapName', type: 'string', header: 'ФАП участок',width:100},
									{name: 'CardCloseCause_Name', type: 'string', header: 'Причина закрытия', width: 150},
									{name: 'PersonCard_IsAttachCondit', header: 'Усл. прикрепл.', width: 150, type: 'checkbox'},
									{name: 'AmbulatCardLocatType_Name', header: 'Местонахождение амб. карты', width: 150, type: 'string'},
									{name: 'PersonAmbulatCard_id',hidden:true,type:'int'}
								],
								toolbar: true,
								onRowSelect: function (sm, rowIdx, record) {
									var type_panel = Ext.getCmp('PCVAW_regions_tab_panel');
									var person_grid = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid(),
										person_row = person_grid.getSelectionModel().getSelected(),
										disable = true,
										options = getGlobalOptions();
									if ((!person_row) || (person_grid.getStore().getCount() == 0))
										return;
									if ((!record) || !(record.get('PersonCard_id') > 0))
										return;
									if (true) {
										disable = !(
											record.get('PersonCard_endDate') == '' &&
												record.get('Is_OurLpu') == 'true'  /*&& (
											 Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date ||
											 person_row.get('Person_IsBDZ') == 'false' ||
											 record.get('PersonCard_IsAttachCondit') == 'true'
											 )*/
											);
										if (Ext.getCmp('PCVAW_regions_tab_panel').getActiveTab().id == 'dms_region')
											disable = false;
										if (isSuperAdmin())
											disable = false;
                                        //_this.allowEditLpuRegion
                                        
                                        if(getRegionNick() == 'astra' || getRegionNick() == 'perm')
                                        {
                                            this.setActionDisabled('action_add_lpuregion', true);
                                            if(Ext.isEmpty(record.get('LpuRegion_Name')) && record.get('PersonCard_endDate') == '')
                                                this.setActionDisabled('action_add_lpuregion', false);
                                            else
                                                _this.allowEditLpuRegion = 0;
                                        }
                                        else
                                            _this.allowEditLpuRegion = 0;
                                        /*if(getRegionNick() == 'astra' && Ext.isEmpty(record.get('LpuRegion_Name')) && record.get('PersonCard_endDate') == '')
                                        {
                                            action_add_lpuregion
                                            _this.allowEditLpuRegion = 1;
                                        }
                                        else
                                        {
                                            _this.allowEditLpuRegion = 0;
                                        }*/
										this.setActionDisabled('action_edit', disable);
									}
									if (true) {
										disable = true;
										//disable = !( record.get('PersonCard_endDate') == '' && Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date && record.get('Is_OurLpu') == 'true' && person_row.get('Person_IsBDZ') == 'true');
										if ((isSuperAdmin() || isLpuAdmin()) && record.get('PersonCard_endDate') == '')
											disable = false;

                                        //Добавил в рамках задачи https://redmine.swan.perm.ru/issues/68071 и https://redmine.swan.perm.ru/issues/84087
                                        if(
											getRegionNick().inlist([ 'ufa', 'khak' ]) && record.get('Is_OurLpu') == 'true' &&
                                            record.get('PersonCard_endDate') == '' &&
                                            (
                                                (
                                                    isPolkaRegistrator() &&
                                                    Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date
                                                )
                                                || isLpuAdmin()
                                            )
                                        )
                                        {
                                            disable = false;
                                        }

                                        if (Ext.util.Format.date(record.get('PersonCard_insDate'), 'd.m.Y') == options.date && record.get('Is_OurLpu') == 'true'){
                                        	disable = false;
                                        }
										this.setActionDisabled('action_delete', disable);
									}
									if (true) {
										disable = false;
										disable = (Ext.isEmpty(record.get('PersonAmbulatCard_id')));
										this.setActionDisabled('action_openac', disable);
										this.setActionHidden('action_openac',disable);
									}
									if (true) {
										disable = !(Ext.isEmpty(record.get('PersonCard_endDate')) && Ext.isEmpty(record.get('CardCloseCause_id')) && type_panel.getActiveTab().id == 'common_region');
										if(getRegionNick()=='ufa' && type_panel.getActiveTab().id == 'service_region' && (Ext.isEmpty(record.get('PersonCard_endDate')) && Ext.isEmpty(record.get('CardCloseCause_id'))))
											disable = false;
										if (this.getAction('action_print').menu) {
											if(getRegionNick()=='ufa' && type_panel.getActiveTab().id == 'service_region')
												this.getAction('action_print').menu.printObject.initialConfig.text = 'Печать инф. согласия';
											this.getAction('action_print').menu.printObject.setDisabled(disable);
											this.initActionPrint();
										}
									}
									if (!isSuperAdmin())
										_this.checkAttachAllow();
								},
								onLoadData: function (data_is_exists) {
									var type_panel = Ext.getCmp('PCVAW_regions_tab_panel'),
										pc_grid = this.getGrid(),
										disable = true,
										options = getGlobalOptions(),
										index;
									if (true) {
										this.setActionDisabled('action_openac', true);
										this.setActionHidden('action_openac',true);
									}
									if (data_is_exists) {
										pc_grid.getSelectionModel().selectFirstRow();
										//Закоментировал, чтобы фокус оставался на этом гриде. Для хоткеев
										//pc_grid.getView().focusRow(0);
									}
									if (true) {
										if (!data_is_exists)
											disable = false;
										else {
											//ищем созданное сегодня актуальное (незакрытое) прикрепление
											index = pc_grid.getStore().findBy(function (record, id) {
												if (record.get('PersonCard_endDate') == '' && Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date)
													return true;
												else
													return false;
											});
											disable = (index >= 0);
										}
										if (type_panel.getActiveTab().id == 'service_region')
											disable = false;
										if (isSuperAdmin())
											disable = false;
										if (getRegionNick() == 'kz' &&  type_panel.getActiveTab().id.inlist(['common_region','ginecol_region','stomat_region','service_region','dms_region'])) {
											disable = true;
										}
										this.setActionDisabled('action_add', disable);
									}
									
									_this.checkAttachAllow();
								}
							})
					],
					keys: [
						{
							key: "0123456789",
							alt: true,
							fn: function (e) {
								Ext.getCmp("PCVAW_regions_tab_panel").setActiveTab(Ext.getCmp("PCVAW_regions_tab_panel").items.items[ e - 49 ]);
							},
							stopEvent: true
						}
					]
				})
			]
		});
		this.PersonCardGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass : function (row, index)
				{
					var cls = '';

					if(row.get('Person_deadDT')){
						cls = cls+'x-grid-rowgray ';
					}
					return cls;
				},
				listeners:
				{
					rowupdated: function(view, first, record)
					{
						//log('update');
						view.getRowClass(record);
					}
				}
			});
		sw.Promed.swPersonCardViewAllWindow.superclass.initComponent.apply(this, arguments);

		
		/* Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewToolbar.on('render', function(vt){
		 var a = new Ext.Action({name:'action_openac', id: 'id_action_openac', handler: function() {this.openPersonAmbulatCard();}.createDelegate(this), text:'Амбулаторная карта', tooltip: 'Амбулаторная карта'});
		 vt.insertButton(1,a);
		 Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewActions['action_openac'] = a;
		 Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionDisabled('action_openac', true);
		 return true;
		 }, this);*/
		 

	},
	keys: [
		{
			key: Ext.EventObject.INSERT,
			fn: function (e) {/*Ext.getCmp("PersonCardViewAllWindow").addPersonCard();*/
			},
			stopEvent: true
		},
		{
			key: "0123456789",
			alt: true,
			fn: function (e) {
				Ext.getCmp("PersonCardViewAllFilterTabPanel").setActiveTab(Ext.getCmp("PersonCardViewAllFilterTabPanel").items.items[ e - 49 ]);
			},
			stopEvent: true
		},
		{
			alt: true,
			fn: function (inp, e) {
				var current_window = Ext.getCmp('PersonCardViewAllWindow');
				switch (e.getKey()) {
					case Ext.EventObject.J:
						current_window.hide();
						break;
					case Ext.EventObject.C:
						current_window.doResetAll();
						break;
				}
			},
			key: [ Ext.EventObject.J, Ext.EventObject.C ],
			stopEvent: true
		}
	],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	/*
	 closePersonCardNotBdz: function() {
	 var win = this;
	 var person_grid = Ext.getCmp('PCVAW_PersonSearchGrid');
	 var person_row = person_grid.getSelectionModel().getSelected();
	 var regions_tab_panel = Ext.getCmp('PCVAW_regions_tab_panel');
	 var pc_grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
	 var pc_row = pc_grid.getSelectionModel().getSelected();
	 if ( (!person_row) || (person_grid.getStore().getCount()==0) )
	 return;
	 if ( (!pc_row) || (pc_grid.getStore().getCount()==0) || !(pc_row.get('PersonCard_id') > 0))
	 return;
	 //Откреплять можно, только если пациент не из БДЗ и прикрепление соответствует ЛПУ текущего пользователя.
	 if ( (person_row.get('Person_IsBDZ') == 'true') || (pc_row.get('Is_OurLpu') == 'false') || (Ext.util.Format.date(pc_row.get('PersonCard_endDate'), 'd.m.Y') != ''))
	 return;

	 Ext.Ajax.request({
	 url: '?c=PersonCard&m=closePersonCardNotBdz',
	 params: {PersonCard_id: pc_row.get('PersonCard_id')},
	 callback: function(options, success, response) {
	 if (success)
	 {
	 if ( response.responseText.length > 0 )
	 {
	 var resp_obj = Ext.util.JSON.decode(response.responseText);
	 if (resp_obj[0].success == false)
	 {
	 return false;
	 }
	 else
	 {
	 regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
	 }
	 }
	 }
	 }
	 });
	 },*/
	openPersonAmbulatCard: function() {
		var win = this;
		var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').getGrid();
		var params = {};
		var record = grid.getSelectionModel().getSelected();
		if(!record|| !record.get('PersonAmbulatCard_id')){
			var msg = 'Запись о прикреплении не связана с Амбулаторной картой';
			Ext.Msg.alert("Ошибка", msg);
			return false;
		}
		params ={
			action:win.viewOnly?'view':'edit',
			PersonAmbulatCard_id:record.get('PersonAmbulatCard_id')
		}

		params.callback= function(){
			grid.getStore().reload();
		}
		getWnd('swPersonAmbulatCardEditWindow').show(params);
	 
	},
	deletePersonCard: function () {
		var person_grid = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid();
		var current_person_row = person_grid.getSelectionModel().getSelected();
		if ((!current_person_row) || (person_grid.getStore().getCount() == 0))
			return;
		var Person_IsBDZ = current_person_row.data.Person_IsBDZ;
		var Person_IsFedLgot = current_person_row.data.Person_IsFedLgot;
		var current_window = this;
		var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
		var regions_tab_panel = Ext.getCmp('PCVAW_regions_tab_panel');
		var current_row = grid.getSelectionModel().getSelected();
		if (current_row && current_row.get('PersonCard_id') > 0) {
			var options = getGlobalOptions();
			var date = options['date'];

			/*
			 *	1. Если прикрепление не активное, то вывести сообщение: «Закрытое прикрепление не может быть удалено. (Ок)»:
			 *	Ок – форму закрыть, дальнейшие действия отменить.
			 */
			if (Ext.util.Format.date(current_row.data.PersonCard_endDate, 'd.m.Y') != '' && regions_tab_panel.getActiveTab().id != 'dms_region') {
				Ext.Msg.alert('Ошибка', 'Закрытое прикрепление не может быть удалено.', function () {
					if (grid.getStore().getCount() > 0) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}
			/*
			 *	2. Если пользователь не Администратор ЦОД и МУ прикрепления не равно МУ
			 *	Пользователя, то вывести сообщение: «Прикрепление к другому МУ удалено быть не может (Ок)»:
			 *	Ок – форму закрыть, дальнейшие действия отменить.
			 */
			if (current_row.data.Is_OurLpu == 'false' && !getGlobalOptions().superadmin) {
				Ext.Msg.alert('Ошибка', 'Прикрепление к другому МУ удалено быть не может.', function () {
					var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
					if (grid.getStore().getCount() > 0) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}

			/*if (regions_tab_panel.getActiveTab().id == 'common_region' && current_row.get('PersonCard_IsDmsForCheck') == 'true') {
				Ext.Msg.alert('Ошибка', 'Нельзя удалять основное прикрепление при наличии действующего прикрепления по ДМС.', function () {
					var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
					if (grid.getStore().getCount() > 0) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}*/


			if (regions_tab_panel.getActiveTab().id == 'dms_region') {
				if (Ext.util.Format.date(current_row.data.PersonCard_insDate, 'd.m.Y') == date) {
					// остальные прикрепления
					sw.swMsg.show({
						title: 'Подтверждение удаления',
						msg: 'Вы действительно желаете удалить эту запись?',
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId) {
							if (buttonId == 'yes') {
								Ext.Ajax.request({
									url: '/?c=PersonCard&m=deleteDmsPersonCard',
									params: {PersonCard_id: current_row.data.PersonCard_id},
									callback: function () {
										regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
									}
								});
							}
						}
					});
					return false;
				}
				else {
					Ext.Msg.alert('Ошибка', 'ДМС прикрепление может быть удалено только в дату создания.', function () {
						regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
						return false;
					});
				}
				return false;
			}

			/*
			 * 3. Если дата прикрепления не текущая и у человека нет действующего полиса и МУ прикрепления равно МУ Пользователя, вывести сообщение: «Открепить пациента? (Да/Нет):
			 *	- Да - установить дату открепления равной текущей дате, и
			 *	если у человека проставлена "дата смерти", то в поле "причина закрытия" подставить значение "2. смерть",
			 *	если у человека не проставлена "дата смерти", то в поле "причина закрытия" подставить значение "5. снялся с учета",
			 *	т.е. пациент останется не прикрепленным ни к одной МУ, форму закрыть, дальнейшие действия отменить.
			 *	- Нет – форму закрыть, дальнейшие действия отменить.
			 */
            var do_delete = false;
            if (
                (getGlobalOptions().region.nick == 'khak')
                &&
                Ext.util.Format.date(current_row.data.PersonCard_endDate, 'd.m.Y') == ''
                &&
                (
                    (
                        isPolkaRegistrator()
                        &&
                        Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') == date
                    )
                    ||
                    isLpuAdmin()
                    ||
                    isSuperAdmin()
                )
            )
            {
                do_delete = true;
            }

            if((isSuperAdmin() || isLpuAdmin()) && Ext.util.Format.date(current_row.data.PersonCard_endDate, 'd.m.Y') == '')
            	do_delete = true;	

            if(Ext.util.Format.date(current_row.data.PersonCard_insDate, 'd.m.Y') == date && current_row.data.Is_OurLpu == 'true')
            {
            	do_delete = true;
            }
            
            if(grid.getStore().getCount() == 1) //Если это последняя запись - https://redmine.swan.perm.ru/issues/108560
        		do_delete = true;

            if(getRegionNick().inlist(['astra','vologda']) && current_row.data.isPersonCardAttach == 'true')
            {
            	Ext.Msg.alert('Ошибка','Для удаления прикрепления необходимо предварительно удалить заявление о выборе МО от ' + Ext.util.Format.date(current_row.data.PersonCardAttach_insDT, 'd.m.Y'));
            	return false;
            }

			var Person_HasPolis = current_row.data.Person_HasPolis;
			if (Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date && Person_HasPolis == 'false' && current_row.data.Is_OurLpu == 'true' && !do_delete) {
				sw.swMsg.show({
					title: 'Подтверждение открепления',
					msg: 'Открепить пациента?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: '?c=PersonCard&m=closePersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function (options, success, response) {
									if (success) {
										if (response.responseText.length > 0) {
											var resp_obj = Ext.util.JSON.decode(response.responseText);
											if (resp_obj[0].success == false) {
												if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666) {
													sw.swMsg.show({
														title: 'Подтверждение открепления',
														msg: resp_obj[0].Error_Msg,
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																Ext.Ajax.request({
																	url: '?c=PersonCard&m=closePersonCard',
																	params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																	callback: function (options, success, response) {
																		regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																	}
																});
															}
														}
													});
												}
											}
										}
									}
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});
				return false;
			}

			/*
			 * 4. Если дата прикрепления не текущая и человек не из БДЗ и МУ прикрепления равно МУ Пользователя, вывести сообщение: «Открепить пациента? (Да/Нет):
			 *	- Да - установить дату открепления равной текущей дате и в поле "причина закрытия" подставить значение "5. снялся с учета", т.е. пациент останется не прикрепленным ни к одной МУ, форму закрыть, дальнейшие действия отменить.
			 *	- Нет – форму закрыть, дальнейшие действия отменить.
			 */

			if (Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date && Person_IsBDZ == 'false' && current_row.data.Is_OurLpu == 'true' && !do_delete) {
				sw.swMsg.show({
					title: 'Подтверждение открепления',
					msg: 'Открепить пациента?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: '?c=PersonCard&m=closePersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function (options, success, response) {
									if (success) {
										if (response.responseText.length > 0) {
											var resp_obj = Ext.util.JSON.decode(response.responseText);
											if (resp_obj[0].success == false) {
												if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666) {
													sw.swMsg.show({
														title: 'Подтверждение открепления',
														msg: resp_obj[0].Error_Msg,
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																Ext.Ajax.request({
																	url: '?c=PersonCard&m=closePersonCard',
																	params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																	callback: function (options, success, response) {
																		regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																	}
																});
															}
														}
													});
												}
											}
										}
									}
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});

				return false;
			}

			/*
			 * 5. Если дата прикрепления не текущая и человек из БДЗ и Пользователь – Пользователь МУ вывести сообщение
			 * «Прикрепление застрахованного человека может быть удалено только в дату прикрепления (Ок)»:
			 * Ок – форму закрыть, дальнейшие действия отменить.
			 */
			if (Ext.util.Format.date(current_row.data.PersonCard_insDate, 'd.m.Y') != date && Person_IsBDZ == 'true' && !getGlobalOptions().superadmin) {
				Ext.Msg.alert('Ошибка', 'Прикрепление застрахованного человека может быть удалено только в дату прикрепления.', function () {
					var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
					if (grid.getStore().getCount() > 0) {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}

			/*
			 * 6. Удалить прикрепление с удалением информации об откреплении в записи о предыдущем прикреплении.
			 * Дальнейшие действия отменить. (т.е для Администратора ЦОД д.б. возможность удаления прикрепления в любой день),
			 * Если текущая вкладка набора вкладок «Прикрепление» - «4. Служебный», то не удалять информацию об откреплении в записи о предыдущем прикреплении.
			 */
			// закрываем карты не основного прикрепления
			if (
                (regions_tab_panel.getActiveTab().id == 'service_region')
                &&
                (getGlobalOptions().region.nick != 'ufa')
                && !do_delete
            )
            {
				sw.swMsg.show({
					title: 'Подтверждение открепления',
					msg: 'Открепить пациента?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: '?c=PersonCard&m=closePersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function (options, success, response) {
									if (success) {
										if (response.responseText.length > 0) {
											var resp_obj = Ext.util.JSON.decode(response.responseText);
											if (resp_obj[0].success == false) {
												if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666) {
													sw.swMsg.show({
														title: 'Подтверждение открепления',
														msg: resp_obj[0].Error_Msg,
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																Ext.Ajax.request({
																	url: '?c=PersonCard&m=closePersonCard',
																	params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																	callback: function (options, success, response) {
																		regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																	}
																});
															}
														}
													});
												}
											}
										}
									}
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});
				return false;
			}

			// остальные прикрепления
			sw.swMsg.show({
				title: 'Подтверждение удаления',
				msg: 'Вы действительно желаете удалить эту запись?',
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							url: C_PERSONCARD_DEL,
							params: {PersonCard_id: current_row.data.PersonCard_id},
							callback: function (options, success, response) {
								if (success) {
									if (response.responseText.length > 0) {
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if (resp_obj[0] && resp_obj[0].success == false) {
											if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 777) {
												sw.swMsg.show({
													title: 'Подтверждение открепления',
													msg: resp_obj[0].Error_Msg,
													buttons: Ext.Msg.YESNO,
													fn: function (buttonId) {
														if (buttonId == 'yes') {
															Ext.Ajax.request({
																url: C_PERSONCARD_DEL,
																params: {PersonCard_id: current_row.data.PersonCard_id, isLastAttach: 2},
																callback: function (options, success, response) {
																	regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																}
															});
														}
													}
												});
											}
										} else {
											regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
										}
									}
								}
							}
						});
					}
				}
			});

			return false;

			// !!!!!!!!! старые проверки !!!!!!!!!!
			if (Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') == date) {
				sw.swMsg.show({
					title: 'Подтверждение удаления',
					msg: 'Вы действительно желаете удалить эту карту?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: C_PERSONCARD_DEL,
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function () {
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});
				return;
			}


			// закрываем не БДЗшных и не федералов
			if (Person_IsBDZ == "false" && Person_IsFedLgot == "false") {
				sw.swMsg.show({
					title: 'Подтверждение открепления',
					msg: 'Открепить пациента?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: '?c=PersonCard&m=closePersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function (options, success, response) {
									if (success) {
										if (response.responseText.length > 0) {
											var resp_obj = Ext.util.JSON.decode(response.responseText);
											if (resp_obj[0].success == false) {
												if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666) {
													sw.swMsg.show({
														title: 'Подтверждение открепления',
														msg: resp_obj[0].Error_Msg,
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																Ext.Ajax.request({
																	url: '?c=PersonCard&m=closePersonCard',
																	params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																	callback: function (options, success, response) {
																		regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																	}
																});
															}
														}
													});
												}
											}
										}
									}
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});
				return;
			}
			// закрываем карты не основного прикрепления
			if ((regions_tab_panel.getActiveTab().id == 'service_region')&&(getGlobalOptions().region.nick != 'ufa')) {
				sw.swMsg.show({
					title: 'Подтверждение открепления',
					msg: 'Открепить пациента?',
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							Ext.Ajax.request({
								url: '?c=PersonCard&m=closePersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function (options, success, response) {
									if (success) {
										if (response.responseText.length > 0) {
											var resp_obj = Ext.util.JSON.decode(response.responseText);
											if (resp_obj[0].success == false) {
												if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666) {
													sw.swMsg.show({
														title: 'Подтверждение открепления',
														msg: resp_obj[0].Error_Msg,
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																Ext.Ajax.request({
																	url: '?c=PersonCard&m=closePersonCard',
																	params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																	callback: function (options, success, response) {
																		regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
																	}
																});
															}
														}
													});
												}
											}
										}
									}
									regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
								}
							});
						}
					}
				});
				return;
			}
			if (Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date) {
				if (!getGlobalOptions().superadmin) {
					Ext.Msg.alert('Ошибка', 'Можно удалить карту только в дату создания.', function () {
						var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
						if (grid.getStore().getCount() > 0) {
							grid.getSelectionModel().selectFirstRow();
							grid.getView().focusRow(0);
						}
					});
					return false;
				}
			}
			sw.swMsg.show({
				title: 'Подтверждение удаления',
				msg: 'Вы действительно желаете удалить эту карту?',
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							url: C_PERSONCARD_DEL,
							params: {PersonCard_id: current_row.data.PersonCard_id},
							callback: function () {
								regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
							}
						});
					}
				}
			});
		}
	},
	/**
	 * Открыть форму редактирования человека
	 *
	 * @param {Boolean} isEdit Открыть в режиме редактирования или просмотра?
	 */
	openPersonEdit: function (isEdit) {
		var current_window = Ext.getCmp('PersonCardViewAllWindow');
		var grid = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid();
		var current_row = grid.getSelectionModel().getSelected();
		if ((!current_row) || (grid.getStore().getCount() == 0))
			return;
		var person_id = grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = grid.getSelectionModel().getSelected().data.Server_id;
		if (isEdit) {
			getWnd('swPersonEditWindow').show({
				action: 'edit',
				Person_id: person_id,
				Server_id: server_id,
				callback: function (callback_data) {
					// обновляем грид
					if (callback_data) {
						grid.getStore().each(function (record) {
							if (record.data.Person_id == callback_data.Person_id) {
								record.set('Server_id', callback_data.Server_id);
								record.set('PersonEvn_id', callback_data.PersonEvn_id);
								record.set('Person_SurName', callback_data.PersonData.Person_SurName);
								record.set('Person_FirName', callback_data.PersonData.Person_FirName);
								record.set('Person_SecName', callback_data.PersonData.Person_SecName);
								record.set('PersonBirthDay', callback_data.PersonData.Person_BirthDay);
								record.commit();
							}
						});
					}
					Ext.getCmp('PCVAW_PersonCardHistoryGrid').loadData();
					grid.getView().focusRow(0);
				},

				onClose: function () {
					grid.getView().focusRow(0);
				}
			});
		}
		else {
			getWnd('swPersonEditWindow').show({
				action: 'view',
				readOnly: true,
				Person_id: person_id,
				Server_id: server_id,
				callback: function (callback_data) {
					grid.getView().focusRow(0);
				},
				onClose: function () {
					grid.getView().focusRow(0);
				}
			});
		}

	},
	openPersonCardEditWindow: function (params) {
		var person_search_grid = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid();
		var person_card_grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
		var regions_tab_panel = Ext.getCmp('PCVAW_regions_tab_panel');
		var person_row = person_search_grid.getSelectionModel().getSelected();

		if (person_row && person_row.data.Person_id > 0) {
			var current_window = this;
			if (getWnd('swPersonCardEditWindow').isVisible()) {
				sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
				return false;
			}

			var form_params = {};
			if(person_card_grid.getStore().getCount() == 1)
				form_params.oneCard = true;
			form_params.action = params.action;
			//form_params.real_action = params.action;
			//params.real_action = params.action;
			form_params.callback = function () {
				// перезагружаем текущую вкладку
				regions_tab_panel.setActiveTab(regions_tab_panel.getActiveTab());
			}
			/*form_params.onHide = function() {
			 var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
			 Ext.getCmp('PCVAW_PersonCardHistoryGrid').loadData();
			 if ( grid.getStore().getCount() > 0 )
			 {
			 grid.getSelectionModel().selectFirstRow();
			 grid.getView().focusRow(0);
			 }
			 }*/
			form_params.Person_id = person_row.data.Person_id;
			form_params.PersonEvn_id = person_row.data.PersonEvn_id;
			form_params.Server_id = person_row.data.Server_id;
            form_params.allowEditLpuRegion = this.allowEditLpuRegion;
			if (params.action == 'edit' || params.action == 'view') {
				var person_card_row = person_card_grid.getSelectionModel().getSelected();
				if (person_card_row && person_card_row.data.PersonCard_id > 0) {
					form_params.PersonCard_id = person_card_row.data.PersonCard_id;
					/*
					 var options = getGlobalOptions();
					 var date = options['date'];
					 if ( person_card_row != undefined  && !['service_region'].in_array(regions_tab_panel.getActiveTab().id) && Ext.util.Format.date(person_card_row.data.PersonCard_begDate, 'd.m.Y') != date )
					 {
					 form_params.action = 'view';
					 // мы можем редактировать только номер карты
					 if ( person_card_row.get('PersonCard_IsAttachCondit')=='true' && params.action == 'edit' && !['service_region'].in_array(regions_tab_panel.getActiveTab().id) )
					 form_params.action = 'edit_card_code_only';
					 }
					 // если карта закрыта, то открываем только на просмотр
					 if ( ( params.action == 'edit' || form_params.action == 'edit_card_code_only' ) && !['service_region'].in_array(regions_tab_panel.getActiveTab().id) && person_card_row.data.PersonCard_endDate != '' )
					 {
					 form_params.action = 'view';
					 }
					 */
				}
				// нет выбраной карты, выходим
				else {
					return true;
				}
			}
			// проверяем, свое ли ЛПУ редактировать собираемся
			if (params.action == 'edit' && person_card_row.data.Is_OurLpu == 'false' && !isSuperAdmin()) {
				//Ext.Msg.alert('Ошибка', 'Редактирование карты прикрепления, введенной в другом ЛПУ, запрещено.', function() {
				//var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
				//if ( grid.getStore().getCount() > 0 )
				//{
				//grid.getSelectionModel().selectFirstRow();
				//grid.getView().focusRow(0);
				//}
				//});
				//return false;
				form_params.action = 'view';
			}

			if(getRegionNick() == 'ekb' && (!Ext.isEmpty(getGlobalOptions().allow_edit_attach_date) && getGlobalOptions().allow_edit_attach_date == 1) && isUserGroup('CardEditUser'))
			{
				var prev_beg_date = null;
				var prev_end_date = null;
				if(person_card_grid.getStore().getCount() > 0){ //https://redmine.swan.perm.ru/issues/84790
					var pcard_count = person_card_grid.getStore().getCount();
					if(params.action == 'add')
						person_card_grid.getSelectionModel().selectLastRow();
					else
					{
						if(pcard_count == 1)
							person_card_grid.getSelectionModel().selectRow(pcard_count-1);
						else
							person_card_grid.getSelectionModel().selectRow(pcard_count-2);
					}
					var last_record = person_card_grid.getSelectionModel().getSelected();
					if(params.action == 'add' || (params.action == 'edit' && pcard_count > 1))
					{
						if(!Ext.isEmpty(last_record.get('PersonCard_begDate')))
							prev_beg_date = last_record.get('PersonCard_begDate');
						if(!Ext.isEmpty(last_record.get('PersonCard_endDate')))
							prev_end_date = last_record.get('PersonCard_endDate');
					}
				}
			}
			// при добавлении определяем, прикреплялся ли человек сегодня с этим типом прикрепления
            var othercardexists = 1;
			if (params.action == 'add') {
				var options = getGlobalOptions();
				var date = options['date'];
                var personcard_record = person_card_grid.getSelectionModel().getSelected();
                if(getRegionNick()!= 'ekb' || (Ext.isEmpty(getGlobalOptions().allow_edit_attach_date) || getGlobalOptions().allow_edit_attach_date != 1) || !isUserGroup('CardEditUser'))
                {
	                var prev_beg_date = null;
					var prev_end_date = null;
					if(person_card_grid.getStore().getCount() > 0){ //https://redmine.swan.perm.ru/issues/84790
						person_card_grid.getSelectionModel().selectLastRow();
						var last_record = person_card_grid.getSelectionModel().getSelected();
						if(!Ext.isEmpty(last_record.get('PersonCard_begDate')))
							prev_beg_date = last_record.get('PersonCard_begDate');
						if(!Ext.isEmpty(last_record.get('PersonCard_endDate')))
							prev_end_date = last_record.get('PersonCard_endDate');
					}
				}
                if(personcard_record && personcard_record.data.PersonCard_id > 0)
                    othercardexists = 1;
                else
                    othercardexists = 0;
				// так же проверяем наличие ДМС-прикрепления к другому ЛПУ для основного прикрепления
				if (regions_tab_panel.getActiveTab().id == 'common_region') {
					var any_row = person_card_grid.getStore().getAt(0);
					if (any_row && any_row.get('Person_HasDmsOtherLpu') == 'true') {
						Ext.Msg.alert('Ошибка', 'Человек имеет активное ДМС прикрепление. Изменение основного прикрепления невозможно.', function () {
							var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
							if (grid.getStore().getCount() > 0) {
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
						});
						return false;
					}
				}

				// так же проверяем наличие ДМС-прикрепления к другому ЛПУ для основного прикрепления
				if (regions_tab_panel.getActiveTab().id == 'dms_region') {
					var any_row = person_card_grid.getStore().getAt(0);
					if (any_row && any_row.get('PersonCard_IsDmsForCheck') == 'true') {
						Ext.Msg.alert('Ошибка', 'Человек уже имеет активное ДМС прикрепление.', function () {
							var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
							if (grid.getStore().getCount() > 0) {
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
						});
						return false;
					}
				}

				if (person_card_grid.getStore().getCount() > 0 && !['service_region'].in_array(regions_tab_panel.getActiveTab().id)) {
					var index = person_card_grid.getStore().findBy(function (record, id) {
						if (record.data.PersonCard_endDate == '' && Ext.util.Format.date(record.data.PersonCard_begDate, 'd.m.Y') == date)
							return true;
						else
							return false;
					});
					var last_row = person_card_grid.getStore().getAt(index);
					if (last_row) {
						Ext.Msg.alert('Ошибка', 'Новое прикрепление пациента можно добавлять не чаще одного раза в день. Если пациент прикреплен к Вашему ЛПУ, то прикрепление может быть удалено или изменен участок только в течение даты прикрепления.', function () {
							var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
							if (grid.getStore().getCount() > 0) {
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
						});
						return false;
					}
				}
				else //Отдельно для Служебного http://redmine.swan.perm.ru/issues/23842
					if(getGlobalOptions().region.nick == 'ufa'){
						{
							var index = person_card_grid.getStore().findBy(function(record,id){
								if(record.data.PersonCard_endDate == '' && record.data.Lpu_id == getGlobalOptions().lpu_id)
									return true;
								else
									return false;
							});
							var service_row = person_card_grid.getStore().getAt(index);
							if(service_row){
								Ext.Msg.alert('Ошибка', 'Пациент уже имеет активное служебное прикрепление в данной МО.', function () {
									var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
									if (grid.getStore().getCount() > 0) {
										grid.getSelectionModel().selectFirstRow();
										grid.getView().focusRow(0);
									}
								});
								return false;
							}
						}
					}

				if (person_card_grid.getStore().getCount() > 0) {
					var index = person_card_grid.getStore().findBy(function(record,id){
						if (record.get('CardCloseCause_SysNick') == 'die') {
							return true;
						} else {
							return false;
						}
					});
					var death_row = person_card_grid.getStore().getAt(index);
					if (death_row) {
						Ext.Msg.alert('Ошибка', 'Добавление нового прикрепления невозможно. Установлена причина закрытия «Смерть ЗЛ».', function () {
							var grid = Ext.getCmp('PCVAW_PersonCardHistoryGrid').ViewGridPanel;
							if (grid.getStore().getCount() > 0) {
								grid.getSelectionModel().selectFirstRow();
								grid.getView().focusRow(0);
							}
						});
						return false;
					}
				}
			}
            form_params.otherCardExists = othercardexists;
			form_params.prev_beg_date = prev_beg_date;
			form_params.prev_end_date = prev_end_date;
			form_params.lastAttachIsNotInOurLpu = false;
			form_params.lastAttach_IsAttachCondit = false; //https://redmine.swan.perm.ru/issues/29930
			form_params.oldLpu_id = null;
			// определяем, прикреплена ли текущая открытая карта к другому ЛПУ
			if (person_card_grid.getStore().getCount() > 0) {
				var cnt = person_card_grid.getStore().getCount();
				form_params.oldLpu_id = person_card_grid.getStore().getAt(--cnt).get('Lpu_id');
				
				var index = person_card_grid.getStore().findBy(function (record, id) {
					if (record.data.PersonCard_endDate == '' && record.data.Is_OurLpu == 'false')
						return true;
					else
						return false;
				});
				var last_row = person_card_grid.getStore().getAt(index);
				if (last_row) {
					form_params.lastAttachIsNotInOurLpu = true;
					if(last_row.data.PersonCard_IsAttachCondit == 'true'){
						form_params.lastAttach_IsAttachCondit = true; //https://redmine.swan.perm.ru/issues/29930
					}
				}
			}
			// передаем текущий тип прикрепления
			form_params.attachType = regions_tab_panel.getActiveTab().id;
			if(params.action=='add' && Ext.getCmp('PCVAW_regions_tab_panel').getActiveTab().id != 'dms_region'){
				var loadMask = new Ext.LoadMask(Ext.get('PersonCardViewAllWindow'), {msg: "Проверка наличия амбулаторных карт..."});
				loadMask.show();

				Ext.Ajax.request(
				{
					url: '/?c=PersonAmbulatCard&m=checkPersonAmbulatCard',
					params: {Person_id:person_row.data.Person_id},
					callback: function(options, success, response) 
					{
						loadMask.hide();

						if (success)
						{
							var result = Ext.util.JSON.decode(response.responseText);
							if ( typeof result != 'object' ) {
								sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к сервер (проверка наличия амбулаторных карт).');
								return false;
							}
							else if ( !Ext.isEmpty(result[0].Error_Msg) ) {
								//sw.swMsg.alert('Ошибка', result.Error_Msg);
								sw.swMsg.alert('Предупреждение', 'Номер АК совпадает с существующим в базе', function(){
									getWnd('swPersonCardEditWindow').show(form_params);
								});
								//return false;
							}
							else if ( result.length > 0 && !Ext.isEmpty(result[0].PersonAmbulatCard_id) && !Ext.isEmpty(result[0].PersonCard_Code) ) {
								form_params.PersonAmbulatCard_id=result[0].PersonAmbulatCard_id;
								form_params.PersonCard_Code=result[0].PersonCard_Code;
								if(result[0].newPersonAmbulatCard_id){
									form_params.newPersonAmbulatCard_id=result[0].newPersonAmbulatCard_id;
								}
								if(result[0].PersonAmbulatCard_Count > 1)
								{
									sw.swMsg.alert('Предупреждение', 'Внимание! У данного пациента несколько амбулаторных карт.', function(){
										getWnd('swPersonCardEditWindow').show(form_params);
									});
								}
								else
								{
									getWnd('swPersonCardEditWindow').show(form_params);
								}
							} else if (getRegionNick().inlist(['ufa','pskov','hakasiya','kaluga'])) {
								getWnd('swPersonCardEditWindow').show(form_params);
							}
						}
					}
				});
			}else{
				// собственно отображаем форму редактирования
				getWnd('swPersonCardEditWindow').show(form_params);
			}
			/*
			 if ( form_params.action == 'edit_card_code_only' && Ext.util.Format.date(person_card_row.data.PersonCard_begDate, 'd.m.Y') != date )
			 {
			 Ext.Msg.alert('Внимание', 'Смена участка не доступна в день, отличный от даты прикрепления. Для смены участка добавьте новую карту.', function() {
			 return true;
			 });
			 }
			 */
		}
	},
	plain: true,
	resizable: true,
	exportIdentificationData: function() {
		var win = this;
		var form = this.findById('PersonCardViewAllFilterForm');
		var params = form.getForm().getValues();

		win.getLoadMask('Выгрузка запроса...').show();
		Ext.Ajax.request(
		{
			url: '/?c=Person&m=exportPersonCardForIdentification',
			params: params,
			callback: function(options, success, response) 
			{
				win.getLoadMask().hide();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.filename) {
						sw.swMsg.alert('Выгрузка запроса', "Выгрузка завершена успешно: <a href='" + result.filename + "' target='blank'>сохранить файл</a>");
					}
				}
			}
		});
	},
	importIdentificationData: function() {
		getWnd('swImportIdentificationDataWindow').show();
	},
	show: function () {
		sw.Promed.swPersonCardViewAllWindow.superclass.show.apply(this, arguments);
		var win = this;
		
		var regions_tab_panel = Ext.getCmp('PCVAW_regions_tab_panel');
		if (getGlobalOptions().lpu_is_dms)
			regions_tab_panel.unhideTabStripItem('dms_region');
		else
			regions_tab_panel.hideTabStripItem('dms_region');

		win.showPersonCardAdd = 1;
		if(getGlobalOptions().region.nick == 'khak'){
			if(arguments[0].showPersonCardAdd==2)
			{
				arguments[0].Person_BirthDay = Ext.util.Format.date(arguments[0].Person_BirthDay, 'd.m.Y')
				win.showPersonCardAdd = arguments[0].showPersonCardAdd;
				win.Person_SurName = arguments[0].Person_SurName;
				win.Person_FirName = arguments[0].Person_FirName;
				win.Person_SecName = arguments[0].Person_SecName;
				win.Person_BirthDay = arguments[0].Person_BirthDay + ' - ' + arguments[0].Person_BirthDay;
				win.Person_id = arguments[0].Person_id;
				win.PersonEvn_id = arguments[0].PersonEvn_id;
				win.Server_id = arguments[0].Server_id;
				win.setIsAttachCondit = arguments[0].setIsAttachCondit;
			}
		}
		this.restore();
		this.center();
		this.maximize();

        this.allowEditLpuRegion = 0;
		this.isSearched = false;
		this.searchInProgressFlag = false;
		this.buttons[0].enable();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		}
		Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionHidden('action_add', this.viewOnly);
		Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionHidden('action_edit', this.viewOnly);
		Ext.getCmp('PCVAW_PersonCardHistoryGrid').setActionHidden('action_delete', this.viewOnly);
		Ext.getCmp('PCVAW_PersonSearchGrid').setActionHidden('action_add', this.viewOnly);
		Ext.getCmp('PCVAW_PersonSearchGrid').setActionHidden('action_edit', this.viewOnly);
        if((getRegionNick() == 'astra' || getRegionNick() == 'perm') && this.viewOnly == false)
        {
            if(!this.findById('PCVAW_PersonCardHistoryGrid').getAction('action_add_lpuregion'))
            {
                this.findById('PCVAW_PersonCardHistoryGrid').addActions({
                    name: 'action_add_lpuregion',
                    text: 'Добавить участок',
                    handler: function()
                    {
                        win.allowEditLpuRegion = 1;
                        Ext.getCmp('PersonCardViewAllWindow').openPersonCardEditWindow({action: 'edit'});
                    }
                });
            }
        }

		if (getRegionNick() == 'ekb') {
			if(!this.PersonCardGridPanel.getAction('action_identification'))
			{
				this.PersonCardGridPanel.addActions({
					name: 'action_identification',
					text: 'Идентификация',
					menu: [{
						name: 'action_identification_export',
						text: 'Выгрузить запрос',
						handler: function()
						{
							win.exportIdentificationData();
						}
					}, {
						name: 'action_identification_import',
						text: 'Загрузить ответ',
						handler: function()
						{
							win.importIdentificationData();
						}
					}]
				});
			}
		}
		if (getRegionNick() == 'kz') { // Получение данных с портала РПН
			if(!this.PersonCardGridPanel.getAction('action_getpersonrpn')) {
				this.PersonCardGridPanel.addActions({
					name: 'action_getpersonrpn',
					text: 'Получить данные с портала РПН',
					handler: function() {
						var record = this.PersonCardGridPanel.getGrid().getSelectionModel().getSelected();
						var params = {
							Person_id: record.get('Person_id'), 
							Person_SurName: record.get('Person_SurName'),
							Person_FirName: record.get('Person_FirName'),
							Person_SecName: record.get('Person_SecName'),
							Person_BirthDay: Ext.util.Format.date(record.get('PersonBirthDay'), 'd.m.Y'),
							callback: function() {
								this.findById('PCVAW_PersonCardHistoryGrid').loadData();
							}.createDelegate(this)
						};
						sw.Promed.serviceKZRPN.getPersonCardList(this, params);
					}.createDelegate(this)
				});
			}
		}
		var PersonCardHistoryGrid = this.findById('PCVAW_PersonCardHistoryGrid');
		PersonCardHistoryGrid.addActions({name:'action_openac', id: 'id_action_openac', handler: function() {this.openPersonAmbulatCard();}.createDelegate(this),hidden:true,disabled:true, text:'Амбулаторная карта', tooltip: 'Амбулаторная карта'});
		var grid = this.findById('PCVAW_PersonSearchGrid').getGrid();
		grid.getStore().removeAll();

		var form = this.findById('PersonCardViewAllFilterForm');

		this.doResetAll();

		var tabPanel = this.findById('PersonCardViewAllFilterTabPanel');
		tabPanel.setActiveTab('PCVAFTP_SecondTab');
		tabPanel.setActiveTab('PCVAFTP_FirstTab');
		form.getForm().findField('Person_SurName').focus(true, 200);

		/*form.getForm().findField('LpuRegion_id').clearValue();
		form.getForm().findField('LpuRegion_id').getStore().removeAll();
		form.getForm().findField('LpuRegion_id').getStore().load({
			params: {
				'add_without_region_line': true,
                'Lpu_id': 0
			}
		});*/
        form.getForm().findField('LpuRegionType_id').getStore().filterBy(
            function(record)
            {
                //if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick() == 'perm')
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza']))
                    return false;
                else
                    return true;
            }
        );
		//if(getRegionNick() == 'perm')
		if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb', 'ufa','penza']))
        {
            form.getForm().findField('LpuRegionType_id').setFieldLabel('Тип основного участка');
            form.getForm().findField('LpuRegion_id').setFieldLabel('Основной участок');
        }
        else {
            form.getForm().findField('LpuRegionType_id').setFieldLabel('Тип участка');
            form.getForm().findField('LpuRegion_id').setFieldLabel('Участок');
        }
		sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
		sw.Applets.BarcodeScaner.startBarcodeScaner({ callback: this.getDataFromBarcode.createDelegate(this) });

		form.getForm().getEl().dom.action = "/?c=Person&m=printPersonCardGrid";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;

		loadComboOnce(form.getForm().findField('AttachLpu_id'), 'ЛПУ');
		form.getForm().findField('LpuRegion_id').clearValue();
	},
	title: WND_POL_PERSCARDVIEWALL,
	width: 900
});