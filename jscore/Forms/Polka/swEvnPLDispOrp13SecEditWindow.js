/**
* swEvnPLDispOrp13SecEditWindow - окно редактирования/добавления карты по диспасеризации детей-сирот с 2013 года
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2013 Swan Ltd.
* @author     Dmitry Vlasenko
* @originalauthor	Марков Андрей / Stas Bykov aka Savage (savage1981@gmail.com)
* @version    24.05.2013
* @comment    Префикс для id компонентов EPLDO13SEF (EvnPLDispOrp13SecEditForm)
*	            TABINDEX_EPLDO13SEF: 9300
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLDispOrp_id - ID карты для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              Server_id - ?
*
*
* Использует: окно просмотра истории болезни (swPersonCureHistoryWindow)
*             окно просмотра льгот (swPersonPrivilegeViewWindow)
*             окно редактирования человека (swPersonEditWindow)
*             окно добавления/редактирования услуги по ДД (swEvnUslugaDispOrp13SecEditWindow)
*             окно добавления/редактирования посещения по ДД (swEvnVizitDispOrp13SecEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispOrp13SecEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: 'add',
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispOrp13SecEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispOrp13SecEditWindow.js',
	deleteEvnVizitDispOrp: function() 
	{
		var win = this;
		var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
		
		sw.swMsg.show(
		{
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) 
			{
				if ('yes' == buttonId)
				{
					var evnvizitdispdop_grid = win.findById('EPLDO13SEFEvnVizitDispOrpGrid');

					if (!evnvizitdispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnvizitdispdop_grid.getSelectionModel().getSelected();
					var EvnVizitDispOrp_id = selected_record.get('EvnVizitDispOrp_id');
					
					win.getLoadMask().show();
					Ext.Ajax.request({
						url: '/?c=EvnPLDispOrp13&m=deleteEvnVizitDispOrp',
						params: {EvnVizitDispOrp_id: EvnVizitDispOrp_id},
						failure: function(response, options) {
							win.getLoadMask().hide();
						},
						success: function(response, action) {
							win.getLoadMask().hide();
							win.EvnPLDispOrpVizitPanel.getStore().load({ 
								params: { EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue() },
								callback: function() {
									win.reloadDopDispInfoConsentGrid();
								}
							});
					}
						});
					
					// удаляем соответствующую строку из грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
					var index = win.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) {
						if ( rec.get('EvnVizitDispOrp_id') == EvnVizitDispOrp_id ) {
							return true;
						}
						else {
							return false;
						}
					});
					
					if ( index >= 0 ) {
						var record = win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
						if (record) {
							win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
						}
					}

					if (evnvizitdispdop_grid.getStore().getCount() == 0)
					{
						evnvizitdispdop_grid.getTopToolbar().items.items[1].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[2].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnvizitdispdop_grid.getView().focusRow(0);
						evnvizitdispdop_grid.getSelectionModel().selectFirstRow();
					}

					/*if ( evnvizitdispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnvizitdispdop_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osmotr_vracha-spetsialista'],
			title: lang['vopros']
		})
	},
	deleteEvnUslugaDispOrp: function() {
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnuslugadispdop_grid = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid');

					if (!evnuslugadispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}					

					var selected_record = evnuslugadispdop_grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						evnuslugadispdop_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnuslugadispdop_grid.getStore().filterBy(function(record) {
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (evnuslugadispdop_grid.getStore().getCount() == 0)
					{
						evnuslugadispdop_grid.getTopToolbar().items.items[1].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[2].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnuslugadispdop_grid.getView().focusRow(0);
						evnuslugadispdop_grid.getSelectionModel().selectFirstRow();
					}

					current_window.reloadDopDispInfoConsentGrid();
					
					/*if ( evnuslugadispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnuslugadispdop_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_laboratornoe_issledovanie'],
			title: lang['vopros']
		})
	},
	draggable: true,
	doSave: function(callback, print, check_finish, options) {
		options = options||{};
		var current_window = this;

		var base_form = current_window.findById('EvnPLDispOrp13SecEditForm').getForm();
		var evnpldispdop_form = current_window.findById('EvnPLDispOrp13SecEditForm');// форма с гридами ниже инфоблока с кнопками: Прикрепление, Изменить данные...
		var evnvizitdispdop_grid = current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid');// грид Осмотр врача-специалиста
		var evnuslugadispdop_grid = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid');// грид Обследования
		var evndiagandrecomendation_grid = current_window.EvnDiagAndRecomendationPanel.getGrid();// грид Диагнозы и рекомендации...
		var i = 0;

		if (!evnpldispdop_form.getForm().isValid())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					// current_window.findById('EPLDO13SEFAttachTypeCombo').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		if (
			!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
			&& base_form.findField('HealthKind_id').getValue() == 1
			&& evndiagandrecomendation_grid.getStore().getCount() > 0
			&& !Ext.isEmpty(evndiagandrecomendation_grid.getStore().getAt(0).get('EvnVizitDispOrp_id'))
		) {
			sw.swMsg.alert(lang['oshibka'], 'Нельзя выбрать I группу здоровья при указании диагнозов и рекомендаций по результатам диспансеризации / профосмотра');
			return false;
		}

		if (
			typeof callback != 'function'
			&& getRegionNick() == 'pskov'
			&& base_form.findField('HealthKind_id').getValue() > 2
			&& (
				this.DispAppointGrid.getGrid().getStore().getCount() == 0
				|| Ext.isEmpty(this.DispAppointGrid.getGrid().getStore().getAt(0).get('DispAppointType_id'))
			)
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Раздел «Назначения» должен содержать хотя бы одну запись, так как указана группа здоровья III, IV или V.');
			return false;
		}

		// Проверяем заполнение данных в диагнозах и рекомендациях по результатам диспансеризации / профосмотра
		// @task https://redmine.swan.perm.ru/issues/77880
		if (
			!callback &&
			base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 
			&& evndiagandrecomendation_grid.getStore().getCount() > 0
			&& !Ext.isEmpty(evndiagandrecomendation_grid.getStore().getAt(0).get('EvnVizitDispOrp_id'))
			&& !getRegionNick().inlist([ 'ufa' ])
		) {
			var
				FormDataJSON,
				noRequiredData = false;

			evndiagandrecomendation_grid.getStore().each(function(rec) {
				if ( Ext.isEmpty(rec.get('FormDataJSON')) ) {
					noRequiredData = true;
				}
				else {
					FormDataJSON = Ext.util.JSON.decode(rec.get('FormDataJSON'));

					if ( Ext.isEmpty(FormDataJSON.DispSurveilType_id) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsFirstTime) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsVMP) ) {
						noRequiredData = true;
					}
				}
			});

			if ( noRequiredData == true ) {
				sw.swMsg.alert('Ошибка', 'Не заполнены обязательные поля в разделе «Диагнозы и рекомендации по результатам диспансеризации / профосмотра».');
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrp13SecEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		// Собираем данные из гридов
		var params = new Object();
		params.EvnUslugaDispOrp = Ext.util.JSON.encode(getStoreRecords(evnuslugadispdop_grid.getStore(), {
			clearFilter: true
		}));
		evnuslugadispdop_grid.getStore().filterBy(function(record){
			if (record.data.Record_Status != 3) return true;
		});
		params.EvnDiagAndRecomendation = Ext.util.JSON.encode(getStoreRecords(evndiagandrecomendation_grid.getStore()));

		if ( base_form.findField('ChildStatusType_id').disabled )  {
			params.ChildStatusType_id = base_form.findField('ChildStatusType_id').getValue();
		}

		if ( base_form.findField('PayType_id').disabled )  {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreOsmotrDlit = (!Ext.isEmpty(options.ignoreOsmotrDlit) && options.ignoreOsmotrDlit === 1) ? 1 : 0;

		evnpldispdop_form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 109) {
										options.ignoreParentEvnDateCheck = 1;
									}
									else if (action.result.Error_Code == 110) {
										options.ignoreOsmotrDlit = 1;
									}

									current_window.doSave(callback, print, check_finish, options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else
					if (action.result.Error_Msg)
					{
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				if (action.result)
				{
					if (!Ext.isEmpty(action.result.EvnPLDispOrp_id)) {
						current_window.findById('EPLDO13SEFEvnPLDispOrp_id').setValue(action.result.EvnPLDispOrp_id);
					}
					var evnpldispdop_id = current_window.findById('EPLDO13SEFEvnPLDispOrp_id').getValue();
					
					loadMask.hide();
					if ( print )
					{
						// перечитать данные гридов
						// загрузка грида осмотров
						current_window.EvnPLDispOrpVizitPanel.getStore().load({ 
							params: { EvnPLDispOrp_id: evnpldispdop_id },
							callback: function() {
								if ( Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid').getStore().getCount() == 0 )
									LoadEmptyRow(Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid'));
							}
						});

						// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
						current_window.EvnDiagAndRecomendationPanel.loadData({
							params: {
								EvnPLDispOrp_id: evnpldispdop_id
							},
							globalFilters: {
								EvnPLDispOrp_id: evnpldispdop_id
							},
							noFocusOnLoad: true
						});

						// загрузка грида обследований
						current_window.EvnUslugaDispOrpPanel.getStore().load({ 
							params: { EvnPLDispOrp_id: evnpldispdop_id },
							callback: function() {
								if ( Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
									LoadEmptyRow(Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid'));
							}
						});
								
						var evn_pl_id = current_window.findById('EPLDO13SEFEvnPLDispOrp_id').getValue();
						var server_id = current_window.findById('EPLDO13SEFServer_id').getValue();
						window.open(C_EPLDO13_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
					}
					else
					{
						current_window.callback();
						if (typeof callback == 'function') {
							callback();
						} else {
							current_window.hide();
						}
					}
				}
				else
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();

		if (getRegionNick().inlist([ 'buryatiya', 'krym' ])) {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
						base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id'));
					}
				}
			});
		}
	},
	evnVizitDispOrpEditWindow: null,
	genId: function(obj)
	{
		var id_field = null;
		var index = 0;
		var result = null;
		var store = null;

		switch (obj)
		{

			case 'vizit':
				id_field = 'EvnVizitDispOrp_id';
				store = this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore();
				break;

			case 'usluga':
				id_field = 'EvnUslugaDispOrp_id';
				store = this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore();
				break;

			default:
				return result;
				break;
		}

		while (index >= 0 || result == 0)
		{
			result = Math.floor(Math.random() * 1000000);
			index = store.findBy(function(rec) { return rec.get(id_field) == result; });
		}

		return result;
	},
	height: 570,
	id: 'EvnPLDispOrp13SecEditWindow',
	openEvnDiagAndRecomendationEditWindow: function(action) {
		var grid = this.EvnDiagAndRecomendationPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record) {
			return false;
		}
		
		var params = {
			callback: function(FormDataJSON) {
				// обновляем JSON-поле.
				record.set('FormDataJSON', FormDataJSON);
				record.commit();
			},
			FormDataJSON: record.get('FormDataJSON'),
			Diag_id: record.get('Diag_id'),
			action: action
		};
		
		if (getWnd('swEvnDiagAndRecomendationEditWindow').isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: lang['okno_sostoyanie_zdorovya_redaktirovanie_uje_otkryito'],
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagAndRecomendationEditWindow').show(params);
	},
	reloadDopDispInfoConsentGrid: function() {
		// чистим грид согласий
		var newstore = [];
		this.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		// собираем данные в гридах осмотров и обследований
		this.EvnPLDispOrpVizitPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnVizitDispOrp_id'))) {
				newstore.push({
					DopDispInfoConsent_id: 'viz'+rec.get('EvnVizitDispOrp_id'),
					UslugaComplex_Name: rec.get('UslugaComplex_Name'),
					DopDispInfoConsent_IsAgree: true
				});
			}
		});
		this.EvnUslugaDispOrpPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnUslugaDispOrp_id'))) {
				newstore.push({
					DopDispInfoConsent_id: 'viz'+rec.get('EvnUslugaDispOrp_id'),
					UslugaComplex_Name: rec.get('UslugaComplex_Name'),
					DopDispInfoConsent_IsAgree: true
				});
			}
		});
		// запихиваем в грид согласий
		this.dopDispInfoConsentGrid.getGrid().getStore().loadData(newstore);
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDO13SEF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispOrp13&m=loadDopDispInfoConsent',
			region: 'center',
			height: 200,
			title: '',
			toolbar: false,
			saveAtOnce: false, 
			saveAllParams: false, 
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'string', header: 'ID', key: true },
				{ name: 'UslugaComplex_Name', type: 'string', sortable: false, header: lang['osmotr_issledovanie'], id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkbox', isparams: true, header: lang['soglasie_grajdanina'], width: 180 }
			]
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [
				win.dopDispInfoConsentGrid,
				// кнопки Печать и Сохранить
				{
					border: false,
					bodyStyle: 'padding:5px;',
					layout: 'column',
					items: [{
						border: false,
						bodyStyle: 'margin-left: 5px;',
						layout: 'form',
						items: [
							new Ext.Button({
								handler: function() {
									var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
									var paramEvnPLOrpDisp = base_form.findField('EvnPLDispOrp_id').getValue();
									if (paramEvnPLOrpDisp) {
										var dialog_wnd = Ext.Msg.show({
											title: lang['vid_soglasovaniya'],
											msg: lang['vyiberite_tip_soglasiya'],
											buttons: {yes: "От имени пациента", no: "От имени законного представителя", cancel: "Отмена"},
											fn: function(button){
												if (button == 'cancel') {
													return;
												}
												if (button == 'yes') {	//От имени пациента
													printBirt({
														'Report_FileName': 'EvnPLOrpDispInfoConsent.rptdesign',
														'Report_Params': '&paramEvnPLOrpDisp=' + paramEvnPLOrpDisp,
														'Report_Format': 'pdf'
													});
												}
												if (button == 'no') {	//От имени законного представителя
													printBirt({
														'Report_FileName': 'EvnPLOrpDispInfoConsent_Deputy.rptdesign',
														'Report_Params': '&paramEvnPLOrpDisp=' + paramEvnPLOrpDisp,
														'Report_Format': 'pdf'
													});
												}
											}
										});
									}
								}.createDelegate(this),
								iconCls: 'print16',
								text: BTN_FRMPRINT
							})
						]
					}]
				}
			],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			animCollapse: false,
			title: lang['informirovannoe_dobrovolnoe_soglasie']
		});
		
		this.EvnDiagAndRecomendationPanel = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagAndRecomendationEditForm',
			object: 'EvnDiagAndRecomendation',
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('edit');
				}},
				{ name: 'action_view', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('view');
				}},
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			id: 'EPLDO13SEF_EvnDiagAndRecomendationGrid',
			dataUrl: '/?c=EvnPLDispOrp13&m=loadEvnDiagAndRecomendationSecGrid',
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
			},
			title: lang['diagnozyi_i_rekomendatsii_po_rezultatam_dispanserizatsii_profosmotra'],
			toolbar: true,
			stringfields: [
				{ name: 'EvnVizitDispOrp_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'FormDataJSON', type: 'string', hidden: true }, // данные формы "Состояние здоровья: Редактирование"
				{ name: 'UslugaComplex_Name', type: 'string', header: lang['spetsialnost'], width: 300 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], id: 'autoexpand' }
			]
		});

		this.CostPrintPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['spravka_o_stoimosti_lecheniya'],
			hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
				bodyStyle: 'padding: 5px',
				border: false,
				height: 90,
				layout: 'form',
				region: 'center',
				items: [{
					fieldLabel: lang['data_vyidachi_spravki_otkaza'],
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnCostPrint_setDT',
					xtype: 'swdatefield'
				},{
					fieldLabel: lang['otkaz'],
					hiddenName: 'EvnCostPrint_IsNoPrint',
					width: 60,
					xtype: 'swyesnocombo'
				}]
			}]
		});
		
		this.EvnPLDispOrpVizitPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnVizitDispOrp_setDate',
				header: lang['data'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: lang['osmotr'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'DopDispAlien_Name',
				header: lang['storonniy_spetsialist'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'Diag_Code',
				header: lang['diagnoz'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}],
			collapsible: true,
			frame: false,
			height: 200,
			id: 'EPLDO13SEFEvnVizitDispOrpGrid',
			keys: [{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.END,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.HOME,
					Ext.EventObject.INSERT,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispOrp13SecEditWindow').deleteEvnVizitDispOrp();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if (e.getKey() == Ext.EventObject.F4)
							{
								action = 'edit';
							}
							else if (e.getKey() == Ext.EventObject.ENTER)
							{
								action = 'edit';
							}

							Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnVizitDispOrpEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								Ext.getCmp('EPLDO13SEFIsFinishCombo').focus(true, 200);
							}
							else
							{
								var usluga_grid = Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid');
								if ( usluga_grid.getStore().getCount() > 0 )
								{
									usluga_grid.focus();
									usluga_grid.getSelectionModel().selectFirstRow();
									usluga_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDO13SEFSaveButton').focus();
								}
							}
						break;
							
						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == selected_record.data.EvnVizitDispOrp_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == selected_record.data.EvnVizitDispOrp_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnVizitDispOrpEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_vizitdispdop_id = sm.getSelected().data.EvnVizitDispOrp_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_vizitdispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnVizitDispOrp_id'
				}, [{
					mapping: 'EvnVizitDispOrp_id',
					name: 'EvnVizitDispOrp_id',
					type: 'int'
				}, {
					mapping: 'EvnDiagDopDispGridData',
					name: 'EvnDiagDopDispGridData'
				}, {
					mapping: 'Server_id',
					name: 'Server_id',
					type: 'int'
				}, {
					mapping: 'PersonEvn_id',
					name: 'PersonEvn_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'DopDispInfoConsent_id',
					name: 'DopDispInfoConsent_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_Code',
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					mapping: 'MedPersonal_id',
					name: 'MedPersonal_id',
					type: 'int'
				}, {
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispOrp_setDate',
					name: 'EvnVizitDispOrp_setDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispOrp_setTime',
					name: 'EvnVizitDispOrp_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispOrp_disDate',
					name: 'EvnVizitDispOrp_disDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispOrp_disTime',
					name: 'EvnVizitDispOrp_disTime',
					type: 'string'
				}, {
					mapping: 'Diag_id',
					name: 'Diag_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Name',
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					mapping: 'DopDispAlien_Name',
					name: 'DopDispAlien_Name',
					type: 'string'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'Diag_Code',
					name: 'Diag_Code',
					type: 'string'
				}, {
					mapping: 'DopDispDiagType_id',
					name: 'DopDispDiagType_id',
					type: 'int'
				}, {
					mapping: 'DopDispAlien_id',
					name: 'DopDispAlien_id',
					type: 'int'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: '/?c=EvnPLDispOrp13&m=loadEvnVizitDispOrpSecGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnVizitDispOrpEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnVizitDispOrpEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnVizitDispOrpEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').deleteEvnVizitDispOrp();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['osmotr_vracha-spetsialista']
		});
		
		this.EvnUslugaDispOrpPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnUslugaDispOrp_setDate',
				header: lang['data'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: lang['obsledovanie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'ExaminationPlace_Name',
				header: lang['mesto_vyipolneniya'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}],
			frame: false,
			height: 200,
			id: 'EPLDO13SEFEvnUslugaDispOrpGrid',
			keys: [{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.END,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.HOME,
					Ext.EventObject.INSERT,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispOrp13SecEditWindow').deleteEvnUslugaDispOrp();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;
							
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								var vizit_grid = Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid');
								if ( vizit_grid.getStore().getCount() > 0 )
								{
									vizit_grid.focus();
									vizit_grid.getSelectionModel().selectFirstRow();
									vizit_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDO13SEFIsFinishCombo').focus(true, 200);
								}
							}
							else
							{												
								Ext.getCmp('EPLDO13SEFSaveButton').focus();
							}
						break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if ((e.getKey() == Ext.EventObject.F4) || (e.getKey() == Ext.EventObject.ENTER))
							{
								action = 'edit';
							}

							Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnUslugaDispOrpEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == selected_record.data.EvnUslugaDispOrp_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == selected_record.data.EvnUslugaDispOrp_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnUslugaDispOrpEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'south',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_uslugadispdop_id = sm.getSelected().data.EvnUslugaDispOrp_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_uslugadispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnUslugaDispOrp_id'
				}, [{
					mapping: 'EvnUslugaDispOrp_id',
					name: 'EvnUslugaDispOrp_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_setDate',
					name: 'EvnUslugaDispOrp_setDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispOrp_setTime',
					name: 'EvnUslugaDispOrp_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_disDate',
					name: 'EvnUslugaDispOrp_disDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispOrp_disTime',
					name: 'EvnUslugaDispOrp_disTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispOrp_didDate',
					name: 'EvnUslugaDispOrp_didDate',
					type: 'date'
				}, {
					mapping: 'DopDispInfoConsent_id',
					name: 'DopDispInfoConsent_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'ExaminationPlace_id',
					name: 'ExaminationPlace_id',
					type: 'int'
				}, {
					mapping: 'ExaminationPlace_Name',
					name: 'ExaminationPlace_Name',
					type: 'string'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_id',
					name: 'MedPersonal_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Code',
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Name',
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					mapping: 'EvnUslugaDispOrp_Result',
					name: 'EvnUslugaDispOrp_Result',
					type: 'string'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: '/?c=EvnPLDispOrp13&m=loadEvnUslugaDispOrpSecGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnUslugaDispOrpEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnUslugaDispOrpEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').openEvnUslugaDispOrpEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispOrp13SecEditWindow').deleteEvnUslugaDispOrp();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['obsledovaniya']
		});

		this.DispAppointGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swDispAppointEditForm',
			object: 'DispAppoint',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			uniqueId: true,
			dataUrl: '/?c=DispAppoint&m=loadDispAppointGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DispAppoint_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'DispAppointType_id', type: 'int', hidden: true },
				{ name: 'MedSpecOms_id', type: 'int', hidden: true },
				{ name: 'ExaminationType_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_fid', type: 'int', hidden: true },
				{ name: 'DispAppointType_Name', type: 'string', header: 'Назначение', width: 350 },
				{ name: 'DispAppoint_Comment', type: 'string', header: 'Комментарий', id: 'autoexpand' }
			]
		});

		this.DispAppointPanel = new sw.Promed.Panel({
			hidden: getRegionNick() == 'kz',
			items: [
				win.DispAppointGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Назначения'
		});
		
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var current_window = Ext.getCmp('EvnPLDispOrp13SecEditWindow');

				current_window.findById('EPLDO13SEFPersonEvn_id').setValue(callback_data.PersonEvn_id);
				current_window.findById('EPLDO13SEFServer_id').setValue(callback_data.Server_id);
				
				current_window.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		Ext.apply(this, {
			items: [
				new Ext.Panel ({
					autoScroll: true,
					border: false,
					layout: 'form',
					region: 'north',
					height: (!Ext.isIE) ? 230 : 250,
					items: [
						win.PersonInfoPanel,
						new Ext.form.FormPanel({
							bodyBorder: false,
							border: false,
							buttonAlign: 'left',
							frame: false,
							autoHeight: true,
							id: 'EvnPLDispOrp13SecEditForm',
							labelAlign: 'right',
							labelWidth: 200,
							items: [{
									id: 'EPLDO13SEFEvnPLDispOrp_id',
									name: 'EvnPLDispOrp_id',
									value: 0,
									xtype: 'hidden'
								}, {
									name:'EvnPLDispOrp_IsPaid',
									xtype:'hidden'
								}, {
									name:'EvnPLDispOrp_IndexRep',
									xtype:'hidden'
								}, {
									name:'EvnPLDispOrp_IndexRepInReg',
									xtype:'hidden'
								}, {
									name: 'EvnPLDispOrp_fid',
									xtype: 'hidden'
								}, {
									id: 'EPLDO13SEFPerson_id',
									name: 'Person_id',
									value: 0,
									xtype: 'hidden'
								}, {
									id: 'EPLDO13SEFPersonEvn_id',
									name: 'PersonEvn_id',
									value: 0,
									xtype: 'hidden'
								}, {
									name: 'DispClass_id',
									value: 0,
									xtype: 'hidden'
								}, {
									id: 'EPLDO13SEFServer_id',
									name: 'Server_id',
									value: 0,
									xtype: 'hidden'
								}, {
									hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
									layout: 'form',
									border: false,
									items: [{
										hiddenName: 'UslugaComplex_id',
										width: 400,
										fieldLabel: lang['usluga_dispanserizatsii'],
										disabled: true,
										emptyText: '',
										nonDispOnly: false,
										xtype: 'swuslugacomplexnewcombo'
									}]
								}, {
									fieldLabel: lang['povtornaya_podacha'],
									listeners: {
										'check': function(checkbox, value) {
											if ( getRegionNick() != 'perm' ) {
												return false;
											}

											var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
	
											var
												EvnPLDispOrp_IndexRep = parseInt(base_form.findField('EvnPLDispOrp_IndexRep').getValue()),
												EvnPLDispOrp_IndexRepInReg = parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()),
												EvnPLDispOrp_IsPaid = parseInt(base_form.findField('EvnPLDispOrp_IsPaid').getValue());
	
											var diff = EvnPLDispOrp_IndexRepInReg - EvnPLDispOrp_IndexRep;
	
											if ( EvnPLDispOrp_IsPaid != 2 || EvnPLDispOrp_IndexRepInReg == 0 ) {
												return false;
											}
	
											if ( value == true ) {
												if ( diff == 1 || diff == 2 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep + 2;
												}
												else if ( diff == 3 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep + 4;
												}
											}
											else if ( value == false ) {
												if ( diff <= 0 ) {
													EvnPLDispOrp_IndexRep = EvnPLDispOrp_IndexRep - 2;
												}
											}
	
											base_form.findField('EvnPLDispOrp_IndexRep').setValue(EvnPLDispOrp_IndexRep);
										}
									},
									name: 'EvnPLDispOrp_RepFlag',
									xtype: 'checkbox'
								}, {
									allowBlank: false,
									disabled: true,
									typeCode: 'int',
									useCommonFilter: true,
									width: 300,
									xtype: 'swpaytypecombo'
								}, {
									allowBlank: false,
									disabled: true,
									comboSubject: 'ChildStatusType',
									fieldLabel: 'Статус ребёнка',
									hiddenName: 'ChildStatusType_id',
									lastQuery: '',
									width: 300,
									xtype: 'swcommonsprcombo'
								}, {
									name: 'EvnPLDispOrp_firSetDate',
									disabled: true,
									allowBlank: false,
									fieldLabel: lang['data_nachala_dispanserizatsii_1_etap'],
									format: 'd.m.Y',
									id: 'EPLDO13SEFEvnPLDispOrp_firSetDate',
									listeners: {
										'change': function(field, newValue, oldValue) {
											var age = -1;
											var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();

											if ( !Ext.isEmpty(newValue) ) {
												age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newValue);
											}

											if ( age >= 0 && age <= 4 ) {
												if ( win.action != 'view' ) {
													base_form.findField('NormaDisturbanceType_id').clearValue();
													base_form.findField('NormaDisturbanceType_uid').clearValue();
													base_form.findField('NormaDisturbanceType_eid').clearValue();

													base_form.findField('AssessmentHealth_Gnostic').enable();
													base_form.findField('AssessmentHealth_Motion').enable();
													base_form.findField('AssessmentHealth_Social').enable();
													base_form.findField('AssessmentHealth_Speech').enable();
													base_form.findField('NormaDisturbanceType_id').disable();
													base_form.findField('NormaDisturbanceType_uid').disable();
													base_form.findField('NormaDisturbanceType_eid').disable();
												}
											}
											else if ( age >= 5 && age <= 17 ) {
												if ( win.action != 'view' ) {
													base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
													base_form.findField('AssessmentHealth_Motion').setRawValue('');
													base_form.findField('AssessmentHealth_Social').setRawValue('');
													base_form.findField('AssessmentHealth_Speech').setRawValue('');

													base_form.findField('AssessmentHealth_Gnostic').disable();
													base_form.findField('AssessmentHealth_Motion').disable();
													base_form.findField('AssessmentHealth_Social').disable();
													base_form.findField('AssessmentHealth_Speech').disable();
													base_form.findField('NormaDisturbanceType_id').enable();
													base_form.findField('NormaDisturbanceType_uid').enable();
													base_form.findField('NormaDisturbanceType_eid').enable();
												}
											}
											else {
												// Закрыть для редактирования все поля блока "Оценка психического развития (состояния)"
												base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
												base_form.findField('AssessmentHealth_Motion').setRawValue('');
												base_form.findField('AssessmentHealth_Social').setRawValue('');
												base_form.findField('AssessmentHealth_Speech').setRawValue('');
												base_form.findField('NormaDisturbanceType_id').clearValue();
												base_form.findField('NormaDisturbanceType_uid').clearValue();
												base_form.findField('NormaDisturbanceType_eid').clearValue();

												base_form.findField('AssessmentHealth_Gnostic').disable();
												base_form.findField('AssessmentHealth_Motion').disable();
												base_form.findField('AssessmentHealth_Social').disable();
												base_form.findField('AssessmentHealth_Speech').disable();
												base_form.findField('NormaDisturbanceType_id').disable();
												base_form.findField('NormaDisturbanceType_uid').disable();
												base_form.findField('NormaDisturbanceType_eid').disable();
											}
										}
									},
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									xtype: 'swdatefield'
								}, {
									allowBlank: false,
									codeField: 'YesNo_Code',
									displayField: 'YesNo_Name',
									editable: false,
									value: 1,
									enableKeyEvents: true,
									fieldLabel: lang['sluchay_zakonchen'],
									hiddenName: 'EvnPLDispOrp_IsFinish',
									id: 'EPLDO13SEFIsFinishCombo',
									lastQuery: '',
									listeners: {
										'keydown': function(inp, e) {
											if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
											{
												e.stopEvent();
												var usluga_grid = Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid');
												var vizit_grid = Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid');
												if ( vizit_grid.getStore().getCount() > 0 )
												{
													vizit_grid.focus();
													vizit_grid.getSelectionModel().selectFirstRow();
													vizit_grid.getView().focusRow(0);
													return true;
												}
												if ( usluga_grid.getStore().getCount() > 0 )
												{
													usluga_grid.focus();
													usluga_grid.getSelectionModel().selectFirstRow();
													usluga_grid.getView().focusRow(0);
													return true;
												}
												Ext.getCmp('EPLDO13SEFSaveButton').focus();
											}
										},
										'change': function() {
											win.checkForCostPrintPanel();
										}
									},
									listWidth: 150,
									store: new Ext.db.AdapterStore({
										autoLoad: true,
										dbFile: 'Promed.db',
										fields: [
											{ name: 'YesNo_id', type: 'int' },
											{ name: 'YesNo_Code', type: 'int' },
											{ name: 'YesNo_Name', type: 'string' }
										],
										key: 'YesNo_id',
										sortInfo: { field: 'YesNo_Code' },
										tableName: 'YesNo'
									}),
									tabIndex: 2405,
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<font color="red">{YesNo_Code}</font>&nbsp;{YesNo_Name}',
										'</div></tpl>'
									),
									valueField: 'YesNo_id',
									width: 80,
									xtype: 'swbaselocalcombo'
								}, {
									allowBlank: false,
									fieldLabel: lang['data_podpisaniya_soglasiya_otkaza'],
									format: 'd.m.Y',
									id: 'EPLDO13SEFEvnPLDispOrp_consDate',
									name: 'EvnPLDispOrp_consDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									xtype: 'swdatefield'
								}, {
									fieldLabel: lang['sluchay_obslujen_mobilnoy_brigadoy'],
									name: 'EvnPLDispOrp_IsMobile',
									xtype: 'checkbox',
									listeners: {
										'check': function(checkbox, value) {
											var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
											
											if ( value == true ) {
												base_form.findField('Lpu_mid').setAllowBlank(false);
												base_form.findField('Lpu_mid').enable();
											} else {
												base_form.findField('Lpu_mid').setAllowBlank(true);
												base_form.findField('Lpu_mid').clearValue();
												base_form.findField('Lpu_mid').disable();
											}
										}
									}
								}, {
									fieldLabel: lang['mo_mobilnoy_brigadyi'],
									valueField: 'Lpu_id',
									hiddenName: 'Lpu_mid',
									xtype: 'sworgcombo',
									onTrigger1Click: function() {
										var combo = this;
										if (combo.disabled) {
											return false;
										}
										
										var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
										
										getWnd('swOrgSearchWindow').show({
											enableOrgType: false,
											onlyFromDictionary: true,
											object: 'lpu',
											DispClass_id: base_form.findField('DispClass_id').getValue(),
											Disp_consDate: (typeof win.findById('EPLDO13SEFEvnPLDispOrp_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDO13SEFEvnPLDispOrp_consDate').getValue(), 'd.m.Y') : win.findById('EPLDO13SEFEvnPLDispOrp_consDate').getValue()),
											onSelect: function(lpuData) {
												if ( lpuData.Lpu_id > 0 )
												{
													combo.getStore().load({
														params: {
															OrgType: 'lpu',
															Lpu_oid: lpuData.Lpu_id
														},
														callback: function()
														{
															combo.setValue(lpuData.Lpu_id);
															combo.focus(true, 500);
															combo.fireEvent('change', combo);
														}
													});
												}
												getWnd('swOrgSearchWindow').hide();
											},
											onClose: function() {combo.focus(true, 200)}
										});
									}
								}, {
									fieldLabel: 'Проведен вне МО',
									listeners: {
										'render': function() {
											if (getRegionNick() != 'ekb') {
												this.hideContainer();
											}
										}
									},
									name: 'EvnPLDispOrp_IsOutLpu',
									xtype: 'checkbox'
								},
								// Добровольное информированное согласие
								win.DopDispInfoConsentPanel,
								// Осмотры
								win.EvnPLDispOrpVizitPanel,
								// Обследования
								win.EvnUslugaDispOrpPanel,
								// Диагнозы и рекомендации по результатам диспансеризации / профосмотра
								win.EvnDiagAndRecomendationPanel,
								// Общая оценка здоровья
								{
									title: lang['obschaya_otsenka_zdorovya'],
									id: 'EPLDOEW_CommonHealthCheck',
									animCollapse: false,
									layout: 'form',
									border: false,
									xtype: 'panel',
									bodyStyle: 'padding: 5px;',
									items: [
										// группбокс
										{
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['otsenka_fizicheskogo_razvitiya'],
											width: 600,
											items: [
												{
													fieldLabel: lang['massa_kg'],
													name: 'AssessmentHealth_Weight',
													decimalPrecision: 1,
													minValue: 2,
													maxValue: 500,
													xtype: 'numberfield'
												},
												{
													fieldLabel: lang['rost_sm'],
													name: 'AssessmentHealth_Height',
													minValue: 20,
													maxValue: 275,
													xtype: 'numberfield'
												},
												{
													fieldLabel: lang['okrujnost_golovyi'],
													minValue: 6,
													maxValue: 99,
													name: 'AssessmentHealth_Head',
													xtype: 'numberfield'
												},
												{
													fieldLabel: lang['otklonenie_massa'],
													listeners: {
														'change': function(combo, newValue, oldValue) {
															var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
															if (newValue == 2) {
																base_form.findField('WeightAbnormType_id').enable();
																base_form.findField('WeightAbnormType_id').setAllowBlank(false);
															} else {
																base_form.findField('WeightAbnormType_id').clearValue();
																base_form.findField('WeightAbnormType_id').disable();
																base_form.findField('WeightAbnormType_id').setAllowBlank(true);
															}
														}
													},
													hiddenName: 'WeightAbnormType_YesNo',
													xtype: 'swyesnocombo'
												},
												{
													comboSubject: 'WeightAbnormType',
													disabled: true,
													fieldLabel: lang['tip_otkloneniya_massa'],
													hiddenName: 'WeightAbnormType_id',
													lastQuery: '',
													width: 300,
													xtype: 'swcommonsprcombo'
												},
												{
													fieldLabel: lang['otklonenie_rost'],
													listeners: {
														'change': function(combo, newValue, oldValue) {
															var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
															if (newValue == 2) {
																base_form.findField('HeightAbnormType_id').enable();
																base_form.findField('HeightAbnormType_id').setAllowBlank(false);
															} else {
																base_form.findField('HeightAbnormType_id').clearValue();
																base_form.findField('HeightAbnormType_id').disable();
																base_form.findField('HeightAbnormType_id').setAllowBlank(true);
															}
														}
													},
													hiddenName: 'HeightAbnormType_YesNo',
													xtype: 'swyesnocombo'
												},
												{
													comboSubject: 'HeightAbnormType',
													disabled: true,
													fieldLabel: lang['tip_otkloneniya_rost'],
													hiddenName: 'HeightAbnormType_id',
													lastQuery: '',
													width: 300,
													xtype: 'swcommonsprcombo'
												}
											],
											bodyStyle: 'padding: 5px;',
											xtype: 'fieldset'
										},
										{
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['otsenka_psihicheskogo_razvitiya_sostoyaniya'],
											width: 600,
											items: [
												{
													allowDecimals: false,
													allowNegative: false,
													fieldLabel: lang['poznavatelnaya_funktsiya_vozrast_razvitiya_mes'],
													minValue: 0,
													name: 'AssessmentHealth_Gnostic',
													xtype: 'numberfield'
												},
												{
													allowDecimals: false,
													allowNegative: false,
													fieldLabel: lang['motornaya_funktsiya_vozrast_razvitiya_mes'],
													minValue: 0,
													name: 'AssessmentHealth_Motion',
													xtype: 'numberfield'
												},
												{
													allowDecimals: false,
													allowNegative: false,
													fieldLabel: lang['emotsionalnaya_i_sotsialnaya_kontakt_s_okrujayuschim_mirom_funktsii_vozrast_razvitiya_mes'],
													minValue: 0,
													name: 'AssessmentHealth_Social',
													xtype: 'numberfield'
												},
												{
													allowDecimals: false,
													allowNegative: false,
													fieldLabel: lang['predrechevoe_i_rechevoe_razvitie_vozrast_razvitiya_mes'],
													minValue: 0,
													name: 'AssessmentHealth_Speech',
													xtype: 'numberfield'
												},
												{
													fieldLabel: lang['psihomotornaya_sfera'],
													hiddenName: 'NormaDisturbanceType_id',
													xtype: 'swnormadisturbancetypecombo'
												},
												{
													fieldLabel: lang['intellekt'],
													hiddenName: 'NormaDisturbanceType_uid',
													xtype: 'swnormadisturbancetypecombo'
												},
												{
													fieldLabel: lang['emotsionalno-vegetativnaya_sfera'],
													hiddenName: 'NormaDisturbanceType_eid',
													xtype: 'swnormadisturbancetypecombo'
												}
											],
											bodyStyle: 'padding: 5px;',
											xtype: 'fieldset'
										},
										{
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['otsenka_polovogo_razvitiya'],
											width: 600,
											items: [
												{
													fieldLabel: 'P',
													minValue: 0,
													maxValue: 5,
													name: 'AssessmentHealth_P',
													xtype: 'numberfield'
												},
												{
													fieldLabel: 'Ax',
													minValue: 0,
													maxValue: 5,
													name: 'AssessmentHealth_Ax',
													xtype: 'numberfield'
												},
												{
													fieldLabel: 'Fa',
													minValue: 0,
													maxValue: 5,
													name: 'AssessmentHealth_Fa',
													xtype: 'numberfield'
												},
												{
													fieldLabel: 'Ma',
													minValue: 0,
													maxValue: 5,
													name: 'AssessmentHealth_Ma',
													xtype: 'numberfield'
												},
												{
													fieldLabel: 'Me',
													minValue: 0,
													maxValue: 5,
													name: 'AssessmentHealth_Me',
													xtype: 'numberfield'
												},
												{
													autoHeight: true,
													style: 'padding: 0px;',
													id: 'EPLDO13SEF_menarhe',
													title: lang['harakteristika_menstrualnoy_funktsii_menarhe'],
													width: 580,
													items: [
														{
															fieldLabel: lang['let'],
															minValue: 6,
															maxValue: 17,
															name: 'AssessmentHealth_Years',
															xtype: 'numberfield'
														},
														{
															fieldLabel: lang['mesyatsev'],
															minValue: 0,
															maxValue: 12,
															name: 'AssessmentHealth_Month',
															xtype: 'numberfield'
														}
													],
													bodyStyle: 'padding: 5px;',
													xtype: 'fieldset'
												},
												{
													autoHeight: true,
													style: 'padding: 0px;',
													id: 'EPLDO13SEF_menses',
													title: lang['menses_harakteristika'],
													width: 580,
													items: [
														{
															boxLabel: lang['regulyarnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsRegular',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['neregulyarnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsIrregular',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['obilnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsAbundant',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['umerennyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsModerate',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['skudnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsScanty',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['boleznennyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsPainful',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['bezboleznennyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsPainless',
															xtype: 'checkbox'
														}
													],
													bodyStyle: 'padding: 5px;',
													xtype: 'fieldset'
												}
											],
											bodyStyle: 'padding: 5px;',
											xtype: 'fieldset'
										},
										{
											autoHeight: true,
											style: 'padding: 0px;',
											title: lang['invalidnost'],
											width: 600,
											items: [
												{
													comboSubject: 'InvalidType',
													fieldLabel: lang['invalidnost'],
													hiddenName: 'InvalidType_id',
													lastQuery: '',
													xtype: 'swcommonsprcombo'
												},
												{
													fieldLabel: lang['data_ustanovleniya'],
													name: 'AssessmentHealth_setDT',
													xtype: 'swdatefield'
												},
												{
													fieldLabel: lang['data_poslednego_osvidetelstvovaniya'],
													name: 'AssessmentHealth_reExamDT',
													xtype: 'swdatefield'
												},
												{
													comboSubject: 'InvalidDiagType',
													fieldLabel: lang['zabolevaniya_obuslovivshie_vozniknovenie_invalidnosti'],
													hiddenName: 'InvalidDiagType_id',
													lastQuery: '',
													width: 300,
													xtype: 'swcommonsprcombo'
												},
												{
													autoHeight: true,
													style: 'padding: 0px;',
													title: lang['vidyi_narusheniy'],
													width: 580,
													items: [
														{
															boxLabel: lang['umstvennyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMental',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['drugie_psihologicheskie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsOtherPsych',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['yazyikovyie_i_rechevyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsLanguage',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['sluhovyie_i_vestibulyarnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsVestibular',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['zritelnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsVisual',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['vistseralnyie_i_metabolicheskie_rasstroystva_pitaniya'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMeals',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['dvigatelnyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsMotor',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['uroduyuschie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsDeform',
															xtype: 'checkbox'
														},
														{
															boxLabel: lang['obschie_i_generalizovannyie'],
															hideLabel: true,
															name: 'AssessmentHealth_IsGeneral',
															xtype: 'checkbox'
														}
													],
													bodyStyle: 'padding: 5px;',
													xtype: 'fieldset'
												},
												{
													autoHeight: true,
													style: 'padding: 0px;',
													title: lang['individualnaya_programma_reabilitatsii_rebenka_invalida'],
													width: 580,
													items: [
														{
															fieldLabel: lang['data_naznacheniya'],
															name: 'AssessmentHealth_ReabDT',
															xtype: 'swdatefield'
														},
														{
															comboSubject: 'RehabilitEndType',
															fieldLabel: lang['vyipolnenie_na_moment_dispanserizatsii'],
															hiddenName: 'RehabilitEndType_id',
															lastQuery: '',
															xtype: 'swcommonsprcombo'
														}
													],
													bodyStyle: 'padding: 5px;',
													xtype: 'fieldset'
												}
											],
											bodyStyle: 'padding: 5px;',
											xtype: 'fieldset'
										},
										{
											fieldLabel: 'Подозрение на ЗНО',
											hiddenName: 'EvnPLDispOrp_IsSuspectZNO',
											id: 'EPLDO13SEF_EvnPLDispOrp_IsSuspectZNO',
											width: 100,
											xtype: 'swyesnocombo',
											listeners:{
												'change':function (combo, newValue, oldValue) {
													var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
													var index = combo.getStore().findBy(function (rec) {
														return (rec.get(combo.valueField) == newValue);
													});
													combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

													if (base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2) {
														Ext.getCmp('EPLDO13SEF_PrintKLU').enable();
														Ext.getCmp('EPLDO13SEF_PrintOnko').enable();
													} else {
														Ext.getCmp('EPLDO13SEF_PrintKLU').disable();
														Ext.getCmp('EPLDO13SEF_PrintOnko').disable();
													}
												},
												'select':function (combo, record, idx) {
													if (record.get('YesNo_id') == 2) {
														Ext.getCmp('EPLDO13SEF_Diag_spid').showContainer();
														Ext.getCmp('EPLDO13SEF_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
													} else {
														Ext.getCmp('EPLDO13SEF_Diag_spid').setValue('');
														Ext.getCmp('EPLDO13SEF_Diag_spid').hideContainer();
														Ext.getCmp('EPLDO13SEF_Diag_spid').setAllowBlank(true);
													}
												}
											}
										}, 
										{
											fieldLabel: 'Подозрение на диагноз',
											hiddenName: 'Diag_spid',
											id: 'EPLDO13SEF_Diag_spid',
											additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
											baseFilterFn: function(rec){
												if(typeof rec.get == 'function') {
													return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
												} else if (rec.attributes && rec.attributes.Diag_Code) {
													return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
												} else {
													return true;
												}
											},
											width: 300,
											xtype: 'swdiagcombo'
										},
										{
											comboSubject: 'ProfVaccinType',
											fieldLabel: lang['provedenie_profilakticheskih_privivok'],
											hiddenName: 'ProfVaccinType_id',
											lastQuery: '',
											width: 300,
											xtype: 'swcommonsprcombo'
										},
										{
											fieldLabel: lang['gruppa_zdorovya'],
											hiddenName: 'HealthKind_id',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													var base_form = win.findById('EvnPLDispOrp13SecEditForm').getForm();
													if (
														!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
														&& base_form.findField('HealthKind_id').getValue() != 1
														&& base_form.findField('HealthKind_id').getValue() != 2
													) {
														win.DispAppointPanel.expand();
														win.DispAppointPanel.enable();
													} else {
														win.DispAppointPanel.collapse();
														win.DispAppointPanel.disable();
													}
												}
											},
											loadParams: {params: {where: ' where HealthKind_Code <= 5'}},
											xtype: 'swhealthkindcombo'
										}
									]
								},
								// назначения
								win.DispAppointPanel,
								// Справка о стоимости лечения
								win.CostPrintPanel
							],
							keys: [{
								alt: true,
								fn: function(inp, e) {
									switch (e.getKey())
									{
										case Ext.EventObject.C:
											if (this.action != 'view')
											{
												this.doSave(null, false);
											}
											break;

										case Ext.EventObject.G:
											this.printEvnPLDispOrp();
											break;

										case Ext.EventObject.J:
											this.hide();
											break;
									}
								},
								key: [ Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J ],
								scope: this,
								stopEvent: true
							}],
							reader: new Ext.data.JsonReader({
								success: Ext.emptyFn
							}, [
								{ name: 'EvnPLDispOrp_id' },
								{ name: 'EvnPLDispOrp_IsPaid' },
								{ name: 'EvnPLDispOrp_IndexRep' },
								{ name: 'EvnPLDispOrp_IndexRepInReg' },
								{ name: 'EvnPLDispOrp_fid' },
								{ name: 'EvnPLDispOrp_IsBud' },
								{ name: 'EvnPLDispOrp_IsFinish' },
								{ name: 'ChildStatusType_id' },
								{ name: 'PersonEvn_id' },
								{ name: 'DispClass_id' },
								{ name: 'PayType_id' },
								{ name: 'Lpu_mid' },
								{ name: 'EvnPLDispOrp_IsMobile' },
								{ name: 'EvnPLDispOrp_IsOutLpu' },
								{ name: 'EvnPLDispOrp_firSetDate' },
								{ name: 'EvnPLDispOrp_consDate' },
								{ name: 'AssessmentHealth_Weight' },
								{ name: 'AssessmentHealth_Height' },
								{ name: 'AssessmentHealth_Head' },
								{ name: 'WeightAbnormType_YesNo' },
								{ name: 'WeightAbnormType_id' },
								{ name: 'HeightAbnormType_YesNo' },
								{ name: 'HeightAbnormType_id' },
								{ name: 'AssessmentHealth_Gnostic' },
								{ name: 'AssessmentHealth_Motion' },
								{ name: 'AssessmentHealth_Social' },
								{ name: 'AssessmentHealth_Speech' },
								{ name: 'AssessmentHealth_P' },
								{ name: 'AssessmentHealth_Ax' },
								{ name: 'AssessmentHealth_Fa' },
								{ name: 'AssessmentHealth_Ma' },
								{ name: 'AssessmentHealth_Me' },
								{ name: 'AssessmentHealth_Years' },
								{ name: 'AssessmentHealth_Month' },
								{ name: 'AssessmentHealth_IsRegular' },
								{ name: 'AssessmentHealth_IsIrregular' },
								{ name: 'AssessmentHealth_IsAbundant' },
								{ name: 'AssessmentHealth_IsModerate' },
								{ name: 'AssessmentHealth_IsScanty' },
								{ name: 'AssessmentHealth_IsPainful' },
								{ name: 'AssessmentHealth_IsPainless' },
								{ name: 'InvalidType_id' },
								{ name: 'AssessmentHealth_setDT' },
								{ name: 'AssessmentHealth_reExamDT' },
								{ name: 'InvalidDiagType_id' },
								{ name: 'AssessmentHealth_IsMental' },
								{ name: 'AssessmentHealth_IsOtherPsych' },
								{ name: 'AssessmentHealth_IsLanguage' },
								{ name: 'AssessmentHealth_IsVestibular' },
								{ name: 'AssessmentHealth_IsVisual' },
								{ name: 'AssessmentHealth_IsMeals' },
								{ name: 'AssessmentHealth_IsMotor' },
								{ name: 'AssessmentHealth_IsDeform' },
								{ name: 'AssessmentHealth_IsGeneral' },
								{ name: 'AssessmentHealth_ReabDT' },
								{ name: 'RehabilitEndType_id' },
								{ name: 'ProfVaccinType_id' },
								{ name: 'HealthKind_id' },
								{ name: 'NormaDisturbanceType_id' },
								{ name: 'NormaDisturbanceType_eid' },
								{ name: 'NormaDisturbanceType_uid' },
								{ name: 'EvnCostPrint_setDT' },
								{ name: 'EvnCostPrint_IsNoPrint' },
								{ name: 'EvnPLDispOrp_IsSuspectZNO' },
								{ name: 'Diag_spid' }
							]),
							region: 'center',
							url: '/?c=EvnPLDispOrp13&m=saveEvnPLDispOrpSec'
						})
					]
				})
			],
			buttons: [{
				handler: function() {
					this.doSave(null, false);
				}.createDelegate(this),				
				iconCls: 'save16',
				id: 'EPLDO13SEFSaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDO13SEFPrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var usluga_grid = Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid');
					var vizit_grid = Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid');
					if ( usluga_grid.getStore().getCount() > 0 )
					{
						usluga_grid.focus();
						usluga_grid.getSelectionModel().selectFirstRow();
						usluga_grid.getView().focusRow(0);
						return true;
					}
					if ( vizit_grid.getStore().getCount() > 0 )
					{
						vizit_grid.focus();
						vizit_grid.getSelectionModel().selectFirstRow();
						vizit_grid.getView().focusRow(0);
						return true;
					}					
					Ext.getCmp('EPLDO13SEFIsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispOrp();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDO13SEFPrintButton',
				tabIndex: 2407,
				text: BTN_FRMPRINT
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDO13SEF_PrintKLU',
				tabIndex: 2408,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDO13SEF_PrintOnko',
				tabIndex: 2409,
				text: 'Печать выписки по онкологии'
			}/*, {
				handler: function() {
					var current_window = Ext.getCmp('EvnPLDispOrp13SecEditWindow');
					var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
					var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
					var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
					var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
					var params = {
						onHide: function() {
							this.focus();
						}.createDelegate(this),
						Person_Birthday: person_birthday,
						Person_Firname: person_firname,
						Person_Secname: person_secname,
						Person_Surname: person_surname,
						Person_id: current_window.findById('EPLDO13SEFPerson_id').getValue(),
						Server_id: current_window.findById('EPLDO13SEFServer_id').getValue(),
						isOrpDisp: true
					};
					
					if (getWnd('swPersonDispHistoryWindow').isVisible())
					{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}

					getWnd('swPersonDispHistoryWindow').show(params);
					
				}.createDelegate(this),
				id: 'EPLDO13SEFDispButton',
				tabIndex: 2408,
				text: lang['dispansernyiy_uchet']
			}*/, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDO13SEFCancelButton',
				onTabAction: function() {
					//Ext.getCmp('EPLDO13SEFAttachTypeCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					//Ext.getCmp('EPLDO13SEFAttachTypeCombo').focus(true, 200);
				},
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispOrp13SecEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispOrp13SecEditWindow');
			var tabbar = current_window.findById('EPLDO13SEFEvnPLTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					current_window.doSave();
					break;

				case Ext.EventObject.J:
					current_window.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 570,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	openEvnVizitDispOrpEditWindow: function(action) {
        var current_window = this;
		var base_form = this.findById('EvnPLDispOrp13SecEditForm').getForm();

		if (getWnd('swEvnVizitDispOrp13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');
		var age = current_window.PersonInfoPanel.getFieldValue('Person_Age');

		var selected_record = current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = current_window.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispOrp_id = null;
			params.Record_Status = 0;
			params['EvnPLDispOrp_id'] = base_form.findField('EvnPLDispOrp_id').getValue();
			params['Server_id'] = base_form.findField('Server_id').getValue();
			params['PersonEvn_id'] = base_form.findField('PersonEvn_id').getValue();
			
			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{			
			if (!current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispOrp_id == null || selected_record.data.EvnVizitDispOrp_id == '' )
				return;
			
			params = selected_record.data;
			params['EvnPLDispOrp_id'] = base_form.findField('EvnPLDispOrp_id').getValue();
			
			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}
		
		var show_params = {
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);					
				});

				current_window.EvnPLDispOrpVizitPanel.getStore().load({ 
					params: { EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_id').getValue() },
					callback: function() {
						if ( add_flag == true )
						{
							// добавляем соответствующую строку в грид "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
							if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
								data[0].FormDataJSON = null;

								if ( current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
									&& Ext.isEmpty(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispOrp_id'))
								) {
									current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
								}

								current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
							}
						}
						else {
							// ищем соответствующую строку в гриде "Диагнозы и рекомендации по результатам диспансеризации / профосмотра", если нет, то добавляем, иначе редактируем
							if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
								index = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == data[0].EvnVizitDispOrp_id; });
								if (index == -1)
								{
									data[0].FormDataJSON = null;

									if ( current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
										&& Ext.isEmpty(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispOrp_id'))
									) {
										current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
									}

									current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
								} else {
									var record = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
									var jsonData = Ext.util.JSON.decode(record.get('FormDataJSON'));

									if ( !Ext.isEmpty(jsonData.EvnVizitDisp_IsFirstTime) && (data[0].DopDispDiagType_id == 1 || data[0].DopDispDiagType_id == 2) ) {
										jsonData.EvnVizitDisp_IsFirstTime = data[0].DopDispDiagType_id;
										record.set('FormDataJSON', Ext.util.JSON.encode(jsonData));
									}

									record.set('Diag_id', data[0].Diag_id);
									record.set('Diag_Name', data[0].Diag_Name);
									record.set('UslugaComplex_Name', data[0].UslugaComplex_Name);
									record.commit();
								}
							} else {
								index = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == data[0].EvnVizitDispOrp_id; });
								if (index != -1)
								{
									var record = current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
									current_window.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
								}			
							}
						}

						if (getRegionNick() == 'buryatiya') {
							if (data[0].Diag_Code && data[0].Diag_Code == 'Z03.1') {
								base_form.findField('EvnPLDispOrp_IsSuspectZNO').setValue(2);
							} else {
								base_form.findField('EvnPLDispOrp_IsSuspectZNO').clearValue();
							}
							base_form.findField('EvnPLDispOrp_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispOrp_IsSuspectZNO'), base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue());
						}

						current_window.reloadDopDispInfoConsentGrid();
					}
				});

        		return true;
        	},			
        	formParams: params,
        	onHide: function() {
				if (current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore().getCount()) {
					current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getSelectionModel().selectFirstRow();
				}			
			},
			ownerWindow: current_window,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Year: this.Year,
			Sex_id: sex_id,
			Person_Age: age,
			max_date: max_date
		};
		
		if (base_form.findField('EvnPLDispOrp_id').getValue() > 0) {
			getWnd('swEvnVizitDispOrp13SecEditWindow').show(show_params);
		} else {
			current_window.doSave(function() {
				show_params.formParams['EvnPLDispOrp_id'] = base_form.findField('EvnPLDispOrp_id').getValue();
				getWnd('swEvnVizitDispOrp13SecEditWindow').show(show_params);
			}, false);
		}

	},
	openEvnUslugaDispOrpEditWindow: function(action) {
        var current_window = this;
		var base_form = this.findById('EvnPLDispOrp13SecEditForm').getForm();

		if (getWnd('swEvnUslugaDispOrp13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = current_window.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = current_window.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = current_window.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = current_window.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');
		var age = current_window.PersonInfoPanel.getFieldValue('Person_Age');
		
		if (current_window.action == 'add') {
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			Ext.getCmp('EPLDO13SEFEvnPLDispOrp_consDate').setValue(set_date);
		} else {
			var set_date = Date.parseDate(Ext.getCmp('EPLDO13SEFEvnPLDispOrp_consDate').getValue(), 'd.m.Y');
		}

		if (action == 'add')
		{
			params = current_window.params;

			params.EvnUslugaDispOrp_id = swGenTempId(this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore(), 'EvnUslugaDispOrp_id');
			params.Record_Status = 0;
			
			var usedUslugaComplexCodeList = [];
			current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
				return;

			params = selected_record.data;
			
			var usedUslugaComplexCodeList = [];
			current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}
		
        getWnd('swEvnUslugaDispOrp13SecEditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
        		{
					// удаляем пустую строку если она есть					
					if ( current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
							current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().removeAll();
					}
					
					current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().clearFilter();
					current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().loadData(data, add_flag);
					current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == data[0].EvnUslugaDispOrp_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().getAt(index);

					for (i = 0; i < usluga_fields.length; i++)
					{
						record.set(usluga_fields[i], data[0][usluga_fields[i]]);
					}

					record.commit();
				}
				
				current_window.reloadDopDispInfoConsentGrid();
				
        		return true;
        	},
        	formParams: params,
        	onHide: function() {
				current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getSelectionModel().selectFirstRow();
				current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getView().focusRow(0);				
			},
        	ownerWindow: current_window,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
		    Person_id: person_id,
		    Person_Birthday: person_birthday,
			Person_Surname: person_surname,
		    Person_Firname: person_firname,
			Person_Secname: person_secname,
			Sex_id: sex_id,
			Person_Age: age,
			set_date: set_date,
			UslugaComplex_Date: current_window.findById('EPLDO13SEFEvnPLDispOrp_consDate').getValue()
		});
	},
	openPersonCureHistoryWindow: function() {
		var current_window = this;
		var form = current_window.findById('EvnPLDispOrp13SecEditForm');

		if (getWnd('swPersonCureHistoryWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_prosmotra_istorii_lecheniya_cheloveka_uje_otkryito']);
			return false;
		}

		var person_id = form.findById('EPLDO13SEFPerson_id').getValue();
		var server_id = form.findById('EPLDO13SEFServer_id').getValue();

		getWnd('swPersonCureHistoryWindow').show({
			onHide: function() {
				if (current_window.action == 'view')
				{
					form.buttons[1].focus();
				}
			},
			Person_id: person_id,
			Server_id: server_id
		});
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},
	plain: true,
	printEvnPLDispOrp: function() {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			this.doSave(null, true);
		}
		else if (this.action == 'view')
		{
			var evn_pl_id = this.findById('EPLDO13SEFEvnPLDispOrp_id').getValue();
			var server_id = this.findById('EPLDO13SEFServer_id').getValue();

			window.open(C_EPLDO13_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	printKLU: function() {
		var win = this;
		var base_form = this.findById('EvnPLDispOrp13SecEditForm').getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispOrp_id').getValue();
			printBirt({
				'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id, 
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave( print );
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printOnko: function() {
		var win = this;
		var base_form = this.findById('EvnPLDispOrp13SecEditForm').getForm();

		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispOrp_id').getValue();
			printBirt({
				'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave( print );
		}
		else if ( 'view' == this.action ) {
			print();
		}
	},
	resizable: true,
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnPLDispOrp13SecEditForm').getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispOrp_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	show: function() {
		sw.Promed.swEvnPLDispOrp13SecEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;

		this.restore();
		this.center();
		this.maximize();
		
		// this.findById('EPLDO13SEFEvnPLTabbar').setActiveTab(4);

		var form = this.findById('EvnPLDispOrp13SecEditForm');
		var base_form = form.getForm();
		base_form.reset();
		base_form.findField('HealthKind_id').fireEvent('change', base_form.findField('HealthKind_id'), base_form.findField('HealthKind_id').getValue());
		this.checkForCostPrintPanel();

		base_form.findField('EvnPLDispOrp_RepFlag').hideContainer();

		base_form.findField('InvalidType_id').getStore().filterBy(function(rec) {
			return rec.get('InvalidType_Code').toString().inlist([ '1', '2', '3' ]);
		});

		base_form.findField('HeightAbnormType_id').setAllowBlank(true);
		base_form.findField('WeightAbnormType_id').setAllowBlank(true);
		Ext.getCmp('EPLDO13SEF_PrintKLU').disable();
		Ext.getCmp('EPLDO13SEF_PrintOnko').disable();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (!arguments[0] || ((Ext.isEmpty(arguments[0].DispClass_id) || Ext.isEmpty(arguments[0].EvnPLDispOrp_fid)) && Ext.isEmpty(arguments[0].EvnPLDispOrp_id)))
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
			return false;
		}

		form.getForm().setValues(arguments[0]);

		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}

		if (arguments[0].Year)
		{
			this.Year = arguments[0].Year;
		}
		else 
		{
			this.Year = null;
		}
		
		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispOrp_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispOrp_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function (response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
							this.action = 'view';
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(lang['vnimanie'], response_obj.Alert_Msg);
						}
					}

					//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
					this.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			this.onShow();
		}
	},
	
	onShow: function(){
		
		var form = this.findById('EvnPLDispOrp13SecEditForm');
		var base_form = form.getForm();
		var current_window = this;
		
		var isbud_combo = this.findById('EPLDO13SEFIsBudCombo');
		var isfinish_combo = this.findById('EPLDO13SEFIsFinishCombo');

		var evnpldispdop_id = form.findById('EPLDO13SEFEvnPLDispOrp_id').getValue();
		var person_id = form.findById('EPLDO13SEFPerson_id').getValue();
		var server_id = form.findById('EPLDO13SEFServer_id').getValue();
		
		
		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrp13SecEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		this.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		this.EvnDiagAndRecomendationPanel.getGrid().getStore().removeAll();
		this.DispAppointGrid.getGrid().getStore().removeAll();

		this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDO13SEFEvnVizitDispOrpGrid'));
		this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDO13SEFEvnUslugaDispOrpGrid'));
		this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[3].disable();		

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				var sex_id = current_window.PersonInfoPanel.getFieldValue('Sex_id');

				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					current_window.findById('EPLDO13SEF_menarhe').hide();
					current_window.findById('EPLDO13SEF_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					current_window.findById('EPLDO13SEF_menarhe').show();
					current_window.findById('EPLDO13SEF_menses').show();
				}
				
				if ( sex_id == 2 ) {
					// скрыть поля для мальчиков
					base_form.findField('AssessmentHealth_Fa').hideContainer();
				}
				else {
					base_form.findField('AssessmentHealth_Fa').showContainer();
				}
				
				switch ( current_window.action ) {
					case 'add':
						current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_dobavlenie']);
						current_window.enableEdit(true);

						setCurrentDateTime({
							callback: function(date) {
								base_form.findField('EvnPLDispOrp_consDate').fireEvent('change', base_form.findField('EvnPLDispOrp_consDate'), date);

								base_form.findField('HeightAbnormType_YesNo').setValue(1);
								base_form.findField('WeightAbnormType_YesNo').setValue(1);

								base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
								base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());

								loadMask.hide();
							},
							dateField: base_form.findField('EvnPLDispOrp_consDate'),
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							windowId: current_window.id
						});
						
						// прогрузить данные с 1 этапа
						Ext.Ajax.request({
							failure: function(response, options) {
								sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_dannyih_kartyi_1_etapa_proizoshla_oshibka'], function() {
									current_window.hide();
								});
								return false;
							},
							params: {
								EvnPLDispOrp_id: base_form.findField('EvnPLDispOrp_fid').getValue()
							},
							success: function(response, options) {
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									
									if (answer && answer[0]) {
										answer = answer[0];
									}
									
									if (answer.EvnPLDispOrp_id)
									{
										base_form.findField('PayType_id').setValue(answer.PayType_id);
										base_form.findField('ChildStatusType_id').setValue(answer.ChildStatusType_id);
										base_form.findField('EvnPLDispOrp_firSetDate').setValue(answer.EvnPLDispOrp_setDate);
										base_form.findField('EvnPLDispOrp_firSetDate').fireEvent('change', base_form.findField('EvnPLDispOrp_firSetDate'), base_form.findField('EvnPLDispOrp_firSetDate').getValue());
										base_form.findField('AssessmentHealth_Weight').setValue(answer.AssessmentHealth_Weight);
										base_form.findField('AssessmentHealth_Height').setValue(answer.AssessmentHealth_Height);
										base_form.findField('AssessmentHealth_Head').setValue(answer.AssessmentHealth_Head);
										base_form.findField('WeightAbnormType_YesNo').setValue(answer.WeightAbnormType_YesNo);
										base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
										base_form.findField('WeightAbnormType_id').setValue(answer.WeightAbnormType_id);
										base_form.findField('HeightAbnormType_YesNo').setValue(answer.HeightAbnormType_YesNo);
										base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
										base_form.findField('HeightAbnormType_id').setValue(answer.HeightAbnormType_id);
										base_form.findField('AssessmentHealth_Gnostic').setValue(answer.AssessmentHealth_Gnostic);
										base_form.findField('AssessmentHealth_Motion').setValue(answer.AssessmentHealth_Motion);
										base_form.findField('AssessmentHealth_Social').setValue(answer.AssessmentHealth_Social);
										base_form.findField('AssessmentHealth_Speech').setValue(answer.AssessmentHealth_Speech);
										base_form.findField('NormaDisturbanceType_id').setValue(answer.NormaDisturbanceType_id);
										base_form.findField('NormaDisturbanceType_uid').setValue(answer.NormaDisturbanceType_uid);
										base_form.findField('NormaDisturbanceType_eid').setValue(answer.NormaDisturbanceType_eid);
										base_form.findField('AssessmentHealth_P').setValue(answer.AssessmentHealth_P);
										base_form.findField('AssessmentHealth_Ax').setValue(answer.AssessmentHealth_Ax);
										base_form.findField('AssessmentHealth_Fa').setValue(answer.AssessmentHealth_Fa);
										base_form.findField('AssessmentHealth_Ma').setValue(answer.AssessmentHealth_Ma);
										base_form.findField('AssessmentHealth_Me').setValue(answer.AssessmentHealth_Me);
										base_form.findField('AssessmentHealth_Years').setValue(answer.AssessmentHealth_Years);
										base_form.findField('AssessmentHealth_Month').setValue(answer.AssessmentHealth_Month);
										base_form.findField('AssessmentHealth_IsRegular').setValue(answer.AssessmentHealth_IsRegular);
										base_form.findField('AssessmentHealth_IsIrregular').setValue(answer.AssessmentHealth_IsIrregular);
										base_form.findField('AssessmentHealth_IsAbundant').setValue(answer.AssessmentHealth_IsAbundant);
										base_form.findField('AssessmentHealth_IsModerate').setValue(answer.AssessmentHealth_IsModerate);
										base_form.findField('AssessmentHealth_IsScanty').setValue(answer.AssessmentHealth_IsScanty);
										base_form.findField('AssessmentHealth_IsPainful').setValue(answer.AssessmentHealth_IsPainful);
										base_form.findField('AssessmentHealth_IsPainless').setValue(answer.AssessmentHealth_IsPainless);
										base_form.findField('InvalidType_id').setValue(answer.InvalidType_id);
										base_form.findField('AssessmentHealth_setDT').setValue(answer.AssessmentHealth_setDT);
										base_form.findField('AssessmentHealth_reExamDT').setValue(answer.AssessmentHealth_reExamDT);
										base_form.findField('InvalidDiagType_id').setValue(answer.InvalidDiagType_id);
										base_form.findField('AssessmentHealth_IsMental').setValue(answer.AssessmentHealth_IsMental);
										base_form.findField('AssessmentHealth_IsOtherPsych').setValue(answer.AssessmentHealth_IsOtherPsych);
										base_form.findField('AssessmentHealth_IsLanguage').setValue(answer.AssessmentHealth_IsLanguage);
										base_form.findField('AssessmentHealth_IsVestibular').setValue(answer.AssessmentHealth_IsVestibular);
										base_form.findField('AssessmentHealth_IsVisual').setValue(answer.AssessmentHealth_IsVisual);
										base_form.findField('AssessmentHealth_IsMeals').setValue(answer.AssessmentHealth_IsMeals);
										base_form.findField('AssessmentHealth_IsMotor').setValue(answer.AssessmentHealth_IsMotor);
										base_form.findField('AssessmentHealth_IsDeform').setValue(answer.AssessmentHealth_IsDeform);
										base_form.findField('AssessmentHealth_IsGeneral').setValue(answer.AssessmentHealth_IsGeneral);
										base_form.findField('AssessmentHealth_ReabDT').setValue(answer.AssessmentHealth_ReabDT);
										base_form.findField('RehabilitEndType_id').setValue(answer.RehabilitEndType_id);
										base_form.findField('ProfVaccinType_id').setValue(answer.ProfVaccinType_id);
										base_form.findField('HealthKind_id').setValue(answer.HealthKind_id);
									}
								}
							},
							url: '/?c=EvnPLDispOrp13&m=loadEvnPLDispOrpEditForm'
						});
						
						base_form.findField('EvnPLDispOrp_IsMobile').fireEvent('check', base_form.findField('EvnPLDispOrp_IsMobile'), base_form.findField('EvnPLDispOrp_IsMobile').getValue());

						current_window.findById('EPLDO13SEFIsFinishCombo').setValue(1);
						current_window.loadUslugaComplex();
						
						base_form.findField('Diag_spid').setContainerVisible(false);
						base_form.findField('Diag_spid').setAllowBlank(true);
					break;

					case 'edit':
					case 'view':
						base_form.load({
							failure: function() {
								swEvnPLDispOrp13SecEditWindow.hide();
								loadMask.hide();
							},
							params: {
								EvnPLDispOrp_id: evnpldispdop_id,
								archiveRecord: current_window.archiveRecord
							},
							success: function() {
								if ( current_window.action == 'edit' ) {
									current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_redaktirovanie']);
									current_window.enableEdit(true);
								}
								else {
									current_window.setTitle(lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_prosmotr']);
									current_window.enableEdit(false);
								}
								loadMask.hide();

								if (!Ext.isEmpty(base_form.findField('EvnPLDispOrp_IsSuspectZNO')) && base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2) {
									Ext.getCmp('EPLDO13SEF_PrintKLU').enable();
									Ext.getCmp('EPLDO13SEF_PrintOnko').enable();
								} else {
									Ext.getCmp('EPLDO13SEF_PrintKLU').disable();
									Ext.getCmp('EPLDO13SEF_PrintOnko').disable();
								}

								if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispOrp_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()) > 0 ) {
									base_form.findField('EvnPLDispOrp_RepFlag').showContainer();

									if ( parseInt(base_form.findField('EvnPLDispOrp_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispOrp_IndexRepInReg').getValue()) ) {
										base_form.findField('EvnPLDispOrp_RepFlag').setValue(true);
									}
									else {
										base_form.findField('EvnPLDispOrp_RepFlag').setValue(false);
									}
								}

								base_form.findField('HealthKind_id').fireEvent('change', base_form.findField('HealthKind_id'), base_form.findField('HealthKind_id').getValue());
								current_window.checkForCostPrintPanel();

								base_form.findField('EvnPLDispOrp_firSetDate').fireEvent('change', base_form.findField('EvnPLDispOrp_firSetDate'), base_form.findField('EvnPLDispOrp_firSetDate').getValue());
								base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
								base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());

								var lpucombo = base_form.findField('Lpu_mid');
								if (!Ext.isEmpty(lpucombo.getValue())) {
									lpucombo.getStore().load({
										params: {
											OrgType: 'lpu',
											Lpu_oid: lpucombo.getValue()
										},
										callback: function()
										{
											lpucombo.setValue(lpucombo.getValue());
											lpucombo.focus(true, 500);
											lpucombo.fireEvent('change', lpucombo);
										}
									});
								}
								
								base_form.findField('EvnPLDispOrp_IsMobile').fireEvent('check', base_form.findField('EvnPLDispOrp_IsMobile'), base_form.findField('EvnPLDispOrp_IsMobile').getValue());
				
								// загрузка грида осмотров
								current_window.EvnPLDispOrpVizitPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: evnpldispdop_id },
									callback: function() {
										current_window.reloadDopDispInfoConsentGrid();
										if ( Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('EPLDO13SEFEvnVizitDispOrpGrid'));
									}
								});

								// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
								current_window.EvnDiagAndRecomendationPanel.loadData({
									params: {
										EvnPLDispOrp_id: evnpldispdop_id
									},
									globalFilters: {
										EvnPLDispOrp_id: evnpldispdop_id
									},
									noFocusOnLoad: true
								});

								// загрузка грида обследований
								current_window.EvnUslugaDispOrpPanel.getStore().load({ 
									params: { EvnPLDispOrp_id: evnpldispdop_id },
									callback: function() {
										current_window.reloadDopDispInfoConsentGrid();
										if ( Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
											LoadEmptyRow(Ext.getCmp('EPLDO13SEFEvnUslugaDispOrpGrid'));
									}
								});

								if (getRegionNick() != 'kz') {
									current_window.DispAppointGrid.loadData({
										params: {EvnPLDisp_id: evnpldispdop_id, object: 'EvnPLDispOrp'},
										globalFilters: {EvnPLDisp_id: evnpldispdop_id},
										noFocusOnLoad: true
									});
								}

								if ( !base_form.findField('ChildStatusType_id').disabled ) {
									base_form.findField('ChildStatusType_id').focus(false);
								}
								else {
									current_window.buttons[1].focus();
								}

								current_window.loadUslugaComplex();
								
								//Проверяем доступность редактирования
								Ext.Ajax.request({
									failure: function() {
										sw.swMsg.alert(lang['oshibka'], lang['oshibka_proverki_vozmojnosti_redaktirovaniya_formyi'], function() { current_window.hide(); } );
									},
									params: {
										Evn_id: evnpldispdop_id
									},
									success: function(response, options) {
										if ( !Ext.isEmpty(response.responseText) ) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											if ( response_obj.success == false ) {
												sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_proverki_vozmojnosti_redaktirovaniya_formyi']);
												current_window.action = 'view';
												current_window.enableEdit(false);
												current_window.findById('EPLDO13SEFEvnVizitDispOrpGrid').getTopToolbar().items.items[0].disable();
												current_window.findById('EPLDO13SEFEvnUslugaDispOrpGrid').getTopToolbar().items.items[0].disable();
											}
										}
									}.createDelegate(this),
									url: '/?c=Evn&m=CommonChecksForEdit'
								});
								
								base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispOrp_IsSuspectZNO').getValue() == 2);
								base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispOrp_IsSuspectZNO') != 2);
								var diag_spid = base_form.findField('Diag_spid').getValue();
								if (diag_spid) {
									base_form.findField('Diag_spid').getStore().load({
										callback:function () {
											base_form.findField('Diag_spid').getStore().each(function (rec) {
												if (rec.get('Diag_id') == diag_spid) {
													base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
												}
											});
										},
										params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
									});
								}
							},
							url: '/?c=EvnPLDispOrp13&m=loadEvnPLDispOrpEditForm'
						});
					break;
				}

				form.getForm().clearInvalid();
				current_window.doLayout();
			} 
		});
	},
	
	title: lang['karta_dispanserizatsii_nesovershennoletnego'],
	width: 800
});