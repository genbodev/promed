Ext.namespace("Ext.ux.grid.plugins");

Ext.ux.grid.plugins.GroupCheckboxSelection = {

	init: function(grid){

		grid.view.groupTextTpl =	
			'<input type="checkbox" ' +
			'class="x-grid-group-checkbox" x-grid-group-hd-text="{text}" /> ' +
			grid.view.groupTextTpl;
	
		grid.on('render', function() {
			Ext.ux.grid.plugins.GroupCheckboxSelection.initBehaviors(grid);
		});
	
		grid.view.on('refresh', function() {
			Ext.ux.grid.plugins.GroupCheckboxSelection.initBehaviors(grid);
		});
	},
	
	initBehaviors: function(grid) {
		var id = "#" + grid.id;
		var behaviors = {};

		// Check/Uncheck all items in group
		behaviors[id + ' .x-grid-group-hd .x-grid-group-checkbox@click'] =
			function(e, target){

				var ds = grid.getStore();
				var sm = grid.getSelectionModel();
				var cm = grid.getColumnModel();
				var checked = target.checked;

				var value = target.getAttribute("x-grid-group-hd-text");
				var field = grid.getStore().groupField;
				
				var records = ds.query(field, value).items;
				
				for(var i = 0, len = records.length; i < len; i++){
					var row = ds.indexOf(records[i]);
					if (checked) {
						sm.selectRow(row, true);
					}
					else {
						sm.deselectRow(row);
					}
				}
			};

		// Avoid group expand/collapse clicking on checkbox
		behaviors[id + ' .x-grid-group-hd .x-grid-group-checkbox@mousedown'] =
			function(e, target){
				e.stopPropagation();
			};
		
		Ext.addBehaviors(behaviors);
	}

}

