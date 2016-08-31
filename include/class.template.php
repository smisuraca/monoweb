<?

function is_hash($var)
{
   return is_array($var) && sizeof($var) > 0 && array_keys($var)!==range(0,sizeof($var)-1);
}

class Template
{
	var $FileRoot = '';
	var $Template = '';
	var $FileName = '';

	function Template($strTemplate)
	{
		$this->Template = $strTemplate;
	}

	function Open($FileName)
	{
		$this->FileName = $FileName;

		$fd=fopen($this->FileRoot.$this->FileName,"r");
		if(!$fd)
		{
			$this->Template="<!--[Template] Can't read '$FileName'!-->\n";
			return false;
		}
		$this->Template=fread($fd,filesize($this->FileRoot.$this->FileName));
		fclose($fd);
		
		$this->SSI();

		return true;
	}


	function setFileRoot($FileRoot)
	{
		$this->FileRoot = $FileRoot;
	}


	function setTemplate($strTemplate)
	{
		$this->Template = $strTemplate;
	}


	function addTemplate($objTemplate)
	{
		$this->Template .= $objTemplate->Template;
	}


	function setVar($VarName, $VarValue)
	{
		$this->Template = str_replace($VarName, $VarValue, $this->Template);
	}

	function setVars(&$vars, $prepend)
	{
		if (is_hash($vars))
		{
			foreach($vars as $key => $value)
			{
				if (is_array($value))
					$this->setVars($value, $prepend.'.'.$key);
				else
					$this->setVar('{'.$prepend.'.'.$key.'}', $value);
			}
		}
		elseif (is_array($vars))
		{
			$rstblock = '';
			$strblock = $this->getBlock("$prepend.Row", "<!-- BLOCK $prepend.Row -->");
			if ($strblock)
			{
				$idx = 0;
				$total = sizeof($vars);
				foreach($vars as $key => $value)
				{
					$tpl = new Template($strblock);

					$tpl->setVar('{'.$prepend.'.__idx}', $idx);
					$tpl->setVar('{'.$prepend.'.__parity}', ($idx % 2 == 0) ? 0 : 1);
					if ($idx == 0)
						$tpl->setvar('{'.$prepend.'.__state}', "FIRST");
					elseif ($idx == $total-1)
						$tpl->setvar('{'.$prepend.'.__state}', "LAST");
					else
						$tpl->setvar('{'.$prepend.'.__state}', "BODY");

					if (is_array($value))
						$tpl->setVars($value, $prepend);
					else
						$tpl->setVar('{'.$prepend.'}', $value);

					$rstblock .= $tpl->Template;
					$idx++;
				}
			}

			$this->setVar("<!-- BLOCK $prepend.Row -->", $rstblock);
		}
		return 1;
	}

	function getBlock($BlockName, $VarName)
	{
		$BeginPos = 0;
		$EndPos   = 0;
		$BeginStr = '';
		$EndStr   = '';
		$BeginLen = 0;
		$EndLen   = 0;
		$strBlock = '';

		$BeginStr = "<!-- BEGIN $BlockName -->";
		$BeginLen = strlen($BeginStr);
		$BeginPos = strpos($this->Template, $BeginStr, 0);
		if (!($BeginPos===false)) {
			$EndStr = "<!-- END $BlockName -->";
			$EndLen = strlen($EndStr);
			$EndPos = strpos($this->Template, $EndStr, $BeginPos);
			if (!($EndPos===false)) {
				$strBlock = substr($this->Template, $BeginPos, $EndPos + $EndLen - $BeginPos);
				$this->setVar($strBlock, $VarName);
				$tplBlock = new Template('');
				$tplBlock->setTemplate($strBlock);
				$tplBlock->setVar($BeginStr, '');
				$tplBlock->setVar($EndStr, '');

				return $tplBlock->Template;
			}
		}
	}

	function isTag($TagName)
	{
		if (strpos($this->Template, $TagName, 0)===false)
			return 0;
		else
			return 1;
	}

	function ApplyDefined($vars)
	{
		foreach($vars as $key => $value)
		{
			while ($this->isTag("<!-- BEGIN IFDEF " . $key . " -->") && $value == 0)
				$this->getBlock("IFDEF " . $key , "");

			while ($this->isTag("<!-- BEGIN IFNDEF " . $key . " -->") && $value == 1)
				$this->getBlock("IFNDEF " . $key , "");
		}
	}

	function SSI()
	{
		$tplmodulo = new Template("");
		$tplmodulo->setFileRoot($this->FileRoot);
	
		$count = 0;
		while (preg_match('/<!--#include virtual="([^\"]+)" -->/i', $this->Template, $regs) && $count < 50)
		{
			$tplmodulo->Open("/$regs[1]");
			$this->setVar($regs[0], $tplmodulo->Template);
			$count++;
		}
	}
}

?>
