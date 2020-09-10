
sw.Promed.swPersonFeedingTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {

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
		var _this = this;




		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();


		var index;
		var params = new Object();



		var data = new Object();

		switch ( this.formMode ) {
			case 'local':


				data.personFeedingTypeData = {
					'FeedingTypeAge_id': base_form.findField('FeedingTypeAge_id').getValue(),
					'PersonChild_id': base_form.findField('PersonChild_id').getValue(),
					'FeedingTypeAge_Age': base_form.findField('FeedingTypeAge_Age').getValue(),
					'FeedingType_id': base_form.findField('FeedingType_id').getValue(),
					'Person_id': base_form.findField('Person_id').getValue(),
					'Server_id': base_form.findField('Server_id').getValue()
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
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.FeedingTypeAge_id > 0 ) {
								base_form.findField('FeedingTypeAge_id').setValue(action.result.FeedingTypeAge_id);



								data.personFeedingTypeData = {
									'FeedingTypeAge_id': base_form.findField('FeedingTypeAge_id').getValue(),
									'PersonChild_id': base_form.findField('PersonChild_id').getValue(),
									'FeedingTypeAge_Age': base_form.findField('FeedingTypeAge_Age').getValue(),
									'FeedingType_id': base_form.findField('FeedingType_id').getValue(),
									'Person_id': base_form.findField('Person_id').getValue(),
									'Server_id': base_form.findField('Server_id').getValue()
								};
								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								}
							}
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
						if (Ext.getCmp('PersonEditWindow')){
							var grid = Ext.getCmp('PersonEditWindow').PersonFeedingType;
							grid.refreshRecords(null, 0);
						}
					}.createDelegate(this)
				});
				break;

			default:
				loadMask.hide();
				break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Server_id',
			'Person_id',
			'PersonChild_id',
			'FeedingTypeAge_Age',
			'FeedingType_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formMode: 'remote',
	formStatus: 'edit',
	id: 'PersonFeedingTypeEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonFeedingTypeEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'FeedingTypeAge_id' },
				{ name: 'PersonChild_id' },
				{ name: 'FeedingTypeAge_Age' },
				{ name: 'FeedingType_id' },
				{ name: 'Person_id' },
				{ name: 'Server_id' }
			]),
			url: '/?c=PersonFeedingType&m=savePersonFeedingType',

			items: [ {
				name: 'FeedingTypeAge_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'PersonChild_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'Server_id',
				value: 0,
				xtype: 'hidden'
			},{
				allowBlank: false,
				allowNegative: false,
				decimalPrecision: 0,
				fieldLabel: langs('Возраст (мес)'),
				name: 'FeedingTypeAge_Age',
				regex:new RegExp('(^[0-9]{0,6})$'),
				maxValue:60,
				width: 100,
				maxLength: 2,
				tabIndex: TABINDEX_PFT + 1,
				xtype: 'numberfield',
				maxLengthText: langs('Максимальное значение этого поля 60 ')
			},
				{
					allowBlank: false,
					comboSubject: 'FeedingType',
					fieldLabel: langs('Вид вскармливания'),
					hiddenName: 'FeedingType_id',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var record = combo.getStore().getById(newValue);

						}.createDelegate(this)
					},
					tabIndex: TABINDEX_PFT + 2,
					width: 200,
					xtype: 'swcommonsprcombo'
				}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}

				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PFT + 3,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if ( !this.buttons[0].hidden ) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					onTabAction: function () {

						 if ( !this.buttons[0].hidden ) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_PFT + 4,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonFeedingTypeEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonFeedingTypeEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
					break;

				case Ext.EventObject.J:
					current_window.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonFeedingTypeEditWindow.superclass.show.apply(this, arguments);
		this.center();


		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		this.personMode = 'man';
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}



		base_form.setValues(arguments[0].formParams);
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}


		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].personMode && typeof arguments[0].personMode == 'string' && arguments[0].personMode.inlist([ 'child', 'man' ]) ) {
			this.personMode = arguments[0].personMode;
		}



		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });

		loadMask.show();
		var index;
		var record;
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PFT_ADD);
				this.enableEdit(true);


				loadMask.hide();

				break;

			case 'edit':
			case 'view':
				if ( this.formMode == 'local' ) {
					if ( this.action == 'edit' ) {
						this.setTitle(WND_PFT_EDIT);
						this.enableEdit(true);
					}
					else {
						this.setTitle(WND_PFT_VIEW);
						this.enableEdit(false);
					}

					loadMask.hide();


				}
				else {
					var feeding_type_age_id = base_form.findField('FeedingTypeAge_id').getValue();

					if ( !feeding_type_age_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'FeedingTypeAge_id': feeding_type_age_id
						},
						success: function() {
							if ( this.action == 'edit' ) {
								this.setTitle(WND_PFT_EDIT);
								this.enableEdit(true);
							}
							else {
								this.setTitle(WND_PFT_VIEW);
								this.enableEdit(false);
							}

							loadMask.hide();

							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}

						}.createDelegate(this),
						url: '/?c=PersonFeedingType&m=loadPersonFeedingTypeEditForm'
					});
				}
				break;

			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},
	width: 400
});