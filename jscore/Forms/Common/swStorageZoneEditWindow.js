/**
* swStorageZoneEditWindow - окно редактирования места хранения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Kurakin A.
* @version      02.2017
* @comment      
*/
sw.Promed.swStorageZoneEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Место хранения склада',
	layout: 'border',
	id: 'StorageZoneEditWindow',
	modal: true,
	shim: false,
	width: 550,
	height: 450,
	resizable: false,
	maximizable: true,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('StorageZoneEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if(Ext.isEmpty(this.form.findField('StorageZone_daterange').getValue1())){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.form.findField('StorageZone_daterange').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: 'Дата начала периода действия обязательна для заполнения.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var params = new Object();
		params.StorageZone_begDate = Ext.util.Format.date(this.form.findField('StorageZone_daterange').getValue1(),'d.m.Y');
		params.StorageZone_endDate = Ext.util.Format.date(this.form.findField('StorageZone_daterange').getValue2(),'d.m.Y');

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Msg) {
						Ext.Msg.alert('Ошибка', action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.StorageZone_id > 0) {
					var id = action.result.StorageZone_id;
					wnd.form.findField('StorageZone_id').setValue(id);
					if(typeof wnd.callback == 'function'){
						if(wnd.action == 'edit' && wnd.StorageZone_pid != wnd.form.findField('StorageZone_pid').getValue()){
							wnd.owner.StorageZoneTree.getRootNode().select();
						}
						wnd.callback(wnd.owner, id);
					}
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swStorageZoneEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.EmptyFn;
		this.StorageZone_id = null;
		this.StorageZone_pid = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].StorageZone_id ) {
			this.StorageZone_id = arguments[0].StorageZone_id;
		}
		this.StorageZoneCombo.getStore().baseParams.exceptStorageZone_id = this.StorageZone_id;
		this.setTitle("Место хранения склада");
		this.form.reset();
		if(getGlobalOptions().orgtype == 'lpu'){
			wnd.form.findField('LpuBuilding_id').showContainer();
			wnd.form.findField('LpuSection_id').showContainer();
			wnd.form.findField('LpuBuilding_id').getStore().baseParams = {Lpu_id:getGlobalOptions().lpu_id};
			wnd.form.findField('LpuBuilding_id').getStore().load();
			wnd.form.findField('LpuSection_id').getStore().baseParams = {Lpu_id:getGlobalOptions().lpu_id,mode:'combo'};
			wnd.form.findField('LpuSection_id').getStore().load();
		} else {
			wnd.form.findField('LpuBuilding_id').hideContainer();
			wnd.form.findField('LpuSection_id').hideContainer();
		}
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
        this.setFieldsDisabled(false);
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				var date_str = Ext.util.Format.date(new Date(),'d.m.Y');
				this.form.findField('StorageZone_daterange').setValue(date_str);
				if(arguments[0].StorageZone_pid){
					this.form.findField('StorageZone_pid').setValue(arguments[0].StorageZone_pid);
				}
				if(arguments[0].Storage_id){
					this.form.findField('Storage_id').setValue(arguments[0].Storage_id);
				}
				if(arguments[0].LpuBuilding_id){
					var LpuBuilding_id = arguments[0].LpuBuilding_id;
					this.form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
				}
				if(arguments[0].LpuSection_id){
					var LpuSection_id = arguments[0].LpuSection_id;
					this.form.findField('LpuSection_id').setValue(LpuSection_id);
				}
				if(arguments[0].Org_id){
					var Org_id = arguments[0].Org_id;
					this.form.findField('Org_id').setValue(arguments[0].Org_id);
				} else {
					var Org_id = getGlobalOptions().org_id;
				}
				this.form.findField('Org_id').getStore().load({
					params: {Org_id:Org_id},
					callback: function(){
						wnd.form.findField('Org_id').setValue(Org_id);
						wnd.form.findField('Org_id').fireEvent('change',wnd.form.findField('Org_id'),Org_id);
					}
				});
				if(arguments[0].fromARM == 'smp'){
					this.form.findField('StorageZone_IsMobile').setValue(2);
				} else {
					this.form.findField('StorageZone_IsMobile').setValue(1);
				}
				this.form.findField('StorageZone_IsPKU').setValue(1);
				this.form.findField('TempConditionType_id').setValue(2);
				this.form.findField('StorageZone_Address').hideContainer();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				if(this.action == 'view'){
					this.setFieldsDisabled(true);
				}
				this.form.findField('StorageZone_Address').showContainer();
				Ext.Ajax.request({
					url: '/?c=StorageZone&m=loadStorageZone',
					params:
					{
						StorageZone_id: this.StorageZone_id
					},
					success: function (response)
					{
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if(result && result[0]) {
							wnd.form.setValues(result[0]);
							if(result[0].StorageZone_pid){
								wnd.StorageZone_pid = result[0].StorageZone_pid;
							}
							var date_str = result[0].StorageZone_begDate+' - ';
							if(result[0].StorageZone_endDate){
								date_str += result[0].StorageZone_endDate;
							}
							this.form.findField('StorageZone_daterange').setValue(date_str);
							if(result[0].Org_id){
								var Org_id = result[0].Org_id;
								wnd.form.findField('Org_id').getStore().load({
									params: {Org_id:Org_id},
									callback: function(){
										wnd.form.findField('Org_id').setValue(Org_id);
										wnd.form.findField('Org_id').fireEvent('change',wnd.form.findField('Org_id'),Org_id);
									}
								});
							}
						}
						
					}.createDelegate(this),
					failure: function (response)
					{
						loadMask.hide();
						
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.Error_Msg) {
							// Ошибку уже показали
						} else {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
						}
						this.hide();
					}.createDelegate(this)
				});	
				loadMask.hide();
				break;
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	loadStorageCombo: function(){
		var wnd = this;
		var storage = this.form.findField('Storage_id').getValue();
		var baseParams = {
			Org_id: this.form.findField('Org_id').getValue()
		};
		if(getGlobalOptions().orgtype == 'lpu'){
			if(!Ext.isEmpty(this.form.findField('LpuBuilding_id').getValue())){
				baseParams.LpuBuilding_id = this.form.findField('LpuBuilding_id').getValue();
			}
			if(!Ext.isEmpty(this.form.findField('LpuSection_id').getValue())){
				baseParams.LpuSection_id = this.form.findField('LpuSection_id').getValue();
			}
		}
		this.form.findField('Storage_id').getStore().baseParams = baseParams;
		this.form.findField('Storage_id').getStore().load({
			callback:function(){
				if(Ext.isEmpty(storage) || wnd.form.findField('Storage_id').getStore().getById(storage) == -1){
					storage = '';
				}
				wnd.form.findField('Storage_id').setValue(storage);
				wnd.form.findField('Storage_id').fireEvent('change',wnd.form.findField('Storage_id'),storage)
			}
		});
	},
	initComponent: function() {
		var wnd = this;	

		this.StorageZoneCombo = new sw.Promed.SwBaseRemoteCombo(
		{
			displayField: 'StorageZone_Address',
			editable: true,
			enableKeyEvents: true,
			forceSelection: true,
			fieldLabel: 'Размещение',
			hiddenName: 'StorageZone_pid',
			queryDelay: 1,
			lastQuery: '',
			mode: 'remote',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'StorageZone_id'
				},
				[
					{name: 'StorageZone_id', mapping: 'StorageZone_id'},
					{name: 'StorageZone_Name', mapping: 'StorageZone_Name'},
					{name: 'StorageZone_Address', mapping: 'StorageZone_Address'}
				]),
				listeners: {
					'load': function(store) {
						var win = this;
						this.StorageZoneCombo.lastQuery = '';
						this.StorageZoneCombo.getStore().clearFilter();
					}.createDelegate(this)
				},
				url: '/?c=StorageZone&m=loadStorageZoneList',
				baseParams: {withStorageOnly:1}
			}),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table><tr><td style="width: 40px;"><td>{StorageZone_Address}&nbsp;</td><td>{StorageZone_Name}&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'StorageZone_id',
			width: 300,
			xtype: 'swbaseremotecombo',
			onTrigger2Click: function() {
				this.clearValue();
			},
			trigger2Class: 'x-form-clear-trigger',
			listeners: {
				render: function(c) {
				    Ext.QuickTips.register({
				        target: c.getEl(),
				        text: 'Поле заполняется для создания иерархической структуры мест хранения склада: указывается в каком ряду/шкафу/полке находится текущее место хранения.',
				        enabled: true,
				        showDelay: 20,
				        trackMouse: true,
				        autoShow: true
				    });
			    }
			}
		});	
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'StorageZoneEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=StorageZone&m=saveStorageZone',
				items: [{					
					xtype: 'hidden',
					name: 'StorageZone_id'
				},
				{
					fieldLabel: 'Период действия',
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					width: 230,
					name: 'StorageZone_daterange'
				},
				{
					xtype: 'sworgcomboex',
					hiddenName: 'Org_id',
					fieldLabel: 'Организация',
					width: 300,
					allowBlank: false,
					disabled: (!isSuperAdmin()),
					listeners: {
						'select':function (combo) {
							combo.fireEvent('change',combo, combo.getValue());
						},
						'change':function(combo,newValue){
							var wnd = this;
							var base_form = wnd.form;
							var rec = combo.getStore().getById(newValue);
							if(rec && rec.get('OrgType_SysNick') == 'lpu'){
								var LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
								base_form.findField('LpuBuilding_id').showContainer();
								Ext.Ajax.request({
									url: '/?c=Org&m=getLpuData',
									params:
									{
										Org_id: newValue
									},
									success: function (response)
									{
										var result = Ext.util.JSON.decode(response.responseText);
										if(result && result[0] && result[0].Lpu_id) {
											base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = result[0].Lpu_id;
											base_form.findField('LpuBuilding_id').getStore().load({
												params:{Lpu_id:result[0].Lpu_id},
												callback:function(){
													if(LpuBuilding_id && base_form.findField('LpuBuilding_id').getStore().getById(LpuBuilding_id)){
														base_form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
													} else {
														base_form.findField('LpuBuilding_id').setValue('');
													}
												}
											});
										}
									}.createDelegate(this)
								});
								var LpuSection_id = base_form.findField('LpuSection_id').getValue();
								base_form.findField('LpuSection_id').showContainer();
								base_form.findField('LpuBuilding_id').getStore().baseParams.mode = 'combo';
								base_form.findField('LpuBuilding_id').getStore().baseParams.Org_id = newValue;
								base_form.findField('LpuSection_id').getStore().load({
									params:{Org_id:newValue,mode:'combo'},
									callback:function(){
										if(LpuSection_id && base_form.findField('LpuSection_id').getStore().getById(LpuSection_id)){
											base_form.findField('LpuSection_id').setValue(LpuSection_id);
										} else {
											base_form.findField('LpuSection_id').setValue('');
										}
									}
								});
							} else {
								base_form.findField('LpuBuilding_id').setValue('');
								base_form.findField('LpuSection_id').setValue('');
								base_form.findField('LpuBuilding_id').hideContainer();
								base_form.findField('LpuSection_id').hideContainer();
							}
							this.loadStorageCombo();
						}.createDelegate(this)
					}
				},
				{
					hiddenName: 'LpuBuilding_id',
					fieldLabel: 'Подразделение',
					xtype: 'swlpubuildingglobalcombo',
					listeners:{
						'select':function (combo) {
							combo.fireEvent('change',combo);
						}.createDelegate(this),
						'change':function (combo, newValue, oldValue) {
							var lpusection = this.form.findField('LpuSection_id').getValue();
							this.form.findField('LpuSection_id').getStore().load({
								params: {LpuBuilding_id:newValue},
								callback: function(){
									if(Ext.isEmpty(lpusection) || wnd.form.findField('LpuSection_id').getStore().getById(lpusection) == -1){
										lpusection = '';
									}
									wnd.form.findField('LpuSection_id').setValue(lpusection);
									wnd.loadStorageCombo();
								}
							});
						}.createDelegate(this)
					},
					width: 300
				},
				{
					hiddenName: 'LpuSection_id',
					fieldLabel: 'Отделение',
					xtype: 'swlpusectionglobalcombo',
					lastQuery:'',
					listeners:{
						'select':function (combo) {
							combo.fireEvent('change',combo, combo.getValue());
						}.createDelegate(this),
						'change':function (combo, newValue, oldValue) {
							this.loadStorageCombo();
						}.createDelegate(this)
					},
					width: 300
				},
				{
					xtype: 'swstoragecombo',
					width: 300,
					allowBlank: false,
					hiddenName:'Storage_id',
					fieldLabel: 'Склад',
					listWidth: 500,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table><tr><td style="width: 40px;"><font color="red">{Storage_Code}</font>&nbsp;</td><td>{Storage_Name}&nbsp;</td><td>{Address}&nbsp;</td></tr></table>',
						'</div></tpl>'
					),
					initComponent: function() {
						sw.Promed.SwStorageCombo.superclass.initComponent.apply(this, arguments);
						this.store = new Ext.data.JsonStore({
							url: '/?c=DocumentUc&m=loadStorageList',
							key: 'Storage_id',
							autoLoad: false,
							fields: [
								{name: 'Storage_id', type:'int'},
								{name: 'StorageType_id', type:'int'},
								{name: 'StorageType_Code', type:'int'},
								{name: 'Storage_Code', type:'int'},
								{name: 'Storage_Name', type:'string'},
								{name: 'Storage_begDate', type:'date', dateFormat: 'd.m.Y'},
								{name: 'Storage_endDate', type:'date', dateFormat: 'd.m.Y'},
								{name: 'StorageStructLevel', type:'string'},
								{name: 'LpuSection_id', type:'int'},
								{name: 'MedService_id', type:'int'},
								{name: 'Org_id', type:'int'},
								{name: 'Address', type:'string'}
							],
				            listeners: {
				                'load': function(store) {
				                    this.onLoadStore(store);
				                }.createDelegate(this)
				            }
						});
					},
					listeners: {
						'select':function (combo) {
							combo.fireEvent('change',combo, combo.getValue());
						}.createDelegate(this),
						'change':function (combo, newValue, oldValue) {
							var wnd = this;
							var stor_zone = this.StorageZoneCombo.getValue();
							this.StorageZoneCombo.getStore().baseParams.Storage_id = newValue;
							this.StorageZoneCombo.getStore().load({
								callback:function(){
									if(Ext.isEmpty(stor_zone) || wnd.StorageZoneCombo.getStore().getById(stor_zone) == -1){
										stor_zone = '';
									}
									wnd.StorageZoneCombo.setValue(stor_zone);
								}
							});
						}.createDelegate(this)
					}
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Код',
					name: 'StorageZone_Code',
					allowBlank: false,
					width: 300,
					listeners: {
						render: function(c) {
						    Ext.QuickTips.register({
						        target: c.getEl(),
						        text: 'По коду места хранения формируется адрес места хранения',
						        enabled: true,
						        showDelay: 20,
						        trackMouse: true,
						        autoShow: true
						    });
					    }
					}
				},
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: 'Наименование',
					comboSubject: 'StorageUnitType',
					hiddenName: 'StorageUnitType_id',
					editable: true,
					allowBlank: false,
					width: 300
				},
				{
					xtype: 'swyesnocombo',
					fieldLabel: 'ПКУ',
					hiddenName: 'StorageZone_IsPKU',
					width: 300
				},
				{
					xtype: 'swyesnocombo',
					fieldLabel: 'Мобильное',
					hiddenName: 'StorageZone_IsMobile',
					width: 300
				},
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: 'Темп.режим',
					comboSubject: 'TempConditionType',
					hiddenName: 'TempConditionType_id',
					allowBlank: false,
					width: 300
				},
				this.StorageZoneCombo,
				{
					xtype: 'textfield',
					readOnly: true,
					fieldLabel: 'Адрес',
					name: 'StorageZone_Address',
					width: 300
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Примечание',
					name: 'StorageZone_AdditionalInfo',
					width: 300
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
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
		sw.Promed.swStorageZoneEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('StorageZoneEditForm').getForm();
	}	
});