<?php
ini_set('max_execution_time', 300000);
require_once 'simple_html_dom.php';
//@header("Content-type: text/html; charset=UTF-8");
$GLOBALS['sysMessages'] = "Нет системных сообщений";
echo "<p style='color: darkgreen; font-size: 18px;'>".$sysMessages."</p>" ;
require_once 'PHPExcel/Classes/PHPExcel.php'; //Подключаем библиотеку
$phpexcel = new PHPExcel(); //Создаем новый Excel файл
$page_excel = $phpexcel->setActiveSheetIndex(0); //Устанавливаем активный лист
$page_excel->setTitle("Phones"); //Записываем название 
$page_excel->setCellValue("A1", "Телефоны");

$url='https://api.proxyscrape.com/?request=getproxies&proxytype=socks5&timeout=2100&country=all';
download_proxy($url);

$phone_number=1; 
$page_number=1;
$max_pages=100000000;
$number_of_phones=10; //vvodim post
$url='';
$main_url='https://www.avito.ru/nizhniy_tagil/bytovaya_elektronika';
echo $main_url;
while($page_number<$max_pages && $phone_number<=$number_of_phones)
{
	$url=$main_url.'?p='.$page_number;
	echo $url;
	$time_sleep=rand(1,2);
	$html=Curl_avito($url,$time_sleep);
	//while($html=='') { $html=Curl_avito($url);}
	if($page_number==1)
	{
		foreach($html->find('span.pagination-item-1WyVp') as $all_span)
		{
			$max_pages_array[]=$all_span->plaintext;
		}
		$max_pages=$max_pages_array[7];
		echo "<br>".$max_pages."<br>";
	}
	$url_number=0;
	foreach($html->find('div.snippet-horizontal') as $href_div)
	{
		$id=$href_div->attr['data-item-id'];
		if($id%2!=0)
		{
			$array0[$id]=$href_div->attr['data-pkey'];
			if($array0[$id]!='')
			{
				$id_array[]=$id;
				//echo $id.") ".$array0[$id]."<br>";
				foreach($href_div->find('a.snippet-link') as $href_to_check)
				{echo $id.") ".$href_to_check->href."<br>";}								//убрать
			}
		}
	}
	$max_id=count($id_array);
	echo "<br>Количество айди".$max_id."<br>";
	echo "<br>Perviy cicl<br>";
	$time_sleep=rand(1,2);
	$html=Curl_avito($url,$time_sleep);
	for($id_numer=0;$id_numer<$max_id; $id_numer++)
	{
		$id=$id_array[$id_numer];
		echo $id.") ";
		foreach($html->find("div.snippet-horizontal[data-item-id=$id]") as $href_div)
		{
				$array1[$id]=$href_div->attr['data-pkey'];
		}
		//echo $array1[$id]."<br>";
	}
	echo "<br>vtoroy cicl<br>";
	$time_sleep=rand(1,2);
	$html=Curl_avito($url,$time_sleep);
	for($id_numer=0;$id_numer<$max_id; $id_numer++)
	{
		$id=$id_array[$id_numer];
		echo $id.") ";
		foreach($html->find("div.snippet-horizontal[data-item-id=$id]") as $href_div)
		{
				$array2[$id]=$href_div->attr['data-pkey'];
				if($array0[$id]!='' && $array1[$id]!='' && $array2[$id]!=''){ $id_all_pages[]=$id; $phone_number++; $GLOBALS['sysMessages'] = "Парсим ключ завтравку для ".$phone_number."из ".$number_of_phones;}
		}
		//echo $array2[$id]."<br>";
	}
	echo "<br>number of page ".$page_number."<br>";
	//echo "Номер телефона ".$phone_number."<br>";
	unset($id_array);
	$page_number++;
}
$checked_id=1;
$max_id_all=count($id_all_pages);
echo "<br>Vst linki".$max_id_all."<br>";
for($int=0;$int<$max_id_all;$int++)
{
	$id=$id_all_pages[$int];
	if($array0[$id]!='' && $array1[$id]!='' && $array2[$id]!='')
	{
		$GLOBALS['sysMessages'] = "Распознаем фото для ".$checked_id."из ".$number_of_phones;
		echo "<br>Номер айди)".$id."<br>";
		//echo $checked_id.") ".$array0[$id]."<br>".$array1[$id]."<br>".$array2[$id]."<br>";
		$phone_item_only0=$array0[$id];
		$phone_item_only1=$array1[$id];
		$phone_item_only2=$array2[$id];
		$url=find_phone_url($id,$phone_item_only0, $phone_item_only1, $phone_item_only2);
		echo "<br>Фоне юрл".$checked_id.")".$url."<br>";
		$time_sleep=rand(2,3);
		$imgContent = Curl_avito($url,$time_sleep);
		//echo "<br>".$imgContent."<br>";
		$avitoContact = new AvitoContact;
		$imgContent = explode('base64,', $imgContent)[1];
		//echo "<br>".$imgContent."<br>";
		$a = fopen('phone.png', 'wb');
		fwrite($a, base64_decode($imgContent));
		fclose($a);
		$image='phone.png';
		$result = $avitoContact->recognize('phone.png');
		if ($result) 
		{
			echo "<br>Phone number: ".$result."<br>";
			$page_excel->setCellValue("A$checked_id", $result);
		} 
		else 
		{
			echo '<h2 class="text-danger">Ничего не получилось</h2>';
		}
		$checked_id++;
	}
}
$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007'); //Формат
$objWriter->save("phones.xlsx"); //Сохраняем
echo 'File created successfuly'; //Сообщаем о создании файла
function find_phone_url($id,$phone_item_only0,$phone_item_only1,$phone_item_only2)
{
	$id_only=$id;
	//$phone_item_only0=$phone_item_only0;
	//$phone_item_only1=$phone_item_only1;
	//$phone_item_only2=$phone_item_only2;
	$array0 = str_split($phone_item_only0); 
	$array1 = str_split($phone_item_only1);
	$array2 = str_split($phone_item_only2);
	//function check_code($array0,$array1,$array2)
	$a=0;//a номера переменных
	$k=0; //Номер кода
	$code_key='';//Буква разделитель
	$finish=0; //Код для авершения
	while($array0[$a]!=NULL and $array1[$a]!=NULL and $array2[$a]!=NULL and $finish==0)
	{
		//Если все 3 совпадают
		if($array0[$a]==$array1[$a] and $array0[$a]==$array2[$a])
		{ $a=$a+3; }
		//Проверка когда 2 отличаются
		elseif($array0[$a]!=$array1[$a] and $array0[$a]!=$array2[$a] and $array1[$a]!=$array2[$a])
		{
			if($array0[$a+1]==$array1[$a+1]) { $k=0; $code_key=$array0[$a]; $finish=1; break;}
			elseif($array0[$a+1]==$array2[$a+1]) { $k=0; $code_key=$array0[$a]; $finish=1;break;} 
			elseif($array1[$a+1]==$array2[$a+1]) { $k=1; $code_key=$array1[$a]; $finish=1;break;}	
		}
		//Проверка когда 1 отличается
		elseif($array0[$a]==$array1[$a] and $array0[$a]!=$array2[$a])
		{
			$code_key=$array2[$a];
			if($array0[$a]==$array2[$a+1]) { $k=2; $finish=1;break;}	
		}
		elseif($array0[$a]==$array2[$a] and $array0[$a]!=$array1[$a])
		{
			$code_key=$array1[$a];
			if($array0[$a]==$array1[$a+1]) { $k=1; $finish=1;break;}	
		}
		elseif($array1[$a]==$array2[$a] and $array1[$a]!=$array0[$a])
		{
			$code_key=$array0[$a];
			if($array1[$a]==$array0[$a+1]) { $k=0; $finish=1;break;}	
		}
	}
	//Находим количество букв и вгоняем линию
	if($k==0) { $numer=count($array0); $crypted_line_array=$array0; }
	elseif($k==1) { $numer=count($array1); $crypted_line_array=$array1; }
	elseif($k==2) { $numer=count($array2); $crypted_line_array=$array2; }
	$pkey=''; //Код
	$i=0;
	while($i<$numer)
	{
		if($crypted_line_array[$i]==$code_key) {$i++;}
		$pkey.=$crypted_line_array[$i];
		$i=$i+3;
	}
	$phoneUrl="https://www.avito.ru/items/phone/".$id_only."?pkey=".$pkey."&vsrc=r";
	//echo "<br>Вывод из поиска".$phoneUrl."<br>";
	return $phoneUrl;
}
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
class AvitoContact {

