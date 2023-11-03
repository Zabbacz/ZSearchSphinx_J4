<?php
/**
 * Helper class for SearchSphinx! module
 * 
 * @subpackage Modules
 * @license        GNU/GPL, see LICENSE.php
 * mod_zsearchsphinx is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

 defined('_JEXEC') or die('Restricted access');

class ModZSearchSphinxHelper
{
    public static function getAjax() {
        $input = JFactory::getApplication()->input;
        if ($input -> exists('query')){
        $q  = $input->post->get('query', '', 'string');
        $condition = preg_replace('/[^A-Za-z0-9\- ]/', '', $q);
          $db = JFactory::getDBO();
          $db = JDatabaseDriver::getInstance(self::pripojDatabazi('sphinx'));
          $stmt = $db->getQuery(true);
            $aq = explode(' ',$q);
            if(strlen($aq[count($aq)-1])<3){
        	$query = $q;
            }else{
                $query = $q.'*';
            }
            $stmt
                ->select($db->quoteName("product_name"))
                ->from($db->quoteName("#__sphinx_test1"))
                ->where("MATCH"."('".$query."')"." ORDER BY product_in_stock DESC LIMIT  0,10 OPTION ranker=sph04");

        $db->setQuery($stmt);
        $results = $db->loadObjectList();
        $replace_string = '<b>'.$condition.'</b>';
        if($results){
            foreach($results as $row){
                $data[] = array(
                    'product_name'		=>	str_ireplace($condition, $replace_string, $row->product_name)
            );
            }
            echo json_encode($data);
        }
        else{
            echo json_encode('');
        }
    }
    $input = JFactory::getApplication()->input->json;
    if ($input -> exists('search_query')){
    
    $post_data = json_decode(file_get_contents('php://input'), true);
	$data = array(
		':search_query'		=>	$post_data['search_query']
	);
    $db = JFactory::getDBO(); 
    $db = JDatabaseDriver::getInstance(self::pripojDatabazi('joomla'));

    $query = $db->getQuery(true);
    $query
        ->insert($db->quoteName('#__zsphinx_recent_search'))
        ->columns($db->quoteName('search_query'))
        ->values('"'.$data[':search_query'].'"');
    $db->setQuery($query);
    $db->execute();        


	$output = array(
		'success'	=>	true
	);
    

	echo json_encode($output);

}

    }

    public static function getSearch()
    {
    $input = JFactory::getApplication()->input;
    if ($input -> exists('search_box'))
    {
        $query  = $input->get('search_box', '', 'string');
        if(strlen($query)<4){$query='musiszadattriznaky';}
        $docs = array();
        $offset = 24;
        $current = 1;
        $url = 'obchod/';
        $user = JFactory::getUser();
        /*mozna bude potreba casem nahradit
        $app     = Factory::getApplication();
        $user = $app->getIdentity();
        $userId = $user->id;
        */        
        $userId = $user->get( 'id' );

        $znacky_search = $input -> get('znacky_search','','string');
        $query_order = '%';
        if($znacky_search){
            $text_db = '$db->quoteName';
            $query_order = $znacky_search;
        }
        $query .= ' '.$query_order;
        $query = (string) preg_replace('/[^\p{L}\d\s]/u', ' ', $query);
        $query = trim($query);
