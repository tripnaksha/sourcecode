<?php
/**
 * sh404SEF support for VirtueMart component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_virtuemart.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_virtuemart', $shLangIso, '_PHPSHOP_LIST_ALL_PRODUCTS');
// ------------------  load language file - adjust as needed ----------------------------------------


/**
 * Function vm_sef_get_category_array() is based on
 * Mark Fabrizio, Joomlicious
 * fabrizim@owlwatch.com
 * http://www.joomlicious.com
 */
if( !function_exists( 'vm_sef_get_category_array' ) ){
  function vm_sef_get_category_array( &$db, $category_id, $option, $shLangName ){

    global $shMosConfig_locale;
    $sefConfig = & shRouter::shGetConfig();

    static $tree = null;  // V 1.2.4.m  $tree must an array based on current language
     
    if(empty($tree[$shMosConfig_locale])){
      $q  = "SELECT c.category_name, c.category_id, x.category_parent_id FROM #__vm_category AS c" ;
      $q .= "\n LEFT JOIN #__vm_category_xref AS x ON c.category_id = x.category_child_id;";
      //$q .= "\n WHERE c.category_publish = 'Y';"; // V x
      $db->setQuery( $q );
      if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
      $tree[$shMosConfig_locale] = $db->loadObjectList( 'category_id', false);  // V 1.2.4.m if Joomfish, and don't translate
      // use special call of loadObjectList, asking JF not to translate
      else
      $tree[$shMosConfig_locale] = $db->loadObjectList( 'category_id' );
    }
    $title=array();
    if ($sefConfig->shVMInsertCategories == 1)    // only one category
    $title[] = ($sefConfig->shInsertCategoryId ?
    $tree[$shMosConfig_locale][ $category_id ]->category_id.$sefConfig->replacement : '')
    .$tree[$shMosConfig_locale][ $category_id ]->category_name;
    else
    do {               // all categories and subcategories. We don't really need id, as path
      $title[] = ($sefConfig->shInsertCategoryId ?
      $tree[$shMosConfig_locale][ $category_id ]->category_id.$sefConfig->replacement : '') // to category
      .$tree[$shMosConfig_locale][ $category_id ]->category_name;                           // will always be unique
      $category_id = $tree[$shMosConfig_locale][ $category_id ]->category_parent_id;
    } while( $category_id != 0 );
    return array_reverse( $title );
  }
}

if (!function_exists('vmSefGetProductName')) {  // V 1.2.4.s
  function vmSefGetProductName( $productId, $option, $shLangName, $shLangIso) {
    if (empty($productId)) return null;
    global $sh_LANG;
    $sefConfig = & shRouter::shGetConfig();
    // get DB
    $database =& JFactory::getDBO();
    $q = "SELECT product_id, product_sku, product_name FROM #__vm_product";  // then try to add its name as well
    $q .= "\n WHERE product_id = ".$productId;
    $database->setQuery( $q);
    if (!shTranslateUrl($option, $shLangName))
    $row = $database->loadObject( false);
    else $row = $database->loadObject( );
    if (empty( $row))   // non name available
    return $sh_LANG[$shLangIso]['_PHPSHOP_PRODUCT'].$sefConfig->replacement.$product_id;
    $shName = '';
    if ($sefConfig->shInsertProductId)
    $shName .= $row->product_id;
    if ($sefConfig->shVmUseProductSKU )
    $shName .= (empty($shName) ? '':$sefConfig->replacement).$row->product_sku;
    if ($sefConfig->shVmInsertProductName )
    $shName .= (empty($shName) ? '':$sefConfig->replacement).$row->product_name;
    if (empty($shName))
    $shName = $row->product_name;
    return $shName;
  }
}

shRemoveFromGETVarsList('option');
if (!empty($lang))
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
shRemoveFromGETVarsList('Itemid');
if (!empty($vmcchk)) // V 1.2.4.s
shRemoveFromGETVarsList('vmcchk');
if (!empty($limit)) // V 1.2.4.t bug #167
shRemoveFromGETVarsList('limit');
if (isset($limitstart)) // V 1.2.4.t bug #167
//if (!empty($limitstart)) // V x
shRemoveFromGETVarsList('limitstart');
// start VM specific stuff
$shVmCChk = false;
$page = isset($page) ? @$page : null;
$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;

