Ext6.define('common.EMK.PersonForm', {
	requires: [
		'common.EMK.SignalInfo.PersonLpuInfoPanel',
		'common.EMK.SignalInfo.PersonPrivilegePanel',
		'common.EMK.SignalInfo.PersonBloodGroupPanel',
		'common.EMK.SignalInfo.PersonCardioRiskCalcPanel',
		'common.EMK.SignalInfo.PersonMedHistoryPanel',
		'common.EMK.SignalInfo.PersonAllergicReactionPanel',
		'common.EMK.SignalInfo.PersonDispPanel',
		'common.EMK.SignalInfo.PersonDiagPanel',
        'common.EMK.SignalInfo.PersonAnthropometricPanel',
		getRegionNick()=='ufa' ? 'common.EMK.SignalInfo.PersonRacePanel' : null,
		'common.EMK.SignalInfo.HeadCircumferencePanel',
		'common.EMK.SignalInfo.ChestCircumferencePanel',
		'common.EMK.SignalInfo.PersonFeedingTypePanel',
		'common.EMK.SignalInfo.PersonSvidPanel',
		'common.EMK.SignalInfo.PersonSurgicalPanel',
		'common.EMK.SignalInfo.PersonDirFailPanel',
		'common.EMK.SignalInfo.PersonEvnPLDispPanel',
		'common.EMK.SignalInfo.PersonProfilePanel',
		getRegionNick()=='vologda' ? 'common.EMK.SignalInfo.PersonDrugRequestPanel' : null,
		'common.EMK.SignalInfo.PersonMantuReactionPanel',
		'common.EMK.SignalInfo.PersonInoculationPanel',
		'common.EMK.SignalInfo.PersonInoculationPlanPanel',
		getRegionNick() != 'kz' ? 'common.EMK.SignalInfo.PersonQuarantinePanel' : null,
		'common.EMK.PersonBottomPanel'
	],
	extend: 'Ext6.Panel',
	layout: 'border',
	region: 'center',
	border: false,
	loadData: function(options) {
		var me = this;
		if(options && options.Person_id)
			me.Person_id = options.Person_id;
		var base_form = me.formPanel.getForm();
		me.mask('Загрузка...');
		Ext6.suspendLayouts();
		base_form.reset();
		me.PersonLpuInfoPanel.setTitleCounter(0);
		me.RiskFactorPanel.setTitleCounter(0);
		me.PersonPrivilegePanel.setTitleCounter(0);
		me.PersonBloodGroupPanel.setTitleCounter(0);
		me.PersonCardioRiskCalcPanel.setTitleCounter(0);
		me.PersonMedHistoryPanel.setTitleCounter(0);
		me.PersonAllergicReactionPanel.setTitleCounter(0);
		me.PersonDispPanel.setTitleCounter(0);
		me.PersonDiagPanel.setTitleCounter(0);
        me.PersonAnthropometricPanel.setTitleCounter(0);
		if ( getRegionNick() == 'ufa' ) {
			me.PersonRacePanel.setTitleCounter(0);
		}
		me.HeadCircumferencePanel.setTitleCounter(0);
		me.ChestCircumferencePanel.setTitleCounter(0);
		me.PersonFeedingTypePanel.setTitleCounter(0);
		me.PersonSvidPanel.setTitleCounter(0);
		me.PersonSurgicalPanel.setTitleCounter(0);
		me.PersonDirFailPanel.setTitleCounter(0);
		me.PersonEvnPLDispPanel.setTitleCounter(0);
		me.PersonProfilePanel.setTitleCounter(0);
		if (getRegionNick()=='vologda') {
			me.PersonDrugRequestPanel.setTitleCounter(0);
		}
		if(getRegionNick()!='perm') {
			me.PersonMantuReactionPanel.setTitleCounter(0);
			me.PersonInoculationPanel.setTitleCounter(0);
			me.PersonInoculationPlanPanel.setTitleCounter(0);
		}
		if(getRegionNick()!='kz') {
			me.PersonQuarantinePanel.setTitleCounter(0);
		}
		Ext6.resumeLayouts(true);
		base_form.load({
			params: {
				Person_id: me.Person_id
			},
			FeedingTypeHide: function(){
				var age;
				var Birthday = base_form.findField('Person_Birthday').value;
				var dayBirthday = +(Birthday.substring(0, 2));
				var monthBirthday = +(Birthday.substring(3, 5));
				var yearBirthday = +(Birthday.substring(6));
				var now = new Date();
				var day = now.getDate();
				var month = now.getMonth() +1;
				var year = now.getFullYear();
				if(monthBirthday < month){
					age = year-yearBirthday;
				}
				else if(monthBirthday == month){
					if(dayBirthday <= day){
						age = year-yearBirthday;
					}
					else{
						age = year-yearBirthday-1;
					}
				}
				else{
					age = year-yearBirthday-1;
				}

				if(age > 5) {
					base_form.findField('FeedingType_Name').hide();
				}
				else{
					base_form.findField('FeedingType_Name').show();
				}
			},
			success: function (form, action) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}

				me.unmask();

				if (action.response && action.response.responseText) {
					Ext6.suspendLayouts();
					var data = Ext6.decode(action.response.responseText);
					if (data[0]) {
						me.PersonLpuInfoPanel.setTitleCounter(data[0].PersonLpuInfoCount);
						me.PersonLpuInfoPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonPrivilegePanel.setTitleCounter(data[0].PersonPrivilegeCount);
						me.PersonPrivilegePanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id,
                            userMedStaffFact: me.ownerWin.userMedStaffFact,
							Person_Birthday: data[0].Person_Birthday ? data[0].Person_Birthday : null,
							Person_deadDT: data[0].Person_deadDT ? data[0].Person_deadDT : null
						});
						me.PersonBloodGroupPanel.setTitleCounter(data[0].PersonBloodGroupCount);
						me.PersonBloodGroupPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});

						me.PersonCardioRiskCalcPanel.setTitleCounter(data[0].PersonCardioRiskCalcPanelCount);
						me.PersonCardioRiskCalcPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id,
							Person_Birthday: data[0].Person_Birthday
						});



						me.PersonMedHistoryPanel.setTitleCounter(data[0].PersonMedHistoryCount);
						me.PersonMedHistoryPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonAllergicReactionPanel.setTitleCounter(data[0].PersonAllergicReactionCount);
						me.PersonAllergicReactionPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonDispPanel.setTitleCounter(data[0].PersonDispCount);
						me.PersonDispPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						// me.PersonDiagPanel.setTitleCounter(data[0].EvnDiagCount); нет смысла, запрос слишком сложный, проще сразу загрузить данные тогда уж
						me.PersonDiagPanel.setParams({
							Person_id: data[0].Person_id,
							PersonEvn_id: me.ownerWin.PersonEvn_id,
							Server_id: data[0].Server_id,
							userMedStaffFact: me.ownerWin.userMedStaffFact
						});
						me.PersonDiagPanel.load();

						me.PersonAnthropometricPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id,
							Evn_id: data[0].Evn_id,
							PersonEvn_id: data[0].PersonEvn_id,
							isEmk: true
						});
						me.PersonAnthropometricPanel.load();
						if ( getRegionNick() == 'ufa' ) {
							me.PersonRacePanel.setTitleCounter(data[0].PersonRaceCount);
							me.PersonRacePanel.setParams({
								Person_id: data[0].Person_id,
								Server_id: data[0].Server_id
							});
						}
						me.HeadCircumferencePanel.setTitleCounter(data[0].HeadCircumferenceCount);
						me.HeadCircumferencePanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.ChestCircumferencePanel.setTitleCounter(data[0].ChestCircumferenceCount);
						me.ChestCircumferencePanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonFeedingTypePanel.setTitleCounter(data[0].PersonFeedingTypeCount);
						me.PersonFeedingTypePanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id,
							PersonChild_id: data[0].PersonChild_id
						});
						me.PersonSvidPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonSvidPanel.load();
						me.PersonSurgicalPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonSurgicalPanel.load();
						me.PersonDirFailPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonDirFailPanel.load();
						me.PersonEvnPLDispPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonEvnPLDispPanel.load();

						me.PersonMantuReactionPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonMantuReactionPanel.setTitleCounter(data[0].PersonMantuReactionCount);
						me.PersonMantuReactionPanel.load();

						me.PersonInoculationPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonInoculationPanel.setTitleCounter(data[0].PersonInoculationCount);
						me.PersonInoculationPanel.load();

						me.PersonInoculationPlanPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id
						});
						me.PersonInoculationPlanPanel.setTitleCounter(data[0].PersonInoculationPlanCount);
						me.PersonInoculationPlanPanel.load();


						me.PersonProfilePanel.setParams({
							Person_id: data[0].Person_id,
							Person_Birthday: data[0].Person_Birthday,
							userMedStaffFact: me.ownerWin.userMedStaffFact
						});
						me.PersonProfilePanel.load();

						if (getRegionNick()=='vologda') {
							me.PersonDrugRequestPanel.setParams({
								Person_id: data[0].Person_id
							});
							me.PersonDrugRequestPanel.load();
						}

						if(getRegionNick()!='kz') {
							me.PersonQuarantinePanel.setParams({
								userMedStaffFact: me.ownerWin.userMedStaffFact,
								Person_id: data[0].Person_id,
								Server_id: data[0].Server_id
							});
							me.PersonQuarantinePanel.setTitleCounter(data[0].PersonInoculationPlanCount);
							me.PersonQuarantinePanel.load();
						}

						me.bottomPanel.setParams({
							Person_id: data[0].Person_id,
							Server_id: data[0].Server_id,
							PersonEvn_id: data[0].PersonEvn_id,
							userMedStaffFact: me.ownerWin.userMedStaffFact
						});

						var dm_button = me.queryById('dm-temperature-button');
						dm_button.setParams(data[0]);
						dm_button.setAction(data[0].MonitorTemperatureStartDate ? 'view':'add');
					}
					Ext6.resumeLayouts(true);
				}
				this.FeedingTypeHide();
			},
			failure: function (form, action) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});

	},
	printMedCard: function() {
		var me = this;

		var PersonCard_id = 0;

		if (getRegionNick() == 'ufa') {
			printMedCard4Ufa(PersonCard_id);
		} else if (getRegionNick().inlist(['adygeya', 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak', 'kareliya', 'penza', 'kaluga'])) {
			printBirt({
				'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
				'Report_Params': '&paramPerson=' + me.Person_id + '&paramPersonCard=' + PersonCard_id + '&paramLpu=' + getLpuIdForPrint(),
				'Report_Format': 'pdf'
			});
		} else {
			me.mask(LOAD_WAIT);
			Ext6.Ajax.request({
				url: '/?c=PersonCard&m=printMedCard',
				params: {
					PersonCard_id: PersonCard_id,
					Person_id: me.Person_id
				},
				callback: function(options, success, response) {
					me.unmask();

					if (success) {
						var responseData = Ext6.JSON.decode(response.responseText);

						if (getRegionNick() == 'ekb') {
							if (!Ext6.isEmpty(responseData.result1)) {
								openNewWindow(responseData.result1);
							}

							if (!Ext6.isEmpty(responseData.result2)) {
								openNewWindow(responseData.result2);
							}
						}
						else if (!Ext6.isEmpty(responseData.result)) {
							openNewWindow(responseData.result);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при получении данных для печати'));
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при получении данных для печати'));
					}
				}
			});
		}
	},
	printDiagPanel: function() {
			var me = this;
			var params= {
				object: 'DiagList',
				object_id: 'Person_id',
				object_value: me.Person_id,
				parent_object_value: me.Person_id,
				parent_object_id: 'Person_id',
				Person_id: me.Person_id
			};
			Ext6.Ajax.request({
				url: '/?c=Template&m=getEvnForm',
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( response_obj.success ) {
								var templ = (response_obj.html) ? response_obj.html : '';
								var id_salt = Math.random();
								var win_id = 'printEvent' + Math.floor(id_salt*10000);
								var win = window.open('', 'win_id');
								win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /><style>td,th{border:solid 1px black;text-align:center;padding:3px}</style></head><body id="rightEmkPanelPrint">'+ templ +'</body></html>');
							}
						}
					}, params: params
			});

	},
	printPersonLpuInfo: function(data) {
		var me = this;
		me.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: '/?c=Person&m=savePersonLpuInfo',
			failure: function() {
				me.unmask();
			},
			success: function(response) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj && response_obj.Error_Msg) {
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
					return false;
				} else if (response_obj && !Ext6.isEmpty(response_obj.PersonLpuInfo_id)) {
					var lan = (getAppearanceOptions().language == 'ru' ? 1 : 2);
					if (data.IsAgree == 1) {
						var template = 'Otkaz';
						var parLang = '';
					} else {
						var template = 'Soglasie';
						var parLang = '&paramLang=' + lan;
					}
					if (getRegionNick() == 'kz') {
						printBirt({
							'Report_FileName': 'Person' + template + '_PersData.rptdesign',
							'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id + parLang,
							'Report_Format': 'pdf'
						});
					} else {
						printBirt({
							'Report_FileName': 'Person' + template + '_PersData.rptdesign',
							'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
							'Report_Format': 'pdf'
						});
					}
				}
			}.createDelegate(this),
			params: {
				Person_id: me.Person_id,
				PersonLpuInfo_IsAgree: data.IsAgree
			}
		});
	},
	initComponent: function() {
		var me = this,
			regNick = getRegionNick();

		this.titleLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			style: 'font-size: 18px; padding: 10px;',
			html: 'Сигнальная информация'
		});

		this.toolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, {
				userCls: 'button-without-frame black',
				style: {
					'color': 'transparent'
				},
				text: langs('Печать'),
				tooltip: langs('Печать'),
				menu: new Ext6.menu.Menu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Печать медицинской карты',
						handler: function() {
							me.printMedCard();
						}
					}, {
						text: 'Печать согласия на обработку перс. данных',
						handler: function() {
							me.printPersonLpuInfo({IsAgree: 2});
						}
					}, {
						text: 'Печать отзыва согласия на обработку перс. данных',
						handler: function() {
							me.printPersonLpuInfo({IsAgree: 1});
						}
					}, {
						text: 'Печать списка уточненных диагнозов',
						handler: function() {
							me.printDiagPanel();
						}

					}]
				})
			}]
		});

		this.titlePanel = Ext6.create('Ext6.Panel', {
			region: 'north',
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: 10,
				items: [
					this.titleLabel
				]
			}, this.toolPanel
			],
			xtype: 'panel'
		});

		this.PersonPanel = Ext6.create('swPanel', {
			title: 'ДАННЫЕ ПАЦИЕНТА',
			layout: 'column',
			bodyPadding: '10px 10px 20px 30px',
			cls: 'personPanel accordion-panel-window',
			items: [{
				layout: 'anchor',
				border: false,
				columnWidth: 1,
				defaults: {
					anchor: '90%',
					labelWidth: 135,
					border: false
				},
				items: [{
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonCard_id',
					xtype: 'hidden'
				}, {
					name: 'Person_Birthday',
					xtype: 'hidden'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Пол',
					allowBlank: false,
					name: 'Sex_Name'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Дата рождения',
					allowBlank: false,
					name: 'Person_BirthDay'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Соц. статус',
					allowBlank: false,
					name: 'SocStatus_Name'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'СНИЛС',
					allowBlank: false,
					name: 'Person_Snils',
					hidden: regNick == 'kz'
				}, {
					xtype: 'displayfield',
					fieldLabel: (regNick == 'kz' ? 'ИИН' : 'ИНН'),
					allowBlank: false,
					name: 'Person_Inn'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Регистрация',
					allowBlank: false,
					name: 'Person_UAddress'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Проживает',
					allowBlank: false,
					name: 'Person_PAddress'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Полис',
					hidden: getRegionNick() == 'kz',
					allowBlank: false,
					name: 'Person_Polis'
				}, {
					xtype: 'displayfield',
					hidden: getRegionNick() == 'kz',
					fieldLabel: 'Документ',
					allowBlank: false,
					name: 'Person_Document'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Работа',
					allowBlank: false,
					name: 'Person_Job'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Должность',
					allowBlank: false,
					name: 'Person_Post'
				}, {
					layout: 'hbox',
					items: [
						{
							xtype: 'displayfield',
							fieldLabel: 'Прикрепление',
							allowBlank: false,
							margin: '0 0 5 0',
							name: 'Person_Attach',
							labelWidth: 135
						}, {
							xtype: 'button',
							cls: 'button-without-frame attach-history',
							text: 'История прикреплений',
							handler: function(){

								var params = {
									callback: Ext6.emptyFn, // почему-то в форме swPersonCardHistoryWindow вызывается только при нажатии на кн. "помощь"
									onHide: function(data){
										// нужно обновить секцию person_data, пока будем перезагружать всю сигн.информацию
										me.loadData();
									}
								};
								if(me.ownerWin && me.ownerWin.PersonInfoPanel)
									me.ownerWin.PersonInfoPanel.openForm('swPersonCardHistoryWindow','XXX_id',params,'edit',langs('История прикрепления'));
							}
						}
					]
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Семейное положение',
					allowBlank: false,
					name: 'FamilyStatus_Name'
				}, {
					layout: 'hbox',
					items: [
						{
							xtype: 'displayfield',
							fieldLabel: 'Дистанционный мониторинг',
							value: '',
							name: 'DistMonitoringTemperature',
							labelWidth: 135
						}, {
							xtype: 'button',
							itemId: 'dm-temperature-button',
							cls: 'button-without-frame attach-history emk-dm-temperature-button',
							text: 'Добавить в программу мониторинга температуры',
							action: 'add',
							DateStart: '',
							params: {},
							setAction: function(action) {
								this.action=action;
								var dt = this.DateStart;
								if(dt) dt = dt.dateFormat('d.m.Y');
								this.setText(action=='add'?'Добавить в программу мониторинга температуры': (dt?'температура с '+dt:''));
							},
							setParams: function(data) {
								this.params.Person_id=data.Person_id;
								this.params.Server_id=data.Server_id;
								this.params.Person_Birthday=data.Person_Birthday;
								this.DateStart = Date.parseDate(data.MonitorTemperatureStartDate,'d.m.Y');
								this.params.Lpu_id = data.Lpu_id;
							},
							handler: function(){
								var _this = this;
								if(Ext6.isEmpty(this.params.Lpu_id) || this.params.Lpu_id!=getGlobalOptions().lpu_id) {
									Ext6.Msg.alert('Сообщение','Пациент не прикреплен к данной МО');
									return;
								}
								if(_this.action=='add') {
									_this.disable();
									var params = {
										Person_id:this.params.Person_id, 
										Label_id:7, 
										action: 'add',
										Person_Birthday: this.params.Person_Birthday,
										DateFormat: 'd.m.Y',
										callback: function(data) {
											_this.DateStart = data.dateConsent;
											_this.setAction('view');
											_this.enable();
											me.ownerWin.queryById('ObserveChartPanel').ownerWin.down('[refId=ObserveChartPanel]').reload();
										}
									};
									getWnd('swRemoteMonitoringConsentWindow').show(params);
								} else {
									getWnd('swRemoteMonitoringWindow').show({Label_id: 7, Person_id: _this.params.Person_id});
								}
							}
						}
					]
				},{
					xtype: 'displayfield',
					fieldLabel: 'Способ вскармливания',
					allowBlank: false,
					name: 'FeedingType_Name',
				},
				]
			}, {
				border: false,
				width: 150,
				items: [{
					userCls: 'person-photo',
					width: 100,
					height: 100,
					handler: function() {
						inDevelopmentAlert();
					},
					xtype: 'button'
				}]
			}]
		});

		this.PersonLpuInfoPanel = Ext6.create('common.EMK.SignalInfo.PersonLpuInfoPanel', {cls:'accordion-panel-window'});
		this.RiskFactorPanel = Ext6.create('swPanel', {
			title: 'ФАКТОРЫ РИСКА',
			allTimeExpandable: false,
			cls: 'accordion-panel-window',
			btnAddClickEnable: true,
			collapseOnOnlyTitle: true,
			onBtnAddClick: function(){
				inDevelopmentAlert();
			},
			collapsed: true,
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					inDevelopmentAlert();
				}
			}]
		}); // что тут будет выводиться пока не понятно, но дизайнер просил добавить
		this.PersonPrivilegePanel = Ext6.create('common.EMK.SignalInfo.PersonPrivilegePanel', {cls:'accordion-panel-window'});
		this.PersonBloodGroupPanel = Ext6.create('common.EMK.SignalInfo.PersonBloodGroupPanel', {cls:'accordion-panel-window'});
		this.PersonCardioRiskCalcPanel = Ext6.create('common.EMK.SignalInfo.PersonCardioRiskCalcPanel', {cls:'accordion-panel-window'});
		this.PersonMedHistoryPanel = Ext6.create('common.EMK.SignalInfo.PersonMedHistoryPanel', {cls:'accordion-panel-window'});
		this.PersonAllergicReactionPanel = Ext6.create('common.EMK.SignalInfo.PersonAllergicReactionPanel', {cls:'accordion-panel-window'});
		this.PersonDispPanel = Ext6.create('common.EMK.SignalInfo.PersonDispPanel', {cls:'accordion-panel-window'});
		this.PersonDiagPanel = Ext6.create('common.EMK.SignalInfo.PersonDiagPanel', {cls:'accordion-panel-window'});
		this.PersonAnthropometricPanel = Ext6.create('common.EMK.SignalInfo.PersonAnthropometricPanel', {
			itemID: 'PersonAnthropometricPanel',
			ownerWin: me.ownerWin,
			ownerPanel: me.ownerPanel,
			cls: 'accordion-panel-window'
		});
		if ( getRegionNick() == 'ufa' ) {
			this.PersonRacePanel = Ext6.create('common.EMK.SignalInfo.PersonRacePanel', {cls: 'accordion-panel-window'});
		}
		this.HeadCircumferencePanel = Ext6.create('common.EMK.SignalInfo.HeadCircumferencePanel', {cls:'accordion-panel-window'});
		this.ChestCircumferencePanel = Ext6.create('common.EMK.SignalInfo.ChestCircumferencePanel', {cls:'accordion-panel-window'});
		this.PersonFeedingTypePanel = Ext6.create('common.EMK.SignalInfo.PersonFeedingTypePanel', {cls:'accordion-panel-window'});
		this.PersonSvidPanel = Ext6.create('common.EMK.SignalInfo.PersonSvidPanel', {cls:'accordion-panel-window'});
		this.PersonSurgicalPanel = Ext6.create('common.EMK.SignalInfo.PersonSurgicalPanel', {cls:'accordion-panel-window'});
		this.PersonDirFailPanel = Ext6.create('common.EMK.SignalInfo.PersonDirFailPanel', {cls:'accordion-panel-window'});
		this.PersonEvnPLDispPanel = Ext6.create('common.EMK.SignalInfo.PersonEvnPLDispPanel', {cls:'accordion-panel-window'});
		this.PersonProfilePanel = Ext6.create('common.EMK.SignalInfo.PersonProfilePanel', {cls:'accordion-panel-window', ownerWin: me.ownerWin});
		if (getRegionNick()=='vologda') {
			this.PersonDrugRequestPanel = Ext6.create('common.EMK.SignalInfo.PersonDrugRequestPanel', {cls:'accordion-panel-window'});
		}
		this.PersonMantuReactionPanel = Ext6.create('common.EMK.SignalInfo.PersonMantuReactionPanel', {cls:'accordion-panel-window'});
		this.PersonInoculationPanel = Ext6.create('common.EMK.SignalInfo.PersonInoculationPanel', {cls:'accordion-panel-window'});
		this.PersonInoculationPlanPanel = Ext6.create('common.EMK.SignalInfo.PersonInoculationPlanPanel', {cls:'accordion-panel-window'});
		if (getRegionNick() !='kz') {
			this.PersonQuarantinePanel = Ext6.create('common.EMK.SignalInfo.PersonQuarantinePanel', {cls:'accordion-panel-window'});
		}

		this.bottomPanel = Ext6.create('common.EMK.PersonBottomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin
		});

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			border: true,
			defaults: {
				margin: "0px 0px 2px 0px"
			},
			url: '/?c=EMK&m=loadPersonForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'Person_id'},
						{name: 'PersonCard_id'},
						{name: 'Sex_Name'},
						{name: 'Person_BirthDay'},
						{name: 'SocStatus_Name'},
						{name: 'Person_Snils'},
						{name: 'Person_UAddress'},
						{name: 'Person_PAddress'},
						{name: 'Person_Polis'},
						{name: 'Person_Document'},
						{name: 'Person_Job'},
						{name: 'Person_Post'},
						{name: 'Person_Attach'},
						{name: 'FeedingType_Name'},
						{name: 'FamilyStatus_Name'}
					]
				})
			}),
			layout: {
				type: 'accordion',
				titleCollapse: false,
				animate: true,
				multi: true,
				activeOnTop: false
			},
			listeners: {
				'resize': function() {
					this.updateLayout();
				}
			},
			items: [
				me.PersonPanel,
				me.PersonLpuInfoPanel,
				me.RiskFactorPanel,
				me.PersonPrivilegePanel,
				me.PersonBloodGroupPanel,
				me.PersonCardioRiskCalcPanel,
				me.PersonMedHistoryPanel,
				me.PersonAllergicReactionPanel,
				me.PersonDispPanel,
				me.PersonDiagPanel,
                me.PersonAnthropometricPanel,
				getRegionNick() == 'ufa' ? me.PersonRacePanel : null,
				me.HeadCircumferencePanel,
				me.ChestCircumferencePanel,
				me.PersonFeedingTypePanel,
				me.PersonSvidPanel,
				me.PersonSurgicalPanel,
				me.PersonDirFailPanel,
				me.PersonEvnPLDispPanel,
				me.PersonMantuReactionPanel,
				me.PersonInoculationPanel,
				me.PersonInoculationPlanPanel,
				me.PersonProfilePanel,
				getRegionNick()=='vologda' ? me.PersonDrugRequestPanel : null,
				getRegionNick() !='kz' ? me.PersonQuarantinePanel : null
			]
		});

		this.scrollablePanel = Ext6.create('Ext6.panel.Panel', {
			region: 'center',
			flex: 400,
			bodyPadding: 10,
			scrollable: true,
			border: false,
			items: [
				this.formPanel
			]
		});

		Ext6.apply(this, {
			items: [
				this.titlePanel,
				this.scrollablePanel,
				this.bottomPanel
			]
		});

		me.callParent(arguments);
	}
});
