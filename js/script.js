
var MGASML_Setting_LanguagesList = '';
var MGASML_Setting_Languages;
var MGASML_Setting_Default_Language = ''; 
var MGASML_Setting_Separator = '';
var MGASML_Setting_AttributesList = 'placeholder,title,content,value,class,href,alt';
var MGASML_Setting_ClassName = '';
var MGASML_Setting_FadeInSpeed = 0;
var MGASML_Setting_Mode = ''; 
var MGASML_Setting_GoogleTranslateAPIKey = '';
	
function MGASML_SimpleMultilingual_getCookieLanguage() {
	let name = "MGASML_SimpleMultilingual_Language=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for(let i = 0; i <ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function MGASML_SimpleMultilingual_setCookieLanguage(lang) {
	document.cookie = "MGASML_SimpleMultilingual_Language="+lang+"; expires=Thu, 18 Dec 2099 12:00:00 UTC; path=/";
}

function MGASML_SimpleMultilingual_ApplyOnAttribute(attribute, language='') {
	jQuery("["+attribute+"_"+MGASML_Setting_Default_Language+"]").each(function() {
		jQuery(this).attr(attribute,jQuery(this).attr(attribute+"_"+language));
	});
}

function MGASML_SimpleMultilingual_Apply(language='') {
	if (language=='') {
		var qs = window.location.search;
		var urlParams = new URLSearchParams(qs);
		var lang = urlParams.get('lang');
		if (lang > '') { language = lang; } 
	}
	if (language=='') { language = MGASML_SimpleMultilingual_getCookieLanguage(); }
	if (language=='') { language = (window.navigator.userLanguage || window.navigator.language).substring(0,2); }
	if (language=='' || language.length>2) { language = MGASML_Setting_Default_Language; }
	MGASML_SimpleMultilingual_setCookieLanguage(language);
	jQuery('.SML_SimpleMultilingual_Switcher').attr("CurrentLanguage",language);
	
	jQuery(".SML_SimpleMultilingual_Switcher select").val(language);
	jQuery(".SML_SimpleMultilingual_Switcher").fadeIn(MGASML_Setting_FadeInSpeed);
	jQuery("[class*='"+MGASML_Setting_ClassName+"']").hide(); 
	jQuery("."+MGASML_Setting_ClassName+language).show();
	jQuery(".SML_SimpleMultilingual").removeClass("hide");
	jQuery(".SML_SimpleMultilingual").each(function(){
		if (jQuery(this).hasClass(MGASML_Setting_ClassName+language)) {
			jQuery(this).hide();
		} else {
			jQuery(this).show();
		}
	});
	jQuery("option, title").each(function(){
		jQuery(this).text(jQuery(this).attr('text_'+language));
	});
	
	MGASML_Setting_AttributesList.split(",").forEach((item) => {
		MGASML_SimpleMultilingual_ApplyOnAttribute(item,language);
	});
}


Array.prototype.get=function(index){
	if (index >= this.length) { 
		if (MGASML_Setting_Mode == "dev") {
			return '???'; 
		} else {
			return this[0]; 
		}
	}
    return this[index];
};

/*
function MGASML_SimpleMultilingual_GetText(text,text_default) {
	if (!text) {
		if (MGASML_Setting_Mode == 'dev') {				
			text = '???';
		} else {
			if (!text_default) {
				text = '';
			} else {
				text = text_default;
			}
		}
	}
	return text;
}
*/

function MGASML_SimpleMultilingual_FormatSpan(text,lang) {
	if (lang === undefined) { return ''; }
	//text = MGASML_SimpleMultilingual_GetText(text,text_default);
	//text = MGASML_SimpleMultilingual_GetText(text,text_default);
	return '<span class="'+MGASML_Setting_ClassName+lang+'" style="display:none" >' + text + '</span>';
}

