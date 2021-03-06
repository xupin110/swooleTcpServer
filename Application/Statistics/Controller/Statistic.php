<?php
namespace Controller;

class Statistic extends \Framework\CController
{

    public function actionIndex()
    {
        $error_msg = '';
        $success_series_data = [];
        $fail_series_data = [];
        $success_time_series_data = [];
        $fail_time_series_data = [];
        try {

            if(PHP_VERSION >= 7){
                $manager = (new \MongoDB\Client('statistics'))->getManager();
                $collectionListObj = (new \MongoDB\Database($manager,'Statistics'))->listCollections();
                $collectionList = array();
                foreach($collectionListObj as $row){
                    if($row->getName() == 'system.indexes'){
                        continue;
                    }
                    array_push($collectionList,$row->getName());
                }
            } else {
                $mongo = \Mongo\Connection::instance('statistics')->getMongoConnection();
                $db = $mongo->selectDB('Statistics');
                $collectionList = $db->getCollectionNames();
            }

            //去掉全局的统计
            $remove_key = array_search('All_Statistics',$collectionList);
            if($remove_key !== false){
                unset($collectionList[$remove_key]);
            }
            $this->assign('collectionList',$collectionList);

            $_GET['start_time'] = isset($_GET['start_time']) ? $_GET['start_time'] : date('Y-m-d 00:00:00');
            $_GET['end_time'] = isset($_GET['end_time']) ? $_GET['end_time'] : date('Y-m-d 23:59:59');
            if(!isset($_GET['project_name'])){
                throw new \Exception('请先选择项目后再开始查看监控');
            }
            if(empty($_GET['project_name'])){
                throw new \Exception('项目不能为空！');
            }

            $start_timestamp = strtotime($_GET['start_time']);
            $end_timestamp = strtotime($_GET['end_time']);
            if($start_timestamp >= $end_timestamp){
                throw new \Exception('开始时间不能大于等于结束时间！');
            }

            if(PHP_VERSION >= 7){
                $where = array();
                $where['time_stamp'] = array('$gte'=>$start_timestamp,'$lte'=>$end_timestamp);
                if(!empty($_GET['project_name'])){
                    $where['project_name'] = $_GET['project_name'];
                }
                if(!empty($_GET['function_name'])){
                    $where['function_name'] = $_GET['function_name'];
                }
                if(!empty($_GET['project_name'])){
                    $where['project_name'] = $_GET['project_name'];
                }
                $options = array('skip' => 0);
                $collection = new \MongoDB\Collection($manager, 'Statistics',$_GET['project_name']);
                $dataList = $collection->find($where, $options);
                $list = array();
                foreach($dataList as $row) {
                    array_push($list,$row);
                }
            } else {
                $mongo = \Mongo\Connection::instance('statistics')->getMongoConnection();
                $db = $mongo->selectDB('Statistics');
                $collection = $db->selectCollection($_GET['project_name']);
                $where = array();
                $where['time_stamp'] = array('$gt'=>$start_timestamp,'$lt'=>$end_timestamp);
                $where = array();
                if(!empty($_GET['class_name'])){
                    $where['class_name'] = $_GET['class_name'];
                }
                if(!empty($_GET['function_name'])){
                    $where['function_name'] = $_GET['function_name'];
                }
                if(!empty($_GET['project_name'])){
                    $where['project_name'] = $_GET['project_name'];
                }
                $list = $collection->find($where);
            }

            //整理成每5分钟数据，看起来比较清晰些
            $success_series_data = [];
            $fail_series_data = [];
            $success_time_series_data = [];
            $fail_time_series_data = [];
            foreach($list as $row){
                if(isset($success_series_data[$row['time_stamp']])){
                    $success_series_data[$row['time_stamp']]        += $row['success_count'];
                    $fail_series_data[$row['time_stamp']]           += $row['fail_count'];
                    $success_time_series_data[$row['time_stamp']]   += $row['success_cost_time'];
                    $fail_time_series_data[$row['time_stamp']]      += $row['fail_cost_time'];
                } else {
                    $success_series_data[$row['time_stamp']]        = $row['success_count'];
                    $fail_series_data[$row['time_stamp']]           = $row['fail_count'];
                    $success_time_series_data[$row['time_stamp']]   = $row['success_cost_time'];
                    $fail_time_series_data[$row['time_stamp']]      = $row['fail_cost_time'];
                }
            }

            foreach($success_series_data as $time_stamp => $row){
                $success_series_data[$time_stamp]        = "[".($time_stamp*1000).",{$row}]";
            }
            foreach($fail_series_data as $time_stamp => $row){
                $fail_series_data[$time_stamp]        = "[".($time_stamp*1000).",{$row}]";
            }
            foreach($success_time_series_data as $time_stamp => $row){
                $success_time_series_data[$time_stamp]        = "[".($time_stamp*1000).",{$row}]";
            }
            foreach($fail_time_series_data as $time_stamp => $row){
                $fail_time_series_data[$time_stamp]        = "[".($time_stamp*1000).",{$row}]";
            }

            for($i = $start_timestamp; $i < $end_timestamp; $i += 60){
                if(!isset($success_series_data[$i])){
                    $fail_series_data[$i] = "[".($i*1000).",0]";
                    $fail_time_series_data[$i]    = "[".($i*1000).",0]";
                }
            }

            ksort($success_series_data);
            ksort($fail_series_data);
            ksort($success_time_series_data);
            ksort($fail_time_series_data);
        } catch (\Exception $e) {
            $error_msg = $e->getMessage();
        }

        $this->assign('error_msg',$error_msg);
        $this->assign('page_request',$_GET);

        $success_series_data = implode(',', $success_series_data);
        $fail_series_data = implode(',', $fail_series_data);
        $success_time_series_data = implode(',', $success_time_series_data);
        $fail_time_series_data = implode(',', $fail_time_series_data);
        $this->assign('statistics_title','【'.$_GET['start_time'].' ~ '. $_GET['end_time'].'】');
        $this->assign('success_series_data',$success_series_data);
        $this->assign('fail_series_data',$fail_series_data);
        $this->assign('success_time_series_data',$success_time_series_data);
        $this->assign('fail_time_series_data',$fail_time_series_data);
        return $this->display('index');
    }

}