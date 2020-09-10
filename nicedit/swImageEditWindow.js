/**
* swImageEditWindow - окно добавления/редактирования изображения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      18.10.2012
* @comment      
*/

/*NO PARSE JSON*/
sw.Promed.swImageEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swImageEditWindow',
	objectSrc: '/nicedit/swImageEditWindow.js',

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
	id: 'swImageEditWindow',
	width: 600,
	height: 175,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};
		
		if ( !form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return false;
		}
		
		for ( var i = 0,n ; i < this.fieldNames.length ; i++ ) {
			n = this.fieldNames[i];
			params[n] = form.findField(n).getValue();
		}
		
		/*
		var Evn_id = parseInt(params['Evn_id']);
		if(Evn_id <= 0) {
			sw.swMsg.alert('Сообщение','Количество строк и столбцов должно быть больше единицы');
			return false;
		}
		*/

		this.hide();
		this.onSubmit(params);
		return true;
	},
	setFormDisable: function(config) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			n;
		
		if (!config) {
			config = {allowAll: true, focusField: this.fieldNames[0]};
		}
		
		if (config.allowAll) {
			for ( var i = 0 ; i < this.fieldNames.length ; i++ ) {
				n = this.fieldNames[i];
				form.findField(n).setDisabled(false);
			}
			save_btn.show();
		}
		
		if (config.disableAll) {
			for ( var i = 0 ; i < this.fieldNames.length ; i++ ) {
				n = this.fieldNames[i];
				form.findField(n).setDisabled(true);
			}
			save_btn.hide();
		}

		if (config.allowExcept) {
			for ( var i = 0 ; i < config.allowExcept.length ; i++ ) {
				n = config.allowExcept[i];
				form.findField(n).setDisabled(true);
			}
		}
		
		if (config.focusField) {
			form.findField(config.focusField).focus(true, 250);
		} else if (config.indexFocusButton) {
			this.buttons[config.indexFocusButton].focus(true, 250);
		}
	},

	initComponent: function() {
		var win = this;
		
		this.fieldNames = ['title','EvnMediaData_id','EvnXml_id','Evn_id','src','align','border'];
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
				value: null,
				name: 'EvnXml_id', // идентификатор редактируемого документа
				xtype: 'hidden'
			},{
				value: null,
				name: 'Evn_id', // идентификатор ТАП или КВС
				xtype: 'hidden'
			},{
				value: null,
				name: 'src', // URL
				xtype: 'hidden'
			},{
				anchor: '100%',
				allowBlank: true,
				fieldLabel: 'Комментарий',
				name: 'title',
				id: 'IEW_Image_title',
				maxLength: 200,
				xtype: 'textfield'
			},{
				anchor: '100%',
				allowBlank: false,
				filterType: 'image',
				saveOnce: true,
				hiddenName: 'EvnMediaData_id',
				fieldLabel: 'Изображение',
				listeners : {
					'select': function(combo, record, index) {
						var form = win.formPanel.getForm();
						if(record) {
							form.findField('src').setValue(record.get('EvnMediaData_Src'));
							form.findField('title').setValue(record.get('EvnMediaData_Comment'));
							form.findField('Evn_id').setValue(record.get('Evn_id'));
						} else {
							form.findField('src').setValue('');
							form.findField('title').setValue('');
							form.findField('EvnMediaData_id').setValue(null);
						}
					}
				},
				xtype: 'swevnmediadatacombo'
			},{
				allowBlank: false,
				valueField: 'align',
				comboData: [
					['left','Слева'],
					['center','По центру'],
					['right','Справа']
				],
				comboFields: [
					{name: 'align', type:'string'},
					{name: 'align_Name', type:'string'}
				],
				value: 'left',
				fieldLabel: 'Выравнивание',
				width: 120,
				xtype: 'swstoreinconfigcombo'
			},{
				allowBlank: true,
				fieldLabel: 'Размер рамки',
				name: 'border',
				value: 0,
				allowNegative: false,
				allowDecimals: false,
				maskRe:  new RegExp("^[0-9]*$"),
				width: 80,
				xtype: 'numberfield'
			}],
			keys: 
			[{
				title: true,
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
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
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
				onTabElement: 'IEW_Image_title',
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swImageEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swImageEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].formParams || (!arguments[0].formParams.EvnXml_id && !arguments[0].formParams.Evn_id) )
		{
			sw.swMsg.alert('Ошибка открытия формы', 'Отсутствуют параметры!');
			this.onHide = (arguments[0] && arguments[0].onHide) ||  Ext.emptyFn;
			this.hide();
			return false;
		}
		this.onSubmit = arguments[0].onSubmit || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		if(arguments[0].formParams.EvnMediaData_id)
		{
			this.action = 'edit';
		}
		else
		{
			this.action = 'add';
		}
		
		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm();
		form.setValues(arguments[0].formParams);

		switch (this.action) {
			case 'edit':
				this.setTitle('Изображение: Редактирование');
				this.setFormDisable({allowAll: true, focusField: 'title', allowExcept: []});
			break;
			default:
				this.setTitle('Изображение: Добавление');
				this.setFormDisable({allowAll: true, focusField: 'title'});
			break;
		}
		
		var img_combo = form.findField('EvnMediaData_id');
		img_combo.getStore().removeAll();
		img_combo.getStore().baseParams = {
			Evn_id: form.findField('Evn_id').getValue(),
			EvnXml_id: form.findField('EvnXml_id').getValue(),
			filterType: 'image'
		};
		img_combo.getStore().load({
			callback: function(rec, opt) {
				var id = img_combo.getValue();
				var value = (id && img_combo.getStore().getById(id)) ? id : null;
				img_combo.setValue(value);
				if (!value) {
					img_combo.fireEvent('select', img_combo, false, null);
				}
			}
		});
	}
});
