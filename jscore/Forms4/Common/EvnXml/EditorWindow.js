Ext6.define('common.EvnXml.EditorWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEvnXmlEditorWindow',
	requires: [
		'common.EvnXml.EditorPanel'
	],
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new arm-window-new-without-padding',
	title: 'XML-Документ',
	maximized: true,
	constrain: true,
	header: false,

	setParams: function(params) {
		var me = this;

		me.params = {
			EvnXml_id: params.EvnXml_id,
			XmlTemplate_id: params.XmlTemplate_id,
			XmlType_id: params.XmlType_id,
			Person_id: params.Person_id,
			Evn_id: params.Evn_id,
			EvnClass_id: params.EvnClass_id,
			LpuSection_id: params.LpuSection_id,
			MedPersonal_id: params.MedPersonal_id,
			MedStaffFact_id: params.MedStaffFact_id,
			MedService_id: params.MedService_id
		};

		me.editorPanel.reset();
		me.editorPanel.setParams(me.params);
		me.editorPanel.load({resetState: true});
	},

	show: function() {
		var me = this;
		var params = arguments[0];

		me.callParent(arguments);

		me.editorPanel.afterInitEditor(function() {
			me.setParams(params);
			me.editorPanel.allowedEvnClassList = params.allowedEvnClassList;
			me.editorPanel.allowedXmlTypeEvnClassLink = params.allowedXmlTypeEvnClassLink;
		});
	},

	initComponent: function() {
		var me = this;

		me.editorPanel = Ext6.create('common.EvnXml.EditorPanel', {
			style: 'border-width: 0;',
			footerHidden: false,
			isAutoSave: true,
			toolbarCfg: [
				'undo redo | fontsize | bold italic underline strikethrough | subscript superscript | list indent align | paste | image table |',
				'inputblock textgenerator | parameter document specmarker marker | showsigns |',
				'templateedit templaterestore templateclear templatesave | html print | -> preloader save emdbutton'
			]
		});

		me.editorContainerPanel = Ext6.create('Ext6.Panel', {
			region: 'center',
			layout: 'fit',
			scroll: true,
			border: false,
			items: [
				me.editorPanel
			]
		});

		Ext6.apply(me, {
			layout: 'border',
			border: false,
			style: 'padding: 0 !important;',
			items: [
				me.editorContainerPanel
			]
		});

		me.callParent(arguments);
	}
});