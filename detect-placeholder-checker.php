<?php
/**
 * Plugin Name: Detect Placeholder Checker
 * Description: Automatically detects placeholder texts and images in WooCommerce product listings.
 * Version: 1.0.0
 * Author: Med Maaoui
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: detect-placeholder-checker
 */

add_action('admin_menu', 'add_placeholder_detection_page');

function add_placeholder_detection_page() {
    add_submenu_page(
        'woocommerce',
        'Placeholder Detection',
        'Placeholder Check',
        'manage_options',
        'placeholder-detection',
        'render_placeholder_detection_page'
    );
}

function render_placeholder_detection_page() {
    $image_issues = detect_placeholder_images();
    $text_issues = detect_placeholder_text();
    $short_description_issues = detect_short_descriptions();

    ?>
    <div class="wrap">
        <h1>Placeholder Detection</h1>
        
        <h2>Products with Placeholder Images (<?php echo count($image_issues); ?>)</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($image_issues)) : ?>
                    <?php foreach ($image_issues as $product) : ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo esc_html($product['title']); ?></td>
                            <td><a href="<?php echo esc_url($product['edit_link']); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No products with placeholder images found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2>Products with Placeholder Text (<?php echo count($text_issues); ?>)</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($text_issues)) : ?>
                    <?php foreach ($text_issues as $product) : ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo esc_html($product['title']); ?></td>
                            <td><a href="<?php echo esc_url($product['edit_link']); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="3">No products with placeholder text found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Products with Descriptions Below 200 Characters (<?php echo count($short_description_issues); ?>)</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Description Length</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($short_description_issues)) : ?>
                    <?php foreach ($short_description_issues as $product) : ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo esc_html($product['title']); ?></td>
                            <td><?php echo esc_html($product['description_length']); ?> characters</td>
                            <td><a href="<?php echo esc_url($product['edit_link']); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="4">No products with short descriptions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function detect_placeholder_images() {
    $products_with_placeholder = [];
    $placeholder_image_id = get_option('woocommerce_placeholder_image');
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish', // Check only published products
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $image_id = get_post_thumbnail_id($product_id);

            // Check if no image or the placeholder image is being used
            if (empty($image_id) || $image_id == $placeholder_image_id) {
                $products_with_placeholder[] = array(
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'edit_link' => get_edit_post_link($product_id)
                );
            }
        }
    }
    wp_reset_postdata();

    return $products_with_placeholder;
}

function detect_placeholder_text() {
    $products_with_placeholder = [];
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish', // Check only published products
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $description = get_post_field('post_content', $product_id);

            // Check if description is empty or contains placeholder text
            if (empty($description) || stripos($description, 'lorem ipsum') !== false || stripos($description, 'placeholder') !== false) {
                $products_with_placeholder[] = array(
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'edit_link' => get_edit_post_link($product_id)
                );
            }
        }
    }
    wp_reset_postdata();

    return $products_with_placeholder;
}

function detect_short_descriptions() {
    $products_with_short_descriptions = [];
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish', // Check only published products
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $description = get_post_field('post_content', $product_id);

            // Check if description length is under 200 characters
            if (!empty($description) && strlen($description) < 200) {
                $products_with_short_descriptions[] = array(
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'description_length' => strlen($description),
                    'edit_link' => get_edit_post_link($product_id)
                );
            }
        }
    }
    wp_reset_postdata();

    return $products_with_short_descriptions;

}