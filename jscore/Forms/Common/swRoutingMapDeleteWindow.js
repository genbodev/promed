/**
* swRoutingMapDeleteWindow - Добавление типа маршрутизации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sharipov Fidan
* @version      11.2019

*/
sw.Promed.swRoutingMapDeleteWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	width: 400,
	height: 114,
	border: false,
	resizable: false,
	closeAction: 'hide',
	buttonAlign: 'center',
	url: '/?c=RoutingMap&m=delete',
	title: 'Вы действительно хотите удалить выбранные записи?',
	initComponent: function () {
		let wnd = this;

		wnd.formPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelWidth: 120,
			bodyStyle:'padding: 5px',
			items: [
				new Ext.form.ComboBox({
					hiddenName: 'permanenteDelete',
					name: 'permanenteDelete',
					fieldLabel: 'Причина удаления',
					emptyText : 'Укажите причину удаления',
					valueField: 'id',
					displayField: 'text',
					triggerAction : 'all',
					allowBlank: false,
					anchor: '100%',
					mode: 'local',
					store: new Ext.data.SimpleStore({
						fields: [
							{ name: 'id', type: 'int' },
							{ name: 'text', type: 'string' }
						],
						data: [
							[ 1, 'Окончание действия маршрутизации' ],
							[ 2, 'Ошибка ввода' ]
						]
					})
				})
			]
		});

		Ext.apply(wnd, {
			border: false,
			items: [
				wnd.formPanel
			],
			buttons: [{
				text: 'Да',
				handler: function () {
					wnd.doDelete();
				}
			}, {
				text: 'Нет',
				handler: function () {
					wnd.hide();
				}
			}]
		});
		sw.Promed.swRoutingMapDeleteWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		let wnd = this;
		if (!wnd.setRequiredArg(arguments[0])) {
			sw.swMsg.alert(lang['oshibka'], 'Переданы не все обязательные параметры');
			wnd.hide();
			return false;
		}

		let combo = wnd.formPanel.getForm().findField('permanenteDelete');
		let begDT = wnd.RoutingMap.get('RoutingMap_begDate');
		begDT = new Date(Ext.util.Format.date(begDT, 'Y-m-d')).setHours(0, 0, 0, 0);
		let today = new Date().setHours(0, 0, 0, 0);
		if (begDT != today) {
			combo.setValue(1);
			combo.setDisabled(true);
		} else {
			combo.setValue(null);
			combo.setDisabled(false);
		}

		sw.Promed.swRoutingMapDeleteWindow.superclass.show.apply(this, arguments);
	},
	doDelete: function () {
		let wnd = this;
		let form = wnd.formPanel.getForm();
		if (!form.isValid()) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return;
		}

		let params = {};
		if (arguments && arguments[0] === true) {
			params.deleteChild = 2;
		}
		params.permanenteDelete = wnd.formPanel.getForm().findField('permanenteDelete').getValue();
		// При необходимости можно будет передавать массив идентификаторов
		params.RoutingMap_List = Ext.util.JSON.encode([wnd.RoutingMap.get('RoutingMap_id')]);
		wnd.submit(params);
	},
	submit: function (params) {
		let wnd = this;
		Ext.Ajax.request({
			params: params,
			url: wnd.url,
			failure: function(response, options) {
				sw.swMsg.alert(lang['oshibka'], 'Не удалось удалить данные');
			},
			success: function(response, options) {
				let resp = Ext.util.JSON.decode(response.responseText);
				if (resp && !Ext.isEmpty(resp.isEmpty) && resp.isEmpty === false) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId) {
							if (buttonId == 'yes') wnd.doDelete(true);
						},
						icon: Ext.Msg.QUESTION,
						msg: 'У данной записи есть дочерние МО с причиной удаления «Окончание срока действия маршрутизации». Продолжить безвозвратное удаление?',
						title: 'Вопрос'
					});
					return;
				}
				wnd.parentWnd.LpuTree.root.reload();
				wnd.parentWnd.LpuGrid.reload();
				wnd.hide();
			}
		});
	},
	setRequiredArg: function (params) {
		let paramsIsEmpty = Ext.isEmpty(params);
		if (!paramsIsEmpty && !Ext.isEmpty(params.parentWnd)) {
			this.parentWnd = params.parentWnd;
		}
		if (!paramsIsEmpty && !Ext.isEmpty(params.RoutingMap)) {
			this.RoutingMap = params.RoutingMap;
		}

		if (Ext.isEmpty(this.parentWnd)
			|| Ext.isEmpty(this.RoutingMap)
		) return false;
		return true;
	}
});