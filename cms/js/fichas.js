Event.observe(window, 'load', loadPage);

function loadPage() {
// 	BindById('delete_fichas', 'click', deleteFichas);
 	BindById('buscar',         'click', fetchallFichas);
// 	BindById('reestablecer',   'click', reestablecerGrabaciones);

	fetchallFichas();

}

function deleteFichas() {

        if(! confirm("Esta seguro ?"))
                return;

        var ids = "0";

        document.getElementsByClassName('check_ficha').each(function(item) {
                if(item.checked != false) {
                        ids += "," + item.getAttribute('idficha');
                }
        });

        var url = '?';
        var myDate=new Date();
        var pars = "event=delete";
        pars+="&ids=" + ids;
        pars+="&time="+ myDate.getTime();
        var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: deleteFichasResponse });
        $('fichas_status').innerHTML = "Borrando";
}

function paginarFichas(e) {
    $('fichas_limit_from').value  = this.getAttribute('limit_from');
    $('fichas_limit_count').value  = this.getAttribute('limit_count');
    fetchallFichas();
}

function fetchallFichas() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=list";

	pars+="&filter_id="     + $('filter_id').value;	
	pars+="&filter_titulo=" + $('filter_titulo').value;	
	pars+="&filter_direccion=" + $('filter_direccion').value;	
	pars+="&filter_genero="      + $('filter_genero').options[$('filter_genero').selectedIndex].value;	
	pars+="&filter_material="      + $('filter_material').options[$('filter_material').selectedIndex].value;	
	pars+="&filter_publicacion=" + $('filter_publicacion').options[$('filter_publicacion').selectedIndex].value;	
	pars+="&filter_order_by=" + $('filter_order_by').options[$('filter_order_by').selectedIndex].value;	

	if($('fichas_limit_from').value)   pars+="&fichas_limit_from="   + $('fichas_limit_from').value;
	if($('fichas_limit_count').value)  pars+="&fichas_limit_count="  + $('fichas_limit_count').value;
//	if($('fichas_order_by').value)     pars+="&fichas_order_by="     + $('fichas_order_by').value;
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: fetchallFichasResponse });
// 	$('repositorio_status').src = "../images/wait.gif";
	$('fichas_status').innerHTML = "Cargando...";
}

function editFicha() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=edit";
	pars+="&id=" + this.getAttribute('idficha');
	pars+="&time="+myDate.getTime();
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete: editFichaResponse });
	$('fichas_status').innerHTML = "Cargando...";
}

function grabarFicha() {
	var url = '?';
	var myDate=new Date();
	var pars = "event=grabar_ficha";
	pars+="&id=" + $('id').value;
	pars+="&titulo=" + escape($('titulo').value);
	pars+="&titulo_en=" + escape($('titulo_en').value);
	pars+="&duracion=" + $('duracion').value;
	pars+="&genero=" + $('genero').value;
	pars+="&material=" + $('material').value;
	pars+="&formato_realizacion=" + $('formato_realizacion').value;
	pars+="&idioma=" + escape($('idioma').value);
	pars+="&subtitulo_es=" + $('subtitulo_es').value;
	pars+="&subtitulo_en=" + $('subtitulo_en').value;
	pars+="&anno_realizacion=" + $('anno_realizacion').value;
	pars+="&lugar_realizacion=" + escape($('lugar_realizacion').value);
	pars+="&web=" + escape($('web').value);
	pars+="&premios=" + escape($('premios').value);
	pars+="&sinopsis_es=" + escape($('sinopsis_es').value);
	pars+="&sinopsis_en=" + escape($('sinopsis_en').value);
	pars+="&direccion=" + escape($('direccion').value);
	pars+="&guion=" + escape($('guion').value);
	pars+="&produccion=" + escape($('produccion').value);
	pars+="&asistente_direccion=" + escape($('asistente_direccion').value);
	pars+="&fotografia=" + escape($('fotografia').value);
	pars+="&camara=" + escape($('camara').value);
	pars+="&arte=" + escape($('arte').value);
	pars+="&musica=" + escape($('musica').value);
	pars+="&sonido=" + escape($('sonido').value);
	pars+="&edicion=" + escape($('edicion').value);
	pars+="&animacion=" + escape($('animacion').value);
	pars+="&productora=" + escape($('productora').value);
	pars+="&interpretes=" + escape($('interpretes').value);
	pars+="&director_nombre=" + escape($('director_nombre').value);
	pars+="&director_domicilio=" + escape($('director_domicilio').value);
	pars+="&director_codigo_postal=" + $('director_codigo_postal').value;
	pars+="&director_telefono=" + $('director_telefono').value;
	pars+="&director_mail=" + escape($('director_mail').value);
	pars+="&director_nacionalidad=" + escape($('director_nacionalidad').value);
	pars+="&url=" + escape($('url').value);
	pars+="&url_cinevivo=" + escape($('url_cinevivo').value);
	pars+="&url_cinenacional=" + escape($('url_cinenacional').value);
	pars+="&url_kane=" + escape($('url_kane').value);
	pars+="&image_gif=" + escape($('image_gif').value);
	pars+="&publicacion=" + $('publicacion').value;
	pars+="&reproducciones=" + $('reproducciones').value;
	
	var myAjax = new Ajax.Request( url, { method: 'get', parameters: pars, onComplete:  fetchallFichas });
	$('fichas_status').innerHTML = "Cargando...";
}

