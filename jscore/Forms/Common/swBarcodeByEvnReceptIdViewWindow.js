/**
* swBarcodeByEvnReceptIdViewWindow - произвольное окно редактирования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Salakhov R.
* @version      12.2019
* @comment      
*/
sw.Promed.swBarcodeByEvnReceptIdViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Получение бинарного кода по идентификатору рецепта',
	layout: 'border',
	id: 'BarcodeByEvnReceptIdViewWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 412,
	resizable: false,
	maximizable: false,
	maximized: false,
	show: function() {
        var wnd = this;
		sw.Promed.swBarcodeByEvnReceptIdViewWindow.superclass.show.apply(this, arguments);		

		this.form.reset();
	},
	getBinaryString: function() {
		var wnd = this;
		var evnrecept_id = wnd.form.findField('EvnRecept_id').getValue();

		wnd.form.findField('Binary_String').setValue(null);
		if (!Ext.isEmpty(evnrecept_id)) {
			Ext.Ajax.request({
				url: '/?c=BarCode&m=GetBarcodeBinaryString',
				params: {
					EvnRecept_id: evnrecept_id
				},
				callback: function (opt, success, response) {
					var response_text = response.responseText.replace("/*NO PARSE JSON*/", "");
					wnd.form.findField('Binary_String').setValue(response_text);
				}
			});
		}
	},
	initComponent: function() {
		var wnd = this;

        var form = new Ext.form.FormPanel({
            url: '/?c=BarcodeByEvnReceptId&m=save',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            bodyStyle: 'padding: 5px 5px 0',
			labelWidth: 160,
            items: [{
                xtype: 'hidden',
                name: 'BarcodeByEvnReceptId_id'
            }, {
                xtype: 'numberfield',
                fieldLabel: langs('Идентификатор рецепта'),
                name: 'EvnRecept_id',
				enableKeyEvents: true,
				listeners: {
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.ENTER ) {
							e.stopEvent();
							wnd.getBinaryString();
						}
					}
				}
            }, {
            	width: 400,
				height: 300,
				xtype: 'textarea',
				fieldLabel: langs('Бинарная строка'),
				name: 'Binary_String'
			}]
        });

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function()
				{
					this.ownerCt.getBinaryString();
				},
				iconCls: 'ok16',
				text: 'Получить код'
			}, {
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function () {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swBarcodeByEvnReceptIdViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}	
});