function MGASML_SimpleMultilingual_FormatAttribute(attribute) {
	
	var selector = "["+attribute+"*='"+MGASML_Setting_Separator+"']";
	
	jQuery(selector).each(function() {
		var text = ''+jQuery(this).attr(attribute);
		text = text.trim();
		/*
		var asterix = "";
		if (text.endsWith("*")) {
			asterix = "*";
			text  = text.slice(0, -1);
		}
		*/
		var texts = text.split(MGASML_Setting_Separator);
		for (i = 0; i < MGASML_Setting_Languages.length; i++) {
			//var text = MGASML_SimpleMultilingual_GetText(texts[i]+asterix,texts[0]+asterix);
			//var text = MGASML_SimpleMultilingual_GetText(texts[i],texts[0]);
			var text = texts.get(i);
			if (i==0) {
				jQuery(this).attr(attribute,text);
			}
			jQuery(this).attr(attribute+"_"+MGASML_Setting_Languages[i],text);
		}
	});
}

function MGASML_SimpleMultilingual_Debug(text) {
	var s = jQuery('.SML_SimpleMultilingual_Switcher').attr("Debug");
	if (s) { 
		s += ',';
	} else {
		s = "";
	}
	s += text;
	jQuery('.SML_SimpleMultilingual_Switcher').attr("Debug",s);
}

function MGASML_SimpleMultilingual_WrapTextNode(textNode) {
    var spanNode = document.createElement('span');
    spanNode.setAttribute('class', 'red');
    var newTextNode = document.createTextNode(textNode.textContent);
    spanNode.appendChild(newTextNode);
    textNode.parentNode.replaceChild(spanNode, textNode);
}

function MGASML_SimpleMultilingual_FormatElements_Loop() {

	var selector = ":contains('"+MGASML_Setting_Separator+"'):not(:has(*))";

	var count=0;
	jQuery(selector).filter(function(){ return this.nodeType == Node.ELEMENT_NODE; }).each(function() {
		if (jQuery(this).prop("tagName") != "SCRIPT" && jQuery(this).prop("tagName") != "STYLE") {
			if (jQuery(this)[0].childNodes[0].nodeValue) {
				var text = jQuery(this)[0].childNodes[0].nodeValue.trim();
				if (text > '' && text.includes(MGASML_Setting_Separator)) {
					var elements = text.split(MGASML_Setting_Separator);
					if (jQuery(this).prop("tagName") == "OPTION" || jQuery(this).prop("tagName") == "TITLE") {
						for (i = 0; i < MGASML_Setting_Languages.length; i++) {
							jQuery(this).attr('text_'+MGASML_Setting_Languages[i],''+elements[i]);
						}
						count++;
						jQuery(this).text(jQuery(this).attr('text_'+MGASML_Setting_Languages[0]));
					} else {
						var html = '';
						for (var i = 0; i < MGASML_Setting_Languages.length; i++) {
							html += MGASML_SimpleMultilingual_FormatSpan(elements.get(i),MGASML_Setting_Languages[i]);
						}
						count++;
						jQuery(this).html(html);
					}
				}
			}
		}
	});
	
	MGASML_SimpleMultilingual_Debug("FormatElements_Loop:"+count);
	
}

function MGASML_SimpleMultilingual_FormatElements() {
	
	MGASML_SimpleMultilingual_FormatElements_Loop();
	
	var selector = ":contains('"+MGASML_Setting_Separator+"')";
	
	var count=0;
	jQuery(selector).each(function() {
		for (var j=0; j<jQuery(this)[0].childNodes.length; j++) {
			if (jQuery(this)[0].childNodes[j].nodeType == Node.TEXT_NODE && jQuery(this)[0].childNodes[j].nodeValue && jQuery(this).prop("tagName") != "SCRIPT" && jQuery(this).prop("tagName") != "STYLE") {
				var text = jQuery(this)[0].childNodes[j].nodeValue.trim();
				if (text > '' && text.indexOf(MGASML_Setting_Separator) > -1) {
					MGASML_SimpleMultilingual_WrapTextNode(jQuery(this)[0].childNodes[j]);
					count++;
				}
			}
		}
	});
	MGASML_SimpleMultilingual_Debug("WrapTextNode:"+count);

	MGASML_SimpleMultilingual_FormatElements_Loop();

}

