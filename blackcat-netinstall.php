<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Netinstall
 *   @package         CAT_Netinstall
 *
 **/

define('BC_BASE_URL','http://blackcat-cms.org/media/netinstall');
define('CURRENT_URL',$_SERVER['SCRIPT_NAME']);
define('GITHUB_URL','https://github.com/webbird/BlackCatCMS');
define('TOKEN','ea2d52e7e978eb9885a82c0f65a0f05d4c6e6cd5');

ignore_user_abort(true); //avoid apache to kill the php running
session_start();

if(!isset($_SESSION['__bc_netinstall__'])) {
    $_SESSION['__bc_netinstall__'] = array();
}

if(isset($_GET['dl'])) {
    bc_do_download();
    exit;
}
if(isset($_GET['check'])) {
    echo bc_check();
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : 1;
if(isset($_POST['destination'])) {
    $_SESSION['__bc_netinstall__']['destination'] = $_POST['destination'];
    $step = 2;
    if(isset($_POST['proxy']))
        $_SESSION['__bc_netinstall__']['proxy'] = $_POST['proxy'];
    if(isset($_POST['proxy_port']))
        $_SESSION['__bc_netinstall__']['proxy_port'] = $_POST['proxy_port'];
}
if(isset($_GET['source'])) {
    $_SESSION['__bc_netinstall__']['source']      = $_GET['source'];
    $step = 2;
}

init_lang();
startPage($step);
endPage();

function startPage($step=1)
{
    header('Content-Type: text/html; charset=UTF-8');
    echo "
<!DOCTYPE html>
<html lang=\"de_DE\" dir=\"ltr\">
<head>
    <meta charset=\"UTF-8\" />
    <title>BlackCat CMS Netinstall</title>
    <meta name=\"ROBOTS\" content=\"NOARCHIVE,NOINDEX,NOFOLLOW\" />
    <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"".BC_BASE_URL."/netinstall.css\" />
</head>
<body>
    <div id=\"header\" class=\"icon icon-logo_bc\"></div>
    <div id=\"headline\">".translate('Welcome to BlackCat CMS NetInstall!')."</div>".
    timeline($step)."
";
}

function endPage()
{
    echo "
        <div class=\"note border round\">Please note: This tool is available in German language only at the moment. If you need the NetInstall tool in another language, please help us translating it.</div><br /><br />
    </div>
    <script charset=windows-1250 type=\"text/javascript\">
    function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
    </script>
</body></html>";
}

function timeline($step)
{
    $steps = array(
        1 => translate('Config'),
        2 => translate('Download'),
        3 => translate('Unpack'),
        4 => translate('Ready'),
    );
    $tl = "    <ol class=\"timeline\">\n";
    for($i=1;$i<=4;$i++) {
        $tl .= "        <li class=\"step".
             ( ($i==$step) ? ' current' : '' ).
             "\">
           <a href=\"".CURRENT_URL."?step=$i\"><i class=\"step-marker\">$i</i></a><span>".$steps[$i]."</span>\n";
    }
    $tl .= "    </ol>\n";
    return $tl;
}

function translate($msg)
{
    global $lang_de;
    return ( isset($lang_de[$msg]) ? $lang_de[$msg] : $msg );
}

function init_lang()
{
    global $lang_de;
    $lang_de = array(
        'Config' => 'Konfiguration',
        'Destination' => 'Zielverzeichnis',
        'Do you need Proxy settings?' => 'Brauchen Sie Proxy-Einstellungen?',
        'Installation precheck results'
            => 'Vorinstallation-Check Ergebnisse',
        'Please wait for the download to complete...' => 'Bitte warten bis der Download abgeschlossen wurde...',
        'Proxy host' => 'Proxy Name',
        'Proxy port' => 'Proxy Port',
        'Ready' => 'Fertig',
        'Retrieve and unzip' => 'Herunterladen und entpacken von',
        'This tool will download the latest version of BlackCat CMS and unpack it on your webspace.'
            => 'Dieses Tool lädt die aktuellste Version von BlackCat CMS herunter und entpackt sie auf Ihrem Webspace.',
        'Unable to check releases, maybe you need to set proxy options?'
            => 'Kann Releases nicht ermitteln, müssen Sie vielleicht Proxy-Einstellungen vornehmen?',
        'Unpack' => 'Entpacken',
        'Welcome to BlackCat CMS NetInstall!' => 'Willkommen bei BlackCat CMS NetInstall!',
        'Yes, I\'m a button!' => 'Ja, ich bin ein Button!',
        'You can leave the field blank to install into the folder where the NetInstall tool is located.'
            => 'Sie können das Feld leer lassen, um in das Verzeichnis zu installieren, in dem das NetInstall Tool installiert ist.',
        'You will be forwarded to the BlackCat CMS Installation Wizard then.' => 'Sie werden anschließend zum BlackCat CMS Installationswizard weitergeleitet.',
        'You will be forwarded to the Installation Wizard now.' => 'Sie werden jetzt zum Installation Wizard weitergeleitet.',
    );
}

function init_client()
{
    $headers = array(
        'Authorization: token ' . TOKEN,
        'User-Agent: php-curl'
    );
    $connection = curl_init();
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
    if(isset($_SESSION['__bc_netinstall__']['proxy']) && $_SESSION['__bc_netinstall__']['proxy']!='')
        curl_setopt($connection, CURLOPT_PROXY, $_SESSION['__bc_netinstall__']['proxy']);
    if(isset($_SESSION['__bc_netinstall__']['proxy_port']) && $_SESSION['__bc_netinstall__']['proxy_port']!='')
        curl_setopt($connection, CURLOPT_PROXYPORT, $_SESSION['__bc_netinstall__']['proxy_port']);
    return $connection;
}

/**
 * retrieve GitHub info
 **/
function retrieve($url,$connection)
{
    #https://github.com/webbird/BlackCatCMS
    $repo = pathinfo(GITHUB_URL,PATHINFO_FILENAME);
    $org  = str_replace('https://github.com/','',pathinfo(GITHUB_URL,PATHINFO_DIRNAME));
    $url  = sprintf('https://api.github.com/repos/%s/%s/%s',
            $org, $repo, $url);
    try {
        //echo "retrieve url: $url<br />";
        curl_setopt($connection,CURLOPT_URL,$url);
        $result = json_decode(curl_exec($connection), true);
        if(isset($result['documentation_url']))
            echo "GitHub Error: ", $result['message'], "<br />URL: $url<br />";
        return $result;
    } catch ( Exception $e ) {
        echo "CUrl error: ", $e->getMessage(), "<br />";
    }
}

function retrieve_remote_file_size($url)
{
     $connection = init_client();
     curl_setopt($connection, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($connection, CURLOPT_HEADER, TRUE);
     curl_setopt($connection, CURLOPT_NOBODY, TRUE);
     curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
     curl_setopt($connection, CURLOPT_MAXREDIRS, 2);
     curl_setopt($connection, CURLOPT_URL, $url);
     $data = curl_exec($connection);
     $size = curl_getinfo($connection, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
     curl_close($connection);
     return $size;
}

/**
 * print error message and exit
 **/
function writeError($msg)
{
    echo "<div class=\"error\">".translate($msg)."</div>";
    exit;
}

/**
 * checks some pre-installation requirements
 **/
function precheck()
{
    $php     = version_compare(PHP_VERSION, '5.3.1', '>=');
    $globals = ini_get('register_globals');
    $safe    = ini_get('safe_mode');
    $ok      = ( $php && !$globals && !$safe ) ? true : false;
    echo "
    <div id=\"content\">
    ".translate('This tool will download the latest version of BlackCat CMS and unpack it on your webspace.')."<br />
    ".translate('You will be forwarded to the BlackCat CMS Installation Wizard then.')."<br /><br />";
    if($ok) {
        echo "
    <form method=\"post\" action=\"\">
    <label for=\"destination\">".translate('Destination').":</label> ".dirname(__FILE__).DIRECTORY_SEPARATOR."
      <input type=\"text\" id=\"destination\" name=\"destination\" value=\"cms\" /><br />
      <span class=\"note\">".translate('You can leave the field blank to install into the folder where the NetInstall tool is located.')."<br /><br />
    <a href=\"#\" id=\"toggle_proxy_settings\" onclick=\"toggle_visibility('proxy_settings')\">".translate('Do you need Proxy settings?')."</a><br /><br />
    <div id=\"proxy_settings\" style=\"display:none;\">
    <label for=\"proxy\">".translate('Proxy host').":</label>
      <input type=\"text\" id=\"proxy\" name=\"proxy\" value=\"".( isset($_SESSION['__bc_netinstall__']['proxy']) ? $_SESSION['__bc_netinstall__']['proxy'] : '' )."\" /><br />
    <label for=\"proxy_port\">".translate('Proxy port').":</label>
      <input type=\"text\" id=\"proxy_port\" name=\"proxy_port\" value=\"".( isset($_SESSION['__bc_netinstall__']['proxy_port']) ? $_SESSION['__bc_netinstall__']['proxy_port'] : '' )."\" /><br />
    </div>
    <button type=\"submit\" name=\"start\" id=\"start\" class=\"round\">".translate('Retrieve and unzip')." BlackCat CMS (".translate('Yes, I\'m a button!').")</button>
    </form>
    ";
    }
    else
    {
        echo "<h1>".translate('Installation precheck results').":</h1><br />
    <span class=\"label\">".translate('PHP Version').": 5.3.1</span><span class=\"icon icon-".($php?'checkmark-circle':'warning')."\"></span><br />
    <span class=\"label\">".translate('PHP register_globals').": off</span><span class=\"icon icon-".(!$globals?'checkmark-circle':'warning')."\"></span><br />
    <span class=\"label\">".translate('PHP safe_mode').": off</span><span class=\"icon icon-".(!$safe?'checkmark-circle':'warning')."\"></span><br />
    <div class=\"error\">".translate('Sorry, unable to install, see above')."</div>";
    }
}

/**
 * download latest version from GitHub
 **/
function download()
{
    if(isset($_GET['error'])) {
        writeError('The download failed (timeout)');
    }

    // second call
    if(isset($_SESSION['__bc_netinstall__']['source']))
    {
        $path  = dirname(__FILE__).DIRECTORY_SEPARATOR.$_SESSION['__bc_netinstall__']['destination'];
        if(file_exists($path.'/blackcat.zip')) {
            $size     = retrieve_remote_file_size($_SESSION['__bc_netinstall__']['source']);
            $loc_size = filesize($path.'/blackcat.zip');
            if($size == $loc_size) {
                header('Location: '.CURRENT_URL.'?step=3');
            }
        }

        echo "<div class=\"running\">".translate('Please wait for the download to complete...')."</div>
    <script charset=\"iso-8859-1\" type=\"text/javascript\">
    var result = false;
    var max    = 120000;
    var est    = 0;
    function ajaxRequest(url) {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject(\"Microsoft.XMLHTTP\");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 ) {
                if(xmlhttp.status != 200 && xmlhttp.status != 0) {
                    if(xmlhttp.status == 400) {
                        alert('There was an error 400')
                    }
                    else {
                        console.log('something other than 200 was returned: ' + xmlhttp.status)
                    }
                }
                else
                {
                    if(xmlhttp.responseText.length && xmlhttp.responseText == 1) {
                        result = true;
                        return result;
                    }
                }
            }
        }
        if(result) { return result; }
        xmlhttp.open(\"GET\", url, true);
        xmlhttp.send();
    }
    function poll() {
       est = est + 1000;
       if(est > max) {
           location.href = '".CURRENT_URL."?step=2&error=1';
       }
       setTimeout(function(){
           var xresult = ajaxRequest('".CURRENT_URL."?check=true');
           if( xresult !== true ) {
               poll();
           }
           location.href = '".CURRENT_URL."?step=3';
      }, 1000);
    }
    ajaxRequest('".CURRENT_URL."?dl=true');
    poll();
    </script>
        ";



    }
    else
    {
        // first call
        $connection = init_client();
        $releases   = retrieve('releases',$connection);
        $latest     = array();
        if(is_array($releases) && count($releases))
        {
            foreach($releases as $r)
            {
                if($r['prerelease']==1) continue;
                $latest = $r;
                break;
            }
            if(is_array($latest)) {
                echo "
    <script charset=\"iso-8859-1\" type=\"text/javascript\">
        location.href = '".CURRENT_URL.'?step=2&source='.$latest['zipball_url']."';
    </script>";
                exit();
            }
        }
        writeError('Unable to check releases, maybe you need to set proxy options?'.trim(curl_error($connection)));
    }
}

function bc_check()
{
    $path = dirname(__FILE__).DIRECTORY_SEPARATOR.$_SESSION['__bc_netinstall__']['destination'];
    if(file_exists($path.'/ready'))
        return true;
    return false;
}

function bc_do_download()
{
    $connection = init_client();

    $dlurl = $_SESSION['__bc_netinstall__']['source'];
    $path  = dirname(__FILE__).DIRECTORY_SEPARATOR.$_SESSION['__bc_netinstall__']['destination'];
    curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($connection, CURLOPT_MAXREDIRS, 2);
    curl_setopt($connection, CURLOPT_URL, $dlurl);
    $data = curl_exec($connection);
    if(curl_error($connection))
    {
        writeError(trim(curl_error($connection)));
    }
    if(curl_getinfo($connection,CURLINFO_HTTP_CODE)==302) // handle redirect
    {
        preg_match('/Location:(.*?)\n/', $data, $matches);
        $newUrl = trim(array_pop($matches));
        curl_setopt($connection, CURLOPT_URL, $newUrl);
        $data  = curl_exec($connection);
        if(curl_error($connection))
        {
            writeError(trim(curl_error($connection)));
        }
    }
    
    if(!$data || curl_error($connection)) {
        writeError(trim(curl_error($connection)));
    }

    if(!is_dir($path)) mkdir($path,0770);
    $fd = fopen($path.'/blackcat.zip', 'w');
    fwrite($fd, $data);
    fclose($fd);
    $lg = fopen($path.'/ready','w');
    fwrite($lg,print_r($_SESSION,1));
    fclose($lg);
}

function bc_unpack()
{
    $path  = dirname(__FILE__).DIRECTORY_SEPARATOR.$_SESSION['__bc_netinstall__']['destination'];
    $uz    = new fileUnzip($path.DIRECTORY_SEPARATOR."blackcat.zip");
    $files = $uz->getList();
    if (!is_array($files) or count($files) == 0) {
        writeError('Invalid zip file.');
    } else {
        foreach ($files as $k => $v) {
            if(!preg_match('#/upload/#',$k))             continue;
            if(pathinfo($k,PATHINFO_EXTENSION)=='empty') continue;
            $t = preg_replace('#^[^/]*/upload/#','./'.$_SESSION['__bc_netinstall__']['destination'].'/',$k);
            $t = str_replace('//','/',$t);
            if ($v['is_dir']) {
                if(!is_dir($t)) {
                    mkdir(sanitizePath($path.'/'.$t),0775);
                }
                continue;
            }
            if($v['compressed_size']==0) {
                files::touch(sanitizePath($path.'/'.$t));
                continue;
            }
            $uz->unzip($k,$t);
        }
    }
    $uz->close();
    unset($uz);
    echo "
    <script charset=\"iso-8859-1\" type=\"text/javascript\">
        location.href = '".CURRENT_URL."?step=4';
    </script>";
    exit();

}


switch($step)
{
    // precheck
    case 1:
        precheck();
        break;

    // download
    case 2:
        download();
        break;

    // unzip
    case 3:
        bc_unpack();
        break;

    // forward to installer
    case 4:
        echo "<div>",
             translate("You will be forwarded to the Installation Wizard now."),
             "<br />
<script charset=\"iso-8859-1\" type=\"text/javascript\">
    function forward_to_installer() {
		location.href= '".pathinfo(CURRENT_URL,PATHINFO_DIRNAME).'/'.$_SESSION['__bc_netinstall__']['destination'].'/install/index.php'."';
	}
	window.setTimeout('forward_to_installer()', 2000); // in msecs 1000 => eine Sekunde
</script>
";

        break;
}

function sanitizePath($path)
{
    $path       = preg_replace( '~/{1,}$~', '', $path );
	$path       = str_replace( '\\', '/', $path );
    $path       = preg_replace('~/\./~', '/', $path);
    $parts      = array();
    foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part ) {
        if ($part === ".." || $part == '') {
            array_pop($parts);
        }
        elseif ($part!="") {
            $parts[] = $part;
        }
    }
    $new_path = implode("/", $parts);
    // windows
    if ( ! preg_match( '/^[a-z]\:/i', $new_path ) ) {
		$new_path = '/' . $new_path;
	}
    return $new_path;
}

# ***** BEGIN LICENSE BLOCK *****
# This file is part of Clearbricks.
# Copyright (c) 2008 Olivier Meunier and contributors.
# All rights reserved.
#
# Clearbricks is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Clearbricks is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Clearbricks; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class fileUnzip
{
	protected $file_name;
	protected $compressed_list = array();
	protected $eo_central = array();

	protected $zip_sig   = "\x50\x4b\x03\x04"; # local file header signature
	protected $dir_sig   = "\x50\x4b\x01\x02"; # central dir header signature
	protected $dir_sig_e = "\x50\x4b\x05\x06"; # end of central dir signature
	protected $fp = null;

	protected $memory_limit = null;

	public function __construct($file_name)
	{
		$this->file_name = $file_name;
	}

	public function __destroy()
	{
		$this->close();
	}

	public function close()
	{
		if ($this->fp) {
			fclose($this->fp);
		}

		if ($this->memory_limit) {
			ini_set('memory_limit',$this->memory_limit);
		}
	}

	public function getList($stop_on_file=false,$exclude=false)
	{
		if (!empty($this->compressed_list)) {
			return $this->compressed_list;
		}

		if (!$this->loadFileListByEOF($stop_on_file,$exclude)) {
			if(!$this->loadFileListBySignatures($stop_on_file,$exclude)) {
				return false;
			}
		}

		return $this->compressed_list;
	}

	public function unzipAll($target)
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		foreach ($this->compressed_list as $k => $v)
		{
			if ($v['is_dir']) {
				continue;
			}

			$this->unzip($k,$target.'/'.$k);
		}
	}

	public function unzip($file_name,$target=false)
	{
		if (empty($this->compressed_list)) {
			$this->getList($file_name);
		}

		if (!isset($this->compressed_list[$file_name])) {
			throw new Exception(sprintf('File %s is not compressed in the zip.'),$file_name);
		}
		$details =& $this->compressed_list[$file_name];

		if ($details['is_dir']) {
			throw new Exception(sprintf('Trying to unzip a folder name %s',$file_name));
		}

		if (!$details['uncompressed_size']) {
			return $this->putContent('',$target);
		}

		if ($target) {
			$this->testTargetDir(dirname($target));
		}

		fseek($this->fp(),$details['contents_start_offset']);

		$this->memoryAllocate($details['compressed_size']);
		return $this->uncompress(
			fread($this->fp(), $details['compressed_size']),
			$details['compression_method'],
			$details['uncompressed_size'],
			$target
		);
	}

	public function getFilesList()
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		$res = array();
		foreach ($this->compressed_list as $k => $v) {
			if (!$v['is_dir']) {
				$res[] = $k;
			}
		}
		return $res;
	}

	public function getDirsList()
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		$res = array();
		foreach ($this->compressed_list as $k => $v) {
			if ($v['is_dir']) {
				$res[] = substr($k,0,-1);
			}
		}
		return $res;
	}

	public function getRootDir()
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		$files = $this->getFilesList();
		$dirs = $this->getDirsList();

		$root_files = 0;
		$root_dirs = 0;
		foreach ($files as $v) { if (strpos($v,'/') === false) { $root_files++; }}
		foreach ($dirs as $v)  { if (strpos($v,'/') === false) { $root_dirs++;  }}

		if ($root_files == 0 && $root_dirs == 1) {
			return $dirs[0];
		} else {
			return false;
		}
	}

	public function isEmpty()
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		return count($this->compressed_list) == 0;
	}

	public function hasFile($f)
	{
		if (empty($this->compressed_list)) {
			$this->getList();
		}

		return isset($this->compressed_list[$f]);
	}

	protected function fp()
	{
		if ($this->fp === null) {
			$this->fp = @fopen($this->file_name,'rb');
		}

		if ($this->fp === false) {
			throw new Exception('Unable to open file.');
		}

		return $this->fp;
	}

	protected function putContent($content,$target=false)
	{
		if ($target) {
			$r = @file_put_contents($target,$content);
			if (!$r) {
				throw new Exception('Unable to write destination file. '.$target);
			}
			chmod($target,fileperms(dirname($target)));
			return true;
		}
		return $content;
	}

	protected function testTargetDir($dir)
	{
		if (is_dir($dir) && !is_writable($dir)) {
			throw new Exception('Unable to write in target directory, permission denied.');
		}

		if (!is_dir($dir)) {
			files::makeDir($dir,true);
		}
	}

	protected function uncompress($content,$mode,$size,$target=false)
	{
		switch ($mode)
		{
			case 0:
				# Not compressed
				$this->memoryAllocate($size*2);
				return $this->putContent($content,$target);
			case 1:
				throw new Exception('Shrunk mode is not supported.');
			case 2:
			case 3:
			case 4:
			case 5:
				throw new Exception('Compression factor '.($mode-1).' is not supported.');
			case 6:
				throw new Exception('Implode is not supported.');
			case 7:
				throw new Exception('Tokenizing compression algorithm is not supported.');
			case 8:
				# Deflate
				if (!function_exists('gzinflate')) {
					throw new Exception('Gzip functions are not available.');
				}
				$this->memoryAllocate($size*2);
				return $this->putContent(gzinflate($content,$size),$target);
			case 9:
				throw new Exception('Enhanced Deflating is not supported.');
			case 10:
				throw new Exception('PKWARE Date Compression Library Impoloding is not supported.');
			case 12:
				# Bzip2
				if (!function_exists('bzdecompress')) {
					throw new Exception('Bzip2 functions are not available.');
				}
				$this->memoryAllocate($size*2);
				return $this->putContent(bzdecompress($content),$target,$chmod);
			case 18:
				throw new Exception('IBM TERSE is not supported.');
			default:
				throw new Exception('Unknown uncompress method');
		}
	}

	protected function loadFileListByEOF($stop_on_file=false,$exclude=false)
	{
		$fp = $this->fp();

		for ($x=0; $x<1024; $x++)
		{
			fseek($fp,-22-$x,SEEK_END);
			$signature = fread($fp,4);

			if ($signature == $this->dir_sig_e)
			{
				$dir_list = array();

				$eodir = array(
					'disk_number_this'   => unpack('v', fread($fp,2)),
					'disk_number'        => unpack('v', fread($fp,2)),
					'total_entries_this' => unpack('v', fread($fp,2)),
					'total_entries'      => unpack('v', fread($fp,2)),
					'size_of_cd'         => unpack('V', fread($fp,4)),
					'offset_start_cd'    => unpack('V', fread($fp,4))
				);

				$zip_comment_len = unpack('v', fread($fp,2));
                if(is_array($zip_comment_len)) $zip_comment_len = $zip_comment_len[1];
				$eodir['zipfile_comment'] = $zip_comment_len[1] ? fread($fp,$zip_comment_len) : '';

				$this->eo_central = array(
					'disk_number_this'   => $eodir['disk_number_this'][1],
					'disk_number'        => $eodir['disk_number'][1],
					'total_entries_this' => $eodir['total_entries_this'][1],
					'total_entries'      => $eodir['total_entries'][1],
					'size_of_cd'         => $eodir['size_of_cd'][1],
					'offset_start_cd'    => $eodir['offset_start_cd'][1],
					'zipfile_comment'    => $eodir['zipfile_comment']
				);

				fseek($fp, $this->eo_central['offset_start_cd']);
				$signature = fread($fp,4);

				while ($signature == $this->dir_sig)
				{
					$dir = array();
					$dir['version_madeby']       = unpack("v",fread($fp, 2)); # version made by
					$dir['version_needed']       = unpack("v",fread($fp, 2)); # version needed to extract
					$dir['general_bit_flag']     = unpack("v",fread($fp, 2)); # general purpose bit flag
					$dir['compression_method']   = unpack("v",fread($fp, 2)); # compression method
					$dir['lastmod_time']         = unpack("v",fread($fp, 2)); # last mod file time
					$dir['lastmod_date']         = unpack("v",fread($fp, 2)); # last mod file date
					$dir['crc-32']               = fread($fp,4);              # crc-32
					$dir['compressed_size']      = unpack("V",fread($fp, 4)); # compressed size
					$dir['uncompressed_size']    = unpack("V",fread($fp, 4)); # uncompressed size

					$file_name_len               = unpack("v",fread($fp, 2)); # filename length
					$extra_field_len             = unpack("v",fread($fp, 2)); # extra field length
					$file_comment_len            = unpack("v",fread($fp, 2)); # file comment length

					$dir['disk_number_start']    = unpack("v",fread($fp, 2)); # disk number start
					$dir['internal_attributes']  = unpack("v",fread($fp, 2)); # internal file attributes-byte1
					$dir['external_attributes1'] = unpack("v",fread($fp, 2)); # external file attributes-byte2
					$dir['external_attributes2'] = unpack("v",fread($fp, 2)); # external file attributes
					$dir['relative_offset']      = unpack("V",fread($fp, 4)); # relative offset of local header
					$dir['file_name']            = $this->cleanFileName(fread($fp, $file_name_len[1]));          # filename
					$dir['extra_field']          = $extra_field_len[1] ? fread($fp, $extra_field_len[1]) : '';   # extra field
					$dir['file_comment']         = $file_comment_len[1] ? fread($fp, $file_comment_len[1]) : ''; # file comment

					$dir_list[$dir['file_name']] = array(
						'version_madeby'       => $dir['version_madeby'][1],
						'version_needed'       => $dir['version_needed'][1],
						'general_bit_flag'     => str_pad(decbin($dir['general_bit_flag'][1]), 8, '0', STR_PAD_LEFT),
						'compression_method'   => $dir['compression_method'][1],
						'lastmod_datetime'     => $this->getTimeStamp($dir['lastmod_date'][1],$dir['lastmod_time'][1]),
						'crc-32'               => str_pad(dechex(ord($dir['crc-32'][3])), 2, '0', STR_PAD_LEFT).
											 str_pad(dechex(ord($dir['crc-32'][2])), 2, '0', STR_PAD_LEFT).
											 str_pad(dechex(ord($dir['crc-32'][1])), 2, '0', STR_PAD_LEFT).
											 str_pad(dechex(ord($dir['crc-32'][0])), 2, '0', STR_PAD_LEFT),
						'compressed_size'      => $dir['compressed_size'][1],
						'uncompressed_size'    => $dir['uncompressed_size'][1],
						'disk_number_start'    => $dir['disk_number_start'][1],
						'internal_attributes'  => $dir['internal_attributes'][1],
						'external_attributes1' => $dir['external_attributes1'][1],
						'external_attributes2' => $dir['external_attributes2'][1],
						'relative_offset'      => $dir['relative_offset'][1],
						'file_name'            => $dir['file_name'],
						'extra_field'          => $dir['extra_field'],
						'file_comment'         => $dir['file_comment']
					);
					$signature = fread($fp, 4);
				}

				foreach ($dir_list as $k => $v)
				{
					if ($exclude && preg_match($exclude,$k)) {
						continue;
					}

					$i = $this->getFileHeaderInformation($v['relative_offset']);

					$this->compressed_list[$k]['file_name']            = $k;
					$this->compressed_list[$k]['is_dir']               = $v['external_attributes1'] == 16 || substr($k,-1,1) == '/';
					$this->compressed_list[$k]['compression_method']   = $v['compression_method'];
					$this->compressed_list[$k]['version_needed']       = $v['version_needed'];
					$this->compressed_list[$k]['lastmod_datetime']     = $v['lastmod_datetime'];
					$this->compressed_list[$k]['crc-32']               = $v['crc-32'];
					$this->compressed_list[$k]['compressed_size']      = $v['compressed_size'];
					$this->compressed_list[$k]['uncompressed_size']    = $v['uncompressed_size'];
					$this->compressed_list[$k]['lastmod_datetime']     = $v['lastmod_datetime'];
					$this->compressed_list[$k]['extra_field']          = $i['extra_field'];
					$this->compressed_list[$k]['contents_start_offset'] = $i['contents_start_offset'];

					if(strtolower($stop_on_file) == strtolower($k)) {
						break;
					}
				}
				return true;
			}
		}
		return false;
	}

	protected function loadFileListBySignatures($stop_on_file=false,$exclude=false)
	{
		$fp = $this->fp();
		fseek($fp,0);

		$return = false;
		while(true)
		{
			$details = $this->getFileHeaderInformation();
			if (!$details) {
				fseek($fp,12-4,SEEK_CUR); # 12: Data descriptor - 4: Signature (that will be read again)
				$details = $this->getFileHeaderInformation();
			}
			if (!$details) {
				break;
			}
			$filename = $details['file_name'];

			if ($exclude && preg_match($exclude,$filename)) {
				continue;
			}

			$this->compressed_list[$filename] = $details;
			$return = true;

			if (strtolower($stop_on_file) == strtolower($filename)) {
				break;
			}
		}

		return $return;
	}

	protected function getFileHeaderInformation($start_offset=false)
	{
		$fp = $this->fp();

		if ($start_offset !== false) {
			fseek($fp,$start_offset);
		}

		$signature = fread($fp, 4);
		if ($signature == $this->zip_sig)
		{
			# Get information about the zipped file
			$file = array();
			$file['version_needed']        = unpack("v",fread($fp, 2)); # version needed to extract
			$file['general_bit_flag']      = unpack("v",fread($fp, 2)); # general purpose bit flag
			$file['compression_method']    = unpack("v",fread($fp, 2)); # compression method
			$file['lastmod_time']          = unpack("v",fread($fp, 2)); # last mod file time
			$file['lastmod_date']          = unpack("v",fread($fp, 2)); # last mod file date
			$file['crc-32']                = fread($fp,4);              # crc-32
			$file['compressed_size']       = unpack("V",fread($fp, 4)); # compressed size
			$file['uncompressed_size']     = unpack("V",fread($fp, 4)); # uncompressed size

			$file_name_len                 = unpack("v",fread($fp, 2)); # filename length
			$extra_field_len               = unpack("v",fread($fp, 2)); # extra field length

			$file['file_name']             = $this->cleanFileName(fread($fp,$file_name_len[1])); # filename
			$file['extra_field']           = $extra_field_len[1] ? fread($fp, $extra_field_len[1]) : ''; # extra field
			$file['contents_start_offset'] = ftell($fp);

			# Look for the next file
			fseek($fp, $file['compressed_size'][1], SEEK_CUR);

			# Mount file table
			$i = array(
				'file_name'            => $file['file_name'],
				'is_dir'               => substr($file['file_name'],-1,1) == '/',
				'compression_method'   => $file['compression_method'][1],
				'version_needed'       => $file['version_needed'][1],
				'lastmod_datetime'     => $this->getTimeStamp($file['lastmod_date'][1],$file['lastmod_time'][1]),
				'crc-32'               => str_pad(dechex(ord($file['crc-32'][3])), 2, '0', STR_PAD_LEFT).
									 str_pad(dechex(ord($file['crc-32'][2])), 2, '0', STR_PAD_LEFT).
									 str_pad(dechex(ord($file['crc-32'][1])), 2, '0', STR_PAD_LEFT).
									 str_pad(dechex(ord($file['crc-32'][0])), 2, '0', STR_PAD_LEFT),
				'compressed_size'      => $file['compressed_size'][1],
				'uncompressed_size'    => $file['uncompressed_size'][1],
				'extra_field'          => $file['extra_field'],
				'general_bit_flag'     => str_pad(decbin($file['general_bit_flag'][1]), 8, '0', STR_PAD_LEFT),
				'contents_start_offset'=>$file['contents_start_offset']
			);
			return $i;
		}
		return false;
	}

	protected function getTimeStamp($date,$time)
	{
		$BINlastmod_date = str_pad(decbin($date), 16, '0', STR_PAD_LEFT);
		$BINlastmod_time = str_pad(decbin($time), 16, '0', STR_PAD_LEFT);
		$lastmod_dateY   = bindec(substr($BINlastmod_date,  0, 7))+1980;
		$lastmod_dateM   = bindec(substr($BINlastmod_date,  7, 4));
		$lastmod_dateD   = bindec(substr($BINlastmod_date, 11, 5));
		$lastmod_timeH   = bindec(substr($BINlastmod_time,   0, 5));
		$lastmod_timeM   = bindec(substr($BINlastmod_time,   5, 6));
		$lastmod_timeS   = bindec(substr($BINlastmod_time,  11, 5)) * 2;

		return mktime($lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY);
	}

	protected function cleanFileName($n)
	{
		$n = str_replace('../','',$n);
		$n = preg_replace('#^/+#','',$n);
		return $n;
	}

	protected function memoryAllocate($size)
	{
		$mem_used = @memory_get_usage();
		$mem_limit = @ini_get('memory_limit');
		if ($mem_used && $mem_limit)
		{
			$mem_limit = files::str2bytes($mem_limit);
			$mem_avail = $mem_limit-$mem_used-(512*1024);
			$mem_needed = $size;

			if ($mem_needed > $mem_avail)
			{
				if (@ini_set('memory_limit',$mem_limit+$mem_needed+$mem_used) === false) {
					throw new Exception('Not enough memory to open file.');
				}

				if (!$this->memory_limit) {
					$this->memory_limit = $mem_limit;
				}
			}
		}
	}
}

