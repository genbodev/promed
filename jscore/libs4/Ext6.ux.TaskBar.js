/*
 * Ext JS Library 2.3.0
 * Copyright(c) 2006-2009, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext6.ux.TaskBar
 * @extends Ext6.util.Observable
 */
Ext6.ux.TaskBar = function(){
    this.init();
}

Ext6.extend(Ext6.ux.TaskBar, Ext6.util.Observable, {
    init : function(){
		this.tbPanel = new Ext6.ux.TaskButtonsPanel({
			el: 'ux-taskbuttons-panel',
			bodyStyle: 'padding: 10px 0px;',
			id: 'TaskBarButtons'
		});
		
		return this;
    },
    
    addTaskButton : function(win){
		return this.tbPanel.addButton(win, 'ux-taskbuttons-panel');
	},
	
	buttonsCount: function() {
		return this.tbPanel.buttonsCount();
	},
	
	removeTaskButton : function(btn){
        if (btn) {
            this.tbPanel.removeButton(btn);
        }
	},
	
	setActiveButton : function(btn){
        if (btn) {
            this.tbPanel.setActiveButton(btn);
        }
	}
});



/**
 * @class Ext6.ux.TaskButtonsPanel
 * @extends Ext6.Component
 */
Ext6.ux.TaskButtonsPanel = Ext6.extend(Ext6.Component, {
	activeButton: null,
	enableScroll: true,
	scrollIncrement: 0,
    scrollRepeatInterval: 400,
    scrollDuration: .35,
    animScroll: true,
    resizeButtons: false,
    buttonWidth: 168,
    minButtonWidth: 118,
    buttonMargin: 2,
    buttonWidthSet: false,
	
	initComponent : function() {
        Ext6.ux.TaskButtonsPanel.superclass.initComponent.call(this);
        this.on('resize', this.delegateUpdates);
        this.items = [];
        
        this.stripWrap = Ext6.get(this.el).createChild({
        	cls: 'ux-taskbuttons-strip-wrap',
        	cn: {
            	tag:'ul', cls:'ux-taskbuttons-strip'
            }
		});
        this.stripSpacer = Ext6.get(this.el).createChild({
        	cls:'ux-taskbuttons-strip-spacer'
        });
        this.strip = new Ext6.Element(this.stripWrap.dom.firstChild);
        
        this.edge = this.strip.createChild({
        	tag:'li',
        	cls:'ux-taskbuttons-edge'
        });
        this.strip.createChild({
        	cls:'x-clear'
        });
	},
	
	addButton : function(win){
		var li = this.strip.createChild({tag:'li'}, this.edge); // insert before the edge
        var btn = new Ext6.ux.TaskBar.TaskButton(win, li);
		
		this.items.push(btn);
		
		if(!this.buttonWidthSet){
			this.lastButtonWidth = btn.container.getWidth();
		}
		
		this.setActiveButton(btn);
		return btn;
	},
	
	setTitle : function (win) {
		
	},
	
	removeButton : function(btn){
		var li = document.getElementById(btn.container.id);
		btn.destroy();
		li.parentNode.removeChild(li);
		
		var s = [];
		for(var i = 0, len = this.items.length; i < len; i++) {
			if(this.items[i] != btn){
				s.push(this.items[i]);
			}
		}
		this.items = s;
		
		this.delegateUpdates();
	},
	
	setActiveButton : function(btn){
		this.activeButton = btn;
		this.delegateUpdates();
	},
	
	delegateUpdates : function(){
		/*if(this.suspendUpdates){
            return;
        }*/
        if(this.resizeButtons && this.rendered){
            this.autoSize();
        }
        if(this.enableScroll && this.rendered){
            this.autoScroll();
        }
    },
    
	buttonsCount: function() {
		return this.items.length;
	},
	
    autoSize : function(){
        var count = this.items.length;
        var ow = this.el.dom.offsetWidth;
        var aw = this.el.dom.clientWidth;

        if(!this.resizeButtons || count < 1 || !aw){ // !aw for display:none
            return;
        }
        
        var each = Math.max(Math.min(Math.floor((aw-4) / count) - this.buttonMargin, this.buttonWidth), this.minButtonWidth); // -4 for float errors in IE
        var btns = this.stripWrap.dom.getElementsByTagName('button');
        
        this.lastButtonWidth = Ext6.get(btns[0].id).findParent('li').offsetWidth;
        
        for(var i = 0, len = btns.length; i < len; i++) {            
            var btn = btns[i];
            
            var tw = Ext6.get(btns[i].id).findParent('li').offsetWidth;
            var iw = btn.offsetWidth;
            
            //btn.style.width = (each - (tw-iw)) + 'px';
			btn.style.width = '210px';
        }
    },
    
    autoScroll : function(){
    	var count = this.items.length;
        var ow = this.el.dom.offsetWidth;
        var tw = this.el.dom.clientWidth;
        
        var wrap = this.stripWrap;
        var cw = wrap.dom.offsetWidth;
        var pos = this.getScrollPos();
        var l = this.edge.getOffsetsTo(this.stripWrap)[0] + pos;
        
        if(!this.enableScroll || count < 1 || cw < 20){ // 20 to prevent display:none issues
            return;
        }
        
        wrap.setWidth(tw); // moved to here because of problem in Safari
        
        if(l <= tw){
            wrap.dom.scrollLeft = 0;
            //wrap.setWidth(tw); moved from here because of problem in Safari
            if(this.scrolling){
                this.scrolling = false;
                this.el.removeClass('x-taskbuttons-scrolling');
                this.scrollLeft.hide();
                this.scrollRight.hide();
            }
        }else{
            if(!this.scrolling){
                this.el.addClass('x-taskbuttons-scrolling');
            }
            tw -= wrap.getMargins('lr');
            wrap.setWidth(tw > 20 ? tw : 20);
            if(!this.scrolling){
                if(!this.scrollLeft){
                    this.createScrollers();
                }else{
                    this.scrollLeft.show();
                    this.scrollRight.show();
                }
            }
            this.scrolling = true;
            if(pos > (l-tw)){ // ensure it stays within bounds
                wrap.dom.scrollLeft = l-tw;
            }else{ // otherwise, make sure the active button is still visible
				this.scrollToButton(this.activeButton, true); // true to animate
            }
            this.updateScrollButtons();
        }
    },

    createScrollers : function(){
        var h = this.el.dom.offsetHeight; //var h = this.stripWrap.dom.offsetHeight;
		
        // left
        var sl = this.el.insertFirst({
            cls:'ux-taskbuttons-scroller-left'
        });
        sl.setHeight(h);
        sl.addClassOnOver('ux-taskbuttons-scroller-left-over');
        this.leftRepeater = new Ext6.util.ClickRepeater(sl, {
            interval : this.scrollRepeatInterval,
            handler: this.onScrollLeft,
            scope: this
        });
        this.scrollLeft = sl;

        // right
        var sr = this.el.insertFirst({
            cls:'ux-taskbuttons-scroller-right'
        });
        sr.setHeight(h);
        sr.addClassOnOver('ux-taskbuttons-scroller-right-over');
        this.rightRepeater = new Ext6.util.ClickRepeater(sr, {
            interval : this.scrollRepeatInterval,
            handler: this.onScrollRight,
            scope: this
        });
        this.scrollRight = sr;
    },
    
    getScrollWidth : function(){
        return this.edge.getOffsetsTo(this.stripWrap)[0] + this.getScrollPos();
    },

    getScrollPos : function(){
        return parseInt(this.stripWrap.dom.scrollLeft, 10) || 0;
    },

    getScrollArea : function(){
        return parseInt(this.stripWrap.dom.clientWidth, 10) || 0;
    },

    getScrollAnim : function(){
        return {
        	duration: this.scrollDuration,
        	callback: this.updateScrollButtons,
        	scope: this
        };
    },

    getScrollIncrement : function(){
    	return (this.scrollIncrement || this.lastButtonWidth+2);
    },
    
    /* getBtnEl : function(item){
        return document.getElementById(item.id);
    }, */
    
    scrollToButton : function(item, animate){
    	item = item.el.dom.parentNode; // li
        if(!item){ return; }
        var el = item; //this.getBtnEl(item);
        var pos = this.getScrollPos(), area = this.getScrollArea();
        var left = Ext6.fly(el).getOffsetsTo(this.stripWrap)[0] + pos;
        var right = left + el.offsetWidth;
        if(left < pos){
            this.scrollTo(left, animate);
        }else if(right > (pos + area)){
            this.scrollTo(right - area, animate);
        }
    },
    
    scrollTo : function(pos, animate){
        this.stripWrap.scrollTo('left', pos, animate ? this.getScrollAnim() : false);
        if(!animate){
            this.updateScrollButtons();
        }
    },
    
    onScrollRight : function(){
        var sw = this.getScrollWidth()-this.getScrollArea();
        var pos = this.getScrollPos();
        var s = Math.min(sw, pos + this.getScrollIncrement());
        if(s != pos){
        	this.scrollTo(s, this.animScroll);
        }        
    },

    onScrollLeft : function(){
        var pos = this.getScrollPos();
        var s = Math.max(0, pos - this.getScrollIncrement());
        if(s != pos){
            this.scrollTo(s, this.animScroll);
        }
    },
    
    updateScrollButtons : function(){
        var pos = this.getScrollPos();
        this.scrollLeft[pos == 0 ? 'addClass' : 'removeClass']('ux-taskbuttons-scroller-left-disabled');
        this.scrollRight[pos >= (this.getScrollWidth()-this.getScrollArea()) ? 'addClass' : 'removeClass']('ux-taskbuttons-scroller-right-disabled');
    }
});



