<?php
/*
Plugin Name: Related Post
Plugin URI: https://www.github.com/jahidhasan018/related-post-local
Description: This plugin will show list of related post based on the current post category.
Version: 1.0
Author: Jahid Hossain
Author URI: https://www.jahiddev.com
License: GPLv2 or later
Text Domain: rlpl
Domain Path: "/languages"
Requires PHP: 7.4
Requires at least: 5.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Class: Related_Post_Local
 */
class Related_Post_Local {
    public function __construct() {
        $this->define_constants();

        // Load Plugin Text Domain
        add_action( 'plugins_loaded', array( $this, 'rlpl_load_textdomain' ) );
 
        // Register Activation Hook
        register_activation_hook( __FILE__, array( $this, 'rlpl_after_activate' ) );

        // Show related post on single post page
        add_filter( 'the_content', array( $this, 'rlpl_display_related_post' ), 10, 1 );

        // Enqueue Scripts and Styles
        add_action( 'wp_enqueue_scripts', array( $this, 'rlpl_enqueue_scripts' ) );
    }

    // Define Constants
    public function define_constants(){
        define( "RLPL_VERSION", "1.0" );
        define( 'RLPL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        define( 'RLPL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    // Load Plugin Text Domain
    public function rlpl_load_textdomain() {
        load_plugin_textdomain( 'rlpl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    // Show related post on single post page
    public function rlpl_display_related_post( $content ){
        if( is_single() ){
            $post_id = get_the_ID();
            $post_categories = wp_get_post_categories( $post_id );

            // Related Post Query
            $args = [
                'post_type' => 'post',
                'post__not_in' => [$post_id],
                'posts_per_page' => apply_filters( 'rlpl_related_post_count', 5),
                'category__in' => $post_categories,
                'orderby' => 'rand'
            ];
            $related_posts = new WP_Query( $args );

            // Related post title
            $related_post_title = apply_filters( 'rlpl_related_post_title', __( 'Related Post', 'rlpl' ) );

            // Related Post grid or list style
            $related_post_style = apply_filters( 'rlpl_related_post_style', 'grid' ); // grid or list


            if( $related_posts ){
                ob_start();
                ?>
                <div class="related-post">
                    <h2><?php esc_html_e( $related_post_title ); ?></h2>

                    <ul class="rlpl-related-post-<?php esc_attr_e( $related_post_style ); ?>">
                        <?php
                            while( $related_posts->have_posts() ){
                                $related_posts->the_post();
                                ?>
                                    <li>
                                        <?php 
                                            $show_thumbnail = apply_filters( 'rlpl_show_thumbnail', true );

                                            if ( has_post_thumbnail() && $show_thumbnail === true ) {
                                                echo "<a class='rlpl-thumbnail' href='" . get_the_permalink() . "'>";
                                                    the_post_thumbnail('thumbnail');
                                                echo "</a>";
                                            }
                                        ?>
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </li>
                                <?php
                            }
                            wp_reset_postdata();
                        ?>
                    </ul>
                </div>
                <?php
                $related_post_content = ob_get_clean();
            }
        }
        return $content . $related_post_content;
    }

    // Enqueue Scripts and Styles
    public function rlpl_enqueue_scripts(){
        if( is_single() ){
            wp_enqueue_style( 'rlpl-style', RLPL_PLUGIN_URL . 'assets/css/style.css', [], RLPL_VERSION );
        }        
    }

}

// Initialize the plugin
new Related_Post_Local();