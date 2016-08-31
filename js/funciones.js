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

function getParams(str) {
	var params = new Array();
	var pairs = str.split('&');
	for (var i=0; i<pairs.length; i++) {
		nameVal = pairs[i].split('=');
		params[nameVal[0]] = nameVal[1];
	}
	return params;
}

function setvaluesclear(obj) {
    for (var i = 0; i < obj.length; i++) {
	    obj.options[i].selected=false
    }
}

function setvalue(obj, value) {
    var values = value.split(',');
    for (var x=0; x<values.length; x++) {
        for (var i = 0; i < obj.length; i++) {
	    if (obj.options[i].value == values[x]) {
		    obj.options[i].selected=true
	    }
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
    input.value = horas+":"+minutos+":"+segundos;
    return false;
}

function SystemModeSet(mode)
{
  	var system_mode = document.getElementById('system_mode');
	if (system_mode)
	{
		if (!mode)
			mode = SystemModeGet();
		else if (mode == 'expert')
			if (!confirm("ATENCI�!!!\nUd. est�por pasar el sistema a Modo Experto.\n Recuerde que este modo le permite realizar ciertas operaciones que pueden dejar su equipo fuera de servicio. Desea Continuar?"))
				return;

		if (mode == 'expert')
		{
			var d = new Date("January 1, 3000");
			setCookie('system_mode', 'expert', d, "/");
			system_mode.innerHTML =  '<img src=../images/greenled.gif align=absmiddle>&nbsp;Pasar a Modo B&aacute;sico';
		} 
		else
		{
			var d = new Date("January 1, 3000");
			setCookie('system_mode', 'basic', d, "/");
			system_mode.innerHTML =  '<img src=../images/redled.gif align=absmiddle>&nbsp;Pasar a Modo Experto';
		}
	}
}

function SystemModeSwitch()
{
	var data = getCookie('system_mode');
	if (data == 'basic')
		SystemModeSet('expert');
	else
		SystemModeSet('basic');
}

function SystemModeGet()
{
	return getCookie('system_mode');
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

var detect = navigator.userAgent.toLowerCase();

function checkIt(string)
{
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}

function checkBlank(s){
	if (s.indexOf(" ")==-1)
         return true;
	else return false;
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
	
	if (total > 0) 	paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=5>5</a>';
	if (total > 5)  paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=10>10</a>';
	if (total > 10)	paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=20>20</a>';
	if (total > 20)	paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from=0 limit_count=50>50</a>';
	
	if (next < total) {
	    paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from='+next+'              limit_count='+count+'>>></a>';
	    paginado +='<a class="pager_'+table+'" table="'+table+'" limit_from='+(paginas-1)*count+' limit_count='+count+'>>|</a>';
        } else {
            paginado +='<a class="inactive">>></a>';
            paginado +='<a class="inactive">>|</a>';
        }
	return(paginado);
}

// Validaciones
function isEmpty(inputStr)
{
	return inputStr == null || inputStr == "";
}
			
function isNumeric(inputVal) {
	inputStr = inputVal.toString()
	for (var i = 0; i < inputStr.length; i++) {
		var punto=0;
		var oneChar = inputStr.charAt(i)
		if ((oneChar < "0" || oneChar > "9") && (oneChar != ".")) {
			return false
		}
		if (oneChar!='.') {
			punto=1;
		}
	}
	if (punto!='0')
	{
		return true
	}
}

function getDuracion(inputVal) {
	val = inputVal.toString();
	var tmp;
	ret = 0;
	
	if (val.match(/^\d{2}:\d{2}:\d{2}$/)) {
		tmp = val.split(/:/);
		ret = tmp[0] * 3600 + tmp[1] * 60 + tmp[2];
	} else if (isNumeric(inputVal)) {
		ret = inputVal;
	}
	
	return ret;
}

function checkSchedule(h, dm, dw, my) {
	//var undefined;
	//alert(h)
	if (h == -1) {
		return false;
	}
	if (dm == -1 || dw == -1 || my == -1) {
		return false;
	}
	return true;
}

function validateProgramacion() {
	esValido = true;
	var field;
	msg = "";
	
	if (isEmpty(document.form.programacion_duracion.value) || (getDuracion(document.form.programacion_duracion.value) <= 0)) {
		esValido = false;
		field = document.form.programacion_duracion;
		msg = "La duracion no puede ser 0 o menor";
	}
	else if (!checkSchedule($('programacion_h').selectedIndex, $('programacion_dm').selectedIndex, $('programacion_dw').selectedIndex, $('programacion_my').selectedIndex)) {
		esValido = false;
		field = document.form.programacion_h;
		msg = "La configuracion de horario es incorrecta";
	}
	
	if(!esValido) {
		alert(msg);
		field.focus();
	}
	
	return esValido;
}
