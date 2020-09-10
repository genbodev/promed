/**
* swMorbusCrazyBaseDrugStartWindow - окно редактирования возраста начала употребления психоактивных средств
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      20.12.2011
*/

sw.Promed.swMorbusCrazyBaseDrugStartWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	doSave: function() 
	{
		
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var crazy_drug_recept_id = base_form.findField('CrazyDrugReceptType_id').getValue();
		var crazy_drug_recept_name = '';
		var index;
		var params = new Object();
		
		index = base_form.findField('CrazyDrugReceptType_id').getStore().findBy(function(rec) {
			if ( rec.get('CrazyDrugReceptType_id') == crazy_drug_recept_id ) {
				return true;
			}
			else {
				return false;
			}
		});		

		if ( index >= 0 ) {
			crazy_drug_recept_name = base_form.findField('CrazyDrugReceptType_id').getStore().getAt(index).get('CrazyDrugReceptType_Name');
		}
		
		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.crazyBaseDrugStartData = {
					'MorbusCrazyBaseDrugStart_id': base_form.findField('MorbusCrazyBaseDrugStart_id').getValue(),
					'MorbusCrazyBaseDrugStart_Name': base_form.findField('MorbusCrazyBaseDrugStart_Name').getValue(),
					'CrazyDrugReceptType_id': crazy_drug_recept_id,
					'CrazyDrugReceptType_Name': crazy_drug_recept_name,
					'MorbusCrazyBaseDrugStart_Age': base_form.findField('MorbusCrazyBaseDrugStart_Age').getValue()
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.MorbusCrazyBaseDrugStart_id > 0 ) {
								base_form.findField('MorbusCrazyBaseDrugStart_id').setValue(action.result.MorbusCrazyBaseDrugStart_id);

								data.crazyBaseDrugStartData = {
									'MorbusCrazyBaseDrugStart_id': base_form.findField('MorbusCrazyBaseDrugStart_id').getValue(),
									'MorbusCrazyBaseDrugStart_Name': base_form.findField('MorbusCrazyBaseDrugStart_Name').getValue(),
									'CrazyDrugReceptType_id': crazy_drug_recept_id,
									'CrazyDrugReceptType_Name': crazy_drug_recept_name,
									'MorbusCrazyBaseDrugStart_Age': base_form.findField('MorbusCrazyBaseDrugStart_Age').getValue()
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusCrazyBaseDrugStartWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].MorbusCrazyBaseDrugStart_id) 
			this.MorbusCrazyBaseDrugStart_id = arguments[0].MorbusCrazyBaseDrugStart_id;
		else 
			this.MorbusCrazyBaseDrugStart_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MorbusCrazyBaseDrugStart_id ) && ( this.MorbusCrazyBaseDrugStart_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['vozrast_nachala_upotrebleniya_psihoaktivnyih_sredstv_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['vozrast_nachala_upotrebleniya_psihoaktivnyih_sredstv_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['vozrast_nachala_upotrebleniya_psihoaktivnyih_sredstv_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
		loadMask.hide();
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				name: 'MorbusCrazyBaseDrugStart_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['naimenovanie'],
				allowBlank: false,
				name: 'MorbusCrazyBaseDrugStart_Name',
				xtype: 'textfield',
				width: 350
			}, {
				fieldLabel: lang['tip_priema'],
				allowBlank: false,
				hiddenName: 'CrazyDrugReceptType_id',
				xtype: 'swcrazydrugrecepttypecombo',
				width: 350
			}, {
				fieldLabel: lang['chislo_polnyih_let'],
				allowBlank: false,
				name: 'MorbusCrazyBaseDrugStart_Age',
				xtype: 'textfield',
				maskRe: /[0-9]/
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusCrazyBaseDrugStart_id'},
				{name: 'MorbusCrazyBaseDrugStart_Name'},
				{name: 'CrazyDrugReceptType_id'},
				{name: 'MorbusCrazyBaseDrugStart_Age'}
			])
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusCrazyBaseDrugStartWindow.superclass.initComponent.apply(this, arguments);
	}
});