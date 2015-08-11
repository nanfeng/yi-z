<?php 

/**
* 
*/
class Util
{

    private $user = 'ylcdcd';
    private $pass = 'xdb1989123';

    //login
    private $cookie_file = "";
    private $loginid_cookie_file = "";
    private $guid_file = "";
    private $login_file = "";
    private $code_file = '';
    private $data_file = '';
    private $user_file = '';
    private $code_url = 'http://sso.yi-z.com/CaptchaImage.aspx?guid=';
    private $guid_url = 'http://sso.yi-z.com/';
    private $index_url = 'http://admin.yi-z.cn/Account/Login';
    private $login_url = 'http://sso.yi-z.com/login.aspx';
    private $commit_url = 'http://admin.yi-z.cn/product/productedit';
    private $log_file = "";

    public function __construct()
    {
        $this->cookie_file = realpath(dirname(__FILE__)."/../../../")."/Public/cookie/cookie";
        $this->loginid_cookie_file = realpath(dirname(__FILE__)."/../../../")."/Public/cookie/loginidcookie";
        $this->guid_file = realpath(dirname(__FILE__)."/../../../")."/Public/img/guid";
        $this->login_file = realpath(dirname(__FILE__)."/../../../")."/Public/img/loginid";
        $this->code_file = realpath(dirname(__FILE__)."/../../../")."/Public/img/code.png";
        $this->data_file = realpath(dirname(__FILE__)."/../../../")."/Public/data/data.txt";
        $this->user_file = realpath(dirname(__FILE__)."/../../../")."/Public/cookie/user";
        $this->status_file = realpath(dirname(__FILE__)."/../../../")."/status.txt";
        $this->log_file = realpath(dirname(__FILE__)."/../../../")."/log.txt";


        // $this->cookie_file = dirname(__FILE__)."/cookie";
        // $this->loginid_cookie_file = dirname(__FILE__)."/loginidcookie";
        // $this->guid_file = dirname(__FILE__)."/guid";
        // $this->login_file = dirname(__FILE__)."/loginid";
        // $this->code_file = dirname(__FILE__)."/code.png";
        // $this->data_file = dirname(__FILE__)."/data.txt";
        // $this->status_file = dirname(__FILE__)."/status.txt";
        // $this->log_file = dirname(__FILE__)."/log.txt";
        file_put_contents($this->log_file, "");
    }

    /**
    * 创建cookie路径，清空cookie内容     *
    */
    public function initCookie()
    {
        $dir = dirname($this->cookie_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->cookie_file, "");

        $dir = dirname($this->loginid_cookie_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->loginid_cookie_file, "");

        $dir = dirname($this->user_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->user_file, "");
    }

    /**
    * 创建验证码图片路径，删除已有的图片
    */
    public function initCode()
    {
        $dir = dirname($this->code_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->code_file, "");
        
        $dir = dirname($this->guid_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->guid_file, "");

        $dir = dirname($this->login_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->login_file, "");
    }

    /**
    * 创建文件上传路径，删除原有文件
    */
    public function initData()
    {
        $dir = dirname($this->data_file);
        is_dir($dir) || mkdir($dir);
        file_put_contents($this->data_file, "");
    }

