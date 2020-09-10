/**
 * swPersonCardPrintDialogWindow - диологовое окно для заявления о выбре МО, согласия/отказа от мед. вмешательств
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Polka
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swPersonCardPrintDialogWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonCardPrintDialogWindow',
	width: 540,
	//height: 300,
	autoHeight: true,
	modal: true,
	buttonAlign: 'center',
	title: lang['vyibor_vyirianta_pechati'],

	getPersonDeputyData: function() {
		var form = this;
		var person_id = form.params.Person_id;
		Ext.Ajax.request({
			params: {Person_id: person_id},
			callback: function(options, success, response) {
				if (success && response.responseText != '')
				{
					var data =  Ext.util.JSON.decode(response.responseText);
					form.PersonDeputyData = data[0];
				}
				else
				{
					form.showMessage(lang['oshibka'], lang['oshibka_poluchenii_dannyih_zakonnogo_predstavitelya_patsienta']);
				}
			},
			url: '/?c=Mse&m=getDeputyKind'
		});
	},

	printMedicalInterventBlank: function(params) {
		var pattern_consent = '';
		var pattern_refuse = '';

		if (params.is_himself) {
			pattern_consent = 'PersonCardInfoConsent.rptdesign';
			pattern_refuse = 'PersonCardMedicalIntervent.rptdesign';
		} else {
			pattern_consent = 'PersonCardInfoConsent_Deputy.rptdesign';
			pattern_refuse = 'PersonCardMedicalIntervent_Deputy.rptdesign';
		}

		if (!params.is_refuse || (params.total_count > 0 && params.refuse_count == 0)) {
			printBirt({
				'Report_FileName': pattern_consent,
				'Report_Params': '&paramPersonCard=' + params.person_card_id + '&paramMedPersonal=' + params.med_personal_id,
				'Report_Format': 'pdf'
			});
		} else
		if (params.is_refuse && params.total_count > 0 && params.total_count == params.refuse_count) {
			printBirt({
				'Report_FileName': pattern_refuse,
				'Report_Params': '&paramPersonCard=' + params.person_card_id + '&paramMedPersonal=' + params.med_personal_id,
				'Report_Format': 'pdf'
			});
		} else
		if (params.is_refuse && params.refuse_count > 0 && params.total_count > params.refuse_count) {
			printBirt({
				'Report_FileName': pattern_consent,
				'Report_Params': '&paramPersonCard=' + params.person_card_id + '&paramMedPersonal=' + params.med_personal_id,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': pattern_refuse,
				'Report_Params': '&paramPersonCard=' + params.person_card_id + '&paramMedPersonal=' + params.med_personal_id,
				'Report_Format': 'pdf'
			});
		}
	},

	print: function(type) {
		if (!type.inlist(['personally', 'deputy'])) {return false;}

		var base_form = this.FormPanel.getForm();

		var is_attach = base_form.findField('PrintPersonCardAttach').getValue();
		var is_mi = base_form.findField('PrintMedicalIntervent').getValue();

		if (!is_attach && !is_mi) {
			this.showMessage(lang['soobschenie'], lang['ne_vyibranyi_dokumentyi_dlya_pechati']);
			return false;
		}

		var params = this.params;
		params.mi_params.is_himself = (type == 'personally');

		if (type == 'deputy' && (!this.PersonDeputyData || Ext.isEmpty(this.PersonDeputyData.Person_id))) {
			this.showMessage(lang['soobschenie'], lang['u_patsienta_net_zakonnogo_predstavitelya']);
			return false;
		}

		if(getRegionNick()=='astra'){ //https://redmine.swan.perm.ru/issues/26686
			// Для Астрахани
			if (is_attach) {
				var paramDeputy = (type=='personally')?2:1;
				printBirt({
					'Report_FileName': 'ApplicationForAttachment.rptdesign',
					'Report_Params': '&paramPerson_id=' + params.Person_id + '&paramDeputy=' + paramDeputy + '&paramLpu=' + params.Lpu_id,
					'Report_Format': 'pdf'
				});
			}
			if (is_mi) {
				this.printMedicalInterventBlank(params.mi_params);
			}
		}
        else if (getRegionNick()=='pskov'){ //https://redmine.swan.perm.ru/issues/48169
            if (is_attach) {
                var template = '';
                var paramDeputy = (type=='personally')?2:1;
                if(paramDeputy == 2)
                    template = 'pan_DeclarationPrik_MO.rptdesign';
                else
                    template = 'pan_DeclarationPrik_MO_Deputy.rptdesign';
                if(!Ext.isEmpty(params.mi_params) && !Ext.isEmpty(params.mi_params.person_card_id))
                    params.PersonCard_id = params.mi_params.person_card_id;
				printBirt({
					'Report_FileName': template,
					'Report_Params': '&paramPersonCard=' + params.PersonCard_id,
					'Report_Format': 'pdf'
				});
            }
            if (is_mi) {
                this.printMedicalInterventBlank(params.mi_params);
            }
        }
        else if (getRegionNick()=='msk'){
        	if (is_attach) {
	        	printBirt({
					'Report_FileName': 'PersonCardAttachment.rptdesign',
					'Report_Params': '&paramPerson=' + params.Person_id + '&paramPersonCard=' + params.mi_params.person_card_id,
					'Report_Format': 'pdf'
				});
			}
            if (is_mi) {
                this.printMedicalInterventBlank(params.mi_params);
            }
        }
        else if (getRegionNick()=='krym'){
        	if (is_attach) {
				if(!Ext.isEmpty(params.mi_params) && !Ext.isEmpty(params.mi_params.person_card_id))
					params.PersonCard_id = params.mi_params.person_card_id;
	        	printBirt({
					'Report_FileName': 'PersonCardAttachment.rptdesign',
					'Report_Params': '&paramPerson=' + params.Person_id + '&paramPersonCard=' + params.PersonCard_id,
					'Report_Format': 'pdf'
				});
			}
            if (is_mi) {
                this.printMedicalInterventBlank(params.mi_params);
            }
        }
        else{
			// Для остальных
			if (is_attach) {
				var link = '/?c=PersonCard&m=printPersonCardAttach';
				if (getRegionNick() == 'kareliya'){
					link = '/?c=PersonCard&m=printPersonCardAttachKareliya'; 
				}
				else if (getRegionNick() == 'ekb')
					link = '/?c=PersonCard&m=printPersonCardAttachEkb';
				Ext.Ajax.request({
					url: link,
					params: {
						Person_id: params.Person_id,
						Server_id: params.Server_id,
						PersonCardAttach_IsHimself: type=='personally'?2:1,
						LpuRegion_id: this.params.LpuRegion_id
					},
					callback: function(o, s, r) {
						if( s ) {
							if( /<html>/g.test(r.responseText) ) {
								openNewWindow(r.responseText);
							}
							if (is_mi) {
								this.printMedicalInterventBlank(params.mi_params);
							}
						}
					}.createDelegate(this)
				});
			} else if (is_mi) {
				this.printMedicalInterventBlank(params.mi_params);
			}
		}
		this.hide();
	},

	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},

	show: function() {
		sw.Promed.swPersonCardPrintDialogWindow.superclass.show.apply(this, arguments);

		this.params = null;
		var base_form = this.FormPanel.getForm();

		if (!arguments[0].params) {
			this.showMessage(lang['oshibka'], lang['ne_byili_peredanyi_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		this.params = arguments[0].params;
		var base_form = this.FormPanel.getForm();

		var is_attach = base_form.findField('PrintPersonCardAttach').getValue();
		base_form.reset();

		if (this.params.printAgreementOnly==1)
		{
			base_form.findField('PrintPersonCardAttach').setValue(false);
			base_form.findField('PrintPersonCardAttach').setDisabled(true);
		}
		else
			base_form.findField('PrintPersonCardAttach').setDisabled(false);
		this.getPersonDeputyData();
	},

	initComponent: function() {
		var html = "Выберите вариант печати и необходимый документ. Нажмите: <b>Лично</b> - от имени" +
			" гражданина, лично. <b>Представитель</b> - от имени законного представителя. Отмена - отмена действия";

		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			border: false,
			style: 'margin-bottom: 5px;',
			id: 'PCPDW_TextPanel',
			html: html
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'background: #c6d4e6; padding: 5px 5px 0;',
			defaults: {bodyStyle: 'background: #c6d4e6;'},
			border: false,
			id: 'PCPDW_FormPanel',
			labelAlign: 'right',
			labelWidth: 50,

			items: [
				this.TextPanel,
				{
					xtype: 'checkbox',
					checked: true,
					labelSeparator: '',
					boxLabel: lang['zayavlenie_o_vyibore_mo'],
					name: 'PrintPersonCardAttach'
				}, {
					xtype: 'checkbox',
					checked: true,
					labelSeparator: '',
					boxLabel: lang['informirovannoe_soglasie_otkaz'],
					name: 'PrintMedicalIntervent'
				}
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					handler: function () {
						this.print('personally');
					}.createDelegate(this),
					id: 'PCPDW_PrintPerson',
					text: lang['lichno']
				}, {
					handler: function () {
						this.print('deputy');
					}.createDelegate(this),
					id: 'PCPDW_PrintDeputy',
					text: lang['predstavitel']
				}, {
					handler: function () {
						this.hide();
					}.createDelegate(this),
					id: 'PCPDW_CancelButton',
					text: lang['otmena']
				}]
		});

		sw.Promed.swPersonCardPrintDialogWindow.superclass.initComponent.apply(this, arguments);
	}
});