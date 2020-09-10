/**
 * swRegistryLLOExpertiseWindow - окно редактирования исхода беременности
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.04.2016
 */
/*NO PARSE JSON*/

sw.Promed.swRegistryLLOExpertiseWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryLLOExpertiseWindow',
	width: 400,
	autoHeight: true,
	modal: true,
	title: 'Статус реестра рецептов по экспертизе: Редактирование',

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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		base_form.submit({
			success: function() {
				loadMask.hide();
				this.callback();
				this.hide();
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	doFilterReceptUploadStatus: function() {
		var base_form = this.FormPanel.getForm();

		var accept_count = base_form.findField('AcceptRecept_Count').getValue();
		var error_count = base_form.findField('RegistryLLO_ErrorCount').getValue();
		var status_list = [];

		if (accept_count > 0 && error_count > 0) {
			status_list.push(5);
		}
		if (error_count > 0) {
			status_list.push(3);
		} else {
			status_list.push(4);
		}

		base_form.findField('ReceptUploadStatus_id').lastQuery = '';
		base_form.findField('ReceptUploadStatus_id').setBaseFilter(function(record) {
			return record.get('ReceptUploadStatus_Code').inlist(status_list);
		});
	},

	show: function() {
		sw.Promed.swRegistryLLOExpertiseWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		base_form.findField('ReceptUploadStatus_id').lastQuery = '';
		base_form.findField('ReceptUploadStatus_id').setBaseFilter(function(record) {
			return record.get('ReceptUploadStatus_Code').inlist([3,4,5]);
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		base_form.load({
			url: '/?c=RegistryLLO&m=loadRegistryLLOExpertiseForm',
			params: {RegistryLLO_id: base_form.findField('RegistryLLO_id').getValue()},
			success: function () {
				loadMask.hide();

				this.doFilterReceptUploadStatus();
			}.createDelegate(this),
			failure: function () {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'RLLOEW_FormPanel',
			labelAlign: 'right',
			labelWidth: 140,
			url: '/?c=RegistryLLO&m=saveRegistryLLOExpertise',
			items: [{
				xtype: 'hidden',
				name: 'RegistryLLO_id'
			}, {
				disabled: true,
				xtype: 'numberfield',
				name: 'AcceptRecept_Count',
				fieldLabel: 'Рецертов к оплате',
				width: 200
			}, {
				disabled: true,
				xtype: 'numberfield',
				name: 'RegistryLLO_ErrorCount',
				fieldLabel: 'Количество ошибок',
				width: 200
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'ReceptUploadStatus',
				hiddenName: 'ReceptUploadStatus_id',
				fieldLabel: 'Статус экспертизы',
				width: 200
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'RegistryLLO_id'},
				{name: 'AcceptRecept_Count'},
				{name: 'RegistryLLO_ErrorCount'},
				{name: 'ReceptUploadStatus_id'}
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'RLLOEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swRegistryLLOExpertiseWindow.superclass.initComponent.apply(this, arguments);
	}
});