/**
* swPersonDispOrpProfSearchWindow - окно поиска в регистре диспанцеризации несовершеннолетних
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
* prefix: pdoprosw
*/

sw.Promed.swPersonDispOrpProfSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
	id: 'PersonDispOrpProfSearchWindow',
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
	onPersonDispOrpSaved: function(data)
	{
		var grid = this.GridPanel.getGrid();
		
		if ( !data || !data.personDispOrpData ) {
			return false;
		}
		// Обновить запись в grid
		var record = grid.getStore().getById(data.personDispOrpData.PersonDispOrp_id);
		if ( record ) {
			var grid_fields = new Array();
			var i = 0;
			grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});
			for ( i = 0; i < grid_fields.length; i++ ) {
				record.set(grid_fields[i], data.personDispOrpData[grid_fields[i]]);
			}
			record.commit();
		}
		else {
			if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonDispOrp_id') ) {
				grid.getStore().removeAll();
			}
			grid.getStore().loadData({'data': [ data.personDispOrpData ]}, true);
		}
	},
	keys:
	[{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDispOrpProfSearchWindow").addPersonDispOrp();},
		stopEvent: true
	}, 
	{
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonDispOrpProfFilterTabPanel").setActiveTab(Ext.getCmp("PersonDispOrpProfFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, 
	{
		alt: true,
		fn: function(inp, e) 
		{
			var frm = Ext.getCmp('PersonDispOrpProfSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					frm.hide();
					break;
				case Ext.EventObject.C:
					frm.doResetAll();
					break;
				case Ext.EventObject.R:
					frm.doEvnPLDOAdd();
					break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.R ],
		stopEvent: true
	}],
	addPersonDo: function(person_data, cancel_check_other_lpu) 
	{
		frm = this;
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispOrpProfSearchWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var params = 
		{
			Person_id: person_data.Person_id,
			PersonEvn_id: person_data.PersonEvn_id,
			Server_id: person_data.Server_id,
			PersonDispOrp_Year: Ext.getCmp('pdoproswYearCombo').getValue()
		};
		if ( cancel_check_other_lpu )
			params.cancel_check_other_lpu = true;
		Ext.Ajax.request(
		{
			url: '?c=PersonDispOrp13&m=addPersonDispOrp',
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
									var years_combo = frm.findById('pdoproswYearCombo');
									years_combo.getStore().load({
										callback: function() 
										{
											var date = new Date();
											var year = date.getFullYear();
										    years_combo.setValue(year);
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
		var grid = frm.GridPanel.getGrid();
		
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
				getWnd('swPersonSearchWindow').hide();
				
				getWnd('swPersonDispOrpProfEditWindow').show({
					action: 'add',
					callback: function(data) {
						frm.onPersonDispOrpSaved(data);
					},
					formParams: {
						PersonDispOrp_Year: Ext.getCmp('pdoproswYearCombo').getValue(),
						Person_id: person_data.Person_id,
						Server_id: person_data.Server_id
					}
				});
			},
			searchMode: (getRegionNick()=='perm'?'attbefore3':(getRegionNick().inlist(['astra','buryatiya','kareliya','vologda'])?'all':'att'))
		});
	},
	doResetAll: function() 
	{
  	var form = this.findById('PersonDispOrpProfFilterForm');
		var year = Ext.getCmp('pdoproswYearCombo').getValue();
		form.getForm().reset();
		Ext.getCmp('pdoproswYearCombo').setValue(year);
		this.GridPanel.removeAll();
	},
	editPersonDispOrp: function(action) 
	{
		var frm = this;
 		var grid = frm.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record)
			return;
		if (frm.GridPanel.getCount()==0)
			return;
			
		if (record.get('ExistsDOPL') == true) {
			action = 'view';
		}
		
		var person_id = grid.getSelectionModel().getSelected().get('Person_id');
		var server_id = grid.getSelectionModel().getSelected().get('Server_id');
		var PersonDispOrp_id = grid.getSelectionModel().getSelected().get('PersonDispOrp_id');
		
		getWnd('swPersonDispOrpProfEditWindow').show(
		{
			action: action,
			formParams: {
				Person_id: person_id,
				Server_id: server_id,
				PersonDispOrp_id: PersonDispOrp_id
			},
			callback: function(data) {
				frm.onPersonDispOrpSaved(data);
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
		if ( record.get('EvnPLDispTeenInspection_id') > 0 )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					grid.getView().focusRow(0)
				},
				icon: Ext.Msg.WARNING,
				msg: lang['na_etogo_cheloveka_zavedena_karta_dispanserizatsii_nelzya_udalit_napravlenie'],
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
						callback: function(options, success, response)
						{
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_napravleniya_dd']);
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
						}
					});
				}
			}
		});
	},
	doEvnPLDOAdd: function() {
		var grid = this.GridPanel.getGrid();
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Person_id')) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibrano_ni_odnoy_zapisi']);
			return false;
		}
		
		if ( getWnd('swEvnPLDispTeenInspectionProfEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}
		
		var params = {
			action: 'edit',
			Person_id: record.get('Person_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			Server_id: record.get('Server_id'),
			PersonDispOrp_id: record.get('PersonDispOrp_id')
		};

		if ( !Ext.isEmpty(record.get('EvnPLDispTeenInspection_id')) ) {
			params.EvnPLDispTeenInspection_id = record.get('EvnPLDispTeenInspection_id');
		}

		if ( !Ext.isEmpty(record.get('AgeGroupDisp_id')) ) {
			params.AgeGroupDisp_id = record.get('AgeGroupDisp_id');
		}
		
		if ( !Ext.isEmpty(record.get('Org_id')) ) {
			params.Org_id = record.get('Org_id');
			params.OrgExist = 1;
		}
		
		params.callback = function(resp) {
			if (resp && resp.evnPLDispTeenInspectionData && !Ext.isEmpty(resp.evnPLDispTeenInspectionData.EvnPLDispTeenInspection_id)) {
				record.set('ExistsDOPL', true);
				record.set('EvnPLDispTeenInspection_id', resp.evnPLDispTeenInspectionData.EvnPLDispTeenInspection_id);
				record.commit();
			}
		};

		getWnd('swEvnPLDispTeenInspectionProfEditWindow').show(params);
	},
	doSearch: function(params) 
	{
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		var grid = this.GridPanel.getGrid();
		var form = this.findById('PersonDispOrpProfFilterForm');
		
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
		params.PersonDispOrp_Year = Ext.getCmp('pdoproswYearCombo').getValue();
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
		var form = this.findById('PersonDispOrpProfFilterForm');
		var params = {mode: 'streaminput'};
		params.reg_beg_date = this.begDate;
		params.reg_beg_time = this.begTime;
		params.start = 0;
		params.limit = 100;
		params.PersonDispOrp_Year = Ext.getCmp('pdoproswYearCombo').getValue();
		params.SearchFormType = 'PersonDispOrpProf';
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
					frm.findById('pdoproswStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	refreshPersonDispOrpViewGrid: function() 
	{
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
		if ( this.is_potok )
			this.doStreamInputSearch();
		var panel = this.findById('PersonDispOrpProfFilterTabPanel').getActiveTab();
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
		var grid = this.findById('PDOPRO_PersonDispOrpViewGrid').ViewGridPanel;
		this.setTitle(this.wintitle + lang['potochnyiy_vvod']);
		this.findById('PersonDispOrpProfFilterForm').hide();
		this.doLayout();
		this.is_potok = true;
		if ( this.findById('pdoproswYearCombo').getStore().getCount() > 0 )
			this.findById('pdoproswYearCombo').focus(true, 100);
	},
	setSearchMode: function()
	{
		this.buttons[0].enable();
		this.buttons[1].enable();
		var grid = this.findById('PDOPRO_PersonDispOrpViewGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(this.wintitle + ': ' + lang['poisk']);
		this.findById('PersonDispOrpProfFilterForm').show();
		this.doLayout();
		this.is_potok = false;
		if ( this.findById('pdoproswYearCombo').getStore().getCount() > 0 )
			this.findById('pdoproswYearCombo').focus(true, 100);
	},
	show: function() 
	{
		sw.Promed.swPersonDispOrpProfSearchWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		this.restore();
		this.center();
		this.maximize();
		this.GridPanel
		
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}

		this.GridPanel.setActionDisabled('action_add', this.viewOnly);
		this.GridPanel.setActionDisabled('action_edit', this.viewOnly);
		this.GridPanel.setActionDisabled('action_delete', this.viewOnly);
		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();

		this.wintitle = lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih'];
		
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
		var form = this.findById('PersonDispOrpProfFilterForm');
		this.doResetAll();
		
		var years_combo = this.findById('pdoproswYearCombo');
		years_combo.getStore().baseParams.CategoryChildType_SysNick = 'orpprof';

		years_combo.getStore().load({
			callback: function() 
			{
				var date = new Date();
				var year = date.getFullYear();
				years_combo.setValue(year);
				years_combo.focus(true, 300);
			}
		});

		var tabPanel = this.findById('PersonDispOrpProfFilterTabPanel');
		tabPanel.setActiveTab('pdoproswFirstTab');
		
		// для печати списка
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
		
	},
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('pdoproswSearchButton');
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
						key: 'pdoproswyear_combo',
						autoLoad: false,
						fields:
						[
							{name:'PersonDispOrp_Year',type: 'int'},
							{name:'count', type: 'int'}
						],
						url: C_PDO_LOAD_YEARS
					}),
					id: 'pdoproswYearCombo',
					hiddenName: 'PersonDispOrp_Year',
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{PersonDispOrp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
					region: 'north',
					valueField: 'PersonDispOrp_Year',
					displayField: 'PersonDispOrp_Year',
					editable: false,
					tabIndex: TABINDEX_PDOPROSF + 55,
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								var panel = inp.ownerCt.ownerCt.findById('PersonDispOrpProfFilterTabPanel').getActiveTab();
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
					id: 'pdoproswStream_begDateTime',
					width: 165,
					xtype: 'textfield'
				}]
			},
			getBaseSearchFiltersFrame(
			{
				id: 'PersonDispOrpProfFilterForm',
				ownerWindow: this,
				searchFormType: 'PersonDispOrpProf',
				tabIndexBase: TABINDEX_PDOPROSF,
				tabPanelId: 'PersonDispOrpProfFilterTabPanel',
				tabGridId: 'PDOPRO_PersonDispOrpViewGrid',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 250,
					id: 'EPLDOP_FirstTab',
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							var form = this.findById('PersonDispOrpProfFilterForm');
							form.getForm().findField('OrgExist').focus(400, true);									
						}.createDelegate(this)
					},								
					title: lang['6_napravlenie'],
					items: [{
						fieldLabel: lang['obuchayuschiysya'],
						hiddenName: 'OrgExist',
						xtype:'swyesnocombo'
					}, {
						comboSubject: 'AgeGroupDisp',
						fieldLabel: lang['vozrastnaya_gruppa'],
						loadParams: {params: {where: "where DispType_id = 4"}},
						hiddenName: 'AgeGroupDisp_id',
						lastQuery: '',
						width: 300,
						xtype: 'swcommonsprcombo'
					}]
				}]
			})]
		});
		
		this.GridPanel = new sw.Promed.ViewFrame(
		{
			dataUrl: C_SEARCH,
			id: 'PDOPRO_PersonDispOrpViewGrid',
			object: 'PersonDispOrp',
			autoLoadData: false,
			region: 'center',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			//editformclassname: swLpuUnitEditForm,
			focusOn: {name:'pdoproswSearchButton', type:'other'},
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
				{name: 'AgeGroupDisp_Name',  type: 'string', header: lang['vozrastnaya_gruppa']},
				{name: 'AgeGroupDisp_id',  type: 'int', hidden: true, hideable: false},
				{name: 'Org_id',  type: 'int', hidden: true, hideable: false},
				{name: 'PersonDispOrp_begDate',  type: 'date', header: lang['data_napravleniya'], width: 110},
				{name: 'OrgExist',  type: 'checkbox', header: lang['obuchayuschiysya'], width: 110},
				{name: 'ExistsDOPL',  type: 'checkbox', header: lang['karta_osmotra']},
				{name: 'EvnPLDispTeenInspection_id',  type: 'int', hidden: true, hideable: false}
			],
			actions:
			[
				{name: 'action_add', handler: function() {Ext.getCmp('PersonDispOrpProfSearchWindow').addPersonDispOrp(); }},
				{name: 'action_edit', handler: function() {Ext.getCmp('PersonDispOrpProfSearchWindow').editPersonDispOrp('edit'); }},
				{name: 'action_view', handler: function() {Ext.getCmp('PersonDispOrpProfSearchWindow').editPersonDispOrp('view'); }},
				{name: 'action_delete', handler: function() {Ext.getCmp('PersonDispOrpProfSearchWindow').deletePersonDispOrp(); }},
				{name: 'action_refresh', handler: function() { this.GridPanel.getGrid().getStore().reload(); }.createDelegate(this) },
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
				id: 'pdoproswSearchButton',
				tabIndex: TABINDEX_PDOPROSF + 50,
				text: BTN_FRMSEARCH
			}, 
			{
				handler: function() 
				{
					this.ownerCt.doResetAll();
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PDOPROSF + 51,
				text: lang['cbros']
			}, 
			{
				disabled: false,
				handler: function() 
				{
					this.ownerCt.doEvnPLDOAdd();
				},
				//iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PDOPROSF + 52,
				text: lang['karta_osmotra']
			},
			'-',
			HelpButton(this, TABINDEX_PDOPROSF + 53),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				onTabAction: function() {
					Ext.getCmp('pdoproswYearCombo').focus(true, 200);
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_PDOPROSF + 54,
				text: BTN_FRMCANCEL
			}
		],
		items: 
		[
			this.EditPanel,
			this.GridPanel
			]
		});
		sw.Promed.swPersonDispOrpProfSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});