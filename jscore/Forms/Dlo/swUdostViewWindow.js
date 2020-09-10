/**
* swUdostViewWindow - окно просмотра удостоверений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-06.08.2009
* @comment      Префикс для id компонентов UVW (UdostViewWindow)
*
*
* Использует: окно поиска человека (swPersonSearchWindow)
*             окно редактирования удостоверения (swEvnUdostEditWindow)
*/

sw.Promed.swUdostViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	listeners: {
		activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
		}
	},
	getDataFromUec: function(uec_data, person_data){
		this.findById('UVW_Filter_PersonSurname').setRawValue(uec_data.surName);
		this.setFilter();
		this.refreshUdostGrid();
	},
	getDataFromBdz: function(bdz_data, person_data){
		this.findById('UVW_Filter_PersonSurname').setRawValue(bdz_data.surName);
		this.setFilter();
		this.refreshUdostGrid();
	},
	clearFilter: function() {
		this.findById('UVW_Filter_PersonSurname').setRawValue('');
		this.findById('UVW_Filter_WorkPeriod').setRawValue('');
		this.setFilter();
		this.findById('UVW_EvnUdostGrid').getGrid().getStore().removeAll();
		this.findById('UVW_Filter_PersonSurname').focus(false);
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnUdost: function() {
		var current_window = this;
		var udost_grid = current_window.findById('UVW_EvnUdostGrid').getGrid();

		if (!udost_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var selected_record = udost_grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								udost_grid.getStore().remove(selected_record);

								if (udost_grid.getStore().getCount() == 0)
								{
									LoadEmptyRow(udost_grid);
								}

								udost_grid.getView().focusRow(0);
								udost_grid.getSelectionModel().selectFirstRow();
							}
							else
							{
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										var index = udost_grid.getStore().indexOf(selected_record);
										udost_grid.getView().focusRow(index);
										udost_grid.getSelectionModel().selectRow(index);
									},
									icon: Ext.Msg.WARNING,
									msg: lang['pri_udalenii_udostovereniya_lgotnika_voznikli_oshibki'],
									title: lang['oshibka']
								});
							}
						},
						params: {
							EvnUdost_id: selected_record.get('EvnUdost_id')
						},
						url: C_EVNUDOST_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_udostoverenie_lgotnika'],
			title: lang['vopros']
		})
	},
	draggable: true,
	getRecordsCount: function() {
		var current_window = this;

		var loadMask = new Ext.LoadMask(Ext.get('UdostViewWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = new Object();

		post.Person_Surname = current_window.findById('UVW_Filter_PersonSurname').getRawValue();
		post.Work_Period = current_window.findById('UVW_Filter_WorkPeriod').getRawValue();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: '/?c=EvnUdost&m=getRecordsCount'
		});
	},
	height: 400,
	id: 'UdostViewWindow',
	initComponent: function() {

        var _this = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.getRecordsCount();
				},
				// iconCls: 'resetsearch16',
				// tabIndex: TABINDEX_PRIVSF + 92,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: '<u>З</u>акрыть'
			}],
			items: [ new Ext.Panel({
				autoHeight: true,
				bodyStyle: 'padding: 5px 5px 0;',
				border: false,
				buttonAlign: 'left',
				buttons: [{
					handler: function() {
						this.ownerCt.ownerCt.setFilter();
						this.ownerCt.ownerCt.refreshUdostGrid();
					},
					text: lang['pokazat'],
					tooltip: lang['pokazat_udostovereniya']
				}, {
					handler: function() {
						this.ownerCt.ownerCt.clearFilter();
					},
					text: lang['ochistit_filtr'],
					tooltip: lang['ochistit_filtr_alt_+_ch']
				}],
//				collapsible: false,
                                collapsible: true,
                                collapsed: false,
                                floatable: false,
                                titleCollapse: true,
                                title: '<div>Фильтры</div>',
				id: 'UVW_FilterForm',
				labelWidth: 120,
				layout: 'form',
				region: 'north',
//				title: 'Параметры',
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						labelWidth: 120,
						border: false,
						items: [{
							allowBlank: true,
							fieldLabel: lang['rabochiy_period'],
							id: 'UVW_Filter_WorkPeriod',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							width: 170,
							xtype: 'daterangefield'
						}, {
							allowBlank: true,
							enableKeyEvents: true,
							fieldLabel: lang['poisk_po_familii'],
							id: 'UVW_Filter_PersonSurname',
							listeners: {
								'keydown': function (inp, e) {
									var current_window = inp.ownerCt.ownerCt.ownerCt.ownerCt;

									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										current_window.setFilter();
										current_window.refreshUdostGrid();
									}
								}
							},
							width: 340,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						width: 65,
						layout: 'form',
						style: 'padding-left: 5px',
						items: [{											
							xtype: 'button',
							hidden: !getGlobalOptions()['card_reader_is_enable'],
							cls: 'x-btn-large',
							iconCls: 'idcard32',
							tooltip: lang['identifitsirovat_po_karte_i_nayti'],
							handler: function() {
								var win = this;
								// 1. пробуем считать с эл. полиса
								sw.Applets.AuthApi.getEPoliceData({callback: function(bdzData, person_data) {
									if (bdzData) {
										win.getDataFromBdz(bdzData, person_data);
									} else {
										// 2. пробуем считать с УЭК
										var successRead = false;
										if (sw.Applets.uec.checkPlugin()) {
											successRead = sw.Applets.uec.getUecData({callback: this.getDataFromUec.createDelegate(this), onErrorRead: function() {
												sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
												return false;
											}});
										}
										// 3. если не считалось, то "Не найден плагин для чтения данных картридера либо не возможно прочитать данные с карты"
										if (!successRead) {
											sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
											return false;
										}
									}
								}});
							}.createDelegate(this)
						}]
					}]					
				}]
			}),
			this.UdostGridPanel = new sw.Promed.ViewFrame({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 100,
				border: false,
				id: 'UVW_EvnUdostGrid',
                autoLoadData: false,
                tbar: false,
				loadMask: true,
                stringfields: [
                    { name: 'EvnUdost_id', type: 'string', header: 'EvnUdost_id',  key: true },
                    { name: 'Person_id', type: 'string', header: 'Person_id',  hidden: true },
                    { name: 'Person_Surname', type: 'string', header: lang['familiya'], sort: true , width: 100 },
                    { name: 'Person_Firname', type: 'string', header: lang['imya'], sort: true , width: 100 },
                    { name: 'Person_Secname', type: 'string', header: lang['otchestvo'], sort: true , width: 100 },
                    { name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], sort: true , width: 100 },
                    { name: 'Person_deadDT', type: 'date', header: lang['data_smerti'], sort: true , width: 100 },
                    { name: 'PrivilegeType_Code', type: 'string', header: lang['kod_kategorii'], sort: true , width: 120 },
                    { name: 'EvnUdost_setDate', type: 'date', header: lang['vyidano'], sort: true , width: 100 },
                    { name: 'EvnUdost_disDate', type: 'date', header: lang['zakryito'], sort: true , width: 100 },
                    { name: 'Privilege_Refuse', type: 'checkcolumn', header: lang['otkaz'], sort: true , width: 70 },
                    { name: 'EvnUdost_Ser', type: 'string', header: lang['seriya'], sort: true , width: 80 },
                    { name: 'EvnUdost_Num', type: 'string', header: lang['nomer'], sort: true , width: 100 }
                ],
                actions: [
                    { name: 'action_add', handler: function(){ _this.openEvnUdostEditWindow('add');}},
                    { name: 'action_edit', handler: function(){ _this.openEvnUdostEditWindow('edit');} },
                    { name: 'action_view', handler: function(){ _this.openEvnUdostEditWindow('view');} },
                    { name: 'action_delete', handler: function(){ _this.deleteEvnUdost();} },
                    { name: 'action_refresh' },
                    { name: 'action_print' }
                ],
				region: 'center',
                dataUrl: '/?c=EvnUdost&m=loadUdostList',
				stripeRows: true
			})]
		});
		sw.Promed.swUdostViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UdostViewWindow');

			switch (e.getKey())
			{
				case Ext.EventObject.P:
					current_window.hide();
					break;

				case Ext.EventObject.X:
					current_window.clearFilter();
					break;
			}
		},
		key: [
			Ext.EventObject.P,
			Ext.EventObject.X
		],
		stopEvent: true
	}],
	layout: 'border',
        listeners: {
            'resize': function (win, nW, nH, oW, oH) {
                win.findById('UVW_FilterForm').setWidth(nW - 5);
            }
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: false,
	openEvnUdostEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (action == 'add' && getWnd('swPersonSearchWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnUdostEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_udostovereniya_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var udost_grid = current_window.findById('UVW_EvnUdostGrid').getGrid();

		params.action = action;
		params.callback = function(data) {
			if (!data || !data.EvnUdostData)
			{
				current_window.refreshUdostGrid();
			}
			else
			{
				// Добавить или обновить запись в udost_grid
				var record = udost_grid.getStore().getById(data.EvnUdostData.EvnUdost_id);

				if (record)
				{
					// Обновление
					record.set('EvnUdost_disDate', data.EvnUdostData.EvnUdost_disDate);
					record.set('EvnUdost_Num', data.EvnUdostData.EvnUdost_Num);
					record.set('EvnUdost_Ser', data.EvnUdostData.EvnUdost_Ser);
					record.set('EvnUdost_setDate', data.EvnUdostData.EvnUdost_setDate);
					record.set('Person_Birthday', data.EvnUdostData.Person_Birthday);
					record.set('Person_Firname', data.EvnUdostData.Person_Firname);
					record.set('Person_id', data.EvnUdostData.Person_id);
					record.set('Person_Secname', data.EvnUdostData.Person_Secname);
					record.set('Person_Surname', data.EvnUdostData.Person_Surname);
					record.set('PersonEvn_id', data.EvnUdostData.PersonEvn_id);
					// record.set('Privilege_Refuse', data.EvnUdostData.Privilege_Refuse);
					record.set('PrivilegeType_Code', data.EvnUdostData.PrivilegeType_Code);
					record.set('Server_id', data.EvnUdostData.Server_id);

					record.commit();
				}
				else
				{
					// Добавление
					if (udost_grid.getStore().getCount() == 1 && !udost_grid.getStore().getAt(0).get('EvnUdost_id'))
					{
						udost_grid.getStore().removeAll();
					}

					udost_grid.getStore().loadData([ data.EvnUdostData ], true);
				}
			}
		};

		if (action == 'add')
		{
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swEvnUdostEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else
		{
			if (!udost_grid.getSelectionModel().getSelected())
			{
				return false;
			}

			var record = udost_grid.getSelectionModel().getSelected();

			if (!record.get('EvnUdost_id'))
			{
				return false;
			}

			params.EvnUdost_id = record.get('EvnUdost_id');
			params.onHide = function() {
				udost_grid.getSelectionModel().selectRow(udost_grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnUdostEditWindow').show(params);
		}
	},
	personSurnameFilter: null,
	plain: true,
	refreshUdostGrid: function(params) {
		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
	
		var params = new Object();

		params.Person_Surname = this.personSurnameFilter;
		
		if ( soc_card_id )
		{
			var params = {
				soc_card_id: soc_card_id
			};
		}
		
		params.Work_Period = this.workPeriodFilter;

		this.findById('UVW_EvnUdostGrid').getGrid().getStore().removeAll();
		this.findById('UVW_EvnUdostGrid').getGrid().getStore().load({ params: params });
	},
	resizable: true,
	setFilter: function() {
		this.personSurnameFilter = this.findById('UVW_Filter_PersonSurname').getValue();
		this.workPeriodFilter = this.findById('UVW_Filter_WorkPeriod').getRawValue();
	},
	show: function() {
		sw.Promed.swUdostViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.maximize();

		this.personSurnameFilter = '';
		this.workPeriodFilter = '';

		var current_date = '';
		var date = new Date();

		if (date.getDate() < 10)
			current_date = current_date + '0';

		current_date = current_date + date.getDate() + '.';

		if (date.getMonth() < 9)
			current_date = current_date + '0';

		current_date = current_date + (date.getMonth() + 1) + '.';
		current_date = current_date + date.getFullYear();

		this.findById('UVW_Filter_PersonSurname').setRawValue('');
		this.findById('UVW_Filter_WorkPeriod').setRawValue(current_date + ' - ' + current_date);

		var ARMType = '';
		if(arguments[0].ARMType)
			ARMType = arguments[0].ARMType;
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly);
			this.viewOnly = arguments[0].viewOnly;
		if(ARMType == 'spesexpertllo' || ARMType == 'adminllo' || (this.viewOnly === true))
		{
			this.UdostGridPanel.setReadOnly(true);
			this.UdostGridPanel.setActionHidden('action_add', true);
			this.UdostGridPanel.setActionHidden('action_delete', true);
			this.UdostGridPanel.setActionHidden('action_edit', true);
		}
		else {
			this.UdostGridPanel.setReadOnly(false);
			this.UdostGridPanel.setActionHidden('action_add', false);
			this.UdostGridPanel.setActionHidden('action_delete', false);
			this.UdostGridPanel.setActionHidden('action_edit', false);
		}

		this.setFilter();
		this.refreshUdostGrid();
	},
	title: WND_DLO_UDOSTSEARCH,
	width: 700,
	workPeriodFilter: null
});