if (!empty($page)) {
  shRemoveFromGETVarsList('page');
}
if (empty($keyword)) // V 1.3.1
shRemoveFromGETVarsList('keyword');
if (empty($orderby)) // V 1.3.1
shRemoveFromGETVarsList('orderby');
if (!defined('VM_BROWSE_ORDERBY_FIELD') && file_exists(sh404SEF_ADMIN_ABS_PATH.'com_virtuemart/virtuemart.cfg.php')) {
  include_once(sh404SEF_ADMIN_ABS_PATH.'com_virtuemart/virtuemart.cfg.php');
  $defaultOrderField = VM_BROWSE_ORDERBY_FIELD;
} else $defaultOrderField = 'product_name';
if (!empty($orderby) && $orderby == $defaultOrderField) // V 1.3.1
shRemoveFromGETVarsList('orderby');

// shumisha : insert shop name from menu
$shShopName = shGetComponentPrefix($option);
$shShopName = empty($shShopName) ?
getMenuTitle($option, (isset($task) ? @$task : null), null, null, $shLangName ) : $shShopName;
$shShopName = $shShopName == '/' ? 'Shop':$shShopName; // V 1.2.4.t

// special handling for 'vmcchk' cookie test
// plus workaround to allow inclusion of /vmchk/ strings in search engines
//if (strpos( $string, 'vmcchk')) {// if VM is doing a cookie check
if (strpos( $string, 'vmcchk') !== false) {// if VM is doing a cookie check
  $shVmCChk = true;
  // this is a trick to counter a 'bug' in VM 1.0.10 when using SEF URL
  setcookie( 'VMCHECK', 'OK', time()+60*60, '/' );
}
//die($func);
switch ($page)
{
  case 'shop.browse':
    if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
    if ($shVmCChk)
    $title[] = 'vmchk';
    $shManufactureName = '';  // V 1.2.4.r
    if ( !empty ($manufacturer_id)) {
      $query  = "SELECT mf_name, manufacturer_id FROM #__vm_manufacturer" ;
      $query .= "\n WHERE manufacturer_id=".$manufacturer_id;
      $database->setQuery( $query );
      if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
      $result = $database->loadObject(false);
      else
      $result = $database->loadObject();
      $shRef = empty($result)?  // no name available
      $sh_LANG[$shLangIso]['_PHPSHOP_MANUFACTURER'].$sefConfig->replacement.$manufacturer_id // put ID
      : ($sefConfig->shInsertManufacturerId ? $manufacturer_id.$sefConfig->replacement : ''); // if name, put ID only if requested
      $shManufactureName = $shRef.(empty( $result ) ? '' :  $result->mf_name);
    }
    // V 1.2.4.r
    if ( $sefConfig->shVmInsertManufacturerName && !empty($shManufactureName)) {
      $title[] = $shManufactureName;
      $shManufactureName = '';  // don't put it twice
    }
    if (isset($manufacturer_id))  // V 1.2.4.m
    shRemoveFromGETVarsList('manufacturer_id');

    // process $root
    if (!empty($root)) {
      // first insert root cat
      $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_ROOT_CAT'].$sefConfig->replacement.$root;
      shRemoveFromGETVarsList('root');
      // then insert child cat (but only one cat, not full list of nested cats)
      $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_CATEGORY'].$sefConfig->replacement.$category_id;
    } else { // if no $root process categories as usual
      if (($sefConfig->shVMInsertCategories && !empty ($category_id))
      || (!$sefConfig->shVMInsertCategories && empty ($product_id))) {
        $title = array_merge( $title, vm_sef_get_category_array( $database, $category_id, $option, $shLangName ));
      } else { // V 1.2.4.f : still need to add category id even if we don't want to add name!!
        if (!empty($category_id)) {
          $title = array_merge( $title, vm_sef_get_category_array( $database, $category_id, $option, $shLangName ));
        }
      }
    }
    // V 1.2.4.m
    if (isset($category_id))
    shRemoveFromGETVarsList('category_id');
    if (empty($product_id) && empty($product_type_id) && empty($category_id) && empty($manufacturer_id) && empty($Search)
    && empty($root)) {
      $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_LIST_ALL_SHOP_PRODUCTS'];
    } else {
      if (!empty($shManufactureName)) // V 1.2.4.r
      $title[] = $shManufactureName;
      if (!empty($Search)) {
        $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_SH_REGULAR_SEARCH'];
        shRemoveFromGETVarsList('Search');
      } elseif ($sefConfig->shVmAdditionalText)  // V 1.2.4.k additional text is now optional
      $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_LIST_ALL_PRODUCTS'];
    }

    // process filter hack
    $shFilterType = isset($shFilterType) ? @$shFilterType : null;
    $shFilterValue = isset($shFilterValue) ? @$shFilterValue : null;
    switch ($shFilterType) {
      case '':
        break;
      case 'minStock':
        $title[] = 'dispo';
        if (!empty( $shFilterValue)) {
          $title[] = $shFilterValue;
        }
        shRemoveFromGETVarsList( 'shFilterType');
        shRemoveFromGETVarsList( 'shFilterValue');
        break;
    }

    if( @count($title) > 0 )
    if (isset($sefConfig->suffix))
    $title[count($title)-1] .= $sefConfig->suffix;
    else $title[] = '/';
    else $dosef = false;
    break;
      case 'shop.downloads':  // V 1.2.4.g 2007-04-07
        if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
        if ($shVmCChk) {
          $title[] = 'vmchk';
        }
        $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_DOWNLOADS_TITLE'];
        $title[] = '/';
        break;
      case 'shop.cart':
        if (!empty($func))
        switch ($func){
          case 'cartAdd':
            if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
            if ($shVmCChk) $title[] = 'vmchk';
            $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_ADD'];
            shRemoveFromGETVarsList('func');
            if (!empty($product_id)) {  // if a product_id is set (it should!)
              $title[] = vmSefGetProductName( $product_id, $option, $shLangName, $shLangIso);
            }
            shRemoveFromGETVarsList('product_id');
            break;
          case 'cartUpdate':
            if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
            if ($shVmCChk) $title[] = 'vmchk';
            $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_UPDATE'];
            shRemoveFromGETVarsList('func');
            if (!empty($product_id)) {  // if a product_id is set (it should!)
              $title[] = vmSefGetProductName( $product_id, $option, $shLangName, $shLangIso);
            }
            shRemoveFromGETVarsList('product_id');
            break;
          case 'cartdelete':
            if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
            if ($shVmCChk) $title[] = 'vmchk';
            $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_DELETE'];
            shRemoveFromGETVarsList('func');
            if (!empty($product_id)) {  // if a product_id is set (it should!)
              $title[] = vmSefGetProductName( $product_id, $option, $shLangName, $shLangIso);
            }
            shRemoveFromGETVarsList('product_id');
            break;
        } else {  // only show cart, no function
          if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
          if ($shVmCChk) $title[] = 'vmchk';
          $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_CART_TITLE'];
        }
        if (count($title) == 0)
        $dosef = false;
        break;

          case 'shop.product_details':
            $q = "SELECT p.product_id, x.category_id FROM #__vm_product AS p LEFT JOIN #__vm_product_category_xref AS x ON p.product_id = x.product_id";
            $q .= "\n WHERE p.product_id = %s";
            if (SH_VM_ALLOW_PRODUCTS_IN_MULTIPLE_CATS && !empty( $category_id)) {
              $q .= " AND x.category_id=" . ((int)$category_id) . ';';
            }
            $database->setQuery( sprintf( $q, $product_id ) );
            if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
            $rows = $database->loadObjectList( '', false);
            else $rows = $database->loadObjectList( );
            if( @count( $rows ) > 0 ){
              $row = $rows[0];
              if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
              if ($shVmCChk) $title[] = 'vmchk';
              if ( $sefConfig->shVmInsertManufacturerName && !empty($manufacturer_id)) {
                $query  = "SELECT mf_name, manufacturer_id FROM #__vm_manufacturer" ;
                $query .= "\n WHERE manufacturer_id=".$manufacturer_id;
                $database->setQuery( $query );
                if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
                $result = $database->loadObject(false);
                else $result = $database->loadObject();
                if (!empty($result)) {
                  $title[] = ($sefConfig->shInsertManufacturerId ? $manufacturer_id.$sefConfig->replacement: '')
                  .$result->mf_name;
                  shRemoveFromGETVarsList('manufacturer_id');
                } else {
                  $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_MANUFACTURER_MOD']  // add its ID to URL
                  .$sefConfig->replacement.$manufacturer_id;
                  shRemoveFromGETVarsList('manufacturer_id');
                }

              } else if ( $sefConfig->shVmInsertManufacturerName && !empty($product_id)) {
                $query  = "SELECT m.mf_name, m.manufacturer_id FROM #__vm_manufacturer AS m" ;
                $query .= "\n LEFT JOIN #__vm_product_mf_xref AS x ON m.manufacturer_id = x.manufacturer_id";
                $query .= "\n WHERE x.product_id=".$product_id;
                $database->setQuery( $query );
                if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
                $result = $database->loadObject(false);
                else $result = $database->loadObject();
                if (!empty($result)) {
                  $title[] = ($sefConfig->shInsertManufacturerId ? $result->manufacturer_id.$sefConfig->replacement: '')
                  .$result->mf_name;
                  shRemoveFromGETVarsList('manufacturer_id');
                } else {
                  $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_MANUFACTURER_MOD']  // add its ID to URL
                  .$sefConfig->replacement.$manufacturer_id;
                  shRemoveFromGETVarsList('manufacturer_id');
                }
              } else
              if (isset($manufacturer_id))
              shRemoveFromGETVarsList('manufacturer_id'); // this has to be manufacturer_id=0

              if ($sefConfig->shVMInsertCategories) {
                $title = array_merge( $title, vm_sef_get_category_array( $database, $row->category_id, $option, $shLangName ));
              }
              if (isset($category_id))  // V 1.2.4.m
              shRemoveFromGETVarsList('category_id');
              // $title[] = $sefConfig->shInsertProductId ? $product_id.$sefConfig->replacement.$row->$shProductName:$row->$shProductName;  // V 1.2.4.s
              $title[] = vmSefGetProductName( $product_id, $option, $shLangName, $shLangIso);
              shRemoveFromGETVarsList('product_id');
              // v 1.2.4.f : flypage param was not passed on
              // V 1.2.4.m : now can be switched on/off
              if (!empty($flypage) && $sefConfig->shVmInsertFlypage) {
                $title[] = empty($sh_LANG[$shLangIso]['_PHPSHOP_PRODUCT_DETAILS_'.$flypage]) ?
                $flypage : $sh_LANG[$shLangIso]['_PHPSHOP_PRODUCT_DETAILS_'.$flypage];
                shRemoveFromGETVarsList('flypage');
              } else if (!empty($flypage)) shRemoveFromGETVarsList('flypage');

              // process filter hack
              $shFilterType = isset($shFilterType) ? @$shFilterType : null;
              $shFilterValue = isset($shFilterValue) ? @$shFilterValue : null;
              switch ($shFilterType) {
                case '':
                  break;
                case 'minStock':
                  $title[] = 'dispo';
                  if (!empty( $shFilterValue)) {
                    $title[] = $shFilterValue;
                  }
                  shRemoveFromGETVarsList( 'shFilterType');
                  shRemoveFromGETVarsList( 'shFilterValue');
                  break;
              }

            } else $dosef = false;
            break;

                case 'shop.search':
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk) $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_SEARCH_TITLE'];
                  if (count($title) == 0) $dosef = false;
                  break;
                case 'shop.registration':  // V 1.2.4.k
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk) $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_SH_CREATE_ACCOUNT'];
                  break;
                case 'shop.view_images':
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk) $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_MORE_IMAGES'];

                  if ($sefConfig->shVMInsertCategories && !empty($category_id)) {
                    $title = array_merge( $title, vm_sef_get_category_array( $database, $category_id, $option, $shLangName ));
                  }
                  if (isset($category_id)) // V 1.2.4.m
                  shRemoveFromGETVarsList('category_id');

                  if (!empty($product_id)) {
                    $title[] = vmSefGetProductName( $product_id, $option, $shLangName, $shLangIso);// V 1.2.4.s
                  }
                  if (isset($product_id))  // V 1.2.4.r
                  shRemoveFromGETVarsList('product_id');

                  if (!empty($image_id))
                  if ($image_id == 'product') {
                    $title[] = $sh_LANG[$shLangIso]['_PHPSHOP_SH_PRODUCT_IMAGE'];
                  }
                  else {
                    $q = "SELECT file_id, file_title FROM #__vm_product_files";  // then try to add its name as well
                    $q .= "\n WHERE file_id = %s";
                    $database->setQuery( sprintf( $q, $image_id ) );
                    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
                    $row = $database->loadObject( false);
                    else $row = $database->loadObject( );
                    if (!empty($row)) {
                      $title[] = $row->file_id.$sefConfig->replacement.$row->file_title;
                    }
                  }
                  if (isset($image_id))  // V 1.2.4.r
                  shRemoveFromGETVarsList('image_id');
                  // V 1.2.4.m : now can be switched on/off
                  if (!empty($flypage) && $sefConfig->shVmInsertFlypage) {
                    $title[] = empty($sh_LANG[$shLangIso]['_PHPSHOP_PRODUCT_DETAILS_'.$flypage]) ?
                    $flypage : $sh_LANG[$shLangIso]['_PHPSHOP_PRODUCT_DETAILS_'.$flypage];
                    shRemoveFromGETVarsList('flypage');
                  } else  if (isset($flypage)) shRemoveFromGETVarsList('flypage');
                  if (count($title) == 0) $dosef = false;
                  break;

                case 'checkout.index': // note: this is not currently used, as VM 1.0.10 misses some calls to shSefRelToAbs()
                  if (!$sefConfig->shForceNonSefIfHttps) {
                    if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                    $ssl_redirect = isset($ssl_redirect) ? @$ssl_redirect : null;
                    if ($ssl_redirect) {  // let's add ssl just after shopname so that we can block it through robots.txt if we want
                      $title[] = 'ssl';
                      shRemoveFromGETVarsList('ssl_redirect');
                    }
                    $cartReset = isset($cartReset) ? @$cartReset : null; // need to preserve cartReset param, used when
                    if ($cartReset) {                                    // switching to SSL w/ shared certificate
                      $title[] = 'cartReset'.$sefConfig->replacement.$cartReset;
                      shRemoveFromGETVarsList('cartReset');
                    }
                    if (empty($sefConfig->shAppendRemainingGETVars)) {  // if martID is not passed as a regular parameter, we need to encode it in the sef URL
                      $martID = isset($martID) ? @$martID : null;
                      if ($martID) {  // 1.2.4.j need to preserve martID when switching to shared SSL
                        $title[] = 'martID'.$sefConfig->replacement.$martID;
                      }
                    } // if shAppendRemainingGETVars is true, then no need to encode martID in sef URL, it will be passed as an additional param
                    if ($shVmCChk)
                    $title[] = 'vmchk';
                    $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_CHECKOUT_TITLE'];
                    if ((!empty($checkout_this_step))
                    || (!empty($ship_to_info_id))
                    || (!empty($shipping_rate_id))
                    || (!empty($payment_method_id))
                    || (!empty($first_payment_method_id))
                    || (!empty($payment_method_id))
                    || (!empty($checkout_next_step)))
                    $dosef=false;
                  } else $dosef = false;
                  break;

                case 'checkout.confirm':
                case 'checkout.customer_info':
                case 'checkout.dandomain_cc_form':
                case 'checkout.dandomain_result':
                case 'checkout.danhost_cc_form':
                case 'checkout.danhost_result':
                case 'checkout.freepay_cc_form':
                case 'checkout.freepay_result':
                case 'checkout.login_form':
                case 'checkout.paymentradio':
                case 'checkout.result':
                case 'checkout.thankyou':
                case 'checkout.wannafind_cc_form':
                case 'checkout.wannafind_result':
                case 'checkout_bar':
                case 'checkout_register_form':
                  $dosef = false; // V x 28/08/2007 21:13:46 does not work with Paypal
                  break;

                case 'account.index':
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk)
                  $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_ACCOUNT_TITLE'];
                  break;

                case 'account.billing':
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk)
                  $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_ACC_ACCOUNT_INFO'];
                  // V 1.2.4.f april 4, 2007
                  if (!empty($next_page)) {
                    $title[] = $next_page;
                  }
                  if (isset($next_page))  // V 1.2.4.r
                  shRemoveFromGETVarsList('next_page');
                  break;

                case 'account.shipping':
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk)
                  $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_ACC_SHIP_INFO'];
                  break;

                case 'account.order_details':
                  $order_id = isset($order_id) ? @$order_id : null;
                  if ($sefConfig->shVmInsertShopName) $title[] = $shShopName;
                  if ($shVmCChk)
                  $title[] = 'vmchk';
                  $title[] =  $sh_LANG[$shLangIso]['_PHPSHOP_VIEW'].$sefConfig->replacement
                  .$sh_LANG[$shLangIso]['_PHPSHOP_ORDER_ITEM']
                  .($order_id ? $sefConfig->replacement.'id'.strval($order_id):'');
                  if (isset($order_id))  // V 1.2.4.r
                  shRemoveFromGETVarsList('order_id');
                  break;

                case '':  // this is main menu link, let's fetch menu title
                case 'shop.index':
                  $title[] = getMenuTitle($option, (isset($task) ? @$task : null), $Itemid, '', $shLangName );
                  if ($shVmCChk)
                  $title[] = 'vmchk';

                  // process filter hack
                  $shFilterType = isset($shFilterType) ? @$shFilterType : null;
                  $shFilterValue = isset($shFilterValue) ? @$shFilterValue : null;
                  switch ($shFilterType) {
                    case '':
                      break;
                    case 'minStock':
                      $title[] = 'dispo';
                      if (!empty( $shFilterValue)) {
                        $title[] = $shFilterValue;
                      }
                      shRemoveFromGETVarsList( 'shFilterType');
                      shRemoveFromGETVarsList( 'shFilterValue');
                      break;
                  }

                  if (count($title) == 0) $dosef = false;
                  break;
                    default:
                      $dosef = false;
                      break;
}

// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef){
  $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
  (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
  (isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
