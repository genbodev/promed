Ext6.define('videoChat.ContactInfoPanel', {
	extend: 'Ext6.panel.Panel',
	cls: 'video-chat-contact-info',

	tpl: [
		'<div id="{id}-contact-avatar" class="video-chat-contact-avatar" style="width: 75px; height: 75px; flex-shrink: 0;">',
			'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="empty-img"></div></tpl>',
			'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="img" style="background-image: url({Avatar})"></div></tpl>',
		'</div>',
		'<div>',
			'<div style="display: flex; flex-direction: row;">',
				'<div id="{id}-contact-user" class="video-chat-contact-user">',
					'{SurName} {FirName} {SecName}',
				'</div>',
				'<div id="{id}-contact-status" class="video-chat-contact-status" style="margin-left: 30px;">',
					'<tpl if="values.Status==\'online\'"><span class="contact-icon contact-online"></span><span>Пользователь в сети</span></tpl>',
					'<tpl if="values.Status==\'offline\'"><span class="contact-icon contact-offline"></span><span>Пользователь не сети</span></tpl>',
					//'<tpl if="values.Status==\'add\'"><span class="contact-icon contact-add"></span><span>Пользователь не включен в список контактов</span></tpl>',
					'<tpl if="values.Status==\'none\'"><span>&nbsp;</span></tpl>',
				'</div>',
			'</div>',
			'<div id="{id}-contact-lpu" class="video-chat-contact-lpu">',
				'{[this.renderList(values.LpuList, \'Lpu_Nick\')]}',
			'</div>',
		'</div>',
		{
			renderList: function(list, field) {
				if (!Ext6.isArray(list)) {
					return '';
				}
				if (!field) {
					return list.join(', ');
				}
				return list.map(function(item) {
					return item[field];
				}).join(', ');
			}
		}
	],

	defaultData: {
		Avatar: null,
		SurName: '',
		FirName: '',
		SecName: '',
		Status: 'none',
		LpuList: []
	},

	setContact: function(contact) {
		var me = this;

		if (!contact) {
			me.setData(me.defaultData);
			return;
		}

		var data = {};
		Object.keys(me.defaultData).forEach(function(field) {
			if (contact.get(field) != undefined) {
				data[field] = contact.get(field);
			} else {
				data[field] = me.defaultData[field];
			}
		});

		me.setData(data);
	},

	initComponent: function() {
		var me = this;
		me.data = Ext6.apply({}, me.defaultData);
		me.callParent(arguments);
	}
});