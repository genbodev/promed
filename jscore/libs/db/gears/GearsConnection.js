 Ext.sql.GearsConnection = Ext.extend(Ext.sql.Connection, {
	// abstract methods
/*    open : function(db){
        this.conn = new air.SQLConnection();
		var file = air.File.applicationDirectory.resolvePath(db);
		this.conn.open(file);
        this.openState = true;
		this.fireEvent('open', this);
    },*/

	open : function(db){
			if (!window['gearsConnect'])
			{
				window['gearsConnect'] = google.gears.factory.create('beta.database', '1.0');
				window['gearsConnect'].open(db);
			}
			this.conn = window['gearsConnect'];
			/*
			this.conn.open(db);
			*/
			this.openState = true;
	//Ext.callback(cb, scope, [this]);
	this.fireEvent('open', this);
	},

/*	close : function(){
        this.conn.close();
        this.fireEvent('close', this);
    },*/

	close : function(){
        //this.conn.close();
        this.fireEvent('close', this);
    },

	/* must be rewriten createStatement : function(type){
		var stmt = new air.SQLStatement();
		stmt.sqlConnection = this.conn;
		return stmt;
	}, */

/*    exec : function(sql){
        var stmt = this.createStatement('exec');
		stmt.text = sql;
		stmt.execute();
    },*/

	exec : function(sql){
		this.conn.execute(sql).close();
//        Ext.callback(cb, scope, [true]);
    },

/*	execBy : function(sql, args){
		var stmt = this.createStatement('exec');
		stmt.text = sql;
		this.addParams(stmt, args);
		stmt.execute();
	},*/

	execBy : function(sql, args){
		this.conn.execute(sql, args).close();
    //    Ext.callback(cb, scope, [true]);
    },

	UexecBy : function(sql, args){
		this.conn.execute(sql, args);
    //    Ext.callback(cb, scope, [true]);
    },

/*	query : function(sql){
		var stmt = this.createStatement('query');
		stmt.text = sql;
		stmt.execute(this.maxResults);
		return this.readResults(stmt.getResult());
	},*/

	query : function(sql){
        var rs = this.conn.execute(sql);
        var r = this.readResults(rs);
//        Ext.callback(cb, scope, [r]);
        return r;
    },

/*	queryBy : function(sql, args){
		var stmt = this.createStatement('query');
		stmt.text = sql;
		this.addParams(stmt, args);
		stmt.execute(this.maxResults);
		return this.readResults(stmt.getResult());
	},*/

	queryBy : function(sql, args){
        var rs = this.conn.execute(sql, args);
        var r = this.readResults(rs);
//        Ext.callback(cb, scope, [r]);
        return r;
    },

    /* mast be added addParams : function(stmt, args){
		if(!args){ return; }
		for(var key in args){
			if(args.hasOwnProperty(key)){
				if(!isNaN(key)){
					var v = args[key];
					if(Ext.isDate(v)){
						v = v.format(Ext.sql.Proxy.DATE_FORMAT);
					}
					stmt.parameters[parseInt(key)] = v;
				}else{
					stmt.parameters[':' + key] = args[key];
				}
			}
		}
		return stmt;
	},*/

    /*readResults : function(rs){
        var r = [];
        if(rs && rs.data){
		    var len = rs.data.length;
	        for(var i = 0; i < len; i++) {
	            r[r.length] = rs.data[i];
	        }
        }
        return r;
    }*/

	readResults : function(rs){
        var r = [];
        if(rs){
            var c = rs.fieldCount();
            // precache field names
            var fs = [];
            for(var i = 0; i < c; i++){
                fs[i] = rs.fieldName(i);
            }
            // read the data
            while(rs.isValidRow()){
                var o = {};
                for(var i = 0; i < c; i++){
                    o[fs[i]] = rs.field(i);
                }
                r[r.length] = o;
                rs.next();
            }
            rs.close();
        }
        return r;
    }
});