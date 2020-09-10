/**
* swNewslatterEditWindow - Редактирование рассылок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Aleksandr Chebukin
* @version      22.12.2015
*/

sw.Promed.swNewslatterEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Рассылка',
	id: 'swNewslatterEditWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 500,
	autoHeight: true,
	minWidth: 500,
	minHeight: 420,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lbOk',
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},{
		text:'-'
	},{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},{
		text: BTN_FRMCANCEL,
		id: 'lbCancel',
		iconCls: 'cancel16',
		handler: function() {
			this.ownerCt.hide();
		}
	}],
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swNewslatterEditWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swNewslatterEditWindow'), { msg: "Загрузка..." });
		loadMask.show();
		this.mode = 'on'; //форма может открываться для конкретного пациента (on), либо для множественного добавления рассылок (all)
		
		if (arguments[0].Newslatter_id)
			this.Newslatter_id = arguments[0].Newslatter_id;
		else
			this.Newslatter_id = null;
		
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		else
			this.returnFunc = function(owner) {};
			
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		else
			this.owner = null;
			
		if (arguments[0].action)
			this.action = arguments[0].action;
		else
			this.action = 'add';
			
		if (arguments[0].formParams)
			this.formParams = arguments[0].formParams;
		else
			this.formParams = null;

		this.previousForm = (arguments[0].currentForm) ? arguments[0].currentForm : null;
		if(this.previousForm == 'swNewslatterListWindow'){
			this.mode = 'all';
		}
		this.NewslatterGroupType_id = (arguments[0].NewslatterGroupType_id) ? arguments[0].NewslatterGroupType_id : null;

		var form = this;
		var grid = form.PersonNewslatterGrid.getGrid();
		base_form = form.MainPanel.getForm();
		base_form.reset();
		form.PersonNewslatterGrid.removeAll();		
		this.disableFields(this.action == 'view');
		
		if (this.action == 'add') {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Newslatter_insDT').setValue(getGlobalOptions().date);
			base_form.findField('Newslatter_IsActive').setValue(true);
			if (this.formParams) {			
				base_form.findField('Newslatter_IsSMS').setValue(this.formParams.NewslatterAccept_IsSMS == 2);
				base_form.findField('Newslatter_IsEmail').setValue(this.formParams.NewslatterAccept_IsEmail == 2);
				if (this.formParams.Person_id && this.formParams.NewslatterAccept_id) {				
					Ext.Ajax.request({
						url: '/?c=Common&m=loadPersonData',
						params: {Person_id: this.formParams.Person_id, mode: 'NewslatterEditWindow', LoadShort: 'true'},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj && response_obj.length > 0) {
								var record = response_obj[0];
								record.NewslatterAccept_id = form.formParams.NewslatterAccept_id;
								record.Person_Fio = record.Person_Surname + ' ' + record.Person_Firname + ' ' + record.Person_Secname;
								record.PersonNewslatter_id = -swGenTempId(grid.getStore());
								record.RecordStatus_Code = 0;
								grid.getStore().loadData([record]);
							}
						}
					});
				}
			}
			loadMask.hide();
			base_form.clearInvalid();

			base_form.findField('NewslatterGroupType_id').setValue(1);
			base_form.findField('NewslatterGroupType_id').fireEvent('select', base_form.findField('NewslatterGroupType_id'),base_form.findField('NewslatterGroupType_id').getValue(), null);
		} else {
			//form.PersonNewslatterGrid.loadData({ globalFilters: {Newslatter_id : form.Newslatter_id} });
			base_form.load({
				params:{
					Newslatter_id: form.Newslatter_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка запроса к серверу. Попробуйте повторить операцию.',
						title: 'Ошибка'
					});
				},
				success: function(result, request) {
					loadMask.hide();
					base_form.findField('NewslatterGroupType_id').fireEvent('select', base_form.findField('NewslatterGroupType_id'),base_form.findField('NewslatterGroupType_id').getValue(), null);
				},
				url: '/?c=Newslatter&m=load'
			});
		}		
		
	},
	doSave: function() 
	{
		var form = this.findById('swNewslatterEditWindowPanel');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	disableFields: function(action) {
		base_form = this.MainPanel.getForm();;
		base_form.findField('Newslatter_IsActive').setDisabled(action);
		base_form.findField('NewslatterType_id').setDisabled(action);
		base_form.findField('Newslatter_IsSMS').setDisabled(action);
		base_form.findField('Newslatter_IsEmail').setDisabled(action);
		base_form.findField('Newslatter_begDate').setDisabled(action);
		base_form.findField('Newslatter_endDate').setDisabled(action);
		base_form.findField('Newslatter_begTime').setDisabled(action);
		base_form.findField('Newslatter_Text').setDisabled(action);
		base_form.findField('NewslatterGroupType_id').setDisabled(action);
		this.buttons[0].setVisible(!action);
	},
	submit: function() 
	{
		var form = this.findById('swNewslatterEditWindowPanel');
		var NewslatterGroupType_id = base_form.findField('NewslatterGroupType_id').getValue();
		var loadMask = new Ext.LoadMask(Ext.get('swNewslatterEditWindow'), { msg: "Подождите, идет сохранение..." });
		// loadMask.show();
		var win = this;
		var params = {};

		var obj = {
			1: 'PersonNewslatter',
			2: 'LpuSectionNewslatter',
			3: 'LpuRegionNewslatter'
		}

		if(!obj[NewslatterGroupType_id]) return false;
		var name = obj[NewslatterGroupType_id];
		var grid = win[name+'Grid'].getGrid();
		if(grid.getStore().getCount() == 0){
			Ext.Msg.alert('Информация', 'Список для рассылки пуст!');
			return false;
		}
		grid.getStore().clearFilter();
		var data = getStoreRecords(grid.getStore());
		params[name+'Data'] = Ext.util.JSON.encode(data);

		// собираем данные грида. пока что там может быть только один человек, но в будудем возможно редактирование
		/*
		var grid = win.PersonNewslatterGrid.getGrid();
		grid.getStore().clearFilter();
		var PersonNewslatterData = getStoreRecords(grid.getStore());
		params.PersonNewslatterData = Ext.util.JSON.encode(PersonNewslatterData);
		*/
		grid.getStore().filterBy(function(rec) {
			return (Number(rec.get('RecordStatus_Code')) != 3);
		});

		loadMask.show();
		form.getForm().submit({
				params: params,
				failure: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.Error_Code) {
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						}
					}
					loadMask.hide();
				}, 
				success: function(result_form, action) {
					loadMask.hide();
					if (action.result) {
						if (action.result.Newslatter_id) {
							params.Newslatter_id = action.result.Newslatter_id;
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, true, params);
						} else {
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
						}
					} else {
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
					}
				}
			});
	},
	initComponent: function() 
	{
		var win = this;
		this.MainPanel = new sw.Promed.FormPanel({
			id:'swNewslatterEditWindowPanel',
			frame: true,
			region: 'center',
			labelWidth: 120,
			items:
			[{
				name: 'Newslatter_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				layout: 'column',
				border: false,
				autoHeight: true,
				labelWidth: 120,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 120,
					width: 240,
					items: [{
						fieldLabel: 'Дата',
						xtype: 'swdatefield',
						format: 'd.m.Y',
						name: 'Newslatter_insDT',
						disabled: true
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						boxLabel: 'Активная',
						name: 'Newslatter_IsActive',
						xtype: 'checkbox',
						hideLabel: true,
						inputValue: '2',
						uncheckedValue: '1'
					}]
				}]
			}, {
				hiddenName: 'NewslatterType_id',
				fieldLabel: 'Тип рассылки',
				codeField: 'NewslatterType_Code',
				displayField: 'NewslatterType_Name',
				valueField: 'NewslatterType_id',
				editable: false,
				store: new Ext.data.Store({
					autoLoad: true,
					baseParams: {Object: 'NewslatterType', NewslatterType_id: '', NewslatterType_Name: ''},
					reader: new Ext.data.JsonReader({
						id: 'NewslatterType_id'
					}, [
						{ name: 'NewslatterType_id', mapping: 'NewslatterType_id' },
						{ name: 'NewslatterType_Code', mapping: 'NewslatterType_Code' },
						{ name: 'NewslatterType_Name', mapping: 'NewslatterType_Name' }
					]),
					url: C_GETOBJECTLIST
				}),
				xtype: 'swbaselocalcombo',
				width: 219,
				allowBlank: false
			}, {
				layout: 'column',
				border: false,
				autoHeight: true,
				labelWidth: 120,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 120,
					width: 240,
					items: [{
						fieldLabel : 'Период рассылки',
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'Newslatter_begDate',
						allowBlank: false
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						fieldLabel : 'Период рассылки',
						hideLabel: true,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'Newslatter_endDate'
					}]
				}]
			}, {
				fieldLabel : 'Время рассылки',
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				xtype: 'swtimefield',
				name: 'Newslatter_begTime',
				allowBlank: false
            }, {
				boxLabel: 'СМС',
				name: 'Newslatter_IsSMS',
				xtype: 'checkbox',
				labelSeparator: '',
				inputValue: '2',
				uncheckedValue: '1'
			}, {
				boxLabel: 'E-mail',
				name: 'Newslatter_IsEmail',
				xtype: 'checkbox',
				labelSeparator: '',
				inputValue: '2',
				uncheckedValue: '1'
			}, {
				fieldLabel: 'Текст',
				anchor: '100%',
				name: 'Newslatter_Text',
				xtype: 'textarea',
				allowBlank: false
			}, {
				xtype: 'swnewslattergrouptypecombo',
				mode: 'local',
				value: 1,
				allowBlank: false,
				hiddenName: 'NewslatterGroupType_id',
				listeners: {
					select: function(combo, newValue) {
						win.showGrid();
					}
				}
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() {}
			},
			[
				{ name: 'Newslatter_id' },
				{ name: 'Lpu_id' },
				{ name: 'Newslatter_insDT' },
				{ name: 'Newslatter_IsActive' },
				{ name: 'NewslatterType_id' },
				{ name: 'Newslatter_IsSMS' },
				{ name: 'Newslatter_IsEmail' },
				{ name: 'Newslatter_begDate' },
				{ name: 'Newslatter_endDate' },
				{ name: 'Newslatter_begTime' },
				{ name: 'Newslatter_Text' },
				{ name: 'NewslatterGroupType_id' },
			]
			),
			url: '/?c=Newslatter&m=save'
		});

		this.PersonNewslatterGrid = new sw.Promed.ViewFrame({
			id: 'PersonNewslatterGrid',
			title: 'Список пациентов для рассылки',
			object: 'PersonNewslatter',
			dataUrl: '/?c=Newslatter&m=loadPersonNewslatterList',
			autoLoadData: false,
			toolbar: true,
			region: 'south',
			useEmptyRecord: false,
			selectionModel: 'multiselect',
			hidden: false,
			stringfields: [
				{name: 'PersonNewslatter_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NewslatterAccept_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'NewslatterAccept_IsSMS', type: 'int', hidden: true},
				{name: 'NewslatterAccept_IsEmail', type: 'int', hidden: true},
				{name: 'Person_Fio', header: 'ФИО', width: 300, id: 'autoexpand'},
				{name: 'Person_Birthday', header: 'Дата рождения', width: 150}
			],
			actions: [
				{
					name:'action_add',
					hidden: false,
					disabled: false,
					handler: function(){
						this.addPerson();
					}.createDelegate(this)
				},
				{name:'action_edit',  hidden: true},
				{name:'action_view',  hidden: true},
				{
					name:'action_delete', 
					hidden: false,
					handler: function(){
						win.deleteRecord('PersonNewslatter');
					}
				},
				{name:'action_save',  hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			]
		});

		this.LpuSectionNewslatterGrid = new sw.Promed.ViewFrame({
			id: 'LpuSectionNewslatterGrid',
			title: 'Список отделений для рассылки',
			object: 'LpuSectionNewslatter',
			dataUrl: '/?c=Newslatter&m=loadLpuSectionNewslatterList',
			autoLoadData: false,
			toolbar: true,
			region: 'south',
			useEmptyRecord: false,
			hidden: true,
			selectionModel: 'multiselect',
			stringfields: [
				{name: 'LpuSectionNewslatter_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Lpu_Name', header: 'МО', width: 200},
				{name: 'LpuSection_Name', header: 'Отделение', width: 300, id: 'autoexpand'}
			],
			actions: [
				{
					name:'action_add',
					hidden: false,
					disabled: false,
					handler: function(){
						var params = {
							mode: 'section',
							action: 'add',
							callback: function(data){
								if(data) win.addLpuSectionNewslatterGrid(data);
							}
						}
						getWnd('swSelectSectionOrRegion').show(params);
					}.createDelegate(this)
				},
				{name:'action_edit',  hidden: true},
				{name:'action_view',  hidden: true},
				{
					name:'action_delete', 
					hidden: false,
					handler: function(){
						win.deleteRecord('LpuSectionNewslatter');
					}
				},
				{name:'action_save',  hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			]
		});

		this.LpuRegionNewslatterGrid = new sw.Promed.ViewFrame({
			id: 'LpuRegionNewslatterGrid',
			title: 'Список участков для рассылки',
			object: 'LpuRegionNewslatter',
			dataUrl: '/?c=Newslatter&m=loadLpuRegionNewslatterList',
			autoLoadData: false,
			toolbar: true,
			region: 'south',
			useEmptyRecord: false,
			hidden: true,
			selectionModel: 'multiselect',
			layout: 'fit',
			stringfields: [
				{name: 'LpuRegionNewslatter_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'LpuRegion_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Lpu_Name', header: 'МО', width: 200},
				{name: 'LpuRegion_Name', header: 'Участок', width: 300, id: 'autoexpand'}
			],
			actions: [
				{
					name:'action_add',
					hidden: false,
					disabled: false,
					handler: function(){
						var params = {
							mode: 'region',
							action: 'add',
							callback: function(data){
								if(data) win.addLpuRegionNewslatterGrid(data);
							},
						}
						getWnd('swSelectSectionOrRegion').show(params);
					}.createDelegate(this)
				},
				{name:'action_edit',  hidden: true},
				{name:'action_view',  hidden: true},
				{
					name:'action_delete', 
					hidden: false,
					handler: function(){
						win.deleteRecord('LpuRegionNewslatter');
					}
				},
				{name:'action_save',  hidden: true},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			]
		});
		
		Ext.apply(this, 
		{
			items: [
				this.MainPanel,
				this.PersonNewslatterGrid,
				this.LpuSectionNewslatterGrid,
				this.LpuRegionNewslatterGrid
			]
		});
		sw.Promed.swNewslatterEditWindow.superclass.initComponent.apply(this, arguments);
	},
	showGrid: function(){
		var form = this;
		var grids = {
			1: 'PersonNewslatterGrid',
			2: 'LpuSectionNewslatterGrid',
			3: 'LpuRegionNewslatterGrid'
		}
		var base_form = form.MainPanel.getForm();

		if(this.mode == 'on'){
			base_form.findField('NewslatterGroupType_id').setValue(1);
			form.PersonNewslatterGrid.ViewActions.action_add.hide();
			form.PersonNewslatterGrid.ViewActions.action_delete.hide();
			base_form.findField('NewslatterGroupType_id').hideContainer();
		}else{
			form.PersonNewslatterGrid.ViewActions.action_add.show();
			form.PersonNewslatterGrid.ViewActions.action_delete.show();
			base_form.findField('NewslatterGroupType_id').showContainer();
		}
		var NewslatterGroupType_id = base_form.findField('NewslatterGroupType_id').getValue();
		var hide = (this.action == 'view') ? true : false;
		for (var key in grids){
			var obj = form[grids[key]];
			if(obj){
				obj.getGrid().getStore().removeAll();
				if(key == NewslatterGroupType_id){
					obj.show();
					obj.doLayout();
					if(form.Newslatter_id && form.NewslatterGroupType_id == NewslatterGroupType_id) obj.loadData({ globalFilters: {Newslatter_id : form.Newslatter_id} });
				}else{
					obj.hide();
				}
				if(hide){
					obj.ViewActions.action_add.hide();
					obj.ViewActions.action_delete.hide();
				}else{
					obj.ViewActions.action_add.show();
					obj.ViewActions.action_delete.show();
				}
			}
		}
	},
	deleteRecord: function(name){
		if(!name) return false;
		var form = this;
		var grid = null;
		if(form[name+'Grid']) grid = form[name+'Grid'].getGrid();
		if(!grid) return false;
		var selecteds = form[name+'Grid'].getMultiSelections();
		
		selecteds.forEach(function(item, i, arr){
			var key = grid.getStore().find(name+'_id', item.get(name+'_id'));
			if(key >=0 ) grid.getStore().removeAt(key);
		});
	},
	addLpuSectionNewslatterGrid: function(data){
		if(!data) return false;
		var form = this;
		var grid = form.LpuSectionNewslatterGrid.getGrid();
		var base_form = form.MainPanel.getForm();
		var lpu_rec = (data.lpu_data) ? data.lpu_data : null;
		var lpusection_rec = (data.lpusection_data) ? data.lpusection_data : null;
		if(lpu_rec && lpusection_rec){
			var record = {
				LpuSectionNewslatter_id: -swGenTempId(grid.getStore()),
				LpuSection_id: lpusection_rec.get('LpuSection_id'),
				Lpu_id: lpu_rec.get('Lpu_id'),
				Lpu_Name: lpu_rec.get('Lpu_Nick'),
				LpuSection_Name: lpusection_rec.get('LpuSection_Name'),
				RecordStatus_Code: 0
			}

			var key = grid.getStore().find('LpuSection_id', record.LpuSection_id);
			if(key < 0) grid.getStore().loadData([record], true);
		}
	},
	addLpuRegionNewslatterGrid: function(data){
		if(!data) return false;
		var form = this;
		var grid = form.LpuRegionNewslatterGrid.getGrid();
		var base_form = form.MainPanel.getForm();
		var lpu_rec = (data.lpu_data) ? data.lpu_data : null;
		var lpusection_rec = (data.lpusection_data) ? data.lpusection_data : null;
		var lpuregion_rec = (data.lpuregion_data) ? data.lpuregion_data : null;
		if(lpu_rec && lpuregion_rec){
			var record = {
				LpuRegionNewslatter_id: -swGenTempId(grid.getStore()),
				LpuSection_id: (lpusection_rec) ? lpusection_rec.get('LpuSection_id') : null,
				Lpu_id: lpu_rec.get('Lpu_id'),
				Lpu_Name: lpu_rec.get('Lpu_Nick'),
				LpuRegion_id: lpuregion_rec.get('LpuRegion_id'),
				LpuRegion_Name: lpuregion_rec.get('LpuRegion_Name') + ' ' + lpuregion_rec.get('LpuRegion_Descr'),
				RecordStatus_Code: 0
			}

			var key = grid.getStore().find('LpuRegion_id', record.LpuRegion_id);
			if(key < 0) grid.getStore().loadData([record], true);
		}
	},
	loadPersonData: function(data){
		var form = this;
		var grid = form.PersonNewslatterGrid.getGrid();
		var base_form = form.MainPanel.getForm();
		Ext.Ajax.request({
			url: '/?c=Common&m=loadPersonData',
			params: {Person_id: data.Person_id,  mode: 'NewslatterEditWindow', LoadShort: 'true'},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj.length > 0) {
					var record = response_obj[0];
					record.NewslatterAccept_id = record.NewslatterAccept_id;
					record.Person_Fio = record.Person_Surname + ' ' + record.Person_Firname + ' ' + record.Person_Secname;
					record.PersonNewslatter_id = -swGenTempId(grid.getStore());
					record.RecordStatus_Code = 0;

					if(!record.NewslatterAccept_id){
						Ext.Msg.alert(lang['soobschenie'], 'У пациента '+record.Person_Fio+' отсутствует согласие наполучение рассылок. Пациент не будет добавлен в рассылку');
						return false;
					}
					var key = grid.getStore().find('Person_id', record.Person_id);
					if(key < 0) grid.getStore().loadData([record], true);
				}
			}
		});
	},
	addPerson: function(){
		var form = this;
		if (getWnd('swPersonSearchWindow').isVisible()){
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		getWnd('swPersonSearchWindow').show({
			onSelect: function (person_data) {
				if(person_data && person_data.Person_id){
					form.loadPersonData(person_data);
				}
				getWnd('swPersonSearchWindow').hide();
			},
			searchMode: 'all'
		});
	}
});