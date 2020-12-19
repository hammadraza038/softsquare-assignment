<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



/**
 * Register a custom post type called "book".
 *
 * @see get_post_type_labels() for label keys.
 */
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function my_theme_enqueue_styles() { 
    //  conditional loading of parent theme stylesheet if that got broken upon activation of child theme
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    wp_enqueue_style('bootsrap-css', get_stylesheet_directory_uri() . '/css/bootstrap.min.css');
    wp_enqueue_style('child-style', get_stylesheet_uri());
}

function wpdocs_codex_product_init() {
    $labels = array(
        'name' => _x('Products', 'Post type general name', 'textdomain'),
        'singular_name' => _x('Product', 'Post type singular name', 'textdomain'),
        'menu_name' => _x('Products', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Product', 'Add New on Toolbar', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'add_new_item' => __('Add New Product', 'textdomain'),
        'new_item' => __('New Product', 'textdomain'),
        'edit_item' => __('Edit Product', 'textdomain'),
        'view_item' => __('View Product', 'textdomain'),
        'all_items' => __('All Products', 'textdomain'),
        'search_items' => __('Search Product', 'textdomain'),
        'parent_item_colon' => __('Parent productg:', 'textdomain'),
        'not_found' => __('No product found.', 'textdomain'),
        'not_found_in_trash' => __('No product found in Trash.', 'textdomain'),
        'featured_image' => _x('product Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
        'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
        'archives' => _x('product archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
        'insert_into_item' => _x('Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
        'uploaded_to_this_item' => _x('Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
        'filter_items_list' => _x('Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
        'items_list_navigation' => _x('Product list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
        'items_list' => _x('Product lists', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
    );

    $argss = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'product'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('my_products', $argss);
    flush_rewrite_rules();
}

add_action('init', 'wpdocs_codex_product_init');


function manage_custom_product() {
    $dir = ABSPATH . "product-images/";

    $scanned_directory = array_diff(scandir($dir), array('..', '.'));

    if ($scanned_directory) {
        foreach ($scanned_directory as $filename) {

            $image_url = 'http://localhost/project/product-images/' . $filename;
            $image_name = pathinfo($filename, PATHINFO_FILENAME);

            $my_post = array(
                'post_title' => wp_strip_all_tags($image_name),
                'post_type' => 'my_products',
                'post_status' => 'publish'
            );

            $post_id = wp_insert_post($my_post);
            insert_product_feature_image($post_id, $image_url);
        }
    }
}

function show_products() {
    // The Query
    $args = array(
        'post_type' => 'my_products',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    $the_query = new WP_Query($args);
    ob_start();
// The Loop
    if ($the_query->have_posts()) {
        ?>
        <div class="container">
            <div class="row">
                <?php
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    ?>
                    <div class = "col-md-4">
                        <div class = "product-item" style="background-color:<?php the_field('color');?> ">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h3>
                            <div class = "product-thumbnail">
                                <?php the_post_thumbnail( 'medium' );?>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
        <?php
    } else {
        // no posts found
    }
    ?>


    <?php
    $products = ob_get_clean();
    /* Restore original Post Data */
    wp_reset_postdata();

    return $products;
}

add_shortcode('show_products', 'show_products');

function insert_product_feature_image($post_id, $image_url) {
    $image_name = basename($image_url);
    $upload_dir = wp_upload_dir(); // Set upload folder
    $image_data = file_get_contents($image_url); // Get image data
    $unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
    $filename = basename($unique_file_name); // Create image file name
// Check folder permission and define file location
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

// Create the image  file on the server
    file_put_contents($file, $image_data);

// Check image file type
    $wp_filetype = wp_check_filetype($filename, null);

// Set attachment data
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

// Create the attachment
    $attach_id = wp_insert_attachment($attachment, $file, $post_id);

// Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

// Define attachment metadata
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);

// Assign metadata to attachment
    wp_update_attachment_metadata($attach_id, $attach_data);

// And finally assign featured image to post
    set_post_thumbnail($post_id, $attach_id);
}
