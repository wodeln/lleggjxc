<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sales extends CI_Controller {

    public function __construct(){
        parent::__construct();
		$this->common_model->checkpurview();
    }

    public function index(){
        $v = array();
        $data['status'] = 200;
        $data['msg']    = 'success';
        $list = $this->mysql_model->get_results(ADMIN,"(username LIKE 'xs_%') order by uid desc");
        foreach ($list as $arr=>$row) {
            $v[$arr]['id']          =intval($row['uid']);
            $v[$arr]['name']        = $row['name'];
            $v[$arr]['mobile']      = $row['mobile'];
            $v[$arr]['lever']       = $row['lever'];
            $v[$arr]['roleid']      = $row['roleid'];
            $v[$arr]['rightids']    = $row['rightids'];
            $v[$arr]['righttype1']  = $row['righttype1'];
            $v[$arr]['righttype2']  = $row['righttype2'];
            $v[$arr]['righttype3']  = $row['righttype3'];
            $v[$arr]['righttype4']  = $row['righttype4'];
            $v[$arr]['righttype8']  = $row['righttype8'];

            /*$v[$arr]['birthday']    ="";
            $v[$arr]['allowNeg']    = false;
            $v[$arr]['commissionrate'] = 0;
            $v[$arr]['creatorId']    = 0;
            $v[$arr]['deptId']       = 0;
            $v[$arr]['description']  = NULL;
            $v[$arr]['email']        = $row['name'];
            $v[$arr]['empId']        = 0;
            $v[$arr]['empType']      = 1;
            $v[$arr]['fullId']       = 0;
            $v[$arr]['leftDate']     = NULL;
            $v[$arr]['mobile']       = $row['mobile'];
            $v[$arr]['number']       = "0000".$row['number'];
            $v[$arr]['parentId']     = NULL;
            $v[$arr]['sex']          = NULL;
            $v[$arr]['userName']     = "";*/
            $v[$arr]['delete']      = intval($row['status'])==0 ? true : false;   //是否禁用

        }
        $data['data']['items']      = $v;
        $data['data']['total']      = $this->mysql_model->get_count(ADMIN);
        die(json_encode($data));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */