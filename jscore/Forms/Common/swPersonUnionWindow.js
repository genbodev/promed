/**
* swPersonUnionWindow - окно объединения людей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      18.10.2009
*/
/*NO PARSE JSON*/
sw.Promed.swPersonUnionWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'swPersonUnionWindow',
	successFn: null, // функция вызывающаяся при удачном объединении
	// Функция объединения записей
	doPersonUnion: function () {
		grid = Ext.getCmp('PUW_RecordGrid');
		var hasMainRec = false;
		grid.getStore().each(function(record) {
			if (record.data.IsMainRec == 1) {
				hasMainRec = true;
			}
		});
		if (grid.getStore().getCount()<2){
			sw.swMsg.alert(lang['vnimanie'],lang['dlya_obyedineniya_doljnyi_byit_hotya_byi_2_zapisi']);
			return false;
		}
		if (!hasMainRec){
			sw.swMsg.alert(lang['vnimanie'],lang['doljna_byit_vyibrana_glavnaya_zapis_dlya_obyedineniya']);
			return false;
		}
		var Mask = new Ext.LoadMask(Ext.get('swPersonUnionWindow'), {msg:"Пожалуйста, подождите, идет сохранение данных..."});
		Mask.show();
		controlStoreRequest = Ext.Ajax.request({
			url: C_PERSON_UNION,
			success: function(result){
				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext.util.JSON.decode(result.responseText);
					if (resp_obj.success == true) {
						if (resp_obj.Info_Msg) {
							showSysMsg(lang['vyibrannyie_zapisi_uspeshno_otpravlenyi_na_moderatsiyu']
								+ '<br />' + resp_obj.Info_Msg,lang['spasibo']);
						} else if (resp_obj.Success_Msg) {
							showSysMsg(lang['vyibrannyie_zapisi_uspeshno_otpravlenyi_na_moderatsiyu'],lang['spasibo']);
						}
					}
				}
				Mask.hide();
				if (handleResponseError(result))
					Ext.getCmp('swPersonUnionWindow').hide();
				if (Ext.getCmp('swPersonUnionWindow').successFn!==null) {
					Ext.getCmp('swPersonUnionWindow').successFn.call(this);
				}
			},
			params: {
					'Records': Ext.util.JSON.encode(getStoreRecords(grid.getStore()))
				},
			failure: function(result){
				Mask.hide();
			},
			method: 'POST',
				timeout: 120000
		});
	},
	// Функция переноса случаев по человеку
	doPersonEvnTransfer: function () {
		grid = Ext.getCmp('PUW_RecordGrid');
		var hasMainRec = false;
		grid.getStore().each(function(record) {
			if ( record.data.IsMainRec == 1 ) {
				hasMainRec = true;
			}
		});
		if ( grid.getStore().getCount() < 2 ){
			sw.swMsg.alert(lang['vnimanie'],lang['dlya_perenosa_sluchaev_doljnyi_byit_hotya_byi_2_zapisi']);
			return false;
		}
		if (!hasMainRec){
			sw.swMsg.alert(lang['vnimanie'],lang['doljna_byit_vyibrana_glavnaya_zapis_dlya_perenosa_sluchaev']);
			return false;
		}
		var Mask = new Ext.LoadMask(Ext.get('swPersonUnionWindow'), {msg:"Пожалуйста, подождите, идет перенос данных случаев..."});
		Mask.show();
		Ext.Ajax.request({
			url: C_PERSON_TRANSFER,
			success: function(result){
				Mask.hide();

				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext.util.JSON.decode(result.responseText);
					if (resp_obj.success == true && resp_obj.Info_Msg) {
						sw.swMsg.alert(lang['soobschenie'], resp_obj.Info_Msg);
					}
				}
				if (handleResponseError(result))
					Ext.getCmp('swPersonUnionWindow').hide();
				if (Ext.getCmp('swPersonUnionWindow').successFn!==null) {
					Ext.getCmp('swPersonUnionWindow').successFn.call(this, {});
				}
			},
			params: {
				'Records': Ext.util.JSON.encode(getStoreRecords(grid.getStore()))
			},
			failure: function(result){
				Mask.hide();
			},
			method: 'POST',
				timeout: 120000
		});
	},
	initComponent: function() {
		var win = this;

		Ext.apply(this, {
			buttons: [ {
				handler: function() {
					this.ownerCt.doPersonUnion();
				},
				iconCls: 'copy16',
				text: BTN_FRMUNION
			}, {
				handler: function() {
					this.ownerCt.doPersonEvnTransfer();
				},
				iconCls: 'copy16',
				text: lang['perenos_sluchaev'],
				hidden: !isAdmin
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.returnFunc();
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}
			],
			items: [
			new Ext.grid.GridPanel({
				autoExpandColumn: 'Record_Name',
				bodyBorder: false,
				border: false,
				// autoHeight: true,
				// height: 100,
				id: 'PUW_RecordGrid',
				columns: [{
					dataIndex: 'Person_Surname',
					header: lang['familiya'],
					hidden: false,
					id: 'Person_Surname',
					sortable: true
				}, {
					dataIndex: 'Person_Firname',
					header: lang['imya'],
					hidden: false,
					id: 'Person_Firname',
					sortable: true
				}, {
					dataIndex: 'Person_Secname',
					header: lang['otchestvo'],
					hidden: false,
					id: 'Person_Secname',
					sortable: true
				}, {
					dataIndex: 'Person_Birthdate',
					header: lang['data_rojdeniya'],
					hidden: false,
					id: 'Person_Birthdate',
					//renderer: Ext.util.Format.dateRenderer('d.m.Y'), // в chrome 67 сбрасывает на 1970 год
					renderer: function(v,m) {
						var dt = new Date(v);
						if( isValidDate(dt) ) {
							return Ext.util.Format.date(dt, 'd.m.Y');
						}
					},
					sortable: true
				}, {
					dataIndex: 'Server_id',
					header: lang['identifikator_servera'],
					hidden: false,
					id: 'Server_id',
					sortable: true
				}],
				region: 'center',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'Person_id'
					}, [{
						mapping: 'Person_id',
						name: 'Person_id',
						type: 'int'
					}, {
						mapping: 'Person_Surname',
						name: 'Person_Surname',
						type: 'string'
					}, {
						mapping: 'Person_Firname',
						name: 'Person_Firname',
						type: 'string'
					}, {
						mapping: 'Person_Secname',
						name: 'Person_Secname',
						type: 'string'
					}, {
						mapping: 'Person_Birthdate',
						name: 'Person_Birthdate',
						type: 'string'
					}, {
						mapping: 'Server_id',
						name: 'Server_id',
						type: 'int'
					}, {
						mapping: 'IsMainRec',
						name: 'IsMainRec',
						type: 'int'
					}, {
						mapping: 'IsMedPersonal',
						name: 'IsMedPersonal',
						type: 'int'
					}])
				}),
				stripeRows: true,
				tbar : new Ext.Toolbar(
					[{
						id: 'PUW_SelectMainButton',
						handler : function(button, event) {
							var grid = Ext.getCmp('PUW_RecordGrid');
							var selectedRecord = grid.getSelectionModel().getSelected();

							if (win.HasMedPersonal && !selectedRecord.get('IsMedPersonal')) {
								return false;
							}

							grid.getStore().each(function(record) {
								record.set('IsMainRec', 0);
								record.commit();
							});
							selectedRecord.set('IsMainRec', 1);
							selectedRecord.commit();
						}.createDelegate(this),
						text : "Главная запись",
						tooltip : "Сделать главной записью",
						iconCls: 'actions16'
					}, {
						xtype : "tbseparator"
					}, {
						handler : function(button, event) {
							grid = Ext.getCmp('PUW_RecordGrid');
							grid.getStore().remove(grid.getSelectionModel().getSelected());
						}.createDelegate(this),
						text : BTN_GRIDDEL,
						tooltip : "Удаление выбранной записи <b>(DEL)</b>",
						iconCls: 'delete16'
					}]
				),
				viewConfig: {
					forceFit: true,
					getRowClass: function(record, index) {
						var c = record.get('IsMainRec');
						if (c == 1) {
							return 'mainrec';
						} else {
							return '';
						}
					}
				}
			}),
			new sw.Promed.Panel({
				region: 'south',
				id: 'PUW_BottomPanel',
				layout: "form",
				bodyBorder: false,
				bodyStyle: 'padding: 1px 1px',
				border: true,
				frame: false,
				height:28,
				html: '<table><tr><td valign="middle"><img src="/img/info.png" alt="" /> </td><td valign="middle">&nbsp;&nbsp;&nbsp;&nbsp;Выберите дополнительные записи для объединения и нажмите кнопку <b>Объединение</b>.</td></tr></table>'
			})]
		});
		sw.Promed.swPersonUnionWindow.superclass.initComponent.apply(this, arguments);

		Ext.getCmp('PUW_RecordGrid').getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			Ext.getCmp('PUW_SelectMainButton').setDisabled(win.HasMedPersonal && !rec.get('IsMedPersonal'));
		});
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('swPersonUnionWindow');
			current_window.hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: false,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,

	refreshMainRecord: function() {
		var unionGrid = Ext.getCmp('PUW_RecordGrid');
		var recStore = unionGrid.getStore();

		var mainRec = recStore.getAt(recStore.find('IsMainRec', 1));
		var firstMPRec = recStore.getAt(recStore.find('IsMedPersonal', 1));

		if (firstMPRec && (!mainRec || !mainRec.get('IsMedPersonal'))) {
			mainRec.set('IsMainRec', 0);
			firstMPRec.set('IsMainRec', 1);
		}
	},

	show: function() {
		sw.Promed.swPersonUnionWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		var win = this;
		if (arguments[0])
		{
			if (arguments[0].clearGrid)
			{
				this.findById('PUW_RecordGrid').store.removeAll();
			}
			
			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
			}

			if (arguments[0].onHide)
			{
				this.onHide = arguments[0].onHide;
			}

			if (arguments[0].Person_id)
			{
				this.personId = arguments[0].Person_id;
			}

			if (arguments[0].Server_id)
			{
				this.serverId = arguments[0].Server_id;
			}

			if (arguments[0].successFn)
			{
				this.successFn = arguments[0].successFn;
			}
			
			if (typeof arguments[0].selRec == 'object')
			{
				var Mask = new Ext.LoadMask(Ext.get('swPersonUnionWindow'), {msg:"Пожалуйста, подождите..."});
				Mask.show();
				var selRec = arguments[0].selRec;

				var unionGrid = Ext.getCmp('PUW_RecordGrid');
				var recStore = unionGrid.getStore();
				var IsMainRec = 0;
				if (recStore.getCount() == 0) {
					IsMainRec = 1;
				}

				var index = recStore.findBy(function(rec) {
					return (rec.get('Person_id') == selRec.data['Person_id']);
				});

				if ( index >= 0 ) {
					Mask.hide();
					sw.swMsg.alert('Ошибка', 'Человек уже добавлен на объединение');
					return false;
				}
				// Добавляем эти данные в грид для объединения в окне Объединения
				recStore.loadData(
				[{
					Person_id: selRec.data['Person_id'],
					Person_Surname: selRec.data['PersonSurName_SurName'],
					Person_Firname: selRec.data['PersonFirName_FirName'],
					Person_Secname: selRec.data['PersonSecName_SecName'],
					Person_Birthdate: selRec.data['PersonBirthDay_BirthDay'],
					Server_id: selRec.data['Server_id'],
					IsMainRec: IsMainRec
				}], true);

				if (!arguments[0].clearGrid) {
					var grid = Ext.getCmp('PUW_RecordGrid');
					var persArr = [];
					grid.getStore().each(
						function (r) {

							persArr.push(r.get('Person_id'));
						}
					)

					Ext.Ajax.request({
						url: '/?c=Person&m=CheckSpecifics',
						success: function(result){
							Mask.hide();
							if ( result.responseText.length > 0 ) {
								var resp_obj = Ext.util.JSON.decode(result.responseText);
								if (resp_obj.success == true) {
									
								}else{
									win.hide();
								}
							}
						
							
						},
						params: {
							'Records': Ext.util.JSON.encode(persArr)
						},
						failure: function(result){
						//	/*Mask.hide();
							//sw.swMsg.alert('Внимание','У нескольких пациентов есть специфика новорожденного. Объединение невозможно!');
							win.hide();
							
						}
					});
				} else {
					win.HasMedPersonal = false;
					Mask.hide();
				}

				Ext.Ajax.request({
					url: '/?c=Person&m=getInfoForDouble',
					params: {Person_id: selRec.data.Person_id},
					success: function(response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						var record = recStore.getById(response_obj.Person_id);
						record.set('IsMedPersonal', response_obj.IsMedPersonal);
						record.commit();

						if (response_obj.IsMedPersonal) {
							win.HasMedPersonal = true;
						}

						win.refreshMainRecord();
					}
				});
			}
		}

		this.restore();
		this.center();
	},
	title: lang['obyedinenie_lyudey'],
	width: 600
});
