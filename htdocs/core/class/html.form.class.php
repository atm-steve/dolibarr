<?php
/* Copyright (c) 2002-2007  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2004       Benoit Mortier          <benoit.mortier@opensides.be>
 * Copyright (C) 2004       Sebastien Di Cintio     <sdicintio@ressource-toi.org>
 * Copyright (C) 2004       Eric Seigne             <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2017  Regis Houssin           <regis.houssin@capnetworks.com>
 * Copyright (C) 2006       Andre Cianfarani        <acianfa@free.fr>
 * Copyright (C) 2006       Marc Barilley/Ocebo     <marc@ocebo.com>
 * Copyright (C) 2007       Franky Van Liedekerke   <franky.van.liedekerker@telenet.be>
 * Copyright (C) 2007       Patrick Raguin          <patrick.raguin@gmail.com>
 * Copyright (C) 2010       Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2010-2014  Philippe Grand          <philippe.grand@atoo-net.com>
 * Copyright (C) 2011       Herve Prot              <herve.prot@symeos.com>
 * Copyright (C) 2012-2016  Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2012       Cedric Salvador         <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2015  Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2014       Alexandre Spangaro      <aspangaro.dolibarr@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/class/html.form.class.php
 *  \ingroup    core
 *	\brief      File of class with all html predefined components
 */


/**
 *	Class to manage generation of HTML components
 *	Only common components must be here.
 *
 *  TODO Merge all function load_cache_* and loadCache* (except load_cache_vatrates) into one generic function loadCacheTable
 */
class Form
{
    var $db;
    var $error;
    var $num;

    // Cache arrays
    var $cache_types_paiements=array();
    var $cache_conditions_paiements=array();
    var $cache_availability=array();
    var $cache_demand_reason=array();
    var $cache_types_fees=array();
    var $cache_vatrates=array();


