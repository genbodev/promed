/*
swWindows
*/

//конструктор для типичных окон

Ext.define('sw.standartToolsWindow', {
	alias: 'widget.standartToolsWindow',
	extend: 'Ext.window.Window',
	title: 'Заголовок окна',
	width: 1400,
	height: 600,
	layout: 'fit',
	//closeAction: 'hide',
	modal: true,
	plugins: [new Ux.Translit(true, true)],
	
	initComponent: function() {
		var standartWin = this,
			conf = standartWin.initialConfig,
			comps = standartWin.configComponents;
		
		Ext.applyIf(standartWin, {
			items: [
				{
					xtype: 'BaseForm',
					id: standartWin.id+'BaseForm',
					layout: 'fit',
					dockedItems: [
						{
							xtype: 'toolbar',
							dock: 'top',
							items: [
								comps.top
							]
						},
						{
                            xtype: 'toolbar',
                            dock: 'bottom',
                            layout: {
                                type: 'hbox',
                                pack: 'end'
                            },
                            items: comps.subBottomItems
                        },
						{
							xtype: 'container',
							dock: 'bottom',
							refId: 'bottomButtons',
							weight: -100,
							margin: '5 4',
							layout: {
								align: 'top',
								pack: 'end',
								type: 'hbox'
							},
							items: [
								{
                                    xtype: 'container',
                                    flex: 1,
                                    items: [								
										comps.leftButtons										
									]
                                },
                                {
                                    xtype: 'container',
                                    layout: {
                                        type: 'hbox',
                                        align: 'middle'
                                    },
                                    items: [
										comps.rigthButtons,
										{
											xtype: 'button',
											text: 'Помощь',
											refId: 'helpButton',
											iconCls   : 'help16',
											handler   : function()
											{
												ShowHelp(this.up('window').title);
											}
										},
										{
											xtype: 'button',								
											iconCls: 'cancel16',
											refId: 'cancelButton',
											text: 'Закрыть',
											margin: '0 5',
											handler: function(){
												standartWin.close()
											}
										}
                                    ]
                                }
								
							]
						}
					],
					items: 
						comps.center
					
				}
			]
		});

        standartWin.callParent(arguments);

	}
})

Ext.define('sw.redNotifyWindow', {
	alias: 'widget.redNotifyWindow',
	extend: 'Ext.window.Window',
	title: 'titleText',
	height: 100,
	width: 300,
	layout: 'fit',
	cls: 'notifyWindow',
	contentText: 'contentText',
	header: false,
	hideInToolbar: true,
	bbar: [
		{
			xtype: 'button',
			text: 'Закрыть',
			handler: function () {}
		}
	],
	initComponent: function() {
		var redNotifyWindow = this,
			conf = redNotifyWindow.initialConfig;
		
		redNotifyWindow.on('beforeshow', function(){
			var win = Ext.ComponentQuery.query('redNotifyWindow');
			if(win.length > 1){
				win[0].destroy();
			}
		});
		
		var bbar = redNotifyWindow.bbar;
		
		Ext.applyIf(redNotifyWindow, {
			bbar: bbar,
			items: [
				{
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch',
						flex: 1,
						padding: '10 0 0 0'
					},
					items: [
						{
							xtype: 'label',
							html: redNotifyWindow.contentText
						},
						{
							xtype: 'button',
							cls: 'closeBtn',
							handler: function () {
								redNotifyWindow.close();
							}
						},
					]
				}
			]
		});
		
		redNotifyWindow.callParent(arguments);
	}
});

