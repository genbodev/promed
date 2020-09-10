/**
* МСЭ - Ограничение основной категории жизнедеятельности и степень ее выраженности
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swEvnMseCategoryLifeTypeEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Ограничение основной категории жизнедеятельности и степень ее выраженности',
	maximized: false,
	maximizable: false,
	modal: true,
	resizable: false,
	height: 150,
	width: 560,
	onHide: Ext.emptyFn,
	shim: false,
	buttonAlign: "right",
	objectName: 'swEvnMseCategoryLifeTypeEditWindow',
	closeAction: 'hide',
	id: 'swEvnMseCategoryLifeTypeEditWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnMseCategoryLifeTypeEditWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.save();
			},
			iconCls: 'save16',
			tooltip: lang['sohranit'],
			text: lang['sohranit']
		},
		'-',
		{
			text: lang['otmena'],
			tabIndex: -1,
			tooltip: lang['otmena'],
			iconCls: 'cancel16',
			handler: function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		hide: function(w){
			w.disableFields(false);
			w.Frm.getForm().reset();
			w.buttons[0].setVisible(true);
		}
	},
	show: function()
	{
		sw.Promed.swEvnMseCategoryLifeTypeEditWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0] || !arguments[0].EvnMse_id){
			this.hide();
			return false;
		}
		
		if(arguments[0].action)
			this.action = arguments[0].action;
		else {
			this.hide();
			return false;
		}
		
		if(arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		
		if(arguments[0].callback) {
			this.callback = arguments[0].callback;
			this.obj_call = arguments[0].owner;
			this.onHide = function(){
				this.callback(this.obj_call, 0);
			}
		}
		
		var base_form = this.Frm.getForm();
		
		if (getRegionNick() != 'kz') {
			base_form.findField('CategoryLifeType_id').lastQuery = '';
			base_form.findField('CategoryLifeType_id').getStore().filterBy(function(rec) {
				return rec.get('CategoryLifeType_id') <= 7;
			});
		}
		
		switch(this.action)
		{
			case 'add':
				this.mode = 'ins';
				this.setTitle('Ограничение основной категории жизнедеятельности и степень ее выраженности: Добавление');
				base_form.findField('EvnMse_id').setValue(arguments[0].EvnMse_id);
			break;
			
			case 'edit':
			case 'view':
				base_form.setValues(arguments[0]);
				this.filterCategoryLifeTypeLinkCombo();
				if(this.action == 'edit'){
					this.setTitle('Ограничение основной категории жизнедеятельности и степень ее выраженности: Редактирование');
				} else if(this.action == 'view') {
					this.buttons[0].setVisible(false);
					this.setTitle('Ограничение основной категории жизнедеятельности и степень ее выраженности: Просмотр');
					this.disableFields(true);
				}
			break;
		}
		
		this.doLayout();
		this.center();
	},
	filterCategoryLifeTypeLinkCombo: function() {
		var win = this;
		var frm = win.Frm.getForm();
		
		var combo = frm.findField('CategoryLifeTypeLink_id');
		var categorylifetype_id = frm.findField('CategoryLifeType_id').getValue();
		var categorylifetypelink_id = combo.getValue();
		
		combo.getStore().load({
			globalFilters: {CategoryLifeType_id: categorylifetype_id}, 
			params: {CategoryLifeType_id: categorylifetype_id},
			callback: function() {
				if (!Ext.isEmpty(categorylifetypelink_id)) {
					combo.setValue(categorylifetypelink_id);
				}
			}
		});
		
	},
	save: function()
	{
		var win = this;
		var frm = win.Frm.getForm();

		if (!frm.isValid()) {
			sw.swMsg.alert(
				lang['oshibka'],
				lang['zapolnenyi_ne_vse_obyazatelnyie_polya_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']
			);
			return false;
		}

		win.getLoadMask(lang['sohranenie_dannyih']).show();
		frm.submit({
			params: {action: win.mode},
			success: function(){
				win.getLoadMask().hide();
				win.hide();
				win.onHide();
			},
			failure: function(){
				win.getLoadMask().hide();
			}
		});
	},
	
	disableFields: function(o) {
		this.Frm.findBy(function(field){
			if(field.xtype && field.xtype != 'hidden'){
				if(o) field.disable();
				else field.enable();
			}
		});
	},
	
	initComponent: function()
	{
		var win = this;
	
		this.Frm = new Ext.form.FormPanel({
			border: false,
			url: '/?c=Mse&m=saveEvnMseCategoryLifeType',
			labelAlign: 'right',
			labelWidth: 200,
			bodyStyle: 'padding: 5px;',
			frame: true,
			items: [
				{
					xtype: 'hidden',
					name: 'EvnMseCategoryLifeTypeLink_id'
				}, {
					xtype: 'hidden',
					name: 'EvnMse_id'
				}, {
					allowBlank: false,
					comboSubject: 'CategoryLifeType',
					hiddenName: 'CategoryLifeType_id',
					fieldLabel: 'Категория жизнедеятельности',
					xtype: 'swcommonsprcombo',
					listWidth: 350,
					width: 300,
					listeners:{
						'change':function (combo, newValue, oldValue) {
							win.filterCategoryLifeTypeLinkCombo();
						}.createDelegate(this),
						'select':function (combo) {
							combo.fireEvent('change',combo);
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					hiddenName: 'CategoryLifeTypeLink_id',
					displayField: 'CategoryLifeDegreeType_Name',
					valueField: 'CategoryLifeTypeLink_id',
					fieldLabel: 'Степень выраженности',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'CategoryLifeTypeLink_id'
						}, [
							{ name: 'CategoryLifeTypeLink_id', mapping: 'CategoryLifeTypeLink_id' },
							{ name: 'CategoryLifeDegreeType_Name', mapping: 'CategoryLifeDegreeType_Name' },
							{ name: 'CategoryLifeTypeLink_Name', mapping: 'CategoryLifeTypeLink_Name' }
						]),
						url:'/?c=Mse&m=loadCategoryLifeTypeLinkList'
					}),
					xtype: 'swbaselocalcombo',
					editable: false,
					mode: 'local',
					listWidth: 500,
					width: 300,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<div><h3>{CategoryLifeDegreeType_Name}&nbsp;</h3></div><div style="font-size: 10px;">{CategoryLifeTypeLink_Name}</div>',
						'</div></tpl>'
					)
				}
			]
		});
	
		Ext.apply(this,
		{
			layout: 'fit',
			items: [this.Frm]
		});
		sw.Promed.swEvnMseCategoryLifeTypeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});