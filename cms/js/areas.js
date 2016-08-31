Event.observe(window, 'load', loadPage);

function loadPage() {
// 	BindById('delete_contenidos', 'click', deleteContenidos);
// 	BindById('buscar',         'click', fetchallContenidos);
// 	BindById('reestablecer',   'click', reestablecerGrabaciones);

	fetchallContenidos();

}

function reestablecerGrabaciones() {
    $("fecha").value  = "";
    $("nombre").value = "";
}

function deleteContenidos() {

        if(! confirm("Esta seguro ?"))
                return;

        var ids = "0";

        document.getElementsByClassName('check_contenido').each(function(item) {
                if(item.checked != false) {
                        ids += "," + item.getAttribute('id');
                }
        });

        var url = '?';
        var myDate=new Date();
        var pars = "event=delete_contenidos";
        pars+="&ids=" + ids;
        pars+="&time="+ myDate.getTime();
        var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: deleteContenidosResponse });
        $('contenidos_status').innerHTML = "Borrando";
}

function paginarContenidos(e) {
    $('contenidos_limit_from').value  = this.getAttribute('limit_from');
    $('contenidos_limit_count').value  = this.getAttribute('limit_count');
    fetchallContenidos();
}

function fetchallContenidos() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=list_contenidos";
	pars+="&idarea=" + $('idarea').value;
// 	if($('nombre').value)    pars+="&nombre="    + $('nombre').value;
// 	if($('contenido').value) pars+="&contenido=" + $('contenido').value;
// 	if($('idtipo').value)    pars+="&idtipo="    + $('idtipo').value;
	if($('contenidos_limit_from').value)   pars+="&contenidos_limit_from="   + $('contenidos_limit_from').value;
	if($('contenidos_limit_count').value)  pars+="&contenidos_limit_count="  + $('contenidos_limit_count').value;
	if($('contenidos_order_by').value)     pars+="&contenidos_order_by="     + $('contenidos_order_by').value;
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: fetchallContenidosResponse });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('contenidos_status').innerHTML = "Cargando...";
}

function editarPublicacion() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=editar_publicacion";
	pars+="&idpublicacion=" + this.getAttribute('idpublicacion');
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: editarPublicacionResponse });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('contenidos_status').innerHTML = "Cargando...";
}

function grabarPublicacion() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=grabar_publicacion";
	pars+="&idarea=" + $('idarea').value;
	pars+="&publicacion_contenido_titulo=" + $('contenido_titulo').value;
	pars+="&publicacion_idcontenido=" + $('publicacion_idcontenido').value;
	pars+="&publicacion_idpublicacion=" + $('publicacion_idpublicacion').value;
	pars+="&publicacion_fecha=" + $('publicacion_fecha').value;
	pars+="&publicacion_destacada=" + $('publicacion_destacada').value;
	pars+="&publicacion_orden=" + $('publicacion_orden').value;
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete:  fetchallContenidos });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('contenidos_status').innerHTML = "Cargando...";
}

function orderBy(e) {
	if($('contenidos_order_by').value == this.getAttribute("tipo") + " asc") {
		$('contenidos_order_by').value = this.getAttribute("tipo") + " desc";
	} else {
		$('contenidos_order_by').value = this.getAttribute("tipo") + " asc";
	}
	fetchallContenidos();
}

function deleteContenidosResponse(deleteContenidosResponse) {

	$('contenidos_status').innerHTML = "Listo";
	fetchallContenidos();
}

function fetchallContenidosResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);
	$('listcontenidos_place').innerHTML  = TrimPath.processDOMTemplate("listcontenidos", C);
	$('listcontenidos_place_pager').innerHTML = pager('contenidos',C['from'],C['count'],C['total']);
	BindByClassName('pager_contenidos',  'click', paginarContenidos);
	BindById('chequear_todas', 'click', checkAll);
// 	BindByClassName('fetch_comentario',   'click', fetchComentario);
	BindByClassName('borrar_contenidos',  'click', deleteContenidos);
	BindByClassName('editar_publicacion',  'click', editarPublicacion);
	BindByClassName('nueva_publicacion',  'click', nuevaPublicacion);
	BindByClassName('order',  'click', orderBy);

	$('contenidos_status').innerHTML = "Listo";
}

function editarPublicacionResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);
	$('listcontenidos_place').innerHTML  = TrimPath.processDOMTemplate("editpublicacion", C);
	$('listcontenidos_place_pager').innerHTML = "";
	BindByClassName('grabar_publicacion',  'click', grabarPublicacion);
	BindByClassName('cerrar_publicacion',  'click', fetchallContenidos);

// 	alert(C['publicacion']['destacada']);
	setvalue($('publicacion_destacada'), C['publicacion']['destacada']);

	Calendar.setup({
		inputField     :    "publicacion_fecha",      // id of the input field
		ifFormat       :    "%Y-%m-%d %H:%M:00",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "bt_publicacion_fecha",   // trigger for the calendar (button ID)
		singleClick    :    false,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

	$('contenidos_status').innerHTML = "Listo";
}

// function fetchGrabacionResponse(originalRequest) {
//         var Grabacion = JSON.parse(originalRequest.responseText);
// 	$('titulo_place').innerHTML = TrimPath.processDOMTemplate("titulo", Grabacion);
// 	$('idgrabacion').value  = Grabacion['grabacion']['id'];
// 	$('b_limit_from').value = 0;
// 	$('repositorio_status').src = "../images/ready.gif";
// }


function checkAll() {
	if(this.checked == false) {
		document.getElementsByClassName('check_contenido').each(function(item) {
			item.checked = false;
		});
	} else {
		document.getElementsByClassName('check_contenido').each(function(item) {
			item.checked = true;
		});
	}
}

function nuevaPublicacion() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=editar_publicacion";
	pars+="&idpublicacion=" + "0";
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: nuevaPublicacionResponse });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('contenidos_status').innerHTML = "Cargando...";
}




function nuevaPublicacionResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);
	$('listcontenidos_place').innerHTML  = TrimPath.processDOMTemplate("nuevapublicacion", C);
	$('listcontenidos_place_pager').innerHTML = "";
	BindByClassName('grabar_publicacion',  'click', grabarPublicacion);
	BindByClassName('cerrar_publicacion',  'click', fetchallContenidos);

// 	alert(C['publicacion']['destacada']);
	Calendar.setup({
		inputField     :    "publicacion_fecha",      // id of the input field
		ifFormat       :    "%Y-%m-%d %H:%M:00",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "bt_publicacion_fecha",   // trigger for the calendar (button ID)
		singleClick    :    false,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});

 	new Ajax.Autocompleter('contenido_titulo','contenido_tituloupdate','?event=autocompleter_contenidos&contenido_titulo');
	$('contenidos_status').innerHTML = "Listo";
}


