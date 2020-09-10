/**
 * Панель диагнозов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnPLDiagPanel', {
	extend: 'swPanel',
	title: 'ДИАГНОЗЫ',
	layout: 'border',
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.Person_id = params.Person_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	loaded: false,
	showDiagMenu: function(link) {
		var me = this;

		var tpl = new Ext6.XTemplate(
			'<div class="diag-list">',
			'<tpl for="items">',
			'<div class="diag-list-first">{Diag_Name}</div>',
			'<div class="diag-list-second">{EvnVizitPL_setDate} {Person_Fin} {Lpu_Nick}</div>',
			'</tpl>',
			'</div>'
		);

		var html = tpl.apply(me.DiagData);

		var menu = Ext6.create('Ext6.menu.Menu', {
			items: [{
				html: html,
				xtype: 'label'
			}]
		});

		menu.showBy(link);
	},
	load: function() {
		var me = this;
		me.loaded = true;
		me.mask('Загрузка...');

		var base_form = this.formPanel.getForm();

		base_form.reset();
		this.diagSopMultiPanel.removeAllItems();
		base_form.findField('Diag_Name').setFieldLabel('Основной диагноз');
		me.DiagData = {};
		base_form.load({
			params: {
				EvnPL_id: me.Evn_id
			},
			success: function (form, action) {
				// good
				me.unmask();
				if (action.response && action.response.responseText) {
					var data = Ext6.JSON.decode(action.response.responseText);
					if (data[0] && data[0].DiagSop) {
						me.diagSopMultiPanel.setItems(data[0].DiagSop);
					}
					if (data[0] && data[0].Diag && data[0].Diag.length > 0) {
						me.DiagData = {
							items: data[0].Diag
						};
						base_form.findField('Diag_Name').setFieldLabel('Основной диагноз <a href="#" data-qtip="История установки диагноза" onClick="Ext6.getCmp(\'' + me.id + '\').showDiagMenu(this);" class="history-link"></a>');
					}
				}
			},
			failure: function (form, action) {
				// not good
			}
		});
	},
	addSopDiag: function() {
		var me = this;

		var params = {
			action: 'add',
			formParams: {
				Person_id: me.Person_id,
				PersonEvn_id: me.PersonEvn_id,
				Server_id: me.Server_id,
				EvnDiagPL_id: 0
			},
			EvnPL_id: me.Evn_id,
			onHide: Ext6.emptyFn,
			callback: function(data) {
				if (!data || !data.evnDiagPLData || !data.evnDiagPLData[0] || !data.evnDiagPLData[0].EvnDiagPL_id) {
					return false;
				}
				me.load();
				if (me.ownerPanel && me.ownerPanel.reloadListMorbus) {
					me.ownerPanel.reloadListMorbus();
				}
				return true;
			}
		};

		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			params.Person_Surname = piPanel.getFieldValue('Person_Surname');
			params.Person_Firname = piPanel.getFieldValue('Person_Firname');
			params.Person_Secname = piPanel.getFieldValue('Person_Secname');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		getWnd('swEvnDiagPLEditWindow').show(params);
	},
	deleteSopDiag: function(EvnDiag_id) {
		var me = this;
		checkDeleteRecord({
			callback: function () {
				me.mask('Удаление сопутствующего диагноза...');
				Ext6.Ajax.request({
					url: '/?c=EvnPL&m=deleteEvnDiagPL',
					params: {
						EvnDiagPL_id: EvnDiag_id
					},
					callback: function () {
						me.unmask();
						me.load();
						if (me.ownerPanel && me.ownerPanel.reloadListMorbus) {
							me.ownerPanel.reloadListMorbus();
						}
					}
				})
			}
		}, 'сопутствующий диагноз');
	},
	initComponent: function() {
		var me = this;

		this.diagSopMultiPanel = Ext6.create('Ext6.Panel', {
			border: false,
			itemsCount: 0,
			margin: 0,
			padding: 0,
			setItems: function(EvnDiagArray) {
				var panel = this;
				panel.removeAllItems();
				for (var k in EvnDiagArray) {
					if (EvnDiagArray[k].EvnDiagPLSop_id) {
						panel.addItem(EvnDiagArray[k].EvnDiagPLSop_id, EvnDiagArray[k].Diag_Name);
					}
				}

				panel.addButton();
			},
			addItem: function(EvnDiag_id, Diag_Name) {
				var panel = this;
				panel.itemsCount++;
				var diagSopField = Ext6.create('Ext6.form.field.Display', {
					fieldLabel: 'Сопутствующий диагноз',
					hideLabel: panel.itemsCount > 1,
					labelWidth: 270,
					anchor: '90%',
					margin: panel.itemsCount > 1 ? "0px 0px 2px 275px" : "0px 0px 2px 0px",
					value: Diag_Name + ' <a href="#" onClick="Ext6.getCmp(\'' + me.id + '\').deleteSopDiag(' + EvnDiag_id + ');" class="red-link">Удалить</a>'
				});

				panel.add(diagSopField);
			},
			addButton: function() {
				var panel = this;
				panel.itemsCount++;
				var diagSopField = Ext6.create('Ext6.form.field.Display', {
					fieldLabel: 'Сопутствующий диагноз',
					hideLabel: panel.itemsCount > 1,
					labelWidth: 270,
					anchor: '90%',
					margin: panel.itemsCount > 1 ? "0px 0px 2px 275px" : "0px 0px 2px 0px",
					value: '<a href="#" onClick="Ext6.getCmp(\'' + me.id + '\').addSopDiag();">Добавить</a>'
				});

				if(Ext6.isEmpty(me.editAvailable) || me.editAvailable)
					panel.add(diagSopField);
			},
			removeAllItems: function() {
				var panel = this;
				panel.itemsCount = 0;
				panel.removeAll();
			}
		});

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			defaults: {
				margin: "0px 0px 2px 0px",
				anchor: '90%',
				labelWidth: 270
			},
			region: 'center',
			url: '/?c=EvnPL&m=loadEvnPLDiagPanel',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'Diag_dName'},
						{name: 'Diag_fName'},
						{name: 'Diag_preName'},
						{name: 'Diag_Name'},
						{name: 'DiagSop_Name'},
						{name: 'Diag_lName'},
						{name: 'Diag_concName'}
					]
				})
			}),
			layout: 'anchor',
			bodyPadding: '24px 37px 20px 30px',
			cls: 'personPanel person-panel-emk-info',
			listeners: {
				'resize': function() {
					this.updateLayout();
				}
			},
			items: [{
				xtype: 'displayfield',
				fieldLabel: 'Диагноз направившего учреждения',
				name: 'Diag_dName'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Предварительный диагноз',
				name: 'Diag_fName'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Предварительная внешняя причина',
				name: 'Diag_preName'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Основной диагноз',
				name: 'Diag_Name'
			}, me.diagSopMultiPanel, {
				xtype: 'displayfield',
				fieldLabel: 'Заключительный диагноз',
				name: 'Diag_lName'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Заключительная внешняя причина',
				name: 'Diag_concName'
			}]
		});

		Ext6.apply(this, {
			items: [
				this.formPanel
			]
		});

		this.callParent(arguments);
	}
});