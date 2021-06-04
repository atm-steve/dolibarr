<?php

include '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'product WHERE fk_product_type=0';
$resql = $db->query($sql);
if($resql) {
    while($obj = $db->fetch_object($resql)) {
        $prod = new Product($db);
        $prod->fetch($obj->rowid);
        $prod->status_batch = 1;
        $prod->update($prod->id, $user);
    }
}
echo 'fini';