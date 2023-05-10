<?php 
function blogPing($mysqli,$idact,$dateping)
{
$sql ="UPDATE account SET pinger='Y',dateping='$dateping' WHERE id=".$idact."";
     if ($mysqli->query($sql) === TRUE) { } else { }

}


function tmdbSend($mysqli,$tmdb,$niche,$virtual)
{
$sql ="UPDATE dbtmdb SET $virtual='Y' WHERE niche=''.$niche.'' AND tmdb=''.$tmdb.'";
     if ($mysqli->query($sql) === TRUE) { } else { }

}

 

function getMovie($jservice,$tmdb) {
$url = ''.$jservice.'?id='.$tmdb.'';
$file = file_get_contents($url);
if($file) {
	$json = json_decode($file);
		 return array(
                        'tmdb'     =>$json->data->tmdb,
                        'imdb'     =>$json->data->imdb,
                        'mtitle'   =>$json->data->mtitle,
                        'mdesc'    =>$json->data->mdesc,
                        'mrelease' =>$json->data->mrelease,
                        'mruntime' =>$json->data->mruntime,
                        'voteavg'  =>$json->data->voteavg,
                        'votecount'=>$json->data->votecount,
                        'genre'    =>$json->data->genre,
                        'myear'    =>$json->data->myear,
                        'tagline'  =>$json->data->tagline,
                        'tposter'  =>$json->data->tposter,
                        'tbackdrop'=>$json->data->tbackdrop
			);
   } else {
  echo "J error 1 <br>";
  }
}



function xmlRpcPing( $url ) {
    global $myBlogName, $myBlogUrl, $myBlogUpdateUrl, $myBlogRSSFeedUrl;
    $client = new IXR_Client( $url );
    $client->timeout = 3;
    $client->useragent .= 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
    $client->debug = false;
    if( $client->query( 'weblogUpdates.extendedPing', $myBlogName, $myBlogUrl, $myBlogUpdateUrl, $myBlogRSSFeedUrl ) )
    {
        return $client->getResponse();
    }
    echo 'Failed extended XML-RPC ping for "' . $url . '": ' . $client->getErrorCode() . '->' . $client->getErrorMessage() . '<br />';
    if( $client->query( 'weblogUpdates.ping', $myBlogName, $myBlogUrl ) )
    {
        return $client->getResponse();
    }
    echo 'Failed basic XML-RPC ping for "' . $url . '": ' . $client->getErrorCode() . '->' . $client->getErrorMessage() . '<br />';
    return false;
}

function Submit($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpCode;
}
function SubmitSiteMap($url) {
  $returnCode = Submit($url);
  if ($returnCode != 200) {
    echo "<font color=red>Error $returnCode: $url <br></font>";
  } else {
    echo "<font color=blue>Submitted $returnCode: $url <br></font>";
  }
}


function myCurl($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpCode;
}

function return_code_check($pingedURL, $returnedCode) {

    $to = "aditya.kurnia@gmail.com";
    $subject = "Sitemap ping fail: ".$pingedURL;
    $message = "Error code ".$returnedCode.". Go check it out!";
    $headers = "From: hello@XMLSitemap";

    if($returnedCode != "200") {
        mail($to, $subject, $message, $headers);
    }
}




function pingomatic($title,$url,$debug=false) {
    $content='<?xml version="1.0"?>'.
        '<methodCall>'.
        ' <methodName>weblogUpdates.ping</methodName>'.
        '  <params>'.
        '   <param>'.
        '    <value>'.$title.'</value>'.
        '   </param>'.
        '  <param>'.
        '   <value>'.$url.'</value>'.
        '  </param>'.
        ' </params>'.
        '</methodCall>';

    $headers="POST / HTTP/1.0\r\n".
    "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 3.5.30729)\r\n".
    "Host: rpc.pingomatic.com\r\n".
    "Content-Type: text/xml\r\n".
    "Content-length: ".strlen($content);

    if ($debug) nl2br($headers);

    $request=$headers."\r\n\r\n".$content;
    $response = "";
    $fs=fsockopen('rpc.pingomatic.com',80, $errno, $errstr);
    if ($fs) {
        fwrite ($fs, $request);
        while (!feof($fs)) $response .= fgets($fs);
        if ($debug) echo "<xmp>".$response."</xmp>";
        fclose ($fs);
        preg_match_all("/<(name|value|boolean|string)>(.*)<\/(name|value|boolean|string)>/U",$response,$ar, PREG_PATTERN_ORDER);
        for($i=0;$i<count($ar[2]);$i++) $ar[2][$i]= strip_tags($ar[2][$i]);
        return array('status'=> ( $ar[2][1]==1 ? 'ko' : 'ok' ), 'msg'=>$ar[2][3] );
    } else {
        if ($debug) echo "<xmp>".$errstr." (".$errno.")</xmp>";
        return array('status'=>'ko', 'msg'=>$errstr." (".$errno.")");
    }
}


function seoUrl($string) {
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        $string = preg_replace("/[\s-]+/", " ", $string);
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
}

function fixWord($str, $delimiter = ' ', $options = array()) {
	        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
	        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	
	        $defaults = array(
		        'delimiter' =>  $delimiter,
		        'limit' => null,
		        'lowercase' => false,
		        'replacements' => array(),
		        'transliterate' => true,
	        );
	
	        // Merge options
	        $options = array_merge($defaults, $options);
	
		$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
		'ß' => 'ss', 
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
		'ÿ' => 'y',

		// Latin symbols
		'©' => '(c)',

		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',

		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
		'Ž' => 'Z', 
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z', 

		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
		'Ż' => 'Z', 
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',

		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);
	
	        // Make custom replacements
	        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	
	        // Transliterate characters to ASCII
	        if ($options['transliterate']) {
		        $str = str_replace(array_keys($char_map), $char_map, $str);
	        }
	
	        // Replace non-alphanumeric characters with our delimiter
	        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	
	        // Remove duplicate delimiters
	        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
	        // Truncate slug to max. characters
	        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : strlen($str)), 'UTF-8');
	
	        // Remove delimiter from ends
	        $str = trim($str, $options['delimiter']);
	
                return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
        }


function Spin($s)
{

            if (preg_match_all('#\{(((?>[^{}]+)|(?R))*)\}#', $s, $matches, PREG_OFFSET_CAPTURE)) {

                for ($i = count($matches[0]) - 1; $i >= 0; --$i) {

                    $s = substr_replace($s, Spin($matches[1][$i][0]), $matches[0][$i][1], strlen($matches[0][$i][0]));
                }
            }

            $choices = explode('|', $s);

            return $choices[array_rand($choices)];
        }

?>
