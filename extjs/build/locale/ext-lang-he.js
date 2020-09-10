/**
 * Hebrew Translations
 * By spartacus (from forums) 06-12-2007
 */

Ext.UpdateManager.defaults.indicatorText = '<div class="loading-indicator">...иетп</div>';

if(Ext.View){
  Ext.View.prototype.emptyText = "";
}

if(Ext.grid.GridPanel){
  Ext.grid.GridPanel.prototype.ddText = "щешеъ рбзшеъ {0}";
}

if(Ext.TabPanelItem){
  Ext.TabPanelItem.prototype.closeText = "свеш мщерйъ";
}

if(Ext.form.Field){
  Ext.form.Field.prototype.invalidText = "дтшк бщгд жд щвей";
}

if(Ext.LoadMask){
  Ext.LoadMask.prototype.msg = "...иетп";
}

Date.monthNames = [
  "йреаш",
  "фбшеаш",
  "ошх",
  "афшйм",
  "оай",
  "йерй",
  "йемй",
  "аевеси",
  "сфиобш",
  "аечиебш",
  "ребобш",
  "гцобш"
];

Date.getShortMonthName = function(month) {
  return Date.monthNames[month].substring(0, 3);
};

Date.monthNumbers = {
  Jan : 0,
  Feb : 1,
  Mar : 2,
  Apr : 3,
  May : 4,
  Jun : 5,
  Jul : 6,
  Aug : 7,
  Sep : 8,
  Oct : 9,
  Nov : 10,
  Dec : 11
};

Date.getMonthNumber = function(name) {
  return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
};

Date.dayNames = [
  "а",
  "б",
  "в",
  "г",
  "д",
  "е",
  "щ"
];

Date.getShortDayName = function(day) {
  return Date.dayNames[day].substring(0, 3);
};

if(Ext.MessageBox){
  Ext.MessageBox.buttonText = {
    ok     : "айщеш",
    cancel : "бйием",
    yes    : "лп",
    no     : "ма"
  };
}

if(Ext.util.Format){
  Ext.util.Format.date = function(v, format){
    if(!v) return "";
    if(!(v instanceof Date)) v = new Date(Date.parse(v));
    return v.dateFormat(format || "d/m/Y");
  };
}

if(Ext.DatePicker){
  Ext.apply(Ext.DatePicker.prototype, {
    todayText         : "дйен",
    minText           : ".ъашйк жд зм чегн мъашйк ддъзмъй щрчбт",
    maxText           : ".ъашйк жд зм мазш дъашйк дсефй щрчбт",
    disabledDaysText  : "",
    disabledDatesText : "",
    monthNames        : Date.monthNames,
    dayNames          : Date.dayNames,
    nextText          : '(Control+Right) дзегщ дба',
    prevText          : '(Control+Left) дзегщ дчегн',
    monthYearText     : '(мбзйшъ щрд Control+Up/Down) бзш зегщ',
    todayTip          : "очщ шеез) {0})",
    format            : "d/m/Y",
    okText            : "&#160;айщеш&#160;",
    cancelText        : "бйием",
    startDay          : 0
  });
}

if(Ext.PagingToolbar){
  Ext.apply(Ext.PagingToolbar.prototype, {
    beforePageText : "тоег",
    afterPageText  : "{0} оъек",
    firstText      : "тоег шащеп",
    prevText       : "тоег чегн",
    nextText       : "тоег дба",
    lastText       : "тоег азшеп",
    refreshText    : "штрп",
    displayMsg     : "оцйв {0} - {1} оъек {2}",
    emptyMsg       : 'айп ойгт мдцвд'
  });
}

if(Ext.form.TextField){
  Ext.apply(Ext.form.TextField.prototype, {
    minLengthText : "{0} даешк дойрйоамй мщгд жд деа",
    maxLengthText : "{0} даешк дойшбй мщгд жд деа",
    blankText     : "щгд жд длшзй",
    regexText     : "",
    emptyText     : null
  });
}

if(Ext.form.NumberField){
  Ext.apply(Ext.form.NumberField.prototype, {
    minText : "{0} дтшк дойрйоамй мщгд жд деа",
    maxText : "{0} дтшк дойшбй мщгд жд деа",
    nanText : "деа ма осфш {0}"
  });
}

