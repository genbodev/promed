/**
* PromedInit - инициализация неймспейсов и прочего
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       SWAN developers
* @version      19.06.2009
*/

Ext6.BLANK_IMAGE_URL = 'extjs/resources/images/default/s.gif';
Ext6.ns('sw');
Ext6.ns('sw.Promed');
Ext6.ns('sw.Promed.Dlo');
Ext6.ns('sw.Promed.Polka');
Ext6.ns('sw.Promed.Admin');

Ext6.ns('sw4');
Ext6.ns('sw4.Promed');

sw.Promed.Glossary = {};

// хитрости
Ext = Ext6;
Ext.apply(Function.prototype, {
	createCallback: function() {
		var args = arguments;
		var method = this;
		return function() {
			return method.apply(window, args);
		};
	}, createDelegate: function(obj, args, appendArgs) {
		var method = this;
		return function() {
			var callArgs = args || arguments;
			if (appendArgs === true) {
				callArgs = Array.prototype.slice.call(arguments, 0);
				callArgs = callArgs.concat(args);
			} else if (typeof appendArgs == "number") {
				callArgs = Array.prototype.slice.call(arguments, 0);
				var applyArgs = [appendArgs, 0].concat(args);
				Array.prototype.splice.apply(callArgs, applyArgs);
			}
			return method.apply(obj || window, callArgs);
		};
	}, defer: function(millis, obj, args, appendArgs) {
		var fn = this.createDelegate(obj, args, appendArgs);
		if (millis) {
			return setTimeout(fn, millis);
		}
		fn();
		return 0;
	}, createSequence: function(fcn, scope) {
		if (typeof fcn != "function") {
			return this;
		}
		var method = this;
		return function() {
			var retval = method.apply(this || window, arguments);
			fcn.apply(scope || this || window, arguments);
			return retval;
		};
	}, createInterceptor: function(fcn, scope) {
		if (typeof fcn != "function") {
			return this;
		}
		var method = this;
		return function() {
			fcn.target = this;
			fcn.method = method;
			if (fcn.apply(scope || this || window, arguments) === false) {
				return;
			}
			return method.apply(this || window, arguments);
		};
	}
});


Ext.apply(Ext, {
	escapeRe: function(s) {
		return s.replace(/([.*+?^${}()|[\]\/\\])/g, "\\$1");
	}
});

