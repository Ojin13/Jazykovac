<?php
/*
//--------------------------------------------------
            Created by Robin Chmelik/@Ojin



Setup:
1. Copy this whole code into your functions.php file (to your active theme).
2. Do the same for your other WordPress instalations for other languages.

eg.:     www.mysite.com     =>  Your first WordPress instalation for English version of your site.
         www.cz.mysite.com  =>  Your second WordPress instalation for Czech version of your site.
         www.de.mysite.com  =>  Your third WordPress instalation for German version of your site.

3. Put your subdomain names (in this example "cz" and "de") into global variable/array $subdomains (see below).
4. Setup global variable $mainLanguage - for all WordPress installations same. This variable declares which language is not just subdomain installation. In this case its English so it should be "en" (www.mysite.com => no subdomai => main language / www.de.mysite.com => has subdomain => isn't main language)
5. Setup global variable $currentLanguage - for every WordPress installation different. eg.: this is english installation so current language is "en" on our German installation in german functions.php file it would be "de".
6. Create new database from DB_mapping.sql file and here setup password, username and hostname for PDO connection (see below). 
7. Setup variables that allow or disable specific synchronization task like sync creating/deleting etc.. In default all is turned on.
8. Setup variable $yourSiteDomain but dont include "www." on the start. (should looks like "my-site.com")
9. Remember that language codes from $subdomains array must be same as prefexes of collumns from database tables.





//--------------------------------------------------
*/














//----------------------------------------------MAPPING-------------------------------------------------------

	//connect to mapping database
    global $pdo;
    if(!isset($pdo))
    {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=xxxx","xxxx","xxxx");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Connection to database failed: " . $e->getMessage();
            $pdo = null;
            return;
        }
    }
//--------------------------------------------
    
    
    
    
    
    
    
    
    
    

    
    
    
    //----Global variables
    global $subdomains;
    $subdomains = array   //[0]:subdomain name that will be visible in url eg.: "italy.my-site.com"     [1]:language shortcut for subdomain language eg.: "en" - must be same as prefixes in database
                          //because its 2D array shown subdomain - [0] - in url and language code - [1] -for database and http requests can be different.
    (
        array("italy","it"),
        //array("en","en"),
        //array("france","fr"),
        //array("sp","sp"),
    );

    
    global $currentLanguage;
    $currentLanguage = "en";    //for every installation different

    global $mainLanguage;
    $mainLanguage = "en";       //for all installation same
    

    global $yourSiteDomain;
    $yourSiteDomain = "your-domain.com";       //change this to your domain dont add "www." to the start! 
    
    
    $allowSyncCreate = true;
    $allowSyncTrash = true;
    $allowSyncUntrash = true;
    $allowSyncDelete = true;
    $allowSyncTaxonomyDelete = true;
    //----------------------


/*
$allowSyncCreate = Allow synchronized creating of pages and posts on all subdomains at once. Is activated when post or page is created. //CZ: Povolit synchronizovane vytvaření stranek a příspěvků na všech instalacích naráz - volá se při vytvoření nové stránky/příspěvku
$allowSyncTrash = Allow synchronized move of pages and posts to trash bin on all subdomains at once. Is activated when post or page is moved to trash bin. //CZ: povolit synchronizovane přesunutí stranek a příspěvků do koše na všech instalacích naráz - volá se při odstranění příspěvku/stránky
$allowSyncUntrash = Allow synchronized untrash of pages and posts from trash bin on all subdomains at once. Is activated when post or page is moved from trash bin. //CZ: povolit synchronizovane obnovení stranek a příspěvků na všech instalacích naráz - volá se při obnovení stránky/příspěvku z koše
$allowSyncDelete = Allow synchronized untrash of pages and posts from trash bin on all subdomains at once. Is activated when post or page is moved from trash bin. //CZ: povolit synchronizovane pernamentní mazání stranek a příspěvků na všech instalacích naráz - volá se při mazání koše
$allowSyncTaxonomyDelete = Allow synchronized pernament delete of categories and tags on all subdomains at once. Is activated when taxonomy is deleted. //CZ: povolit synchronizovane pernamentní mazání katergorií a tagů na všech instalacích naráz - volá se při mazání taxonomií



Map all posts, products and pages:
http://your-domain.com/wp-admin/admin-ajax.php?action=map_all_posts

Map all taxonomy:
http://your-domain.com/wp-admin/admin-ajax.php?action=map_all_taxonomy

Map everything:
http://your-domain.com/wp-admin/admin-ajax.php?action=map_everything
*/





//-----------------------Map on Create-----------------------------
//Hook to check if post was created
if($allowSyncCreate)
{
add_action( 'wp_insert_post', 'call_wp_remote_post', 10, 2 );
}

