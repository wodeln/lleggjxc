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
        $usersJson = file_get_contents('http://www.llegg.cn/index.php/Api/Jxcapi/getAllUser');
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
     * 更新所有用户地址
     */
    public function updateAllUsers(){
        $usersJson = file_get_contents('http://www.llegg.cn/index.php/Api/Jxcapi/getAllUser');
        $users=json_decode($usersJson,TRUE);
        $data = "";
        foreach ($users as $k=>$v){
            $address="";
            foreach ($v['address'] as $key=>$value){
                $address[$key]["linkName"]=$value["consignee"];
                $address[$key]["linkMobile"]=$value["mobile"];
                $address[$key]["linkPhone"]="";
                $address[$key]["linkIm"]="";
                $address[$key]["province"]=$value["province_str"];
                $address[$key]["city"]=$value["city_str"];
                $address[$key]["county"]=$value["country_str"];
                $address[$key]["address"]=$value["address"];
                $address[$key]["linkFirst"]= $value["is_default"];
                $address[$key]["id"]= $value["address_id"];
            }
            $data['linkMans']=json_encode($address,JSON_UNESCAPED_UNICODE);
            $where = "(shop_user_id=".$v['user_id'].")";
            $this->save_log($data,"user",__FUNCTION__);
            $this->mysql_model->update("ci_contact",$data,$where);
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
        $addressFromJson = json_decode($data["linkMans"],TRUE);
        $address = "";
        foreach ($addressFromJson as $key=>$value){
            $address[$key]["linkName"]=$value["consignee"];
            $address[$key]["linkMobile"]=$value["mobile"];
            $address[$key]["linkPhone"]="";
            $address[$key]["linkIm"]="";
            $address[$key]["province"]=$value["province_str"];
            $address[$key]["city"]=$value["city_str"];
            $address[$key]["county"]=$value["country_str"];
            $address[$key]["address"]=$value["address"];
            $address[$key]["linkFirst"]= $value["is_default"];
            $address[$key]["id"]= $value["address_id"];
        }
        $data['linkMans']=json_encode($address,JSON_UNESCAPED_UNICODE);
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

    //订单接口开始
    /**
     * 插入订单
     */
    public function insertOrder(){
        $data = $_POST;
//        $data[0] = '{"order_id":2361,"order_sn":"201710171045558712","user_id":605,"order_status":0,"shipping_status":0,"pay_status":0,"consignee":"\u8d75\u4e1c","country":0,"province":10543,"city":10544,"district":10560,"twon":0,"address":"\u94a6\u5dde\u5317\u8def874\u53f7","zipcode":"","mobile":"15821530726","email":"","shipping_code":"ziyouwuliu","shipping_name":"\u9f99\u9f99\u8f66","pay_code":"cod","pay_name":"\u5230\u8d27\u4ed8\u6b3e","invoice_title":"","goods_price":"2850.00","shipping_price":"50.00","user_money":"0.00","coupon_price":"0.00","integral":0,"integral_money":"0.00","order_amount":"2900.00","total_amount":"2900.00","add_time":1508208355,"shipping_time":0,"confirm_time":0,"pay_time":0,"order_prom_id":0,"order_prom_amount":"0.00","discount":"0.00","user_note":"","admin_note":"","parent_sn":null,"is_distribut":0,"driver_id":11,"delivery_sort":16,"deliver_opt_time":1508256000,"products":[{"goods_id":9,"cat_id":11,"extend_cat_id":8,"goods_sn":"JDYP0001","goods_name":"\u666e\u901a\u6d0b\u9e21\u86cb\/\u6846\/\u51c0\u91cd27\u65a4\/\u79f0\u5dee\u22640.1kg\/\u7834\u635f\u226410\u679a","click_count":15,"brand_id":1,"store_count":9937,"comment_count":0,"weight":500,"market_price":"0.00","shop_price":"99.00","cost_price":"0.00","keywords":"","goods_remark":"","goods_content":"&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u5546\u54c1\u4fe1\u606f&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u5546\u54c1\u540d\u79f0\uff1a\u6d0b\u9e21\u86cb\uff08\u666e\u901a\uff09&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-size: 14px;&quot;&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u5546\u54c1\u89c4\u683c\uff1a&lt;\/span&gt;&lt;span style=&quot;font-family: Calibri; font-size: 14px;&quot;&gt;27&lt;\/span&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u65a4&lt;\/span&gt;&lt;span style=&quot;font-family: Calibri; font-size: 14px;&quot;&gt;\/&lt;\/span&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u7b50&lt;\/span&gt;&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u50a8\u85cf\u65b9\u5f0f\uff1a\u8bf7\u653e\u4e8e\u9634\u51c9\u5e72\u71e5\u5904\u6216\u51b7\u85cf&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;&lt;img width=&quot;497&quot; height=&quot;499&quot; title=&quot;\u6d0b\u9e21\u86cb-01.jpg&quot; style=&quot;width: 287px; height: 284px;&quot; src=&quot;\/Public\/upload\/goods\/2017\/07-27\/59798c9ddc4aa.jpg&quot;\/&gt;&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;&lt;br\/&gt;&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u5546\u54c1\u7279\u70b9&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-size: 14px;&quot;&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u9996\u5148\u6d0b\u9e21\u86cb\u4e2d\uff0c\u94c1\u3001\u9499\u3001\u9541\u7b49\u77ff\u7269\u8d28\u5143\u7d20\u7684\u542b\u91cf\u90fd\u9ad8\u4e8e&lt;\/span&gt;&lt;span style=&quot;font-family:; font-size: 14px;&quot;&gt;&lt;a href=&quot;https:\/\/baike.baidu.com\/item\/%E5%9C%9F%E9%B8%A1%E8%9B%8B&quot; target=&quot;_blank&quot;&gt;&lt;span style=&quot;color: windowtext; font-family: \u5b8b\u4f53; font-size: 14px; text-underline: none;&quot;&gt;\u571f\u9e21\u86cb&lt;\/span&gt;&lt;\/a&gt;&lt;\/span&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;\u3002\u9002\u7528\u4e8e\u9752\u5e74\u3001\u513f\u7ae5\u53ca\u8001\u5e74\u3002\u5176\u6b21\uff0c\u6d0b\u9e21\u6240\u5403\u7684\u9972\u6599\u4e2d\u6dfb\u52a0\u4e86\u4e00\u5b9a\u91cf\u7684\u81b3\u98df\u7ea4\u7ef4\uff0c\u4f7f\u5f97\u86cb\u9ec4\u4e2d\u7684\u80c6\u56fa\u9187\u548c\u8102\u80aa\u542b\u91cf\u6bd4\u571f\u9e21\u86cb\u4f4e\u5f88\u591a\uff0c\u66f4\u9002\u5408\u8001\u5e74\u4eba\u98df\u7528\u3002\u53ef\u4ee5\u9884\u9632\u5fc3\u8840\u7ba1\u75be\u75c5\u3001\u764c\u75c7\u548c\u773c\u75c5\u3002&lt;\/span&gt;&lt;\/span&gt;&lt;\/p&gt;&lt;p&gt;&lt;br\/&gt;&lt;\/p&gt;&lt;p&gt;&lt;span style=&quot;font-family: \u5b8b\u4f53; font-size: 14px;&quot;&gt;&lt;img width=&quot;500&quot; height=&quot;499&quot; title=&quot;\u6d0b\u9e21\u86cb0.jpg&quot; style=&quot;width: 280px; height: 289px;&quot; src=&quot;\/Public\/upload\/remote\/2017\/07\/85151501138037.jpg&quot;\/&gt;&lt;\/span&gt;&lt;\/p&gt;","original_img":"\/Public\/upload\/goods\/2017\/07-24\/5976115c4144f.jpg","is_real":1,"is_on_sale":1,"is_free_shipping":0,"on_time":1508233652,"sort":1,"is_recommend":1,"is_new":1,"is_hot":0,"last_update":0,"goods_type":0,"spec_type":1,"give_integral":0,"exchange_integral":0,"suppliers_id":0,"sales_sum":2998,"prom_type":0,"prom_id":0,"commission":"0.00","spu":"","sku":"","shipping_area_ids":"","rec_id":5951,"order_id":2361,"goods_num":25,"goods_price":"114.00","member_goods_price":"114.00","spec_key":"14","spec_key_name":"\u6563\u88c5:27\u65a4\uff08\u542b\u6846\u62bc\u91d115\u5143\uff09","bar_code":"","is_comment":0,"is_send":0,"delivery_id":0,"goods_total":"2850.00"}]}';
        $this->save_log($data,"order",__FUNCTION__);
        $order = json_decode($data['data'],TRUE);
        //产品总数
        $productCount = 0;
        foreach ($order['products'] as $k=>$v){
            $productCount+=$v['goods_num'];
        }
        //用户信息
        $user = $this->mysql_model->get_row(CONTACT,"(shop_user_id='".$order['user_id']."')");
        //总欠款数
        $totalArrears = $this->mysql_model->get_results(INVOICE,"(billType='SALE' AND uid='".$user['id']."')","sum(arrears) totalArrears");

        $insertOrder['buId']            =$user['id'];
//        $insertOrder['billNo']          ="XS".date("YmdHis").rand(0,9);
        $insertOrder['billNo']          ="XS".$order['order_sn'];
        $insertOrder['uid']             =4;
        $insertOrder['userName']        =iconv('GB2312', 'UTF-8',"网商接口");
        $insertOrder['transType']       =150601;
        $insertOrder['billType']        =iconv('GB2312', 'UTF-8','SALE');
        $insertOrder['totalAmount']     =$order['total_amount'];
        $insertOrder['amount']          =$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
        $insertOrder['rpAmount']        =0;
        $insertOrder['billDate']        =date('Y-m-d',$order['add_time']);
        /*if($order['admin_note']!="")    $insertOrder['description']     =iconv('GB2312', 'UTF-8',"管理员：".$order['admin_note']);
        elseif($order['user_note']!="") $insertOrder['description']     =iconv('GB2312', 'UTF-8',"用户：".$order['user_note']);
        elseif($order['admin_note']!="" && $order['user_note']!="")    $insertOrder['description']=iconv('GB2312', 'UTF-8',"管理员：".$order['admin_note']." 用户：".$order['user_note']);*/
        $insertOrder['description']     =$order['consignee']."_".$order['mobile']."_".$order['city'].$order['district'].$order['town'].$order['address'];
        $insertOrder['arrears']         =$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
        $insertOrder['disRate']         =($order['coupon_price']+$order['order_prom_amount'])/$order['total_amount'];
        $insertOrder['disAmount']       =$order['coupon_price']+$order['order_prom_amount'];
        $insertOrder['totalQty']        =$productCount;
        $insertOrder['totalArrears']    =$totalArrears[0]['totalArrears']+$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
        $insertOrder['hxStateCode']     =0;
        $insertOrder['transTypeName']   =iconv('GB2312', 'UTF-8','销货');
        $insertOrder['totalDiscount']   =$order['coupon_price']+$order['order_prom_amount'];
        $insertOrder['salesId']         =$user['salerId'];
        $insertOrder['customerFree']    =$order['shipping_price'];
        $insertOrder['modifyTime']      =date('Y-m-d h:i:s',time());
        $insertOrder['shop_order_id']   =$order['order_id'];

        $res = $this->mysql_model->insert(INVOICE,$insertOrder);

        if($res){
            foreach ($order['products'] as $k=>$v){
                $goods = $this->mysql_model->get_row(GOODS,"(shop_goods_id='".$v['goods_id']."')");
                $orderProduct['iid']            =$res;
                $orderProduct['buId']           =$insertOrder['buId'];
                $orderProduct['billNo']         =$insertOrder['billNo'];
                $orderProduct['transType']      =$insertOrder['transType'];
                $orderProduct['amount']         =$v['goods_num']*$v['goods_price'];
                $orderProduct['billDate']       =$insertOrder['billDate'];
                $orderProduct['description']    ="";
                $orderProduct['invId']          =$goods['id'];
                $orderProduct['price']          =$v['goods_price'];
                $orderProduct['qty']            =-$v['goods_num'];
                $orderProduct['locationId']     =1;
                $orderProduct['unitId']         =-1;
                $orderProduct['skuId']          =-1;
                $orderProduct['entryId']        =1;
                $orderProduct['transTypeName']  =$insertOrder['transTypeName'];
                $orderProduct['billType']       =$insertOrder['billType'];
                $orderProduct['salesId']        =$insertOrder['salesId'];
                $orderProduct['shop_order_id']  =$insertOrder['shop_order_id'];
                $this->mysql_model->insert(INVOICE_INFO,$orderProduct);
            }
        }

        echo $res;
    }

    /**
     * 更新订单
     */
    public function updateOrder(){
        $data = $_POST;
        $this->save_log($data,"order",__FUNCTION__);
        $order = json_decode($data['data'],TRUE);

        //订单信息
        $updateOrder = $this->mysql_model->get_row(INVOICE,"(shop_order_id='".$order['order_id']."')");
        //判断订单是否已确认
        if($updateOrder){
            //产品总数
            $productCount = 0;
            foreach ($order['products'] as $k=>$v){
                $productCount+=$v['goods_num'];
            }
            //用户信息
            $user = $this->mysql_model->get_row(CONTACT,"(shop_user_id='".$order['user_id']."')");
            //总欠款数
            $totalArrears = $this->mysql_model->get_results(INVOICE,"(billType='SALE' AND uid='".$user['id']."')","sum(arrears) totalArrears");

            $update['totalAmount']     =$order['total_amount'];
            $update['amount']          =$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
            $update['description']     =$order['consignee']."_".$order['mobile']."_".$order['city'].$order['district'].$order['town'].$order['address'];
            $update['arrears']         =$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
            $update['disRate']         =($order['coupon_price']+$order['order_prom_amount'])/$order['total_amount'];
            $update['disAmount']       =$order['coupon_price']+$order['order_prom_amount'];
            $update['totalQty']        =$productCount;
            $update['totalArrears']    =$totalArrears[0]['totalArrears']+$order['total_amount']-$order['coupon_price']-$order['order_prom_amount'];
            $update['customerFree']    =$order['shipping_price'];
            $update['modifyTime']      =date('Y-m-d h:i:s',time());

            $where = "(shop_order_id=".$order['order_id'].")";
            $res = $this->mysql_model->update(INVOICE,$update,$where);
            $this->mysql_model->delete(INVOICE_INFO,$where);
            foreach ($order['products'] as $k=>$v){
                $goods = $this->mysql_model->get_row(GOODS,"(shop_goods_id='".$v['goods_id']."')");
                $orderProduct['iid']            =$updateOrder['id'];
                $orderProduct['buId']           =$updateOrder['buId'];
                $orderProduct['billNo']         =$updateOrder['billNo'];
                $orderProduct['transType']      =$updateOrder['transType'];
                $orderProduct['amount']         =$v['goods_num']*$v['goods_price'];
                $orderProduct['billDate']       =$updateOrder['billDate'];
                $orderProduct['description']    ="";
                $orderProduct['invId']          =$goods['id'];
                $orderProduct['price']          =$v['goods_price'];
                $orderProduct['qty']            =-$v['goods_num'];
                $orderProduct['locationId']     =1;
                $orderProduct['unitId']         =-1;
                $orderProduct['skuId']          =-1;
                $orderProduct['entryId']        =1;
                $orderProduct['transTypeName']  =$updateOrder['transTypeName'];
                $orderProduct['billType']       =$updateOrder['billType'];
                $orderProduct['salesId']        =$updateOrder['salesId'];
                $orderProduct['shop_order_id']  =$updateOrder['shop_order_id'];
                $this->mysql_model->insert(INVOICE_INFO,$orderProduct);
            }
        }
        echo $res;
    }

    /**
     * 删除订单
     */
    public function deleteOrder(){
        $data = $_POST;
        $this->save_log($data,"order",__FUNCTION__);
        $where = "(shop_order_id=".$data['order_id'].")";
        $this->mysql_model->delete(INVOICE_INFO,$where);
        $res = $this->mysql_model->delete(INVOICE,$where);
        echo $res;

    }

    /**
     * 更新订单司机
     */
    public function editDriver(){
        $data = $_POST;
        $this->save_log($data,"order",__FUNCTION__);
        $where = "(shop_order_id=".$data['order_id'].")";
        unset($data["order_id"]);
        $res = $this->mysql_model->update(INVOICE,$data,$where);
        echo $res;
    }
    //订单接口结束

    /**
     * 日志方法
     * @param $res 传递 数组/JSON 详情
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