//Fix numeric validation
jQuery(document).ready(function(){
		jQuery.validator.methods.range = function (value, element, param) {
		var globalizedValue = value.replace(",", ".");
		return this.optional(element) || (globalizedValue >= param[0] && globalizedValue <= param[1]);
		} 

		jQuery.validator.methods.number = function (value, element) {
		return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
		}
});