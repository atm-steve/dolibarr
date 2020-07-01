<?php
/* Copyright (C) 2007-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2013      Ferran Marcet        <fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *      \file       labelprint/class/labelprint.class.php
 *      \ingroup    labelprint
 *      \brief      Class for labelprint
 *		\author		Juanjo Menent
 */


/**
 *      \class      Labelprint
 *      \brief      Put here description of your class
 */
class Labelprint
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)

    var $id;
    
	var $entity;
	var $fk_product;
	var $qty;
	var $fk_user;
	var $datec='';
	var $price_level;


    /**
     *      Constructor
     *      @param      dolDB 	$DB      Database handler
     */
    function Labelprint($DB)
    {
        $this->db = $DB;
        return 1;
    }


    /**
     *      Create object into database
     *      @param      User	$user        	User that create
     *      @param      int		$notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int         			<0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
		$qty = $this->qty;
		
		$res = $this->fetch($this->id,$this->fk_product);
		if ($res==1)
		{
			$qty=$this->qty+$qty;
			$this->qty = $qty;
			$res = $this->update($user, $notrigger);
			return $res;
		}
        
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->qty)) $this->qty=trim($this->qty);
		
		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."labelprint(";
		
		$sql.= "entity,";
		$sql.= "fk_product,";
		$sql.= "qty,";
		$sql.= "fk_user,";
		$sql.= "datec,";
		$sql.= "price_level";
		
        $sql.= ") VALUES (";
        
		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->fk_product)?'NULL':"'".$this->fk_product."'").",";
		$sql.= " ".(! isset($this->qty)?'NULL':"'".$this->qty."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':$this->db->idate($this->datec)).",";
		$sql.= " ".(! isset($this->price_level)?1:$this->price_level)."";
        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."labelprint");

        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }
    
    /**
     *      Create object into database
     *      @param      User	$user        	User that create
     *      @param      Array	$toPrint	    Array with products to print
     *      @return     int         			<0 if KO, Id of created object if OK
     */
    function multicreate($user, $toPrint)
    {
    	global $conf, $langs;
		$error=0;
		require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
		$product = new Product($this->db);
    	foreach ($toPrint as $prodid)
		{
			
			$result = $product->fetch($prodid);
			if ($result)
			{
				if ($conf->stock->enabled)
				{
					$product->load_stock();				
	    			$qty = $product->stock_reel;
				}
			
			}
			
			
			$res = $this->fetch($this->id,$product->id);
			if ($res==1)
			{
				$qty=$this->qty+$qty;
				$this->qty = $qty;
				$res = $this->update($user, $notrigger);
				if($res!=1) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
			}
        	else
        	{	
	        	// Insert request
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."labelprint(";
			
				$sql.= "entity,";
				$sql.= "fk_product,";
				$sql.= "qty,";
				$sql.= "fk_user,";
				$sql.= "datec,";
				$sql.= "price_level";
				
	        	$sql.= ") VALUES (";
	        
				$sql.= " ".$conf->entity.",";
				$sql.= " ".$product->id.",";
				$sql.= " ".$qty.",";
				$sql.= " ".$user->id.",";
				$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':$this->db->idate($this->datec)).",";
				$sql.= " ".(! isset($this->price_level)?1:$this->price_level)."";
	
				$sql.= ")";
	
				//$this->db->begin();
	
		   		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
	        	$resql=$this->db->query($sql);
	    		if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
        	}
			
    	}
    	if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."labelprint");

	}

	// Commit or rollback
	if ($error)
	{
		foreach($this->errors as $errmsg)
		{
			dol_syslog(get_class($this)."::multicreate ".$errmsg, LOG_ERR);
			$this->error.=($this->error?', '.$errmsg:$errmsg);
		}
		//$this->db->rollback();
		return -1*$error;
	}
	else
	{
		//$this->db->commit();
		return $this->id;
	}
    }


    /**
     *    Load object in memory from database
     *    @param      int	$id          id object
     *    @return     int    		     <0 if KO, >0 if OK
     */
    function fetch($id, $fk_product='')
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.entity,";
		$sql.= " t.fk_product,";
		$sql.= " t.qty,";
		$sql.= " t.fk_user,";
		$sql.= " t.datec,";
		$sql.= " t.price_level";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."labelprint as t";
        if($fk_product)
        	$sql.= " WHERE t.fk_product = ".$fk_product;
       	else
        	$sql.= " WHERE t.rowid = ".$id;
       	

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->entity = $obj->entity;
				$this->fk_product = $obj->fk_product;
				$this->qty = $obj->qty;
				$this->fk_user = $obj->fk_user;
				$this->datec = $this->db->jdate($obj->datec);
				$this->price_level = $obj->price_level;
				$this->db->free($resql);

            	return 1;    
            }
            else
            {
            	$this->db->free($resql);

            	return -1;
            }
            
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *      Update object into database
     *      @param      User	$user        	User that modify
     *      @param      int		$notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int     		    	<0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->fk_product)) $this->fk_product=trim($this->fk_product);
		if (isset($this->qty)) $this->qty=trim($this->qty);
		if (isset($this->fk_user)) $this->fk_user=trim($this->fk_user);
		if (isset($this->price_level)) $this->price_level=trim($this->price_level);

		// Check parameters
		// Put here code to add control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."labelprint SET";
        
		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " fk_product=".(isset($this->fk_product)?$this->fk_product:"null").",";
		$sql.= " qty=".(isset($this->qty)?$this->qty:"null").",";
		$sql.= " fk_user=".(isset($this->fk_user)?$this->fk_user:"null").",";
		$sql.= " price_level=".(isset($this->price_level)?$this->price_level:1).",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null')."";
  
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *   Delete object in database
     *	 @param     User	$user        	User that delete
     *   @param     int		$notrigger	    0=launch triggers after, 1=disable triggers
	 *   @return	int						<0 if KO, >0 if OK
	 */
	function truncate($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;
		
		$sql ="TRUNCATE TABLE ".MAIN_DB_PREFIX."labelprint";
		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}
	
	/**
	 *   Delete object in database
     *	 @param     User	$user        	User that delete
     *   @param     int		$notrigger	    0=launch triggers after, 1=disable triggers
	 *   @return	int						<0 if KO, >0 if OK
	 */
	function delete($line, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;
		
		$sql ="DELETE FROM ".MAIN_DB_PREFIX."labelprint";
		$sql.=" WHERE rowid=".$line;
		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *		Load an object from its id and create a new one in database
	 *		@param      int		$fromid     		Id of object to clone
	 * 	 	@return		int							New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Labelprint($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *		Initialisz object with example values
	 *		Id must be 0 if object instance is a specimen.
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->entity='';
		$this->fk_product='';
		$this->qty='';
		$this->fk_user='';
		$this->datec='';		
		$this->price_level='';
	}
	
	
	/**
	* Encode EAN
	*
	* @param	string	$ean		Code
	* @param	string	$encoding	Encoding
	* @return	array				array('encoding': the encoding which has been used, 'bars': the bars, 'text': text-positioning info)
	*/
	function generate_barcode($id_prod)
	{
		require_once(DOL_DOCUMENT_ROOT."/core/lib/barcode.lib.php");
		
		global $conf, $langs,$db;
		$loop=true;
		$encoding = "EAN-13";
		while($loop){
			$ean = rand(300000,399999).rand(0,999999);
			
			$ean=substr($ean,0,12);
		    $eansum=barcode_gen_ean_sum($ean);
		    $ean.=$eansum;
		    
		    $sql ="SELECT rowid FROM ".MAIN_DB_PREFIX."product";
		    $sql.=" WHERE barcode=".$ean;
		    $db->begin();
		    
		    $resql = $db->query($sql);	  
	
		    if ($resql)
		    {
		    	if($db->num_rows($resql) == 0){
		    		$loop=false;
		    		
		    		$sql ="UPDATE ".MAIN_DB_PREFIX."product";
		    		$sql.=" SET barcode = ".$ean." WHERE rowid=".$id_prod;
		    				    				    		
		    		$res = $db->query($sql);
		    		$db->commit();
		    	}
		    }
		}	
	    return $res;
	}
	
	
	

}

