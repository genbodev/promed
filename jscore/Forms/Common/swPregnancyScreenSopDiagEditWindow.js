/**
 * swPregnancyScreenSopDiagEditWindow - окно редактирования Сопутствующих диагнозов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Aleksandr Chebukin
 * @version      20.02.2016
 */

/*NO PARSE JSON*/
sw.Promed.swPregnancyScreenSopDiagEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPregnancyScreenSopDiagEditWindow',
	maximizable: false,
	width: 500,
	autoHeight: true,
	modal: true,
	objectName: 'swPregnancyScreenSopDiagEditWindow',
	objectSrc: '/jscore/Forms/Common/swPregnancyScreenSopDiagEditWindow.js',

	listeners: {
		'resize': function() {
			this.syncShadow();
		}
	},

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var data = {};
		var formParams = getAllFormFieldValues(this.FormPanel);

		formParams.DiagSetClass_Name = base_form.findField('DiagSetClass_id').getFieldValue('DiagSetClass_Name');

		var diag_combo = base_form.findField('Diag_id');
		formParams.Diag_FullName = diag_combo.getFieldValue('Diag_Code')+'. '+diag_combo.getFieldValue('Diag_Name');

		data.PregnancyScreenSopDiagData = formParams;

		this.callback(data);
		this.hide();
	},

	show: function() {
		sw.Promed.swPregnancyScreenSopDiagEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		var diag_combo = base_form.findField('Diag_id');
		var diag_id = diag_combo.getValue();
		if (!Ext.isEmpty(diag_id)) {
			diag_combo.getStore().load({
				callback: function() {
					var record = diag_combo.getStore().getById(diag_id);
					if (record) {
						diag_combo.setValue(diag_id);
					} else {
						diag_combo.setValue(null);
					}
				},
				params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
			});
		}

		switch(this.action) {
			case 'add':
				this.setTitle('Сопутствующий диагноз: Добавление');
				this.enableEdit(true);
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Сопутствующий диагноз: Изменеие');
					this.enableEdit(true);
				} else {
					this.setTitle('Сопутствующий диагноз: Просмотр');
					this.enableEdit(false);
				}
				loadMask.hide();
				break;
		}
	},

	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			id:'PSSDEW_FormPanel',
			border: false,
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 60,
			url: '/?c=PersonPregnancy&m=savePregnancyScreenSopDiag',
			items: [{
				name: 'PregnancyScreenSopDiag_id',
				xtype: 'hidden'
			}, {
				name: 'PregnancyScreen_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				xtype: 'swdiagsetclasscombo',
				hiddenName: 'DiagSetClass_id',
				fieldLabel: 'Вид',
				anchor: '100%',
				onLoadStore: function() {
					var combo = this;
					combo.lastQuery = '';
					combo.getStore().filterBy(function(rec) {
						return rec.get('DiagSetClass_SysNick').inlist(['osl','sop']);
					});
				}
			}, {
				allowBlank: false,
				xtype: 'swdiagcombo',
				hiddenName: 'Diag_id',
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'PregnancyScreenSopDiag_id' },
				{ name: 'PregnancyScreen_id' },
				{ name: 'DiagSetClass_id' },
				{ name: 'Diag_id' }
			])
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event)
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPregnancyScreenSopDiagEditWindow.superclass.initComponent.apply(this, arguments);
	}
});