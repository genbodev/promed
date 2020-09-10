/**
 * swTableRecordDataWindow - просмотр информации о записи в таблице БД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swTableRecordDataWindow', {
	/* свойства */
	alias: 'widget.swTableRecordDataWindow',
    autoShow: false,
    autoScroll: true,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	height: 600,
	modal: true,
	layout: 'form',
	refId: 'tablerecorddatasw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Информация о записи',
	width: 800,

	/* методы */
	show: function() {
		this.callParent(arguments);

		var win = this;

		win.setTitle('Информация о записи');

		if ( !arguments || !arguments[0] ) {
			Ext6.Msg.alert(langs('Ошибка'), 'Ошибка открытия формы "' + win.title + '".<br/>Отсутствуют необходимые параметры.', function() { win.hide(); });
			return false;
		}

		win.recordId = (!Ext6.isEmpty(arguments[0].recordId) ? arguments[0].recordId : null);
		win.schema = (!Ext6.isEmpty(arguments[0].schema) ? arguments[0].schema : null);
		win.table = (!Ext6.isEmpty(arguments[0].table) ? arguments[0].table : null);

		if ( Ext6.isEmpty(win.recordId) || Ext6.isEmpty(win.table) ) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не указаны обязательные параметры.'), function() { win.hide(); });
			return false;
		}

		win.setTitle(win.getTitle() + ' в ' + (!Ext6.isEmpty(win.schema) ? win.schema + '.' : "") + win.table);

		win.center();

		win.DataView.getStore().removeAll();
		win.DataView.getStore().load({
			params: {
				id: win.recordId,
				schema: win.schema,
				table: win.table
			},
			callback: function(options, success, response) {
			}
		});
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		win.dataViewStore = Ext6.create('Ext.data.Store', {
			fields: [
				{ name: 'column_name' },
				{ name: 'isPrimaryKey' },
				{ name: 'description' },
				{ name: 'value' },
				{ name: 'nameValue' }
			],
			proxy: {
				reader: {
					rootProperty: 'data',
					type: 'json'
				},
				type: 'ajax',
				url: '/?c=Utils&m=getTableData'
			}
		});

		win.DataView = Ext6.create({
			border: false,
			itemSelector: 'div.RecordData',
			region: 'center',
			store: win.dataViewStore,
			tpl: new Ext6.XTemplate(
				'<tpl for=".">',
				'<div class="RecordData" style="padding: 5px; font-size: 16;">',
				'<tpl if="this.valueIsEmpty(values.description) == false">',
				'{description} ({field}): ',
				'<tpl else>',
				'{field}: ',
				'</tpl>',
				'<span style="font-weight: bold;">',
				'{value}',
				'<tpl if="this.valueIsEmpty(values.nameValue) == false">',
				' ({nameValue})',
				'</tpl>',
				'</span>',
				'</div>',
				'</tpl>',
				{
					valueIsEmpty: function (val) {
						return Ext6.isEmpty(val);
					}
				}
			),
			xtype: 'dataview'
		});

		win.Panel = new Ext6.Panel({
			autoScroll: true,
			border: false,
			bodyStyle: 'padding: 5px;',
			items: [
				win.DataView
			]
		});

        Ext6.apply(win, {
			items: [
				win.Panel
			],
			buttons: [
			'->',
			{
				handler:function () {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		this.callParent(arguments);
    }
});