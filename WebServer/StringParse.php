<?php
/**
 * Created by IntelliJ IDEA.
 * User: Hwansuk Choi <heychs@gmail.com>
 * Date: 2016-12-02
 * Time: 오후 7:40
 * Description: 마트의 MMS를 받은 MMSForwarding APP이 MMS 내용을 DB에 해당 마트의 데이터로 저장하고
 *              mart-promotion-parser를 호출하여 MMS로부터 할인상품데이터를 저장하도록 함.
 */

$promotion_message_sample = "[Web발신]
(광고)즐거운마트세일

이마트에브리데이 상품공급점 즐거운마트
☏ 02-523-0497

**야채**
애호박1개 980
꽈리고추100g 690
양파10개 2980
팽이버섯3봉 1000
통연근100g 298
물미역1팩 1000
달래1개 1000
강릉심층수왕콩나물1봉 1000
콜라비1개 1000

**청과**
청송알뜰사과3kg 9800
고당도설향딸기(특품)1팩 8800
황금향8개 7800
고당도타이백밀감5kg 12800
씨없는청포도100g 850
골드키위1+1 9800
유자청1kg 4900

**수산**
봉지굴1봉 1980
동태(특대)2마리 5980
명태코다리(특대)4마리 5980

**정육**
한우업진살(1+등급)로스구이
100g 8480
한우사태(1+등급)
100g 3980
한우사골100g 990
한우샤브샤브(1+등급)
100g 5580
돼지등갈비100g 1980
돼지앞다리(보쌈,찌게)
100g 1180
훈제오리500g 8800

**양곡**
순수미20kg 35000
경기미20kg 48000
철원오대쌀10kg 33800
찹쌀4kg 12800
찰현미4kg 12800
현미4kg 12800

**공산품**
롯데아이시스생수2L*6입 3000
롯데초코파이12입 1980
스낵번들1번들 2980
서천재래김16입 3800
백설군만두450g*2입 6980
cj야채물만두350g*2입 6980
cj우동4인 5980
동원마일드참치210g 1980
펭귄고등어,꽁치캔400g 1980
오뚜기죽1개 2300
햇반컵반1개 2500
풀무원)육개장칼국수5입 4980
롯데런천미트340g 2350
비트세제2L+2L 9900
샤프란2000mL 2500
주방세제1.2k 2500
휴지30롤 9900

무료수신거부
080-833-1598

================
투게더S는 모바일 고객카드!
실시간으로 내 포인트 확인!
단골마트의 세일전단 확인!
매주 받는 1,000원 할인쿠폰!
왕대박 이벤트 응모의 기회!
(매장에 따라 다를수 있음)
아이폰 --> 앱스토어 --> 투게더S
안드로이드폰 --> 플레이스토어 --> 투게더S
";

$promotion_message = $promotion_message_sample;

// 받은 MMS 로부터 해당 Mart 의 ID 를 찾아냄
$mart_name = find_mart_name_by_promotion_message($promotion_message);
//$mart_id = find_mart_id_by_mart_name($mart_name);

// 해당하는 마트가 있다면 받은 메시지를 저장하고 파싱하여 할인 상품 데이터를 저장 후 푸시 알림
if (isset($mart_name)) {

    $promotion_products = parse_promotion_message($mart_name, $promotion_message);

    /*if (isset($promotion_products)) {
        update_user_meta($mart_id, '_fooding_promotion_message', $promotion_message);
        update_user_meta($mart_id, '_fooding_promotion_products', $promotion_products);

        // 메시지 받은 시간 저장(유닉스 형식)
        update_user_meta($mart_id, '_fooding_promotion_lastupdate', date("U"));
    }*/


    $message = "'s promotion message is parsed and saved at ";
    date_default_timezone_set("Asia/Seoul");
    //$lastupdate = date("F j, Y, g:i a", get_user_meta($mart_id, '_fooding_promotion_lastupdate', true));

    echo $mart_name . $message . $lastupdate;
    print_r($promotion_products);
}
else {
    $message = "마트이름을 찾을 수 없습니다. 메시지에 마트이름을 정확히 포함해주세요.";
    echo $message;
}

/* php 코드에서 script 로서 console창에 error log 출력하기
echo "<script>console.log('PHP debugger: mart-promotion');</script>";
$output  = "<script>console.log( '";
$output .= json_encode(print_r($promotion_message, true));
$output .= "' );</script>";
echo $output;
echo "\n";
*/

/**
 * find_mart_name_by_promotion_message. 할인 문자를 파싱하여 해당하는 마트의 이름를 찾아냄
 * parameter(string : 원본 할인 문자) return(int : mart_name)
 */
function find_mart_name_by_promotion_message($promotion_message)
{
    // 문자자체에서 마트이름 검사. 마트이름이 나온 문자열 위치를 찾아 그 이후부터 확인한다
    // 혹은 개행문자를 기준으로 텍스트를 한줄한줄 Split 하여 배열에 저장한 다음
    // '마트'라는 문자를 가진 행에서 한글자씩 검사하며 찾을 수도 있겠지만 비효율적일 것 같음
    global $promotion_message;

    if($promotion_message = strstr($promotion_message, "즐거운마트"))
        $mart_name = "즐거운마트";
    elseif($promotion_message = strstr($promotion_message, "럭키할인마트"))
        $mart_name = "럭키할인마트";
    return $mart_name;
}

/**
 * find_mard_id_by_mart_name. 할인 문자를 파싱하여 해당하는 마트의 id 를 찾아냄
 * parameter(string : mart_name) return(int : mart_id)
 */
