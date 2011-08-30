<?php
/*
Plugin Name: Media Images Widget
Plugin URI: http://floriantobias.de/web-stuff/media-images-widget/
Description: You can select Images in your Media Gallery and this will be shown on your Widget-Position
Version: 0.9.2
Author: Florian Tobias
Author URI: http://floriantobias.de
*/
	
	class FT_Media_Images_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(false, 'Media Images Widget');
		}
		
		public function form($instance) {
			$query = new WP_Query(
				array(
					'post_status' => 'any',
					'post_type' => 'attachment',
					'post_mime_type' => array(
						'image/jpeg',
						'image/png',
						'image/gif'
					) ,
					'posts_per_page' => 1000
				)
			);

			$html_form = '<td style="padding:10px 2px;"><label style="line-height:25px;">
				<img src="%1$s" width="%2$s" height="%3$s" alt="%4$s" />
				<input type="checkbox" name="%5$s[]" value="%6$s" %7$s/>
			</label></td>';
			$row_form = '<tr style="background-color:%s">%s</tr>';
			$html_select = 'checked="checked"';

			$html_td = '';
			$html = '';
			$i = 1;
			$j = 1;

			while($query->have_posts()) {
				$query->the_post();
				
				$id = get_the_ID();
				$select = '';

				$thumb = wp_get_attachment_image_src($id, array(30,30000));

				if(is_array($instance['thumbs']) && in_array($id, $instance['thumbs'])) {
					$select = $html_select;
				}
				
                if(($i % 4) === 0) {
					if(($j % 2) === 0) {
                    	$color = '#eeeeee';
					} else {
						$color = 'white';
					}
					
					$html .= sprintf($row_form, $color, $html_td);
					$html_td = '';
					$j++;
				}

				$html_td .= sprintf(
					$html_form, 
					$thumb[0], 
					$thumb[1], 
					$thumb[2], 
					get_the_title(), 
					$this->get_field_name('thumbs'), 
					$id,
					$select
				);

				$i++;
			}
			
			$html_form = '<p>
				<label>Title:
					<input type="text" class="widefat" name="%s" value="%s" />
				</label>
			</p>
			<p>
				<label>Size of Thumbnails:<br/>
				<small>(x,y e.g.: 50,50)</small>
					<input type="text" class="widefat" name="%s" value="%s" />
				</label>
			</p>
			<p>
				<table>%s</table>
			</p>';

			printf(
				$html_form, 
				$this->get_field_name('title'), 
				$instance['title'],
				$this->get_field_name('size'),
				$instance['size'],
				$html
			);
		}
		
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;

			$instance['title'] = strip_tags($new_instance['title']);
            $instance['thumbs'] = $new_instance['thumbs'];
            $instance['size'] = strip_tags($new_instance['size']);
			
			return $instance;
		}
		
		public function widget($args, $instance) {
			extract($args);

			$html_form = '<li>
				<a href="%s">
					%s
				</a>
			</li>';
			$html = '';

			$size = explode(',', $instance['size']);
			$width = (int)trim($size[0]);
			$height = (int)trim($size[1]);

			if($width <= 0) {
				$width = 50;
			}
			
			if($height <= 0) {
				$height = 10000;
			}

			foreach($instance['thumbs'] as $id) {
				$thumb_image = wp_get_attachment_image($id, array($width, $height));
				$big_image = wp_get_attachment_image_src($id, 'full');
				
				$html .= sprintf(
					$html_form, 
					$big_image[0], 
					$thumb_image
				);

			}
			
			$title = apply_filters('widget_title', $instance['title']);
			if(!empty($title)) {
				$title = $before_title.$title.$after_title;
			}

			// Output the Widget
			printf('%s%s<ul>%s</ul>%s', $before_widget, $title, $html, $after_widget);
		}

		public function register() {
			register_widget(__CLASS__);
		}
	}
	
	add_action('widgets_init', array('FT_Media_Images_Widget', 'register'));
	wp_register_style('media-images-widget', plugins_url('ft-media-images-widget/style.css'));
	wp_enqueue_style('media-images-widget');
?>