/**
* swResourceMedServiceEditWindow - редактирование ресурса на службе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Promed
* @version      30.08.2012
* @comment      Префикс для id компонентов RMSEW (swResourceMedServiceEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swResourceMedServiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swResourceMedServiceEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swResourceMedServiceEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();
		
		var data = new Object();
		var params = {};
				
		var grid = this.MedProductCardResourceGrid.getGrid();
		var MedProductCardResourceData = [];
		grid.getStore().clearFilter();
		
		if ( grid.getStore().getCount() > 0 ) {
			MedProductCardResourceData = getStoreRecords(grid.getStore());
			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}
		
		params.MedProductCardResourceData = Ext.util.JSON.encode(MedProductCardResourceData);
		
		var Resource_Name = base_form.findField('Resource_Name').getValue();
		
		data.ResourceMedServiceData = {
			'Resource_id': base_form.findField('Resource_id').getValue(),
			'ResourceType_id': base_form.findField('ResourceType_id').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'Resource_Name': base_form.findField('Resource_Name').getValue(),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};
		if(base_form.findField('ResourceType_id').disabled){
			params.ResourceType_id = base_form.findField('ResourceType_id').getValue();
		}
		// Получаем данные из грида связей для сохранения 
		params.ucrData = [];
		var rgrid = this.ResourceGrid.getGrid();
		if ( rgrid.getStore().getCount() > 0 ) {
			// todo: Здесь конечно можно выбрать только те которые изменились
			params.ucrData = Ext.util.JSON.encode(getStoreRecords(rgrid.getStore()));
		}
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();
				
				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.success ) {
							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	deleteMedProductCardResource: function() {
	
		var grid = this.MedProductCardResourceGrid.getGrid();
		var	wnd = this;

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('MedProductCardResource_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {			
				
					var record = grid.getSelectionModel().getSelected();

					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
			
					wnd.FormPanel.getForm().findField('ResourceType_id').setDisabled(wnd.action == 'view' || (wnd.MedProductCardResourceGrid.getGrid().getStore().getCount() > 0 && !Ext.isEmpty(wnd.MedProductCardResourceGrid.getGrid().getStore().getAt(0).get('MedProductCardResource_id'))));
					
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_meditsinskoe_izdelie'],
			title: lang['vopros']
		});
	
	},
	openMedProductCardResourceEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swMedProductCardResourceEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_svyazi_s_meditsinskim_izdeniem_uje_otkryito']);
			return false;
		}

		var
			formParams = new Object(),
			grid = this.MedProductCardResourceGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.Lpu_id = this.Lpu_id;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.MedProductCardResourceData != 'object' ) {
				sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_dannyie']);
				return false;
			}

			data.MedProductCardResourceData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('MedProductCardResource_id') == data.MedProductCardResourceData.MedProductCardResource_id);
			});

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.MedProductCardResourceData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.MedProductCardResourceData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('MedProductCardResource_id')) ) {
					grid.getStore().removeAll();
				}
				
				data.MedProductCardResourceData.MedProductCardResource_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.MedProductCardResourceData ], true);
			}
			
			wnd.FormPanel.getForm().findField('ResourceType_id').setDisabled(wnd.action == 'view' || (wnd.MedProductCardResourceGrid.getGrid().getStore().getCount() > 0 && !Ext.isEmpty(wnd.MedProductCardResourceGrid.getGrid().getStore().getAt(0).get('MedProductCardResource_id'))));

			return true;
		};
		params.formMode = 'local';

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('MedProductCardResource_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('swMedProductCardResourceEditWindow').show(params);

		return true;
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'ResourceMedServiceEditWindow',
	setRangeFilter: function(){
		var form = this;
		var grid = form.ResourceGrid.getGrid();
		var base_form = form.FormPanel.getForm();// console.log('base_form:', base_form);
		var resource_begDT = new Date(base_form.findField('Resource_begDT').getValue());
		var resource_endDT = new Date(base_form.findField('Resource_endDT').getValue());
		grid.getStore().clearFilter(true);// suppressEvent = true
		grid.getStore().filterBy(function(record){
			var recordBegDate = new Date(record.get('UslugaComplexMedService_begDT'));
			var recordEndDate = new Date(record.get('UslugaComplexMedService_endDT'));
			if(recordEndDate && recordEndDate < resource_begDT){// услуга закончена на момент начала ресурса
				return false;
			}
			if(resource_endDT && resource_endDT < recordBegDate){// услуга ещё не началась, а ресурс уже закрыт
				return false;
			}
			return true;
		}, this);
	},
	initComponent: function() {
		var form = this;
		
		this.ResourceGrid = new sw.Promed.ViewFrame({
			id: 'UslugaComplexGrid',
			region: 'center',
			object: 'UslugaComplexMedService',
			height:200,
			dataUrl: '/?c=MedService&m=loadUslugaComplexResourceGrid',
			editformclassname: '',
			autoLoadData: false,
			saveAtOnce: false,
			//saveAllParams: true,
			stringfields: [
				{ name: 'UslugaComplexMedService_id', key: true, type:'int' ,hidden:true, isparams: true },
				{ name: 'isActive', header: lang['svyaz_da_net'], sortable: false, type: 'checkcolumnedit', isparams: true },
				{ name: 'Resource_id', hidden:true, type:'int', isparams: true},
				{ name: 'UslugaComplexResource_id', hidden:true, type:'int', isparams: true},
				{ name: 'UslugaComplex_Name', header: lang['naimenovanie'], autoexpand: true},
				{ name: 'UslugaComplexResource_Time',  header: lang['planovaya_dlitelnost'], width: 100, editor: new Ext.form.NumberField(), isparams: true }/*,
				{ name: 'UslugaComplexResource_begDT', type: 'date', editor: new Ext.form.DateField(), header: lang['data_nachala'], width: 100},
				{ name: 'UslugaComplexResource_endDT',header: lang['data_okonchaniya'],type: 'date', width: 100 }*/
				,{ name: 'UslugaComplexMedService_begDT', hidden:true}
				,{ name: 'UslugaComplexMedService_endDT', hidden:true}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name: 'action_delete',hidden: true},
				{name: 'action_refresh',hidden: false},
				{name: 'action_print',hidden: true},
				{name: 'action_save', hidden: true/*, url: '/?c=MedService&m=saveResourceLink'*/}
			],
			/*onBeforeEdit: function(o) {
				if(o.row==0){
					var check = o.value;
					if(check == 'true'){
						check = 'false'
					}else{
						check = 'true'
					}
					o.record.set('isActive',check);
					form.ResourceGrid.onAfterEdit(o)
				}
				return o;
			},*/
			onAfterEdit: function(o) {
				if (!o.record.get('isActive')) {
					// Если сняли галку, то и убираем время
					o.record.set('UslugaComplexResource_Time',null);
					// todo: по идее если сохранять сразу при изменении здесь еще надо сбрасывать UslugaComplexResource_id сразу после сохранения, но это пока убрали
				}
				/*
				var base_form = form.FormPanel.getForm();
				var params = {
					Resource_id:base_form.findField('Resource_id').getValue(),
					UslugaComplexMedService_id:o.record.get('UslugaComplexMedService_id'),
					isActive:o.record.get('isActive'),
					UslugaComplexResource_id:o.record.get('UslugaComplexResource_id')
				}
				Ext.Ajax.request(
				{
					url: '/?c=MedService&m=saveResourceLink',
					params: params,
					failure: function(response, options) {
						// do nothing
					},
					success: function(response, action) {
						o.record.commit();
						o.grid.stopEditing(true);
					}
				});
				*/
			},
			onLoadData: function() {// console.log('---ResourceGrid onLoadData()');
				if (form.action == 'add') {
					var resourceTypeCombo = form.FormPanel.getForm().findField('ResourceType_id');

					if (resourceTypeCombo.store.data.length > 0) {
						resourceTypeCombo.setValue(resourceTypeCombo.store.getAt('0').get('ResourceType_id'));
					}
				}

				var grid = form.ResourceGrid.getGrid();
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplexMedService_id') ) {
					grid.getStore().removeAll();
					return true;
				}
				form.setRangeFilter();
			}
		});		
		this.MedProductCardResourceGrid = new sw.Promed.ViewFrame({
			id: 'MedProductCardResourceGrid',
			region: 'center',
			object: 'MedProductCardResource',
			height: 200,
			dataUrl: '/?c=MedService&m=loadMedProductCardResourceGrid',
			editformclassname: '',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			saveAtOnce: false,
			stringfields: [
				{ name: 'MedProductCardResource_id', key: true, type: 'int', hidden:true },
				{ name: 'RecordStatus_Code', hidden: true, type: 'int'},
				{ name: 'MedProductCard_id', hidden: true, type: 'int'},
				{ name: 'MedProductClass_Name', header: lang['naimenovanie_mi'], type: 'string', id: 'autoexpand'},
				{ name: 'MedProductCardResource_begDT', header: lang['data_nachala'], type: 'date', width: 100},
				{ name: 'MedProductCardResource_endDT', header: lang['data_okonchaniya'], type: 'date', width: 100}			
			],
			actions: [
				{name: 'action_add', handler: function() { form.openMedProductCardResourceEditWindow('add'); }},
				{name: 'action_edit', handler: function() { form.openMedProductCardResourceEditWindow('edit'); }},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', handler: function() { form.deleteMedProductCardResource(); }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true},
				{name: 'action_save', hidden: true}
			],
			onLoadData: function() {
				var grid = form.MedProductCardResourceGrid.getGrid();
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MedProductCardResource_id') ) {
					grid.getStore().removeAll();
				}
			},
			title: lang['svyaz_s_meditsinskim_izdeliem']
		});
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'ResourceMedServiceEditForm',
			labelAlign: 'right',
			labelWidth: 110,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Resource_id' },
				{ name: 'MedService_id' },
				{ name: 'Resource_Name' },
				{ name: 'ResourceType_id' },
				{ name: 'Resource_begDT' },
				{ name: 'Resource_endDT' }
			]),
			url: '/?c=MedService&m=saveResource',
			items: [{
				name: 'Resource_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name:'Resource_Name',
				hiddenName: 'Resource_Name',
				xtype: 'textfield',
				width: 400,
				fieldLabel: lang['naimenovanie']
			}, {
				fieldLabel: lang['tip'],
				hiddenName: 'ResourceType_id',
				allowBlank: false,
				listWidth: 600,
				listeners: 
				{
					'change': function(combo, newValue, oldValue) {
						
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						if ( typeof record == 'object' ) {
							( record.get('ResourceType_id') == 3 ) ? this.MedProductCardResourceGrid.show() : this.MedProductCardResourceGrid.hide();
							form.syncSize();
							form.syncShadow();
						}
					}.createDelegate(this)
				},
				width: 400,
				xtype: 'swresourcetypecombo'
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 160,
					columnWidth: .5,
					items: [{
						fieldLabel: lang['data_nachala'],
						name: 'Resource_begDT',
						allowBlank: false,
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();
								base_form.findField('Resource_endDT').setMinValue(newValue);
								form.setRangeFilter();
							}.createDelegate(this)
						},
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					}]
				},{
					layout: 'form',
					border: false,
					labelWidth: 128,
					columnWidth: .5,
					items: [{
						fieldLabel: lang['data_okonchaniya'],
						name: 'Resource_endDT',
						allowBlank: true,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						listeners: {
							'change': function(field, newValue){
								form.setRangeFilter();
							}.createDelegate(this)
						},
						xtype: 'swdatefield'
					}]
				}]
			},
			this.ResourceGrid,
			this.MedProductCardResourceGrid
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('Resource_id').disabled ) {
						base_form.findField('Resource_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('Resource_id').disabled ) {
						base_form.findField('Resource_id').focus(true);
					}
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swResourceMedServiceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('ResourceMedServiceEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swResourceMedServiceEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.doLayout();
		this.center();

		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.Lpu_id = arguments[0].Lpu_id || getGlobalOptions().lpu_id;

		this.getLoadMask().show();
		
		var resourceTypeCombo = base_form.findField('ResourceType_id');

		resourceTypeCombo.getStore().clearFilter();
		this.AllowedResourceType_ids = null;
		if ( arguments[0].AllowedResourceType_ids ) {
			this.AllowedResourceType_ids = arguments[0].AllowedResourceType_ids;
			resourceTypeCombo.getStore().filterBy(function(rec) {
				return rec.get('ResourceType_id').inlist(win.AllowedResourceType_ids);
			});
		}
		resourceTypeCombo.lastQuery = '';

		this.MedProductCardResourceGrid.hide();
		this.syncSize();
		this.syncShadow();
		this.ResourceGrid.removeAll({clearAll:true});
		
		this.ResourceGrid.getGrid().getStore().baseParams.MedService_id = base_form.findField('MedService_id').getValue();
		this.MedProductCardResourceGrid.getGrid().getStore().baseParams.Resource_id = 0;
		if(base_form.findField('Resource_id').getValue()>0){
			this.ResourceGrid.getGrid().getStore().baseParams.Resource_id = base_form.findField('Resource_id').getValue();
			this.MedProductCardResourceGrid.getGrid().getStore().baseParams.Resource_id = base_form.findField('Resource_id').getValue();
		}
		switch ( this.action ) {
			case 'add':
				this.ResourceGrid.getGrid().getStore().load();
				this.MedProductCardResourceGrid.getGrid().getStore().removeAll();
				this.setTitle("Ресурс: Добавление");
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
					},
					params: {
						MedService_id:base_form.findField('MedService_id').getValue(),
						Resource_id:base_form.findField('Resource_id').getValue()
					},
					success: function(a,z,s) {
						win.ResourceGrid.getGrid().getStore().load()
						win.MedProductCardResourceGrid.getGrid().getStore().load({
							callback: function () {
								resourceTypeCombo.setDisabled(win.action == 'view' || (win.MedProductCardResourceGrid.getGrid().getStore().getCount() > 0 && !Ext.isEmpty(win.MedProductCardResourceGrid.getGrid().getStore().getAt(0).get('MedProductCardResource_id'))));
							}
						});
						if ( win.action == 'edit' ) {
							win.setTitle("Ресурс: Редактирование");
							win.enableEdit(true);
						}
						else {
							win.setTitle("Ресурс: Просмотр");
							win.enableEdit(false);
						}

						( resourceTypeCombo.getValue() == 3 ) ? win.MedProductCardResourceGrid.show() : win.MedProductCardResourceGrid.hide();
						win.MedProductCardResourceGrid.setReadOnly(win.action == 'view');
						// https://redmine.swan.perm.ru/issues/76159#note-27
						//resourceTypeCombo.disable();

						win.getLoadMask().hide();
						base_form.clearInvalid();
					},
					url: '/?c=MedService&m=loadResource'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('ResourceType_id').disabled ) {
			base_form.findField('ResourceType_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});