//        $query = $query.'*';
        $start  = $input->get('start', '0', 'INT');
        $current = $start/$offset+1;
        $db = JFactory::getDBO();
        $db = JDatabaseDriver::getInstance(self::pripojDatabazi('sphinx'));
        $stmt = $db->getQuery(true);
        $stmt
            ->select ($db->quoteName('id'))
            ->from($db->quoteName('#__sphinx_test1'))
            ->where("MATCH"."('".$query."')"." ORDER BY product_in_stock DESC LIMIT "  . $start .",". $offset." OPTION ranker=sph04,field_weights=(product_name=100)");
        $db->setQuery($stmt);
        $rows = $db->loadAssocList();
        $meta=$db->setQuery('show meta');
        $meta = $db->loadAssocList();
        foreach($meta as $m) {
	    $meta_map[$m['Variable_name']] = $m['Value'];
	}
        $total_found = $meta_map['total_found'];
        $total = $meta_map['total'];
        $total_array = array('total'=> $total, 'total_found' => $total_found, 'offset' => $offset, 'current' => $current,'start' => $start, 'query' => $query);
     	$ids = array();
        $tmpdocs = array();
        if (count($rows)> 0) {
            foreach ($rows as  $v) {
        		$ids[] =  $v['id'];
		    }
            $db = JFactory::getDBO();
            $db = JDatabaseDriver::getInstance(self::pripojDatabazi('joomla'));
            $user_group =$db->getQuery(true);
            $user_group
                    ->select ($db->quoteName ('virtuemart_shoppergroup_id'))
                    ->from ($db->quoteName ('#__virtuemart_vmuser_shoppergroups'))
                   ->where ($db->quoteName('virtuemart_user_id'). '=' .$userId);
            $db->setQuery($user_group);
            $row_user_group = $db->loadRow();
            if(!$row_user_group){$row_user_group[0]=5;}
//            $row_user_group[0]=0;
            $q = $db->getQuery(true);
            $q
                ->select ($db->quoteName (array('t1.virtuemart_product_id', 't3.product_in_stock','product_name', 'virtuemart_category_id', 'product_availability','product_price','file_url', 't3.product_params', 't7.calc_value', 't9.mf_name', 't9.virtuemart_manufacturer_id')))
                ->from($db->quoteName('#__virtuemart_products_cs_cz','t1'))
                ->join('INNER',$db->quoteName('#__virtuemart_product_prices','t4'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t4.virtuemart_product_id'))    
                ->join('INNER',$db->quoteName('#__virtuemart_products','t3'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t3.virtuemart_product_id'))
                ->join('INNER',$db->quoteName('#__virtuemart_product_medias','t5'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t5.virtuemart_product_id'))    
                ->join('INNER',$db->quoteName('#__virtuemart_medias','t6'). ' ON ' . $db->quoteName('t5.virtuemart_media_id') . ' = ' . $db->quoteName('t6.virtuemart_media_id'))
                ->join('INNER',$db->quoteName('#__virtuemart_calcs','t7'). ' ON ' . $db->quoteName('t4.product_tax_id') . ' = ' . $db->quoteName('t7.virtuemart_calc_id'))
                ->join('INNER',$db->quoteName('#__virtuemart_product_manufacturers','t8'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t8.virtuemart_product_id'))
                ->join('INNER',$db->quoteName('#__virtuemart_manufacturers_cs_cz','t9'). ' ON ' . $db->quoteName('t8.virtuemart_manufacturer_id') . ' = ' . $db->quoteName('t9.virtuemart_manufacturer_id'))
                ->join('LEFT',$db->quoteName('#__virtuemart_product_categories','t2'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t2.virtuemart_product_id'))
                ->where($db->quoteName('t1.virtuemart_product_id'). ' IN '.'  (' . implode(",", $ids) . ')')
                ->where($db->quoteName('t4.virtuemart_shoppergroup_id'). ' = '.$row_user_group[0]);
            $db->setQuery ($q);
            $q = $db->loadAssocList(); 
            foreach ($q as $row) {
                $parametry = self::getBaleni($row['product_params']);
                $price = Round($row['product_price'],2);
                
                $sdph = Round($price*(1+($row['calc_value']/100)),2);
                $tmpdocs[$row['virtuemart_product_id']] = array(
                    'product_name' => $row['product_name'], 
                    'virtuemart_product_id' => $row['virtuemart_product_id'],
                    'virtuemart_category_id' => $row['virtuemart_category_id'],
                    'product_availability' => $row['product_availability'],
                    'product_price' => $price,
                    'file_url' => $row['file_url'],
                    'min_order_level' => $parametry['min_order_level'],
                    'step_order_level' => $parametry['step_order_level'],
                    's_dph' => $sdph, 
                    'manufacturer' => $row['mf_name'],
                    'manufacturer_id' => $row['virtuemart_manufacturer_id'],
                    'product_in_stock' => $row['product_in_stock'],);
        } 
            foreach ($ids as $id) {
                $docs[] = $tmpdocs[$id];
    		}
            $last = count ($docs)+1;
            $docs[$last]=$total_array;
	}
    }
	return $docs;  
}

public static function getManufacturers($query_znacky = null){
    $db = JFactory::getDBO();
    $db = JDatabaseDriver::getInstance(self::pripojDatabazi('sphinx'));
    $stmt2 = $db->getQuery(true);
    $stmt2
        ->select ($db->quoteName('mf_name'))
        ->from($db->quoteName('#__sphinx_test1'))
        ->where("MATCH"."('".$query_znacky."')"." GROUP BY mf_name LIMIT  0,1000 OPTION ranker=sph04");

    $db->setQuery($stmt2);
//    $rows = $db->loadAssocList();
    $manufacturers = $db->loadColumn();
/*    $ids2 = array();
    $tmpdocs2 = array();
    if (count($rows)> 0) {
        foreach ($rows as  $v) {
        $ids2[] =  $v['id'];
        }
    $db = JFactory::getDBO();
    $db = JDatabaseDriver::getInstance(self::pripojDatabazi('joomla'));
    $q = $db->getQuery(true);
    $q
        ->select ($db->quoteName ('t3.mf_name'))
        ->from($db->quoteName('#__virtuemart_products','t1'))
        ->join('INNER',$db->quoteName('#__virtuemart_product_manufacturers','t2'). ' ON ' . $db->quoteName('t1.virtuemart_product_id') . ' = ' . $db->quoteName('t2.virtuemart_product_id'))
        ->join('INNER',$db->quoteName('#__virtuemart_manufacturers_cs_cz','t3'). ' ON ' . $db->quoteName('t2.virtuemart_manufacturer_id') . ' = ' . $db->quoteName('t3.virtuemart_manufacturer_id'))
        ->where($db->quoteName('t1.virtuemart_product_id'). ' IN '.'  (' . implode(",", $ids2) . ')')
        ->group($db->quoteName('t3.mf_name'));
        $db->setQuery($q);
        $manufacturers = $db->loadColumn();
    }*/
    return $manufacturers;
    
}

public static function getBaleni($vstup) {
    mb_internal_encoding("UTF-8");
    $vystup = array();
    if($vstup){
        $delka = mb_strlen($vstup);
        $podretezec = mb_strpos($vstup,':');
        $nahrad = array(' ' => '');
        $vstup_zprac = (mb_substr(strtr($vstup,$nahrad), $podretezec, $delka));
        $cisla = explode('|', $vstup_zprac);
        $i = 0; foreach ($cisla as $cislo):
        $polozka = array(str_replace('"','',explode('=',($cisla[$i]))));
        if(isset($polozka[0][1])){
            $key = $polozka[0][0];
            $value = $polozka[0][1];
            $vystup[$key] = $value;
        }
    $i++; endforeach;
    }
    else {
        $vystup["min_order_level"] = "1";
        $vystup["step_order_level"] = "1";

    }   
    return($vystup);
}

private static function pripojDatabazi($database) {
            $option = array();
            switch ($database)
     {    case 'sphinx':
             $option['driver']   = 'mysql';            // Database driver name
             $option['host']     = '127.0.0.1:9306';    // Database host name
//             $option['user']     = JFactory::getApplication()->get('user');      // User for database authentication
//             $option['password'] = JFactory::getApplication()->get('password');   // Password for database authentication
             $option['prefix']   = '';             // Database prefix (may be empty)

             break;
         case 'joomla':
             $option['driver']   = 'mysqli';            // Database driver name
             $option['host']     = JFactory::getApplication()->get('host');    // Database host name
             $option['user']     = JFactory::getApplication()->get('user');       // User for database authentication
             $option['password'] = JFactory::getApplication()->get('password');   // Password for database authentication
             $option['database'] = JFactory::getApplication()->get('db');     // Database name
             $option['prefix']   = JFactory::getApplication()->get('dbprefix');             // Database prefix (may be empty)
             break;

}     
return $option;
       
   }
}
