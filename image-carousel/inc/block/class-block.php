<?php

// Exit if accessed directly.

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! class_exists( 'Carousel_Block' ) ) {

    class Carousel_Block
    {

        public $script_slug       = 'carousel-block-js';
        public $style_slug        = 'carousel-block-style-css';
        public $editor_style_slug = 'carousel-block-editor-css';

        public function __construct()
        {

            add_action( 'init', array( $this, 'register_block_action' ) );

            if ( is_admin() ) {
                add_action( 'enqueue_block_assets', array( $this, 'icp_enqueue_block_assets' ), 5 );
            }

        }

        public function icp_widget_script_dependencies( $dep )
        {

            $dep = array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-block-editor' );

            return $dep;

        }

        public function icp_enqueue_block_assets()
        {

            $is_widget    = false;
            $dependencies = apply_filters( 'icp_script_dependencies', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ) );

            $currentScreen = get_current_screen();

            if ( isset( $currentScreen->id ) && $currentScreen->id === 'widgets' ) {

                $is_widget = true;
                add_filter( 'icp_script_dependencies', array( $this, 'icp_widget_script_dependencies' ) );

            }

            wp_register_script(
                $this->script_slug, // Handle.
                plugin_dir_url( __FILE__ ).'carousel-block/dist/blocks.build.js', // Block.build.js: We register the block here. Built with Webpack.
                apply_filters( 'icp_script_dependencies', $dependencies ) // Dependencies, defined above.
            );

            // Styles.
            wp_register_style(
                $this->style_slug, // Handle.
                plugin_dir_url( __FILE__ ).'carousel-block/dist/blocks.style.build.css', // Block style CSS.
                array( 'wp-blocks' ) // Dependency to include the CSS after it.
            );

            wp_register_style(
                $this->editor_style_slug, // Handle.
                plugin_dir_url( __FILE__ ).'carousel-block/dist/blocks.editor.build.css', // Block editor CSS.
                array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
            );

            wp_localize_script( $this->script_slug, 'icp_block_vars', array( 'is_widget' => $is_widget ) );

        }

        public function register_block_action()
        {

            if ( ! function_exists( 'register_block_type' ) ) {
                return;
            }

            register_block_type(
                'carousel/block', // Block name with namespace
                array(
                    'style'           => $this->style_slug, // General block style slug
                    'editor_style' => $this->editor_style_slug, // Editor block style slug
                    'editor_script' => $this->script_slug, // The block script slug
                    'attributes' => array(
                        'images'           => array(
                            'type'    => 'string',
                            'default' => '[]',
                        ),
                        'native_shortcode' => array(
                            'type'    => 'string',
                            'default' => '',
                        ),
                        'columns'          => array(
                            'type'    => 'number',
                            'default' => 5,
                        ),
                        'style_boxshadow'  => array(
                            'type'    => 'boolean',
                            'default' => true,
                        ),
                    ),
                    'render_callback' => array( $this, 'render_callback' ),
                )
            );
        }

        public function render_callback( $attributes, $content = null, $context = 'frontend' )
        {

            if ( ! is_admin() && isset( $attributes['native_shortcode'] ) && $attributes['native_shortcode'] ) {

                return $attributes['native_shortcode'];

            }

            return '';

        }

    }

    new Carousel_Block();

}