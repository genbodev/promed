/**
 * swEvnLabRequestSelectWindow - форма выбора заявки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      02.10.2013
 */

sw.Promed.swEvnLabRequestSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 300,
	modal: true,
	title: 'Выбор заявки',
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},
	show: function() {
		sw.Promed.swEvnLabRequestSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		if ( !arguments[0] || !arguments[0].Person_id || !arguments[0].MedService_id ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.onNewEvnLabRequest = Ext.emptyFn;
		if (arguments[0].onNewEvnLabRequest) {
			this.onNewEvnLabRequest = arguments[0].onNewEvnLabRequest;
		}

		this.Person_id = null;
		if (arguments[0].Person_id) {
			this.Person_id = arguments[0].Person_id;
		}

		this.MedService_id = null;
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}

		this.ARMType = null;
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		win.getLoadMask('Поиск новых заявок на пациента').show();
		this.EvnLabRequestGrid.removeAll();
		this.EvnLabRequestGrid.getGrid().getStore().load({
			params: {
				Person_id: win.Person_id,
				MedService_id: win.MedService_id
			},
			callback: function() {
				win.getLoadMask().hide();

				if (win.EvnLabRequestGrid.getGrid().getStore().getCount() == 0) {
					win.onNewEvnLabRequest();
					win.hide();
				}
			}
		});

		this.center();
	},

	callback: Ext.emptyFn,

	OnEvnLabRequestSelect: function() {
		var record = this.EvnLabRequestGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('EvnDirection_id'))) {
			return false;
		}

		var params = new Object();
		params.action = 'edit';
		params.ARMType = this.ARMType;
		params.MedService_id = this.MedService_id;
		params.Person_ShortFio = record.get('Person_ShortFio');
		params.EvnDirection_id = record.get('EvnDirection_id');
		params.Person_id = record.get('Person_id');
		params.PersonEvn_id = record.get('PersonEvn_id');
		params.Server_id = record.get('Server_id');
		params.OuterKzDirection = record.get('OuterKzDirection');

		getWnd('swEvnLabRequestEditWindow').show(params);

		this.hide();
	},

	initComponent: function() {

		var win = this;

		this.EvnLabRequestGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			useEmptyRecord: false,
			onEnter: this.OnEvnLabRequestSelect.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'TimetableMedService_begTime', type: 'timedate', header: lang['zapis'], direction: 'ASC', width: 110},
				{name: 'EvnDirection_IsCito', headerAlign: 'left', align: 'center', header: 'Cito!', direction: 'DESC', type: 'string', width: 60},
				{name: 'EvnLabRequest_UslugaName', header: langs('Услуга (исследование)'), renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext.isEmpty(value) && value[0] == "[" && value[value.length-1] == "]") {
						// разджейсониваем
						var uslugas = Ext.util.JSON.decode(value);
						for(var k in uslugas) {
							if (uslugas[k].UslugaComplex_Name) {
								if (!Ext.isEmpty(result)) {
									result += '<br />';
								}
								result += uslugas[k].UslugaComplex_Name;
							}
						}

						return result;
					} else {
						return value;
					}
				}, width: 280, id: 'autoexpand'},
				{name: 'EvnDirection_Num', header: langs('№ напр.'), width: 55},
				{name: 'EvnDirection_setDate', sort: true, dateFormat: 'd.m.Y', type: 'date', header: langs('Дата напр.'), width: 80},
				{name: 'PrehospDirect_Name', header: langs('Кем направлен'), width: 100},
				{name: 'OuterKzDirection', hidden: true}
			],
			dataUrl: '/?c=EvnLabRequest&m=getNewEvnLabRequests'
		});

		this.EvnLabRequestGrid.getGrid().on('rowdblclick', this.OnEvnLabRequestSelect.createDelegate(this));

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.OnEvnLabRequestSelect.createDelegate(this)
			},{
				text: 'Новая заявка',
				iconCls: 'add16',
				handler: function() {
					win.onNewEvnLabRequest();
					win.hide();
				}
			},
				'-',
				{
					text: lang['zakryit'],
					iconCls: 'close16',
					handler: function(button, event) {
						win.onCancel();
						win.hide();
					}
				}],
			items: [this.EvnLabRequestGrid]

		});

		sw.Promed.swEvnLabRequestSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});