/**
* swMorbusHepatitisPlanWindow - План лечения Гепатита C.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      06.2019
*/
sw.Promed.swMorbusHepatitisPlanWindow = Ext.extend(sw.Promed.BaseForm,
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
	width: 500,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

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
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
				win.formStatus = 'edit';
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				win.callback();
				win.hide();
			}
		});
		
	},
	show: function() 
	{
		sw.Promed.swMorbusHepatitisPlanWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
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
		
		if (arguments[0].MorbusHepatitisPlan_id) 
			this.MorbusHepatitisPlan_id = arguments[0].MorbusHepatitisPlan_id;
		else 
			this.MorbusHepatitisPlan_id = null;
			
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
			if ( ( this.MorbusHepatitisPlan_id ) && ( this.MorbusHepatitisPlan_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.findField('MorbusHepatitisPlan_Year').getStore().removeAll();

		var currDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		for (var year = currDate.getFullYear(); year <= currDate.getFullYear() + 2; year++) {
			var record = new Ext.data.Record({
				text: year
			});
			base_form.findField('MorbusHepatitisPlan_Year').getStore().add(record, true);
		}
				
		switch (this.action) 
		{
			case 'add':
				this.setTitle(langs('План лечения Гепатита C: Добавление'));
				this.enableEdit(true);

				if (getGlobalOptions().lpu_id) {
					base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
				}
				break;
			case 'edit':
				this.setTitle(langs('План лечения Гепатита C: Редактирование'));
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(langs('План лечения Гепатита C: Просмотр'));
				this.enableEdit(false);
				break;
		}
		
		if (this.action != 'add') {
			base_form.load({
				failure: function() {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
				},
				params:{
					MorbusHepatitisPlan_id: this.MorbusHepatitisPlan_id
				},
				success: function(form, action) {
					base_form.findField('MorbusHepatitisPlan_Year').fireEvent('change', base_form.findField('MorbusHepatitisPlan_Year'), base_form.findField('MorbusHepatitisPlan_Year').getValue());
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisPlan&m=load'
			});			
		} else {
			loadMask.hide();
		}
		
	},	
	initComponent: function() 
	{
		var win = this;

		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 150,
			url:'/?c=MorbusHepatitisPlan&m=save',
			items: 
			[{
				name: 'MorbusHepatitisPlan_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitis_id',
				xtype: 'hidden'
			}, {
				hiddenName: 'MorbusHepatitisPlan_Year',
                fieldLabel: langs('Год лечения'),
				allowBlank: false,
				store: [],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var currDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

						var base_form = win.FormPanel.getForm();
						base_form.findField('MorbusHepatitisPlan_Month').lastQuery = '';
						base_form.findField('MorbusHepatitisPlan_Month').getStore().clearFilter();
						if (currDate.getFullYear() == newValue) {
							base_form.findField('MorbusHepatitisPlan_Month').getStore().filterBy(function(rec) {
								if (rec.get('value') > currDate.getMonth()) {
									return true;
								} else {
									return false;
								}
							});
						}
					}
				},
				triggerAction: 'all',
				forceSelection: true,
				xtype: 'combo',
				width: 300
			}, {
				hiddenName: 'MorbusHepatitisPlan_Month',
                fieldLabel: langs('Месяц лечения'),
				allowBlank: false,
				store: [
					[1, 1],
					[2, 2],
					[3, 3],
					[4, 4],
					[5, 5],
					[6, 6],
					[7, 7],
					[8, 8],
					[9, 9],
					[10, 10],
					[11, 11],
					[12, 12]
				],
				triggerAction: 'all',
				forceSelection: true,
				xtype: 'combo',
				width: 300
			}, {
				hiddenName: 'MedicalCareType_id',
                fieldLabel: langs('Условия оказания МП'),
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				prefix: 'fed_',
				comboSubject: 'MedicalCareType',
				width: 300
			}, {
				hiddenName: 'Lpu_id',
                fieldLabel: langs('МО планируемого лечения'),
				allowBlank: false,
				xtype: 'swlpucombo',
				width: 300
			}, {
				value: 1,
				allowBlank: false,
				width: 100,
				fieldLabel: langs('Лечение проведено'),
				hiddenName: 'MorbusHepatitisPlan_Treatment',
				xtype: 'swyesnocombo'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisPlan_id'},
				{name: 'MorbusHepatitis_id'},
				{name: 'MorbusHepatitisPlan_Year'},
				{name: 'MorbusHepatitisPlan_Month'},
				{name: 'MedicalCareType_id'},
				{name: 'Lpu_id'},
				{name: 'MorbusHepatitisPlan_Treatment'}
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
		sw.Promed.swMorbusHepatitisPlanWindow.superclass.initComponent.apply(this, arguments);
	}
});