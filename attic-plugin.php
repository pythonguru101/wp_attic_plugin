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

if (file_exists(dirname(__FILE__). "/post/target.csv"))
	add_action('admin_menu', 'test_plugin_setup_menu');
else
	add_action('admin_menu', 'csv_plugin_setup_menu');

function csv_plugin_setup_menu(){
    add_menu_page( 'Attic Plugin Page', 'Attic Plugin', 'manage_options', 'test-plugin', 'csv_init' );
}

function csv_init(){
    csv_handle_post();
?>
    <h2>Please choose target csv file to upload.</h2>
    <form id="target_form1" method="post" enctype="multipart/form-data">
        <input name="upload[]" type="file" multiple="multiple" />
        <?php submit_button('Upload') ?>
    </form>
    <h2>Or you can input target keyword and URL.</h2>
    <form id="target_form2" method="post" enctype="multipart/form-data">
        Keyword:<input name="target_keyword" type="text" />
        URL:<input name="target_url" type="text" />
        <?php submit_button('Submit') ?>
    </form>
<?php
}

function csv_handle_post(){
	$total = 0;
	$current_dirname = dirname(__FILE__);

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
    add_menu_page( 'Attic Plugin Page', 'Attic Plugin', 'manage_options', 'test-plugin', 'test_init' );
}
 
function test_init(){
    test_handle_post();
?>
    <h2>Please choose article files to upload.</h2>
    <form  id="article_upload_form" method="post" enctype="multipart/form-data">
        <input name="upload[]" type="file" multiple="multiple" />
        <?php submit_button('Upload') ?>
    </form>
    <h2>You can change target keyword here.</h2>
    <form id="target_change_form" method="post" enctype="multipart/form-data">
        Keyword: <input name="keyword_to_change" type="text" />
        <?php submit_button('Submit') ?>
    </form>
<?php
}
 
function test_handle_post(){
	$current_dirname = dirname(__FILE__);
	$target_url = "";
	$d2 = new Datetime("now");
	$dt = $d2->format('U');
	mkdir($current_dirname. "/post/$dt");

	if (isset($_POST["keyword_to_change"]))
	{
		$fp1 = fopen(dirname(__FILE__)."/post/target.csv", "r");
		while( false !== ( $data = fgetcsv($fp1) ) ){ 
			$target_url = $data[1];
		}
		fclose($fp1);

		$val = array($_POST["keyword_to_change"], $target_url);
		$fp2 = fopen(dirname(__FILE__)."/post/target.csv", "wb");
		fputcsv($fp2, $val);
		fclose($fp2);

		// UPDATE `wp_posts`
  // 			SET `post_content` =
  // 			REGEXP_REPLACE( post_content, '<pre class="brush: php;">', '<pre>' );
	}


	if ($_FILES['upload']['name']) {
		// Count # of uploaded files in array
		$total = count($_FILES['upload']['name']);
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
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], null,  $_FILES['upload']['name'][$i+1]);
	    	if ($i == $total - 1)
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $_FILES['upload']['name'][$i-1], null);
	    	if ($i > 0 && $i < $total - 1)
	    		add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $_FILES['upload']['name'][$i-1], $_FILES['upload']['name'][$i+1]);
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
		$file_content = str_replace($line[0], "<a href='$line[1]'>$line[0]</a>", $file_content);
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