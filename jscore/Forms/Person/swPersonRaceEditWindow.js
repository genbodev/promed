sw.Promed.swPersonRaceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonRaceEditWindow',

	width: 300,
	autoHeight: true,
	closeAction: 'hide',
	buttonAlign: 'left',
	modal: true,
	plain: true,
	resizable: false,
	layout: 'form',
	formMode: 'remote',
	formStatus: 'edit',
	action: null,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	formName: 'PersonRaceEditForm',
	params: {
		PersonRace_id: null,
		Person_id: null,
		RaceType_id: null,
		PersonRace_setDT: null,
		pmUser_id: null
	},
	hiddenFields: [
		'Person_id',
		'PersonRace_id',
		'PersonRace_setDT',
		'RaceType_id'
	],
	initComponent: function () {
		var scope = this;
		this.formPanel = new sw.Promed.FormPanel({
			id: 'PersonRaceEditForm',
			saveUrl: '/?c=PersonRace&m=doSave',
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				{
					xtype: 'textfield',
					name: 'PersonRace_id',
					hidden: true,
					hideLabel: true,
				}, {
					xtype: 'textfield',
					name: 'Person_id',
					hidden: true,
					hideLabel: true,
				}, {
					xtype: 'swdatefield',
					name: 'PersonRace_setDT',
					fieldLabel: 'Дата внесения',
					allowBlank: false,
					maxValue: getGlobalOptions().date,
					value: getGlobalOptions().date,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					width: 150
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Раса'),
					comboSubject: 'RaceType',
					hiddenName: 'RaceType_id',
					width: 150,
					allowBlank: false
				}
			]
		});

		this.items = [this.formPanel];
		sw.Promed.swPersonRaceEditWindow.superclass.initComponent
			.apply(this, arguments);

		this._form = this.formPanel.getForm();
	},
	show: function () {
		sw.Promed.swPersonRaceEditWindow.superclass.show.apply(this, arguments);
		this.center();
		this._form.reset();

		for(var i in this.params) {
			this.params[i] = null;
		}

		var errorList = this._setRequiredParams(arguments[0]);
		if (errorList.length) {
			sw.swMsg.alert(lang['oshibka'], errorList.join('<br/>'));
			this.hide();
			return false;
		}
		if (arguments[0].action) this.action = arguments[0].action;
		this._setTitle();
		this.setValueToHidden();
	},
	saveForm: function () {
		let form = this._form;
		if (!form.isValid()) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return;
		}
		var params = form.getValues();
		this.showLoadMask(langs('podojdite_idet_sohranenie'));
		this.submit(params);
	},
	submit: function (params) {
		var scope = this;
		Ext.Ajax.request({
			params: params,
			url: scope.formPanel.saveUrl,
			failure: function(response, options) {
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
						return;
					}
				}
				scope.hideLoadMask();
				sw.swMsg.alert(lang['oshibka'], 'Не удалось сохранить данные');
			},
			success: function(response, options) {
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer._id || answer.PersonRace_id) {
						var data = {
							personRaceData: {
								'PersonRace_id': answer._id || answer.PersonRace_id,
								'Person_id': scope.params.Person_id,
								'PersonRace_setDT': scope.params.PersonRace_setDT,
								'RaceType_id': scope._form.findField('RaceType_id').getValue()
							}
						};
						scope.callback(data);
					}
				}
				scope.hideLoadMask();
				scope.hide();
			}
		});
	},
	_setTitle: function () {
		var actionText = '';
		switch (this.action) {
			case 'add':
				actionText = 'Добавление';
				break;
			case 'edit':
				actionText = 'Редактирование';
				break;
			case 'view':
				actionText = 'Просмотр';
		}
		this.setTitle(['Раса', actionText].join(': '));
	},
	_setRequiredParams: function (params) {
		var paramsIsEmpty = Ext.isEmpty(params);
		var errorList = [];

		if (Ext.isEmpty(params.action) && !params.action.inlist(['add', 'edit', 'view'])) {
			errorList.push('Action mod имеет неверное значение');
		}
		if (!paramsIsEmpty && Ext.isEmpty(params.formParams)) {
			errorList.push('Не передан массив параметров для формы');
		} else {
			if (!Ext.isEmpty(params.formParams.Person_id)) {
				this.params.Person_id = params.formParams.Person_id;
			} else errorList.push('Идентификатор пациента не может быть пустым');
			if (!Ext.isEmpty(params.formParams.PersonRace_id)) {
				this.params.PersonRace_id = params.formParams.PersonRace_id;
			} else errorList.push('Идентификатор расы не может быть пустым');
			if (!Ext.isEmpty(params.formParams.PersonRace_setDT)) {
				this.params.PersonRace_setDT = params.formParams.PersonRace_setDT;
			}
			if (!Ext.isEmpty(params.formParams.PersonRace_id)) {
				this.params.PersonRace_id = params.formParams.PersonRace_id;
			}
			if (!Ext.isEmpty(params.formParams.RaceType_id)) {
				this.params.RaceType_id = params.formParams.RaceType_id;
			}
		}

		this.callback = params.callback ? params.callback : Ext.emptyFn;
		this.onHide = params.onHide ? params.onHide : Ext.emptyFn;

		this.params.pmUser_id = getGlobalOptions().pmuser_id;

		return errorList;
	}
});