    /**
     * Constructor
     *
     * @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Output key field for an editable field
     *
     * @param   string	$text			Text of label or key to translate
     * @param   string	$htmlname		Name of select field ('edit' prefix will be added)
     * @param   string	$preselected    Value to show/edit (not used in this function)
     * @param	object	$object			Object
     * @param	boolean	$perm			Permission to allow button to edit parameter. Set it to 0 to have a not edited field.
     * @param	string	$typeofdata		Type of data ('string' by default, 'email', 'amount:99', 'numeric:99', 'text' or 'textarea:rows:cols', 'datepicker' ('day' do not work, don't know why), 'ckeditor:dolibarr_zzz:width:height:savemethod:1:rows:cols', 'select;xxx[:class]'...)
     * @param	string	$moreparam		More param to add on a href URL.
     * @param   int     $fieldrequired  1 if we want to show field as mandatory using the "fieldrequired" CSS.
     * @param   int     $notabletag     1=Do not output table tags but output a ':', 2=Do not output table tags and no ':', 3=Do not output table tags but output a ' '
     * @return	string					HTML edit field
     */
    function editfieldkey($text, $htmlname, $preselected, $object, $perm, $typeofdata='string', $moreparam='', $fieldrequired=0, $notabletag=0)
    {
        global $conf,$langs;

        $ret='';

        // TODO change for compatibility
        if (! empty($conf->global->MAIN_USE_JQUERY_JEDITABLE) && ! preg_match('/^select;/',$typeofdata))
        {
            if (! empty($perm))
            {
                $tmp=explode(':',$typeofdata);
                $ret.= '<div class="editkey_'.$tmp[0].(! empty($tmp[1]) ? ' '.$tmp[1] : '').'" id="'.$htmlname.'">';
	            if ($fieldrequired) $ret.='<span class="fieldrequired">';
                $ret.= $langs->trans($text);
	            if ($fieldrequired) $ret.='</span>';
                $ret.= '</div>'."\n";
            }
            else
            {
	            if ($fieldrequired) $ret.='<span class="fieldrequired">';
                $ret.= $langs->trans($text);
	            if ($fieldrequired) $ret.='</span>';
            }
        }
        else
		{
            if (empty($notabletag) && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='<table class="nobordernopadding" width="100%"><tr><td class="nowrap">';
	        if ($fieldrequired) $ret.='<span class="fieldrequired">';
            $ret.=$langs->trans($text);
	        if ($fieldrequired) $ret.='</span>';
	        if (! empty($notabletag)) $ret.=' ';
            if (empty($notabletag) && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='</td>';
            if (empty($notabletag) && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='<td align="right">';
            if ($htmlname && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='<a href="'.$_SERVER["PHP_SELF"].'?action=edit'.$htmlname.'&amp;id='.$object->id.$moreparam.'">'.img_edit($langs->trans('Edit'), ($notabletag ? 0 : 1)).'</a>';
	        if (! empty($notabletag) && $notabletag == 1) $ret.=' : ';
	        if (! empty($notabletag) && $notabletag == 3) $ret.=' ';
            if (empty($notabletag) && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='</td>';
            if (empty($notabletag) && GETPOST('action','aZ09') != 'edit'.$htmlname && $perm) $ret.='</tr></table>';
        }

        return $ret;
    }

    /**
     * Output val field for an editable field
     *
     * @param	string	$text			Text of label (not used in this function)
     * @param	string	$htmlname		Name of select field
     * @param	string	$value			Value to show/edit
     * @param	object	$object			Object
     * @param	boolean	$perm			Permission to allow button to edit parameter
     * @param	string	$typeofdata		Type of data ('string' by default, 'email', 'amount:99', 'numeric:99', 'text' or 'textarea:rows:cols', 'datepicker' ('day' do not work, don't know why), 'dayhour' or 'datepickerhour', 'ckeditor:dolibarr_zzz:width:height:savemethod:toolbarstartexpanded:rows:cols', 'select:xxx'...)
     * @param	string	$editvalue		When in edit mode, use this value as $value instead of value (for example, you can provide here a formated price instead of value). Use '' to use same than $value
     * @param	object	$extObject		External object
     * @param	mixed	$custommsg		String or Array of custom messages : eg array('success' => 'MyMessage', 'error' => 'MyMessage')
     * @param	string	$moreparam		More param to add on a href URL
     * @param   int     $notabletag     Do no output table tags
     * @return  string					HTML edit field
     */
    function editfieldval($text, $htmlname, $value, $object, $perm, $typeofdata='string', $editvalue='', $extObject=null, $custommsg=null, $moreparam='', $notabletag=0)
    {
        global $conf,$langs,$db;

        $ret='';

        // Check parameters
        if (empty($typeofdata)) return 'ErrorBadParameter';

        // When option to edit inline is activated
        if (! empty($conf->global->MAIN_USE_JQUERY_JEDITABLE) && ! preg_match('/^select;|datehourpicker/',$typeofdata)) // TODO add jquery timepicker
        {
            $ret.=$this->editInPlace($object, $value, $htmlname, $perm, $typeofdata, $editvalue, $extObject, $custommsg);
        }
        else
        {
            if (GETPOST('action','aZ09') == 'edit'.$htmlname)
            {
                $ret.="\n";
                $ret.='<form method="post" action="'.$_SERVER["PHP_SELF"].($moreparam?'?'.$moreparam:'').'">';
                $ret.='<input type="hidden" name="action" value="set'.$htmlname.'">';
                $ret.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
                $ret.='<input type="hidden" name="id" value="'.$object->id.'">';
                if (empty($notabletag)) $ret.='<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
                if (empty($notabletag)) $ret.='<tr><td>';
                if (preg_match('/^(string|email)/',$typeofdata))
                {
                    $tmp=explode(':',$typeofdata);
                    $ret.='<input type="text" id="'.$htmlname.'" name="'.$htmlname.'" value="'.($editvalue?$editvalue:$value).'"'.($tmp[1]?' size="'.$tmp[1].'"':'').'>';
                }
                else if (preg_match('/^(numeric|amount)/',$typeofdata))
                {
                    $tmp=explode(':',$typeofdata);
                    $valuetoshow=price2num($editvalue?$editvalue:$value);
                    $ret.='<input type="text" id="'.$htmlname.'" name="'.$htmlname.'" value="'.($valuetoshow!=''?price($valuetoshow):'').'"'.($tmp[1]?' size="'.$tmp[1].'"':'').'>';
                }
                else if (preg_match('/^text/',$typeofdata) || preg_match('/^note/',$typeofdata))
                {
                    $tmp=explode(':',$typeofdata);
                    $cols=$tmp[2];
                    $morealt='';
                    if (preg_match('/%/',$cols))
                    {
                        $morealt=' style="width: '.$cols.'"';
                        $cols='';
                    }
                    $ret.='<textarea id="'.$htmlname.'" name="'.$htmlname.'" wrap="soft" rows="'.($tmp[1]?$tmp[1]:'20').'"'.($cols?' cols="'.$cols.'"':'').$morealt.'">'.($editvalue?$editvalue:$value).'</textarea>';
                }
                else if ($typeofdata == 'day' || $typeofdata == 'datepicker')
                {
                    $ret.=$this->select_date($value,$htmlname,0,0,1,'form'.$htmlname,1,0,1);
                }
                else if ($typeofdata == 'dayhour' || $typeofdata == 'datehourpicker')
                {
                    $ret.=$this->select_date($value,$htmlname,1,1,1,'form'.$htmlname,1,0,1);
                }
                else if (preg_match('/^select;/',$typeofdata))
                {
                     $arraydata=explode(',',preg_replace('/^select;/','',$typeofdata));
                     foreach($arraydata as $val)
                     {
                         $tmp=explode(':',$val);
                         $arraylist[$tmp[0]]=$tmp[1];
                     }
                     $ret.=$this->selectarray($htmlname,$arraylist,$value);
                }
                else if (preg_match('/^ckeditor/',$typeofdata))
                {
                    $tmp=explode(':',$typeofdata);
                    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
                    $doleditor=new DolEditor($htmlname, ($editvalue?$editvalue:$value), ($tmp[2]?$tmp[2]:''), ($tmp[3]?$tmp[3]:'100'), ($tmp[1]?$tmp[1]:'dolibarr_notes'), 'In', ($tmp[5]?$tmp[5]:0), true, true, ($tmp[6]?$tmp[6]:'20'), ($tmp[7]?$tmp[7]:'100'));
                    $ret.=$doleditor->Create(1);
                }
                if (empty($notabletag)) $ret.='</td>';

                if (empty($notabletag)) $ret.='<td align="left">';
                //else $ret.='<div class="clearboth"></div>';
               	$ret.='<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="modify" value="'.$langs->trans("Modify").'">';
               	if (preg_match('/ckeditor|textarea/',$typeofdata) && empty($notabletag)) $ret.='<br>'."\n";
               	$ret.='<input type="submit" class="button'.(empty($notabletag)?'':' ').'" name="cancel" value="'.$langs->trans("Cancel").'">';
               	if (empty($notabletag)) $ret.='</td>';

               	if (empty($notabletag)) $ret.='</tr></table>'."\n";
                $ret.='</form>'."\n";
            }
            else
			{
				if (preg_match('/^(email)/',$typeofdata))              $ret.=dol_print_email($value,0,0,0,0,1);
                elseif (preg_match('/^(amount|numeric)/',$typeofdata)) $ret.=($value != '' ? price($value,'',$langs,0,-1,-1,$conf->currency) : '');
                elseif (preg_match('/^text/',$typeofdata) || preg_match('/^note/',$typeofdata))  $ret.=dol_htmlentitiesbr($value);
                elseif ($typeofdata == 'day' || $typeofdata == 'datepicker') $ret.=dol_print_date($value,'day');
                elseif ($typeofdata == 'dayhour' || $typeofdata == 'datehourpicker') $ret.=dol_print_date($value,'dayhour');
                else if (preg_match('/^select;/',$typeofdata))
                {
                    $arraydata=explode(',',preg_replace('/^select;/','',$typeofdata));
                    foreach($arraydata as $val)
                    {
                        $tmp=explode(':',$val);
                        $arraylist[$tmp[0]]=$tmp[1];
                    }
                    $ret.=$arraylist[$value];
                }
                else if (preg_match('/^ckeditor/',$typeofdata))
                {
                    $tmpcontent=dol_htmlentitiesbr($value);
                    if (! empty($conf->global->MAIN_DISABLE_NOTES_TAB))
                    {
                        $firstline=preg_replace('/<br>.*/','',$tmpcontent);
                        $firstline=preg_replace('/[\n\r].*/','',$firstline);
                        $tmpcontent=$firstline.((strlen($firstline) != strlen($tmpcontent))?'...':'');
                    }
                    $ret.=$tmpcontent;
                }
                else $ret.=$value;
            }
        }
        return $ret;
    }

    /**
     * Output edit in place form
     *
     * @param	object	$object			Object
     * @param	string	$value			Value to show/edit
     * @param	string	$htmlname		DIV ID (field name)
     * @param	int		$condition		Condition to edit
     * @param	string	$inputType		Type of input ('string', 'numeric', 'datepicker' ('day' do not work, don't know why), 'textarea:rows:cols', 'ckeditor:dolibarr_zzz:width:height:?:1:rows:cols', 'select:xxx')
     * @param	string	$editvalue		When in edit mode, use this value as $value instead of value
     * @param	object	$extObject		External object
     * @param	mixed	$custommsg		String or Array of custom messages : eg array('success' => 'MyMessage', 'error' => 'MyMessage')
     * @return	string   		      	HTML edit in place
     */
    private function editInPlace($object, $value, $htmlname, $condition, $inputType='textarea', $editvalue=null, $extObject=null, $custommsg=null)
    {
        global $conf;

        $out='';

        // Check parameters
        if ($inputType == 'textarea') $value = dol_nl2br($value);
        else if (preg_match('/^numeric/',$inputType)) $value = price($value);
        else if ($inputType == 'day' || $inputType == 'datepicker') $value = dol_print_date($value, 'day');

        if ($condition)
        {
            $element		= false;
            $table_element	= false;
            $fk_element		= false;
            $loadmethod		= false;
            $savemethod		= false;
            $ext_element	= false;
            $button_only	= false;
            $inputOption    = '';

            if (is_object($object))
            {
                $element = $object->element;
                $table_element = $object->table_element;
                $fk_element = $object->id;
            }

            if (is_object($extObject))
            {
                $ext_element = $extObject->element;
            }

            if (preg_match('/^(string|email|numeric)/',$inputType))
            {
                $tmp=explode(':',$inputType);
                $inputType=$tmp[0];
                if (! empty($tmp[1])) $inputOption=$tmp[1];
                if (! empty($tmp[2])) $savemethod=$tmp[2];
				$out.= '<input id="width_'.$htmlname.'" value="'.$inputOption.'" type="hidden"/>'."\n";
            }
            else if ((preg_match('/^day$/',$inputType)) || (preg_match('/^datepicker/',$inputType)) || (preg_match('/^datehourpicker/',$inputType)))
            {
                $tmp=explode(':',$inputType);
                $inputType=$tmp[0];
                if (! empty($tmp[1])) $inputOption=$tmp[1];
                if (! empty($tmp[2])) $savemethod=$tmp[2];

                $out.= '<input id="timestamp" type="hidden"/>'."\n"; // Use for timestamp format
            }
            else if (preg_match('/^(select|autocomplete)/',$inputType))
            {
                $tmp=explode(':',$inputType);
                $inputType=$tmp[0]; $loadmethod=$tmp[1];
                if (! empty($tmp[2])) $savemethod=$tmp[2];
                if (! empty($tmp[3])) $button_only=true;
            }
            else if (preg_match('/^textarea/',$inputType))
            {
            	$tmp=explode(':',$inputType);
            	$inputType=$tmp[0];
            	$rows=(empty($tmp[1])?'8':$tmp[1]);
            	$cols=(empty($tmp[2])?'80':$tmp[2]);
            }
            else if (preg_match('/^ckeditor/',$inputType))
            {
                $tmp=explode(':',$inputType);
                $inputType=$tmp[0]; $toolbar=$tmp[1];
                if (! empty($tmp[2])) $width=$tmp[2];
                if (! empty($tmp[3])) $heigth=$tmp[3];
                if (! empty($tmp[4])) $savemethod=$tmp[4];

                if (! empty($conf->fckeditor->enabled))
                {
                    $out.= '<input id="ckeditor_toolbar" value="'.$toolbar.'" type="hidden"/>'."\n";
                }
                else
                {
                    $inputType = 'textarea';
                }
            }

            $out.= '<input id="element_'.$htmlname.'" value="'.$element.'" type="hidden"/>'."\n";
            $out.= '<input id="table_element_'.$htmlname.'" value="'.$table_element.'" type="hidden"/>'."\n";
            $out.= '<input id="fk_element_'.$htmlname.'" value="'.$fk_element.'" type="hidden"/>'."\n";
            $out.= '<input id="loadmethod_'.$htmlname.'" value="'.$loadmethod.'" type="hidden"/>'."\n";
            if (! empty($savemethod))	$out.= '<input id="savemethod_'.$htmlname.'" value="'.$savemethod.'" type="hidden"/>'."\n";
            if (! empty($ext_element))	$out.= '<input id="ext_element_'.$htmlname.'" value="'.$ext_element.'" type="hidden"/>'."\n";
            if (! empty($custommsg))
            {
            	if (is_array($custommsg))
            	{
            		if (!empty($custommsg['success']))
            			$out.= '<input id="successmsg_'.$htmlname.'" value="'.$custommsg['success'].'" type="hidden"/>'."\n";
            		if (!empty($custommsg['error']))
            			$out.= '<input id="errormsg_'.$htmlname.'" value="'.$custommsg['error'].'" type="hidden"/>'."\n";
            	}
            	else
            		$out.= '<input id="successmsg_'.$htmlname.'" value="'.$custommsg.'" type="hidden"/>'."\n";
            }
            if ($inputType == 'textarea') {
            	$out.= '<input id="textarea_'.$htmlname.'_rows" value="'.$rows.'" type="hidden"/>'."\n";
            	$out.= '<input id="textarea_'.$htmlname.'_cols" value="'.$cols.'" type="hidden"/>'."\n";
            }
            $out.= '<span id="viewval_'.$htmlname.'" class="viewval_'.$inputType.($button_only ? ' inactive' : ' active').'">'.$value.'</span>'."\n";
            $out.= '<span id="editval_'.$htmlname.'" class="editval_'.$inputType.($button_only ? ' inactive' : ' active').' hideobject">'.(! empty($editvalue) ? $editvalue : $value).'</span>'."\n";
        }
        else
        {
            $out = $value;
        }

        return $out;
    }

    /**
     *	Show a text and picto with tooltip on text or picto.
     *  Can be called by an instancied $form->textwithtooltip or by a static call Form::textwithtooltip
     *
     *	@param	string		$text				Text to show
     *	@param	string		$htmltext			HTML content of tooltip. Must be HTML/UTF8 encoded.
     *	@param	int			$tooltipon			1=tooltip on text, 2=tooltip on image, 3=tooltip sur les 2
     *	@param	int			$direction			-1=image is before, 0=no image, 1=image is after
     *	@param	string		$img				Html code for image (use img_xxx() function to get it)
     *	@param	string		$extracss			Add a CSS style to td tags
     *	@param	int			$notabs				0=Include table and tr tags, 1=Do not include table and tr tags, 2=use div, 3=use span
     *	@param	string		$incbefore			Include code before the text
     *	@param	int			$noencodehtmltext	Do not encode into html entity the htmltext
     *  @param  string      $tooltiptrigger     ''=Tooltip on hover, 'abc'=Tooltip on click (abc is a unique key)
     *	@return	string							Code html du tooltip (texte+picto)
     *	@see	Use function textwithpicto if you can.
     *  TODO Move this as static as soon as everybody use textwithpicto or @Form::textwithtooltip
     */
    function textwithtooltip($text, $htmltext, $tooltipon = 1, $direction = 0, $img = '', $extracss = '', $notabs = 2, $incbefore = '', $noencodehtmltext = 0, $tooltiptrigger='')
    {
        global $conf;

        if ($incbefore) $text = $incbefore.$text;
        if (! $htmltext) return $text;

        $tag='td';
        if ($notabs == 2) $tag='div';
        if ($notabs == 3) $tag='span';
        // Sanitize tooltip
        $htmltext=str_replace("\\","\\\\",$htmltext);
        $htmltext=str_replace("\r","",$htmltext);
        $htmltext=str_replace("\n","",$htmltext);

        $extrastyle='';
        if ($direction < 0) { $extracss=($extracss?$extracss.' ':'').'inline-block'; $extrastyle='padding: 0px; padding-left: 3px !important;'; }
        if ($direction > 0) { $extracss=($extracss?$extracss.' ':'').'inline-block'; $extrastyle='padding: 0px; padding-right: 3px !important;'; }

        $classfortooltip='classfortooltip';

        $s='';$textfordialog='';

        $htmltext=str_replace('"',"&quot;",$htmltext);
        if ($tooltiptrigger != '')
        {
            $classfortooltip='classfortooltiponclick';
            $textfordialog.='<div style="display: none;" id="idfortooltiponclick_'.$tooltiptrigger.'" class="classfortooltiponclicktext">'.$htmltext.'</div>';
        }
        if ($tooltipon == 2 || $tooltipon == 3)
        {
            $paramfortooltipimg=' class="'.$classfortooltip.' inline-block'.($extracss?' '.$extracss:'').'" style="padding: 0px;'.($extrastyle?' '.$extrastyle:'').'"';
            if ($tooltiptrigger == '') $paramfortooltipimg.=' title="'.($noencodehtmltext?$htmltext:dol_escape_htmltag($htmltext,1)).'"'; // Attribut to put on img tag to store tooltip
            else $paramfortooltipimg.=' dolid="'.$tooltiptrigger.'"';
        }
        else $paramfortooltipimg =($extracss?' class="'.$extracss.'"':'').($extrastyle?' style="'.$extrastyle.'"':''); // Attribut to put on td text tag
        if ($tooltipon == 1 || $tooltipon == 3)
        {
            $paramfortooltiptd=' class="'.($tooltipon == 3 ? 'cursorpointer ' : '').$classfortooltip.' inline-block'.($extracss?' '.$extracss:'').'" style="padding: 0px;'.($extrastyle?' '.$extrastyle:'').'" ';
            if ($tooltiptrigger == '') $paramfortooltiptd.=' title="'.($noencodehtmltext?$htmltext:dol_escape_htmltag($htmltext,1)).'"'; // Attribut to put on td tag to store tooltip
            else $paramfortooltiptd.=' dolid="'.$tooltiptrigger.'"';
        }
        else $paramfortooltiptd =($extracss?' class="'.$extracss.'"':'').($extrastyle?' style="'.$extrastyle.'"':''); // Attribut to put on td text tag
        if (empty($notabs)) $s.='<table class="nobordernopadding" summary=""><tr style="height: auto;">';
        elseif ($notabs == 2) $s.='<div class="inline-block">';
        // Define value if value is before
        if ($direction < 0) {
            $s.='<'.$tag.$paramfortooltipimg;
            if ($tag == 'td') {
                $s .= ' valign="top" width="14"';
            }
            $s.= '>'.$textfordialog.$img.'</'.$tag.'>';
        }
        // Use another method to help avoid having a space in value in order to use this value with jquery
        // Define label
        if ((string) $text != '') $s.='<'.$tag.$paramfortooltiptd.'>'.$text.'</'.$tag.'>';
        // Define value if value is after
        if ($direction > 0) {
            $s.='<'.$tag.$paramfortooltipimg;
            if ($tag == 'td') $s .= ' valign="middle" width="14"';
            $s.= '>'.$textfordialog.$img.'</'.$tag.'>';
        }
        if (empty($notabs)) $s.='</tr></table>';
		elseif ($notabs == 2) $s.='</div>';

        return $s;
    }

    /**
     *	Show a text with a picto and a tooltip on picto
     *
     *	@param	string	$text				Text to show
     *	@param  string	$htmltext	     	Content of tooltip
     *	@param	int		$direction			1=Icon is after text, -1=Icon is before text, 0=no icon
     * 	@param	string	$type				Type of picto ('info', 'help', 'warning', 'superadmin', 'mypicto@mymodule', ...) or image filepath
     *  @param  string	$extracss           Add a CSS style to td, div or span tag
     *  @param  int		$noencodehtmltext   Do not encode into html entity the htmltext
     *  @param	int		$notabs				0=Include table and tr tags, 1=Do not include table and tr tags, 2=use div, 3=use span
     *  @param  string  $tooltiptrigger     ''=Tooltip on hover, 'abc'=Tooltip on click (abc is a unique key)
     * 	@return	string						HTML code of text, picto, tooltip
     */
    function textwithpicto($text, $htmltext, $direction = 1, $type = 'help', $extracss = '', $noencodehtmltext = 0, $notabs = 2, $tooltiptrigger='')
    {
        global $conf, $langs;

        $alt = '';
        if ($tooltiptrigger) $alt=$langs->trans("ClickToShowHelp");

        //For backwards compatibility
        if ($type == '0') $type = 'info';
        elseif ($type == '1') $type = 'help';

        // If info or help with no javascript, show only text
        if (empty($conf->use_javascript_ajax))
        {
            if ($type == 'info' || $type == 'help')	return $text;
            else
            {
                $alt = $htmltext;
                $htmltext = '';
            }
        }

        // If info or help with smartphone, show only text (tooltip can't works)
        if (! empty($conf->dol_no_mouse_hover))
        {
            if ($type == 'info' || $type == 'help') return $text;
        }

        if ($type == 'info') $img = img_help(0, $alt);
        elseif ($type == 'help') $img = img_help(($tooltiptrigger != '' ? 2 : 1), $alt);
        elseif ($type == 'superadmin') $img = img_picto($alt, 'redstar');
        elseif ($type == 'admin') $img = img_picto($alt, 'star');
        elseif ($type == 'warning') $img = img_warning($alt);
		else $img = img_picto($alt, $type);

        return $this->textwithtooltip($text, $htmltext, ($tooltiptrigger?3:2), $direction, $img, $extracss, $notabs, '', $noencodehtmltext, $tooltiptrigger);
    }

    /**
     * Generate select HTML to choose massaction
     *
     * @param	string	$selected		Value auto selected when at least one record is selected. Not a preselected value. Use '0' by default.
     * @param	int		$arrayofaction	array('code'=>'label', ...). The code is the key stored into the GETPOST('massaction') when submitting action.
     * @param   int     $alwaysvisible  1=select button always visible
     * @return	string					Select list
     */
    function selectMassAction($selected, $arrayofaction, $alwaysvisible=0)
    {
    	global $conf,$langs,$hookmanager;

    	if (count($arrayofaction) == 0) return;

    	$disabled=0;
    	$ret='<div class="centpercent center">';
    	$ret.='<select data-role="none" class="flat'.(empty($conf->use_javascript_ajax)?'':' hideobject').' massaction massactionselect" name="massaction"'.($disabled?' disabled="disabled"':'').'>';

        // Complete list with data from external modules. THe module can use $_SERVER['PHP_SELF'] to know on which page we are, or use the $parameters['currentcontext'] completed by executeHooks.
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('addMoreMassActions',$parameters);    // Note that $action and $object may have been modified by hook
        if (empty($reshook))
        {
        	$ret.='<option value="0"'.($disabled?' disabled="disabled"':'').'>-- '.$langs->trans("SelectAction").' --</option>';
        	foreach($arrayofaction as $code => $label)
        	{
        		$ret.='<option value="'.$code.'"'.($disabled?' disabled="disabled"':'').'>'.$label.'</option>';
        	}
        }
        $ret.=$hookmanager->resPrint;

    	$ret.='</select>';
    	// Warning: if you set submit button to disabled, post using 'Enter' will no more work.
    	$ret.='<input type="submit" data-role="none" name="confirmmassaction" class="button'.(empty($conf->use_javascript_ajax)?'':' hideobject').' massaction massactionconfirmed" value="'.dol_escape_htmltag($langs->trans("Confirm")).'">';
    	$ret.='</div>';

    	if (! empty($conf->use_javascript_ajax))
    	{
        	$ret.='<!-- JS CODE TO ENABLE mass action select -->
    		<script type="text/javascript">
        		function initCheckForSelect()
        		{
        			atleastoneselected=0;
    	    		jQuery(".checkforselect").each(function( index ) {
    	  				/* console.log( index + ": " + $( this ).text() ); */
    	  				if ($(this).is(\':checked\')) atleastoneselected++;
    	  			});
    	  			if (atleastoneselected || '.$alwaysvisible.')
    	  			{
    	  				jQuery(".massaction").show();
        			    '.($selected ? 'if (atleastoneselected) jQuery(".massactionselect").val("'.$selected.'");' : '').'
        			    '.($selected ? 'if (! atleastoneselected) jQuery(".massactionselect").val("0");' : '').'
    	  			}
    	  			else
    	  			{
    	  				jQuery(".massaction").hide();
    	            }
        		}

        	jQuery(document).ready(function () {
        		initCheckForSelect();
        		jQuery(".checkforselect").click(function() {
        			initCheckForSelect();
    	  		});
    	  		jQuery(".massactionselect").change(function() {
        			var massaction = $( this ).val();
        			var urlform = $( this ).closest("form").attr("action").replace("#show_files","");
        			if (massaction == "builddoc")
                    {
                        urlform = urlform + "#show_files";
    	            }
        			$( this ).closest("form").attr("action", urlform);
                    console.log("we select a mass action "+massaction+" - "+urlform);
        	        /* Warning: if you set submit button to disabled, post using Enter will no more work
        			if ($(this).val() != \'0\')
    	  			{
    	  				jQuery(".massactionconfirmed").prop(\'disabled\', false);
    	  			}
    	  			else
    	  			{
    	  				jQuery(".massactionconfirmed").prop(\'disabled\', true);
    	  			}
        	        */
    	        });
        	});
    		</script>
        	';
    	}

    	return $ret;
    }

    /**
     *  Return combo list of activated countries, into language of user
     *
     *  @param	string	$selected       Id or Code or Label of preselected country
     *  @param  string	$htmlname       Name of html select object
     *  @param  string	$htmloption     Options html on select object
     *  @param	integer	$maxlength		Max length for labels (0=no limit)
     *  @param	string	$morecss		More css class
     *  @return string           		HTML string with select
     */
    function select_country($selected='',$htmlname='country_id',$htmloption='',$maxlength=0,$morecss='minwidth300')
    {
        global $conf,$langs;

        $langs->load("dict");

        $out='';
        $countryArray=array();
		$favorite=array();
        $label=array();
		$atleastonefavorite=0;

        $sql = "SELECT rowid, code as code_iso, code_iso as code_iso3, label, favorite";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_country";
        $sql.= " WHERE active > 0";
        //$sql.= " ORDER BY code ASC";

        dol_syslog(get_class($this)."::select_country", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $out.= '<select id="select'.$htmlname.'" class="flat maxwidth200onsmartphone selectcountry'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" '.$htmloption.'>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                $foundselected=false;

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $countryArray[$i]['rowid'] 		= $obj->rowid;
                    $countryArray[$i]['code_iso'] 	= $obj->code_iso;
                    $countryArray[$i]['code_iso3'] 	= $obj->code_iso3;
                    $countryArray[$i]['label']		= ($obj->code_iso && $langs->transnoentitiesnoconv("Country".$obj->code_iso)!="Country".$obj->code_iso?$langs->transnoentitiesnoconv("Country".$obj->code_iso):($obj->label!='-'?$obj->label:''));
                    $countryArray[$i]['favorite']   = $obj->favorite;
                    $favorite[$i]					= $obj->favorite;
					$label[$i] = dol_string_unaccent($countryArray[$i]['label']);
                    $i++;
                }

                array_multisort($favorite, SORT_DESC, $label, SORT_ASC, $countryArray);

                foreach ($countryArray as $row)
                {
                	if ($row['favorite'] && $row['code_iso']) $atleastonefavorite++;
					if (empty($row['favorite']) && $atleastonefavorite)
					{
						$atleastonefavorite=0;
						$out.= '<option value="" disabled class="selectoptiondisabledwhite">----------------------</option>';
					}
                    if ($selected && $selected != '-1' && ($selected == $row['rowid'] || $selected == $row['code_iso'] || $selected == $row['code_iso3'] || $selected == $row['label']) )
                    {
                        $foundselected=true;
                        $out.= '<option value="'.$row['rowid'].'" selected>';
                    }
                    else
					{
                        $out.= '<option value="'.$row['rowid'].'">';
                    }
                    $out.= dol_trunc($row['label'],$maxlength,'middle');
                    if ($row['code_iso']) $out.= ' ('.$row['code_iso'] . ')';
                    $out.= '</option>';
                }
            }
            $out.= '</select>';
        }
        else
		{
            dol_print_error($this->db);
        }

        // Make select dynamic
        include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        $out .= ajax_combobox('select'.$htmlname);

        return $out;
    }

	/**
     *  Return select list of incoterms
     *
     *  @param	string	$selected       		Id or Code of preselected incoterm
     *  @param	string	$location_incoterms     Value of input location
     *  @param	string	$page       			Defined the form action
     *  @param  string	$htmlname       		Name of html select object
     *  @param  string	$htmloption     		Options html on select object
     * 	@param	int		$forcecombo				Force to use standard combo box (no ajax use)
     *  @param	array	$events					Event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @return string           				HTML string with select and input
     */
    function select_incoterms($selected='', $location_incoterms='', $page='', $htmlname='incoterm_id', $htmloption='', $forcecombo=1, $events=array())
    {
        global $conf,$langs;

        $langs->load("dict");

        $out='';
        $incotermArray=array();

        $sql = "SELECT rowid, code";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_incoterms";
        $sql.= " WHERE active > 0";
        $sql.= " ORDER BY code ASC";

        dol_syslog(get_class($this)."::select_incoterm", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
        	if ($conf->use_javascript_ajax && ! $forcecombo)
			{
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
				$out .= ajax_combobox($htmlname, $events);
			}

			if (!empty($page))
			{
				$out .= '<form method="post" action="'.$page.'">';
	            $out .= '<input type="hidden" name="action" value="set_incoterms">';
	            $out .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			}

            $out.= '<select id="'.$htmlname.'" class="flat selectincoterm noenlargeonsmartphone" name="'.$htmlname.'" '.$htmloption.'>';
			$out.= '<option value="0">&nbsp;</option>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                $foundselected=false;

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $incotermArray[$i]['rowid'] = $obj->rowid;
                    $incotermArray[$i]['code'] = $obj->code;
                    $i++;
                }

                foreach ($incotermArray as $row)
                {
                    if ($selected && ($selected == $row['rowid'] || $selected == $row['code']))
                    {
                        $out.= '<option value="'.$row['rowid'].'" selected>';
                    }
                    else
					{
                        $out.= '<option value="'.$row['rowid'].'">';
                    }

                    if ($row['code']) $out.= $row['code'];

					$out.= '</option>';
                }
            }
            $out.= '</select>';

			$out .= '<input id="location_incoterms" class="maxwidth100onsmartphone" name="location_incoterms" value="'.$location_incoterms.'">';

			if (!empty($page))
			{
	            $out .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'"></form>';
			}
        }
        else
		{
            dol_print_error($this->db);
        }

        return $out;
    }

    /**
     *	Return list of types of lines (product or service)
     * 	Example: 0=product, 1=service, 9=other (for external module)
     *
     *	@param  string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in html form
     * 	@param	int		$showempty		Add an empty field
     * 	@param	int		$hidetext		Do not show label 'Type' before combo box (used only if there is at least 2 choices to select)
     * 	@param	integer	$forceall		1=Force to show products and services in combo list, whatever are activated modules, 0=No force, -1=Force none (and set hidden field to 'service')
     *  @return	void
     */
    function select_type_of_lines($selected='',$htmlname='type',$showempty=0,$hidetext=0,$forceall=0)
    {
        global $db,$langs,$user,$conf;

        // If product & services are enabled or both disabled.
        if ($forceall > 0 || (empty($forceall) && ! empty($conf->product->enabled) && ! empty($conf->service->enabled))
        || (empty($forceall) && empty($conf->product->enabled) && empty($conf->service->enabled)) )
        {
            if (empty($hidetext)) print $langs->trans("Type").': ';
            print '<select class="flat" id="select_'.$htmlname.'" name="'.$htmlname.'">';
            if ($showempty)
            {
                print '<option value="-1"';
                if ($selected == -1) print ' selected';
                print '>&nbsp;</option>';
            }

            print '<option value="0"';
            if (0 == $selected) print ' selected';
            print '>'.$langs->trans("Product");

            print '<option value="1"';
            if (1 == $selected) print ' selected';
            print '>'.$langs->trans("Service");

            print '</select>';
            //if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        }
        if (empty($forceall) && empty($conf->product->enabled) && ! empty($conf->service->enabled))
        {
        	print $langs->trans("Service");
            print '<input type="hidden" name="'.$htmlname.'" value="1">';
        }
        if (empty($forceall) && ! empty($conf->product->enabled) && empty($conf->service->enabled))
        {
        	print $langs->trans("Product");
            print '<input type="hidden" name="'.$htmlname.'" value="0">';
        }
		if ($forceall < 0)	// This should happened only for contracts when both predefined product and service are disabled.
		{
            print '<input type="hidden" name="'.$htmlname.'" value="1">';	// By default we set on service for contract. If CONTRACT_SUPPORT_PRODUCTS is set, forceall should be 1 not -1
		}
    }

    /**
     *	Load into cache cache_types_fees, array of types of fees
     *
     *	@return     int             Nb of lines loaded, <0 if KO
     */
    function load_cache_types_fees()
    {
        global $langs;

        $num = count($this->cache_types_fees);
        if ($num > 0) return 0;    // Cache already loaded

        dol_syslog(__METHOD__, LOG_DEBUG);

        $langs->load("trips");

        $sql = "SELECT c.code, c.label";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_type_fees as c";
        $sql.= " WHERE active > 0";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;

            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                // Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
                $label=($obj->code != $langs->trans($obj->code) ? $langs->trans($obj->code) : $langs->trans($obj->label));
                $this->cache_types_fees[$obj->code] = $label;
                $i++;
            }

			asort($this->cache_types_fees);

            return $num;
        }
        else
		{
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *	Return list of types of notes
     *
     *	@param	string		$selected		Preselected type
     *	@param  string		$htmlname		Name of field in form
     * 	@param	int			$showempty		Add an empty field
     * 	@return	void
     */
    function select_type_fees($selected='',$htmlname='type',$showempty=0)
    {
        global $user, $langs;

        dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

        $this->load_cache_types_fees();

        print '<select class="flat" name="'.$htmlname.'">';
        if ($showempty)
        {
            print '<option value="-1"';
            if ($selected == -1) print ' selected';
            print '>&nbsp;</option>';
        }

        foreach($this->cache_types_fees as $key => $value)
        {
            print '<option value="'.$key.'"';
            if ($key == $selected) print ' selected';
            print '>';
            print $value;
            print '</option>';
        }

        print '</select>';
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }


    /**
     *  Return HTML code to select a company.
     *
     *  @param		int			$selected				Preselected products
     *  @param		string		$htmlname				Name of HTML select field (must be unique in page)
     *  @param		int			$filter					Filter on thirdparty
     *  @param		int			$limit					Limit on number of returned lines
     *  @param		array		$ajaxoptions			Options for ajax_autocompleter
     * 	@param		int			$forcecombo				Force to use combo box
     *  @return		string								Return select box for thirdparty.
	 *  @deprecated	3.8 Use select_company instead. For exemple $form->select_thirdparty(GETPOST('socid'),'socid','',0) => $form->select_company(GETPOST('socid'),'socid','',1,0,0,array(),0)
     */
    function select_thirdparty($selected='', $htmlname='socid', $filter='', $limit=20, $ajaxoptions=array(), $forcecombo=0)
    {
   		return $this->select_thirdparty_list($selected,$htmlname,$filter,1,0,$forcecombo,array(),'',0,$limit);
    }

    /**
     *  Output html form to select a third party
     *
     *	@param	string	$selected       		Preselected type
     *	@param  string	$htmlname       		Name of field in form
     *  @param  string	$filter         		optional filters criteras (example: 's.rowid <> x', 's.client IN (1,3)')
     *	@param	string	$showempty				Add an empty field (Can be '1' or text key to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype				Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo				Force to use combo box
     *  @param	array	$events					Ajax event options to run on change. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@param	int		$limit					Maximum number of elements
     *  @param	string	$morecss				Add more css styles to the SELECT component
     *	@param  string	$moreparam      		Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
	 *	@param	string	$selected_input_value	Value of preselected input text (for use with ajax)
     *  @param	int		$hidelabel				Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
     *  @param	array	$ajaxoptions			Options for ajax_autocompleter
     * 	@return	string							HTML string with select box for thirdparty.
     */
    function select_company($selected='', $htmlname='socid', $filter='', $showempty='', $showtype=0, $forcecombo=0, $events=array(), $limit=0, $morecss='minwidth100', $moreparam='', $selected_input_value='', $hidelabel=1, $ajaxoptions=array())
    {
    	global $conf,$user,$langs;

    	$out='';

    	if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT) && ! $forcecombo)
    	{
    	    // No immediate load of all database
    		$placeholder='';
    		if ($selected && empty($selected_input_value))
    		{
    			require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
    			$societetmp = new Societe($this->db);
    			$societetmp->fetch($selected);
    			$selected_input_value=$societetmp->name;
    			unset($societetmp);
    		}
    		// mode 1
    		$urloption='htmlname='.$htmlname.'&outjson=1&filter='.$filter.($showtype?'&showtype='.$showtype:'');
    		$out.=  ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/societe/ajax/company.php', $urloption, $conf->global->COMPANY_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
			$out.='<style type="text/css">
					.ui-autocomplete {
						z-index: 250;
					}
				</style>';
    		if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
    		else if ($hidelabel > 1) {
    			if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
    			else $placeholder=' title="'.$langs->trans("RefOrLabel").'"';
    			if ($hidelabel == 2) {
    				$out.=img_picto($langs->trans("Search"), 'search');
    			}
    		}
            $out.=  '<input type="text" class="'.$morecss.'" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->THIRDPARTY_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
    		if ($hidelabel == 3) {
    			$out.=img_picto($langs->trans("Search"), 'search');
    		}
    	}
    	else
    	{
    	    // Immediate load of all database
    		$out.=$this->select_thirdparty_list($selected, $htmlname, $filter, $showempty, $showtype, $forcecombo, $events, '', 0, $limit, $morecss, $moreparam);
    	}

    	return $out;
    }

    /**
     *  Output html form to select a third party.
     *  Note, you must use the select_company to get the component to select a third party. This function must only be called by select_company.
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x', 's.client in (1,3)')
     *	@param	string	$showempty		Add an empty field (Can be '1' or text to use on empty line like 'SelectThirdParty')
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     *  @param	string	$morecss		Add more css styles to the SELECT component
     *	@param  string	$moreparam      Add more parameters onto the select tag. For example 'style="width: 95%"' to avoid select2 component to go over parent container
     * 	@return	string					HTML string with
     */
    function select_thirdparty_list($selected='',$htmlname='socid',$filter='',$showempty='', $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $morecss='minwidth100', $moreparam='')
    {
        global $conf,$user,$langs;

        $out='';
        $num=0;
        $outarray=array();

        // On recherche les societes
        $sql = "SELECT s.rowid, s.nom as name, s.name_alias, s.client, s.fournisseur, s.code_client, s.code_fournisseur";
        $sql.= " FROM ".MAIN_DB_PREFIX ."societe as s";
        if (!$user->rights->societe->client->voir && !$user->societe_id) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql.= " WHERE s.entity IN (".getEntity('societe').")";
        if (! empty($user->societe_id)) $sql.= " AND s.rowid = ".$user->societe_id;
        if ($filter) $sql.= " AND (".$filter.")";
        if (!$user->rights->societe->client->voir && !$user->societe_id) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
        if (! empty($conf->global->COMPANY_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND s.status <> 0";
        // Add criteria
        if ($filterkey && $filterkey != '')
        {
			$sql.=" AND (";
        	$prefix=empty($conf->global->COMPANY_DONOTSEARCH_ANYWHERE)?'%':'';	// Can use index if COMPANY_DONOTSEARCH_ANYWHERE is on
        	// For natural search
        	$scrit = explode(' ', $filterkey);
        	$i=0;
        	if (count($scrit) > 1) $sql.="(";
        	foreach ($scrit as $crit) {
        		if ($i > 0) $sql.=" AND ";
        		$sql.="(s.nom LIKE '".$this->db->escape($prefix.$crit)."%')";
        		$i++;
        	}
        	if (count($scrit) > 1) $sql.=")";
            if (! empty($conf->barcode->enabled))
        	{
        		$sql .= " OR s.barcode LIKE '".$this->db->escape($filterkey)."%'";
        	}
        	$sql.=")";
        }
        $sql.=$this->db->order("nom","ASC");
		if ($limit > 0) $sql.=$this->db->plimit($limit);

		// Build output string
        dol_syslog(get_class($this)."::select_thirdparty_list", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
           	if ($conf->use_javascript_ajax && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$comboenhancement =ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
            	$out.= $comboenhancement;
            }

            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat'.($morecss?' '.$morecss:'').'"'.($moreparam?' '.$moreparam:'').' name="'.$htmlname.'">'."\n";

            $textifempty='';
            // Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
            //if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
            if (! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT))
            {
                if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
                else $textifempty.=$langs->trans("All");
            }
            if ($showempty) $out.= '<option value="-1">'.$textifempty.'</option>'."\n";

			$num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $label='';
                    if ($conf->global->SOCIETE_ADD_REF_IN_LIST) {
                    	if (($obj->client) && (!empty($obj->code_client))) {
                    		$label = $obj->code_client. ' - ';
                    	}
                    	if (($obj->fournisseur) && (!empty($obj->code_fournisseur))) {
                    		$label .= $obj->code_fournisseur. ' - ';
                    	}
                    	$label.=' '.$obj->name;
                    }
                    else
                    {
                    	$label=$obj->name;
                    }

					if(!empty($obj->name_alias)) {
						$label.=' ('.$obj->name_alias.')';
					}

                    if ($showtype)
                    {
                        if ($obj->client || $obj->fournisseur) $label.=' (';
                        if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
                        if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
                        if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
                        if ($obj->client || $obj->fournisseur) $label.=')';
                    }
                    if ($selected > 0 && $selected == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected>'.$label.'</option>';
                    }
                    else
					{
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }

                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));

                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";
        }
        else
        {
            dol_print_error($this->db);
        }

        $this->result=array('nbofthirdparties'=>$num);

        if ($outputmode) return $outarray;
        return $out;
    }


    /**
     *    	Return HTML combo list of absolute discounts
     *
     *    	@param	string	$selected       Id remise fixe pre-selectionnee
     *    	@param  string	$htmlname       Nom champ formulaire
     *    	@param  string	$filter         Criteres optionnels de filtre
     * 		@param	int		$socid			Id of thirdparty
     * 		@param	int		$maxvalue		Max value for lines that can be selected
     * 		@return	int						Return number of qualifed lines in list
     */
    function select_remises($selected, $htmlname, $filter, $socid, $maxvalue=0)
    {
        global $langs,$conf;

        // On recherche les remises
        $sql = "SELECT re.rowid, re.amount_ht, re.amount_tva, re.amount_ttc,";
        $sql.= " re.description, re.fk_facture_source";
        $sql.= " FROM ".MAIN_DB_PREFIX ."societe_remise_except as re";
        $sql.= " WHERE re.fk_soc = ".(int) $socid;
        $sql.= " AND re.entity = " . $conf->entity;
        if ($filter) $sql.= " AND ".$filter;
        $sql.= " ORDER BY re.description ASC";

        dol_syslog(get_class($this)."::select_remises", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            print '<select class="flat maxwidthonsmartphone" name="'.$htmlname.'">';
            $num = $this->db->num_rows($resql);

            $qualifiedlines=$num;

            $i = 0;
            if ($num)
            {
                print '<option value="0">&nbsp;</option>';
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $desc=dol_trunc($obj->description,40);
                    if (preg_match('/\(CREDIT_NOTE\)/', $desc)) $desc=preg_replace('/\(CREDIT_NOTE\)/', $langs->trans("CreditNote"), $desc);
                    if (preg_match('/\(DEPOSIT\)/', $desc)) $desc=preg_replace('/\(DEPOSIT\)/', $langs->trans("Deposit"), $desc);
                    if (preg_match('/\(EXCESS RECEIVED\)/', $desc)) $desc=preg_replace('/\(EXCESS RECEIVED\)/', $langs->trans("ExcessReceived"), $desc);

                    $selectstring='';
                    if ($selected > 0 && $selected == $obj->rowid) $selectstring=' selected';

                    $disabled='';
                    if ($maxvalue > 0 && $obj->amount_ttc > $maxvalue)
                    {
                        $qualifiedlines--;
                        $disabled=' disabled';
                    }

					if (!empty($conf->global->MAIN_SHOW_FACNUMBER_IN_DISCOUNT_LIST) && !empty($obj->fk_facture_source))
					{
						$tmpfac = new Facture($this->db);
						if ($tmpfac->fetch($obj->fk_facture_source) > 0) $desc=$desc.' - '.$tmpfac->ref;
					}

                    print '<option value="'.$obj->rowid.'"'.$selectstring.$disabled.'>'.$desc.' ('.price($obj->amount_ht).' '.$langs->trans("HT").' - '.price($obj->amount_ttc).' '.$langs->trans("TTC").')</option>';
                    $i++;
                }
            }
            print '</select>';
            return $qualifiedlines;
        }
        else
        {
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *	Return list of all contacts (for a third party or all)
     *
     *	@param	int		$socid      	Id ot third party or 0 for all
     *	@param  string	$selected   	Id contact pre-selectionne
     *	@param  string	$htmlname  	    Name of HTML field ('none' for a not editable field)
     *	@param  int		$showempty      0=no empty value, 1=add an empty value
     *	@param  string	$exclude        List of contacts id to exclude
     *	@param	string	$limitto		Disable answers that are not id in this array list
     *	@param	integer	$showfunction   Add function into label
     *	@param	string	$moreclass		Add more class to class style
     *	@param	integer	$showsoc	    Add company into label
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	bool	$options_only	Return options only (for ajax treatment)
     *	@return	int						<0 if KO, Nb of contact in list if OK
     */
    function select_contacts($socid,$selected='',$htmlname='contactid',$showempty=0,$exclude='',$limitto='',$showfunction=0, $moreclass='', $showsoc=0, $forcecombo=0, $events=array(), $options_only=false)
    {
    	print $this->selectcontacts($socid,$selected,$htmlname,$showempty,$exclude,$limitto,$showfunction, $moreclass, $options_only, $showsoc, $forcecombo, $events);
    	return $this->num;
    }

    /**
     *	Return list of all contacts (for a third party or all)
     *
     *	@param	int		$socid      	Id ot third party or 0 for all
     *	@param  string	$selected   	Id contact pre-selectionne
     *	@param  string	$htmlname  	    Name of HTML field ('none' for a not editable field)
     *	@param  int		$showempty     	0=no empty value, 1=add an empty value, 2=add line 'Internal' (used by user edit)
     *	@param  string	$exclude        List of contacts id to exclude
     *	@param	string	$limitto		Disable answers that are not id in this array list
     *	@param	integer	$showfunction   Add function into label
     *	@param	string	$moreclass		Add more class to class style
     *	@param	bool	$options_only	Return options only (for ajax treatment)
     *	@param	integer	$showsoc	    Add company into label
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *	@return	 int					<0 if KO, Nb of contact in list if OK
     */
    function selectcontacts($socid,$selected='',$htmlname='contactid',$showempty=0,$exclude='',$limitto='',$showfunction=0, $moreclass='', $options_only=false, $showsoc=0, $forcecombo=0, $events=array())
    {
        global $conf,$langs;

        $langs->load('companies');

        $out='';

        // On recherche les societes
        $sql = "SELECT sp.rowid, sp.lastname, sp.statut, sp.firstname, sp.poste";
        if ($showsoc > 0) $sql.= " , s.nom as company";
        $sql.= " FROM ".MAIN_DB_PREFIX ."socpeople as sp";
        if ($showsoc > 0) $sql.= " LEFT OUTER JOIN  ".MAIN_DB_PREFIX ."societe as s ON s.rowid=sp.fk_soc";
        $sql.= " WHERE sp.entity IN (".getEntity('societe').")";
        if ($socid > 0) $sql.= " AND sp.fk_soc=".$socid;
        if (! empty($conf->global->CONTACT_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND sp.statut <> 0";
        $sql.= " ORDER BY sp.lastname ASC";

        dol_syslog(get_class($this)."::select_contacts", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num=$this->db->num_rows($resql);

            if ($conf->use_javascript_ajax && ! $forcecombo && ! $options_only)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$comboenhancement = ajax_combobox($htmlname, $events, $conf->global->CONTACT_USE_SEARCH_TO_SELECT);
            	$out.= $comboenhancement;
            }

            if ($htmlname != 'none' || $options_only) $out.= '<select class="flat'.($moreclass?' '.$moreclass:'').'" id="'.$htmlname.'" name="'.$htmlname.'">';
            if ($showempty == 1) $out.= '<option value="0"'.($selected=='0'?' selected':'').'></option>';
            if ($showempty == 2) $out.= '<option value="0"'.($selected=='0'?' selected':'').'>'.$langs->trans("Internal").'</option>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                include_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
                $contactstatic=new Contact($this->db);

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);

                    $contactstatic->id=$obj->rowid;
                    $contactstatic->lastname=$obj->lastname;
                    $contactstatic->firstname=$obj->firstname;
					if ($obj->statut == 1){
                    if ($htmlname != 'none')
                    {
                        $disabled=0;
                        if (is_array($exclude) && count($exclude) && in_array($obj->rowid,$exclude)) $disabled=1;
                        if (is_array($limitto) && count($limitto) && ! in_array($obj->rowid,$limitto)) $disabled=1;
                        if ($selected && $selected == $obj->rowid)
                        {
                            $out.= '<option value="'.$obj->rowid.'"';
                            if ($disabled) $out.= ' disabled';
                            $out.= ' selected>';
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                            if (($showsoc > 0) && $obj->company) $out.= ' - ('.$obj->company.')';
                            $out.= '</option>';
                        }
                        else
                        {
                            $out.= '<option value="'.$obj->rowid.'"';
                            if ($disabled) $out.= ' disabled';
                            $out.= '>';
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                            if (($showsoc > 0) && $obj->company) $out.= ' - ('.$obj->company.')';
                            $out.= '</option>';
                        }
                    }
                    else
					{
                        if ($selected == $obj->rowid)
                        {
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                            if (($showsoc > 0) && $obj->company) $out.= ' - ('.$obj->company.')';
                        }
                    }
				}
                    $i++;
                }
            }
            else
			{
            	$out.= '<option value="-1"'.($showempty==2?'':' selected').' disabled>'.$langs->trans($socid?"NoContactDefinedForThirdParty":"NoContactDefined").'</option>';
            }
            if ($htmlname != 'none' || $options_only)
            {
                $out.= '</select>';
            }

            $this->num = $num;
            return $out;
        }
        else
        {
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *	Return select list of users
     *
     *  @param	string	$selected       Id user preselected
     *  @param  string	$htmlname       Field name in form
     *  @param  int		$show_empty     0=liste sans valeur nulle, 1=ajoute valeur inconnue
     *  @param  array	$exclude        Array list of users id to exclude
     * 	@param	int		$disabled		If select list must be disabled
     *  @param  array	$include        Array list of users id to include
     * 	@param	int		$enableonly		Array list of users id to be enabled. All other must be disabled
     *  @param	int		$force_entity	0 or Id of environment to force
     * 	@return	void
     *  @deprecated
     *  @see select_dolusers()
     */
    function select_users($selected='',$htmlname='userid',$show_empty=0,$exclude=null,$disabled=0,$include='',$enableonly='',$force_entity=0)
    {
        print $this->select_dolusers($selected,$htmlname,$show_empty,$exclude,$disabled,$include,$enableonly,$force_entity);
    }

    /**
     *	Return select list of users
     *
     *  @param	string	$selected       User id or user object of user preselected. If -1, we use id of current user.
     *  @param  string	$htmlname       Field name in form
     *  @param  int		$show_empty     0=list with no empty value, 1=add also an empty value into list
     *  @param  array	$exclude        Array list of users id to exclude
     * 	@param	int		$disabled		If select list must be disabled
     *  @param  array|string	$include        Array list of users id to include or 'hierarchy' to have only supervised users or 'hierarchyme' to have supervised + me
     * 	@param	array	$enableonly		Array list of users id to be enabled. All other must be disabled
     *  @param	int		$force_entity	0 or Id of environment to force
     *  @param	int		$maxlength		Maximum length of string into list (0=no limit)
     *  @param	int		$showstatus		0=show user status only if status is disabled, 1=always show user status into label, -1=never show user status
     *  @param	string	$morefilter		Add more filters into sql request
     *  @param	integer	$show_every		0=default list, 1=add also a value "Everybody" at beginning of list
     *  @param	string	$enableonlytext	If option $enableonlytext is set, we use this text to explain into label why record is disabled. Not used if enableonly is empty.
     *  @param	string	$morecss		More css
     *  @param  int     $noactive       Show only active users (this will also happened whatever is this option if USER_HIDE_INACTIVE_IN_COMBOBOX is on).
     * 	@return	string					HTML select string
     *  @see select_dolgroups
     */
    function select_dolusers($selected='', $htmlname='userid', $show_empty=0, $exclude=null, $disabled=0, $include='', $enableonly='', $force_entity=0, $maxlength=0, $showstatus=0, $morefilter='', $show_every=0, $enableonlytext='', $morecss='', $noactive=0)
    {
        global $conf,$user,$langs;

        // If no preselected user defined, we take current user
        if ((is_numeric($selected) && ($selected < -2 || empty($selected))) && empty($conf->global->SOCIETE_DISABLE_DEFAULT_SALESREPRESENTATIVE)) $selected=$user->id;

        $excludeUsers=null;
        $includeUsers=null;

        // Permettre l'exclusion d'utilisateurs
        if (is_array($exclude))	$excludeUsers = implode(",",$exclude);
        // Permettre l'inclusion d'utilisateurs
        if (is_array($include))	$includeUsers = implode(",",$include);
		else if ($include == 'hierarchy')
		{
			// Build list includeUsers to have only hierarchy
			$includeUsers = implode(",",$user->getAllChildIds(0));
		}
		else if ($include == 'hierarchyme')
		{
		    // Build list includeUsers to have only hierarchy and current user
		    $includeUsers = implode(",",$user->getAllChildIds(1));
		}

        $out='';

        // On recherche les utilisateurs
        $sql = "SELECT DISTINCT u.rowid, u.lastname as lastname, u.firstname, u.statut, u.login, u.admin, u.entity";
        if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity)
        {
            $sql.= ", e.label";
        }
        $sql.= " FROM ".MAIN_DB_PREFIX ."user as u";
        if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity)
        {
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."entity as e ON e.rowid=u.entity";
            if ($force_entity) $sql.= " WHERE u.entity IN (0,".$force_entity.")";
            else $sql.= " WHERE u.entity IS NOT NULL";
        }
        else
       {
        	if (! empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE))
        	{
        		$sql.= ", ".MAIN_DB_PREFIX."usergroup_user as ug";
        		$sql.= " WHERE ug.fk_user = u.rowid";
        		$sql.= " AND ug.entity = ".$conf->entity;
        	}
        	else
        	{
        		$sql.= " WHERE u.entity IN (0,".$conf->entity.")";
        	}
        }
        if (! empty($user->societe_id)) $sql.= " AND u.fk_soc = ".$user->societe_id;
        if (is_array($exclude) && $excludeUsers) $sql.= " AND u.rowid NOT IN (".$excludeUsers.")";
        if ($includeUsers) $sql.= " AND u.rowid IN (".$includeUsers.")";
        if (! empty($conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX) || $noactive) $sql.= " AND u.statut <> 0";
        if (! empty($morefilter)) $sql.=" ".$morefilter;