/**
 * @class Ext6.ux.TaskBar.TaskButton
 * @extends Ext6.Button
 */
Ext6.ux.TaskBar.TaskButton = function(win, el){
	this.win = win;
	var textLength = 33;

	var closeBtn = '<span class="taskbar-close-btn"></span>';
	if (win.noCloseOnTaskBar) {
		closeBtn = '';
	}

    var refreshCodeBtn = '';
    if (IS_DEBUG) {
        refreshCodeBtn = '<span class="taskbar-refreshcode-btn"></span>';
    }

	var title = win.title.replace(/<\/?[^>]+>/gi, '');
    Ext6.ux.TaskBar.TaskButton.superclass.constructor.call(this, {
        iconCls: win.iconCls,
        text: Ext6.util.Format.ellipsis(title, textLength),
        tooltip: title.length>textLength ? title : null,
		textLength: textLength,
        renderTo: el,
		handler: function (button, e) {
			if (e.getTarget('.taskbar-close-btn', 3) !== null) {
                win.hide();
            } else if (e.getTarget('.taskbar-refreshcode-btn', 3) !== null) {
                win.refreshCode();
            } else if (win.minimized || win.hidden) {
				markActive(win);
				win.toFront();
			} else if (button.hasCls('active-win')) {
				// win.minimize();
			} else {
				win.toFront();
			}
        },
        clickEvent:'mousedown',
		renderTpl: '<span id="{id}-btnWrap" data-ref="btnWrap" role="presentation" unselectable="on" style="{btnWrapStyle}" ' + 'class="{btnWrapCls} {btnWrapCls}-{ui} {splitCls}{childElCls}">' + '<span id="{id}-btnEl" data-ref="btnEl" role="presentation" unselectable="on" style="{btnElStyle}" ' + 'class="{btnCls} {btnCls}-{ui} {textCls} {noTextCls} {hasIconCls} ' + '{iconAlignCls} {textAlignCls} {btnElAutoHeightCls}{childElCls}">' + '<tpl if="iconBeforeText">{[values.$comp.renderIcon(values)]}</tpl>' + '<span id="{id}-btnInnerEl" data-ref="btnInnerEl" unselectable="on" ' + 'class="{innerCls} {innerCls}-{ui}{childElCls}">{text}</span>' + '<tpl if="!iconBeforeText">{[values.$comp.renderIcon(values)]}</tpl>' + '</span>' + '</span>' + '{[values.$comp.getAfterMarkup ? values.$comp.getAfterMarkup(values) : ""]}' + // if "closable" (tab) add a close element icon
			closeBtn + refreshCodeBtn +
			'<tpl if="split">' + '<span id="{id}-arrowEl" class="{arrowElCls}" data-ref="arrowEl" ' + 'role="button" hidefocus="on" unselectable="on"' + '<tpl if="tabIndex != null"> tabindex="{tabIndex}"</tpl>' + '<tpl foreach="arrowElAttributes"> {$}="{.}"</tpl>' + ' style="{arrowElStyle}"' + '>{arrowElText}</span>' + '</tpl>'
    });
};

