function formSubmit(argdict) {
        var     args = Object.extend({'form' : false, 'addPars' : '', 'target' : false, 'callback' : function() {} }, argdict);
        var form = args['form'], addPars = args['addPars'], target = (args['target'] ? args['target'] : target = form.target) , callback = args['callback'];
        form = ( typeof(element) == 'string' ? $$(form) : form);
//        var esValido = (validarAll(form.getElementsByTagName('input')) ? ( validarAll(form.getElementsByTagName('select')) ? ( validarAll(form.getElementsByTagName('textarea')) ? true : false ) : false ) :false);
  //      if (esValido) {
                var pars = form.serialize() + '&' + addPars;
                var myAjax = new Ajax.Request( target, { method: 'get', parameters: pars, onComplete: callback });
    //    }
}


function BindByClassName(classname,event,callback){
        document.getElementsByClassName(classname).each(function(item) {
            Event.observe(item, event, callback.bindAsEventListener(item), false);
        });
}

function BindById(id,event,callback){
        Event.observe($(id), event,   callback.bindAsEventListener($(id)), false);
}

function mOvr(src,className) {
    src.className=className;
}

function mOut(src,className) {
    src.className=className;
}

function mClk(url) {
    window.location = url;
}

function setvalue(obj, value) {
    for (var i = 0; i < obj.length; i++) {
        if (obj.options[i].value == value) {
            obj.options[i].selected=true
        }
    }
}

function setHoy(input) {
    var time = new Date();
    var anio = time.getFullYear()
    var mes  = time.getMonth()+1
    var dia  = time.getDate()

    if (mes<10) {mes = "0"+mes+""}
    if (dia<10) {dia = "0"+dia+""}
    input.value = anio+""+mes+""+dia;
    return false;
}

function setHora(input) {
    var time = new Date();
    var horas    = time.getHours()
    var minutos  = time.getMinutes()
    var segundos = time.getSeconds()

    if (horas<10) {horas = "0"+horas+""}
    if (minutos<10) {minutos = "0"+minutos+""}
    if (segundos<10) {segundos = "0"+segundos+""}
    input.value = horas+""+minutos+""+segundos;
    return false;
}

function preview_idcontenido(idcontenido) {
    window.open('../contenidos/index.php?event=contenidos_preview&idcontenido='+idcontenido, 'preview', 'scrollbars=yes, menubar=no, toolbar=no, statusbar=no, width=500, height=400');
    return false;
}

function preview_text(text) {
    win = window.open('', 'preview', 'scrollbars=yes, menubar=no, toolbar=no, statusbar=no, width=500, height=400');
    win.document.write('<link rel="stylesheet" href="/css/ccstyles_preview.css" type="text/css">');
    win.document.write(text);
    win.document.close()
;
    return false;
}

var CURRENT_TAB = null;

function TabSelect(name, style, style_select)
{
	if(CURRENT_TAB)
	{
		var tab  = document.getElementById(CURRENT_TAB+'-tab');
		var area = document.getElementById(CURRENT_TAB+'-tab-area');
		tab.className = style;
		area.style.display = "none";
	}

	CURRENT_TAB = name;
	var tab  = document.getElementById(CURRENT_TAB+'-tab');
	var area = document.getElementById(CURRENT_TAB+'-tab-area');
	tab.className = style+" "+style_select;
	area.style.display = "block";
	setCookie("CURRENT_TAB",CURRENT_TAB, false, "/");

	document.body.style.visibility = "hidden";
	document.body.style.visibility = "visible";
}



function pager(table, from, count, total) {
	from    = parseInt(from)  || 0;
	count   = parseInt(count) || 0;
	total   = parseInt(total) || 0;
	
	paginas = Math.ceil(total / count);
	page    = Math.floor(from / count);
	next    = (page + 1) * count;
	prev    = (page - 1) * count;
	
	var paginado = "";
	if (prev > -1) {
		paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0        limit_count='+count+'>|<</a>';
		paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from='+prev+' limit_count='+count+'><<</a>';
	} else {
		paginado +='<a class="inactive">|<</a>';
		paginado +='<a class="inactive"><<</a>';
	}
	
	paginado +='<span>[ '+ ( (total)? from + 1 : total) + ' - ' + ((from+count < total) ? (from+count) : total)  + ' de ' + total + ' ]</span>';
	
	if (total > 0)  paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=5>5</a>';
	if (total > 5)  paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=10>10</a>';
	if (total > 10) paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=20>20</a>';
	if (total > 20) paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=50>50</a>';
	
	if (next < total) {
		paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from='+next+'              limit_count='+count+'>>></a>';
		paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from='+(paginas-1)*count+' limit_count='+count+'>>|</a>';
	} else {
		paginado +='<a class="inactive">>></a>';
		paginado +='<a class="inactive">>|</a>';
	}
	return(paginado);
}
