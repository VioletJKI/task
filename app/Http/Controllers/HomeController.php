<?php
/**
 * ------------------------
 * Created by PhpStorm.
 * ------------------------
 *
 * ------------------------
 * Author: frank
 * Date: 16-4-19
 * Desc:
 * ------------------------
 *
 */

namespace App\Http\Controllers;

use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Finance\Model\CashoutModel;
use App\Modules\Manage\Model\LinkModel;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\AuthRecordModel;
use Illuminate\Routing\Controller;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Manage\Model\ConfigModel;
use Cache;
use Teepluss\Theme\Theme;


class HomeController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('common');
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        //首页banner
        $banner = \CommonClass::getHomepageBanner();
        $this->theme->set('banner', $banner);

        //公告
        $notice = \CommonClass::getHomepageNotice();
        $this->theme->set('notice',$notice);

        //中标通知
        $taskWin = WorkModel::where('work.status',1)->join('users','users.id','=','work.uid')
            ->leftJoin('task','task.id','=','work.task_id')
            ->select('work.*','users.name','task.show_cash','task.title')
            ->orderBy('work.bid_at','Desc')->limit(5)->get()->toArray();
        $this->theme->set('task_win',$taskWin);

        //提现
        $withdraw = CashoutModel::where('cashout.status',1)->join('users','users.id','=','cashout.uid')
            ->select('cashout.*','users.name')
            ->orderBy('cashout.updated_at','DESC')->limit(5)->get()->toArray();
        $this->theme->set('withdraw',$withdraw);

        //投诉建议用户信息
        $user = \CommonClass::getPhone();
        $this->theme->set('complaints_user',$user);

        //最新任务查询前15条
        $task = TaskModel::where('task.status','>',2)->where('task.bounty_status',1)
            ->where('task.begin_at','<',date('Y-m-d H:i:s',time()))
            ->join('users','users.id','=','task.uid')
            ->select('task.*','users.name')
            ->orderBy('task.created_at','DESC')
            ->orderBy('task.top_status','DESC')->limit(15)->get()->toArray();
        //最新动态查询前10条
        $active = WorkModel::where('work.status',1)->join('users','users.id','=','work.uid')
            ->leftJoin('task','task.id','=','work.task_id')
            ->select('work.*','users.name','task.show_cash','task.title')
            ->orderBy('work.bid_at','Desc')->limit(10)->get()->toArray();

        //推荐服务商
        $recommendPosition = RePositionModel::where('code','HOME_MIDDLE')->where('is_open',1)->first();
        if($recommendPosition['id']){
            $recommend = RecommendModel::getRecommendInfo($recommendPosition['id'],'service')
                ->leftJoin('users','users.id','=','recommend.recommend_id')->orderBy('recommend.created_at','DESC')->limit(8)->get()->toArray();
        }else{
            $recommend = [];
        }
        if(!empty($recommend) && is_array($recommend))
        {
            $recommendIds = array();
            foreach($recommend as $m => $n)
            {
                $recommendIds[] = $n['recommend_id'];
            }
            //查询用户的绑定关系
            $userAuthOne = AuthRecordModel::whereIn('uid', $recommendIds)->where('status', 2)->where('auth_code','!=','realname')->get()->toArray();
            $userAuthTwo = AuthRecordModel::whereIn('uid', $recommendIds)->where('status', 1)->where('auth_code','realname')->get()->toArray();
            $userAuth = array_merge($userAuthOne,$userAuthTwo);
            //查询用户成功案例
            $busSuccess = SuccessCaseModel::whereIn('uid',$recommendIds)
                    ->leftJoin('cate','cate.id','=','success_case.cate_id')
                    ->select('success_case.*','cate.name')
                    ->orderBy('success_case.created_at','DESC')->get()->toArray();

            //查询评论
            $goodComments = CommentModel::whereIn('to_uid',$recommendIds)->where('type',1)->get()->toArray();
            $comments = CommentModel::whereIn('to_uid',$recommendIds)->get()->toArray();
            foreach($recommend as $m => $n)
            {
                if(!empty($busSuccess) && is_array($busSuccess)){
                    foreach($busSuccess as $a => $b){
                        if($n['recommend_id'] == $b['uid']){
                            $recommend[$m]['success'][] = $b;
                        }
                    }
                }
                if(!empty($goodComments) && is_array($goodComments)){
                    foreach($goodComments as $c => $d){
                        if($n['recommend_id'] == $d['to_uid']){
                            $recommend[$m]['good_comments'][] = $d;
                        }
                    }
                }
                if(!empty($comments) && is_array($comments)){
                    foreach($comments as $e => $f){
                        if($n['recommend_id'] == $f['to_uid']){
                            $recommend[$m]['comments'][] = $f;
                        }
                    }
                }
                if (!empty($userAuth) && is_array($userAuth)) {
                    foreach ($userAuth as $w => $z) {
                        if ($n['recommend_id'] == $z['uid']) {
                            $recommend[$m]['authCode'][] = $z;
                        }
                    }
                }
            }
            foreach($recommend as $m => $n){
                if(!isset($recommend[$m]['success'])){
                    $recommend[$m]['success'] = array();
                }
                if(!isset($recommend[$m]['comments'])){
                    $recommend[$m]['comments'] = array();
                }
                if(!isset($recommend[$m]['goodCommentsRate'])){
                    $recommend[$m]['good_comments'] = array();
                }
                if( !empty($recommend[$m]['comments']))
                {
                    $recommend[$m]['good_comment_rate'] = intval((count($recommend[$m]['good_comments'])/count( $recommend[$m]['comments']))*100);
                }
                else
                {
                    $recommend[$m]['good_comment_rate'] = 100;
                }

                if(!empty($recommend[$m]['authCode']) && is_array($recommend[$m]['authCode'])) {
                    foreach ($recommend[$m]['authCode'] as $k => $v) {
                        $recommend[$m]['auth'][] = $v['auth_code'];
                    }
                    if (in_array('realname', $recommend[$m]['auth'])) {
                        $recommend[$m]['realname_auth'] = true;
                    } else {
                        $recommend[$m]['realname_auth']  = false;
                    }
                    if (in_array('bank', $recommend[$m]['auth'])) {
                        $recommend[$m]['bank_auth']  = true;
                    } else {
                        $recommend[$m]['bank_auth'] = false;
                    }
                    if (in_array('alipay', $recommend[$m]['auth'])) {
                        $recommend[$m]['alipay_auth'] = true;
                    } else {
                        $recommend[$m]['alipay_auth']= false;
                    }
                }else{
                    $recommend[$m]['realname_auth']  = false;
                    $recommend[$m]['bank_auth'] = false;
                    $recommend[$m]['alipay_auth'] = false;
                }
            }
        }
        $count = count($recommend);
        $recommendArr = array();
        //重新组装推荐服务商的数组
        for($a=0;$a<$count;$a=$a+2) {
            if(isset($recommend[$a+1])) {
                $reArr = array($recommend[$a],$recommend[$a+1]);
            } else {
                $reArr = array($recommend[$a]);
            }
            $recommendArr[] = $reArr;
        }
        //成功案例
        $recommendPositionSuccess = RePositionModel::where('code','HOME_MIDDLE_BOTTOM')->where('is_open',1)->first();
        if($recommendPositionSuccess['id']){
            $recommendSuccess = RecommendModel::getRecommendInfo($recommendPositionSuccess['id'],'successcase')
                ->join('success_case','success_case.id','=','recommend.recommend_id')
                ->leftJoin('cate','cate.id','=','success_case.cate_id')
                ->leftJoin('user_detail','user_detail.uid','=','success_case.uid')
                ->leftJoin('users','users.id','=','success_case.uid')
                ->select('recommend.*','success_case.id','success_case.cate_id','success_case.title','cate.name','user_detail.avatar','users.name as username')
                ->orderBy('recommend.sort','ASC')->orderBy('recommend.created_at','DESC')->limit(4)->get()->toArray();
        }else{
            $recommendSuccess = [];
        }

        //资讯
        $recommendPositionArticle = RePositionModel::where('code','HOME_BOTTOM')->where('is_open',1)->first();
        if($recommendPositionArticle['id']){
            $article = RecommendModel::getRecommendInfo($recommendPositionArticle['id'],'article')
                ->join('article','article.id','=','recommend.recommend_id')
                ->leftJoin('article_category','article_category.id','=','article.cat_id')
                ->select('recommend.*','article_category.cate_name','article.summary')
                ->orderBy('recommend.created_at','DESC')->limit(5)->get()->toArray();
        }else{
            $article = [];
        }

        $articleArr = array();
        if(!empty($article) && is_array($article))
        {
            foreach($article as $k => $v)
            {
                if($k > 0)
                {
                    $articleArr[] = $v;
                }
            }
        }

        //友情链接
        $friendUrl = LinkModel::where('status',1)->orderBy('addTime','DESC')->get()->toArray();

        //广告位
        $ad = AdTargetModel::getAdInfo('HOME_BOTTOM');

        $data = array(
            'task' => $task,
            'active' => $active,
            'articleArr' => $articleArr,
            'recommend' => $recommendArr,
            'recommend_position' => $recommendPosition,
            'success' => $recommendSuccess,
            'recommend_success' =>$recommendPositionSuccess,
            'article' => $article,
            'recommend_article' => $recommendPositionArticle,
            'friendUrl' => $friendUrl,
            'ad' => $ad
        );
        //seo配置信息
        $seoConfig = ConfigModel::getConfigByType('seo');
        if(!empty($seoConfig['seo_index']) && is_array($seoConfig['seo_index'])){
            $this->theme->setTitle($seoConfig['seo_index']['title']);
            $this->theme->set('keywords',$seoConfig['seo_index']['keywords']);
            $this->theme->set('description',$seoConfig['seo_index']['description']);
        }else{
            $this->theme->setTitle('威客|系统—客客出品,专业威客建站系统开源平台');
            $this->theme->set('keywords','威客,众包,众包建站,威客建站,建站系统,在线交易平台');
            $this->theme->set('description','客客专业开源建站系统，国内外知名站长使用最多的众包威客系统，建在线交易平台，首选KPPW众包威客开源建站系统。');
        }
        $this->theme->set('now_menu','/');
        return $this->theme->scope('bre.homepage',$data)->render();

    }









}