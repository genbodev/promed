/**
 * swDocNormativeFileUploadWindow - окно выбора файла нормативного документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Dlo
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.02.2015
 */
/*NO PARSE JSON*/

sw.Promed.swDocNormativeFileUploadWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocNormativeFileUploadWindow',
	width: 620,
	autoHeight: true,
	modal: true,
	title: lang['vyibor_fayla_normativnogo_dokumenta'],

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

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		//loadMask.show();

		base_form.submit({
			success: function(result_form, action) {
				loadMask.hide();

				if (action.result.DocNormative_File) {
					base_form.findField('DocNormative_File').setValue(action.result.DocNormative_File);

					var data = {DocNormativeData: getAllFormFieldValues(this.FormPanel)};
					this.callback(data);
					this.hide();
				}
			}.createDelegate(this),
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swDocNormativeFileUploadWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.needType = true;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0] && !Ext.isEmpty(arguments[0].needType)) {
			this.needType = arguments[0].needType;
		}

		var type_combo = base_form.findField('DocNormativeType_id');
		type_combo.enable();

		if (arguments[0] && arguments[0].excludeDocNormativeTypes && arguments[0].excludeDocNormativeTypes.length > 0) {
			var excludeDocNormativeTypes = arguments[0].excludeDocNormativeTypes;

			type_combo.getStore().filterBy(function(rec) {
				return (!rec.get('DocNormativeType_Code').inlist(excludeDocNormativeTypes));
			});
			if (type_combo.getStore().getCount() == 1) {
				type_combo.disable();
				var record = type_combo.getStore().getAt(0);
				type_combo.setValue(record.get('DocNormativeType_id'));
			}
		}

		type_combo.setAllowBlank(!this.needType);
		type_combo.setContainerVisible(this.needType);
		this.syncShadow();
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			fileUpload: true,
			id: 'DNFUW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			url: '/?c=DocNormative&m=uploadDocNormativeFile',
			items: [{
				xtype: 'hidden',
				name: 'DocNormative_File'
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'DocNormativeType',
				hiddenName: 'DocNormativeType_id',
				fieldLabel: lang['tip_dokumenta'],
				width: 420
			}, {
				allowBlank: false,
				xtype: 'fileuploadfield',
				name: 'File',
				fieldLabel: lang['fayl'],
				emptyText: lang['vyiberite_fayl_normativnogo_dokumenta'],
				width: 420
			}]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.doSave();
						}.createDelegate(this),
						iconCls: 'save16',
						id: 'DNFUW_SaveButton',
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

		sw.Promed.swDocNormativeFileUploadWindow.superclass.initComponent.apply(this, arguments);
	}
});