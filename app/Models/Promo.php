<?php
namespace LpdPromo\Models;

use Timber\Timber;
use Carbon_Fields\Field;
use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Detection\MobileDetect;


class Promo
{
    const POST_TYPE = 'lpd-promo';
    public function __construct()
    {
    }

    public static function init()
    {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_filter('manage_lpd-promo_posts_columns', [__CLASS__, 'add_new_columns']);
        add_action('carbon_fields_register_fields', [__CLASS__, 'register_custom_fields']);
        add_action('manage_lpd-promo_posts_custom_column', [__CLASS__, 'manage_custom_columns'], 10, 2);
        add_action('wp', [__CLASS__, 'setup_schedule']);
        add_action('lpd_check_expired_promos', [__CLASS__, 'handle_expired_promos']);
        add_action('template_redirect', [ __CLASS__, 'check_promo_public_status' ] );
        add_action('carbon_fields_register_fields', [__CLASS__, 'make_promo_block']);


    }
    public static function check_promo_public_status() {
        if(is_singular(SELF::POST_TYPE)) {
            $post_id = get_the_ID();
            $promo_not_public = carbon_get_post_meta($post_id, 'promo_not_public');
            if($promo_not_public) {
                // Add noindex meta tag to prevent search engines from indexing the page
                echo '<meta name="robots" content="noindex">';
                wp_redirect( home_url() );
                exit;
            }
        }
    }
// NEED TO ADD HELP TEXT FOR THE CUSTOM FIELDS
// ->set_help_text('This is the promo start date') as an example
    public static function register_custom_fields()
    {
        Container::make('post_meta', 'Promo Settings')
            ->where('post_type', '=', 'lpd-promo')
            ->add_tab('Settings',[
                Field::make('checkbox', 'promo_not_public', 'Disable Individual Promo'),
                Field::make('date', 'promo_start_date', 'Promo Start Date')
                ->set_attribute('placeholder', 'Please select the date and time')
                ->set_width(50),
                Field::make('date', 'promo_end_date', 'Promo End Date')
                    ->set_attribute('placeholder', 'Please select the date and time')
                    ->set_width(50),
                Field::make('association', 'linked_category', 'Promo Category')
                ->set_types(
                    [
                        [
                            'type' => 'term',
                            'taxonomy' => 'promo_category'
                        ]
                    ])
                    ->set_width(90)
            ])
            ->add_tab( 'Images', [
                Field::make('image', 'mobile_promo_image', 'Mobile Promo Image')
                ->set_width(10),
                Field::make('radio', 'desktop_promo_image_type', 'Desktop Promo Image Type')
                    ->add_options([
                        'full' => 'Full Width Image',
                        'half' => 'Half Width Image',
                    ])
                    ->set_width(10),
                Field::make('image', 'full_desktop_promo_image', 'Desktop Promo Image -- Full Width')
                ->set_conditional_logic([
                    [
                        'field' => 'desktop_promo_image_type',
                        'value' => 'full',
                    ],
                ])
                ->set_width(10),
                Field::make('image', 'half_desktop_promo_image', 'Desktop Promo Image -- Half Width')
                ->set_conditional_logic([
                    [
                        'field' => 'desktop_promo_image_type',
                        'value' => 'half',
                    ],
                ])
                ->set_width(10),

            ])
            ->add_tab('Content', [
                Field::make('text', 'promo_title', 'Promo Title'),
                Field::make('rich_text', 'promo_content', 'Promo Content')
                ->set_width(100),
                Field::make('text', 'promo_link_text', 'Promo Link Text'),
            ]);
    }

