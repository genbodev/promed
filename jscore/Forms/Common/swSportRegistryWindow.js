/**
 * swSportRegistryWindow - окно регистра спортсменов
 *
 *
 * @package      SportRegistry
 * @access       public
 * @author       Хамитов Марат
 * @version      12.2018
 *
 */
sw.Promed.swSportRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Регистр спортсменов'),
	width: 800,
	codeRefresh: true,
	objectName: 'swSportRegistryWindow',
	id: 'swSportRegistryWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function () {
		return Ext.getCmp('SR_SearchButton');
	},
	inArray: function (needle, array) {
		for (var k in array) {
			if (array[k] == needle)
				return true;
		}
		return false;
	},
	doReset: function () {
		var base_form = this.findById('SportRegistryFilterForm').getForm();
		base_form.reset();
		this.SportRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.SportRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.SportRegistrySearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function (params) {

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('SportRegistryFilterForm').getForm();

		if (!params.firstLoad && this.findById('SportRegistryFilterForm').isEmpty()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function () {
			});
			return false;
		}

		var grid = this.SportRegistrySearchFrame.getGrid();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (base_form.findField('PersonPeriodicType_id').getValue().toString().inlist(['2', '3']) && (!params.ignorePersonPeriodicType)) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('SportRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;

		if (base_form.isValid()) {
			this.SportRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}

	},
	getRecordsCount: function () {
		var base_form = this.getFilterForm().getForm();

		if (!base_form.isValid()) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if (post.PersonPeriodicType_id == null) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Records_Count != undefined) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					} else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	openSportRegisterUMOWindow: function (action) {
		if (action == 'include') {
			getWnd('swPersonSearchWindow').show({
				onSelect: function (person_data) {
					if (person_data.Person_Age % 10 === 1)
						person_data.Person_Age += ' год';
					else if (person_data.Person_Age % 10 >= 2 && person_data.Person_Age % 10 <= 4)
						person_data.Person_Age += ' года';
					else
						person_data.Person_Age += ' лет';

					swSportRegistryWindow.checkInSportRegister(person_data);
				}
			});
		} else {
			var rec = this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected();
			if (action == 'edit') {
				var params = {
					Person_id: rec.data.Person_id,
					Server_id: rec.data.Server_id,
					SportRegisterUMO_id: rec.data.SportRegisterUMO_id,
					PersonRegisterOutCause_id: rec.data.PersonRegisterOutCause_id,
					SportRegister_delDT: rec.data.SportRegister_delDT,
					SportRegister_insDT: rec.data.SportRegister_insDT,
					SportRegister_updDT: rec.data.SportRegister_updDT,
					Person_Age: rec.data.Person_Age,
					SportRegister_id: rec.data.SportRegister_id,
					isEdit: true,
					isAdding: false,
				}
				console.log('Sending params to "personSportRegistryWindow"', params);
				getWnd('personSportRegistryWindow').show(params);
			} else if (action == 'view') {
				var params = {
					Person_id: rec.data.Person_id,
					Server_id: rec.data.Server_id,
					SportRegisterUMO_id: rec.data.SportRegisterUMO_id,
					PersonRegisterOutCause_id: rec.data.PersonRegisterOutCause_id,
					SportRegister_delDT: rec.data.SportRegister_delDT,
					SportRegister_insDT: rec.data.SportRegister_insDT,
					SportRegister_updDT: rec.data.SportRegister_updDT,
					Person_Age: rec.data.Person_Age,
					SportRegister_id: rec.data.SportRegister_id,
					isEdit: false,
					isAdding: false,
				}
				console.log('Sending params to "personSportRegistryWindow"', params);
				getWnd('personSportRegistryWindow').show(params);
			}
		}
	},
	checkInSportRegister: function (person_data) {
		Ext.Ajax.request({
			url: '/?c=SportRegister&m=checkInSportRegister',
			params: {
				Person_id: person_data.Person_id
			},
			callback: function (options, success, response) {
				if (success) {
					var resp = Ext.util.JSON.decode(response.responseText);
					if (resp.length != 0) {
						var resp = Ext.util.JSON.decode(response.responseText);
						person_data.SportRegister_id = resp[0].SportRegister_id;
						person_data.PersonRegisterOutCause_id = resp[0].PersonRegisterOutCause_id;
						person_data.SportRegister_delDT = resp[0].SportRegister_delDT;
						person_data.SportRegister_insDT = resp[0].SportRegister_insDT;
						person_data.SportRegister_updDT = resp[0].SportRegister_updDT;
						person_data.isEdit = true;
						person_data.isAdding = true;
						getWnd('personSportRegistryWindow').show(person_data);
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									Ext.Ajax.request({
										url: '/?c=SportRegister&m=addSportRegister',
										params: {
											Person_id: person_data.Person_id,
											pmUser_id: parseInt(getGlobalOptions().pmuser_id)
										},
										callback: function (options, success, response) {
											if (success) {
												var resp = Ext.util.JSON.decode(response.responseText);
												person_data.SportRegister_id = resp[0].SportRegister_id;
												person_data.PersonRegisterOutCause_id = resp[0].PersonRegisterOutCause_id;
												person_data.SportRegister_delDT = resp[0].SportRegister_delDT;
												person_data.SportRegister_insDT = resp[0].SportRegister_insDT;
												person_data.SportRegister_updDT = resp[0].SportRegister_updDT;
												person_data.isEdit = true;
												person_data.isAdding = true;
												getWnd('personSportRegistryWindow').show(person_data);
											} else {
												return false;
											}
										}.createDelegate(this)
									});
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Выбранный спортсмен отсутствует в регистре. Добавить его?'),
							title: langs('Предупреждение')
						});
					}
				} else {
					return false;
				}
			}.createDelegate(this)
		});
	},
	initComponent: function () {
		var win = this;

		this.SportRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add', handler: function () {
						this.openSportRegisterUMOWindow('include');
					}.createDelegate(this)
				},
				{
					name: 'action_edit', handler: function () {
						this.openSportRegisterUMOWindow('edit');
					}.createDelegate(this)
				},
				{
					name: 'action_view', handler: function () {
						this.openSportRegisterUMOWindow('view');
					}.createDelegate(this)
				},
				{name: 'action_delete', hidden: true, handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
			id: 'SportRegistry',
			object: 'SportRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'SportRegisterUMO_id', type: 'int', header: 'ID', key: true },
				{ name: 'SportRegister_id', type: 'int', header: langs('Идентификатор спортсмена'), key: false, hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'PersonRegisterOutCause_id', type: 'int', hidden: true },
				{ name: 'isOut', type: 'checkbox', header: langs('Исключен'), width: 70 },
				{ name: 'Person_SurName', type: 'string', header: langs('Фамилия'), width: 170 },
				{ name: 'Person_FirName', type: 'string', header: langs('Имя'), width: 170 },
				{ name: 'Person_SecName', type: 'string', header: langs('Отчество'), width: 170 },
				{ name: 'Person_BirthDay', type: 'date', format: 'd.m.Y', hidden: true, header: langs('Д/р'), width: 90 },
				{ name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 70 },
				{ name: 'SportType_name', type: 'string', format: 'string', header: langs('Вид спорта'), width: 130 },
				{ name: 'SportStage_name', type: 'string', format: 'string', header: langs('Этап спорт.подготовки'), width: 180 },
				{ name: 'SportRegisterUMO_UMODate', type: 'date', format: 'd.m.Y', header: langs('Дата последнего УМО'), width: 130 },
				{ name: 'UMOResult_name', type: 'string', format: 'string', header: langs('Заключение'), width: 150 },
				{ name: 'SportCategory_name', type: 'string', format: 'string', header: langs('Спортивный разряд'), width: 130 },
				{ name: 'InvalidGroupType_Name', type: 'string', format: 'string', header: langs('Группа инвалидности'), width: 125 },
				{ name: 'SportParaGroup_name', type: 'string', format: 'string', header: langs('Паралимп. группа'), width: 110 },
				{ name: 'SportRegisterUMO_IsTeamMember', type: 'checkbox', header: langs('Сборник'), width: 60 },
				{ name: 'SportRegisterUMO_AdmissionDtBeg', type: 'date', format: 'd.m.Y', header: langs('Допуск с'), width: 70 },
				{ name: 'SportRegisterUMO_AdmissionDtEnd', type: 'date', format: 'd.m.Y', header: langs('Допуск до'), width: 70 },
				{ name: 'MedPersonal_pname', type: 'string', header: 'Врач', width: 250 },
				/*{ name: 'SportOrg_name', type: 'string', format: 'string', header: langs('Спорт орг-я'), width: 120 },*/
				/*{ name: 'SportTrainer_name', type: 'string', format: 'string', header: langs('Тренер'), width: 250 },*/
				{ name: 'SportRegister_updDT', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 90, /*hidden: true*/ },
				{ name: 'SportRegister_insDT', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 90, hidden: true },
				{ name: 'SportRegister_delDT', type: 'date', format: 'd.m.Y', header: langs('Дата исключения из регистра'), width: 90, /*hidden: true*/ },
				{ name: 'PersonRegisterOutCause_Name', type: 'string', format: 'string', header: langs('Причина исключения'), width: 200/*, hidden: true*/ }
			],
			focusOnFirstLoad: false,
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function () {
				this.getButtonSearch().disable();
				/*if (Ext.getCmp('SportRegisterType').getValue() == 2) {
					swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getColumnModel().setHidden(25,true)
				} else {
					swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getColumnModel().setHidden(25,false)
				}*/
			}.createDelegate(this),
			onLoadData: function () {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function (sm, index, record) {
				//console.log('sm',sm.getSelected(),'index',index,'record',record);
				this.getAction('action_delete').setDisabled(Ext.isEmpty(record.get('SportRegisterUMO_id')));
				this.getAction('action_edit').setDisabled(Ext.isEmpty(record.get('SportRegisterUMO_id')));
				this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('SportRegisterUMO_id')));
				this.getAction('person_register_restore').setHidden(record.get('PersonRegisterOutCause_id') == 1 ? true : Ext.isEmpty(record.get('SportRegister_delDT')));
				this.getAction('person_register_out').setHidden(!Ext.isEmpty(record.get('SportRegister_delDT')));
			},
			onDblClick: function (x, c, v) {

			}
		});
		
		this.SportRegistrySearchFrame.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass : function (row, index)
				{
					var cls = '';
					if(row.get('SportRegisterUMO_AdmissionDtEnd') > new Date.now() - 86400000) // 86400000 – день в миллисекундак
						cls = "x-grid-rowgreen " ;
					if(row.get('PersonRegisterOutCause_id')  == 1 )
						cls = "x-grid-rowgray " ;
					if (cls.length == 0)
						cls = 'x-grid-panel';
					
					return cls;
				}
			});

		var SportStore = this.SportRegistrySearchFrame.getGrid().on(
			'rowdblclick',
			function () {
				var rec = this.getSelectionModel().getSelected();
				//console.log('REC', rec);
				var params = {
					Person_id: rec.data.Person_id,
					Server_id: rec.data.Server_id,
					SportRegisterUMO_id: rec.data.SportRegisterUMO_id,
					PersonRegisterOutCause_id: rec.data.PersonRegisterOutCause_id,
					SportRegister_delDT: rec.data.SportRegister_delDT,
					SportRegister_insDT: rec.data.SportRegister_insDT,
					SportRegister_updDT: rec.data.SportRegister_updDT,
					Person_Age: rec.data.Person_Age,
					SportRegister_id: rec.data.SportRegister_id,
					isEdit: false,
					isAdding: false,
				}
				console.log('Sending params to "personSportRegistryWindow"', params);
				getWnd('personSportRegistryWindow').show(params);
			}
		);

		var SportStore = this.SportRegistrySearchFrame.getGrid().getStore().on(
			'load',
			function () {
				var unique_ids = [];
				var Person_ids = [];

				var recs = this.data.items;

				console.log('Search Grid Store', recs);

				var currentdate = new Date.now();
				
				for (var k in recs) {
					if (typeof recs[k] == 'object') {
							
						// Формируем данные о возрасте
						if (recs[k].get('Person_BirthDay')) {
							recs[k].set('Person_Age', Math.floor((currentdate - recs[k].get('Person_BirthDay')) / (1000 * 60 * 60 * 24 * 365)));
							if (recs[k].get('Person_Age') % 10 === 1)
								recs[k].set('Person_Age', recs[k].get('Person_Age') + ' год');
							else if (recs[k].get('Person_Age') % 10 >= 2 && recs[k].get('Person_Age') % 10 <= 4)
								recs[k].set('Person_Age', recs[k].get('Person_Age') + ' года');
							else
								recs[k].set('Person_Age', recs[k].get('Person_Age') + ' лет');
						}

						if (recs[k].get('IsTeamMemberFilter') == 1)
							recs[k].set('IsTeamMemberFilter', true);
						else
							recs[k].set('IsTeamMemberFilter', false);

						if (recs[k].get('SportRegister_delDT'))
							recs[k].set('isOut', true);
						else
							recs[k].set('isOut', false);

						var SHOW_DUBLICATES = false;

						if (recs[k].data.Person_id) {
							if (!Person_ids.includes(recs[k].get('Person_id')) || SHOW_DUBLICATES) {
								Person_ids.push(recs[k].get('Person_id'));
							}
						}
					}
				}
				this.filterBy(function (record, id) {
					var filter = Person_ids.includes(record.get('Person_id'));
					Person_ids.remove(record.get('Person_id'));
					return filter;
				});
				this.totalLength = this.getCount();
				this.commitChanges();
			});

		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_ORW + 120,
				id: 'SR_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function () {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function () {
					this.getRecordsCount();
				}.createDelegate(this),
				tabIndex: TABINDEX_ORW + 123,
				text: BTN_FRMCOUNT
			},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function () {
						this.findById('SR_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('SR_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}],
			getFilterForm: function () {
				if (this.filterForm == undefined) {
					this.filterForm = this.findById('SportRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
				isDisplayPersonRegisterRecordTypeField: false,
				allowPersonPeriodicSelect: true,
				id: 'SportRegistryFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'SportRegistry',
				tabIndexBase: TABINDEX_ORW,
				tabPanelHeight: 225,
				tabPanelId: 'SR_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function () {
							this.getFilterForm().getForm().findField('SportRegisterType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: langs('<u>6</u>. Регистр'),
					items: [
						{
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'combo',
									fieldLabel: 'Тип записи',
									hiddenName: 'SportRegisterType_id',
									labelAlign: 'left',
									editable: false,
									id: 'SportRegisterType',
									mode: 'local',
									width: 200,
									triggerAction: 'all',
									allowBlank: false,
									store: new Ext.data.SimpleStore({
										fields: [
											{name: 'SportRegisterType_id', type: 'int'},
											{name: 'SportRegisterType_Name', type: 'string'}
										],
										data: [
											['1', langs('Все')],
											['2', langs('Включенные в регистр')],
											['3', langs('Исключенные из регистра')]
										],
									}),
									displayField: 'SportRegisterType_Name',
									valueField: 'SportRegisterType_id',
									listeners: {
										scope: this,
										'render': function () {
											Ext.getCmp('SportRegisterType').setValue('2');
										},
										'change': function(obj) {
											
										}
									}
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: 'Врач',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getMedPersonalFilter',
										fields: [
											{name: 'MedPersonal_pid', type: 'int'},
											{name: 'MedPersonal_pname', type: 'string'}
										],
										sortInfo: {
											direction: 'ASC',
											field: 'MedPersonal_pname'
										},
										key: 'MedPersonal_pid',
									}),
									editable: true,
									triggerAction: 'all',
									id: 'MedPersonalPFilter',
									hiddenName: 'MedPersonal_pid',
									displayField: 'MedPersonal_pname',
									valueField: 'MedPersonal_pid',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{MedPersonal_pname} ' + '&nbsp;' +
										'</div></tpl>',
									listeners: {
										change: function () {

										},
										keydown: function (inp, e) {

										},
										beforequery: function (q) {
											if (q.combo.getStore().getCount() == 0) {
												q.combo.getStore().removeAll();
												q.combo.getStore().load();
											}
										}
									}
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: 'Вид спорта',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getSportType',
										autoLoad: true,
										fields: [
											{name: 'SportType_id', type: 'int'},
											{name: 'SportType_name', type: 'string'}
										],
										key: 'SportType_id',
									}),
									editable: true,
									triggerAction: 'all',
									hiddenName: 'SportType_id',
									displayField: 'SportType_name',
									valueField: 'SportType_id',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportType_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: 'Тренер',
									mode: 'local',
									store: new Ext.data.SimpleStore({
										//url: '/?c=SportRegister&m=getSportTrainer',
										fields: [
											{name: 'SportTrainer_id', type: 'int'},
											{name: 'SportTrainer_name', type: 'string'}
										],
										sortInfo: {
											direction: 'ASC',
											field: 'SportTrainer_name'
										},
										key: 'SportTrainer_id',
									}),
									editable: true,
									id: 'SportTrainerFilter',
									triggerAction: 'all',
									hiddenName: 'SportTrainer_id',
									displayField: 'SportTrainer_name',
									valueField: 'SportTrainer_id',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportTrainer_name} ' + '&nbsp;' +
										'</div></tpl>',
									listeners: {
										beforequery: function () {
											Ext.Ajax.request({
												url: '/?c=SportRegister&m=getSportTrainer',
												params: {
													SportTrainer_name: this.getRawValue() == '' ? '%' : this.getRawValue()
												},
												callback: function (options, success, response) {
													if (success === true) {
														var resp = Ext.util.JSON.decode(response.responseText);
														//console.log('resp', resp);
														var finalResp = resp.map(function (obj) {
															return [obj.SportTrainer_id, obj.SportTrainer_name];
														});
														Ext.getCmp('SportTrainerFilter').getStore().loadData(finalResp)
													} else {
														return false;
													}
												}
											});
										}
									}
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: 'Этап спорт. подготовки',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getSportStage',
										autoLoad: true,
										fields: [
											{name: 'SportStage_id', type: 'int'},
											{name: 'SportStage_name', type: 'string'}
										],
										key: 'SportStage_id',
									}),
									editable: false,
									triggerAction: 'all',
									hiddenName: 'SportStage_id',
									displayField: 'SportStage_name',
									valueField: 'SportStage_id',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportStage_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									xtype: 'combo',
									fieldLabel: 'Сборник',
									hiddenName: 'IsTeamMember_id',
									labelAlign: 'left',
									editable: false,
									id: 'IsTeamMemberFilter',
									mode: 'local',
									width: 200,
									triggerAction: 'all',
									store: new Ext.data.SimpleStore({
										fields: [
											{name: 'IsTeamMember_id', type: 'int'},
											{name: 'IsTeamMember_name', type: 'string'}
										],
										data: [
											['2', langs('Да')],
											['1', langs('Нет')]
										],
									}),
									displayField: 'IsTeamMember_name',
									valueField: 'IsTeamMember_id',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{IsTeamMember_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: 'Спортивный разряд',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getSportCategory',
										autoLoad: true,
										fields: [
											{name: 'SportCategory_id', type: 'int'},
											{name: 'SportCategory_name', type: 'string'}
										],
										key: 'SportCategory_id',
									}),
									editable: false,
									triggerAction: 'all',
									hiddenName: 'SportCategory_id',
									displayField: 'SportCategory_name',
									valueField: 'SportCategory_id',
									width: 200,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportCategory_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: 'Гр. инвалидности',
									mode: 'local',
									id: 'InvalidGroupTypeFilter',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getDisabilityGroup',
										autoLoad: true,
										fields: [
											{name: 'InvalidGroupType_id', type: 'int'},
											{name: 'InvalidGroupType_Name', type: 'string'}
										],
										key: 'InvalidGroupType_id',
									}),
									editable: false,
									triggerAction: 'all',
									hiddenName: 'InvalidGroupType_id',
									displayField: 'InvalidGroupType_Name',
									valueField: 'InvalidGroupType_id',
									width: 200,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{InvalidGroupType_Name} ' + '&nbsp;' +
										'</div></tpl>'
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: 'Спортивная школа',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getSportOrg',
										autoLoad: true,
										fields: [
											{name: 'SportOrg_id', type: 'int'},
											{name: 'SportOrg_name', type: 'string'}
										],
										key: 'SportOrg_id',
									}),
									editable: true,
									triggerAction: 'all',
									hiddenName: 'SportOrg_id',
									displayField: 'SportOrg_name',
									valueField: 'SportOrg_id',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportOrg_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: 'Паралимпийская гр.',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getSportParaGroup',
										autoLoad: true,
										fields: [
											{name: 'SportParaGroup_id', type: 'int'},
											{name: 'SportParaGroup_name', type: 'string'}
										],
										key: 'SportParaGroup_id',
									}),
									editable: false,
									triggerAction: 'all',
									hiddenName: 'SportParaGroup_id',
									displayField: 'SportParaGroup_name',
									valueField: 'SportParaGroup_id',
									width: 200,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{SportParaGroup_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: 'Заключение',
									mode: 'local',
									store: new Ext.data.JsonStore({
										url: '/?c=SportRegister&m=getUMOResult',
										autoLoad: true,
										fields: [
											{name: 'UMOResult_id', type: 'int'},
											{name: 'UMOResult_name', type: 'string'}
										],
										key: 'UMOResult_id',
									}),
									editable: false,
									triggerAction: 'all',
									hiddenName: 'UMOResult_id',
									displayField: 'UMOResult_name',
									valueField: 'UMOResult_id',
									width: 200,
									listWidth: 300,
									xtype: 'combo',
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
										'{UMOResult_name} ' + '&nbsp;' +
										'</div></tpl>',
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: langs('Допуск с'),
									name: 'SportRegisterUMO_AdmissionDtBeg',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 200,
									xtype: 'daterangefield'
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: langs('Дата УМО'),
									name: 'SportRegisterUMO_UMODate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 200,
									xtype: 'daterangefield'
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 150,
								items: [{
									fieldLabel: langs('Допуск до'),
									name: 'SportRegisterUMO_AdmissionDtEnd',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									width: 200,
									xtype: 'daterangefield'
								}]
							}]
						}]
				}
				]
			}),
				this.SportRegistrySearchFrame]
		});

		sw.Promed.swSportRegistryWindow.superclass.initComponent.apply(this, arguments);

	},
	layout: 'border',
	listeners: {
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('SportRegistryFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('SportRegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('SR_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('SportRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function () {
		sw.Promed.swSportRegistryWindow.superclass.show.apply(this, arguments);
		
		this.SportRegistrySearchFrame.addActions({
			name: 'person_register_restore',
			id: 'presonRegisterRestore',
			text: langs('Восстановить в регистр'),
			tooltip: langs('Восстановить в регистр'),
			iconCls: 'refresh16',
			hidden: true,
			handler: function () {
				if (this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected()) {
					var params = {
						SportRegister_id: this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected().data.SportRegister_id
					}
					this.restoreSportRegister(params);
				} else {
					sw.swMsg.show({
						title: 'Ошибка',
						msg: 'Для начала выберите запись',
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.WARNING
					});
				}
			}.createDelegate(this)
		}, 10);

		this.SportRegistrySearchFrame.addActions({
			name: 'person_register_out',
			id: 'presonRegisterOut',
			text: langs('Исключить из регистра'),
			tooltip: langs('Исключить из регистра'),
			iconCls: 'delete16',
			hidden: true,
			handler: function () {
				if (this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected()) {
					var params = {
						SportRegister_id: this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected().data.SportRegister_id,
						Person_id: this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected().data.Person_id
					}
					getWnd('swSportRegistryOutCause').show(params);
				} else {
					sw.swMsg.show({
						title: 'Ошибка',
						msg: 'Для начала выберите запись',
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.WARNING
					});
				}
			}.createDelegate(this)
		}, 10);
		
		this.SportRegistrySearchFrame.addActions({
			name: 'person_register_umo_delete',
			text: langs('Удалить УМО'),
			tooltip: langs('Удалить УМО'),
			iconCls: 'delete16',
			hidden: true,
			handler: function () {
				if (this.currentSportRegisterUMO_id) {
					var currentSportRegisterUMO_id = this.SportRegistrySearchFrame.getGrid().getSelectionModel().getSelected().data.SportRegisterUMO_id;
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет удаление..."});
								loadMask.show();
								Ext.Ajax.request({
									url: '/?c=SportRegister&m=deleteSportRegisterUMO',
									params: {
										SportRegisterUMO_id: currentSportRegisterUMO_id,
										pmUser_id: parseInt(getGlobalOptions().pmuser_id)
									},
									callback: function (options, success, response) {
										loadMask.hide();
										if (success) {
											var resp = Ext.util.JSON.decode(response.responseText);
											this.SportRegistrySearchFrame.getGrid().getStore().load();
											sw.swMsg.show({
												title: 'Успешно',
												msg: 'Данные УМО были удалены',
												buttons: Ext.Msg.OK,
												icon: Ext.MessageBox.INFO
											});
										} else {
											sw.swMsg.show({
												title: 'Ошибка',
												msg: 'Произошла ошибка при удалении',
												buttons: Ext.Msg.OK,
												icon: Ext.MessageBox.WARNING
											});
											return false;
										}
									}.createDelegate(this)
								});
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Вы действительно хотите удалить эту анкету УМО?'),
						title: langs('Предупреждение')
					});
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.MessageBox.WARNING,
						msg: langs('Для начала выберите запись!'),
						title: langs('Внимание')
					});
				}
			}.createDelegate(this)
		}, 9);

		this.SportRegistrySearchFrame.addActions({
			name: 'open_emk',
			text: langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		}, 8);

		var base_form = this.findById('SportRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);

		this.doLayout();

		base_form.findField('SportRegisterType_id').setValue(1);
	},
	emkOpen: function () {
		var grid = this.SportRegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id')) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			//usergetMedPersonalP: this.usergetMedPersonalP,
			//getMedPersonalP_id: this.usergetMedPersonalP.getMedPersonalP_id,
			//LpuSection_id: this.usergetMedPersonalP.LpuSection_id,
			ARMType: 'common',
			callback: function () {

			}.createDelegate(this)
		});
	},
	deletePersonRegister: function () {
		var grid = this.SportRegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id')) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Удалить выбранную запись регистра?'),
			buttons: Ext.Msg.YESNO,
			fn: function (btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Удаление...')).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function (options, success, response) {
							this.getLoadMask().hide();
							if (success) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if (obj.success)
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении записи регистра!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	restoreSportRegister: function (params) {
		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Восстановить выбранного спортсмена?'),
			buttons: Ext.Msg.YESNO,
			fn: function (btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Восстановление...')).show();
					Ext.Ajax.request({
						url: '/?c=SportRegister&m=restoreSportRegister',
						params: {
							SportRegister_id: params.SportRegister_id,
							pmUser_id: parseInt(getGlobalOptions().pmuser_id)
						},
						callback: function (options, success, response) {
							this.getLoadMask().hide();
							if (success) {
								sw.swMsg.alert(langs('Успешно'), langs('Спортсмен восстановлен в регистер!'));
								swSportRegistryWindow.SportRegistrySearchFrame.getGrid().getStore().load();
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при восстановлении!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	}
});