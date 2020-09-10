Ext.ns( 'Ext.ux.grid');

Ext.ux.grid.FilterRow = Ext.extend( Ext.util.Observable, {

	remoteFilter:true,
	id:null,
	group:true,

	parId:null,
	hiddenPrint: true,
	vFilter:false,
	hidden: false,
	value:1,
	hideable: false,
	sort:true,
	clearFilterBtn: true,

	type: 'string',
	name:'flt',
	width:24,
	header:'filterRow',
	constructor: function(config){

		if (!config.id) {config.id = Ext.id(); }
		Ext.apply(this, config);

		this.addEvents('change','search');

		Ext.ux.grid.FilterRow.superclass.constructor.call(this);
	},
	init: function(grid) {

		this.grid = grid;
		var view = grid.getView();

		this.grid.addClass('filter-row');

		var rawHtml = '<table id="'
			+grid.id+'-header" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">'
			+'<thead><tr class="x-grid3-hd-row  ">{cells}</tr><tr class="filterInput" id="'
			+grid.id+'-filter"></tr></thead>'+"</table>";

		var headerTpl = new Ext.Template(rawHtml);

		Ext.applyIf(view, {templates: {}});
		view.templates.header = headerTpl;

		view.templates.hcell = new Ext.XTemplate(
			'<tpl if="value!=\'filterRow\'"><td class="x-grid3-hd x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
			'<div {tooltip} {attr} class="x-grid3-hd-inner x-grid3-hd-{id}" unselectable="on" style="{istyle}">',
			'<span style="cursor:hand">{value}</span>',
			'<a class="x-grid3-hd-btn" href="#"></a>',
			'</div>',
			'</td></tpl>',
			'<tpl if="value==\'filterRow\'"><td onclick="Ext.getCmp(\''+this.parId+'\').'+this.id+'.setVisible()" class="x-grid3-hd x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
			'</td></tpl>'
		);

		Ext.applyIf(view, {templates: {}});
		view.templates.header = headerTpl;

		grid.on('resize', this.syncFields, this);
		grid.on('columnresize', this.syncFields, this);
		grid.on('render', this.renderFields, this);

		grid.getColumnModel().on('hiddenchange', this.renderFields.createDelegate(this));
		// private
		var FilterRow = this;
		view.updateHeaders = function(){
			this.innerHd.firstChild.innerHTML = this.renderHeaders();
			this.innerHd.firstChild.style.width = this.getOffsetWidth();
			this.innerHd.firstChild.firstChild.style.width = this.getTotalWidth();
			FilterRow.renderFields(false);
		};
		Ext.apply(grid, {
			enableColumnHide_: false,
			enableColumnMove: true
		});
		this.setVisible();

	},
	setVisible:function(){
		if(this.vFilter){
			Ext.select('.filterInput').setStyle('display','none');
			this._search(true);
			this.vFilter=false;
		}else{
			Ext.select('.filterInput').setStyle('display','table-row');
			this.vFilter=true;
		}
	},
	/**
	 * Returns DOM Element that is the root element of form field.
	 *
	 * <p>For most fields, this will be the "el" property, but
	 * TriggerField and it's descendants will wrap "el" inside another
	 * div called "wrap".
	 *
	 * @return {HTMLElement}
	 */
	getFieldDom: function() {
		return this.field.wrap ? this.field.wrap.dom : this.field.el.dom;
	},
	onKeyDown: function(inp,e) {
		this.grid.blockKeyEvents = true;

		var el = inp.getEl();
		if(e.getKey() == e.ENTER) {

			// при использовании с комбобоксом, работало некоректно если у комбика раскрыт выпадающий список
			var columns = this.grid.getColumnModel().config;
			for(var i = 0; i < columns.length; ++i) {
				var filter = columns[i].filter;
				if(filter && filter.isExpanded && filter.isExpanded()) {
					return;
				}
			}

			this._search()
		}
	},
	renderFields: function() {
		var html = '';
		var filter = this;

		var grid = this.grid;
		var cols = grid.getColumnModel().config;

		var hasClear = (this.clearFilterBtn)
			? '<div class="filterRow-clearFilter" id="'+ grid.id +'-filter-clearfilter"><a data-qtip="Очистить" href="javascript://" onClick="Ext.getCmp(\''+this.parId+'\').'+this.id+'.clearFilters();">X</a></div>'
			: '';

		Ext.each(cols, function(col, k) {

			if (!col.hidden) {

				var colId = grid.id +'-filter-' + col.id;
				var colStyle = (col.filterStyle ? col.filterStyle : "");

				html += '<td><div id="' + colId + '" style="' + colStyle + '">';
				if (k === cols.length - 1){ html += hasClear }
				html += '</div></td>';
			}

		}, this);

		var template =  new Ext.Template(html);
		var filterEl = Ext.get(grid.id + "-filter");

		if (filterEl) {

			template.overwrite(grid.id + "-filter", null);

			Ext.each(cols, function(col) {

				if (!col.hidden) {

					var editor = col.filter;
					if (editor) {

						var colId = grid.id +'-filter-' + col.id;
						var colDivEl = Ext.get(colId);

						if (editor.rendered) {

							colDivEl.appendChild(
								editor.wrap ? editor.wrap.dom : editor.el.dom
							);

							editor.on("keydown", this.onKeyDown, this);

						} else {

							if (Ext.isIE){
								col.filter = editor = editor.cloneConfig({value:editor.getValue()});
							}

							if (editor.baseCls == 'x-form-check') {
								editor.on("check", function() {filter._search(); });
							}

							var panel = new Ext.Panel({
								border: false,
								layout: 'fit',
								items: editor,
								renderTo:colId
							});

							editor.on("keydown", this.onKeyDown, this);
						}
					}
				}
			}, this);
		} else {
			log('can`t template.overwrite, no ' + grid.id + "-filter");
		}
	},
	getFilter: function (name) {
		var params = {};
		var grid = this.grid;
		var cm = grid.getColumnModel();
		var cols = cm.config;
		var filter = null;
		Ext.each(cols, function (col) {
			if (col.filter && col.filter.name && col.filter.name == name) {
				filter = col.filter;
			}
		});
		return filter;
	},
	getFilters: function () {
		var params = {};
		var grid = this.grid;
		var cm = grid.getColumnModel();
		var cols = cm.config;
		Ext.each(cols, function (col) {
			if (col.filter) {
				if (col.filter.baseCls == 'x-form-check') {
					params[col.filter.name] = (col.filter.checked) ? 2 : '';
				} else {
					params[col.filter.name] = col.filter.getValue();
				}
			}
		});
		return params;
	},
	clearFilters: function() {
		this._search(true);
	},
	_search:function(hide){

		var grid = this.grid;
		var cm = grid.getColumnModel();
		var cols = cm.config;
		var params = {};
		Ext.each(cols, function(col) {
			if(col.filter){
				if(hide){
					if(col.filter.baseCls=='x-form-check'){
						col.filter.setValue(false);
						params[col.filter.name]=''
					}else{
						col.filter.setValue('');
						params[col.filter.name]=col.filter.getValue();
					}
				}else{
					if(col.filter.baseCls=='x-form-check'){
						params[col.filter.name]=(col.filter.checked)?2:'';
					}else{
						params[col.filter.name]=col.filter.getValue();
					}
				}
			}
		});

		this.fireEvent("search", params);
	},
	syncFields: function() {
		var grid = this.grid;
		var cm = grid.getColumnModel();
		var cols = cm.config;
		Ext.each(cols, function(col) {
			if (!col.hidden) {
				var editor = col.filter;
				if (editor) {
					editor.lastSize = null;
					editor.setSize(col.width-2);
				}else if(col.clearFilter && col.clearFilter.setSize){
					col.clearFilter.setSize(col.width - 10);
				}
			}
		});
	}
});

Ext.reg('filterrow', Ext.ux.grid.FilterRow);
Ext.grid.FilterRow = Ext.ux.grid.FilterRow;

