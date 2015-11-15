<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'没六儿',
	'defaultController' => 'posts/index',    //change to what i want

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
	        'application.classes.*',
		'application.models.*',
		'application.controllers.*',
		'application.components.*',
        	'application.modules.*',
        	'application.extensions.*',
        	'ext.yii-mail.YiiMailMessage',
        	'ext.PHPDomParser.*',
        	'ext.imperavi-redactor-widget.ImperaviRedactorWidget',
		'ext.segment_analytics.lib.*',
		'ext.s3.*',
	        'application.classes.*',
	),

    	'aliases' => array(
        	'Segment' => 'ext.segment_analytics.lib.Segment',
        	'Analytics' => 'classes.Analytics'
    	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool

        
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'86626728',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('*', '::1'),
		),
        
	),

	// application components
	'components'=>array(

/*
        'cache'=>array(
            'class'=>'ext.redis.CRedisCache',
            //if you dont set up the servers options it will use the default one
            //"host=>'127.0.0.1',port=>6379"
            'servers'=>array(
                array(
                    'host'=>'127.0.0.1',
                    'port'=>6379,
                ),
            ),
        ),
*/


	's3'=>array(
       		'class'=>'ext.s3.ES3',
        	'aKey'=>'AKIAJ4LRRVNFL5JNIWOA', 	//access key
        	'sKey'=>'VJgfwqw8MmmW8Sn96rdl5nXY5lWjikgpg8wdoGmO',	//secret key
    	),

	'session' => array(
    		'class' => 'system.web.CDbHttpSession',
    		'autoStart' => true,
    		'connectionID' => 'db',
    		'sessionTableName' => 'tbl_session',
    		'autoCreateSessionTable' => false    // for performance reasons
	),

        'clientScript'=>array(

            //'class'=>'ext.minScript.components.ExtMinScript',

            'packages'=>array(

                'jquery'=>false,

            ),
        ),

        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'smtp',
            'transportOptions'=>array(
                'host'=>'smtp.mailgun.org',
                'encryption'=>'ssl',
                'username'=>'postmaster@meiliuer.com',
                'password'=>'78ef396786f06b44294cd13d69fb81e7',
                'port'=>465,
            ),
            'viewPath' => 'application.views.mail',
            'logging' => false,
            'dryRun' => false,
        ),

        'user'=>array(
            'class'=>'CWebUser',
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            'loginUrl' => array('/site/login'),
            'autoUpdateFlash' => false,
        ),

        'citygeoip' => array(
            'class' => 'application.extensions.geoip.CityGeoIP',
            // specify filename location for the corresponding database
            'filename' => '/var/www/html/webapp/geoip/GeoIPCity.dat',
            // Choose MEMORY_CACHE or STANDARD mode
            'mode' => 'MEMORY_CACHE',
        ),


        'geoip' => array(
            'class' => 'application.extensions.geoip.CGeoIP',
            // specify filename location for the corresponding database
            'filename' => '/var/www/html/webapp/geoip/GeoIP.dat',
            // Choose MEMORY_CACHE or STANDARD mode
            'mode' => 'MEMORY_CACHE',
        ),

		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
            		'showScriptName'=>false,
			'caseSensitive'=>false,  
			'routeVar'=>'route',

			'rules'=>array(

                                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                		'<module:\w+><controller:\w+>/<id:\d+>'=>'<module><controller>/view',
                		'<module:\w+><controller:\w+>/<action:\w+>/<id:\d+>'=>'<module><controller>/<action>',
                		'<module:\w+><controller:\w+>/<action:\w+>'=>'<module><controller>/<action>',

				'submit'=>'/posts/create',
				'post'=>'/posts/create?post=1',
				'/'=>'/posts/index',

				'funny'=>'/posts/index/category_id/1',
				'news'=>'/posts/index/category_id/2',
				'tech'=>'/posts/index/category_id/3',
				'ama'=>'/posts/index/category_id/4',
				'other'=>'/posts/index/category_id/30',

			),
		),

		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				
/*
				array(
					'class'=>'CWebLogRoute',
				),
*/
				
				
			),
		),

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'url'=>'http://meiliuer.com', //update to hard code domain
		'adminEmail'=>'jimmyzhong@meiliuer.com',
	),
);
