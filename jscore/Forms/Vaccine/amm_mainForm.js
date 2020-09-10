/**
 * amm_mainForm - основная форма-контейнер, содержащая все остальные касающ-ся вакцинации
 *
 * @package      Vaccine
 * @access       public
 * @copyright    Copyright (c) Progress
 * @author       ArslanovAZ
 * @version      16.10.2012
 */

//поиск в журналах по фильтрам
function SearchJournalsVac() {
	var vacSearchForm = Ext.getCmp('vacSearchForm');
	log('vacSearchForm = ', vacSearchForm);
	var centerForm = Ext.getCmp('journalsVaccine');
	vacSearchForm.getForm().findField('SearchFormType').setValue('');//сброс SearchFormType

	var $dateplan = 1;
	if ((Ext.getCmp('journalsVaccine').cardRight.getLayout().activeItem.id == 'amm_VacPlan') &&
			Ext.getCmp('filtrVacPlan_DatePlan').isValid() == false) {
		$dateplan = 0;
	}
	;

	if ((vacSearchForm.getForm().isValid()) && ($dateplan == 1)) {
		var post = vacSearchForm.getForm().getValues();
		if (post.Date_Change == undefined) {
			post.Date_Change = '';
		}
		if(Ext.getCmp('vacJournals_PersonNoAddress')){
			post.PersonNoAddress = (Ext.getCmp('vacJournals_PersonNoAddress').getValue()) ? 1 : 0;
			if (post.PersonNoAddress == 0) {
				if (Ext.getCmp('vacJournals_CountryCombo').getValue() != '')
					post.KLCountry_id = Ext.getCmp('vacJournals_CountryCombo').getValue();
				if (Ext.getCmp('vacJournals_CityCombo').getValue() != '')
					post.KLCity_id = Ext.getCmp('vacJournals_CityCombo').getValue();
				if (Ext.getCmp('vacJournals_RegionCombo').getValue() != '')
					post.KLRgn_id = Ext.getCmp('vacJournals_RegionCombo').getValue();
				if (Ext.getCmp('vacJournals_SubRegionCombo').getValue() != '')
					post.KLSubRgn_id = Ext.getCmp('vacJournals_SubRegionCombo').getValue();
			}
		}

		sw.Promed.vac.utils.consoleLog('getForm().getValues:');

		sw.Promed.vac.utils.consoleLog(post);

		sw.Promed.vac.utils.consoleLog('centerGrid.store.load START...');
		var centerFrame = Ext.getCmp(centerForm.cardRight.getLayout().activeItem.id);
		if (centerFrame.filtrObj == undefined) {
			centerFrame.filtrObj = sw.Promed.vac.utils.getFiltrObj();
			if (centerForm.cardRight.getLayout().activeItem.id == 'amm_VacPlan') {
				post.Date_Plan = new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y');
			}
		}
		centerFrame.filtrObj.setFiltr(post);

		post.ImplVacOnly = (post.checkbox_ImplVacOnly != undefined) ? 'on' : 'off';
		post.limit = 100;
		post.start = 0;

		/*
		 * проверка наличия изменений параметров поиска
		 */
		log('post = ', post);
		if ((centerFrame.isLoaded == undefined) || (centerFrame.filtrObj.getIsChanged() == true)) {
			centerFrame.ViewGridPanel.getStore().reload({
				params: post,
				callback: function () {
					sw.Promed.vac.utils.consoleLog('centerGrid.store.load END - callback');
					centerFrame.filtrObj.resetIsChanged();
					centerFrame.isLoaded = true;
				}
			});
		}

	} else if ($dateplan == 1)
	{
		Ext.MessageBox.show({
			title: "Проверка данных формы",
			msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING},
				function () {
					vacSearchForm.getForm().findField(0).focus()
				}
		);
	} else {
		Ext.getCmp('filtrExtTab').setActiveTab(1);
		Ext.MessageBox.show({
			title: "Проверка данных формы",
			msg: "Поле 'Период планиварования' дополнительного фильтра не может быть пустым!",
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.WARNING},
				function () {

					Ext.getCmp('filtrExtTab').focus()
				}
		)
	}

}

/*
 * класс описания конфигурации основной таблицы
 */
function gridConfiMain() {
	var isLoad = false;
	/*
	 * хранилище для основной таблицы
	 */
	this.store = new Ext.data.JsonStore({
		fields: ['Journal_id', 'Name'],
		url: '/?c=VaccineCtrl&m=loadJournals',
		key: 'Journal_id',
		root: 'data'
	});

	this.columnModel = new Ext.grid.ColumnModel({
		columns: [
			{header: 'Наименование', dataIndex: 'Name', width: 200, sortable: true}
		]});

	this.load = function () {
		if (isLoad)
			return; // уже загружено
		this.store.load();
		isLoad = true;
	};
}

/*
 * Основная форма
 */
