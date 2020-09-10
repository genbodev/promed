/**
* swPersonMedHistoryEditWindow - окно просмотра, добавления и редактирования анамнеза жизни
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
*/

/*NO PARSE JSON*/
sw.Promed.swPersonMedHistoryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonMedHistoryEditWindow',
	objectSrc: '/jscore/Forms/Common/swPersonMedHistoryEditWindow.js',

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
	id: 'swPersonMedHistoryEditWindow',
	//width: 700,
	//height: 500,
	modal: true,
	plain: true,
	resizable: false,
	listeners: { 
		'maximize': function(win) {
			var new_height = win.getInnerHeight();
			win.formPanel.setHeight(new_height);
			win.formPanel.getForm().findField('PersonMedHistory_Descr').setHeight(new_height-35);
		},
		'restore': function(win) {
			win.formPanel.getForm().findField('PersonMedHistory_Descr').onResize('100%',620);
			win.syncSize();
			win.doLayout();
		}
	},

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();		
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
				data.PersonMedHistory_id = action.result.PersonMedHistory_id;
				win.callback(data);
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = [
				'PersonMedHistory_Descr'
				,'PersonMedHistory_setDT'
			];

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			form.findField('PersonMedHistory_Descr').getCKEditor().focus();
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},

	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'PersonMedHistoryRecordEditForm',
			labelAlign: 'right',
			labelWidth: 80,
			region: 'center',
			items: [{
				fieldLabel: lang['data_zapisi'],
				id: 'PMHEW_PersonMedHistory_setDT',
				name: 'PersonMedHistory_setDT',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_ETEW + 60,
				xtype: 'swdatefield'
			}, {
				anchor: '100%',
				//autoHeight: true,
				height: 400,
				allowBlank: false,
				hideLabel: true,
				CKConfig: {
					toolbarStartupExpanded: true,
					customConfig : '/ckeditor/config.js',
					toolbar: 'minimal',
					enterMode: CKEDITOR.ENTER_BR,
					shiftEnterMode: CKEDITOR.ENTER_BR
				},
  				name: 'PersonMedHistory_Descr',
				tabIndex: TABINDEX_ETEW + 61,
				xtype: 'ckeditor'
			}, {
				name: 'PersonMedHistory_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
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
								this.submit();
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
				{ name: 'PersonMedHistory_id' },
				{ name: 'Person_id' },
				{ name: 'PersonMedHistory_Descr' },
				{ name: 'PersonMedHistory_setDT' }
			]),
			timeout: 600,
			url: '/?c=PersonMedHistory&m=savePersonMedHistory'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_ETEW + 62,
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
				onTabElement: 'PMHEW_PersonMedHistory_setDT',
				tabIndex: TABINDEX_ETEW + 63,
				text: BTN_FRMCANCEL
			}],
			layout: 'border',
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swPersonMedHistoryEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swPersonMedHistoryEditWindow.superclass.show.apply(this, arguments);
		this.maximize();
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;

		this.doReset();

		var win = this,
			form = this.formPanel.getForm();

		form.setValues(arguments[0]);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['anamnez_jizni_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['anamnez_jizni_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['anamnez_jizni_dobavlenie']);
				form.findField('PersonMedHistory_setDT').setRawValue(getGlobalOptions().date);
			break;

			default:
				log('swPersonMedHistoryEditWindow - action invalid');
				return false;
			break;
		}

		if(this.action == 'add')
		{
			win.allowEdit(true);
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
					PersonMedHistory_id: form.findField('PersonMedHistory_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						form.findField('PersonMedHistory_setDT').setRawValue(getGlobalOptions().date);
						win.allowEdit(true);
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=PersonMedHistory&m=loadPersonMedHistoryEditForm'
			});
		}
	}
});
