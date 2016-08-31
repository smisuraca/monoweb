    function UtilBeginScript() {
		return String.fromCharCode(60, 115, 99, 114, 105, 112, 116, 62);
    }

    function UtilEndScript() {
		return String.fromCharCode(60, 47, 115, 99, 114, 105, 112, 116, 62);
    }



	function IDGenerator(nextID)
	{
		this.nextID = nextID;
		this.GenerateID = IDGeneratorGenerateID;
	}

	function IDGeneratorGenerateID()
	{
		return this.nextID++;
	}

	
	
	var BUTTON_IMAGE_PREFIX = "buttonImage";
	var BUTTON_DIV_PREFIX = "buttonDiv";
	var BUTTON_PAD1_PREFIX = "buttonPad1";
	var BUTTON_PAD2_PREFIX = "buttonPad2";
	var buttonMap = new Object();

	function Button
	(
		idGenerator,
		caption,
		action,
		image
	)
	{
		this.idGenerator = idGenerator;
		this.caption = caption;
		this.action = action;
		this.image = image;
		this.enabled = true;
		this.Instantiate = ButtonInstantiate;
		this.Enable = ButtonEnable;
	}

	function ButtonInstantiate()
	{
		this.id = this.idGenerator.GenerateID();
		buttonMap[this.id] = this;
		var html = "";
		html += '<div id="';
		html += BUTTON_DIV_PREFIX;
		html += this.id+'"';
//		html += ' class="ButtonNormal"';
		html += ' onselectstart="ButtonOnSelectStart()"';
		html += ' ondragstart="ButtonOnDragStart()"';
		html += ' onmousedown="ButtonOnMouseDown(this)"';
		html += ' onmouseup="ButtonOnMouseUp(this)"';
		html += ' onmouseout="ButtonOnMouseOut(this)"';
		html += ' onmouseover="ButtonOnMouseOver(this)"';
		html += ' onclick="ButtonOnClick(this)"';
		html += ' ondblclick="ButtonOnDblClick(this)"';
		html += '>';
		html += '<table cellpadding=0 cellspacing=0 border=0><tr><td><img id="';
		html += BUTTON_PAD1_PREFIX;
		html += this.id;
		html += '" width=2 height=2></td><td></td><td></td></tr><tr><td></td><td>';
		html += '<img id="';
		html += BUTTON_IMAGE_PREFIX;
		html += this.id;
		html += '" src="';
		html += this.image;
		html += '" title="';
		html += this.caption+'"';
//		html += ' class="Image"';
		html += '>';
		html += '</td><td></td></tr><tr><td></td><td></td><td><img id="';
		html += BUTTON_PAD2_PREFIX;
		html += this.id;
		html += '" width=2 height=2></td></tr></table>';
		html += '</div>';
		document.write(html);
	}

	function ButtonEnable(enabled)
	{
		this.enabled = enabled;
		if (this.enabled)
		{
//			document.all[BUTTON_DIV_PREFIX + this.id].className = "ButtonNormal";
		}
		else
		{
//			document.all[BUTTON_DIV_PREFIX + this.id].className = "ButtonDisabled";
		}
	}

	function ButtonOnSelectStart()
	{
		window.event.returnValue = false;
	}

	function ButtonOnDragStart()
	{
		window.event.returnValue = false;
	}

	function ButtonOnMouseDown(element)
	{
		if (event.button == 1)
		{
			var id = element.id.substring(BUTTON_DIV_PREFIX.length, element.id.length);
			var button = buttonMap[id];
			if (button.enabled)
			{
				ButtonPushButton(id);
			}
		}
	}

	function ButtonOnMouseUp(element)
	{
		if (event.button == 1)
		{
			var id = element.id.substring(BUTTON_DIV_PREFIX.length, element.id.length);
			var button = buttonMap[id];
			if (button.enabled)
			{
				ButtonReleaseButton(id);
			}
		}
	}

	function ButtonOnMouseOut(element)
	{
		var id = element.id.substring(BUTTON_DIV_PREFIX.length, element.id.length);
		var button = buttonMap[id];
		if (button.enabled)
		{
			ButtonReleaseButton(id);
		}
	}

	function ButtonOnMouseOver(element)
	{
		var id = element.id.substring(BUTTON_DIV_PREFIX.length, element.id.length);
		var button = buttonMap[id];
		if (button.enabled)
		{
			ButtonReleaseButton(id);
//			document.all[BUTTON_DIV_PREFIX + id].className = "ButtonMouseOver";
		}
	}

	function ButtonOnClick(element)
	{
		var id = element.id.substring(BUTTON_DIV_PREFIX.length, element.id.length);
		var button = buttonMap[id];
		if (button.enabled)
		{
			eval(button.action);
		}
	}

	function ButtonOnDblClick(element)
	{
		ButtonOnClick(element);
	}

	function ButtonPushButton(id)
	{
		document.all[BUTTON_PAD1_PREFIX + id].width = 3;
		document.all[BUTTON_PAD1_PREFIX + id].height = 3;
		document.all[BUTTON_PAD2_PREFIX + id].width = 1;
		document.all[BUTTON_PAD2_PREFIX + id].height = 1;
//		document.all[BUTTON_DIV_PREFIX + id].className = "ButtonPressed";
	}

	function ButtonReleaseButton(id)
	{
		document.all[BUTTON_PAD1_PREFIX + id].width = 2;
		document.all[BUTTON_PAD1_PREFIX + id].height = 2;
		document.all[BUTTON_PAD2_PREFIX + id].width = 2;
		document.all[BUTTON_PAD2_PREFIX + id].height = 2;
//		document.all[BUTTON_DIV_PREFIX + id].className = "ButtonNormal";
	}

    var IMAGE_CHOOSER_DIV_PREFIX = "imageChooserDiv";
    var IMAGE_CHOOSER_IMG_PREFIX = "imageChooserImg";
    var IMAGE_CHOOSER_ICON_PREFIX = "imageChooserIcon";
    var imageChooserMap = new Object();

    function ImageChooser
    (
	    idGenerator,
	    numRows,
	    numCols,
	    images,
	    callback
    )
    {
	    this.idGenerator = idGenerator;
	    this.numRows = numRows;
	    this.numCols = numCols;
	    this.images = images;
	    this.callback = callback;
	    this.Instantiate = ImageChooserInstantiate;
	    this.Show = ImageChooserShow;
	    this.Hide = ImageChooserHide;
	    this.IsShowing = ImageChooserIsShowing;
	    this.SetUserData = ImageChooserSetUserData;
    }

    function ImageChooserInstantiate()
    {
	    this.id = this.idGenerator.GenerateID();
	    imageChooserMap[this.id] = this;
	    var html = '';
	    html += '<div id="' + IMAGE_CHOOSER_DIV_PREFIX + this.id + '" style="display:none;position:absolute;background-color:buttonface;border-left:buttonhighlight solid 1px;border-top:buttonhighlight solid 1px;border-right:buttonshadow solid 1px;border-bottom:buttonshadow solid 1px">';
	    html += '<table>';
	    for (var i = 0; i < this.numRows; i++) {
		    html += '<tr>';
		    for (var j = 0; j < this.numCols; j++) {
			    html += '<td>';
			    var k = i * this.numCols + j;
			    html += '<div id="' + IMAGE_CHOOSER_ICON_PREFIX + this.id + '_' + k + '" style="border:buttonface solid 1px">';
			    html += '<img src="' + this.images[k] + '" id="' + IMAGE_CHOOSER_IMG_PREFIX + this.id + '_' + k + '" onmouseover="ImageChooserOnMouseOver()" onmouseout="ImageChooserOnMouseOut()" onclick="ImageChooserOnClick()">';
			    html += '</div>';
			    html += '</td>';
		    }
		    html += '</tr>';
	    }
	    html += '</table>';
	    html += '</div>';
	    document.write(html);
    }

    function ImageChooserShow(x, y)
    {
	    eval(IMAGE_CHOOSER_DIV_PREFIX + this.id).style.left = x;
	    eval(IMAGE_CHOOSER_DIV_PREFIX + this.id).style.top = y;
	    eval(IMAGE_CHOOSER_DIV_PREFIX + this.id).style.display = "block";
    }

    function ImageChooserHide()
    {
	    eval(IMAGE_CHOOSER_DIV_PREFIX + this.id).style.display = "none";
    }

    function ImageChooserIsShowing()
    {
	    return eval(IMAGE_CHOOSER_DIV_PREFIX + this.id).style.display == "block";
    }

    function ImageChooserSetUserData(userData)
    {
	this.userData = userData;
    }

    function ImageChooserOnMouseOver()
    {
	    if (event.srcElement.tagName == "IMG") {
		    var underscore = event.srcElement.id.indexOf("_");
		    if (underscore != -1) {
			    var id = event.srcElement.id.substring(IMAGE_CHOOSER_IMG_PREFIX.length, underscore);
			    var index = event.srcElement.id.substring(underscore + 1);
			    eval(IMAGE_CHOOSER_ICON_PREFIX + id + "_" + index).style.borderColor = "black";
		    }
	    }
    }

    function ImageChooserOnMouseOut()
    {
	    if (event.srcElement.tagName == "IMG") {
		    var underscore = event.srcElement.id.indexOf("_");
		    if (underscore != -1) {
			    var id = event.srcElement.id.substring(IMAGE_CHOOSER_IMG_PREFIX.length, underscore);
			    var index = event.srcElement.id.substring(underscore + 1);
			    eval(IMAGE_CHOOSER_ICON_PREFIX + id + "_" + index).style.borderColor = "buttonface";
		    }
	    }
    }

    function ImageChooserOnClick()
    {
	    if (event.srcElement.tagName == "IMG") {
		    var underscore = event.srcElement.id.indexOf("_");
		    if (underscore != -1) {
			    var id = event.srcElement.id.substring(IMAGE_CHOOSER_IMG_PREFIX.length, underscore);
			    var imageChooser = imageChooserMap[id];
			    imageChooser.Hide();
			    var index = event.srcElement.id.substring(underscore + 1);
			    if (imageChooser.callback) {
				    imageChooser.callback(imageChooser.images[index], imageChooser.userData);
			    }
		    }
	    }
    }


	var EDITOR_COMPOSITION_PREFIX = "editorComposition";
	var EDITOR_PARAGRAPH_PREFIX = "editorParagraph";
	var EDITOR_LIST_AND_INDENT_PREFIX = "editorListAndIndent";
	var EDITOR_TOP_TOOLBAR_PREFIX = "editorTopToolbar";
	var EDITOR_BOTTOM_TOOLBAR_PREFIX = "editorBottomToolbar";
	var EDITOR_SMILEY_BUTTON_PREFIX = "editorSmileyButton";
