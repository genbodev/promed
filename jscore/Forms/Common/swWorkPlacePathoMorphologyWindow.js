/**
* АРМ врача патологоанатомического бюро
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/
sw.Promed.swWorkPlacePathoMorphologyWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: true,
	buttonPanelActions: {
		action_Proto: {
			disabled: false, 
			iconCls : 'appointments32',
			menu: new Ext.menu.Menu({
				items: [{
					text: lang['protokolyi_patologogistologicheskih_issledovaniy'],
					tooltip: lang['jurnal_protokolov_patologogistologicheskih_issledovaniy'],
					iconCls : 'pathohistproto16',
					handler: function() {
						getWnd('swEvnHistologicProtoViewWindow').show();
					},
					hidden: false
				}, {
					text: lang['protokolyi_patomorfogistologicheskih_issledovaniy'],
					tooltip: lang['jurnal_protokolov_patomorfogistologicheskih_issledovaniy'],
					iconCls : 'pathomorph16',
					handler: function() {
						getWnd('swEvnMorfoHistologicProtoViewWindow').show();
					},
					hidden: false
				}]
					// Указание экшенов почему-то взрывает форму
					// sw.Promed.Actions.EvnHistologicProtoViewAction
					// ,sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
			}),
			menuAlign: 'tr',
			nn: 'action_Proto',
			text: lang['protokolyi'],
			tooltip: lang['protokolyi']
		},
		action_Svid: {
			disabled: false,
			iconCls : 'medsvid32',
			hidden: !isMedSvidAccess(),
			menu: new Ext.menu.Menu({
				items: [
				{
					text: lang['svidetelstva_o_smerti'],
					tooltip: lang['svidetelstva_o_smerti'],
					iconCls : 'svid-death16',
					handler: function() {
						getWnd('swMedSvidDeathStreamWindow').show({fromPatMorphArm:true});
					},
					hidden: false
				}, {
					text: lang['svidetelstva_o_perinatalnoy_smerti'],
					tooltip: lang['svidetelstva_o_perinatalnoy_smerti'],
					iconCls : 'svid-pdeath16',
					handler: function() {
						getWnd('swMedSvidPntDeathStreamWindow').show({fromPatMorphArm:true});
					},
					hidden: false
				}, {
					text: lang['pechat_blankov_svidetelstv'],
					tooltip: lang['pechat_blankov_svidetelstv'],
					iconCls : 'svid-blank16',
					handler: function() {
						getWnd('swMedSvidSelectSvidType').show({fromPatMorphArm:true});
					},
					hidden: false
				}]
			}),
			menuAlign: 'tr',
			nn: 'action_svid',
			text: lang['svidetelstva'],
			tooltip: lang['svidetelstva']
		},
		action_Journal: {
			text: lang['jurnal_registratsii_postupleniya_i_vydachi_tel_umershih'],
			tooltip: lang['registratsiya_tel_umershih'],
			iconCls: 'worksheets32',
			handler: function () {
				getWnd('swRegistryDeadBodiesWindow').show();
			},
		}
	},
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	id: 'swWorkPlacePathoMorphologyWindow',
	openEvnProtoEditWindow: function(action) {
		if ( !action || !action.inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel.getGrid();
		var params = new Object();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var dir_type = parseInt(selected_record.get('DirectionType_id'));

		if ( !dir_type || !dir_type.toString().inlist([ '1', '2' ]) ) {
			return false;
		}

		switch ( dir_type ) {
			case 1:
				if ( getWnd('swEvnHistologicProtoEditWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_protokola_patologogistologicheskogo_issledovaniya_uje_otkryito']);
					return false;
				}
			break;

			case 2:
				if ( getWnd('swEvnMorfoHistologicProtoEditWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_protokola_patomorfogistologicheskogo_issledovaniya_uje_otkryito']);
					return false;
				}
			break;
		}

		formParams.Person_id = selected_record.get('Person_id');
		formParams.PersonEvn_id = selected_record.get('PersonEvn_id');
		formParams.Server_id = selected_record.get('Server_id');


		if ( action == 'add' ) {
			if ( !selected_record.get('EvnDirection_id') ) {
				return false;
			}
			else if ( selected_record.get('EvnProto_id') ) {
				return false;
			}

			switch ( dir_type ) {
				case 1:
					formParams.EvnDirectionHistologic_id = selected_record.get('EvnDirection_id');
					formParams.EvnDirectionHistologic_SerNum = selected_record.get('EvnDirection_Ser') + ' ' + selected_record.get('EvnDirection_Num') + ', ' + Ext.util.Format.date(selected_record.get('EvnDirection_setDate'), 'd.m.Y');
					formParams.EvnHistologicProto_id = 0;
				break;

				case 2:
					formParams.EvnDirectionMorfoHistologic_id = selected_record.get('EvnDirection_id');
					formParams.EvnDirectionMorfoHistologic_SerNum = selected_record.get('EvnDirection_Ser') + ' ' + selected_record.get('EvnDirection_Num') + ', ' + Ext.util.Format.date(selected_record.get('EvnDirection_setDate'), 'd.m.Y');
					formParams.EvnMorfoHistologicProto_id = 0;
				break;
			}
		}
		else {
			switch ( dir_type ) {
				case 1:
					formParams.EvnHistologicProto_id = selected_record.get('EvnProto_id');
				break;

				case 2:
					formParams.EvnMorfoHistologicProto_id = selected_record.get('EvnProto_id');
				break;
			}
		}

		if (action === 'edit' && !checkPathoMorphEditable(selected_record)){
			action = 'view'
		}

		params.action = action;
		params.callback = function() {
			if ( action == 'add' ) {
				//selected_record.set('Proto_Exists', 'true');
				//selected_record.commit();
				grid.getStore().reload();
			}
		};
		params.formParams = formParams;
		params.onHide = function() {
			var index = grid.getStore().indexOf(selected_record);
			if ( index >= 0 ) {
				grid.getView().focusRow(index);
			}
		};

		switch ( dir_type ) {
			case 1:
				getWnd('swEvnHistologicProtoEditWindow').show(params);
			break;

			case 2:
				getWnd('swEvnMorfoHistologicProtoEditWindow').show(params);
			break;
		}
	},
	openPersonEmkWindow: function() {
		var grid = this.GridPanel.getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['spisok_napravleniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirection_id') ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		var isMyOwnRecord = false;
		var record = grid.getSelectionModel().getSelected();

		if ( record.get('pmUser_insID') == getGlobalOptions().pmuser_id ) {
			isMyOwnRecord = true;
		}
		
		getWnd('swPersonEmkWindow').show({
			ARMType: 'common', // this.ARMType,
			isMyOwnRecord: isMyOwnRecord,
			mode: 'workplace',
			Person_id: record.get('Person_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			Server_id: record.get('Server_id'),
			// TimetableGraf_id: record.get('TimetableGraf_id'),
			userMedStaffFact: this.userMedStaffFact
		});
	},
	setIsBad: function(flag, cause) {
		var grid = this.GridPanel.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var dir_type = selected_record.get('DirectionType_id');
		var id = selected_record.get('EvnDirection_id');

		if ( !id || !dir_type || !dir_type.toString().inlist([ '1', '2' ]) ) {
			return false;
		};

		if ( flag == true && Number(selected_record.get('EvnDirection_IsBad')) == 1 ) {
			return false;
		}
		else if ( !flag && Number(selected_record.get('EvnDirection_IsBad')) != 1 ) {
			return false;
		}

		this.getLoadMask().show();

		var params = new Object();
		var url = '';

		switch ( parseInt(dir_type) ) {
			case 1:
				params.EvnDirectionHistologic_id = id;
				params.EvnDirectionHistologic_IsBad = (flag == true ? 1 : 0);
				url = '/?c=EvnDirectionHistologic&m=setEvnDirectionHistologicIsBad';
			break;

			case 2:
				params.EvnDirectionMorfoHistologic_id = id;
				params.EvnDirectionMorfoHistologic_IsBad = (flag == true ? 1 : 0);
				url = '/?c=EvnDirectionMorfoHistologic&m=setEvnDirectionMorfoHistologicIsBad';
			break;
		}
		if(cause) {
			params.EvnStatusCause_id = cause.EvnStatusCause_id;
			params.EvnStatusHistory_Cause = cause.EvnStatusHistory_Cause;
		}
		Ext.Ajax.request({
			failure: function(response, options) {
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + ' ' + (parseInt(dir_type) == 1 ? lang['patologogistologicheskogo'] : lang['patomorfogistologicheskogo']) + lang['napravleniya_[tip_oshibki_2]']);
			}.createDelegate(this),
			params: params,
			success: function(response, options) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + ' ' + (parseInt(dir_type) == 1 ? lang['patologogistologicheskogo'] : lang['patomorfogistologicheskogo']) + lang['napravleniya_[tip_oshibki_3]']);
				}
				else {
					grid.getStore().reload();
				}
			}.createDelegate(this),
			url: url
		});
	},
	addDeathSvid: function() {
		var grid = this.GridPanel.getGrid(),
			params = {};

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['spisok_napravleniy_ne_nayden']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_chelovek']);
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		params.Person_id = record.get('Person_id');
		params.PersonEvn_id = record.get('PersonEvn_id');
		params.Server_id = record.get('Server_id');

		getWnd('swMedSvidDeathEditWindow').show({
			action: 'add',
			formParams: params
		});
	},
	showDirection: function() {
		var selectedRecord = this.GridPanel.getGrid().getSelectionModel().getSelected();

		var EvnDirection_id = selectedRecord.get('EvnDirection_id');
		var DirectionType_id = selectedRecord.get('DirectionType_id');
		var params = {
			action: 'view',
			formParams:{
				Person_id: selectedRecord.get('Person_id'),
				Server_id: selectedRecord.get('Server_id')
			}
		};

		switch(DirectionType_id) {
			case 1:
				params.formParams.EvnDirectionHistologic_id = EvnDirection_id;

				getWnd('swEvnDirectionHistologicEditWindow').show(params);
				break;
			case 2:
				params.formParams.EvnDirectionMorfoHistologic_id = EvnDirection_id;

				getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);
				break;
		}
	},

	externalDirectionPersonSearchWindowOpen: function() {
		var params = {
			allowUnknownPerson: true,
			searchMode: 'all',
			onSelect: function(person_data) {
				paramMorfoHistologicEditWindow = {};
				paramMorfoHistologicEditWindow.formParams = {};
				paramMorfoHistologicEditWindow.action = 'add';
				paramMorfoHistologicEditWindow.outer = true;
				paramMorfoHistologicEditWindow.formParams.Person_id =  person_data.Person_id;
				paramMorfoHistologicEditWindow.formParams.PersonEvn_id = person_data.PersonEvn_id;
				paramMorfoHistologicEditWindow.formParams.Server_id = person_data.Server_id;
				paramMorfoHistologicEditWindow.callback = function() {
					var pat_grid = Ext.getCmp('WorkPlacePathoMorphologyGridPanel');
					if (pat_grid && pat_grid.ViewGridStore) {
						pat_grid.ViewGridStore.reload();
					}
				};

				getWnd('swPersonSearchWindow').hide();
				getWnd('swEvnDirectionMorfoHistologicEditWindow').show(paramMorfoHistologicEditWindow);
			}
		};

		getWnd('swPersonSearchWindow').show(params);
	},

	
	showCorpseRecieptForm: function (action) {
		var selectedRecord = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var params = {
			action: action,
			CorpseGiveaway_Date: selectedRecord.get('CorpseGiveaway_Date'),
			EvnMorfoHistologicProto_autopsyDate: selectedRecord.get('EvnMorfoHistologicProto_autopsyDate'),
			EvnMorfoHistologicProto_deathDate: selectedRecord.get('EvnMorfoHistologicProto_deathDate'),
			formParams: {
				MorfoHistologicCorpseReciept_id: (action !== 'add') ? selectedRecord.get('CorpseReciept_Id') : 0,
				EvnDirectionMorfoHistologic_id: selectedRecord.get('EvnDirection_id')
			}
		};
		getWnd('swMorfoHistologicCorpseRecieptWindow').show(params);
	},
	
	deleteMorfoHistologicCorpseRecieptRequest: function() {
		var win = this,
			grid = win.GridPanel.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if (record.get('Proto_Exists') === 'true' || record.get('CorpseGiveaway_Exists')) {
			
			var msg = lang['po_dannomy_telu_umershego_ukazany_svedeniya_o'];
			
			if (record.get('Proto_Exists') === 'true') {
				msg += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + lang['protokol_patomorfogistologicheskogo_issledovaniya'] + ': ';
				msg += record.get('EvnDirection_Ser') + ' ' + record.get('EvnDirection_Num') + ', ' + Ext.util.Format.date(record.get('EvnDirection_setDate'), 'd.m.Y');
				msg += '<br/>';
			}
			if (record.get('CorpseGiveaway_Exists')) {
				msg += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + lang['data_vydachi_tela'] + ': ' + Ext.util.Format.date(record.get('CorpseGiveaway_Date'), 'd.m.Y') + '<br/>';
			}
			
			msg += lang['udalit_svedeniya_o_prinyatii_tela_nevozmozhno'];
			
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function(buttonId) {},
					icon: Ext.Msg.WARNING,
					msg: msg,
					title: lang['oshibka']
				});
		}
		else {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if (buttonId == 'yes') {
							win.getLoadMask().show();
							Ext.Ajax.request({
								url: '/?c=MorfoHistologicCorpseReciept&m=deleteMorfoHistologicCorpseReciept',
								params: {MorfoHistologicCorpseReciept_id: record.get('CorpseReciept_Id')},
								failure: function (response, options) {
									win.getLoadMask().hide();
									sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_2]']);
								}.createDelegate(this),
								success: function (response, options) {
									win.getLoadMask().hide();

									var response_obj = Ext.util.JSON.decode(response.responseText);

									if (response_obj.success == false) {
										sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_3]']);
									} else {
										grid.getStore().reload();
									}
								}
							});
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: lang['udalit_svedeniya_o_prinyatii_tela'] + '?',
					title: lang['vopros']
				});
		}
	},

	showCorpseGiveawayForm: function (action) {
		var selectedRecord = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var params = {
			action: action,
			CorpseReciept_Date: selectedRecord.get('CorpseReciept_Date'),
			EvnMorfoHistologicProto_autopsyDate: selectedRecord.get('EvnMorfoHistologicProto_autopsyDate'),
			formParams: {
				MorfoHistologicCorpseGiveaway_id: (action !== 'add') ? selectedRecord.get('CorpseGiveaway_Id') : 0,
				EvnDirectionMorfoHistologic_id: selectedRecord.get('EvnDirection_id'),
			}
		};
		getWnd('swMorfoHistologicCorpseGiveawayWindow').show(params);
	},

	deleteMorfoHistologicCorpseGiveawayRequest: function () {
		var win = this,
			grid = win.GridPanel.getGrid(),
			record = grid.getSelectionModel().getSelected();

		sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						win.getLoadMask().show();
						Ext.Ajax.request({
							url: '/?c=MorfoHistologicCorpseGiveaway&m=deleteMorfoHistologicCorpseGiveaway',
							params: {MorfoHistologicCorpseGiveaway_id: record.get('CorpseGiveaway_Id')},
							failure: function (response, options) {
								win.getLoadMask().hide();
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_2]']);
							}.createDelegate(this),
							success: function (response, options) {
								win.getLoadMask().hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_3]']);
								} else {
									grid.getStore().reload();
								}
							}
						});
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: lang['udalit_svedeniya_o_vydache_tela'] + '?',
				title: lang['vopros']
			});
	},
	
	showRefuseForm: function (action) {
		var selectedRecord = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var params = {
			action: action,
			formParams: {
				MorfoHistologicRefuse_id: (action !== 'add') ? selectedRecord.get('MorfoHistologicRefuse_Id') : 0,
				EvnDirectionMorfoHistologic_id: selectedRecord.get('EvnDirection_id')
			}
		};
		getWnd('swMorfoHistologicRefuseWindow').show(params);
	},

	deleteMorfoHistologicRefuseRequest: function() {
		var win = this,
			grid = win.GridPanel.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if (record.get('CorpseGiveaway_Exists')) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function(buttonId) {},
					icon: Ext.Msg.WARNING,
					msg: lang['po_dannomy_telu_umershego_ukazany_svedeniya_o_vydache_tela'],
					title: lang['oshibka']
				});
		}
		else {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if (buttonId == 'yes') {
							win.getLoadMask().show();
							Ext.Ajax.request({
								url: '/?c=MorfoHistologicRefuse&m=deleteMorfoHistologicRefuse',
								params: { MorfoHistologicRefuse_id: record.get('MorfoHistologicRefuse_Id')},
								failure: function (response, options) {
									win.getLoadMask().hide();
									sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_2]']);
								}.createDelegate(this),
								success: function (response, options) {
									win.getLoadMask().hide();

									var response_obj = Ext.util.JSON.decode(response.responseText);

									if (response_obj.success == false) {
										sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_svedeniy_voznikli_oshibki_[tip_oshibki_3]']);
									} else {
										grid.getStore().reload();
									}
								}
							});
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: lang['udalit_otkaz'] + '?',
					title: lang['vopros']
				});
		}
	},
	
	show: function() {
		var win = this;
		sw.Promed.swWorkPlacePathoMorphologyWindow.superclass.show.apply(this, arguments);

		if ( !this.GridPanel.getAction('external_direction') ) {
			this.GridPanel.addActions({
				handler: function() {
					this.externalDirectionPersonSearchWindowOpen();
				}.createDelegate(this),
				name: 'external_direction',
				text: 'Внешнее направление'
			});
		} else {
			this.GridPanel.ViewActions.external_direction.setDisabled(true);
		}


		if (!this.GridPanel.getAction('action_refuse')) {
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_refuse',
				text: lang['otkaz_ot_vskryitiya'],
				menu: this.RefuseMenu,
			});
		}

		if (!this.GridPanel.getAction('action_corpse_giveaway')) {
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_corpse_giveaway',
				text: lang['vydacha_tela'],
				menu: this.CorpseGiveawayMenu,
			});
		}
		
		if (!this.GridPanel.getAction('action_corpse_reciept')) {
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_corpse_reciept',
				text: lang['postuplenie_tela'],
				menu: this.CorpseRecieptMenu,
			});
		}
		
		if ( !this.GridPanel.getAction('action_showdirection') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.showDirection();
				}.createDelegate(this),
				name: 'action_showdirection',
				text: 'Просмотр направления'
			});
		} else {
			this.GridPanel.ViewActions.action_showdirection.setDisabled(true);
		}

		if ( !this.GridPanel.getAction('action_deathsvid') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.addDeathSvid();
				}.createDelegate(this),
				name: 'action_deathsvid',
				text: lang['svidetelstvo_o_smerti']
			});
		}

		if ( !this.GridPanel.getAction('action_isnotbad') ) {
			this.GridPanel.addActions({
				disabled: true,
				hidden: true,
				handler: function() {
					this.setIsBad(false);
				}.createDelegate(this),
				name: 'action_isnotbad',
				text: lang['otmenit_annulirovanie']
			});
		}

		if ( !this.GridPanel.getAction('action_isbad') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					var selected_record = this.GridPanel.getGrid().getSelectionModel().getSelected();

					getWnd('swSelectEvnStatusCauseWindow').show({
						Evn_id: selected_record.get('EvnDirection_id'),
						EvnClass_id: 27, //Выписка направлений
						callback(values) {
							win.setIsBad(true, values);
						}
					});
	
				}.createDelegate(this),
				name: 'action_isbad',
				text: lang['annulirovat']
			});
		}

		if ( !this.GridPanel.getAction('action_proto_view') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.openEvnProtoEditWindow('edit');
				}.createDelegate(this),
				name: 'action_proto_view',
				text: lang['otkryit_protokol']
			});
		}

		if ( !this.GridPanel.getAction('action_proto_add') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.openEvnProtoEditWindow('add');
				}.createDelegate(this),
				name: 'action_proto_add',
				text: lang['dobavit_protokol']
			});
		}

		if ( !this.GridPanel.getAction('open_emk') ) {
			this.GridPanel.addActions({
				disabled: true,
				handler: function() {
					this.openPersonEmkWindow();
				}.createDelegate(this),
				name: 'open_emk',
				text: lang['otkryit_emk'],
				tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta']
			});
		}
	},
	initComponent: function() {
		var form = this;
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			labelWidth: 120,
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank: true,
							codeField: 'DirectionType_Code',
							displayField: 'DirectionType_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: lang['tip_napravleniya'],
							hiddenName: 'DirectionType_id',
							hideEmptyRow: false,
							ignoreIsEmpty: true,
							listeners: {
								'keydown': form.onKeyDown,
								'select': function (combo, record) {
									if (record.get('DirectionType_Name') == 'Патологогистологическое') {
										form.FilterPanel.getForm().findField('Search_CorpseRecieptDate').setValue('');
										form.FilterPanel.getForm().findField('Search_CorpseRecieptDate').disable();
										form.FilterPanel.getForm().findField('Search_CorpseGiveawayDate').setValue('');
										form.FilterPanel.getForm().findField('Search_CorpseGiveawayDate').disable();
									}
									else {
										form.FilterPanel.getForm().findField('Search_CorpseRecieptDate').enable();
										form.FilterPanel.getForm().findField('Search_CorpseGiveawayDate').enable();
									}
								}.createDelegate(this)
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['patologogistologicheskoe'] ],
									[ 2, 2, lang['patomorfogistologicheskoe'] ]
								],
								fields: [
									{ name: 'DirectionType_id', type: 'int'},
									{ name: 'DirectionType_Code', type: 'int'},
									{ name: 'DirectionType_Name', type: 'string'}
								],
								key: 'DirectionType_id',
								sortInfo: { field: 'DirectionType_Code' }
							}),
							tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{DirectionType_Code}</font>&nbsp;{DirectionType_Name}',
									'</div></tpl>'
							),
							valueField: 'DirectionType_id',
							width: 200,
							xtype: 'swbaselocalcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['seriya'],
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'EvnDirection_Ser',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['nomer'],
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'EvnDirection_Num',
							width: 120,
							xtype: 'textfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 200,
							name: 'Search_SurName',
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_FirName',
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_SecName',
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: lang['dr'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 150,
						items: [{
							xtype: 'daterangefield',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 160,
							name: 'Search_CorpseRecieptDate',
							fieldLabel: lang['data_postupleniya_tela'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 150,
						items: [{
							xtype: 'daterangefield',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							width: 160,
							name: 'Search_CorpseGiveawayDate',
							fieldLabel: lang['data_vydachi_tela'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				},{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: form.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								form.doReset();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true},
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=EvnDirection&m=loadPathoMorphologyWorkPlace',
			grouping: true,
			groupingView: {
				showGroupName: false,
				showGroupsText: true
			},
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			id: 'WorkPlacePathoMorphologyGridPanel',
			onDblClick: function(grid, rowIdx, colIdx, event) {
                /*alert('dfdfdf');
                log(grid);
                log(grid.getSelectionModel().getSelected());
                log(grid.getSelectionModel().getSelected().get('Proto_Exists'));
                alert('fff');*/
				var record = grid.getSelectionModel().getSelected();
				
				if (record.get('DirectionType_Name') === 'Патоморфогистологическое') {
					if (!record.get('CorpseReciept_Exists')) {
						form.showCorpseRecieptForm('add');
					}
					else {
						if (record.get('Proto_Exists') === 'false' && record.get('Refuse_Exists') === 'false') {
							form.openEvnProtoEditWindow('add');
						} else if (record.get('Refuse_Exists') === 'true') {
							sw.swMsg.alert(lang['soobschenie'], lang['dlya_umershego_zaveden_otkaz_ot_vskryitiya_sozdanie_protokola_nevozmozhno']);
							return false;
						}
						else {
							form.openEvnProtoEditWindow('edit');
						}
					}
				}
				else {
					if(grid.getSelectionModel().getSelected().get('Proto_Exists')=='true')
					{
						form.openEvnProtoEditWindow('edit');
					}
					else
					{
						form.openEvnProtoEditWindow('add');
					}
				}
				//this.onEnter();
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
				else {
					this.ViewActions.action_view.execute();
				}
			},
			onLoadData: function(sm, index, record) {/*
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			*/},
			onRowSelect: function(sm, index, record) {
				
				var disallowEdit = !checkPathoMorphEditable(record),
				disallowOpenEmk = !record.get('Person_id'),
				disallowProtoView = !record.get('EvnProto_id'),
				disallowIsNotBad = !(Number(record.get('EvnDirection_IsBad')) == 1),
				disallowIsBad = (Number(record.get('EvnDirection_IsBad')) == 1) || !record.get('EvnDirection_id'),
				disallowProtoAdd = record.get('EvnProto_id') || !record.get('EvnDirection_id') || parseInt(record.get('EvnDirection_IsBad')) == 1 || !(isPathoMorphoUser() || isOperator()),
				disallowDeathSvid = record.get('DeathSvid_Exists') == 1,
				directionType = record.get('DirectionType_Name') === 'Патоморфогистологическое',
				existsProto = record.get('Proto_Exists') === 'true',
				existsCorpseReciept = record.get('CorpseReciept_Exists'), // сведения о поступлении тела
				existsCorpseGiveaway = record.get('CorpseGiveaway_Exists'), // сведения о выдаче тела
				existsRefuse = record.get('Refuse_Exists') === 'true'; // сведения об отказе от вскрытия

				this.GridPanel.ViewActions.open_emk.setDisabled(disallowOpenEmk);
				// this.GridPanel.ViewActions.action_proto_add.setDisabled(disallowProtoAdd && existsRefuse && existsCorpseReciept);
				// отсутствует отказ от вскрытия и есть сведения о поступлении тела
				if (!disallowProtoAdd && !existsRefuse && existsCorpseReciept) {
					this.GridPanel.ViewActions.action_proto_add.setDisabled(false);
				}
				else {
					this.GridPanel.ViewActions.action_proto_add.setDisabled(true);
				}
				this.GridPanel.ViewActions.action_proto_view.setDisabled(disallowProtoView);
				this.GridPanel.ViewActions.action_isnotbad.setDisabled(disallowIsNotBad || disallowEdit);
				this.GridPanel.ViewActions.action_isbad.setDisabled(disallowIsBad || disallowEdit);
				this.GridPanel.ViewActions.action_deathsvid.setDisabled(disallowDeathSvid || disallowEdit);
				this.GridPanel.ViewActions.action_showdirection.setDisabled(!record.get('EvnDirection_id'));
				this.GridPanel.ViewActions.external_direction.setDisabled(false);

				if (directionType) {
					this.GridPanel.ViewActions.action_corpse_reciept.enable();
					this.GridPanel.ViewActions.action_corpse_giveaway.enable();
					this.GridPanel.ViewActions.action_refuse.enable();

					//форма принять тело доступна, если нет записей о принятии тела
					this.CorpseRecieptMenu.items.itemAt(0).setDisabled(existsCorpseReciept);
					this.CorpseRecieptMenu.items.itemAt(1).setDisabled(!existsCorpseReciept);
					this.CorpseRecieptMenu.items.itemAt(2).setDisabled(!existsCorpseReciept);
					this.CorpseRecieptMenu.items.itemAt(3).setDisabled(!existsCorpseReciept);
					
					//форма выдаать доступна, если существует запись о принятии тела И не существует записи о выдачи тела И 
					//есть протокол патоморфо исследования ИЛИ подписан отказ от вскрытия
					if (existsCorpseReciept && !existsCorpseGiveaway && (existsProto || existsRefuse)) {
						this.CorpseGiveawayMenu.items.itemAt(0).setDisabled(false);
					}
					else {
						this.CorpseGiveawayMenu.items.itemAt(0).setDisabled(true);
					}
					this.CorpseGiveawayMenu.items.itemAt(1).setDisabled(!existsCorpseGiveaway);
					this.CorpseGiveawayMenu.items.itemAt(2).setDisabled(!existsCorpseGiveaway);
					this.CorpseGiveawayMenu.items.itemAt(3).setDisabled(!existsCorpseGiveaway);

					//форма добавить отказ доступна если не существует отказ от вскрытия и нет протокола исследования
					if (!existsRefuse && !existsProto) {
						this.RefuseMenu.items.itemAt(0).setDisabled(false);
					}
					else {
						this.RefuseMenu.items.itemAt(0).setDisabled(true);
					}
					this.RefuseMenu.items.itemAt(1).setDisabled(!existsRefuse);
					this.RefuseMenu.items.itemAt(2).setDisabled(!existsRefuse);
					this.RefuseMenu.items.itemAt(3).setDisabled(!existsRefuse);

				} else {
					this.GridPanel.ViewActions.action_corpse_reciept.disable();
					this.GridPanel.ViewActions.action_corpse_giveaway.disable();
					this.GridPanel.ViewActions.action_refuse.disable();
				}

			}.createDelegate(this),
			pageSize: 20,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
				{ name: 'DirectionType_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Lpu_did', type: 'int', hidden: true },
				{ name: 'Group_date', header: lang['data'], sortable: true, hidden: true, type: 'date' },
				{ name: 'Person_Surname', hidden: true },
				{ name: 'Person_Firname', hidden: true },
				{ name: 'Person_Secname', hidden: true },
				{ name: 'pmUser_insID', type: 'int', hidden: true },
				{ name: 'DeathSvid_Exists', type: 'int', header: lang['suschestvuet_svidetelstvo_o_smerti'], hidden: true },
				{ name: 'EvnDirection_IsBad', type: 'int', hidden: true },
				{ name: 'MedPersonal_id', type: 'int', hidden: true },
				{ name: 'EvnProto_id', type: 'int', hidden: true },
				{ name: 'CorpseReciept_Exists', type: 'bool', hidden: true },
				{ name: 'CorpseReciept_Id', type: 'int', hidden: true },
				{ name: 'CorpseGiveaway_Exists', type: 'bool', hidden: true },
				{ name: 'CorpseGiveaway_Id', type: 'int', hidden: true },
				{ name: 'EvnMorfoHistologicProto_autopsyDate', type: 'date', hidden: true },
				{ name: 'EvnMorfoHistologicProto_deathDate', type: 'date', hidden: true },
				{ name: 'MorfoHistologicRefuse_Id', type: 'int', hidden: true },
				{ name: 'Proto_Exists', header: lang['protokol'], type: 'checkbox', width: 60 },
				{ name: 'DirectionType_Name', header: lang['tip_napravleniya'], type: 'string', sortable: true, width: 160 },
				{ name: 'EvnDirection_setDate', header: lang['data_napravleniya'], type: 'date', sortable: true, width: 100 },
				{ name: 'EvnDirection_Ser', header: lang['seriya'], type: 'string', sortable: true, width: 80 },
				{ name: 'EvnDirection_Num', header: lang['nomer'], type: 'string', sortable: true, width: 80 },
				{ name: 'Person_FIO', header: lang['patsient'], width: 280, id: 'autoexpand' },
				{ name: 'Person_BirthDay', header: lang['data_rojdeniya'], type: 'date' },
				{ name: 'EvnPS_NumCard', header: lang['nomer_kvs'], type: 'string', sortable: true, width: 100 },
				{ name: 'CorpseReciept_Date', header: lang['data_postupleniya_tela'], type: 'date',  sortable: true, width: 140},
				{ name: 'CorpseGiveaway_Date', header: lang['data_vydachi_tela'], type: 'date', sortable: true, width: 140},
				{ name: 'Refuse_Exists', header: lang['otkaz_ot_vskryitiya'], type: 'checkbox', width: 140 }
			],
			title: lang['jurnal_rabochego_mesta'],
			totalProperty: 'totalCount'
		});

		this.CorpseRecieptMenu = new Ext.menu.Menu({
			items: [
				{ name: 'corpse_reciept_add', text: lang['prinyat_telo'], handler: function () { this.showCorpseRecieptForm('add'); }.createDelegate(this) },
				{ name: 'corpse_reciept_edit', text: lang['izmenit_svedeniya_o_prinyatii_tela'], handler: function () { this.showCorpseRecieptForm('edit'); }.createDelegate(this) },
				{ name: 'corpse_reciept_view', text: lang['prosmotr_svedeniy_o_prinyatii_tela'], handler: function () { this.showCorpseRecieptForm('view'); }.createDelegate(this) },
				{ name: 'corpse_reciept_del', text: lang['udalit_svedeniya_o_prinyatii_tela'], handler: function () { this.deleteMorfoHistologicCorpseRecieptRequest(); }.createDelegate(this) }
			]
		});

		this.CorpseGiveawayMenu = new Ext.menu.Menu({
			items: [
				{ name: 'corpse_away_add', text: lang['vydat_telo'], handler: function () { this.showCorpseGiveawayForm('add'); }.createDelegate(this) },
				{ name: 'corpse_away_edit', text: lang['izmenit_svedeniya_o_vydache_tela'], handler: function () { this.showCorpseGiveawayForm('edit'); }.createDelegate(this) },
				{ name: 'corpse_away_view', text: lang['prosmotr_svedeniy_o_vydache_tela'], handler: function () { this.showCorpseGiveawayForm('view'); }.createDelegate(this) },
				{ name: 'corpse_away_delete', text: lang['udalit_svedeniya_o_vydache_tela'], handler: function () { this.deleteMorfoHistologicCorpseGiveawayRequest(); }.createDelegate(this) }
			]
		});

		this.RefuseMenu = new Ext.menu.Menu({
			items: [
				{ name: 'refuse_add', text: lang['dobavit_otkaz'], handler: function () { this.showRefuseForm('add'); }.createDelegate(this) },
				{ name: 'refuse_edit', text: lang['izmenit_otkaz'], handler: function () { this.showRefuseForm('edit'); }.createDelegate(this) },
				{ name: 'refuse_view', text: lang['prosmotr_otkaza'], handler: function () { this.showRefuseForm('view'); }.createDelegate(this) },
				{ name: 'refuse_del', text: lang['udalit_otkaz'], handler: function () { this.deleteMorfoHistologicRefuseRequest(); }.createDelegate(this) }
			]
		});
		
		this.GridPanel.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( row.get('EvnDirection_IsBad') == 1 ) {
					cls = cls + 'x-grid-rowgray';
				}
				else {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});

		sw.Promed.swWorkPlacePathoMorphologyWindow.superclass.initComponent.apply(this, arguments);
	}
});