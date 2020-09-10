/**
 * swAnalyzerRackEditWindow - окно редактирования "Штативы"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Alexander Chebukin
 * @version      06.2012
 * @comment
 */
sw.Promed.swAnalyzerRackEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	autoHeight: false,
	objectName: 'swAnalyzerRackEditWindow',
	objectSrc: '/jscore/Forms/Admin/swAnalyzerRackEditWindow.js',
	title: lang['shtativyi'],
	layout: 'border',
	id: 'AnalyzerRackEditWindow',
	modal: true,
	shim: false,
	width:280,
	height:180,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						that.findById('AnalyzerRackEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.form;
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		form.submit(
			{
				params: params,
				failure: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						}
					}
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					that.callback(that.owner, action.result.AnalyzerRack_id);
					that.hide();
				}
			});
	},
	show: function()
	{
		var that = this;
		sw.Promed.swAnalyzerRackEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerRack_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].AnalyzerRack_id ) {
			this.AnalyzerRack_id = arguments[0].AnalyzerRack_id;
		}
		if ( undefined != arguments[0].AnalyzerModel_id ) {
			this.AnalyzerModel_id = arguments[0].AnalyzerModel_id;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_analyzermodel_id'], function() { that.hide(); });
		}
		this.form.reset();
		that.form.findField('AnalyzerModel_id').setValue(that.AnalyzerModel_id);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						AnalyzerRack_id: that.AnalyzerRack_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						that.form.setValues(result[0]);
						that.AnalyzerModel_id = result[0].AnalyzerModel_id;
						loadMask.hide();
					},
					url:'/?c=AnalyzerRack&m=load'
				});
				break;
		}
	},
	initComponent: function()
	{
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'AnalyzerRackEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 110,
				collapsible: true,
				region: 'north',
				url:'/?c=AnalyzerRack&m=save',
				items: [{
					name: 'AnalyzerRack_id',
					xtype: 'hidden',
					value: 0
				},
					{
						name: 'AnalyzerModel_id',
						xtype: 'hidden',
						value: 0
					},
					{
						allowDecimals:true,
						allowNegative:true,
						fieldLabel: lang['razmernost_po_h'],
						name: 'AnalyzerRack_DimensionX',
						width:100,
						xtype:'numberfield'
					},
					{
						allowDecimals:true,
						allowNegative:true,
						fieldLabel: lang['razmernost_po_y'],
						name: 'AnalyzerRack_DimensionY',
						width:100,
						xtype:'numberfield'
					},
					{
						fieldLabel: lang['po_umolchaniyu'],
						hiddenName: 'AnalyzerRack_IsDefault',
						xtype: 'swyesnocombo',
						width: 100
					}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerRack_id'},
				{name: 'AnalyzerModel_id'},
				{name: 'AnalyzerRack_DimensionX'},
				{name: 'AnalyzerRack_DimensionY'},
				{name: 'AnalyzerRack_IsDefault'},
				{name: 'AnalyzerRack_Deleted'}
			]),
			url: '/?c=AnalyzerRack&m=save'
		});
		Ext.apply(this,
			{
				layout: 'border',
				buttons:
					[{
						handler: function()
						{
							this.ownerCt.doSave();
						},
						iconCls: 'save16',
						text: BTN_FRMSAVE
					},
						{
							text: '-'
						},
						{
							handler: function()
							{
								this.ownerCt.hide();
							},
							iconCls: 'cancel16',
							text: BTN_FRMCANCEL
						}],
				items:[form]
			});
		sw.Promed.swAnalyzerRackEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerRackEditForm').getForm();
	}
});