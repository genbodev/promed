/**
* swTestEventsWindow - тестовая форма
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka Lich (ipshon@rambler.ru)
* @version      17.09.2009
*/

sw.Promed.swTestEventsWindow = Ext.extend(sw.Promed.BaseForm, {
	width : 400,
	height : 300,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	show: function() {
        sw.Promed.swTestEventsWindow.superclass.show.apply(this, arguments);
		this.center();
	},
	title: lang['test'],
	initComponent: function() {
    	Ext.apply(this, {
			buttonAlign : "right",
			buttons : [{
					text : lang['zakryit'],
					iconCls: 'close16',
					handler : function(button, event) {
						button.ownerCt.hide();
					}
				}],
			items : [
				new sw.Promed.FormPanelWithChangeEvents({
					items: [{
						autoLoad: true,
						id: 'TEW_Lpu',
						width: 200,
						xtype: 'swlpulocalcombo'
					}, {
						id: 'TEW_MedPersonal_id',
						width: 200,
						xtype: 'swmedpersonalcombo',
						onFormParamChange: function(field, value) {							switch ( field.id )
							{								// изменилось зависимое поле
								case 'TEW_Lpu':
                                	// загружаем себя
									this.clearValue();
									this.getStore().removeAll();
									this.getStore().load({										params: {											Lpu_id: value
										}									});
								break;							}						}
					}]
				})
			]
		});
		sw.Promed.swTestEventsWindow.superclass.initComponent.apply(this, arguments);
	}
});