if(Ext.form.DateField){
  Ext.apply(Ext.form.DateField.prototype, {
    disabledDaysText  : "ореишм",
    disabledDatesText : "ореишм",
    minText           : "{0} дъашйк бщгд жд зййб мдйеъ мазш",
    maxText           : "{0} дъашйк бщгд жд зййб мдйеъ мфрй",
    invalidText       : "{1} деа ма ъашйк ъчрй - зййб мдйеъ бфешои {0}",
    format            : "m/d/y",
    altFormats        : "m/d/Y|m-d-y|m-d-Y|m/d|m-d|md|mdy|mdY|d|Y-m-d"
  });
}

if(Ext.form.ComboBox){
  Ext.apply(Ext.form.ComboBox.prototype, {
    loadingText       : "...иетп",
    valueNotFoundText : undefined
  });
}

if(Ext.form.VTypes){
  Ext.apply(Ext.form.VTypes, {
    emailText    : '"user@domain.com" щгд жд цшйк мдйеъ лъебъ геаш амчишерй бфешои',
    urlText      : '"http:/'+'/www.domain.com" щгд жд цшйк мдйеъ лъебъ айришри бфешои',
    alphaText    : '_щгд жд йлем мдлйм шч аеъйеъ е',
    alphanumText : '_щгд жд йлем мдлйм шч аеъйеъ, осфшйн е'
  });
}

if(Ext.form.HtmlEditor){
  Ext.apply(Ext.form.HtmlEditor.prototype, {
    createLinkText : ':ара дчмг аъ лъебъ дайришри тбеш дчйщеш',
    buttonTips : {
      bold : {
        title: '(Ctrl+B) оегвщ',
        text: '.дгвщ аъ дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      italic : {
        title: '(Ctrl+I) рией',
        text: '.дид аъ дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      underline : {
        title: '(Ctrl+U) че ъзъй',
        text: '.десу чп ъзъй тбеш дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      increasefontsize : {
        title: 'двгм ичси',
        text: '.двгм вефп тбеш дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      decreasefontsize : {
        title: 'дчип ичси',
        text: '.дчип вефп тбеш дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      backcolor : {
        title: 'цбт шчт мичси',
        text: '.щрд аъ цбт дшчт тбеш дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      forecolor : {
        title: 'цбт вефп',
        text: '.щрд аъ цбт двефп тбеш дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      justifyleft : {
        title: 'йщеш мщоам',
        text: '.йщш щоамд аъ дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      justifycenter : {
        title: 'йщеш мошлж',
        text: '.йщш мошлж аъ дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      justifyright : {
        title: 'йщеш мйойп',
        text: '.йщш йойрд аъ дичси дрбзш',
        cls: 'x-html-editor-tip'
      },
      insertunorderedlist : {
        title: 'шщйоъ рчегеъ',
        text: '.дъзм шщйоъ рчегеъ',
        cls: 'x-html-editor-tip'
      },
      insertorderedlist : {
        title: 'шщйод ооесфшъ',
        text: '.дъзм шщйод ооесфшъ',
        cls: 'x-html-editor-tip'
      },
      createlink : {
        title: 'чйщеш',
        text: '.дфек аъ дичси дрбзш мчйщеш',
        cls: 'x-html-editor-tip'
      },
      sourceedit : {
        title: 'тшйлъ чег очеш',
        text: '.дцв чег очеш',
        cls: 'x-html-editor-tip'
      }
    }
  });
}

if(Ext.grid.GridView){
  Ext.apply(Ext.grid.GridView.prototype, {
    sortAscText  : "оййп бсгш темд",
    sortDescText : "оййп бсгш йешг",
    lockText     : "ртм тоегд",
    unlockText   : "щзшш тоегд",
    columnsText  : "тоегеъ"
  });
}

if(Ext.grid.GroupingView){
  Ext.apply(Ext.grid.GroupingView.prototype, {
    emptyGroupText : '(шйч)',
    groupByText    : 'дцв бчбецеъ мфй щгд жд',
    showGroupsText : 'дцв бчбецеъ'
  });
}

if(Ext.grid.PropertyColumnModel){
  Ext.apply(Ext.grid.PropertyColumnModel.prototype, {
    nameText   : "щн",
    valueText  : "тшк",
    dateFormat : "m/j/Y"
  });
}

if(Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion){
  Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {
    splitTip            : ".ощек мщйрей вегм",
    collapsibleSplitTip : ".ощек мщйрей вегм. мзйцд лфемд мдсъшд"
  });
}
