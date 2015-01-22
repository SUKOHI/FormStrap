<?php namespace Sukohi\FormStrap;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class FormStrap {

	private $_type, $_name = '', $_value, $_label, $_view, $_url, $_text, $_cancel_position, 
				$_separator, $_input_class, $_group_class, $_content_class = '';
	private $_label_options, $_options, $_cancel_options, $_values, $_checked_values, $_icons, 
				$_attribute_names, $_alert_icons, $_option_labels = array();
	private $_alert_levels = array('danger', 'warning', 'success', 'info');
	private $_submit_flag, $_alert_dismissable = false;
	
	public function __toString() {
		
        return $this->render();
        
    }
	
	public function label($label, $options = array()) {

		$this->_label = $label;
		$this->_label_options = $options;
		$this->_attribute_names[$this->_name] = $label;
		return $this;
		
	}
	
	public function icon($tag, $position) {
		
		if(in_array($position, array('right', 'left'))) {
			
			$this->_icons[$position] = $tag;
			
		}
		
		return $this;
		
	}
	
	public function css($mode, $class) {

		if($mode == 'input') {
			
			$this->_input_class = $class;
			
		} elseif($mode == 'group') {
			
			$this->_group_class = $class;
			
		} elseif($mode == 'content') {
			
			$this->_content_class = $class;
			
		} elseif($mode == 'label') {
			
			$this->_label_options['class'] = $class;
			
		}
		return $this;
		
	}

	public function attributeNames($key = 'attribute_names', $additional_values=array()) {
	
		$attribute_names = array();
	
		if(!empty($this->_attribute_names)) {
		
			foreach ($this->_attribute_names as $attribute_key => $attribute_name) {
					
				$attribute_names[$key .'['. $attribute_key .']'] = $attribute_name;
					
			}
		
		}
	
		if(!empty($additional_values)) {
				
			foreach ($additional_values as $attribute_key => $attribute_name) {
					
				$attribute_names[$key .'['. $attribute_key .']'] = $attribute_name;
					
			}
				
		}
	
		return $this->hidden($attribute_names);
	
	}
	
	public function optionLabels($key = 'option_labels', $additional_values = array()) {
		
		$option_labels = array();
		
		foreach ($this->_option_labels as $label_key => $labels) {
		
			foreach ($labels as $value => $label) {
				
				$option_labels[$key .'['. $label_key .']['. $value .']'] = $label;
				
			}
		
		}
	
		if(!empty($additional_values)) {
				
			foreach ($additional_values as $label_key => $labels) {
					
				foreach ($labels as $value => $label) {
					
					$option_labels[$key .'['. $label_key .']['. $value .']'] = $label;
					
				}
					
			}
				
		}
		
		return $this->hidden($option_labels);
		
	}
	
	public function selectedOptionLabels($key, $option_labels = array()) {
		
		$returns = array();
		
		if(empty($option_labels)) {
			
			$option_labels = Input::get('option_labels');
			
		}
		
		$input_value = Input::get($key);
		
		if(!isset($option_labels[$key]) || empty($input_value)) {
			
			return $returns;
				
		}
		
		$labels = $option_labels[$key];
		$values = (is_array(Input::get($key))) ? Input::get($key) : array(Input::get($key));

		foreach ($values as $value) {
			
			if(isset($labels[$value])) {
				
				$returns[$value] = $labels[$value];
				
			}
			
		}
		
		return $returns;
		
	}
	
	public function resetAttributeNames() {
		
		$this->_attribute_names = array();
		
	}
	
	public function text($name, $value = null, $options = array()) {

		$this->_type = 'text';
		$this->_name = $name;
		$this->_value = $value;
		$this->_options = $options;
		return $this;
		
	}
	
	public function password($name, $options = array()) {

		$this->_type = 'password';
		$this->_name = $name;
		$this->_options = $options;
		return $this;
		
	}
	
	public function textarea($name, $value = null, $options = array()) {

		$this->_type = 'textarea';
		$this->_name = $name;
		$this->_value = $value;
		$this->_options = $options;
		return $this;
		
	}
	
	public function view($name, $view, $parameters = array()) {

		$this->_type = 'view';
		$this->_name = $name;
		$this->_view = $view;
		$this->_options = $parameters;
		return $this;
		
	}
	
	public function radio($name, $values, $checked_value = null, $options = array(), $separator = '&nbsp;') {
		
		$this->_type = 'radio';
		$this->_name = $name;
		$this->_values = $values;
		$this->_checked_values = array($checked_value);
		$this->_options = $options;
		$this->_separator = $separator;
		$this->_option_labels[$name] = $values;
		return $this;
		
	}
	
	public function checkbox($name, $values, $checked_values = array(), $options = array(), $separator = '&nbsp;') {
		
		$this->_type = 'checkbox';
		$this->_name = $name;
		$this->_values = $values;
		$this->_checked_values = $checked_values;
		$this->_options = $options;
		$this->_separator = $separator;
		$this->_option_labels[$name] = $values;
		return $this;
		
	}
	
	public function select($name, $values, $selected_value, $options = array()) {
		
		$this->_type = 'select';
		$this->_name = $name;
		$this->_values = $values;
		$this->_checked_values = array($selected_value);
		$this->_options = $options;
		$this->_option_labels[$name] = $values;
		return $this;
		
	}
	
	public function file($name, $options = array()) {
		
		$this->_type = 'file';
		$this->_name = $name;
		$this->_options = $options;
		return $this;
		
	}
	
	public function multiple() {
		
		$this->_options['multiple'] = true;
		return $this;
		
	}
	
	public function accept($accept_type) {
		
		$this->_options['accept'] = $accept_type;
		return $this;
		
	}
	
	public function hidden($values) {
		
		$this->_type = 'hidden';
		$this->_values = $values;
		return $this;
		
	}
	
	public function submit($value = null, $options = array()) {

		$options['type'] = 'submit';
		$this->_type = 'submit';
		$this->_submit_flag = true;
		$this->_value = $value;
		$this->_options = $options;
		return $this;
		
	}
	
	public function cancel($url, $text='Cancel', $options = array()) {
		
		$this->_cancel_position = 'left';
		$this->_url = $url;
		$this->_text = $text;
		$this->_cancel_options = $options;
		return $this;
		
	}
	
	public function left() {
		
		$this->_cancel_position = 'left';
		return $this;
		
	}
	
	public function right() {
		
		$this->_cancel_position = 'right';
		return $this;
		
	}
	
	public function alert($level = '', $dismissable = true) {
		
		$this->_type = 'alert';
		$this->_alert_dismissable = $dismissable;
		return $this;
		
	}
	
	public function hasAlert($lavels = array()) {
		
		$check_levels = (empty($lavels)) ? $this->_alert_levels : $lavels;
		
		foreach ($check_levels as $level) {
			
			if(!empty(Session::get($level))) {
				
				return true;
				
			}
			
		}
		
		return false;
		
	}
	
	public function icons($icons) {
		
		$this->_alert_icons = $icons;
		return $this;
		
	}
	
	protected function setView($view) {
		
		if(empty($this->_view)) $this->_view = $view;
		
	}
	
	protected function viewParameters() {
		
		if(empty($this->_input_class) 
				&& !isset($this->_options['class']) 
				&& !in_array($this->_type, array('radio', 'checkbox', 'submit', 'file', 'hidden', 'view'))) {
			
			$this->_options['class'] = 'form-control';
			
		} elseif(!empty($this->_input_class)) {
			
			$this->_options['class'] = $this->_input_class;
			
		}
		
		return array(
			'type' => $this->_type, 
			'name' => $this->_name, 
			'value' => $this->_value, 
			'options' => $this->_options, 
			'label' => $this->_label, 
			'label_options' => $this->_label_options, 
			'view' => $this->_view, 
			'url' => $this->_url, 
			'text' => $this->_text, 
			'cancel_position' => $this->_cancel_position, 
			'cancel_options' => $this->_cancel_options, 
			'values' => $this->_values, 
			'checked_values' => $this->_checked_values, 
			'separator' => $this->_separator, 
			'group_class' => $this->_group_class, 
			'content_class' => $this->_content_class, 
			'submit_flag' => $this->_submit_flag, 
			'icons' => $this->_icons
		);
		
	}
	
	protected function render() {
		
		$render = '';
		
		if($this->_type == 'alert') {

			$render = $this->alertRender();
			
		} else {
			
			$render = View::make('packages.sukohi.form-strap.main', $this->viewParameters())->render();
			
		}
		
		$this->_type = $this->_name = $this->_value = $this->_label = $this->_view = $this->_url = $this->_text
				 	= $this->_cancel_position = $this->_input_class = $this->_group_class = $this->_content_class = '';
		$this->_options = $this->_label_options = $this->_view_options = $this->_cancel_options = $this->_icons = array();
		$this->_submit_flag = false;
		return $render;
		
	}
	
	private function alertRender() {
		
		$render = '';
			
		if(empty($level)) {
				
			$levels = $this->_alert_levels;
				
		} else if(!is_array($level)) {
				
			$levels = array($level);
				
		} else {
				
			$levels = $level;
				
		}
		
		foreach ($levels as $level) {
		
			if(in_array($level, $this->_alert_levels)) {
					
				$render .= View::make('packages.sukohi.form-strap.alert', array(
						'level' => $level,
						'dismissable' => $this->_alert_dismissable, 
						'icons' => $this->_alert_icons
				))->render();
					
			}
		
		}
			
		$this->_alert_icons = array();
		$this->_alert_dismissable = false;
		return $render;
		
	}
	
}