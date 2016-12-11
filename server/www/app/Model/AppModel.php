<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	function checkUnique($data, $fields) { 
		if (!is_array($fields)) { 
        	$fields = array($fields); 
        } 
        foreach($fields as $key) { 
         	$tmp[$key] = $this->data[$this->name][$key]; 
        } 
        if (isset($this->data[$this->name][$this->primaryKey])) { 
        	$tmp[$this->primaryKey] = "<>".$this->data[$this->name][$this->primaryKey];
        } 
        return $this->isUnique($tmp, false); 
    }
}
