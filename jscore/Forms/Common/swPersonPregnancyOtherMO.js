/**
* swPersonPregnancyOtherMO - окно редактирования справочника Иное МО (для Хакасии)
*
*
* @package      
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Gilmiyarov Artur aka GAF (turken@yandex.ru)
* @version      04.12.2019
*/

sw.Promed.swPersonPregnancyOtherMO = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	width: 600,
	height: 600,
	formParams: null,
	modal: true,
	resizable: false,
	draggable: true,
	closeAction: 'hide',
	buttonAlign: 'left',
	title: 'Редактирование справочника Иное МО',
	id:'swPersonPregnancyOtherMO',
	Person_id: null,
	filterType: null,
	saveOnce: false,
	plain: true,
	action: 'add',
	onWinClose: function () {
	},
	deleteDiffLpu: function(viewframe) {
		var win = this;
		var grid = viewframe.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('LpuDifferent_id')) {
			return false;
		}
		if (record.get('LpuDifferent_id') < 10){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: "Удаление невозможно.",
				title: ERR_INVFIELDS_TIT
			});			
			return false;
		}

		var params = {LpuDifferent_id: record.get('LpuDifferent_id')};
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								viewframe.getAction('action_refresh').execute();
								var storegrid = win.ViewFrame.getGrid().getStore();
								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										var i=0;
										var record = new Ext.data.Record.create(win.ViewFrame.jsonData['store']);
										win.ViewFrame.getGrid().getStore().removeAll();
										response_obj.forEach(function(item) {
											storegrid.insert(i, new record(item));
											i++;
										});
										Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.getCategory("Anketa").getForm().findField('QuestionType_774').getStore().reload()
									}.createDelegate(this),
									url: '/?c=PersonPregnancy&m=getDifferentLpu',
								});
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=PersonPregnancy&m=deleteDifferentLpu'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	show: function (combo) {
		var storecombo = combo.getStore();
		var storegrid = this.ViewFrame.getGrid().getStore();
		var record = new Ext.data.Record.create(this.ViewFrame.jsonData['store']);
		storegrid.removeAll();
		k = 0;
		for (var i = 0; i < storecombo.getCount(); i++){
			item = storecombo.getAt(i);
			if (item && item.data['LpuDifferent_id'] != ''){
				storegrid.insert(k, new record(item.data));
				k++;
			}
		}

		sw.Promed.swPersonPregnancyOtherMO.superclass.show.apply(this, arguments);
	},
	initComponent: function () {
		var win = this;			
				
		win.ViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true},
				{ name: 'action_edit', hidden: true},
				{ name: 'action_view', hidden: true},
				{ name: 'action_delete', handler: function(){ win.deleteDiffLpu(win.ViewFrame)} },
				{ name: 'action_refresh', hidden: true},
				{ name: 'action_print', hidden: true},
			],
			border: true,
			uniqueId: true,
			autoLoadData: false,
			forcePrintMenu: true,
			dataUrl: '/?c=PersonPregnancy&m=getDifferentLpu',
			pageSize: 100,
			paging: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			scrollable: true,
			height: 500, 
			stringfields: [
				{name: 'LpuDifferent_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuDifferent_Name', header: 'Наименование', type: 'string', width: 500},
				{name: 'LpuDifferent_Code', header: 'Код', type: 'string', width: 60},
			],
			onRowSelect: function (sm, index, record) {
				win.ViewFrame.ViewActions.action_delete.setDisabled(false);
			},			
		});

		win.ViewFramePanel = new Ext.form.FormPanel({              
				//frame: true,
				autoHeight: true,
				//region: 'north',
				region: 'center',
				border: false,
				id: 'research_form',
				autoLoad: false,
				buttonAlign: 'left',
				bodyStyle: 'background:#FFF;padding:0;',
				items: [
					this.ViewFrame										
				]
		});                
                

		Ext.apply(this, {
			items: [
				this.ViewFramePanel
			],
			buttons: [
				{
					text: '-'
				},				
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function () {
						this.ownerCt.hide()
					},
					tabIndex: TABINDEX_PERSSEARCH + 21
				}
			]
		});

		sw.Promed.swPersonPregnancyOtherMO.superclass.initComponent.apply(this, arguments);
	}
});