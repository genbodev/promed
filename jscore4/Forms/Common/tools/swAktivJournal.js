Ext.define('sw.tools.swAktivJournal', {
    alias: 'widget.swAktivJournal',
    extend: 'Ext.window.Window',
    title: 'Журнал активов в поликлинику',
	width: '90%',
	height: '70%',
	//maximizable: true,
	modal: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	showWndFromExt2: function(wnd, card_id){

		if(Ext.isEmpty(wnd) || Ext.isEmpty(card_id)){
			return;
		}
		var title = (wnd == 'swCmpCallCardNewCloseCardWindow') ? 'Карта вызова: Редактирование' : 'Талон вызова';
		new Ext.Window({
			id: "myFFFrameServed",
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
					src : "/?c=promed&getwnd=" + wnd + "&act=edit&showTop=1&cccid="+card_id
				}
			}]
		}).show();
		
	},
	initComponent: function() {
		var me = this;
		
		var cald = Ext.create('sw.datePickerRange', {
			maxValue: 'unlimited',
			dateFields: ['begDate', 'endDate'],
			refId: 'dateCalendar'
			});
		
			me.tbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			items: [
				Ext.create('sw.datePrevDay', {width: 100}),
				cald,
				Ext.create('sw.dateNextDay', {width: 100}),
				Ext.create('sw.dateCurrentDay', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentWeek', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				}),
				Ext.create('sw.dateCurrentMonth', {
					enableToggle: true,
					toggleGroup: 'dateGroup'
				})
			]
		});
		
		
		
		var swAktivGrid = Ext.create('Ext.grid.Panel', {
			viewConfig: {
				loadingText: 'Загрузка'
			},
			flex: 1,
			//autoScroll: true,
			stripeRows: true,
			refId: 'aktivGrid',
			tbar: [
				{
					xtype: 'button',
					refId: 'showCallCardBtn',
					text: 'Талон вызова',
					handler: function(){
						var rec = swAktivGrid.getSelectionModel().getSelection()[0];
						if (typeof rec != 'undefined'){
							me.showWndFromExt2('swCmpCallCardNewShortEditWindow',rec.get('CmpCallCard_id'));
						}
					}
				},{
					xtype: 'button',
					refId: 'showCloseCardBtn',
					text: 'Карта вызова',
					handler: function(){
						var rec = swAktivGrid.getSelectionModel().getSelection()[0];
						if (typeof rec != 'undefined'){
							me.showWndFromExt2('swCmpCallCardNewCloseCardWindow',rec.get('CmpCallCard_id'));
						}
					}
				}
			],
			listeners:{
				itemclick: function( grid, record, item, index ){
					swAktivGrid.down('[refId=showCallCardBtn]').setDisabled(!(record.get('CmpCallCard_id') > 0));
					swAktivGrid.down('[refId=showCloseCardBtn]').setDisabled(!(record.get('CmpCloseCard_id') > 0));
				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'AktivGridStore',
				fields: [

					{ name: 'CmpCallCard_id', type: 'int'},
					{ name: 'CmpCloseCard_id', type: 'int'},

					{ name: 'AcceptTime', type: 'string'},
					{ name: 'Day_num', type: 'string'},
					{ name: 'Year_num', type: 'string'},

					{ name: 'Person_FIO', type: 'string'},
					{ name: 'Person_Age', type: 'string'},

					{ name: 'CmpReason_Name', type: 'string'},
					{ name: 'Diag', type: 'string'},
					{ name: 'Lpu_Nick', type: 'string'},

					{ name: 'HomeVisitStatus_Name', type: 'string'},
					{ name: 'Lpu_Phone', type: 'string'},
					{ name: 'HomeVisit_Address', type: 'string'},
					{ name: 'Person_Fin', type: 'string'},
					{ name: 'LpuBuilding_Name', type: 'string'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadAktivJournalList',
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					},
					extraParams:{
						endDate: Ext.Date.format(cald.dateTo, 'd.m.Y'),
						begDate: Ext.Date.format(cald.dateFrom, 'd.m.Y')
					}
				}
			}),
			columns: [
				{ dataIndex: 'CmpCallCard_id', key: true, hidden: true},
				{ dataIndex: 'CmpCloseCard_id', hidden: true},
				{ dataIndex: 'AcceptTime', header: 'Дата, время приема вызова', width: 150 },
				{ dataIndex: 'Day_num', header: '№ вызова за день', width: 105},
				{ dataIndex: 'Year_num', header: '№ вызова за год', width: 105},
				{ dataIndex: 'Person_FIO', header: 'ФИО', width: 200 },
				{ dataIndex: 'Person_Age', header: 'Возраст', width: 50 },
				{ dataIndex: 'CmpReason_Name', header: 'Повод', width: 200},
				{ dataIndex: 'Diag', header: 'Диагноз', width: 200},
				{ dataIndex: 'Lpu_Nick', header: 'МО передачи актива', width: 200},
				{ dataIndex: 'HomeVisitStatus_Name', header: 'Статус актива', width: 200},
				{ dataIndex: 'Lpu_Phone', header: 'Телефон', width: 200},
				{ dataIndex: 'HomeVisit_Address', header: 'Адрес посещения', width: 200},
				{ dataIndex: 'Person_Fin', header: 'Принял ', width: 200},
				{ dataIndex: 'LpuBuilding_Name', header: 'Подразделение СМП ', width: 200}
			]
		})		
		
		Ext.applyIf(me, {
			items: [
				swAktivGrid
			],
			
			 dockedItems: [
                 {
                     xtype: 'toolbar',
                     dock: 'bottom',
                     items: [
                         '->',
                         {
                             xtype: 'button',
                             text: 'Помощь',
                             iconCls: 'help16',
                             handler: function () {
                                 ShowHelp(me.title);
                             }
                         },
                         {
                             xtype: 'button',
                             refId: 'cancelBtn',
                             iconCls: 'cancel16',
                             text: 'Закрыть',
                             margin: '0 10',
                             handler: function () {
                                 me.close();
                             }
                         }
                     ]
                 }
            ]
		});
		
		me.callParent();
	}
});