     /**
     * $offxetX - режем картинку с верху (т.е. начинаем читать с этого пикселя сверху)
     * $offxetY - режем снизу, т.к. читаем не до полной высоты картинки
     * $offxetL - режем картинку по ширине с начала (т.е. на чинаем читать с этого пикселя)
     * $white - код цвета пропуска, фон
     */
    function __construct()
    {
        $this->offxetX = 13;
        $this->offxetY = 6;
        $this->whitePixel = 2147483647;
        $this->offxetL = 2;
    }
    // 2 этап. Распознавание!

    /**
     * Распознать файл и получить номер
     */
    function recognize($image)
    {

        $imageScheme = $this->getImageScheme($image);
        //echo '<pre>'; print_r($imageScheme); echo '</pre>'; exit;
        $phoneNumber = $this->recognizeByScheme($imageScheme);

        return $phoneNumber;
    }

    /**
     * Собственно проходим по изображению и собираем в $data его схему - 1 и 0.
     */
    function getImageScheme($image, $columnFrom=false, $columnTo=false)
    {

        $size = getimagesize($image);
        if (!$size) {
            $this->error('Ошибка разбора картинки '.$image.' - это не изображение?');
            return ;
        }
        $img = strpos($image, 'png') ? imagecreatefrompng($image) : imagecreatefromjpeg($image);

        $w = $size[0];//x
        $h = $size[1];//y

        $data = array();
        $dataColumn = array();

        $this->showall = 0;
        $columnIndex = 0;
        $this->rows = '';
        $this->colorStat = array();
        for($x = 0; $x < $w; $x ++) {
            if ($x < $this->offxetL) {
                continue;
            }
            // $data это основной массив в который сохраняем все 0 и 1 найденных цветов пикселей в колонке
            $dataColumn = array();
            $foundedOneFilled = 0;
            $width = -$this->offxetX + $h - $this->offxetY;
            //e/cho '<br />'.$width;
        	for ($y = $this->offxetX; $y < ($h - $this->offxetY); $y++){
                // запись в масив каждой точки ее значения
                $pixel = imagecolorat($img, $x, $y);
                //if ($this->showall) echo ' '.$pix;
                $this->colorStat [$pix]++;
                if ($pixel >= $this->whitePixel) {
                    $dataColumn []= 0;
                } else {
                    $dataColumn []= 1;
                    $foundedOneFilled = 1;
                }
                // белый фон записываем как 0, все остальные пиксели как 1
                /*if ($d > 50) {
                	break;
                }*/
        	}

            // пропускаем черточку
            if (array_sum($dataColumn) == 4 && $dataColumn[18].$dataColumn[19].$dataColumn[20].$dataColumn[21] == '1111') {
                continue;
            }

            // Добавляем колонку только если нашли хотя бы 1 заполненную ячейку не белого цвета
            if ($foundedOneFilled == 1) {
                $data []= $dataColumn;
                if ($columnIndex >= $columnFrom && (!$columnTo || $columnIndex <= $columnTo)) {
                    $t = 0;
                    // для наглядности выводим значения полученного массива в браузер
                    foreach($dataColumn as $key => $r) {
                        $t ++;
                        $this->rows .= '<span title="'.$columnIndex.'" style="color:'.(!$r ? 'green' : 'red; background-color:blue;').'">'.$r.'</span>'."<br/>";
                        if ($t == $width) {
                            $this->rows .= '</td><td>';
                            $t = 0;
                        }
                    }
                }
                $columnIndex ++;
            }
        }

        // echo '<pre>'; print_r($data); echo '</pre>';

        // Сорфимровать отладочную таблицу для карты
        if (!$this->rows) {
            $this->debugOutput = 'Нет строк';
        } else {
            $this->debugOutput .= '<table style="margin-bottom:20px;"><tr><td>';
            $this->debugOutput .= $this->rows;
            $this->debugOutput .= '</td></tr></table> ';
        }

        return $data;
    }


