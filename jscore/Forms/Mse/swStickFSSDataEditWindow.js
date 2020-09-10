/**
 * swStickFSSDataEditWindow - окно редактировния запроса в ФСС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			18.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swStickFSSDataEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swStickFSSDataEditWindow',
	layout: 'form',
	autoHeight: true,
	width: 500,
	action: 'view',
	modal: true,
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}

		var params = {
			StickFSSData_Num: base_form.findField('StickFSSData_Num').getValue(),
			Lpu_OGRN: base_form.findField('Lpu_OGRN').getValue()
		};

		if (base_form.findField('Person_id').disabled) {
			params.Person_id = base_form.findField('Person_id').getValue();
		}

		if (base_form.findField('StickFSSData_StickNum').disabled) {
			params.StickFSSData_StickNum = base_form.findField('StickFSSData_StickNum').getValue();
		}


		if (win.ignoreCheckExist) {
			params.ignoreCheckExist = 1;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			params: params,
			failure: function() {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
			}.createDelegate(this),
			success: function(form, action) {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
				if (action.result.success) {
					if (action.result.warnExist) {
						sw.swMsg.alert('Внимание', action.result.warnExist, function() {
							this.callback(action.result);
							this.hide();
						}.createDelegate(this));
					} else {
						this.callback(action.result);
						this.hide();
					}
				}
			}.createDelegate(this)
		});

		return true;
	},

	getNewNum: function(options) {
		options = options || {};
		var cb = options.callback || Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		var params = {};

		if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			params.StickFSSData_id = base_form.findField('StickFSSData_id').getValue();
		}

		Ext.Ajax.request({
			params: params,
			url: '/?c=StickFSSData&m=getNewStickFSSDataNum',
			failure: function(){},
			success: function(response){
				var responseObj = Ext.util.JSON.decode(response.responseText);
				base_form.findField('StickFSSData_Num').setValue(responseObj.StickFSSData_Num);
				base_form.findField('Lpu_OGRN').setValue(responseObj.Lpu_OGRN);
				cb();
			}
		});
		return true;
	},

	show: function(){
		sw.Promed.swStickFSSDataEditWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		this.enableEdit(false);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			this.action = 'view';
		}

		this.Person_id = null;
		if (arguments[0].Person_id) {
			this.Person_id = arguments[0].Person_id;

			base_form.findField('Person_id').getStore().load({
				params: {
					Person_id: win.Person_id
				},
				callback: function() {
					base_form.findField('Person_id').setValue(win.Person_id);
				}
			});
		}

		if (arguments[0] && arguments[0].StickFSSData_id) {
			base_form.findField('StickFSSData_id').setValue(arguments[0].StickFSSData_id);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		if (arguments[0] && arguments[0].ignoreCheckExist) {
			this.ignoreCheckExist = arguments[0].ignoreCheckExist;
		} else {
			this.ignoreCheckExist = false;
		}

		this.getLoadMask(LOAD_WAIT).show();

		switch(this.action) {
			case 'add':
				this.setTitle('Запрос на получение данных ЭЛН: Добавление');

				this.getNewNum();
				this.enableEdit(true);

				if (arguments[0] && arguments[0].StickFSSData_StickNum) {
					base_form.findField('StickFSSData_StickNum').setValue(arguments[0].StickFSSData_StickNum);
					base_form.findField('StickFSSData_StickNum').setDisabled(true);
					base_form.findField('Person_id').setDisabled(true);
				}

				this.getLoadMask().hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Запрос на получение данных ЭЛН: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Запрос на получение данных ЭЛН: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {StickFSSData_id: base_form.findField('StickFSSData_id').getValue()},
					url: '/?c=StickFSSData&m=loadStickFSSDataForm',
					failure: function(){
						this.getLoadMask().hide();
					}.createDelegate(this),
					success: function() {
						this.getLoadMask().hide();
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		var win = this;
		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			labelWidth: 160,
			url: '/?c=StickFSSData&m=saveStickFSSData',
			timeout: 6000,
			items: [{
				xtype: 'hidden',
				name: 'StickFSSData_id'
			}, {
				allowBlank: false,
				allowDecimal: false,
				allowNegative: false,
				xtype: 'numberfield',
				disabled: true,
				name: 'StickFSSData_Num',
				fieldLabel: 'Номер запроса',
				width: 120
			}, {
				allowBlank: false,
				disabled: true,
				xtype: 'textfield',
				name: 'Lpu_OGRN',
				fieldLabel: 'ОГРН МО',
				width: 120
			}, {
				xtype: 'swpersoncombo',
				allowBlank: false,
				hiddenName: 'Person_id',
				fieldLabel: 'Пациент',
				onTrigger1Click: function () {
					if (this.disabled) return false;
					var combo = this;
					getWnd('swPersonSearchWindow').show({
						onSelect: function (personData) {
							if (personData.Person_id > 0) {
								combo.getStore().loadData([{
									Person_id: personData.Person_id,
									Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
								}]);
								combo.setValue(personData.Person_id);
								combo.collapse();
								combo.focus(true, 500);
								combo.fireEvent('change', combo);
							}
							getWnd('swPersonSearchWindow').hide();
						},
						onClose: function () {
							combo.focus(true, 500)
						}
					});
				},
				anchor: '100%'
			}, {
				allowBlank: false,
				allowDecimal: false,
				allowNegative: false,
				maxLength: 12,
				minLength: 12,
				xtype: 'numberfield',
				name: 'StickFSSData_StickNum',
				fieldLabel: 'Номер ЭЛН',
				width: 120
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{ name: 'StickFSSData_id' },
				{ name: 'StickFSSData_Num' },
				{ name: 'Lpu_OGRN' },
				{ name: 'Person_id' },
				{ name: 'StickFSSData_StickNum' }
			])
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swStickFSSDataEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