    public static function register_post_type()
    {
        register_post_type(
            self::POST_TYPE,
            [
                'labels' => [
                    'name' => __('Promos'),
                    'singular_name' => __('Promo'),
                    'menu_name' => __('Promos'),
                    'name_admin_bar' => __('Promo'),
                    'add_new' => __('Add New'),
                    'add_new_item' => __('Add New Promo'),
                    'new_item' => __('New Promo'),
                    'edit_item' => __('Edit Promo'),
                    'view_item' => __('View Promo'),
                    'all_items' => __('All Promos'),
                    'search_items' => __('Search Promos'),
                    'parent_item_colon' => __('Parent Promos:'),
                    'not_found' => __('No promos found.'),
                    'not_found_in_trash' => __('No promos found in Trash.')
                ],
                'public' => true,
                'has_archive' => true,
                'show_ui' => true,
                'rewrite' => [
                    'slug' => 'promos'
                ],
                'supports' => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields'
                ],
                'menu_icon' => 'dashicons-awards',
                'show_in_rest' => true,
                'rest_base' => 'lpd-promo'
            ]
        );
    }

    public static function register_taxonomies()
    {
        register_taxonomy('promo_category', self::POST_TYPE, [
            'labels' => [
                'name' => __('Promo Categories'),
                'singular_name' => __('Promo Category'),
                'menu_name' => __('Promo Categories'),
                'all_items' => __('All Promo Categories'),
                'edit_item' => __('Edit Promo Category'),
                'view_item' => __('View Promo Category'),
                'update_item' => __('Update Promo Category'),
                'add_new_item' => __('Add New Promo Category'),
                'new_item_name' => __('New Promo Category Name'),
                'parent_item' => __('Parent Promo Category'),
                'parent_item_colon' => __('Parent Promo Category:'),
                'search_items' => __('Search Promo Categories'),
                'popular_items' => __('Popular Promo Categories'),
                'separate_items_with_commas' => __('Separate promo categories with commas'),
                'add_or_remove_items' => __('Add or remove promo categories'),
                'choose_from_most_used' => __('Choose from the most used promo categories'),
                'not_found' => __('No promo categories found.')
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'lpd-promo',
            'show_in_rest' => true,
            'hierarchical' => true
        ]);

    }

    // create new columns for this post type, these should include start date, end date, image, promo category
    public static function add_new_columns($columns)
    {
        $new_columns['cb'] = '<input type="checkbox" />';
        $new_columns['title'] = __('Title');
        $new_columns['status'] = __('Status');
        $new_columns['start_date'] = __('Start Date');
        $new_columns['end_date'] = __('End Date');
        $new_columns['promo_category'] = __('Promo Category');
        $new_columns['image'] = __('Image');
        $new_columns['date'] = __('Date');
        return $new_columns;
    }
    public static function is_promo_active($post_id) {
        $start_date = carbon_get_post_meta($post_id, 'promo_start_date');
        $end_date = carbon_get_post_meta($post_id, 'promo_end_date');
        $now = new \DateTime();
        $now = $now->format('Y-m-d');

        return ($now >= $start_date && $now <= $end_date);
    }
    public static function get_active_promos() {
        $args = array(
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $query = new \WP_Query($args);
        $active_promos = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                if (self::is_promo_active($post_id)) {
                    $active_promos[] = get_post($post_id);

                }
            }
            wp_reset_postdata();
        }
        return $active_promos;
    }
    public static function handle_expired_promos() {
        $args = array(
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                if (!self::is_promo_active($post_id)) {
                    $post = get_post($post_id);
                    $post->post_status = 'private';
                    wp_update_post($post);
                } else {
                    $post = get_post($post_id);
                    $post->post_status = 'publish';
                    wp_update_post($post);
                }
            }
            wp_reset_postdata();
        }
    }
    public static function setup_schedule() {
        if (!wp_next_scheduled('lpd_check_expired_promos')) {
            wp_schedule_event(time(), 'hourly', 'lpd_check_expired_promos');
        }
    }

    public static function manage_custom_columns($column, $post_id) {
        switch ($column) {
            case 'status':
            // if the time is between the start and end date then the status is active
                $start_date = carbon_get_post_meta($post_id, 'promo_start_date');
                $end_date = carbon_get_post_meta($post_id, 'promo_end_date');
                $now = new \DateTime();
                $now = $now->format('Y-m-d');

                if ($now >= $start_date && $now <= $end_date) {
                    echo '<span style="color: green;">Active</span>';
                } else if ($now < $start_date) {
                    echo '<span style="color: orange;">Pending</span>';
                } else {
                    echo '<span style="color: red;">Expired</span>';
                }
                break;
            case 'start_date':
                $start_date = carbon_get_post_meta($post_id, 'promo_start_date');
                echo $start_date;
                break;
    
            case 'end_date':
                $end_date = carbon_get_post_meta($post_id, 'promo_end_date');
                echo $end_date;
                break;
    
            case 'promo_category':
                $categories = get_the_terms($post_id, 'promo_category');
                if ($categories) {
                    $output = '';
                    foreach ($categories as $category) {
                        $output .= '<a href="' . esc_url(get_edit_term_link($category->term_id, 'promo_category')) . '">' . esc_html($category->name) . '</a>, ';
                    }
                    echo trim($output, ', '); // trim the trailing comma
                } else {
                    echo '—'; // dash if no categories are linked
                }
                break;

            case 'image':
                $desktop_image_type = carbon_get_post_meta($post_id, 'desktop_promo_image_type');
                if ($desktop_image_type === 'full') {
                    $image_id = carbon_get_post_meta($post_id, 'full_desktop_promo_image');
                } else if ($desktop_image_type === 'half') {
                    $image_id = carbon_get_post_meta($post_id, 'half_desktop_promo_image');
                } else {
                    $image_id = carbon_get_post_meta($post_id, 'mobile_promo_image');
                }
                if ($image_id) {
                    $image_src = wp_get_attachment_image_src($image_id, 'thumbnail');
                    echo '<img src="' . esc_url($image_src[0]) . '" style="max-width:150px;">';
                } else {
                    echo '—'; // dash if no image is set
                }
                break;
    
            default:
                break;
        }
    }
    public static function make_promo_block() {
        $terms = Timber::get_terms('promo_category', ['hide_empty' => false]);
        $category_options = [];
        foreach ($terms as $term) {
            $category_options[$term->id] = str_replace('&amp;', '&', $term->name);
        }
        Block::make( __( 'Promo Block' ) )
            ->set_description( 'A block that displays active promos' )
            ->add_fields([
                Field::make( 'select' , 'promo_layout', 'Promo Layout' )
                    ->add_options( [
                        'list' => 'List',
                        'slider' => 'Slider',
                    ])
                    ->set_width( 50 ),
                Field::make( 'select' , 'promo_type', 'Promo Type' )
                    ->add_options( [
                        'all' => 'All',
                        'category' => 'Category',
                    ])
                    ->set_width( 50 ),
                Field::make( 'select' , 'promo_category', 'Promo Category' )
                    ->add_options(
                    // use $terms to make the keys and values
                        $category_options
                    )
                    ->set_width( 50 )
                    ->set_conditional_logic( [
                        [
                            'field' => 'promo_type',
                            'value' => 'category',
                        ],
                    ])
            ])
            ->set_icon( 'megaphone' )
            ->set_mode('preview')
            ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
            $promos = SELF::get_active_promos();

            // if ($fields['promo_type'] === 'category') {
            //     $promos = array_filter($promos, function($promo) use ($fields) {
            //         $promo_category = get_the_terms($promo->ID, 'promo_category');
            //         $promo_category = array_shift($promo_category);
            //         return $promo_category->term_id === $fields['promo_category'];
            //     });
            // }




            // Attach the data to each promo
            foreach ($promos as $promo) {
                $promo->promo_title = carbon_get_post_meta($promo->ID, 'promo_title');
                $promo->promo_content = carbon_get_post_meta($promo->ID, 'promo_content');
                $promo->promo_link_text = carbon_get_post_meta($promo->ID, 'promo_link_text');
                $promo->promo_start_date = carbon_get_post_meta($promo->ID, 'promo_start_date');
                $promo->promo_end_date = carbon_get_post_meta($promo->ID, 'promo_end_date');
                $promo->promo_category = get_the_terms($promo->ID, 'promo_category');
                $promo->mobile_promo_image = carbon_get_post_meta($promo->ID, 'mobile_promo_image');
                $promo->desktop_promo_image_type = carbon_get_post_meta($promo->ID, 'desktop_promo_image_type');
                $promo->full_desktop_promo_image = carbon_get_post_meta($promo->ID, 'full_desktop_promo_image');
                $promo->half_desktop_promo_image = carbon_get_post_meta($promo->ID, 'half_desktop_promo_image');
            }
            ob_start();
        ?>

        <div class="lpd-promo-list">
            <?php foreach ($promos as $promo) {
                $image = [
                    'desktop' => [
                        'full' => wp_get_attachment_image($promo->full_desktop_promo_image, 'full'),
                        'half' => wp_get_attachment_image($promo->half_desktop_promo_image, 'full'),
                    ],
                    'mobile' => wp_get_attachment_image($promo->mobile_promo_image, 'full'),
                ];

                $detect = new MobileDetect;
                if ($detect->isMobile()) {
                    $image = $image['mobile'];
                } else {
                    if ($promo->desktop_promo_image_type === 'full') {
                        $image =  $image['desktop']['full'];
                    } else {
                        $image =  $image['desktop']['half'];
                    }
                }
                ?>
                <!-- <h3><?php echo $promo->post_title; ?></h3> -->
                <?php
                if ($image) {
                    echo $image;
                }
                ?>

                <p><?php echo $promo->promo_content; ?></p>
                <a href="<?php echo get_permalink($promo->ID); ?>"><?php echo $promo->promo_link_text; ?></a>

            <?php } ?>
        </div>
        


                <?php
                $output = ob_get_clean();
                echo $output;
            } );
    }

}