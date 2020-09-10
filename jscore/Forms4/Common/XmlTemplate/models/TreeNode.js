Ext6.define('common.XmlTemplate.models.TreeNode', {
	extend: 'Ext.data.TreeModel',
	alias: 'model.xmltemplatetreenode',
	schema: {
		namespace: 'common.XmlTemplate.models'
	},
	fields: [
		{name: 'XmlTemplateCat_id', defaultValue: null},
		{name: 'text', type: 'string'},
		{name: 'leaf', type: 'boolean', defaultValue: false},
		{name: 'sort', type: 'int', defaultValue: 9999}
	],
	proxy: {
		type: 'ajax',
		url: '/?c=XmlTemplate6E&m=loadXmlTemplateTree',
		reader: {
			type: 'json',
			typeProperty: 'nodeType'
		}
	},
	listeners: {
		append: function(me, children) {
			if (children.self == common.XmlTemplate.models.FolderNode ||
				children.self == common.XmlTemplate.models.SectionNode
			) {
				children.set('expandable', null);
			}
		}
	},
	refreshChildrenCount: Ext6.emptyFn
});

Ext6.define('common.XmlTemplate.models.LpuSectionNode', {
	extend: 'common.XmlTemplate.models.TreeNode',
	alias: 'model.xmltemplatelpusectionnode',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'LpuSection_id', type: 'int'},
		{name: 'LpuSection_Name', type: 'string'},
		{name: 'text', mapping: 'LpuSection_Name'}
	]
});

Ext6.define('common.XmlTemplate.models.MedPersonalNode', {
	extend: 'common.XmlTemplate.models.LpuSectionNode',
	alias: 'model.xmltemplatemedpersonalnode',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'MedPersonal_id', type: 'int'},
		{name: 'MedPersonal_FIO', type: 'string'},
		{name: 'text', mapping: 'MedPersonal_FIO'}
	]
});

Ext6.define('common.XmlTemplate.models.FolderNode', {
	extend: 'common.XmlTemplate.models.TreeNode',
	alias: 'model.xmltemplatefoldernode',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'XmlTemplateCat_id'},
		{name: 'XmlTemplateCat_pid'},
		{name: 'XmlTemplateCat_Name', type: 'string'},
		{name: 'childrenFoldersCount', type: 'int', defaultValue: 0},
		{name: 'childrenTemplatesCount', type: 'int', defaultValue: 0},
		{name: 'text', mapping: 'XmlTemplateCat_Name'},
		{name: 'leaf', type: 'boolean', defaultValue: false},
		{name: 'expandable', type: 'boolean', convert: function(value, node) {
			var parent = node.parentNode || {};
			var params = parent.lastParams || {};
			var childrenCount = node.get('childrenFoldersCount');

			if (!params.onlyFolders) {
				childrenCount += node.get('childrenTemplatesCount');
			}

			return childrenCount > 0;
		}}
	],
	refreshChildrenCount: function() {
		var me = this;
		var childrenFoldersCount = 0;
		var childrenTemplatesCount = 0;

		me.childNodes.forEach(function(node) {
			if (node.isLeaf()) {
				childrenTemplatesCount++;
			} else {
				childrenFoldersCount++;
			}
		});

		me.set('childrenFoldersCount', childrenFoldersCount);
		me.set('childrenTemplatesCount', childrenTemplatesCount);
		me.set('expandable', null);
	}
});

Ext6.define('common.XmlTemplate.models.SharedNode', {
	extend: 'common.XmlTemplate.models.TreeNode',
	alias: 'model.xmltemplatesharednode',
	fields: [
		{name: 'leaf', type: 'boolean', defaultValue: false},
		{name: 'childrenFoldersCount', type: 'int', defaultValue: 0},
		{name: 'childrenTemplatesCount', type: 'int', defaultValue: 0},
		{name: 'expandable', type: 'boolean', convert: function(value, node) {
			var parent = node.parentNode || {};
			var params = parent.lastParams || {};
			var childrenCount = node.get('childrenFoldersCount');

			if (!params.onlyFolders) {
				childrenCount += node.get('childrenTemplatesCount');
			}

			return childrenCount > 0;
		}}
	],
	refreshChildrenCount: function() {
		var me = this;
		var childrenFoldersCount = 0;
		var childrenTemplatesCount = 0;

		me.childNodes.forEach(function(node) {
			if (node.isLeaf()) {
				childrenTemplatesCount++;
			} else {
				childrenFoldersCount++;
			}
		});

		me.set('childrenFoldersCount', childrenFoldersCount);
		me.set('childrenTemplatesCount', childrenTemplatesCount);
		me.set('expandable', null);
	}
});

Ext6.define('common.XmlTemplate.models.TemplateNode', {
	extend: 'common.XmlTemplate.models.TreeNode',
	alias: 'model.xmltemplatenode',
	fields: [
		{name: 'id', type: 'string'},
		{name: 'XmlTemplate_id', type: 'int'},
		{name: 'XmlTemplate_Caption', type: 'string'},
		{name: 'XmlTemplate_Descr', type: 'string'},
		{name: 'Author_id', type: 'int'},
		{name: 'Author_Fin', type: 'string'},
		{name: 'XmlType_id', type: 'int'},
		{name: 'XmlType_Name', type: 'string'},
		{name: 'EvnClass_id', type: 'int'},
		{name: 'EvnClass_SysNick', type: 'string'},
		{name: 'EvnClass_Name', type: 'string'},
		{name: 'XmlTemplateScope_id', type: 'int'},
		{name: 'XmlTemplateScope_Name', type: 'string'},
		{name: 'XmlTemplate_IsDefault', type: 'int'},
		{name: 'XmlTemplate_IsFavorite', type: 'int'},
		{name: 'XmlTemplateShared_id', type: 'int'},
		{name: 'XmlTemplateShared_IsReaded', type: 'int'},
		{name: 'text', mapping: 'XmlTemplate_Caption'},
		{name: 'leaf', type: 'boolean', defaultValue: true}
	]
});