/**
 *      \class      pdfLabel
 *      \brief      Create a PDF with the labels
 */
class pdfLabel
{

	/**
	 * 
	 * Create a pdf with the labels
	 *
	 */
	function createPdf()
	{
		global $conf, $mysoc, $db, $langs;
		$langs->load("other");
		
		require_once(DOL_DOCUMENT_ROOT.'/includes/tecnickcom/tcpdf/tcpdf.php');
		require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
        require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
        
        if ($conf->marketplace->enabled){
            dol_include_once('/marketplace/class/productseller.class.php');
        }
		
        $pdf=new TCPDF();
        
        $marginLeft = 5;
        $marginTop = 5;
        $keepmargins = true;

        $pdf->SetMargins($marginLeft, $marginTop, $right=-1, $keepmargins);
        
		$pdf->SetFont('dejavusans','', 10);
		
		$lab_start = $conf->global->LAB_START;
		
		if ($conf->global->MAIN_MODULE_LABELPRINT_LABELS_0){
			$PosY=5+(floor($lab_start/3)*36);
			$PosX=5+($lab_start % 3)*70;
		}
		else if ($conf->global->MAIN_MODULE_LABELPRINT_LABELS_1){
			$PosY=4+(floor($lab_start/3)*37);
			$PosX=5+($lab_start % 3)*70;
		}
		else{
			$PosY=8+(floor($lab_start/5)*21);
			$PosX=3+($lab_start % 5)*21;
		}

        //$PosX=5+($lab_start % 3)*70;
        //var_dump($PosX);
		//var_dump($PosY); exit;
		//$PosX=5;
		$Line=5;
		
		// define barcode style
		$style = array(
    	'position' => '',
    	'align' => 'C',
    	'stretch' => false,
    	'fitwidth' => true,
    	'cellfitalign' => '',
    	'border' => false,
    	'hpadding' => 'auto',
    	'vpadding' => 'auto',
    	'fgcolor' => array(0,0,0),
    	'bgcolor' => false, //array(255,255,255),
    	'text' => true,
    	'font' => 'helvetica',
    	'fontsize' => 8,
    	'stretchtext' => 4
		);
		
		//First page
		$pdf->AddPage();
		
		$sql ="SELECT fk_product, qty, price_level";
		$sql .=" FROM ".MAIN_DB_PREFIX."labelprint";
		$sql .=" WHERE entity=".$conf->entity;			
		$resql = $db->query($sql);
			
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;
				
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$product = new Product($db);
				
				$product->fetch($objp->fk_product);
				$qty=$objp->qty;
				$n=0;
				while($n<$qty)
				{
					if($conf->global->MAIN_MODULE_LABELPRINT_LABELS_0 || $conf->global->MAIN_MODULE_LABELPRINT_LABELS_1){
						//Position X
						$PosXLabel=($PosX<70?$PosX:$PosX-3); 
						
						//Soc Name
						$pdf->SetFont('dejavusans','B', 9);
						$pdf->SetXY($PosXLabel,$PosY);
						$pdf->SetFillColor(230,230,230);
						if($conf->global->LAB_COMP){
							$pdf->MultiCell(65,5,dol_trunc($mysoc->nom,25),0,'L');
							$flag=1;
						} 
						elseif($conf->global->LAB_PROD_LABEL){
							$pdf->MultiCell(65,5,dol_trunc($product->label,25),0,'L');
							$flag=2;
						}
						else{ 
							$pdf->MultiCell(65,5,dol_trunc($conf->global->LAB_FREE_TEXT,25),0,'L');
							$flag=3;
                        }
                        
						$pdf->SetFont('dejavusans','', 8);
						
						//Position Y
						$PosYLabel=$PosY+$Line;
	
						//Product label
						$pdf->SetXY($PosXLabel,$PosYLabel);
						if($flag==1){
							if($conf->global->LAB_PROD_LABEL)
								$pdf->Cell(25,5,dol_trunc($product->label,25),0,0,'L');
							else 
								$pdf->Cell(25,5,dol_trunc($conf->global->LAB_FREE_TEXT,25),0,0,'L');
						}
						if($flag==2){
                            
                            if($conf->global->LAB_PROD_REF){
                                $pdf->SetFont('dejavusans','', 6);
                                $pdf->Cell(15,3,dol_trunc($product->ref,14),0,0,'L');
                                $pdf->SetFont('dejavusans','', 8);
                                $pdf->SetXY($PosXLabel,$PosYLabel+2);
                            } 
                            $pdf->Cell(25,5,dol_trunc($conf->global->LAB_FREE_TEXT,25),0,0,'L');

						}
						else{
							$pdf->Cell(25,5,"",0,0,'L');
                        }


                        if ($conf->marketplace->enabled){
                            $retroSupplierProduct = new ProductSeller($db);
                            $idSupplier = $retroSupplierProduct->getSellerIDForProduct($product->id);
                            $socstatic = new Societe($db);
                            $socstatic->fetch($idSupplier);
                            $labelSeller = $socstatic->name;
                            $pdf->SetXY($PosXLabel+38,$PosYLabel+2);
                            $pdf->Cell(25,5,$labelSeller,0,0,'R');
                        }
                        
						//$pdf->Cell(25,5,dol_trunc($product->libelle,25),0,0,'L');
						//$pdf->Write($Line,dol_trunc($product->libelle,25));
				
						$PosYLabel=$PosYLabel+$Line+2;
						$pdf->SetXY($PosXLabel,$PosYLabel);
						
						//Barcode
						if ($conf->barcode->enabled)
						{
							$product->fetch_barcode();
							
							$pdf->write1DBarcode($product->barcode, $product->barcode_type_code, '', '', 35, 18, 0.4, $style, 'N');
		
						} 
						
						//Price
						$pdf->SetFont('dejavusans','B', 10);
						if (empty($conf->global->PRODUIT_MULTIPRICES))
						{
							$labelPrice= price($product->price_ttc);
						}
						else{
							$labelPrice= price($product->multiprices_ttc[$objp->price_level]);
						}
						$pdf->SetXY($PosXLabel+28,$PosYLabel);
						$pdf->Cell(25,5,$labelPrice,0,0,'R');
                        
                        $pdf->SetFont('dejavusans','', 9);
						//$PosYLabel=$PosYLabel+$Line+1;
						$labelPrice= $langs->trans(currency_name($conf->currency));
						$pdf->SetXY($PosXLabel+38,$PosYLabel);
                        $pdf->Cell(25,5,$labelPrice,0,0,'R');
                        
                        $labelSet = '';
						
						$PosYLabel=$PosYLabel+$Line;
						if($conf->global->LAB_WEIGHT && !empty($product->weight)){
							$labelSet = $product->weight;
							$labelSet .= " ".measuring_units_string($product->weight_units,"weight");
						}
						elseif($conf->global->LAB_LENGTH && !empty($product->length)){
							$labelSet = $product->length;
							$labelSet .= " ".measuring_units_string($product->length_units,"size");
						}
						elseif($conf->global->LAB_AREA && !empty($product->surface)){
							$labelSet = $product->surface;
							$labelSet .= " ".measuring_units_string($product->surface_units,"surface");
						}
						elseif($conf->global->LAB_VOLUME && !empty($product->volume)){
							$labelSet = $product->volume;
							$labelSet .= " ".measuring_units_string($product->volume_units,"volume");
						}
						elseif($conf->global->LAB_COUNTRY){
							$labelSet = getCountry($product->country_id,'',0,$langs,0);
						}
											
						$pdf->SetXY($PosXLabel+38,$PosYLabel);
                        $pdf->Cell(25,5,$labelSet,0,0,'R');
                        
                        if ($conf->global->LAB_UNIT) {
                            $unitCode = $this->getLabelOfUnit($product->fk_unit, 'code');

                            /*
                             Weight unit : -6=mg, -3=g, 0=kg, 3=tonne, 98=once, 99=livre
                             */
                            // Calcul au kilo
                            $kiloPrice = '';
                            $breakLinePrice = true;
                            //print 'prod '.$product->id.' / unitcode='.$unitCode.' / poids='.$product->weight.' '.$product->weight_units.' / volume='.$product->volume.' '.$product->volume_units;
                            switch ($unitCode) {
                                case 'p':
                                    if ($product->weight > 0) {
                                        // Si pièce, check kilo ou volume
                                        if($product->weight_units == '-3') { // Gramme
                                            $kiloPrice = price2num(($product->price_ttc / $product->weight) * 1000, 2);
                                            $kiloPrice .= ' '.currency_name($conf->currency) .'/kg';
                                        } elseif ($product->weight_units == '0') { // kilo
                                            $kiloPrice = price2num($product->price_ttc / $product->weight, 2);
                                            $kiloPrice .= ' '.currency_name($conf->currency) .'/kg';
                                        } elseif ($product->volume_units == '-3') {

                                        }
                                    }
                                    break;
                                case 'kg':
                                    if($product->weight > 0) {
                                        if($product->weight_units == '-3') {
                                            // Prix au kilo d'après poids en gramme
                                            $kiloPrice = price2num(($product->price_ttc / $product->weight) * 1000, 2);
                                            $kiloPrice .= ' '.currency_name($conf->currency) .'/' .$unitCode;
                                            //var_dump($product->weight_units); exit;
                                            
                                        } elseif ($product->weight_units == '0') {
                                            
                                            // Prix au kilo
                                            $kiloPrice = price2num($product->price_ttc / $product->weight, 2);
                                            $kiloPrice .= ' '.currency_name($conf->currency) .'/kg';
                                        }
                                    } else {
                                        $kiloPrice .= 'le kg';
                                        $breakLinePrice = false;
                                    }
                                    break;
                                case 'l';
                                    // Si litre, check volume
                                    if($product->volume_units == '-3') {
                                        if($product->volume > 0) {
                                            $kiloPrice = price2num($product->price_ttc / $product->volume, 2);
                                            $kiloPrice .= ' '.currency_name($conf->currency) .'/' .strtoupper($unitCode);
                                        } else {
                                            $kiloPrice .= 'le litre';
                                            $breakLinePrice = false;
                                        }
                                    }
                                    break;
                                
                                default:
                                # code...
                                    //var_dump($unitCode); 
                                    //var_dump($product->fk_unit);
                                    break;
                            }
                            if($breakLinePrice) {
                                $PosYLabel=$PosYLabel+$Line;
                            }
                            $pdf->SetXY($PosXLabel+38,$PosYLabel);
                            $pdf->Cell(25,5,$kiloPrice,0,0,'R');
                        }

						$pdf->SetAutoPageBreak(false);
						
						$PosX=$PosX+70;
						If($PosX>=200) 
						{
							$PosX=5;
							if ($conf->global->MAIN_MODULE_LABELPRINT_LABELS_0)
								$PosY=$PosY+36;
							else 
								$PosY=$PosY+37;
							If($PosY>=265) 
							{
								if ($conf->global->MAIN_MODULE_LABELPRINT_LABELS_0)
									$PosY=5;
								else 
									$PosY=4;
								
								$pdf->AddPage();
							}
						}
						$n++;
					}
					else{
						$Line = 3;
						//Position X
						$PosXLabel=($PosX<38?$PosX:$PosX-2);
						
						$pdf->SetFont('dejavusans','B', 6);
						$pdf->SetXY($PosXLabel+1,$PosY);
						$pdf->SetFillColor(230,230,230);
						if($conf->global->LAB_COMP){
							$pdf->MultiCell(32,5,dol_trunc($mysoc->nom,14),0,'L');
						} elseif($conf->global->LAB_PROD_REF){
							$pdf->MultiCell(32,3,dol_trunc($product->ref,14),0,'C');
						}
						elseif($conf->global->LAB_PROD_LABEL){
							$pdf->MultiCell(32,3,dol_trunc($product->label,25),0,'L');
                        } else{
							$pdf->MultiCell(32,5,dol_trunc($conf->global->LAB_FREE_TEXT,14),0,'L');
                        }
        
                        
						//Position Y
						$PosYLabel=$PosY+$Line;
						//$PosYLabel=$PosYLabel+$Line+2;
						$pdf->SetXY($PosXLabel,$PosYLabel);
						$pdf->SetAutoPageBreak(true,1);
						
						//Barcode
						if ($conf->barcode->enabled)
						{
							$product->fetch_barcode();
								
							$pdf->write1DBarcode($product->barcode, $product->barcode_type_code, '', '', 35, 15, 0.4, $style, 'N');
						
						}
						
						$PosX=$PosX+42;
						if($PosX>=200)
						{
							$PosX=3;
							$PosY=$PosY+22;
							
							if($PosY>=280)
							{
								$PosY=8;
														
								$pdf->AddPage();
							}
						}
						$n++;
					}
				}
				$i++;
			}
        }

        //var_dump($pdf);
        //exit;
		ini_set('display_errors','Off');
		$buf=$pdf->Output("", "S");
		$len = strlen($buf);
				
		//$file_temp = ini_get("session.save_path")."/".dol_now().".pdf"; 
		$file_temp = DOL_DATA_ROOT."/".dol_now().".pdf";

		$gestor = fopen($file_temp, "w");
		fwrite($gestor, $buf);
		fclose($gestor);
		$url=dol_buildpath("/labelprint/download.php",1)."?file=".$file_temp;
		print "<meta http-equiv='refresh' content='0;url=".$url."'>";
		
    } 
    
    /**
     *    Returns the text label from units dictionary
     *
     * @param  string $type Label type (long or short)
     * @return string|int <0 if ko, label if ok
     */
    function getLabelOfUnit($fk_unit, $type='long')
    {
        global $langs, $db;

        if (!$fk_unit) {
            return '';
        }

        $langs->load('products');

        $label_type = 'label';

        if ($type == 'short') {
            $label_type = 'short_label';
        } elseif ($type == 'code') {
            $label_type = 'short_label';
        }

        $sql = 'select '.$label_type.' from '.MAIN_DB_PREFIX.'c_units where rowid='.$fk_unit;
        $resql = $db->query($sql);
        if($resql && $db->num_rows($resql) > 0) {
            $res = $db->fetch_array($resql);
            $label = $res[$label_type];
            $db->free($resql);
            return $label;
        }
        else
        {
            $this->error=$db->error().' sql='.$sql;
            dol_syslog(get_class($this)."::getLabelOfUnit Error ".$this->error, LOG_ERR);
            return -1;
        }
    }
}
?>
