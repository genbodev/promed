/**
* swMessageBox - компонент заменяющий стандартный Ext.Msg
* отличие в том, что работает клавиатура для перехода по кнопкам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      29.05.2009
*/

sw.swMessageBox = function(){
	var dlg, opt, mask, waitTimer;
	var bodyEl, msgEl, textboxEl, textareaEl, progressBar, pp, iconEl, spacerEl;
	var buttons, activeTextEl, bwidth, iconCls = '';

	// private
	var handleButton = function(button){
		if(dlg.isVisible()){
			dlg.hide();
			Ext.callback(opt.fn, opt.scope||window, [button, activeTextEl.dom.value, opt], 1);
		}
	};

	// private
	var handleHide = function(){
		if(opt && opt.cls){
			dlg.el.removeCls(opt.cls);
		}
		progressBar.reset();
	};

	// private
	var handleEsc = function(d, k, e){
		if(opt && opt.closable !== false){
			dlg.hide();
		}
		if(e){
			e.stopEvent();
		}
	};

	// private
	var updateButtons = function(b){
		var width = 0;
		if(!b){
			buttons["ok"].hide();
			buttons["cancel"].hide();
			buttons["yes"].hide();
			buttons["no"].hide();
			buttons["refresh"].hide();
			buttons["close"].hide();
			return width;
		}
//		console.log({'dlg.footer':dlg.footer});
//		dlg.footer.dom.style.display = '';
		for(var k in buttons){
			if(typeof buttons[k] != "function"){
				if(b[k]){
					buttons[k].show();
					buttons[k].setText(typeof b[k] == "string" ? b[k] : sw.swMessageBox.buttonText[k]);
					width += buttons[k].el.getWidth()+15;
				}else{
					buttons[k].hide();
				}
			}
		}
		return width;
	};

	return {

		getDialog : function(titleText){
			if(!dlg){
				dlg = Ext.create('Ext.Window',{
					autoCreate : true,
					title:titleText,
					resizable:false,
					constrain:true,
					constrainHeader:true,
					minimizable : false,
					maximizable : false,
					stateful: false,
					modal: true,
					shim:true,
					buttonAlign:"center",
					width:400,
					height:100,
					minHeight: 80,
					plain:true,
					footer:true,
					closable:true,
					close : function(){
						if(opt && opt.buttons && opt.buttons.no && !opt.buttons.cancel){
							handleButton("no");
						}else{
							handleButton("cancel");
						}
					},
					keys: [{
						key:[
								Ext.EventObject.TAB,
								Ext.EventObject.LEFT,
								Ext.EventObject.RIGHT
							],
						fn: function() {
							if (dlg.focusEl==dlg.buttons[2]) {
								dlg.buttons[1].focus();
								dlg.focusEl=dlg.buttons[1];
							}
							else
								if (dlg.focusEl==dlg.buttons[1]) {
									dlg.buttons[2].focus();
									dlg.focusEl=dlg.buttons[2];
								}
						},
						stopEvent: true
					}]
				});
				buttons = {};
				var bt = this.buttonText;
				//TODO: refactor this block into a buttons config to pass into the Window constructor
				
				buttons["ok"] = dlg.add(Ext.create('Ext.Button', {text: bt["ok"], handler: Ext.Function.pass(handleButton, ["ok"], this)}));
				buttons["yes"] = dlg.add(Ext.create('Ext.Button', {text: bt["yes"], handler: Ext.Function.pass(handleButton, ["yes"], this)}));
				buttons["no"] = dlg.add(Ext.create('Ext.Button', {text: bt["no"], handler: Ext.Function.pass(handleButton, ["no"], this)}));
				buttons["cancel"] = dlg.add(Ext.create('Ext.Button', {text: bt["cancel"], handler: Ext.Function.pass(handleButton, ["cancel"], this)}));
				buttons["refresh"] = dlg.add(Ext.create('Ext.Button', {text: bt["refresh"], handler: Ext.Function.pass(handleButton, ["refresh"], this)}));
				buttons["close"] = dlg.add(Ext.create('Ext.Button', {text: bt["close"], handler: Ext.Function.pass(handleButton, ["close"], this)}));
				
//				buttons["ok"] = dlg.addButton(bt["ok"], Ext.Function.pass(handleButton, ["ok"], this));
//				buttons["yes"] = dlg.addButton(bt["yes"], Ext.Function.pass(handleButton, ["yes"], this));
//				buttons["no"] = dlg.addButton(bt["no"], Ext.Function.pass(handleButton, ["no"], this));
//				buttons["cancel"] = dlg.addButton(bt["cancel"], Ext.Function.pass(handleButton, ["cancel"], this));
//				buttons["refresh"] = dlg.addButton(bt["refresh"], Ext.Function.pass(handleButton, ["refresh"], this));
//				buttons["close"] = dlg.addButton(bt["close"], Ext.Function.pass(handleButton, ["close"], this))  ;
				buttons["ok"].hideMode = buttons["yes"].hideMode = buttons["no"].hideMode = buttons["cancel"].hideMode = 'offsets';
				dlg.render(document.body);
				dlg.getEl().addCls('x-window-dlg');
				mask = dlg.mask;
				bodyEl = dlg.body.createChild({
					html:'<div class="ext-mb-icon"></div><div class="ext-mb-content"><span class="ext-mb-text"></span><br /><div class="ext-mb-fix-cursor"><input type="text" class="ext-mb-input" /><textarea class="ext-mb-textarea"></textarea></div></div>'
				});
				iconEl = Ext.get(bodyEl.dom.firstChild);
				var contentEl = bodyEl.dom.childNodes[1];
				msgEl = Ext.get(contentEl.firstChild);
				textboxEl = Ext.get(contentEl.childNodes[2].firstChild);
				textboxEl.enableDisplayMode();
				textboxEl.addKeyListener([10,13], function(){
					if(dlg.isVisible() && opt && opt.buttons){
						if(opt.buttons.ok){
							handleButton("ok");
						}else if(opt.buttons.yes){
							handleButton("yes");
						}
					}
				});
				textareaEl = Ext.get(contentEl.childNodes[2].childNodes[1]);
				textareaEl.enableDisplayMode();
				progressBar = Ext.create('Ext.ProgressBar',{
					renderTo:bodyEl
				});
				bodyEl.createChild({cls:'x-clear'});
			}
			return dlg;
		},


		updateText : function(text){
			if(!dlg.isVisible() && !opt.width){
				dlg.setSize(this.maxWidth, 100); // resize first so content is never clipped from previous shows
			}
			msgEl.update(text || '&#160;');
			var iw = iconCls != '' ? (iconEl.getWidth() + iconEl.getMargin('lr')) : 0;
			var mw = msgEl.getWidth() + msgEl.getMargin('lr');
			console.log({dlg:dlg});
			var fw = dlg.getWidth();
			var bw = dlg.body.getFrameWidth('lr');
			if (Ext.isIE && iw > 0){
				//3 pixels get subtracted in the icon CSS for an IE margin issue,
				//so we have to add it back here for the overall width to be consistent
				iw += 3;
			}
			var w = Math.max(Math.min(opt.width || iw+mw+fw+bw, this.maxWidth),
						Math.max(opt.minWidth || this.minWidth, bwidth || 0));

			if(opt.prompt === true){
				activeTextEl.setWidth(w-iw-fw-bw);
			}
			if(opt.progress === true || opt.wait === true){
				progressBar.setSize(w-iw-fw-bw);
			}
			if(Ext.isIE && w == bwidth){
				w += 4; //Add offset when the content width is smaller than the buttons.
			}
			dlg.setSize(w, 'auto').center();
			return this;
		},


		updateProgress : function(value, progressText, msg){
			progressBar.updateProgress(value, progressText);
			if(msg){
				this.updateText(msg);
			}
			return this;
		},


		isVisible : function(){
			return dlg && dlg.isVisible();
		},


		hide : function(){
			var proxy = dlg.activeGhost;
			if(this.isVisible() || proxy) {
				dlg.hide();
				handleHide();
				if (proxy) {
					proxy.hide();
				}
			}
			return this;
		},


		show : function(options){
			if(this.isVisible()){
				this.hide();
			}
			opt = options;
			var d = this.getDialog(opt.title || "&#160;");

			d.setTitle(opt.title || "&#160;");
			var allowClose = (opt.closable !== false && opt.progress !== true && opt.wait !== true);
			d.tools.close.setVisible(allowClose);
			activeTextEl = textboxEl;
			opt.prompt = opt.prompt || (opt.multiline ? true : false);
			if(opt.prompt){
				if(opt.multiline){
					textboxEl.hide();
					textareaEl.show();
					textareaEl.setHeight(typeof opt.multiline == "number" ?
						opt.multiline : this.defaultTextHeight);
					activeTextEl = textareaEl;
				}else{
					textboxEl.show();
					textareaEl.hide();
				}
			}else{
				textboxEl.hide();
				textareaEl.hide();
			}
			activeTextEl.dom.value = opt.value || "";
			if(opt.prompt){
				d.focusEl = activeTextEl;
			}else{
				var bs = opt.buttons;
				var db = null;
				if(bs && bs.ok){
					db = buttons["ok"];
				}else if(bs && bs.yes){
					db = buttons["yes"];
				}
				if (db){
					d.focusEl = db;
				}
			}
			if(opt.iconCls){
				d.setIconClass(opt.iconCls);
			}
			this.setIcon(opt.icon);
			bwidth = updateButtons(opt.buttons);
			progressBar.setVisible(opt.progress === true || opt.wait === true);
			this.updateProgress(0, opt.progressText);
			this.updateText(opt.msg);
			if(opt.cls){
				d.el.addCls(opt.cls);
			}
			d.proxyDrag = opt.proxyDrag === true;
			d.modal = opt.modal !== false;
			d.mask = opt.modal !== false ? mask : false;
			if(!d.isVisible()){
				// force it to the end of the z-index stack so it gets a cursor in FF
				document.body.appendChild(dlg.el.dom);
//				d.setAnimateTarget(opt.animEl);
				d.show(opt.animEl);
			}

			//workaround for window internally enabling keymap in afterShow
			d.on('show', function(){
				if(allowClose === true){
					d.keyMap.enable();
				}else{
					d.keyMap.disable();
				}
			}, this, {single:true});

			//workaround for window internally enabling keymap in afterShow
			if(opt.wait === true){
				progressBar.wait(opt.waitConfig);
			}
			return this;
		},


		setIcon : function(icon){
			if(icon && icon != ''){
				iconEl.removeCls('x-hidden');
				iconEl.replaceCls(iconCls, icon);
				iconCls = icon;
			}else{
				iconEl.replaceCls(iconCls, 'x-hidden');
				iconCls = '';
			}
			return this;
		},


		progress : function(title, msg, progressText){
			this.show({
				title : title,
				msg : msg,
				buttons: false,
				progress:true,
				closable:false,
				minWidth: this.minProgressWidth,
				progressText: progressText
			});
			return this;
		},


		wait : function(msg, title, config){
			this.show({
				title : title,
				msg : msg,
				buttons: false,
				closable:false,
				wait:true,
				modal:true,
				minWidth: this.minProgressWidth,
				waitConfig: config
			});
			return this;
		},


		alert : function(title, msg, fn, scope){
			this.show({
				title : title,
				msg : msg,
				buttons: this.OK,
				fn: fn,
				scope : scope
			});
			return this;
		},


		confirm : function(title, msg, fn, scope){
			this.show({
				title : title,
				msg : msg,
				buttons: this.YESNO,
				fn: fn,
				scope : scope,
				icon: this.QUESTION
			});
			return this;
		},


		prompt : function(title, msg, fn, scope, multiline, value){
			this.show({
				title : title,
				msg : msg,
				buttons: this.OKCANCEL,
				fn: fn,
				minWidth:250,
				scope : scope,
				prompt:true,
				multiline: multiline,
				value: value
			});
			return this;
		},


		OK : {ok:true},

		CANCEL : {cancel:true},

		OKCANCEL : {ok:true, cancel:true},

		YESNO : {yes:true, no:true},

		YESNOCANCEL : {yes:true, no:true, cancel:true},
		
		REFRESHCLOSE : {refresh:true, close:true},

		INFO : 'ext-mb-info',

		WARNING : 'ext-mb-warning',

		QUESTION : 'ext-mb-question',

		ERROR : 'ext-mb-error',


		defaultTextHeight : 75,

		maxWidth : 600,

		minWidth : 100,

		minProgressWidth : 250,

		buttonText : {
			ok : "OK",
			cancel : "Отмена",
			yes : "Да",
			no : "Нет",
			refresh: "Обновить",
			close: "Закрыть"
		}
	};
}();


sw.swMsg = sw.swMessageBox