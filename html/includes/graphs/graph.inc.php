<?php

if($_GET['debug']) {
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', E_ALL);
} else {
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

  include("../includes/defaults.inc.php");
  include("../config.php");
  include("../includes/common.php");
  include("../includes/rewrites.php");
  include("includes/functions.inc.php");
  include("includes/authenticate.inc.php");

  $from     = mres($_GET['from']);
  $to       = mres($_GET['to']);
  $width    = mres($_GET['width']);
  $height   = mres($_GET['height']);
  $title    = mres($_GET['title']);
  $vertical = mres($_GET['vertical']);
  $legend   = mres($_GET['legend']);
  $id       = mres($_GET['id']);

  if(!$config['allow_unauth_graphs']) 
  {
    if(!$_SESSION['authenticated']) { graph_error("Not authenticated"); exit; }
  }
  
#  if($_GET['device']) {
#    $_GET['id'] = $_GET['device'];
#    $device_id = $_GET['device'];
#  } elseif($_GET['if']) {
#    $_GET['id'] = $_GET['if'];
#  } elseif($_GET['port']) {
#    $_GET['id'] = $_GET['port'];
#  } elseif($_GET['peer']) {
#    $_GET['id'] = $_GET['peer'];
#  }

  preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', mres($_GET['type']), $graphtype);

  $type = $graphtype['type'];
  $subtype = $graphtype['subtype'];

  if($debug) {print_r($graphtype);}

  $graphfile = $config['temp_dir'] . "/"  . strgen() . ".png";

#  $os = gethostosbyid($device_id);
#  if($config['os'][$os]['group']) {$os_group = $config['os'][$os]['group'];}
#  if(is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os.".inc.php")) {
#    /// Type + OS Specific
#    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os.".inc.php");
#  }elseif($os_group && is_file($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php")) {
#    /// Type + OS Group Specific
#    include($config['install_dir'] . "/html/includes/graphs/".$type."_".$os_group.".inc.php");
#  } elseif(is_file($config['install_dir'] . "/html/includes/graphs/$type.inc.php")) {
#    /// Type Specific
#    include($config['install_dir'] . "/html/includes/graphs/$type.inc.php");
#  }

if(is_file($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php")) {
  include($config['install_dir'] . "/html/includes/graphs/$type/auth.inc.php");
  if($auth) { 
    include($config['install_dir'] . "/html/includes/graphs/$type/$subtype.inc.php");
  }
} else {
  graph_error("Graph Template Missing");
}

function graph_error ($string) 
{
  global $width, $height;
  header('Content-type: image/png');
  if($height > "99")  { $width +=76; }
  $im     = imagecreate($width, $height);
  $orange = imagecolorallocate($im, 255, 225, 225);
  $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
  imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
  imagepng($im);
  imagedestroy($im);
  exit();
}

if(!$auth)
{
  if($width < 200)
  {
   graph_error("No Auth");
  } else {
   graph_error("No Authorisation");
  }
} else {
  $rrd_options .= " HRULE:0#999999";
  if($no_file)
  {
    if($width < 200)
    {
      graph_error("No RRD");
    } else {
      graph_error("Missing RRD Datafile");
    }
  } else {
    if($rrd_options) 
    {
      if($config['rrdcached']) { $rrd_switches = " --daemon ".$config['rrdcached'] . " "; }
      $rrd_cmd = $config['rrdtool'] . " graph $graphfile $rrd_options" . $rrd_switches;
      $woo = shell_exec($rrd_cmd);
      if($_GET['debug']) { echo("<pre>".$rrd_cmd."</pre>"); }    
      if(is_file($graphfile)) {
        header('Content-type: image/png');
        $fd = fopen($graphfile,'r');fpassthru($fd);fclose($fd);
        unlink($graphfile);
      } else {
        if($width < 200)
        {
          graph_error("Draw Error");
        } else {
          graph_error("Error Drawing Graph");
        }
      }
    } else {
      if($width < 200)
      {
        graph_error("Def Error");
      } else {
        graph_error("Graph Definition Error");
      }
    }
  }
}

?>
