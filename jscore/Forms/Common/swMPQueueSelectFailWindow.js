/**
* swMPQueueSelectFailWindow - окно выбора причины отмены направления из очереди
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Salakhov Rustam
* @version      22.10.2010
*/

/**
 * swMPQueueSelectFailWindow - окно выбора причины отмены направления из очереди
 *
 * @class sw.Promed.swMPQueueSelectFailWindow
 * @extends Ext.Window
 */
sw.Promed.swMPQueueSelectFailWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	closable: false,
	closeAction:'hide',
	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			id: 'QueueFailSelectForm',
			labelAlign: 'top',
			labelWidth: 140,
			style: 'padding: 10px',

			items: [{
				allowBlank: false,
				comboSubject: 'QueueFailCause',
				fieldLabel: lang['prichina_otmenyi_napravleniya_iz_ocheredi'],
				typeCode: 'int',
				sortField: 'QueueFailCause_Name',
				width: 450,
				codeField: false,
				onSelect : function(record, index){
					if(this.fireEvent('beforeselect', this, record, index) !== false){
						this.setValue(record.data[this.valueField || this.displayField]);
						this.collapse();
					}
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{QueueFailCause_Name}&nbsp;',
					'</div></tpl>'
				),
				onLoadStore: function() {
					form.filterQueueFailCause();
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['kommentariy'],
                allowBlank: false,
				height: 70,
                value:' ',
                name: 'EvnComment_Comment',
				width: 450,
				xtype: 'textarea'
			}]
		});
	
		Ext.apply(this, {
			buttonAlign: "right",
			buttons: [{
				handler: function(button, event) {
					var base_form = form.FormPanel.getForm();

                    if ( !base_form.isValid() )
                    {
                        sw.swMsg.show(
                            {
                                buttons: Ext.Msg.OK,
                                icon: Ext.Msg.WARNING,
                                msg: ERR_INVFIELDS_MSG,
                                title: ERR_INVFIELDS_TIT
                            });
                        return false;
                    }

					form.onSelectValue({
						 val: base_form.findField('QueueFailCause_id').getValue()
						,comment: base_form.findField('EvnComment_Comment').getValue()
					});
				}.createDelegate(this),
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			}, {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCANCEL
			}],
			items: [
				form.FormPanel
			]
		});

		sw.Promed.swMPQueueSelectFailWindow.superclass.initComponent.apply(this, arguments);
	},
	modal: true,
	onSelectValue: Ext.emptyFn,
	plain: false,
	resizable: false,
	filterQueueFailCause: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('QueueFailCause_id').getStore().clearFilter();
		base_form.findField('QueueFailCause_id').lastQuery = '';
		base_form.findField('QueueFailCause_id').getStore().filterBy(function(rec){
			var flag = true;
			if (Ext.isArray(win.enabledQueueFailCauseCodeList)) {
				if (false == rec.get('QueueFailCause_Code').inlist(win.enabledQueueFailCauseCodeList)) {
					flag = false;
				}
			} else if (Ext.isArray(win.disabledQueueFailCauseCodeList)) {
				if (true == rec.get('QueueFailCause_Code').inlist(win.disabledQueueFailCauseCodeList)) {
					flag = false;
				}
			} else {
				if (rec.get('QueueFailCause_Code').inlist([9,10,11])) {
					flag = false;
				}
				if(win.from=='workplacepriem'&&!rec.get('QueueFailCause_Code').inlist([4,12])){
					flag = false;
				}
			}
			return flag;
		});
	},
	show: function() {
		sw.Promed.swMPQueueSelectFailWindow.superclass.show.apply(this, arguments);

		this.onSelectValue = Ext.emptyFn;
		this.from = null;
		if ( arguments[0].onSelectValue && typeof arguments[0].onSelectValue == 'function' ) {
			this.onSelectValue = arguments[0].onSelectValue;
		}
		if(arguments[0]&&arguments[0].from){
			this.from = arguments[0].from;
		}
		this.defaultQueueFailCause_Code = arguments[0].defaultQueueFailCause_Code || null;
		this.enabledQueueFailCauseCodeList = arguments[0].enabledQueueFailCauseCodeList || null;
		this.disabledQueueFailCauseCodeList = arguments[0].disabledQueueFailCauseCodeList || null;
		
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		
		this.filterQueueFailCause();
		if (this.defaultQueueFailCause_Code > 0) {
			base_form.findField('QueueFailCause_id').setFieldValue('QueueFailCause_Code', this.defaultQueueFailCause_Code);
		} else if (this.from==null) {
			base_form.findField('QueueFailCause_id').setValue(1);
		}
		base_form.findField('QueueFailCause_id').focus(true, 250);
	},
	title: lang['prichina_otmenyi_napravleniya_iz_ocheredi'],
	width: 500
});