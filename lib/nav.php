<?php
/**
 * Navigation
 *
 * @package      Bootstrap for Genesis
 * @since        1.0
 * @link         http://www.recommendwp.com
 * @author       RecommendWP <www.recommendwp.com>
 * @copyright    Copyright (c) 2015, RecommendWP
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
*/

if ( class_exists( 'UberMenuStandard' ) ) {
    return;
}

// remove primary & secondary nav from default position
remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_header', 'genesis_do_nav' );

// filter menu args for bootstrap walker and other settings
add_filter( 'wp_nav_menu_args', 'bfg_nav_menu_args_filter' );

// add bootstrap markup around the nav
add_filter( 'wp_nav_menu', 'bfg_nav_menu_markup_filter', 10, 2 );
function bfg_nav_menu_args_filter( $args ) {

    require_once( BFG_THEME_MODULES . 'wp_bootstrap_navwalker.php' );

    $navalign = get_theme_mod( 'navalign', false );
    
    if ( 'primary' === $args['theme_location'] ) {
        $args['menu_class'] = 'nav navbar-nav ' . $navalign;
        $args['fallback_cb'] = 'wp_bootstrap_navwalker::fallback';
        $args['walker'] = new wp_bootstrap_navwalker();
    }
    return $args;
}
function bfg_nav_menu_markup_filter( $html, $args ) {
    // only add additional Bootstrap markup to
    // primary and secondary nav locations
    if ( 'primary'   !== $args->theme_location ) {
        return $html;
    }

    $data_target = "nav-collapse" . sanitize_html_class( '-' . $args->theme_location );
    $output = <<<EOT
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#{$data_target}">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
EOT;
    // only include blog name and description in the nav
    // if it is the primary nav location
    if ( 'primary' === $args->theme_location ) {
        $output .= apply_filters( 'bfg_navbar_brand', bfg_navbar_brand_markup() );
    }
    $output .= '</div>'; // .navbar-header
    $output .= genesis_html5() ? "<nav class=\"collapse navbar-collapse\" id=\"{$data_target}\">" : "<div class=\"collapse navbar-collapse\" id=\"{$data_target}\">";
    $output .= $html;
    
    if ( get_theme_mod( 'navextra', false ) ) {
        $output .= apply_filters( 'bfg_navbar_content', bfg_navbar_content_markup() );
    }
    $output .= genesis_html5() ? '</nav>' : '</div>'; // .collapse .navbar-collapse
    
    return $output;
}

function bfg_navbar_brand_markup() {
    // Display navbar brand on small displays 
    $output = '<a class="navbar-brand" id="logo" title="'.esc_attr( get_bloginfo( 'description' ) ).'" href="'.esc_url( home_url( '/' ) ).'">';
    
    // $output .= apply_filters( 'bfg_nav_brand_args', get_bloginfo( 'name' ) );
    $output .= get_theme_mod( 'logo', false ) ? '<img src="'.get_theme_mod( 'logo' ).'" alt="'.esc_attr( get_bloginfo( 'description' ) ).'" />' : get_bloginfo( 'name' );

    $output .= '</a>';

    return $output;
}

//* Navigation Extras
function bfg_navbar_content_markup() {
    $url = get_home_url();
    
    $choices = get_theme_mod( 'select', false );
    switch( $choices ) {
        case 'search':
        default:
            $output = '<form method="get" class="navbar-form navbar-right" action="' .  $url . '" role="search">';
            $output .= '<div class="form-group">';
            $output .= '<input class="form-control" name="s" placeholder="Search" type="text">';
            $output .= '</div>';
            $output .= '<button class="btn btn-default" value="Search" type="submit">Submit</button>';
            $output .= '</form>';
            break;
        case 'date': 
            $output = '<p class="navbar-text navbar-right">';
            $output .= date_i18n( get_option( 'date_format' ) );
            $output .= '</p>';
            break;
    }

	return $output;
}

//* Filter primary navigation output to match Bootstrap markup
// @link http://wordpress.stackexchange.com/questions/58377/using-a-filter-to-modify-genesis-wp-nav-menu/58394#58394
add_filter( 'genesis_do_nav', 'bfg_override_do_nav', 10, 3 );
function bfg_override_do_nav($nav_output, $nav, $args) {
    // return the modified result
    return sprintf( '%1$s', $nav );

}