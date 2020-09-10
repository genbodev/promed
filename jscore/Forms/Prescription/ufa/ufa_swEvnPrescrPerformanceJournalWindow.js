/**
 * swEvnPrescrPerformanceJournalWindow - окно просмотра "Журнал назначений и выполнений"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Нигматуллин Тагир
 * @version      НОЯБРЬ 2018
 
 */

sw.Promed.swEvnPrescrPerformanceJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: "Журнал назначений и выполнений",
	id: 'PrescrPerformanceJournalWindow',
	border: false,
	width: 800,
	height: 500,
	maximized: true,
	maximizable: true,
	layout: 'border',
	resizable: true,
	codeRefresh: true,
	period: '',
	setFilter: function () {
		//  Поиск в гриде
		var store = Ext.getCmp('PrescrPerformanceGrid').getGrid().getStore();
		Ext.getCmp('PrescrPerformanceGrid').getGrid().store.clearFilter()
		store.filterBy(function (rec) {
			return ((rec.get('Person_Fio').toUpperCase().indexOf(Ext.getCmp('PrescrPerform_FIO').getValue().toUpperCase()) >= 0)
					&& (rec.get('DrugCode_Plan') == Ext.getCmp('PrescrPerform_DrugCodeNazn').getValue() || Ext.getCmp('PrescrPerform_DrugCodeNazn').getValue() == "")
					&& (rec.get('DrugName_Plan').toUpperCase().indexOf(Ext.getCmp('PrescrPerform_DrugNameNazn').getValue().toUpperCase()) >= 0)
					&& (rec.get('DrugCode_DocUc') == Ext.getCmp('PrescrPerform_DrugCodeIspoln').getValue() || Ext.getCmp('PrescrPerform_DrugCodeIspoln').getValue() == "")
					&& (rec.get('DrugName_DocUc').toUpperCase().indexOf(Ext.getCmp('PrescrPerform_DrugNameIspoln').getValue().toUpperCase()) >= 0)
					&& (Ext.getCmp('PrescrPerform_IspolnCombo').getValue() != 6 
							&& (rec.get('EvnPrescrDay_IsExec') == Ext.getCmp('PrescrPerform_IspolnCombo').getValue() || Ext.getCmp('PrescrPerform_IspolnCombo').getValue() == '-1')
						|| (Ext.getCmp('PrescrPerform_IspolnCombo').getValue() == 6 && rec.get('CourseGoodsUnitNick_Ctrl') == 1))
					&& (Ext.getCmp('PrescrPerform_Differences').getValue() ? rec.get('ctrl') == '1' : ' 1==1 ')
					)
		});
	},
	doSearch: function () {
		//  Поиск в БД
		var form = this;
		if (Ext.getCmp('PrescrPerform_PeriodDate').value != form.period) {
			var d1 = Ext.getCmp('PrescrPerform_PeriodDate').getValue1();
			var d2 = Ext.getCmp('PrescrPerform_PeriodDate').getValue2();

			if (d1.getFullYear() != d2.getFullYear() || d1.getMonth() != d2.getMonth()) {
				sw.swMsg.alert('Внимание', 'Отчетный период должен быть в пределах календарного месяца.');
				return false;
			}

			var params = new Object();
			params.Lpu_id = form.lpu_id,
			params.LpuSection_id = form.lpuSection_id, 
					params.PeriodRange = Ext.getCmp('PrescrPerform_PeriodDate').value;
			//		params.PrescrPerform_DrugNameNazn = Ext.getCmp('PrescrPerform_DrugNameNazn').getValue();
			//		params.PrescrPerform_DrugCodeNazn = Ext.getCmp('PrescrPerform_DrugCodeNazn').getValue();
			//		params.PrescrPerform_DrugNameIspoln = Ext.getCmp('PrescrPerform_DrugNameIspoln').getValue();
			//		params.PrescrPerform_DrugCodeIspoln = Ext.getCmp('PrescrPerform_DrugCodeIspoln').getValue();
			//		params.PrescrPerform_IspolnCombo = Ext.getCmp('PrescrPerform_IspolnCombo').getValue();
			//		params.PrescrPerform_Differences = Ext.getCmp('PrescrPerform_Differences').getValue() ? 1 : 0;
			form.PrescrPerformanceGrid.getGrid().getStore().load({
				params: params
			});
		} else {
			form.setFilter();
		}
	},
	initComponent: function () {
		var form = this;

		this.SearchParamsPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			labelWidth: 110,
			autoHeight: true,
			id: 'PrescrPerform_FilterPanel',
			filter: {
				title: langs('Фильтры'),
				collapsed: false,
				id: 'PrescrPerform_rr',
				layout: 'form',
				items: [
					{
						layout: 'column',
						items: [
							{layout: 'form',
								labelWidth: 120,
								items: [
									{
										name: "PrescrPerform_PeriodDate",
										id: 'PrescrPerform_PeriodDate',
										xtype: "daterangefield",
										layout: 'form',
										allowBlank: false,
										width: 200,
										Height: 70,
										//labelWidth: 200,
										fieldLabel: langs('Период'),
										plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
										tabIndex: TABINDEX_PrescrPerformance + 1
									}
								]
							},
							{
								layout: 'form',
								labelWidth: 170,
								items: [
									{
										fieldLabel: 'ФИО пациента',
										width: 465,
										id: 'PrescrPerform_FIO',
										xtype: 'textfield',
										tabIndex: TABINDEX_PrescrPerformance + 2
									}
								]
							},
						]},
					{
						layout: 'column',
						items: [
							{
								id: 'PrescrPerform_filter1',
								style: 'margin: 5px; ',
								autoHeight: true,
								autoScroll: true,
								title: 'Назначено',
								labelWidth: 120,
								width: 500,
								xtype: 'fieldset',
								items: [
									{
										layout: 'column',
										items: [
											{layout: 'form',
												items: [
													{
														fieldLabel: langs('Код ЛП'),
														width: 200,
														id: 'PrescrPerform_DrugCodeNazn',
														xtype: 'textfield',
														tabIndex: TABINDEX_PrescrPerformance + 3
													}
												]
											},
											{layout: 'form',
												//labelWidth: 120,
												items: [
													{
														fieldLabel: 'Наименование ЛС',
														width: 340,
														id: 'PrescrPerform_DrugNameNazn',
														xtype: 'textfield',
														tabIndex: TABINDEX_PrescrPerformance + 4
													}
												]
											}
										]
									}

								]
							},
							{
								id: 'PrescrPerform_filter2',
								style: 'margin: 5px; ',
								autoHeight: true,
								autoScroll: true,
								title: 'Выполнено',
								labelWidth: 120,
								width: 500,
								xtype: 'fieldset',
								items: [
									{
										layout: 'column',
										items: [
											{layout: 'form',
												items: [
													{
														fieldLabel: langs('Код ЛП'),
														width: 200,
														id: 'PrescrPerform_DrugCodeIspoln',
														xtype: 'textfield',
														tabIndex: TABINDEX_PrescrPerformance + 5
													}
												]
											},
											{layout: 'form',
												items: [
													{
														fieldLabel: 'Наименование ЛС',
														width: 340,
														id: 'PrescrPerform_DrugNameIspoln',
														xtype: 'textfield',
														tabIndex: TABINDEX_PrescrPerformance + 6
													}
												]
											}
										]
									}

								]
							}
						]},
					{
						layout: 'column',
						items: [
							{
								layout: 'form',
								labelWidth: 125,
								items: [
									{
										xtype: 'combo',
										fieldLabel: langs('Выполнение'),
										name: 'PrescrPerform_IspolnCombo',
										id: 'PrescrPerform_IspolnCombo',
										width: 340,
										triggerAction: 'all',
										forceSelection: true,
										store: [
											[-1, 'Все'],
											[1, 'Не выполнено'],
											[2, 'Выполнено с использованием медикаментов'],
											[3, 'Выполнено без использования медикаментов'],
											[4, 'ДУ не исполнен'],
											[5, 'Не назначено'],
											[6, 'Ошибка в единицах измерения']
										],
										allowBlank: false,
										value: -1,
										tabIndex: TABINDEX_PrescrPerformance + 7
									}

								]},
							{layout: 'form',
								labelWidth: 200,
								items: [
									{
										xtype: 'checkbox',
										id: 'PrescrPerform_Differences',
										fieldLabel: langs('Расхождения в количестве'),
										tabIndex: TABINDEX_PrescrPerformance + 8
									}
								]
							},
							{
								layout: 'form',
								items: [{
										style: "padding-left: 120px",
										text: langs('Найти'),
										xtype: 'button',
										iconCls: 'search16',
										id: 'PrescrPerformSearch',
										tabIndex: TABINDEX_PrescrPerformance + 9,
										handler: function () {
											Ext.getCmp('PrescrPerformanceJournalWindow').doSearch();
										}
									}]
							},
							{
								layout: 'form',
								items: [{
										style: "padding-left: 25px",
										//style: "padding-right: 25px",
										xtype: 'button',
										id: 'PrescrPerform_BtnClear',
										text: langs('Сброс'),
										iconCls: 'reset16',
										tabIndex: TABINDEX_PrescrPerformance + 10,
										handler: function () {
											//  Очищаем фильтр на панеле фильтров
											Ext.getCmp('PrescrPerform_FIO').reset();
											Ext.getCmp('PrescrPerform_DrugNameNazn').reset();
											Ext.getCmp('PrescrPerform_DrugCodeNazn').reset();
											Ext.getCmp('PrescrPerform_DrugNameIspoln').reset();
											Ext.getCmp('PrescrPerform_DrugCodeIspoln').reset();
											Ext.getCmp('PrescrPerform_IspolnCombo').setValue(-1);
											Ext.getCmp('PrescrPerform_Differences').setValue(0);
											Ext.getCmp('PrescrPerformanceGrid').getGrid().store.clearFilter();
										}
									}]
							}

						]
					}

				]
			}
		}),
				this.PrescrPerformanceGrid = new sw.Promed.ViewFrame({
					id: 'PrescrPerformanceGrid',
					title: '',
					dataUrl: '/?c=EvnPrescr&m=loadPrescrPerformanceList',
					autoLoadData: false,
					region: 'center',
					toolbar: true,
					cls: 'txtwrap',
					//paging: true,
					root: 'data',
					stringfields:
							[
								{name: 'RowNumber', type: 'int', header: 'ID', key: true, hidden: true},
								{name: 'EvnPrescrTreatDrug_id', type: 'int', header: 'DrugOstatRegistry_id', hidden: true},
								{name: 'EvnPrescrDay_id', header: 'Lpu_id', width: 80, hidden: true},
								{name: 'EvnPrescr_planDate', type: 'date', header: 'Дата<br>назначения', width: 80},
								{name: 'DocumentUc_diddate', type: 'date', header: 'Дата<br>выполнения', width: 80},
								{name: 'Person_Fio', header: 'ФИО пациента', width: 200},
								{name: 'DrugCode_Plan', header: 'Код ЛС<br>(назначено)', width: 80},
								{name: 'DrugName_Plan', header: 'Наименование ЛС<br>(назначено)', width: 250, id: 'autoexpand'},
								{name: 'DrugCode_DocUc', header: 'Код ЛС<br>(выполнено)', width: 80},
								{name: 'DrugName_DocUc', header: 'Наименование ЛС<br>(выполнено)', width: 250},
								{name: 'CourseGoodsUnit_Nick', header: 'Ед. изм.', width: 80,
									renderer: function (value, meta, r) {
										if (r.get('CourseGoodsUnitNick_Ctrl') == 1) {
											meta.css = 'x-grid-rowbackdarkred';
										};
										return value;
									}},
								{name: 'EvnPrescrTreatDrug_Kolvo', header: 'Назначено<br>(в ед. изм.)', width: 120},
								{name: 'DocumentUcStr_EdCount', header: 'Списано<br>(в ед. изм.)', width: 120},
								{name: 'EvnCourseTreatDrug_Count', header: 'Назначено<br>(в упаковках)', width: 120},
								{name: 'DocumentUcStr_Count', header: 'Списано<br>(в упаковках)', width: 120},
								{name: 'EvnPrescrDay_IsExec', header: 'EvnPrescrDay_IsExec', hidden: true},
								{name: 'comment', header: 'Выполнение', width: 250},
								{name: 'ctrl', header: 'ctrl', hidden: true}, 
								{name: 'CourseGoodsUnitNick_Ctrl', header: 'CourseGoodsUnitNick_Ctrl', hidden: true},
								{name: 'pmUser_execName', header: 'Выполнил', width: 120}
							],
					actions:
							[
								{name: 'action_add', hidden: true},
								{name: 'action_edit', hidden: true},
								{name: 'action_delete', hidden: true},
								{name: 'action_save', hidden: true},
								{name: 'action_view', hidden: true},
								{name: 'action_refresh', handler: function () {
										form.period = '';
										Ext.getCmp('PrescrPerformanceJournalWindow').doSearch();
									}}
							],
					onLoadData: function (isData) {
						if (isData) {
							form.period = Ext.getCmp('PrescrPerform_PeriodDate').value;
							form.setFilter();
						}

					}
				});

		this.PrescrPerformanceGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('EvnPrescrDay_IsExec') == '1') {//  Не выполнено
					cls = cls + 'x-grid-rowbold ';
				} else if (row.get('EvnPrescrDay_IsExec') == '2') { // Выполнено с использованием медикаментов
					cls = cls + 'x-grid-rowbold ';
					cls = cls + ' x-grid-rowgreen ';
					if(row.get('EvnCourseTreatDrug_Count') != row.get('DocumentUcStr_Count')){
						cls = cls + 'x-grid-rowbackyellow';
					}
				} else if (row.get('EvnPrescrDay_IsExec') =='3') { // Выполнено без использования медикаментов 
					//cls = cls + 'x-grid-rowbold ';
					//cls = cls + ' x-grid-rowgray ';
					cls = cls + 'x-grid-rowbackred';
				} else if (row.get('EvnPrescrDay_IsExec') =='4') { // ДУ не исполнен
					//cls = cls + 'x-grid-rowbold ';
					cls = cls + ' x-grid-rowgray ';
				} else if (row.get('EvnPrescrDay_IsExec') == '5') { // Не назначено
					cls = cls + 'x-grid-rowbackyellow';
				}
				return cls;
			}
		});

		Ext.apply(this, {
			lbodyBorder: true,
			layout: "border",
			cls: 'tg-label',
			items:
					[
						form.SearchParamsPanel,
						{
							layout: 'border',
							region: 'center',
							items: [
								form.PrescrPerformanceGrid
							]
						}
					],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, TABINDEX_PrescrPerformance + 31),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'PrescrPerform_CancelButton',
					tabIndex: TABINDEX_PrescrPerformance + 32,
					text: langs('Закрыть'),
					onTabAction: function () {
						Ext.getCmp('PrescrPerform_PeriodDate').focus(true, 50);
					}.createDelegate(this),
				}
			]
		});


		sw.Promed.swEvnPrescrPerformanceJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		sw.Promed.swEvnPrescrPerformanceJournalWindow.superclass.show.apply(this, arguments);
		var form = this;
		var d1 = new Date(2016, 0, 1);
		var d2 = new Date();
		var date = new Date(getGlobalOptions().date.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
		if (arguments[0] && arguments[0].LpuSection_id) {
			form.lpuSection_id = arguments[0].LpuSection_id;
			form.lpu_id = arguments[0].Lpu_id;
		}

		d1 = new Date(d2.getFullYear(), d2.getMonth(), 1);

		Ext.getCmp('PrescrPerform_PeriodDate').setValue(d1.format('d.m.Y') + ' - ' + d2.format('d.m.Y'));
		Ext.getCmp('PrescrPerform_CancelButton').focus(true, 50);
	}

});
