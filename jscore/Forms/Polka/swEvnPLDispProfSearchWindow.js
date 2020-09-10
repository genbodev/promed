/**
* swEvnPLDispProfSearchWindow - окно поиска профосмотра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @originalauthor	Ivan Petukhov aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version		20.06.2013
* @comment		Префикс для id компонентов EPLDPSW (EvnPLDispProfSearchWindow)
*
*
* Использует: окно редактирования профосмотра (swEvnPLDispProfEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispProfSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispProfSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispProfSearchWindow.js',
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	setEvnIsTransit: function() {
		if ( !lpuIsTransit() ) {
			return false;
		}

		var grid = this.findById('EPLDPSW_EvnPLDispProfSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispProf_id') || grid.getSelectionModel().getSelected().get('EvnPLDispProf_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLDispProf_id'),
			Evn_IsTransit: Evn_IsTransit
		}

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
						record.set('EvnPLDispProf_IsTransit', Evn_IsTransit);
						record.commit();
						this.findById('EPLDPSW_EvnPLDispProfSearchGrid').onRowSelect(null, null, record);
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
	addEvnPLDP: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispProfEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_profosmotra_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDPSW_YearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDPSW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				var filter_form = current_window.findById('EvnPLDispProfSearchFilterForm');
				
				if ( !filter_form.isEmpty() ) {
					current_window.refreshEvnPLDPList();
				}
			},
			onSelect: function(person_data) {
				getWnd('swEvnPLDispProfEditWindow').show({
					action: 'add',
					DispClass_id: 5,
					EvnPLDispProf_id: null,
					onHide: Ext.emptyFn,
					callback: function() {
						current_window.refreshEvnPLDPList();
					},
					Person_id: person_data.Person_id,
					Server_id: person_data.Server_id
				});
			},
			searchMode: 'all',
			Year: Year
		});
	},
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.findById('EPLDPSW_EvnPLDispProfSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispProf_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispProf_id');

		var params = {
			EvnPLDispProf_id: evn_pl_dd_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_talona_dd']);
					}
					else if (response_obj.Alert_Msg) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg + ' Продолжить?',
							title: lang['podtverjdenie'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									options.ignoreCheckRegistry = true;
									win.doDeleteEvnPLDD(options);
								}
							}
						});
					}
					else {
						grid.getStore().remove(record);

						if ( grid.getStore().getCount() == 0 ) {
							LoadEmptyRow(grid);
						}
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_dd_voznikli_oshibki']);
				}
			},
			params: params,
			url: '/?c=EvnPLDispProf&m=deleteEvnPLDispProf'
		});
	},
	deleteEvnPLDP: function() {
		var win = this;
		var grid = win.findById('EPLDPSW_EvnPLDispProfSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispProf_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispProf_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon_dd'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispProfSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').getGrid().getStore().removeAll();
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
		
		if ( this.isStream )
		{
			this.doStreamInputSearch();
			return true;
		}
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispProfSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispProf_grid = current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').ViewGridPanel;

		var vals = filter_form.getForm().getValues();
		var flag = true;
		for ( value in vals )
		{
			if ( vals[value] != "" )
			flag = false;
		}
		if ( flag )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { filter_form.getForm().findField(0).focus()});
			thisWindow.searchInProgress = false;
			return false;
		}

		var arr = filter_form.find('disabled', true);
		var params = filter_form.getForm().getValues();

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
			{
				if (arr[i].hiddenName != undefined)
					params[arr[i].hiddenName] = arr[i].getValue();
				else if (arr[i].name != undefined)
					params[arr[i].name] = arr[i].getValue();
			}
		}
		
		var Year = Ext.getCmp('EPLDPSW_YearCombo').getValue();
		if (Year>0)
		{
			params['PersonDopDisp_Year'] = Year;
		}

		if (filter_form.getForm().isValid())
		{
			if ( soc_card_id )
			{
				var params = {
					soc_card_id: soc_card_id,
					SearchFormType: params.SearchFormType
				};
			}			
			params.start = 0;
			params.limit = 100;

			if (!Ext.isEmpty(params.autoLoadArchiveRecords)) {
				current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispProf",
			EvnPLDispProf_grid.getStore().removeAll();
			EvnPLDispProf_grid.getStore().baseParams = params;
			EvnPLDispProf_grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
		else {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	doStreamInputSearch: function() {
		var grid = this.findById('EPLDPSW_EvnPLDispProfSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispProfSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispProfStream_begDate = this.begDate;
		params.EvnPLDispProfStream_begTime = this.begTime;
		if ( !params.EvnPLDispProfStream_begDate && !params.EvnPLDispProfStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispProfStream",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				callback: function(){
					thisWindow.searchInProgress = false;
				},
				params: params
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var current_window = this;
		/*Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);

					current_window.begDate = response_obj.begDate;
					current_window.begTime = response_obj.begTime;
					if ( current_window.isStream ) {
						current_window.doStreamInputSearch();
					}
					current_window.findById('EPLDPSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});*/
	},
	height: 550,
	id: 'EvnPLDispProfSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDPSW_SearchButton');
	},
	printCost: function() {
		var grid = this.EvnPLDispProfSearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispProf_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispProf_id'),
				type: 'EvnPLDispProf',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.EvnPLDispProfSearchGrid.getGrid();
		var menuPrint = this.EvnPLDispProfSearchGrid.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispProf_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispProf_IsEndStage') != lang['da']);
			}
		}
	},
	initComponent: function() {
		var win = this;
		
		function addTooltip(value, metadata, record, rowIndex, colIndex, store){
			var qtip = '';
			switch (record.get('AccessType_Code')) {
				case 1:
					qtip = lang['vozrast_patsienta_ne_sootvetsvuet_kriteriyu_bolshe_ili_raven_21_godu_i_kraten_trem_21_24_27_i_t_d'];
				break;
				case 2:
					qtip = lang['patsient_uje_prohodil_dd_v_2011_i_2012_godu'];
				break;
				case 3:
					qtip = lang['patsient_sostoit_v_registre_vov'];
				break;
				case 4:
					qtip = lang['patsient_uje_imeet_kartu_dd_v_drugom_lpu'];
				break;
			}			
			if (!Ext.isEmpty(metadata) && qtip.length > 0) {
				metadata.attr = 'ext:qtip="' + qtip + '"';
			}
			return value;
		}
		
		win.EvnPLDispProfSearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispProfSearchWindow').addEvnPLDP(); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispProfSearchWindow').openEvnPLDPEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispProfSearchWindow').openEvnPLDPEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispProfSearchWindow').deleteEvnPLDP(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispProfSearchWindow').refreshEvnPLDPList(); } },
				{ name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
				}},
				{
					hidden: false,
					name:'action_printpassport',
					tooltip: lang['napechatat_pasport_zdorovya'],
					icon: 'img/icons/print16.png',
					handler: function() {
						var grid = Ext.getCmp('EPLDPSW_EvnPLDispProfSearchGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();							
						if (!record) {
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
							return false;
						}
						var evn_pl_id = record.get('EvnPLDispProf_id');
						var server_id = record.get('Server_id');							
						if (!evn_pl_id)
							return false;
						var id_salt = Math.random();
						var win_id = 'print_passport' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=EvnPLDispProf&m=printEvnPLDispProfPassport&EvnPLDispProf_id=' + evn_pl_id + '&Server_id=' + server_id, win_id);
					}, 
					text: lang['pechat_pasporta_zdorovya']
				}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				if(win.viewOnly == true){
					win.EvnPLDispProfSearchGrid.setActionDisabled('action_view', false);
					win.EvnPLDispProfSearchGrid.setActionDisabled('action_edit', true);
					win.EvnPLDispProfSearchGrid.setActionDisabled('action_delete', true);
				}
				else
				{
					var disabled = false;
					if (getGlobalOptions().archive_database_enable) {
						disabled = disabled || (record.get('archiveRecord') == 1);
					}
					if (record.get('AccessType_Code') != 0) {
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_view', true);
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_edit', true);
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_delete', true);
					} else {
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_view', false);
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_edit', disabled);
						win.EvnPLDispProfSearchGrid.setActionDisabled('action_delete', disabled);
					}

					if ( record.get('EvnPLDispProf_id') && record.get('AccessType_Code') == 0 ) {
						this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLDispProf_IsTransit') == 1));
					}
					else {
						this.setActionDisabled('action_setevnistransit', true);
					}
				}
				win.checkPrintCost();
			},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLDPSW_SearchButton', type: 'field'
			},
			id: 'EPLDPSW_EvnPLDispProfSearchGrid',
			layout: 'fit',
			object: 'EvnPLDP',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'EvnPLDispProf_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPLDispProf_IsTransit', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'AccessType_Code', type: 'int', hidden: true },
				//{ name: 'UslugaComplex_Name', type: 'string', hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]), header: 'Услуга диспансеризации', width: 150 },
				{ name: 'Person_Surname', renderer:addTooltip, header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', renderer:addTooltip, header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', renderer:addTooltip, header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
				{ name: 'ua_name', type: 'string', header: langs('Адрес регистрации')},
				{ name: 'pa_name', type: 'string', header: langs('Адрес проживания')},
				{ name: 'EvnPLDispProf_rejDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа профосмотра') },
				{ name: 'EvnPLDispProf_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала профосмотра') },
				{ name: 'EvnPLDispProf_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания профосмотра') },
				{ name: 'EvnPLDispProf_IsEndStage', type: 'string', header: langs('Профосмотр закончен') },
				{ name: 'EvnPLDispProf_HealthKind_Name', type: 'string', header: langs('Группа здоровья') },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), header: langs('Справка о стоимости лечения'), width: 150 }
			]
		});
		
		win.EvnPLDispProfSearchGrid.ViewGridPanel.view.getRowClass = function (row, index)
			{
				var cls = '';

				if ( row.get('AccessType_Code') != 0 ) {
					cls = cls+'x-grid-rowgray ';
				}

				if ( cls.length == 0 ) {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		
			
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
				autoHeight: true,
				id: 'EPLDPSW_SearchFilterPanel',
				region: 'north',
				//layout: 'border',
				items: [
					{
						border: false,
						region: 'center',
						layout: 'column',
						height: 25,
						items: [
						{
							width: 300,
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								xtype: 'swbaselocalcombo',
								mode: 'local',
								triggerAction: 'all',
								fieldLabel: lang['god'],
								store: new Ext.data.JsonStore(
								{
									key: 'EvnPLDisp_Year',
									autoLoad: false,
									fields:
									[
										{name:'EvnPLDisp_Year',type: 'int'},
										{name:'count', type: 'int'}
									],
									url: C_EPLD_LOAD_YEARS
								}),
								id: 'EPLDPSW_YearCombo',
								hiddenName: 'EvnPLDisp_Year',
								tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDisp_Year}</td>'+
									'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
									'</div></tpl>',
								region: 'north',
								valueField: 'EvnPLDisp_Year',
								displayField: 'EvnPLDisp_Year',
								editable: false,
								tabIndex: 2036,
								enableKeyEvents: true,
								listeners: {
									'keydown': function(combo, e)
									{
										if ( e.getKey() == Ext.EventObject.ENTER )
										{											
											e.stopEvent();
											var current_window = Ext.getCmp('EvnPLDispProfSearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispProfSearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDPSW_EvnPLDispProfSearchGrid').focus();													
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDPSW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDPSW+56
							}]
						}/*, {
							width: 400,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDPSW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDPSW+57
							}]
						}*/]
					},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispProfSearchFilterForm',
						ownerWindow: this,
                                                listeners: {
                                                    'collapse': function(p) {
                                                        p.ownerWindow.doLayout();
                                                    },
                                                    'expand': function(p) {
                                                        p.ownerWindow.doLayout();
                                                    }
                                                },
						region: 'north',
						searchFormType: 'EvnPLDispProf',
						tabIndexBase: TABINDEX_EPLDPSW,
						tabPanelId: 'EPLDPSW_SearchFilterTabbar',
						tabGridId: 'EPLDPSW_EvnPLDispProfSearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 200,
							id: 'EPLDP_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispProfSearchFilterForm');
									form.getForm().findField('EvnPLDispProf_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: lang['6_profosmotr'],
							items: [/*{
								hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
								layout: 'form',
								border: false,
								items: [{
									hiddenName: 'UslugaComplex_id',
									width: 400,
									fieldLabel: lang['usluga_dispanserizatsii'],
									dispOnly: true,
									DispClass_id: 5,
									nonDispOnly: false,
									xtype: 'swuslugacomplexnewcombo'
								}]
							},*/ {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispProf_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDPSW + 59,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispProf_setDate_Range',
										tabIndex: TABINDEX_EPLDPSW + 60,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_okonchaniya'],
										name: 'EvnPLDispProf_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDPSW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispProf_disDate_Range',
										tabIndex: TABINDEX_EPLDPSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							},
							{
								allowBlank: true,
								editable: false,
								displayField: 'UslugaComplex_Name',
								fieldLabel: lang['usluga'],
								hiddenName: 'EvnPLDisp_UslugaComplex',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'UslugaComplex_id'
									}, [
										{ name: 'UslugaComplex_id', mapping: 'UslugaComplex_id'},
										{ name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code'},
										{ name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name'}
									]),
									url: '/?c=Common&m=loadDispUslugaComplex'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3>{UslugaComplex_Code}</h3></td><td>&nbsp;&nbsp;{UslugaComplex_Name}</td></tr></table>',
									'</div></tpl>'
								),
								triggerAction: 'all',
								hideTrigger: false,
								valueField: 'UslugaComplex_id',
								width: 500,
								listWidth: 700,
								xtype: 'swbaselocalcombo'
							},
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['otkaz_ot_profosmotra'],
										hiddenName: 'EvnPLDispProf_IsRefusal',
										tabIndex: TABINDEX_EPLDPSW + 66,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['sluchay_oplachen'],
										tabIndex: TABINDEX_EPLDPSW + 66,
										hiddenName: 'EvnPLDispProf_isPaid',
										width: 100,
										listeners: {
											'keydown': function(combo, e) {
												if ( !e.shiftKey && e.getKey() == e.TAB )
												{
													Ext.TaskMgr.start({
														run : function() {
															Ext.TaskMgr.stopAll();
															Ext.getCmp('EPLDPSW_EvnPLDispProfSearchGrid').focus();													
														},
														interval : 200
													});
												}
											}
										},
										xtype: 'swyesnocombo'
									}]
								}]
							},
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['sluchay_zakonchen'],
										hiddenName: 'EvnPLDispProf_IsFinish',
										tabIndex: TABINDEX_EPLDPSW + 66,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['obslujen_mobilnoy_brigadoy'],
										hiddenName: 'EvnPLDispProf_isMobile',
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}]
							},
							{
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								/*listeners: {
									'render': function(combo) {
										combo.getStore().load();
									}
								},*/
								hiddenName: 'EvnPLDispProf_HealthKind_id',
								tabIndex: TABINDEX_EPLDPSW + 66,
								validateOnBlur: false,
								width: 100,
								xtype: 'swhealthkindcombo'
							},
							{
								xtype: 'swlpubuildingglobalcombo',
								hiddenName: 'Disp_LpuBuilding_id',
								width: 330
							},
							{
								xtype: 'swlpusectionglobalcombo',
								hiddenName: 'Disp_LpuSection_id',
								width: 330
							},
							{
								xtype: 'swmedstafffactglobalcombo',
								hiddenName: 'Disp_MedStaffFact_id',
								width: 330
							}
							]
						}]
					})]
			}),
			win.EvnPLDispProfSearchGrid
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDPSW_SearchButton',
				tabIndex: TABINDEX_EPLDPSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDPSW+91,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					if ( this.ownerCt.isStream == false )
					{
						this.ownerCt.setStreamInputMode();
					}
					else
					{
						this.ownerCt.setSearchMode();
					}
				},
				hidden: true,
				tabIndex: TABINDEX_EPLDPSW+92,
				text: "Режим потокового ввода"
			},/* {
				handler: function() {
					var base_form = this.findById('EvnPLDispProfSearchFilterForm').getForm();
					base_form.submit();					
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EPLSW + 111,
				text: lang['pechat_spiska']
			},*/
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDPSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDPSW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					this.buttons[0].focus(true, 200);
				}.createDelegate(this)
			}
			]
		});
		sw.Promed.swEvnPLDispProfSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispProfSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDPSW_SearchFilterTabbar');

			switch (e.getKey())
			{
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
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}],
	layout: 'border',
	loadYearsCombo: function () {
		var years_combo = this.findById('EPLDPSW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 5
				},
				callback: function() {
					var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
				}
			});
		}
		else
		{
			var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDPEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispProf_grid = current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispProfEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_profosmotra_uje_otkryito']);
			return false;
		}

		var record = EvnPLDispProf_grid.getSelectionModel().getSelected();
		if (!record)
		{
			return false;
		}
		
		if (record.get('AccessType_Code') != 0) {
			return false;
		}
		
		var EvnPLDispProf_id = record.data.EvnPLDispProf_id;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (/*EvnPLDispProf_id > 0 &&*/ person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				DispClass_id: 5,
				EvnPLDispProf_id: EvnPLDispProf_id,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDPList();
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			getWnd('swEvnPLDispProfEditWindow').show(params);
		}
	},
	plain: true,
	refreshEvnPLDPList: function(action) {
		var current_window = this;
		var EvnPLDispProf_grid = current_window.findById('EPLDPSW_EvnPLDispProfSearchGrid').ViewGridPanel;
		if ( this.isStream ) {
			this.doStreamInputSearch();
			this.loadYearsCombo();
		}
		else {
			this.doSearch();
		}
	},
	resizable: true,
	setSearchMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].enable();
		this.buttons[1].enable();
		button.setText(lang['rejim_potokovogo_vvoda']);
		this.setTitle(WND_POL_EPLDPSEARCH);
		Ext.getCmp('EvnPLDispProfSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispProfSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDPSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDPSW_YearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EvnPLDispProfSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDPSW_EvnPLDispProfSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDPSTREAM);
		this.findById('EvnPLDispProfSearchFilterForm').hide();
		Ext.getCmp('EvnPLDispProfSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDPSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDPSW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispProfSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDPSW_SearchFilterTabbar').setActiveTab(2);
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

		if ( !this.EvnPLDispProfSearchGrid.getAction('action_setevnistransit') ) {
			this.EvnPLDispProfSearchGrid.addActions({
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

		this.EvnPLDispProfSearchGrid.setActionHidden('action_setevnistransit', !lpuIsTransit());

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		var form = this.findById('EvnPLDispProfSearchFilterForm');
		var base_form = form.getForm();
		//EPLDPSW_EvnPLDispProfSearchGrid
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('EPLDPSW_EvnPLDispProfSearchGrid').setActionDisabled('action_add', this.viewOnly);
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		
		if ( !Ext.isEmpty(getGlobalOptions().lpu_id) ) {
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
			if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','vologda','penza'])) //https://redmine.swan.perm.ru/issues/78988
			{
				var params = new Object();
				params.Lpu_id = getGlobalOptions().lpu_id;
				base_form.findField('LpuRegion_Fapid').getStore().load({
					params: params
				});
			}
			if ( !isSuperAdmin() && !getRegionNick().inlist(['krym','kareliya','buryatiya'])) {
				if(!getWnd('swWorkPlaceMZSpecWindow').isVisible())
					base_form.findField('AttachLpu_id').disable();
			}
			if(swLpuBuildingGlobalStore.data.length == 0){
				swLpuBuildingGlobalStore.load();
			}
			if(swLpuSectionGlobalStore.data.length == 0){
				swLpuSectionGlobalStore.load();
			}
			if(swMedStaffFactGlobalStore.data.length == 0){
				swMedStaffFactGlobalStore.load();
			}
			swLpuBuildingGlobalStore.clearFilter();
			swLpuSectionGlobalStore.clearFilter();
			swMedStaffFactGlobalStore.clearFilter();
			base_form.findField('Disp_LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
			base_form.findField('Disp_LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('Disp_MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}
		base_form.findField('LpuRegionType_id').getStore().filterBy( //https://redmine.swan.perm.ru/issues/78988
			function(record)
			{
				//if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick() == 'perm')
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','vologda']))
					return false;
				else
					return true;
			}
		);
		base_form.findField('LpuAttachType_id').setValue(1);

		base_form.findField('EvnPLDispProf_HealthKind_id').getStore().clearFilter();
		base_form.findField('EvnPLDispProf_HealthKind_id').lastQuery = '';
		/*base_form.findField('EvnPLDispProf_HealthKind_id').getStore().filterBy(function(rec) {
			if(!rec.get('HealthKind_Code').inlist(['6','7']))
			{
				return true;
			}
			return false;
		});*/

		var UslugaCombo = base_form.findField('EvnPLDisp_UslugaComplex');
		UslugaCombo.getStore().removeAll();
		UslugaCombo.getStore().baseParams = {
			DispClass_id: 5
		}
		UslugaCombo.getStore().load();
		this.loadYearsCombo();
		this.findById('EPLDPSW_SearchFilterTabbar').setActiveTab(2);
	},
	title: WND_POL_EPLDPSEARCH,
	width: 800
});
