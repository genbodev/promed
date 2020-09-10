/**
* swEvnPLDispOrpEditWindow - окно редактирования/добавления талона по диспасеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков Андрей
* @originalauthor	Stas Bykov aka Savage (savage1981@gmail.com)
* @version    май 2010
* @comment    Префикс для id компонентов epldoef (EvnPLDispOrpEditForm)
*	            TABINDEX_EPLDOEF: 9300
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLDispOrp_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              Server_id - ?
*
*
* Использует: окно просмотра истории болезни (swPersonCureHistoryWindow)
*             окно просмотра льгот (swPersonPrivilegeViewWindow)
*             окно редактирования человека (swPersonEditWindow)
*             окно добавления/редактирования услуги по ДД (swEvnUslugaDispOrpEditWindow)
*             окно добавления/редактирования посещения по ДД (swEvnVizitDispOrpEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispOrpEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispOrpEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispOrpEditWindow.js',
	deleteEvnVizitDispOrp: function() 
	{
		sw.swMsg.show(
		{
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) 
			{
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnvizitdispdop_grid = current_window.findById('epldoefEvnVizitDispOrpGrid');

					if (!evnvizitdispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnvizitdispdop_grid.getSelectionModel().getSelected();

					if (selected_record.data.Record_Status == 0)
					{
						evnvizitdispdop_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnvizitdispdop_grid.getStore().filterBy(function(record) 
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
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
					
					if ( evnvizitdispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnvizitdispdop_grid);
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
					var evnuslugadispdop_grid = current_window.findById('epldoefEvnUslugaDispOrpGrid');

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
					
					if ( evnuslugadispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnuslugadispdop_grid);
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_laboratornoe_issledovanie'],
			title: lang['vopros']
		})
	},
	draggable: true,
	doSave: function(print, check_finish) {
		return false;
		var current_window = this;
		var evnpldispdop_form = current_window.findById('EvnPLDispOrpEditForm');
		var evnvizitdispdop_grid = current_window.findById('epldoefEvnVizitDispOrpGrid');
		var evnuslugadispdop_grid = current_window.findById('epldoefEvnUslugaDispOrpGrid');
		var i = 0;

		if (!evnpldispdop_form.getForm().isValid())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('epldoefAttachTypeCombo').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// проверка на заполненность всех услуг и посещений, если пытаются закрыть талон по ДД
		var sex_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Sex_id');
		current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Age')
				
		var ped_date = null;
		current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
			if ( rec.get('OrpDispSpec_id') == 1 )
				ped_date = rec.data.EvnVizitDispOrp_setDate;			
		});
		if ( !ped_date )
		{
			var age = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Age');
		}
		else
		{	
			var birth_date = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Birthday');
			var age = (birth_date.getMonthsBetween(ped_date) - (birth_date.getMonthsBetween(ped_date) % 12)) / 12;
		}
		
		// услуги
		max_count = 6;
		
		if ( age < 1 )
		{
			max_count++;
		}
		
		var count = 0;
		current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
			if ( rec.data.OrpDispUslugaType_id != 8 )
			{
				count++;
			}
		});
		
		var usluga_is_full = (count >= max_count);
				
		// посещения
		max_count = 8;		
		// 9 – с 3-х лет включительно
		if ( age >= 3 )
		{
			max_count++; // 9
		}
		
		if ( age >= 5 )
		{
			max_count++; // 11
		}		

		var count = 0;
		var UsedOrpDispSpec = new Array();
		current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
			if ( rec.data.Record_Status !=3 )
				UsedOrpDispSpec[rec.data.OrpDispSpec_id] = 1;
		});
		var stand = 0;
		for ( var i = 0; i < UsedOrpDispSpec.length; i++ )
		{
			if ( i.inlist([1,2,3,4,5,7,8]) && UsedOrpDispSpec[i] == 1 )
				stand++;
		}
		var prov = 7;
		
		var spec_is_full = ( stand == prov );
		
		if ( age >= 5 && sex_id == 1 && UsedOrpDispSpec[10] != 1 )
			spec_is_full = false;
			
		if ( sex_id == 2 && UsedOrpDispSpec[6] != 1 )
			spec_is_full = false;
		
		if ( age >= 3 && UsedOrpDispSpec[9] != 1 )
			spec_is_full = false;

		if ( age >= 5 && UsedOrpDispSpec[11] != 1 )
			spec_is_full = false;
					
		//var spec_is_full = (count >= max_count);
		if ( Ext.getCmp('epldoefIsFinishCombo').getValue() == 2 )
		{			
			if ( !spec_is_full || !usluga_is_full )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('epldoefIsFinishCombo').setValue(1);
						current_window.findById('epldoefIsFinishCombo').focus();
					},
					icon: Ext.Msg.WARNING,
					msg: "Случай не может быть закончен, так как заполнены не все исследования или осмотры.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			// проверка на максимальную дату
			var max_date = false;
			var min_date = false;
			var therapy_date = false;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.get('OrpDispSpec_id') == 1 )
					therapy_date = rec.data.EvnVizitDispOrp_setDate;
				else
				{
					if ( max_date == false )
						max_date = rec.data.EvnVizitDispOrp_setDate;
					else
						if ( rec.data.EvnVizitDispOrp_setDate > max_date )
							max_date = rec.data.EvnVizitDispOrp_setDate;
					if ( min_date == false )
						min_date = rec.data.EvnVizitDispOrp_setDate;
					else
						if ( rec.data.EvnVizitDispOrp_setDate < min_date )
							min_date = rec.data.EvnVizitDispOrp_setDate;
				}
			});			
			current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispOrp_didDate;
				else
					if ( rec.data.EvnUslugaDispOrp_didDate > max_date )
						max_date = rec.data.EvnUslugaDispOrp_didDate;
			});
			if ( therapy_date < max_date )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('epldoefAttachTypeCombo').focus(true, 200);
					},
					icon: Ext.Msg.WARNING,
					msg: "Осмотр педиатра не может быть проведен ранее других осмотров или даты получения результатов исследований.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			
			// проверка на разницу в месяцах между самым ранним осмотром и осмотром педиатра
			if ( ( min_date.getMonthsBetween(therapy_date) > 3 ) || ( min_date.getMonthsBetween(therapy_date) == 3 && (min_date.getDate() != therapy_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('epldoefAttachTypeCombo').focus(true, 200);
					},
					icon: Ext.Msg.WARNING,
					msg: "Осмотр педиатра не может быть проведен по истечении 3х месяцев после самого раннего осмотра.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		else
		{
			if ( spec_is_full && usluga_is_full && check_finish != 2  )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes')
						{
							Ext.getCmp('epldoefIsFinishCombo').setValue(2);
							current_window.doSave(print, 2);
						}
						else
							current_window.doSave(print, 2);
					},
					icon: Ext.MessageBox.QUESTION,
					msg: lang['v_talone_zapolnenyi_vse_neobhodimyie_dannyie_prostavit_priznak_zakonchennosti_sluchaya'],
					title: lang['vopros']
				});
				return;
			}
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrpEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		// Функция get_grid_records возвращает записи из store
		function get_grid_records(store, save_trigger)
		{
			var fields = new Array();
			var result = new Array();

			store.fields.eachKey(function(key, item) {
				if (save_trigger == true && key.indexOf('_Name') == -1 && key.indexOf('_Fio') == -1)
				{
					fields.push(key);
				}
				else if (save_trigger == false)
				{
					fields.push(key);
				}
			});

			store.clearFilter();
			store.each(function(record) {
				if (record.data.Record_Status == 0 || record.data.Record_Status == 2 || record.data.Record_Status == 3)
				{
					var temp_record = new Object();
					for (i = 0; i < fields.length; i++)
					{
						if (save_trigger == true && fields[i].indexOf('Date') != -1)
						{
							temp_record[fields[i]] = Ext.util.Format.date(record.data[fields[i]], 'd.m.Y');
						}
						else
						{
							temp_record[fields[i]] = record.data[fields[i]];
						}
					}
					result.push(temp_record);
				}
			});

			store.filterBy(function(record) {
				if (record.data.Record_Status != 3)
				{
					return true;
				}
			});

			return result;
		}

		// Собираем данные из гридов
		var params = new Object();
		params.EvnVizitDispOrp = get_grid_records(evnvizitdispdop_grid.getStore(), true);
		params.EvnUslugaDispOrp = get_grid_records(evnuslugadispdop_grid.getStore(), true);

		evnpldispdop_form.getForm().submit({
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
									Ext.Msg.alert(lang['oshibka'], action.result.Error_Msg, function() {
										Ext.getCmp('epldoefIsFinishCombo').focus(true, 200);
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
				'EvnVizitDispOrp': Ext.util.JSON.encode(params.EvnVizitDispOrp),
				'EvnUslugaDispOrp': Ext.util.JSON.encode(params.EvnUslugaDispOrp)
			},
			success: function(result_form, action) {
				if (action.result)
				{
					loadMask.hide();
					if ( print )
					{
						var evn_pl_id = current_window.findById('epldoefEvnPLDispOrp_id').getValue();
						var server_id = current_window.findById('epldoefServer_id').getValue();
						window.open(C_EPLDO_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
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
	enableEdit: function(enable) {
		var form_fields = new Array(
			'epldoefAttachTypeCombo',
			'epldoefPrikLpuCombo',
			'epldoefIsFinishCombo'
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

		if (enable)
		{
			this.buttons[0].enable();
			this.buttons[2].enable();
		}
		else
		{
			this.buttons[0].disable();
			this.buttons[2].disable();
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
				store = this.findById('epldoefEvnVizitDispOrpGrid').getStore();
				break;

			case 'usluga':
				id_field = 'EvnUslugaDispOrp_id';
				store = this.findById('epldoefEvnUslugaDispOrpGrid').getStore();
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
	id: 'EvnPLDispOrpEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			items: [
			new Ext.Panel ({
				border: false,
				layout: 'border',
				region: 'north',
				height: (!Ext.isIE) ? 210 : 230,
				items: [
					new sw.Promed.PersonInformationPanel({
						button2Callback: function(callback_data) {
							var current_window = Ext.getCmp('EvnPLDispOrpEditWindow');

							current_window.findById('epldoefPersonEvn_id').setValue(callback_data.PersonEvn_id);
							current_window.findById('epldoefServer_id').setValue(callback_data.Server_id);
							
							current_window.findById('epldoefPersonInformationFrame').load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
						},
						button2OnHide: function() {
							var current_window = Ext.getCmp('EvnPLDispOrpEditWindow');

							if (!current_window.findById('epldoefAttachTypeCombo').disabled)
							{
								current_window.findById('epldoefAttachTypeCombo').focus(false);
							}
						},
						button3OnHide: function() {
							var current_window = Ext.getCmp('EvnPLDispOrpEditWindow');

							if (!current_window.findById('epldoefAttachTypeCombo').disabled)
							{
								current_window.findById('epldoefAttachTypeCombo').focus(false);
							}
						},
						id: 'epldoefPersonInformationFrame',
						region: 'north'
					}),
					new Ext.form.FormPanel({
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						buttonAlign: 'left',
						frame: false,
						height: (!Ext.isIE) ? 105 : 125,
						id: 'EvnPLDispOrpEditForm',
						labelAlign: 'right',
						labelWidth: 195,
						items: [{
							id: 'epldoefEvnPLDispOrp_id',
							name: 'EvnPLDispOrp_id',
							value: 0,
							xtype: 'hidden'
							}, {
								id: 'epldoefPerson_id',
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							}, {
								id: 'epldoefPersonEvn_id',
								name: 'PersonEvn_id',
								value: 0,
								xtype: 'hidden'
							}, {
								id: 'epldoefEvnPLDispOrp_setDate',
								name: 'EvnPLDispOrp_setDate',
								xtype: 'hidden'
							}, {
								id: 'epldoefServer_id',
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									labelWidth: 195,
									layout: 'form',
									items: [{
										allowBlank: false,
										enableKeyEvents: true,
										fieldLabel: lang['prikreplen_dlya'],
										id: 'epldoefAttachTypeCombo',
										listeners: {
											'change': function(combo, newValue) {
												Ext.getCmp('epldoefPrikLpuCombo').setAllowBlank(newValue != 1);
											}
										},
										name: 'AttachType_id',
										tabIndex: 2403,
										validateOnBlur: false,
										width: 290,
										xtype: 'swattachtypecombo'
									}]
								}]
							}, new sw.Promed.SwLpuLocalCombo({
								allowBlank: true,
								editable: false,
								fieldLabel: lang['lpu_postoyannogo_prikrepleniya'],
								hiddenName: 'Lpu_aid',
								id: 'epldoefPrikLpuCombo',
								listWidth: 505,
								tabIndex: 2404,
								width: 505
							}), {
								allowBlank: false,
								codeField: 'YesNo_Code',
								displayField: 'YesNo_Name',
								editable: false,
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispOrp_IsFinish',
								id: 'epldoefIsFinishCombo',
								lastQuery: '',
								listeners: {
									'keydown': function(inp, e) {
										if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();
											var usluga_grid = Ext.getCmp('epldoefEvnUslugaDispOrpGrid');
											var vizit_grid = Ext.getCmp('epldoefEvnVizitDispOrpGrid');
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
											Ext.getCmp('epldoefSaveButton').focus();
										}
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
							}
						],
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
										// TODO: WTF? Нет данной функции в этом объекте
										this.openPersonEditWindow();
										break;

									case Ext.EventObject.F11:
										this.openPersonCureHistoryWindow();
										break;

									case Ext.EventObject.F12:
										// TODO: WTF? Нет данной функции в этом объекте
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
							{ name: 'EvnPLDispOrp_IsBud' },
							{ name: 'EvnPLDispOrp_IsFinish' },
							{ name: 'AttachType_id' },
							{ name: 'Lpu_aid' },
							{ name: 'PersonEvn_id' },
							{ name: 'EvnPLDispOrp_setDate' }
						]),
						region: 'center',
						url: C_EPLDO_SAVE
					})
			]}),
			new Ext.Panel ({
					border: false,
					region: 'center',
					items: [
					new Ext.grid.GridPanel({
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 100,
							columns: [{
								dataIndex: 'EvnVizitDispOrp_setDate',
								header: lang['data_posescheniya'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'OrpDispSpec_Name',
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
								width: 130
							}],
							collapsible: true,
							frame: false,
							height: 190,
							id: 'epldoefEvnVizitDispOrpGrid',
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

									var grid = Ext.getCmp('epldoefEvnVizitDispOrpGrid');

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											Ext.getCmp('EvnPLDispOrpEditWindow').deleteEvnVizitDispOrp();
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
												action = 'view';
											}
											else if (e.getKey() == Ext.EventObject.ENTER)
											{
												action = 'view';
											}

											Ext.getCmp('EvnPLDispOrpEditWindow').openEvnVizitDispOrpEditWindow(action);

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
												Ext.getCmp('epldoefIsFinishCombo').focus(true, 200);
											}
											else
											{
												var usluga_grid = Ext.getCmp('epldoefEvnUslugaDispOrpGrid');
												if ( usluga_grid.getStore().getCount() > 0 )
												{
													usluga_grid.focus();
													usluga_grid.getSelectionModel().selectFirstRow();
													usluga_grid.getView().focusRow(0);
												}
												else
												{
													Ext.getCmp('epldoefSaveButton').focus();
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
									Ext.getCmp('EvnPLDispOrpEditWindow').openEvnVizitDispOrpEditWindow('view');
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
										if (evn_vizitdispdop_id)
										{
											toolbar.items.items[1].enable();
											toolbar.items.items[2].enable();

/*											if (record_status == 0)
											{*/
												toolbar.items.items[3].enable();
