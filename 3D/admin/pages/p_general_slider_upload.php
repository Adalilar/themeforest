<?php include_once('../func_wp_load.php'); ?>

<?php
if(isset($_GET['logo'])) # get logo
{
	$qp_slider_id = trim(mysql_real_escape_string($_GET['slider_id']));
	echo '<img id="image_logo_src" src="'.get_iam($qp_slider_id,'','1').'" />';
} # /get logo
else if(isset($_GET['order'])) # order
{
	$orderArray = $_POST['id']; // move array that comes with post into variable									
    $order = 1; // the head of record number in the sequence database
    foreach ($orderArray as $id) // move all ID that is in array to user ID thanks to foreach function								
    {
		// Update with UPDATE function in the database for every foreach loop
		$order = trim(strip_tags(mysql_real_escape_string($order)));
		$id = trim(strip_tags(mysql_real_escape_string($id)));
		
        mysql_query("UPDATE ".$prefix."iam SET ord = " . $order . " WHERE id = " . $id . "") or die('Error, insert query failed');
        $order++;	 // Increase the value of variable as [1] in every foreach loop. By this means, sequence is increased. 		
    }	
} # /order
else if(isset($_POST['sliderUpload'])) # else if post image
{
	include_once('../func_wp_load.php'); 
	include_once(TEMPLATEPATH."/admin/class.upload.php");  
	
	$pg_slider_id = trim(mysql_real_escape_string($_POST['sliderID']));
	$pp_title = trim(mysql_real_escape_string($_POST['text_title']));
	$pp_url = trim(mysql_real_escape_string($_POST['text_url']));
	$pp_description = trim(mysql_real_escape_string($_POST['text_description']));
	
	$upload = new upload($_FILES['image1']);
	if ($upload->uploaded)
	{
		$upload->file_auto_rename = true;
		$upload->image_resize = false;
		$upload->image_ratio_crop      = false;
		$upload->process($three_folder.'/wp-content/uploads/iamthemes/');
	
		if($upload->processed)
		{
			if(strstr(get_iam($pg_slider_id,'','1'),'/wp-content/uploads/iamthemes/')){unlink($three_folder.str_replace(get_bloginfo('url'), '', get_iam($pg_slider_id,'','1')));}
			$image1 = get_bloginfo('url').'/wp-content/uploads/iamthemes/'.$upload->file_dst_name;
			update_iam('','homepage_slider',$image1,$pp_title,$pp_url,$pp_description,'','','','',$pg_slider_id);
			$uploaded = 1;
			
		}
		else { echo  $upload->error; $uploaded = 0;}
	}
	else if($pp_title != '' or $pp_url != '' or $pp_description != '')
	{
		update_iam('','homepage_slider',get_iam($pg_slider_id,'','1'), $pp_title,$pp_url,$pp_description,'','','','',$pg_slider_id);
		$uploaded = 1;
	}
	else { $uploaded = 0;}
?>
	<script language="javascript" type="text/javascript">window.top.window.$.stopUpload('<?php echo $uploaded; ?>', '<?php echo $pg_slider_id; ?>');</script>  

<?php	
} # / else if post image 
else
{
?>
<?php include_once('../func.php'); ?>

<!-- #Bigtitle -->
<div class="bigtitle">
    <h1>Slider Upload</h1>
</div>



<!-- #Slide View -->
<div class="stage">
    <h1 class="mini-title">Slide Preview:</h1>
    

<script language="javascript" type="text/javascript">
$.startUpload = function(slider_id){
	$("#form_image_return_" + slider_id).html('<?php echo $loadingBar; ?>'); 
}


$.stopUpload = function (success, slider_id){
	if (success == 1)
	{
		$("#form_image_return_" + slider_id).html(''); 
		$("#div_image_logo_" + slider_id).load("<?php bloginfo('template_url'); ?>/admin/pages/p_general_slider_upload.php?logo&slider_id=" + slider_id);	
	}
	else
	{
	  $("#form_image_return").html(''); 
	}
}

$.sortTable = function() // function created
{
	//When the function works, convert all data in in <ul> label with #list ID into array 
	var orderArray = $(this).sortable("serialize"); 
	//Post the variable in [orderArray] to updata_mysql.php page.
	//We add the returned result from update_mysql.php to #div_return ID								 
	$.post("<?php bloginfo('template_url'); ?>/admin/pages/p_general_slider_upload.php?order", orderArray, function(theResponse){$("#div_return").html(theResponse);});  
}	
<!-- /FUNCTION CREATED -->

$(document).ready(function(){ 	   
	$(function() {
		$("#list").sortable({ 
		opacity: 0.6,  // opacity value is [0.6] in the process of moving
						
		cursor: 'move', // mouse cursor is [move] during moving
						
		update: $.sortTable // Required function during movement process [defined above]
					
		});
	});

});	
</script>            
<ul id="list">   
	<?php
    $query_homepage_slider = mysql_query("SELECT * FROM ".$prefix."iam WHERE title='homepage_slider' ORDER BY ord ASC");
    while($list_homepage_slider = mysql_fetch_assoc($query_homepage_slider))
    {
        $q_slider_id = $list_homepage_slider['id'];
        $q_slider_image_url = $list_homepage_slider['value1'];
        $q_slider_title = $list_homepage_slider['value2'];
        $q_slider_link = $list_homepage_slider['value3'];
        $q_slider_description = $list_homepage_slider['value4'];
    ?>
        
     <li id="id_<?php echo $q_slider_id; ?>" class="accordion-li">
	<!-- Accordion Start -->
    <div class="mfx_accordion">
        
        <div class="section">
            <h2 class="trigger settings">
            	<a href="#" class="icon"><img src="<?php bloginfo('template_url'); ?>/admin/img/move.png" alt=""></a> 
            	<span class="icon-title"> Slide : <?php echo $q_slider_title; ?></span>
            </h2>
            
            <!-- #Content -->
            <div class="content"> 
                <div class="detail">
                <form name="form_<?php echo $q_slider_id; ?>" id="form_<?php echo $q_slider_id; ?>" action="<?php bloginfo('template_url'); ?>/admin/pages/p_general_slider_upload.php" method="POST" enctype="multipart/form-data" target="upload_target_<?php echo $q_slider_id; ?>">
                    <a href="#" class="delete-button"><img src="<?php bloginfo('template_url'); ?>/admin/img/delete.png" alt=""></a>
                    <!-- #Picture -->
                    <div class="logo-preview">
                    	<div id="div_image_logo_<?php echo $q_slider_id; ?>">
                       		<img src="<?php echo $q_slider_image_url; ?>" alt="">
                        </div>
                        <!-- #Form Input -->
                        <span class="logo-text-two">
                            
                            <div class="uploader red">
                                <div id="div_form_image">
                                    <input type="text" name="image1_text" id="image1_text" class="filename" readonly="readonly" value=""/>
                                    <input type="button" name="file" class="buttonupload" value="File"/>
                                    <input type="file" name="image1" id="image1" size="30"/>
                                </div>
                            </div>
                            
                            <h1 class="mini-title-two">Title:</h1>
                            <input type="text" name="text_title" id="text_title" class="form-input" value="<?php echo $q_slider_title; ?>"/>
                            <span class="clear"></span>
                                                                    
                            <h1 class="mini-title-two">Link/Url:</h1>
                            <input type="text" name="text_url" id="text_url" class="form-input" style="margin-bottom:24px;" value="<?php echo $q_slider_link; ?>"/>
                            <span class="clear"></span>
                            
                            <h1 class="mini-title-two">Description:</h1>
                            <textarea name="text_description" class="form-input"><?php echo $q_slider_description; ?></textarea>
                            <span class="clear"></span>
                            
                            <div class="stage-two">
    <button href="#" id="form_image_button" onClick="document.getElementById('submit_buttons_<?php echo $q_slider_id; ?>').click(); $.startUpload(<?php echo $q_slider_id; ?>)" class="btn_pink">Save</button>
</div>
                            
                        </span>
                        
                    </div> <!-- /.logo-preview -->
                    <div class="clear"></div> 
                    <input type="hidden" name="sliderID" value="<?php echo $q_slider_id; ?>" />	
                    <input type="hidden" name="sliderUpload" />	
                    <input type="submit" value="Submit" style="display:none;" id="submit_buttons_<?php echo $q_slider_id; ?>" />
                </form>
                <iframe id="upload_target_<?php echo $q_slider_id; ?>" name="upload_target_<?php echo $q_slider_id; ?>" src="<?php bloginfo('template_url'); ?>/admin/pages" style="width:0;height:0;border:0px solid #fff;"></iframe>
				<div id="form_image_return_<?php echo $q_slider_id; ?>"></div>

                </div> <!-- /.detail -->
            </div> <!-- /.content -->
            
        </div> <!-- /.section -->
        
	</div> <!-- /.mfx_accordion -->
    </li> <!-- /#id_ -->
        
        
         <?php
		}
		?>
        
        
</ul> <!-- /#list -->

    <div class="clear"></div>
</div> <!-- /.stage -->
  <div id="div_return"></div> 

    
<?php } ?>     

