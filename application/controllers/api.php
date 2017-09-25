<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct(){
        parent::__construct();
		$this->common_model->checkpurview();
    }
	
    //其他入库列表高级搜索
	public function other_search() {
		$this->load->view('storage/other-search');	
	}
	
	//盘点
	public function inventory() {
	    $this->common_model->checkpurview(11);
	    $this->load->view('storage/inventory');	
	}
	
	//其他入库列表高级搜索
	public function transfers_search () {
	    $this->load->view('storage/transfers-search');	
	}
	
	//盘点导入
	public function import () {
	    $this->load->view('storage/import');	
	}
	
	public function getAllUsers(){
        $usersJson = file_get_contents('http://b.com/index.php/Api/Jxcapi/getAllUser');
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
            $this->mysql_model->insert("ci_contact",$data);
        }
    }

    public function inserUser(){
        $data = $this->input->post(NULL,TRUE);
        $this->mysql_model->insert("ci_contact",$data);
    }
	 
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */