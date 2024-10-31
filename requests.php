<?php
/*
Plugin Name: Music Request Manager
Plugin URI: http://www.whereyoursolutionis.com
Description: Allows people to add their music requests online for DJs, skating rinks, clubs, and more. Use shortcode [music-requests] to generate user and admin pages, page will adjust automatically using custom permission manage_requests. 
Version: 1.3
Author URI: http://www.whereyoursolutionis.com/author/scriptonite/
*/


 
 register_activation_hook(__FILE__,'BuildRequestTables');
 add_shortcode('music-requests','song_requests');
 add_action('admin_menu','setup_music_request_options');

 
  
 function BuildRequestTables(){ 
global $wpdb;
global $wp_roles;
$wp_roles->add_cap( 'administrator', 'manage_requests' );
 
 
     if($wpdb->get_var("show tables like ".$wpdb->prefix . "current_requests") != $wpdb->prefix . "current_requests") {
    $sql1 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "current_requests(  
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	title varchar(500) NOT NULL,
	artist varchar(50) NOT NULL, 
    count int(50) NOT NULL,
    played varchar(50) NOT NULL
     );"; 

	}

    

	

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql1);

 add_option('music_request_credit','yes');
 add_option('music_request_countdown','30');
 
 } 
 
 
 
 
 
 
 
 
 
 
 
 function song_requests(){
 
 ob_start();
  wp_enqueue_script('jquery');
 global $wpdb;
 ?>
 <script>
 jQuery(document).ready(function() {
jQuery('#adminnote').delay(8000).fadeOut('slow')
});
 
 </script>
 
 <?php
 
 if(isset($_POST['remove'])){
 
  
$curSong = $wpdb->query("DELETE FROM ".$wpdb->prefix . "current_requests WHERE id='".$_POST['remove']."'");
 
 } 
 if(isset($_POST['clear'])){
 
  
$wpdb->query("DELETE FROM ".$wpdb->prefix . "current_requests");
  		echo '<span  id="adminnote" style="color:red;font-size:14px;"> Request list deleted.</span>';
 }
 
 if(isset($_POST['played'])){
 
 	date_default_timezone_set('America/New_York'); 
	$date = date("D M j g:i a");
 $tlt = $wpdb->get_var("SELECT title FROM ".$wpdb->prefix . "current_requests WHERE id='".$_POST['played']."'");

 
$wpdb->update($wpdb->prefix . "current_requests",array('played'=>$date),array('id'=>$_POST['played']));
 
  		echo '<span id="adminnote" style="color:red;font-size:14px;">'. $tlt.' was marked as played.</span><br />';

 
 }
 
  
 if(isset($_POST['metoo'])){
 
 

$curNum = $wpdb->get_var("SELECT count FROM ".$wpdb->prefix . "current_requests WHERE id='".$_POST['metoo']."'");

		if(empty($curNum)){
		echo '<span id="adminnote"  style="color:red;font-size:14px;"> There has been an error, this song no longer exists. </span>';


		}else{
		$tlt = $wpdb->get_var("SELECT title FROM ".$wpdb->prefix . "current_requests WHERE id='".$_POST['metoo']."'");
		 
		 $num=$curNum + 1;

		$wpdb->update($wpdb->prefix . "current_requests", array('count'=>( $num ) ), array('id'=>$_POST['metoo']) );
				
		echo '<span  id="adminnote" style="color:red;font-size:14px;"> Your request has been added to '.$tlt.'. </span>';
						
		}				
 
 
 }
 
 
 if(isset($_POST['title'])){
 
 $title=trim($_POST['title']);
 $artist=trim($_POST['artist']);
 
 
 $cleansedin = preg_replace('#\W+#', '', strtolower($title));
 $cleansedart = preg_replace('#\W+#', '', strtolower($artist));
 
 $curReq = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . "current_requests");
 
		 
		 if(empty($curReq)){
		 
		$wpdb->insert($wpdb->prefix . "current_requests",array('title'=>$title,'artist'=>$artist,'count'=>1));
		
		echo '<span  id="adminnote" style="color:red;font-size:14px;"> Your request '.$title.' by '. $artist.' has been added</span>';

		 }else{
		 $sID = 0;
		 
			foreach ($curReq as $existReq){
			
		
			$sChk = preg_replace('#\W+#', '', strtolower($existReq->title));
			
				if( $cleansedin == $sChk ){
						$sID = $existReq->id;

						break;
				}
				
			}
				
				
				if($sID>=1 ){
				
				$hasWent = $wpdb->get_var("SELECT played FROM ".$wpdb->prefix . "current_requests WHERE id='".$sID."'");
				
						if(empty($hasWent)){
						$num = $wpdb->get_var("SELECT count FROM ".$wpdb->prefix . "current_requests WHERE id='".$sID."'");
						
						
						$wpdb->update($wpdb->prefix . "current_requests", array('count'=>( $num+1 ) ), array('id'=>$sID) );
				
						echo '<span  id="adminnote" style="color:red;font-size:14px;"> Your request '.$title.' has already been requested. You have been added as an additional request for this song. </span>';
						}else{
						
						echo '<span  id="adminnote" style="color:red;font-size:14px;">Sorry, '.$title.' has already been played at '.date('l \a\t g:i a',strtotime($hasWent)).' Please make a new request. </span>';
						}
				
				
				}else{
				
				$wpdb->insert($wpdb->prefix . "current_requests",array('title'=>$title,'artist'=>$artist,'count'=>1));
		
				echo '<span  id="adminnote" style="color:red;font-size:14px;"> Your request '.$title.' by '. $artist.' has been added</span>';
				
				}
				

			}
				
				
			
			
			
	}

 
 
 
   $curSong = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . "current_requests ORDER BY played ASC, count DESC");
 $i=1;



