/**
 * swEvnQueueWaitingListJournal - Журнал листов ожидания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 */
Ext6.define('common.Worksheet.worksheetListWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.worksheetListWindow',
	autoShow: false,
	maximized: true,
	cls: 'arm-window-new AnketaMaker',
	title: 'Список анкет',
	constrain: true,
	layout: 'absolute',
	header: true,
	show: function() {
		var me = this;
		me.store.load();
		me.callParent(arguments);
	},

	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			storeId: 'allMedicalForms',
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=MedicalForm&m=getMedicalForms',
				reader: {
					type: 'json'
				}
			},
		});

		me.worksheetGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			/* requires: [ //еще понадобится
				'Ext6.ux.GridHeaderFilters'
			],*/
			store: me.store,
			columns: [
				{ text: 'Название анкеты',  dataIndex: 'MedicalForm_Name', flex: 1},
				{ text: 'Параметры', dataIndex: 'MedicalFormAgeSex', flex: 1 },
				{ text: 'Создан', dataIndex: 'MedicalForm_insDT', flex: 1},
				{ text: 'Последнее изменение', dataIndex: 'MedicalForm_updDT', flex: 1},
				/*{ width: 50,
					renderer: function (value, cell, record) {
						return  '<img src="../img/Worksheet/overflow.png" class="moreinfo">'
					}
				}*/
			],
			listeners:{
				itemdblclick: function( obj, record, item, index, event, eOpts ) {
					getWnd('worksheetConstructor').show({
						MedicalForm_id: record.data.MedicalForm_id
					})
				},
				/*itemclick: function( obj, record, item, index, event, eOpts ){
					var elCls = event.target.getAttribute('class');

					if (elCls && elCls.indexOf("moreinfo") !== -1){
						me.showMoreInfo(record, event.getX(), event.getY());
					}
				},*/
			}
		});

		Ext6.apply(me, {
			items: [
				{
					xtype: 'container',
					anchor: '100% 100%',
					layout: {
						type: 'border'
					},
					items: [
						me.worksheetGrid
					]
				}, {
					xtype: 'button',
					itemId: 'addQuestionAbsolute',
					iconCls: 'addQuestionAbsolute-icon',
					cls: 'addQuestionAbsolute',
					style: {
						right: '30px',
						bottom: '20px',
						'z-index': '100500'
					},
					handler:function () {
						getWnd('accessParamsWorksheet').show();
					}
				}
			]
		});
		me.callParent(arguments);
	},

	showMoreInfo: function(record ,getX,getY) {
		var me = this;

		Ext6.create('Ext6.menu.Menu', {
			width: 228,
		//	shadow: false,
		//	plain: true,
			items:[
				{
					text: 'Продублировать'

				}, {
					text:'Изменить',
					handler: function (record) {
						getWnd('worksheetConstructor').show()
					}

				}, {
					text: 'Параметры',

				}, {
					text: 'Закрыть анкету',

				}

			]
		}).showAt(getX, getY);
	},
});
