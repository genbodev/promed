/**
 * swLsLinkEditWindow - окно редактирования взаимодействия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			12.2019
 */

/*NO PARSE JSON*/

sw.Promed.swLsLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLsLinkEditWindow',
	width: 700,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var params = {};
		params.ACTMATTERS_G1ID = base_form.findField('LS_GROUP1').getFieldValue('ACTMATTERS_ID');
		params.TRADENAMES_G1ID = base_form.findField('LS_GROUP1').getFieldValue('TRADENAMES_ID');
		params.CLSPHARMAGROUP_G1ID = base_form.findField('LS_GROUP1').getFieldValue('CLSPHARMAGROUP_ID');
		params.FTGGRLS_G1ID = base_form.findField('LS_GROUP1').getFieldValue('FTGGRLS_ID');
		params.ACTMATTERS_G2ID = base_form.findField('LS_GROUP2').getFieldValue('ACTMATTERS_ID');
		params.TRADENAMES_G2ID = base_form.findField('LS_GROUP2').getFieldValue('TRADENAMES_ID');
		params.CLSPHARMAGROUP_G2ID = base_form.findField('LS_GROUP2').getFieldValue('CLSPHARMAGROUP_ID');
		params.FTGGRLS_G2ID = base_form.findField('LS_GROUP2').getFieldValue('FTGGRLS_ID');

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swLsLinkEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(langs('Взаимодейтсвие ЛС: Добавление'));
				loadMask.hide();
				break;

			case 'copy':
			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(langs('Взаимодейтсвие ЛС: Редактирование'));
				} else if (this.action == 'copy') {
					form.enableEdit(true);
					form.setTitle(langs('Взаимодейтсвие ЛС: Добавление'));
				} else {
					form.enableEdit(false);
					form.setTitle(langs('Взаимодейтсвие ЛС: Просмотр'));
				}

				base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						loadMask.hide();
						form.hide();
					},
					url: '/?c=LsLink&m=loadLsLinkEditForm',
					params: {
						LS_LINK_ID: base_form.findField('LS_LINK_ID').getValue()
					},
					success: function(result_form, action) {
						loadMask.hide();

						if (this.action == 'copy') {
							base_form.findField('LS_LINK_ID').setValue(null);
						}

						var LS_GROUP1 = base_form.findField('LS_GROUP1').getValue();
						if (!Ext.isEmpty(LS_GROUP1)) {
							base_form.findField('LS_GROUP1').getStore().baseParams.query ='';
							base_form.findField('LS_GROUP1').getStore().load({
								params: {
									LS_GROUP_ID: LS_GROUP1
								},
								callback: function() {
									base_form.findField('LS_GROUP1').setValue(LS_GROUP1);
								}
							});
						}

						var LS_GROUP2 = base_form.findField('LS_GROUP2').getValue();
						if (!Ext.isEmpty(LS_GROUP2)) {
							base_form.findField('LS_GROUP2').getStore().baseParams.query ='';
							base_form.findField('LS_GROUP2').getStore().load({
								params: {
									LS_GROUP_ID: LS_GROUP2
								},
								callback: function() {
									base_form.findField('LS_GROUP2').setValue(LS_GROUP2);
								}
							});
						}
					}.createDelegate(this)
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=LsLink&m=saveLsLink',
			labelWidth: 210,
			labelAlign: 'right',

			items: [{
				name: 'LS_LINK_ID',
				xtype: 'hidden'
			}, {
				name: 'PREP_ID',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				hiddenName: 'LS_GROUP1',
				fieldLabel: langs('Группа 1 ЛС'),
				xtype: 'swlsgroupcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				hiddenName: 'LS_GROUP2',
				fieldLabel: langs('Группа 2 ЛС'),
				xtype: 'swlsgroupcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				prefix: 'rls_',
				hiddenName: 'LS_FT_TYPE_ID',
				comboSubject: 'LS_FT_TYPE',
				fieldLabel: langs('Фармакологический тип'),
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				prefix: 'rls_',
				hiddenName: 'LS_INFLUENCE_TYPE_ID',
				comboSubject: 'LS_INFLUENCE_TYPE',
				fieldLabel: langs('Тип влияния'),
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				prefix: 'rls_',
				hiddenName: 'LS_EFFECT_ID',
				comboSubject: 'LS_EFFECT',
				fieldLabel: langs('Терапевтический эффект'),
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				prefix: 'rls_',
				hiddenName: 'LS_INTERACTION_CLASS_ID',
				comboSubject: 'LS_INTERACTION_CLASS',
				fieldLabel: langs('Класс взаимодействия'),
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				allowBlank: false,
				name: 'DESCRIPTION',
				fieldLabel: langs('Описание взаимодействия'),
				xtype: 'textarea',
				anchor: '100%'
			}, {
				allowBlank: false,
				name: 'RECOMMENDATION',
				fieldLabel: langs('Рекомендации'),
				xtype: 'textarea',
				anchor: '100%'
			}, {
				allowBlank: false,
				name: 'BREAKTIME',
				fieldLabel: langs('Перерыв между приемами ЛС из разных групп в одном курсе (часы)'),
				xtype: 'numberfield',
				allowNegative: false,
				allowDecimal: true,
				decimalPrecision: 1,
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'LS_LINK_ID'},
				{name: 'LS_GROUP1'},
				{name: 'LS_GROUP2'},
				{name: 'LS_FT_TYPE_ID'},
				{name: 'LS_INFLUENCE_TYPE_ID'},
				{name: 'LS_EFFECT_ID'},
				{name: 'LS_INTERACTION_CLASS_ID'},
				{name: 'DESCRIPTION'},
				{name: 'RECOMMENDATION'},
				{name: 'BREAKTIME'}
			])
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			}, {
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: lang['otmenit']
			}]
		});

		sw.Promed.swLsLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});