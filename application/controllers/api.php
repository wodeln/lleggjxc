<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct(){
        parent::__construct();
//		$this->common_model->checkpurview();
    }
	
   //用户接口开始

    /**
     * 获取所有用户
     */
	public function getAllUsers(){
        $usersJson = file_get_contents('http://www.llegg.com/index.php/Api/Jxcapi/getAllUser');
        $users=json_decode($usersJson,TRUE);
        $data = "";
        foreach ($users as $k=>$v){
            $address="";
            $data['name']=$v['nickname'];
            $data['number']="DS".$v['user_id'];
            $data['cCategory']="5";
            $data['cCategoryName']="001";
            $data['cLevelName']=iconv('GB2312', 'UTF-8',"零售客户");
            $data['cLevel']="0";
            $data['type']=-10;
            $data['shop_user_id']=$v['user_id'];
            foreach ($v['address'] as $key=>$value){
                $address[$key]["linkName"]=$value["consignee"];
                $address[$key]["linkMobile"]=$value["mobile"];
                $address[$key]["linkPhone"]="";
                $address[$key]["linkIm"]="";
                $address[$key]["province"]=$value["province_str"];
                $address[$key]["city"]=$value["city_str"];
                $address[$key]["county"]=$value["county_str"];
                $address[$key]["address"]=$value["address"];
                $address[$key]["linkFirst"]= $value["is_default"];
                $address[$key]["id"]= $value["address_id"];
            }
            $data['linkMans']=json_encode($address,JSON_UNESCAPED_UNICODE);
            $this->save_log($data,"user",__FUNCTION__);
            $this->mysql_model->insert("ci_contact",$data);
        }
    }

    /**
     * 添加用户
     */
    public function insertUser(){
        $data = $_POST;
        $this->save_log($data,"user",__FUNCTION__);
        $res = $this->mysql_model->insert("ci_contact",$data);
        echo $res;
    }

    /**
     * 编辑用户
     */
    public function updateUser(){
        $data = $_POST;
        $this->save_log($data,"user",__FUNCTION__);
        $where = "(shop_user_id=".$data['userId'].")";
        unset($data["userId"]);
        $res = $this->mysql_model->update("ci_contact",$data,$where);
        echo $res;
    }
    //用户接口结束

    //商品接口开始
    /**
     * 添加商品
     */
    public function insertGoods(){
        $data = $_POST;

        /*$data = array (
            'name' => iconv('GB2312', 'UTF-8','土鸡蛋/箱/净重48斤/420枚/称差≤0kg/破损≤5枚'),
            'number' => 'JDT00001',
            'unitName' => iconv('GB2312', 'UTF-8','箱装'),
            'categoryName' => iconv('GB2312', 'UTF-8','土鸡蛋 纸箱'),
            'shop_goods_id' => '2',
        );*/


        $this->save_log($data,"goods",__FUNCTION__);
        $this->load->library('lib_cn2pinyin');
        $data['pinYin'] = $this->lib_cn2pinyin->encode($data['name']);
        $data['baseUnitId'] = $this->mysql_model->get_results(UNIT,"(`name` = '".$data['unitName']."')",'id')[0]['id'];
        $data['categoryId'] = $this->mysql_model->get_results(CATEGORY,"(`name` = '".$data['categoryName']."')",'id')[0]['id'];
        $res = $this->mysql_model->insert(GOODS,$data);
        echo $res;;
    }

    public function updateGoods(){
        $data = $_POST;
        $this->save_log($data,"goods",__FUNCTION__);
        $this->load->library('lib_cn2pinyin');
        $data['pinYin'] = $this->lib_cn2pinyin->encode($data['name']);
        $data['baseUnitId'] = $this->mysql_model->get_results(UNIT,"(`name` = '".$data['unitName']."')",'id')[0]['id'];
        $data['categoryId'] = $this->mysql_model->get_results(CATEGORY,"(`name` = '".$data['categoryName']."')",'id')[0]['id'];
        $where = "(shop_goods_id=".$data['shop_goods_id'].")";
        $res = $this->mysql_model->update(GOODS,$data,$where);
        echo $res;
    }

    //商品接口结束
    /**
     * 日志方法
     * @param $res 传递数组详情
     * @param string $functionName 调用接口方法名称
     * @param string $url 调用接口URL
     */
    function save_log($res,$type,$functionName="",$url="") {
        $date = date("Y-m-d", time());
        //$address = '/var/log/error';
        $address = './application/apilog';
        if (!is_dir($address)) {
            mkdir($address, 0777, true);
        }
        $address = $address.'/'.$date . '_'.$type.'.log';
        $error_date = date("Y-m-d H:i:s", time());
        if(!empty($_SERVER['HTTP_REFERER'])) {
            $file = $_SERVER['HTTP_REFERER'];
        } else {
            $file = $_SERVER['REQUEST_URI'];
        }

        $res_real = "$error_date\t$file\t$functionName";
        file_put_contents($address, $res_real . PHP_EOL, FILE_APPEND);
        if($url!=""){
            file_put_contents($address, $url . PHP_EOL, FILE_APPEND);
        }
        $res = var_export($res,true);
        $res = $res."\n";
        file_put_contents($address, $res . PHP_EOL, FILE_APPEND);

    }
}