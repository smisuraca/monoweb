// ====================================
//	Array
// ====================================
Array.prototype.contains = function (obj) {
  return this.indexOf(obj) != -1;
};


// ====================================
//	Form
// ====================================
Form.Element.setValue = function(element, value) {
  var element = $(element);
  var method = element.tagName.toLowerCase();
  var parameter = Form.Element.Deserializers[method](element, value);
};
Form.Element.Deserializers = {
  input: function(element, value) {
    switch (element.type.toLowerCase()) {
      case 'submit':
      case 'hidden':
      case 'password':
      case 'text':
        Form.Element.Deserializers.textarea(element, value);
      case 'checkbox':
      case 'radio':
        Form.Element.Deserializers.inputSelector(element, value);
    }
  },

  inputSelector: function(element, value) {
    element.checked = value;
  },

  textarea: function(element, value) {
    element.value = value;
  },

  select: function(element, values) {
    if (!values) {
      values = [];
    }
    if (! (values instanceof Array)) {
      values = [ values ];
    }
    for (var i = 0; i < element.length; i++) {
      var opt = element.options[i];
      opt.selected = values.contains(opt.value);
    }
  }
}

var $S = Form.Element.setValue;


// ====================================
//	Event
// ====================================
Event.observeByClass = function(className, event, callback, parent, useCapture){
	useCapture = useCapture || false;
	document.getElementsByClassName(className, parent).each(function(item) {
		Event.observe(item, event, callback.bindAsEventListener(item), false);
	});
}