//Send HTTP request with post parameters in URL
function call_wp_remote_post ($post_id, $post )
{		
if ($post->post_status == 'publish' && empty(get_post_meta( $post_id, 'check_if_run_once' )))
{	

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
    
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => base64_encode($post->post_content),
            'post_excerpt'   => $post->post_excerpt,
            'post_name'      => $post->post_name,
            'post_parent'    => $post->post_parent,
            'post_password'  => $post->post_password,
            'post_status'    => 'draft',
            'post_title'     => $post->post_title,
            'post_type'      => $post->post_type,
            'to_ping'        => $post->to_ping,
            'menu_order'     => $post->menu_order
        );

    
    //------------------META DATA-------------------------
    $price = get_post_meta( $post_id, "_price" );
    $editLast = get_post_meta( $post_id, "_edit_last" );
    $editLock = get_post_meta( $post_id, "_edit_lock" );
    $sku = get_post_meta( $post_id, "_sku" );
    $salePrice = get_post_meta( $post_id, "_sale_price" );
    $totalSales = get_post_meta( $post_id, "total_sales" );
    $taxStatus = get_post_meta( $post_id, "_tax_status" );
    $manageStock = get_post_meta( $post_id, "_manage_stock" );
    $backOrders = get_post_meta( $post_id, "_backorders" );
    $lowStackAmount = get_post_meta( $post_id, "_low_stock_amount" );
    $soldIndividually = get_post_meta( $post_id, "_sold_individually" );
    $weight = get_post_meta( $post_id, "_weight" );
    $length = get_post_meta( $post_id, "_length" );
    $width = get_post_meta( $post_id, "_width" );
    $height = get_post_meta( $post_id, "_height" );
    $purchaseNote = get_post_meta( $post_id, "_purchase_note" );
    $virtualProduct = get_post_meta( $post_id, "_virtual" );
    $canDownload = get_post_meta( $post_id, "_downloadable" );
    $downloadLimit = get_post_meta( $post_id, "_download_limit" );
    $downloadExpiry = get_post_meta( $post_id, "_download_expiry" );
    $stock = get_post_meta( $post_id, "_stock" );
    $stockStatus = get_post_meta( $post_id, "_stock_status" );
    $averageRating = get_post_meta( $post_id, "_wc_average_rating" );
    $reviewCount = get_post_meta( $post_id, "_wc_review_count" );
    $regularPrice = get_post_meta( $post_id, "_regular_price" );
    
    

    $meta_args = array(
            '_price' => $price[0],
            '_edit_last' => $editLast[0],
            '_edit_lock' => $editLock[0],
            '_sku' => $sku[0],
            '_sale_price' => $salePrice[0],
            'total_sales' => $totalSales[0],
            '_tax_status' => $taxStatus[0],
            '_manage_stock' => $manageStock[0],
            '_backorders' => $backOrders[0],
            '_low_stock_amount' => $lowStackAmount[0],
            '_sold_individually' => $soldIndividually[0],
            '_weight' => $weight[0],
            '_length' => $length[0],
            '_width' => $width[0],
            '_height' => $height[0],
            '_purchase_note' => $purchaseNote[0],
            '_virtual' => $virtualProduct[0],
            '_downloadable' => $canDownload[0],
            '_download_limit' => $downloadLimit[0],
            '_download_expiry' => $downloadExpiry[0],
            '_stock' => $stock[0],
            '_stock_status' => $stockStatus[0],
            '_wc_average_rating' => $averageRating[0],
            '_wc_review_count' => $reviewCount[0],
            '_regular_price' => $regularPrice[0]	
    );
    //-------------------------------------------------
    
    
    global $currentLanguage;
    global $mainLanguage;
    global $subdomains;
    global $yourSiteDomain;
    global $pdo;

        //insert into idMapping table
        $stmt = $pdo->prepare("INSERT INTO idMapping(".$currentLanguage."_id) VALUES (?)");
        $stmt->execute([$post_id]);
        
  
        //All subdomains for upload	
        for($i=0;$i<sizeof($subdomains);$i++)
        {

            if($post->post_type == "product")
            {
                if($subdomains[$i][0] == $mainLanguage)
                {
                    wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$post_id."&".http_build_query($args)."&".http_build_query($meta_args)."&languageSubdomain=".$subdomains[$i][1]."&sourceLanguage=".$currentLanguage);
                }
                else
                {
                    wp_remote_post("http://".$subdomains[$i][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$post_id."&".http_build_query($args)."&".http_build_query($meta_args)."&languageSubdomain=".$subdomains[$i][1]."&sourceLanguage=".$currentLanguage);
                }
            }
            else
            {
                if($subdomains[$i][0] == $mainLanguage)
                {
                    wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$post_id."&".http_build_query($args)."&languageSubdomain=".$subdomains[$i][1]."&sourceLanguage=".$currentLanguage);
                }
                else
                {
                    wp_remote_post("http://".$subdomains[$i][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$post_id."&".http_build_query($args)."&languageSubdomain=".$subdomains[$i][1]."&sourceLanguage=".$currentLanguage);
                }
            }
        }
    

    //make sure to run only once (ignore revisions etc...)
    update_post_meta( $post_id, 'check_if_run_once', true );
}
}




//-----------------------Map on Create AJAX------------------------------
//Hook to create new Post from url parameters
add_action( 'wp_ajax_Ajax_create_post', 'Ajax_create_post' );
add_action( 'wp_ajax_nopriv_Ajax_create_post', 'Ajax_create_post' );



//Create new post
function Ajax_create_post()
{	
$args = array(
    'comment_status' => $_REQUEST["comment_status"],
    'ping_status'    => $_REQUEST["ping_status"],
    'post_author'    => $_REQUEST["post_author"],
    'post_content'   => base64_decode($_GET["post_content"]),
    'post_excerpt'   => $_REQUEST["post_excerpt"],
    'post_name'      => $_REQUEST["post_name"],
    'post_parent'    => $_REQUEST["post_parent"],
    'post_password'  => $_REQUEST["post_password"],
    'post_status'    => 'draft',
    'post_title'     => $_REQUEST["post_title"],
    'post_type'      => $_REQUEST["post_type"],
    'to_ping'        => $_REQUEST["to_ping"],
    'menu_order'     => $_REQUEST["menu_order"]
);

    
global $pdo;
global $currentLanguage;

//check if post isnt already in databse idMapping table
$stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["postid"]]);
$checkIfAlreadyExist = $stmt->fetch();

if(is_null($checkIfAlreadyExist[$currentLanguage."_id"]))
{
    //insert into idMapping table
    $newPostID = wp_insert_post( $args );
        
    $stmt = $pdo->prepare("UPDATE idMapping SET ".$_REQUEST["languageSubdomain"]."_id = ".$newPostID." WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["postid"]);
    $stmt->execute();
    



    //set META data
    if($_REQUEST["post_type"] == "product")
    {
        if(isset($_REQUEST["_price"]))
        {
            add_post_meta( $newPostID, "_price", $_REQUEST["_price"] );
        }
        
        if(isset($_REQUEST["_edit_last"]))
        {
            add_post_meta( $newPostID, "_edit_last", $_REQUEST["_edit_last"] );
        }
        
        if(isset($_REQUEST["_edit_lock"]))
        {
            add_post_meta( $newPostID, "_edit_lock", $_REQUEST["_edit_lock"] );
        }
        
        if(isset($_REQUEST["_sku"]))
        {
            add_post_meta( $newPostID, "_sku", $_REQUEST["_sku"] );
        }
        
        if(isset($_REQUEST["_sale_price"]))
        {
            add_post_meta( $newPostID, "_sale_price", $_REQUEST["_sale_price"] );
        }
        
        if(isset($_REQUEST["total_sales"]))
        {
            add_post_meta( $newPostID, "total_sales", $_REQUEST["total_sales"] );
        }
        
        if(isset($_REQUEST["_tax_status"]))
        {
            add_post_meta( $newPostID, "_tax_status", $_REQUEST["_tax_status"] );
        }
        
        if(isset($_REQUEST["_manage_stock"]))
        {
            add_post_meta( $newPostID, "_manage_stock", $_REQUEST["_manage_stock"] );
        }
        
        if(isset($_REQUEST["_backorders"]))
        {
            add_post_meta( $newPostID, "_backorders", $_REQUEST["_backorders"] );
        }
        
        if(isset($_REQUEST["_low_stock_amount"]))
        {
            add_post_meta( $newPostID, "_low_stock_amount", $_REQUEST["_low_stock_amount"] );
        }
        
        if(isset($_REQUEST["_sold_individually"]))
        {
            add_post_meta( $newPostID, "_sold_individually", $_REQUEST["_sold_individually"] );
        }
        
        if(isset($_REQUEST["_weight"]))
        {
            add_post_meta( $newPostID, "_weight", $_REQUEST["_weight"] );
        }
        
        if(isset($_REQUEST["_length"]))
        {
            add_post_meta( $newPostID, "_length", $_REQUEST["_length"] );
        }
        
        if(isset($_REQUEST["_width"]))
        {
            add_post_meta( $newPostID, "_width", $_REQUEST["_width"] );
        }
        
        if(isset($_REQUEST["_height"]))
        {
            add_post_meta( $newPostID, "_height", $_REQUEST["_height"] );
        }
        
        if(isset($_REQUEST["_purchase_note"]))
        {
            add_post_meta( $newPostID, "_purchase_note", $_REQUEST["_purchase_note"] );
        }
        
        if(isset($_REQUEST["_virtual"]))
        {
            add_post_meta( $newPostID, "_virtual", $_REQUEST["_virtual"] );
        }
        
        if(isset($_REQUEST["_downloadable"]))
        {
            add_post_meta( $newPostID, "_downloadable", $_REQUEST["_downloadable"] );
        }
        
        if(isset($_REQUEST["_download_limit"]))
        {
            add_post_meta( $newPostID, "_download_limit", $_REQUEST["_download_limit"] );
        }
        
        if(isset($_REQUEST["_download_expiry"]))
        {
            add_post_meta( $newPostID, "_download_expiry", $_REQUEST["_download_expiry"] );
        }
        
        if(isset($_REQUEST["_stock"]))
        {
            add_post_meta( $newPostID, "_stock", $_REQUEST["_stock"] );
        }
        
        if(isset($_REQUEST["_stock_status"]))
        {
            add_post_meta( $newPostID, "_stock_status", $_REQUEST["_stock_status"] );
        }
        
        if(isset($_REQUEST["_wc_average_rating"]))
        {
            add_post_meta( $newPostID, "_wc_average_rating", $_REQUEST["_wc_average_rating"] );
        }
        
        if(isset($_REQUEST["_wc_review_count"]))
        {
            add_post_meta( $newPostID, "_wc_review_count", $_REQUEST["_wc_review_count"] );
        }
        
        if(isset($_REQUEST["_regular_price"]))
        {
            add_post_meta( $newPostID, "_regular_price", $_REQUEST["_regular_price"] );
        }
    }
}
else
{
    //This post already exist
}

die();
}









