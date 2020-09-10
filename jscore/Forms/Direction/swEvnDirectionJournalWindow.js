/**
* swEvnDirectionJournalWindow - журнал направлений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author
* @version      апрель.2012
* @comment      Префикс для id компонентов EDJW (EvnDirectionJournalWindow)
**/
/*NO PARSE JSON*/
sw.Promed.swEvnDirectionJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnDirectionJournalWindow',
	objectSrc: '/jscore/Forms/Direction/swEvnDirectionJournalWindow.js',

	title: lang['jurnal_napravleniy_na_gospitalizatsiyu'],
	layout: 'border',
	maximized: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	plain: true,
	id: 'EvnDirectionJournalWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',

	userMedStaffFact: null,

	addEvnPS: function(record) {
		var params = {};
		var me = this;
		params.action = 'add';
		params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
		params.MedPersonal_id = getGlobalOptions().medpersonal_id;
		params.form_mode = 'dj_hosp';
		if ( record ) {
			//создает КВС, заполняет данными из направления. После сохранения КВС направлению присваивается признак «Создана КВС на основании направления  № …= да» > обновление списка.
			params.callback = function() {
				me.EvnDirectionGrid.refreshRecords(null,0);
			};
			params.EvnDirection = record;
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			this.openForm('swEvnPSEditWindow', lang['dobavlenie_kvs'], params);
		} else {
			//создает КВС без направления
			//открывает форму поиска человека > создает новую пустую карту выбывшего из стационара.
			this.openForm('swPersonSearchWindow', lang['poisk_cheloveka'], {
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					me.openForm('swEvnPSEditWindow', lang['dobavlenie_kvs'], params);
				},
				searchMode: 'all'
			});
		}
	},
	/**
	 * Подтвердить госпитализацию
	 */
	confirmEvnDirection: function() {
		var me = this;
		var record = this.getSelectedRecord();
		if ( !record ) {
			return false;
		}
		log(me.userMedStaffFact);
		me.openForm('swHospDirectionConfirmWindow', lang['podtverjdenie_gospitalizatsii'], {
			record: record,
			userLpuSectionProfile_id: (me.ARMType_id ==3 && me.userMedStaffFact.LpuSectionProfile_id) || null,
			MedPersonal_FIO: (me.userMedStaffFac && me.userMedStaffFact.MedPersonal_FIO) || null,
			PostMed_Name: (me.userMedStaffFac && me.userMedStaffFact.PostMed_Name) || null,
			onConfirm: function(){
				record.set('IsConfirmed', 'true');
				record.commit();
				me.EvnDirectionGrid.getGrid().getStore().commitChanges();
				me.EvnDirectionGrid.setActionDisabled('action_confirm', true);
			}
		});
		return true;
	},
	/**
	 * Госпитализировать по направлению
	 */
	dirHospitalize: function() {
		var me = this;
		var record = this.getSelectedRecord();
		if ( !record ) {
			return false;
		}
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					me.getLoadMask(LOAD_WAIT).show();
					Ext.Ajax.request(
					{
						url: '/?c=EvnPS&m=checkDirHospitalize',
						callback: function(options, success, response)
						{
							me.getLoadMask().hide();
							if (response.responseText) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									me.addEvnPS(record);
							}
							}
						},
						params: {EvnDirection_id: record.get('EvnDirection_id')}
					});
				}
			},
			msg: lang['vyi_deystvitelno_hotite_sozdat_kvs_po_dannomu_napravleniyu'],
			title: lang['podtverjdenie']
		});
		return true;
	},
	/**
	 * Госпитализировать без направления
	 */
	hospitalize: function() {
		var me = this;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' ) {
					me.addEvnPS();
				}
			},
			msg: lang['vyi_deystvitelno_hotite_sozdat_kvs_bez_napravleniya'],
			title: lang['podtverjdenie']
		});
	},
	/**
	 * Внешнее направление
	 */
	ExtDirection: function() {
		var win = this;

		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if ( swPersonSearchWindow.isVisible() ) {
			sw.swMsg.alert('Окно поиска человека уже открыто', 'Для продолжения необходимо закрыть окно поиска человека.');
			return false;
		}

		var params = {
			action: 'add',
			callback: function(data) {},
			onSelect: function(pdata)
			{
				getWnd('swPersonSearchWindow').hide();
				var personData = new Object();

				personData.Person_id = pdata.Person_id;
				personData.Person_IsDead = pdata.Person_IsDead;
				personData.Person_Firname = pdata.PersonFirName_FirName;
				personData.Person_Surname = pdata.PersonSurName_Surname;
				personData.Person_Secname = pdata.PersonSecName_Secname;
				personData.PersonEvn_id = pdata.PersonEvn_id;
				personData.Server_id = pdata.Server_id;
				personData.Person_Birthday = pdata.Person_Birthday;

				var directionData = {
					LpuUnitType_SysNick: null
					, EvnQueue_id: null
					, QueueFailCause_id: null
					, Lpu_did: null // ЛПУ куда направляем
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
					, fromBj: false
				};

				var params = {
					userMedStaffFact: null,
					isDead: false,
					type: 'ExtDirKVS',
					personData: personData,
					directionData: directionData,
					callback: function() { this.hide(); },
					onDirection: function (dataEvnDirection_id) {
						win.loadGridWithFilter(false);
					}
				};
				getWnd('swDirectionMasterWindow').show(params);
			},
			searchMode: 'all'
		};
		getWnd('swPersonSearchWindow').show(params);
	},
	currentDay: function () {
		var date1 = Date.parseDate(this.curDate, 'd.m.Y');
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y') + ' - ' + Ext.util.Format.date(date2, 'd.m.Y'));
	},
	doReset: function() {
		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		this.currentDay();
		this.loadGridWithFilter(true);
	},
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	getCurrentDateTime: function() {
		var me = this;
		me.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				me.getLoadMask().hide();
				if ( success && response.responseText != '' ) {
					var result  = Ext.util.JSON.decode(response.responseText);
					me.curDate = result.begDate;
					me.curTime = result.begTime;
					me.userName = result.pmUser_Name;
					me.doReset();
				}
			},
			url: C_LOAD_CURTIME
		});
	},
	getSelectedRecord: function() {
		var record = this.EvnDirectionGrid.getGrid().getSelectionModel().getSelected();
		if ( !record || !record.get('EvnDirection_id') ) {
			return false;
		}
		return record;
	},
	initComponent: function() {
		var me = this;

		this.dateMenu = new Ext.form.DateRangeField({
			fieldLabel: lang['datyi_napravleniy'],
			listeners:  {
				'select': function () {
					me.doSearch();
				}
			},
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			width: 170
		});

		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 130,
			region: 'north',

			items: [{
				autoHeight: true,
				title: lang['napravlenie'],
				xtype: 'fieldset',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [
							//Даты направлений
							me.dateMenu
						]
					}, {
						layout: 'form',
						style: "padding-left: 5px",
						items: [{
							handler: function() {
								me.currentDay();
								me.doSearch();
							},
							text: lang['segodnya'],
							xtype: 'button'
						}]
					}, {
						border: false,
						layout: 'form',
						style: 'padding-left: 5px',
						hidden: !(getGlobalOptions().archive_database_enable),
						labelWidth: 0,
						items: [{
							allowBlank: true,
							name: 'autoLoadArchiveRecords',
							boxLabel: lang['uchityivat_arhivnyie_dannyie'],
							hideLabel: true,
							xtype: 'checkbox'
						}]
					},{
						layout: 'form',
						items: [{
							autoLoad: false,
							comboSubject: 'DirType',
							fieldLabel: lang['tip_napravleniya'],
							hiddenName: 'DirType_id',
							lastQuery: '',
							typeCode: 'int',
							width: 250,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						style: 'padding-left: 5px',
						items: [{
							comboSubject: 'LpuSectionProfile',
							fieldLabel: lang['profil_napravleniya'],
							hiddenName: 'LpuSectionProfile_id',
							listWidth: 300,
							width: 250,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							allowBlank: true,
							fieldLabel: langs('Направившая МО'),
							hiddenName: 'Lpu_sid',
							width: 300,
							xtype: 'swlpulocalcombo'
						}]
					}, {
						layout: 'form',
						style: 'padding-left: 5px',
						items: [{
							comboSubject: 'LpuSectionProfile',
							fieldLabel: 'Профиль направившего врача',
							hiddenName: 'MedPersonalProfile_id',
							width: 350,
							xtype: 'swcommonsprcombo'
						}]

					}, {
						layout: 'form',
						style: 'padding-left: 5px',
						items: [{
							allowBlank: true,
							hiddenName: 'Diag_id',
							//id: 'ReabAnketDiag',
							listWidth: 580,
							tabIndex: TABINDEX_EDPLEF + 5,
							width: 480,
							xtype: 'swdiagcombo'
						}]

					}]
				},{
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						style: 'padding-left: 5px',
						labelWidth: 0,
						items: [{
							allowBlank: true,
							name: 'isCanceled',
							boxLabel: 'Отменен/Отклонен',
							hideLabel: true,
							xtype: 'checkbox'
						}]
					},{
						layout: 'form',
						style: 'padding-left: 5px',
						items: [{
							allowBlank: true,
							comboSubject: 'EvnStatusCause',
							hiddenName: 'EvnStatusCause_id',
							fieldLabel: 'Причина отмены',
							typeCode: 'int',
							sortField: 'EvnStatusCause_Code',
							width: 350,
							xtype: 'swcommonsprcombo'
						}]

					}]
				}]
			}, {
				autoHeight: true,
				title: lang['patsient'],
				xtype: 'fieldset',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 400,
						items: [{
							fieldLabel: lang['familiya'],
							width: 250,
							xtype: 'textfield',
							name: 'Person_SurName'
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['imya'],
							xtype: 'textfield',
							name: 'Person_FirName'
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['otchestvo'],
							xtype: 'textfield',
							name: 'Person_SecName'
						}]
					},{
						layout: 'form',
						items: [{
							fieldLabel: lang['data_rojdeniya'],
							xtype: 'swdatefield',
							name: 'Person_BirthDay'
						}]
					},{
						border: false,
						hidden: getRegionNick() != 'kz',
						layout: 'form',
						xtype: 'panel',
						items: [{
							fieldLabel: langs('ИИН'),
							name: 'Person_INN',
							maxLength: 12,
							minLength: 12,
							xtype: 'numberfield',
							allowDecimals: false,
							allowNegative: false
						}]
					}
				]}
			]}, {
				autoHeight: true,
				title: 'Госпитализация',
				xtype: 'fieldset',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 220,
						items: [{
							comboSubject: 'YesNo',
							fieldLabel: lang['gospitalizatsiya_podtverjdena'],
							hiddenName: 'IsConfirmed',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.ENTER ) {
										me.doSearch();
									}
								}
							},
							width: 60,
							xtype: 'swcommonsprcombo'
						}]
					},{
						layout: 'form',
						labelWidth: 140,
						style: 'padding-left: 5px',
						items:
							[{
								width: 200,
								fieldLabel: 'Статус госпитализации',
								id: 'EDLW_PrehospStatus_id',
								xtype: 'swprehospstatuscombo'
							}]
					},{
						layout: 'form',
						items: [{
							fieldLabel: 'Дата госпитализации',
							xtype: 'swdatefield',
							name: 'EvnPS_setDate'
						}]
					},{
						layout: 'form',
						items: [{
							allowBlank: true,
							fieldLabel: 'Исход госпитализации',
							hiddenName: 'LeaveType_id',
							width: 300,
							xtype: 'swleavetypecombo'
						}]
					}, {
						layout: 'form',
						items: [{
							hiddenName: 'PrehospWaifRefuseCause_id',
							fieldLabel: langs('Отказ'),
							width: 250,
							comboSubject: 'PrehospWaifRefuseCause',
							autoLoad: true,
							xtype: 'swcommonsprcombo'
						}]
					}]
				}]
			}]
		});

		this.EvnDirectionGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			toolbar: true,
			tbActions:true,
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh', hidden: true, disabled: true },
				{ name: 'action_print', hidden: true, disabled: true }
				,{ name:'action_confirm', position: 10, text: langs('Подтвердить госпитализацию'), iconCls : 'x-btn-text', icon: 'img/icons/edit16.png', handler: function() { me.confirmEvnDirection(); }}
				,{ name:'action_viewdir', position: 11, text: langs('Просмотреть'), tooltip: langs('Просмотреть электронное направление'), iconCls : 'x-btn-text', icon: 'img/icons/view16.png', handler: function() { me.openEvnDirectionViewWindow(); }}
				,{ name:'action_extdir', position: 12, text: langs('Внешнее направление'), tooltip: langs(''), handler: function() { me.ExtDirection(); }}
				,{ name:'action_hospitalize_by_dir', position: 13, text: langs('Госпитализировать по направлению'), handler: function() { me.dirHospitalize(); }}
				,{ name:'action_hospitalize', position: 14, text: langs('Госпитализировать без направления'), handler: function() { me.hospitalize(); }}
				,{ name:'action_print_all', position: 15, text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT_TIP, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', handler: function() { me.EvnDirectionGrid.printObjectListFull(); }}
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnDirection&m=loadEvnDirectionJournal',
			id: 'EDJW_EvnDirectionGrid',
			layout: 'fit',
			object: 'EvnDirection',
			onDblClick: function() {
				me.openEvnDirectionViewWindow();
			},
			onEnter: function() {
				me.openEvnDirectionViewWindow();
			},
			onLoadData: function(has_records) {
				this.setActionDisabled('action_print_all', !has_records);
			},
			onRowSelect: function(sm, index, record) {
				//log(record);
				this.setActionDisabled('action_confirm', ( 'true'==record.get('IsConfirmed') || !record.get('DirType_Code') || !record.get('DirType_Code').inlist(me.getConfirmableDirTypeCodeArray()) || record.get('DirFailType_Name') ));
				this.setActionDisabled('action_hospitalize_by_dir', (me.ARMType_id !=3 || !record.get('EvnDirection_id') || record.get('EvnStatus_id') == 15 || record.get('DirFailType_Name')/*|| 'false'==record.get('IsConfirmed')*/ ));
				this.setActionDisabled('action_hospitalize', (me.ARMType_id !=3) || record.get('DirFailType_Name'));
				this.setActionDisabled('action_viewdir', !record.get('EvnDirection_id') || record.get('DirFailType_Name') || record.get('DirFailType_Name'));
				this.setActionDisabled('action_extdir', record.get('DirFailType_Name'));
				this.setActionDisabled('action_print_all', record.get('DirFailType_Name'));
			},
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'IsConfirmed', header: lang['podtverjdenie'], renderer: sw.Promed.Format.checkColumn, width: 100},
                { name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'Lpu_Name', type: 'string', header: 'Направляющая МО' , width: 150 },
				{ name: 'Org_Nick', type: 'string', hidden: true },
				{ name: 'Org_Name', type: 'string', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'EvnDirection_Num', type: 'string', header: lang['nomer'], width: 70 },
				{ name: 'DirType_id', type: 'int', hidden: true },
				{ name: 'DirType_Code', type: 'string', hidden: true },
				{ name: 'DirType_Name', type: 'string', header: lang['tip_napravleniya'], width: 150 },
				{ name: 'DirFailType_Name', type: 'string', header: 'Причина отмены ', width: 150 },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil_napravleniya'], width: 150 },
				{ name: 'LpuSection_id', type: 'int', hidden: true },
				{ name: 'LpuSection_did', type: 'int', hidden: true },
				{ name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 150 },//Отделение текущего ЛПУ, куда направлен больной.
				{name: 'EvnQueue_Days', type: 'int', hidden: true},
				{
					name: 'EvnDirection_setDate',
					width: 80,
					header: lang['data_napravleniya'],
					renderer: function (value, cell, record) {
						var days = false;
						if (record.get('DirType_id') && record.get('DirType_id').inlist([16, 3])) { // На поликлинический прием и На консультацию
							days = parseInt(getGlobalOptions().promed_waiting_period_polka);
						} else if (record.get('DirType_id') && record.get('DirType_id').inlist([1, 5])) { // На госпитализацию плановую и На госпитализацию экстренную
							days = parseInt(getGlobalOptions().promed_waiting_period_stac);
						}
						if (days && !isNaN(days) && record.get('EvnQueue_Days') && record.get('EvnQueue_Days') > days) {
							var daysText = days + ' ' + ru_word_case('день', 'дня', 'дней', days);
							return value + " <img src='/img/icons/warn_red_round12.png' ext:qtip='Направление с периодом ожидания более " + daysText + "!' />";
						}
						return value;
					},
				},
				{ name: 'EvnDirection_setTime', type: 'string', header: lang['vremya_napravleniya'], hidden: true, width: 70 },
				{ name: 'TimetableStac_setDate', type: 'string', header: 'Запись на дату', width: 70 },
				{ name: 'PrehospStatus_id', type: 'int', hidden: true },
				{ name: 'PrehospStatus_Name', type: 'string', header: 'Статус госпитализации', width: 120},
				{ name: 'EvnDirection_desDT', type: 'date', header: lang['jelaemaya_data'], width: 70 , hidden:(getGlobalOptions().region.nick != 'astra')},
				{ name: 'Person_id', type: 'int', hidden: true},
				{ name: 'PersonEvn_id', type: 'int', hidden: true},
				{ name: 'Server_id', type: 'int', hidden: true},
				{ name: 'Person_Fio', type: 'string', header: lang['fio_patsienta'], autoexpand: true, autoExpandMin: 150 },
				{ name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 70},
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz_po_mkb'], width: 120 },
				{ name: 'EvnDirection_Descr', type: 'string', header: lang['obosnovanie_napravleniya'], width: 150 },
				{ name: 'MedPersonal_id', type: 'int', hidden: true},
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Мед. работник', width: 120 },//ФИО направившего мед. работника
				{ name: 'MedPersonalProfile_Name', type: 'string', header: 'Профиль направившего врача', width: 150 },
				{ name: 'EvnPS_setDate', type: 'date', header: 'Дата госпитализации', width: 70 },
				{ name: 'LeaveType_Name', type: 'string', header: 'Исход', width: 70 },
				{ name: 'EvnStatus_id', type: 'int', hidden: true, width: 50, header: 'test' }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					me.doSearch();
				},
				iconCls: 'search16',
				text: lang['nayti']
			}, {
				handler: function() {
					me.doReset();
				},
				iconCls: 'resetsearch16',
				text: lang['sbros']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					me.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: TABINDEX_ARMSTAC + 50,
				onTabAction: function() {
					me.dateMenu.focus(true);
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				this.FilterPanel,
				this.EvnDirectionGrid
			]
		});

		sw.Promed.swEvnDirectionJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	getFilterForm: function() {
		return this.FilterPanel;
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.EvnDirectionGrid;

		if (!base_form.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		grid.removeAll();

		var beg_date = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		var end_date = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

		var params = base_form.getValues();
		this.EvnDirectionGrid.showArchive = !Ext.isEmpty(params.autoLoadArchiveRecords);

		if ( clear ) {
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					beg_date: beg_date,
					end_date: end_date,
					Person_SurName: null,
					Person_FirName: null,
					Person_SecName: null,
					Person_BirthDay: null,
					Person_INN: null,
					DirType_id: null,
					LpuSectionProfile_id: null,
					IsConfirmed : null,
					isCanceled: false
				}
			});
		}
		else {
			grid.loadData({
				globalFilters: {
					beg_date: beg_date,
					end_date: end_date,
					DirType_id: base_form.findField('DirType_id').getValue() || null,
					IsConfirmed: base_form.findField('IsConfirmed').getValue() || null,
					LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue() || null,
					Person_BirthDay: base_form.findField('Person_BirthDay').getRawValue() || null,
					Person_SurName: base_form.findField('Person_SurName').getValue() || null,
					Person_FirName: base_form.findField('Person_FirName').getValue() || null,
					Person_SecName: base_form.findField('Person_SecName').getValue() || null,
					Person_INN: base_form.findField('Person_INN').getValue() || null,
					isCanceled: base_form.findField('isCanceled').getValue() || null,
					EvnStatusCause_id: base_form.findField('EvnStatusCause_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue() || null,
					MedPersonalProfile_id: base_form.findField('MedPersonalProfile_id').getValue() || null,
					Lpu_sid: base_form.findField('Lpu_sid').getValue() || null,
					PrehospStatus_id: this.findById('EDLW_PrehospStatus_id').getValue() || null,
					EvnPS_setDate: base_form.findField('EvnPS_setDate').getRawValue() || null,
					LeaveType_id: base_form.findField('LeaveType_id').getValue() || null,
					PrehospWaifRefuseCause_id: base_form.findField('PrehospWaifRefuseCause_id').getValue() || null,

					limit: 100,
					start: 0
				}
			});
		}
	},
	/**
	 * Открывает форму электронного направления для просмотра
	 */
	openEvnDirectionViewWindow: function() {
		var record = this.getSelectedRecord();
		if ( record == false ) {
			return false;
		}
		this.openForm('swEvnDirectionEditWindow', lang['prosmotr_elektronnogo_napravleniya'], {
			action: 'view',
			formParams: {},
			Person_id: record.data.Person_id,
			EvnDirection_id: record.get('EvnDirection_id')
		});
		return true;
	},
	openForm: function(name, title, params) {
		if ( getWnd(name).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['forma'] + title + lang['v_dannyiy_moment_otkryita']);
			return false;
		} else {
			getWnd(name).show(params);
			return true;
		}
	},
	getVisibleDirTypeCodeArray: function() {
		return ['1','4','5','6'];
	},
	getConfirmableDirTypeCodeArray: function() {
		return ['1','4','5','6'];
	},
	show: function() {
		sw.Promed.swEvnDirectionJournalWindow.superclass.show.apply(this, arguments);

		var me = this;

		if ( getRegionNick().inlist([ 'astra' ]) ) {
			this.EvnDirectionGrid.addActions({name:'action_EvnDirectionExt', text: lang['vneshnie_napravleniya'], handler: function() { getWnd('swEvnDirectionExtWindow').show(); }});
		}

		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last || null;
		this.ARMType_id = (this.userMedStaffFact && this.userMedStaffFact.ARMType_id) || null;

		var base_form = this.FilterPanel.getForm();
		base_form.reset();
		var dir_type_combo = base_form.findField('DirType_id');
		dir_type_combo.getStore().clearFilter();
		dir_type_combo.getStore().filterBy(function(rec){
			return (rec.get('DirType_Code').inlist(me.getVisibleDirTypeCodeArray()));
		});
		this.getCurrentDateTime();
		base_form.findField('EvnStatusCause_id').getStore().clearFilter();
		base_form.findField('EvnStatusCause_id').lastQuery = '';
		base_form.findField('EvnStatusCause_id').getStore().filterBy(function (rec) {
			var flag = true;
			var EvnStatusCauseCodeList = [ '1', '5', '18', '22'];
			switch (getRegionNick()) {
				case 'kareliya':
					EvnStatusCauseCodeList.push('6');
					EvnStatusCauseCodeList.push('24');
					break;

				case 'ufa':
					EvnStatusCauseCodeList.push('2');
					EvnStatusCauseCodeList.push('9');
					break;

				case 'kz':
					EvnStatusCauseCodeList.push('4');
					break;
				case 'ekb':
					EvnStatusCauseCodeList.push('4');
					break;
			}
			flag = rec.get('EvnStatusCause_Code').toString().inlist(EvnStatusCauseCodeList);
			return flag;
		});
		if (getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov', 'adygeya'])) {
			base_form.findField('LeaveType_id').getStore().clearFilter();
			base_form.findField('LeaveType_id').lastQuery = '';
			base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
				var flag = true;

				if (getRegionNick() == 'buryatiya') {
					topCode = 106;
				} else if (getRegionNick() == 'krym') {
					topCode = 110;
				} else {
					topCode = 199;
				}
				if (!(rec.get('LeaveType_Code') >= 101 && rec.get('LeaveType_Code') <= topCode)) {
					flag = false;
				}
				return flag;
			});
		}
		

		this.EvnDirectionGrid.setColumnHidden('PrehospStatus_Name', !(this.userMedStaffFact && this.userMedStaffFact.ARMType == 'stacpriem'));
		base_form.findField('EDLW_PrehospStatus_id').setContainerVisible(this.userMedStaffFact && this.userMedStaffFact.ARMType == 'stacpriem');
	}
});
