/**
* swEvnPLDispDopEditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor	Stas Bykov aka Savage (savage1981@gmail.com)
* @version		23.06.2009
* @comment		Префикс для id компонентов EPLDDEF (EvnPLDispDopEditForm)
*				tabIndex: 2401
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLDispDop_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ?
*              Server_id - ?
*
*
* Использует: окно просмотра истории болезни (swPersonCureHistoryWindow)
*             окно просмотра льгот (swPersonPrivilegeViewWindow)
*             окно редактирования человека (swPersonEditWindow)
*             окно добавления/редактирования услуги по ДД (swEvnUslugaDispDopEditWindow)
*             окно добавления/редактирования посещения по ДД (swEvnVizitDispDopEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDopEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDopEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDopEditWindow.js',
	
	deleteEvnVizitDispDop: function() {
		var grid = this.findById('EPLDDEF_EvnVizitDispDopGrid');
		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record || !selected_record.get('EvnVizitDispDop_id') )
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
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osmotr_vracha-spetsialista'],
			title: lang['vopros']
		});
	},
	deleteEvnUslugaDispDop: function() {
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var current_window = this;
					var evnuslugadispdop_grid = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid');

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
					
					if ( evnuslugadispdop_grid.getStore().getCount() == 0 )
					{
						var data = new Object();
						var load_data = new Object();

						evnuslugadispdop_grid.getStore().fields.eachKey(function(key, item) {
							data[key] = null;
						});

						evnuslugadispdop_grid.getStore().loadData([ data ], true);
					}

					evnuslugadispdop_grid.getView().focusRow(0);
					evnuslugadispdop_grid.getSelectionModel().selectFirstRow();

					/*if (evnuslugadispdop_grid.getStore().getCount() == 0)
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
						LoadEmptyRow(evnuslugadispdop_grid);*/
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
		var evnpldispdop_form = current_window.findById('EvnPLDispDopEditForm');
		var evnvizitdispdop_grid = current_window.findById('EPLDDEF_EvnVizitDispDopGrid');
		var evnuslugadispdop_grid = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid');
		var i = 0;

		if ( !evnpldispdop_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('EPLDDEF_OkvedCombo').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// проверка на заполненность всех услуг и посещений, если пытаются закрыть талон по ДД
		var sex_id = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Age');

		var ter_date = null;
		current_window.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
			if ( rec.get('DopDispSpec_id') == 1 )
				ter_date = rec.data.EvnVizitDispDop_setDate;			
		});
		if ( !ter_date )
		{
			var age = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Age');
		}
		else
		{	
			var birth_date = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Birthday');
			var age = (birth_date.getMonthsBetween(ter_date) - (birth_date.getMonthsBetween(ter_date) % 12)) / 12;
		}
		// услуги
		max_count = 13;
		
		if ( age >= 45 )
		{
			max_count++;
		}
		
		if ( age >= 40 && sex_id == 2 && Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotMammograf').getValue() != true )
		{
			max_count++;
		}
		
		if ( sex_id == 2 && Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotCito').getValue() != true )
		{
			max_count++;
		}

		var count = 0;
		evnuslugadispdop_grid.getStore().each(function(rec) {
			if ( rec.get('DopDispUslugaType_id') != 8 )
			{
				count++;
			}
		});
		
		var usluga_is_full = (count >= max_count);
		
		// посещения
		max_count = 4;
		if ( sex_id == 2 )
		{
			max_count++;
		}		

		var count = 0;
		evnvizitdispdop_grid.getStore().each(function(rec) {
			count++;
		});
		var spec_is_full = (count >= max_count);
			
		if ( Ext.getCmp('EPLDDEF_IsFinishCombo').getValue() == 2 )
		{			
			if ( !spec_is_full || !usluga_is_full )
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EPLDDEF_IsFinishCombo').setValue(1);
						current_window.findById('EPLDDEF_IsFinishCombo').focus();
					},
					icon: Ext.Msg.WARNING,
					msg: "Случай не может быть закончен, так как заполнены не все исследования или осмотры.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			// проверка на максимальную дату
			var max_date = false;
			var therapy_date = false;

			evnvizitdispdop_grid.getStore().each(function(rec) {
				if ( rec.get('DopDispSpec_id') == 1 )
					therapy_date = rec.get('EvnVizitDispDop_setDate');
				else
				{
					if ( max_date == false )
						max_date = rec.get('EvnVizitDispDop_setDate');
					else
						if ( rec.get('EvnVizitDispDop_setDate') > max_date )
							max_date = rec.get('EvnVizitDispDop_setDate');
				}
			});			
			current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.get('EvnUslugaDispDop_didDate');
				else
					if ( rec.get('EvnUslugaDispDop_didDate') > max_date )
						max_date = rec.get('EvnUslugaDispDop_didDate');
			});
			if ( therapy_date < max_date )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EPLDDEF_OkvedCombo').focus(true, 200);
					},
					icon: Ext.Msg.WARNING,
					msg: "Осмотр терапевта не может быть проведен ранее других осмотров или даты получения результатов исследований.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			
			// проверяем на просроченность исследований
			var errors = false;
			current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				if ( rec.get('Record_Status') != 3 )
				{
					// 2 года которые
					var set_date = rec.get('EvnUslugaDispDop_setDate');
					var usluga_type_id = rec.get('DopDispUslugaType_id');
					if ( therapy_date > set_date && (usluga_type_id == 6 || usluga_type_id == 5) )
					{
						if ( ( set_date.getMonthsBetween(therapy_date) > 24 ) || ( set_date.getMonthsBetween(therapy_date) == 24 && (set_date.getDate() != therapy_date.getDate()) ) )
						{
							Ext.MessageBox.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									current_window.findById('EPLDDEF_IsFinishCombo').focus();
								},
								icon: Ext.Msg.WARNING,
								msg: 'Давность исследования "' + rec.get('DopDispUslugaType_Name') + '" не может быть более 2х лет.',
								title: lang['oshibka']
							});
							errors = true;
							return false;
						}
					}
					
					// 3 месяца которые
					if ( therapy_date > set_date && usluga_type_id != 6 && usluga_type_id != 5 )
					{
						if ( ( set_date.getMonthsBetween(therapy_date) > 3 ) || ( set_date.getMonthsBetween(therapy_date) == 3 && (set_date.getDate() != therapy_date.getDate()) ) )
						{
							Ext.MessageBox.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									current_window.findById('EPLDDEF_IsFinishCombo').focus();
								},
								icon: Ext.Msg.WARNING,
								msg: 'Давность исследования "' + rec.get('DopDispUslugaType_Name') + '" не может быть более 3х месяцев.',
								title: lang['oshibka']
							});
							errors = true;
							return false;
						}
					}
				}				
			});
			if ( errors === true )
				return false;
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
							Ext.getCmp('EPLDDEF_IsFinishCombo').setValue(2);
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

		var loadMask = new Ext.LoadMask(Ext.get('EvnPLDispDopEditWindow'), { msg: "Подождите, идет сохранение..." });
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
		params.EvnVizitDispDop = get_grid_records(evnvizitdispdop_grid.getStore(), true);
		params.EvnUslugaDispDop = get_grid_records(evnuslugadispdop_grid.getStore(), true);
		//swalert(params.EvnUslugaDispDop);

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
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg, function() {
										Ext.getCmp('EPLDDEF_IsFinishCombo').focus(true, 200);
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
				'EvnVizitDispDop': Ext.util.JSON.encode(params.EvnVizitDispDop),
				'EvnUslugaDispDop': Ext.util.JSON.encode(params.EvnUslugaDispDop)
			},
			success: function(result_form, action) {
				if (action.result)
				{
					loadMask.hide();
					if ( print )
					{
						var evn_pl_id = current_window.findById('EPLDDEF_EvnPLDispDop_id').getValue();
						var server_id = current_window.findById('EPLDDEF_Server_id').getValue();
						if ( !print_other ) {
							if ( !print_blank ) {
								printBirt({
									'Report_FileName': 'f131u_dd_09.rptdesign',
									'Report_Params': '&paramEvnPLDispDop=' + evn_pl_id,
									'Report_Format': 'pdf'
								});
							} else {
								printBirt({
									'Report_FileName': 'f131u_dd_09_blank.rptdesign',
									'Report_Params': '&paramEvnPLDispDop=' + evn_pl_id,
									'Report_Format': 'pdf'
								});
							}
						} else {
							switch (print_other) {								
								case 'passport':
									if (evn_pl_id > 0)
										window.open('/?c=EvnPLDispDop&m=printEvnPLDispDopPassport&EvnPLDispDop_id=' + evn_pl_id + '&Server_id=' + server_id + '&blank_only=2', '_blank');
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
	enableEdit: function(enable) {
		this.findById('EPLDDEF_EvnVizitDispDopGrid').setReadOnly(!enable);
		this.findById('EPLDDEF_EvnUslugaDispDopGrid').setReadOnly(!enable);
		var form_fields = new Array(
			'EPLDDEF_OkvedCombo',
			'EPLDDEF_IsBudCombo',
			'EPLDDEF_AttachTypeCombo',
			'EPLDDEF_PrikLpuCombo',
			'EPLDDEF_IsFinishCombo',
			'EPLDDEF_PassportGiveCombo'
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
	evnVizitDispDopEditWindow: null,
	evnUslugaDispDopEditWindow: null,
	genId: function(obj) {
		var id_field = null;
		var index = 0;
		var result = null;
		var store = null;

		switch (obj)
		{

			case 'vizit':
				id_field = 'EvnVizitDispDop_id';
				store = this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore();
				break;

			case 'usluga':
				id_field = 'EvnUslugaDispDop_id';
				store = this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore();
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
	id: 'EvnPLDispDopEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			items: [
			new Ext.Panel ({
				border: false,
				layout: 'border',
				region: 'north',
				height: (!Ext.isIE) ? 230 : 250,
				items: [
					new sw.Promed.PersonInformationPanel({
						button2Callback: function(callback_data) {
							var current_window = Ext.getCmp('EvnPLDispDopEditWindow');

							current_window.findById('EPLDDEF_PersonEvn_id').setValue(callback_data.PersonEvn_id);
							current_window.findById('EPLDDEF_Server_id').setValue(callback_data.Server_id);
							
							current_window.findById('EPLDDEF_PersonInformationFrame').load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
						},
						button2OnHide: function() {
							var current_window = Ext.getCmp('EvnPLDispDopEditWindow');

							if (!current_window.findById('EPLDDEF_OkvedCombo').disabled)
							{
								current_window.findById('EPLDDEF_OkvedCombo').focus(false);
							}
						},
						button3OnHide: function() {
							var current_window = Ext.getCmp('EvnPLDispDopEditWindow');

							if (!current_window.findById('EPLDDEF_OkvedCombo').disabled)
							{
								current_window.findById('EPLDDEF_OkvedCombo').focus(false);
							}
						},
						id: 'EPLDDEF_PersonInformationFrame',
						region: 'north'
					}),
					new Ext.form.FormPanel({
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						buttonAlign: 'left',
						frame: false,
						height: (!Ext.isIE) ? 125 : 145,
						id: 'EvnPLDispDopEditForm',
						labelAlign: 'right',
						labelWidth: 195,
						items: [{
							id: 'EPLDDEF_EvnPLDispDop_id',
							name: 'EvnPLDispDop_id',
							value: 0,
							xtype: 'hidden'
							}, {
								id: 'EPLDDEF_Person_id',
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							}, {
								id: 'EPLDDEF_PersonEvn_id',
								name: 'PersonEvn_id',
								value: 0,
								xtype: 'hidden'
							}, {
								id: 'EPLDDEF_EvnPLDispDop_setDate',
								name: 'EvnPLDispDop_setDate',
								xtype: 'hidden'
							}, {
								id: 'EPLDDEF_Server_id',
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							}, {
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['kod_spetsialnosti_po_okved'],
								hiddenName: 'EvnPLDispDop_Okved_id',
								id: 'EPLDDEF_OkvedCombo',
								listeners: {
									'keydown': function(inp, e) {
										if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();											
											Ext.getCmp('EPLDDEF_CancelButton').focus();
										}
									}
								},
								name: 'EvnPLDispDop_Okved_id',
								tabIndex: 2401,
								validateOnBlur: false,
								width: 505,
								xtype: 'swokvedcombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									labelWidth: 195,
									layout: 'form',
									items: [{
										allowBlank: false,
										codeField: 'YesNo_Code',
										displayField: 'YesNo_Name',
										editable: false,
										fieldLabel: lang['byudjetnyiy_rabotnik'],
										hiddenName: 'EvnPLDispDop_IsBud',
										id: 'EPLDDEF_IsBudCombo',
										lastQuery: '',
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
										tabIndex: 2402,
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'<font color="red">{YesNo_Code}</font>&nbsp;{YesNo_Name}',
											'</div></tpl>'
										),
										valueField: 'YesNo_id',
										width: 80,
										xtype: 'swbaselocalcombo'
									}]
								}, {
									border: false,
									labelWidth: 130,
									layout: 'form',
									items: [{
										allowBlank: false,
										enableKeyEvents: true,
										fieldLabel: lang['prikreplen_dlya'],
										id: 'EPLDDEF_AttachTypeCombo',
										listeners: {
											'change': function(combo, newValue) {
												Ext.getCmp('EPLDDEF_PrikLpuCombo').setAllowBlank(newValue != 1);
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
								id: 'EPLDDEF_PrikLpuCombo',
								listWidth: 505,
								tabIndex: 2404,
								width: 505
							}), {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									labelWidth: 195,
									layout: 'form',
									items: [{
										allowBlank: false,
										codeField: 'YesNo_Code',
										displayField: 'YesNo_Name',
										editable: false,
										enableKeyEvents: true,
										fieldLabel: lang['sluchay_zakonchen'],
										hiddenName: 'EvnPLDispDop_IsFinish',
										id: 'EPLDDEF_IsFinishCombo',
										lastQuery: '',
										listeners: {
											'keydown': function(inp, e) {
												if ( !e.shiftKey && e.getKey() == Ext.EventObject.TAB )
												{
													e.stopEvent();
													var usluga_grid = Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid');
													var vizit_grid = Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid');
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
													Ext.getCmp('EPLDDEF_SaveButton').focus();
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
									}]
								}, {
									border: false,
									labelWidth: 340,
									layout: 'form',
									items: [{
										allowBlank: false,
										codeField: 'YesNo_Code',
										displayField: 'YesNo_Name',
										editable: false,
										enableKeyEvents: true,
										fieldLabel: lang['pasport_zdorovya_vyidan_patsientu'],
										hiddenName: 'EvnPLDispDop_PassportGive',
										id: 'EPLDDEF_PassportGiveCombo',
										lastQuery: '',
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
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								style: 'padding-left: 5px;',
								items: [{
									border: false,
									labelWidth: 25,
									layout: 'form',
									items: [{
										name: 'EvnPLDispDop_IsNotMammograf',
										boxLabel: lang['nevozmojnost_provedeniya_mammografii'],
										id: 'EPLDDEF_EvnPLDispDop_IsNotMammograf',
										hideLabel: true,
										xtype: 'checkbox'
									}]
								}, {
									border: false,
									labelWidth: 30,
									layout: 'form',
									items: [{
										name: 'EvnPLDispDop_IsNotCito',
										id: 'EPLDDEF_EvnPLDispDop_IsNotCito',
										boxLabel: lang['nevozmojnost_provedeniya_tsitologicheskogo_issledovaniya_mazka_iz_tservikalnogo_kanala'],
										hideLabel: true,
										xtype: 'checkbox'
									}]
								}]
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
										this.printEvnPLDispDop();
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
							{ name: 'EvnPLDispDop_id' },
							{ name: 'EvnPLDispDop_IsBud' },
							{ name: 'EvnPLDispDop_IsFinish' },							
							{ name: 'EvnPLDispDop_Okved_id' },
							{ name: 'EvnPLDispDop_PassportGive' },
							{ name: 'AttachType_id' },
							{ name: 'Lpu_aid' },
							{ name: 'PersonEvn_id' },
							{ name: 'EvnPLDispDop_setDate' },
							{ name: 'EvnPLDispDop_IsNotMammograf' },
							{ name: 'EvnPLDispDop_IsNotCito' }
						]),
						region: 'center',
						url: C_EPLDD_SAVE
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
								dataIndex: 'EvnVizitDispDop_setDate',
								header: lang['data_posescheniya'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'DopDispSpec_Name',
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
							id: 'EPLDDEF_EvnVizitDispDopGrid',
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

									var grid = Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid');

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											Ext.getCmp('EvnPLDispDopEditWindow').deleteEvnVizitDispDop();
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

											Ext.getCmp('EvnPLDispDopEditWindow').openEvnVizitDispDopEditWindow(action);

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
												Ext.getCmp('EPLDDEF_IsFinishCombo').focus(true, 200);
											}
											else
											{
												var usluga_grid = Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid');
												if ( usluga_grid.getStore().getCount() > 0 )
												{
													usluga_grid.focus();
													usluga_grid.getSelectionModel().selectFirstRow();
													usluga_grid.getView().focusRow(0);
												}
												else
												{
													Ext.getCmp('EPLDDEF_SaveButton').focus();
												}
											}
										break;
											
										case Ext.EventObject.PAGE_DOWN:
											var records_count = grid.getStore().getCount();

											if (records_count > 0 && grid.getSelectionModel().getSelected())
											{
												var selected_record = grid.getSelectionModel().getSelected();

												var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == selected_record.data.EvnVizitDispDop_id; });

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

												var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == selected_record.data.EvnVizitDispDop_id; });

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
									Ext.getCmp('EvnPLDispDopEditWindow').openEvnVizitDispDopEditWindow('edit');
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
										var evn_vizitdispdop_id = sm.getSelected().data.EvnVizitDispDop_id;
										var record_status = sm.getSelected().data.Record_Status;
										var toolbar = this.grid.getTopToolbar();
										if (evn_vizitdispdop_id)
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
									id: 'EvnVizitDispDop_id'
								}, [{
									mapping: 'EvnVizitDispDop_id',
									name: 'EvnVizitDispDop_id',
									type: 'int'
								}, {
									mapping: 'LpuSection_id',
									name: 'LpuSection_id',
									type: 'int'
								}, {
									mapping: 'DopDispSpec_id',
									name: 'DopDispSpec_id',
									type: 'int'
								}, {
									mapping: 'MedPersonal_id',
									name: 'MedPersonal_id',
									type: 'int'
								}, {
									dateFormat: 'd.m.Y',
									mapping: 'EvnVizitDispDop_setDate',
									name: 'EvnVizitDispDop_setDate',
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
									mapping: 'DopDispSpec_Name',
									name: 'DopDispSpec_Name',
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
									mapping: 'EvnVizitDispDop_IsSanKur',
									name: 'EvnVizitDispDop_IsSanKur',
									type: 'int'
								}, {
									mapping: 'EvnVizitDispDop_IsOut',
									name: 'EvnVizitDispDop_IsOut',
									type: 'int'
								}, {
									mapping: 'DopDispAlien_id',
									name: 'DopDispAlien_id',
									type: 'int'
								}, {
									mapping: 'EvnVizitDispDop_Recommendations',
									name: 'EvnVizitDispDop_Recommendations',
									type: 'string'
								}, {
									mapping: 'Record_Status',
									name: 'Record_Status',
									type: 'int'
								}]),
								url: C_EPLDD_VIZIT_LIST
							}),
							tbar: new sw.Promed.Toolbar({
								buttons: [{
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnVizitDispDopEditWindow('add');
									},
									iconCls: 'add16',
									text: BTN_GRIDADD,
									tooltip: BTN_GRIDADD_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnVizitDispDopEditWindow('edit');
									},
									iconCls: 'edit16',
									text: BTN_GRIDEDIT,
									tooltip: BTN_GRIDEDIT_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnVizitDispDopEditWindow('view');
									},
									iconCls: 'view16',
									text: BTN_GRIDVIEW,
									tooltip: BTN_GRIDVIEW_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').deleteEvnVizitDispDop();
									},
									iconCls: 'delete16',
									text: BTN_GRIDDEL,
									tooltip: BTN_GRIDDEL_TIP
								}]
							}),
							title: lang['osmotr_vracha-spetsialista']
						}),
						new Ext.grid.GridPanel({
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 100,
							columns: [{
								dataIndex: 'EvnUslugaDispDop_setDate',
								header: lang['issledovan'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'EvnUslugaDispDop_didDate',
								header: lang['rezultat'],
								hidden: false,
								renderer: Ext.util.Format.dateRenderer('d.m.Y'),
								resizable: false,
								sortable: true,
								width: 100
							}, {
								dataIndex: 'DopDispUslugaType_Name',
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
							height: 300,
							id: 'EPLDDEF_EvnUslugaDispDopGrid',
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

									var grid = Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid');

									switch (e.getKey())
									{
										case Ext.EventObject.DELETE:
											Ext.getCmp('EvnPLDispDopEditWindow').deleteEvnUslugaDispDop();
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
												var vizit_grid = Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid');
												if ( vizit_grid.getStore().getCount() > 0 )
												{
													vizit_grid.focus();
													vizit_grid.getSelectionModel().selectFirstRow();
													vizit_grid.getView().focusRow(0);
												}
												else
												{
													Ext.getCmp('EPLDDEF_IsFinishCombo').focus(true, 200);
												}
											}
											else
											{												
												Ext.getCmp('EPLDDEF_SaveButton').focus();
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

											Ext.getCmp('EvnPLDispDopEditWindow').openEvnUslugaDispDopEditWindow(action);

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

												var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == selected_record.data.EvnUslugaDispDop_id; });

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

												var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == selected_record.data.EvnUslugaDispDop_id; });

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
									Ext.getCmp('EvnPLDispDopEditWindow').openEvnUslugaDispDopEditWindow('edit');
								}
							},
							loadMask: true,
							region: 'south',
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
										var evn_uslugadispdop_id = sm.getSelected().data.EvnUslugaDispDop_id;
										var record_status = sm.getSelected().data.Record_Status;
										var toolbar = this.grid.getTopToolbar();
										if (evn_uslugadispdop_id)
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
									id: 'EvnUslugaDispDop_id'
								}, [{
									mapping: 'EvnUslugaDispDop_id',
									name: 'EvnUslugaDispDop_id',
									type: 'int'
								}, {
									dateFormat: 'd.m.Y',
									mapping: 'EvnUslugaDispDop_setDate',
									name: 'EvnUslugaDispDop_setDate',
									type: 'date'
								}, {
									dateFormat: 'd.m.Y',
									mapping: 'EvnUslugaDispDop_didDate',
									name: 'EvnUslugaDispDop_didDate',
									type: 'date'
								}, {
									mapping: 'DopDispUslugaType_id',
									name: 'DopDispUslugaType_id',
									type: 'int'
								}, {
									mapping: 'DopDispUslugaType_Name',
									name: 'DopDispUslugaType_Name',
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
								}, {
									mapping: 'RateGrid_Data',
									name: 'RateGrid_Data',
									type: 'string'
								}, {
									mapping: 'RateGrid_DataNumber',
									name: 'RateGrid_DataNumber',
									type: 'string'
								}]),
								url: C_EPLDD_USLUGA_LIST
							}),
							tbar: new sw.Promed.Toolbar({
								buttons: [{
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnUslugaDispDopEditWindow('add');
									},
									iconCls: 'add16',
									text: BTN_GRIDADD,
									tooltip: BTN_GRIDADD_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnUslugaDispDopEditWindow('edit');
									},
									iconCls: 'edit16',
									text: BTN_GRIDEDIT,
									tooltip: BTN_GRIDEDIT_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').openEvnUslugaDispDopEditWindow('view');
									},
									iconCls: 'view16',
									text: BTN_GRIDVIEW,
									tooltip: BTN_GRIDVIEW_TIP
								}, {
									handler: function() {
										Ext.getCmp('EvnPLDispDopEditWindow').deleteEvnUslugaDispDop();
									},
									iconCls: 'delete16',
									text: BTN_GRIDDEL,
									tooltip: BTN_GRIDDEL_TIP
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
				id: 'EPLDDEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDDEF_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var usluga_grid = Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid');
					var vizit_grid = Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid');
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
					Ext.getCmp('EPLDDEF_IsFinishCombo').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispDop();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDDEF_PrintButton',
				tabIndex: 2407,
				text: BTN_FRMPRINT
			}, {
				handler: function() {
					this.printEvnPLDispDop(true);
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDDEF_PrintBlankButton',
				tabIndex: 2407,
				text: lang['pechat_blanka']
			}, {
				hidden: false,
				handler: function() {
					this.printEvnPLDispDopPassport();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDDEF_PrintPassportButton',
				tabIndex: 2408,
				text: lang['pechat_pasporta_zdorovya']
			}, {
				handler: function() {
					var current_window = Ext.getCmp('EvnPLDispDopEditWindow');
					var person_birthday = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Birthday');
					var person_surname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Surname');
					var person_firname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Firname');
					var person_secname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Secname');
					var params = {
						onHide: function() {
							this.focus();
						}.createDelegate(this),
						Person_Birthday: person_birthday,
						Person_Firname: person_firname,
						Person_Secname: person_secname,
						Person_Surname: person_surname,
						Person_id: current_window.findById('EPLDDEF_Person_id').getValue(),
						Server_id: current_window.findById('EPLDDEF_Server_id').getValue(),
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
				id: 'EPLDDEF_DispButton',
				tabIndex: 2408,
				text: lang['dispansernyiy_uchet']
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDDEF_CancelButton',
				onTabAction: function() {
					Ext.getCmp('EPLDDEF_OkvedCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDDEF_DispButton').focus(true, 200);
				},
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispDopEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispDopEditWindow');
			var tabbar = current_window.findById('EPLDDEF_EvnPLTabbar');

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
	openEvnVizitDispDopEditWindow: function(action) {
		if ( getWnd('swEvnVizitDispDopEditWindow').isVisible() )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = this.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Age');

		var selected_record = this.findById('EPLDDEF_EvnVizitDispDopGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = this.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispDop_id = swGenTempId(this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore(), 'EvnVizitDispDop_id');
			params.Record_Status = 0;
			var UsedDopDispSpec = Array();
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.get('EvnVizitDispDop_setDate');
				else
					if ( rec.get('EvnVizitDispDop_setDate') > max_date )
						max_date = rec.get('EvnVizitDispDop_setDate');
					
				UsedDopDispSpec[rec.get('DopDispSpec_id')] = 1;
			});
			params['UsedDopDispSpec']=UsedDopDispSpec;
			
			var UsedDopDispUslugaType = Array();
			this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispDop_didDate;
				else
					if ( rec.data.EvnUslugaDispDop_didDate > max_date )
						max_date = rec.data.EvnUslugaDispDop_didDate;
				UsedDopDispUslugaType[rec.data.DopDispUslugaType_id]=1;
			});
			params['UsedDopDispUslugaType']=UsedDopDispUslugaType;

			params['EvnVizitDispDop_IsSanKur_Test'] = false;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.data.EvnVizitDispDop_IsSanKur == 2 && rec.data.DopDispSpec_id != 1 )
					params['EvnVizitDispDop_IsSanKur_Test'] = true;
			});
			
			params['Max_HealthKind_id'] = -1;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.get('HealthKind_id') > 0 && rec.get('DopDispSpec_id') != 1 ) {
					if ( rec.get('HealthKind_id') > params['Max_HealthKind_id'] ) {
						params['Max_HealthKind_id'] = rec.get('HealthKind_id');
					}
				}
			});

			params.Not_Z_Group_Diag = false;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.get('DopDispSpec_id') != 1 ) {
					var diag_code = rec.get('Diag_Code').substr(0, 3);

					if ( diag_code.length > 0 && !diag_code.inlist(['Z00', 'Z01', 'Z02', 'Z04', 'Z10']) ) {
						params.Not_Z_Group_Diag = true;
					}
				}
			});
		}
		else if ( (action == 'edit') || (action == 'view') )
		{
			if ( !this.findById('EPLDDEF_EvnVizitDispDopGrid').getSelectionModel().getSelected() )
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispDop_id == null || selected_record.data.EvnVizitDispDop_id == '' )
				return;
			
			params = selected_record.data;
			
			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			var UsedDopDispSpec = Array();
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnVizitDispDop_setDate;
				else
					if ( rec.data.EvnVizitDispDop_setDate > max_date )
						max_date = rec.data.EvnVizitDispDop_setDate;
				if (rec != selected_record)
					UsedDopDispSpec[rec.data.DopDispSpec_id] = 1;
			});
			params['UsedDopDispSpec'] = UsedDopDispSpec;
			
			var UsedDopDispUslugaType = Array();
			this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				if ( max_date == false )
					max_date = rec.data.EvnUslugaDispDop_didDate;
				else
					if ( rec.data.EvnUslugaDispDop_didDate > max_date )
						max_date = rec.data.EvnUslugaDispDop_didDate;
				UsedDopDispUslugaType[rec.data.DopDispUslugaType_id]=1;
			});
			params['UsedDopDispUslugaType']=UsedDopDispUslugaType;

			params['Max_HealthKind_id'] = -1;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.get('HealthKind_id') > 0 && rec.get('DopDispSpec_id') != 1 ) {
					if ( rec.get('HealthKind_id') > params['Max_HealthKind_id'] ) {
						params['Max_HealthKind_id'] = rec.get('HealthKind_id');
					}
				}
			});

			params['Not_Z_Group_Diag'] = false;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.data.DopDispSpec_id != 1 )
				{
					var diag_code = rec.data.Diag_Code.substr(0,3);
					if ( !diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) );
						params['Not_Z_Group_Diag'] = true;
				}
			});
			
			params['EvnVizitDispDop_IsSanKur_Test'] = false;
			this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.data.EvnVizitDispDop_IsSanKur == 2 && rec.data.DopDispSpec_id != 1 )
					params['EvnVizitDispDop_IsSanKur_Test'] = true;
			});
		}
		else
		{
			return false;
		}
		getWnd('swEvnVizitDispDopEditWindow').show({
			archiveRecord: this.archiveRecord,
			action: action,
			callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);
				});

				if ( add_flag == true )
				{
					// удаляем пустую строку если она есть
					if ( this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().getCount() == 1 )
					{
						var selected_record = this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().getAt(0);
						if ( !selected_record.get('EvnVizitDispDop_id') == null || selected_record.get('EvnVizitDispDop_id') == '' )
							this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().removeAll();
					}
					this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().clearFilter();
					this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().loadData(data, add_flag);
					this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().filterBy(function(record) {
						if ( record.get('Record_Status') != 3 )
						{
							return true;
						}
					});
				}
				else {
					index = this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == data[0].EvnVizitDispDop_id; });

					if (index == -1)
					{
						return false;
					}

					var record = this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().getAt(index);
					for (i = 0; i < vizit_fields.length; i++)
					{
						record.set(vizit_fields[i], data[0][vizit_fields[i]]);
					}

					record.commit();
				}

				var max_health_kind_id = -1;

				this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
					if ( rec.get('HealthKind_id') > max_health_kind_id && rec.get('DopDispSpec_id') != 1 ) {
						max_health_kind_id = rec.get('HealthKind_id');
					}
				});

				if ( max_health_kind_id > 0 ) {
					this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().each(function(rec) {
						if ( rec.get('DopDispSpec_id') == 1 && rec.get('HealthKind_id') < max_health_kind_id ) {
							rec.set('HealthKind_id', max_health_kind_id);
							rec.commit();
						}
					});
				}

				return true;
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				this.findById('EPLDDEF_EvnVizitDispDopGrid').getSelectionModel().selectFirstRow();
				this.findById('EPLDDEF_EvnVizitDispDopGrid').getView().focusRow(0);
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
	openEvnUslugaDispDopEditWindow: function(action) {
		var current_window = this;

		if (getWnd('swEvnUslugaDispDopEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_surname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Surname');
		var person_firname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var sex_id = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Sex_id');
		var age = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Age');
		
		if (current_window.action == 'add')
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		else
			var set_date = Date.parseDate(Ext.getCmp('EPLDDEF_EvnPLDispDop_setDate').getValue(), 'd.m.Y');			

		if (action == 'add')
		{
			params = current_window.params;

			params.EvnUslugaDispDop_id = swGenTempId(this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore(), 'EvnUslugaDispDop_id');
			params.Record_Status = 0;
			var UsedDopDispUslugaType = Array();
			current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				UsedDopDispUslugaType[rec.data.DopDispUslugaType_id]=1;
			});
			params['UsedDopDispUslugaType']=UsedDopDispUslugaType;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
				return;

			params = selected_record.data;
			var UsedDopDispUslugaType = Array();
			current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record)
					UsedDopDispUslugaType[rec.data.DopDispUslugaType_id]=1;
			});
			params['UsedDopDispUslugaType']=UsedDopDispUslugaType;
		}
		else
		{
			return false;
		}

		getWnd('swEvnUslugaDispDopEditWindow').show({
			archiveRecord: this.archiveRecord,
			action: action,
			callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
				{
					// удаляем пустую строку если она есть					
					if ( current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().getCount() == 1 )
					{
						var selected_record = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
							current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().removeAll();
					}
					
					current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().clearFilter();
					current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().loadData(data, add_flag);
					current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
				}
				else {
					index = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == data[0].EvnUslugaDispDop_id; });

					if (index == -1)
					{
						return false;
					}

					var record = current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().getAt(index);

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
				current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getSelectionModel().selectFirstRow();
				current_window.findById('EPLDDEF_EvnUslugaDispDopGrid').getView().focusRow(0);				
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
			UserMedStaffFact_id: this.UserMedStaffFact_id,
			EvnPLDispDop_IsNotMammograf: Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotMammograf').getValue(),
			EvnPLDispDop_IsNotCito: Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotCito').getValue()
		});
	},
	openPersonCureHistoryWindow: function() {
		var current_window = this;
		var form = current_window.findById('EvnPLDispDopEditForm');

		if (getWnd('swPersonCureHistoryWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_prosmotra_istorii_lecheniya_cheloveka_uje_otkryito']);
			return false;
		}

		var person_id = form.findById('EPLDDEF_Person_id').getValue();
		var server_id = form.findById('EPLDDEF_Server_id').getValue();

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
	printEvnPLDispDop: function(print_blank) {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			if ( print_blank === true )
				this.doSave(true, 1, true);
			else
				this.doSave(true);
		}
		else if (this.action == 'view')
		{
			var evn_pl_id = this.findById('EPLDDEF_EvnPLDispDop_id').getValue();
			var server_id = this.findById('EPLDDEF_Server_id').getValue();

			printBirt({
				'Report_FileName': 'f131u_dd_09.rptdesign',
				'Report_Params': '&paramEvnPLDispDop=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
	},
	printEvnPLDispDopPassport: function(print_blank) {
		if ((this.action == 'add') || (this.action == 'edit'))
		{
			this.doSave(true, 1, true, 'passport');
		} else if (this.action == 'view') {
			var evn_pl_id = this.findById('EPLDDEF_EvnPLDispDop_id').getValue();
			var server_id = this.findById('EPLDDEF_Server_id').getValue();

			window.open('/?c=EvnPLDispDop&m=printEvnPLDispDopPassport&EvnPLDispDop_id=' + evn_pl_id + '&Server_id=' + server_id, '_blank');
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispDopEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		
		this.loadMask = new Ext.LoadMask(Ext.get('EvnPLDispDopEditWindow'), { msg: LOAD_WAIT });
		this.loadMask.show();
		
		var current_window = this;

		this.restore();
		this.center();
		this.maximize();

		// this.findById('EPLDDEF_EvnPLTabbar').setActiveTab(4);

		var form = this.findById('EvnPLDispDopEditForm');
		form.getForm().reset();

		Ext.getCmp('EPLDDEF_PrikLpuCombo').setAllowBlank(true);
		
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
		form.getForm().setValues(arguments[0]);

		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}

		if ( this.action == 'add' ) {
			sw.swMsg.alert(lang['soobschenie'], lang['dobavlenie_zaprescheno']);
			this.loadMask.hide();
			return false;
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

		var inf_frame_is_loaded = false;
		var person_id = form.findById('EPLDDEF_Person_id').getValue();
		var server_id = form.findById('EPLDDEF_Server_id').getValue();

		this.findById('EPLDDEF_PersonInformationFrame').load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				this.loadMask.hide(); 
				inf_frame_is_loaded = true; 
				current_window.findById('EPLDDEF_OkvedCombo').focus(false);
				if ( current_window.action == 'add' )
				{
					var Lpu_id = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Lpu_id');
					if ( Lpu_id && Lpu_id > 0 )
						current_window.findById('EPLDDEF_PrikLpuCombo').setValue(Lpu_id);
				}
				
				var sex_id = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Sex_id');
				var age = current_window.findById('EPLDDEF_PersonInformationFrame').getFieldValue('Person_Age');

				if ( age >= 40 && sex_id == 2 )
					Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotMammograf').enable();
				else
					Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotMammograf').disable();

				if ( sex_id == 2 )
					Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotCito').enable();
				else
					Ext.getCmp('EPLDDEF_EvnPLDispDop_IsNotCito').disable();
			}.createDelegate(this)
		});

		this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDDEF_EvnVizitDispDopGrid'));
		this.findById('EPLDDEF_EvnVizitDispDopGrid').getSelectionModel().selectFirstRow();

		this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().removeAll();
		LoadEmptyRow(this.findById('EPLDDEF_EvnUslugaDispDopGrid'));
		this.findById('EPLDDEF_EvnUslugaDispDopGrid').getSelectionModel().selectFirstRow();

		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && getRegionNick() === 'kareliya' && arguments[0].EvnPLDispDop_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispDop_id,
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
					this.onShow(inf_frame_is_loaded);
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			this.onShow(inf_frame_is_loaded);
		}	

	},
	
	onShow: function(inf_frame_is_loaded){
		var current_window = this;
		var form = this.findById('EvnPLDispDopEditForm');

		var okved_combo = this.findById('EPLDDEF_OkvedCombo');
		var isbud_combo = this.findById('EPLDDEF_IsBudCombo');
		var attach_combo = this.findById('EPLDDEF_AttachTypeCombo');
		var priklpu_combo = this.findById('EPLDDEF_PrikLpuCombo');
		var isfinish_combo = this.findById('EPLDDEF_IsFinishCombo');

		var evnpldispdop_id = form.findById('EPLDDEF_EvnPLDispDop_id').getValue();
		var person_id = form.findById('EPLDDEF_Person_id').getValue();
		var server_id = form.findById('EPLDDEF_Server_id').getValue();

		switch (this.action)
		{
			case 'add':
				this.setTitle(WND_POL_EPLDDADD);
				this.enableEdit(true);
				if ( inf_frame_is_loaded )
					this.loadMask.hide();				
				this.findById('EPLDDEF_IsFinishCombo').setValue(1);
				this.findById('EPLDDEF_AttachTypeCombo').setValue(2);				
				this.findById('EPLDDEF_OkvedCombo').focus(false);
				break;
			case 'edit':
				this.setTitle(WND_POL_EPLDDEDIT);
				this.enableEdit(true);

				this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().load({ 
					params: { EvnPLDispDop_id: evnpldispdop_id },
					callback: function() {
						if ( Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('EPLDDEF_EvnVizitDispDopGrid'));
					}
				});
				this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().load({ 
					params: { EvnPLDispDop_id: evnpldispdop_id },
					callback: function() {
						if ( Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid').getStore().getCount() == 0 )
							LoadEmptyRow(Ext.getCmp('EPLDDEF_EvnUslugaDispDopGrid'));
					}
				});

				form.getForm().load({
					failure: function() {
						swEvnPLDispDopEditWindow.hide();
						if ( inf_frame_is_loaded )
							this.loadMask.hide();
					}.createDelegate(this),
					params: {
						EvnPLDispDop_id: evnpldispdop_id,
						archiveRecord: current_window.archiveRecord
					},
					success: function() {
						var evnpldispdop_lpu_id = priklpu_combo.getValue();
						if ( inf_frame_is_loaded )
							this.loadMask.hide();
						Ext.getCmp('EPLDDEF_PrikLpuCombo').setAllowBlank( Ext.getCmp('EPLDDEF_AttachTypeCombo').getValue() != 1 );
					}.createDelegate(this),
					url: C_EPLDD_LOAD
				});
				this.findById('EPLDDEF_OkvedCombo').focus(false);
				break;

			case 'view':
				this.setTitle(WND_POL_EPLDDVIEW);
				this.enableEdit(false);

				this.findById('EPLDDEF_EvnVizitDispDopGrid').getStore().load({ params: { EvnPLDispDop_id: evnpldispdop_id } });
				this.findById('EPLDDEF_EvnUslugaDispDopGrid').getStore().load({ params: { EvnPLDispDop_id: evnpldispdop_id } });

				form.getForm().load({
					failure: function() {
						if ( inf_frame_is_loaded )
							this.loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { current_window.hide(); } );
					}.createDelegate(this),
					params: {
						EvnPLDispDop_id: evnpldispdop_id,
						archiveRecord: current_window.archiveRecord
					},
					success: function() {
						var evnpldispdop_lpu_id = priklpu_combo.getValue();
						if ( inf_frame_is_loaded )
							this.loadMask.hide();
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
					}.createDelegate(this),
					url: C_EPLDD_LOAD
				});

				this.buttons[1].focus();
				break;
		};
		
		form.getForm().clearInvalid();
		this.doLayout();
	},
	title: WND_POL_EPLDDADD,
	width: 800
});
