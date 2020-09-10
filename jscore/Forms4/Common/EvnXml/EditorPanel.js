Ext6.define('common.EvnXml.EditorPanel', {
	extend: 'common.XmlTemplate.EditorPanel',
	alias: 'widget.evnxmleditor',

	markerMode: 'content',

	toolbarCfg: [
		'undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | insertobject | showsigns |',
		'templateedit templaterestore templateclear templatesave | html print T9 | scale | -> preloader togglemaximize save emdbutton'
	],

	toolbarMaximizedCfg: [
		'undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | image table | inputblock textgenerator |',
		'parameter document specmarker marker | showsigns | templateedit templaterestore templateclear templatesave | html print T9 | scale |',
		'-> preloader togglemaximize save emdbutton'
	],
	T9mode: true, //текущее состояние режима Т9 (отключается кнопкой в панели инструментов редактора)
	allowT9: false, //опция на уровне кода - в каких разделах использовать Т9
	
	insertword: function(text, pos, num) {
		var me = this;
		me.T9list.hide();
		
		var focusNode = me.T9list.focusNode;
		var range = me.T9list.range;
		var cursor_pos = me.T9list.cursor_pos;
		var pos_slice = me.T9list.pos_slice;
		if(num) pos_slice++;

		//~ me.mce.focus();
		
		if(pos>=0) { //обычно 1 == один пробел после текущего слова, перед вставляемым
			if(pos_slice>cursor_pos) focusNode.deleteData(cursor_pos, pos_slice-cursor_pos);
			focusNode.insertData(cursor_pos, ' '+text );
			me.mce.selection.getSel().setPosition(focusNode, cursor_pos+text.length+1);
		} else if(cursor_pos+pos>=0) {
			if(pos_slice>cursor_pos) focusNode.deleteData(cursor_pos, pos_slice-cursor_pos);
			focusNode.insertData(cursor_pos, text );
			focusNode.deleteData(cursor_pos+pos, -pos);
			me.mce.selection.getSel().setPosition(focusNode, cursor_pos+pos+text.length);
		}
		
		var bm = me.mce.selection.getBookmark();
		me.blocks.forEach(function(block) {
			if(block.setContent)
				block.setContent(block.getContent());
		});
		me.saveDocument();
		me.mce.selection.moveToBookmark(bm);		
	},

	onKeyUp: function(e) {
		this.callParent(arguments);

		if(getRegionNick()!='vologda' || !this.T9mode || !this.allowT9) return;
		var me = this;
		
		if(me.T9mode && getGlobalOptions().enableT9) {
			var menuvisibled = false;
			if(me.T9list.isVisible()) {
				menuvisibled = true;
				me.T9list.hide();
			}
			if(e.getKey().inlist([e.LEFT, e.RIGHT, e.UP, e.DOWN, e.CTRL, e.SHIFT, e.SPACE, e.ENTER, e.DELETE, e.BACKSPACE])) {
				return;
			}
			
			if(menuvisibled && e.getKey() >=49 && e.getKey()<=57) {
				var n = e.getKey()-48
				var item = me.T9list.items.getAt(n-1);
				if(item) {
					item.enter(item, n);
				}
				return;
			}
			var selection = me.mce.selection;
			var str = selection.getSel().focusNode.data;
			var pos = selection.getSel().focusOffset;
			if(!str || str.length==0) return;
			var s = str.slice(0,pos);
			if(! /[a-zа-я]/i.test(s.slice(-1))) return;
			var words = s.split(' ');
			if(words.length>3) words = words.slice(-3);
			for(i=0;i<words.length;i++) words[i]=words[i].trim();
			if(words[words.length-1].length<3) return;
			var query = words.join('+');
			
			me.T9list.focusNode = selection.getSel().focusNode;
			me.T9list.range = selection.getRng();
			me.T9list.cursor_pos = pos;
			var pos_slice = pos;
			while(str.slice(pos_slice, pos_slice+1)=='_') {
				pos_slice+=1;
			}
			me.T9list.pos_slice = pos_slice;
			me.T9list.elem_id = selection.getNode().id;
			
			Ext6.data.JsonP.request({
				url: 'https://predictor.yandex.net/api/v1/predict.json/complete',
				params: {
					key: 'pdct.1.1.20181125T124441Z.0246b2bc05d6531d.5417543d0833dce6b16b67c30b6920ed42b14423',
					q: query,
					lang: 'ru',
					limit: '10'
				},
				crossDomain: true,
				type: "GET", 
				dataType: "json", 
				scope: this, 
				callback: function (response, value, request) {					
					if(value && value.text && value.text.length>0) {
						me.T9list.removeAll();
						var i = 0;
						value.text.forEach(function(variant) {
							if(me.T9list.items.length<9) {
								i++;
								var boldword = '';
								var suffix = '';
								if(value.pos<0) {
									boldword = variant.slice(0, -value.pos);
									suffix = variant.slice(-value.pos);
								} else {
									boldword = '';
									suffix = variant;
								}
								me.T9list.add(Ext6.create('Ext6.menu.Item', {
									textvalue: variant,
									iconCls: 't9item'+i,
									pos: value.pos,
									text: '<b>'+boldword+'</b>'+suffix,
									handler: function(item) {
										this.enter(item, 0);
									},
									enter: function(item, number) {
										me.insertword(item.textvalue, item.pos, number);
									}
								}));
							}
						});
						var pos = this.mce.selection.getBoundingClientRect();			
						this.T9list.showBy(main_menu_panel.getEl(), 'tl-tl', [pos.left,pos.bottom]);
					}
				}
			});
		}
	},

	setParams: function(params) {
		var me = this;
		me.callParent(arguments);

		if (params.EvnXml_id) {
			me.params.EvnXml_id = params.EvnXml_id;
			this.swEMDPanel.setParams({
				EMDRegistry_ObjectName: 'EvnXml',
				EMDRegistry_ObjectID: params.EvnXml_id
			});
		} else {
			this.swEMDPanel.setParams({
				EMDRegistry_ObjectName: 'EvnXml',
				EMDRegistry_ObjectID: null,
				beforeSign: function() {
					if (typeof me.beforeSign == 'function') {
						me.beforeSign({
							callback: function(EvnXml_id) {
								if (EvnXml_id) {
									me.swEMDPanel.setParams({
										EMDRegistry_ObjectName: 'EvnXml',
										EMDRegistry_ObjectID: EvnXml_id
									});
									me.swEMDPanel.doSign();
								}
							}
						});

						return false;
					} else {
						return true;
					}
				}
			});
		}
		if(me.queryById('t9')){
			me.queryById('t9').setVisible(getGlobalOptions().enableT9);
			me.queryById('t9').toggle(me.T9mode);
		}
		if (typeof params.EvnXml_IsSigned !== 'undefined') {
			me.swEMDPanel.setIsSigned(params.EvnXml_IsSigned);
		}
	},

	openTemplateEditor: function() {
		var me = this;
		var params = Ext6.apply({}, me.params);

		if (!me.isReadOnly) {
			params.onSelect = me.onChangeTemplate;
		}

		params.allowedEvnClassList = this.allowedEvnClassList || [params.EvnClass_id];

		if (this.allowedXmlTypeEvnClassLink)
			params.allowedXmlTypeEvnClassLink = this.allowedXmlTypeEvnClassLink;
		else
		{
			params.allowedXmlTypeEvnClassLink = {};
			params.allowedXmlTypeEvnClassLink[params.XmlType_id] = [params.EvnClass_id];
		}

		getWnd('swXmlTemplateEditorWindow').show(params);
	},

	openSpecmarkerSelector: function() {
		var me = this;
		getWnd('swXmlTemplateSpecMarkerBlockSelectWindow').show({
			EvnClass_id: me.params.EvnClass_id,
			onSelect: function(SpecMarker) {
				me.addSpecMarkerBlocks([SpecMarker]);
				getWnd('swXmlTemplateSpecMarkerBlockSelectWindow').hide();
			}
		});
	},

	printDocument: function(mode) {
		var me = this;
		if (mode && !mode.inlist(['top','bottom'])) {
			return;
		}

		var params = {
			EvnXml_id: me.params.EvnXml_id,
			Evn_id: me.params.Evn_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_id: me.params.XmlTemplate_id,
			XmlTemplate_HtmlTemplate: me.getTemplate(me.T9mode),
			EvnXml_Data: Ext6.JSON.encode(me.xmlData),
			EvnXml_DataSettings: Ext6.JSON.encode(me.xmlDataSettings),
			printMode: mode
		};

		me.printForm.submit({
			target: '_blank',
			url : '/?c=EvnXml6E&m=printEvnXml',
			params: params
		});
	},

	saveDocument: function(saveAs, autoSave) {
		var me = this;

		if(!me.params || (autoSave && !me.params.EvnXml_id)) {
			return;
		}

		var params = {
			EvnXml_id: me.params.EvnXml_id,
			Evn_id: me.params.Evn_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_id: me.params.XmlTemplate_id,
			XmlTemplate_HtmlTemplate: me.getTemplate(me.T9mode),
			EvnXml_Data: Ext6.JSON.encode(me.xmlData),
			EvnXml_DataSettings: Ext6.JSON.encode(me.xmlDataSettings)
		};

		me.getToolbarButton('preloader').show();

		Ext6.Ajax.request({
			url: '/?c=EvnXml6E&m=saveEvnXml',
			params: params,
			callback: function(options, success, response) {
				me.getToolbarButton('preloader').hide();
				var responseObj = Ext6.JSON.decode(response.responseText);

				if (!Ext6.isEmpty(responseObj.EvnXml_id)) {
					me.setParams(responseObj);
					me.resetState(!me.isAutoSave);
					me.onContentChange();
					me.onSaveDocument(responseObj);
				}
			}
		});
	},

	onSaveDocument: function(response){

	},

	onChangeTemplate: function(data){

	},

	setScale: function(value) {
		var me = this;
		value = me.callParent(arguments);
		me.getToolbarButton('scale').setScale(value);
	},

	maximize: function() {
		var me = this;
		me.callParent(arguments);
		me.getToolbarButton('togglemaximize').setTooltip('Свернуть');
		me.getToolbarButton('togglemaximize').setIconCls('icon-restore');
	},

	restore: function() {
		var me = this;
		me.callParent(arguments);
		me.getToolbarButton('togglemaximize').setTooltip('Развернуть');
		me.getToolbarButton('togglemaximize').setIconCls('icon-maximize');
	},

	getToolbarButtonsCfg: function() {
		var me = this;

		//todo: Часть функций и кнопок наследовать из редактора шаблонов
		this.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel');

		var toolbarButtonsCfg = {
			insertobject: {
				text: 'Вставка',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: me.createMenu({
					items: [{
						iconCls: 'icon-image',
						text: 'Изображение',
						handler: function() {
							me.mce.execCommand('mceImage');
						}
					}, {
						iconCls: 'icon-table',
						text: 'Таблица',
						menu: Ext6.create('sw.table.Menu', {
							select: me.insertTable.bind(me)
						})
					}, '-', {
						iconCls: 'icon-input-block',
						text: 'Область ввода данных',
						tooltip: 'Раздел для ввода текста вручную',
						handler: function() {
							me.openInputBlockSelector();
						}
					}, {
						iconCls: 'icon-text-generator',
						text: 'Генерация текста',
						tooltip: 'Помощник автоматического составления текста',
						//disabled: true,
						handler: function() {
							me.openTextGenerator();
						}
					}, {
						text: 'Анкета',
						tooltip: 'Анкета',
						itemId: 'anketmenu',
						// iconCls: //?
						menu: Ext6.create('Ext6.menu.Menu', {
							isLoaded: false,
							listeners: {
								beforeshow: function(submenu) {
									submenu.initMenu();
								}
							},
							width: 180,
							items: [],
							initMenu: function() {
								var anketmenu = this;
								if(!anketmenu.isLoaded) {
									//~ anketmenu.mask('Загрузка...');
									Ext6.Ajax.request({
										url: '/?c=MedicalForm&m=getMedicalFormActualList', //url: '/?c=OnkoCtrl&m=loadPersonOnkoProfileList',
										params: {
											Person_id: me.params.Person_id
										},
										callback: function(options, success, response) {
											//anketmenu.isLoaded = true; //пусть обновляется на каждое раскрытие (автообновление)
											//~ anketmenu.unmask();
											var data = Ext6.JSON.decode(response.responseText);
											if(data.success && !Ext6.isEmpty(data.list)) {
												anketmenu.removeAll();
												if(data.list.length > 0) {
													
													data.list.forEach(function(rec) {
														anketmenu.add({
															text: rec.PersonProfileType_Name,
															record: rec,
															handler: function(menuitem, a,b) {
																me.addAnketa(menuitem.record);
															}
														});
													});
												}
											}
										}
									});
								}
							}
						})
					}, '-', {
						iconCls: 'icon-parameter',
						text: 'Параметр',
						tooltip: 'Блок для выбора значения из выпадающего списка, установки флага или переключателя',
						handler: function() {
							me.openParameterSelector();
						}
					}, {
						iconCls: 'icon-document',
						text: 'Документ',
						tooltip: 'Данные из другого документа, связанного с пациентом',
						disabled: true,
						handler: function() {
							me.openDocumentSelector();
						}
					}, {
						iconCls: 'icon-specmarker',
						text: 'Спецмаркер',
						tooltip: 'Переменная, которая при печати документа заменяется на значение из данных о человеке, посещении, осмотре и т.д.',
						handler: function() {
							me.openSpecmarkerSelector();
						}
					}, {
						iconCls: 'icon-marker',
						text: 'Маркер документа',
						tooltip: 'Информация, которая будет введена в другом документе, например, для подстановки информации об оказанных услугах',
						handler: function() {
							me.openMarkerSelector();
						}
					}]
				})
			},
			templateedit: {
				iconCls: 'icon-template-edit',
				tooltip: 'Открыть шаблон в редакторе',
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				handler: function() {
					me.openTemplateEditor();
				}
			},
			templatesave: {
				iconCls: 'icon-template-save',
				tooltip: 'Сохранить текущий документ в качестве шаблона для дальнейшего использования',
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				menu: [],	//Штобы отобразилась стрелка
				handler: function() {
					me.saveTemplate(true);
				}
			},
			templaterestore: {
				iconCls: 'icon-template-restore',
				tooltip: 'Отменить изменения и повторно сформировать документ по шаблону',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.restoreTemplate();
				}
			},
			templateclear: {
				iconCls: 'icon-template-clear',
				tooltip: 'Полностью удалить содержимое документа',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.clearTemplate();
				}
			},
			print: {
				iconCls: 'icon-print',
				tooltip: 'Печать',
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				handler: function() {
					me.printDocument();
				}
			},
			printMenu: {
				iconCls: 'icon-print',
				tooltip: 'Печать',
				hidden: true,
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				menu: me.createMenu({
					items: [{
						iconCls: 'icon-print-top',
						text: 'Печать на верхней половине листа',
						disabled: true,
						handler: function() {
							me.printDocument('top');
						}
					}, {
						iconCls: 'icon-print-bottom',
						text: 'Печать на нижней половине листа',
						disabled: true,
						handler: function() {
							me.printDocument('bottom');
						}
					}]
				})
			},
			T9: {
				text: 'T9',
				itemId: 't9',
				tooltip: 'Предиктивный ввод текста',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				toggleHandler: function (button, pressed, eOpts) {
					me.T9mode = pressed;
				}
			},
			preloader: {
				xtype: 'panel',
				hidden: true,
				html: '<span class="icon-preloader"/>'
			},
			scale: {
				xtype: 'swscalebutton',
				value: me.scale,
				onScale: me.onScale.bind(me),
				minMenuWidth: 80
			},
			togglemaximize: {
				iconCls: me.maximized?'icon-restore':'icon-maximize',
				tooltip: 'Развернуть',
				handler: function() {
					me.toggleMaximize();
				}
			},
			emdbutton: {
				xtype: 'panel',
				border: false,
				height: 25,
				width: 45,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				items: [
					me.swEMDPanel
				]
			}
		};

		toolbarButtonsCfg = Ext6.apply(me.callParent(), toolbarButtonsCfg);

		if (me.isAutoSave) {
			toolbarButtonsCfg.save.hidden = true;
		}

		return toolbarButtonsCfg;
	},

	initComponent: function() {
		var me = this;

		me.printForm = Ext6.create('Ext6.form.Panel', {
			standardSubmit: true
		});

		me.callParent(arguments);
		
		me.T9list = Ext6.create('Ext6.menu.Menu', {
			width: 200,
			items: []
		});
	}
});