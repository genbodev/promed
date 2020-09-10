/**
* swPersonDispSopDiagEditWindow - окно редактирования Сопутствующих диагнозов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Aleksandr Chebukin 
* @version      20.02.2016
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDispSopDiagEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PersonDispSopDiagEditWindow',
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 150,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDispSopDiagEditWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispSopDiagEditWindow.js',
	show: function() {		
		sw.Promed.swPersonDispSopDiagEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('PersonDispSopDiagEditForm').getForm();
		base_form.reset();

		this.action = arguments[0]['action'] || 'add';
		this.PersonDispSopDiag_id = arguments[0]['PersonDispSopDiag_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;
		
		switch (this.action){
			case 'add':
				this.setTitle(lang['soputstvuyuschie_diagnozyi_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['soputstvuyuschie_diagnozyi_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['soputstvuyuschie_diagnozyi_prosmotr']);
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('PersonDispSopDiagEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('PersonDispSopDiagEditForm').getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispSopDiag',
				params: { PersonDispSopDiag_id: this.PersonDispSopDiag_id },
				success: function (form, action) {
					loadMask.hide();	
					var diag_combo = base_form.findField('Diag_id');
					var diag_id = diag_combo.getValue();
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if (record.data.Diag_id == diag_id) {
									diag_combo.setValue(record.data.Diag_id);
									diag_combo.fireEvent('select', diag_combo, record, 0);
									diag_combo.fireEvent('change', diag_combo, record.data.Diag_id, 0);
									diag_combo.focus();
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
					}
				},
				scope: this
			});		
		} else {
			base_form.findField('DopDispDiagType_id').setAllowBlank(true);
			base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
			base_form.findField('Diag_id').focus();
		}		
		
		if (this.action=='view') {
			base_form.findField('Diag_id').disable();
			base_form.findField('DopDispDiagType_id').disable();
			this.buttons[0].disable();
		} else {
			base_form.findField('Diag_id').enable();
			base_form.findField('DopDispDiagType_id').disable();
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('PersonDispSopDiagEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispSopDiagEditForm'), { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
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
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_primechaniya_proizoshla_oshibka']);
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'PersonDispSopDiagEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'PersonDispSopDiag_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonDisp_id',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'Diag_id',
				allowBlank: false,
				listWidth: 580,
				width: 400,
				xtype: 'swdiagcombo',
				onChange: function(combo, newValue, oldValue) {
					var base_form = win.MainPanel.getForm();
					var Diag_Code = this.getFieldValue('Diag_Code');
					var DopDispDiagType = base_form.findField('DopDispDiagType_id');
					if (newValue && !Ext.isEmpty(Diag_Code)) {
						if (Diag_Code.substr(0,1) == 'Z') {
							DopDispDiagType.disable();
							DopDispDiagType.setAllowBlank(true);
							DopDispDiagType.clearValue();
						} else {
							DopDispDiagType.enable();
							DopDispDiagType.setAllowBlank(false);
							DopDispDiagType.setFieldValue('DopDispDiagType_Code', 1);
						}					
					}
				}
			}, {
				fieldLabel: lang['harakter_zabolevaniya'],
				name: 'DopDispDiagType_id',
				width: 400,
				listWidth: 450,
				xtype: 'swdopdispdiagtypecombo'
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'PersonDispSopDiag_id' },
				{ name: 'PersonDisp_id' },
				{ name: 'Diag_id' },
				{ name: 'DopDispDiagType_id' }
			]
			),
			url: '/?c=PersonDisp&m=savePersonDispSopDiag'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDispSopDiagEditWindow.superclass.initComponent.apply(this, arguments);
	}
});