Ext.define('sw.selectStationsToControlWindow', {
	extend: 'sw.standartToolsWindow',
	xtype: 'selectStationsToControlWindow',
	title: 'Выбор подстанций для управления',
	height: 200,
	width: 500,
	layout: 'fit',
	refId: 'toolsBuildingwin',
	id: 'toolsBuildingwin',
	modal: true,
	configComponents: {
		center: {
			xtype: 'grid',
			border: false,
			refId: 'toolsBuildingwinGrid',
			columns: [
				{dataIndex: 'LpuBuilding_id', text: 'LpuBuilding_id', flex: 1, hidden: true, hideable: false},
				{dataIndex: 'LpuBuilding_Name', text: 'Подстанция', flex: 1, hidden: false, hideable: false},
				//{dataIndex: 'visibleSections', text: 'Отделения (кабинеты) НМП', width: 150, xtype: 'checkcolumn', hideable: false, sortable: false,
				//	hidden: (!getRegionNick().inlist(['ufa']))
				//},
				{dataIndex: 'visible', text: '', width: 55, xtype: 'checkcolumn', hideable: false, sortable: false},
				{dataIndex: 'MedPersonal_Name', text: 'Диспетчер', flex: 1, hidden: true, hideable: false}
			],
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			store: {
				autoLoad: true,
				proxy: {
					type: 'ajax',
					url: '/?c=MedService4E&m=loadMedPersonalLpuBuildings',
					reader: {
						type: 'json',
						root: 'users'
					}
				},
				fields: [
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'LpuBuilding_Name', type: 'string'},
					{name: 'visible', type: 'boolean', defaultValue: false},
					{name: 'MedPersonal_Name', type: 'string'}
					//{ name: 'visibleSections', type: 'boolean', defaultValue: true }
				],
				listeners: {
					'load': function (store) {
						var armtype = sw.Promed.MedStaffFactByUser.last.ARMType || sw.Promed.MedStaffFactByUser.current.ARMType,
							grid = Ext.ComponentQuery.query('[refId=toolsBuildingwinGrid]')[0];

						if (armtype && (armtype == 'smpheaddoctor')) {
							store.add({
								'LpuBuilding_id': 0,
								'LpuBuilding_Name': 'Отделения (кабинеты) НМП',
								'MedPersonal_Name': '',
								'visible': true
							});
						}

						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=getOperDepartamentOptions',
							callback: function (opt, success, response) {

								if (success && response.responseText && getRegionNick().inlist(['perm'])) {
									var res = Ext.JSON.decode(response.responseText);
									grid.operDepartamentOptions = res;

									if((armtype && (armtype != 'smpheaddoctor') && (res.SmpUnitParam_IsDispNoControl == 'true' || res.SmpUnitParam_IsDispOtherControl == 'true')) || (armtype && (armtype == 'smpheaddoctor') && res.SmpUnitParam_IsDocNoControl == 'true'))
									grid.columns.find(function (a, b, c) {
										if (a.dataIndex == 'MedPersonal_Name') {
											 a.show();
										}
									})
								}
							}
						});

						Ext.Ajax.request({
							url: '/?c=Options&m=getLpuBuildingsWorkAccess',
							callback: function (opt, success, response) {

								if (!success) {
									return;
								}

								var res = Ext.JSON.decode(response.responseText);

								store.each(function (r) {
									var LpuBuilding_id = r.get('LpuBuilding_id');
									if (r) {
										r.set('visible', (LpuBuilding_id.inlist(res.lpuBuildingsWorkAccess)));
										//r.set('visibleSections', (LpuBuilding_id.inlist(res.lpuBuildingsSectionsWorkAccess)));
									}
								});
							}
						});
					}
				}
			}
		},
		leftButtons: {
			xtype: 'button',
			text: 'Сохранить',
			iconCls: 'ok16',
			refId: 'saveButton',
			disabled: false,
			handler: function (btn, evt) {

				function saveLpuBuildingsWorkAccess(collectChecked){
					var armtype = sw.Promed.MedStaffFactByUser.last.ARMType || sw.Promed.MedStaffFactByUser.current.ARMType;

					Ext.Ajax.request({
						url: '/?c=Options&m=saveLpuBuildingsWorkAccess',
						params: {
							'lpuBuildingsWorkAccess': [collectChecked]
						},
						callback: function (opt, success, response) {
							if (success) {

								var w = Ext.getCmp('toolsBuildingwin');
								if (w) w.close();

								if (armtype && (armtype == 'smpheaddoctor')) {
									return false;
								}

								Ext.Ajax.request({
									url: '/?c=CmpCallCard4E&m=updateSmpUnitHistoryData',
									params: {
										'lpuBuildings': Ext.JSON.encode(collectChecked)
									}
								});

								//Ext.Msg.alert('Сообщение', 'Изменения сохранены', function(){toolsDPwin.close();});
								//cntr.reloadStores();

							}
						}
					});
				}

				var grid = btn.up('selectStationsToControlWindow').down('grid'),
					st = btn.up('selectStationsToControlWindow').down('grid').getStore(),
					armtype = sw.Promed.MedStaffFactByUser.last.ARMType || sw.Promed.MedStaffFactByUser.current.ARMType,
					collectChecked = [];
				//collectCheckedSections = [];

				st.each(function (rec) {
					if (rec.get('visible')) {
						collectChecked.push(rec.get('LpuBuilding_id'))
					}
					//if(rec.get('visibleSections')){collectCheckedSections.push(rec.get('LpuBuilding_id'))}
				});

				if(grid.operDepartamentOptions && grid.operDepartamentOptions.SmpUnitParam_IsDispOtherControl == 'true' && armtype && armtype!= 'smpheaddoctor'){
					//Проверка подстанций под управлением другими диспетчерами
					Ext.Ajax.request({
						url: '/?c=CmpCallCard4E&m=getDispControlLpuBuilding',
						params: {
							'lpuBuildings': Ext.JSON.encode(collectChecked)
						},
						callback: function(opt, success, response){
							if (!success) {
								return;
							}

							var res = Ext.JSON.decode(response.responseText),
								lpuBuildings = [];

							if(res.length > 0){
								res.forEach(function(item){
									lpuBuildings.push(item.LpuBuilding_Name)
								});

								Ext.MessageBox.confirm('Сообщение', 'Подстанции '+ lpuBuildings.join(', ') +' находятся под управлением другими диспетчерами. Взять данные подстанции для управления?', function(btn){
									if(btn == 'yes'){
										saveLpuBuildingsWorkAccess(collectChecked);
									}
								});
							}else{
								saveLpuBuildingsWorkAccess(collectChecked);
							}
						}
					});
				}else{
					saveLpuBuildingsWorkAccess(collectChecked)
				}
			}
		}
	}
});