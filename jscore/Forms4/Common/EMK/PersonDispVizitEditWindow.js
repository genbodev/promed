
Ext6.define('common.EMK.PersonDispVizitEditWindow', {
	/* свойства */
	alias: 'widget.swPersonDispVizitEditWindowExt6',
	
	height: 195,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 450,
	cls: 'arm-window-new emk-forms-window person-disp-diag-edit-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	
	title: 'Посещение',
	show: function() {
		var win = this;
		this.callParent(arguments);
		
		win.taskButton.hide();
		var base_form = this.queryById('PersonDispVizitEditForm').getForm();
		base_form.reset();

		this.action = arguments[0]['action'] || 'add';
		this.PersonDispVizit_id = arguments[0]['PersonDispVizit_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext6.emptyFn;
		
		if (this.action != 'add') {
			var loadMask = new Ext6.LoadMask(win.queryById('PersonDispVizitEditForm'), { msg: "Подождите, идет сохранение..." });
			this.queryById('PersonDispVizitEditForm').getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispVizit',
				params: { PersonDispVizit_id: this.PersonDispVizit_id },
				success: function (form, action) {
					loadMask.hide();
					base_form.findField('PersonDispVizit_NextDate').focus(true, 250);
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
						this.hide();
					}
				},
				scope: this
			});		
		} else {
			base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
			base_form.findField('PersonDispVizit_NextDate').focus(true, 250);
		}		
		
		if (this.action=='view') {
			base_form.findField('PersonDispVizit_IsHomeDN').disable();
			base_form.findField('PersonDispVizit_NextDate').disable();
			base_form.findField('PersonDispVizit_NextFactDate').disable();
			this.queryById('button_save').disable();
		} else {
			base_form.findField('PersonDispVizit_IsHomeDN').enable();
			base_form.findField('PersonDispVizit_NextDate').enable();
			base_form.findField('PersonDispVizit_NextFactDate').enable();
			this.queryById('button_save').enable();
		}
	},
	doSave: function() 
	{
		var win = this;
		var form = this.queryById('PersonDispVizitEditForm').getForm();
		var loadMask = new Ext6.LoadMask(win.queryById('PersonDispVizitEditForm'), { msg: "Подождите, идет сохранение..." });

		if (
			Ext6.isEmpty(form.findField('PersonDispVizit_NextDate').getValue()) &&
			Ext6.isEmpty(form.findField('PersonDispVizit_NextFactDate').getValue())
		) {
			Ext6.Msg.show( {
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING,
				msg: langs('Одновременно оба поля "Назначено явиться" и "Явился" пустыми быть не могут'),
				title: ERR_INVFIELDS_TIT
			});
			return false;		
		}
		
		loadMask.show();		
		form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.returnFunc();
					}	
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении примечания произошла ошибка'));
				}
							
			}.createDelegate(this)
		});
	},
	initComponent: function() {
		var win = this;
		
		if(getGlobalOptions().client != 'ext2') this.addHelpButton = Ext6.emptyFn;
		
		win.MainPanel =  new Ext6.form.FormPanel({
			itemId: 'PersonDispVizitEditForm',
			bodyPadding: '10 25 25 30',
			userCls: 'PersonDispSopDiag dispcard',
			region: 'center',
			border: false,
			items:[{
				name: 'PersonDispVizit_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonDisp_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonDispVizit_IsHomeDN',
				checked: false,
				xtype: 'checkbox',
				width: 280,
				labelWidth: 155,
				fieldLabel: langs('ДН на дому')
			}, {
				fieldLabel: langs('Назначено явиться'),
				width: 280,
				labelWidth: 155,
				name: 'PersonDispVizit_NextDate',
				xtype: 'datefield',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				formatText: null,
				invalidText: 'Неправильная дата',
			}, {
				fieldLabel: langs('Явился'),
				width: 280,
				labelWidth: 155,
				name: 'PersonDispVizit_NextFactDate',
				xtype: 'datefield',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				formatText: null,
				invalidText: 'Неправильная дата',
			}
			],			
			url: '/?c=PersonDisp&m=savePersonDispVizit',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{ name: 'PersonDispVizit_id' },
						{ name: 'PersonDisp_id' },
						{ name: 'PersonDispVizit_IsHomeDN'},
						{ name: 'PersonDispVizit_NextDate' },
						{ name: 'PersonDispVizit_NextFactDate' }
					]
				})
			})
		});

		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: ['->',
			{
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler:function () {
					win.hide();
				}
			},
			{
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}
			]
		});

		this.callParent(arguments);
	}
});