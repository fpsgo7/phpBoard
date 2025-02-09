<?php
    include 'dbconfig.php';

    $no = (isset($_POST['no']) && $_POST['no'] !='') 
        ? $_POST['no'] : '';
    
    // 재대로된 접근일경우
    if($no != ''){
        $sql = "DELETE FROM board WHERE no =:no";
        $stmt = $conn->prepare($sql);
        $stmt -> bindParam(':no',$no);
        $stmt -> execute();

        $arr = ['result' => 'success'];
    }else{
        $arr = ['result' => 'faild'];
    }
    $j = json_encode($arr); // {"result" : "success"}
    die($j);
?>
