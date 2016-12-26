<?php
/*
Plugin Name: WATCHDOG
Plugin URI: https://github.com/theWEBDOGS/watchdog
Description: WEBDOGS Version Support
Version: 1.0.1
Author: WEBDOGS Support Team
Author URI: http://WEBDOGS.COM
License: GPLv2
*/

define( 'WATCHDOG_DIR', dirname( __FILE__ ) );
if(!function_exists( 'WEBDOGS_LATEST_VERSION' )) { 
if( file_exists( WATCHDOG_DIR . '/watchdog/plugin-update-checker.php' ) ) {
require_once WATCHDOG_DIR . '/watchdog/plugin-update-checker.php';
$webdogs_github_checker = PucFactory::getLatestClassVersion( 'PucGitHubChecker' );
$GLOBALS['webdogs_plugin_updates'] = new $webdogs_github_checker( 'https://github.com/theWEBDOGS/webdogs-support/', WP_PLUGIN_DIR . '/webdogs-support/webdogs-support.php', 'master'); }

function WEBDOGS_LATEST_VERSION() { $webdogs_latest_version = '2.0.0';
    if( defined( 'WEBDOGS_LATEST_VERSION' )) { return WEBDOGS_LATEST_VERSION; }
elseif( isset( $GLOBALS['webdogs_plugin_updates'] ) ) { $webdogs_plugin_release = $GLOBALS['webdogs_plugin_updates']->getUpdate();
    if(!is_null( $webdogs_plugin_release )) { $webdogs_latest_version = $webdogs_plugin_release->version; } }
elseif( defined( 'WEBDOGS_VERSION' )) { $webdogs_latest_version = WEBDOGS_VERSION; } 
elseif( file_exists( WP_PLUGIN_DIR . '/webdogs-support/webdogs-support.php' ) ) { 
           $webdogs_plugin_data = get_file_data( WP_PLUGIN_DIR . '/webdogs-support/webdogs-support.php', array( 'Version' => 'Version' ) );
        $webdogs_latest_version = $webdogs_plugin_data['Version']; }
 return $webdogs_latest_version; } } 