    /**
     * @param  string
     * @param  string
     * @param  string
     * @param  boolean
     * @param  boolean
     * @return [type]
     */
    public function curl_func_get($url='', $cookie_file='', $referer='', $header=true, $returntransfer=true)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
        curl_setopt($ch, CURLOPT_REFERER, $referer);

        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }

    public function curl_func_post($url='', $cookie_file='', $fields_string='', $referer='', $header=true, $returntransfer=true)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

        $out = curl_exec($ch);
        curl_close($ch);
        return $out;
    }

    /**
     * 获取验证码图片、loginid、signtext、生成cookie
     * @return [type]
     */
    public function index()
    {
        $this->initCookie();
        $this->initCode();
        // $this->initData();
        $out = $this->curl_func_get($this->index_url, $this->cookie_file);

        //登陆页面第一次302
        list($header) = explode("\r\n\r\n", $out, 2);
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $out = $this->curl_func_get($url, $this->loginid_cookie_file);

        //登陆页面第二次302
        list($header) = explode("\r\n\r\n", $out, 2);
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $out = $this->curl_func_get($url, $this->cookie_file);

        $parse = (parse_url($url));
        if (count($parse) > 3) {
            parse_str($parse['query'], $arr);
            $loginid = $arr['loginid'];
            $signtext = $arr['signtext'];
        }else{
            preg_match('/\<input type=hidden name="loginid" value="(.*)" \/\>/', $out, $matches);
            $loginid = $matches[1];
            preg_match('/\<input type=hidden name="signtext" value="(.*)" \/\>/', $out, $matches);
            $signtext = $matches[1];
        }

        file_put_contents($this->login_file, $loginid.','.$signtext);

        //guid
        $out = $this->curl_func_get($this->guid_url, $this->loginid_cookie_file, '', false);
        $out = strip_tags($out);
        $out = substr($out, strpos($out, '\'')+1);
        $out = substr($out, 0, strpos($out, '\''));
        //img
        $this->code_url .= $out;
        $out = $this->curl_func_get($this->code_url, $this->loginid_cookie_file, '', false);
        file_put_contents($this->code_file, $out);
    }

    /**
     * 登陆
     * @param  [type] $user [description]
     * @param  [type] $pass [description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function login($user, $pass, $code)
    {
        file_put_contents($this->user_file, $user);

        $line = file_get_contents($this->login_file);
        $line = trim($line);
        $splits = explode(',', $line);
        $fields_post = array(
                'UserName' => $user, 
                'Password' => $pass, 
                'captcha' => $code,
                'RememberMe' =>25200,
                'loginAddress' => 'http://sso.yi-z.com/login.aspx',
                'loginid' => $splits[0],
                'signtext' => $splits[1],
                'uid' => '',
                'fromurl' => 'http://admin.yi-z.cn/Account/Login'
                );
        $fields_string = http_build_query($fields_post);
        $out = $this->curl_func_post($this->login_url, $this->loginid_cookie_file, $fields_string, 'http://admin.yi-z.cn/Account/Login');

        //登陆第一次302
        list($header) = explode("\r\n\r\n", $out, 2);
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        if ($url == 'http://admin.yi-z.cn/Account/Login?err=-9') {
            return $out;
        }
        $out = $this->curl_func_get($url, $this->cookie_file, 'http://admin.yi-z.cn/Account/Login');

        //登陆第二次302
        list($header) = explode("\r\n\r\n", $out, 2);
        preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        $url = 'http://admin.yi-z.cn'.$url;
        $out = $this->curl_func_get($url, $this->cookie_file, 'http://admin.yi-z.cn/Account/Login');
        return $out;
    }

        /**
        * 字符编码转换为utf8
        */
        public function charsetConvert($data)
        {
                $filetype = mb_detect_encoding($data , array('utf-8','gbk','latin1','big5')) ;

                if( $filetype != 'utf-8'){
                   $data = mb_convert_encoding($data ,'utf-8' , $filetype); 
                }
                return $data;
        }

        /**
        * 解析上传的文件
        */
