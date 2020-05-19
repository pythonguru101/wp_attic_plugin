<?php
/*
Plugin Name: Attic plugin
Description: This is Attic plugin
Author: Vasily Chigaev
Version: 0.1
*/
class Spintax
{
    public function process($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*?)\}/x',
            array($this, 'replace'),
            $text
        );
    }

    public function replace($text)
    {
        $text = $this->process($text[1]);
        $parts = explode('|', $text);
        return $parts[array_rand($parts)];
    }
}

//if (file_exists(dirname(__FILE__). "/post/target.csv"))
add_action('admin_menu', 'test_plugin_setup_menu');
//else
add_action('admin_menu', 'csv_plugin_setup_menu');

function csv_plugin_setup_menu(){
    add_menu_page( 'Attic-Target', 'Attic-1', 'manage_options', 'test-plugin1', 'csv_init' );
}

function csv_init(){
    csv_handle_post();
?>
	<div class="notice notice-info">
		<h3>Please input target keyword and URL before uploading articles.</h3>
	    <form id="target_form2" method="post" enctype="multipart/form-data">
	    	<p>Target Keyword:</p>
	        <input name="target_keyword" type="text" />
	        <p>Target URL:</p>
	        <input name="target_url" type="text" />
	        <?php submit_button('Submit') ?>
	    </form>
	</div>
	<div class="notice notice-success">
	    <h3>You can change target keyword here.</h3>
	    <form id="target_change_form" method="post" enctype="multipart/form-data">
	    	<p>Target keyword to change:</p>
	        <input name="keyword_to_change" type="text" /><br><br>
	        <input type="checkbox" id="check_all" name="check_all">
  			<label for="vehicle1">Replace all</label>
	        <?php submit_button('Submit') ?>
	    </form>
	</div>
	<div class="notice notice-warning">
	    <h3>Choose target csv file to upload.</h3>
	    <form id="target_form1" method="post" enctype="multipart/form-data">
	        <input name="upload[]" type="file" multiple="multiple" />
	        <?php submit_button('Upload') ?>
	    </form>
	</div>
<?php
}

function csv_handle_post(){
	$total = 0;
	$original_target_keyword = '';
	$new_target_keyword = '';
	$target_url = '';
	$current_dirname = dirname(__FILE__);

	if (isset($_POST["keyword_to_change"]))
	{
		$new_target_keyword = $_POST["keyword_to_change"];
		$fp1 = fopen(dirname(__FILE__)."/post/target.csv", "r");
		while( false !== ( $data = fgetcsv($fp1) ) ){ 
			$original_target_keyword = $data[0];
			$target_url = $data[1];
		}
		fclose($fp1);

		$val = array($_POST["keyword_to_change"], $target_url);
		$fp2 = fopen(dirname(__FILE__)."/post/target.csv", "wb");
		fputcsv($fp2, $val);
		fclose($fp2);

		$args = array(
			'post_type' => array('post'/*,'page'*/),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'ignore_sticky_posts' => true,
		);
		$qry = new WP_Query($args);
		
		// remove published posts
		foreach ($qry->posts as $p) { 
		    $id = $p->ID;
		    $content = strip_tags($p->post_content);
		    $content = preg_replace("/$original_target_keyword/", $_POST["keyword_to_change"], $content));

		    if (isset($_POST['check_all']))
		    	$content = preg_replace("/$new_target_keyword/", "<a href='$target_url'>$new_target_keyword</a>", $content);
		    else
		    	$content = preg_replace("/$new_target_keyword/", "<a href='$target_url'>$new_target_keyword</a>", $content, 1);

		    // wp_delete_post($p->ID);
		    $updated_post = array(
				'ID'    => "$id",
				'post_content'  => "$content",
				'post_type'     => 'post',
				'post_status' 	=> 'publish',
		    );

		    wp_update_post( $updated_post );
		}
	}

	if ($_FILES['upload']['name']) {
		// Count # of uploaded files in array
		$total = count($_FILES['upload']['name']);
	} 
		
	// Loop through each file
	for( $i=0 ; $i < $total ; $i++ ) {

	  //Get the temp file path
	  $tmpFilePath = $_FILES['upload']['tmp_name'][$i];

	  //Make sure we have a file path
	  if ($tmpFilePath != ""){
	    //Setup our new file path
	    $newFilePath = $current_dirname. "/post/" . $_FILES['upload']['name'][$i];

	    //Upload the file into the temp dir
	    if(move_uploaded_file($tmpFilePath, $newFilePath)) {}
	  }
	}
	if (isset($_POST['target_keyword']) && isset($_POST['target_url']))
	{
		$val = array($_POST["target_keyword"], $_POST["target_url"]);
		$fp = fopen(dirname(__FILE__)."/post/target.csv", "wb");
		fputcsv($fp, $val);
		fclose($fp);
	}
}

