<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use EasyDingTalk\Application;

class DingtalkController extends Controller
{
    public $dingtalk;
    protected  $whiteList = ['010955159635', '095251551325', '02243352475573', '02565464666604', '02545202537221', '02155958316104', '02566750422760', '02595217504362', '01100147554637', '0303282162946090', '01096920058464'];

    public function __construct()
    {
        $config = [
            'corp_id' => 'ding827e7fdc4f03dd20',
            'app_key' => 'dingnn23ewzoucb7eznu',
            'app_secret' => 'CRkFbNvuwqktMEIjQU8_NUHosi-Hq39FU0M0aB_ZTbh-UfqOPwjCYKBvURjvS4CA',
        ];

        $this->dingtalk = new Application($config);
    }

    public function selfLine(Request $request)
    {
        $whiteList = ['010955159635', '095251551325', '02243352475573', '02565464666604', '02545202537221', '02155958316104', '02566750422760', '02595217504362', '01100147554637', '0303282162946090', '01096920058464'];
        $response = $this->dingtalk->user->getUserByCode($request->code);
        if($response['errcode'] == 0) {
            if(in_array($response['userid'], $this->whiteList)){
                return response()->json([
                    'authorization' => true,
                ])->setStatusCode(200);
            }
        }
        return response()->json([
            'authorization' => false,
        ])->setStatusCode(200);

    }

    public function d()
    {
//        $link = [
//            "pic_url" => "https://weixin.sinotrans-zjg.com/smalllogo.png",
//            "message_url" => "dingtalk://dingtalkclient/action/openapp?corpid=ding827e7fdc4f03dd20&container_type=work_platform&app_id=0_214049081&redirect_type=jump&redirect_url=http%3a%2f%2fweixin.sinotrans-zjg.com%2fselfLine.html",
//            "text" => "点击查看详情1",
//            "title" => "自营航线分析1",
//        ];
        $params = [
            "agent_id" => " 214049081",
            "userid_list" => implode(',', $this->whiteList),
	        "msg" => json_encode(
	            [
	                "msgtype" => "link",
                    "link" => [
                        "picUrl" => "https://weixin.sinotrans-zjg.com/smalllogo.png",
                        "messageUrl" => "dingtalk://dingtalkclient/action/openapp?corpid=ding827e7fdc4f03dd20&container_type=work_platform&app_id=0_214049081&redirect_type=jump&redirect_url=http%3a%2f%2fweixin.sinotrans-zjg.com%2fselfLine.html",
                        "text" => "点击查看详情",
                        "title" => "自营航线分析",
                    ]
                ],320
            )
        ];


        //dd($params);
        $reponse = $this->dingtalk->conversation->sendCorporationMessage($params);
        dd($reponse);
//        if($response['errcode'] == 0) {
//            return 'success';
//        }else {
//            return 'failed';
//        }
    }
}