//-----------------------Map all taxonomy------------------------------
add_action( 'wp_ajax_map_all_taxonomy', 'map_all_taxonomy' );
add_action( 'wp_ajax_nopriv_map_all_taxonomy', 'map_all_taxonomy' );



function map_all_taxonomy($shouldDie=true)
{	
    map_categories();
    map_tags();
    Send_Relationship_sync_request();

    if($shouldDie)
    {
        die();
    }
}


//-----------------------Map all posts------------------------------
add_action( 'wp_ajax_map_all_posts', 'map_all_posts' );
add_action( 'wp_ajax_nopriv_map_all_posts', 'map_all_posts' );


function map_all_posts($shouldDie=true)
{	
wp_post_remote_mapAllPosts();
wp_post_remote_mapAllProducts();
wp_post_remote_mapAllPages();

if($shouldDie)
{
    die();
}
}


//-----------------------Map whole website------------------------------
add_action( 'wp_ajax_map_everything', 'map_everything' );
add_action( 'wp_ajax_nopriv_map_everything', 'map_everything' );


function map_everything()
{	
map_all_posts(false);
map_all_taxonomy(false);
die();
}





//-----------------------Map all posts at once------------------------

add_action( 'wp_ajax_wp_post_remote_mapAllPosts', 'wp_post_remote_mapAllPosts' );
add_action( 'wp_ajax_nopriv_wp_post_remote_mapAllPosts', 'wp_post_remote_mapAllPosts' );


function wp_post_remote_mapAllPosts()
{
        global $currentLanguage;
        global $subdomains;
        global $yourSiteDomain;

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
    

        //Get all post IDs
        $postIds = get_posts(array(
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1		//Get all posts
        ));
        

                    
        for($i=0;$i<sizeof($postIds);$i++)
        {

            
            $post = get_post($postIds[$i]);
            
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => base64_encode($post->post_content),
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'draft',
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );
        
        
        
    
                global $pdo;
                
                //check if post isnt already in databse idMapping table
                $stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$postIds[$i]]);
                
                if($stmt->rowCount() == 0)
                {
                    $stmt = $pdo->prepare("INSERT INTO idMapping(".$currentLanguage."_id) VALUES (?)");
                    $stmt->execute([$postIds[$i]]);
                }
    
                global $mainLanguage;

                //insert post to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$postIds[$i]."&".http_build_query($args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$postIds[$i]."&".http_build_query($args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                }    
        }
        
echo "<p>Mapped all posts!</p>";
}












add_action( 'wp_ajax_wp_post_remote_mapAllProducts', 'wp_post_remote_mapAllProducts' );
add_action( 'wp_ajax_nopriv_wp_post_remote_mapAllProducts', 'wp_post_remote_mapAllProducts' );


function wp_post_remote_mapAllProducts()
{
        global $currentLanguage;
        global $subdomains;
        global $yourSiteDomain;


        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
    
    


        //Get all post IDs
        $postIds = get_posts(array(
            'post_type' 	  => 'product',
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1		//Get all posts
        ));
        
                    
        for($i=0;$i<sizeof($postIds);$i++)
        {

            $post = get_post($postIds[$i]);
            
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => base64_encode($post->post_content),
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'draft',
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );
            
            
            
            //------------------META DATA-------------------------
            $price = get_post_meta( $postIds[$i], "_price" );
            $editLast = get_post_meta( $postIds[$i], "_edit_last" );
            $editLock = get_post_meta( $postIds[$i], "_edit_lock" );
            $sku = get_post_meta( $postIds[$i], "_sku" );
            $salePrice = get_post_meta( $postIds[$i], "_sale_price" );
            $totalSales = get_post_meta( $postIds[$i], "total_sales" );
            $taxStatus = get_post_meta( $postIds[$i], "_tax_status" );
            $manageStock = get_post_meta( $postIds[$i], "_manage_stock" );
            $backOrders = get_post_meta( $postIds[$i], "_backorders" );
            $lowStackAmount = get_post_meta( $postIds[$i], "_low_stock_amount" );
            $soldIndividually = get_post_meta( $postIds[$i], "_sold_individually" );
            $weight = get_post_meta( $postIds[$i], "_weight" );
            $length = get_post_meta( $postIds[$i], "_length" );
            $width = get_post_meta( $postIds[$i], "_width" );
            $height = get_post_meta( $postIds[$i], "_height" );
            $purchaseNote = get_post_meta( $postIds[$i], "_purchase_note" );
            $virtualProduct = get_post_meta( $postIds[$i], "_virtual" );
            $canDownload = get_post_meta( $postIds[$i], "_downloadable" );
            $downloadLimit = get_post_meta( $postIds[$i], "_download_limit" );
            $downloadExpiry = get_post_meta( $postIds[$i], "_download_expiry" );
            $stock = get_post_meta( $postIds[$i], "_stock" );
            $stockStatus = get_post_meta( $postIds[$i], "_stock_status" );
            $averageRating = get_post_meta( $postIds[$i], "_wc_average_rating" );
            $reviewCount = get_post_meta( $postIds[$i], "_wc_review_count" );
            $regularPrice = get_post_meta( $postIds[$i], "_regular_price" );
            
            

            $meta_args = array(
                    '_price' => $price[0],
                    '_edit_last' => $editLast[0],
                    '_edit_lock' => $editLock[0],
                    '_sku' => $sku[0],
                    '_sale_price' => $salePrice[0],
                    'total_sales' => $totalSales[0],
                    '_tax_status' => $taxStatus[0],
                    '_manage_stock' => $manageStock[0],
                    '_backorders' => $backOrders[0],
                    '_low_stock_amount' => $lowStackAmount[0],
                    '_sold_individually' => $soldIndividually[0],
                    '_weight' => $weight[0],
                    '_length' => $length[0],
                    '_width' => $width[0],
                    '_height' => $height[0],
                    '_purchase_note' => $purchaseNote[0],
                    '_virtual' => $virtualProduct[0],
                    '_downloadable' => $canDownload[0],
                    '_download_limit' => $downloadLimit[0],
                    '_download_expiry' => $downloadExpiry[0],
                    '_stock' => $stock[0],
                    '_stock_status' => $stockStatus[0],
                    '_wc_average_rating' => $averageRating[0],
                    '_wc_review_count' => $reviewCount[0],	
                    '_regular_price' => $regularPrice[0]
            );
            //-------------------------------------------------
        
        
        
    
                global $pdo;
                
                //check if post isnt already in databse idMapping table
                $stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$postIds[$i]]);
                
                if($stmt->rowCount() == 0)
                {
                    $stmt = $pdo->prepare("INSERT INTO idMapping(".$currentLanguage."_id) VALUES (?)");
                    $stmt->execute([$postIds[$i]]);
                }
    
                global $mainLanguage;
                
                //insert post to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$postIds[$i]."&".http_build_query($args)."&".http_build_query($meta_args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$postIds[$i]."&".http_build_query($args)."&".http_build_query($meta_args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                }    
        }
