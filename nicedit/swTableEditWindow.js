/**
* swTableEditWindow - окно добавления/редактирования таблицы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      16.10.2012
* @comment      
*/

/*NO PARSE JSON*/
sw.Promed.swTableEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swTableEditWindow',
	objectSrc: '/nicedit/swTableEditWindow.js',

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
	id: 'swTableEditWindow',
	width: 600,
	height: 255,
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
		
		var rows = parseInt(params['rows']);
		var cols = parseInt(params['cols']);
		if(rows <= 1 || cols <= 1) {
			sw.swMsg.alert('Сообщение','Количество строк и столбцов должно быть больше единицы');
			return false;
		}

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
		
		this.fieldNames = ['caption','rows','cols','spacing','padding','width','height','header','align'];//,'border'
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 170,
			region: 'center',
			items: [{
				anchor: '100%',
				allowBlank: true,
				fieldLabel: 'Заголовок таблицы',
				name: 'caption',
				id: 'TEW_Table_Caption',
				maxLength: 200,
				xtype: 'textfield'
			},{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: 'Строки',
						name: 'rows',
						allowNegative: false,
						allowDecimals: false,
						maskRe:  new RegExp("^[0-9]*$"),
						width: 80,
						xtype: 'numberfield'
					},{
						allowBlank: false,
						fieldLabel: 'Колонки',
						name: 'cols',
						allowNegative: false,
						allowDecimals: false,
						maskRe:  new RegExp("^[0-9]*$"),
						width: 80,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					labelWidth: 230,
					items: [{
						allowBlank: true,
						fieldLabel: 'Промежуток между ячейками',
						name: 'spacing',
						value: 0,
						allowNegative: false,
						allowDecimals: false,
						maskRe:  new RegExp("^[0-9]*$"),
						width: 80,
						xtype: 'numberfield'
					},{
						allowBlank: true,
						fieldLabel: 'Отступ внутри ячейки',
						name: 'padding',
						value: 0,
						allowNegative: false,
						allowDecimals: false,
						maskRe:  new RegExp("^[0-9]*$"),
						width: 80,
						xtype: 'numberfield'
					}]
				}]												
			},{
				allowBlank: false,
				fieldLabel: 'Ширина таблицы (%)',
				name: 'width',
				allowNegative: false,
				allowDecimals: false,
				maskRe:  new RegExp("^[0-9]*$"),
				width: 80,
				value: 100,
				minValue: 30,
				maxValue: 100,
				xtype: 'numberfield'
			},{
				allowBlank: true,
				fieldLabel: 'Высота таблицы (пиксели)',
				name: 'height',
				allowNegative: false,
				allowDecimals: false,
				maskRe:  new RegExp("^[0-9]*$"),
				width: 80,
				xtype: 'numberfield'
			},{
				allowBlank: false,
				valueField: 'header',
				comboData: [
					['no','Нет'],
					['row','Первая строка'],
					['col','Первый столбец'],
					['rowcol','Оба варианта']
				],
				comboFields: [
					{name: 'header', type:'string'},
					{name: 'header_Name', type:'string'}
				],
				value: 'no',
				fieldLabel: 'Заголовки',
				width: 130,
				xtype: 'swstoreinconfigcombo'
			},{
				allowBlank: false,
				valueField: 'align',
				comboData: [
					['left','По левому краю'],
					['center','По центру'],
					['right','По правому краю']
				],
				comboFields: [
					{name: 'align', type:'string'},
					{name: 'align_Name', type:'string'}
				],
				value: 'left',
				fieldLabel: 'Выравнивание',
				width: 130,
				xtype: 'swstoreinconfigcombo'
			/*},{
				allowBlank: true,
				fieldLabel: 'Размер рамки',
				name: 'border',
				value: 1,
				allowNegative: false,
				allowDecimals: false,
				maskRe:  new RegExp("^[0-9]*$"),
				width: 80,
				xtype: 'numberfield'*/
			},{
				name: 'border',
				value: 1,
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
				onTabElement: 'TEW_Table_Caption',
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swTableEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swTableEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.onSubmit = arguments[0].onSubmit || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		if(arguments[0].formParams)
		{
			this.action = 'edit';
		}
		else
		{
			arguments[0].formParams = {};
			this.action = 'add';
		}
		
		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm();

		form.setValues(arguments[0].formParams);
		switch (this.action) {
			case 'edit':
				this.setTitle('Таблица: Редактирование');
				this.setFormDisable({allowAll: true, focusField: 'caption', allowExcept: ['rows','cols','header']});
			break;
			default:
				this.setTitle('Таблица: Добавление');
				this.setFormDisable({allowAll: true, focusField: 'caption', allowExcept: []});
			break;
		}
	}
});
