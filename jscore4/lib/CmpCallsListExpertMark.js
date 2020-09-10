//Форма Журнал вызовов

Ext.define('sw.CmpCallsListExpertMark', {
	alias: 'widget.CmpCallsListExpertMark',
	extend: 'Ext.panel.Panel',
	refId: 'CmpCallsListExpertMark',
	flex: 1,
	firstShow: true,
	layout: {
		type: 'fit',
		align: 'stretch'
	},

	initComponent: function() {
		var me = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;

		me.curArm = curArm;

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		me.armtype = me.initialConfig.armtype || 'default';

		me.gridStore = Ext.create('Ext.data.Store', {
			fields: [
				{
					name: 'CmpCallCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard_prmDate',
					type: 'date',
					convert : function(dt) {
						return new Date(dt.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
					}
				},
				{
					name: 'CmpCallCard_Numv',
					type: 'string'
				},
				{
					name: 'CmpCallCard_prmDateStr',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Ngod',
					type: 'string'
				},
				{
					name: 'Person_FIO',
					type: 'string'
				},
				{
					name: 'personAgeText',
					type: 'string'
				},
				{
					name: 'Person_Birthday',
					type: 'date'
				},
				{
					name: 'Adress_Name',
					type: 'string'
				},
				{
					name: 'CmpCallType_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_IsExtraText',
					type: 'string'
				},
				{
					name: 'CmpReason_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCardStatusType_id',
					type: 'int'
				},
				{
					name: 'CmpCallCardStatusType_Name',
					type: 'string'
				},
				{
					name: 'CmpCallCard_Comm',
					type: 'string'
				},
				{
					name: 'CmpCallCard_IsExtra',
					type: 'string'
				},
				{
					name: 'Diag',
					type: 'string'
				},
				{
					name: 'LpuBuilding_Name',
					type: 'string'
				},
				{
					name: 'EmergencyTeam_Num',
					type: 'string'
				},
				{
					name: 'CmpCloseCard_id',
					type: 'int'
				},
				{
					name: 'CmpCallCard112_id',
					type: 'int'
				},
				{
					name: 'CmpCallRecord_id',
					type: 'int'
				},{
					name: 'CmpCallCard_isControlCall',
					type: 'int'
				},
				{
					name: 'CmpCallCard_isControlCall',
					type: 'int'
				},
				{
					name: 'hasHdMark',
					type: 'int'
				},
				{
					name: 'Lpu_NMP_Name',
					type: 'string'
				},
				{
					name: 'ActiveVisitLpu_Nick',
					type: 'string'
				}
			],
			autoLoad: false,
			stripeRows: true,
			numLoad: 0,
			pageSize:100,
			sorters: [
				{
					property : 'CmpCallCard_prmDate',
					direction: 'DESC'
				}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=loadDispatcherCallsList',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				}
			}
		});

		me.bbar = Ext.create('Ext.PagingToolbar', {
			store: me.gridStore,
			displayInfo: true,
			pageSize: 100,
			beforePageText: 'Страница',
			afterPageText: 'из {0}',
			displayMsg: 'показано {0} - {1} из {2}'
		});

		var streetsCombo = Ext.create('sw.streetsSpeedCombo', {
			mainAddressField: true,
			name:'dStreetsCombo',
			fieldLabel: 'Улица',
			width: 222,
			labelWidth: 77,
			labelAlign: 'right',
			listConfig: {minWidth: 800, width: 800},
			defaultListConfig: {minWidth: 800, width: 800},
			forceSelection: (!getRegionNick().inlist(['krym'])),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-boundlist-item">'+
				'{[ this.addressObj(values) ]} '+
				'</div></tpl>',
				{
					addressObj: function(val){
						var city = val.Address_Name+' ';

						if(val.UnformalizedAddressDirectory_id){
							return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
						}else{
							return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
						}
					}
				}
			),
			displayTpl: new Ext.XTemplate(
				'<tpl for=".">' +
				'{[ this.getDateFinish(values) ]} ',
				'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
				'</tpl>',
				{
					getDateFinish: function(val){
						if (val.UnformalizedAddressDirectory_id){
							return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
						}
						else{
							return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
						}
					}
				}
			),
			listeners: {}
		});

		var cityCombo = Ext.create('sw.dCityCombo', {
			mainAddressField: true,
			width: 481,
			tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
			'{Town_Name}'+
			'<span style="color:gray; font-size: 12px"> {Socr_Name}</span>'+
			'</br><span style="color:gray; font-size: 10px"> {Region_Name}</span>'+
			'<span style="color:gray; font-size: 10px"> {Region_Socr}</span>'+
			'</div></tpl>',
			displayTpl: '<tpl for="."><tpl if="{Region_Nick}">{[values.Region_Nick]}</tpl> ' +
			'<tpl if="{Region_Name}">{[values.Region_Name]}</tpl> ' +
			'<tpl if="{Socr_Nick}">{[values.Socr_Nick]}</tpl> ' +
			'<tpl if="{Town_Name}">{[values.Town_Name]}</tpl></tpl>',
			listeners: {
				change: function( c, newV, oldV, o ){
					if(!c.getRawValue()) c.clearValue();
				},
				select: function(cmp, recs){
					var storeCity = cmp.getStore();

					if(storeCity.count()>1){
						storeCity.removeAll();
						storeCity.add(recs[0]);
					}
					streetsCombo.bigStore.getProxy().extraParams = {
						'town_id' : recs[0].get('Town_id'),
						'Lpu_id' : sw.Promed.MedStaffFactByUser.current.Lpu_id
					};
					streetsCombo.bigStore.load();
				}
			}

		});
		cityCombo.store.getProxy().extraParams = {
			'region_id' : getGlobalOptions().region.number,
			'region_name' : getGlobalOptions().region.name
			// 'showUnformalizedAdresses' : 2
		};
		cityCombo.store.load();


		me.dockedItems = [
			{
				xtype: 'toolbar',
				margin: '0 0 20 0',
				dock: 'bottom',
				items: [
					'->',
					{
						xtype: 'button',
						text: 'Помощь',
						iconCls: 'help16',
						handler: function()
						{
							ShowHelp(me.up('container').title);
						}
					}
				]
			}
		];

		me.items = [{
			xtype: 'BaseForm',
			id: me.id + 'CmpCallsListExpertMarkForm',
			border: false,
			frame: true,
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			flex: 1,
			autoScroll: true,
			bodyBorder: false,
			refId: 'CmpCallsListExpertMarkForm',
			tbar: [{
				xtype: 'container',
				items: [
					{
						xtype: 'toolbar',
						layout: {
							type: 'hbox',
							align: 'stretch'
						},
						margin: '5',
						border: false,
						items: [
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'left'
								},
								defaults:{
									labelAlign: 'right',
									width: 200,
									labelWidth: 80
								},
								items: [
									{
										xtype: 'swdatefield',
										fieldLabel: 'Дата c',
										name: 'begDate',
										allowBlank: false
									},
									{
										xtype: 'datefield',
										fieldLabel: 'Время c',
										format: 'H:i',
										hideTrigger: true,
										allowBlank: true,
										stripCharsRe: new RegExp('__:__'),
										invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
										plugins: [new Ux.InputTextMask('99:99')],
										name: 'begTime'
									},
									{
										xtype: 'swdatefield',
										fieldLabel: 'Дата по',
										name: 'endDate',
										allowBlank: false
									},
									{
										xtype: 'datefield',
										fieldLabel: 'Время по',
										format: 'H:i',
										hideTrigger: true,
										allowBlank: true,
										stripCharsRe: new RegExp('__:__'),
										invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
										plugins: [new Ux.InputTextMask('99:99')],
										name: 'endTime'
									},
									{
										xtype: 'combo',
										fieldLabel: 'Карты вызова',
										store: Ext.create('Ext.data.Store', {
											fields: ['id', 'name', 'mode'],
											data : [
												{"id":1, "name":"Требующие оценки"},
												{"id":2, "name":"Оцененные"}
											]
										}),
										queryMode: 'local',
										name: 'hasHdMark',
										displayField: 'name',
										disabled: me.isNmpArm,
										labelWidth: 110,
										width: 250,
										valueField: 'id',
										value: 1
										//value: ((getRegionNick() == 'ufa') || me.isNmpArm)?1:2
									},
								]
							},
							'->',
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'right'
								},
								items: [
									{
										xtype: 'button',
										iconCls: 'search16',
										text: 'Найти',
										width: 70,
										margin: '0',
										handler: function(){
											me.searchCmpCalls()
										}
									},
									{
										xtype: 'button',
										refId: 'resetBtn',
										iconCls: 'reset16',
										width: 70,
										text: 'Сброс',
										margin: '0 5',
										handler: function(){
											me.down('BaseForm').getForm().reset();
											me.down('BaseForm').getForm().isValid();
											/*
											var medpersonalfield = me.down('BaseForm').getForm().findField('MedPersonal_id')
											if(me.armtype == 'smpdispatchcall'){
												var record = medpersonalfield.getStore().findRecord('MedPersonal_id',getGlobalOptions().CurMedPersonal_id)
												if(record)
													medpersonalfield.setValue(record)
											}*/
										}
									},
									{
										xtype: 'splitbutton',
										iconCls: 'print16',
										padding: 3,
										text: 'Печать',
										menu: {
											xtype: 'menu',
											items: [
												{
													xtype: 'menuitem',
													iconCls: 'print16',
													text: 'Печать',
													handler: function () {
														Ext.ux.grid.Printer.print(me.down('grid'))
													}
												},
												{
													xtype: 'menuitem',
													iconCls: 'print16',
													text: 'Печать всего списка',
													handler: function () {
														var params = me.gridStore.proxy.extraParams,
															strParams = '';
														if(!params)
															return;
														for(var param in params){
															if (params.hasOwnProperty(param) && !Ext.isEmpty(params[param])) {
																strParams += '&' + param + '=' + params[param];
															}
														}
														var location = '/?c=CmpCallCard4E&m=printCmpCallsList' + strParams;
														var win = window.open(location);

													}
												}]
										},
										listeners: {
											click: function(){
												this.showMenu();
											}
										}
									}
								]
							}

						]
					},
					{
						xtype: 'fieldset',
						title: 'Фильтры',
						//collapsed: true,
						collapsible: true,
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						padding: '0 10 5 0',
						margin: 3,
						//flex: 1,
						fieldDefaults: {
							margin: 2,
							labelWidth: 120
						},
						listeners:{
							render: function(){
								this.collapse();
							}
						},
						items: [
							/*{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'swmedpersonalcombo',
										fieldLabel: 'Диспетчер вызовов',
										name: 'MedPersonal_id',
										hidden: me.curArm.inlist(['dispcallnmp', 'dispdirnmp']),
										listeners: {
											beforerender: function () {
												var me = this;
												me.getStore().on('beforeload', function (store) {
													store.proxy.extraParams = {MedServiceType_id: 19}
												})
												if(getGlobalOptions().setARM_id.split('_')[0] == 'smpdispatchcall'){
													me.getStore().on('load', function (store){
														var index = store.findBy(function(rec){return (rec.get('MedPersonal_id') == getGlobalOptions().CurMedPersonal_id)});
														var record = store.getAt(index);
														if(record && me.readOnly)
															me.setValue(record)
													})
												}

											}
										},
										readOnly: (getGlobalOptions().setARM_id.split('_')[0] == 'smpdispatchcall'),
										width: 352,
										displayField: 'Person_Fin',
										tpl: '<tpl for=".">' +
										'<div class="x-boundlist-item">' +
										'{Person_Fin}' +
										'</div></tpl>'

									}
								]
							},*/
							{
								xtype: 'container',
								layout: {
									type: 'hbox'
								},
								items: [
									{
										allowBlank: true,
										xtype: 'datefield',
										fieldLabel: 'Дата рождения с',
										labelAlign: 'right',
										format: 'd.m.Y',
										width: 352,
										validateOnBlur: false,
										validateOnChange: false,
										invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГГГ',
										plugins: [new Ux.InputTextMask('99.99.9999')],
										name: 'Person_Birthday_From',
										listeners: {
											change: function(cmp, newVal){
												var dt = Ext.Date.parse(newVal, 'd.m.Y'),
													blank = (newVal == '__.__.____' || newVal == '');

												if(blank || dt){
													cmp.clearInvalid()
												}else{
													cmp.validate()
												}
											}
										}
									},
									{
										allowBlank: true,
										xtype: 'datefield',
										fieldLabel: 'Дата рождения по',
										labelAlign: 'right',
										format: 'd.m.Y',
										width: 352,
										validateOnBlur: false,
										validateOnChange: false,
										invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГГГ',
										plugins: [new Ux.InputTextMask('99.99.9999')],
										name: 'Person_Birthday_To',
										listeners: {
											change: function(cmp, newVal){
												var dt = Ext.Date.parse(newVal, 'd.m.Y'),
													blank = (newVal == '__.__.____' || newVal == '');

												if(blank || dt){
													cmp.clearInvalid()
												}else{
													cmp.validate()
												}
											}
										}

									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'numberfield',
										hideTrigger: true,
										width: 352,
										fieldLabel: 'Возраст с',
										labelAlign: 'right',
										name: 'Person_Age_From'
									},
									{
										xtype: 'numberfield',
										hideTrigger: true,
										width: 352,
										fieldLabel: 'Возраст по',
										labelAlign: 'right',
										name: 'Person_Age_To'
									},
									{
										xtype: 'textfield',
										width: 352,
										fieldLabel: 'Фамилия',
										labelAlign: 'right',
										name: 'Person_Fam'
									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									cityCombo,
									streetsCombo,
									{
										xtype: 'textfield',
										width: 352,
										fieldLabel: 'Имя',
										labelAlign: 'right',
										name: 'Person_Name'
									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'textfield',
										fieldLabel: 'Дом',
										labelAlign: 'right',
										width: 265,
										name: 'CmpCallCard_Dom'
									},
									{
										xtype: 'textfield',
										fieldLabel: 'Корпус',
										labelAlign: 'right',
										width: 213,
										labelWidth: 77,
										name: 'CmpCallCard_Korp'
									},
									{
										xtype: 'numberfield',
										fieldLabel: 'Квартира',
										labelAlign: 'right',
										width: 222,
										labelWidth: 77,
										name: 'CmpCallCard_Kvar',
										hideTrigger: true
									},
									{
										xtype: 'textfield',
										width: 352,
										fieldLabel: 'Отчество',
										labelAlign: 'right',
										name: 'Person_Middle'
									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'swCmpCallTypeCombo',
										name: 'CmpCallType',
										//id: 'CmpCallTypeCombo',
										fieldLabel: 'Тип вызова',
										labelWidth: 67,
										width: 213,
										hidden: true,
										editable: false
									},
									{
										xtype: 'swCmpCallTypeIsExtraCombo',
										fieldLabel: 'Вид вызова',
										width: 222,
										labelWidth: 77,
										triggerClear: true,
										labelAlign: 'right',
										name: 'IsExtra',
										hidden: true,
										editable: false
									}

								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'swDiag',
										name: 'Diag_id_from',
										width: 352,
										autoFilter:false,
										translate: false,
										labelAlign: 'right',
										triggerFind: true,
										fieldLabel: 'Диагноз с'
									},
									{
										xtype: 'swDiag',
										name: 'Diag_id_to',
										width: 352,
										translate: false,
										autoFilter:false,
										triggerFind: true,
										labelAlign: 'right',
										fieldLabel: 'Диагноз по'
									}
								]
							},
							{
								xtype: 'container',
								layout: {
									type: 'hbox',
									align: 'stretch'
								},
								items: [
									{
										xtype: 'swCmpResultCombo',
										hidden: !(getRegionNick().inlist(['perm'])),
										width: 352,
										labelAlign: 'right',
										name: 'CmpResult_id'
									}
								]
							}
						]
					}
				]
			}
			],
			items: [
				{
					xtype: 'gridpanel',
					store: me.gridStore,
					columns: [
						{
							dataIndex: 'CmpCallCard_id',
							text: 'ИД карты вызова',
							hidden: true
						},
						/*
						{
							dataIndex: 'CmpCallCard_prmDate',
							text: 'Дата и время',
							width: 120,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						*/
						{
							dataIndex: 'CmpCallCard_prmDate',
							text: 'Дата и время',
							width: 120,
							xtype:'datecolumn',
							//format: 'd.m.Y H:i:s',
							format: (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ? 'd.m.Y H:i' : 'd.m.Y H:i:s',
							filter: {
								xtype: 'swdatefield',
								format: 'd.m.Y',
								allowBlank: true,
								triggerClear: true,
								translate: false,
								filterName:'CmpCallCard_prmDateStr',
								onTriggerClick: function() {
									var dt1 = this;
									Ext.form.DateField.prototype.onTriggerClick.apply(this, arguments);

									if(!this.clearBtn){
										this.clearBtn = new Ext.Component({
											autoEl: {
												tag: 'div',
												cls: 'clearDatefieldsButton',
											},
											listeners: {
												el: {
													click: function() {
														dt1.reset();
													}
												}
											}
										});
									}
								}
							}
						},
						{
							dataIndex: 'CmpCallCard_Numv',
							text: '№ В/Д',
							width: 60,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpCallCard_Ngod',
							text: '№ В/Г',
							width: 60,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'Person_FIO',
							text: 'Пациент',
							width: 180,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'Person_Birthday',
							xtype: 'datecolumn',
							sortable: true,
							text: 'Возраст',
							width: 55,
							renderer:function(birthday){
								var result,
									now = new Date();

								if (Ext.isEmpty(birthday)) {
									result = '';
								} else {
									var years = swGetPersonAge(birthday, now);

									if (years > 0) {
										result = years + ' лет.';
									} else {
										var days = Math.floor(Math.abs((now - birthday)/(1000 * 3600 * 24))),
											months = Math.floor(Math.abs(now.getMonthsBetween(birthday)));

										if (months > 0) {
											result = months + ' мес.';
										} else {
											result = days + ' дн.';
										}
									}
								}

								return result;
							},
							//format: 'd.m.Y',
							filter: {
								xtype: 'transFieldDelbut',
								translate: false,
								filterMap: 'personAgeText'
							}
						},
						{
							dataIndex: 'Adress_Name',
							text: 'Адрес',
							width: 180,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpCallType_Name',
							text: 'Тип вызова',
							width: 120,
							filter: {xtype: 'swCmpCallTypeCombo'}
						},
						{
							dataIndex: 'CmpCallCard_IsExtraText',
							text: 'Вид вызова',
							width: me.isNmpArm ? 150 : 70,
							filter: {xtype: 'swCmpCallTypeIsExtraCombo'}
						},
						{
							dataIndex: 'CmpReason_Name',
							text: 'Повод',
							width: 150,
							filter: {
								xtype: 'cmpReasonCombo',
								autoFilter: false
							}
						},
						{
							dataIndex: 'CmpCallCardStatusType_Name',
							text: 'Статус вызова',
							width: 100,
							filter: {xtype: 'swCmpCallCardStatusTypeCombo'}
						},
						{
							dataIndex: 'CmpCallCard_Comm',
							text: 'Доп. информация',
							width: 200,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'CmpCallCard_IsExtra',
							text: 'СМП / НМП',
							width: 70,
							hidden: me.isNmpArm,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'Diag',
							text: 'Диагноз',
							width: 130,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						{
							dataIndex: 'LpuBuilding_Name',
							text: me.isNmpArm ? 'Подразделение НМП' : 'Подразделение СМП',
							//text: 'Подразделение СМП',
							width: 130,
							filter: {xtype: 'smpUnitsNestedCombo',displayTpl: '<tpl for=".">{LpuBuilding_fullName}</tpl>'}
						},
						{
							dataIndex: 'EmergencyTeam_Num',
							text: 'Бригада',
							width: 60,
							filter: {xtype: 'transFieldDelbut', translate: false}
						},
						/*{
							dataIndex: 'Lpu_NMP_Name',
							text: 'МО НМП',
							width: 100
						},*/
						{
							dataIndex: 'ActiveVisitLpu_Nick',
							text: 'МО передачи актива',
							hidden: me.isNmpArm,
							width: 120
						},
						{
							dataIndex: 'hasHdMark',
							refId: 'hasHdMarkColumn',
							text: 'Экспертная оценка',
							width: 110,
							renderer: function(value){
								if(value==2)
								return '<div class="x-grid3-check-on"></div>';
							},
							/*filter: {
								xtype: 'checkbox',
								altCls: 'none',
								enableKeyEvents : false,
								uncheckedValue: '1',
								value: '1',
								style: 'margin-left: 6px;',
								getRawValue: function(){
									return this.value?'2':'';
								},
								listeners:{
									change: function(){
										me.searchCmpCalls();
									}
								}
							}*/
						},
						{
							dataIndex: 'CmpCallCard_isControlCall',
							refId: 'isControlCallColumn',
							text: 'На контроле',
							width: 80,
							renderer: function(value){
								var swtcher = (value==2)?' x-grid-checkcolumn-checked':'';
								return '<img class="x-grid-checkcolumn swtcher'+swtcher+'" src="extjs/resources/images/default/s.gif">';
							},
							filter: {
								xtype: 'checkbox',
								altCls: 'none',
								enableKeyEvents : false,
								uncheckedValue: '1',
								value: '1',
								style: 'margin-left: 6px;',
								getRawValue: function(){
									return this.value?'2':'';
								},
								listeners:{
									change: function(){
										me.searchCmpCalls();
									}
								}
							}
						}
					],
					requires: [
						'Ext.ux.GridHeaderFilters'
					],
					/*plugins: [
						Ext.create('Ext.ux.GridHeaderFilters',
							{
								enableTooltip: false,
								reloadOnChange:true,
								enableFunctionOnEnter:true,
								functionOnEnter:function(){
									me.searchCmpCalls();
								}
							}
						)
					],*/
					plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true})],
					listeners: {
						render: function(){
							var baseForm = me.down('BaseForm').getForm();

							Ext.Ajax.request({
								url: '/?c=CmpCallCard&m=getDatesToNumbersDayYear',
								callback: function (opt, success, response) {
									var datesParams = Ext.JSON.decode(response.responseText);

									if(datesParams.data){
										var Dt_from = Ext.Date.parse(datesParams.data.startDateTime,'Y-m-d H:i:s'),
											Dt_to = Ext.Date.parse(datesParams.data.endDateTime,'Y-m-d H:i:s');

										baseForm.findField('endDate').setValue(Ext.Date.format(Dt_to,'d.m.Y'));
										baseForm.findField('endTime').setValue(Ext.Date.format(Dt_to,'H:i'));
										baseForm.findField('begDate').setValue(Ext.Date.format(Dt_from,'d.m.Y'));
										baseForm.findField('begTime').setValue(Ext.Date.format(Dt_from,'H:i'));
									}
									me.searchCmpCalls()
								}
							});

							Ext.Ajax.request({
								url: '/?c=CmpCallCard&m=getIsCallControllFlag',
								callback: function (opt, success, response) {
									var responseObj = Ext.decode(response.responseText);
									if(responseObj.length > 0){
										responseObj = responseObj[0];
									}
									me.down('[refId=isControlCallColumn]').setVisible(responseObj.SmpUnitParam_IsCallControll == 'true');

								}
							});
						},
						afterrender: function(){
							var tabpanel = me.up('tabpanel');

							var pressedkeyg = new Ext.util.KeyMap({
								target: me.el,
								binding: [
									{
										key: [Ext.EventObject.ENTER],
										fn: function(){me.searchCmpCalls()}
									}
								]
							});

							if(tabpanel){
								tabpanel.on('tabchange', function( tabPanel, newCard, oldCard, eOpts){
									var journalCalls = newCard.down('panel[refId=CmpCallsListExpertMark]');

									if(journalCalls && !me.firstShow){
										me.searchCmpCalls();
									};

								});
							};
						},
						itemcontextmenu: function(grid, record, item, index, event, eOpts){
							event.preventDefault();
							event.stopPropagation();
							me.showSubMenu(event.getX(), event.getY());
						},
						itemdblclick: function(){
							//var me = this,
							var recCard = this.getSelectionModel().getSelection()[0],
								card_id = recCard.get('CmpCallCard_id');
							me.showWndFromExt2('swCmpCallCardNewCloseCardWindow',card_id);
						},
						cellkeydown: function(grid, td, cellIndex, record, tr, rowIndex, e){

							if(e.getKey() == Ext.EventObject.ENTER){
								//var me = this,
								var recCard = this.getSelectionModel().getSelection()[0],
									card_id = recCard.get('CmpCallCard_id');
								me.showWndFromExt2('swCmpCallCardNewCloseCardWindow',card_id);
							}

						}
					}
				}
			]
		}];


		me.callParent(arguments);
	},

	searchCmpCalls: function(){
		var me = this,
			grid = me.down('grid'),
			baseForm = me.down('BaseForm'),
			dateTo = baseForm.down('[name=begDate]').getValue(),
			dateFrom = baseForm.down('[name=endDate]').getValue(),
			params = baseForm.getValues(),
			maxDays = (getGlobalOptions().setARM_id.split('_')[0] == 'smpdispatchcall') ? 1 : 31;

		if(!getRegionNick().inlist(['perm'])){
			var dateDiffInDays = parseInt((dateTo - dateFrom)/(24*3600*1000));
			if(dateDiffInDays > maxDays ){
				Ext.Msg.alert('Ошибка','Период поиска вызовов не может превышать ' + maxDays +' день');
				return false;
			}
		}

		if(!Ext.isDate(dateTo) || !Ext.isDate(dateFrom)){
			return ;
		}

		var cityCombo = baseForm.down('[name=dCityCombo]');
		if( cityCombo.getRawValue() ) {
			var city = cityCombo.store.getAt(0).data;
			if(city.KLAreaLevel_id==4){
				params.KLTown_id = city.Town_id;
				//если региона нет тогда нас пункт не относится к городу
				if(city.Region_id){
					params.KLSubRgn_id = city.Area_pid;
				} else{
					params.KLCity_id = city.Area_pid;
				}
			} else{
				params.KLCity_id = city.Town_id;
				//если город верхнего уровня
				if(city.KLAreaStat_id!=0){
					params.KLSubRgn_id = city.Area_pid;
				}
			}
			params.KLAreaLevel_id = city.KLAreaLevel_id;
			params.KLRgn_id = city.Region_id;
			params.UAD_id = city.UAD_id
		}

		var streetsCombo = baseForm.down('[name=dStreetsCombo]'),
			selStreetRec = streetsCombo.store.findRecord('StreetAndUnformalizedAddressDirectory_id', streetsCombo.getValue());

		if(selStreetRec){
			params.KLStreet_id = selStreetRec.get('KLStreet_id');
			params.UnformalizedAddressDirectory_id = selStreetRec.get('UnformalizedAddressDirectory_id');
		}


		params.CmpCallCard_IsExtra = params.IsExtra;
		params.CmpCallType_id = params.CmpCallType;
		params.CmpReason_id = params.CmpReason;
		params.searchType = 3;

		grid.store.proxy.extraParams = params;
		grid.applyHeaderFilters();
		grid.store.reload();

		me.firstShow = false;
	},
	clearPersonFields: function(){
		var baseForm = this.down('BaseForm');
		baseForm.down('[name=Person_FIO]').reset();
		baseForm.down('[name=Person_id]').reset();
		baseForm.down('[name=Sex_id]').reset();

	},
	setPatient: function(personInfo) {
		var baseForm = this.down('BaseForm');
		baseForm.down('[name=Person_FIO]').setValue(personInfo.PersonSurName_SurName + ' ' + personInfo.PersonFirName_FirName + ' ' + personInfo.PersonSecName_SecName);
		baseForm.down('[name=Person_id]').setValue(personInfo.Person_id);
		baseForm.down('[name=Sex_id]').setValue(personInfo.Sex_id);
	},
	showSubMenu: function(x,y){
		var me = this,
			grid = me.down('grid'),
			recCard = grid.getSelectionModel().getSelection()[0],
			card_id = recCard.get('CmpCallCard_id'),
			card112_id = recCard.get('CmpCallCard112_id'),
			closecard_id = recCard.get('CmpCloseCard_id');
		var subMenu = Ext.create('Ext.menu.Menu', {
			plain: true,
			renderTo: Ext.getBody(),
			items: [
				/*
				 	Талон вызова – при выборе пункта открывается форма «Талон вызова» в режиме редактирования. Доступность полей формы определяется правилами, описанными в ТЗ Талон вызова СМП.
				 	Карта вызова – пункт доступен, если на выбранный вызов создана Карта вызова. При выборе пункта открывается форма «Карта вызова» в режиме редактирования. Доступность полей формы определяется правилами, описанными в ТЗ Карта вызова СМП (раздел «Доступность полей Карты вызова для редактирования»).
				 	Экспертная оценка – пункт доступен из АРМ Старшего врача СМП, если на выбранный вызов создана Карта вызова. При выборе пункта открывается форма «Экспертная оценка» (см. раздел Описание формы «Экспертная оценка»).
				 	История вызова – при выборе пункта открывается форма «История вызова» (см. раздел Описание формы «История вызова»).
				 */
				{
					text: 'Талон вызова',
					handler: function(){
						subMenu.close();
						me.showWndFromExt2('swCmpCallCardNewShortEditWindow',card_id);
					}
				},
				{
					text: 'Карта вызова',
					hidden: !(closecard_id > 0),
					handler: function(){
						subMenu.close();
						me.showWndFromExt2('swCmpCallCardNewCloseCardWindow',card_id);
					}
				},
				{
					text: 'Карточка вызова 112',
					hidden: !(card112_id > 0),
					handler: function(){
						me.showCmpCallCard112(card_id);
						subMenu.close();
					}
				},
				{
					text: 'Экспертная оценка',
					hidden: !(closecard_id > 0 && (me.armtype == 'smpheaddoctor')),
					handler: function(){
						subMenu.close();
						var expertResponseWindow = Ext.create('sw.tools.swExpertResponseWindow',{
							closecard_id: closecard_id
						});
						expertResponseWindow.show();
					}
				},
				{
					text: 'История вызова',
					handler: function(){
						subMenu.close();

						var callCardHistoryWindow = Ext.create('sw.tools.swCmpCallCardHistory',{
							card_id: card_id
						});
						callCardHistoryWindow.show();
					}
				},
				{
					text: 'Прослушать аудиозапись',
					//hidden: !(recCard.get('CmpCallRecord_id')),
					handler: function(){
						subMenu.close();

						Ext.create('common.tools.swCmpCallRecordListenerWindow',{
							record_id : recCard.get('CmpCallRecord_id')
						}).show();
					}
				}
			]
		});
		subMenu.showAt(x,y);
	},
	showWndFromExt2: function(wnd, card_id){

		if(Ext.isEmpty(wnd) || Ext.isEmpty(card_id)){
			return;
		}
		var me = this,
			title = (wnd == 'swCmpCallCardNewCloseCardWindow') ? 'Карта вызова: Редактирование' : 'Талон вызова',
			action = 'edit';

		if(
			//getRegionNick() == 'ufa'
		(getRegionNick() == 'ufa' && me.armtype != 'smpdispatchstation') ||
		(getRegionNick() == 'perm' && me.armtype != 'smpdispatchstation')
		)
		{
			action = 'view';
		}

		new Ext.Window({
			id: "myFFFrame",
			title: title,
			header: false,
			extend: 'sw.standartToolsWindow',
			toFrontOnShow: true,
			//width : '100%',
			//modal: true,
			style: {
				'z-index': 90000
			},
			//height: '90%',
			//layout : 'fit',
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			maximized: true,
			constrain: true,
			renderTo: Ext.getCmp('inPanel').body,
			items : [{
				xtype : "component",
				autoEl : {
					tag : "iframe",
					src : "/?c=promed&getwnd=" + wnd + "&act=" + action + "&showTop=1&cccid="+card_id
				}
			}]
		}).show();
	},
	showCmpCallCard112: function(card_id){
		if(!card_id )
			return;
		var callcard112 = Ext.create('sw.tools.swCmpCallCard112',{
			view: 'view',
			card_id: card_id
		});
		callcard112.show();
	}
});