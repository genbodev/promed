/**
 * swRzhdRegistryViewWindow - окно просмотра/добавления/редактирования записей в регистр ржд
 * @author       Salavat Magafurov
 * @version      12.2017
 * Форма должна сохраняться только при изменении,
 * т.к. регистр сохраняет все изменения в историю в r2.RzhdRegistryHistory
 */

sw.Promed.swRzhdRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRzhdRegistryViewWindow',
	objectName: 'swRzhdRegistryViewWindow',
	objectSrc: '/jscore/Forms/Admin/swRzhdRegistryViewWindow.js',
	layout: 'border',
	title: 'Просмотр',
	closeAction: 'hide',
	modal: true,
	closable: true,
	resizable: false,
	callback: Ext.emptyFn,

	loadEditForm: function(id) {
		this.MainForm.getForm().load({ params: {'RzhdRegistry_id': id }});
	},

	doSave: function() {
		var wnd = this;
		var base_form = wnd.MainForm.getForm();
		var params = wnd.MainForm.getForm().getAllValues();
		params.Person_id = wnd.PersonInfoPanel.personId;
		if(!base_form.isValid()) {
			sw.swMsg.alert("Ошибка", "Некоторые поля заполнены неверно");
			return;
		}
		if(base_form.isDirty()) {
			Ext.Ajax.request({
				params: params,
				url: '/?c=RzhdRegistry&m=doSave',
				callback: function(options, success, response) {
					if(!response.responseText || !success) {
						sw.swMsg.alert('Ошибка', 'Ошибка при сохранении');
						return;
					}
					var result = Ext.util.JSON.decode(response.responseText);
					if(result.Error_Msg) {
						sw.swMsg.alert('Ошибка', result.Error_Msg);
						return;
					}
					if(result.RzhdRegistry_id) {
						if(wnd.callback)
							wnd.callback(result);
						wnd.hide();
					}
				}
			});
		} else {
			wnd.hide();
		}
	},

	show: function () {
		sw.Promed.swRzhdRegistryViewWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		var params = arguments[0];
		var base_form = wnd.MainForm.getForm();

		wnd.getLoadMask('Загрузка').show();

		if(params.callback) {
			wnd.callback = params.callback;
			delete params.callback;
		}

		//нельзя использовать reset, тк используется (trackResetOnLoad: true) - форма сбрасывается к предыдущим значениям
		wnd.MainForm.getForm().setValues({
			RzhdRegistry_id: null,
			RzhdWorkerCategory_id: null,
			RzhdWorkerGroup_id: null,
			RzhdWorkerSubgroup_id: null,
			RzhdOrg_id: null,
			RzhdRegistry_PensionBegDate: null,
			Register_setDate: null,
			Register_disDate: null,
			RegisterDisCause_id: null
		});

		if(params.RzhdRegistry_id) {
			wnd.loadEditForm(params.RzhdRegistry_id);
		}

		if(params.msg)
			sw.swMsg.alert('Сообщение', params.msg);

		base_form.setValues(params);
		if(params.Action == 'edit') {
			wnd.setTitle('Редактирование записи в регистре РЖД');
			wnd.buttons[0].show();
			wnd.enableEdit(true);
		} else {
			wnd.setTitle('Просмотр записи в регистре РЖД');
			wnd.buttons[0].hide();
			wnd.enableEdit(false);
		}

		wnd.PersonInfoPanel.load({ 
			Person_id: params.Person_id,
			callback: function() {
				var personInfo = wnd.PersonInfoPanel.DataView.getStore().getAt(0);
				if(personInfo && personInfo.get('Person_Birthday'))
					base_form.findField('RzhdRegistry_PensionBegDate').setMinValue(personInfo.get('Person_Birthday'));
				wnd.getLoadMask().hide();
				wnd.PersonInfoPanel.setPersonTitle();
			} 
		});
	},

	initComponent: function() {

		var wnd = this;

		/* FORM */

		wnd.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			region: 'north',
			title: '<div>Загрузка...</div>'
		});

		wnd.MainForm = new Ext.form.FormPanel({
			region: 'center',
			trackResetOnLoad: true,
			bodyStyle: 'padding: 10px;',
			labelWidth: 200,
			title: 'Данные регистра',
			items: [
				{
					xtype: 'hidden',
					name: 'Register_id'
				},
				new Ext.form.Hidden({
					name: 'RzhdRegistry_id'
				}),
				new sw.Promed.SwRzhdWorkerCategoryCombo({
					hiddenName: 'RzhdWorkerCategory_id',
					anchor: '100%',
					allowBlank: false,
					listeners:{
						'select': function() {
							var PersonGroupCombo = wnd.MainForm.getForm().findField('RzhdWorkerGroup_id');
							var PersonSubGroupCombo = wnd.MainForm.getForm().findField('RzhdWorkerSubgroup_id');
							PersonGroupCombo.clearValue();
							PersonSubGroupCombo.clearValue();
						},
						'valid': function() {
							if(this.disabled) return;
	
							var categoryCombo = this;
							var PersonGroupCombo = wnd.MainForm.getForm().findField('RzhdWorkerGroup_id');
							var PersonSubGroupCombo = wnd.MainForm.getForm().findField('RzhdWorkerSubgroup_id');
	
							var isFirstCategory = this.getValue() == 2;
							PersonGroupCombo.setAllowBlank(!isFirstCategory);
							PersonGroupCombo.setDisabled(!isFirstCategory);
							PersonSubGroupCombo.setAllowBlank(!isFirstCategory);
							PersonSubGroupCombo.setDisabled(!isFirstCategory);
							PersonGroupCombo.getStore().filterBy(function(record){
								return record.get('RzhdWorkerCategory_id') == categoryCombo.hiddenField.value;
							});
						}
					}
				}),
				new sw.Promed.SwRzhdWorkerGroupCombo({
					hiddenName:'RzhdWorkerGroup_id',
					allowBlank: false,
					anchor: '100%',
					listeners: {
						'select': function(){
							wnd.MainForm.getForm().findField('RzhdWorkerSubgroup_id').clearValue();
						},
						'valid': function(){
							var value = this.hiddenField.value;
							var SubgroupCombo =  wnd.MainForm.getForm().findField('RzhdWorkerSubgroup_id');
							SubgroupCombo.getStore().filterBy(function(record){
								return record.get('RzhdWorkerGroup_id') == value;
							});
	
						},
						'beforequery': function(queryEvent) {
							queryEvent.combo.onLoad();
							return false; 
						}
					}
				}),
				new sw.Promed.SwRzhdWorkerSubgroupCombo({
					hiddenName:'RzhdWorkerSubgroup_id',
					allowBlank: false,
					anchor: '100%',
					listeners:{
						'beforequery': function(queryEvent) {
							queryEvent.combo.onLoad();
							return false; 
						}
					}
				}),
				new sw.Promed.SwRzhdOrgCombo({
					hiddenName: 'RzhdOrg_id',
					editable: false
				}),
				new sw.Promed.SwDateField({
					name: 'RzhdRegistry_PensionBegDate',
					fieldLabel: 'Дата начала пенсии',
					maxValue: getGlobalOptions().date
				}),
	
				new sw.Promed.SwDateField({
					name: 'Register_setDate',
					fieldLabel: 'Дата включения в регистр',
					disabled: true
				}),
	
				new sw.Promed.SwDateField({
					name: 'Register_disDate',
					fieldLabel: 'Дата исключения из регистра',
					disabled: true
				}),
	
				new sw.Promed.SwRegisterDisCauseCombo({
					hiddenName: 'RegisterDisCause_id',
					fieldLabel: 'Причина исключения',
					RegisterType_Code: 'RZHD',
					disabled: true
				})
			],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						alert('success');
					}
				},
				[
					{ name: 'Register_id' },
					{ name: 'RzhdRegistry_id' },
					{ name: 'RzhdWorkerCategory_id' },
					{ name: 'RzhdWorkerGroup_id' },
					{ name: 'RzhdWorkerSubgroup_id' },
					{ name: 'RzhdOrg_id' },
					{ name: 'RzhdRegistry_PensionBegDate' }
				]
				),
				url: '/?c=RzhdRegistry&m=loadEditForm',
				listeners: {
					'beforeaction': function(form, action) {
						if(action.type=='load') {
							wnd.getLoadMask('Загрузка...').show();
						}
					},
					'actioncomplete': function(form, action) {
						if(action.type=='load') {
							wnd.getLoadMask().hide();
							var result = Ext.util.JSON.decode(action.response.responseText)[0];
							var base_form = wnd.MainForm.getForm();
							base_form.findField('RzhdWorkerCategory_id').validate();
							base_form.findField('RzhdOrg_id').setRawValue(result.RzhdOrg_Nick);
						}
					}
				}
		});

		Ext.apply(wnd,{
			items: [ wnd.PersonInfoPanel, wnd.MainForm ],
			buttons: [
				new Ext.Button({
					iconCls: 'save16',
					text: 'Сохранить',
					handler: function() {
						swRzhdRegistryViewWindow.doSave()
					}
				}),
				'-',
				HelpButton(this, -1),
				new Ext.Button({
					iconCls: 'close16',
					text: 'Закрыть',
					handler: function() {
						swRzhdRegistryViewWindow.hide();
					}
				})
			]
		});
		sw.Promed.swRzhdRegistryViewWindow.superclass.initComponent.apply(wnd, arguments);
	}
});