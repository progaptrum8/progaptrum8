<?php
namespace app\modules\baocao\controllers;
use app\components\BaseController;
use yii\data\Pagination;
use app\models\VbVanban;
use app\components\Common;
use app\models\User;
use Yii;

class BaocaothongketheonguoiController extends BaseController
{
    public function actionIndex()
    {
        $listNguoiTiepNhan=User::find()
                ->where(['isDeleted'=>0,'isActive'=>1])
                ->all();
        return $this->render('index',[
            'listNguoiTiepNhan'=>$listNguoiTiepNhan
        ]);
    }
    public function actionShowview()
    {
        $isExcel = (int)trim(Yii::$app->request->post("isExcel"));
        $isPrint = (int)trim(Yii::$app->request->post("isPrint"));
        
        $tuNgayRaw = trim(Yii::$app->request->post("tungay"));
        $denNgayRaw = trim(Yii::$app->request->post("denngay"));
        $selectNguoiTiepNhan = Yii::$app->request->post("selectNguoiTiepNhan");
        $daXoa = trim(Yii::$app->request->post("daXoa"));
        $tuNgay=$tuNgayRaw;
        if($tuNgay!=""){
            $tuNgay = Common::dateVNToUSDate($tuNgay);
        }else{
            $tuNgay = date("Y-m-d");
            $tuNgayRaw=date("d/m/Y");
        }
        $denNgay=$denNgayRaw;
        if($denNgay!=""){
            $denNgay = Common::dateVNToUSDate($denNgay);
        }else{
            $denNgay = date("Y-m-d");
            $denNgayRaw=date("d/m/Y");
        }
        $stringCondition = " CAST(vb.ngayTiepNhan AS DATE) BETWEEN :tuNgay AND :denNgay and vb.isDeleted = :isDeleted";
        $arrayCondition = array(
            ':tuNgay'=>$tuNgay,
            ':denNgay'=>$denNgay,
            ':isDeleted'=>((int) $daXoa == 1 ? 1 : 0)
        );
        if($selectNguoiTiepNhan >0){
            $stringCondition .= " AND vb.idNguoiTiepNhan = :selectNguoiTiepNhan ";
            $arrayCondition[':selectNguoiTiepNhan'] = $selectNguoiTiepNhan;
        }
        
        $ngayChinhSuaTu=trim(Yii::$app->request->post("ngayChinhSuaTu"));
        $ngayChinhSuaDen=trim(Yii::$app->request->post("ngayChinhSuaDen"));
        if($ngayChinhSuaTu != null && $ngayChinhSuaTu != "" && $ngayChinhSuaDen != null && $ngayChinhSuaDen != ""){
            $stringCondition .= " AND CAST(log.ngayChuyen AS DATE) BETWEEN :ngayChinhSuaTu AND :ngayChinhSuaDen";
            $arrayCondition[':ngayChinhSuaTu']= Common::dateVNToUSDate($ngayChinhSuaTu);
            $arrayCondition[':ngayChinhSuaDen']=Common::dateVNToUSDate($ngayChinhSuaDen);
        }elseif($ngayChinhSuaTu != null && $ngayChinhSuaTu != ""){
            $stringCondition .= " AND CAST(log.ngayChuyen AS DATE) >= :ngayChinhSuaTu ";
            $arrayCondition[':ngayChinhSuaTu']=Common::dateVNToUSDate($ngayChinhSuaTu);
        }elseif($ngayChinhSuaDen != null && $ngayChinhSuaDen != ""){
            $stringCondition .= " AND CAST(log.ngayChuyen AS DATE) <= :ngayChinhSuaDen";
            $arrayCondition[':ngayChinhSuaDen']=Common::dateVNToUSDate($ngayChinhSuaDen);
        }
        
        $query = VbVanban::find()->alias('vb')->select(['vb.idVanBan'])
                ->join('LEFT JOIN','vb_vanban_processlog as log', 'log.idVanBan = vb.idVanBan')
                ->where($stringCondition, $arrayCondition)
                ->groupBy(['vb.idVanBan','vb.ngayBanHanh']);         
        //Phân trang
        $pageSize = Yii::$app->request->post("pageSize");
        $perPage = Yii::$app->request->get("per-page");        
        if( (int) $pageSize <= 0){
            if((int)$perPage >0){
                $pageSize = (int)$perPage;
            }else{
                $pageSize = 40;
            }
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $pageSize]);
        
        if($isExcel == 1){
            header('Content-Disposition: attachment; filename="baocaotheonguoi.xls"' );
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-type:application/vnd.ms-excel");
            $data = $query->all();
            return $this->renderPartial('excel',[
                'data' => $data,
                'tuNgay'=>$tuNgayRaw,
                'denNgay'=>$denNgayRaw,
                'ngayChinhSuaTu'=>$ngayChinhSuaTu,
                'ngayChinhSuaDen'=>$ngayChinhSuaDen,
                'daXoa'=>((int) $daXoa == 1 ? 1 : 0)
            ]);
        }
        if($isPrint == 1){
            $data = $query->all();
            return $this->renderPartial('print',['data' => $data]);
        }else{            
            //Lấy dữ liệu
            $data = $query->offset($pages->offset)
                    ->orderBy('vb.ngayBanHanh ASC')
                    ->limit($pages->limit)
                    ->all();
            return $this->renderPartial('show',[                
                'pageSize' => $pageSize,
                'data' => $data,
                'pages' => $pages,
                'tuNgay'=>$tuNgayRaw,
                'denNgay'=>$denNgayRaw,
                'ngayChinhSuaTu'=>$ngayChinhSuaTu,
                'ngayChinhSuaDen'=>$ngayChinhSuaDen,
                'daXoa'=>((int) $daXoa == 1 ? 1 : 0),
                'selectNguoiTiepNhan'=>$selectNguoiTiepNhan
            ]);
        }
    }
}
