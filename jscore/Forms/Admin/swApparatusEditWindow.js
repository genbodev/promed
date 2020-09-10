/**
* swApparatusEditWindow - окно просмотра, добавления и редактирования аппаратов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.10.2013
*/

/*NO PARSE JSON*/
sw.Promed.swApparatusEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swApparatusEditWindow',
	objectSrc: '/jscore/Forms/Admin/swApparatusEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swApparatusEditWindow',
	width: 600,
	height: 215,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	doSave: function() {
		this.submit();
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				data.MedService_id = action.result.MedService_id;
				win.callback(win.owner,action.result.MedService_id);
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = [
				'MedService_Name'
				,'MedService_Nick'
				,'MedService_begDT'
				,'MedService_endDT'
			];

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			form.findField('MedService_Name').focus(true, 250);
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},
	allowedMedServiceTypes: [],
	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MedServiceRecordEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [{
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				name: 'MedService_Name',
				id: 'MSEW_MedService_Name',
				maxLength: 200,
				tabIndex: TABINDEX_AEW,
				xtype: 'textfield'
			}, {
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['kratkoe_naimenovanie'],
				name: 'MedService_Nick',
				maxLength: 50,
				tabIndex: TABINDEX_AEW + 3,
				triggerClass: 'x-form-equil-trigger',
				onTriggerClick: function() {
					var base_form = win.formPanel.getForm();
					if ( base_form.findField('MedService_Nick').disabled ) {						
						return false;
					}
					var fullname = base_form.findField('MedService_Name').getValue();
					base_form.findField('MedService_Nick').setValue(fullname);
				},
				xtype: 'trigger'
			}, {
				fieldLabel: lang['data_sozdaniya'],
				name: 'MedService_begDT',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_AEW + 7,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_zakryitiya'],
				name: 'MedService_endDT',
				allowBlank: true,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_AEW + 8,
				xtype: 'swdatefield'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_pid',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'MedService_id' },
				{ name: 'MedService_pid' },
				{ name: 'MedService_Name' },
				{ name: 'MedService_Nick' },
				{ name: 'MedService_begDT' },
				{ name: 'MedService_endDT' }
			]),
			timeout: 600,
			url: '/?c=MedService&m=saveApparatus'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_AEW + 11,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'MSEW_MedService_Name',
				tabIndex: TABINDEX_AEW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swApparatusEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swApparatusEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		
		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm();

		form.setValues(arguments[0]);
		if ( arguments[0].MedService_pid ) {
			form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}
		
		switch (this.action) {
			case 'view':
				this.setTitle(lang['apparat_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['apparat_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['apparat_dobavlenie']);
			break;

			default:
				log('swApparatusEditWindow - action invalid');
				return false;
			break;
		}

		if(this.action == 'add')
		{
			this.allowEdit(true);
			this.syncSize();
			this.doLayout();
		}
		else
		{
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					MedService_id: form.findField('MedService_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.allowEdit(true);
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=MedService&m=loadApparatusEditForm'
			});
		}
	}
});
