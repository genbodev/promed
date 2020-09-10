/**
* swEvnPLDispSomeAdultSearchWindow - окно поиска талона амбулаторного пациента.
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
* @comment      Префикс для id компонентов EPLDSASW (EvnPLDispSomeAdultSearchWindow)
*
*
* Использует: окно редактирования талона амбулаторного пациента (swEvnPLDispSomeAdultEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*             окно поиска человека (swPersonSearchWindow)
*/

sw.Promed.swEvnPLDispSomeAdultSearchWindow = Ext.extend(sw.Promed.BaseForm, {
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
		}

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
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPL: function() {
		var grid = this.getActiveViewFrame().getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPL_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_id = record.get('EvnPL_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_tap']);
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
						},
						params: {
							Evn_id: evn_pl_id
						},
						url: '/?c=Evn&m=deleteEvn'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon'],
			title: lang['vopros']
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

		if ( form.getForm().findField('PrivilegeStateType_id') != null ) {
			form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
		}

		form.findById('EPLDSASW_SearchFilterTabbar').setActiveTab(0);
		form.findById('EPLDSASW_SearchFilterTabbar').getActiveTab().fireEvent('activate', form.findById('EPLDSASW_SearchFilterTabbar').getActiveTab());

		current_window.getActiveViewFrame().ViewGridPanel.getStore().removeAll();
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
		
		var base_form = this.findById('EvnPLSearchFilterForm').getForm();
		var form = this.findById('EvnPLSearchFilterForm');
		
		if ( form.isEmpty() && !(params && params['soc_card_id']) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
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
				title: lang['preduprejdenie']
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

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
		var params = getAllFormFieldValues(form);
		
		if ( params.PersonCardStateType_id == null ) {
			params.PersonCardStateType_id = 1;
		}

		if ( params.PrivilegeStateType_id == null ) {
			params.PrivilegeStateType_id = 1;
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
							sw.swMsg.alert(lang['eksport_talonov'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['eksport_talonov'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
				}
			},
			params: params,
			url: '/?c=Search&m=exportSearchResultsToDbf'
		});
	},
	getRecordsCount: function() {
		var form = this.findById('EvnPLSearchFilterForm');

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
	id: 'EvnPLDispSomeAdultSearchWindow',
	Wizard: {EvnPLSearchFrame:null, EvnVizitPLSearchFrame: null, Panel: null},
	getActiveViewFrame: function () 
	{
		return this.Wizard[this.Wizard.Panel.layout.activeItem.id];
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDSASW_SearchButton');
	},
	initComponent: function() {
		this.Wizard.EvnPLSearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').deleteEvnPL(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLDSASW_EvnPLSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').printEvnPL(); } }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLDSASW_SearchButton',
				type: 'button'
			},
			id: 'EPLDSASW_EvnPLSearchGrid',
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
				{ name: 'EvnPL_NumCard', type: 'string', header: lang['№_talona'], width: 70 },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 70 },
				{ name: 'EvnPL_VizitCount', type: 'int', header: lang['posescheniy'], width: 80 },
				{ name: 'EvnPL_IsFinish', type: 'string', header: lang['zakonch'], width: 70 },
				{ name: 'Diag_Name', type: 'string', header: lang['osnovnoy_diagnoz'], id: 'autoexpand' },
				{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 160 },
				{ name: 'EvnPL_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'], width: 90 },
				{ name: 'EvnPL_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'], width: 90 },
				{ name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30 }
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
				if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
					if ( record.get('EvnPL_id') ) {
						this.ViewActions.action_changeperson.setDisabled(false);
					}
					else {
						this.ViewActions.action_changeperson.setDisabled(true);
					}
				}
			}
		});
		
		this.Wizard.EvnVizitPLSearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('add'); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').openEvnPLDispSomeAdultEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').deleteEvnPL(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EPLDSASW_EvnVizitPLSearchGrid').ViewGridPanel.getStore().reload(); } },
				{ name: 'action_print', handler: function() { Ext.getCmp('EvnPLDispSomeAdultSearchWindow').printEvnPL(); } }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLDSASW_SearchButton',
				type: 'button'
			},
			id: 'EPLDSASW_EvnVizitPLSearchGrid',
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
				{ name: 'EvnPL_NumCard', type: 'string', header: lang['№_talona'], width: 70 },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 70 },
				{ name: 'Diag_Name', type: 'string', header: lang['osnovnoy_diagnoz'], id: 'autoexpand' },
				
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 100 },
				
				{ name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 160 },
				{ name: 'UslugaComplex_Code', type: 'string', header: lang['kod_posescheniya'], width: 100, hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ? false : true },
				{ name: 'EvnVizitPL_setDate', type: 'date', format: 'd.m.Y', header: lang['data_posescheniya'], width: 90 },
				{ name: 'ServiceType_Name', type: 'string', header: lang['mesto_obslujivaniya'], width: 100 },
				{ name: 'VizitType_Name', type: 'string', header: lang['tsel_posescheniya'], width: 100 },
				{ name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 70 },
				
				{ name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30 }
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

		Ext.apply(this, {
			buttons: [
			{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EPLDSASW_SearchButton',
				tabIndex: TABINDEX_EPLDSASW + 109,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDSASW + 110,
				text: BTN_FRMRESET
			}, {
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
				tabIndex: TABINDEX_EPLDSASW + 111,
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDSASW + 112,
				text: BTN_FRMCOUNT
			}, {
				handler: function() {
					this.exportRecordsToDbf();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSASW + 112,
				text: lang['eksport_naydennyih_talonov_v_dbf']
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_EPLDSASW + 113),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EPLDSASW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EPLDSASW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSASW + 114,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnPLSearchFilterForm');
				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
				allowPersonPeriodicSelect: true,
				id: 'EvnPLSearchFilterForm',
				ownerWindow: this,
				searchFormType: 'EvnPL',
				ownerWindowWizardPanel: this.Wizard.Panel,
				tabIndexBase: TABINDEX_EPLDSASW,
				tabPanelId: 'EPLDSASW_SearchFilterTabbar',
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

					// tabIndexStart: TABINDEX_EPLDSASW + 68
					items: [{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: lang['diagnoz'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							fieldLabel: lang['kod_diagnoza_s'],
							hiddenName: 'Diag_Code_From',
							listWidth: 620,
							tabIndex: TABINDEX_EPLDSASW + 68,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, {
							fieldLabel: lang['po'],
							hiddenName: 'Diag_Code_To',
							listWidth: 620,
							tabIndex: TABINDEX_EPLDSASW + 69,
							valueField: 'Diag_Code',
							width: 590,
							xtype: 'swdiagcombo'
						}, /*{
							hiddenName: 'LpuSectionDiag_id',
							id: 'EPLDSASW_LpuSectionDiagCombo',
							lastQuery: '',
							linkedElements: [
								'EPLDSASW_MedStaffFactDiagCombo'
							],
							tabIndex: TABINDEX_EPLDSASW + 70,
							width: 620,
							xtype: 'swlpusectionglobalcombo'
						}, {
							hiddenName: 'MedStaffFactDiag_id',
							id: 'EPLDSASW_MedStaffFactDiagCombo',
							lastQuery: '',
							parentElementId: 'EPLDSASW_LpuSectionDiagCombo',
							tabIndex: TABINDEX_EPLDSASW + 71,
							width: 620,
							xtype: 'swmedstafffactglobalcombo'
						}, */{
							comboSubject: 'DeseaseType',
							fieldLabel: lang['harakter_zabolev'],
							hiddenName: 'DeseaseType_id',
							tabIndex: TABINDEX_EPLDSASW + 72,
							width: 420,
							xtype: 'swcommonsprcombo'
						}]
					}, 
					{
						autoHeight: true,
						labelWidth: 150,
						style: 'padding: 0px;',
						title: lang['uslugi'],
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
							loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}} : null),
							tabIndex: TABINDEX_EPLDSASW + 73,
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
									tabIndex: TABINDEX_EPLDSASW + 74,
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
									tabIndex: TABINDEX_EPLDSASW + 75,
									width: 250,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}]
						}, {
							border: false,
							hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])), // Открыто для Уфы
							layout: 'form',
							items: [{
								fieldLabel: lang['kod_posescheniya'],
								hiddenName: 'UslugaComplex_uid',
								listWidth: 590,
								showUslugaComplexEndDate: true,
								tabIndex: TABINDEX_EPLDSASW + 75,
								width: 590,
								xtype: 'swuslugacomplexnewcombo'
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
									isPolka: true
								});
								this.getFilterForm().getForm().findField('MedStaffFactViz_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
							}

							this.getFilterForm().getForm().findField('EvnPL_NumCard').focus(250, true);
						}.createDelegate(this)
					},
					title: '<u>7</u>. Посещение',

					// tabIndexStart: TABINDEX_EPLDSASW + 77
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
								tabIndex: TABINDEX_EPLDSASW + 77,
								width: 150,
								maskRe: /[^%]/,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 180,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_nachala_sluchaya'],
								name: 'EvnPL_setDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLDSASW + 77,
								width: 200,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							labelWidth: 160,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_okonchaniya_sluchaya'],
								name: 'EvnPL_disDate_Range',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: TABINDEX_EPLDSASW + 77,
								width: 200,
								xtype: 'daterangefield'
							}]
						},
							{
								border: false,
								layout: 'form',
								items:[{
									fieldLabel:lang['vid_posescheniya'],
									name: 'VizitClass',
									tabIndex: TABINDEX_EPLDSASW + 77,
									xtype: 'swvizitclasscombo'
								}]
							}]
					}, {
						comboSubject: 'PrehospTrauma',
						fieldLabel: lang['travma'],
						hiddenName: 'PrehospTrauma_id',
						tabIndex: TABINDEX_EPLDSASW + 78,
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						border: false,
						labelWidth: 130,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo', // TODO: Зачем справочник от swcommonsprcombo, а не от yesno
								fieldLabel: lang['protivopravnaya'],
								hiddenName: 'EvnPL_IsUnlaw',
								tabIndex: TABINDEX_EPLDSASW + 79,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							labelWidth: 170,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: lang['netransportabelnost'],
								hiddenName: 'EvnPL_IsUnport',
								tabIndex: TABINDEX_EPLDSASW + 80,
								width: 200,
								xtype: 'swyesnocombo'
							}]
						}]
					}, {
						hiddenName: 'LpuBuildingViz_id',
						fieldLabel: lang['podrazdelenie'],
						id: 'EPLDSASW_LpuBuildingVizCombo',
						lastQuery: '',
						linkedElements: [
							'EPLDSASW_LpuSectionVizCombo'
						],
						listWidth: 700,
						tabIndex: TABINDEX_EPLDSASW + 81,
						width: 450,
						xtype: 'swlpubuildingglobalcombo'
					}, {
						hiddenName: 'LpuSectionViz_id',
						id: 'EPLDSASW_LpuSectionVizCombo',
						lastQuery: '',
						linkedElements: [
							'EPLDSASW_MedStaffFactVizCombo',
							'EPLDSASW_MidMedStaffFactVizCombo'
						],
						parentElementId: 'EPLDSASW_LpuBuildingVizCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EPLDSASW + 82,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						hiddenName: 'MedStaffFactViz_id',
						id: 'EPLDSASW_MedStaffFactVizCombo',
						lastQuery: '',
						parentElementId: 'EPLDSASW_LpuSectionVizCombo',
						listWidth: 700,
						tabIndex: TABINDEX_EPLDSASW + 83,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						fieldLabel: lang['sredniy_m_pers'],
						hiddenName: 'MedStaffFactViz_sid',
						id: 'EPLDSASW_MidMedStaffFactVizCombo',
						lastQuery: '',
						listWidth: 700,
						parentElementId: 'EPLDSASW_LpuSectionVizCombo',
						tabIndex: TABINDEX_EPLDSASW + 84,
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
								comboSubject: 'ServiceType',
								hiddenName: 'ServiceType_id',
								fieldLabel: lang['mesto_obsluj-ya'],
								tabIndex: TABINDEX_EPLDSASW + 85,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASW + 86,
								width: 200,
								useCommonFilter: true,
								xtype: 'swpaytypecombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								listWidth: 400,
								tabIndex: TABINDEX_EPLDSASW + 87,
								width: 200,
								EvnClass_id: 11,
								xtype: 'swvizittypecombo'
							}, {
								fieldLabel: lang['data_posescheniya'],
								name: 'Vizit_Date_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EPLDSASW + 88,
								width: 200,
								xtype: 'daterangefield'
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

					// tabIndexStart: TABINDEX_EPLDSASW + 89
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'YesNo',
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
								tabIndex: TABINDEX_EPLDSASW + 89,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'DirectClass',
								fieldLabel: lang['kuda_napravlen'],
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
								tabIndex: TABINDEX_EPLDSASW + 90,
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								comboSubject: 'ResultClass',
								fieldLabel: ( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) ? lang['rezultat_obrascheniya'] : lang['rezultat_lecheniya'],
								hiddenName: 'ResultClass_id',
								tabIndex: TABINDEX_EPLDSASW + 91,
								width: 200,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'DirectType',
								fieldLabel: lang['napravlenie'],
								hiddenName: 'DirectType_id',
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASW + 92,
								width: 200,
								xtype: 'swcommonsprcombo'
							}]
						}]
					}, {
						disabled: true,
						fieldLabel: lang['otdelenie_lpu'],
						hiddenName: 'LpuSection_oid',
						lastQuery: '',
						tabIndex: TABINDEX_EPLDSASW + 93,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						disabled: true,
						displayField: 'Lpu_Nick',
						fieldLabel: lang['drugoe_lpu'],
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
						tabIndex: TABINDEX_EPLDSASW + 94,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						valueField: 'Lpu_id',
						width: 450,
						xtype: 'swbaselocalcombo'
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
									tabIndex: TABINDEX_EPLDSASW + 95,
									width: 200,
									xtype: 'daterangefield'
								}, {
									fieldLabel: 'Закрыт',
									name: 'EvnStick_endDate_Range',
									plugins: [
										new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
									],
									tabIndex: TABINDEX_EPLDSASW + 96,
									width: 200,
									xtype: 'daterangefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'StickType',
									fieldLabel: lang['tip_lista'],
									hiddenName: 'StickType_id',
									tabIndex: TABINDEX_EPLDSASW + 97,
									width: 200,
									xtype: 'swcommonsprcombo'
								}, {
									tabIndex: TABINDEX_EPLDSASW + 98,
									listWidth: 450,
									width: 200,
									xtype: 'swstickcausecombo'
								}]
							}]
						}]
					}]
				}]
			}),
			this.Wizard.Panel
			]
		});

		sw.Promed.swEvnPLDispSomeAdultSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispSomeAdultSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					current_window.openEvnPLDispSomeAdultEditWindow('add');
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
			var current_window = Ext.getCmp('EvnPLDispSomeAdultSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDSASW_SearchFilterTabbar');

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
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDispSomeAdultEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var current_window = this;

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPLDispSomeAdultEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_ambulatornogo_patsienta_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var grid = current_window.getActiveViewFrame().ViewGridPanel;

		params.action = action;
		// params.callback только при поиске талонов!!! т.к. при поиске посещений будет другой грид - с посещениями!
		if ('EvnPL' != current_window.getActiveViewFrame().object)
		{
			params.callback = Ext.emptyFn;
		}
		else
		{
			params.callback = function(data) {
				if ( !data || !data.EvnPLData ) {
					return false;
				}

				// Обновить запись в grid
				var record = grid.getStore().getById(data.EvnPLData.EvnPL_id);

				if ( record ) {
					record.set('Diag_Name', data.EvnPLData.Diag_Name);
					record.set('EvnPL_disDate', data.EvnPLData.EvnPL_disDate);
					record.set('EvnPL_id', data.EvnPLData.EvnPL_id);
					record.set('EvnPL_IsFinish', data.EvnPLData.EvnPL_IsFinish);
					record.set('EvnPL_NumCard', data.EvnPLData.EvnPL_NumCard);
					record.set('EvnPL_setDate', data.EvnPLData.EvnPL_setDate);
					record.set('EvnPL_VizitCount', data.EvnPLData.EvnPL_VizitCount);
					record.set('MedPersonal_Fio', data.EvnPLData.MedPersonal_Fio);
					record.set('Person_Birthday', data.EvnPLData.Person_Birthday);
					record.set('Person_Surname', data.EvnPLData.Person_Surname);
					record.set('Person_Firname', data.EvnPLData.Person_Firname);
					record.set('Person_Secname', data.EvnPLData.Person_Secname);
					record.set('Person_id', data.EvnPLData.Person_id);
					record.set('PersonEvn_id', data.EvnPLData.PersonEvn_id);
					record.set('Server_id', data.EvnPLData.Server_id);

					record.commit();
				}
				else {
					grid.getStore().loadData([ data.EvnPLData ], true);
				}

				grid.getStore().each(function(record) {
					if ( record.get('Person_id') == data.EvnPLData.Person_id && record.get('Server_id') == data.EvnPLData.Server_id ) {
						record.set('Person_Birthday', data.EvnPLData.Person_Birthday);
						record.set('Person_Surname', data.EvnPLData.Person_Surname);
						record.set('Person_Firname', data.EvnPLData.Person_Firname);
						record.set('Person_Secname', data.EvnPLData.Person_Secname);

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

					getWnd('swEvnPLDispSomeAdultEditWindow').show(params);
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

			if ( evn_pl_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.EvnPL_id = evn_pl_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_id =  person_id;
				params.Server_id = server_id;

				getWnd('swEvnPLDispSomeAdultEditWindow').show(params);
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
			if(getGlobalOptions().region.nick == 'penza'){ //https://redmine.swan.perm.ru/issues/63097
				printBirt({
					'Report_FileName': 'EvnPLPrint.rptdesign',
					'Report_Params': '&paramEvnPL=' + evn_pl_id,
					'Report_Format': 'pdf'
				});
			}
			else
				window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + evn_pl_id, '_blank');
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispSomeAdultSearchWindow.superclass.show.apply(this, arguments);
		var base_form = this.findById('EvnPLSearchFilterForm').getForm();

		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
			if ( !this.Wizard.EvnPLSearchFrame.getAction('action_changeperson') ) {
				this.Wizard.EvnPLSearchFrame.addActions({
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

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
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

		if ( getGlobalOptions().region ) {
			switch ( getGlobalOptions().region.nick ) {
				case 'ufa':
					base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
				break;
			}
		}
	},
	title: WND_POL_EPLDSASEARCH,
	width: 800
});