# ***** BEGIN LICENSE BLOCK *****
# This file is part of Clearbricks.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
# reserved.
#
# Clearbricks is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# Clearbricks is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Clearbricks; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class files
{
	public static function scandir($d,$order=0)
	{
		$res = array();
		$dh = @opendir($d);

		if ($dh === false) {
			throw new Exception('Unable to open directory.');
		}

		while (($f = readdir($dh)) !== false) {
			$res[] = $f;
		}
		closedir($dh);

		sort($res);
		if ($order == 1) {
			rsort($res);
		}

		return $res;
	}

	public static function getExtension($f)
	{
		$f = explode('.',basename($f));

		if (count($f) <= 1) { return ''; }

		return strtolower($f[count($f)-1]);
	}

	public static function getMimeType($f)
	{
		$ext = self::getExtension($f);
		$types = self::mimeTypes();

		if (isset($types[$ext])) {
			return $types[$ext];
		} else {
			return 'text/plain';
		}
	}

	public static function mimeTypes()
	{
		return array(
			'odt'	=> 'application/vnd.oasis.opendocument.text',
			'odp'	=> 'application/vnd.oasis.opendocument.presentation',
			'ods'	=> 'application/vnd.oasis.opendocument.spreadsheet',

			'sxw'	=> 'application/vnd.sun.xml.writer',
			'sxc'	=> 'application/vnd.sun.xml.calc',
			'sxi'	=> 'application/vnd.sun.xml.impress',

			'ppt' 	=> 'application/mspowerpoint',
			'doc'	=> 'application/msword',
			'xls'	=> 'application/msexcel',
			'rtf'	=> 'application/rtf',

			'pdf'	=> 'application/pdf',
			'ps'		=> 'application/postscript',
			'ai'		=> 'application/postscript',
			'eps'	=> 'application/postscript',

			'bin'	=> 'application/octet-stream',
			'exe'	=> 'application/octet-stream',

			'deb'	=> 'application/x-debian-package',
			'gz'		=> 'application/x-gzip',
			'jar'	=> 'application/x-java-archive',
			'rar'	=> 'application/rar',
			'rpm'	=> 'application/x-redhat-package-manager',
			'tar'	=> 'application/x-tar',
			'tgz'	=> 'application/x-gtar',
			'zip'	=> 'application/zip',

			'aiff'	=> 'audio/x-aiff',
			'ua'		=> 'audio/basic',
			'mp3'	=> 'audio/mpeg3',
			'mid'	=> 'audio/x-midi',
			'midi'	=> 'audio/x-midi',
			'ogg'	=> 'application/ogg',
			'wav'	=> 'audio/x-wav',

			'swf'	=> 'application/x-shockwave-flash',
			'swfl'	=> 'application/x-shockwave-flash',

			'bmp'	=> 'image/bmp',
			'gif'	=> 'image/gif',
			'jpeg'	=> 'image/jpeg',
			'jpg'	=> 'image/jpeg',
			'jpe'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'tiff'	=> 'image/tiff',
			'tif'	=> 'image/tiff',
			'xbm'	=> 'image/x-xbitmap',

			'css'	=> 'text/css',
			'js'		=> 'text/javascript',
			'html'	=> 'text/html',
			'htm'	=> 'text/html',
			'txt'	=> 'text/plain',
			'rtf'	=> 'text/richtext',
			'rtx'	=> 'text/richtext',

			'mpg'	=> 'video/mpeg',
			'mpeg'	=> 'video/mpeg',
			'mpe'	=> 'video/mpeg',
			'viv'	=> 'video/vnd.vivo',
			'vivo'	=> 'video/vnd.vivo',
			'qt'		=> 'video/quicktime',
			'mov'	=> 'video/quicktime',
			'flv'	=> 'video/x-flv',
			'avi'	=> 'video/x-msvideo'
		);
	}

	public static function isDeletable($f)
	{
		if (is_file($f)) {
			return is_writable(dirname($f));
		} elseif (is_dir($f)) {
			return (is_writable(dirname($f)) && count(files::scandir($f)) <= 2);
		}
	}

	# Recusive remove (rm -rf)
	public static function deltree($dir)
	{
		$current_dir = opendir($dir);
		while($entryname = readdir($current_dir))
		{
			if (is_dir($dir.'/'.$entryname) and ($entryname != '.' and $entryname!='..'))
			{
				if (!files::deltree($dir.'/'.$entryname)) {
					return false;
				}
			}
			elseif ($entryname != '.' and $entryname!='..')
			{
				if (!@unlink($dir.'/'.$entryname)) {
					return false;
				}
			}
		}
		closedir($current_dir);
		return @rmdir($dir);
	}

	public static function touch($f)
	{
		#if (is_writable($f)) {
			if (function_exists('touch')) {
				touch($f);
			} else {
				# Very bad hack
				file_put_contents($f,file_get_contents($f));
			}
		#}
	}

	public static function makeDir($f,$r=false)
	{
		if (empty($f)) {
			return;
		}

		if (DIRECTORY_SEPARATOR == '\\') {
			$f = str_replace('/','\\',$f);
		}

		if (is_dir($f)) {
			return;
		}

		if ($r)
		{
			$dir = path::real($f,false);
			$dirs = array();

			while (!is_dir($dir)) {
				array_unshift($dirs,basename($dir));
				$dir = dirname($dir);
			}

			foreach ($dirs as $d)
			{
				$dir .= DIRECTORY_SEPARATOR.$d;
				if ($d != '' && !is_dir($dir)) {
					self::makeDir($dir);
				}
			}
		}
		else
		{
			if (@mkdir($f) === false) {
				throw new Exception('Unable to create directory.');
			}
			chmod($f,fileperms(dirname($f)));
		}
	}

	public static function putContent($f, $f_content)
	{
		if (file_exists($f) && !is_writable($f)) {
			throw new Exception('File is not writable.');
		}

		$fp = @fopen($f, 'w');

		if ($fp === false) {
			throw new Exception('Unable to open file.');
		}

		fwrite($fp,$f_content,strlen($f_content));
		fclose($fp);
		return true;
	}

	public static function size($size)
	{
		$kb = 1024;
		$mb = 1024 * $kb;
		$gb = 1024 * $mb;
		$tb = 1024 * $gb;

		if($size < $kb) {
			return $size." B";
		}
		else if($size < $mb) {
			return round($size/$kb,2)." KB";
		}
		else if($size < $gb) {
			return round($size/$mb,2)." MB";
		}
		else if($size < $tb) {
			return round($size/$gb,2)." GB";
		}
		else {
			return round($size/$tb,2)." TB";
		}
	}

	public static function str2bytes($v)
	{
		$v = trim($v);
		$last = strtolower(substr($v,-1,1));

		switch($last)
		{
			case 'g':
				$v *= 1024;
			case 'm':
				$v *= 1024;
			case 'k':
				$v *= 1024;
		}

		return $v;
	}

	public static function uploadStatus($file)
	{
		if (!isset($file['error'])) {
			throw new Exception('Not an uploaded file.');
		}

		switch ($file['error']) {
			case UPLOAD_ERR_OK:
				return true;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new Exception('The uploaded file exceeds the maximum file size allowed.');
				return false;
			case UPLOAD_ERR_PARTIAL:
				throw new Exception('The uploaded file was only partially uploaded.');
				return false;
			case UPLOAD_ERR_NO_FILE:
				throw new Exception('No file was uploaded.');
				return false;
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Exception('Missing a temporary folder.');
				return false;
			case UPLOAD_ERR_CANT_WRITE:
				throw new Exception('Failed to write file to disk.');
				return false;
			default:
				return true;
		}
	}

	# Packages generation methods
	#
	public static function getDirList($dirName, &$contents = null)
	{
		if (!$contents) {
			$contents = array('dirs'=> array(),'files' => array());
		}

		$exclude_list=array('.','..','.svn');

		if (empty($res)) {
			$res = array();
		}

		$dirName = preg_replace('|/$|','',$dirName);

		if (!is_dir($dirName)) {
			throw new Exception(sprintf('%s is not a directory.'),$dirName);
		}

		$contents['dirs'][] = $dirName;

		$d = @dir($dirName);

		if ($d === false) {
			throw new Exception('Unable to open directory.');
		}

		while($entry = $d->read())
		{
			if (!in_array($entry,$exclude_list))
			{
				if (is_dir($dirName.'/'.$entry))
				{
					files::getDirList($dirName.'/'.$entry, $contents);
				}
				else
				{
					$contents['files'][] = $dirName.'/'.$entry;
				}
			}
		}
		$d->close();

		return $contents;
	}

	public static function makePackage($name,$dir,$remove_path='',$gzip=true)
	{
		if ($gzip && !function_exists('gzcompress')) {
			throw new Exception('No compression functions');
		}

		if (($filelist = files::getDirList($dir)) === false) {
			throw new Exception('Unable to list directory.');
		}

		$res = array ('name' => $name, 'dirs' => array(), 'files' => array());

		foreach ($filelist['dirs'] as $v) {
			$res['dirs'][] = preg_replace('/^'.preg_quote($remove_path,'/').'/','',$v);
		}

		foreach ($filelist['files'] as $v) {
			$f_content = base64_encode(file_get_contents($v));
			$v = preg_replace('/^'.preg_quote($remove_path,'/').'/','',$v);
			$res['files'][$v] = $f_content;
		}

		$res = serialize($res);

		if ($gzip) {
			$res = gzencode($res);
		}

		return $res;
	}

	public static function installPackage($package,$with_remove=true)
	{
		$dest_path = dirname($package);

		if (($content = @implode('',@gzfile($package))) === false) {
			if ($with_remove) {
				unlink($package);
			}
			throw new Exception('Cannot open file.');
		}

		if (($list = @unserialize($content)) === false) {
			if ($with_remove) {
				unlink($package);
			}
			throw new Exception('Package not valid.');
		}
		unset($content);

		if (is_dir($dest_path.'/'.$list['name'])) {
			unlink($package);
			throw new Exception('This package is already installed. Delete it before.');
		}

		foreach ($list['dirs'] as $d) {
			mkdir($dest_path.'/'.$d,fileperms($dest_path));
			chmod($dest_path.'/'.$d,fileperms($dest_path));
		}

		foreach ($list['files'] as $f => $v) {
			$v = base64_decode($v);
			$fp = fopen($dest_path.'/'.$f,'w');
			fwrite($fp,$v,strlen($v));
			fclose($fp);
			chmod($dest_path.'/'.$f,fileperms($dest_path) & ~0111);
		}

		unlink($package);

		return true;
	}

	public static function tidyFileName($n)
	{
		$n = text::deaccent($n);
		$n = preg_replace('/^[.]/u','',$n);
		return preg_replace('/[^A-Za-z0-9._-]/u','_',$n);
	}
}