sw.Promed.amm_mainForm = Ext.extend(sw.Promed.BaseForm, {
	maximized: true,
	codeRefresh: true,
	objectName: 'amm_mainForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_mainForm.js',
	buttons: null,
	closable: true,
	collapsible: true,
	closeAction: 'hide',
	modal: true,
	onHide: Ext.emptyFn,
	id: 'journalsVaccine',
	layout: 'border',
	onUslugaSelect: Ext.emptyFn,
	Usluga_date: null,
	plain: true,
	resizable: false,
	title: 'Просмотр журналов вакцинации',
	titleBase: 'Просмотр журналов вакцинации',
	journals: [],
	activeJournalKey: 0, //задаем активный таб после загрузки вкладки журналов

	initComponent: function () {

		var form = this;

		this.gridConfiMain = new gridConfiMain();
		form.gridConfiMain.load();

		for (var k = 0; k < 8; k++) {
			var obj = new Object();
			switch (k) {
				case 0:
					obj.type = 'VacMap';
					obj.title = VAC_JOURNAL_VACMAP;
					obj.id = 'amm_VacMap';
					break;
				case 1:
					obj.type = 'VacPlan';
					obj.title = VAC_JOURNAL_VACPLAN;
					obj.id = 'amm_VacPlan';
					break;
				case 2:
					obj.type = 'VacAssigned';
					obj.title = VAC_JOURNAL_VACASSIGNED;
					obj.id = 'amm_VacFixed';
					break;
				case 3:
					obj.type = 'VacRegistr';
					obj.title = VAC_JOURNAL_VACREGISTR;
					obj.id = 'amm_VacAccount';
					break;
				case 4:
					obj.type = 'TubPlan';
					obj.title = VAC_JOURNAL_TUBPLAN;
					obj.id = 'amm_TubPlan';
					break;
				case 5:
					obj.type = 'VacRefuse';
					obj.title = VAC_JOURNAL_VACREFUSE;
					obj.id = 'amm_MedTapRefusal';
					break;
				case 6:
					obj.type = 'TubAssigned';
					obj.title = VAC_JOURNAL_TUBASSIGNED;
					obj.id = 'amm_TubAssigned';
					break;
				case 7:
					obj.type = 'TubReaction';
					obj.title = VAC_JOURNAL_TUBREACTION;
					obj.id = 'amm_TubReaction';
					break;

			}
			form.journals[k] = obj;
		}

		this.grid0 = new Ext.grid.GridPanel({
			id: 'journalsGrid0',
			region: 'west',
			width: 200,
			//autoWidth: true,
			split: true,
			collapsible: true,
			floatable: false,
			store: form.gridConfiMain.store, // определили хранилище
			title: 'Журналы', // Заголовок
			colModel: form.gridConfiMain.columnModel,
			listeners: {
				'resize': function (grid, adjWidth, adjHeight, rawWidth, rawHeight) {
					grid.getColumnModel().setColumnWidth(0, adjWidth - 4);
				},
				'cellclick': function (grid, rowIndex, columnIndex, e) {
					var record = grid.getStore().getAt(rowIndex);  // Get the Record
					//var fieldName = grid.getColumnModel().getDataIndex(columnIndex); // Get field name
					var journalId = record.get('Journal_id');

					Ext.getCmp('cardRight').getLayout().setActiveItem(journalId - 1);
					Ext.getCmp('cardRight').activeJournalKey = journalId - 1;
					Ext.getCmp('journalsVaccine').setTitle(form.titleBase + ': ' + form.journals[journalId - 1].title);
					form.refreshSearchTabs(Ext.getCmp('filtrExtTab'));
					SearchJournalsVac();
				}
			}
		});

		this.ViewFrameVacMap = new sw.Promed.ViewFrame(
				{
					id: 'amm_VacMap',
					dataUrl: '/?c=VaccineCtrl&m=searchVacMap',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					root: 'data',
					paging: true,
					pageSize: 100,
					border: false,
					totalProperty: 'totalCount',
					height: 300,
					autoScroll: true,
					stringfields:
							[
								{name: 'Person_id', type: 'int', header: 'Person_id', key: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 90},
								{name: 'Sex_id', type: 'int', header: 'Sex_id', width: 90, hidden: true},
								{name: 'sex', type: 'string', header: 'Пол', width: 35},
								{name: 'group_risk', type: 'string', header: VAC_TIT_GROUP_RISK, width: 90},
								{name: 'Age', type: 'string', header: 'Age', width: 90, hidden: true},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 130},
								{name: 'Address', type: 'string', header: VAC_TIT_ADDRESS, id: 'autoexpand'},
								{name: 'SocStatus_Name', type: 'string', header: 'SocStatus_Name', width: 90, hidden: true},
								{name: 'Lpu_id', type: 'int', header: 'Lpu_id', width: 90, hidden: true},
								{name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true},
								{name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_OPEN,
									handler: function ()
									{
										// вызываем карту проф. прививок
										var record = this.findById('amm_VacMap').getGrid().getSelectionModel().getSelected();
										record.viewOnly = form.viewOnly;
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'VacMap'
										}, this);//.findById('amm_VacMap'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]

				});


		this.ViewFrameVacMap.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});


		this.ViewFrameVacPlan = new sw.Promed.ViewFrame(
				{
					id: 'amm_VacPlan',
					dataUrl: '/?c=VaccineCtrl&m=searchVacPlan',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					root: 'data',
					paging: true,
					pageSize: 100,
					border: false,
					totalProperty: 'totalCount',
					height: 300,
					autoScroll: true,
					stringfields:
							[
								{name: 'planTmp_id', type: 'int', header: 'ID', key: true},
								{name: 'Date_Plan', type: 'date', header: 'Дата планирования', width: 80},
								{name: 'Person_id', type: 'int', header: 'Person_id', width: 70, hidden: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 80},
								{name: 'Age', type: 'string', header: 'Возраст', width: 50},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
								{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 70, hidden: true},
								{name: 'type_name', type: 'string', header: 'Вид иммунизации', width: 80},
								{name: 'VaccineType_id', type: 'int', header: 'Прививка', width: 70, hidden: true},
//      {name: 'Name', type: 'string', header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
								{name: 'Name', type: 'string', header: VAC_TIT_INFECT_TYPE, width: 150},
								{name: 'SequenceVac', type: 'string', header: 'Очередность прививки', width: 70, hidden: true},
								{name: 'DateSave', type: 'date', header: 'Дата внесения записи', width: 80},
								{name: 'Address', type: 'string', header: VAC_TIT_ADDRESS, id: 'autoexpand'},
								{name: 'date_S', type: 'date', header: 'Дата начала', width: 90, hidden: true},
								{name: 'date_E', type: 'date', header: 'Дата окончания', width: 90, hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'amm_PurposeVacForm':
									Ext.getCmp('amm_VacPlan').ViewGridPanel.getStore().reload();
									Ext.getCmp('amm_VacFixed').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}

					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_PURPOSE_IMPL,
									handler: function ()
									{
										// Назначение прививки
										var record =
												this.findById('amm_VacPlan').getGrid().getSelectionModel().getSelected();
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'VacPlan'
										}, this.findById('amm_VacPlan'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});


		this.ViewFrameVacPlan.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});
		//назначенные прививки (вкладка Назначено)
		this.ViewFrameVacFixed = new sw.Promed.ViewFrame(
				{
					id: 'amm_VacFixed',
					dataUrl: '/?c=VaccineCtrl&m=searchVacAssigned',
					region: 'center',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					paging: true,
					pageSize: 100,
					border: false,
//			autoScroll:true,
					cls: 'txtwrap',
					height: 400,
					root: 'data', //добавлен, т.к. в searchVacAssigned исп-ся root='data'
					totalProperty: 'totalCount',
					stringfields:
							[
								{name: 'JournalVacFixed_id', type: 'int', header: 'ID', key: true},
								{name: 'Date_Purpose', type: 'date', header: 'Дата назначения', width: 70},
								{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 90},
								{name: 'age', type: 'string', header: 'Возраст', width: 50},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
								{name: 'vac_name', type: 'string', header: 'Наименование вакцины', width: 200},
								{name: 'NAME_TYPE_VAC', type: 'string', header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
								{name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 100},
								{name: 'DateSave', type: 'date', header: 'Дата сохранения назначения'},
								{name: 'WAY_PLACE', type: 'string', header: 'Способ и место введения', width: 160, hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							],
					listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'amm_ImplVacForm':
									Ext.getCmp('amm_VacFixed').ViewGridPanel.getStore().reload();
									Ext.getCmp('amm_VacAccount').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}
					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_IMPL,
									handler: function ()
									{
										//  Исполнение прививки
										var record = this.findById('amm_VacFixed').getGrid().getSelectionModel().getSelected()
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'VacAssigned'
										}, this.findById('amm_VacFixed'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});

		this.ViewFrameVacFixed.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});

		//исполненные прививки (вкладка Исполнено)
		this.ViewFrameVacAccount = new sw.Promed.ViewFrame(
				{
					id: 'amm_VacAccount',
					dataUrl: '/?c=VaccineCtrl&m=searchVacRegistr',
					region: 'center',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					paging: true,
					pageSize: 100,
					border: false,
					height: 300,
					root: 'data',
					totalProperty: 'totalCount',
					stringfields:
							[
								{name: 'vacJournalAccount_id', type: 'int', header: 'ID', key: true},
								{name: 'Person_id', type: 'int', header: 'Person_id', width: 70, hidden: true},
								{name: 'Date_Purpose', type: 'date', header: VAC_TIT_DATE_PURPOSE, width: 70},
								{name: 'Date_Vac', type: 'date', header: 'Дата вакцинации', width: 70},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 90},
								{name: 'age', type: 'string', header: 'Возраст', width: 50},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
								{name: 'vac_name', type: 'string', header: 'Наименование вакцины', width: 180},
								{name: 'NAME_TYPE_VAC', type: 'string', header: VAC_TIT_INFECT_TYPE, id: 'autoexpand'},
								{name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 70},
								{name: 'WAY_PLACE', type: 'string', header: 'Способ и место введения', width: 210, hidden: true},
								{name: 'DateSave', type: 'date', header: 'Дата сохранения назначения', hidden: true},
								{name: 'VacDateSave', type: 'date', header: 'Дата сохранения исполнения'}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'amm_ImplVacForm':
									Ext.getCmp('amm_VacAccount').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}

					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_EDIT,
									handler: function ()
									{
										//Редактирование исполненной прививки
										var record = this.findById('amm_VacAccount').getGrid().getSelectionModel().getSelected();
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'VacRegistr'
										}, this.findById('amm_VacAccount'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});


		this.ViewFrameVacAccount.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});

		/****************************************************************************
		 * ...TubPlan - Журнал Планирование туберкулинодиагностики
		 */
		this.ViewFrameTubPlan = new sw.Promed.ViewFrame(
				{
					id: 'amm_TubPlan',
					dataUrl: '/?c=VaccineCtrl&m=searchTubPlan',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					root: 'data',
					paging: true,
					pageSize: 100,
					border: false,
					totalProperty: 'totalCount',
					height: 300,
					autoScroll: true,
					stringfields:
							[
								{name: 'PlanTuberkulin_id', type: 'int', header: 'PlanTuberkulin_id', key: true},
								{name: 'Date_Plan', type: 'date', header: VAC_TIT_TUB_DATE_PLAN},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'sex', type: 'string', header: 'Пол', hidden: true},
								{name: 'BirthDay', type: 'date', header: VAC_TIT_BIRTHDAY},
								{name: 'Age', type: 'string', header: VAC_TIT_AGE, width: 55},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
								{name: 'Address', type: 'string', header: VAC_TIT_ADDRESS, id: 'autoexpand'},
								{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								//{name: 'Lpu_Name', type: 'string', header: VAC_TIT_LPU_NAME, hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'TubPlan':
									Ext.getCmp('amm_TubPlan').ViewGridPanel.getStore().reload();
									Ext.getCmp('amm_TubAssigned').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}

					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_PURPOSE_IMPL,
									handler: function ()
									{
										// назначение манту
										var record = this.findById('amm_TubPlan').getGrid().getSelectionModel().getSelected();
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'TubPlan'
										}, this.findById('amm_TubPlan'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});

		this.ViewFrameTubPlan.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});

		/****************************************************************************
		 * ...TubAssigned - Журнал Манту-назначенные
		 */
		this.ViewFrameTubAssigned = new sw.Promed.ViewFrame(
				{
					id: 'amm_TubAssigned',
					dataUrl: '/?c=VaccineCtrl&m=searchTubAssigned',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					root: 'data',
					paging: true,
					pageSize: 100,
					border: false,
					totalProperty: 'totalCount',
					height: 300,
					autoScroll: true,
					stringfields:
							[
								{name: 'JournalMantuFixed_id', type: 'int', header: 'JournalMantuFixed_id', key: true},
								{name: 'Date_Purpose', type: 'date', header: VAC_TIT_TUB_DATE},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'sex', type: 'string', header: 'Пол', hidden: true},
								{name: 'BirthDay', type: 'date', header: VAC_TIT_BIRTHDAY},
								{name: 'age', type: 'string', header: VAC_TIT_AGE, width: 55},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, id: 'autoexpand'},
								{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								//{name: 'Lpu_Name', type: 'string', header: VAC_TIT_LPU_NAME, hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'TubAssigned':
									Ext.getCmp('amm_TubAssigned').ViewGridPanel.getStore().reload();
									Ext.getCmp('amm_TubReaction').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}

					, actions:
							[
								{
									name: 'action_edit',
									text: VAC_MENU_IMPL,
									handler: function ()
									{
										// назначение манту
										var record = this.findById('amm_TubAssigned').getGrid().getSelectionModel().getSelected();
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'TubAssigned'
										}, this.findById('amm_TubAssigned'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});

		this.ViewFrameTubAssigned.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});

		/****************************************************************************
		 * ...TubReaction - Журнал Манту-реакция
		 */
		this.ViewFrameTubReaction = new sw.Promed.ViewFrame(
				{
					id: 'amm_TubReaction',
					dataUrl: '/?c=VaccineCtrl&m=searchTubReaction',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					root: 'data',
					paging: true,
					pageSize: 100,
					border: false,
					totalProperty: 'totalCount',
					height: 300,
					autoScroll: true,
					stringfields:
							[
								{name: 'Date_Purpose', type: 'date', header: VAC_TIT_TUB_DATE},
								{name: 'JournalMantuFixed_id', type: 'int', header: 'JournalMantuFixed_id', key: true},
								{name: 'date_Vac', type: 'date', header: VAC_TIT_DATE_VAC},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'sex', type: 'string', header: 'Пол', hidden: true},
								{name: 'BirthDay', type: 'date', header: VAC_TIT_BIRTHDAY},
								{name: 'age', type: 'string', header: VAC_TIT_AGE, width: 55},
								{name: 'TubDiagnosisType_Name', type: 'string', header: 'Метод диагностики', width: 120},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, id: 'autoexpand'},
								{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
								//{name: 'Lpu_Name', type: 'string', header: VAC_TIT_LPU_NAME, hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'TubReaction':
									Ext.getCmp('amm_TubReaction').ViewGridPanel.getStore().reload();
									break;
							}
						}
					}

					, actions:
							[
								{name: 'action_edit',
									text: VAC_MENU_EDIT,
									handler: function ()
									{
										//Редактирование исполненной прививки манту
										var record = this.findById('amm_TubReaction').getGrid().getSelectionModel().getSelected();
										sw.Promed.vac.utils.callVacWindow({
											record: record,
											gridType: 'TubReaction'
										}, this.findById('amm_TubReaction'));
									}.createDelegate(this)
								},
								{name: 'action_add', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true}
							]
				});

		this.ViewFrameTubReaction.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				});

		/****************************************************************************
		 * ...MedTapRefusal - Журнал Медотводов
		 */
		this.ViewFrameMedTapRefusal = new sw.Promed.ViewFrame(
				{
					id: 'amm_MedTapRefusal',
					dataUrl: '/?c=VaccineCtrl&m=searchVacRefuse',
					region: 'center',
					toolbar: true,
					setReadOnly: false,
					autoLoadData: false,
					cls: 'txtwrap',
					height: 300,
					paging: true,
					pageSize: 100,
					border: false,
					root: 'data',
					totalProperty: 'totalCount',
					stringfields:
							[
								{
									name: 'vacJournalMedTapRefusal_id',
									type: 'int',
									header: 'ID',
									key: true
								},
								{name: 'SurName', type: 'string', header: 'Фамилия', width: 90},
								{name: 'FirName', type: 'string', header: 'Имя', width: 90},
								{name: 'SecName', type: 'string', header: 'Отчество', width: 90},
								{name: 'BirthDay', type: 'date', header: VAC_TIT_BIRTHDAY},
								{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
								{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
								{
									name: 'DateBegin',
									type: 'date',
									header: 'Начало отвода/отказа',
									width: 90
								},
								{
									name: 'DateEnd',
									type: 'date',
									header: 'Окончание отвода/отказа',
									width: 90
								},
								{
									name: 'VaccineType_Name',
									type: 'string',
									header: VAC_TIT_INFECT_TYPE,
									width: 100
								},
								{
									name: 'Reason',
									type: 'string',
									header: 'Причина отвода/отказа',
									id: 'autoexpand'
//        width: 300
								},
								{
									name: 'type_rec',
									type: 'string',
									header: 'Тип записи',
									width: 200
								},
								{
									name: 'Person_id',
									type: 'int',
									header: 'Person_id',
									hidden: true
								},
								{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
								{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true}
								, {name: 'LpuRegion_id', type: 'int', header: 'LpuRegion_id', hidden: true}
								, {name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
							]

					, listeners: {
						'success': function (source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
//          switch(source){
//						case 'MedTapRefusal':
							Ext.getCmp('amm_MedTapRefusal').ViewGridPanel.getStore().reload();
//              break; 
//          }
						}
					}

					, actions: [
						{
							name: 'action_add', hidden: true

						},
						{
							name: 'action_edit',
							text: VAC_MENU_EDIT,
							handler: function () {
								var record = this.findById('amm_MedTapRefusal').getGrid().getSelectionModel().getSelected();
								var params = {
									'person_id': record.get('Person_id'),
									'refuse_id': record.get('vacJournalMedTapRefusal_id')
								};
								sw.Promed.vac.utils.consoleLog('редактирование отказа:');
								sw.Promed.vac.utils.consoleLog(record.data);
								sw.Promed.vac.utils.consoleLog('- до вызова формы');
								sw.Promed.vac.utils.callVacWindow({
									record: params,
									type1: 'btnForm',
									type2: 'btnFormRefuse'
								}, this.findById('amm_MedTapRefusal'));
							}.createDelegate(this)
						},
						{
							name: 'action_delete',
							handler: function () {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if (buttonId == 'yes') {

											var record = this.findById('amm_MedTapRefusal').getGrid().getSelectionModel().getSelected();
											var params = {
												'refuse_id': record.get('vacJournalMedTapRefusal_id')
											};
											Ext.Ajax.request({
												url: '/?c=VaccineCtrl&m=deletePrivivRefuse',
												method: 'POST',
												params: params,
												success: function (response, opts) {
													sw.Promed.vac.utils.consoleLog(response);
													if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
														Ext.getCmp('amm_MedTapRefusal').fireEvent('success', 'amm_RefuseVacForm');
													}
												}.createDelegate(this),
												failure: function (response, opts) {
													sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
												}
											});

										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: 'Удалить мед. отвод или отказ?',
									title: 'Удаление'
								});
							}.createDelegate(this)
						},
						{name: 'action_view', hidden: true},
						{name: 'action_save', hidden: true}
					]
				});

		this.cardRight = new Ext.Panel({
			id: 'cardRight',
			layout: 'card',
			region: 'center',
			height: 200,
			activeItem: 0, // index or id
			autoScroll: true,
			border: false,
			items: [
				this.ViewFrameVacMap //0
						, this.ViewFrameVacPlan //1-Планирование
						, this.ViewFrameVacFixed //2-Назначено
						, this.ViewFrameVacAccount //3-Исполнено
						, this.ViewFrameTubPlan //4-Планирование туберкулинодиагностики
						, this.ViewFrameMedTapRefusal //5-Отказы и отводы
						, this.ViewFrameTubAssigned //6-Журнал Манту-назначенные
						, this.ViewFrameTubReaction //7-Журнал Манту-реакция
			]
		});

		this.ViewFrameMedTapRefusal.getGrid().view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						if (row.get('Person_dead') == 1)
							cls = cls + 'x-grid-rowgray';
						return cls;
					}
				})

