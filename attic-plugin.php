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

add_action('admin_menu', 'plugin_setup_menu');


function plugin_setup_menu(){
    add_menu_page( 'Attic-Plugin', 'Attic-Plugin', 'manage_options', 'test-plugin', 'plugin_init' );
}

function plugin_init(){
    handle_post();
?>
	<div class="notice notice-info">
		<h3 style="color:#0e637d;">Please input target keyword and URL before uploading articles.</h3>
	    <form id="form1" method="post" enctype="multipart/form-data">
	    	<p>Target Keyword:</p>
	        <input name="target_keyword" type="text" style="width:50%;"/>
	        <p>Target URL:</p>
	        <input name="target_url" type="text" style="width:50%;"/>
	        <p>Add to the title:</p>
	        <input name="add_to_title" type="text" style="width:50%;"/>
	        <p>Target keyword to change:</p>
	        <input name="keyword_to_change" type="text" style="width:50%;"/><br><br>
	        <input type="checkbox" id="check_all" name="check_all">
  			<label for="vehicle1">Replace All Links</label>
	        <p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Submit" style="width: 10%;"></p>
	    </form>
	</div>
	<div class="notice notice-success">
	    <h3 style="color:#106118;">Choose article files or target CSV file to upload.</h3>
	    <form id="form2" method="post" enctype="multipart/form-data">
	        <input name="upload[]" type="file" multiple="multiple" />
	        <p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Upload" style="width: 10%; color:#125a18; border-color: #04580b;"></p>
	    </form>
	</div>
<?php
}

