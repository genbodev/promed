/* 
	Ухудшение состояния
*/


Ext.define('sw.tools.swDeteriorationSmpCallCardWindow', {
	alias: 'widget.swDeteriorationSmpCallCardWindow',
	extend: 'Ext.window.Window',
	title: 'Ухудшение состояния',
	refId: 'swDeteriorationSmpCallCardWindow',
	width: 400,
	resizable: false,
	modal: true,
	closeAction: 'hide',

	initComponent: function() {
		var win = this;

		win.addEvents({
			selectCmpReason: true
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{
					xtype: 'BaseForm',
					id: this.id + 'BaseForm',
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					items: [
						{
							xtype: 'cmpReasonCombo',
							width: 380,
							// fieldLabel: 'Повод',
							labelAlign: 'center',
							style:'margin: 15px;',
							name: 'CmpReason_id',
							allowBlank: false,
							listeners:{
								select: function(combo, val, index){},
                                focus: function(combo){
                                    var win = Ext.create('sw.tools.swDesigionTreeWindow', {
                                        treestore1: Ext.getStore('desigionTreePreStore'),
                                        treedata1: Ext.getStore('desigionTreePreStore').getRootNode().childNodes,
                                        listeners: {
                                            selectReason: function (CmpReason_id, CmpReason_Name) {
                                                var win = this;
                                                if (CmpReason_id > 1) {
                                                    combo.setValue(CmpReason_id);
                                                };
                                                Ext.defer(function(){win.close();}, 200)
                                            }
                                        }
                                    }).show();
                                }
							}
						}
					]
				}
			],
			buttons: [
				{
					text: 'Сохранить',
					iconCls: 'save16',
					refId: 'selectButton',
					handler: function () {
						var form = win.down('form').getForm(),
							data = form.getValues();
							
						data.CmpReason = form.findField('CmpReason_id').getRawValue();
						if( !data.CmpReason ) return;
						win.fireEvent('selectCmpReason', data, win.recCmp);
					}
				},
				'->',
				{
					xtype: 'button',
					iconCls: 'close16',
					refId: 'cancelButton',
					text: 'Закрыть',
					handler: function () {
						win.close();
					}
				}
			]

		});

		win.callParent(arguments);
	},
	
	show: function(a){
		var me = this,
			form = me.down('form').getForm();

		me.recCmp = a.recCmp;
		form.findField('CmpReason_id').reset();
		this.callParent(arguments);		
	}

},
function() {
    /**
     * @singleton
     * Singleton instance of {@link Ext.window.MessageBox}.
     */
    new this();
});