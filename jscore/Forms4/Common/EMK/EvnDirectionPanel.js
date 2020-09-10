/**
 * Панель направлений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnDirectionPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	title: 'НАПРАВЛЕНИЯ',
	isScreenOnko: false,
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	setParams: function(params) {
		var me = this;
		me.Evn_id = params.Evn_id;
		me.DopDispInfoConsent_id = params.DopDispInfoConsent_id;
		me.EvnClass_id = params.EvnClass_id;
		me.ownerPanel = params.ownerPanel;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		if(this.plusMenu)
			this.plusMenu.showBy(this);
		if (this.plusMenu.hidden == false)
			this.btnAddClick.setStyle('visibility','visible');
	},
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		this.EvnDirectionGrid.getStore().load({
			params: {
				EvnDirection_pid: me.Evn_id
				,DopDispInfoConsent_id: me.DopDispInfoConsent_id || null
			}
		});
	},
	deleteEvnDirection: function() {
		var me = this;

		var EvnDirection_id = me.EvnDirectionGrid.recordMenu.EvnDirection_id;
		if (EvnDirection_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление направления...');
					Ext6.Ajax.request({
						url: '/?c=EvnDirection&m=deleteEvnDirection',
						params: {
							EvnDirection_id: EvnDirection_id
						},
						callback: function () {
							me.unmask();
							me.load();
						}
					});
				}
			}, 'направление');
		}
	},
	cancelEvnDirection: function() {
		var me = this;

		var EvnDirection_id = me.EvnDirectionGrid.recordMenu.EvnDirection_id;
		if (EvnDirection_id) {
			var record = this.EvnDirectionGrid.getStore().findRecord('EvnDirection_id', EvnDirection_id);
			if (!record) {
				return false;
			}
			var params = {
				cancelType: 'cancel',
				ownerWindow: me,
				EvnDirection_id: EvnDirection_id,
				DirType_Code: record.get('DirType_Code'),
				TimetableGraf_id: record.get('TimetableGraf_id'),
				TimetableMedService_id: record.get('TimetableMedService_id'),
				TimetableResource_id: record.get('TimetableResource_id'),
				TimetableStac_id: record.get('TimetableStac_id'),
				EvnQueue_id: record.get('EvnQueue_id'),
				allowRedirect: false,
				userMedStaffFact: me.userMedStaffFact,
				personData: {
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					PersonEvn_id: me.PersonEvn_id
				},
				callback: function() {
					me.load();
				}
			};

			var piPanel = me.ownerWin.PersonInfoPanel;
			if (piPanel && piPanel.getFieldValue('Person_Surname')) {
				params.personData.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
				params.personData.Person_Surname = piPanel.getFieldValue('Person_Surname');
				params.personData.Person_Firname = piPanel.getFieldValue('Person_Firname');
				params.personData.Person_Secname = piPanel.getFieldValue('Person_Secname');
				params.personData.Person_IsDead = piPanel.getFieldValue('Person_IsDead');
			} else {
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
				return false;
			}

			sw.Promed.Direction.cancel(params);
		}
	},
	printEvnDirection: function() {
		var me = this;

		var EvnDirection_id = me.EvnDirectionGrid.recordMenu.EvnDirection_id;
		var row = me.EvnDirectionGrid.getStore().getAt(me.EvnDirectionGrid.recordMenu.rowIndex);
		var record = this.EvnDirectionGrid.getStore().findRecord('EvnDirection_id', EvnDirection_id);
		var addParams = '';
		// включена опция печати тестов с мнемоникой
		if (Ext.globalOptions.lis.PrintMnemonikaDirections)
		{
			addParams += '&PrintMnemonikaDirections=1';
		} // или просто опция печати исследований
		else if (Ext.globalOptions.lis.PrintResearchDirections)
		{
			addParams += '&PrintResearchDirections=1';
		}

		if (EvnDirection_id && row) {
			if(row.get('DirType_Code')==9) {//"на исследование"
				var birtParams = {
					'Report_FileName': 'printEvnDirection.rptdesign',
					'Report_Params': '&paramEvnDirection=' + EvnDirection_id + addParams,
					'Report_Format': 'pdf'
				};

				if (
					getRegionNick() == 'perm' &&
					!Ext.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
					Ext.globalOptions.lis.direction_print_form == 2
				) {
					Ext6.Ajax.request({
						url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
						params: {
							EvnDirection_id: params.EvnDirection_id
						},
						callback: function (options, success, response) {
							if (success) {
								var result = Ext6.util.JSON.decode(response.responseText);
								if (!Ext.isEmpty(result.MedServiceType_SysNick) && result.MedServiceType_SysNick != 'func') {
									birtParams.Report_FileName = 'printEvnDirectionCKDL.rptdesign';
								}
								printBirt(birtParams);
							}
						}
					});
				} else {
					printBirt(birtParams);
				}
			}else if(record.get('EvnDirectionHTM_id')){
				//направление на ВМП
				printBirt({
					'Report_FileName': 'printEvnDirection.rptdesign',
					'Report_Params': '&paramEvnDirection=' + EvnDirection_id,
					'Report_Format': 'pdf'
				});
			}else {
				sw.Promed.Direction.print({
					EvnDirection_id: EvnDirection_id
				});
			}
		}
	},
	openEvnDirectionEditWindow: function(action) {
		var me = this;

		var formParams = {},
			onEvnDirectionSave = function(data) {
				if (!data || !data.evnDirectionData) {
					return false;
				}

				me.load();
			};

		if ( action == 'add' || action == 'addtome' ) {
			// запись пациента к другому врачу с выпиской электр.направления
			var evnData = me.ownerPanel.getEvnData();

			var my_params = new Object({
				EvnDirection_id: 0,
				EvnDirection_pid: me.Evn_id,
				Diag_id: evnData.Diag_id,
				PersonEvn_id: me.PersonEvn_id,
				Person_id: me.Person_id,
				Server_id: me.Server_id,
				UserMedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
				userMedStaffFact: me.userMedStaffFact,
				formMode: 'vizit_PL'
			});

			var piPanel = me.ownerWin.PersonInfoPanel;
			if (piPanel && piPanel.getFieldValue('Person_Surname')) {
				my_params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
				my_params.Person_Surname = piPanel.getFieldValue('Person_Surname');
				my_params.Person_Firname = piPanel.getFieldValue('Person_Firname');
				my_params.Person_Secname = piPanel.getFieldValue('Person_Secname');
			} else {
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
				return false;
			}

			my_params.personData = {
				PersonEvn_id: me.PersonEvn_id,
				Person_id: me.Person_id,
				Server_id: me.Server_id
			};

			if (action == 'addtome') {
				my_params.isThis = true;
				my_params.type = 'HimSelf';
			}
			my_params.fromEmk = true;

			my_params.onHide = function(){
				onEvnDirectionSave({
					evnDirectionData: {
						EvnDirection_id: 0,
						EvnDirection_pid: me.Evn_id
					}
				});
			};
			if (action == 'addtome') {
				my_params.onClose = my_params.onHide;
				getWnd('swDirectionMasterWindow').show(my_params);
			} else {
				getWnd('swMPRecordWindow').show(my_params);
			}
		}
		else
		{
			var EvnDirection_id = me.EvnDirectionGrid.recordMenu.EvnDirection_id;
			if (!EvnDirection_id) {
				return false;
			}
			var record = this.EvnDirectionGrid.getStore().findRecord('EvnDirection_id', EvnDirection_id);
			if (!record) {
				return false;
			}

			if (record.get('EvnPrescrVK_id')) {
				getWnd('swEvnPrescrVKWindow').show({
					EvnPrescrVK_id: record.get('EvnPrescrVK_id'),
					action: 'edit'
				});
				return true;
			}

			formParams = {
				EvnDirection_id: record.get('EvnDirection_id'),
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				Lpu_gid: record.get('Lpu_gid'),
				EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
				EvnDirectionHTM_id: record.get('EvnDirectionHTM_id'),
				EvnDirectionCVI_id: record.get('EvnDirectionCVI_id'),
				DirType_Code: record.get('DirType_Code'),
				EvnDirectionHistologic_id: record.get('EvnDirectionHistologic_id')
			};

			if (formParams.EvnDirectionCVI_id) {
				var params = {
					action: (formParams.Lpu_gid != getGlobalOptions().lpu_id) ? 'view' : action,
					EvnDirectionCVI_id: formParams.EvnDirectionCVI_id,
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					callback: onEvnDirectionSave,
					onHide: Ext.emptyFn
				};
				getWnd('swEvnDirectionCviEditWindow').show(params);
				return true;
			}

			// если направление на МСЭ, открываем соответсвующую форму
			if (formParams.EvnPrescrMse_id) {
				var params = {
					action: (formParams.Lpu_gid != getGlobalOptions().lpu_id) ? 'view' : action,
					EvnPrescrMse_id: formParams.EvnPrescrMse_id,
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					onHide: Ext.emptyFn
				};
				getWnd('swDirectionOnMseEditForm').show(params);
				return true;
			}
			// если направление на ВМП, открываем соответсвующую форму
			if (formParams.EvnDirectionHTM_id) {
				var params = {
					EvnDirectionHTM_id: formParams.EvnDirectionHTM_id,
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					action: action,
					onHide: Ext.emptyFn
				};
				getWnd('swDirectionOnHTMEditForm').show(params);
				return true;
			}

			// если направление на ВМП, открываем соответсвующую форму
			if (formParams.EvnDirectionHTM_id) {
				var params = {
					action: action,
					EvnDirectionHTM_id: formParams.EvnDirectionHTM_id,
					Person_id: me.Person_id,
					Server_id: me.Server_id,
					onHide: Ext.emptyFn
				};
				getWnd('swDirectionOnHTMEditForm').show(params);
				return true;
			}

			var my_params = new Object({
				Person_id: me.Person_id,
				EvnDirection_id: formParams.EvnDirection_id,
				callback: onEvnDirectionSave,
				formParams: formParams,
				action: action
			});

			my_params.onHide = Ext.emptyFn;

			if ( formParams.EvnDirectionHistologic_id  ) {
				var action = (formParams.Lpu_gid == getGlobalOptions().lpu_id) ? 'edit' : 'view';
				formParams.Person_id = me.Person_id;
				getWnd('swEvnDirectionHistologicEditWindow').show({
					action: action,
					formParams: formParams,
					onHide: Ext.emptyFn,
					userMedStaffFact: this.userMedStaffFact,
					callback: function(){
						// --
					}.createDelegate(this)
				});
				return true;
			}

			//зачем из Ext6 вызывать формы Ext2 ?
			/*
			if(action=='view' && record.get('DirType_Code')==9)
				getWnd('swEvnDirectionEditWindowExt6').show(my_params);
			else
				getWnd('swEvnDirectionEditWindow').show(my_params);
			*/
			getWnd('swEvnDirectionEditWindowExt6').show(my_params);
		}
	},
	openDirectionOnHTMEditForm: function(cb){
		var callback = (cb && typeof cb == 'function') ? cb : null;
		if(getRegionNick() == 'kz'){
			if(callback) callback();
			return false;
		}
		var me = this;
		this.getLastDirectionVKforVMP(function(data){
			if(data && data.EvnVK_id){
				getWnd('swSelectMedServiceWindow').show({
					isRecord: true, // на запись
					ARMType: 'htm',
					onSelect: function(msData) {
						if(!msData){
							getWnd('swDirectionOnHTMEditForm').show({
								action: "add",
								Person_id: data.Person_id,
								PersonEvn_id: data.PersonEvn_id,
								Server_id: data.Server_id,
								MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
								LpuSection_id: null,
								LpuSection_did: null,
								ARMType: 'htm',
								Diag_id: data.Diag_id,
								// EvnDirection_pid: data.EvnPrescrVK_pid,
								EvnDirectionHTM_pid: this.Evn_id || null,
								EvnVK_id: data.EvnVK_id,
								EvnVK_setDT: data.EvnVK_setDT,
								EvnVK_NumProtocol: data.EvnVK_NumProtocol
							});
							return false;
						}
						getWnd('swTTMSScheduleRecordWindow').show({
							Person: {
								Person_Surname: data.Person_Surname || data.Person_Fio,
								Person_Firname: data.Person_Firname,
								Person_Secname: data.Person_Secname,
								Person_Birthday: data.Person_Birthday,
								Person_id: data.Person_id,
								PersonEvn_id: data.PersonEvn_id,
								Server_id: data.Server_id
							},
							MedService_id: msData.MedService_id,
							MedServiceType_id: msData.MedServiceType_id,
							MedService_Nick: msData.MedService_Nick,
							MedService_Name: msData.MedService_Name,
							MedServiceType_SysNick: msData.MedServiceType_SysNick,
							Lpu_did: msData.Lpu_id,
							LpuUnit_did: msData.LpuUnit_id,
							LpuUnitType_SysNick: msData.LpuUnitType_SysNick,
							LpuSection_uid: msData.LpuSection_id,
							LpuSection_Name: msData.LpuSection_Name,
							LpuSectionProfile_id: msData.LpuSectionProfile_id,
							ARMType: 'htm',
							Diag_id: data.Diag_id,
							EvnDirection_pid: this.Evn_id,
							EvnDirectionHTM_pid: this.Evn_id || null,
							EvnVK_id: data.EvnVK_id,
							EvnVK_setDT: data.EvnVK_setDT,
							EvnVK_NumProtocol: data.EvnVK_NumProtocol,
							callback: function(data){
								getWnd('swTTMSScheduleRecordWindow').hide();
							},
							userMedStaffFact: this.userMedStaffFact,
							userClearTimeMS: function() {
								this.getLoadMask(lang['osvobojdenie_zapisi']).show();
								Ext.Ajax.request({
									url: '/?c=Mse&m=clearTimeMSOnEvnPrescrMse',
									params: {
										TimetableMedService_id: this.TimetableMedService_id
									},
									callback: function(o, s, r) {
										this.getLoadMask().hide();
									}.createDelegate(this)
								});
							}
						});
					}.bind(this)
				});
			}else{
				if(callback) callback();
			}
		}.bind(this));
	},
	getLastDirectionVKforVMP: function(cb){
		var callback = (cb && typeof cb == 'function') ? cb : null;
		//получить последний протокол BK для ВМП
		var me = this;
		Ext6.Ajax.request({
			url: '/?c=EMK&m=getLastDirectionVKforVMP',
			params: {
				Person_id: me.Person_id
			},
			success: function (response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				var res = (response_obj.data && response_obj.data.length>0) ? response_obj.data : false;
				if(callback) callback(res[0]);
			}
		})
	},
	createDirection: function(dir_type_rec, excList) {
		var me = this;

		var personData = {};
		personData.Person_id = me.Person_id;
		personData.Server_id = me.Server_id;
		personData.PersonEvn_id = me.PersonEvn_id;

		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			personData.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			personData.Person_Surname = piPanel.getFieldValue('Person_Surname');
			personData.Person_Firname = piPanel.getFieldValue('Person_Firname');
			personData.Person_Secname = piPanel.getFieldValue('Person_Secname');
			personData.Person_IsDead = piPanel.getFieldValue('Person_IsDead');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		if (dir_type_rec.get('DirType_Code') == '18') {
			// На консультацию в другую МИС
			var Person_Fio = personData.Person_Surname + ' ' + personData.Person_Firname + ' ' + personData.Person_Secname;
			getWnd('swDirectionMasterMisRbWindow').show({
				personData: {
					Person_Fio: Person_Fio,
					Person_id: me.Person_id
				}
			});
			return true;
		}

		var directionData = {
			EvnDirection_pid: me.Evn_id || null
			,DopDispInfoConsent_id: me.DopDispInfoConsent_id || null
			,Diag_id: me.ownerPanel.getDiagId()
			,DirType_id: dir_type_rec.get('DirType_id')
			,MedService_id: this.userMedStaffFact.MedService_id
			,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
			,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
			,LpuSection_id: this.userMedStaffFact.LpuSection_id
			,ARMType_id: this.userMedStaffFact.ARMType_id
			,Lpu_sid: getGlobalOptions().lpu_id
			,withDirection: true
		};
		directionData.Person_id = personData.Person_id;
		directionData.PersonEvn_id = personData.PersonEvn_id;
		directionData.Server_id = personData.Server_id;

		var onDirection = function () {
			me.load();
		};

		if (dir_type_rec.get('DirType_Code') == 23) {
			checkEvnPrescrMseExists({
				Person_id: personData.Person_id,
				callback: function() {
					createEvnPrescrMse({
						personData: personData,
						userMedStaffFact: this.userMedStaffFact,
						directionData: directionData,
						callback: onDirection
					})
				}.createDelegate(this)
			});
			return true;
		}

		if (dir_type_rec.get('DirType_Code') == 15 && getRegionNick() != 'kz') {
			this.openDirectionOnHTMEditForm(function(){
				var params = {
					type: 'HTM',
					dirTypeData: { DirType_id: 19, DirType_Code: 15, DirType_Name: 'На высокотехнологичную помощь' },
					personData: {
						Person_id:	personData.Person_id,
						PersonEvn_id:	personData.PersonEvn_id,
						Server_id:	personData.Server_id,
					},
					directionData: {
						action: 'add',
						EvnDirectionHTM_pid: me.Evn_id || null,
						EvnDirection_pid: me.Evn_id || null,
						Person_id: personData.Person_id,
						PersonEvn_id: personData.PersonEvn_id,
						Server_id: personData.Server_id,
						// MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
						LpuSection_id: directionData.LpuSection_id,
						LpuSection_did: directionData.LpuSection_id,
						withCreateDirection: false,
						// ARMType: 'htm',
					},
					MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id,
					onSave: onDirection
				};
				// окно мастера направлений #101026
				getWnd('swDirectionMasterWindow').show(params);
				return true;
			});
			return true;
		}

		if (7 == dir_type_rec.get('DirType_Code')) {
			// На патологогистологическое исследование
			directionData.EvnDirectionHistologic_pid = me.Evn_id || null,
			getWnd('swEvnDirectionHistologicEditWindow').show({
				action: 'add',
				formParams: directionData,
				callback: onDirection,
				userMedStaffFact: this.userMedStaffFact
			});
			return true;
		}

		if (getRegionNick().inlist(['perm', 'vologda']) && dir_type_rec.get('DirType_Code') == 8) {
			createEvnPrescrVK({
				personData: personData,
				userMedStaffFact: this.userMedStaffFact,
				directionData: directionData,
				win: this,
				callback: onDirection
			});
			return true;
		} else if (dir_type_rec.get('DirType_Code').inlist([8,16])) {
			// Направление на ВК или МСЭ
			getWnd('swUslugaComplexMedServiceListWindow').show({
				userMedStaffFact: this.userMedStaffFact,
				personData: personData,
				dirTypeData: dir_type_rec.data,
				directionData: directionData,
				onDirection: onDirection
			});
			return true;
		}

		if (13 == dir_type_rec.get('DirType_Code')) {
			// Направление на удаленную консультацию
			directionData.Lpu_did = this.userMedStaffFact.Lpu_id;
			getWnd('swEvnDirectionEditWindowExt6').show({
				action: 'add',
				disableQuestionPrintEvnDirection: true,
				callback: onDirection,
				Person_id: personData.Person_id,
				Server_id: personData.Server_id,
				PersonEvn_id: personData.PersonEvn_id,
				Person_IsDead: personData.Person_IsDead,
				Person_Firname: personData.Person_Firname,
				Person_Secname: personData.Person_Secname,
				Person_Surname: personData.Person_Surname,
				Person_Birthday: personData.Person_Birthday,
				formParams: directionData,
			/*	formParams: {
					Person_id: personData.Person_id,
					PersonEvn_id: personData.PersonEvn_id,
					Server_id: personData.Server_id,
					DirType_id: 17,
					//~ EvnDirection_IsReceive: 2,
					ARMType_id: this.userMedStaffFact.ARMType_id,
					Lpu_did: this.userMedStaffFact.Lpu_id
				}*/
			});
			return true;
		}

		if (5 == dir_type_rec.get('DirType_Code')) {
			// Направление на экстренную госпитализацию
			getWnd('swEvnDirectionEditWindow').show({
				action: 'add',
				EvnDirection_id: null,
				callback: onDirection,
				Person_id: personData.Person_id,
				Person_Firname: personData.Person_Firname,
				Person_Secname: personData.Person_Secname,
				Person_Surname: personData.Person_Surname,
				Person_Birthday: personData.Person_Birthday,
				personData: {
					Person_id: personData.Person_id,
					Server_id: personData.Server_id,
					PersonEvn_id: personData.PersonEvn_id,
					Person_Firname: personData.Person_Firname,
					Person_Secname: personData.Person_Secname,
					Person_Surname: personData.Person_Surname,
					Person_Birthday: personData.Person_Birthday
				},
				formParams: {
					EvnDirection_pid: me.Evn_id
					,Diag_id: me.ownerPanel.getDiagId()
					,DirType_id: dir_type_rec.get('DirType_id')
					,MedService_id: this.userMedStaffFact.MedService_id
					,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
					,LpuSection_id: this.userMedStaffFact.LpuSection_id
					,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
					,Lpu_did: getGlobalOptions().lpu_id
					,Lpu_sid: getGlobalOptions().lpu_id
				}
			});
			return true;
		}

		if (9 == dir_type_rec.get('DirType_Code')) {
			// Направление на исследование в другую МО
			var directionDataOtherMO = {
				ext6: true,
				userMedStaffFact: Ext.apply({}, this.userMedStaffFact),
				person: Ext.apply({}, personData),
				direction: Ext.apply({}, directionData),
				callback: function(data){
					onDirection();
					if (data.EvnDirection_id) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: langs('Вывести направление на печать?'),
							title: langs('Вопрос'),
							icon: Ext.MessageBox.QUESTION,
							fn: function(buttonId){
								if (buttonId === 'yes') {
									sw.Promed.Direction.print({
										EvnDirection_id: data.EvnDirection_id
									});
								}
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				mode: 'nosave',
				windowId: this.getId()
			};
			directionDataOtherMO.direction.LpuUnitType_SysNick = 'polka';
			directionDataOtherMO.direction.LpuUnit_did = null;
			directionDataOtherMO.direction.isNotForSystem = true;

			sw.Promed.Direction.queuePerson(directionDataOtherMO);

			return true;
		}

		if (!excList) {
			excList = [];
		}
		excList.push('8');
		excList.push('5');
		excList.push('13');

		getWnd('swDirectionMasterWindow').show({
			userMedStaffFact: this.userMedStaffFact,
			personData: personData,
			dirTypeData: dir_type_rec.data,
			dirTypeCodeExcList: excList,
			directionData: directionData,
			onHide: onDirection
		});

		return true;
	},
	directZav: function() {
		var me = this;
		var EvnDirection_id = me.EvnDirectionGrid.recordMenu.EvnDirection_id;
		if (!EvnDirection_id) {
			return false;
		}
		var record = this.EvnDirectionGrid.getStore().findRecord('EvnDirection_id', EvnDirection_id);
		if (!record || !record.get('EvnPrescrVK_id')) {
			return false;
		}

		me.getLoadMask(LOAD_WAIT_SAVE).show();
		setEvnStatus({
			EvnClass_SysNick: 'EvnPrescrVK',
			EvnStatus_SysNick: 'Agreement',
			Evn_id: record.get('EvnPrescrVK_id'),
			callback: function() {
				me.getLoadMask().hide();
				me.load();
			}
		});
	},
	initComponent: function() {
		var me = this;

		if (!me.isScreenOnko) {

			this.plusMenu = Ext6.create('Ext6.menu.Menu', {
				userCls: 'menuWithoutIcons',
				items: [],
				listeners:{
					hide: function () {
						me.btnAddClick.setStyle('visibility','');
					}
				}
			});

			var dirTypeCodeExcList = ['10','11','14','16','17'];
			if (getRegionNick() != 'buryatiya') {
				dirTypeCodeExcList.push('18');
			}
			if (getRegionNick() != 'perm') {
				dirTypeCodeExcList.push('23');
			}
			sw.Promed.Direction.createDirTypeMenuItems({
				excList: dirTypeCodeExcList,
				id: 'DirTypeListMenu',
				onSelect: function(rec) {
					me.createDirection(rec, this.excList);
				},
				onCreate: function() {
					me.plusMenu.add('-');
					me.plusMenu.add({
						text: 'Записать к себе',
						handler: function() {
							me.openEvnDirectionEditWindow('addtome');
						}
					});
				},
				menu: this.plusMenu
			});
		}

		this.EvnDirectionGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Направить зав. отделением',
					iconCls: 'panicon-add',
					handler: function() {
						me.directZav();
					}
				}, {
					text: 'Просмотр',
					iconCls: 'panicon-view',
					handler: function() {
						me.openEvnDirectionEditWindow('view');
					}
				}, {
					text: 'Редактировать',
					iconCls: 'panicon-edit',
					handler: function() {
						me.openEvnDirectionEditWindow('edit');
					}
				}, {
					text: 'Печать',
					iconCls: 'panicon-print',
					handler: function() {
						me.printEvnDirection();
					}
				}, {
					text: 'Отменить направление',
					iconCls: 'panicon-delete',
					handler: function() {
						me.cancelEvnDirection();
					}
				}]
			}),
			showRecordMenu: function(el, EvnDirection_id, rowIndex) {
				var record = me.EvnDirectionGrid.getStore().findRecord('EvnDirection_id', EvnDirection_id);
				if (!record) {
					return false;
				}
				if (getRegionNick().inlist(['perm', 'vologda']) && record.get('DirType_Code') == 8 && record.get('EvnStatus_epvkSysNick') && record.get('EvnStatus_epvkSysNick').inlist(['New', 'Rework'])) {
					this.recordMenu.items.items[0].show();
				} else {
					this.recordMenu.items.items[0].hide();
				}
				this.recordMenu.EvnDirection_id = EvnDirection_id;
				this.recordMenu.rowIndex = rowIndex;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				minWidth: 100,
				tdCls: 'padLeft20',
				dataIndex: 'EvnDirection_Data',
				renderer: function (value, metaData, record) {
					var direction = 'Направление';
					var text = '<span class="dirInfoIcon"></span>' + record.get('DirType_Name') + ": " + record.get('LpuSection_Name') + " / " + (Ext6.isEmpty(record.get('Org_Name')) ? record.get('Lpu_Name') : record.get('Org_Name')) + " / " + record.get('EvnDirection_setDate') + " / " + direction + " № " + record.get('EvnDirection_Num');

					if (record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12, 13])) {
						text = text + ' / <span style="color: red;">' + record.get('EvnStatus_Name') + ' ' + record.get('EvnDirection_statusDate') + '</span>';

						if (record.get('EvnStatusCause_Name')) {
							text = text + ', по причине: <span style="color: red;">' + record.get('EvnStatusCause_Name') + '</span>';
						}
					}

					if (record.get('EvnStatus_epvkName')) {
						text = text + ' / ' + record.get('EvnStatus_epvkName')
					}

					return text;
				}
			}, {
				width: 60,
				dataIndex: 'EvnDirection_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: '{record.EMDRegistry_ObjectName}',
						EMDRegistry_ObjectID: '{record.EMDRegistry_ObjectID}',
						SignCount: '{record.EvnDirection_SignCount}',
						MinSignCount: '{record.EvnDirection_MinSignCount}',
						IsSigned: '{record.IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			}, {
				width: 40,
				dataIndex: 'EvnDirection_Action',
				tdCls: 'vertical-middle',
				renderer: function (value, metaData, record) {
					if (me.accessType == 'edit') {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnDirectionGrid.id + "\").showRecordMenu(this, " + record.get('EvnDirection_id') + ", " + metaData.rowIndex + ");'></div>";
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnDirection_id', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (me.accessType == 'edit' && row.get('DirType_Code') && row.get('DirType_Code').inlist([1, 2, 3, 4, 5, 6, 8, 9, 12, 13, 15, 23])) {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'EMDRegistry_ObjectName', type: 'string' },
					{ name: 'EMDRegistry_ObjectID', type: 'int' },
					{ name: 'IsSigned', type: 'int' },
					{ name: 'EvnPrescrVK_id', type: 'int' },
					{ name: 'EvnStatus_epvkSysNick', type: 'string' },
					{ name: 'EvnStatus_id', type: 'int' },
					{ name: 'DirType_Name', type: 'string' },
					{ name: 'LpuSection_Name', type: 'string' },
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'Org_Name', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'Org_Nick', type: 'string' },
					{ name: 'EvnDirection_setDate', type: 'string' },
					{ name: 'EvnDirection_Num', type: 'string' },
					{ name: 'TimetableGraf_id', type: 'string' },
					{ name: 'TimetableMedService_id', type: 'string' },
					{ name: 'TimetableResource_id', type: 'string' },
					{ name: 'TimetableStac_id', type: 'string' },
					{ name: 'EvnQueue_id', type: 'string' },
					{ name: 'DirType_Code', type: 'string' },
					{ name: 'EvnStatus_Name', type: 'string' },
					{ name: 'EvnDirection_statusDate', type: 'string' },
					{ name: 'EvnStatusCause_Name', type: 'string' },
					{ name: 'EvnPrescrMse_id', type: 'int' },
					{ name: 'EvnDirectionHTM_id', type: 'int' },
					{ name: 'EvnDirectionHistologic_id', type: 'int' },
					{ name: 'EvnDirectionCVI_id', type: 'int' },
					{ name: 'EvnDirectionCVI_Lab', type: 'string' },
					{ name: 'EvnDirectionCVI_takeDate', type: 'string' },
					{ name: 'Lpu_gid', type: 'int' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnDirection&m=loadEvnDirectionPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnDirection_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDirectionGrid
			]
		});

		this.callParent(arguments);
	}
});