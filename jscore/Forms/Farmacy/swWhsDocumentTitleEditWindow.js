/**
* swWhsDocumentTitleEditWindow - окно редактирования "Правоустанавливающие документы"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      07.08.2012
* @comment      
*/
sw.Promed.swWhsDocumentTitleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['pravoustanavlivayuschie_dokumentyi'],
	layout: 'border',
	id: 'WhsDocumentTitleEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	is_executed: false,
	onHide: Ext.emptyFn,
	setDisabled: function(mode) { // disable - полная блокировка, enable - полная разблокировка, executed - режим блокировки для исполненных документов
		if (!mode)
			mode = 'disable';
		
		var disable = (mode == 'disable');
		var enable = (mode == 'enable');
		var executed = (mode == 'executed');
					
		var wnd = this;
		var form = wnd.form;
		
		if (disable || executed) {
			wnd.dateMenu.disable();
			form.findField('WhsDocumentTitleType_id').disable();
			form.findField('WhsDocumentTitle_Name').disable();
			form.findField('WhsDocumentUc_id').disable();
		} else {
			wnd.dateMenu.enable();
			form.findField('WhsDocumentTitleType_id').enable();
			form.findField('WhsDocumentTitle_Name').enable();
			form.findField('WhsDocumentUc_id').enable();
		}
		
		if (disable) {
			wnd.FileUploadPanel.disable();
			wnd.buttons[0].disable();
		} else {
			wnd.FileUploadPanel.enable();
			wnd.buttons[0].enable();
		}
		
		if (disable || executed) {
			wnd.buttons[1].disable();			
		} else {
			wnd.buttons[1].enable();
		}
		
		wnd.RecipientGrid.setReadOnly(disable || executed);
	},
	setDocumentType: function(type_code) {
		var wnd = this;
		var combo = wnd.form.findField('WhsDocumentUc_id');
		var status = wnd.statusType;
		if (type_code == 3) {
			combo.ownerCt.show();
			status.show();
			combo.allowBlank = false;
		} else {
			combo.ownerCt.hide();
			status.hide();
			combo.setValue(null);
			status.setText(null);
			combo.allowBlank = true;
		}
	},
	fillEmptyDates: function() {
		var wnd = this;
		var begDate = wnd.dateMenu.getValue1();
		var endDate = wnd.dateMenu.getValue2();		
		wnd.RecipientGrid.getGrid().getStore().each(function(r) {
			if (r.get('Org_id') > 0) {
				if (!r.get('WhsDocumentRightRecipient_begDate') || r.get('WhsDocumentRightRecipient_begDate') == '')
					r.set('WhsDocumentRightRecipient_begDate', begDate);
				if (!r.get('WhsDocumentRightRecipient_endDate') || r.get('WhsDocumentRightRecipient_endDate') == '')
					r.set('WhsDocumentRightRecipient_endDate', endDate);
			}
		});
	},
	checkRightRecipientList: function() { //проверка списка правополучателей
		var res = false;
		this.RecipientGrid.getGrid().getStore().each(function(r){
			if (!res && r.get('Org_id') > 0) {
				res = true;
			}
		});
		return res;
	},
	doExecute: function() {
		if (this.checkRightRecipientList()) {
			this.doSave({
				execute: true
			});
		} else {
			Ext.Msg.alert(lang['oshibka'], lang['spisok_kontragentov_pust_ispolnenie_dokumenta_ne_vozmojno']);
		}
	},
	doSave:  function(options) {
		var wnd = this;
		if (wnd.is_executed) { //если документ исполнен только сохраняем файлы
			if (wnd.WhsDocumentTitle_id && wnd.WhsDocumentTitle_id > 0) {
				wnd.FileUploadPanel.listParams = {
					ObjectName: 'WhsDocumentTitle',
					ObjectID: wnd.WhsDocumentTitle_id
				};
				wnd.FileUploadPanel.saveChanges();
				wnd.callback(wnd.owner, wnd.WhsDocumentTitle_id);
			}
		} else {
			if ( !wnd.form.isValid() )
			{
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function() 
					{
						wnd.findById('WhsDocumentTitleEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			wnd.fillEmptyDates();
			wnd.submit(options);
		}
		return true;		
	},
	submit: function(options) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.WhsDocumentStatusType_id = wnd.form.findField('WhsDocumentStatusType_id').getValue();
		params.WhsDocumentTitle_begDate = Ext.util.Format.date(wnd.dateMenu.getValue1(), 'd.m.Y');
		params.WhsDocumentTitle_endDate = Ext.util.Format.date(wnd.dateMenu.getValue2(), 'd.m.Y');
		params.RightRecipientJSON = wnd.RecipientGrid.getJSONChangedData();
		params.action = wnd.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				var id = action.result.WhsDocumentTitle_id;
				wnd.FileUploadPanel.listParams = {
					ObjectName: 'WhsDocumentTitle',
					ObjectID: id					
				};
				wnd.FileUploadPanel.saveChanges();				
				loadMask.hide();
				if (options && options.execute) {
					wnd.execute(action.result.WhsDocumentTitle_id);
				} else {//при исполнении калбэк вызывается в функции execute, если исполнения нет, вызываем калбэк прямо тут
					wnd.callback(wnd.owner, action.result.WhsDocumentTitle_id);
				}
				wnd.hide();
			}
		});
	},
	execute: function(id) {
		var wnd = this;
		Ext.Ajax.request({
			params:{
				WhsDocumentTitle_id: id
			},
			success: function (response) {
				wnd.callback(wnd.owner, id);
			},
			failure:function () {
				sw.swMsg.alert(lang['oshibka'], lang['pri_ispolnenii_dokumenta_proizoshla_oshibka']);
			},
			url:'/?c=WhsDocumentTitle&m=execute'
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentTitleEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.WhsDocumentTitle_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentTitle_id ) {
			this.WhsDocumentTitle_id = arguments[0].WhsDocumentTitle_id;
		}

		this.form.reset();
		this.is_executed = false;

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		wnd.RecipientGrid.removeAll();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				wnd.FileUploadPanel.reset();
				wnd.setDisabled('enable');
				wnd.contract_combo.getStore().load();
				wnd.setDocumentType(null);
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						WhsDocumentTitle_id: wnd.WhsDocumentTitle_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						log(['dsdf',result])
						wnd.form.setValues(result[0]);
						if (result[0].WhsDocumentTitle_begDate && result[0].WhsDocumentTitle_endDate) {
							wnd.dateMenu.setValue(result[0].WhsDocumentTitle_begDate + ' - ' + result[0].WhsDocumentTitle_endDate);								
						}
						if (result[0].WhsDocumentStatusType_id == 2) {
							wnd.is_executed = true;
						}
						var type_code = null;
						if (result[0].WhsDocumentTitleType_id && result[0].WhsDocumentTitleType_id> 0) {
							var idx = wnd.form.findField('WhsDocumentTitleType_id').getStore().findBy(function(rec) { return rec.get('WhsDocumentTitleType_id') == result[0].WhsDocumentTitleType_id; });
							if (idx >= 0) {
								type_code = wnd.form.findField('WhsDocumentTitleType_id').getStore().getAt(idx).get('WhsDocumentTitleType_Code');
							}
						}
						
						wnd.setDocumentType(type_code);
						wnd.RecipientGrid.loadData({
							globalFilters: {
								WhsDocumentTitle_id: wnd.WhsDocumentTitle_id
							},
							options: {
								addEmptyRecord: false
							}
						});
						
						var disable_mode = 'disable';
						if (wnd.action == 'edit') {							
							disable_mode = wnd.is_executed ? 'executed' : 'enable';
						}
						
						//загружаем файлы
						wnd.FileUploadPanel.reset();
						wnd.FileUploadPanel.listParams = {
							ObjectName: 'WhsDocumentTitle',
							ObjectID: wnd.WhsDocumentTitle_id,
							callback: function() {
								wnd.setDisabled(disable_mode);
							}
						};
						wnd.FileUploadPanel.loadData();
						
						wnd.contract_combo.getStore().load({
							params: {
								WhsDocumentUc_id: result[0].WhsDocumentUc_id
							},
							callback: function() {
								wnd.contract_combo.setValue(result[0].WhsDocumentUc_id)
							}
						});
					
						loadMask.hide();
					},
					url:'/?c=WhsDocumentTitle&m=load'
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;
	
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 97,
			labelWidth: 150,
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile'
		});
	
		this.dateMenu = new Ext.form.DateRangeField({
			width: 175,
			fieldLabel: lang['period_deystviya'],
			hiddenName: 'WhsDocumentTitle_DateRange',
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.RecipientGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', text: lang['redaktirovat_spisok'], handler: function() {
					var OrgType_id = null;
					var endDate = null

					if (wnd.form.findField('WhsDocumentTitleType_id').getValue() == 1) {
						OrgType_id = 4;
					}
					if (!Ext.isEmpty(wnd.dateMenu.getValue2())) {
						endDate = wnd.dateMenu.getValue2().format('d.m.Y');
					}

					wnd.RecipientGrid.params.OrgType_id = OrgType_id;
					wnd.RecipientGrid.params.endDate = endDate;

					wnd.RecipientGrid.editRecord('add');
				}},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete'},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print'},
				{name: 'action_save', disable: true, hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=WhsDocumentRightRecipient&m=loadList',
			saveAtOnce: false,
			height: 420,			
			object: 'WhsDocumentRightRecipient',
			editformclassname: 'swWhsDocumentRightRecipientListEditWindow',
			id: 'wdteWhsDocumentRightRecipientGrid',
			paging: false,
			style: 'margin-top: 8px',
			params: {
				OrgType_id: null,
				onSelect: function(selection) {
					var view_frame = wnd.RecipientGrid;
					var store = view_frame.getGrid().getStore();
					var data_arr = new Array();
					selection.each(function(r) {
						var data = new Object();
						Ext.apply(data, r.data);
						data_arr.push(data);
					});
					store.loadData(data_arr);
					this.hide();
					wnd.fillEmptyDates();
					view_frame.setFilters();
				}
			},
			stringfields: [
				{name: 'WhsDocumentRightRecipient_id', type: 'int', header: 'ID', key: true},
				{name: 'state', type: 'string', header: 'state', width: 120, hidden: true},
				{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 350},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'PAddress_Address', header: lang['adres'], hidden: true},
				{name: 'Contragent_Code', type: 'string', header: lang['kontragent'], width: 120},
				{name: 'Contragent_id', type: 'int', hidden: true},
				{name: 'WhsDocumentRightRecipient_begDate', type: 'date', editor: new Ext.form.DateField(), header: lang['data_nachala_deystviya'], width: 175},
				{name: 'WhsDocumentRightRecipient_endDate', type: 'date', editor: new Ext.form.DateField(), header: lang['data_okonchaniya_deystviya'], width: 175}
			],
			title: lang['kontragentyi'],
			toolbar: true,
			editing: true,
			onAfterEdit: function() {
				if (arguments[0] && arguments[0].record && arguments[0].record.get('state') == 'saved')
					arguments[0].record.set('state', 'edit');
			},
			onLoadData:  function() {
				if (!this.isEmpty())
					this.getGrid().getStore().each(function(r){
						if (r.get('state') == '')
							r.set('state', 'saved');
					});
			},
			onRowSelect: function(sm,rowIdx,record) {				
				if (!this.readOnly) {
					this.ViewActions.action_add.setDisabled(false);
					if (record.get('Org_id') > 0)
						this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_add.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				sw.swMsg.show( {
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vyi_hotite_udalit_zapis'],
					title: lang['podtverjdenie'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {							
							var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
							if (selected_record.get('state') == 'add') {
								view_frame.getGrid().getStore().remove(selected_record);
							} else {								
								selected_record.set('state', 'delete');
								selected_record.commit();
								view_frame.setFilters();
							}
						}
					}
				});
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.getGrid().getStore().clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete'))
						data.push(record.data);
				});
				this.setFilters();
				return data;
			},						
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			setFilters: function() {
				this.getGrid().getStore().filterBy(function(record){
					if(record.data.state == 'delete') return false;
					return true;
				});
			}
		});

		this.contract_combo = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=WhsDocumentTitle&m=loadWhsDocumentSupplyList',
				key: 'WhsDocumentUc_id',
				autoLoad: false,
				fields: [
					{name: 'WhsDocumentUc_id', type:'int'},
					{name: 'WhsDocumentUc_Name', type:'string'},
					{name: 'WhsDocumentUc_Num', type:'string'},
					{name: 'WhsDocumentStatusType_Name', type:'string'}
				],
				sortInfo: {
					field: 'WhsDocumentUc_Num'
				}
			}),
			displayField:'WhsDocumentUc_Num',
			valueField: 'WhsDocumentUc_id',
			hiddenName: 'WhsDocumentUc_id',
			fieldLabel: lang['№_kontrakta'],
			width: 500,
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{WhsDocumentUc_Num}'+
				'</div></tpl>',
			listeners: {
				select: function(combo, record, id) {
					wnd.form.findField('WhsDocumentTitle_Name').setValue("Приложение к контракту № " + record.get('WhsDocumentUc_Num') + ". Список пунктов отпуска лекарственных средств");
					wnd.statusType.setText(record.get('WhsDocumentStatusType_Name'));
			},
				'callback': function(combo, record, id) {
					alert("dsfsd")
				}
			}
		});
		
		this.statusType = new Ext.form.Label({
					fieldLabel: lang['status_gk'],
					name: 'statusType',
					text: '',
					width:200
					
				});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentTitleEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'padding-top:5px;',
				border: true,
				labelWidth: 180,
				labelAlign: 'right',
				collapsible: true,
				url:'/?c=WhsDocumentTitle&m=save',
				items: [{
					name: 'WhsDocumentTitle_id',
					xtype: 'hidden',
					value: 0
				},
					{
					name: 'WhsDocumentStatusType_Name',
					xtype: 'hidden',
					value: 0
				},{
					name: 'WhsDocument_id',
					xtype: 'hidden',
					value: 0
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [this.dateMenu]
					}, {
						layout: 'form',
						items: [{
								fieldLabel: lang['status_dokumenta'],
								hiddenName: 'WhsDocumentStatusType_id',
								xtype: 'swcommonsprcombo',								
								allowBlank:true,
								disabled:true,
								sortField:'WhsDocumentStatusType_Code',
								comboSubject: 'WhsDocumentStatusType',
								value: 1,
								width: 140
							}]
					}]
				}, {
					fieldLabel: lang['tip'],
					hiddenName: 'WhsDocumentTitleType_id',
					xtype: 'swcommonsprcombo',								
					allowBlank:false,
					sortField:'WhsDocumentTitleType_Code',
					comboSubject: 'WhsDocumentTitleType',
					width: 500,
					listeners: {
						select: function(combo, record, id) {
							wnd.setDocumentType(record.get('WhsDocumentTitleType_Code'));
						}
					}
				}, {
					layout: 'column',
					items: [
						{
							layout: 'form',
							items:[this.contract_combo]
						},
						{
							layout: 'form',
							style:'margin-left:20px;',
							items:[this.statusType]
						}]
				},{
					fieldLabel: lang['naimenovanie_dokumenta'],
					name: 'WhsDocumentTitle_Name',
					allowBlank:false,
					xtype: 'textfield',
					width: 500
				}]
			},
			this.RecipientGrid,
			{
				xtype: 'panel',
				autoHeight: true,
				title: lang['faylyi'],
				border: true,
				frame: true,
				style: 'padding-top: 3px; margin-top: 5px; display:block;',
				collapsible: true,
				items:
				[this.FileUploadPanel]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'WhsDocument_id'}, 
				{name: 'WhsDocumentTitle_id'}, 
				{name: 'WhsDocumentTitle_Name'}, 
				{name: 'WhsDocumentTitleType_id'}, 
				{name: 'WhsDocumentStatusType_id'}, 
				{name: 'WhsDocumentTitle_begDate'}, 
				{name: 'WhsDocumentTitle_endDate'}
			]),
			url: '/?c=WhsDocumentTitle&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.ownerCt.doExecute();
				},
				iconCls: 'ok16',
				text: lang['ispolnit']
			},  {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swWhsDocumentTitleEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('WhsDocumentTitleEditForm').getForm();
	}	
});