function find_mart_id_by_mart_name($mart_name)
{
    global $wpdb;

    // 쿼리를 이용하여 mart_name으로 mart_id 알아내기
    if($mart_name != null) {
        $ids = $wpdb->get_results("SELECT ID FROM $wpdb->users WHERE display_name = '$mart_name'");
    }
    if (count($ids) != 0)
        return $ids[0]->ID;
    else
        return null;
}


/**
 * parse_promotion_message. 할인 문자를 파싱하여 할인 상품 데이터를 뽑아냄
 * 마트에 따른 문자 파싱 알고리즘 추가 구현
 * parameter(string : 마트 이름, string : 원본 할인 문자) return(mixed : 할인 상품 데이터)
 */
function parse_promotion_message($mart_name, $promotion_message)
{
    switch ($mart_name) {
        case "즐거운마트" :
            // ※ [Web발신]과 일반발신 둘로 보내진다.

            // 개행문자를 기준으로 텍스트를 한줄한줄 Split 하여 배열에 저장
            $text_arr = preg_split("/\r\n|\n|\r/", trim($promotion_message)) ;

            foreach ($text_arr as $linenum => $line) {
                $line = trim($line);

                // 숫자로 끝나는 라인들을 골라낸다.
                if ( preg_match_all("/.*\d$/", $line) ) {
                    // 이전 line 합치기
                    if(!empty($preline))
                        $line = $preline . $line;

                    // 뒤에서부터 숫자가 끝나는 곳까지 읽고 상품 이름과 가격 분리
                    $cursor = strlen($line);
                    while($cursor != 0){
                        $char = $line{$cursor-1};
                        //if(!preg_match_all("/[0-9]|\>|\-/", $char)) {
                        if ( !(is_numeric($char) || $char == ">" || $char == "-") ) {
                            break;
                        }
                        $cursor--;
                    }
                    $title = trim(substr($line, 0, $cursor));
                    $substr_price = substr($line, $cursor);
                    // "-" 기호를 포함하는 지 확인. 1)전화번호이거나 2)할인된 가격임
                    if (preg_match_all("/\-+/", $substr_price)) {
                        // "-->" 기호를 통해 가격 변동이 표시된 경우 Sale price 와 Regular price 분리
                        if (preg_match_all("/\>+/", $substr_price)) {
                            $substr_price = preg_replace("/[\-\>]/", " ", $substr_price);
                            $substr_price = preg_split("/\s+/", $substr_price);
                            $regular_price = $substr_price[0];
                            $sale_price = $substr_price[1];
                        }
                        // 전화번호
                        else {
                            $preline = "";
                            continue;
                        }
                    }
                    // 그러한 기호를 포함하지 않는다면 할인가격만 표시한 것이므로 할인가격으로 저장
                    else
                        $sale_price = $substr_price;


                }
                // 카테고리 분류. "**"로 둘러싸여 있다면 카테고리로 인식하고 저장
                elseif(preg_match_all("/\*+.*\*+/", $line)) {
                    $category = str_replace("**", "", $line);   // "**" 제거
                }
                // 제목도 아니고 숫자로 끝나는 것도 아닌데 빈것도 아니라면 이어지는 건데 개행되었다 생각하고 그냥 다음 line과 합치기위해 준비.
                elseif (!empty($line))
                {
                    $preline = $line;
                    continue;
                }

                // 저장 및 초기화
                if($sale_price != NULL) {
                    $sale_price = floatval($sale_price);
                    if($sale_price > 1000000)
                        continue;
                    if($regular_price != NULL)
                        $regular_price = floatval($regular_price);

                    //$promotion_products[] = array("title" => $title, "categories" => array($category), "regular_price" => $regular_price, "sale_price" => $sale_price);
                    $product = new Product($title, $category, $regular_price, $sale_price);
                    if($product->validation_check())
                        $promotion_products[] = array("title" => $product->title, "categories" => array($product->category), "regular_price" => $product->regular_price, "sale_price" => $product->sale_price);

                    $preline = "";
                    $regular_price = NULL;
                    $sale_price = NULL;
                }

            } // end foreach
            /*echo "<pre>";
            echo json_encode($product);
            echo json_decode($json);
            echo "</pre>";*/
            break;

        case "럭키할인마트":

        default : break;
    }
    return $promotion_products;
}

/**
 * product_validation_check. 할인 상품 데이터를 검증함
 * parameter() return(bool : 유효성)
 */

class Product {
    //public $title;
    //public $category;
    //public $regular_price;
    //public $sale_price;

    private $data = array();

    function __construct($title, $category = NULL, $regular_price = NULL, $sale_price)
    {
        /*$this->title = $title;
        $this->category = $category;

        if ( !is_float($this->sale_price) )
            $this->sale_price = floatval($this->sale_price);
        else
            $this->sale_price = $sale_price;

        if($this->regular_price != NULL) {
            if ( !is_float($this->regular_price) )
                $this->regular_price = floatval($this->regular_price);
            else
                $this->regular_price = $regular_price;
        }*/

        $this->data['title'] = $title;
        $this->data['category'] = $category;
        $this->data['sale_price'] = $sale_price;
        $this->data['regular_price'] = $regular_price;
    }

    public function __set($name, $value)
    {
        if(strstr($name, 'price'))
        {
            if( !is_float($value) )
                $value = floatval($value);
        }

        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function validation_check()
    {
        if ( $this->data['title'] != NULL && $this->data['sale_price'] != NULL )
        {
            if($this->data['sale_price'] < 1000000)
                return true;
        }

        return false;
    }
}