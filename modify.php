<?php
    include 'dbconfig.php';

    /*http 요청의 값에서 값 추출하기*/ 
    $no = (isset($_POST['no']) && $_POST['no'] !='')
    ? $_POST['no'] : '';
    $title = (isset($_POST['title']) && $_POST['title'] !='')
    ? $_POST['title'] : '';
    $content = (isset($_POST['content']) && $_POST['content'] !='')
    ? $_POST['content'] : '';

    /* 이미지를 추출하기*/
    // 정규식 을 활용하여 IMG 태그 전체 / SRC 값을 추출한다.
    preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $content, $matches);

    $img_array = [];
    foreach($matches[1] AS $key => $val){
        // 이미 업로드된 이미지는 그대로사용한다.
        if(substr($val,0,6) == 'upload'){
            $img_array[] = $val;
            continue;
        }

        // 외부 링크 이미지는 skip
        if(substr($val,0,5) != 'data:'){
            continue;
        }

        //  $val 형태 'data:image/png;base64,랜덤영단어와 숫자'
        list($type,$data) = explode(';', $val);
        // $type : data:image/png
        // $data : base64,랜덤 영어단어와 숫자.
        list(,$ext) = explode('/',$type);// , 앞의 base64, 를 짤라낸다.
        $ext = ($ext=='jpeg') ? 'jpg' : $ext;// 확장자 구하기
        // 날짜정보_키값.파일확장자명
        $filename = date('YmdHis').'_'.$key.'.'.$ext;
        //  base64 데이터만 가져옴
        list(,$base64_decode_data) = explode(',',$data);
        
        // base64 데이터를 디코딩함
        $rs_code = base64_decode($base64_decode_data);
        // 디코딩한 데이터를 폴더에 집어넣는다.
        file_put_contents("upload/". $filename, $rs_code);

        $img_array[] = "upload/".$filename;
        // $content = str_replace(변경하기위해 찾을 대상, 변경할 이름, 바꿀대상)
        $content = str_replace($val, "upload/".$filename, $content);
    }

    $imglist = implode('|',$img_array);

    /* DB에 Insert하기 */
    $sql = "UPDATE board SET title=:title, content=:content, imglist=:imglist
        WHERE no=:no";

    $stmt = $conn->prepare($sql);
    $stmt -> bindParam(':title',$title);
    $stmt -> bindParam(':content',$content);
    $stmt -> bindParam(':imglist',$imglist);
    $stmt -> bindParam(':no',$no);
    $stmt -> execute();

    $arr = ['result' => 'success'];

    $j = json_encode($arr); // {"result" : "success"}
    die($j);
?>
