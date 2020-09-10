
Ext.ns( 'Ext.ux.grid');

Ext.ux.grid.RowExpander = Ext.extend( Ext.util.Observable, {

    expandOnEnter : true,

    expandOnDblClick : true,

    header           : '',
    width            : 24,
    sortable         : false,
    fixed            : true,
    menuDisabled     : true,
	name			 :'is_leaf',
	hiddenPrint: true,
    dataIndex        : '',
    id               : 'expander',
    lazyRender       : true,
    enableCaching    : true,
    actAsTree        : false,
    treeLeafProperty : 'is_leaf',
    appendRowClass   : true,
	hideable: false,

	constructor: function( config){
		if (!config.id) {
			config.id = Ext.id();
		}
		Ext.apply( this, config);

		var css =
			'.x-' + this.id + '-grid3-row-collapsed .x-grid3-row-expander { background-image:url("../img/grid/folded.png");background-position:0 0px; }' +
			'.x-' + this.id + '-grid3-row-collapsed .x-grid3-row-expander:hover { background-image:url("../img/grid/folded.png");background-position:0 -16px; }' +
			'.x-' + this.id + '-grid3-row-expanded .x-grid3-row-expander { background-image:url("../img/grid/folded.png"); background-position:0 -32px; }' +
			'.x-' + this.id + '-grid3-row-expanded .x-grid3-row-expander:hover { background-image:url("../img/grid/folded.png"); background-position:0 -48px; }' +
			'.x-' + this.id + '-grid3-row-collapsed .x-grid3-row-body { display:none !important; }' +
			'.x-' + this.id + '-grid3-row-expanded .x-grid3-row-body { display:block !important; }' +
			'.x-grid-expander-leaf .x-grid3-row-expander { background: none; }'
		;
		Ext.util.CSS.createStyleSheet( css, Ext.id());

		this.expanderClass     = 'x-grid3-row-expander';
		this.rowExpandedClass  = 'x-' + this.id + '-grid3-row-expanded';
		this.rowCollapsedClass = 'x-' + this.id + '-grid3-row-collapsed';
		this.leafClass         = 'x-grid-expander-leaf';

        this.addEvents({

            beforeexpand: true,

            expand: true,

            beforecollapse: true,

            collapse: true
        });

        Ext.ux.grid.RowExpander.superclass.constructor.call(this);

        if(this.tpl){
            if(typeof this.tpl == 'string'){
                this.tpl = new Ext.Template(this.tpl);
            }
            this.tpl.compile();
        }

        this.state = {};
        this.bodyContent = {};
    },

    getRowClass : function(record, rowIndex, p, ds){
       if(p){ p.cols = p.cols;
        var content = this.bodyContent[record.id];
        if(!content && !this.lazyRender){
            content = this.getBodyContent(record, rowIndex);
        }
        if(content){
            p.body = content;
        }
        var cssClass = this.state[record.id] ? this.rowExpandedClass : this.rowCollapsedClass;
        if (this.actAsTree) {
        	cssClass = this.leafClass;
        }
			/*log([1,cssClass]);
			cssClass+=this.gtr(record, rowIndex)
			log([2,cssClass]);*/
			var  css=this.gtr(record)
			css+=' '+cssClass;
        return css;}
    },

    init : function(grid){
        this.grid = grid;
		
        var view = grid.getView();
		Ext.applyIf(view, {templates: {}});
		view.templates.row = new Ext.Template(
			'<div class="x-grid3-row {alt}" style="{tstyle}"><table class="x-grid3-row-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
			'<tbody><tr>{cells}</tr>',
			'<tr class="x-grid3-row-body-tr" style="{bodyStyle}"><td class="x-grid3-col x-grid3-cell x-grid3-td-checker x-grid3-cell-first "></td><td colspan="{cols}" class="x-grid3-body-cell" tabIndex="0" hidefocus="on"><div class="x-grid3-row-body">{body}</div></td></tr>',
			'</tbody></table></div>'
        );

        grid.on( 'render',        this.onRender,        this);
        grid.on( 'destroy',       this.onDestroy,       this);

        view.on( 'beforerefresh', this.onBeforeRefresh, this);
        view.on( 'refresh',       this.onRefresh,       this);
    },

    // @private
    onRender: function() {
        var grid = this.grid;
        var mainBody = grid.getView().mainBody;
        mainBody && mainBody.on( 'mousedown', this.onMouseDown, this, {delegate: '.' + this.expanderClass});
        // grid.on('rowdblclick', this.onRowDblClick, this);
       var view = grid.getView();
	   this.gtr = view.getRowClass;
        view.getRowClass = this.getRowClass.createDelegate( this);
        view.enableRowBody = true;
        if (this.actAsTree) {
			grid.getEl().swallowEvent([ 'mouseout', 'mousedown', 'click', 'dblclick' ]);
        }
    },

    // @private
    onBeforeRefresh : function() {
    	var rows = this.grid.getEl().select( '.' + this.rowExpandedClass);
    	rows.each( function( row) {
    		this.collapseRow( row.dom);
    	}, this);
    },

    // @private
    onRefresh : function() {
    	var rows = this.grid.getEl().select( '.' + this.rowExpandedClass);
    	rows.each( function( row) {
    		Ext.fly( row).replaceClass( this.rowExpandedClass, this.rowCollapsedClass);
    	}, this);
    },

    // @private    
    onDestroy: function() {
        var mainBody = this.grid.getView().mainBody;
        mainBody && mainBody.un( 'mousedown', this.onMouseDown, this);
    },

    // @private
    onRowDblClick: function( grid, rowIdx, e) {
        this.toggleRow(rowIdx);
    },

    onEnter: function( e) {
        var g = this.grid;
        var sm = g.getSelectionModel();
        var sels = sm.getSelections();
        for (var i = 0, len = sels.length; i < len; i++) {
            var rowIdx = g.getStore().indexOf(sels[i]);
            this.toggleRow(rowIdx);
        }
    },

    getBodyContent : function( record, index){
        if (!this.enableCaching) {
            return this.tpl.apply( record.data);
        }
        var content = this.bodyContent[record.id];
        if (!content){
            content = this.tpl.apply( record.data);
            this.bodyContent[record.id] = content;
        }
        return content;
    },

    onMouseDown : function(e, t){
        e.stopEvent();
        var row = e.getTarget( '.x-grid3-row');
        this.toggleRow(row);
    },

    renderer : function(v, p, record){
       return '<div class="x-grid3-row-expander">&#160;</div>';
    },

    beforeExpand : function(record, body, rowIndex){
        if(this.fireEvent('beforeexpand', this, record, body, rowIndex) !== false){
            if(this.tpl && this.lazyRender){
                body.innerHTML = this.getBodyContent(record, rowIndex);
            }
            return true;
        }else{
            return false;
        }
    },

    toggleRow : function(row){
        if(typeof row == 'number'){
            row = this.grid.view.getRow(row);
        }
        if (Ext.fly(row).hasClass( this.leafClass)) {
        	return ;
        }
        this[Ext.fly(row).hasClass( this.rowCollapsedClass) ? 'expandRow' : 'collapseRow'](row);
    },

    expandRow : function( row){
        if(typeof row == 'number'){
            row = this.grid.view.getRow( row);
        }
        if (Ext.fly(row).hasClass( this.leafClass)) {
        	return ;
        }
        var record = this.grid.store.getAt( row.rowIndex);
        var body = Ext.DomQuery.selectNode( 'tr:nth(2) div.x-grid3-row-body', row);
        if (this.beforeExpand(record, body, row.rowIndex)){
            this.state[record.id] = true;
            Ext.fly( row).replaceClass( this.rowCollapsedClass, this.rowExpandedClass);
            this.fireEvent( 'expand', this, record, body, row.rowIndex);
        }
    },

    destroyNestedGrids : function( gridEl) {
		if (gridEl) {
			if (childGridEl = gridEl.child( '.x-grid-panel')) {
				this.destroyNestedGrids( childGridEl);
			}
			var grid = Ext.getCmp( gridEl.id);
			if (grid && (grid != this.grid)) {
				if (grid instanceof Ext.grid.EditorGridPanel) {
					var cm = grid.getColumnModel();
					for (var i = 0, s = cm.getColumnCount(); i < s; i++) {
						for (var ii = 0, ss = grid.getStore().getCount(); ii < ss; ii++) {
							if (editor = cm.getCellEditor( i, ii)) {
								editor.destroy();
							}
						}
					}
		        	cm.destroy();
		        }
				grid.destroy();
			}
		}
    },

    collapseRow : function( row){
        if (typeof row == 'number'){
            row = this.grid.view.getRow( row);
        }
        if (Ext.fly( row).hasClass( this.leafClass)) {
        	return ;
        }
	    var record = this.grid.store.getAt( row.rowIndex);
	    var body = Ext.fly( row).child( 'tr:nth(1) div.x-grid3-row-body', true);
	    if (this.fireEvent( 'beforecollapse', this, record, body, row.rowIndex) !== false) {
	    	this.destroyNestedGrids( Ext.get( row).child( '.x-grid-panel'));
	        if (record) this.state[record.id] = false;
	        Ext.fly( row).replaceClass( this.rowExpandedClass, this.rowCollapsedClass);
	        this.fireEvent( 'collapse', this, record, body, row.rowIndex);
	    }
    }
});

Ext.reg( 'rowexpander', Ext.ux.grid.RowExpander);

Ext.grid.RowExpander = Ext.ux.grid.RowExpander;