echo "<p>Mapped all products!</p>";
}

//------------------------------------------------------------------







//-----------------------Map all pages at once------------------------

add_action( 'wp_ajax_wp_post_remote_mapAllPages', 'wp_post_remote_mapAllPages' );
add_action( 'wp_ajax_nopriv_wp_post_remote_mapAllPages', 'wp_post_remote_mapAllPages' );


function wp_post_remote_mapAllPages()
{
        global $currentLanguage;
        global $subdomains;
        global $yourSiteDomain;

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
    

        //Get all page IDs
        $page_ids=get_all_page_ids();

        

                    
        for($i=0;$i<sizeof($page_ids);$i++)
        {

            
            $page = get_page($page_ids[$i]);
            
            $args = array(
                'comment_status' => $page->comment_status,
                'ping_status'    => $page->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => base64_encode($page->post_content),
                'post_excerpt'   => $page->post_excerpt,
                'post_name'      => $page->post_name,
                'post_parent'    => $page->post_parent,
                'post_password'  => $page->post_password,
                'post_status'    => 'draft',
                'post_title'     => $page->post_title,
                'post_type'      => $page->post_type,
                'to_ping'        => $page->to_ping,
                'menu_order'     => $page->menu_order
            );
        
        
        
        
    
                global $pdo;
                
                //check if post isnt already in databse idMapping table
                $stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$page_ids[$i]]);
                
                if($stmt->rowCount() == 0)
                {
                    $stmt = $pdo->prepare("INSERT INTO idMapping(".$currentLanguage."_id) VALUES (?)");
                    $stmt->execute([$page_ids[$i]]);
                }
    
                global $mainLanguage;

                //insert page to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$page_ids[$i]."&".http_build_query($args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_post&postid=".$page_ids[$i]."&".http_build_query($args)."&languageSubdomain=".$subdomains[$y][1]."&sourceLanguage=".$currentLanguage);
                    }
                }    
        }

echo "<p>Mapped all pages!</p>";	
}
//------------------------------------------------------------------











//-----------------------Hook to move all post to trash at once----------------
if($allowSyncTrash)
{
add_action( 'wp_trash_post', 'wp_remote_post_trash_post' );
add_action( 'wp_ajax_Ajax_trash_post', 'Ajax_trash_post' );
add_action( 'wp_ajax_nopriv_Ajax_trash_post', 'Ajax_trash_post' );
}

function wp_remote_post_trash_post($postid)
{
    global $currentLanguage;
    global $subdomains;
    global $yourSiteDomain;
    global $mainLanguage;
  
      //All subdomains for upload	
    for($i=0;$i<sizeof($subdomains);$i++)
    {
        if($subdomains[$i][0] == $mainLanguage)
        {
            wp_remote_post("http://www.".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_trash_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
        else
        {
            wp_remote_post("http://".$subdomains[$i][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_trash_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
    }
}




function Ajax_trash_post()
{
global $currentLanguage;
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["postid"]);
$stmt->execute();


$postIDs = $stmt->fetch();
$postIDtoTrash = $postIDs[$currentLanguage."_id"];


wp_trash_post($postIDtoTrash);
}


//-----------------------------------------------------------------------------------











//-----------------------Hook to restore all post from trash at once----------------
if($allowSyncUntrash)
{
add_action( 'untrash_post', 'wp_remote_post_untrash_post' );
add_action( 'wp_ajax_Ajax_untrash_post', 'Ajax_untrash_post' );
add_action( 'wp_ajax_nopriv_Ajax_untrash_post', 'Ajax_untrash_post' );
}

function wp_remote_post_untrash_post($postid)
{
    global $currentLanguage;
    global $subdomains;
    global $yourSiteDomain;
    global $mainLanguage;

      //All subdomains for upload	
    for($i=0;$i<sizeof($subdomains);$i++)
    {
        if($subdomains[$i][0] == $mainLanguage)
        {
            wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_untrash_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
        else
        {
            wp_remote_post("http://".$subdomains[$i][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_untrash_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
    }
}





function Ajax_untrash_post()
{
global $currentLanguage;
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["postid"]);
$stmt->execute();


$postIDs = $stmt->fetch();
$postIDtoTrash = $postIDs[$currentLanguage."_id"];


wp_untrash_post($postIDtoTrash);
}


//-----------------------------------------------------------------------------------

















//-----------------------Hook to permanently delete all posts at once----------------
if($allowSyncDelete)
{
add_action( 'before_delete_post', 'wp_remote_post_delete_post' );
add_action( 'wp_ajax_Ajax_delete_post', 'Ajax_delete_post' );
add_action( 'wp_ajax_nopriv_Ajax_delete_post', 'Ajax_delete_post' );
}

function wp_remote_post_delete_post($postid)
{
    global $currentLanguage;
    global $subdomains;
    global $yourSiteDomain;
    global $mainLanguage;

      //All subdomains for upload	
    for($i=0;$i<sizeof($subdomains);$i++)
    {
        if($subdomains[$i][0] == $mainLanguage)
        {
            wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_delete_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
        else
        {
            wp_remote_post("http://".$subdomains[$i][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_delete_post&postid=".$postid."&sourceLanguage=".$currentLanguage);
        }
    }
}





function Ajax_delete_post()
{
global $currentLanguage;
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["postid"]);
$stmt->execute();


$postIDs = $stmt->fetch();
$postIDtoTrash = $postIDs[$currentLanguage."_id"];


wp_delete_post($postIDtoTrash);


$stmt = $pdo->prepare("DELETE FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["postid"]);
$stmt->execute();
}


//-----------------------------------------------------------------------------------
























//-----------------------Map all normal tags------------------------------
add_action( 'wp_ajax_map_tags', 'map_tags' );
add_action( 'wp_ajax_nopriv_map_tags', 'map_tags' );


function map_tags()
{
global $currentLanguage;
global $subdomains;
global $pdo;
global $yourSiteDomain;

$args = array('hide_empty' => FALSE);
$tags = get_tags($args);


        for($i=0;$i<sizeof($tags);$i++)
        {
                //check if taxonomy isnt already in databse idCategoMapping table
                $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$tags[$i]->term_id]);
                
                if($stmt->rowCount() == 0)
                {
                    $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url) VALUES (?,?)");
                    $stmt->execute([$tags[$i]->term_id, $tags[$i]->slug]);
                }
    

                global $mainLanguage;
                
                //insert taxonomy to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$tags[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$tags[$i]->name."&TaxSlug=".$tags[$i]->slug."&TaxDesc=".$tags[$i]->description."&isTag=true");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$tags[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$tags[$i]->name."&TaxSlug=".$tags[$i]->slug."&TaxDesc=".$tags[$i]->description."&isTag=true");
                    }   
                }
        }
        
        echo "<p>Mapped all Tags!</p>";
        map_product_tags();
}







//-----------------------Map all product tags------------------------------
add_action( 'wp_ajax_map_product_tags', 'map_product_tags' );
add_action( 'wp_ajax_nopriv_map_product_tags', 'map_product_tags' );


function map_product_tags()
{		
global $currentLanguage;
global $subdomains;
global $pdo;
global $yourSiteDomain;

$args = array('taxonomy' => 'product_tag','hide_empty' => FALSE);  
$tags = get_terms( $args );


        for($i=0;$i<sizeof($tags);$i++)
        {
            
            
            
            
            
                //check if taxonomy isnt already in databse taxonomyMapping table
                $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$tags[$i]->term_id]);
                
                if($stmt->rowCount() == 0)
                {
                    $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url) VALUES (?,?)");
                    $stmt->execute([$tags[$i]->term_id, $tags[$i]->slug]);
                }

                global $mainLanguage;
    
                
                //insert taxonomy to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://www.".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$tags[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$tags[$i]->name."&TaxSlug=".$tags[$i]->slug."&TaxDesc=".$tags[$i]->description."&isTag=true&WooTag=true");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$tags[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$tags[$i]->name."&TaxSlug=".$tags[$i]->slug."&TaxDesc=".$tags[$i]->description."&isTag=true&WooTag=true");
                    }
                }
        }

echo "<p>Mapped all product tags!</p>";
}