//	var EDITOR_IMAGE_CHOOSER_PREFIX = "editorImageChooser";
	var editorMap = new Object();
	var editorIDGenerator = null;

	function Editor(idGenerator)
	{
		this.idGenerator = idGenerator;
		this.textMode = false;
		this.brief = false;
		this.instantiated = false;
		this.Instantiate = EditorInstantiate;
		this.GetText = EditorGetText;
		this.SetText = EditorSetText;
		this.GetHTML = EditorGetHTML;
		this.SetHTML = EditorSetHTML;
		this.GetBrief = EditorGetBrief;
		this.SetBrief = EditorSetBrief;
	}

	function EditorInstantiate()
	{
		if (this.instantiated) {
			return;
		}
		this.id = this.idGenerator.GenerateID();
		editorMap[this.id] = this;
		editorIDGenerator = this.idGenerator;

		var html = "";
		html += "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">";
		html += "<tr>";
		html += "<td id=\"" + EDITOR_TOP_TOOLBAR_PREFIX + this.id + "\">";
		html += "<table cellpaddin=\"0\" cellspacing=\"0\" border=\"0\">";
		html += "<tr>";
//		html += "<td>";
//		html += "<div class=\"Space\"></div>";
//		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Swatch\"></div>";
//		html += "</td>";
//		html += "<td>";
//		html += "<span id=\"" + EDITOR_PARAGRAPH_PREFIX + this.id + "\" style=\"display:" + (this.brief ? "none" : "inline") + "\">";
//		html += "<select class=\"List\" onchange=\"EditorOnParagraph(" + this.id + ", this)\">";
//		html += "<option class=\"Heading\">Paragraph</option>";
//		html += "<option value=\"Normal\">Normal</option>";
//		html += "<option value=\"Heading 1\">Heading 1 &lt;H1&gt;</option>";
//		html += "<option value=\"Heading 2\">Heading 2 &lt;H2&gt;</option>";
//		html += "<option value=\"Heading 3\">Heading 3 &lt;H3&gt;</option>";
//		html += "<option value=\"Heading 4\">Heading 4 &lt;H4&gt;</option>";
//		html += "<option value=\"Heading 5\">Heading 5 &lt;H5&gt;</option>";
//		html += "<option value=\"Heading 6\">Heading 6 &lt;H6&gt;</option>";
//		html += "<option value=\"Address\">Address &lt;ADDR&gt;</option>";
//		html += "<option value=\"Formatted\">Formatted &lt;PRE&gt;</option>";
//		html += "</select>";
//		html += "</span>";
//		html += "</td>";
//		html += "<td>";
//		html += "<select class=\"List\" onchange=\"EditorOnFont(" + this.id + ", this)\">";
//		html += "<option class=\"Heading\">Fuente</option>";
//		html += "<option value=\"Arial\">Arial</option>";
//		html += "<option value=\"Arial Black\">Arial Black</option>";
//		html += "<option value=\"Arial Narrow\">Arial Narrow</option>";
//		html += "<option value=\"Comic Sans MS\">Comic Sans MS</option>";
//		html += "<option value=\"Courier New\">Courier New</option>";
//		html += "<option value=\"System\">System</option>";
//		html += "<option value=\"Times New Roman\">Times New Roman</option>";
//		html += "<option value=\"Verdana\">Verdana</option>";
//		html += "<option value=\"Wingdings\">Wingdings</option>";
//		html += "</select>";
//		html += "</td>";
//		html += "<td>";
//		html += "<select class=\"List\" onchange=\"EditorOnSize(" + this.id + ", this)\">";
//		html += "<option class=\"Heading\">Size</option>";
//		html += "<option value=\"1\">1</option>";
//		html += "<option value=\"2\">2</option>";
//		html += "<option value=\"3\">3</option>";
//		html += "<option value=\"4\">4</option>";
//		html += "<option value=\"5\">5</option>";
//		html += "<option value=\"6\">6</option>";
//		html += "<option value=\"7\">7</option>";
//		html += "</select>";
//		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
//		html += "<td class=\"Text\">";
//		html += "<input type=\"checkbox\" onclick=\"EditorOnViewHTMLSource(" + this.id + ", this.checked)\">";
//		html += "View HTML Source";
//		html += "</td>";
//		html += "</tr>";
//		html += "</table>";
//		html += "</td>";
//		html += "</tr>";
//		html += "<tr>";
//		html += "<td id=\"" + EDITOR_BOTTOM_TOOLBAR_PREFIX + this.id + "\" class=\"Toolbar\">";
//		html += "<table cellpaddin=\"0\" cellspacing=\"0\" border=\"0\">";
//		html += "<tr>";
//		html += "<td>";
//		html += "<div class=\"Space\"></div>";
//		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Swatch\"></div>";
//		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var cutButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Cortar\",";
		html += "\"EditorOnCut(" + this.id + ")\",";
		html += "\"../images/editorhtml/cut.gif\"";
		html += ");";
		html += "cutButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var copyButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Copiar\",";
		html += "\"EditorOnCopy(" + this.id + ")\",";
		html += "\"../images/editorhtml/copy.gif\"";
		html += ");";
		html += "copyButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var pasteButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Pegar\",";
		html += "\"EditorOnPaste(" + this.id + ")\",";
		html += "\"../images/editorhtml/paste.gif\"";
		html += ");";
		html += "pasteButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var boldButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Negrita\",";
		html += "\"EditorOnBold(" + this.id + ")\",";
		html += "\"../images/editorhtml/bold.gif\"";
		html += ");";
		html += "boldButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var italicButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Italico\",";
		html += "\"EditorOnItalic(" + this.id + ")\",";
		html += "\"../images/editorhtml/italic.gif\"";
		html += ");";
		html += "italicButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var underlineButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Subrayado\",";
		html += "\"EditorOnUnderline(" + this.id + ")\",";
		html += "\"../images/editorhtml/uline.gif\"";
		html += ");";
		html += "underlineButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
//		html += "<td>";
//		html += UtilBeginScript();
//		html += "var foregroundColorButton = new Button(";
//		html += "editorIDGenerator,";
//		html += "\"Foreground Color\",";
//		html += "\"EditorOnForegroundColor(" + this.id + ")\",";
//		html += "\"tpaint.gif\"";
//		html += ");";
//		html += "foregroundColorButton.Instantiate();";
//		html += UtilEndScript();
//		html += "</td>";
//		html += "<td>";
//		html += UtilBeginScript();
//		html += "var backgroundColorButton = new Button(";
//		html += "editorIDGenerator,";
//		html += "\"Background Color\",";
//		html += "\"EditorOnBackgroundColor(" + this.id + ")\",";
//		html += "\"parea.gif\"";
//		html += ");";
//		html += "backgroundColorButton.Instantiate();";
//		html += UtilEndScript();
//		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var alignLeftButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Alinear a la Izquierda\",";
		html += "\"EditorOnAlignLeft(" + this.id + ")\",";
		html += "\"../images/editorhtml/aleft.gif\"";
		html += ");";
		html += "alignLeftButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var centerButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Centrar\",";
		html += "\"EditorOnCenter(" + this.id + ")\",";
		html += "\"../images/editorhtml/center.gif\"";
		html += ");";
		html += "centerButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var alignRightButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Alinear a la Derecha\",";
		html += "\"EditorOnAlignRight(" + this.id + ")\",";
		html += "\"../images/editorhtml/aright.gif\"";
		html += ");";
		html += "alignRightButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
		html += "<td id=\"" + EDITOR_LIST_AND_INDENT_PREFIX + this.id + "\" style=\"display:" + (this.brief ? "none" : "inline") + "\">";
		html += "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		html += "<tr>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var numberedListButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Numerar\",";
		html += "\"EditorOnNumberedList(" + this.id + ")\",";
		html += "\"../images/editorhtml/nlist.gif\"";
		html += ");";
		html += "numberedListButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var bullettedListButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Viñetas\",";
		html += "\"EditorOnBullettedList(" + this.id + ")\",";
		html += "\"../images/editorhtml/blist.gif\"";
		html += ");";
		html += "bullettedListButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var decreaseIndentButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Indentar\",";
		html += "\"EditorOnDecreaseIndent(" + this.id + ")\",";
		html += "\"../images/editorhtml/ileft.gif\"";
		html += ");";
		html += "decreaseIndentButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var increaseIndentButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Des-Indentar\",";
		html += "\"EditorOnIncreaseIndent(" + this.id + ")\",";
		html += "\"../images/editorhtml/iright.gif\"";
		html += ");";
		html += "increaseIndentButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
//		html += "<td>";
//		html += "<div class=\"Divider\"></div>";
//		html += "</td>";
		html += "</tr>";
		html += "</table>";
		html += "</td>";
		html += "<td>";
		html += UtilBeginScript();
		html += "var createHyperlinkButton = new Button(";
		html += "editorIDGenerator,";
		html += "\"Crear Hiperlink\",";
		html += "\"EditorOnCreateHyperlink(" + this.id + ")\",";
		html += "\"../images/editorhtml/wlink.gif\"";
		html += ");";
		html += "createHyperlinkButton.Instantiate();";
		html += UtilEndScript();
		html += "</td>";
		html += "<td>";
		html += "<input type=\"checkbox\" onclick=\"EditorOnViewHTMLSource(" + this.id + ", this.checked)\">";
		html += "<font size=2>View HTML Source</font>";
		html += "</td>";
//		html += "<td id=\"" + EDITOR_SMILEY_BUTTON_PREFIX + this.id + "\">";
//		html += UtilBeginScript();
//		html += "var insertSmileyButton = new Button(";
//		html += "editorIDGenerator,";
//		html += "\"Insert Smiley\",";
//		html += "\"EditorOnStartInsertSmiley(" + this.id + ")\",";
//		html += "\"smiley.gif\"";
//		html += ");";
//		html += "insertSmileyButton.Instantiate();";
//		html += UtilEndScript();
//		html += "</td>";
		html += "</tr>";
		html += "</table>";
		html += "</td>";
		html += "</tr>";
		html += "<tr>";
		html += "<td>";
		html += "<iframe id=\"" + EDITOR_COMPOSITION_PREFIX + this.id + "\" width=\"100%\" height=\"155\">";
		html += "</iframe>";
		html += "</td>";
		html += "</tr>";
		html += "</table>";
//		html += UtilBeginScript();
//		html += "var " + EDITOR_IMAGE_CHOOSER_PREFIX + this.id + " = new ImageChooser(";
//		html += "editorIDGenerator,";
//		html += "5, 5,";
//		html += "EditorOnEndInsertSmiley";
//		html += ");";
//		html += EDITOR_IMAGE_CHOOSER_PREFIX + this.id + ".SetUserData(" + this.id + ");";
//		html += EDITOR_IMAGE_CHOOSER_PREFIX + this.id + ".Instantiate();";
//		html += UtilEndScript();
		document.write(html);

		html = '';
		html += '<body style="font:10pt arial">';
		html += '</body>';
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.open();
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.write(html);
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.close();
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.designMode = "on";
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.onclick = new Function("EditorOnClick(" + this.id + ")");

		editorIDGenerator = null;
		this.instantiated = true;
	}

	function  EditorGetText()
	{
		return eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerText;
	}

	function  EditorSetText(text)
	{
		text = text.replace(/\n/g, "<br>");
		eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerHTML = text;
	}

	function  EditorGetHTML()
	{
		if (this.textMode) {
			return eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerText;
		}
		EditorCleanHTML(this.id);
		EditorCleanHTML(this.id);
		return eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerHTML;
	}

	function  EditorSetHTML(html)
	{
		if (this.textMode) {
			eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerText = html;
		}
		else {
			eval(EDITOR_COMPOSITION_PREFIX + this.id).document.body.innerHTML = html;
		}
	}

	function EditorGetBrief()
	{
		return this.brief;
	}

	function EditorSetBrief(brief)
	{
		this.brief = brief;
		var display = this.brief ? "none" : "inline";
		if (this.instantiated) {
			eval(EDITOR_PARAGRAPH_PREFIX + this.id).style.display = display;
			eval(EDITOR_LIST_AND_INDENT_PREFIX + this.id).style.display = display;
		}
	}

	function EditorOnCut(id)
	{
		EditorFormat(id, "cut");
	}

	function EditorOnCopy(id)
	{
		EditorFormat(id, "copy");
	}

	function EditorOnPaste(id)
	{
		EditorFormat(id, "paste");
	}

	function EditorOnBold(id)
	{
		EditorFormat(id, "bold");
	}

	function EditorOnItalic(id)
	{
		EditorFormat(id, "italic");
	}

	function EditorOnUnderline(id)
	{
		EditorFormat(id, "underline");
	}