/*											}
											else
											{
												toolbar.items.items[3].disable();
											}*/
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
									id: 'EvnVizitDispOrp_id'
								}, [{
									mapping: 'EvnVizitDispOrp_id',
									name: 'EvnVizitDispOrp_id',
									type: 'int'
								}, {
									mapping: 'LpuSection_id',
									name: 'LpuSection_id',
									type: 'int'
								}, {
									mapping: 'OrpDispSpec_id',
									name: 'OrpDispSpec_id',
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
									mapping: 'EvnVizitDispOrp_setDate',
									name: 'EvnVizitDispOrp_setDate',
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
									mapping: 'OrpDispSpec_Name',
									name: 'OrpDispSpec_Name',
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
									mapping: 'EvnVizitDispOrp_IsSanKur',
									name: 'EvnVizitDispOrp_IsSanKur',
									type: 'int'
								}, {
									mapping: 'EvnVizitDispOrp_IsOut',
									name: 'EvnVizitDispOrp_IsOut',
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
								url: C_EPLDO_VIZIT_LIST
							}),
							tbar: new sw.Promed.Toolbar({
								buttons: [{
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').openEvnVizitDispOrpEditWindow('add');
									},
									iconCls: 'add16',
									text: BTN_GRIDADD,
									tooltip: BTN_GRIDADD_TIP, hidden: true, disabled: true
								}, {
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').openEvnVizitDispOrpEditWindow('edit');
									},
									iconCls: 'edit16',
									text: BTN_GRIDEDIT,
									tooltip: BTN_GRIDEDIT_TIP, hidden: true, disabled: true
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispOrpEditWindow').openEvnVizitDispOrpEditWindow('view');
									},
									iconCls: 'view16',
									text: BTN_GRIDVIEW,
									tooltip: BTN_GRIDVIEW_TIP
								}, {
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').deleteEvnVizitDispOrp();
									},
									iconCls: 'delete16',
									text: BTN_GRIDDEL,
									tooltip: BTN_GRIDDEL_TIP, hidden: true, disabled: true
								}]
							}),
							title: lang['osmotr_vracha-spetsialista']
						}),
						new Ext.grid.GridPanel({
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 100,
							columns: [{
								dataIndex: 'EvnUslugaDispOrp_setDate',
								header: lang['issledovan'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'EvnUslugaDispOrp_didDate',
								header: lang['rezultat'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'OrpDispUslugaType_Name',
								header: lang['vid_issledovaniya'],
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
								dataIndex: 'Usluga_Code',
								header: lang['kod'],
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
							}],
							frame: false,
							height: 300,
							id: 'epldoefEvnUslugaDispOrpGrid',
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

									var grid = Ext.getCmp('epldoefEvnUslugaDispOrpGrid');

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											Ext.getCmp('EvnPLDispOrpEditWindow').deleteEvnUslugaDispOrp();
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
												var vizit_grid = Ext.getCmp('epldoefEvnVizitDispOrpGrid');
												if ( vizit_grid.getStore().getCount() > 0 )
												{
													vizit_grid.focus();
													vizit_grid.getSelectionModel().selectFirstRow();
													vizit_grid.getView().focusRow(0);
												}
												else
												{
													Ext.getCmp('epldoefIsFinishCombo').focus(true, 200);
												}
											}
											else
											{												
												Ext.getCmp('epldoefSaveButton').focus();
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
												action = 'view';
											}

											Ext.getCmp('EvnPLDispOrpEditWindow').openEvnUslugaDispOrpEditWindow(action);

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
									Ext.getCmp('EvnPLDispOrpEditWindow').openEvnUslugaDispOrpEditWindow('view');
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

										if (evn_uslugadispdop_id)
										{
											toolbar.items.items[1].enable();
											toolbar.items.items[2].enable();

/*											if (record_status == 0)
											{*/
												toolbar.items.items[3].enable();
/*											}
											else
											{
												toolbar.items.items[3].disable();
											}*/
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
									dateFormat: 'd.m.Y',
									mapping: 'EvnUslugaDispOrp_didDate',
									name: 'EvnUslugaDispOrp_didDate',
									type: 'date'
								}, {
									mapping: 'OrpDispUslugaType_id',
									name: 'OrpDispUslugaType_id',
									type: 'int'
								}, {
									mapping: 'OrpDispUslugaType_Name',
									name: 'OrpDispUslugaType_Name',
									type: 'string'
								}, {
									mapping: 'ExaminationPlace_id',
									name: 'ExaminationPlace_id',
									type: 'int'
								}, {
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
								}]),
								url: C_EPLDO_USLUGA_LIST
							}),
							tbar: new sw.Promed.Toolbar({
								buttons: [{
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').openEvnUslugaDispOrpEditWindow('add');
									},
									iconCls: 'add16',
									text: BTN_GRIDADD,
									tooltip: BTN_GRIDADD_TIP, hidden: true, disabled: true
								}, {
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').openEvnUslugaDispOrpEditWindow('edit');
									},
									iconCls: 'edit16',
									text: BTN_GRIDEDIT,
									tooltip: BTN_GRIDEDIT_TIP, hidden: true, disabled: true
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispOrpEditWindow').openEvnUslugaDispOrpEditWindow('view');
									},
									iconCls: 'view16',
									text: BTN_GRIDVIEW,
									tooltip: BTN_GRIDVIEW_TIP
								}, {
									handler: function() {
										// Ext.getCmp('EvnPLDispOrpEditWindow').deleteEvnUslugaDispOrp();
									},
									iconCls: 'delete16',
									text: BTN_GRIDDEL,
									tooltip: BTN_GRIDDEL_TIP, hidden: true, disabled: true
								}]
							}),
							title: lang['laboratornoe_issledovanie']
						})
					]
				})
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),				
				iconCls: 'save16',
				id: 'epldoefSaveButton',
				onTabAction: function() {
					Ext.getCmp('epldoefPrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var usluga_grid = Ext.getCmp('epldoefEvnUslugaDispOrpGrid');
					var vizit_grid = Ext.getCmp('epldoefEvnVizitDispOrpGrid');
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
					Ext.getCmp('epldoefIsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispOrp();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'epldoefPrintButton',
				tabIndex: 2407,
				text: BTN_FRMPRINT
			}, {
				handler: function() {
					var current_window = Ext.getCmp('EvnPLDispOrpEditWindow');
					var person_birthday = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Birthday');
					var person_surname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Surname');
					var person_firname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Firname');
					var person_secname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Secname');
					var params = {
						onHide: function() {
							this.focus();
						}.createDelegate(this),
						Person_Birthday: person_birthday,
						Person_Firname: person_firname,
						Person_Secname: person_secname,
						Person_Surname: person_surname,
						Person_id: current_window.findById('epldoefPerson_id').getValue(),
						Server_id: current_window.findById('epldoefServer_id').getValue(),
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
				id: 'epldoefDispButton',
				tabIndex: 2408,
				text: lang['dispansernyiy_uchet']
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'epldoefCancelButton',
				onTabAction: function() {
					Ext.getCmp('epldoefAttachTypeCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('epldoefAttachTypeCombo').focus(true, 200);
				},
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispOrpEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispOrpEditWindow');
			var tabbar = current_window.findById('epldoefEvnPLTabbar');

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
	openEvnVizitDispOrpEditWindow: function(action) {
        var current_window = this;

		if (getWnd('swEvnVizitDispOrpEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Age');

		var selected_record = current_window.findById('epldoefEvnVizitDispOrpGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = current_window.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispOrp_id = swGenTempId(this.findById('epldoefEvnVizitDispOrpGrid').getStore(), 'EvnVizitDispOrp_id');
			params.Record_Status = 0;
			var UsedOrpDispSpec = Array();
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnVizitDispOrp_setDate;
				else
					if ( rec.data.EvnVizitDispOrp_setDate > max_date )
						max_date = rec.data.EvnVizitDispOrp_setDate;
					
				if (rec.data.Record_Status != 3)
					UsedOrpDispSpec[rec.data.OrpDispSpec_id] = 1;
			});
			params['UsedOrpDispSpec']=UsedOrpDispSpec;
			
			var UsedOrpDispUslugaType = Array();
			current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispOrp_didDate;
				else
					if ( rec.data.EvnUslugaDispOrp_didDate > max_date )
						max_date = rec.data.EvnUslugaDispOrp_didDate;
				if (rec.data.Record_Status != 3)
					UsedOrpDispUslugaType[rec.data.OrpDispUslugaType_id]=1;
			});
			params['UsedOrpDispUslugaType']=UsedOrpDispUslugaType;
			
			params['EvnVizitDispOrp_IsSanKur_Test'] = false;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.EvnVizitDispOrp_IsSanKur == 2 && rec.data.OrpDispSpec_id != 1 )
					params['EvnVizitDispOrp_IsSanKur_Test'] = true;
			});
			
			params['Max_HealthKind_id'] = -1;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.HealthKind_id > 0 && rec.data.OrpDispSpec_id != 1 )
				{
					if ( params['Max_HealthKind_id'] == -1 )
						params['Max_HealthKind_id'] = rec.data.HealthKind_id;
					else
					{
						if ( rec.data.HealthKind_id > params['Max_HealthKind_id'] )
							params['Max_HealthKind_id'] = rec.data.HealthKind_id;							
					}
				}
			});
			
			params['Not_Z_Group_Diag'] = false;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.OrpDispSpec_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,3);
					if ( !diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			});
		}
		else if ((action == 'edit') || (action == 'view'))
		{			
			if (!current_window.findById('epldoefEvnVizitDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispOrp_id == null || selected_record.data.EvnVizitDispOrp_id == '' )
				return;
			
			params = selected_record.data;
			
			// буду собирать максимальную дату осмотра или анализов (исключая осмотр у педиатра - задача #4503)
			var max_date = false;
			
			var UsedOrpDispSpec = Array();
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if(rec.data.OrpDispSpec_id != 1) {//исключаем дату осмотра у педиатра
					if ( max_date == false )
						max_date = rec.data.EvnVizitDispOrp_setDate;
					else
						if ( rec.data.EvnVizitDispOrp_setDate > max_date )
							max_date = rec.data.EvnVizitDispOrp_setDate;
				}
				if ( rec != selected_record && rec.data.Record_Status != 3 )
					UsedOrpDispSpec[rec.data.OrpDispSpec_id] = 1;
			});
			params['UsedOrpDispSpec'] = UsedOrpDispSpec;
			
			var UsedOrpDispUslugaType = Array();
			current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispOrp_didDate;
				else
					if ( rec.data.EvnUslugaDispOrp_didDate > max_date )
						max_date = rec.data.EvnUslugaDispOrp_didDate;
				UsedOrpDispUslugaType[rec.data.OrpDispUslugaType_id]=1;
			});
			params['UsedOrpDispUslugaType']=UsedOrpDispUslugaType;
			
			params['Max_HealthKind_id'] = -1;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.HealthKind_id > 0 && rec.data.OrpDispSpec_id != 1 )
				{
					if ( params['Max_HealthKind_id'] == -1 )
						params['Max_HealthKind_id'] = rec.data.HealthKind_id;
					else
					{
						if ( rec.data.HealthKind_id > params['Max_HealthKind_id'] )
							params['Max_HealthKind_id'] = rec.data.HealthKind_id;							
					}
				}
			});
			
			params['Not_Z_Group_Diag'] = false;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {				
				if ( rec.data.OrpDispSpec_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,3);
					if ( !diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			});
			
			params['EvnVizitDispOrp_IsSanKur_Test'] = false;
			current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.EvnVizitDispOrp_IsSanKur == 2 && rec.data.OrpDispSpec_id != 1 )
					params['EvnVizitDispOrp_IsSanKur_Test'] = true;
			});
		}
		else
		{
			return false;
		}

        getWnd('swEvnVizitDispOrpEditWindow').show({
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);					
				});

				if ( add_flag == true )
        		{
	        		// удаляем пустую строку если она есть					
					if ( current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnVizitDispOrp_id == null || selected_record.data.EvnVizitDispOrp_id == '' )
							current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().removeAll();
					}					
					current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().clearFilter();
					current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().loadData(data, add_flag);
					current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().findBy(function(rec) { return rec.get('EvnVizitDispOrp_id') == data[0].EvnVizitDispOrp_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = current_window.findById('epldoefEvnVizitDispOrpGrid').getStore().getAt(index);
					for (i = 0; i < vizit_fields.length; i++)
					{
						record.set(vizit_fields[i], data[0][vizit_fields[i]]);
					}

					record.commit();
				}
        		return true;
        	},			
        	formParams: params,
        	onHide: function() {
				current_window.findById('epldoefEvnVizitDispOrpGrid').getSelectionModel().selectFirstRow();
				current_window.findById('epldoefEvnVizitDispOrpGrid').getView().focusRow(0);				
			},
			ownerWindow: current_window,
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Year: this.Year,
			Sex_id: sex_id,
			Person_Age: age,
			max_date: max_date
		});
	},
	openEvnUslugaDispOrpEditWindow: function(action) {
        var current_window = this;

		if (getWnd('swEvnUslugaDispOrpEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Person_Age');
		
		if (current_window.action == 'add')
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		else
			var set_date = Date.parseDate(Ext.getCmp('epldoefEvnPLDispOrp_setDate').getValue(), 'd.m.Y');			

		if (action == 'add')
		{
			params = current_window.params;

			params.EvnUslugaDispOrp_id = swGenTempId(this.findById('epldoefEvnUslugaDispOrpGrid').getStore(), 'EvnUslugaDispOrp_id');
			params.Record_Status = 0;
			var UsedOrpDispUslugaType = Array();
			current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					UsedOrpDispUslugaType[rec.data.OrpDispUslugaType_id]=1;
			});
			params['UsedOrpDispUslugaType']=UsedOrpDispUslugaType;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!current_window.findById('epldoefEvnUslugaDispOrpGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = current_window.findById('epldoefEvnUslugaDispOrpGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
				return;

			params = selected_record.data;
			var UsedOrpDispUslugaType = Array();
			current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					UsedOrpDispUslugaType[rec.data.OrpDispUslugaType_id]=1;
			});
			params['UsedOrpDispUslugaType']=UsedOrpDispUslugaType;
		}
		else
		{
			return false;
		}

        getWnd('swEvnUslugaDispOrpEditWindow').show({
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
        		{
					// удаляем пустую строку если она есть					
					if ( current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispOrp_id == null || selected_record.data.EvnUslugaDispOrp_id == '' )
							current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().removeAll();
					}
					
					current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().clearFilter();
					current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().loadData(data, add_flag);
					current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispOrp_id') == data[0].EvnUslugaDispOrp_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = current_window.findById('epldoefEvnUslugaDispOrpGrid').getStore().getAt(index);

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
				current_window.findById('epldoefEvnUslugaDispOrpGrid').getSelectionModel().selectFirstRow();
				current_window.findById('epldoefEvnUslugaDispOrpGrid').getView().focusRow(0);				
			},
        	ownerWindow: current_window,
		    Person_id: person_id,
		    Person_Birthday: person_birthday,
			Person_Surname: person_surname,
		    Person_Firname: person_firname,
			Person_Secname: person_secname,
			Sex_id: sex_id,
			Person_Age: age,
			set_date: set_date
		});
	},
	openPersonCureHistoryWindow: function() {
		var current_window = this;
		var form = current_window.findById('EvnPLDispOrpEditForm');

		if (getWnd('swPersonCureHistoryWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_prosmotra_istorii_lecheniya_cheloveka_uje_otkryito']);
			return false;
		}

		var person_id = form.findById('epldoefPerson_id').getValue();
		var server_id = form.findById('epldoefServer_id').getValue();

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
			this.doSave(true);
		}
		else if (this.action == 'view')
		{
			var evn_pl_id = this.findById('epldoefEvnPLDispOrp_id').getValue();
			var server_id = this.findById('epldoefServer_id').getValue();

			window.open(C_EPLDO_PRINT + '&EvnPLDispOrp_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispOrpEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;

		this.restore();
		this.center();
		this.maximize();

		// this.findById('epldoefEvnPLTabbar').setActiveTab(4);

		var form = this.findById('EvnPLDispOrpEditForm');
		form.getForm().reset();

		Ext.getCmp('epldoefPrikLpuCombo').setAllowBlank(true);
		
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (!arguments[0])
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
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
		if (this.action === 'edit' && getRegionNick() === 'kareliya' && arguments[0].EvnPLDispOrp_id) {
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
		var form = this.findById('EvnPLDispOrpEditForm');
		var isbud_combo = this.findById('epldoefIsBudCombo');
		var attach_combo = this.findById('epldoefAttachTypeCombo');
		var priklpu_combo = this.findById('epldoefPrikLpuCombo');
		var isfinish_combo = this.findById('epldoefIsFinishCombo');
		var evnpldispdop_id = form.findById('epldoefEvnPLDispOrp_id').getValue();
		var person_id = form.findById('epldoefPerson_id').getValue();
		var server_id = form.findById('epldoefServer_id').getValue();
		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispOrpEditWindow'), { msg: LOAD_WAIT });
		
		loadMask.show();
		
		inf_frame_is_loaded = false;
		
		this.findById('epldoefPersonInformationFrame').load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				loadMask.hide(); 
				inf_frame_is_loaded = true; 
				current_window.findById('epldoefAttachTypeCombo').focus(false);
				if ( current_window.action == 'add' )
				{
					var Lpu_id = current_window.findById('epldoefPersonInformationFrame').getFieldValue('Lpu_id');
					if ( Lpu_id && Lpu_id > 0 )
						current_window.findById('epldoefPrikLpuCombo').setValue(Lpu_id);
				}
			} 
		});

		this.findById('epldoefEvnVizitDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('epldoefEvnVizitDispOrpGrid'));
		this.findById('epldoefEvnVizitDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('epldoefEvnVizitDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('epldoefEvnVizitDispOrpGrid').getTopToolbar().items.items[3].disable();

		this.findById('epldoefEvnUslugaDispOrpGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('epldoefEvnUslugaDispOrpGrid'));
		this.findById('epldoefEvnUslugaDispOrpGrid').getTopToolbar().items.items[1].disable();
		this.findById('epldoefEvnUslugaDispOrpGrid').getTopToolbar().items.items[2].disable();
		this.findById('epldoefEvnUslugaDispOrpGrid').getTopToolbar().items.items[3].disable();		

		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['talon_po_dispanserizatsii_detey-sirot_dobavlenie']);
				this.enableEdit(true);
				if ( inf_frame_is_loaded )
					loadMask.hide();				
				this.findById('epldoefIsFinishCombo').setValue(1);
				this.findById('epldoefAttachTypeCombo').setValue(1);
				break;
			case 'edit':
				this.setTitle(lang['talon_po_dispanserizatsii_detey-sirot_redaktirovanie']);
				this.enableEdit(true);

				this.findById('epldoefEvnVizitDispOrpGrid').getStore().load({ 
					params: { EvnPLDispOrp_id: evnpldispdop_id },
					callback: function() {
						if ( Ext.getCmp('epldoefEvnVizitDispOrpGrid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('epldoefEvnVizitDispOrpGrid'));
					}
				});
				this.findById('epldoefEvnUslugaDispOrpGrid').getStore().load({ 
					params: { EvnPLDispOrp_id: evnpldispdop_id },
					callback: function() {
						if ( Ext.getCmp('epldoefEvnUslugaDispOrpGrid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('epldoefEvnUslugaDispOrpGrid'));
					}
				});

				form.getForm().load({
					failure: function() {
						swEvnPLDispOrpEditWindow.hide();
						if ( inf_frame_is_loaded )
							loadMask.hide();
					},
					params: {
						EvnPLDispOrp_id: evnpldispdop_id
					},
					success: function() {
						var evnpldispdop_lpu_id = priklpu_combo.getValue();
						if ( inf_frame_is_loaded )
							loadMask.hide();
						Ext.getCmp('epldoefPrikLpuCombo').setAllowBlank( Ext.getCmp('epldoefAttachTypeCombo').getValue() != 1 );
					},
					url: C_EPLDO_LOAD
				});
				this.findById('epldoefAttachTypeCombo').focus(false);
				break;

			case 'view':
				this.setTitle(lang['talon_po_dopolnitelnoy_detey-sirot_prosmotr']);
				this.enableEdit(false);

				this.findById('epldoefEvnVizitDispOrpGrid').getStore().load({ params: { EvnPLDispOrp_id: evnpldispdop_id } });
				this.findById('epldoefEvnUslugaDispOrpGrid').getStore().load({ params: { EvnPLDispOrp_id: evnpldispdop_id } });

				form.getForm().load({
					failure: function() {
						if ( inf_frame_is_loaded )
							loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { current_window.hide(); } );
					},
					params: {
						EvnPLDispOrp_id: evnpldispdop_id
					},
					success: function() {
						var evnpldispdop_lpu_id = priklpu_combo.getValue();
						if ( inf_frame_is_loaded )
							loadMask.hide();
						priklpu_combo.getStore().load({
							callback: function(records, options, success) {
								if (success)
								{
									priklpu_combo.setValue(evnpldispdop_lpu_id);
								}
							},
							params: {
								Org_id: evnpldispdop_lpu_id,
								OrgType: 'lpu'
							}
						});
					},
					url: C_EPLDO_LOAD
				});

				this.buttons[1].focus();
				break;
		};
		form.getForm().clearInvalid();
	},
	
	title: WND_POL_EPLDOADD,
	width: 800
});