//-----------------------Map all normal categories------------------------------
add_action( 'wp_ajax_map_categories', 'map_categories' );
add_action( 'wp_ajax_nopriv_map_categories', 'map_categories' );


function map_categories()
{		
global $currentLanguage;
global $subdomains;
global $pdo;
global $yourSiteDomain;


        $args = array('hide_empty' => FALSE);
        $categories = get_categories($args);
        

        for($i=0;$i<sizeof($categories);$i++)
        {		
                //check if taxonomy isnt already in databse taxonomyMapping table
                $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$categories[$i]->term_id]);
                
                if($stmt->rowCount() == 0)
                {
                    $category = get_category($categories[$i]->term_id);
                    $categorySlug = $category->slug;
        
                    if((get_category($categories[$i]->term_id)->parent) != 0)
                    {
                        $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url,".$currentLanguage."_parent_id) VALUES (?,?,?)");
                        $stmt->execute([$categories[$i]->term_id, $categorySlug, get_category($categories[$i]->term_id)->parent]);
                    }
                    else
                    {
                        $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url) VALUES (?,?)");
                        $stmt->execute([$categories[$i]->term_id, $categorySlug]);
                    }
                
                }
    

                global $mainLanguage;
                
                //insert taxonomy to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".get_cat_name($categories[$i]->term_id)."&TaxSlug=".$categories[$i]->slug."&TaxDesc=".$categories[$i]->description);
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".get_cat_name($categories[$i]->term_id)."&TaxSlug=".$categories[$i]->slug."&TaxDesc=".$categories[$i]->description);
                    }
                }
        }






        for($i=0;$i<sizeof($categories);$i++)
        {
            //set Categories parents
            for($x = 0;$x<sizeof($subdomains); $x++)
            {
                if((get_category($categories[$i]->term_id)->parent) != 0)
                {		
                    if($subdomains[$x][0] == $mainLanguage)
                    {					
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_category_parent&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxParent=".get_category($categories[$i]->term_id)->parent);
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_category_parent&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxParent=".get_category($categories[$i]->term_id)->parent);
                    }

                }
            }			
        }
        
echo "<p>Mapped all categories!</p>";
map_woocommerce_categories();
}

























//-----------------------Map all woocommerce categories------------------------------
add_action( 'wp_ajax_map_woocommerce_categories', 'map_woocommerce_categories' );
add_action( 'wp_ajax_nopriv_map_woocommerce_categories', 'map_woocommerce_categories' );


function map_woocommerce_categories()
{
global $currentLanguage;
global $subdomains;
global $pdo;
global $mainLanguage;
global $yourSiteDomain;                    


        $args = array('taxonomy' => 'product_cat','hide_empty' => FALSE);  
        $categories = get_categories( $args );
 

        for($i=0;$i<sizeof($categories);$i++)
        {		
            
                //check if taxonomy isnt already in databse taxonomyMapping table
                $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$categories[$i]->term_id]);
                
                if($stmt->rowCount() == 0)
                {
                    $categorySlug = $categories[$i]->slug;
        
                    if($categories[$i]->parent != 0)
                    {
                        $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url,".$currentLanguage."_parent_id) VALUES (?,?,?)");
                        $stmt->execute([$categories[$i]->term_id, $categorySlug, $categories[$i]->parent]);
                    }
                    else
                    {
                        $stmt = $pdo->prepare("INSERT INTO taxonomyMapping(".$currentLanguage."_id,".$currentLanguage."_url) VALUES (?,?)");
                        $stmt->execute([$categories[$i]->term_id, $categorySlug]);
                    }
                
                }
    

                
                //insert taxonomy to all subdomains
                for($y = 0;$y<sizeof($subdomains); $y++)
                {
                    if($subdomains[$y][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$categories[$i]->name."&TaxSlug=".$categories[$i]->slug."&TaxDesc=".$categories[$i]->description."&woocommerce_category=true");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$y][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_create_taxonomy&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxName=".$categories[$i]->name."&TaxSlug=".$categories[$i]->slug."&TaxDesc=".$categories[$i]->description."&woocommerce_category=true");
                    }
                }
        }






        for($i=0;$i<sizeof($categories);$i++)
        {
            //set Categories parents
            for($x = 0;$x<sizeof($subdomains); $x++)
            {
                if($categories[$i]->parent != 0)
                {							
                    if($subdomains[$x][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_category_parent&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxParent=".$categories[$i]->parent."&woocommerce_category=true");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_category_parent&taxonomyId=".$categories[$i]->term_id."&sourceLanguage=".$currentLanguage."&TaxParent=".$categories[$i]->parent."&woocommerce_category=true");
                    }
                }
            }			
        }

echo "<p>Mapped all product categories!</p>";
}























