/**
 * swBloodTransfusion - форма добавления осложнения случая переливания крови
 *
 * Kukuzapa forever!
 */
sw.Promed.swTransfusionComplication = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swTransfusionComplication',
	objectSrc: '/jscore/Forms/Hospital/swTransfusionComplication.js',
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	collapsible: false,
	draggable: true,
	modal: true,
	
	initComponent: function(){
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'TransfusionComplicationForm',
			labelAlign: 'right',
			labelWidth: 150,
			items:[{
				allowBlank: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				id: 'TransfusionCompl_FactDT',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				selectOnFocus: true,
				width: 100,
				value: new Date(),
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'TransfusionComplType',
				fieldLabel: 'Осложнения при трансфузии',
				hiddenName: 'TransfusionComplType_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}]
		});
		
		this.buttons = [{
			handler: function() {
				var transfusionComplication = win.getFormPanel().getForm().findField('TransfusionComplType_id');
				
				var transfusion_complication_id = transfusionComplication.getValue();

				var index = transfusionComplication.getStore().findBy(function(rec){
					return rec.get('TransfusionComplType_id') == transfusion_complication_id;
				});
				
				transfusionComplication = transfusionComplication.getStore().getAt(index);
				
				if (!transfusionComplication || !win.getFormPanel().getForm().findField('TransfusionCompl_FactDT').getValue()) {
					return false;
				}
				
				var data = {
					TransfusionComplType_id: transfusionComplication.get('TransfusionComplType_id'),
					TransfusionComplType_Name: transfusionComplication.get('TransfusionComplType_Name'),
					TransfusionCompl_FactDT: win.getFormPanel().getForm().findField('TransfusionCompl_FactDT').getValue()
				};
				
				this.callback(data);
			}.createDelegate(this),
			iconCls: 'save16',
			text: BTN_FRMSAVE
		}, {
			text: '-'
		}, {
			handler: function() {
				this.hide();
			}.createDelegate(this),
			iconCls: 'cancel16',
			text: BTN_FRMCANCEL
		}]
		
		Ext.apply(this,{items:[this.formPanel,this.buttons]});
		
		sw.Promed.swTransfusionComplication.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		sw.Promed.swTransfusionComplication.superclass.show.apply(this, arguments);
		
		var base_form = this.findById('TransfusionComplicationForm').getForm();
		
		base_form.reset();
		
		if (arguments && arguments[0]){
			
			var actionList = {
				'add': 'Осложнение при переливании: добавление',
				'edit': 'Осложнение при переливании: редактирование',
				'view': 'Осложнение при переливании: просмотр'
			}
			
			this.setTitle(actionList[arguments[0].action]);
			
			this.enableEdit(true);
			this.buttons[0].setDisabled(false);
			
			if (arguments[0].action == 'view') {
				this.enableEdit(false);
				this.buttons[0].setDisabled(true);
				this.buttons[0].show();
			}
			
			if (arguments[0].callback) {
				this.callback = arguments[0].callback;
			} else {
				this.callback = Ext.emptyFn;
			}
			
			if (arguments[0].TransfusionComplType_id) {
				base_form.findField('TransfusionComplType_id').setValue(arguments[0].TransfusionComplType_id);
			}
			
			if (arguments[0].TransfusionCompl_FactDT) base_form.setValues( {'TransfusionCompl_FactDT':arguments[0].TransfusionCompl_FactDT} );
		}
	}
});