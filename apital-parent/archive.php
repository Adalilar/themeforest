<?php
/**
 * The template for displaying Archive pages
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 */
get_header();?>
<?php
    $subtitle = ''; $blog_view = 'normal'; $breadcrumbs = 'no';
    $title = (is_category() || is_tag()) ? single_cat_title(  '', false ) : __('Archive','fw');
    if(defined('FW'))
    {
        //get inner banner
        $banner = fw_get_db_settings_option('blog_banner');

        if($banner['enable-blog-banner'] == 'yes')
        {
            $blog_subtile = fw_get_db_term_option(get_query_var('cat'), 'category', 'blog-subtitle');
            $subtitle = (!empty($blog_subtile)) ? $blog_subtile : $banner['yes']['blog-subtitle'];
            $breadcrumbs = $banner['yes']['enable-blog-breadcrumbs'];
        }

        //get blog view type
        $blog_view = fw_get_db_settings_option('blog_view');
        $blog_view = (isset($_GET['blog_type']) && $_GET['blog_type'] == 'medium') ? 'medium' : $blog_view;

        //show inner banner
        fw_show_inner_banner($banner['enable-blog-banner'], $title, $subtitle, $breadcrumbs);
    }
?>

<?php $sidebar_position = (function_exists('fw_ext_sidebars_get_current_position')) ? fw_ext_sidebars_get_current_position() : 'right';?>
    <div class="w-section section">
        <div class="w-container">
            <div class="w-row">
                <div class="w-col <?php echo ($sidebar_position == null || $sidebar_position == 'full') ? 'w-col-12' : 'w-col-9'; ?> w-col-stack">
                    <div class="normal-blog-wrapper">

                        <?php if ( have_posts() ) : ?>

                            <?php
                            // Start the Loop.
                            while ( have_posts() ) : the_post();

                                if($blog_view == 'normal')
                                    get_template_part( 'listing', 'blog1' );
                                else
                                    get_template_part( 'listing', 'blog2' );

                            endwhile;
                            // archive pagination
                            fw_theme_paging_nav();

                        else :
                            // If no content, include the "No posts found" template.
                            get_template_part( 'content', 'none' );

                        endif; ?>
                    </div>
                </div>
                <?php if($sidebar_position == 'left' || $sidebar_position == 'right'):?>
                    <div class="w-col w-col-3 w-col-stack">
                        <div class="sidebar">
                            <?php get_sidebar();?>
                        </div>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </div>
    <?php
        //call to action settings
        if(defined('FW'))
        {
            $call_to_action = fw_get_db_settings_option('blog_action');

            if($call_to_action['enable-blog-action'] == 'yes')
                fw_show_call_to_action($call_to_action['yes']);
        }
    ?>
<?php
get_footer();