//Hook to create menu where user can switch languages
//WARNGING! You will have to edit this function to fit your theme needs - change menu structure etc.. This is how I got url link to same page/post/taxonomy/woocommerce but in another language and on another domain!
add_action('wp_footer', 'hooker');
function hooker()
{ 
    global $currentLanguage;
    global $subdomains;
    global $pdo;
    global $mainLanguage;
    global $yourSiteDomain;


    $obj_type = get_queried_object();


    // If displaying category
    if(!empty( $obj_type->cat_ID))
    {
        $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
        $stmt->execute([$obj_type->cat_ID]);
        $remoteTaxID = $stmt->fetch();
        
        
        
        for($x = 0; $x < sizeof($subdomains); $x++)
        {	
            echo '
            <script>
                    var submenu = document.getElementsByClassName("menu_dropdown--jazyky");
                    var subelement = document.createElement("li");
                    subelement.setAttribute("class", "menu_dropdown_item");
                    submenu[0].appendChild(subelement);
                    
                    var subelementHref = document.createElement("a");
                    var textToMakeUpperCase = "'.$subdomains[$x][1].'";
                    var uppercaseNode = textToMakeUpperCase.toUpperCase();
                    var textNode = document.createTextNode(uppercaseNode);
                    subelementHref.appendChild(textNode);
                    subelementHref.setAttribute("href", "'.get_category_url($obj_type->cat_ID,$x).'");
                    subelement.appendChild(subelementHref);
            </script>';
        }
        return;
    }


    // If displaying post, page or product
    if(!empty( $obj_type->ID))
    {
        $stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$currentLanguage."_id LIKE ?");
        $stmt->execute([$obj_type->ID]);
        $remotePostID = $stmt->fetch();
            
        for($x = 0; $x < sizeof($subdomains); $x++)
        {	
            echo '
            <script>
                    var submenu = document.getElementsByClassName("menu_dropdown--jazyky");
                    var subelement = document.createElement("li");
                    subelement.setAttribute("class", "menu_dropdown_item");
                    submenu[0].appendChild(subelement);
                    
                    var subelementHref = document.createElement("a");
                    var textToMakeUpperCase = "'.$subdomains[$x][1].'";
                    var uppercaseNode = textToMakeUpperCase.toUpperCase();
                    var textNode = document.createTextNode(uppercaseNode);
                    subelementHref.appendChild(textNode);

                    subelementHref.setAttribute("href", "http://'.(($subdomains[$x][0]==$mainLanguage)?"":($subdomains[$x][0].".")).$yourSiteDomain.'?p='.$remotePostID[$subdomains[$x][1]."_id"].'");
                    subelement.appendChild(subelementHref);
            </script>';
        }
        return;
    }

        
        
        

    if(isset($obj_type->taxonomy))
    {
        if($obj_type->taxonomy=="product_tag")
        {	
            $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
            $stmt->execute([$obj_type->term_id]);
            $remoteTaxID = $stmt->fetch();
            
            for($x = 0; $x < sizeof($subdomains); $x++)
            {	
                echo '
                <script>
                        var submenu = document.getElementsByClassName("menu_dropdown--jazyky");
                        var subelement = document.createElement("li");
                        subelement.setAttribute("class", "menu_dropdown_item");
                        submenu[0].appendChild(subelement);
                        
                        var subelementHref = document.createElement("a");
                        var textToMakeUpperCase = "'.$subdomains[$x][1].'";
                        var uppercaseNode = textToMakeUpperCase.toUpperCase();
                        var textNode = document.createTextNode(uppercaseNode);
                        subelementHref.appendChild(textNode);
                        subelementHref.setAttribute("href", "http://'.(($subdomains[$x][0]==$mainLanguage)?"":($subdomains[$x][0].".")).$yourSiteDomain.'/product-tag/'.$remoteTaxID[$subdomains[$x][1]."_url"].'");
                        subelement.appendChild(subelementHref);
                </script>';
            }
            return;
        }
        
        if($obj_type->taxonomy=="product_cat")
        {
            $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
            $stmt->execute([$obj_type->term_id]);
            $remoteTaxID = $stmt->fetch();
            
            
            
            for($x = 0; $x < sizeof($subdomains); $x++)
            {	
                echo '
                <script>
                        var submenu = document.getElementsByClassName("menu_dropdown--jazyky");
                        var subelement = document.createElement("li");
                        subelement.setAttribute("class", "menu_dropdown_item");
                        submenu[0].appendChild(subelement);
                        
                        var subelementHref = document.createElement("a");
                        var textToMakeUpperCase = "'.$subdomains[$x][1].'";
                        var uppercaseNode = textToMakeUpperCase.toUpperCase();
                        var textNode = document.createTextNode(uppercaseNode);
                        subelementHref.appendChild(textNode);
                        subelementHref.setAttribute("href", "http://'.(($subdomains[$x][0]==$mainLanguage)?"":($subdomains[$x][0].".")).$yourSiteDomain.'/product-category/'.$remoteTaxID[$subdomains[$x][1]."_url"].'");
                        subelement.appendChild(subelementHref);
                </script>';
            }
            return;
        }
    }


    //if got here user is on homepage
    for($x = 0; $x < sizeof($subdomains); $x++)
    {	
        echo '
        <script>
            var submenu = document.getElementsByClassName("menu_dropdown--jazyky");
            var subelement = document.createElement("li");
            subelement.setAttribute("class", "menu_dropdown_item");
            submenu[0].appendChild(subelement);
                        
            var subelementHref = document.createElement("a");
            var textToMakeUpperCase = "'.$subdomains[$x][1].'";
            var uppercaseNode = textToMakeUpperCase.toUpperCase();
            var textNode = document.createTextNode(uppercaseNode);
            subelementHref.appendChild(textNode);
            subelementHref.setAttribute("href", "http://'.(($subdomains[$x][0]==$mainLanguage)?"":($subdomains[$x][0].".")).$yourSiteDomain.'");
            subelement.appendChild(subelementHref);
        </script>';
    }
}










function get_category_url($cat_id,$x)
{
global $currentLanguage;
global $subdomains;
global $pdo;
global $mainLanguage;
global $yourSiteDomain;    

    if(get_category($cat_id) != null)
    {
        if((get_category($cat_id)->parent) != 0)
        {
                
                
            $temporaryCatID = get_category($cat_id)->parent;
            $parentCatUrls = array();
            
            while(1)
            {
                $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                $stmt->execute([$temporaryCatID]);
                $categorySlug = $stmt->fetch();
                
                
                if(get_category($temporaryCatID)->parent != 0)
                {
                    array_push($parentCatUrls,$categorySlug[$subdomains[$x][1]."_url"]);
                    $temporaryCatID = get_category($temporaryCatID)->parent;
                }
                else
                {
                    $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
                    $stmt->execute([$temporaryCatID]);
                    $categorySlug = $stmt->fetch();
                
                    array_push($parentCatUrls,$categorySlug[$subdomains[$x][1]."_url"]);
                    
                    break;
                }
            }
            
            
            $reversedArray = array_reverse($parentCatUrls,false);

            
            if($subdomains[$x][0] != $mainLanguage)
            {
                $finalURL = "https://".$subdomains[$x][0].".".$yourSiteDomain."/category/";
            }
            else
            {
                $finalURL = "https://".$yourSiteDomain."/category/";
            }



            for($y=0;$y<sizeof($reversedArray);$y++)
            {
                if(!empty($reversedArray[$y]))
                {
                    $finalURL .= $reversedArray[$y]."/";
                }
            }

            $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
            $stmt->execute([$cat_id]);
            $categorySlug = $stmt->fetch();
            
            $finalURL .= $categorySlug[$subdomains[$x][1]."_url"];
            
            
        }
        else
        {
            $stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ?");
            $stmt->execute([$cat_id]);
            $categorySlug = $stmt->fetch();
            

            if($subdomains[$x][0] != $mainLanguage)
            {
                $finalURL = "https://".$subdomains[$x][0].".".$yourSiteDomain."/category/".$categorySlug[$subdomains[$x][1]."_url"];
            }
            else
            {
                $finalURL = "https://".$yourSiteDomain."/category/".$categorySlug[$subdomains[$x][1]."_url"];
            }
        }
        
        return $finalURL;
    }
}






