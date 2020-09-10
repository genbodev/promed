/**
 * swMorbusGeriatricsEditWindow - Форма просмотра/редактирования записи регистра по гериатрии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Morbus
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Stanislav Bykov
 * @version      12.2018
 */

sw.Promed.swMorbusGeriatricsEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	MorbusType_SysNick: 'geriatrics',
	title: langs('Запись регистра по гериатрии'),
	width: 750,

	/* методы */
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		params.AgeNotHindrance_id = base_form.findField('AgeNotHindrance_id').getValue();

		base_form.submit({
			failure: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.Error_Code ) {
					sw.swMsg.alert(langs('Ошибка #') + action.result.Error_Code, action.result.Error_Msg);
				}
			},
			params: params,
			success: function(result_form, action) {
				win.formStatus = 'edit';
				loadMask.hide();

				if ( !action.result ) {
					return false;
				}
				else if ( action.result.success ) {
					base_form.findField('MorbusGeriatrics_id').setValue(action.result.MorbusGeriatrics_id);

					var data = base_form.getValues();
					win.callback(data);

					if ( typeof options.callback == 'function' ) {
						options.callback();
					}
					else {
						win.hide();
					}
				}
			}
		});
	},
	openAnket: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		var
			base_form = this.FormPanel.getForm();
			params = new Object(),
			win = this;

		if ( Ext.isEmpty(base_form.findField('MorbusGeriatrics_id').getValue()) ) {
			win.doSave({
				callback: function() {
					win.openAnket();
				}
			});
			return false;
		}

		params.action = 'add';
		params.callback = function(anketForm, anketId, additionalParams) {
			if ( !Ext.isEmpty(additionalParams.AgeNotHindrance_id) ) {
				base_form.findField('AgeNotHindrance_id').setValue(additionalParams.AgeNotHindrance_id);
			}
		};
		params.MorbusGeriatrics_id = base_form.findField('MorbusGeriatrics_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.ReportType = 'geriatrics';

		getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
	},
	setFieldsDisabled: function(d) {
		var base_form = this.FormPanel.getForm();
		
		base_form.items.each(function(f) {
			if ( f && (f.xtype != 'hidden') && (f.xtype != 'fieldset')  && (f.changeDisabled !== false) ) {
				f.setDisabled(d);
			}
		});

		if ( d == true ) {
			this.buttons[0].hide();
		}
		else {
			this.buttons[0].show();
		}
	},
	show: function() {
		sw.Promed.swMorbusGeriatricsEditWindow.superclass.show.apply(this, arguments);

		var me = this;

		if ( !arguments[0] || !arguments[0].Person_id && !arguments[0].MorbusGeriatrics_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					me.hide();
				}
			});
			return false;
		}

		this.action = arguments[0].action || 'add';
		this.callback = typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = typeof arguments[0].onHide == 'function' ? arguments[0].onHide : Ext.emptyFn;
	
		this.center();

		var base_form = me.FormPanel.getForm();
		base_form.reset();

		arguments[0].Lpu_iid = getGlobalOptions().lpu_id;

		base_form.findField('MorbusType_SysNick').setValue(this.MorbusType_SysNick);

		var loadMask = new Ext.LoadMask(me.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		if ( this.action == 'add' ) {
			me.setTitle(langs('Запись регистра по гериатрии: Добавление'));

			me.setFieldsDisabled(false);
			base_form.findField('PersonRegister_setDate').disable();
			base_form.findField('AgeNotHindrance_id').disable();

			base_form.setValues(arguments[0]);
			
			me.InformationPanel.load({
				Person_id: base_form.findField('Person_id').getValue()
			});

			me.syncShadow();

			loadMask.hide();
		} 
		else {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					me.getLoadMask().hide();
				},
				params: {
					MorbusGeriatrics_id: arguments[0].MorbusGeriatrics_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( !result[0] ) {
						return false;
					}

					base_form.setValues(result[0]);

					me.InformationPanel.load({
						Person_id: base_form.findField('Person_id').getValue()
					});

					switch ( me.action ) {
						case 'edit':
							me.setTitle(langs('Запись регистра по гериатрии: Редактирование'));
							me.setFieldsDisabled(false);
							base_form.findField('PersonRegister_setDate').disable();
							base_form.findField('AgeNotHindrance_id').disable();
							break;

						case 'view':				
							me.setTitle(langs('Запись регистра по гериатрии: Просмотр'));
							me.setFieldsDisabled(true);
							break;
					}

					me.syncShadow();

					me.getLoadMask().hide();
				},
				url: '/?c=MorbusGeriatrics&m=load'
			});
		}
	},

	/* конструктор */
	initComponent: function() {
		var win = this;
		
		this.InformationPanel = new sw.Promed.PersonInformationPanel({
			region: 'north'
		});

		this.FormPanel = new Ext.form.FormPanel({	
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			frame: true,
			labelAlign: 'right',
			labelWidth: 260,
			layout: 'form',
			region: 'center',
			url:'/?c=MorbusGeriatrics&m=save',

			items: [{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'MorbusGeriatrics_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'Diag_id',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					allowBlank: false,
					disabled: true,
					fieldLabel: langs('Дата включения в регистр'),
					name: 'PersonRegister_setDate',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							comboSubject: 'AgeNotHindrance',
							disabled: true,
							fieldLabel: langs('Градация пациента по скринингу'),
							hiddenName: 'AgeNotHindrance_id',
							width: 200,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							handler: function() {
								win.openAnket();
							},
							text: langs('Заполнить анкету «Возраст не помеха»'),
							xtype: 'button'
						}]
					}]
				}, {
					fieldLabel: langs('Заполнена Карта комплексной гериатрической оценки (КГО)'),
					hiddenName: 'MorbusGeriatrics_IsKGO',
					width: 105,
					xtype: 'swyesnocombo'
				}, {
					fieldLabel: langs('Колясочник'),
					hiddenName: 'MorbusGeriatrics_IsWheelChair',
					width: 105,
					xtype: 'swyesnocombo'
				}, {
					autoHeight: true,
					defaults: {
						width: 105
					},
					labelWidth: 260,
					layout: 'form',
					style: 'padding-left: 0px;',
					title: langs('Наличие основных синдромов'),
					xtype: 'fieldset',
					items: [{
						fieldLabel: langs('Падения'),
						hiddenName: 'MorbusGeriatrics_IsFallDown',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Снижение веса'),
						hiddenName: 'MorbusGeriatrics_IsWeightDecrease',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Снижение функциональной активности'),
						hiddenName: 'MorbusGeriatrics_IsCapacityDecrease',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Когнитивные нарушения'),
						hiddenName: 'MorbusGeriatrics_IsCognitiveDefect',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Депрессии'),
						hiddenName: 'MorbusGeriatrics_IsMelancholia',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Недержание мочи'),
						hiddenName: 'MorbusGeriatrics_IsEnuresis',
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Полипрагмазия'),
						hiddenName: 'MorbusGeriatrics_IsPolyPragmasy',
						xtype: 'swyesnocombo'
					}]
				}]
			}]
		});

		Ext.apply(this, {	
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.InformationPanel,
				win.FormPanel
			]
		});

		sw.Promed.swMorbusGeriatricsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});