//'oImgB=&pid=5_636_638
        // &Name=aaaaa&SEOTitle=aaaaa&SEOKeywords=aaaaaa&SEODescription=aaaaaa
        // &SellerPid=1672702&Img_s0=http%3A%2F%2Fwww.dginfo.com%2Fimages%2Fnoimg%2F128_128.jpg&Img_s1=http%3A%2F%2Fwww.dginfo.com%2Fimages%2Fnoimg%2F128_128.jpg&Img_s2=http%3A%2F%2Fwww.dginfo.com%2Fimages%2Fnoimg%2F128_128.jpg&Img_s3=http%3A%2F%2Fwww.dginfo.com%2Fimages%2Fnoimg%2F128_128.jpg
        // &brief=aaaaaaaaaa&FCKeditor1=aaaaaaaaaaaaaaaaaaaaaaaaaaaaa
        // &Price=0&Price2=0&Price3=0&Price4=0&Stock=0&OrderNum=0&Deliver1=0&Deliver2=0&Deliver3=0&Audit=0&ReservationsNum=0&MemberLevel=0';
        public function parseFile()
        {
                $content = file_get_contents($this->data_file);
                $match = preg_split('/[-]{5,}\d{0,3}/', $content);// -----10 处理这种分割
                $res = array();
                $user = file_get_contents($this->user_file);

                if ($user == 'ylcdcd'){
                    $cid = 3400767;
                    $cname = '';
                }
                else{
                    $cid = 3395727;
                    $cname = '';
                }

                for($i=0; $i<count($match); $i++){
                        $line = trim($match[$i]);
                        $pos = strpos($line, "\n");
                        $title = trim(substr($line, 0, $pos-1));
                        $title = $this->charsetConvert($title);
                        if (empty($title))
                                continue;
                        $cont = trim(substr($line, $pos));
                        $cont = $this->charsetConvert($cont);
                        $cont = str_replace(array("\r\n", "\r", "\n"), "<br/>", $cont); 
                        $res[] = array(
                                'ProductInfo.ShowState' => 1,
                                'ProductInfo.CatalogName' => $cname,
                                'ProductInfo.CatalogID' => $cid,
                                'ProductInfo.BrandName' => ' ',
                                'ProductInfo.BrandID' => 0,
                                'ProductInfo.ProductName' => $title,
                                'ProductInfo.DffixationTitle' => $title,
                                'ProductInfo.ProductModel' => '',
                                'ProductInfo.ProductIntro' => '招标',
                                'ProductInfo.ProductDetailIntro' => $cont,
                                'ProductInfo.JoinDate' => date('Y-m-d H:i:s').'.774',
                                'ProductInfo.Manufacturer' => '',
                                'ProductInfo.CustomID' => '',
                                'ProductInfo.Inventory' => 0,
                                'ProductInfo.Price' => 0,
                                'ProductInfo.DiscountPrice' => 0,
                                'ProductInfo.MemberPrice' => 0,
                                'ProductInfo.EntryName' => '',
                                'ProductInfo.ProductID' => 0,
                                'ProductInfo.SmallImgName' => '',
                                'ProductInfo.BigImgName' => '',
                                'TempImgName' => '',
                                'DeleteImg' => '0,0,0',
                                'CheckDataIntro' => 'true',
                                'CheckContent' => 'false',
                                'X-Requested-With' => 'XMLHttpRequest'
                                );
                }
                return $res;
        }

    public function commitData()
    {
         // $fields_post = array(
         //        'ProductInfo.ShowState' => 1,
         //        'ProductInfo.CatalogName' => '招标',
         //        'ProductInfo.CatalogID' => 3390801,
         //        'ProductInfo.BrandName' => '其它品牌',
         //        'ProductInfo.BrandID' => 3327816,
         //        'ProductInfo.ProductName' => '2产品名称2产品名称',
         //        'ProductInfo.DffixationTitle' => '22222222222',
         //        'ProductInfo.ProductModel' => '',
         //        'ProductInfo.ProductIntro' => '2产品名称2产品名称2产品名称2产品名称',
         //        'ProductInfo.ProductDetailIntro' => '333333333333333333333333333333333333333333333',
         //        'ProductInfo.JoinDate' => date('Y-m-d H:i:s').'.774',
         //        'ProductInfo.Manufacturer' => '',
         //        'ProductInfo.CustomID' => '',
         //        'ProductInfo.Inventory' => 0,
         //        'ProductInfo.Price' => 0,
         //        'ProductInfo.DiscountPrice' => 0,
         //        'ProductInfo.MemberPrice' => 0,
         //        'ProductInfo.EntryName' => '',
         //        'ProductInfo.ProductID' => 0,
         //        'ProductInfo.SmallImgName' => '',
         //        'ProductInfo.BigImgName' => '',
         //        'TempImgName' => '',
         //        'DeleteImg' => '0,0,0',
         //        'CheckDataIntro' => 'true',
         //        'CheckContent' => 'false',
         //        'X-Requested-With' => 'XMLHttpRequest'
         //        );
//ProductInfo.ShowState=1&ProductInfo.CatalogName=%E6%8B%9B%E6%A0%87&ProductInfo.CatalogID=3390801&ProductInfo.BrandName=%E5%85%B6%E5%AE%83%E5%93%81%E7%89%8C
//&ProductInfo.BrandID=3327816&ProductInfo.ProductName=1%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B0
//&ProductInfo.DffixationTitle=22222222&ProductInfo.ProductModel=&ProductInfo.ProductIntro=1%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B0
//&ProductInfo.ProductDetailIntro=3333333333333333333333333333333333333333333333
//&ProductInfo.JoinDate=2015-08-08+18%3A00%3A41.950&ProductInfo.Manufacturer=&ProductInfo.CustomID=&ProductInfo.Inventory=0
//&ProductInfo.Price=0&ProductInfo.DiscountPrice=0&ProductInfo.MemberPrice=0&ProductInfo.EntryName=&ProductInfo.ProductID=0
//&ProductInfo.SmallImgName=&ProductInfo.BigImgName=&TempImgName=&DeleteImg=0%2C0%2C0&CheckDataIntro=true
//&CheckContent=false&X-Requested-With=XMLHttpRequest

        $arr = $this->parseFile();
        $content = '';
        $res = array();
        $timearr = array(30,40,38,53,46);
        $t = 0;
        $res = array();

        foreach ($arr as $item) {
            $fields_string = http_build_query($item);
            
            sleep($timearr[$t%5]);
            $t++;

            $out = $this->curl_func_post($this->commit_url, $this->cookie_file, $fields_string, 'http://admin.yi-z.cn/product/productedit', false, true);
            file_put_contents($this->status_file, "upload:".$t."\t\t total:".count($arr));
$this->writeLog($t."\t".$out);
            if(strstr($out, '{"Status":0,"') === false)
            {
                $res[] = array(
                    'title' => 'error:'.$item['ProductInfo.ProductName'],
                    'status' => 0
                );
            }else{
                $res[] = array(
                        'title' => $item['ProductInfo.ProductName'],
                        'status' => 1
                    );
            }
        }

        return $res;


        // $arr = $this->parseFile();
        // $fields_post = $arr[0];
        // $fields_string = http_build_query($fields_post);
        // $out = $this->curl_func_post($this->commit_url, $this->cookie_file, $fields_string, 'http://admin.yi-z.cn/product/productedit', false, false);
        // return $out;
    }

    public function writeLog($str)
    {
        file_put_contents($this->log_file, $str."\n", FILE_APPEND | LOCK_EX);
    }
}

