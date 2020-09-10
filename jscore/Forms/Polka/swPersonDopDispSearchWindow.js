/**
* swPersonDopDispSearchWindow - окно поиска в регистре доп. диспанцеризации.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      22.06.2009
* tabIndex: TABINDEX_DDREG
*/

sw.Promed.swPersonDopDispSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	addPersonDo: function(person_data, cancel_check_other_lpu, cancel_check_job_data) {
		return false;

		current_window = this;
		var loadMask = new Ext.LoadMask(Ext.get('PersonDopDispSearchWindow'), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {
			Person_id: person_data.Person_id,
			PersonEvn_id: person_data.PersonEvn_id,
			Server_id: person_data.Server_id,
			PersonDopDisp_Year: Ext.getCmp('PDDSW_yearCombo').getValue()
		};
		if ( cancel_check_other_lpu )
			params.cancel_check_other_lpu = true;
		if ( cancel_check_job_data )
			params.cancel_check_job_data = true;
		Ext.Ajax.request({
			url: '?c=PersonDopDisp&m=addPersonDopDisp',
			params: params,
			callback: function(options, success, response) {
				loadMask.hide();
				if (success)
				{
	          		if ( response.responseText.length > 0 )
					{
	                    var resp_obj = Ext.util.JSON.decode(response.responseText);
						if (resp_obj.success == false)
						{
							if ( resp_obj.Error_Code && resp_obj.Error_Code && resp_obj.Error_Code != 100500 )
							{
								switch ( resp_obj.Error_Code )
								{
									// человек уже есть в своем регистре
									case '666':
										Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function () {
												// TODO: getWnd
												getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
											}
										);
									break;
									// не заполнены поля
									case '667':
										// для перми полный запрет
										Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function() {
												getWnd('swPersonEditWindow').show({
													action: 'edit',
													Person_id: person_data.Person_id,
													Server_id: person_data.Server_id,
													callback: function(callback_data) {
														person_data.Person_id = callback_data.Person_id;
														person_data.Server_id = callback_data.Server_id;
														person_data.PersonEvn_id = callback_data.PersonEvn_id;
														current_window.addPersonDo(person_data);
													},
													onClose: function() {
														// TODO: getWnd
														getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
													}
												});
											}
										);
									break;
									// для Уфы, если ошибка только в месте работы, то просто предупреждение
									case '661':
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function ( buttonId ) {
												if ( buttonId == 'yes' )
												{
													current_window.addPersonDo(person_data, cancel_check_other_lpu, true);
												}
												else
												{
													getWnd('swPersonEditWindow').show({
														action: 'edit',
														Person_id: person_data.Person_id,
														Server_id: person_data.Server_id,
														callback: function(callback_data) {
															person_data.Person_id = callback_data.Person_id;
															person_data.Server_id = callback_data.Server_id;
															person_data.PersonEvn_id = callback_data.PersonEvn_id;
															current_window.addPersonDo(person_data);
														},
														onClose: function() {
															// TODO: getWnd
															getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
														}
													});
												}
											},
											msg: resp_obj.Error_Msg + lang['vyi_deystvitelno_hotite_dobavit_cheloveka_v_registr_s_etimi_dannyimi'],
											title: lang['poverka_mesta_rabotyi_cheloveka']
										});										
									break;
									// человек в регистре другого ЛПУ
									case '668':
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function ( buttonId ) {
												if ( buttonId == 'yes' )
												{
													current_window.addPersonDo(person_data, true);
												}
												else
												{
													// TODO: getWnd
														getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
												}
											},
											msg: resp_obj.Error_Msg,
											title: lang['proverka_cheloveka_v_registre_v_drugom_lpu']
										});
									break;
									default:
										Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function () {
												// TODO: getWnd
												getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
											}
										);
								}
							}							
						}
						else
						{
							Ext.Msg.alert(
								lang['dobavleno'],
								lang['chelovek_uspeshno_dobavlen_v_registr_dopolnitelnoy_dispanserizatsii'],
								function () {
									var years_combo = current_window.findById('PDDSW_yearCombo');
									years_combo.getStore().load({
										url: '/?c=PersonDopDisp&m=GetPersonDopDispYearsCombo',
										callback: function() {
											var date = new Date();
											var year = date.getFullYear();
										    years_combo.setValue(year);
										}
									});
									getWnd('swPersonSearchWindow').hide();
									current_window.refreshPersonDopDispViewGrid();
								}
							);
						}
					}
				}
			}
		});
	},
	addPersonDopDisp: function() {
		return false;

		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

/*		if (getWnd('swPersonDopDispEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_patsienta_uje_otkryito']);
			return false;
		}
*/
			getWnd('swPersonSearchWindow').show({
            onClose: function() {
        		current_window.refreshPersonDopDispViewGrid();
            },
    		onSelect: function(person_data) {
				current_window.addPersonDo(person_data);
            },
            searchMode: 'all'
        });
	},
	buttonAlign: 'left',
	doExportToDbf: function() {
		var loadMask = new Ext.LoadMask(Ext.getCmp('PersonDopDispSearchWindow').getEl(), {msg: "Подождите, идет формирование архива..."});
		loadMask.show();
		var params = {
			PersonDopDisp_Year: Ext.getCmp('PDDSW_yearCombo').getValue()
		};
		Ext.Ajax.request({
			params: params,
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success ) {
						sw.swMsg.alert('Экспорт регистра по ДД', '<a target="_blank" href="' + response_obj.url + '">Скачать архив с регистром по ДД</a>');

						if ( isSuperAdmin() ) {
							var id_salt = Math.random();
							var win_id = 'exprepwd' + Math.floor(id_salt*10000);
							var win = window.open('', win_id);
							win.document.write(response_obj.html);
							win.document.close();
						}
					}
					else {
						sw.swMsg.alert(lang['eksport_registra_po_dd'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
					}
				}
				else {
					sw.swMsg.alert(lang['eksport_registra_po_dd'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
				}
			},
			url: '/?c=PersonDopDisp&m=exportPersonDopDispToDbf'
		});
	},
	doResetAll: function() {
  		var form = this.findById('PersonDopDispFilterForm');
		var year = Ext.getCmp('PDDSW_yearCombo').getValue();
		form.getForm().reset();
		Ext.getCmp('PDDSW_yearCombo').setValue(year);
		var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		grid.getStore().removeAll();

		var base_form = form.getForm();
		var el;
		el = base_form.findField('PrivilegeStateType_id');// Льгота - Актуальность льготы - 1. Действующие льготы
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PersonCardStateType_id');// Прикрепление - Актуальность прикр-я - 1. Актуальные прикрепления
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('AddressStateType_id');// Адрес - Тип адреса - 1. Адрес регистрации
		if(typeof(el) !== 'undefined') el.setValue(1);
	},	
	editPersonDopDisp: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonDopDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
			return;
   		var person_id = grid.getSelectionModel().getSelected().data.Person_id;
   		var server_id = grid.getSelectionModel().getSelected().data.Server_id;
   		getWnd('swPersonEditWindow').show({
			action: 'edit',
       		Person_id: person_id,
       		Server_id: server_id,
			callback: function(callback_data) {
				grid.getView().focusRow(0);
			},
			onClose: function() {
				grid.getView().focusRow(0);
			}
		});
	},
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    monitorResize: true,
    draggable: true,
	deletePersonDopDisp: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonDopDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if ( !current_row.get('PersonDopDisp_id') || current_row.get('PersonDopDisp_id') == '' )
			return;		
		if ( current_row.get('EvnPLDispDop_id') > 0 )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					grid.getView().focusRow(0)
				},
				icon: Ext.Msg.WARNING,
				msg: lang['na_etogo_cheloveka_zaveden_talon_dd_ego_nelzya_udalit_iz_registra'],
				title: lang['oshibka']
			});
			return;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '?c=PersonDopDisp&m=deletePersonDopDisp',
						params: {PersonDopDisp_id: current_row.data.PersonDopDisp_id},
						callback: function() {
							if ( !current_window.is_potok )
								current_window.doSearch();
							else
								current_window.doStreamInputSearch();
						}
					});
				}
			}
		});
	},
	doEvnPLDDAdd: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonDopDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if ( !current_row || !current_row.get('Person_id') || current_row.get('Person_id') == '' )
			return;
			
		if (getWnd('swEvnPLDispDopEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}		
			
		var action = 'add';
		if ( current_row.get('EvnPLDispDop_id') > 0 )
			action = 'edit';

		var params = {
			Person_id: current_row.get('Person_id'),
			PersonEvn_id: current_row.get('PersonEvn_id'),
			Server_id: current_row.get('Server_id')
		};
		params.action = action;
		if ( current_row.get('EvnPLDispDop_id') > 0 ) {
			params.EvnPLDispDop_id = current_row.get('EvnPLDispDop_id');
			getWnd('swEvnPLDispDopEditWindow').show(params);
		} else {
			// сначала проверим, можем ли мы добавлять талон на этого человека
			Ext.Ajax.request({
				url: '/?c=EvnPLDispDop&m=checkIfEvnPLDispDopExists',
				callback: function(opt, success, response) {
					if (success && response.responseText != '')
					{
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.isEvnPLDispDopExists == false )
						{
							getWnd('swEvnPLDispDopEditWindow').show(params);
							return;
						}
						else
						{
							sw.swMsg.alert("Ошибка", "На этого человека уже был заведен талон в этом году.",
								function () {
									getWnd('swPersonSearchWindow').hide();
								}
							);
							return;
						}
					}
				},
				params: { Person_id: params.Person_id }
			});
		}
	},
	doSearch: function(params) {
		if ( params && params['soc_card_id'] ) {
			var soc_card_id = params['soc_card_id'];
		}

    	var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		var form = this.findById('PersonDopDispFilterForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}

		form.getForm().findField('PersonDopDisp_Year').setValue(this.findById('PDDSW_yearCombo').getValue());

		var params = form.getForm().getValues();		
		var arr = form.find('disabled', true);

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
				params[arr[i].hiddenName] = arr[i].getValue();
		}
		
		if ( soc_card_id )
		{
			var params = {
				soc_card_id: soc_card_id,
				SearchFormType: params.SearchFormType
			};
		}
		
		params.start = 0;
		params.limit = 100;

		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params
		});
	},
	doStreamInputSearch: function() {
    	var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		var form = this.findById('PersonDopDispFilterForm');
		var params = {dop_disp_mode: 'streaminput'};
		params.dop_disp_reg_beg_date = this.begDate;
		params.dop_disp_reg_beg_time = this.begTime;
		params.start = 0;
		params.limit = 100;
		params.PersonDopDisp_Year = Ext.getCmp('PDDSW_yearCombo').getValue();
		params.SearchFormType = 'PersonDopDisp';
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params
		});
	},
    getBegDateTime: function() {
    	var current_window = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);

					current_window.begDate = response_obj.begDate;
					current_window.begTime = response_obj.begTime;
					if ( current_window.is_potok )
	                    current_window.doStreamInputSearch();
			        current_window.findById('PDDSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'PersonDopDispSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('PDDSW_SearchButton');
	},
	initComponent: function() {
		Ext.apply(this, {
		buttons: [{
			handler: function() {
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			id: 'PDDSW_SearchButton',
			tabIndex: TABINDEX_DDREG + 50,
			text: BTN_FRMSEARCH
		}, {
			handler: function() {
				this.ownerCt.doResetAll();
			},
			iconCls: 'resetsearch16',
			tabIndex: TABINDEX_DDREG + 51,
			text: lang['cbros']
		}, {
			disabled: false,
			handler: function() {
				this.ownerCt.doEvnPLDDAdd();
			},
			//iconCls: 'resetsearch16',
			tabIndex: TABINDEX_DDREG + 52,
			text: lang['talon']
		}, {
			disabled: false,
			hidden: false,
			handler: function() {
				this.doExportToDbf();
			}.createDelegate(this),
			//iconCls: 'resetsearch16',
			tabIndex: TABINDEX_DDREG + 53,
			text: lang['vyigruzka_v_dbf']
		}, /*{
			handler: function() {
				var base_form = this.findById('PersonDopDispFilterForm').getForm();
				var params = new Object();

				base_form.findField('PersonDopDisp_Year').setValue(this.findById('PDDSW_yearCombo').getValue());

				base_form.submit();
			}.createDelegate(this),
			iconCls: 'print16',
			tabIndex: TABINDEX_DDREG + 54,
			text: lang['pechat_vsego_spiska']
		},*/
		'-',
		HelpButton(this, -1),
		{
			handler: function() {
				this.ownerCt.hide();
			},
			iconCls: 'cancel16',
			tabIndex: TABINDEX_DDREG + 55,
			text: BTN_FRMCANCEL
		}
		],
			items: [
				new Ext.Panel({
					autoHeight: true,
					region: 'north',
					bodyStyle:'padding:3px',
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'swbaselocalcombo',
						mode: 'local',
						fieldLabel: lang['god'],
						store: new Ext.data.JsonStore(
						{
							key: 'PDDSW_year_combo',
							autoLoad: false,
							fields:
							[
								{name:'PersonDopDisp_Year',type: 'int'},
								{name:'count', type: 'int'}
							],
							url: '/?c=PersonDopDisp&m=GetPersonDopDispYearsCombo'
						}),
						id: 'PDDSW_yearCombo',
						hiddenName: 'PersonDopDisp_Year',
						tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{PersonDopDisp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
						region: 'north',
						valueField: 'PersonDopDisp_Year',
						displayField: 'PersonDopDisp_Year',
						editable: false,
						tabIndex: TABINDEX_DDREG + 55,
						enableKeyEvents: true,
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									var panel = inp.ownerCt.ownerCt.findById('PersonDopDispFilterTabPanel').getActiveTab();
									var els=panel.findByType('textfield', false);
									if (els==undefined)
										els=panel.findByType('combo', false);
									var el=els[0];
									if (el!=undefined && el.focus)
										el.focus(true, 200);
								}
							}
						}
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'PDDSW_Stream_begDateTime',
						width: 165,
						xtype: 'textfield'
					},	getBaseSearchFiltersFrame({
						hiddenFields: [{
							name: 'PersonDopDisp_Year',
							value: 0,
							xtype: 'hidden'
						}],
						id: 'PersonDopDispFilterForm',
						ownerWindow: this,
                                                autoHeight: true,
						searchFormType: 'PersonDopDisp',
						tabIndexBase: TABINDEX_DDREG,
						tabPanelId: 'PersonDopDispFilterTabPanel',
                                                listeners: {
                                                    'collapse': function(p) {
                                                        p.ownerWindow.doLayout();
                                                    },
                                                    'expand': function(p) {
                                                        p.ownerWindow.doLayout();
                                                    }
                                                }
					})]
				}),
                new sw.Promed.ViewFrame(
				{
					actions:
					[
						{name: 'action_add', handler: function() {Ext.getCmp('PersonDopDispSearchWindow').addPersonDopDisp();}, disabled: true, hidden: true},
						{name: 'action_edit', handler: function() {Ext.getCmp('PersonDopDispSearchWindow').editPersonDopDisp();}},
						{name: 'action_view', handler: function() {Ext.getCmp('PersonDopDispSearchWindow').viewPersonDopDisp();}},
						{name: 'action_delete', handler: function() {Ext.getCmp('PersonDopDispSearchWindow').deletePersonDopDisp();}},
						{name: 'action_refresh', disabled: false},
						{name: 'action_print',
							menuConfig: {
								printObjectListFull: { handler: function() {
									var base_form = this.findById('PersonDopDispFilterForm').getForm();
									base_form.findField('PersonDopDisp_Year').setValue(this.findById('PDDSW_yearCombo').getValue());
									base_form.submit();
								}.createDelegate(this) }
							}
						}
					],
//					autoExpandColumn: 'autoexpand',
//                                        autoHeight: true,
					autoLoadData: false,
					dataUrl: C_SEARCH,
					id: 'PersonDopDispViewGrid',
					focusOn: {name:'PDDSW_SearchButton', type:'field'},
					object: 'PersonDopDisp',
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					totalProperty: 'totalCount',
					//editformclassname: swLpuUnitEditForm,
					stringfields:
					[
						{name: 'PersonDopDisp_id', type: 'int', header: 'ID', key: true},
						{name: 'Person_id', type: 'int', hidden: true},
						{name: 'Server_id', type: 'int', hidden: true},
						{name: 'PersonEvn_id', type: 'int', hidden: true},
						{name: 'Person_Surname',  type: 'string', header: lang['familiya']},
						{name: 'Person_Firname',  type: 'string', header: lang['imya']},
						{name: 'Person_Secname',  type: 'string', header: lang['otchestvo']},
						{name: 'Person_Birthday',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
						{name: 'Sex_Name',  type: 'string', header: lang['pol']},
						{name: 'Polis_Ser',  type: 'string', header: lang['seriya_polisa'], hidden: true, hideable: true},
						{name: 'Polis_Num',  type: 'string', header: lang['№_polisa'], hidden: true, hideable: true},
						{name: 'Person_KLAreaStat_Name',  type: 'string', header: lang['territoriya_mesta_registratsii'], hidden: true, hideable: true},
						{name: 'PersonOrg_Okved',  type: 'string', header: lang['okved_organizatsii'], hidden: true, hideable: true},
						{name: 'PersonOrg_KLAreaStat_Name',  type: 'string', header: lang['territoriya_organizatsii'], hidden: true, hideable: true},
						{name: 'PersonOrg_OGRN',  type: 'string', header: lang['ogrn_organizatsii'], hidden: true, hideable: true},
						{name: 'UAddress_Address',  type: 'string', header: lang['adres_propiski'], hidden: true, hideable: true},
						{name: 'OnDispInOtherLpu',  type: 'string', header: lang['v_registre_dr-go_lpu']},
						{name: 'ExistsDDPL',  type: 'checkbox', header: lang['est_talon_dd'], hidden: true, hideable: false},
						{name: 'EvnPLDispDop_id',  type: 'int', hidden: true, hideable: false}
					],
					toolbar: true,
					onBeforeLoadData: function() {
						this.getButtonSearch().disable();
					}.createDelegate(this),
					onLoadData: function() {
						if (!this.is_potok) {
							this.getButtonSearch().enable();
						}
					}.createDelegate(this)
				})
			]
		});
		
		sw.Promed.swPersonDopDispSearchWindow.superclass.initComponent.apply(this, arguments);
	},
    keys: [{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDopDispSearchWindow").addPersonDopDisp();},
		stopEvent: true
	}, {
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonDopDispFilterTabPanel").setActiveTab(Ext.getCmp("PersonDopDispFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, {
    	alt: true,
        fn: function(inp, e) {
        	var current_window = Ext.getCmp('PersonDopDispSearchWindow');
        	switch (e.getKey())
        	{
        		case Ext.EventObject.J:
        			current_window.hide();
        		break;
				case Ext.EventObject.C:
        			current_window.doResetAll();
        		break;
				case Ext.EventObject.N:
        			current_window.doEvnPLDDAdd();
        		break;
        	}
        },
        key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.N ],
        stopEvent: true
    }],
    layout: 'border',
    maximizable: true,
    minHeight: 550,
    minWidth: 900,
    modal: false,
    plain: true,
	  resizable: true,
	refreshPersonDopDispViewGrid: function() {
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
		if ( this.is_potok )
			this.doStreamInputSearch();
		var panel = this.findById('PersonDopDispFilterTabPanel').getActiveTab();
		var els=panel.findByType('textfield', false);
		if (els==undefined)
			els=panel.findByType('combo', false);
		var el=els[0];
		if (el!=undefined && el.focus)
			el.focus(true, 200);
	},
	setStreamInputMode: function()
	{
		this.buttons[0].disable();
		this.buttons[1].disable();
		var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		grid.getColumnModel().setHidden(17, true);
		this.setTitle(MM_POL_PERSDDSTREAMINPUT);
        this.findById('PersonDopDispFilterForm').hide();
		this.doLayout();
		this.is_potok = true;
		if ( this.findById('PDDSW_yearCombo').getStore().getCount() > 0 )
			this.findById('PDDSW_yearCombo').focus(true, 100);

	},
	setSearchMode: function()
	{
		this.buttons[0].enable();
		this.buttons[1].enable();
		var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		grid.getColumnModel().setHidden(17, false);
		grid.getStore().removeAll();
		this.setTitle(MM_POL_PERSDDSEARCH);
        this.findById('PersonDopDispFilterForm').show();
		this.doLayout();
		this.is_potok = false;
		if ( this.findById('PDDSW_yearCombo').getStore().getCount() > 0 )
			this.findById('PDDSW_yearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swPersonDopDispSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		if ( arguments[0] != undefined && arguments[0].mode != undefined)
		{
			if ( arguments[0].mode == 'stream' )
				this.setStreamInputMode();
			else
				this.setSearchMode();
		}
		else{
			this.setSearchMode();
		}
        this.getBegDateTime();
		
		var grid = this.findById('PersonDopDispViewGrid').ViewGridPanel;
		grid.getStore().removeAll();

		var form = this.findById('PersonDopDispFilterForm');

		this.doResetAll();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		//this.findById('PersonDopDispViewGrid').setActionDisabled('action_add', this.viewOnly);
		this.findById('PersonDopDispViewGrid').setActionDisabled('action_edit', this.viewOnly);
		this.findById('PersonDopDispViewGrid').setActionDisabled('action_delete', this.viewOnly);
		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();
		var years_combo = this.findById('PDDSW_yearCombo');
		if ( years_combo.getStore().getCount() == 0 )
			years_combo.getStore().load({
				url: '/?c=PersonDopDisp&m=GetPersonDopDispYearsCombo',
				callback: function() {
					var date = new Date();
					var year = date.getFullYear();
					var max_year = 0;

					var index = years_combo.getStore().findBy(function(rec) {
						return (rec.get('PersonDopDisp_Year') == year);
					});

					if ( index >= 0 ) {
						max_year = year;
					}
					else {
						years_combo.getStore().each(function(rec) {
							if ( rec.get('PersonDopDisp_Year') > max_year ) {
								max_year = rec.get('PersonDopDisp_Year');
							}
						});
					}

					if ( !Ext.isEmpty(max_year) ) {
						years_combo.setValue(max_year);
					}

					years_combo.focus(true, 500);
				}
			});

		var tabPanel = this.findById('PersonDopDispFilterTabPanel');
		tabPanel.setActiveTab('PDDSW_FirstTab');
		
		// для печати списка
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
	},
    title: WND_POL_PERSDDSEARCH,
	viewPersonDopDisp: function() {
		var current_window = this;
  		var grid = current_window.findById('PersonDopDispViewGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if ((!grid.getSelectionModel().getSelected()) || (grid.getStore().getCount()==0))
			return;
   		var person_id = grid.getSelectionModel().getSelected().data.Person_id;
   		var server_id = grid.getSelectionModel().getSelected().data.Server_id;
   		getWnd('swPersonEditWindow').show({
			readOnly: true,
       		Person_id: person_id,
       		Server_id: server_id,
			callback: function(callback_data) {
				grid.getView().focusRow(0);
			},
			onClose: function() {
				grid.getView().focusRow(0);
			}
		});
	},
    width: 900
});