if( file_exists( WP_PLUGIN_DIR . '/webdogs-support/webdogs-support.php' ) ) { 
    require_once WP_PLUGIN_DIR . '/webdogs-support/webdogs-support.php'; } /*

 __      __  _________________________   ___ ___________   ________    ________ 
/  \    /  \/  _  \__    ___/\_   ___ \ /   |   \______ \  \_____  \  /  _____/ 
\   \/\/   /  /_\  \|    |   /    \  \//    ~    \    |  \  /   |   \/   \  ___ 
 \        /    |    \    |   \     \___\    Y    /    `   \/    |    \    \_\  \
  \__/\  /\____|__  /____|    \______  /\___|_  /_______  /\_______  /\______  /
       \/         \/                 \/       \/        \/         \/        \/ 

                              ZZZZZZZZZZZZZZZZZZZZ                              
                         ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ                         
                      ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ                      
                   ZZZZZZZZZZZZI...............,ZZZZZZZZZZZZ                    
                 ZZZZZZZZZI..........................ZZZZZZZZZZ                 
              ZZZZZZZZZ~................................ZZZZZZZZZ               
            ZZZZZZZZ$.....................................:ZZZZZZZZ             
           ZZZZZZZ+...............$Z............ZZ...........ZZZZZZZZ           
         ZZZZZZZI.................ZZZ..........ZZZ,............ZZZZZZZ          
        ZZZZZZZ...................ZZZ?........=ZZZ=.............?ZZZZZZ         
       ZZZZZZI....................~ZZZ,.......$ZZZ?...............ZZZZZZ       
      ZZZZZZ.......................ZZZZ.......ZZZZZ................ZZZZZZ       
     ZZZZZZ........................,ZZZZ......ZZZZZ.................IZZZZZ      
    ZZZZZZ..........................ZZZZZ....,ZZZZZZ.................7ZZZZZ     
   ZZZZZZ...........................ZZZZZ$...ZZZZZZZZ.................IZZZZZ    
  ZZZZZZ,..........................:ZZZZZZI.~ZZZZZZZZZ.................ZZZZZZ   
  ZZZZZZ..........................$ZZZZZZZZZZZZZZZZZZZZ.................ZZZZZ   
 ZZZZZZ........................,ZZZZZ+ZZZZZZZZZZZZZZZZZ~................+ZZZZZ  
 ZZZZZ~.....................?ZZZZ7.$ZZZZZZZ=:ZZZZZZZZZZZ.................ZZZZZ  
ZZZZZZ...................+ZZZZZ~.ZZZZ+........ZZZZZZZZZZ.................7ZZZZZ 
ZZZZZ7..................ZZZZZ~..ZZZZZ.........=ZZZZZZZZ?..................ZZZZZ 
ZZZZZ..................ZZZZZ..ZZZZZZZI.........$ZZZZZZZ:..................ZZZZZZ
ZZZZZ.................$ZZZZ..ZZZZZZZZZ7........ZZZZZZZZ~..................7ZZZZZ
ZZZZZ.................$ZZZ.~ZZZZZZZZZZZZ......,ZZZZZZZZZ..................?ZZZZZ
ZZZZZ.................+ZZ,ZZZZZZZZZZZZZZZ?.....ZZZZZZZZZ..................=ZZZZZ
ZZZZZ............IZZZZZZZZZZZZZZZZZZZZZZZZ.....ZZZZZZZZZ..................=ZZZZZ
ZZZZZ.........=ZZZZZZZ..ZZZZZZZZZZZZZZZZZZZZ?..=ZZZZZZZZZ.................+ZZZZZ
ZZZZZ.........IZZZZZZZ:.ZZZZZZZZZZZZZZZZZ+ZZ...?ZZZZZZZZZ,................IZZZZZ
ZZZZZ.........$ZZZZZZ,.~ZZZZZZZZZZZZZZZ..ZZ:Z.,ZZZZZZZZZZZ,...............ZZZZZZ
ZZZZZ?........ZZZZZ~...ZZZZZZZZZZZZZZ~ . .7ZZZZZZZZZZZZZZZZ:..............ZZZZZ 
ZZZZZZ........ZZZZ ...+ZZZZZZZZZZZZZ7......ZZZZZZZZZZZZZZZZZ7............:ZZZZZ 
 ZZZZZ,.......ZZZZ....,ZZZZZZZZZZZZZZZ~....ZZZZZZZZZZZZZZZZZZZ...........ZZZZZ  
 ZZZZZZ......=ZZZZZZZ77ZZZZZZZZZZZZZZZZ....ZZZZZZZZZZZZZZZZZZZZ~.........ZZZZZ  
  ZZZZZ?.....+ZZZZZZZZZZZZZZZZZZZZZZZZZ?...ZZZZZZZZZZZZZZZZZZZZZZ.......ZZZZZ   
  ZZZZZZ......ZZZZZZZZZZZZZZZZZZZZZZZZZ$...ZZZZZZZZZZZZZZZZZZZZZZZ+....?ZZZZZ   
   ZZZZZZ.... ZZZZZZZZZZZZZZZZZZZZZZZZZ7...ZZZZZZZZZZZZZZZZZZZZZZZ:+7.:ZZZZZ    
    ZZZZZZ.....ZZZZZZZZZZZZZZZZZZZZZZZZ,...~ZZZZZZZZZZZZZZZZZZZZZ...~.ZZZZZZ    
     ZZZZZZ.....$ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ..Z.?ZZZZZZ     
      ZZZZZZ.......7ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ:~.~?ZZZZZZ      
       ZZZZZZ...................ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ.=,ZZZZZZZZ       
        ZZZZZZI....................$ZZZZZZZZZZZZZZZZZZZZZZZ=.ZZZ.ZZZZZZZ        
         ZZZZZZZ.....................$ZZZZZZZZZZZZZZZZZZZ:.$::=7ZZZZZZ          
           ZZZZZZZ.....................7ZZZZZZZZZZZZZZZ..7..7$ZZZZZZZ           
            ZZZZZZZZ,....................ZZZZZZZZZZ$,I.+=.Z+ZZZZZZZ             
              ZZZZZZZZ?...................ZZZZ$:.?.?Z.~,7ZZZZZZZZZ              
                ZZZZZZZZZ$.................Z..=.,7$..ZZZZZZZZZZZ                
                  ZZZZZZZZZZZZ:.............Z.:.ZIZZZZZZZZZZZ                   
                     ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ                     
                        ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ                         
                             ZZZZZZZZZZZZZZZZZZZZZZ                             
                                   ZZZZZZZZZZ                                   
*///print(WEBDOGS_LATEST_VERSION());print(WEBDOGS_LATEST_VERSION);