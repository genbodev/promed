/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 14.07.15
 * Time: 11:20
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swWorkPlaceEvnStickRegWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceEvnStickRegWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
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
	show: function() {
		sw.Promed.swWorkPlaceEvnStickRegWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		var base_form = wnd.FilterPanel.getForm();
        wnd.CurMedService_id = getGlobalOptions().CurMedService_id;

        if(!wnd.FilterPanel.fieldSet.expanded){
            wnd.FilterPanel.fieldSet.expand();
        }
        wnd.doReset();
        wnd.StickPidsPanel.getGrid().getStore().removeAll();
        wnd.StickPanel.getGrid().getStore().removeAll();
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1;
        var yyyy = today.getFullYear();
        if(dd<10) {
            dd='0'+dd
        }
        if(mm<10) {
            mm='0'+mm
        }
        today = yyyy + '-' + mm + '-' + dd;
        base_form.findField('EvnStick_pidDate').setValue(today + ' - '  + today);

        wnd.StickPanel.addActions({
        	name: 'action_cancel_stick',
        	text: langs('Аннулировать'),
        	hidden: getRegionNick() == 'kz',
        	handler: function() {
        		wnd.deleteEvnStick({ ignoreQuestion: true });
        	}
        }, 5);

        Ext.Ajax.request({
            url: '/?c=Stick&m=getMedServiceParent',
            params: {
                MedService_id: wnd.CurMedService_id
            },
            callback: function(o,s,r){
                wnd.CurLpuSection_id = '';
                wnd.CurLpuUnit_id = '';
                wnd.CurLpuBuilding_id = '';
                if(s && r.responseText != ''){
                    var result  = Ext.util.JSON.decode(r.responseText);
                    if(!Ext.isEmpty(result[0].LpuSection_id))
                        wnd.CurLpuSection_id = result[0].LpuSection_id;
                    if(!Ext.isEmpty(result[0].LpuBuilding_id))
                        wnd.CurLpuBuilding_id = result[0].LpuBuilding_id;
                    if(!Ext.isEmpty(result[0].LpuUnit_id))
                        wnd.CurLpuUnit_id = result[0].LpuUnit_id;

                    base_form.findField('LpuSection_id').getStore().load({
                        params: {
                            Object: 'LpuSection',
                            LpuSection_id: wnd.CurLpuSection_id,
                            LpuBuilding_id: wnd.CurLpuBuilding_id,
                            Lpu_id: getGlobalOptions().lpu_id,
                            LpuUnit_id: wnd.CurLpuUnit_id,
                            LpuSection_Name: ''
                        },
                        callback: function()
                        {
                            wnd.doSearch();
                            wnd.StickPidsPanel.setParam('start', 0);
                        }
                    });
                }
                else
                {
                    base_form.findField('LpuSection_id').getStore().load({
                        params: {
                            Object: 'LpuSection',
                            LpuSection_id: '',
                            Lpu_id: getGlobalOptions().lpu_id,
                            LpuUnit_id: '',
                            LpuSection_Name: ''
                        },
                        callback: function()
                        {
                            wnd.doSearch();
                            wnd.StickPidsPanel.setParam('start', 0);
                        }
                    });
                }
            }
        });
	},
	doReset: function(){
		sw.Promed.swWorkPlaceEvnStickRegWindow.superclass.doReset.apply(this, arguments); // выполняем базовый метод
	},
	doSearch: function(){
		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {});
        params.CurMedService_id = getGlobalOptions().CurMedService_id;
        params.CurLpuSection_id = this.CurLpuSection_id;
        params.CurLpuBuilding_id = this.CurLpuBuilding_id;
        params.CurLpuUnit_id = this.CurLpuUnit_id;
		this.StickPidsPanel.removeAll({clearAll:true});
		this.StickPidsPanel.loadData({globalFilters: params});
		//this.StickPidsPanel.getGrid().getStore().load({params: params});
	},
	deleteEvnStick: function(options) {
		options = options || {};

		if ( this.action == 'view') {
			return false;
		}

		var selected_stick_pid = this.StickPidsPanel.getGrid().getSelectionModel().getSelected(),
			selected_stick = this.StickPanel.getGrid().getSelectionModel().getSelected(),
			error = '',
			_this = this,
			grid = null,
			question = '',
			params = {},
			url = '',
			lastEvnDeleted = false;

		if (options.params) {
			params = options.params;
		}

		grid = this.StickPidsPanel.getGrid();

		if ( !selected_stick || !selected_stick.get('EvnStick_id') ) {
			return false;
		}

		error = lang['pri_udalenii_lvn_voznikli_oshibki'];
		question = lang['udalit_lvn'];

		url = '/?c=Stick&m=deleteEvnStick';

		params['EvnStick_id'] = selected_stick.get('EvnStick_id');
		params['EvnStick_mid'] = selected_stick_pid.get('Evn_id');

		var alert = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				_this.deleteEvnStick(options);
			},
			options: options
		});

		if (options.StickCauseDel_id) {
			params.StickCauseDel_id = options.StickCauseDel_id;
		}

		var doDelete = function() {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
			loadMask.show();

			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], error);
				},
				params: params,
				success: function(response, options) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						if (response_obj.Alert_Msg) {
							if (response_obj.Alert_Code == 705) {
								getWnd('swStickCauseDelSelectWindow').show({
									countNotPaid: response_obj.countNotPaid,
									existDuplicate: response_obj.existDuplicate,
									callback: function(StickCauseDel_id) {
										if (StickCauseDel_id) {
											options.ignoreQuestion = true;
											options.StickCauseDel_id = StickCauseDel_id;
											this.deleteEvnStick(options);
										}
									}.createDelegate(this)
								});
							} else {
								var a_params = alert[response_obj.Alert_Code];
								sw.swMsg.show({
									buttons: a_params.buttons,
									fn: function(buttonId) {
										a_params.fn(buttonId, this);
									}.createDelegate(this),
									msg: response_obj.Alert_Msg,
									icon: Ext.MessageBox.QUESTION,
									title: lang['vopros']
								});
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
						}
					}
					else {
						if (response_obj.IsDelQueue) {
							sw.swMsg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
							selected_stick.set('EvnStick_IsDelQueue', 2);
							selected_stick.commit();
						} else {
							grid.getStore().remove(selected_stick);
						}

						if ( grid.getStore().getCount() == 0 ) {
							grid.getTopToolbar().items.items[1].disable();
							grid.getTopToolbar().items.items[2].disable();
							grid.getTopToolbar().items.items[3].disable();
							LoadEmptyRow(grid);
						}

						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
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
				title: lang['vopros']
			});
		}
	},
    openEvnStick: function(action){
        var wnd = this;
        var formParams = new Object();
        var params = new Object();
        var selected_stick_pid = wnd.StickPidsPanel.getGrid().getSelectionModel().getSelected();
        params.action = action;
        var evnStickType = 0;
        var Evn_id = selected_stick_pid.data.Evn_id;
        var joborg_id = selected_stick_pid.data.JobOrg_id;
        var Person_id = selected_stick_pid.data.Person_id;
        var PersonEvn_id = selected_stick_pid.data.PersonEvn_id;
        var Server_id = selected_stick_pid.data.Server_id;
        var Person_Post = selected_stick_pid.data.Person_Post;
        var Person_SurName = selected_stick_pid.data.Person_SurName;
        var Person_FirName = selected_stick_pid.data.Person_FirName;
        var Person_SecName = selected_stick_pid.data.Person_SecName;
        var Person_Birthday = getValidDT(selected_stick_pid.data.Person_Birthday,'');
        var EvnStick_begDate = getValidDT(selected_stick_pid.data.EvnStick_pidBegDate,'');
        var EvnStick_setDate = getValidDT(selected_stick_pid.data.EvnStick_pidBegDate,'');
        if(params.action != 'add')
        {
            var selected_stick = wnd.StickPanel.getGrid().getSelectionModel().getSelected();
            evnStickType = selected_stick.data.evnStickType;
            params.evnStickType = evnStickType;
            formParams.EvnStick_id = selected_stick.data.EvnStick_id;
        }
        params.callback = function(data) {
            var stick_data = new Object();
            stick_data.Evn_id = Evn_id;
            wnd.StickPanel.loadData({globalFilters: stick_data});
        }.createDelegate(this);
        if(selected_stick_pid.data.EvnStick_pidType == lang['tap'])
            params.parentClass = 'EvnPL';
        else
            params.parentClass = 'EvnPS';
        params.JobOrg_id = joborg_id;
        params.Person_id = Person_id;
        params.PersonEvn_id = PersonEvn_id;
        params.Server_id = Server_id;
        params.EvnStick_mid = Evn_id;
        params.Person_Post = Person_Post;
        params.Person_Post = Person_Post;
        params.Person_Firname = Person_FirName;
        params.Person_Secname = Person_SecName;
        params.Person_Surname = Person_SurName;
        params.Person_Birthday = Person_Birthday;
        formParams.EvnStick_mid = Evn_id;
        formParams.EvnStick_pid = Evn_id;
        formParams.Server_id = Server_id;
        formParams.StickReg = 1;
        formParams.CurLpuSection_id = wnd.CurLpuSection_id;
        formParams.CurLpuUnit_id = wnd.CurLpuUnit_id;
        formParams.CurLpuBuilding_id = wnd.CurLpuBuilding_id;
        formParams.IngoreMSFFilter = 1;
        formParams.EvnStick_begDate = EvnStick_begDate;
        formParams.EvnStick_setDate = EvnStick_setDate;
        formParams.Person_id = Person_id;
        formParams.PersonEvn_id = PersonEvn_id;
        params.formParams = formParams;
        if(params.action == 'add')
            getWnd('swEvnStickChangeWindow').show(params);
        else
        {
            if(evnStickType == 3)
                getWnd('swEvnStickStudentEditWindow').show(params);
            else
                getWnd('swEvnStickEditWindow').show(params);
        }
    },
	initComponent: function(){
		var wnd = this;
		this.gridPanelAutoLoad = false;
		this.showToolbar = false;
		this.buttonPanelActions = {
			action_Lvn: {
				nn: 'action_Lvn',
				text: lang['lvn_poisk'],
				tooltip: lang['poisk_listkov_vremennoy_netrudosposobnosti'],
				iconCls : 'lvn-search16',
				handler: function() {
                    var params = new Object();
                    params.CurLpuSection_id = wnd.CurLpuSection_id;
                    params.CurLpuUnit_id = wnd.CurLpuUnit_id;
                    params.CurLpuBuilding_id = wnd.CurLpuBuilding_id;
                    params.StickReg = 1;
					getWnd('swEvnStickViewWindow').show(params);
				}
			},
			actions_settings: {
				nn: 'actions_settings',
				iconCls: 'settings32',
				text: lang['servis'],
				tooltip: lang['servis'],
				listeners: {
					'click': function(){
						var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
								if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: lang['otkryityih_okon_net'],
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
							menu.add(new Ext.menu.Separator());
							menu.add(new Ext.menu.Item(
								{
									text: lang['zakryit_vse_okna'],
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
												wnd.hide();
											}
										});
									}
								})
							);
						}
					}
				},
				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_UserProfile',
							text: lang['moy_profil'],
							tooltip: lang['profil_polzovatelya'],
							iconCls : 'user16',
							hidden: false,
							handler: function()
							{
								args = {}
								args.action = 'edit';
								getWnd('swUserProfileEditWindow').show(args);
							}
						},
						{
							nn: 'action_settings',
							text: lang['nastroyki'],
							tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
							iconCls : 'settings16',
							handler: function()
							{
								getWnd('swOptionsWindow').show();
							}
						},
						{
							nn: 'action_selectMO',
							text: lang['vyibor_mo'],
							tooltip: lang['vyibor_mo'],
							iconCls: 'lpu-select16',
							hidden: !isSuperAdmin(),
							handler: function()
							{
								Ext.WindowMgr.each(function(wnd){
									if ( wnd.isVisible() )
									{
										wnd.hide();
									}
								});
								getWnd('swSelectLpuWindow').show({});
							}
						},
						{
							text:lang['pomosch'],
							nn: 'action_help',
							iconCls: 'help16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'menu_help',
									items:
										[
											{
												text: lang['vyizov_spravki'],
												tooltip: lang['pomosch_po_programme'],
												iconCls : 'help16',
												handler: function()
												{
													ShowHelp(lang['soderjanie']);
												}
											},
											{
												text: lang['forum_podderjki'],
												iconCls: 'support16',
												xtype: 'tbbutton',
												handler: function() {
													window.open(ForumLink);
												}
											},
											{
												text: lang['o_programme'],
												tooltip: lang['informatsiya_o_programme'],
												iconCls : 'promed16',
												testId: 'mainmenu_help_about',
												handler: function()
												{
													getWnd('swAboutWindow').show();
												}
											}
										]
								}),
							tabIndex: -1
						},
						{
							text: lang['informatsiya_o_polzovatele'],
							nn: 'action_user_about',
							iconCls: 'user16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'user_menu',
									items:
										[
											{
												disabled: true,
												iconCls: 'user16',
												text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
												xtype: 'tbtext'
											}
										]
								})
						},
						{
							text: lang['okna'],
							nn: 'action_windows',
							iconCls: 'windows16',
							listeners: {
								'click': function(e) {
									var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: lang['otkryityih_okon_net'],
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: lang['zakryit_vse_okna'],
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								},
								'mouseover': function() {
									var menu = Ext.menu.MenuMgr.get('wpprw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: lang['otkryityih_okon_net'],
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: lang['zakryit_vse_okna'],
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								}
							},
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'wpprw_menu_windows',
									items: [
										'-'
									]
								}),
							tabIndex: -1
						}
					]
				})
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			},
			action_RegistryES: {
				nn: 'action_RegistryES',
				tooltip: lang['reestryi_lvn'],
				text: lang['reestryi_lvn'],
				iconCls: 'book32',
				hidden: getRegionNick() == 'kz',
				handler: function(){
					getWnd('swRegistryESViewWindow').show()
				}
			},
			action_RegistryESStorage: {
				nn: 'action_RegistryESStorage',
				tooltip: 'Номера ЭЛН',
				text: 'Номера ЭЛН',
				iconCls: 'book32',
				hidden: getRegionNick() == 'kz',
				handler: function(){
					getWnd('swRegistryESStorageViewWindow').show();
				}
			},
			action_StickFSSDataView: {
				nn: 'action_StickFSSDataView',
				tooltip: 'Запросы в ФСС',
				text: 'Запросы в ФСС',
				iconCls: 'card-view32',
				hidden: getRegionNick() == 'kz',
				handler: function(){
					getWnd('swStickFSSDataViewWindow').show();
				}
			}
		};
		this.onKeyDown = function (inp, e) {

			if ( e.getKey() == Ext.EventObject.ENTER ) {
				e.stopEvent();

				var counter = 0;
				for (var i in wnd.FilterPanel.getForm().getValues()){
					if (wnd.FilterPanel.getForm().getValues()[i] != '') {
						counter++;
					}
				}
				if ( counter < 1){
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {});
					return false;
				}
				wnd.doSearch();
				wnd.StickPidsPanel.setParam('start', 0);
			}

		};
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: wnd,
			labelWidth: 120,
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Person_Surname',
							width: 200,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Person_Firname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Person_Secname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							fieldLabel: lang['dr'],
							format: 'd.m.Y',
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Person_Birthday',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank: true,
							codeField: 'EvnStick_pidType_Code',
							displayField: 'EvnStick_pidType_Name',
							editable: false,
							fieldLabel: lang['tip_dokumenta'],
							hiddenName: 'EvnStick_pidType_id',
							hideEmptyRow: true,
							ignoreIsEmpty: true,
							listeners: {
								'blur': function(combo)  {
									if ( combo.value == '' )
										combo.setValue(1);
								},
                                'keydown': wnd.onKeyDown
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 1, 1, lang['tap'] ],
									[ 2, 2, lang['kvs'] ]
								],
								fields: [
									{ name: 'EvnStick_pidType_id', type: 'int'},
									{ name: 'EvnStick_pidType_Code', type: 'int'},
									{ name: 'EvnStick_pidType_Name', type: 'string'}
								],
								key: 'EvnStick_pidType_id',
								sortInfo: { field: 'EvnStick_pidType_Code' }
							}),
							tabIndex: -1,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{EvnStick_pidType_Code}</font>&nbsp;{EvnStick_pidType_Name}',
								'</div></tpl>'
							),
							value: 1,
							valueField: 'EvnStick_pidType_id',
							width: 200,
							xtype: 'swbaselocalcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['nomer'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'EvnStick_pidNum',
							width: 120,
							xtype: 'textfield'
						}]
					},
                    {
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank:true,
							hiddenName:'LpuSection_id',
							xtype: 'swlpusectioncombo',
                            listeners: {
                                'keydown': wnd.onKeyDown
                            },
							width: 295
						}]
					},
                    {
                        layout: 'form',
                        labelWidth: 120,
                        items: [{
                            fieldLabel: lang['data_nachala'],
                            name: 'EvnStick_pidDate',
                            plugins: [
                                new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                            ],
                            listeners: {
                                'keydown': wnd.onKeyDown
                            },
                            width: 180,
                            xtype: 'daterangefield'
                        }]
                    },
					{
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: 'Случай закончен',
							name: 'EvnStick_pidIsEnd',
							hiddenName: 'EvnStick_pidIsFinish',
							listeners: {
								'keydown': wnd.onKeyDown
							},
							width: 180,
							xtype: 'swyesnocombo'
						}]
					}
                    ]
				},  {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['seriya_polisa'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Polis_Ser',
							width: 200,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['nomer_polisa'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Polis_Num',
							width: 120,
							xtype: 'numberfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['ed_nomer'],
							listeners: {
								'keydown': wnd.onKeyDown
							},
							name: 'Person_Code',
							width: 120,
							xtype: 'numberfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						hidden: getRegionNick().inlist(['kz']), // базовый кроме казахстана
						items: [{
							fieldLabel: langs('Вид ЛВН'),
							hiddenName: 'LvnType',
							width: 175,
							xtype: 'swbaselocalcombo',
							listeners: {
								beforerender: function (combo)
								{
									combo.setValue(null);
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ null, '' ],
									[ 1, langs('На бумажном носителе') ],
									[ 2, langs('Электронный') ]
								],
								fields: [
									{ name: 'LvnValue', type: 'int' },
									{ name: 'LvnType', type: 'string' }
								],
								key: 'LvnValue',
								sortInfo: { field: 'LvnValue' }
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red"></font>&nbsp;{LvnType}',
								'</div></tpl>'
							),
							valueField: 'LvnValue',
							displayField: 'LvnType'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: wnd.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								var counter = 0;
								 for (var i in wnd.FilterPanel.getForm().getValues()){
									 if (wnd.FilterPanel.getForm().getValues()[i] != '') {
										counter++;
									 }
								 }
								 if ( counter < 1){
									 sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {});
									 return false;
								 }
								 wnd.doSearch();
								 wnd.StickPidsPanel.setParam('start', 0);
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: wnd.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								wnd.doReset();
								wnd.StickPidsPanel.getGrid().getStore().removeAll();
								wnd.StickPanel.getGrid().getStore().removeAll();
							}
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
									wnd.readFromCard();
								}
							}]
					}]
				}]
			}
		});
		this.StickPidsPanel = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					hidden: true
				},
				{
					name: 'action_edit',
					hidden: true
				},
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh', handler: function(){wnd.doSearch(); wnd.StickPidsPanel.setParam('start', 0);} },
				{ name: 'action_print', text: lang['pechat_spiska'] }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=Stick&m=loadEvnStickPids',
			id: wnd.id + 'EvnStickPidsPanel',
			onLoadData: function(sm, index, record) {
				//
			},
			onRowSelect: function(sm, index, record) {
				var stick_data = this.getParams();
				wnd.StickPanel.setActionDisabled('action_add', Ext.isEmpty(stick_data.Evn_id));
				wnd.StickPanel.loadData({globalFilters: stick_data});
			},
			pageSize: 50,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'Evn_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnStick_pidType', type: 'string', header: lang['tip_dokumenta'], width: 100 },
				{ name: 'EvnStick_pidNum', type: 'string', header: lang['nomer_dokumenta'], width: 100 },
				{ name: 'Person_FIO', type: 'string', header: lang['patsient'], width: 250 },
                { name: 'Person_Birthday', type: 'string', hidden: false, header: lang['data_rojdeniya']},
				{ name: 'MedPersonal_FIO', type: 'string', header: lang['vrach'], width: 250 },
				{ name: 'EvnStick_pidBegDate', type: 'date', header: lang['data_nachala'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'EvnStick_pidEndDate', type: 'date', header: lang['data_okonchaniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 250 },
				{ name: 'HasEvnStick', type: 'checkbox', header: lang['netrudosposobnost'], width: 150},
				{ name: 'JobOrg_id', type: 'int', hidden: true, header: lang['mesto_rabotyi']},
				{ name: 'Person_id', type: 'int', hidden: true, header: lang['identifikator_cheloveka']},
				{ name: 'PersonEvn_id', type: 'int', hidden: true, header: 'PersonEvn_id'},
				{ name: 'Server_id', type: 'int', hidden: true, header: 'Server_id'},
				{ name: 'Person_Post', type: 'int', hidden: true, header: 'Person_Post'},
				{ name: 'Person_SurName', type: 'string', hidden: true, header: 'Person_SurName'},
				{ name: 'Person_FirName', type: 'string', hidden: true, header: 'Person_FirName'},
				{ name: 'Person_SecName', type: 'string', hidden: true, header: 'Person_SecName'}
				//Person_SecName
			],
			title: lang['jurnal_rabochego_mesta'],
			totalProperty: 'totalCount',
			getParams: function() {
				var viewframe = this;
				var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
				if(selected_record && selected_record.data.Evn_id > 0){
					var params = new Object();
					params.Evn_id = selected_record.data.Evn_id;
					return params;
				}
				return false;
			}
		});

		this.StickPanel = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					hidden: false,
					handler: function()
					{
                        wnd.openEvnStick('add');
					}
				},
				{
					name: 'action_edit',
					hidden: false,
					handler: function()
					{
                        wnd.openEvnStick('edit');
					}
				},
				{
					name: 'action_view',
					hidden: false,
					handler: function()
					{
                        wnd.openEvnStick('view');
					}
				},
				{
					name: 'action_delete',
					hidden: false,
					handler: function()
					{
						wnd.deleteEvnStick();
					}
				},
				{ name: 'action_refresh', hidden: false },
				{ name: 'action_print', text: lang['pechat_spiska'] }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: '/?c=Stick&m=loadEvnStickForARM',
			id: wnd.id + 'StickPanel',
			onDblClick: function() {
                wnd.openEvnStick('edit');
			},

			onLoadData: function(sm, index, record) {

			},
			onRowSelect: function(sm, index, record) {
				if (record) {
					if (record.get('accessType') == 'view' && record.get('EvnStick_IsDelQueue') == 2) {
						this.setActionDisabled('action_delete', true);
					} else {
						this.setActionDisabled('action_delete', false);
					}

					if(record.get('cancelAccessType') != 'view') {
						this.setActionDisabled('action_cancel_stick', false);
					} else {
						this.setActionDisabled('action_cancel_stick', true);
					}

				}
			},
			paging: false,
			region: 'south',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnStick_id', type: 'string', header: 'ID', key: true },
				{ name: 'StickType_Name', type: 'string', header: lang['vid_dokumenta'], width: 100 },
				{ name: 'EvnStick_setDate', type: 'string', header: lang['data_vyidachi'], width: 150 },
				{ name: 'EvnStick_Num', type: 'string', header: lang['nomer'], width: 150 },
				{ name: 'StickOrder_Name', type: 'string', header: lang['poryadok_vyipiski'], width: 150 },
				{ name: 'StickWorkType_Name', type: 'string', header: lang['tip_zanyatosti'], width: 150 },
				{ name: 'EvnStatus_Name', type: 'string', header: lang['tip_lvn'], width: 150 },
				{ name: 'OrgJob_Name', type: 'string', header: lang['mesto_rabotyi'], width: 250 },
                { name: 'evnStickType', type: 'int', hidden: true, header: 'evnStickType'},
                { name: 'delAccessType', type: 'string', hidden: true, header: 'delAccessType'},
                { name: 'cancelAccessType', type: 'string', hidden: true, header: 'cancelAccessType'},
				{ name: 'EvnStick_IsDelQueue', type: 'int', hidden: true, header: 'EvnStick_IsDelQueue'}
			],
			title: lang['spisok_lvn']
		});

		this.StickPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('EvnStick_IsDelQueue') == 2) {
					cls = cls + 'x-grid-rowbackgray ';
				}
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		this.GridPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			defaults: {split: true},
			items: [
				this.StickPidsPanel,
				this.StickPanel
			]
		});
		sw.Promed.swWorkPlaceEvnStickRegWindow.superclass.initComponent.apply(this, arguments);
	}
});