// $c = new Util();
// $c->index();
// sleep(20);
// $login = $c->login('ylcdcd', 'xdb1989123', '22');
// $commit = $c->commitData();
// echo "<pre>";
// // print_r($login);
// echo "====================";
// print_r($commit);

//{"Status":0,"InsertedRowID":2007756659,"Message":null,"Exception":null}

// $fields_post = array(
//         'ProductInfo.ShowState' => 1, 
//         'ProductInfo.CatalogName' => '%E4%BA%A7%E5%93%81', 
//         'ProductInfo.CatalogID' => '3327969',
//         'ProductInfo.BrandName' =>'%E5%85%B6%E5%AE%83%E5%93%81%E7%89%8C',
//         'ProductInfo.BrandID' => '3327816',
//         'ProductInfo.ProductName' => '%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01',
//         'ProductInfo.DffixationTitle' => '+%E9%99%84%E5%8A%A0%E4%BF%A1%E6%81%AF+1',
//         'ProductInfo.ProductModel' => '',
//         'fromurl' => 'http://admin.yi-z.cn/Account/Login'
//         );
// $fields_string = http_build_query($fields_post);




// $fields_string = 'ProductInfo.ShowState=1&ProductInfo.CatalogName=%E4%BA%A7%E5%93%81&ProductInfo.CatalogID=3327969&ProductInfo.BrandName=%E5%85%B6%E5%AE%83%E5%93%81%E7%89%8C&ProductInfo.BrandID=3327816&ProductInfo.ProductName=%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01&ProductInfo.DffixationTitle=+%E9%99%84%E5%8A%A0%E4%BF%A1%E6%81%AF+1&ProductInfo.ProductModel=%E4%BA%A7%E5%93%81%E5%9E%8B%E5%8F%B71&ProductInfo.ProductIntro=%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01%0D%0A%E4%BA%A7%E5%93%81%E5%90%8D%E7%A7%B01&ProductInfo.ProductDetailIntro=%E8%AF%A6%E7%BB%86%E8%AF%B4%E6%98%8E+%E8%AF%A6%E7%BB%86%E8%AF%B4%E6%98%8E+%E8%AF%A6%E7%BB%86%E8%AF%B4%E6%98%8E+1&ProductInfo.JoinDate=2015-08-03+15%3A59%3A45.034&ProductInfo.Manufacturer=&ProductInfo.CustomID=&ProductInfo.Inventory=0&ProductInfo.Price=0&ProductInfo.DiscountPrice=0&ProductInfo.MemberPrice=0&ProductInfo.EntryName=&ProductInfo.ProductID=0&ProductInfo.SmallImgName=&ProductInfo.BigImgName=&TempImgName=&DeleteImg=0%2C0%2C0&CheckDataIntro=true&CheckContent=false&X-Requested-With=XMLHttpRequest';
// $url = 'http://admin.yi-z.cn/Account/Login?ReturnUrl=/product/productedit';

// $ch = curl_init($url);
// curl_setopt($ch, CURLOPT_HEADER, true);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_POST, 1) ; // 启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。  
// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
// // curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file); //保存  
// //curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt '); //读取  
// curl_setopt($ch, CURLOPT_REFERER, 'http://admin.yi-z.cn/product/productedit');
// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
// curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
// curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
// // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 2);
// // curl_setopt($ch, CURLOPT_COOKIESESSION, 1); 
// // curl_setopt($ch, CURLOPT_MAXREDIRS, 5); 
// curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);

// $out = curl_exec($ch);
// curl_close($ch);
// var_dump($out);

 ?>