if(!current_user_can('manage_requests')){


?>	
	
	 <form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
	   <h3>Add Your Request</h3>
	    <table width="90%">


		<tr ><td><h4>Title</h4> <input type="text" name="title" size=65 value="" /></td><td><h4>Artist</h4> <input type="text" name="artist" value="" /></td></tr>
		<tr><td></td><td align="right"><input type="submit" value="Add My Request" /></td></tr>
	
	

		</table>
		

		
	 </form>	 
	
	
	
<br />
<hr />
<br />	

<?php
	}
	

	

if(current_user_can('manage_requests')){ 
  
   echo '<button href="javascript:;" name="a" onclick="ReloadMyPlaylist();" value="" > Refresh Request List </button><br />';
   ?>
   <script>


var i=<?php echo get_option('music_request_countdown');?>;

function ReloadTheList()
{


if(i==0){

ReloadMyPlaylist();

}else{
jQuery('#counter').html(i);
i--;
}

}

jQuery(document).ready(function() {
setInterval(function(){ReloadTheList()},1000);
});

function ReloadMyPlaylist(){
jQuery('#counter').html(0);
  jQuery.ajax({
			type: "post",url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',data: { action: 'music_list_reloader',nonce:'<?php echo wp_create_nonce( 'requestnonce' );?>' },
			beforeSend: function() {},
			success: function(html){ 
			 
			jQuery('#request-list').html(html).fadeIn('slow');
			i=<?php echo get_option('music_request_countdown');?>;
			
			}
		}); 
		return false;
  
  

}

</script>

   <div style="font-decoration:underline;margin-bottom:15px;">Auto refresh in:<span id="counter" style="font-weight:bold;margin-bottom:15px;">30</span> seconds</div>
   
   
   <?php
}	
	 
	
	 
 if (empty( $curSong)){
	echo ' <div id="request-list" >
<h4>There are no requests.</h4><br /></div>';	
	
 }else{
 ?>
 <div id="request-list" >
	 <form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
	 <table width="100%" ><tr><th style="text-align:left;">Song</th><th><?php  if(current_user_can('manage_requests')){ ?> Options <?php } ?></th></tr>
	 <?php  foreach ( $curSong as $bl){   ?>
	 
	 <tr><td><?php echo ucwords($bl->title).' - ('.ucwords($bl->artist).')  '.$bl->count .' requests'; ?></td>
	 
	
	 
	 <?php  if(current_user_can('manage_requests')){ ?>
	 <td><button name="remove" value="<?php echo $bl->id; ?>"> Delete This</button>
	 
	 <?php
				 if(empty($bl->played)){
				 ?>
				 
				 <button name="played" value="<?php echo $bl->id; ?>" > Set as Played </button> </td></tr>
				 
				 
				 <?php
				  
				 }else{
				 
				echo '<td>Played on '.date('l \a\t g:i a',strtotime($bl->played)).'</td></tr>';
				 
				 }
	 
	 
		}else{
		
			if(!empty($bl->played)){
			
			echo '<td>Played on '.date('l \a\t g:i a',strtotime($bl->played)).'</td></tr>';
			
			}else{
			echo '<td>  <button type="submit" name="metoo" value="'.$bl->id.'"> Request This Also </button></td></tr>';
			}
		
		}
		
	 
	 }


	 echo '</table>';
	 
     if(current_user_can('manage_requests')){ 
	 
	 echo '<br /><hr /><br /><button type="submit" name="clear" value="Clear List"> Clear Request List</button>';
	 
	 }
	 echo '</form></div>';
	 
 }
 
 
 return ob_get_clean(); 
 }
 
 
 
 
 ##################################
 #                                #
 #  Reload Song List              #
 #                                #
 ##################################
 
 add_action('wp_ajax_music_list_reloader', 'list_reloader');

  
