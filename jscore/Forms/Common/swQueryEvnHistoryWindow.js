/**
* swQueryEvnHistoryWindow - История запросов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swQueryEvnHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swQueryEvnHistoryWindow',
	objectSrc: '/jscore/Forms/Common/swQueryEvnHistoryWindow.js',
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: 'История изменения запроса',
	draggable: true,
	id: 'swQueryEvnHistoryWindow',
	width: 900,
	height: 300,
	modal: true,
	plain: true,
	resizable: false,
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	show: function() {
		var win = this;
		sw.Promed.swQueryEvnHistoryWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].QueryEvn_id) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { win.hide(); });
		}
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.QueryEvn_id = arguments[0].QueryEvn_id;
		this.viewFrame.getGrid().getStore().load({
			params: {QueryEvn_id: this.QueryEvn_id}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=QueryEvn&m=doLoadHistory',
			id: this.id + 'viewFrame',
			paging: false,
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'QueryEvnUpd_id', key: true },
				{ header: 'Дата и время изменения', type: 'string', name: 'QueryEvnUpd_Date', width: 160 },
				{ header: 'Пользователь', type: 'string', name: 'pmUser_NameChange', width: 120 },
				{ header: 'Тип изменений', type: 'string', name: 'QueryEvnUpdType_Name', id: 'autoexpand' },
				{ header: 'Исполнитель', type: 'string', name: 'pmUser_NameExec', width: 120 },
				{ header: 'Ответственный ', type: 'string', name: 'pmUser_NameResp', width: 120 },
				{ header: 'Статус', type: 'string', name: 'QueryEvnStatus_Name', width: 120 },
			],
			toolbar: false,
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [
				this.viewFrame
			]
		});
		sw.Promed.swQueryEvnHistoryWindow.superclass.initComponent.apply(this, arguments);
	}
});