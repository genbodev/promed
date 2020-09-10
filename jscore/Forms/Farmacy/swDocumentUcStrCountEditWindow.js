/**
* swDocumentUcStrCountEditWindow - произвольное окно редактирования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      04.2018
* @comment      
*/
sw.Promed.swDocumentUcStrCountEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Перевод количества упаковок в единицы измерения',
	layout: 'border',
	id: 'DocumentUcStrCountEditWindow',
	modal: true,
	shim: false,
	width: 380,
    height: 157,
	resizable: false,
	maximizable: false,
	maximized: false,
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DocumentUcStrCountEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        var data = new Object();
        data.GoodsUnit_Count = this.form.findField('GoodsUnit_Count').getValue();
        wnd.onSave(data);
        wnd.hide();
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDocumentUcStrCountEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.onSave = Ext.emptyFn;
		this.params = new Object();
        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
        if ( arguments[0].params != undefined ) {
            this.params = arguments[0].params;
        }

		this.form.reset();
        this.form.setValues(this.params);
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            url:'/?c=DocumentUcStrCount&m=save',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 120,
            bodyStyle: 'padding: 5px 5px 0',
            items: [{
                xtype: 'hidden',
                name: 'GoodsPackCount_Count'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Кол-во уп.',
                name: 'Pack_Count',
                allowDecimal: false,
                allowNegative: false,
                enableKeyEvents: true,
                listeners: {
                    'keyup': function() {
                        var val = wnd.form.findField('Pack_Count').getValue();
                        var koef = wnd.form.findField('GoodsPackCount_Count').getValue();
                        wnd.form.findField('GoodsUnit_Count').setValue(koef > 0 && val > 0 ? koef*val : null);
                    }
                }
            }, {
                xtype: 'textfield',
                fieldLabel: 'Ед.учета',
                name: 'GoodsUnit_Name',
                disabled: true
            }, {
                xtype: 'textfield',
                fieldLabel: 'Кол-во (ед.уч.)',
                name: 'GoodsUnit_Count',
                disabled: true
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
		sw.Promed.swDocumentUcStrCountEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});