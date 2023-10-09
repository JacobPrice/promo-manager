<?php

namespace LpdPromo\Controllers;

use Carbon_Fields\Field;
use Carbon_Fields\Container;
use LpdPromo\Og\PostController;
use LpdPromo\Models\PromoModel;

class PromoController extends PostController {
    public function __construct(PromoModel $promo_model) {
        parent::__construct($promo_model);
    }

    public function add_new_columns($columns)
    {
        $new_columns = [];
        $new_columns['cb'] = '<input type="checkbox" />';
        $new_columns['title'] = __('Title');
        $new_columns['status'] = __('Status');
        $new_columns['start_date'] = __('Start Date');
        $new_columns['end_date'] = __('End Date');
        $new_columns['promo_category'] = __('Promo Category');
        $new_columns['image'] = __('Image');
        $new_columns['date'] = __('Date');
        return array_merge($new_columns, $columns);
    }
    public function add_promo_css_variables_to_root() {
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

    public function manage_custom_columns($column, $post_id) {
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

    public function add_custom_fields() {
        Container::make('post_meta', 'Promo Settings')
        ->where('post_type', '=', 'lpd-promo')
        ->add_tab('Settings', [
            Field::make('checkbox', 'promo_not_public', 'Disable Individual Promo')
            ->set_help_text('Check this box to disable this promo from being accessible directly. This will still show in the promo block if it is utilized. If this is not checked the promo will utilize global settings.'),
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

    public function setup_schedule() {
        if (!wp_next_scheduled('lpd_check_expired_promos')) {
            wp_schedule_event(time(), 'hourly', 'lpd_check_expired_promos');
        }
    }
    public function check_promo_public_status()
    {
        if (is_singular($this->model->post_type)) {
            $post_id = get_the_ID();
            if(carbon_get_theme_option('promo_global_individual_pages_disabled')) {
                // Add noindex meta tag to prevent search engines from indexing the page
                echo '<meta name="robots" content="noindex">';
                wp_redirect(home_url());
                exit;
            }
            $promo_not_public = carbon_get_post_meta($post_id, 'promo_not_public');
            if ($promo_not_public) {
                // Add noindex meta tag to prevent search engines from indexing the page
                echo '<meta name="robots" content="noindex">';
                wp_redirect(home_url());
                exit;
            }
        }
    }
        public function handle_expired_promos()
    {
        $args = array(
            'post_type' => $this->model->post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                if (!$this->model::is_active($post_id)) {
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
}