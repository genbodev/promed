/**
* swMorbusHepatitisVaccinationWindow - Вакцинация.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisVaccinationWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var win = this;
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
		
		base_form.submit({
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				win.callback();
				win.hide();
			}
		});
		
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
		sw.Promed.swMorbusHepatitisVaccinationWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
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
		
		if (arguments[0].MorbusHepatitisVaccination_id) 
			this.MorbusHepatitisVaccination_id = arguments[0].MorbusHepatitisVaccination_id;
		else 
			this.MorbusHepatitisVaccination_id = null;
			
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
			if ( ( this.MorbusHepatitisVaccination_id ) && ( this.MorbusHepatitisVaccination_id > 0 ) )
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
				this.setTitle(lang['vaktsinatsiya_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['vaktsinatsiya_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['vaktsinatsiya_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHepatitisVaccination_id: this.MorbusHepatitisVaccination_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					//log(result[0]);
					base_form.setValues(result[0]);
					var combo = base_form.findField('Drug_id');
					var drug_id = combo.getValue();
					combo.getStore().baseParams.Drug_id=drug_id;
					combo.getStore().baseParams.query=null;
					combo.getStore().load({
						params: {Drug_id: drug_id},
						callback: function() {
							this.setValue(drug_id);
							combo.hasFocus = true;
							combo.getStore().baseParams.Drug_id=null;
						}.createDelegate(combo)
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisVaccination&m=load'
			});			
		} else {
			loadMask.hide();
		}
		
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
			labelWidth: 200,
			url:'/?c=MorbusHepatitisVaccination&m=save',
			items: 
			[{
				name: 'MorbusHepatitisVaccination_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitis_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data'],
				name: 'MorbusHepatitisVaccination_setDT',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				hiddenName: 'Drug_id',
				displayField: 'Drug_Name',
				valueField: 'Drug_id',
				fieldLabel: lang['preparat'],
				xtype: 'swbaseremotecombo',
				width: 450,
				triggerAction: 'none',
				allowBlank: false,
				trigger1Class: 'x-form-search-trigger',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'Drug_id'
					}, [
						{name: 'Drug_id', type:'int'},
						{name: 'Drug_Code', type:'int'},
						{name: 'Drug_Name', type: 'string'}
					]),
					url: '/?c=RlsDrug&m=loadDrugSimpleList'
				}),
				onTrigger1Click: function() 
				{
					if (this.disabled)
						return false;
					var combo = this;
					// Именно для этого комбо логика несколько иная 
					if (!this.formList)
					{
						if (Ext.getCmp('DrugPrepWinSearch')) {
							this.formList = Ext.getCmp('DrugPrepWinSearch');
						} else {
							this.formList = new sw.Promed.swListSearchWindow(
							{
								//params: {
									title: lang['poisk_medikamenta'],
									id: 'DrugPrepWinSearch',
									object: 'Drug',
									modal: false,
									//maximizable: true,
									maximized: true,
									paging: true,
									prefix: 'dprws',
									dataUrl: '/?c=Farmacy&m=loadDrugMultiList',
									columns: true,
									stringfields:
									[
										{name: 'Drug_id', key: true},
										{name: 'DrugPrepFas_id', hidden: true},
										{name: 'DrugTorg_Name', autoexpand: true, header: lang['torgovoe_naimenovanie'], isfilter:true, columnWidth: '.4'},
										{name: 'DrugForm_Name', header: lang['forma_vyipuska'], width: 140, isfilter:true, columnWidth: '.15'},
										{name: 'Drug_Dose', header: lang['dozirovka'], width: 100, isfilter:true, columnWidth: '.15'},
										{name: 'Drug_Fas', header: lang['fasovka'], width: 100},
										{name: 'Drug_PackName', header: lang['upakovka'], width: 100},
										{name: 'Drug_Firm', header: lang['proizvoditel'], width: 200, isfilter:true, columnWidth: '.3'},
										{name: 'Drug_Ean', header: 'EAN', width: 100},
										{name: 'Drug_RegNum', header: lang['ru'], width: 120}
									],
									useBaseParams: true
								//}
							});
						}
					}
					var params = (combo.getStore().baseParams)?combo.getStore().baseParams:{};
					params.Drug_id = null;
					combo.collapse();
					this.collapse();
					this.formList.show(
					{
						params:params,
						onSelect: function(data) 
						{
							combo.hasFocus = false;
							combo.getStore().baseParams.Drug_id=data['Drug_id'];
							combo.getStore().baseParams.query=null;
							combo.getStore().load({
								params: {Drug_id: data['Drug_id']},
								callback: function() {
									this.setValue(data['Drug_id']);
									combo.hasFocus = true;
									combo.getStore().baseParams.Drug_id=null;
								}.createDelegate(combo)
							});
						}.createDelegate(this), 
						onHide: function() 
						{
							this.focus(false);
						}.createDelegate(this)
					});
					return false;
				}
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisVaccination_id'},
				{name: 'MorbusHepatitisVaccination_setDT'},
				{name: 'MorbusHepatitisVaccination_Vaccine'}
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
		sw.Promed.swMorbusHepatitisVaccinationWindow.superclass.initComponent.apply(this, arguments);
	}
});