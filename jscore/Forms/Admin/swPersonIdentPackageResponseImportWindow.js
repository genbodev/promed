/**
 * swPersonIdentPackageResponseImportWindow - окно для загрузки ответа из сервиса идентификации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.04.2017
 */
/*NO PARSE JSON*/

sw.Promed.swPersonIdentPackageResponseImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	modal: true,
	id: 'swPersonIdentPackageResponseImportWindow',
	title: 'Загрузить ответ от ТФОМС',
	width: 460,
	resizable: false,

	doImport: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
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

		this.getLoadMask('Загрузка файла ответа. Подождите...').show();

		var params = {
			ARMType: this.ARMType
		};

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				this.getLoadMask().hide();
				var answer = action.result;
				if (answer) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this),
			failure: function(result_form, action) {
				this.getLoadMask().hide();

				if (action.result) {
					if (action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					} else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			}.createDelegate(this)
		});

		return true;
	},

	getLoadMask: function(MSG) {
		if (MSG) {
			delete(this.loadMask);
		}
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},

	show: function() {
		sw.Promed.swPersonIdentPackageResponseImportWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.ARMType = null;
		this.callback = Ext.emptyFn;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			url: '/?c=PersonIdentPackage&m=importPersonIdentPackageResponse',
			labelAlign: 'right',
			labelWidth: 40,
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: 'Выберите файл',
				fieldLabel: 'Файл',
				name: 'PersonIdentPackage_Response'
			}]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doImport();
					}.createDelegate(this),
					iconCls: 'refresh16',
					text: 'Загрузить'
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					onTabElement: 'rixfOk',
					text: BTN_FRMCANCEL
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPersonIdentPackageResponseImportWindow.superclass.initComponent.apply(this, arguments);
	}
});