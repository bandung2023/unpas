<?php
include 'db.php';
error_reporting(E_ALL);
session_start();
session_regenerate_id();


$options = array(60     => '1 Minutes',
                 120    => '2 Minutes',
                 1800   => '30 Minutes',
                 1860   => '31 Minutes',
                 1920   => '32 Minutes',
                 1980   => '33 Minutes',
                 2040   => '34 Minutes',
                 2100   => '35 Minutes',
                 2160   => '36 Minutes',
                 2220   => '37 Minutes',
                 2280   => '38 Minutes',
                 2340   => '39 Minutes',
                 2400   => '40 Minutes',
                 2460   => '41 Minutes',
                 2520   => '42 Minutes',
                 2580   => '43 Minutes',
                 2640   => '44 Minutes',
                 2700   => '45 Minutes',
                 2760   => '46 Minutes',
                 2820   => '47 Minutes',
                 2880   => '48 Minutes',
                 2940   => '49 Minutes',
                 3000   => '50 Minutes',
                 3060   => '51 Minutes',
                 3120   => '52 Minutes',
                 3180   => '53 Minutes',
                 3240   => '54 Minutes',
                 3300   => '55 Minutes',
                 3360   => '56 Minutes',
                 3420   => '57 Minutes',
                 3480   => '58 Minutes',
                 3540   => '59 Minutes',
                 3600   => '60 Minutes',
          
                 );
				 
// From: https://gist.github.com/Xeoncross/1204255
$regions = array('Africa'     => DateTimeZone::AFRICA,
                 'America'    => DateTimeZone::AMERICA,
                 'Antarctica' => DateTimeZone::ANTARCTICA,
                 'Aisa'       => DateTimeZone::ASIA,
                 'Atlantic'   => DateTimeZone::ATLANTIC,
                 'Europe'     => DateTimeZone::EUROPE,
                 'Indian'     => DateTimeZone::INDIAN,
                 'Pacific'    => DateTimeZone::PACIFIC);
 
$timezones = array();
foreach ($regions as $name => $mask) {
    $zones = DateTimeZone::listIdentifiers($mask);
    foreach($zones as $timezone) {
		// Lets sample the time there right now
		$time = new DateTime(NULL, new DateTimeZone($timezone));
 
		// Us dumb Americans can't handle millitary time
		$ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';
 
		// Remove region name and add a sample time
		$timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
	}
}

