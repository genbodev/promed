/**
* swSmpCallRangeGlossaryWindow - СМП справочник срочности вызова
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Miyusov Alexandr
* @version      17.10.2012
*/

sw.Promed.swSmpCallRangeGlossaryWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	width: 600,
	height: 500,
	autoHeight: true,
	resizable: false,
	plain: false,
	closable: false,
	title: lang['smp_spravochnik_srochnosti_vyizova'],
	
	doSave: function(){
		var res = [],
			params;
		this.CmpRangeReason.ViewGridStore.findBy(function(rec, id){
			if(rec.dirty){				
				res.push(rec.data)
			}			
		});

		params = {SmpCallRange: Ext.encode(res)};

		this.FormPanel.getForm().submit({
			params: params,
			failure: function(result_form, action){
				
			},
			success: function(result_form, action) {
				this.CmpRangeReason.ViewGridStore.reload();
			}.createDelegate(this)
		})	
		//console.log(res);
	},
	
	initComponent: function()
	{
		var me = this;
		
		var gridFields = [
			{ name: 'CmpReason_id', type: 'int', header: 'ID', key: true },
			{ name: 'CmpReasonRange_id', header: lang['povod'] , width: 300, hidden: true},
			{ name: 'CmpReason_Code', header: lang['kod_povoda'], width: 100},
			{ name: 'CmpReason_Name', header: lang['povod'] , width: 300},
			{ name: 'CmpReasonRange_Value', header: lang['koeffitsient_srochnosti'], editor: new Ext.form.TextField({
				regex: new RegExp(/^[1-9]+$/),
				plugins: [ new Ext.ux.InputTextMask('9', true) ]
			}), width: 150 }
		];
		
		var gridActions = [
			{ name: 'action_add', handler: function(){}.createDelegate(this), hidden: true },
			{ name: 'action_edit', handler: function(){}.createDelegate(this), disabled: true },
			{ name: 'action_view', disabled: true, hidden: true },
			{ name: 'action_delete', handler: function(){ }.createDelegate(this), disabled: true, hidden: true },
			{ name: 'action_refresh', disabled: true, hidden: true },
			{ name: 'action_print', disabled: true, hidden: true },
			{ name: 'action_save', disabled: true, hidden: true }
		];

		me.CmpRangeReason = new sw.Promed.ViewFrame({
			actions: gridActions,
			autoLoadData: true,
			autoexpand: 'expand',
			border: true,
			dataUrl: '/?c=CmpCallCard&m=getCmpRangeReasonList',
			height: 400,
			id: this.id+'CmpRangeReasonList',
			region: 'center',
			saveAtOnce: false,
			selectionModel: 'cell',
			stringfields: gridFields,
			onLoadData: function(){
			},
			addEmptyRow: function() {
			},
			deleteRow: function() {
			},
			onCellSelect: function(sm,rowIdx,colIdx){
			},
			editSelectedCell: function(){
			}
		});
		
		
		
		this.FormPanel = new sw.Promed.FormPanel(
		{
			region: 'center',
			url: '/?c=CmpCallCard&m=saveCmpRangeReasonList',
			items:
			[
				me.CmpRangeReason                  
			]
		});  
                
		Ext.apply(this,{
		buttonAlign : "left",
		buttons :
			[
				{
					text : lang['sohranit'],
					id: 'close',
					iconCls: 'save16',
					handler : function()
					{
						this.doSave();
					}.createDelegate(this)
				},
				{
					text: "-"
				},
				HelpButton(this, -1),
				{
					text : lang['zakryit'],
					iconCls: 'close16',
					handler : function(button, event) {
						button.ownerCt.hide();
					}
				}
			],
			items: [ this.FormPanel ]
		});
                
		sw.Promed.swSmpCallRangeGlossaryWindow.superclass.initComponent.apply(this, arguments);
	}
		

		
});