//--------------------------------------------------------------------------
// Доп фильтры для поиска в журналах
//--------------------------------------------------------------------------
		var $dopTitle = "<u>3</u>. Доп фильтр";
		this.filtrVacMap = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
//      height : 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Change",
					id: "Date_Change",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: 'Период внесения изменений',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 10
				}, {
					name: 'ImplVacOnly',
					value: 'off',
					xtype: 'hidden'
				}, {
					xtype: 'checkbox',
//				hideLabel: true,
//				height:24,
					name: 'checkbox_ImplVacOnly',
//				id: 'checkbox_ImplVacOnly',
					labelSeparator: '',
					checked: false,
					boxLabel: 'Учитывать только исполненные прививки'
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrTubPlan = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Plan",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_PLAN,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrTubAssigned = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Purpose",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_PURPOSE_PERIOD,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrTubReaction = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "date_Vac",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_VAC_PERIOD,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrVacPlan = {
			title: $dopTitle,
			id: 'filtrVacPlan',
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Plan",
					id: 'filtrVacPlan_DatePlan',
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_PLAN_PERIOD,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41,
					value: new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y'),
					allowBlank: false
				}, {
					name: "Name",
					xtype: "textfield",
					width: 520,
					fieldLabel: VAC_TIT_INFECT_TYPE,
					tabIndex: TABINDEX_VACMAINFRM + 42
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrVacAssigned = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Purpose",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_PURPOSE_PERIOD,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41
				}, {
					name: "vac_name",
					xtype: "textfield",
					width: 520,
					fieldLabel: VAC_TIT_VAC_NAME,
					tabIndex: TABINDEX_VACMAINFRM + 42
				}, {
					name: "NAME_TYPE_VAC",
					xtype: "textfield",
					width: 520,
					fieldLabel: VAC_TIT_NAME_TYPE_VAC,
					tabIndex: TABINDEX_VACMAINFRM + 43
				}],
			bodyBorder: true,
			layout: "form"
		};

		this.filtrVacRegistr = {
			title: $dopTitle,
			cls: 'extFilter',
			labelWidth: 150,
			frame: false,
			border: false,
			height: 220,
			items: [{
					height: 5,
					border: false
				}, {
					name: "Date_Vac",
					xtype: "daterangefield",
					width: 170,
					fieldLabel: VAC_TIT_DATE_VAC_PERIOD,
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: TABINDEX_VACMAINFRM + 41
				}, {
					name: "vac_name",
					xtype: "textfield",
					width: 520,
					fieldLabel: VAC_TIT_VAC_NAME,
					tabIndex: TABINDEX_VACMAINFRM + 42
				}, {
					name: "NAME_TYPE_VAC",
					xtype: "textfield",
					width: 520,
					fieldLabel: VAC_TIT_NAME_TYPE_VAC,
					tabIndex: TABINDEX_VACMAINFRM + 43
				}],
			bodyBorder: true,
			layout: "form"
					//,autoHeight : true
		};
		//--------------------------------------------------------------------------

		this.refreshSearchTabs = function (obj) {
			log('obj = ', obj.items);
			obj.remove(2);
			var centerForm = Ext.getCmp('journalsVaccine');
			switch (centerForm.cardRight.getLayout().activeItem.id) {
				case 'amm_VacMap':  //Список карт проф. прививок';
					obj.add(form.filtrVacMap);
					break;
				case 'amm_VacPlan': //'План прививок';

					obj.add(form.filtrVacPlan);
					break;
				case 'amm_VacFixed':
					obj.add(form.filtrVacAssigned);
					break;
				case 'amm_VacAccount':
					obj.add(form.filtrVacRegistr);
					break;
				case 'amm_TubPlan':
					obj.add(form.filtrTubPlan);
					break;
				case 'amm_TubAssigned':
					obj.add(form.filtrTubAssigned);
					break;
				case 'amm_TubReaction':
					obj.add(form.filtrTubReaction);
					break;
				case 'amm_MedTapRefusal':
					break;
				default:
					break;
			}
			$page = $title.match(/[0-9]/) - 1;
			obj.setActiveTab($page)
			obj.doLayout();
			Ext.getCmp('vacSearchForm').getForm().findField('SurName').focus();
		};

