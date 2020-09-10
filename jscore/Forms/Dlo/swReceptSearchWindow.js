/**
* swReceptSearchWindow - окно поиска рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
* @comment      Префикс для id компонентов ERS (EvnReceptSearch)
* @comment      tabIndex от 301 до 400
*/

var EvnReceptSearchFilterForm;
var EvnReceptSearchViewGrid;
var EvnReceptSearchGridStore;

sw.Promed.swReceptSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	closeAction : "hide",
	id : "EvnReceptSearchWindow",
	modal: false,
	maximizable: true,
	height: 500,
	width: 670,
	layout: 'border',
	printEvnRecept: function() {
		var grid = this.findById('EvnReceptSearchViewGrid');

		if (!grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var evn_recept_id = grid.getSelectionModel().getSelected().get('EvnRecept_id');
		var server_id = grid.getSelectionModel().getSelected().get('Server_id');

		window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id + '&Server_id=' + server_id, '_blank');
	},
	getRecordsCount: function() {
		var current_window = this;
		var form = current_window.findById('EvnSearchForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk_lgot'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('EvnReceptSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

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
			url: C_SEARCH_RECCNT
		});
	},
	SearchRecept: function()
	{
		if ( EvnReceptSearchFilterForm.isEmpty() )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { EvnReceptSearchFilterForm.getForm().findField(0).focus()});
			return false;
		}
	
		if (EvnReceptSearchFilterForm.getForm().isValid() ) {
			var post = getAllFormFieldValues(EvnReceptSearchFilterForm);
			EvnReceptSearchViewGrid.store.removeAll();
	
			EvnReceptSearchViewGrid.getStore().baseParams = getAllFormFieldValues(EvnReceptSearchFilterForm);
			post.limit = 100;
			post.start = 0;
	
			EvnReceptSearchViewGrid.store.load({
				params: post,
				callback: function(r, opt ) {
					var len = r.length;
					if ( len > 0 )
					{
						EvnReceptSearchViewGrid.focus();
						EvnReceptSearchViewGrid.getView().focusRow(0);
						EvnReceptSearchViewGrid.getSelectionModel().selectFirstRow();
						// Элементы типа tbtext не берутся по id? o_O
						EvnReceptSearchViewGrid.getTopToolbar().items.items[10].el.innerHTML = '1 / ' + len;
					}
					else {
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_DeleteReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_EditReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_ViewReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.items[10].el.innerHTML = '0 / 0';
					}
				}
			});
		}
		else {
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING},
				function () { EvnReceptSearchFilterForm.getForm().findField(0).focus()}
			);
		}
	},
	refreshReceptList: function()
	{
		if(EvnReceptSearchViewGrid.getStore().getCount()!=0) {
			EvnReceptSearchViewGrid.getStore().reload();
			EvnReceptSearchViewGrid.getView().focusRow(0);
			EvnReceptSearchViewGrid.getSelectionModel().selectFirstRow();
		}
	},
	getLastFieldOnCurrentTab: function() {
		return getLastFieldOnForm(EvnReceptSearchFilterForm.findById('ERSTabPanel').getActiveTab());
	},
	printEvnReceptList: function() {
		var grid = this.findById('EvnReceptSearchViewGrid');
		Ext.ux.GridPrinter.print(grid);
	},
	listeners : {
		'beforeshow' : function() {
			EvnReceptSearchFilterForm.getForm().reset();
			EvnReceptSearchViewGrid.store.removeAll();
		},
		'show' : function() {
			this.restore();
			this.center();
			this.maximize();

			EvnReceptSearchFilterForm.findById('ERS_Person_Surname').focus(true, 500);
			loadComboOnce(EvnReceptSearchFilterForm.findById('ERS_MedPersonalCombo'), lang['meditsinskiy_personal']);
			// Переключение на первый таб
			this.findById('ERSTabPanel').setActiveTab(0);
			EvnReceptSearchFilterForm.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
			EvnReceptSearchFilterForm.getForm().getEl().dom.method = "post";
			EvnReceptSearchFilterForm.getForm().getEl().dom.target = "_blank";
			EvnReceptSearchFilterForm.getForm().standardSubmit = true;
			
			loadComboOnce(EvnReceptSearchFilterForm.findById('ERS_OrgFarmacy'), lang['apteki']);
		},
		'hide' : function() {
			EvnReceptSearchViewGrid.store.removeAll();
		}
	},
	initComponent : function() {
		this.EvnReceptSearchGridStore = new Ext.data.Store({
			id: 'EvnReceptSearchGridStore',
			url: C_SEARCH,
			reader : new Ext.data.JsonReader({
				id : "EvnRecept_id",
				root : "data",
				totalProperty : "totalCount"
			}, [{
				name : "EvnRecept_id",
				mapping : "EvnRecept_id",
				type : "int"
			}, {
				name : "Person_id",
				mapping : "Person_id",
				type : "int"
			}, {
				name : "PersonEvn_id",
				mapping : "PersonEvn_id",
				type : "int"
			}, {
				name : "Server_id",
				mapping : "Server_id",
				type : "int"
			}, {
				name : "Person_Surname",
				mapping : "Person_Surname",
				type : "string"
			}, {
				name : "Person_Firname",
				mapping : "Person_Firname",
				type : "string"
			}, {
				name : "Person_Secname",
				mapping : "Person_Secname",
				type : "string"
			}, {
				name : "Person_Birthday",
				mapping : "Person_Birthday",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_setDate",
				mapping : "EvnRecept_setDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_Ser",
				mapping : "EvnRecept_Ser",
				type : "string"
			}, {
				name : "EvnRecept_Num",
				mapping : "EvnRecept_Num",
				type : "string"
			}, {
				name : "EvnRecept_Kolvo",
				mapping : "EvnRecept_Kolvo",
				type : "float"
			}, {
				name : "MedPersonal_Fio",
				mapping : "MedPersonal_Fio",
				type : "string"
			}, {
				name : "Drug_Name",
				mapping : "Drug_Name",
				type : "string"
			}/*, {
				name : "EvnRecept_otpDate",
				mapping : "EvnRecept_otpDate",
				type : "date",
				dateFormat:'d.m.Y'
			}*/])
		});

		this.EvnReceptSearchViewGrid = new Ext.grid.GridPanel({
			bbar: new Ext.PagingToolbar ({
				store: this.EvnReceptSearchGridStore,
				pageSize: 100,
				displayInfo: true,
				displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
		        emptyMsg: "Нет записей для отображения"
			}),
			id : 'EvnReceptSearchViewGrid',
			autoExpandColumn: 'autoexpand_drug',
			autoExpandMin: 100,
			region: 'center',
			tabIndex : 13,
			store : this.EvnReceptSearchGridStore,
			loadMask : true,
			columns : [{
				hidden : false,
				sortable : true,
				dataIndex : "Person_Surname",
				header : lang['familiya']
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Firname",
				header : lang['imya']
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Secname",
				header : lang['otchestvo']
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Birthday",
				header : "Дата рождения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_setDate",
				header : lang['data'],
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Ser",
				header : lang['seriya'],
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Num",
				header : lang['nomer'],
				width: 70
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "MedPersonal_Fio",
				header : lang['vrach'],
				width: 200
			}, {
				hidden : false,
				id: 'autoexpand_drug',
				sortable : true,
				dataIndex : "Drug_Name",
				header : lang['medikament'],
				width: 500
			}, {
				css: 'text-align: right;',
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Kolvo",
				header : lang['kolichestvo'],
				width: 80
			}/*, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otpDate",
				header : "Дата отпуска",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 80
			}*/],
			border : false,
			tbar : new Ext.Toolbar(
			[{
				text : BTN_GRIDADD,
				iconCls: 'add16',
				handler : function(button, event) {
					EvnReceptSearchViewGrid.addRecept();
				}.createDelegate(this),
				id: 'ERS_NewReceptBtn',
				tooltip : "Ввод нового рецепта <b>(INS)</b>",
				iconCls: 'add16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptSearchViewGrid.openRecept('edit');
				}.createDelegate(this),
				id: 'ERS_EditReceptBtn',
				iconCls: 'edit16',
				text : BTN_GRIDEDIT,
				tooltip : "Редактирование выбранного рецепта <b>(F4)</b>",
				iconCls: 'edit16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptSearchViewGrid.openRecept('view');
				}.createDelegate(this),
				id: 'ERS_ViewReceptBtn',
				iconCls: 'view16',
				text : BTN_GRIDVIEW,
				tooltip : "Просмотр выбранного рецепта <b>(F3)</b>",
				iconCls: 'view16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptSearchViewGrid.deleteRecept();
				}.createDelegate(this),
				id: 'ERS_DeleteReceptBtn',
				iconCls: 'delete16',
				text : BTN_GRIDDEL,
				tooltip : "Удаление выбранного рецепта <b>(DEL)</b>",
				iconCls: 'delete16'
			}, {
				xtype : "tbseparator"
			}, {
				disabled: true,
				iconCls: 'actions16',
				text : lang['deystviya'],
				handler : function(button, event) {
					alert('');
				}.createDelegate(this),
				id: 'ERS_ActionBtn',
				iconCls: 'actions16'
			}, {
				xtype : "tbseparator"
			}, {
				disabled: false,
				iconCls: 'refresh16',
				text : BTN_GRIDREFR,
				handler : function(button, event) {
					Ext.getCmp('EvnReceptSearchWindow').refreshReceptList();
				}.createDelegate(this),
				id: 'ERS_RefreshBtn',
				tooltip : "Обновление списка с сервера <b>(F5)</b>",
				iconCls: 'refresh16'
			}, {
				iconCls: 'print16',
				menu: [{
					handler: function() {
						Ext.getCmp('EvnReceptSearchWindow').printEvnReceptList();
					},
					text: lang['lgotnyie_retseptyi_spisok_f9'],
					xtype: 'tbbutton'
				}, {
					handler: function() {
						Ext.getCmp('EvnReceptSearchWindow').printEvnRecept();
					},
					text: lang['lgotnyiy_retsept_ctrl_+_f9'],
					xtype: 'tbbutton'
				}],
				text: BTN_GRIDPRINT,
				xtype: 'tbbutton'
			}, {
				xtype : "tbfill"
			}, {
				id: 'ERS_GridCounter',
				text: '0 / 0',
				xtype: 'tbtext'
			}]),
			enableKeyEvents: true,
			listeners : {
				'rowdblclick' : function (grd, rowIndex, e) {
					grd.openRecept('edit');
				}
			},
			keys: [
				{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.F5,
					Ext.EventObject.F6,
					Ext.EventObject.F9,
					Ext.EventObject.F10,
					Ext.EventObject.F11,
					Ext.EventObject.F12,
					Ext.EventObject.INSERT,
					Ext.EventObject.TAB,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.HOME,
					Ext.EventObject.END
				],
				fn: function(inp, e) {
					e.stopEvent();
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
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

					var grd = EvnReceptSearchViewGrid;

					var selected_record = grd.getSelectionModel().getSelected();
					var params = new Object();
					params.Person_id = selected_record.get('Person_id');
					params.Server_id = selected_record.get('Server_id');
					params.Person_Birthday = Ext.util.Format.date(selected_record.get('Person_Birthday'), 'd.m.Y');
					params.Person_Firname = selected_record.get('Person_Firname');
					params.Person_Secname = selected_record.get('Person_Secname');
					params.Person_Surname = selected_record.get('Person_Surname');

					switch (e.getKey())
					{
						case Ext.EventObject.ENTER:
							grd.openRecept('edit');
						break;
						
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
							if (!grd.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'edit';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}

							grd.openRecept(action);

						break;

						case Ext.EventObject.F5:
							Ext.getCmp('EvnReceptSearchWindow').refreshReceptList();
						break;

						case Ext.EventObject.F9:
							if (e.ctrlKey == true)
							{
								Ext.getCmp('EvnReceptSearchWindow').printEvnRecept();
							}
							else
							{
								Ext.getCmp('EvnReceptSearchWindow').printEvnReceptList();
							}
						break;
				
						case Ext.EventObject.F6:
							ShowWindow('swPersonCardHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F10:
							ShowWindow('swPersonEditWindow', params);
							return false;
						break;

						case Ext.EventObject.F11:
							ShowWindow('swPersonCureHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F12:
							if (e.ctrlKey)
							{
								ShowWindow('swPersonDispHistoryWindow', params);
							}
							else
							{
								ShowWindow('swPersonPrivilegeViewWindow', params);
							}
							return false;
						break;
							
						case Ext.EventObject.INSERT:
							grd.addRecept();
						break;

						case Ext.EventObject.DELETE:
							grd.deleteRecept();
						break;

						case Ext.EventObject.TAB:
							if (e.shiftKey == false) {
								Ext.getCmp('ERS_BottomButtons').buttons[0].focus(false, 100);
							}
							else {
								Ext.getCmp('EvnReceptSearchWindow').getLastFieldOnCurrentTab().focus(true);
							}
						break;
						
						case Ext.EventObject.END:
							GridEnd(grd);
						break;
						
						case Ext.EventObject.HOME:
							GridHome(grd);
						break;
						
						case Ext.EventObject.PAGE_DOWN:
							GridPageDown(grd);
						break;
						
						case Ext.EventObject.PAGE_UP:
							GridPageUp(grd);
						break;
					}
				},
				stopEvent: true
			}],
			sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							var evn_recept_id = sm.getSelected().data.EvnRecept_id;
							var person_id = sm.getSelected().data.Person_id;
							var server_id = sm.getSelected().data.Server_id;

							if (evn_recept_id && person_id && server_id >= 0)
							{
								this.grid.getTopToolbar().items.item('ERS_DeleteReceptBtn').enable();
								this.grid.getTopToolbar().items.item('ERS_EditReceptBtn').enable();
								this.grid.getTopToolbar().items.item('ERS_ViewReceptBtn').enable();
							}
							else
							{
								this.grid.getTopToolbar().items.item('ERS_DeleteReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERS_EditReceptBtn').disable();
								this.grid.getTopToolbar().items.item('ERS_ViewReceptBtn').disable();
							}
							this.grid.getTopToolbar().items.items[10].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();
						}
					}
				}),
			openRecept: function(action) {
				if (!EvnReceptSearchViewGrid.getSelectionModel().getSelected())
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_retsept_iz_spiska']);
					return false;
				}

				var selected_record = EvnReceptSearchViewGrid.getSelectionModel().getSelected();
				var evn_recept_id = selected_record.data.EvnRecept_id;
				var person_id = selected_record.data.Person_id;
				var person_evn_id = selected_record.data.PersonEvn_id;
				var server_id = selected_record.data.Server_id;

				if (evn_recept_id && person_id && person_evn_id && server_id >= 0)
				{
					getWnd('swEvnReceptEditWindow').show({
						action: action,
						callback: function(data) {
							setGridRecord(EvnReceptSearchViewGrid, data.EvnReceptData);
						},
						EvnRecept_id: evn_recept_id,
						onHide: function() {
							EvnReceptSearchViewGrid.getView().focusRow(EvnReceptSearchViewGrid.getStore().indexOf(selected_record));
							EvnReceptSearchViewGrid.getSelectionModel().selectRow(EvnReceptSearchViewGrid.getStore().indexOf(selected_record));
						},
						Person_id: person_id,
						PersonEvn_id: person_evn_id,
						Server_id: server_id
					});
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_retsept_iz_spiska']);
				}
			},
			deleteRecept: function() {
				if (!EvnReceptSearchViewGrid.getSelectionModel().getSelected())
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_retsept_iz_spiska']);
					return false;
				}

				var evn_recept_id = EvnReceptSearchViewGrid.getSelectionModel().getSelected().data.EvnRecept_id;

				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId)
						{
							Ext.Ajax.request({
								failure: function(response, options) {
									Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_retsepta_voznikli_oshibki_[tip_oshibki_2]']);
								},
								params: { EvnRecept_id: evn_recept_id},
								success: function(response, options) {
									EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_DeleteReceptBtn').disable();
									EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_EditReceptBtn').disable();
									EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_ViewReceptBtn').disable();
									EvnReceptSearchViewGrid.removeAll();
									EvnReceptSearchViewGrid.getStore().reload();
								},
								url: C_EVNREC_DEL
							});
						}
						else if ('no' == buttonId) {
							var index = 0;
							var selected_record;

							if (EvnReceptSearchViewGrid.getSelectionModel().getSelected())
							{
								selected_record = EvnReceptSearchViewGrid.getSelectionModel().getSelected();

								if (selected_record.data.EvnRecept_id != null)
								{
									index = EvnReceptSearchViewGrid.getStore().findBy(function(rec) { return rec.get('EvnRecept_id') == selected_record.data.EvnRecept_id; });
								}
							}

							EvnReceptSearchViewGrid.getView().focusRow(index);

							if (index == 0)
							{
								EvnReceptSearchViewGrid.getSelectionModel().selectFirstRow();
							}
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: lang['udalit_retsept'],
					title: lang['vopros']
				});
			},
			addRecept: function() {
				var current_window = this.ownerCt;
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swEvnReceptEditWindow').show({
							action: 'add',
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							// ReceptType_id: 2,
							Server_id: person_data.Server_id,
							onHide: person_data.onHide
						});
					},
					searchMode: 'all'
				});
			}
		});

		this.EvnReceptSearchFilterForm = new Ext.form.FormPanel({
			id : "EvnSearchForm",
			labelWidth : 100,
			frame : false,
			border: false,
			region: 'north',
			items : [{
				id: 'ERSTabPanel',
				items : [{
					title : "<u>1</u>. Основной фильтр",
					frame: false,
					border: false,
					height : 220,
					items : [{
						name: 'SearchFormType',
						value: 'EvnRecept',
						xtype: 'hidden'
					}, {
						name: 'StateType_id',
						value: 1,
						xtype: 'hidden'
					}, {
						height : 5,
						border : false
					}, {
						id : "ERS_Person_Surname",
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									swReceptSearchWindow.findById('ERS_BottomButtons').buttons[4].focus();
								}
							}
						},
						name : "Person_Surname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : lang['familiya'],
						tabIndex : 301
					}, {
						name : "Person_Firname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : lang['imya'],
						tabIndex : 302
					}, {
						name : "Person_Secname",
						xtype : "textfieldpmw",
						width : 520,
						fieldLabel : lang['otchestvo'],
						tabIndex : 303
					}, {
						items : [{
							items : [{
								name : "Person_Birthday_Range",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Дата рождения",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 304
							}],
							layout : "form",
							width : 300,
							border : false
						}, {
							items : [{
								name : "PersonCard_Code",
								xtype : "textfield",
								width : 220,
								fieldLabel : "Номер карты",
								tabIndex : 305
							}],
							layout : "form",
							border : false
						}],
						layout : "column",
						//height: 240,
						autoHeight : true,
						border : false
					}, {
						items : [{
							items : [{
								name : "EvnRecept_setDate_Range",
								width : 170,
								xtype : "daterangefield",
								fieldLabel : "Дата выписки",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 306
							}],
							width : 300,
							layout : "form",
							border : false
						}, {
							items : [{
								autoCreate: {tag: "input", type: "text", size: "11", maxLength: "11", autocomplete: "off"},
								fieldLabel : lang['snils'],
								maskRe: /\d/,
								maxLength: 11,
								minLength: 11,
								name : "Person_Snils",
								width : 220,
								xtype : "textfield",
								tabIndex : 307
							}],
							layout : "form",
							border : false
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}, {
						items : [/*{
							items : [{
								name : "EvnRecept_otpDate",
								width : 170,
								xtype : "daterangefield",
								fieldLabel : "Дата отпуска",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : 306
							}],
							width : 300,
							layout : "form",
							border : false
						}, */{
							items : [{
								displayField: 'PrivilegeType_Name',
								codeField: 'PrivilegeType_Code',
								editable: false,
								fieldLabel: lang['kategoriya'],
								forceSelection : true,
								hiddenName: 'ER_PrivilegeType_id',
								id: 'RSF_ERPrivilegeTypeCombo',
								listWidth: 250,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'PrivilegeType_id', type: 'int'},
										{ name: 'PrivilegeType_Code', type: 'int'},
										{ name: 'PrivilegeType_Name', type: 'string'}
									],
									key: 'PrivilegeType_id',
									sortInfo: { field: 'PrivilegeType_Code' },
									tableName: 'PrivilegeType'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{PrivilegeType_Code}</font>&nbsp;{PrivilegeType_Name}',
									'</div></tpl>'
								),
								valueField: 'PrivilegeType_id',
								width: 220,
								xtype: 'swbaselocalcombo',
								tabIndex : 308
							}],
							layout : "form",
							width : 330,
							border : false
						}/*, {
							labelWidth : 70,
							items : [{
								displayField: 'YesNo_Name',
								codeField: 'YesNo_Code',
								editable: false,
								hiddenName: 'Person_IsRefuse',
								xtype : "swyesnocombo",
								width : 220,
								fieldLabel : lang['otkaz'],
								tabIndex : 309
							}],
							layout : "form",
							border : false
						}*/],
						layout : "column",
						autoHeight : true,
						border : false
					}, {
						allowBlank: true,
						codeField: 'MedPersonal_Code',
						editable: false,
						displayField: 'MedPersonal_Fio',
						fieldLabel: lang['vrach'],
						hiddenName: 'ER_MedPersonal_id',
						id: 'ERS_MedPersonalCombo',
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'MedPersonal_id'
							}, [
								{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
								{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
								{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' }
							]),
							url: C_MP_DLO_LOADLIST
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><td style="width: 70px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
							'</div></tpl>'
						),
						triggerAction: 'all',
						hideTrigger: false,
						valueField: 'MedPersonal_id',
						width: 520,
						xtype: 'swbaselocalcombo',
						tabIndex : 310
					}, {
						items : [{
							labelWidth : 100,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: lang['vvedite_kod_diagnoza'],
								fieldLabel: lang['kod_diagnoza_s'],
								hiddenName: 'ER_Diag_Code_From',
								hideTrigger: false,
								id: 'ERS_DiagComboFrom',
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 311
							}],
							layout : "form",
							width : 330,
							border : false
						}, {
							labelWidth : 70,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: lang['vvedite_kod_diagnoza'],
								fieldLabel: lang['po'],
								hiddenName: 'ER_Diag_Code_To',
								hideTrigger: false,
								id: 'ERS_DiagComboTo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptSearchViewGrid);
											}
										}
									}
								},
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 312
							}],
							layout : "form",
							width : 300,
							border : false
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}
					],
					bodyBorder : true,
					layout : "form",
					autoHeight : true
				}, {
					title : "<u>2</u>. Пациент",
					frame: false,
					border: false,
					height : 220,
					style: 'padding: 0px; margin-bottom: 5px',
					border : false,
					items : [{
						layout: 'column',
						border : true,
						items: [{
							labelWidth : 30,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								codeField: 'Sex_Code',
								editable: false,
								fieldLabel: lang['pol'],
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptSearchWindow.findById('ERS_BottomButtons').buttons[4].focus();
										}
									}
								},
								xtype: 'swpersonsexcombo',
								hiddenName: 'Sex_id',
								width : 120,
								tabIndex : 313
							}]
						}, {
							labelWidth : 75,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								codeField: 'SocStatus_Code',
								editable: false,
								fieldLabel: lang['sots_status'],
								listWidth: 250,
								xtype: 'swsocstatuscombo',
								hiddenName: 'SocStatus_id',
								width : 140,
								tabIndex : 314
								}]
						}, {
							labelWidth : 70,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								codeField: 'PrivilegeType_Code',
								editable: false,
								displayField: 'PrivilegeType_Name',
								fieldLabel: lang['kategoriya'],
								hiddenName: 'PrivilegeType_id',
								id: 'RSF_PrivilegeTypeCombo',
								listWidth: 250,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'PrivilegeType_id', type: 'int'},
										{ name: 'PrivilegeType_Code', type: 'int'},
										{ name: 'PrivilegeType_Name', type: 'string'}
									],
									key: 'PrivilegeType_id',
									sortInfo: { field: 'PrivilegeType_Code' },
									tableName: 'PrivilegeType'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{PrivilegeType_Code}</font>&nbsp;{PrivilegeType_Name}',
									'</div></tpl>'
								),
								valueField: 'PrivilegeType_id',
								width: 158,
								xtype: 'swbaselocalcombo',
								tabIndex : 315
							}]
						}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['dokument'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 40,
						items: [{
								layout: 'column',
								border : false,
								items: [{
										layout: 'form',
										border : false,
										items: [{
											codeField: 'DocumentType_Code',
											editable: false,
											xtype: 'swdocumenttypecombo',
											hiddenName: 'DocumentType_id',
											tabIndex : 316
										}]
									},
									{
										layout: 'form',
										width: 406,
										border : false,
										labelWidth : 50,
										items:[{
											allowBlank: true,
											xtype: 'sworgdepcombo',
											validateOnBlur: false,
											validationEvent: false,
											hiddenName: 'OrgDep_id',
											editable: false,
											triggerAction: 'none',
											listWidth: '300',
											onTrigger1Click: function() {
												if ( this.disabled )
													return;
												var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
												var combo = this;
												getWnd('swOrgSearchWindow').show({
													onSelect: function(orgData) {
														if ( orgData.Org_id > 0 )
														{
															combo.getStore().load({
																params: {
																	Object:'OrgDep',
																	OrgDep_id: orgData.Org_id,
																	OrgDep_Name: ''
																},
																callback: function()
																{
																	combo.setValue(orgData.Org_id);
																	combo.focus(true, 500);
																	combo.fireEvent('change', combo);
																}
															});
														}
														getWnd('swOrgSearchWindow').hide();
													},
													object: 'OrgDep'
												});
											},
											enableKeyEvents: true,
											listeners: {
												'keydown': function( inp, e ) {
													if ( inp.disabled )
														return;
													if ( e.F4 == e.getKey() )
													{
														if ( e.browserEvent.stopPropagation )
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if ( e.browserEvent.preventDefault )
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if ( Ext.isIE )
														{
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														inp.onTrigger1Click();
														return false;
													}
													if ( e.DELETE == e.getKey() && e.altKey) {
														inp.onTrigger2Click();
														return false;
													}
												},
												'keyup': function(inp, e) {
													if ( e.F4 == e.getKey() )
													{
														if ( e.browserEvent.stopPropagation )
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if ( e.browserEvent.preventDefault )
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if ( Ext.isIE )
														{
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
													if ( e.DELETE == e.getKey() && e.altKey) {
														inp.onTrigger2Click();
														return false;
													}
												}
											},
											width: 342,
											tabIndex : 317
										}]
									}
								]
						}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['polis'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 80,
						items: [{
							layout: 'form',
							border : false,
							width : 608,
							items:[{
									editable: false,
									codeField: 'OMSSprTerr_Code',
									hiddenName: 'OMSSprTerr_id',
									width : 522,
									xtype: 'swomssprterrcombo',
									tabIndex : 318
								}
							]
							},
							{
								layout: 'column',
								border : false,
								items: [{
									layout: 'form',
									border : false,
									width : 280,
									items: [{
											codeField: 'PolisType_Code',
											editable: false,
											border : false,
											hiddenName: 'PolisType_id',
											xtype: 'swpolistypecombo',
											tabIndex : 319
									}]
								},
								{
									layout: 'form',
									border : false,
									labelWidth : 50,
									items: [{
										allowBlank: true,
										id: 'ERS_OrgSMO_id',
										validateOnBlur: false,
										validationEvent: false,
										editable: false,
										triggerAction: 'none',
										xtype: 'sworgsmocombo',
										hiddenName: 'OrgSmo_id',
										listWidth: '300',
										onTrigger1Click: function() {
											if ( this.disabled )
													return;
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var combo = this;
											getWnd('swOrgSearchWindow').show({
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.getStore().load({
															params: {
																Object:'OrgSMO',
																OrgSMO_id: orgData.Org_id,
																OrgSMO_Name: ''
															},
															callback: function()
															{
																combo.setValue(orgData.Org_id);
																combo.focus(true, 500);
																combo.fireEvent('change', combo);
															}
														});
													}
													getWnd('swOrgSearchWindow').hide();
												},
												object: 'OrgSMO'
											});
										},
										enableKeyEvents: true,
										listeners: {
											'keydown': function( inp, e ) {
												if ( e.F4 == e.getKey() )
												{
													if ( inp.disabled )
														return;
													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													inp.onTrigger1Click();
													return false;
												}
												if ( e.DELETE == e.getKey() && e.altKey) {
													inp.onTrigger2Click();
													return false;
												}
											},
											'keyup': function(inp, e) {
												if ( e.F4 == e.getKey() )
												{
													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
												if ( e.DELETE == e.getKey() && e.altKey) {
													inp.onTrigger2Click();
													return false;
												}
											}
										},
										width : 272,
										tabIndex : 320
									}]
								}]
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['mesto_rabotyi_uchebyi'],
						style: 'padding: 3px; margin-bottom: 0px; display:block;',
						items: [{
							layout: 'column',
							border : false,
							items: [{
									layout: 'form',
									width: 640,
									border : false,
									labelWidth : 110,
									items:[{
												allowBlank: true,
												xtype: 'sworgcombo',
												hiddenName: 'Org_id',
												editable: false,
												triggerAction: 'none',
												anchor: '95%',
												onTrigger1Click: function() {
													var ownerWindow = swReceptSearchWindow;
													var combo = this;
													getWnd('swOrgSearchWindow').show({
														onSelect: function(orgData) {
															if ( orgData.Org_id > 0 )
															{
																combo.getStore().load({
																	params: {
																		Object:'Org',
																		Org_id: orgData.Org_id,
																		Org_Name:''
																	},
																	callback: function()
																	{
																		combo.setValue(orgData.Org_id);
																		combo.focus(true, 500);
																		combo.fireEvent('change', combo);
																	}
																});
															}
															getWnd('swOrgSearchWindow').hide();
														}
													});
												},
												enableKeyEvents: true,
												listeners: {
													'keydown': function( inp, e ) {
														if ( e.F4 == e.getKey() )
														{
															if ( e.browserEvent.stopPropagation )
																e.browserEvent.stopPropagation();
															else
																e.browserEvent.cancelBubble = true;
															if ( e.browserEvent.preventDefault )
																e.browserEvent.preventDefault();
															else
																e.browserEvent.returnValue = false;
															e.browserEvent.returnValue = false;
															e.returnValue = false;
															if ( Ext.isIE )
															{
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}
															inp.onTrigger1Click();
															return false;
														}
														if ( e.DELETE == e.getKey() && e.altKey) {
															inp.onTrigger2Click();
															return false;
														}
														if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
															if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
																e.stopEvent();
																TabToGrid(EvnReceptSearchViewGrid);
															}
														}
													},
													'keyup': function(inp, e) {
														if ( e.F4 == e.getKey() )
														{
															if ( e.browserEvent.stopPropagation )
																e.browserEvent.stopPropagation();
															else
																e.browserEvent.cancelBubble = true;
															if ( e.browserEvent.preventDefault )
																e.browserEvent.preventDefault();
															else
																e.browserEvent.returnValue = false;
															e.browserEvent.returnValue = false;
															e.returnValue = false;
															if ( Ext.isIE )
															{
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}
															return false;
														}
														if ( e.DELETE == e.getKey() && e.altKey) {
															inp.onTrigger2Click();
															return false;
														}
													}
												},
												tabIndex : 321
											}
									]
								}
							]
						}]
					}
					]
				}, {
					autoHeight: true,
					labelWidth: 120,
					layout:'form',
					style: 'padding: 2px',
					title: '<u>3</u>. Адрес',
					items: [{
						codeField: 'KLAreaStat_Code',
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: lang['territoriya'],
						hiddenName: 'KLAreaStat_id',
						id: 'ERS_KLAreaStatCombo',
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									swReceptSearchWindow.findById('ERS_BottomButtons').buttons[4].focus();
								}
							},
							'change': function(combo, newValue, oldValue) {
								var current_window = swReceptSearchWindow;
								var index = combo.getStore().findBy(function(rec) { return rec.get('KLAreaStat_id') == newValue; });

								current_window.findById('ERS_CountryCombo').enable();
								current_window.findById('ERS_RegionCombo').enable();
								current_window.findById('ERS_SubRegionCombo').enable();
								current_window.findById('ERS_CityCombo').enable();
								current_window.findById('ERS_TownCombo').enable();
								current_window.findById('ERS_StreetCombo').enable();

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
									current_window.findById('ERS_CountryCombo').areaLevel, 
									{'Country': current_window.findById('ERS_CountryCombo'),
									'Region': current_window.findById('ERS_RegionCombo'),
									'SubRegion': current_window.findById('ERS_SubRegionCombo'),
									'City': current_window.findById('ERS_CityCombo'),
									'Town': current_window.findById('ERS_TownCombo'),
									'Street': current_window.findById('ERS_StreetCombo')
									}
								);

								if (country_id != null)
								{
									current_window.findById('ERS_CountryCombo').setValue(country_id);
									current_window.findById('ERS_CountryCombo').disable();
								}
								else
								{
									return false;
								}

								current_window.findById('ERS_RegionCombo').getStore().load({
									callback: function() {
										current_window.findById('ERS_RegionCombo').setValue(region_id);
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

								current_window.findById('ERS_SubRegionCombo').getStore().load({
									callback: function() {
										current_window.findById('ERS_SubRegionCombo').setValue(subregion_id);
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

								current_window.findById('ERS_CityCombo').getStore().load({
									callback: function() {
										current_window.findById('ERS_CityCombo').setValue(city_id);
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

								current_window.findById('ERS_TownCombo').getStore().load({
									callback: function() {
										current_window.findById('ERS_TownCombo').setValue(town_id);
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

								current_window.findById('ERS_StreetCombo').getStore().load({
									params: {
										country_id: 0,
										level: 5,
										value: klarea_pid
									}
								});

								switch (level)
								{
									case 1:
										current_window.findById('ERS_RegionCombo').disable();
										break;

									case 2:
										current_window.findById('ERS_RegionCombo').disable();
										current_window.findById('ERS_SubRegionCombo').disable();
										break;

									case 3:
										current_window.findById('ERS_RegionCombo').disable();
										current_window.findById('ERS_SubRegionCombo').disable();
										current_window.findById('ERS_CityCombo').disable();
										break;

									case 4:
										current_window.findById('ERS_RegionCombo').disable();
										current_window.findById('ERS_SubRegionCombo').disable();
										current_window.findById('ERS_CityCombo').disable();
										current_window.findById('ERS_TownCombo').disable();
										break;
								}
							}
						},
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLAreaStat_id', type: 'int' },
								{ name: 'KLAreaStat_Code', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' },
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLRGN_id', type: 'int' },
								{ name: 'KLSubRGN_id', type: 'int' },
								{ name: 'KLCity_id', type: 'int' },
								{ name: 'KLTown_id', type: 'int' }
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tabIndex: 323,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}' +
							'</div></tpl>',
						valueField: 'KLAreaStat_id',
						width: 620,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 0,
						codeField: 'KLCountry_Code',
						displayField: 'KLCountry_Name',
						editable: true,
						fieldLabel: lang['strana'],
						hiddenName: 'KLCountry_id',
						id: 'ERS_CountryCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										},
										combo.getValue(),
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE)
								{
									if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
									{
										combo.fireEvent('change', combo, null, combo.getValue());
									}
								}
							},
							'select': function(combo, record, index) {
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
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLCountry_Code', type: 'int' },
								{ name: 'KLCountry_Name', type: 'string' }
							],
							key: 'KLCountry_id',
							sortInfo: {
								field: 'KLCountry_Name'
							},
							tableName: 'KLCountry'
						}),
						tabIndex: 324,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}' +
							'</div></tpl>',
						valueField: 'KLCountry_id',
						width: 620,
						xtype: 'swbaselocalcombo'
					}, {
						areaLevel: 1,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: lang['region'],
						hiddenName: 'KLRgn_id',
						id: 'ERS_RegionCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
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
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 325,
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
						fieldLabel: lang['rayon'],
						hiddenName: 'KLSubRgn_id',
						id: 'ERS_SubRegionCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
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
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 326,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						areaLevel: 3,
						displayField: 'KLArea_Name',
						enableKeyEvents: true,
						fieldLabel: lang['gorod'],
						hiddenName: 'KLCity_id',
						id: 'ERS_CityCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
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
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 327,
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
						fieldLabel: lang['naselennyiy_punkt'],
						hiddenName: 'KLTown_id',
						id: 'ERS_TownCombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if (newValue != null && combo.getRawValue().toString().length > 0)
								{
									loadAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										},
										0,
										combo.getValue(), 
										true
									);
								}
								else
								{
									clearAddressCombo(
										combo.areaLevel, 
										{'Country': swReceptSearchWindow.findById('ERS_CountryCombo'),
										'Region': swReceptSearchWindow.findById('ERS_RegionCombo'),
										'SubRegion': swReceptSearchWindow.findById('ERS_SubRegionCombo'),
										'City': swReceptSearchWindow.findById('ERS_CityCombo'),
										'Town': swReceptSearchWindow.findById('ERS_TownCombo'),
										'Street': swReceptSearchWindow.findById('ERS_StreetCombo')
										}
									);
								}
							},
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							},
							'select': function(combo, record, index) {
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
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLArea_id', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' }
							],
							key: 'KLArea_id',
							sortInfo: {
								field: 'KLArea_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 328,
						tpl: '<tpl for="."><div class="x-combo-list-item">' +
							'{KLArea_Name}' +
							'</div></tpl>',
						triggerAction: 'all',
						valueField: 'KLArea_id',
						xtype: 'combo'
					}, {
						displayField: 'KLStreet_Name',
						enableKeyEvents: true,
						fieldLabel: lang['ulitsa'],
						hiddenName: 'KLStreet_id',
						id: 'ERS_StreetCombo',
						listeners: {
							'keydown': function(combo, e) {
								if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0)
								{
									combo.clearValue();
								}
							}
						},
						minChars: 0,
						mode: 'local',
						queryDelay: 250,
						width: 620,
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'KLStreet_id', type: 'int' },
								{ name: 'KLStreet_Name', type: 'string' }
							],
							key: 'KLStreet_id',
							sortInfo: {
								field: 'KLStreet_Name'
							},
							url: C_LOAD_ADDRCOMBO
						}),
						tabIndex: 329,
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
								fieldLabel: lang['dom'],
								id: 'ERS_Address_House',
								listeners: {
									'keydown': function(inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptSearchViewGrid);
											}
										}
									}
								},
								name: 'Address_House',
								tabIndex: 330,
								width: 156,
								xtype: 'textfield'
							}]
						}]
					}]
				}, {
					title : "<u>4</u>. Рецепт",
					border: false,
					frame: false,
					height : 220,
					style: 'padding: 0px; margin-bottom: 5px;',
					items : [{
						layout: 'column',
						border : true,
						items: [{
							labelWidth : 105,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								displayField: 'ReceptFinance_Name',
								codeField: 'ReceptFinance_Code',
								editable: false,
								fieldLabel: lang['finansirovanie'],
								hiddenName: 'ReceptFinance_id',
								//hideTrigger: true,
								id: 'ERS_ReceptFinanceCombo',
								lastQuery: '',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptSearchWindow.findById('ERS_BottomButtons').buttons[4].focus();
										}
									},
									'change': function(combo, newValue, oldValue) {
										Ext.getCmp('ERS_DrugMnnCombo').getStore().baseParams.ReceptFinance_Code = newValue;
										Ext.getCmp('ERS_DrugCombo').getStore().baseParams.ReceptFinance_Code = newValue;
									}
								},
								listWidth: 200,
								selectOnFocus: true,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'ReceptFinance_Name', mapping: 'ReceptFinance_Name' },
										{ name: 'ReceptFinance_Code', mapping: 'ReceptFinance_Code' },
										{ name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' }
									],
									key: 'ReceptFinance_id',
									sortInfo: { field: 'ReceptFinance_Code' },
									tableName: 'ReceptFinance'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{ReceptFinance_Code}</font>&nbsp;{ReceptFinance_Name}',
									'</div></tpl>'
								),
								//trigger2Class: 'hideTrigger',
								valueField: 'ReceptFinance_id',
								width: 140,
								xtype: 'swbaselocalcombo',
								tabIndex : 331
							}]
						}, {
							labelWidth : 50,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								displayField: 'ReceptDiscount_Name',
								codeField: 'ReceptDiscount_Code',
								editable: false,
								fieldLabel: lang['skidka'],
								hiddenName: 'ReceptDiscount_id',
								id: 'ERS_ReceptDiscountCombo',
								listWidth: 100,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'ReceptDiscount_Name', mapping: 'ReceptDiscount_Name' },
										{ name: 'ReceptDiscount_Code', mapping: 'ReceptDiscount_Code' },
										{ name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' }
									],
									key: 'ReceptDiscount_id',
									sortInfo: { field: 'ReceptDiscount_Code' },
									tableName: 'ReceptDiscount'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td style="width: 15px;"><font color="red">{ReceptDiscount_Code}</font></td><td><h3>{ReceptDiscount_Name}</h3></td></tr></table>',
									'</div></tpl>'
								),
								//trigger2Class: 'hideTrigger',
								valueField: 'ReceptDiscount_id',
								width: 120,
								xtype: 'swbaselocalcombo',
								tabIndex : 332
							}]
						}, {
							labelWidth : 90,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								displayField: 'ReceptValid_Name',
								codeField: 'ReceptValid_Code',
								editable: false,
								fieldLabel: lang['srok_deystviya'],
								hiddenName: 'ReceptValid_id',
								id: 'ERS_ReceptValidCombo',
								listWidth: 100,
								store: new Ext.db.AdapterStore({
									autoLoad: true,
									dbFile: 'Promed.db',
									fields: [
										{ name: 'ReceptValid_Name', mapping: 'ReceptValid_Name', type: 'string' },
										{ name: 'ReceptValid_Code', mapping: 'ReceptValid_Code', type: 'int' },
										{ name: 'ReceptValid_id', mapping: 'ReceptValid_id', type: 'int' }
									],
									key: 'ReceptValid_id',
									sortInfo: { field: 'ReceptValid_Code' },
									tableName: 'ReceptValid'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<table style="border: 0;"><td><h3><font style="color: #f00;">{ReceptValid_Code}</font> {ReceptValid_Name}</h3></td></tr></table>',
									'</div></tpl>'
								),
								//trigger2Class: 'hideTrigger',
								value: 2,
								valueField: 'ReceptValid_id',
								width: 100,
								xtype: 'swbaselocalcombo',
								tabIndex : 333
							}]
						}, {
							labelWidth : 90,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: [{
								allowBlank: true,
								fieldLabel: lang['tip_retsepta'],
								id: 'ERIS_ReceptTypeCombo',
								hiddenName: 'ReceptType_id',
								tabIndex: 1334,
								width: 150,
								xtype: 'swrecepttypecombo'
							}]
						}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['retsept'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							layout: 'column',
							border : false,
							items: [{
									layout: 'form',
									border : false,
									items: [{
										fieldLabel: lang['seriya'],
										name: 'EvnRecept_Ser',
										xtype : "textfield",
										tabIndex : 334
									}],
									width: 300
								},
								{
									layout: 'form',
									border : false,
									labelWidth : 50,
									items:[{
										fieldLabel: lang['nomer'],
										name: 'EvnRecept_Num',
										xtype : "textfield",
										tabIndex : 335
									}],
									width: 300
								}
							]
						}]
					}, /*{
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['udostoverenie'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							layout: 'column',
							border : false,
							items: [{
									layout: 'form',
									border : false,
									items: [{
										fieldLabel: lang['seriya'],
										name: 'EvnUdost_Ser',
										xtype : "textfield",
										tabIndex : 328
									}],
									width: 300
								},
								{
									layout: 'form',
									border : false,
									labelWidth : 50,
									items:[{
										fieldLabel: lang['nomer'],
										name: 'EvnUdost_Num',
										xtype : "textfield",
										tabIndex : 329
									}],
									width: 300
								}
							]
						}]
					},*/ {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['medikament'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							allowBlank: true,
							id: 'ERS_DrugMnnCombo',
							emptyText: lang['nachnite_vvodit_mnn'],
							onTrigger2Click: function() {
								var drug_mnn_combo = this;
								var current_window = Ext.getCmp('EvnReceptSearchWindow');

								getWnd('swDrugMnnSearchWindow').show({
									onClose: function() {
										drug_mnn_combo.focus(false);
									},
									onSelect: function(drugMnnData) {
										drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);
										var index = drug_mnn_combo.getStore().findBy(function(rec) { return rec.get('DrugMnn_id') == drugMnnData.DrugMnn_id; });
										var record = drug_mnn_combo.getStore().getAt(index);

										if (record)
										{
											drug_mnn_combo.fireEvent('change', drug_mnn_combo, drugMnnData.DrugMnn_id, 0);
										}

										getWnd('swDrugMnnSearchWindow').hide();
										drug_mnn_combo.focus(false);
									}
								});
							},
							lastQuery: '',
							listeners: {
								'keydown': function(inp, e) {
									if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
									{
										e.stopEvent();

										if (e.browserEvent.stopPropagation)
										{
											e.browserEvent.stopPropagation();
										}
										else
										{
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault)
										{
											e.browserEvent.preventDefault();
										}
										else
										{
											e.browserEvent.returnValue = false;
										}

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if (Ext.isIE)
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										switch (e.getKey())
										{
											case Ext.EventObject.DELETE:
												inp.setValue('');
												inp.setRawValue('');
												break;

											case Ext.EventObject.F4:
												inp.onTrigger2Click();
												break;
										}
									}
								},
								'beforeselect': function() {
									Ext.getCmp('ERS_DrugCombo').lastQuery = '';
								},
								'change': function(combo, newValue, oldValue) {
									var drug_combo = Ext.getCmp('ERS_DrugCombo');

									drug_combo.clearValue();
									drug_combo.getStore().removeAll();
									drug_combo.lastQuery = '';
									var ReceptFinance = drug_combo.getStore().baseParams.ReceptFinance_Code;
									drug_combo.getStore().baseParams = {};
									drug_combo.getStore().baseParams.ReceptFinance_Code = ReceptFinance;
									drug_combo.getStore().baseParams.DrugMnn_id = newValue;
									drug_combo.getStore().baseParams.query = '';
									drug_combo.getStore().baseParams.searchFull = 1;

									if (newValue > 0)
									{
										drug_combo.getStore().load();
									}
								},
								'blur': function(combo)
								{
									if (combo.getRawValue() == '')
									{
										combo.setValue('');
									}
									else
									{
										return false;
									}
								}
							},
							listWidth: 800,
							minChars: 0,
							minLength: 1,
							minLengthText: lang['pole_doljno_byit_zapolneno'],
							plugins: [ new Ext.ux.translit(true) ],
							queryDelay: 250,
							tabIndex: 336,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 517,
							xtype: 'swdrugmnncombo'
						}, {
							allowBlank: true,
							id: 'ERS_DrugCombo',
							listeners: {
								'beforeselect': function(combo, record, index) {
									combo.setValue(record.get('Drug_id'));

									var drug_mnn_combo = Ext.getCmp('ERS_DrugMnnCombo');
									var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));

									if (drug_mnn_record)
									{
										drug_mnn_combo.setValue(record.get('DrugMnn_id'));
									}
									else
									{
										if (combo.getRawValue()!='') {
											var ReceptFinance = drug_mnn_combo.getStore().baseParams.ReceptFinance_Code;
											drug_mnn_combo.getStore().baseParams = {};
											drug_mnn_combo.getStore().baseParams.ReceptFinance_Code = ReceptFinance;
											drug_mnn_combo.getStore().baseParams.searchFull = 1;
											drug_mnn_combo.getStore().load({
												callback: function() {
													drug_mnn_combo.setValue(record.get('DrugMnn_id'));
												},
												params: {
													DrugMnn_id: record.get('DrugMnn_id')
												}
											})
										}
									}
								},
								'keydown': function(inp, e) {
									if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
									{
										e.stopEvent();

										if (e.browserEvent.stopPropagation)
										{
											e.browserEvent.stopPropagation();
										}
										else
										{
											e.browserEvent.cancelBubble = true;
										}

										if (e.browserEvent.preventDefault)
										{
											e.browserEvent.preventDefault();
										}
										else
										{
											e.browserEvent.returnValue = false;
										}

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if (Ext.isIE)
										{
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										switch (e.getKey())
										{
											case Ext.EventObject.DELETE:
												inp.setValue('');
												inp.setRawValue('');
												break;

											case Ext.EventObject.F4:
												inp.onTrigger2Click();
												break;
										}
									}
								}
							},
							listWidth: 800,
							loadingText: lang['idet_poisk'],
							minLengthText: lang['pole_doljno_byit_zapolneno'],
							onTrigger2Click: function() {
								var drug_combo = this;
								var current_window = Ext.getCmp('EvnReceptSearchWindow');

								getWnd('swDrugTorgSearchWindow').show({
									onHide: function() {
										drug_combo.focus(false);
									},
									onSelect: function(drugTorgData) {
										drug_combo.getStore().removeAll();

										drug_combo.getStore().loadData([{
											Drug_Code: drugTorgData.Drug_Code,
											Drug_id: drugTorgData.Drug_id,
											Drug_Name: drugTorgData.Drug_Name,
											Drug_Price: drugTorgData.Drug_Price,
											DrugMnn_id: drugTorgData.DrugMnn_id
										}]);

										drug_combo.setValue(drugTorgData.Drug_id);
										drug_combo.getStore().baseParams.Drug_id = drugTorgData.Drug_id;
										drug_combo.getStore().baseParams.DrugMnn_id = 0;
										drug_combo.getStore().baseParams.searchFull = 1;
										index = drug_combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drugTorgData.Drug_id; });
										record = drug_combo.getStore().getAt(index);

										if (record)
										{
											drug_combo.fireEvent('beforeselect', drug_combo, record);
										}

										getWnd('swDrugTorgSearchWindow').hide();
									}
								});
							},
							tabIndex: 337,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 517,
							xtype: 'swdrugcombo'
						}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['apteka'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							layout: 'column',
							border : false,
							items: [{
								layout: 'form',
								border : false,
								labelWidth : 200,
								items: [
									new sw.Promed.SwYesNoCombo({
										fieldLabel: lang['vyipiska_bez_nalichiya_v_apteke'],
										hiddenName: 'EvnRecept_IsNotOstat',
										id: 'ERSEvnRecept_IsNotOstat',
										tabIndex: 1338,
										width: 70
									})
								],
								width: 300
							}, {
								layout: 'form',
								border : false,
								labelWidth : 50,
								items: [{
										fieldLabel: lang['apteka'],
										id: 'ERS_OrgFarmacy',
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
										name: 'OrgFarmacy_id',
										tabIndex: 1339,
										xtype : "sworgfarmacycombo",
										width: 300
								}],
								width: 360
							}]
						}]
					}]
				}, {
					title : "<u>5</u>." + lang['polzovatel'],
					height : 200,
					style: 'padding: 5px; margin-bottom: 5px',
					items: [{
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['izmenenie_retsepta_v_baze_dannyih'],
						style: 'padding: 5px; margin-bottom: 5px',
						items: [
							new sw.Promed.SwProMedUserCombo({
								id : 'ERS_pmUser_updID',
								hiddenName : "pmUser_updID",
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptSearchWindow.findById('ERS_BottomButtons').buttons[4].focus();
										}
									}
								},
								name : "pmUser_updID",
								width : 300,
								fieldLabel : lang['polzovatel'],
								tabIndex: 338
							}),
							{
								name : "UpdDate_Range",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : lang['period'],
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: 339
							}
						]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['dobavlenie_retsepta_v_baze_dannyih'],
						style: 'padding: 5px; margin-bottom: 5px',
						items: [
							new sw.Promed.SwProMedUserCombo({
								id : 'ERS_pmUser_insID',
								hiddenName : "pmUser_insID",
								width : 300,
								fieldLabel : lang['polzovatel'],
								tabIndex: 340
							}),
							{
								name : "InsDate_Range",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : lang['period'],
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptSearchViewGrid);
											}
										}
									}
								},
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex: 341
							}		
						]
					}]
				}],
				listeners: {
					'tabchange': function(tab, panel) {
						var els=panel.findByType('textfield', false);
						if (els=='undefined')
							els=panel.findByType('combo', false);
						var el=els[0];
						if (el!='undefined' && el.focus)
							el.focus(true, 200);
					}
				},
				xtype : "tabpanel",
				layout : "",
				activeTab : 0,
				border : false,
				layoutOnTabChange: true
			}],
			layout : "form",
			xtype : "form",
			autoHeight : true,
			labelAlign : "right",
			border : false,
			keys: [{
				key: 13,
				fn: function() {
					Ext.getCmp('EvnReceptSearchWindow').SearchRecept();
				},
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			height : 500,
			items : [
				this.EvnReceptSearchFilterForm,
				this.EvnReceptSearchViewGrid, {
				id : "ERS_BottomButtons",
				region : "south",
				height : 40,
				buttons : [{
					text : BTN_FRMSEARCH,
					iconCls: 'search16',
					handler: function() {
						Ext.getCmp('EvnReceptSearchWindow').SearchRecept();
					}.createDelegate(this),
					onTabAction : function () {
						Ext.getCmp('ERS_BottomButtons').buttons[1].focus(false, 0);
					},
					onShiftTabAction : function () {
						if (EvnReceptSearchViewGrid.getStore().getCount() == 0) {
							Ext.getCmp('EvnReceptSearchWindow').getLastFieldOnCurrentTab().focus(true);
							return;
						}
						var selected_record = EvnReceptSearchViewGrid.getSelectionModel().getSelected();
						if (selected_record != -1) {
							var index = EvnReceptSearchViewGrid.getStore().indexOf(selected_record);
						}
						else {
							var index = 0;
						}
						EvnReceptSearchViewGrid.getView().focusRow(index);
    					EvnReceptSearchViewGrid.getSelectionModel().selectRow(index);
					},
					tabIndex : 391
				}, {
					text : BTN_FRMRESET,
					iconCls: 'resetsearch16',
					handler : function(button, event) {
						EvnReceptSearchFilterForm.getForm().reset();
						EvnReceptSearchViewGrid.store.removeAll();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_DeleteReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_EditReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_ViewReceptBtn').disable();
					}.createDelegate(this),
					tabIndex : 392
				}, {
					text : BTN_FRMCOUNT,
					iconCls: 'search16',
					handler : function(button, event) {
						Ext.getCmp('EvnReceptSearchWindow').getRecordsCount();
					}.createDelegate(this),
					tabIndex : 393
				}, {
					handler: function() {
						EvnReceptSearchFilterForm.getForm().submit();
					},
					iconCls: 'print16',
					tabIndex: 394,
					text: BTN_FRMPRINT
				}, {
					text : '-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCLOSE,
					iconCls: 'cancel16',
					handler : function(button, event) {
						this.hide();
					}.createDelegate(this),
					onTabAction : function () {
						EvnReceptSearchFilterForm.findById('ERS_Person_Surname').focus(true, 0);
					},
					onShiftTabAction : function () {
						Ext.getCmp('ERS_BottomButtons').buttons[1].focus(false, 0);
					},
					tabIndex : 395
				}
				],
				buttonAlign : "left"
				}
			],
			keys: [{
				key: Ext.EventObject.INSERT,
				fn: function(e) {
					EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_NewReceptBtn').handler();
				},
				stopEvent: true
			}, {
				key: "123456789",
				alt: true,
				fn: function(e) {
					Ext.getCmp("ERSTabPanel").setActiveTab(Ext.getCmp("ERSTabPanel").items.items[ e - 49 ]);
				},
				stopEvent: true
			}, {
				key: Ext.EventObject.F5,
				fn: function(e) {
					// тупо чтобы по F5 не перезагружалась страница
					return false;
				},
				stopEvent: true
			},	{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
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

					if (e.getKey() == Ext.EventObject.J)
					{
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						EvnReceptSearchFilterForm.getForm().reset();
						EvnReceptSearchViewGrid.store.removeAll();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_DeleteReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_EditReceptBtn').disable();
						EvnReceptSearchViewGrid.getTopToolbar().items.item('ERS_ViewReceptBtn').disable();
						return false;
					}

					if (e.getKey() == Ext.EventObject.G)
					{
						Ext.getCmp('EvnReceptSearchWindow').SearchRecept();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.G ],
				scope: this,
				stopEvent: false
			}],
			title : WND_DLO_RECSEARCH,
			width : 670,
			xtype : "window"
		});
		EvnReceptSearchFilterForm = this.EvnReceptSearchFilterForm;
		EvnReceptSearchViewGrid = this.EvnReceptSearchViewGrid;
		sw.Promed.swReceptSearchWindow.superclass.initComponent.apply(this, arguments);

		Ext.getCmp('ERS_DrugCombo').getStore().baseParams.searchFull = 1;
		Ext.getCmp('ERS_DrugMnnCombo').getStore().baseParams.searchFull = 1;
	}
});