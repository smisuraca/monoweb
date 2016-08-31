var Home  = {};

Home.Encuesta = {};

Home.Encuesta.Save = function(e)
{
	var params = {
		idcontenido: this.getAttribute("idcontenido"),
		idopcion:    this.getAttribute("idopcion"),
		tpl:         "home_encuesta_view"
	};

	var res = new Ajax.Updater(
		this.getAttribute("idcontenido"),
		"../encuestas/",
		{
			method: 'post',
			parameters: $H(params).toQueryString()
		}
	);
}

Home.Init = function()
{
// 	Event.observeByClass('btnVotar', 'click', Home.Encuesta.Save);
}

FastInit.addOnLoad(Home.Init);