    /**
     * Получаем маску распознавания
     */
    function getMask()
    {
        $maskFile = 'avito-mask.php';
        if (!file_exists($maskFile)) {
            $this->error('Не существует файла маски '.$maskFile);
            return ;
        }

        include $maskFile;
        if (!is_array($mask)) {
            $this->error('Это не маска ('.$maskFile.')');
            return ;
        }
        return $mask;
    }

    // Сохранение колонок для распознавания в режиме отладки
    function makeColumnData($imageScheme, $columnFrom, $columnTo)
    {
        $index = 0;
        $textarea = '';
        foreach ($imageScheme as $columnIndex => $column) {
            if ($columnIndex >= $columnFrom && $columnIndex <= $columnTo) {
                foreach ($column as $k => $v) {
                    $textarea .= $index." => '$v', ";
                    $index ++;
                }
                $textarea .= "\n";
            }
        }
        return $textarea;
    }


    function recognizeByScheme($imageScheme)
    {

        $mask = $this->getMask();
        if (!$mask) {
            return ;
        }

        // Допуск похожести
        $dopusk = 3;
        $phoneNumber = '';
        $columnsSet = array();

        //$process = '<h2>Процесс распознования:</h2>';


/*
        echo '<br /><b>Маски и количества колонок у них:</b>';
        foreach ($mask as $k => $v) {
            echo '<br />'.$k.' - '.count($v).'';
        }
        echo '<hr />';
*/

        $debug = 0;

        // Проходим по каждому столбцу изображения. Аккумулируем его в $columnsSet - там собирается набор.
        // Для каждого прохода проверяем по каждой маске, совпадает ли набранный набор с какой-то маской. Если ок, то
        // посимвольно сверяем маску с набором. Если схожеть больше 3, то значит нашли. Обнуляем идем дальше.
        // Если $columnsSet достиг макс. предела в 70 (шире цифр пока нет) - то выходим. Значит здесь косяк.
        foreach ($imageScheme as $aindex => $column) {

            // Все колонки по очереди объединяем в набор колонок до тех пор пока либо найдем подходящую под него маску
            // либо выйдем за пределы ширины букв и завершим с ошибкой
        	foreach ($column as $it) {
        		$columnsSet [] = $it;
        	}


            if ($debug) {
            	echo '<br />Колонка '.$aindex;
            }

        	foreach ($mask as $key => $mk) {

                if ($debug) {
                    echo '<span style="color:#ccc"> - '.count($columnsSet).' == '.count($mk).' </span>';
                }

        		if (count($columnsSet) == count($mk)) {

                    if ($debug) {
            		    echo "<div> +++ $aindex / $key - проверяем совпадает ли набор с $key</div>";
                    }

                    // Сравниваем посимвольно массив маски с собранным массивом картинки
                    // Сколько символов совпадает?
                    $countEqual = 0;
        			foreach ($columnsSet as $i => $nit) {
        				if ($nit == $mk[$i]) {
                            $countEqual ++;
                        }
        			}

        			$cnm = count($columnsSet);

                    // Да, мы нашли эту цифру!
                    // Коичество либо полностью совпадает, либо находится в границах допустимого
        			if ($countEqual == count($mk) || ($countEqual > count($mk) - $dopusk && $countEqual < count($mk) + $dopusk)) {
                        $phoneNumber .= $key;
                        $columnsSet = array();
                        if ($debug) {
                            echo '<div>Нашли число <span style="color:red">'.$key.'</span>! Итого наш телефон уже такой: <b>'.$out.'</b></div>';
                            echo "<p> selected = $key with $countEqual (<b>$out</b>)</p>";
                        }
                    }

        		}

                if (count($columnsSet) > 900) {
                    $this->error = 'Достигли предела и не нашли подходящую маску для текущего набора символов (количество достигло '.count($columnsSet).')';
                    //echo '<pre>'; print_r($columnsSet); echo '</pre>';
                    //echo '<pre>'; print_r($mask[4]); echo '</pre>';
                    break 2;
                }
        	}
        }
        return $phoneNumber;
    }

    function error($text)
    {
        echo '<div class="alert alert-danger">'.$text.'</div>';
    }
}

?>

