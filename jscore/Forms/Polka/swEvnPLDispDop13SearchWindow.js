/**
* swEvnPLDispDop13SearchWindow - окно поиска талона по диспансеризации взрослых 2013 - 1 этап
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
* @comment		Префикс для id компонентов EPLDD13SW (EvnPLDispDop13SearchWindow)
*
*
* Использует: окно редактирования талона по диспансеризации взрослых 2013 (swEvnPLDispDop13EditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDop13SearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDop13SearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDop13SearchWindow.js',
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

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13_id') || grid.getSelectionModel().getSelected().get('EvnPLDispDop13_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLDispDop13_id'),
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
						record.set('EvnPLDispDop13_IsTransit', Evn_IsTransit);
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
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var grid = win.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispDop13_id');

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
						var fieldsArray = [ 'EvnPLDispDop13_id', 'EvnPLDispDop13_IsTransit', 'Server_id', 'Lpu_Nick', 'EvnPLDispDop13_IsMobile',
							'EvnPLDispDop13_rejDate', 'EvnPLDispDop13_setDate', 'EvnPLDispDop13_disDate', 'EvnPLDispDop13_IsEndStage',
							'EvnPLDispDop13_HealthKind_Name', 'EvnPLDispDop13Second_napDate', 'EvnPLDispDop13Second_rejDate', 'EvnPLDispDop13Second_setDate',
							'EvnPLDispDop13Second_disDate', 'EvnPLDispDop13Second_IsEndStage', 'EvnPLDispDop13Second_HealthKind_Name', 'EvnCostPrint_setDT',
							'EvnCostPrint_IsNoPrintText', 'UslugaComplex_Name'
						];

						for ( idx in fieldsArray ) {
							record.set(fieldsArray[idx], (fieldsArray[idx] == 'EvnPLDispDop13_IsMobile' ? lang['net'] : null));
						}

						record.commit();
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_dd_voznikli_oshibki']);
				}
			},
			params: params,
			url: '/?c=EvnPLDispDop13&m=deleteEvnPLDispDop13'
		});
	},
	deleteEvnPLDD: function() {
		var win = this;
		var grid = win.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop13_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_vsyu_informatsiyu_v_karte_dispanserizatsii_vzroslogo_naseleniya_1_etap'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispDop13SearchFilterForm'),
			AttachLpu_id = filter_form.getForm().findField('AttachLpu_id');
		filter_form.getForm().reset();

		if (AttachLpu_id.disabled == true && getRegionNick().inlist(['ekb']))
		{
			AttachLpu_id.setValue(getGlobalOptions().lpu_id);
		}

		//log(current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid'))
		current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').getGrid().getStore().removeAll();
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
		var filter_form = current_window.findById('EvnPLDispDop13SearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole']);
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').ViewGridPanel;

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
		
		var Year = Ext.getCmp('EPLDD13SW_YearCombo').getValue();
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
				current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispDop13";
			EvnPLDispDop13_grid.getStore().removeAll();
			EvnPLDispDop13_grid.getStore().baseParams = params;
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
		var grid = this.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispDop13SearchFilterForm');
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
					current_window.findById('EPLDD13SW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});*/
	},
	height: 550,
	id: 'EvnPLDispDop13SearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDD13SW_SearchButton');
	},
	printCost: function() {
		var grid = this.EvnPLDispDop13SearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispDop13_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispDop13_id'),
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
			if (selected_record && selected_record.get('EvnPLDispDop13_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispDop13_IsEndStage') != lang['da']);
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
		var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		var stringfields;

		if ( isUfa == true ) {
			stringfields = [
				{ name: 'id', type: 'string', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnPLDispDop13_id', type: 'int', hidden: true },
				{ name: 'EvnPLDispDop13_IsTransit', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'AccessType_Code', type: 'int', hidden: true },
				{ name: 'Person_Surname', renderer:addTooltip, header: lang['familiya'], width: 150 },
				{ name: 'Person_Firname', renderer:addTooltip, header: lang['imya'], width: 150 },
				{ name: 'Person_Secname', renderer:addTooltip, header: lang['otchestvo'], width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo_provedeniya'], hidden:!(isPskov || isUfa)},
				{ name: 'EvnPLDispDop13_IsMobile', type: 'string', header: lang['obslujen_mobilnoy_brigadoy'] },
				{ name: 'EvnPLDispDop13_rejDate', type: 'date', format: 'd.m.Y', header: lang['data_otkaza_ot_dispanserizatsii'] },
				{ name: 'EvnPLDispDop13_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala_1_etapa'] },
				{ name: 'EvnPLDispDop13_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya_1_etapa'] },
				{ name: 'EvnPLDispDop13_IsEndStage', type: 'string', header: lang['1_etap_zakonchen'] },
				{ name: 'EvnPLDispDop13_HealthKind_Name', type: 'string', header: lang['gruppa_zdorovya_1_etap'] },
				{ name: 'EvnPLDispDop13Second_napDate', type: 'date', format: 'd.m.Y', header: lang['data_napravleniya_na_2_etap'] },
				{ name: 'EvnPLDispDop13Second_rejDate', type: 'date', format: 'd.m.Y', header: lang['data_otkaza_ot_2_etapa'] },
				{ name: 'EvnPLDispDop13Second_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala_2_etapa'] },
				{ name: 'EvnPLDispDop13Second_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya_2_etapa'] },
				{ name: 'EvnPLDispDop13Second_IsEndStage', type: 'string', header: lang['2_etap_zakonchen'] },
				{ name: 'EvnPLDispDop13Second_HealthKind_Name', type: 'string', header: lang['gruppa_zdorovya_2_etap'] },
				{ name: 'ua_name', type: 'string', header: lang['adres_registratsii'] },
				{ name: 'pa_name', type: 'string', header: lang['adres_projivaniya'] },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: lang['data_vyidachi_spravki_otkaza'], width: 150 },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: lang['spravka_o_stoimosti_lecheniya'], width: 150 }
			];
		}
		else {
			stringfields = [
				{ name: 'id', type: 'string', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnPLDispDop13_id', type: 'int', hidden: true },
				{ name: 'EvnPLDispDop13_IsTransit', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'AccessType_Code', type: 'int', hidden: true },
				{ name: 'UslugaComplex_Name', type: 'string', hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]), header: langs('Услуга диспансеризации'), width: 150 },
				{ name: 'Person_Surname', renderer:addTooltip, header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', renderer:addTooltip, header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', renderer:addTooltip, header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
				{ name: 'Lpu_Nick', type: 'string', header: langs('МО проведения'), hidden:!(isPskov || isUfa)},
				{ name: 'ua_name', type: 'string', header: langs('Адрес регистрации') },
				{ name: 'pa_name', type: 'string', header: langs('Адрес проживания') },
				{ name: 'EvnPLDispDop13_IsMobile', type: 'string', header: langs('Обслужен мобильной бригадой') },
				{ name: 'EvnPLDispDop13_rejDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа от диспансеризации') },
				{ name: 'EvnPLDispDop13_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала 1 этапа') },
				{ name: 'EvnPLDispDop13_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания 1 этапа') },
				{ name: 'EvnPLDispDop13_IsEndStage', type: 'string', header: langs('1 этап закончен') },
				{ name: 'EvnPLDispDop13_HealthKind_Name', type: 'string', header: langs('Группа здоровья 1 этап') },
				{ name: 'EvnPLDispDop13Second_napDate', type: 'date', format: 'd.m.Y', header: langs('Дата направления на 2 этап') },
				{ name: 'EvnPLDispDop13Second_rejDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа от 2 этапа') },
				{ name: 'EvnPLDispDop13Second_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала 2 этапа') },
				{ name: 'EvnPLDispDop13Second_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания 2 этапа') },
				{ name: 'EvnPLDispDop13Second_IsEndStage', type: 'string', header: langs('2 этап закончен') },
				{ name: 'EvnPLDispDop13Second_HealthKind_Name', type: 'string', header: langs('Группа здоровья 2 этап') },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', hidden: ! getRegionNick().inlist(['perm', 'kz']), header: langs('Справка о стоимости лечения'), width: 150 }
			];
		}

		win.EvnPLDispDop13SearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispDop13SearchWindow').openEvnPLDDEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispDop13SearchWindow').openEvnPLDDEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispDop13SearchWindow').deleteEvnPLDD(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispDop13SearchWindow').refreshEvnPLDDList(); } },
				{ name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
				}},
				{
					hidden: false,
					name:'action_printpassport',
					tooltip: lang['napechatat_pasport_zdorovya'],
					icon: 'img/icons/print16.png',
					handler: function() {
						var grid = Ext.getCmp('EPLDD13SW_EvnPLDispDop13SearchGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();							
						if (!record) {
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
							return false;
						}
						var evn_pl_id = record.get('EvnPLDispDop13_id');
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
				if(win.viewOnly==true){
					win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', false);
					win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', true);
					win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);
				}
				else
				{
					if (record.get('AccessType_Code') != 0) {
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', true);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);
					} else {
						var disabled = false;
						if (getGlobalOptions().archive_database_enable) {
							disabled = disabled || (record.get('archiveRecord') == 1);
						}
						if ( !Ext.isEmpty(record.get('EvnPLDispDop13_id')) ) {
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', disabled);
						} else {
							win.EvnPLDispDop13SearchGrid.setActionDisabled('action_delete', true);	
						}
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_view', false);
						win.EvnPLDispDop13SearchGrid.setActionDisabled('action_edit', disabled);
					}

					if ( record.get('EvnPLDispDop13_id') && record.get('AccessType_Code') == 0 ) {
						this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLDispDop13_IsTransit') == 1));
					}
					else {
						this.setActionDisabled('action_setevnistransit', true);
					}
				}
				win.checkPrintCost();
			},
			auditOptions: {
				field: 'EvnPLDispDop13_id',
				key: 'EvnPLDispDop13_id'
			},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=SearchEvnPLDispDop13&m=searchData',
			focusOn: {
				name: 'EPLDD13SW_SearchButton', type: 'field'
			},
			id: 'EPLDD13SW_EvnPLDispDop13SearchGrid',
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
			stringfields: stringfields
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
				id: 'EPLDD13SW_SearchFilterPanel',
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
								id: 'EPLDD13SW_YearCombo',
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
											var current_window = Ext.getCmp('EvnPLDispDop13SearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispDop13SearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDD13SW_EvnPLDispDop13SearchGrid').focus();													
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDD13SW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									},
									select: function (field, value, eOpts) {
										let oldValue = String(field.startValue);
										let newValue = String(value.id);
										
										// Update oldValue.
										field.startValue = newValue;
										
										if (oldValue !== newValue) {
											// Change.
											let year = newValue;
											let isRegion = ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'].indexOf(getRegionNick()) === -1;
											let isYear = year >= 2020;
											let isShow = isRegion && isYear;
											
											let base_form = Ext.getCmp('EvnPLDispDop13SearchFilterForm').getForm();
											let fieldPersonIsNotDispDopOnTime;
											
											if (typeof base_form != 'undefined') {
												fieldPersonIsNotDispDopOnTime = base_form.findField('Person_isNotDispDopOnTime');
											}
											
											if (typeof fieldPersonIsNotDispDopOnTime != 'undefined') {
												fieldPersonIsNotDispDopOnTime.setContainerVisible(isShow);
											}
										}
									}
								},
								tabIndex: TABINDEX_EPLDD13SW+56
							}]
						}/*, {
							width: 400,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDD13SW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDD13SW+57
							}]
						}*/]
					},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispDop13SearchFilterForm',
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
						searchFormType: 'EvnPLDispDop13',
						tabIndexBase: TABINDEX_EPLDD13SW,
						tabPanelId: 'EPLDD13SW_SearchFilterTabbar',
						tabGridId: 'EPLDD13SW_EvnPLDispDop13SearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 200,
							id: 'EPLDD13_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispDop13SearchFilterForm');
									form.getForm().findField('EvnPLDispDop13_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: '<u>6</u>. Диспансеризация 1 этап',
							items: [{
								hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
								layout: 'form',
								border: false,
								items: [{
									hiddenName: 'UslugaComplex_id',
									width: 400,
									fieldLabel: lang['usluga_dispanserizatsii'],
									dispOnly: true,
									DispClass_id: 1,
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
										name: 'EvnPLDispDop13_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDD13SW + 59,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispDop13_setDate_Range',
										tabIndex: TABINDEX_EPLDD13SW + 60,
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
										name: 'EvnPLDispDop13_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDD13SW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispDop13_disDate_Range',
										tabIndex: TABINDEX_EPLDD13SW + 62,
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
										fieldLabel: lang['otkaz_ot_dispanserizatsii'],
										hiddenName: 'EvnPLDispDop13_IsRefusal',
										tabIndex: TABINDEX_EPLDD13SW + 66,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										allowBlank: true,
										enableKeyEvents: true,
										fieldLabel: lang['gruppa_zdorovya'],
										loadParams: (getRegionNick() == 'penza' ? {params: {where: 'where HealthKind_Code in (1,2,6,7)'}} : null),
										hiddenName: 'EvnPLDispDop13_HealthKind_id',
										tabIndex: TABINDEX_EPLDD13SW + 66,
										validateOnBlur: false,
										width: 100,
										xtype: 'swhealthkindcombo'
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
										fieldLabel: lang['napravlen_na_2_etap'],
										hiddenName: 'EvnPLDispDop13_IsTwoStage',
										tabIndex: TABINDEX_EPLDD13SW + 66,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['sluchay_oplachen'],
										tabIndex: TABINDEX_EPLDD13SW + 66,
										hiddenName: 'EvnPLDispDop13_isPaid',
										width: 100,
										listeners: {
											'keydown': function(combo, e) {
												if ( !e.shiftKey && e.getKey() == e.TAB )
												{
													Ext.TaskMgr.start({
														run : function() {
															Ext.TaskMgr.stopAll();
															Ext.getCmp('EPLDD13SW_EvnPLDispDop13SearchGrid').focus();													
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
										fieldLabel: lang['1_etap_zakonchen'],
										hiddenName: 'EvnPLDispDop13_IsFinish',
										tabIndex: TABINDEX_EPLDD13SW + 66,
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['obslujen_mobilnoy_brigadoy'],
										hiddenName: 'EvnPLDispDop13_isMobile',
										width: 100,
										xtype: 'swyesnocombo'
									}]
								}]
							}, {
								fieldLabel: langs('Записан ФЭР'),
								name: 'FarRegistered',
								xtype: 'checkbox'
							}, {
								fieldLabel: 'Не проходившие в установленные сроки',
								name: 'Person_isNotDispDopOnTime',
								xtype: 'checkbox',
								listeners: {
									render: function () {
										let date = new Date();
										let year = date.getFullYear();
										let isRegion = ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'].indexOf(getRegionNick()) === -1;
										let isYear = year >= 2020;
										let isShow = isRegion && isYear;
										
										// Hide container ".x-form-item" with field.
										this.setContainerVisible(isShow);
									},
									hide: function () {
										let form = this.findForm();
										let ownerWindow;
										
										this.setValue(false);

										if (typeof form != 'undefined') {
											ownerWindow = form.getOwnerWindow();
										}

										if (typeof ownerWindow != 'undefined') {
											// Updated Layout.
											setTimeout(function () {
												ownerWindow.doLayout();
											}, 10);
										}
									},
									show: function () {
										let form = this.findForm();
										let ownerWindow;

										if (typeof form != 'undefined') {
											ownerWindow = form.getOwnerWindow();
										}

										if (typeof ownerWindow != 'undefined') {
											// Updated Layout.
											setTimeout(function () {
												ownerWindow.doLayout();
											}, 10);
										}
									}
								}
							}, {
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
				id: 'EPLDD13SW_SearchButton',
				tabIndex: TABINDEX_EPLDD13SW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDD13SW+91,
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
				tabIndex: TABINDEX_EPLDD13SW+92,
				text: "Режим потокового ввода"
			},/* {
				handler: function() {
					var base_form = this.findById('EvnPLDispDop13SearchFilterForm').getForm();
					var filter_form = this.findById('EvnPLDispDop13SearchFilterForm');

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
				tabIndex: TABINDEX_EPLDD13SW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDD13SW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					this.buttons[0].focus(true, 200);
				}.createDelegate(this)
			}
			]
		});
		sw.Promed.swEvnPLDispDop13SearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispDop13SearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDD13SW_SearchFilterTabbar');

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
		var years_combo = this.findById('EPLDD13SW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 1
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
	openEvnPLDDEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').ViewGridPanel;

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
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;
		var Year = this.findById('EPLDD13SW_YearCombo').getValue();

		if (/*EvnPLDispDop13_id > 0 &&*/ person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				DispClass_id: 1,
				Year: Year,
				EvnPLDispDop13_id: EvnPLDispDop13_id,
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
		var EvnPLDispDop13_grid = current_window.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').ViewGridPanel;
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
		this.setTitle(WND_POL_EPLDD13SEARCH);
		Ext.getCmp('EvnPLDispDop13SearchFilterForm').setHeight(280);
		this.findById('EvnPLDispDop13SearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDD13SW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDD13SW_YearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EvnPLDispDop13SearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDD13SW_EvnPLDispDop13SearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDD13STREAM);
		this.findById('EvnPLDispDop13SearchFilterForm').hide();
		Ext.getCmp('EvnPLDispDop13SearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDD13SW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDD13SW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispDop13SearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDD13SW_SearchFilterTabbar').setActiveTab(2);
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

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
		this.findById('EPLDD13SW_SearchFilterTabbar').setActiveTab(2);

		var win = this;
		var form = this.findById('EvnPLDispDop13SearchFilterForm');
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
			if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) //https://redmine.swan.perm.ru/issues/78988
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
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa']))
					return false;
				else
					return true;
			}
		);
		base_form.findField('LpuAttachType_id').setValue(1);
		//base_form.findField('LpuRegionType_id').setValue(1); //http://redmine.swan.perm.ru/issues/22880
		
		base_form.findField('EvnPLDispDop13_HealthKind_id').getStore().clearFilter();
		
		this.loadYearsCombo();

		var UslugaCombo = base_form.findField('EvnPLDisp_UslugaComplex');
		UslugaCombo.getStore().removeAll();
		UslugaCombo.getStore().baseParams = {
			DispClass_id: 1
		};
		UslugaCombo.getStore().load();
	},
	title: WND_POL_EPLDD13SEARCH,
	width: 800
});
