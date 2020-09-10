/**
 * swGoodsUnitEditWindow - окно редактирования единицы измерения товара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.01.2016
 */
/*NO PARSE JSON*/

sw.Promed.swGoodsUnitEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swGoodsUnitEditWindow',
	width: 540,
	minWidth: 540,
	autoHeight: true,
	modal: true,

	doSave: function() {
		var wnd = this;

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
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action)
			{
				loadMask.hide();

			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result){
					if (action.result.GoodsUnit_id ){
						this.callback();
						this.hide();
					} else if (!Ext.isEmpty(action.Error_Msg)) {
						Ext.Msg.alert(lang['oshibka'], action.Error_Msg);
					} else {
						Ext.Msg.alert(lang['oshibka'], lang['proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje']);
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swGoodsUnitEditWindow.superclass.show.apply(this, arguments);

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

		switch(this.action) {
			case 'add':
				this.setTitle(lang['edinitsa_izmereniya_tovara_dobavlenie']);
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['edinitsa_izmereniya_tovara_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['edinitsa_izmereniya_tovara_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						GoodsUnit_id: base_form.findField('GoodsUnit_id').getValue()
					},
					url: '/?c=GoodsUnit&m=loadGoodsUnitForm',
					success: function() {
						loadMask.hide();


					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'GUEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			url: '/?c=GoodsUnit&m=saveGoodsUnit',
			items: [{
				xtype: 'hidden',
				name: 'GoodsUnit_id'
			}, {
				xtype: 'textfield',
				name: 'GoodsUnit_Name',
				fieldLabel: lang['naimenovanie'],
				width: 360
			}, {
				xtype: 'textfield',
				name: 'GoodsUnit_Nick',
				fieldLabel: lang['kr_naimenovanie'],
				width: 360
			}, {
				editable: true,
				xtype: 'swokeicombo',
				hiddenName: 'Okei_id',
				fieldLabel: lang['po_spr_okei'],
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table><tr><td style="width: 40px;"><font color="red">{Okei_Code}</font>&nbsp;</td><td>{Okei_Name}&nbsp;</td></tr></table>',
					'</div></tpl>'
				),
				displayField: 'Okei_Name',
				width: 360
			}, {
				xtype: 'textfield',
				name: 'GoodsUnit_Descr',
				fieldLabel: lang['primechanie'],
				width: 360
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'GoodsUnit_id'},
				{name: 'GoodsUnit_Name'},
				{name: 'GoodsUnit_Nick'},
				{name: 'GoodsUnit_Descr'},
				{name: 'Okei_id'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'GUEW_SaveButton',
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

		sw.Promed.swGoodsUnitEditWindow.superclass.initComponent.apply(this, arguments);
	}
});