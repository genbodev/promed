/**
* swReceptFarmacySearchWindow - Журнал отсрочки для аптек
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
* @comment      Префикс для id компонентов RFSW (swReceptFarmacySearchWindow)
* @comment      tabIndex от 1301 до 1400
*/

var EvnReceptFarmacySearchFilterForm;
var EvnReceptFarmacySearchViewGrid;
var EvnReceptFarmacySearchGridStore;

function SearchFarmacyRecept()
{
	if ( EvnReceptFarmacySearchFilterForm.isEmpty() )
	{
		sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
		function () { EvnReceptFarmacySearchFilterForm.getForm().findField(0).focus()});
		return false;
	}

	if (EvnReceptFarmacySearchFilterForm.getForm().isValid() ) {
		var post = EvnReceptFarmacySearchFilterForm.getForm().getValues();
		EvnReceptFarmacySearchViewGrid.store.removeAll();

		EvnReceptFarmacySearchViewGrid.getStore().baseParams = EvnReceptFarmacySearchFilterForm.getForm().getValues();

		post.limit = 100;
		post.start = 0;

		EvnReceptFarmacySearchViewGrid.store.load({
			params: post,
			callback: function(r, opt ) {
				var len = r.length;
				if ( len > 100 ) { // опа! 101 запись!
					new Ext.ux.window.MessageWindow({
						title: lang['jurnal_otsrochki'],
						autoDestroy: true,//default = true
						autoHeight: true,
						autoHide: true,//default = true
						help: false,
						bodyStyle: 'text-align:center',
						closable: false,
						//pinState: null,
						//pinOnClick: false,
						hideFx: {
							delay: 2000,
							//duration: 0.25,
							mode: 'standard',//null,'standard','custom',or default ghost
							useProxy: false //default is false to hide window instead
						},
						html: lang['naydeno_bolshe_100_zapisey_pokazanyi_pervyie_100_zapisey_pojaluysta_utochnite_parametryi_zaprosa'],
						iconCls: 'info16',
						showFx: {
							delay: 0,
							//duration: 0.5, //defaults to 1 second
							mode: 'standard',//null,'standard','custom',or default ghost
							useProxy: false //default is false to hide window instead
						},
						width: 250 //optional (can also set minWidth which = 200 by default)
					}).show(Ext.getDoc());
					EvnReceptFarmacySearchViewGrid.getStore().removeAt(len - 1);
					len--;
				}
				if ( len > 0 )
				{
					EvnReceptFarmacySearchViewGrid.focus();
					EvnReceptFarmacySearchViewGrid.getView().focusRow(0);
					EvnReceptFarmacySearchViewGrid.getSelectionModel().selectFirstRow();
					// Элементы типа tbtext не берутся по id? o_O
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.items[10].el.innerHTML = '1 / ' + len;
				}
				else {
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_DeleteReceptBtn').disable();
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_EditReceptBtn').disable();
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_ViewReceptBtn').disable();
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.items[10].el.innerHTML = '0 / 0';
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
			function () { EvnReceptFarmacySearchFilterForm.getForm().findField(0).focus()}
		);
	}
}

sw.Promed.swReceptFarmacySearchWindow = Ext.extend(sw.Promed.BaseForm, {
	getRecordsCount: function() {
		var current_window = this;
		
		if ( EvnReceptFarmacySearchFilterForm.isEmpty() )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { EvnReceptFarmacySearchFilterForm.getForm().findField(0).focus()});
			return false;
		}

		if (EvnReceptFarmacySearchFilterForm.getForm().isValid() ) {
		
			var post = EvnReceptFarmacySearchFilterForm.getForm().getValues();

			var loadMask = new Ext.LoadMask(Ext.get('EvnReceptFarmacySearchWindow'), { msg: "Подождите, идет подсчет записей..." });
			loadMask.show();

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
				url: C_SEARCH_RECINCCNT
			});
		}	
	},
	closeAction : "hide",
	id : "EvnReceptFarmacySearchWindow",
	modal: false,
	maximizable: true,
	height: 500,
	width: 670,
	layout: 'border',
	printEvnRecept: function() {
		var grid = this.findById('EvnReceptFarmacySearchViewGrid');

		if (!grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var evn_recept_id = grid.getSelectionModel().getSelected().get('EvnRecept_id');
		var server_id = grid.getSelectionModel().getSelected().get('Server_id');

		window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id + '&Server_id=' + server_id, '_blank');
	},
	refreshReceptList: function()
	{
		if(EvnReceptFarmacySearchViewGrid.getStore().getCount()!=0) {
			EvnReceptFarmacySearchViewGrid.getStore().reload();
			EvnReceptFarmacySearchViewGrid.getView().focusRow(0);
			EvnReceptFarmacySearchViewGrid.getSelectionModel().selectFirstRow();
		}
	},
	getLastFieldOnCurrentTab: function() {
		return getLastFieldOnForm(EvnReceptFarmacySearchFilterForm.findById('RFSWTabPanel').getActiveTab());
	},
	printEvnReceptList: function() {
		var grid = this.findById('EvnReceptFarmacySearchViewGrid');
		Ext.ux.GridPrinter.print(grid);
	},
	listeners : {
		'beforeshow' : function() {
			EvnReceptFarmacySearchFilterForm.getForm().reset();
			EvnReceptFarmacySearchViewGrid.store.removeAll();
		},
		'show' : function() {
			this.restore();
			this.center();
			this.maximize();

			EvnReceptFarmacySearchFilterForm.findById('RFSW_Person_Surname').focus(true, 500);
			loadComboOnce(EvnReceptFarmacySearchFilterForm.findById('RFSW_MedPersonalCombo'), lang['meditsinskiy_personal']);
			// если минздрав и не добавлена вкладка для минздрава, то добавляем вкладку
			if ( getGlobalOptions().isMinZdravOrNotLpu )
			{
				this.findById('RFSWTabPanel').unhideTabStripItem('lpu_tab');
				// Переключение на нужный таб
				this.findById('RFSWTabPanel').setActiveTab(6);
				Ext.getCmp('RFSW_SearchedLpuCombo').getStore().load();
				Ext.getCmp('RFSW_SearchedLpuCombo').getStore().filterBy(function(record) {
					if ( 
						record.get('Lpu_DloBegDate') != '' && 
						(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
						(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
					) 
						return true;					
				});
				// копируем в сторе
				if ( !this.terrAdded )
				{
					var terr_store = new Ext.db.AdapterStore({
						autoLoad: true,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'OMSSprTerr_Code', type: 'int' },
							{ name: 'OMSSprTerr_id', type: 'int' },
							{ name: 'OMSSprTerr_Name', type: 'string' }
						],
						key: 'OMSSprTerr_id',
						sortInfo: {
							field: 'OMSSprTerr_Code',
							direction: 'ASC'
						},
						tableName: 'OMSSprTerr'
					});
					terr_store.load();
					var records = getStoreRecords(terr_store);
					Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().loadData([{OMSSprTerr_Code: -1, OMSSprTerr_id: -1, OMSSprTerr_Name: lang['permskiy_kray']}]);
					Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().loadData(records, true);
					//Ext.getCmp('RFSW_LpuAreaCombo').getStore().loadData([{LpuArea_id: 0, LpuArea_Name: ''}], true);
					Ext.getCmp('RFSW_LpuAreaCombo').getStore().loadData([{Lpu_IsOblast_id: 2, Lpu_IsOblast_Name: lang['kraevyie']}], true);
					Ext.getCmp('RFSW_LpuAreaCombo').getStore().loadData([{Lpu_IsOblast_id: 1, Lpu_IsOblast_Name: lang['ne_kraevyie']}], true);
					this.terrAdded = true;
				}
				Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().clearFilter();
				Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().filterBy(function(record) {
					if ( record.get('OMSSprTerr_Code') < 62 && record.get('OMSSprTerr_Code') != 0 ) 
						return true;
				});
				Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').setValue(-1);
				/*if ( !this.terrAdded )
				{
					var record = new Ext.data.Record({ 
						'OMSSprTerr_Code': -2,
						'OMSSprTerr_id': 100500,
						'OMSSprTerr_Name': lang['ves_kray']
					});
					Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().add(record, true)
					var record = new Ext.data.Record({ 
						'OMSSprTerr_Code': -1,
						'OMSSprTerr_id': 100501,
						'OMSSprTerr_Name': lang['tolko_kraevyie_lpu']
					});
					Ext.getCmp('RFSW_SearchedOMSSprTerrCombo').getStore().add(record, true)
					this.terrAdded = true;
				}*/
				
			}
			else
			{
				this.findById('RFSWTabPanel').hideTabStripItem('lpu_tab');
			}
			
			// Переключение на нужный таб
			this.findById('RFSWTabPanel').setActiveTab(4);
			
			EvnReceptFarmacySearchFilterForm.getForm().getEl().dom.action = C_EVNRECINC_PRINTSEARCH;
			EvnReceptFarmacySearchFilterForm.getForm().getEl().dom.method = "post";
			EvnReceptFarmacySearchFilterForm.getForm().getEl().dom.target = "_blank";
			EvnReceptFarmacySearchFilterForm.getForm().standardSubmit = true;
			
			loadComboOnce(EvnReceptFarmacySearchFilterForm.findById('RFSW_OrgFarmacy'), lang['apteki']);
		},
		'hide' : function() {
			EvnReceptFarmacySearchViewGrid.store.removeAll();
		}
	},
	initComponent : function() {
		this.EvnReceptFarmacySearchGridStore = new Ext.data.Store({
			id: 'EvnReceptFarmacySearchGridStore',
			url: C_EVNRECINC_SEARCH,
			reader : new Ext.data.JsonReader({
				id : "EvnRecept_id",
				root : "data",
				totalProperty : "count"
			}, [{
				name : "EvnRecept_id",
				mapping : "EvnRecept_id",
				type : "int"
			}, {
				name : "Lpu_Nick",
				mapping : "Lpu_Nick",
				type : "string"
			}, {
				name : "ReceptDelayType_id",
				mapping : "ReceptDelayType_id",
				type : "int"
			}, {
				name : "ReceptDelayType_Name",
				mapping : "ReceptDelayType_Name",
				type : "string"
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
				name : "Person_Snils",
				mapping : "Person_Snils",
				type : "string"
			}, {
				name : "ReceptFinance_Name",
				mapping : "ReceptFinance_Name",
				type : "string"
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
				type : "string"
			}, {
				name : "MedPersonal_Fio",
				mapping : "MedPersonal_Fio",
				type : "string"
			}, {
				name : "DrugMnn_Name",
				mapping : "DrugMnn_Name",
				type : "string"
			}, {
				name : "OrgFarmacy_Name",
				mapping : "OrgFarmacy_Name",
				type : "string"
			}, {
				name : "Drug_Name",
				mapping : "Drug_Name",
				type : "string"
			}, {
				name : "EvnRecept_setDate",
				mapping : "EvnRecept_setDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_obrDate",
				mapping : "EvnRecept_obrDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_otpDate",
				mapping : "EvnRecept_otpDate",
				type : "date",
				dateFormat:'d.m.Y'
			}, {
				name : "EvnRecept_obrDay",
				mapping : "EvnRecept_obrDay",
				type : "int"
			}, {
				name : "EvnRecept_otsDay",
				mapping : "EvnRecept_otsDay",
				type : "int"
			}, {
				name : "EvnRecept_otovDay",
				mapping : "EvnRecept_otovDay",
				type : "int"
			}
			/*, {
				name : "EvnRecept_otpDate",
				mapping : "EvnRecept_otpDate",
				type : "date",
				dateFormat:'d.m.Y'
			}*/])
		});

		this.EvnReceptFarmacySearchViewGrid = new Ext.grid.GridPanel({
			bbar: new Ext.PagingToolbar ({
				store: this.EvnReceptFarmacySearchGridStore,
				pageSize: 100,
				displayInfo: true,
				displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
		        emptyMsg: "Нет записей для отображения"
			}),
			id : 'EvnReceptFarmacySearchViewGrid',
			//autoExpandColumn: 'autoexpand_drug',
			//autoExpandMin: 100,
			region: 'center',
			tabIndex : 13,
			store : this.EvnReceptFarmacySearchGridStore,
			loadMask : true,
			columns : [{
				hidden : true,
				sortable : true,
				dataIndex : "ReceptDelayType_id",
				header : lang['status']
			},{
				hidden : false,
				sortable : true,
				dataIndex : "ReceptDelayType_Name",
				header : lang['status']
			},{
				hidden : !getGlobalOptions().isMinZdravOrNotLpu,
				sortable : true,
				dataIndex : "Lpu_Nick",
				header : lang['lpu']
			},{
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
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "Person_Snils",
				header : lang['snils']
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
				dataIndex : "ReceptFinance_Name",
				header : lang['finansirovanie'],
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "MedPersonal_Fio",
				header : lang['vrach'],
				width: 200
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "DrugMnn_Name",
				header : lang['mnn'],
				width: 200
			}, {
				hidden : false,
				//id: 'autoexpand_drug',
				sortable : true,
				dataIndex : "Drug_Name",
				header : "Торговое наименование",
				width: 400
			}, {
				css: 'text-align: right;',
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_Kolvo",
				header : lang['kolichestvo'],
				width: 100
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "OrgFarmacy_Name",
				header : lang['apteka'],
				width: 300
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_setDate",
				header : "Дата выписки",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_obrDate",
				header : "Дата обращения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			},{
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otpDate",
				header : "Дата отоваривания",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				width: 90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_obrDay",
				header : "Срок обращения",
				width:90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otsDay",
				header : lang['otsrochka'],
				width: 90
			}, {
				align : "right",
				hidden : false,
				sortable : true,
				dataIndex : "EvnRecept_otovDay",
				header : "Срок отоваривания",
				width: 90
			}],
			border : false,
			tbar : new Ext.Toolbar(
			[{
				text : BTN_GRIDADD,
				iconCls: 'add16',
				handler : function(button, event) {
					EvnReceptFarmacySearchViewGrid.addRecept();
				}.createDelegate(this),
				id: 'RFSW_NewReceptBtn',
				tooltip : "Ввод нового рецепта <b>(INS)</b>",
				iconCls: 'add16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptFarmacySearchViewGrid.openRecept('edit');
				}.createDelegate(this),
				id: 'RFSW_EditReceptBtn',
				iconCls: 'edit16',
				text : BTN_GRIDEDIT,
				tooltip : "Редактирование выбранного рецепта <b>(F4)</b>",
				iconCls: 'edit16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptFarmacySearchViewGrid.openRecept('view');
				}.createDelegate(this),
				id: 'RFSW_ViewReceptBtn',
				iconCls: 'view16',
				text : BTN_GRIDVIEW,
				tooltip : "Просмотр выбранного рецепта <b>(F3)</b>",
				iconCls: 'view16'
			}, {
				disabled: true,
				handler : function(button, event) {
					EvnReceptFarmacySearchViewGrid.deleteRecept();
				}.createDelegate(this),
				id: 'RFSW_DeleteReceptBtn',
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
				id: 'RFSW_ActionBtn',
				iconCls: 'actions16'
			}, {
				xtype : "tbseparator"
			}, {
				disabled: false,
				iconCls: 'refresh16',
				text : BTN_GRIDREFR,
				handler : function(button, event) {
					Ext.getCmp('EvnReceptFarmacySearchWindow').refreshReceptList();
				}.createDelegate(this),
				id: 'RFSW_RefreshBtn',
				tooltip : "Обновление списка с сервера <b>(F5)</b>",
				iconCls: 'refresh16'
			}, {
				iconCls: 'print16',
				menu: [{
					handler: function() {
						Ext.getCmp('EvnReceptFarmacySearchWindow').printEvnReceptList();
					},
					text: lang['lgotnyie_retseptyi_spisok_f9'],
					xtype: 'tbbutton'
				}, {
					handler: function() {
						Ext.getCmp('EvnReceptFarmacySearchWindow').printEvnRecept();
					},
					text: lang['lgotnyiy_retsept_ctrl_+_f9'],
					xtype: 'tbbutton'
				}],
				text: BTN_GRIDPRINT,
				xtype: 'tbbutton'
			}, {
				xtype : "tbfill"
			}, {
				id: 'RFSW_GridCounter',
				text: '0 / 0',
				xtype: 'tbtext'
			}]),
			enableKeyEvents: true,
			listeners : {
				'rowdblclick' : function (grd, rowIndex, e) {
					grd.openRecept('view');
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

					var grd = EvnReceptFarmacySearchViewGrid;

					var selected_record = grd.getSelectionModel().getSelected();
					var params = new Object();
					params.Person_id = selected_record.get('Person_id');
					params.Server_id = selected_record.get('Server_id');
					params.Person_Birthday = selected_record.get('Person_Birthday');
					params.Person_Firname = selected_record.get('Person_Firname');
					params.Person_Secname = selected_record.get('Person_Secname');
					params.Person_Surname = selected_record.get('Person_Surname');

					switch (e.getKey())
					{
						case Ext.EventObject.ENTER:
							grd.openRecept('view');
						break;
						
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
							if (!grd.getSelectionModel().getSelected())
							{
								return false;
							}

							action = 'view';

							grd.openRecept(action);

						break;

						case Ext.EventObject.F5:
							Ext.getCmp('EvnReceptFarmacySearchWindow').refreshReceptList();
						break;

						case Ext.EventObject.F9:
							if (e.ctrlKey == true)
							{
								Ext.getCmp('EvnReceptFarmacySearchWindow').printEvnRecept();
							}
							else
							{
								Ext.getCmp('EvnReceptFarmacySearchWindow').printEvnReceptList();
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
								Ext.getCmp('RFSW_BottomButtons').buttons[0].focus(false, 100);
							}
							else {
								Ext.getCmp('EvnReceptFarmacySearchWindow').getLastFieldOnCurrentTab().focus(true);
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
								this.grid.getTopToolbar().items.item('RFSW_DeleteReceptBtn').disable();
								this.grid.getTopToolbar().items.item('RFSW_EditReceptBtn').disable();
								this.grid.getTopToolbar().items.item('RFSW_ViewReceptBtn').enable();
							}
							else
							{
								this.grid.getTopToolbar().items.item('RFSW_DeleteReceptBtn').disable();
								this.grid.getTopToolbar().items.item('RFSW_EditReceptBtn').disable();
								this.grid.getTopToolbar().items.item('RFSW_ViewReceptBtn').disable();
							}
							this.grid.getTopToolbar().items.items[10].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();
							
							record.set('set', 1);
							record.commit();
							//EvnReceptFarmacySearchViewGrid.getView().focusRow(index);
						},
						'rowdeselect': function(sm, rowIndex, record) {
							record.set('set', 0);
							record.commit();
						}
					}
				}),
			openRecept: function(action) {
				if (!EvnReceptFarmacySearchViewGrid.getSelectionModel().getSelected())
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_retsept_iz_spiska']);
					return false;
				}

				var selected_record = EvnReceptFarmacySearchViewGrid.getSelectionModel().getSelected();
				var evn_recept_id = selected_record.data.EvnRecept_id;
				var person_id = selected_record.data.Person_id;
				var person_evn_id = selected_record.data.PersonEvn_id;
				var server_id = selected_record.data.Server_id;

				if (evn_recept_id && person_id && person_evn_id && server_id >= 0)
				{
					getWnd('swEvnReceptEditWindow').show({
						action: action,
						callback: function(data) {
							setGridRecord(EvnReceptFarmacySearchViewGrid, data.EvnReceptData);
						},
						EvnRecept_id: evn_recept_id,
						onHide: function() {
							EvnReceptFarmacySearchViewGrid.getView().focusRow(EvnReceptFarmacySearchViewGrid.getStore().indexOf(selected_record));
							EvnReceptFarmacySearchViewGrid.getSelectionModel().selectRow(EvnReceptFarmacySearchViewGrid.getStore().indexOf(selected_record));
						},
						Person_id: person_id,
						PersonEvn_id: person_evn_id,
						Server_id: server_id
					});
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['dannyih_po_etomu_retseptu_net_v_baze']);
				}
			},
			deleteRecept: function() {
				return false;
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
		
		
		this.EvnReceptFarmacySearchViewGrid.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index, rowParams)
			{
				var cls = '';
				if (row.get('set') == 0 || row.get('set') == undefined) {
					if (row.get('ReceptDelayType_Name') == lang['otovaren'] )
						cls = cls+'x-grid-rowbackgreen ';
					if (row.get('ReceptDelayType_Name') == lang['otsrochen'] )
						cls = cls+'x-grid-rowbackyellow ';
					if (row.get('ReceptDelayType_Name') == lang['otkaz'] )
						cls = cls+'x-grid-rowbackred ';
					if (row.get('ReceptDelayType_Name') == lang['prosrochen'] )
						cls = cls+'x-grid-rowbackred ';
					if (cls.length == 0)
						cls = 'x-grid-panel';
				}
				return cls;
			}
		});

		this.EvnReceptFarmacySearchFilterForm = new Ext.form.FormPanel({
			id : "EvnSearchForm",
			labelWidth : 100,
			frame : false,
			border: false,
			region: 'north',
			items : [{
				id: 'RFSWTabPanel',
				items : [
				{
					title : "<u>1</u>. Рецепт",
					border: false,
					frame: false,
					height : 300,
					style: 'padding: 0px; margin-bottom: 5px;',
					items : [{
						layout: 'column',
						border : true,
						items: [{
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
								id: 'RFSW_ReceptDiscountCombo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											swReceptFarmacySearchWindow.findById('RFSW_BottomButtons').buttons[4].focus();
										}
									}
								},
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
								tabIndex : TABINDEX_RFSW + 1
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
								id: 'RFSW_ReceptValidCombo',
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
								tabIndex : TABINDEX_RFSW + 2
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
								id: 'RFSW_ReceptTypeCombo',
								hiddenName: 'ReceptType_id',
								tabIndex: TABINDEX_RFSW + 3,
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
										tabIndex : TABINDEX_RFSW + 4
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
										tabIndex : TABINDEX_RFSW + 5
									}],
									width: 300
								}
							]
						}]
					}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: lang['medikament'],
						style: 'padding: 3px; margin-bottom: 2px; display:block;',
						labelWidth : 103,
						items: [{
							allowBlank: true,
							id: 'RFSW_DrugMnnCombo',
							emptyText: lang['nachnite_vvodit_mnn'],
							onTrigger2Click: function() {
								var drug_mnn_combo = this;
								var current_window = Ext.getCmp('EvnReceptFarmacySearchWindow');

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
									Ext.getCmp('RFSW_DrugCombo').lastQuery = '';
								},
								'change': function(combo, newValue, oldValue) {
									var drug_combo = Ext.getCmp('RFSW_DrugCombo');

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
							tabIndex: TABINDEX_RFSW + 6,
							trigger2Class: 'hideTrigger',
							validateOnBlur: false,
							width: 517,
							xtype: 'swdrugmnncombo'
						}, {
							allowBlank: true,
							id: 'RFSW_DrugCombo',
							listeners: {
								'beforeselect': function(combo, record, index) {
									combo.setValue(record.get('Drug_id'));

									var drug_mnn_combo = Ext.getCmp('RFSW_DrugMnnCombo');
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
								var current_window = Ext.getCmp('EvnReceptFarmacySearchWindow');

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
							tabIndex: TABINDEX_RFSW + 7,
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
										id: 'RFSW_EvnRecept_IsNotOstat',
										tabIndex: TABINDEX_RFSW + 8,
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
										id: 'RFSW_OrgFarmacy',
										listeners: {
											'keydown': function (inp, e) {
												if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
													if (EvnReceptFarmacySearchViewGrid.getStore().getCount() > 0) {
														e.stopEvent();
														TabToGrid(EvnReceptFarmacySearchViewGrid);
													}
												}
											}
										},
										name: 'OrgFarmacy_id',
										tabIndex: TABINDEX_RFSW + 9,
										xtype : "sworgfarmacycombo",
										width: 300
								}],
								width: 360
							}]
						}]
					}]
				}, {
					title : "<u>2</u>. Рецепт (доп.)",
					border: false,
					frame: false, 
					height : 200,
					style: 'padding: 0px; margin-bottom: 5px;',
					items : 
					[{
						layout: 'column',
						border : false,
						items: 
						[{
							labelWidth : 200,
							columnWidth: .5,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: 
							[{
								displayField: 'YesNo_Name',
								codeField: 'YesNo_Code',
								editable: false,
								fieldLabel: lang['retsept_vyipisan_v_promed'],
								forceSelection : true,
								hiddenName: 'ReceptYes_id',
								tabIndex: TABINDEX_RFSW + 10,
								value: 2,
								valueField: 'YesNo_id',
								width: 70,
								xtype: 'swyesnocombo'
							},
							{
								//anchor: '100%',
								width: 300,
								fieldLabel: lang['rezultat'],
								hiddenName: 'ReceptResult_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										// 2. Не было обращения;  – при выборе устанавливать Выписан рецепт = Да
										if (newValue == 2) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										//9. Рецепт просрочен без обращения;  – при выборе устанавливать Выписан рецепт = Да
										if (newValue == 2) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										
										/*При изменении в поле Результат и если нарушается условие
										Результат ! = Не было обращения 
										+lang['i']+ 
										Результат ! = Рецепт просрочен без обращения,
										то поля Время обращ… очищать.*/
										if ( !(newValue != 2 && newValue != 9) ) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_obrTimeFrom').setValue('');
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_obrTimeTo').setValue('');
										}
										
										/*	При изменении в поле Результат и если нарушается условие
											Результат = Рецепт отоварен
											+lang['ili']+
											Результат = Рецепт отоварен после отсрочки
											+lang['ili']+
											Результат = Рецепт отсрочен,
											то поля "Отсрочка отоваривания рецепта, от ... до ..." очищать.
										*/
										if ( !(newValue == 4 || newValue == 6 || newValue == 7) )
										{
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otsTimeFrom').setValue('');
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otsTimeTo').setValue('');
										}		
										
										/*При изменении в поле Результат и если нарушается условие
										Результат = Рецепт отоварен
										+lang['ili']+ 
										Результат = Рецепт отоварен без отсрочки
										+lang['ili']+ 
										Результат = Рецепт отоварен после отсрочки,
										то поля Время отоваривания очищать.*/
										if ( !(newValue == 4 || newValue == 5 || newValue == 6) ) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otovTimeFrom').setValue('');
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otovTimeTo').setValue('');
										}
										
										/*При изменении в поле Результат и если нарушается условие
										Результат =  Рецепт отоварен после отсрочки
										+lang['ili']+ 
										Результат =  Рецепт отсрочен
										+lang['ili']+ 
										Результат =  Рецепт просрочен после отсрочки,
										то поле Дата актуальности отсрочки очищать.
										*/
										if ( !(newValue == 6 || newValue == 7 || newValue == 10) ) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otsDate').setValue('');
										}
										
										/* При установке Результат = Рецепт отсрочен, 
										   если поле Дата актуальности отсрочки пустое, 
										   то в поле Дата актуальности отсрочки ставить текущую дату.
										*/
										if ( newValue == 7 && EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otsDate').getValue() == "" )
										{
										 	EvnReceptFarmacySearchFilterForm.getForm().findField('EvnRecept_otsDate').setValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
										}								
									}
								},
								tabIndex: TABINDEX_RFSW + 11,
								valueField: 'ReceptResult_id',
								xtype: 'swreceptresultcombo'
							},
							{
								//anchor: '100%',
								width: 300,
								fieldLabel: 'Несовпадения в рецептах',
								hiddenName: 'ReceptMismatch_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										// При выборе в поле Несовпадения… любого значения кроме «пусто» устанавливать Выписан рецепт = Да
										if (newValue != null && newValue != '') {
											EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptYes_id').setValue(2);
										}
										/*и если нарушается условие 
										(Результат = Рецепт отоварен
										+lang['ili']+ 
										Результат = Рецепт отоварен без отсрочки
										+lang['ili']+ 
										Результат = Рецепт отоварен после отсрочки),
										то устанавливать Результат = Рецепт отоварен.*/
										var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
										if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
											EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
										}
									}
								},
								tabIndex: TABINDEX_RFSW + 12,
								valueField: 'ReceptMismatch_id',
								xtype: 'swreceptmismatchcombo'
							},
							{
								layout: 'column',
								labelWidth : 310,
								border : false,
								items: 
								[{
									labelWidth : 310,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_obrTimeFrom',
										id:  'RFSW_Recept_obrTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время обращ… любого значения и если нарушается условие 
												Результат ! = Не было обращения 
												+lang['i']+ 
												Результат ! = Рецепт просрочен без обращения,
												то устанавливать Результат = Было обращение.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( ReceptResult == 2 || ReceptResult == 9 ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['srok_obrascheniya_v_apteku_s_momenta_vyipiski_ot'],
										tabIndex: TABINDEX_RFSW + 13
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otsTimeFrom',
										id:  'RFSW_Recept_otsTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отсроч… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												+lang['ili']+ 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 6 || ReceptResult == 7) ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['otsrochka_otovarivaniya_retsepta_ot'],
										tabIndex: TABINDEX_RFSW + 14
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otovTimeFrom',
										id:  'RFSW_Recept_otovTimeFrom',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отовар… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												+lang['ili']+ 
												Результат = Рецепт отоварен без отсрочки
												+lang['ili']+ 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['srok_otov_retsepta_s_momenta_vyipiski_ot'],
										tabIndex: TABINDEX_RFSW + 15
									}
									]
								},
								{
									labelWidth : 30,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_obrTimeTo',
										id:  'RFSW_Recept_obrTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время обращ… любого значения и если нарушается условие 
												Результат ! = Не было обращения 
												+lang['i']+ 
												Результат ! = Рецепт просрочен без обращения,
												то устанавливать Результат = Было обращение.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( ReceptResult == 2 || ReceptResult == 9 ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['do'],
										tabIndex: TABINDEX_RFSW + 16
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otsTimeTo',
										id:  'RFSW_Recept_otsTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отсроч… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												+lang['ili']+ 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 6 || ReceptResult == 7) ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['do'],
										tabIndex: TABINDEX_RFSW + 17
									},
									{
										ancor: '100%',
										xtype: 'numberfield',
										name: 'EvnRecept_otovTimeTo',
										id:  'RFSW_Recept_otovTimeTo',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												/*При вводе в поля Время отовар… любого значения и если нарушается условие 
												Результат = Рецепт отоварен
												+lang['ili']+ 
												Результат = Рецепт отоварен без отсрочки
												+lang['ili']+ 
												Результат = Рецепт отоварен после отсрочки,
												то устанавливать Результат = Рецепт отоварен.
												*/
												if (newValue != '') {
													var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
													if ( !(ReceptResult == 4 || ReceptResult == 5 || ReceptResult == 6) ) {
														EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
													}
												}
											}
										},
										maxValue: 356,
										minValue: 0,
										autoCreate: {tag: "input", size:3, maxLength: "6", autocomplete: "off"},
										fieldLabel: lang['do'],
										tabIndex: TABINDEX_RFSW + 18
									}]
								}, {
									labelWidth : 30,
									layout: 'form',
									autoHeight: true,
									border : false,
									items:
									[{
										ancor: '100%',
										fieldLabel: lang['dn'],
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									},
									{
										ancor: '100%',
										fieldLabel: lang['dn'],
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									},
									{
										ancor: '100%',
										fieldLabel: lang['dn'],
										labelSeparator: '',
										hidden: true,
										xtype: 'textfield'
									}]
								}]
							}]
						}, {
							labelWidth : 180,
							columnWidth: .5,
							layout: 'form',
							autoHeight: true,
							border : false,
							style: 'margin-top: 3px',
							items: 
							[{
								name : "EvnRecept_setDate",
								id : "RFSW_Recept_setDate",
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Выписка рецепта",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : TABINDEX_RFSW + 19
							},
							{
								name : "EvnRecept_obrDate",
								id : "RFSW_Recept_obrDate",
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Диапазон дат обращения в аптеку любого значения и если нарушается условие 
										Результат ! = Не было обращения 
										+lang['i']+ 
										Результат ! = Рецепт просрочен без обращения,
										то устанавливать Результат = Было обращение.*/
										if (newValue != '') {
											var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( ReceptResult == 2 || ReceptResult == 9 ) {
												EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(1);
											}
										}
									}
								},
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Обращение в аптеку",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : TABINDEX_RFSW + 20
							},
							{
								name : "EvnRecept_otpDate",
								id : "RFSW_Recept_otpDate",
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Диапазон дат отоваривания рецепта любого значения и если нарушается условие 
										Результат = Рецепт отоварен
										+lang['ili']+ 
										Результат = Рецепт отоварен без отсрочки
										+lang['ili']+ 
										Результат = Рецепт отоварен после отсрочки,
										то устанавливать Результат = Рецепт отоварен.
										*/
										if (newValue != '') {
											var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( !(ReceptResult == 4 || ReceptResult == 7 || ReceptResult == 10) ) {
												EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(4);
											}
										}
									}
								},
								xtype : "daterangefield",
								width : 170,
								fieldLabel : "Отоваривание рецепта",
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								tabIndex : TABINDEX_RFSW + 21
							},
							{
								allowBlank: true,
								name : "EvnRecept_otsDate",
								id : "RFSW_Recept_otsDate",
								format: 'd.m.Y',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										/*При вводе в поле Дата актуальности отсрочки любого значения и если нарушается условие 
										Результат =  Рецепт отоварен после отсрочки
										+lang['ili']+ 
										Результат =  Рецепт отсрочен
										+lang['ili']+ 
										Результат =  Рецепт просрочен после отсрочки,
										то устанавливать Результат = Рецепт отсрочен.*/
										if (newValue != '') {
											var ReceptResult = EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').getValue();
											if ( !(ReceptResult == 6 || ReceptResult == 7 || ReceptResult == 10) ) {
												EvnReceptFarmacySearchFilterForm.getForm().findField('ReceptResult_id').setValue(7);
											}
										}
									}
								},					
								xtype : "swdatefield",
								width : 100,
								fieldLabel : "Актуальность отсрочки",
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex : TABINDEX_RFSW + 22
							}, {
								displayField: 'ReceptFinance_Name',
								codeField: 'ReceptFinance_Code',
								editable: false,
								fieldLabel: lang['finansirovanie'],
								hiddenName: 'ReceptFinance_id',
								//hideTrigger: true,
								id: 'RFSW_ReceptFinanceCombo',
								lastQuery: '',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										Ext.getCmp('RFSW_DrugMnnCombo').getStore().baseParams.ReceptFinance_Code = newValue;
										Ext.getCmp('RFSW_DrugCombo').getStore().baseParams.ReceptFinance_Code = newValue;
									},
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptFarmacySearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptFarmacySearchViewGrid);
											}
										}
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
								width: 170,
								xtype: 'swbaselocalcombo',
								tabIndex : TABINDEX_RFSW + 23
							}]
						}]
					}]
				},
				{
					title : "<u>3</u>. ЛПУ",
					border: false,
					frame: false,
					height : 300,
					id: 'lpu_tab',
					labelWidth : 140,
					layout: 'form',
					style: 'padding: 5px; margin-bottom: 5px;',
					items : [{
						fieldLabel: lang['territoriya'],
						hiddenName: 'SearchedOMSSprTerr_Code',
						hideEmptyRow: true,
						id: 'RFSW_SearchedOMSSprTerrCombo',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue) {
								var lpu_combo = Ext.getCmp('RFSW_SearchedLpuCombo');
								var obl_combo = Ext.getCmp('RFSW_LpuAreaCombo');
								var obl_val = obl_combo.getValue();
								lpu_combo.getStore().clearFilter();
								lpu_combo.clearValue();
								lpu_combo.getStore().filterBy(function(record) {
									if (
										( obl_val == '' || obl_val == record.get('Lpu_IsOblast') ) &&																		
										((record.get('Lpu_RegNomC2') == newValue) || (newValue == 1 && record.get('Lpu_RegNomC2') <= 7)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
									)
										return true;
									if (
										( obl_val == '' || obl_val == record.get('Lpu_IsOblast') ) &&																		
										((newValue == 0) || (newValue == null) || (newValue == -1)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y'))
									)
										return true;
								});															
							}
						},
						tabIndex: TABINDEX_RFSW + 24,
						valueField: 'OMSSprTerr_Code',
						width : 300,
						xtype: 'swomssprterrsimplecombo'
					},
					new sw.Promed.SwBaseLocalCombo ({
						allowBlank: true,
						displayField: 'Lpu_IsOblast_Name',
						editable: true,
						fieldLabel: lang['prinadlejnost_lpu'],
						hiddenName: 'Lpu_IsOblast_id',
						id: 'RFSW_LpuAreaCombo',
						listeners: {
							'change': function(combo, newValue) 
							{
								var lpu_combo = Ext.getCmp('RFSW_SearchedLpuCombo');
								var terr_combo = Ext.getCmp('RFSW_SearchedOMSSprTerrCombo');
								var terr_val = terr_combo.getValue();
								lpu_combo.getStore().clearFilter();
								lpu_combo.clearValue();
								lpu_combo.getStore().filterBy(function(record) {
									if ( 
									    ( newValue == '' || newValue == record.get('Lpu_IsOblast') ) &&
										((record.get('Lpu_RegNomC2') == terr_val) || (terr_val == 1 && record.get('Lpu_RegNomC2') <= 7)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) 
									)
										return true;
									if (
										( newValue == '' || newValue == record.get('Lpu_IsOblast') ) &&									
										((terr_val == 0) || (terr_val == null) || (terr_val == -1)) && 
										record.get('Lpu_DloBegDate') != '' && 
										(record.get('Lpu_DloEndDate') == '' || Date.parseDate(record.get('Lpu_DloEndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y')) && 
										(record.get('Lpu_EndDate') == '' || Date.parseDate(record.get('Lpu_EndDate'), 'Y-m-d') > Date.parseDate('01.10.2009', 'd.m.Y'))
									)
										return true;
								});					
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_IsOblast_Name}&nbsp;',
							'</div></tpl>'
						),
						valueField: 'Lpu_IsOblast_id',
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{ name: 'Lpu_IsOblast_id', mapping: 'Lpu_IsOblast_id' },
								{ name: 'Lpu_IsOblast_Name', mapping: 'Lpu_IsOblast_Name' }								
							],
							key: 'Lpu_IsOblast_id',
							sortInfo: { field: 'Lpu_IsOblast_Name' }							
						}),
						tabIndex: TABINDEX_RFSW + 25,
						width : 300
					}),
					{
						fieldLabel: lang['lpu'],
						hiddenName: 'SearchedLpu_id',
						id: 'RFSW_SearchedLpuCombo',
						lastQuery: '',
						tabIndex: TABINDEX_RFSW + 26,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{[(values.Lpu_EndDate != "" && Date.parseDate(values.Lpu_EndDate, "Y-m-d") < Date.parseDate(getGlobalOptions().date, "d.m.Y")) ? values.Lpu_Nick + " (закрыта " + Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate, "Y-m-d"), "d.m.Y") + ")" : values.Lpu_Nick ]}&nbsp;',
							'</div></tpl>'
						),
						width : 300,
						xtype: 'swlpucombo'
					}]
				}
				],
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
					SearchFarmacyRecept();
				},
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			height : 500,
			items : [
				this.EvnReceptFarmacySearchFilterForm,
				this.EvnReceptFarmacySearchViewGrid, {
				id : "RFSW_BottomButtons",
				region : "south",
				height : 40,
				buttons : [{
					text : BTN_FRMSEARCH,
					iconCls: 'search16',
					handler: function() {
						SearchFarmacyRecept();
					}.createDelegate(this),
					onTabAction : function () {
						Ext.getCmp('RFSW_BottomButtons').buttons[1].focus(false, 0);
					},
					onShiftTabAction : function () {
						if (EvnReceptFarmacySearchViewGrid.getStore().getCount() == 0) {
							Ext.getCmp('EvnReceptFarmacySearchWindow').getLastFieldOnCurrentTab().focus(true);
							return;
						}
						var selected_record = EvnReceptFarmacySearchViewGrid.getSelectionModel().getSelected();
						if (selected_record != -1) {
							var index = EvnReceptFarmacySearchViewGrid.getStore().indexOf(selected_record);
						}
						else {
							var index = 0;
						}
						EvnReceptFarmacySearchViewGrid.getView().focusRow(index);
    					EvnReceptFarmacySearchViewGrid.getSelectionModel().selectRow(index);
					},
					tabIndex : TABINDEX_RFSW + 90
				}, {
					text : BTN_FRMRESET,
					iconCls: 'resetsearch16',
					handler : function(button, event) {
						EvnReceptFarmacySearchFilterForm.getForm().reset();
						EvnReceptFarmacySearchViewGrid.store.removeAll();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_DeleteReceptBtn').disable();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_EditReceptBtn').disable();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_ViewReceptBtn').disable();
					}.createDelegate(this),
					tabIndex : TABINDEX_RFSW + 91
				}, {
					text : BTN_FRMCOUNT,
					iconCls: 'search16',
					handler : function(button, event) {
						Ext.getCmp('EvnReceptFarmacySearchWindow').getRecordsCount();
					}.createDelegate(this),
					tabIndex : TABINDEX_RFSW + 92
				}, {
					handler: function() {
						EvnReceptFarmacySearchFilterForm.getForm().submit();
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
						EvnReceptFarmacySearchFilterForm.findById('RFSW_Person_Surname').focus(true, 0);
					},
					onShiftTabAction : function () {
						Ext.getCmp('RFSW_BottomButtons').buttons[1].focus(false, 0);
					},
					tabIndex : TABINDEX_RFSW + 93
				}
				],
				buttonAlign : "left"
				}
			],
			keys: [{
				key: Ext.EventObject.INSERT,
				fn: function(e) {
					EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_NewReceptBtn').handler();
				},
				stopEvent: true
			}, {
				key: "123456789",
				alt: true,
				fn: function(e) {
					Ext.getCmp("RFSWTabPanel").setActiveTab(Ext.getCmp("RFSWTabPanel").items.items[ e - 49 ]);
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
						EvnReceptFarmacySearchFilterForm.getForm().reset();
						EvnReceptFarmacySearchViewGrid.store.removeAll();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_DeleteReceptBtn').disable();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_EditReceptBtn').disable();
						EvnReceptFarmacySearchViewGrid.getTopToolbar().items.item('RFSW_ViewReceptBtn').disable();
						return false;
					}

					if (e.getKey() == Ext.EventObject.G)
					{
						SearchFarmacyRecept();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.G ],
				scope: this,
				stopEvent: false
			}],
			title : lang['jurnal_otsrochki_dlya_aptek'],
			width : 670,
			xtype : "window"
		});
		EvnReceptFarmacySearchFilterForm = this.EvnReceptFarmacySearchFilterForm;
		EvnReceptFarmacySearchViewGrid = this.EvnReceptFarmacySearchViewGrid;
		sw.Promed.swReceptFarmacySearchWindow.superclass.initComponent.apply(this, arguments);

		Ext.getCmp('RFSW_DrugCombo').getStore().baseParams.searchFull = 1;
		Ext.getCmp('RFSW_DrugMnnCombo').getStore().baseParams.searchFull = 1;
	}
});