//-----------------------Map on Taxonomy Create AJAX------------------------------
add_action( 'wp_ajax_Ajax_create_taxonomy', 'Ajax_create_taxonomy' );
add_action( 'wp_ajax_nopriv_Ajax_create_taxonomy', 'Ajax_create_taxonomy' );




//Create new taxonomy
function Ajax_create_taxonomy()
{
global $pdo;
global $currentLanguage;

//check if taxonomy isnt already in databse taxonomyMapping table
$stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["taxonomyId"]]);
$checkIfAlreadyExist = $stmt->fetch();

if(is_null($checkIfAlreadyExist[$currentLanguage."_id"]))
{
    
    $newTaxonomyID;
    
    if(isset($_REQUEST["isTag"]))
    {
        
        if(isset($_REQUEST["WooTag"]))
        {
            wp_create_term( $_REQUEST["TaxName"], 'product_tag' );
            $newTaxonomyObj = get_term_by( 'name', $_REQUEST["TaxName"], 'product_tag');
            $newTaxonomyID = $newTaxonomyObj->term_id;
        
            $args = array('description' => $_REQUEST["TaxDesc"], 'slug' => $_REQUEST["TaxSlug"]);
            wp_update_term( $newTaxonomyID, 'product_tag', $args);

        }
        else
        {
            wp_create_term( $_REQUEST["TaxName"], 'post_tag' );
            $newTaxonomyObj = get_term_by( 'name', $_REQUEST["TaxName"], 'post_tag');
            $newTaxonomyID = $newTaxonomyObj->term_id;
        
            $args = array('description' => $_REQUEST["TaxDesc"], 'slug' => $_REQUEST["TaxSlug"]);
            wp_update_term( $newTaxonomyID, 'post_tag', $args);
        }
    }
    else
    {
        
        if(isset($_REQUEST["woocommerce_category"]))
        {
            $term = wp_insert_term( $_REQUEST["TaxName"], 'product_cat', ['description' => $_REQUEST["TaxDesc"], 'slug' => $_REQUEST["TaxSlug"] ]);
            $newTaxonomyID = $term['term_id'];
        }
        else
        {
            $term = wp_insert_term( $_REQUEST["TaxName"], 'category', ['description' => $_REQUEST["TaxDesc"], 'slug' => $_REQUEST["TaxSlug"] ]);
            $newTaxonomyID = $term['term_id'];
        }
    }
    
    $stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_id = ".$newTaxonomyID." WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["taxonomyId"]);
    $stmt->execute();

    $stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_url = '".$_REQUEST["TaxSlug"]."' WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ".$_REQUEST["taxonomyId"]);
    $stmt->execute();
}
else
{
    //This taxonomy already exist
}




die();
}












//Hook to update database slug data on update
add_action( 'edited_category', 'update_DB_data_category', 10, 2); 

function update_DB_data_category( $term_id, $taxonomy ) {


global $pdo;
global $currentLanguage;

$editedTax = get_category($term_id);
    
$stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_url = '".$editedTax->slug."' WHERE ".$currentLanguage."_id LIKE ".$term_id);
$stmt->execute();

}





//Hook to update database category data on update
add_action( 'edited_product_cat', 'update_DB_data_procuct_cat', 10, 2); 

function update_DB_data_procuct_cat( $term_id, $taxonomy ) {


global $pdo;
global $currentLanguage;

$editedTax = get_term_by('id', $term_id, 'product_cat');			
    
$stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_url = '".$editedTax->slug."' WHERE ".$currentLanguage."_id LIKE ".$term_id);
$stmt->execute();

}








//Hook to update database slug data on update
add_action( 'edited_post_tag', 'update_DB_data_tag', 10, 2); 

function update_DB_data_tag( $term_id, $taxonomy ) {


global $pdo;
global $currentLanguage;

$editedTax = get_term_by('id', $term_id, 'post_tag');			
    
$stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_url = '".$editedTax->slug."' WHERE ".$currentLanguage."_id LIKE ".$term_id);
$stmt->execute();

}









//Hook to update database slug data on update
add_action( 'edited_product_tag', 'update_DB_data_product_tag', 10, 2); 

function update_DB_data_product_tag( $term_id, $taxonomy ) {


global $pdo;
global $currentLanguage;

$editedTax = get_term_by('id', $term_id, 'product_tag');			
    
$stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_url = '".$editedTax->slug."' WHERE ".$currentLanguage."_id LIKE ".$term_id);
$stmt->execute();

}





















//-----------------------Set category parent AJAX------------------------------
add_action( 'wp_ajax_Ajax_set_category_parent', 'Ajax_set_category_parent' );
add_action( 'wp_ajax_nopriv_Ajax_set_category_parent', 'Ajax_set_category_parent' );



function Ajax_set_category_parent()
{		
global $pdo;
global $currentLanguage;

$stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["TaxParent"]]);
$parentId = $stmt->fetch();

$IDparent = $parentId[$currentLanguage."_id"];					
                    
                    
$stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["taxonomyId"]]);
$currentID = $stmt->fetch();
                    
                    
$args = array('parent' => $IDparent);

if(isset($_REQUEST["woocommerce_category"]))
{
    wp_update_term( $currentID[$currentLanguage."_id"], 'product_cat', $args);
}
else
{
    wp_update_term( $currentID[$currentLanguage."_id"], 'category', $args);
}

$stmt = $pdo->prepare("UPDATE taxonomyMapping SET ".$currentLanguage."_parent_id = ".$IDparent." WHERE ".$currentLanguage."_id LIKE ?");
$stmt->execute([$currentID[$currentLanguage."_id"]]);

die();
}







//-----------------------Hook for synchronozed taxonomy delete------------------------------
if($allowSyncTaxonomyDelete)
{
add_action( 'delete_term_taxonomy', 'sync_tax_delete' );
}

