/**
* swNeedConsultEditForm - окно просмотра и редактирования наследственности по заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2013 Swan Ltd.
* @author       
* @version      22.05.2013
* @comment      префикс NCEF
*/
/*NO PARSE JSON*/

sw.Promed.swNeedConsultEditForm = Ext.extend(sw.Promed.BaseForm, {
	layout: 'form',
	title: lang['pokazanie_k_konsultatsii_vracha-spetsialista'],
	id: 'NeedConsultEditForm',
	width: 450,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	doSave: function()  {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		var form = this.FormPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		form.getForm().submit(
		{
			url: '/?c=NeedConsult&m=saveNeedConsult',
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.NeedConsult_id) 
					{
						win.hide();
						win.callback(win.owner, action.result.NeedConsult_id);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swNeedConsultEditForm.superclass.show.apply(this, arguments);
		
		this.formStatus = 'edit';
		var win = this;
		win.getLoadMask("Подождите, идет загрузка...").show();
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		win.object = 'EvnPLDispDop13';
		
        if (arguments[0].object)
        {
        	win.object = arguments[0].object;
        }

		this.callback = Ext.EmptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].NeedConsult_id) {
			this.NeedConsult_id = arguments[0].NeedConsult_id;
		} else {
			this.NeedConsult_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		
		if (arguments[0].EvnPLDisp_id) {
			base_form.findField('EvnPLDisp_id').setValue(arguments[0].EvnPLDisp_id);
		}
		
		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle(lang['pokazanie_k_konsultatsii_vracha-spetsialista_dobavlenie']);
				break;
			case 'edit':
				this.enableEdit(true);
				win.setTitle(lang['pokazanie_k_konsultatsii_vracha-spetsialista_redaktirovanie']);
				break;
			case 'view':
				this.enableEdit(false);
				win.setTitle(lang['pokazanie_k_konsultatsii_vracha-spetsialista_prosmotr']);
				break;
		}
		
		if ( base_form.findField('Post_id').getStore().getCount() == 0 ) {
			base_form.findField('Post_id').getStore().load({
					callback: function() {
						if ( base_form.findField('Post_id').getValue() > 0 )
							base_form.findField('Post_id').setValue(base_form.findField('Post_id').getValue());
					}
			});
		}
		
		if (this.action != 'add') 
		{
			base_form.load(
			{
				url: '/?c=NeedConsult&m=loadNeedConsultGrid',
				params: 
				{
					NeedConsult_id: win.NeedConsult_id
				},
				success: function() 
				{
					win.getLoadMask().hide();
					base_form.findField('Post_id').focus(true, 100);
				},
				failure: function() 
				{
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { win.hide(); } );
				}
			});
		} 
		else 
		{
			win.getLoadMask().hide();
			base_form.findField('Post_id').focus(true, 100);
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			id: 'NeedConsultEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 130,
			items:
			[
				{
					name: 'NeedConsult_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDisp_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					fieldLabel: lang['vrach-spetsialist'],
					hiddenName: 'Post_id',
					width: 250,
					store: new Ext.data.JsonStore({
						url: '/?c=Common&m=loadPostCombo',
						baseParams: {Object:'Post', Post_id:'', Post_Name:'', Server_id:'check_it'},
						key: 'Post_id',
						autoLoad: false,
						fields: [
							{name: 'Post_id',    type:'int'},
							{name: 'Post_Name',  type:'string'}
						],
						sortInfo: {
							field: 'Post_Name'
						}
					}),
					tabIndex: TABINDEX_NCEF + 1,
					xtype: 'swpostcombo'
				},
				{
					allowBlank: false,
					comboSubject: 'ConsultationType',
					fieldLabel: lang['mesto_provedeniya'],
					hiddenName: 'ConsultationType_id',
					tabIndex: TABINDEX_NCEF + 2,
					width: 250,
					xtype: 'swcommonsprcombo'
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//alert('success');
				}
			},
			[
				{ name: 'NeedConsult_id' },
				{ name: 'EvnPLDisp_id' },
				{ name: 'Post_id' },
				{ name: 'ConsultationType_id' }
			]
			)
		});
		
		Ext.apply(this,
		{
			border: false,
			items: [this.FormPanel],
			buttons:
			[
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_NCEF + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_NCEF + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_NCEF + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swNeedConsultEditForm.superclass.initComponent.apply(this, arguments);
	}
});