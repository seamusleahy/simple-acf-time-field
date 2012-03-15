<?php
/**
 * A really simple time field to use with the Advance Custom Fields plugin
 */
class Simple_ACF_Time_Field extends acf_Field
{

	function __construct($parent)	{
		// do not delete!
    	parent::__construct($parent);
    	
    	// set name / title
    	$this->name = 'simple_time_field'; // variable name (no spaces / special characters / etc)
      $this->title = __("Time",'acf'); // field label (Displayed in edit screens)
		
   	}

	
	/**
   * Create field option form
   *
   * Outputs the form fields for the options for the field.
   */
	function create_options($key, $field) {
		// defaults
		$field['time_format'] = isset($field['time_format']) ? $field['time_format'] : '';
    $field['24_hour'] = isset($field['24_hour']) ? $field['24_hour'] : false;
		
		?>    
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('24 hour time'); ?></label>
      </td>
      <td>
        <input type="checkbox" name="fields[<?php echo $key; ?>][24_hour]" value="1" <?php if($field['24_hour']) echo 'checked="checked"'; ?> />
      </td>
    </tr>
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('Output format'); ?></label>
        <p class="description"><?php _e('<a href="http://php.net/manual/en/function.date.php">See for Time section for replacement</a>.'); ?></p>
      </td>
      <td>
        <input type="input" name="fields[<?php echo $key; ?>][time_format]" value="<?php echo $field['time_format']; ?>" />
      </td>
    </tr>

		<?php
	}
		
	
	
  /**
   * Output the post edit form for the field.
   */
	function create_field($field)	{
		// vars
		$field['24_hour'] = isset($field['24_hour']) ? $field['24_hour'] : false;
    $hour = floor($field['value']/60);
    $minute = $field['value']%60;
    
		// html
    ?>
    <select name="<?php echo $field['name']; ?>[hour]">
      <?php 
      $start = $field['24_hour'] ? 0 : 1;
      $end = $field['24_hour'] ? 23: 12;
      for($i=$start; $i<=$end; ++$i) {
        echo '<option value="'.$i.'" '.($hour==$i || (!$field['24_hour'] && $hour%12==$i) ? 'selected="selected"' : '').'>'.$i.'</option>';
      }?>
    </select>
    :
    <select name="<?php echo $field['name']; ?>[minute]">
      <?php 
      for($i=0; $i<=59; ++$i) {
        printf( '<option value="%d" %s>%02d</option>', $i, ($minute==$i ? 'selected="selected"' : ''), $i);
      }?>
    </select>
    <?php if(!$field['24_hour']): ?>
    <select name="<?php echo $field['name']; ?>[meridiem]">
      <option value="am" <?php if($hour<12) echo 'selected="selected"'; ?>>AM</option>
      <option value="pm" <?php if($hour>=12) echo 'selected="selected"'; ?>>PM</option>
    </select>
    <?php endif;
	}
	
	

	
  /**
   * Include stylesheet
   */
	function admin_print_styles()	{
		$url = plugins_url('', SIMPLE_ACF_TIME_FIELD_FILE).'/simple-acf-time-field.css';
    wp_enqueue_style( 'simple-acf-time-field', $url);
	}

	
	/**
   * Process the post edit submit
   *
   * Convert the various fields into a single value (minutes since the start of the day) to save to the database.
   */
	function update_value($post_id, $field, $value) {
    $field['24_hour'] = isset($field['24_hour']) ? $field['24_hour'] : false;
		// do stuff with value
    // Convert the parts into minutes since the start of the day
    if($field['24_hour']) {
      $val = $value['hour'] * 60 + $value['minute'];
    } else {
      $val = ($value['hour']%12) * 60 + $value['minute'];
      if($value['meridiem'] == 'pm') {
        $val += 12*30;
      }
    }
    
		// save value
		parent::update_value($post_id, $field, $val);
	}
	
	
	
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*	- called from your template file when using the API functions (get_field, etc). 
	*	This function is useful if your field needs to format the returned value
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field){
		// get value
		$value = $this->get_value($post_id, $field);
		
		// format value
    $field['24_hour'] = isset($field['24_hour']) ? $field['24_hour'] : false;
		$field['time_format'] = isset($field['time_format']) ? $field['time_format'] : $field['24_hour'] ? 'G:i' : 'g:ia';
    
    $value = $this->format_minutes($value, $field['time_format']);
		// return value
		return $value;

	}
  
  
  
  /**
   * Format minutes into a time format
   *
   * @param $minutes integer - the minutes since the start of the day
   * @param $format string - the format with the replacement tokens
   *
   * @return string
   */
   function format_minutes($minutes, $format) {
    $hour = floor($minutes/60);
    $min = $minutes%60;
    
    $tokens = array(
      'a' => $hour < 12 ? 'am' : 'pm',
      'A' => $hour < 12 ? 'AM' : 'PM',
      'g' => $hour%12 + 1,
      'G' => $hour,
      'h' => sprintf('%02d', $hour%12 + 1),
      'H' => sprintf('%02d', $hour),
      'i' => sprintf('%02d', $min),
    );
    
    return str_replace(array_keys($tokens), $tokens, $format);
   }
	
}