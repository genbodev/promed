/**
 * swTimeTableGrafRecListWindow  - Просмотр списка записавшихся/Групповой прием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.PolkaWP
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.PolkaWP.swTimeTableGrafRecListWindow', {
	/* свойства */
	alias: 'widget.swTimeTableGrafRecListWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swTimeTableGrafRecListWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Групповой прием',
	width: 600,
	height: 500,
	autoHeight: true,
	bodyPadding: 0,
	data: {},
	show: function (conf) {
		var win = this;
		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		win.mask('Загрузка списка пациентов');
		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		} else {
			win.callback = Ext6.emptyFn;
		}
		win.TimetableGraf_id = conf.TimetableGraf_id;
		this.callParent(arguments);
		var store = win.grid.getStore();
		store.removeAll();
		if(!Ext6.isEmpty(win.TimetableGraf_id)){
			store.load({
				params: {
					TimetableGraf_id: win.TimetableGraf_id
				},
				callback: function (records, operation, success) {
					win.unmask();
				}
			});
		}


	},
	getSaveArr: function(savePacket) {
		var me = this,
			arrAllPerson = me.grid.getStore().getModifiedRecords(),
			TimetableGrafRecList = false;
		if(arrAllPerson.length>0){
			TimetableGrafRecList = [];
			arrAllPerson.forEach(function (el) {
				TimetableGrafRecList.push({
					'TimetableGrafRecList_id': el.get('TimetableGrafRecList_id'),
					'TimeTableGrafRecList_IsGroupFact': el.get('TimeTableGrafRecList_IsGroupFact')
				});
			});
		}
		return TimetableGrafRecList;
	},
	doSave: function(){

		var me = this,
			save_url = '/?c=TimetableGraf6E&m=saveCheckedPerson',
			TimetableGrafRecList = me.getSaveArr(),
			params = {};

		if(TimetableGrafRecList){
			params.TimetableGrafRecList = Ext6.JSON.encode(TimetableGrafRecList).toString();
			me.mask('Сохранение признака присутствия');
			Ext6.Ajax.request({
				url: save_url,
				callback: function (opt, success, response) {
					me.unmask();
					me.close();
				},
				params: params
			});
		} else {
			me.close();
		}
	},
	selectAllItems: function(rec) {
		var me = this,
			g = me.grid,
			selItem = [];
		me.autoSelect = true; // Для обхода обработчика
		g.getStore().each(function (rec) {
			if (rec.get('TimeTableGrafRecList_IsGroupFact') && rec.get('TimeTableGrafRecList_IsGroupFact')>1)
				selItem.push(rec);
		});
		g.getSelectionModel().select(selItem);
		delete me.autoSelect;
	},
	/* конструктор */
	initComponent: function() {
		var win = this;

		win.grid = Ext6.create("Ext6.grid.Panel", {
			viewConfig: {
				loadMask: false
			},
			cls: 'checking-grid',
			viewModel: true,
			height: 410,
			scrollable: true,
			border: false,
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 30,
				checkOnly: true,
				listeners: {
					select: function (store,rec,i) {
						if(!win.autoSelect){
							rec.set('TimeTableGrafRecList_IsGroupFact',2);
						}
					},
					deselect: function (store,rec,i) {
						if(!win.autoSelect){
							rec.set('TimeTableGrafRecList_IsGroupFact',0);
						}
					}
				}
			}),
			columns: [
				{text: 'ФИО', flex: 1, align: 'left', dataIndex: 'Person_FIO', height: 30},
				{text: 'Дата рождения', align: 'centr', width: 150, dataIndex: 'Person_BirthDay'}
			],
			store: {
				model:  Ext6.create('Ext6.data.Model',{
					fields: [{
						name: 'TimetableGrafRecList_id',
						type: 'int'
					}, {
						name: 'Person_id',
						type: 'int'
					}, {
						name: 'Person_FIO',
						type: 'string'
					},{
						name: 'Person_BirthDay',
						type: 'string'
					},{
						name: 'TimeTableGrafRecList_IsGroupFact',
						type: 'auto',
						convert: function (value) {
							var resStr = '';
							if( !isNaN(parseInt(value)) && parseInt(value) == 1)
								resStr = '1';
							return resStr;
						}
					}]
				}),
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=TimetableGraf6E&m=loadTimeTableGrafRecList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null,
				listeners: {
					load: function(){
						win.selectAllItems();
					}
				}
			}
		});



		Ext6.apply(win, {
			border: false,
			items: [
				win.grid
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена'
			}, {
				handler: function () {
					win.doSave();
				},
				cls: 'buttonAccept',
				text: 'Сохранить',
				margin: '0 20 0 0'
			}]
		});

		this.callParent(arguments);
	}
});