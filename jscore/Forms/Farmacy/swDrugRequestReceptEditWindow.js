/**
* swDrugRequestReceptEditWindow - окно редактирования сводной заявки
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      01.2015
* @comment      
*/
sw.Promed.swDrugRequestReceptEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnaya_zayavka'],
	layout: 'border',
	id: 'DrugRequestReceptEditWindow',
	modal: true,
	shim: false,
	width: 500,
	height: 260,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DrugRequestReceptEditForm').getFirstInvalidEl().focus(true);
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
	submit: function() {
		var wnd = this;
		var params = new Object();
		params.DrugRequestRecept_Kolvo = this.form.findField('DrugRequestRecept_Kolvo').getValue();

		wnd.getLoadMask(lang['podojdite_idet_sohranenie']).show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.DrugRequestRecept_id > 0) {
					var id = action.result.DrugRequestRecept_id;
					wnd.form.findField('DrugRequestRecept_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestReceptEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestRecept_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestRecept_id ) {
			this.DrugRequestRecept_id = arguments[0].DrugRequestRecept_id;
		}
		this.setTitle("Сводная заявка");
		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						wnd.getLoadMask().hide();
						wnd.hide();
					},
					params:{
						DrugRequestRecept_id: wnd.DrugRequestRecept_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false
						}
						wnd.form.setValues(result[0]);

						wnd.setDisabled(wnd.action == 'view');
						loadMask.hide();
					},
					url:'/?c=DrugRequestRecept&m=load'
				});
				break;
		}
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DrugRequestReceptEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 120,
				collapsible: true,
				url:'/?c=DrugRequestRecept&m=save',
				items: [{					
					xtype: 'hidden',
					name: 'DrugRequestRecept_id'
				}, {
					xtype: 'hidden',
					name: 'DrugRequestPeriod_id'
				}, {
					xtype: 'hidden',
					name: 'DrugProtoMnn_id'
				}, {
					xtype: 'textfield',
					fieldLabel: lang['kod'],
					name: 'DrugProtoMnn_Code',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['medikament'],
					name: 'DrugProtoMnn_Name',
					disabled: true,
					anchor: '100%'
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['zayavleno'],
					name: 'DrugRequestRecept_Kolvo',
					disabled: true
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['vhodyaschiy_ostatok'],
					name: 'DrugRequestRecept_KolvoRAS'
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['zakup'],
					name: 'DrugRequestRecept_KolvoPurch'
				}, {
					xtype: 'numberfield',
					fieldLabel: lang['dop_zakup'],
					name: 'DrugRequestRecept_KolvoDopPurch'
				}]
			}]
		});
		Ext.apply(this, {
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
			HelpButton(this, 0),
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
		sw.Promed.swDrugRequestReceptEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DrugRequestReceptEditForm').getForm();
	}	
});