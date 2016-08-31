Event.observe(window, 'load', loadPage);

function loadPage() {
// 	BindById('delete_comentarios', 'click', deleteComentarios);
	BindById('buscar',         'click', fetchallComentarios);
	BindById('reestablecer',   'click', reestablecerGrabaciones);

	fetchallComentarios();

}

function reestablecerGrabaciones() {
    $("fecha").value  = "";
    $("nombre").value = "";
}

function deleteComentarios() {

        if(! confirm("Esta seguro ?"))
                return;

        var ids = "0";

        document.getElementsByClassName('check_comentario').each(function(item) {
                if(item.checked != false) {
                        ids += "," + item.getAttribute('id');
                }
        });

        var url = '?';
        var myDate=new Date();
        var pars = "event=delete";
        pars+="&ids=" + ids;
        pars+="&time="+ myDate.getTime();
        var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: deleteComentariosResponse });
        $('comentarios_status').innerHTML = "Borrando";
}

function paginarComentarios(e) {
    $('comentarios_limit_from').value  = this.getAttribute('limit_from');
    $('comentarios_limit_count').value  = this.getAttribute('limit_count');
    fetchallComentarios();
}

function fetchallComentarios() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=list";
	if($('fecha').value)     pars+="&fecha="     + $('fecha').value;
	if($('nombre').value)    pars+="&nombre="    + $('nombre').value;
// 	if($('contenido').value) pars+="&contenido=" + $('contenido').value;
// 	if($('idtipo').value)    pars+="&idtipo="    + $('idtipo').value;
	if($('comentarios_limit_from').value)   pars+="&comentarios_limit_from="   + $('comentarios_limit_from').value;
	if($('comentarios_limit_count').value)  pars+="&comentarios_limit_count="  + $('comentarios_limit_count').value;
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: fetchallComentariosResponse });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('comentarios_status').innerHTML = "Cargando...";
}

function fetchComentario(id) {
/*        var url = '?';
        var myDate=new Date();
	var pars = "event=fetch_grabacion&idgrabacion="+id;
        pars+="&time="+myDate.getTime();
        var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: fetchGrabacionResponse });
	disconnect();
	$('comentarios_status').innerHTML = "Cargando...";*/
}

function deleteComentariosResponse(deleteComentariosResponse) {

	$('comentarios_status').innerHTML = "Listo";
	fetchallComentarios();
}

function fetchallComentariosResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);
	$('listcomentarios_place').innerHTML  = TrimPath.processDOMTemplate("listcomentarios", C);
	$('listcomentarios_place_pager').innerHTML = pager('comentarios',C['from'],C['count'],C['total']);
	BindByClassName('pager_comentarios',  'click', paginarComentarios);
	BindById('chequear_todas', 'click', checkAll);
// 	BindByClassName('fetch_comentario',   'click', fetchComentario);
	BindByClassName('borrar_comentarios',  'click', deleteComentarios);

	$('comentarios_status').innerHTML = "Listo";
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
		document.getElementsByClassName('check_comentario').each(function(item) {
			item.checked = false;
		});
	} else {
		document.getElementsByClassName('check_comentario').each(function(item) {
			item.checked = true;
		});
	}
}
