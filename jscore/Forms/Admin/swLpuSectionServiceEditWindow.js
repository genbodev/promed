/**
 * swLpuSectionServiceEditWindow - окно редактирования групп диагнозов для ограничения доступа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.08.2014
 */

/*NO PARSE JSON*/

sw.Promed.swLpuSectionServiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuSectionServiceEditWindow',
	width: 580,
	autoHeight: true,
	modal: true,

	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		if ( !base_form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						form.getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		this.getLoadMask("Подождите, идет сохранение...").show();

		var data = new Object();

		data.LpuSectionServiceData = getAllFormFieldValues(this.FormPanel);

		this.callback(data);
		this.getLoadMask().hide();

		this.hide();
	},

	show: function() {
		sw.Promed.swLpuSectionServiceEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		this.LpuUnitType_SysNick = null;

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
		if (arguments[0].LpuUnitType_id) {
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		}

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var lpu_section_combo = base_form.findField('LpuSection_did');
		if (this.LpuUnitType_id) {
			setLpuSectionGlobalStoreFilter({
				arrayLpuUnitTypeId: [this.LpuUnitType_id]
			});
		}
		lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(lang['obslujivaemoe_otdelenie_dobavlenie']);
				loadMask.hide();
				base_form.findField('LpuSection_did').focus(true, 500);
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(lang['obslujivaemoe_otdelenie_redaktirovanie']);
				} else {
					form.enableEdit(false);
					form.setTitle(lang['obslujivaemoe_otdelenie_prosmotr']);
				}

				/*base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						loadMask.hide();
						form.hide();
					},
					url: '/?c=&m=',
					params: {},
					success: function() {
						loadMask.hide();

					}.createDelegate(this)
				});*/

			break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			/*bodyBorder: false,
			border: false,*/
			buttonAlign: 'left',
			frame: true,
			id: 'LSSEW_LpuSectionServiceEditForm',
			url: '/?c=Attribute&m=saveAttribute',
			bodyStyle: 'padding: 10px 20px;',
			labelAlign: 'right',

			items: [{
				xtype: 'hidden',
				value: 0,
				name: 'RecordStatus_Code'
			}, {
				xtype: 'hidden',
				name: 'LpuSectionService_id'
			}, {
				xtype: 'hidden',
				name: 'LpuSection_id'
			}, {
				allowBlank: false,
				xtype: 'swlpusectionglobalcombo',
				hiddenName: 'LpuSection_did',
				width: 360
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'LSSEW_ButtonSave',
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
					id: 'LSSEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swLpuSectionServiceEditWindow.superclass.initComponent.apply(this, arguments);
	}
});