function MGASML_SimpleMultilingual_Setup() {

	MGASML_Setting_AttributesList.split(",").forEach((item) => {
		MGASML_SimpleMultilingual_FormatAttribute(item);
	});
	MGASML_SimpleMultilingual_FormatElements();
	
	jQuery('.SML_SimpleMultilingual').on( "click", function() {
		var lang = jQuery(this).attr('class').split(" language_")[1].split(" ")[0];
		MGASML_SimpleMultilingual_Apply(lang);
	});
	jQuery('.SML_SimpleMultilingual_Switcher select').on( "change", function() {
		var lang = jQuery(this).val();
		MGASML_SimpleMultilingual_Apply(lang); 
	});	 
	
}
function MGASML_SimpleMultilingual_LanguageIndex(Language) {
	return MGASML_Setting_LanguagesList.split(',').findIndex((e) => e == Language);	
}

function MGASML_SimpleMultilingual_ArrayConverter(Texts, Language='') {
	if (Language == '') { Language = MGASML_SimpleMultilingual_getCookieLanguage(); }
	var index = MGASML_SimpleMultilingual_LanguageIndex(Language);
	if (index == -1) { return Texts; }
	for(let i = 0; i < Texts.length; i++) {
		Texts[i] = Texts[i].split(MGASML_Setting_Separator)[index];
	}
	return Texts;
}

function MGASML_SimpleMultilingual_Init(delay=0) {
	if (delay == 0) {
		MGASML_SimpleMultilingual_Setup();
		MGASML_SimpleMultilingual_Apply();
	} else {
		setTimeout(function() {
			MGASML_SimpleMultilingual_Setup();
			MGASML_SimpleMultilingual_Apply();
		}, delay);
	}
}

jQuery(function() {
	var container = jQuery('.SML_SimpleMultilingual_Switcher');
	if (container == undefined) { return; }
	
	MGASML_Setting_LanguagesList = jQuery(container).attr('Languages');
	MGASML_Setting_Languages = MGASML_Setting_LanguagesList.split(",");
	MGASML_Setting_Default_Language = MGASML_Setting_Languages[0];
	MGASML_Setting_Separator = jQuery(container).attr('Separator');
	MGASML_Setting_ClassName = jQuery(container).attr('ClassName');
	MGASML_Setting_FadeInSpeed = jQuery(container).attr('FadeInSpeed');
	MGASML_Setting_Mode = jQuery(container).attr('Mode');
	
	jQuery("head [hreflang]").remove();	
	for(let i = 0; i < MGASML_Setting_Languages.length; i++) {
		var lang = MGASML_Setting_Languages[i];
		jQuery('head').append('<link rel="alternate" info="SML_SimpleMultilingual" hreflang="'+lang+'" href="'+window.location.href+'?lang='+lang+'" > </link>');
	}
	jQuery('head').append('<link rel="alternate" info="SML_SimpleMultilingual" hreflang="x-default" href="'+window.location.href+'?lang='+MGASML_Setting_Default_Language+'" ></link>'); 
	
	if (jQuery('.SML_SimpleMultilingual_Switcher').length == 1) {
		jQuery('nav ul').each(function() {
			if (jQuery(this).parents('ul').length == 0) {
				jQuery(this).append(jQuery("#SML_SimpleMultilingual_SwitcherTemplateContainer div").clone().show());
			}
		});
	}
	
	var qs = window.location.search;
	var urlParams = new URLSearchParams(qs);
	var Activated = urlParams.get('SML_Activated');
	if (Activated && (Activated.toLowerCase()=='off' || ActivatedtoLowerCase()=='false')) {
		return;
	}
	
	MGASML_SimpleMultilingual_Init();
	MGASML_SimpleMultilingual_Init(2000);
	
	jQuery(document).on('gform_post_render', function(){
		MGASML_SimpleMultilingual_Init();
	});

});