//==========================================================================
// Панель фильтров, параметров
//==========================================================================


		this.SearchFiltersAdr = {
			autoHeight: true,
			labelWidth: 120,
			layout: 'form',
			style: 'padding: 2px',
			title: '<u>2</u>. Адрес',
			cls: 'extFilter',
			items: [
				{
					border: false,
					layout: 'form',
					items: [{
							boxLabel: langs('Без адреса'),
							id: 'vacJournals_PersonNoAddress',
							labelSeparator: '',
							listeners: {
								'check': function (checkbox, checked) {
									if (checked) {
										Ext.getCmp('vacJournals_KLAreaStatCombo').disable();
										Ext.getCmp('vacJournals_CountryCombo').disable();
										Ext.getCmp('vacJournals_RegionCombo').disable();
										Ext.getCmp('vacJournals_SubRegionCombo').disable();
										Ext.getCmp('vacJournals_CityCombo').disable();
										Ext.getCmp('vacJournals_TownCombo').disable();
										Ext.getCmp('vacJournals_StreetCombo').disable();
										Ext.getCmp('vacJournals_Address_House').disable();
									} else {
										Ext.getCmp('vacJournals_KLAreaStatCombo').enable();
										Ext.getCmp('vacJournals_CountryCombo').enable();
										Ext.getCmp('vacJournals_RegionCombo').enable();
										Ext.getCmp('vacJournals_SubRegionCombo').enable();
										Ext.getCmp('vacJournals_CityCombo').enable();
										Ext.getCmp('vacJournals_TownCombo').enable();
										Ext.getCmp('vacJournals_StreetCombo').enable();
										Ext.getCmp('vacJournals_Address_House').enable();
										var combo = Ext.getCmp('vacJournals_KLAreaStatCombo');
										combo.fireEvent('change', combo, combo.getValue(), null);
									}
								}.createDelegate(this)
							},
							name: 'Person_NoAddress',
							tabIndex: TABINDEX_VACMAINFRM + 21,
							width: 150,
							xtype: 'checkbox'
						}]
				},
				{border: false,
					layout: 'form',
					items: [
						{
							allowBlank: true,
							ignoreIsEmpty: true,
							codeField: 'AddressStateType_Code',
							displayField: 'AddressStateType_Name',
							editable: false,
							disabled: true,
							fieldLabel: langs('Тип адреса'),
							hiddenName: 'AddressStateType_id',
							ignoreIsEmpty: true,
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[1, 1, langs('Адрес регистрации')],
									[2, 2, langs('Адрес проживания')]
								],
								fields: [
									{name: 'AddressStateType_id', type: 'int'},
									{name: 'AddressStateType_Code', type: 'int'},
									{name: 'AddressStateType_Name', type: 'string'},
									{name: 'ReceptFinance_id', type: 'int'}
								],
								key: 'AddressStateType_id',
								sortInfo: {field: 'AddressStateType_Code'}
							}),
							tabIndex: TABINDEX_VACMAINFRM + 22,
							tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{AddressStateType_Code}</font>&nbsp;{AddressStateType_Name}',
									'</div></tpl>'
									),
							value: 2,
							valueField: 'AddressStateType_id',
							width: 325,
							xtype: 'swbaselocalcombo'
						}
					]},
				{
					//==колонки:===========================================	
					border: false,
					layout: 'column',
					style: 'padding: 2px',
					labelWidth: 120,
					width: 1050,
					defaults: {
						bodyBorder: false,
						anchor: '100%'
					},
					items: [//столбец 1
						{border: false,
							layout: 'form',
							defaults: {
								width: 325
							},
							items: [
								{
									codeField: 'KLAreaStat_Code',
									displayField: 'KLArea_Name',
									editable: true,
									enableKeyEvents: true,
									fieldLabel: langs('Территория'),
									hiddenName: 'KLAreaStat_id',
									id: 'vacJournals_KLAreaStatCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											log('change: newValue = ', newValue, 'oldValue = ', oldValue);
											var current_window = Ext.getCmp('journalsVaccine');
											var index = combo.getStore().findBy(function (rec) {
												return rec.get('KLAreaStat_id') == newValue;
											});

											current_window.findById('vacJournals_CountryCombo').enable();
											current_window.findById('vacJournals_RegionCombo').enable();
											current_window.findById('vacJournals_SubRegionCombo').enable();
											current_window.findById('vacJournals_CityCombo').enable();
											current_window.findById('vacJournals_TownCombo').enable();
											current_window.findById('vacJournals_StreetCombo').enable();

											if (index == -1)
											{
												return false;
											}

											var current_record = combo.getStore().getAt(index);

											var country_id = current_record.data.KLCountry_id;
											var region_id = current_record.data.KLRGN_id;
											var subregion_id = current_record.data.KLSubRGN_id;
											var city_id = current_record.data.KLCity_id;
											var town_id = current_record.data.KLTown_id;
											var klarea_pid = 0;
											var level = 0;

											clearAddressCombo(
													current_window.findById('vacJournals_CountryCombo').areaLevel,
													{'Country': current_window.findById('vacJournals_CountryCombo'),
														'Region': current_window.findById('vacJournals_RegionCombo'),
														'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
														'City': current_window.findById('vacJournals_CityCombo'),
														'Town': current_window.findById('vacJournals_TownCombo'),
														'Street': current_window.findById('vacJournals_StreetCombo')
													}
											);

											if (country_id != null)
											{
												current_window.findById('vacJournals_CountryCombo').setValue(country_id);
												current_window.findById('vacJournals_CountryCombo').disable();
											} else
											{
												return false;
											}

											current_window.findById('vacJournals_RegionCombo').getStore().load({
												callback: function () {
													current_window.findById('vacJournals_RegionCombo').setValue(region_id);
												},
												params: {
													country_id: country_id,
													level: 1,
													value: 0
												}
											});

											if (region_id.toString().length > 0)
											{
												klarea_pid = region_id;
												level = 1;
											}

											current_window.findById('vacJournals_SubRegionCombo').getStore().load({
												callback: function () {
													current_window.findById('vacJournals_SubRegionCombo').setValue(subregion_id);
												},
												params: {
													country_id: 0,
													level: 2,
													value: klarea_pid
												}
											});

											if (subregion_id.toString().length > 0)
											{
												klarea_pid = subregion_id;
												level = 2;
											}

											current_window.findById('vacJournals_CityCombo').getStore().load({
												callback: function () {
													current_window.findById('vacJournals_CityCombo').setValue(city_id);
												},
												params: {
													country_id: 0,
													level: 3,
													value: klarea_pid
												}
											});

											if (city_id.toString().length > 0)
											{
												klarea_pid = city_id;
												level = 3;
											}

											current_window.findById('vacJournals_TownCombo').getStore().load({
												callback: function () {
													current_window.findById('vacJournals_TownCombo').setValue(town_id);
												},
												params: {
													country_id: 0,
													level: 4,
													value: klarea_pid
												}
											});

											if (town_id.toString().length > 0)
											{
												klarea_pid = town_id;
												level = 4;
											}

											current_window.findById('vacJournals_StreetCombo').getStore().load({
												params: {
													country_id: 0,
													level: 5,
													value: klarea_pid
												}
											});

											switch (level)
											{
												case 1:
													current_window.findById('vacJournals_RegionCombo').disable();
													break;

												case 2:
													current_window.findById('vacJournals_RegionCombo').disable();
													current_window.findById('vacJournals_SubRegionCombo').disable();
													break;

												case 3:
													current_window.findById('vacJournals_RegionCombo').disable();
													current_window.findById('vacJournals_SubRegionCombo').disable();
													current_window.findById('vacJournals_CityCombo').disable();
													break;

												case 4:
													current_window.findById('vacJournals_RegionCombo').disable();
													current_window.findById('vacJournals_SubRegionCombo').disable();
													current_window.findById('vacJournals_CityCombo').disable();
													current_window.findById('vacJournals_TownCombo').disable();
													break;
											}
										}
									},
									store: new Ext.db.AdapterStore({
										autoLoad: true,
										dbFile: 'Promed.db',
										fields: [
											{name: 'KLAreaStat_id', type: 'int'},
											{name: 'KLAreaStat_Code', type: 'int'},
											{name: 'KLArea_Name', type: 'string'},
											{name: 'KLCountry_id', type: 'int'},
											{name: 'KLRGN_id', type: 'int'},
											{name: 'KLSubRGN_id', type: 'int'},
											{name: 'KLCity_id', type: 'int'},
											{name: 'KLTown_id', type: 'int'}
										],
										key: 'KLAreaStat_id',
										sortInfo: {
											field: 'KLAreaStat_Code',
											direction: 'ASC'
										},
										tableName: 'KLAreaStat'
									}),
									tabIndex: TABINDEX_VACMAINFRM + 23,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}' +
											'</div></tpl>',
									valueField: 'KLAreaStat_id',
									//width: 620,
									xtype: 'swbaselocalcombo'
								},
								{
									areaLevel: 0,
									codeField: 'KLCountry_Code',
									displayField: 'KLCountry_Name',
									editable: true,
									fieldLabel: langs('Страна'),
									hiddenName: 'KLCountry_id',
									id: 'vacJournals_CountryCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var current_window = Ext.getCmp('journalsVaccine');
											if (newValue != null && combo.getRawValue().toString().length > 0)
											{
												loadAddressCombo(
														combo.areaLevel,
														{'Country': current_window.findById('vacJournals_CountryCombo'),
															'Region': current_window.findById('vacJournals_RegionCombo'),
															'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
															'City': current_window.findById('vacJournals_CityCombo'),
															'Town': current_window.findById('vacJournals_TownCombo'),
															'Street': current_window.findById('vacJournals_StreetCombo')
														},
														0,
														combo.getValue(),
														true
														);
											} else
											{
												clearAddressCombo(
														combo.areaLevel,
														{'Country': current_window.findById('vacJournals_CountryCombo'),
															'Region': current_window.findById('vacJournals_RegionCombo'),
															'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
															'City': current_window.findById('vacJournals_CityCombo'),
															'Town': current_window.findById('vacJournals_TownCombo'),
															'Street': current_window.findById('vacJournals_StreetCombo')
														}
												);
											}
										},
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE)
											{
												if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
												{
													combo.fireEvent('change', combo, null, combo.getValue());
												}
											}
										},
										'select': function (combo, record, index) {
											if (record.data.KLCountry_id == combo.getValue())
											{
												combo.collapse();
												return false;
											}
											combo.fireEvent('change', combo, record.data.KLArea_id, null);
										}
									},
									store: new Ext.db.AdapterStore({
										autoLoad: true,
										dbFile: 'Promed.db',
										fields: [
											{name: 'KLCountry_id', type: 'int'},
											{name: 'KLCountry_Code', type: 'int'},
											{name: 'KLCountry_Name', type: 'string'}
										],
										key: 'KLCountry_id',
										sortInfo: {
											field: 'KLCountry_Name'
										},
										tableName: 'KLCountry'
									}),
									tabIndex: TABINDEX_VACMAINFRM + 24,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}' +
											'</div></tpl>',
									valueField: 'KLCountry_id',
									//width: 620,
									xtype: 'swbaselocalcombo'
								}, {
									areaLevel: 1,
									displayField: 'KLArea_Name',
									enableKeyEvents: true,
									fieldLabel: langs('Регион'),
									hiddenName: 'KLRgn_id',
									id: 'vacJournals_RegionCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var current_window = Ext.getCmp('journalsVaccine');
											if (newValue != null && combo.getRawValue().toString().length > 0)
											{
												loadAddressCombo(
														combo.areaLevel,
														{'Country': current_window.findById('vacJournals_CountryCombo'),
															'Region': current_window.findById('vacJournals_RegionCombo'),
															'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
															'City': current_window.findById('vacJournals_CityCombo'),
															'Town': current_window.findById('vacJournals_TownCombo'),
															'Street': current_window.findById('vacJournals_StreetCombo')
														},
														0,
														combo.getValue(),
														true
														);
											} else
											{
												clearAddressCombo(
														combo.areaLevel,
														{'Country': swReceptInCorrectSearchWindow.findById('vacJournals_CountryCombo'),
															'Region': swReceptInCorrectSearchWindow.findById('vacJournals_RegionCombo'),
															'SubRegion': swReceptInCorrectSearchWindow.findById('vacJournals_SubRegionCombo'),
															'City': swReceptInCorrectSearchWindow.findById('vacJournals_CityCombo'),
															'Town': swReceptInCorrectSearchWindow.findById('vacJournals_TownCombo'),
															'Street': swReceptInCorrectSearchWindow.findById('vacJournals_StreetCombo')
														}
												);
											}
										},
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
											{
												combo.fireEvent('change', combo, null, combo.getValue());
											}
										},
										'select': function (combo, record, index) {
											if (record.data.KLArea_id == combo.getValue())
											{
												combo.collapse();
												return false;
											}
											combo.fireEvent('change', combo, record.data.KLArea_id);
										}
									},
									minChars: 0,
									mode: 'local',
									queryDelay: 250,
									//width: 620,
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'KLArea_id', type: 'int'},
											{name: 'KLArea_Name', type: 'string'}
										],
										key: 'KLArea_id',
										sortInfo: {
											field: 'KLArea_Name'
										},
										url: C_LOAD_ADDRCOMBO
									}),
									tabIndex: TABINDEX_VACMAINFRM + 25,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
									triggerAction: 'all',
									valueField: 'KLArea_id',
									xtype: 'combo'
								}, {
									areaLevel: 2,
									displayField: 'KLArea_Name',
									enableKeyEvents: true,
									fieldLabel: langs('Район'),
									hiddenName: 'KLSubRgn_id',
									id: 'vacJournals_SubRegionCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											if (newValue != null && combo.getRawValue().toString().length > 0)
											{
												loadAddressCombo(
														combo.areaLevel,
														{'Country': swReceptInCorrectSearchWindow.findById('vacJournals_CountryCombo'),
															'Region': swReceptInCorrectSearchWindow.findById('vacJournals_RegionCombo'),
															'SubRegion': swReceptInCorrectSearchWindow.findById('vacJournals_SubRegionCombo'),
															'City': swReceptInCorrectSearchWindow.findById('vacJournals_CityCombo'),
															'Town': swReceptInCorrectSearchWindow.findById('vacJournals_TownCombo'),
															'Street': swReceptInCorrectSearchWindow.findById('vacJournals_StreetCombo')
														},
														0,
														combo.getValue(),
														true
														);
											} else
											{
												clearAddressCombo(
														combo.areaLevel,
														{'Country': swReceptInCorrectSearchWindow.findById('vacJournals_CountryCombo'),
															'Region': swReceptInCorrectSearchWindow.findById('vacJournals_RegionCombo'),
															'SubRegion': swReceptInCorrectSearchWindow.findById('vacJournals_SubRegionCombo'),
															'City': swReceptInCorrectSearchWindow.findById('vacJournals_CityCombo'),
															'Town': swReceptInCorrectSearchWindow.findById('vacJournals_TownCombo'),
															'Street': swReceptInCorrectSearchWindow.findById('vacJournals_StreetCombo')
														}
												);
											}
										},
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
											{
												combo.fireEvent('change', combo, null, combo.getValue());
											}
										},
										'select': function (combo, record, index) {
											if (record.data.KLArea_id == combo.getValue())
											{
												combo.collapse();
												return false;
											}
											combo.fireEvent('change', combo, record.data.KLArea_id);
										}
									},
									minChars: 0,
									mode: 'local',
									queryDelay: 250,
									//width: 620,
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'KLArea_id', type: 'int'},
											{name: 'KLArea_Name', type: 'string'}
										],
										key: 'KLArea_id',
										sortInfo: {
											field: 'KLArea_Name'
										},
										url: C_LOAD_ADDRCOMBO
									}),
									tabIndex: TABINDEX_VACMAINFRM + 26,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
									triggerAction: 'all',
									valueField: 'KLArea_id',
									xtype: 'combo'
								}
							]},
						{//столбец 2
							layout: 'form',
							//columnWidth: 0.65,
							defaults: {
								width: 325
							},
							items: [{
									areaLevel: 3,
									displayField: 'KLArea_Name',
									enableKeyEvents: true,
									fieldLabel: langs('Город'),
									hiddenName: 'KLCity_id',
									id: 'vacJournals_CityCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var current_window = Ext.getCmp('journalsVaccine');
											if (newValue != null && combo.getRawValue().toString().length > 0)
											{
												loadAddressCombo(
														combo.areaLevel,
														{'Country': current_window.findById('vacJournals_CountryCombo'),
															'Region': current_window.findById('vacJournals_RegionCombo'),
															'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
															'City': current_window.findById('vacJournals_CityCombo'),
															'Town': current_window.findById('vacJournals_TownCombo'),
															'Street': current_window.findById('vacJournals_StreetCombo')
														},
														0,
														combo.getValue(),
														true
														);
											}
										},
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
											{
												combo.fireEvent('change', combo, null, combo.getValue());
											}
										},
										'select': function (combo, record, index) {
											if (record.data.KLArea_id == combo.getValue())
											{
												combo.collapse();
												return false;
											}
											combo.fireEvent('change', combo, record.data.KLArea_id);
										}
									},
									minChars: 0,
									mode: 'local',
									queryDelay: 250,
									//width: 620,
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'KLArea_id', type: 'int'},
											{name: 'KLArea_Name', type: 'string'}
										],
										key: 'KLArea_id',
										sortInfo: {
											field: 'KLArea_Name'
										},
										url: C_LOAD_ADDRCOMBO
									}),
									tabIndex: TABINDEX_VACMAINFRM + 27,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
									triggerAction: 'all',
									valueField: 'KLArea_id',
									xtype: 'combo'
								}, {
									areaLevel: 4,
									displayField: 'KLArea_Name',
									enableKeyEvents: true,
									fieldLabel: langs('Нас. пункт'),
									hiddenName: 'KLTown_id',
									id: 'vacJournals_TownCombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var current_window = Ext.getCmp('journalsVaccine');
											if (newValue != null && combo.getRawValue().toString().length > 0)
											{
												loadAddressCombo(
														combo.areaLevel,
														{'Country': current_window.findById('vacJournals_CountryCombo'),
															'Region': current_window.findById('vacJournals_RegionCombo'),
															'SubRegion': current_window.findById('vacJournals_SubRegionCombo'),
															'City': current_window.findById('vacJournals_CityCombo'),
															'Town': current_window.findById('vacJournals_TownCombo'),
															'Street': current_window.findById('vacJournals_StreetCombo')
														},
														0,
														combo.getValue(),
														true
														);
											}
										},
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
											{
												combo.fireEvent('change', combo, null, combo.getValue());
											}
										},
										'select': function (combo, record, index) {
											if (record.data.KLArea_id == combo.getValue())
											{
												combo.collapse();
												return false;
											}
											combo.fireEvent('change', combo, record.data.KLArea_id);
										}
									},
									minChars: 0,
									mode: 'local',
									queryDelay: 250,
									//width: 620,
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'KLArea_id', type: 'int'},
											{name: 'KLArea_Name', type: 'string'}
										],
										key: 'KLArea_id',
										sortInfo: {
											field: 'KLArea_Name'
										},
										url: C_LOAD_ADDRCOMBO
									}),
									tabIndex: TABINDEX_VACMAINFRM + 28,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLArea_Name}' +
											'</div></tpl>',
									triggerAction: 'all',
									valueField: 'KLArea_id',
									xtype: 'combo'
								}, {
									displayField: 'KLStreet_Name',
									enableKeyEvents: true,
									fieldLabel: langs('Улица'),
									hiddenName: 'KLStreet_id',
									id: 'vacJournals_StreetCombo',
									listeners: {
										'keydown': function (combo, e) {
											if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
											{
												combo.clearValue();
											}
										}
									},
									minChars: 0,
									mode: 'local',
									queryDelay: 250,
									//width: 620,
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'KLStreet_id', type: 'int'},
											{name: 'KLStreet_Name', type: 'string'}
										],
										key: 'KLStreet_id',
										sortInfo: {
											field: 'KLStreet_Name'
										},
										url: C_LOAD_ADDRCOMBO
									}),
									tabIndex: TABINDEX_VACMAINFRM + 29,
									tpl: '<tpl for="."><div class="x-combo-list-item">' +
											'{KLStreet_Name}' +
											'</div></tpl>',
									triggerAction: 'all',
									valueField: 'KLStreet_id',
									xtype: 'combo'
								}, {
									border: false,
									layout: 'column',
									items: [{
											border: false,
											width: 300,
											layout: 'form',
											items: [{
													fieldLabel: langs('Дом'),
													id: 'vacJournals_Address_House',
													listeners: {
														'keydown': function (inp, e) {
															if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
																if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() > 0) {
																	e.stopEvent();
																	TabToGrid(EvnReceptIncorrectSearchViewGrid);
																}
															}
														}
													},
													name: 'Address_House',
													tabIndex: TABINDEX_VACMAINFRM + 30,
													width: 156,
													xtype: 'textfield'
												}]
										}]
								}]
						}]
				}
			]


		}
		this.journalsSearchFilterForm = new Ext.form.FormPanel({
			id: "vacSearchForm",
			labelWidth: 150,
			frame: false,
			border: false,
			bodyStyle: 'border-bottom-width: 1px;',
			region: 'north',
			layout: 'form',
			height: 26 * 6 + 38,
//			height: 400,
			items: [{
					id: 'filtrExtTab',
					items: [{
							title: "<u>1</u>. Основной фильтр",
							frame: false,
							border: false,
							height: 220,
							//autoScroll: true,
							defaults: {
								width: 520
							},
							items: [{
									height: 5,
									border: false
								}, {
									//==колонки:===========================================	
									border: false,
									layout: 'column',
									width: 1050,
									defaults: {
										bodyBorder: false,
										anchor: '100%'
									},
									items: [{//столбец 1
											layout: 'form',
											columnWidth: 0.35,
											defaults: {
												width: 200
											},
											items: [{
													id: "Journals_Person_Surname",
													listeners: {
														'keydown': function (inp, e) {
															if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
															{
																e.stopEvent();
															}
														}
													},
													name: "SurName",
													xtype: "textfieldpmw",
													fieldLabel: "Фамилия",
													tabIndex: TABINDEX_VACMAINFRM + 1
												}, {
													name: "FirName",
													xtype: "textfieldpmw",
													fieldLabel: "Имя",
													tabIndex: TABINDEX_VACMAINFRM + 2
												}, {
													name: "SecName",
													xtype: "textfieldpmw",
													fieldLabel: "Отчество",
													tabIndex: TABINDEX_VACMAINFRM + 3
												}, 
												{
														codeField: 'Sex_Code',
														editable: false,
														fieldLabel: langs('Пол'),
														xtype: 'swpersonsexcombo',
														hiddenName: 'PersonSex_id',
														tabIndex : TABINDEX_VACMAINFRM + 4
												},
												{
													name: "BirthDayRange",
													xtype: "daterangefield",
													//width : 170,
													fieldLabel: 'Дата рождения',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													tabIndex: TABINDEX_VACMAINFRM + 5
												},
												{
													border: false,
													layout: 'column',
													width: 500,
													items: [
														{
															layout: 'form',
															border: false,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('Возраст с'),
																	name: 'PersonAge_AgeFrom',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "9",
																		maxLength: "3",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_VACMAINFRM + 6
																}]
														},
														{
															layout: 'form',
															border: false,
															labelWidth: 25,
															items: [{
																	xtype: 'numberfield',
																	fieldLabel: langs('по'),
																	name: 'PersonAge_AgeTo',
																	allowNegative: false,
																	allowDecimals: false,
																	autoCreate: {
																		tag: "input",
																		type: "text",
																		size: "10",
																		maxLength: "3",
																		autocomplete: "off"
																	},
																	tabIndex: TABINDEX_VACMAINFRM + 7
																}]
														}
													]
												}
											]
										},
										{//столбец 2
											layout: 'form',
											columnWidth: 0.65,
											defaults: {
												width: 450
											},
											items: [{
													autoLoad: true,
													fieldLabel: 'МО вакцинации',
													hiddenName: 'Lpu_id',
													xtype: 'amm_LpuListCombo',
													disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(),
													tabIndex: TABINDEX_VACMAINFRM + 11
												},
												{
													autoLoad: true,
													fieldLabel: 'МО прикрепления',
													hiddenName: 'Lpu_atid',
													xtype: 'amm_LpuListCombo',
													tabIndex: TABINDEX_VACMAINFRM + 12,
													listeners: {
														'select': function (combo, record, index) {
															//								combo.fireEvent('change', combo, record.get(combo.valueField));
															//								this.journalsSearchFilterForm.getForm().findField('uch_id').setValue(-1);
															this.journalsSearchFilterForm.getForm().findField('uch_id').getStore().load({
																params: {
																	//										lpu_id: getGlobalOptions().lpu_id
																	lpu_id: combo.getValue()
																},
																callback: function () {
																	this.journalsSearchFilterForm.getForm().findField('uch_id').setValue(-1);
																}.createDelegate(this)
															});

															this.journalsSearchFilterForm.getForm().findField('Org_id').getStore().load({
																params: {
																	lpu_id: combo.getValue()
																}
																, callback: function () {
																	this.journalsSearchFilterForm.getForm().findField('Org_id').setValue(0);
																}.createDelegate(this)
															});
														}.createDelegate(this)
													}
												},
												{
													autoLoad: true,
													hiddenName: "uch_id",
													xtype: "amm_uchListCombo",
													fieldLabel: "Участок",
													tabIndex: TABINDEX_VACMAINFRM + 13
												}, {
													xtype: "amm_AttachMethodCombo",
													hiddenName: 'AttachMethod_id',
													tabIndex: TABINDEX_VACMAINFRM + 14,
													fieldLabel: 'Прикрепление',
													listeners: {
														'select': function (combo, record, index) {
															var curForm = combo.findForm().getForm();
															if (combo.getValue() == 1) {
																curForm.findField('Org_id').showContainer();
																curForm.findField('OrgType_id').hideContainer();
																curForm.findField('uch_id').setValue(-1);
																curForm.findField('uch_id').setDisabled(1);
															} else {
																curForm.findField('Org_id').hideContainer();
																curForm.findField('OrgType_id').showContainer();
																curForm.findField('uch_id').setDisabled(0);
															}

														}.createDelegate(this)
													}
												}, {
													xtype: "amm_OrgUnOrgPopulationCombo",
													hiddenName: 'OrgType_id',
													//fieldLabel: "Выборка",
													tabIndex: TABINDEX_VACMAINFRM + 15
												}, {
													id: 'Vac_OrgJob2LpuCombo',
													editable: false,
													tabIndex: TABINDEX_VACMAINFRM + 16,
													fieldLabel: "Организация",
													hiddenName: 'Org_id',
													xtype: 'amm_VacOrgJob2LpuCombo'

												}]
										}]
								}, {
									//==:колонки===========================================	

									name: 'SearchFormType',
									value: '',
									xtype: 'hidden'
								}, {
									name: 'limit',
									value: '1000',
									xtype: 'hidden'


								}],
							bodyBorder: true,
							layout: "form",
							autoHeight: true
						}
					],
					listeners: {
						'tabchange': function (tab, panel) {
							var els = panel.findByType('textfield', false);
							if (els == 'undefined')
								els = panel.findByType('combo', false);
							var el = els[0];
							if (el != 'undefined' && el.focus)
								el.focus(true, 200);
						}
					},
					xtype: "tabpanel",
					activeTab: 0,
					border: false,
					layoutOnTabChange: true
				}
			],
			labelAlign: "right",
			keys: [{
					key: 13,
					fn: function () {
						sw.Promed.vac.utils.consoleLog('key 13');
						SearchJournalsVac();
					},
					stopEvent: true
				}]
		});

		
		/*
		 * Кнопки в нижней части формы
		 */
		this.journalsButtons = {
			id: "Journals_BottomButtons",
			region: "south",
			height: 40,
			buttons: [{
					text: BTN_FRMSEARCH,
					iconCls: 'search16',
					handler: function () {
						SearchJournalsVac();
					}.createDelegate(this),
					onTabAction: function () {
						Ext.getCmp('Journals_BottomButtons').buttons[1].focus(false, 0);
					},
					onShiftTabAction: function () {
						if (EvnReceptIncorrectSearchViewGrid.getStore().getCount() == 0) {
							Ext.getCmp('EvnReceptInCorrectSearchWindow').getLastFieldOnCurrentTab().focus(true);
							return;
						}
						var selected_record = EvnReceptIncorrectSearchViewGrid.getSelectionModel().getSelected();
						var index = 0;
						if (selected_record != -1) {
							index = EvnReceptIncorrectSearchViewGrid.getStore().indexOf(selected_record);
						} else {
							index = 0;
						}
						EvnReceptIncorrectSearchViewGrid.getView().focusRow(index);
						EvnReceptIncorrectSearchViewGrid.getSelectionModel().selectRow(index);
					},
					tabIndex: TABINDEX_VACMAINFRM + 51
				}, {
					text: BTN_FRMRESET,
					iconCls: 'resetsearch16',
					handler: function (button, event) {
						form.journalsSearchFilterForm.getForm().reset();
						//TODO: выделить в отдельную процу (нач-я установка):
						var comboLpuID = this.journalsSearchFilterForm.getForm().findField('Lpu_id');
						var comboLpuATID = this.journalsSearchFilterForm.getForm().findField('Lpu_atid');
						if((getRegionNick() == 'perm')){
							comboLpuID.setValue();
							comboLpuID.disable();
							var rec = comboLpuATID.findRecord('Lpu_Name', "Пермь ГКП 2");
							if(rec && rec.get('Lpu_id')){
								comboLpuATID.setValue(rec.get('Lpu_id'));
							}else{
								comboLpuATID.setValue();
							}
						}else{
							comboLpuID.setValue(getGlobalOptions().lpu_id);
							comboLpuATID.setValue(getGlobalOptions().lpu_id);
						}
						// form.journalsSearchFilterForm.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
						// form.journalsSearchFilterForm.getForm().findField('Lpu_atid').setValue(getGlobalOptions().lpu_id);
						form.journalsSearchFilterForm.getForm().findField('uch_id').setValue(-1);
						//не вып-ся reset для динамических вкладок => поставили костыль:
						form.refreshSearchTabs(Ext.getCmp('filtrExtTab')); 
						if(Ext.getCmp('vacJournals_PersonNoAddress')){
							Ext.getCmp('vacJournals_PersonNoAddress').reset();
							Ext.getCmp('vacJournals_KLAreaStatCombo').reset();
							Ext.getCmp('vacJournals_CountryCombo').reset();
							Ext.getCmp('vacJournals_RegionCombo').reset();
							Ext.getCmp('vacJournals_SubRegionCombo').reset();
							Ext.getCmp('vacJournals_CityCombo').reset();
							Ext.getCmp('vacJournals_TownCombo').reset();
							Ext.getCmp('vacJournals_StreetCombo').reset();
							Ext.getCmp('vacJournals_Address_House').reset();
							combo = Ext.getCmp('vacJournals_KLAreaStatCombo');
							combo.fireEvent('change', combo, combo.getValue(), null);
						}
										
						SearchJournalsVac();
					}.createDelegate(this),
					tabIndex: TABINDEX_VACMAINFRM + 52
				}, {
					handler: function () {
//					var grid = Ext.getCmp('journalsVaccine').cardRight.getLayout().activeItem.getGrid();
//					Ext.getCmp('journalsVaccine').printGridList(grid);
						this.journalsSearchFilterForm.getForm().findField('SearchFormType').setValue('PrintForm');
						this.journalsSearchFilterForm.getForm().getEl().dom.action = Ext.getCmp('cardRight').getLayout().activeItem.dataUrl;
//					Ext.getCmp('vacSearchForm').getForm().getEl().dom.acceptCharset = 'utf-8';
						this.journalsSearchFilterForm.getForm().getEl().dom.acceptCharset = 'utf-8';
						var curCardRightId = Ext.getCmp('journalsVaccine').cardRight.getLayout().activeItem.id;
						if (Ext.getCmp(curCardRightId).getGrid().getBottomToolbar().getPageData().total > 1000) {
							alert("На печать будут выведены только первые 1000 записей!");
						}
						Ext.getCmp('vacSearchForm').getForm().submit();
					}.createDelegate(this),
					iconCls: 'print16',
					text: BTN_GRIDPRINT,
					tabIndex: TABINDEX_VACMAINFRM + 53
				}, {
					text: 'Добавить пациента',
					iconCls: 'add16',
					handler: function () {
						getWnd('swPersonSearchWindow').show({
							onSelect: function (person_data) {
								var params = new Object();
								this.hide();
								params.person_id = person_data.Person_id;

								sw.Promed.vac.utils.callVacWindow({
									record: params,
									type1: 'btnForm',
									type2: 'btnAddPerson'
								}, this);

							}
						})
					},
					tabIndex: TABINDEX_VACMAINFRM + 54

				}, {
					text: '-'
				},
				{text: BTN_FRMHELP,
					iconCls: 'help16',
					tabIndex: TABINDEX_VACMAINFRM + 55,
					handler: function (button, event)
					{
						ShowHelp(Ext.getCmp('journalsVaccine').titleBase);
					}
				},
				{
					text: BTN_FRMCLOSE,
					iconCls: 'cancel16',
					handler: function (button, event) {
						this.hide();
					}.createDelegate(this),
					onTabAction: function () {
						form.journalsSearchFilterForm.findById('Journals_Person_Surname').focus(true, 0);
					},
					onShiftTabAction: function () {
						Ext.getCmp('Journals_BottomButtons').buttons[1].focus(false, 0);
					},
					tabIndex: TABINDEX_VACMAINFRM + 56
				}
			],
			buttonAlign: "left"
		};