class path
{
	public static function real($p,$strict=true)
	{
		$os = (DIRECTORY_SEPARATOR == '\\') ? 'win' : 'nix';

		# Absolute path?
		if ($os == 'win') {
			$_abs = preg_match('/^\w+:/',$p);
		} else {
			$_abs = substr($p,0,1) == '/';
		}

		# Standard path form
		if ($os == 'win') {
			$p = str_replace('\\','/',$p);
		}

		# Adding root if !$_abs
		if (!$_abs) {
			$p = dirname($_SERVER['SCRIPT_FILENAME']).'/'.$p;
		}

		# Clean up
		$p = preg_replace('|/+|','/',$p);

		if (strlen($p) > 1) {
			$p = preg_replace('|/$|','',$p);
		}

		$_start = '';
		if ($os == 'win') {
			list($_start,$p) = explode(':',$p);
			$_start .= ':/';
		} else {
			$_start = '/';
		}
		$p = substr($p,1);

		# Go through
		$P = explode('/',$p);
		$res = array();

		for ($i=0;$i<count($P);$i++)
		{
			if ($P[$i] == '.') {
				continue;
			}

			if ($P[$i] == '..') {
				if (count($res) > 0) {
					array_pop($res);
				}
			} else {
				array_push($res,$P[$i]);
			}
		}

		$p = $_start.implode('/',$res);

		if ($strict && !@file_exists($p)) {
			return false;
		}

		return $p;
	}

	public static function clean($p)
	{
		$p = str_replace('..','',$p);
		$p = preg_replace('|/{2,}|','/',$p);
		$p = preg_replace('|/$|','',$p);

		return $p;
	}

	public static function info($f)
	{
		$p = pathinfo($f);
		$res = array();

		$res['dirname'] = $p['dirname'];
		$res['basename'] = $p['basename'];
		$res['extension'] = isset($p['extension']) ? $p['extension'] : '';
		$res['base'] = preg_replace('/\.'.preg_quote($res['extension'],'/').'$/','',$res['basename']);

		return $res;
	}

	public static function fullFromRoot($p,$root)
	{
		if (substr($p,0,1) == '/') {
			return $p;
		}

		return $root.'/'.$p;
	}
}