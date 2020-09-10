/**
* swEvnPLDispDop13SecondSearchWindow - окно поиска талона по диспансеризации взрослых 2013 - 2 этап
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
* @version		16.05.2013
* @comment		Префикс для id компонентов EPLDD13SSW (EvnPLDispDop13SecondSearchWindow)
*
*
* Использует: окно редактирования талона по диспансеризации взрослых 2013 (swEvnPLDispDop13EditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDop13SecondSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDop13SecondSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDop13SecondSearchWindow.js',
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

		var grid = this.EvnPLDispDop13SearchGrid.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13Second_id') || grid.getSelectionModel().getSelected().get('EvnPLDispDop13Second_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLDispDop13Second_id'),
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
						record.set('EvnPLDispDop13Second_IsTransit', Evn_IsTransit);
						record.commit();
						this.EvnPLDispDop13SearchGrid.onRowSelect(null, null, record);
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
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.EvnPLDispDop13SearchGrid.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13Second_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispDop13Second_id');

		var params = {
			EvnPLDispDop13_id: evn_pl_dd_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_dvn_2_etap']);
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
						var fieldsArray = [ 'EvnPLDispDop13Second_id', 'EvnPLDispDop13Second_napDate', 'EvnPLDispDop13Second_rejDate',
							'EvnPLDispDop13Second_setDate', 'EvnPLDispDop13Second_disDate', 'EvnPLDispDop13Second_IsEndStage',
							'EvnPLDispDop13Second_HealthKind_Name'
						];

						for ( idx in fieldsArray ) {
							record.set(fieldsArray[idx], null);
						}

						record.commit();
					}

					if ( !Ext.isEmpty(record) ) {
						grid.getSelectionModel().selectRow(record);
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_dvn_2-go_etapa_voznikli_oshibki']);
				}
			},
			params: params,
			url: '/?c=EvnPLDispDop13&m=deleteEvnPLDispDop13'
		});
	},
	deleteEvnPLDD: function() {
		var win = this;
		var grid = win.EvnPLDispDop13SearchGrid.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13Second_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispDop13Second_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vsyu_informatsiyu_v_karte_dispanserizatsii_vzroslogo_naseleniya_2_etap'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispDop13SecondSearchFilterForm'),
		AttachLpu_id = filter_form.getForm().findField('AttachLpu_id');
		filter_form.getForm().reset();

		if (AttachLpu_id.disabled == true && getRegionNick().inlist(['ekb']))
		{
			AttachLpu_id.setValue(getGlobalOptions().lpu_id);
		}

		current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').getGrid().getStore().removeAll();
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
		var filter_form = current_window.findById('EvnPLDispDop13SecondSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').ViewGridPanel;

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
		
		var Year = Ext.getCmp('EPLDD13SSW_YearCombo').getValue();
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
				current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispDop13Sec",
			EvnPLDispDop13_grid.getStore().removeAll();
			EvnPLDispDop13_grid.getStore().baseParams = params;
			if (getRegionNick() != 'ekb') {
				EvnPLDispDop13_grid.getStore().baseParams.EvnPLDispDop13_IsTwoStage = 2;
			}
			EvnPLDispDop13_grid.getStore().load({
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
		var grid = this.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispDop13SecondSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispDop13Stream_begDate = this.begDate;
		params.EvnPLDispDop13Stream_begTime = this.begTime;
		if ( !params.EvnPLDispDop13Stream_begDate && !params.EvnPLDispDop13Stream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispDop13Stream",
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
					current_window.findById('EPLDD13SSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});*/
	},
	height: 550,
	id: 'EvnPLDispDop13SecondSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDD13SSW_SearchButton');
	},
	printCost: function() {
		var grid = this.EvnPLDispDop13SearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispDop13Second_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispDop13Second_id'),
				type: 'EvnPLDispDop13',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.EvnPLDispDop13SearchGrid.getGrid();
		var menuPrint = this.EvnPLDispDop13SearchGrid.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispDop13Second_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispDop13Second_IsEndStage') != lang['da']);
			}
		}
	},
	initComponent: function() {
		var win = this;
		
		function addTooltip(value, metadata, record, rowIndex, colIndex, store){
			var qtip = '';
			switch (record.get('AccessType_Code')) {
				case 1:
					qtip = 'Возраст пациента не соответсвует критерию: больше или равен 21 году и кратен трём (21,24,27 и т.д.)';
				break;
				case 2:
					qtip = lang['patsient_uje_prohodil_dd_v_2011_i_2012_godu'];
				break;
				case 3:
					qtip = lang['patsient_sostoit_v_registre_vov'];
				break;
				case 4:
					qtip = lang['patsient_uje_imeet_kartu_dd_v_drugoy_mo'];
				break;
			}			
			if (!Ext.isEmpty(metadata) && qtip.length > 0) {
				metadata.attr = 'ext:qtip="' + qtip + '"';
			}
			return value;
		}
		
		win.EvnPLDispDop13SearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { win.addEvnPLDD(); }, disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.openEvnPLDDEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.openEvnPLDDEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { win.deleteEvnPLDD(); } },
				{ name: 'action_refresh', handler: function() { win.refreshEvnPLDDList(); } },
				{ name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
				}},
				{
					hidden: false,
					name:'action_printpassport',
					tooltip: lang['napechatat_pasport_zdorovya'],
					icon: 'img/icons/print16.png',
					handler: function() {
						var grid = win.EvnPLDispDop13SearchGrid.getGrid();
						var record = grid.getSelectionModel().getSelected();
						if (!record) {
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
							return false;
						}
						var evn_pl_id = record.get('EvnPLDispDop13Second_id');
						var server_id = record.get('Server_id');
						if (!evn_pl_id)
							return false;
						var id_salt = Math.random();
						var win_id = 'print_passport' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id, win_id);
					}, 
					text: lang['pechat_pasporta_zdorovya']
				}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				if(win.viewOnly == true){
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', false);
				}
				else
				{
					if ( record.get('AccessType_Code') != 0 ) {
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', true);
					}
					else {
						var disabled = false;
						if (getGlobalOptions().archive_database_enable) {
							disabled = disabled || (record.get('archiveRecord') == 1);
						}
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', disabled);

						if ( Ext.isEmpty(record.get('EvnPLDispDop13Second_id')) ) {
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', true);
						}
						else {
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', disabled);
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', false);
						}
					}

					if ( record.get('EvnPLDispDop13Second_id') && record.get('AccessType_Code') == 0 ) {
						this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLDispDop13Second_IsTransit') == 1));
					}
					else {
						this.setActionDisabled('action_setevnistransit', true);
					}
				}
				win.checkPrintCost();
			},
			auditOptions: {
				field: 'EvnPLDispDop13_id',
				key: 'EvnPLDispDop13Second_id'
			},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=SearchEvnPLDispDop13Sec&m=searchData',
			focusOn: {
				name: 'EPLDD13SSW_SearchButton', type: 'field'
			},
			id: 'EPLDD13SSW_EvnPLDispDop13SearchGrid',
			layout: 'fit',
			object: 'EvnPLDD',
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
				{ name: 'EvnPLDispDop13_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPLDispDop13Second_id', type: 'int', header: 'ID', hidden: true },
				{ name: 'EvnPLDispDop13Second_IsTransit', type: 'int', header: lang['perehodnyiy_sluchay'], hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'AccessType_Code', type: 'int', hidden: true },
				{ name: 'UslugaComplex_Name', type: 'string', hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]), header: langs('Услуга диспансеризации'), width: 150 },
				{ name: 'Person_Surname', renderer:addTooltip, header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', renderer:addTooltip, header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', renderer:addTooltip, header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
				{ name: 'ua_name', type: 'string', header: langs('Адрес регистрации')},
				{ name: 'pa_name', type: 'string', header: langs('Адрес проживания')},
				{ name: 'EvnPLDispDop13_rejDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа от диспансеризации') },
				{ name: 'EvnPLDispDop13_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала 1 этапа') },
				{ name: 'EvnPLDispDop13_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания 1 этапа') },
				{ name: 'VopOsm_EvnUslugaDispDop_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания осмотра ВОП из 1 этапа'), hidden: true },
				{ name: 'EvnPLDispDop13_IsEndStage', type: 'string', header: langs('1 этап закончен') },
				{ name: 'EvnPLDispDop13_HealthKind_Name', type: 'string', header: langs('Группа здоровья 1 этап') },
				{ name: 'EvnPLDispDop13Second_napDate', type: 'date', format: 'd.m.Y', header: langs('Дата направления на 2 этап') },
				{ name: 'EvnPLDispDop13Second_rejDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа от 2 этапа') },
				{ name: 'EvnPLDispDop13Second_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала 2 этапа') },
				{ name: 'EvnPLDispDop13Second_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания 2 этапа') },
				{ name: 'EvnPLDispDop13Second_IsEndStage', type: 'string', header: langs('2 этап закончен') },
				{ name: 'EvnPLDispDop13Second_HealthKind_Name', type: 'string', header: langs('Группа здоровья 2 этап') },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) }
			]
		});
		
		win.EvnPLDispDop13SearchGrid.ViewGridPanel.view.getRowClass = function (row, index) {
			var cls = '';

			if ( row.get('AccessType_Code') != 0 ) {
				cls = cls+'x-grid-rowgray ';
			}

			if ( cls.length == 0 ) {
				cls = 'x-grid-panel';
			}

			return cls;
		};
		
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
                autoHeight: true,
				id: 'EPLDD13SSW_SearchFilterPanel',
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
								id: 'EPLDD13SSW_YearCombo',
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
											var current_window = Ext.getCmp('EvnPLDispDop13SecondSearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispDop13SecondSearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDD13SSW_EvnPLDispDop13SearchGrid').focus();
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDD13SSW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDD13SSW+56
							}]
						}/*, {
							width: 400,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDD13SSW_Stream_begDateTime',
								width: 165,
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDD13SSW+57
							}]
						}*/]
					},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispDop13SecondSearchFilterForm',
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
						searchFormType: 'EvnPLDispDop13Sec',
						tabIndexBase: TABINDEX_EPLDD13SSW,
						tabPanelId: 'EPLDD13SSW_SearchFilterTabbar',
						tabGridId: 'EPLDD13SSW_EvnPLDispDop13SearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 200,
							id: 'EPLDD13S_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispDop13SecondSearchFilterForm');
									form.getForm().findField('EvnPLDispDop13Second_setDate').focus(400, true);
								}.createDelegate(this)
							},
							title: '<u>6</u>. Диспансеризация 2 этап',
							items: [{
								hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
								layout: 'form',
								border: false,
								items: [{
									hiddenName: 'UslugaComplex_id',
									width: 400,
									fieldLabel: lang['usluga_dispanserizatsii'],
									dispOnly: true,
									DispClass_id: 2,
									nonDispOnly: false,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispDop13Second_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDD13SSW + 59,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispDop13Second_setDate_Range',
										tabIndex: TABINDEX_EPLDD13SSW + 60,
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
										name: 'EvnPLDispDop13Second_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDD13SSW + 61,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispDop13Second_disDate_Range',
										tabIndex: TABINDEX_EPLDD13SSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								fieldLabel: lang['2_etap_zakonchen'],
								hiddenName: 'EvnPLDispDop13Second_IsFinish',
								tabIndex: TABINDEX_EPLDD13SSW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								loadParams: (getRegionNick() == 'penza' ? {params: {where: 'where HealthKind_Code in (1,2,6,7)'}} : null),
								listeners: {
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDD13SSW_EvnPLDispDop13SearchGrid').focus();
												},
												interval : 200
											});
										}
									}
								},
								hiddenName: 'EvnPLDispDop13Second_HealthKind_id',
								tabIndex: TABINDEX_EPLDD13SSW + 66,
								validateOnBlur: false,
								width: 100,
								xtype: 'swhealthkindcombo'
							}, {
								fieldLabel: lang['sluchay_oplachen'],
								tabIndex: TABINDEX_EPLDD13SSW + 66,
								hiddenName: 'EvnPLDispDop13Second_isPaid',
								width: 100,
								listeners: {
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDD13SSW_EvnPLDispDop13SearchGrid').focus();
												},
												interval : 200
											});
										}
									}
								},
								xtype: 'swyesnocombo'
							}, {
								fieldLabel: lang['obslujen_mobilnoy_brigadoy'],
								hiddenName: 'EvnPLDispDop13Second_isMobile',
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}]
					})]
			}),
			win.EvnPLDispDop13SearchGrid
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EPLDD13SSW_SearchButton',
				tabIndex: TABINDEX_EPLDD13SSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDD13SSW+91,
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
				tabIndex: TABINDEX_EPLDD13SSW+92,
				text: "Режим потокового ввода"
			},/* {
				handler: function() {
					var base_form = this.findById('EvnPLDispDop13SecondSearchFilterForm').getForm();
					var filter_form = this.findById('EvnPLDispDop13SecondSearchFilterForm');

					var arr = filter_form.find('disabled', true);
					var i;

					for ( i = 0; i < arr.length; i++ ) {
						arr[i].enable();
					}

					base_form.submit();

					for ( i = 0; i < arr.length; i++ ) {
						arr[i].disable();
					}
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
				tabIndex: TABINDEX_EPLDD13SSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDD13SSW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					this.buttons[0].focus(true, 200);
				}.createDelegate(this)
			}
			]
		});
		sw.Promed.swEvnPLDispDop13SecondSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispDop13SecondSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDD13SSW_SearchFilterTabbar');

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
		var years_combo = this.findById('EPLDD13SSW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 2
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
	addEvnPLDD: function()
	{
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispDop13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDD13SSW_YearCombo').getValue();

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				var params = {
					action: 'edit',
					DispClass_id: 2,
					Year: Year,
					EvnPLDispDop13_fid: null,
					EvnPLDispDop13_id: null,
					PayType_id: null,
					VopOsm_EvnUslugaDispDop_disDate: null,
					onHide: Ext.emptyFn,
					callback: function() {
						current_window.refreshEvnPLDDList();
					},
					Person_id: person_data.Person_id,
					Server_id: person_data.Server_id
				};
				if (getGlobalOptions().archive_database_enable) {
					params.archiveRecord = record.get('archiveRecord');
				}
				getWnd('swEvnPLDispDop13EditWindow').show(params);
			},
			searchMode: 'dddispclass2',
			Year: Year
		});
	},
	openEvnPLDDEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispDop13EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}

		var record = EvnPLDispDop13_grid.getSelectionModel().getSelected();
		if (!record)
		{
			return false;
		}
		
		if (record.get('AccessType_Code') != 0) {
			return false;
		}
		
		var EvnPLDispDop13_id = record.get('EvnPLDispDop13_id');
		var EvnPLDispDop13Second_id = record.get('EvnPLDispDop13Second_id');
		var PayType_id = record.get('PayType_id');
		var person_id = record.get('Person_id');
		var server_id = record.get('Server_id');
		var VopOsm_EvnUslugaDispDop_disDate = record.get('VopOsm_EvnUslugaDispDop_disDate'); //дата окончания осмотра врачом-терапевтом из первого этапа
		var Year = this.findById('EPLDD13SSW_YearCombo').getValue();

		if (/*EvnPLDispDop13_id > 0 &&*/ person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				DispClass_id: 2,
				Year: Year,
				EvnPLDispDop13_fid: EvnPLDispDop13_id,
				EvnPLDispDop13_id: EvnPLDispDop13Second_id,
				PayType_id: PayType_id,
				VopOsm_EvnUslugaDispDop_disDate: VopOsm_EvnUslugaDispDop_disDate,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDDList();
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			getWnd('swEvnPLDispDop13EditWindow').show(params);
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		var current_window = this;
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').ViewGridPanel;
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
		this.setTitle(WND_POL_EPLDD13SSEARCH);
		Ext.getCmp('EvnPLDispDop13SecondSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispDop13SecondSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDD13SSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDD13SSW_YearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EvnPLDispDop13SecondSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDD13SSW_EvnPLDispDop13SearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDD13SSTREAM);
		this.findById('EvnPLDispDop13SecondSearchFilterForm').hide();
		Ext.getCmp('EvnPLDispDop13SecondSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDD13SSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDD13SSW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispDop13SecondSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDD13SSW_SearchFilterTabbar').setActiveTab(2);
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

		var win = this;

		if (getRegionNick() == 'ekb') {
			win.EvnPLDispDop13SearchGrid.setActionHidden('action_add', true);
			win.EvnPLDispDop13SearchGrid.setActionDisabled('action_add', true);
			// проверяем наличие объёма ДВН2
			win.getLoadMask('Проверка налчичия объёма ДВН2').show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					win.getLoadMask('Проверка налчичия объёма ДВН2').hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						// Если для текущей МО есть открытая запись в этом объеме, то на панели кнопок отображается кнопка «Добавить».
						if (response_obj.volumeExists) {
							win.EvnPLDispDop13SearchGrid.setActionHidden('action_add', false);
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_add', false);
						}
					}
				},
				url: '/?c=EvnPLDispDop13&m=checkDispClass2Volume'
			});
		}

		if ( !this.EvnPLDispDop13SearchGrid.getAction('action_setevnistransit') ) {
			this.EvnPLDispDop13SearchGrid.addActions({
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

		this.EvnPLDispDop13SearchGrid.setActionHidden('action_setevnistransit', !lpuIsTransit());

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		this.findById('EPLDD13SSW_SearchFilterTabbar').setActiveTab(2);
		
		var form = this.findById('EvnPLDispDop13SecondSearchFilterForm');
		var base_form = form.getForm();
		base_form.findField('LpuRegion_id').clearValue();
		base_form.findField('AttachLpu_id').clearBaseFilter();
		base_form.findField('AttachLpu_id').lastQuery = '';
		base_form.findField('AttachLpu_id').setAllowBlank(true);

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}

		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		
		if ( !Ext.isEmpty(getGlobalOptions().lpu_id) ) {
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
			if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','krasnoyarsk'])) //https://redmine.swan.perm.ru/issues/78988
			{
				var params = new Object();
				params.Lpu_id = getGlobalOptions().lpu_id;
				base_form.findField('LpuRegion_Fapid').getStore().load({
					params: params
				});
			}

			if (!isSuperAdmin() && !getRegionNick().inlist(['pskov', 'ufa','krym','kareliya','buryatiya'])) {
				if(!getWnd('swWorkPlaceMZSpecWindow').isVisible())
					base_form.findField('AttachLpu_id').disable();
			}
			
			if (!Ext.isEmpty(getGlobalOptions().medpersonal_id)) {
				Ext.Ajax.request(
				{
					url: '/?c=LpuRegion&m=getMedPersLpuRegionList',
					callback: function(options, success, response) 
					{
						if (success)
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj[0] && response_obj[0].LpuRegion_id)
							{
								base_form.findField('LpuRegion_id').setValue(response_obj[0].LpuRegion_id);
								base_form.findField('LpuRegion_id').onLoadStore = function() {
									// alert(1);
								}
							}
						}
					},
					params: {
						MedPersonal_id: getGlobalOptions().medpersonal_id,
						Lpu_id: getGlobalOptions().lpu_id,
						Ignore_Closed: 1
					}
				});
			}

			if (getRegionNick() == 'ekb') {
				// #100008 Регион: Свердловская область
				// Значение фильтра «МО прикрепления» является значение своей МО (в объеме это МО прикрепления) и  значения из объема «Мед. диспансеризация взрослого населения в чужой МО»(в объеме это «МО проведения»), где
				// o «МО прикрепления» – МО, которая разрешила конкретным МО проводить осмотры\диспансеризацию по их прикрепленному населению
				// o «МО проведения» - МО, которая проводит осмотры\ диспансеризацию
				// Поле доступно для редактирования, если есть несколько вариантов значений для выбора. Если вариант для выбора один, тогда он устанавливается по умолчанию и поле недоступно для редактирования

				win.getLoadMask('Получение списка доступных МО прикрепления').show();
				Ext.Ajax.request({
					callback: function(options, success, response) {
						win.getLoadMask().hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							// Отключаем поле Другая МО. Оно добавляется при каждом клике на поле Мо прекрепоения
							base_form.findField('AttachLpu_id').getStore().addListener('add', function (store, records, index) {

								var anotherMOindex = store.find('Lpu_id', 100500);
								if (anotherMOindex > -1)
								{
									store.removeAt(anotherMOindex);
								}

								return true;

							});

							// Если у МО открыт объем ДВН_Б_ПРИК
							if (response_obj.Dvn_B_Prik == true)
							{
								// После фильтрации добавляем поле без прикрепления к мо, которое будет выводить людей, не прикрепленных ни к одной мо
								// base_form.findField('AttachLpu_id').getStore().addListener('datachanged', function (store) {
								//
								// 	var extraRecord = new Ext.data.Record({Lpu_id: 666666, Lpu_Nick: 'Без прикрепления к МО'}),
								// 		idx = store.find('Lpu_id', 666666);
								//
								//
								// 	if (idx = -1)
								// 	{
								// 		store.insert(0, extraRecord);
								// 	}
								//
								// });

							} else
							{
								base_form.findField('AttachLpu_id').setAllowBlank(false);
							}

							var cnt = 0;
							if ( response_obj.Lpus ) {
								// Фильтруем поле "МО прикрепления".
								base_form.findField('AttachLpu_id').setBaseFilter(function (rec) {
									if (rec.get('Lpu_id').inlist(response_obj.Lpus)) {
										cnt++;
										return true;
									}

									return false;
								});


								base_form.findField('AttachLpu_id').lastQuery = '';
								base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
								// Если значений осталось больше чем одно, то делаем поле доступным.
								if (cnt > 1) {
									base_form.findField('AttachLpu_id').enable();
								} else
								{
									base_form.findField('AttachLpu_id').disable();
								}
							}
						}
					}.createDelegate(this),
					params: params,
					url: '/?c=EvnPLDispDop13&m=getLpuIdsIfVolumeIsDvn_B_PrikOrNot'
				});
			}
		}
		base_form.findField('LpuRegionType_id').getStore().filterBy( //https://redmine.swan.perm.ru/issues/78988
			function(record)
			{
				//if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick() == 'perm')
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','krasnoyarsk']))
					return false;
				else
					return true;
			}
		);
		base_form.findField('LpuAttachType_id').setValue(1);
		//base_form.findField('LpuRegionType_id').setValue(1); //http://redmine.swan.perm.ru/issues/22880
		
		base_form.findField('EvnPLDispDop13Second_HealthKind_id').getStore().clearFilter();
		
		this.loadYearsCombo();
	},
	title: WND_POL_EPLDD13SSEARCH,
	width: 800
});