function handle_post(){
	$total = 0;
	$current_dirname = dirname(__FILE__);
	$path = '';
	$original_target_keyword = '';
	$new_target_keyword = '';
	$target_url = '';
	$val = '';

	// if (isset($_POST["keyword_to_change"]))
	// {
	// 	$new_target_keyword = $_POST["keyword_to_change"];
	// 	$fp1 = fopen(dirname(__FILE__)."/post/target.csv", "r");
	// 	while( false !== ( $data = fgetcsv($fp1) ) ) { 
	// 		$original_target_keyword = $data[0];
	// 		$target_url = $data[1];
	// 	}
	// 	fclose($fp1);

	// 	$val = array($_POST["keyword_to_change"], $target_url);
	// 	$fp2 = fopen(dirname(__FILE__)."/post/target.csv", "wb");
	// 	fputcsv($fp2, $val);
	// 	fclose($fp2);

	// 	$args = array(
	// 		'post_type' => array('post'/*,'page'*/),
	// 		'post_status' => 'publish',
	// 		'posts_per_page' => -1,
	// 		'ignore_sticky_posts' => true,
	// 	);
	// 	$qry = new WP_Query($args);
		
	// 	foreach ($qry->posts as $p) { 
	// 	    $id = $p->ID;
	// 	    $content = strip_tags($p->post_content);
	// 	    $content = preg_replace("/$original_target_keyword/", $_POST["keyword_to_change"], $content));

	// 	    if (isset($_POST['check_all']))
	// 	    	$content = preg_replace("/$new_target_keyword/", "<a href='$target_url'>$new_target_keyword</a>", $content);
	// 	    else
	// 	    	$content = preg_replace("/$new_target_keyword/", "<a href='$target_url'>$new_target_keyword</a>", $content, 1);

	// 	    // wp_delete_post($p->ID);
	// 	    $updated_post = array(
	// 			'ID'    => "$id",
	// 			'post_content'  => "$content",
	// 			'post_type'     => 'post',
	// 			'post_status' 	=> 'publish',
	// 	    );

	// 	    wp_update_post( $updated_post );
	// 	}
	// }

	if ($_FILES['upload']['name']) {
		// Count # of uploaded files in array
		$total = count($_FILES['upload']['name']);
		shuffle($_FILES['upload']['name']);
		$d2 = new Datetime("now");
		$dt = $d2->format('U');
		$path = $current_dirname. "/post/$dt/";
		if (!file_exists($path)) {
		    mkdir($path, 0777, true);
		}
	} 

	for( $i=0 ; $i < 5/*$total*/; $i++ ) {
		$tmpFilePath = $_FILES['upload']['tmp_name'][$i];
		if ($tmpFilePath != "") {
			if(strpos($_FILES['upload']['tmp_name'][$i], ".csv") !== false) {
				$newFilePath = $current_dirname. "/post/" . $_FILES['upload']['name'][$i];
				move_uploaded_file($tmpFilePath, $newFilePath);
			}
			else {
				$newFilePath = $path. $_FILES['upload']['name'][$i];
				if (move_uploaded_file($tmpFilePath, $newFilePath)) {
					if ($i == 0)
					{
						$next = str_replace(".txt", "", $_FILES['upload']['name'][$i+1]);
						if (isset($_POST['check_all']))
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], null,  $next, 1);
						else
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], null,  $next, 0);
					}
					if ($i == $total - 1)
					{
						$prev = str_replace(".txt", "", $_FILES['upload']['name'][$i-1]);
						if (isset($_POST['check_all']))
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, null, 1);
						else
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, null, 0);
					}
					if ($i > 0 && $i < $total - 1)
					{
						$prev = str_replace(".txt", "", $_FILES['upload']['name'][$i-1]);
						$next = str_replace(".txt", "", $_FILES['upload']['name'][$i+1]);
						if (isset($_POST['check_all']))
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, $next, 1);
						else
							add_custom_post($newFilePath, $_FILES['upload']['name'][$i], $prev, $next, 0);
					}
				}
			}
		}
	}
	
	// update target.csv file
	if (isset($_POST['target_keyword']) && isset($_POST['target_url']) && isset($_POST["keyword_to_change"]) && isset($_POST["add_to_title"]))
	{
		if (isset($_POST['check_all']))
			$val = array($_POST["target_keyword"], $_POST["target_url"], $_POST["add_to_title"], $_POST["keyword_to_change"], 1);
		else
			$val = array($_POST["target_keyword"], $_POST["target_url"], $_POST["add_to_title"], $_POST["keyword_to_change"], 0);

		$fp = fopen($current_dirname."/post/target.csv", "wb");
		fputcsv($fp, $val);
		fclose($fp);
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

function add_custom_post($file_path, $file_name, $prev, $next, $check_all_flag) {
	$spintax = new Spintax();
    $myfile = fopen($file_path, "r") or die("Unable to open file!");
	$file_content = fread($myfile,filesize($file_path));
	fclose($myfile);
	
	$line = '';

    // Create post object
    if ($next == null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$prev</a></div>";
    if ($prev == null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$next</a></div>";
    if ($next != null && $prev != null)
    	$file_content = $file_content. "<div><a href='http://localhost/wordpressTest'>$prev</a></div><div><a href='http://localhost/wordpressTest'>$next</a></div>";

    $csv = fopen(dirname(__FILE__). "/post/target.csv", 'r');
	while (($line = fgetcsv($csv)) !== FALSE) {
		$file_content = preg_replace("/$line[0]/", "$line[3]", $file_content);
		if ($line[4] == 1)
			$file_content = preg_replace("/$line[3]/", "<a href='$line[1]'>$line[3]</a>", $file_content);
		else
			$file_content = preg_replace("/$line[3]/", "<a href='$line[1]'>$line[3]</a>", $file_content, 1);
		break;
		
		/* EXAMPLE USAGE */
		$file_content = $spintax->process($file_content);
	}

	$file_name = str_replace(".txt", "", $file_name);
    $my_post = array(
		'post_title'    => $file_name." ".$line[2],
		'post_content'  => "$file_content",
		'post_type'     => 'post',
		'post_status' 	=> 'publish',
    );

    // Insert the post into the database
    wp_insert_post( $my_post );
    fclose($csv);
}