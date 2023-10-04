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
        add_filter('manage_lpd-promo_posts_columns', [__CLASS__, 'add_new_columns']);
        add_action('carbon_fields_register_fields', [__CLASS__, 'register_custom_fields']);
        add_action('manage_lpd-promo_posts_custom_column', [__CLASS__, 'manage_custom_columns'], 10, 2);
        add_action('wp', [__CLASS__, 'setup_schedule']);
        add_action('lpd_check_expired_promos', [__CLASS__, 'handle_expired_promos']);
        add_action('template_redirect', [__CLASS__, 'check_promo_public_status']);
        add_action('carbon_fields_register_fields', [__CLASS__, 'make_promo_block']);
        add_action('carbon_fields_register_fields', [__CLASS__, 'make_promo_settings']);
        // put the promo_settings in the menu
        // add_action('admin_menu', [__CLASS__, 'add_promo_settings_to_menu'], 999);
        add_action('admin_head', [__CLASS__, 'print_css_to_head']);
        add_action('wp_head', [__CLASS__, 'print_css_to_head']);


    }
    public static function print_css_to_head() {
       function field_exists($field, $name) {
        $field = carbon_get_theme_option($field);
        if($field && $field != '') {
            if(is_numeric($field)) {
                $field = $field . 'px';
            }
            return "$name: $field;";
          }
        };
        $promo_theme_color = field_exists('promo_theme_color', '--swiper-theme-color');
        $promo_theme_color_secondary = field_exists('promo_theme_color_secondary', '--swiper-theme-color-accent');
        $promo_theme_navigation_size = field_exists('promo_theme_navigation_size', '--swiper-navigation-size');
        $promo_theme_navigation_sides_offset = field_exists('promo_theme_navigation_sides_offset', '--swiper-navigation-sides-offset');
        $styles = <<<EOT
        <style>
            :root {
                $promo_theme_color
                $promo_theme_color_secondary
                $promo_theme_navigation_size
                $promo_theme_navigation_sides_offset
           }
        </style>
        EOT;
        echo $styles;
    }
    public static function add_promo_settings_to_menu() {
        add_submenu_page(
            'edit.php?post_type=lpd-promo',
            'Promo Settings',
            'Promo Settings',
            'manage_options',
            'lpd_promo_settings',
            function() {
                echo '<div class="wrap">';
                echo '<h1>Promo Settings</h1>';
                echo '<p>These settings are used globally for promos.</p>';
                echo '</div>';
                // create settings page with carbon fields here
                \Carbon_Fields\Carbon_Fields::boot();

                // add the settings container
                Container::make('theme_options', 'Promo Settings')
                ->add_fields([
                    Field::make('text', 'promo_link_text', 'Promo Link Text')
                    ->set_help_text('This is the text that will be displayed on the promo link/button.')
                    ->set_width(50),
                    Field::make('text', 'promo_link_external', 'Promo Link')
                    ->set_help_text('This is the link that will be used for the promo.')
                    ->set_width(50),
                ])
                ->set_page_parent('edit.php?post_type=lpd-promo')
                ->add_tab('Promo Settings', [
                    Field::make('text', 'promo_link_text', 'Promo Link Text')
                    ->set_help_text('This is the text that will be displayed on the promo link/button.')
                    ->set_width(50),
                    Field::make('text', 'promo_link_external', 'Promo Link')
                    ->set_help_text('This is the link that will be used for the promo.')
                    ->set_width(50),
                ]);

                    // promo_global_individual_pages_disabled
                    // promo_global_link_is_external
                    // promo_global_link_text
                    // promo_global_link_external
                    

                $promo_settings = \Carbon_Fields\Container\Container::make('theme_options', 'Promo Settings')
    
                ->set_page_parent('edit.php?post_type=lpd-promo')
                ->add_fields([
                    Field::make('text', 'promo_link_text', 'Promo Link Text')
                    ->set_help_text('This is the text that will be displayed on the promo link/button.')
                    ->set_width(50),
                    Field::make('text', 'promo_link_external', 'Promo Link')
                    ->set_help_text('This is the link that will be used for the promo.')
                    ->set_width(50)
                    ->set_attribute('type', 'url')
                ]);
            }
        );
        
    }
    public static function check_promo_public_status()
    {
        if (is_singular(self::POST_TYPE)) {
            $post_id = get_the_ID();
            $promo_not_public = carbon_get_post_meta($post_id, 'promo_not_public');
            if ($promo_not_public) {
                // Add noindex meta tag to prevent search engines from indexing the page
                echo '<meta name="robots" content="noindex">';
                wp_redirect(home_url());
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
            ->add_tab('Settings', [
                Field::make('checkbox', 'promo_not_public', 'Disable Individual Promo')
                ->set_help_text('Check this box to disable this promo from being accessible directly. This will also add a noindex meta tag to prevent search engines from indexing the page. This will still show in the promo block if it is utilized.'),
                Field::make('checkbox', 'promo_customize_link', 'Customize The Promo Link')
                ->set_help_text('Check this box to customize the promo link. If this is not checked the promo will utilize global settings.'),
                Field::make('radio', 'desktop_promo_image_type', 'Desktop Promo Image Type')
                ->add_options([
                    'full' => 'Full Width Image',
                    'half' => 'Half Width Image',
                ])
                ->set_help_text('Select the type of desktop image you want to use. If you select half width, content is expected to be added to the right side of the image.')
                ->set_width(10),
                Field::make('checkbox', 'promo_uses_custom_content', 'Utilize Custom Content Instead Of The Promo Excerpt')
                ->set_help_text('Check this box to utilize the custom content instead of the promo excerpt. The promo excerpt is generated from the promo content in the above editor.'),
                // Field::make('association', 'promo_on_page', 'Show Promo On Page')
                // ->set_help_text('Select the page you want to show this promo on. NOTE: If the Promo Block is utilized it will still show in the block. Utilizing this will add the promo to the bottom of the page in a slider if multiple promos are selected.')
                //     ->set_types([
                //         [
                //             'type' => 'post',
                //             'post_type' => 'page',
                //         ],
                //     ])
            ])
            ->add_tab('Dates', [
                Field::make('date', 'promo_start_date', 'Promo Start Date')
                    ->set_width(50)
                    ->set_required(true),
                Field::make('date', 'promo_end_date', 'Promo End Date')
                    ->set_width(50)
                    ->set_required(true),
            ])
            ->add_tab('Images', [
                Field::make('image', 'mobile_promo_image', 'Mobile Promo Image')
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
                Field::make('text', 'promo_link_text', 'Promo Link Text')
                ->set_help_text('This is the text that will be displayed on the promo link/button. This option overrides the global promo link text.')
                ->set_conditional_logic([
                    [
                        'field' => 'promo_customize_link',
                        'value' => true,
                    ]])
                    ->set_width(50),
                Field::make('text', 'promo_link_external', 'Promo Link')
                ->set_help_text('This is the link that will be used for the promo. This option overrides the global promo link.')
                ->set_conditional_logic([
                    [
                        'field' => 'promo_customize_link',
                        'value' => true,
                    ]])
                    ->set_width(50),

                Field::make('rich_text', 'promo_content', 'Promo Content')
                    ->set_width(100)
                    ->set_conditional_logic([
                        [
                            'field' => 'promo_uses_custom_content',
                            'value' => false,
                        ],
                    ])
                    ->set_help_text('This is the content that will be displayed on the promo. This option overrides the use of the promo excerpt.'),
                    Field::make( 'separator', 'crb_separator', __( 'No Content Can Be Added If The Promo Is Using The Promo Excerpt Setting, See The Settings Tab.' ) )
                    ->set_conditional_logic([
                        [
                            'field' => 'promo_uses_custom_content',
                            'value' => true,
                        ],
                    ]),
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
    public static function is_promo_active($post_id)
    {
        $start_date = carbon_get_post_meta($post_id, 'promo_start_date');
        $end_date = carbon_get_post_meta($post_id, 'promo_end_date');
        $now = new \DateTime();
        $now = $now->format('Y-m-d');

        return ($now >= $start_date && $now <= $end_date);
    }
    public static function get_active_promos()
    {
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
    public static function handle_expired_promos()
    {
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
    public static function setup_schedule()
    {
        if (!wp_next_scheduled('lpd_check_expired_promos')) {
            wp_schedule_event(time(), 'hourly', 'lpd_check_expired_promos');
        }
    }

    public static function manage_custom_columns($column, $post_id)
    {
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
    public static function category_options_for_promo_categories()
    {
        // set the options for promo_category
        $category_options = [];
        $categories = get_terms(
            array(
                'taxonomy' => 'promo_category',
                // replace with your taxonomy name
                'hide_empty' => false,
                // set to true if you only want terms with posts
            )
        );

        foreach ($categories as $term) {
            $category_options[$term->term_id] = str_replace('&amp;', '&', $term->name); // Note the change from $term->id to $term->term_id
        }

        return $category_options;
    }


    public static function make_promo_block()
    {
        $category_options = self::category_options_for_promo_categories();
        Block::make(__('Promo Block'))
            ->set_description('A block that displays active promos')
            ->add_fields([
                Field::make('select', 'promo_layout', 'Promo Layout')
                    ->add_options([
                        'list' => 'List',
                        'slider' => 'Slider',
                    ])
                    ->set_width(50)
                    ->set_default_value('list'),
                Field::make('select', 'promo_type', 'Promo Type')
                    ->add_options([
                        'all' => 'All',
                        'category' => 'Category',
                    ])
                    ->set_width(50)
                    ->set_default_value('all'),
                Field::make('select', 'promo_category', 'Promo Category')
                    ->add_options($category_options)
                    ->set_default_value(array_keys($category_options)[0])
                    ->set_width(50)
                    ->set_conditional_logic([
                        [
                            'field' => 'promo_type',
                            'value' => 'category',
                        ],
                    ])
            ])
            ->set_icon('megaphone')
            ->set_mode('preview')
            ->set_render_callback(
                function ($fields, $attributes, $inner_blocks) {
                    $promos = self::get_active_promos();
                    if ($fields['promo_type'] === 'category') {
                        $promos = array_filter($promos, function ($promo) use ($fields) {
                            $promo_categories = get_the_terms($promo->ID, 'promo_category');
                            if (is_array($promo_categories)) {
                                foreach ($promo_categories as $category) {
                                    if ($category->term_id === intval($fields['promo_category'])) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        });
                    }
                    
                    $detect = new MobileDetect;
                    $is_mobile = $detect->isMobile();
                    foreach ($promos as $promo) {
                        $promo->promo_title = carbon_get_post_meta($promo->ID, 'promo_title');
                        $promo->promo_content = carbon_get_post_meta($promo->ID, 'promo_content');
                        $promo->promo_link_text = carbon_get_post_meta($promo->ID, 'promo_link_text');
                        $promo->promo_start_date = carbon_get_post_meta($promo->ID, 'promo_start_date');
                        $promo->promo_end_date = carbon_get_post_meta($promo->ID, 'promo_end_date');
                        $promo->promo_category = get_the_terms($promo->ID, 'promo_category');
                        $promo->promo_desktop_image_type = carbon_get_post_meta($promo->ID, 'desktop_promo_image_type');
                        $promo->images = [
                            'mobile' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'mobile_promo_image'), 'full'),
                            'desktop' => [
                                'full' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'full_desktop_promo_image'), 'full'),
                                'half' => wp_get_attachment_image(carbon_get_post_meta($promo->ID, 'half_desktop_promo_image'), 'full'),
                            ],
                        ];
                    }

                    // get theme directory
                    $theme = wp_get_theme();
                    $theme_dir = $theme->get_stylesheet_directory();
                    if (file_exists($theme_dir . '/promo-block.twig')) {
                    $template = $theme_dir . '/promo-block.twig';
                    } else {
                    $template = plugin_dir_path(__DIR__) . 'views/blocks/promo-block.twig';
                    }
                    // include the template with the promos
                    Timber::render(
                        $template,
                        [
                            'promos' => $promos,
                            'promo_layout' => $fields['promo_layout'],
                            'is_mobile' => $is_mobile,
                        ]
                    );
                }
            );
    }

    public static function make_promo_settings() {
        Container::make('theme_options', 'Promo Settings')
        ->add_tab('Default Settings', [
            Field::make('checkbox', 'promo_global_individual_pages_disabled', 'Disable Individual Promo Pages')
            ->set_help_text('Check this box to disable individual promo pages. This can be overridden on individual promos.'),
            Field::make('checkbox', 'promo_global_link_is_external', 'Link The Promos Globally To A Specific Link')
            ->set_help_text('Check this box to link all promos to a specific link. This is useful if individual promo pages are not needed. This can be overridden on individual promos.'),
            Field::make('text', 'promo_global_link_text', 'Promo Global Link Text')
            ->set_help_text('This is the text that will be displayed on the promo link/button.')
            ->set_width(50)
            ->set_conditional_logic([
                [
                    'field' => 'promo_global_link_is_external',
                    'value' => true,
                ],
            ]),
            Field::make('text', 'promo_global_link_external', 'Promo Global Link External')
            ->set_help_text('This is the link that will be used for the promo.')
            ->set_width(50)
            ->set_attribute('type', 'url')
            ->set_conditional_logic([
                [
                    'field' => 'promo_global_link_is_external',
                    'value' => true,
                ],
            ]),
        ])
        ->add_tab('Custom Styles',[
            Field::make('color', 'promo_theme_color', 'Promo Theme Color Primary')
            ->set_help_text('This colors the following: Link Background, Next/Prev Arrows, Active Slide Dot')
            ->set_width(25),

            Field::make('color', 'promo_theme_color_secondary', 'Promo Theme Color Secondary')
            ->set_help_text('This colors the following: Link Text, Next/Prev Arrows Hover, Inactive Slide Dot')
            ->set_width(25),
            Field::make('text', 'promo_theme_navigation_size', 'Promo Theme Navigation Size')
            ->set_help_text('This sets the size of the next/prev arrows and the slide dots. If left blank it will default to 20px.')
            ->set_width(25)
            ->set_attribute('type', 'number'),
            Field::make('text', 'promo_theme_navigation_sides_offset', 'Promo Theme Navigation Sides Offset')
            ->set_help_text('This sets the offset of the next/prev arrows. If left blank it will default to 10px.')
            ->set_width(25)
            ->set_attribute('type', 'number'),
        ])
        ->set_page_parent('edit.php?post_type=lpd-promo');
    }
}