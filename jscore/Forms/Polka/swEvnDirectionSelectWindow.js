/**
* swEvnDirectionSelectWindow - окно выбора направления.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-18.02.2010
*/
/*NO PARSE JSON*/

sw.Promed.swEvnDirectionSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPSEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnDirectionSelectWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 800,
	height: 600,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnDirectionSelectWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 600,
	minWidth: 800,
	title: langs('Выбор направления'),
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	id: 'EvnDirectionSelectWindow',
	doSelect: function(view_frame, options) {
		var win = this;

		options = options || {};

		// смотря какой таб открыт такие и действия
		if (this.tabPanel.getActiveTab().id == 'tab_directions') {
			var grid = this.EvnDirectionNotAutoGrid.getGrid();
			var grid2 = this.EvnDirectionIsAutoGrid.getGrid();
			var record;

			if (grid2.getSelectionModel().getSelected() && grid2.getSelectionModel().getSelected().get('EvnDirection_id')) {
				record = grid2.getSelectionModel().getSelected();
			}
			if (!record && grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('EvnDirection_id')) {
				record = grid.getSelectionModel().getSelected();
			}
			if (view_frame && view_frame.getGrid().getSelectionModel().getSelected() && view_frame.getGrid().getSelectionModel().getSelected().get('EvnDirection_id')) {
				record = view_frame.getGrid().getSelectionModel().getSelected();
			}
			if (!record) {
				return false;
			}
			if (2 == record.get('enabled')) {
				if (this.useCase.inlist([
					'create_evnplstom_without_recording'
					,'create_evnpl_without_recording'
					,'choose_for_evnpl_stream_input'
					,'choose_for_evnplstom_stream_input'
					,'choose_for_evnpl'
					,'choose_for_evnplstom'
				])) {
					var begTime = record.get('Timetable_begTime') || null;
					if (begTime) {
						begTime = Date.parseDate(begTime, 'd.m.Y H:i');
					}
					if (begTime && Ext.util.Format.date(begTime, 'd.m.Y') == getGlobalOptions().date && this.useCase.inlist([
						'create_evnplstom_without_recording','create_evnpl_without_recording'
					])) {
						Ext.Msg.alert(langs('Сообщение'), langs('Пациент записан на текущий день. Воспользуйтесь функцией приема по записи'));
						return false;
					}
					var msg, buttons;
					var person_information = win.findById('EDSW_PersonInformationFrame').items.items[0].getStore().getAt(0).data;
			
					var Person_Fio = person_information.Person_Firname + ' ' + person_information.Person_Surname + ' ' + person_information.Person_Secname;

					if (17 == record.get('EvnStatus_id') || record.get('Timetable_begTime')) {
						if (record.get('EvnDirection_IsAuto') != 2) {
							msg = langs('Обслужить направление № ') + record.get('EvnDirection_Num') + langs(' по профилю ') + record.get('LpuSectionProfile_Name') 
								+ ' (записано на ' + record.get('Timetable_begTime') + ', врач ' + record.get('MSF_Person_Fin') + ')?';
						} else {
							msg = 'Пациент записан на ' + record.get('Timetable_begTime') + ', врач ' + record.get('MSF_Person_Fin') + '.';
						}
						buttons = {
							yes: (record.get('EvnDirection_IsAuto') != 2) ? langs('Обслужить направление') : 'Принять по этому направлению',
							no: langs('Принять без направления'),
							cancel: langs('Отмена')
						};
					} else if (10 == record.get('EvnStatus_id') || !record.get('Timetable_begTime')) {
						if (record.get('EvnDirection_IsAuto') != 2) {
							/*msg = langs('Обслужить направление № ') + record.get('EvnDirection_Num') + langs(' по профилю ') + record.get('LpuSectionProfile_Name') 
								+ langs(' и убрать пациента из очереди?');*/
							msg = langs('У пациента ') + '<b>' + Person_Fio + '</b>' + langs(' имеется очередь к врачу ') + record.get('MSF_Person_Fin') + '.' + langs(' Принять ') + Person_Fio + langs(' и исключить его/ее из очереди врача ') + record.get('MSF_Person_Fin') + '?';
						} else {
							//msg = langs('Убрать пациента из очереди по профилю ') + record.get('LpuSectionProfile_Name') + '?';
							msg = langs('У пациента ') + '<b>' + Person_Fio + '</b>' + langs(' имеется очередь к врачу по профилю  ') + record.get('LpuSectionProfile_Name') + '.' + langs(' Принять ') + Person_Fio + langs(' и исключить его/ее из очереди по профилю ') + record.get('LpuSectionProfile_Name') + '?';
						}
						buttons = {
							yes: langs('Убрать из очереди'),
							no: langs('Оставить в очереди и принять пациента'),
							cancel: langs('Отмена')
						};
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Данное направление не имеет статуса "Поставлено в очередь" или "Записано". Пожалуйста, выберите другое направление'));
						return false;
					}
					sw.swMsg.show(
					{
						buttons: buttons,
						fn: function( buttonId ) 
						{
							if ( buttonId == 'yes') {
								var evnDirectionData = {};
								evnDirectionData.EvnDirection_IsAuto = record.get('EvnDirection_IsAuto');
								evnDirectionData.EvnDirection_IsReceive = record.get('EvnDirection_IsReceive');
								evnDirectionData.Diag_did = record.get('Diag_id');
								evnDirectionData.EvnDirection_id = record.get('EvnDirection_id');
								evnDirectionData.EvnDirectionHTM_id = record.get('EvnDirectionHTM_id');
								evnDirectionData.EvnDirection_Num = record.get('EvnDirection_Num');
								evnDirectionData.EvnDirection_setDate = record.get('EvnDirection_setDate');
								evnDirectionData.LpuSection_id = record.get('LpuSection_id');
								evnDirectionData.MedStaffFact_id = record.get('MedStaffFact_id');
								evnDirectionData.MedPersonal_id = record.get('MedPersonal_id');
								evnDirectionData.Org_did = record.get('Org_id');
								evnDirectionData.Lpu_sid = record.get('Lpu_sid');
								evnDirectionData.Lpu_id = record.get('Lpu_id');
								evnDirectionData.UslugaComplex_id = record.get('UslugaComplex_id');
								evnDirectionData.DirType_id = record.get('DirType_id');
								evnDirectionData.EvnQueue_id = record.get('EvnQueue_id');
								evnDirectionData.TimetableGraf_id = record.get('TimetableGraf_id');
								evnDirectionData.TimetableStac_id = record.get('TimetableStac_id');
								evnDirectionData.TimetableMedService_id = record.get('TimetableMedService_id');
								evnDirectionData.EmergencyData_CallNum = record.get('EmergencyData_CallNum');
								evnDirectionData.LpuBuildingType_id = record.get('LpuBuildingType_id');
								evnDirectionData.MedicalCareFormType_id = record.get('MedicalCareFormType_id');
								evnDirectionData.PurposeHospital_id = record.get('PurposeHospital_id');
								evnDirectionData.Diag_cid = record.get('Diag_cid');
								evnDirectionData.PayType_id = record.get('PayType_id');
								win.callback(evnDirectionData);
								win.hide();
							} else if ( buttonId == 'no') {
								//win.callback(null);
								win.hide();
							}
						},
						msg: msg,
						title: langs('Вопрос')
					});
				} else {
					var evnDirectionData = {};
					evnDirectionData.EvnDirection_IsAuto = record.get('EvnDirection_IsAuto');
					evnDirectionData.EvnDirection_IsReceive = record.get('EvnDirection_IsReceive');
					evnDirectionData.Diag_did = record.get('Diag_id');
					evnDirectionData.EvnDirection_id = record.get('EvnDirection_id');
					evnDirectionData.EvnDirectionHTM_id = record.get('EvnDirectionHTM_id');
					evnDirectionData.EvnDirection_Num = record.get('EvnDirection_Num');
					evnDirectionData.EvnDirection_setDate = record.get('EvnDirection_setDate');
					evnDirectionData.LpuSection_id = record.get('LpuSection_id');
					evnDirectionData.MedStaffFact_id = record.get('MedStaffFact_id');
					evnDirectionData.MedPersonal_id = record.get('MedPersonal_id');
					evnDirectionData.Org_did = record.get('Org_id');
					evnDirectionData.Lpu_sid = record.get('Lpu_sid');
					evnDirectionData.Lpu_id = record.get('Lpu_sid') || record.get('Lpu_id');
					evnDirectionData.UslugaComplex_id = record.get('UslugaComplex_id');
					evnDirectionData.DirType_id = record.get('DirType_id');
					evnDirectionData.EvnQueue_id = record.get('EvnQueue_id');
					evnDirectionData.TimetableGraf_id = record.get('TimetableGraf_id');
					evnDirectionData.TimetableStac_id = record.get('TimetableStac_id');
					evnDirectionData.TimetableMedService_id = record.get('TimetableMedService_id');
					evnDirectionData.LpuBuildingType_id = record.get('LpuBuildingType_id');
					evnDirectionData.MedicalCareFormType_id = record.get('MedicalCareFormType_id');
					evnDirectionData.PurposeHospital_id = record.get('PurposeHospital_id');
					evnDirectionData.Diag_cid = record.get('Diag_cid');
					evnDirectionData.PayType_id = record.get('PayType_id');
					win.callback(evnDirectionData);
					win.hide();
				}
			} else {
				sw.swMsg.alert(langs('Направление не действительно'), langs('Данное направление уже было использовано в другом учетном документе. Пожалуйста, выберите другое направление'));
			}
		} else {
			var grid = this.EvnDirectionExtGrid.getGrid();

			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirectionExt_id')) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			if (record) {
				var evnDirectionData = new Object();

				evnDirectionData.Diag_did = record.get('Diag_id');
				evnDirectionData.EvnDirectionExt_id = record.get('EvnDirectionExt_id');
				evnDirectionData.EvnDirection_Num = record.get('EvnDirectionExt_NPRID');
				evnDirectionData.EvnDirection_setDate = record.get('EvnDirectionExt_setDT');
				evnDirectionData.LpuSection_id = null;
				evnDirectionData.MedStaffFact_id = null;
				evnDirectionData.MedPersonal_id = null;
				evnDirectionData.Org_did = record.get('Org_id');
				evnDirectionData.Lpu_id = record.get('Lpu_id');
				evnDirectionData.UslugaComplex_id = null;
				evnDirectionData.PurposeHospital_id = record.get('PurposeHospital_id');
				evnDirectionData.Diag_cid = record.get('Diag_cid');
				evnDirectionData.PayType_id = record.get('PayType_id');

				win.callback(evnDirectionData);
				win.hide();
			}
		}
	},
	doExtDirection: function() {
		var win = this;
		if (this.tabPanel.getActiveTab().id == 'tab_directions') {
			var win = this;
			
			//var persondata = win.findById('EDSW_PersonInformationFrame').items.items[0].store.data.items[0].data;
			var person_information = win.findById('EDSW_PersonInformationFrame').items.items[0].getStore().getAt(0).data;
			
			//открывается мастер направлений
			var personData = new Object();
			//person_information = win.findById('EPSEF_PersonInformationFrame');
			personData.Person_IsDead = person_information.Person_deadDT;
			personData.Person_Firname = person_information.Person_Firname;
			personData.Person_id = person_information.Person_id;
			personData.Person_Surname = person_information.Person_Surname;
			personData.Person_Secname = person_information.Person_Secname;
			personData.PersonEvn_id = person_information.PersonEvn_id;
			personData.Server_id = person_information.Server_id;
			personData.Person_Birthday = person_information.Person_Birthday;															

			var directionData = {
				LpuUnitType_SysNick: null
				, EvnQueue_id: null
				, QueueFailCause_id: null
				, Lpu_did: null
				, LpuUnit_did: null
				, LpuSection_did: null
				, EvnUsluga_id: null
				, LpuSection_id: null
				, EvnDirection_pid: null
				, EvnPrescr_id: null
				, PrescriptionType_Code: null
				, DirType_id: null
				, LpuSectionProfile_id: null
				, LpuUnitType_id: null
				, Diag_id: null
				, MedStaffFact_id: null
				, MedPersonal_id: null
				, MedPersonal_did: null
				, withDirection: 3
				, EvnDirection_IsReceive: 2
				, fromBj: true
			};
			if (personData != null) {
				var isDead = (this.personData && this.personData.Person_IsDead && this.persData.Person_IsDead == 'true');

				if (isDead) {
					sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна, т.к. у пациента стоит дата смерти.'));
					return false;
				}

				var params = {
					userMedStaffFact: null,
					isDead: false,
					type: 'ExtDirKVS',
					personData: personData,
					directionData: directionData,
					callback: function() { this.hide(); },
					onDirection: function (dataEvnDir) {
						win.dirdata = dataEvnDir;
						var EvnDirection_id = null;
						if(dataEvnDir.evnDirectionData) EvnDirection_id = dataEvnDir.evnDirectionData.EvnDirection_id;
						else EvnDirection_id = dataEvnDir.EvnDirection_id;
						Ext.Ajax.request({
							params: {EvnDirection_id: EvnDirection_id },
							url: '/?c=EvnDirection&m=getDataEvnDirection',
							callback: function(options, success, response) {
								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(response_obj[0]) {
										var data = response_obj[0];
										
										var dataSet = {
											EvnDirection_id: data.EvnDirection_id,
											EvnDirectionExt_id: data.EvnDirection_id,
											//EvnDirectionHTM_id: data.EvnDirectionHTM_id
											LpuSection_id: data.LpuSection_id,
											Org_did: data.Org_did,
											EvnDirection_Num: data.EvnDirection_Num,
											EvnDirection_setDate: data.EvnDirection_setDate,
											Diag_did: data.Diag_id,
											PurposeHospital_id: data.PurposeHospital_id,
											Diag_cid: data.Diag_cid,
											PayType_id: data.PayType_id,
											PrehospDirect_id: 2,
											Server_id: data.Server_id,
											Lpu_id: data.Lpu_id,
											LpuSection_did: data.LpuSection_did,
											EvnDirection_IsAuto: data.EvnDirection_IsAuto,
											EvnDirection_IsReceive: data.EvnDirection_IsReceive,
											DirType_id: data.DirType_id,
											Lpu_id: data.Lpu_id,
											Lpu_sid: data.Lpu_sid
										};
										win.callback(dataSet);
										win.hide();
										
									}
								}
							}
						});
					}
				};
				getWnd('swDirectionMasterWindow').show(params);
			}
		
		}
	},
	initComponent: function() {
		var win = this;
		var evndirection_all_cnfg = {
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			focusOn: {
				name: 'EDSW_SelectButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EDSW_CloseButton',
				type: 'button'
			},
			uniqueId: true,
			onDblClick: function() {
				win.doSelect(this);
			},
			onEnter: function() {
				win.doSelect(this);
			},
			onLoadData: function(result) {
				this.getGrid().getSelectionModel().clearSelections();
				this.getGrid().getSelectionModel().selectFirstRow();
			},
			paging: false,
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnDirectionHTM_id', type: 'int', hidden: true },
				{ name: 'EvnDirection_IsAuto', type: 'int', hidden: true},
				{ name: 'EvnDirection_IsReceive', type: 'int', hidden: true},
				{ name: 'LpuSection_id', type: 'int', hidden: true},
				{ name: 'MedStaffFact_id', type: 'int', hidden: true},
				{ name: 'MedPersonal_id', type: 'int', hidden: true},
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'DirType_id', type: 'int', hidden: true },
				{ name: 'EvnQueue_id', type: 'int', hidden: true },
				{ name: 'TimetableGraf_id', type: 'int', hidden: true },
				{ name: 'TimetableStac_id', type: 'int', hidden: true },
				{ name: 'TimetableMedService_id', type: 'int', hidden: true },
				{ name: 'EmergencyData_CallNum', type: 'string', hidden: true },
				{ name: 'EvnStatus_id', type: 'int', hidden: true },
				{ name: 'Lpu_sid', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'UslugaComplex_id', type: 'int', hidden: true },
				{ name: 'MSF_Person_Fin', type: 'string', hidden: true },
				{ name: 'UslugaComplex_Name', type: 'string', header: langs('Услуга'), width: 150 },
				{ name: 'Lpu_Name', type: 'string', header: langs('Направившая МО'), id: 'autoexpand', autoExpandMin: 150 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 150 },
				{ name: 'Timetable_begTime', header: langs('Дата записи'), width: 150, renderer: function(value, cellEl, rec){
					if (!rec.get('EvnDirection_id')) return '';
					if (!rec.get('Timetable_begTime')) return langs('Очередь');
					return rec.get('Timetable_begTime');
				} }, /* Дата и время записи */
				{ name: 'EvnDirection_Num', type: 'string', header: langs('Номер'), width: 100 },
				{ name: 'DirType_Name', type: 'string', header: langs('Тип направления'), width: 150 },
				{ name: 'Diag_Name', type: 'string', header: langs('Диагноз'), width: 150 },
				{ name: 'EvnDirection_setDate', type: 'date', header: langs('Дата направления'), width: 110 },
				{ name: 'EvnDirection_IsVMP', type: 'checkbox', header: 'Направление на ВМП', width: 150 },
				{ name: 'LpuBuildingType_id', type: 'int', hidden: true },
				{ name: 'PurposeHospital_id', type: 'int', hidden: true },
				{ name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'Diag_cid', type: 'int', hidden: true },
				{ name: 'enabled', type: 'int', hidden: true }
			],
			toolbar: true
		};
		
		this.EvnDirectionIsAutoGrid = new sw.Promed.ViewFrame(Ext.apply(evndirection_all_cnfg, {
			onRowSelect: function(sm, index, record) {
				win.EvnDirectionNotAutoGrid.getGrid().getSelectionModel().clearSelections();
			},
			id: 'EDSW_EvnDirectionIsAutoGrid',
			region: 'south',
			title: langs('Записи')
		}));
		this.EvnDirectionNotAutoGrid = new sw.Promed.ViewFrame(Ext.apply(evndirection_all_cnfg, {
			onRowSelect: function(sm, index, record) {
				win.EvnDirectionIsAutoGrid.getGrid().getSelectionModel().clearSelections();
			},
			id: 'EDSW_EvnDirectionNotAutoGrid',
			region: 'center',
			title: langs('Направления')
		}));

		this.EvnDirectionExtGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnDirectionExt&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			focusOn: {
				name: 'EDSW_SelectButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EDSW_CloseButton',
				type: 'button'
			},
			uniqueId: true,
			onDblClick: function() {
				this.doSelect(this.EvnDirectionExtGrid);
			}.createDelegate(this),
			onEnter: function() {
				this.doSelect(this.EvnDirectionExtGrid);
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirectionExt_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_Nick', type: 'string', header: langs('Направившая МО'), width: 150 },
				{ name: 'Person_SurName', type: 'string', header: langs('Фамилия'), width: 150, id: 'autoexpand' },
				{ name: 'Person_FirName', type: 'string', header: langs('Имя'), width: 150 },
				{ name: 'Person_SecName', type: 'string', header: langs('Отчество'), width: 150 },
				{ name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 150 },
				{ name: 'Sex_Name', type: 'string', header: langs('Пол'), width: 150 },
				{ name: 'Polis_Ser', type: 'string', header: langs('Серия полиса'), width: 150 },
				{ name: 'Polis_Num', type: 'string', header: langs('Номер полиса'), width: 150 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 150 },
				{ name: 'EvnDirectionExt_NPRID', type: 'string', header: langs('Номер направления'), width: 150 },
				{ name: 'PrehospType_Name', type: 'string', header: langs('Тип направления'), width: 150 },
				{ name: 'Diag_Name', type: 'string', header: langs('Диагноз'), width: 150 },
				{ name: 'EvnDirectionExt_setDT', type: 'date', header: langs('Дата направления'), width: 150 },
				{ name: 'PurposeHospital_id', type: 'int', hidden: true },
				{ name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'Diag_cid', type: 'int', hidden: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true }
			],
			toolbar: true
		});

		this.EvnDirectionNotAutoGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var result = '';
				if (row.get('enabled') == 2) {
					result = 'x-grid-panel';
				} else {
					result = 'x-grid-rowgray ';
				}
				return result;
			}
		});
		this.EvnDirectionIsAutoGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var result = '';
				if (row.get('enabled') == 2) {
					result = 'x-grid-panel';
				} else {
					result = 'x-grid-rowgray ';
				}
				return result;
			}
		});

		this.tabPanel = new Ext.TabPanel({
			enableTabScroll: true,
			region: 'center',
			activeTab: 0,
			layoutOnTabChange: true,
			items: [{
				title: langs('Направления и записи'),
				layout: 'border',
				id: 'tab_directions',
				items: [
					win.EvnDirectionNotAutoGrid,
					win.EvnDirectionIsAutoGrid
				]
			}, {
				title: langs('Внешние направления'),
				layout: 'fit',
				id: 'tab_extdirections',
				items: [
					win.EvnDirectionExtGrid
				]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDSW_SelectButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: langs('Выбрать')
			}, win.ExtDirButton = new Ext.Button({
				handler: function() {
					this.doExtDirection();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDSW_ExtDirectionButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: langs('Внешнее направление')
			}), {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EDSW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'EDSW_PersonInformationFrame',
					region: 'north'
				}),
				win.tabPanel
			]
		});
		sw.Promed.swEvnDirectionSelectWindow.superclass.initComponent.apply(this, arguments);

		this.EvnDirectionNotAutoGrid.addListenersFocusOnFields();
		if(this.EvnDirectionIsAutoGrid.getGrid().getStore().data.length > 0)
			this.EvnDirectionIsAutoGrid.addListenersFocusOnFields();
	},
	loadEvnDirectionGrid: function(storeData) {
		this.EvnDirectionNotAutoGrid.getGrid().getStore().removeAll();
		this.EvnDirectionIsAutoGrid.getGrid().getStore().removeAll();
		this.EvnDirectionNotAutoGrid.setActionDisabled('action_refresh', true);
		this.EvnDirectionIsAutoGrid.setActionDisabled('action_refresh', true);
		var i = 0, storeData1 = [], storeData2 = [],
			is_visible_auto_grid = this.useCase.inlist(['create_evnplstom_without_recording','create_evnpl_without_recording']);
		while (i < storeData.length) {
			if (storeData[i].EvnDirection_IsAuto && 2 == storeData[i].EvnDirection_IsAuto) {
				storeData2.push(storeData[i]);
			} else {
				storeData1.push(storeData[i]);
			}
			i++;
		}
		this.EvnDirectionNotAutoGrid.getGrid().getStore().loadData(storeData1);
		// Форма выбора направлений показывается с двумя гридами только при вызове из главного журнала АРМ врача поликлиники/стоматолога
		this.EvnDirectionIsAutoGrid.setVisible(is_visible_auto_grid);
		if (is_visible_auto_grid) {
			this.EvnDirectionIsAutoGrid.getGrid().getStore().loadData(storeData2);
		}
	},
	show: function() {
		sw.Promed.swEvnDirectionSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onDate = arguments[0].onDate || null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.personId = arguments[0].Person_id || null;
		this.MedStaffFactId = arguments[0].MedStaffFact_id || null;
		this.LpuSectionId = arguments[0].LpuSection_id || null;
		this.parentClass = arguments[0].parentClass || null;
		this.formType = arguments[0].formType || null;
		this.DirType_id = arguments[0].DirType_id || null;
		this.storeData = arguments[0].storeData || null;
		this.useCase = arguments[0].useCase || '';

		this.findById('EDSW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		if (getRegionNick().inlist(['penza','perm']) && this.useCase.inlist([
			'choose_for_evnps'
			,'choose_for_evnps_stream_input'
		])) {
			this.EvnDirectionNotAutoGrid.setColumnHidden('EvnDirection_IsVMP', false);
		} else {
			this.EvnDirectionNotAutoGrid.setColumnHidden('EvnDirection_IsVMP', true);
		}

		win.loadEvnDirectionGrid([]);
		if ( this.storeData ) {
			win.loadEvnDirectionGrid(win.storeData);
		} else if ( this.personId ) {
			var loadMask = new Ext.LoadMask(win.getEl(), {
				msg: "Получение списка направлений и записей..."
			});
			loadMask.show();
			Ext.Ajax.request({
				params: {
					 onDate: (typeof this.onDate == 'object' ? Ext.util.Format.date(this.onDate, 'd.m.Y') : this.onDate)
					,Person_id: this.personId
					,MedStaffFact_id:this.MedStaffFactId
					,LpuSection_id:this.LpuSectionId
					,formType:this.formType
					,parentClass: this.parentClass
					,DirType_id: this.DirType_id
					,useCase:this.useCase
				},
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							win.storeData = response_obj;
							win.loadEvnDirectionGrid(win.storeData);
						}
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при получении списка направлений и записей'));
					}
				},
				url: '/?c=EvnDirection&m=loadEvnDirectionList'
			});
		}
		win.tabPanel.setActiveTab(1);
		win.tabPanel.setActiveTab(0);
		win.tabPanel.hideTabStripItem('tab_extdirections');
		if ((this.parentClass == 'EvnPS' || this.formType == 'stac') && getRegionNick() == 'astra') {
			// показываем вкладку внешние направления.
			win.tabPanel.unhideTabStripItem('tab_extdirections');

			this.EvnDirectionExtGrid.loadData({
				globalFilters: {
					notIdentOnly: 1,
					start: 0,
					limit: 100,
					onDate: (typeof this.onDate == 'object' ? Ext.util.Format.date(this.onDate, 'd.m.Y') : this.onDate)
				}
			})
		}
		if( getRegionNick()=='ekb' && win.parentClass=='EvnPS') win.ExtDirButton.show(); else win.ExtDirButton.hide();
	}
});