//==========================================================================

		this.wincenter = new Ext.Panel({
			region: 'center',
			layout: 'border', // тип лэйоута - трехколонник с подвалом и шапкой
			items: [
				//Панель фильтров
				form.journalsSearchFilterForm,
				//Таблица
				form.cardRight,
				//Кнопки в нижней части формы
				form.journalsButtons
			]
		});

		Ext.apply(this, {
			title: 'Просмотр журналов вакцинации',
			closable: true,
			closeAction: 'hide',
			width: 600,
			minWidth: 350,
			height: 350,
			layout: 'border',
			bodyStyle: 'padding: 5px;',
			items: [
				form.grid0,
				form.wincenter
			]
		});

		sw.Promed.amm_mainForm.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
			alt: true,
			fn: function (inp, e) {
				var search_filter_tabbar = Ext.getCmp('filtrExtTab');
				log('e.getKey() = ', e.getKey());
				//$page = $title.match(/[0-9]/) - 1;
				//search_filter_tabbar.setActiveTab($page)
				switch (e.getKey()) { 
					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						search_filter_tabbar.setActiveTab(0);
						break;
					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						search_filter_tabbar.setActiveTab(1);
						//Ext.getCmp('vacSearchForm').getForm().findField('SurName').focus();
						break;
					case Ext.EventObject.NUM_THREE:
					case Ext.EventObject.THREE:
						search_filter_tabbar.setActiveTab(2);
						break;
				}
			},
			key: [
				Ext.EventObject.NUM_ONE,
				Ext.EventObject.NUM_TWO,
				Ext.EventObject.NUM_THREE,
				Ext.EventObject.ONE,
				Ext.EventObject.TWO,
				Ext.EventObject.THREE
			],
			stopEvent: true
		}],
	listeners: {
		'hide': function () {
			this.onHide();
		}
	},
	show: function () {
		sw.Promed.amm_mainForm.superclass.show.apply(this, arguments);

		Ext.getCmp('cardRight').getLayout().setActiveItem(this.activeJournalKey);
		Ext.getCmp('journalsVaccine').setTitle(this.titleBase + ': ' + this.journals[this.activeJournalKey].title);

		this.journalsSearchFilterForm.getForm().getEl().dom.method = "post";
		this.journalsSearchFilterForm.getForm().getEl().dom.target = "_blank";
		this.journalsSearchFilterForm.getForm().standardSubmit = true;
		//показываем вкладку с адресами
		Ext.getCmp('filtrExtTab').add(this.SearchFiltersAdr);
		//показываем вкладку с Доп фильтрами:
		this.refreshSearchTabs(Ext.getCmp('filtrExtTab'));
		Ext.getCmp('vacSearchForm').getForm().Date_Plan = new Date().format('d.m.Y') + ' - ' + new Date().format('d.m.Y');

		this.viewOnly = false;
			if (arguments[0])
		{
			if (arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		if (this.viewOnly == true)
			Ext.getCmp('Journals_BottomButtons').buttons[3].hide();
		else
			Ext.getCmp('Journals_BottomButtons').buttons[3].show();

		this.ViewFrameVacPlan.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameVacFixed.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameVacAccount.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameTubPlan.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameTubAssigned.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameTubReaction.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameMedTapRefusal.setActionDisabled('action_edit', this.viewOnly);
		this.ViewFrameMedTapRefusal.setActionDisabled('action_delete', this.viewOnly);
		//настройка отображения основного фильтра:
		var curForm = this.journalsSearchFilterForm.getForm();
		if (curForm.findField('AttachMethod_id').getValue() == 1) {
			curForm.findField('Org_id').showContainer();
			curForm.findField('OrgType_id').hideContainer();
		} else {
			curForm.findField('Org_id').hideContainer();
			curForm.findField('OrgType_id').showContainer();
		}
		/*this.ViewFrameVacPlan = new sw.Promed.ViewFrame(
		 {
		 id: 'amm_VacPlan',*/
		this.journalsSearchFilterForm.getForm().findField('Lpu_id').getStore().load({
			callback: function () {
				var comboLpuID = this.journalsSearchFilterForm.getForm().findField('Lpu_id');
				var comboLpuATID = this.journalsSearchFilterForm.getForm().findField('Lpu_atid');
				if((getRegionNick() == 'perm')){
					comboLpuID.setValue();
					comboLpuID.disable();
					// var rec = comboLpuATID.findRecord('Lpu_Name', "Пермь ГКП 2"); //10010833
					var rec = comboLpuATID.findRecord('Lpu_id', 10010833);
					if(rec && rec.get('Lpu_id')){
						comboLpuATID.setValue(rec.get('Lpu_id'));
					}else{
						comboLpuATID.setValue();
					}
				}else{
					comboLpuID.setValue(getGlobalOptions().lpu_id);
					comboLpuATID.setValue(getGlobalOptions().lpu_id);
				}
				// this.journalsSearchFilterForm.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				// this.journalsSearchFilterForm.getForm().findField('Lpu_atid').setValue(getGlobalOptions().lpu_id);
				this.journalsSearchFilterForm.getForm().findField('uch_id').getStore().load({
					params: {
						lpu_id: getGlobalOptions().lpu_id
					},
					callback: function () {
						this.journalsSearchFilterForm.getForm().findField('uch_id').setValue(-1);
						SearchJournalsVac();
					}.createDelegate(this)
				});

				this.journalsSearchFilterForm.getForm().findField('Org_id').getStore().load({
					params: {
						lpu_id: getGlobalOptions().lpu_id
					}
					, callback: function () {
						this.journalsSearchFilterForm.getForm().findField('Org_id').setValue(0);
					}.createDelegate(this)
				});

				this.journalsSearchFilterForm.getForm().findField('SurName').focus();
			}.createDelegate(this)
		});

	}
});