function list_reloader(){
  global $wpdb;
 $curSong = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . "current_requests ORDER BY played ASC, count DESC");
 $i=1;

  ?>
   <form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
	 <table width="100%" ><tr><th style="text-align:left;">Song</th><th><?php  if(current_user_can('manage_requests')){ ?> Options <?php } ?></th></tr>
	 <?php  foreach ( $curSong as $bl){   ?>
	 
	 <tr><td><?php echo ucwords($bl->title).' - ('.ucwords($bl->artist).')  '.$bl->count .' requests'; ?></td>
	 
	
	 
	 <?php  if(current_user_can('manage_requests')){ ?>
	 <td><button name="remove" value="<?php echo $bl->id; ?>"> Delete This</button>
	 
	 <?php
				 if(empty($bl->played)){
				 ?>
				 
				 <button name="played" value="<?php echo $bl->id; ?>" > Set as Played </button> </td></tr>
				 
				 
				 <?php
				  
				 }else{
				 
				echo '<td>Played on '.date('l \a\t g:i a',strtotime($bl->played)).'</td></tr>';
				 
				 }
	 
	 
		}else{
		
			if(!empty($bl->played)){
			
			echo '<td>Played on '.date('l \a\t g:i a',strtotime($bl->played)).'</td></tr>';
			
			}else{
			echo '<td>  <button type="submit" name="metoo" value="'.$bl->id.'"> Request This Also </button></td></tr>';
			}
		
		}
		
	 
	 }


	 echo '</table>';
	 
     if(current_user_can('manage_requests')){ 
	 
	 echo '<br /><hr /><br /><button type="submit" name="clear" value="Clear List"> Clear Request List</button>';
	 
	 }
	 echo '</form>';
	 
	 die();
  
  }
 
 
 add_action('wp_footer','the_musiclist_credit');
 
 function the_musiclist_credit(){
	if(get_option('music_request_credit')=='yes'){
	 ?>
	 <script>
	 jQuery(document).ready(function() {
	 jQuery('footer:last').append('<div >Music Request List By <a href="http://www.whereyoursolutionis.com">Innovative Solutions</a></div>');
	 });
	 </script>
	 <?php
	 }
 }
 

 
 function setup_music_request_options(){
 
 add_options_page('Music Request Options','Music Request','manage_options','set-music-options','the_music_request_settings');
 
 
 }
 
 function the_music_request_settings(){
 global $wpdb;
 
 
 if(isset($_POST['counter'])){
 
 update_option('music_request_credit',$_POST['credit']);
 update_option('music_request_countdown',$_POST['counter']);
 
 echo '<div id="message" class="updated">Options Updated</div>';
 
 }
 
 
 ?>
 
 <div class="wrap"style="display:inline;">
<h2> Simplify The User Profile</h2>

		<div style="float:right;width:250px;margin-right:20%;margin-top:50px;top:0;" >
				<table class="widefat">
							<thead><tr><th>Need some code......</th></tr></thead>
							<tr><td style="padding:10px 5px 10px 5px;">
							<p>
							Scriptonite is available for hire.  If you need custom theme functions, plugins, or software why not <a href="http://www.whereyoursolutionis.com/contact-scriptonite/">get a quote</a>?
							
							</p>
							
							
							</td></tr>
							</table>
							

				<table class="widefat" style="margin-top:20px;">
							<thead><tr><th>Feature Requests?</th></tr></thead>
							<tr><td style="padding:10px 5px 10px 5px;">
							<p>
                            Have feature request for this plugin? Let us know <a href="http://www.whereyoursolutionis.com/music-list-feature-request-contact-form/" target="_blank">here</a>.							
							</p>
							
							
							</td></tr>
							</table>



		</div>
 <h2>Music Request Options</h2>
 
	 <form action ="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">
	 
	 Auto refresh every <input type="number" name="counter" value="<?php echo get_option('music_request_countdown');?>" /> seconds<br /><br /><br />
	 <input type="checkbox" value="yes" name="credit" <?php  if(get_option('music_request_credit')=='yes'){echo ' checked ';}?> /> Show credit in footer, never expected but always appreciated.<br /><br />
	 <input type="submit" value="Save Options" />
	 </form>
	 
 </div>
 <?php
 
 }
 
 
 
 ?>