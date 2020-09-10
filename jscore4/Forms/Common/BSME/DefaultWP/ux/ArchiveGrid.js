Ext.define('common.BSME.DefaultWP.ux.ArchiveGrid',{
	extend: 'Ext.panel.Panel',
	height: '100%',
	width: '100%',
	columns: [],
	store: {},	
	searchField:{},
	exportButton: null, //Кнопка "Экспорт в ДБФ"
	getStore: function(){
		return this.store;
	},
	clearFilter: function() {
		var me = this;
		
		me.EvnForensicNumSearchField.reset();
		me.PersonFioSearchField.reset();
		me.MedPersonalSearchCombo.reset();
	},
	//Переключиться на главный фрейм и отобразить заявку
	_showEvnInMainFrame: function(rec) {
		var me = this;
		
		if (!rec || !Ext.isFunction(rec.get)) {
			return false;
		}
		
		var EvnForensic_id = rec.get(me.getStore().idProperty),
			EvnForensic_Num = rec.get('EvnForensic_Num');
		
		if (!EvnForensic_id || !EvnForensic_Num) {
			return false;
		}
		
		var wnd = me.up('window');
		
		if (!wnd) {
			return false;
		}
		
		//setRawValue не вызывает 'change'
		wnd.SearchForm.getForm().reset().findField('EvnForensic_Num').setRawValue(EvnForensic_Num);

		
		wnd.centerPanel.items.findBy(function(el){
			if ((typeof el.getXType == 'function') && (el.getXType() == 'tabpanel')) {
				el.setActiveTab('All');
			}
		});
		
		wnd.loadRequestViewStore({
			aftercallback: function() {
				var selrec = wnd.RequestListDataview.getStore().findRecord( 'EvnForensic_id' ,EvnForensic_id);
				if (selrec) {
					wnd.RequestListDataview.getSelectionModel().select([selrec]);
					wnd.requestListViewItemClick(selrec);
					wnd.updateRequestView(EvnForensic_id);
				}
			}
		});
	},
	initComponent: function() {
		var me = this;
		
		me.store.on('load', function(){
			me.returnToWorkButton.setDisabled(true);
			me.xmlVersionsButton.setDisabled(true);
		});
		
		me.archiveGrid = Ext.create('Ext.grid.GridPanel',{
			loadingText: 'Загрузка',
			flex: 1,
			width: '100%',
			height: '100%',
			preserveScrollOnRefresh: true,
			store: me.store,
			columns: me.columns,
			plugins: Ext.create('Ext.grid.plugin.CellEditing', {
				clicksToEdit: 2
			}),
			listeners: {
				'selectionchange': function(grid, selection) {
					me.returnToWorkButton.setDisabled( selection.length != 1 );
					me.xmlVersionsButton.setDisabled( selection.length != 1 );
				}
			}
		});
		
		me.datePickerRange = Ext.create('sw.datePickerRange', {
			dateFields: [ 'begDate', 'endDate'],
			dateFrom: Ext.Date.add(new Date(), Ext.Date.DAY, -new Date().getDay()+1),
			setExtraParams: true
		});
				
		me.dateRangeSplitbutton = Ext.create('Ext.button.Split', {
			text: 'Период: ',
			cls: 'range-splitbutton',
			menu: Ext.create('Ext.menu.Menu', {
				showSeparator: false,
				plain: true,
				items:[			
					{ text: 'За все время', handler: function(){ me.datePickerRange.clearDate(); me.datePickerRange.setVisible(false);} },
					{ text: 'За день', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentDay();} },
					{ text: 'За неделю', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentWeek();} },
					{ text: 'За месяц', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentMonth();} },
					{ text: 'За период', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.expand();} }					
				]			
			}),
			listeners: {
				click: function(c){
					c.showMenu();
				}
			}
		});

		me.EvnForensicNumSearchField = Ext.create('Ext.form.TextField',{
			fieldLabel: '№',
			name: 'EvnForensic_Num',
			labelWidth: '25px',
			width:  75,
			listeners: {
				change: function(field, nV, oV) {
					me.getStore().getProxy().setExtraParam(field.name,nV);
					me.getStore().abort().load();
				}
			}
		});
		
		me.PersonSurNameSearchField = Ext.create('Ext.form.TextField',{
			fieldLabel: 'ФИО',
			emptyText: 'Фамилия',
			name: 'Person_SurName',
			labelWidth: '40px',
			width:  175,
			listeners: {
				change: function(field, nV, oV) {
					me.getStore().getProxy().setExtraParam(field.name,nV);
					me.getStore().abort().load();
				}
			}
		});
		
		me.PersonFirNameSearchField = Ext.create('Ext.form.TextField',{
			emptyText: 'Имя',
			name: 'Person_FirName',
			labelWidth: '15px',
			width:  150,
			listeners: {
				change: function(field, nV, oV) {
					me.getStore().getProxy().setExtraParam(field.name,nV);
					me.getStore().abort().load();
				}
			}
		});
		
		me.PersonSecNameSearchField = Ext.create('Ext.form.TextField',{
			emptyText: 'Отчество',
			name: 'Person_SecName',
			labelWidth: '15px',
			width:  150,
			listeners: {
				change: function(field, nV, oV) {
					me.getStore().getProxy().setExtraParam(field.name,nV);
					me.getStore().abort().load();
				}
			}
		});
		
		me.MedPersonalSearchCombo = Ext.create('sw.MedPersonalExpertsCombo',{
			fieldLabel: 'Эксперт',
			labelWidth: '50px',
			width:  360,
			name: 'MedPersonal_eid',
			listeners: {
				change: function(combo, nV, oV) {
					me.getStore().getProxy().setExtraParam(combo.name,nV);
					me.getStore().load();
				}
			}
			
			// Невыясненный баг ExtJS: у следующего после комбобокса элемента
			// атрибут left высчитывается неверно, в результате чего, этот 
			// элемент частично "наезжает" на комбобокс
			//padding: '0 40 0 0',
			//margin: '0 40 0 0'
		});
		
		me.MedPersonalSearchCombo.getStore().load();
		
		me.returnToWorkButton = Ext.create('Ext.button.Button',{
			disabled: true,
			xtype: 'button',
			text: 'Вернуть в работу',
			handler: function() {
				var sel = me.archiveGrid.getSelectionModel().getSelection();
				if (!sel.length) {
					return false;
				}
				
				var rec = sel[0];
				var loadMask =  new Ext.LoadMask(me.up('window'), {msg:"Пожалуйста, подождите, идёт возвращение заявки в работу..."}); 
				loadMask.show();
				
				Ext.Ajax.request({
					url: '/?c=BSME&m=revisionEvnForensic',
					params: {
						EvnForensic_id: rec.get(me.archiveGrid.getStore().idProperty),
						EvnXml_id: rec.get('EvnXml_id')
					},
					callback: function(params,success,result) {
						if (result.status !== 200) {
							loadMask.hide();
							Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
							return false;
						} 

						var resp = Ext.JSON.decode(result.responseText, true);
						if (resp === null || resp.Error_Msg) {
							loadMask.hide();
							Ext.Msg.alert('Ошибка', resp.Error_Msg || 'Ошибка обработки запроса');
							return false;
						}
						
						loadMask.hide();
						
						me._showEvnInMainFrame(rec);
						
					}
				})
				
			}
		});
		
		me.xmlVersionsButton = Ext.create('Ext.button.Button',{
			text: 'Версии документа',
			xtype: 'button',
			disabled: true,
			handler: function() {
				var sel = me.archiveGrid.getSelectionModel().getSelection();
				if (!sel.length) {
					return false;
				}
				
				var rec = sel[0];
				Ext.create('common.BSME.tools.swBSMEXmlVersionListWindow',{
					EvnForensic_id: rec.get(me.archiveGrid.getStore().idProperty)
				});
			}
		})
		
		
		me.firstToolbar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			xtype: 'toolbar',
			defaults: {
				padding: '0 5 0 0'
			},
			items:[
				me.printButton,
				//me.exportButton,
				me.returnToWorkButton,
				me.xmlVersionsButton,
				'->',
				me.datePickerRange,
				me.dateRangeSplitbutton
			]
		})
		
		me.secondToolbar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			xtype: 'toolbar',
			defaults: {
				padding: '0 5 0 0'
			},
			items:[
				me.EvnForensicNumSearchField,
				me.MedPersonalSearchCombo,
				me.PersonSurNameSearchField,
				me.PersonFirNameSearchField,
				me.PersonSecNameSearchField,
			]
		})
		
		Ext.apply(me,{
			store: me.store, 
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [
				me.archiveGrid
			],
			dockedItems: [
			me.firstToolbar,
			me.secondToolbar,
			{
				xtype: 'pagingtoolbar',
				cls: 'paginator',
				store: me.store,
				dock: 'bottom',
				displayInfo: true
			}]
		})
		
		me.callParent(arguments);
		
		me.datePickerRange.currentDay();
	}
})

