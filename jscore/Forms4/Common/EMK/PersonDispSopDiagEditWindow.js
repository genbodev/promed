/**
* Окно редактирования Сопутствующих диагнозов
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.PersonDispSopDiagEditWindow', {
	/* свойства */
	alias: 'widget.swPersonDispSopDiagEditWindowExt6',
	
	height: 195,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 588,
	cls: 'arm-window-new emk-forms-window person-disp-diag-edit-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext.emptyFn,
	modal: true,
	
	title: 'Сопутствующий диагноз',
	show: function() {
		var win = this;
		this.callParent(arguments);
		
		win.taskButton.hide();
		var base_form = win.MainPanel.getForm();
		base_form.reset();
		base_form.findField('DopDispDiagType_id').setAllowBlank(false);
		base_form.findField('DopDispDiagType_id').getStore().load();

		this.action = arguments[0]['action'] || 'add';
		this.PersonDispSopDiag_id = arguments[0]['PersonDispSopDiag_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;
		
		if (this.action != 'add') {
			var loadMask = new Ext6.LoadMask(win.MainPanel, { msg: "Подождите, идет сохранение..." });
			win.MainPanel.getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispSopDiag',
				params: { PersonDispSopDiag_id: win.PersonDispSopDiag_id },
				success: function (form, action) {
					loadMask.hide();	
					var diag_combo = base_form.findField('Diag_id');
					var diag_id = parseInt(diag_combo.getValue());
					diag_combo.setValue(diag_id);
					diag_combo.getStore().load({
						callback: function() {
							var indx = diag_combo.getStore().findBy(function(rec) { if(rec.data.Diag_id==diag_id) return rec;});
							if(indx>=0) {
								diag_combo.setValue(parseInt(diag_id));
								diag_combo.focus();
							}
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
						this.hide();
					}
				},
				scope: win
			});		
		} else {
			//~ base_form.findField('DopDispDiagType_id').setAllowBlank(true);
			base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
			base_form.findField('Diag_id').focus();
		}		
		
		if (this.action=='view') {
			base_form.findField('Diag_id').disable();
			base_form.findField('DopDispDiagType_id').disable();
			this.queryById('button_save').disable();
		} else {
			base_form.findField('Diag_id').enable();
			base_form.findField('DopDispDiagType_id').disable();
			this.queryById('button_save').enable();
		}
	},
	doSave: function() {
		var win = this;
		var form = win.MainPanel.getForm();
		var loadMask = new Ext6.LoadMask(win.MainPanel, { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
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
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении примечания произошла ошибка'));
				}
							
			}.createDelegate(this)
		});
	},
	initComponent: function() {
		var win = this;
		
		win.MainPanel =  new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			userCls: 'PersonDispSopDiag dispcard',
			region: 'center',
			border: false,
			items:[{
					name: 'PersonDispSopDiag_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonDisp_id',
					value: 0,
					xtype: 'hidden'
				}, {
					xtype: 'swDiagCombo',
					fieldLabel: 'Диагноз',
					allowBlank: false,
					name: 'Diag_id',
					itemId: 'Diag_id',
					valueField: 'Diag_id',
					userCls: 'diagnoz PersonDispPanel',
					width: 488,
					labelWidth: 155,
					listeners: {
						'change': function (combo, newValue, oldValue, eOpts) {
							//~ log('newValue = '+newValue+' ; oldValue = '+oldValue);
							var base_form = win.MainPanel.getForm();
							var Diag_Code = this.getFieldValue('Diag_Code');
							var DopDispDiagType = base_form.findField('DopDispDiagType_id');
							if (newValue && !Ext6.isEmpty(Diag_Code)) {
								//~ log('Diag_Code = '+Diag_Code);
								if (Diag_Code.substr(0,1) == 'Z') {
									DopDispDiagType.disable();
									DopDispDiagType.setAllowBlank(true);
									DopDispDiagType.clearValue();
								} else {
									DopDispDiagType.enable();
									DopDispDiagType.setAllowBlank(false);
									if(!DopDispDiagType.getValue()) {
										DopDispDiagType.setFieldValue('DopDispDiagType_Code', 1);
									}
								}					
							}
						}
					}
				}, {
					xtype: 'SwDopDispDiagTypeCombo',
					fieldLabel: 'Характер заболевания',
					name: 	'DopDispDiagType_id',
					itemId: 'DopDispDiagType_id',
					userCls: 'PersonDispPanel',
					width: 488,
					labelWidth: 155,
					queryMode: 'local',
					allowBlank: false
				}
			],			
			url: '/?c=PersonDisp&m=savePersonDispSopDiag',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{ name: 'PersonDispSopDiag_id' },
						{ name: 'PersonDisp_id' },
						{ name: 'Diag_id' },
						{ name: 'DopDispDiagType_id' }
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