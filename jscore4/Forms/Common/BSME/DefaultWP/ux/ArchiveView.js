Ext.define('common.BSME.DefaultWP.ux.ArchiveView',{
	extend: 'Ext.panel.Panel',
	//extend: 'Ext.view.View',
	hidden: true,
	columns: [],
	store: {},	
	tpl: [],
	displayFields: [],
	searchField:{},
	toolbarItems: null,
	getStore: function(){
		return this.store;
	},	
	initComponent: function() {
		var me = this;
		
		me.archiveDataView = Ext.create('Ext.view.View', {
			overflowY: 'scroll',
			loadingText: 'Загрузка',
			flex: 1,
			width: '100%',
			height: '100%',
			itemSelector: 'div.archiveEvnSubViewCell',
			preserveScrollOnRefresh: true,
			tpl:  me.tpl,
			store: me.store,
			columns: me.columns
		});
		
		me.datePickerRange = Ext.create('sw.datePickerRange', {
			dateFields: ['dateFinish', 'dateStart'],
			dateFrom: Ext.Date.add(new Date(), Ext.Date.DAY, -new Date().getDay()+1),
			hidden: true
		});
		
		me.datePickerRange.on('setInterval', function(dateFrom, dateTo){
			me.filterByDate(dateFrom, dateTo);
		});
		
		me.dateRangeSplitbutton = Ext.create('Ext.button.Split', {
			text: 'Период: ',
			cls: 'range-splitbutton',
			menu: Ext.create('Ext.menu.Menu', {
				showSeparator: false,
				plain: true,
				items:[			
					{ text: 'За все время', handler: function(){ me.store.load(); me.datePickerRange.setVisible(false); } },
					{ text: 'За день', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentDay();} },
					{ text: 'За неделю', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentWeek(); } },
					{ text: 'За месяц', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.currentMonth(); } },
					{ text: 'За период', handler: function(){ me.datePickerRange.setVisible(true); me.datePickerRange.expand()} }					
				]			
			}),
			listeners: {
				click: function(c){
					c.showMenu();
				}
			}
		});

		//комбобокс фильтров по колонкам
		me.searchListCombobox = Ext.create('swFindBSMEDCombo', {
			name: 'searchComboGrid',
			forceSelection: true,
			hideTrigger: true,
			autoFilter: false,
			width: 350,
			forceSelection: false,
			triggerFind: true,
			onTrigger3Click: function(e) {
				me.searchListCombobox.clearValue();
				me.searchListCombobox.collapse();
				me.store.load();
			},
			onTrigger2Click: function(e) {},
			listeners: {
				keydown: function(c,e){
					if(e.getKey()==13){}
				}.bind(this),
				select: function(c,r){
					var st = me.store,
						pr = st.proxy,
						rec = r[0].data;
					
					st.load({
						params:{
							filterField: rec.fieldName,
							filterVal: rec.val
						}
					});
				},
				change: function(c,n){
					var cols = (me.columns.length>0)?me.columns:me.displayFields;
					
					c.store.removeAll();
					for(var i in cols){
						c.store.add({
							'val': n,
							'dir': cols[i].text,
							'fieldName': cols[i].dataIndex
						})							
					}				
				}.bind(this)
			}
		});
		
		if (!me.toolbarItems) {
			me.toolbarItems = [
				//me.searchField,
				me.searchListCombobox,
				me.printButton,
				me.exportButton,
				'->',
				me.datePickerRange,
				me.dateRangeSplitbutton
			];
		}
		
		Ext.apply(me,{
			store: me.store, 
			layout: {
				type: 'vbox'
			},
			items: [
				this.archiveDataView
			],
			dockedItems: [{
				dock: 'top',
				xtype: 'toolbar',
				items: me.toolbarItems
			},{
				xtype: 'pagingtoolbar',
				cls: 'paginator',
				store: me.store,
				dock: 'bottom',
				displayInfo: true
			}]
		})
		
		me.callParent(arguments);
	},
	filterByDate: function(dateFrom, dateTo){
		this.store.load({
			params:{
				begDate: Ext.Date.format(dateFrom, 'd.m.Y'),
				endDate: Ext.Date.format(dateTo, 'd.m.Y')
			}
		});
	}
})

