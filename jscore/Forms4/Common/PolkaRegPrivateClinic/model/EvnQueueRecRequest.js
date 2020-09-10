Ext6.define('common.PolkaRegPrivateClinic.model.EvnQueueRecRequest', {
    extend: 'Ext.data.Model',
    alias: 'model.recordrequest',
    fields: [
        {name: 'EvnQueue_id', type: 'int'},
        {name: 'EvnDirection_id', type: 'int'},
		{name: 'EvnStatus_id', type: 'int'},
		{name: 'EvnStatus_Name', type: 'string'},
		{name: 'EvnStatusCause_id', type: 'string'},
		{name: 'EvnStatusCause_Name', type: 'string'},
		{name: 'RequestStatus_Name', type: 'string'},
        {name: 'Person_id', type: 'int'},
		{name: 'QueueFailCause_id', type: 'int'},
        {name: 'EvnQueue_insDT', type: 'string'},
        {name: 'EvnQueue_insDT_date', type: 'string',
            convert: function(v, rec) {
                var datetime = rec.get('EvnQueue_insDT').split(' ');
                return datetime[0] ? datetime[0] : null
            }
        },
        {name: 'EvnQueue_insDT_time', type: 'string',
            convert: function(v, rec) {
                var datetime = rec.get('EvnQueue_insDT').split(' ');
                return datetime[1] ? datetime[1] : null
            }
        },
        {name: 'Person_FullName', type: 'string',
            convert: function(v, rec) {
                return Ext6.util.Format.capitalize(rec.get('Person_Surname').toLowerCase())
                    + ' ' + Ext6.util.Format.capitalize(rec.get('Person_Firname').toLowerCase())
                    + ' ' + Ext6.util.Format.capitalize(rec.get('Person_Secname').toLowerCase());
            }
        },
		{name: 'Person_FullName_Short', type: 'string',
			convert: function(v, rec) {
				return Ext6.util.Format.capitalize(rec.get('Person_Surname').toLowerCase())
					+ ' ' + rec.get('Person_Firname').charAt(0)
					+ '. ' + rec.get('Person_Secname').charAt(0) + '. ';
			}
		},
        {name: 'Person_Age', type: 'string'},
        {name: 'Person_BirthDay', type: 'string'},
        {name: 'Person_Phone', type: 'string'},
        {name: 'EvnDirection_Descr', type: 'string'},
        {name: 'MedStaffFact_id', type: 'int'},
		{name: 'MedPersonal_Name', type: 'string',
			convert: function(v, rec) {
				return Ext6.util.Format.capitalize(rec.get('MedPersonal_Surname').toLowerCase())
					+ ' ' + Ext6.util.Format.capitalize(rec.get('MedPersonal_Firname').toLowerCase())
					+ ' ' + Ext6.util.Format.capitalize(rec.get('MedPersonal_Secname').toLowerCase());
			}
		},
		{name: 'MedPersonal_Name_Short', type: 'string',
			convert: function(v, rec) {
				return Ext6.util.Format.capitalize(rec.get('MedPersonal_Surname').toLowerCase())
					+ ' ' + rec.get('MedPersonal_Firname').charAt(0)
					+ '. ' + rec.get('MedPersonal_Secname').charAt(0) + '. ';
			}
		},
        {name: 'Person_Email', type: 'string'},
        {name: 'LpuSectionProfile_Code', type: 'int'},
		{name: 'LpuSectionProfile_id', type: 'int'},
		{name: 'ProfileSpec_Name', type: 'string',
			convert: function(v, rec) {
				return Ext6.util.Format.capitalize(v).toLowerCase();
			}},
        {name: 'TimetableGraf_id', type: 'int'},
        {name: 'TimetableGraf_begTime', type: 'string'},
        {name: 'TimetableGraf_begTime_date', type: 'string',
            convert: function(v, rec) {
                var datetime = rec.get('TimetableGraf_begTime').split(' ');
                return datetime[0] ? datetime[0] : null
            }
        },
        {name: 'TimetableGraf_begTime_time', type: 'string',
            convert: function(v, rec) {
                var datetime = rec.get('TimetableGraf_begTime').split(' ');
                return datetime[1] ? datetime[1] : null
            }
        },
    ]
});