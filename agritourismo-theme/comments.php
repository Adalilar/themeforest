<?php
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) { ?>
		<p class="nocomments"><?php printf ( __( 'This post is password protected. Enter the password to view comments.' , THEME_NAME ));?></p>
	<?php
		return;
	}

	

?>
<?php //You can start editing here. ?>
						<div class="main-title">
							<h2><?php comments_number(__('No Comments', THEME_NAME), __('1 Comment', THEME_NAME), __('% Comments', THEME_NAME)); ?></h2>
							<span><?php _e("What people say",THEME_NAME);?></span>
						</div>
						<?php if ( have_comments() && comments_open()) : ?>
							<ol class="comments" id="comments">
								<?php wp_list_comments('type=comment&callback=orangethemes_comment'); ?>
							</ol>
							<div class="comments-pager"><?php paginate_comments_links(); ?></div>
							
						 <?php else : // this is displayed if there are no comments so far ?>

							<?php if ( comments_open() ) : ?>
								<div class="no-comments-banner">
									<a href="#respond">
										<img src="<?php echo THEME_IMAGE_URL;?>no-comments-layer.png" alt="<?php _e("Respond", THEME_NAME);?>" />
									</a>
								</div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( comments_open() ) : ?>
							<?php if ( get_option('comment_registration') && !is_user_logged_in() ) : ?>
								<p class="registered-user-restriction"><?php printf ( __( 'Only <a href="%1$s"> registered </a> users can comment.', THEME_NAME ), wp_login_url( get_permalink() ));?> </p>
							<?php else : ?>
								<div class="main-title">
									<h2><?php _e("Write a Comment", THEME_NAME);?></h2>
									<span><?php _e("Join the conversation", THEME_NAME);?></span>
								</div>

								<div class="writecomment">
									<a href="#" name="respond"></a>
									<?php 
										$defaults = array(
											'comment_field'       	=> '<p class="contact-form-message"><textarea name="comment" id="comment" placeholder="'.__("Your message..",THEME_NAME).'"></textarea></p>',
											'comment_notes_before' 	=> '',
											'comment_notes_after'  	=> '',
											'id_form'              	=> 'writecomment',
											'id_submit'            	=> 'submit',
											'title_reply'          => '',
											'title_reply_to'       => '',
											'cancel_reply_link'    	=> '',
											'label_submit'         	=> ''.__( 'Post a Comment', THEME_NAME ).'',
										);
										comment_form($defaults);			
									?>
								</div>



							<?php endif; // if you delete this the sky will fall on your head ?>

						<?php endif; ?>
						