//	function EditorOnForegroundColor(id)
//	{
//		if (!EditorValidateMode(id)) {
//			return;
//		}
//		var color = showModalDialog("/ym/ColorSelect", "", "font-family:Verdana;font-size:12;dialogWidth:30em;dialogHeight:35em");
//		if (color) {
//			EditorFormat(id, "forecolor", color);
//		}
//		else {
//
//			eval(EDITOR_COMPOSITION_PREFIX + id).focus();
//		}
//	}

//	function EditorOnBackgroundColor(id)
//	{
//		if (!EditorValidateMode(id)) {
//			return;
//		}
//		var color = showModalDialog("/ym/ColorSelect", "", "font-family:Verdana;font-size:12;dialogWidth:30em;dialogHeight:35em");
//		if (color) {
//			EditorFormat(id, "backcolor", color);
//		}
//		else {
//			eval(EDITOR_COMPOSITION_PREFIX + id).focus();
//		}
//	}

	function EditorOnAlignLeft(id)
	{
		EditorFormat(id, "justifyleft");
	}

	function EditorOnCenter(id)
	{
		EditorFormat(id, "justifycenter");
	}

	function EditorOnAlignRight(id)
	{
		EditorFormat(id, "justifyright");
	}

	function EditorOnNumberedList(id)
	{
		EditorFormat(id, "insertOrderedList");
	}

	function EditorOnBullettedList(id)
	{
		EditorFormat(id, "insertUnorderedList");
	}

	function EditorOnDecreaseIndent(id)
	{
		EditorFormat(id, "outdent");
	}

	function EditorOnIncreaseIndent(id)
	{
		EditorFormat(id, "indent");
	}

	function EditorOnCreateHyperlink(id)
	{
		if (!EditorValidateMode(id)) {
			return;
		}
		var anchor = EditorGetElement("A", eval(EDITOR_COMPOSITION_PREFIX + id).document.selection.createRange().parentElement());
		var link = prompt("Ingrese el URL (ejemplo: http://www.eima.tv):", anchor ? anchor.href : "http://");
		if (link && link != "http://") {
			if (eval(EDITOR_COMPOSITION_PREFIX + id).document.selection.type == "None") {
				var range = eval(EDITOR_COMPOSITION_PREFIX + id).document.selection.createRange();
				range.pasteHTML('<A HREF="' + link + '"></A>');
				range.select();
			}
			else {
				EditorFormat(id, "CreateLink", link);
			}
		}
	}

