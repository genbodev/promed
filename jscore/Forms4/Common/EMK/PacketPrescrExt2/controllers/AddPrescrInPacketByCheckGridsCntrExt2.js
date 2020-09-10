Ext6.define('common.EMK.PacketPrescrExt2.controllers.AddPrescrInPacketByCheckGridsCntrExt2', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.AddPrescrInPacketByCheckGridsCntrExt2',
	//data: {},
	onCancel: function(){
		var cntr = this,
			view = cntr.getView(),
			parentPanel = view.parentPanel;
		if(parentPanel)
			parentPanel.close();
			//parentPanel.setMode('my');
	},
	expandCollapseAll: function(pressed) {
		var cntr = this,
			allrowexpander,
			view = cntr.getView(),
			toolbar = view.lookup('tbar'),
			grids = view.query('grid');

		//toolbar.disable();
		view.mask('...');
		for(var i=0;i<grids.length;i++){

			grids[i].mask('...');
			allrowexpander =  grids[i].getPlugin('allrowexpander');
			if (!pressed)
				allrowexpander.expandAll();
			else
				allrowexpander.collapseAll();
			grids[i].unmask();

		}
		view.unmask();
		//toolbar.enable();
	},
	loadStores: function(grids,data)
	{
		var cntr = this,
			view = cntr.getView();
		if (grids.length > 0) {
			var grid = grids.pop();
			var params = Ext6.apply({}, grid.params, {
				CureStandart_id: data.CureStandart_id,
				PacketPrescr_id: data.PacketPrescr_id,
				Evn_pid: data.Evn_id,
				parentEvnClass_SysNick: 'EvnSection',
				newWndExt6: true,
				objectPrescribe: grid.objectPrescribe
			});
			grid.getStore().load({
				params: params,
				callback: function(records, operation, success) {
					//grid.setVisible(records && records.length>0);
					cntr.loadStores(grids,data)
				}
			});
		}
		else{
			view.unmask();
			cntr.selectAllItems();
		}
	},
	loadGrids: function(data) {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid');
		cntr.data = data;
		view.mask('Загрузка назначений');
		cntr.loadStores(grids,data);
	},
	selectAllItems: function(rec) {
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid'),
			countAll = 0;
		cntr.selectAllProcess = true;
		for(var i=0;i<grids.length;i++){
			countAll += grids[i].getStore().getCount();
			grids[i].getSelectionModel().selectAll();
		}
		view.setCount(countAll);
		cntr.selectAllProcess = false;
	},
	loadData: function(data) {
		var me = this;
		this.data = data;
		me.loadGrids();
	},
	getCitoClass: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'grid-header-icon-cito';
		} else {
			return 'grid-header-icon-empty';
		}
	},
	getCitoTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isCito';
		} else {
			return 'notCito';
		}
	},
	getDirectionClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrLabDiag':
				if (rec.get('EvnDirection_id')) {
					return 'grid-header-icon-direction';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getDirectionTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isDirection';
		} else {
			return 'noDirection';
		}
	},
	getOtherMOClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			/*case 'EvnCourseTreat':
				if (rec.get('EvnPrescr_IsCito') > 1) {
					return 'grid-header-icon-otherMO';
				} else {
					return 'grid-header-icon-empty';
				}
				break;*/
			case 'EvnPrescrLabDiag':
				if (rec.get('otherMO') > 1) {
					return 'grid-header-icon-otherMO';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getOtherMOTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isOtherMO';
		} else {
			return 'notOtherMO';
		}
	},
	getSelectDTClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrLabDiag':
				switch(rec.get('EvnStatus_SysNick')) {
					case 'Queued':
						return 'grid-header-icon-queued';
						break;
					case 'DirZap':
						return 'grid-header-icon-selectDT';
						break;
					default:
						return 'grid-header-icon-needSelectDT';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getSelectDTTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isSelectDT';
		} else {
			return 'noSelectDT';
		}
	},
	getResultsClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			/*case 'EvnCourseTreat':
				if (rec.get('EvnPrescr_IsCito') > 1) {
					return 'grid-header-icon-results';
				} else {
					return 'grid-header-icon-empty';
				}
				break;*/
			default:
				return 'grid-header-icon-empty';
		}
	},
	getResultsTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'isResults';
		} else {
			return 'notResults';
		}
	},
	setTitleCounterGrids: function(panel){
		var cntr = this,
			params,
			view = cntr.getView(),
			grids = view.query('grid'),
			count = 0;

		for(var i=0;i<grids.length;i++){
			count += grids[i].getStore().getCount();
		}
		if(count > 0)
			panel.setTitleCounter(count);
	},
	setEditMode: function(model, record){
		if(this.selectAllProcess)
			return false;
		this.setSelectedPrescrCount();
	},
	setEditModeSave: function(model, record){
		if(this.selectAllProcess)
			return false;
		this.setSelectedPrescrCount();
	},
	setSelectedPrescrCount: function(model, record){
		var cntr = this,
			view = cntr.getView(),
			grids = view.query('grid'),
			countSel = 0;
		for(var i=0;i<grids.length;i++){
			if(grids[i].prescribeGridType && grids[i].prescribeGridType == 'EvnPrescribeAdd')
				countSel += grids[i].getSelectionModel().getCount();
		}
		view.setCount(countSel);
	}
});