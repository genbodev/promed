Ext6.define('usluga.associatedwindows.uslugaResultWindow', {
	alias: 'widget.uslugaResultWindow',
	extend: 'base.BaseForm',
	
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	maximized: false,
	width: 800,
	height: 550,
	modal: true,
	cls: 'arm-window-new usluga-result-window',
	noTaskBarButton: true,
	resizable: true,
    title: 'Результат',
	show: function() {
        this.callParent(arguments);
        
        var me = this;
        
        if ( !arguments[0] || !arguments[0].Evn_id || !arguments[0].object || !arguments[0].object_id )
		{
			Ext6.Msg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

        this.callback = Ext6.emptyFn;
        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }
        		
        if (arguments[0].Evn_id) {
			this.Evn_id = arguments[0].Evn_id;
		}
		
		if (arguments[0].object) {
			this.object = arguments[0].object;
		}
		
		if (arguments[0].object_id) {
			this.object_id = arguments[0].object_id;
		}
		
		if(arguments[0].userMedStaffFact) {
			this.userMedStaffFact_id = arguments[0].userMedStaffFact.MedStaffFact_id;
		} else this.userMedStaffFact_id = null;
		
		var params = {
			user_MedStaffFact_id: this.userMedStaffFact_id,
			object: this.object,
			object_id: this.object_id,
			object_value: this.Evn_id,
			archiveRecord: 0,
			from_MZ: 1,
			from_MSE: 1,
			view_section: 'main'
		};
		me.body.mask(LOAD_WAIT);

        Ext6.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			params: params,
			callback: function(options, success, response)
			{
				
				if ( success )
				{
					var response_obj = Ext6.JSON.decode(response.responseText);
					if ( response_obj.success )
						if (response_obj['html'] && response_obj['map'])
						{
							me.MainPanel.setHtml( response_obj['html'] );
							me.hidePrintOnly();
						}
				}
				me.body.unmask();
			}
		});

        return true;
	},
	printHtml: function() {
		var s = this.MainPanel.body;
		var id_salt = Math.random();
		var win_id = 'printEvent' + Math.floor(id_salt * 10000);
		var win = window.open('', win_id);
		win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?' + id_salt + '" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">' + s.dom.innerHTML + '</body></html>');
		var i, el;
		// нужно показать скрытые области для печати
		var printonly_list = Ext6.query("div[class=printonly]", win.document);
		for (i = 0; i < printonly_list.length; i++) {
			el = Ext6.get(printonly_list[i]);
			el.setStyle({display: 'block'});
		}
		// нужно скрыть элементы управления
		var tb_list = Ext6.query("*[class*=section-toolbar]", win.document);
		tb_list = tb_list.concat(Ext6.query("*[class*=sectionlist-toolbar]", win.document));
		tb_list = tb_list.concat(Ext6.query("*[class*=item-toolbar]", win.document));
		for (i = 0; i < tb_list.length; i++) {
			el = Ext6.get(tb_list[i]);
			el.setStyle({display: 'none'});
		}
		win.document.close();
	},
	hidePrintOnly: function ()
	{
		var me = this;
		var rootnode = me.MainPanel.getEl();
		var node_list = rootnode.query('div[class*=printonly]');
		var i, el;
		for(i=0; i < node_list.length; i++)
		{
			el = Ext6.get(node_list[i]);
			el.setStyle({display: 'none'});
		}
	},
	initComponent: function() {
        var thas = this;
			
        this.MainPanel = new Ext6.create('swPanel', {
			region: 'center',
			bodyPadding: '20 20 20 37',
			border: false,
			scrollable: true,
			html: ''
        });

    	Ext6.apply(this, {
			buttons: [{
				xtype: 'SimpleButton',
				text: 'Печать',
				handler: function() {
					thas.printHtml();
				}
			}, {
				xtype: 'SimpleButton',
				text: 'Закрыть'
			}],
            border: false,
			layout: 'border',
			items: [
                this.MainPanel
            ]
		});
		this.callParent(arguments);
	}
});