//	function EditorOnStartInsertSmiley(id)
//	{
//		if (eval(EDITOR_IMAGE_CHOOSER_PREFIX + id).IsShowing()) {
//			eval(EDITOR_IMAGE_CHOOSER_PREFIX + id).Hide();
//		}
//		else {
//			var editor = editorMap[id];
//			editor.selectionRange = eval(EDITOR_COMPOSITION_PREFIX + id).document.selection.createRange();
//			eval(EDITOR_IMAGE_CHOOSER_PREFIX + id).Show(eval(EDITOR_SMILEY_BUTTON_PREFIX + id).offsetLeft - 124, eval(EDITOR_BOTTOM_TOOLBAR_PREFIX + id).offsetTop + eval(EDITOR_BOTTOM_TOOLBAR_PREFIX + id).offsetHeight - 124);
//		}
//	}

	function EditorOnEndInsertSmiley(image, id)
	{
	    if (!EditorValidateMode(id)) {
		return;
	    }
	    var imgTag = '<img src="' + image + '">';
	    var editor = editorMap[id];
	    var bodyRange = eval(EDITOR_COMPOSITION_PREFIX + id).document.body.createTextRange();
	    if (bodyRange.inRange(editor.selectionRange)) {
		editor.selectionRange.pasteHTML(imgTag);
		eval(EDITOR_COMPOSITION_PREFIX + id).focus();
	    }
	    else {
		eval(EDITOR_COMPOSITION_PREFIX + id).document.body.innerHTML += imgTag;
		editor.selectionRange.collapse(false);
		editor.selectionRange.select();
	    }
	}

	function EditorOnParagraph(id, select)
	{
		EditorFormat(id, "formatBlock", select[select.selectedIndex].value);
		select.selectedIndex = 0;
	}

	function EditorOnFont(id, select)
	{
		EditorFormat(id, "fontname", select[select.selectedIndex].value);
		select.selectedIndex = 0;
	}

	function EditorOnSize(id, select)
	{
		EditorFormat(id, "fontsize", select[select.selectedIndex].value);
		select.selectedIndex = 0;
	}

	function EditorOnViewHTMLSource(id, textMode)
	{
		var editor = editorMap[id];
		editor.textMode = textMode;
		if (editor.textMode) {
			EditorCleanHTML(id);
			EditorCleanHTML(id);
			eval(EDITOR_COMPOSITION_PREFIX + id).document.body.innerText = eval(EDITOR_COMPOSITION_PREFIX + id).document.body.innerHTML;
		}
		else {
			eval(EDITOR_COMPOSITION_PREFIX + id).document.body.innerHTML = eval(EDITOR_COMPOSITION_PREFIX + id).document.body.innerText;
		}
		eval(EDITOR_COMPOSITION_PREFIX + id).focus();
	}

	function EditorOnClick(id)
	{
//		eval(EDITOR_IMAGE_CHOOSER_PREFIX + id).Hide();
	}
		
	function EditorValidateMode(id)
	{
		var editor = editorMap[id];
		if (!editor.textMode) {
			return true;
		}
		alert("Please uncheck the \"View HTML Source\" checkbox to use the toolbars.");
		eval(EDITOR_COMPOSITION_PREFIX + id).focus();
		return false;
	}

	function EditorFormat(id, what, opt)
	{
		if (!EditorValidateMode(id)) {
			return;
		}
		if (opt == "removeFormat") {
			what = opt;
			opt = null;
		}
		if (opt == null) {
			eval(EDITOR_COMPOSITION_PREFIX + id).document.execCommand(what);
		}
		else {
			eval(EDITOR_COMPOSITION_PREFIX + id).document.execCommand(what, "", opt);
		}
	}

	function EditorCleanHTML(id)
	{
		var fonts = eval(EDITOR_COMPOSITION_PREFIX + id).document.body.all.tags("FONT");
		for (var i = fonts.length - 1; i >= 0; i--) {
			var font = fonts[i];
			if (font.style.backgroundColor == "#ffffff") {
				font.outerHTML = font.innerHTML;
			}
		}
	}

	function EditorGetElement(tagName, start)
	{
		while (start && start.tagName != tagName) {
			start = start.parentElement;
		}
		return start;
	}