function sync_tax_delete($term_id)
{
global $pdo;
global $subdomains;
global $currentLanguage;
global $mainLanguage;
global $yourSiteDomain;

for($x = 0;$x<sizeof($subdomains); $x++)
{
    if($subdomains[$x][0] == $mainLanguage)
    {
        wp_remote_post("http://www.".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_sync_delete_tax&catIDtoDelete=".$term_id."&sourceLanguage=".$currentLanguage);
    }
    else
    {
        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_sync_delete_tax&catIDtoDelete=".$term_id."&sourceLanguage=".$currentLanguage);
    }
}

$stmt = $pdo->prepare("DELETE FROM taxonomyMapping WHERE ".$currentLanguage."_id LIKE ".$term_id);
$stmt->execute();
}



add_action( 'wp_ajax_Ajax_sync_delete_tax', 'Ajax_sync_delete_tax' );
add_action( 'wp_ajax_nopriv_Ajax_sync_delete_tax', 'Ajax_sync_delete_tax' );



//Ajax delete category
function Ajax_sync_delete_tax()
{
global $currentLanguage;
global $pdo;


$stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["catIDtoDelete"]]);
$currentID = $stmt->fetch();


$TaxType = get_term($currentID[$currentLanguage."_id"]);


if($TaxType->taxonomy == 'category')
{
    wp_delete_term( $currentID[$currentLanguage."_id"], 'category' );
}
else
{
    wp_delete_term( $currentID[$currentLanguage."_id"], 'post_tag' );
}

die();
}









//Hook to set categories and tags to posts..
add_action( 'wp_ajax_Send_Relationship_sync_request', 'Send_Relationship_sync_request' );
add_action( 'wp_ajax_nopriv_Send_Relationship_sync_request', 'Send_Relationship_sync_request' );

function Send_Relationship_sync_request()
{
global $pdo;
global $subdomains;
global $currentLanguage;
global $mainLanguage;
global $yourSiteDomain;


        //Get all post IDs
        $postIds = get_posts(array(
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1		//Get all posts
        ));
        

        //loop through all posts
        for($i=0;$i<sizeof($postIds);$i++)
        {
            $post_id = $postIds[$i];
            
            
            //get categories from post				
            $post_categories = wp_get_post_categories( $post_id );
            foreach($post_categories as $c){
                $cat = get_category( $c );
                for($x = 0;$x<sizeof($subdomains); $x++)
                {
                    if($subdomains[$x][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$cat->cat_ID."&sourceLanguage=".$currentLanguage."&type=category");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$cat->cat_ID."&sourceLanguage=".$currentLanguage."&type=category");
                    }
                }
            }
            
            
            
        
        
            //get tags from post
            $post_tags = get_the_terms( $post_id, "post_tag" );
            for($j=0;$j < sizeof($post_tags);$j++)
            {
                for($x = 0;$x<sizeof($subdomains); $x++)
                {
                    if($subdomains[$x][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$post_tags[$j]->term_id."&sourceLanguage=".$currentLanguage."&type=tag");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$post_tags[$j]->term_id."&sourceLanguage=".$currentLanguage."&type=tag");
                    }
                }
            }
            
        }
        Send_WooCommerceRelationship_sync_request();
        echo "<p>Mapped all non-product(posts,pages) relationships (tags, categories)!</p>";
}







//Hook to set categories and tags to all products
add_action( 'wp_ajax_Send_WooCommerceRelationship_sync_request', 'Send_WooCommerceRelationship_sync_request' );
add_action( 'wp_ajax_nopriv_SendWooCommerceRelationship_sync_request', 'Send_WooCommerceRelationship_sync_request' );

function Send_WooCommerceRelationship_sync_request()
{
global $pdo;
global $subdomains;
global $currentLanguage;
global $mainLanguage;
global $yourSiteDomain;


        //Get all products IDs
        $postIds = get_posts(array(
            'post_type' 	  => 'product',
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1		//Get all posts
        ));
        
        

        //loop through all products
        for($i=0;$i<sizeof($postIds);$i++)
        {
            $post_id = $postIds[$i];				
            
            
            //get categories from product
            $post_categories = get_the_terms( $post_id, 'product_cat' );
            foreach($post_categories as $c){
                for($x = 0;$x<sizeof($subdomains); $x++)
                {
                    if($subdomains[$x][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$c->term_id."&sourceLanguage=".$currentLanguage."&type=WooCategory");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$c->term_id."&sourceLanguage=".$currentLanguage."&type=WooCategory");
                    }
                }
            }
            
            
            
            
            //get tags from products
            $post_tags = get_the_terms( $post_id, "product_tag" );
            
            
            
            
            for($j=0;$j < sizeof($post_tags);$j++)
            {
                for($x = 0;$x<sizeof($subdomains); $x++)
                {
                    if($subdomains[$x][0] == $mainLanguage)
                    {
                        wp_remote_post("http://".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$post_tags[$j]->term_id."&sourceLanguage=".$currentLanguage."&type=WooTag");
                    }
                    else
                    {
                        wp_remote_post("http://".$subdomains[$x][0].".".$yourSiteDomain."/wp-admin/admin-ajax.php?action=Ajax_set_Relationship_sync&postID=".$post_id."&relationshipID=".$post_tags[$j]->term_id."&sourceLanguage=".$currentLanguage."&type=WooTag");
                    }
                }
            }
            
        }
        
echo "<p>Mapped all WooCommerce relationships (product tags, product categories)!</p>";
}








add_action( 'wp_ajax_Ajax_set_Relationship_sync', 'Ajax_set_Relationship_sync' );
add_action( 'wp_ajax_nopriv_Ajax_set_Relationship_sync', 'Ajax_set_Relationship_sync' );


function Ajax_set_Relationship_sync()
{
global $currentLanguage;
global $pdo;

$stmt = $pdo->prepare("SELECT * FROM taxonomyMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["relationshipID"]]);
$currentCategoryID = $stmt->fetch();



$stmt = $pdo->prepare("SELECT * FROM idMapping WHERE ".$_REQUEST["sourceLanguage"]."_id LIKE ?");
$stmt->execute([$_REQUEST["postID"]]);
$currentPostID = $stmt->fetch();

    

if($_REQUEST["type"] == "category")
{
    if(isset($currentCategoryID[$currentLanguage."_id"]))
    {
        wp_set_post_categories($currentPostID[$currentLanguage."_id"], $currentCategoryID[$currentLanguage."_id"],true);
    }
}
else if($_REQUEST["type"] == "tag")
{
    if(isset($currentCategoryID[$currentLanguage."_id"]))
    {
        $tag = get_tag($currentCategoryID[$currentLanguage."_id"]);
        wp_set_post_tags($currentPostID[$currentLanguage."_id"], $tag->name, true );
    }
}
else if($_REQUEST["type"] == "WooCategory")
{
    if(isset($currentCategoryID[$currentLanguage."_id"]))
    {
        $CategoryToAssign = get_term($currentCategoryID[$currentLanguage."_id"],"product_cat");
        wp_set_object_terms($currentPostID[$currentLanguage."_id"], $CategoryToAssign->name, 'product_cat', true);
    }
}
else if($_REQUEST["type"] == "WooTag")
{
    if(isset($currentCategoryID[$currentLanguage."_id"]))
    {
        $tagToAssign = get_term($currentCategoryID[$currentLanguage."_id"],"product_tag");
        wp_set_object_terms($currentPostID[$currentLanguage."_id"], $tagToAssign->name, 'product_tag', true);
    }
}

die();
}

//------------------------------------------END OF MAPPING--------------------------------------------------------


/*
@by Ojin
Website:    https://www.ojin.cz
Email:      robin.chmelik@seznam.cz
*/

?>