function updateCronjobs($id = '') {
	if (@file_put_contents(dirname(__FILE__) . '/cronjobs.dat.php', '<' . '?php /*' . base64_encode(serialize($_SESSION['cronjobs'])) . '*/')) {
		$_SESSION['notices'][] = 'Database saved';
		
		// create 'backup'
		@file_put_contents(dirname(__FILE__) . '/cronjobs.backup-' . date('Y-m-d') . '.php', '<' . '?php /*' . base64_encode(serialize($_SESSION['cronjobs'])) . '*/');
	}
	else {
		$_SESSION['errors'][] = 'Database not saved, could not create database file on server, please check write rights of this script';
	}
	
	// remove old cronjob backup files
	$files = glob(dirname(__FILE__). '/cronjobs.backup*.php');
	foreach ($files as $file) {
		if (is_file($file) && time() - filemtime($file) >= 2*24*60*60) { // 2 days
			unlink($file);
		}
    }
    
	if ($id != '' && is_numeric($id)) {
		header('Location: ?m=edit&id=' . $id);
	}
	else {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
    exit;
}

if (file_exists(dirname(__FILE__) . '/cronjobs.dat.php')) {
	$data = @unserialize(@base64_decode(substr(file_get_contents(dirname(__FILE__) . '/cronjobs.dat.php'), 7, -2)));
	if (is_array($data)) {
		$_SESSION['cronjobs'] = $data;
	}
}
elseif (isset($_SESSION['cronjobs'])) {
    $_SESSION = null;
}
    
date_default_timezone_set(isset($_SESSION['cronjobs']['settings']['timezone']) ? $_SESSION['cronjobs']['settings']['timezone'] : 'Europe/Amsterdam');
	
if (isset($_SESSION['cronjobs'], $_SESSION['cronjobs']['settings'], $_SESSION['cronjobs']['settings']['password']) && 
    (!isset($_SESSION['login']) OR time() < ($_SESSION['login'] - (60 * 15)))) {     
     $template = 'index_login';
     
     if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
        sleep(2);
        if ($_POST['password'] == $_SESSION['cronjobs']['settings']['password']) {
            $_SESSION['login'] = time();
            
            header('Location: ' . basename($_SERVER['PHP_SELF']));
            exit;
        }
		else {
			$_SESSION['errors'][] = 'Password incorrect, try again';
		}
    }
}
else {
    $_SESSION['login'] = time();

    $m = isset($_GET['m']) ? $_GET['m'] : '';

    $template = 'index';
    $content  = '';
    
    if (!isset($_SESSION['cronjobs']['settings'])) {
        $_SESSION['notices'][] = 'First time here?<br />If not? the script crashed, please check out a backup of past days. <br /><br />Installation of this script, please check the <strong>settings</strong> page and fill in the required information to make the cronjob script work!';
    }
    
    
    

    switch ($m) {        
    
        case 'quit';
        
            $_SESSION = null;
            session_destroy();
            
            header('Location: ' . basename($_SERVER['PHP_SELF']));
            exit;
            
        break;        
            
        case 'settings':
        
            $template = 'form/index_settings';
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
				$good = true;	
				if (!isset($_POST['password']) OR !preg_match('/([a-zA-Z0-9_ ]{4,})/i', $_POST['password'])) {
					$_SESSION['errors'][] = 'Your password contains wrong characters, minimum of 4 letters and numbers';
				}
                
				if (strlen(trim($_POST['cronjobpassword'])) < 2) {
                    $_SESSION['errors'][] = 'Your cronjob script cannot run without a password, Your cronjob password contains wrong characters, minimum of 4 letters and numbers';
					$good = false;
                }
				
				$found = false;
				foreach($timezones as $region => $list) {
					foreach($list as $timezone => $name) {
						if ($timezone == $_POST['timezone']) {
							$found = true;
							break;
						}
					}
				}
				
				if ($found == false) {
					$_SESSION['errors'][] = 'You need to select a correct timezone';
					$good = false;
				}
				
				if ($good == true) {
					$_SESSION['cronjobs']['settings'] = array('password'        => $_POST['password'],
															  'cronjobpassword' => $_POST['cronjobpassword'],
															  'timezone'        => $_POST['timezone'],
															  'timeout'         => (isset($_POST['timeout']) && is_numeric($_POST['timeout']) ? $_POST['timeout'] : 30));
					updateCronjobs();
			    }
            }
            
            if (isset($_SESSION['cronjobs']['settings']) && !isset($good)) { 
                $_POST = $_SESSION['cronjobs']['settings'];
            }
            
        break;
        
        case 'new':
        
            $template = 'form/index_new';
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'])) {
                if (filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                    $found = false;
                    if (isset($_SESSION['cronjobs'], $_SESSION['cronjobs']['jobs']) && count($_SESSION['cronjobs']['jobs']) > 0) {
                        foreach ($_SESSION['cronjobs']['jobs'] as $null => $cronjob) {
                            if ($cronjob['url'] == $_POST['url']) {
                                $found = true;
                            }
                        }
                    }
                    
                    if ($found == false) {
                        if ($_POST['time'] == '' && $_POST['each'] == '') {
                            $_SESSION['errors'][] = 'Time settings missing, please add time settings';
                        }
                        else {
                            if (isset($_POST['maillog'], $_POST['maillogaddress']) && !filter_var($_POST['maillogaddress'], FILTER_VALIDATE_EMAIL)) {
                                $_SESSION['errors'][] = 'Email address is invalid!';
                            }
                            
                            $_SESSION['cronjobs']['jobs'][] = array('url'            => $_POST['url'],
                                                                    'time'           => ((isset($_POST['time']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/',  $_POST['time'])) ? $_POST['time'] : ''),
                                                                    'each'           => ((isset($_POST['each']) && is_numeric($_POST['each'])) ? $_POST['each'] : ''),
                                                                    'eachtime'       => ((isset($_POST['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/',  $_POST['eachtime'])) ? $_POST['eachtime'] : ''),
                                                                    'lastrun'        => '',
                                                                    'runned'         => 0,
                                                                    'savelog'        => (isset($_POST['savelog']) ? true : false),
                                                                    'maillog'        => (isset($_POST['maillog']) ? true : false),
                                                                    'maillogaddress' => ((isset($_POST['maillogaddress']) && filter_var($_POST['maillogaddress'], FILTER_VALIDATE_EMAIL)) ? $_POST['maillogaddress'] : ''));

                            updateCronjobs(count($_SESSION['cronjobs']['jobs']));
                        }
                    }
                    else {
                        $_SESSION['errors'][] = 'Cronjob already known in this system, if you would like to use more of the same use ?=randomnumber or if ? already is being used add &=randomnumber';
                    }
                }
                else {
                    $_SESSION['errors'][] = 'Cronjob URL is wrong';
                }
            }
                        
        break;
        
        case 'edit':
        
            $template = 'form/index_edit';
            $update = true;
            
            if (isset($_GET['id'], $_SESSION['cronjobs'], $_SESSION['cronjobs']['jobs'], $_SESSION['cronjobs']['jobs'][$_GET['id']])) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'], $_POST['time'], $_POST['each'])) {
                    if (filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                        if (isset($_POST['maillog'], $_POST['maillogaddress']) && !filter_var($_POST['maillogaddress'], FILTER_VALIDATE_EMAIL)) {
                            $_SESSION['errors'][] = 'Email address is invalid and not saved to database!';
                        }
                        
                        $_SESSION['cronjobs']['jobs'][$_GET['id']] = array('url'            => $_POST['url'],
                                                                           'time'           => ((isset($_POST['time']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/',  $_POST['time'])) ? $_POST['time'] : ''),
                                                                           'each'           => ((isset($_POST['each']) && is_numeric($_POST['each'])) ? $_POST['each'] : ''),
                                                                           'eachtime'       => ((isset($_POST['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/',  $_POST['eachtime'])) ? $_POST['eachtime'] : ''),
                                                                           'lastrun'        => $_SESSION['cronjobs']['jobs'][$_GET['id']]['lastrun'],
                                                                           'runned'         => $_SESSION['cronjobs']['jobs'][$_GET['id']]['runned'],
                                                                           'savelog'        => (isset($_POST['savelog']) ? true : false),
                                                                           'maillog'        => (isset($_POST['maillog']) ? true : false),
                                                                           'maillogaddress' => ((isset($_POST['maillogaddress']) && filter_var($_POST['maillogaddress'], FILTER_VALIDATE_EMAIL)) ? $_POST['maillogaddress'] : ''));

                        updateCronjobs();
                    }
                    else {
                        $_SESSION['errors'][] = 'Current URL is not correct, must contact http(s):// and a path';
                        $update = false;
                    }
                }
                
                if ($update == true) {
                    $_POST = $_SESSION['cronjobs']['jobs'][$_GET['id']];
                }
            }
            else {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        
        break;
            
        case 'log':
        
            $template = 'form/index_log';
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['clean'])) {
                $_SESSION['notices'][] = 'Cronjob log cleaned';
                file_put_contents('cronjobs.log', '');
                
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
        break;
        
        case 'about':
            
            $template = 'form/index_about';
        
        break;


        case 'account':
           $template = 'form/addaccount';

           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['secmail']))
           {
                if(isset($_POST['secmail'])) {$secmail =$_POST['secmail'];}else{ $secmail = '';}
                if(isset($_POST['uname'])) {$uname =$_POST['uname'];}else{ $uname = '';}
                if(isset($_POST['upswd'])) {$upswd =$_POST['upswd'];}else{ $upswd = '';}
                if(isset($_POST['sendname'])) {$sendname =$_POST['sendname'];}else{ $sendname = '';}
                if(isset($_POST['receipname'])) {$receipname =$_POST['receipname'];}else{ $receipname = '';}
                if(isset($_POST['blogurl'])) {$blogurl =$_POST['blogurl'];}else{ $blogurl = '';}
                if(isset($_POST['blogredir'])) {$blogredir =$_POST['blogredir'];}else{ $blogredir = '';}
                if(isset($_POST['img1'])) {$img1 =$_POST['img1'];}else{ $img1 = '';}
                if(isset($_POST['img2'])) {$img2 =$_POST['img2'];}else{ $img2 = '';}
                if(isset($_POST['img3'])) {$img3 =$_POST['img3'];}else{ $img3 = '';}
                if(isset($_POST['img4'])) {$img4 =$_POST['img4'];}else{ $img4 = '';}
                if(isset($_POST['img5'])) {$img5 =$_POST['img5'];}else{ $img5 = '';}

                $sts="active";
                $sql = "INSERT INTO account (secmail,uname,upswd,sendname,receipname,blogurl,blogredir,img1,img2,img3,img4,img5,status,pinger,dateping) VALUES
                 ('$secmail','$uname','$upswd','$sendname','$receipname','$blogurl','$blogredir','$img1','$img2','$img3','$img4','$img5','$sts','','')";

                if ($mysqli->query($sql) === TRUE) { } else {}

                $_SESSION['notices'][] = ''.$secmail.' Posted : New Account BlogSpot';

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

        break;


        //Sender Beggin
         case 'accountlist':
          $template = 'form/accountlist';
         break;
         
         case 'accountcopy':
          $template = 'form/accountcopy';
         
          if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['secmail']))
           {
                if(isset($_POST['secmail'])) {$secmail =$_POST['secmail'];}else{ $secmail = '';}
                if(isset($_POST['uname'])) {$uname =$_POST['uname'];}else{ $uname = '';}
                if(isset($_POST['upswd'])) {$upswd =$_POST['upswd'];}else{ $upswd = '';}
                if(isset($_POST['sendname'])) {$sendname =$_POST['sendname'];}else{ $sendname = '';}
                if(isset($_POST['receipname'])) {$receipname =$_POST['receipname'];}else{ $receipname = '';}
                if(isset($_POST['blogurl'])) {$blogurl =$_POST['blogurl'];}else{ $blogurl = '';}
                if(isset($_POST['blogredir'])) {$blogredir =$_POST['blogredir'];}else{ $blogredir = '';}
                if(isset($_POST['img1'])) {$img1 =$_POST['img1'];}else{ $img1 = '';}
                if(isset($_POST['img2'])) {$img2 =$_POST['img2'];}else{ $img2 = '';}
                if(isset($_POST['img3'])) {$img3 =$_POST['img3'];}else{ $img3 = '';}
                if(isset($_POST['img4'])) {$img4 =$_POST['img4'];}else{ $img4 = '';}
                if(isset($_POST['img5'])) {$img5 =$_POST['img5'];}else{ $img5 = '';}

                $sts="active";
                $sql = "INSERT INTO account (secmail,uname,upswd,sendname,receipname,blogurl,blogredir,img1,img2,img3,img4,img5,status,pinger,dateping) VALUES
                 ('$secmail','$uname','$upswd','$sendname','$receipname','$blogurl','$blogredir','$img1','$img2','$img3','$img4','$img5','$sts','','')";

                if ($mysqli->query($sql) === TRUE) { } else {}

                $_SESSION['notices'][] = ''.$secmail.' Posted : New Account BlogSpot';

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
         break;  
         
          case 'accountedit':
          $template = 'form/accountedit';
          
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['secmail']))
           {
                if(isset($_POST['secmail'])) {$secmail =$_POST['secmail'];}else{ $secmail = '';}
                if(isset($_POST['uname'])) {$uname =$_POST['uname'];}else{ $uname = '';}
                if(isset($_POST['upswd'])) {$upswd =$_POST['upswd'];}else{ $upswd = '';}
                if(isset($_POST['sendname'])) {$sendname =$_POST['sendname'];}else{ $sendname = '';}
                if(isset($_POST['receipname'])) {$receipname =$_POST['receipname'];}else{ $receipname = '';}
                if(isset($_POST['blogurl'])) {$blogurl =$_POST['blogurl'];}else{ $blogurl = '';}
                if(isset($_POST['blogredir'])) {$blogredir =$_POST['blogredir'];}else{ $blogredir = '';}
                if(isset($_POST['img1'])) {$img1 =$_POST['img1'];}else{ $img1 = '';}
                if(isset($_POST['img2'])) {$img2 =$_POST['img2'];}else{ $img2 = '';}
                if(isset($_POST['img3'])) {$img3 =$_POST['img3'];}else{ $img3 = '';}
                if(isset($_POST['img4'])) {$img4 =$_POST['img4'];}else{ $img4 = '';}
                if(isset($_POST['img5'])) {$img5 =$_POST['img5'];}else{ $img5 = '';}
                if(isset($_POST['dbid'])) {$dbid =$_POST['dbid'];}else{ $dbid = '';}

                $sql = "UPDATE account SET secmail='$secmail',uname='$uname',upswd='$upswd',sendname='$sendname',receipname='$receipname',
                                blogurl='$blogurl',blogredir='$blogredir',img1='$img1',img2='$img2',img3='$img3',img4='$img4',img5='$img5'

                       WHERE id=".$dbid."";

                if ($mysqli->query($sql) === TRUE) { } else {}

                $_SESSION['notices'][] = ''.$secmail.' Update : Account BlogSpot';

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

         break;
        
        case 'desc':
         $template = 'form/desc';

           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['ctitle'])) {
                if(isset($_POST['descat'])) {$descat =$_POST['descat'];}else{ $descat = '';}
                if(isset($_POST['thname'])) {$thname =$_POST['thname'];}else{ $thname = '';}
                if(isset($_POST['ctitle'])) {$ctitle =$mysqli->real_escape_string($_POST['ctitle']);}else{ $ctitle = '';}
                if(isset($_POST['cbody'])) {$cbody =$mysqli->real_escape_string($_POST['cbody']);}else{ $cbody = '';}
                $_SESSION['notices'][] = ''.$ctitle.'';

                $sql = "INSERT INTO cdesc(descat,thname,ctitle,cbody) VALUES  ('$descat','$thname','$ctitle','$cbody')";
                if ($mysqli->query($sql) === TRUE) { } else {}
                
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        break;

      
         
         case 'descedit':
         $template = 'form/descedit';
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['ctitle'])) {
                if(isset($_POST['ctitle'])) {$ctitle =$mysqli->real_escape_string($_POST['ctitle']);}else{ $ctitle = '';}
                if(isset($_POST['cbody'])) {$cbody =$mysqli->real_escape_string($_POST['cbody']);}else{ $cbody = '';}
                if(isset($_POST['dbid'])) {$dbid =$_POST['dbid'];}else{ $dbid = '';}
                if(isset($_POST['descat'])) {$descat =$_POST['descat'];}else{ $descat = '';}
                if(isset($_POST['thname'])) {$thname =$_POST['thname'];}else{ $thname = '';}
                $_SESSION['notices'][] = ''.$ctitle.'';

                $sql = "UPDATE cdesc SET descat='$descat',thname='$thname',ctitle='$ctitle',cbody='$cbody' WHERE id=".$dbid."";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
         break;
         
          case 'askwd':
          $template = 'form/askwd';
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['title1'])) {
            
                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['title1'])) {$title1 =$mysqli->real_escape_string($_POST['title1']);}else{ $title1 = '';}
                if(isset($_POST['title2'])) {$title2 =$mysqli->real_escape_string($_POST['title2']);}else{ $title2 = '';}
                if(isset($_POST['title3'])) {$title3 =$mysqli->real_escape_string($_POST['title3']);}else{ $title3 = '';}
                if(isset($_POST['title4'])) {$title4 =$mysqli->real_escape_string($_POST['title4']);}else{ $title4 = '';}
                if(isset($_POST['title5'])) {$title5 =$mysqli->real_escape_string($_POST['title5']);}else{ $title5 = '';}
                if(isset($_POST['asin1'])) {$asin1 =$_POST['asin1'];}else{ $asin1 = '';}
                if(isset($_POST['asin2'])) {$asin2 =$_POST['asin2'];}else{ $asin2 = '';}
                if(isset($_POST['asin3'])) {$asin3 =$_POST['asin3'];}else{ $asin3 = '';}
                if(isset($_POST['asin4'])) {$asin4 =$_POST['asin4'];}else{ $asin4 = '';}
                if(isset($_POST['asin5'])) {$asin5 =$_POST['asin5'];}else{ $asin5 = '';}
                if(isset($_POST['poster1'])) {$poster1 =$_POST['poster1'];}else{ $poster1 = '';}
                if(isset($_POST['poster2'])) {$poster2 =$_POST['poster2'];}else{ $poster2 = '';}
                if(isset($_POST['poster3'])) {$poster3 =$_POST['poster3'];}else{ $poster3 = '';}
                if(isset($_POST['poster4'])) {$poster4 =$_POST['poster4'];}else{ $poster4 = '';}
                if(isset($_POST['poster5'])) {$poster5 =$_POST['poster5'];}else{ $poster5 = '';}
          
                $_SESSION['notices'][] = 'Amazon Asin Book Submited';

                $sql = "INSERT INTO dbasin(niche,title1,asin1,poster1,title2,asin2,poster2,title3,asin3,poster3,title4,asin4,poster4,title5,asin5,poster5) 
                        VALUES  ('$niche','$title1','$asin1','$poster1','$title2','$asin2','$poster2','$title3','$asin3','$poster3','$title4','$asin4','$poster4','$title5','$asin5','$poster5')";
                if ($mysqli->query($sql) === TRUE) { } else {}
                
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
          break;
          
          case 'xlis':
          $template = 'form/xlis';
          break;
          
          case 'xtraedit':
          $template = 'form/xtraedit';
          
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['niche'])) {
                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['cspin'])) {$cspin =$mysqli->real_escape_string($_POST['cspin']);}else{ $cspin = '';}
                if(isset($_POST['dbid'])) {$dbid =$_POST['dbid'];}else{ $dbid = '';}
                $_SESSION['notices'][] = 'Xtra Content Submited';

                $sql = "UPDATE xtracontent SET niche='$niche',cspin='$cspin' WHERE id=".$dbid."";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
          break;
          
        
          case 'xtra':
          $template = 'form/xtra';
            if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['niche'])) {
                if(isset($_POST['niche'])) {$niche =$mysqli->real_escape_string($_POST['niche']);}else{ $niche = '';}
                if(isset($_POST['cspin'])) {$cspin =$mysqli->real_escape_string($_POST['cspin']);}else{ $cspin = '';}
                 
                $_SESSION['notices'][] = 'Xtra Content Submited';

                $sql = "INSERT INTO xtracontent(niche,cspin,ckwd) VALUES  ('$niche','$cspin')";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
          break;
          
         
          
          case 'askwdlist':
          $template = 'form/askwdlist';
          break;
          

          case 'desclist':
          $template = 'form/desclist';
          break;
          
          case 'service':
          $template = 'form/service';

           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['descat'])) {
                if(isset($_POST['descat'])) {$descat =$mysqli->real_escape_string($_POST['descat']);}else{ $descat = '';}
                if(isset($_POST['jservice'])) {$jservice =$_POST['jservice'];}else{ $jservice = '';}
                if(isset($_POST['ccd'])) {$ccd =$_POST['ccd'];}else{ $ccd = '';}
                if(isset($_POST['weblp'])) {$weblp =$_POST['weblp'];}else{ $weblp = '';}
                if(isset($_POST['htacs'])) {$htacs =$_POST['htacs'];}else{ $htacs = '';}
                $_SESSION['notices'][] = 'Service Submited';

                $sql = "INSERT INTO webservice(descat,jservice,ccd,weblp,htacs) VALUES  ('$descat','$jservice','$ccd','$weblp','$htacs')";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            

          break;

          case 'serviceedit':
          $template = 'form/serviceedit';
          
          if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['descat'])) {
                if(isset($_POST['descat'])) {$descat =$mysqli->real_escape_string($_POST['descat']);}else{ $descat = '';}
                if(isset($_POST['jservice'])) {$jservice =$_POST['jservice'];}else{ $jservice = '';}
                if(isset($_POST['ccd'])) {$ccd =$_POST['ccd'];}else{ $ccd = '';}
                if(isset($_POST['dbid'])) {$dbid =$_POST['dbid'];}else{ $dbid = '';}
                if(isset($_POST['weblp'])) {$weblp =$_POST['weblp'];}else{ $weblp = '';}
                if(isset($_POST['htacs'])) {$htacs =$_POST['htacs'];}else{ $htacs = '';}
                $_SESSION['notices'][] = 'Service Edited';

                 $sql = "UPDATE webservice SET descat='$descat',jservice='$jservice',ccd='$ccd',weblp='$weblp',htacs='$htacs'  WHERE id=".$dbid."";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

          break;


        //Sender Beggin
         case 'dbconf':
          $template = 'form/dbconf';
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['shordb'])) {
                if(isset($_POST['shordb'])) {$shordb =$_POST['shordb'];}else{ $shordb = '';}
                if(isset($_POST['limitpage'])) {$limitpage =$_POST['limitpage'];}else{ $limitpage = '';}
                if(isset($_POST['cronmix'])) {$cronmix =$_POST['cronmix'];}else{ $cronmix = '';}
                if(isset($_POST['cronpid'])) {$cronpid =$_POST['cronpid'];}else{ $cronpid = '';}
                if(isset($_POST['linkmode'])) {$linkmode =$_POST['linkmode'];}else{ $linkmode = '';}
                if(isset($_POST['smode'])) {$smode =$_POST['smode'];}else{ $smode = '';}
                if(isset($_POST['dbid'])) {$dbid =$_POST['dbid'];}else{ $dbid = '';}
                
                $_SESSION['notices'][] = 'Update DB Conf';

                $sql = "UPDATE dbconf SET shordb='$shordb',limitpage='$limitpage',
                        cronmix='$cronmix',cronpid='$cronpid',smode='$smode',linkmode='$linkmode'
                        WHERE id=1";
                        
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

         break;
         
        case 'panel':
          $template = 'form/panel';
          if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['cronpid'])) {
                if(isset($_POST['cronpid'])) {$cronpid =$_POST['cronpid'];}else{ $cronpid = '';}
                if(isset($_POST['vt'])) {$vt =$_POST['vt'];}else{ $vt = '';}
                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['act'])) {$act =$_POST['act'];}else{ $act = '';}
                if(isset($_POST['dbservice'])) {$dbservice =$_POST['dbservice'];}else{ $dbservice = '';}
                if(isset($_POST['cdesc'])) {$cdesc =$_POST['cdesc'];}else{ $cdesc = '';}
                $_SESSION['notices'][] = 'Submit Cron Panel PID :'.$cronpid.'';
                $status='active';
                $sql1 = "INSERT INTO cronpanel(vt,cronpid,niche,cdesc,act,status,wservice,sendcron) 
                                      VALUES  ('$vt','$cronpid','$niche','$cdesc','$act','$status','$dbservice','')";
                if ($mysqli->query($sql1) === TRUE) { } else {}
                
                $sql2 = "UPDATE account SET status='sending' WHERE id=".$act."";
                if ($mysqli->query($sql2) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        break;
       
       
        case 'kwdtitle':
        $template = 'form/kwdtitle';
        
           if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['webkwd'])) {
                if(isset($_POST['webkwd'])) {$kwd =ucwords($_POST['webkwd']);}else{ $kwd = '';}
                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['karea'])) {$karea =$_POST['karea'];}else{ $karea = '';}
             
                $_SESSION['notices'][] = ''.$kwd.' ,'.$niche.' ,'.$karea.' ';

                $sql = "INSERT INTO kwdtitle(kniche,kwd,karea) VALUES ('$niche','$kwd','$karea')";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            

        break;
          
         case 'panellist':
         $template = 'form/panellist';
         break;
         
         case 'sender':
         $template = 'form/sender';
         break;
         
         case 'jsoncron':
         $template = 'form/cronjson';
         break;
         
         case 'tmdbcron':
         $template = 'form/crontmdb';
         break;
         
         case 'tmdbapi':
         $template = 'form/tmdbapi';
         if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['tapi'])) {
                
                if(isset($_POST['tapi'])) {$tapi =$_POST['tapi'];}else{ $tapi = '';}
                $_SESSION['notices'][] = 'API Submited';

                $sql = "INSERT INTO tmdbapi(tmdbapi) VALUES  ('$tapi')";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
         break;
 
         
         
          case 'tmdb':
          $template = 'form/tmdb';
          if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['niche'])) {

                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['tmdb'])) {$tmdb =$_POST['tmdb'];}else{ $tmdb = '';}
                $v1='';
                $v2='';
                $v3='';
                $v4='';
                $v5='';
             
                
                $_SESSION['notices'][] = 'TMDB Inserted';
                $i = 0;
                $text = explode("\n",$tmdb);
                foreach ($text as $key => $value) {
                $fasn=$mysqli->real_escape_string($value);
                $sql = "INSERT INTO dbtmdb (niche,tmdb,v1,v2,v3,v4,v5) VALUES ('$niche','$fasn','$v1','$v2','$v3','$v4','$v5')";

                if ($mysqli->query($sql) === TRUE) {} else { }
                      $i++;
                }
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
          break;
          
          case 'tmdblist':
          $template = 'form/tmdblist';
          break;
          
          case 'tmdbsend':
               $template = 'form/tmdbsend';
          break;
        // Sender End
         
         case 'pinger':
          $template = 'form/pinger';
          break;
          
          case 'relatedlist':
          $template = 'form/relatedlist';
          break;
         
        case 'related':
         $template = 'form/related';
         if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['title1'])) {

                if(isset($_POST['niche'])) {$niche =$_POST['niche'];}else{ $niche = '';}
                if(isset($_POST['title1'])) {$title1 =$mysqli->real_escape_string($_POST['title1']);}else{ $title1 = '';}
                if(isset($_POST['title2'])) {$title2 =$mysqli->real_escape_string($_POST['title2']);}else{ $title2 = '';}
                if(isset($_POST['title3'])) {$title3 =$mysqli->real_escape_string($_POST['title3']);}else{ $title3 = '';}
                if(isset($_POST['title4'])) {$title4 =$mysqli->real_escape_string($_POST['title4']);}else{ $title4 = '';}

                if(isset($_POST['tmdb1'])) {$tmdb1 =$_POST['tmdb1'];}else{ $tmdb1 = '';}
                if(isset($_POST['tmdb2'])) {$tmdb2 =$_POST['tmdb2'];}else{ $tmdb2 = '';}
                if(isset($_POST['tmdb3'])) {$tmdb3 =$_POST['tmdb3'];}else{ $tmdb3 = '';}
                if(isset($_POST['tmdb4'])) {$tmdb4 =$_POST['tmdb4'];}else{ $tmdb4 = '';}

                if(isset($_POST['poster1'])) {$poster1 =$_POST['poster1'];}else{ $poster1 = '';}
                if(isset($_POST['poster2'])) {$poster2 =$_POST['poster2'];}else{ $poster2 = '';}
                if(isset($_POST['poster3'])) {$poster3 =$_POST['poster3'];}else{ $poster3 = '';}
                if(isset($_POST['poster4'])) {$poster4 =$_POST['poster4'];}else{ $poster4 = '';}

                $_SESSION['notices'][] = 'Related Submited';

                $sql = "INSERT INTO related(niche,title1,tmdb1,poster1,title2,tmdb2,poster2,title3,tmdb3,poster3,title4,tmdb4,poster4)
                        VALUES  ('$niche','$title1','$tmdb1','$poster1','$title2','$tmdb2','$poster2','$title3','$tmdb3','$poster3','$title4','$tmdb4','$poster4')";
                if ($mysqli->query($sql) === TRUE) { } else {}

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
         break;
         
        case 'logs':
            $template = 'form/index_logs';
            
            if (isset($_GET['id'], $_SESSION['cronjobs'], $_SESSION['cronjobs']['jobs'], $_SESSION['cronjobs']['jobs'][$_GET['id']])) {
                $files = glob('./logs/*' . preg_replace('/[^A-Za-z0-9 ]/', '', $_SESSION['cronjobs']['jobs'][$_GET['id']]['url']) . '.log');
                if (is_array($files) && count($files) > 0) {
                    arsort($files);
                }

                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cronjobs'])) {
                    foreach ($_POST['cronjobs'] as $k => $v) {
                        if (isset($files[$k]) && file_exists($k)) {
							if (!@unlink($files[$k])) {
								$_SESSION['errors'][] = 'File ' . $files[$k] . ' could not be removed from the server, please do it manualy';
							}
						}
                    }
                    
                    $_SESSION['notices'][] = 'Removed ' . count($_POST['cronjobs']) . ' logs from the server';
                    
                    header('Location: ' . basename($_SERVER['PHP_SELF']) . '?m=logs&id=' .  $_GET['id']);
                    exit;
                }
            }
            else {
                header('Location: ' . basename($_SERVER['PHP_SELF']));
                exit;
            }
            
        break;

        default:
        
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cronjobs']) && is_array($_POST['cronjobs'])) {
                // remove from session
                foreach ($_POST['cronjobs'] as $k => $v) {
                    // get log files, if exists;
                    if (is_dir('./logs/')) {
                        $files = glob('./logs/*' . preg_replace('/[^A-Za-z0-9 ]/', '', $_SESSION['cronjobs']['jobs'][$k]['url']) . '.log');
                        // files found?
                        if (is_array($files) && count($files) > 0) {
                            // remove all!!
                            foreach ($files as $k => $file) {
                                if (!@unlink($file)) {
									$_SESSION['errors'][] = 'Could not remove file ' . $file . ' from server, please do this manually';
								}
                            }
                        }
                    }
                    unset($_SESSION['cronjobs']['jobs'][$k]);
                }
                
                $_SESSION['notices'][] = count($_POST['cronjobs']) . ' cronjobs removed';
                
                updateCronjobs();
            }
            
        break;
    }
}

if (file_exists($template . '.tpl')) {
    ob_start();
    include $template . '.tpl';
    $content = ob_get_contents();
    ob_end_clean();
}
elseif (!file_exists('layout.tpl')) {
	die('Main template could not be loaded, aborting');
}
else {
    die('Template can not be found, how irritating...');
}

include 'layout.tpl';
