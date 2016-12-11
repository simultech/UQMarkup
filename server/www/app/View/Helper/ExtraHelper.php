<?php
/* /app/View/Helper/LinkHelper.php */
App::uses('AppHelper', 'View/Helper');

class ExtraHelper extends AppHelper {
    
    // Function to calculate square of value - mean
	function sd_square($x, $mean) { return pow($x - $mean,2); }

	// Function to calculate standard deviation (uses sd_square)    
	public function sd($array) {
		// square root of sum of squares devided by N-1
		$array = array_values($array);
/* 		print_r($array); */
		return sqrt(array_sum(array_map(array($this,"sd_square"), $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}
    
}