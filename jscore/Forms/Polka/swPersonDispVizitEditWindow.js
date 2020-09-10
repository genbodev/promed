/**
* swPersonDispVizitEditWindow - окно редактирования Контроля посещений
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
sw.Promed.swPersonDispVizitEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PersonDispVizitEditWindow',
	layout: 'border',
	maximizable: false,
	width: 300,
	height: 170,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDispVizitEditWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispVizitEditWindow.js',
	show: function() {		
		sw.Promed.swPersonDispVizitEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('PersonDispVizitEditForm').getForm();
		base_form.reset();

		this.action = arguments[0]['action'] || 'add';
		this.PersonDispVizit_id = arguments[0]['PersonDispVizit_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;
		
		switch (this.action){
			case 'add':
				this.setTitle(lang['kontrol_posescheniy_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['kontrol_posescheniy_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['kontrol_posescheniy_prosmotr']);
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('PersonDispVizitEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('PersonDispVizitEditForm').getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispVizit',
				params: { PersonDispVizit_id: this.PersonDispVizit_id },
				success: function (form, action) {
					loadMask.hide();
					base_form.findField('PersonDispVizit_NextDate').focus(true, 250);
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
			base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
			base_form.findField('PersonDispVizit_NextDate').focus(true, 250);
		}		
		
		if (this.action=='view') {
			base_form.findField('PersonDispVizit_NextDate').disable();
			base_form.findField('PersonDispVizit_NextFactDate').disable();
			this.buttons[0].disable();
		} else {
			base_form.findField('PersonDispVizit_NextDate').enable();
			base_form.findField('PersonDispVizit_NextFactDate').enable();
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('PersonDispVizitEditForm');
		var base_form = form.getForm();
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispVizitEditForm'), { msg: "Подождите, идет сохранение..." });

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (
			Ext.isEmpty(base_form.findField('PersonDispVizit_NextDate').getValue()) &&
			Ext.isEmpty(base_form.findField('PersonDispVizit_NextFactDate').getValue())
		) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['odnovremenno_oba_polya_naznacheno_yavitsya_i_yavilsya_pustyimi_byit_ne_mogut'],
				title: ERR_INVFIELDS_TIT
			});
			return false;		
		}
		
		loadMask.show();
		base_form.submit({
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
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_primechaniya_proizoshla_oshibka']);
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'PersonDispVizitEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 120,
			items:
			[{
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
				fieldLabel: 'ДН на дому'
			}, {
				fieldLabel: langs('Назначено явиться'),
				allowBlank: !getRegionNick().inlist(['perm', 'vologda']),
				width: 100,
				name: 'PersonDispVizit_NextDate',
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['yavilsya'],
				width: 100,
				name: 'PersonDispVizit_NextFactDate',
				xtype: 'swdatefield'
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'PersonDispVizit_id' },
				{ name: 'PersonDisp_id' },
				{ name: 'PersonDispVizit_IsHomeDN' },
				{ name: 'PersonDispVizit_NextDate' },
				{ name: 'PersonDispVizit_NextFactDate' }
			]
			),
			url: '/?c=PersonDisp&m=savePersonDispVizit'
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
		sw.Promed.swPersonDispVizitEditWindow.superclass.initComponent.apply(this, arguments);
	}
});