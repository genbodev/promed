/**
* swUnionRegistryEditWindow - окно просмотра, добавления и редактирования объединённых реестров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.10.2013
*/

/*NO PARSE JSON*/
sw.Promed.swUnionRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUnionRegistryEditWindow',
	objectSrc: '/jscore/Forms/Admin/swUnionRegistryEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swUnionRegistryEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();

		form.findField('PayType_id').setContainerVisible(this.PayType_SysNick == 'mbudtrans');
		form.findField('PayType_id').setAllowBlank(this.PayType_SysNick != 'mbudtrans');

		form.reset();
	},
	doSave: function() {
		var win = this,
			base_form = this.formPanel.getForm(),
			params = {};

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		
		if (
			base_form.findField('Registry_accDate').getValue() <= base_form.findField('Registry_endDate').getValue()
			&& base_form.findField('Registry_accDate').getValue() >= base_form.findField('Registry_begDate').getValue()
		) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Дата не должна входить в период реестра.');
			return;
		}

		params.Registry_IsNew = win.Registry_IsNew;
		params.PayType_SysNick = win.PayType_SysNick;

		var RegNum = base_form.findField('Registry_Num').getValue().split('_'),
			numError = false,
			RegNumLength = 2,
			msg = 'Номер объединенного реестра должен быть в формате КодМО_ММГГГГ.';

		if(win.PayType_SysNick == 'mbudtrans') {

			params.PayType_SysNick=base_form.findField('PayType_id').getFieldValue('PayType_SysNick');

			if (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'mbudtrans'){
				msg = 'Номер объединенного реестра должен быть в формате КодМО_ММГГГГ_NZ.';
				RegNumLength = 3;
				if (RegNum[2] !== 'NZ') numError = true;
			}else{
				msg = 'Номер объединенного реестра должен быть в формате КодМО_ММГГГГ_SB.';
				RegNumLength = 3;
				if(RegNum[2] !== 'SB') numError = true;
			}
		}
		
		if (RegNum.length != RegNumLength) {
			numError = true;
		}  else if (RegNum[1].length != 6){
			numError = true;
		} else {
			var month = RegNum[1].substr(0, 2);
			if (month < '01' || month > '12') {
				numError = true;
				msg = 'Месяц в номере объединенного реестра должен быть от 01 до 12';
			} else {
				var year = RegNum[1].substr(2, 4),
					curYear = new Date().getFullYear();
				if (!year.inlist([curYear, curYear -1])) {
					numError = true;
					msg = 'Год в номере объединенного реестра должен быть текущий или предыдущий';
				}
			}
		}

		if (numError) {
			sw.swMsg.alert('Ошибка заполнения номера объединенного реестра', msg);
			return;
		}

		win.getLoadMask('Подождите, сохраняется запись...').show();
		base_form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();

				if (action.result && action.result.RegistrysFLKMore100) {
					sw.swMsg.alert('Предупреждение', 'Следующие реестры не были включены в объединённый, т.к. имеют более 100 ошибок ФЛК: '+action.result.RegistrysFLKMore100);
				}

				win.callback(win.owner,action.result.Registry_id);
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'center',
			items: [{
				allowBlank: false,
				autoCreate: {
					tag: "input",
					type: "text",
					maxLength: "16",
					autocomplete: "off"
				},
				disabled: false,
				fieldLabel: 'Номер',
				id: 'UREW_Registry_Num',
				name: 'Registry_Num',
				tabIndex: TABINDEX_UREW + 0,
				width: 115,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				disabled: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				name: 'Registry_accDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 1,
				width: 115,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Вид оплаты',
				hiddenName: 'PayType_id',
				loadParams: {
					params: {where: " where PayType_SysNick in (''mbudtrans', 'mbudtrans_mbud')"}
				},
				tabIndex: TABINDEX_UREW + 2,
				width: 115,
				xtype: 'swpaytypecombo',
				listeners: {
					select: function(cb,rec){
						var UnionRegistryNumber = win.formPanel.getForm().findField('Registry_Num').getValue();
						if(!UnionRegistryNumber) return false;
						
						var arRegistryNumber = UnionRegistryNumber.split('_');
						
						if (arRegistryNumber.length > 2){
							UnionRegistryNumber = arRegistryNumber[0] + '_' + arRegistryNumber[1] + (rec.get('PayType_SysNick') == 'mbudtrans' ? '_NZ' : '_SB');
						}
						win.formPanel.getForm().findField('Registry_Num').setValue(UnionRegistryNumber);
					}
				}
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 3,
				width: 115,
				xtype: 'swdatefield'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 4,
				width: 115,
				xtype: 'swdatefield'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
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
				{ name: 'Registry_id' },
				{ name: 'Registry_Num' },
				{ name: 'Registry_accDate' },
				{ name: 'Registry_begDate' },
				{ name: 'Registry_endDate' },
				{ name: 'Lpu_id' },
				{ name: 'PayType_id' }
			]),
			timeout: 600,
			url: '/?c=Registry&m=saveUnionRegistry'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_UREW + 11,
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
				onTabElement: 'UREW_Registry_Num',
				tabIndex: TABINDEX_UREW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swUnionRegistryEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swUnionRegistryEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;

		if (arguments[0].Registry_IsNew) {
			this.Registry_IsNew = arguments[0].Registry_IsNew;
		} else {
			this.Registry_IsNew = null;
		}
		if (arguments[0].PayType_SysNick) {
			this.PayType_SysNick = arguments[0].PayType_SysNick;
		} else {
			this.PayType_SysNick = null;
		}
		
		this.doReset();
		this.center();

		var win = this,
			base_form = this.formPanel.getForm();

		base_form.setValues(arguments[0]);

		if ( arguments[0].MedService_pid ) {
			base_form.findField('MedService_pid').setValue(arguments[0].MedService_pid);
		}
		
		switch (this.action) {
			case 'view':
				this.setTitle('Объединённый реестр: Просмотр');
			break;

			case 'edit':
				this.setTitle('Объединённый реестр: Редактирование');
			break;

			case 'add':
				this.setTitle('Объединённый реестр: Добавление');
			break;

			default:
				log('swUnionRegistryEditWindow - action invalid');
				return false;
			break;
		}
		
		if(this.action == 'add')
		{
			this.enableEdit(true);
			this.syncSize();
			this.doLayout();
			base_form.findField('Registry_accDate').setValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').setMaxValue(getGlobalOptions().date);
			base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			
			Ext.Ajax.request({
				url: '/?c=Registry&m=getUnionRegistryNumber',
				params: {
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				callback: function(options, success, response) 
				{
					win.getLoadMask().hide();
					if (success && response.responseText)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(result.UnionRegistryNumber)) {
							if(win.PayType_SysNick === 'mbudtrans'){
								if (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'mbudtrans'){
									result.UnionRegistryNumber += '_NZ';
								}else{
									result.UnionRegistryNumber += '_SB';
								}

							}
							base_form.findField('Registry_Num').setValue(result.UnionRegistryNumber);
						}
					}
				}
			});
		}
		else
		{
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					Registry_id: base_form.findField('Registry_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.enableEdit(true);
					}
					base_form.findField('Registry_accDate').fireEvent('change', base_form.findField('Registry_accDate'), base_form.findField('Registry_accDate').getValue(), 0);
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=Registry&m=loadUnionRegistryEditForm'
			});
		}
	}
});