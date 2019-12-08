<?php
namespace Home\Model;
use Think\Model;
class UserPushModel extends Model {
    protected $fields = array('pid', 'uid', 'push_name', 'push_id', 'push_mark', 'time',
        '_type'=>array('pid'=>'int', 'uid'=>'int', 'push_name'=>'varchar', 'push_id'=>'varchar', 'push_mark'=>'varchar', 'time'=>'int')
    );

    // 添加推送信息
    public function addPush($formId, $name="Weixin", $mark=null) {
        $uid = session('uid');
        if (session('username') == C('APP_DEMO_USERNAME')) {
            return false;
        }
        if ($uid && $uid > 0) {
            $pushData['uid'] = $uid;
            $pushData['push_name'] = $name;
            $pushData['push_id'] = $formId;
            if ($mark) {
                $pushData['push_mark'] = "Model.".$mark;
            }
            if ($this->where($pushData)->find()) {
                return false;
            }
            $pushData['time'] = time();
            return $this->add($pushData);
        } else {
            return false;
        }
    }

    // 获取推送相关信息
    public function getPushInfo($uid=null, $name="Weixin", $mark=null) {
        if ($uid == null) {
            $uid = session('uid');
        }
        if ($uid && $uid > 0) {
            $data = array();
            $data['uid'] = $uid;
            $data['push_name'] = $name;
            if ($mark) {
                $data['push_mark'] = $mark;
            }
            $dbPush = $this->where($data)->order('time')->find();
            return $dbPush;
        } else {
            return false;
        }
    }

    // 获取推送FormId
    public function getPushFormid($uid=null, $name="Weixin", $mark=null) {
        $dbPush = $this->getPushInfo($uid, $name, $mark);
        if ($dbPush) {
            return $dbPush['push_id'];
        } else {
            return false;
        }
    }

    // 删除FormId
    public function removePushFormid($formid) {
        $data = array();
        $data['push_id'] = $formid;
        return $this->where($data)->delete();
    }

    // 清理过期FormId，可指定多少天以前的记录
    public function clearPushFormid($name="Weixin", $day = 7) {
        $data = array();
        if ($name) {
            $data['push_name'] = $name;
        }
        $data['time'] = array('lt', time() - ($day*24*3600) + 10);
        return $this->where($data)->delete();
    }

    // 获取微信access_token，官方有效期7200秒
    public function getWeixinToken() {
        $token = S('weixin_access_token');
        if (!is_string($token)) {
            $data = request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".C('WX_OPENID')."&secret=".C('WX_SECRET'));
            $data = json_decode($data, true);
            if(isset($data['access_token'])) {
                $token = $data['access_token'];
                S('weixin_access_token', $token, 7000);
            } else {
                return false;
            }
        }
        return $token;
    }

    // 获取推送列表
    public function getWeixinTemplateList() {
        $ret = S('weixin_template_list');
        if (is_array($ret) && count($ret) > 0) {
            return $ret;
        }
        $token = $this->getWeixinToken();
        if ($token) {
            $url = "https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=".$token;
            $data = array();
            $data['offset'] = 0;
            $data['count'] = 20;
            $data = json_decode(request($url, $data, 'json'), true);
            if ($data['errcode'] == 0 && isset($data['list'])) {
                S('weixin_template_list', $data['list'], 7000);
                return $data['list'];
            }
        }
        return false;
    }

    //获取推送TemplateId
    public function getWeixinTemplateId($name = "本月记账成功通知") {
        $list = $this->getWeixinTemplateList();
        if ($list && count($list) > 0) {
            foreach ($list as $key => $item) {
                if ($item['title'] == $name) {
                    return $item['template_id'];
                }
            }
        }
        return false;
    }

    // 获取登录ID
    public function getLoginId($uid = null, $login_name = 'Weixin') {
        if ($uid == null) {
            $uid = session('uid');
        }
        if ($uid && $uid > 0) {
            $data = array();
            $data['uid'] = $uid;
            $data['login_name'] = $login_name;
            return M('user_login')->where($data)->getField('login_id');
        } else {
            return false;
        }
    }

    // 获取可用UID列表
    public function getUidList() {
        $this->clearPushFormid(); //清除不可用数据记录
        $uidList = $this->distinct(true)->field('uid')->select();
        if ($uidList && count($uidList) > 0) {
            $ret = array();
            foreach ($uidList as $key => $item) {
                array_push($ret, $item['uid']);
            }
            return $ret;
        } else {
            return false;
        }
    }

    // 发送微信推送（推送ID，用户ID，小程序跳转页面，推送内容数组）
    public function sendWeixinPush($template_id, $uid=null, $page="main", $arrData = null) {
        $token = $this->getWeixinToken();
        $loginId = $this->getLoginId($uid);
        $formId = $this->getPushFormid($uid);
        if ($token && $loginId && $formId && $template_id) {
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$token;
            $data = array();
            $data['touser'] = $loginId;
            $data['template_id'] = $template_id;
            $data['form_id'] = $formId;
            $data['page'] = 'pages/index/index?jump='.$page;
            if (count($arrData) >= 4) {
                $data['data'] = array(
                    "keyword1"=>array("value"=>$arrData[0]),
                    "keyword2"=>array("value"=>$arrData[1]),
                    "keyword3"=>array("value"=>$arrData[2]),
                    "keyword4"=>array("value"=>$arrData[3]),
                );
            }
            // dump($data);
            $data = json_decode(request($url, $data, 'json'), true);
            // dump($data);
            switch ($data['errcode']) {
                case 0:
                    $this->removePushFormid($formId);
                    return $data;
                    break;
                case 41028:
                case 41029:
                    $this->removePushFormid($formId);
                    $this->sendPush();
                    return $data;
                    break;
                default:
                    dump($data);
                    return false;
                    break;
            }
        }
        return false;
    }
}
