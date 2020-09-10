/**
* swPersonDispOrpSearchWindow - окно поиска в регистре диспанцеризации детей-сирот.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
* tabIndex: 9000
* prefix: pdosw
*/

sw.Promed.swPersonDispOrpSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['registr_detey-sirot_poisk'],
	id: 'PersonDispOrpSearchWindow',
	height: 550,
	width: 900,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	buttonAlign: 'left',
	resizable: true,
	keys:
	[{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDispOrpSearchWindow").addPersonDispOrp();},
		stopEvent: true
	}, 
	{
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonDispOrpFilterTabPanel").setActiveTab(Ext.getCmp("PersonDispOrpFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, 
	{
		alt: true,
		fn: function(inp, e) 
		{
			var frm = Ext.getCmp('PersonDispOrpSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					frm.hide();
					break;
				case Ext.EventObject.C:
					frm.doResetAll();
					break;
				case Ext.EventObject.N:
					frm.doEvnPLDOAdd();
					break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.N ],
		stopEvent: true
	}],
	addPersonDo: function(person_data, cancel_check_other_lpu) 
	{
		frm = this;
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispOrpSearchWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var params = 
		{
			Person_id: person_data.Person_id,
			PersonEvn_id: person_data.PersonEvn_id,
			Server_id: person_data.Server_id,
			PersonDispOrp_Year: Ext.getCmp('pdoswYearCombo').getValue()
		};
		if ( cancel_check_other_lpu )
			params.cancel_check_other_lpu = true;
		Ext.Ajax.request(
		{
			url: '?c=PersonDispOrp&m=addPersonDispOrp',
			params: params,
			callback: function(options, success, response) 
			{
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
											function () 
											{
												// TODO: Здесь надо будет переделать использование getWnd
												getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
											}
										);
									break;
									// не заполнены поля
									case '667':
										Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function() 
											{
												getWnd('swPersonEditWindow').show(
												{
													action: 'edit',
													Person_id: person_data.Person_id,
													Server_id: person_data.Server_id,
													callback: function(callback_data) 
													{
														person_data.Person_id = callback_data.Person_id;
														person_data.Server_id = callback_data.Server_id;
														person_data.PersonEvn_id = callback_data.PersonEvn_id;
														frm.addPersonDo(person_data);
													},
													onClose: function() 
													{
														// TODO: Здесь надо будет переделать использование getWnd
														getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 100);
													}
												});
											}
										);
									break;
									// человек в регистре другого ЛПУ
									case '668':
										sw.swMsg.show(
										{
											buttons: Ext.Msg.YESNO,
											fn: function ( buttonId ) 
											{
												if ( buttonId == 'yes' )
												{
													frm.addPersonDo(person_data, true);
												}
												else
												{
													// TODO: Здесь надо будет переделать использование getWnd
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
											function () 
											{
												// TODO: Здесь надо будет переделать использование getWnd
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
								lang['chelovek_uspeshno_dobavlen_v_registr_detey-sirot'],
								function () 
								{
									var years_combo = frm.findById('pdoswYearCombo');
									years_combo.getStore().load({
										url: '/?c=PersonDispOrp&m=GetPersonDispOrpYearsCombo',
										callback: function() 
										{
										    years_combo.setValue(2012);
										}
									});
									getWnd('swPersonSearchWindow').hide();
									frm.refreshPersonDispOrpViewGrid();
								}
							);
						}
					}
				}
			}
		});
	},
	addPersonDispOrp: function() 
	{
		var frm = this;
		if (getWnd('swPersonSearchWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		getWnd('swPersonSearchWindow').show(
		{
			onClose: function() 
			{
				frm.refreshPersonDispOrpViewGrid();
			},
			onSelect: function(person_data) 
			{
				frm.addPersonDo(person_data);
			},
			searchMode: 'all'
		});
	},
	doExportToDbf: function() 
	{
		var loadMask = new Ext.LoadMask(Ext.getCmp('PersonDispOrpSearchWindow').getEl(), { msg: "Подождите, идет формирование архива..." });
		loadMask.show();
		var params = 
		{
			PersonDispOrp_Year: Ext.getCmp('pdoswYearCombo').getValue()
		};
		Ext.Ajax.request(
		{
			params: params,
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if ( success ) 
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success ) 
					{
						sw.swMsg.alert('Экспорт регистра детей-сирот', '<a target="_blank" href="' + response_obj.url + '">Скачать архив с регистром детей-сирот</a>');
						var id_salt = Math.random();
						var win_id = 'exprepwd' + Math.floor(id_salt*10000);
						var win = window.open('', win_id);
						win.document.write(response_obj.html);
						win.document.close();
					}
					else 
					{
						var r = '';
						if (response_obj.Error_Msg)
							r = response_obj.Error_Msg;
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.ERROR,
							msg: lang['pri_formirovanii_arhiva_proizoshli_oshibki_n_r']+r,
							title: lang['eksport_registra_detey-sirot']
						});
					}
				}
				else 
				{
					sw.swMsg.alert(lang['eksport_registra_detey-sirot'], lang['pri_formirovanii_arhiva_proizoshli_oshibki']);
				}
			},
			url: '/?c=PersonDispOrp&m=exportPersonDispOrpToDbf'
		});
	},
	doResetAll: function() 
	{
		var form = this.findById('PersonDispOrpFilterForm');
		var year = Ext.getCmp('pdoswYearCombo').getValue();
		form.getForm().reset();
		Ext.getCmp('pdoswYearCombo').setValue(year);
		this.GridPanel.removeAll();
	},
	editPersonDispOrp: function() 
	{
		var frm = this;
 		var grid = frm.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record)
			return;
		if (frm.GridPanel.getCount()==0)
			return;
   	var person_id = grid.getSelectionModel().getSelected().get('Person_id');
   	var server_id = grid.getSelectionModel().getSelected().get('Server_id');
   	getWnd('swPersonEditWindow').show(
		{
			action: 'edit',
			Person_id: person_id,
			Server_id: server_id,
			callback: function(callback_data) 
			{
				grid.getView().focusRow(0);
			},
			onClose: function() 
			{
				grid.getView().focusRow(0);
			}
		});
	},
	deletePersonDispOrp: function() 
	{
		var frm = this;
  	var grid = frm.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record)
			return;
		if ( !record.get('PersonDispOrp_id') || record.get('PersonDispOrp_id') == '' )
			return;
		if ( record.get('EvnPLDispOrp_id') > 0 )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					grid.getView().focusRow(0)
				},
				icon: Ext.Msg.WARNING,
				msg: lang['na_etogo_cheloveka_zaveden_talon_ego_nelzya_udalit_iz_registra'],
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
						url: '?c=PersonDispOrp13&m=deletePersonDispOrp',
						params: {PersonDispOrp_id: record.data.PersonDispOrp_id},
						callback: function() 
						{
							if ( !frm.is_potok )
								frm.doSearch();
							else
								frm.doStreamInputSearch();
						}
					});
				}
			}
		});
	},
	doEvnPLDOAdd: function() 
	{
		var frm = this;
  	var grid = frm.GridPanel.getGrid();
		var record = frm.GridPanel.getGrid().getSelectionModel().getSelected();
		if ( !record || !record.get('Person_id') || record.get('Person_id') == '' )
			Ext.Msg.alert(lang['soobschenie'], lang['ne_vyibrano_ni_odnoy_zapisi']);
		
		if (getWnd('swEvnPLDispOrpEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}
		
		var action = 'add';
		if ( record.get('EvnPLDispOrp_id') > 0 )
			action = 'view';

		var params = 
		{
			Person_id: record.get('Person_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			Server_id: record.get('Server_id')
		};
		params.action = action;
		if ( record.get('EvnPLDispOrp_id') > 0 )
			params.EvnPLDispOrp_id = record.get('EvnPLDispOrp_id');
		
		getWnd('swEvnPLDispOrpEditWindow').show(params);
	},
	doSearch: function(params) 
	{
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var grid = this.GridPanel.getGrid();
		var form = this.findById('PersonDispOrpFilterForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
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
		params.PersonDispOrp_Year = Ext.getCmp('pdoswYearCombo').getValue();
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load(
		{
			params: params
		});
	},
	doStreamInputSearch: function() 
	{
		var grid = this.GridPanel.getGrid();
		var form = this.findById('PersonDispOrpFilterForm');
		var params = {mode: 'streaminput'};
		params.reg_beg_date = this.begDate;
		params.reg_beg_time = this.begTime;
		params.start = 0;
		params.limit = 100;
		params.PersonDispOrp_Year = Ext.getCmp('pdoswYearCombo').getValue();
		params.SearchFormType = 'PersonDispOrpOld';
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
	},
	getBegDateTime: function() 
	{
		var frm = this;
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					frm.begDate = response_obj.begDate;
					frm.begTime = response_obj.begTime;
					if ( frm.is_potok )
						frm.doStreamInputSearch();
					frm.findById('pdoswStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	refreshPersonDispOrpViewGrid: function() 
	{
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
		if ( this.is_potok )
			this.doStreamInputSearch();
		var panel = this.findById('PersonDispOrpFilterTabPanel').getActiveTab();
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
		var grid = this.findById('PersonDispOrpViewGrid').ViewGridPanel;
		grid.getColumnModel().setHidden(17, true);
		this.setTitle(lang['registr_detey-sirot_potochnyiy_vvod']);
		this.findById('PersonDispOrpFilterForm').hide();
		this.doLayout();
		this.is_potok = true;
		if ( this.findById('pdoswYearCombo').getStore().getCount() > 0 )
			this.findById('pdoswYearCombo').focus(true, 100);
	},
	setSearchMode: function()
	{
		this.buttons[0].enable();
		this.buttons[1].enable();
		var grid = this.findById('PersonDispOrpViewGrid').ViewGridPanel;
		grid.getColumnModel().setHidden(17, false);
		grid.getStore().removeAll();
		this.setTitle(lang['registr_detey-sirot_poisk']);
		this.findById('PersonDispOrpFilterForm').show();
		this.doLayout();
		this.is_potok = false;
		if ( this.findById('pdoswYearCombo').getStore().getCount() > 0 )
			this.findById('pdoswYearCombo').focus(true, 100);
	},
	viewPersonDispOrp: function() 
	{
		var frm = this;
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record)
			return;
		if ((!grid.getSelectionModel().getSelected()) || (grid.getStore().getCount()==0))
			return;
		var person_id = grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = grid.getSelectionModel().getSelected().data.Server_id;
		getWnd('swPersonEditWindow').show(
		{
			readOnly: true,
			Person_id: person_id,
			Server_id: server_id,
			callback: function(callback_data) 
			{
				grid.getView().focusRow(0);
			},
			onClose: function() 
			{
				grid.getView().focusRow(0);
			}
		});
	},
	show: function() 
	{
		sw.Promed.swPersonDispOrpSearchWindow.superclass.show.apply(this, arguments);
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
		else
			this.setSearchMode();
		this.getBegDateTime();
		var form = this.findById('PersonDispOrpFilterForm');
		this.doResetAll();

		var years_combo = this.findById('pdoswYearCombo');
		if ( years_combo.getStore().getCount() == 0 )
			years_combo.getStore().load(
			{
				url: '/?c=PersonDispOrp&m=GetPersonDispOrpYearsCombo',
				callback: function() 
				{
					years_combo.setValue(2012);
					years_combo.focus(true, 300);
				}
			});

		var tabPanel = this.findById('PersonDispOrpFilterTabPanel');
		tabPanel.setActiveTab('pdoswFirstTab');
		
		// для печати списка
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
		
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('pdoswSearchButton');
	},
	initComponent: function() 
	{
	
		this.EditPanel = new Ext.Panel(
		{
			autoHeight: true,
			region: 'north',
			layout: 'form',
			border: false,
			labelWidth: 120,
			items: 
			[{
				bodyStyle:'padding:3px; padding-left:5px;',
				layout: 'form',
				xtype: 'panel',
				border: false,
				//frame: true,
				labelWidth: 125,
				items:
				[{
					xtype: 'swbaselocalcombo',
					mode: 'local',
					fieldLabel: lang['god'],
					store: new Ext.data.JsonStore(
					{
						key: 'pdoswyear_combo',
						autoLoad: false,
						fields:
						[
							{name:'PersonDispOrp_Year',type: 'int'},
							{name:'count', type: 'int'}
						],
						url: '/?c=PersonDispOrp&m=GetPersonDispOrpYearsCombo'
					}),
					id: 'pdoswYearCombo',
					hiddenName: 'PersonDispOrp_Year',
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{PersonDispOrp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
					region: 'north',
					valueField: 'PersonDispOrp_Year',
					displayField: 'PersonDispOrp_Year',
					editable: false,
					tabIndex: TABINDEX_PDOSF + 55,
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								var panel = inp.ownerCt.ownerCt.findById('PersonDispOrpFilterTabPanel').getActiveTab();
								var els=panel.findByType('textfield', false);
								if (els==undefined)
									els=panel.findByType('combo', false);
								var el=els[0];
								// почему-то на вкладке "Прикрепление" встает на 2е поле, принудительно на первое ставим ;)
								if (el!=undefined && el.focus)
								{
									if ( el['hiddenName'] && el['hiddenName'] == 'LpuAttachType_id' )
									{
										panel.items.items[0].focus(true, 500);
									}
									else
										el.focus(true, 200);
								}
							}
						}
					}
				}, {
					disabled: true,
					fieldLabel: lang['data_nachala_vvoda'],
					id: 'pdoswStream_begDateTime',
					width: 165,
					xtype: 'textfield'
				}]
			},
			getBaseSearchFiltersFrame(
			{
				id: 'PersonDispOrpFilterForm',
				ownerWindow: this,
				searchFormType: 'PersonDispOrpOld',
				tabIndexBase: TABINDEX_PDOSF,
				tabPanelId: 'PersonDispOrpFilterTabPanel'
			})]
		});
		
		this.GridPanel = new sw.Promed.ViewFrame(
		{
			dataUrl: C_SEARCH,
			id: 'PersonDispOrpViewGrid',
			object: 'PersonDispOrp',
			autoLoadData: false,
			region: 'center',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			//editformclassname: swLpuUnitEditForm,
			focusOn: {name:'pdoswSearchButton', type:'field'},
			stringfields:
			[
				{name: 'PersonDispOrp_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{id: 'autoexpand', name: 'Person_Surname',  type: 'string', header: lang['familiya'], width: 120},
				{name: 'Person_Firname',  type: 'string', header: lang['imya'], width: 120},
				{name: 'Person_Secname',  type: 'string', header: lang['otchestvo'], width: 120},
				{name: 'Person_Birthday',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
				{name: 'Sex_Name',  type: 'string', header: lang['pol']},
				{name: 'Polis_Ser',  type: 'string', header: lang['seriya_polisa'], hidden: true, hideable: true},
				{name: 'Polis_Num',  type: 'string', header: lang['№_polisa'], hidden: true, hideable: true},
				{name: 'Person_KLAreaStat_Name',  type: 'string', header: lang['territoriya_mesta_registratsii'], hidden: true, hideable: true},
				{name: 'PersonOrg_Okved',  type: 'string', header: lang['okved_organizatsii'], hidden: true, hideable: true},
				{name: 'PersonOrg_KLAreaStat_Name',  type: 'string', header: lang['territoriya_organizatsii'], hidden: true, hideable: true},
				{name: 'PersonOrg_OGRN',  type: 'string', header: lang['ogrn_organizatsii'], hidden: true, hideable: true},
				{name: 'UAddress_Address',  type: 'string', header: lang['adres_propiski'], hidden: true, hideable: true},
				{name: 'OnDispInOtherLpu',  type: 'string', header: lang['v_registre_dr-go_lpu'], width: 110},
				{name: 'ExistsDOPL',  type: 'checkbox', header: lang['talon'], hidden: true, hideable: false},
				{name: 'EvnPLDispOrp_id',  type: 'int', hidden: true, hideable: false}
			],
			actions:
			[
				{name: 'action_add', handler: function() {/*Ext.getCmp('PersonDispOrpSearchWindow').addPersonDispOrp();*/ }, disabled: true, hiden: true },
				{name: 'action_edit', handler: function() {/*Ext.getCmp('PersonDispOrpSearchWindow').editPersonDispOrp();*/ }, disabled: true, hiden: true },
				{name: 'action_view', handler: function() {Ext.getCmp('PersonDispOrpSearchWindow').viewPersonDispOrp(); }},
				{name: 'action_delete', handler: function() {/*Ext.getCmp('PersonDispOrpSearchWindow').deletePersonDispOrp();*/ }, disabled: true, hiden: true },
				{name: 'action_refresh', disabled: true},
				{name: 'action_print'}
			],
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this)
		});
	
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'pdoswSearchButton',
				tabIndex: TABINDEX_PDOSF + 50,
				text: BTN_FRMSEARCH
			}, 
			{
				handler: function() 
				{
					this.ownerCt.doResetAll();
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PDOSF + 51,
				text: lang['cbros']
			}, 
			{
				disabled: false,
				handler: function() 
				{
					this.ownerCt.doEvnPLDOAdd();
				},
				//iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PDOSF + 52,
				text: lang['talon']
			}, 
			{
				disabled: !isSuperAdmin(),
				hidden: !isSuperAdmin(),
				handler: function() 
				{
					this.ownerCt.doExportToDbf();
				},
				tabIndex: TABINDEX_PDOSF + 53,
				text: lang['vyigruzka_v_dbf']
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_PDOSF + 54,
				text: BTN_FRMCANCEL
			}
		],
		items: 
		[
			this.EditPanel,
			this.GridPanel
			]
		});
		sw.Promed.swPersonDispOrpSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});