<?PHP
	
	class Core
	{
	
		function set_option($key, $val)
		{
			$db = Database::getInstance();
			$db->query('REPLACE INTO options (`key`, `value`) VALUES (:key:, :value:)', array('key' => $key, 'value' => $val));
		}

		function get_option($key, $default = null)
		{
			$db = Database::getInstance();
			$db->query('SELECT `value` FROM options WHERE `key` = :key:', array('key' => $key));
			if($db->hasRows())
				return $db->getValue();
			else
				return $default;
		}

		function delete_option($key)
		{
			$db = Database::getInstance();
			$db->query('DELETE FROM options WHERE `key` = :key:', array('key' => $key));
			return $db->affectedRows();
		}

		function printr($var)
		{
			$output = print_r($var, true);
			$output = str_replace("\n", "<br>", $output);
			$output = str_replace(' ', '&nbsp;', $output);
			echo "<div style='font-family:courier;'>$output</div>";
		}

		// Formats a given number of seconds into proper mm:ss format
		function format_time($seconds)
		{
			return floor($seconds / 60) . ':' . str_pad($seconds % 60, 2, '0');
		}

		// Given a string such as "comment_123" or "id_57", it returns the final, numeric id.
		function split_id($str)
		{
			return match('/[_-]([0-9]+)$/', $str, 1);
		}

		// Creates a friendly URL slug from a string
		function slugify($str)
		{
			$str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
			$str = strtolower(str_replace(' ', '-', trim($str)));
			$str = preg_replace('/-+/', '-', $str);
			return $str;
		}

		// Computes the *full* URL of the current page (protocol, server, path, query parameters, etc)
		function full_url()
		{
			$s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
			$protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
			$port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
			return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
		}

		// Returns an English representation of a date
		// Graciously stolen from http://ejohn.org/files/pretty.js
		function time2str($ts)
		{
			if(!ctype_digit($ts))
				$ts = strtotime($ts);

			$diff = time() - $ts;
			if($diff == 0)
				return 'now';
			elseif($diff > 0)
			{
				$day_diff = floor($diff / 86400);
				if($day_diff == 0)
				{
					if($diff < 60) return 'just now';
					if($diff < 120) return '1 minute ago';
					if($diff < 3600) return floor($diff / 60) . ' minutes ago';
					if($diff < 7200) return '1 hour ago';
					if($diff < 86400) return floor($diff / 3600) . ' hours ago';
				}
				if($day_diff == 1) return 'Yesterday';
				if($day_diff < 7) return $day_diff . ' days ago';
				if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
				if($day_diff < 60) return 'last month';
				$ret = date('F Y', $ts);
				return ($ret == 'December 1969') ? '' : $ret;
			}
			else
			{
				$diff = abs($diff);
				$day_diff = floor($diff / 86400);
				if($day_diff == 0)
				{
					if($diff < 120) return 'in a minute';
					if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
					if($diff < 7200) return 'in an hour';
					if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
				}
				if($day_diff == 1) return 'Tomorrow';
				if($day_diff < 4) return date('l', $ts);
				if($day_diff < 7 + (7 - date('w'))) return 'next week';
				if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
				if(date('n', $ts) == date('n') + 1) return 'next month';
				$ret = date('F Y', $ts);
				return ($ret == 'December 1969') ? '' : $ret;
			}
		}

		// Returns an array representation of the given calendar month.
		// The array values are timestamps which allow you to easily format
		// and manipulate the dates as needed.
		function calendar($month = null, $year = null)
		{
			if(is_null($month)) $month = date('n');
			if(is_null($year)) $year = date('Y');

			$first = mktime(0, 0, 0, $month, 1, $year);
			$last = mktime(23, 59, 59, $month, date('t', $first), $year);

			$start = $first - (86400 * date('w', $first));
			$stop = $last + (86400 * (7 - date('w', $first)));

			$out = array();
			while($start < $stop)
			{
				$week = array();
				if($start > $last) break;
				for($i = 0; $i < 7; $i++)
				{
					$week[$i] = $start;
					$start += 86400;
				}
				$out[] = $week;
			}

			return $out;
		}

		// Processes mod_rewrite URLs into key => value pairs
		// See .htacess for more info.
		function pick_off($grab_first = false, $sep = '/')
		{
			$ret = array();
			$arr = explode($sep, trim($_SERVER['REQUEST_URI'], $sep));
			if($grab_first) $ret[0] = array_shift($arr);
			while(count($arr) > 0)
				$ret[array_shift($arr)] = array_shift($arr);
			return (count($ret) > 0) ? $ret : false;
		}

		// Creates a list of <option>s from the given database table.
		// table name, column to use as value, column(s) to use as text, default value(s) to select (can accept an array of values), extra sql to limit results
		function get_options($table, $val, $text, $default = null, $sql = '')
		{
			$db = Database::getInstance(true);
			$out = '';

			$table = $db->escape($table);
			$rows = $db->getRows("SELECT * FROM `$table` $sql");
			foreach($rows as $row)
			{
				$the_text = '';
				if(!is_array($text)) $text = array($text); // Allows you to concat multiple fields for display
				foreach($text as $t)
					$the_text .= $row[$t] . ' ';
				$the_text = htmlspecialchars(trim($the_text));

				if(!is_null($default) && $row[$val] == $default)
					$out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>';
				elseif(is_array($default) && in_array($row[$val],$default))
					$out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>';
				else
					$out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '">' . $the_text . '</option>';
			}
			return $out;
		}

		// More robust strict date checking for string representations
		function chkdate($str)
		{
			// Requires PHP 5.2
			if(function_exists('date_parse'))
			{
				$info = date_parse($str);
				if($info !== false && $info['error_count'] == 0)
				{
					if(checkdate($info['month'], $info['day'], $info['year']))
						return true;
				}

				return false;
			}

			// Else, for PHP < 5.2
			return strtotime($str);
		}

		// Converts a date/timestamp into the specified format
		function dater($date = null, $format = null)
		{
			if(is_null($format))
				$format = 'Y-m-d H:i:s';

			if(is_null($date))
				$date = time();

			if(is_int($date))
				return date($format, $date);
			if(is_float($date))
				return date($format, $date);
			if(is_string($date)) {
				if(ctype_digit($date) === true)
					return date($format, $date);
				if((preg_match('/[^0-9.]/', $date) == 0) && (substr_count($date, '.') <= 1))
					return date($format, floatval($date));
				return date($format, strtotime($date));
			}
			
			// If $date is anything else, you're doing something wrong,
			// so just let PHP error out however it wants.
			return date($format, $date);
		}

		// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
		function format_phone($phone)
		{
			$phone = preg_replace("/[^0-9]/", '', $phone);

			if(strlen($phone) == 7)
				return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
			elseif(strlen($phone) == 10)
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
			else
				return $phone;
		}

		// Outputs hour, minute, am/pm dropdown boxes
		function hourmin($hid = 'hour', $mid = 'minute', $pid = 'ampm', $hval = null, $mval = null, $pval = null)
		{
			// Dumb hack to let you just pass in a timestamp instead
			if(func_num_args() == 1)
			{
				list($hval, $mval, $pval) = explode(' ', date('g i a', strtotime($hid)));
				$hid = 'hour';
				$mid = 'minute';
				$aid = 'ampm';
			}
			else
			{
				if(is_null($hval)) $hval = date('h');
				if(is_null($mval)) $mval = date('i');
				if(is_null($pval)) $pval = date('a');
			}

			$hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);
			$out = "<select name='$hid' id='$hid'>";
			foreach($hours as $hour)
				if(intval($hval) == intval($hour)) $out .= "<option value='$hour' selected>$hour</option>";
				else $out .= "<option value='$hour'>$hour</option>";
			$out .= "</select>";

			$minutes = array('00', 15, 30, 45);
			$out .= "<select name='$mid' id='$mid'>";
			foreach($minutes as $minute)
				if(intval($mval) == intval($minute)) $out .= "<option value='$minute' selected>$minute</option>";
				else $out .= "<option value='$minute'>$minute</option>";
			$out .= "</select>";

			$out .= "<select name='$pid' id='$pid'>";
			$out .= "<option value='am'>am</option>";
			if($pval == 'pm') $out .= "<option value='pm' selected>pm</option>";
			else $out .= "<option value='pm'>pm</option>";
			$out .= "</select>";

			return $out;
		}

		// Returns the HTML for a month, day, and year dropdown boxes.
		// You can set the default date by passing in a timestamp OR a parseable date string.
		// $prefix_ will be appened to the name/id's of each dropdown, allowing for multiple calls in the same form.
		// $output_format lets you specify which dropdowns appear and in what order.
		function mdy($date = null, $prefix = null, $output_format = 'm d y')
		{
			if(is_null($date)) $date = time();
			if(!ctype_digit($date)) $date = strtotime($date);
			if(!is_null($prefix)) $prefix .= '_';
			list($yval, $mval, $dval) = explode(' ', date('Y n j', $date));

			$month_dd = "<select name='{$prefix}month' id='{$prefix}month'>";
			for($i = 1; $i <= 12; $i++)
			{
				$selected = ($mval == $i) ? ' selected="selected"' : '';
				$month_dd .= "<option value='$i'$selected>" . date('F', mktime(0, 0, 0, $i, 1, 2000)) . "</option>";
			}
			$month_dd .= "</select>";

			$day_dd = "<select name='{$prefix}day' id='{$prefix}day'>";
			for($i = 1; $i <= 31; $i++)
			{
				$selected = ($dval == $i) ? ' selected="selected"' : '';
				$day_dd .= "<option value='$i'$selected>$i</option>";
			}
			$day_dd .= "</select>";

			$year_dd = "<select name='{$prefix}year' id='{$prefix}year'>";
			for($i = date('Y'); $i < date('Y') + 10; $i++)
			{
				$selected = ($yval == $i) ? ' selected="selected"' : '';
				$year_dd .= "<option value='$i'$selected>$i</option>";
			}
			$year_dd .= "</select>";

			$trans = array('m' => $month_dd, 'd' => $day_dd, 'y' => $year_dd);
			return strtr($output_format, $trans);
		}

		// Redirects user to $url
		function redirect($url = null)
		{
			if(is_null($url)) $url = $_SERVER['PHP_SELF'];
			header("Location: $url");
			exit();
		}

		// Ensures $str ends with a single /
		function slash($str)
		{
			return rtrim($str, '/') . '/';
		}

		// Ensures $str DOES NOT end with a /
		function unslash($str)
		{
			return rtrim($str, '/');
		}

		// Returns an array of the values of the specified column from a multi-dimensional array
		function gimme($arr, $key = null)
		{
			if(is_null($key))
				$key = current(array_keys($arr));

			$out = array();
			foreach($arr as $a)
				$out[] = $a[$key];

			return $out;
		}

		// Fixes MAGIC_QUOTES
		function fix_slashes($arr = '')
		{
			if(is_null($arr) || $arr == '') return null;
			if(!get_magic_quotes_gpc()) return $arr;
			return is_array($arr) ? array_map('fix_slashes', $arr) : stripslashes($arr);
		}

		// Returns the first $num words of $str
		function max_words($str, $num, $suffix = '')
		{
			$words = explode(' ', $str);
			if(count($words) < $num)
				return $str;
			else
				return implode(' ', array_slice($words, 0, $num)) . $suffix;
		}

		// Serves an external document for download as an HTTP attachment.
		function download_document($filename, $mimetype = 'application/octet-stream')
		{
			if(!file_exists($filename) || !is_readable($filename)) return false;
			$base = basename($filename);
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Disposition: attachment; filename=$base");
			header("Content-Length: " . filesize($filename));
			header("Content-Type: $mimetype");
			readfile($filename);
			exit();
		}

		// Retrieves the filesize of a remote file.
		function remote_filesize($url, $user = null, $pw = null)
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			if(!is_null($user) && !is_null($pw))
			{
				$headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			}

			$head = curl_exec($ch);
			curl_close($ch);

			preg_match('/Content-Length:\s([0-9].+?)\s/', $head, $matches);

			return isset($matches[1]) ? $matches[1] : false;
		}

		// Inserts a string within another string at a specified location
		function str_insert($needle, $haystack, $location)
		{
		   $front = substr($haystack, 0, $location);
		   $back  = substr($haystack, $location);

		   return $front . $needle . $back;
		}

		// Outputs a filesize in human readable format.
		function bytes2str($val, $round = 0)
		{
			$unit = array('','K','M','G','T','P','E','Z','Y');
			while($val >= 1000)
			{
				$val /= 1024;
				array_shift($unit);
			}
			return round($val, $round) . array_shift($unit) . 'B';
		}

		// Tests for a valid email address and optionally tests for valid MX records, too.
		function valid_email($email, $test_mx = false)
		{
			if(preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email))
			{
				if($test_mx)
				{
					list( , $domain) = explode("@", $email);
					return getmxrr($domain, $mxrecords);
				}
				else
					return true;
			}
			else
				return false;
		}

		// Grabs the contents of a remote URL. Can perform basic authentication if un/pw are provided.
		function geturl($url, $username = null, $password = null)
		{
			if(function_exists('curl_init'))
			{
				$ch = curl_init();
				if(!is_null($username) && !is_null($password))
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				$html = curl_exec($ch);
				curl_close($ch);
				return $html;
			}
			elseif(ini_get('allow_url_fopen') == true)
			{
				if(!is_null($username) && !is_null($password))
					$url = str_replace("://", "://$username:$password@", $url);
				$html = file_get_contents($url);
				return $html;
			}
			else
			{
				// Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
				return false;
			}
		}

		// Returns the user's browser info.
		// browscap.ini must be available for this to work.
		// See the PHP manual for more details.
		function browser_info()
		{
			$info    = get_browser(null, true);
			$browser = $info['browser'] . ' ' . $info['version'];
			$os      = $info['platform'];
			$ip      = $_SERVER['REMOTE_ADDR'];
			return array('ip' => $ip, 'browser' => $browser, 'os' => $os);
		}

		// Quick wrapper for preg_match
		function match($regex, $str, $i = 0)
		{
			if(preg_match($regex, $str, $match) == 1)
				return $match[$i];
			else
				return false;
		}

		// Sends an SMTP email
		function send_smtp_mail($smtp, $to, $subject, $msg, $from, $plaintext = '')
		{

			if(!is_array($smtp)) return false;
			if(!is_array($to)) $to = array($to);

			$mail = new PHPMailer;

			$mail->isSMTP();
			$mail->Host = $smtp["host"];
			$mail->SMTPAuth = $smtp["auth"];
			$mail->Username = $smtp["username"];
			$mail->Password = $smtp["password"];
			$mail->SMTPSecure = $smtp["secure"];
			$mail->Port = $smtp["port"];

			if($from != null)
				$mail->setFrom( $from, 'Mailer');

			foreach($to as $address)
			{
				$mail->addAddress( $address );
			}

			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $msg;
			$mail->AltBody = $plaintext;

			//die('Mailer Error: ' . $mail->ErrorInfo);

			return $mail->send();
		}

		// Sends an HTML formatted email
		function send_html_mail($to, $subject, $msg, $from, $plaintext = '')
		{
			if(!is_array($to)) $to = array($to);

			$all_sent = true;

			foreach($to as $address)
			{
				$boundary = uniqid(rand(), true);

				$headers  = "From: $from\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: multipart/alternative; boundary = $boundary\n";
				$headers .= "This is a MIME encoded message.\n\n";
				$headers .= "--$boundary\n" .
							"Content-Type: text/plain; charset=ISO-8859-1\n" .
							"Content-Transfer-Encoding: base64\n\n";
				$headers .= chunk_split(base64_encode($plaintext));
				$headers .= "--$boundary\n" .
							"Content-Type: text/html; charset=ISO-8859-1\n" .
							"Content-Transfer-Encoding: base64\n\n";
				$headers .= chunk_split(base64_encode($msg));
				$headers .= "--$boundary--\n" .

				$all_sent = mail($address, $subject, '', $headers);
			}

			return $all_sent;
		}

		// Returns the lat, long of an address via Yahoo!'s geocoding service.
		// You'll need an App ID, which is available from here:
		// http://developer.yahoo.com/maps/rest/V1/geocode.html
		// Note: needs to be updated to use PlaceFinder instead.
		function geocode($location, $appid)
		{
			$location = urlencode($location);
			$appid    = urlencode($appid);
			$data     = file_get_contents("http://local.yahooapis.com/MapsService/V1/geocode?output=php&appid=$appid&location=$location");
			$data     = unserialize($data);

			if($data === false) return false;

			$data = $data['ResultSet']['Result'];

			return array('lat' => $data['Latitude'], 'lng' => $data['Longitude']);
		}

		// A stub for Yahoo!'s reverse geocoding service
		// http://developer.yahoo.com/geo/placefinder/
		function reverse_geocode($lat, $lng)
		{

		}

		// Quick and dirty wrapper for curl scraping.
		function curl($url, $referer = null, $post = null)
		{
			static $tmpfile;

			if(!isset($tmpfile) || ($tmpfile == '')) $tmpfile = tempnam('/tmp', 'FOO');

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// curl_setopt($ch, CURLOPT_VERBOSE, 1);

			if($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
			if(!is_null($post))
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}

			$html = curl_exec($ch);

			// $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			return $html;
		}

		// Accepts any number of arguments and returns the first non-empty one
		function pick()
		{
			foreach(func_get_args() as $arg)
				if(!empty($arg))
					return $arg;
			return '';
		}

		// Secure a PHP script using basic HTTP authentication
		function http_auth($un, $pw, $realm = "Secured Area")
		{
			if(!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $un && $_SERVER['PHP_AUTH_PW'] == $pw))
			{
				header('WWW-Authenticate: Basic realm="' . $realm . '"');
				header('Status: 401 Unauthorized');
				exit();
			}
		}

		// This is easier than typing 'echo WEB_ROOT'
		function WEBROOT()
		{
			echo WEB_ROOT;
		}

		// Returns a file's mimetype based on its extension
		function mime_type($filename, $default = 'application/octet-stream')
		{
			$mime_types = array('323'     => 'text/h323',
								'acx'     => 'application/internet-property-stream',
								'ai'      => 'application/postscript',
								'aif'     => 'audio/x-aiff',
								'aifc'    => 'audio/x-aiff',
								'aiff'    => 'audio/x-aiff',
								'asf'     => 'video/x-ms-asf',
								'asr'     => 'video/x-ms-asf',
								'asx'     => 'video/x-ms-asf',
								'au'      => 'audio/basic',
								'avi'     => 'video/x-msvideo',
								'axs'     => 'application/olescript',
								'bas'     => 'text/plain',
								'bcpio'   => 'application/x-bcpio',
								'bin'     => 'application/octet-stream',
								'bmp'     => 'image/bmp',
								'c'       => 'text/plain',
								'cat'     => 'application/vnd.ms-pkiseccat',
								'cdf'     => 'application/x-cdf',
								'cer'     => 'application/x-x509-ca-cert',
								'class'   => 'application/octet-stream',
								'clp'     => 'application/x-msclip',
								'cmx'     => 'image/x-cmx',
								'cod'     => 'image/cis-cod',
								'cpio'    => 'application/x-cpio',
								'crd'     => 'application/x-mscardfile',
								'crl'     => 'application/pkix-crl',
								'crt'     => 'application/x-x509-ca-cert',
								'csh'     => 'application/x-csh',
								'css'     => 'text/css',
								'dcr'     => 'application/x-director',
								'der'     => 'application/x-x509-ca-cert',
								'dir'     => 'application/x-director',
								'dll'     => 'application/x-msdownload',
								'dms'     => 'application/octet-stream',
								'doc'     => 'application/msword',
								'dot'     => 'application/msword',
								'dvi'     => 'application/x-dvi',
								'dxr'     => 'application/x-director',
								'eps'     => 'application/postscript',
								'etx'     => 'text/x-setext',
								'evy'     => 'application/envoy',
								'exe'     => 'application/octet-stream',
								'fif'     => 'application/fractals',
								'flac'    => 'audio/flac',
								'flr'     => 'x-world/x-vrml',
								'gif'     => 'image/gif',
								'gtar'    => 'application/x-gtar',
								'gz'      => 'application/x-gzip',
								'h'       => 'text/plain',
								'hdf'     => 'application/x-hdf',
								'hlp'     => 'application/winhlp',
								'hqx'     => 'application/mac-binhex40',
								'hta'     => 'application/hta',
								'htc'     => 'text/x-component',
								'htm'     => 'text/html',
								'html'    => 'text/html',
								'htt'     => 'text/webviewhtml',
								'ico'     => 'image/x-icon',
								'ief'     => 'image/ief',
								'iii'     => 'application/x-iphone',
								'ins'     => 'application/x-internet-signup',
								'isp'     => 'application/x-internet-signup',
								'jfif'    => 'image/pipeg',
								'jpe'     => 'image/jpeg',
								'jpeg'    => 'image/jpeg',
								'jpg'     => 'image/jpeg',
								'js'      => 'application/x-javascript',
								'latex'   => 'application/x-latex',
								'lha'     => 'application/octet-stream',
								'lsf'     => 'video/x-la-asf',
								'lsx'     => 'video/x-la-asf',
								'lzh'     => 'application/octet-stream',
								'm13'     => 'application/x-msmediaview',
								'm14'     => 'application/x-msmediaview',
								'm3u'     => 'audio/x-mpegurl',
								'man'     => 'application/x-troff-man',
								'mdb'     => 'application/x-msaccess',
								'me'      => 'application/x-troff-me',
								'mht'     => 'message/rfc822',
								'mhtml'   => 'message/rfc822',
								'mid'     => 'audio/mid',
								'mny'     => 'application/x-msmoney',
								'mov'     => 'video/quicktime',
								'movie'   => 'video/x-sgi-movie',
								'mp2'     => 'video/mpeg',
								'mp3'     => 'audio/mpeg',
								'mpa'     => 'video/mpeg',
								'mpe'     => 'video/mpeg',
								'mpeg'    => 'video/mpeg',
								'mpg'     => 'video/mpeg',
								'mpp'     => 'application/vnd.ms-project',
								'mpv2'    => 'video/mpeg',
								'ms'      => 'application/x-troff-ms',
								'mvb'     => 'application/x-msmediaview',
								'nws'     => 'message/rfc822',
								'oda'     => 'application/oda',
								'oga'     => 'audio/ogg',
								'ogg'     => 'audio/ogg',
								'ogv'     => 'video/ogg',
								'ogx'     => 'application/ogg',
								'p10'     => 'application/pkcs10',
								'p12'     => 'application/x-pkcs12',
								'p7b'     => 'application/x-pkcs7-certificates',
								'p7c'     => 'application/x-pkcs7-mime',
								'p7m'     => 'application/x-pkcs7-mime',
								'p7r'     => 'application/x-pkcs7-certreqresp',
								'p7s'     => 'application/x-pkcs7-signature',
								'pbm'     => 'image/x-portable-bitmap',
								'pdf'     => 'application/pdf',
								'pfx'     => 'application/x-pkcs12',
								'pgm'     => 'image/x-portable-graymap',
								'pko'     => 'application/ynd.ms-pkipko',
								'pma'     => 'application/x-perfmon',
								'pmc'     => 'application/x-perfmon',
								'pml'     => 'application/x-perfmon',
								'pmr'     => 'application/x-perfmon',
								'pmw'     => 'application/x-perfmon',
								'pnm'     => 'image/x-portable-anymap',
								'pot'     => 'application/vnd.ms-powerpoint',
								'ppm'     => 'image/x-portable-pixmap',
								'pps'     => 'application/vnd.ms-powerpoint',
								'ppt'     => 'application/vnd.ms-powerpoint',
								'prf'     => 'application/pics-rules',
								'ps'      => 'application/postscript',
								'pub'     => 'application/x-mspublisher',
								'qt'      => 'video/quicktime',
								'ra'      => 'audio/x-pn-realaudio',
								'ram'     => 'audio/x-pn-realaudio',
								'ras'     => 'image/x-cmu-raster',
								'rgb'     => 'image/x-rgb',
								'rmi'     => 'audio/mid',
								'roff'    => 'application/x-troff',
								'rtf'     => 'application/rtf',
								'rtx'     => 'text/richtext',
								'scd'     => 'application/x-msschedule',
								'sct'     => 'text/scriptlet',
								'setpay'  => 'application/set-payment-initiation',
								'setreg'  => 'application/set-registration-initiation',
								'sh'      => 'application/x-sh',
								'shar'    => 'application/x-shar',
								'sit'     => 'application/x-stuffit',
								'snd'     => 'audio/basic',
								'spc'     => 'application/x-pkcs7-certificates',
								'spl'     => 'application/futuresplash',
								'src'     => 'application/x-wais-source',
								'sst'     => 'application/vnd.ms-pkicertstore',
								'stl'     => 'application/vnd.ms-pkistl',
								'stm'     => 'text/html',
								'svg'     => "image/svg+xml",
								'sv4cpio' => 'application/x-sv4cpio',
								'sv4crc'  => 'application/x-sv4crc',
								't'       => 'application/x-troff',
								'tar'     => 'application/x-tar',
								'tcl'     => 'application/x-tcl',
								'tex'     => 'application/x-tex',
								'texi'    => 'application/x-texinfo',
								'texinfo' => 'application/x-texinfo',
								'tgz'     => 'application/x-compressed',
								'tif'     => 'image/tiff',
								'tiff'    => 'image/tiff',
								'tr'      => 'application/x-troff',
								'trm'     => 'application/x-msterminal',
								'tsv'     => 'text/tab-separated-values',
								'txt'     => 'text/plain',
								'uls'     => 'text/iuls',
								'ustar'   => 'application/x-ustar',
								'vcf'     => 'text/x-vcard',
								'vrml'    => 'x-world/x-vrml',
								'wav'     => 'audio/x-wav',
								'wcm'     => 'application/vnd.ms-works',
								'wdb'     => 'application/vnd.ms-works',
								'wks'     => 'application/vnd.ms-works',
								'wmf'     => 'application/x-msmetafile',
								'wps'     => 'application/vnd.ms-works',
								'wri'     => 'application/x-mswrite',
								'wrl'     => 'x-world/x-vrml',
								'wrz'     => 'x-world/x-vrml',
								'xaf'     => 'x-world/x-vrml',
								'xbm'     => 'image/x-xbitmap',
								'xla'     => 'application/vnd.ms-excel',
								'xlc'     => 'application/vnd.ms-excel',
								'xlm'     => 'application/vnd.ms-excel',
								'xls'     => 'application/vnd.ms-excel',
								'xlt'     => 'application/vnd.ms-excel',
								'xlw'     => 'application/vnd.ms-excel',
								'xof'     => 'x-world/x-vrml',
								'xpm'     => 'image/x-xpixmap',
								'xwd'     => 'image/x-xwindowdump',
								'z'       => 'application/x-compress',
								'zip'     => 'application/zip');
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			return isset($mime_types[$ext]) ? $mime_types[$ext] : $default;
		}
		
		function isMobile(){

			$useragent = $_SERVER['HTTP_USER_AGENT'];

			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
				return true;
			return false;
		}
	}