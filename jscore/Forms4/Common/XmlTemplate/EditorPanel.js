Ext6.define('common.XmlTemplate.EditorPanel', {
	extend: 'base.EditorPanel',
	alias: 'widget.xmltemplateeditor',
	requires: [
		'base.EditorTableBlock',
		'common.XmlTemplate.tools.EditorUndoManager',
		'common.XmlTemplate.SpecMarkerBlockDropdownSelectPanel',
		'common.XmlTemplate.EditorInputBlock',
		'common.XmlTemplate.AnketaBlock',
		'common.XmlTemplate.EditorParameterBlock',
		'common.XmlTemplate.EditorSpecMarkerBlock',
		'common.XmlTemplate.EditorMarkerBlock',
		'common.XmlTemplate.EditorSpecMarkerNotice'
	],

	params: {},
	xmlData: {},
	xmlDataSettings: {},
	originalXmlData: {},
	originalXmlDataSettings: {},
	originalTemplate: '',
	savedXmlData: {},
	savedXmlDataSettings: {},
	savedTemplate: '',
	markerMode: 'marker',
	signsVisible: false,
	delayTypingTime: 500,
	headerPanel: null,
	headerHidden: true,
	footerPanel: null,
	footerHidden: true,
	scale: 100,
	isAutoSave: false,
	delayAutoSave: 5000,	//5 секунд
	editing: false,
	maximized: false,
	maximizedContainer: null,
	disableStandartUndoManager: true,
	useUserEventSaveDocumentFunction: false,
	toolbarCfg: [
		'savemenu | undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | image table |',
		'inputblock textgenerator | parameter document specmarker marker | showsigns | templaterestore templateclear | html'
	],

	setParams: function(params) {
		var me = this;
		var keys = [
			'XmlType_id','XmlType_Name','XmlTemplate_id','XmlTemplate_Caption','XmlTemplate_Descr', 'XmlTemplate_IsDefault',
			'XmlTemplateScope_id','XmlTemplateCat_id','XmlTemplateSettings_id','Author_id','Author_Fin', 'Evn_id',
			'EvnClass_id','EvnClass_Name','Evn_id','Person_id','MedStaffFact_id','MedPersonal_id','LpuSection_id','Lpu_id','Lpu_Nick'
		];
		keys.forEach(function(key) {
			if (!Ext6.isEmpty(params[key])) {
				me.params[key] = params[key];
			}
		});

		if (me.headerPanel && me.headerPanel.setData) {
			me.headerPanel.setData(me.params);
		}

		if (!Ext6.isEmpty(params.EvnPrescrCount)) {
			me.EvnPrescrCount = params.EvnPrescrCount;
		}
		if (!Ext6.isEmpty(params.EvnStickCount)) {
			me.EvnStickCount = params.EvnStickCount;
		}
	},

	setEditing: function(editing) {
		var me = this;
		me.editing = editing;
		if (me.rendered) {
			me.setDisabled(!editing);
		} else {
			//me.disabled = true;
		}
	},

	resetState: function(resetUndo) {
		var me = this;
		if (resetUndo === undefined) {
			resetUndo = true;
		}
		me.needResetState = false;
		me.savedXmlData = Ext6.apply({}, me.xmlData);
		me.savedXmlDataSettings = Ext6.decode(Ext6.encode(me.xmlDataSettings));
		me.savedTemplate = me.getTemplate();
		me.savedContent = me.getContent();
		if (resetUndo) {
			me.getUndoManager().clear();
			me.getUndoManager().add();
		}
	},

	reset: function() {
		var me = this;
		me.setEditing(false);
		me.params = {};
		me.xmlData = {};
		me.xmlDataSettings = {};
		me.callParent();
		me.originalXmlData = {};
		me.originalXmlDataSettings = {};
		me.originalTemplate = me.getTemplate();
		me.savedXmlData = {};
		me.savedXmlDataSettings = {};
		me.savedTemplate = me.getTemplate();
		me.savedContent = me.getContent();
		me.refreshNotice();
	},

	refreshNotice: function() {
		var me = this;

		/*var prescrSpecMarkerBlock = me.blocks.find(function(block) {
			return block.xtype == 'xmltemplatespecmarkerblock' && block.isPrescr();
		});

		me.specMarkerNotice.setVisible(
			!Ext6.isEmpty(me.params.Evn_id) &&
			!Ext6.isEmpty(prescrSpecMarkerBlock) &&
			prescrSpecMarkerBlock.getPrescrCount() != me.EvnPrescrCount
		);*/
	},

	wrapContentInParagraph: function() {
		var me = this;
		me.el.query('.sw-editor-page-content > *').filter(function(el) {
			return !me.mce.dom.isBlock(el);
		}).forEach(function(el) {
			tinyMCE.helpers.wrapElInParagraph(me.mce, el);
		});
	},

	load: function(opts) {
		opts = opts || {};
		callback = opts.callback || Ext6.emptyFn;
		var me = this;

		me.setEditing(false);
		me.mask('Загрузка...');

		var params = Ext6.apply({}, me.params);
		if (opts.resetTemplate) {
			params.reset = true;
		}
		if (opts.loadEmpty) {
			params.loadEmpty = true;
		}

		if (opts.resetState) {
			me.getUndoManager().clear();
		}
		if (me.headerPanel && me.headerPanel.setData) {
			me.headerPanel.setData({});
		}
		me.setContent('', {addUndoLevel: false});
		me.onContentChange();

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getXmlTemplateForEvnXml',
			params: params,
			success: function(response) {
				me.unmask();
				var responseData = Ext6.JSON.decode(response.responseText);

				if (responseData.Alert_Msg) {
					sw4.showInfoMsg({
						panel: me,
						type: 'warning',
						text: responseData.Alert_Msg,
						hideDelay: 10000
					});
				}

				if (responseData.success && responseData.xmlData) {
					me.setParams(responseData);

					me.xmlDataSettings = !Ext6.isArray(responseData.xmlDataSettings)
						?responseData.xmlDataSettings:{};
					me.originalXmlDataSettings = Ext6.decode(Ext6.encode(me.xmlDataSettings));

					var xmlData = !Ext6.isEmpty(responseData.originalXmlData)
						?responseData.originalXmlData:responseData.xmlData;
					me.xmlData = !Ext6.isEmpty(xmlData)?xmlData:{};
					me.originalXmlData = Ext6.apply({}, me.xmlData);

					me.cache.setData('XmlDataSection', responseData.XmlDataSections);
					me.cache.setData('ParameterValue', responseData.ParameterValues, true);
					me.cache.setData('SpecMarker', responseData.SpecMarkers, true);
					me.cache.setData('AnketMarker', responseData.AnketMarkers, true);

					me.setContent(responseData.template);
					me.xmlData = !Ext6.isEmpty(responseData.xmlData)?responseData.xmlData:{};
					me.blocks = me.renderBlocks();
					if (opts.resetState) {
						me.resetState(!me.isAutoSave);
					}
					me.setEditing(true);
					me.getUndoManager().add();
					me.onContentChange();
				}
				me.setReadOnly(me.isReadOnly);
				callback(response);
			},
			failure: function(response) {
				me.unmask();
				me.setEditing(false);
				me.onContentChange();
				callback(response);
			}
		});
	},

	addInputBlocks: function(XmlDataSectionList, content) {
		var me = this;
		if (!Ext6.isArray(XmlDataSectionList) || XmlDataSectionList.length == 0) {
			return;
		}

		me.cache.setData('XmlDataSection', XmlDataSectionList);

		var blockClass = common.XmlTemplate.EditorInputBlock;
		var blocks = blockClass.insertToEditor(me, XmlDataSectionList, content);
		if (blocks.length > 0) blocks[0].focus();
	},
	
	addAnketa: function(anketa_info) {
		var me = this;
		
		me.cache.setData('AnketMarker', me.cache.getData('AnketMarker').push(anketa_info));
		var blockClass = common.XmlTemplate.AnketaBlock;
		
		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getAnketMarkerContent',
			params: { MedicalFormPerson_id: anketa_info.MedicalFormPerson_id},
			success: function(response) {
				var resp = Ext6.JSON.decode(response.responseText);
				if(resp && resp.data && resp.data.length>0 && resp.data[0].content) {
					var anketa_data = resp.data[0].content;
					blockClass.insertToEditor(me, anketa_info, anketa_data);
				}
			}
		});
	},

	openInputBlockSelector: function() {
		var me = this;

		var selection = me.mce.selection;

		var contentBox = Ext6.fly(selection.getNode()).up('.template-block');
		if (contentBox) {
			Ext6.MessageBox.show({
				title: 'Запрещено',
				msg: 'В этом месте нельзя создавать область для ввода данных!',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return;
		}

		var appliedInputBlockList = me.blocks.filter(function(block) {
			return (
				block.xtype == 'xmltemplateinputblock' &&
				block.XmlDataSection.XmlDataSection_SysNick != 'autoname'
			);
		}).map(function(block) {
			return block.XmlDataSection.XmlDataSection_SysNick;
		});

		getWnd('swXmlTemplateInputBlockSelectWindow').show({
			appliedInputBlockList: appliedInputBlockList,
			callback: function(XmlDataSectionList) {
				me.addInputBlocks(XmlDataSectionList, selection.getContent());
			}
		});
	},

	addParameterBlocks: function(ParameterValueList) {
		var me = this;
		if (!Ext6.isArray(ParameterValueList) || ParameterValueList.length == 0) {
			return;
		}

		me.cache.setData('ParameterValue', ParameterValueList, true);

		var blockClass = common.XmlTemplate.EditorParameterBlock;
		var blocks = blockClass.insertToEditor(me, ParameterValueList);
		if (blocks[0] && blocks[0].focus) {
			blocks[0].focus();
		}
	},

	openParameterSelector: function() {
		var me = this;
		getWnd('swXmlTemplateParameterBlockSelectWindow').show({
			callback: function(ParameterValueList) {
				log({ParameterValueList});
				me.addParameterBlocks(ParameterValueList);
			}
		});
	},

	openDocumentSelector: function() {

	},

	loadSpecMarkers: function(names, callback) {
		var me = this;

		var params = {
			EvnClass_id: me.params.EvnClass_id,
			names: Ext6.encode(names)
		};

		me.mask('Получение данных спецмаркеров');

		Ext6.Ajax.request({
			params: params,
			url: '/?c=XmlTemplate6E&m=loadSpecMarkerList',
			callback: function(request, success, response) {
				me.unmask();
				var responseObj = Ext6.decode(response.responseText);
				if (success) {
					me.cache.setData('SpecMarker', responseObj, true);
					callback(responseObj);
				} else {
					callback(null);
				}
			}
		});
	},

	getSpecMarkerBlocksContent: function(Evn_id, SpecMarkers, callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		var params = {
			Evn_id: Evn_id
		};

		params.SpecMarkerIds = Ext6.encode(SpecMarkers.map(function(item) {
			return item.id
		}));

		me.mask('Получение содержимого для маркеров');
		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getSpecMarkerContent',
			params: params,
			success: function(response) {
				me.unmask();
				var responseObj = Ext6.decode(response.responseText);

				responseObj.data.forEach(function(item) {
					if (!me.xmlData['specMarker_'+item.id]) {
						me.xmlData['specMarker_' + item.id] = item.content;
					}
				});

				callback(responseObj.data);
			},
			failure: function() {
				me.unmask();
			}
		});
	},

	refreshSpecMarkerBlocksContent: function(callback) {
		var me = this;
		var Evn_id = me.params.Evn_id;
		callback = callback || Ext6.emptyFn;

		var listForLoad = me.blocks.filter(function(block) {
			return block.xtype == 'xmltemplatespecmarkerblock';
		}).map(function(block) {
			return block.SpecMarker;
		});

		if (!Evn_id || listForLoad.length == 0) {
			callback(null);
			return;
		}

		me.getSpecMarkerBlocksContent(me.params.Evn_id, listForLoad, function(data) {
			if (!data) {
				callback(data);
				return;
			}
			data.forEach(function(item) {
				me.xmlData['specMarker_'+item.id] = item.content;
			});
			me.renderBlocks(true);
			callback(null);
		});
	},

	addSpecMarkerBlocks: function(SpecMarkers) {
		var me = this;
		if (!Ext6.isArray(SpecMarkers) || SpecMarkers.length == 0) {
			return;
		}

		var blockClass = common.XmlTemplate.EditorSpecMarkerBlock;
		var Evn_id = me.params.Evn_id;
		var listForLoad = SpecMarkers.filter(function(SpecMarker) {
			return !me.xmlData['specMarker_'+SpecMarker.id];
		});

		me.cache.setData('SpecMarker', SpecMarkers, true);

		if (!Evn_id || listForLoad.length == 0) {
			blockClass.insertToEditor(me, SpecMarkers);
		} else {
			me.getSpecMarkerBlocksContent(Evn_id, listForLoad, function() {
				blockClass.insertToEditor(me, SpecMarkers);
			});
		}
	},

	openSpecmarkerSelector: function() {
		var me = this;

		if (!me.specMarkerDropdown) me.specMarkerDropdown = Ext6.create(
			'common.XmlTemplate.SpecMarkerBlockDropdownSelectPanel'
		);

		//var el = me.mce.selection.getStart();

		me.specMarkerDropdown.show({
			align: 'tr-br?',
			target: me.getToolbarButton('specmarker'),
			EvnClass_id: me.params.EvnClass_id,
			onSelect: function (SpecMarker) {
				me.specMarkerDropdown.hide();
				//me.setCursorLocationByEl(el);
				me.addSpecMarkerBlocks([SpecMarker]);
			}
		});
	},

	getMarkerBlockContent: function(Evn_id, markerData, callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		var params = {
			Evn_id: Evn_id,
			markerData: {}
		};

		var _markerData = {};
		Object.keys(markerData).forEach(function(key) {
			_markerData[key] = markerData[key].value;
		});
		params.markerData = Ext6.encode(_markerData);

		me.mask('Получение содержимого маркера');
		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=getMarkerContent',
			params: params,
			success: function(response) {
				me.unmask();
				var responseObj = Ext6.decode(response.responseText);

				if (!me.xmlData[responseObj.key]) {
					me.xmlData[responseObj.key] = responseObj.content;
				}

				callback(responseObj);
			},
			failure: function() {
				me.unmask();
			}
		});
	},

	addMarkerBlock: function(markerData) {
		var me = this;

		var blockClass = common.XmlTemplate.EditorMarkerBlock;
		var Evn_id = me.params.Evn_id;

		me.getMarkerBlockContent(Evn_id, markerData, function(data) {
			blockClass.insertToEditor(me, data.key, markerData);
		});
	},

	openMarkerSelector: function() {
		var me = this;
		getWnd('swXmlTemplateMarkerBlockSelectWindow').show({
			EvnClass_id: me.params.EvnClass_id,
			callback: function(markerData) {
				me.addMarkerBlock(markerData);
			}
		});
	},

	openTextGenerator: function() {
		var me = this;
		var params = {
			XmlTemplate_id: me.params.XmlTemplate_id,
			MedStaffFact_id: me.params.MedStaffFact_id,
			MedPersonal_id: me.params.MedPersonal_id,
			LpuSection_id: me.params.LpuSection_id,
			EvnClass_id: me.params.EvnClass_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_Caption: me.params.XmlTemplate_Caption,
			XmlTemplate_Descr: me.params.XmlTemplate_Descr,
			XmlTemplateScope_id: me.params.XmlTemplateScope_id,
			XmlTemplateCat_id: me.params.XmlTemplateCat_id,
			Author_id: me.params.Author_id,
			mode: 'properties'
		};
		var onOpenTextSaveTemplate = function (response) {
			var responseData = Ext6.JSON.decode(response.responseText);
			if (responseData.Alert_Msg) {
				sw4.showInfoMsg({
					panel: me,
					type: 'warning',
					text: responseData.Alert_Msg,
					hideDelay: 10000
				});
			}
			if (responseData.success && responseData.xmlData) {
				var selection = me.mce.selection;
				selection.setContent(responseData.template);
				me.saveDocument(false, true);
				/*
				me.addInputBlocks([{
					XmlDataSection_Name: responseData.template,
					XmlDataSection_SysNick: "textBlock" + me.blocks.length.toString()
				}], selection.getContent());
				/**
				 me.cache.setData('ParameterValue', ParameterValueList, true);
				 let blockClass = common.XmlTemplate.EditorParameterBlock;
				 let blocks = blockClass.insertToEditor(me, ParameterValueList);
				 if (blocks[0] && blocks[0].focus) {
					blocks[0].focus();
				 }
				 */
				/*
				me.xmlDataSettings = !Ext6.isArray(responseData.xmlDataSettings) ? responseData.xmlDataSettings : {};
				me.originalXmlDataSettings = Ext6.decode(Ext6.encode(me.xmlDataSettings));
				let xmlData = !Ext6.isEmpty(responseData.originalXmlData) ? responseData.originalXmlData : responseData.xmlData;
				me.xmlData = !Ext6.isEmpty(xmlData) ? xmlData : {};
				me.originalXmlData = Ext6.apply({}, me.xmlData);
				me.cache.setData('XmlDataSection', responseData.XmlDataSections);
				//me.cache.setData('ParameterValue', responseData.ParameterValues, true);
				//me.cache.setData('SpecMarker', responseData.SpecMarkers, true);
				me.setContent(responseData.template);
				me.xmlData = !Ext6.isEmpty(responseData.xmlData) ? responseData.xmlData : {};
				me.blocks = me.renderBlocks();
				me.setEditing(true);
				me.getUndoManager().add();
				me.onContentChange();
				*/
			}
		};
		getWnd('swXmlTemplateOpenTextWindow').show({
			params: params,
			callback: onOpenTextSaveTemplate
		});
	},

	restoreTemplate: function() {
		var me = this;
		me.load({resetTemplate: true});
	},

	clearTemplate: function() {
		var me = this;
		me.load({loadEmpty: true});
	},

	createTemplate: function(params, callback) {
		var me = this;
		callback = callback || Ext6.emptyFn;

		me.reset();
		me.setParams(params);

		me.load({
			loadEmpty: true,
			callback: callback
		});
	},

	editProperties: function() {
		var me = this;

		if (!me.params.XmlTemplate_id) {
			return;
		}

		var params = {
			XmlTemplate_id: me.params.XmlTemplate_id,
			MedStaffFact_id: me.params.MedStaffFact_id,
			MedPersonal_id: me.params.MedPersonal_id,
			LpuSection_id: me.params.LpuSection_id,
			EvnClass_id: me.params.EvnClass_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_Caption: me.params.XmlTemplate_Caption,
			XmlTemplate_Descr: me.params.XmlTemplate_Descr,
			XmlTemplateScope_id: me.params.XmlTemplateScope_id,
			XmlTemplateCat_id: me.params.XmlTemplateCat_id,
			Author_id: me.params.Author_id,
			mode: 'properties'
		};

		var onSaveTemplate = function(response) {
			if (response) {
				me.setParams(response);
			}
		};

		getWnd('swXmlTemplateSaveWindow').show({
			params: params,
			callback: onSaveTemplate
		});
	},

	autoSave: function() {
		var me = this;
		if (me.isInitEditor && me.isContentChanged()) {
			if (me.delaySaveId) {
				clearTimeout(me.delaySaveId);
			}
			me.delaySaveId = setTimeout(
				me.doAutoSave.bind(me),
				me.delayAutoSave
			);
		}
	},

	doAutoSave: function() {
		var me = this;
		if (me.delaySaveId && me.isContentChanged()) {
			me.delaySaveId = null;
			me.saveDocument(false, true);
		}
	},

	saveTemplate: function(saveAs, forceWindow) {
		var me = this;
		if (me.params.Author_id != getGlobalOptions().pmuser_id) {
			saveAs = true;
		}

		var params = {
			XmlTemplate_id: (!saveAs && me.params.XmlTemplate_id)
				? me.params.XmlTemplate_id : null,
			MedStaffFact_id: me.params.MedStaffFact_id,
			MedPersonal_id: me.params.MedPersonal_id,
			LpuSection_id: me.params.LpuSection_id,
			EvnClass_id: me.params.EvnClass_id,
			XmlType_id: me.params.XmlType_id,
			XmlTemplate_Caption: !saveAs?me.params.XmlTemplate_Caption:null,
			XmlTemplate_Descr: me.params.XmlTemplate_Descr,
			XmlTemplateScope_id: me.params.XmlTemplateScope_id,
			XmlTemplateCat_id: me.params.XmlTemplateCat_id,
			XmlTemplate_HtmlTemplate: me.getTemplate(),
			EvnXml_Data: Ext6.JSON.encode(me.xmlData),
			EvnXml_DataSettings: Ext6.JSON.encode(me.xmlDataSettings),
			allowedEvnClassList: this.allowedEvnClassList,
			allowedXmlTypeEvnClassLink: this.allowedXmlTypeEvnClassLink,
			mode: 'template'
		};
		var onSaveTemplate = function(response) {
			var action = (!params.XmlTemplate_id)?'create':'update';

			if (response) {
				me.setParams(response);
				me.resetState(!me.isAutoSave);
				me.onContentChange();
				me.onSaveTemplate(action, response);
			}
		};

		if (forceWindow || !params.XmlTemplate_id) {
			getWnd('swXmlTemplateSaveWindow').show({
				params: params,
				callback: onSaveTemplate
			});
		} else {
			var infoMsg = sw4.showInfoMsg({
				panel: me,
				type: 'loading',
				text: 'Сохранение ...',
				hideDelay: null
			});

			Ext6.Ajax.request({
				url: '/?c=XmlTemplate6E&m=saveXmlTemplate',
				params: params,
				success: function(response) {
					if (infoMsg) infoMsg.hide();
					var responseObj = Ext6.decode(response.responseText);
					if (responseObj.success) {
						sw4.showInfoMsg({
							panel: me,
							type: 'success',
							text: 'Данные сохранены'
						});
					}
					onSaveTemplate(responseObj);
				},
				failure: function() {
					if (infoMsg) infoMsg.hide();
					onSaveTemplate(null);
				}
			});
		}
	},

	onSaveTemplate: function(response, action) {

	},

	saveDocument: function(saveAs, autoSave) {
		let me = this;
		if (me.useUserEventSaveDocumentFunction === true) {
			this.onSaveTemplate(me.getTemplate(), saveAs);
		} else {
			this.saveTemplate(saveAs);
		}
	},

	setDefault: function(opts) {
		var me = this;
		opts = opts || {};

		if (!me.params.XmlTemplate_id && me.params.XmlTemplate_IsDefault != 2) {
			return;
		}

		var params = {
			XmlTemplate_id: me.params.XmlTemplate_id,
			XmlType_id: me.params.XmlType_id,
			EvnClass_id: me.params.EvnClass_id,
			LpuSection_id: me.params.LpuSection_id,
			MedPersonal_id: me.params.MedPersonal_id,
			MedStaffFact_id: me.params.MedStaffFact_id,
			MedService_id: me.params.MedService_id,
			ignoreCheckSetDefault: opts.ignoreCheckSetDefault || false
		};

		var YesNoFn = function(responseObj, buttonId){
			if ( buttonId == 'yes' ) {
				switch (Number(responseObj.Error_Code)) {
					case 201:
						opts.ignoreCheckSetDefault = true;
						break;
				}
				me.setDefault(opts);
			}
		};

		var infoMsg = sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение ...',
			hideDelay: null
		});

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=setXmlTemplateDefault',
			params: params,
			callback: function(options, success, response) {
				if (infoMsg) infoMsg.hide();
				var responseObj = Ext6.decode(response.responseText);
				if (responseObj.Error_Msg == 'YesNo') {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId){
							YesNoFn(responseObj, buttonId);
						},
						icon: Ext6.MessageBox.QUESTION,
						msg: responseObj.Alert_Msg,
						title: langs('Продолжить сохранение?')
					});
				}
				if (responseObj.newXmlTemplate_id) {
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены'
					});
					me.setParams({XmlTemplate_IsDefault: 2});
					me.onSetDefault(responseObj);
				}
			}
		});
	},

	onSetDefault: function(response){},

	getXmlData: function() {
		var me = this;
		var level = me.getUndoManager().current || {};
		return level.xmlData || me.xmlData || {};
	},

	getTemplate: function(reset) {
		var me = this;
		var level = (me.getUndoManager() || {}).current || {};
		return (reset || !level.template) ? me.generateTemplate() : level.template;
	},

	correctTemplate: function(template, convertBlocksToPlaceHolders) {
		var me = this;
		var dom = document.createElement('div');
		var removeEl = function(el){el.remove()};

		dom.insertAdjacentHTML('afterbegin', template);

		me.forEachNode(dom.querySelectorAll('p[data-mce-bogus]'), function(el) {
			var text = document.createTextNode(el.innerText);
			el.parentNode.replaceChild(text, el);
		});

		me.forEachNode(dom.querySelectorAll('.mce-visual-caret'), removeEl);
		me.forEachNode(dom.querySelectorAll('.mce-offscreen-selection'), removeEl);
		me.forEachNode(dom.querySelectorAll('[data-mce-caret]'), removeEl);

		me.forEachNode(dom.querySelectorAll('.ext-anchor'), function(el) {
			el.classList.remove('ext-anchor');
		});
		me.forEachNode(dom.querySelectorAll('.mce-item-table'), function(el) {
			el.classList.remove('mce-item-table');
		});

		if (convertBlocksToPlaceHolders) {
			me.forEachNode(dom.querySelectorAll('.editor-table-block-container'), function(el, index) {
				var tableEl = el.querySelector('table');
				if (tableEl) el.parentNode.replaceChild(tableEl, el);
			});

			me.blocks.forEach(function(block) {
				var id = block.getId();
				var text = document.createTextNode(block.getPlace());

				me.forEachNode(dom.querySelectorAll('[for='+id+']'), function(el, index) {
					if (index == 0) el.parentNode.replaceChild(text, el);
					else el.remove();
				});
			});

			me.forEachNode(dom.querySelectorAll('.spec-marker-block-container'), function(el, index) {
				var text = document.createTextNode(el.innerText);
				el.parentNode.replaceChild(text, el);
			});
		}

		me.forEachNode(dom.querySelectorAll('[data-mce-bogus]:not(.editor-block-br)'), function(el) {
			if (el.nodeName != 'BR') {
				el.removeAttribute('data-mce-bogus');
			}
		});

		me.forEachNode(dom.querySelectorAll('div:empty:not(.editor-block-container), p:empty'), removeEl);

		var response = dom.innerHTML;
		if (Ext6.isEmpty(response)) {
			response = '<br data-mce-bogus="1">';
		}

		return response.replace('\ufeff', '');
	},

	generateTemplate: function(content) {
		var me = this;
		return me.correctTemplate(content || me.getContent(true), true);
	},

	getContentForHtmlView: function() {
		return this.getTemplate();
	},

	stripSpecialChars: function(str) {
		return String(str||'').replace(/[\n\t\r]/g, '');
	},

	stripSpecialCharsInObject: function(data) {
		var me = this;
		var result = {};
		var keys = Object.keys(data);
		for (var key in keys) {
			result[key] = (Ext6.isObject(data[key]))
				? me.stripSpecialCharsInObject(data[key])
				: me.stripSpecialChars(data[key]);
		}
		return result;
	},

	isContentChanged: function() {
		var me = this;
		var savedXmlData = me.stripSpecialCharsInObject(me.savedXmlData);
		var xmlData = me.stripSpecialCharsInObject(me.xmlData);
		var savedXmlDataSettings = Ext6.encode(me.stripSpecialCharsInObject(me.savedXmlDataSettings));
		var xmlDataSettings = Ext6.encode(me.stripSpecialCharsInObject(me.xmlDataSettings));

		return me.editing && !(
			me.savedTemplate == me.getTemplate() &&
			Ext6.Object.equals(savedXmlData, xmlData) &&
			savedXmlDataSettings == xmlDataSettings
		);
	},

	delayTyping: function(callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;

		if (me.delayTypingId) {
			clearTimeout(me.delayTypingId);
		}
		me.delayTypingId = setTimeout(function() {
			me.delayTypingId = null;

			callback();

			me.getUndoManager().add();
			me.onContentChange();
		}, me.delayTypingTime || 500);
	},

	onContentChange: function() {
		var me = this;
		me.callParent(arguments);

		var prescrSpecMarkerBlock = me.blocks.find(function(block) {
			return block.xtype == 'xmltemplatespecmarkerblock' && block.isPrescr();
		});

		if (prescrSpecMarkerBlock && prescrSpecMarkerBlock.recommendBtn && !me.xmlData.recommendations) {
			prescrSpecMarkerBlock.recommendBtn.show();
		}
	},

	onKeyDown: function(e) {
		var me = this
		var el = Ext6.fly(e.target);
		var block = null;

		if (common.XmlTemplate.EditorInputBlock.isCaptionEl(el)) {
			block = Ext6.getCmp(el.up('.template-block').id);
			block.onCaptionTextKeyDown(e);
			if (e.stopped) return;
		}

		me.callParent(arguments);
		me.delayTyping();
	},

	onEditorBlur: function(e) {
		var me = this;
		me.callParent(arguments);
		var activeEl = Ext6.dom.Element.getActiveElement();
		if (!me.el.contains(activeEl)) {
			me.doAutoSave();
		}
		if (me.isAutoSave === true) {
			me.saveDocument(false, true);
		}
	},

	beforeExecCommand: function(command) {
		var me = this;
		if (me.callParent(arguments) === false) {
			return false;
		}

		var selection = me.mce.selection;
		var el = Ext6.fly(selection.getNode());
		var block = null;

		var allowedForEditInputBlockCaption = [
			'fontsize','paste','bold','italic','underline','strikethrough','subscript','superscript',
			'outdent','indent','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'
		];

		if (common.XmlTemplate.EditorInputBlock.isCaptionEl(el) && !command.inlist(allowedForEditInputBlockCaption)) {
			return false;
		}
		if (common.XmlTemplate.EditorInputBlock.isDataEl(el)) {
			block = Ext6.getCmp(el.up('.template-block').id);
			block.onFocus();
		}

		return true;
	},

	afterExecCommand: function(command) {
		var me = this;
		var selection = me.mce.selection;
		var el = Ext6.fly(selection.getNode());
		var block = null;

		me.callParent(arguments);

		if (common.XmlTemplate.EditorInputBlock.isCaptionEl(el)) {
			block = Ext6.getCmp(el.up('.template-block').id);
			block.refreshXmlDataLabel();
		}
		if (common.XmlTemplate.EditorInputBlock.isDataEl(el)) {
			block = Ext6.getCmp(el.up('.template-block').id);
			block.refreshXmlData();
		}
	},

	renderBlocks: function(reset) {
		var me = this;
		var template = reset ? me.generateTemplate() : me.getContent();
		var renderBlock = function(blocks, block) {
			//Внутри областей ввода могут рендериться спецмаркеры,
			//поэтому renderToContainer возвращает массив
			try {
				blocks = blocks.concat(block.renderToContainer());
			} catch(e) {
				log('Block not rendered: '+e.message, block);
			}
			return blocks;
		};

		me.blocksRendering = true;

		var blocks = me.blockClasses.reduce(function(blocks, blockClass) {
			return blocks.concat(blockClass.factory(me, template));
		}, []);

		blocks.forEach(function(block) {
			if (!block.getPlace) return;
			template = template.replace(
				block.getPlace(),
				block.getContainerHtml()
			);
		});

		me.getUndoManager().transact(function() {
			if (blocks.length > 0) {
				me.setContent(me.correctTemplate(template), {addUndoLevel: false});
				blocks = blocks.reduce(renderBlock, []);
			}
			me.wrapContentInParagraph();
		});

		me.blocksRendering = false;

		me.refreshNotice();

		return blocks;
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);

		var emk = me.up('swPersonEmkWindow');

		if (!me.maximizedContainer && emk) {
			me.maximizedContainer = emk.mainPanel.el;
		}
	},

	toggleMarkerMode: function() {
		var me = this;
		if (me.markerMode == 'marker') {
			me.markerMode = 'content';
		} else {
			me.markerMode = 'marker';
		}
		me.getUndoManager().ignore(function() {
			me.renderBlocks(true);
		});
	},

	toggleSigns: function() {
		var me = this;
		me.signsVisible = !me.signsVisible;
		me.getUndoManager().ignore(function() {
			me.renderBlocks(true);
		});
	},

	setHeaderPanelVisible: function(visible) {
		var me = this;
		me.headerHidden = !visible;
		me.headerPanel.setVisible(visible);
	},

	showHeaderPanel: function() {
		var me = this;
		me.setHeaderPanelVisible(true);
	},

	hideHeaderPanel: function() {
		var me = this;
		me.setHeaderPanelVisible(false);
	},

	setFooterPanelVisible: function(visible) {
		var me = this;
		me.footerHidden = !visible;
		me.footerPanel.setVisible(visible);
	},

	showFooterPanel: function() {
		var me = this;
		me.setFooterPanelVisible(true);
	},

	hideFooterPanel: function() {
		var me = this;
		me.setFooterPanelVisible(false);
	},

	toggleMaximize: function() {
		var me = this;
		if (me.maximized) {
			me.restore();
		} else {
			me.maximize();
		}
	},

	maximize: function() {
		var me = this;
		var containerEl = me.maximizedContainer || main_center_panel.body;
		var maximizeBtn = me.headerPanel.down('[iconCls=icon-maximize]');
		var restoreBtn = me.headerPanel.down('[iconCls=icon-restore]');

		if (me.maximized) {
			return;
		}

		me.origLayoutProps = {
			ownerLayout: me.ownerLayout,
			position: me.el.getStyle('position'),
			zIndex: me.el.getZIndex() || null,
			left: null,
			top: null,
			width: null,
			height: null,
			outerCtList: []
		};

		var el = me.el;
		var wnd = me.up('window');
		while(el = el.up('.x6-autocontainer-outerCt', wnd)) {
			me.origLayoutProps.outerCtList.push(el);
			el.setStyle('transform', 'unset');
		}

		me.resize = function() {
			me.ownerLayout = null;

			var obj = {
				left: containerEl.getLeft(),
				top: containerEl.getTop(),
				width: containerEl.getWidth(),
				height: containerEl.getHeight()
			};

			var obj1 = {
				left: me.el.getLeft(),
				top: me.el.getTop(),
				width: me.getWidth(),
				height: me.getHeight()
			};

			if (!Ext6.Object.equals(obj, obj1)) {
				me.el.setStyle('position', 'fixed');
				me.el.setZIndex(10000);
				me.el.setLeft(obj.left);
				me.el.setTop(obj.top);
				me.setWidth(obj.width);
				me.setHeight(obj.height);
				me.updateLayout();
			}
		};

		me.maximized = true;

		if (me.toolbarMaximizedCfg) {
			me.initToolbar();
		}

		me.resize();
		me.on('resize', me.resize);

		maximizeBtn.hide();
		restoreBtn.show();
		me.headerPanel.setData(me.params);
		me.observeScrollForToolbar();
	},

	restore: function() {
		var me = this;
		var maximizeBtn = me.headerPanel.down('[iconCls=icon-maximize]');
		var restoreBtn = me.headerPanel.down('[iconCls=icon-restore]');

		if (!me.maximized || !me.origLayoutProps) {
			return;
		}

		me.un('resize', me.resize);

		me.origLayoutProps.outerCtList.forEach(function(el) {
			el.setStyle('transform', null);
		});

		me.ownerLayout = me.origLayoutProps.ownerLayout;
		me.el.setStyle('position', me.origLayoutProps.position);
		me.el.setZIndex(me.origLayoutProps.zIndex);
		me.el.setLeft(me.origLayoutProps.left);
		me.el.setTop(me.origLayoutProps.top);
		me.setWidth(me.origLayoutProps.width);
		me.setHeight(me.origLayoutProps.height);
		if (me.autoHeight) {
			me.el.setWidth(null);
			me.el.setHeight(null);
		}

		me.origLayoutProps = null;
		me.maximized = false;

		if (me.toolbarMaximizedCfg) {
			me.initToolbar();
		}
		restoreBtn.hide();
		maximizeBtn.show();
		me.headerPanel.setData(me.params);

		me.refreshSize();
	},

	getToolbarButtonsCfg: function() {
		var me = this;

		var toolbarButtonsCfg = {
			inputblock: {
				iconCls: 'icon-input-block',
				tooltip: 'Область ввода данных',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.openInputBlockSelector();
				}
			},
			textgenerator: {
				iconCls: 'icon-text-generator',
				tooltip: 'Генерация текста',
				disabled: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.openTextGenerator();
				}
			},
			parameter: {
				iconCls: 'icon-parameter',
				tooltip: 'Параметр',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.openParameterSelector();
				}
			},
			document: {
				iconCls: 'icon-document',
				tooltip: 'Документ',
				disabled: true,
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.openDocumentSelector();
				}
			},
			specmarker: {
				iconCls: 'icon-specmarker',
				tooltip: 'Спецмаркер',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				menu: [],	//todo: DropdownSelectPanel
				handler: function() {
					me.openSpecmarkerSelector();
				}
			},
			marker: {
				iconCls: 'icon-marker',
				tooltip: 'Маркер документа',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.openMarkerSelector();
				}
			},
			showsigns: {
				iconCls: 'icon-paragraph',
				tooltip: 'Отобразить все знаки',
				enableToggle: true,
				refreshDisabled: function(btn, prms) {
					return prms.viewHtml;
				},
				handler: function() {
					me.toggleSigns();
				}
			},
			templaterestore: {
				iconCls: 'icon-template-restore',
				tooltip: 'Вернуть шаблон в исходное состояние',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.restoreTemplate();
				}
			},
			templateclear: {
				iconCls: 'icon-template-clear',
				tooltip: 'Полностью удалить содержимое шаблона',
				refreshDisabled: function(btn, prms) {
					return (prms.isReadOnly || prms.viewHtml);
				},
				handler: function() {
					me.clearTemplate();
				}
			}
		};

		return Ext6.apply(me.callParent(), toolbarButtonsCfg);
	},

	initUndoManager: function() {
		var me = this;
		var config = {editor: me, onChangeLevel: []};

		if (me.isAutoSave) {
			config.onChangeLevel.push(me.autoSave.bind(me));
		}

		return Ext6.create('common.XmlTemplate.tools.EditorUndoManager', config);
	},

	initHeaderPanel: function() {
		var me = this;

		me.headerPanel = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'top',
			cls: 'sw-editor-header',
			hidden: me.headerHidden,
			setData: function(data) {
				var caption = me.headerPanel.down('[cls=sw-editor-header-caption]');
				var info = me.headerPanel.down('[cls=sw-editor-header-info]');

				var captionData = {
					XmlTemplate_Caption: data.XmlTemplate_Caption || '&nbsp;',
					showSetDefault: (!me.maximized && !Ext6.isEmpty(data.XmlTemplate_Caption)),
					isDefault: (data.XmlTemplate_IsDefault == 2)
				};
				var infoData = {
					EvnClass_Name: data.EvnClass_Name || '&nbsp;',
					XmlType_Name: data.XmlType_Name || '&nbsp;'
				};

				caption.setHtml(caption.tpl.apply(captionData));
				info.setHtml(info.tpl.apply(infoData));

				var setDefaultEl = caption.el.down('.sw-editor-header-set-default');
				if (setDefaultEl && data.XmlTemplate_IsDefault != 2) {
					setDefaultEl.on('click', function(){me.setDefault()});
				}
			},
			items: [{
				xtype: 'label',
				cls: 'sw-editor-header-caption',
				tpl: new Ext6.XTemplate([
					'<p class="sw-editor-header-caption-text">{XmlTemplate_Caption}</p>',
					'<tpl if="showSetDefault && !isDefault">',
					'<p style="margin-top: 2px" class="sw-editor-header-set-default">Использовать по умолчанию</p>',
					'</tpl>',
					'<tpl if="showSetDefault && isDefault">',
					'<p style="margin-top: 2px" class="sw-editor-header-set-default active">Используется по умолчанию</p>',
					'</tpl>'
				])
			}, '->', {
				xtype: 'label',
				cls: 'sw-editor-header-info',
				tpl: new Ext6.XTemplate([
					'<span>{EvnClass_Name}</span><br/>',
					'<span>{XmlType_Name} {Lpu_Nick} {Author_Fin}</span>'
				])
			}, '-', {
				xtype: 'button',
				iconCls: 'icon-maximize',
				handler: function() {
					me.maximize();
				}
			}, {
				xtype: 'button',
				iconCls: 'icon-restore',
				hidden: true,
				handler: function() {
					me.restore();
				}
			}]
		});

		return me.headerPanel;
	},

	refreshSize: function() {
		var me = this;
		me.setScale(me.scale);
	},

	setScale: function(value) {
		var me = this;
		value = value || me.scale;
		me.scaleMenuBtn.setScale(value);
		me.onScale(value);
		return me.scale;
	},

	onScale: function(value) {
		var me = this;

		me.suspendLayouts();

		me.scale = value;

		var scaleValue = (value/100);
		var scaleStyle = 'scale('+(value/100)+')';

		var bodyEl = me.body;
		var editorEl = me.getEditorEl();
		var htmlViewerEl = me.getHtmlViewerEl();
		var wrapOuterEl = me.getEditorWrapOuterEl();
		var wrapEl = me.getEditorWrapEl();
		var wrapInnerEl = Ext6.get(me.id+'-editor-wrap-inner');
		var owner = me.ownerCt;

		wrapInnerEl.setStyle('transform', scaleStyle);

		var top = wrapInnerEl.getTop();
		var left = wrapInnerEl.getLeft();
		var bottom = wrapInnerEl.getBottom();
		var right = wrapInnerEl.getRight();

		var width = (right - left) + wrapEl.getPadding('lr');
		var height = (bottom - top) * scaleValue + wrapEl.getPadding('tb');

		var bodyWidth = bodyEl.getWidth();
		var bodyHeight = bodyEl.getHeight();

		if (width != wrapEl.getWidth() || height != wrapEl.getHeight()) {
			wrapEl.setHeight(height);
			wrapEl.setWidth(width);

			if (me.autoHeight) {
				wrapOuterEl.setHeight(height < bodyHeight ? height : null);
				wrapOuterEl.setWidth(bodyWidth);
			}

			me.updateLayout();
		} else if (me.autoHeight && wrapOuterEl.getWidth() != bodyWidth) {
			wrapOuterEl.setWidth(bodyWidth);
			me.updateLayout();
		}

		if (me.autoHeight && me.maximized) {
			wrapOuterEl.setWidth(null);
			wrapOuterEl.setHeight(null);
		}

		me.resumeLayouts(true);
	},

	initFooterPanel: function() {
		var me = this;

		me.scaleMenuBtn = Ext6.create('sw.button.Scale', {
			menuCls: 'sw-editor-footer-menu',
			onScale: me.onScale.bind(me),
			value: me.scale
		});

		me.footerPanel = Ext6.create('Ext6.toolbar.Toolbar', {
			dock: 'bottom',
			cls: 'sw-editor-footer',
			hidden: me.footerHidden,
			items: [me.scaleMenuBtn]
		});

		return me.footerPanel;
	},

	initNotice: function() {
		var me = this;
		me.callParent(arguments);

		me.specMarkerNotice = Ext6.create('common.XmlTemplate.EditorSpecMarkerNotice', {
			hidden: true,
			buttonHandler: function() {
				me.refreshSpecMarkerBlocksContent();
			},
			listeners: {
				show: function() {
					me.refreshSize();
				},
				hide: function() {
					me.refreshSize();
				}
			}
		});

		me.notice.add(me.specMarkerNotice);
	},

	setHtmlText: function(html){
		let me = this;
		me.setContent(html);
	},

	initComponent: function() {
		var me = this;

		me.reset();

		me.originalMarkerMode = me.markerMode;

		me.cache = (function(){
			var _data = {};

			return {
				setData: function(name, data, flag) {
					if (!Ext6.isArray(data)) {
						data = [data];
					}
					if  (data.length == 0) return null;

					var cache = _data[name] || [];

					var getField = function(item, fieldName) {
						return item[flag?fieldName:name+'_'+fieldName];
					};

					var find = function(id) {
						return cache.find(function(item) {
							return getField(item, 'id') == id;
						});
					};

					return _data[name] = cache.concat(data.reduce(function(newData, item) {
						if (!Ext6.isObject(item)) return newData;
						return find(getField(item, 'id'))?newData:newData.concat(item);
					}, []));
				},

				getData: function(name, field, value) {
					if (!name) return Ext6.apply({}, _data);
					if (!field && !value) return _data[name] || [];
					return (_data[name] || []).find(function(item) {
						return item[field] == value;
					});
				},

				clear: function(name) {
					if (name) {
						delete _data[name];
					} else {
						_data = [];
					}
				}
			};
		}());

		if (!me.headerPanel) {
			me.initHeaderPanel();
		}
		if (!me.footerPanel) {
			me.initFooterPanel();
		}

		me.on('resize', me.refreshSize);

		Ext6.apply(me, {
			dockedItems: [
				me.headerPanel,
				me.footerPanel
			]
		});

		me.callParent(arguments);

		me.setEditing(me.editing);

		me.blockClasses = [
			base.EditorTableBlock,
			common.XmlTemplate.EditorInputBlock,
			common.XmlTemplate.EditorParameterBlock,
			common.XmlTemplate.EditorSpecMarkerBlock,
			common.XmlTemplate.EditorMarkerBlock,
			common.XmlTemplate.AnketaBlock
		];
	}
});