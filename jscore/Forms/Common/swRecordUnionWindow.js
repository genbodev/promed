/**
* swRecordUnionWindow - окно объединения записей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.07.2009
*/
/*NO PARSE JSON*/
sw.Promed.swRecordUnionWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'RecordUnionWindow',
	RecordType_Code: null,
	RecordType_Name: null,
	successFn: null, // функция вызывающаяся при удачном объединении
	// Функция объединения записей
	doRecordUnion: function () {
		grid = Ext.getCmp('RUW_RecordGrid');
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
		if (this.RecordType_Code == 'Org'){ 
			if(grid.getStore().getCount()>2){
				sw.swMsg.alert(lang['vnimanie'],lang['v_ramkah_odnoy_procedury_mozno_obedinyat_ne_bolee_2_organizaciy']);
				return false;
			} else {
				var data = grid.getStore().data.items;
				if(data[0].data.IsExternalRec == 'true' && data[1].data.IsExternalRec == 'true'){
					sw.swMsg.alert(lang['vnimanie'],'Запрещено объединять две записи из внешнего источника.');
					return false;
				} else if ((data[0].data.IsExternalRec == 'true' && data[0].data.IsMainRec != 1) || (data[1].data.IsExternalRec == 'true' && data[1].data.IsMainRec != 1)) {
					sw.swMsg.alert(lang['vnimanie'],'Объединение запрещено. Запись из внешнего источника должна быть главной.');
					return false;
				} else if (data[0].data.OrgType_id == 11 || data[1].data.OrgType_id == 11) {
					if (getRegionNick() == 'kareliya') {
						if (data[0].data.HasLpu && data[1].data.HasLpu) {
							sw.swMsg.alert(langs('Внимание'),'Объединение запрещено. Записи не являются дублирующими.');
							return false;
						} else if (
							(data[0].data.HasLpu && !data[0].data.IsMainRec) ||
							(data[1].data.HasLpu && !data[1].data.IsMainRec)
						) {
							sw.swMsg.alert(langs('Внимание'),'Неправильно определена главная запись. Запись, которая внесена в Систему с верным наименованием, должна быть главной.');
							return false;
						}
					} else {
						sw.swMsg.alert(langs('Внимание'), 'Запрещено объединять медицинские организации.');
						return false;
					}
				}
			}
		}
		if (!hasMainRec){
			sw.swMsg.alert(lang['vnimanie'],lang['doljna_byit_vyibrana_glavnaya_zapis_dlya_obyedineniya']);
			return false;
		}

		var win =  this;
		this.checkRecordsForUnion({callback: function() {
		var Mask = new Ext.LoadMask(Ext.get('RecordUnionWindow'), {msg:"Пожалуйста, подождите, идет сохранение данных..."});
		Mask.show();
				if (win.RecordType_Code.inlist(['LpuSection'])) {
			Mask.hide();
					//win.hide();
			getWnd('swRecordUnionSettingsWindow').show({
						RecordType_Code: win.RecordType_Code,
						RecordType_Name: win.RecordType_Name,
				Records: getStoreRecords(grid.getStore()),
						//returnFunc: win.returnFunc,
						successFn: win.successFn
			});
		} else {
			controlStoreRequest = Ext.Ajax.request({
				url: C_RECORD_UNION,
				success: function(result){
					Mask.hide();
					sw.swMsg.alert(lang['vnimanie'],lang['zapisi_postavleny_v_ochered_na_objedinenie']);
					if (handleResponseError(result))
						Ext.getCmp('RecordUnionWindow').hide();
					if (Ext.getCmp('RecordUnionWindow').successFn!==null) {
								Ext.getCmp('RecordUnionWindow').successFn.call(win);
					}
				},
				params: {
							'Table': win.RecordType_Code,
					'Records': Ext.util.JSON.encode(getStoreRecords(grid.getStore()))
				},
				failure: function(result){
					Mask.hide();
				},
				method: 'POST',
				timeout: 120000
			});
		}
			}});
	},
	initComponent: function() {

		Ext.apply(this, {
			buttons: [ {
				handler: function() {
					this.ownerCt.doRecordUnion();
				},
				iconCls: 'copy16',
				text: BTN_FRMUNION
			},
			{
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
			new sw.Promed.Panel({
				region: 'north',
				id: 'RUW_InfoPanel',
				layout: 'border',
				bodyBorder: false,
				bodyStyle: 'padding: 0 ',
				border: false,
				frame: false,
				height:30,
				items: [ new Ext.DataView({
					border: false,
					frame: false,
					itemSelector: 'div',
					region: 'center',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'RecordType_Name' }
						]
					}),
					style: 'padding: 0.2em;',
					tpl: new Ext.XTemplate(
						'<tpl for=".">',
						'<div style="font-size: 18px;">Записи для объединения : <b>{RecordType_Name}</b></div>',
						'</tpl>'
					)
				})]
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'Record_Name',
				bodyBorder: false,
				border: false,
				// autoHeight: true,
				// height: 100,
				id: 'RUW_RecordGrid',
				columns: [{
					dataIndex: 'Record_Name',
					header: lang['naimenovanie'],
					hidden: false,
					id: 'Record_Name',
					sortable: true
				}, {
					dataIndex: 'Record_Code',
					header: lang['kod'],
					hidden: false,
					id: 'Record_Code',
					sortable: true
				}],
				region: 'center',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'Record_id'
					}, [{
						mapping: 'Record_id',
						name: 'Record_id',
						type: 'int'
					}, {
						mapping: 'Record_Name',
						name: 'Record_Name',
						type: 'string'
					}, {
						mapping: 'Record_Code',
						name: 'Record_Code',
						type: 'string'
					}, {
						mapping: 'IsMainRec',
						name: 'IsMainRec',
						type: 'int'
					}, {
						mapping: 'IsExternalRec',
						name: 'IsExternalRec',
						type: 'string'
					}, {
						mapping: 'HasLpu',
						name: 'HasLpu',
						type: 'int'
					}, {
						mapping: 'OrgType_id',
						name: 'OrgType_id',
						type: 'string'
					}])
				}),
				stripeRows: true,
				tbar : new Ext.Toolbar(
					[{
						handler : function(button, event) {
							grid = Ext.getCmp('RUW_RecordGrid');
							grid.getStore().each(function(record) {
								record.set('IsMainRec', 0);
								record.commit();
							});
							grid.getSelectionModel().getSelected().set('IsMainRec', 1);
							grid.getSelectionModel().getSelected().commit();
						}.createDelegate(this),
						text : "Главная запись",
						tooltip : "Сделать главной записью",
						iconCls: 'actions16'
					}, {
						xtype : "tbseparator"
					}, {
						handler : function(button, event) {
							grid = Ext.getCmp('RUW_RecordGrid');
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
				id: 'RUW_BottomPanel',
				layout: "form",
				bodyBorder: false,
				bodyStyle: 'padding: 1px 1px',
				border: true,
				frame: false,
				height:28,
				html: '<table><tr><td valign="middle"><img src="/img/info.png" alt="" /> </td><td valign="middle">&nbsp;&nbsp;&nbsp;&nbsp;Выберите дополнительные записи для объединения и нажмите кнопку <b>Объединение</b>.</td></tr></table>'
			})]
		});
		sw.Promed.swRecordUnionWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('RecordUnionWindow');
			current_window.hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: false,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	prepareRecordForUnion: function(record) {
		var win = this;
		var unionGrid = Ext.getCmp('RUW_RecordGrid');
		var IsMainRec = (unionGrid.getStore().getCount() == 0)?1:0;
		var preparedRecord = {};

		if ( win.RecordType_Code == 'MedStaffFact' ) {
			preparedRecord ={
				Record_id: record.data['MedStaffFact_id'],
				Record_Name: record.data['MedPersonal_FIO'],
				Record_Code: record.data['MedPersonal_TabCode'],
				IsMainRec: IsMainRec
			};
		} else if ( win.RecordType_Code == 'MedPersonal' ) {
			preparedRecord = {
				Record_id: record.data['MedPersonal_id'],
				Record_Name: record.data['Person_SurName'] + ' ' + record.data['Person_FirName'] + ' ' + record.data['Person_SecName'],
				Record_Code: record.data['MedPersonal_Code'],
				IsMainRec: IsMainRec
			};
		} else if ( win.RecordType_Code == 'Org' ) {
			preparedRecord = {
				Record_id: record.data[win.RecordType_Code+ '_id'],
				Record_Name: record.data[win.RecordType_Code+ '_Name'],
				Record_Code: record.data[win.RecordType_Code+ '_Code'],
				IsMainRec: IsMainRec,
				IsExternalRec: record.data['Org_External'],
				HasLpu: Ext.isEmpty(record.data['Lpu_id'])?0:1,
				OrgType_id: record.data['OrgType_id']
			};
		} else {
			preparedRecord = {
				Record_id: record.data[win.RecordType_Code+ '_id'],
				Record_Name: record.data[win.RecordType_Code+ '_Name'],
				Record_Code: record.data[win.RecordType_Code+ '_Code'],
				IsMainRec: IsMainRec
			};
		}

		return preparedRecord;
	},
	checkRecordsForUnion: function(options) {
		options = Ext.applyIf(options || {}, {callback: Ext.emptyFn});

		var unionGrid = Ext.getCmp('RUW_RecordGrid');
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет проверка." });

		var records = getStoreRecords(unionGrid.getStore());
		if (options.additionRecord) {
			records.push(options.additionRecord);
		}

		Ext.Ajax.request({
			url: '/?c=Utils&m=checkRecordsForUnion',
			params: {
				Table: this.RecordType_Code,
				Records: Ext.util.JSON.encode(records)
			},
			success: function(response){
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (!Ext.isEmpty(response_obj.Error_Msg)) {

				} else {
					options.callback();
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
	addRecordForUnion: function(preparedRecord) {
		Ext.getCmp('RUW_RecordGrid').getStore().loadData([preparedRecord], true );
	},
	show: function() {
		sw.Promed.swRecordUnionWindow.superclass.show.apply(this, arguments);

		var win = this;
		var unionGrid = Ext.getCmp('RUW_RecordGrid');
		
		if (arguments[0])
		{
			if (arguments[0].clearGrid)
			{
				this.findById('RUW_RecordGrid').store.removeAll();
				this.RecordType_Code = null;
				this.RecordType_Name = null;
			}
			
			if (arguments[0].RecordType_Code) {
				if (this.RecordType_Code != null && arguments[0].RecordType_Code != this.RecordType_Code) {
					sw.swMsg.alert(lang['vnimanie'],lang['vyi_uje_nachali_vyibirat_zapisi_drugogo_tipa']+this.RecordType_Name+ '</b>!');
					return false;
				}
				
				this.RecordType_Code = arguments[0].RecordType_Code;
			}
			
			if (arguments[0].RecordType_Name) {
				this.RecordType_Name = arguments[0].RecordType_Name;
				
				var unionInfo = Ext.getCmp('RUW_InfoPanel');
				unionInfo.items.items[0].getStore().removeAll();
				unionInfo.items.items[0].getStore().loadData([{
					RecordType_Name: this.RecordType_Name
				}]);
			}
			
			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
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
			
			if (arguments[0].selRec)
			{
				var preparedRecord = win.prepareRecordForUnion(arguments[0].selRec);

				if (preparedRecord.Record_id){
					if ( win.RecordType_Code == 'LpuSection' && unionGrid.getStore().getCount() == 2 ) {
						sw.swMsg.alert(lang['vnimanie'],lang['mojno_obyedinit_tolko_dva_otdeleniya_za_odnu_operatsiyu']);
					} else if( win.RecordType_Code.inlist(['LpuSection']) && unionGrid.getStore().getCount() >= 1 ) {
						//По задаче http://redmine.swan.perm.ru/issues/127811 проверка вынесена отсюда в кнопку "Объединение"
					//	win.checkRecordsForUnion({
					//		additionRecord: preparedRecord,
					//		callback: function() {
								win.addRecordForUnion(preparedRecord);
					//		}
					//	});
					} else {
						win.addRecordForUnion(preparedRecord);
					}
				}
			}
		}
		
		this.restore();
		this.center();
		return true;
	},
	title: lang['obyedinenie_zapisey'],
	width: 600
});