Ext.applyIf(String, {
	escape: function(string) {
		return string.replace(/('|\\)/g, "\\$1");
	}, leftPad: function(val, size, ch) {
		var result = new String(val);
		if (!ch) {
			ch = " ";
		}
		while (result.length < size) {
			result = ch + result;
		}
		return result.toString();
	}, format: function(format) {
		var args = Array.prototype.slice.call(arguments, 1);
		return format.replace(/\{(\d+)\}/g, function(m, i) {
			return args[i];
		});
	}
});

(function() {


	Date.useStrict = false;

// create private copy of Ext's String.format() method
// - to remove unnecessary dependency
// - to resolve namespace conflict with M$-Ajax's implementation
	function xf(format) {
		var args = Array.prototype.slice.call(arguments, 1);
		return format.replace(/\{(\d+)\}/g, function(m, i) {
			return args[i];
		});
	}


// private
	Date.formatCodeToRegex = function(character, currentGroup) {
		// Note: currentGroup - position in regex result array (see notes for Date.parseCodes below)
		var p = Date.parseCodes[character];

		if (p) {
			p = typeof p == 'function'? p() : p;
			Date.parseCodes[character] = p; // reassign function result to prevent repeated execution
		}

		return p? Ext.applyIf({
			c: p.c? xf(p.c, currentGroup || "{0}") : p.c
		}, p) : {
			g:0,
			c:null,
			s:Ext.escapeRe(character) // treat unrecognised characters as literals
		}
	}

// private shorthand for Date.formatCodeToRegex since we'll be using it fairly often
	var $f = Date.formatCodeToRegex;

	Ext.apply(Date, {

		parseFunctions: {
			"M$": function(input, strict) {
				// note: the timezone offset is ignored since the M$ Ajax server sends
				// a UTC milliseconds-since-Unix-epoch value (negative values are allowed)
				var re = new RegExp('\\/Date\\(([-+])?(\\d+)(?:[+-]\\d{4})?\\)\\/');
				var r = (input || '').match(re);
				return r? new Date(((r[1] || '') + r[2]) * 1) : null;
			}
		},
		parseRegexes: [],


		formatFunctions: {
			"M$": function() {
				// UTC milliseconds since Unix epoch (M$-AJAX serialized date format (MRSF))
				return '\\/Date(' + this.getTime() + ')\\/';
			}
		},

		y2kYear : 50,


		MILLI : "ms",


		SECOND : "s",


		MINUTE : "mi",


		HOUR : "h",


		DAY : "d",


		MONTH : "mo",


		YEAR : "y",


		defaults: {},


		dayNames : [
			"Sunday",
			"Monday",
			"Tuesday",
			"Wednesday",
			"Thursday",
			"Friday",
			"Saturday"
		],


		monthNames : [
			"January",
			"February",
			"March",
			"April",
			"May",
			"June",
			"July",
			"August",
			"September",
			"October",
			"November",
			"December"
		],


		monthNumbers : {
			Jan:0,
			Feb:1,
			Mar:2,
			Apr:3,
			May:4,
			Jun:5,
			Jul:6,
			Aug:7,
			Sep:8,
			Oct:9,
			Nov:10,
			Dec:11
		},


		getShortMonthName : function(month) {
			return Date.monthNames[month].substring(0, 3);
		},


		getShortDayName : function(day) {
			return Date.dayNames[day].substring(0, 3);
		},


		getMonthNumber : function(name) {
			// handle camel casing for english month names (since the keys for the Date.monthNumbers hash are case sensitive)
			return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
		},


		formatCodes : {
			d: "String.leftPad(this.getDate(), 2, '0')",
			D: "Date.getShortDayName(this.getDay())", // get localised short day name
			j: "this.getDate()",
			l: "Date.dayNames[this.getDay()]",
			N: "(this.getDay() ? this.getDay() : 7)",
			S: "this.getSuffix()",
			w: "this.getDay()",
			z: "this.getDayOfYear()",
			W: "String.leftPad(this.getWeekOfYear(), 2, '0')",
			F: "Date.monthNames[this.getMonth()]",
			m: "String.leftPad(this.getMonth() + 1, 2, '0')",
			M: "Date.getShortMonthName(this.getMonth())", // get localised short month name
			n: "(this.getMonth() + 1)",
			t: "this.getDaysInMonth()",
			L: "(this.isLeapYear() ? 1 : 0)",
			o: "(this.getFullYear() + (this.getWeekOfYear() == 1 && this.getMonth() > 0 ? +1 : (this.getWeekOfYear() >= 52 && this.getMonth() < 11 ? -1 : 0)))",
			Y: "this.getFullYear()",
			y: "('' + this.getFullYear()).substring(2, 4)",
			a: "(this.getHours() < 12 ? 'am' : 'pm')",
			A: "(this.getHours() < 12 ? 'AM' : 'PM')",
			g: "((this.getHours() % 12) ? this.getHours() % 12 : 12)",
			G: "this.getHours()",
			h: "String.leftPad((this.getHours() % 12) ? this.getHours() % 12 : 12, 2, '0')",
			H: "String.leftPad(this.getHours(), 2, '0')",
			i: "String.leftPad(this.getMinutes(), 2, '0')",
			s: "String.leftPad(this.getSeconds(), 2, '0')",
			u: "String.leftPad(this.getMilliseconds(), 3, '0')",
			O: "this.getGMTOffset()",
			P: "this.getGMTOffset(true)",
			T: "this.getTimezone()",
			Z: "(this.getTimezoneOffset() * -60)",

			c: function() { // ISO-8601 -- GMT format
				for (var c = "Y-m-dTH:i:sP", code = [], i = 0, l = c.length; i < l; ++i) {
					var e = c.charAt(i);
					code.push(e == "T" ? "'T'" : Date.getFormatCode(e)); // treat T as a character literal
				}
				return code.join(" + ");
			},


			U: "Math.round(this.getTime() / 1000)"
		},


		isValid : function(y, m, d, h, i, s, ms) {
			// setup defaults
			h = h || 0;
			i = i || 0;
			s = s || 0;
			ms = ms || 0;

			var dt = new Date(y, m - 1, d, h, i, s, ms);

			return y == dt.getFullYear() &&
				m == dt.getMonth() + 1 &&
				d == dt.getDate() &&
				h == dt.getHours() &&
				i == dt.getMinutes() &&
				s == dt.getSeconds() &&
				ms == dt.getMilliseconds();
		},


		parseDate : function(input, format, strict) {
			var p = Date.parseFunctions;
			if (p[format] == null) {
				Date.createParser(format);
			}
			return p[format](input, strict === undefined ? Date.useStrict : strict);
		},

		// private
		getFormatCode : function(character) {
			var f = Date.formatCodes[character];

			if (f) {
				f = typeof f == 'function'? f() : f;
				Date.formatCodes[character] = f; // reassign function result to prevent repeated execution
			}

			// note: unknown characters are treated as literals
			return f || ("'" + String.escape(character) + "'");
		},

		// private
		createFormat : function(format) {
			var code = [],
				special = false,
				ch = '';

			for (var i = 0; i < format.length; ++i) {
				ch = format.charAt(i);
				if (!special && ch == "\\") {
					special = true;
				} else if (special) {
					special = false;
					code.push("'" + String.escape(ch) + "'");
				} else {
					code.push(Date.getFormatCode(ch))
				}
			}
			Date.formatFunctions[format] = new Function("return " + code.join('+'));
		},

		// private
		createParser : function() {
			var code = [
				"var dt, y, m, d, h, i, s, ms, o, z, zz, u, v,",
				"def = Date.defaults,",
				"results = String(input).match(Date.parseRegexes[{0}]);", // either null, or an array of matched strings

				"if(results){",
				"{1}",

				"if(u != null){", // i.e. unix time is defined
				"v = new Date(u * 1000);", // give top priority to UNIX time
				"}else{",
				// create Date object representing midnight of the current day;
				// this will provide us with our date defaults
				// (note: clearTime() handles Daylight Saving Time automatically)
				"dt = (new Date()).clearTime();",

				// date calculations (note: these calculations create a dependency on Ext.num())
				"y = y >= 0? y : Ext.num(def.y, dt.getFullYear());",
				"m = m >= 0? m : Ext.num(def.m - 1, dt.getMonth());",
				"d = d >= 0? d : Ext.num(def.d, dt.getDate());",

				// time calculations (note: these calculations create a dependency on Ext.num())
				"h  = h || Ext.num(def.h, dt.getHours());",
				"i  = i || Ext.num(def.i, dt.getMinutes());",
				"s  = s || Ext.num(def.s, dt.getSeconds());",
				"ms = ms || Ext.num(def.ms, dt.getMilliseconds());",

				"if(z >= 0 && y >= 0){",
				// both the year and zero-based day of year are defined and >= 0.
				// these 2 values alone provide sufficient info to create a full date object

				// create Date object representing January 1st for the given year
				"v = new Date(y, 0, 1, h, i, s, ms);",

				// then add day of year, checking for Date "rollover" if necessary
				"v = !strict? v : (strict === true && (z <= 364 || (v.isLeapYear() && z <= 365))? v.add(Date.DAY, z) : null);",
				"}else if(strict === true && !Date.isValid(y, m + 1, d, h, i, s, ms)){", // check for Date "rollover"
				"v = null;", // invalid date, so return null
				"}else{",
				// plain old Date object
				"v = new Date(y, m, d, h, i, s, ms);",
				"}",
				"}",
				"}",

				"if(v){",
				// favour UTC offset over GMT offset
				"if(zz != null){",
				// reset to UTC, then add offset
				"v = v.add(Date.SECOND, -v.getTimezoneOffset() * 60 - zz);",
				"}else if(o){",
				// reset to GMT, then add offset
				"v = v.add(Date.MINUTE, -v.getTimezoneOffset() + (sn == '+'? -1 : 1) * (hr * 60 + mn));",
				"}",
				"}",

				"return v;"
			].join('\n');

			return function(format) {
				var regexNum = Date.parseRegexes.length,
					currentGroup = 1,
					calc = [],
					regex = [],
					special = false,
					ch = "";

				for (var i = 0; i < format.length; ++i) {
					ch = format.charAt(i);
					if (!special && ch == "\\") {
						special = true;
					} else if (special) {
						special = false;
						regex.push(String.escape(ch));
					} else {
						var obj = $f(ch, currentGroup);
						currentGroup += obj.g;
						regex.push(obj.s);
						if (obj.g && obj.c) {
							calc.push(obj.c);
						}
					}
				}

				Date.parseRegexes[regexNum] = new RegExp("^" + regex.join('') + "$", "i");
				Date.parseFunctions[format] = new Function("input", "strict", xf(code, regexNum, calc.join('')));
			}
		}(),

		// private
		parseCodes : {

			d: {
				g:1,
				c:"d = parseInt(results[{0}], 10);\n",
				s:"(\\d{2})" // day of month with leading zeroes (01 - 31)
			},
			j: {
				g:1,
				c:"d = parseInt(results[{0}], 10);\n",
				s:"(\\d{1,2})" // day of month without leading zeroes (1 - 31)
			},
			D: function() {
				for (var a = [], i = 0; i < 7; a.push(Date.getShortDayName(i)), ++i); // get localised short day names
				return {
					g:0,
					c:null,
					s:"(?:" + a.join("|") +")"
				}
			},
			l: function() {
				return {
					g:0,
					c:null,
					s:"(?:" + Date.dayNames.join("|") + ")"
				}
			},
			N: {
				g:0,
				c:null,
				s:"[1-7]" // ISO-8601 day number (1 (monday) - 7 (sunday))
			},
			S: {
				g:0,
				c:null,
				s:"(?:st|nd|rd|th)"
			},
			w: {
				g:0,
				c:null,
				s:"[0-6]" // javascript day number (0 (sunday) - 6 (saturday))
			},
			z: {
				g:1,
				c:"z = parseInt(results[{0}], 10);\n",
				s:"(\\d{1,3})" // day of the year (0 - 364 (365 in leap years))
			},
			W: {
				g:0,
				c:null,
				s:"(?:\\d{2})" // ISO-8601 week number (with leading zero)
			},
			F: function() {
				return {
					g:1,
					c:"m = parseInt(Date.getMonthNumber(results[{0}]), 10);\n", // get localised month number
					s:"(" + Date.monthNames.join("|") + ")"
				}
			},
			M: function() {
				for (var a = [], i = 0; i < 12; a.push(Date.getShortMonthName(i)), ++i); // get localised short month names
				return Ext.applyIf({
					s:"(" + a.join("|") + ")"
				}, $f("F"));
			},
			m: {
				g:1,
				c:"m = parseInt(results[{0}], 10) - 1;\n",
				s:"(\\d{2})" // month number with leading zeros (01 - 12)
			},
			n: {
				g:1,
				c:"m = parseInt(results[{0}], 10) - 1;\n",
				s:"(\\d{1,2})" // month number without leading zeros (1 - 12)
			},
			t: {
				g:0,
				c:null,
				s:"(?:\\d{2})" // no. of days in the month (28 - 31)
			},
			L: {
				g:0,
				c:null,
				s:"(?:1|0)"
			},
			o: function() {
				return $f("Y");
			},
			Y: {
				g:1,
				c:"y = parseInt(results[{0}], 10);\n",
				s:"(\\d{4})" // 4-digit year
			},
			y: {
				g:1,
				c:"var ty = parseInt(results[{0}], 10);\n"
					+ "y = ty > Date.y2kYear ? 1900 + ty : 2000 + ty;\n", // 2-digit year
				s:"(\\d{1,2})"
			},
			a: {
				g:1,
				c:"if (results[{0}] == 'am') {\n"
					+ "if (h == 12) { h = 0; }\n"
					+ "} else { if (h < 12) { h += 12; }}",
				s:"(am|pm)"
			},
			A: {
				g:1,
				c:"if (results[{0}] == 'AM') {\n"
					+ "if (h == 12) { h = 0; }\n"
					+ "} else { if (h < 12) { h += 12; }}",
				s:"(AM|PM)"
			},
			g: function() {
				return $f("G");
			},
			G: {
				g:1,
				c:"h = parseInt(results[{0}], 10);\n",
				s:"(\\d{1,2})" // 24-hr format of an hour without leading zeroes (0 - 23)
			},
			h: function() {
				return $f("H");
			},
			H: {
				g:1,
				c:"h = parseInt(results[{0}], 10);\n",
				s:"(\\d{2})" //  24-hr format of an hour with leading zeroes (00 - 23)
			},
			i: {
				g:1,
				c:"i = parseInt(results[{0}], 10);\n",
				s:"(\\d{2})" // minutes with leading zeros (00 - 59)
			},
			s: {
				g:1,
				c:"s = parseInt(results[{0}], 10);\n",
				s:"(\\d{2})" // seconds with leading zeros (00 - 59)
			},
			u: {
				g:1,
				c:"ms = results[{0}]; ms = parseInt(ms, 10)/Math.pow(10, ms.length - 3);\n",
				s:"(\\d+)" // decimal fraction of a second (minimum = 1 digit, maximum = unlimited)
			},
			O: {
				g:1,
				c:[
					"o = results[{0}];",
					"var sn = o.substring(0,1),", // get + / - sign
					"hr = o.substring(1,3)*1 + Math.floor(o.substring(3,5) / 60),", // get hours (performs minutes-to-hour conversion also, just in case)
					"mn = o.substring(3,5) % 60;", // get minutes
					"o = ((-12 <= (hr*60 + mn)/60) && ((hr*60 + mn)/60 <= 14))? (sn + String.leftPad(hr, 2, '0') + String.leftPad(mn, 2, '0')) : null;\n" // -12hrs <= GMT offset <= 14hrs
				].join("\n"),
				s: "([+\-]\\d{4})" // GMT offset in hrs and mins
			},
			P: {
				g:1,
				c:[
					"o = results[{0}];",
					"var sn = o.substring(0,1),", // get + / - sign
					"hr = o.substring(1,3)*1 + Math.floor(o.substring(4,6) / 60),", // get hours (performs minutes-to-hour conversion also, just in case)
					"mn = o.substring(4,6) % 60;", // get minutes
					"o = ((-12 <= (hr*60 + mn)/60) && ((hr*60 + mn)/60 <= 14))? (sn + String.leftPad(hr, 2, '0') + String.leftPad(mn, 2, '0')) : null;\n" // -12hrs <= GMT offset <= 14hrs
				].join("\n"),
				s: "([+\-]\\d{2}:\\d{2})" // GMT offset in hrs and mins (with colon separator)
			},
			T: {
				g:0,
				c:null,
				s:"[A-Z]{1,4}" // timezone abbrev. may be between 1 - 4 chars
			},
			Z: {
				g:1,
				c:"zz = results[{0}] * 1;\n" // -43200 <= UTC offset <= 50400
					+ "zz = (-43200 <= zz && zz <= 50400)? zz : null;\n",
				s:"([+\-]?\\d{1,5})" // leading '+' sign is optional for UTC offset
			},
			c: function() {
				var calc = [],
					arr = [
						$f("Y", 1), // year
						$f("m", 2), // month
						$f("d", 3), // day
						$f("h", 4), // hour
						$f("i", 5), // minute
						$f("s", 6), // second
						{c:"ms = results[7] || '0'; ms = parseInt(ms, 10)/Math.pow(10, ms.length - 3);\n"}, // decimal fraction of a second (minimum = 1 digit, maximum = unlimited)
						{c:[ // allow either "Z" (i.e. UTC) or "-0530" or "+08:00" (i.e. UTC offset) timezone delimiters. assumes local timezone if no timezone is specified
								"if(results[8]) {", // timezone specified
								"if(results[8] == 'Z'){",
								"zz = 0;", // UTC
								"}else if (results[8].indexOf(':') > -1){",
								$f("P", 8).c, // timezone offset with colon separator
								"}else{",
								$f("O", 8).c, // timezone offset without colon separator
								"}",
								"}"
							].join('\n')}
					];

				for (var i = 0, l = arr.length; i < l; ++i) {
					calc.push(arr[i].c);
				}

				return {
					g:1,
					c:calc.join(""),
					s:[
						arr[0].s, // year (required)
						"(?:", "-", arr[1].s, // month (optional)
						"(?:", "-", arr[2].s, // day (optional)
						"(?:",
						"(?:T| )?", // time delimiter -- either a "T" or a single blank space
						arr[3].s, ":", arr[4].s,  // hour AND minute, delimited by a single colon (optional). MUST be preceded by either a "T" or a single blank space
						"(?::", arr[5].s, ")?", // seconds (optional)
						"(?:(?:\\.|,)(\\d+))?", // decimal fraction of a second (e.g. ",12345" or ".98765") (optional)
						"(Z|(?:[-+]\\d{2}(?::)?\\d{2}))?", // "Z" (UTC) or "-0530" (UTC offset without colon delimiter) or "+08:00" (UTC offset with colon delimiter) (optional)
						")?",
						")?",
						")?"
					].join("")
				}
			},
			U: {
				g:1,
				c:"u = parseInt(results[{0}], 10);\n",
				s:"(-?\\d+)" // leading minus sign indicates seconds before UNIX epoch
			}
		}
	});

}());

Ext.apply(Date.prototype, {
	// private
	dateFormat : function(format) {
		if (Date.formatFunctions[format] == null) {
			Date.createFormat(format);
		}
		return Date.formatFunctions[format].call(this);
	},


	getTimezone : function() {
		// the following list shows the differences between date strings from different browsers on a WinXP SP2 machine from an Asian locale:
		//
		// Opera  : "Thu, 25 Oct 2007 22:53:45 GMT+0800" -- shortest (weirdest) date string of the lot
		// Safari : "Thu Oct 25 2007 22:55:35 GMT+0800 (Malay Peninsula Standard Time)" -- value in parentheses always gives the correct timezone (same as FF)
		// FF     : "Thu Oct 25 2007 22:55:35 GMT+0800 (Malay Peninsula Standard Time)" -- value in parentheses always gives the correct timezone
		// IE     : "Thu Oct 25 22:54:35 UTC+0800 2007" -- (Asian system setting) look for 3-4 letter timezone abbrev
		// IE     : "Thu Oct 25 17:06:37 PDT 2007" -- (American system setting) look for 3-4 letter timezone abbrev
		//
		// this crazy regex attempts to guess the correct timezone abbreviation despite these differences.
		// step 1: (?:\((.*)\) -- find timezone in parentheses
		// step 2: ([A-Z]{1,4})(?:[\-+][0-9]{4})?(?: -?\d+)?) -- if nothing was found in step 1, find timezone from timezone offset portion of date string
		// step 3: remove all non uppercase characters found in step 1 and 2
		return this.toString().replace(/^.* (?:\((.*)\)|([A-Z]{1,4})(?:[\-+][0-9]{4})?(?: -?\d+)?)$/, "$1$2").replace(/[^A-Z]/g, "");
	},


	getGMTOffset : function(colon) {
		return (this.getTimezoneOffset() > 0 ? "-" : "+")
			+ String.leftPad(Math.floor(Math.abs(this.getTimezoneOffset()) / 60), 2, "0")
			+ (colon ? ":" : "")
			+ String.leftPad(Math.abs(this.getTimezoneOffset() % 60), 2, "0");
	},


	getDayOfYear: function() {
		var i = 0,
			num = 0,
			d = this.clone(),
			m = this.getMonth();

		for (i = 0, d.setMonth(0); i < m; d.setMonth(++i)) {
			num += d.getDaysInMonth();
		}
		return num + this.getDate() - 1;
	},


	getWeekOfYear : function() {
		// adapted from http://www.merlyn.demon.co.uk/weekcalc.htm
		var ms1d = 864e5, // milliseconds in a day
			ms7d = 7 * ms1d; // milliseconds in a week

		return function() { // return a closure so constants get calculated only once
			var DC3 = Date.UTC(this.getFullYear(), this.getMonth(), this.getDate() + 3) / ms1d, // an Absolute Day Number
				AWN = Math.floor(DC3 / 7), // an Absolute Week Number
				Wyr = new Date(AWN * ms7d).getUTCFullYear();

			return AWN - Math.floor(Date.UTC(Wyr, 0, 7) / ms7d) + 1;
		}
	}(),


	isLeapYear : function() {
		var year = this.getFullYear();
		return !!((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
	},


	getFirstDayOfMonth : function() {
		var day = (this.getDay() - (this.getDate() - 1)) % 7;
		return (day < 0) ? (day + 7) : day;
	},


	getLastDayOfMonth : function() {
		return this.getLastDateOfMonth().getDay();
	},



	getFirstDateOfMonth : function() {
		return new Date(this.getFullYear(), this.getMonth(), 1);
	},


	getLastDateOfMonth : function() {
		return new Date(this.getFullYear(), this.getMonth(), this.getDaysInMonth());
	},


	getDaysInMonth: function() {
		var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

		return function() { // return a closure for efficiency
			var m = this.getMonth();

			return m == 1 && this.isLeapYear() ? 29 : daysInMonth[m];
		}
	}(),


	getSuffix : function() {
		switch (this.getDate()) {
			case 1:
			case 21:
			case 31:
				return "st";
			case 2:
			case 22:
				return "nd";
			case 3:
			case 23:
				return "rd";
			default:
				return "th";
		}
	},


	clone : function() {
		return new Date(this.getTime());
	},


	isDST : function() {
		// adapted from http://extjs.com/forum/showthread.php?p=247172#post247172
		// courtesy of @geoffrey.mcgill
		return new Date(this.getFullYear(), 0, 1).getTimezoneOffset() != this.getTimezoneOffset();
	},


	clearTime : function(clone) {
		if (clone) {
			return this.clone().clearTime();
		}

		// get current date before clearing time
		var d = this.getDate();

		// clear time
		this.setHours(0);
		this.setMinutes(0);
		this.setSeconds(0);
		this.setMilliseconds(0);

		if (this.getDate() != d) { // account for DST (i.e. day of month changed when setting hour = 0)
			// note: DST adjustments are assumed to occur in multiples of 1 hour (this is almost always the case)
			// refer to http://www.timeanddate.com/time/aboutdst.html for the (rare) exceptions to this rule

			// increment hour until cloned date == current date
			for (var hr = 1, c = this.add(Date.HOUR, hr); c.getDate() != d; hr++, c = this.add(Date.HOUR, hr));

			this.setDate(d);
			this.setHours(c.getHours());
		}

		return this;
	},


	add : function(interval, value) {
		var d = this.clone();
		if (!interval || value === 0) return d;

		switch(interval.toLowerCase()) {
			case Date.MILLI:
				d.setMilliseconds(this.getMilliseconds() + value);
				break;
			case Date.SECOND:
				d.setSeconds(this.getSeconds() + value);
				break;
			case Date.MINUTE:
				d.setMinutes(this.getMinutes() + value);
				break;
			case Date.HOUR:
				d.setHours(this.getHours() + value);
				break;
			case Date.DAY:
				d.setDate(this.getDate() + value);
				break;
			case Date.MONTH:
				var day = this.getDate();
				if (day > 28) {
					day = Math.min(day, this.getFirstDateOfMonth().add('mo', value).getLastDateOfMonth().getDate());
				}
				d.setDate(day);
				d.setMonth(this.getMonth() + value);
				break;
			case Date.YEAR:
				d.setFullYear(this.getFullYear() + value);
				break;
		}
		return d;
	},


	between : function(start, end) {
		var t = this.getTime();
		return start.getTime() <= t && t <= end.getTime();
	}
});



Date.prototype.format = Date.prototype.dateFormat;


// private
if (Ext.isSafari && (navigator.userAgent.match(/WebKit\/(\d+)/)[1] || NaN) < 420) {
	Ext.apply(Date.prototype, {
		_xMonth : Date.prototype.setMonth,
		_xDate  : Date.prototype.setDate,

		// Bug in Safari 1.3, 2.0 (WebKit build < 420)
		// Date.setMonth does not work consistently if iMonth is not 0-11
		setMonth : function(num) {
			if (num <= -1) {
				var n = Math.ceil(-num),
					back_year = Math.ceil(n / 12),
					month = (n % 12) ? 12 - n % 12 : 0;

				this.setFullYear(this.getFullYear() - back_year);

				return this._xMonth(month);
			} else {
				return this._xMonth(num);
			}
		},

		// Bug in setDate() method (resolved in WebKit build 419.3, so to be safe we target Webkit builds < 420)
		// The parameter for Date.setDate() is converted to a signed byte integer in Safari
		// http://brianary.blogspot.com/2006/03/safari-date-bug.html
		setDate : function(d) {
			// use setTime() to workaround setDate() bug
			// subtract current day of month in milliseconds, then add desired day of month in milliseconds
			return this.setTime(this.getTime() - (this.getDate() - d) * 864e5);
		}
	});
}