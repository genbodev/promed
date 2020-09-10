/* 
	Wialon
*/

Ext.define('sw.tools.swWialonWindow', {
	alias: 'widget.swWialonWindow',
	extend: 'Ext.window.Window',
	title: 'Wialon',
	autoshow: true,
	modal: true,
	width: 1040,
	height: '100%',
	autoScroll: true,
	modal: true,
	layout: {
		align: 'stretch',
		type: 'vbox'
    },
	location: null,
	initComponent: function() {
		var me = this,
			conf = this.initialConfig;
	
		this.WForm = Ext.create('sw.BaseForm', {
			flex: 1,
			id: 'WForm',
			cls: 'mainFormNeptune',
			border: false,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelWidth: 120
			},
			items: [			
				{
				xtype: 'panel',				
				autoheight: true,
				border: false,
				autoScroll: true,
				loader: {
					renderer: function (loader, response, active) {
						var text = response.responseText;
						loader.getTarget().update(response.responseText);
						return true;
					},
					loadMask: true,
					autoLoad: true,					
					mode: 'iframe',
					url :'/?c=Wialon&m=getWialonWindow&url='+me.location
				}
			}
			]
		});
		
		Ext.applyIf(me, {
			items: [
				me.WForm
			]
		});	
		
		me.callParent();
	}
});