Ext6.extend(Ext6.ux.TaskBar.TaskButton, Ext6.Button, {
    onRender: function(){
        Ext6.ux.TaskBar.TaskButton.superclass.onRender.apply(this, arguments);

        this.cmenu = new Ext6.menu.Menu({
            items: [{
                text: langs('Свернуть'),
                handler: this.minimizeWin.createDelegate(this, this.win, true),
                scope: this.win
            },'-', {
                text: langs('Закрыть'),
                handler: this.closeWin.createDelegate(this, this.win, true),
                scope: this.win
            }]
        });

        this.cmenu.on('beforeshow', function(){
            var items = this.cmenu.items.items;
            var w = this.win;
            items[0].setDisabled(w.minimized == true);
        }, this);

        /*this.el.on('contextmenu', function(e){
            e.stopEvent();
            if(!this.cmenu.el){
                this.cmenu.render();
            }
            var xy = e.getXY();
            xy[1] -= this.cmenu.el.getHeight();
            this.cmenu.showAt(xy);
        }, this);*/
    },
	setButtonText: function(text) {
		var title = text.replace(/<\/?[^>]+>/gi, '');
        this.setTooltip(title.length>this.textLength ? title : null);
		this.setText(Ext6.util.Format.ellipsis(title, this.textLength));
	},
    minimizeWin: function(cMenu, e, win){
		win.minimize();
	},    
    closeWin: function(cMenu, e, win){
		// win.close();
        win.hide(); // для промеда хайд.
	}
});