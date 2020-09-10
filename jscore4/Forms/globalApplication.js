//globalControl

Ext.Loader.setConfig({
    enabled: true,
    disableCaching: false,
	paths: {
		'sw.tools' : 'jscore4/Forms/Common/tools',
		'sw.lib' : 'jscore4/lib',
		'common': 'jscore4/Forms/Common',
		'stores' : 'jscore4/stores',
		'Ext.ux': 'extjs4/src/ux',
		'SMP' : 'jscore4/Forms/globCtrl/controller/SMP'
	}
});

Ext.require([
    'Ext.*',
    'Ext.ux.form.field.ClearButton'
]);

Ext.application({
    require: (['Ext.container.Viewport']),
    name: 'globalApp',
 
    appFolder: 'jscore4/Forms/globCtrl',
	
	controllers: [
        'MainController',
		'SMP.swDispatcherCallWorkPlace_controller',
		'SMP.swDispatcherDirectWorkPlace_controller',
		'SMP.swDispatcherStationWorkPlace_controller',
		'SMP.swHeadDoctorWorkPlace_controller',
		'SMP.HeadDoctorWorkPlace.swWialonTrackPlayerTab_controller',
		'common.InteractiveMapSMP.swInteractiveMapPanelController',
		'swBSMEDefaultWorkPlace_controller'
    ],
 
    launch: function() {
		
		//вечная  ошибка при сворачивании несуществующей группы
		Ext.override(Ext.grid.feature.Grouping, 
		{
			doCollapseExpand: function(collapsed, groupName, focus) {
				var me = this,
					view = me.view,
					lockingPartner = me.lockingPartner,
					header;

				if (me.groupCache[groupName] && me.groupCache[groupName].isCollapsed != collapsed) {
					me.groupCache[groupName].isCollapsed = collapsed;

					Ext.suspendLayouts();
					me.dataSource.onRefresh();
					header = Ext.get(this.getHeaderNode(groupName));
					view.fireEvent(collapsed ? 'groupcollapse' : 'groupexpand', view, header, groupName);

					
					if (lockingPartner) {
						lockingPartner.doCollapseExpand(collapsed, groupName, focus);
					}
					Ext.resumeLayouts(true);
					if (focus) {
						header.up(view.getItemSelector()).scrollIntoView(view.el, null, true);
					}
				}
			}
		});
		
		//кеширование окон
		Ext.define('winOptsCache', {
			fields: ['id', 'winClass', 'winWidth', 'winHeight', 'winX', 'winY', 'winMaximum'],
			extend: 'Ext.data.Model',
			proxy: {
				type: 'localstorage',
				id  : 'twitter-Searches'
			}
		});
		
		var winOptsCacheStore = Ext.create('Ext.data.Store', {
			model: 'winOptsCache',
			storeId: 'winOptsCacheStore'
		});
		
		winOptsCacheStore.load();
		//конец кеширования

		var createViewPort = function(selectArmButton,callback) {
			Ext.create('Ext.container.Viewport', {
				layout: 'border',
				items: [
				{
					xtype: 'panel',
					id: 'tbar_panel',
					region: 'north',
					bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;',
					items: [
						{
							xtype: 'toolbar',
							id: 'windowsListToolbar',
							defaults: {
								reorderable: true
							},
							plugins : Ext.create('Ext.ux.BoxReorderer', {}),
							items: []
						}
					],
					tbar: {
						xtype: 'toolbar',
						id: 'Mainviewport_Toolbar',
						items: 
						[
							selectArmButton	,
							'->',
/*
                            {
                                xtype: 'radiogroup',
                                columns: 4,
                                vertical: true,
                                padding: '2px 10px 0 0',
                                cls: 'theme-selector',
                                defaults:{
                                    fieldSubTpl: [
                                        '<div class="{wrapInnerCls} {noBoxLabelCls}" role="presentation">',
                                        '<input type="button" id="{id}" role="{role}" {inputAttrTpl}',
                                        '<tpl if="tabIdx"> tabIndex="{tabIdx}"</tpl>',
                                        '<tpl if="disabled"> disabled="disabled"</tpl>',
                                        '<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
                                        ' class="{fieldCls} color-theme {inputCls} {childElCls} {afterLabelCls}" autocomplete="off" hidefocus="true" />',
                                        '</div>',
                                        {
                                            disableFormats: true,
                                            compiled: true
                                        }
                                    ],
                                },
                                items: [
                                    { name: 'rb', inputValue: 'classic', checked: true, fieldStyle:'background: #c3d9ff;' },
                                    { name: 'rb', inputValue: 'light', fieldStyle:'background: #a5a5a5;'},
                                    { name: 'rb', inputValue: 'dark', fieldStyle:'background: #fff;'},
                                    { name: 'rb', inputValue: 'classic-modern', fieldStyle:'background: #e2edff;' }
                                ],
                                listeners:{
                                    change: function(cmp, newVal){

                                        Ext.util.CSS.removeStyleSheet("theme");

                                        var path = '';

                                        switch(newVal.rb){
                                            case 'classic' : path = "/extjs4/resources/css/ext-all.css"; break;
                                            case 'light' : path = "/extjs4/resources/ext-theme-neptune/ext-theme-neptune-all.css"; break;
                                            case 'dark' : path = "/extjs4/resources/ext-theme-gray/ext-theme-gray-all.css"; break;
                                            //case 'classic-modern' : path = "/extjs4/resources/ext-theme-access/ext-theme-access-all.css"; break;
                                            case 'classic-modern' : path = "/extjs4/resources/ext-newtheme/ext-theme-neptune.css"; break;
                                        }

                                        Ext.util.CSS.swapStyleSheet("theme", path);

                                    }
                                }

                            },
                            */
							{
								xtype: 'button',
								text: 'Управление подстанциями',
								refId: 'settingsBtn',
								iconCls: 'settings16',
								hidden: true,
								listeners: {
									click: function(){
										openSelectSMPStationsToControlWindow();
									}
								}
							}
							,
							sw.Promed.Actions.PromedExit
						]
					},
					layout: {
						type: 'auto'
					}
				},
				{	
					region: 'center',
					xtype: 'panel',							
					layout: {
						type: 'fit'
					},
					id: 'inPanel'
				}
				]

			});
			if (typeof callback == 'function') {
				callback();
			}
		}
		sw.Promed.MedStaffFactByUser.initiate({
			//сначала загрузим store MedStaffFact
			callback: function(selectArmButton) {
				//Потом создадим Viewport (основной фрейм), c кнопкой выбора армов, которая будет генерится с помощью store MedStaffFact
				createViewPort(selectArmButton, function(){
					//После создания Viewport открываем рабочее место по умолчания
					sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
				});
			}
		})
    }
});
