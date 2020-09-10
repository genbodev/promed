Ext6.define('common.Stick.StickFSSDataEditWindow', {
	alias: 'widget.StickFSSDataEditWindow',
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	
	alias: 'widget.itest',
	title: 'Тест',
	extend: 'base.BaseForm',
	maximized: false,
	width: 548,
	height: 235,
	modal: true,
	
	findWindow: false,
	closable: true,
	cls: 'arm-window-new emk-forms-window privilege-window',
	renderTo: Ext6.getBody(),
	layout: 'form',
	title: 'Запрос на получение ЭЛН',
	plain: true,
	resizable: false,
	enableEdit: function(enable) {
		var fields = this.query("field,label");
        for(i=0; i < fields.length; i++)
        {
            el = fields[i];
            if(enable) {
				if(typeof el.enable == 'function') el.enable()
				else if(el.handler) el.show();
				
			} else {
				if(! this.notdisable) {
					if(typeof el.disable == 'function') el.disable()
					else if(el.handler) el.hide();
				}
			}
			
        }
	},
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext6.Msg.WARNING,
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
			params.Person_id = win.Person_id //base_form.findField('Person_id').getValue();
		}

		if (win.ignoreCheckExist) {
			params.ignoreCheckExist = 1;
		}

		win.mask(LOAD_WAIT_SAVE);
		base_form.submit({
			params: params,
			failure: function() {
				win.unmask();
				this.formStatus = 'edit';
			}.createDelegate(this),
			success: function(form, action) {
				win.unmask();
				this.formStatus = 'edit';
				if (action.result.success) {
					if (action.result.warnExist) {
						Ext6.Msg.alert('Внимание', /*action.result.warnExist*/ null , function() {
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
		var cb = options.callback || Ext6.emptyFn;

		var base_form = this.FormPanel.getForm();
		var params = {};

		if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			params.StickFSSData_id = base_form.findField('StickFSSData_id').getValue();
		}

		Ext6.Ajax.request({
			params: params,
			url: '/?c=StickFSSData&m=getNewStickFSSDataNum',
			failure: function(){},
			success: function(response){
				var responseObj = Ext6.util.JSON.decode(response.responseText);
				base_form.findField('StickFSSData_Num').setValue(responseObj.StickFSSData_Num);
				base_form.findField('Lpu_OGRN').setValue(responseObj.Lpu_OGRN);
				cb();
			}
		});
		return true;
	},
	nameNormalize: function(name) {
		return name.slice(0,1).toUpperCase()+name.slice(1).toLowerCase();
	},
	show: function(){
		this.callParent(arguments);

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
		win.Fio = '';
		if(arguments[0].Person_Surname) win.Fio += win.nameNormalize(arguments[0].Person_Surname);
		if(arguments[0].Person_Surname) win.Fio += ' '+win.nameNormalize(arguments[0].Person_Firname);
		if(arguments[0].Person_Surname) win.Fio += ' '+win.nameNormalize(arguments[0].Person_Secname);

		this.Person_id = null;
		if (arguments[0].Person_id) {
			this.Person_id = arguments[0].Person_id;

			base_form.findField('Person_Name').setValue(win.Fio);
			base_form.findField('Person_id').setValue(win.Person_id);
		}

		if (arguments[0] && arguments[0].StickFSSData_id) {
			base_form.findField('StickFSSData_id').setValue(arguments[0].StickFSSData_id);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext6.emptyFn;
		}

		if (arguments[0] && arguments[0].ignoreCheckExist) {
			this.ignoreCheckExist = arguments[0].ignoreCheckExist;
		} else {
			this.ignoreCheckExist = false;
		}

		this.mask(LOAD_WAIT);

		switch(this.action) {
			case 'add':
				this.getNewNum();
				this.enableEdit(true);
				this.unmask();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.enableEdit(true);
				} else {
					this.enableEdit(false);
				}

				base_form.load({
					params: {StickFSSData_id: base_form.findField('StickFSSData_id').getValue()},
					url: '/?c=StickFSSData&m=loadStickFSSDataForm',
					failure: function(){
						this.unmask();
					}.createDelegate(this),
					success: function() {
						this.unmask();
					}.createDelegate(this)
				});
				break;
		}
	},
	initComponent: function() {
		var win = this;
		win.FormPanel = new Ext6.form.FormPanel({
			border: false,
			bodyPadding: 30,
			autoHeight: true,
			url: '/?c=StickFSSData&m=saveStickFSSData',
			timeout: 6000,
			defaults: {
				labelWidth: 124,
				width: 452
			},
			items: [{
				xtype: 'hidden',
				name: 'StickFSSData_id'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				layout: 'column',
				border: false,
				width: 460,
				padding: '0 0 5 0',
				items: [{
						allowBlank: false,
						allowDecimal: false,
						allowNegative: false,
						xtype: 'numberfield',
						disabled: true,
						name: 'StickFSSData_Num',
						fieldLabel: 'Номер запроса',
						width: 218,
						labelWidth: 124,
						hideTrigger: true
					}, {
						allowBlank: false,
						disabled: true,
						xtype: 'textfield',
						name: 'Lpu_OGRN',
						fieldLabel: 'ОГРН МО',
						labelAlign: 'right',
						width: 234,
						labelWidth: 87
					}]
			}, {
				xtype: 'textfield',
				allowBlank: false,
				name: 'Person_Name',
				fieldLabel: 'Пациент',
				hideTrigger: true,
				editable: false
			}, {
				allowBlank: false,
				allowDecimal: false,
				allowNegative: false,
				maxLength: 12,
				minLength: 12,
				xtype: 'numberfield',
				name: 'StickFSSData_StickNum',
				fieldLabel: 'Номер ЭЛН',
				hideTrigger: true
			}],			
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'StickFSSData_id' },
						{ name: 'StickFSSData_Num' },
						{ name: 'Lpu_OGRN' },
						{ name: 'Person_id' },
						{ name: 'StickFSSData_StickNum' }
					]
				})
			})
		});
			
		Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			layout: 'border',
			border: false,
			buttons:
			[ '->'
			, {
				text: langs('Отмена'),
				userCls:'buttonPoupup buttonCancel',
				handler: function() {
					win.hide();
				}
			}, {
				text: langs('Применить'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					win.doSave();
				}
			}]
		});

		this.callParent(arguments);
	}
});