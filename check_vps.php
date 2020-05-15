<?php
ini_set('max_execution_time', 300000);
require_once 'simple_html_dom.php';
@header("Content-type: text/html; charset=UTF-8");
$url='https://api.proxyscrape.com/?request=getproxies&proxytype=socks5&timeout=2100&country=all';
download_proxy($url);
function download_proxy($url)
{
	$fp = fopen('socks5_proxies.txt', 'wb'); // создаём и открываем файл для записи
	$ch = curl_init($url); // $url содержит прямую ссылку на видео
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FILE, $fp); // записать вывод в файл
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}
for($n=0; $n<30; $n++)
{
//////////////////////////////////////////////////////-------delite!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
$url="https://www.avito.ru/ekaterinburg/tovary_dlya_detey_i_igrushki/detskiy_elektromobil_mercedes_style_12v_-_hl-1558_1907369170";
//////////////////////////////////////////////////////-------delite!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
/*
$useragent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_REFERER, "https://www.avito.ru");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$pagerr = curl_exec($ch );
*/
$time_sleep=rand(3,4);
$html=Curl_avito($url,$time_sleep);

$regexp = '~avito.item.phone = \'(.*?)\';~i';
if($n==0)
{
$regexp_id = '~avito.item.id = \'(.*?)\';~i';
preg_match($regexp_id, $html, $id);
$id_only=$id[1];
}
preg_match($regexp, $html, $phone_item); 
echo $phone_item[1];
}
function Curl_avito($url,$time_sleep)
{
	$useragent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL,$url);
	if($proxy!=NULL)
	{
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	}
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	$page = curl_exec($ch);
	$html=str_get_html($page);
	//echo $html;
	echo "<br><br>Вступавет проверочка<br>";
	sleep($time_sleep);
	curl_close($ch);
	$html=check_html($html,$url);
	//echo "<br><br><br><br>Верный хтмл только этот стоп<br><br>м".$html."<br><br><br>";
	return $html;
}
function check_html($html,$url)
{
	$check_html=$html;
	//echo "<br>".$url."<br>";
	$string=1;
	$check_1=strpos($check_html,'Объявления');
	$check_2=strpos($check_html,'user_unauth');
	$check_3=strpos($check_html,'image64');
	if($check_1!==false or $check_2!==false or $check_3!==false){echo "<br>Vse norm<br>"; $check_proxy_check=1;}
	else {echo "<br>Vse ploho<br>"; $check_proxy_check=0;}
	while($check_html=='' or $check_proxy_check==0)
	{
		$check_1=strpos($check_html,'Объявления');
		$check_2=strpos($check_html,'user_unauth');
		$check_3=strpos($check_html,'image64');
		if($check_html!='')
		{
		if($check_1!==false or $check_2!==false or $check_3!==false){echo "<br>Vse norm<br>"; $check_proxy_check=1; break;}
		else {echo "<br>Vse ploho<br>";}
		}
		else {echo "<br>Vse ploho<br>";}
		$useragent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36';
		$show_info = file('socks5_proxies.txt');
		$proxy=$show_info[$string];
		echo "<br>Внутри swhile".$proxy."<br>";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		$page = curl_exec($ch);
		$check_html=str_get_html($page);
		curl_close($ch);
		//echo "<br><br>Плохой хтмл<br>".$check_html;
		$string++;
	}
	echo "<br>End of while<br>";
	sleep(4);
	return $check_html;
}
?>
