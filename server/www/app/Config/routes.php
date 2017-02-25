<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	Router::connect('/', array('controller' => 'pages', 'action' => 'home'));
/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
 	Router::connect('/pages/contactus', array('controller' => 'pages', 'action' => 'contactus'));
 	Router::connect('/pages/ethicalclearance/*', array('controller' => 'pages', 'action' => 'ethicalclearance'));
 	Router::connect('/links', array('controller' => 'pages', 'action' => 'links'));
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'home'));
	
	Router::connect('/course/staffcsvupload', array('controller' => 'course', 'action' => 'staffcsvupload'));
	Router::connect('/course/staffcsv', array('controller' => 'course', 'action' => 'staffcsv'));
	Router::connect('/course/create', array('controller' => 'course', 'action' => 'create'));
	Router::connect('/course/refreshlist/*', array('controller' => 'course', 'action' => 'refreshlist'));
	Router::connect('/course/comparelists/*', array('controller' => 'course', 'action' => 'comparelists'));
	Router::connect('/course/admin/*', array('controller' => 'course', 'action' => 'admin'));
	Router::connect('/course/managestaff/*', array('controller' => 'course', 'action' => 'managestaff'));
    Router::connect('/course/managestudents/*', array('controller' => 'course', 'action' => 'managestudents'));
	Router::connect('/course/removestaff/*', array('controller' => 'course', 'action' => 'removestaff'));
    Router::connect('/course/changestaff/*', array('controller' => 'course', 'action' => 'changestaff'));
	Router::connect('/course/updateassign/*', array('controller' => 'course', 'action' => 'updateassign'));
	Router::connect('/submission/*', array('controller' => 'submission', 'action' => 'display'));
	Router::connect('/course/*', array('controller' => 'course', 'action' => 'display'));

/**
 * Load all plugin routes.  See the CakePlugin documentation on 
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
