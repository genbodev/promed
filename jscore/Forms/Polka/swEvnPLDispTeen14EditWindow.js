/**
* swEvnPLDispTeen14EditWindow - окно редактирования/добавления талона по диспансеризации подростков 14ти лет
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2009 - 2011 Swan Ltd.
* @author		Pshenitcyn Ivan aka IVP (ipshon@gmail.com)
* @version		01.07.2011
* @comment		Префикс для id компонентов EPLDT14EF (EvnPLDispTeen14EditForm)
*				tabIndex: TABINDEX_EPLDT14EF
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLDispTeen14_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              Server_id - ?
*
*
* Использует: окно просмотра истории болезни (swPersonCureHistoryWindow)
*             окно просмотра льгот (swPersonPrivilegeViewWindow)
*             окно редактирования человека (swPersonEditWindow)
*             окно добавления/редактирования услуги по ДД 14 (swEvnUslugaDispTeen14EditWindow)
*             окно добавления/редактирования посещения по ДД 14 (swEvnVizitDispTeen14EditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeen14EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeen14EditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeen14EditWindow.js',
	
	deleteEvnVizitDispTeen14: function() {
		var grid = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid');
		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record || !selected_record.get('EvnVizitDispTeen14_id') )
		{
			return false;
		}

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) 
			{
				if ( 'yes' == buttonId )
				{
					if ( selected_record.get('Record_Status') == 0 )
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();

						grid.getStore().filterBy(function(record) {
							if ( record.get('Record_Status') != 3 )
							{
								return true;
							}
						});
					}

					if ( grid.getStore().getCount() == 0 )
					{
						var data = new Object();
						var load_data = new Object();

						grid.getStore().fields.eachKey(function(key, item) {
							data[key] = null;
						});

						grid.getStore().loadData([ data ], true);
					}

					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
					this.refreshHealthGrid();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osmotr_vracha-spetsialista'],
			title: lang['vopros']
		});
	},
	deleteEvnUslugaDispTeen14: function() {
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnuslugaDispTeen14_grid = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid');

					if (!evnuslugaDispTeen14_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnuslugaDispTeen14_grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						evnuslugaDispTeen14_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnuslugaDispTeen14_grid.getStore().filterBy(function(record) {
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}
					
					if ( evnuslugaDispTeen14_grid.getStore().getCount() == 0 )
					{
						var data = new Object();
						var load_data = new Object();

						evnuslugaDispTeen14_grid.getStore().fields.eachKey(function(key, item) {
							data[key] = null;
						});

						evnuslugaDispTeen14_grid.getStore().loadData([ data ], true);
					}

					evnuslugaDispTeen14_grid.getView().focusRow(0);
					evnuslugaDispTeen14_grid.getSelectionModel().selectFirstRow();

					/*if (evnuslugaDispTeen14_grid.getStore().getCount() == 0)
					{
						evnuslugaDispTeen14_grid.getTopToolbar().items.items[1].disable();
						evnuslugaDispTeen14_grid.getTopToolbar().items.items[2].disable();
						evnuslugaDispTeen14_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnuslugaDispTeen14_grid.getView().focusRow(0);
						evnuslugaDispTeen14_grid.getSelectionModel().selectFirstRow();
					}
					
					if ( evnuslugaDispTeen14_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnuslugaDispTeen14_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_laboratornoe_issledovanie'],
			title: lang['vopros']
		})
	},
	draggable: true,
	doSave: function(print, check_finish, print_blank, print_other) {
		var current_window = this;
		var evnplDispTeen14_form = current_window.findById('EvnPLDispTeen14EditForm');
		var evnvizitDispTeen14_grid = current_window.findById('EPLDT14EF_EvnVizitDispTeen14Grid');
		var evnuslugaDispTeen14_grid = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid');
		var i = 0;

		if ( !evnplDispTeen14_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('EPLDT14EF_IsFinishCombo').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// проверка на заполненность всех услуг и посещений, если пытаются закрыть талон по ДД
		var sex_id = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Age');

		var ter_date = null;		
		// услуги
		max_count = 5;				

		var count = 0;
		evnuslugaDispTeen14_grid.getStore().each(function(rec) {
			if ( rec.get('Record_Status') != 3 && rec.get('StudyType_id') == 1 )
				count++;
		});
		
		var usluga_is_full = true;//(count >= max_count);
		
		// посещения
		max_count = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 9 : 7);

		var count = 0;
		var PediatrDate = new Date();
		var MaxDate = new Date();
		evnvizitDispTeen14_grid.getStore().each(function(rec) {
			if ( rec.get('Record_Status') != 3 ) {
				// Для Уфы все осмотры являются обязательным
				if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
					count++;
				}
				// Для остальных регионов осмотр врача-стоматолога не считается обязательным
				else if ( rec.get('Teen14DispSpecType_id') != 9 ) {
					count++;
				}
				if (rec.get('Teen14DispSpecType_id') == 1) {
					PediatrDate = rec.get('EvnVizitDispTeen14_setDate');//берем дату осмотра педиатра
				}
			}
		});
		MaxDate = PediatrDate;
		evnvizitDispTeen14_grid.getStore().each(function(rec) {
				if (MaxDate < rec.get('EvnVizitDispTeen14_setDate'))
					MaxDate = rec.get('EvnVizitDispTeen14_setDate');
		});

		if (PediatrDate < MaxDate)
		{
			alert(lang['data_osmotra_pediatra_ne_mojet_byit_menshe_datyi_osmotra_drugih_spetsialistov']);
			return false;
		}

		PediatrDate = PediatrDate.getFullYear()+'-'+(PediatrDate.getMonth()+1)+'-'+PediatrDate.getDate();

		var spec_is_full = (count >= max_count);

		var health_info_is_full = true;
		evnvizitDispTeen14_grid.getStore().each(function(rec) {			
			var diag_code = rec.get('Diag_Code').substr(0, 1);
			if ( diag_code != 'Z' && rec.get('Diag_id') > 0 && rec.get('Record_Status') != 3 )
			{				
				if (
					!(rec.get('DeseaseFuncType_id') > 0) ||
					!(rec.get('DiagType_id') > 0) ||
					!(rec.get('DispRegistrationType_id') > 0) ||
					!(rec.get('EvnVizitDispTeen14_isFirstDetected') > 0) ||
					!(rec.get('RecommendationsTreatmentType_id') > 0) ||
					!(rec.get('EvnVizitDispTeen14_isVMPRecommented') > 0) ||
					!(rec.get('RecommendationsTreatmentDopType_id') > 0)
				)
					health_info_is_full = false;
			}
		});
			
		if ( Ext.getCmp('EPLDT14EF_IsFinishCombo').getValue() == 2 )
		{
			if ( !spec_is_full || !usluga_is_full )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EPLDT14EF_IsFinishCombo').setValue(1);
						current_window.findById('EPLDT14EF_IsFinishCombo').focus();
						current_window.findById('EPLDT14EF_IsFinishField').setValue(1);
					},
					icon: Ext.Msg.WARNING,
					msg: "Случай не может быть закончен, так как заполнены не все осмотры.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			if ( !health_info_is_full )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EPLDT14EF_IsFinishCombo').focus();
					},
					icon: Ext.Msg.WARNING,
					msg: "Необходимо заполнить информацию по всем полям списка «Диагнозы и рекомендации».",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		else
		{
			if ( spec_is_full && usluga_is_full && check_finish != 2 && health_info_is_full  )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes')
						{
							Ext.getCmp('EPLDT14EF_IsFinishCombo').setValue(2);
							Ext.getCmp('EPLDT14EF_IsFinishField').setValue(2);
							current_window.doSave(print, 2, print_blank);
						}
						else
							current_window.doSave(print, 2, print_blank);
					},
					icon: Ext.MessageBox.QUESTION,
					msg: lang['zapolnenyi_vse_osmotryi_i_issledovaniya_hotite_chtobyi_sluchay_byil_zakonchen'],
					title: lang['vopros']
				});
				return;
			}
		}

		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispTeen14EditWindow'), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		// Функция get_grid_records возвращает записи из store
		function get_grid_records(store, save_trigger)
		{
			var fields = new Array();
			var result = new Array();
			
			store.fields.eachKey(function(key, item) {
				if ( save_trigger == true && key.indexOf('_Name') == -1 && key.indexOf('_Fio') == -1 )
				{
					fields.push(key);
				}
				else if ( save_trigger == false )
				{
					fields.push(key);
				}
			});

			store.clearFilter();
			store.each(function(record) {
				if ( String(record.get('Record_Status')) != "" && (record.get('Record_Status') == 0 || record.get('Record_Status') == 2 || record.get('Record_Status') == 3) )
				{
					var temp_record = new Object();
					for ( i = 0; i < fields.length; i++ )
					{
						if ( save_trigger == true && fields[i].indexOf('Date') != -1 )
						{
							temp_record[fields[i]] = Ext.util.Format.date(record.get(fields[i]), 'd.m.Y');
						}
						else
						{
							temp_record[fields[i]] = record.get(fields[i]);
						}
					}
					result.push(temp_record);
				}
			});

			store.filterBy(function(record) {
				if ( record.get('Record_Status') != 3 )
				{
					return true;
				}
			});

			return result;
		}

		// Собираем данные из гридов
		var params = new Object();		
		params.EvnVizitDispTeen14 = get_grid_records(evnvizitDispTeen14_grid.getStore(), true);
		params.EvnUslugaDispTeen14 = get_grid_records(evnuslugaDispTeen14_grid.getStore(), true);
		params.PediatrDate = PediatrDate;
		//swalert(params.EvnUslugaDispTeen14);
		
		// TODO: Надо делать рефакторинг формы, очень много всего наверчено. Совершенно не понятно почему для одного поля (EvnPLDispTeen14_IsFinish) используется два компонента !!!
		// пока делаю затычку: если значение компонент отображаемого не равен значению скрытого, то тогда в скрытый пишем значение отображаемого (жесть как она есть)
		if (this.findById('EPLDT14EF_IsFinishCombo').getValue()!=this.findById('EPLDT14EF_IsFinishField').getValue()) {
			this.findById('EPLDT14EF_IsFinishField').setValue(this.findById('EPLDT14EF_IsFinishCombo').getValue());
		}
		
		evnplDispTeen14_form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Error_Msg)
					{
						if ( action.result.Error_Code )
						{
							switch ( action.result.Error_Code )
							{
								// не все исследования и осмотры
								case 10:
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg, function() {
										Ext.getCmp('EPLDT14EF_IsFinishCombo').focus(true, 200);
									});
								break;
								default:
									Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);
							}
						}
						else
							Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else
					{
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			},
			params: {
				'EvnVizitDispTeen14': Ext.util.JSON.encode(params.EvnVizitDispTeen14),
				'EvnUslugaDispTeen14': Ext.util.JSON.encode(params.EvnUslugaDispTeen14),
				'PediatrDate' : PediatrDate
			},
			success: function(result_form, action) {
				if (action.result)
				{
					loadMask.hide();
					if ( print )
					{
						var evn_pl_id = current_window.findById('EPLDT14EF_EvnPLDispTeen14_id').getValue();
						var server_id = current_window.findById('EPLDT14EF_Server_id').getValue();
						if ( !print_other ) {
							if ( !print_blank )
								window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
							else
								window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id + '&blank_only=2', '_blank');
						} else {
							switch (print_other) {								
								case 'passport':
									if (evn_pl_id > 0)
										window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14Passport&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id + '&blank_only=2', '_blank');
									break;
							}
						}
					}
					else
					{
						current_window.callback();
						current_window.hide();
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
	onEnableEdit: function(enable) {
		this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').setReadOnly(!enable);
		this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').setReadOnly(!enable);

		var form_fields = new Array(
			'EPLDT14EF_IsFinishCombo'
		);

		var i = 0;

		for (i = 0; i < form_fields.length; i++)
		{
			if (enable)
			{
				this.findById(form_fields[i]).enable();
			}
			else
			{
				this.findById(form_fields[i]).disable();
			}
		}
	},
	EnableRecommendationsTreatment: function () {

		if (this.action != 'view')
		{
			var j = 0;
			var k = 0;
					
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {	
			
				var diag_code = rec.get('Diag_Code').substr(0, 1);
				if ( diag_code.length > 0 && diag_code != 'Z' )
				{	

					if (rec.get('EvnVizitDispTeen14_isVMPRecommented') > 1) 
						j++;
					if (rec.get('RecommendationsTreatmentDopType_id') > 1) 
						k++;
				} 	
			}.createDelegate(this));

			if (j > 0) {
				this.findById('EPLDT14EF_EvnPLDispTeen14_isHTAid').enable();
				this.findById('EPLDT14EF_EvnPLDispTeen14_HTAidDT').enable();
			} else {
				this.findById('EPLDT14EF_EvnPLDispTeen14_isHTAid').disable();
				this.findById('EPLDT14EF_EvnPLDispTeen14_HTAidDT').disable();
			}

			if (k > 0) {
				this.findById('EPLDT14EF_DopDispResType').enable();
			} else {
				this.findById('EPLDT14EF_DopDispResType').disable();
			}
			
		}
	},
	evnVizitDispTeen14EditWindow: null,
	evnUslugaDispTeen14EditWindow: null,
	genId: function(obj) {
		var id_field = null;
		var index = 0;
		var result = null;
		var store = null;

		switch (obj)
		{

			case 'vizit':
				id_field = 'EvnVizitDispTeen14_id';
				store = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore();
				break;

			case 'usluga':
				id_field = 'EvnUslugaDispTeen14_id';
				store = this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore();
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
	id: 'EvnPLDispTeen14EditWindow',
	initComponent: function() {
		this.person_info_panel = new sw.Promed.PersonInfoPanel({
			button2Callback: function(callback_data) {
				var current_window = Ext.getCmp('EvnPLDispTeen14EditWindow');

				current_window.findById('EPLDT14EF_PersonEvn_id').setValue(callback_data.PersonEvn_id);
				current_window.findById('EPLDT14EF_Server_id').setValue(callback_data.Server_id);

				current_window.findById('EPLDT14EF_PersonInformationFrame').load( { 
					Person_id: callback_data.Person_id, Server_id: callback_data.Server_id,
					callback: function() {
						Ext.getCmp('EPLDT14EF_PersonInformationFrame').setPersonTitle();
					}.createDelegate(this)
				} );
			},
			button2OnHide: function() {
				var current_window = Ext.getCmp('EvnPLDispTeen14EditWindow');

				if (!current_window.findById('EPLDT14EF_IsFinishCombo').disabled)
				{
					current_window.findById('EPLDT14EF_IsFinishCombo').focus(false);
				}
			},
			button3OnHide: function() {
				var current_window = Ext.getCmp('EvnPLDispTeen14EditWindow');

				if (!current_window.findById('EPLDT14EF_IsFinishCombo').disabled)
				{
					current_window.findById('EPLDT14EF_IsFinishCombo').focus(false);
				}
			},
			id: 'EPLDT14EF_PersonInformationFrame',
			region: 'north',
			collapsible: true,
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			title: '<div>Загрузка...</div>',
			titleCollapse: true
		});
		
		this.visits_grid_panel = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnVizitDispTeen14_setDate',
				header: lang['data_posescheniya'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'Teen14DispSpecType_Name',
				header: lang['spetsialnost'],
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
				width: 230
			}],
			collapsible: true,
			frame: false,
			height: 190,
			id: 'EPLDT14EF_EvnVizitDispTeen14Grid',
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

					var grid = Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispTeen14EditWindow').deleteEvnVizitDispTeen14();
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

							Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnVizitDispTeen14EditWindow(action);

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
								Ext.getCmp('EPLDT14EF_IsFinishCombo').focus(true, 200);
							}
							else
							{
								var usluga_grid = Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid');
								if ( usluga_grid.getStore().getCount() > 0 )
								{
									usluga_grid.focus();
									usluga_grid.getSelectionModel().selectFirstRow();
									usluga_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDT14EF_SaveButton').focus();
								}
							}
						break;

						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispTeen14_id') == selected_record.data.EvnVizitDispTeen14_id; });

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

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispTeen14_id') == selected_record.data.EvnVizitDispTeen14_id; });

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
					Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnVizitDispTeen14EditWindow('edit');
				}
			},
			loadMask: true,
			region: 'center',
			setReadOnly: function (is_read_only) {
				//this {Ext.grid.GridPanel}
				this.isReadOnly = (is_read_only == true);
				this.getTopToolbar().items.items[0].setDisabled(this.isReadOnly);
				this.getTopToolbar().items.items[1].setDisabled(this.isReadOnly);
				this.getTopToolbar().items.items[3].setDisabled(this.isReadOnly);
			},
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						//this {Ext.grid.RowSelectionModel}
						var evn_vizitDispTeen14_id = sm.getSelected().data.EvnVizitDispTeen14_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						if (evn_vizitDispTeen14_id)
						{
							toolbar.items.items[1].setDisabled(this.grid.isReadOnly);
							toolbar.items.items[3].setDisabled(this.grid.isReadOnly);
							toolbar.items.items[2].enable();
						}
						else
						{
							toolbar.items.items[1].disable();
							toolbar.items.items[2].disable();
							toolbar.items.items[3].disable();
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
					id: 'EvnVizitDispTeen14_id'
				}, [{
					mapping: 'EvnVizitDispTeen14_id',
					name: 'EvnVizitDispTeen14_id',
					type: 'int'
				}, 
				// поля для дополнительной формы
				{
					mapping: 'DeseaseFuncType_id',
					name: 'DeseaseFuncType_id',
					type: 'int'
				}, {
					mapping: 'DiagType_id',
					name: 'DiagType_id',
					type: 'int'
				}, {
					mapping: 'DispRegistrationType_id',
					name: 'DispRegistrationType_id',
					type: 'int'
				}, {
					mapping: 'EvnVizitDispTeen14_isFirstDetected',
					name: 'EvnVizitDispTeen14_isFirstDetected',
					type: 'int'
				},{
					mapping: 'DeseaseFuncType_Name',
					name: 'DeseaseFuncType_Name',
					type: 'string'
				}, {
					mapping: 'DiagType_Name',
					name: 'DiagType_Name',
					type: 'string'
				}, {
					mapping: 'DispRegistrationType_Name',
					name: 'DispRegistrationType_Name',
					type: 'string'
				}, {
					mapping: 'EvnVizitDispTeen14_isFirstDetected_Name',
					name: 'EvnVizitDispTeen14_isFirstDetected_Name',
					type: 'string'
				}, {
					mapping: 'RecommendationsTreatmentType_id',
					name: 'RecommendationsTreatmentType_id',
					type: 'int'
				}, {
					mapping: 'EvnVizitDispTeen14_isVMPRecommented',
					name: 'EvnVizitDispTeen14_isVMPRecommented',
					type: 'int'
				}, {
					mapping: 'RecommendationsTreatmentDopType_id',
					name: 'RecommendationsTreatmentDopType_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Teen14DispSpecType_id',
					name: 'Teen14DispSpecType_id',
					type: 'int'
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
					mapping: 'EvnVizitDispTeen14_setDate',
					name: 'EvnVizitDispTeen14_setDate',
					type: 'date'
				}, {
					mapping: 'Diag_id',
					name: 'Diag_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'Teen14DispSpecType_Name',
					name: 'Teen14DispSpecType_Name',
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
					mapping: 'DeseaseStage_id',
					name: 'DeseaseStage_id',
					type: 'int'
				}, {
					mapping: 'HealthKind_id',
					name: 'HealthKind_id',
					type: 'int'
				}, {
					mapping: 'EvnVizitDispTeen14_IsSanKur',
					name: 'EvnVizitDispTeen14_IsSanKur',
					type: 'int'
				}, {
					mapping: 'EvnVizitDispTeen14_IsOut',
					name: 'EvnVizitDispTeen14_IsOut',
					type: 'int'
				}, {
					mapping: 'DopDispAlien_id',
					name: 'DopDispAlien_id',
					type: 'int'
				}, {
					mapping: 'EvnVizitDispTeen14_Descr',
					name: 'EvnVizitDispTeen14_Descr',
					type: 'string'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: C_EPLDT14_VIZIT_LIST
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnVizitDispTeen14EditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnVizitDispTeen14EditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnVizitDispTeen14EditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').deleteEvnVizitDispTeen14();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['osmotr_vracha-spetsialista']
		});
		
		this.usluga_grid_panel = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			collapsible: true,
			columns: [{
				dataIndex: 'EvnUslugaDispTeen14_setDate',
				header: lang['issledovan'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'EvnUslugaDispTeen14_didDate',
				header: lang['rezultat'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
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
				dataIndex: 'Usluga_Code',
				header: lang['kod'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'StudyType_Name',
				header: lang['vid_issledovaniya'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'Usluga_Name',
				header: lang['naimenovanie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'RateGrid_Data',
				header: lang['dannyie_po_pokazatelyam'],
				hidden: true
			}, {
				dataIndex: 'RateGrid_DataNumber',
				header: lang['nomer_dannyih_po_pokazatelyam'],
				hidden: true
			}],
			frame: false,
			height: 190,
			id: 'EPLDT14EF_EvnUslugaDispTeen14Grid',
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

					var grid = Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							Ext.getCmp('EvnPLDispTeen14EditWindow').deleteEvnUslugaDispTeen14();
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
								var vizit_grid = Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid');
								if ( vizit_grid.getStore().getCount() > 0 )
								{
									vizit_grid.focus();
									vizit_grid.getSelectionModel().selectFirstRow();
									vizit_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDT14EF_IsFinishCombo').focus(true, 200);
								}
							}
							else
							{												
								Ext.getCmp('EPLDT14EF_PersonWeight_Weight').focus();
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

							Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnUslugaDispTeen14EditWindow(action);

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

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispTeen14_id') == selected_record.data.EvnUslugaDispTeen14_id; });

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

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispTeen14_id') == selected_record.data.EvnUslugaDispTeen14_id; });

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
					Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnUslugaDispTeen14EditWindow('edit');
				}
			},
			loadMask: true,
			setReadOnly: function (is_read_only) {
				//this {Ext.grid.GridPanel}
				this.isReadOnly = (is_read_only == true);
				this.getTopToolbar().items.items[0].setDisabled(this.isReadOnly);
				this.getTopToolbar().items.items[1].setDisabled(this.isReadOnly);
				this.getTopToolbar().items.items[3].setDisabled(this.isReadOnly);
			},
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						//this {Ext.grid.RowSelectionModel}
						var evn_uslugaDispTeen14_id = sm.getSelected().data.EvnUslugaDispTeen14_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						if (evn_uslugaDispTeen14_id)
						{
							toolbar.items.items[1].setDisabled(this.grid.isReadOnly);
							toolbar.items.items[3].setDisabled(this.grid.isReadOnly);
							toolbar.items.items[2].enable();
						}
						else
						{
							toolbar.items.items[1].disable();
							toolbar.items.items[2].disable();
							toolbar.items.items[3].disable();
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
					id: 'EvnUslugaDispTeen14_id'
				}, [{
					mapping: 'EvnUslugaDispTeen14_id',
					name: 'EvnUslugaDispTeen14_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispTeen14_setDate',
					name: 'EvnUslugaDispTeen14_setDate',
					type: 'date'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispTeen14_didDate',
					name: 'EvnUslugaDispTeen14_didDate',
					type: 'date'
				}, {
					mapping: 'DispUslugaTeen14Type_id',
					name: 'DispUslugaTeen14Type_id',
					type: 'int'
				}, {
					mapping: 'DispUslugaTeen14Type_Name',
					name: 'DispUslugaTeen14Type_Name',
					type: 'string'
				}, 
				{
					mapping: 'ExaminationPlace_id',
					name: 'ExaminationPlace_id',
					type: 'int'
				}, 
				{
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
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
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'Usluga_id',
					name: 'Usluga_id',
					type: 'int'
				}, {
					mapping: 'StudyType_id',
					name: 'StudyType_id',
					type: 'int'
				}, {
					mapping: 'StudyType_Name',
					name: 'StudyType_Name',
					type: 'string'
				}, {
					mapping: 'Usluga_Code',
					name: 'Usluga_Code',
					type: 'string'
				}, {
					mapping: 'Usluga_Name',
					name: 'Usluga_Name',
					type: 'string'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}, {
					mapping: 'RateGrid_Data',
					name: 'RateGrid_Data',
					type: 'string'
				}, {
					mapping: 'RateGrid_DataNumber',
					name: 'RateGrid_DataNumber',
					type: 'string'
				}]),
				url: C_EPLDT14_USLUGA_LIST
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnUslugaDispTeen14EditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnUslugaDispTeen14EditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').openEvnUslugaDispTeen14EditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						Ext.getCmp('EvnPLDispTeen14EditWindow').deleteEvnUslugaDispTeen14();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['laboratornoe_issledovanie']
		});
		
		this.finish_field =	new Ext.Panel({
		bodyStyle: 'padding: 5px 5px 0',
		layout: 'form',
		items: [{
			allowBlank: false,					
			editable: false,
			enableKeyEvents: true,
			fieldLabel: lang['sluchay_zakonchen'],
			hiddenName: 'EvnPLDispTeen14_IsFinish',
			id: 'EPLDT14EF_IsFinishCombo',
			comboSubject: 'YesNo',
			lastQuery: '',
			listeners: {
				'keydown': function(inp, e) {
					if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
					{
						e.stopEvent();
						var usluga_grid = Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid');
						var vizit_grid = Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid');
						var health_info_grid = Ext.getCmp('EPLDT14EF_HealthInfoGrid');
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
						if ( health_info_grid.getStore().getCount() > 0 )
						{
							health_info_grid.getGrid().focus();
							health_info_grid.getGrid().getSelectionModel().selectFirstRow();
							health_info_grid.getGrid().getView().focusRow(0);
							return true;
						}
						Ext.getCmp('EPLDT14EF_SaveButton').focus();
					}
				},
				'change': function(field, newValue) {
					Ext.getCmp('EPLDT14EF_IsFinishField').setValue(newValue);
				}
			},
			listWidth: 150,
			tabIndex: TABINDEX_EPLDT14EF + 5,
			width: 80,
			xtype: 'swyesnocombo'
		}
		]});

		this.health_grid_panel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true},
				{name: 'action_edit', disabled: false, handler: function() {Ext.getCmp('EvnPLDispTeen14EditWindow').openTeen14DispHealthEditWindow('edit');}},
				{name: 'action_view', disabled: true},
				{name: 'action_delete', disabled: true},
				{name: 'action_refresh', disabled: true},
				{name: 'action_print', disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			collapsible: true,
			dataUrl: '',
			toolbar: true,
			focusOn: {
				name: 'EPLDT14EF_DopDispResType',
				type: 'field'
			},
			id: 'EPLDT14EF_HealthInfoGrid',
			paging: false,
			object: 'Teen14DispHealth',
			stringfields: [
				{name: 'Teen14DispSpecType_id', type: 'int', header: 'ID', key: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'Teen14DispSpecType_Name', type: 'string', header: lang['spetsialnost'], width: 250},
				{name: 'Diag_Code', type: 'string', header: lang['diagnoz'], width: 250},
				{name: 'DeseaseFuncType_Name', type: 'string', header: lang['zabolevanie'], width: 250},
				{name: 'DiagType_Name', type: 'string', header: lang['tip_diagnoza'], width: 250},
				{name: 'EvnVizitDispTeen14_isFirstDetected_Name', type: 'string', header: lang['vyiyavlen_vpervyie'], width: 250},
				{name: 'DispRegistrationType_Name', type: 'string', header: lang['dispansernyiy_uchet'], width: 250}
			]
		});
		
		this.main_form_panel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			buttonAlign: 'left',
			collapsible: true,
			frame: false,
			height: (!Ext.isIE) ? 624 : 629,
			id: 'EvnPLDispTeen14EditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [new Ext.Panel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				collapsible: true,
				frame: false,
				height: (!Ext.isIE) ? 200 : 205,
				labelAlign: 'right',
				labelWidth: 200,
				layout: 'form',
				title: lang['obschaya_otsenka_zdorovya'],
				items: [
				{
					id: 'EPLDT14EF_EvnPLDispTeen14_id',
					name: 'EvnPLDispTeen14_id',
					value: 0,
					xtype: 'hidden'
					}, {
						id: 'EPLDT14EF_Person_id',
						name: 'Person_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EPLDT14EF_PersonEvn_id',
						name: 'PersonEvn_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EPLDT14EF_EvnPLDispTeen14_setDate',
						name: 'EvnPLDispTeen14_setDate',
						xtype: 'hidden'
					}, {
						id: 'EPLDT14EF_Server_id',
						name: 'Server_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EPLDT14EF_IsFinishField',
						name: 'EvnPLDispTeen14_IsFinish',
						value: 1,
						xtype: 'hidden'
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['otsenka_fizicheskogo_razvitiya'],
						style: 'padding: 0; margin: 0; margin-bottom: 5px',
						defaults: {
							border: false
						},
						items: [{
							layout: 'column',
							defaults: {
								border: false
							},
							items: [{
								layout: 'form',
								defaults: {
									border: false
								},
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											xtype: 'numberfield',
											allowNegative: false,
											width: 100,
											decimalPrecision: 3,
											fieldLabel: lang['massa'],
											name: 'PersonWeight_Weight',
											id: 'EPLDT14EF_PersonWeight_Weight',
											tabIndex: TABINDEX_EPLDT14EF + 6
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											hideLabel: true,
											allowBlank: false,
											width: 60,
											value: 37,
											tabIndex: TABINDEX_EPLDT14EF + 7,
											loadParams: {params: {where: ' where Okei_id in (36,37)'}},
											hiddenName: 'Okei_id',
											xtype: 'swokeicombo'
										}]
									}]
								}]
							}, {
								layout: 'form',
								defaults: {
									border: false
								},
								labelWidth: 90,
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: lang['otklonenie'],
									hiddenName: 'PersonWeight_IsWeightAbnorm',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPLDispTeen14EditForm').getForm();

											var record = combo.getStore().getById(newValue);

											if ( record && record.get('YesNo_Code') == 1 ) {
												base_form.findField('WeightAbnormType_id').enable();
												base_form.findField('WeightAbnormType_id').setAllowBlank(false);
											}
											else {
												base_form.findField('WeightAbnormType_id').clearValue();
												base_form.findField('WeightAbnormType_id').disable();
												base_form.findField('WeightAbnormType_id').setAllowBlank(true);
											}
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EPLDT14EF + 8,
									value: 1,
									width: 60,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								layout: 'form',
								defaults: {
									border: false
								},
								labelWidth: 42,
								items: [{
									allowBlank: true,
									comboSubject: 'WeightAbnormType',
									disabled: true,
									fieldLabel: lang['tip'],
									tabIndex: TABINDEX_EPLDT14EF + 9,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							layout: 'column',
							defaults: {
								border: false
							},
							items: [{
								layout: 'form',
								defaults: {
									border: false
								},
								items: [{
									xtype: 'numberfield',
									allowNegative: false,
									allowDecimals: false,
									minValue: 20,
									maxValue: 240,
									autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
									width: 160,
									fieldLabel: lang['rost_sm'],
									name: 'PersonChild_Height',
									tabIndex: TABINDEX_EPLDT14EF + 10
								}]
							}, {
								layout: 'form',
								defaults: {
									border: false
								},
								labelWidth: 90,
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: lang['otklonenie'],
									hiddenName: 'PersonChild_IsHeightAbnorm',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPLDispTeen14EditForm').getForm();

											var record = combo.getStore().getById(newValue);

											if ( record && record.get('YesNo_Code') == 1 ) {
												base_form.findField('HeightAbnormType_id').enable();											
												base_form.findField('HeightAbnormType_id').setAllowBlank(false);
											}
											else {
												base_form.findField('HeightAbnormType_id').clearValue();
												base_form.findField('HeightAbnormType_id').disable();
												base_form.findField('HeightAbnormType_id').setAllowBlank(true);
											}
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EPLDT14EF + 11,
									value: 1,
									width: 60,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								layout: 'form',
								defaults: {
									border: false
								},
								labelWidth: 42,
								items: [{
									allowBlank: true,
									comboSubject: 'HeightAbnormType',
									disabled: true,
									fieldLabel: lang['tip'],
									tabIndex: TABINDEX_EPLDT14EF + 12,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}]
					}, {
						comboSubject: 'PsychicalConditionType',
						fieldLabel: lang['otsenka_psihicheskogo_razvitiya'],
						tabIndex: TABINDEX_EPLDT14EF + 13,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'SexualConditionType',
						fieldLabel: lang['otsenka_polovogo_razvitiya'],
						tabIndex: TABINDEX_EPLDT14EF + 14,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'InvalidType',
						fieldLabel: lang['invalidnost'],
						tabIndex: TABINDEX_EPLDT14EF + 15,
						enableKeyListeners: true,
						listeners: {
							'keydown': function(inp, e) {
								if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									var health_info_grid = Ext.getCmp('EPLDT14EF_HealthInfoGrid');								
									if ( health_info_grid.getGrid().getStore().getCount() > 0 )
									{
										health_info_grid.getGrid().focus();
										health_info_grid.getGrid().getSelectionModel().selectFirstRow();
										health_info_grid.getGrid().getView().focusRow(0);
										return true;
									}
									else
										Ext.getCmp('EPLDT14EF_DopDispResType').focus();
								}
							}
						},
						value: 1,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						comboSubject: 'HealthKind',
						id: 'EPLDT14EF_HealthKindCombo',
						disabled: true,
						fieldLabel: lang['gruppa_zdorovya'],
						loadParams: {params: {where: ' where HealthKind_Code <= 5'}},
						tabIndex: TABINDEX_EPLDT14EF + 16,
						xtype: 'swcommonsprcombo'
					}
				]}),
				new Ext.Panel({
					title: lang['diagnozyi_i_rekomendatsii'],
					collapsible: true,
					items: [
						this.health_grid_panel
					]
				}),
				new Ext.Panel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				collapsible: true,
				frame: false,
				height: (!Ext.isIE) ? 200 : 205,
				//labelWidth: 400,
				layout: 'form',
				title: lang['provedennoe_obsledovanie_i_lechenie_po_rezultatam_dispanserizatsii'],
				items: [new Ext.Panel({
					border: false,
					layout: 'form',
					labelAlign: 'top',
					frame: false,
					items:[{					
						allowBlank: true,
						comboSubject: 'DopDispResType',
						id: 'EPLDT14EF_DopDispResType',
						fieldLabel: lang['provedeno_dopolnitelnoe_obsledovanie_po_rezultatam_dispanserizatsii'],
						tabIndex: TABINDEX_EPLDT14EF + 17,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: true,
						comboSubject: 'DispResMedicalMeasureType',
						id: 'EPLDT14EF_DispResMedicalMeasureType',
						fieldLabel: lang['provedenyi_lechebno-ozdorovitelnyie_i_reabilitatsionnyie_meropriyatiya_po_rezultatam_dispanserizatsii'],
						tabIndex: TABINDEX_EPLDT14EF + 18,
						width: 300,
						xtype: 'swcommonsprcombo'
					}]
				}), {
						xtype: 'fieldset',
						autoHeight: true,
						bodyStyle: 'padding: 5px 5px 0',
						title: lang['vyisokotehnologichnaya_meditsinskaya_pomosch'],
						style: 'padding: 0; margin: 0; margin-bottom: 5px',
						labelWidth: 55,
						layout: 'form',
						labelAlign: 'right',
						defaults: {
							border: false
						},
						items: [{
							id:  'EPLDT14EF_EvnPLDispTeen14_isHTAid',
							allowBlank: true,
							comboSubject: 'YesNo',
							fieldLabel: lang['okazana'],
							hiddenName: 'EvnPLDispTeen14_isHTAid',
							tabIndex: TABINDEX_EPLDT14EF + 19,
							width: 60,
							xtype: 'swcommonsprcombo'
						}, {
							id:  'EPLDT14EF_EvnPLDispTeen14_HTAidDT',
							allowBlank: true,
							fieldLabel : lang['data_okazaniya'],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							tabIndex: TABINDEX_EPLDT14EF + 20,
							name: 'EvnPLDispTeen14_HTAidDT'
						}]
				}	
				]})],
			keys: [{
				fn: function(inp, e) {
					if (e.hasModifier())
					{
						return false;
					}

					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.F10:
							// TODO: Нет такого метода на форме.
							this.openPersonEditWindow();
							break;

						case Ext.EventObject.F11:
							this.openPersonCureHistoryWindow();
							break;

						case Ext.EventObject.F12:
							// TODO: И такого метода тоже нет!
							this.openPersonPrivilegeViewWindow();
							break;
					}
				},
				key: [ Ext.EventObject.F10, Ext.EventObject.F11, Ext.EventObject.F12 ],
				scope: this,
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave(false);
							}
							break;

						case Ext.EventObject.G:
							this.printEvnPLDispTeen14();
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
				{name: 'EvnPLDispTeen14_id'},
				{name: 'EvnPLDispTeen14_IsFinish'},
				{name: 'PersonEvn_id'},
				{name: 'EvnPLDispTeen14_setDate'},
				{name: 'PersonWeight_Weight'},
				{name: 'Okei_id'},
				{name: 'PersonWeight_IsWeightAbnorm'},
				{name: 'WeightAbnormType_id'},
				{name: 'PersonChild_Height'},
				{name: 'PersonChild_IsHeightAbnorm'},
				{name: 'HeightAbnormType_id'},
				{name: 'PsychicalConditionType_id'},
				{name: 'SexualConditionType_id'},
				{name: 'InvalidType_id'},
				{name: 'DopDispResType_id'},				
				{name: 'DispResMedicalMeasureType_id'},				
				{name: 'EvnPLDispTeen14_isHTAid'},				
				{name: 'EvnPLDispTeen14_HTAidDT'}
			]),			
			url: C_EPLDT14_SAVE
		});				
		
		Ext.apply(this, {
			layout: 'border',
			items: [
				this.person_info_panel,
				new Ext.Panel ({
					region: 'center',
					autoScroll: true,
					items: [
						this.finish_field,						
						this.visits_grid_panel,
						this.usluga_grid_panel,
						this.main_form_panel
					]
				})
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),				
				iconCls: 'save16',
				id: 'EPLDT14EF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDT14EF_CancelButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var usluga_grid = Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid');
					var vizit_grid = Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid');
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
					Ext.getCmp('EPLDT14EF_IsFinishCombo').focus(true, 200);
				},
				tabIndex: TABINDEX_EPLDT14EF + 16,
				text: BTN_FRMSAVE
			},/* {
				handler: function() {
					this.printEvnPLDispTeen14();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDT14EF_PrintButton',
				tabIndex: TABINDEX_EPLDT14EF + 17,
				text: BTN_FRMPRINT
			}, {
				handler: function() {
					this.printEvnPLDispTeen14(true);
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDT14EF_PrintBlankButton',
				tabIndex: TABINDEX_EPLDT14EF + 17,
				text: lang['pechat_blanka']
			}, {
				hidden: false,
				handler: function() {
					this.printEvnPLDispTeen14Passport();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDT14EF_PrintPassportButton',
				tabIndex: TABINDEX_EPLDT14EF + 18,
				text: lang['pechat_pasporta_zdorovya']
			}, {
				handler: function() {
					var current_window = Ext.getCmp('EvnPLDispTeen14EditWindow');
					var person_birthday = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Birthday');
					var person_surname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Surname');
					var person_firname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Firname');
					var person_secname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Secname');
					var params = {
						onHide: function() {
							this.focus();
						}.createDelegate(this),
						Person_Birthday: person_birthday,
						Person_Firname: person_firname,
						Person_Secname: person_secname,
						Person_Surname: person_surname,
						Person_id: current_window.findById('EPLDT14EF_Person_id').getValue(),
						Server_id: current_window.findById('EPLDT14EF_Server_id').getValue(),
						isDopDisp: true
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
				id: 'EPLDT14EF_DispButton',
				tabIndex: TABINDEX_EPLDT14EF + 18,
				text: lang['dispansernyiy_uchet']
			},*/ '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDT14EF_CancelButton',
				onTabAction: function() {
					Ext.getCmp('EPLDT14EF_IsFinishCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDT14EF_SaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EPLDT14EF + 19,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeen14EditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispTeen14EditWindow');
			var tabbar = current_window.findById('EPLDT14EF_EvnPLTabbar');

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
	layout: 'border',
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
	openEvnVizitDispTeen14EditWindow: function(action) {
		if ( getWnd('swEvnVizitDispTeen14EditWindow').isVisible() )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = this.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Age');

		var selected_record = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = this.params;
			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispTeen14_id = swGenTempId(this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore(), 'EvnVizitDispTeen14_id');
			params.Record_Status = 0;
			var UsedTeen14DispSpecType = Array();
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.get('EvnVizitDispTeen14_setDate');
				else
					if ( rec.get('EvnVizitDispTeen14_setDate') > max_date )
						max_date = rec.get('EvnVizitDispTeen14_setDate');
					
				UsedTeen14DispSpecType[rec.get('Teen14DispSpecType_id')] = 1;
			});
			params['UsedTeen14DispSpecType']=UsedTeen14DispSpecType;
			
			var UsedDispUslugaTeen14Type = Array();
			var UsedDispUslugaTeen14Type_Code = Array();
			this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispTeen14_didDate;
				else
					if ( rec.data.EvnUslugaDispTeen14_didDate > max_date )
						max_date = rec.data.EvnUslugaDispTeen14_didDate;
				if ( rec.data.StudyType_id == 1 ){
					UsedDispUslugaTeen14Type[rec.data.Usluga_id]=1;
					UsedDispUslugaTeen14Type_Code[rec.data.Usluga_Code]=1;
				}
			});
			params['UsedDispUslugaTeen14Type']=UsedDispUslugaTeen14Type;
			params['UsedDispUslugaTeen14Type_Code']=UsedDispUslugaTeen14Type_Code;
			
			
			params['Max_HealthKind_id'] = -1;
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( rec.get('HealthKind_id') > 0 && rec.get('Teen14DispSpecType_id') != 1 ) {
					if ( rec.get('HealthKind_id') > params['Max_HealthKind_id'] ) {
						params['Max_HealthKind_id'] = rec.get('HealthKind_id');
					}
				}
			});

			params.Not_Z_Group_Diag = false;
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( rec.get('Teen14DispSpecType_id') != 1 ) {
					var diag_code = rec.get('Diag_Code').substr(0, 1);

					if ( diag_code.length > 0 && !diag_code.inlist(['Z']) ) {
						params.Not_Z_Group_Diag = true;
					}
				}
			});
		}
		else if ( (action == 'edit') || (action == 'view') )
		{
			if ( !this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getSelectionModel().getSelected() )
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispTeen14_id == null || selected_record.data.EvnVizitDispTeen14_id == '' )
				return;
			
			params = selected_record.data;
			
			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			var UsedTeen14DispSpecType = Array();
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnVizitDispTeen14_setDate;
				else
					if ( rec.data.EvnVizitDispTeen14_setDate > max_date )
						max_date = rec.data.EvnVizitDispTeen14_setDate;
				if (rec != selected_record)
					UsedTeen14DispSpecType[rec.data.Teen14DispSpecType_id] = 1;
			});
			params['UsedTeen14DispSpecType'] = UsedTeen14DispSpecType;
			
			var UsedDispUslugaTeen14Type = Array();
			var UsedDispUslugaTeen14Type_Code = Array();
			this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispTeen14_didDate;
				else
					if ( rec.data.EvnUslugaDispTeen14_didDate > max_date )
						max_date = rec.data.EvnUslugaDispTeen14_didDate;
				if ( rec.data.StudyType_id == 1 ){
					UsedDispUslugaTeen14Type[rec.data.Usluga_id]=1;
					UsedDispUslugaTeen14Type_Code[rec.data.Usluga_Code]=1;
				}
			});
			params['UsedDispUslugaTeen14Type']=UsedDispUslugaTeen14Type;
			params['UsedDispUslugaTeen14Type_Code']=UsedDispUslugaTeen14Type_Code;

			params['Max_HealthKind_id'] = -1;
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( rec.get('HealthKind_id') > 0 && rec.get('Teen14DispSpecType_id') != 1 ) {
					if ( rec.get('HealthKind_id') > params['Max_HealthKind_id'] ) {
						params['Max_HealthKind_id'] = rec.get('HealthKind_id');
					}
				}
			});

			params['Not_Z_Group_Diag'] = false;
			this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
				if ( rec.data.Teen14DispSpecType_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,1);
					if ( !diag_code.inlist( Array('Z') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			});						
		}
		else
		{
			return false;
		}
		getWnd('swEvnVizitDispTeen14EditWindow').show({
			action: action,
			callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);
				});

				if ( add_flag == true )
				{
					// удаляем пустую строку если она есть
					if ( this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().getCount() == 1 )
					{
						var selected_record = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().getAt(0);
						if ( !selected_record.get('EvnVizitDispTeen14_id') == null || selected_record.get('EvnVizitDispTeen14_id') == '' )
							this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().removeAll();
					}
					this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().clearFilter();
					this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().loadData(data, add_flag);
					this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().filterBy(function(record) {
						if ( record.get('Record_Status') != 3 )
						{
							return true;
						}
					});
				}
				else {
					index = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().findBy(function(rec) { return rec.get('EvnVizitDispTeen14_id') == data[0].EvnVizitDispTeen14_id; });

					if ( index == -1 )
					{
						return false;
					}

					var record = this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().getAt(index);
					for ( i = 0; i < vizit_fields.length; i++ )
					{
						record.set(vizit_fields[i], data[0][vizit_fields[i]]);
					}

					record.commit();
				}

				var max_health_kind_id = -1;

				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
					if ( rec.get('HealthKind_id') > max_health_kind_id && rec.get('Teen14DispSpecType_id') != 1 ) {
						max_health_kind_id = rec.get('HealthKind_id');
					}
				});

				if ( max_health_kind_id > 0 ) {
					this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(function(rec) {
						if ( rec.get('Teen14DispSpecType_id') == 1 && rec.get('HealthKind_id') < max_health_kind_id ) {
							rec.set('HealthKind_id', max_health_kind_id);
							rec.commit();
						}
					});
				}
				
				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getView().refresh();
								
				this.refreshHealthGrid();

				return true;
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getSelectionModel().selectFirstRow();
				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getView().focusRow(0);
			}.createDelegate(this),
			ownerWindow: this,
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Sex_id: sex_id,
			Person_Age: age,
			max_date: max_date,
			Year: this.Year,
			UserLpuSection_id: this.UserLpuSection_id,
			UserMedStaffFact_id: this.UserMedStaffFact_id
		});
	},
	openTeen14DispHealthEditWindow: function(action) {
		var params = new Object();

		var selected_record = this.findById('EPLDT14EF_HealthInfoGrid').getGrid().getSelectionModel().getSelected();
		if ( !selected_record )
			return false;
		
		if ( getWnd('swTeen14DispHealthEditWindow').isVisible() )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_sostoyaniya_zdorovya_po_rezultatam_dispanserizatsii_uje_otkryito']);
			return false;
		}
		
		var data_row = false;
		this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(
			function( row ) {
				if ( row.get('Teen14DispSpecType_id') == selected_record.get('Teen14DispSpecType_id') )
				{
					data_row = row;
					return true;
				}
			}			
		);
		
		if ( !data_row )
			return false;

		getWnd('swTeen14DispHealthEditWindow').show({
			action: 'edit',
			callback: function(data) {
				// находим нужную строку и пишем в нее данные
				var data_row = false;
				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().each(
					function( row ) {
						if ( row.get('Teen14DispSpecType_id') == selected_record.get('Teen14DispSpecType_id') )
						{
							data_row = row;
							return true;
						}
					}			
				);
				if ( data_row )
				{
					for (i in data)
					{
						data_row.set(i, data[i]);
						if ( data_row.get('Record_Status') == '1' )
							data_row.set('Record_Status', '2');
						data_row.commit();
					}
				}
				return true;
			}.createDelegate(this),
			formParams: data_row.data,
			onHide: function() {
				this.findById('EPLDT14EF_HealthInfoGrid').getGrid().getSelectionModel().selectFirstRow();
				this.findById('EPLDT14EF_HealthInfoGrid').getGrid().getView().focusRow(0);
				this.EnableRecommendationsTreatment();
			}.createDelegate(this)
		});
	},
	openEvnUslugaDispTeen14EditWindow: function(action) {
		var current_window = this;

		if (getWnd('swEvnUslugaDispTeen14EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}
		var params = new Object();

		var person_id = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('EPLDT14EF_PersonInformationFrame').getFieldValue('Person_Age');
		
		if (current_window.action == 'add')
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		else
			var set_date = Date.parseDate(Ext.getCmp('EPLDT14EF_EvnPLDispTeen14_setDate').getValue(), 'd.m.Y');			

		if (action == 'add')
		{
			params = current_window.params;

			params.EvnUslugaDispTeen14_id = swGenTempId(this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore(), 'EvnUslugaDispTeen14_id');
			params.Record_Status = 0;
			var UsedDispUslugaTeen14Type = Array();
			var UsedDispUslugaTeen14Type_Code = Array();
			var Uslugawnd = this;
			this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().each(function(rec) {
				if (rec.data.StudyType_id == 1)
				{
					UsedDispUslugaTeen14Type[rec.data.Usluga_id]=1;
					UsedDispUslugaTeen14Type_Code[rec.data.Usluga_Code]=1;
				}
			});

			params['UsedDispUslugaTeen14Type']=UsedDispUslugaTeen14Type;
			params['UsedDispUslugaTeen14Type_Code']=UsedDispUslugaTeen14Type_Code;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispTeen14_id == null || selected_record.data.EvnUslugaDispTeen14_id == '' )
				return;

			params = selected_record.data;
			var UsedDispUslugaTeen14Type = Array();
			var UsedDispUslugaTeen14Type_Code = Array();
			var Uslugawnd = this;
			this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().each(function(rec) {
				//Передаем в параметры заведенные услуги, исключая выбранную (чтоб она отображалась в комбобоксе)
				if ( rec.data.StudyType_id == 1 && rec!=Uslugawnd.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getSelectionModel().getSelected())
				{
					UsedDispUslugaTeen14Type[rec.data.Usluga_id]=1;
					UsedDispUslugaTeen14Type_Code[rec.data.Usluga_Code]=1;
				}
			});
			params['UsedDispUslugaTeen14Type']=UsedDispUslugaTeen14Type;
			params['UsedDispUslugaTeen14Type_Code']=UsedDispUslugaTeen14Type_Code;
		}
		else
		{
			return false;
		}

		getWnd('swEvnUslugaDispTeen14EditWindow').show({
			action: action,
			callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
				{
					// удаляем пустую строку если она есть					
					if ( current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispTeen14_id == null || selected_record.data.EvnUslugaDispTeen14_id == '' )
							current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().removeAll();
					}
					
					current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().clearFilter();
					current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().loadData(data, add_flag);
					current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
				}
				else {
					index = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispTeen14_id') == data[0].EvnUslugaDispTeen14_id; });

					if (index == -1)
					{
						return false;
					}

					var record = current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().getAt(index);

					for (i = 0; i < usluga_fields.length; i++)
					{
						record.set(usluga_fields[i], data[0][usluga_fields[i]]);
					}

					record.commit();
				}
				return true;
			},
			formParams: params,
			onHide: function() {
				current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getSelectionModel().selectFirstRow();
				current_window.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getView().focusRow(0);				
			},
			ownerWindow: current_window,
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Sex_id: sex_id,
			Person_Age: age,
			set_date: set_date,
			UserLpuSection_id: this.UserLpuSection_id,
			UserMedStaffFact_id: this.UserMedStaffFact_id
		});
	},
	openPersonCureHistoryWindow: function() {
		var current_window = this;
		var form = current_window.findById('EvnPLDispTeen14EditForm');

		if (getWnd('swPersonCureHistoryWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_prosmotra_istorii_lecheniya_cheloveka_uje_otkryito']);
			return false;
		}

		var person_id = form.findById('EPLDT14EF_Person_id').getValue();
		var server_id = form.findById('EPLDT14EF_Server_id').getValue();

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
	printEvnPLDispTeen14: function(print_blank) {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			if ( print_blank === true )
				this.doSave(true, 1, true);
			else
				this.doSave(true);
		}
		else if (this.action == 'view')
		{
			var evn_pl_id = this.findById('EPLDT14EF_EvnPLDispTeen14_id').getValue();
			var server_id = this.findById('EPLDT14EF_Server_id').getValue();

			window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	printEvnPLDispTeen14Passport: function(print_blank) {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			this.doSave(true, 1, true, 'passport');
		} else if (this.action == 'view') {
			var evn_pl_id = this.findById('EPLDT14EF_EvnPLDispTeen14_id').getValue();
			var server_id = this.findById('EPLDT14EF_Server_id').getValue();

			window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14Passport&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	refreshHealthGrid: function() {
		this.health_grid_panel.getGrid().getStore().removeAll();
		var count = 0;
		var health_kind = false;
		this.visits_grid_panel.getStore().each(function(row) {
			var diag_code = row.get('Diag_Code').substr(0, 1);
			if ( row.get('Teen14DispSpecType_id') == 1 )
				health_kind = row.get('HealthKind_id');
			if ( diag_code != 'Z' && row.get('Diag_id') > 0 && row.get('Record_Status') != 3 )
			{
				this.health_grid_panel.getGrid().getStore().add([row]);				
				count++;
			}
		}.createDelegate(this));
		if ( health_kind > 0 )
			Ext.getCmp('EPLDT14EF_HealthKindCombo').setValue(health_kind);
		else
			Ext.getCmp('EPLDT14EF_HealthKindCombo').clearValue();
		if ( count > 0 )
			this.health_grid_panel.setActionDisabled('action_edit', false);
		else
			this.health_grid_panel.setActionDisabled('action_edit', true);
		this.EnableRecommendationsTreatment();
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispTeen14EditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		this.loadMask = new Ext.LoadMask(Ext.get('EvnPLDispTeen14EditWindow'), {msg: LOAD_WAIT});
		this.loadMask.show();
		
		var current_window = this;

		this.restore();
		this.center();
		this.maximize();
		
		Ext.getCmp('EPLDT14EF_HealthInfoGrid').syncSize();
		Ext.getCmp('EPLDT14EF_HealthInfoGrid').doLayout();

		// this.findById('EPLDT14EF_EvnPLTabbar').setActiveTab(4);

		var form = this.findById('EvnPLDispTeen14EditForm');
		form.getForm().reset();
		
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
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
		
		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
		{
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		else
		{
			this.UserMedStaffFact_id = null;
			// если в настройках есть medstafffact, то имеем список мест работы
			if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
			{
				this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];			
			}
			else
			{				
				// свободный выбор врача и отделения
				this.UserMedStaffFacts = null;
				this.UserLpuSections = null;
			}
		}
		
		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
		{
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		else
		{
			this.UserLpuSection_id = null;
			// если в настройках есть lpusection, то имеем список мест работы
			if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
			{
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{				
				// свободный выбор врача и отделения
				this.UserLpuSectons = null;
			}
		}
		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && getRegionNick() === 'kareliya' && arguments[0].EvnPLDispTeenInspection_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispTeenInspection_id,
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
		
		var current_window = this;
		var form = this.findById('EvnPLDispTeen14EditForm');
		var isfinish_combo = this.findById('EPLDT14EF_IsFinishCombo');
		
		isfinish_combo.focus(200, true);

		var evnplDispTeen14_id = form.findById('EPLDT14EF_EvnPLDispTeen14_id').getValue();
		var person_id = form.findById('EPLDT14EF_Person_id').getValue();
		var server_id = form.findById('EPLDT14EF_Server_id').getValue();
		
		inf_frame_is_loaded = false;
		
		this.findById('EPLDT14EF_PersonInformationFrame').load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				current_window.loadMask.hide(); 
				inf_frame_is_loaded = true; 								
				Ext.getCmp('EPLDT14EF_PersonInformationFrame').setPersonTitle();
			} 
		});

		this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDT14EF_EvnVizitDispTeen14Grid'));
		this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getSelectionModel().selectFirstRow();

		this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid'));
		this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getSelectionModel().selectFirstRow();

		switch (this.action)
		{
			case 'add':
				this.setTitle(WND_POL_EPLDT14ADD);
				this.enableEdit(true);
				if ( inf_frame_is_loaded )
					current_window.loadMask.hide();				
				this.findById('EPLDT14EF_IsFinishCombo').expand();
				this.findById('EPLDT14EF_IsFinishCombo').collapse();
				this.findById('EPLDT14EF_IsFinishCombo').setValue(1);
				this.findById('EPLDT14EF_IsFinishField').setValue(1);
				this.refreshHealthGrid();
				break;
			case 'edit':
				this.setTitle(WND_POL_EPLDT14EDIT);
				this.enableEdit(true);

				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().load({
					params: {EvnPLDispTeen14_id: evnplDispTeen14_id},
					callback: function() {
						if ( Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('EPLDT14EF_EvnVizitDispTeen14Grid'));
						this.refreshHealthGrid();
					}.createDelegate(this)
				});
				this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().load({ 
					params: {EvnPLDispTeen14_id: evnplDispTeen14_id},
					callback: function() {
						if ( Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('EPLDT14EF_EvnUslugaDispTeen14Grid'));
					}
				});

				form.getForm().load({
					failure: function() {
						swEvnPLDispTeen14EditWindow.hide();
						if ( inf_frame_is_loaded )
							current_window.loadMask.hide();
					},
					params: {
						EvnPLDispTeen14_id: evnplDispTeen14_id
					},
					success: function() {
						if ( inf_frame_is_loaded )
							current_window.loadMask.hide();
						Ext.getCmp('EPLDT14EF_IsFinishCombo').setValue(Ext.getCmp('EPLDT14EF_IsFinishField').getValue());
						Ext.getCmp('EPLDT14EF_IsFinishCombo').collapse();
						if ( form.getForm().findField('PersonChild_IsHeightAbnorm').getValue() == 2 )
						{
							form.getForm().findField('HeightAbnormType_id').enable();
							form.getForm().findField('HeightAbnormType_id').setAllowBlank(false);
						}
						else
						{
							form.getForm().findField('HeightAbnormType_id').clearValue();
							form.getForm().findField('HeightAbnormType_id').disable();							
							form.getForm().findField('HeightAbnormType_id').setAllowBlank(true);
						}
						
						if ( form.getForm().findField('PersonWeight_IsWeightAbnorm').getValue() == 2 )
						{
							form.getForm().findField('WeightAbnormType_id').enable();
							form.getForm().findField('WeightAbnormType_id').setAllowBlank(false);
						}
						else
						{
							form.getForm().findField('WeightAbnormType_id').clearValue();
							form.getForm().findField('WeightAbnormType_id').disable();							
							form.getForm().findField('WeightAbnormType_id').setAllowBlank(true);
						}		
					},
					url: C_EPLDT14_LOAD
				});				
				
				this.findById('EPLDT14EF_IsFinishCombo').focus(false);
				break;

			case 'view':
				this.setTitle(WND_POL_EPLDT14VIEW);
				this.enableEdit(false);

				this.findById('EPLDT14EF_EvnVizitDispTeen14Grid').getStore().load({params: {EvnPLDispTeen14_id: evnplDispTeen14_id}, callback: function() {this.refreshHealthGrid();}.createDelegate(this)});
				this.findById('EPLDT14EF_EvnUslugaDispTeen14Grid').getStore().load({params: {EvnPLDispTeen14_id: evnplDispTeen14_id}});

				form.getForm().load({
					failure: function() {
						if ( inf_frame_is_loaded )
							current_window.loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {current_window.hide();} );
					},
					params: {
						EvnPLDispTeen14_id: evnplDispTeen14_id
					},
					success: function() {
						if ( inf_frame_is_loaded )
							current_window.loadMask.hide();						
					},
					url: C_EPLDT14_LOAD
				});

				this.buttons[2].focus();
				break;
		}
		
		this.EnableRecommendationsTreatment();
		form.getForm().clearInvalid();
	},
	
	title: WND_POL_EPLDT14ADD,
	width: 800
});