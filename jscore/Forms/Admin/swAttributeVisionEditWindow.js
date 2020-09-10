/**
 * swAttributeVisionEditWindow - окно редактирования базового справочника
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAttributeVisionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeVisionEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					log(this);
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('AttributeVision_AppCode').getValue())) {
			try {
				var obj = {};
				eval('obj={'+base_form.findField('AttributeVision_AppCode').getValue()+'}');
			} catch(e) {
				var msg = 'Ошибка в коде условия: "'+e+'"';
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function()
					{
						this.formStatus = 'edit';
						base_form.findField('AttributeVision_AppCode').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: msg,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swAttributeVisionEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		this.hideDBObject = false;
		if (arguments[0].hideDBObject) {
			this.hideDBObject = arguments[0].hideDBObject;
		}


		if (this.hideDBObject) {
			base_form.findField('AttributeVision_TableName').hideContainer();
		} else {
			base_form.findField('AttributeVision_TableName').showContainer();
		}

		this.syncShadow();

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var attribute_combo = base_form.findField('Attribute_id');
		var attribute_sign_combo = base_form.findField('AttributeSign_id');

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(lang['oblast_vidimosti_atributa_dobavlenie']);
				loadMask.hide();

				attribute_combo.getStore().load();

				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(lang['oblast_vidimosti_atributa_redaktirovanie']);
				} else {
					form.enableEdit(false);
					form.setTitle(lang['oblast_vidimosti_atributa_prosmotr']);
				}

				base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						loadMask.hide();
						form.hide();
					},
					url: '/?c=Attribute&m=loadAttributeVisionForm',
					params: {AttributeVision_id: base_form.findField('AttributeVision_id').getValue()},
					success: function() {
						loadMask.hide();
						attribute_combo.getStore().load({
							//params: {Attribute_id: attribute_combo.getValue()},
							callback: function(){
								attribute_combo.setValue(attribute_combo.getValue());
							}
						});

						attribute_sign_combo.getStore().load({
							params: {AttributeSign_id: attribute_sign_combo.getValue()},
							callback: function(){
								attribute_sign_combo.setValue(attribute_sign_combo.getValue());
							}
						});
					}.createDelegate(this)
				});

				break;
		}

		if (arguments[0].existsKeyValue) {
			base_form.findField('AttributeVision_IsKeyValue').disable();
		} else {
			base_form.findField('AttributeVision_IsKeyValue').enable();
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'AVEW_AttributeVisionEditForm',
			url: '/?c=Attribute&m=saveAttributeVision',
			labelWidth: 150,
			labelAlign: 'right',

			items: [{
				name: 'AttributeVision_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				hiddenName: 'Attribute_id',
				fieldLabel: lang['atribut'],
				xtype: 'swattributecombo',
				width: 180
			}, {
				//allowBlank: false,
				name: 'AttributeVision_TableName',
				fieldLabel: lang['obyekt_bd'],
				xtype: 'textfield',
				width: 180
			}, {
				name: 'AttributeVision_TablePKey', // значение в таблице
				xtype: 'hidden'
			}, {
				name: 'AttributeVision_IsKeyValue', // является значением
				fieldLabel: lang['yavlyaetsya_znacheniem'],
				xtype: 'checkbox'
			}, {
				allowBlank: false,
				name: 'AttributeVision_Sort',
				fieldLabel: lang['sortirovka'],
				xtype: 'numberfield',
				maxValue: 1000,
				minValue: 0,
				width: 180
			}, {
				xtype  : 'combo',
				store  : new Ext.data.SimpleStore({
					fields : ['Region_id','Region_Name'],
					data   : [
						['1' , langs('Адыгея')],
						['10' , langs('Карелия')],
						['19' , langs('Хакасия')],
						['30' , langs('Астрахань')],
						['60' , langs('Псков')],
						['63' , langs('Самара')],
						['64' , langs('Саратов')],
						['77' , langs('Москва')],
						['101' , langs('Казахстан')],
						['66' , langs('Екатеринбург')],
						['59' , langs('Пермь')],
						['2' , langs('Уфа')],
						['3' , langs('Бурятия')],
						['58' , langs('Пенза')],
						['91' , langs('Крым')]
					]
				}),
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'{Region_Name}&nbsp;'+
					'</div></tpl>',
				name        : 'Region_id' ,
				hiddenName  : 'Region_id' ,
				fieldLabel  : lang['region'],
				displayField: 'Region_Name',
				valueField  : 'Region_id',
				triggerAction : 'all',
				mode        : 'local',
				editable    : false,
				width: 180
			}, {
				xtype: 'swattributesigncombo',
				name: 'AttributeSign_id',
				fieldLabel: lang['priznak'],
				anchor: '99%'
			}, {
				xtype: 'sworgcomboex',
				hiddenName: 'Org_id',
				fieldLabel: lang['organizatsiya'],
				//width: 360
				anchor: '99%'
			}, {
				xtype: 'textarea',
				name: 'AttributeVision_AppCode',
				fieldLabel: lang['kod_usloviya'],
				maxLength: 4000,
				//width: 360
				height: 200,
				anchor: '99%'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						name: 'AttributeVision_begDate',
						fieldLabel: lang['nachalo'],
						width: 120
					}]
				}, {
					layout: 'form',
					labelWidth: 90,
					items: [{
						xtype: 'swdatefield',
						name: 'AttributeVision_endDate',
						fieldLabel: lang['okonchanie'],
						width: 120
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'AttributeVision_id'},
				{name: 'Attribute_id'},
				{name: 'AttributeVision_TableName'},
				{name: 'AttributeVision_TablePKey'},
				{name: 'AttributeVision_IsKeyValue'},
				{name: 'AttributeVision_Sort'},
				{name: 'Region_id'},
				{name: 'Org_id'},
				{name: 'AttributeVision_AppCode'},
				{name: 'AttributeVision_begDate'},
				{name: 'AttributeVision_endDate'},
				{name: 'AttributeSign_id'}
			])/*,
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]*/
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'AVEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'AVEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAttributeVisionEditWindow.superclass.initComponent.apply(this, arguments);
	}
});