function test_plugin_setup_menu(){
    add_menu_page( 'Attic-Article', 'Attic-2', 'manage_options', 'test-plugin2', 'test_init' );
}
 
function test_init(){
    test_handle_post();
?>
	<div class="notice notice-warning">
	    <h3>Please choose article files to upload.</h3>
	    <form  id="article_upload_form" method="post" enctype="multipart/form-data">
	        <input name="upload[]" type="file" multiple="multiple" />
	        <?php submit_button('Upload') ?>
	    </form>
	</div>
<?php
}
 
function test_handle_post(){
	$current_dirname = dirname(__FILE__);
	$target_url = "";
	$d2 = new Datetime("now");
	$dt = $d2->format('U');
	mkdir($current_dirname. "/post/$dt");


	if ($_FILES['upload']['name']) {
		// Count # of uploaded files in array
		$total = count($_FILES['upload']['name']);
		shuffle($_FILES['upload']['name']);
	} 

	// Loop through each file
	for( $i=0 ; $i < 5 ; $i++ ) {

	  //Get the temp file path
	  $tmpFilePath = $_FILES['upload']['tmp_name'][$i];

	  //Make sure we have a file path
	  if ($tmpFilePath != ""){

	    //Setup our new file path
	    $newFilePath = $current_dirname. "/post/$dt/". $_FILES['upload']['name'][$i];

	    //Upload the file into the temp dir
	    if(move_uploaded_file($tmpFilePath, $newFilePath)) {

	    	//Handle other code here
	    	if ($i == 0)
	    	{
	    		$next = str_replace(".txt", "", $_FILES['upload']['name'][$i+1]);
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], null,  $next);
	    	}
	    	if ($i == $total - 1)
	    	{
	    		$prev = str_replace(".txt", "", $_FILES['upload']['name'][$i-1]);
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, null);
	    	}
	    	if ($i > 0 && $i < $total - 1)
	    	{
	    		$prev = str_replace(".txt", "", $_FILES['upload']['name'][$i-1]);
	    		$next = str_replace(".txt", "", $_FILES['upload']['name'][$i+1]);
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, $next);
	    	}
	    }
	  }
	}
	
	for( $i=0 ; $i < $total ; $i++ ) {
	  $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
	  if ($tmpFilePath != ""){
	    $newFilePath = $current_dirname. "/post/$dt/". $_FILES['upload']['name'][$i];
	    move_uploaded_file($tmpFilePath, $newFilePath);
	  }
	}

}
 
function add_custom_page() {
	// Create post object
    $my_post = array(
      'post_title'    => wp_strip_all_tags( 'Article Page' ),
      'post_content'  => '***',
      'post_type'     => 'page',
    );

    // Insert the post into the database
    wp_insert_post( $my_post );
}

function add_custom_post($file_path, $file_name, $prev, $next) {
    $myfile = fopen($file_path, "r") or die("Unable to open file!");
	$file_content = fread($myfile,filesize($file_path));
	fclose($myfile);
	$spintax = new Spintax();
    // Create post object
    if ($next == null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$prev</a></div>";
    if ($prev == null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$next</a></div>";
    if ($next != null && $prev != null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$prev</a></div><div><a href='http://localhost/wordpressTest'>$next</a></div>";

    $csv = fopen(dirname(__FILE__). "/post/target.csv", 'r');
	while (($line = fgetcsv($csv)) !== FALSE) {

		$file_content = preg_replace("/$line[0]/", "<a href='$line[1]'>$line[0]</a>", $file_content, 1);
		break;
		/* EXAMPLE USAGE */
		$file_content = $spintax->process($file_content);
	}
	fclose($csv);

	$file_name = str_replace(".txt", "", $file_name);
    $my_post = array(
		'post_title'    => "$file_name",
		'post_content'  => "$file_content",
		'post_type'     => 'post',
		'post_status' 	=> 'publish',
    );

    // Insert the post into the database
    wp_insert_post( $my_post );
}