//mainController
Ext.define('globalApp.controller.MainController', {
    extend: 'Ext.app.Controller', 
//	views: [
//		'swSMPHeadDutyView'
//	],
	
    init: function() {
		
		globalCtrl = this;
		countOfLoad = 0;
//временно удалено
//		switch (Ext.globalOptions.defaultARM.ARMType)
//		{
//			case 'smpdispatchdirect' : {getWnd('swWorkPlaceSMPDispatcherDWE4').show({}); break;}
//			case 'smpdispatchcall' : {getWnd('swWorkPlaceSMPDispatcherCallE4').show({}); break;}
//			case 'smpheadduty' : {getWnd('swWorkPlaceSMPHeadDutyWindowE4').show({}); break;}
//		}
//до сюда
		
//		window.onerror = function(message, url, linenumber) {
//			Ext.defer(function() {
//			Ext.Msg.show({
//				title:'Ошибочка вышла...',
//				msg: 'Ошибка следующего характера<br><b>'+message+'</b><br>Url'+url+'<br>Line:'+linenumber+ '.<br> Надо исправить. Обновить?',
//				buttons: Ext.Msg.YESNO,
//				icon: Ext.Msg.QUESTION,
//				fn: function(btn){
//					if (btn == 'yes'){
//						Ext.Loader.loadScript({
//						url: url,                    // URL of script
//						scope: this})
//					}
//				}
//			})
//			}, 1000)
//		}

		if(getRegionNick().inlist(['ufa', 'krym', 'kz']))	this.checkConnect();
		
        this.control({
			'window': {
				show: function(self, eOpts) {
					//забираем данные из локального стора
					var winOptsCacheStore = Ext.getStore('winOptsCacheStore'),
						res = winOptsCacheStore.findRecord('winClass', Ext.getClassName(self)),
						params_init = self.initialConfig ? self.initialConfig.params_init : null,
						windowsListToolbar = Ext.getCmp('windowsListToolbar'),
						form = self.down('form');


					if(form) form.fireEvent('show', form);

					if (!self.hideInToolbar) {
						var winBtn = Ext.create('Ext.button.Split', {
							xtype: 'splitbutton',
							text: self.title,
							refId: self.id,
							winRefId: self.refId ? self.refId : null,
							armId: params_init ? params_init.id : null,
							arrowCls: 'cross',
							enableToggle: true,
							toggleGroup: 'windowsListToolbarTG',
							handler: function (btn) {
								Ext.getCmp(btn.refId).show();

								if(btn.winRefId){

									var menuRec = null;

									if(btn.armId){
										menuRec = sw.Promed.MedStaffFactByUser.store.findRecord('id', btn.armId);
									}
									else{
										menuRec = sw.Promed.MedStaffFactByUser.store.findRecord('ARMType', btn.winRefId)
									}

									if(menuRec){
										sw.Promed.MedStaffFactByUser.setARM(menuRec.getData());
									}
								}


							},
							arrowHandler: function (btn) {
								Ext.getCmp(btn.refId).close();
								windowsListToolbar.remove(btn);
							}
						});
						windowsListToolbar.add(winBtn);

						winBtn.toggle(true);
					}

					if (res && self.refId)
					{
//						if (self.maximizable && res.get('winMaximum'))
//						{
//							self.maximize()
//						}
//						else{
//							self.setX(res.get('winX'))
//							self.setY(res.get('winY'))
//							Ext.apply(self, 
//								{
//									x: res.get('winX'),
//									y: res.get('winY'),
//									width: res.get('winWidth'),
//									height: res.get('winHeight')
//								}
//							)
//						}
					}
					
					//выравнивание окна
					if(self.refId)
					{
//						self.getEl().setTop(0);
//						var armbutt = (Ext.ComponentQuery.query('armButton[refId=buttonChooseArm]'));
//						armbutt[0].menu.items.each(function(item){
//						if(item.disabled)
//						{
//							self.setTitle('<span style="font-size: 12px">'+item.wtitle+'</span>');
//
//						}
//						}.bind(this))
					}
					
					//кнопка обновить
					if (self.tools){
						if (IS_DEBUG && (self.tools.length != 0))
						{
							this.loadScriptFile(self)

						}
					}

				},
				activate: function(wind, eOpts){
					var windowsListToolbar = Ext.getCmp('windowsListToolbar'),
						windowsListToolbarButton = windowsListToolbar.down('button[refId="'+wind.id+'"]');

					if(windowsListToolbarButton) windowsListToolbarButton.toggle(true);
				},
				hide: function(wind){
					var windowsListToolbar = Ext.getCmp('windowsListToolbar'),
						windowsListToolbarButton = windowsListToolbar.down('button[refId="'+wind.id+'"]');

					if(windowsListToolbarButton) windowsListToolbar.remove(windowsListToolbarButton);
				},
				close: function(wind){

					var winOptsCacheStore = Ext.getStore('winOptsCacheStore'),					
						res = winOptsCacheStore.findRecord('winClass', Ext.getClassName(wind)),
						windowsListToolbar = Ext.getCmp('windowsListToolbar'),
						windowsListToolbarButton = windowsListToolbar.down('button[refId="'+wind.id+'"]');

					if(windowsListToolbarButton) windowsListToolbar.remove(windowsListToolbarButton);
					
					if (res){
						res.set('winWidth', wind.width);
						res.set('winHeight', wind.height);
						res.set('winX', wind.x);
						res.set('winY', wind.y);
						res.set('winMaximum', wind.maximized);
					}
					else
					{
						winOptsCacheStore.add({
							winClass : Ext.getClassName(wind),
							winWidth : wind.width,
							winHeight : wind.height,
							winX : wind.x,
							winY : wind.y,
							winMaximum : wind.maximized
						})
					}
					winOptsCacheStore.sync();
				}
			},
			'button[refId=buttonChooseArm]':{
				afterSelectArm: function(){
					var smpController = globalApp.app.getController('SMP.swSMPDefaultController_controller');
					smpController.checkHavingLpuBuilding();
				}
			}
		})
    },
	loadScriptFile: function(win){
		
		var winOptsCacheStore = Ext.getStore('winOptsCacheStore');
		
		if (Ext.Array.findBy(win.tools, function(item, index){if(item.type=='refresh') return true;}))return false;
		
		win.addTool({
		type:'refresh',
		tooltip: 'Обновить функционал',
		handler: function(event, toolEl, panelHeader) {

			Ext.Loader.loadScript({
				url: 'jscore4/lib/swComponentLibPanels.js',
				scope: this
			});

			//preload components
			Ext.Loader.loadScript({
				url: 'jscore4/lib/swComponentLibComboboxes.js',
				scope: this,
				onLoad: function(o) {

				},
				onError: function(o) {

				}
			});
			Ext.Loader.loadScript({
				url: 'jscore4/lib/swComponentLibTextfields.js',
				scope: this
			});
			Ext.Loader.loadScript({
				url: 'jscore4/lib/swComponentLibGridPanelTwoStore.js',
				scope: this
			});
			Ext.Loader.loadScript({
				url: 'jscore4/lib/swComponentLibDateField.js',
				scope: this
			});
			
			var conf = win.initialConfig,
				classname = Ext.getClassName(win),
				pathWindow = Ext.Loader.getPath(Ext.getClassName(win));

			Ext.Loader.loadScript({
				url: pathWindow,
				scope: this,
				onLoad: function(o) {
					win.close()
					var currentOptsWindow = winOptsCacheStore.findRecord('winClass', Ext.getClassName(win))
					if (currentOptsWindow)
					{
						winOptsCacheStore.remove(currentOptsWindow);
						winOptsCacheStore.sync();
					}
					var reloadedWin = {}
					try {
						Ext.create(classname, conf).show();
					}
					catch(error){
						Ext.defer(function() {
						Ext.Msg.show({
							title:'Ошибочка вышла...',
							msg: 'Ошибка следующего характера <br><b>'+error+ '<b><br> Надо исправить. Обновить?',
							buttons: Ext.Msg.YESNO,
							icon: Ext.Msg.QUESTION,
							fn: function(btn){
								if (btn == 'yes'){
									globalCtrl.reloadScriptFile(win)
								}
							}
						})
						}, 1000)
					}
					finally{}
				},
				onError: function(o) {        // callback fn if load fails
					alert('Форма не может быть загружена');
				}
			})
		}						
	})
	},
	reloadScriptFile: function(win){
		var	conf = win.initialConfig,
			classname = Ext.getClassName(win),
			pathWindow = Ext.Loader.getPath(Ext.getClassName(win));
		
		countOfLoad++;
		
		Ext.Loader.loadScript({
			url: pathWindow,                    // URL of script
			scope: this,                   // scope of callbacks
			onLoad: function(o) {
				try {
					Ext.create(classname, conf).show()
				}
				catch(error){
					Ext.defer(function() {
					Ext.Msg.show({
						title:'Ошибка...',
						msg: 'Ошибка следующего характера:<br><b>'+error+ '</b><br> Надо исправить. Обновить?',
						buttons: Ext.Msg.YESNO,
						icon: Ext.Msg.WARNING,
						fn: function(btn){
							if (btn == 'yes'){
								globalCtrl.reloadScriptFile(win);
							}
						}
					})
					}, 1000)
				}
				finally{}
			}
		})
	},
	checkConnect: function(){
		
		Ext.define('App.override.data.Store', {
			override: 'Ext.data.Store',
			
			onProxyLoad: function(operation) {
				
				if(operation.response && operation.response.status!=200)
					{me.onErrorLoading(operation.response.status);}
				
				var me = this,
					resultSet = operation.getResultSet(),
					records = operation.getRecords(),
					successful = operation.wasSuccessful();

				if (me.isDestroyed) {
					return;
				}
				
				if (resultSet) {
					me.totalCount = resultSet.total;
				}

				// Loading should be set to false before loading the records.
				// loadRecords doesn't expose any hooks or events until refresh
				// and datachanged, so by that time loading should be false
				me.loading = false;
				if (successful) {
					me.loadRecords(records, operation);
				} else {
					if (operation.request.url.indexOf("getPersonByAddress") == 0) me.onErrorLoading();
				}

				if (me.hasListeners.load) {
					me.fireEvent('load', me, records, successful);
				}

				//TODO: deprecate this event, it should always have been 'load' instead. 'load' is now documented, 'read' is not.
				//People are definitely using this so can't deprecate safely until 2.x
				if (me.hasListeners.read) {
					me.fireEvent('read', me, records, successful);
				}

				//this is a callback that would have been passed to the 'read' function and is optional
				Ext.callback(operation.callback, operation.scope || me, [records, operation, successful]);
			},
			onErrorLoading: function(){
				Ext.MessageBox.alert(
					'Связь с сервером потеряна'
					,'Обратитесь к администратору медицинской организации. Нажмите "ОК" для попытки восстановления связи.'
					,function (btn) {
						if (btn === 'ok') {
							
							var progressBar = Ext.create('Ext.ProgressBar', {
								width: 300
							}),
							win = Ext.create('Ext.window.Window', {
								title: 'Попытка подключения',
								width: 300,
								height: 100,
								modal: true,
								closable: false,
								layout: {
									type: 'vbox',
									align: 'center',
									pack: 'center'
								},
								dockedItems: [
									progressBar,
									{
										xtype: 'container',
										dock: 'bottom',
										layout: {
											type: 'hbox',
											align: 'middle',
											pack: 'center',
											padding: 10
										},
										items: [
											{
												xtype: 'button',
												margin: '0 10',
												refId: 'exitBtn',
												text: 'Выход',
												handler: function(){
													window.onbeforeunload = null;
													window.location=C_LOGOUT;
												}
											}								 
										]
									}
								],
							}).show();

							count = 1;

							myFunction = function() {
								Ext.Ajax.request({
									url: '/?c=Common&m=checkConnect',
									//method: 'POST',
									callback : function(options, success, response) {
										if(response.status !== 200){
											progressBar.wait({
												interval: 1000, //bar will move fast!
												duration: 15000,
												increment: 15,
												text: 'Подключение ' + count + ' из 60',
												fn: function(){
													count += 1;
													if(count<61){myFunction();}
													else {
														window.onbeforeunload = null;
														window.location=C_LOGOUT;
													}
												}
											});
										}
										else{
											progressBar.updateProgress(1, 'Подключено');
											setTimeout(function(){win.close()}, 2000);
											win.down('button[refId=exitBtn]').disable();
										}
									}
								});
							};
							myFunction();

						} else {
						
						}
				})
			}
		});
	}
	
});