function edittext(form, input, text) {
	win = window.open("../editorhtml/Compose.php?text="+text+"&form="+form+"&input="+input, "popup", 'toolbar = no, status =no, width=585, height=630');
//	win.document.write("<html><head><title></title></head><body topmargin=0 leftmargin=4 rightmargin=4 bottommargin= alink=#0000ff vlink=#0000ff bgcolor=#C3C3C3>");
//	win.document.write("<script language=\"JavaScript\" src=\"/admin/cms/js/editorHTML.js\"><\/script>");

//	win.document.write("<script>");
//	win.document.write("function Exit() {");
//	win.document.write("	window.close();");
//	win.document.write("	return false;}");
//	win.document.write("function SetVals() {");
//	win.document.write("	window.opener.document."+form+"."+input+".value = editor.GetHTML();");
//	win.document.write("	window.close();");
//	win.document.write("	return false;}");
//	win.document.write("function GetVals() {");
//	win.document.write("	document.all.plainmsg.innerHTML= document.form.Body.value;");
//	win.document.write("	editor.SetHTML(document.all.plainmsg.innerHTML);}");
//	win.document.write("<\/script>");
//	win.document.write("<div style='position:relative;top=0px;left=0px;height:200' width=100%>");
//	win.document.write("<div id=\"plainmsg\" style='visibility:hidden;position:absolute;top:0px;left:0px'></div>");
//	win.document.write("<script>");
//	win.document.write("	var idGenerator = new IDGenerator(0);");
//	win.document.write("	var editor = new Editor(idGenerator);");
//	win.document.write("	editor.Instantiate();");
//	win.document.write("<\/script>");
//	win.document.write("</div>");
//	win.document.write("<input type=hidden name=Format id=Format value=\"html\">");
//	win.document.write("<input type=button name=SEND value=\"Grabar\" onclick=\"SetVals()\">");
//	win.document.write("<input type=submit name=CAN value=\"Salir\" onclick=\"Exit()\">");
//	win.document.write("</body>");
//	win.document.write("</html>");
//	win.document.close();
}
