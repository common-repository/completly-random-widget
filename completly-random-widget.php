<?php
/*
Plugin Name: Completely Random Widget
Plugin URI: http://x-inferno.com/computers/the-internet/wordpress-plugin-completely-random-widget
Description: Uses google images to pull a random image from the internet into your sidebar!
Author: Thomas Renck
Version: 2.1
Author URI: http://x-inferno.com/
*/
class CRW_widget extends WP_Widget {
    function CRW_widget() {
        parent::WP_Widget(false, $name = 'Completly Random [image] Widget');
    }

    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $show_caption = $instance['show_caption'];
        $show_footer = $instance['show_footer'];
        
        /*----Lookup image!----*/
        //Load an xml feed file (from mlia.com) to use as search seed
	$url = "http://feeds.feedburner.com/mlia";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, "http://toemat.com");
	$xmlraw = curl_exec($ch);
	curl_close($ch);
        
	//Parse xml file and take the first sentence from a random (0-8th) quote
	$xml = new SimpleXMLElement($xmlraw);
	$i = rand(0,8);
        $query = strtok($xml->channel->item[$i]->description, '<');
	$query = urlencode(strtok($query, '.'));
	$query = str_ireplace("Today","",$query); 

        //Build google images API url
	$url = "http://ajax.googleapis.com/ajax/services/search/images?" .
               "v=1.0&rsz=8&imgsz=medium|large|xlarge&q=$query";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, site_url());
	$body = curl_exec($ch);
	curl_close($ch);

	// now, process the JSON string returned by google images
	$json = json_decode($body);
	//Choose a random image (0-6th) from the returned images
	$i = rand(0,6);
	$img_url = $json->responseData->results[$i]->tbUrl;
	$context = $json->responseData->results[$i]->originalContextUrl;
	$img_title = $json->responseData->results[$i]->contentNoFormatting;
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                    <!-- Completely Random Widget by Thomas Renck 2011 -->        
                    <link href="<?php echo plugins_url("/completly-random-widget/style.css"); ?>" rel="stylesheet"/>
                    <div id="CRW_wrapper">  
                        <a href="<?php echo $context;?>" target="_blank">
                        <img src="<?php echo $img_url;?>"/>
                        </a>
                        <?php if($show_caption): ?>
                            <div class="CRW_caption">&OpenCurlyDoubleQuote;<?php echo $img_title?>&CloseCurlyDoubleQuote;</div>
                        <?php endif;?>
                        <?php if($show_footer): ?>
                            <div class="CRW_footer">
                                This uses Google Images to pull a completely random image from the web. Enjoy!
                            </div>
                        <?php endif; ?>
                    </div>
              <?php echo $after_widget; ?>
        <?php
    }

    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['other'] = strip_tags($new_instance['other']);
	$instance['show_caption'] = strip_tags($new_instance['show_caption']);
	$instance['show_footer'] = strip_tags($new_instance['show_footer']);
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        $show_caption = esc_attr($instance['show_caption']);
        $show_footer = esc_attr($instance['show_footer']);
        
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
         </p>
         <p>
          <input class="" id="<?php echo $this->get_field_id('show_caption'); ?>" 
                 name="<?php echo $this->get_field_name('show_caption'); ?>" 
                 type="checkbox" value="1"
                 <?php echo $show_caption? "checked":""?>/>
          <label for="<?php echo $this->get_field_id('show_caption'); ?>">Show image caption</label>
         </p>
         <p>
          <input class="" id="<?php echo $this->get_field_id('show_footer'); ?>" 
                 name="<?php echo $this->get_field_name('show_footer'); ?>" 
                 type="checkbox" value="1"
                 <?php echo $show_footer? "checked":""?>/>
          <label for="<?php echo $this->get_field_id('show_footer'); ?>">Show widget footer text</label>
         </p>
        <?php 
    }

} 

// register widget
add_action('widgets_init', create_function('', 'return register_widget("CRW_widget");'));

?>