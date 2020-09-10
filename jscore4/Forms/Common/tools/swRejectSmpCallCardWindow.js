/* 
	Выбор талона вызова для отказа
*/


Ext.define('sw.tools.swRejectSmpCallCardWindow', {
	alias: 'widget.swRejectSmpCallCardWindow',
	extend: 'Ext.window.Window',
	title: 'Отказ от вызова',
	id: 'swRejectSmpCallCardWindow',
	width: 1200,
	height: 400,
	modal: true,
	
	callback: Ext.emptyFn,
	
	//Метод фильтрации грида по введённым полям
	filterResults: function(){
		
		var win = this,
			form = win.down('form').getForm(),
			grid = win.grid,
			nameField = form.findField('filterByName'),
			famField = form.findField('filterByFamily'),			
			secNameField = form.findField('filterBySecName'),
			addressField = form.findField('filterByAddress'),
			CmpReason_id = form.findField('CmpReason_id'),
			filterConfig =  [
				{property: "Person_Surname", value: famField.getValue()},
				{property: "Person_Firname", value: nameField.getValue()},
				{property: "Person_Secname", value: secNameField.getValue()},
				{property: "Adress_Name", value: addressField.getRawValue()}
			];
		
		win.grid.store.load({
			params:{
				'Search_SurName': famField.getValue(),
				'Search_FirName': nameField.getValue(),
				'Search_SecName': secNameField.getValue()
			}
		});
								
		win.grid.store.clearFilter(false);
			
		if (CmpReason_id.getValue()) {
			//filterConfig.push({property: "CmpReason_id", value: CmpReason_id.getValue()});
		}
			
		grid.store.filter(filterConfig);
		
	},
	
	//Выбор талона для отказа
	selectRejectCallCard: function() {
		var selection = this.grid.getSelectionModel().getSelection();
		if ( selection.length == 0 ) {			
			Ext.Msg.alert('Ошибка','Не выбран ни один талон для отказа. Пожалуйста, выберите талон первичного вызова');
			return false;			
		}
		
		var win = this,
			form = win.down('form').getForm(),			
			data = selection[0].getData();
		
		Ext.Msg.prompt('Причина отказа', 'Введите причину отказа:', function(btn, text){			
			if ( Ext.isFunction( win.callback ) ) {				
				data.CmpReason_id = form.findField('CmpReason_id').getValue();				
				if (btn == 'ok'){
					data.CmpCallCardStatus_Comment = text;								
					win.callback( data );
				} else {
					return false;
				}
			}			
			win.close();
		});
		
	},
	
	//Активирует кнопку выбора первичного вызова, если выполнены условия
	checkEnabledSelectButton: function( ) {
		var enabled	 = ( this.grid.getSelectionModel().getSelection().length > 0);
		this.down('button[refId=selectButton]').setDisabled( !enabled );
		return enabled;
	},
	
	
	
	initComponent: function() {
		var win = this;
		
		win.commonComboStore = new Ext.data.JsonStore({
			storeId: this.id+'_comboCommonStore',
			fields: [
				{name: 'CmpCallCard_id', type: 'int'},
				{name: 'Person_id', type: 'int'},
				{name: 'Person_Surname', type: 'srting'},
				{name: 'Person_Firname', type: 'srting'},
				{name: 'Person_Secname', type: 'srting'},
				{name: 'CmpReason_id', type: 'int'},
				{name: 'Adress_Name', type: 'string'}
			]
		});
		
		win.filterFieldset = Ext.create('Ext.form.FieldSet',{
			xtype: 'fieldset',
			padding: '0 2 4 2',
			collapsible: true,
			title: 'Фильтры',
			layout: 'vbox',
			items: [
				{
					xtype: 'container',
					layout: 'hbox',
					padding: '0 2 4 2',
					items:[
						{
//							xtype: 'transFieldDelbut',
							xtype: 'textfield',
							fieldLabel: 'Фамилия',
							name: 'filterByFamily',
//							typeAheadDelay: 1,
							style: 'margin: 0 10px 0 0;',
							labelWidth: 60,
							width: 200,
							displayField: 'Person_Surname',
							storeName:  this.id+'_comboCommonStore',
							enableKeyEvents : true,
							listeners: {								
								keypress: function(c, e, o){
									if ( (e.getKey() == Ext.EventObject.ENTER)) {
										win.filterResults()
									}
								}
							}
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Имя',
							name: 'filterByName',
//							typeAheadDelay: 1,
							labelWidth: 30,
							width: 200,
							style: 'margin: 0 10px 0 0;',
							displayField: 'Person_Firname',
							storeName:  this.id+'_comboCommonStore',
							enableKeyEvents : true,
							listeners: {
								keypress: function(c, e, o){
									if ( (e.getKey() == Ext.EventObject.ENTER))
									{
										win.filterResults()
									}
								}
							}
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Отчество',
							name: 'filterBySecName',
//							typeAheadDelay: 1,
							labelWidth: 60,
							width: 200,
							displayField: 'Person_Secname',
							storeName: this.id+'_comboCommonStore',
							enableKeyEvents : true,
							listeners: {
								keypress: function(c, e, o){
									if ( (e.getKey() == Ext.EventObject.ENTER))
									{
										win.filterResults()
									}
								}
							}
						},
						{
							xtype: 'commonSprCombo',
							fieldLabel: 'Адрес',
							name: 'filterByAddress',
							cls: 'transFieldDelbut',
							store: null,
							labelWidth: 60,
							width: 300,
							labelAlign: 'right',
							fields: null,
							displayField: 'Adress_Name',
							valueField: 'CmpCallCard_id',
							storeName:  this.id+'_comboCommonStore',
							enableKeyEvents : true
						},
						{
							xtype: 'button',
							text: 'Найти',
							iconCls: 'search16',
							margin: '0 0 0 10',
							handler: function() {															
								win.filterResults();								
							}
						},
						{
							xtype: 'button',
							text: 'Сброс',
							iconCls: 'resetsearch16',
							margin: '0 0 0 10',
							handler: function() {
								win.grid.store.removeAll();
								form = win.down('form').getForm().reset();
							}
						}
					]
				},
				{
					xtype: 'container',
					layout: 'hbox',
					padding: '0 2 4 2',
					items:[
						{
							id: this.id+'_CmpReason_id',
							xtype:'cmpReasonCombo',
							name: 'CmpReason_id',
							labelWidth: 50,
							width: 600,
							tpl: Ext.create('Ext.XTemplate', 
								'<tpl for=".">' +
								'<div class="enlarged-font x-boundlist-item">' +
								'<font color="red">{CmpReason_Code}</font> {CmpReason_Name}'+
							'</div></tpl>'),
							listeners: {
//								change: function(combo, nV, oV){
//									var rec = combo.findRecordByValue(nV);
//									if (rec || !nV) {
//										win.filterResults()
//									}
//								}
							}
						}
					]
				}
			]
		})
		
		win.grid = Ext.create('Ext.grid.Panel', {
			flex: 1,
			stripeRows: true,
			id: win.id+'_grid',
			viewConfig: {
				loadingText: 'Загрузка'
			},
			listeners: {
				itemClick: function(cmp, record, item, index, e, eOpts ){
					var b = win.down('button[refId=selRec]');
					if (b){
						b.enable();
					}					
				},
				cellkeydown: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					 if (e.getKey() == e.ENTER){
						 win.selectRejectCallCard();
					 }
				},
				celldblclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					win.selectRejectCallCard();
				},
				selectionchange: function( grid, selected, eOpts ) {
					win.checkEnabledSelectButton();
				}
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				storeId: 'selectFirstSmpCallCardStore',
				fields: [
					{name: 'CmpCallCard_id', type: 'int'},
					{name: 'Person_id', type: 'int'},
					{name: 'PersonEvn_id', type: 'int'},
					{name: 'Server_id', type: 'int'},
					{name: 'Person_Surname', type: 'srting'},
					{name: 'Person_Firname', type: 'srting'},
					{name: 'Person_Secname', type: 'srting'},
					{name: 'pmUser_insID', type: 'srting'},
					{name: 'CmpCallCard_prmDate', type: 'string'},
					{name: 'CmpCallCard_Numv', type: 'string'},
					{name: 'CmpCallCard_Ngod', type: 'string'},
					{name: 'CmpCallCard_isLocked', type: 'int'},
					{name: 'Person_FIO', type: 'string'},
					{name: 'Person_Birthday', type: 'string'},
					{name: 'CmpReason_Name', type: 'string'},
					{name: 'CmpReason_id', type: 'int'},
					{name: 'CmpCallType_Name', type: 'string'},
					{name: 'CmpGroup_id',type: 'int'},
					{name: 'CmpGroupName_id', type: 'int'},
					{name: 'CmpLpu_Name', type: 'string'},
					{name: 'CmpDiag_Name', type: 'string'},
					{name: 'StacDiag_Name', type: 'string'},
					{name: 'SendLpu_Nick', type: 'string'},
					{name: 'PPDUser_Name', type: 'string'},
					{name: 'ServeDT', type: 'string'},
					{name: 'PPDResult',	type: 'string'},
					{name: 'Adress_Name', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadSMPCmpCallCardsList',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					},
					extraParams:{
						begDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y'),
						endDate: Ext.Date.format(new Date(Date.now()), 'd.m.Y')
						
					}
				},
				filters: [{
					property: 'CmpGroup_id',
					value: 2||3
				}],
				listeners: {
					load: function( store, records, successful, eOpts ){
						if ( !records ) {
							return;
						}
						win.commonComboStore.loadData(records);
						win.grid.getSelectionModel().select(0);
					}
				}
			}),
			columns: [
				{ dataIndex: 'Person_Surname', text: 'Фамилия', flex: 1, hideable: false  },
				{ dataIndex: 'Person_Firname', text: 'Имя', flex: 1, hideable: false  },
				{ dataIndex: 'Person_Secname', text: 'Отчество', flex: 1, hideable: false  },
				{ dataIndex: 'CmpCallCard_Numv', text: '№ вызова (день)', width: 120, hideable: false  }, 
				{ dataIndex: 'CmpReason_Name', text: 'Повод', flex: 2, hideable: false },
				{ dataIndex: 'Adress_Name', text: 'Место', flex: 3, hideable: false  }		
			]
		})
		
		
		Ext.apply(this,{
			buttonAlign:'right',
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			items: [
				{
					xtype: 'BaseForm',
					id: this.id+'BaseForm',
					layout: {
						align: 'stretch',
						type: 'vbox'
					},
					items: [
						win.filterFieldset,
					]
				},
				win.grid
			],
			buttons: [
				{
					text: 'Выбрать',
					iconCls: 'ok16',
					disabled: true,
					refId: 'selectButton',
					handler: function(){
						win.selectRejectCallCard();
					}
				},
				'->',
				{
				xtype: 'button',
					text: 'Помощь',
					//margin: '0 5 0 0',
					iconCls   : 'help16',
					handler   : function()
					{
						ShowHelp(this.up('window').title);								
					}
				},
				{
					xtype: 'button',								
					iconCls: 'cancel16',
					refId: 'cancelButton',
					text: 'Закрыть',
					//margin: '0 5',
					handler: function(){
						win.close()
					}
				}
			]
		})
		
		win.callParent(arguments);
	}
})