        if(empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION)){
            $sql.= " ORDER BY u.firstname ASC";
        }else{
            $sql.= " ORDER BY u.lastname ASC";
        }


        dol_syslog(get_class($this)."::select_dolusers", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
           		// Enhance with select2
		        if ($conf->use_javascript_ajax)
		        {
		            include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
		            $comboenhancement = ajax_combobox($htmlname);
		            $out.=$comboenhancement;
		        }

		        // do not use maxwidthonsmartphone by default. Set it by caller so auto size to 100% will work when not defined
                $out.= '<select class="flat'.($morecss?' minwidth100 '.$morecss:' minwidth200').'" id="'.$htmlname.'" name="'.$htmlname.'"'.($disabled?' disabled':'').'>';
                if ($show_empty) $out.= '<option value="-1"'.((empty($selected) || $selected==-1)?' selected':'').'>&nbsp;</option>'."\n";
				if ($show_every) $out.= '<option value="-2"'.(($selected==-2)?' selected':'').'>-- '.$langs->trans("Everybody").' --</option>'."\n";

                $userstatic=new User($this->db);

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);

                    $userstatic->id=$obj->rowid;
                    $userstatic->lastname=$obj->lastname;
                    $userstatic->firstname=$obj->firstname;

                    $disableline='';
                    if (is_array($enableonly) && count($enableonly) && ! in_array($obj->rowid,$enableonly)) $disableline=($enableonlytext?$enableonlytext:'1');

                    if ((is_object($selected) && $selected->id == $obj->rowid) || (! is_object($selected) && $selected == $obj->rowid))
                    {
                        $out.= '<option value="'.$obj->rowid.'"';
                        if ($disableline) $out.= ' disabled';
                        $out.= ' selected>';
                    }
                    else
                    {
                        $out.= '<option value="'.$obj->rowid.'"';
                        if ($disableline) $out.= ' disabled';
                        $out.= '>';
                    }

                    $fullNameMode = 0; //Lastname + firstname
                    if(empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION)){
                        $fullNameMode = 1; //firstname + lastname
                    }
                    $out.= $userstatic->getFullName($langs, $fullNameMode, -1, $maxlength);

                    // Complete name with more info
                    $moreinfo=0;
                    if (! empty($conf->global->MAIN_SHOW_LOGIN))
                    {
                    	$out.= ($moreinfo?' - ':' (').$obj->login;
                    	$moreinfo++;
                    }
                    if ($showstatus >= 0)
                    {
                    	if ($obj->statut == 1 && $showstatus == 1)
                    	{
                    		$out.=($moreinfo?' - ':' (').$langs->trans('Enabled');
                    		$moreinfo++;
                    	}
						if ($obj->statut == 0)
						{
							$out.=($moreinfo?' - ':' (').$langs->trans('Disabled');
							$moreinfo++;
						}
					}
                    if (! empty($conf->multicompany->enabled) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1 && $user->admin && ! $user->entity)
                    {
                        if ($obj->admin && ! $obj->entity)
                        {
                        	$out.=($moreinfo?' - ':' (').$langs->trans("AllEntities");
                        	$moreinfo++;
                        }
                        else
                     {
                        	$out.=($moreinfo?' - ':' (').($obj->label?$obj->label:$langs->trans("EntityNameNotDefined"));
                        	$moreinfo++;
                     	}
                    }
					$out.=($moreinfo?')':'');
					if ($disableline && $disableline != '1')
					{
						$out.=' - '.$disableline;	// This is text from $enableonlytext parameter
					}
                    $out.= '</option>';

                    $i++;
                }
            }
            else
            {
                $out.= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" disabled>';
                $out.= '<option value="">'.$langs->trans("None").'</option>';
            }
            $out.= '</select>';
        }
        else
        {
            dol_print_error($this->db);
        }

        return $out;
    }


    /**
     *	Return select list of users. Selected users are stored into session.
     *  List of users are provided into $_SESSION['assignedtouser'].
     *
     *  @param  string	$action         Value for $action
     *  @param  string	$htmlname       Field name in form
     *  @param  int		$show_empty     0=liste sans valeur nulle, 1=ajoute valeur inconnue
     *  @param  array	$exclude        Array list of users id to exclude
     * 	@param	int		$disabled		If select list must be disabled
     *  @param  array	$include        Array list of users id to include or 'hierarchy' to have only supervised users
     * 	@param	array	$enableonly		Array list of users id to be enabled. All other must be disabled
     *  @param	int		$force_entity	0 or Id of environment to force
     *  @param	int		$maxlength		Maximum length of string into list (0=no limit)
     *  @param	int		$showstatus		0=show user status only if status is disabled, 1=always show user status into label, -1=never show user status
     *  @param	string	$morefilter		Add more filters into sql request
     * 	@return	string					HTML select string
     *  @see select_dolgroups
     */
    function select_dolusers_forevent($action='', $htmlname='userid', $show_empty=0, $exclude=null, $disabled=0, $include='', $enableonly='', $force_entity=0, $maxlength=0, $showstatus=0, $morefilter='')
    {
        global $conf,$user,$langs;

        $userstatic=new User($this->db);
		$out='';

        // Method with no ajax
        //$out.='<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
        if ($action == 'view')
        {
			$out.='';
        }
		else
		{
			$out.='<input type="hidden" class="removedassignedhidden" name="removedassigned" value="">';
			$out.='<script type="text/javascript" language="javascript">jQuery(document).ready(function () {    jQuery(".removedassigned").click(function() {        jQuery(".removedassignedhidden").val(jQuery(this).val());    });})</script>';
			$out.=$this->select_dolusers('', $htmlname, $show_empty, $exclude, $disabled, $include, $enableonly, $force_entity, $maxlength, $showstatus, $morefilter);
			$out.=' <input type="submit" class="button valignmiddle" name="'.$action.'assignedtouser" value="'.dol_escape_htmltag($langs->trans("Add")).'">';
		}
		$assignedtouser=array();
		if (!empty($_SESSION['assignedtouser']))
		{
			$assignedtouser=json_decode($_SESSION['assignedtouser'], true);
		}
		$nbassignetouser=count($assignedtouser);

		if ($nbassignetouser && $action != 'view') $out.='<br>';
		if ($nbassignetouser) $out.='<div class="myavailability">';
		$i=0; $ownerid=0;
		foreach($assignedtouser as $key => $value)
		{
			if ($value['id'] == $ownerid) continue;
			$userstatic->fetch($value['id']);
			$out.=$userstatic->getNomUrl(-1);
			if ($i == 0) { $ownerid = $value['id']; $out.=' ('.$langs->trans("Owner").')'; }
			if ($nbassignetouser > 1 && $action != 'view') $out.=' <input type="image" style="border: 0px;" src="'.img_picto($langs->trans("Remove"), 'delete', '', 0, 1).'" value="'.$userstatic->id.'" class="removedassigned" id="removedassigned_'.$userstatic->id.'" name="removedassigned_'.$userstatic->id.'">';
			//$out.=' '.($value['mandatory']?$langs->trans("Mandatory"):$langs->trans("Optional"));
			//$out.=' '.($value['transparency']?$langs->trans("Busy"):$langs->trans("NotBusy"));
			$out.='<br>';
			$i++;
		}
		if ($nbassignetouser) $out.='</div>';

		//$out.='</form>';
        return $out;
    }


    /**
     *  Return list of products for customer in Ajax if Ajax activated or go to select_produits_list
     *
     *  @param		int			$selected				Preselected products
     *  @param		string		$htmlname				Name of HTML select field (must be unique in page)
     *  @param		int			$filtertype				Filter on product type (''=nofilter, 0=product, 1=service)
     *  @param		int			$limit					Limit on number of returned lines
     *  @param		int			$price_level			Level of price to show
     *  @param		int			$status					-1=Return all products, 0=Products not on sell, 1=Products on sell
     *  @param		int			$finished				2=all, 1=finished, 0=raw material
     *  @param		string		$selected_input_value	Value of preselected input text (for use with ajax)
     *  @param		int			$hidelabel				Hide label (0=no, 1=yes, 2=show search icon (before) and placeholder, 3 search icon after)
     *  @param		array		$ajaxoptions			Options for ajax_autocompleter
     *  @param      int			$socid					Thirdparty Id (to get also price dedicated to this customer)
     *  @param		string		$showempty				'' to not show empty line. Translation key to show an empty line. '1' show empty line with no text.
     * 	@param		int			$forcecombo				Force to use combo box
     *  @param      string      $morecss                Add more css on select
     *  @param      int         $hidepriceinlabel       1=Hide prices in label
     *  @param      string      $warehouseStatus        warehouse status filter, following comma separated filter options can be used
     *										            'warehouseopen' = select products from open warehouses,
	 *										            'warehouseclosed' = select products from closed warehouses,
	 *										            'warehouseinternal' = select products from warehouses for internal correct/transfer only
     *  @param array $selected_combinations Selected combinations. Format: array([attrid] => attrval, [...])
     *  @return		void
     */
    function select_produits($selected='', $htmlname='productid', $filtertype='', $limit=20, $price_level=0, $status=1, $finished=2, $selected_input_value='', $hidelabel=0, $ajaxoptions=array(), $socid=0, $showempty='1', $forcecombo=0, $morecss='', $hidepriceinlabel=0, $warehouseStatus='', $selected_combinations = array())
    {
        global $langs,$conf;

        $price_level = (! empty($price_level) ? $price_level : 0);

        if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
        {
        	$placeholder='';

            if ($selected && empty($selected_input_value))
            {
                require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
                $producttmpselect = new Product($this->db);
                $producttmpselect->fetch($selected);
                $selected_input_value=$producttmpselect->ref;
                unset($producttmpselect);
            }
            // mode=1 means customers products
            $urloption='htmlname='.$htmlname.'&outjson=1&price_level='.$price_level.'&type='.$filtertype.'&mode=1&status='.$status.'&finished='.$finished.'&warehousestatus='.$warehouseStatus;
            //Price by customer
            if (! empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid)) {
            	$urloption.='&socid='.$socid;
            }
            print ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/product/ajax/products.php', $urloption, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);

			if (!empty($conf->variants->enabled)) {
				?>
				<script>

					selected = <?php echo json_encode($selected_combinations) ?>;
					combvalues = {};

					jQuery(document).ready(function () {

						jQuery("input[name='prod_entry_mode']").change(function () {
							if (jQuery(this).val() == 'free') {
								jQuery('div#attributes_box').empty();
							}
						});

						jQuery("input#<?php echo $htmlname ?>").change(function () {

							if (!jQuery(this).val()) {
								jQuery('div#attributes_box').empty();
								return;
							}

							jQuery.getJSON("<?php echo dol_buildpath('/variants/ajax/getCombinations.php', 2) ?>", {
								id: jQuery(this).val()
							}, function (data) {
								jQuery('div#attributes_box').empty();

								jQuery.each(data, function (key, val) {

									combvalues[val.id] = val.values;

									var span = jQuery(document.createElement('div')).css({
										'display': 'table-row'
									});

									span.append(
										jQuery(document.createElement('div')).text(val.label).css({
											'font-weight': 'bold',
											'display': 'table-cell',
											'text-align': 'right'
										})
									);

									var html = jQuery(document.createElement('select')).attr('name', 'combinations[' + val.id + ']').css({
										'margin-left': '15px',
										'white-space': 'pre'
									}).append(
										jQuery(document.createElement('option')).val('')
									);

									jQuery.each(combvalues[val.id], function (key, val) {
										var tag = jQuery(document.createElement('option')).val(val.id).html(val.value);

										if (selected[val.fk_product_attribute] == val.id) {
											tag.attr('selected', 'selected');
										}

										html.append(tag);
									});

									span.append(html);
									jQuery('div#attributes_box').append(span);
								});
							})
						});

						<?php if ($selected): ?>
						jQuery("input#<?php echo $htmlname ?>").change();
						<?php endif ?>
					});
				</script>
                <?php
            }
            if (empty($hidelabel)) print $langs->trans("RefOrLabel").' : ';
            else if ($hidelabel > 1) {
            	if (! empty($conf->global->MAIN_HTML5_PLACEHOLDER)) $placeholder=' placeholder="'.$langs->trans("RefOrLabel").'"';
            	else $placeholder=' title="'.$langs->trans("RefOrLabel").'"';
            	if ($hidelabel == 2) {
            		print img_picto($langs->trans("Search"), 'search');
            	}
            }
            print '<input type="text" class="minwidth100" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'"'.$placeholder.' '.(!empty($conf->global->PRODUCT_SEARCH_AUTOFOCUS) ? 'autofocus' : '').' />';
            if ($hidelabel == 3) {
            	print img_picto($langs->trans("Search"), 'search');
            }
        }
        else
		{
            print $this->select_produits_list($selected,$htmlname,$filtertype,$limit,$price_level,'',$status,$finished,0,$socid,$showempty,$forcecombo,$morecss,$hidepriceinlabel, $warehouseStatus);
        }
    }

    /**
     *	Return list of products for a customer
     *
     *	@param      int		$selected           Preselected product
     *	@param      string	$htmlname           Name of select html
     *  @param		string	$filtertype         Filter on product type (''=nofilter, 0=product, 1=service)
     *	@param      int		$limit              Limit on number of returned lines
     *	@param      int		$price_level        Level of price to show
     * 	@param      string	$filterkey          Filter on product
     *	@param		int		$status             -1=Return all products, 0=Products not on sell, 1=Products on sell
     *  @param      int		$finished           Filter on finished field: 2=No filter
     *  @param      int		$outputmode         0=HTML select string, 1=Array
     *  @param      int		$socid     		    Thirdparty Id (to get also price dedicated to this customer)
     *  @param		string	$showempty		    '' to not show empty line. Translation key to show an empty line. '1' show empty line with no text.
     * 	@param		int		$forcecombo		    Force to use combo box
     *  @param      string  $morecss            Add more css on select
     *  @param      int     $hidepriceinlabel   1=Hide prices in label
     *  @param      string  $warehouseStatus    warehouse status filter, following comma separated filter options can be used
     *										    'warehouseopen' = select products from open warehouses,
	 *										    'warehouseclosed' = select products from closed warehouses,
	 *										    'warehouseinternal' = select products from warehouses for internal correct/transfer only
     *  @return     array    				    Array of keys for json
     */
    function select_produits_list($selected='',$htmlname='productid',$filtertype='',$limit=20,$price_level=0,$filterkey='',$status=1,$finished=2,$outputmode=0,$socid=0,$showempty='1',$forcecombo=0,$morecss='',$hidepriceinlabel=0, $warehouseStatus='')
    {
        global $langs,$conf,$user,$db;

        $out='';
        $outarray=array();

        $warehouseStatusArray = array();
        if (! empty($warehouseStatus))
        {
            require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
            if (preg_match('/warehouseclosed/', $warehouseStatus))
            {
                $warehouseStatusArray[] = Entrepot::STATUS_CLOSED;
            }
            if (preg_match('/warehouseopen/', $warehouseStatus))
            {
                $warehouseStatusArray[] = Entrepot::STATUS_OPEN_ALL;
            }
            if (preg_match('/warehouseinternal/', $warehouseStatus))
            {
                $warehouseStatusArray[] = Entrepot::STATUS_OPEN_INTERNAL;
            }
        }

        $selectFields = " p.rowid, p.label, p.ref, p.description, p.barcode, p.fk_product_type, p.price, p.price_ttc, p.price_base_type, p.tva_tx, p.duration, p.fk_price_expression";
        (count($warehouseStatusArray)) ? $selectFieldsGrouped = ", sum(ps.reel) as stock" : $selectFieldsGrouped = ", p.stock";

        $sql = "SELECT ";
        $sql.= $selectFields . $selectFieldsGrouped;
        //Price by customer
        if (! empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid)) {
        	$sql.=' ,pcp.rowid as idprodcustprice, pcp.price as custprice, pcp.price_ttc as custprice_ttc,';
        	$sql.=' pcp.price_base_type as custprice_base_type, pcp.tva_tx as custtva_tx';
            $selectFields.= ", idprodcustprice, custprice, custprice_ttc, custprice_base_type, custtva_tx";
        }

        // Multilang : we add translation
        if (! empty($conf->global->MAIN_MULTILANGS))
        {
            $sql.= ", pl.label as label_translated";
            $selectFields.= ", label_translated";
        }
		// Price by quantity
		if (! empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY))
		{
			$sql.= ", (SELECT pp.rowid FROM ".MAIN_DB_PREFIX."product_price as pp WHERE pp.fk_product = p.rowid";
			if ($price_level >= 1 && !empty($conf->global->PRODUIT_MULTIPRICES)) $sql.= " AND price_level=".$price_level;
			$sql.= " ORDER BY date_price";
			$sql.= " DESC LIMIT 1) as price_rowid";
			$sql.= ", (SELECT pp.price_by_qty FROM ".MAIN_DB_PREFIX."product_price as pp WHERE pp.fk_product = p.rowid";
			if ($price_level >= 1 && !empty($conf->global->PRODUIT_MULTIPRICES)) $sql.= " AND price_level=".$price_level;
			$sql.= " ORDER BY date_price";
			$sql.= " DESC LIMIT 1) as price_by_qty";
            $selectFields.= ", price_rowid, price_by_qty";
		}
        $sql.= " FROM ".MAIN_DB_PREFIX."product as p";
        if (count($warehouseStatusArray))
        {
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock as ps on ps.fk_product = p.rowid";
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."entrepot as e on ps.fk_entrepot = e.rowid";
        }

        //Price by customer
        if (! empty($conf->global->PRODUIT_CUSTOMER_PRICES) && !empty($socid)) {
        	$sql.=" LEFT JOIN  ".MAIN_DB_PREFIX."product_customer_price as pcp ON pcp.fk_soc=".$socid." AND pcp.fk_product=p.rowid";
        }
        // Multilang : we add translation
        if (! empty($conf->global->MAIN_MULTILANGS))
        {
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_lang as pl ON pl.fk_product = p.rowid AND pl.lang='". $langs->getDefaultLang() ."'";
        }

        if (!empty($conf->global->PRODUIT_ATTRIBUTES_HIDECHILD)) {
            $sql .= " LEFT JOIN llx_product_attribute_combination pac ON pac.fk_product_child = p.rowid";
        }

        $sql.= ' WHERE p.entity IN ('.getEntity('product').')';
        if (count($warehouseStatusArray))
        {
            $sql.= ' AND (p.fk_product_type = 1 OR e.statut IN ('.$this->db->escape(implode(',',$warehouseStatusArray)).'))';
        }

        if (!empty($conf->global->PRODUIT_ATTRIBUTES_HIDECHILD)) {
            $sql .= " AND pac.rowid IS NULL";
        }

        if ($finished == 0)
        {
            $sql.= " AND p.finished = ".$finished;
        }
        elseif ($finished == 1)
        {
            $sql.= " AND p.finished = ".$finished;
            if ($status >= 0)  $sql.= " AND p.tosell = ".$status;
        }
        elseif ($status >= 0)
        {
            $sql.= " AND p.tosell = ".$status;
        }
        if (strval($filtertype) != '') $sql.=" AND p.fk_product_type=".$filtertype;
        // Add criteria on ref/label
        if ($filterkey != '')
        {
        	$sql.=' AND (';
        	$prefix=empty($conf->global->PRODUCT_DONOTSEARCH_ANYWHERE)?'%':'';	// Can use index if PRODUCT_DONOTSEARCH_ANYWHERE is on
            // For natural search
            $scrit = explode(' ', $filterkey);
            $i=0;
            if (count($scrit) > 1) $sql.="(";
            foreach ($scrit as $crit)
            {
            	if ($i > 0) $sql.=" AND ";
                $sql.="(p.ref LIKE '".$db->escape($prefix.$crit)."%' OR p.label LIKE '".$db->escape($prefix.$crit)."%'";
                if (! empty($conf->global->MAIN_MULTILANGS)) $sql.=" OR pl.label LIKE '".$db->escape($prefix.$crit)."%'";
                $sql.=")";
                $i++;
            }
            if (count($scrit) > 1) $sql.=")";
          	if (! empty($conf->barcode->enabled)) $sql.= " OR p.barcode LIKE '".$db->escape($prefix.$filterkey)."%'";
        	$sql.=')';
        }
        if (count($warehouseStatusArray))
        {
            $sql.= ' GROUP BY'.$selectFields;
        }
        $sql.= $db->order("p.ref");
        $sql.= $db->plimit($limit);

        // Build output string
        dol_syslog(get_class($this)."::select_produits_list search product", LOG_DEBUG);
        $result=$this->db->query($sql);
        if ($result)
        {
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';
            $num = $this->db->num_rows($result);

            $events=null;

            if ($conf->use_javascript_ajax && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$comboenhancement =ajax_combobox($htmlname, $events, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT);
            	$out.= $comboenhancement;
            }

            $out.='<select class="flat'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" id="'.$htmlname.'">';

            $textifempty='';
            // Do not use textifempty = ' ' or '&nbsp;' here, or search on key will search on ' key'.
            //if (! empty($conf->use_javascript_ajax) || $forcecombo) $textifempty='';
            if (! empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
            {
                if ($showempty && ! is_numeric($showempty)) $textifempty=$langs->trans($showempty);
                else $textifempty.=$langs->trans("All");
            }
            if ($showempty) $out.='<option value="0" selected>'.$textifempty.'</option>';

            $i = 0;
            while ($num && $i < $num)
            {
            	$opt = '';
				$optJson = array();
				$objp = $this->db->fetch_object($result);

				if (!empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY) && !empty($objp->price_by_qty) && $objp->price_by_qty == 1)
				{ // Price by quantity will return many prices for the same product
					$sql = "SELECT rowid, quantity, price, unitprice, remise_percent, remise";
					$sql.= " FROM ".MAIN_DB_PREFIX."product_price_by_qty";
					$sql.= " WHERE fk_product_price=".$objp->price_rowid;
					$sql.= " ORDER BY quantity ASC";

					dol_syslog(get_class($this)."::select_produits_list search price by qty", LOG_DEBUG);
					$result2 = $this->db->query($sql);
					if ($result2)
					{
						$nb_prices = $this->db->num_rows($result2);
						$j = 0;
						while ($nb_prices && $j < $nb_prices) {
							$objp2 = $this->db->fetch_object($result2);

							$objp->quantity = $objp2->quantity;
							$objp->price = $objp2->price;
							$objp->unitprice = $objp2->unitprice;
							$objp->remise_percent = $objp2->remise_percent;
							$objp->remise = $objp2->remise;
							$objp->price_by_qty_rowid = $objp2->rowid;

							$this->constructProductListOption($objp, $opt, $optJson, 0, $selected, $hidepriceinlabel);

							$j++;

							// Add new entry
							// "key" value of json key array is used by jQuery automatically as selected value
							// "label" value of json key array is used by jQuery automatically as text for combo box
							$out.=$opt;
							array_push($outarray, $optJson);
						}
					}
				}
				else
				{
                    if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_price_expression)) {
                        $price_product = new Product($this->db);
                        $price_product->fetch($objp->rowid, '', '', 1);
                        $priceparser = new PriceParser($this->db);
                        $price_result = $priceparser->parseProduct($price_product);
                        if ($price_result >= 0) {
                            $objp->price = $price_result;
                            $objp->unitprice = $price_result;
                            //Calculate the VAT
                            $objp->price_ttc = price2num($objp->price) * (1 + ($objp->tva_tx / 100));
                            $objp->price_ttc = price2num($objp->price_ttc,'MU');
                        }
                    }
					$this->constructProductListOption($objp, $opt, $optJson, $price_level, $selected, $hidepriceinlabel);
					// Add new entry
					// "key" value of json key array is used by jQuery automatically as selected value
					// "label" value of json key array is used by jQuery automatically as text for combo box
					$out.=$opt;
					array_push($outarray, $optJson);
				}

                $i++;
            }

            $out.='</select>';

            $this->db->free($result);

            if (empty($outputmode)) return $out;
            return $outarray;
        }
        else
		{
            dol_print_error($db);
        }
    }

    /**
     * constructProductListOption
     *
     * @param 	resultset	$objp			    Resultset of fetch
     * @param 	string		$opt			    Option (var used for returned value in string option format)
     * @param 	string		$optJson		    Option (var used for returned value in json format)
     * @param 	int			$price_level	    Price level
     * @param 	string		$selected		    Preselected value
     * @param   int         $hidepriceinlabel   Hide price in label
     * @return	void
     */
	private function constructProductListOption(&$objp, &$opt, &$optJson, $price_level, $selected, $hidepriceinlabel=0)
	{
		global $langs,$conf,$user,$db;

        $outkey='';
        $outval='';
        $outref='';
        $outlabel='';
        $outdesc='';
        $outbarcode='';
        $outtype='';
        $outprice_ht='';
        $outprice_ttc='';
        $outpricebasetype='';
        $outtva_tx='';
		$outqty=1;
		$outdiscount=0;

		$maxlengtharticle=(empty($conf->global->PRODUCT_MAX_LENGTH_COMBO)?48:$conf->global->PRODUCT_MAX_LENGTH_COMBO);

        $label=$objp->label;
        if (! empty($objp->label_translated)) $label=$objp->label_translated;
        if (! empty($filterkey) && $filterkey != '') $label=preg_replace('/('.preg_quote($filterkey).')/i','<strong>$1</strong>',$label,1);

        $outkey=$objp->rowid;
        $outref=$objp->ref;
        $outlabel=$objp->label;
        $outdesc=$objp->description;
        $outbarcode=$objp->barcode;

        $outtype=$objp->fk_product_type;
        $outdurationvalue=$outtype == Product::TYPE_SERVICE?substr($objp->duration,0,dol_strlen($objp->duration)-1):'';
        $outdurationunit=$outtype == Product::TYPE_SERVICE?substr($objp->duration,-1):'';

        $opt = '<option value="'.$objp->rowid.'"';
        $opt.= ($objp->rowid == $selected)?' selected':'';
		$opt.= (!empty($objp->price_by_qty_rowid) && $objp->price_by_qty_rowid > 0)?' pbq="'.$objp->price_by_qty_rowid.'"':'';
        if (! empty($conf->stock->enabled) && $objp->fk_product_type == 0 && isset($objp->stock))
        {
			if ($objp->stock > 0) $opt.= ' class="product_line_stock_ok"';
			else if ($objp->stock <= 0) $opt.= ' class="product_line_stock_too_low"';
        }
        $opt.= '>';
        $opt.= $objp->ref;
        if ($outbarcode) $opt.=' ('.$outbarcode.')';
        $opt.=' - '.dol_trunc($label,$maxlengtharticle);

        $objRef = $objp->ref;
        if (! empty($filterkey) && $filterkey != '') $objRef=preg_replace('/('.preg_quote($filterkey).')/i','<strong>$1</strong>',$objRef,1);
        $outval.=$objRef;
        if ($outbarcode) $outval.=' ('.$outbarcode.')';
        $outval.=' - '.dol_trunc($label,$maxlengtharticle);

        $found=0;

        // Multiprice
        if (empty($hidepriceinlabel) && $price_level >= 1 && $conf->global->PRODUIT_MULTIPRICES)		// If we need a particular price level (from 1 to 6)
        {
            $sql = "SELECT price, price_ttc, price_base_type, tva_tx";
            $sql.= " FROM ".MAIN_DB_PREFIX."product_price";
            $sql.= " WHERE fk_product='".$objp->rowid."'";
            $sql.= " AND entity IN (".getEntity('productprice').")";
            $sql.= " AND price_level=".$price_level;
            $sql.= " ORDER BY date_price DESC, rowid DESC"; // Warning DESC must be both on date_price and rowid.
            $sql.= " LIMIT 1";

            dol_syslog(get_class($this).'::constructProductListOption search price for level '.$price_level.'', LOG_DEBUG);
            $result2 = $this->db->query($sql);
            if ($result2)
            {
                $objp2 = $this->db->fetch_object($result2);
                if ($objp2)
                {
                    $found=1;
                    if ($objp2->price_base_type == 'HT')
                    {
                        $opt.= ' - '.price($objp2->price,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("HT");
                        $outval.= ' - '.price($objp2->price,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("HT");
                    }
                    else
                    {
                        $opt.= ' - '.price($objp2->price_ttc,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("TTC");
                        $outval.= ' - '.price($objp2->price_ttc,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("TTC");
                    }
                    $outprice_ht=price($objp2->price);
                    $outprice_ttc=price($objp2->price_ttc);
                    $outpricebasetype=$objp2->price_base_type;
                    $outtva_tx=$objp2->tva_tx;
                }
            }
            else
            {
                dol_print_error($this->db);
            }
        }

		// Price by quantity
		if (empty($hidepriceinlabel) && !empty($objp->quantity) && $objp->quantity >= 1 && ! empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY))
		{
			$found = 1;
			$outqty=$objp->quantity;
			$outdiscount=$objp->remise_percent;
			if ($objp->quantity == 1)
			{
				$opt.= ' - '.price($objp->unitprice,1,$langs,0,0,-1,$conf->currency)."/";
				$outval.= ' - '.price($objp->unitprice,0,$langs,0,0,-1,$conf->currency)."/";
				$opt.= $langs->trans("Unit");	// Do not use strtolower because it breaks utf8 encoding
				$outval.=$langs->transnoentities("Unit");
			}
			else
			{
				$opt.= ' - '.price($objp->price,1,$langs,0,0,-1,$conf->currency)."/".$objp->quantity;
				$outval.= ' - '.price($objp->price,0,$langs,0,0,-1,$conf->currency)."/".$objp->quantity;
				$opt.= $langs->trans("Units");	// Do not use strtolower because it breaks utf8 encoding
				$outval.=$langs->transnoentities("Units");
			}

			$outprice_ht=price($objp->unitprice);
            $outprice_ttc=price($objp->unitprice * (1 + ($objp->tva_tx / 100)));
            $outpricebasetype=$objp->price_base_type;
            $outtva_tx=$objp->tva_tx;
		}
		if (empty($hidepriceinlabel) && !empty($objp->quantity) && $objp->quantity >= 1)
		{
			$opt.=" (".price($objp->unitprice,1,$langs,0,0,-1,$conf->currency)."/".$langs->trans("Unit").")";	// Do not use strtolower because it breaks utf8 encoding
			$outval.=" (".price($objp->unitprice,0,$langs,0,0,-1,$conf->currency)."/".$langs->transnoentities("Unit").")";	// Do not use strtolower because it breaks utf8 encoding
		}
		if (empty($hidepriceinlabel) && !empty($objp->remise_percent) && $objp->remise_percent >= 1)
		{
			$opt.=" - ".$langs->trans("Discount")." : ".vatrate($objp->remise_percent).' %';
			$outval.=" - ".$langs->transnoentities("Discount")." : ".vatrate($objp->remise_percent).' %';
		}

		// Price by customer
		if (empty($hidepriceinlabel) && !empty($conf->global->PRODUIT_CUSTOMER_PRICES))
		{
			if (!empty($objp->idprodcustprice))
			{
				$found = 1;

				if ($objp->custprice_base_type == 'HT')
				{
					$opt.= ' - '.price($objp->custprice,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("HT");
					$outval.= ' - '.price($objp->custprice,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("HT");
				}
				else
				{
					$opt.= ' - '.price($objp->custprice_ttc,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("TTC");
					$outval.= ' - '.price($objp->custprice_ttc,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("TTC");
				}

				$outprice_ht=price($objp->custprice);
				$outprice_ttc=price($objp->custprice_ttc);
				$outpricebasetype=$objp->custprice_base_type;
				$outtva_tx=$objp->custtva_tx;
			}
		}

        // If level no defined or multiprice not found, we used the default price
        if (empty($hidepriceinlabel) && ! $found)
        {
            if ($objp->price_base_type == 'HT')
            {
                $opt.= ' - '.price($objp->price,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("HT");
                $outval.= ' - '.price($objp->price,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("HT");
            }
            else
            {
                $opt.= ' - '.price($objp->price_ttc,1,$langs,0,0,-1,$conf->currency).' '.$langs->trans("TTC");
                $outval.= ' - '.price($objp->price_ttc,0,$langs,0,0,-1,$conf->currency).' '.$langs->transnoentities("TTC");
            }
            $outprice_ht=price($objp->price);
            $outprice_ttc=price($objp->price_ttc);
            $outpricebasetype=$objp->price_base_type;
            $outtva_tx=$objp->tva_tx;
        }

        if (! empty($conf->stock->enabled) && isset($objp->stock) && $objp->fk_product_type == 0)
        {
            $opt.= ' - '.$langs->trans("Stock").':'.$objp->stock;

            if ($objp->stock > 0) {
            	$outval.= ' - <span class="product_line_stock_ok">'.$langs->transnoentities("Stock").':'.$objp->stock.'</span>';
            }elseif ($objp->stock <= 0) {
            	$outval.= ' - <span class="product_line_stock_too_low">'.$langs->transnoentities("Stock").':'.$objp->stock.'</span>';
            }
        }

        if ($outdurationvalue && $outdurationunit)
        {
            $da=array("h"=>$langs->trans("Hour"),"d"=>$langs->trans("Day"),"w"=>$langs->trans("Week"),"m"=>$langs->trans("Month"),"y"=>$langs->trans("Year"));
            if (isset($da[$outdurationunit]))
            {
                $key = $da[$outdurationunit].($outdurationvalue > 1?'s':'');
                $opt.= ' - '.$outdurationvalue.' '.$langs->trans($key);
                $outval.=' - '.$outdurationvalue.' '.$langs->transnoentities($key);
            }
        }

        $opt.= "</option>\n";
		$optJson = array('key'=>$outkey, 'value'=>$outref, 'label'=>$outval, 'label2'=>$outlabel, 'desc'=>$outdesc, 'type'=>$outtype, 'price_ht'=>$outprice_ht, 'price_ttc'=>$outprice_ttc, 'pricebasetype'=>$outpricebasetype, 'tva_tx'=>$outtva_tx, 'qty'=>$outqty, 'discount'=>$outdiscount, 'duration_value'=>$outdurationvalue, 'duration_unit'=>$outdurationunit);
	}

    /**
     *	Return list of products for customer (in Ajax if Ajax activated or go to select_produits_fournisseurs_list)
     *
     *	@param	int		$socid			Id third party
     *	@param  string	$selected       Preselected product
     *	@param  string	$htmlname       Name of HTML Select
     *  @param	string	$filtertype     Filter on product type (''=nofilter, 0=product, 1=service)
     *	@param  string	$filtre			For a SQL filter
     *	@param	array	$ajaxoptions	Options for ajax_autocompleter
	 *  @param	int		$hidelabel		Hide label (0=no, 1=yes)
	 *  @param  int     $alsoproductwithnosupplierprice    1=Add also product without supplier prices
     *	@return	void
     */
    function select_produits_fournisseurs($socid, $selected='', $htmlname='productid', $filtertype='', $filtre='', $ajaxoptions=array(), $hidelabel=0, $alsoproductwithnosupplierprice=0)
    {
        global $langs,$conf;
        global $price_level, $status, $finished;

        $selected_input_value='';
        if (! empty($conf->use_javascript_ajax) && ! empty($conf->global->PRODUIT_USE_SEARCH_TO_SELECT))
        {
            if ($selected > 0)
            {
                require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
                $producttmpselect = new Product($this->db);
                $producttmpselect->fetch($selected);
                $selected_input_value=$producttmpselect->ref;
                unset($producttmpselect);
            }

			// mode=2 means suppliers products
            $urloption=($socid > 0?'socid='.$socid.'&':'').'htmlname='.$htmlname.'&outjson=1&price_level='.$price_level.'&type='.$filtertype.'&mode=2&status='.$status.'&finished='.$finished.'&alsoproductwithnosupplierprice='.$alsoproductwithnosupplierprice;
            print ajax_autocompleter($selected, $htmlname, DOL_URL_ROOT.'/product/ajax/products.php', $urloption, $conf->global->PRODUIT_USE_SEARCH_TO_SELECT, 0, $ajaxoptions);
            print ($hidelabel?'':$langs->trans("RefOrLabel").' : ').'<input type="text" size="20" name="search_'.$htmlname.'" id="search_'.$htmlname.'" value="'.$selected_input_value.'">';
        }
        else
        {
        	print $this->select_produits_fournisseurs_list($socid,$selected,$htmlname,$filtertype,$filtre,'',-1,0,0,$alsoproductwithnosupplierprice);
        }
    }

    /**
     *	Return list of suppliers products
     *
     *	@param	int		$socid   		Id societe fournisseur (0 pour aucun filtre)
     *	@param  int		$selected       Produit pre-selectionne
     *	@param  string	$htmlname       Nom de la zone select
     *  @param	string	$filtertype     Filter on product type (''=nofilter, 0=product, 1=service)
     *	@param  string	$filtre         Pour filtre sql
     *	@param  string	$filterkey      Filtre des produits
     *  @param  int		$statut         -1=Return all products, 0=Products not on sell, 1=Products on sell (not used here, a filter on tobuy is already hard coded in request)
     *  @param  int		$outputmode     0=HTML select string, 1=Array
     *  @param  int     $limit          Limit of line number
	 *  @param  int     $alsoproductwithnosupplierprice    1=Add also product without supplier prices
     *  @return array           		Array of keys for json
     */
    function select_produits_fournisseurs_list($socid,$selected='',$htmlname='productid',$filtertype='',$filtre='',$filterkey='',$statut=-1,$outputmode=0,$limit=100,$alsoproductwithnosupplierprice=0)
    {
        global $langs,$conf,$db;

        $out='';
        $outarray=array();

        $langs->load('stocks');

        $sql = "SELECT p.rowid, p.label, p.ref, p.price, p.duration, p.fk_product_type,";
        $sql.= " pfp.ref_fourn, pfp.rowid as idprodfournprice, pfp.price as fprice, pfp.quantity, pfp.remise_percent, pfp.remise, pfp.unitprice,";
        $sql.= " pfp.fk_supplier_price_expression, pfp.fk_product, pfp.tva_tx, pfp.fk_soc, s.nom as name,";
        $sql.= " pfp.supplier_reputation";
        $sql.= " FROM ".MAIN_DB_PREFIX."product as p";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
        if ($socid) $sql.= " AND pfp.fk_soc = ".$socid;
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON pfp.fk_soc = s.rowid";
        $sql.= " WHERE p.entity IN (".getEntity('product').")";
        $sql.= " AND p.tobuy = 1";
        if (strval($filtertype) != '') $sql.=" AND p.fk_product_type=".$this->db->escape($filtertype);
        if (! empty($filtre)) $sql.=" ".$filtre;
        // Add criteria on ref/label
        if ($filterkey != '')
        {
        	$sql.=' AND (';
        	$prefix=empty($conf->global->PRODUCT_DONOTSEARCH_ANYWHERE)?'%':'';	// Can use index if PRODUCT_DONOTSEARCH_ANYWHERE is on
        	// For natural search
        	$scrit = explode(' ', $filterkey);
        	$i=0;
        	if (count($scrit) > 1) $sql.="(";
        	foreach ($scrit as $crit)
        	{
        		if ($i > 0) $sql.=" AND ";
        		$sql.="(pfp.ref_fourn LIKE '".$this->db->escape($prefix.$crit)."%' OR p.ref LIKE '".$this->db->escape($prefix.$crit)."%' OR p.label LIKE '".$this->db->escape($prefix.$crit)."%')";
        		$i++;
        	}
        	if (count($scrit) > 1) $sql.=")";
        	if (! empty($conf->barcode->enabled)) $sql.= " OR p.barcode LIKE '".$this->db->escape($prefix.$filterkey)."%'";
        	$sql.=')';
        }
        $sql.= " ORDER BY pfp.ref_fourn DESC, pfp.quantity ASC";
        $sql.= $db->plimit($limit);

        // Build output string

        dol_syslog(get_class($this)."::select_produits_fournisseurs_list", LOG_DEBUG);
        $result=$this->db->query($sql);
        if ($result)
        {
            require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';

            $num = $this->db->num_rows($result);

            //$out.='<select class="flat" id="select'.$htmlname.'" name="'.$htmlname.'">';	// remove select to have id same with combo and ajax
            $out.='<select class="flat maxwidthonsmartphone" id="'.$htmlname.'" name="'.$htmlname.'">';
            if (! $selected) $out.='<option value="0" selected>&nbsp;</option>';
            else $out.='<option value="0">&nbsp;</option>';

            $i = 0;
            while ($i < $num)
            {
                $objp = $this->db->fetch_object($result);

                $outkey=$objp->idprodfournprice;                                                    // id in table of price
                if (! $outkey && $alsoproductwithnosupplierprice) $outkey='idprod_'.$objp->rowid;   // id of product

                $outref=$objp->ref;
                $outval='';
                $outqty=1;
				$outdiscount=0;
                $outtype=$objp->fk_product_type;
                $outdurationvalue=$outtype == Product::TYPE_SERVICE?substr($objp->duration,0,dol_strlen($objp->duration)-1):'';
                $outdurationunit=$outtype == Product::TYPE_SERVICE?substr($objp->duration,-1):'';

                $opt = '<option value="'.$outkey.'"';
                if ($selected && $selected == $objp->idprodfournprice) $opt.= ' selected';
                if (empty($objp->idprodfournprice) && empty($alsoproductwithnosupplierprice)) $opt.=' disabled';
                $opt.= '>';

                $objRef = $objp->ref;
                if ($filterkey && $filterkey != '') $objRef=preg_replace('/('.preg_quote($filterkey).')/i','<strong>$1</strong>',$objRef,1);
                $objRefFourn = $objp->ref_fourn;
                if ($filterkey && $filterkey != '') $objRefFourn=preg_replace('/('.preg_quote($filterkey).')/i','<strong>$1</strong>',$objRefFourn,1);
                $label = $objp->label;
                if ($filterkey && $filterkey != '') $label=preg_replace('/('.preg_quote($filterkey).')/i','<strong>$1</strong>',$label,1);

                $opt.=$objp->ref;
                if (! empty($objp->idprodfournprice) && ($objp->ref != $objp->ref_fourn))
                	$opt.=' ('.$objp->ref_fourn.')';
                $opt.=' - ';
                $outval.=$objRef;
                if (! empty($objp->idprodfournprice) && ($objp->ref != $objp->ref_fourn))
                	$outval.=' ('.$objRefFourn.')';
                $outval.=' - ';
                $opt.=dol_trunc($label, 72).' - ';
                $outval.=dol_trunc($label, 72).' - ';

                if (! empty($objp->idprodfournprice))
                {
                    $outqty=$objp->quantity;
					$outdiscount=$objp->remise_percent;
                    if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_supplier_price_expression)) {
                        $prod_supplier = new ProductFournisseur($this->db);
                        $prod_supplier->product_fourn_price_id = $objp->idprodfournprice;
                        $prod_supplier->id = $objp->fk_product;
                        $prod_supplier->fourn_qty = $objp->quantity;
                        $prod_supplier->fourn_tva_tx = $objp->tva_tx;
                        $prod_supplier->fk_supplier_price_expression = $objp->fk_supplier_price_expression;
                        $priceparser = new PriceParser($this->db);
                        $price_result = $priceparser->parseProductSupplier($prod_supplier);
                        if ($price_result >= 0) {
                            $objp->fprice = $price_result;
                            if ($objp->quantity >= 1)
                            {
                                $objp->unitprice = $objp->fprice / $objp->quantity;
                            }
                        }
                    }
                    if ($objp->quantity == 1)
                    {
	                    $opt.= price($objp->fprice,1,$langs,0,0,-1,$conf->currency)."/";
                    	$outval.= price($objp->fprice,0,$langs,0,0,-1,$conf->currency)."/";
                    	$opt.= $langs->trans("Unit");	// Do not use strtolower because it breaks utf8 encoding
                        $outval.=$langs->transnoentities("Unit");
                    }
                    else
                    {
    	                $opt.= price($objp->fprice,1,$langs,0,0,-1,$conf->currency)."/".$objp->quantity;
	                    $outval.= price($objp->fprice,0,$langs,0,0,-1,$conf->currency)."/".$objp->quantity;
                    	$opt.= ' '.$langs->trans("Units");	// Do not use strtolower because it breaks utf8 encoding
                        $outval.= ' '.$langs->transnoentities("Units");
                    }

                    if ($objp->quantity >= 1)
                    {
                        $opt.=" (".price($objp->unitprice,1,$langs,0,0,-1,$conf->currency)."/".$langs->trans("Unit").")";	// Do not use strtolower because it breaks utf8 encoding
                        $outval.=" (".price($objp->unitprice,0,$langs,0,0,-1,$conf->currency)."/".$langs->transnoentities("Unit").")";	// Do not use strtolower because it breaks utf8 encoding
                    }
					if ($objp->remise_percent >= 1)
                    {
                        $opt.=" - ".$langs->trans("Discount")." : ".vatrate($objp->remise_percent).' %';
                        $outval.=" - ".$langs->transnoentities("Discount")." : ".vatrate($objp->remise_percent).' %';
                    }
                    if ($objp->duration)
                    {
                        $opt .= " - ".$objp->duration;
                        $outval.=" - ".$objp->duration;
                    }
                    if (! $socid)
                    {
                        $opt .= " - ".dol_trunc($objp->name,8);
                        $outval.=" - ".dol_trunc($objp->name,8);
                    }
                    if ($objp->supplier_reputation)
                    {
            			//TODO dictionary
            			$reputations=array(''=>$langs->trans('Standard'),'FAVORITE'=>$langs->trans('Favorite'),'NOTTHGOOD'=>$langs->trans('NotTheGoodQualitySupplier'), 'DONOTORDER'=>$langs->trans('DoNotOrderThisProductToThisSupplier'));

                        $opt .= " - ".$reputations[$objp->supplier_reputation];
                        $outval.=" - ".$reputations[$objp->supplier_reputation];
                    }
                }
                else
                {
                    if (empty($alsoproductwithnosupplierprice))     // No supplier price defined for couple product/supplier
                    {
                        $opt.= $langs->trans("NoPriceDefinedForThisSupplier");
                        $outval.=$langs->transnoentities("NoPriceDefinedForThisSupplier");
                    }
                    else                                            // No supplier price defined for product, even on other suppliers
                    {
                        $opt.= $langs->trans("NoPriceDefinedForThisSupplier");
                        $outval.=$langs->transnoentities("NoPriceDefinedForThisSupplier");
                    }
                }
                $opt .= "</option>\n";


                // Add new entry
                // "key" value of json key array is used by jQuery automatically as selected value
                // "label" value of json key array is used by jQuery automatically as text for combo box
                $out.=$opt;
                array_push($outarray, array('key'=>$outkey, 'value'=>$outref, 'label'=>$outval, 'qty'=>$outqty, 'discount'=>$outdiscount, 'type'=>$outtype, 'duration_value'=>$outdurationvalue, 'duration_unit'=>$outdurationunit, 'disabled'=>(empty($objp->idprodfournprice)?true:false)));
				// Exemple of var_dump $outarray
				// array(1) {[0]=>array(6) {[key"]=>string(1) "2" ["value"]=>string(3) "ppp"
				//           ["label"]=>string(76) "ppp (<strong>f</strong>ff2) - ppp - 20,00 Euros/1unité (20,00 Euros/unité)"
				//      	 ["qty"]=>string(1) "1" ["discount"]=>string(1) "0" ["disabled"]=>bool(false)
                //}
                //var_dump($outval); var_dump(utf8_check($outval)); var_dump(json_encode($outval));
                //$outval=array('label'=>'ppp (<strong>f</strong>ff2) - ppp - 20,00 Euros/ Unité (20,00 Euros/unité)');
                //var_dump($outval); var_dump(utf8_check($outval)); var_dump(json_encode($outval));

                $i++;
            }
            $out.='</select>';

            $this->db->free($result);

            if (empty($outputmode)) return $out;
            return $outarray;
        }
        else
        {
            dol_print_error($this->db);
        }
    }

    /**
     *	Return list of suppliers prices for a product
     *
     *  @param	    int		$productid       	Id of product
     *  @param      string	$htmlname        	Name of HTML field
     *  @param      int		$selected_supplier  Pre-selected supplier if more than 1 result
     *  @return	    void
     */
    function select_product_fourn_price($productid, $htmlname='productfournpriceid', $selected_supplier='')
    {
        global $langs,$conf;

        $langs->load('stocks');

        $sql = "SELECT p.rowid, p.label, p.ref, p.price, p.duration, pfp.fk_soc,";
        $sql.= " pfp.ref_fourn, pfp.rowid as idprodfournprice, pfp.price as fprice, pfp.quantity, pfp.unitprice,";
        $sql.= " pfp.fk_supplier_price_expression, pfp.fk_product, pfp.tva_tx, s.nom as name";
        $sql.= " FROM ".MAIN_DB_PREFIX."product as p";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as pfp ON p.rowid = pfp.fk_product";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON pfp.fk_soc = s.rowid";
        $sql.= " WHERE p.entity IN (".getEntity('productprice').")";
        $sql.= " AND p.tobuy = 1";
        $sql.= " AND s.fournisseur = 1";
        $sql.= " AND p.rowid = ".$productid;
        $sql.= " ORDER BY s.nom, pfp.ref_fourn DESC";

        dol_syslog(get_class($this)."::select_product_fourn_price", LOG_DEBUG);
        $result=$this->db->query($sql);

        if ($result)
        {
            $num = $this->db->num_rows($result);

            $form = '<select class="flat" name="'.$htmlname.'">';

            if (! $num)
            {
                $form.= '<option value="0">-- '.$langs->trans("NoSupplierPriceDefinedForThisProduct").' --</option>';
            }
            else
            {
                require_once DOL_DOCUMENT_ROOT.'/product/dynamic_price/class/price_parser.class.php';
                $form.= '<option value="0">&nbsp;</option>';

                $i = 0;
                while ($i < $num)
                {
                    $objp = $this->db->fetch_object($result);

                    $opt = '<option value="'.$objp->idprodfournprice.'"';
                    //if there is only one supplier, preselect it
                    if($num == 1 || ($selected_supplier > 0 && $objp->fk_soc == $selected_supplier)) {
                        $opt .= ' selected';
                    }
                    $opt.= '>'.$objp->name.' - '.$objp->ref_fourn.' - ';

                    if (!empty($conf->dynamicprices->enabled) && !empty($objp->fk_supplier_price_expression)) {
                        $prod_supplier = new ProductFournisseur($this->db);
                        $prod_supplier->product_fourn_price_id = $objp->idprodfournprice;
                        $prod_supplier->id = $productid;
                        $prod_supplier->fourn_qty = $objp->quantity;
                        $prod_supplier->fourn_tva_tx = $objp->tva_tx;
                        $prod_supplier->fk_supplier_price_expression = $objp->fk_supplier_price_expression;
                        $priceparser = new PriceParser($this->db);
                        $price_result = $priceparser->parseProductSupplier($prod_supplier);
                        if ($price_result >= 0) {
                            $objp->fprice = $price_result;
                            if ($objp->quantity >= 1)
                            {
                                $objp->unitprice = $objp->fprice / $objp->quantity;
                            }
                        }
                    }
                    if ($objp->quantity == 1)
                    {
                        $opt.= price($objp->fprice,1,$langs,0,0,-1,$conf->currency)."/";
                    }

                    $opt.= $objp->quantity.' ';

                    if ($objp->quantity == 1)
                    {
                        $opt.= $langs->trans("Unit");
                    }
                    else
                    {
                        $opt.= $langs->trans("Units");
                    }
                    if ($objp->quantity > 1)
                    {
                        $opt.=" - ";
                        $opt.= price($objp->unitprice,1,$langs,0,0,-1,$conf->currency)."/".$langs->trans("Unit");
                    }
                    if ($objp->duration) $opt .= " - ".$objp->duration;
                    $opt .= "</option>\n";

                    $form.= $opt;
                    $i++;
                }
            }

            $form.= '</select>';
            $this->db->free($result);
            return $form;
        }
        else
        {
            dol_print_error($this->db);
        }
    }

    /**
     *    Return list of delivery address
     *
     *    @param    string	$selected          	Id contact pre-selectionn
     *    @param    int		$socid				Id of company
     *    @param    string	$htmlname          	Name of HTML field
     *    @param    int		$showempty         	Add an empty field
     *    @return	integer|null
     */
    function select_address($selected, $socid, $htmlname='address_id',$showempty=0)
    {
        // On recherche les utilisateurs
        $sql = "SELECT a.rowid, a.label";
        $sql .= " FROM ".MAIN_DB_PREFIX ."societe_address as a";
        $sql .= " WHERE a.fk_soc = ".$socid;
        $sql .= " ORDER BY a.label ASC";

        dol_syslog(get_class($this)."::select_address", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            print '<select class="flat" name="'.$htmlname.'">';
            if ($showempty) print '<option value="0">&nbsp;</option>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);

                    if ($selected && $selected == $obj->rowid)
                    {
                        print '<option value="'.$obj->rowid.'" selected>'.$obj->label.'</option>';
                    }
                    else
                    {
                        print '<option value="'.$obj->rowid.'">'.$obj->label.'</option>';
                    }
                    $i++;
                }
            }
            print '</select>';
            return $num;
        }
        else
        {
            dol_print_error($this->db);
        }
    }


    /**
     *      Load into cache list of payment terms
     *
     *      @return     int             Nb of lines loaded, <0 if KO
     */
    function load_cache_conditions_paiements()
    {
        global $langs;

        $num = count($this->cache_conditions_paiements);
        if ($num > 0) return 0;    // Cache already loaded

        dol_syslog(__METHOD__, LOG_DEBUG);

        $sql = "SELECT rowid, code, libelle as label";
        $sql.= " FROM ".MAIN_DB_PREFIX.'c_payment_term';
        $sql.= " WHERE active > 0";
        $sql.= " ORDER BY sortorder";

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                // Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
                $label=($langs->trans("PaymentConditionShort".$obj->code)!=("PaymentConditionShort".$obj->code)?$langs->trans("PaymentConditionShort".$obj->code):($obj->label!='-'?$obj->label:''));
                $this->cache_conditions_paiements[$obj->rowid]['code'] =$obj->code;
                $this->cache_conditions_paiements[$obj->rowid]['label']=$label;
                $i++;
            }

			//$this->cache_conditions_paiements=dol_sort_array($this->cache_conditions_paiements, 'label', 'asc', 0, 0, 1);		// We use the field sortorder of table

            return $num;
        }
        else
		{
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *      Charge dans cache la liste des délais de livraison possibles
     *
     *      @return     int             Nb of lines loaded, <0 if KO
     */
    function load_cache_availability()
    {
        global $langs;

        $num = count($this->cache_availability);
        if ($num > 0) return 0;    // Cache already loaded

        dol_syslog(__METHOD__, LOG_DEBUG);

		$langs->load('propal');

        $sql = "SELECT rowid, code, label";
        $sql.= " FROM ".MAIN_DB_PREFIX.'c_availability';
        $sql.= " WHERE active > 0";

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                // Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
                $label=($langs->trans("AvailabilityType".$obj->code)!=("AvailabilityType".$obj->code)?$langs->trans("AvailabilityType".$obj->code):($obj->label!='-'?$obj->label:''));
                $this->cache_availability[$obj->rowid]['code'] =$obj->code;
                $this->cache_availability[$obj->rowid]['label']=$label;
                $i++;
            }

            $this->cache_availability = dol_sort_array($this->cache_availability, 'label', 'asc', 0, 0, 1);

            return $num;
        }
        else
		{
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *      Retourne la liste des types de delais de livraison possibles
     *
     *      @param	int		$selected        Id du type de delais pre-selectionne
     *      @param  string	$htmlname        Nom de la zone select
     *      @param  string	$filtertype      To add a filter
     *		@param	int		$addempty		Add empty entry
     *		@return	void
     */
    function selectAvailabilityDelay($selected='',$htmlname='availid',$filtertype='',$addempty=0)
    {
        global $langs,$user;

        $this->load_cache_availability();

        dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

        print '<select class="flat" name="'.$htmlname.'">';
        if ($addempty) print '<option value="0">&nbsp;</option>';
        foreach($this->cache_availability as $id => $arrayavailability)
        {
            if ($selected == $id)
            {
                print '<option value="'.$id.'" selected>';
            }
            else
            {
                print '<option value="'.$id.'">';
            }
            print $arrayavailability['label'];
            print '</option>';
        }
        print '</select>';
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }

    /**
     *      Load into cache cache_demand_reason, array of input reasons
     *
     *      @return     int             Nb of lines loaded, <0 if KO
     */
    function loadCacheInputReason()
    {
        global $langs;

        $num = count($this->cache_demand_reason);
        if ($num > 0) return 0;    // Cache already loaded

        $sql = "SELECT rowid, code, label";
        $sql.= " FROM ".MAIN_DB_PREFIX.'c_input_reason';
        $sql.= " WHERE active > 0";

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            $tmparray=array();
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                // Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
                $label=($langs->trans("DemandReasonType".$obj->code)!=("DemandReasonType".$obj->code)?$langs->trans("DemandReasonType".$obj->code):($obj->label!='-'?$obj->label:''));
                $tmparray[$obj->rowid]['id']   =$obj->rowid;
                $tmparray[$obj->rowid]['code'] =$obj->code;
                $tmparray[$obj->rowid]['label']=$label;
                $i++;
            }

            $this->cache_demand_reason=dol_sort_array($tmparray, 'label', 'asc', 0, 0, 1);

            unset($tmparray);
            return $num;
        }
        else
		{
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
	 *	Return list of input reason (events that triggered an object creation, like after sending an emailing, making an advert, ...)
	 *  List found into table c_input_reason loaded by loadCacheInputReason
     *
     *  @param	int		$selected        Id or code of type origin to select by default
     *  @param  string	$htmlname        Nom de la zone select
     *  @param  string	$exclude         To exclude a code value (Example: SRC_PROP)
     *	@param	int		$addempty		 Add an empty entry
     *	@return	void
     */
    function selectInputReason($selected='',$htmlname='demandreasonid',$exclude='',$addempty=0)
    {
        global $langs,$user;

        $this->loadCacheInputReason();

        print '<select class="flat" name="'.$htmlname.'">';
        if ($addempty) print '<option value="0"'.(empty($selected)?' selected':'').'>&nbsp;</option>';
        foreach($this->cache_demand_reason as $id => $arraydemandreason)
        {
            if ($arraydemandreason['code']==$exclude) continue;

            if ($selected && ($selected == $arraydemandreason['id'] || $selected == $arraydemandreason['code']))
            {
                print '<option value="'.$arraydemandreason['id'].'" selected>';
            }
            else
            {
                print '<option value="'.$arraydemandreason['id'].'">';
            }
            print $arraydemandreason['label'];
            print '</option>';
        }
        print '</select>';
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }

    /**
     *      Charge dans cache la liste des types de paiements possibles
     *
     *      @return     int                 Nb of lines loaded, <0 if KO
     */
    function load_cache_types_paiements()
    {
        global $langs;

        $num=count($this->cache_types_paiements);
        if ($num > 0) return $num;    // Cache already loaded

        dol_syslog(__METHOD__, LOG_DEBUG);

        $this->cache_types_paiements = array();

        $sql = "SELECT id, code, libelle as label, type, active";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_paiement";
        //if ($active >= 0) $sql.= " WHERE active = ".$active;

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                // Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
                $label=($langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code)!=("PaymentTypeShort".$obj->code)?$langs->transnoentitiesnoconv("PaymentTypeShort".$obj->code):($obj->label!='-'?$obj->label:''));
                $this->cache_types_paiements[$obj->id]['id'] =$obj->id;
                $this->cache_types_paiements[$obj->id]['code'] =$obj->code;
                $this->cache_types_paiements[$obj->id]['label']=$label;
                $this->cache_types_paiements[$obj->id]['type'] =$obj->type;
                $this->cache_types_paiements[$obj->id]['active'] =$obj->active;
                $i++;
            }

            $this->cache_types_paiements = dol_sort_array($this->cache_types_paiements, 'label', 'asc', 0, 0, 1);

            return $num;
        }
        else
		{
            dol_print_error($this->db);
            return -1;
        }
    }


    /**
     *      Return list of payment modes.
     *      Constant MAIN_DEFAULT_PAYMENT_TERM_ID can used to set default value but scope is all application, probably not what you want.
     *      See instead to force the default value by the caller.
     *
     *      @param	int  	$selected        Id of payment term to preselect by default
     *      @param  string	$htmlname        Nom de la zone select
     *      @param  int 	$filtertype      Not used
     *		@param	int		$addempty		 Add an empty entry
     *		@return	void
     */
    function select_conditions_paiements($selected=0, $htmlname='condid', $filtertype=-1, $addempty=0)
    {
        global $langs, $user, $conf;

        dol_syslog(__METHOD__." selected=".$selected.", htmlname=".$htmlname, LOG_DEBUG);

        $this->load_cache_conditions_paiements();

        // Set default value if not already set by caller
        if (empty($selected) && ! empty($conf->global->MAIN_DEFAULT_PAYMENT_TERM_ID)) $selected = $conf->global->MAIN_DEFAULT_PAYMENT_TERM_ID;

        print '<select class="flat" name="'.$htmlname.'">';
        if ($addempty) print '<option value="0">&nbsp;</option>';
        foreach($this->cache_conditions_paiements as $id => $arrayconditions)
        {
            if ($selected == $id)
            {
                print '<option value="'.$id.'" selected>';
            }
            else
            {
                print '<option value="'.$id.'">';
            }
            print $arrayconditions['label'];
            print '</option>';
        }
        print '</select>';
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }


    /**
     *      Return list of payment methods
     *
     *      @param	string	$selected       Id du mode de paiement pre-selectionne
     *      @param  string	$htmlname       Nom de la zone select
     *      @param  string	$filtertype     To filter on field type in llx_c_paiement ('CRDT' or 'DBIT' or array('code'=>xx,'label'=>zz))
     *      @param  int		$format         0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
     *      @param  int		$empty			1=peut etre vide, 0 sinon
     * 		@param	int		$noadmininfo	0=Add admin info, 1=Disable admin info
     *      @param  int		$maxlength      Max length of label
     *      @param  int     $active         Active or not, -1 = all
     *      @param  string  $morecss        Add more css
     * 		@return	void
     */
    function select_types_paiements($selected='', $htmlname='paiementtype', $filtertype='', $format=0, $empty=0, $noadmininfo=0, $maxlength=0, $active=1, $morecss='')
    {
        global $langs,$user;

        dol_syslog(__METHOD__." ".$selected.", ".$htmlname.", ".$filtertype.", ".$format, LOG_DEBUG);

        $filterarray=array();
        if ($filtertype == 'CRDT')  	$filterarray=array(0,2,3);
        elseif ($filtertype == 'DBIT') 	$filterarray=array(1,2,3);
        elseif ($filtertype != '' && $filtertype != '-1') $filterarray=explode(',',$filtertype);

        $this->load_cache_types_paiements();

        print '<select id="select'.$htmlname.'" class="flat selectpaymenttypes'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'">';
        if ($empty) print '<option value="">&nbsp;</option>';
        foreach($this->cache_types_paiements as $id => $arraytypes)
        {
            // If not good status
            if ($active >= 0 && $arraytypes['active'] != $active) continue;

            // On passe si on a demande de filtrer sur des modes de paiments particuliers
            if (count($filterarray) && ! in_array($arraytypes['type'],$filterarray)) continue;

            // We discard empty line if showempty is on because an empty line has already been output.
            if ($empty && empty($arraytypes['code'])) continue;

            if ($format == 0) print '<option value="'.$id.'"';
            if ($format == 1) print '<option value="'.$arraytypes['code'].'"';
            if ($format == 2) print '<option value="'.$arraytypes['code'].'"';
            if ($format == 3) print '<option value="'.$id.'"';
            // Si selected est text, on compare avec code, sinon avec id
            if (preg_match('/[a-z]/i', $selected) && $selected == $arraytypes['code']) print ' selected';
            elseif ($selected == $id) print ' selected';
            print '>';
            if ($format == 0) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
            if ($format == 1) $value=$arraytypes['code'];
            if ($format == 2) $value=($maxlength?dol_trunc($arraytypes['label'],$maxlength):$arraytypes['label']);
            if ($format == 3) $value=$arraytypes['code'];
            print $value?$value:'&nbsp;';
            print '</option>';
        }
        print '</select>';
        if ($user->admin && ! $noadmininfo) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }


    /**
     *  Selection HT or TTC
     *
     *  @param	string	$selected       Id pre-selectionne
     *  @param  string	$htmlname       Nom de la zone select
     * 	@return	string					Code of HTML select to chose tax or not
     */
    function selectPriceBaseType($selected='',$htmlname='price_base_type')
    {
        global $langs;

        $return='';

        $return.= '<select class="flat" name="'.$htmlname.'">';
        $options = array(
			'HT'=>$langs->trans("HT"),
			'TTC'=>$langs->trans("TTC")
        );
        foreach($options as $id => $value)
        {
            if ($selected == $id)
            {
                $return.= '<option value="'.$id.'" selected>'.$value;
            }
            else
            {
                $return.= '<option value="'.$id.'">'.$value;
            }
            $return.= '</option>';
        }
        $return.= '</select>';

        return $return;
    }

    /**
     *  Return a HTML select list of shipping mode
     *
     *  @param	string	$selected          Id shipping mode pre-selected
     *  @param  string	$htmlname          Name of select zone
     *  @param  string	$filtre            To filter list
     *  @param  int		$useempty          1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
     *  @param  string	$moreattrib        To add more attribute on select
     * 	@return	void
     */
    function selectShippingMethod($selected='',$htmlname='shipping_method_id',$filtre='',$useempty=0,$moreattrib='')
    {
        global $langs, $conf, $user;

        $langs->load("admin");
        $langs->load("deliveries");

        $sql = "SELECT rowid, code, libelle as label";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_shipment_mode";
        $sql.= " WHERE active > 0";
        if ($filtre) $sql.=" AND ".$filtre;
        $sql.= " ORDER BY libelle ASC";

        dol_syslog(get_class($this)."::selectShippingMode", LOG_DEBUG);
        $result = $this->db->query($sql);
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            if ($num) {
                print '<select id="select'.$htmlname.'" class="flat selectshippingmethod" name="'.$htmlname.'"'.($moreattrib?' '.$moreattrib:'').'>';
                if ($useempty == 1 || ($useempty == 2 && $num > 1)) {
                    print '<option value="-1">&nbsp;</option>';
                }
                while ($i < $num) {
                    $obj = $this->db->fetch_object($result);
                    if ($selected == $obj->rowid) {
                        print '<option value="'.$obj->rowid.'" selected>';
                    } else {
                        print '<option value="'.$obj->rowid.'">';
                    }
                    print ($langs->trans("SendingMethod".strtoupper($obj->code)) != "SendingMethod".strtoupper($obj->code)) ? $langs->trans("SendingMethod".strtoupper($obj->code)) : $obj->label;
                    print '</option>';
                    $i++;
                }
                print "</select>";
                if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
            } else {
                print $langs->trans("NoShippingMethodDefined");
            }
        } else {
            dol_print_error($this->db);
        }
    }

    /**
     *    Display form to select shipping mode
     *
     *    @param	string	$page        Page
     *    @param    int		$selected    Id of shipping mode
     *    @param    string	$htmlname    Name of select html field
     *    @param    int		$addempty    1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
     *    @return	void
     */
    function formSelectShippingMethod($page, $selected='', $htmlname='shipping_method_id', $addempty=0)
    {
        global $langs, $db;

        $langs->load("deliveries");

        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setshippingmethod">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->selectShippingMethod($selected, $htmlname, '', $addempty);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        } else {
            if ($selected) {
                $code=$langs->getLabelFromKey($db, $selected, 'c_shipment_mode', 'rowid', 'code');
                print $langs->trans("SendingMethod".strtoupper($code));
            } else {
                print "&nbsp;";
            }
        }
    }

	/**
	 * Creates HTML last in cycle situation invoices selector
	 *
	 * @param     string  $selected   		Preselected ID
	 * @param     int     $socid      		Company ID
	 *
	 * @return    string                     HTML select
	 */
	function selectSituationInvoices($selected = '', $socid = 0)
	{
		global $langs;

		$langs->load('bills');

		$opt = '<option value ="" selected></option>';
		$sql = 'SELECT rowid, facnumber, situation_cycle_ref, situation_counter, situation_final, fk_soc FROM ' . MAIN_DB_PREFIX . 'facture WHERE situation_counter>=1';
		$sql.= ' ORDER by situation_cycle_ref, situation_counter desc';
		$resql = $this->db->query($sql);
		if ($resql && $this->db->num_rows($resql) > 0) {
			// Last seen cycle
			$ref = 0;
			while ($res = $this->db->fetch_array($resql, MYSQL_NUM)) {
				//Same company ?
				if ($socid == $res[5]) {
					//Same cycle ?
					if ($res[2] != $ref) {
						// Just seen this cycle
						$ref = $res[2];
						//not final ?
						if ($res[4] != 1) {
							//Not prov?
							if (substr($res[1], 1, 4) != 'PROV') {
								if ($selected == $res[0]) {
									$opt .= '<option value="' . $res[0] . '" selected>' . $res[1] . '</option>';
								} else {
									$opt .= '<option value="' . $res[0] . '">' . $res[1] . '</option>';
								}
							}
						}
					}
				}
			}
		}
		else
		{
				dol_syslog("Error sql=" . $sql . ", error=" . $this->error, LOG_ERR);
		}
		if ($opt == '<option value ="" selected></option>')
		{
			$opt = '<option value ="0" selected>' . $langs->trans('NoSituations') . '</option>';
		}
		return $opt;
	}

    /**
     *      Creates HTML units selector (code => label)
     *
     *      @param	string	$selected       Preselected Unit ID
     *      @param  string	$htmlname       Select name
     *      @param	int		$showempty		Add a nempty line
     * 		@return	string                  HTML select
     */
    function selectUnits($selected = '', $htmlname = 'units', $showempty=0)
    {
        global $langs;

        $langs->load('products');

        $return= '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';

        $sql = 'SELECT rowid, label, code from '.MAIN_DB_PREFIX.'c_units';
        $sql.= ' WHERE active > 0';

        $resql = $this->db->query($sql);
        if($resql && $this->db->num_rows($resql) > 0)
        {
	        if ($showempty) $return .= '<option value="none"></option>';

            while($res = $this->db->fetch_object($resql))
            {
                if ($selected == $res->rowid)
                {
                    $return.='<option value="'.$res->rowid.'" selected>'.($langs->trans('unit'.$res->code)!=$res->label?$langs->trans('unit'.$res->code):$res->label).'</option>';
                }
                else
                {
                    $return.='<option value="'.$res->rowid.'">'.($langs->trans('unit'.$res->code)!=$res->label?$langs->trans('unit'.$res->code):$res->label).'</option>';
                }
            }
            $return.='</select>';
        }
        return $return;
    }

    /**
     *  Return a HTML select list of bank accounts
     *
     *  @param	string	$selected          Id account pre-selected
     *  @param  string	$htmlname          Name of select zone
     *  @param  int		$statut            Status of searched accounts (0=open, 1=closed, 2=both)
     *  @param  string	$filtre            To filter list
     *  @param  int		$useempty          1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
     *  @param  string	$moreattrib        To add more attribute on select
     * 	@return	void
     */
    function select_comptes($selected='',$htmlname='accountid',$statut=0,$filtre='',$useempty=0,$moreattrib='')
    {
        global $langs, $conf;

        $langs->load("admin");

        $sql = "SELECT rowid, label, bank, clos as status";
        $sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
        $sql.= " WHERE entity IN (".getEntity('bank_account').")";
        if ($statut != 2) $sql.= " AND clos = '".$statut."'";
        if ($filtre) $sql.=" AND ".$filtre;
        $sql.= " ORDER BY label";

        dol_syslog(get_class($this)."::select_comptes", LOG_DEBUG);
        $result = $this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            if ($num)
            {
                print '<select id="select'.$htmlname.'" class="flat selectbankaccount" name="'.$htmlname.'"'.($moreattrib?' '.$moreattrib:'').'>';
                if ($useempty == 1 || ($useempty == 2 && $num > 1))
                {
                    print '<option value="-1">&nbsp;</option>';
                }

                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($result);
                    if ($selected == $obj->rowid)
                    {
                        print '<option value="'.$obj->rowid.'" selected>';
                    }
                    else
                    {
                        print '<option value="'.$obj->rowid.'">';
                    }
                    print trim($obj->label);
                    if ($statut == 2 && $obj->status == 1) print ' ('.$langs->trans("Closed").')';
                    print '</option>';
                    $i++;
                }
                print "</select>";
            }
            else
            {
                print $langs->trans("NoActiveBankAccountDefined");
            }
        }
        else {
            dol_print_error($this->db);
        }
    }

    /**
     *    Display form to select bank account
     *
     *    @param	string	$page        Page
     *    @param    int		$selected    Id of bank account
     *    @param    string	$htmlname    Name of select html field
     *    @param    int		$addempty    1=Add an empty value in list, 2=Add an empty value in list only if there is more than 2 entries.
     *    @return	void
     */
    function formSelectAccount($page, $selected='', $htmlname='fk_account', $addempty=0)
    {
        global $langs;
        if ($htmlname != "none") {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setbankaccount">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->select_comptes($selected, $htmlname, 0, '', $addempty);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        } else {

        	$langs->load('banks');

            if ($selected) {
                require_once DOL_DOCUMENT_ROOT .'/compta/bank/class/account.class.php';
                $bankstatic=new Account($this->db);
                $bankstatic->fetch($selected);
                print $this->textwithpicto($bankstatic->getNomUrl(1),$langs->trans("AccountCurrency").'&nbsp;'.$bankstatic->currency_code);
            } else {
                print "&nbsp;";
            }
        }
    }

    /**
     *    Return list of categories having choosed type
     *
     *    @param	int		$type				Type of category ('customer', 'supplier', 'contact', 'product', 'member'). Old mode (0, 1, 2, ...) is deprecated.
     *    @param    string	$selected    		Id of category preselected or 'auto' (autoselect category if there is only one element)
     *    @param    string	$htmlname			HTML field name
     *    @param    int		$maxlength      	Maximum length for labels
     *    @param    int		$excludeafterid 	Exclude all categories after this leaf in category tree.
     *    @param	int		$outputmode			0=HTML select string, 1=Array
     *    @return	string
     *    @see select_categories
     */
    function select_all_categories($type, $selected='', $htmlname="parent", $maxlength=64, $excludeafterid=0, $outputmode=0)
    {
        global $conf, $langs;
        $langs->load("categories");

		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		// For backward compatibility
		if (is_numeric($type))
		{
		    dol_syslog(__METHOD__ . ': using numeric value for parameter type is deprecated. Use string code instead.', LOG_WARNING);
		}

		if ($type === Categorie::TYPE_BANK_LINE)
		{
		    // TODO Move this into common category feature
		    $categids=array();
		    $sql = "SELECT c.label, c.rowid";
		    $sql.= " FROM ".MAIN_DB_PREFIX."bank_categ as c";
		    $sql.= " WHERE entity = ".$conf->entity;
		    $sql.= " ORDER BY c.label";
		    $result = $this->db->query($sql);
		    if ($result)
		    {
		        $num = $this->db->num_rows($result);
		        $i = 0;
		        while ($i < $num)
		        {
		            $objp = $this->db->fetch_object($result);
		            if ($objp) $cate_arbo[$objp->rowid]=array('id'=>$objp->rowid, 'fulllabel'=>$objp->label);
		            $i++;
		        }
		        $this->db->free($result);
		    }
		    else dol_print_error($this->db);
		}
		else
		{
            $cat = new Categorie($this->db);
            $cate_arbo = $cat->get_full_arbo($type,$excludeafterid);
		}

        $output = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
		$outarray=array();
        if (is_array($cate_arbo))
        {
            if (! count($cate_arbo)) $output.= '<option value="-1" disabled>'.$langs->trans("NoCategoriesDefined").'</option>';
            else
            {
                $output.= '<option value="-1">&nbsp;</option>';
                foreach($cate_arbo as $key => $value)
                {
                    if ($cate_arbo[$key]['id'] == $selected || ($selected == 'auto' && count($cate_arbo) == 1))
                    {
                        $add = 'selected ';
                    }
                    else
                    {
                        $add = '';
                    }
                    $output.= '<option '.$add.'value="'.$cate_arbo[$key]['id'].'">'.dol_trunc($cate_arbo[$key]['fulllabel'],$maxlength,'middle').'</option>';

					$outarray[$cate_arbo[$key]['id']] = $cate_arbo[$key]['fulllabel'];
                }
            }
        }
        $output.= '</select>';
        $output.= "\n";

		if ($outputmode) return $outarray;
		return $output;
    }

    /**
     *     Show a confirmation HTML form or AJAX popup
     *
     *     @param	string		$page        	   	Url of page to call if confirmation is OK
     *     @param	string		$title       	   	Title
     *     @param	string		$question    	   	Question
     *     @param 	string		$action      	   	Action
     *	   @param	array		$formquestion	   	An array with forms complementary inputs
     * 	   @param	string		$selectedchoice		"" or "no" or "yes"
     * 	   @param	int			$useajax		   	0=No, 1=Yes, 2=Yes but submit page with &confirm=no if choice is No, 'xxx'=preoutput confirm box with div id=dialog-confirm-xxx
     *     @param	int			$height          	Force height of box
     *     @param	int			$width				Force width of box
     *     @return 	void
     *     @deprecated
     *     @see formconfirm()
     */
    function form_confirm($page, $title, $question, $action, $formquestion='', $selectedchoice="", $useajax=0, $height=170, $width=500)
    {
        print $this->formconfirm($page, $title, $question, $action, $formquestion, $selectedchoice, $useajax, $height, $width);
    }

    /**
     *     Show a confirmation HTML form or AJAX popup.
     *     Easiest way to use this is with useajax=1.
     *     If you use useajax='xxx', you must also add jquery code to trigger opening of box (with correct parameters)
     *     just after calling this method. For example:
     *       print '<script type="text/javascript">'."\n";
     *       print 'jQuery(document).ready(function() {'."\n";
     *       print 'jQuery(".xxxlink").click(function(e) { jQuery("#aparamid").val(jQuery(this).attr("rel")); jQuery("#dialog-confirm-xxx").dialog("open"); return false; });'."\n";
     *       print '});'."\n";
     *       print '</script>'."\n";
     *
     *     @param  	string		$page        	   	Url of page to call if confirmation is OK
     *     @param	string		$title       	   	Title
     *     @param	string		$question    	   	Question
     *     @param 	string		$action      	   	Action
     *	   @param  	array		$formquestion	   	An array with complementary inputs to add into forms: array(array('label'=> ,'type'=> , ))
     * 	   @param  	string		$selectedchoice  	"" or "no" or "yes"
     * 	   @param  	int			$useajax		   	0=No, 1=Yes, 2=Yes but submit page with &confirm=no if choice is No, 'xxx'=Yes and preoutput confirm box with div id=dialog-confirm-xxx
     *     @param  	int			$height          	Force height of box
     *     @param	int			$width				Force width of box ('999' or '90%'). Ignored and forced to 90% on smartphones.
     *     @return 	string      	    			HTML ajax code if a confirm ajax popup is required, Pure HTML code if it's an html form
     */
    function formconfirm($page, $title, $question, $action, $formquestion='', $selectedchoice="", $useajax=0, $height=200, $width=500)
    {
        global $langs,$conf;
        global $useglobalvars;

        $more='';
        $formconfirm='';
        $inputok=array();
        $inputko=array();

        // Clean parameters
        $newselectedchoice=empty($selectedchoice)?"no":$selectedchoice;
        if ($conf->browser->layout == 'phone') $width='95%';

        if (is_array($formquestion) && ! empty($formquestion))
        {
        	// First add hidden fields and value
        	foreach ($formquestion as $key => $input)
            {
                if (is_array($input) && ! empty($input))
                {
                	if ($input['type'] == 'hidden')
                    {
                        $more.='<input type="hidden" id="'.$input['name'].'" name="'.$input['name'].'" value="'.dol_escape_htmltag($input['value']).'">'."\n";
                    }
                }
            }

        	// Now add questions
            $more.='<table class="paddingtopbottomonly" width="100%">'."\n";
            $more.='<tr><td colspan="3">'.(! empty($formquestion['text'])?$formquestion['text']:'').'</td></tr>'."\n";
            foreach ($formquestion as $key => $input)
            {
                if (is_array($input) && ! empty($input))
                {
                	$size=(! empty($input['size'])?' size="'.$input['size'].'"':'');

                    if ($input['type'] == 'text')
                    {
                        $more.='<tr><td>'.$input['label'].'</td><td colspan="2" align="left"><input type="text" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'" /></td></tr>'."\n";
                    }
                    else if ($input['type'] == 'password')
                    {
                        $more.='<tr><td>'.$input['label'].'</td><td colspan="2" align="left"><input type="password" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"'.$size.' value="'.$input['value'].'" /></td></tr>'."\n";
                    }
                    else if ($input['type'] == 'select')
                    {
                        $more.='<tr><td>';
                        if (! empty($input['label'])) $more.=$input['label'].'</td><td valign="top" colspan="2" align="left">';
                        $more.=$this->selectarray($input['name'],$input['values'],$input['default'],1);
                        $more.='</td></tr>'."\n";
                    }
                    else if ($input['type'] == 'checkbox')
                    {
                        $more.='<tr>';
                        $more.='<td>'.$input['label'].' </td><td align="left">';
                        $more.='<input type="checkbox" class="flat" id="'.$input['name'].'" name="'.$input['name'].'"';
                        if (! is_bool($input['value']) && $input['value'] != 'false') $more.=' checked';
                        if (is_bool($input['value']) && $input['value']) $more.=' checked';
                        if (isset($input['disabled'])) $more.=' disabled';
                        $more.=' /></td>';
                        $more.='<td align="left">&nbsp;</td>';
                        $more.='</tr>'."\n";
                    }
                    else if ($input['type'] == 'radio')
                    {
                        $i=0;
                        foreach($input['values'] as $selkey => $selval)
                        {
                            $more.='<tr>';
                            if ($i==0) $more.='<td class="tdtop">'.$input['label'].'</td>';
                            else $more.='<td>&nbsp;</td>';
                            $more.='<td width="20"><input type="radio" class="flat" id="'.$input['name'].'" name="'.$input['name'].'" value="'.$selkey.'"';
                            if ($input['disabled']) $more.=' disabled';
                            $more.=' /></td>';
                            $more.='<td align="left">';
                            $more.=$selval;
                            $more.='</td></tr>'."\n";
                            $i++;
                        }
                    }
					else if ($input['type'] == 'date')
					{
						$more.='<tr><td>'.$input['label'].'</td>';
						$more.='<td colspan="2" align="left">';
						$more.=$this->select_date($input['value'],$input['name'],0,0,0,'',1,0,1);
						$more.='</td></tr>'."\n";
						$formquestion[] = array('name'=>$input['name'].'day');
						$formquestion[] = array('name'=>$input['name'].'month');
						$formquestion[] = array('name'=>$input['name'].'year');
						$formquestion[] = array('name'=>$input['name'].'hour');
						$formquestion[] = array('name'=>$input['name'].'min');
					}
                    else if ($input['type'] == 'other')
                    {
                        $more.='<tr><td>';
                        if (! empty($input['label'])) $more.=$input['label'].'</td><td colspan="2" align="left">';
                        $more.=$input['value'];
                        $more.='</td></tr>'."\n";
                    }
                }
            }
            $more.='</table>'."\n";
        }

		// JQUI method dialog is broken with jmobile, we use standard HTML.
		// Note: When using dol_use_jmobile or no js, you must also check code for button use a GET url with action=xxx and check that you also output the confirm code when action=xxx
		// See page product/card.php for example
        if (! empty($conf->dol_use_jmobile)) $useajax=0;
		if (empty($conf->use_javascript_ajax)) $useajax=0;

        if ($useajax)
        {
            $autoOpen=true;
            $dialogconfirm='dialog-confirm';
            $button='';
            if (! is_numeric($useajax))
            {
                $button=$useajax;
                $useajax=1;
                $autoOpen=false;
                $dialogconfirm.='-'.$button;
            }
            $pageyes=$page.(preg_match('/\?/',$page)?'&':'?').'action='.$action.'&confirm=yes';
            $pageno=($useajax == 2 ? $page.(preg_match('/\?/',$page)?'&':'?').'confirm=no':'');
            // Add input fields into list of fields to read during submit (inputok and inputko)
            if (is_array($formquestion))
            {
                foreach ($formquestion as $key => $input)
                {
                	//print "xx ".$key." rr ".is_array($input)."<br>\n";
                    if (is_array($input) && isset($input['name'])) array_push($inputok,$input['name']);
                    if (isset($input['inputko']) && $input['inputko'] == 1) array_push($inputko,$input['name']);
                }
            }
			// Show JQuery confirm box. Note that global var $useglobalvars is used inside this template
            $formconfirm.= '<div id="'.$dialogconfirm.'" title="'.dol_escape_htmltag($title).'" style="display: none;">';
            if (! empty($more)) {
            	$formconfirm.= '<div class="confirmquestions">'.$more.'</div>';
            }
            $formconfirm.= ($question ? '<div class="confirmmessage">'.img_help('','').' '.$question . '</div>': '');
            $formconfirm.= '</div>'."\n";

            $formconfirm.= "\n<!-- begin ajax form_confirm page=".$page." -->\n";
            $formconfirm.= '<script type="text/javascript">'."\n";
            $formconfirm.= 'jQuery(document).ready(function() {
            $(function() {
            	$( "#'.$dialogconfirm.'" ).dialog(
            	{
                    autoOpen: '.($autoOpen ? "true" : "false").',';
            		if ($newselectedchoice == 'no')
            		{
						$formconfirm.='
						open: function() {
            				$(this).parent().find("button.ui-button:eq(2)").focus();
						},';
            		}
        			$formconfirm.='
                    resizable: false,
                    height: "'.$height.'",
                    width: "'.$width.'",
                    modal: true,
                    closeOnEscape: false,
                    buttons: {
                        "'.dol_escape_js($langs->transnoentities("Yes")).'": function() {
                        	var options="";
                        	var inputok = '.json_encode($inputok).';
                         	var pageyes = "'.dol_escape_js(! empty($pageyes)?$pageyes:'').'";
                         	if (inputok.length>0) {
                         		$.each(inputok, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         		    if ($("#" + inputname).attr("type") == "radio") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + inputvalue;
                         		});
                         	}
                         	var urljump = pageyes + (pageyes.indexOf("?") < 0 ? "?" : "") + options;
                         	//alert(urljump);
            				if (pageyes.length > 0) { location.href = urljump; }
                            $(this).dialog("close");
                        },
                        "'.dol_escape_js($langs->transnoentities("No")).'": function() {
                        	var options = "";
                         	var inputko = '.json_encode($inputko).';
                         	var pageno="'.dol_escape_js(! empty($pageno)?$pageno:'').'";
                         	if (inputko.length>0) {
                         		$.each(inputko, function(i, inputname) {
                         			var more = "";
                         			if ($("#" + inputname).attr("type") == "checkbox") { more = ":checked"; }
                         			var inputvalue = $("#" + inputname + more).val();
                         			if (typeof inputvalue == "undefined") { inputvalue=""; }
                         			options += "&" + inputname + "=" + inputvalue;
                         		});
                         	}
                         	var urljump=pageno + (pageno.indexOf("?") < 0 ? "?" : "") + options;
                         	//alert(urljump);
            				if (pageno.length > 0) { location.href = urljump; }
                            $(this).dialog("close");
                        }
                    }
                }
                );

            	var button = "'.$button.'";
            	if (button.length > 0) {
                	$( "#" + button ).click(function() {
                		$("#'.$dialogconfirm.'").dialog("open");
        			});
                }
            });
            });
            </script>';
            $formconfirm.= "<!-- end ajax form_confirm -->\n";
        }
        else
        {
        	$formconfirm.= "\n<!-- begin form_confirm page=".$page." -->\n";

            $formconfirm.= '<form method="POST" action="'.$page.'" class="notoptoleftroright">'."\n";
            $formconfirm.= '<input type="hidden" name="action" value="'.$action.'">'."\n";
            $formconfirm.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";

            $formconfirm.= '<table width="100%" class="valid">'."\n";

            // Line title
            $formconfirm.= '<tr class="validtitre"><td class="validtitre" colspan="3">'.img_picto('','recent').' '.$title.'</td></tr>'."\n";

            // Line form fields
            if ($more)
            {
                $formconfirm.='<tr class="valid"><td class="valid" colspan="3">'."\n";
                $formconfirm.=$more;
                $formconfirm.='</td></tr>'."\n";
            }

            // Line with question
            $formconfirm.= '<tr class="valid">';
            $formconfirm.= '<td class="valid">'.$question.'</td>';
            $formconfirm.= '<td class="valid">';
            $formconfirm.= $this->selectyesno("confirm",$newselectedchoice);
            $formconfirm.= '</td>';
            $formconfirm.= '<td class="valid" align="center"><input class="button valignmiddle" type="submit" value="'.$langs->trans("Validate").'"></td>';
            $formconfirm.= '</tr>'."\n";

            $formconfirm.= '</table>'."\n";

            $formconfirm.= "</form>\n";
            $formconfirm.= '<br>';

            $formconfirm.= "<!-- end form_confirm -->\n";
        }

        return $formconfirm;
    }


    /**
     *    Show a form to select a project
     *
     *    @param	int		$page        		Page
     *    @param	int		$socid       		Id third party (-1=all, 0=only projects not linked to a third party, id=projects not linked or linked to third party id)
     *    @param    int		$selected    		Id pre-selected project
     *    @param    string	$htmlname    		Name of select field
     *    @param	int		$discard_closed		Discard closed projects (0=Keep,1=hide completely except $selected,2=Disable)
     *    @param	int		$maxlength			Max length
     *    @param	int		$forcefocus			Force focus on field (works with javascript only)
     *    @param    int     $nooutput           No print is done. String is returned.
     *    @return	string                      Return html content
     */
    function form_project($page, $socid, $selected='', $htmlname='projectid', $discard_closed=0, $maxlength=20, $forcefocus=0, $nooutput=0)
    {
        global $langs;

        require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
        require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

        $out='';

        $formproject=new FormProjets($this->db);

        $langs->load("project");
        if ($htmlname != "none")
        {
            $out.="\n";
            $out.='<form method="post" action="'.$page.'">';
            $out.='<input type="hidden" name="action" value="classin">';
            $out.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $out.=$formproject->select_projects($socid, $selected, $htmlname, $maxlength, 0, 1, $discard_closed, $forcefocus, 0, 0, '', 1);
            $out.='<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
            $out.='</form>';
        }
        else
        {
            if ($selected)
            {
                $projet = new Project($this->db);
                $projet->fetch($selected);
                //print '<a href="'.DOL_URL_ROOT.'/projet/card.php?id='.$selected.'">'.$projet->title.'</a>';
                $out.=$projet->getNomUrl(0,'',1);
            }
            else
            {
                $out.="&nbsp;";
            }
        }

        if (empty($nooutput))
        {
            print $out;
            return '';
        }
        return $out;
    }

    /**
     *	Show a form to select payment conditions
     *
     *  @param	int		$page        	Page
     *  @param  string	$selected    	Id condition pre-selectionne
     *  @param  string	$htmlname    	Name of select html field
     *	@param	int		$addempty		Add empty entry
     *  @return	void
     */
    function form_conditions_reglement($page, $selected='', $htmlname='cond_reglement_id', $addempty=0)
    {
        global $langs;
        if ($htmlname != "none")
        {
            print '<form method="post" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setconditions">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->select_conditions_paiements($selected,$htmlname,-1,$addempty);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
            if ($selected)
            {
                $this->load_cache_conditions_paiements();
                print $this->cache_conditions_paiements[$selected]['label'];
            } else {
                print "&nbsp;";
            }
        }
    }

    /**
     *  Show a form to select a delivery delay
     *
     *  @param  int		$page        	Page
     *  @param  string	$selected    	Id condition pre-selectionne
     *  @param  string	$htmlname    	Name of select html field
     *	@param	int		$addempty		Ajoute entree vide
     *  @return	void
     */
    function form_availability($page, $selected='', $htmlname='availability', $addempty=0)
    {
        global $langs;
        if ($htmlname != "none")
        {
            print '<form method="post" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setavailability">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->selectAvailabilityDelay($selected,$htmlname,-1,$addempty);
            print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
            if ($selected)
            {
                $this->load_cache_availability();
                print $this->cache_availability[$selected]['label'];
            } else {
                print "&nbsp;";
            }
        }
    }

    /**
	 *	Output HTML form to select list of input reason (events that triggered an object creation, like after sending an emailing, making an advert, ...)
	 *  List found into table c_input_reason loaded by loadCacheInputReason
     *
     *  @param  string	$page        	Page
     *  @param  string	$selected    	Id condition pre-selectionne
     *  @param  string	$htmlname    	Name of select html field
     *	@param	int		$addempty		Add empty entry
     *  @return	void
     */
    function formInputReason($page, $selected='', $htmlname='demandreason', $addempty=0)
    {
        global $langs;
        if ($htmlname != "none")
        {
            print '<form method="post" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setdemandreason">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->selectInputReason($selected,$htmlname,-1,$addempty);
            print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
            if ($selected)
            {
                $this->loadCacheInputReason();
                foreach ($this->cache_demand_reason as $key => $val)
                {
                    if ($val['id'] == $selected)
                    {
                        print $val['label'];
                        break;
                    }
                }
            } else {
                print "&nbsp;";
            }
        }
    }

    /**
     *    Show a form + html select a date
     *
     *    @param	string		$page        	Page
     *    @param	string		$selected    	Date preselected
     *    @param    string		$htmlname    	Html name of date input fields or 'none'
     *    @param    int			$displayhour 	Display hour selector
     *    @param    int			$displaymin		Display minutes selector
     *    @param	int			$nooutput		1=No print output, return string
     *    @return	string
     *    @see		select_date
     */
    function form_date($page, $selected, $htmlname, $displayhour=0, $displaymin=0, $nooutput=0)
    {
        global $langs;

        $ret='';

        if ($htmlname != "none")
        {
            $ret.='<form method="post" action="'.$page.'" name="form'.$htmlname.'">';
            $ret.='<input type="hidden" name="action" value="set'.$htmlname.'">';
            $ret.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $ret.='<table class="nobordernopadding" cellpadding="0" cellspacing="0">';
            $ret.='<tr><td>';
            $ret.=$this->select_date($selected,$htmlname,$displayhour,$displaymin,1,'form'.$htmlname,1,0,1);
            $ret.='</td>';
            $ret.='<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
            $ret.='</tr></table></form>';
        }
        else
        {
        	if ($displayhour) $ret.=dol_print_date($selected,'dayhour');
        	else $ret.=dol_print_date($selected,'day');
        }

        if (empty($nooutput)) print $ret;
        return $ret;
    }


    /**
     *  Show a select form to choose a user
     *
     *  @param	string	$page        	Page
     *  @param  string	$selected    	Id of user preselected
     *  @param  string	$htmlname    	Name of input html field. If 'none', we just output the user link.
     *  @param  array	$exclude		List of users id to exclude
     *  @param  array	$include        List of users id to include
     *  @return	void
     */
    function form_users($page, $selected='', $htmlname='userid', $exclude='', $include='')
    {
        global $langs;

        if ($htmlname != "none")
        {
            print '<form method="POST" action="'.$page.'" name="form'.$htmlname.'">';
            print '<input type="hidden" name="action" value="set'.$htmlname.'">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print $this->select_dolusers($selected,$htmlname,1,$exclude,0,$include);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
		{
            if ($selected)
            {
                require_once DOL_DOCUMENT_ROOT .'/user/class/user.class.php';
                $theuser=new User($this->db);
                $theuser->fetch($selected);
                print $theuser->getNomUrl(1);
            } else {
                print "&nbsp;";
            }
        }
    }


    /**
     *    Show form with payment mode
     *
     *    @param	string	$page        	Page
     *    @param    int		$selected    	Id mode pre-selectionne
     *    @param    string	$htmlname    	Name of select html field
     *    @param  	string	$filtertype		To filter on field type in llx_c_paiement (array('code'=>xx,'label'=>zz))
     *    @param    int     $active         Active or not, -1 = all
     *    @return	void
     */
    function form_modes_reglement($page, $selected='', $htmlname='mode_reglement_id', $filtertype='', $active=1)
    {
        global $langs;
        if ($htmlname != "none")
        {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setmode">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            $this->select_types_paiements($selected,$htmlname,$filtertype,0,0,0,0,$active);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
            if ($selected)
            {
                $this->load_cache_types_paiements();
                print $this->cache_types_paiements[$selected]['label'];
            } else {
                print "&nbsp;";
            }
        }
    }

	/**
     *    Show form with multicurrency code
     *
     *    @param	string	$page        	Page
     *    @param    string	$selected    	code pre-selectionne
     *    @param    string	$htmlname    	Name of select html field
     *    @return	void
     */
    function form_multicurrency_code($page, $selected='', $htmlname='multicurrency_code')
    {
        global $langs;
        if ($htmlname != "none")
        {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setmulticurrencycode">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print $this->selectMultiCurrency($selected, $htmlname, 0);
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
        	dol_include_once('/core/lib/company.lib.php');
        	print !empty($selected) ? currency_name($selected,1) : '&nbsp;';
        }
    }

	/**
     *    Show form with multicurrency rate
     *
     *    @param	string	$page        	Page
     *    @param    double	$rate	    	Current rate
     *    @param    string	$htmlname    	Name of select html field
     *    @param    string  $currency       Currency code to explain the rate
     *    @return	void
     */
    function form_multicurrency_rate($page, $rate='', $htmlname='multicurrency_tx', $currency='')
    {
        global $langs, $mysoc, $conf;

        if ($htmlname != "none")
        {
            print '<form method="POST" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setmulticurrencyrate">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<input type="text" name="'.$htmlname.'" value="'.(!empty($rate) ? price($rate) : 1).'" size="10" /> ';
			print '<select name="calculation_mode">';
			print '<option value="1">'.$currency.' > '.$conf->currency.'</option>';
			print '<option value="2">'.$conf->currency.' > '.$currency.'</option>';
			print '</select> ';
            print '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
            print '</form>';
        }
        else
        {
        	if (! empty($rate))
        	{
        	    print price($rate, 1, $langs, 1, 0);
        	    if ($currency && $rate != 1) print ' &nbsp; ('.price($rate, 1, $langs, 1, 0).' '.$currency.' = 1 '.$conf->currency.')';
        	}
        	else
        	{
        	    print 1;
        	}
        }
    }


    /**
     *	Show a select box with available absolute discounts
     *
     *  @param  string	$page        	Page URL where form is shown
     *  @param  int		$selected    	Value pre-selected
     *	@param  string	$htmlname    	Name of SELECT component. If 'none', not changeable. Example 'remise_id'.
     *	@param	int		$socid			Third party id
     * 	@param	float	$amount			Total amount available
     * 	@param	string	$filter			SQL filter on discounts
     * 	@param	int		$maxvalue		Max value for lines that can be selected
     *  @param  string	$more           More string to add
     *  @param  int     $hidelist       1=Hide list
     *  @return	void
     */
    function form_remise_dispo($page, $selected, $htmlname, $socid, $amount, $filter='', $maxvalue=0, $more='', $hidelist=0)
    {
        global $conf,$langs;
        if ($htmlname != "none")
        {
            print '<form method="post" action="'.$page.'">';
            print '<input type="hidden" name="action" value="setabsolutediscount">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<div class="inline-block">';
            if (! empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS))	// Never use this option.
            {
                if (! $filter || $filter=="fk_facture_source IS NULL") print $langs->trans("CompanyHasAbsoluteDiscount",price($amount,0,$langs,0,0,-1,$conf->currency));    // If we want deposit to be substracted to payments only and not to total of final invoice
                else print $langs->trans("CompanyHasCreditNote",price($amount,0,$langs,0,0,-1,$conf->currency));
            }
            else
            {
                if (! $filter)
                {
                	print $langs->trans("CompanyHasAbsoluteDiscount",price($amount,0,$langs,0,0,-1,$conf->currency));
                }
                elseif ($filter=="fk_facture_source IS NULL OR (fk_facture_source IS NOT NULL AND (description LIKE '(DEPOSIT)%' AND description NOT LIKE '(EXCESS RECEIVED)%'))") 
                {
                	// Replace trans key with CompanyHasDownPaymentOrCommercialDiscount
                	print $langs->trans("CompanyHasAbsoluteDiscount",price($amount,0,$langs,0,0,-1,$conf->currency));
                }
                else 
                {
                	print $langs->trans("CompanyHasCreditNote",price($amount,0,$langs,0,0,-1,$conf->currency));
                }
            }