function grabarFichaResponse(e) {
	alert(e.responseText);
}

/*
function orderBy(e) {
	if($('fichas_order_by').value == this.getAttribute("tipo") + " asc") {
		$('fichas_order_by').value = this.getAttribute("tipo") + " desc";
	} else {
		$('fichas_order_by').value = this.getAttribute("tipo") + " asc";
	}
	fetchallFichas();
}
*/

function deleteFichasResponse(deleteFichasResponse) {

	$('fichas_status').innerHTML = "Listo";
	fetchallFichas();
}

function fetchallFichasResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);
	$('listfichas_place').innerHTML  = TrimPath.processDOMTemplate("listfichas", C);
	$('listfichas_place_pager').innerHTML = pager('fichas',C['from'],C['count'],C['total']);
	BindByClassName('pager_fichas',  'click', paginarFichas);
	BindById('chequear_todas', 'click', checkAll);
	BindByClassName('borrar_fichas',  'click', deleteFichas);
	BindByClassName('editar_ficha',  'click', editFicha);
//	BindByClassName('order',  'click', orderBy);
//	BindByClassName('order',  'click', orderBy);

	$('fichas_status').innerHTML = "Listo";
}

function editFichaResponse(originalRequest) {
        var C = JSON.parse(originalRequest.responseText);

	var myDate = new Date();
	C['f']['datecache'] = myDate.getTime();
        
	$('listfichas_place').innerHTML  = TrimPath.processDOMTemplate("editficha", C);
	$('listfichas_place_pager').innerHTML = "";
	BindByClassName('reload_image',  'click', reloadImage);
	BindByClassName('grabar',  'click', grabarFicha);
	BindByClassName('salir',  'click', fetchallFichas);

	setvalue($('genero'), C['f']['genero']);
	setvalue($('material'), C['f']['material']);
	setvalue($('formato_realizacion'), C['f']['formato_realizacion']);
//	setvalue($('formato_proyeccion'), C['f']['formato_proyeccion']);
	setvalue($('subtitulo_es'), C['f']['subtitulo_es']);
	setvalue($('subtitulo_en'), C['f']['subtitulo_en']);
	setvalue($('publicacion'), C['f']['publicacion']);

/*	Calendar.setup({
		inputField     :    "publicacion_fecha",      // id of the input field
		ifFormat       :    "%Y-%m-%d %H:%M:00",       // format of the input field
		showsTime      :    true,            // will display a time selector
		button         :    "bt_publicacion_fecha",   // trigger for the calendar (button ID)
		singleClick    :    false,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
*/
	$('fichas_status').innerHTML = "Listo";
}

function reloadImage() {
	var myDate = new Date();

	$('img_ficha').src = $('img_src').value + "&cache="+ myDate.getTime() + "&image.jpg";
}

function checkAll() {
	if(this.checked == false) {
		document.getElementsByClassName('check_ficha').each(function(item) {
			item.checked = false;
		});
	} else {
		document.getElementsByClassName('check_ficha').each(function(item) {
			item.checked = true;
		});
	}
}


