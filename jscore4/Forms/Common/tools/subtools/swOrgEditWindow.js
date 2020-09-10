Ext.define('sw.tools.subtools.swOrgEditWindow', {
    extend: 'Ext.window.Window',

//    requires: [
//        'Ext.container.Container',
//        'Ext.button.Button'
//    ],
	refId: 'OrgEditWindow',
	width: 1024,
    height: 800,		
    title: 'Организация',	
	callback: Ext.emptyFn,
	saveOrgEditWindow: function(callback){
		if (!this.down('form').getForm().isValid()){
			Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
		}
		
		var form = this.down('form').getForm();		
		params = form.getValues();		
		if (this.Org_id != null) params.Org_id = this.Org_id;
				
		Ext.Ajax.request({
			url: '/?c=Org4E&m=saveOrg',
			params: params,
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText);
					
					if (res.Org_id) {						
						callback(res.Org_id);
					} else {
						Ext.Msg.alert('Ошибка',res.Error_Msg);
					}
				}

			}
		});
	},
	
	setValues: function(){
			var me = this,
				base_form = me.down('form').getForm();
				
			Ext.Ajax.request({
				url: '/?c=Org&m=getOrgData',
				params: {
					Org_id: me.Org_id
				},
				callback: function(opt, success, response) {
					if (success){
						var res = Ext.JSON.decode(response.responseText)[0];
							
//									base_form.findField('OrgType_id').setValue(base_form.findField('OrgType_id').value);
//							if ( base_form.findField('Org_rid').getValue() ) {
//								var Org_rid = base_form.findField('Org_rid').getValue();
//								base_form.findField('Org_rid').getStore().load({
//									params: {
//										Object:'Org',
//										Org_id: Org_rid,
//										Org_Name:''
//									},
//									callback: function()
//									{
//										base_form.findField('Org_rid').setValue(Org_rid);
//									}
//								});
//							}
//							if ( base_form.findField('Org_nid').getValue() ) {
//								var Org_nid = base_form.findField('Org_nid').getValue();
//								base_form.findField('Org_nid').getStore().load({
//									params: {
//										Object:'Org',
//										Org_id: Org_nid,
//										Org_Name:''
//									},
//									callback: function()
//									{
//										base_form.findField('Org_nid').setValue(Org_nid);
//									}
//								});
//							}

//									startDate = Ext.Date.parse(res.EmergencyTeamDuty_DTStart, "Y-m-d H:i:s"),
//									endDate = Ext.Date.parse(res.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s");
//
//								if (startDate) res.DutyTimeStart = Ext.Date.format(startDate, 'H:i');
//								if (startDate) res.DutyTimeFinish = Ext.Date.format(endDate, 'H:i');

						base_form.setValues(res);
						if ( res && res.Org_rid ) {
							base_form.findField('Org_rid').getStore().load({
								params: {
									Object:'Org',
									Org_id: res.Org_rid											
								},
								callback: function()
								{
									base_form.findField('Org_rid').setValue(base_form.findField('Org_rid').getValue());
								}
							});
						}
						
						if ( res && res.Org_nid ) {
							base_form.findField('Org_nid').getStore().load({
								params: {
									Object:'Org',
									Org_id: res.Org_nid											
								},
								callback: function()
								{
									base_form.findField('Org_nid').setValue(base_form.findField('Org_nid').getValue());
								}
							});
						}
		
						//console.log('@=');
						//console.log(res.Org_rid);
//								if ( conf.Org_id ) {
//									me.Org_id = conf.Org_id;
//									base_form.findField('Org_id').setValue(conf.Org_id);
//								} else {
//									me.Org_id = null;
//								}
					}
				}.bind(this)
			})
	},
	
	getBaseForm: function(){
		var me = this;
		var form = Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',			
			cls: 'mainFormNeptune',	
			items: [ {
				xtype: 'container',
				padding: '10 0 0 0',
				width: '100%',
				bodyPadding: 10,				
				layout: 'column',
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [{
					border: false,
					layout: 'form',					
					labelWidth: 220,
					width:350,
					padding: '10 10 10 10',
					items: [{						
						labelWidth: 150,
						fieldLabel: 'Код организации',
						format: 'd.m.Y',
						allowBlank: false,
						minValue: 1,
						maxValue: 2147483647, // в базе поле int.
						allowBlank: false,
						autoCreate: {tag: "input", size:14, maxLength: "10", autocomplete: "off"},
						name: 'Org_Code',
						endDateField: 'Org_endDate',
						listeners: {
							keydown: function(inp, e) {
								if (e.getKey() == e.F2)
								{
									this.onTriggerClick();

									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}
									e.stopEvent();
								}
							}
						},
						onTriggerClick: function() {
							var Mask = new Ext.LoadMask(Ext.get('OrgEditWindow'), {msg: "Пожалуйста, подождите, идет загрузка данных формы..."});
							Mask.show();

							Ext.Ajax.request({
								callback: function(opt, success, resp) {
									Mask.hide();

									var form = me.OrgEditForm.getForm();
									var response_obj = Ext.util.JSON.decode(resp.responseText);

									if (response_obj.Org_Code != '')
									{
										form.findField('Org_Code').setValue(response_obj.Org_Code);
									}
								},
								url: '/?c=Org&m=getMaxOrgCode'
							});
						},
						triggerAction: 'all',
						triggerClass: 'x-form-plus-trigger',
						width: 150,
						xtype: 'numberfield',
						tabIndex: 1
					}]
				}, {
					border: false,
					layout: 'form',					
					labelWidth: 220,
					width:300,
					padding: '10 10 10 10',
					items: [{
						xtype: 'datefield',
						width: 200,
						fieldLabel: 'Дата открытия',
						format: 'd.m.Y',
						allowBlank: true,
						name: 'Org_begDate',
						endDateField: 'Org_endDate',
						tabIndex: 2
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 220,
					width:300,
					padding: '10 10 10 10',
					items: [{
						xtype: 'datefield',
						width: 200,
						fieldLabel: 'Дата закрытия',
						format: 'd.m.Y',
						name: 'Org_endDate',
						begDateField: 'Org_begDate',						
						tabIndex: 3
					}]
				}, {
					allowBlank: false,
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					fieldLabel: 'Наименование',
					id: 'OEW_Org_Name',
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.getForm().findField('Org_Code').focus(true);
							}
						}
					},
					name: 'Org_Name',
					tabIndex: 4,
					anchor: '100%',
					xtype: 'textfield'
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					allowBlank: false,
					fieldLabel: 'Краткое наименование',
					//id: 'OEW_Org_Nick',
					name: 'Org_Nick',
					tabIndex: 5,
					anchor: '100%',
					xtype: 'textfield'
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					allowBlank: true,				
					typeCode: 'int',
					fieldLabel: 'Тип организации',
					//id: 'OEW_OrgType',
					allowSysNick: true,
					hiddenName: 'OrgType_id',
					name: 'OrgType_id',
					tabIndex: 6,
					anchor: '100%',
					xtype: 'swOrgTypeCombo'
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					fieldLabel: 'Описание',
					name: 'Org_Description',
					anchor: '100%',
					tabIndex: 7,
					xtype: 'textfield'
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					fieldLabel: 'Наследователь',
					anchor: '100%',
					name: 'Org_rid',
					triggerFind: false,
					allowBlank: true,
					xtype: 'dOrgCombo',
					tabIndex: 8,
					store: new Ext.data.Store({
						storeId: 'Org_rid_store',
						fields: [
							{name: 'Org_id', type:'int'},
							{name: 'Org_Nick', type:'string'},
						],
						proxy: {
							limitParam: undefined,
							startParam: undefined,
							paramName: undefined,
							pageParam: undefined,
							type: 'ajax',
							url: '?c=Org&m=getOrgList',
							reader: {
							type: 'json',
							successProperty: 'success',
							root: 'data'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							}
						}
					}),
					onTrigger2Click: function() {}			
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					fieldLabel: 'Правопреемник',
					anchor: '100%',
					name: 'Org_nid',
					triggerFind: false,					
					//onTrigger2Click: function() {},
					allowBlank: true,
					//hideTrigger: true,
					xtype: 'dOrgCombo',
					store: new Ext.data.Store({
						storeId: 'Org_nid_store',
						fields: [
							{name: 'Org_id', type:'int'},
							{name: 'Org_Nick', type:'string'},
						],
						proxy: {
							limitParam: undefined,
							startParam: undefined,
							paramName: undefined,
							pageParam: undefined,
							type: 'ajax',
							url: '?c=Org&m=getOrgList',
							reader: {
							type: 'json',
							successProperty: 'success',
							root: 'data'
							},
							actionMethods: {
								create : 'POST',
								read   : 'POST',
								update : 'POST',
								destroy: 'POST'
							}
						}
					}),
					tabIndex: 9
				}, {
					padding: '5 10',
					labelWidth: 150,		
					width:950,
					allowDecimals: false,
					allowNegative: false,					
					fieldLabel: 'Код стац. учреждения',
					name: 'OrgStac_Code',				
					xtype: 'numberfield',
					tabIndex: 10
				}]
			},
			{
				xtype: 'container',
				height: 800,
				items: [
					new Ext.TabPanel({
						activeTab: 0,
						border: false,							
						//enableTabScroll: true,
						//id: 'OrgEditTabPanel',
						items: [{
						//	height: 250,
							labelWidth: 60,
							layout: 'form',
							padding: '10 10 10 10',
							style: 'padding: 2px',
							//id: 'tab_data',
							title: '<u>1</u>. Основные атрибуты',
							items: [{
								autoHeight: true,
								title: 'Адрес',									
								xtype: 'fieldset',
								items: [{
									padding: '5 10',
									labelWidth: 150,		
									width:920,
									fieldLabel: 'Юридический адрес',
									name: 'UAddress_AddressText',
									xtype: 'textfield'
								}, {
									padding: '5 10',
									labelWidth: 150,		
									width:920,
									fieldLabel: 'Фактический адрес',
									name: 'PAddress_AddressText',
									xtype: 'textfield'
								}]
							}, {
								layout: 'column',
								xtype: 'fieldset',
								border: false,
								items: [{
									border: false,
									layout: 'form',
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ИНН',
										name: 'Org_INN',
										tabIndex: 10,											
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'КПП',
										name: 'Org_KPP',
										tabIndex: 11,											
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОГРН',
										name: 'Org_OGRN',
										tabIndex: 12,											
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОКАТО',
										name: 'Org_OKATO',
										tabIndex: 13,
										xtype: 'numberfield'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОКФС',
										name: 'Okfs_id',
										tabIndex: 14,										
										xtype: 'swOkfsCombo'
									}]
								}, {
									border: false,
									layout: 'form',
									width:300,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОКПО',
										name: 'Org_OKPO',
										tabIndex: 16,
										width: 213,
										xtype: 'textfield'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:450,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОКОПФ',
										name: 'Okopf_id',
										tabIndex: 15,
										width: 305,
										xtype: 'swOkopfCombo'
									}]
								}, {
									border: false,
									layout: 'form',									
									width:450,
									padding: '0 0 0 10',
									items: [{
										labelWidth: 50,
										fieldLabel: 'ОКВЭД',
										name: 'Okved_id',
										tabIndex: 16,
										width: 397,
										xtype: 'swOkvedCombo'
									}]
								}]
							}, {
								autoHeight: true,
								title: 'Контакты',									
								xtype: 'fieldset',
								layout: 'column',
								items: [{
									padding: '5 10',
									labelWidth: 100,		
									width:400,
									fieldLabel: 'Телефон',
									name: 'Org_Phone',
									xtype: 'textfield'
								}, {
									padding: '5 10',
									labelWidth: 100,		
									width:400,
									fieldLabel: 'Email',
									name: 'Org_Email',
									xtype: 'textfield'
								}]
							}]
						},
//						{
//							height: 250,
//							labelWidth: 143,
//							layout: 'fit',
//							refId: 'tab_serveterr',
//							title: '<u>2</u>. Территория обслуживания',
//							items: [ me.OrgServiceTerrGrid ]
//						}, {
//							height: 250,
//							labelWidth: 143,
//							layout: 'fit',
//							refId: 'tab_OrgRSchet',
//							title: '<u>3</u>. Расчётные счета',
//							items: [ me.OrgRSchetGrid]
//						}, {
//							height: 250,
//							labelWidth: 143,
//							layout: 'fit',
//							refId: 'tab_OrgHead',
//							title: '<u>4</u>. Контактные лица',
//							items: [ me.OrgHeadGrid ]
//						}, {
//							height: 250,
//							labelWidth: 143,
//							layout: 'fit',							
//							refId: 'tab_OrgLicence',
//							title: '<u>5</u>. Лицензии',
//							items: [ me.OrgLicenceGrid ]
//						}, {
//							height: 250,
//							labelWidth: 143,
//							layout: 'fit',
//							//disabled: true,
//							refId: 'tab_OrgFilial',
//							title: '<u>6</u>. Филиалы',
//							items: [ me.OrgFilialGrid ]
//						}
						]
					})
				]
			}]
		});
		form.on('storeloaded',function(){
			me.setValues();
		});
		return form;
	},
	
	
	// ИНИЦИАЛИЗАЦИЯ
	
	initComponent: function() {
        var me = this,
			conf = me.initialConfig;
        var win = this;
		
		/*
		 * Обслуживаемые территории grid
		 */
			
		me.OrgServiceTerrGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swOrgServiceTerrGridGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},
			listeners: {
				itemClick: function(cmp, record, item, index, e, eOpts ){

				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){

				},
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					//var btn = win.down('button[refId=viewTeamTemplate]');
					//btn.handler();
				}
			},
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [											
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Обновить',
						iconCls: 'refresh16',
						handler: function(){
							me.OrgServiceTerrGrid.store.reload();
						}
					}
				]
			}),
			store: new Ext.data.JsonStore({										
				autoLoad: true,
				numLoad: 0,
				storeId: 'OrgServiceTerrStore',
				fields: [
					{name: 'OrgServiceTerr_id', type: 'int'},
					{name: 'KLCountry_Name', type: 'string'},
					{name: 'KLRgn_Name', type: 'string'},
					{name: 'KLSubRgn_Name', type: 'string'},
					{name: 'KLCity_Name', type: 'string'},
					{name: 'KLTown_Name', type: 'string'},
					{name: 'KLAreaType_Name', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=OrgServiceTerr&m=loadOrgServiceTerrGrid',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{dataIndex: 'OrgServiceTerr_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'KLCountry_Name', text: 'Страна', width: 120, hideable: false},
				{dataIndex: 'KLRgn_Name', text: 'Регион', width: 120, hideable: false},
				{dataIndex: 'KLSubRgn_Name', text: 'Район', width: 120, hideable: false},
				{dataIndex: 'KLCity_Name', text: 'Город', width: 220, hideable: false},										
				{dataIndex: 'KLTown_Name', text: 'Населенный пункт', width: 250, hideable: false},
				{dataIndex: 'KLAreaType_Name', text: 'Тип населенного пункта', width: 180, hideable: false}
			]
		});
		
		
						
		/*
		 * Расчетные счета grid
		 */
			
		me.OrgRSchetGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swOrgRSchetGridGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},			
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [											
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Обновить',
						iconCls: 'refresh16',
						handler: function(){
							me.OrgRSchetGrid.store.reload();
						}
					}
				]
			}),			
			store: new Ext.data.JsonStore({										
				autoLoad: true,
				numLoad: 0,
				storeId: 'OrgRSchetStore',
				fields: [
					{name: 'OrgRSchet_id', type: 'int'},
					{name: 'OrgRSchet_RSchet', type: 'string'},
					{name: 'OrgRSchetType_Name', type: 'string'},
					{name: 'OrgBank_Name', type: 'string'},
					{name: 'OrgRSchet_begDate', type: 'string'},
					{name: 'OrgRSchet_endDate', type: 'string'},
					{name: 'Okv_Nick', type: 'string'},
					{name: 'OrgRSchet_Name', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=OrgStruct&m=loadOrgRSchetGrid',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{dataIndex: 'OrgRSchet_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'OrgRSchet_RSchet', text: 'Номер счёта', width: 120, hideable: false},
				{dataIndex: 'OrgRSchetType_Name', text: 'Тип счёта', width: 120, hideable: false},
				{dataIndex: 'OrgBank_Name', text: 'Банк', width: 120, hideable: false},
				{dataIndex: 'OrgRSchet_begDate', text: 'Дата открытия', width: 150, hideable: false},										
				{dataIndex: 'OrgRSchet_endDate', text: 'Дата закрытия', width: 150, hideable: false},
				{dataIndex: 'Okv_Nick', text: 'Валюта', width: 150, hideable: false},
				{dataIndex: 'OrgRSchet_Name', text: 'Примечание', width: 180, hideable: false}
			]
		});
		
		
						
		/*
		 * Контактные лица grid
		 */
			
		me.OrgHeadGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swOrgHeadGridGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},			
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [											
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Обновить',
						iconCls: 'refresh16',
						handler: function(){
							me.OrgHeadGrid.store.reload();
						}
					}
				]
			}),			
			store: new Ext.data.JsonStore({										
				autoLoad: true,
				numLoad: 0,
				storeId: 'OrgHeadStore',
				fields: [
					{name: 'OrgHead_id', type: 'int'},
					{name: 'OrgHead_Fio', type: 'string'},
					{name: 'OrgHeadPost_Name', type: 'string'},
					{name: 'OrgHead_Phone', type: 'string'},
					{name: 'OrgHead_Mobile', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=OrgStruct&m=loadOrgHeadGrid',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{dataIndex: 'OrgHead_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'OrgHead_Fio', text: 'ФИО', width: 480, hideable: false},
				{dataIndex: 'OrgHeadPost_Name', text: 'Должность', width: 220, hideable: false},
				{dataIndex: 'OrgHead_Phone', text: 'Телефон', width: 120, hideable: false},
				{dataIndex: 'OrgHead_Mobile', text: 'Мобильный телефон', width: 180, hideable: false}
			]
		});
		
					
		/*
		 * Лицензии grid
		 */
			
		me.OrgLicenceGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swOrgLicenceGridGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},			
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [											
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function(){

						}
					}, {
						xtype: 'button',
						text: 'Обновить',
						iconCls: 'refresh16',
						handler: function(){
							me.OrgLicenceGrid.store.reload();
						}
					}
				]
			}),			
			store: new Ext.data.JsonStore({										
				autoLoad: true,
				numLoad: 0,
				storeId: 'OrgLicenceStore',
				fields: [
					{name: 'OrgLicence_id', type: 'int'},
					{name: 'OrgLicence_Num', type: 'string'},
					{name: 'OrgLicence_setDate', type: 'string'},
					{name: 'OrgLicence_RegNum', type: 'string'},
					{name: 'OrgLicence_begDate', type: 'string'},
					{name: 'OrgLicence_endDate', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=OrgStruct&m=loadOrgLicenceGrid',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{dataIndex: 'OrgLicence_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'OrgLicence_Num', text: 'Номер лицензии', width: 220, hideable: false},
				{dataIndex: 'OrgLicence_setDate', text: 'Дата выдачи', width: 220, hideable: false},
				{dataIndex: 'OrgLicence_RegNum', text: 'Регистрационный номер', width: 200, hideable: false},
				{dataIndex: 'OrgLicence_begDate', text: 'Начало действия', width: 200, hideable: false},
				{dataIndex: 'OrgLicence_endDate', text: 'Окончание действия', width: 180, hideable: false}
			]
		});
		
		
					
		/*
		 * Филиалы  grid
		 */
			
		me.OrgFilialGrid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			refId: 'swOrgFilialGridGP',
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false				
			},			
			tbar: Ext.create('Ext.toolbar.Toolbar', {
				items: [
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						handler: function(){
							var swOrgFilialEditWindow = Ext.create('sw.tools.subtools.swOrgFilialEditWindow', {																
								Org_id: me.Org_id,
								renderTo: Ext.getCmp('inPanel').body,								
								//renderTo: Ext.getBody(),
								action: 'add'
							});
//							swOrgFilialEditWindow.on('selectTeamsFromTemplate', function(recs){
//								swOrgFilialEditWindow.close();
//								win.saveEmergencyTeamWithTime(recs);
//							})
							swOrgFilialEditWindow.show();
						}
					}, {
						xtype: 'button',
						text: 'Удалить',
						iconCls: 'delete16',
						handler: function(){						
							var record = me.OrgFilialGrid.getSelectionModel().getSelection();
							//console.log(record[0].data.OrgFilial_id);
							if (record[0] && record[0].data.OrgFilial_id > 0) {
								Ext.Ajax.request({
									params: {
										OrgFilial_id: record[0].data.OrgFilial_id
									},
									url: '/?c=OrgStruct&m=deleteOrgFilial',							
									callback: function(opt, success, response) 
									{
										me.OrgFilialGrid.store.reload();
									}
								})
							}
						}
					}, {
						xtype: 'button',
						text: 'Обновить',
						iconCls: 'refresh16',
						handler: function(){
							me.OrgFilialGrid.store.reload();
						}
					}
				]
			}),			
			store: new Ext.data.JsonStore({										
				autoLoad: true,
				numLoad: 0,
				storeId: 'OrgFilialStore',
				fields: [
					{name: 'OrgFilial_id', type: 'int'},
					{name: 'OrgFilial_Name', type: 'string'}
				],				
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=OrgStruct&m=loadOrgFilialGrid&Org_id='+me.Org_id,
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{dataIndex: 'checked', text: '', width: 55, xtype: 'checkcolumn', hidden: true, sortable: false},
				{dataIndex: 'OrgFilial_id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'OrgFilial_Name', text: 'Название филиала', width: 620, hideable: false}
			]
		});
		
		
		/*		 
		 * SHOW		 
		 */
		
		
		me.on('render', function(cmp){
			
//			cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_serveterr')).disable();
//			cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgRSchet')).disable();
//			cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgHead')).disable();
//			cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgLicence')).disable();
//			cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgFilial')).disable();
			
			switch(conf.action){
				case 'add' : {
					me.setTitle('Организация: Добавление');
					var base_form = me.down('form').getForm();
					base_form.reset();
					
//					base_form.findField('Org_begDate').setMinValue(undefined);
//					base_form.findField('Org_begDate').setMaxValue(undefined);
//					base_form.findField('Org_endDate').setMinValue(undefined);
//					base_form.findField('Org_endDate').setMaxValue(undefined);
					
//					base_form.setValues({
//						Org_id: me.Org_id
//					});
					
//					base_form.findField('Org_Name').focus(100, true);

					
					Ext.Ajax.request({
						callback: function(opt, success, resp) {
							var response_obj = Ext.JSON.decode(resp.responseText);
							if (response_obj.Org_Code != '') {
								base_form.findField('Org_Code').setValue(response_obj.Org_Code);
							}
						},
						url: '/?c=Org&m=getMaxOrgCode'
					});
					break;
				}
				
				case 'edit' : {
					me.setTitle('Организация: Редактирование');
					
					var base_form = me.down('form').getForm();
					base_form.reset();
					
					//cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_serveterr')).enable();
					//cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgRSchet')).enable();
					//cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgHead')).enable();
					//cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgLicence')).enable();
					//cmp.down('tabpanel').items.get(cmp.down('tabpanel').items.findIndex('refId','tab_OrgFilial')).enable();
					
					
				
					
//					base_form.load({
//						failure: function() {
//							Ext.Msg.alert('Ошибка','Не удалось загрузить данные с сервера');
//							me.hide(); 
//						},
//						params: {
//							Org_id: me.Org_id
//						},
//						success: function() {
//							//Mask.hide();
//							base_form.findField('OrgType_id').setValue(base_form.findField('OrgType_id').value);
//							if ( base_form.findField('Org_rid').getValue() ) {
//								var Org_rid = base_form.findField('Org_rid').getValue();
//								base_form.findField('Org_rid').getStore().load({
//									params: {
//										Object:'Org',
//										Org_id: Org_rid,
//										Org_Name:''
//									},
//									callback: function()
//									{
//										base_form.findField('Org_rid').setValue(Org_rid);
//									}
//								});
//							}
//							if ( base_form.findField('Org_nid').getValue() ) {
//								var Org_nid = base_form.findField('Org_nid').getValue();
//								base_form.findField('Org_nid').getStore().load({
//									params: {
//										Object:'Org',
//										Org_id: Org_nid,
//										Org_Name:''
//									},
//									callback: function()
//									{
//										base_form.findField('Org_nid').setValue(Org_nid);
//									}
//								});
//							}
//							//win.checkOrgTypeAdditional();
//							//win.checkOrgRid();
//							//win.setAddress();
//						},
//						url: '/?c=Org&m=getOrgData'
//					});
					
					
					
					break;
				}
//				case 'view' : {
//					win.loadEmergencyTeamTemplate(conf.config);
//					win.lockForm();
//					break;
//				}
			};
			
			var pressedkey = new Ext.util.KeyMap({
				target: me.getEl(),
				binding: [
					{
						key: [13],
						fn: function(){
							me.saveBtn.handler();
						}
					}
				]
			})
		})
		
		
		
		
		/*
		 * поехали
		 */
		
        Ext.applyIf(me, {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
               this.getBaseForm()
            ],
			dockedItems: [{
				xtype: 'container',
				dock: 'bottom',
				layout: {
					type: 'hbox',
					align: 'stretch',
					padding: 4
				},
				items: [{
					xtype: 'container',
					layout: 'column',
					items: [{
						xtype: 'button',
						iconCls: 'add16',
						text: 'Добавить',
						refId: 'saveBtn',
						handler: function(){
							me.saveOrgEditWindow(function(a){
								Ext.Msg.alert('ОК', 'Организация добавлена.');
								me.callback(a);
								me.close();								
							});
						//	me.close();
						}
					},{
						xtype: 'button',
						text: 'Сброс',
						margin: '0 5',
						iconCls: 'resetsearch16',
						handler: function(){
							var base_form = me.down('form').getForm();
							base_form.reset();
							Ext.Ajax.request({
								callback: function(opt, success, resp) {
									var response_obj = Ext.JSON.decode(resp.responseText);							
									if (response_obj.Org_Code != '') {
										base_form.findField('Org_Code').setValue(response_obj.Org_Code);
									}
								},
								url: '/?c=Org&m=getMaxOrgCode'
							});
						}
					}]
				},{
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch',
						pack: 'end'
					},
					items: [{
						xtype: 'button',
						iconCls: 'cancel16',
						text: 'Закрыть',
						margin: '0 5',
						handler: function(){
							me.close()
						}
					}]
				}]
			}]
        });
        me.callParent(arguments);
    }
});