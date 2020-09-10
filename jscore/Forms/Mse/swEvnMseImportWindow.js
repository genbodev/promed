/**
 * swEvnMseImportWindow - Импорт обратных талонов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Mse
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */
/*NO PARSE JSON*/

sw.Promed.swEvnMseImportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swEvnMseImportWindow',
	width: 470,
	autoHeight: true,
	modal: true,
	title: langs('Импорт обратных талонов'),

	doSave: function() {
		var win = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask('Загрузка и анализ файла. Подождите...').show();
		
		base_form.submit({
			params: {
				Lpu_oid: base_form.findField('Lpu_oid').getValue()
			},
			failure: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg )  {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки обратного талона произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				var answer = action.result;

				if ( answer ) {
					if ( answer.EvnMse_id ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки обратного талона произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			}
		});
	},

	resetFormParams: function() {
		var base_form = this.FormPanel.getForm();

		base_form.reset();

		var LpuCombo = base_form.findField('Lpu_oid');

		LpuCombo.enable();
		LpuCombo.clearBaseFilter();

		switch(this.ARMType) {
			case 'mse':
			case 'superadmin':
				break;

			case 'lpuadmin':
				LpuCombo.disable();
				LpuCombo.setValue(getGlobalOptions().lpu_id);
				LpuCombo.setBaseFilter(function(record){
					return record.get('Lpu_id') == getGlobalOptions().lpu_id
				});
				break;
		}
	},

	show: function() {
		sw.Promed.swEvnMseImportWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		this.MedService_id = null;

		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}

		this.resetFormParams();
	},

	initComponent: function() {
		
		this.FormPanel = new Ext.FormPanel({
			id: 'EPMEW_FormPanel',
			frame: true,
			autoHeight: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 120,
			bodyStyle: 'margin-top: 10px;',
			url: '/?c=Mse&m=importEvnMse',
			items: [{
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'МО',
				width: 280
			}, {
				allowedExtensions: [ 'xml' ],
				fieldLabel: 'Выбрать файл',
				allowBlank: false,
				name: 'EvnMseFile',
				width: 280,
				xtype: 'fileuploadfield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: 'Загрузить'
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swEvnMseImportWindow.superclass.initComponent.apply(this, arguments);
	}
});