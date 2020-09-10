//Форма Вызовы на контроле

Ext.define('sw.CmpCallsUnderControlList', {
	alias: 'widget.CmpCallsUnderControlList',
	extend: 'Ext.panel.Panel',
	refId: 'CmpCallsUnderControlList',
	flex: 1,
	layout: {
		type: 'fit',
		align: 'stretch'
	},

	initComponent: function() {
		var me = this;

		me.armtype = me.initialConfig.armtype || 'default';

		me.gridStore = Ext.create('Ext.data.Store', {
			fields: [
				{
					name: 'CmpCallCard_id',
					type: 'int'
				},
				{
					name: 'CmpCloseCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard_prmDate',
					type: 'date',
					convert : function(dt) {
						return new Date(dt.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
					}
				},
				{
					name: 'CmpCallCard_Numv',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Ngod',
					type: 'string'
				},
				{
					name: 'Person_FIO',
					type: 'string'
				},
				{
					name: 'personAgeText',
					type: 'string'
				},
				{
					name: 'Person_Birthday',
					type: 'date'
				},
				{
					name: 'Adress_Name',
					type: 'string'
				},
				{
					name: 'CmpCallType_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_IsExtraText',
					type: 'string'
				},
				{
					name: 'CmpReason_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCardStatusType_id',
					type: 'int'
				},
				{
					name: 'CmpCallCardStatusType_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Comm',
					type: 'string'
				},
				{
					name: 'CmpCallCard_IsExtra',
					type: 'string'
				},
				{
					name: 'Diag',
					type: 'string'
				},
				{
					name: 'LpuBuilding_Name',
					type: 'string'
				},
				{
					name: 'EmergencyTeam_Num',
					type: 'string'
				},
				{
					name: 'CmpCloseCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard112_id',
					type: 'int'
				},
				{
					name: 'CmpCallRecord_id',
					type: 'int'
				},{
					name: 'CmpCallCard_isControlCall',
					type: 'int'
				},
				{
					name: 'Lpu_NMP_Name',
					type: 'string'
				},
				{
					name: 'ActiveVisitLpu_Nick',
					type: 'string'
				},
				{
					name: 'CmpCallCardEventType_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Urgency',
					type: 'int'
				},
				{
					name: 'DuplicateAndActiveCall_Count',
					type: 'string'
				},
				{
					name: 'CmpResult_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_prmDateStr',
					type: 'string'
				},
				{
					name: 'CmpGroup_id',
					type: 'int'
				}
			],
			autoLoad: false,
			stripeRows: true,
			numLoad: 0,
			groupField: 'CmpGroup_id',
			sorters: [
				{
					property : 'CmpCallCard_prmDate',
					direction: 'DESC'
				}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=loadCallsUnderControlList',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});

		me.dockedItems = [
			{
				xtype: 'toolbar',
				margin: '0 0 20 0',
				dock: 'bottom',
				items: [
					'->',
					{
						xtype: 'button',
						text: 'Помощь',
						iconCls: 'help16',
						handler: function()
						{
							ShowHelp(me.up('container').title);
						}
					}
				]
			}
		];

		me.items = [{
			xtype: 'BaseForm',
			id: me.id + 'CmpCallsUnderControlListForm',
			border: false,
			frame: true,
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			flex: 1,
			autoScroll: true,
			overflowX: 'scroll',
			bodyBorder: false,
			refId: 'CmpCallsUnderControlList',
			items: [
				{
					xtype: 'gridpanel',
					store: me.gridStore,
					viewConfig: {
						loadMask: true,
						loadingText: 'Загрузка..',
						preserveScrollOnRefresh: true,
						minHeight: '1px',
						overflowX: 'scroll'
					},
					columns: [
						{
							dataIndex: 'CmpCallCard_id',
							text: 'ИД карты вызова',
							hidden: true
						},
						/*
						 {
						 dataIndex: 'CmpCallCard_prmDate',
						 text: 'Дата и время',
						 width: 120,
						 filter: {xtype: 'transFieldDelbut', translate: false}
						 },
						 */
						{
							dataIndex: 'CmpCallCard_prmDate',
							text: 'Дата и время',
							width: 120,
							xtype:'datecolumn',
							//format: 'd.m.Y H:i:s',
							format: (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ? 'd.m.Y H:i' : 'd.m.Y H:i:s',
							filter: {
								xtype: 'datefield',
								format: 'd.m.Y',
								allowBlank: true,
								translate: false,
								filterName:'CmpCallCard_prmDateStr',
								onTriggerClick: function() {
									var dt1 = this;
									Ext.form.DateField.prototype.onTriggerClick.apply(this, arguments);

									if(!this.clearBtn){
										this.clearBtn = new Ext.Component({
											autoEl: {
												tag: 'div',
												cls: 'clearDatefieldsButton',
											},
											listeners: {
												el: {
													click: function() {
														dt1.reset();
													}
												}
											}
										});
									}
								}
							}
						},
						{
							dataIndex: 'CmpCallCard_Numv',
							text: '№ В/Д',
							width: 60,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpCallCard_Ngod',
							text: '№ В/Г',
							width: 60,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'Person_FIO',
							text: 'Пациент',
							width: 180,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'Person_Birthday',
							xtype: 'datecolumn',
							sortable: true,
							text: 'Возраст',
							width: 55,
							renderer:function(birthday){
								var result,
									now = new Date();

								if (Ext.isEmpty(birthday)) {
									result = '';
								} else {
									var years = swGetPersonAge(birthday, now);

									if (years > 0) {
										result = years + ' лет.';
									} else {
										var days = Math.floor(Math.abs((now - birthday)/(1000 * 3600 * 24))),
											months = Math.floor(Math.abs(now.getMonthsBetween(birthday)));

										if (months > 0) {
											result = months + ' мес.';
										} else {
											result = days + ' дн.';
										}
									}
								}

								return result;
							},
							//format: 'd.m.Y',
							filter: {
								xtype: 'transFieldDelbut',
								translate: false,
								filterMap: 'personAgeText'
							}
						},
						{
							dataIndex: 'Adress_Name',
							text: 'Адрес',
							flex: 1,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpCallType_Name',
							text: 'Тип вызова',
							width: 120,
							filter: {xtype: 'swCmpCallTypeCombo'}
						},
						{
							dataIndex: 'CmpCallCard_IsExtraText',
							text: 'Вид вызова',
							width: 80,
							filter: {xtype: 'swCmpCallTypeIsExtraCombo'}
						},
						{
							dataIndex: 'CmpReason_Name',
							text: 'Повод',
							width: 150,
							filter: {
								xtype: 'cmpReasonCombo',
								autoFilter: false
							}
						},
						{dataIndex: 'CmpCallCardEventType_Name', text: 'Событие', width: 120,
							filter: {
								xtype: 'swCmpCallCardEventTypeCombo', translate: false,
								matchFieldWidth: false
							}
						},
						{
							dataIndex: 'CmpCallCard_Urgency',
							text: 'Срочность',
							width: 130,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'LpuBuilding_Name',
							text: 'Подразделение СМП',
							width: 130,
							filter: {xtype: 'smpUnitsNestedCombo',displayTpl: '<tpl for=".">{LpuBuilding_fullName}</tpl>'}
						},
						{
							dataIndex: 'DuplicateAndActiveCall_Count',
							text: 'Дубли / Акт. зв.',
							width: 100,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpResult_Name',
							text: 'Результат выезда ',
							width: 130,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
					],
					requires: [
						'Ext.ux.GridHeaderFilters',
						'Ext.grid.feature.Grouping'
					],
					plugins: [
						Ext.create('Ext.ux.GridHeaderFilters',
							{
								enableTooltip: false,
								reloadOnChange:true,
								//enableFunctionOnEnter:true,
								//functionOnEnter:function(){
									//me.searchCmpCalls();
								//}
							}
						)
					],
					features: [{
						ftype: 'grouping',
						refId: 'GroupingTableGridFeature',
						enableGroupingMenu: false,
						groupHeaderTpl: Ext.create('Ext.XTemplate',
							'<div>{name:this.formatName} ({[this.getCount(values)]})</div>',
							{
								formatName: function(name) {
									var groupname = '';
									switch (name)
									{
										case 1: {groupname = 'Вызовы с превышением времени ожидания назначения на бригаду'; break;}
										case 2: {groupname = 'Прочие вызовы'; break;}

										default: {groupname = 'Неизвестный статус'; break;}
									}
									return groupname
								},
								getCount: function(group) {
									return group.children.length;
								}
							}
						),
						hideGroupedHeader: true,
						startCollapsed: false
					}],

					listeners: {
						render: function(){
							//me.searchCmpCalls();
						},
						afterrender: function(){
							var tabpanel = me.up('tabpanel');

							var pressedkeyg = new Ext.util.KeyMap({
								target: me.el,
								binding: [
									{
										key: [Ext.EventObject.ENTER],
										fn: function(){me.searchCmpCalls()}
									}
								]
							});

							if(tabpanel){
								tabpanel.on('tabchange', function( tabPanel, newCard, oldCard, eOpts){
									var currentCard = newCard.down('panel[refId=CmpCallsUnderControlList]');

									if(currentCard){
										me.searchCmpCalls();
									};

								});
							};
						},
						itemcontextmenu: function(grid, record, item, index, event, eOpts){
							event.preventDefault();
							event.stopPropagation();
							me.showSubMenu(event.getX(), event.getY());
						},
						itemdblclick: function(){
							//var me = this,
							var recCard = this.getSelectionModel().getSelection()[0],
								card_id = recCard.get('CmpCallCard_id');
							me.showWndFromExt2('swCmpCallCardNewShortEditWindow',card_id);
						},
						cellkeydown: function(grid, td, cellIndex, record, tr, rowIndex, e){

							if(e.getKey() == Ext.EventObject.ENTER){
								//var me = this,
								var recCard = this.getSelectionModel().getSelection()[0],
									card_id = recCard.get('CmpCallCard_id');
								me.showWndFromExt2('swCmpCallCardNewShortEditWindow',card_id);
							}

						}
					}
				}
			]
		}];


		me.callParent(arguments);
	},

	searchCmpCalls: function(){
		var me = this,
			grid = me.down('grid');

		grid.store.reload();
	},
	showSubMenu: function(x,y){
		var me = this,
			grid = me.down('grid'),
			recCard = grid.getSelectionModel().getSelection()[0],
			card_id = recCard.get('CmpCallCard_id'),
			card112_id = recCard.get('CmpCallCard112_id'),
			closecard_id = recCard.get('CmpCloseCard_id'),
			recCardGroupId = parseInt(recCard.get('CmpCallCardStatusType_id')),
			emergencyTeamStore = Ext.getStore('common.HeadDoctorWP.store.EmergencyTeamStore');

		var subMenu = Ext.create('Ext.menu.Menu', {
			plain: true,
			renderTo: Ext.getBody(),
			items: [
				{
					xtype: 'button',
					text: 'Активный звонок',
					name: 'activeCall',
					refId: 'activeCall',
					handler: function(){
						var params = {},
							callIsOverdue = ((new Date() - recCard.data.CmpCallCard_prmDate) / 86400000) >= 1? true: false;
						params = {
							'CmpCallCard_DayNumberRid': recCard.raw.CmpCallCard_Numv, //связь с первичным обращением
							'CmpCallCard_rid' : recCard.raw.CmpCallCard_id,
							'region_id' : getGlobalOptions().region.number,
							'typeEditCard' : 'CallUnderControl',
							'callIsOverdue' : callIsOverdue
						};
						subMenu.close();
						getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
							showByDP: true,
							onClose: function() {
								me.searchCmpCalls();
							},
							onSaveByDp: function(card_id){
								getWnd('swWorkPlaceSMPDispatcherCallWindow').close();

								me.searchCmpCalls(function(){});

							},
							params: params
						});
					}.bind(this)
				},
				{
					text: 'На бригаду',
					hideOnClick: false,
					//hidden: getRegionNick().inlist(['ufa']),
					itemId: 'TeamsToCallDynamicOpenSubMenu',
					disabled: (!Ext.Array.contains([1,2,18], recCardGroupId) || (!emergencyTeamStore.getCount())), //Активно для вызовов из группы.... а хз какой нет тз
					handler: function(i){
						i.cancelDeferHide();
						i.doExpandMenu();
					},
					menu: {
						xtype: 'menu',
						showSeparator: false,
						itemId: 'TeamsToCallDynamicSubMenu',
						listeners: {
							show: function(sm){

							},
							click: function(m,i)
							{
								var team_id = i.value,
									teamRec = emergencyTeamStore.findRecord('EmergencyTeam_id', team_id);

								if (teamRec.get('EmergencyTeamStatus_Code') == '36') {
									setTimeout(function(){
										Ext.Msg.alert('Ошибка','Бригада в статусе ожидания принятия.');
									},1000);
								}

								//только свободные бригады и свободные вызовы
								if ( !recCard || typeof recCard == 'undefined' || recCard.get('EmergencyTeam_id') ) {
									return false;
								}

								Ext.MessageBox.confirm('Назначение бригады на вызов', ' Назначить выбранную бригаду на вызов?', function (btn) {
									if (btn === 'yes') {
										me.setEmergencyTeamToCall(recCard, teamRec);
									} else {

									}
								}, this);
							}
						}
					}
				},
				{
					text: 'Снять с контроля',
					disabled: !(recCard.get('CmpCallCard_isControlCall') == 2),
					handler: function(){
						me.setCallToControl(recCard, false);
					}
				},

				{
					text: 'Талон вызова',
					handler: function(){
						subMenu.close();
						me.showWndFromExt2('swCmpCallCardNewShortEditWindow',card_id);
					}
				},
				{
					text: 'Карта вызова',
					hidden: !(closecard_id > 0),
					handler: function(){
						subMenu.close();
						me.showWndFromExt2('swCmpCallCardNewCloseCardWindow',card_id);
					}
				},
				{
					text: 'История вызова',
					handler: function(){
						subMenu.close();

						var callCardHistoryWindow = Ext.create('sw.tools.swCmpCallCardHistory',{
							card_id: card_id
						});
						callCardHistoryWindow.show();
					}
				},
				{
					text: 'Прослушать аудиозапись',
					hidden: !(recCard.get('CmpCallRecord_id')),
					handler: function(){
						subMenu.close();

						Ext.create('common.tools.swCmpCallRecordListenerWindow',{
							record_id : recCard.get('CmpCallRecord_id')
						}).show();
					}
				}
			]
		});

		var subMenuEmergencyTeamsToCall = subMenu.down('menu[itemId=TeamsToCallDynamicSubMenu]');

		emergencyTeamStore.each(function(rec){
			if( rec.get('EmergencyTeamDuty_isNotFact')==1 ) {
				// Бригады, плановая смена которых закончилась, но не закончилась фактическая смена будут НЕдоступны для назначения на вызов #94154
				return;
			}
			subMenuEmergencyTeamsToCall.add({
				text: rec.get('EmergencyTeam_Num')+' '+rec.get('EmergencyTeamStatus_Name'),
				value: rec.get('EmergencyTeam_id')
			});
		});
		if( subMenuEmergencyTeamsToCall.items.length == 0) subMenu.down('[itemId=TeamsToCallDynamicOpenSubMenu]').disable();

		subMenu.showAt(x,y);
	},
	showWndFromExt2: function(wnd, card_id){

		if(Ext.isEmpty(wnd) || Ext.isEmpty(card_id)){
			return;
		}
		var me = this,
			title = (wnd == 'swCmpCallCardNewCloseCardWindow') ? 'Карта вызова: ' : 'Талон вызова',
			action = 'edit';

		if(	getRegionNick().inlist(['ufa', 'perm'])	){
			action = 'view';
			title += 'Просмотр';
		}
		else{
			title += 'Редактирование';
		}

		new Ext.Window({
			id: "myFFFrame",
			title: title,
			header: false,
			extend: 'sw.standartToolsWindow',
			toFrontOnShow: true,
			//width : '100%',
			//modal: true,
			style: {
				'z-index': 90000
			},
			//height: '90%',
			//layout : 'fit',
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			maximized: true,
			constrain: true,
			renderTo: Ext.getCmp('inPanel').body,
			items : [{
				xtype : "component",
				autoEl : {
					tag : "iframe",
					src : "/?c=promed&getwnd=" + wnd + "&act=" + action + "&showTop=1&cccid="+card_id
				}
			}]
		}).show();
	},
	showCmpCallCard112: function(card_id){
		if(!card_id )
			return;
		var callcard112 = Ext.create('sw.tools.swCmpCallCard112',{
			view: 'view',
			card_id: card_id
		});
		callcard112.show();
	},
	// установка/снятие вызова с контроля
	setCallToControl: function(recCard, setControl){
		var me = this;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setCmpCallCardToControl',
			params: {
				CmpCallCard_id : recCard.get('CmpCallCard_id'),
				CmpCallCard_isControlCall : setControl ? 2 : 1
			},
			success: function (response) {
				var obj = Ext.decode(response.responseText);
				if (obj.success) {
					me.gridStore.reload();
				}
			}
		});
	},

	setEmergencyTeamToCall: function(selectedCallRec, selectedTeamRec){
		var	cntr = this,
			hdWin = Ext.ComponentQuery.query('swHeadDoctorWorkPlace')[0],
			selectedTeamId = selectedTeamRec.get('EmergencyTeam_id'),
			selectedCallId = selectedCallRec.get('CmpCallCard_id'),
			selectedTeamNum = selectedTeamRec.get('EmergencyTeam_Num'),
			selectedCallNum = selectedCallRec.get('CmpCallCard_Numv'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');

		if ( !selectedTeamId || !selectedCallId ) {
			setTimeout(function(){
				Ext.Msg.alert('Ошибка','Перед назначением, сначала выберите вызов, а затем бригаду.');
			},1000);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.getBody(),{msg:"Сохранение данных..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=setEmergencyTeamWithoutSending',
			params: {
				EmergencyTeam_id: selectedTeamId,
				CmpCallCard_id: selectedCallId
			},
			success: function(response, opts){
				loadMask.hide();
				var obj = Ext.decode(response.responseText);
				if (obj.success) {

					var socketData = {EmergencyTeam_id: selectedTeamId, CmpCallCard_id: selectedCallId};
					if (hdWin.socket) {
						hdWin.socket.emit('setEmergencyTeamToCall', socketData, function (data) {
							log('NODE emit setEmergencyTeamToCall : apk=' + data);
						});

						hdWin.socket.emit('changeEmergencyTeamStatus', socketData, 'changeStatus', function (data) {
							log('NODE emit changeStatus : apk=' + data);
						});
					}
					Ext.Ajax.request({
						url: '/?c=Messages&m=sendNotificationEmergencyTeam',
						params: {
							EmergencyTeam_id: selectedTeamRec.get('EmergencyTeam_id'),
							EmergencyTeam_Num: selectedTeamRec.get('EmergencyTeam_Num'),
							Person_FIO: selectedCallRec.get('Person_FIO'),
							Urgency: selectedCallRec.get('CmpCallCard_Urgency'),
							Numv: selectedCallRec.get('CmpCallCard_Numv'),
							Adress_Name: selectedCallRec.get('Adress_Name'),
							MedService_id: getGlobalOptions().CurMedService_id
						},
						success: function (response, opts) {

							Ext.getStore('common.HeadDoctorWP.store.EmergencyTeamStore').reload();

							Ext.MessageBox.confirm('Сообщение',
								'Бригада №' + selectedTeamNum + ' назначена на вызов №' + selectedCallNum + '.' + '</br>Распечатать контрольный талон?', function (btn) {
									if (btn === 'yes') {
										if (getRegionNick().inlist(['ufa', 'krym'])) {
											var location = '/?c=CmpCallCard&m=printCmpCallCardHeader&CmpCallCard_id=' + selectedCallId;
											var win = window.open(location);
										} else {
											this.printControlBill({
												EmergencyTeam_id: selectedTeamId,
												CmpCallCard_id: selectedCallId
											});
										}
									}
								}.bind(this));
						}.bind(this),
						failure: function (response, opts) {
							Ext.MessageBox.show({
								title: 'Ошибка',
								msg: 'Во время отправки СМС произошла непредвиденная ошибка.',
								buttons: Ext.MessageBox.OK
							});
						}
					});

				} else {
					Ext.Msg.alert('Ошибка','Во время назначения бригады произошла непредвиденная ошибка. Перезагрузите страницу и попробуйте выполнить действие заново. Если ошибка повторится, обратитесь к администратору.');
					log('/?c=CmpCallCard&m=setEmergencyTeamWithoutSending query error.');
					log( response );
				}
			}.bind(this),
			failure: function(response, opts){
				loadMask.hide();
				Ext.MessageBox.show({title:'Ошибка',msg:'Во время выполнения запроса произошла непредвиденная ошибка.',buttons:Ext.MessageBox.OK});
				log({response:response,opts:opts});
			}
		});
	}
});